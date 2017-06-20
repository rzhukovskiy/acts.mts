<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 19.06.17
 * Time: 17:49
 */
$this->title = 'Почтовые шаблоны';

?>
    <div class="user-index">
        <?= $this->render('_tabs') ?>
    </div>

<?php
echo $this->render('_list', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
]);
?>