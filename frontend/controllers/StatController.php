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
                        'actions' => ['view', 'month', 'day', 'total', 'act'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER]
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

    // Акты компании с учетом типа или без
    // Выбор шаблона в зависимости от типа компании
    // $type - service_type
    public function actionView($id = null, $type = null, $group = null)
    {
        $viewName = $this->selectTemplate();

        $searchModel = new ActSearch();
        $searchModel->load(Yii::$app->request->queryParams);
        $searchModel->scenario = 'statistic_client_filter';

        /** @var Company $companyModel */
        $id = $id ? $id : $searchModel->client_id;
        $companyModel = $this->findCompanyModel($id);

        $this->view->title = 'Статистика "' . $companyModel->name;

        $dataProvider = $searchModel->searchTypeByMonth(Yii::$app->request->queryParams);

        if (!is_null($type))
            $dataProvider->query->andWhere(['service_type' => $type]);

        // Акты разные для партнера и клиента, уточняем что выбирать
        if ($companyModel->type == Company::TYPE_OWNER) {
            $dataProvider->query
                ->andWhere(['client.parent_id' => $companyModel->id])->orWhere(['client_id' => $companyModel->id])
                ->joinWith('client client');
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
        $id = $id ? $id : $searchModel->client_id;
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
                ->andWhere(['client.parent_id' => $companyModel->id])->orWhere(['client_id' => $companyModel->id])
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

        $id = $id ? $id : $searchModel->client_id;
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
                ->andWhere(['client.parent_id' => $companyModel->id])->orWhere(['client_id' => $companyModel->id])
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

        $dataProvider = $searchModel->searchTotal(Yii::$app->request->queryParams);
        $chartDataProvider = $searchModel->searchTotal(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $dataProvider->query->with(['client']);

        /** @var User $identity */
        $identity = Yii::$app->user->identity;
        if ($identity->role == User::ROLE_CLIENT) {
            $dataProvider->query->andWhere(['client_id' => $identity->company_id]);
            $chartDataProvider->query->andWhere(['client_id' => $identity->company_id]);
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