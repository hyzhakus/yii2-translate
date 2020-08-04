<?php
namespace hyzhak\translate\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use hyzhak\translate\models\MessageSource;

class MessageSearch extends Message {

	const STATUS_TRANSLATED = 1;
	const STATUS_NOT_TRANSLATED = 2;
	public $category;
	public $message;
	public $status;
	public $translation;
	public $language;

	public function rules() {
		return [
			['category', 'safe'],
			['message', 'safe'],
			['language', 'safe'],
			['translation', 'safe'],
			['status', 'safe']
		];
	}

	public function search($params) {
		$filter = Yii::$app->request->get('filter', Message::FILTER_ALL);
		$query = MessageSource::find();
		$query->joinWith('translate');
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'attributes' => [
					'category', 'message'
				],
				'defaultOrder' => [
					'category' => SORT_ASC,
					'message' => SORT_ASC,
				]
			],
		]);

		switch($filter) {
			case Message::FILTER_TRANSLATED:
				$query->andWhere("translation != ''");
		// 		$query->having(['cnt' => count($languages)]);
				break;
			case Message::FILTER_NOT_TRANSLATED:
				$query->andWhere("translation = '' OR translation IS NULL");
				break;
		}

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$query
			->andWhere([
				'category' => array_keys(MessageSource::getCategories()),
			])
			->andFilterWhere(['category' => $this->category])
			->andFilterWhere(['like', 'UPPER(message)', strtoupper($this->message)])
			->andFilterWhere(['like', 'UPPER(translation)', strtoupper($this->translation)]);
		return $dataProvider;
	}

	public static function getStatus($id = null) {
		$statuses = [
			self::STATUS_TRANSLATED => Yii::t('main', 'Translated'),
			self::STATUS_NOT_TRANSLATED => Yii::t('main', 'Not Translated'),
		];
		if ($id !== null) {
			return ArrayHelper::getValue($statuses, $id, null);
		}
		return $statuses;
	}
}
