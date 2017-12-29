<?php

use yii\widgets\ActiveForm;
use common\models\Service;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View
 * @var $model common\models\Service
 * @var $form yii\widgets\ActiveForm
 * @var $searchModel common\models\search\ServiceSearch
 */

$this->title = 'Изменение замещения';

$items = [];
$items[] = [
    'label' => 'Замещение услуг',
    'url' => ['replace', 'type' => $model->type],
    'active' => Yii::$app->controller->action->id == 'replace',
];
$items[] = [
    'label' => 'Редактирование замещения',
    'url' => ['updatereplace', 'id' => $model->id],
    'active' => Yii::$app->controller->action->id == 'updatereplace',
];

echo Tabs::widget([
    'items' => $items,
]);

echo $this->render('_formReplace', [
    'model' => $model,
    'type' => $type,
]);

?>