<?php
    namespace frontend\controllers;

    use common\models\Car;
    use common\models\Type;
    use Yii;
    use yii\data\ActiveDataProvider;
    use yii\web\Controller;

    /**
     * Created by PhpStorm.
     * User: ruslanzh
     * Date: 05/08/16
     * Time: 10:14
     */
    class CarCountController extends Controller
    {
        public function actionList()
        {
            $companyId = null;

            $query = Car::find()
                ->carsCountByTypes( $companyId );

            $carByTypes = new ActiveDataProvider( [
                'query' => $query,
                'sort' => false,
                'pagination' => false,
            ] );

            return $this->render( 'list', [
                'carByTypes' => $carByTypes,
                'companyId' => $companyId,
            ] );
        }

        public function actionView( $type )
        {
            $query = Car::find()
                ->with(['mark', 'type'])
                ->byType($type);

            $provider = new ActiveDataProvider([
                'query' => $query,
                'sort' => false,
                'pagination' => false
            ]);

            $typeModel = Type::findOne($type);

            return $this->render( 'view', [
                'provider' => $provider,
                'typeModel' => $typeModel,
            ] );
        }
    }