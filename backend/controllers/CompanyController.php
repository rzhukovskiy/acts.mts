<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;


use common\models\Company;
use common\models\CompanyDriver;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use common\models\CompanyOffer;
use common\models\CompanyService;
use common\models\search\CompanyDriverSearch;
use common\models\search\CompanyMemberSearch;
use common\models\search\CompanySearch;
use common\models\search\UserSearch;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CompanyController extends Controller
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

                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive2', 'new', 'create', 'update', 'info', 'member', 'driver', 'delete', 'attribute'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive2', 'new', 'create', 'update', 'info', 'member', 'driver'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive2', 'new', 'create', 'update', 'info', 'member', 'driver'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    public function actionAddPrice($id)
    {
        $model = $this->findModel($id);

        if ($priceData = Yii::$app->request->post('Price')) {
            foreach ($priceData['type'] as $type_id) {
                foreach ($priceData['service'] as $service_id => $price) {
                    $companyService = new CompanyService();
                    $companyService->company_id = $model->id;
                    $companyService->service_id = $service_id;
                    $companyService->type_id = $type_id;
                    $companyService->price = $price;

                    $companyService->save();
                }
            }
        }
        Yii::$app->session->setFlash('saved', true);

        return $this->redirect(['price', 'id' => $model->id]);
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionNew($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_NEW;
        
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_NEW);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_NEW;
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $searchModelUser = new UserSearch();
        $dataProviderUser = $searchModelUser
            ->search(Yii::$app->request->queryParams);
        $dataProviderUser->query
            ->joinWith('departments')
            ->andWhere(['is not', 'department_id', null]);
        $dataProviderUser->pagination = false;
        $userList = $dataProviderUser->getModels();

        $userData = [];
        foreach ($userList as $user) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type;
            $badgeSearch->user_id = $user->id;
            $badgeSearch->status = Company::STATUS_NEW;
            $badgeSearch->user_id = $user->id;
            $userData[$user->id] = ['badge' => $badgeSearch->search()->count, 'username' => $user->username];
        }

        $this->view->title = 'Заявки - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
        [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'type'         => $type,
            'model'        => $model,
            'listType'     => $listType,
            'userData'     => $userData,
            'admin'        => Yii::$app->user->identity->role == User::ROLE_ADMIN,
        ]);
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionActive($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_ACTIVE;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_ACTIVE);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'address'    => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_ACTIVE;
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Активные - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'model'        => $model,
                'listType'     => $listType,
            ]);
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionArchive($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = [Company::STATUS_ARCHIVE , Company::STATUS_ACTIVE];

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_ARCHIVE);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'address'    => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = [Company::STATUS_ARCHIVE , Company::STATUS_ACTIVE];
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Архив - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'model'        => $model,
                'listType'     => $listType,
            ]);
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionRefuse($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_REFUSE;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_REFUSE);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'address'    => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_REFUSE;
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Отказавшиеся - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
        [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'type'         => $type,
            'model'        => $model,
            'listType'     => $listType,
        ]);
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionArchive2($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_ARCHIVE2;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_ARCHIVE2);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'address'    => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_ARCHIVE2;
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Отказавшиеся - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'model'        => $model,
                'listType'     => $listType,
            ]);
    }

    /**
     * Creates Company model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Company();
        $model->status = Company::STATUS_NEW;
        $model->name = 'Без названия ' . rand(1, 1000);
        $model->address = 'Неизвестный';

        if ($model->load(Yii::$app->request->get()) && $model->save()) {
            return $this->redirect(['company/update', 'id' => $model->id]);
        } else {
            return $this->goBack();
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $output = [];
                foreach (Yii::$app->request->post('Company') as $name => $value) {
                    if ($name == 'workTime') {
                        $output[] = $model->getWorkTimeHtml();
                    } else {
                        $output[] = $value;
                    }
                }
                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        }

        $modelCompanyInfo = $model->info ? $model->info : new CompanyInfo();
        $modelCompanyInfo->company_id = $model->id;
        if ($modelCompanyInfo->isNewRecord) {
            $modelCompanyInfo->save();
        }

        $modelCompanyOffer = $model->offer ? $model->offer : new CompanyOffer();
        $modelCompanyOffer->company_id = $model->id;
        if ($modelCompanyOffer->isNewRecord) {
            $modelCompanyOffer->save();
        }

        return $this->render('offer', [
            'modelCompany' => $model,
            'modelCompanyInfo' => $modelCompanyInfo,
            'modelCompanyOffer' => $modelCompanyOffer,
            'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
        ]);
    }

    public function actionStatus($id, $status)
    {
        $model = $this->findModel($id);
        $model->status = $status;
        $model->save();

        return $this->redirect(['company/update', 'id' => $model->id]);
    }

    public function actionPrice($id)
    {
        $model = $this->findModel($id);

        return $this->render('price', [
            'model' => $model,
        ]);
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);

        $modelCompanyInfo = $model->info ? $model->info : new CompanyInfo();
        $modelCompanyInfo->company_id = $model->id;
        if ($modelCompanyInfo->isNewRecord) {
            $modelCompanyInfo->save();
        }

        return $this->render('info', [
            'modelCompanyInfo' => $modelCompanyInfo,
        ]);
    }

    public function actionMember($id)
    {
        $model = $this->findModel($id);
        $modelCompanyMember = new CompanyMember();
        $modelCompanyMember->company_id = $model->id;

        $searchModel = new CompanyMemberSearch();
        $searchModel->company_id = $model->id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('member',
        [
            'model'        => $modelCompanyMember,
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAttribute($id)
    {
        $model = $this->findModel($id);

        return $this->render('attribute',
        [
            'model' => $model,
        ]);
    }

    public function actionDriver($id)
    {
        $model = $this->findModel($id);
        $modelCompanyMember = new CompanyDriver();
        $modelCompanyMember->company_id = $model->id;

        $searchModel = new CompanyDriverSearch();
        $searchModel->company_id = $model->id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('driver', [
            'model' => $modelCompanyMember,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Deletes an existing Company model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $status = Company::$listStatus[$model->status]['en'];
        $model->delete();

        return $this->redirect(['company/' . $status, 'type' => $model->type]);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}