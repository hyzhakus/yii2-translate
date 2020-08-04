<?php
return [
	'id' => 'translate',
	'class' => hyzhak\translate\Module::className(),
	'urlManagerRules' => [
		'translate/<action>' => 'translate/default/<action>',
	],
	'events' => [
		[
			'class' => \app\widgets\Menu::className(),
			'event' => \app\widgets\Menu::EVENT_MODULE_MENU,
			'callback' => [\hyzhak\translate\Events::className(), 'onMenuRegister'],
		],
	],
];
