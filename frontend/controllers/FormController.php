<?php

namespace frontend\controllers;


use frontend\models\forms\OwnerForm;
use frontend\models\forms\ServiceForm;
use frontend\models\forms\TiresForm;
use frontend\models\forms\WashForm;
use yii\filters\AccessControl;
use yii\web\Controller;

class FormController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['owner', 'wash', 'service', 'tires'],
                        'allow'   => true,
                    ],
                ],
            ],
        ];
    }

    public function actionOwner()
    {
        $this->view->registerAssetBundle('frontend\assets\FormAsset', \yii\web\View::POS_END);

        $this->layout = 'system';
        $model = new OwnerForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {

        }

        return $this->render('owner',
            [
                'model' => $model
            ]);
    }

    public function actionWash()
    {
        $this->view->registerAssetBundle('frontend\assets\FormAsset', \yii\web\View::POS_END);

        $this->layout = 'system';
        $model = new WashForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {

        }

        return $this->render('wash',
            [
                'model' => $model
            ]);
    }

    public function actionService()
    {
        $this->view->registerAssetBundle('frontend\assets\FormAsset', \yii\web\View::POS_END);

        $this->layout = 'system';
        $model = new ServiceForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {

        }

        return $this->render('service',
            [
                'model' => $model
            ]);
    }

    public function actionTires()
    {
        $this->view->registerAssetBundle('frontend\assets\FormAsset', \yii\web\View::POS_END);

        $this->layout = 'system';
        $model = new TiresForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {

        }

        return $this->render('tires',
            [
                'model' => $model
            ]);
    }


}
