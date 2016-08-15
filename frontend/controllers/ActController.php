<?php

namespace frontend\controllers;

use Yii;
use common\models\Act;
use yii\base\Controller;
use yii\data\ActiveDataProvider;

class ActController extends Controller
{
    public function actionList( $type = null, $company = false )
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

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'type' => $type,
            'model' => $model,
        ]);
    }

}
