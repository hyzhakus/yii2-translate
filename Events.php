<?php
namespace hyzhak\translate;

use Yii;
use yii\base\Object;

class Events extends Object {
	public static function onMenuRegister($event) {
		$className = $event->sender->className();
		$className::$_additionalMenu['app'][] = [
			'label' => Yii::t('main', 'Translate'),
			'icon' => 'fa fa-language fa-lg',
			'url' => ['/translate'],
			'visible' => Yii::$app->permit->can('translate'),
		];
	}
}
