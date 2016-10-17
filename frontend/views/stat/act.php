<?php
/**
 * @var $this \yii\web\View
 * @var $model \common\models\Act
 * @var $group string
 */
$this->title = 'Акт';

echo $this->render($group == 'company' ? '/act/client/_view' : '/act/partner/_view', [
    'model' => $model,
    'company' => $group == 'company',
]);