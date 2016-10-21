<?php

namespace frontend\controllers;

use common\components\ArrayHelper;
use common\models\Act;
use common\models\Car;
use common\models\Company;
use common\models\search\ActSearch;
use common\models\Service;
use common\models\Type;
use frontend\models\forms\carUploadXlsForm;
use frontend\models\search\CarSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use common\models\User;
use yii\web\UploadedFile;

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
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'act-view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['check-extra'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER],
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all Car models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_HISTORY]);

        if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
            $searchModel->client_id = Yii::$app->user->identity->company->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['!=', 'service_type', Service::TYPE_DISINFECT]);
        $dataProvider->query
            ->addSelect('client_id, act.number, act.mark_id, act.type_id, COUNT(act.id) as actsCount')
            ->groupBy('act.number');

        $companyDropDownData = Company::dataDropDownList();

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'companyDropDownData' => $companyDropDownData,
        ]);
    }

    public function actionDirty()
    {
        $searchModel = new CarSearch();

        if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
            $searchModel->client_id = Yii::$app->user->identity->company->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->with(['company', 'mark', 'type']);

        $dataProvider->pagination = [
            'pageSize' => 100,
        ];

        $companyDropDownData = Company::dataDropDownList();

        return $this->render('dirty', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            'companyDropDownData' => $companyDropDownData,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_CAR]);
        $searchModel->number = $model->number;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andFilterWhere(['!=', 'service_type', Service::TYPE_DISINFECT]);

        return $this->render('view', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    /**
     * Shows Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionActView($id)
    {
        $model = Act::findOne(['id' => $id]);

        return $this->render('act/view', [
            'model' => $model,
            'company' => 1,
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
            if (!Yii::$app->request->isAjax) {
                return $this->redirect(Yii::$app->getRequest()->referrer);
            }
        } else {
            if (!Yii::$app->request->isAjax) {
                return $this->goBack();
            }
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

    /**
     * Upload xlsx file and parse it
     *
     * @return string
     */
    public function actionUpload()
    {
        $model = new CarUploadXlsForm();
        $typeDropDownItems = ArrayHelper::map(Type::find()->all(), 'id', 'name');
        $companyDropDownItems = ArrayHelper::map(Company::find()->active()->active()->all(), 'id', 'name');

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->save()) {  // загрузка прошла успешно
                $query = Car::find()
                    ->andWhere(['in', 'id', $model->updatedIds]);

                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                ]);
                $dataProvider->pagination = false;

                $this->view->params['emptyText'] = '';
                if (!count($model->updatedIds))
                    $this->view->params['emptyText'] = "Ничего не добавлено.";

                if (!empty($updated))
                    $this->view->params['emptyText'] .= " Обновлено: " . $updated;

                return $this->render('upload/list', ['dataProvider' => $dataProvider]);
            }
        }

        return $this->render('upload', [
            'model' => $model,
            'typeDropDownItems' => $typeDropDownItems,
            'companyDropDownItems' => $companyDropDownItems,
        ]);
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

    public function actionCheckExtra($number)
    {
        $car = Car::findOne(['number' => $number]);
        if (!empty($car->company) && $car->company->is_split) {
            echo Json::encode(['res' => 1]);
        } else {
            echo Json::encode(['res' => 0]);
        }
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
