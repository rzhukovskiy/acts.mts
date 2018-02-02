<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\components\DateHelper;
use common\models\Company;
use common\models\CompanyDriver;
use common\models\Email;
use common\models\MonthlyAct;
use yii\helpers\Html;
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
                        'actions' => ['list', 'notification', 'add', 'update', 'delete', 'test', 'sendemail', 'sendemailmass', 'deletefile', 'smstext', 'sendsms', 'notifdirectors'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'notification', 'add', 'update', 'delete', 'test', 'sendemail', 'sendemailmass', 'deletefile', 'smstext', 'sendsms', 'notifdirectors'],
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'notification', 'add', 'update', 'delete', 'test', 'sendemail', 'sendemailmass', 'deletefile', 'smstext', 'sendsms', 'notifdirectors'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                    [
                        'actions' => ['cronmailer', 'cronaddressnew', 'crondebt'],
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

    public function actionNotification()
    {

        $searchModel = Company::find()->where(['OR', ['type' => 2], ['type' => 4]])->andWhere(['OR', ['status' => 2], ['status' => 10]])->with('info')->with('offer');

        $dataProvider = new ActiveDataProvider([
            'query' => $searchModel,
            'pagination' => false,
        ]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'type'    => SORT_ASC,
            ]
        ];

        return $this->render('notific', [
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

        // Отправка тестового письма

        if((Yii::$app->request->post('email')) && (Yii::$app->request->post('title')) && (Yii::$app->request->post('text')) && (Yii::$app->request->post('id'))) {

            $plainTextContent = Yii::$app->request->post('text');
            $subject = Yii::$app->request->post('title');
            $toEmail = Yii::$app->request->post('email');
            $id = Yii::$app->request->post('id');

            /*$un = strtoupper(uniqid(time()));

            $plainText = '';

            $headers  = 'From: notice@mtransservice.ru' . "\r\n";
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
                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
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

    public function actionSendemail()
    {

        // Универсальная отправка писем по заданному шаблону

        if((Yii::$app->request->post('email')) && (Yii::$app->request->post('id')) && (Yii::$app->request->post('data'))) {

            $toEmail = Yii::$app->request->post('email');
            $idTemplate = Yii::$app->request->post('id');
            $dataArr = json_decode(Yii::$app->request->post('data'));

            // Почтовый шаблон для уведомления
            $emailCont = Email::findOne(['id' => $idTemplate]);

            if (isset($emailCont)) {

                if ((isset($emailCont->title)) && (isset($emailCont->text))) {

                    $subject = $emailCont->title;
                    $plainTextContent = nl2br($emailCont->text);

                    if(count($dataArr) > 0) {
                        for ($iData = 0; $iData < count($dataArr); $iData++) {

                            if($dataArr[$iData][0] == '{TRACKLINK}') {
                                $plainTextContent = str_replace($dataArr[$iData][0], Html::a($dataArr[$iData][1], $dataArr[$iData][1], ['target' => 'blank']), $plainTextContent);
                            } else if($dataArr[$iData][0] == '{TRACKLIST}') {

                                // Запрос отслеживания
                                $ResTrack = json_decode(file_get_contents('https://api.track24.ru/tracking.json.php?apiKey=a5edc8e48db79d1aec6891cb2ebe0cf2&domain=mtransservice.ru&code=' . $dataArr[$iData][1]));
                                $trackCont = 'Нет информации по отслеживанию';

                                if(isset($ResTrack->data->events)) {

                                    $DataTrack = $ResTrack->data->events;
                                    $trackCont = '';

                                    for ($iTrack = 0; $iTrack < count($DataTrack); $iTrack++) {
                                        if (($iTrack + 1) < count($DataTrack)) {
                                            $trackCont .= $DataTrack[$iTrack]->operationDateTime . ' - ' . $DataTrack[$iTrack]->operationType . ' - ' . $DataTrack[$iTrack]->operationPlacePostalCode . ', ' . $DataTrack[$iTrack]->operationPlaceName . '<br />';
                                        } else {
                                            $trackCont .= $DataTrack[$iTrack]->operationDateTime . ' - ' . $DataTrack[$iTrack]->operationType . ' - ' . $DataTrack[$iTrack]->operationPlacePostalCode . ', ' . $DataTrack[$iTrack]->operationPlaceName;
                                        }
                                    }

                                }

                                $plainTextContent = str_replace($dataArr[$iData][0], $trackCont, $plainTextContent);

                            }

                        }
                    }

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo($toEmail)
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $pathfolder = \Yii::getAlias('@webroot/files/email/' . $idTemplate . '/');

                    if (file_exists($pathfolder)) {

                        foreach (FileHelper::findFiles($pathfolder) as $file) {
                            $mailCont->attach($pathfolder . basename($file));
                        }

                    }

                    $resSend = $mailCont->send();

                    if ($resSend) {
                        echo json_encode(['success' => 'true']);
                    } else {
                        echo json_encode(['success' => 'false']);
                    }

                } else {
                    echo json_encode(['success' => 'false']);
                }

            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    // Получаем список смс шаблонов
    public static function getSmsTemplates()
    {

        $arrEmailList = Email::find()->where(['like', 'name', 'смс'])->orWhere(['like', 'name', 'СМС'])->select('name')->indexBy('id')->column();

        if(count($arrEmailList) > 0) {
            return $arrEmailList;
        } else {
            return [];
        }

    }
    // Получаем список смс шаблонов

    // Получаем текст выбранного смс шаблона для просмотра
    public function actionSmstext()
    {

        if(Yii::$app->request->post('id')) {
            $id = Yii::$app->request->post('id');

            if(($id > 0) && ($id != '')) {
            $model = Email::findOne(['id' => $id]);

            if(isset($model->text)) {
                echo json_encode(['success' => 'true', 'text' => nl2br($model->text), 'id' => $id]);
            } else {
                echo json_encode(['success' => 'false']);
            }

            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }
    // Получаем текст выбранного смс шаблона для просмотра

    // Оповещение Арама и Герберта в актах и оплате
    public function actionNotifdirectors()
    {
        if((Yii::$app->request->post('name')) && (Yii::$app->request->post('price')) && (Yii::$app->request->post('period')) && (Yii::$app->request->post('type')) && (Yii::$app->request->post('user_id')) && (Yii::$app->request->post('url'))) {

            $name = Yii::$app->request->post('name');
            $price = Yii::$app->request->post('price');
            $period = Yii::$app->request->post('period');
            $type = Yii::$app->request->post('type');
            $user_id = Yii::$app->request->post('user_id');
            $url = Yii::$app->request->post('url');
            $status = Yii::$app->request->post('status');

            // Получаем почтовый шаблон

            $model = Email::findOne(['id' => 12]);

            $userModel = User::findOne(['id' => $user_id]);

            if ((isset($model)) && (isset($userModel))) {

                if ((isset($model->title)) && (isset($model->text)) && (isset($userModel->username))) {

                    // для статуса ЭДО
                    if ($status == 7) {
                        $subject = $model->title . " ЭДО";
                    } else {
                        $subject = $model->title;
                    }

                    $plainTextContent = nl2br($model->text);

                    if(preg_match('~"([^"]*)"~u' , $name , $n)) {
                        $name = $n[1];
                    } else {
                        //net slova v kavychkah
                    }

                    $name = str_replace('+', '%2B', $name);

                    // заменяем теги данными
                    $plainTextContent = str_replace('{COMPANY-NAME}', $name, $plainTextContent);
                    $plainTextContent = str_replace('{PRICE}', $price . " руб.", $plainTextContent);
                    $plainTextContent = str_replace('{MONTH}', $period, $plainTextContent);
                    $plainTextContent = str_replace('{TYPE}', Company::$listType[$type]['ru'], $plainTextContent);
                    $plainTextContent = str_replace('{USER}', $userModel->username, $plainTextContent);

                    if((mb_strpos($name, "'") > 0) || (mb_strpos($name, '"') > 0) || (mb_strpos($name, '«') > 0) || (mb_strpos($name, '»') > 0)) {
                        $plainTextContent = str_replace('{LINK}', Html::a('Ссылка', (urldecode($url) . "/monthly-act/list?MonthlyActSearch%5Bact_date%5D=" . $period . "&type=" . $type)), $plainTextContent);
                    } else {
                        $plainTextContent = str_replace('{LINK}', Html::a('Ссылка', (urldecode($url) . "/monthly-act/list?MonthlyActSearch%5Bact_date%5D=" . $period . "&MonthlyActSearch%5Bclient_name%5D=" . urlencode($name) . "&type=" . $type)), $plainTextContent);
                    }

                    // для статуса ЭДО
                    if ($status == 7) {
                        $plainTextContent = "ЭДО срочно оплатить!<br /><br />" . $plainTextContent;
                    }

                    // Арам
                    /*if($user_id != 176) {
                        $toEmail = "aram.mtransservice@mail.ru";

                        $mailCont = Yii::$app->mailer->compose()
                            ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo($toEmail)
                            ->setSubject($subject)
                            ->setHtmlBody($plainTextContent)->send();
                    }*/

                    // Герберт
                    $toEmail = "mtransservice@mail.ru";

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo($toEmail)
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent)->send();

                    echo json_encode(['success' => 'true']);

                } else {
                    echo json_encode(['success' => 'false']);
                }

            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }
    }

    // Отправляем смс шаблон по водителям компании
    public function actionSendsms()
    {

        if((Yii::$app->request->post('id')) && (Yii::$app->request->post('company_id'))) {
            $id = Yii::$app->request->post('id');
            $company_id = Yii::$app->request->post('company_id');

            if(($id > 0) && ($id != '') && ($company_id > 0) && ($company_id != '')) {

                //$arrDrivers = CompanyDriver::find()->where(['AND', ['company_id' => $company_id], ['>', 'car_id', 0]])->select('phone')->asArray()->column();
                $arrDrivers = CompanyDriver::find()->where(['company_id' => $company_id])->andWhere(['not', ['phone' => null]])->select('phone')->asArray()->column();

                if(count($arrDrivers) > 0) {

                    // проверяем на повторные номера
                    $arrSendSms = [];

                    // Получаем почтовый шаблон
                    $model = Email::findOne(['id' => $id]);

                    if(isset($model->text)) {

                        $textSMS = strip_tags(trim($model->text));
                        include_once (\Yii::getAlias('@backend/models/sms.php'));

                        for ($i = 0; $i < count($arrDrivers); $i++) {
                            if (isset($arrDrivers[$i])) {
                                $number = $arrDrivers[$i];

                                $number = trim($number);
                                $number = str_replace('  ', '', $number);
                                $number = str_replace(' ', '', $number);
                                $number = str_replace('--', '', $number);
                                $number = str_replace('-', '', $number);
                                $number = str_replace('+7', '7', $number);

                                if ($number[0] == '8') {
                                    $number[0] = '7';
                                }

                                $haveNumber = false;

                                // проверка на повторный номер
                                for ($j = 0; $j < count($arrSendSms); $j++) {
                                    if ($number == $arrSendSms[$j]) {
                                        $haveNumber = true;
                                    }
                                }

                                if ($haveNumber == false) {

                                    // Отправка смс
                                    $key = '5499Pf110SP094weDdjgG88d';
                                    $phone = $number;
                                    $text = $textSMS;
                                    $sender_name = "MTC.";
                                    $resultSMS = smsapi_push_msg_nologin_key($key, $phone, $text, array("sender_name"=>$sender_name));

                                    //Далее, пример обработки полученных данных
                                    if (isset($resultSMS['response'])) {

                                        if ($resultSMS['response']['msg']['err_code'] > 0) {
                                            /*// Получили ошибку
                                            print $resultSMS['response']['msg']['err_code']; // код ошибки
                                            print $resultSMS['response']['msg']['text']; // текстовое описание ошибки*/
                                        } else {
                                            $arrSendSms[] = $number;
                                            // Запрос прошел без ошибок, получаем нужные данные
                                            /*print $resultSMS['response']['data']['id']; // id SMS
                                            $resultSMS['response']['data']['credits']; // Стоимость
                                            $resultSMS['response']['data']['n_raw_sms']; // Количество сегментов SMS
                                            $resultSMS['response']['data']['sender_name']; // Отправитель*/
                                        }

                                    }
                                    // Отправка смс
                                }

                                $number = '';
                                $haveNumber = false;

                            }
                        }

                        echo json_encode(['success' => 'true', 'num' => count($arrSendSms)]);

                    } else {
                        echo json_encode(['success' => 'false']);
                    }

                } else {
                    echo json_encode(['success' => 'false']);
                }

            } else {
                echo json_encode(['success' => 'false']);
            }

        } else {
            echo json_encode(['success' => 'false']);
        }

    }
    // Отправляем смс шаблон по водителям компании

    // Массовая отправка уведомлений о прибытии в место получения
    public function actionSendemailmass()
    {

        // Универсальная отправка писем по заданному шаблону

        if((Yii::$app->request->post('email')) && (Yii::$app->request->post('id')) && (Yii::$app->request->post('number'))) {

            $emailArr = json_decode(json_decode(Yii::$app->request->post('email'), True), True);
            $idTemplate = Yii::$app->request->post('id');
            $numberArr = json_decode(json_decode(Yii::$app->request->post('number'), True), True);

            $numTrueSend = 0;

            // Почтовый шаблон для уведомления
            $emailCont = Email::findOne(['id' => $idTemplate]);

            if (isset($emailCont)) {

                if ((isset($emailCont->title)) && (isset($emailCont->text))) {

                    $subject = $emailCont->title;
                    $maintext = nl2br($emailCont->text);
                    $maintext = str_replace('{TRACKLIST}', 'Прибыло в пункт назначения', $maintext);

                    if(count($emailArr) > 0) {
                        foreach ($emailArr as $key => $value) {

                            $plainTextContent = $maintext;

                            if(isset($numberArr[$key])) {

                                if(count(explode(',', $value)) > 1) {

                                    $arrEmail = explode(',', $value);
                                    $checkSendMail = false;

                                    for ($iEmail = 0; $iEmail < count($arrEmail); $iEmail++) {

                                        $emailContent = trim($arrEmail[$iEmail]);

                                        if (filter_var($emailContent, FILTER_VALIDATE_EMAIL)) {

                                            $linkTrack = 'https://www.pochta.ru/tracking#' . $numberArr[$key];
                                            $plainTextContent = str_replace('{TRACKLINK}', Html::a($linkTrack, $linkTrack, ['target' => 'blank']), $plainTextContent);
                                            $plainTextContent = str_replace('История почтового отправления', 'Местоположение почтового отправления', $plainTextContent);

                                            $resSend = Yii::$app->mailer->compose()
                                                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                                ->setTo($emailContent)
                                                ->setSubject($subject)
                                                ->setHtmlBody($plainTextContent)->send();

                                            if ($resSend) {
                                                $numTrueSend++;
                                                $checkSendMail = true;
                                            }

                                        }
                                    }

                                    if($checkSendMail) {
                                        // Сохраняем комментарий в акте и оплате
                                        $modelMonthlyAct = MonthlyAct::findOne(['id' => $key]);

                                        if(isset($modelMonthlyAct)) {
                                            $modelMonthlyAct->act_comment = trim($modelMonthlyAct->act_comment . " Отправление поступило в место вручения. Отправлено повторное уведомление.");
                                            $modelMonthlyAct->save();
                                        }
                                        // Сохраняем комментарий в акте и оплате
                                    }

                                } else {

                                    if (filter_var($value, FILTER_VALIDATE_EMAIL)) {

                                        $linkTrack = 'https://www.pochta.ru/tracking#' . $numberArr[$key];
                                        $plainTextContent = str_replace('{TRACKLINK}', Html::a($linkTrack, $linkTrack, ['target' => 'blank']), $plainTextContent);
                                        $plainTextContent = str_replace('История почтового отправления', 'Местоположение почтового отправления', $plainTextContent);

                                        $resSend = Yii::$app->mailer->compose()
                                            ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                            ->setTo($value)
                                            ->setSubject($subject)
                                            ->setHtmlBody($plainTextContent)->send();

                                        if ($resSend) {
                                            $numTrueSend++;

                                            // Сохраняем комментарий в акте и оплате
                                            $modelMonthlyAct = MonthlyAct::findOne(['id' => $key]);

                                            if(isset($modelMonthlyAct)) {
                                                $modelMonthlyAct->act_comment = trim($modelMonthlyAct->act_comment . " Отправление поступило в место вручения. Отправлено повторное уведомление.");
                                                $modelMonthlyAct->save();
                                            }
                                            // Сохраняем комментарий в акте и оплате

                                        }

                                    }

                                }

                            }

                        }
                    }

                    if($numTrueSend > 0) {
                        echo json_encode(['success' => 'true', 'numsend' => $numTrueSend]);
                    } else {
                        echo json_encode(['success' => 'false']);
                    }

                } else {
                    echo json_encode(['success' => 'false']);
                }

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

/*            $dateFrom = date("Y-m-t", strtotime("-3 month")) . 'T21:00:00.000Z';
            $dateTo = date("Y-m-t") . 'T21:00:00.000Z';

            $partnerArr = Company::find()->where(['OR', ['`company`.`status`' => 2], ['`company`.`status`' => 10]])->andWhere(['OR', ['`company`.`type`' => '2'], ['`company`.`type`' => '4']])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->innerJoin('act', '`act`.`partner_id` = `company`.`id`')->andWhere(['not', ['`company_info`.`email`' => null]])->andWhere(['!=', '`company_info`.`email`', ''])->andWhere(['between', "DATE(FROM_UNIXTIME(`act`.`created_at`))", $dateFrom, $dateTo])->select('`company_info`.`email`, `company`.`type`, `company`.`id`, `company`.`name`')->groupBy('`company`.`id`')->asArray()->all();*/
            $partnerArr = Company::find()->where(['OR', ['`company`.`status`' => 2], ['`company`.`status`' => 10]])->andWhere(['OR', ['`company`.`type`' => '2'], ['`company`.`type`' => '4']])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->innerJoin('company_offer', '`company_offer`.`company_id` = `company`.`id`')->andWhere(['not', ['`company_info`.`email`' => null]])->andWhere(['!=', '`company_info`.`email`', ''])->andWhere(['`company_offer`.`email_status`' => 1])->select('`company_info`.`email`, `company`.`type`, `company`.`id`, `company`.`name`')->groupBy('`company`.`id`')->asArray()->all();

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

                            $toEmail = strtolower(trim($partnerArr[$iCompany]['email']));

                            if(count(explode(',', $toEmail)) > 1) {

                                $arrEmail = explode(',', $toEmail);

                                for($iEmail = 0; $iEmail < count($arrEmail); $iEmail++) {

                                    $emailContent = trim($arrEmail[$iEmail]);

                                    if(filter_var($emailContent, FILTER_VALIDATE_EMAIL)) {

                                        // получаем email назначения и отправляем письмо

                                        if($partnerArr[$iCompany]['type'] == 2) {

                                            $mailCont = Yii::$app->mailer->compose()
                                                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
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
                                                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
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
                                            ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
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
                                            ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
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

                    /*// Копия

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('margarita@mtransservice.ru')
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('anna@mtransservice.ru')
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('merkulova@mtransservice.ru')
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
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('oksana@mtransservice.ru')
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

                    // Копия*/

                    if(($numError + $numErrorWash) > 0) {

                        if(($numError) > 0) {
                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('margarita@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('anna@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();
                        }

                        if(($numErrorWash) > 0) {
                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('merkulova@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('oksana@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();
                        }

                        return "Неудалось отправить " . ($numError + $numErrorWash) . " из " . ($numError + $numErrorWash + $numEmailSend + $numEmailSendWash) . " писем рассылки по партнерам";
                    } else {

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('margarita@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('anna@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('merkulova@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('oksana@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        // To Gerbert

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        return "Письма удачно отправлены (" . ($numEmailSend + $numEmailSendWash) . ")";
                    }

                }
            }

        }

        return 1;

       // Рассылка 2 раза в неделю для партреров

    }

    public function actionCronaddressnew($id)
    {

        // Рассылка по шаблону крон
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

            $numTemplate = $id;

            // Получаем партнеров

            /*            $dateFrom = date("Y-m-t", strtotime("-3 month")) . 'T21:00:00.000Z';
                        $dateTo = date("Y-m-t") . 'T21:00:00.000Z';

                        $partnerArr = Company::find()->where(['OR', ['`company`.`status`' => 2], ['`company`.`status`' => 10]])->andWhere(['OR', ['`company`.`type`' => '2'], ['`company`.`type`' => '4']])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->innerJoin('act', '`act`.`partner_id` = `company`.`id`')->andWhere(['not', ['`company_info`.`email`' => null]])->andWhere(['!=', '`company_info`.`email`', ''])->andWhere(['between', "DATE(FROM_UNIXTIME(`act`.`created_at`))", $dateFrom, $dateTo])->select('`company_info`.`email`, `company`.`type`, `company`.`id`, `company`.`name`')->groupBy('`company`.`id`')->asArray()->all();*/
            $partnerArr = Company::find()->where(['OR', ['`company`.`status`' => 2], ['`company`.`status`' => 10]])->andWhere(['OR', ['`company`.`type`' => '2'], ['`company`.`type`' => '4']])->innerJoin('company_info', '`company_info`.`company_id` = `company`.`id`')->innerJoin('company_offer', '`company_offer`.`company_id` = `company`.`id`')->andWhere(['not', ['`company_info`.`email`' => null]])->andWhere(['!=', '`company_info`.`email`', ''])->andWhere(['`company_offer`.`email_status`' => 1])->select('`company_info`.`email`, `company`.`type`, `company`.`id`, `company`.`name`')->groupBy('`company`.`id`')->asArray()->all();

            if(isset($partnerArr)) {
                if(count($partnerArr) > 0) {

                    // Получаем шаблон письма
                    $emailCont = Email::findOne(['id' => $numTemplate]);
                    $plainTextContent = nl2br($emailCont->text);
                    $subject = $emailCont->title;

                    for($iCompany = 0; $iCompany < count($partnerArr); $iCompany++) {

                        $resSend = false;
                        $mailCont = Yii::$app->mailer->compose();

                        if((isset($partnerArr[$iCompany]['email'])) && (isset($partnerArr[$iCompany]['type'])) && (isset($partnerArr[$iCompany]['name']))) {

                            $toEmail = strtolower(trim($partnerArr[$iCompany]['email']));

                            if(count(explode(',', $toEmail)) > 1) {

                                $arrEmail = explode(',', $toEmail);

                                for($iEmail = 0; $iEmail < count($arrEmail); $iEmail++) {

                                    $emailContent = trim($arrEmail[$iEmail]);

                                    if(filter_var($emailContent, FILTER_VALIDATE_EMAIL)) {

                                        // получаем email назначения и отправляем письмо

                                        if($partnerArr[$iCompany]['type'] == 2) {

                                            $mailCont = Yii::$app->mailer->compose()
                                                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                                ->setTo($emailContent)
                                                ->setSubject($subject)
                                                ->setHtmlBody($plainTextContent);

                                            $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplate . '/');

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
                                                ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                                ->setTo($emailContent)
                                                ->setSubject($subject)
                                                ->setHtmlBody($plainTextContent);

                                            $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplate . '/');

                                            if (file_exists($pathfolder)) {

                                                foreach (FileHelper::findFiles($pathfolder) as $file) {
                                                    $mailCont->attach($pathfolder . basename($file));
                                                }

                                            }

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
                                            ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                            ->setTo($toEmail)
                                            ->setSubject($subject)
                                            ->setHtmlBody($plainTextContent);

                                        $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplate . '/');

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
                                            ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                            ->setTo($toEmail)
                                            ->setSubject($subject)
                                            ->setHtmlBody($plainTextContent);

                                        $pathfolder = \Yii::getAlias('@webroot/files/email/' . $numTemplate . '/');

                                        if (file_exists($pathfolder)) {

                                            foreach (FileHelper::findFiles($pathfolder) as $file) {
                                                $mailCont->attach($pathfolder . basename($file));
                                            }

                                        }

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

                    /*// Копия

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('margarita@mtransservice.ru')
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('anna@mtransservice.ru')
                        ->setSubject($subject)
                        ->setHtmlBody($plainTextContent);

                    $resSend = $mailCont->send();

                    $mailCont = '';
                    $resSend = '';
                    $toEmail = '';

                    $mailCont = Yii::$app->mailer->compose()
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('merkulova@mtransservice.ru')
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
                        ->setFrom(['notice@mtransservice.ru' => 'Международный Транспортный Сервис'])
                        ->setTo('oksana@mtransservice.ru')
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

                    // Копия*/

                    if(($numError + $numErrorWash) > 0) {

                        if(($numError) > 0) {
                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('margarita@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('anna@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numError из " . ($numError + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmail)->send();
                        }

                        if(($numErrorWash) > 0) {
                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('merkulova@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('oksana@mtransservice.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();

                            Yii::$app->mailer->compose()
                                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                                ->setTo('mtransservice@mail.ru')
                                ->setSubject('Ошибка при отправке рассылок')
                                ->setHtmlBody("Неудалось отправить $numErrorWash из " . ($numErrorWash + $numEmailSend) . " писем рассылки по партнерам:<br /><br />" . $stringErrorEmailWash)->send();
                        }

                        return "Неудалось отправить " . ($numError + $numErrorWash) . " из " . ($numError + $numErrorWash + $numEmailSend + $numEmailSendWash) . " писем рассылки по партнерам";
                    } else {

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('margarita@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('anna@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('merkulova@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('oksana@mtransservice.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        // To Gerbert

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSendWash)<br /><br />" . $stringSendEmailWash)->send();

                        Yii::$app->mailer->compose()
                            ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                            ->setTo('mtransservice@mail.ru')
                            ->setSubject('Рассылка успешно отправлена')
                            ->setHtmlBody("Список получателей: ($numEmailSend)<br /><br />" . $stringSendEmail)->send();

                        return "Письма удачно отправлены (" . ($numEmailSend + $numEmailSendWash) . ")";
                    }

                }
            }

        }

        return 1;

        // Рассылка по шаблону крон

    }

    public function actionCrondebt()
    {

        // Рассылка 2 раза в неделю о должниках Араму и Юле
        if(isset(Yii::$app->user->identity->id)) {
            return $this->redirect('/');
        } else {

            $ArrDebt = [];
            $dateFrom = date('Y-m-t', strtotime("-6 month")) . 'T21:00:00.000Z';
            $dateTo = date('Y-m-t') . 'T21:00:00.000Z';

            $profitRes = \common\models\Act::find()->innerJoin('monthly_act', 'monthly_act.client_id = act.client_id AND monthly_act.type_id = act.service_type AND (monthly_act.act_date = DATE_FORMAT(from_unixtime(act.served_at), "%Y-%m-00"))')->innerJoin('company', 'company.id = monthly_act.client_id')->where(['AND', ['monthly_act.payment_status' => 0], [">", "act.income", 0], ['between', 'act_date', $dateFrom, $dateTo]])->andWhere(['OR', ['AND', ['monthly_act.type_id' => 5], ['monthly_act.service_id' => 4]], ['!=', 'monthly_act.type_id', 5]])->andWhere(['OR', ['AND', ['!=', 'monthly_act.type_id', 3], ['!=', 'monthly_act.act_date', (date("Y-m") . '-00')]], ['AND', ['monthly_act.type_id' => 3], '`act`.`id`=`monthly_act`.`act_id`']])->select('SUM(act.income) as profit, company.name as name, monthly_act.client_id as id, monthly_act.id as mid, monthly_act.act_date as date, monthly_act.type_id as type')->groupBy('monthly_act.id')->indexBy('mid')->orderBy('monthly_act.act_date, monthly_act.client_id, monthly_act.type_id')->asArray()->all();

            if(count($profitRes) > 0) {
                foreach ($profitRes as $key => $value) {

                    $arrDate = $profitRes[$key];
                    $indexP = $arrDate['date'];
                    $index = $arrDate['id'];
                    $indexT = $arrDate['type'];

                    $ArrDebt[$indexP][$index][$indexT][0] = $arrDate['name'];
                    $ArrDebt[$indexP][$index][$indexT][1] = $arrDate['profit'];
                }
            }

            // дез
            $profitResDes = \common\models\Act::find()->innerJoin('monthly_act', 'monthly_act.client_id = act.client_id AND monthly_act.type_id = act.service_type AND (monthly_act.act_date = DATE_FORMAT(from_unixtime(act.served_at), "%Y-%m-00"))')->innerJoin('act_scope', 'act_scope.act_id = act.id AND act_scope.company_id = act.client_id AND act_scope.service_id = 5')->innerJoin('company', 'company.id = monthly_act.client_id')->where(['AND', ['monthly_act.payment_status' => 0], ['monthly_act.type_id' => 5], [">", "act.income", 0], ['between', 'act_date', $dateFrom, $dateTo]])->andWhere(['OR', ['AND', ['!=', 'monthly_act.type_id', 3], ['!=', 'monthly_act.act_date', (date("Y-m") . '-00')]], ['AND', ['monthly_act.type_id' => 3], '`act`.`id`=`monthly_act`.`act_id`']])->select('SUM(act.income) as profit, company.name as name, monthly_act.client_id as id, monthly_act.id as mid, monthly_act.act_date as date, monthly_act.type_id as type')->groupBy('monthly_act.id')->indexBy('mid')->orderBy('monthly_act.act_date, monthly_act.client_id, monthly_act.type_id')->asArray()->all();

            if(count($profitResDes) > 0) {
                foreach ($profitResDes as $key => $value) {

                    $arrDate = $profitResDes[$key];
                    $indexP = $arrDate['date'];
                    $index = $arrDate['id'];
                    $indexT = $arrDate['type'];

                    if((isset($ArrDebt[$indexP][$index][$indexT][0])) && (isset($ArrDebt[$indexP][$index][$indexT][1]))) {
                        $ArrDebt[$indexP][$index][$indexT][1] += $arrDate['profit'];
                    } else {
                        $ArrDebt[$indexP][$index][$indexT][0] = $arrDate['name'];
                        $ArrDebt[$indexP][$index][$indexT][1] = $arrDate['profit'];
                    }

                }
            }

            $resText = '<b style="color:#069;">Должники за последние 5 месяцев:</b><br />';
            $arrTypes = Company::$listType;
            $i = 1;
            $summ = 0;

            $old_id = 0;

            foreach ($ArrDebt as $keyP => $valueP) {
                foreach ($valueP as $key => $value) {
                    foreach ($value as $keyT => $valueT) {

                        $new_id = $key;
                        $arrPeriod = explode('-', $keyP);
                        $in = (int) $arrPeriod[1];
                        $showDate = DateHelper::$months[$in][0] . ' ' . $arrPeriod[0];

                        if($old_id != $new_id) {

                            $resText .= '<br /><b>' . $ArrDebt[$keyP][$key][$keyT][0] . '</b><br />';
                            $resText .= $showDate . ' - ' . $arrTypes[$keyT]['ru'] . ' - ' . $ArrDebt[$keyP][$key][$keyT][1] . '₽<br />';

                            $old_id = $new_id;
                        } else {
                            $resText .= $showDate . ' - ' . $arrTypes[$keyT]['ru'] . ' - ' . $ArrDebt[$keyP][$key][$keyT][1] . '₽<br />';
                        }

                        $summ += $ArrDebt[$keyP][$key][$keyT][1];
                        $i++;
                    }
                }
            }

            $resText .= '<br /><b style="color:#069;">Общая сумма: </b>' . $summ . '₽';

            // Юля
            Yii::$app->mailer->compose()
                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                ->setTo('merkulova@mtransservice.ru')
                ->setSubject('Рассылка по должникам ' . date('d.m.Y'))
                ->setHtmlBody($resText)->send();

            // Арам
            Yii::$app->mailer->compose()
                ->setFrom(['system@mtransservice.ru' => 'Международный Транспортный Сервис'])
                ->setTo('aram@mtransservice.ru')
                ->setSubject('Рассылка по должникам ' . date('d.m.Y'))
                ->setHtmlBody($resText)->send();

        }

        return 1;

        // Рассылка 2 раза в неделю о должниках Араму и Юле

    }

}