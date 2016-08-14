<?php
    use yii\bootstrap\Tabs;

    /**
     * @var $this \yii\web\View
     * @var $active string
     */

    $items = [
        [
            'label' => 'Машины',
            'url' => '/car/list',
            'active' => \Yii::$app->controller->action->id == 'list',
        ],
        [
            'label' => 'Немытые',
            'url' => '/car/dirty',
            'active' => \Yii::$app->controller->action->id == 'dirty',
        ],
        [
            'label' => 'Загрузка',
            'url' => '/car/upload',
            'active' => \Yii::$app->controller->action->id == 'upload',
        ],
    ];

    echo Tabs::widget( [
        'items' => $items,
    ] );