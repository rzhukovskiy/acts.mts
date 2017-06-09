<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 09.08.2016
 * Time: 0:25
 */

namespace backend\controllers;

use common\components\ArrayHelper;
use common\models\Company;
use common\models\MonthlyAct;
use common\models\search\MonthlyActSearch;
use common\models\Service;
use common\models\User;
use common\models\ActData;
use yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Json;

class MonthlyActController extends Controller
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
                        'actions' => ['delete', 'delete-image', 'ajax-act-status', 'ajax-payment-status', 'archive', 'searchact'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['update', 'detail', 'list', 'archive', 'searchact'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_WATCHER, User::ROLE_MANAGER],
                    ],
                    [
                        'actions' => ['detail', 'list'],
                        'allow'   => true,
                        'roles'   => [User::ROLE_ACCOUNT],
                    ],
                ],
            ],
        ];
    }


    public function actionList($type, $company = 0)
    {
        $searchModel = new MonthlyActSearch();
        $searchModel->type_id = $type;

        $params = Yii::$app->request->queryParams;

        if(isset($params['filterStatus'])) {

            if($params['filterStatus'] == 1) {
                $searchModel->payment_status = 0;
            } else if($params['filterStatus'] == 2) {
                $searchModel->act_status = 0;
            } else if($params['filterStatus'] == 3) {
                $searchModel->payment_status = 15;
            } else if($params['filterStatus'] == 4) {
                $searchModel->act_status = 5;
            } else if($params['filterStatus'] == 5) {
                $searchModel->act_status = 4;
            } else if($params['filterStatus'] == 6) {
                $searchModel->act_status = 2;
            } else if($params['filterStatus'] == 7) {
                $searchModel->act_status = 3;
            } else if($params['filterStatus'] == 8) {
                $searchModel->act_status = 1;
            }

        }

        $dataProvider = $searchModel->search($params);
        //Запоминаем
        $this->setSessionDate($searchModel->act_date);

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Service::$listType;
        } else {
            $listType = Yii::$app->user->identity->getAllServiceType(Company::STATUS_ACTIVE);
        }

        $currentUser = Yii::$app->user->identity;
        if ($currentUser && $currentUser->role == User::ROLE_ADMIN) {
            $admin = true;
        } else {
            $admin = false;
        }

        return $this->render('list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'type'         => $type,
                'company'      => $company,
                'listType'     => $listType,
                'admin'        => $admin,
            ]);
    }

    public function actionArchive($type)
    {
        $searchModel = new MonthlyActSearch();
        $searchModel->type_id = $type;
        // $searchModel->scenario = 'statistic_filter';
        $searchModel->period = Yii::$app->request->get('period');
        $dataProvider = $searchModel->searchArchive(Yii::$app->request->queryParams);

        $models = $dataProvider->getModels();
        $totalProfit = array_sum(ArrayHelper::getColumn($models, 'profit'));
        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $listType = Company::$listType;
            array_pop($listType);
        } else {
            $listType = Yii::$app->user->identity->getAllServiceType(Company::STATUS_ACTIVE);
        }


        return $this->render('archive/list',
            [
                'dataProvider' => $dataProvider,
                'searchModel'  => $searchModel,
                'totalProfit'  => $totalProfit,
                'listType'     => $listType,
                'type'         => $type
            ]);
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
        if ($model->load(Yii::$app->request->post())) {
            $model->image = yii\web\UploadedFile::getInstances($model, 'image');
            $model->uploadImage();
            if ($model->save()) {
                $redirect = [
                    'list',
                    'type'                       => $model->type_id,
                    'company'                    => !$model->is_partner,
                    'MonthlyActSearch[act_date]' => $this->getSessionDate()
                ];

                return $this->redirect($redirect);
            }
        }

        return $this->render('update',
            [
                'model' => $this->findModel($id)
            ]);

    }

    /**
     * Updates an existing Company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDetail($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'detail';

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $redirect = [
                'list',
                'type'                       => $model->type_id,
                'company'                    => !$model->is_partner,
                'MonthlyActSearch[act_date]' => $this->getSessionDate()
            ];

            return $this->redirect($redirect);
        }

        return $this->render('detail',
            [
                'model' => $this->findModel($id)
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
        $this->findModel($id)->delete();

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionSearchact()
    {

        if(Yii::$app->request->post('number')) {
            if(mb_strlen(Yii::$app->request->post('number')) > 0) {

                $number = Yii::$app->request->post('number');
                $resLink = '/monthly-act/list?type=';

                if(is_numeric($number[0])) {
                    $arrActData = ActData::find()->where(['id' => $number])->select('type, company, period')->all();

                    if(count($arrActData) > 0) {

                        if((isset($arrActData[0]['type'])) && (isset($arrActData[0]['company'])) && (isset($arrActData[0]['period']))) {

                            if ((($arrActData[0]['type'] == 2) || ($arrActData[0]['type'] == 3) || ($arrActData[0]['type'] == 4) || ($arrActData[0]['type'] == 5)) && (($arrActData[0]['company'] == 0) || ($arrActData[0]['company'] == 1))) {

                                $resLink .= $arrActData[0]['type'];

                                if ($arrActData[0]['company'] == 1) {
                                    $resLink .= '&company=1';
                                }

                                $period = explode('-', $arrActData[0]['period']);

                                if ($period[0][0] == 0) {
                                    $period = mb_substr($arrActData[0]['period'], 1);
                                } else {
                                    $period = $arrActData[0]['period'];
                                }

                                $resLink .= '&MonthlyActSearch%5Bact_date%5D=' . $period;

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

                    $arrActData = ActData::find()->where(['number' => $number])->select('type, company, period, number')->all();

                    if(count($arrActData) > 0) {

                        if((isset($arrActData[0]['type'])) && (isset($arrActData[0]['company'])) && (isset($arrActData[0]['period'])) && (isset($arrActData[0]['number']))) {

                            if ((($arrActData[0]['type'] == 2) || ($arrActData[0]['type'] == 3) || ($arrActData[0]['type'] == 4) || ($arrActData[0]['type'] == 5)) && (($arrActData[0]['company'] == 0) || ($arrActData[0]['company'] == 1))) {

                                $resLink .= $arrActData[0]['type'];

                                if ($arrActData[0]['company'] == 1) {
                                    $resLink .= '&company=1';
                                }

                                $period = explode('-', $arrActData[0]['period']);

                                if ($period[0][0] == 0) {
                                    $period = mb_substr($arrActData[0]['period'], 1);
                                } else {
                                    $period = $arrActData[0]['period'];
                                }

                                $resLink .= '&MonthlyActSearch%5Bact_date%5D=' . $period;

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

                echo json_encode(['success' => 'true', 'link' => $resLink]);
            } else {
                echo json_encode(['success' => 'false']);
            }
        } else {
            echo json_encode(['success' => 'false']);
        }

    }

    /**
     * @param $id
     * @param $url
     * @return yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDeleteImage($id, $url)
    {
        $model = $this->findModel($id);
        $model->deleteImage($url);
        $model->save();

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionAjaxPaymentStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->payment_status = $status;
        $model->save();

        return MonthlyAct::colorForPaymentStatus($model->payment_status);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function actionAjaxActStatus()
    {
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $model = $this->findModel($id);
        $model->act_status = $status;
        $model->save();

        $pass['color'] = MonthlyAct::colorForStatus($model->act_status);
        $pass['value'] = $status;
        return Json::encode($pass);
    }

    /**
     * Finds the Company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MonthlyAct the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MonthlyAct::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @return mixed
     */
    protected function getSessionDate()
    {
        return Yii::$app->session->get($this->id . "_act_date");
    }

    /**
     * @param $actDate
     */
    protected function setSessionDate($actDate)
    {
        Yii::$app->session->set($this->id . "_act_date", $actDate);
    }

    protected function removeSessionDate()
    {
        Yii::$app->session->remove($this->id . "_act_date");
    }

}