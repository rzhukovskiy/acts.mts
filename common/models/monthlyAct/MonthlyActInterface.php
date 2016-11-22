<?php

namespace common\models\monthlyAct;

use common\models\MonthlyAct;


/**
 * ServiceMonthlyAct
 */
interface MonthlyActInterface
{
    /**
     * @param $act \common\models\Act
     * @return mixed
     */
    public function saveFromAct($act);

    /**
     * @return int
     */
    public function getProfit();
}