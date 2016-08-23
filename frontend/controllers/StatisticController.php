<?php

namespace frontend\controllers;

use common\models\Company;
use frontend\models\Act;
use frontend\traits\ChartTrait;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use frontend\models\search\ActSearch;
use yii\web\NotFoundHttpException;
use common\components\DateHelper;

class StatisticController extends Controller
{
    /**
     * Generate chart data
     */
    use ChartTrait;

    /**
     * @param null $type
     * @return string
     */
    public function actionList($type)
    {
        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchByType(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $dataProvider->query->andWhere(['service_type' => $type]);
        $this->view->title = Company::$listType[$type]['ru'] . '. Статистика';

        $models = $dataProvider->getModels();

        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('list', [
            'type' => $type,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalServe' => number_format($totalServe, 0, '', ' '),
            'totalProfit' => $formatter->asCurrency($totalProfit),
            'totalIncome' => $formatter->asCurrency($totalIncome),
            'totalExpense' => $formatter->asCurrency($totalExpense),
        ]);
    }

    /**
     * @param $id int Company::id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $companyModel = $this->findCompanyModel($id);

        $this->view->title = 'Статистика "' . $companyModel->name;

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchTypeByMonth(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['partner_id' => $id]);
        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('view', [
            'model' => $companyModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $this->chartByMonth($models),
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asCurrency($totalProfit),
            'totalIncome' => $formatter->asCurrency($totalIncome),
            'totalExpense' => $formatter->asCurrency($totalExpense),
        ]);
    }

    public function actionByDay($id, $date)
    {
        $companyModel = $this->findCompanyModel($id);
        $this->view->title = 'Статистика "' . $companyModel->name . '" за ' . DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date));

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchByDays(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $dataProvider->query
            ->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => date('m', strtotime($date))])
            ->andWhere(["YEAR(FROM_UNIXTIME(served_at))" => date('Y', strtotime($date))])
            ->andWhere(['partner_id' => $id]);

        $models = $dataProvider->getModels();
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('by-day', [
            'model' => $companyModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $this->chartDataByDay($models, $date),
            'chartTitle' => DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date)),
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asCurrency($totalProfit),
            'totalIncome' => $formatter->asCurrency($totalIncome),
            'totalExpense' => $formatter->asCurrency($totalExpense),
        ]);
    }

    public function actionByHours($id, $date)
    {
        $companyModel = $this->findCompanyModel($id);

        $this->view->title = 'Статистика "' . $companyModel->name . '" за ' . date('d', strtotime($date)) . ' ' . DateHelper::getMonthName($date, 0) . ' ' . date('Y', strtotime($date));

        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchDayCars(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;
        $dataProvider->query
            ->andWhere(["DATE(FROM_UNIXTIME(served_at))" => $date])
            ->andWhere(['partner_id' => $id]);

        $models = $dataProvider->getModels();

        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('by-hours', [
            'model' => $companyModel,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalProfit' => $formatter->asCurrency($totalProfit),
            'totalExpense' => $formatter->asCurrency($totalExpense),
            'totalIncome' => $formatter->asCurrency($totalIncome),
        ]);
    }

    public function actionViewAct($id)
    {
        if (($actModel = Act::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist.');

        return $this->render('view-act', [
            'model' => $actModel,
        ]);
    }

    public function actionTotal()
    {
        $searchModel = new ActSearch();
        $searchModel->scenario = 'statistic_partner_filter';
        $dataProvider = $searchModel->searchTotal(Yii::$app->request->queryParams);
        $chartDataProvider = $searchModel->searchTotal(Yii::$app->request->queryParams);
        $dataProvider->pagination = false;

        $models = $dataProvider->getModels();

        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        $totalServe = array_sum(ArrayHelper::getColumn($models, 'countServe'));
        $totalExpense = array_sum(ArrayHelper::getColumn($models, 'expense'));
        $totalIncome = array_sum(ArrayHelper::getColumn($models, 'income'));

        $formatter = Yii::$app->formatter;

        return $this->render('total', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'chartData' => $this->chartTotal($chartDataProvider),
            'totalServe' => $totalServe,
            'totalProfit' => $formatter->asCurrency($totalProfit),
            'totalIncome' => $formatter->asCurrency($totalIncome),
            'totalExpense' => $formatter->asCurrency($totalExpense),
        ]);
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    private function findCompanyModel($id)
    {
        if (($companyModel = Company::findOne($id)) == null)
            throw new NotFoundHttpException('The requested page does not exist.');

        return $companyModel;
    }

}