<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 19.06.17
 * Time: 17:49
 */

if((Yii::$app->controller->action->id == 'new') || (Yii::$app->controller->action->id == 'shownew')) {
    $this->title = 'Статистика заявок';
} else if((Yii::$app->controller->action->id == 'archive') || (Yii::$app->controller->action->id == 'showarchive')) {
    $this->title = 'Статистика архивов';
}

?>
    <div class="user-index">
        <?= $this->render('_tabs', ['listType' => $listType, 'type' => $type]) ?>
    </div>

<?php
echo $this->render('_new', [
    'dataProvider' => $dataProvider,
    'searchModel'  => $searchModel,
    'authorMembers' => $authorMembers,
    'type' => $type,
]);
?>