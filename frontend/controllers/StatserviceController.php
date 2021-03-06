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
                        'actions' => ['list', 'company', 'service'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN]
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER]
                    ],
                    [
                        'actions' => ['list', 'company', 'service'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT]
                    ]
                ]
            ]
        ];
    }

    public function actionCompany($type)
    {

        $searchModel = new ActSearch();

        $query = ActScope::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $dataProvider->query->select('SUM(`act_scope`.`price`) AS `price`, COUNT(DISTINCT `act_id`) as actsCount, `client_id`, `description`, `name`');
        $dataProvider->query->innerJoin('act', '`act_scope`.`act_id`=`act`.`id` AND `act_scope`.`company_id` != `act`.`partner_id`');
        $dataProvider->query->innerJoin('company', '`act`.`client_id`=`company`.`id`');
        $dataProvider->query->andWhere(['`act`.`service_type`' => $type]);

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
                $dataProvider->query->andWhere(['>', '`act`.`client_id`', '0']);
            }
        } else {
            $dataProvider->query->andWhere(['>', '`act`.`client_id`', '0']);
        }

        if(isset(Yii::$app->request->queryParams['ActSearch']['client_name'])) {
            $searchModel->client_name = Yii::$app->request->queryParams['ActSearch']['client_name'];
            $dataProvider->query->andWhere(['LIKE', '`company`.`name`', $searchModel->client_name]);
        }

        $dataProvider->query->andWhere(['between', '`act`.`served_at`', $dateFrom, $dateTo]);

        $dataProvider->query->groupBy('`client_id`, `description`');
        $dataProvider->query->orderBy('`client_id` ASC, actsCount DESC');

        return $this->render('list',
            [
                'type'                => $type,
                'searchModel'         => $searchModel,
                'dataProvider'        => $dataProvider,
            ]);

    }

    public function actionList($type)
    {

        $searchModel = new ActSearch();

        $query = ActScope::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $dataProvider->query->select('SUM(`act_scope`.`price`) AS `price`, COUNT(DISTINCT `act_id`) as actsCount, `partner_id`, `description`, `name`, `address`');
        $dataProvider->query->innerJoin('act', '`act_scope`.`act_id`=`act`.`id` AND `act_scope`.`company_id` = `act`.`partner_id`');
        $dataProvider->query->innerJoin('company', '`act`.`partner_id`=`company`.`id`');
        $dataProvider->query->andWhere(['`act`.`service_type`' => $type]);

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

        if(isset(Yii::$app->request->queryParams['ActSearch']['partner_id'])) {
            if(Yii::$app->request->queryParams['ActSearch']['partner_id'] > 0) {
                $dataProvider->query->andWhere(['`act`.`partner_id`' => Yii::$app->request->queryParams['ActSearch']['partner_id']]);
                $searchModel->partner_id = Yii::$app->request->queryParams['ActSearch']['partner_id'];
            } else {
                $dataProvider->query->andWhere(['>', '`act`.`partner_id`', '0']);
            }
        } else {
            $dataProvider->query->andWhere(['>', '`act`.`partner_id`', '0']);
        }

        if(isset(Yii::$app->request->queryParams['ActSearch']['client_name'])) {
            $searchModel->client_name = Yii::$app->request->queryParams['ActSearch']['client_name'];
            $dataProvider->query->andWhere(['LIKE', '`company`.`name`', $searchModel->client_name]);
        }

        $dataProvider->query->andWhere(['between', '`act`.`served_at`', $dateFrom, $dateTo]);

        $dataProvider->query->groupBy('`partner_id`, `description`');
        $dataProvider->query->orderBy('`partner_id` ASC, actsCount DESC');

        return $this->render('partnerList',
            [
                'type'                => $type,
                'searchModel'         => $searchModel,
                'dataProvider'        => $dataProvider,
            ]);

    }

    public function actionService($type, $company = 0)
    {

        $searchModel = new ActSearch();

        $query = Act::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

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

        // Inner Company
        $query->with('client');
        $query->with('partner');

        $dataProvider->query->andWhere(['`act`.`service_type`' => $type]);

        if(isset(Yii::$app->request->queryParams['ActSearch']['partner_id'])) {
            if(Yii::$app->request->queryParams['ActSearch']['partner_id'] > 0) {
                $dataProvider->query->andWhere(['`act`.`partner_id`' => Yii::$app->request->queryParams['ActSearch']['partner_id']]);
                $searchModel->partner_id = Yii::$app->request->queryParams['ActSearch']['partner_id'];
            } else {
                $dataProvider->query->andWhere(['>', '`act`.`partner_id`', '0']);
            }
        } else {
            $dataProvider->query->andWhere(['>', '`act`.`partner_id`', '0']);
        }

        if(isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {
            if(Yii::$app->request->queryParams['ActSearch']['client_id'] > 0) {
                $dataProvider->query->andWhere(['`act`.`client_id`' => Yii::$app->request->queryParams['ActSearch']['client_id']]);
                $searchModel->client_id = Yii::$app->request->queryParams['ActSearch']['client_id'];
            } else {
                $dataProvider->query->andWhere(['>', '`act`.`client_id`', '0']);
            }
        } else {
            $dataProvider->query->andWhere(['>', '`act`.`client_id`', '0']);
        }

        $dataProvider->query->andWhere(['between', '`act`.`served_at`', $dateFrom, $dateTo]);

        $dataProvider->query->addselect('`act`.*, COUNT(DISTINCT `act`.`id`) as actsCount');

        if($company) {
            $dataProvider->query->groupBy('`act`.`client_id`, `act`.`partner_id`');
            //$dataProvider->query->orderBy('`act`.`client_id` ASC, `act`.`partner_id` ASC');
            $dataProvider->query->orderBy('`act`.`client_id` ASC, `actsCount` DESC');
        } else {
            $dataProvider->query->groupBy('`act`.`partner_id`, `act`.`client_id`');
            //$dataProvider->query->orderBy('`act`.`partner_id` ASC, `act`.`client_id` ASC');
            $dataProvider->query->orderBy('`act`.`partner_id` ASC, `actsCount` DESC');
        }


        return $this->render('serviceList',
            [
                'type'                => $type,
                'company'             => $company,
                'searchModel'         => $searchModel,
                'dataProvider'        => $dataProvider,
            ]);

    }

}