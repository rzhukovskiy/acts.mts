<?php
namespace api\controllers;

use common\models\Act;
use common\models\Company;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


class StatController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['list', 'month'],
                'rules' => [
                    [
                        'actions' => ['list', 'month'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['list', 'month'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['post', 'get'],
                    'month' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionError()
    {
        return $this->redirect("http://docs.mtransservice.ru/site/index");
    }

    public function actionList()
    {

        /*$company_id = Yii::$app->request->get("company_id");
        $type = Yii::$app->request->get("type");

        $ressArray = '';

        // Фильтр даты Только за текущий год
        $dateFrom = strtotime((((int) date('Y', time())) - 1) . '-12-31T21:00:00.000Z');
        $dateTo = strtotime(date('Y', time()) . '-12-31T21:00:00.000Z');

        if($type == 1) {

            $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

            $arrParParIds = [];

            for ($i = 0; $i < count($queryPar); $i++) {

                $arrParParIds[] = $queryPar[$i];

                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                for ($j = 0; $j < count($queryParPar); $j++) {
                    $arrParParIds[] = $queryParPar[$j];
                }

            }

            $ressArray = Act::find()->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(['between', 'served_at', $dateFrom, $dateTo])->select('COUNT(`act`.id) AS countServe, service_type, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['service_type', 'DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m")'])->orderBy('service_type, served_at')->asArray()->all();
        } else {
            $ressArray = Act::find()->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(['between', 'served_at', $dateFrom, $dateTo])->select('COUNT(`act`.id) AS countServe, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m")'])->asArray()->all();
        }

        return json_encode(['result' => json_encode($ressArray)]);*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");

            $ressArray = '';

            // Фильтр даты Только за текущий год
            $dateFrom = strtotime((((int) date('Y', time())) - 1) . '-12-31T21:00:00.000Z');
            $dateTo = strtotime(date('Y', time()) . '-12-31T21:00:00.000Z');

            if($type == 1) {

                $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

                $arrParParIds = [];

                for ($i = 0; $i < count($queryPar); $i++) {

                    $arrParParIds[] = $queryPar[$i];

                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                    for ($j = 0; $j < count($queryParPar); $j++) {
                        $arrParParIds[] = $queryParPar[$j];
                    }

                }

                $ressArray = Act::find()->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(['between', 'served_at', $dateFrom, $dateTo])->select('COUNT(`act`.id) AS countServe, service_type, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['service_type', 'DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m")'])->orderBy('service_type, served_at')->asArray()->all();
            } else {
                $ressArray = Act::find()->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(['between', 'served_at', $dateFrom, $dateTo])->select('COUNT(`act`.id) AS countServe, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m")'])->asArray()->all();
            }

            return json_encode(['result' => json_encode($ressArray)]);

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    public function actionMonth()
    {

        /*$company_id = Yii::$app->request->get("company_id");
        $type = Yii::$app->request->get("type");
        $act_type = Yii::$app->request->get("act_type");

        $period = Yii::$app->request->get("period");
        $period = explode('-', $period);

        if(count($period) > 1) {
            $periodM = $period[0];
            $periodY = $period[1];

            $ressArray = '';

            if($type == 1) {
                $ressArray = Act::find()->where(['client_id' => $company_id, 'service_type' => $act_type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $periodM, "YEAR(FROM_UNIXTIME(served_at))" => $periodY])->select('COUNT(`act`.id) AS countServe, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m-%d")'])->asArray()->all();
            } else {
                $ressArray = Act::find()->where(['partner_id' => $company_id, 'service_type' => $act_type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $periodM, "YEAR(FROM_UNIXTIME(served_at))" => $periodY])->select('COUNT(`act`.id) AS countServe, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m-%d")'])->asArray()->all();
            }

            return json_encode(['result' => json_encode($ressArray)]);

        } else {
            return json_encode(['error' => 1]);
        }*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("act_type")) && (Yii::$app->request->post("period"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $act_type = Yii::$app->request->post("act_type");

            $period = Yii::$app->request->post("period");
            $period = explode('-', $period);

            if(count($period) > 1) {
                $periodM = $period[0];
                $periodY = $period[1];

                $ressArray = '';

                if($type == 1) {

                    $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

                    $arrParParIds = [];

                    for ($i = 0; $i < count($queryPar); $i++) {

                        $arrParParIds[] = $queryPar[$i];

                        $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                        for ($j = 0; $j < count($queryParPar); $j++) {
                            $arrParParIds[] = $queryParPar[$j];
                        }

                    }

                    $ressArray = Act::find()->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(['service_type' => $act_type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $periodM, "YEAR(FROM_UNIXTIME(served_at))" => $periodY])->select('COUNT(`act`.id) AS countServe, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m-%d")'])->asArray()->all();
                } else {
                    $ressArray = Act::find()->where(['partner_id' => $company_id, 'service_type' => $act_type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $periodM, "YEAR(FROM_UNIXTIME(served_at))" => $periodY])->select('COUNT(`act`.id) AS countServe, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, served_at')->groupBy(['DATE_FORMAT(FROM_UNIXTIME(served_at), "%Y-%m-%d")'])->asArray()->all();
                }

                return json_encode(['error' => 0, 'result' => json_encode($ressArray)]);

            } else {
                return json_encode(['error' => 1]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}