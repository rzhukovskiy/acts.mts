<?php

use yii\bootstrap\Html;
use yii\widgets\DetailView;
use frontend\models\Act;
use common\models\Service;

/**
 * @var $this \yii\web\View
 * @var $model \common\models\Act
 */
$this->title = 'Акт';
$formatter = Yii::$app->formatter;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $this->title ?>
    </div>
    <div class="panel-body">
        <div class="text-center" style="padding: 10px">
            <?php echo Html::a('Редактировать акт', ['/act/update', 'id' => $model->id], ['class' => 'btn btn-primary']); ?>
        </div>
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <div class="well">
                    <?php
                    echo DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            [
                                'attribute' => 'client_id',
                                'label' => 'Клиент',
                                'value' => $model->client->name,
                            ],
                            [
                                'attribute' => 'partner_id',
                                'value' => $model->partner->name,
                            ],
                            [
                                'attribute' => 'type_id',
                                'value' => $model->type->name,
                            ],
                            [
                                'attribute' => 'mark_id',
                                'value' => $model->mark->name,
                            ],
                            'number',
                            [
                                'attribute' => 'card_id',
                                'value' => $model->card->number,
                            ],
                            [
                                'label' => 'Статус',
                                'attribute' => 'status',
                                'value' => Act::$listStatus[$model->status]['ru'],
                                'format' => 'TEXT',
                            ],
                            [
                                'label' => 'Потрачено',
                                'attribute' => 'expense',
                                'value' => $formatter->asDecimal($model->expense, 0),
                            ],
                            [
                                'label' => 'Приход',
                                'attribute' => 'income',
                                'value' => $formatter->asDecimal($model->income, 0),
                            ],
                            [
                                'label' => 'Прибыль',
                                'attribute' => 'profit',
                                'value' => $formatter->asDecimal($model->profit, 0),
                            ],
                            [
                                'label' => 'Услуга',
                                'attribute' => 'service_type',
                                'value' => Service::$listType[$model->service_type]['ru'],
                            ],
                            [
                                'label' => 'Закрыт',
                                'attribute' => 'served_at',
                                'value' => $formatter->asDate($model->served_at, 'long'),
                            ],
                            [
                                'label' => 'Создан',
                                'attribute' => 'created_at',
                                'value' => $formatter->asDate($model->created_at, 'long'),
                            ],
                            [
                                'label' => 'Редектирован',
                                'attribute' => 'updated_at',
                                'value' => $formatter->asDate($model->updated_at, 'long'),
                            ],
                            [
                                'attribute' => 'check',
                            ]
                        ]
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

