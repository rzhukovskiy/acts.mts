<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\search\ActSearch;

class StatisticController extends Controller
{
    public function actionList($type = null)
    {
        $searchModel = new ActSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (!empty($type))
            $dataProvider->query->andWhere(['type_id' => $type]);

        $dataProvider->query
            ->with(['partner', 'client', 'type', 'card']);

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionTotal()
    {
        return $this->render('total');
    }

}