<?php

namespace frontend\controllers;

use common\components\ActExporter;
use common\models\Act;
use common\models\search\ActSearch;
use common\models\Service;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class ActController extends Controller
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
                        'actions' => ['list', 'update', 'delete', 'view', 'fix', 'export'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'fix', 'export'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['list', 'view', 'create', 'sign'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER],
                    ],
                ],
            ],
        ];
    }

    public function actionList($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'company' => $company,
            'role' => Yii::$app->user->identity->role,
        ]);
    }

    public function actionExport($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $exporter = new ActExporter();
        $exporter->exportCSV($searchModel, $company);

        return $this->render('export', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'company' => $company,
            'role' => Yii::$app->user->identity->role,
        ]);
    }

    public function actionFix($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;

        foreach ($searchModel->search(Yii::$app->request->queryParams)->getModels() as $act) {
            $act->save();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Creates Act model.
     * @param integer $type
     * @return mixed
     */
    public function actionCreate($type)
    {
        $model = new Act();
        $model->service_type = $type;
        $model->partner_id = Yii::$app->user->identity->company_id;

        $serviceList = Service::find()->where(['type' => $type])->select(['description', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post())) {
            $model->image = UploadedFile::getInstance($model, 'image');
            if ($model->save()) {
                if (Yii::$app->user->identity->company->is_sign) {
                    return $this->redirect(['act/sign', 'id' => $model->id]);
                }
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        $searchModel = new ActSearch();
        $searchModel->partner_id = Yii::$app->user->identity->company_id;
        $searchModel->service_type = $type;
        $searchModel->createDay = date('Y-m-d');

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);


        return $this->render('create', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'serviceList' => $serviceList,
            'role' => Yii::$app->user->identity->role,
            'model' => $model,
        ]);
    }

    /**
     * Updates Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->time_str = date('d-m-Y', $model->served_at);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->post('__returnUrl'));
        } else {
            $clientScopes = $model->getClientScopes()->all();
            $partnerScopes = $model->getPartnerScopes()->all();

            $serviceList = Service::find()->where(['type' => $model->service_type])->select(['description', 'id'])->indexBy('id')->column();
            return $this->render('update', [
                'model' => $model,
                'serviceList' => $serviceList,
                'clientScopes' => $clientScopes,
                'partnerScopes' => $partnerScopes,
            ]);
        }
    }

    /**
     * Shows Act model.
     * @param integer $id
     * @param bool $company
     * @return mixed
     */
    public function actionView($id, $company = false)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'company' => $company,
        ]);
    }

    /**
     * Signs Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionSign($id)
    {
        $model = $this->findModel($id);

        if (isset($_POST['name'])) {
            $data = explode('base64,', $_POST['name']);

            $str = base64_decode($data[1]);
            $image = imagecreatefromstring($str);

            imagealphablending($image, false);
            imagesavealpha($image, true);
            $dir = 'files/signs/';
            imagepng($image, $dir . $id . '-name.png');
            return Json::encode(['file' => $id]);
        }

        if (isset($_POST['sign'])) {
            $data = explode('base64,', $_POST['sign']);

            $str = base64_decode($data[1]);
            $image = imagecreatefromstring($str);

            imagealphablending($image, false);
            imagesavealpha($image, true);
            $dir = 'files/signs/';
            imagepng($image, $dir . $id . '-sign.png');
            return Json::encode(['file' => $id]);
        }

        return $this->render('sign', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Act model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Act model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Act the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Act::findOne($id)) !== null) {
            if (
                Yii::$app->user->can(User::ROLE_ADMIN) ||
                Yii::$app->user->can(User::ROLE_WATCHER) ||
                Yii::$app->user->identity->company_id == $model->partner_id ||
                Yii::$app->user->identity->company_id == $model->client_id ||
                Yii::$app->user->identity->company_id == $model->client->parent_id
            ) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
