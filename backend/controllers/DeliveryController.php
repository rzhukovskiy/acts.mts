<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\Act;
use common\models\Company;
use common\models\CompanyInfo;
use common\models\Delivery;
use common\models\DepartmentLinking;
use common\models\HistoryChecks;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\data\ActiveDataProvider;

class DeliveryController extends Controller
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
                        'actions' => ['listchemistry', 'newchemistry', 'fullchemistry', 'updatechemistry', 'listchecks', 'newchecks', 'fullchecks', 'updatechecks', 'actcount'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['listchemistry', 'newchemistry', 'fullchemistry', 'updatechemistry', 'listchecks', 'newchecks', 'fullchecks', 'updatechecks', 'actcount'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER, User::ROLE_ACCOUNT, User::ROLE_MANAGER],
                    ],
                ],
            ],
        ];
    }

    public function actionListchemistry()
    {

        $searchModel = Delivery::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);


        return $this->render('/delivery/listchemistry', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,

        ]);

    }

    public function actionNewchemistry()
    {
        $model = new Delivery();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            return $this->redirect(['/delivery/listchemistry']);

        } else {
            return $this->render('/delivery/newchemistry', [
                'model' => $model,
            ]);
        }
    }

    public function actionFullchemistry($id)
    {
        $model = Delivery::findOne(['id' => $id]);

        return $this->render('/delivery/fullchemistry', [
            'model' => $model,
        ]);
    }

    public function actionUpdatechemistry($id)
    {
        $model = Delivery::findOne(['id' => $id]);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            foreach ($arrUpdate['Delivery'] as $name => $value) {
                if ($name == 'date_send') {
                    $arrUpdate['Delivery'][$name] = (String) strtotime($value);
                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }
    }
    public function actionListchecks()
    {
        $searchModel = HistoryChecks::find();
        $companyWash = Company::find()->where(['type' => 2])->andWhere(['OR', ['status' => Company::STATUS_ARCHIVE], ['status' => Company::STATUS_ACTIVE], ['status' => Company::STATUS_NEW]])->select('name')->indexby('id')->orderBy('name ASC')->column();
        $usersList = User::find()->select('username')->indexby('id')->column();

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);


        return $this->render('/delivery/listchecks', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'companyWash' => $companyWash,
            'usersList' => $usersList,

        ]);

    }

    public function actionNewchecks()
    {
        $model = new HistoryChecks();
        $model->user_id = Yii::$app->user->identity->id;

        $array =[];

        if (Yii::$app->request->post()) {
            $array = Yii::$app->request->post();
            $serialNumber = '';

            for ($i = 0; $i < count($array['HistoryChecks']['serial_number']); $i++) {
                if (($i + 1) < count($array['HistoryChecks']['serial_number'])) {
                    $serialNumber .= $array['HistoryChecks']['serial_number'][$i] . ', ';
                } else {
                    $serialNumber .= $array['HistoryChecks']['serial_number'][$i];
                }
            }

            $array['HistoryChecks']['serial_number'] = $serialNumber;
        }

        $companyWash = Company::find()->where(['type' => 2])->andWhere(['OR', ['status' => Company::STATUS_ARCHIVE], ['status' => Company::STATUS_ACTIVE], ['status' => Company::STATUS_NEW]])->select('name')->indexby('id')->orderBy('name ASC')->column();
        if ($model->load($array) && $model->save() && (Yii::$app->request->isPost)) {

            return $this->redirect(['/delivery/listchecks']);

        } else {
            return $this->render('/delivery/newchecks', [
                'model' => $model,
                'companyWash' => $companyWash,
            ]);
        }
    }

    public function actionFullchecks($id)
    {
        $searchModel = HistoryChecks::find()->where(['company_id' => $id]);
        $companyWash = Company::find()->where(['type' => 2])->andWhere(['OR', ['status' => Company::STATUS_ARCHIVE], ['status' => Company::STATUS_ACTIVE], ['status' => Company::STATUS_NEW]])->select('name')->indexby('id')->orderBy('name ASC')->column();
        $usersList = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER]])->select('username')->indexby('id')->column();

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        return $this->render('/delivery/fullchecks', [
            'dataProvider' => $dataProvider,
            'company_id' => $id,
            'companyWash' => $companyWash,
            'usersList' => $usersList,

        ]);
    }

    public function actionActcount()
    {

        if (Yii::$app->request->post('company_id') && Yii::$app->request->post('date')) {

            $company_id = Yii::$app->request->post('company_id');
            $date = Yii::$app->request->post('date');

            $count = Act::find()->where(['between', "served_at", $date, time()])->andWhere(['AND', ['partner_id' => $company_id], ['service_type' => Company::TYPE_WASH]])->count();

            if (isset($count)) {
                return json_encode(['result' => $count, 'success' => 'true']);
            } else {
                return json_encode(['success' => 'false']);
            }

        } else {
            return json_encode(['success' => 'false']);
        }

    }

    public function actionUpdatechecks($id)
    {
        $model = HistoryChecks::findOne(['id' => $id]);
        $companyWash = Company::find()->where(['type' => 2])->andWhere(['OR', ['status' => Company::STATUS_ARCHIVE], ['status' => Company::STATUS_ACTIVE], ['status' => Company::STATUS_NEW]])->select('name')->indexby('id')->orderBy('name ASC')->column();

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            foreach ($arrUpdate as $name => $value) {
                if ($name == 'date_send') {
                    $arrUpdate['HistoryChecks'][$name] = (String) strtotime($value);
                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];

                if (Yii::$app->request->post('HistoryChecks')) {

                    foreach (Yii::$app->request->post('HistoryChecks') as $name => $value) {

                        if ($name == 'company_id') {
                            $output[] = $companyWash[$value];
                        }

                    }
                }

                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }
    }

}