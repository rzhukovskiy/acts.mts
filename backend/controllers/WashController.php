<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;


use common\models\Company;
use common\models\CompanyService;
use common\models\Entry;
use common\models\search\CompanySearch;
use common\models\search\EntrySearch;
use common\models\User;
use yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class WashController extends Controller
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
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_ACCOUNT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Wash Company models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new CompanySearch();
        $searchModel->type = Company::TYPE_WASH;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->joinWith('acts');
        $dataProvider->sort = [
            'defaultOrder' => [
                'created_at' => SORT_DESC,
            ]
        ];

        $entrySearchModel = new EntrySearch();
        $entrySearchModel->load(Yii::$app->request->queryParams);
        if (empty($entrySearchModel->day)) {
            $entrySearchModel->day = date('d-m-Y');
        }
        $listCity = Company::find()->active()->andWhere(['type' => Company::TYPE_WASH])->groupBy('address')->select(['address', 'address'])->indexBy('address')->column();
        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'entrySearchModel' => $entrySearchModel,
            'listCity' => $listCity,
        ]);
    }

    /**
     * Shows an existing Company model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $modelEntry = new Entry();
        $modelEntry->load(Yii::$app->request->queryParams);
        $modelEntry->company_id = $model->id;
        $modelEntry->service_type = $model->type;

        $entrySearchModel = new EntrySearch();
        $entrySearchModel->day = $modelEntry->day;
        $entrySearchModel->company_id = $modelEntry->company_id;

        return $this->render('view', [
            'model' => $model,
            'modelEntry' => $modelEntry,
            'searchModel' => $entrySearchModel,
        ]);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Company the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Company::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}