<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\MonthlyAct;
use common\models\Plan;
use common\models\search\PlanSearch;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PlanController extends Controller
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
                        'actions' => ['list', 'create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'update'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_WATCHER],
                    ]
                ],
            ],
        ];
    }

    /**
     * @param bool $userId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionList($userId = false)
    {
        /**
         * @var $allUser  \common\models\User[]
         */
        $realUser = User::findOne(Yii::$app->user->id);
        if ($realUser->role == User::ROLE_ADMIN) {
            $allUser =
                User::find()->innerJoinWith('departments')->where(['{{%user}}.role' => User::ROLE_WATCHER])->indexBy('id')->all();
        } else {
            $allUser = [$realUser];
        }

        if (!$allUser) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if ($userId && isset($allUser[$userId])) {
            $user = $allUser[$userId];
        } else {
            $user = $allUser[0];
        }

        $userId = $user->id;
        $this->view->title = 'Планы сотрудника ' . $user->username;

        $searchModel = new PlanSearch();
        $searchModel->user_id = $userId;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new Plan([
            'user_id' => $user->id,
            'status'  => Plan::STATUS_NOT_DONE,
        ]);


        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'userId'       => $userId,
                'allUser'      => $allUser,
                'admin'        => $realUser->role == User::ROLE_ADMIN,
                'model'        => $model
            ]);
    }

    /**
     * @return yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Plan();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['list', 'userId' => $model->user_id]);
        } else {
            return $this->redirect(['list', 'userId' => $model->user_id]);
        }
    }


    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return ['message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        }
        $status = Yii::$app->request->post('status', false);
        $userId = Yii::$app->request->post('userId', false);
        if ($status) {
            $model->status = $status;
            $model->save();

            return $this->redirect(['list', 'userId' => $userId]);
        }

        return false;

    }

    /**
     * Deletes an existing Act model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Plan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Plan::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}