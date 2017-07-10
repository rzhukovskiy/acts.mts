<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 19.06.17
 * Time: 17:49
 */
$this->title = 'Настройка рассылки для партнеров';

?>
    <div class="user-index">
        <?= $this->render('_tabs') ?>
    </div>

<?php
echo $this->render('_notific', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
]);
?>