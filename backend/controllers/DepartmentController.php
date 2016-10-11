<?php

namespace backend\controllers;

use common\models\DepartmentCompanyType;
use common\models\query\DepartmentCompanyTypeQuery;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use common\models\Department;
use common\models\search\DepartmentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DepartmentController implements the CRUD actions for Department model.
 */
class DepartmentController extends Controller
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
                        'actions' => ['index', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Department models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new DepartmentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Department model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Department();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            foreach (Yii::$app->request->post('CompanyType') as $companyStatus => $companyTypeData) {
                foreach ($companyTypeData as $companyType => $value) {
                    $modelDepartmentCompanyType = new DepartmentCompanyType();
                    $modelDepartmentCompanyType->department_id = $model->id;
                    $modelDepartmentCompanyType->company_type = $companyType;
                    $modelDepartmentCompanyType->company_status = $companyStatus;
                    $modelDepartmentCompanyType->save();
                }
            }
            return $this->redirect(['index']);
        } else {
            return $this->redirect(['index']);
        }
    }

    /**
     * Updates an existing Department model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            DepartmentCompanyType::deleteAll(['department_id' => $model->id]);
            foreach (Yii::$app->request->post('CompanyType') as $companyStatus => $companyTypeData) {
                foreach ($companyTypeData as $companyType => $value) {
                    $modelDepartmentCompanyType = new DepartmentCompanyType();
                    $modelDepartmentCompanyType->department_id = $model->id;
                    $modelDepartmentCompanyType->company_type = $companyType;
                    $modelDepartmentCompanyType->company_status = $companyStatus;
                    $modelDepartmentCompanyType->save();
                }
            }
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Department model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Department model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Department the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Department::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
