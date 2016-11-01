<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace frontend\controllers;


use common\models\Company;
use common\models\CompanyDuration;
use common\models\CompanyService;
use common\models\PartnerExclude;
use common\models\search\CompanySearch;
use common\models\Service;
use common\models\Type;
use common\models\User;
use yii;
use yii\web\Controller;
use yii\filters\AccessControl;
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
                        'actions' => ['list', 'create', 'update', 'delete', 'add-price','update-partner-exclude','add-duration','view'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view'],
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
    public function actionList($type)
    {
        $searchModel = new CompanySearch();
        $searchModel->type = $type;
        $searchModel->status = Company::STATUS_ACTIVE;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ];

        $model = new Company();
        $model->type = $type;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'model' => $model,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['company/list', 'type' => $type]);
        } else {
            return $this->goBack();
        }
    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $this->findModel($id),
                ''
            ]);
        }
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

        return $this->redirect(['update', 'id' => $model->id]);
    }

    /**
     * @param $id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionAddDuration($id)
    {
        $model = $this->findModel($id);

        if ($durationData = Yii::$app->request->post('Duration')) {
            foreach ($durationData['type'] as $type_id) {

                $companyDuration = new CompanyDuration();
                $companyDuration->company_id = $model->id;
                $companyDuration->type_id = $type_id;
                $companyDuration->duration = $durationData['duration'];
                if (!$companyDuration->duration) {
                    $type = Type::findOne($type_id);
                    if ($type) {
                        $companyDuration->duration = $type->time;
                    }
                }
                $companyDuration->save();
            }
        }

        return $this->redirect(['update', 'id' => $model->id]);
    }

    /**
     * @param $id
     * @return yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdatePartnerExclude($id)
    {
        $model = $this->findModel($id);

        $partnerId = Yii::$app->request->post('partner');

        if (isset($partnerId)) {
            PartnerExclude::deleteAll('client_id=:client_id', [':client_id' => $id]);
            //Прообегаем все типы, ищем и инвертируем исключаемые компании по всем типам
            foreach (Service::$listType as $type_id => $type) {
                $partner = yii\helpers\ArrayHelper::getValue($partnerId, $type_id, []);
                $allExcludeId = $model->getInvertIds($type_id, $partner);
                if ($allExcludeId) {
                    foreach ($allExcludeId as $excludeId) {
                        $partnerExclude = new PartnerExclude();
                        $partnerExclude->client_id = $id;
                        $partnerExclude->partner_id = $excludeId;
                        $partnerExclude->save();
                    }
                }
            }

        }

        return $this->redirect(['update', 'id' => $model->id]);
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