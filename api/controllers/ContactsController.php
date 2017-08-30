<?php
namespace api\controllers;

use yii\rest\ActiveController;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;


class ContactsController extends ActiveController
{

    public $modelClass = 'common\models\Contact';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {

        $behaviors = parent::behaviors();
        $behaviors['corsFilter' ] = [
            'class' => \yii\filters\Cors::className(),
        ];
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['list'],
            'rules' => [
                [
                    'actions' => ['list'],
                    'allow' => true,
                    'roles' => ['?'],
                ],
                [
                    'actions' => ['list'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'list' => ['post', 'get'],
            ],
        ];

        return $behaviors;
    }

    public function actionList()
    {
        return $this->redirect("http://docs.mtransservice.ru/site/index");
    }

}
