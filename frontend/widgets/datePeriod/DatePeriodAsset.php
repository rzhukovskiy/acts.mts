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
        if (\Yii::$app->id == 'app-frontend') {
            $this->depends = ['frontend\assets\AppAsset'];
        } else {
            $this->depends = ['yii\web\YiiAsset'];
        }

        parent::init();
    }
}