<?php
namespace api\controllers;

use common\models\Act;
use common\models\Company;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


class ActController extends Controller
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

/*        $company_id = Yii::$app->request->get("company_id");
        $type = Yii::$app->request->get("type");
        $monthFilter = Yii::$app->request->get("month");
        $yearFilter = Yii::$app->request->get("year");

        $ressArray = '';

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

            $ressArray = Act::find()->innerJoin('type', '`type`.`id` = `act`.`type_id`')->innerJoin('mark', '`mark`.`id` = `act`.`mark_id`')->innerJoin('card', '`card`.`id` = `act`.`card_id`')->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->asArray()->all();
        } else {
            $ressArray = Act::find()->innerJoin('type', '`type`.`id` = `act`.`type_id`')->innerJoin('mark', '`mark`.`id` = `act`.`mark_id`')->innerJoin('card', '`card`.`id` = `act`.`card_id`')->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->asArray()->all();
        }

        return json_encode(['result' => json_encode($ressArray)]);*/

        if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type")) && (Yii::$app->request->post("month")) && (Yii::$app->request->post("year"))) {

            $company_id = Yii::$app->request->post("company_id");
            $type = Yii::$app->request->post("type");
            $monthFilter = Yii::$app->request->post("month");
            $yearFilter = Yii::$app->request->post("year");

            $ressArray = '';

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

                $ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['OR', ['client_id' => $company_id], ['client_id' => $arrParParIds]])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->asArray()->all();
            } else {
                $ressArray = Act::find()->leftJoin('type', '`type`.`id` = `act`.`type_id`')->leftJoin('mark', '`mark`.`id` = `act`.`mark_id`')->leftJoin('card', '`card`.`id` = `act`.`card_id`')->where(['partner_id' => $company_id, 'service_type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(served_at))" => $monthFilter, "YEAR(FROM_UNIXTIME(served_at))" => $yearFilter])->select('service_type, type.name as carType, mark.name as carMark, car_number, card.number as card_number, expense, income, profit, served_at')->orderBy('service_type, served_at')->asArray()->all();
            }

            return json_encode(['result' => json_encode($ressArray)]);

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}