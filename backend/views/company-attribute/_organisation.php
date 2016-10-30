<?php
use yii\grid\GridView;

/* @var $this yii\web\View
 * @var $model common\models\Company
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Организации, транспорт которых обслуживается в этой компании
    </div>
    <div class="panel-body">
        <?php  $dataProvider = new \yii\data\ActiveDataProvider([
            'query'      => $model->getCompanyClient(),
            'pagination' => false,
        ]);

        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'layout'       => "{items}\n{pager}",
            'columns'      => [
                'name',
                'phone'
            ],
        ]);
        ?>
    </div>
</div>
