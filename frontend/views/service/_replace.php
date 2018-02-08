<?php

use yii\grid\GridView;
use common\models\Service;
use yii\helpers\Html;
use common\models\ServiceReplace;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider
 */

$GLOBALS['CarTypes'] = $CarTypes;
$GLOBALS['CarMarks'] = $CarMarks;
$GLOBALS['CompanyList'] = $CompanyList;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список Замещений
    </div>
    <div class="panel-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'layout' => '{items}',
            'emptyText' => '',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'type',
                    'filter' => false,
                    'value' => function ($data) {
                        return Service::$listType[$data->type]['ru'];
                    },
                ],
                [
                    'attribute' => 'partner_id',
                    'filter' => Html::activeDropDownList($searchModel, 'partner_id', ServiceReplace::find()->innerJoin('company', '`company`.`id` = `service_replace`.`partner_id`')->where(['service_replace.type' => Yii::$app->request->get('type')])->select('company.name')->indexBy('partner_id')->column(), ['class' => 'form-control', 'prompt' => 'Все партнеры']),
                    'value' => function ($data) {
                        return isset($GLOBALS['CompanyList'][$data->partner_id]) ? $GLOBALS['CompanyList'][$data->partner_id] : '-';
                    },
                ],
                [
                    'attribute' => 'client_id',
                    'filter' => Html::activeDropDownList($searchModel, 'client_id', ServiceReplace::find()->innerJoin('company', '`company`.`id` = `service_replace`.`client_id`')->where(['service_replace.type' => Yii::$app->request->get('type')])->select('company.name')->indexBy('client_id')->column(), ['class' => 'form-control', 'prompt' => 'Все клиенты']),
                    'value' => function ($data) {
                        return isset($GLOBALS['CompanyList'][$data->client_id]) ? $GLOBALS['CompanyList'][$data->client_id] : '-';
                    },
                ],
                [
                    'attribute' => 'type_partner',
                    'filter' => Html::activeDropDownList($searchModel, 'type_partner', ServiceReplace::find()->innerJoin('type', '`type`.`id` = `service_replace`.`type_partner`')->where(['service_replace.type' => Yii::$app->request->get('type')])->select('name')->indexBy('type_partner')->column(), ['class' => 'form-control', 'prompt' => 'Все типы']),
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
                    'filter' => Html::activeDropDownList($searchModel, 'type_client', ServiceReplace::find()->innerJoin('type', '`type`.`id` = `service_replace`.`type_client`')->where(['service_replace.type' => Yii::$app->request->get('type')])->select('name')->indexBy('type_client')->column(), ['class' => 'form-control', 'prompt' => 'Все типы']),
                    'value' => function ($data) {
                        if($data->type_client > 0) {
                            return isset($GLOBALS['CarTypes'][$data->type_client]) ? $GLOBALS['CarTypes'][$data->type_client] : '-';
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'mark_partner',
                    'filter' => Html::activeDropDownList($searchModel, 'mark_partner', ServiceReplace::find()->innerJoin('mark', '`mark`.`id` = `service_replace`.`mark_partner`')->where(['service_replace.type' => Yii::$app->request->get('type')])->select('name')->indexBy('mark_partner')->column(), ['class' => 'form-control', 'prompt' => 'Все типы']),
                    'value' => function ($data) {
                        if($data->mark_partner > 0) {
                            return isset($GLOBALS['CarMarks'][$data->mark_partner]) ? $GLOBALS['CarMarks'][$data->mark_partner] : '-';
                        } else {
                            return '-';
                        }
                    },
                ],
                [
                    'attribute' => 'mark_client',
                    'filter' => Html::activeDropDownList($searchModel, 'mark_client', ServiceReplace::find()->innerJoin('mark', '`mark`.`id` = `service_replace`.`mark_client`')->where(['service_replace.type' => Yii::$app->request->get('type')])->select('name')->indexBy('mark_client')->column(), ['class' => 'form-control', 'prompt' => 'Все типы']),
                    'value' => function ($data) {
                        if($data->mark_client > 0) {
                            return isset($GLOBALS['CarMarks'][$data->mark_client]) ? $GLOBALS['CarMarks'][$data->mark_client] : '-';
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
