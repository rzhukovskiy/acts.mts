<?php
namespace console\controllers;

use common\models\Act;
use common\models\MonthlyAct;
use Yii;
use yii\console\Controller;

class MonthlyActController extends Controller
{
    public function actionCreateAll()
    {
        $allAct = Act::find()->all();
        foreach ($allAct as $act) {
            MonthlyAct::getRealObject($act->service_type)->saveFromAct($act);
        }
        echo "Monthly Acts successfully created!\n";

        return 0;
    }

    /**
     *
     */
    public function actionClear()
    {
        MonthlyAct::deleteAll();
    }
}