<?php
namespace hyzhak\translate\components;

use Yii;
use yii\base\InvalidConfigException;
use hyzhak\translate\components\DbTranslate;

class I18N extends \yii\i18n\I18N {

	public $messageTable = '{{%message}}';
	public $sourceMessageTable = '{{%source_message}}';
	public $languages;
	public $languagesAll;
	public $categories;
	public $missingTranslationHandler;
	public $db = 'db';
	public $sourcePath = [
		'@app/../web',
		'@app',
		'@vendor/hyzhak',
	];
	public $format = 'db';
    /**
     * @var array list of patterns that specify which files/directories should NOT be processed.
     * If empty or not set, all files/directories will be processed.
     * See helpers/FileHelper::findFiles() description for pattern matching rules.
     * If a file/directory matches both a pattern in "only" and "except", it will NOT be processed.
     */
	public $except = [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/BaseYii.php', // contains examples about Yii:t()
		'/htdocs',
	];
    /**
     * @var array list of patterns that specify which files (not directories) should be processed.
     * If empty or not set, all files will be processed.
     * See helpers/FileHelper::findFiles() description for pattern matching rules.
     * If a file/directory matches both a pattern in "only" and "except", it will NOT be processed.
     */
	public $only = ['*.php'];
    /**
     * String, the name of the function for translating messages.
     * Defaults to 'Yii::t'. This is used as a mark to find the messages to be
     * translated. You may use a string for single function name or an array for
     * multiple function names.
     *
     * @var string
     */
	public $translator = 'Yii::t';
    /**
     * boolean, whether to remove messages that no longer appear in the source code.
     * Defaults to false, which means each of these messages will be enclosed with
     * a pair of '@@' marks.
     *
     * @var boolean
     */
	public $removeUnused = false;

	public function init() {
		if (!$this->languagesAll) {
			if (!isset(Yii::$app->params['languages'])) {
				throw new InvalidConfigException('You should configure i18n component [languagesAll].');
			}
		}
		$this->languages = array_diff(array_keys($this->languagesAll), [Yii::$app->sourceLanguage]);

		if(!isset($this->categories) || !is_array($this->categories)) {
			throw new InvalidConfigException('You should configure i18n component [categories].');
		}

		if (!isset($this->translations['*'])) {
			$this->translations['*'] = [
				'class' => \yii\i18n\DbMessageSource::className(),
				'db' => $this->db,
			];
			if(!empty($this->missingTranslationHandler)) {
				$this->translations['*']['on missingTranslation'] = $this->missingTranslationHandler;
			}
		}

        //if (!isset($this->translations['app']) && !isset($this->translations['app*'])) {
        //    $this->translations['app'] = [
        //        'class' => \yii\i18n\DbMessageSource::className(),
        //        'db' => $this->db,
        //        'sourceMessageTable' => $this->sourceMessageTable,
        //        'messageTable' => $this->messageTable,
        //        'on missingTranslation' => $this->missingTranslationHandler
        //    ];
        //}

		parent::init();
	}
}
