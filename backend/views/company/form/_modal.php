<?php

/**
 * @var $this yii\web\View
 * @var $modelCompany common\models\Company
 * @var $modelCompanyInfo common\models\CompanyInfo
 * @var $modelCompanyOffer common\models\CompanyOffer
 * @var $admin bool
 */
use kartik\time\TimePicker;
$string = $modelCompany->WorkTime1;
//print_r($string);
?>

<div class="modaltime" style="display: none;">
    <div style="/*width: 50%;float: left;*/">
        <div class='radio'>
        <label><input id="radio1" type='radio' name='optradio' class="inputtimer" value="val1">Круглосуточно</label>
        </div>
        <div id="radio2" class='radio'><label><input type='radio' name='optradio' value="val2">Каждый день</label>
        </div>
        <div id="radio3" class='radio'><label><input type='radio' name='optradio' value="val3">Другой</label>
        </div>
    </div>
    <div id="everyday" style="display: none;/*width: 50%;float: left;*/">
        <div style="float: left;padding-top: 10px;width: 5%">C  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[0],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[1],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>

    </div>
    <div id="anyday" style="display: none;/*width: 50%;float: left;*/">
        <div style="float: left;padding-top: 10px;width: 13%">Пон с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[0],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[1],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;padding-top: 10px;width: 13%">ВТ с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[2],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[3],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;padding-top: 10px;width: 13%">СР с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[4],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[5],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;padding-top: 10px;width: 13%">Чет с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[6],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[7],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;padding-top: 10px;width: 13%">Пт с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[8],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[9],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;padding-top: 10px;width: 13%">Сб с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[10],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[11],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="clear: both;"></div>
        <div style="float: left;padding-top: 10px;width: 13%">Воск с  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[12],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
        <div style="float: left;width: 5%;padding-top: 10px;margin-left: 10px;margin-right: 10px">
         По
        </div>
        <div style="float: left;width: 25%">
        <? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => $string[13],
            'pluginOptions' => [
                'showSeconds' => false,
                'showMeridian'=>false,

            ]
        ]);
        ?>
        </div>
    </div>
    <div style="clear: both;"></div>
</div>