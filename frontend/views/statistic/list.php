<?php

use yii\grid\GridView;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;

/**
 * @var $this yii\web\View
 * @var $type null|string
 * @var $dataProvider
 * @var $searchModel
 */

echo $this->render('_tabs');
?>
<h1>statistic/list</h1>

<p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'summary' => false,
        'emptyCell' => '',
        'columns' => [
            [
                'header' => '№',
                'class' => 'yii\grid\SerialColumn',
            ],
            'id',
            'partner_id',
            [
                'attribute' => 'partner_id',
                'content' => function($data) {
                    return !empty($data->partner->name) ? $data->partner->name : '—';
                },
            ],
            [
                'header' => 'Город',
                'attribute' => 'company_id',
                'content' => function($data) {
                    return $data->partner->address;
                }
            ],
            //'client_id',
//            [
//                'attribute' => 'client_id',
//                'content' => function($data) {
//                    return !empty($data->client->name) ? $data->client->name : '—';
//                },
//            ],
            //'type_id',
            [
                'attribute' => 'type_id',
                'content' => function($data) {
                    return !empty($data->type->name) ? $data->type->name : '—';
                },
            ],
//            'card_id',
            [
                'attribute' => 'card_id',
                'content' => function($data) {
                    return !empty($data->card->number) ? $data->card->number : '—';
                },
            ],
            // 'mark_id',
            // 'expense',
            // 'income',
            // 'profit',
            // 'service_type',
            // 'status',
            // 'number',
            // 'extra_number',
            // 'check',
            // 'created_at',
            // 'updated_at',
            // 'served_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
    <?php

    //        echo $this->render( Company::$listType[ $type ][ 'en' ] . '/_list', [
    //            'dataProvider' => null,
    //        ] );
    ?>

</p>
