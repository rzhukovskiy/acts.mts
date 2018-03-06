<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\Act;
use common\models\Car;
use common\models\Changes;
use common\models\Company;
use common\models\CompanyAddress;
use common\models\CompanyDriver;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use common\models\CompanyOffer;
use common\models\CompanyService;
use common\models\CompanyState;
use common\models\DepartmentLinking;
use common\models\EntryEvent;
use common\models\MonthlyAct;
use common\models\search\TenderControlSearch;
use common\models\search\TenderLinksSearch;
use common\models\search\TenderMemberSearch;
use common\models\search\TenderOwnerSearch;
use common\models\search\TenderSearch;
use common\models\Tender;
use common\models\TenderHystory;
use common\models\TenderLinks;
use common\models\TenderLists;
use common\models\TenderControl;
use common\models\TenderMembers;
use common\models\TenderOwner;
use yii\base\DynamicModel;
use common\models\CompanySubType;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use common\models\DepartmentCompany;
use common\models\DepartmentUserCompanyType;
use common\models\search\CompanyDriverSearch;
use common\models\search\CompanyMemberSearch;
use common\models\search\CompanySearch;
use common\models\search\UserSearch;
use common\models\Service;
use common\models\Type;
use common\models\Mark;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet;

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

                        'actions' => ['add-price', 'ajaxpaymentstatus', 'price', 'status', 'new2', 'active', 'archive', 'refuse', 'archive3', 'tender', 'tenders', 'newtender', 'fulltender', 'filtertender', 'tenderlist', 'updatetender', 'new', 'create', 'update', 'updatemember', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'delete', 'attribute', 'offer', 'undriver', 'subtype', 'closedownload', 'listitems', 'newitemlist', 'deleteitemlist', 'edititemlist', 'newtendattach', 'tendersexcel', 'exceltenders', 'controltender', 'newcontroltender', 'fullcontroltender', 'updatecontroltender', 'controlisarchive', 'archivetender', 'tendermembers', 'newtendermembers', 'fulltendermembers', 'updatetendermembers', 'newtenderlinks', 'map', 'membersontender', 'tendermemberwin', 'tenderownerlist', 'tenderowneradd', 'tenderownerupdate', 'tenderownerfull', 'pickup', 'ownerdelete', 'getcomments', 'uploadtenderexel', 'ajaxstatus', 'sendtotender', 'statplace', 'showstatplace', 'statprice', 'showstatprice', 'newaddress', 'updateaddress', 'deleteaddress'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['add-price', 'ajaxpaymentstatus', 'price', 'status', 'new2', 'active', 'archive', 'refuse', 'archive3', 'tender', 'tenders', 'newtender', 'fulltender', 'filtertender', 'tenderlist', 'updatetender', 'new', 'create', 'update', 'updatemember', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer', 'undriver', 'subtype', 'closedownload', 'listitems', 'newitemlist', 'deleteitemlist', 'edititemlist', 'newtendattach', 'tendersexcel', 'exceltenders', 'controltender', 'newcontroltender', 'fullcontroltender', 'updatecontroltender', 'controlisarchive', 'archivetender', 'tendermembers', 'newtendermembers', 'fulltendermembers', 'updatetendermembers', 'newtenderlinks', 'map', 'membersontender', 'tendermemberwin', 'tenderownerlist', 'tenderowneradd', 'tenderownerupdate', 'tenderownerfull', 'pickup', 'getcomments', 'ajaxstatus', 'sendtotender', 'statplace', 'showstatplace', 'statprice', 'showstatprice', 'newaddress', 'updateaddress', 'deleteaddress'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['add-price', 'ajaxpaymentstatus', 'price', 'status', 'new2', 'active', 'archive', 'refuse', 'archive3', 'tender', 'tenders', 'newtender', 'fulltender', 'filtertender', 'tenderlist', 'updatetender', 'new', 'create', 'update', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer', 'undriver', 'subtype', 'closedownload', 'listitems', 'newitemlist', 'deleteitemlist', 'edititemlist', 'newtendattach', 'tendersexcel', 'exceltenders', 'controltender', 'newcontroltender', 'fullcontroltender', 'updatecontroltender', 'controlisarchive', 'archivetender', 'tendermembers', 'newtendermembers', 'fulltendermembers', 'updatetendermembers', 'newtenderlinks', 'map', 'membersontender', 'tendermemberwin', 'tenderownerlist', 'tenderowneradd', 'tenderownerupdate', 'tenderownerfull', 'pickup', 'getcomments', 'ajaxstatus', 'sendtotender', 'statplace', 'showstatplace', 'statprice', 'showstatprice', 'newaddress', 'updateaddress', 'deleteaddress'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                    [
                        'actions' => ['archive', 'refuse', 'archive3', 'new', 'new2', 'create', 'update', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer', 'undriver', 'subtype', 'map', 'attribute', 'price'],
                        'allow' => true,
                        'roles' => [User::ROLE_ACCOUNT],
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

                    // Проверка на наличие цен
                    $existed = CompanyService::findOne([
                        'company_id' => $model->id,
                        'type_id' => $type_id,
                        'service_id' => $service_id,
                    ]);

                    $companyService = new CompanyService();
                    $companyService->company_id = $model->id;
                    $companyService->service_id = $service_id;
                    $companyService->type_id = $type_id;
                    $companyService->price = $price;

                    $companyService->save();

                    if ($price) {
                        // Добавление в историю изменения цен
                        $companyModel = Company::findOne(['id' => $model->id]);

                        $newChange = new Changes();
                        $newChange->type = Changes::TYPE_PRICE;
                        $newChange->sub_type = $companyModel->type;
                        $newChange->user_id = Yii::$app->user->identity->id;
                        $newChange->service_id = $service_id;

                        // Проверяем добавлена или изменена цена
                        if (isset($existed)) {
                            if (isset($existed->price)) {
                                if ($existed->price) {
                                    $newChange->old_value = (String)$existed->price;
                                    $newChange->status = Changes::EDIT_PRICE;
                                } else {
                                    $newChange->old_value = '0';
                                    $newChange->status = Changes::NEW_PRICE;
                                }
                            } else {
                                $newChange->old_value = '0';
                                $newChange->status = Changes::NEW_PRICE;
                            }
                        } else {
                            $newChange->old_value = '0';
                            $newChange->status = Changes::NEW_PRICE;
                        }

                        $newChange->new_value = (String)$price;
                        $newChange->company_id = $companyService->company_id;
                        $newChange->type_id = $companyService->type_id;
                        $newChange->date = (String)time();

                        if (($newChange->status == Changes::EDIT_PRICE) && (($newChange->old_value > $newChange->new_value) || ($newChange->old_value < $newChange->new_value))) {
                            $newChange->save();
                        } else if (($newChange->status == Changes::NEW_PRICE)) {
                            $newChange->save();
                        }
                        // Добавление в историю изменения цен
                    }

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

        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        // загрузка страницы с компаниями текущего пользователя
        if (isset($params['CompanySearch']['dep_user_id'])) {
            if ($params['CompanySearch']['dep_user_id'] > 0) {
                $searchModel->dep_user_id = $params['CompanySearch']['dep_user_id'];
                $dataProvider->query->andWhere(['department_company.user_id' => $params['CompanySearch']['dep_user_id']]);
            }
        } else {
            $exists = Company::find()->innerJoin('department_company', 'department_company.company_id = company.id')->where(['AND',['company.status' => $searchModel->status], ['company.type' => $searchModel->type], ['department_company.user_id' => Yii::$app->user->identity->id]])->exists();
            if (Yii::$app->user->identity->role != User::ROLE_ADMIN && ($exists)) {
                $searchModel->dep_user_id = Yii::$app->user->identity->id;
                $dataProvider->query->andWhere(['department_company.user_id' => Yii::$app->user->identity->id]);
            }
        }
        // загрузка страницы с компаниями текущего пользователя


        // Подкатегории для сервиса
        if ($type == 3) {
            $requestSupType = 0;

            if (Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if ($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
                'userData' => $userData,
                'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ]);
    }

    public function actionNew2($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_NEW2;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_NEW2);
        }

        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        // загрузка страницы с компаниями текущего пользователя
        if (isset($params['CompanySearch']['dep_user_id'])) {
            if ($params['CompanySearch']['dep_user_id'] > 0) {
                $searchModel->dep_user_id = $params['CompanySearch']['dep_user_id'];
                $dataProvider->query->andWhere(['department_company.user_id' => $params['CompanySearch']['dep_user_id']]);
            }
        } else {
            $exists = Company::find()->innerJoin('department_company', 'department_company.company_id = company.id')->where(['AND',['company.status' => $searchModel->status], ['company.type' => $searchModel->type], ['department_company.user_id' => Yii::$app->user->identity->id]])->exists();
            if (Yii::$app->user->identity->role != User::ROLE_ADMIN && ($exists)) {
                $searchModel->dep_user_id = Yii::$app->user->identity->id;
                $dataProvider->query->andWhere(['department_company.user_id' => Yii::$app->user->identity->id]);
            }
        }
        // загрузка страницы с компаниями текущего пользователя


        // Подкатегории для сервиса
        if ($type == 3) {
            $requestSupType = 0;

            if (Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if ($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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
            $badgeSearch->status = Company::STATUS_NEW2;
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
            $badgeSearch->status = Company::STATUS_NEW2;
            $badgeSearch->user_id = $user->id;
            $userData[$user->id] = ['badge' => $badgeSearch->search()->count, 'username' => $user->username];
        }

        $this->view->title = 'Заявки 2 - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
                'userData' => $userData,
                'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
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
        if ($type == 3) {
            $requestSupType = 0;

            if (Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if ($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
            }

        }
        // Подкатегории для сервиса

        $dataProvider->sort = [
            'defaultOrder' => [
                'address' => SORT_ASC,
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
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
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
        $searchModel->status = [Company::STATUS_ARCHIVE, Company::STATUS_ACTIVE];

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_ARCHIVE);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Подкатегории для сервиса
        if ($type == 3) {
            $requestSupType = 0;

            if (Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if ($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
            }

        }
        // Подкатегории для сервиса

        $dataProvider->sort = [
            'defaultOrder' => [
                'address' => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = [Company::STATUS_ARCHIVE, Company::STATUS_ACTIVE];
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Архив - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
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
        if ($type == 3) {
            $requestSupType = 0;

            if (Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if ($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
            }

        }
        // Подкатегории для сервиса

        $dataProvider->sort = [
            'defaultOrder' => [
                'address' => SORT_ASC,
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
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
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
        if ($type == 3) {
            $requestSupType = 0;

            if (Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if ($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
            }

        }
        // Подкатегории для сервиса

        $dataProvider->sort = [
            'defaultOrder' => [
                'address' => SORT_ASC,
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
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
            ]);
    }

    // Раздел тендеры
    public function actionTender($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_TENDER);
        }

        $dataProvider = $searchModel->searchTender(Yii::$app->request->queryParams);

        $dataProvider->sort = [
            'defaultOrder' => [
                'address' => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_TENDER;
            if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
                $badgeSearch->user_id = $currentUser->id;
            }
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Тендеры - ' . Company::$listType[$type]['ru'];

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'type' => $type,
                'model' => $model,
                'listType' => $listType,
            ]);
    }

    // Все тендеры
    public function actionTenderlist()
    {

        $currentUser = Yii::$app->user->identity;

        $searchModel = new TenderSearch(['scenario' => 'tenderlist']);

        if ($currentUser->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = $currentUser->getAllCompanyType(Company::STATUS_TENDER);
        }

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['OR', ['purchase_status' => 15], ['purchase_status' => 18], ['purchase_status' => 19], ['purchase_status' => 57], ['purchase_status' => 58], ['purchase_status' => 85]]);

        $usersOwner = TenderOwner::find()->innerJoin('user', 'user.id = tender_owner.tender_user')->andWhere(['AND', ['!=', 'tender_owner.tender_user', 0], ['is', 'tender_owner.tender_id', null], ['tender_owner.status' => 0]])->orWhere(['AND', ['!=', 'tender_owner.tender_user', 0], ['tender_owner.tender_id' => ''], ['tender_owner.status' => 0]])->orWhere(['AND', ['!=', 'tender_owner.tender_user', 0], ['is', 'tender_owner.tender_id', null], ['tender_owner.status' => 0]])->select('user.username')->asArray()->all();
        $arrusersOwner = [];

        if (count($usersOwner) > 0) {
        $arr1 = [];
        for ($i = 0; $i < count($usersOwner); $i++) {
            $arr1[$i] = $usersOwner[$i]['username'];
        }
        $arrusersOwner = array_count_values ($arr1);
        }

        return $this->render('tender/tenderlist',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'listType' => $listType,
                'usersList' => $usersList,
                'arrusersOwner' => $arrusersOwner,
            ]);
    }

    // Список договоров по дате окончания
    public function actionFiltertender()
    {

        $currentUser = Yii::$app->user->identity;

        $searchModel = new TenderSearch(['scenario' => 'tender']);

        if ($currentUser->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = $currentUser->getAllCompanyType(Company::STATUS_TENDER);
        }

        $searchModel->purchase_status = 22;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->sort = [
            'defaultOrder' => [
                'term_contract' => SORT_ASC,
            ]
        ];

        return $this->render('tender/filtertender',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
    }

    // Вкладка тендеры
    public function actionTenders($id)
    {

        $model = $this->findModel($id);

        $searchModel = Tender::find()->where(['company_id' => $id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'date_request_end' => SORT_ASC,
            ]
        ];

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        return $this->render('tenders', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'usersList' => $usersList,
        ]);

    }

    // Новый тендер
    public function actionNewtender($id)
    {
        $model = new Tender();
        $model->company_id = $id;

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            $model->files = UploadedFile::getInstances($model, 'files');

            if ($model->files) {

                if ($model->upload()) {
                    // file is uploaded successfully
                }
            }

            return $this->redirect(['company/tenders', 'id' => $model->company_id]);

        } else {
            return $this->render('form/newtender', [
                'id' => $id,
                'model' => $model,
                'usersList' => $usersList,
            ]);
        }
    }

    public function actionFulltender($tender_id)
    {

        $model = Tender::findOne(['id' => $tender_id]);
        $newmodel = new TenderControl();

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        $searchModel = TenderControl::find()->where(['tender_id' => $tender_id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        return $this->render('tender/fulltender', [
            'model' => $model,
            'usersList' => $usersList,
            'newmodel' => $newmodel,
            'dataProvider' => $dataProvider,
        ]);

    }

    // Участники тендеров
    public function actionTendermembers()
    {
        $model = TenderMembers::find()->all();

        $searchModel = new TenderMemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        return $this->render('tender-members/tendermembers', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);

    }

    public function actionNewtendermembers($id)
    {
        $model = new TenderMembers();
        $model->tender_id = $id;

        if (($model->load(Yii::$app->request->post())) && (Yii::$app->request->isPost)) {
            if ($model->save()) {
                return $this->redirect(['company/membersontender', 'id' => $id]);
            } else {
                return $this->redirect(['company/membersontender', 'id' => $id]);
            }

        } else {
            return $this->render('tender-members/newtendermembers', [
                'model' => $model,
                'id' => $id,
            ]);
        }
    }

    public function actionFulltendermembers($id)
    {
        $model = TenderMembers::findOne(['id' => $id]);
        $model->id = $id;

        $searchModel = new TenderMemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->innerJoin('tender_links', '`tender_links`.`member_id` = `tender_members`.`id`')->innerJoin('tender', '`tender`.`id` = `tender_links`.`tender_id` ')->where('tender_members.id=' . $id)->select('tender.id')->column();

        return $this->render('tender-members/fulltendermembers', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'id' => $id,
        ]);

    }

    public function actionMembersontender($id)
    {
        $model = Tender::findOne(['id' => $id]);

        $searchModel = new TenderMemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->innerJoin('tender_links', '`tender_links`.`member_id` = `tender_members`.`id`')->innerJoin('tender', '`tender`.`id` = `tender_links`.`tender_id` ')->where('tender_links.tender_id=' . $id)->select('tender_members.id, tender_members.company_name, tender_members.inn, tender_members.city, tender_members.comment')->column();

        return $this->render('tender-members/membersontender', [
            'model' => $model,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,

        ]);

    }

    public function actionUpdatetendermembers($id)
    {
        {
            $model = TenderMembers::findOne(['id' => $id]);

            $hasEditable = Yii::$app->request->post('hasEditable', false);
            if ($hasEditable) {
                Yii::$app->response->format = Response::FORMAT_JSON;

                // Подготовка данных перед сохранением
                $arrUpdate = Yii::$app->request->post();

                if ($model->load($arrUpdate) && $model->save()) {
                    $output = [];
                    return ['output' => implode(', ', $output), 'message' => ''];
                } else {
                    return ['message' => 'не получилось'];
                }
            } else {
                return ['message' => 'не получилось'];
            }
        }
    }

    public function actionNewtenderlinks()
    {
        $model = new TenderLinks();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            return $this->redirect(['company/tendermembers']);

        } else {
            return $this->render('tender-members/newtenderlinks', [
                'model' => $model,
            ]);
        }
    }

    public function actionTendermemberwin($tender_id, $member_id, $winner)
    {

        if ($winner == 1) {

            // Проверка
            $model = TenderLinks::findOne(['tender_id' => $tender_id, 'member_id' => $member_id]);

            if ($model->winner == 0) {

                TenderLinks::updateAll(['winner' => 0], ['tender_id' => $tender_id]);

                $model->winner = $winner;
                $model->save();
            }

        } else {
            $model = TenderLinks::findOne(['tender_id' => $tender_id, 'member_id' => $member_id, 'winner' => 1]);
            $model->winner = $winner;
            $model->save();
        }

        return $this->redirect(['company/membersontender', 'id' => $tender_id]);

    }

    public static function getCount($count)
    {

        $countTenders = TenderLinks::find()->where(['member_id' => $count])->count();

        return $countTenders;

    }

    // Раздел архив тендеров
    public function actionArchivetender($win)
    {
        $searchModel = new TenderSearch(['scenario' => 'tenderlist']);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if ($win) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 21], ['purchase_status' => 22]]);
        } else {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 16], ['purchase_status' => 17], ['purchase_status' => 20], ['purchase_status' => 23]]);
        }

        $dataProvider->sort = [
            'defaultOrder' => [
                'term_contract' => SORT_ASC,
            ]
        ];

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        return $this->render('tender/archivetender',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'win' => $win,
                'usersList' => $usersList,
            ]);
    }

    public function actionControltender()
    {

        $searchModel = new TenderControlSearch(['scenario' => 'all']);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        return $this->render('tender/controltender', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'usersList' => $usersList,

        ]);

    }

    public function actionAjaxpaymentstatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = TenderControl::findOne(['id' => $id]);
        $model->id = $id;
        $model->payment_status = $status;
        $model->save();

        return TenderControl::colorForPaymentStatus($model->payment_status);
    }


    public function actionNewcontroltender()
    {
        $id = Yii::$app->request->get('id');
        $model = new TenderControl();
        $model->tender_id = $id;

        $mailIrina = Yii::$app->mailer->compose();
        $mailGerbert = Yii::$app->mailer->compose();

        $userIrina = User::find()->where(['id' => 708])->select('email')->column();
        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            $model->filescont = UploadedFile::getInstances($model, 'filescont');

                if ($model->filescont) {

                    if ($model->upload()) {
                        // file is uploaded successfully
                    }
                }

            if ($model->filescont || $model->requisite) {
                $requisite = '';
                if ($model->requisite) {
                    $requisite = '<br/><b>Реквизиты:</b><br/>' . nl2br($model->requisite);
                }

                // отправка ирине уведомления об оплате тендера
                if (isset($userIrina[0])) {

                    $mailIrina = Yii::$app->mailer->compose()
                        ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo($userIrina[0])
                        ->setSubject('Срочно оплата тендера ' . date('d.m.Y'))
                        ->setHtmlBody('Оплата тендера:<br/><br/><b>Сумма:</b> ' . $model->send . $requisite . '<br/><br/>');

                        $pathfolder = \Yii::getAlias('@webroot/files/tender_control/' . $model->id . '/');

                        if (file_exists($pathfolder)) {

                            foreach (FileHelper::findFiles($pathfolder) as $file) {
                                $mailIrina->attach($pathfolder . basename($file));
                            }

                        }
                }

                // отправка герберту уведомления об оплате тендера
                $mailGerbert = Yii::$app->mailer->compose()
                    ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                    ->setTo('mtransservice@mail.ru')
                    ->setSubject('Срочно оплата тендера ' . date('d.m.Y'))
                    ->setHtmlBody('Оплата тендера:<br/><br/><b>Сумма:</b> ' . $model->send . $requisite . '<br/><br/>');

                    $pathfolder = \Yii::getAlias('@webroot/files/tender_control/' . $model->id . '/');

                    if (file_exists($pathfolder)) {

                        foreach (FileHelper::findFiles($pathfolder) as $file) {
                            $mailGerbert->attach($pathfolder . basename($file));
                        }

                    }

                    $mailGerbert->send();
                    $mailIrina->send();

            }

            return $this->redirect(['company/fulltender', 'tender_id' => $id]);

        }
    }


    public function actionUpdatecontroltender($id)
    {
        $model = TenderControl::findOne(['id' => $id]);

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // массив списков
            $arrayTenderList = TenderLists::find()->select('id, description, type')->orderBy('type, id')->asArray()->all();

            $arrLists = [];
            $oldType = -1;
            $tmpArray = [];

            for ($i = 0; $i < count($arrayTenderList); $i++) {

                if ($arrayTenderList[$i]['type'] == $oldType) {

                    $index = $arrayTenderList[$i]['id'];
                    $tmpArray[$index] = $arrayTenderList[$i]['description'];

                } else {

                    if ($i > 0) {

                        $arrLists[$oldType] = $tmpArray;
                        $tmpArray = [];

                        $oldType = $arrayTenderList[$i]['type'];

                        $index = $arrayTenderList[$i]['id'];
                        $tmpArray[$index] = $arrayTenderList[$i]['description'];

                    } else {
                        $oldType = $arrayTenderList[$i]['type'];
                        $tmpArray = [];

                        $index = $arrayTenderList[$i]['id'];
                        $tmpArray[$index] = $arrayTenderList[$i]['description'];
                    }
                }

                if (($i + 1) == count($arrayTenderList)) {
                    $arrLists[$oldType] = $tmpArray;
                }

            }
            //
            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            // Списки с данными
            $arrSiteAddress = isset($arrLists[8]) ? $arrLists[8] : [];
            $arrTypePayment = isset($arrLists[9]) ? $arrLists[9] : [];

            foreach ($arrUpdate as $name => $value) {
                if ($name == 'date_send') {
                    $arrUpdate['TenderControl'][$name] = (String)strtotime($value);
                } else if ($name == 'date_enlistment') {
                    $arrUpdate['TenderControl'][$name] = (String)strtotime($value);
                } else if ($name == 'money_unblocking') {
                    $arrUpdate['TenderControl'][$name] = (String)strtotime($value);
                } else if ($name == 'date_return') {
                    $arrUpdate['TenderControl'][$name] = (String)strtotime($value);
                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                if (Yii::$app->request->post('TenderControl')) {

                    foreach (Yii::$app->request->post('TenderControl') as $name => $value) {

                        if ($name == 'site_address') {
                            $output[] = $arrSiteAddress[$value];
                        } else if ($name == 'type_payment') {
                            $output[] = $arrTypePayment[$value];
                        } else if ($name == 'user_id') {
                            $output[] = $usersList[$value];
                        } else {
                            $output[] = $value;
                        }

                    }
                }
                if (Yii::$app->request->post('date_return')) {
                    $output[] = $value;
                } else if (Yii::$app->request->post('money_unblocking')) {
                    $output[] = $value;
                } else if (Yii::$app->request->post('date_enlistment')) {
                    $output[] = $value;
                } else if (Yii::$app->request->post('date_send')) {
                    $output[] = $value;
                }

                return ['output' => implode(', ', $output), 'message' => ''];

            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }
    }

    // Закрыть изменения controltender
    public function actionControlisarchive()
    {

        if (Yii::$app->request->get('id')) {

            $id = Yii::$app->request->get('id');
            $is_archive = Yii::$app->request->get('is_archive');

            $model = TenderControl::findOne(['id' => $id]);

            if ($is_archive == 1) {
                $model->is_archive = 0;
            } else {
                $model->is_archive = 1;
            }

            if ($model->save()) {
                return $this->redirect(['company/controltender']);
            } else {
                return $this->redirect(['company/controltender']);
            }

        } else {
            return $this->redirect(['/']);
        }

    }

    // Скачиваем файл Excel для заполнения
    public function actionTendersexcel()
    {
        $resExcel = self::createExcelTenders();

        $pathFile = Yii::getAlias('@webroot/files/tenders/filtertender.xls');

        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Content-Length: " . filesize($pathFile));
        header("Content-Disposition: attachment; filename=filtertender.xls");
        readfile($pathFile);

    }

    // Формирование Excel файла
    public static function createExcelTenders()
    {

        $arrTenders = Tender::find()->where(['purchase_status' => 22])->select('inn_customer, customer, city, service_type, number_purchase, place, date_contract, term_contract')->orderby('term_contract ASC')->asArray()->all();

        $objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Creating a workbook
        $objPHPExcel->getProperties()->setCreator('Mtransservice');
        $objPHPExcel->getProperties()->setTitle('Список договоров');
        $objPHPExcel->getProperties()->setSubject('Список договоров');
        $objPHPExcel->getProperties()->setDescription('');
        $objPHPExcel->getProperties()->setCategory('');
        $objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Список договоров');
        $objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getPageMargins()->setTop(2);
        $companyWorkSheet->getPageMargins()->setLeft(0.5);
        $companyWorkSheet->getRowDimension(1)->setRowHeight(1);
        $companyWorkSheet->getRowDimension(10)->setRowHeight(100);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        $row = 1;

        // Body
        if (count($arrTenders) > 0) {

            $companyWorkSheet->getColumnDimension('A')->setWidth(20);
            $companyWorkSheet->getColumnDimension('B')->setWidth(20);
            $companyWorkSheet->getColumnDimension('C')->setWidth(30);
            $companyWorkSheet->getColumnDimension('D')->setWidth(25);
            $companyWorkSheet->getColumnDimension('E')->setWidth(32);
            $companyWorkSheet->getColumnDimension('F')->setWidth(28);
            $companyWorkSheet->getColumnDimension('G')->setWidth(40);
            $companyWorkSheet->getColumnDimension('H')->setWidth(35);
            $companyWorkSheet->getColumnDimension('I')->setWidth(40);

            // Заголовки
            $companyWorkSheet->setCellValue('A' . $row, 'Заказчик');
            $companyWorkSheet->setCellValue('B' . $row, 'ИНН Заказчика');
            $companyWorkSheet->setCellValue('C' . $row, 'Город, Область поставки');
            $companyWorkSheet->setCellValue('D' . $row, 'Закупаемые услуги');
            $companyWorkSheet->setCellValue('E' . $row, 'Номер закупки на площадке');
            $companyWorkSheet->setCellValue('F' . $row, 'Электронная площадка');
            $companyWorkSheet->setCellValue('G' . $row, 'Дата заключения договора');
            $companyWorkSheet->setCellValue('H' . $row, 'Дата окончания заключенного договора');
            $companyWorkSheet->setCellValue('I' . $row, 'Осталось дней до окончания договора');

            $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            ));

            $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Times New Roman'
                    ],
                ]
            );

            $companyWorkSheet->getRowDimension($row)->setRowHeight(17);

            $row++;


            for ($i = 0; $i < count($arrTenders); $i++) {

                // массив списков
                $arrayTenderList = TenderLists::find()->where(['type' => 3])->select('id, description, type')->orderBy('type, id')->asArray()->all();

                $arrLists = [];
                $oldType = -1;
                $tmpArray = [];

                for ($j = 0; $j < count($arrayTenderList); $j++) {

                    if ($arrayTenderList[$j]['type'] == $oldType) {

                        $index = $arrayTenderList[$j]['id'];
                        $tmpArray[$index] = $arrayTenderList[$j]['description'];

                    } else {

                        if ($j > 0) {

                            $arrLists[$oldType] = $tmpArray;
                            $tmpArray = [];

                            $oldType = $arrayTenderList[$j]['type'];

                            $index = $arrayTenderList[$j]['id'];
                            $tmpArray[$index] = $arrayTenderList[$j]['description'];

                        } else {
                            $oldType = $arrayTenderList[$j]['type'];
                            $tmpArray = [];

                            $index = $arrayTenderList[$j]['id'];
                            $tmpArray[$index] = $arrayTenderList[$j]['description'];
                        }
                    }

                    if (($j + 1) == count($arrayTenderList)) {
                        $arrLists[$oldType] = $tmpArray;
                    }

                }

                $ServicesList = isset($arrLists[3]) ? $arrLists[3] : [];
                $stringServText = '';

                //
                if (isset($arrTenders[$i]['service_type'])) {

                    $serviseVal = explode(', ', $arrTenders[$i]['service_type']);

                    if ((is_array($serviseVal)) && (count($serviseVal) > 0)) {

                        for ($z = 0; $z < count($serviseVal); $z++) {

                            if ($z == (count($serviseVal) - 1)) {
                                if (isset($ServicesList[$serviseVal[$z]])) {
                                    $stringServText .= $ServicesList[$serviseVal[$z]];
                                } else {
                                    $stringServText .= "-";
                                }
                            } else {
                                if (isset($ServicesList[$serviseVal[$z]])) {
                                    $stringServText .= $ServicesList[$serviseVal[$z]];
                                    $stringServText .= ", ";
                                } else {
                                    $stringServText .= "-, ";
                                }
                            }

                        }

                    } else {

                        if ($arrTenders[$i]['service_type'] > 0) {
                            $index = $arrTenders[$i]['service_type'];
                            $stringServText = isset($ServicesList[$index]) ? ($ServicesList[$index] . ", ") : '-';
                        } else {
                            $stringServText = '-';
                        }

                    }

                } else {
                    $stringServText = '-';
                }

                $showTotal = '';

                if (isset($arrTenders[$i]['term_contract'])) {
                    $timeNow = time();


                    if ($arrTenders[$i]['term_contract'] > $timeNow) {

                        $totalDate = $arrTenders[$i]['term_contract'] - $timeNow;

                        $days = ((Int)($totalDate / 86400));
                        $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                        if ($days < 0) {
                            $days = 0;
                        }

                        $showTotal = $days . ' д.';

                    } else {
                        if (mb_strlen($arrTenders[$i]['term_contract']) > 3) {
                            $totalDate = $timeNow - $arrTenders[$i]['term_contract'];

                            $days = ((Int)($totalDate / 86400));
                            $totalDate -= (((Int)($totalDate / 86400)) * 86400);

                            if ($days < 0) {
                                $days = 0;
                            }
                            $showTotal = '- ' . $days . ' д.';
                        } else {
                            $showTotal = '-';
                        }
                    }

                }

                $companyWorkSheet->setCellValue('A' . $row, isset($arrTenders[$i]['customer']) ? (mb_strlen($arrTenders[$i]['customer']) > 0 ? $arrTenders[$i]['customer'] : '-') : '-');
                $companyWorkSheet->setCellValue('B' . $row, isset($arrTenders[$i]['inn_customer']) ? (mb_strlen($arrTenders[$i]['inn_customer']) > 0 ? $arrTenders[$i]['inn_customer'] : '-') : '-');
                $companyWorkSheet->setCellValue('C' . $row, isset($arrTenders[$i]['city']) ? (mb_strlen($arrTenders[$i]['city']) > 0 ? $arrTenders[$i]['city'] : '-') : '-');
                $companyWorkSheet->setCellValue('D' . $row, $stringServText);
                $companyWorkSheet->setCellValue('E' . $row, isset($arrTenders[$i]['number_purchase']) ? (mb_strlen($arrTenders[$i]['number_purchase']) > 0 ? $arrTenders[$i]['number_purchase'] : '-') : '-');
                $companyWorkSheet->setCellValue('F' . $row, isset($arrTenders[$i]['place']) ? (mb_strlen($arrTenders[$i]['place']) > 0 ? $arrTenders[$i]['place'] : '-') : '-');
                $companyWorkSheet->setCellValue('G' . $row, isset($arrTenders[$i]['date_contract']) ? (mb_strlen($arrTenders[$i]['date_contract']) > 3 ? date('d.m.Y', $arrTenders[$i]['date_contract']) : '-') : '-');
                $companyWorkSheet->setCellValue('H' . $row, isset($arrTenders[$i]['term_contract']) ? (mb_strlen($arrTenders[$i]['term_contract']) > 3 ? date('d.m.Y', $arrTenders[$i]['term_contract']) : '-') : '-');
                $companyWorkSheet->setCellValue('I' . $row, $showTotal);

                $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                ));

                $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                        'font' => [
                            'size' => 12,
                            'name' => 'Times New Roman'
                        ],
                    ]
                );

                $companyWorkSheet->getRowDimension($row)->setRowHeight(17);

                $row++;
            }
        }

        $objPHPExcel->getActiveSheet()->setSelectedCells('A1');

        //saving document
        $pathFile = \Yii::getAlias('@webroot/files/tenders/');

        if (!is_dir($pathFile)) {
            mkdir($pathFile, 0755, 1);
        }


        $filename = 'filtertender.xls';

        $objWriter->save($pathFile . $filename);
        return $filename;
    }

    // Закрыть изменения тендера
    public function actionClosedownload()
    {

        if (Yii::$app->request->post('tender_id')) {

            $id = Yii::$app->request->post('tender_id');
            $tender_close = Yii::$app->request->post('tender_close');

            $model = Tender::findOne(['id' => $id]);

            if ($tender_close == 1) {
                $model->tender_close = 0;
            } else {
                $model->tender_close = 1;
            }

            if ($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionTenderowneradd()
    {
        $model = new TenderOwner();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            return $this->redirect(['company/tenderownerlist?win=1']);

        } else {
            return $this->render('tender/tenderowneradd', [
                'model' => $model,
            ]);
        }
    }

    public function actionTenderownerfull($id)
    {
        $model = TenderOwner::findOne(['id' => $id]);
        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        return $this->render('tender/tenderownerfull', [
            'model' => $model,
            'usersList' => $usersList,
        ]);
    }

    public function actionTenderownerupdate($id)
    {
        $model = TenderOwner::findOne(['id' => $id]);
        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();


        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();
            foreach ($arrUpdate['TenderOwner'] as $name => $value) {
                if ($name == 'date_from') {
                    $arrUpdate['TenderOwner'][$name] = (String)strtotime($value);
                } else if ($name == 'date_to') {
                    $arrUpdate['TenderOwner'][$name] = (String)strtotime($value);
                } else if ($name == 'reason_not_take') {
                    $model->user_comment = Yii::$app->user->identity->id;
                    $model->save();
                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];

                foreach (Yii::$app->request->post('TenderOwner') as $name => $value) {

                    if ($name == 'tender_user') {
                        $output[] = $usersList[$value];
                    } else if ($name == 'purchase') {
                        $output[] = $value . " ₽";
                    } else {
                        $output[] = $value;
                    }

                }

                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }

    }

    public function actionTenderownerlist($win)
    {
        $model = TenderOwner::find()->all();

        $searchModel = new TenderOwnerSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->sort = [
            'defaultOrder' => [
                'status' => SORT_ASC,
                'date_to' => SORT_ASC,
                'purchase' => SORT_DESC,
            ]
        ];
        if ($win == 1) {
            $dataProvider->query->andwhere(['AND', ['tender_user' => 0], ['status' => 0]]);
        } else if ($win == 2) {
            $dataProvider->query->andWhere(['AND', ['!=', 'tender_user', 0], ['!=', 'tender_id', ''], ['NOT', ['tender_id' => null]]])->orderBy('tender_user');
        } else if ($win == 3) {
            $dataProvider->query->andwhere(['>', 'status', 0]);
        } else {
            $dataProvider->query->andWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['status' => 0]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['status' => 0]])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['status' => 0]])->orderBy('tender_user');
        }


        return $this->render('tender/tenderownerlist', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'win' => $win,
            'model' => $model,
        ]);

    }

    public function actionAjaxstatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = TenderOwner::findOne(['id' => $id]);
        $model->id = $id;
        $model->status = $status;
        $model->save();

    }

    public function actionOwnerdelete($id)
    {
        TenderOwner::findOne(['id' => $id])->delete();

        // Удаляем
        Yii::$app->db->createCommand()->delete('{{%tender_owner}}', ['id' => $id])->execute();

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionPickup($id, $tender_user, $data)
    {

        $model = TenderOwner::findOne(['id' => $id]);
        $model->tender_user = $tender_user;
        $model->data = $data;
        $model->save();

        return $this->redirect(['company/tenderownerlist?win=1']);
    }

    public function actionSendtotender($id)
    {
        $TenderOwner = TenderOwner::findOne(['id' => $id]);
        // Проверка на сущ. инн заказчика
        if (isset($TenderOwner->inn_customer)) {
            if ($TenderOwner->inn_customer) {
                // Получаем ID компании
                $inn = CompanyInfo::find()->innerJoin('company', '`company`.`id` = `company_info`.`company_id` AND `company`.`status` = 5')->where(['company_info.inn' => $TenderOwner->inn_customer])->select('company_info.company_id')->column();
                // Проверяем на сущ. ID компании
                if (isset($inn[0])) {
                    if ($inn[0]) {
                        $tender = new Tender();
                        $tender->company_id = $inn[0];
                        $tender->customer = $TenderOwner->customer;
                        $tender->price_nds = $TenderOwner->purchase;
                        $tender->user_id = $TenderOwner->tender_user;
                        $tender->inn_customer = $TenderOwner->inn_customer;
                        $tender->purchase = $TenderOwner->purchase_name;
                        $tender->city = $TenderOwner->city;

                        // проверка на существование даты
                        if (isset($TenderOwner->date_from)) {
                            if ($TenderOwner->date_from) {
                            $tender->date_request_start = date('H:i d-m-Y', (int) $TenderOwner->date_from);
                            }
                        }
                        if (isset($TenderOwner->date_to)) {
                            if ($TenderOwner->date_to) {
                            $tender->date_request_end = date('H:i d-m-Y', (int) $TenderOwner->date_to);
                            }
                        }
                        if (isset($TenderOwner->date_bidding)) {
                            if ($TenderOwner->date_bidding) {
                                $tender->time_bidding_start = date('H:i d-m-Y', (int) $TenderOwner->date_bidding);
                            }
                        }
                        if (isset($TenderOwner->date_consideration)) {
                            if ($TenderOwner->date_consideration) {
                                $tender->time_request_process = date('H:i d-m-Y', (int) $TenderOwner->date_consideration);
                            }
                        }

                        $tender->site = $TenderOwner->link_official;
                        $tender->link = $TenderOwner->link;
                        $tender->customer_full = $TenderOwner->customer_full;
                        $tender->request_security = $TenderOwner->request_security;
                        $tender->number_purchase = $TenderOwner->number;
                        if ($tender->save()) {
                            $TenderOwner->tender_id = $tender->id;
                            $TenderOwner->save();
                            return $this->redirect(['/company/fulltender', 'tender_id' => $tender->id]);
                        } else {
                            return $this->redirect(['/company/tenderownerlist', 'win' => 0]);
                        }
                    } else {
                        // Если нет ID компании создаем компанию, записываем пользователя, записываем инн, создаем тендер
                        if (isset($TenderOwner->customer)) {
                            if ($TenderOwner->customer) {
                                $company = new Company();
                                $company->name = $TenderOwner->customer;
                                $company->address = $TenderOwner->city;
                                $company->status = 5;
                                $company->type = 1;

                                if ($company->save()) {
                                    $TenderHystory = new TenderHystory();
                                    $TenderHystory->company_id = $company->id;
                                    $TenderHystory->user_id = $TenderOwner->tender_user;
                                    $TenderHystory->remove_id = 0;
                                    $TenderHystory->save();

                                    $companyInfo = new CompanyInfo();
                                    $companyInfo->company_id = $company->id;
                                    $companyInfo->inn = $TenderOwner->inn_customer;
                                    $companyInfo->save();

                                    $tender = new Tender();
                                    $tender->company_id = $company->id;
                                    $tender->customer = $TenderOwner->customer;
                                    $tender->price_nds = $TenderOwner->purchase;
                                    $tender->user_id = $TenderOwner->tender_user;
                                    $tender->inn_customer = $TenderOwner->inn_customer;
                                    $tender->purchase = $TenderOwner->purchase_name;
                                    $tender->city = $TenderOwner->city;

                                    // проверка на существование даты
                                    if (isset($TenderOwner->date_from)) {
                                        if ($TenderOwner->date_from) {
                                            $tender->date_request_start = date('H:i d-m-Y', (int) $TenderOwner->date_from);
                                        }
                                    }
                                    if (isset($TenderOwner->date_to)) {
                                        if ($TenderOwner->date_to) {
                                            $tender->date_request_end = date('H:i d-m-Y', (int) $TenderOwner->date_to);
                                        }
                                    }
                                    if (isset($TenderOwner->date_bidding)) {
                                        if ($TenderOwner->date_bidding) {
                                            $tender->time_bidding_start = date('H:i d-m-Y', (int) $TenderOwner->date_bidding);
                                        }
                                    }
                                    if (isset($TenderOwner->date_consideration)) {
                                        if ($TenderOwner->date_consideration) {
                                            $tender->time_request_process = date('H:i d-m-Y', (int) $TenderOwner->date_consideration);
                                        }
                                    }

                                    $tender->site = $TenderOwner->link_official;
                                    $tender->link = $TenderOwner->link;
                                    $tender->customer_full = $TenderOwner->customer_full;
                                    $tender->request_security = $TenderOwner->request_security;
                                    $tender->number_purchase = $TenderOwner->number;

                                    if ($tender->save()) {
                                        $TenderOwner->tender_id = $tender->id;
                                        $TenderOwner->save();
                                        return $this->redirect(['/company/fulltender', 'tender_id' => $tender->id]);
                                    } else {
                                        return $this->redirect(['/company/tenderownerlist', 'win' => 0]);
                                    }
                                } else {
                                    print_r($company->errors);
                                }
                        } else {
                            echo 'Компания не заполнена';
                        }
                    } else {
                            echo 'Компания не заполнена';
                    }
                }
                } else {
                    // Если нет ID компании создаем компанию, записываем пользователя, записываем инн, создаем тендер
                    if (isset($TenderOwner->customer)) {
                        if ($TenderOwner->customer) {
                            $company = new Company();
                            $company->name = $TenderOwner->customer;
                            $company->address = $TenderOwner->city;
                            $company->status = 5;
                            $company->type = 1;

                            if ($company->save()) {
                                $TenderHystory = new TenderHystory();
                                $TenderHystory->company_id = $company->id;
                                $TenderHystory->user_id = $TenderOwner->tender_user;
                                $TenderHystory->remove_id = 0;
                                $TenderHystory->save();

                                $companyInfo = new CompanyInfo();
                                $companyInfo->company_id = $company->id;
                                $companyInfo->inn = $TenderOwner->inn_customer;
                                $companyInfo->save();

                                $tender = new Tender();
                                $tender->company_id = $company->id;
                                $tender->customer = $TenderOwner->customer;
                                $tender->price_nds = $TenderOwner->purchase;
                                $tender->user_id = $TenderOwner->tender_user;
                                $tender->inn_customer = $TenderOwner->inn_customer;
                                $tender->purchase = $TenderOwner->purchase_name;
                                $tender->city = $TenderOwner->city;

                                // проверка на существование даты
                                if (isset($TenderOwner->date_from)) {
                                    if ($TenderOwner->date_from) {
                                        $tender->date_request_start = date('H:i d-m-Y', (int) $TenderOwner->date_from);
                                    }
                                }
                                if (isset($TenderOwner->date_to)) {
                                    if ($TenderOwner->date_to) {
                                        $tender->date_request_end = date('H:i d-m-Y', (int) $TenderOwner->date_to);
                                    }
                                }
                                if (isset($TenderOwner->date_bidding)) {
                                    if ($TenderOwner->date_bidding) {
                                        $tender->time_bidding_start = date('H:i d-m-Y', (int) $TenderOwner->date_bidding);
                                    }
                                }
                                if (isset($TenderOwner->date_consideration)) {
                                    if ($TenderOwner->date_consideration) {
                                        $tender->time_request_process = date('H:i d-m-Y', (int) $TenderOwner->date_consideration);
                                    }
                                }

                                $tender->site = $TenderOwner->link_official;
                                $tender->link = $TenderOwner->link;
                                $tender->customer_full = $TenderOwner->customer_full;
                                $tender->request_security = $TenderOwner->request_security;
                                $tender->number_purchase = $TenderOwner->number;

                                    if ($tender->save()) {
                                        $TenderOwner->tender_id = $tender->id;
                                        $TenderOwner->save();
                                        return $this->redirect(['/company/fulltender', 'tender_id' => $tender->id]);
                                    } else {
                                        return $this->redirect(['/company/tenderownerlist', 'win' => 0]);
                                    }
                            } else {
                                print_r($company->errors);
                            }
                        } else {
                            echo 'Компания не заполнена';
                        }
                    } else {
                        echo 'Компания не заполнена';
                    }
                }
            } else {
                echo 'ИНН не заполнен';
            }
        } else {
            echo 'ИНН не заполнен';
        }
    }

    public function actionUploadtenderexel()
    {
        if (Yii::$app->request->isPost) {
            // Загрузка в распределение тендеров из экселя
            $uploadFile = UploadedFile::getInstanceByName('files');

            if (isset($uploadFile)) {

                // Проверяем что загружен Excel файл
                $arrFileName = explode('.', $uploadFile->name);
                $countArrFileName = count($arrFileName) - 1;

                if (($arrFileName[$countArrFileName] == 'xlsx') || ($arrFileName[$countArrFileName] == 'xls')) {
                    $pExcel = PHPExcel_IOFactory::load($uploadFile->tempName);

                    // Загружаем только первую страницу
                    $firstPage = false;
                    $tables = [];

                    foreach ($pExcel->getWorksheetIterator() as $worksheet) {

                        if ($firstPage == false) {
                            $tables[] = $worksheet->toArray();
                            $firstPage = true;
                        }

                    }

                    $tables = $tables[0];

                    // Цикл по строкам
                    $numRows = count($tables);

                    $numTrueDis = 0;

                    if ($numRows > 1) {

                        for ($i = 0; $i < $numRows; $i++) {

                            // Цикл по столбцам
                            if ($i > 0) {

                                $numCol = count($tables[$i]);

                                if ($numCol > 1) {
                                    // Проверка если эксель 27 столбцов
                                    if (isset($tables[0][27])) {
                                        $number = (String) $tables[$i][0];
                                        $date_from = (str_replace('/', '-', (String) $tables[$i][2]));
                                        $date_to = (str_replace('/', '-', (String) $tables[$i][3]));
                                        $date_bidding = (str_replace('/', '-', (String) $tables[$i][4]));
                                        $date_consideration = (str_replace('/', '-', (String) $tables[$i][5]));

                                        $purchase_name = (str_replace('\\', '', (String) $tables[$i][6]));
                                        $purchase_name = (str_replace('&#034;', '', $purchase_name));

                                        $fz = $tables[$i][7];
                                        $customer = $tables[$i][11];
                                        $customer_full = $tables[$i][12];
                                        $inn_customer = (String) $tables[$i][13];
                                        $purchase = str_replace(',', '', (String) $tables[$i][17]);
                                        $city = $tables[$i][19];
                                        $link_official = $tables[$i][21];
                                        $request_security = str_replace(',', '', (String) $tables[$i][22]);
                                        $electronic_platform = $tables[$i][26];
                                        $link = $tables[$i][27];
                                    } else {
                                        $number = '';
                                        $date_from = (str_replace('/', '-', (String) $tables[$i][2]));
                                        $date_to = (str_replace('/', '-', (String) $tables[$i][3]));
                                        $date_bidding = '';
                                        $date_consideration = '';

                                        $purchase_name = (str_replace('\\', '', (String) $tables[$i][1]));
                                        $purchase_name = (str_replace('&#034;', '', $purchase_name));

                                        $fz = '';
                                        $customer = $tables[$i][5];
                                        $customer_full = '';
                                        $inn_customer = '';
                                        $purchase = str_replace(',', '', (String) $tables[$i][4]);
                                        $city = $tables[$i][8];
                                        $link_official = $tables[$i][7];
                                        $request_security = '';
                                        $electronic_platform = $tables[$i][6];
                                        $link = '';
                                    }
                                    if ($date_from && $customer) {

                                        $model = new TenderOwner();
                                        $model->number = $number;
                                        $model->date_from = $date_from;
                                        $model->date_to = $date_to;
                                        $model->date_bidding = $date_bidding;
                                        $model->date_consideration = $date_consideration;
                                        $model->purchase_name = $purchase_name;
                                        $model->fz = $fz;
                                        $model->customer = $customer;
                                        $model->customer_full = $customer_full;
                                        $model->inn_customer = $inn_customer;
                                        $model->purchase = $purchase;
                                        $model->city = $city;
                                        $model->link_official = $link_official;
                                        $model->request_security = $request_security;
                                        $model->electronic_platform = $electronic_platform;
                                        $model->link = $link;
                                        $model->save();


                                        $numTrueDis++;
                                    }
                                }
                            }
                        }

                        if ($numTrueDis > 0) {
                            return $this->redirect(['/company/tenderownerlist', 'win' => 1]);
                        }
                    }
                }
            }
        }
    }

    // Получение списков для изменения в тендерах
    public function actionListitems()
    {

        $type = Yii::$app->request->post('type');

        $resArray = TenderLists::find()->where(['type' => $type])->select('id, description, required')->orderBy('id')->asArray()->all();

        echo json_encode(['success' => 'true', 'items' => json_encode($resArray)]);

    }

    // Добавление нового элемента списка
    public function actionNewitemlist()
    {

        if(Yii::$app->request->post('name')) {

            $type = Yii::$app->request->post('type');
            $name = Yii::$app->request->post('name');
            $required = Yii::$app->request->post('required');

            $model = new TenderLists();
            $model->description = $name;
            $model->required = $required;
            $model->type = $type;

            if($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    // Удаление элемента списка
    public function actionDeleteitemlist()
    {

        if(Yii::$app->request->post('item_id')) {

            $item_id = Yii::$app->request->post('item_id');

            $model = TenderLists::findOne(['id' => $item_id]);

            if($model->delete()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    // Изменение элемента списка
    public function actionEdititemlist()
    {

        if((Yii::$app->request->post('id')) && (Yii::$app->request->post('name'))) {

            $id = Yii::$app->request->post('id');
            $name = Yii::$app->request->post('name');

            $model = TenderLists::findOne(['id' => $id]);
            $model->description = $name;

            if($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionUpdatetender($id)
    {
        $model = Tender::findOne(['id' => $id]);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // массив списков
            $arrayTenderList = TenderLists::find()->select('id, description, type')->orderBy('type, id')->asArray()->all();

            $arrLists = [];
            $oldType = -1;
            $tmpArray = [];

            for ($i = 0; $i < count($arrayTenderList); $i++) {

                if($arrayTenderList[$i]['type'] == $oldType) {

                    $index = $arrayTenderList[$i]['id'];
                    $tmpArray[$index] = $arrayTenderList[$i]['description'];

                } else {

                    if($i > 0) {

                        $arrLists[$oldType] = $tmpArray;
                        $tmpArray = [];

                        $oldType = $arrayTenderList[$i]['type'];

                        $index = $arrayTenderList[$i]['id'];
                        $tmpArray[$index] = $arrayTenderList[$i]['description'];

                    } else {
                        $oldType = $arrayTenderList[$i]['type'];
                        $tmpArray = [];

                        $index = $arrayTenderList[$i]['id'];
                        $tmpArray[$index] = $arrayTenderList[$i]['description'];
                    }
                }

                if(($i + 1) == count($arrayTenderList)) {
                    $arrLists[$oldType] = $tmpArray;
                }

            }
            //
            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            // Списки с данными
            $ServicesList = isset($arrLists[3]) ? $arrLists[3] : [];
            $arrFZlist = isset($arrLists[4]) ? $arrLists[4] : [];
            $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();
            $arrPurchstatus = isset($arrLists[0]) ? $arrLists[0] : [];
            $arrMethods = isset($arrLists[2]) ? $arrLists[2] : [];
            $arrStatusRequestlist = isset($arrLists[6]) ? $arrLists[6] : [];
            $arrStatusContractlist = isset($arrLists[7]) ? $arrLists[7] : [];
            $arrSitelist = isset($arrLists[8]) ? $arrLists[8] : [];

            foreach ($arrUpdate['Tender'] as $name => $value) {
                if($name == 'date_search') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'status_request_security') {
                    $arrUpdate['Tender']['date_status_request'] = (String) time();
                } else if($name == 'status_contract_security') {
                    $arrUpdate['Tender']['date_status_contract'] = (String) time();
                } else if($name == 'date_request_start') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'date_request_end') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'time_request_process') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'time_bidding_start') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'time_bidding_end') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'date_contract') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'term_contract') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'work_user_id') {

                    if($value == '') {
                        $arrUpdate['Tender'][$name] = 0;
                    }

                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                foreach (Yii::$app->request->post('Tender') as $name => $value) {

                    if ($name == 'service_type') {
                        $output[] = $ServicesList[$value];
                    } else if ($name == 'user_id') {
                        $output[] = $usersList[$value];
                    } else if ($name == 'percent_down') {
                        $output[] = $value . "%";
                    } else if ($name == 'percent_max') {
                        $output[] = $value . "%";
                    } else if ($name == 'federal_law') {
                        $output[] = $arrFZlist[$value];
                    } else if ($name == 'purchase_status') {
                        $output[] = $arrPurchstatus[$value];
                    } else if ($name == 'method_purchase') {
                        $output[] = $arrMethods[$value];
                    } else if ($name == 'status_request_security') {
                        $output[] = $arrStatusRequestlist[$value];
                    } else if ($name == 'status_contract_security') {
                        $output[] = $arrStatusContractlist[$value];
                    } else if ($name == 'site_address') {
                        $output[] = $arrSitelist[$value];
                    } else if ($name == 'price_nds' || $name == 'pre_income' || $name == 'final_price' || $name == 'contract_security' || $name == 'maximum_purchase_price' || $name == 'cost_purchase_completion' || $name == 'maximum_purchase_nds' || $name == 'maximum_purchase_notnds' || $name == 'maximum_agreed_calcnds' || $name == 'maximum_agreed_calcnotnds' || $name == 'site_fee_participation' || $name == 'ensuring_application' || $name == 'last_sentence_nds' || $name == 'last_sentence_nonds') {
                        $output[] = $value . " ₽";
                    } else if($name == 'work_user_id') {

                        if($value > 0) {

                            $workUserArr = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['OR', ['department_id' => 1], ['department_id' => 7]])->select('user.id, user.username')->asArray()->all();

                            $workUserData = [];

                            foreach ($workUserArr as $key => $result) {
                                $index = $result['id'];
                                $workUserData[$index] = trim($result['username']);
                            }

                            $output[] = isset($workUserData[$value]) ? $workUserData[$value] : '';

                        } else {
                            $output[] = '';
                        }

                    } else {
                        $output[] = $value;
                    }

                }
                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }
    }

    public function actionGetcomments()
    {

        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $model = TenderOwner::findOne(['id' => $id]);
            $userLists = User::find()->select('username')->indexby('id')->column();

            if (isset($model->reason_not_take)) {
                if (isset($model->user_comment)) {
                $resComm = "<u style='color:#757575;'>Комментарий от</u><b> " . $userLists[$model->user_comment] . "</b>: " . nl2br($model->reason_not_take) . "<br />";
                } else {
                $resComm = "<u style='color:#757575;'>Комментарий:</u> " . nl2br($model->reason_not_take) . "<br />";
                }
            } else {
                $resComm = "<u style='color:#757575;'>Нет комментария</u><br />";
            }

            echo json_encode(['success' => 'true', 'comment' => $resComm]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    /**
     * Creates Company model.
     * @return mixed
     */
    public function actionCreate($sub = 0)
    {
        $model = new Company();
        $model->status = Company::STATUS_NEW;
        $model->name = 'Без названия ' . rand(1, 1000);
        $model->address = 'Неизвестный';

        if ($model->load(Yii::$app->request->get()) && $model->save()) {

            if($model->status != Company::STATUS_TENDER) {

                if($model->status != Company::STATUS_ARCHIVE) {
                    $DepartmentCompany = new DepartmentCompany();
                    $DepartmentCompany->company_id = $model->id;
                    $DepartmentCompany->user_id = Yii::$app->user->identity->id;
                    $DepartmentCompany->remove_id = 0;
                    if ($model->status == Company::STATUS_NEW2) {
                        $DepartmentCompany->type_user = 1;
                    } else {
                        $DepartmentCompany->type_user = 0;
                    }
                    $DepartmentCompany->save();
                } else {
                    $DepartmentCompany = new DepartmentCompany();
                    $DepartmentCompany->company_id = $model->id;
                    $DepartmentCompany->user_id = Yii::$app->user->identity->id;
                    $DepartmentCompany->remove_date = (String) time();
                    $DepartmentCompany->remove_id = Yii::$app->user->identity->id;
                    $DepartmentCompany->save();
                }

            } else {
                $TenderHystory = new TenderHystory();
                $TenderHystory->company_id = $model->id;
                $TenderHystory->user_id = Yii::$app->user->identity->id;
                $TenderHystory->remove_id = 0;
                $TenderHystory->save();
            }

            if($sub > 0) {
                // Подкатегории для сервиса
                $modelSub = new CompanySubType();
                $modelSub->company_id = $model->id;
                $modelSub->sub_type = $sub;
                $modelSub->save();
                // Подкатегории для сервиса
            }

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
                    } else if ($name == 'car_type') {
                        $resType = "";

                        if($value == 0) {
                            $resType = "Грузовой транспорт";
                        } else if($value == 1) {
                            $resType = "Легковой транспорт";
                        } else if($value == 2) {
                            $resType = "Грузовой и легковой транспорт";
                        }
                        $output[] = $resType;
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

        $modelevent = EntryEvent::findOne(['company_id' => $model->id]);
        if (isset($modelevent)) {
            $modelevent = EntryEvent::findOne(['company_id' => $model->id]);
        } else {
            $modelevent = new EntryEvent();
        }

        return $this->render('offer', [
            'modelCompany' => $model,
            'modelCompanyInfo' => $modelCompanyInfo,
            'modelCompanyOffer' => $modelCompanyOffer,
            'admin' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            'modelevent' => $modelevent,
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

            } else if(isset($arrCompanyMember['show_member'][$id])) {

                $newVal = $arrCompanyMember['show_member'][$id];
                $ListValue = '';

                if ($newVal == 1) {
                    $ListValue = 'Да';
                } else {
                    $ListValue = 'Нет';
                }

                $companyMember = CompanyMember::findOne($id);
                $companyMember->show_member = $newVal;

                if ($companyMember->save()) {
                    return json_encode(['output' => $ListValue, 'message' => '']);
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
/*        if ($model->status == Company::STATUS_TENDER) {

        }*/

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

            if ($model->files) {

                if ($model->upload()) {
                    // file is uploaded successfully
                }
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

    public function actionNewtendattach($id)
    {

        $modelAddAttach = new DynamicModel(['files']);
        $modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

        $filesArr = UploadedFile::getInstances($modelAddAttach, 'files');

        $filePath = \Yii::getAlias('@webroot/files/tenders/' . $id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/tenders/'))) {
            mkdir(\Yii::getAlias('@webroot/files/tenders/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/tenders/' . $id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/tenders/' . $id . '/'), 0775);
        }

        foreach ($filesArr as $file) {

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

        return $this->redirect(['company/fulltender', 'tender_id' => $id]);

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

    public function actionStatplace($type)
    {
       $namePlace = TenderLists::find()->select('description')->indexby('id')->column();

        $searchModel = new TenderSearch(['scenario' => 'statplace']);
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        // победные
        if ($type == 1) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 21], ['purchase_status' => 22]])->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->groupBy('site_address')->select(['site_address', 'link' => 'COUNT(site_address)']);
            // проигранные
        } else if ($type == 2) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 17], ['purchase_status' => 23]])->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->groupBy('site_address')->select(['site_address', 'link' => 'COUNT(site_address)']);
            // отклоненные
        } else if ($type == 3) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 16], ['purchase_status' => 20]])->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->groupBy('site_address')->select(['site_address', 'link' => 'COUNT(site_address)']);
            // общие
        } else {
            $dataProvider->query->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->groupBy('site_address')->select(['site_address', 'link' => 'COUNT(site_address)']);
        }

        return $this->render('/stattender/statplace', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'namePlace' => $namePlace,
            'type' => $type,
        ]);
    }

    public function actionShowstatplace($site_address, $type)
    {

        $namePlace = TenderLists::find()->select('description')->indexby('id')->column();

        $searchModel = new TenderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // победные
        if ($type == 1) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 21], ['purchase_status' => 22]])->andWhere(['site_address' => $site_address])->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $searchModel->dateFrom, $searchModel->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);
            // проигранные
        } else if ($type == 2) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 17], ['purchase_status' => 23]])->andWhere(['site_address' => $site_address])->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $searchModel->dateFrom, $searchModel->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);
            // отклоненные
        } else if ($type == 3) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 16], ['purchase_status' => 20]])->andWhere(['site_address' => $site_address])->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $searchModel->dateFrom, $searchModel->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);
            // общие
        } else {
            $dataProvider->query->andWhere(['site_address' => $site_address])->andWhere(['OR', ['between', "DATE(FROM_UNIXTIME(date_request_end))", $searchModel->dateFrom, $searchModel->dateTo], ['is', 'date_request_end', null], ['date_request_end' => '']]);
        }

        return $this->render('/stattender/statplace', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'type' => $type,
            'namePlace' => $namePlace,
        ]);

    }

    public function actionStatprice($type)
    {
        {
            $namePlace = TenderLists::find()->select('description')->indexby('id')->column();

            $searchModel = new TenderControlSearch(['scenario' => 'statprice']);
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

                 // Возвратные
            if ($type == 1) {
                $dataProvider->query->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->andWhere(['payment_status' => 1])->groupBy('site_address')->select(['site_address', 'send' => 'SUM(send)']);
                // Невозвратные
            } else {
                $dataProvider->query->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->andWhere(['payment_status' => 0])->groupBy('site_address')->select(['site_address', 'send' => 'SUM(send)']);
            }


            return $this->render('/stattender/statprice', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'namePlace' => $namePlace,
                'type' => $type,
            ]);
        }
    }

    public function actionShowstatprice($site_address, $type)
    {
        {
            $namePlace = TenderLists::find()->select('description')->indexby('id')->column();

            $searchModel = new TenderControlSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            // Возвратные
            if ($type == 1) {
                $dataProvider->query->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->andWhere(['payment_status' => 1])->andWhere(['site_address' => $site_address])->andWhere(['between', "DATE(FROM_UNIXTIME(date_send))", $searchModel->dateFrom, $searchModel->dateTo]);
                // Невозвратные
            } else {
                $dataProvider->query->andWhere(['AND', ['!=', 'site_address', ''], ['!=', 'site_address', 76]])->andWhere(['payment_status' => 0])->andWhere(['site_address' => $site_address])->andWhere(['between', "DATE(FROM_UNIXTIME(date_send))", $searchModel->dateFrom, $searchModel->dateTo]);
            }


            return $this->render('/stattender/statprice', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'namePlace' => $namePlace,
                'type' => $type,
            ]);
        }
    }

    public function actionNewaddress($id)
    {
        $array = [];

        if (Yii::$app->request->post()) {
            $array = Yii::$app->request->post();
            $address = '';
            $city = '';
            $street = '';

            foreach ($array['CompanyAddress']['address']['type'] as $i => $value) {
                    if ($array['CompanyAddress']['address']['city'][$i]) {
                        $city = $array['CompanyAddress']['address']['city'][$i];
                    } else {
                        $city = '';
                    }

                    if ($array['CompanyAddress']['address']['street'][$i]) {
                        $street = $array['CompanyAddress']['address']['street'][$i];
                    } else {
                        $street = '';
                    }

                    if ($array['CompanyAddress']['address']['building'][$i]) {
                        $building = $array['CompanyAddress']['address']['building'][$i];
                    } else {
                        $building = '';
                    }

                    if ($array['CompanyAddress']['address']['index'][$i]) {
                        $index = $array['CompanyAddress']['address']['index'][$i];
                    } else {
                        $index = '';
                    }

                    $address = $city . ', ' . $street . ', ' . $building . ', ' . $index;

                    if ($city && $street) {
                        if ($array['CompanyAddress']['address']['type'][$i] > 0) {
                            $model = new CompanyAddress();
                            $model->company_id = $id;
                            $model->type = $array['CompanyAddress']['address']['type'][$i];
                            $model->address = $address;
                            $model->save();
                        }
                    }
                }
        }

        return $this->redirect(Yii::$app->request->referrer);

    }

    public function actionUpdateaddress($id)
    {
        $model = CompanyAddress::findOne(['id' => $id]);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }
    }

    public function actionDeleteaddress()
    {
        if (Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');
            CompanyAddress::deleteAll(['id' => $id]);

            return $this->redirect(Yii::$app->request->referrer);

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

        // Массив Типов ТС и Марок ТС
        $arrTypes = Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column();
        $arrMarks = Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column();

        return $this->render('driver', [
            'model' => $modelCompanyMember,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'arrTypes' => $arrTypes,
            'arrMarks' => $arrMarks,
        ]);
    }

    public function actionUndriver($id)
    {

        $modelCompanyMember = Company::findOne(['id' => $id]);

        $searchModel = Car::find()->leftJoin('company_driver', '`company_driver`.`car_id` = `car`.`id`')->where(['`car`.`company_id`' => $id])->andWhere('`company_driver`.`id` IS NULL');

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'type_id'    => SORT_ASC,
                'number'    => SORT_ASC,
            ]
        ];

        // Массив Типов ТС и Марок ТС
        $arrTypes = Type::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column();
        $arrMarks = Mark::find()->select(['name', 'id'])->orderBy('id ASC')->indexBy('id')->column();

        return $this->render('driver', [
            'model' => $modelCompanyMember,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'arrTypes' => $arrTypes,
            'arrMarks' => $arrMarks,
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

        // Удаляем тендеры
        if($model->status == Company::STATUS_TENDER) {
            Tender::deleteAll(['company_id' => $model->id]);
        }
        // Удаляем из тендер хистори
        TenderHystory::deleteAll(['company_id' => $model->id]);
        // Удаляем тендеры

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

    // Получаем список подтипов
    public static function getSubTypes($id) {
        return CompanySubType::find()->where(['company_id' => $id])->indexBy('sub_type')->select('sub_type')->column();
    }

    // Сохраняем подтипы
    public function actionSubtype($id, $type)
    {

        if(($id > 0) && ($type == 3)) {
            CompanySubType::deleteAll(['company_id' => $id]);

            if(Yii::$app->request->post('sub_type')) {
                foreach (Yii::$app->request->post('sub_type') as $key => $value) {
                    $modelSub = new CompanySubType();
                    $modelSub->company_id = $id;
                    $modelSub->sub_type = $key;
                    $modelSub->save();
                }
            }

        }

        return $this->redirect(['/company/update', 'id' => $id]);
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

        $arrServiceAll = ['2' => '2', '3' => '3', '4' => '4', '5' => '5', '7' => '7', '8' => '8'];
        $noPurchased = array_diff($arrServiceAll, $PurchasedService);

        if(count($noPurchased) > 0) {
            foreach ($noPurchased as $key => $value) {
                $resArr[1] .= Company::$listType[$value]['ru'] . '<br />';
            }
        }

        return $resArr;
    }

    // Карта компаний
    public function actionMap($status = null, $type = null, $id = null, $car_type = null)
    {
        $Company = [];
        $typePage = 0;
        $selID = 0;

        if(($status) && ($type)) {

        // все компании

            // Фильтр по типу ТС
            if($car_type == null) {
                if ($status == 2) {
                    $Company = Company::find()->where(['type' => $type])->andWhere(['OR', ['status' => 2], ['status' => 10]])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->select('company.name, company_info.city, company_info.street, company_info.house, company_info.lat, company_info.lng, company_info.company_id, company.car_type as type')->asArray()->all();
                } else {
                    $Company = Company::find()->where(['AND', ['type' => $type], ['status' => $status]])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->select('company.name, company_info.city, company_info.street, company_info.house, company_info.lat, company_info.lng, company_info.company_id, company.car_type as type')->asArray()->all();
                }
            } else {
                if ($status == 2) {
                    $Company = Company::find()->where(['AND', ['type' => $type], ['car_type' => $car_type]])->andWhere(['OR', ['status' => 2], ['status' => 10]])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->select('company.name, company_info.city, company_info.street, company_info.house, company_info.lat, company_info.lng, company_info.company_id, company.car_type as type')->asArray()->all();
                } else {
                    $Company = Company::find()->where(['AND', ['type' => $type], ['status' => $status], ['car_type' => $car_type]])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->select('company.name, company_info.city, company_info.street, company_info.house, company_info.lat, company_info.lng, company_info.company_id, company.car_type as type')->asArray()->all();
                }
            }

        } elseif($id) {

            // выбранная компания
            $Company = Company::find()->where(['company.id' => $id])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->select('company.name, company_info.city, company_info.street, company_info.house, company_info.lat, company_info.lng, company_info.company_id, company.car_type as type')->asArray()->all();
            $typePage = 1;
            $selID = $id;

        } else {
            return $this->redirect('/');
        }

        return $this->render('map',
            [
                'status' => $status,
                'type' => $type,
                'Company' => $Company,
                'typePage' => $typePage,
                'selID' => $selID,
            ]);
    }

}