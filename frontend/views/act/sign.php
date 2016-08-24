<?php
/**
 * @var $this yii\web\View
 * @var $model \common\models\Act
 */

\frontend\assets\WpaintAsset::register($this);

$this->title = 'Подпись';

echo $this->render('_sign', [
    'model' => $model,
]);