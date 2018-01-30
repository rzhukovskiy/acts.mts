<?php

namespace frontend\controllers;

use common\components\Translit;
use common\models\ActError;
use common\models\Mark;
use common\models\PenaltyInfo;
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
use common\models\Type;
use common\models\User;
use frontend\models\Penalty;
use yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use common\models\CompanyOffer;
use PHPExcel_IOFactory;

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
                        'actions' => ['list', 'update', 'delete', 'view', 'fix', 'export', 'lock', 'unlock', 'closeload', 'exportsave', 'rotate', 'penalty'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'fix', 'export', 'closeload', 'exportsave', 'rotate', 'penalty'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view', 'penalty'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['list', 'update', 'view', 'create', 'sign', 'disinfect', 'deldisinfect', 'create-entry', 'closeload'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER],
                    ],
                    [
                        'actions' => ['penaltysearch', 'penaltyupdate'],
                        'allow' => true,
                        'roles' => ['?'],
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

                $name = str_replace('«', '', $name);
                $name = str_replace('»', '', $name);

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

    public function actionDisinfect($serviceId = null, $showError = '')
    {
        $dataProvider = null;
        $searchModel = new CarSearch(['scenario' => Car::SCENARIO_INFECTED]);
        $searchModel->period = date('n-Y', strtotime("+1 month"));
        $searchModel->periodel = date('n-Y', strtotime("+1 month"));
        $searchModel->periodex = date('n-Y', strtotime("+1 month"));

        if ($serviceId) {
            // Массовая дезинфекция из компании
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

        if(Yii::$app->request->isPost) {
            // Массовая дезинфекция из файла
            $uploadFile = UploadedFile::getInstanceByName('CarList');

            if(isset($uploadFile)) {

                $period = Yii::$app->request->post('CarSearch')['periodex'];
                $service_id = Yii::$app->request->post('service_id');

                // Проверяем что загружен Excel файл
                $arrFileName = explode('.', $uploadFile->name);
                $countArrFileName = count($arrFileName) - 1;

                if (($arrFileName[$countArrFileName] == 'xlsx') || ($arrFileName[$countArrFileName] == 'xls')) {
                    $pExcel = PHPExcel_IOFactory::load($uploadFile->tempName);

                    // Загружаем только первую страницу
                    $firstPage = false;
                    $tables = [];

                    foreach ($pExcel->getWorksheetIterator() as $worksheet) {

                        if ($firstPage == false) {
                            $tables[] = $worksheet->toArray();
                            $firstPage = true;
                        }

                    }

                    $tables = $tables[0];

                    if (isset($tables[0][0])) {
                        $companyName = trim($tables[0][0]);

                        // Ищем компанию по названию
                        $companyArr = Company::find()->where(['name' => $companyName])->asArray()->select('id')->column();
                        $company_id = count($companyArr) > 0 ? $companyArr[0] : 0;

                        if ($company_id > 0) {

                            // Цикл по строкам
                            $numRows = count($tables);

                            $numTrueDis = 0;

                            if ($numRows > 2) {

                                for ($i = 0; $i < $numRows; $i++) {

                                    // Цикл по столбцам
                                    if ($i > 1) {

                                        $numCol = count($tables[$i]);

                                        if ($numCol > 3) {
                                            // Проверка

                                            if ((isset($tables[$i][0]) && (isset($tables[$i][1]))) && (isset($tables[$i][2])) && (isset($tables[$i][3]))) {

                                                if ($tables[$i][3] == 1) {

                                                    $mark = $tables[$i][0];
                                                    $number = mb_strtoupper(str_replace(' ', '', $tables[$i][1]), 'UTF-8');
                                                    $number = strtr($number, Translit::$rules);
                                                    $type_id = $tables[$i][2];

                                                    $markArr = Mark::find()->where(['name' => $mark])->asArray()->select('id')->column();
                                                    $mark_id = count($markArr) > 0 ? $markArr[0] : 0;

                                                    $typeArr = Type::find()->where(['name' => $type_id])->asArray()->select('id')->column();
                                                    $type_id = count($typeArr) > 0 ? $typeArr[0] : 0;

                                                    $carArr = Car::find()->where(['number' => $number])->asArray()->all();
                                                    $car_id = isset($carArr[0]['id']) ? $carArr[0]['id'] : 0;

                                                    if(count($carArr) > 0) {
                                                        // не заменять марку $mark_id = $carArr[0]['mark_id'];
                                                        //$type_id = $carArr[0]['type_id'];
                                                    }

                                                    if (($type_id > 0) && (mb_strlen($number) > 3)) {

                                                        $model = new Act();
                                                        $model->time_str = '01-' . $period;
                                                        $model->partner_id = Yii::$app->user->identity->company_id;

                                                        $model->client_id = $company_id;
                                                        $model->car_number = $number;
                                                        $model->mark_id = $mark_id;
                                                        $model->type_id = $type_id;
                                                        $model->car_id = $car_id;
                                                        $model->service_type = Service::TYPE_DISINFECT;

                                                        $model->serviceList = [
                                                            0 => [
                                                                'service_id' => $service_id,
                                                                'amount' => 1,
                                                            ]
                                                        ];

                                                        if($model->save()) {
                                                            // Удаляем ошибочные акты где неверный номер ТС
                                                            ActError::deleteAll(['act_id' => $model->id, 'error_type' => 4]);
                                                        }

                                                        $numTrueDis++;
                                                    }

                                                }

                                            }

                                        }

                                    }

                                }

                                if (($numTrueDis == 0) && ($showError == '')) {
                                    $showError = 'Неверный формат файла.';
                                } else if (($numTrueDis > 0) && ($showError == '')) {
                                    return $this->redirect(['act/disinfect', 'CarSearch[period]' => $period, 'CarSearch[company_id]' => $company_id, 'serviceId' => $service_id]);
                                }

                            }

                        } else {
                            $showError = 'Неверное название компании. Компания не найдена.';
                        }

                    } else {
                        $showError = 'Неверный формат файла.';
                    }

                } else {
                    $showError = 'Неверный тип файла.';
                }

            } else {
                $showError = 'Не выбран файл.';
            }

        }

        return $this->render('disinfect', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'showError' => $showError,
            'serviceList' => Service::find()->where(['type' => Service::TYPE_DISINFECT])->select(['description', 'id'])->indexBy('id')->column(),
            'companyList' => Company::find()->byType(Company::TYPE_OWNER)->select(['name', 'id'])->indexBy('id')->active()->column(),
            'role' => Yii::$app->user->identity->role,
        ]);
    }

    // Удаление массовой дезинфекции
    public function actionDeldisinfect()
    {

        if(Yii::$app->request->isPost) {

            if((Yii::$app->request->post('CarSearch')) && (isset(Yii::$app->request->post('CarSearch')['periodel'])) && (Yii::$app->request->post('service_id')) && (Yii::$app->request->post('CarSearch')['company_del'])) {

                $period = Yii::$app->request->post('CarSearch')['periodel'];
                $comopany_id = Yii::$app->request->post('CarSearch')['company_del'];
                $service_id = Yii::$app->request->post('service_id');
                $periodQuery = strtotime('01-' . $period);

                $arrPeriod = explode('-', $period);

                if($periodQuery >= strtotime('01-' . date('n-Y'))) {

                    $query = Yii::$app->db->createCommand("DELETE act, act_scope FROM act INNER JOIN act_scope ON act_scope.act_id = act.id WHERE act.client_id=" . $comopany_id . " AND act.service_type=5 AND act_scope.service_id=" . $service_id . " AND (MONTH(FROM_UNIXTIME(act.served_at)) = " . $arrPeriod[0] . ") AND (YEAR(FROM_UNIXTIME(act.served_at)) = " . $arrPeriod[1] . ");")->query();

                    return $this->redirect(['act/disinfect']);
                } else {
                    return $this->redirect(['act/disinfect', 'showError' => 'Задан неверный период']);
                }



            } else {
                return $this->redirect(['act/disinfect', 'showError' => 'Заполните все данные']);
            }

        } else {
            return $this->redirect(['act/disinfect', 'showError' => 'Ошибка удаления']);
        }

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

        /* старый вывод услуг в лк
         * if($type == 2) {
            $serviceList = Service::find()->innerJoin('company_service', '(`company_service`.`company_id`=' . Yii::$app->user->identity->company_id . ' AND `company_service`.`service_id` = `service`.`id`) OR `service`.`id`=52')->where(['`service`.`type`' => $type])
                ->groupBy('`service`.`id`')->orderBy('`service`.`id`')->select(['description', '`service`.`id`'])
                ->indexBy('id')->column();
        } else {
            $serviceList = Service::find()->where(['type' => $type])
                ->orderBy('description')->select(['description', 'id'])
                ->indexBy('id')->column();
        }*/
        $serviceList = Service::find()->where(['type' => $type])
            ->orderBy('description')->select(['description', 'id'])
            ->indexBy('id')->column();

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

        $serviceList = [];

        /* старый вывод услуг в лк
         * if($model->service_type == 2) {
            $serviceList = Service::find()->innerJoin('company_service', '(`company_service`.`company_id`=' . $model->client_id . ' AND `company_service`.`service_id` = `service`.`id`) OR `service`.`id`=52')->where(['`service`.`type`' => $model->service_type])
                ->groupBy('`service`.`id`')->orderBy('`service`.`id`')->select(['description', '`service`.`id`'])
                ->indexBy('id')->column();
        } else {
            $serviceList = Service::find()->where(['type' => $model->service_type])->select(['description', 'id'])->indexBy('id')->column();
        }*/
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

    // Детализация штрафа
    public function actionPenalty($id)
    {

        $model = PenaltyInfo::findOne($id);

        return $this->render('penalty', [
            'model' => $model,
            'id' => $id,
        ]);

    }

    // Cron для проверки штрафов
    public function actionPenaltysearch()
    {

        // Рассылка 2 раза в неделю для партреров
        if (isset(Yii::$app->user->identity->id)) {
            return $this->redirect('/');
        } else {

            $numCreate = 0;

            $resCompany = Company::find()->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->where(['company.use_penalty' => 1])->andWhere(['not', ['company_info.inn' => null]])->select('company.use_penalty, company.id, company_info.inn')->orderBy('company.id')->asArray()->all();

            if(count($resCompany) > 0) {

                // Штрафы
                $modelPenalty = new Penalty();
                $modelPenalty->createToken();

                // Получаем токен
                $token = $modelPenalty->createToken();
                $resToken = json_decode($token[1], true);

                // Сохраняем полученный токен
                $modelPenalty->setParams(['token' => $resToken['token']]);

                $arrCompanyID = [];
                $arrPenaltyValue = [];
                $arrPenIDs = [];

                for ($i = 0; $i < count($resCompany); $i++) {
                    if (mb_strlen($resCompany[$i]['inn']) > 3) {
                        $arrCompanyID[] = $resCompany[$i]['id'];

                        // Проверка штрафа
                        $resPenalty = $modelPenalty->getClientFines($resCompany[$i]['id'] . '@mtransservice.ru');
                        $arrPenalty = json_decode($resPenalty[1], true);

                        if(isset($arrPenalty['fines'])) {
                            if(count($arrPenalty['fines']) > 0) {

                                $arrPenCont = $arrPenalty['fines'];

                                // Записываем штрафы
                                for ($z = 0; $z < count($arrPenCont); $z++) {
                                    $arrPenaltyValue[$z]['id'] = $arrPenCont[$z]['id'];
                                    $arrPenaltyValue[$z]['carCert'] = $arrPenCont[$z]['carCert'];
                                    $arrPenaltyValue[$z]['carReg'] = $arrPenCont[$z]['carReg'];

                                    if((!isset($arrPenCont[$z]['koapText'])) && (!isset($arrPenCont[$z]['name']))) {
                                        $arrPenaltyValue[$z]['description'] = $arrPenCont[$z]['wireUsername'];
                                    } else {
                                        $arrPenaltyValue[$z]['description'] = isset($arrPenCont[$z]['koapText']) ? $arrPenCont[$z]['koapText'] : $arrPenCont[$z]['name'];
                                    }

                                    $arrPenaltyValue[$z]['postNumber'] = $arrPenCont[$z]['postNumber'];
                                    $arrPenaltyValue[$z]['postedAt'] = $arrPenCont[$z]['postedAt'];
                                    $arrPenaltyValue[$z]['violationAt'] = isset($arrPenCont[$z]['violationAt']) ? $arrPenCont[$z]['violationAt'] : $arrPenCont[$z]['postedAt'];
                                    $arrPenaltyValue[$z]['amount'] = $arrPenCont[$z]['amount'];
                                    $arrPenaltyValue[$z]['totalAmount'] = $arrPenCont[$z]['totalAmount'];
                                    $arrPenaltyValue[$z]['isDiscount'] = $arrPenCont[$z]['isDiscount'];
                                    $arrPenaltyValue[$z]['discountDate'] = $arrPenCont[$z]['discountDate'];
                                    $arrPenaltyValue[$z]['discountSize'] = $arrPenCont[$z]['discountSize'];
                                    $arrPenaltyValue[$z]['isExpired'] = $arrPenCont[$z]['isExpired'];
                                    $arrPenaltyValue[$z]['penaltyDate'] = $arrPenCont[$z]['penaltyDate'];
                                    $arrPenaltyValue[$z]['isPaid'] = $arrPenCont[$z]['isPaid'];
                                    $arrPenaltyValue[$z]['docType'] = $arrPenCont[$z]['docType'];
                                    $arrPenaltyValue[$z]['docNumber'] = $arrPenCont[$z]['docNumber'];
                                    $arrPenaltyValue[$z]['enablePics'] = $arrPenCont[$z]['enablePics'];
                                    $arrPenaltyValue[$z]['pics'] = $arrPenCont[$z]['pics'];

                                    $arrPenIDs[] = $arrPenCont[$z]['id'];

                                }

                            }
                        }

                    }
                }

                $arrPenCheck = PenaltyInfo::find()->where(['pen_id' => $arrPenIDs])->select('pen_id')->asArray()->column();

                $arrPenIDs = [];
                for ($n = 0; $n < count($arrPenCheck); $n++) {
                    $index = $arrPenCheck[$n];
                    $arrPenIDs[$index] = 1;
                }

                if (count($arrCompanyID) > 0) {
                    $resCar = Car::find()->where(['company_id' => $arrCompanyID])->andWhere(['is_penalty' => 1])->andWhere(['not', ['cert' => null]])->select('id, company_id, number, cert, mark_id, type_id')->orderBy('company_id')->asArray()->all();

                    for ($n = 0; $n < count($arrPenaltyValue); $n++) {

                        for ($j = 0; $j < count($resCar); $j++) {

                            if((mb_strtoupper(str_replace(' ', '', $arrPenaltyValue[$n]['carReg']), 'UTF-8') == $resCar[$j]['number']) && ($arrPenaltyValue[$n]['carCert'] == $resCar[$j]['cert'])) {

                                $index = $arrPenaltyValue[$n]['id'];

                                if(isset($arrPenIDs[$index]) == false) {

                                    // Записываем информацию о штрафах в базу
                                    $newPenalty = new PenaltyInfo();

                                    $newPenalty->pen_id = $arrPenaltyValue[$n]['id'];
                                    $newPenalty->car_id = $resCar[$j]['id'];
                                    $newPenalty->company_id = $resCar[$j]['company_id'];

                                    $newPenalty->description = $arrPenaltyValue[$n]['description'];
                                    $newPenalty->postNumber = $arrPenaltyValue[$n]['postNumber'];
                                    $newPenalty->postedAt = $arrPenaltyValue[$n]['postedAt'];
                                    $newPenalty->violationAt = $arrPenaltyValue[$n]['violationAt'];
                                    $newPenalty->amount = $arrPenaltyValue[$n]['amount'];
                                    $newPenalty->totalAmount = $arrPenaltyValue[$n]['totalAmount'];
                                    $newPenalty->isDiscount = ($arrPenaltyValue[$n]['isDiscount'] > 0) ? $arrPenaltyValue[$n]['isDiscount'] : 0;
                                    $newPenalty->discountDate = $arrPenaltyValue[$n]['discountDate'];
                                    $newPenalty->discountSize = (String)$arrPenaltyValue[$n]['discountSize'];
                                    $newPenalty->isExpired = ($arrPenaltyValue[$n]['isExpired'] > 0) ? $arrPenaltyValue[$n]['isExpired'] : 0;
                                    $newPenalty->penaltyDate = $arrPenaltyValue[$n]['penaltyDate'];
                                    $newPenalty->isPaid = ($arrPenaltyValue[$n]['isPaid'] > 0) ? $arrPenaltyValue[$n]['isPaid'] : 0;
                                    $newPenalty->docType = $arrPenaltyValue[$n]['docType'];
                                    $newPenalty->docNumber = $arrPenaltyValue[$n]['docNumber'];
                                    $newPenalty->enablePics = ($arrPenaltyValue[$n]['enablePics'] > 0) ? $arrPenaltyValue[$n]['enablePics'] : 0;

                                    // Изображения
                                    $stringPics = "";
                                    if (($arrPenaltyValue[$n]['enablePics'] == 1) && (count($arrPenaltyValue[$n]['pics']) > 0)) {

                                        $picsArr = $arrPenaltyValue[$n]['pics'];

                                        for ($x = 0; $x < count($picsArr); $x++) {

                                            if ($x == 0) {
                                                $stringPics .= $picsArr[$x]['url'];
                                            } else {
                                                $stringPics .= ', ' . $picsArr[$x]['url'];
                                            }

                                        }

                                    }

                                    $newPenalty->pics = $stringPics;

                                    // Создание акта
                                    if($newPenalty->isPaid == 0) {
                                        $modelAct = new Act();
                                        $modelAct->service_type = Company::TYPE_PENALTY;
                                        $modelAct->partner_id = 80;

                                        $createParams = [];
                                        $createParams['Act']['time_str'] = date("d-m-Y", strtotime($newPenalty->postedAt));
                                        $createParams['Act']['car_number'] = $resCar[$j]['number'];
                                        $createParams['Act']['extra_car_number'] = "";
                                        $createParams['Act']['mark_id'] = $resCar[$j]['mark_id'];
                                        $createParams['Act']['type_id'] = $resCar[$j]['type_id'];
                                        $createParams['Act']['serviceList'] = [0 => ['description' => $newPenalty->description, 'amount' => 1, 'price' => $newPenalty->totalAmount]];

                                        if ($modelAct->load($createParams)) {

                                            if ($modelAct->save()) {
                                                $newPenalty->act_id = $modelAct->id;

                                                if ($newPenalty->save()) {
                                                    $numCreate++;
                                                }
                                            }

                                        }
                                    }
                                    // Создание акта

                                }

                            }

                        }

                    }

                }

            }

            return $numCreate;
        }
    }

    // Cron для для обновления штрафов
    public function actionPenaltyupdate()
    {

        // Рассылка 2 раза в неделю для партреров
        if (isset(Yii::$app->user->identity->id)) {
            return $this->redirect('/');
        } else {

            $numCreate = 0;

            $resCompany = Company::find()->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->where(['company.use_penalty' => 1])->andWhere(['not', ['company_info.inn' => null]])->select('company.use_penalty, company.id, company_info.inn')->orderBy('company.id')->asArray()->all();

            if(count($resCompany) > 0) {

                // Штрафы
                $modelPenalty = new Penalty();
                $modelPenalty->createToken();

                // Получаем токен
                $token = $modelPenalty->createToken();
                $resToken = json_decode($token[1], true);

                // Сохраняем полученный токен
                $modelPenalty->setParams(['token' => $resToken['token']]);

                $arrCompanyID = [];
                $arrPenaltyValue = [];
                $arrPenIDs = [];

                for ($i = 0; $i < count($resCompany); $i++) {
                    if (mb_strlen($resCompany[$i]['inn']) > 3) {
                        $arrCompanyID[] = $resCompany[$i]['id'];

                        // Проверка штрафа
                        $resPenalty = $modelPenalty->getClientFines($resCompany[$i]['id'] . '@mtransservice.ru');
                        $arrPenalty = json_decode($resPenalty[1], true);

                        if(isset($arrPenalty['fines'])) {
                            if(count($arrPenalty['fines']) > 0) {

                                $arrPenCont = $arrPenalty['fines'];

                                // Записываем штрафы
                                for ($z = 0; $z < count($arrPenCont); $z++) {
                                    $arrPenaltyValue[$z]['id'] = $arrPenCont[$z]['id'];
                                    $arrPenaltyValue[$z]['carCert'] = $arrPenCont[$z]['carCert'];
                                    $arrPenaltyValue[$z]['carReg'] = $arrPenCont[$z]['carReg'];

                                    if((!isset($arrPenCont[$z]['koapText'])) && (!isset($arrPenCont[$z]['name']))) {
                                        $arrPenaltyValue[$z]['description'] = $arrPenCont[$z]['wireUsername'];
                                    } else {
                                        $arrPenaltyValue[$z]['description'] = isset($arrPenCont[$z]['koapText']) ? $arrPenCont[$z]['koapText'] : $arrPenCont[$z]['name'];
                                    }

                                    $arrPenaltyValue[$z]['postNumber'] = $arrPenCont[$z]['postNumber'];
                                    $arrPenaltyValue[$z]['postedAt'] = $arrPenCont[$z]['postedAt'];
                                    $arrPenaltyValue[$z]['violationAt'] = isset($arrPenCont[$z]['violationAt']) ? $arrPenCont[$z]['violationAt'] : $arrPenCont[$z]['postedAt'];
                                    $arrPenaltyValue[$z]['amount'] = $arrPenCont[$z]['amount'];
                                    $arrPenaltyValue[$z]['totalAmount'] = $arrPenCont[$z]['totalAmount'];
                                    $arrPenaltyValue[$z]['isDiscount'] = $arrPenCont[$z]['isDiscount'];
                                    $arrPenaltyValue[$z]['discountDate'] = $arrPenCont[$z]['discountDate'];
                                    $arrPenaltyValue[$z]['discountSize'] = $arrPenCont[$z]['discountSize'];
                                    $arrPenaltyValue[$z]['isExpired'] = $arrPenCont[$z]['isExpired'];
                                    $arrPenaltyValue[$z]['penaltyDate'] = $arrPenCont[$z]['penaltyDate'];
                                    $arrPenaltyValue[$z]['isPaid'] = $arrPenCont[$z]['isPaid'];
                                    $arrPenaltyValue[$z]['docType'] = $arrPenCont[$z]['docType'];
                                    $arrPenaltyValue[$z]['docNumber'] = $arrPenCont[$z]['docNumber'];
                                    $arrPenaltyValue[$z]['enablePics'] = $arrPenCont[$z]['enablePics'];
                                    $arrPenaltyValue[$z]['pics'] = $arrPenCont[$z]['pics'];

                                    $arrPenIDs[] = $arrPenCont[$z]['id'];

                                }

                            }
                        }

                    }
                }

                $arrPenCheck = PenaltyInfo::find()->where(['pen_id' => $arrPenIDs])->select('pen_id')->asArray()->column();

                $arrPenIDs = [];
                for ($n = 0; $n < count($arrPenCheck); $n++) {
                    $index = $arrPenCheck[$n];
                    $arrPenIDs[$index] = 1;
                }

                if (count($arrCompanyID) > 0) {
                    $resCar = Car::find()->where(['company_id' => $arrCompanyID])->andWhere(['is_penalty' => 1])->andWhere(['not', ['cert' => null]])->select('id, company_id, number, cert, mark_id, type_id')->orderBy('company_id')->asArray()->all();

                    for ($n = 0; $n < count($arrPenaltyValue); $n++) {

                        for ($j = 0; $j < count($resCar); $j++) {

                            if((mb_strtoupper(str_replace(' ', '', $arrPenaltyValue[$n]['carReg']), 'UTF-8') == $resCar[$j]['number']) && ($arrPenaltyValue[$n]['carCert'] == $resCar[$j]['cert'])) {

                                $index = $arrPenaltyValue[$n]['id'];

                                if(isset($arrPenIDs[$index]) == true) {

                                    // Обновление штрафа
                                    $localPenaltyModel = PenaltyInfo::findOne(['pen_id' => $index]);
                                    $localPenaltyModel->isPaid = ($arrPenaltyValue[$n]['isPaid'] > 0) ? $arrPenaltyValue[$n]['isPaid'] : 0;

                                    $localPenaltyModel->isDiscount = ($arrPenaltyValue[$n]['isDiscount'] > 0) ? $arrPenaltyValue[$n]['isDiscount'] : 0;
                                    $localPenaltyModel->discountDate = ($arrPenaltyValue[$n]['discountDate'] > 0) ? $arrPenaltyValue[$n]['discountDate'] : 0;
                                    $localPenaltyModel->discountSize = (String)$arrPenaltyValue[$n]['discountSize'];
                                    $localPenaltyModel->docNumber = $arrPenaltyValue[$n]['docNumber'];
                                    $localPenaltyModel->enablePics = ($arrPenaltyValue[$n]['enablePics'] > 0) ? $arrPenaltyValue[$n]['enablePics'] : 0;

                                    // Изображения
                                    $stringPics = "";
                                    if (($arrPenaltyValue[$n]['enablePics'] == 1) && (count($arrPenaltyValue[$n]['pics']) > 0)) {

                                        $picsArr = $arrPenaltyValue[$n]['pics'];

                                        for ($x = 0; $x < count($picsArr); $x++) {

                                            if ($x == 0) {
                                                $stringPics .= $picsArr[$x]['url'];
                                            } else {
                                                $stringPics .= ', ' . $picsArr[$x]['url'];
                                            }

                                        }

                                    }

                                    $localPenaltyModel->pics = $stringPics;

                                    if($localPenaltyModel->save()) {
                                        $numCreate++;
                                    }

                                }

                            }

                        }

                    }

                }

            }

            return $numCreate;

        }
    }

}
