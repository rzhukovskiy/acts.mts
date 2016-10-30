<?php

/* @var $this yii\web\View
 * @var $model common\models\CompanyAttributes
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Услуги, которые оказывает компания
    </div>
    <div class="panel-body company-attribute-line">
        <?
        $values = $model->value;
        foreach ($values as &$tireService) {
            $tireService = \backend\models\forms\TiresForm::$listService[$tireService];
        }
        echo implode(", ", $values) ?>

    </div>
</div>
