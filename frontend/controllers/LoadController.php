<?php

namespace frontend\controllers;

use common\components\ActExporter;
use common\components\LoadHelper;
use common\models\Act;
use common\models\ActScope;
use common\models\Car;
use common\models\Company;
use common\models\Entry;
use common\models\Lock;
use common\models\search\ActSearch;
use common\models\search\CarSearch;
use common\models\search\EntrySearch;
use common\models\Service;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class LoadController extends Controller
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
                        'actions' => ['list', 'update', 'delete', 'view', 'fix', 'export', 'lock', 'unlock', 'close'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'fix', 'export'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['list', 'update', 'view', 'create', 'sign', 'disinfect', 'create-entry'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER],
                    ],
                ],
            ],
        ];
    }

    public function actionList($type, $company = 0)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $role = Yii::$app->user->identity->role;

        $locked = Lock::CheckLocked($searchModel->period, $type);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'company' => $company,
            'role' => $role,
            'locked' => $locked,
            'period' => $searchModel->period,
            'columns' => LoadHelper::getColumnsByType($type, $role, $locked, $searchModel->period, $company, !empty(Yii::$app->user->identity->company->children)),
            'is_locked' => Lock::CheckLocked($searchModel->period, $searchModel->service_type, true),
        ]);
    }

    public function actionExport($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);

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

    public function actionClose($type, $company, $period)
    {

        $LockedLisk = Lock::CheckLocked($period, $type);

        if(count($LockedLisk) > 0) {

            $CloseAll = false;
            $CloseCompany = false;

            for ($c = 0; $c < count($LockedLisk); $c++) {
                if ($LockedLisk[$c]["company_id"] == 0) {
                    $CloseAll = true;
                }
                if ($LockedLisk[$c]["company_id"] == $company) {
                    $CloseCompany = true;
                }
            }

            if (($CloseAll == false) && ($CloseCompany == false)) {
                (new \yii\db\Query())->createCommand()->insert('{{%lock}}', [
                    'id' => '',
                    'type' => $type,
                    'period' => $period,
                    'company_id' => $company,
                ])->execute();
                return 2;
            } elseif (($CloseAll == true) && ($CloseCompany == true)) {
                (new \yii\db\Query())->createCommand()->delete('{{%lock}}', [
                    'type' => $type,
                    'period' => $period,
                    'company_id' => $company,
                ])->execute();
                return 2;
            } elseif (($CloseAll == true) && ($CloseCompany == false)) {
                (new \yii\db\Query())->createCommand()->insert('{{%lock}}', [
                    'id' => '',
                    'type' => $type,
                    'period' => $period,
                    'company_id' => $company,
                ])->execute();
                return 1;
            } elseif (($CloseAll == false) && ($CloseCompany == true)) {
                (new \yii\db\Query())->createCommand()->delete('{{%lock}}', [
                    'type' => $type,
                    'period' => $period,
                    'company_id' => $company,
                ])->execute();
                return 1;
            }

        } else {
            (new \yii\db\Query())->createCommand()->insert('{{%lock}}', [
                'id' => '',
                'type' => $type,
                'period' => $period,
                'company_id' => $company,
            ])->execute();
            return 2;
        }
    }

    public function actionDisinfect($serviceId = null)
    {
        $dataProvider = null;
        $searchModel = new CarSearch(['scenario' => Car::SCENARIO_INFECTED]);
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);
        
        if ($serviceId) {
            $searchModel->is_infected = 1;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            foreach ($dataProvider->getModels() as $car) {
                $existed = Act::find()->where([
                    'number' => $car->number,
                    'service_type' => Service::TYPE_DISINFECT,
                    'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $searchModel->period,
                ])->all();
                if (count($existed)) {
                    continue;
                }
                $model = new Act();
                $model->time_str = '01-' . $searchModel->period;
                $model->partner_id = Yii::$app->user->identity->company_id;
                $model->disinfectCar($car, $serviceId);
            }
        }

        return $this->render('disinfect', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'serviceList' => Service::find()->where(['type' => Service::TYPE_DISINFECT])->select(['description', 'id'])->indexBy('id')->column(),
            'companyList' => Company::find()->byType(Company::TYPE_OWNER)->select(['name', 'id'])->indexBy('id')->active()->column(),
            'role' => Yii::$app->user->identity->role,
        ]);
    }

    public function actionFix($type, $company = false)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;

        foreach ($searchModel->search(Yii::$app->request->queryParams)->getModels() as $act) {
            $act->byAdmin = Yii::$app->user->identity->role == User::ROLE_ADMIN;
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

        $serviceList = Service::find()->where(['type' => $type])
            ->orderBy('description')->select(['description', 'id'])
            ->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post())) {
            $entryId = Yii::$app->request->post('entry_id', false);
            if ($entryId) {
                $modelEntry = Entry::findOne($entryId);
                $model->attributes = $modelEntry->attributes;
                $model->partner_id = $modelEntry->company_id;
                $model->served_at = $modelEntry->end_at;

                if ($model->save()) {
                    $modelEntry->act_id = $model->id;
                    if ($modelEntry->save()) {
                        return $this->redirect(['act/create-entry', 'type' => $type]);
                    }
                }
                return $this->redirect(Yii::$app->request->referrer);
            } else {
                $model->image = UploadedFile::getInstance($model, 'image');
                if ($model->save()) {
                    if (Yii::$app->user->identity->company->is_sign) {
                        return $this->redirect(['act/sign', 'id' => $model->id]);
                    }
                    return $this->redirect(Yii::$app->request->referrer);
                }
            }
        }

        if (!empty(Yii::$app->user->identity->company->schedule)) {
            return $this->redirect(['act/create-entry', 'type' => $type]);
        }

        $searchModel = new ActSearch();
        $searchModel->partner_id = Yii::$app->user->identity->company_id;
        $searchModel->service_type = $type;
        $searchModel->createDay = date('Y-m-d');

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $role = Yii::$app->user->identity->role;

        return $this->render('create', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'serviceList' => $serviceList,
            'role' => $role,
            'model' => $model,
            'columns' => LoadHelper::getColumnsByType($type, $role, 0, !empty(Yii::$app->user->identity->company->children)),
        ]);
    }

    /**
     * Creates Entry model.
     * @param integer $type
     * @param string $day
     * @return mixed
     */
    public function actionCreateEntry($type, $day = null)
    {
        $model = new Entry();
        $model->service_type = $type;
        $model->company_id = Yii::$app->user->identity->company_id;
        if (!$day) {
            $model->day = date('d-m-Y');
        } else {
            $model->day = $day;
        }

        $serviceList = Service::find()->where(['type' => $type])->select(['description', 'id'])->indexBy('id')->column();

        if ($model->load(Yii::$app->request->post())) {
            $modelAct = new Act();
            $modelAct->load(Yii::$app->request->post());
            $modelAct->attributes = $model->attributes;
            $modelAct->partner_id = $model->company_id;
            $modelAct->served_at = \DateTime::createFromFormat('d-m-Y H:i:s', $model->day . ' ' . $model->start_str . ':00')->getTimestamp();

            if (!empty($modelAct->serviceList) && $modelAct->save()) {
                $model->act_id = $modelAct->id;
            }

            if ($model->save()) {
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        $searchModel = new ActSearch();
        $searchModel->partner_id = Yii::$app->user->identity->company_id;
        $searchModel->service_type = $type;
        $searchModel->createDay = date('Y-m-d');

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $entrySearchModel = new EntrySearch();
        $entrySearchModel->load(Yii::$app->request->queryParams);
        $entrySearchModel->company_id = $model->company_id;
        $entrySearchModel->day = $model->day;
        $role = Yii::$app->user->identity->role;

        return $this->render('create-entry', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'entrySearchModel' => $entrySearchModel,
            'type' => $type,
            'serviceList' => $serviceList,
            'role' => $role,
            'model' => $model,
            'columns' => LoadHelper::getColumnsByType($type, $role, 0),
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

        if ($model->load(Yii::$app->request->post())) {
            if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
                ActScope::deleteAll(['act_id' => $model->id]);
                $model->delete();
            }

            $model->image = UploadedFile::getInstance($model, 'image');
            if ($model->save()) {
                return $this->redirect(Yii::$app->request->post('__returnUrl'));
            }
        }

        $clientScopes = $model->getClientScopes()->all();
        $partnerScopes = $model->getPartnerScopes()->all();

        $serviceList = Service::find()->where(['type' => $model->service_type])->select(['description', 'id'])->indexBy('id')->column();
        return $this->render('update', [
            'model' => $model,
            'serviceList' => $serviceList,
            'clientScopes' => $clientScopes,
            'partnerScopes' => $partnerScopes,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
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
            $dir = 'files/checks/';
            imagepng($image, $dir . $id . '-name.png');
            return Json::encode(['file' => $id]);
        }

        if (isset($_POST['sign'])) {
            $data = explode('base64,', $_POST['sign']);

            $str = base64_decode($data[1]);
            $image = imagecreatefromstring($str);

            imagealphablending($image, false);
            imagesavealpha($image, true);
            $dir = 'files/checks/';
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
