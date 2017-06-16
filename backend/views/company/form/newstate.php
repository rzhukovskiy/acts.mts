<?php
$this->title = 'Новый статус клиента';
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        Добавление статуса
    </div>
    <div class="panel-body">
        <?= $this->render('_newstate', [
            'id' => $id,
            'model' => $model,
            'companyMembers' => $companyMembers,
            'authorMembers' => $authorMembers,
            'modelCompanyOffer' => $modelCompanyOffer,
        ]);
        ?>
    </div>
</div>