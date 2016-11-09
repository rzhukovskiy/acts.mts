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
use common\models\search\CompanyDriverSearch;
use common\models\search\CompanyMemberSearch;
use common\models\search\CompanySearch;
use common\models\Service;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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

                        'actions' => ['archive', 'refuse', 'new', 'create', 'update', 'info', 'member', 'driver', 'delete','attribute'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['archive', 'refuse', 'new', 'create', 'update', 'info', 'member', 'driver'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['archive', 'refuse', 'new', 'create', 'update', 'info', 'member', 'driver'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Company models.
     * @param integer $type
     * @return mixed
     */
    public function actionNew($type)
    {
        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_NEW;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_NEW);
        }

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_NEW;
            $typeData['badge'] = $badgeSearch->search()->count;
        }

        $this->view->title = 'Заявки - ' . Company::$listType[$type]['ru'];

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
        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_ARCHIVE;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'address'    => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_ARCHIVE);
        }

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_ARCHIVE;
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
        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_REFUSE;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'address'    => SORT_ASC,
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
        } else {
            $listType = Yii::$app->user->identity->getAllCompanyType(Company::STATUS_REFUSE);
        }

        foreach ($listType as $type_id => &$typeData) {
            $badgeSearch = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
            $badgeSearch->type = $type_id;
            $badgeSearch->status = Company::STATUS_REFUSE;
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
     * @param integer $type
     * @return mixed
     */
    public function actionCreate($type)
    {
        $model = new Company();
        $model->type = $type;
        $model->status = Company::STATUS_NEW;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['company/new', 'type' => $type]);
        } else {
            return $this->goBack();
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return ['message' => ''];
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

        return $this->render('offer',
        [
            'modelCompany' => $model,
            'modelCompanyInfo' => $modelCompanyInfo,
            'modelCompanyOffer' => $modelCompanyOffer,
        ]);
    }

    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        $modelCompanyInfo = $model->info ? $model->info : new CompanyInfo();
        $modelCompanyInfo->company_id = $model->id;

        return $this->render('info',
        [
            'model' => $modelCompanyInfo,
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
        $type = $model->type;
        $model->delete();

        return $this->redirect(['list', 'type' => $type]);
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