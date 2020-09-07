<?php
namespace hyzhak\translate\assets;

class TranslateAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@vendor/hyzhak/yii2-translate/web';
    public $css = [
        'css/translate.css',
        'css/bootstrap-editable.css'
    ];
    public $js = [
        'js/translate.js',
        'js/bootstrap-editable.min.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap4\BootstrapAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
    ];
}
