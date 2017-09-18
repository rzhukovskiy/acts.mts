<?php
namespace api\controllers;

use common\components\ArrayHelper;
use common\models\Act;
use common\models\Company;
use common\models\Lock;
use common\models\Service;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


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
                'only' => ['list', 'closeload', 'checkperiodclose', 'getservicedata'],
                'rules' => [
                    [
                        'actions' => ['list', 'closeload', 'checkperiodclose', 'getservicedata'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['list', 'closeload', 'checkperiodclose', 'getservicedata'],
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

                $ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->offset($startLimit)->limit(51)->asArray()->all();
            } else {
                $ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->offset($startLimit)->limit(51)->asArray()->all();
            }

            return json_encode(['result' => json_encode($ressArray)]);

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // Закрываем загрузку (кнопка)
    public function actionCloseload()
    {

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("period"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $period = Yii::$app->request->post("period");

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

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");

            if($type == 2) {

                $serviceList = Service::find()->innerJoin('company_service', '(`company_service`.`company_id`=' . $company_id . ' AND `company_service`.`service_id` = `service`.`id`) OR `service`.`id`=52')->where(['`service`.`type`' => $type])
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

                for($i = 0; $i < count($numcontArr); $i++) {
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
            } else {
                return json_encode(['error' => 1]);
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

}