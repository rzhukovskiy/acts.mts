<?php

namespace frontend\controllers;


use common\models\Company;
use common\models\CompanyAttributes;
use common\models\CompanyClient;
use common\models\CompanyInfo;
use common\models\CompanyMember;
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
                //Сохраняем адрес
                $companyInfo = new CompanyInfo();
                $companyInfo->company_id = $company->id;
                $companyInfo->phone = $model->phone;
                $companyInfo->email = $model->email;
                $companyInfo->save();
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
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->name;
            $company->address = $model->city;
            $company->director = $model->director_fio;
            $company->type = Company::TYPE_WASH;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //Сохраняем адрес
                $companyInfo = new CompanyInfo();
                $companyInfo->company_id = $company->id;
                $companyInfo->phone = $model->phone;
                $companyInfo->address_mail = $model->getAddressMail();
                $companyInfo->start_at = $model->work_from;
                $companyInfo->end_at = $model->work_to;
                $companyInfo->save();
                //сохраняем директора
                $director = new CompanyMember();
                $director->company_id = $company->id;
                $director->position = 'Директор';
                $director->phone = $model->director_phone;
                $director->email = $model->director_email;
                $director->save();
                //сохраняем ответственного
                $responsible = new CompanyMember();
                $responsible->company_id = $company->id;
                $responsible->position = 'Ответственный за договорную работу';
                $responsible->phone = $model->manager_phone;
                $responsible->email = $model->manager_email;
                $responsible->save();
                //Сохраняем клиентов компании
                foreach ($model->organisation_name as $key => $organisation_name) {
                    if (!empty($organisation_name)) {
                        $companyClient = new CompanyClient();
                        $companyClient->company_id = $company->id;
                        $companyClient->name = $organisation_name;
                        $companyClient->phone = $model->organisation_phone[$key];
                        $companyClient->save();
                    }
                }
            }
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
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->name;
            $company->address = $model->city;
            $company->director = $model->director_fio;
            $company->type = Company::TYPE_SERVICE;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //Сохраняем адрес
                $companyInfo = new CompanyInfo();
                $companyInfo->company_id = $company->id;
                $companyInfo->phone = $model->phone;
                $companyInfo->address_mail = $model->getAddressMail();
                $companyInfo->start_at = $model->work_from;
                $companyInfo->end_at = $model->work_to;
                $companyInfo->save();
                //сохраняем директора
                $director = new CompanyMember();
                $director->company_id = $company->id;
                $director->position = 'Директор';
                $director->phone = $model->director_phone;
                $director->email = $model->director_email;
                $director->save();
                //сохраняем ответственного
                $responsible = new CompanyMember();
                $responsible->company_id = $company->id;
                $responsible->position = 'Ответственный за договорную работу';
                $responsible->phone = $model->manager_phone;
                $responsible->email = $model->manager_email;
                $responsible->save();
                //Сохраняем клиентов компании
                foreach ($model->organisation_name as $key => $organisation_name) {
                    if (!empty($organisation_name)) {
                        $companyClient = new CompanyClient();
                        $companyClient->company_id = $company->id;
                        $companyClient->name = $organisation_name;
                        $companyClient->phone = $model->organisation_phone[$key];
                        $companyClient->save();
                    }
                }
                //сохраняем дилеров
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_SERVICE_MARK;
                $companyAttribute->value = $model->getDealerMark();
                $companyAttribute->save();
                //сохраняем нормочасы
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_SERVICE_TYPE;
                $companyAttribute->value = $model->getNormHour();
                $companyAttribute->save();
            }
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
            //Сохраняем компанию
            $company = new Company();
            $company->name = $model->name;
            $company->address = $model->city;
            $company->director = $model->director_fio;
            $company->type = Company::TYPE_TIRES;
            $company->status = Company::STATUS_NEW;
            if ($company->save()) {
                //Сохраняем адрес
                $companyInfo = new CompanyInfo();
                $companyInfo->company_id = $company->id;
                $companyInfo->phone = $model->phone;
                $companyInfo->address_mail = $model->getAddressMail();
                $companyInfo->start_at = $model->work_from;
                $companyInfo->end_at = $model->work_to;
                $companyInfo->save();
                //сохраняем директора
                $director = new CompanyMember();
                $director->company_id = $company->id;
                $director->position = 'Директор';
                $director->phone = $model->director_phone;
                $director->email = $model->director_email;
                $director->save();
                //сохраняем ответственного
                $responsible = new CompanyMember();
                $responsible->company_id = $company->id;
                $responsible->position = 'Ответственный за договорную работу';
                $responsible->phone = $model->manager_phone;
                $responsible->email = $model->manager_email;
                $responsible->save();
                //Сохраняем клиентов компании
                foreach ($model->organisation_name as $key => $organisation_name) {
                    if (!empty($organisation_name)) {
                        $companyClient = new CompanyClient();
                        $companyClient->company_id = $company->id;
                        $companyClient->name = $organisation_name;
                        $companyClient->phone = $model->organisation_phone[$key];
                        $companyClient->save();
                    }
                }
                //сохраняем услуги
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_TIRE_SERVICE;
                $companyAttribute->value = $model->type_service;
                $companyAttribute->save();
                //сохраняем типы ТС для шиномонтажа
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_TYPE_CAR_CHANGE_TIRES;
                $companyAttribute->value = $model->type_car_change_tires;
                $companyAttribute->save();
                //сохраняем типы ТС для продажи шин и дисков
                $companyAttribute = new CompanyAttributes();
                $companyAttribute->company_id = $company->id;
                $companyAttribute->type = CompanyAttributes::TYPE_TYPE_CAR_SELL_TIRES;
                $companyAttribute->value = $model->type_car_sell_tires;
                $companyAttribute->save();
            }
        }

        return $this->render('tires',
            [
                'model' => $model
            ]);
    }


}
