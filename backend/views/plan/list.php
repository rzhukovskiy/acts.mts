<?php

/**
 * @var $this yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel  common\models\search\PlanSearch
 * @var $userId  integer
 * @var $allUser  common\models\User[]
 * @var $admin boolean
 */
$script = <<< JS
    $('.change-status').change(function(){
     var select=$(this);
        $.ajax({
            url: "/plan/update?id="+$(this).data('id'),
            type: "post",
            data: {status:$(this).val(),userId:$(this).data('user-id')},
            success: function(data){
                window.location.reload();
            }
        });
    });
JS;
$this->registerJs($script, \yii\web\View::POS_READY);

echo $this->render('_tabs',
    [
        'allUser' => $allUser,
        'userId'  => $userId,
    ]);

if ($admin) {
    echo $this->render('_form',
        [
            'model'       => $model,
            'searchModel' => $searchModel,
        ]);
}
echo $this->render('_list',
    [
        'dataProvider' => $dataProvider,
        'searchModel'  => $searchModel,
        'admin'        => $admin
    ]);