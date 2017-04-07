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

    <!-- Кнопки сортировки -->
    <?php

    $arrSelCarTypes = Yii::$app->request->queryParams['CompanySearch']['cartypes'];

    // удаляем пустые значения из массива
    for($i = 0; $i < count($arrSelCarTypes); $i++) {
        if(isset($arrSelCarTypes[$i])) {
            if ($arrSelCarTypes[$i] > 0) {

            } else {
                unset($arrSelCarTypes[$i]);
            }
        } else {
            if(count($arrSelCarTypes) == 1) {
                $arrSelCarTypes = [];
            }
        }
    }
    // удаляем пустые значения из массива

    if(count($arrSelCarTypes) == 1) {
        if (Yii::$app->request->queryParams['CompanySearch']['services']) {
            $arrButtSort = Yii::$app->request->queryParams['CompanySearch']['services'];

            for ($i = 0; $i < count($arrButtSort); $i++) {
                if (strpos(Yii::$app->request->url, '&sort=') > 0) {
                    echo Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 30px; margin-bottom: 20px;">' . $listService[$arrButtSort[$i]] . '</span>', substr(Yii::$app->request->url, 0, strpos(Yii::$app->request->url, '&sort=')) . '&sort=' . $arrButtSort[$i]);
                } else {
                    echo Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 30px; margin-bottom: 20px;">' . $listService[$arrButtSort[$i]] . '</span>', Yii::$app->request->url . '&sort=' . $arrButtSort[$i]);
                }
            }

        } else {

            foreach ($listService as $key => $value) {
                if (strpos(Yii::$app->request->url, '&sort=') > 0) {
                    echo Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 30px; margin-bottom: 20px;">' . $value . '</span>', substr(Yii::$app->request->url, 0, strpos(Yii::$app->request->url, '&sort=')) . '&sort=' . $key);
                } else {
                    echo Html::a('<span class="btn btn-primary btn-sm" style="margin-left: 30px; margin-bottom: 20px;">' . $value . '</span>', Yii::$app->request->url . '&sort=' . $key);
                }
            }

        }
    }

    ?>
    <!-- Кнопки сортировки -->

    <div class="panel-body">
        <?php

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
                        return \backend\controllers\CompanyController::getPriceFilter($data);
                    }
                ],
                [
                    'attribute' => 'range',
                    'label' => 'Расстояние',
                    'content'   => function ($data) {
                        if ($data->fullAddress) {

                            // Поиск координат яндекса по адресу + поиск рассточния по координатам
                            /*$response = json_decode(file_get_contents('https://geocode-maps.yandex.ru/1.x/?format=json&results=1&geocode=' . $data->fullAddress . '&apikey=LT8LjqNzfg8R9wpemrSQvRUJHgQ9x7b7yjtlBnystFe1lYO6h0Kratmxax3ojSFDMcLf0-Oi4oXkc-nyFapZJPB92eQaTdYu8Zq~UkmNWeg='));

                            if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {

                                $posB = explode(' ', $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                                $posB = $posB[0] . '%20' . $posB[1];

                                $distance = json_decode(file_get_contents("http://calc-api.ru/app:geo-api/null?a=39.202175%2051.662603&b=" . $posB));

                                if(isset($distance->distanse)) {

                                    if($distance->distanse > 0) {
                                        return $distance->distanse . ' км.';
                                    } else {
                                        return 'не задано';
                                    }

                                } else {
                                    return 'не задано';
                                }

                            } else {
                                return 'не задано';
                            }*/
                            // END Поиск координат яндекса по адресу + поиск рассточния по координатам

                            // Поиск расстояния по адресу

                                if(strlen(Yii::$app->request->get('ad')) > 0) {

                                $distance = json_decode(file_get_contents("http://calc-api.ru/app:geo-api/null?a=" . Yii::$app->request->get('ad') . "&b=" . $data->fullAddress));

                                if (isset($distance->distanse)) {

                                    if ($distance->distanse > 0) {
                                        return $distance->distanse . ' км.';
                                    } else {
                                        return 'не задано';
                                    }

                                } else {
                                    return 'не задано';
                                }

                                } else {
                                    return 'не задано';
                                }
                            // END Поиск расстояния по адресу

                        } else {
                            return 'не задано';
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