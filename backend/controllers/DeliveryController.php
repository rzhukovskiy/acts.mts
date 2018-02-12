<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\Delivery;
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
                        'actions' => ['listchemistry', 'newchemistry', 'fullchemistry', 'updatechemistry'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['listchemistry', 'newchemistry', 'fullchemistry', 'updatechemistry'],
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
}