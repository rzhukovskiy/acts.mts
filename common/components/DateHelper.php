<?php

namespace common\components;

class DateHelper
{
    static $months = [
        1 => ['Январь', 'Января', 'Январе', 'январь'],
        2 => ['Февраль', 'Февраля', 'Феврале', 'февраль'],
        3 => ['Март', 'Марта', 'Марте', 'март'],
        4 => ['Апрель', 'Апреля', 'Апреле', 'апрель'],
        5 => ['Май', 'Мая', 'Мае', 'май'],
        6 => ['Июнь', 'Июня', 'Июне', 'июнь'],
        7 => ['Июль', 'Июля', 'Июле', 'июль'],
        8 => ['Август', 'Августа', 'Августе', 'август'],
        9 => ['Сентябрь', 'Сентября', 'Сентябре', 'сентябрь'],
        10 => ['Октябрь', 'Октября', 'Октябре', 'октябрь'],
        11 => ['Ноябрь', 'Ноября', 'Ноябре', 'ноябрь'],
        12 => ['Декабрь', 'Декабря', 'Декабре', 'декабрь']
    ];
    
    static $listWeekDay = ['0', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    public static function getMonthName($date = false, $position = null)
    {
        if (is_string($date))
            $unixTimeStamp = strtotime($date);
        else
            $unixTimeStamp = $date;

        if (!$unixTimeStamp)
            $num = date('m');
        else
            $num = date('m', (int)$unixTimeStamp);

        $date = self::$months[(int)$num];
        if (!is_null($position))
            $date = $date[$position];

        return $date;
    }

    public static function getMonthNameByNum($num = false)
    {
        // Если не задано время в UNIX, то используем текущий
        if (!$num)
            $num = date('m');

        return self::$months[(int)$num];
    }
    
    public static function getWeekDayName($day = false)
    {
        if (!$day) {
            $day = date('w') ? date('w') : 7;
        }
        return self::$listWeekDay[$day];
    }
}