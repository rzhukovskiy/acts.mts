<?php

namespace backend\controllers;

use common\models\Department;
use common\models\Message;
use common\models\search\MessageSearch;
use common\models\User;
use yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;

/**
 * MessageController implements the CRUD actions for Message model.
 */
class MessageController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionList($department_id, $type = 'inbox')
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;
        $searchModel = new MessageSearch();
        $searchModel->department_id = $department_id;
        if ($type == 'outbox') {
            $dataProvider = $searchModel->searchOutboxByUser($currentUser);
        } else {
            $dataProvider = $searchModel->searchInboxByUser($currentUser);
        }

        $currentDepartment = Department::findOne($department_id);
        if (!$currentDepartment){
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $listUser = User::find()
            ->alias('user')
            ->joinWith('department department')
            ->where(['department_id' => $department_id])
            ->andwhere(['!=', 'user.id', $currentUser->id])
            ->select(['username', 'user.id'])
            ->indexBy('id')
            ->column();
        
        $model = new Message();
        $model->user_from = $currentUser->id;

        Url::remember();

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'listUser' => $listUser,
            'model' => $model,
            'type' => $type,
            'department_id' => $department_id,
        ]);
    }
    public function actionView($id)
    {
        $model = $this->findModel($id);
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new MessageSearch();
        $searchModel->topic_id = $model->topic_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $newModel = new Message();
        $newModel->user_from = $currentUser->id;
        $newModel->topic_id = $model->topic_id;
        if ($currentUser->id == $model->user_from) {
            $newModel->user_to = $model->user_to;
            $recipient = $model->recipient;
        } else  {
            $newModel->user_to = $model->user_from;
            $recipient = $model->author;
        }

        Url::remember();
        Message::updateAll(['is_read' => 1], [
            'user_to' => $currentUser->id,
            'topic_id' => $model->topic_id,
        ]);

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $newModel,
            'recipient' => $recipient,
        ]);
    }

    /**
     * Creates a new Message model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Message();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Url::previous());
        } else {
            return $this->redirect(Url::previous());
        }
    }

    /**
     * Updates an existing Message model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Message model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Message model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Message the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
