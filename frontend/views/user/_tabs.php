<?php
    use yii\bootstrap\Tabs;
    use common\models\Company;

    /**
     * @var $this \yii\web\View
     * @var $active string
     */

    $action = \Yii::$app->controller->action->id;
    $requestType = Yii::$app->request->get('type');

    $items = [
        [
            'label' => 'Компания',
            'url' => ['/user/list', 'type'=> Company::TYPE_OWNER],
            'active' => $action == 'list' && $requestType == Company::TYPE_OWNER,
        ],
        [
            'label' => 'Мойка',
            'url' => ['/user/list', 'type' => Company::TYPE_WASH],
            'active' => $action == 'list' && $requestType == Company::TYPE_WASH,
        ],
        [
            'label' => 'Сервис',
            'url' => ['/user/list', 'type' => Company::TYPE_SERVICE],
            'active' => $action == 'list' && $requestType == Company::TYPE_SERVICE,
        ],
        [
            'label' => 'Шиномонтаж',
            'url' => ['/user/list', 'type' => Company::TYPE_TIRES],
            'active' => $action == 'list' && $requestType == Company::TYPE_TIRES,
        ],
        [
            'label' => 'Дезинфекция',
            'url' => ['/user/list', 'type' => Company::TYPE_DISINFECT],
            'active' => $action == 'list' && $requestType == Company::TYPE_DISINFECT,
        ],
        [
            'label' => 'Универсальная',
            'url' => ['/user/list', 'type' => Company::TYPE_UNIVERSAL],
            'active' => $action == 'list' && $requestType == Company::TYPE_UNIVERSAL,
        ],
    ];


    echo Tabs::widget( [
        'items' => $items,
    ] ) ?>