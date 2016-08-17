<?php
    use common\models\User;

    /**
     * @var $this yii\web\View
     * @var $type null|integer
     * @var $dataProvider yii\data\ActiveDataProvider
     * @var $searchModel \common\models\search\ActSearch
     * @var $model \common\models\Act
     * @var $serviceList array
     */

    $this->title = 'Акты';

    $request = Yii::$app->request;

    echo $this->render( '_tabs' );

    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
        echo $this->render( $request->get('company') ? 'client/_form' : 'partner/_form', [
            'serviceList' => $serviceList,
            'model' => $model,
        ] );        
    }

    echo $this->render( $request->get('company') ? 'client/_list' : 'partner/_list', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
    ] );

