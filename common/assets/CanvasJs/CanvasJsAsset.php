<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 22.08.2016
 * Time: 22:20
 */

namespace common\assets\CanvasJs;

use yii\web\AssetBundle;

class CanvasJsAsset extends AssetBundle
{
    public $sourcePath = '@common/assets/CanvasJs';
    public $css = [];
    public $js = [
        'js/jquery.canvasjs.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}