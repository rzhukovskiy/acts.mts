<?php
namespace api\controllers;

use api\models\ApiToken;
use common\models\Company;
use common\models\User;
use Yii;
use api\models\LoginForm;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $layout = 'login';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['error', 'login', 'token', 'logout'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['error', 'login', 'token', 'logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'login' => ['post', 'get'],
                    'token' => ['post', 'get'],
                    'logout' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionError()
    {
        return $this->redirect("http://docs.mtransservice.ru/site/index");
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect("http://docs.mtransservice.ru/site/index");
    }

    // авторизация в приложении
    public function actionLogin()
    {

        if((Yii::$app->request->post("username")) && (Yii::$app->request->post("password"))) {

            $params = [];
            $params['username'] = Yii::$app->request->post("username");
            $params['password'] = Yii::$app->request->post("password");

            $params['username'] = str_replace('\"', '"', $params['username']);
            $params['username'] = str_replace("\'", "'", $params['username']);
            $params['password'] = str_replace('\"', '"', $params['password']);
            $params['password'] = str_replace("\'", "'", $params['password']);

            $model = new LoginForm();
            $model->load($params, '');

            $token = $model->auth();

            if ($token) {

                if ($token->user_id > 0) {

                    $user = User::findOne(['id' => $token->user_id]);

                    if ($user->company_id > 0) {

                        $company = Company::findOne(['id' => $user->company_id]);

                        if ($company->type) {

                            return json_encode(['error' => 0, 'token' => $token->token, 'id' => $token->user_id, 'company_id' => $user->company_id, 'company_name' => $company->name, 'type' => $company->type, 'status' => $company->status]);

                        } else {
                            // Возвращаем ошибку системы
                            return json_encode(['error' => 2]);
                        }

                    } else {
                        // Возвращаем ошибку системы
                        return json_encode(['error' => 2]);
                    }

                } else {
                    // Возвращаем ошибку системы
                    return json_encode(['error' => 2]);
                }

            } else {
                // Возвращаем ошибку неверный логин или пароль
                return json_encode(['error' => 1]);
            }

        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // проверка токена
    public function actionToken()
    {

        if(Yii::$app->request->post("token")) {

            $token = Yii::$app->request->post("token");
            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $model = ApiToken::findOne(['token' => $token]);

            if((isset($model)) && (isset($model->user_id)) && (isset($model->expired_at))) {

                $timenow = time();

                if($timenow >= $model->expired_at) {
                    // Удаление токена
                    $model->delete();
                    return json_encode(['error' => 1]);
                } else {
                    // Продляем токен на 45 дней
                    $model->expired_at = (String) (time() + 3600 * 24 * 45);
                    $model->save();

                    return json_encode(['error' => 0, 'id' => $model->user_id]);
                }

            } else {
                return json_encode(['error' => 1]);
            }


        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

    // выход
    public function actionLogout()
    {

        if((Yii::$app->request->post("token")) && (Yii::$app->request->post("id"))) {

            $token = Yii::$app->request->post("token");
            $token = str_replace('\"', '"', $token);
            $token = str_replace("\'", "'", $token);

            $id = Yii::$app->request->post("id");

            $model = ApiToken::findOne(['token' => $token, 'user_id' => $id]);

            if((isset($model)) && (isset($model->user_id)) && (isset($model->id))) {

                // Удаление токена
                $model->delete();
                return json_encode(['error' => 0]);

            } else {
                return json_encode(['error' => 1]);
            }


        } else {
            return $this->redirect("http://docs.mtransservice.ru/site/index");
        }

    }

}
