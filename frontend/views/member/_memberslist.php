<?php

use kartik\grid\GridView;
use common\models\Company;
use \yii\web\View;

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $searchModel common\models\search\CompanyDriverSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$script = <<< JS

$('.uploadPhonesButt').on('click', function(){
// Инициируем нажатие на форму выбора файла
$(".uploadPhones").trigger("click");
});

// Отправляем файл если он был выбран
form = $(".uploadPhonesForm"), upload = $(".uploadPhones");
upload.change(function(){
form.submit();
});

JS;
$this->registerJs($script, View::POS_READY);

?>
<div class="panel panel-primary">
    <div class="panel-heading">Сотрудники</div>

    <?php


    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover' => false,
        'striped' => false,
        'export' => false,
        'summary' => false,
        'emptyText' => '',
        'layout' => '{items}',
        'filterSelector' => '.ext-filter',
        'beforeHeader' => [
            [
                'options' => ['class' => 'extend-header'],
            ],

        ],
        'columns' => [

            [
                'header' => '№',
                'class' => 'kartik\grid\SerialColumn'
            ],

            [
                'header' => 'Филиал',
                'group' => true,
                'groupedRow' => true,
                'groupOddCssClass' => 'kv-group-header',
                'groupEvenCssClass' => 'kv-group-header',
                'filter' => false,
                'attribute' => 'company_id',
                'value' => function ($data) {
                    return Company::find()->select(['name'])->where(['id' => $data->company_id])->column()[0];
                },
            ],
            [
                'attribute' => 'position',

            ],
            [
                'attribute' => 'email',
            ],
            [
                'attribute' => 'name',
            ],
            [
                'attribute' => 'phone',
                'value' => function ($data) {

                    $phone = $data->phone;
                    $phone = str_replace(" ", '', $phone);
                    $phone = str_replace("-", '', $phone);

                    return $phone;

                },
            ],
        ],
    ]);

    ?>

</div>
