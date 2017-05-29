<?php

namespace frontend\controllers;

use common\models\Act;
use common\models\Car;
use common\models\search\ActSearch;
use common\models\Service;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ErrorController extends Controller
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
                        'actions' => ['list', 'update', 'delete', 'view', 'numberlist'],
                        'allow' => true,
                        'roles' => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['list', 'view', 'numberlist'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER,User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['list', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_WATCHER],
                    ],
                ],
            ],
        ];
    }

    public function actionList($type)
    {
        $searchModel = new ActSearch(['scenario' => Act::SCENARIO_ERROR]);
        $searchModel->service_type = $type;

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('list', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'type' => $type,
            'role' => Yii::$app->user->identity->role,
            'admin' => Yii::$app->user->can(User::ROLE_ADMIN),
        ]);
    }

    /**
     * Updates Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->time_str = date('d-m-Y', $model->served_at);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(Yii::$app->request->post('__returnUrl'));
        } else {
            $clientScopes = $model->getClientScopes()->all();
            $partnerScopes = $model->getPartnerScopes()->all();

            $serviceList = Service::find()->where(['type' => $model->service_type])->select(['description', 'id'])->indexBy('id')->column();
            return $this->render('update', [
                'model' => $model,
                'serviceList' => $serviceList,
                'clientScopes' => $clientScopes,
                'partnerScopes' => $partnerScopes,
            ]);
        }
    }

    /**
     * Shows Act model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Act model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->status = Act::STATUS_FIXED;
        $model->save();

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionNumberlist($number, $mark, $type, $card, $actType)
    {

        $arrRes = [];
        $resType = 0;

        if($card > 0) {

            if(mb_strlen($number) >= 8) {

                $likeString = '(`car`.`number` LIKE \'%' . $number . '%\')';

                for($j = 0; $j <= mb_strlen($number); $j++) {

                    $tmpNumber = mb_substr($number, 0, $j) . '%' . mb_substr($number, ($j + 1), (mb_strlen($number) - ($j + 1)));

                    if ($j == mb_strlen($number)) {
                    } else {
                        $likeString .= ' OR (`car`.`number` LIKE \'' . $tmpNumber . '\')';
                    }

                }

                $carRes = Car::find()->innerJoin('card', 'card.company_id = car.company_id')->where(['card.number' => $card])->andWhere($likeString)->andWhere(['car.mark_id' => $mark])->andWhere(['car.type_id' => $type])->select('car.number')->all();

                $maxI = count($carRes);

                if($maxI > 3) {
                    $maxI = 3;
                }

                for($i = 0; $i < $maxI; $i++) {
                    $arrRes[] = $carRes[$i]['number'];
                }

            } else {

                $carRes = Car::find()->innerJoin('card', 'card.company_id = car.company_id')->where(['card.number' => $card])->andWhere(['like', 'car.number', $number])->andWhere(['car.mark_id' => $mark])->andWhere(['car.type_id' => $type])->select('car.number')->all();

                $maxI = count($carRes);

                if($maxI > 3) {
                    $maxI = 3;
                }

                for($i = 0; $i < $maxI; $i++) {
                    $arrRes[] = $carRes[$i]['number'];
                }

            }

            if(count($arrRes) == 0) {
                $resType = 1;
                $carRes = Act::find()->innerJoin('card', 'card.id = act.card_id')->where(['card.number' => $card])->andWhere(['act.mark_id' => $mark])->andWhere(['act.type_id' => $type])->andWhere(['act.service_type' => $actType])->select('act.car_number')->all();

                $maxI = count($carRes);

                if($maxI > 3) {
                    $maxI = 3;
                }

                for($i = 0; $i < $maxI; $i++) {
                    $arrRes[] = $carRes[$i]['car_number'];
                }

            }

        } else {

            if(mb_strlen($number) >= 8) {

                $likeString = '(`car`.`number` LIKE \'%' . $number . '%\')';

                for($j = 0; $j <= mb_strlen($number); $j++) {

                    $tmpNumber = mb_substr($number, 0, $j) . '%' . mb_substr($number, ($j + 1), (mb_strlen($number) - ($j + 1)));

                    if ($j == mb_strlen($number)) {
                    } else {
                        $likeString .= ' OR (`car`.`number` LIKE \'' . $tmpNumber . '\')';
                    }

                }

                $carRes = Car::find()->where($likeString)->andWhere(['mark_id' => $mark])->andWhere(['type_id' => $type])->select('number')->all();

                $maxI = count($carRes);

                if($maxI > 3) {
                    $maxI = 3;
                }

                for($i = 0; $i < $maxI; $i++) {
                    $arrRes[] = $carRes[$i]['number'];
                }

            } else {

                $carRes = Car::find()->where(['like', 'number', $number])->andWhere(['mark_id' => $mark])->andWhere(['type_id' => $type])->select('number')->all();

                $maxI = count($carRes);

                if($maxI > 3) {
                    $maxI = 3;
                }

                for($i = 0; $i < $maxI; $i++) {
                    $arrRes[] = $carRes[$i]['number'];
                }

            }

        }

        echo json_encode(['success' => 'true', 'listCar' => $arrRes, 'resType' => $resType]);
    }

    /**
     * Finds the Act model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Act the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Act::findOne($id)) !== null) {
            if (
                Yii::$app->user->can(User::ROLE_ADMIN) ||
                Yii::$app->user->can(User::ROLE_WATCHER) ||
                Yii::$app->user->identity->company_id == $model->partner_id ||
                Yii::$app->user->identity->company_id == $model->client_id
            ) {
                return $model;
            }
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
