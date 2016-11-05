<?php

use yii\grid\GridView;
use yii\widgets\ListView;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        <?= $searchModel->company->name ?> :: Сотрудники
    </div>
    <div class="panel-body">
        <?= $this->render('/company-member/_form', [
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
