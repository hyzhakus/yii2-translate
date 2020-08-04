<?php
namespace hyzhak\translate\assets;

class TranslateAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/hyzhak/yii2-translate/web';
    public $css = [
        'css/translate.css',
    ];
    public $js = [
        'js/translate.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}