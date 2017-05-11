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

$arrSelCity = Yii::$app->request->queryParams['CompanySearch']['address'];

// удаляем пустые значения из массива
for($i = 0; $i < count($arrSelCity); $i++) {
    if(isset($arrSelCity[$i])) {
        if (strlen($arrSelCity[$i]) > 1) {

        } else {
            unset($arrSelCity[$i]);
        }
    } else {
        if(count($arrSelCity) == 1) {
            $arrSelCity = [];
        }
    }
}
// удаляем пустые значения из массива

if(count($arrSelCity) == 1) {

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
    arrayDataKey[i][3] = Number(0);
    } else {
    
    var numRange = $(el).children("td").eq(4).text();
    numRange = numRange.split(" ");
    
    arrayDataKey[i][3] = Number(numRange[0]);
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
    arrayDataKey[i][3] = Number(0);
    } else {
    
    var numRange = $(el).children("td").eq(4).text();
    numRange = numRange.split(" ");
    
    arrayDataKey[i][3] = Number(numRange[0]);
    }
    
    i++;
    }
});

}

// Кнопка сортировать
var readyToSort = 1;
var typeSort = 1;

rangeTitle[0].addEventListener("click", function() {

if(readyToSort == 1) {

if(typeSort == 0) {
typeSort = 1;
} else {
typeSort = 0;
}

readyToSort = 0;

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

readyToSort = 1;

}

}, false);

';
    $this->registerJs($js);

    $arrSelCarTypes = Yii::$app->request->queryParams['CompanySearch']['cartypes'];

// удаляем пустые значения из массива
    for ($i = 0; $i < count($arrSelCarTypes); $i++) {
        if (isset($arrSelCarTypes[$i])) {
            if ($arrSelCarTypes[$i] > 0) {

            } else {
                unset($arrSelCarTypes[$i]);
            }
        } else {
            if (count($arrSelCarTypes) == 1) {
                $arrSelCarTypes = [];
            }
        }
    }
// удаляем пустые значения из массива

    if (count($arrSelCarTypes) == 1) {

        $jss = '
var tableContses = $(".table table-bordered kv-grid-table kv-table-wrap").children("tbody");
var tableContsesTR = tableContses.find("tr");

var izes = 0;
var numNotFulles = 0;

var arrayDataKeyses = [];
$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
    arrayDataKeyses[izes] = [];
    arrayDataKeyses[izes][0] = el;
    arrayDataKeyses[izes][1] = $(el).data("key");
    
    if($(el).children("td").eq(4).text() == "-") {
    arrayDataKeyses[izes][2] = 0;
    arrayDataKeyses[izes][3] = "1";
    numNotFulles++;
    } else {
    
    arrayDataKeyses[izes][3] = "0";
    
    var trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").find("td");
        
        if(trVal.length != 0) {
        
    var SummPrice = 0;
    
    for (var zz = 0; zz < trVal.length; zz++) {
    
    if($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() == "-") {
    arrayDataKeyses[izes][3] = "1";
    numNotFulles++;
    } else {
    SummPrice = SummPrice + parseInt($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() || 0);   
    }
    
    }
    
    arrayDataKeyses[izes][2] = SummPrice;
    
    } else {
    var SummPrice = 0;
    trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).text();
    
        if(trVal == "-") {
    arrayDataKeyses[izes][3] = "1";
    numNotFulles++;
    } else {
    SummPrice = SummPrice + parseInt(trVal || 0);   
    }
    
    arrayDataKeyses[izes][2] = SummPrice;
    }
    
    }

    izes++;
    }
});

function ReplaceItemsTableses(firstEl, secEl) {

    var content1 = $(arrayDataKeyses[firstEl][0]).html();
    var content1i = $(arrayDataKeyses[firstEl][0]).children("td:first").text();
    var content2 = $(arrayDataKeyses[secEl][0]).html();
    var content2i = $(arrayDataKeyses[secEl][0]).children("td:first").text();

    $(arrayDataKeyses[firstEl][0]).html(content2).show();
    $(arrayDataKeyses[secEl][0]).html(content1).show();
    $(arrayDataKeyses[firstEl][0]).children("td:first").text(content1i);
    $(arrayDataKeyses[secEl][0]).children("td:first").text(content2i);
    
 
izes = 0;
numNotFulles = 0;

arrayDataKeyses = [];
$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
    arrayDataKeyses[izes] = [];
    arrayDataKeyses[izes][0] = el;
    arrayDataKeyses[izes][1] = $(el).data("key");
    
    if($(el).children("td").eq(4).text() == "-") {
    arrayDataKeyses[izes][2] = 0;
    arrayDataKeyses[izes][3] = "1";
    numNotFulles++;
    } else {
    
    arrayDataKeyses[izes][3] = "0";
    
    var trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").find("td");
        
        if(trVal.length != 0) {
        
    var SummPrice = 0;
    
    for (var zz = 0; zz < trVal.length; zz++) {
    
    if($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() == "-") {
    arrayDataKeyses[izes][3] = "1";
    numNotFulles++;
    } else {
    SummPrice = SummPrice + parseInt($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() || 0);   
    }
    
    }
    
    arrayDataKeyses[izes][2] = SummPrice;
    
    } else {
    var SummPrice = 0;
    trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).text();
    
        if(trVal == "-") {
    arrayDataKeyses[izes][3] = "1";
    numNotFulles++;
    } else {
    SummPrice = SummPrice + parseInt(trVal || 0);   
    }
    
    arrayDataKeyses[izes][2] = SummPrice;
    }
    
    }

    izes++;
    }
});

$(arrayDataKeys[firstEl][0]).css("color", "#006699");
$(arrayDataKeys[secEl][0]).css("color", "#006699");

}

// Кнопка сортировать
var readyToSortSumm = 1;
var typeSortSumm = 1;

$("#sortSumm").click(function() {

if(readyToSortSumm == 1) {

if(typeSortSumm == 0) {
typeSortSumm = 1;
} else {
typeSortSumm = 0;
}

readyToSortSumm = 0;

            if(typeSortSumm == 0) {

    for (var z = 0; z < izes; z++) {

        var min = arrayDataKeyses[z][2];
        var min_i = z;

        for (var j = z+1; j < izes; j++) {

            if (arrayDataKeyses[j][2] < min) {
                min = arrayDataKeyses[j][2];
                min_i = j;
            }
        }

        if (z != min_i) {
        ReplaceItemsTableses(min_i, z);
        }
        
        if (z == (izes - 1)) {
        GetElements();
        MinPrice();
        BigPrice();
        }
        
     }

            } else {

    for (var z = 0; z < izes; z++) {

        var min = arrayDataKeyses[z][2];
        var min_i = z;

        for (var j = z+1; j < izes; j++) {

            if (arrayDataKeyses[j][2] > min) {
                min = arrayDataKeyses[j][2];
                min_i = j;
            }
            
        }

        if (z != min_i) {
        ReplaceItemsTableses(min_i, z);
        }
        
        if (z == (izes - 1)) {
        GetElements();
        MinPrice();
        BigPrice();
        }
        
     }

            }

readyToSortSumm = 1;

}

});

';

        $this->registerJs($jss);

    }

}

$arrSelCarTypes = Yii::$app->request->queryParams['CompanySearch']['cartypes'];

// удаляем пустые значения из массива
for ($i = 0; $i < count($arrSelCarTypes); $i++) {
    if (isset($arrSelCarTypes[$i])) {
        if ($arrSelCarTypes[$i] > 0) {

        } else {
            unset($arrSelCarTypes[$i]);
        }
    } else {
        if (count($arrSelCarTypes) == 1) {
            $arrSelCarTypes = [];
        }
    }
}
// удаляем пустые значения из массива

if (count($arrSelCarTypes) == 1) {

    if (isset(Yii::$app->request->queryParams['sort'])) {

        $numNeedColumn = 0;

        if(Yii::$app->request->queryParams['type'] == 2) {

            if(isset(Yii::$app->request->queryParams['CompanySearch']['services'])) {

                $services = Yii::$app->request->queryParams['CompanySearch']['services'];

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

                if((count($services) > 0) && (count($services) != 2)) {

                    if($services[0] == 1) {
                        $numNeedColumn = 0;
                    } else {
                        $numNeedColumn = 1;
                    }

                } else {
                    if (Yii::$app->request->queryParams['sort'] == 1) {
                        $numNeedColumn = 0;
                    } else if (Yii::$app->request->queryParams['sort'] == 2) {
                        $numNeedColumn = 1;
                    }
                }

            } else {

                if (Yii::$app->request->queryParams['sort'] == 1) {
                    $numNeedColumn = 0;
                } else if (Yii::$app->request->queryParams['sort'] == 2) {
                    $numNeedColumn = 1;
                }

            }

        } else if(Yii::$app->request->queryParams['type'] == 4) {

            if(isset(Yii::$app->request->queryParams['CompanySearch']['services'])) {

                $services = Yii::$app->request->queryParams['CompanySearch']['services'];

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

                if((count($services) > 0) && (count($services) != 4)) {

                    if(count($services) == 1) {

                        if($services[0] == 6) {
                            $numNeedColumn = 0;
                        } else if($services[0] == 7) {
                            $numNeedColumn = 1;
                        } else if($services[0] == 8) {
                            $numNeedColumn = 3;
                        } else if($services[0] == 9) {
                            $numNeedColumn = 2;
                        }

                    } else if(count($services) == 2) {

                        if(($services[0] == 6) && ($services[1] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 0;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 0;
                            }

                        }

                    } else if(count($services) == 3) {

                        if(($services[0] == 6) && ($services[1] == 7) && ($services[2] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 7) && ($services[2] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 9) && ($services[2] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 9) && ($services[2] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 8) && ($services[2] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 6) && ($services[1] == 8) && ($services[2] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 6) && ($services[2] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 6) && ($services[2] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 8) && ($services[2] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 8) && ($services[2] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 9) && ($services[2] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 7) && ($services[1] == 9) && ($services[2] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 6) && ($services[2] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 6) && ($services[2] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 7) && ($services[2] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 7) && ($services[2] == 9)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 9) && ($services[2] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 8) && ($services[1] == 9) && ($services[2] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 6) && ($services[2] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 6) && ($services[2] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 7) && ($services[2] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 7) && ($services[2] == 8)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 8) && ($services[2] == 6)) {

                            if (Yii::$app->request->queryParams['sort'] == 6) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        } else if(($services[0] == 9) && ($services[1] == 8) && ($services[2] == 7)) {

                            if (Yii::$app->request->queryParams['sort'] == 7) {
                                $numNeedColumn = 0;
                            } else if (Yii::$app->request->queryParams['sort'] == 9) {
                                $numNeedColumn = 1;
                            } else if (Yii::$app->request->queryParams['sort'] == 8) {
                                $numNeedColumn = 2;
                            }

                        }

                    }

                } else {
                    if (Yii::$app->request->queryParams['sort'] == 6) {
                        $numNeedColumn = 0;
                    } else if (Yii::$app->request->queryParams['sort'] == 7) {
                        $numNeedColumn = 1;
                    } else if (Yii::$app->request->queryParams['sort'] == 8) {
                        $numNeedColumn = 3;
                    } else if (Yii::$app->request->queryParams['sort'] == 9) {
                        $numNeedColumn = 2;
                    }
                }

            } else {

                if (Yii::$app->request->queryParams['sort'] == 6) {
                    $numNeedColumn = 0;
                } else if (Yii::$app->request->queryParams['sort'] == 7) {
                    $numNeedColumn = 1;
                } else if (Yii::$app->request->queryParams['sort'] == 8) {
                    $numNeedColumn = 3;
                } else if (Yii::$app->request->queryParams['sort'] == 9) {
                    $numNeedColumn = 2;
                }

            }

        }

        $js = '
var tableConts = $(".table table-bordered kv-grid-table kv-table-wrap").children("tbody");
var tableContsTR = tableConts.find("tr");

var iz = 0;
var numNotFull = 0;

var arrayDataKeys = [];

function GetElements() {

iz = 0;
numNotFull = 0;
arrayDataKeys = []

$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
    arrayDataKeys[iz] = [];
    arrayDataKeys[iz][0] = el;
    arrayDataKeys[iz][1] = $(el).data("key");
    
    if($(el).children("td").eq(4).text() == "-") {
    arrayDataKeys[iz][2] = 0;
    arrayDataKeys[iz][3] = "1";
    numNotFull++;
    } else {
    
    arrayDataKeys[iz][3] = "0";
    
    var trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").find("td");
        
        if(trVal.length != 0) {
        
    var SummPrice = 0;
    
    for (var zz = 0; zz < trVal.length; zz++) {
    
    if(zz == ' . $numNeedColumn . ') {
    
    if($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() == "-") {
    arrayDataKeys[iz][3] = "1";
    numNotFull++;
    } else {
    SummPrice = SummPrice + parseInt($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() || 0);   
    }
    
    }
    
    }
    
    arrayDataKeys[iz][2] = SummPrice;
    
    } else {
    var SummPrice = 0;
    trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).text();
    
        if(trVal == "-") {
    arrayDataKeys[iz][3] = "1";
    numNotFull++;
    } else {
    SummPrice = SummPrice + parseInt(trVal || 0);   
    }
    
    arrayDataKeys[iz][2] = SummPrice;
    }
    
    }

    iz++;
    }
});
}
GetElements();

// Выделить самый дорогой
function BigPrice() {

    var bigIndex = "-1";

    for (var zj = 0; zj < iz; zj++) {
    
    if((arrayDataKeys[zj][2] > 0) && (bigIndex == "-1")) {
    bigIndex = zj;
    } else if(bigIndex != "-1") {
    if((arrayDataKeys[bigIndex][2] < arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "0") && (arrayDataKeys[zj][2] > 0)) {
    bigIndex = zj;
    } else if((arrayDataKeys[bigIndex][2] < arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "1") && (arrayDataKeys[zj][2] > 0) && (numNotFull == iz)) {
    bigIndex = zj;  
    }
    }
    
    }

if(bigIndex != "-1") {
    $(arrayDataKeys[bigIndex][0]).css("color", "#c90606");
}
    
}
BigPrice();
// Выделить самый дорогой

// Выделить самый дешевый
function MinPrice() {

    var minIndex = "-1";

    for (var zj = 0; zj < iz; zj++) {
    
    if((arrayDataKeys[zj][2] > 0) && (minIndex == "-1")) {
    minIndex = zj;
    } else if(minIndex != "-1") {
    if((arrayDataKeys[minIndex][2] > arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "0") && (arrayDataKeys[zj][2] > 0)) {
    minIndex = zj;
    } else if((arrayDataKeys[minIndex][2] > arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "1") && (arrayDataKeys[zj][2] > 0) && (numNotFull == iz)) {
    minIndex = zj;  
    }
    }
    
    }

if(minIndex != "-1") {
    $(arrayDataKeys[minIndex][0]).css("color", "#028924");
}
    
}
MinPrice();
// Выделить самый дешевый

';
        $this->registerJs($js);

    } else {

        $js = '
var tableConts = $(".table table-bordered kv-grid-table kv-table-wrap").children("tbody");
var tableContsTR = tableConts.find("tr");

var iz = 0;
var numNotFull = 0;

var arrayDataKeys = [];

function GetElements() {

iz = 0;
numNotFull = 0;
arrayDataKeys = []

$.map($(".table tbody tr"), function(el) {

    if($(el).data("key") > 0) {
    arrayDataKeys[iz] = [];
    arrayDataKeys[iz][0] = el;
    arrayDataKeys[iz][1] = $(el).data("key");
    
    if($(el).children("td").eq(4).text() == "-") {
    arrayDataKeys[iz][2] = 0;
    arrayDataKeys[iz][3] = "1";
    numNotFull++;
    } else {
    
    arrayDataKeys[iz][3] = "0";
    
    var trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").find("td");
        
        if(trVal.length != 0) {
        
    var SummPrice = 0;
    
    for (var zz = 0; zz < trVal.length; zz++) {
    
    if($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() == "-") {
    arrayDataKeys[iz][3] = "1";
    numNotFull++;
    } else {
    SummPrice = SummPrice + parseInt($(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).children("table").children("tbody").children("tr").children("td").eq(zz).text() || 0);   
    }
    
    }
    
    arrayDataKeys[iz][2] = SummPrice;
    
    } else {
    var SummPrice = 0;
    trVal = $(el).children("td").eq(3).children("table").children("tbody").children("tr").eq(1).children("td").eq(1).text();
    
        if(trVal == "-") {
    arrayDataKeys[iz][3] = "1";
    numNotFull++;
    } else {
    SummPrice = SummPrice + parseInt(trVal || 0);   
    }
    
    arrayDataKeys[iz][2] = SummPrice;
    }
    
    }

    iz++;
    }
});
}
GetElements();

// Выделить самый дорогой
function BigPrice() {

    var bigIndex = "-1";

    for (var zj = 0; zj < iz; zj++) {
    
    if((arrayDataKeys[zj][2] > 0) && (bigIndex == "-1")) {
    bigIndex = zj;
    } else if(bigIndex != "-1") {
    if((arrayDataKeys[bigIndex][2] < arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "0") && (arrayDataKeys[zj][2] > 0)) {
    bigIndex = zj;
    } else if((arrayDataKeys[bigIndex][2] < arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "1") && (arrayDataKeys[zj][2] > 0) && (numNotFull == iz)) {
    bigIndex = zj;  
    }
    }
    
    }

if(bigIndex != "-1") {
    $(arrayDataKeys[bigIndex][0]).css("color", "#c90606");
}
    
}
BigPrice();
// Выделить самый дорогой

// Выделить самый дешевый
function MinPrice() {

    var minIndex = "-1";

    for (var zj = 0; zj < iz; zj++) {
    
    if((arrayDataKeys[zj][2] > 0) && (minIndex == "-1")) {
    minIndex = zj;
    } else if(minIndex != "-1") {
    if((arrayDataKeys[minIndex][2] > arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "0") && (arrayDataKeys[zj][2] > 0)) {
    minIndex = zj;
    } else if((arrayDataKeys[minIndex][2] > arrayDataKeys[zj][2]) && (arrayDataKeys[zj][3] == "1") && (arrayDataKeys[zj][2] > 0) && (numNotFull == iz)) {
    minIndex = zj;  
    }
    }
    
    }

if(minIndex != "-1") {
    $(arrayDataKeys[minIndex][0]).css("color", "#028924");
}
    
}
MinPrice();
// Выделить самый дешевый

';
        $this->registerJs($js);
    }

}