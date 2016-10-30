<?php

/* @var $this yii\web\View
 * @var $model common\models\CompanyAttributes
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Обслуживаемые марки ТС
    </div>
    <div class="panel-body company-attribute-line">

        <div class="col-sm-2">
            Обслуживание как официальный дилер
        </div>
        <div class="col-sm-10">
            <?= implode(", ", $model->value['official_dealer_mark']) ?>
        </div>


        <div class="col-sm-2">
            Обслуживание как неофициальный дилер
        </div>
        <div class="col-sm-10">
            <?= implode(", ", $model->value['nonofficial_dealer_mark']) ?>
        </div>

    </div>
</div>
