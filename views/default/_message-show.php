<?php

use yii\helpers\Html;
use kartik\editable\Editable;

foreach(Yii::$app->i18n->languages  as $lang ) {
    $message = isset($models[$lang]) ? $models[$lang]->translation : '';
    //$direction = (in_array($lang, ['ar', 'fa']) ? 'rtl' : 'ltr'); // todo: rebuild. get rtl||ltr from locale
    $options= [
        'id' => $lang.'-'.$model->id,
        'data' => [
            'pk' => [
                'id' => $model->id,
                'language' => $lang,
            ],
            'value' => $message,
            'type' => 'textarea',
            'rows' => '3',
            'placement' => 'right',
            //'title' => 'Translate phrase',
            'showbuttons' => 'bottom',
            'url' => yii\helpers\Url::to('/translate/default/update'),
        ]
    ];

    echo Html::beginTag('div', ['class'=>'mt-0', 'data-lang'=>$lang]);
    echo Html::tag('span', strtoupper($lang), ['class'=>'badge mr-2 bg-'.(($message)?'success':'secondary')]);
    echo Html::a($message, '#', $options);
    echo Html::endTag('div');

    $this->registerJs('
        $("#'.$lang.'-'.$model->id.'").editable({
            "success": function(e, params) {
                var label = $(this).prev();
                label.removeClass("bg-success bg-secondary");
                if(params == "") label.addClass("bg-secondary");
                else label.addClass("bg-success");
            }
        });
    ');

}
