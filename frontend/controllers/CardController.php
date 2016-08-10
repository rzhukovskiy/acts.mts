<?php

namespace frontend\controllers;

class CardController extends \yii\web\Controller
{
    public function actionList()
    {
        return $this->render('list');
    }

}
