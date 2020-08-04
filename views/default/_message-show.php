<?php

use yii\helpers\Html;
use dosamigos\editable\Editable;

foreach(Yii::$app->i18n->languages  as $lang ) {
	$message = isset($models[$lang]) ? $models[$lang]->translation : '';
	$direction = (in_array($lang, ['ar', 'fa']) ? 'rtl' : 'ltr'); // todo: rebuild. get rtl||ltr from locale
	echo Html::beginTag('div', ['data-lang'=>$lang]);
	echo Html::tag('span', strtoupper($lang), ['class'=>'label label-'.(($message)?'success':'warning')]);
	echo Editable::widget( [
		'name' => 'translate',
		'value' => $message,
		'url' => yii\helpers\Url::to('/translate/default/update'),
		'type' => 'textarea',
		'mode' => 'pop',
		'placement' => 'top',
		'clientOptions' => [
			'showbuttons' => 'bottom',
			'rows' => 3,
			'tpl' => '<textarea dir="'.$direction.'" style="width:300px;height:150px;"></textarea>',
			'pk' => [
				'id' => $model->id,
				'language' => $lang,
			],
		],
		'clientEvents' => [
			'save' => 'function(e, params) {
				var label = $(e.target).prev();
				label.removeClass("label-success label-warning");
				if(params.newValue == "") label.addClass("label-warning");
				else label.addClass("label-success");
			}',
		],
	]);
	echo Html::endTag('div');
}
