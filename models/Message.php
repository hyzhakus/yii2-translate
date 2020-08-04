<?php
namespace hyzhak\translate\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class Message extends ActiveRecord {

    const FILTER_TRANSLATED     = 'translated';
    const FILTER_NOT_TRANSLATED = 'not-translated';
    const FILTER_ALL            = 'all';

	public static function getDb() {
		return Yii::$app->db;
	}

	public static function tableName() {
		$i18n = Yii::$app->i18n;
		if (!isset($i18n->messageTable)) {
			throw new InvalidConfigException('You should configure i18n component. Please provide messageTable.');
		}
		return $i18n->messageTable;
	}

	public function rules() {
		return [
			[['language'], 'required'],
			[['language','translation'], 'string'],
			['language', 'string', 'max'=>16],
			['translation', 'string', 'max'=>2048],
			[['translation'], 'default', 'value' => null],
		];
	}

	public function attributeLabels() {
		return [
			'category' => Yii::t('main', 'Category'),
			'language' => Yii::t('main', 'Language'),
			'message' => Yii::t('main', 'Message'),
			'translation' => Yii::t('main', 'Translations'),
		];
	}

	public function getTranslate() {
		return $this->hasMany(Message::className(), ['id'=>'id'])
			->andwhere(['category'=>new \yii\db\Expression('[category]')])
			->andwhere(['in', 'lang', Yii::$app->i18n->languages]);
	}

	public static function updateTranslate($category, $source, $message) {
		// delete old message
		self::deleteAll(['category'=>$category, 'source'=>$source]);
		// batch insert new message
		$params = [];
		foreach (Yii::$app->i18n->languages as $lang) {
			$params[] = [
				'category' => $category,
				'source' => $message,
				'lang' => $lang,
			];
		}
		self::getDb()->createCommand()->batchInsert(self::tableName(), ['category', 'source', 'lang'], $params)->execute();
	}

}
