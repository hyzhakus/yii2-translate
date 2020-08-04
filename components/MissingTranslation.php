<?php
namespace hyzhak\translate\components;

class MissingTranslation extends \yii\base\Component {

	private static $_collection = [];

/*
yii\i18n\MissingTranslationEvent Object
(
    [message] => Tablets
    [translatedMessage] => 
    [category] => main
    [language] => uk
    [name] => missingTranslation
    [sender] => hyzhak\translate\components\DbTranslate Object
*/
    public static function collect(\yii\i18n\MissingTranslationEvent $event) {
		self::setCollection(['category'=>$event->category, 'message'=>$event->message]);
	}

	public static function setCollection($params) {
		self::$_collection[] = $params;
	}

	public static function getCollection() {
		return self::$_collection;
	}
}