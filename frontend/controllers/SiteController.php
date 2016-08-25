<?php
namespace frontend\controllers;

use common\models\Company;
use common\models\User;
use frontend\models\SignupForm;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\LoginForm;

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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
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
