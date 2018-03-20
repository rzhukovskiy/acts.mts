<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\components\ArrayHelper;
use common\components\DateHelper;
use common\models\Company;
use common\models\MonthlyAct;
use common\models\search\MonthlyActSearch;
use common\models\Service;
use common\models\TrackerInfo;
use common\models\User;
use common\models\ActData;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use PHPExcel_Worksheet;
use yii\bootstrap\Html;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;

class MonthlyActController extends Controller
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
                        'actions' => ['delete', 'delete-image', 'ajax-act-status', 'ajax-payment-status', 'archive', 'searchact', 'getcomments', 'gettrack', 'gettrackerlist', 'debt-excel'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['update', 'detail', 'list', 'archive', 'searchact', 'getcomments', 'gettrack', 'gettrackerlist', 'debt-excel'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_WATCHER, User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['detail', 'list'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ACCOUNT],
                    ],
                ],
            ],
        ];
    }


    public function actionList($type, $company = 0)
    {
        $searchModel = new MonthlyActSearch();
        $searchModel->type_id = $type;

        $params = Yii::$app->request->queryParams;

        if(isset($params['filterStatus'])) {

            if($params['filterStatus'] == 1) {
                $searchModel->payment_status = 0;
            } else if($params['filterStatus'] == 2) {
                $searchModel->act_status = 0;
            } else if($params['filterStatus'] == 3) {
                $searchModel->payment_status = 15;
            } else if($params['filterStatus'] == 4) {
                $searchModel->act_status = 5;
            } else if($params['filterStatus'] == 5) {
                $searchModel->act_status = 4;
            } else if($params['filterStatus'] == 6) {
                $searchModel->act_status = 2;
            } else if($params['filterStatus'] == 7) {
                $searchModel->act_status = 3;
            } else if($params['filterStatus'] == 8) {
                $searchModel->act_status = 1;
            } else if($params['filterStatus'] == 10) {
                $searchModel->act_status = 6;
            } else if($params['filterStatus'] == 9) {
                $searchModel->act_status = 7;
            }

        }

        $dataProvider = $searchModel->search($params);
        //Запоминаем
        $this->setSessionDate($searchModel->act_date);

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Service::$listType;
        } else {
            $listType = Yii::$app->user->identity->getAllServiceType(Company::STATUS_ACTIVE);
        }

        $currentUser = Yii::$app->user->identity;
        if ($currentUser && $currentUser->role == User::ROLE_ADMIN) {
            $admin = true;
        } else {
            $admin = false;
        }

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'company'      => $company,
                'listType'     => $listType,
                'admin'        => $admin,
            ]);
    }

    public function actionArchive($type, $company = 0)
    {
        $searchModel = new MonthlyActSearch();

        if((!($type == 1)) && (!($type == -1)) && (!($type == -99))) {
            $searchModel->type_id = $type;
        }

        // $searchModel->scenario = 'statistic_filter';
        //$searchModel->period = Yii::$app->request->get('period');

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то
        if(!isset($params['MonthlyActSearch']['dateFrom'])) {
            $params['MonthlyActSearch']['dateFrom'] = date("Y", strtotime("-1 year")) . '-12-31T21:00:00.000Z';
            $searchModel->dateFrom = $params['MonthlyActSearch']['dateFrom'];
        }

        if(!isset($params['MonthlyActSearch']['dateTo'])) {
            $params['MonthlyActSearch']['dateTo'] = date("Y") . '-12-31T21:00:00.000Z';
            $searchModel->dateTo = $params['MonthlyActSearch']['dateTo'];
        }
        // Если не выбран период то

        $dataProvider = $searchModel->searchArchive($params);
        $dataProviderDuble = $searchModel->searchArchiveDuble($params);

        $models = $dataProvider->getModels();
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Service::$listType;
        } else {
            $listType = Yii::$app->user->identity->getAllServiceType(Company::STATUS_ACTIVE);
        }

        return $this->render('archive/list',
            [
                'dataProvider' => $dataProvider,
                'dataProviderDuble' => $dataProviderDuble,
                'searchModel'  => $searchModel,
                'totalProfit'  => $totalProfit,
                'listType'     => $listType,
                'company'      => $company,
                'type'         => $type
            ]);
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $model->image = yii\web\UploadedFile::getInstances($model, 'image');
            $model->uploadImage();
            if ($model->save()) {
                $redirect = [
                    'list',
                    'type'                       => $model->type_id,
                    'company'                    => !$model->is_partner,
                    'MonthlyActSearch[act_date]' => $this->getSessionDate()
                ];

                return $this->redirect($redirect);
            }
        }

        return $this->render('update',
            [
                'model' => $this->findModel($id)
            ]);

    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'detail';

        $arrPost = Yii::$app->request->post();

        if ($model->load($arrPost) && $model->save()) {

            // Добавляем статус в отслеживание для ускорения загрузки
            $post_number = '';

            switch ($model->type_id) {
                case Service::TYPE_WASH:
                    $post_number = $arrPost['WashMonthlyAct']['post_number'];
                    break;
                case Service::TYPE_SERVICE:
                    $post_number = $arrPost['ServiceMonthlyAct']['post_number'];
                    break;
                case Service::TYPE_TIRES:
                    $post_number = $arrPost['TiresMonthlyAct']['post_number'];
                    break;
                case Service::TYPE_DISINFECT:
                    $post_number = $arrPost['DisinfectMonthlyAct']['post_number'];
                    break;
                case Service::TYPE_PARKING:
                    $post_number = $arrPost['ParkingMonthlyAct']['post_number'];
                    break;
                case Service::TYPE_PENALTY:
                    $post_number = $arrPost['PenaltyMonthlyAct']['post_number'];
                    break;
            }

            if(mb_strlen($post_number) > 2) {

                $oldResTrack = TrackerInfo::findOne(['type' => 1, 'second_id' => $model->id, 'number' => $post_number]);
                $doTrack = true;

                if (isset($oldResTrack)) {
                    $doTrack = false;
                }

                if ($doTrack) {

                    $api_track_link = 'https://gdeposylka.ru';

                    $ResTrack = curl_init($api_track_link . '/api/v4/tracker/detect/' . $post_number);
                    curl_setopt_array($ResTrack, [
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Authorization-Token: 23acf42e4453471bac36d514e2370bc6d1b24bf5e1708eeedd7c2761f388204a8bfc442b5676d931']]
                    );
                    $result = json_decode(curl_exec($ResTrack), TRUE);
                    curl_close($ResTrack);

                    if (isset($result['result'])) {
                        if ($result['result'] == 'success') {

                            if (isset($result['data'][0]['tracker_url'])) {
                                $tracker_url = $result['data'][0]['tracker_url'];

                                $ResTrack = curl_init($api_track_link . $tracker_url);
                                curl_setopt_array($ResTrack, [
                                        CURLOPT_RETURNTRANSFER => true,
                                        CURLOPT_FOLLOWLOCATION => true,
                                        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Authorization-Token: 23acf42e4453471bac36d514e2370bc6d1b24bf5e1708eeedd7c2761f388204a8bfc442b5676d931']]
                                );
                                $result = json_decode(curl_exec($ResTrack), TRUE);
                                curl_close($ResTrack);

                                if (isset($result['result'])) {
                                    if ($result['result'] == 'success') {

                                        if(isset($result['data']['checkpoints'])) {

                                            $checkpoints = $result['data']['checkpoints'];

                                            if(count($checkpoints) > 0) {

                                                $haveNeedTrack = 0;

                                                for ($j = 0; $j < count($checkpoints); $j++) {

                                                    if((isset($checkpoints[$j]['status_name'])) && (isset($checkpoints[$j]['status_raw']))) {

                                                        if (($checkpoints[$j]['status_name'] == 'Прибыла в пункт назначения') && ($checkpoints[$j]['status_raw'] == 'Обработка - Прибыло в место вручения') && ($haveNeedTrack == 0)) {
                                                            $haveNeedTrack = 1;
                                                        }

                                                        if (($checkpoints[$j]['status_name'] == 'Посылка доставлена') && ($checkpoints[$j]['status_raw'] == 'Вручение - Вручение адресату')) {
                                                            $haveNeedTrack = 2;
                                                        }

                                                    }

                                                }

                                                $newTrackerInfo = new TrackerInfo();
                                                $newTrackerInfo->value = $haveNeedTrack;
                                                $newTrackerInfo->type = 1;
                                                $newTrackerInfo->second_id = $model->id;
                                                $newTrackerInfo->number = $post_number;
                                                $newTrackerInfo->save();

                                            }

                                        }

                                    }
                                }

                            }

                        }
                    }

                }

            }
            // Добавляем статус в отслеживание для ускорения загрузки

            $redirect = [
                'list',
                'type'                       => $model->type_id,
                'company'                    => !$model->is_partner,
                'MonthlyActSearch[act_date]' => $this->getSessionDate()
            ];

            return $this->redirect($redirect);
        }

        return $this->render('detail',
            [
                'model' => $this->findModel($id)
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

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionSearchact()
    {

        if(Yii::$app->request->post('number')) {
            if(mb_strlen(Yii::$app->request->post('number')) > 0) {

                $number = Yii::$app->request->post('number');
                $resLink = '/monthly-act/list?type=';

                if(is_numeric($number[0])) {
                    $arrActData = ActData::find()->where('(number LIKE "_' . $number . '") OR (number = "DD' . $number . '")')->select('type, company, period, number')->limit(1)->all();

                    if(count($arrActData) > 0) {

                        if((isset($arrActData[0]['type'])) && (isset($arrActData[0]['company'])) && (isset($arrActData[0]['period']))) {

                        $resLink .= $arrActData[0]['type'];

                        if($arrActData[0]['company'] == 1) {
                            $resLink .= '&company=1';
                        }

                        $period = explode('-', $arrActData[0]['period']);

                        if($period[0][0] == 0) {
                            $period = mb_substr($arrActData[0]['period'], 1);
                        } else {
                            $period = $arrActData[0]['period'];
                        }

                        $resLink .= '&MonthlyActSearch%5Bact_date%5D=' . $period;
                        $resLink .= '&search_number=' . $arrActData[0]['number'];
                        echo json_encode(['success' => 'true', 'link' => $resLink]);

                        } else {
                            echo json_encode(['success' => 'false']);
                        }

                    } else {
                        echo json_encode(['success' => 'false']);
                    }

                } else {

                    $arrActData = ActData::find()->where(['number' => $number])->select('type, company, period, number')->limit(1)->all();

                    if(count($arrActData) > 0) {

                        if((isset($arrActData[0]['type'])) && (isset($arrActData[0]['company'])) && (isset($arrActData[0]['period'])) && (isset($arrActData[0]['number']))) {

                        $resLink .= $arrActData[0]['type'];

                        if($arrActData[0]['company'] == 1) {
                            $resLink .= '&company=1';
                        }

                        $period = explode('-', $arrActData[0]['period']);

                        if($period[0][0] == 0) {
                            $period = mb_substr($arrActData[0]['period'], 1);
                        } else {
                            $period = $arrActData[0]['period'];
                        }

                        $resLink .= '&MonthlyActSearch%5Bact_date%5D=' . $period;
                        $resLink .= '&search_number=' . $arrActData[0]['number'];
                        echo json_encode(['success' => 'true', 'link' => $resLink]);

                        } else {
                            echo json_encode(['success' => 'false']);
                        }

                    } else {
                        echo json_encode(['success' => 'false']);
                    }

                }

            } else {
                echo json_encode(['success' => 'false']);
            }
        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionGetcomments()
    {

        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');
            $resComm = '';

            $model = MonthlyAct::findOne(['id' => $id]);

            $resComm .= "<u style='color:#757575;'>Комментарии к акту:</u> " . $model->act_comment . "<br />";
            //$resComm .= "<u style='color:#757575;'>Дата отправления акта по почте:</u> " . $model->act_send_date . "<br />";
            //$resComm .= "<u style='color:#757575;'>Дата получения акта клиентом:</u> " . $model->act_client_get_date . "<br /><br />";
            $resComm .= "<u style='color:#757575;'>Комментарии к оплате:</u> " . $model->payment_comment . "<br />";
            //$resComm .= "<u style='color:#757575;'>Дата получения акта нами:</u> " . $model->act_we_get_date . "<br />";
            //$resComm .= "<u style='color:#757575;'>Дата предполагаемой оплаты:</u> " . $model->payment_estimate_date . "<br />";

            echo json_encode(['success' => 'true', 'comment' => $resComm]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    /**
     * @param $id
     * @param $url
     * @return yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteImage($id, $url)
    {
        $model = $this->findModel($id);
        $model->deleteImage($url);
        $model->save();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionAjaxPaymentStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->payment_status = $status;
        $model->save();

        return MonthlyAct::colorForPaymentStatus($model->payment_status);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionAjaxActStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->act_status = $status;
        $model->save();

        $pass['color'] = MonthlyAct::colorForStatus($model->act_status);
        $pass['value'] = $status;
        return Json::encode($pass);
    }

    // Отчет о должниках - Excel
    public function actionDebtExcel()
    {

        // Формируем Excel файл
        $ArrDebt = [];
        $dateFrom = date('Y-m-t', strtotime("-6 month")) . 'T21:00:00.000Z';
        $dateTo = date('Y-m-t') . 'T21:00:00.000Z';

        $profitRes = \common\models\Act::find()->innerJoin('monthly_act', 'monthly_act.client_id = act.client_id AND monthly_act.type_id = act.service_type AND (monthly_act.act_date = DATE_FORMAT(from_unixtime(act.served_at), "%Y-%m-00"))')->innerJoin('company', 'company.id = monthly_act.client_id')->where(['AND', ['monthly_act.payment_status' => 0], [">", "act.income", 0], ['between', 'act_date', $dateFrom, $dateTo]])->andWhere(['OR', ['AND', ['monthly_act.type_id' => 5], ['monthly_act.service_id' => 4]], ['!=', 'monthly_act.type_id', 5]])->andWhere(['OR', ['AND', ['!=', 'monthly_act.type_id', 3], ['!=', 'monthly_act.act_date', (date("Y-m") . '-00')]], ['AND', ['monthly_act.type_id' => 3], '`act`.`id`=`monthly_act`.`act_id`']])->andWhere(['!=', 'monthly_act.type_id', Company::TYPE_PENALTY])->select('SUM(act.income) as profit, company.name as name, monthly_act.client_id as id, monthly_act.id as mid, monthly_act.act_date as date, monthly_act.type_id as type')->groupBy('monthly_act.id')->indexBy('mid')->orderBy('monthly_act.client_id, monthly_act.act_date, monthly_act.type_id')->asArray()->all();

        if(count($profitRes) > 0) {
            foreach ($profitRes as $key => $value) {

                $arrDate = $profitRes[$key];
                $indexD = $arrDate['date'];
                $index = $arrDate['id'];
                $indexT = $arrDate['type'];

                $ArrDebt[$index][$indexT][$indexD][0] = $arrDate['name'];
                $ArrDebt[$index][$indexT][$indexD][1] = $arrDate['profit'];
            }
        }

        // дез
        $profitResDes = \common\models\Act::find()->innerJoin('monthly_act', 'monthly_act.client_id = act.client_id AND monthly_act.type_id = act.service_type AND (monthly_act.act_date = DATE_FORMAT(from_unixtime(act.served_at), "%Y-%m-00"))')->innerJoin('act_scope', 'act_scope.act_id = act.id AND act_scope.company_id = act.client_id AND act_scope.service_id = 5')->innerJoin('company', 'company.id = monthly_act.client_id')->where(['AND', ['monthly_act.payment_status' => 0], ['monthly_act.type_id' => 5], [">", "act.income", 0], ['between', 'act_date', $dateFrom, $dateTo]])->andWhere(['OR', ['AND', ['!=', 'monthly_act.type_id', 3], ['!=', 'monthly_act.act_date', (date("Y-m") . '-00')]], ['AND', ['monthly_act.type_id' => 3], '`act`.`id`=`monthly_act`.`act_id`']])->select('SUM(act.income) as profit, company.name as name, monthly_act.client_id as id, monthly_act.id as mid, monthly_act.act_date as date, monthly_act.type_id as type')->groupBy('monthly_act.id')->indexBy('mid')->orderBy('monthly_act.client_id, monthly_act.act_date, monthly_act.type_id')->asArray()->all();

        if(count($profitResDes) > 0) {
            foreach ($profitResDes as $key => $value) {

                $arrDate = $profitResDes[$key];
                $indexD = $arrDate['date'];
                $index = $arrDate['id'];
                $indexT = $arrDate['type'];

                if((isset($ArrDebt[$index][$indexT][$indexD][0])) && (isset($ArrDebt[$index][$indexT][$indexD][1]))) {
                    $ArrDebt[$index][$indexT][$indexD][1] += $arrDate['profit'];
                } else {
                    $ArrDebt[$index][$indexT][$indexD][0] = $arrDate['name'];
                    $ArrDebt[$index][$indexT][$indexD][1] = $arrDate['profit'];
                }

            }
        }

        $objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Creating a workbook
        $objPHPExcel->getProperties()->setCreator('Mtransservice');
        $objPHPExcel->getProperties()->setTitle('Отчет по должникам');
        $objPHPExcel->getProperties()->setSubject('Отчет по должникам');
        $objPHPExcel->getProperties()->setDescription('Должники за последние 5 месяцев');
        $objPHPExcel->getProperties()->setCategory('');
        $objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $debtWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Отчет по должникам');
        $objPHPExcel->addSheet($debtWorkSheet);

        $debtWorkSheet->getPageMargins()->setTop(2);
        $debtWorkSheet->getPageMargins()->setLeft(0.5);

        $row = 1;

        $resText = '<b style="color:#069;">Должники за последние 5 месяцев:</b><br />';
        $arrTypes = Company::$listType;
        $i = 1;
        $summ = 0;

        $old_id = 0;
        $old_type = 0;
        $summCompany = 0;

        $tmpRow = 0;
        $indexTypes[0] = 0;
        $indexTypes[1] = 0;
        $indexTypes[2] = 0;
        $indexTypes[3] = 0;
        $indexTypes[4] = 0;
        $indexTypes[5] = 0;

        $sumTypes[0] = 0;
        $sumTypes[1] = 0;
        $sumTypes[2] = 0;
        $sumTypes[3] = 0;
        $sumTypes[4] = 0;
        $sumTypes[5] = 0;

        foreach ($ArrDebt as $id => $value) {
            foreach ($value as $idType => $valueT) {
                foreach ($valueT as $keyD => $valueD) {

                    $new_id = $id;
                    $arrPeriod = explode('-', $keyD);
                    $in = (int)$arrPeriod[1];
                    $showDate = DateHelper::$months[$in][0] . ' ' . $arrPeriod[0];

                    if ($old_id != $new_id) {

                        if($i > 1) {

                            $row = $row + (max($indexTypes) - $row);

                            // Сумма итого для каждого типа
                            $debtWorkSheet->getRowDimension($row)->setRowHeight(23);

                            $debtWorkSheet->mergeCells('B' . $row . ':C' . $row);
                            $debtWorkSheet->mergeCells('E' . $row . ':F' . $row);
                            $debtWorkSheet->mergeCells('H' . $row . ':I' . $row);
                            $debtWorkSheet->mergeCells('K' . $row . ':L' . $row);

                            $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                )
                            ));

                            $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                    'font' => [
                                        'size' => 14,
                                        'name' => 'Times New Roman'
                                    ],
                                ]
                            );

                            $debtWorkSheet->setCellValue('A' . $row, "Итого:");
                            $debtWorkSheet->setCellValue('D' . $row, "Итого:");
                            $debtWorkSheet->setCellValue('G' . $row, "Итого:");
                            $debtWorkSheet->setCellValue('J' . $row, "Итого:");


                            $debtWorkSheet->setCellValue('B' . $row, $sumTypes[2] . "₽");
                            $debtWorkSheet->setCellValue('E' . $row, $sumTypes[4] . "₽");
                            $debtWorkSheet->setCellValue('H' . $row, $sumTypes[5] . "₽");
                            $debtWorkSheet->setCellValue('K' . $row, $sumTypes[3] . "₽");

                            $row++;
                            // Сумма итого для каждого типа

                            // Сумма всего
                            $debtWorkSheet->mergeCells('A' . $row . ':L' . $row);

                            $debtWorkSheet->getRowDimension($row)->setRowHeight(23);

                            $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                )
                            ));

                            $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                    'font' => [
                                        'size' => 14,
                                        'name' => 'Times New Roman'
                                    ],
                                ]
                            );

                            $debtWorkSheet->setCellValue('A' . $row, "Всего: " . $summCompany . "₽");
                            $summCompany = 0;
                            $row++;
                            // Сумма всего

                            $debtWorkSheet->getRowDimension($row)->setRowHeight(22);
                            $debtWorkSheet->mergeCells('A' . $row . ':L' . $row);
                            $row++;
                        }

                        //headers
                        $debtWorkSheet->mergeCells('A' . $row . ':L' . $row);
                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            )
                        ));

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'font' => [
                                    'size' => 18,
                                    'name' => 'Times New Roman'
                                ],
                            ]
                        );

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->getFill()->applyFromArray([
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => [
                                    'rgb' => 'e9ef43'
                                ]
                            ]
                        );

                        $debtWorkSheet->getRowDimension($row)->setRowHeight(25);

                        $debtWorkSheet->setCellValue('A' . $row, $ArrDebt[$id][$idType][$keyD][0]);

                        $row++;
                        //headers

                        // Types
                        $debtWorkSheet->getRowDimension($row)->setRowHeight(25);

                        $debtWorkSheet->mergeCells('A' . $row . ':C' . $row);
                        $debtWorkSheet->mergeCells('D' . $row . ':F' . $row);
                        $debtWorkSheet->mergeCells('G' . $row . ':I' . $row);
                        $debtWorkSheet->mergeCells('J' . $row . ':L' . $row);

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            )
                        ));

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'font' => [
                                    'size' => 18,
                                    'name' => 'Times New Roman'
                                ],
                            ]
                        );

                        $debtWorkSheet->getStyle('A' . $row . ':C' . $row)->getFill()->applyFromArray([
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => [
                                    'rgb' => '93d65c'
                                ]
                            ]
                        );

                        $debtWorkSheet->getStyle('D' . $row . ':F' . $row)->getFill()->applyFromArray([
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => [
                                    'rgb' => '4cb2e5'
                                ]
                            ]
                        );

                        $debtWorkSheet->getStyle('G' . $row . ':I' . $row)->getFill()->applyFromArray([
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => [
                                    'rgb' => 'e5b24c'
                                ]
                            ]
                        );

                        $debtWorkSheet->getStyle('J' . $row . ':L' . $row)->getFill()->applyFromArray([
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'startcolor' => [
                                    'rgb' => 'b01717'
                                ]
                            ]
                        );

                        $debtWorkSheet->setCellValue('A' . $row, "Мойка");
                        $debtWorkSheet->setCellValue('D' . $row, "Шиномонтаж");
                        $debtWorkSheet->setCellValue('G' . $row, "Дезинфекция");
                        $debtWorkSheet->setCellValue('J' . $row, "Сервис");

                        $row++;
                        // Types

                        // Titels
                        $debtWorkSheet->getRowDimension($row)->setRowHeight(25);

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            )
                        ));

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'font' => [
                                    'size' => 18,
                                    'name' => 'Times New Roman'
                                ],
                            ]
                        );

                        $debtWorkSheet->setCellValue('A' . $row, "Период");
                        $debtWorkSheet->setCellValue('B' . $row, "Сумма");
                        $debtWorkSheet->setCellValue('C' . $row, "Комментарий");
                        $debtWorkSheet->setCellValue('D' . $row, "Период");
                        $debtWorkSheet->setCellValue('E' . $row, "Сумма");
                        $debtWorkSheet->setCellValue('F' . $row, "Комментарий");
                        $debtWorkSheet->setCellValue('G' . $row, "Период");
                        $debtWorkSheet->setCellValue('H' . $row, "Сумма");
                        $debtWorkSheet->setCellValue('I' . $row, "Комментарий");
                        $debtWorkSheet->setCellValue('J' . $row, "Период");
                        $debtWorkSheet->setCellValue('K' . $row, "Сумма");
                        $debtWorkSheet->setCellValue('L' . $row, "Комментарий");


                        $debtWorkSheet->getColumnDimension('A')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('B')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('C')->setWidth(23);
                        $debtWorkSheet->getColumnDimension('D')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('E')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('F')->setWidth(23);
                        $debtWorkSheet->getColumnDimension('G')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('H')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('I')->setWidth(23);
                        $debtWorkSheet->getColumnDimension('J')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('K')->setWidth(15);
                        $debtWorkSheet->getColumnDimension('L')->setWidth(23);

                        $row++;
                        // Titels

                        // Values
                        $debtWorkSheet->getRowDimension($row)->setRowHeight(25);

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            )
                        ));

                        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                                'font' => [
                                    'size' => 14,
                                    'name' => 'Times New Roman'
                                ],
                            ]
                        );

                        switch ($idType) {
                            case 2:
                                $debtWorkSheet->setCellValue('A' . $row, $showDate);
                                $debtWorkSheet->setCellValue('B' . $row, $ArrDebt[$id][$idType][$keyD][1]);
                                $debtWorkSheet->setCellValue('C' . $row, "");

                                $indexTypes[2] = ($row + 1);
                                $indexTypes[3] = $row;
                                $indexTypes[4] = $row;
                                $indexTypes[5] = $row;

                                break;
                            case 3:
                                $debtWorkSheet->setCellValue('J' . $row, $showDate);
                                $debtWorkSheet->setCellValue('K' . $row, $ArrDebt[$id][$idType][$keyD][1]);
                                $debtWorkSheet->setCellValue('L' . $row, "");

                                $indexTypes[2] = $row;
                                $indexTypes[3] = ($row + 1);
                                $indexTypes[4] = $row;
                                $indexTypes[5] = $row;

                                break;
                            case 4:
                                $debtWorkSheet->setCellValue('D' . $row, $showDate);
                                $debtWorkSheet->setCellValue('E' . $row, $ArrDebt[$id][$idType][$keyD][1]);
                                $debtWorkSheet->setCellValue('F' . $row, "");

                                $indexTypes[2] = $row;
                                $indexTypes[3] = $row;
                                $indexTypes[4] = ($row + 1);
                                $indexTypes[5] = $row;

                                break;
                            case 5:
                                $debtWorkSheet->setCellValue('G' . $row, $showDate);
                                $debtWorkSheet->setCellValue('H' . $row, $ArrDebt[$id][$idType][$keyD][1]);
                                $debtWorkSheet->setCellValue('I' . $row, "");

                                $indexTypes[2] = $row;
                                $indexTypes[3] = $row;
                                $indexTypes[4] = $row;
                                $indexTypes[5] = ($row + 1);

                                break;
                            default:
                                break;
                        }
                        // Values

                        $resText .= '<br /><b>' . $ArrDebt[$id][$idType][$keyD][0] . '</b><br />';
                        $resText .= $showDate . ' - ' . $arrTypes[$idType]['ru'] . ' - ' . $ArrDebt[$id][$idType][$keyD][1] . '₽<br />';

                        $sumTypes[0] = 0;
                        $sumTypes[1] = 0;
                        $sumTypes[2] = 0;
                        $sumTypes[3] = 0;
                        $sumTypes[4] = 0;
                        $sumTypes[5] = 0;

                        $old_id = $new_id;
                        $old_type = $idType;

                    } else {

                        // Values

                        $tmpRow = 0;

                        $tmpRow = $indexTypes[$idType];
                        $indexTypes[$idType]++;

                        if($idType != $old_type) {

                            switch ($idType) {
                                case 2:

                                    $debtWorkSheet->setCellValue('A' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('B' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('C' . $tmpRow, "");
                                    break;
                                case 3:

                                    $debtWorkSheet->setCellValue('J' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('K' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('L' . $tmpRow, "");
                                    break;
                                case 4:

                                    $debtWorkSheet->setCellValue('D' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('E' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('F' . $tmpRow, "");
                                    break;
                                case 5:

                                    $debtWorkSheet->setCellValue('G' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('H' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('I' . $tmpRow, "");
                                    break;
                                default:
                                    break;
                            }

                            $debtWorkSheet->getRowDimension($tmpRow)->setRowHeight(25);

                            $debtWorkSheet->getStyle('A' . $tmpRow . ':L' . $tmpRow)->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                )
                            ));

                            $debtWorkSheet->getStyle('A' . $tmpRow . ':L' . $tmpRow)->applyFromArray([
                                    'font' => [
                                        'size' => 14,
                                        'name' => 'Times New Roman'
                                    ],
                                ]
                            );

                            $tmpRow++;

                        } else {

                            $debtWorkSheet->getRowDimension($tmpRow)->setRowHeight(25);

                            $debtWorkSheet->getStyle('A' . $tmpRow . ':L' . $tmpRow)->applyFromArray(array(
                                'alignment' => array(
                                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                                )
                            ));

                            $debtWorkSheet->getStyle('A' . $tmpRow . ':L' . $tmpRow)->applyFromArray([
                                    'font' => [
                                        'size' => 14,
                                        'name' => 'Times New Roman'
                                    ],
                                ]
                            );

                            switch ($idType) {
                                case 2:
                                    $debtWorkSheet->setCellValue('A' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('B' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('C' . $tmpRow, "");
                                    break;
                                case 3:
                                    $debtWorkSheet->setCellValue('J' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('K' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('L' . $tmpRow, "");
                                    break;
                                case 4:
                                    $debtWorkSheet->setCellValue('D' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('E' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('F' . $tmpRow, "");
                                    break;
                                case 5:
                                    $debtWorkSheet->setCellValue('G' . $tmpRow, $showDate);
                                    $debtWorkSheet->setCellValue('H' . $tmpRow, $ArrDebt[$id][$idType][$keyD][1]);
                                    $debtWorkSheet->setCellValue('I' . $tmpRow, "");
                                    break;
                                default:
                                    break;
                            }
                        }
                        // Values

                        $resText .= $showDate . ' - ' . $arrTypes[$idType]['ru'] . ' - ' . $ArrDebt[$id][$idType][$keyD][1] . '₽<br />';

                        $old_type = $idType;

                    }

                    $sumTypes[$idType] += $ArrDebt[$id][$idType][$keyD][1];
                    $summCompany += $ArrDebt[$id][$idType][$keyD][1];
                    $summ += $ArrDebt[$id][$idType][$keyD][1];
                    $i++;

                }
            }
        }

        $objPHPExcel->getActiveSheet()->setSelectedCells('A1');
        $row = $row + (max($indexTypes) - $row);

        // Сумма итого для каждого типа  последней компании
        $debtWorkSheet->getRowDimension($row)->setRowHeight(23);

        $debtWorkSheet->mergeCells('B' . $row . ':C' . $row);
        $debtWorkSheet->mergeCells('E' . $row . ':F' . $row);
        $debtWorkSheet->mergeCells('H' . $row . ':I' . $row);
        $debtWorkSheet->mergeCells('K' . $row . ':L' . $row);

        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        ));

        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                'font' => [
                    'size' => 14,
                    'name' => 'Times New Roman'
                ],
            ]
        );

        $debtWorkSheet->setCellValue('A' . $row, "Итого:");
        $debtWorkSheet->setCellValue('D' . $row, "Итого:");
        $debtWorkSheet->setCellValue('G' . $row, "Итого:");
        $debtWorkSheet->setCellValue('J' . $row, "Итого:");


        $debtWorkSheet->setCellValue('B' . $row, $sumTypes[2] . "₽");
        $debtWorkSheet->setCellValue('E' . $row, $sumTypes[4] . "₽");
        $debtWorkSheet->setCellValue('H' . $row, $sumTypes[5] . "₽");
        $debtWorkSheet->setCellValue('K' . $row, $sumTypes[3] . "₽");

        $row++;
        // Сумма итого для каждого типа  последней компании

        // Сумма всего последней компании

        $debtWorkSheet->mergeCells('A' . $row . ':L' . $row);

        $debtWorkSheet->getRowDimension($row)->setRowHeight(23);

        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        ));

        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                'font' => [
                    'size' => 14,
                    'name' => 'Times New Roman'
                ],
            ]
        );

        $debtWorkSheet->setCellValue('A' . $row, "Всего: " . $summCompany . "₽");
        $summCompany = 0;
        $row++;
        // Сумма всего последней компании

        // Итоговая сумма
        $debtWorkSheet->getRowDimension($row)->setRowHeight(22);
        $debtWorkSheet->mergeCells('A' . $row . ':L' . $row);
        $row++;

        $debtWorkSheet->mergeCells('A' . $row . ':L' . $row);

        $debtWorkSheet->getRowDimension($row)->setRowHeight(23);

        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        ));

        $debtWorkSheet->getStyle('A' . $row . ':L' . $row)->applyFromArray([
                'font' => [
                    'size' => 15,
                    'name' => 'Times New Roman'
                ],
            ]
        );

        $debtWorkSheet->setCellValue('A' . $row, "Итого: " . $summ . "₽");
        // Итоговая сумма

        //saving document
        $pathFile = \Yii::getAlias('@webroot/files/');

        if (!is_dir($pathFile)) {
            mkdir($pathFile, 0755, 1);
        }

        $prefix = trim("Должники");
        $prefix = str_replace(' ', '_', $prefix);

        $filename = $prefix . '.xls';

        $objWriter->save($pathFile . $filename);
        // Формируем Excel файл

        // Выводим скачку файла
        $pathFile = \Yii::getAlias('@webroot/files/' . $filename);

        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Length: ".filesize($pathFile));
        header("Content-Disposition: attachment; filename=" . $filename);
        readfile($pathFile);
        // Выводим скачку файла

    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MonthlyAct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MonthlyAct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return mixed
     */
    protected function getSessionDate()
    {
        return Yii::$app->session->get($this->id . "_act_date");
    }

    /**
     * @param $actDate
     */
    protected function setSessionDate($actDate)
    {
        Yii::$app->session->set($this->id . "_act_date", $actDate);
    }

    protected function removeSessionDate()
    {
        Yii::$app->session->remove($this->id . "_act_date");
    }

    public function actionGettrack($trackID) {

        $ResTrack = json_decode(file_get_contents('https://api.track24.ru/tracking.json.php?apiKey=a5edc8e48db79d1aec6891cb2ebe0cf2&domain=mtransservice.ru&code=' . $trackID));
        $trackCont = 'Нет информации по отслеживанию';

        if(isset($ResTrack->data->events)) {

            $DataTrack = $ResTrack->data->events;
            $trackCont = '';

            for ($iTrack = 0; $iTrack < count($DataTrack); $iTrack++) {
                if (($iTrack + 1) < count($DataTrack)) {
                    $trackCont .= $DataTrack[$iTrack]->operationDateTime . ' - ' . $DataTrack[$iTrack]->operationType . ' - ' . $DataTrack[$iTrack]->operationPlacePostalCode . ', ' . $DataTrack[$iTrack]->operationPlaceName . '<br />';
                } else {
                    $trackCont .= $DataTrack[$iTrack]->operationDateTime . ' - ' . $DataTrack[$iTrack]->operationType . ' - ' . $DataTrack[$iTrack]->operationPlacePostalCode . ', ' . $DataTrack[$iTrack]->operationPlaceName;
                }
            }

            echo json_encode(['success' => 'true', 'trackCont' => $trackCont]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    // Получение списка для модального окна отслеживания
    public static function actionGettrackerlist() {

        if((Yii::$app->request->post('period')) && (Yii::$app->request->post('type'))) {

            $period = Yii::$app->request->post('period');
            $company = 0;

            if(Yii::$app->request->post('company')) {
                $company = Yii::$app->request->post('company');
            }

            $type = Yii::$app->request->post('type');

            // получаем месяц и год из периода
            $periodArr = explode('-', $period);

            $actList = [];

            if ($company) {
                $actList = MonthlyAct::find()->innerJoin('{{%act}}', 'monthly_act.client_id=act.client_id')->innerJoin('{{%company}}', 'company.id=monthly_act.client_id')->innerJoin('{{%company_info}}', 'company_info.company_id=company.id')->where(['AND', ['monthly_act.type_id' => $type], ['act.service_type' => $type], ['monthly_act.is_partner' => 0], ['DATE_FORMAT(`act_date`, "%c-%Y")' => $period], ["MONTH(FROM_UNIXTIME(served_at))" => $periodArr[0]], ["YEAR(FROM_UNIXTIME(served_at))" => $periodArr[1]], ['>', 'act.expense', 0], ['not', ['monthly_act.post_number' => null]], ['not', ['monthly_act.post_number' => '']]])->select('company.name as name, monthly_act.post_number as number, monthly_act.id as id, company_info.email as email')->groupBy('monthly_act.id')->asArray()->all();
            } else {
                $actList = MonthlyAct::find()->innerJoin('{{%act}}', 'monthly_act.client_id=act.partner_id')->innerJoin('{{%company}}', 'company.id=monthly_act.client_id')->innerJoin('{{%company_info}}', 'company_info.company_id=company.id')->where(['AND', ['monthly_act.type_id' => $type], ['act.service_type' => $type], ['monthly_act.is_partner' => 1], ['DATE_FORMAT(`act_date`, "%c-%Y")' => $period], ["MONTH(FROM_UNIXTIME(served_at))" => $periodArr[0]], ["YEAR(FROM_UNIXTIME(served_at))" => $periodArr[1]], ['>', 'act.expense', 0], ['not', ['monthly_act.post_number' => null]], ['not', ['monthly_act.post_number' => '']]])->select('company.name as name, monthly_act.post_number as number, monthly_act.id as id, company_info.email as email')->groupBy('monthly_act.id')->asArray()->all();
            }

            $resArr = [];
            $resArr[0] = '';
            $resArr[1] = '';
            $resArr[2] = '';
            $emailArr = [];
            $numberArr = [];

            $ArrIdsActs = [];
            $ArrNameActs = [];
            $tracker_url = '';

            for ($i = 0; $i < count($actList); $i++) {

                $ResTrack = '';

                $oldResTrack = TrackerInfo::findOne(['type' => 1, 'second_id' => $actList[$i]['id'], 'number' => $actList[$i]['number']]);
                $doTrack = true;

                if (isset($oldResTrack)) {
                    if (isset($oldResTrack->value)) {
                        if ($oldResTrack->value == 2) {
                            $doTrack = false;

                            $indexID = $oldResTrack->second_id;
                            $ArrIdsActs[] = $indexID;
                            $ArrNameActs[$indexID] = $actList[$i]['name'];
                            $emailArr[$indexID] = $actList[$i]['email'];
                            $numberArr[$indexID] = $actList[$i]['number'];

                        }
                    }
                }

                if ($doTrack == true) {

                    $api_track_link = 'https://gdeposylka.ru';

                    if($tracker_url == '') {
                        $ResTrack = curl_init($api_track_link . '/api/v4/tracker/detect/' . $actList[$i]['number']);
                        curl_setopt_array($ResTrack, [
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Authorization-Token: 23acf42e4453471bac36d514e2370bc6d1b24bf5e1708eeedd7c2761f388204a8bfc442b5676d931']]
                        );
                        $result = json_decode(curl_exec($ResTrack), TRUE);
                        curl_close($ResTrack);

                        if (isset($result['result'])) {
                            if ($result['result'] == 'success') {

                                if (isset($result['data'][0]['tracker_url'])) {
                                    $tracker_url = $result['data'][0]['tracker_url'];
                                }

                            }
                        }
                    }

                    if($tracker_url != '') {

                        $index = $actList[$i]['id'];
                        $ArrIdsActs[] = $index;
                        $ArrNameActs[$index] = $actList[$i]['name'];
                        $emailArr[$index] = $actList[$i]['email'];
                        $numberArr[$index] = $actList[$i]['number'];

                        $ResTrack = curl_init($api_track_link . $tracker_url);
                        curl_setopt_array($ResTrack, [
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Authorization-Token: 23acf42e4453471bac36d514e2370bc6d1b24bf5e1708eeedd7c2761f388204a8bfc442b5676d931']]
                        );
                        $result = json_decode(curl_exec($ResTrack), TRUE);
                        curl_close($ResTrack);

                        if (isset($result['result'])) {
                            if ($result['result'] == 'success') {

                                if(isset($result['data']['checkpoints'])) {

                                    $checkpoints = $result['data']['checkpoints'];

                                    if(count($checkpoints) > 0) {

                                        $haveNeedTrack = 0;

                                        for ($j = 0; $j < count($checkpoints); $j++) {

                                            if((isset($checkpoints[$j]['status_name'])) && (isset($checkpoints[$j]['status_raw']))) {

                                                if (($checkpoints[$j]['status_name'] == 'Прибыла в пункт назначения') && ($checkpoints[$j]['status_raw'] == 'Обработка - Прибыло в место вручения') && ($haveNeedTrack == 0)) {
                                                    $haveNeedTrack = 1;
                                                }

                                                if (($checkpoints[$j]['status_name'] == 'Посылка доставлена') && ($checkpoints[$j]['status_raw'] == 'Вручение - Вручение адресату')) {
                                                    $haveNeedTrack = 2;
                                                }

                                            }

                                        }

                                        if (isset($oldResTrack)) {
                                            TrackerInfo::updateAll(['value' => $haveNeedTrack], 'type = ' . 1 . ' AND second_id = ' . $actList[$i]['id'] . ' AND number = ' . $actList[$i]['number']);
                                        } else {
                                            $newTrackerInfo = new TrackerInfo();
                                            $newTrackerInfo->value = $haveNeedTrack;
                                            $newTrackerInfo->type = 1;
                                            $newTrackerInfo->second_id = $actList[$i]['id'];
                                            $newTrackerInfo->number = $actList[$i]['number'];
                                            $newTrackerInfo->save();
                                        }

                                    }

                                }

                            }
                        }
                    }

                }

            }

            $arrTreckInfo = TrackerInfo::find()->where(['type' => 1])->andWhere(['second_id' => $ArrIdsActs])->select('value, second_id')->asArray()->all();
            $numTypes = [0 => 1, 1 => 1, 2 => 1];
            $resEmail = [];
            $resNumber = [];

            for ($i = 0; $i < count($arrTreckInfo); $i++) {

                $index = $arrTreckInfo[$i]['second_id'];

                if ($arrTreckInfo[$i]['value'] == 1) {
                    // Ждут в месте получения
                    $resArr[0] .= '<span style="color:#7F7F7F">' . $numTypes[0] . '.</span> ' . Html::a($ArrNameActs[$index], ['detail', 'id' => $arrTreckInfo[$i]['second_id']], ['target' => '_blank']) . "<br />";
                    $numTypes[0]++;

                    if((isset($emailArr[$index])) && (isset($numberArr[$index]))) {
                        $resEmail[$index] = $emailArr[$index];
                        $resNumber[$index] = $numberArr[$index];
                    }

                } else if ($arrTreckInfo[$i]['value'] == 2) {
                    $resArr[1] .= '<span style="color:#7F7F7F">' . $numTypes[1] . '.</span> ' . Html::a($ArrNameActs[$index], ['detail', 'id' => $arrTreckInfo[$i]['second_id']], ['target' => '_blank']) . "<br />";
                    $numTypes[1]++;
                } else {
                    $resArr[2] .= '<span style="color:#7F7F7F">' . $numTypes[2] . '.</span> ' . Html::a($ArrNameActs[$index], ['detail', 'id' => $arrTreckInfo[$i]['second_id']], ['target' => '_blank']) . "<br />";
                    $numTypes[2]++;
                }
            }

            echo json_encode(['success' => 'true', 'result' => $resArr, 'emails' => json_encode($resEmail), 'numbers' => json_encode($resNumber)]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

}