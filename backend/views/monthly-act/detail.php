<?php
use yii\bootstrap\Tabs;

/**
 * @var $this yii\web\View
 * @var $type null|integer
 * @var $model \common\models\MonthlyAct
 */

$this->title = 'Детализация акта';

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
        /*[
            'label'  => 'Редактирование',
            'url'    => ['update', 'id' => $model->id],
            'active' => false,
        ],*/
        [
            'label'  => 'Детализация',
            'url'    => '#',
            'active' => true,
        ],
    ],
]);

echo $this->render('_form_detail',
    [
        'model' => $model,
    ]);