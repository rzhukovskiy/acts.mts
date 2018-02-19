<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use kartik\editable\Editable;
use kartik\popover\PopoverX;

$this->title = 'Отправка чеков';

$GLOBALS['companyWash'] = $companyWash;
$GLOBALS['usersList'] = $usersList;

$column = [

    [
        'header' => '№',
        'vAlign'=>'middle',
        'class' => 'kartik\grid\SerialColumn'
    ],
    [
        'attribute' => 'company_id',
        'format'    => 'raw',
        'value'     => function ($data) {
            return Editable::widget([
                'model'           => $data,
                'placement'       => PopoverX::ALIGN_RIGHT,
                'inputType'       => Editable::INPUT_DROPDOWN_LIST,
                'formOptions'     => [
                    'action' => ['/delivery/updatechecks', 'id' => $data->id]
                ],
                'valueIfNull'     => '(не задано)',
                'buttonsTemplate' => '{submit}',
                'displayValue' => isset($GLOBALS['companyWash'][$data->company_id]) ? $GLOBALS['companyWash'][$data->company_id] : '-',
                'data' => $GLOBALS['companyWash'],
                'submitButton'    => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute'       => 'company_id',
                'asPopover'       => true,
                'size'            => 'md',
                'options'         => [
                    'class'       => 'form-control',
                    'placeholder' => 'Выберите мойку',
                    'id'          => 'company_id' . $data->id,
                    'value'       => $data->company_id
                ],
            ]);
        },
    ],
    [
        'attribute' => 'user_id',
        'filter' => false,
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($GLOBALS['usersList'][$data->user_id]) {
                return $GLOBALS['usersList'][$data->user_id];
            } else {
                return '-';
            }

        },
    ],
    [
        'attribute' => 'date_send',
        'format' => 'raw',
        'value'     => function ($data) {
            return Editable::widget([
                'name'            => 'date_send',
                'placement'       => PopoverX::ALIGN_LEFT,
                'inputType'       => Editable::INPUT_DATE,
                'asPopover'       => true,
                'value'           => ($data->date_send) ? date('d.m.Y', $data->date_send) : '-',
                'valueIfNull'     => '(не задано)',
                'buttonsTemplate' => '{submit}',
                'submitButton'    => [
                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'size'            => 'md',
                'formOptions'     => [
                    'action'      => ['/delivery/updatechecks', 'id' => $data->id]
                ],
                'options'         => [
                    'class'         => 'form-control',
                    'id'            => 'date_send' . $data->id,
                    'removeButton'  => false,
                    'pluginOptions' => [
                        'format'         => 'dd.mm.yyyy',
                        'autoclose'      => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options' => ['value' => ($data->date_send) ? date('d.m.Y', $data->date_send) : '']
                ],
            ]);
        },
    ],
    [
        'attribute' => 'serial_number',
        'format'    => 'raw',
        'value'     => function ($data) {
            return Editable::widget([
                'model'           => $data,
                'placement'       => PopoverX::ALIGN_LEFT,
                'inputType'       => Editable::INPUT_TEXT,
                'formOptions'     => [
                    'action'      => ['/delivery/updatechecks', 'id' => $data->id]
                ],
                'displayValue'    => isset($data->serial_number) ? $data->serial_number : '',
                'valueIfNull'     => '(не задано)',
                'buttonsTemplate' => '{submit}',
                'submitButton'    => [
                    'icon'        => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute'       => 'serial_number',
                'asPopover'       => true,
                'size'            => 'md',
                'options'         => [
                    'class'       => 'form-control',
                    'placeholder' => 'Например: 900-999',
                    'id'          => 'serial_number' . $data->id,
                    'value'       => $data->serial_number
                ],
            ]);
        },
    ],
    [
        'header' => 'Количество',
        'vAlign'=>'middle',
        'value' => function ($data) {

            if ($data->serial_number) {
                $serial_number = str_replace(' ', '', $data->serial_number);
                $serial_number = explode('-', $serial_number);
                return $serial_number[1]-$serial_number[0];
            } else {
                return '-';
            }

        },
    ],
    [
        'class' => 'kartik\grid\ActionColumn',
        'header' => 'Действие',
        'vAlign'=>'middle',
        'template' => '{update}',
        'contentOptions' => ['style' => 'min-width: 60px'],
        'buttons' => [
            'update' => function ($url, $data, $key) {
                return Html::a('<span class="glyphicon glyphicon-search"></span>',
                    ['/delivery/fullchecks', 'id' => $data->company_id]);
            },
        ],
    ],
];

?>

<div class="panel panel-primary">
    <div class="panel-heading">
        История отправки чеков
        <div class="header-btn pull-right">
            <?= Html::a('Добавить', ['delivery/newchecks'], ['class' => 'btn btn-success btn-sm']) ?>
        </div>
    </div>
    <div class="panel-body">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'layout' => '{items}',
            'columns' => $column,
        ]);
        ?>
    </div>
</div>
