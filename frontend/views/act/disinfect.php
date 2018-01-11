<?php
/**
 * @var $this yii\web\View
 * @var $serviceList array[]
 * @var $companyList array[]
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\ActSearch
 * @var $role string
 */

$this->title = 'Массовая дезинфекция';

echo $this->render('_create_tabs');

echo $this->render('_mass_form', [
    'searchModel' => $searchModel,
    'serviceList' => $serviceList,
    'companyList' => $companyList,
    'showError' => $showError,
]);

if ($dataProvider) {
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Обработанные
    </div>
    <div class="panel-body">
        <?php
        echo $this->render('/car/_list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'role' => $role,
        ]);
        }
        ?>
    </div>
</div>

