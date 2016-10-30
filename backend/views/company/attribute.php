<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\Company
 */

$this->title = 'Редактирование ' . $company->name;

echo $this->render('_update_tabs',
    [
        'model' => $model,
    ]);
?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Данные заявки
    </div>
    <div class="panel-body company-attribute-line">
        <?php
        foreach ($model->companyAttribute as $attribute) {

            $template = $attribute->getTemplate();
            if ($template) {
                echo $this->render($template,
                    [
                        'model' => $attribute,
                    ]);
            }
        }
        if ($model->companyClient) {
            echo $this->render("/company-attribute/_organisation",
                [
                    'model' => $model,
                ]);
        }
        if (!$model->companyAttribute && !$model->companyClient) {
            echo "Данные заявки отсутствуют";
        }
        ?>
    </div>
</div>