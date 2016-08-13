<?php
namespace frontend\controllers;

use common\models\Car;
use common\models\Type;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Created by PhpStorm.
 * User: ruslanzh
 * Date: 05/08/16
 * Time: 10:14
 */
class CarCountController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT, User::ROLE_PARTNER, User::ROLE_WATCHER],
                    ]
                ]
            ]
        ];
    }

    public function actionList()
    {
        $companyId = null;
        if (!empty(Yii::$app->user->identity->company_id)) {
            $companyId = Yii::$app->user->identity->company_id;
        }
        $query = Car::find()
            ->with(['type'])
            ->carsCountByTypes($companyId);

        $carByTypes = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('list', [
            'carByTypes' => $carByTypes,
            'companyId' => $companyId,
        ]);
    }

    public function actionView($type)
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

        return $this->render('view', [
            'provider' => $provider,
            'typeModel' => $typeModel,
        ]);
    }
}