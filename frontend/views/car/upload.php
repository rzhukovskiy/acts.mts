<?php

use yii\bootstrap\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model \frontend\models\forms\CarUploadXlsForm
 * @var $typeDropDownItems array
 * @var $companyDropDownItems array
 */

$this->title = 'Загрузка';
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_tabs');
?>
<div class="panel panel-primary">
    <div class="panel-heading"><?=Html::encode($this->title)?></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-8">
                <?php echo $this->render('upload/_form', [
                    'model' => $model,
                    'typeDropDownItems' => $typeDropDownItems,
                    'companyDropDownItems' => $companyDropDownItems,
                ])?>
            </div>
            <div class="col-sm-4">
                <div style="padding: 20px">
                <p>Формат файла - xls. Любое количество листов.</p>
                    <p>Строка из трех объединенных столбцов - название компании.</p>
                    <p>Первый столбик - марка. Второй столбик - номер. Третий столбик - тип ТС.</p>
                    <p>Пoрядок и количество столбиков обязаны быть постоянными, даже если они пустые.</p>
                    <p>Если не указано имя компании и тип ТС, то они берутся из настроек указанных в форме загрузки.</p>
                </div>
            </div>
        </div>
    </div>
</div>