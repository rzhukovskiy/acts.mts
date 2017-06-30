<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\models\Company;
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
                    [
                        'actions' => ['cronmailer'],
                        'allow' => true,
                        'roles' => ['?'],
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

    public function actionCronmailer()
    {

       // Рассылка 2 раза в неделю для партреров
        if(isset(Yii::$app->user->identity->id)) {
            return $this->redirect('/');
        } else {

            $numError = 0;
            $numErrorWash = 0;
            $numEmailSend = 0;
            $numEmailSendWash = 0;
            $stringSendEmail = '';
            $stringSendEmailWash = '';
            $stringErrorEmail = '';
            $stringErrorEmailWash = '';

            $numTemplate = 2;
            $numTemplateWash = 1;

            // Получаем партнеров

            $dateFrom = date("Y-m-t", strtotime("-6 month")) . 'T21:00:00.000Z';
            $dateTo = date("Y-m-t") . 'T21:00:00.000Z';

            $partnerArr = Company::find()->where(['OR', ['`company`.`status`' => 2], ['`company`.`status`' => 10]])->andWhere(['OR', ['`company`.`type`' => '2'], ['`company`.`type`' => '4']])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->innerJoin('act', '`act`.`partner_id` = `company`.`id`')->andWhere(['not', ['`company_info`.`email`' => null]])->andWhere(['!=', '`company_info`.`email`', ''])->andWhere(['between', "DATE(FROM_UNIXTIME(`act`.`created_at`))", $dateFrom, $dateTo])->select('`company_info`.`email`, `company`.`type`, `company`.`id`, `company`.`name`')->groupBy('`company`.`id`')->asArray()->all();

            if(isset($partnerArr)) {
                if(count($partnerArr) > 0) {

                    // Получаем шаблон письма для моек
                    $emailContWash = Email::findOne(['id' => $numTemplateWash]);
                    $plainTextContentWash = nl2br($emailContWash->text);
                    $subjectWash = $emailContWash->title;

                    // Получаем шаблон письма для остальных
                    $emailCont = Email::findOne(['id' => $numTemplate]);
                    $plainTextContent = nl2br($emailCont->text);
                    $subject = $emailCont->title;

                    for($iCompany = 0; $iCompany < count($partnerArr); $iCompany++) {

                        $resSend = false;
                        $mailCont = Yii::$app->mailer->compose();

                        if((isset($partnerArr[$iCompany]['email'])) && (isset($partnerArr[$iCompany]['type'])) && (isset($partnerArr[$iCompany]['name']))) {

                            $toEmail = $partnerArr[$iCompany]['email'];

                            if(count(explode(',', $toEmail)) > 1) {

                                $arrEmail = explode(',', $toEmail);

                                for($iEmail = 0; $iEmail < count($arrEmail); $iEmail++) {

                                    $emailContent = trim($arrEmail[$iEmail]);

                                    if(filter_var($emailContent, FILTER_VALIDATE_EMAIL)) {

                                        // получаем email назначения и отправляем письмо

                                        if($partnerArr[$iCompany]['type'] == 2) {

                                            $mailCont = Yii::$app->mailer->compose()
                                                ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                                ->setTo($emailContent)
                                                ->setSubject($subjectWash)
                                                ->setHtmlBody($plainTextContentWash);

                                            $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplateWash . '/');

                                            if (file_exists($pathfolder)) {

                                                foreach (FileHelper::findFiles($pathfolder) as $file) {
                                                    $mailCont->attach($pathfolder . basename($file));
                                                }

                                            }

                                            $resSend = $mailCont->send();

                                            if($resSend) {
                                                $stringSendEmailWash .= $partnerArr[$iCompany]['name'] . ' - ' . $emailContent . '<br />';
                                                $numEmailSendWash++;
                                            } else {
                                                $stringErrorEmailWash .= $partnerArr[$iCompany]['name'] . ' - ' . $emailContent . '<br />';
                                                $numErrorWash++;
                                            }

                                            $mailCont = '';
                                            $resSend = '';
                                            $toEmail = '';
                                            $emailContent = '';

                                        } else {

                                            $mailCont = Yii::$app->mailer->compose()
                                                ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                                ->setTo($emailContent)
                                                ->setSubject($subject)
                                                ->setHtmlBody($plainTextContent);

                                            $resSend = $mailCont->send();

                                            if($resSend) {
                                                $stringSendEmail .= $partnerArr[$iCompany]['name'] . ' - ' . $emailContent . '<br />';
                                                $numEmailSend++;
                                            } else {
                                                $stringErrorEmail .= $partnerArr[$iCompany]['name'] . ' - ' . $emailContent . '<br />';
                                                $numError++;
                                            }

                                            $mailCont = '';
                                            $resSend = '';
                                            $toEmail = '';
                                            $emailContent = '';

                                        }

                                    }

                                    $emailContent = '';

                                }

                            } else {
                                if(filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {

                                    // получаем email назначения и отправляем письмо

                                    if($partnerArr[$iCompany]['type'] == 2) {

                                        $mailCont = Yii::$app->mailer->compose()
                                            ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                            ->setTo($toEmail)
                                            ->setSubject($subjectWash)
                                            ->setHtmlBody($plainTextContentWash);

                                        $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplateWash . '/');

                                        if (file_exists($pathfolder)) {

                                            foreach (FileHelper::findFiles($pathfolder) as $file) {
                                                $mailCont->attach($pathfolder . basename($file));
                                            }

                                        }

                                        $resSend = $mailCont->send();

                                        if($resSend) {
                                            $stringSendEmailWash .= $partnerArr[$iCompany]['name'] . ' - ' . $toEmail . '<br />';
                                            $numEmailSendWash++;
                                        } else {
                                            $stringErrorEmailWash .= $partnerArr[$iCompany]['name'] . ' - ' . $toEmail . '<br />';
                                            $numErrorWash++;
                                        }

                                        $mailCont = '';
                                        $resSend = '';
                                        $toEmail = '';

                                    } else {

                                        $mailCont = Yii::$app->mailer->compose()
                                            ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                            ->setTo($toEmail)
                                            ->setSubject($subject)
                                            ->setHtmlBody($plainTextContent);

                                        $resSend = $mailCont->send();

                                        if($resSend) {
                                            $stringSendEmail .= $partnerArr[$iCompany]['name'] . ' - ' . $toEmail . '<br />';
                                            $numEmailSend++;
                                        } else {
                                            $stringErrorEmail .= $partnerArr[$iCompany]['name'] . ' - ' . $toEmail . '<br />';
                                            $numError++;
                                        }

                                        $mailCont = '';
                                        $resSend = '';
                                        $toEmail = '';

                                    }

                                }
                            }

                        }

                        $toEmail = '';

                    }

                    // Копия

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('margarita.mtransservice@mail.ru')
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('anna.mtransservice@mail.ru')
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('merkulova.mtransservice@mail.ru')
                        ->setSubject($subjectWash)
                        ->setHtmlBody($plainTextContentWash);

                    $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplateWash . '/');

                    if (file_exists($pathfolder)) {

                        foreach (FileHelper::findFiles($pathfolder) as $file) {
                            $mailCont->attach($pathfolder . basename($file));
                        }

                    }

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['info@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('oksana.mtransservice@mail.ru')
                        ->setSubject($subjectWash)
                        ->setHtmlBody($plainTextContentWash);

                    $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplateWash . '/');

                    if (file_exists($pathfolder)) {

                        foreach (FileHelper::findFiles($pathfolder) as $file) {
                            $mailCont->attach($pathfolder . basename($file));
                        }

                    }

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    // Копия

                    if(($numError + $numErrorWash) > 0) {

                        if(($numError) > 0) {
                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('margarita.mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('anna.mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();
                        }

                        if(($numErrorWash) > 0) {
                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('merkulova.mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('oksana.mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();
                        }

                        return "Неудалось отправить " . ($numError + $numErrorWash) . " из " . ($numError + $numErrorWash + $numEmailSend + $numEmailSendWash) . " писем рассылки по партнерам";
                    } else {

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('margarita.mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('anna.mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('merkulova.mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('oksana.mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        return "Письма удачно отправлены (" . ($numEmailSend + $numEmailSendWash) . ")";
                    }

                }
            }

        }
       // Рассылка 2 раза в неделю для партреров

    }

}