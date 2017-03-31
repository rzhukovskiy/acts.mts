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
use common\models\search\ServiceSearch;
use common\models\search\TypeSearch;
use common\models\search\UserSearch;
use common\models\Service;
use common\models\Type;
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

                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'new', 'create', 'update', 'info', 'member', 'driver', 'delete', 'attribute', 'offer'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'new', 'create', 'update', 'info', 'member', 'driver', 'offer'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'new', 'create', 'update', 'info', 'member', 'driver', 'offer'],
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
    public function actionArchive3($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_ARCHIVE3;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_ARCHIVE3);
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
            $badgeSearch->status = Company::STATUS_ARCHIVE3;
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

    public function actionOffer($type)
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

        $dataProvider = $searchModel->searchOffer(Yii::$app->request->queryParams);
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

        $listCar = Type::find()->select(['name', 'id'])->orderBy('id')->indexBy('id')->column();

        if($type == 2) {
            $listService = Service::find()->andWhere(['id' => 1])->orWhere(['id' => 2])->select(['description', 'id'])->indexBy('id')->column();
        } else if($type == 4) {
            $listService = Service::find()->andWhere(['id' => 6])->orWhere(['id' => 7])->orWhere(['id' => 8])->orWhere(['id' => 9])->select(['description', 'id'])->indexBy('id')->column();
        } else {
            $listService = Service::find()->select(['description', 'id'])->indexBy('id')->column();
        }

        $listCity = Company::find()->active()->andWhere(['type' => $type])->orWhere(['type' => 6])->groupBy('address')->select(['address', 'address'])->indexBy('address')->column();

        return $this->render('newoffer',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'model'        => $model,
                'listType'     => $listType,
                'listCar' => $listCar,
                'listService' => $listService,
                'listCity' => $listCity,
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

    public static function getPriceFilter($data) {

        // Список типов машин
        $carTypes = $GLOBALS['getParams']['cartypes'];

        // удаляем пустые значения из массива
        for($i = 0; $i < count($carTypes); $i++) {
            if(isset($carTypes[$i])) {
                if ($carTypes[$i] > 0) {

                } else {
                    unset($carTypes[$i]);
                }
            } else {
                if(count($carTypes) == 1) {
                    $carTypes = [];
                }
            }
        }
        // удаляем пустые значения из массива

        // Список типов машин

        // Список услуг
        $services = $GLOBALS['getParams']['services'];

        // удаляем пустые значения из массива
        for($i = 0; $i < count($services); $i++) {
            if(isset($services[$i])) {
                if ($services[$i] > 0) {

                } else {
                    unset($services[$i]);
                }
            } else {
                if(count($services) == 1) {
                    $services = [];
                }
            }
        }
        // удаляем пустые значения из массива

        // Список услуг

        if ($data->type == 2) {

            $typelist = \common\models\Type::find()->asArray()->all();
            $servicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=1 OR service_id=2)')->orderBy('company_id ASC')->asArray()->all();

            $arrayTypes = [];

            foreach ($typelist as $type) {
                $arrayTypes[$type['id']] = $type['name'];
            }

            $priceArray = [];

            $numServices = 2;

            if(count($services) > 0) {
                $numServices = count($services);
            }

            $resTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr>';

            if($numServices == 2) {
                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Снаружи</td>';
                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Внутри</td>';
            } else {

                if($services[0] == 1) {
                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Снаружи</td>';
                } else {
                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Внутри</td>';
                }

            }


            $resTypeCompany .= '</tr></table></td></tr>';

            foreach ($servicesList as $service) {
                $priceArray[$service['type_id']][$service['service_id']] = $service['price'];
            }

            $last_service_type = [];

            foreach ($servicesList as $service) {

                if (!in_array($service['type_id'], $last_service_type)) {

                    if (count($carTypes) == 0) {

                        $type_id = $service['type_id'];

                        $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                        $type_id = 0;

                        if ((isset($priceArray[$service['type_id']][1])) && (isset($priceArray[$service['type_id']][2]))) {

                            if($numServices == 2) {

                                if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                } else if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] == 0)) {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                } else if (($priceArray[$service['type_id']][1] == 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                } else {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                }

                            } else {

                                if($services[0] == 1) {

                                    if (isset($priceArray[$service['type_id']][1])) {

                                        if ($priceArray[$service['type_id']][1] > 0) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                } else {

                                    if (isset($priceArray[$service['type_id']][2])) {

                                        if ($priceArray[$service['type_id']][2] > 0) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                }

                            }

                        } else if (isset($priceArray[$service['type_id']][1])) {

                            if($numServices == 2) {

                                if ($priceArray[$service['type_id']][1] > 0) {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                } else {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                }

                            } else {

                                if($services[0] == 1) {

                                    if ($priceArray[$service['type_id']][1] > 0) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                } else {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                }

                            }

                        } else if (isset($priceArray[$service['type_id']][2])) {

                            if($numServices == 2) {

                                if ($priceArray[$service['type_id']][2] > 0) {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                } else {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                }

                            } else {

                                if($services[0] == 1) {

                                    if ($priceArray[$service['type_id']][2] > 0) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                } else {
                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                }

                            }

                        } else {

                            if ($numServices == 2) {

                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                            } else {

                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                            }

                        }

                    } else {

                        $type_id = $service['type_id'];

                        for($i = 0; $i < count($carTypes); $i++) {

                            if($carTypes[$i] == $type_id) {

                                $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                                if ((isset($priceArray[$service['type_id']][1])) && (isset($priceArray[$service['type_id']][2]))) {

                                    if($numServices == 2) {

                                        if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                        } else if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] == 0)) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                        } else if (($priceArray[$service['type_id']][1] == 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                        }

                                    } else {

                                        if($services[0] == 1) {

                                            if (isset($priceArray[$service['type_id']][1])) {

                                                if ($priceArray[$service['type_id']][1] > 0) {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                                } else {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        } else {

                                            if (isset($priceArray[$service['type_id']][2])) {

                                                if ($priceArray[$service['type_id']][2] > 0) {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                                } else {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        }

                                    }

                                } else if (isset($priceArray[$service['type_id']][1])) {

                                    if($numServices == 2) {

                                        if ($priceArray[$service['type_id']][1] > 0) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                        }

                                    } else {

                                        if($services[0] == 1) {

                                            if ($priceArray[$service['type_id']][1] > 0) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    }

                                } else if (isset($priceArray[$service['type_id']][2])) {

                                    if($numServices == 2) {

                                        if ($priceArray[$service['type_id']][2] > 0) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                        }

                                    } else {

                                        if($services[0] == 1) {

                                            if ($priceArray[$service['type_id']][2] > 0) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    }

                                } else {

                                    if ($numServices == 2) {

                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                    } else {

                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                }

                            }

                        }

                        $type_id = 0;

                    }

                }

                $last_service_type[] = $service['type_id'];

            }

            $resTypeCompany .= '</table>';

            return $resTypeCompany;

        } else if($data->type == 4) {

            $typelist = \common\models\Type::find()->asArray()->all();
            $servicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=6 OR service_id=7 OR service_id=8 OR service_id=9) AND price>0')->orderBy('company_id ASC')->asArray()->all();

            $arrayTypes = [];

            foreach ($typelist as $type) {
                $arrayTypes[$type['id']] = $type['name'];
            }

            $priceArray = [];

            $numServices = 4;

            if(count($services) > 0) {
                $numServices = count($services);
            }

            $resTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr><tr>';


            if($numServices == 4) {
                $resTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
            } else if($numServices == 1) {

                switch ($services[0]) {
                    case 6:
                        $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Одинарное</td>';
                        break;
                    case 7:
                        $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Парное</td>';
                        break;
                    case 9:
                        $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Балансировка</td>';
                        break;
                    case 8:
                        $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Полный</td>';
                        break;
                }

            } else if($numServices == 2) {

                $tmpArray = $services;

                if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {
                    list($tmpArray[0], $tmpArray[1]) = array($tmpArray[1], $tmpArray[0]);
                }

                for($z = 0; $z < count($tmpArray); $z++) {

                    switch ($tmpArray[$z]) {
                        case 6:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                            }

                            break;
                        case 7:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                            }

                            break;
                        case 9:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                            }

                            break;
                        case 8:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                            }

                            break;
                    }

                }

            } else if($numServices == 3) {

                $tmpArray = $services;

                if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {

                    $index1 = 0;
                    $index2 = 0;

                    for ($z = 0; $z < count($tmpArray); $z++) {

                        if($tmpArray[$z] == 9) {
                            $index1 = $z;
                        } else if($tmpArray[$z] == 8) {
                            $index2 = $z;
                        }

                    }

                    list($tmpArray[$index1], $tmpArray[$index2]) = array($tmpArray[$index2], $tmpArray[$index1]);

                }

                for($z = 0; $z < count($tmpArray); $z++) {

                    switch ($tmpArray[$z]) {
                        case 6:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                            } else {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Одинарное</td>';
                            }

                            break;
                        case 7:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                            } else {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td>';
                            }

                            break;
                        case 9:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                            } else {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td>';
                            }

                            break;
                        case 8:

                            if($z == 0) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                            } else if(($z + 1) == count($tmpArray)) {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                            } else {
                                $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Полный</td>';
                            }

                            break;
                    }

                }

            }

            $resTypeCompany .= '</tr></table></td></tr>';

            foreach ($servicesList as $service) {
                $priceArray[$service['type_id']][$service['service_id']] = $service['price'];
            }

            $last_service_type = [];

            foreach ($servicesList as $service) {

                if (!in_array($service['type_id'], $last_service_type)) {

                    $numPercent = 0;

                    if($numServices == 4) {
                        $numPercent = 25;
                    } else if($numServices == 1) {
                        $numPercent = 100;
                    } else if($numServices == 2) {
                        $numPercent = 50;
                    } else if($numServices == 3) {
                        $numPercent = 33;
                    }

                    if (count($carTypes) == 0) {

                        $type_id = $service['type_id'];

                        $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                        $type_id = 0;

                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                        if(count($services) > 0) {

                            if (in_array(6, $services)) {

                                $stringStyle = '';

                                if($numServices != 1) {
                                    $stringStyle = ' style=\'padding-right:5px;\'';
                                } else {
                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                }

                                if (isset($priceArray[$service['type_id']][6])) {
                                    if ($priceArray[$service['type_id']][6] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][6] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                }

                            }

                            if(in_array(7, $services)) {

                                $stringStyle = '';

                                if($numServices != 1) {
                                    if($services[0] != 7) {
                                        $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                    } else {
                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                    }
                                } else {
                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                }

                                if (isset($priceArray[$service['type_id']][7])) {
                                    if ($priceArray[$service['type_id']][7] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][7] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                }

                            }

                            if(in_array(9, $services)) {

                                $stringStyle = '';

                                if($numServices != 1) {
                                    if($services[0] != 9) {
                                        $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                    } else {
                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                    }
                                } else {
                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                }

                                if (isset($priceArray[$service['type_id']][9])) {
                                    if ($priceArray[$service['type_id']][9] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][9] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                }

                            }

                            if(in_array(8, $services)) {

                                $stringStyle = '';

                                if($numServices != 1) {
                                    $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                } else {
                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                }

                                if (isset($priceArray[$service['type_id']][8])) {
                                    if ($priceArray[$service['type_id']][8] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][8] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                }

                            }

                        } else {

                            if (isset($priceArray[$service['type_id']][6])) {
                                if ($priceArray[$service['type_id']][6] > 0) {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][6] . '</td>';
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                }
                            } else {
                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                            }

                            if (isset($priceArray[$service['type_id']][7])) {
                                if ($priceArray[$service['type_id']][7] > 0) {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][7] . '</td>';
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                }
                            } else {
                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                            }

                            if (isset($priceArray[$service['type_id']][9])) {
                                if ($priceArray[$service['type_id']][9] > 0) {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][9] . '</td>';
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                }
                            } else {
                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                            }

                            if (isset($priceArray[$service['type_id']][8])) {
                                if ($priceArray[$service['type_id']][8] > 0) {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][8] . '</td>';
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                }
                            } else {
                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                            }

                        }

                        $resTypeCompany .= '</tr></table></td></tr>';

                    } else {

                        $type_id = $service['type_id'];

                        for ($i = 0; $i < count($carTypes); $i++) {

                            if ($carTypes[$i] == $type_id) {


                                $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                if(count($services) > 0) {

                                    if (in_array(6, $services)) {

                                        $stringStyle = '';

                                        if($numServices != 1) {
                                            $stringStyle = ' style=\'padding-right:5px;\'';
                                        } else {
                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                        }

                                        if (isset($priceArray[$service['type_id']][6])) {
                                            if ($priceArray[$service['type_id']][6] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][6] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }

                                    }

                                    if(in_array(7, $services)) {

                                        $stringStyle = '';

                                        if($numServices != 1) {
                                            if($services[0] != 7) {
                                                $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                            } else {
                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                            }
                                        } else {
                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                        }

                                        if (isset($priceArray[$service['type_id']][7])) {
                                            if ($priceArray[$service['type_id']][7] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][7] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }

                                    }

                                    if(in_array(9, $services)) {

                                        $stringStyle = '';

                                        if($numServices != 1) {
                                            if($services[0] != 9) {
                                                $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                            } else {
                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                            }
                                        } else {
                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                        }

                                        if (isset($priceArray[$service['type_id']][9])) {
                                            if ($priceArray[$service['type_id']][9] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][9] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }

                                    }

                                    if(in_array(8, $services)) {

                                        $stringStyle = '';

                                        if($numServices != 1) {
                                            $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                        } else {
                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                        }

                                        if (isset($priceArray[$service['type_id']][8])) {
                                            if ($priceArray[$service['type_id']][8] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][8] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }

                                    }

                                } else {

                                    if (isset($priceArray[$service['type_id']][6])) {
                                        if ($priceArray[$service['type_id']][6] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][6] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                    }

                                    if (isset($priceArray[$service['type_id']][7])) {
                                        if ($priceArray[$service['type_id']][7] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][7] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                    }

                                    if (isset($priceArray[$service['type_id']][9])) {
                                        if ($priceArray[$service['type_id']][9] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][9] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                    }

                                    if (isset($priceArray[$service['type_id']][8])) {
                                        if ($priceArray[$service['type_id']][8] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][8] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                    }

                                }

                                $resTypeCompany .= '</tr></table></td></tr>';

                            }

                        }

                        $type_id = 0;

                    }

                }

                $last_service_type[] = $service['type_id'];

            }

            $resTypeCompany .= '</table>';

            return $resTypeCompany;
        } else if ($data->type == 6) {

            if(Yii::$app->request->get('type') == 2) {

                $typelist = \common\models\Type::find()->asArray()->all();
                $servicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=1 OR service_id=2)')->orderBy('company_id ASC')->asArray()->all();

                $arrayTypes = [];

                foreach ($typelist as $type) {
                    $arrayTypes[$type['id']] = $type['name'];
                }

                $priceArray = [];

                $numServices = 2;

                if(count($services) > 0) {
                    $numServices = count($services);
                }

                $resTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr>';

                if($numServices == 2) {
                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Снаружи</td>';
                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Внутри</td>';
                } else {

                    if($services[0] == 1) {
                        $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Снаружи</td>';
                    } else {
                        $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Внутри</td>';
                    }

                }


                $resTypeCompany .= '</tr></table></td></tr>';

                foreach ($servicesList as $service) {
                    $priceArray[$service['type_id']][$service['service_id']] = $service['price'];
                }

                $last_service_type = [];

                foreach ($servicesList as $service) {

                    if (!in_array($service['type_id'], $last_service_type)) {

                        if (count($carTypes) == 0) {

                            $type_id = $service['type_id'];

                            $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                            $type_id = 0;

                            if ((isset($priceArray[$service['type_id']][1])) && (isset($priceArray[$service['type_id']][2]))) {

                                if($numServices == 2) {

                                    if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                    } else if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] == 0)) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                    } else if (($priceArray[$service['type_id']][1] == 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                    }

                                } else {

                                    if($services[0] == 1) {

                                        if (isset($priceArray[$service['type_id']][1])) {

                                            if ($priceArray[$service['type_id']][1] > 0) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    } else {

                                        if (isset($priceArray[$service['type_id']][2])) {

                                            if ($priceArray[$service['type_id']][2] > 0) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    }

                                }

                            } else if (isset($priceArray[$service['type_id']][1])) {

                                if($numServices == 2) {

                                    if ($priceArray[$service['type_id']][1] > 0) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                    }

                                } else {

                                    if($services[0] == 1) {

                                        if ($priceArray[$service['type_id']][1] > 0) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                }

                            } else if (isset($priceArray[$service['type_id']][2])) {

                                if($numServices == 2) {

                                    if ($priceArray[$service['type_id']][2] > 0) {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                    }

                                } else {

                                    if($services[0] == 1) {

                                        if ($priceArray[$service['type_id']][2] > 0) {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                        } else {
                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    } else {
                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                    }

                                }

                            } else {

                                if ($numServices == 2) {

                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                } else {

                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                }

                            }

                        } else {

                            $type_id = $service['type_id'];

                            for($i = 0; $i < count($carTypes); $i++) {

                                if($carTypes[$i] == $type_id) {

                                    $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                                    if ((isset($priceArray[$service['type_id']][1])) && (isset($priceArray[$service['type_id']][2]))) {

                                        if($numServices == 2) {

                                            if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                            } else if (($priceArray[$service['type_id']][1] > 0) && ($priceArray[$service['type_id']][2] == 0)) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                            } else if (($priceArray[$service['type_id']][1] == 0) && ($priceArray[$service['type_id']][2] > 0)) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                            }

                                        } else {

                                            if($services[0] == 1) {

                                                if (isset($priceArray[$service['type_id']][1])) {

                                                    if ($priceArray[$service['type_id']][1] > 0) {
                                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                                    } else {
                                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                } else {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            } else {

                                                if (isset($priceArray[$service['type_id']][2])) {

                                                    if ($priceArray[$service['type_id']][2] > 0) {
                                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                                    } else {
                                                        $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                } else {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            }

                                        }

                                    } else if (isset($priceArray[$service['type_id']][1])) {

                                        if($numServices == 2) {

                                            if ($priceArray[$service['type_id']][1] > 0) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                            }

                                        } else {

                                            if($services[0] == 1) {

                                                if ($priceArray[$service['type_id']][1] > 0) {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][1] . '</td></tr>';
                                                } else {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        }

                                    } else if (isset($priceArray[$service['type_id']][2])) {

                                        if($numServices == 2) {

                                            if ($priceArray[$service['type_id']][2] > 0) {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                            }

                                        } else {

                                            if($services[0] == 1) {

                                                if ($priceArray[$service['type_id']][2] > 0) {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $priceArray[$service['type_id']][2] . '</td></tr>';
                                                } else {
                                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            } else {
                                                $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        }

                                    } else {

                                        if ($numServices == 2) {

                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                        } else {

                                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                        }

                                    }

                                }

                            }

                            $type_id = 0;

                        }

                    }

                    $last_service_type[] = $service['type_id'];

                }

                $resTypeCompany .= '</table>';

                return $resTypeCompany;

            } else {

                $typelist = \common\models\Type::find()->asArray()->all();
                $servicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=6 OR service_id=7 OR service_id=8 OR service_id=9) AND price>0')->orderBy('company_id ASC')->asArray()->all();

                $arrayTypes = [];

                foreach ($typelist as $type) {
                    $arrayTypes[$type['id']] = $type['name'];
                }

                $priceArray = [];

                $numServices = 4;

                if(count($services) > 0) {
                    $numServices = count($services);
                }

                $resTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr><tr>';


                if($numServices == 4) {
                    $resTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                } else if($numServices == 1) {

                    switch ($services[0]) {
                        case 6:
                            $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Одинарное</td>';
                            break;
                        case 7:
                            $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Парное</td>';
                            break;
                        case 9:
                            $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Балансировка</td>';
                            break;
                        case 8:
                            $resTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Полный</td>';
                            break;
                    }

                } else if($numServices == 2) {

                    $tmpArray = $services;

                    if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {
                        list($tmpArray[0], $tmpArray[1]) = array($tmpArray[1], $tmpArray[0]);
                    }

                    for($z = 0; $z < count($tmpArray); $z++) {

                        switch ($tmpArray[$z]) {
                            case 6:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                                }

                                break;
                            case 7:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                                }

                                break;
                            case 9:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                                }

                                break;
                            case 8:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                }

                                break;
                        }

                    }

                } else if($numServices == 3) {

                    $tmpArray = $services;

                    if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {

                        $index1 = 0;
                        $index2 = 0;

                        for ($z = 0; $z < count($tmpArray); $z++) {

                            if($tmpArray[$z] == 9) {
                                $index1 = $z;
                            } else if($tmpArray[$z] == 8) {
                                $index2 = $z;
                            }

                        }

                        list($tmpArray[$index1], $tmpArray[$index2]) = array($tmpArray[$index2], $tmpArray[$index1]);

                    }

                    for($z = 0; $z < count($tmpArray); $z++) {

                        switch ($tmpArray[$z]) {
                            case 6:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                                } else {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Одинарное</td>';
                                }

                                break;
                            case 7:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                                } else {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td>';
                                }

                                break;
                            case 9:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                                } else {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td>';
                                }

                                break;
                            case 8:

                                if($z == 0) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                                } else if(($z + 1) == count($tmpArray)) {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                } else {
                                    $resTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Полный</td>';
                                }

                                break;
                        }

                    }

                }

                $resTypeCompany .= '</tr></table></td></tr>';

                foreach ($servicesList as $service) {
                    $priceArray[$service['type_id']][$service['service_id']] = $service['price'];
                }

                $last_service_type = [];

                foreach ($servicesList as $service) {

                    if (!in_array($service['type_id'], $last_service_type)) {

                        $numPercent = 0;

                        if($numServices == 4) {
                            $numPercent = 25;
                        } else if($numServices == 1) {
                            $numPercent = 100;
                        } else if($numServices == 2) {
                            $numPercent = 50;
                        } else if($numServices == 3) {
                            $numPercent = 33;
                        }

                        if (count($carTypes) == 0) {

                            $type_id = $service['type_id'];

                            $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                            $type_id = 0;

                            $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                            if(count($services) > 0) {

                                if (in_array(6, $services)) {

                                    $stringStyle = '';

                                    if($numServices != 1) {
                                        $stringStyle = ' style=\'padding-right:5px;\'';
                                    } else {
                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                    }

                                    if (isset($priceArray[$service['type_id']][6])) {
                                        if ($priceArray[$service['type_id']][6] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][6] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }

                                }

                                if(in_array(7, $services)) {

                                    $stringStyle = '';

                                    if($numServices != 1) {
                                        if($services[0] != 7) {
                                            $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                        } else {
                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                        }
                                    } else {
                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                    }

                                    if (isset($priceArray[$service['type_id']][7])) {
                                        if ($priceArray[$service['type_id']][7] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][7] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }

                                }

                                if(in_array(9, $services)) {

                                    $stringStyle = '';

                                    if($numServices != 1) {
                                        if($services[0] != 9) {
                                            $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                        } else {
                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                        }
                                    } else {
                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                    }

                                    if (isset($priceArray[$service['type_id']][9])) {
                                        if ($priceArray[$service['type_id']][9] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][9] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }

                                }

                                if(in_array(8, $services)) {

                                    $stringStyle = '';

                                    if($numServices != 1) {
                                        $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                    } else {
                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                    }

                                    if (isset($priceArray[$service['type_id']][8])) {
                                        if ($priceArray[$service['type_id']][8] > 0) {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][8] . '</td>';
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                        }
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                    }

                                }

                            } else {

                                if (isset($priceArray[$service['type_id']][6])) {
                                    if ($priceArray[$service['type_id']][6] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][6] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                }

                                if (isset($priceArray[$service['type_id']][7])) {
                                    if ($priceArray[$service['type_id']][7] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][7] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                }

                                if (isset($priceArray[$service['type_id']][9])) {
                                    if ($priceArray[$service['type_id']][9] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][9] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                }

                                if (isset($priceArray[$service['type_id']][8])) {
                                    if ($priceArray[$service['type_id']][8] > 0) {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][8] . '</td>';
                                    } else {
                                        $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                    }
                                } else {
                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                }

                            }

                            $resTypeCompany .= '</tr></table></td></tr>';

                        } else {

                            $type_id = $service['type_id'];

                            for ($i = 0; $i < count($carTypes); $i++) {

                                if ($carTypes[$i] == $type_id) {


                                    $resTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $arrayTypes[$type_id] . "</td>";

                                    $resTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                    if(count($services) > 0) {

                                        if (in_array(6, $services)) {

                                            $stringStyle = '';

                                            if($numServices != 1) {
                                                $stringStyle = ' style=\'padding-right:5px;\'';
                                            } else {
                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                            }

                                            if (isset($priceArray[$service['type_id']][6])) {
                                                if ($priceArray[$service['type_id']][6] > 0) {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][6] . '</td>';
                                                } else {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }

                                        }

                                        if(in_array(7, $services)) {

                                            $stringStyle = '';

                                            if($numServices != 1) {
                                                if($services[0] != 7) {
                                                    $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                } else {
                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                }
                                            } else {
                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                            }

                                            if (isset($priceArray[$service['type_id']][7])) {
                                                if ($priceArray[$service['type_id']][7] > 0) {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][7] . '</td>';
                                                } else {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }

                                        }

                                        if(in_array(9, $services)) {

                                            $stringStyle = '';

                                            if($numServices != 1) {
                                                if($services[0] != 9) {
                                                    $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                } else {
                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                }
                                            } else {
                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                            }

                                            if (isset($priceArray[$service['type_id']][9])) {
                                                if ($priceArray[$service['type_id']][9] > 0) {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][9] . '</td>';
                                                } else {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }

                                        }

                                        if(in_array(8, $services)) {

                                            $stringStyle = '';

                                            if($numServices != 1) {
                                                $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                            } else {
                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                            }

                                            if (isset($priceArray[$service['type_id']][8])) {
                                                if ($priceArray[$service['type_id']][8] > 0) {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $priceArray[$service['type_id']][8] . '</td>';
                                                } else {
                                                    $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                            }

                                        }

                                    } else {

                                        if (isset($priceArray[$service['type_id']][6])) {
                                            if ($priceArray[$service['type_id']][6] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $priceArray[$service['type_id']][6] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                        }

                                        if (isset($priceArray[$service['type_id']][7])) {
                                            if ($priceArray[$service['type_id']][7] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][7] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                        }

                                        if (isset($priceArray[$service['type_id']][9])) {
                                            if ($priceArray[$service['type_id']][9] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $priceArray[$service['type_id']][9] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                        }

                                        if (isset($priceArray[$service['type_id']][8])) {
                                            if ($priceArray[$service['type_id']][8] > 0) {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $priceArray[$service['type_id']][8] . '</td>';
                                            } else {
                                                $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                            }
                                        } else {
                                            $resTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                        }

                                    }

                                    $resTypeCompany .= '</tr></table></td></tr>';

                                }

                            }

                            $type_id = 0;

                        }

                    }

                    $last_service_type[] = $service['type_id'];

                }

                $resTypeCompany .= '</table>';

                return $resTypeCompany;

            }

        } else {
            return '-';
        }

    }

}