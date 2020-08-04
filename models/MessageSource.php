<?php
namespace hyzhak\translate\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class MessageSource extends ActiveRecord {

	public static function getDb() {
		return Yii::$app->db;
	}

	public static function tableName() {
		$i18n = Yii::$app->i18n;
		if (!isset($i18n->messageTable)) {
			throw new InvalidConfigException('You should configure i18n component. Please provide messageTable.');
		}
		return $i18n->sourceMessageTable;
	}

	public function rules() {
		return [
			[['category','message'], 'required'],
			['category', 'string', 'max'=>255],
			['message', 'string', 'max'=>2048],
		];
	}

	public function attributeLabels() {
		return [
			'category' => Yii::t('main', 'Category'),
			'message' => Yii::t('main', 'Message'),
		];
	}

	public function getTranslate() {
		return $this->hasMany(Message::className(), ['id'=>'id']);
	}

	public static function getCategories() {
		$categories = [];
		foreach(Yii::$app->i18n->categories as $category) {
			$categories[$category] = \yii\helpers\Inflector::camelize($category);
		}
		if (method_exists('\common\models\Catalog', 'getDictionary')) {
			$categories = $categories + \common\models\Catalog::getDictionary();
		}
		return $categories;
	}

}
