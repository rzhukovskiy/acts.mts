<?php

/**
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $searchModel \common\models\search\CompanySearch
 * @var $type integer
 * @var $admin bool
 * @var $userList User[]
 */
use common\models\Company;
use kartik\grid\GridView;
use common\models\User;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Список
        <div class="header-btn pull-right">
        </div>
    </div>

    <div class="panel-body">
        <?php
        $form = ActiveForm::begin([
            'id' => 'offer-form',
            'method' => 'get',
            'options' => ['class' => 'form-horizontal col-sm-12', 'style' => 'margin: 20px 0;'],
            'fieldConfig' => [
                'template' => '{input}',
                'inputOptions' => ['class' => 'form-control input-sm'],
                'options' => ['class' => 'col-sm-3'],
            ],
        ]) ?>
        <?= $form->field($searchModelType, 'id')->dropdownList($listCar, ['multiple' => 'true', 'prompt' => 'Выберите типы ТС']); ?>
        <?= $form->field($searchModelService, 'id')->dropdownList($listService, ['multiple' => 'true', 'prompt' => 'Выберите типы услуг']); ?>
        <?= $form->field($searchModel, 'address')->dropdownList($listCity, ['multiple' => 'true', 'prompt' => 'Выберите город']); ?>
        <?= Html::submitButton('Применить', ['class' => 'btn btn-primary btn-sm']) ?>

        <?php ActiveForm::end() ?>
    </div>

    <div class="panel-body">
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'hover' => false,
            'striped' => false,
            'export' => false,
            'summary' => false,
            'emptyText' => '',
            'tableOptions' => ['class' => 'table table-bordered'],
            'layout' => '{items}',
            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn'
                ],
                [
                    'attribute' => 'address',
                    'group' => true,
                    'groupedRow' => true,
                    'groupOddCssClass' => 'kv-group-header',
                    'groupEvenCssClass' => 'kv-group-header',
                ],
                [
                    'header' => 'Организация',
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'fullAddress',
                    'content'   => function ($data) {
                        return ($data->fullAddress) ? $data->fullAddress : 'не задан';
                    }
                ],
                [
                    'attribute' => 'Стоимость услуг',
                    'content'   => function ($data) {

                        if ($data->type == 2) {

                            $Typelist = \common\models\Type::find()->asArray()->all();
                            $ServicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=1 OR service_id=2) AND price>0')->orderBy('company_id ASC')->asArray()->all();

                            $ArrayTypes = [];

                            foreach ($Typelist as $type) {
                                $ArrayTypes[$type['id']] = $type['name'];
                            }

                            $PriceArray = [];

                            $ResTypeCompany = '<table width="100%" border="1" bordercolor="#dddddd"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'2\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #dddddd;\'>Стоимость</td></tr>
<tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Снаружи</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>Внутри</td></tr>
</table>
</td></tr>';

                            foreach ($ServicesList as $service) {
                                $PriceArray[$service['type_id']][$service['service_id']] = $service['price'];
                            }

                            $Last_service_type = 0;

                            foreach ($ServicesList as $service) {

                                if (($service['price'] > 0) && ($Last_service_type != $service['type_id'])) {
                                    $type_id = $service['type_id'];
                                    $Last_service_type = $type_id;
                                    $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";
                                    $type_id = 0;

                                    if ((isset($PriceArray[$service['type_id']][1])) && (isset($PriceArray[$service['type_id']][2]))) {
                                        if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                        }
                                    } else if (isset($PriceArray[$service['type_id']][1])) {
                                        if ($PriceArray[$service['type_id']][1] > 0) {
                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                        }
                                    } else if (isset($PriceArray[$service['type_id']][2])) {
                                        if ($PriceArray[$service['type_id']][2] > 0) {
                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                        }
                                    }

                                }

                            }

                            $ResTypeCompany .= '</table>';

                            return $ResTypeCompany;

                        } else if($data->type == 4) {

                            $Typelist = \common\models\Type::find()->asArray()->all();
                            $ServicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=6 OR service_id=7 OR service_id=8 OR service_id=9) AND price>0')->orderBy('company_id ASC')->asArray()->all();

                            $ArrayTypes = [];

                            foreach ($Typelist as $type) {
                                $ArrayTypes[$type['id']] = $type['name'];
                            }

                            $PriceArray = [];

                            $ResTypeCompany = '<table width="100%" border="1" bordercolor="#dddddd"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'4\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #dddddd;\'>Стоимость</td></tr>
<tr><td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>Парное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>Балансировка</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>Полный</td></tr>
</table>
</td></tr>';

                            foreach ($ServicesList as $service) {
                                $PriceArray[$service['type_id']][$service['service_id']] = $service['price'];
                            }

                            $Last_service_type = 0;

                            foreach ($ServicesList as $service) {

                                if (($service['price'] > 0) && ($Last_service_type != $service['type_id'])) {
                                    $type_id = $service['type_id'];
                                    $Last_service_type = $type_id;
                                    $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";
                                    $type_id = 0;

                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                    if (isset($PriceArray[$service['type_id']][6])) {
                                        if ($PriceArray[$service['type_id']][6] > 0) {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][6] . '</td>';
                                        } else {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                        }
                                    } else {
                                        $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                    }

                                    if (isset($PriceArray[$service['type_id']][7])) {
                                        if ($PriceArray[$service['type_id']][7] > 0) {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][7] . '</td>';
                                        } else {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>-</td>';
                                        }
                                    } else {
                                        $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>-</td>';
                                    }

                                    if (isset($PriceArray[$service['type_id']][9])) {
                                        if ($PriceArray[$service['type_id']][9] > 0) {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][9] . '</td>';
                                        } else {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>-</td>';
                                        }
                                    } else {
                                        $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px; padding-right:5px;\'>-</td>';
                                    }

                                    if (isset($PriceArray[$service['type_id']][8])) {
                                        if ($PriceArray[$service['type_id']][8] > 0) {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>' . $PriceArray[$service['type_id']][8] . '</td>';
                                        } else {
                                            $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>-</td>';
                                        }
                                    } else {
                                        $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #dddddd; padding-left:5px;\'>-</td>';
                                    }

                                    $ResTypeCompany .= '</tr></table></td></tr>';

                                }

                            }

                            $ResTypeCompany .= '</table>';

                            return $ResTypeCompany;
                        } else {
                            return '-';
                        }

                    }
                ],
                [
                    'attribute' => 'Галочка',
                    'content'   => function ($data) {
                        return '';
                    }
                ],
            ],
        ]);
        ?>
    </div>
</div>