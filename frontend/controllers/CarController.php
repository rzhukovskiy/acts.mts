<?php

namespace frontend\controllers;

use common\models\Car;
use common\models\search\CarSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * CarController implements the CRUD actions for Car model.
 */
class CarController extends Controller
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
        ];
    }

    public function actionDirty()
    {
        return $this->render('dirty');
    }

    /**
     * Lists all Car models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new CarSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query
            ->with([
                'company' => function ($query) {
                    $query->select('id, name');
                },
                'type' => function ($query) {
                    $query->select('id, name');
                },
                'mark' => function ($query) {
                    $query->select('id, name');
                }
            ]);
        $dataProvider->pagination = [
            'pageSize' => 300,
        ];
        $dataProvider->sort = false;

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpload()
    {
        return $this->render('upload');
    }

    /**
     * Displays a single Car model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Car model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Car the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Car::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
