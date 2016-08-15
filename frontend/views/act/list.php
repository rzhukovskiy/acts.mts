<?php
    use common\models\User;

    /**
     * @var $this yii\web\View
     * @var $type null|integer
     * @var $dataProvider yii\data\ActiveDataProvider
     * @var $model \common\models\Act
     */

    $request = Yii::$app->request;

    echo $this->render( '_tabs' );

    if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
        echo $this->render( $request->get('company') ? 'client/_form' : 'partner/_form', [
            'model' => $model,
        ] );        
    }

    echo $this->render( $request->get('company') ? 'client/_list' : 'partner/_list', [
        'dataProvider' => $dataProvider,
    ] );

