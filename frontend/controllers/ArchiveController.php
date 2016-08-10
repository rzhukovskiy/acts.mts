<?php

namespace frontend\controllers;

class ArchiveController extends \yii\web\Controller
{
    public function actionError($type = null)
    {
        return $this->render('error', ['type' => $type]);
    }

}
