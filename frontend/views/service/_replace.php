<?php

use yii\grid\GridView;
use common\models\Service;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider
 */

$GLOBALS['CarTypes'] = $CarTypes;
$GLOBALS['CompanyList'] = $CompanyList;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список Замещений
    </div>
    <div class="panel-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'type',
                    'value' => function ($data) {
                        return Service::$listType[$data->type]['ru'];
                    },
                ],
                [
                    'attribute' => 'partner_id',
                    'value' => function ($data) {
                        return isset($GLOBALS['CompanyList'][$data->partner_id]) ? $GLOBALS['CompanyList'][$data->partner_id] : '-';
                    },
                ],
                [
                    'attribute' => 'client_id',
                    'value' => function ($data) {
                        return isset($GLOBALS['CompanyList'][$data->client_id]) ? $GLOBALS['CompanyList'][$data->client_id] : '-';
                    },
                ],
                [
                    'attribute' => 'type_partner',
                    'value' => function ($data) {
                        if($data->type_partner > 0) {
                            return isset($GLOBALS['CarTypes'][$data->type_partner]) ? $GLOBALS['CarTypes'][$data->type_partner] : '-';
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'type_client',
                    'value' => function ($data) {
                        if($data->type_client > 0) {
                            return isset($GLOBALS['CarTypes'][$data->type_client]) ? $GLOBALS['CarTypes'][$data->type_client] : '-';
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'updated_at',
                    'value' => function ($data) {
                        return date('d-m-Y', $data->updated_at);
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'buttons'        => [
                        'update' => function ($url, $data, $key) {

                            return Html::a('<span class="glyphicon glyphicon glyphicon-pencil"></span>', [
                                'updatereplace',
                                'id' => $data->id,
                            ]);

                        },
                        'delete' => function ($url, $data, $key) {

                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', [
                                'delreplace',
                                'id' => $data->id,
                            ], [
                                'data-confirm' => "Вы уверены, что хотите удалить этот элемент?"
                            ]);

                        },
                    ],
                ],
            ],
        ]); ?>
    </div>
</div>
