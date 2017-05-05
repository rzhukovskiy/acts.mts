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

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $output = [];
                foreach (Yii::$app->request->post('CompanyInfo') as $name => $value) {
                    $output[] = $value;
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

                if (($newDayType >= 0) && ($newDay >= 0)) {

                    if($newDayType == 0) {
                        $newDayType = ' банковских дней.';
                    } else {
                        $newDayType = ' календарных дней.';
                    }

                    $companyInfo = CompanyInfo::findOne($id);
                    $companyInfo->pay = $newDay . $newDayType;

                    if ($companyInfo->save()) {
                        return json_encode(['output' => $newDay . $newDayType, 'message' => '']);
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
