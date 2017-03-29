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
        $subQuery = null;

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
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'type' => $type,
            'group' => $group,
            'subQuery' => $subQuery,
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

    public static function GetSrTime($number, $serviceType) {

        // Вывод среднего времени обслуживания

        if($serviceType >= 0) {

            $TimeNow = time(); // Текущая дата

            $rows = (new Query())
                ->select(['id', 'served_at'])
                ->from('{{%act}}')
                ->where(['car_number' => $number])
                ->andWhere(['service_type' => $serviceType])
                //->andWhere(['>' ,'served_at', ($TimeNow - 31535999)]) Если хотим узнать среднее количество только за прошедший год
                ->orderBy('served_at ASC')
                ->all();

            // Есди автомобиль обслуживался в этом году
            if (count($rows) > 0) {

                // Вычисляем количество обслуживаний автомобиля и вычисляем среднюю частоту
                $srTimeService = round(round(($TimeNow - $rows[0]["served_at"]) / 86400) / count($rows));

                return "1 раз в " . $srTimeService . " дней";
            } else {
                return "Не обслуживался более года.";
            }

        } else {
            return "Не обслуживался более года.";
        }

        // END Вывод среднего времени обслуживания
    }

    public static function GetWorkCars($company_id, $service_type)
    {

        // Получаем среднее количество операций

        if (isset(Yii::$app->request->queryParams['ActSearch']['dateFrom']) && (isset(Yii::$app->request->queryParams['ActSearch']['dateFrom']))) {

            // Если указан период

            // Дата от
            $DataFrom = explode("T", Yii::$app->request->queryParams['ActSearch']['dateFrom']);
            $DataFrom = explode("-", $DataFrom[0]);
            $DataFrom = mktime(00, 00, 01, $DataFrom['1'], $DataFrom['2'], $DataFrom['0']) + 86400;

            // Дата до
            $DataTo = explode("T", Yii::$app->request->queryParams['ActSearch']['dateTo']);
            $DataTo = explode("-", $DataTo[0]);
            $DataTo = mktime(00, 00, 01, $DataTo['1'], $DataTo['2'], $DataTo['0']) + 86400;

            // Получаем список заказов компании
            $sqlRows = (new Query())
                ->select(['id', 'car_number'])
                ->from('{{%act}}')
                ->where(['client_id' => $company_id])
                ->andWhere(['>=', 'served_at', $DataFrom])
                ->andWhere(['<', 'served_at', $DataTo])
                ->andWhere(['service_type' => $service_type])
                ->all();
        } else {
            // Получаем список заказов компании
            $sqlRows = (new Query())
                ->select(['id', 'car_number'])
                ->from('{{%act}}')
                ->where(['client_id' => $company_id])
                ->andWhere(['service_type' => $service_type])
                ->all();
        }

        if (count($sqlRows) > 0) {

            // Получаем список машин компании

            $sqlCars = Car::find()->where(['company_id' => $company_id])
                ->andWhere(['!=', 'type_id', 7])
                ->andWhere(['!=', 'type_id', 8])->all();

            $ArrayCars = [];

            for ($c = 0; $c < count($sqlCars); $c++) {
                $index = $sqlCars[$c]["number"];
                $ArrayCars[$index] = 1;

                $index = null;
            }

            $ArrayWorkCars = [];

            for ($i = 0; $i < count($sqlRows); $i++) {
                $index = $sqlRows[$i]["car_number"];

                // Сравниваем список машин компании со списком машин из заказов без повторных заказов
                if (isset($ArrayCars[$index])) {
                    if ($ArrayCars[$index] == 1) {
                        $ArrayWorkCars[$index] = 1;
                    }
                }

                $index = null;
            }

            return count($ArrayWorkCars);
        } else {
            return 0;
        }

    }

}
