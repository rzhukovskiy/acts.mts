<?php
/**
 * @var $this yii\web\View
 * @var $model \common\models\Act
 */

use yii\helpers\Html;
use yii\web\View;

$script = <<< JS
    var first = false;

    function saveSign() {
        var image = $('#wPaint').wPaint('image');
        if (first) {
            var data = {sign: image};
        } else {
            var data = {name: image};
        }

        $.ajax({
            type: 'POST',
            url: '/act/sign?id=$model->id',
            data: data,
            success: function (resp) {
                if (first) {
                    document.location.href = document.referrer;
                } else {
                    first = true;
                    $('#wPaint').wPaint('clear');
                    $('div.panel-heading').text('Распишитесь:');
                }
            }
        });
    }

    // init wPaint
    $('#wPaint').wPaint({
        saveImg:     saveSign,
        bg:          '#fff',
        lineWidth:   '1',       // starting line width
        fillStyle:   '#fff', // starting fill style
        strokeStyle: '#3355aa'  // start stroke style
    });
JS;
$this->registerJs($script, View::POS_END);
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Введите фамилию:
    </div>
    <div class="panel-body">

        <div class="row" id="wPaint"
             style="position:relative; width:100%; height:300px; background-color:#eee; margin: 20px 0px;">
        </div>

        <div style="padding: 20px">
            <span class="field">
                <?= Html::button('Очистить', ['class' => 'btn btn-primary input-sm', 'onclick' => "$('#wPaint').wPaint('clear');"]); ?>
                <?= Html::button('Далее', ['class' => 'btn btn-primary input-sm', 'style' => 'opacity: 1;', 'onclick' => "saveSign();"]); ?>
            </span>
        </div>
    </div>
</div>