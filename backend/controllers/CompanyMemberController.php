<?php

namespace backend\controllers;

use common\models\Company;
use Yii;
use common\models\CompanyMember;
use common\models\search\CompanyMemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Contact;
use yii\helpers\Html;

/**
 * CompanyMemberController implements the CRUD actions for CompanyMember model.
 */
class CompanyMemberController extends Controller
{
    /**
     * Lists all CompanyMember models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyMemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionSend($id)
    {
        $model = $this->findModel($id);

        $plainTextContent = Yii::$app->request->post('text');
        $subject = Yii::$app->request->post('topic');
        $toEmail = $model->email;
        $toName = $model->name;

        // Получаем контакты отправителя
        $userID = Yii::$app->user->identity->id;

        $footerMail = '';

        switch ($userID) {
            case 238:
                $userID = 1;

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Меркулова Юлия<br />';
                $footerMail .= 'Руководитель отдела "Автомойка"<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: 8 920 211 08 55<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('merkulova@mtransservice.ru', 'merkulova@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

                break;
            case 176:
                $userID = 2;

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Арам Петросян<br />';
                $footerMail .= 'Генеральный директор<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: 8 920 46 008 55<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('aram@mtransservice.ru', 'aram@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

                break;
            case 364:
                $userID = 7;

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Маргарита Григорян<br />';
                $footerMail .= 'Руководитель отдела "Шиномонтажа"<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: 8 903 652 81 55<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('margarita@mtransservice.ru', 'margarita@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

                break;
            case 222:
                $userID = 9;

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Анна Арамаисовна<br />';
                $footerMail .= 'Специалист отдела "Шиномонтаж"<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: +7 961 189 08 55<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('anna@mtransservice.ru', 'anna@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

                break;
            case 379:
                $userID = 8;

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Оксана Шелудько<br />';
                $footerMail .= 'Специалист отдела "Автомойка"<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: 8 960 127 08 55<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('oksana@mtransservice.ru', 'oksana@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

                break;
            default:
                $userID = 0;
        }

        $emailFrom = '';
        $nameFrom = '';

        if($userID > 0) {
            $contactModel = Contact::findOne(['id' => $userID]);

            $emailFrom = $contactModel->email;
            $nameFrom = $contactModel->name;
        } else {
            if(Yii::$app->user->identity->id == 1) {
                $emailFrom = 'notice@mtransservice.ru';
                $nameFrom = 'Герберт Ромберг';

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Герберт Ромберг<br />';
                $footerMail .= 'Советник по международному развитию<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: +49 176 725 22 835<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('mtransservice@mail.ru', 'mtransservice@mail.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

            } else if(Yii::$app->user->identity->id == 256) {
                $emailFrom = 'denis@mtransservice.ru';
                $nameFrom = 'Митрофанов Денис';

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Митрофанов Денис<br />';
                $footerMail .= 'Региональный менеджер B2B<br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Моб.: 8 960 127 70 55<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('denis@mtransservice.ru', 'denis@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

            } else {
                $emailFrom = 'notice@mtransservice.ru';
                $nameFrom = 'Международный Транспортный Сервис';

                $footerMail = 'С Уважением,<br /><br />';
                $footerMail .= 'Международный Транспортный Сервис<br />';
                $footerMail .= 'Гор. линия: 8 800 55 008 55<br />';
                $footerMail .= 'Эл. Адрес: ' . Html::mailto('callcenter@mtransservice.ru', 'callcenter@mtransservice.ru') . '<br />';
                $footerMail .= 'Сайт: ' . Html::a('mtransservice.ru', 'http://mtransservice.ru/', ['target' => 'blank']);

            }
        }

        $plainTextContent .= '<br /><br /><br />' . $footerMail;

        $mailCont = Yii::$app->mailer->compose()
            ->setFrom([$emailFrom => $nameFrom])
            ->setTo([$toEmail => $toName])
            ->setSubject($subject)
            ->setHtmlBody($plainTextContent);

        $resSend = $mailCont->send();

        return $this->redirect(['company/member', 'id' => $model->company_id]);
    }

    /**
     * Creates a new CompanyMember model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $company_id
     * @return mixed
     */
    public function actionCreate($company_id = null)
    {
        $model = new CompanyMember();
        $model->company_id = $company_id;

        $modelCompany = Company::findOne(['id' => $company_id]);

        $postArr = '';

        $postArr = Yii::$app->request->post();

        // Переводим email в нижний регистр
        if(isset($postArr['CompanyMember']['email'])) {
            $postArr['CompanyMember']['email'] = strtolower($postArr['CompanyMember']['email']);
        }

        if ($model->load($postArr) && $model->save()) {
            return $this->redirect(['company/member', 'id' => $model->company_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'modelCompany' => $modelCompany,
            ]);
        }
    }

    /**
     * Updates an existing CompanyMember model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->referrer);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CompanyMember model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['/company/member', 'id' => $model->company_id]);
    }

    /**
     * Finds the CompanyMember model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CompanyMember the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyMember::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
