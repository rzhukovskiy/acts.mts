<?php

namespace api\controllers;

use common\models\Company;
use common\models\LoginForm;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Cookie;

class UserController extends Controller
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
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER, User::ROLE_CLIENT, User::ROLE_WATCHER],
                    ]
                ]
            ]
        ];
    }

    public function actionList($type)
    {
        Url::remember();

        $searchModel = new UserSearch();
        $dataProvider = $searchModel
            ->search(\Yii::$app->request->queryParams);

        $dataProvider->query
            ->joinWith('company company')
            ->andWhere(['type' => $type]);
        $dataProvider->pagination = false;

        $dataProvider->query->addSelect([
            new Expression('IF(IFNULL(company.parent_id,0)=0, company.id*1000, company.parent_id*1000+company.id) as parent_key')
        ]);

        $sort = $dataProvider->getSort();
        $sort->attributes = array_merge($sort->attributes,
            [
                'parent_key' => [
                    'asc'  => ['parent_key' => SORT_ASC],
                    'desc' => ['parent_key' => SORT_DESC]
                ]
            ]);
        $dataProvider->setSort($sort);
        $dataProvider->sort->defaultOrder=['parent_key' => SORT_ASC];

        $newUser = new userAddForm();
        $newUser->role = User::ROLE_PARTNER;
        if ($type == Company::TYPE_OWNER)
            $newUser->role = User::ROLE_CLIENT;

        if ($newUser->load(Yii::$app->request->post()))
            if ($newUser->save())
                return $this->redirect(['list', 'type' => $type]); // TODO: add flash message
            else {
                // TODO: add normal message about error
                Yii::$app->session->addFlash('add_user_form', 'Ошибка. Пользователь не сохранен.');
            }

        return $this->render('list',
            [
                'searchModel'         => $searchModel,
                'dataProvider'        => $dataProvider,
                'companyDropDownData' => Company::dataDropDownList($type, false, ['id' => SORT_DESC]),
                'newUser'             => $newUser,
                'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
            ]);
    }

}
