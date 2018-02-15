<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;


use common\models\Act;
use common\models\Car;
use common\models\Card;
use common\models\Company;
use common\models\Entry;
use common\models\EntryEvent;
use common\models\search\CompanySearch;
use common\models\search\EntrySearch;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class OrderController extends Controller
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
                        'actions' => ['list', 'view', 'archive', 'allarchive', 'compare'],
                        'allow' => true,
                        'roles' => [User::ROLE_ACCOUNT, User::ROLE_MANAGER, User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param integer $type
     * @return mixed
     */
    public function actionList($type)
    {
        $searchModel = new CompanySearch();
        $searchModel->type = $type;
        $searchModel->status = [Company::STATUS_ACTIVE, Company::STATUS_ARCHIVE];

        $params = Yii::$app->request->queryParams;

        // вывод названия компании
        $companyName = '';
        $cardNumber = 0;

        if(!isset($params['CompanySearch']['address'])) {
            $params['CompanySearch']['address'] = 'Архангельск';
        }

        // Получаем название компании по введенной карте
        if(isset($params['CompanySearch']['card_number'])) {
            $cardNumber = $params['CompanySearch']['card_number'];
        }

        if($cardNumber > 0) {
            $nameArr = Card::find()->innerJoin('company', 'company.id = card.company_id')->where(['number' => $cardNumber])->select('company.name')->asArray()->column();

            if(isset($nameArr[0])) {
                $companyName = ' для компании ' . $nameArr[0];
            }

        }
        // Получаем название компании по введенной карте

        $dataProvider = $searchModel->searchWithCard($params);

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
            }

        }
        // Подкатегории для сервиса

        $entrySearchModel = new EntrySearch();
        $entrySearchModel->load($params);
        if (empty($entrySearchModel->day)) {
            $entrySearchModel->day = date('d-m-Y');
        }

        $entryData = Yii::$app->request->get('Entry', false);
        $entryModel = null;
        if ($entryData) {
            $entryModel = Entry::findOne($entryData['id']);
        }

        $listCity = Company::find()->where(['OR', ['status' => Company::STATUS_ACTIVE], ['status' => Company::STATUS_ARCHIVE]])->andWhere(['type' => Company::TYPE_WASH])->groupBy('address')->select(['address', 'address'])->indexBy('address')->column();

        // Убираем пробел у Оренбурга
        $newListCity = [];

        foreach ($listCity as $key => $value) {
            if($key == 'Оренбург ') {
                $newListCity['Оренбург'] = 'Оренбург';
            } else {
                $newListCity[$key] = $value;
            }
        }

        $listCity = $newListCity;


        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'entrySearchModel' => $entrySearchModel,
            'entryModel' => $entryModel,
            'listCity' => $listCity,
            'companyName' => $companyName,
        ]);
    }

    /**
     * @param integer $type
     * @return mixed
     */
    public function actionArchive($type)
    {
        $searchModel = new EntrySearch();
        $searchModel->service_type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy('created_at');
        
        $listCity = Company::find()->active()->andWhere(['type' => Company::TYPE_WASH])->groupBy('address')->select(['address', 'address'])->indexBy('address')->column();
        return $this->render('archive', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'listCity' => $listCity,
        ]);
    }

    public function actionAllarchive()
    {
        $searchModel = new EntrySearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->orderBy('created_at');

        $listCity = Company::find()->active()->andWhere(['type' => Company::TYPE_WASH])->groupBy('address')->select(['address', 'address'])->indexBy('address')->column();
        return $this->render('allarchive', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'listCity' => $listCity,
        ]);
    }

    /**
     * Shows an existing Company model.
     * @param integer $id
     * @param integer $card_number
     * @return mixed
     */
    public function actionView($id, $card_number = null)
    {
        $model = $this->findModel($id);
        $entryData = Yii::$app->request->get('Entry', false);
        if (!empty($entryData['id']) and $modelEntry = Entry::findOne($entryData['id'])) {
            $modelEntry->load(Yii::$app->request->queryParams);
            $modelEntry->company_id = $model->id;
        } else {
            $modelEntry = new Entry();
            $modelEntry->load(Yii::$app->request->queryParams);
            $modelEntry->company_id = $model->id;
            $modelEntry->service_type = $model->type;
        }
        $modelCard = Card::findOne(['number' => $card_number]);

        if ($modelCard) {
            /** @var Act $modelAct */
            $modelAct = Act::find()->where(['card_id' => $modelCard->id])->select(['*', 'COUNT(id) AS count'])->groupBy('car_number')->orderBy('count DESC')->one();
            if ($modelAct) {
                $modelEntry->card_id = $modelAct->card_id;
                $modelEntry->number  = $modelAct->car_number;
                $modelEntry->mark_id = $modelAct->mark_id;
                $modelEntry->type_id = $modelAct->type_id;
            }
        }

        $entrySearchModel = new EntrySearch();
        $entrySearchModel->day = $modelEntry->day;
        $entrySearchModel->company_id = $modelEntry->company_id;

        return $this->render('view', [
            'model' => $model,
            'modelEntry' => $modelEntry,
            'searchModel' => $entrySearchModel,
        ]);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionCompare()
    {
        if (Yii::$app->request->post('arrMonth') || Yii::$app->request->post('arrYear') || Yii::$app->request->post('arrMonthYears') || Yii::$app->request->post('arrDay') || Yii::$app->request->post('arrDayCount')) {
            $arrMonth = json_decode(Yii::$app->request->post("arrMonth"));
            $arrMonthYears = json_decode(Yii::$app->request->post("arrMonthYears"));
            $arrYear = json_decode(Yii::$app->request->post("arrYear"));
            $Day = json_decode(Yii::$app->request->post("arrDay"));
            $arrDayCount = json_decode(Yii::$app->request->post("arrDayCount"));
            $ressArray =[];

            if (count($arrMonth) > 0) {
                for ($i = 0; $i < count($arrMonth); $i++) {

                    $query = Yii::$app->db->createCommand("SELECT COUNT(id) AS countServe, `service_type`, created_at AS served_at FROM `entry` WHERE MONTH(FROM_UNIXTIME(created_at)) =" . $arrMonth[$i] . " AND YEAR(FROM_UNIXTIME(created_at)) =" . $arrMonthYears[$i] . " GROUP BY `service_type`");
                    $queryArray = $query->queryAll();

                    for ($j = 0; $j < count($queryArray); $j++) {
                        $arr = $queryArray[$j];
                        $index = $arr['service_type'];
                        $indexM = $arrMonth[$i];
                        $ressArray[$index][$indexM]['countServe'] = $arr['countServe'];
                        $ressArray[$index][$indexM]['served_at'] = $arr['served_at'];
                    }
                }
            } else if ($Day) {
                $dataStart = date("Y-m-01", strtotime($Day));

                 $query = Yii::$app->db->createCommand("SELECT COUNT(id) AS countServe, `service_type`, created_at AS served_at FROM `entry` WHERE (DATE (FROM_UNIXTIME(created_at)) BETWEEN '". $dataStart ."T00:00:00.000Z' AND '". $Day ."T23:59:59.000Z') GROUP BY DAY(FROM_UNIXTIME(created_at)), `service_type` ORDER BY created_at ASC");
                 $queryArray = $query->queryAll();

                for ($j = 0; $j < count($queryArray); $j++) {
                    $arr = $queryArray[$j];
                    $index = $arr['service_type'];
                    $indexM = date("j", $arr['served_at']);
                    $ressArray[$index][$indexM]['countServe'] = $arr['countServe'];
                    $ressArray[$index][$indexM]['served_at'] = $arr['served_at'];
                }
            } else if (count($arrDayCount) > 0) {
                for ($i = 0; $i < count($arrDayCount); $i++) {

                    $dataStart = date("Y-m-01", strtotime($arrDayCount[$i]));
                    $query = Yii::$app->db->createCommand("SELECT COUNT(id) AS countServe, `service_type`, created_at AS served_at FROM `entry` WHERE (DATE (FROM_UNIXTIME(created_at)) BETWEEN '". $dataStart ."T00:00:00.000Z' AND '". $arrDayCount[$i] ."T23:59:59.000Z') GROUP BY `service_type`");
                    $queryArray = $query->queryAll();

                    for ($j = 0; $j < count($queryArray); $j++) {
                        $arr = $queryArray[$j];
                        $index = $arr['service_type'];
                        $indexM = date("n", $arr['served_at']);
                        $ressArray[$index][$indexM]['countServe'] = $arr['countServe'];
                        $ressArray[$index][$indexM]['served_at'] = $arr['served_at'];
                        $ressArray[$index][$indexM]['day'] = date("j", strtotime($arrDayCount[$i]));
                    }
                }
            } else {
                for ($i = 0; $i < count($arrYear); $i++) {

                    $query = Yii::$app->db->createCommand("SELECT COUNT(id) AS countServe, `service_type`, created_at AS served_at FROM `entry` WHERE YEAR(FROM_UNIXTIME(created_at)) =" . $arrYear[$i] . " GROUP BY `service_type`");
                    $queryArray = $query->queryAll();

                    for ($j = 0; $j < count($queryArray); $j++) {
                        $arr = $queryArray[$j];
                        $index = $arr['service_type'];
                        $indexM = $arrYear[$i];
                        $ressArray[$index][$indexM]['countServe'] = $arr['countServe'];
                    }
                }
            }
            return json_encode(['result' => json_encode($ressArray), 'success' => 'true']);
        } else {
            return json_encode(['success' => 'false']);
        }
    }
}