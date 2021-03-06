<?php
namespace frontend\controllers;

use common\models\Car;
use common\models\Company;
use common\models\search\CarSearch;
use common\models\Type;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

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
                        'actions' => ['list', 'view','list-full'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view','list-full'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT, User::ROLE_WATCHER],
                    ],
                    [
                        'actions' => ['list', 'view','list-full'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT, User::ROLE_WATCHER],
                    ]
                ]
            ]
        ];
    }

    public function actionList()
    {
        $searchModel = new CarSearch();
        if (!empty(Yii::$app->user->identity->company_id)) {
            $searchModel->company_id = Yii::$app->user->identity->company->id;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->with(['type'])
            ->carsCountByTypes($searchModel->company_id);

        if (Yii::$app->user->can(User::ROLE_ADMIN)) {
            $companyModels = Company::find()->active()
                ->andWhere(['type' => Company::TYPE_OWNER])
                ->active()->all();
            $companyDropDownData = ArrayHelper::map($companyModels, 'id', 'name');
        } else {
            $companyModels = Company::find()->active()
                ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
                ->active()->all();
            $companyDropDownData = ArrayHelper::map($companyModels, 'id', 'name');
        }

        return $this->render('list', [
            'searchModel' => $searchModel,
            'carByTypes' => $dataProvider,
            'companyId' => $searchModel->company_id,
            'companyDropDownData' => $companyDropDownData,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }


    /**
     * Lists all Car models.
     * @return mixed
     */
    public function actionListFull()
    {
        $searchModel = new CarSearch();
        $searchModel->company_id=Yii::$app->user->identity->company->id;

        $dataProvider = $searchModel->search([]);
        $dataProvider->query->orderBy(['type_id'=>SORT_ASC]);
        return $this->render('list-full', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $type
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($type)
    {
        $typeModel = $this->findModel($type);

        $searchModel = new CarSearch();
        if (!empty(Yii::$app->user->identity->company_id)) {
            $searchModel->company_id = Yii::$app->user->identity->company->id;
        }
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->byType($type)
            ->with(['mark', 'type', 'company']);

        $dataProvider->pagination = false;

        $companyModels = Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->active()->all();
        $companyDropDownData = ArrayHelper::map($companyModels, 'id', 'name');

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'typeModel' => $typeModel,
            'companyDropDownData' => $companyDropDownData,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    /**
     * @param $id
     * @return null|static
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        if (($model = Type::findOne($id)) == null)
            throw new NotFoundHttpException('Page not found!');

        return $model;
    }
}