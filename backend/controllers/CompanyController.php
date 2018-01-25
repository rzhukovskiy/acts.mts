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
use common\models\Company;
use common\models\CompanyDriver;
use common\models\CompanyInfo;
use common\models\CompanyMember;
use common\models\CompanyOffer;
use common\models\CompanyService;
use common\models\CompanyState;
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

                        'actions' => ['add-price', 'ajaxpaymentstatus', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'tender', 'tenders', 'newtender', 'fulltender', 'filtertender' ,'tenderlist', 'updatetender', 'new', 'create', 'update', 'updatemember', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'delete', 'attribute', 'offer', 'undriver', 'subtype', 'closedownload', 'listitems', 'newitemlist', 'deleteitemlist', 'edititemlist', 'newtendattach', 'tendersexcel', 'exceltenders', 'controltender', 'newcontroltender', 'fullcontroltender', 'updatecontroltender', 'controlisarchive', 'archivetender', 'tendermembers', 'newtendermembers', 'fulltendermembers', 'updatetendermembers', 'newtenderlinks', 'map', 'membersontender', 'tendermemberwin', 'tenderownerlist', 'tenderowneradd', 'tenderownerupdate', 'tenderownerfull', 'pickup', 'ownerdelete', 'getcomments'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['add-price', 'ajaxpaymentstatus', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'tender', 'tenders', 'newtender', 'fulltender', 'filtertender', 'tenderlist', 'updatetender', 'new', 'create', 'update', 'updatemember', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer', 'undriver', 'subtype', 'closedownload', 'listitems', 'newitemlist', 'deleteitemlist', 'edititemlist', 'newtendattach', 'tendersexcel', 'exceltenders', 'controltender', 'newcontroltender', 'fullcontroltender', 'updatecontroltender', 'controlisarchive', 'archivetender', 'tendermembers', 'newtendermembers', 'fulltendermembers', 'updatetendermembers', 'newtenderlinks', 'map', 'membersontender', 'tendermemberwin', 'tenderownerlist', 'tenderowneradd', 'tenderownerupdate', 'tenderownerfull', 'pickup', 'getcomments'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['add-price', 'ajaxpaymentstatus', 'price', 'status', 'active', 'archive', 'refuse', 'archive3', 'tender', 'tenders', 'newtender', 'fulltender', 'filtertender', 'tenderlist', 'updatetender', 'new', 'create', 'update', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer', 'undriver', 'subtype', 'closedownload', 'listitems', 'newitemlist', 'deleteitemlist', 'edititemlist', 'newtendattach', 'tendersexcel', 'exceltenders', 'controltender', 'newcontroltender', 'fullcontroltender', 'updatecontroltender', 'controlisarchive', 'archivetender', 'tendermembers', 'newtendermembers', 'fulltendermembers', 'updatetendermembers', 'newtenderlinks', 'map', 'membersontender', 'tendermemberwin', 'tenderownerlist', 'tenderowneradd', 'tenderownerupdate', 'tenderownerfull', 'pickup', 'getcomments'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                    [
                        'actions' => ['archive', 'refuse', 'archive3', 'new', 'create', 'update', 'info', 'state', 'newstate', 'attaches', 'newattach', 'getcomment', 'getcall', 'member', 'driver', 'offer', 'undriver', 'subtype', 'map', 'attribute', 'price'],
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
        $plainTextContent = '';

        $user_id = Yii::$app->user->identity->id;
        $modelUser = User::findOne(['id' => $user_id]);
        $companyModel = Company::findOne(['id' => $model->id]);

        $plainTextContent = 'Сотрудник <b>' . $modelUser->username . '</b> добавил новые цены на услуги для компании <b>' . $companyModel->name . '</b><br /><br />';

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

                    // Для email Рассылки
                    if ($price) {
                        $modelService = Service::findOne(['id' => $service_id]);
                        $modelType = Type::findOne(['id' => $type_id]);

                        if(isset($existed)) {
                            if(isset($existed->price)) {
                                if($existed->price) {
                                    $plainTextContent .= $modelService->description . ', тип: ' . $modelType->name . ', старая цена: ' . $existed->price . ' руб, новая цена: ' . $price . ' руб.<br />';
                                } else {
                                    $plainTextContent .= $modelService->description . ', тип: ' . $modelType->name . ', цена: ' . $price . ' руб.<br />';
                                }
                            } else {
                                $plainTextContent .= $modelService->description . ', тип: ' . $modelType->name . ', цена: ' . $price . ' руб.<br />';
                            }
                        } else {
                            $plainTextContent .= $modelService->description . ', тип: ' . $modelType->name . ', цена: ' . $price . ' руб.<br />';
                        }
                    }

                }
            }

            // Уведомление Герберта
            $toEmail = "mtransservice@mail.ru";

            $mailCont = Yii::$app->mailer->compose()
                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                ->setTo($toEmail)
                ->setSubject('Добавлена новая цена на услугу для ' . $companyModel->name)
                ->setHtmlBody($plainTextContent)->send();
            // Уведомление Герберта

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
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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

    // Раздел тендеры
    public function actionTender($type)
    {
        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_TENDER;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $searchModel->user_id = Yii::$app->user->identity->id;
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_TENDER);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Подкатегории для сервиса
        if($type == 3) {
            $requestSupType = 0;

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
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
                'searchModel'  => $searchModel,
                'type'         => $type,
                'model'        => $model,
                'listType'     => $listType,
            ]);
    }

    // Все тендеры
    public function actionTenderlist()
    {

        $currentUser = Yii::$app->user->identity;

        $searchModel = new TenderSearch();

        if ($currentUser->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = $currentUser->getAllCompanyType(Company::STATUS_TENDER);
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['OR', ['purchase_status' => 15], ['purchase_status' => 18], ['purchase_status' => 19], ['purchase_status' => 57], ['purchase_status' => 58], ['purchase_status' => 85]]);

        return $this->render('tender/tenderlist',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'listType'     => $listType,
            ]);
    }

    // Список договоров по дате окончания
    public function actionFiltertender()
    {

        $currentUser = Yii::$app->user->identity;

        $searchModel = new TenderSearch();

        if ($currentUser->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = $currentUser->getAllCompanyType(Company::STATUS_TENDER);
        }

        $searchModel->purchase_status = 22;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->sort = [
            'defaultOrder' => [
                'term_contract'    => SORT_ASC,
            ]
        ];

        return $this->render('tender/filtertender',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
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
                'date_request_end'    => SORT_ASC,
            ]
        ];

        return $this->render('tenders', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'model' => $model,
        ]);

    }

    // Новый тендер
    public function actionNewtender($id)
    {
        $model = new Tender();
        $model->company_id = $id;

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
            ]);
        }
    }

    public function actionFulltender($tender_id)
    {

        $model = Tender::findOne(['id' => $tender_id]);

        return $this->render('tender/fulltender', [
            'model' => $model,
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

        if($winner == 1) {

            // Проверка
            $model = TenderLinks::findOne(['tender_id' => $tender_id, 'member_id' => $member_id]);

            if($model->winner == 0) {

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
        $searchModel = new TenderSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if($win) {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 21], ['purchase_status' => 22]]);
        } else {
            $dataProvider->query->andWhere(['OR', ['purchase_status' => 16], ['purchase_status' => 17], ['purchase_status' => 20], ['purchase_status' => 23]]);
        }

        $dataProvider->sort = [
            'defaultOrder' => [
                'term_contract'    => SORT_ASC,
            ]
        ];

        return $this->render('tender/archivetender',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'win' => $win,
            ]);
    }

    public function actionControltender()
    {

        $searchModel = new TenderControlSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        return $this->render('tender/controltender', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
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
        $model = new TenderControl();

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            return $this->redirect(['company/controltender']);

        } else {
            return $this->render('form/newcontroltender', [
                'model' => $model,
                'usersList' => $usersList,
            ]);
        }
    }
    public function actionFullcontroltender($id)
    {
        $model = TenderControl::findOne(['id' => $id]);

        $usersList = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id` AND `department_user`.`department_id` = 6')->select('user.username')->indexby('user_id')->column();

        return $this->render('tender/fullcontroltender', [
            'model' => $model,
            'usersList' => $usersList,
        ]);

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
            $arrSiteAddress = isset($arrLists[8]) ? $arrLists[8] : [];
            $arrTypePayment = isset($arrLists[9]) ? $arrLists[9] : [];

            foreach ($arrUpdate['TenderControl'] as $name => $value) {
                if($name == 'date_send') {
                    $arrUpdate['TenderControl'][$name] = (String) strtotime($value);
                } else if($name == 'date_enlistment') {
                    $arrUpdate['TenderControl'][$name] = (String) strtotime($value);
                } else if($name == 'money_unblocking') {
                    $arrUpdate['TenderControl'][$name] = (String) strtotime($value);
                } else if($name == 'date_return') {
                    $arrUpdate['TenderControl'][$name] = (String) strtotime($value);
                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                foreach (Yii::$app->request->post('TenderControl') as $name => $value) {

                    if ($name == 'site_address') {
                        $output[] = $arrSiteAddress[$value];
                    } else if ($name == 'type_payment') {
                        $output[] = $arrTypePayment[$value];
                    } else if ($name == 'user_id') {
                        $output[] = $usersList[$value];
                    } else if ($name == 'send' || $name == 'return' || $name == 'balance_work') {
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

    // Закрыть изменения controltender
    public function actionControlisarchive()
    {

        if(Yii::$app->request->post('control_id')) {

            $id = Yii::$app->request->post('control_id');
            $is_archive = Yii::$app->request->post('is_archive');

            $model = TenderControl::findOne(['id' => $id]);

            if($is_archive == 1) {
                $model->is_archive = 0;
            } else {
                $model->is_archive = 1;
            }

            if($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }
    // Скачиваем файл Excel для заполнения
    public function actionTendersexcel()
    {
            $resExcel = self::createExcelTenders();

            $pathFile = Yii::getAlias('@webroot/files/tenders/filtertender.xls');

            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: ".filesize($pathFile));
            header("Content-Disposition: attachment; filename=filtertender.xls");
            readfile($pathFile);

    }

    // Формирование Excel файла
    public static function createExcelTenders() {

        $arrTenders = Tender::find()->where(['purchase_status' => 22])->select('inn_customer, customer, city, service_type, number_purchase, place, cost_purchase_completion, date_contract, term_contract')->orderby('term_contract ASC')->asArray()->all();

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
        if(count($arrTenders) > 0) {

            $companyWorkSheet->getColumnDimension('A')->setWidth(20);
            $companyWorkSheet->getColumnDimension('B')->setWidth(20);
            $companyWorkSheet->getColumnDimension('C')->setWidth(30);
            $companyWorkSheet->getColumnDimension('D')->setWidth(25);
            $companyWorkSheet->getColumnDimension('E')->setWidth(32);
            $companyWorkSheet->getColumnDimension('F')->setWidth(28);
            $companyWorkSheet->getColumnDimension('G')->setWidth(40);
            $companyWorkSheet->getColumnDimension('H')->setWidth(35);
            $companyWorkSheet->getColumnDimension('I')->setWidth(40);
            $companyWorkSheet->getColumnDimension('J')->setWidth(40);

            // Заголовки
            $companyWorkSheet->setCellValue('A' . $row, 'Заказчик');
            $companyWorkSheet->setCellValue('B' . $row, 'ИНН Заказчика');
            $companyWorkSheet->setCellValue('C' . $row, 'Город, Область поставки');
            $companyWorkSheet->setCellValue('D' . $row, 'Закупаемые услуги');
            $companyWorkSheet->setCellValue('E' . $row, 'Номер закупки на площадке');
            $companyWorkSheet->setCellValue('F' . $row, 'Электронная площадка');
            $companyWorkSheet->setCellValue('G' . $row, 'Стоимость закупки по завершению закупки без НДС');
            $companyWorkSheet->setCellValue('H' . $row, 'Дата заключения договора');
            $companyWorkSheet->setCellValue('I' . $row, 'Дата окончания заключенного договора');
            $companyWorkSheet->setCellValue('J' . $row, 'Осталось дней до окончания договора');

            $companyWorkSheet->getStyle('A' . $row . ':J' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            ));

            $companyWorkSheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name'  => 'Times New Roman'
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

                    if($arrayTenderList[$j]['type'] == $oldType) {

                        $index = $arrayTenderList[$j]['id'];
                        $tmpArray[$index] = $arrayTenderList[$j]['description'];

                    } else {

                        if($j > 0) {

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

                    if(($j + 1) == count($arrayTenderList)) {
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

                                if($z == (count($serviseVal) - 1)) {
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

                if(isset($arrTenders[$i]['term_contract'])) {
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
                    $companyWorkSheet->setCellValue('G' . $row, isset($arrTenders[$i]['cost_purchase_completion']) ? ($arrTenders[$i]['cost_purchase_completion'] . ' р.') : '-');
                    $companyWorkSheet->setCellValue('H' . $row, isset($arrTenders[$i]['date_contract']) ? (mb_strlen($arrTenders[$i]['date_contract']) > 3 ? date('d.m.Y', $arrTenders[$i]['date_contract']) : '-') : '-');
                    $companyWorkSheet->setCellValue('I' . $row, isset($arrTenders[$i]['term_contract']) ? (mb_strlen($arrTenders[$i]['term_contract']) > 3 ? date('d.m.Y', $arrTenders[$i]['term_contract']) : '-') : '-');
                    $companyWorkSheet->setCellValue('J' . $row, $showTotal);

                    $companyWorkSheet->getStyle('A' . $row . ':J' . $row)->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    ));

                    $companyWorkSheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                            'font' => [
                                'size' => 12,
                                'name'  => 'Times New Roman'
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

        if(Yii::$app->request->post('tender_id')) {

            $id = Yii::$app->request->post('tender_id');
            $tender_close = Yii::$app->request->post('tender_close');

            $model = Tender::findOne(['id' => $id]);

            if($tender_close == 1) {
                $model->tender_close = 0;
            } else {
                $model->tender_close = 1;
            }

           if($model->save()) {
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
                    if ($name == 'data') {
                        $arrUpdate['TenderOwner'][$name] = (String)strtotime($value);
                    }
                }

                if ($model->load($arrUpdate) && $model->save()) {
                    $output = [];

                    foreach (Yii::$app->request->post('TenderOwner') as $name => $value) {

                        if ($name == 'tender_user') {
                            $output[] = $usersList[$value];
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

        if ($win == 1) {
            $dataProvider->query->andWhere(['AND', ['tender_user' => 0], ['is', 'reason_not_take', null]])->orWhere(['AND', ['tender_user' => 0], ['reason_not_take' => '']]);
        } else if ($win == 2) {
            $dataProvider->query->andWhere(['AND', ['!=', 'tender_user', 0], ['!=', 'tender_id', ''], ['NOT', ['tender_id' => null]]])->orderBy('tender_user');
        } else if ($win == 3) {
            $dataProvider->query->andWhere(['!=', 'reason_not_take', ''])->orWhere(['!=', 'reason_not_take', null]);
        } else {
            $dataProvider->query->andWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['is', 'reason_not_take', null]])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['is', 'tender_id', null], ['reason_not_take' => '']])->orWhere(['AND', ['!=', 'tender_user', 0], ['tender_id' => ''], ['is', 'reason_not_take', null]])->orderBy('tender_user');
        }


        return $this->render('tender/tenderownerlist', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'win' => $win,
            'model' => $model,
        ]);

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
            $stringServicesText = "";
            $stringUserTendText = "";
            $stringMethodsTendText = "";
            $stringFZText = "";
            $stringKeyTypeText = "";
            $stringStatusRequestText = "";
            $stringStatusContractText = "";
            $ServicesList = isset($arrLists[3]) ? $arrLists[3] : [];
            $arrFZlist = isset($arrLists[4]) ? $arrLists[4] : [];
            $usersList = isset($arrLists[1]) ? $arrLists[1] : [];
            $arrPurchstatus = isset($arrLists[0]) ? $arrLists[0] : [];
            $arrMethods = isset($arrLists[2]) ? $arrLists[2] : [];
            $arrStatusRequestlist = isset($arrLists[6]) ? $arrLists[6] : [];
            $arrStatusContractlist = isset($arrLists[7]) ? $arrLists[7] : [];
            $arrKeyTypelist = isset($arrLists[5]) ? $arrLists[5] : [];

            foreach ($arrUpdate['Tender'] as $name => $value) {
                if($name == 'date_search') {
                    $arrUpdate['Tender'][$name] = (String) strtotime($value);
                } else if($name == 'service_type') {

                    // запись в базу нескольких услуг
                    if (is_array($value)) {

                        $arrServices = $value;

                        if (count($arrServices) > 0) {
                            $stringServices = '';

                            for ($i = 0; $i < count($arrServices); $i++) {
                                if ($i == 0) {
                                    $stringServices .= $arrServices[$i];
                                } else {
                                    $stringServices .= ', ' . $arrServices[$i];
                                }

                                if(isset($ServicesList[$arrServices[$i]])) {
                                    $stringServicesText .= $ServicesList[$arrServices[$i]] . '<br />';
                                }

                            }

                            $arrUpdate['Tender'][$name] = $stringServices;

                        }

                    }
                } else if($name == 'user_id') {

                    // запись в базу нескольких пользователей
                    if (is_array($value)) {

                        $arrUserTend = $value;

                        if (count($arrUserTend) > 0) {
                            $stringUserTend = '';

                            for ($i = 0; $i < count($arrUserTend); $i++) {
                                if ($i == 0) {
                                    $stringUserTend .= $arrUserTend[$i];
                                } else {
                                    $stringUserTend .= ', ' . $arrUserTend[$i];
                                }

                                if(isset($usersList[$arrUserTend[$i]])) {
                                    $stringUserTendText .= $usersList[$arrUserTend[$i]] . '<br />';
                                }

                            }

                            $arrUpdate['Tender'][$name] = $stringUserTend;

                        }
                    }
                    } else if($name == 'federal_law') {

                // запись в базу нескольких фз
                if (is_array($value)) {

                    $arrFZ = $value;

                    if (count($arrFZ) > 0) {
                        $stringFz = '';

                        for ($i = 0; $i < count($arrFZ); $i++) {
                            if ($i == 0) {
                                $stringFz .= $arrFZ[$i];
                            } else {
                                $stringFz .= ', ' . $arrFZ[$i];
                            }

                            if(isset($arrFZlist[$arrFZ[$i]])) {
                                $stringFZText .= $arrFZlist[$arrFZ[$i]] . '<br />';
                            }

                        }

                        $arrUpdate['Tender'][$name] = $stringFz;

                    }

                }

                } else if($name == 'status_contract_security') {

                    // запись в базу нескольких Статус обеспечения заявки
                    if (is_array($value)) {

                        $arrStatusContract = $value;

                        if (count($arrStatusContract) > 0) {
                            $stringStatusContract = '';

                            for ($i = 0; $i < count($arrStatusContract); $i++) {
                                if ($i == 0) {
                                    $stringStatusContract .= $arrStatusContract[$i];
                                } else {
                                    $stringStatusContract .= ', ' . $arrStatusContract[$i];
                                }

                                if(isset($arrStatusContractlist[$arrStatusContract[$i]])) {
                                    $stringStatusContractText .= $arrStatusContractlist[$arrStatusContract[$i]] . '<br />';
                                }

                            }

                            $arrUpdate['Tender'][$name] = $stringStatusContract;

                        }

                    }

                } else if($name == 'status_request_security') {

                    // запись в базу нескольких Статус обеспечения заявки
                    if (is_array($value)) {

                        $arrStatusRequest = $value;

                        if (count($arrStatusRequest) > 0) {
                            $stringStatusRequest = '';

                            for ($i = 0; $i < count($arrStatusRequest); $i++) {
                                if ($i == 0) {
                                    $stringStatusRequest .= $arrStatusRequest[$i];
                                } else {
                                    $stringStatusRequest .= ', ' . $arrStatusRequest[$i];
                                }

                                if(isset($arrStatusRequestlist[$arrStatusRequest[$i]])) {
                                    $stringStatusRequestText .= $arrStatusRequestlist[$arrStatusRequest[$i]] . '<br />';
                                }

                            }

                            $arrUpdate['Tender'][$name] = $stringStatusRequest;

                        }

                    }

                } else if($name == 'key_type') {

                    // запись в базу нескольких типов ключей
                    if (is_array($value)) {

                        $arrKeyType = $value;

                        if (count($arrKeyType) > 0) {
                            $stringKeyType = '';

                            for ($i = 0; $i < count($arrKeyType); $i++) {
                                if ($i == 0) {
                                    $stringKeyType .= $arrKeyType[$i];
                                } else {
                                    $stringKeyType .= ', ' . $arrKeyType[$i];
                                }

                                if(isset($arrKeyTypelist[$arrKeyType[$i]])) {
                                    $stringKeyTypeText .= $arrKeyTypelist[$arrKeyType[$i]] . '<br />';
                                }

                            }

                            $arrUpdate['Tender'][$name] = $stringKeyType;

                        }

                    }
                } else if($name == 'method_purchase') {

                    // запись в базу нескольких услуг
                    if (is_array($value)) {

                        $arrMethodsTend = $value;

                        if (count($arrMethodsTend) > 0) {
                            $stringMethods = '';

                            for ($i = 0; $i < count($arrMethodsTend); $i++) {
                                if ($i == 0) {
                                    $stringMethods .= $arrMethodsTend[$i];
                                } else {
                                    $stringMethods .= ', ' . $arrMethodsTend[$i];
                                }

                                if(isset($arrMethods[$arrMethodsTend[$i]])) {
                                    $stringMethodsTendText .= $arrMethods[$arrMethodsTend[$i]] . '<br />';
                                }

                            }

                            $arrUpdate['Tender'][$name] = $stringMethods;

                        }

                    }
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
                }
            }

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                foreach (Yii::$app->request->post('Tender') as $name => $value) {

                    if ($name == 'service_type') {
                        $output[] = $stringServicesText;
                    } else if ($name == 'user_id') {
                        $output[] = $stringUserTendText;
                    } else if ($name == 'percent_down') {
                        $output[] = $value . "%";
                    } else if ($name == 'percent_max') {
                        $output[] = $value . "%";
                    } else if ($name == 'federal_law') {
                        $output[] = $stringFZText;
                    } else if ($name == 'purchase_status') {
                        $output[] = $arrPurchstatus[$value];
                    } else if ($name == 'method_purchase') {
                        $output[] = $stringMethodsTendText;
                    } else if ($name == 'status_request_security') {
                        $output[] = $stringStatusRequestText;
                    } else if ($name == 'status_contract_security') {
                        $output[] = $stringStatusContractText;
                    } else if ($name == 'key_type') {
                        $output[] = $stringKeyTypeText;
                    } else if ($name == 'price_nds' || $name == 'pre_income' || $name == 'final_price' || $name == 'contract_security' || $name == 'maximum_purchase_price' || $name == 'cost_purchase_completion' || $name == 'maximum_purchase_nds' || $name == 'maximum_purchase_notnds' || $name == 'maximum_agreed_calcnds' || $name == 'maximum_agreed_calcnotnds' || $name == 'site_fee_participation' || $name == 'ensuring_application' || $name == 'last_sentence_nds' || $name == 'last_sentence_nonds') {
                        $output[] = $value . " ₽";
                    } else if($name == 'work_user_id') {

                        $workUserArr = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->andWhere(['OR', ['department_id' => 1], ['department_id' => 7]])->select('user.id, user.username')->asArray()->all();

                        $workUserData = [];

                        foreach ($workUserArr as $key => $result) {
                            $index = $result['id'];
                            $workUserData[$index] = trim($result['username']);
                        }

                        $output[] = isset($workUserData[$value]) ? $workUserData[$value] : $value;

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

            if (isset($model->reason_not_take)) {
                $resComm = "<u style='color:#757575;'>Комментарий:</u> " . $model->reason_not_take . "<br />";
            } else {
                $resComm = "<u style='color:#757575;'>Комментарий:</u><br />";
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