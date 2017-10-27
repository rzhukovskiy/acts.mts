<?php
namespace api\controllers;

use api\models\ApiToken;
use common\models\Act;
use common\models\Car;
use common\models\Company;
use common\models\Mark;
use common\models\Type;
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
                'only' => ['count', 'getcardata', 'checknumber'],
                'rules' => [
                    [
                        'actions' => ['count', 'getcardata', 'checknumber'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['count', 'getcardata', 'checknumber'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'count' => ['post', 'get'],
                    'getcardata' => ['post', 'get'],
                    'checknumber' => ['post', 'get'],
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

                    // подкомпании выбранной компании
                    $queryPar = Company::find()->where(['parent_id' => $company_filter])->select('id')->column();

                    $arrSubFilter = [];

                    for ($i = 0; $i < count($queryPar); $i++) {

                        $arrSubFilter[] = $queryPar[$i];

                        $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

                        for ($j = 0; $j < count($queryParPar); $j++) {
                            $arrSubFilter[] = $queryParPar[$j];
                        }

                    }

                    $ressArray = Car::find()->leftJoin('type', '`type`.`id` = `car`.`type_id`')->where(['OR', ['car.company_id' => $company_filter], ['car.company_id' => $arrSubFilter]])->select('type.name as name, count(car.id) as count')->orderBy('car.type_id')->groupBy('car.type_id')->asArray()->all();
                } else {
                    $ressArray = Car::find()->leftJoin('type', '`type`.`id` = `car`.`type_id`')->where(['OR', ['car.company_id' => $company_id], ['car.company_id' => $arrParParIds]])->select('type.name as name, count(car.id) as count')->orderBy('car.type_id')->groupBy('car.type_id')->asArray()->all();
                }

                // Название компаний
                $companyArray = Company::find()->innerJoin('car', '`car`.`company_id` = `company`.`id`')->where(['OR', ['company.id' => $company_id], ['company.id' => $arrParParIds]])->select('company.name as name, company.id as id')->orderBy('company.id')->asArray()->all();

                return json_encode(['result' => json_encode($ressArray), 'company' => json_encode($companyArray), 'error' => 0]);

            } else {
                return json_encode(['error' => 1]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    public function actionGetcardata()
    {

        /*$company_id = Yii::$app->request->get("company_id");
        $type = Yii::$app->request->get("type");

        if($type > 1) {

            $markList = Mark::find()->select('id, name')->orderBy('id')->asArray()->all();
            $typeList = Type::find()->innerJoin('company_service', '`company_service`.`type_id` = `type`.`id` AND `company_service`.`company_id` =' . $company_id)->select('type.id, type.name')->orderBy('type.id')->asArray()->all();

            return json_encode(['mark' => json_encode($markList), 'type' => json_encode($typeList), 'error' => 0]);

        } else {
            return json_encode(['error' => 1]);
        }*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");

            // Проверяем отображать ли кнопку для закрытия загрузки
            $canCloseLoad = 1;

            // Текушая дата
            $dateNow = time();

            // Текущий день недели
            $dayNow = date("j", $dateNow);

            if (($dayNow >= 1) && ($dayNow < 15)) {
                $canCloseLoad = ActController::checkCanCloseLoad($type, $company_id);
            }
            // Проверяем отображать ли кнопку для закрытия загрузки

            if($type > 1) {

                $markList = Mark::find()->select('id, name')->orderBy('id')->asArray()->all();
                $typeList = Type::find()->innerJoin('company_service', '`company_service`.`type_id` = `type`.`id` AND `company_service`.`company_id` =' . $company_id)->select('type.id, type.name')->orderBy('type.id')->asArray()->all();

                return json_encode(['mark' => json_encode($markList), 'type' => json_encode($typeList), 'error' => 0, 'closeButt' => $canCloseLoad]);

            } else {
                return json_encode(['error' => 1]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    public function actionChecknumber()
    {

        /*$number = Yii::$app->request->get("number");

        $carRes = Car::find()->where(['number' => $number])->select('mark_id, type_id')->asArray()->all();

        if(count($carRes) > 0) {

            if((isset($carRes[0]['mark_id'])) && (isset($carRes[0]['type_id']))) {

                if(($carRes[0]['mark_id'] > 0) && ($carRes[0]['type_id'] > 0)) {
                    return json_encode(['error' => 0, 'mark_id' => $carRes[0]['mark_id'], 'type_id' => $carRes[0]['type_id']]);
                } else {
                    return json_encode(['error' => 1]);
                }

            } else {
                return json_encode(['error' => 1]);
            }

        } else {
            return json_encode(['error' => 1]);
        }*/

        if ((Yii::$app->request->post("token")) && (Yii::$app->request->post("user_id")) && (Yii::$app->request->post("number"))) {

            $token = Yii::$app->request->post("token");
            $user_id = Yii::$app->request->post("user_id");
            $number = Yii::$app->request->post("number");

            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $modelToken = ApiToken::findOne(['token' => $token]);

            if((isset($modelToken)) && (isset($modelToken->user_id)) && (isset($modelToken->expired_at))) {

                $timenow = time();

                if($timenow >= $modelToken->expired_at) {
                    // Удаление токена
                    $modelToken->delete();
                    return json_encode(['error' => 2]);
                } else if($modelToken->user_id != $user_id) {
                    return json_encode(['error' => 2]);
                } else {

                    $carRes = Car::find()->where(['number' => $number])->select('mark_id, type_id')->asArray()->all();

                    if (count($carRes) > 0) {

                        if ((isset($carRes[0]['mark_id'])) && (isset($carRes[0]['type_id']))) {

                            if (($carRes[0]['mark_id'] > 0) && ($carRes[0]['type_id'] > 0)) {
                                return json_encode(['error' => 0, 'mark_id' => $carRes[0]['mark_id'], 'type_id' => $carRes[0]['type_id']]);
                            } else {
                                return json_encode(['error' => 1]);
                            }

                        } else {
                            return json_encode(['error' => 1]);
                        }

                    } else {
                        return json_encode(['error' => 1]);
                    }

                }

            } else {
                return json_encode(['error' => 2]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}