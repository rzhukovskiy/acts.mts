<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\Email;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use yii;
use yii\filters\AccessControl;
use common\models\User;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class EmailController extends Controller
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
                        'actions' => ['list', 'add', 'update', 'delete', 'test', 'deletefile'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    public function actionList()
    {

        $searchModel = Email::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'id'    => SORT_ASC,
            ]
        ];

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);

    }

    public function actionAdd()
    {

        $model = new Email();

        // Загрузка файлов

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            $model->files = UploadedFile::getInstances($model, 'files');

            if ($model->upload()) {
                // file is uploaded successfully
            } else {
            }

            return $this->redirect(['email/list']);

        } else {
            return $this->render('add', [
                'model' => $model,
            ]);
        }

    }

    public function actionUpdate($id)
    {

        $model = Email::findOne(['id' => $id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $model->files = UploadedFile::getInstances($model, 'files');

            if ($model->upload()) {
                // file is uploaded successfully
            } else {
            }

            return $this->redirect(['email/list']);
        } else {
            return $this->render('add', [
                'model' => $model,
            ]);
        }

    }

    public function actionDelete($id)
    {
        $model = Email::findOne(['id' => $id]);
        $model->delete();

        $pathFolderEmail = \Yii::getAlias('@webroot/files/email/' . $id . '/');

        if (file_exists($pathFolderEmail)) {
            foreach (FileHelper::findFiles($pathFolderEmail) as $file) {

                unlink($pathFolderEmail . basename($file));

            }
            rmdir($pathFolderEmail);
        }

        return $this->redirect(['email/list']);
    }

    public function actionDeletefile()
    {

        if((Yii::$app->request->post('id')) && (Yii::$app->request->post('name'))) {

            $id = Yii::$app->request->post('id');
            $name = Yii::$app->request->post('name');

            $pathFolderEmail = \Yii::getAlias('@webroot/files/email/' . $id . '/');

            unlink($pathFolderEmail . $name);
            echo json_encode(['success' => 'true']);

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    public function actionTest()
    {

        if((Yii::$app->request->post('email')) && (Yii::$app->request->post('title')) && (Yii::$app->request->post('text'))) {

            $plainTextContent = Yii::$app->request->post('text');
            $subject = Yii::$app->request->post('title');
            $toEmail = Yii::$app->request->post('email');

            $headers  = 'From: info@mtransservice.ru' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

            $resSend = mail($toEmail, $subject, $plainTextContent, $headers);

            if($resSend) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

            /*$resSend = Yii::$app->mailer->compose()
                ->setFrom('info@mtransservice.ru')
                ->setTo(Yii::$app->request->post('email'))
                ->setSubject(Yii::$app->request->post('title'))
                ->setHtmlBody(Yii::$app->request->post('text'))
                ->send();

            if($resSend) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }*/

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

}