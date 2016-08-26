<?php
namespace frontend\controllers;

use common\models\Company;
use common\models\User;
use frontend\models\SignupForm;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\base\Exception;
use yii\base\UserException;

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
                        'actions' => ['index', 'error', 'signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'error'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionError()
    {
        $this->layout = 'main';

        if (($exception = Yii::$app->getErrorHandler()->exception) === null) {
            // action has been invoked not from error handler, but by direct route, so we display '404 Not Found'
            $exception = new HttpException(404, Yii::t('yii', 'Page not found.'));
        }

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }
        if ($exception instanceof Exception) {
            $name = $exception->getName();
        } else {
            $name = $this->defaultName ?: Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($exception instanceof UserException) {
            $message = $exception->getMessage();
        } else {
            $message = $this->defaultMessage ?: Yii::t('yii', 'An internal server error occurred.');
        }

        if (Yii::$app->getRequest()->getIsAjax()) {
            return "$name: $message";
        } elseif($message == 'Page not found.' || $code == 403 || $code == 404) {
            $exception->statusCode = 302;
            return $this->goHome();
        } else {
            return $this->render('error', [
                'name' => $name,
                'message' => $message,
                'exception' => $exception,
            ]);
        }
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('index', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }


    public function goHome()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('/site/index');
        }
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
    }
}
