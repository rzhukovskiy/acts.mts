<?php
use frontend\assets\AppAsset;

AppAsset::register($this);
$script = <<< JS
plyr.setup();
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

$this->title = 'Новогоднее поздравление';
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Новогоднее поздравление
        </div>
    <video poster="http://mtransservice.ru/wp-includes/images/logoplayer.jpg" controls>
        <source src="http://mtransservice.ru/newyear.mp4" type="video/mp4">

    </video>
    </div>