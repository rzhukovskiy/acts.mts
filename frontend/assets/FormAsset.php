<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class FormAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //'css/bootstrap-checkbox.css'
    ];
    public $js = [
        'js/front-form.js'
    ];
    public $depends = [
        'frontend\assets\AppAsset',
    ];
}
