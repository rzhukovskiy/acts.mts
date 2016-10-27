<?php

namespace backend\controllers;

use common\models\Company;
use common\models\CompanyOffer;
use common\models\search\CompanyOfferSearch;
use common\models\User;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CompanyOfferController implements the CRUD actions for CompanyOffer model.
 */
class CompanyOfferController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Lists all CompanyOffer models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyOfferSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CompanyOffer model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CompanyOffer model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CompanyOffer();

        if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
            $model->user_id = Yii::$app->user->identity->id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CompanyOffer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelay($id)
    {
        $model = $this->findModel($id);
        $model->communication_at = time() + 300;

        if ($model->save()) {
            return Json::encode(['code' => 1]);
        } else {
            return Json::encode(['code' => 0]);
        }
    }

    /**
     * Deletes an existing CompanyOffer model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionGetAlert()
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;
        $modelCompanyOffer = CompanyOffer::find()->joinWith('company')->where([
            'user_id' => $currentUser->id,
            'status' => Company::STATUS_NEW,
        ])->where(['<', 'communication_at', time() - 300])->one();

        if($modelCompanyOffer) {
            return Json::encode([
                'id' => $modelCompanyOffer->id,
                'title' => 'Запланированный звонок в ' . $modelCompanyOffer->company->name,
                'content' => $this->renderPartial('_alert', [
                    'model' => $modelCompanyOffer,
                ]),
            ]);
        } else {
            return Json::encode([]);
        }
    }

    /**
     * Finds the CompanyOffer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CompanyOffer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyOffer::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
