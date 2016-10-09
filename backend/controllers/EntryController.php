<?php

namespace backend\controllers;

use common\models\Card;
use common\models\User;
use Yii;
use common\models\Entry;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * EntryController implements the CRUD actions for Entry model.
 */
class EntryController extends Controller
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
                        'actions' => ['create', 'delete'],
                        'allow' => true,
                        'roles' => [User::ROLE_ACCOUNT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Creates a new Entry model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Entry();

        if ($model->load(Yii::$app->request->post())) {
            $model->card_id = Card::findOne($model->card_id)->id;
            if (!$model->save()) {
                $message = '';
                foreach ($model->getFirstErrors() as $error) {
                    $message .= ' ' . $error[0];
                }
                Yii::$app->session->setFlash('error', 'Ошибка: ' . $message);
            }
        }

        return $this->redirect([
            'wash/view',
            'id' => $model->company->id,
            'Entry[day]' => $model->day,
        ]);
    }

    /**
     * Deletes an existing Entry model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['wash/list']);
    }

    /**
     * Finds the Entry model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Entry the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Entry::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}