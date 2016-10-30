<?php

/* @var $this yii\web\View
 * @var $model common\models\CompanyAttributes
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Для каких видов ТС компания продает шины и диски
    </div>
    <div class="panel-body company-attribute-line">
        <?
        $values = $model->value;
        foreach ($values as &$carType) {
            $carType = \backend\models\forms\TiresForm::$listCarType[$carType];
        }
        echo implode(", ", $values) ?>
    </div>
</div>
