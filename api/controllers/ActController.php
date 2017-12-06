<?php
namespace api\controllers;

use api\models\ApiToken;
use common\components\ArrayHelper;
use common\models\Act;
use common\models\Card;
use common\models\Company;
use common\models\CompanyService;
use common\models\Entry;
use common\models\Lock;
use common\models\Service;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;


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
                'only' => ['list', 'closeload', 'checkperiodclose', 'getservicedata', 'create', 'createtires', 'uploadcheck', 'checkcard', 'checknumber'],
                'rules' => [
                    [
                        'actions' => ['list', 'closeload', 'checkperiodclose', 'getservicedata', 'create', 'createtires', 'uploadcheck', 'checkcard', 'checknumber'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['list', 'closeload', 'checkperiodclose', 'getservicedata', 'create', 'createtires', 'uploadcheck', 'checkcard', 'checknumber'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['post', 'get'],
                    'closeload' => ['post', 'get'],
                    'checkperiodclose' => ['post', 'get'],
                    'getservicedata' => ['post', 'get'],
                    'create' => ['post', 'get'],
                    'createtires' => ['post', 'get'],
                    'uploadcheck' => ['post', 'get'],
                    'checkcard' => ['post', 'get'],
                    'checknumber' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionList()
    {

/*        $company_id = Yii::$app->request->get("company_id");
        $type = Yii::$app->request->get("type");
        $monthFilter = Yii::$app->request->get("month");
        $yearFilter = Yii::$app->request->get("year");

        $ressArray = '';

        if ($type == 1) {

            $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

            $arrParParIds = [];

            for ($i = 0; $i < count($queryPar); $i++) {

                $arrParParIds[] = $queryPar[$i];

                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                for ($j = 0; $j < count($queryParPar); $j++) {
                    $arrParParIds[] = $queryParPar[$j];
                }

            }

            $ressArray = Act::find()->innerJoin('type', '`type`.`id` = `act`.`type_id`')->innerJoin('mark', '`mark`.`id` = `act`.`mark_id`')->innerJoin('card', '`card`.`id` = `act`.`card_id`')->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->asArray()->all();
        } else {
            $ressArray = Act::find()->innerJoin('type', '`type`.`id` = `act`.`type_id`')->innerJoin('mark', '`mark`.`id` = `act`.`mark_id`')->innerJoin('card', '`card`.`id` = `act`.`card_id`')->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->asArray()->all();
        }

        return json_encode(['result' => json_encode($ressArray)]);*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("month")) && (Yii::$app->request->post("year")) && (Yii::$app->request->post("page"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $monthFilter = Yii::$app->request->post("month");
            $yearFilter = Yii::$app->request->post("year");

            $page = Yii::$app->request->post("page");
            $startLimit = 0;

            if($page > 1) {
                $startLimit = (($page - 1) * 50);
            }

            $ressArray = '';

            if ($type == 1) {

                $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

                $arrParParIds = [];

                for ($i = 0; $i < count($queryPar); $i++) {

                    $arrParParIds[] = $queryPar[$i];

                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                    for ($j = 0; $j < count($queryParPar); $j++) {
                        $arrParParIds[] = $queryParPar[$j];
                    }

                }

                //$ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->offset($startLimit)->limit(51)->asArray()->all();
                $ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->andWhere(['AND', ['!=', 'act.service_type', 6], ['!=', 'act.service_type', 7], ['!=', 'act.service_type', 8]])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->offset($startLimit)->limit(51)->asArray()->all();
            } else {
                //$ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->offset($startLimit)->limit(51)->asArray()->all();
                $ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->andWhere(['AND', ['!=', 'act.service_type', 6], ['!=', 'act.service_type', 7], ['!=', 'act.service_type', 8]])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->offset($startLimit)->limit(51)->asArray()->all();
            }

            return json_encode(['result' => json_encode($ressArray)]);

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Закрываем загрузку (кнопка)
    public function actionCloseload()
    {

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("period"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $period = Yii::$app->request->post("period");

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 2]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 2]);
                } else {

                    if (($type == 2) || ($type == 3) || ($type == 4) || ($type == 5)) {

                        $lockedLisk = Lock::checkLocked($period, $type);

                        if (count($lockedLisk) > 0) {

                            $closeAll = false;
                            $closeCompany = false;

                            for ($c = 0; $c < count($lockedLisk); $c++) {
                                if ($lockedLisk[$c]["company_id"] == 0) {
                                    $closeAll = true;
                                }
                                if ($lockedLisk[$c]["company_id"] == $company_id) {
                                    $closeCompany = true;
                                }
                            }

                            if (($closeAll == false) && ($closeCompany == false)) {

                                $lock = new Lock();
                                $lock->period = $period;
                                $lock->type = $type;
                                $lock->company_id = $company_id;

                                $lock->save();

                                return json_encode(['success' => 1]);
                            } elseif (($closeAll == true) && ($closeCompany == true)) {

                                Lock::deleteAll([
                                    'type' => $type,
                                    'period' => $period,
                                    'company_id' => $company_id,
                                ]);

                                return json_encode(['success' => 1]);
                            } elseif (($closeAll == true) && ($closeCompany == false)) {
                                return json_encode(['success' => 0]);
                            } elseif (($closeAll == false) && ($closeCompany == true)) {
                                return json_encode(['success' => 0]);
                            } else {
                                return json_encode(['success' => 0]);
                            }

                        } else {

                            $lock = new Lock();
                            $lock->period = $period;
                            $lock->type = $type;
                            $lock->company_id = $company_id;

                            $lock->save();

                            return json_encode(['success' => 1]);
                        }

                    } else {
                        return json_encode(['success' => 0]);
                    }

                }

            } else {
                return json_encode(['error' => 2]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Проверяем период, можно ли добавить в него новый акт
    public function actionCheckperiodclose()
    {

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("period"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $period = Yii::$app->request->post("period");

            $dataArrayParam = explode("-", $period);
            $dataArrayParam = mktime(00, 00, 01, $dataArrayParam['1'], $dataArrayParam['0'], $dataArrayParam['2']);
            $timePeriod = date('n-Y', $dataArrayParam);
            $lockedList = Lock::checkLocked($timePeriod, $type);

            $not_locked = 0;

            if(count($lockedList) > 0) {

                $closeAll = false;
                $closeCompany = false;

                for ($c = 0; $c < count($lockedList); $c++) {
                    if ($lockedList[$c]["company_id"] == 0) {
                        $closeAll = true;
                    }
                    if ($lockedList[$c]["company_id"] == $company_id) {
                        $closeCompany = true;
                    }
                }

                if (($closeAll == true) && ($closeCompany == false)) {
                } elseif (($closeAll == false) && ($closeCompany == true)) {
                } else {
                    $not_locked = 1;
                }

            } else {
                $not_locked = 1;
            }

            return json_encode(['notLock' => $not_locked]);

        } else {
            return json_encode(['notLock' => 0]);
        }

    }

    public function actionGetservicedata()
    {

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("car_type"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $car_type = Yii::$app->request->post("car_type");

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 2]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 2]);
                } else {

                    if($type == 2) {

                        $serviceList = Service::find()->innerJoin('company_service', '(`company_service`.`company_id`=' . $company_id . ' AND `company_service`.`type_id`=' . $car_type . ' AND `company_service`.`service_id` = `service`.`id`) OR `service`.`id`=52')->where(['`service`.`type`' => $type])
                            ->groupBy('`service`.`id`')->orderBy('`service`.`id`')->select(['description', '`service`.`id`'])
                            ->indexBy('id')->column();

                        $serviceList = ArrayHelper::perMutate($serviceList);
                        $newServiceList = [];
                        $checkNewArr = false;
                        $numcontArr = [];

                        foreach ($serviceList as $key => $value) {

                            if ($value == 'снаружи') {
                                $numcontArr[] = $key;
                            }

                            if ($value == 'внутри') {
                                $numcontArr[] = $key;
                            }

                            if (($value == 'внутри+снаружи') || ($value == 'снаружи+внутри')) {
                                $numcontArr[] = $key;
                                $serviceList[$key] = 'снаружи+внутри';
                            }

                            if ($value == 'отогрев') {
                                $numcontArr[] = $key;
                            }

                            if ($value == 'двигатель') {
                                $numcontArr[] = $key;
                            }

                            if ($value == 'химчистка') {
                                $numcontArr[] = $key;
                            }

                            //
                            if ($value == 'Стандарт') {
                                $numcontArr[] = $key;
                            }
                            if ($value == 'Экспресс') {
                                $numcontArr[] = $key;
                            }
                            if ($value == 'Уборка салона пылесосом') {
                                $numcontArr[] = $key;
                            }
                            if ($value == 'Влажная уборка салона') {
                                $numcontArr[] = $key;
                            }
                            if ($value == 'Уборка багажника') {
                                $numcontArr[] = $key;
                            }
                            if ($value == 'Протирка стёкол') {
                                $numcontArr[] = $key;
                            }
                            if ($value == 'Удаление битума') {
                                $numcontArr[] = $key;
                            }

                        }

                        for ($i = 0; $i < count($numcontArr); $i++) {
                            $newServiceList[$numcontArr[$i]] = $serviceList[$numcontArr[$i]];
                        }

                        foreach ($serviceList as $key => $value) {

                            if (($value == 'снаружи') || ($value == 'внутри') || ($value == 'внутри+снаружи') || ($value == 'отогрев') || ($value == 'двигатель') || ($value == 'химчистка') || ($value == 'Стандарт') || ($value == 'Экспресс') || ($value == 'Уборка салона пылесосом') || ($value == 'Влажная уборка салона') || ($value == 'Уборка багажника') || ($value == 'Протирка стёкол') || ($value == 'Удаление битума')) {
                            } else {
                                $newServiceList[$key] = $value;
                            }

                        }

                        $arrRes = [];
                        $i = 0;
                        foreach ($newServiceList as $key => $value) {
                            $arrRes[$i]['id'] = $key;
                            $arrRes[$i]['name'] = $value;
                            $i++;
                        }

                        return json_encode(['error' => 0, 'service' => json_encode($arrRes)]);
                    } else if (($type == 3) || ($type == 4)) {
                        $serviceList = Service::find()->where(['type' => $type])
                            ->orderBy('description')->select(['description', 'id', 'is_fixed'])->asArray()->all();

                        $arrServiceID = [];

                        for($i = 0; $i < count($serviceList); $i++) {
                            $arrServiceID[] = $serviceList[$i]['id'];
                        }

                        $servicePrice = CompanyService::find()->where(['OR', ['service_id' => $arrServiceID]])->andWhere(['company_id' => $company_id])->andWhere(['type_id' => $car_type])
                            ->orderBy('id')->select('service_id, price')->asArray()->all();

                        return json_encode(['error' => 0, 'service' => json_encode($serviceList), 'pricelist' => json_encode($servicePrice)]);

                    } else {
                        return json_encode(['error' => 1]);
                    }

                }

            } else {
                return json_encode(['error' => 2]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Проверяем отображать ли кнопку для закрытия загрузки
    public static function checkCanCloseLoad($type, $company_id) {

        // Дата прошлого месяца
        $dateYesterday = strtotime("-1 month");

        $lockedList = \common\models\Lock::checkLocked(date('n-Y', $dateYesterday), $type);
        $is_locked = 0;

        if (count($lockedList) > 0) {

            $closeAll = false;
            $closeCompany = false;

            for ($c = 0; $c < count($lockedList); $c++) {
                if ($lockedList[$c]["company_id"] == 0) {
                    $closeAll = true;
                }
                if ($lockedList[$c]["company_id"] == $company_id) {
                    $closeCompany = true;
                }
            }

            if (($closeAll == true) && ($closeCompany == false)) {
                $is_locked = 1;
            } elseif (($closeAll == false) && ($closeCompany == true)) {
                $is_locked = 1;
            }

        }

        return $is_locked;

    }

    // Проверка номера карты
    public function actionCheckcard()
    {

        if ((Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("card"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $card = Yii::$app->request->post("card");

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 2]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 2]);
                } else {

                    $carRes = Card::find()->where(['number' => $card])->select('company_id, id')->asArray()->all();

                    if (count($carRes) > 0) {

                        if ((isset($carRes[0]['company_id'])) && (isset($carRes[0]['id']))) {

                            if (($carRes[0]['company_id'] > 0) && ($carRes[0]['id'] > 0)) {
                                return json_encode(['error' => 0]);
                            } else {
                                return json_encode(['error' => 1]);
                            }

                        } else {
                            return json_encode(['error' => 1]);
                        }

                    } else {
                        return json_encode(['error' => 1]);
                    }

                }

            } else {
                return json_encode(['error' => 2]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Проверка номера чека
    public function actionChecknumber()
    {

        if ((Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("check"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $check = Yii::$app->request->post("check");

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 2]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 2]);
                } else {

                    $actRes = Act::find()->where(['check' => $check])->select('id, served_at')->asArray()->all();

                    if (count($actRes) > 0) {
                        return json_encode(['error' => 1]);
                    } else {
                        return json_encode(['error' => 0]);
                    }

                }

            } else {
                return json_encode(['error' => 2]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Создаем новый акт
    public function actionCreate()
    {

        if ((Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("selectDate")) && (Yii::$app->request->post("selectCardNum")) && (Yii::$app->request->post("selectCarNumber")) && (Yii::$app->request->post("selectMark")) && (Yii::$app->request->post("selectType")) && (Yii::$app->request->post("services")) && (Yii::$app->request->post("checkNumber"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $selectDate = Yii::$app->request->post("selectDate");
            $selectCardNum = Yii::$app->request->post("selectCardNum");
            $selectCarNumber = Yii::$app->request->post("selectCarNumber");
            $selectMark = Yii::$app->request->post("selectMark");
            $selectType = Yii::$app->request->post("selectType");
            $services = Yii::$app->request->post("services");
            $checkNumber = Yii::$app->request->post("checkNumber");

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 4]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 4]);
                } else {

                    // Токен ок, продолжаем

                    $model = new Act();
                    $model->service_type = $type;
                    $model->partner_id = $company_id;

                    $createParams = [];
                    $createParams['Act']['time_str'] = $selectDate;
                    $createParams['Act']['card_number'] = $selectCardNum;
                    $createParams['Act']['car_number'] = $selectCarNumber;
                    $createParams['Act']['extra_car_number'] = "";
                    $createParams['Act']['mark_id'] = $selectMark;
                    $createParams['Act']['type_id'] = $selectType;
                    $createParams['Act']['serviceList'] = [0 => ['service_id' => $services, 'amount' => 1, 'price' => 0]];
                    $createParams['Act']['check'] = $checkNumber;

                    if ($model->load($createParams)) {

                        if ($model->save()) {

                            // Загрузка чека
                            $photoCheck = UploadedFile::getInstanceByName("image");

                            if((isset($photoCheck)) && (isset($photoCheck->type)) && (isset($photoCheck->tempName))) {
                                if (($type == 2) && (($photoCheck->type == 'image/png') || ($photoCheck->type == 'image/jpg') || ($photoCheck->type == 'image/jpeg'))) {

                                    $image = \Yii::$app->image->load($photoCheck->tempName);

                                    $imagePath = \Yii::getAlias('@frontend/web/files/checks/' . $model->id . '.png');

                                    if (!file_exists(\Yii::getAlias('@frontend/web/files/'))) {
                                        mkdir(\Yii::getAlias('@frontend/web/files/'), 0775);
                                    }

                                    if (!file_exists(\Yii::getAlias('@frontend/web/files/checks/'))) {
                                        mkdir(\Yii::getAlias('@frontend/web/files/checks/'), 0775);
                                    }

                                    $fileHaveName = '';

                                    foreach (glob("web/files/checks/" . $model->id . ".*") as $filename) {
                                        $fileHaveName = $filename;
                                    }

                                    if ($fileHaveName != '') {
                                        if (file_exists(\Yii::getAlias('@frontend/web/' . $fileHaveName))) {
                                            chmod(\Yii::getAlias('@frontend/web/' . $fileHaveName), 0775);
                                            unlink(\Yii::getAlias('@frontend/web/' . $fileHaveName));
                                        }
                                    }

                                    $image->resize(Act::ACT_WIDTH, Act::ACT_HEIGHT)->save($imagePath);
                                    chmod($imagePath, 0775);
                                }
                            }
                            // Загрузка чека

                            return json_encode(['error' => 0]);
                        } else {

                            $errorCode = 1;

                            if(isset($model->errors['check'])) {
                                $errorCode = 3;
                            } elseif (isset($model->errors['card'])) {
                                $errorCode = 2;
                            }

                            return json_encode(['error' => $errorCode]);
                        }
                    } else {
                        return json_encode(['error' => 1]);
                    }

                }

            } else {
                return json_encode(['error' => 4]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Создаем новый акт шиномонтаж
    public function actionCreatetires()
    {

        if ((Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("selectDate")) && (Yii::$app->request->post("selectCardNum")) && (Yii::$app->request->post("selectCarNumber")) && (Yii::$app->request->post("selectMark")) && (Yii::$app->request->post("selectType")) && (Yii::$app->request->post("services")) && (Yii::$app->request->post("amounts")) && (Yii::$app->request->post("prices"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $selectDate = Yii::$app->request->post("selectDate");
            $selectCardNum = Yii::$app->request->post("selectCardNum");
            $selectCarNumber = Yii::$app->request->post("selectCarNumber");
            $selectMark = Yii::$app->request->post("selectMark");
            $selectType = Yii::$app->request->post("selectType");

            // Услуги
            $services = [];
            $amounts = [];
            $prices = [];

            $tmpServices = Yii::$app->request->post("services");
            $tmpAmounts = Yii::$app->request->post("amounts");
            $tmpPrices = Yii::$app->request->post("prices");

            $services = explode(":", $tmpServices);
            $amounts = explode(":", $tmpAmounts);
            $prices = explode(":", $tmpPrices);
            // Услуги

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 4]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 4]);
                } else {

                    if(((count($services) > 1) && (count($amounts) > 1) && (count($prices) > 1)) && ((count($services) == count($amounts)) && (count($services) == count($prices)))) {

                        // Токен ок, продолжаем

                        $model = new Act();
                        $model->service_type = $type;
                        $model->partner_id = $company_id;

                        $createParams = [];
                        $createParams['Act']['time_str'] = $selectDate;
                        $createParams['Act']['card_number'] = $selectCardNum;
                        $createParams['Act']['car_number'] = $selectCarNumber;
                        $createParams['Act']['extra_car_number'] = "";
                        $createParams['Act']['mark_id'] = $selectMark;
                        $createParams['Act']['type_id'] = $selectType;

                        // Услуги
                        $arrServices = [];

                        $iService = 0;

                        for ($i = 0; $i < count($services); $i++) {
                            if(($services[$i] > 0) && ($amounts[$i] > 0)) {
                                $arrServices[$iService]['service_id'] = $services[$i];
                                $arrServices[$iService]['amount'] = $amounts[$i];
                                $arrServices[$iService]['price'] = $prices[$i];
                                $iService++;
                            }
                        }

                        $createParams['Act']['serviceList'] = $arrServices;
                        // Услуги

                        if ($model->load($createParams)) {

                            if ($model->save()) {
                                return json_encode(['error' => 0]);
                            } else {

                                $errorCode = 1;

                                if (isset($model->errors['card'])) {
                                    $errorCode = 2;
                                }

                                return json_encode(['error' => $errorCode]);
                            }
                        } else {
                            return json_encode(['error' => 1]);
                        }

                    } else {
                        return json_encode(['error' => 3]);
                    }

                }

            } else {
                return json_encode(['error' => 4]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}