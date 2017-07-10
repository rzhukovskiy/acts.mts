<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use yii;
use common\models\DepartmentCompany;
use common\models\search\DepartmentCompanySearch;
use common\models\DepartmentUserCompanyType;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use common\models\Company;
use yii\filters\AccessControl;
use common\models\User;

class ActivityController extends Controller
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
                        'actions' => ['new', 'shownew', 'archive', 'showarchive'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    public function actionNew($type)
    {

        $searchModel = new DepartmentCompanySearch(['scenario' => 'new']);
        $searchModel->type = $type;

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if(!isset($params['ActSearch']['dateFrom'])) {
            $params['ActSearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        }

        if(!isset($params['ActSearch']['dateTo'])) {
            $params['ActSearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['ActSearch']['dateTo'];
        }
        // Если не выбран период то показываем только текущий год

        $dataProvider = $searchModel->search($params);

        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        $listType = Company::$listType;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'authorMembers' => $authorMembers,
            'listType' => $listType,
            'type' => $type,
        ]);

    }

    public function actionShownew($user_id, $type)
    {

        $searchModel = new DepartmentCompanySearch(['scenario' => 'shownew']);
        $searchModel->type = $type;
        $searchModel->user_id = $user_id;

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if(!isset($params['ActSearch']['dateFrom'])) {
            $params['ActSearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        }

        if(!isset($params['ActSearch']['dateTo'])) {
            $params['ActSearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['ActSearch']['dateTo'];
        }
        // Если не выбран период то показываем только текущий год

        $dataProvider = $searchModel->search($params);

        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        $listType = Company::$listType;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'authorMembers' => $authorMembers,
            'listType' => $listType,
            'type' => $type,
        ]);

    }

    public function actionArchive($type)
    {

        $searchModel = new DepartmentCompanySearch(['scenario' => 'archive']);
        $searchModel->type = $type;

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то
        if(!isset($params['ActSearch']['dateFrom'])) {
            $params['ActSearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        }

        if(!isset($params['ActSearch']['dateTo'])) {
            $params['ActSearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['ActSearch']['dateTo'];
        }
        // Если не выбран период то

        $dataProvider = $searchModel->search($params);

        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        $listType = Company::$listType;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'authorMembers' => $authorMembers,
            'listType' => $listType,
            'type' => $type,
        ]);

    }

    public function actionShowarchive($user_id, $type)
    {

        $searchModel = new DepartmentCompanySearch(['scenario' => 'showarchive']);
        $searchModel->type = $type;
        $searchModel->user_id = $user_id;

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if(!isset($params['ActSearch']['dateFrom'])) {
            $params['ActSearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        }

        if(!isset($params['ActSearch']['dateTo'])) {
            $params['ActSearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['ActSearch']['dateTo'];
        }
        // Если не выбран период то показываем только текущий год

        $dataProvider = $searchModel->search($params);

        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        $listType = Company::$listType;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'authorMembers' => $authorMembers,
            'listType' => $listType,
            'type' => $type,
        ]);

    }

}