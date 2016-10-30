<?php

/* @var $this yii\web\View
 * @var $model common\models\CompanyAttributes
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Города
    </div>
    <div class="panel-body company-attribute-line">
        <?= implode(", ", $model->value) ?>
    </div>
</div>
