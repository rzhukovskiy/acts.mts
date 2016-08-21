<?php

namespace frontend\controllers;

use common\models\search\ActSearch;
use common\models\Service;
use common\models\User;
use Yii;
use common\models\Act;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
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
                        'actions' => ['list', 'create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],
                    [
                        'actions' => ['list', 'view', 'create'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER],
                    ],
                ],
            ],
        ];
    }

    public function actionList( $type, $company = false )
    {
        $searchModel = new ActSearch(['scenario' => $company ? Act::SCENARIO_CLIENT : Act::SCENARIO_PARTNER]);
        if (!empty(Yii::$app->user->identity->company_id)) {
            if ($company) {
                $searchModel->client_id = Yii::$app->user->identity->company->id;
            } else {
                $searchModel->partner_id = Yii::$app->user->identity->company->id;
            }
        }
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
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

        return $this->render('create', [
            'type' => $type,
            'serviceList' => $serviceList,
            'role' => Yii::$app->user->identity->role,
            'model' => $model,
        ]);
    }
}
