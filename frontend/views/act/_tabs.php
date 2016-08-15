<?php
    use yii\bootstrap\Tabs;
    use common\models\Service;

    /**
     * @var $this \yii\web\View
     * @var $active string
     */

    $request = Yii::$app->request;
    $items = [];

    foreach (Service::$listType as $type_id => $typeData) {
        $items[] = [
            'label' => $typeData['ru'],
            'url' => ['/act/list', 'type' => $type_id],
            'active' => $request->get('type') == $type_id && !$request->get('company'),
        ];
        $items[] = [
            'label' => 'Для компании',
            'url' => ['/act/list', 'type' => $type_id, 'company' => true],
            'active' => $request->get('type') == $type_id && $request->get('company'),
        ];
    }

    echo Tabs::widget( [
        'items' => $items,
    ] );