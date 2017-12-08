<?php

namespace backend\controllers;

use Yii;
use common\models\CompanyInfo;
use common\models\search\CompanyInfoSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CompanyInfoController implements the CRUD actions for CompanyInfo model.
 */
class CompanyInfoController extends Controller
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

    /**
     * Lists all CompanyInfo models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyInfoSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CompanyInfo model.
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
     * Creates a new CompanyInfo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CompanyInfo();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CompanyInfo model.
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

            $postArr = '';

            $postArr = Yii::$app->request->post();

            // Переводим email в нижний регистр
            if(isset($postArr['CompanyInfo']['email'])) {
                $postArr['CompanyInfo']['email'] = strtolower($postArr['CompanyInfo']['email']);
            }

            if(isset($postArr['CompanyInfo']['geolocation'])) {
                unset($postArr['CompanyInfo']['geolocation']);
            }

            $checkGeoLocation = false;

            if((isset($postArr['CompanyInfo']['lat'])) && (isset($postArr['CompanyInfo']['lng']))) {
                if (($postArr['CompanyInfo']['lat']) && ($postArr['CompanyInfo']['lng'])) {
                    $checkGeoLocation = true;
                }
            }

            if ($model->load($postArr) && $model->save()) {
                $output = [];
                foreach ($postArr['CompanyInfo'] as $name => $value) {

                    // НДС
                    if($name == 'nds') {

                        if ($value == 0) {
                            $output[] = 'Нет';
                        } else {
                            $output[] = 'Да';
                        }
                    } elseif($name == 'geolocation') {
                    } elseif($name == 'lat') {
                    } elseif($name == 'lng') {
                    } else {
                        $output[] = $value;
                    }

                }

                if($checkGeoLocation == true) {
                    $output[] = $postArr['CompanyInfo']['lat'] . ':' . $postArr['CompanyInfo']['lng'];
                }

                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
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

    public function actionUpdatepay($id)
    {
        $hasEditable = Yii::$app->request->post('hasEditable');

        if($hasEditable == 1) {
            $newDayCont = Yii::$app->request->post('CompanyInfo');

            if((isset($newDayCont['payTypeDay'])) && (isset($newDayCont['payDay']))) {

                $newDayType = $newDayCont['payTypeDay'];
                $newDay = $newDayCont['payDay'];
                $newPrePaid = '';

                if($newDayType == 4) {
                    $newDay = 3;
                }

                if (($newDayType >= 0) && ($newDay >= 0)) {

                    if(isset($newDayCont['prePaid'])) {

                        if(($newDayCont['prePaid'] != '') && ($newDayCont['prePaid'] != ' ') && (($newDayType == 2) || ($newDayType == 3) || ($newDayType == 4)) && ($newDayCont['prePaid'] > 0)) {
                            $newPrePaid = ':' . $newDayCont['prePaid'];
                        }

                    }

                    $companyInfo = CompanyInfo::findOne($id);
                    $companyInfo->pay = $newDayType . ':' . $newDay . $newPrePaid;

                    if ($companyInfo->save()) {

                        $stringRes = '';

                        $arrPayData = explode(':', $newDayType . ':' . $newDay . $newPrePaid);

                        if(count($arrPayData) > 1) {

                            if($arrPayData[0] == 4) {
                                $stringRes = 'Аванс ' . $arrPayData[2] . ' руб.';
                            } else {

                                if (count($arrPayData) == 3) {
                                    $stringRes .= $arrPayData[2] . ' руб. + ';
                                }

                                if (($arrPayData[0] == 0) || ($arrPayData[0] == 2)) {
                                    $stringRes .= $arrPayData[1] . ' банковских дней';
                                } else {
                                    $stringRes .= $arrPayData[1] . ' календарных дней';
                                }

                            }

                        }

                        return json_encode(['output' => $stringRes, 'message' => '']);
                    } else {
                        return json_encode(['message' => 'не получилось']);
                    }

                } else {
                    return json_encode(['message' => 'не получилось']);
                }

            } else {
                return json_encode(['message' => 'не получилось']);
            }

        } else {
            return 1;
        }

    }

    public function actionUpdatetimelocation($id)
    {
        $hasEditable = Yii::$app->request->post('hasEditable');

        if($hasEditable == 1) {
            $newTimeCont = Yii::$app->request->post('CompanyInfo');

            if(isset($newTimeCont['time_location'])) {

                $newTime_location = $newTimeCont['time_location'];

                    $companyInfo = CompanyInfo::findOne($id);
                    $companyInfo->time_location = $newTime_location;

                    if ($companyInfo->save()) {

                        $stringRes = '';

                        $timeCompany = time() + (3600 * $newTime_location);

                        if($newTime_location == 0) {
                            $stringRes = date('H:i', $timeCompany);
                        } else {
                            if($newTime_location > 0) {
                                $stringRes = date('H:i', $timeCompany) . ' (' . '+' . $newTime_location . ')';
                            } else {
                                $stringRes = date('H:i', $timeCompany) . ' (' . $newTime_location . ')';
                            }
                        }

                        return json_encode(['output' => $stringRes, 'message' => '']);
                    } else {
                        return json_encode(['message' => 'не получилось']);
                    }

            } else {
                return json_encode(['message' => 'не получилось']);
            }

        } else {
            return 1;
        }

    }

    /**
     * Deletes an existing CompanyInfo model.
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
     * Finds the CompanyInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CompanyInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyInfo::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
