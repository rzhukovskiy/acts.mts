<?php

namespace frontend\controllers;

use common\models\Company;
use common\models\forms\userAddForm;
use common\models\forms\userUpdateForm;
use common\models\LoginForm;
use common\models\search\UserSearch;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;

class UserController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
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
            ->joinWith('company')
            ->andWhere(['company.type' => $type]);

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

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'companyDropDownData' => Company::dataDropDownList($type),
            'newUser' => $newUser,
        ]);
    }

    /**
     * @param $id
     * @param $type
     * @return string
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id, $type)
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
                $userModel->password_hash = $model->password;

            if ($userModel->save())
                return $this->redirect(['list', 'type' => $type]);
        }

        return $this->render('update', [
            'model' => $model,
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

        // TODO: редирект на основании роли пользователя.
        // TODO: Например: клиента в расходы, партнера в раздел добавления машин
        if ($loginModel->virtualLogin()) {

            return $this->goHome();
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
