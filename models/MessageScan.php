<?php
namespace hyzhak\translate\models;

use Yii;
use yii\helpers\FileHelper;
use hyzhak\translate\models\MessageSource;

class MessageScan extends MessageSource {

	private $config = [];
	protected $locations = [];

	public function init() {
		if (!Yii::$app->has('i18n')) {
			throw new \Exception('The i18n component does not exist');
		}
		$i18n = Yii::$app->i18n;
		$this->config = [
			'languages'             => $i18n->languages,
			'sourcePath'            => (is_string($i18n->sourcePath) ? [$i18n->sourcePath] : $i18n->sourcePath),
			'translator'            => $i18n->translator,
			//'sort'                  => $i18n->sort,
			'removeUnused'          => $i18n->removeUnused,
			'only'                  => $i18n->only,
			'except'                => $i18n->except,
			//'format'                => $i18n->format,
			//'db'                    => $i18n->db,
			//'messagePath'           => $i18n->messagePath,
			//'overwrite'             => $i18n->overwrite,
			//'catalog'               => $i18n->catalog,
			//'messageTable'          => $i18n->messageTable,
			//'sourceMessageTable'    => $i18n->sourceMessageTable,
		];
	}

	/**
	* Extracts messages to be translated from source code.
	*
	* @throws Exception on failure.
	* @return array
	**/
	public function extract() {
		if (empty($this->config['languages'])) {
			throw new \Exception("Languages cannot be empty.");
		}
		if (!isset($this->config['sourcePath'], $this->config['languages'])) {
			throw new \Exception('The configuration must specify "sourcePath" and "languages".');
		}
		if (empty($this->config['categories'])) {
			$this->config['categories'] = array_keys(MessageSource::getCategories());
		}

		$files = [];
		foreach ( $this->config['sourcePath'] as $key => $sourcePath ) {
			$path = \Yii::getAlias($sourcePath);
			$this->config['sourcePath'][$key] = $path;
			if (!is_dir($path)) {
				throw new \Exception("The alias {$sourcePath} is not a valid directory.");
			}
			$files = array_merge(
				array_values($files),
				array_values(FileHelper::findFiles(realpath($path), ['only'=>$this->config['only'], 'except'=>$this->config['except']]))
			);
		}

		$messages = [];
		foreach ($files as $file) {
			$messages = array_merge_recursive($messages, $this->extractMessages($file, $this->config['translator']));
		}

		return $this->saveMessages($messages);
	}

	/**
	* Extracts messages from a file
	*
	* @param string $fileName name of the file to extract messages from
	* @param string $translator name of the function used to translate messages
	* @return array
	**/
	protected function extractMessages($fileName, $translator) {
		$subject  = file_get_contents($fileName);
		$messages = [];
		foreach ((array)$translator as $currentTranslator) {
			$translatorTokens = token_get_all('<?php ' . $currentTranslator);
			array_shift($translatorTokens);
			$translatorTokensCount = count($translatorTokens);
			$matchedTokensCount = 0;
			$buffer = [];
			$tokens = token_get_all($subject);
			foreach ($tokens as $token) {
				// finding out translator call
				if ($matchedTokensCount < $translatorTokensCount) {
					if ($this->tokensEqual($token, $translatorTokens[$matchedTokensCount])) {
						$matchedTokensCount++;
					} else {
						$matchedTokensCount = 0;
					}
				} elseif ($matchedTokensCount === $translatorTokensCount) {
					// translator found
					// end of translator call or end of something that we can't extract
					if ($this->tokensEqual(')', $token)) {
						if (isset($buffer[0][0], $buffer[1], $buffer[2][0]) && $buffer[0][0] === T_CONSTANT_ENCAPSED_STRING && $buffer[1] === ',' && $buffer[2][0] === T_CONSTANT_ENCAPSED_STRING) {
							// is valid call we can extract
							$category = stripcslashes($buffer[0][1]);
							$category = mb_substr($category, 1, mb_strlen($category) - 2);
							if(!in_array($category, $this->config['categories'])) continue;
							$message = stripcslashes($buffer[2][1]);
							$message = mb_substr($message, 1, mb_strlen($message) - 2);
							$messages[$category][] = $message;
							foreach ($this->config['sourcePath'] as $sourcePath) {
								$location = str_replace(realpath($sourcePath), '', $fileName);
								if ( $location !== $fileName ) {
									$parts = explode('/', $sourcePath);
									$key   = count($parts) - 1;
									$this->locations[$category][] = [md5($message) => $parts[$key] . $location];
								}
							}
						}
						// prepare for the next match
						$matchedTokensCount = 0;
						$buffer = [];
					} elseif ($token !== '(' && isset($token[0]) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
						// ignore comments, whitespaces and beginning of function call
						$buffer[] = $token;
					}
				}
			}
		}
		return $messages;
	}

	/**
	* Finds out if two PHP tokens are equal
	*
	* @param array|string $a
	* @param array|string $b
	* @return boolean
	* @since 2.0.1
	**/
	protected function tokensEqual($a, $b) {
		if (is_string($a) && is_string($b)) {
			return $a === $b;
		} elseif (isset($a[0], $a[1], $b[0], $b[1])) {
			return $a[0] === $b[0] && $a[1] == $b[1];
		}
		return false;
	}

	/**
	* Saves messages to database
	*
	* @param array $messages
	**/
	public function saveMessages($messages) {
		$db = $this->getDb();
		$removeUnused = $this->config['removeUnused'];
		$current = MessageSource::find()->asArray()->all();
		$current = \yii\helpers\ArrayHelper::map($current, 'id', 'message', 'category');
		$new = $params = [];
		$counter = 0;
		foreach ($messages as $category => $msgs) {
			$msgs = array_unique($msgs);
				$new = isset($current[$category]) ? array_diff($msgs, $current[$category]) : $msgs;
				$counter += count($new);
				foreach($new as $message) {
					$params[] = [
						'category' => $category,
						'message' => $message,
					];
				}
		}
		// Inserting new messages
		$db->createCommand()->batchInsert($this->tableName(), ['category', 'message'], $params)->execute();
		return $counter;
	}
}
