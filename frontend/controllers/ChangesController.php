<?php

namespace frontend\controllers;

use common\models\Changes;
use common\models\Company;
use common\models\search\ChangesSearch;
use common\models\Service;
use common\models\Type;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class ChangesController extends Controller
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
                        'actions' => ['card', 'price'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    public function actionCard()
    {
        $searchModel = new ChangesSearch();
        $searchModel->type = Changes::TYPE_CARD;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Список сотрудников
        $workUserData = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->select('user.id, user.username')->asArray()->all();
        $authorMembers = [];

        foreach ($workUserData as $name => $value) {
            $index = $value['id'];
            $authorMembers[$index] = trim($value['username']);
        }
        // Список сотрудников

        // Список компаний
        $arrTypes = Company::find()->where(['type' => Company::TYPE_OWNER])->select('name')->indexBy('id')->asArray()->column();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'authorMembers' => $authorMembers,
            'arrTypes' => $arrTypes,
            'serviceList' => [],
            'type' => 0,
        ]);
    }

    public function actionPrice($type)
    {
        $searchModel = new ChangesSearch();
        $searchModel->type = Changes::TYPE_PRICE;
        $searchModel->sub_type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Список сотрудников
        $workUserData = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->select('user.id, user.username')->asArray()->all();
        $authorMembers = [];

        foreach ($workUserData as $name => $value) {
            $index = $value['id'];
            $authorMembers[$index] = trim($value['username']);
        }
        // Список сотрудников

        // Список типов ТС
        $arrTypes = Type::find()->asArray()->select('name')->indexBy('id')->column();

        // Список типов ТС
        $serviceList = Service::find()->asArray()->select('description')->indexBy('id')->column();

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'authorMembers' => $authorMembers,
            'arrTypes' => $arrTypes,
            'serviceList' => $serviceList,
            'type' => $type,
        ]);
    }

}
