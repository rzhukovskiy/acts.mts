<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class WpaintAsset extends AssetBundle
{
    public $sourcePath = '@vendor/websanova/wPaint';

    public $css = [
        'lib/wColorPicker.min.css',
        'wPaint.min.css',
    ];
    public $js = [
        'lib/jquery.ui.core.1.10.3.min.js',
        'lib/jquery.ui.widget.1.10.3.min.js',
        'lib/jquery.ui.mouse.1.10.3.min.js',
        'lib/jquery.ui.draggable.1.10.3.min.js',
        'lib/wColorPicker.min.js',
        'wPaint.min.js',
        'plugins/main/wPaint.menu.main.min.js',
    ];
    public $depends = [
        'yii\jui\JuiAsset',
    ];
}