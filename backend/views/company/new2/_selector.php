<?php
/**
 * @var $type string
 * @var $userData array
 * @var $type integer
 * @var $searchModel \common\models\search\CompanySearch
 */
use yii\helpers\Url;

?>
<div class="col-sm-12 sub-menu">
    <?php foreach ($userData as $user_id => $data) { if (!$data['badge']) continue; ?>
        <a href="<?= Url::to(['new2', 'CompanySearch[user_id]' => $user_id, 'type' => $type]) ?>"
           class="<?= $user_id == $searchModel->user_id ? 'active' : '' ?>">
            <?=$data['username']?>
            <span class="label label-success"><?= $data['badge'] ?></span>
        </a>

    <?php } ?>
</div>