<?php

namespace frontend\controllers;

class ActController extends \yii\web\Controller
{
    public function actionList( $type = null )
    {
        return $this->render('list', ['type' => $type]);
    }

}
