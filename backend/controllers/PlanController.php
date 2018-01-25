<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\MonthlyAct;
use common\models\Plan;
use common\models\search\PlanSearch;
use common\models\search\TaskUserLinkSearch;
use common\models\search\TaskUserSearch;
use common\models\TaskMy;
use common\models\TaskUser;
use common\models\TaskUserLink;
use common\models\User;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use yii\base\DynamicModel;
use yii\web\Response;

class PlanController extends Controller
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
                        'actions' => ['list', 'create', 'update', 'delete', 'tasklist', 'taskmylist', 'taskadd', 'taskmyadd', 'taskupdate', 'taskmyupdate', 'taskfull', 'taskmyfull', 'ajaxexecutionstatus', 'taskmystatus', 'taskdelete', 'taskmydelete', 'taskmyattach', 'newtendattach', 'getcomments', 'isarchive', 'taskmypriority', 'taskpriority'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'create', 'update', 'tasklist', 'taskmylist', 'taskadd', 'taskmyadd', 'taskupdate', 'taskmyupdate', 'taskfull', 'taskmyfull', 'ajaxexecutionstatus', 'taskmystatus', 'taskdelete', 'taskmydelete', 'taskmyattach', 'newtendattach', 'getcomments', 'isarchive', 'taskmypriority', 'taskpriority'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER, User::ROLE_ACCOUNT, User::ROLE_MANAGER],
                    ]
                ],
            ],
        ];
    }

    /**
     * @param bool $userId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionList($userId = false)
    {
        /**
         * @var $allUser  \common\models\User[]
         */
        $realUser = User::findOne(Yii::$app->user->id);
        if ($realUser->role == User::ROLE_ADMIN) {
            $allUser =
                User::find()
                    ->innerJoinWith('departments')
                    ->where(['<>', '{{%user}}.role', User::ROLE_ADMIN])
                    ->indexBy('id')
                    ->all();
        } else {
            $allUser = [$realUser];
        }

        if (!$allUser) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        if ($userId && isset($allUser[$userId])) {
            $user = $allUser[$userId];
        } else {
            $user = array_shift($allUser);
            array_unshift($allUser, $user);
        }

        $userId = $user->id;
        $this->view->title = 'Планы сотрудника ' . $user->username;

        $searchModel = new PlanSearch();
        $searchModel->user_id = $user->id;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $model = new Plan([
            'user_id' => $user->id,
            'status' => Plan::STATUS_NOT_DONE,
        ]);

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'userId' => $userId,
                'allUser' => $allUser,
                'admin' => $realUser->role == User::ROLE_ADMIN,
                'model' => $model
            ]);
    }

    /**
     * @return yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Plan();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['list', 'userId' => $model->user_id]);
        } else {
            return $this->redirect(['list', 'userId' => $model->user_id]);
        }
    }


    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                return ['message' => ''];
            } else {
                return ['message' => 'не получилось'];
            }
        }
        $status = Yii::$app->request->post('status', false);
        $userId = Yii::$app->request->post('userId', false);
        if ($status) {
            $model->status = $status;
            $model->save();

            return $this->redirect(['list', 'userId' => $userId]);
        }

        return false;

    }

    /**
     * Deletes an existing Act model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionTasklist($type = 0)
    {
        $searchModel = new TaskUserSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $userList = User::find()->select('username')->indexby('id')->column();

        if ($type == 1) {
            $dataProvider->sort = [
                'defaultOrder' => [
                    'priority' => SORT_DESC,
                    'data' => SORT_ASC,
                ]
            ];
           $dataProvider->query->andWhere(['from_user' => Yii::$app->user->identity->id])->andWhere(['is_archive' => 0]);
        } else if ($type == 2) {

            $dataProvider->sort = [
                'defaultOrder' => [
                    'priority' => SORT_DESC,
                    'data' => SORT_ASC,
                ]
            ];
            if ($searchModel->from_user) {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->andWhere(['from_user' => $searchModel->from_user])->andWhere(['is_archive' => 0]);
            } else if ($searchModel->for_user) {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->andWhere(['for_user' => $searchModel->for_user])->andWhere(['is_archive' => 0]);
            } else if (isset($searchModel->status)) {
                if ($searchModel->status || $searchModel->status == '0') {
                    $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->andWhere(['status' => $searchModel->status])->andWhere(['is_archive' => 0]);
                } else {
                    $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->andWhere(['is_archive' => 0]);
                }
            } else {
            $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['OR', ['task_user_link.for_user_copy' => Yii::$app->user->identity->id], ['task_user.for_user' => Yii::$app->user->identity->id]])->andWhere(['!=', 'from_user', Yii::$app->user->identity->id])->andWhere(['is_archive' => 0]);
            }

        } else if (($type == 3) && (Yii::$app->user->identity->role != User::ROLE_ADMIN)) {

            if ($searchModel->from_user) {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andWhere(['from_user' => $searchModel->from_user])->andWhere(['is_archive' => 1]);
            } else if ($searchModel->for_user) {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andWhere(['for_user' => $searchModel->for_user])->andWhere(['is_archive' => 1]);
            } else if (isset($searchModel->status)) {
                if ($searchModel->status || $searchModel->status == '0') {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andWhere(['status' => $searchModel->status])->andWhere(['is_archive' => 1]);
                } else {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andWhere(['is_archive' => 1]);
                }
            } else {
                $dataProvider->query->leftJoin('task_user_link', '`task_user_link`.`task_id` = `task_user`.`id`')->andWhere(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->orWhere(['AND', ['!=', 'from_user', Yii::$app->user->identity->id], ['for_user' => Yii::$app->user->identity->id]])->orWhere(['AND', ['from_user' => Yii::$app->user->identity->id], ['!=', 'for_user', Yii::$app->user->identity->id]])->andWhere(['is_archive' => 1]);
            }

        } else if (($type == 3) && (Yii::$app->user->identity->role == User::ROLE_ADMIN)) {
           $dataProvider->query->andWhere(['is_archive' => 1]);
        } else if ($type == 0) {
            $dataProvider->query->andWhere(['is_archive' => 0]);
        } else if ((($type != 3) && ($type != 2) && ($type != 1)) && ($type == '0' && ((Yii::$app->user->identity->role != User::ROLE_ADMIN) && (Yii::$app->user->identity->id != 176)))) {
            return $this->redirect(['plan/tasklist?type=1']);
        }

        return $this->render('task/tasklist', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'userList' => $userList,
        ]);
    }

    public function actionTaskmylist()
    {
        $searchModel = TaskMy::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'priority' => SORT_DESC,
                'data' => SORT_ASC,
            ]
        ];

        $dataProvider->query->where(['from_user' => Yii::$app->user->identity->id]);

        return $this->render('task/taskmylist', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    public function actionTaskadd()
    {
       // $userLists = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER]])->select('username')->indexby('id')->column();
        $userListsID = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER], ['!=', 'id', Yii::$app->user->identity->id]])->select('username')->indexby('id')->column();

        $newmodellink = new TaskUserLink();
        $model = new TaskUser();
        $model->from_user = Yii::$app->user->identity->id;

        $arrUpdate = Yii::$app->request->post();
        // Проверка на существование копии пользователей и удаление ее из массива
        if (isset($arrUpdate['TaskUserLink']['for_user_copy'])) {
            $arrUserIdCopy = $arrUpdate['TaskUserLink']['for_user_copy'];
            unset($arrUpdate['TaskUserLink']['for_user_copy']);
        }
        //Конец Проверка на существование копии пользователей и удаление ее из массива

        if (($model->load($arrUpdate)) && ($model->save()) && (Yii::$app->request->isPost)) {

            // Добавление в другую таблицу пользователей
            if (isset($arrUserIdCopy)) {
                if ($arrUserIdCopy) {
                    if (count($arrUserIdCopy) > 0) {
                        for ($i = 0; $i < count($arrUserIdCopy); $i++) {
                            $newmodellink = new TaskUserLink();
                            $newmodellink->task_id = $model->id;
                            $newmodellink->for_user_copy = $arrUserIdCopy[$i];
                            $newmodellink->save();
                        }
                    }
                }
            }
            // Добавление в другую таблицу пользователей

            $model->files = UploadedFile::getInstances($model, 'files');

            if ($model->files) {

                if ($model->upload()) {
                    // file is uploaded successfully
                }
            }

            return $this->redirect(['plan/tasklist?type=1']);

        } else {
            return $this->render('task/taskadd', [
                'model' => $model,
                'newmodellink' => $newmodellink,
                'userListsID' => $userListsID,
            ]);
        }

    }

    public function actionTaskmyadd()
    {
        $model = new TaskMy();
        $model->from_user = Yii::$app->user->identity->id;

        $arrUpdate = Yii::$app->request->post();

        if (($model->load($arrUpdate)) && ($model->save()) && (Yii::$app->request->isPost)) {

            $model->files = UploadedFile::getInstances($model, 'files');

            if ($model->files) {

                if ($model->upload()) {
                    // file is uploaded successfully
                }
            }

            return $this->redirect(['plan/taskmylist']);

        } else {
            return $this->render('task/taskmyadd', [
                'model' => $model,
            ]);
        }

    }

    public function actionTaskfull($id)
    {
        $model = TaskUser::findOne(['id' => $id]);
        $newmodel = new TaskUserLink();

        $userLists = User::find()->select('username')->indexby('id')->column();
        $userListsAll = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER]])->select('username')->indexby('id')->column();
        $userListsData = User::find()->where(['AND', ['!=', 'role', User::ROLE_CLIENT], ['!=', 'role', User::ROLE_PARTNER], ['!=', 'id', Yii::$app->user->identity->id]])->select('username')->indexby('id')->column();
        return $this->render('task/taskfull', [
            'model' => $model,
            'userLists' => $userLists,
            'newmodel' => $newmodel,
            'userListsData' => $userListsData,
            'userListsAll' => $userListsAll,
        ]);
    }

    public function actionTaskmyfull($id)
    {
        $model = TaskMy::findOne(['id' => $id]);

        return $this->render('task/taskmyfull', [
            'model' => $model,
        ]);
    }

    public function actionTaskupdate($id)
    {
        $model = TaskUser::findOne(['id' => $id]);
        $userLists = User::find()->select('username')->indexby('id')->column();

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();
            // Проверка на существование копии пользователей и удаление и создание новых
            if (isset($arrUpdate['TaskUserLink']['for_user_copy'])) {
                $arrUserIdCopy = $arrUpdate['TaskUserLink']['for_user_copy'];
                if (count($arrUserIdCopy) > 0) {
                    if (TaskUserLink::find()->where(['task_id' => $id])->exists()) {
                        Yii::$app->db->createCommand()->delete('{{%task_user_link}}', ['task_id' => $id])->execute();
                    }
                    for ($i = 0; $i < count($arrUserIdCopy); $i++) {
                        $newmodellink = new TaskUserLink();
                        $newmodellink->task_id = $id;
                        $newmodellink->for_user_copy = $arrUserIdCopy[$i];
                        $newmodellink->save();
                    }
                }
            }
            //Конец Проверка на существование копии пользователей и удаление и создание новых

            if (isset($arrUpdate['TaskUser']['data'])) {
                foreach ($arrUpdate['TaskUser'] as $name => $value) {
                    if ($name == 'data') {
                        $arrUpdate['TaskUser'][$name] = (String) strtotime($value);
                    }
                }
            }
            //Конец Подготовка данных перед сохранением

            // Вывод после сохранения без перезагрузки
            $output = [];

            if ($model->load($arrUpdate) && $model->save()) {

                if (isset($arrUpdate['TaskUser']['for_user'])) {
                    foreach (Yii::$app->request->post('TaskUser') as $name => $value) {
                        if ($name == 'for_user') {
                            $output[] = $userLists[$value];

                        } else {
                            $output[] = $value;
                        }
                    }
                }

                if (isset($arrUpdate['TaskUser']['from_user'])) {
                    foreach (Yii::$app->request->post('TaskUser') as $name => $value) {
                        if ($name == 'from_user') {
                            $output[] = $userLists[$value];

                        } else {
                            $output[] = $value;
                        }
                    }
                }

            }

            if (isset($arrUpdate['TaskUserLink']['for_user_copy'])) {

                foreach ($arrUpdate['TaskUserLink'] as $name => $value) {

                    if ($name == 'for_user_copy') {

                        $userText = '';
                        for ($i = 0; $i < count($value); $i++) {
                            $userText .= $userLists[$value[$i]] . '<br />';
                        }

                        $output[] = $userText;
                    } else {
                        $output[] = $value;
                    }
                }
            }

            return ['output' => implode(', ', $output), 'message' => ''];

        } else {
            return ['message' => 'не получилось'];
        }
        //Конец Вывод после сохранения без перезагрузки
    }

    public function actionTaskmyupdate($id)
    {
        $model = TaskMy::findOne(['id' => $id]);

        $hasEditable = Yii::$app->request->post('hasEditable', false);
        if ($hasEditable) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            // Подготовка данных перед сохранением
            $arrUpdate = Yii::$app->request->post();

            if (isset($arrUpdate['TaskMy']['data'])) {
                foreach ($arrUpdate['TaskMy'] as $name => $value) {
                    if ($name == 'data') {
                        $arrUpdate['TaskMy'][$name] = (String)strtotime($value);
                    }
                }
            }
            //Конец Подготовка данных перед сохранением

            // Вывод после сохранения без перезагрузки
            $output = [];

            if ($model->load($arrUpdate) && $model->save()) {


                return ['output' => implode(', ', $output), 'message' => ''];

            } else {
                return ['message' => 'не получилось'];
            }
            //Конец Вывод после сохранения без перезагрузки
        }
    }

    public function actionTaskdelete($id)
    {
        TaskUser::findOne(['id' => $id])->delete();

        if (TaskUserLink::find()->where(['task_id' => $id])->exists()) {
        TaskUserLink::findOne(['task_id' => $id])->delete();
        }
        // Удаляем
        Yii::$app->db->createCommand()->delete('{{%task_user}}', ['id' => $id])->execute();

        if (TaskUserLink::find()->where(['task_id' => $id])->exists()) {
        Yii::$app->db->createCommand()->delete('{{%task_user_link}}', ['task_id' => $id])->execute();
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionTaskmydelete($id)
    {
        TaskMy::findOne(['id' => $id])->delete();

        // Удаляем
        Yii::$app->db->createCommand()->delete('{{%task_my}}', ['id' => $id])->execute();

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionNewtendattach($id)
    {

        $modelAddAttach = new DynamicModel(['files']);
        $modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

        $filesArr = UploadedFile::getInstances($modelAddAttach, 'files');

        $filePath = \Yii::getAlias('@webroot/files/task/' . $id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/task/'))) {
            mkdir(\Yii::getAlias('@webroot/files/task/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/task/' . $id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/task/' . $id . '/'), 0775);
        }

        foreach ($filesArr as $file) {

            if (!file_exists($filePath . $file->baseName . '.' . $file->extension)) {
                $file->saveAs($filePath . $file->baseName . '.' . $file->extension);
            } else {

                $filename = $filePath . $file->baseName . '.' . $file->extension;
                $i = 1;

                while (file_exists($filename)) {
                    $filename = $filePath . $file->baseName . '(' . $i . ').' . $file->extension;
                    $i++;
                }

                $file->saveAs($filename);

            }
        }

        return $this->redirect(['plan/taskfull', 'id' => $id]);

    }

    public function actionTaskmyattach($id)
    {

        $modelAddAttach = new DynamicModel(['files']);
        $modelAddAttach->addRule(['files'], 'file', ['skipOnEmpty' => true, 'maxFiles' => 30]);

        $filesArr = UploadedFile::getInstances($modelAddAttach, 'files');

        $filePath = \Yii::getAlias('@webroot/files/mytask/' . $id . '/');

        if (!file_exists(\Yii::getAlias('@webroot/files/'))) {
            mkdir(\Yii::getAlias('@webroot/files/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/mytask/'))) {
            mkdir(\Yii::getAlias('@webroot/files/mytask/'), 0775);
        }

        if (!file_exists(\Yii::getAlias('@webroot/files/mytask/' . $id . '/'))) {
            mkdir(\Yii::getAlias('@webroot/files/mytask/' . $id . '/'), 0775);
        }

        foreach ($filesArr as $file) {

            if (!file_exists($filePath . $file->baseName . '.' . $file->extension)) {
                $file->saveAs($filePath . $file->baseName . '.' . $file->extension);
            } else {

                $filename = $filePath . $file->baseName . '.' . $file->extension;
                $i = 1;

                while (file_exists($filename)) {
                    $filename = $filePath . $file->baseName . '(' . $i . ').' . $file->extension;
                    $i++;
                }

                $file->saveAs($filename);

            }
        }

        return $this->redirect(['plan/taskmyfull', 'id' => $id]);

    }

    public function actionAjaxexecutionstatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = TaskUser::findOne(['id' => $id]);
        $model->id = $id;
        $model->status = $status;
        $model->data_status = (String) time();
        $model->save();

        return TaskUser::colorForExecutionStatus($model->status);
    }

    public function actionTaskmystatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = TaskMy::findOne(['id' => $id]);
        $model->id = $id;
        $model->status = $status;
        $model->data_status = (String) time();
        $model->save();

        return TaskUser::colorForExecutionStatus($model->status);
    }

    public function actionTaskmypriority()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = TaskMy::findOne(['id' => $id]);
        $model->id = $id;
        $model->priority = $status;
        $model->save();

    }

    public function actionTaskpriority()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = TaskUser::findOne(['id' => $id]);
        $model->id = $id;
        $model->priority = $status;
        $model->save();

    }

    public function actionGetcomments()
    {

        if(Yii::$app->request->post('id')) {

            $id = Yii::$app->request->post('id');

            $model = TaskUser::findOne(['id' => $id]);

            if (isset($model->comment)) {
                $resComm = "<u style='color:#757575;'>Комментарий ответственного:</u> " . nl2br($model->comment) . "<br />";
            } else {
                $resComm = "<u style='color:#757575;'>Комментарий ответственного:</u><br />";
            }

            echo json_encode(['success' => 'true', 'comment' => $resComm]);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionIsarchive($id)
    {
            $model = TaskUser::findOne(['id' => $id]);
            $model->is_archive = 1;
            $model->save();
            return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Plan the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Plan::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}