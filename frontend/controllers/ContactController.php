<?php

namespace frontend\controllers;

use common\models\Contact;
use common\models\search\ContactSearch;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


/**
 * ContactController implements the CRUD actions for Contact model.
 */
class ContactController extends Controller
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
                        'actions' => ['create', 'update', 'delete', 'newyear'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'newyear'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view', 'newyear'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_PARTNER, User::ROLE_CLIENT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionList($type)
    {
        $searchModel = new ContactSearch();
        $searchModel->type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new Contact();

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'model'        => $model,
                'admin'        => Yii::$app->user->can(User::ROLE_ADMIN),
            ]);
    }

    /**
     * Displays a single Contact model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view',
            [
                'model' => $this->findModel($id),
            ]);
    }

    /**
     * @param $type
     * @return \yii\web\Response
     */
    public function actionCreate($type)
    {
        $model = new Contact();

            $postArr = '';

            $postArr = Yii::$app->request->post();

            // Переводим email в нижний регистр
            if(isset($postArr['Contact']['email'])) {
                $postArr['Contact']['email'] = strtolower($postArr['Contact']['email']);
            }

        if ($model->load($postArr) && $model->save()) {
            return $this->redirect(['list', 'type' => $type]);
        } else {
            return $this->redirect(['list', 'type' => $type]);
        }
    }

    /**
     * Updates an existing Contact model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

            $postArr = '';

            $postArr = Yii::$app->request->post();

            // Переводим email в нижний регистр
            if(isset($postArr['Contact']['email'])) {
                $postArr['Contact']['email'] = strtolower($postArr['Contact']['email']);
            }

        if ($model->load($postArr) && $model->save()) {
            return $this->redirect(['list', 'type' => $model->type]);
        } else {
            return $this->render('update',
                [
                    'model' => $this->findModel($id),
                ]);
        }
    }

    /**
     * Deletes an existing Contact model.
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
     * Finds the Contact model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Contact the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Contact::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionNewyear()
    {

            return $this->render('newyear/newyear');

    }

}
