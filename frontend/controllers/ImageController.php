<?php

namespace frontend\controllers;

class ImageController extends \yii\web\Controller
{
    public function actionList()
    {
        return $this->render('list');
    }

}
