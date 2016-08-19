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
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT, User::ROLE_PARTNER, User::ROLE_WATCHER],
                    ]
                ]
            ]
        ];
    }

    public function actionList()
    {
        $companyId = null;
        if (!empty(Yii::$app->user->identity->company_id)) {
            $companyId = Yii::$app->user->identity->company_id;
        }
        $query = Car::find()
            ->with(['type'])
            ->carsCountByTypes($companyId);

        $carByTypes = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render('list', [
            'carByTypes' => $carByTypes,
            'companyId' => $companyId,
        ]);
    }

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
            ->with(['mark', 'type']);

        if (!empty($company))
            $dataProvider->query
                ->andWhere(['company_id' => $company]);

        $dataProvider->pagination = false;

        // TODO: need to refactor this shit
        $carModels = $dataProvider->getModels();
        $companyIds = ArrayHelper::getColumn($carModels, 'company_id');
        $companyIds = array_unique($companyIds);
        $companyModels = Company::find()
            ->andWhere(['in', 'id', $companyIds])
            ->all();
        $companyDropDownData = ArrayHelper::map($companyModels, 'id', 'name');

        return $this->render('view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'typeModel' => $typeModel,
            'companyDropDownData' => $companyDropDownData,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = Type::findOne($id)) == null)
            throw new NotFoundHttpException('Page not found!');

        return $model;
    }
}