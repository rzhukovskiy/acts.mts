<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\MonthlyAct;
use common\models\search\MonthlyActSearch;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class MonthlyActController extends Controller
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
                        'actions' => ['archive', 'refuse', 'create', 'update', 'delete'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
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
        if (($model = MonthlyAct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionList($type, $company = 0)
    {
        $searchModel = new MonthlyActSearch();
        $searchModel->type_id = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $role = Yii::$app->user->identity->role;

        return $this->render('list',
        [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
            'type'         => $type,
            'company'      => $company,
            'role'         => $role,
        ]);
    }
}