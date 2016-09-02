<?php
    /**
     * Created by PhpStorm.
     * User: ruslanzh
     * Date: 09/08/16
     * Time: 19:27
     */

    /**
     * @var $this \yii\web\View
     * @var $items array
     */

    use yii\widgets\Menu;

    echo Menu::widget( [
        'encodeLabels' => false,
        'items' => $items,
        'options' => ['class' => 'list-group menu-left'],
        'itemOptions' => ['class' => 'list-group-item'],
    ] );