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
        $period = Yii::$app->request->get('period', date('m') . '-' . date('Y'));
        list($month, $year) = explode('-', $period);
        
        $dataProvider = new ActiveDataProvider([
            'query' => Act::find()
                ->where(['service_type' => $type])
                ->andWhere(['MONTH(FROM_UNIXTIME(`served_at`))' => $month])
                ->andWhere(['YEAR(FROM_UNIXTIME(`served_at`))' => $year])
                ->joinWith(['card', 'type', 'mark']),
            'pagination' => false,
        ]);
        if ($company) {
            $dataProvider->sort = [
                'defaultOrder' => [
                    'client_id' => SORT_DESC,
                    'served_at' => SORT_ASC,
                ]
            ];
        } else {
            $dataProvider->sort = [
                'defaultOrder' => [
                    'partner_id' => SORT_DESC,
                    'served_at' => SORT_ASC,
                ]
            ];
        }

        $model = new Act();
        $model->service_type = $type;

        $serviceList = Service::find()->where(['type' => $type])->select(['description', 'id'])->indexBy('id')->column();

        $searchModel = new ActSearch();
        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
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
