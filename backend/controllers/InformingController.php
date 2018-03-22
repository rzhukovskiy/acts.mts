<?php
namespace backend\controllers;

use common\models\Informing;
use common\models\InformingUsers;
use common\models\search\InformingSearch;
use common\models\User;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii;
use yii\web\Response;

/**
 * Informing Controller
 */
class InformingController extends Controller
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
                        'actions' => ['create', 'isarchive', 'update', 'list', 'change-status', 'fulltext', 'fullusers'],
                        'allow' => true,
                        'roles' => [User::ROLE_ACCOUNT, User::ROLE_WATCHER, User::ROLE_MANAGER, User::ROLE_ADMIN],
                    ],
                ],
            ],
        ];
    }

    public function actionCreate()
    {

        $userListsID = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER], ['!=', 'id', Yii::$app->user->identity->id], ['!=', 'status', 0]])->select('id, username')->indexby('id')->asArray()->all();

        $model = new Informing();
        $model->from_user = Yii::$app->user->identity->id;
        $model->date_create = (String) time();

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

           foreach ($userListsID as $key => $value) {
               $newmodel = new InformingUsers();
               $newmodel->informing_id = $model->id;
               $newmodel->user_id = $key;
               $newmodel->save();
          }

           return $this->redirect(['/informing/list', 'type' => 1]);

        } else {
            return $this->render('/informing/create', [
                'model' => $model,
            ]);
        }

    }

    public function actionList($type)
    {
        $searchModel = new InformingSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // Активные
        if ($type == 1) {
            $dataProvider->query->andWhere(['is_archive' => 0]);

            // перенос в архив
            $informingAllCount = InformingUsers::find()->innerJoin('informing', 'informing.id = informing_users.informing_id')->andwhere(['informing.is_archive' => 0])->select('informing_users.informing_id, COUNT(informing_users.informing_id) as count')->groupBy('informing_users.informing_id')->asArray()->all();
            $informingCount = InformingUsers::find()->innerJoin('informing', 'informing.id = informing_users.informing_id')->andwhere(['informing.is_archive' => 0])->andWhere(['informing_users.status' => 1])->select('informing_users.informing_id, COUNT(informing_users.informing_id) as count')->groupBy('informing_users.informing_id')->asArray()->all();

            $count = false;

            for ($i = 0; $i < count($informingAllCount); $i++) {

                for ($j = 0; $j < count($informingCount); $j++) {
                    if (($informingAllCount[$i]['informing_id'] == $informingCount[$j]['informing_id']) && ($informingAllCount[$i]['count'] == $informingCount[$j]['count'])) {
                        $model = Informing::findOne(['id' => $informingAllCount[$i]['informing_id']]);
                        $model->is_archive = 1;
                        $model->save();
                        $count = true;
                    }
                }
            }

            if($count) {
                return $this->redirect(['/informing/list', 'type' => 1]);
            }
            // Архив
        } else if ($type == 2) {
            $dataProvider->query->andWhere(['is_archive' => 1]);
        }


        $userLists = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER]])->select('username')->indexby('id')->column();
        $informingUsers = InformingUsers::find()->innerJoin('informing', 'informing.id = informing_users.informing_id')->andwhere(['informing.is_archive' => 0])->asArray()->all();

        $allCount = InformingUsers::find()->select('COUNT(informing_id) as count')->groupBy('informing_id')->indexBy('informing_id')->column();
        $allCountAgree = InformingUsers::find()->where(['status' => 1])->select('COUNT(informing_id) as count')->groupBy('informing_id')->indexBy('informing_id')->column();

        return $this->render('/informing/list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'userLists' => $userLists,
            'informingUsers' => $informingUsers,
            'type' => $type,
            'allCount' => $allCount,
            'allCountAgree' => $allCountAgree,
        ]);
    }

    public function actionChangeStatus()
    {
        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $model = InformingUsers::findOne(['informing_id' => $id, 'user_id' => Yii::$app->user->identity->id]);

            if ($model->status == 0) {
                $model->status = 1;
            } else {
                $model->status = 0;
            }

            if ($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            return $this->redirect(['/']);
        }
    }

    public function actionIsarchive()
    {

        if (Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $model = Informing::findOne(['id' => $id]);
            $model->is_archive = 1;

            if ($model->save()) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            return $this->redirect(['/']);
        }

    }

    public function actionFulltext()
    {

        if (Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $model = Informing::findOne(['id' => $id]);

            if ($model->text) {
                $resText = nl2br($model->text);
                echo json_encode(['result' => json_encode($resText), 'success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            return $this->redirect(['/']);
        }

    }

    public function actionFullusers()
    {

        if (Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $usersList = InformingUsers::find()->innerJoin('user', 'user.id = informing_users.user_id')->where(['informing_users.informing_id' => $id])->select('user.username, informing_users.status')->asArray()->all();
            $resText = '';
            $agree = '';
            $nameUser = '';

            if (isset($usersList)) {
                for ($i = 0; $i < count($usersList); $i++) {
                    $nameUser = $usersList[$i]['username'];
                    if ($usersList[$i]['status'] == 1) {
                        $agree = 'Ознакомлен';
                    } else {
                        $agree = 'Не ознакомлен';
                    }

                    $resText .= '<tr><td>' . $nameUser . '</td><td>&nbsp - &nbsp</td><td>' . $agree . '</td></tr>';

                }
                echo json_encode(['result' => json_encode($resText), 'success' => 'true']);

            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            return $this->redirect(['/']);
        }

    }

    public function actionUpdate($id)
    {
        $model = Informing::findOne(['id' => $id]);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            if ($model->load($arrUpdate) && $model->save()) {
                $output = [];
                return ['output' => implode(', ', $output), 'message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        } else {
            return ['message' => 'не получилось'];
        }
    }

}
