<?php
namespace console\controllers;

use common\models\Act;
use common\models\ActError;
use yii;
use yii\console\Controller;

class RoutineController extends Controller
{
    public function actionIndex()
    {
        $this->stdout("\n");
        $this->stdout("Routine controller \n");
        $this->stdout("\nActions: \n");
        $this->stdout('   errors' . " — marks acts with errors.\n");
        $this->stdout("\n");
    }

    public function actionErrors()
    {
        $listAct = Act::find()
            ->where(['>=', 'updated_at', time() - 30 * 24 * 3600])
            ->andWhere(['status' => Act::STATUS_NEW])
            ->all();

        foreach ($listAct as $act) {
            $listErrors = $act->getListError();
            ActError::deleteAll(['act_id' => $act->id]);
            foreach ($listErrors as $errorType) {
                $modelActError = new ActError();
                $modelActError->act_id = $act->id;
                $modelActError->error_type = $errorType;
                $modelActError->save();
            }
            if (count($listErrors)) {
                $this->stdout("$act->id is wrong!\n");
            }
        }
        
        $this->stdout("Errors is done!\n");
    }
}