<?php

/**
 * @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $searchModel common\models\search\CompanyMemberSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

$this->title = 'Новое коммерческое предложение';

$action = Yii::$app->controller->action->id;
echo $this->render('/company/offer/_update_tabs', [
    'model' => $model,
    'listType' => $listType,
]);

echo $this->render($action . '/_list', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'type' => $type,
    'listCar' => $listCar,
    'listService' => $listService,
    'listCity' => $listCity,
//    'userData' => $userData,
    'admin' => isset($admin) ? $admin : false,
]);

$js = '
var tableCont = $(".table table-bordered kv-grid-table kv-table-wrap").children("tbody");
var tableContTR = tableCont.find("tr");

var i = 0;

var arrayDataKey = [];
$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
    arrayDataKey[i] = [];
    arrayDataKey[i][0] = el;
    arrayDataKey[i][1] = $(el).data("key");
    arrayDataKey[i][2] = $(el).children("td:first").text();
    
    if($(el).children("td").eq(4).text() == "не задано") {
    arrayDataKey[i][3] = 0;
    } else {
    
    var numRange = $(el).children("td").eq(4).text();
    numRange = numRange.split(" ");
    
    arrayDataKey[i][3] = numRange[0];
    }
    
    i++;
    }
});

var rangeTitle = document.querySelectorAll("[data-col-seq=\"5\"]");

rangeTitle[0].style.cursor= "pointer";
//rangeTitle[0].style.color= "#23527c";
rangeTitle[0].style.textDecoration= "underline";

function ReplaceItemsTable(firstEl, secEl) {

    var content1 = $(arrayDataKey[firstEl][0]).html();
    var content1i = arrayDataKey[firstEl][2];
    var content2 = $(arrayDataKey[secEl][0]).html();
    var content2i = arrayDataKey[secEl][2];

    $(arrayDataKey[firstEl][0]).html(content2).show();
    $(arrayDataKey[secEl][0]).html(content1).show();
    $(arrayDataKey[firstEl][0]).children("td:first").text(content1i);
    $(arrayDataKey[secEl][0]).children("td:first").text(content2i);
    
 i = 0;

arrayDataKey = [];
$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
    arrayDataKey[i] = [];
    arrayDataKey[i][0] = el;
    arrayDataKey[i][1] = $(el).data("key");
    arrayDataKey[i][2] = $(el).children("td:first").text();
    
    if($(el).children("td").eq(4).text() == "не задано") {
    arrayDataKey[i][3] = 0;
    } else {
    
    var numRange = $(el).children("td").eq(4).text();
    numRange = numRange.split(" ");
    
    arrayDataKey[i][3] = numRange[0];
    }
    
    i++;
    }
});

/*var stringTest = "";

for (var z = 0; z < i; z++) {
stringTest = stringTest + "-" + arrayDataKey[z][3];
}
alert(stringTest);*/

}

var readyToSort = 1;
var typeSort = 1;

rangeTitle[0].addEventListener("click", function() {

if(readyToSort == 1) {

if(typeSort == 0) {
typeSort = 1;
} else {
typeSort = 0;
}



            if(typeSort == 0) {

    for (var z = 0; z < i; z++) {

        var min = arrayDataKey[z][3];
        var min_i = z;

        for (var j = z+1; j < i; j++) {

            if (arrayDataKey[j][3] < min) {
                min = arrayDataKey[j][3];
                min_i = j;
            }
        }

        if (z != min_i) {
        ReplaceItemsTable(min_i, z);
        }
     }

            } else {

    for (var z = 0; z < i; z++) {

        var min = arrayDataKey[z][3];
        var min_i = z;

        for (var j = z+1; j < i; j++) {

            if (arrayDataKey[j][3] > min) {
                min = arrayDataKey[j][3];
                min_i = j;
            }
        }

        if (z != min_i) {
        ReplaceItemsTable(min_i, z);
        }
     }

            }


readyToSort = 0;

readyToSort = 1;

}

}, false);

';
$this->registerJs($js);