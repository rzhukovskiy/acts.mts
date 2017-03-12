<?php

namespace frontend\controllers;

use common\models\Act;
use common\models\Car;
use common\models\search\ActSearch;
use common\models\search\CarSearch;
use common\models\User;
use yii;
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
        $dataProvider->query
            ->addSelect('act.number, served_at, partner_id, client_id, service_type, COUNT(act.id) as actsCount')
            ->orderBy('client_id, actsCount DESC')
            ->andWhere('car.type_id != 7')
            ->andWhere('car.type_id != 8');
        if ($group == 'city') {
            $dataProvider->query
                ->groupBy('client_id, partner.address');
        }
        if ($group == 'type') {
            $dataProvider->query
                ->groupBy('client_id, act.service_type');
        }
        if ($group == 'count') {
            $dataProvider->query
                ->groupBy('client_id, act.number');
            $query = Act::find()
                ->from(['actsCount' => $dataProvider->query])
                ->select('COUNT(actsCount) as carsCount, actsCount, client_id')
                ->groupBy('client_id, actsCount')
                ->orderBy('client_id, actsCount DESC');

            $dataProvider = new yii\data\ActiveDataProvider([
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
                    ->addSelect('act.number, served_at, partner_id, client_id, 
                service_type, COUNT(act.id) as actsCount, act.mark_id, act.type_id')
                    ->having(['actsCount' => $count])
                    ->groupBy('client_id, act.number');
            } else {
                $dataProvider->query->select('act.number');
                $query = Car::find()
                    ->where(['not in', 'number', $dataProvider->query->all()])
                    ->andWhere(['company_id' => $searchModel->client_id])
                    ->andWhere('type_id != 7')
                    ->andWhere('type_id != 8');

                $dataProvider = new yii\data\ActiveDataProvider([
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
}
