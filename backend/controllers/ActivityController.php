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

        $searchModel = DepartmentCompany::find()->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->where(['OR', ['AND', '`department_company`.`remove_date` IS NULL', '`company`.`status` = 1'], ['AND', '`department_company`.`remove_date` IS NOT NULL', '`company`.`status` = 2']])->andWhere('`department_company`.`user_id` > 0')->andWhere(['`company`.`type`' => $type])->select('`department_company`.*, `company`.`id`, `company`.`name`, COUNT(Distinct `department_company`.`company_id`) as companyNum')->groupBy('`department_company`.`user_id`');

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'user_id'    => SORT_DESC,
            ]
        ];

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

        $searchModel = DepartmentCompany::find()->with('company')->where(['`department_company`.`user_id`' => $user_id])->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere(['`department_company`.`remove_date`' => null])->andWhere(['`company`.`type`' => $type])->andWhere(['`company`.`status`' => 1])->select('`department_company`.*, `company`.`id`, `company`.`name`, `company`.`created_at`')->orderBy('`company`.`created_at` ASC');

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

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

        $searchModel = DepartmentCompany::find()->where(['>', '`department_company`.`user_id`', 0])->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere(['not', ['`department_company`.`remove_date`' => null]])->andWhere(['`company`.`type`' => $type])->andWhere(['`company`.`status`' => 2])->select('`department_company`.*, `company`.`id`, `company`.`name`, COUNT(Distinct `department_company`.`company_id`) as companyNum')->groupBy('`department_company`.`user_id`');

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'user_id'    => SORT_DESC,
            ]
        ];

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

        $searchModel = DepartmentCompany::find()->with('company')->where(['`department_company`.`user_id`' => $user_id])->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere(['not', ['`department_company`.`remove_date`' => null]])->andWhere(['`company`.`type`' => $type])->andWhere(['`company`.`status`' => 2])->select('`department_company`.*, `company`.`id`, `company`.`name`, `company`.`created_at`')->orderBy('`company`.`created_at` ASC');

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

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