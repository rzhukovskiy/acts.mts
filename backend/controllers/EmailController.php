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
use common\models\Contact;

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

        //
        $id = 4;
        $emailCont = Email::findOne(['id' => $id]);

        // Получаем шаблон письма
        $toEmail = 'roman92@mfeed.ru';
        $plainTextContent = 'Тестовое сообщение';
        $subject = 'Тестовый заголовок';

        $emailFrom = 'mtransservice@mail.ru';
        $nameFrom = 'Gerbert Romberg';

        $mailCont = Yii::$app->mailer->compose()
            ->setFrom([$emailFrom => $nameFrom])
            ->setTo($toEmail)
            ->setSubject($subject)
            ->setHtmlBody($plainTextContent);

        $pathfolder = \Yii::getAlias('@webroot/files/email/' . $id . '/');

        if (file_exists($pathfolder)) {

            foreach (FileHelper::findFiles($pathfolder) as $file) {
                $mailCont->attach($pathfolder . basename($file));
            }

        }

        $resSend = $mailCont->send();

        if($resSend) {
            echo 123; die;
        } else {
            echo 222; die;
        }
        //

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

        if((Yii::$app->request->post('email')) && (Yii::$app->request->post('title')) && (Yii::$app->request->post('text')) && (Yii::$app->request->post('id'))) {

            $plainTextContent = Yii::$app->request->post('text');
            $subject = Yii::$app->request->post('title');
            $toEmail = Yii::$app->request->post('email');
            $id = Yii::$app->request->post('id');

            /*$un = strtoupper(uniqid(time()));

            $plainText = '';

            $headers  = 'From: info@mtransservice.ru' . "\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";

            $pathfolder = \Yii::getAlias('@webroot/files/email/' . $id . '/');
            $checkFiles = false;

            if (file_exists($pathfolder)) {
                $numFiles = 0;

                foreach (FileHelper::findFiles($pathfolder) as $file) {
                    $numFiles++;
                }

                if($numFiles > 0) {
                    $checkFiles = true;
                }

            }

            if ($checkFiles == false) {
                $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                $plainText = $plainTextContent;
            } else {

                $headers .= "Content-Type:multipart/mixed;";
                $headers .= "boundary=\"----------".$un."\"\r\n";
                $plainText = "------------".$un."\nContent-type: text/html; charset=utf-8;\r\n";
                $plainText .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $plainText .= chunk_split(base64_encode($plainTextContent));

                foreach (FileHelper::findFiles($pathfolder) as $file) {
                    $filename = $pathfolder . basename($file);

                    $f = fopen($filename,"rb");
                    $data = fread($f, filesize( $filename ));
                    fclose($f);

                    $NameFile = basename($file);
                    $File = $data;

                    $plainText .= "------------".$un."\r\n";
                    $plainText .= "Content-Type: application/octet-stream; name=\"$NameFile\"\r\n";
                    $plainText .= "Content-Transfer-Encoding: base64 \r\n";
                    $plainText .= "Content-Disposition: attachment; filename=\"$NameFile\"\r\n";
                    $plainText .= chunk_split(base64_encode($File));
                    $plainText .= "\r\n--$un--\r\n";

                }
            }

            $resSend = mail($toEmail, $subject, $plainText, $headers);

            if($resSend) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }*/

            $mailCont = Yii::$app->mailer->compose()
                ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                ->setTo($toEmail)
                ->setSubject($subject)
                ->setHtmlBody($plainTextContent);

            $pathfolder = \Yii::getAlias('@webroot/files/email/' . $id . '/');

            if (file_exists($pathfolder)) {

                foreach (FileHelper::findFiles($pathfolder) as $file) {
                    $mailCont->attach($pathfolder . basename($file));
                }

            }

            $resSend = $mailCont->send();

            if($resSend) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

}