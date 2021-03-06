<?php

namespace frontend\controllers;

use common\components\ActExporter;
use common\components\LoadHelper;
use common\models\Act;
use common\models\ActScope;
use common\models\Car;
use common\models\Company;
use common\models\CompanyMember;
use common\models\Entry;
use common\models\Lock;
use common\models\LockInfo;
use common\models\search\ActSearch;
use common\models\search\CarSearch;
use common\models\search\CompanyMemberSearch;
use common\models\search\EntrySearch;
use common\models\Service;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\db\Expression;
use kartik\grid\GridView;

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
                        'actions' => ['list', 'update', 'delete', 'view', 'fix', 'export', 'lock', 'unlock', 'close', 'contact', 'stickers', 'comment', 'getcomments', 'fullcompany'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'fix', 'export', 'unlock', 'close', 'contact', 'stickers', 'comment', 'getcomments', 'fullcompany'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view', 'comment'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['list', 'update', 'view', 'create', 'sign', 'disinfect', 'create-entry', 'comment'],
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

        $locked = Lock::checkLocked($searchModel->period, $type);

        $dataFilter = explode('-', $searchModel->period);

        if($dataFilter[0] > 9) {
            $dataFilter = $dataFilter[1] . '-' . $dataFilter[0] . '-00';
        } else {
            $dataFilter = $dataFilter[1] . '-0' . $dataFilter[0] . '-00';
        }

        if($company == 0) {
            $dataProvider->query->select('SUM(expense) as expense, partner.address, partner_id');
            $dataProvider->query->groupBy('partner_id');
            $dataProvider->query->andWhere('(expense > 0) AND (service_type=' . $type . ') AND (date_format(FROM_UNIXTIME(served_at), \'%Y-%m-00\') = \'' . $dataFilter . '\')');

        } else {
            $dataProvider->query->select('SUM(income) as expense, client.*');
            $dataProvider->query->groupBy('client_id');
            $dataProvider->query->andWhere('(income > 0) AND (service_type=' . $type . ') AND (date_format(FROM_UNIXTIME(served_at), \'%Y-%m-00\') = \'' . $dataFilter . '\')');
        }

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'company' => $company,
            'role' => $role,
            'locked' => $locked,
            'period' => $searchModel->period,
            'columns' => LoadHelper::getColumnsByType($type, $role, $locked, $searchModel->period, $company, !empty(Yii::$app->user->identity->company->children)),
            'is_locked' => Lock::checkLocked($searchModel->period, $searchModel->service_type, true),
        ]);
    }

    public function actionClose($type, $company, $period)
    {

        $lockedLisk = Lock::checkLocked($period, $type);

        if(count($lockedLisk) > 0) {

            $closeAll = false;
            $closeCompany = false;

            for ($c = 0; $c < count($lockedLisk); $c++) {
                if ($lockedLisk[$c]["company_id"] == 0) {
                    $closeAll = true;
                }
                if ($lockedLisk[$c]["company_id"] == $company) {
                    $closeCompany = true;
                }
            }

            if (($closeAll == false) && ($closeCompany == false)) {

                $lock = new Lock();
                $lock->period = $period;
                $lock->type = $type;
                $lock->company_id = $company;

                $lock->save();

                return 2;
            } elseif (($closeAll == true) && ($closeCompany == true)) {

                Lock::deleteAll([
                    'type' => $type,
                    'period' => $period,
                    'company_id' => $company,
                ]);

                return 2;
            } elseif (($closeAll == true) && ($closeCompany == false)) {

                $lock = new Lock();
                $lock->period = $period;
                $lock->type = $type;
                $lock->company_id = $company;

                $lock->save();

                return 1;
            } elseif (($closeAll == false) && ($closeCompany == true)) {

                Lock::deleteAll([
                    'type' => $type,
                    'period' => $period,
                    'company_id' => $company,
                ]);

                return 1;
            }

        } else {

            $lock = new Lock();
            $lock->period = $period;
            $lock->type = $type;
            $lock->company_id = $company;

            $lock->save();

            return 2;
        }
    }

    public function actionComment($id, $type, $period, $company = 0)
    {
        $model = LockInfo::findOne(['partner_id' => $id, 'type' => $type, 'period' => $period]);
        if (isset($model)) {
        } else {
            $model = new LockInfo();
            $model->partner_id = $id;
            $model->type = $type;
            $model->period = $period;
        }
        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {
                return $this->redirect(['load/list', 'ActSearch[period]' => $period, 'id' => $id, 'type' => $type, 'period' => $period, 'company' => $company]);
        }

        return $this->render('comment',
                [
                    'model' => $model,
                    'id' => $id,
                    'type' => $type,
                    'period' => $period,
                    'company' => $company,
                ]);

    }

    public function actionGetcomments()
    {

        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');
            $period = Yii::$app->request->post('period');
            $type = Yii::$app->request->post('type');
            $resComm = '';

            $model = LockInfo::findOne(['partner_id' => $id, 'type' => $type, 'period' => $period]);

            if (isset($model)) {
                $resComm = "<u style='color:#757575;'>??????????????????????:</u> " . $model->comment . "<br />";
            } else {
                $resComm = "<u style='color:#757575;'>??????????????????????:</u><br />";
            }

            echo json_encode(['success' => 'true', 'comment' => $resComm]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionFullcompany()
    {

        if (Yii::$app->request->post('type') && Yii::$app->request->post('iscompany') && Yii::$app->request->post('period')) {

            $type = Yii::$app->request->post('type');
            $isCompany = Yii::$app->request->post('iscompany');
            $period = Yii::$app->request->post('period');

            $periodAct = explode('-', $period);
            $periodAct = date('Y-m-00', strtotime($periodAct[1] . '-' . $periodAct[0]));

            if ($isCompany == 1) {
                $ressArray = Company::find()->innerJoin('lock', 'lock.company_id = company.id')->leftJoin('act', 'act.partner_id = company.id AND (date_format(FROM_UNIXTIME(act.served_at), "%Y-%m-00") = "' . $periodAct . '")')->andWhere(['is', 'act.partner_id', null])->andWhere(['lock.period' => $period])->andWhere(['company.type' => $type])->andWhere(['OR', ['company.status' => Company::STATUS_ARCHIVE], ['company.status' => Company::STATUS_ACTIVE]])->select('company.id, company.name, company.address')->asArray()->all();
                return json_encode(['result' => json_encode($ressArray), 'success' => 'true']);
            } else {
                return json_encode(['success' => 'false']);
            }

        } else {
            return json_encode(['success' => 'false']);
        }

    }

    public function actionContact($id)
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

    public function actionStickers($type, $company = 0)
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        $searchModel->service_type = $type;
        $searchModel->period = date('n-Y', time() - 10 * 24 * 3600);

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $role = Yii::$app->user->identity->role;

        $locked = Lock::checkLocked($searchModel->period, $type);

        $dataFilter = explode('-', $searchModel->period);

        if($dataFilter[0] > 10) {
            $dataFilter = $dataFilter[1] . '-' . $dataFilter[0] . '-00';
        } else {
            $dataFilter = $dataFilter[1] . '-0' . $dataFilter[0] . '-00';
        }

        if($company == 0) {
            $dataProvider->query->select('SUM(expense) as expense, partner.address, partner_id');
            $dataProvider->query->groupBy('partner_id');
            $dataProvider->query->andWhere('(expense > 0) AND (service_type=' . $type . ') AND (date_format(FROM_UNIXTIME(served_at), \'%Y-%m-00\') = \'' . $dataFilter . '\')');

        } else {
            $dataProvider->query->select('SUM(income) as expense, client.*');
            $dataProvider->query->groupBy('client_id');
            $dataProvider->query->andWhere('(income > 0) AND (service_type=' . $type . ') AND (date_format(FROM_UNIXTIME(served_at), \'%Y-%m-00\') = \'' . $dataFilter . '\')');
        }

        // ???????????????????? ???????????????????? ?????? ????????????
        $GLOBALS['company'] = $company;

        $columns = [
            [
                'header' => '',
                'class' => 'kartik\grid\SerialColumn'
            ],
            [
                'header' => '',
                'options' => [
                    'style' => 'width: 450px',
                ],
                'value' => function ($data) {
                    if($GLOBALS['company'] == 1) {

                        $model = Company::findOne(['id' => $data->id]);

                        return $model->name;
                    } else {
                        return $data->partner->name;
                    }
                },
            ],
            [
                'header' => '',
                'value' => function ($data) {
                    if($GLOBALS['company'] == 1) {
                        $model = Company::findOne(['id' => $data->id]);

                        return $model->getFullAddress();
                    } else {
                        return $data->partner->fullAddress;
                    }
                },
            ],
        ];

        echo GridView::widget([
            'id' => 'act-grid',
            'dataProvider' => $dataProvider,
            'summary' => false,
            'emptyText' => '',
            'panel' => [
                'type' => 'primary',
                'before' => false,
                'footer' => false,
                'after' => false,
            ],
            'resizableColumns' => false,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'showPageSummary' => false,
            'filterSelector' => '.ext-filter',
            'columns' => $columns,
        ]);
        // ???????????????????? ???????????????????? ?????? ????????????
    }

}
