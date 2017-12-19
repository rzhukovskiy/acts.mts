<?php

use yii\web\View;
use common\models\CompanyInfo;
use common\models\Company;
use yii\helpers\Html;

$titleTable = '';

if($selID > 0) {
    $companyName = Company::findOne(['id' => $selID]);
    $titleTable = Company::$listType[$companyName->type]['ru'] . ' ' . $companyName->name . ' на карте';
    $this->title = $titleTable;
} else {

    switch ($type) {
        case 1:
            $titleTable = "Компании на карте";
            break;
        case 2:
            $titleTable = "Мойки на карте";
            break;
        case 3:
            $titleTable = "Сервисы на карте";
            break;
        case 4:
            $titleTable = "Шиномонтажы на карте";
            break;
        case 5:
            $titleTable = "Дезинфекции на карте";
            break;
        case 6:
            $titleTable = "Универсальная компании на карте";
            break;
        case 7:
            $titleTable = "Стоянки на карте";
            break;
    }

    $this->title = $titleTable;
}

$css = "#map {
        width: 100%;
        height: 600px;
        background-color: grey;
      }";
$this->registerCSS($css);

$arrAddressCompany = [];
$iArr = 0;

$zoomLat = '61.698653';
$zoomLng = '99.505405';
$zoomVal = 3;

for($i = 0; $i < count($Company); $i++) {

    if((isset($Company[$i]['city'])) && (isset($Company[$i]['street'])) && (isset($Company[$i]['house']))) {

        if((isset($Company[$i]['lat'])) && (isset($Company[$i]['lng']))) {

            $arrAddressCompany[$iArr]['lat'] = $Company[$i]['lat'];
            $arrAddressCompany[$iArr]['lng'] = $Company[$i]['lng'];
            $arrAddressCompany[$iArr]['name'] = $Company[$i]['name'];
            $arrAddressCompany[$iArr]['id'] = $Company[$i]['company_id'];
            $arrAddressCompany[$iArr]['type'] = $Company[$i]['type'];
            $iArr++;

            // Зум к единственно выбранной компании
            if(($i == 0) && ($typePage == 1)) {
                $zoomLat = $Company[$i]['lat'];
                $zoomLng = $Company[$i]['lng'];
                $zoomVal = 12;
            }

        } else {

            $dataAddress = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($Company[$i]['city'] . ' ' . $Company[$i]['street'] . ' ' . $Company[$i]['house']) . "&key=AIzaSyBncSqlklvzetwkGxjGbBd0OqOjwTnfpOY");
            $arrAddress = json_decode($dataAddress);

            if ((isset($arrAddress->results[0]->geometry->location->lat)) && (isset($arrAddress->results[0]->geometry->location->lng))) {

                $location = $arrAddress->results[0]->geometry->location;
                $lat = $location->lat;
                $lng = $location->lng;

                $arrAddressCompany[$iArr]['lat'] = $lat;
                $arrAddressCompany[$iArr]['lng'] = $lng;
                $arrAddressCompany[$iArr]['name'] = $Company[$i]['name'];
                $arrAddressCompany[$iArr]['id'] = $Company[$i]['company_id'];
                $arrAddressCompany[$iArr]['type'] = $Company[$i]['type'];

                $CompanyInfo = CompanyInfo::findOne(['company_id' => $Company[$i]['company_id']]);
                $CompanyInfo->lat = $lat;
                $CompanyInfo->lng = $lng;
                $CompanyInfo->save();

                // Зум к единственно выбранной компании
                if(($i == 0) && ($typePage == 1)) {
                    $zoomLat = $lat;
                    $zoomLng = $lng;
                    $zoomVal = 5;
                }

                $iArr++;
            }

        }

    }
}

$arrAddressCompany = json_encode($arrAddressCompany);

echo '
<div class="panel panel-primary">
    <div class="panel-heading">' . $titleTable . ($selID == 0 ? '<div class="header-btn pull-right">' . Html::a('Грузовые', [
            'company/' . Yii::$app->controller->action->id,
            'type' => $type,
            'status' => $status,
            'car_type' => 0,
        ], ['class' => 'btn btn-warning btn-sm', 'style' => 'margin-right:15px;']) . Html::a('Легковые', [
            'company/' . Yii::$app->controller->action->id,
            'type' => $type,
            'status' => $status,
            'car_type' => 1,
        ], ['class' => 'btn btn-warning btn-sm', 'style' => 'margin-right:15px;']) . Html::a('Сбросить фильтр', [
            'company/' . Yii::$app->controller->action->id,
            'type' => $type,
            'status' => $status,
        ], ['class' => 'btn btn-success btn-sm']) . '</div>' : '') . '</div>
    <div class="panel-body">
        <div id="map"></div>
    </div>
</div>
<script>
    function initMap() {
        var rusCountry = {lat: ' . $zoomLat . ', lng: ' . $zoomLng . '};

        var map = new google.maps.Map(document.getElementById("map"), {
            zoom: ' . $zoomVal . ',
            center: rusCountry
        });
        
        var companyArray = ' . $arrAddressCompany . ';

        for (var i = 0; i < companyArray.length; i++) {
            
        var CompanyAddress = {lat: parseFloat(companyArray[i]["lat"]), lng: parseFloat(companyArray[i]["lng"])};
        var name = companyArray[i]["name"];
        var id = companyArray[i]["id"];
        var type = companyArray[i]["type"];
        
        var marker;
        
        if(type == 1) {
        marker = new google.maps.Marker({
            position: CompanyAddress,
            icon: "http://maps.google.com/mapfiles/ms/icons/green-dot.png",
            map: map,
            title: name,
            url: "/company/update?id=" + id
        });
        } else if(type == 2) {
        marker = new google.maps.Marker({
            position: CompanyAddress,
            icon: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
            map: map,
            title: name,
            url: "/company/update?id=" + id
        });
        } else {
        marker = new google.maps.Marker({
            position: CompanyAddress,
            map: map,
            title: name,
            url: "/company/update?id=" + id
        });
        }
        
        // Клик по маркету
        google.maps.event.addListener(marker, "click", function() {
            window.open(this.url, "_blank");
        });
        
        }
        
    }
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBncSqlklvzetwkGxjGbBd0OqOjwTnfpOY&callback=initMap">
</script>';
?>