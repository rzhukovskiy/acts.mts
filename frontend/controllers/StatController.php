<?php

namespace frontend\controllers;

use common\models\User;
use frontend\traits\ChartTrait;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Controller;
use frontend\models\search\ActSearch;
use common\models\Company;
use yii\helpers\ArrayHelper;
use common\components\DateHelper;
use frontend\models\Act;
use yii\web\NotFoundHttpException;

class StatController extends Controller
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
                        'actions' => ['view', 'month', 'day'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER]
                    ],
                    [
                        'actions' => ['view', 'month', 'day', 'total'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT]
                    ]
                ]
            ]
        ];
    }

    // Списки партнеров или компаний по типам
    // $group [company, partner]
    public function actionList($type, $group)
    {
        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchByType(Yii::$app->request->queryParams);

        // Уточнение для текущего набора данных
        $dataProvider->pagination = false;
        $dataProvider->query
            ->andWhere(['service_type' => $type]);

        if ($group == 'company')
            $dataProvider->query->groupBy('client_id');
        elseif ($group == 'partner')
            $dataProvider->query->groupBy('partner_id');
        else
            throw new InvalidParamException('Error, param not right.');

        // Установка заголовка страницы
        $this->view->title = Company::$listType[$type]['ru'] . '. Статистика';

        $models = $dataProvider->getModels();

        // Данные для подвала таблицы
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('list', [
            'group' => $group,
            'type' => $type,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asDecimal($totalProfit, 0),
            'totalIncome' => $formatter->asDecimal($totalIncome, 0),
            'totalExpense' => $formatter->asDecimal($totalExpense, 0),
        ]);
    }

    // Акты компании с учетом типа или без
    // Выбор шаблона в зависимости от типа компании
    // $type - service_type
    public function actionView($id = null, $type = null)
    {
        $viewName = $this->selectTemplate();

        /** @var Company $companyModel */
        $companyModel = $this->findCompanyModel($id);

        $this->view->title = 'Статистика "' . $companyModel->name;

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_client_filter';

        $dataProvider = $searchModel->searchTypeByMonth(Yii::$app->request->queryParams);

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER)
            $dataProvider->query
                ->andWhere(['client_id' => $companyModel->id,])
                ->with('client');
        else
            $dataProvider->query
                ->andWhere(['partner_id' => $companyModel->id,])
                ->with('partner');

        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        // ToDo: refactor this + formatter -> method footerData(Act $models):array
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        // ToDo: extract to method plz chartByMonthRoles(Act $models):array
        // Данные для графика генерим по ролям, целевое значение для ролей разное
        if (Yii::$app->user->identity->role == User::ROLE_CLIENT)
            $chartData = $this->chartByMonth($models, 'income');
        if (Yii::$app->user->identity->role == User::ROLE_PARTNER)
            $chartData = $this->chartByMonth($models, 'expense');
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN)
            $chartData = $this->chartByMonth($models);

        $formatter = Yii::$app->formatter;

        return $this->render('view/' . $viewName, [
            'model' => $companyModel,
            'modelType' => ($companyModel->type == Company::TYPE_OWNER) ? 'client' : 'partner',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asDecimal($totalProfit, 0),
            'totalIncome' => $formatter->asDecimal($totalIncome, 0),
            'totalExpense' => $formatter->asDecimal($totalExpense, 0),
        ]);
    }

    // Акты компании за выбраный месяц
    public function actionMonth($date, $id = null, $type = null)
    {
        $viewName = $this->selectTemplate();

        /** @var Company $companyModel */
        $companyModel = $this->findCompanyModel($id);
        $this->view->title = 'Статистика "' . $companyModel->name . '" за ' . DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date));

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchByDays(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere([
                "MONTH(FROM_UNIXTIME(served_at))" => date('m', strtotime($date)),
                "YEAR(FROM_UNIXTIME(served_at))" => date('Y', strtotime($date)),
            ]);

        if (Yii::$app->user->identity->role == User::ROLE_PARTNER)
            $type = Yii::$app->user->identity->company->type;

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER)
            $dataProvider->query
                ->addSelect('client_id')
                ->andWhere(['client_id' => $companyModel->id,])
                ->with('client');
        else
            $dataProvider->query
                ->addSelect('partner_id')
                ->andWhere(['partner_id' => $companyModel->id,])
                ->with('partner');

        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        // Данные для графика генерим по ролям, целевое значение для ролей разное
        if (Yii::$app->user->identity->role == User::ROLE_CLIENT)
            $chartData = $this->chartDataByDay($models, $date, 'income');
        if (Yii::$app->user->identity->role == User::ROLE_PARTNER)
            $chartData = $this->chartDataByDay($models, $date, 'expense');
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN)
            $chartData = $this->chartDataByDay($models, $date);

        $formatter = Yii::$app->formatter;

        return $this->render('month/' . $viewName, [
            'model' => $companyModel,
            'modelType' => ($companyModel->type == Company::TYPE_OWNER) ? 'client' : 'partner',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $chartData,
            'chartTitle' => DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date)),
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asDecimal($totalProfit, 0),
            'totalIncome' => $formatter->asDecimal($totalIncome, 0),
            'totalExpense' => $formatter->asDecimal($totalExpense, 0),
        ]);
    }

    // Акты компании за день
    public function actionDay($date, $id = null, $type = null)
    {
        $viewName = $this->selectTemplate();

        $companyModel = $this->findCompanyModel($id);
        $this->view->title = 'Статистика "' . $companyModel->name . '" за ' . date('d', strtotime($date)) . ' ' . DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date));

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';

        $dataProvider = $searchModel->searchDayCars(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere(["DATE(FROM_UNIXTIME(served_at))" => $date,]);

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER)
            $dataProvider->query
                ->addSelect('client_id')
                ->andWhere(['client_id' => $companyModel->id,])
                ->with('client');
        else
            $dataProvider->query
                ->addSelect('partner_id')
                ->andWhere(['partner_id' => $companyModel->id,])
                ->with('partner');

        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('day/' . $viewName, [
            'model' => $companyModel,
            'modelType' => ($companyModel->type == Company::TYPE_OWNER) ? 'client' : 'partner',
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalProfit' => $formatter->asDecimal($totalProfit, 0),
            'totalExpense' => $formatter->asDecimal($totalExpense, 0),
            'totalIncome' => $formatter->asDecimal($totalIncome, 0),
        ]);
    }

    // Акт
    public function actionAct($id)
    {
        if (($actModel = Act::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist.');

        $role = Yii::$app->user->identity->role;

        return $this->render('act', [
            'model' => $actModel,
            'role' => $role,
        ]);
    }

    // Общая статистика
    // выбор шаблона в завиимости от типа компании
    // Админу все показать
    // Клиенту показать расход по машинам
    // Партнеру показать доход по компаниям
    public function actionTotal()
    {
        $viewName = $this->selectTemplate();

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';

        $dataProvider = $searchModel->searchTotal(Yii::$app->request->queryParams);
        $chartDataProvider = $searchModel->searchTotal(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $dataProvider->query->with(['client']);

        if (Yii::$app->user->identity->role == User::ROLE_CLIENT) {
            $dataProvider->query->andWhere(['client_id' => Yii::$app->user->identity->company_id]);
        }

        $models = $dataProvider->getModels();

        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('total/' . $viewName, [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $this->chartTotal($chartDataProvider),
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asDecimal($totalProfit, 0),
            'totalIncome' => $formatter->asDecimal($totalIncome, 0),
            'totalExpense' => $formatter->asDecimal($totalExpense, 0),
        ]);
    }

    /**
     * Select template file by user role
     *
     * @throws InvalidParamException
     * @return string
     */
    private function selectTemplate()
    {
        /** @var User $userIdentity */
        $userIdentity = Yii::$app->user->getIdentity();
        $userRole = $userIdentity->role;

        if ($userRole == User::ROLE_CLIENT)
            $view = 'client';
        if ($userRole == User::ROLE_PARTNER)
            $view = 'partner';
        if ($userRole == User::ROLE_ADMIN)
            $view = 'admin';

        if (empty($view))
            throw new InvalidParamException('Error, identity error. ' . __CLASS__);

        return $view;
    }

    /**
     * Find Company model by id
     *
     * @param $id
     * @return null|static
     * @throws InvalidParamException
     * @throws NotFoundHttpException
     */
    private function findCompanyModel($id)
    {
        // для просмотра статистики клиента
        // id компании не передаю, беру из модели авторизованного пользователя
        if (is_null($id))
            $id = Yii::$app->user->identity->company_id;


        if (($model = Company::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist.');

        return $model;
    }
}