<?php

use yii\web\View;
use common\models\CompanyInfo;

$this->title = 'Компании на карте';

$css = "#map {
        width: 100%;
        height: 600px;
        background-color: grey;
      }";
$this->registerCSS($css);

$arrAddressCompany = [];
$iArr = 0;

for($i = 0; $i < count($Company); $i++) {

    if((isset($Company[$i]['city'])) && (isset($Company[$i]['street'])) && (isset($Company[$i]['house']))) {

        if((isset($Company[$i]['lat'])) && (isset($Company[$i]['lng']))) {

            $arrAddressCompany[$iArr]['lat'] = $Company[$i]['lat'];
            $arrAddressCompany[$iArr]['lng'] = $Company[$i]['lng'];
            $arrAddressCompany[$iArr]['name'] = $Company[$i]['name'];
            $iArr++;

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

                $CompanyInfo = CompanyInfo::findOne(['company_id' => $Company[$i]['company_id']]);
                $CompanyInfo->lat = $lat;
                $CompanyInfo->lng = $lng;
                $CompanyInfo->save();

                $iArr++;
            }

        }

    }
}

$arrAddressCompany = json_encode($arrAddressCompany);

echo '
<div class="panel panel-primary">
    <div class="panel-heading">Компании на карте</div>
    <div class="panel-body">
        <div id="map"></div>
    </div>
</div>
<script>
    function initMap() {
        var rusCountry = {lat: 61.698653, lng: 99.505405};

        var map = new google.maps.Map(document.getElementById("map"), {
            zoom: 3,
            center: rusCountry
        });
        
        var companyArray = ' . $arrAddressCompany . ';

        for (var i = 0; i < companyArray.length; i++) {
            
        var CompanyAddress = {lat: parseFloat(companyArray[i]["lat"]), lng: parseFloat(companyArray[i]["lng"])};
        var name = companyArray[i]["name"];
        
        var marker = new google.maps.Marker({
            position: CompanyAddress,
            map: map,
            title: name
        });
        
        }
    }
</script>
<script async defer
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBncSqlklvzetwkGxjGbBd0OqOjwTnfpOY&callback=initMap">
</script>';
?>