<?php

namespace frontend\widgets\datePeriod;

use yii\web\AssetBundle;

class DatePeriodAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';
        $this->css = ['css/datePeriod.css'];
        $this->js = ['js/datePeriod.js'];
        $this->depends = ['frontend\assets\AppAsset'];
        parent::init();
    }
}