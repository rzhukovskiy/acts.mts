<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $model \common\models\MonthlyAct
 */

$this->title = 'Редактирование акта';

$request = Yii::$app->request;

echo Tabs::widget([
    'items' => [
        [
            'label'  => 'Акты',
            'url'    => [
                'monthly-act/list',
                'type' => $model->type_id,
                'MonthlyActSearch[act_date]' => date('n-Y', strtotime($model->act_date) + 432000),
            ],
            'active' => false,
        ],
        [
            'label'  => 'Редактирование',
            'url'    => '#',
            'active' => true,
        ],
        [
            'label'  => 'Детализация',
            'url'    => ['detail', 'id' => $model->id],
            'active' => false,
        ],
    ],
]);

echo $this->render('_form',
    [
        'model' => $model,

    ]);