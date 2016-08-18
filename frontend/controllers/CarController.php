<?php

namespace frontend\controllers;

use common\models\Act;
use common\models\Car;
use common\models\Company;
use common\models\search\ActSearch;
use common\models\search\CarSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use common\models\User;

/**
 * CarController implements the CRUD actions for Car model.
 */
class CarController extends Controller
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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'view', 'create', 'update', 'delete'],
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ]
                ]
            ]
        ];
    }

    public function actionDirty()
    {
        return $this->render('dirty');
    }

    /**
     * Lists all Car models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new CarSearch();
        if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
            $searchModel->company_id = Yii::$app->user->identity->company->id;
        } else {
            $searchModel->company_id = Company::find()->with('cars')->one()->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $companyDropDownData = Company::dataDropDownList();

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'companyDropDownData' => $companyDropDownData,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_CLIENT]);
        $searchModel->number = $model->number;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
        ]);
    }

    /**
     * Creates a new Car model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Car();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->getRequest()->referrer);
        } else {
            return $this->goBack();
        }
    }

    /**
     * Updates an existing Car model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/company/update', 'id' => $model->company_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionUpload()
    {
        return $this->render('upload');
    }

    /**
     * Deletes an existing Car model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->getRequest()->referrer);
    }

    /**
     * Finds the Car model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Car the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Car::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
