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
use common\models\CarHistory;
use common\models\search\CarHistorySearch;
use common\models\DepartmentUserCompanyType;

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
                        'actions' => ['list', 'view', 'act-view', 'dirty','check-extra', 'history', 'movecar'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
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
            ->addSelect('car_id, client_id, car_number, act.mark_id, act.type_id, COUNT(act.id) as actsCount')
            ->orderBy('client.parent_id, client_id, actsCount DESC')
            ->groupBy('car_number');

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
        $searchModel->car_number = $model->number;

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
     * @param integer $act_id
     * @return mixed
     */
    public function actionCreate($act_id = null)
    {
        $model = new Car();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            // Добавляем в историю кто добавил машину
            $modelHistory = new CarHistory();
            $modelHistory->from = $model->company_id;
            $modelHistory->user_id = Yii::$app->user->identity->id;
            $modelHistory->car_id = $model->id;
            $modelHistory->car_number = $model->number;
            $modelHistory->type = 0;
            $modelHistory->date = (string) time();
            $modelHistory->save();
            // Добавляем в историю кто добавил машину

            if (!Yii::$app->request->isAjax) {
                if ($returnUrl = Yii::$app->request->post('_returnUrl', false)) {
                    return $this->redirect([$returnUrl]);
                } else {
                    return $this->redirect(['company/update', 'id' => $model->company_id, 'expanded' => 1]);
                }
            }
        } else {
            if ($returnUrl = Yii::$app->request->post('_returnUrl', false)) {
                return $this->redirect([$returnUrl]);
            } else {
                return $this->redirect(['company/update', 'id' => $model->company_id, 'expanded' => 1]);
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
                'model'     => $model,
                'expanded'  => true,
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
        $companyDropDownItems = ArrayHelper::map(
            Company::find()->active()->where(['type' => Company::TYPE_OWNER])->all(),
            'id',
            'name'
        );

        if ($model->load(Yii::$app->request->post())) {
            $model->file = UploadedFile::getInstance($model, 'file');

            if ($model->save()) {  // загрузка прошла успешно

                // Добавляем в историю кто добавил машину
                $user_dep_id = Yii::$app->user->identity->id;
                $dateCreate = (string) time();

                if(count($model->updatedIds) > 0) {

                    $arrCars = Car::find()->where(['in', 'id', $model->updatedIds])->select('company_id, id, number')->all();

                    for($iCar = 0; $iCar < count($arrCars); $iCar++) {

                        $findCarHistory = CarHistory::find()->where(['car_id' => $arrCars[$iCar]['id']])->count();

                        if($findCarHistory == 0) {

                            $modelHistory = new CarHistory();
                            $modelHistory->from = $arrCars[$iCar]['company_id'];
                            $modelHistory->user_id = $user_dep_id;
                            $modelHistory->car_id = $arrCars[$iCar]['id'];
                            $modelHistory->car_number = $arrCars[$iCar]['number'];
                            $modelHistory->type = 0;
                            $modelHistory->date = $dateCreate;
                            $modelHistory->save();

                            $modelHistory = '';

                        }

                        $findCarHistory = '';

                    }

                }

                // Добавляем в историю кто добавил машину

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

        $modelCar = Car::find()->where(['id' => $id])->select('company_id, number')->all();

        if(count($modelCar) > 0) {
            // Добавляем в историю кто добавил машину
            $modelHistory = new CarHistory();
            $modelHistory->from = $modelCar[0]['company_id'];
            $modelHistory->user_id = Yii::$app->user->identity->id;
            $modelHistory->car_id = 0;
            $modelHistory->car_number = $modelCar[0]['number'];
            $modelHistory->type = 1;
            $modelHistory->date = (string) time();
            $modelHistory->save();
            // Добавляем в историю кто добавил машину
        }

        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->getRequest()->referrer);
    }

    public function actionHistory()
    {

        $searchModel = new CarHistorySearch();

        $params = Yii::$app->request->queryParams;

        // Если не выбран период то показываем только текущий год
        if(!isset($params['CarHistorySearch']['dateFrom'])) {
            $params['CarHistorySearch']['dateFrom'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
            $searchModel->dateFrom = $params['CarHistorySearch']['dateFrom'];
        }

        if(!isset($params['CarHistorySearch']['dateTo'])) {
            $params['CarHistorySearch']['dateTo'] = date("Y-m-t") . 'T21:00:00.000Z';
            $searchModel->dateTo = $params['CarHistorySearch']['dateTo'];
        }

        if(!isset($params['CarHistorySearch']['type'])) {
            $params['CarHistorySearch']['type'] = 2;
            $searchModel->type = $params['CarHistorySearch']['type'];
        }
        // Если не выбран период то показываем только текущий год

        $dataProvider = $searchModel->search($params);

        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        return $this->render('history', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'authorMembers' => $authorMembers,
        ]);

    }

    /**
     * @param $number
     */
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

    public function actionMovecar()
    {
        if((Yii::$app->request->post('id')) && (Yii::$app->request->post('company_from')) && (Yii::$app->request->post('company_id'))) {

            $id = Yii::$app->request->post('id');
            $company_from = Yii::$app->request->post('company_from');
            $company_id = Yii::$app->request->post('company_id');

            $modelCar = Car::findOne(['id' => $id]);

            if(($modelCar->company_id != $company_id) && ($company_from == $modelCar->company_id)) {
                $modelCar->company_id = $company_id;

                if($modelCar->save()) {

                    // Добавляем в историю кто добавил машину
                    $modelHistory = new CarHistory();
                    $modelHistory->from = $company_from;
                    $modelHistory->to = $company_id;
                    $modelHistory->user_id = Yii::$app->user->identity->id;
                    $modelHistory->car_id = $id;
                    $modelHistory->car_number = $modelCar->number;
                    $modelHistory->type = 2;
                    $modelHistory->date = (string) time();
                    $modelHistory->save();
                    // Добавляем в историю кто добавил машину

                    echo json_encode(['success' => 'true']);
                } else {
                    echo json_encode(['success' => 'false']);
                }

            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }
    }

}
