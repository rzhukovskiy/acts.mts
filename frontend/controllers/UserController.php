<?php

namespace frontend\controllers;

use common\models\Company;
use common\models\forms\userAddForm;
use common\models\forms\userUpdateForm;
use common\models\LoginForm;
use common\models\search\UserSearch;
use common\models\User;
use yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;

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
                        'actions' => ['list','login'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => [User::ROLE_PARTNER, User::ROLE_CLIENT, User::ROLE_WATCHER],
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $type
     * @return string|\yii\web\Response
     */
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

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return 'Delete plz';
    }

    /**
     * @param $type
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($type, $id)
    {
        /* @var $userModel User */
        $userModel = $this->findModel($id);
        $model = new userUpdateForm();

        $model->setAttributes([
            'username' => $userModel->username,
            'role' => $userModel->role,
            'company_id' => $userModel->company_id,
            'oldPassword' => $userModel->password_hash,
            'email' => $userModel->email,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->update()) {

            $userModel->setAttributes($model->attributes);
            if (!empty($model->password))
                $userModel->password_hash = md5($userModel->salt . $model->password);

            if ($userModel->save())
                return $this->redirect(['list', 'type' => $type]);
        }

        return $this->render('update', [
            'model' => $model,
            'type' => $type,
            'companyDropDownData' => Company::dataDropDownList($type),
        ]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionLogin($id)
    {
        $this->enableCsrfValidation = false;

        $model = $this->findModel($id);

        $cookieAdmin = new Cookie([
            'name' => 'isAdmin',
            'value' => '1'
        ]);

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN)
            Yii::$app->response->cookies->add($cookieAdmin);
        elseif ($model->role == User::ROLE_ADMIN)
            Yii::$app->response->cookies->remove($cookieAdmin);

        $loginModel = new LoginForm([
            'username' => $model->username,
        ]);

        if ($loginModel->virtualLogin()) {
            if (Yii::$app->user->can(User::ROLE_ADMIN)) {
                return $this->redirect(['company/list', 'type' => Company::TYPE_OWNER]);
            }
            if (Yii::$app->user->can(User::ROLE_CLIENT)) {
                return $this->redirect(['act/list', 'type' => Company::TYPE_WASH, 'company' => true]);
            }
            if (Yii::$app->user->can(User::ROLE_PARTNER)) {
                /** @var Company $company */
                $company = Yii::$app->user->identity->company;
                if ($company->type == Company::TYPE_UNIVERSAL) {
                    return $this->redirect(['act/create', 'type' => $company->serviceTypes[0]->type]);
                } else {
                    return $this->redirect(['act/create', 'type' => $company->type]);
                }
            }
        } else
            return $this->goBack(); // return to list action
    }

    /**
     * @param $id
     * @return null|User
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null)
            return $model;

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
