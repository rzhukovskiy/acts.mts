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
        <table border="0" width="100%" style="margin-top:20px;">
            <tr><td width="25%" style="padding-left: 40px; font-size:15px;">Выберите типы ТС:</td>
                <td width="25%" style="padding-left: 30px; font-size:15px;">Выберите типы услуг:</td>
                <td width="25%" style="padding-left: 25px; font-size:15px;">Выберите город:</td>
                <td width="25%" style="padding-left: 25px; font-size:15px;"></td></tr>
        </table>
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
        <?= $form->field($searchModel, 'cartypes')->dropdownList($listCar, ['multiple' => 'true']); ?>
        <?= $form->field($searchModel, 'services')->dropdownList($listService, ['multiple' => 'true']); ?>
        <?= $form->field($searchModel, 'address')->dropdownList($listCity, ['multiple' => 'true']); ?>
        <?= Html::submitButton('Применить', ['class' => 'btn btn-primary btn-sm']) ?>
        <?= Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 10px;">Сбросить</span>', '/company/offer?type=' . Yii::$app->request->get('type')); ?>

        <?php ActiveForm::end() ?>
    </div>

    <div class="panel-body">
        <?php

        $GLOBALS['getParams'] = Yii::$app->request->get('CompanySearch');

        echo GridView::widget([
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
                    'options' => [
                        'style' => 'width: 320px',
                    ],
                    'attribute' => 'name',
                ],
                [
                    'attribute' => 'fullAddress',
                    'options' => [
                        'style' => 'width: 315px',
                    ],
                    'content'   => function ($data) {
                        return ($data->fullAddress) ? $data->fullAddress : 'не задан';
                    }
                ],
                [
                    'attribute' => 'Стоимость услуг',
                    'content'   => function ($data) {

                        // Список типов машин
                        $carTypes = $GLOBALS['getParams']['cartypes'];

                        // удаляем пустые значения из массива
                       for($i = 0; $i < count($carTypes); $i++) {
                          if(isset($carTypes[$i])) {
                               if ($carTypes[$i] > 0) {

                               } else {
                                  unset($carTypes[$i]);
                                }
                            } else {
                              if(count($carTypes) == 1) {
                                   $carTypes = [];
                                }
                            }
                        }
                        // удаляем пустые значения из массива

                        // Список типов машин

                        // Список услуг
                        $services = $GLOBALS['getParams']['services'];

                        // удаляем пустые значения из массива
                        for($i = 0; $i < count($services); $i++) {
                            if(isset($services[$i])) {
                                if ($services[$i] > 0) {

                                } else {
                                    unset($services[$i]);
                                }
                            } else {
                                if(count($services) == 1) {
                                    $services = [];
                                }
                            }
                        }
                        // удаляем пустые значения из массива

                        // Список услуг

                        if ($data->type == 2) {

                            $Typelist = \common\models\Type::find()->asArray()->all();
                            $ServicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=1 OR service_id=2)')->orderBy('company_id ASC')->asArray()->all();

                            $ArrayTypes = [];

                            foreach ($Typelist as $type) {
                                $ArrayTypes[$type['id']] = $type['name'];
                            }

                            $PriceArray = [];

                            $numServices = 2;

                            if(count($services) > 0) {
                                $numServices = count($services);
                            }

                            $ResTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr>';

                            if($numServices == 2) {
                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Снаружи</td>';
                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Внутри</td>';
                            } else {

                                if($services[0] == 1) {
                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Снаружи</td>';
                                } else {
                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Внутри</td>';
                                }

                            }


                            $ResTypeCompany .= '</tr></table></td></tr>';

                            foreach ($ServicesList as $service) {
                                $PriceArray[$service['type_id']][$service['service_id']] = $service['price'];
                            }

                            $Last_service_type = [];

                            foreach ($ServicesList as $service) {

                                if (!in_array($service['type_id'], $Last_service_type)) {

                                    if (count($carTypes) == 0) {

                                        $type_id = $service['type_id'];

                                        $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                        $type_id = 0;

                                        if ((isset($PriceArray[$service['type_id']][1])) && (isset($PriceArray[$service['type_id']][2]))) {

                                            if($numServices == 2) {

                                                if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                } else if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] == 0)) {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                } else if (($PriceArray[$service['type_id']][1] == 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                } else {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                }

                                            } else {

                                                if($services[0] == 1) {

                                                    if (isset($PriceArray[$service['type_id']][1])) {

                                                        if ($PriceArray[$service['type_id']][1] > 0) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                } else {

                                                    if (isset($PriceArray[$service['type_id']][2])) {

                                                        if ($PriceArray[$service['type_id']][2] > 0) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                }

                                            }

                                        } else if (isset($PriceArray[$service['type_id']][1])) {

                                            if($numServices == 2) {

                                                if ($PriceArray[$service['type_id']][1] > 0) {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                } else {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                }

                                            } else {

                                                if($services[0] == 1) {

                                                    if ($PriceArray[$service['type_id']][1] > 0) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                } else {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            }

                                        } else if (isset($PriceArray[$service['type_id']][2])) {

                                            if($numServices == 2) {

                                                if ($PriceArray[$service['type_id']][2] > 0) {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                } else {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                }

                                            } else {

                                                if($services[0] == 1) {

                                                    if ($PriceArray[$service['type_id']][2] > 0) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                } else {
                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            }

                                        } else {

                                            if ($numServices == 2) {

                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                            } else {

                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                            }

                                        }

                                    } else {

                                        $type_id = $service['type_id'];

                                        for($i = 0; $i < count($carTypes); $i++) {

                                            if($carTypes[$i] == $type_id) {

                                                $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                                if ((isset($PriceArray[$service['type_id']][1])) && (isset($PriceArray[$service['type_id']][2]))) {

                                                    if($numServices == 2) {

                                                        if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                        } else if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] == 0)) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                        } else if (($PriceArray[$service['type_id']][1] == 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                        }

                                                    } else {

                                                        if($services[0] == 1) {

                                                            if (isset($PriceArray[$service['type_id']][1])) {

                                                                if ($PriceArray[$service['type_id']][1] > 0) {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                }

                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        } else {

                                                            if (isset($PriceArray[$service['type_id']][2])) {

                                                                if ($PriceArray[$service['type_id']][2] > 0) {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                }

                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        }

                                                    }

                                                } else if (isset($PriceArray[$service['type_id']][1])) {

                                                    if($numServices == 2) {

                                                        if ($PriceArray[$service['type_id']][1] > 0) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                        }

                                                    } else {

                                                        if($services[0] == 1) {

                                                            if ($PriceArray[$service['type_id']][1] > 0) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    }

                                                } else if (isset($PriceArray[$service['type_id']][2])) {

                                                    if($numServices == 2) {

                                                        if ($PriceArray[$service['type_id']][2] > 0) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                        }

                                                    } else {

                                                        if($services[0] == 1) {

                                                            if ($PriceArray[$service['type_id']][2] > 0) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    }

                                                } else {

                                                    if ($numServices == 2) {

                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                                    } else {

                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                }

                                            }

                                        }

                                        $type_id = 0;

                                    }

                                }

                                $Last_service_type[] = $service['type_id'];

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

                            $numServices = 4;

                            if(count($services) > 0) {
                                $numServices = count($services);
                            }

                            $ResTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr><tr>';


                            if($numServices == 4) {
                                $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                            } else if($numServices == 1) {

                                switch ($services[0]) {
                                    case 6:
                                        $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Одинарное</td>';
                                        break;
                                    case 7:
                                        $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Парное</td>';
                                        break;
                                    case 9:
                                        $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Балансировка</td>';
                                        break;
                                    case 8:
                                        $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Полный</td>';
                                        break;
                                }

                            } else if($numServices == 2) {

                                $tmpArray = $services;

                                if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {
                                    list($tmpArray[0], $tmpArray[1]) = array($tmpArray[1], $tmpArray[0]);
                                }

                                for($z = 0; $z < count($tmpArray); $z++) {

                                    switch ($tmpArray[$z]) {
                                        case 6:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                                            }

                                            break;
                                        case 7:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                                            }

                                            break;
                                        case 9:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                                            }

                                            break;
                                        case 8:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                            }

                                            break;
                                    }

                                }

                            } else if($numServices == 3) {

                                $tmpArray = $services;

                                if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {

                                    $index1 = 0;
                                    $index2 = 0;

                                    for ($z = 0; $z < count($tmpArray); $z++) {

                                        if($tmpArray[$z] == 9) {
                                            $index1 = $z;
                                        } else if($tmpArray[$z] == 8) {
                                            $index2 = $z;
                                        }

                                    }

                                    list($tmpArray[$index1], $tmpArray[$index2]) = array($tmpArray[$index2], $tmpArray[$index1]);

                                }

                                for($z = 0; $z < count($tmpArray); $z++) {

                                    switch ($tmpArray[$z]) {
                                        case 6:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                                            } else {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Одинарное</td>';
                                            }

                                            break;
                                        case 7:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                                            } else {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td>';
                                            }

                                            break;
                                        case 9:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                                            } else {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td>';
                                            }

                                            break;
                                        case 8:

                                            if($z == 0) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                                            } else if(($z + 1) == count($tmpArray)) {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                            } else {
                                                $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Полный</td>';
                                            }

                                            break;
                                    }

                                }

                            }

                            $ResTypeCompany .= '</tr></table></td></tr>';

                            foreach ($ServicesList as $service) {
                                $PriceArray[$service['type_id']][$service['service_id']] = $service['price'];
                            }

                            $Last_service_type = [];

                            foreach ($ServicesList as $service) {

                                if (!in_array($service['type_id'], $Last_service_type)) {

                                    $numPercent = 0;

                                    if($numServices == 4) {
                                        $numPercent = 25;
                                    } else if($numServices == 1) {
                                        $numPercent = 100;
                                    } else if($numServices == 2) {
                                        $numPercent = 50;
                                    } else if($numServices == 3) {
                                        $numPercent = 33;
                                    }

                                    if (count($carTypes) == 0) {

                                        $type_id = $service['type_id'];

                                        $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                        $type_id = 0;

                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                        if(count($services) > 0) {

                                            if (in_array(6, $services)) {

                                                $stringStyle = '';

                                                if($numServices != 1) {
                                                    $stringStyle = ' style=\'padding-right:5px;\'';
                                                } else {
                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                }

                                                if (isset($PriceArray[$service['type_id']][6])) {
                                                    if ($PriceArray[$service['type_id']][6] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }

                                            }

                                            if(in_array(7, $services)) {

                                                $stringStyle = '';

                                                if($numServices != 1) {
                                                    if($services[0] != 7) {
                                                        $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                    } else {
                                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                                    }
                                                } else {
                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                }

                                                if (isset($PriceArray[$service['type_id']][7])) {
                                                    if ($PriceArray[$service['type_id']][7] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }

                                            }

                                            if(in_array(9, $services)) {

                                                $stringStyle = '';

                                                if($numServices != 1) {
                                                    if($tmpArray[0] != 9) {
                                                        $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                    } else {
                                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                                    }
                                                } else {
                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                }

                                                if (isset($PriceArray[$service['type_id']][9])) {
                                                    if ($PriceArray[$service['type_id']][9] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }

                                            }

                                            if(in_array(8, $services)) {

                                                $stringStyle = '';

                                                if($numServices != 1) {
                                                    $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                } else {
                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                }

                                                if (isset($PriceArray[$service['type_id']][8])) {
                                                    if ($PriceArray[$service['type_id']][8] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                }

                                            }

                                        } else {

                                            if (isset($PriceArray[$service['type_id']][6])) {
                                                if ($PriceArray[$service['type_id']][6] > 0) {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                }
                                            } else {
                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                            }

                                            if (isset($PriceArray[$service['type_id']][7])) {
                                                if ($PriceArray[$service['type_id']][7] > 0) {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                }
                                            } else {
                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                            }

                                            if (isset($PriceArray[$service['type_id']][9])) {
                                                if ($PriceArray[$service['type_id']][9] > 0) {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                }
                                            } else {
                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                            }

                                            if (isset($PriceArray[$service['type_id']][8])) {
                                                if ($PriceArray[$service['type_id']][8] > 0) {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                }
                                            } else {
                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                            }

                                        }

                                        $ResTypeCompany .= '</tr></table></td></tr>';

                                    } else {

                                        $type_id = $service['type_id'];

                                        for ($i = 0; $i < count($carTypes); $i++) {

                                            if ($carTypes[$i] == $type_id) {


                                                $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                                if(count($services) > 0) {

                                                    if (in_array(6, $services)) {

                                                        $stringStyle = '';

                                                        if($numServices != 1) {
                                                            $stringStyle = ' style=\'padding-right:5px;\'';
                                                        } else {
                                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][6])) {
                                                            if ($PriceArray[$service['type_id']][6] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }

                                                    }

                                                    if(in_array(7, $services)) {

                                                        $stringStyle = '';

                                                        if($numServices != 1) {
                                                            if($services[0] != 7) {
                                                                $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                            } else {
                                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                                            }
                                                        } else {
                                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][7])) {
                                                            if ($PriceArray[$service['type_id']][7] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }

                                                    }

                                                    if(in_array(9, $services)) {

                                                        $stringStyle = '';

                                                        if($numServices != 1) {
                                                            if($tmpArray[0] != 9) {
                                                                $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                            } else {
                                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                                            }
                                                        } else {
                                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][9])) {
                                                            if ($PriceArray[$service['type_id']][9] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }

                                                    }

                                                    if(in_array(8, $services)) {

                                                        $stringStyle = '';

                                                        if($numServices != 1) {
                                                            $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                        } else {
                                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][8])) {
                                                            if ($PriceArray[$service['type_id']][8] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }

                                                    }

                                                } else {

                                                    if (isset($PriceArray[$service['type_id']][6])) {
                                                        if ($PriceArray[$service['type_id']][6] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][7])) {
                                                        if ($PriceArray[$service['type_id']][7] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][9])) {
                                                        if ($PriceArray[$service['type_id']][9] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][8])) {
                                                        if ($PriceArray[$service['type_id']][8] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                    }

                                                }

                                                $ResTypeCompany .= '</tr></table></td></tr>';

                                            }

                                        }

                                        $type_id = 0;

                                    }

                                }

                                $Last_service_type[] = $service['type_id'];

                            }

                            $ResTypeCompany .= '</table>';

                            return $ResTypeCompany;
                        } else if ($data->type == 6) {

                            if(Yii::$app->request->get('type') == 2) {

                                $Typelist = \common\models\Type::find()->asArray()->all();
                                $ServicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=1 OR service_id=2)')->orderBy('company_id ASC')->asArray()->all();

                                $ArrayTypes = [];

                                foreach ($Typelist as $type) {
                                    $ArrayTypes[$type['id']] = $type['name'];
                                }

                                $PriceArray = [];

                                $numServices = 2;

                                if(count($services) > 0) {
                                    $numServices = count($services);
                                }

                                $ResTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr>';

                                if($numServices == 2) {
                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Снаружи</td>';
                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Внутри</td>';
                                } else {

                                    if($services[0] == 1) {
                                        $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Снаружи</td>';
                                    } else {
                                        $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\'>Внутри</td>';
                                    }

                                }


                                $ResTypeCompany .= '</tr></table></td></tr>';

                                foreach ($ServicesList as $service) {
                                    $PriceArray[$service['type_id']][$service['service_id']] = $service['price'];
                                }

                                $Last_service_type = [];

                                foreach ($ServicesList as $service) {

                                    if (!in_array($service['type_id'], $Last_service_type)) {

                                        if (count($carTypes) == 0) {

                                            $type_id = $service['type_id'];

                                            $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                            $type_id = 0;

                                            if ((isset($PriceArray[$service['type_id']][1])) && (isset($PriceArray[$service['type_id']][2]))) {

                                                if($numServices == 2) {

                                                    if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                    } else if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] == 0)) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                    } else if (($PriceArray[$service['type_id']][1] == 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                    }

                                                } else {

                                                    if($services[0] == 1) {

                                                        if (isset($PriceArray[$service['type_id']][1])) {

                                                            if ($PriceArray[$service['type_id']][1] > 0) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    } else {

                                                        if (isset($PriceArray[$service['type_id']][2])) {

                                                            if ($PriceArray[$service['type_id']][2] > 0) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    }

                                                }

                                            } else if (isset($PriceArray[$service['type_id']][1])) {

                                                if($numServices == 2) {

                                                    if ($PriceArray[$service['type_id']][1] > 0) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                    }

                                                } else {

                                                    if($services[0] == 1) {

                                                        if ($PriceArray[$service['type_id']][1] > 0) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                }

                                            } else if (isset($PriceArray[$service['type_id']][2])) {

                                                if($numServices == 2) {

                                                    if ($PriceArray[$service['type_id']][2] > 0) {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                    }

                                                } else {

                                                    if($services[0] == 1) {

                                                        if ($PriceArray[$service['type_id']][2] > 0) {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                        } else {
                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    } else {
                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                    }

                                                }

                                            } else {

                                                if ($numServices == 2) {

                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                                } else {

                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                }

                                            }

                                        } else {

                                            $type_id = $service['type_id'];

                                            for($i = 0; $i < count($carTypes); $i++) {

                                                if($carTypes[$i] == $type_id) {

                                                    $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                                    if ((isset($PriceArray[$service['type_id']][1])) && (isset($PriceArray[$service['type_id']][2]))) {

                                                        if($numServices == 2) {

                                                            if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                            } else if (($PriceArray[$service['type_id']][1] > 0) && ($PriceArray[$service['type_id']][2] == 0)) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                            } else if (($PriceArray[$service['type_id']][1] == 0) && ($PriceArray[$service['type_id']][2] > 0)) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                            }

                                                        } else {

                                                            if($services[0] == 1) {

                                                                if (isset($PriceArray[$service['type_id']][1])) {

                                                                    if ($PriceArray[$service['type_id']][1] > 0) {
                                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                                    } else {
                                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                    }

                                                                } else {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                }

                                                            } else {

                                                                if (isset($PriceArray[$service['type_id']][2])) {

                                                                    if ($PriceArray[$service['type_id']][2] > 0) {
                                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                                    } else {
                                                                        $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                    }

                                                                } else {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                }

                                                            }

                                                        }

                                                    } else if (isset($PriceArray[$service['type_id']][1])) {

                                                        if($numServices == 2) {

                                                            if ($PriceArray[$service['type_id']][1] > 0) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                            }

                                                        } else {

                                                            if($services[0] == 1) {

                                                                if ($PriceArray[$service['type_id']][1] > 0) {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][1] . '</td></tr>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                }

                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        }

                                                    } else if (isset($PriceArray[$service['type_id']][2])) {

                                                        if($numServices == 2) {

                                                            if ($PriceArray[$service['type_id']][2] > 0) {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr></table></td></tr>';
                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';
                                                            }

                                                        } else {

                                                            if($services[0] == 1) {

                                                                if ($PriceArray[$service['type_id']][2] > 0) {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>' . $PriceArray[$service['type_id']][2] . '</td></tr>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                                }

                                                            } else {
                                                                $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                            }

                                                        }

                                                    } else {

                                                        if ($numServices == 2) {

                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr><td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td><td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td></tr></table></td></tr>';

                                                        } else {

                                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>-</td></tr>';
                                                        }

                                                    }

                                                }

                                            }

                                            $type_id = 0;

                                        }

                                    }

                                    $Last_service_type[] = $service['type_id'];

                                }

                                $ResTypeCompany .= '</table>';

                                return $ResTypeCompany;

                            } else {

                                $Typelist = \common\models\Type::find()->asArray()->all();
                                $ServicesList = $data->getCompanyServices()->where('company_id = ' . $data->id . ' AND (service_id=6 OR service_id=7 OR service_id=8 OR service_id=9) AND price>0')->orderBy('company_id ASC')->asArray()->all();

                                $ArrayTypes = [];

                                foreach ($Typelist as $type) {
                                    $ArrayTypes[$type['id']] = $type['name'];
                                }

                                $PriceArray = [];

                                $numServices = 4;

                                if(count($services) > 0) {
                                    $numServices = count($services);
                                }

                                $ResTypeCompany = '<table width="100%" border="1" bordercolor="#c6c6c6"><tr><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>Вид ТС</td><td align=\'center\' valign=\'middle\' style=\'padding:5px;\'>
<table width="100%" border="0"><tr><td width="100%" colspan=\'' . $numServices . '\' align=\'center\' valign=\'middle\' style=\'border-bottom:1px solid #c6c6c6;\'>Стоимость</td></tr><tr><tr>';


                                if($numServices == 4) {
                                    $ResTypeCompany .= '<td width="25%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td><td width="25%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                } else if($numServices == 1) {

                                    switch ($services[0]) {
                                        case 6:
                                            $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Одинарное</td>';
                                            break;
                                        case 7:
                                            $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Парное</td>';
                                            break;
                                        case 9:
                                            $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Балансировка</td>';
                                            break;
                                        case 8:
                                            $ResTypeCompany .= '<td width="100%" align=\'center\' valign=\'middle\'>Полный</td>';
                                            break;
                                    }

                                } else if($numServices == 2) {

                                    $tmpArray = $services;

                                    if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {
                                        list($tmpArray[0], $tmpArray[1]) = array($tmpArray[1], $tmpArray[0]);
                                    }

                                    for($z = 0; $z < count($tmpArray); $z++) {

                                        switch ($tmpArray[$z]) {
                                            case 6:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                                                }

                                                break;
                                            case 7:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                                                }

                                                break;
                                            case 9:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                                                }

                                                break;
                                            case 8:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="50%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                                }

                                                break;
                                        }

                                    }

                                } else if($numServices == 3) {

                                    $tmpArray = $services;

                                    if((in_array(9, $tmpArray) && in_array(8, $tmpArray))) {

                                        $index1 = 0;
                                        $index2 = 0;

                                        for ($z = 0; $z < count($tmpArray); $z++) {

                                            if($tmpArray[$z] == 9) {
                                                $index1 = $z;
                                            } else if($tmpArray[$z] == 8) {
                                                $index2 = $z;
                                            }

                                        }

                                        list($tmpArray[$index1], $tmpArray[$index2]) = array($tmpArray[$index2], $tmpArray[$index1]);

                                    }

                                    for($z = 0; $z < count($tmpArray); $z++) {

                                        switch ($tmpArray[$z]) {
                                            case 6:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Одинарное</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Одинарное</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Одинарное</td>';
                                                }

                                                break;
                                            case 7:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Парное</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Парное</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Парное</td>';
                                                }

                                                break;
                                            case 9:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Балансировка</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Балансировка</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Балансировка</td>';
                                                }

                                                break;
                                            case 8:

                                                if($z == 0) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>Полный</td>';
                                                } else if(($z + 1) == count($tmpArray)) {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>Полный</td>';
                                                } else {
                                                    $ResTypeCompany .= '<td width="33%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>Полный</td>';
                                                }

                                                break;
                                        }

                                    }

                                }

                                $ResTypeCompany .= '</tr></table></td></tr>';

                                foreach ($ServicesList as $service) {
                                    $PriceArray[$service['type_id']][$service['service_id']] = $service['price'];
                                }

                                $Last_service_type = [];

                                foreach ($ServicesList as $service) {

                                    if (!in_array($service['type_id'], $Last_service_type)) {

                                        $numPercent = 0;

                                        if($numServices == 4) {
                                            $numPercent = 25;
                                        } else if($numServices == 1) {
                                            $numPercent = 100;
                                        } else if($numServices == 2) {
                                            $numPercent = 50;
                                        } else if($numServices == 3) {
                                            $numPercent = 33;
                                        }

                                        if (count($carTypes) == 0) {

                                            $type_id = $service['type_id'];

                                            $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                            $type_id = 0;

                                            $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                            if(count($services) > 0) {

                                                if (in_array(6, $services)) {

                                                    $stringStyle = '';

                                                    if($numServices != 1) {
                                                        $stringStyle = ' style=\'padding-right:5px;\'';
                                                    } else {
                                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][6])) {
                                                        if ($PriceArray[$service['type_id']][6] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }

                                                }

                                                if(in_array(7, $services)) {

                                                    $stringStyle = '';

                                                    if($numServices != 1) {
                                                        if($services[0] != 7) {
                                                            $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                        } else {
                                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                                        }
                                                    } else {
                                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][7])) {
                                                        if ($PriceArray[$service['type_id']][7] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }

                                                }

                                                if(in_array(9, $services)) {

                                                    $stringStyle = '';

                                                    if($numServices != 1) {
                                                        if($tmpArray[0] != 9) {
                                                            $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                        } else {
                                                            $stringStyle = ' style=\'padding-left:5px;\'';
                                                        }
                                                    } else {
                                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][9])) {
                                                        if ($PriceArray[$service['type_id']][9] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }

                                                }

                                                if(in_array(8, $services)) {

                                                    $stringStyle = '';

                                                    if($numServices != 1) {
                                                        $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                    } else {
                                                        $stringStyle = ' style=\'padding-left:5px;\'';
                                                    }

                                                    if (isset($PriceArray[$service['type_id']][8])) {
                                                        if ($PriceArray[$service['type_id']][8] > 0) {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                        }
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                    }

                                                }

                                            } else {

                                                if (isset($PriceArray[$service['type_id']][6])) {
                                                    if ($PriceArray[$service['type_id']][6] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                }

                                                if (isset($PriceArray[$service['type_id']][7])) {
                                                    if ($PriceArray[$service['type_id']][7] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                }

                                                if (isset($PriceArray[$service['type_id']][9])) {
                                                    if ($PriceArray[$service['type_id']][9] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                }

                                                if (isset($PriceArray[$service['type_id']][8])) {
                                                    if ($PriceArray[$service['type_id']][8] > 0) {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                    } else {
                                                        $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                    }
                                                } else {
                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                }

                                            }

                                            $ResTypeCompany .= '</tr></table></td></tr>';

                                        } else {

                                            $type_id = $service['type_id'];

                                            for ($i = 0; $i < count($carTypes); $i++) {

                                                if ($carTypes[$i] == $type_id) {


                                                    $ResTypeCompany .= "<tr><td align='left' valign='middle' style='padding:5px;'>" . $ArrayTypes[$type_id] . "</td>";

                                                    $ResTypeCompany .= '<td align=\'center\' valign=\'middle\' style=\'padding:5px;\'><table width="100%" border="0"><tr>';

                                                    if(count($services) > 0) {

                                                        if (in_array(6, $services)) {

                                                            $stringStyle = '';

                                                            if($numServices != 1) {
                                                                $stringStyle = ' style=\'padding-right:5px;\'';
                                                            } else {
                                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                                            }

                                                            if (isset($PriceArray[$service['type_id']][6])) {
                                                                if ($PriceArray[$service['type_id']][6] > 0) {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                                }
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }

                                                        }

                                                        if(in_array(7, $services)) {

                                                            $stringStyle = '';

                                                            if($numServices != 1) {
                                                                if($services[0] != 7) {
                                                                    $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                                } else {
                                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                                }
                                                            } else {
                                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                                            }

                                                            if (isset($PriceArray[$service['type_id']][7])) {
                                                                if ($PriceArray[$service['type_id']][7] > 0) {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                                }
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }

                                                        }

                                                        if(in_array(9, $services)) {

                                                            $stringStyle = '';

                                                            if($numServices != 1) {
                                                                if($tmpArray[0] != 9) {
                                                                    $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                                } else {
                                                                    $stringStyle = ' style=\'padding-left:5px;\'';
                                                                }
                                                            } else {
                                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                                            }

                                                            if (isset($PriceArray[$service['type_id']][9])) {
                                                                if ($PriceArray[$service['type_id']][9] > 0) {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                                }
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }

                                                        }

                                                        if(in_array(8, $services)) {

                                                            $stringStyle = '';

                                                            if($numServices != 1) {
                                                                $stringStyle = ' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'';
                                                            } else {
                                                                $stringStyle = ' style=\'padding-left:5px;\'';
                                                            }

                                                            if (isset($PriceArray[$service['type_id']][8])) {
                                                                if ($PriceArray[$service['type_id']][8] > 0) {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                                } else {
                                                                    $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                                }
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\'' . $stringStyle . '>-</td>';
                                                            }

                                                        }

                                                    } else {

                                                        if (isset($PriceArray[$service['type_id']][6])) {
                                                            if ($PriceArray[$service['type_id']][6] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>' . $PriceArray[$service['type_id']][6] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'padding-right:5px;\'>-</td>';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][7])) {
                                                            if ($PriceArray[$service['type_id']][7] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][7] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][9])) {
                                                            if ($PriceArray[$service['type_id']][9] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>' . $PriceArray[$service['type_id']][9] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px; padding-right:5px;\'>-</td>';
                                                        }

                                                        if (isset($PriceArray[$service['type_id']][8])) {
                                                            if ($PriceArray[$service['type_id']][8] > 0) {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>' . $PriceArray[$service['type_id']][8] . '</td>';
                                                            } else {
                                                                $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                            }
                                                        } else {
                                                            $ResTypeCompany .= '<td width="' . $numPercent . '%" align=\'center\' valign=\'middle\' style=\'border-left:1px solid #c6c6c6; padding-left:5px;\'>-</td>';
                                                        }

                                                    }

                                                    $ResTypeCompany .= '</tr></table></td></tr>';

                                                }

                                            }

                                            $type_id = 0;

                                        }

                                    }

                                    $Last_service_type[] = $service['type_id'];

                                }

                                $ResTypeCompany .= '</table>';

                                return $ResTypeCompany;

                            }

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