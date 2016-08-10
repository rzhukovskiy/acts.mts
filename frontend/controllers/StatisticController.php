<?php

namespace frontend\controllers;

class StatisticController extends \yii\web\Controller
{
    public function actionList($type = null)
    {
        return $this->render('list', ['type' => $type]);
    }

    public function actionTotal()
    {
        return $this->render('total');
    }

}
