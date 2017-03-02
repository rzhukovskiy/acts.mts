<?php

namespace frontend\controllers;

use common\models\Act;
use common\models\search\ActSearch;
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
                        'actions' => ['list', 'common-list'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'common-list'],
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
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_PARTNER]);
        if ($type) {
            $searchModel->service_type = $type;
        }
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);

        if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
            $searchModel->client_id = Yii::$app->user->identity->company->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->addSelect('partner_id, service_type, COUNT(act.id) as actsCount')
            ->orderBy('actsCount DESC');
        if ($group == 'city') {
            $dataProvider->query
                ->groupBy('partner.address');
        }
        if ($group == 'type') {
            $dataProvider->query
                ->groupBy('act.service_type');
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'type' => $type,
            'group' => $group,
        ]);
    }
}
