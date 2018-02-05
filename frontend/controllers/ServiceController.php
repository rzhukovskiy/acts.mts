<?php

namespace frontend\controllers;

use common\models\Company;
use common\models\Mark;
use common\models\search\ServiceSearch;
use common\models\ServiceReplace;
use common\models\ServiceReplaceItem;
use common\models\Type;
use common\models\User;
use Yii;
use common\models\Service;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ServiceController implements the CRUD actions for Service model.
 */
class ServiceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['index', 'replace', 'createreplace', 'updatereplace', 'delreplace', 'getSelectServices'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Service models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ServiceSearch();
        $dataProvider = new ActiveDataProvider($searchModel->search(Yii::$app->request->queryParams));
        $dataProvider->pagination = false;
        $model = new Service();
        $model->type = $searchModel->type;

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    /**
     * Creates a new Service model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Service();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'ServiceSearch[type]' => $model->type]);
        } else {
            return $this->goBack();
        }
    }

    /**
     * Updates an existing Service model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'ServiceSearch[type]' => $model->type]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Service model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $type = $model->type;
        $model->delete();

        return $this->redirect(['index', 'ServiceSearch[type]' => $type]);
    }

    /**
     * Finds the Service model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Service the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Service::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionReplace($type)
    {

        $searchModel = ServiceReplace::find()->where(['type' => $type]);

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'id'    => SORT_ASC,
            ]
        ];

        $model = new ServiceReplace();
        $CarTypes = Type::find()->select('name')->indexBy('id')->asArray()->column();
        $CarMarks = Mark::find()->select('name')->indexBy('id')->asArray()->column();
        $CompanyList = Company::find()->where(['OR', ['type' => $type], ['type' => Company::TYPE_OWNER]])->andWhere(['OR', ['status' => 2], ['status' => 10]])->select('name')->indexBy('id')->orderBy('id')->asArray()->column();

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'model' => $model,
            'type' => $type,
            'CarTypes' => $CarTypes,
            'CarMarks' => $CarMarks,
            'CompanyList' => $CompanyList,
        ]);
    }

    public function actionCreatereplace($type)
    {

        $model = new ServiceReplace();
        $model->type = $type;

        $params = Yii::$app->request->post();

        // Если не заданы типы ТС
        if(!isset($params['ServiceReplace']['type_partner'])) {
            $params['ServiceReplace']['type_partner'] = 0;
        } elseif(!($params['ServiceReplace']['type_partner'] > 0)) {
            $params['ServiceReplace']['type_partner'] = 0;
        }

        if(!isset($params['ServiceReplace']['type_client'])) {
            $params['ServiceReplace']['type_client'] = 0;
        } elseif(!($params['ServiceReplace']['type_client'] > 0)) {
            $params['ServiceReplace']['type_client'] = 0;
        }
        // Если не заданы типы ТС

        // Если не задана марка ТС
        if(!isset($params['ServiceReplace']['mark_partner'])) {
            $params['ServiceReplace']['mark_partner'] = 0;
        } elseif(!($params['ServiceReplace']['mark_partner'] > 0)) {
            $params['ServiceReplace']['mark_partner'] = 0;
        }

        if(!isset($params['ServiceReplace']['mark_client'])) {
            $params['ServiceReplace']['mark_client'] = 0;
        } elseif(!($params['ServiceReplace']['mark_client'] > 0)) {
            $params['ServiceReplace']['mark_client'] = 0;
        }
        // Если не задана марка ТС

        // не сохранять если не переданы услуги
        if((isset($params['partner'])) && (isset($params['client']))) {
            if ((count($params['partner']) > 0) && (count($params['client']) > 0)) {

                // Если задан тип тс то у обоих
                if(((($params['ServiceReplace']['type_partner'] > 0) && ($params['ServiceReplace']['type_client'] > 0)) || (($params['ServiceReplace']['type_partner'] == 0) && ($params['ServiceReplace']['type_client'] == 0))) && ((($params['ServiceReplace']['mark_partner'] > 0) && ($params['ServiceReplace']['mark_client'] > 0)) || (($params['ServiceReplace']['mark_partner'] == 0) && ($params['ServiceReplace']['mark_client'] == 0)))) {

                    if ($model->load($params) && $model->save()) {
                    // Успешно

                    // Создаем замещение итемс
                    foreach ($params['partner'] as $key => $value) {
                        $item = new ServiceReplaceItem();
                        $item->replace_id = $model->id;
                        $item->service_id = $value;
                        $item->company_id = $model->partner_id;
                        $item->type = $model->type;
                        $item->car_type = $model->type_partner;
                        $item->save();
                        $item = null;
                    }
                    foreach ($params['client'] as $key => $value) {
                        $item = new ServiceReplaceItem();
                        $item->replace_id = $model->id;
                        $item->service_id = $value;
                        $item->company_id = $model->client_id;
                        $item->type = $model->type;
                        $item->car_type = $model->type_client;
                        $item->save();
                        $item = null;
                    }
                }

                }

            }
        }

        return $this->redirect(['replace', 'type' => $model->type]);

    }

    public function actionUpdatereplace($id)
    {

        $model = ServiceReplace::findOne($id);

        $params = Yii::$app->request->post();

        if($params) {

            // Если не заданы типы ТС
            if (!isset($params['ServiceReplace']['type_partner'])) {
                $params['ServiceReplace']['type_partner'] = 0;
            } elseif (!($params['ServiceReplace']['type_partner'] > 0)) {
                $params['ServiceReplace']['type_partner'] = 0;
            }

            if (!isset($params['ServiceReplace']['type_client'])) {
                $params['ServiceReplace']['type_client'] = 0;
            } elseif (!($params['ServiceReplace']['type_client'] > 0)) {
                $params['ServiceReplace']['type_client'] = 0;
            }
            // Если не заданы типы ТС

            // Если не задана марка ТС
            if(!isset($params['ServiceReplace']['mark_partner'])) {
                $params['ServiceReplace']['mark_partner'] = 0;
            } elseif(!($params['ServiceReplace']['mark_partner'] > 0)) {
                $params['ServiceReplace']['mark_partner'] = 0;
            }

            if(!isset($params['ServiceReplace']['mark_client'])) {
                $params['ServiceReplace']['mark_client'] = 0;
            } elseif(!($params['ServiceReplace']['mark_client'] > 0)) {
                $params['ServiceReplace']['mark_client'] = 0;
            }
            // Если не задана марка ТС

            // Если задан тип тс то у обоих
            if(((($params['ServiceReplace']['type_partner'] > 0) && ($params['ServiceReplace']['type_client'] > 0)) || (($params['ServiceReplace']['type_partner'] == 0) && ($params['ServiceReplace']['type_client'] == 0))) && ((($params['ServiceReplace']['mark_partner'] > 0) && ($params['ServiceReplace']['mark_client'] > 0)) || (($params['ServiceReplace']['mark_partner'] == 0) && ($params['ServiceReplace']['mark_client'] == 0)))) {

                // не сохранять если не переданы услуги
                if ((isset($params['partner'])) && (isset($params['client']))) {
                    if ((count($params['partner']) > 0) && (count($params['client']) > 0)) {

                        if ($model->load($params) && $model->save()) {

                            // Успешно

                            // Создаем замещение итемс
                            ServiceReplaceItem::deleteAll(['replace_id' => $id]);

                            foreach ($params['partner'] as $key => $value) {
                                $item = new ServiceReplaceItem();
                                $item->replace_id = $model->id;
                                $item->service_id = $value;
                                $item->company_id = $model->partner_id;
                                $item->type = $model->type;
                                $item->car_type = $model->type_partner;
                                $item->save();
                                $item = null;
                            }
                            foreach ($params['client'] as $key => $value) {
                                $item = new ServiceReplaceItem();
                                $item->replace_id = $model->id;
                                $item->service_id = $value;
                                $item->company_id = $model->client_id;
                                $item->type = $model->type;
                                $item->car_type = $model->type_client;
                                $item->save();
                                $item = null;
                            }

                            return $this->redirect(['replace', 'type' => $model->type]);
                        }

                    } else {
                        return $this->redirect(['updatereplace', 'id' => $model->id]);
                    }
                } else {
                    return $this->redirect(['updatereplace', 'id' => $model->id]);
                }

            } else {

                $model->addError('type_partner', 'Необходимо выбрать или убрать тип ТС для обоих!');
                $model->addError('type_client', 'Необходимо выбрать или убрать тип ТС для обоих!');

                return $this->render('updatereplace', [
                    'model' => $model,
                    'type' => $model->type,
                ]);
            }

        }

        return $this->render('updatereplace', [
            'model' => $model,
            'type' => $model->type,
        ]);

    }

    public function actionDelreplace($id)
    {
        $model = ServiceReplace::findOne($id);

        if($model->delete()) {
            ServiceReplaceItem::deleteAll(['replace_id' => $id]);
        }

        return $this->redirect(['replace', 'type' => $model->type]);

    }

    public static function getSelectServices($replace_id)
    {

        $model = ServiceReplace::findOne(['id' => $replace_id]);

        $arrRes = [];
        $arrPartner = [];
        $arrClient = [];

        $items = ServiceReplaceItem::find()->where(['replace_id' => $replace_id])->select('service_id, company_id')->asArray()->orderBy('id')->all();

        for ($n = 0; $n < count($items); $n++) {
            if($model->partner_id == $items[$n]['company_id']) {
                $arrPartner[] = $items[$n]['service_id'];
            } else {
                $arrClient[] = $items[$n]['service_id'];
            }
        }

        $arrRes[] = $arrPartner;
        $arrRes[] = $arrClient;

        return $arrRes;

    }

}
