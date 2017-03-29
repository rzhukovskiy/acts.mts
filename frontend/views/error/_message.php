<?php
/**
 * @var $model \common\models\Act
 */
use common\models\Act;
use common\models\Car;

?>
<div class="col-sm-12" style="padding: 10px;">
    <?php
    $allMessage = $model->errorMessage();
    if ($allMessage) {
        foreach ($allMessage as $key => $message) {
            echo \yii\helpers\Html::tag('span',
                $message,
                ['class' => 'label label-danger act-error-message']);
        }
    }
    //TODO разделить два типа ошибок с номером машины
    if ($model->hasError(Act::ERROR_CAR) && empty($model->car->company_id)) {
        $car = new Car();
        $car->company_id = $model->client_id;
        $car->number = $model->car_number;
        $car->mark_id = $model->mark_id;
        $car->type_id = $model->type_id;
        echo $this->render('_add_car', ['model' => $car, 'act_id' => $model->id]);
    }
    ?>
</div>