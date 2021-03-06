<?php

namespace backend\controllers;

use common\models\Company;
use common\models\CompanyOffer;
use common\models\search\CompanyOfferSearch;
use common\models\User;
use yii;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $checkClearCommStr = false;

            if(isset(Yii::$app->request->post()['CompanyOffer']['communication_str'])) {
                if(Yii::$app->request->post()['CompanyOffer']['communication_str'] == '') {
                    $checkClearCommStr = true;
                }
            }

            if ($checkClearCommStr == true) {
                $model->communication_at = '';
                if($model->save()) {
                    return json_encode(['output' => '', 'message' => '']);
                } else {
                    return ['message' => '???? ????????????????????'];
                }

            } else if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $output = [];
                foreach (Yii::$app->request->post('CompanyOffer') as $name => $value) {
                    if ($name == 'process') {
                        $output[] = $model->getProcessHtml();
                    } elseif($name == 'communication_str') {

                        // ?????????????????? ???????? ???????????? ???????? ????????. ??????????
                        $wekCommunicDate = '';

                        if (isset($value)) {

                            if (mb_strlen($value) > 1) {

                                try {
                                    $CommunicDate = strtotime($value);
                                    $wekCommunicDate = date("w", $CommunicDate);

                                    switch ($wekCommunicDate) {
                                        case 1:
                                            $wekCommunicDate = '??????????????????????';
                                            break;
                                        case 2:
                                            $wekCommunicDate = '??????????????';
                                            break;
                                        case 3:
                                            $wekCommunicDate = '??????????';
                                            break;
                                        case 4:
                                            $wekCommunicDate = '??????????????';
                                            break;
                                        case 5:
                                            $wekCommunicDate = '??????????????';
                                            break;
                                        case 6:
                                            $wekCommunicDate = '??????????????';
                                            break;
                                        case 7:
                                            $wekCommunicDate = '??????????????????????';
                                            break;
                                    }

                                    $wekCommunicDate = $value . ' (' . $wekCommunicDate . ')';
                                } catch (\Exception $e) {
                                    $wekCommunicDate = $value;
                                }

                            } else {
                                $wekCommunicDate = $value;
                            }

                        } else {
                            $wekCommunicDate = $value;
                        }

                        $output[] = $wekCommunicDate;

                    } else {
                        $output[] = $value;
                    }
                }
                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => '???? ????????????????????'];
            }
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
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
        ])->andWhere(['<', 'communication_at', time() - 300])->one();

        if($modelCompanyOffer) {
            return Json::encode([
                'id' => $modelCompanyOffer->id,
                'title' => '?????????????????????????????? ???????????? ?? ' . $modelCompanyOffer->company->name,
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

    public function actionMailstatus()
    {
        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');
            $value = 1;

            if((Yii::$app->request->post('value') == 0) || (Yii::$app->request->post('value') == 1)) {
                $value = Yii::$app->request->post('value');
            }

            $model = CompanyOffer::findOne(['company_id' => $id]);

            $model->email_status = $value;

            if($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }
    }

}
