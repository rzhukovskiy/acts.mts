<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\components\ArrayHelper;
use common\models\Company;
use common\models\MonthlyAct;
use common\models\search\MonthlyActSearch;
use common\models\Service;
use common\models\TrackerInfo;
use common\models\User;
use common\models\ActData;
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
                        'actions' => ['delete', 'delete-image', 'ajax-act-status', 'ajax-payment-status', 'archive', 'searchact', 'getcomments', 'gettrack', 'gettrackerlist'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['update', 'detail', 'list', 'archive', 'searchact', 'getcomments', 'gettrack', 'gettrackerlist'],
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

        if((!($type == 1)) && (!($type == -1))) {
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