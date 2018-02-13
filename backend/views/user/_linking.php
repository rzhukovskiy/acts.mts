<?php
use yii\grid\GridView;
use yii\helpers\Html;
use common\models\DepartmentLinking;

/**
 * @var $this yii\web\View
 * @var $searchModel common\models\search\UserSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $companyDropDownData array
 * @var $newUser \common\models\User
 */

$GLOBALS['authorMembers'] = $authorMembers;
$GLOBALS['arrCompany'] = $arrCompany;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'summary' => false,
    'emptyText' => '',
    'columns' => [
        [
            'header' => '№',
            'class' => 'yii\grid\SerialColumn'
        ],
        [
            'attribute' => 'user_id',
            'filter' => Html::activeDropDownList($searchModel, 'user_id', DepartmentLinking::find()->innerJoin('user', '`user`.`id` = `department_linking`.`user_id`')->where(['department_linking.type' => Yii::$app->request->get('type')])->select('user.username')->indexBy('user_id')->column(), ['class' => 'form-control', 'prompt' => 'Все сотрудники']),
            'value' => function ($data) {
                return isset($GLOBALS['authorMembers'][$data->user_id]) ? $GLOBALS['authorMembers'][$data->user_id] : '-';
            },
        ],
        [
            'attribute' => 'company_id',
            'filter' => Html::activeDropDownList($searchModel, 'company_id', DepartmentLinking::find()->innerJoin('company', '`company`.`id` = `department_linking`.`company_id`')->where(['department_linking.type' => Yii::$app->request->get('type')])->select('company.name')->indexBy('company_id')->column(), ['class' => 'form-control', 'prompt' => 'Все компании']),
            'value' => function ($data) {
                return isset($GLOBALS['arrCompany'][$data->company_id]) ? $GLOBALS['arrCompany'][$data->company_id] : '-';
            },
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update}{delete}',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['/user/updatelink', 'id' => $model->id]);
                },
                'delete' => function ($url, $model, $key) {
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/user/deletelink', 'id' => $model->id], [
                        'data-confirm' => "Вы уверены, что хотите удалить привязку?"
                    ]);
                },
            ]
        ],
    ],
]);

?>