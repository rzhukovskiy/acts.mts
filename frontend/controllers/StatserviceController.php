<?php

namespace frontend\controllers;

use common\models\ActScope;
use common\models\User;
use frontend\traits\ChartTrait;
use common\models\Service;
use common\components\ArrayHelper;
use yii\data\ActiveDataProvider;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Controller;
use frontend\models\search\ActSearch;
use common\models\Company;
use common\components\DateHelper;
use frontend\models\Act;
use yii\web\NotFoundHttpException;

class StatserviceController extends Controller
{
    use ChartTrait;

    // ToDo: plz setup rules, and close pages from users if
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN]
                    ],
                    [
                        'actions' => ['view', 'month', 'day', 'total', 'act'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER]
                    ],
                    [
                        'actions' => ['view', 'month', 'day', 'total', 'act','list'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['view', 'month', 'day', 'total', 'act'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT]
                    ]
                ]
            ]
        ];
    }

    public function actionCompany($type)
    {

        // Нужно делать вместо поиска по актам поиск по услугам а иннер джоин акт и тип = , group по company_id

        $searchModel = new ActSearch();

        $query = ActScope::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $dataProvider->query->select('SUM(`act`.`income`) AS `price`, COUNT(`act_scope`.`id`) as actsCount, `client_id`, `description`, `name`');
        $dataProvider->query->innerJoin('act', '`act_scope`.`act_id`=`act`.`id`');
        $dataProvider->query->innerJoin('company', '`act`.`client_id`=`company`.`id`');
        $dataProvider->query ->andWhere(['`act`.`service_type`' => $type]);

        $dateFrom = '';
        if(isset(Yii::$app->request->queryParams['ActSearch']['dateFrom'])) {
            $dateFrom = Yii::$app->request->queryParams['ActSearch']['dateFrom'];
        } else {
            $dateFrom = date("Y-m-t", strtotime("-2 month")) . 'T21:00:00.000Z';
        }
        $searchModel->dateFrom = $dateFrom;
        $dateFrom = strtotime($dateFrom);

        $dateTo = '';
        if(isset(Yii::$app->request->queryParams['ActSearch']['dateTo'])) {
            $dateTo = Yii::$app->request->queryParams['ActSearch']['dateTo'];
        } else {
            $dateTo = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
        }
        $searchModel->dateTo = $dateTo;
        $dateTo = strtotime($dateTo);

        if(isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {
            if(Yii::$app->request->queryParams['ActSearch']['client_id'] > 0) {
                $dataProvider->query->andWhere(['`act`.`client_id`' => Yii::$app->request->queryParams['ActSearch']['client_id']]);
                $searchModel->client_id = Yii::$app->request->queryParams['ActSearch']['client_id'];
            } else {
                $dataProvider->query ->andWhere(['>', '`act`.`client_id`', '0']);
            }
        } else {
            $dataProvider->query ->andWhere(['>', '`act`.`client_id`', '0']);
        }

        $dataProvider->query ->andWhere(['between', '`act_scope`.`created_at`', $dateFrom, $dateTo]);
        $dataProvider->query ->andWhere(['between', '`act`.`served_at`', $dateFrom, $dateTo]);

        $dataProvider->query->groupBy('`client_id`, `description`');
        $dataProvider->query->orderBy('`client_id` ASC');

        return $this->render('list',
            [
                'type'                => $type,
                'searchModel'         => $searchModel,
                'dataProvider'        => $dataProvider,
            ]);

    }

    public function actionList($type)
    {

    }

}