<?php

namespace frontend\controllers;

use common\models\ActExport;
use common\components\ActExporter;
use common\components\ActHelper;
use common\models\Act;
use common\models\ActScope;
use common\models\Car;
use common\models\Company;
use common\models\Entry;
use common\models\Lock;
use common\models\search\ActSearch;
use common\models\search\CarSearch;
use common\models\search\EntrySearch;
use common\models\Service;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use common\models\CompanyOffer;

class ActController extends Controller
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
                        'actions' => ['list', 'update', 'delete', 'view', 'fix', 'export', 'lock', 'unlock', 'closeload', 'exportsave', 'rotate'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'fix', 'export', 'closeload', 'exportsave', 'rotate'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['list', 'update', 'view', 'create', 'sign', 'disinfect', 'create-entry', 'closeload'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER],
                    ],
                ],
            ],
        ];
    }

    public function actionList($type, $company = 0)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $role = Yii::$app->user->identity->role;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'company' => $company,
            'role' => $role,
            'columns' => ActHelper::getColumnsByType($type, $role, $company, !empty(Yii::$app->user->identity->company->children)),
            'is_locked' => Lock::checkLocked($searchModel->period, $searchModel->service_type, true),
        ]);
    }

    public function actionExport($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $exporter = new ActExporter();
        $exporter->exportCSV($searchModel, $company);

        return $this->render('export', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'company' => $company,
            'role' => Yii::$app->user->identity->role,
        ]);
    }

    public function actionLock($type)
    {

        Lock::deleteAll([
            'type' => $type,
            'period' => date('n-Y', time() - 10 * 24 * 3600),
        ]);

        $lock = new Lock();
        $lock->period = date('n-Y', time() - 10 * 24 * 3600);
        $lock->type = $type;
        $lock->company_id = 0;

        $lock->save();

        return "Открыть загрузку";
    }

    public function actionExportsave()
    {

        $type = (int) Yii::$app->request->post('type');
        $company = (int) Yii::$app->request->post('company');
        $dataExpl = (string) Yii::$app->request->post('dataExpl');
        $name = (string) Yii::$app->request->post('name');

        $company_id = 0;

        if((isset($type)) && (isset($company)) && (isset($dataExpl)) && (isset($name))) {

            $resActLoad = ActExport::find()->where(['type' => $type, 'company' => $company, 'period' => $dataExpl, 'name' => $name])->select('id')->column();

            if(count($resActLoad) > 0) {
                // Файл уже скачивали
                echo json_encode(['success' => 'true']);
            } else {

                $companyName = '';
                $tmpName = $name;
                $tmpName = mb_convert_encoding($tmpName, 'utf-8');
                $tmpName = str_replace('__', '_', $tmpName);

                // получаем название компании
                if (mb_strpos($tmpName, 'оп._дезинфекция_Справка_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'доп._дезинфекция_Справка_') + 25));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'оп._дезинфекция_Счет_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'доп._дезинфекция_Счет_') + 22));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'оп._дезинфекция_Акт_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'доп._дезинфекция_Акт_') + 21));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'езинфекция_Справка_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'дезинфекция_Справка_') + 20));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'езинфекция_Счет_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'дезинфекция_Счет_') + 17));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'езинфекция_Акт_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'дезинфекция_Акт_') + 16));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'кт_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'Акт_') + 4));
                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'татистика_анализ_мо') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'Статистика_анализ_мо') + 24));
                    $companyName = str_replace('_', ' ', $companyName);
                    $companyName = trim($companyName);
                    $companyName = str_replace(' ', '_', $companyName);

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'татистика_анализ_сервис_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'Статистика_анализ_сервис_') + 25));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'татистика_анализ_шиномонтаж_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'Статистика_анализ_шиномонтаж_') + 29));

                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                } else if (mb_strpos($tmpName, 'чет_') > 0) {

                    $companyName = mb_substr($tmpName, (mb_strpos($tmpName, 'Счет_') + 5));
                    $companyName = mb_substr($companyName, 0, ((mb_strpos($companyName, '_от'))));
                    $companyName = str_replace('_', ' ', $companyName);

                }

                $companyName = trim($companyName);
                $newCompanyName = str_replace('«', '"', $companyName);
                $newCompanyName = str_replace('»', '"', $newCompanyName);

                switch ($type) {
                    case 1:
                        break;
                    case 2:
                        $companyName = str_replace(' мойка ', '', $companyName);
                        $newCompanyName = str_replace(' мойка ', '', $newCompanyName);
                        break;
                    case 3:
                        $companyName = str_replace(' сервис ', '', $companyName);
                        $newCompanyName = str_replace(' сервис ', '', $newCompanyName);

                        $companyNameArr = explode(' - ', $companyName);

                        if(count($companyNameArr) == 3) {
                            $companyName = $companyNameArr[0];
                            $companyName = trim($companyName);
                        }

                        $companyNameArr = explode(' - ', $newCompanyName);

                        if(count($companyNameArr) == 3) {
                            $newCompanyName = $companyNameArr[0];
                            $newCompanyName = trim($newCompanyName);
                        }

                        break;
                    case 4:
                        $companyName = str_replace(' шиномонтаж ', '', $companyName);
                        $newCompanyName = str_replace(' шиномонтаж ', '', $newCompanyName);
                        break;
                    case 5:
                        break;
                }

                $companyArr = Company::find()->where(['name' => $companyName])->orWhere(['REPLACE(name, "\"", "")' => $companyName])->orWhere(['replace(REPLACE(name, "«", ""), "»" ,"")' => $companyName])->orWhere(['name' => $newCompanyName])->orWhere(['REPLACE(name, "\"", "")' => $newCompanyName])->orWhere(['replace(REPLACE(name, "«", ""), "»" ,"")' => $newCompanyName])->select('id')->column();

                if(isset($companyArr)) {
                    if (count($companyArr) > 0) {
                        if(isset($companyArr[0])) {
                            // получаем id компании
                            $company_id = $companyArr[0];
                        }
                    }
                }

                // добавляем в базу дату первой выгрузки файла
                $actExport = new ActExport();
                $actExport->company_id = $company_id;
                $actExport->type = $type;
                $actExport->company = $company;
                $actExport->period = $dataExpl;
                $actExport->name = $name;
                $actExport->data_load = ((string) time());

                if($actExport->save()) {
                    echo json_encode(['success' => 'true']);
                } else {
                    echo json_encode(['success' => 'false']);
                }

            }

        } else {
            echo json_encode(['success' => 'false']);
        }
    }

    public function actionCloseload($type, $company, $period)
    {

        if (($type == 2) || ($type == 3) || ($type == 4) || ($type == 5)) {

            $lockedLisk = Lock::checkLocked($period, $type);

            if (count($lockedLisk) > 0) {

                $closeAll = false;
                $closeCompany = false;

                for ($c = 0; $c < count($lockedLisk); $c++) {
                    if ($lockedLisk[$c]["company_id"] == 0) {
                        $closeAll = true;
                    }
                    if ($lockedLisk[$c]["company_id"] == $company) {
                        $closeCompany = true;
                    }
                }

                if (($closeAll == false) && ($closeCompany == false)) {

                    $lock = new Lock();
                    $lock->period = $period;
                    $lock->type = $type;
                    $lock->company_id = $company;

                    $lock->save();

                    return 1;
                } elseif (($closeAll == true) && ($closeCompany == true)) {

                    Lock::deleteAll([
                        'type' => $type,
                        'period' => $period,
                        'company_id' => $company,
                    ]);

                    return 1;
                } elseif (($closeAll == true) && ($closeCompany == false)) {
                    return 0;
                } elseif (($closeAll == false) && ($closeCompany == true)) {
                    return 0;
                }

            } else {

                $lock = new Lock();
                $lock->period = $period;
                $lock->type = $type;
                $lock->company_id = $company;

                $lock->save();

                return 1;
            }

        } else {
            return 0;
        }

    }

    public function actionUnlock($type)
    {

        Lock::deleteAll([
            'type' => $type,
            'period' => date('n-Y', time() - 10 * 24 * 3600),
        ]);

        return "Закрыть загрузку";
    }

    public function actionDisinfect($serviceId = null)
    {
        $dataProvider = null;
        $searchModel = new CarSearch(['scenario' => Car::SCENARIO_INFECTED]);
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);
        
        if ($serviceId) {
            $searchModel->is_infected = 1;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            foreach ($dataProvider->getModels() as $car) {
                $existed = Act::find()->where([
                    'car_id' => $car->id,
                    'service_type' => Service::TYPE_DISINFECT,
                    'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period,
                ])->all();
                if (count($existed)) {
                    continue;
                }
                $model = new Act();
                $model->time_str = '01-' . $searchModel->period;
                $model->partner_id = Yii::$app->user->identity->company_id;
                $model->disinfectCar($car, $serviceId);
            }
        }

        return $this->render('disinfect', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'serviceList' => Service::find()->where(['type' => Service::TYPE_DISINFECT])->select(['description', 'id'])->indexBy('id')->column(),
            'companyList' => Company::find()->byType(Company::TYPE_OWNER)->select(['name', 'id'])->indexBy('id')->active()->column(),
            'role' => Yii::$app->user->identity->role,
        ]);
    }

    public function actionFix($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;

        foreach ($searchModel->search(Yii::$app->request->queryParams)->getModels() as $act) {
            $act->byAdmin = Yii::$app->user->identity->role == User::ROLE_ADMIN;
            $act->save();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Creates Act model.
     * @param integer $type
     * @return mixed
     */
    public function actionCreate($type)
    {
        $model = new Act();
        $model->service_type = $type;
        $model->partner_id = Yii::$app->user->identity->company_id;

        // Возобновляем рассылку
        $modelOffer = CompanyOffer::findOne(['company_id' => Yii::$app->user->identity->company_id]);
        if(isset($modelOffer)) {
            $modelOffer->email_status = 1;
            $modelOffer->save();
        }
        // Возобновляем рассылку

        $showError = '';

        $serviceList = '';

        if($type == 2) {
            $serviceList = Service::find()->innerJoin('company_service', '`company_service`.`company_id`=' . Yii::$app->user->identity->company_id . ' AND `company_service`.`service_id` = `service`.`id`')->where(['`service`.`type`' => $type])
                ->groupBy('`service`.`id`')->orderBy('`service`.`id`')->select(['description', '`service`.`id`'])
                ->indexBy('id')->column();
        } else {
            $serviceList = Service::find()->where(['type' => $type])
                ->orderBy('description')->select(['description', 'id'])
                ->indexBy('id')->column();
        }

        if ($model->load(Yii::$app->request->post())) {
            $entryId = Yii::$app->request->post('entry_id', false);
            if ($entryId) {
                $modelEntry = Entry::findOne($entryId);
                $model->attributes = $modelEntry->attributes;
                $model->partner_id = $modelEntry->company_id;
                $model->served_at = $modelEntry->end_at;

                if ($model->save()) {
                    $modelEntry->act_id = $model->id;
                    if ($modelEntry->save()) {
                        return $this->redirect(['act/create-entry', 'type' => $type]);
                    }
                }
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                $model->image = UploadedFile::getInstance($model, 'image');
                if ($model->save()) {
                    if (Yii::$app->user->identity->company->is_sign) {
                        return $this->redirect(['act/sign', 'id' => $model->id]);
                    }
                    return $this->redirect(Yii::$app->request->referrer);
                }
                $showError = $model->getErrors();
            }
        }

        if (!empty(Yii::$app->user->identity->company->schedule)) {
            return $this->redirect(['act/create-entry', 'type' => $type]);
        }

        $searchModel = new ActSearch();
        $searchModel->partner_id = Yii::$app->user->identity->company_id;
        $searchModel->service_type = $type;
        $searchModel->createDay = date('Y-m-d');

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $role = Yii::$app->user->identity->role;

        return $this->render('create', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'serviceList' => $serviceList,
            'role' => $role,
            'model' => $model,
            'showError' => $showError,
            'columns' => ActHelper::getColumnsByType($type, $role, 0, !empty(Yii::$app->user->identity->company->children)),
        ]);
    }

    /**
     * Creates Entry model.
     * @param integer $type
     * @param string $day
     * @return mixed
     */
    public function actionCreateEntry($type, $day = null)
    {
        $model = new Entry();
        $model->service_type = $type;
        $model->company_id = Yii::$app->user->identity->company_id;
        if (!$day) {
            $model->day = date('d-m-Y');
        } else {
            $model->day = $day;
        }

        $serviceList = Service::find()->where(['type' => $type])->select(['description', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post())) {
            $modelAct = new Act();
            $modelAct->load(Yii::$app->request->post());
            $modelAct->attributes = $model->attributes;
            $modelAct->partner_id = $model->company_id;
            $modelAct->served_at = \DateTime::createFromFormat('d-m-Y H:i:s', $model->day . ' ' . $model->start_str . ':00')->getTimestamp();

            if (!empty($modelAct->serviceList) && $modelAct->save()) {
                $model->act_id = $modelAct->id;
            }

            if ($model->save()) {
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        $searchModel = new ActSearch();
        $searchModel->partner_id = Yii::$app->user->identity->company_id;
        $searchModel->service_type = $type;
        $searchModel->createDay = date('Y-m-d');

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $entrySearchModel = new EntrySearch();
        $entrySearchModel->load(Yii::$app->request->queryParams);
        $entrySearchModel->company_id = $model->company_id;
        $entrySearchModel->day = $model->day;
        $role = Yii::$app->user->identity->role;

        return $this->render('create-entry', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'entrySearchModel' => $entrySearchModel,
            'type' => $type,
            'serviceList' => $serviceList,
            'role' => $role,
            'model' => $model,
            'columns' => ActHelper::getColumnsByType($type, $role, 0),
        ]);
    }

    /**
     * Updates Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->time_str = date('d-m-Y', $model->served_at);

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
                ActScope::deleteAll(['act_id' => $model->id]);
                $model->delete();
            }

            $model->image = UploadedFile::getInstance($model, 'image');
            if ($model->save()) {
                return $this->redirect(Yii::$app->request->post('__returnUrl'));
            }
        }

        $clientScopes = $model->getClientScopes()->where(['parts' => 0])->all();
        $partnerScopes = $model->getPartnerScopes()->where(['parts' => 0])->all();

        $partsClientScopes = '';
        $partsPartnerScopes = '';

        if($model->service_type == 3) {
            $partsClientScopes = $model->getClientScopes()->where(['!=', 'parts', 0])->all();
            $partsPartnerScopes = $model->getPartnerScopes()->where(['!=', 'parts', 0])->all();
        }

        $serviceList = Service::find()->where(['type' => $model->service_type])->select(['description', 'id'])->indexBy('id')->column();
        return $this->render('update', [
            'model' => $model,
            'serviceList' => $serviceList,
            'clientScopes' => $clientScopes,
            'partnerScopes' => $partnerScopes,
            'partsClientScopes' => $partsClientScopes,
            'partsPartnerScopes' => $partsPartnerScopes,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);

    }

    /**
     * Shows Act model.
     * @param integer $id
     * @param bool $company
     * @return mixed
     */
    public function actionView($id, $company = false)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'company' => $company,
        ]);
    }

    public function actionRotate()
    {

        if((Yii::$app->request->post('name')) && (Yii::$app->request->post('type'))) {

            $imagePath = \Yii::getAlias('@webroot' . Yii::$app->request->post('name'));

            if (file_exists($imagePath)) {
                chmod($imagePath, 0775);

                $img = '';

                if(mime_content_type($imagePath) == 'image/gif') {
                    $img = imagecreatefromgif($imagePath);
                } else if(mime_content_type($imagePath) == 'image/png') {
                    $img = imagecreatefrompng($imagePath);
                } else if(mime_content_type($imagePath) == 'image/jpeg') {
                    $img = imagecreatefromjpeg($imagePath);
                }

                $rotation = 0;

                if(Yii::$app->request->post('type') == 1) {
                    $rotation = 90;
                } else {
                    $rotation = -90;
                }

                $imgRotated = imagerotate($img, $rotation, 0);

                unlink($imagePath);

                imagejpeg($imgRotated, $imagePath, 90);
                chmod($imagePath, 0775);

                echo json_encode(['success' => 'true', 'link' => $imagePath]);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    /**
     * Signs Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionSign($id)
    {
        $model = $this->findModel($id);

        if (isset($_POST['name'])) {
            $data = explode('base64,', $_POST['name']);

            $str = base64_decode($data[1]);
            $image = imagecreatefromstring($str);

            imagealphablending($image, false);
            imagesavealpha($image, true);
            $dir = 'files/checks/';
            imagepng($image, $dir . $id . '-name.png');
            return Json::encode(['file' => $id]);
        }

        if (isset($_POST['sign'])) {
            $data = explode('base64,', $_POST['sign']);

            $str = base64_decode($data[1]);
            $image = imagecreatefromstring($str);

            imagealphablending($image, false);
            imagesavealpha($image, true);
            $dir = 'files/checks/';
            imagepng($image, $dir . $id . '-sign.png');
            return Json::encode(['file' => $id]);
        }

        return $this->render('sign', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Act model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        // Удаляем цены из удаленного акта
        Yii::$app->db->createCommand()->delete('{{%act_scope}}', ['act_id' => $id])->execute();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Act model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Act the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Act::findOne($id)) !== null) {
            if (
                Yii::$app->user->can(User::ROLE_ADMIN) ||
                Yii::$app->user->can(User::ROLE_WATCHER) ||
                Yii::$app->user->identity->company_id == $model->partner_id ||
                Yii::$app->user->identity->company_id == $model->client_id ||
                Yii::$app->user->identity->company_id == $model->client->parent_id
            ) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
