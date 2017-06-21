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

$script = <<< JS
    $('.menu-left a').click(function(){
        if($(this).attr('href')=='#'){
            $(this).parent().find('ul').collapse('toggle');
            return false;
        }
    });
     $('.menu-left .active ul').collapse('show');
JS;
$this->registerJs($script, \yii\web\View::POS_READY);
use yii\widgets\Menu;

echo Menu::widget([
    'encodeLabels'    => false,
    'submenuTemplate' => "\n<ul class='collapse list-group-sub'>\n{items}\n</ul>\n",
    'items'           => $items,
    'options'         => ['class' => 'list-group menu-left'],
    'itemOptions'     => ['class' => 'list-group-item'],
]);