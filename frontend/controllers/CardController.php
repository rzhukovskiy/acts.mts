<?php

namespace frontend\controllers;

use common\models\Card;
use common\models\Company;
use common\models\User;
use frontend\models\search\CardSearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class CardController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['list', 'create', 'update', 'delete', 'diapason'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_WATCHER, User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_CLIENT],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Card models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new CardSearch();
        if (!Yii::$app->user->can(User::ROLE_ADMIN)) {
            $searchModel->company_id = Yii::$app->user->identity->company->id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        if (!Yii::$app->user->can(User::ROLE_ADMIN) && !Yii::$app->user->can(User::ROLE_WATCHER)) {
            $dataProvider->query->groupBy(['company_id']);
        }
        $dataProvider = CardSearch::addCarToSearch($dataProvider);
        $companyDropDownData = Company::dataDropDownList();

        return $this->render('list',
        [
            'model'               => new Card(),
            'searchModel'         => $searchModel,
            'dataProvider'        => $dataProvider,
            'companyDropDownData' => $companyDropDownData,
            'admin'               => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    /**
     * Lists all Card models.
     * @return mixed
     */
    public function actionDiapason()
    {
        $dataProvider = new ArrayDataProvider([
            'allModels'  => Card::getDiapason(),
            'sort'       => [
                'attributes' => ['type', 'val', 'company_name'],
            ],
            'pagination' => false,
        ]);

        return $this->render('diapason',
            [
                'dataProvider' => $dataProvider,
            ]);
    }

    /**
     * Creates a new Card model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Card();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['list']);
        } else {
            return $this->redirect(['list']);
        }
    }

    /**
     * Updates an existing Card model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $companyDropDownData = Company::dataDropDownList();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->goBack();
        } else {
            return $this->render('update',
            [
                'model'               => $model,
                'companyDropDownData' => $companyDropDownData,
            ]);
        }
    }

    /**
     * Deletes an existing Card model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->getRequest()->referrer);
    }

    /**
     * Finds the Card model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Card the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Card::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
