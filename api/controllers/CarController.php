<?php
namespace api\controllers;

use common\models\Act;
use common\models\Car;
use common\models\Company;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


class CarController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['count'],
                'rules' => [
                    [
                        'actions' => ['count'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['count'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'count' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionCount()
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

            // Данные водителей
            $ressArray = '';

            if(Yii::$app->request->get("filter")) {
                $company_filter = Yii::$app->request->get("filter");
                $ressArray = Car::find()->leftJoin('type', '`type`.`id` = `car`.`type_id`')->where(['car.company_id' => $company_filter])->select('type.name as name, count(car.id) as count')->orderBy('car.type_id')->groupBy('car.type_id')->asArray()->all();
            } else {
                $ressArray = Car::find()->leftJoin('type', '`type`.`id` = `car`.`type_id`')->where(['OR', ['car.company_id' => $company_id], ['car.company_id' => $arrParParIds]])->select('type.name as name, count(car.id) as count')->orderBy('car.type_id')->groupBy('car.type_id')->asArray()->all();
            }

            // Название компаний
            $companyArray = Company::find()->innerJoin('car', '`car`.`company_id` = `company`.`id`')->where(['OR', ['company.id' => $company_id], ['company.id' => $arrParParIds]])->select('company.name as name, company.id as id')->orderBy('company.id')->asArray()->all();

            return json_encode(['result' => json_encode($ressArray), 'company' => json_encode($companyArray)]);

        } else {
            return json_encode(['error' => 1]);
        }*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");

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
                    $ressArray = Car::find()->leftJoin('type', '`type`.`id` = `car`.`type_id`')->where(['car.company_id' => $company_filter])->select('type.name as name, count(car.id) as count')->orderBy('car.type_id')->groupBy('car.type_id')->asArray()->all();
                } else {
                    $ressArray = Car::find()->leftJoin('type', '`type`.`id` = `car`.`type_id`')->where(['OR', ['car.company_id' => $company_id], ['car.company_id' => $arrParParIds]])->select('type.name as name, count(car.id) as count')->orderBy('car.type_id')->groupBy('car.type_id')->asArray()->all();
                }

                // Название компаний
                $companyArray = Company::find()->innerJoin('car', '`car`.`company_id` = `company`.`id`')->where(['OR', ['company.id' => $company_id], ['company.id' => $arrParParIds]])->select('company.name as name, company.id as id')->orderBy('company.id')->asArray()->all();

                return json_encode(['result' => json_encode($ressArray), 'company' => json_encode($companyArray)]);

            } else {
                return json_encode(['error' => 1]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}