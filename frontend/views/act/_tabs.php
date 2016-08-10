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
            'url' => ['/act/list', 'type' => 2],
            'active' => $request->get('type') == 2 && !$request->get('company'),
        ],
        [
            'label' => 'Для компании',
            'url' => ['/act/list', 'type' => 2, 'company' => true],
            'active' => $request->get('type') == 2 && $request->get('company') == true,
        ],
        [
            'label' => 'Сервис',
            'url' => ['/act/list', 'type' => 3],
            'active' => $request->get('type') == 3 && !$request->get('company'),
        ],
        [
            'label' => 'Для компании',
            'url' => ['/act/list', 'type' => 3, 'company' => true],
            'active' => $request->get('type') == 3 && $request->get('company') == true,
        ],
        [
            'label' => 'Шиномонтаж',
            'url' => ['/act/list' , 'type' => 4],
            'active' => $request->get('type') == 4 && !$request->get('company'),
        ],
        [
            'label' => 'Для компании',
            'url' => ['/act/list', 'type' => 4, 'company' => true],
            'active' => $request->get('type') == 4 && $request->get('company') == true,
        ],
        [
            'label' => 'Дезинфекция',
            'url' => ['/act/list' , 'type' => 5],
            'active' => $request->get('type') == 5 && !$request->get('company'),
        ],
        [
            'label' => 'Для компании',
            'url' => ['/act/list', 'type' => 5, 'company' => true],
            'active' => $request->get('type') == 5 && $request->get('company') == true,
        ],
        [
            'label' => 'Универсальная',
            'url' => ['/act/list' , 'type' => 6],
            'active' => $request->get('type') == 6 && !$request->get('company'),
        ],
        [
            'label' => 'Для компании',
            'url' => ['/act/list', 'type' => 6, 'company' => true],
            'active' => $request->get('type') == 6 && $request->get('company') == true,
        ],
    ];

    echo Tabs::widget( [
        'items' => $items,
    ] ) ?>