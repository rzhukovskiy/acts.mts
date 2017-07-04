<?php

namespace backend\controllers;

use Yii;
use common\models\CompanyMember;
use common\models\search\CompanyMemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CompanyMemberController implements the CRUD actions for CompanyMember model.
 */
class CompanyMemberController extends Controller
{
    /**
     * Lists all CompanyMember models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyMemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSend($id)
    {
        $model = $this->findModel($id);

        $plainTextContent = Yii::$app->request->post('text');
        $subject = Yii::$app->request->post('topic');
        $toEmail = $model->email;
        $toName = $model->name;

        /** @var SwiftMailer $SwiftMailer */
        $headers  = 'From: Международный Транспортный Сервис <notice@mtransservice.ru>' . "\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= "To: $toName <$toEmail>" . "\r\n";

        $res = mail($toEmail, $subject, $plainTextContent, $headers);

        return $this->redirect(['company/member', 'id' => $model->company_id]);
    }

    /**
     * Creates a new CompanyMember model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $company_id
     * @return mixed
     */
    public function actionCreate($company_id = null)
    {
        $model = new CompanyMember();
        $model->company_id = $company_id;

        $postArr = '';

        $postArr = Yii::$app->request->post();

        // Переводим email в нижний регистр
        if(isset($postArr['CompanyMember']['email'])) {
            $postArr['CompanyMember']['email'] = strtolower($postArr['CompanyMember']['email']);
        }

        if ($model->load($postArr) && $model->save()) {
            return $this->redirect(['company/member', 'id' => $model->company_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CompanyMember model.
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

    /**
     * Deletes an existing CompanyMember model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['/company/member', 'id' => $model->company_id]);
    }

    /**
     * Finds the CompanyMember model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CompanyMember the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyMember::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
