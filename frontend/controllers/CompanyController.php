<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace frontend\controllers;


use common\models\Company;
use common\models\CompanyMember;
use common\models\CompanyDuration;
use common\models\CompanyService;
use common\models\CompanySubType;
use common\models\PartnerExclude;
use common\models\search\CompanySearch;
use common\models\Service;
use common\models\Type;
use common\models\User;
use yii\base\DynamicModel;
use yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

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
                        'actions' => ['list', 'create', 'update', 'delete', 'add-price','update-partner-exclude','add-duration','view','editprice','deletelogo'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view','editprice','deletelogo'],
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

        // Подкатегории для сервиса
        $requestSupType = 0;
        if($type == 3) {

            if(Yii::$app->request->get('sub')) {
                $requestSupType = Yii::$app->request->get('sub');
            }

            if($requestSupType > 0) {
                $dataProvider->query->innerJoin('company_sub_type', 'company_sub_type.company_id = company.id AND company_sub_type.sub_type = ' . $requestSupType);
            }

        }
        // Подкатегории для сервиса

        $dataProvider->sort->defaultOrder=['parent_key' => SORT_ASC];

        $model = new Company();
        $model->type = $type;

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'sub_type' => $requestSupType,
            'model' => $model,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    /**
     * Creates Company model.
     * @param integer $type
     * @return mixed
     */
    public function actionCreate($type, $sub = 0)
    {
        $model = new Company();
        $model->type = $type;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if($sub == 0) {
                return $this->redirect(['company/list', 'type' => $type]);
            } else {

                // Подкатегории для сервиса
                $modelSub = new CompanySubType();
                $modelSub->company_id = $model->id;
                $modelSub->sub_type = $sub;
                $modelSub->save();
                // Подкатегории для сервиса

                return $this->redirect(['company/list', 'type' => $type, 'sub' => $sub]);
            }

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

        // Загрузка логотипа компаний
        $modelAddAttach = new DynamicModel(['logo']);
        $modelAddAttach->addRule(['logo'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 1, 'extensions' => 'jpg, jpeg', 'maxSize' => 1536000, 'tooBig' => 'Максимальный размер файла 1.5MB']);

        $fileLogo = UploadedFile::getInstances($modelAddAttach, 'logo');

        if(count($fileLogo) > 0) {

            if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
                mkdir(\Yii::getAlias('@webroot/files/'), 0775);
            }

            if (!file_exists(\Yii::getAlias('@webroot/files/logos/'))) {
                mkdir(\Yii::getAlias('@webroot/files/logos/'), 0775);
            }

            $filePath = \Yii::getAlias('@webroot/files/logos/' . $id . '.jpg');

            if (file_exists($filePath)) {
                // Удаляем старый файл
                unlink($filePath);
            }

            $fileLogo[0]->saveAs($filePath);

            chmod($filePath, 0775);

        }

        // Загрузка логотипа компаний

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $this->findModel($id),
                'expanded' => Yii::$app->request->get('expanded', false),
                ''
            ]);
        }
    }

    public function actionEditprice($service_id)
    {
        $editableIndex = Yii::$app->request->post('editableIndex');
        $newPrice = Yii::$app->request->post('CompanyService');

        if(!is_numeric($newPrice[$editableIndex]['price']) > 0) {

            foreach ($newPrice[$editableIndex]['price'] as $key => $value) {
                $newPrice = $value;
            }

        } else {
            $newPrice = $newPrice[$editableIndex]['price'];
        }

        if(($service_id > 0) && ($newPrice >= 0)) {

            $companyService = CompanyService::findOne($service_id);
            $companyService->price = $newPrice;

            if ($companyService->save()) {
                return 1;
            } else {
                return json_encode(['message' => 'Ошибка системы']);
            }
        } else if($service_id == 0) {
            return json_encode(['message' => 'Услугу нужно добавить.']);
        } else {
            return json_encode(['message' => 'Ошибка системы']);
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
        Yii::$app->session->setFlash('saved', true);

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
        PartnerExclude::deleteAll('client_id=:client_id', [':client_id' => $id]);

        if (isset($partnerId)) {
            //Прообегаем все типы, ищем и инвертируем исключаемые компании по всем типам
            foreach (Service::$listType as $type_id => $type) {
                $partner = yii\helpers\ArrayHelper::getValue($partnerId, $type_id, []);
                if ($partner) {
                    foreach ($partner as $key => $value) {
                        $partnerExclude = new PartnerExclude();
                        $partnerExclude->client_id = $id;
                        $partnerExclude->partner_id = $value;
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

    public static function getCompanyParents($id) {

        $arrParentId = Company::find()->where(['id' => $id])->select('parent_id')->column();

        if(isset($arrParentId[0])) {
            $arrParentId = $arrParentId[0];

            $arrCompany = Company::find()->where(['OR', ['parent_id' => $id], ['parent_id' => $arrParentId]])->select('name')->indexBy('id')->orderBy('id ASC')->column();

            if(isset($arrCompany)) {

                if(count($arrCompany) > 0) {
                    return $arrCompany;
                }

            }

        }

        return [];

    }

    public static function getCompanyMembers($id)
    {

        $memberCont = CompanyMember::find()->where(['company_id' => $id])->andWhere(['!=', 'email', ''])->select('name, email')->orderBy('id ASC')->all();

        $resArr = [];

        if (isset($memberCont)) {

            if (count($memberCont) > 0) {

                for($i = 0; $i < count($memberCont); $i++) {
                    if(isset($memberCont[$i]['email'])) {
                        if($memberCont[$i]['email'] != '') {
                            $email = strtolower(trim($memberCont[$i]['email']));
                            $resArr[$email] = $memberCont[$i]['name'] . ' (' . $email . ')';

                            $email = '';
                        }
                    }
                }

                return $resArr;
            }

        }

        return [];

    }

    // Удаление логотипа компании
    public function actionDeletelogo()
    {

        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $filePath = \Yii::getAlias('@webroot/files/logos/' . $id . '.jpg');

            if(file_exists($filePath)) {
                unlink($filePath);
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }


    }

}