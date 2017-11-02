<?php

namespace frontend\controllers;

use common\models\CompanyMember;
use common\models\search\CompanyMemberSearch;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\filters\AccessControl;
use common\models\User;

/**
 * MemberController implements the CRUD actions for Member model.
 */
class MemberController extends Controller
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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['memberslist'],
                        'allow' => true,
                        'roles' => [User::ROLE_CLIENT],
                    ],

                ]
            ]
        ];
    }

    // Сотрудники
    public function actionMemberslist()
    {

        //  Раздел сотрудники только для клиентов
        if (Yii::$app->user->identity->role == User::ROLE_CLIENT) {

        $id = Yii::$app->user->identity->company_id;


        $modelCompanyMember = new CompanyMember();
        $modelCompanyMember->company_id = $id;

        $searchModel = new CompanyMemberSearch();
        $searchModel->company_id = $id;

        $dataProvider = $searchModel->searchMemberlist(Yii::$app->request->queryParams);

        return $this->render( 'memberslist', [
            'model' => $modelCompanyMember,
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
        } else {
            return $this->redirect("/");
        }
    }
}
