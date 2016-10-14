<?php
/**
 * @var $allMessage array
 */

?>
<div class="col-sm-12" style="padding: 10px;">
    <?php
    if ($allMessage) {
        foreach ($allMessage as $message) {
            echo \yii\helpers\Html::tag('span',
                $message,
                ['class' => 'label label-danger', 'style' => 'margin:0 5px; font-size: 1.1em']);
        }
    }
    ?>
</div>