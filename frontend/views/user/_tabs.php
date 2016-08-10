<?php
    use yii\bootstrap\Tabs;

    /**
     * @var $this \yii\web\View
     * @var $active string
     */

    $items = [
        [
            'label' => 'Компания',
            'url' => '/user/company',
            'active' => \Yii::$app->controller->action->id == 'company',
        ],
        [
            'label' => 'Мойка',
            'url' => '/user/carwash',
            'active' => \Yii::$app->controller->action->id == 'carwash',
        ],
        [
            'label' => 'Сервис',
            'url' => '/user/service',
            'active' => \Yii::$app->controller->action->id == 'service',
        ],
        [
            'label' => 'Шиномонтаж',
            'url' => '/user/tires',
            'active' => \Yii::$app->controller->action->id == 'tires',
        ],
        [
            'label' => 'Дезинфекция',
            'url' => '/user/disinfection',
            'active' => \Yii::$app->controller->action->id == 'disinfection',
        ],
        [
            'label' => 'Универсальная',
            'url' => '/user/universal',
            'active' => \Yii::$app->controller->action->id == 'universal',
        ],
    ];


    echo Tabs::widget( [
        'items' => $items,
    ] ) ?>