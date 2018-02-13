<?php

namespace backend\controllers;

use common\models\Company;
use common\models\DepartmentLinking;
use common\models\DepartmentUserCompanyType;
use common\models\forms\userAddForm;
use common\models\forms\userUpdateForm;
use common\models\LoginForm;
use common\models\search\DepartmentLinkingSearch;
use common\models\search\UserSearch;
use common\models\Service;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
                        'roles' => [User::ROLE_ACCOUNT, User::ROLE_WATCHER],
                    ]
                ]
            ]
        ];
    }

    /**
     * @param $department
     * @return string|\yii\web\Response
     */
    public function actionList($department)
    {
        Url::remember();

        $searchModel = new UserSearch();
        $dataProvider = $searchModel
            ->search(Yii::$app->request->queryParams);

        $dataProvider->query
            ->joinWith('departments')
            ->andWhere(['department_id' => $department]);
        $dataProvider->pagination = false;

        $newUser = new userAddForm();
        $newUser->role = User::ROLE_WATCHER;

        if ($newUser->load(Yii::$app->request->post())) {
            $user = $newUser->saveToDepartment($department);
            if ($user) {
                $newUser->getCompanyFromDepartment($department, $user);

                return $this->redirect(['list', 'department' => $department]);
            } else {
                Yii::$app->session->addFlash('add_user_form', 'Ошибка. Пользователь не сохранен.');
            }
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'newUser' => $newUser,
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

        return $this->redirect(Yii::$app->getRequest()->referrer);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /* @var $userModel User */
        $userModel = $this->findModel($id);
        $model = new userUpdateForm();

        $model->setAttributes([
            'username' => $userModel->username,
            'role' => $userModel->role,
            'code' => $userModel->code,
            'code_pass' => $userModel->code_pass,
            'is_account' => $userModel->is_account,
            'company_id' => $userModel->company_id,
            'oldPassword' => $userModel->password_hash,
            'email' => $userModel->email,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->update()) {

            $userModel->setAttributes($model->attributes);
            if (!empty($model->password)) {
                $userModel->password_hash = md5($userModel->salt . $model->password);
            }

            if ($userModel->save()) {
                DepartmentUserCompanyType::deleteAll(['user_id' => $userModel->id]);
                foreach (Yii::$app->request->post('CompanyType', []) as $companyStatus => $companyTypeData) {
                    foreach ($companyTypeData as $companyType => $value) {
                        $modelDepartmentCompanyType = new DepartmentUserCompanyType();
                        $modelDepartmentCompanyType->user_id = $userModel->id;
                        $modelDepartmentCompanyType->company_type = $companyType;
                        $modelDepartmentCompanyType->company_status = $companyStatus;
                        $modelDepartmentCompanyType->save();
                    }
                }

                return $this->redirect(Yii::$app->getRequest()->referrer);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'userModel'=>$userModel
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

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            Yii::$app->response->cookies->add($cookieAdmin);
        } else {
            Yii::$app->response->cookies->remove($cookieAdmin);
        }

        $loginModel = new LoginForm([
            'username' => $model->username,
        ]);

        if ($loginModel->virtualLogin()) {
            /** @var User $currentUser */
            $currentUser = Yii::$app->user->identity;

            if (Yii::$app->user->isGuest) {
                return $this->redirect('/site/index');
            }
            if ($currentUser->role == User::ROLE_ADMIN) {
                return $this->redirect(['department/index']);
            }
            if ($currentUser->role == User::ROLE_ACCOUNT) {
                return $this->redirect(['order/list', 'type' => Service::TYPE_WASH]);
            }
            if ($currentUser->role == User::ROLE_WATCHER) {
                return $this->redirect(['/company/new', 'type' => Yii::$app->user->identity->getFirstCompanyType()]);
            }
            if ($currentUser->role == User::ROLE_MANAGER) {
                return $this->redirect(['/company/new', 'type' => Yii::$app->user->identity->getFirstCompanyType()]);
            }
        } else {
            return $this->goBack(); // return to list action
        }
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

    // Привязка сотрудников к компаниям
    public function actionLinking($type)
    {

        Url::remember();

        $searchModel = new DepartmentLinkingSearch();
        $searchModel->type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new DepartmentLinking();
        $model->type = $type;

        // Список сотрудников
        $authorMembers = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->select('user.username, user.id as id')->indexBy('id')->column();
        // Список сотрудников

        // Список компаний
        $arrCompany = Company::find()->where(['type' => $type])->andWhere(['OR', ['status' => 2], ['status' => 10]])->select('name')->indexBy('id')->orderBy('name')->asArray()->column();
        // Список компаний

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'model' => $model,
            'type' => $type,
            'authorMembers' => $authorMembers,
            'arrCompany' => $arrCompany,
        ]);

    }

    public function actionCreatelink($type)
    {

        $model = new DepartmentLinking();
        $model->type = $type;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Успешно
        }

        return $this->redirect(['linking', 'type' => $type]);

    }

    public function actionUpdatelink($id)
    {

        $model = DepartmentLinking::findOne($id);

        // Список сотрудников
        $authorMembers = User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->select('user.username, user.id as id')->indexBy('id')->column();
        // Список сотрудников

        // Список компаний
        $arrCompany = Company::find()->where(['type' => $model->type])->andWhere(['OR', ['status' => 2], ['status' => 10]])->select('name')->indexBy('id')->orderBy('name')->asArray()->column();
        // Список компаний

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['linking', 'type' => $model->type]);
        }

        return $this->render('updatelink', [
            'model' => $model,
            'type' => $model->type,
            'authorMembers' => $authorMembers,
            'arrCompany' => $arrCompany,
        ]);

    }

    public function actionDeletelink($id)
    {
        $modelLink = DepartmentLinking::findOne($id);
        $modelLink->delete();

        return $this->redirect(Yii::$app->getRequest()->referrer);
    }

    // Блокировка доступа сотрудникам
    public function actionCloselogin($status, $id = 0)
    {

        $currentUser = Yii::$app->user->identity;

        if(($currentUser->role == User::ROLE_ADMIN) || ($currentUser->id == 176)) {

            if($id > 0) {

                $arrRes = Yii::$app->getDb()->createCommand('SELECT user_id FROM {{%department_user}} WHERE department_id<>4 AND user_id<>176 GROUP BY user_id ORDER BY user_id')->queryAll();

                for ($i = 0; $i < count($arrRes); $i++) {
                    if($arrRes[$i]['user_id'] == $id) {
                        User::updateAll(['status' => $status], ['id' => $id]);
                    }
                }

            } else {

                $arrRes = Yii::$app->getDb()->createCommand('SELECT user_id FROM {{%department_user}} WHERE department_id<>3 AND department_id<>4 AND user_id<>176 GROUP BY user_id ORDER BY user_id')->queryAll();
                $arrIDs = [];

                for ($i = 0; $i < count($arrRes); $i++) {
                    $arrIDs[] = $arrRes[$i]['user_id'];
                }

                User::updateAll(['status' => $status], ['id' => $arrIDs]);

            }

        }

        return $this->goBack();

    }

}
