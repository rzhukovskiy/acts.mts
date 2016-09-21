<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 26.08.2016
 * Time: 2:11
 */

namespace common\components;

class ArrayHelper extends \yii\helpers\ArrayHelper
{
    public static function perMutate($items)
    {
        $arr = array_values($items);
        $keys = array_flip($items);

        $num = count($arr);
        $total = pow(2, $num);
        $values = [];
        $res = [];
        for ($i = 0; $i < $total; $i++) {
            for ($j = 0; $j < $num; $j++) {
                if (pow(2, $j) & $i) {
                    $values[$i]['values'][] = $arr[$j];
                    $values[$i]['keys'][] = $keys[$arr[$j]];
                }
            }

            if ($i && count($values[$i]['values'])) {
                $res[implode('+', $values[$i]['keys'])] = implode('+', $values[$i]['values']);
            }
        }

        return $res;
    }
}