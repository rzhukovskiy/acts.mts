<?php

namespace backend\controllers;


use common\models\Company;
use frontend\models\forms\OwnerForm;
use frontend\models\forms\ServiceForm;
use frontend\models\forms\TiresForm;
use frontend\models\forms\WashForm;
use yii\filters\AccessControl;
use yii\web\Controller;

class FormController extends Controller
{
    public function beforeAction($action)
    {

        $this->view->registerAssetBundle('backend\assets\FormAsset', \yii\web\View::POS_END);
        $this->layout = 'system';

        return parent::beforeAction($action);
    }

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
                //Сохраняем адрес
                $model->saveCompanyInfo($company->id);
                //сохраняем машины
                $model->saveCarAttribute($company->id);
                //сохраняем города
                $model->saveTownAttribute($company->id);

                return $this->render('_thanks');
            } else {
                $model->addError('name', 'Организация с таким именем уже существует');
            }

        }

        return $this->render('owner',
            [
                'model' => $model
            ]);
    }

    public function actionWash()
    {
        $model = new WashForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->name;
            $company->address = $model->city;
            $company->director = $model->director_fio;
            $company->type = Company::TYPE_WASH;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //Сохраняем адрес
                $model->saveCompanyInfo($company->id);
                //сохраняем директора
                $model->saveDirector($company->id);
                //сохраняем ответственного
                $model->saveResponsible($company->id);
                //Сохраняем клиентов компании
                $model->saveClients($company->id);

                return $this->render('_thanks');
            } else {
                $model->addError('name', 'Организация с таким именем уже существует');
            }
        }

        return $this->render('wash',
            [
                'model' => $model
            ]);
    }

    public function actionService()
    {
        $model = new ServiceForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->name;
            $company->address = $model->city;
            $company->director = $model->director_fio;
            $company->type = Company::TYPE_SERVICE;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //Сохраняем адрес
                $model->saveCompanyInfo($company->id);
                //сохраняем директора
                $model->saveDirector($company->id);
                //сохраняем ответственного
                $model->saveResponsible($company->id);
                //Сохраняем клиентов компании
                $model->saveClients($company->id);
                //сохраняем дилеров
                $model->saveDealerMark($company->id);
                //сохраняем нормочасы
                $model->saveNormHour($company->id);

                return $this->render('_thanks');
            } else {
                $model->addError('name', 'Организация с таким именем уже существует');
            }
        }

        return $this->render('service',
            [
                'model' => $model
            ]);
    }

    public function actionTires()
    {
        $model = new TiresForm();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->name;
            $company->address = $model->city;
            $company->director = $model->director_fio;
            $company->type = Company::TYPE_TIRES;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //Сохраняем адрес
                $model->saveCompanyInfo($company->id);
                //сохраняем директора
                $model->saveDirector($company->id);
                //сохраняем ответственного
                $model->saveResponsible($company->id);
                //Сохраняем клиентов компании
                $model->saveClients($company->id);
                //сохраняем услуги
                $model->saveTypeService($company->id);
                //сохраняем типы ТС для шиномонтажа
                $model->saveTypeCarChangeTires($company->id);
                //сохраняем типы ТС для продажи шин и дисков
                $model->saveTypeCarSellTires($company->id);

                return $this->render('_thanks');
            } else {
                $model->addError('name', 'Организация с таким именем уже существует');
            }
        }

        return $this->render('tires',
            [
                'model' => $model
            ]);
    }


}
