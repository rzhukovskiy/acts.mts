<?php

namespace frontend\controllers;

use common\models\Service;
use common\models\User;
use Yii;
use common\models\Act;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\data\ActiveDataProvider;

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
                ],
            ],
        ];
    }

    public function actionList( $type, $company = false )
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Act::find()->where(['service_type' => $type]),
            'pagination' => false,
        ]);
        if ($company) {
            $dataProvider->sort = [
                'defaultOrder' => [
                    'client_id' => SORT_DESC,
                ]
            ];
        } else {
            $dataProvider->sort = [
                'defaultOrder' => [
                    'partner_id' => SORT_DESC,
                ]
            ];
        }

        $model = new Act();
        $model->service_type = $type;

        $serviceList = Service::find()->where(['type' => $type])->select(['description', 'id'])->indexBy('id')->column();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'type' => $type,
            'model' => $model,
            'serviceList' => $serviceList,
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
        $model->partner_id = 2;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->goBack();
        }
    }
}
