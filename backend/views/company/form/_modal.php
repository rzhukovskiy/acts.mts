<?php

/**
 * @var $this yii\web\View
 * @var $modelCompany common\models\Company
 * @var $modelCompanyInfo common\models\CompanyInfo
 * @var $modelCompanyOffer common\models\CompanyOffer
 * @var $admin bool
 */
use kartik\time\TimePicker;
?>

<div class="modaltime" style="display: none;">
    <div style="/*width: 50%;float: left;*/">
        <div class='radio'>
        <label><input type='radio' name='optradio' class="inputtimer" >Круглосуточно</label>
        </div>
        <div class='radio'><label><input type='radio' name='optradio')>Каждый день</label>
        </div>
        <div class='radio'><label><input type='radio' name='optradio' >Другой</label>
        </div>
    </div>
    <div id="everyday" style="display: none;/*width: 50%;float: left;*/">
        <div style="float: left;padding-top: 10px;width: 5%">C  </div>
        <div style="float: left;width: 25%"><? 
        echo TimePicker::widget([
            'name' => 'start_time', 
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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
            'value' => '00:00',
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