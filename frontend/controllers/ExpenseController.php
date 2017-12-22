<?php

namespace frontend\controllers;

use common\models\Expense;
use common\models\ExpenseCompany;
use common\models\search\ExpenseCompanySearch;
use common\models\search\ExpenseSearch;
use common\models\User;
use yii\filters\AccessControl;
use yii\web\Controller;
use Yii;


class ExpenseController extends Controller
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
                        'actions' => ['addexpense', 'addexpensecomp', 'expensecomp', 'updateexpense', 'fullexpense', 'updateexp', 'statexpense', 'stattotal', 'delete','duplicate'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['addexpense', 'addexpensecomp', 'expensecomp', 'updateexpense', 'fullexpense', 'updateexp', 'statexpense', 'stattotal', 'delete','duplicate'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ]
            ]
        ];
    }




    public function actionAddexpensecomp($type)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {
            $model = new ExpenseCompany();
            $model->type = $type;

            $searchModel = new ExpenseCompanySearch();

            $params = Yii::$app->request->queryParams;
            $params['ExpenseCompanySearch']['type'] = $type;

            $dataProvider = $searchModel->search($params);

            $listType = ExpenseCompany::$listType;

            if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

                return $this->redirect(['expense/addexpensecomp', 'type' => $type]);

            } else {

                return $this->render('addexpensecomp',
                    [
                        'model' => $model,
                        'listType' => $listType,
                        'searchModel' => $searchModel,
                        'dataProvider' => $dataProvider,
                    ]);
            }
        } else {
            return $this->redirect(['/']);
        }
    }

    public function actionExpensecomp($id)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {
        $model = ExpenseCompany::findOne(['id' => $id]);

        $newmodel = new Expense();

        $searchModel = new ExpenseSearch(['scenario' => Expense::SCENARIO_ADD]);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $dataProvider->query->andWhere(['expense_company' => $model->id]);

        return $this->render('expensecomp',
            [
                'model' => $model,
                'newmodel' => $newmodel,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            return $this->redirect(['/']);
        }
    }

    public function actionUpdateexpense($id)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {
        $model = ExpenseCompany::findOne(['id' => $id]);


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['expense/expensecomp', 'id' => $model->id]);
        }
    } else {
         return $this->redirect(['/']);
        }
    }

    public function actionAddexpense($id)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {
        $model = new Expense();
        $model->expense_company = $id;

        if (($model->load(Yii::$app->request->post())) && ($model->save()) && (Yii::$app->request->isPost)) {

            return $this->redirect(['expense/expensecomp', 'id' => $id]);

        }
    } else {
         return $this->redirect(['/']);
        }
    }

    public function actionFullexpense($id)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {
        $model = Expense::findOne(['id' => $id]);
        $expense_company = $model->expense_company;

        return $this->render('fullexpense', [
            'model' => $model,
            'expense_company' => $expense_company,
        ]);
    } else {
        return $this->redirect(['/']);
        }
    }

    public function actionUpdateexp($id)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {
        $model = Expense::findOne(['id' => $id]);
        $expense_company = $model->expense_company;

        // Подготовка данных перед сохранением
        $arrUpdate = Yii::$app->request->post();


        foreach ($arrUpdate['Expense'] as $name => $value) {
            if($name == 'date') {
                $arrUpdate['Expense'][$name] = (String) strtotime($value);
            }
        }

        if ($model->load($arrUpdate) && $model->save()) {
            return $this->redirect(['expense/expensecomp', 'id' => $expense_company]);
        }
        } else {
            return $this->redirect(['/']);
        }
    }

    public function actionDuplicate($type)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {

            $month = date('m', time()) - 1;
            $nowMonth = date('n', time());
            $year = date('Y', time());

            if ($nowMonth == 1) {
                $year = date('Y', time()) - 1;
                $month = 12;
            }

            $array = Expense::find()->where(['type' => $type])->andWhere(["MONTH(FROM_UNIXTIME(date))" => $month, "YEAR(FROM_UNIXTIME(date))" => $year])->asArray()->all();

            for ($i = 0; $i < count($array); $i++) {

                    $checkExpense = Expense::find()->where(['AND', ['type' => $type], ['sum' => $array[$i]['sum']],['expense_company' => $array[$i]['expense_company']]])->andWhere(["DAY(FROM_UNIXTIME(date))" => '01', "MONTH(FROM_UNIXTIME(date))" => date('m', time()), "YEAR(FROM_UNIXTIME(date))" => date('Y', time())])->exists();

                    if ($checkExpense == false) {

                    $model = new Expense;
                    $model->type = $type;
                    $model->date = date('01-m-Y', time());
                    $model->expense_company = $array[$i]['expense_company'];
                    $model->sum = $array[$i]['sum'];

                if ($type != 1) {
                    $model->description = $array[$i]['description'];
                }

                $model->save();
                 }
            }
            return $this->redirect(['expense/addexpensecomp', 'type' => $type]);
        }
    }

    public function actionStatexpense($type)
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {

            $listType = ExpenseCompany::$listType;

            $searchModel = new ExpenseSearch(['scenario' => Expense::SCENARIO_STAT]);
            $searchModel->type = $type;
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('statexpense',
                [
                    'listType' => $listType,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
        } else {
            return $this->redirect(['/']);
        }
    }

    public function actionStattotal()
    {
        if ((Yii::$app->user->identity->role == User::ROLE_ADMIN) || (Yii::$app->user->identity->id == 708)) {

            $searchModel = new ExpenseSearch(['scenario' => Expense::SCENARIO_TOTAL]);
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            $listType = ExpenseCompany::$listType;

            $profit = Yii::$app->db->createCommand("SELECT SUM(profit) as profit FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $searchModel->dateFrom . "' AND '" . $searchModel->dateTo . "')")->queryColumn();
            return $this->render('stattotal',
                [
                    'listType' => $listType,
                    'profit' => $profit,
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
                ]);
        } else {
            return $this->redirect(['/']);
        }
    }

    public function actionDelete($id)
    {
        Expense::findOne(['id' => $id])->delete();

        // Удаляем
        Yii::$app->db->createCommand()->delete('{{%expense}}', ['id' => $id])->execute();

        return $this->redirect(Yii::$app->request->referrer);
    }

}