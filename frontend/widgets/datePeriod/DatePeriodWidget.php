<?php
/**
 * @property string $actionUrl
 * @property string $field
 * @property \yii\db\ActiveRecord $class
 * @property array $field
 */

namespace frontend\widgets\datePeriod;

use common\models\Act;
use yii;
use yii\base\Widget;
use yii\helpers\Html;

class DatePeriodWidget extends Widget
{
    public $baseUrl;
    public $dateFromAttr;
    public $dateToAttr;
    public $model;
    public $periodList;

    const PERIOD_ALL = 0;
    const PERIOD_MONTH = 1;
    const PERIOD_QUARTER = 2;
    const PERIOD_HALF_YEAR = 3;
    const PERIOD_YEAR = 4;

    const START_YEAR_FOR_ALL_YEAR = 2013;

    public static $halves = [
        '1е полугодие',
        '2е полугодие'
    ];
    public static $quarters = [
        '1й квартал',
        '2й квартал',
        '3й квартал',
        '4й квартал',
    ];
    public static $months = [
        'январь',
        'февраль',
        'март',
        'апрель',
        'май',
        'июнь',
        'июль',
        'август',
        'сентябрь',
        'октябрь',
        'ноябрь',
        'декабрь',
    ];

    public function init()
    {
        parent::init();
        $this->registerAssets();
    }

    public function publishAssets()
    {
        $dir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'asset';
        $this->baseUrl = Yii::$app->getAssetManager()->publish($dir);
    }

    /**
     * Добавляем свои скрипты
     */
    public function registerAssets()
    {
        $view = $this->getView();
        DatePeriodAsset::register($view);
    }


    /**
     * Запуск виджета
     */
    public function run()
    {
        $this->registerAssets();
        $this->periodList = Act::$periodList;

        return 'Выбор периода: ' . $this->createField();

    }

    /**
     * @return string
     */
    private function createField()
    {
        $ts1 = strtotime($this->model->{$this->dateFromAttr});
        $ts2 = strtotime($this->model->{$this->dateToAttr});

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        $diff = (($year2 - $year1) * 12) + ($month2 - $month1);
        switch ($diff) {
            case 1:
                $period = self::PERIOD_MONTH;
                break;
            case 3:
                $period = self::PERIOD_QUARTER;
                break;
            case 6:
                $period = self::PERIOD_HALF_YEAR;
                break;
            case 12:
                $period = self::PERIOD_YEAR;
                break;
            default:
                $period = self::PERIOD_ALL;
        }

        $periodForm = '';
        $periodForm .= Html::dropDownList('period',
            $period,
            $this->periodList,
            ['class' => 'select-period form-control', 'style' => 'margin-right: 10px;']);
        $periodForm .= Html::dropDownList('month',
            '',
            self::$months,
            ['id' => 'month', 'class' => 'autoinput form-control', 'style' => $diff == 1 ? '' : 'display:none']);
        $periodForm .= Html::dropDownList('half',
            '',
            self::$halves,
            ['id' => 'half', 'class' => 'autoinput form-control', 'style' => $diff == 6 ? '' : 'display:none']);
        $periodForm .= Html::dropDownList('quarter',
            '',
            self::$quarters,
            ['id' => 'quarter', 'class' => 'autoinput form-control', 'style' => $diff == 3 ? '' : 'display:none']);
        $periodForm .= Html::dropDownList('year',
            10,
            range(date('Y') - 10, date('Y')),
            [
                'id'    => 'year',
                'class' => 'autoinput form-control',
                'style' => $diff && $diff <= 12 ? '' : 'display:none'
            ]);

        $periodForm .= Html::activeTextInput($this->model,
            $this->dateFromAttr,
            ['class' => 'date-from ext-filter hidden']);
        $periodForm .= Html::activeTextInput($this->model, $this->dateToAttr, ['class' => 'date-to ext-filter hidden']);
        $periodForm .= Html::submitButton('Показать',
            ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

        return $periodForm;
    }


    /**
     * Подбираем оптимальное максимальное значение и шаг
     * @param $maximum
     * @return array
     */
    static function getMaxAndStep($maximum)
    {
        $maximum = (int)$maximum;
        //var_dump($maximum);
        $maxFlor = pow(10, strlen($maximum) - 1);
        //var_dump($maxFlor);
        $maxPrepare = $maximum - $maximum % $maxFlor;
        //var_dump($maxPrepare);
        $step = round(($maxPrepare / $maxFlor) / 10);
        //var_dump($step);
        if ($step == 1) {
            $step = $maxFlor;
        } else {
            $step = $maxFlor / 2;
        }
        $maximum = ($maximum - $maximum % $step) + $step;

        return [$maximum, $step];
    }

    /**
     * Меняем подпись под графиком, в зависимости от типа
     * @param $period
     * @return string
     */
    static function getXFormat($period)
    {
        if (in_array($period, [DatePeriodWidget::PERIOD_ALL, DatePeriodWidget::PERIOD_YEAR])) {
            $xFormat['valueFormatString'] = "YYYY/MM";
            $xFormat['interval'] = '1';
            $xFormat['intervalType'] = "month";
        } elseif ($period == DatePeriodWidget::PERIOD_HALF_YEAR) {
            $xFormat['valueFormatString'] = "YYYY/MM/DD";
            $xFormat['interval'] = '1';
            $xFormat['intervalType'] = "month";
        } elseif ($period == DatePeriodWidget::PERIOD_QUARTER) {
            $xFormat['valueFormatString'] = "YYYY/MM/DD";
            $xFormat['interval'] = '10';
            $xFormat['intervalType'] = "day";
        } else {
            $xFormat['valueFormatString'] = "YYYY/MM/DD";
            $xFormat['interval'] = '5';
            $xFormat['intervalType'] = "day";
        }


        return $xFormat;
    }

    /**
     * Набираем графики и максимум
     * @param $data
     * @return array
     */
    static function getDataForGraph($data)
    {
        $expenseData = $profitData = $incomeData = [];
        $expense = $profit = $income = [
            'type'          => "'line'",
            'lineThickness' => 3,
            'axisYType'     => "'secondary'",
            'showInLegend'  => true,
        ];
        $maximum = 0;
        $minimalYear = \DateTime::createFromFormat('Y', self::START_YEAR_FOR_ALL_YEAR)->getTimestamp() * 1000;

        foreach ($data as $key => $val) {
            $date = \DateTime::createFromFormat('Y-m-d', $val['date'])->modify('+1 day')->getTimestamp() * 1000;
            if ($date < $minimalYear) {
                continue;
            }
            $maximum = max($maximum, $val['expense'], $val['profit'], $val['income']);
            $tmp['x'] = "new Date(" . $date . ")";
            $tmp['y'] = $val['expense'];
            $expenseData[] = (object)$tmp;
            $tmp['y'] = $val['profit'];
            $profitData[] = (object)$tmp;
            $tmp['y'] = $val['income'];
            $incomeData[] = (object)$tmp;
        }

        $expense['name'] = "'Расход'";
        $expense['dataPoints'] = $expenseData;
        $profit['name'] = "'Прибыль'";
        $profit['dataPoints'] = $profitData;
        $income['name'] = "'Доход'";
        $income['dataPoints'] = $incomeData;
        $data = [$income, $expense, $profit];
        $data = str_replace('"', '', json_encode($data, true));

        return [$data, $maximum];
    }
}
