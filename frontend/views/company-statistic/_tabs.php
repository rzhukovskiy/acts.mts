<?php
    use yii\bootstrap\Tabs;

    /**
     * @var $this \yii\web\View
     * @var $active string
     */

    $request = Yii::$app->request;
    $items = [
        [
            'label' => 'Мойка',
            'url' => ['/company-statistic/list', 'type' => '2'],
            'active' => $request->get('type') == '2',
        ],
        [
            'label' => 'Сервис',
            'url' => ['/company-statistic/list', 'type' => '3'],
            'active' => $request->get('type') == '3',
        ],
        [
            'label' => 'Шиномонтаж',
            'url' => ['/company-statistic/list' , 'type' => '4'],
            'active' => $request->get('type') == '4',
        ],
        [
            'label' => 'Дезинфекция',
            'url' => ['/company-statistic/list' , 'type' => '5'],
            'active' => $request->get('type') == '5',
        ],
        [
            'label' => 'Универсальная',
            'url' => '/company-statistic/total',
            'active' => Yii::$app->controller->action->id == 'total',
        ],
    ];

    echo Tabs::widget( [
        'items' => $items,
    ] ) ?>