<?php
    use yii\bootstrap\Tabs;
    use \common\models\Company;

    /**
     * @var $this \yii\web\View
     * @var $model common\models\Company
     * @var $active string
     */

    $items = [
        [
            'label' => Company::$listType[$model->type]['ru'],
            'url' => ['list', 'type' => $model->type],
            'active' => \Yii::$app->controller->action->id == 'list',
        ],
        [
            'label' => 'Редактирование',
            'url' => '#',
            'active' => \Yii::$app->controller->action->id == 'update',
        ],
    ];

    echo Tabs::widget( [
        'items' => $items,
    ] );