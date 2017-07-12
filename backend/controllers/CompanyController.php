<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;


use common\models\Act;
use common\models\Company;
use common\models\CompanyDriver;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use common\models\CompanyOffer;
use common\models\CompanyService;
use common\models\CompanyState;
use yii\base\DynamicModel;
use common\models\Department;
use yii\helpers\Url;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use common\models\DepartmentCompany;
use common\models\DepartmentUserCompanyType;
use common\models\search\CompanyDriverSearch;
use common\models\search\CompanyMemberSearch;
use common\models\search\CompanySearch;
use common\models\search\ServiceSearch;
use common\models\search\TypeSearch;
use common\models\search\UserSearch;
use common\models\Service;
use common\models\Type;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
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

                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'new', 'create', 'update', 'updatemember', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'delete', 'attribute', 'offer'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'new', 'create', 'update', 'updatemember', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['add-price', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'new', 'create', 'update', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer'],
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

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->andWhere(['sub_type' => $requestSupType]);
            }

        }
        // Подкатегории для сервиса

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

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->andWhere(['sub_type' => $requestSupType]);
            }

        }
        // Подкатегории для сервиса

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

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->andWhere(['sub_type' => $requestSupType]);
            }

        }
        // Подкатегории для сервиса

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

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->andWhere(['sub_type' => $requestSupType]);
            }

        }
        // Подкатегории для сервиса

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

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->andWhere(['sub_type' => $requestSupType]);
            }

        }
        // Подкатегории для сервиса

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

            $DepartmentCompany = new DepartmentCompany();
            $DepartmentCompany->company_id = $model->id;
            $DepartmentCompany->user_id = Yii::$app->user->identity->id;
            $DepartmentCompany->remove_id = 0;
            $DepartmentCompany->save();

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

    public function actionUpdatemember($id)
    {
        $hasEditable = Yii::$app->request->post('hasEditable');

        if($hasEditable == 1) {
            $arrCompanyMember = Yii::$app->request->post('CompanyMember');

            if(isset($arrCompanyMember['name'][$id])) {

                if(mb_strlen($arrCompanyMember['name'][$id]) > 1) {

                    $newVal = $arrCompanyMember['name'][$id];

                    $companyMember = CompanyMember::findOne($id);
                    $companyMember->name = $newVal;

                    if ($companyMember->save()) {
                        return json_encode(['output' => $newVal, 'message' => '']);
                    } else {
                        return json_encode(['message' => 'не получилось']);
                    }

                } else {
                    return json_encode(['message' => 'не получилось']);
                }

            } else if(isset($arrCompanyMember['email'][$id])) {

                if(mb_strlen($arrCompanyMember['email'][$id]) > 1) {

                    $newVal = $arrCompanyMember['email'][$id];

                    // Переводим email в нижний регистр
                    $newVal = strtolower($newVal);

                    $companyMember = CompanyMember::findOne($id);
                    $companyMember->email = $newVal;

                    if ($companyMember->save()) {
                        return json_encode(['output' => $newVal, 'message' => '']);
                    } else {
                        return json_encode(['message' => 'не получилось']);
                    }

                } else {
                    return json_encode(['message' => 'не получилось']);
                }

            } else if(isset($arrCompanyMember['position'][$id])) {

                if(mb_strlen($arrCompanyMember['position'][$id]) > 1) {

                    $newVal = $arrCompanyMember['position'][$id];

                    $companyMember = CompanyMember::findOne($id);
                    $companyMember->position = $newVal;

                    if ($companyMember->save()) {
                        return json_encode(['output' => $newVal, 'message' => '']);
                    } else {
                        return json_encode(['message' => 'не получилось']);
                    }

                } else {
                    return json_encode(['message' => 'не получилось']);
                }

            } else if(isset($arrCompanyMember['phone'][$id])) {

                if(mb_strlen($arrCompanyMember['phone'][$id]) > 1) {

                    $newVal = $arrCompanyMember['phone'][$id];

                    $companyMember = CompanyMember::findOne($id);
                    $companyMember->phone = $newVal;

                    if ($companyMember->save()) {
                        return json_encode(['output' => '<span style="color:#3fad46;">Успешно</span>', 'message' => '']);
                    } else {
                        return json_encode(['message' => 'не получилось']);
                    }

                } else {
                    return json_encode(['message' => 'не получилось']);
                }


            } else {
                return json_encode(['message' => 'не получилось']);
            }

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

    public function actionStatus($id, $status)
    {
        $model = $this->findModel($id);

        // Записываем дату переноса из заявок
        if ($model->status == Company::STATUS_NEW) {
            $modelDepartmentCompany = DepartmentCompany::findOne(['company_id' => $id]);
            $modelDepartmentCompany->remove_date = time();
            $modelDepartmentCompany->remove_id = Yii::$app->user->identity->id;
            $modelDepartmentCompany->save();
        }

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
            'model' => $model,
            'modelCompanyInfo' => $modelCompanyInfo,
        ]);
    }

    // Раздел статус клиента
    public function actionState($id)
    {

        $model = $this->findModel($id);

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

        $searchModel = CompanyState::find()->where(['company_id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'date'    => SORT_ASC,
            ]
        ];

        $companyMembers = CompanyMember::find()->where(['company_id' => $id])->select('name')->indexBy('id')->column();
        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        return $this->render('state', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'model' => $model,
            'modelCompanyInfo' => $modelCompanyInfo,
            'modelCompanyOffer' => $modelCompanyOffer,
            'companyMembers' => $companyMembers,
            'authorMembers' => $authorMembers,
        ]);

    }

    public function actionNewstate($id)
    {
        $model = new CompanyState();
        $model->company_id = $id;

        $modelOff = $this->findModel($id);
        $modelCompanyOffer = $modelOff->offer;

        $companyMembers = CompanyMember::find()->where(['company_id' => $id])->select('name')->indexBy('id')->column();
        $authorMembers = DepartmentUserCompanyType::find()->innerJoin('user', '`user`.`id` = `department_user_company_type`.`user_id`')->select('`username`')->indexBy('user_id')->groupBy('user_id')->column();

        // Загрузка файлов

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            $model->files = UploadedFile::getInstances($model, 'files');

            if ($model->upload()) {
                // file is uploaded successfully
            } else {
            }

            return $this->redirect(['company/state', 'id' => $model->company_id]);

        } else {
            return $this->render('form/newstate', [
                'id' => $id,
                'model' => $model,
                'companyMembers' => $companyMembers,
                'authorMembers' => $authorMembers,
                'modelCompanyOffer' => $modelCompanyOffer,
            ]);
        }
    }

    public function actionNewattach($id)
    {

        $modelAddAttach = new DynamicModel(['files']);
        $modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

        $filesArr = UploadedFile::getInstances($modelAddAttach, 'files');

        $filePath = \Yii::getAlias('@webroot/files/attaches/' . $id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/attaches/'))) {
            mkdir(\Yii::getAlias('@webroot/files/attaches/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/attaches/' . $id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/attaches/' . $id . '/'), 0775);
        }

        foreach ($filesArr as $file) {

            if($file->baseName != 'attaches.zip') {

                if (!file_exists($filePath . $file->baseName . '.' . $file->extension)) {
                    $file->saveAs($filePath . $file->baseName . '.' . $file->extension);
                } else {

                    $filename = $filePath . $file->baseName . '.' . $file->extension;
                    $i = 1;

                    while (file_exists($filename)) {
                        $filename = $filePath . $file->baseName . '(' . $i . ').' . $file->extension;
                        $i++;
                    }

                    $file->saveAs($filename);

                }

            }

        }

        return $this->redirect(['company/state', 'id' => $id]);

    }

    public function actionAttaches($id)
    {

        if($id > 0) {

            $pathfolder = \Yii::getAlias('@webroot/files/attaches/' . $id . '/');
            $patharchive = \Yii::getAlias('@webroot/files/attaches/attaches.zip');

            if (file_exists($pathfolder)) {

                if (file_exists($patharchive)) {
                    unlink($patharchive);
                }

                $zip = new \ZipArchive;
                if ($zip->open($patharchive, \ZIPARCHIVE::CREATE) === TRUE) {

                    foreach (FileHelper::findFiles($pathfolder) as $file) {
                        $zip->addFile($pathfolder . basename($file), basename($file));
                    }

                    $zip->close();

                    chmod($patharchive, 0755);

                    header("Content-Type: application/octet-stream");
                    header("Accept-Ranges: bytes");
                    header("Content-Length: ".filesize($patharchive));
                    header("Content-Disposition: attachment; filename=attaches.zip");
                    readfile($patharchive);

                }

            }

        }

        return $this->redirect(['company/state', 'id' => $id]);

    }

    public function actionGetcomment()
    {
        $stateID = (int) Yii::$app->request->post('state');

        if($stateID > 0) {

            $stateArr = CompanyState::find()->where(['id' => $stateID])->select('comment')->column();

            $fullComment = '';

            if(isset($stateArr)) {
                if(isset($stateArr[0])) {
                    $fullComment = nl2br($stateArr[0]);
                } else {
                    echo json_encode(['success' => 'false']);
                }
            } else {
                echo json_encode(['success' => 'false']);
            }

            echo json_encode(['success' => 'true', 'comment' => $fullComment]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionOffer($type)
    {
        /** @var User $currentUser */

        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = [Company::STATUS_ARCHIVE , Company::STATUS_ACTIVE];

        $listCar = Type::find()->select(['name', 'id'])->orderBy('id')->indexBy('id')->column();

        if ($type == 2) {
            $listService = Service::find()->andWhere(['id' => 1])->orWhere(['id' => 2])->select(['description', 'id'])->indexBy('id')->column();
        } else if ($type == 4) {
            $listService = Service::find()->andWhere(['id' => 6])->orWhere(['id' => 7])->orWhere(['id' => 8])->orWhere(['id' => 9])->select(['description', 'id'])->indexBy('id')->column();
        } else {
            $listService = Service::find()->select(['description', 'id'])->indexBy('id')->column();
        }

        $listCity = Company::find()->where(['status' => Company::STATUS_ACTIVE])->orWhere(['status' => Company::STATUS_ARCHIVE])->andWhere(['type' => $type])->orWhere(['type' => 6])->groupBy('address')->select(['address', 'address'])->indexBy('address')->column();

        $listType = Company::$listType;

        if(isset(Yii::$app->request->queryParams['CompanySearch'])) {

            if(isset(Yii::$app->request->queryParams['CompanySearch']['address'])) {

                // проверяем что город выбран
                $arrSelCity = Yii::$app->request->queryParams['CompanySearch']['address'];

                // удаляем пустые значения из массива
                for ($i = 0; $i < count($arrSelCity); $i++) {
                    if (isset($arrSelCity[$i])) {
                        if (strlen($arrSelCity[$i]) > 1) {

                        } else {
                            unset($arrSelCity[$i]);
                        }
                    } else {
                        if (count($arrSelCity) == 1) {
                            $arrSelCity = [];
                        }
                    }
                }
                // удаляем пустые значения из массива
                // проверяем что город выбран

                if(count($arrSelCity) > 0) {

                $dataProvider = $searchModel->searchOffer(Yii::$app->request->queryParams);

                if (isset(Yii::$app->request->queryParams['sort'])) {

                    $arrSelCarTypes = Yii::$app->request->queryParams['CompanySearch']['cartypes'];

                    // удаляем пустые значения из массива
                    for ($i = 0; $i < count($arrSelCarTypes); $i++) {
                        if (isset($arrSelCarTypes[$i])) {
                            if ($arrSelCarTypes[$i] > 0) {

                            } else {
                                unset($arrSelCarTypes[$i]);
                            }
                        } else {
                            if (count($arrSelCarTypes) == 1) {
                                $arrSelCarTypes = [];
                            }
                        }
                    }
                    // удаляем пустые значения из массива

                    if (count($arrSelCarTypes) == 1) {
                    } else {
                        $dataProvider->sort = [
                            'defaultOrder' => [
                                'address' => SORT_ASC,
                                'created_at' => SORT_DESC,
                            ]
                        ];
                    }

                } else {
                    $dataProvider->sort = [
                        'defaultOrder' => [
                            'address' => SORT_ASC,
                            'created_at' => SORT_DESC,
                        ]
                    ];
                }

                $model = new Company();
                $model->type = $type;

                return $this->render('newoffer',
                    [
                        'dataProvider' => $dataProvider,
                        'searchModel' => $searchModel,
                        'type' => $type,
                        'model' => $model,
                        'listType' => $listType,
                        'listCar' => $listCar,
                        'listService' => $listService,
                        'listCity' => $listCity
                    ]);

                } else {
                    $model = new Company();
                    $model->type = $type;

                    return $this->render('clearoffer', [
                        'searchModel' => $searchModel,
                        'type' => $type,
                        'model' => $model,
                        'listType' => $listType,
                        'listCar' => $listCar,
                        'listService' => $listService,
                        'listCity' => $listCity]);
                }

            } else {
                $model = new Company();
                $model->type = $type;

                return $this->render('clearoffer', [
                    'searchModel' => $searchModel,
                    'type' => $type,
                    'model' => $model,
                    'listType' => $listType,
                    'listCar' => $listCar,
                    'listService' => $listService,
                    'listCity' => $listCity]);
            }

        } else {
            $model = new Company();
            $model->type = $type;

            return $this->render('clearoffer', [
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
                'listCar' => $listCar,
                'listService' => $listService,
                'listCity' => $listCity]);
        }

    }

    public function actionGetcall()
    {

        if((mb_strlen(Yii::$app->user->identity->code) > 0) && (mb_strlen(Yii::$app->user->identity->code_pass) > 0)) {
            echo json_encode(['success' => 'true', 'code' => Yii::$app->user->identity->code, 'cipher' => Yii::$app->user->identity->code_pass]);
        } else {
            echo json_encode(['success' => 'false']);
        }

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

        // Удалить запись имени пользователя добавившего заявку
        if($model->status == Company::STATUS_NEW) {
            DepartmentCompany::deleteAll([
                'company_id' => $id
            ]);
        }

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
        $paramsGet = Yii::$app->request->get('CompanySearch');
        $carTypes = $paramsGet['cartypes'];

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
        $services = $paramsGet['services'];

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

    public static function getPurchasedService($id) {

        $PurchasedService = Act::find()->where(['client_id' => $id])->indexBy('service_type')->select('service_type')->orderBy('service_type')->groupBy('service_type')->column();

        $resArr = [];
        $resArr[0] = '';
        $resArr[1] = '';

        if(count($PurchasedService) > 0) {
            foreach ($PurchasedService as $key => $value) {
                $resArr[0] .= Company::$listType[$value]['ru'] . '<br />';
            }
        }

        $arrServiceAll = ['2' => '2', '3' => '3', '4' => '4', '5' => '5'];
        $noPurchased = array_diff($arrServiceAll, $PurchasedService);

        if(count($noPurchased) > 0) {
            foreach ($noPurchased as $key => $value) {
                $resArr[1] .= Company::$listType[$value]['ru'] . '<br />';
            }
        }

        return $resArr;
    }

}