<?php
/**
 * Created by PhpStorm.
 * User: dmitryrykov
 * Date: 07.11.17
 * Time: 18:32
 */

namespace api\controllers;

use common\models\Company;
use common\models\CompanyMember;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

class CompanyController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['members'],
                'rules' => [
                    [
                        'actions' => ['members'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['members'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'members' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionMembers()
    {

      if((Yii::$app->request->post("company_id")) && (Yii::$app->request->post("type"))) {

          $company_id = Yii::$app->request->post("company_id");
          $type = Yii::$app->request->post("type");

          if ($type == 1) {

              // Поиск дочерних филиалов
              $queryPar = Company::find()->where(['parent_id' => $company_id])->all();

              $arrParParIds = [];

              for ($i = 0; $i < count($queryPar); $i++) {
                  $arrParParIds[] = $queryPar[$i]['id'];

                  $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                  for ($j = 0; $j < count($queryParPar); $j++) {
                      $arrParParIds[] = $queryParPar[$j]['id'];
                  }

              }
              //Поиск дочерних филиалов

              $queryPar = CompanyMember::find()->where(['OR', ['company_id' => $company_id], ['company_id' => $arrParParIds]])->andWhere(['show_member' => 1])->select('position, phone, email, name')->orderBy('id')->asArray()->all();

              return json_encode(['result' => json_encode($queryPar), 'error' => 0]);

          } else {

              return json_encode(['error' => 1]);
          }

      } else {

      return $this->redirect("http://docs.mtransservice.ru/site/index");

      }
    }
}