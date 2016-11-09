<?php

use yii\widgets\ListView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyDriver
 * @var $searchModel common\models\search\CompanyDriverSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $searchModel->company->name ?> :: Водители
    </div>
    <div class="panel-body">
        <?= $this->render('/company-driver/_form', [
            'model' => $model,
        ]);
        ?><?=
        ListView::widget([
            'dataProvider' => $dataProvider,
            'options' => [
                'tag' => 'div',
                'class' => 'list-data',
            ],
            'layout' => "{items}",
            'itemView' => '_list_item',
        ]);
        ?>
    </div>
</div>
