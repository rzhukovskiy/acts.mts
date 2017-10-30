<?php
namespace api\controllers;

use common\models\Act;
use common\models\Company;
use common\models\CompanyDriver;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


class DriversController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['list'],
                'rules' => [
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'list' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionList()
    {

        /*$company_id = Yii::$app->request->get("company_id");
        $type = Yii::$app->request->get("type");

        if ($type == 1) {

            $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

            $arrParParIds = [];

            for ($i = 0; $i < count($queryPar); $i++) {

                $arrParParIds[] = $queryPar[$i];

                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                for ($j = 0; $j < count($queryParPar); $j++) {
                    $arrParParIds[] = $queryParPar[$j];
                }

            }

            $ressArray = CompanyDriver::find()->leftJoin('car', '`car`.`id` = `company_driver`.`car_id`')->leftJoin('mark', '`mark`.`id` = `car`.`mark_id`')->where(['OR', ['company_driver.company_id' => $company_id], ['company_driver.company_id' => $arrParParIds]])->select('company_driver.name as name, phone, company_driver.company_id as company_id, car.number as number, mark.name as mark')->orderBy('company_id')->asArray()->all();

            $companyArray = Company::find()->innerJoin('company_driver', '`company_driver`.`company_id` = `company`.`id`')->where(['OR', ['company.id' => $company_id], ['company.id' => $arrParParIds]])->select('company.name as name, company.id as id')->orderBy('company.id')->asArray()->all();

            return json_encode(['result' => json_encode($ressArray), 'company' => json_encode($companyArray)]);

        } else {
            return json_encode(['error' => 1]);
        }*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("page"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");

            $page = Yii::$app->request->post("page");
            $startLimit = 0;

            if($page > 1) {
                $startLimit = (($page - 1) * 50);
            }

            if ($type == 1) {

                $queryPar = Company::find()->where(['parent_id' => $company_id])->select('id')->column();

                $arrParParIds = [];

                for ($i = 0; $i < count($queryPar); $i++) {

                    $arrParParIds[] = $queryPar[$i];

                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                    for ($j = 0; $j < count($queryParPar); $j++) {
                        $arrParParIds[] = $queryParPar[$j];
                    }

                }

                // Данные водителей
                $ressArray = '';

                if(Yii::$app->request->post("filter")) {

                    $company_filter = Yii::$app->request->post("filter");

                    if(Yii::$app->request->post("search")) {
                        $searchText = Yii::$app->request->post("search");

                        $ressArray = CompanyDriver::find()->leftJoin('car', '`car`.`id` = `company_driver`.`car_id`')->leftJoin('mark', '`mark`.`id` = `car`.`mark_id`')->where(['company_driver.company_id' => $company_filter])->andWhere(['OR', ['like', 'company_driver.name', $searchText], ['like', 'car.number', $searchText]])->select('company_driver.name as name, phone, company_driver.company_id as company_id, car.number as number, mark.name as mark')->orderBy('company_id')->offset($startLimit)->limit(51)->asArray()->all();

                    } else {
                        $ressArray = CompanyDriver::find()->leftJoin('car', '`car`.`id` = `company_driver`.`car_id`')->leftJoin('mark', '`mark`.`id` = `car`.`mark_id`')->where(['company_driver.company_id' => $company_filter])->select('company_driver.name as name, phone, company_driver.company_id as company_id, car.number as number, mark.name as mark')->orderBy('company_id')->offset($startLimit)->limit(51)->asArray()->all();
                    }

                } else {

                    if(Yii::$app->request->post("search")) {
                        $searchText = Yii::$app->request->post("search");

                        $ressArray = CompanyDriver::find()->leftJoin('car', '`car`.`id` = `company_driver`.`car_id`')->leftJoin('mark', '`mark`.`id` = `car`.`mark_id`')->where(['OR', ['company_driver.company_id' => $company_id], ['company_driver.company_id' => $arrParParIds]])->andWhere(['OR', ['like', 'company_driver.name', $searchText], ['like', 'car.number', $searchText]])->select('company_driver.name as name, phone, company_driver.company_id as company_id, car.number as number, mark.name as mark')->orderBy('company_id')->offset($startLimit)->limit(51)->asArray()->all();

                    } else {
                        $ressArray = CompanyDriver::find()->leftJoin('car', '`car`.`id` = `company_driver`.`car_id`')->leftJoin('mark', '`mark`.`id` = `car`.`mark_id`')->where(['OR', ['company_driver.company_id' => $company_id], ['company_driver.company_id' => $arrParParIds]])->select('company_driver.name as name, phone, company_driver.company_id as company_id, car.number as number, mark.name as mark')->orderBy('company_id')->offset($startLimit)->limit(51)->asArray()->all();
                    }

                }

                // Название компаний
                $companyArray = Company::find()->innerJoin('company_driver', '`company_driver`.`company_id` = `company`.`id`')->where(['OR', ['company.id' => $company_id], ['company.id' => $arrParParIds]])->select('company.name as name, company.id as id')->orderBy('company.id')->asArray()->all();

                return json_encode(['result' => json_encode($ressArray), 'company' => json_encode($companyArray)]);

            } else {
                return json_encode(['error' => 1]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}