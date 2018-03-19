<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\search\TenderSearch;
use common\models\Tender;
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
                        'actions' => ['new', 'shownew', 'new2', 'shownew2', 'archive', 'showarchive', 'tender', 'showtender', 'compare'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['new', 'shownew', 'new2', 'shownew2', 'archive', 'showarchive', 'tender', 'showtender', 'compare'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['new', 'shownew', 'new2', 'shownew2', 'archive', 'showarchive', 'tender', 'showtender', 'compare'],
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

    public function actionNew2($type)
    {

        $searchModel = new DepartmentCompanySearch(['scenario' => 'new2']);
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

    public function actionShownew2($user_id, $type)
    {

        $searchModel = new DepartmentCompanySearch(['scenario' => 'shownew2']);
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

    // Тендеры
    public function actionTender($type)
    {

        $searchModel = new TenderSearch(['scenario' => 'activity']);

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if(!isset($params['TenderSearch']['dateFrom'])) {
            $params['TenderSearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['TenderSearch']['dateFrom'];
        }

        if(!isset($params['TenderSearch']['dateTo'])) {
            $params['TenderSearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['TenderSearch']['dateTo'];
        }
        $params['TenderSearch']['service_type'] = $type;
        // Если не выбран период то показываем только текущий год

        $dataProvider = $searchModel->search($params);

        // Список сотрудников
        $authorMembers = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['department_id' => $type])->select('user.username, user.id as id')->indexBy('id')->column();
        asort($authorMembers);
        // Список сотрудников

        $listType = Company::$listType;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'authorMembers' => $authorMembers,
            'listType' => $listType,
            'type' => $type,
        ]);

    }
    public function actionShowtender($user_id, $type)
    {

        $searchModel = new TenderSearch(['scenario' => 'activity']);

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if(!isset($params['TenderSearch']['dateFrom'])) {
            $params['TenderSearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['TenderSearch']['dateFrom'];
        }

        if(!isset($params['TenderSearch']['dateTo'])) {
            $params['TenderSearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['TenderSearch']['dateTo'];
        }
        $params['TenderSearch']['service_type'] = $type;
        $params['TenderSearch']['work_user_id'] = $user_id;
        // Если не выбран период то показываем только текущий год

        $dataProvider = $searchModel->search($params);

        // Список сотрудников
        $authorMembers = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['department_id' => $type])->select('user.username, user.id as id')->indexBy('id')->column();
        asort($authorMembers);
        // Список сотрудников

        $listType = Company::$listType;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'authorMembers' => $authorMembers,
            'listType' => $listType,
            'type' => $type,
        ]);

    }

    public function actionCompare()
    {

        if (Yii::$app->request->post('arrMonth') || Yii::$app->request->post('arrMonthYears') || Yii::$app->request->post('type') || Yii::$app->request->post('category')) {
            $arrMonth = json_decode(Yii::$app->request->post("arrMonth"));
            $arrMonthYears = json_decode(Yii::$app->request->post("arrMonthYears"));
            $type = json_decode(Yii::$app->request->post("type"));
            $category = json_decode(Yii::$app->request->post("category"));

            $ressArray = [];
            $queryArray = [];
            $text = '';

            for ($i = 0; $i < count($arrMonth); $i++) {
                if (strlen($arrMonth[$i]) < 2) {
                    $text = '0';
                }
                $date = $arrMonthYears[$i] . '-' . $text . $arrMonth[$i];
                $dateStart = $date . '-01T00:00:00.000Z';
                $dateFinal = $date . '-' . date('t', strtotime($date)) . 'T21:00:00.000Z';

                // категория заявки
                if ($category == 1) {
                $queryArray = DepartmentCompany::find()->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->where(['OR', ['AND', '`department_company`.`remove_date` IS NULL', '`company`.`status` = 1'], ['AND', '`department_company`.`remove_date` IS NOT NULL', '`company`.`status` = 2']])->andWhere('`department_company`.`user_id` > 0')->andWhere(['`company`.`type`' => $type])->andWhere(['department_company.type_user' => 0])->andWhere(['between', "DATE(FROM_UNIXTIME(`company`.`created_at`))", $dateStart, $dateFinal])->select('department_company.user_id, company.created_at AS served_at, COUNT(Distinct `department_company`.`company_id`) as countServe')->groupBy('`department_company`.`user_id`')->asArray()->all();
                }
                // категория заявки 2
                if ($category == 2) {
                    $queryArray = DepartmentCompany::find()->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere('`department_company`.`user_id` > 0')->andWhere(['`company`.`type`' => $type])->andWhere(['department_company.type_user' => 1])->andWhere(['between', "DATE(FROM_UNIXTIME(`company`.`created_at`))", $dateStart, $dateFinal])->select('department_company.user_id, company.created_at AS served_at, COUNT(Distinct `department_company`.`company_id`) as countServe')->groupBy('`department_company`.`user_id`')->asArray()->all();
                }
                // категория архив
                if ($category == 3) {
                    $queryArray = DepartmentCompany::find()->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere(['>', '`department_company`.`remove_id`', 0])->andWhere(['not', ['`department_company`.`remove_date`' => null]])->andWhere(['`company`.`type`' => $type])->andWhere(['OR', ['`company`.`status`' => 2], ['`company`.`status`' => 10]])->andWhere(['department_company.type_user' => 0])->andWhere(['between', "DATE(FROM_UNIXTIME(`department_company`.`remove_date`))", $dateStart, $dateFinal])->select('department_company.user_id, department_company.remove_date AS served_at, COUNT(Distinct `department_company`.`company_id`) as countServe')->groupBy('`department_company`.`remove_id`')->orderBy('COUNT(Distinct `department_company`.`company_id`) DESC')->asArray()->all();
                }
                // категория тендеры
                if ($category == 4) {
                    $queryArray = Tender::find()->innerJoin('department_user', '`department_user`.`user_id` = `tender`.`work_user_id`')->andWhere(['department_id' => $type])->andWhere(['between', "DATE(FROM_UNIXTIME(work_user_time))", $dateStart, $dateFinal])->groupBy('`tender`.`work_user_id`')->select('tender.work_user_id as user_id, work_user_time AS served_at, COUNT(Distinct `tender`.`id`) as countServe')->asArray()->all();
                }

                for ($j = 0; $j < count($queryArray); $j++) {
                    $arr = $queryArray[$j];
                    $index = $arr['user_id'];
                    $indexM = $arrMonth[$i];
                    $ressArray[$index][$indexM]['countServe'] = $arr['countServe'];
                    $ressArray[$index][$indexM]['served_at'] = $arr['served_at'];
                }
            }

            return json_encode(['result' => json_encode($ressArray), 'success' => 'true']);
        } else {
            return json_encode(['success' => 'false']);
        }

    }

}