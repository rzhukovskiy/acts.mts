<?php

namespace frontend\controllers;

use common\models\User;
use frontend\traits\ChartTrait;
use frontend\widgets\datePeriod\DatePeriodWidget;
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

    // Списки партнеров или компаний по типам
    // $group [company, partner]
    public function actionList($type, $group)
    {
        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if((!isset($params['ActSearch']['dateFrom'])) && (!isset($params['ActSearch']['dateTo']))) {
            $params['ActSearch']['dateFrom'] = (((int) date('Y', time())) - 1) . '-12-31T21:00:00.000Z';
            $params['ActSearch']['dateTo'] = date('Y', time()) . '-12-31T21:00:00.000Z';
        }

        $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        $searchModel->dateTo = $params['ActSearch']['dateTo'];

        $dataProvider = $searchModel->searchByType($params);

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
        list($totalProfit, $totalServe, $totalExpense, $totalIncome) = $this->footerData($models);

        $formatter = Yii::$app->formatter;

        return $this->render('list', [
            'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
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

    // Списки партнеров или компаний по типам
    // $group [company, partner]
    public function actionListCommon()
    {
        $maximum = $step = $xFormat = $title = false;

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_filter';
        $searchModel->period = Yii::$app->request->get('period');
        $dataProvider = $searchModel->searchStatistic(Yii::$app->request->queryParams);

        /** @var Company $companyModel */
        $companyModel = Company::findOne($searchModel->client_id);

        $data = $dataProvider->query->asArray()->all();

        if (!empty($data)) {
            list($data, $maximum) = DatePeriodWidget::getDataForGraph($data);
            $xFormat = DatePeriodWidget::getXFormat($searchModel->period);
            list($maximum, $step) = DatePeriodWidget::getMaxAndStep($maximum);

            $title = 'Статистика';
            if (isset($companyModel->name)) {
                $title .= ' по компании ' . $companyModel->name;
            }
        }

        return $this->render('list_common',
            [
                'admin'       => Yii::$app->user->identity->role == User::ROLE_ADMIN,
                'searchModel' => $searchModel,
                'data'        => $data,
                'maximum'     => $maximum,
                'step'        => $step,
                'xFormat'     => $xFormat,
                'title'       => $title
            ]);
    }


    // Акты компании с учетом типа или без
    // Выбор шаблона в зависимости от типа компании
    // $type - service_type
    public function actionView($id = null, $type = null, $group = null)
    {
        $viewName = $this->selectTemplate();

        $searchModel = new ActSearch();

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if((!isset($params['ActSearch']['dateFrom'])) && (!isset($params['ActSearch']['dateTo']))) {
            $params['ActSearch']['dateFrom'] = (((int) date('Y', time())) - 1) . '-12-31T21:00:00.000Z';
            $params['ActSearch']['dateTo'] = date('Y', time()) . '-12-31T21:00:00.000Z';
        }

        $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        $searchModel->dateTo = $params['ActSearch']['dateTo'];

        $searchModel->load($params);
        $searchModel->scenario = 'statistic_client_filter';

        /** @var Company $companyModel */
        $id = $id ? $id : ($searchModel->client_id ? $searchModel->client_id : null);
        $companyModel = $this->findCompanyModel($id);

        $this->view->title = 'Статистика "' . $companyModel->name;

        $dataProvider = $searchModel->searchTypeByMonth($params);

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER) {

            if($companyModel->id == 154) {
                $dataProvider->query
                    ->andWhere(['client_id' => $companyModel->id])
                    ->joinWith('client client');
            } else {
                $dataProvider->query
                    ->andWhere('(`client`.`parent_id`=' . $companyModel->id . ') OR (`client_id`=' . $companyModel->id . ')')
                    ->joinWith('client client');
            }
        }
        else {
            $dataProvider->query
                ->andWhere(['partner_id' => $companyModel->id,])
                ->with('partner');
        }

        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        list($totalProfit, $totalServe, $totalExpense, $totalIncome) = $this->footerData($models);

        // Данные для графика генерим по ролям, целевое значение для ролей разное
        $chartData = $this->chartByMonthRoles($models);

        $formatter = Yii::$app->formatter;

        return $this->render('view/' . $viewName, [
            'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            'group' => $group,
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
    public function actionMonth($date, $id = null, $type = null, $group = null)
    {
        $viewName = $this->selectTemplate();

        $searchModel = new ActSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchByDays(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere([
                "MONTH(FROM_UNIXTIME(served_at))" => date('m', strtotime($date)),
                "YEAR(FROM_UNIXTIME(served_at))" => date('Y', strtotime($date)),
            ]);

        /** @var Company $companyModel */
        $id = $id ? $id : ($searchModel->client_id ? $searchModel->client_id : null);
        $companyModel = $this->findCompanyModel($id);
        $this->view->title = 'Статистика "' . $companyModel->name . '" за ' . DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date));

        if (Yii::$app->user->identity->role == User::ROLE_PARTNER)
            $type = Yii::$app->user->identity->company->type;

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER) {
            $dataProvider->query
                ->addSelect('client_id')
                ->andWhere(['or', ['client.parent_id' => $companyModel->id], ['client_id' => $companyModel->id]])
                ->joinWith('client client');
        }
        else {
            $dataProvider->query
                ->addSelect('partner_id')
                ->andWhere(['partner_id' => $companyModel->id,])
                ->with('partner');
        }

        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();
        list($totalProfit, $totalServe, $totalExpense, $totalIncome) = $this->footerData($models);

        // Данные для графика генерим по ролям, целевое значение для ролей разное
        $chartData = $this->chartDataByDayRoles($models, $date);

        $formatter = Yii::$app->formatter;

        return $this->render('month/' . $viewName, [
            'group' => $group,
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

        $searchModel = new ActSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->service_type = $type;
        $searchModel->scenario = 'statistic_partner_filter';

        $id = $id ? $id : ($searchModel->client_id ? $searchModel->client_id : null);
        $companyModel = $this->findCompanyModel($id);
        $this->view->title = 'Статистика "' . $companyModel->name . '" за ' . date('d', strtotime($date)) . ' ' . DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date));

        $dataProvider = $searchModel->searchDayCars(Yii::$app->request->queryParams);
        $dataProvider->query
            ->andWhere(["DATE(FROM_UNIXTIME(served_at))" => $date,]);

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER)
            $dataProvider->query
                ->addSelect('client_id')
                ->andWhere(['or', ['client.parent_id' => $companyModel->id], ['client_id' => $companyModel->id]])
                ->joinWith('client client');
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
    public function actionAct($id, $group = null)
    {
        if (($actModel = Act::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist.');

        return $this->render('act', [
            'model' => $actModel,
            'group' => $group,
        ]);
    }

    // Общая статистика
    // выбор шаблона в завиимости от типа компании
    // Админу все показать
    // Клиенту показать расход по машинам
    // Партнеру показать доход по компаниям
    public function actionTotal($group = null)
    {
        $viewName = $this->selectTemplate();

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if((!isset($params['ActSearch']['dateFrom'])) && (!isset($params['ActSearch']['dateTo']))) {
            $params['ActSearch']['dateFrom'] = (((int) date('Y', time())) - 1) . '-12-31T21:00:00.000Z';
            $params['ActSearch']['dateTo'] = date('Y', time()) . '-12-31T21:00:00.000Z';
        }

        $searchModel->dateFrom = $params['ActSearch']['dateFrom'];
        $searchModel->dateTo = $params['ActSearch']['dateTo'];

        $dataProvider = $searchModel->searchTotal($params);
        $chartDataProvider = $searchModel->searchTotal($params);
        $dataProvider->pagination = false;
        $dataProvider->query->joinWith(['client client']);
        $chartDataProvider->query->joinWith(['client client']);

        /** @var User $identity */
        $identity = Yii::$app->user->identity;
        if ($identity->role == User::ROLE_CLIENT) {
            $dataProvider->query
                ->andWhere(['or', ['client.parent_id' => $identity->company_id], ['client_id' => $identity->company_id]]);
            $chartDataProvider->query
                ->andWhere(['or', ['client.parent_id' => $identity->company_id], ['client_id' => $identity->company_id]]);
        }
        if ($identity->role == User::ROLE_PARTNER) {
            $dataProvider->query->andWhere(['partner_id' => $identity->company_id]);
            $chartDataProvider->query->andWhere(['partner_id' => $identity->company_id]);
        }

        $models = $dataProvider->getModels();

        list($totalProfit, $totalServe, $totalExpense, $totalIncome) = $this->footerData($models);

        $formatter = Yii::$app->formatter;

        return $this->render('total/' . $viewName, [
            'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            'group' => $group,
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
     * @throws NotFoundHttpException
     * @return string
     */
    private function selectTemplate()
    {
        /** @var User $userIdentity */
        $userIdentity = Yii::$app->user->getIdentity();
        $userRole = $userIdentity->role;

        switch ($userRole) {
            case User::ROLE_ADMIN :
                $view = 'admin';
                break;
            case User::ROLE_WATCHER :
                $view = 'admin';
                break;
            case User::ROLE_MANAGER :
                $view = 'admin';
                break;
            case User::ROLE_CLIENT:
                $view = 'client';
                break;
            case User::ROLE_PARTNER:
                if ($userIdentity->company->type == Company::TYPE_UNIVERSAL)
                    $view = 'universal';
                else
                    $view = 'partner';
                break;
            default:
                throw new NotFoundHttpException('Не могу выбрать способ отображения данных.');
        }

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

    /**
     * Collect footer data
     *
     * @param $models
     * @return array
     */
    private function footerData($models)
    {
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        return [$totalProfit, $totalServe, $totalExpense, $totalIncome];
    }
}