<?php

namespace frontend\controllers;

use common\models\Act;
use common\models\Car;
use common\models\search\ActSearch;
use common\models\User;
use yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * CarController implements the CRUD actions for Car model.
 */
class AnalyticsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'detail'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view', 'detail'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                ]
            ]
        ];
    }

    /**
     * @param $type
     * @param $group
     * @return string
     */
    public function actionList($type = null, $group)
    {
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_HISTORY]);
        if ($type) {
            $searchModel->service_type = $type;
        }

        if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
            $searchModel->client_id = Yii::$app->user->identity->company->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $listServed = null;

        if ($group == 'type') {
            // Убираем дизенфекцию из общей статистики
            $dataProvider->query
                ->addSelect('car_id, car_number, served_at, partner_id, client_id, service_type, COUNT(act.id) as actsCount')
                ->orderBy('client_id, actsCount DESC')
                ->andWhere('service_type != 5')
                ->andWhere('car.type_id != 7')
                ->andWhere('car.type_id != 8');
            // Убираем дизенфекцию из общей статистики
        } else {
            $dataProvider->query
                ->addSelect('car_id, car_number, served_at, partner_id, client_id, service_type, COUNT(act.id) as actsCount')
                ->orderBy('client_id, actsCount DESC')
                ->andWhere('car.type_id != 7')
                ->andWhere('car.type_id != 8');
        }

        if ($group == 'city') {
            $dataProvider->query
                ->groupBy('client_id, partner.address');
        }
        if ($group == 'average') {
            $dataProvider->query
                ->groupBy('client_id, service_type');
        }
        if ($group == 'type') {
            $dataProvider->query
                ->groupBy('client_id, service_type');
        }
        if ($group == 'count') {
            $subQuery = $dataProvider->query
                ->groupBy('client_id, car_number');
            $query = Act::find()
                ->from(['actsCount' => $subQuery])
                ->select('COUNT(actsCount) as carsCount, actsCount, client_id')
                ->groupBy('client_id, actsCount')
                ->orderBy('client_id, actsCount DESC');

            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => false,
            ]);

            $clientId = 0;
            foreach ($dataProvider->getModels() as $model) {
                if ($clientId != $model->client_id) {
                    $clientId = $model->client_id;
                    $subQuery = Act::find()
                        ->select('car_id')
                        ->filterWhere([
                            'service_type' => $searchModel->service_type,
                            'client_id' => $clientId,
                        ]);
                    if ($searchModel->dateFrom) {
                        $subQuery->andFilterWhere(['between', 'served_at', strtotime($searchModel->dateFrom), strtotime($searchModel->dateTo)]);
                    }

                    $listServed[$clientId] = Car::find()
                        ->where(['not in', 'id', $subQuery->column()])
                        ->andWhere(['company_id' => $clientId])
                        ->andWhere('type_id != 7')
                        ->andWhere('type_id != 8')->count();
                }
            }
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'type' => $type,
            'group' => $group,
            'listServed' => $listServed,
        ]);
    }

    /**
     * @param $count
     * @param $group
     * @return string
     */
    public function actionView($group, $count = 1)
    {
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_HISTORY]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy('served_at ASC')
            ->andWhere('car.type_id != 7')
            ->andWhere('car.type_id != 8');

        if ($group == 'count') {
            if ($count) {
                $dataProvider->query
                    ->addSelect('car_number, served_at, partner_id, client_id, 
                service_type, COUNT(act.id) as actsCount, act.mark_id, act.type_id')
                    ->having(['actsCount' => $count])
                    ->groupBy('client_id, act.car_number');
            } else {
                $dataProvider->query->select('car_id');
                $query = Car::find()
                    ->where(['not in', 'id', $dataProvider->query->column()])
                    ->andWhere(['company_id' => $searchModel->client_id])
                    ->andWhere('type_id != 7')
                    ->andWhere('type_id != 8');

                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => false,
                ]);
            }

        }

        if ($group == 'average') {
            $dataProvider->query->addSelect('*, COUNT(DISTINCT act.id) as actsCount');
            $dataProvider->query->groupBy(['DATE_FORMAT(DATE(FROM_UNIXTIME(act.served_at)), "%Y-%m")']);
        }

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'group' => $group,
            'count' => $count,
        ]);
    }

    /**
     * @return string
     */
    public function actionDetail()
    {
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_HISTORY]);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy('served_at ASC')
            ->andWhere('car.type_id != 7')
            ->andWhere('car.type_id != 8');

        return $this->render('detail', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    public static function getSrTime($number, $serviceType) {

        // Вывод среднего времени обслуживания

        if($serviceType >= 0) {

            $timeNow = time(); // Текущая дата

            $rows = Act::find()->where(['car_number' => $number, 'service_type' => $serviceType])->orderBy('served_at ASC')->all();

            // Есди автомобиль обслуживался в этом году
            if (count($rows) > 0) {

                // Вычисляем количество обслуживаний автомобиля и вычисляем среднюю частоту
                $srTimeService = round(round(($timeNow - $rows[0]["served_at"]) / 86400) / count($rows));

                return "1 раз в " . $srTimeService . " дней";
            } else {
                return "Не обслуживался более года.";
            }

        } else {
            return "Не обслуживался более года.";
        }

        // END Вывод среднего времени обслуживания
    }

    public static function getWorkCars($company_id, $service_type, $showCarsWork = true, $actsCount = 0, $timeFrom = 0, $timeTo = 0)
    {

        $sqlRows = '';

        // Получаем среднее количество операций

        if (isset(Yii::$app->request->queryParams['ActSearch']['dateFrom']) && (isset(Yii::$app->request->queryParams['ActSearch']['dateFrom']))) {

            // Если указан период

            // Дата от
            $dataFrom = explode("T", Yii::$app->request->queryParams['ActSearch']['dateFrom']);
            $dataFrom = explode("-", $dataFrom[0]);
            $dataFrom = mktime(00, 00, 01, $dataFrom['1'], $dataFrom['2'], $dataFrom['0']) + 86400;

            // Дата до
            $dataTo = explode("T", Yii::$app->request->queryParams['ActSearch']['dateTo']);
            $dataTo = explode("-", $dataTo[0]);
            $dataTo = mktime(00, 00, 01, $dataTo['1'], $dataTo['2'], $dataTo['0']) + 86400;

            // Получаем список заказов компании
            $sqlRows = Act::find()->where(['client_id' => $company_id, 'service_type' => $service_type])->andWhere(['>=', 'served_at', $dataFrom])->andWhere(['<', 'served_at', $dataTo])->all();

        } else {
            // Получаем список заказов компании
            if(($timeFrom > 0) && ($timeTo > 0)) {
                $sqlRows = Act::find()->where(['client_id' => $company_id, 'service_type' => $service_type])->andWhere(['>=', 'served_at', $timeFrom])->andWhere(['<', 'served_at', $timeTo])->all();
            } else {

                // Если не указан период указываем дату за предыдущий месяц

                $dateFrom = strtotime(date("Y-m-t", strtotime("-2 month")) . 'T20:59:59.000Z');
                $dateTo = strtotime(date("Y-m-t", strtotime("-1 month")) . 'T20:59:59.000Z');

                $sqlRows = Act::find()->where(['client_id' => $company_id, 'service_type' => $service_type])->andWhere(['between', 'served_at', $dateFrom, $dateTo])->all();
            }
        }

        if (count($sqlRows) > 0) {

            // Получаем список машин компании

            $sqlCars = Car::find()->where(['company_id' => $company_id])
                ->andWhere(['!=', 'type_id', 7])
                ->andWhere(['!=', 'type_id', 8])->all();

            $numCarCompany = count($sqlCars);

            $arrayCars = [];

            for ($c = 0; $c < count($sqlCars); $c++) {
                $index = $sqlCars[$c]["number"];
                $arrayCars[$index] = 1;

                $index = null;
            }

            $arrayWorkCars = [];

            for ($i = 0; $i < count($sqlRows); $i++) {
                $index = $sqlRows[$i]["car_number"];

                // Сравниваем список машин компании со списком машин из заказов без повторных заказов
                if (isset($arrayCars[$index])) {
                    if ($arrayCars[$index] == 1) {
                        $arrayWorkCars[$index] = 1;
                    }
                }

                $index = null;
            }

            if($showCarsWork == true) {
                return count($arrayWorkCars);
            } else {

                if($numCarCompany > 0) {
                    // Получаем среднее количество операций
                    $averRes = $actsCount / $numCarCompany;

                    // Отображаем только одно число после запатой
                    //$averRes = round($averRes, 2);
                    $averRes = sprintf("%.6f", $averRes);

                    $averRes = rtrim($averRes, '0');

                    if(!(($averRes - floor($averRes)) > 0)) {
                        $averRes = (int) $averRes;
                    }

                } else {
                    $averRes = 0;
                }

                return $averRes;

            }

        } else {
            return 0;
        }

    }

}
