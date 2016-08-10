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
            'url' => ['/archive/error', 'type' => 2],
            'active' => $request->get('type') == 2 && !$request->get('company'),
        ],
        [
            'label' => 'Сервис',
            'url' => ['/archive/error', 'type' => 3],
            'active' => $request->get('type') == 3 && !$request->get('company'),
        ],
        [
            'label' => 'Шиномонтаж',
            'url' => ['/archive/error' , 'type' => 4],
            'active' => $request->get('type') == 4 && !$request->get('company'),
        ],
        [
            'label' => 'Дезинфекция',
            'url' => ['/archive/error' , 'type' => 5],
            'active' => $request->get('type') == 5 && !$request->get('company'),
        ],
        [
            'label' => 'Универсальная',
            'url' => ['/archive/error' , 'type' => 6],
            'active' => $request->get('type') == 6 && !$request->get('company'),
        ],
    ];

    echo Tabs::widget( [
        'items' => $items,
    ] ) ?>