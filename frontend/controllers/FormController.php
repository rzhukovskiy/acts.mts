<?php

namespace frontend\controllers;


use common\models\Company;
use common\models\CompanyAttributes;
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
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->company;
            $company->address = $model->town;
            $company->director = $model->name;
            $company->type = Company::TYPE_OWNER;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //сохраняем машины
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_OWNER_CAR;
                $companyAttribute->value = $model->getCarComplexField();
                $companyAttribute->save();
                //сохраняем города
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_OWNER_CITY;
                $companyAttribute->value = $model->getPreparedCity();
                $companyAttribute->save();
            }

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
