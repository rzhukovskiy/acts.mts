<?php
/**
 * @var $type string
 * @var $department_id integer
 */
use yii\helpers\Url;

?>
<div class="col-sm-12 sub-menu">
    <a href="<?= Url::to(['list', 'department_id' => $department_id, 'type' => 'inbox'])?>" class="<?= $type == 'inbox' ? 'active' : '' ?>">Входящие</a>
    <a href="<?= Url::to(['list', 'department_id' => $department_id, 'type' => 'outbox'])?>" class="<?= $type == 'outbox' ? 'active' : '' ?>">Исходящие</a>
</div>