<?php

namespace frontend\controllers;

class UserController extends \yii\web\Controller
{
    public function actionCarwash()
    {
        return $this->render('carwash');
    }

    public function actionCompany()
    {
        return $this->render('company');
    }

    public function actionDisinfection()
    {
        return $this->render('disinfection');
    }

    public function actionService()
    {
        return $this->render('service');
    }

    public function actionTires()
    {
        return $this->render('tires');
    }

    public function actionUniversal()
    {
        return $this->render('universal');
    }

}
