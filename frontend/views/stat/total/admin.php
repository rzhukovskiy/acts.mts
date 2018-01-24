<?php

use kartik\grid\GridView;
use yii\widgets\Pjax;
use common\models\Service;
use yii\bootstrap\Html;
use common\assets\CanvasJs\CanvasJsAsset;
use common\models\Company;
use \yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\View;

/**
 * @var $this yii\web\View
 * @var $type int
 * @var $searchModel \frontend\models\search\ActSearch
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $chartData array
 * @var $totalProfit int
 * @var $totalServe int
 * @var $totalExpense int
 * @var $totalIncome int
 * @var $group string
 */
$actionLinkCompare = Url::to('@web/stat/compare');
$nowMonth = date('n', strtotime("-1 month"));
$nowYear = date('Y', time());
$datanow = date('Y-m-d', time());
$script = <<< JS

var arrMonth = [];
var arrYear = [];
var arrDay = '';
var arrMonthYears = [];

// открываем модальное окно сравнения по дням
$('.compare-day').on('click', function() {
    $('input[name="from_date"]').val('$datanow');
    $('#showListsDay').modal('show');
    
});

// открываем модальное окно сравнения по месяцу
$('.compare').on('click', function() {
    $('#showListsName').modal('show');
    // убираем галочки
    $('input[type="checkbox"]').removeAttr('checked');
    $('input[type="checkbox"][value="$nowMonth"]').prop('checked','checked');
    
    //сбрасываем селектор
    arrMonthYears = [];
    arrMonth = [];
    var now = new Date();
    var yearM = now.getFullYear();
    $('.yearMonth').val(yearM);
    
});

// открываем модальное окно сравнения по году
$('.compare-year').on('click', function() {
    $('#showListsYear').modal('show');
    // убираем галочки
     
    $('input[type="checkbox"]').removeAttr('checked');
    $('input[type="checkbox"][value="$nowYear"]').prop('checked','checked');
    arrYear = [];
});

// Нажимаем на кнопку сравнить В днях
$('.addNewDay').on('click', function() {
    arrMonth = [];
    arrYear = [];
    arrDay = '';
    $('#showListsDay').modal('hide');
    
    arrDay = $('input[name="from_date"]').val();
    sendCompare();
    $('#showSettingsList').modal('show');
});

// Нажимаем на кнопку сравнить В месяцах

$('.addNewItem').on('click', function() {
    
    arrMonth = [];
    arrYear = [];
    arrDay = '';
    $('#showListsName').modal('hide');
    
        var selectMonth = 1;
    
       $('.monthList').each(function (value) {
      if ($(this).is(':checked')) {
          arrMonth.push($(this).val());
          
          if($("#yearOnMonth[data-month='" + selectMonth + "']") != "undefined" && $("#yearOnMonth[data-month='" + selectMonth + "']") !== null) {  
          arrMonthYears.push($("#yearOnMonth[data-month='" + selectMonth + "']").val());
          }
          
     }
     selectMonth++;
       
});
      sendCompare();
    $('#showSettingsList').modal('show');
});

// Нажимаем на кнопку сравнить В годах
$('.addNewYear').on('click', function() {
    
    arrYear = [];
    arrMonth = [];
    arrDay = '';
    $('#showListsYear').modal('hide');
    
       $('.yearList').each(function (id, value) {
           var thisId = $(this);
      if (thisId.is(':checked')) {
            arrYear.push(thisId.val());
     }
       
});
      sendCompare();
    $('#showSettingsList').modal('show');
});

function sendCompare() {
          $.ajax({
         
                type     :'POST',
                cache    : true,
                data: 'arrMonth=' + JSON.stringify(arrMonth) + '&arrYear=' + JSON.stringify(arrYear) + '&arrMonthYears=' + JSON.stringify(arrMonthYears) + '&arrDay=' + JSON.stringify(arrDay),
                url  : '$actionLinkCompare',
                success  : function(data) {
                var resTables = "";
                var reswash = "";
                var restires = "";
                var resdesinf = "";
                var resservise = "";
                var resall = "";
                var response = $.parseJSON(data);
               
                var countServe = '';
                var ssoom = '';
                var income = '';
                var profit = '';
                
                var month = [];
                month['1'] = "Январь";
                month['2'] = "Февраль";
                month['3'] = "Март";
                month['4'] = "Апрель";
                month['5'] = "Май";
                month['6'] = "Июнь";
                month['7'] = "Июль";
                month['8'] = "Август";
                month['9'] = "Сентябрь";
                month['10'] = "Октябрь";
                month['11'] = "Ноябрь";
                month['12'] = "Декабрь";
                               
                var today = new Date();
                var yr = today.getFullYear();
                var year = [];
                year[yr] = yr;
                for (i = 10; i > 0; i--) {
                year[yr-i] = yr - i;
                }
                
                var oldvalue = [];
                oldvalue[2] = [];
                oldvalue[2]['1'] = '';
                oldvalue[2]['2'] = '';
                oldvalue[2]['3'] = '';
                oldvalue[2]['4'] = '';
                oldvalue[4] = [];
                oldvalue[4]['1'] = '';
                oldvalue[4]['2'] = '';
                oldvalue[4]['3'] = '';
                oldvalue[4]['4'] = '';
                oldvalue[3] = [];
                oldvalue[3]['1'] = '';
                oldvalue[3]['2'] = '';
                oldvalue[3]['3'] = '';
                oldvalue[3]['4'] = '';
                oldvalue[5] = [];
                oldvalue[5]['1'] = '';
                oldvalue[5]['2'] = '';
                oldvalue[5]['3'] = '';
                oldvalue[5]['4'] = '';
                oldvalue[6] = [];
                oldvalue[6]['1'] = '';
                oldvalue[6]['2'] = '';
                oldvalue[6]['3'] = '';
                oldvalue[6]['4'] = '';
                
                var splitFloat = "";
                var splitInt = "";
                
               var sumArr = [];
      
                if (response.success == 'true') {
                  
                // Удачно
                var arr = $.parseJSON(response.result);
                
                $.each(arr,function(key,data) {
                    
                 $.each(data, function(index,value) {
                    
                        if(!sumArr[index]) {
                            sumArr[index] = [];
                        }
                     
                        if(!sumArr[index]['countServe']) {
                            sumArr[index]['countServe'] = 0;
                        }
                        if(!sumArr[index]['ssoom']) {
                            sumArr[index]['ssoom'] = 0;
                        }
                        
                        if(!sumArr[index]['income']) {
                            sumArr[index]['income'] = 0;
                        }
                        
                        if(!sumArr[index]['profit']) {
                            sumArr[index]['profit'] = 0;
                        }
                        
                        sumArr[index]['countServe'] += parseFloat(value['countServe']);
                        sumArr[index]['ssoom'] += parseFloat(value['ssoom']);
                        sumArr[index]['income'] += parseFloat(value['income']);
                        sumArr[index]['profit'] += parseFloat(value['profit']);
                        sumArr[index]['served_at'] = value['served_at'];
                 
               
                     if (key == 2) {
                        
                        if(oldvalue[2]['1'] != '') {
                        if (oldvalue[2]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[2]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['countServe'] - oldvalue[2]['1'])/oldvalue[2]['1']*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        if(oldvalue[2]['2'] != '') {
                        if (oldvalue[2]['2'] > parseFloat(value['ssoom'])) {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['ssoom'] - oldvalue[2]['2'])/value['ssoom']*100).toFixed(1)) + '%</span>';
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['ssoom'] - oldvalue[2]['2'])/oldvalue[2]['2']*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        if(oldvalue[2]['3'] != '') {
                        if (oldvalue[2]['3'] > parseFloat(value['income'])) {
                            if (value['income'] > parseInt(value['income'])) {
                                 splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[2]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        } else { 
                         income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[2]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[2]['3'])/oldvalue[2]['3']*100).toFixed(1))  + '%</span>';
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[2]['3'])/oldvalue[2]['3']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                         if(oldvalue[2]['4'] != '') {
                        if (oldvalue[2]['4'] > parseFloat(value['profit'])) {
                            if (value['profit'] > parseInt(value['profit'])) {
                                 splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[2]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        } else { 
                         profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[2]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[2]['4'])/oldvalue[2]['4']*100).toFixed(1))  + '%</span>';
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[2]['4'])/oldvalue[2]['4']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                       
                        oldvalue[2]['1'] = parseFloat(value['countServe']);
                        oldvalue[2]['2'] = parseFloat(value['ssoom']);
                        oldvalue[2]['3'] = parseFloat(value['income']);
                        oldvalue[2]['4'] = parseFloat(value['profit']);
                        
                        var dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                        if (arrMonth.length > 0) {
                            reswash += "<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else if (arrDay.length > 0) {
                            reswash += "<tr><td>" + index + ' ' + month[(dateShow.getMonth()+1)] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else {
                            reswash += "<tr><td>" + year[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        }
                        
                     }
                     
                     
                     if (key == 4) {
                         
                         if (oldvalue[4]['1'] != '') {
                         if (oldvalue[4]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[4]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                         } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['countServe'] - oldvalue[4]['1'])/oldvalue[4]['1']*100).toFixed(1))  + '%</span>';
                         }
                         } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                         }
                       
                        if (oldvalue[4]['2'] != '') {
                        if (oldvalue[4]['2'] > parseFloat(value['ssoom'])) {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['ssoom'] - oldvalue[4]['2'])/value['ssoom']*100).toFixed(1)) + '%</span>';
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['ssoom'] - oldvalue[4]['2'])/oldvalue[4]['2']*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                       
                        if (oldvalue[4]['3'] != '') {
                        if (oldvalue[4]['3'] > parseFloat(value['income'])) {
                            if (value['income'] > parseInt(value['income'])) {
                                 splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[4]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        } else { 
                         income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[4]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[4]['3'])/oldvalue[4]['3']*100).toFixed(1))  + '%</span>';
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[4]['3'])/oldvalue[4]['3']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        if (oldvalue[4]['4'] != '') {
                        if (oldvalue[4]['4'] > parseFloat(value['profit'])) {
                            if (value['profit'] > parseInt(value['profit'])) {
                                 splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[4]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        } else { 
                         profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[4]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[4]['4'])/oldvalue[4]['4']*100).toFixed(1))  + '%</span>';
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[4]['4'])/oldvalue[4]['4']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        oldvalue[4]['1'] = parseFloat(value['countServe']);
                        oldvalue[4]['2'] = parseFloat(value['ssoom']);
                        oldvalue[4]['3'] = parseFloat(value['income']);
                        oldvalue[4]['4'] = parseFloat(value['profit']);
                         
                        dateShow = '';
                        dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                         if (arrMonth.length > 0) {
                            restires += "<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else if (arrDay.length > 0) {
                            restires += "<tr><td>" + index + ' ' + month[(dateShow.getMonth()+1)] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else {
                            restires += "<tr><td>" + year[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        }
                     
                     }
                     
                     if (key == 3) {
                         
                         if (oldvalue[3]['1'] != '') {
                         if (oldvalue[3]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[3]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                         } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['countServe'] - oldvalue[3]['1'])/oldvalue[3]['1']*100).toFixed(1))  + '%</span>';
                         }
                         } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                         }
                       
                        if (oldvalue[3]['2'] != '') {
                        if (oldvalue[3]['2'] > parseFloat(value['ssoom'])) {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['ssoom'] - oldvalue[3]['2'])/value['ssoom']*100).toFixed(1)) + '%</span>';
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['ssoom'] - oldvalue[3]['2'])/oldvalue[3]['2']*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                       
                        if (oldvalue[3]['3'] != '') {
                        if (oldvalue[3]['3'] > parseFloat(value['income'])) {
                            if (value['income'] > parseInt(value['income'])) {
                                 splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[3]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        } else { 
                         income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[3]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[3]['3'])/oldvalue[3]['3']*100).toFixed(1))  + '%</span>';
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[3]['3'])/oldvalue[3]['3']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        if (oldvalue[3]['4'] != '') {
                        if (oldvalue[3]['4'] > parseFloat(value['profit'])) {
                            if (value['profit'] > parseInt(value['profit'])) {
                                 splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[3]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        } else { 
                         profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[3]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[3]['4'])/oldvalue[3]['4']*100).toFixed(1))  + '%</span>';
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[3]['4'])/oldvalue[3]['4']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        oldvalue[3]['1'] = parseFloat(value['countServe']);
                        oldvalue[3]['2'] = parseFloat(value['ssoom']);
                        oldvalue[3]['3'] = parseFloat(value['income']);
                        oldvalue[3]['4'] = parseFloat(value['profit']);
                         
                        dateShow = '';
                        dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                        if (arrMonth.length > 0) {
                            resservise += "<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else if (arrDay.length > 0) {
                            resservise += "<tr><td>" + index + ' ' + month[(dateShow.getMonth()+1)] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else {
                            resservise += "<tr><td>" + year[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        }
                     }
                     
                     
                     
                     if (key == 5) {
                         
                        if(oldvalue[5]['1'] != '') {
                        if (oldvalue[5]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[5]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[5]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                           countServe = value['countServe'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        if(oldvalue[5]['2'] != '') {
                        if (oldvalue[5]['2'] > parseFloat(value['ssoom'])) {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['ssoom'] - oldvalue[5]['2'])/value['ssoom']*100).toFixed(1)) + '%</span>';
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['ssoom'] - oldvalue[5]['2'])/value['ssoom']*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           ssoom = value['ssoom'].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        if(oldvalue[5]['3'] != '') {
                        if (oldvalue[5]['3'] > parseFloat(value['income'])) {
                            if (value['income'] > parseInt(value['income'])) {
                                 splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[5]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        } else { 
                         income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[5]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[5]['3'])/oldvalue[5]['3']*100).toFixed(1))  + '%</span>';
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[5]['3'])/oldvalue[5]['3']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        if(oldvalue[5]['4'] != '') {
                        if (oldvalue[5]['4'] > parseFloat(value['profit'])) {
                            if (value['profit'] > parseInt(value['profit'])) {
                                 splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[5]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        } else { 
                         profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[5]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[5]['4'])/oldvalue[5]['4']*100).toFixed(1))  + '%</span>';
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[5]['4'])/oldvalue[5]['4']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        oldvalue[5]['1'] = parseFloat(value['countServe']);
                        oldvalue[5]['2'] = parseFloat(value['ssoom']);
                        oldvalue[5]['3'] = parseFloat(value['income']);
                        oldvalue[5]['4'] = parseFloat(value['profit']);
                        
                        dateShow = '';
                        dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                        if (arrMonth.length > 0) {
                            resdesinf += "<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else if (arrDay.length > 0) {
                            resdesinf += "<tr><td>" + index + ' ' + month[(dateShow.getMonth()+1)] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        } else {
                            resdesinf += "<tr><td>" + year[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                        }
                     }
                      
                            });
                    });
                       
                        $.each(sumArr, function(index,value) {
                       
                         if (typeof sumArr[index] !== 'undefined' && sumArr[index] !== null) {
                         
                         if(oldvalue[6]['1'] != '') {
                        if (oldvalue[6]['1'] > parseFloat(value['countServe'])) {
                           countServe = value['countServe'].toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[6]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        } else {
                           countServe = value['countServe'].toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' + Math.abs(((value['countServe'] - oldvalue[6]['1'])/value['countServe']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                           countServe = value['countServe'].toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        if(oldvalue[6]['2'] != '') {
                        if (oldvalue[6]['2'] > (parseFloat(value['profit'])/parseFloat(value['countServe']))) {
                           ssoom = (parseFloat(value['profit'])/parseFloat(value['countServe'])).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs((((parseFloat(value['profit'])/parseFloat(value['countServe'])) - oldvalue[6]['2'])/(parseFloat(value['profit'])/parseFloat(value['countServe']))*100).toFixed(1)) + '%</span>';
                        } else {
                           ssoom = (parseFloat(value['profit'])/parseFloat(value['countServe'])).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs((((parseFloat(value['profit'])/parseFloat(value['countServe'])) - oldvalue[6]['2'])/(parseFloat(value['profit'])/parseFloat(value['countServe']))*100).toFixed(1))  + '%</span>';
                        }
                        } else {
                           ssoom = (parseFloat(value['profit'])/parseFloat(value['countServe'])).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");
                        }
                        
                        if(oldvalue[6]['3'] != '') {
                        if (oldvalue[6]['3'] > parseFloat(value['income'])) {
                            if (value['income'] > parseInt(value['income'])) {
                                 splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[6]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        } else { 
                         income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['income'] - oldvalue[6]['3'])/value['income']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[6]['3'])/oldvalue[6]['3']*100).toFixed(1))  + '%</span>';
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['income'] - oldvalue[6]['3'])/oldvalue[6]['3']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['income'] > parseInt(value['income'])) {
                                splitFloat = (parseFloat(value['income']) - parseInt(value['income'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['income'])).toString().split('.');
                           income = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           income = parseInt(value['income']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        if(oldvalue[6]['4'] != '') {
                        if (oldvalue[6]['4'] > parseFloat(value['profit'])) {
                            if (value['profit'] > parseInt(value['profit'])) {
                                 splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                 splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[6]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        } else { 
                         profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:red;">&#8595 </span><span style="color:red; font-size:13px;">' + Math.abs(((value['profit'] - oldvalue[6]['4'])/value['profit']*100).toFixed(1)) + '%</span>';
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1] + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[6]['4'])/oldvalue[6]['4']*100).toFixed(1))  + '%</span>';
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + ' <span style="color:green;">&#8593 </span><span style="color:green; font-size:13px;">' +  Math.abs(((value['profit'] - oldvalue[6]['4'])/oldvalue[6]['4']*100).toFixed(1))  + '%</span>';
                        }
                        }
                        } else {
                            if (value['profit'] > parseInt(value['profit'])) {
                                splitFloat = (parseFloat(value['profit']) - parseInt(value['profit'])).toFixed(4).toString().split('.');
                                splitInt = (parseInt(value['profit'])).toString().split('.');
                           profit = splitInt[0].replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ") + '.' + splitFloat[1];
                        } else {
                           profit = parseInt(value['profit']).toFixed(0).replace(/(\d{1,3}(?=(\d{3})+(?:\.\d|\b)))/g,"\$1 ");    
                        }
                        }
                        
                        oldvalue[6]['1'] = parseFloat(value['countServe']);
                        oldvalue[6]['2'] = parseFloat(value['profit'])/parseFloat(value['countServe']);
                        oldvalue[6]['3'] = parseFloat(value['income']);
                        oldvalue[6]['4'] = parseFloat(value['profit']);
                        
                        var dateShow = new Date(parseInt(value['served_at']) * 1000);
                        
                         if (arrMonth.length > 0) {
                            resall += "<tr><td>" + month[index] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                         } else if (arrDay.length > 0) {
                            resall += "<tr><td>" + index + ' ' + month[(dateShow.getMonth()+1)] + ' ' + dateShow.getFullYear() + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                         } else {
                            resall += "<tr><td>" + year[index] + "</td><td>" + countServe + "</td><td>" + ssoom + "</td><td>" + income + "</td><td>" + profit + "</td></tr>";
                         }
                         }
                         });
                        
                    var nameColomn = "";
                    if (arrMonth.length > 0) {
                        nameColomn = 'Месяц';
                    } else if (arrDay.length > 0) {
                        nameColomn = 'День';
                    } else {
                        nameColomn = 'Год';
                    }
                     if (reswash.length > 0) {
                      resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Мойка</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:150px;'>" + nameColomn + "</td><td style='width:140px;'>Обслужено</td><td style='width:140px;'>ССООМ</td><td style='width:200px;'>Доход</td><td>Прибыль</td></tr>" + reswash +"</table></br>";
                     }
                     if (restires.length > 0) {
                      resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Шиномонтаж</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:150px;'>" + nameColomn + "</td><td style='width:140px;'>Обслужено</td><td style='width:140px;'>ССООМ</td><td style='width:200px;'>Доход</td><td>Прибыль</td></tr>" + restires +"</table></br>";
                     }
                     if (resservise.length > 0) {
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Сервис</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:150px;'>" + nameColomn + "</td><td style='width:140px;'>Обслужено</td><td style='width:140px;'>ССООМ</td><td style='width:200px;'>Доход</td><td>Прибыль</td></tr>" + resservise +"</table></br>";
                     }
                     if (resdesinf.length > 0) {
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Дезинфекция</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:150px;'>" + nameColomn + "</td><td style='width:140px;'>Обслужено</td><td style='width:140px;'>ССООМ</td><td style='width:200px;'>Доход</td><td>Прибыль</td></tr>" + resdesinf +"</table></br>";
                     }
                     if (resall.length > 0) {
                     resTables += "<table border='1' width='100%' bordercolor='#dddddd'><tr height='25px'><td colspan='5' align='center' style='color: #000000;'>Общая</td></tr><tr height='25px' style='background:#dff0d8;'><td style='width:150px;'>" + nameColomn + "</td><td style='width:140px;'>Обслужено</td><td style='width:140px;'>ССООМ</td><td style='width:200px;'>Доход</td><td>Прибыль</td></tr>" + resall +"</table></br>";
                     }
                
                    $('.place_list').html(resTables);
                    
                } else {
                // Неудачно
                $('.place_list').html();
                }
                
                }
                });
}

JS;
$this->registerJs($script, View::POS_READY);
CanvasJsAsset::register($this);

$css = ".modal {
    overflow-y: auto;
    font-size:17px;
}";
$this->registerCss($css);

$this->title = 'Общая статистика';
echo $this->render('../_tabs', ['action' => $group]);

/**
 * Виджет выбора диапазона дат
 */
$halfs = [
    '1е полугодие',
    '2е полугодие'
];
$quarters = [
    '1й квартал',
    '2й квартал',
    '3й квартал',
    '4й квартал',
];
$months = [
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

$ts1 = strtotime($searchModel->dateFrom);
$ts2 = strtotime($searchModel->dateTo);

$year1 = date('Y', $ts1);
$year2 = date('Y', $ts2);

$month1 = date('m', $ts1);
$month2 = date('m', $ts2);

$diff = (($year2 - $year1) * 12) + ($month2 - $month1);
switch ($diff) {
    case 1:
        $period = 1;
        break;
    case 3:
        $period = 2;
        break;
    case 6:
        $period = 3;
        break;
    case 12:
        $period = 4;
        break;
    default:
        $period = 0;
}

$rangeYear = range(date('Y') - 10, date('Y'));
$currentYear = isset($searchModel->dateFrom)
    ? date('Y', strtotime($searchModel->dateFrom))
    : date('Y');

$currentMonth = isset($searchModel->dateFrom)
    ? date('n', strtotime($searchModel->dateFrom))
    : date('n');
$currentMonth--;

$filters = '';
$periodForm = '';
$periodForm .= Html::dropDownList('period', $period, \common\models\Act::$periodList, [
    'class' =>'select-period form-control',
    'style' => 'margin-right: 10px;'
]);
$periodForm .= Html::dropDownList('month', $currentMonth, $months, [
    'id' => 'month',
    'class' => 'autoinput form-control',
    'style' => $diff == 1 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('half', $currentMonth < 5 ? 0 : 1, $halfs, [
    'id' => 'half',
    'class' => 'autoinput form-control',
    'style' => $diff == 6 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('quarter', floor($currentMonth / 3), $quarters, [
    'id' => 'quarter',
    'class' => 'autoinput form-control',
    'style' => $diff == 3 ? '' : 'display:none'
]);
$periodForm .= Html::dropDownList('year', array_search($currentYear, $rangeYear), range(date('Y') - 10, date('Y')), [
    'id' => 'year',
    'class' => 'autoinput form-control',
    'style' => $diff && $diff <= 12 ? '' : 'display:none'
]);
$periodForm .= Html::activeTextInput($searchModel, 'dateFrom', ['class' => 'date-from ext-filter hidden']);
$periodForm .= Html::activeTextInput($searchModel, 'dateTo',  ['class' => 'date-to ext-filter hidden']);
$periodForm .= Html::submitButton('Показать', ['class' => 'btn btn-primary date-send', 'style' => 'margin-left: 10px;']);

if ($admin) {
    $filters = 'Выбор компании: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['type' => Company::TYPE_OWNER])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
} elseif (!empty(Yii::$app->user->identity->company->children)) {
    $filters = 'Выбор филиала: ' . Html::activeDropDownList($searchModel, 'client_id', Company::find()->active()
            ->andWhere(['parent_id' => Yii::$app->user->identity->company_id])
            ->select(['name', 'id'])->indexBy('id')->column(), ['prompt' => 'все','class' => 'form-control ext-filter', 'style' => 'width: 200px; margin-right: 10px']);
}

$filters .= 'Выбор периода: ' . $periodForm;

/**
 * Конец виджета
 */

?>
<div class="panel panel-primary">
    <div class="panel-heading">
        Общая статистика
    </div>
    <div class="panel-body">
        <?php
        //Pjax::begin();
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'summary' => false,
            'emptyCell' => '',

            'showFooter' => true,
            'floatHeader' => $admin,
            'floatHeaderOptions' => ['top' => '0'],
            'hover' => false,
            'striped' => false,
            'export' => false,
            'filterSelector' => '.ext-filter',
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => $filters . '<span class="pull-right btn btn-danger btn-sm compare-year" style="padding: 6px 8px; margin-top: 2px; border:1px solid #c18431;">Сравнение по году</span>' . '<span class="pull-right btn btn-warning btn-sm compare" style="padding: 6px 8px; margin-top: 2px; border:1px solid #c18431;">Сравнение по месяцу</span>' . '<span class="pull-right btn btn-success btn-sm compare-day" style="padding: 6px 8px; margin-top: 2px; border:1px solid #c18431;">Сравнение по дням</span>',
                            'options' => ['colspan' => 8, 'style' => 'vertical-align: middle', 'class' => 'kv-grid-group-filter period-select'],
                        ],
                    ],
                    'options' => ['class' => 'filters extend-header'],
                ],
                [
                    'columns' => [
                        [
                            'content' => '&nbsp',
                            'options' => [
                                'colspan' => 8,
                            ]
                        ]
                    ],
                    'options' => ['class' => 'kv-group-header'],
                ],
            ],

            'columns' => [
                [
                    'header' => '№',
                    'class' => 'yii\grid\SerialColumn',
                    'footer' => 'Итого:',
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'service_type',
                    'label' => 'Услуга',
                    'content' => function ($data) use ($group) {
                        if (empty($data->service_type))
                            $title = '—';
                        else
                            $title = Html::a(Service::$listType[$data->service_type]['ru'], ['/stat/list', 'type' => $data->service_type, 'group' => $group]);

                        return $title;
                    },
                ],
                [
                    'attribute' => 'countServe',
                    'label' => 'Обслужено',
                    'footer' => $totalServe,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],
                [
                    'attribute' => 'ssoom',
                    'label' => 'ССООМ',
                    'contentOptions' => ['class' => 'success'],
                ],
                [
                    'attribute' => 'expense',
                    'label' => 'Расход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->expense, 0);
                    },
                    'footer' => $totalExpense,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                    'visible' => $group == 'partner',
                ],
                [
                    'attribute' => 'income',
                    'label' => 'Доход',
                    'content' => function ($data) {
                        return Yii::$app->formatter->asDecimal($data->income, 0);
                    },
                    'footer' => $totalIncome,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                    'visible' => $group == 'company',
                ],
                [
                    'attribute' => 'profit',
                    'label' => 'Прибыль',
                    'content' => function ($data) {
                        return Html::tag('strong', Yii::$app->formatter->asDecimal($data->profit, 0));
                    },
                    'footer' => $totalProfit,
                    'footerOptions' => ['style' => 'font-weight: bold'],
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) use ($group) {
                            if (isset(Yii::$app->request->queryParams['ActSearch'])) {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/list', 'type' => $model->service_type, 'group' => $group, 'ActSearch' => Yii::$app->request->queryParams['ActSearch']]);
                            } else {
                                return Html::a('<span class="glyphicon glyphicon-search"></span>', ['/stat/list', 'type' => $model->service_type, 'group' => $group]);
                            }
                        },
                    ]
                ],
            ],
        ]);
        //Pjax::end();

        // Модальное окно дней
        $modalListsName = Modal::begin([
            'header' => '<h5>Выбор дня</h5>',
            'id' => 'showListsDay',
            'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
            'size'=>'modal-sm',
        ]);


        echo \yii\jui\DatePicker::widget([
            'name'  => 'from_date',
            //'value' => date('Y-m-d'),
            'dateFormat' => 'yyyy-MM-dd',
        ]);

        echo "</br></br><span class='btn btn-primary btn-sm addNewDay'>Сравнить</span>";

        Modal::end();
        // Модальное окно дней

        // Модальное окно месяца
        $modalListsName = Modal::begin([
            'header' => '<h5>Выбор месяца</h5>',
            'id' => 'showListsName',
            'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
            'size'=>'modal-sm',
        ]);
        
        // Вывод селектора для года
        $select = [];
        
        for ($j = 1; $j <= 12; $j++) {
            
        $yearOnMonth = "";
        
        for ($i = 10; $i > 0; $i--) {
            $yearOnMonth .= "<option value='" . date('Y', strtotime("-$i year")) . "'>" . date('Y', strtotime("-$i year")) . "</option>";
        }
        
        $nowYearOnMonth = "<option selected value='" . date('Y', time()) . "'>" . date('Y', time()) . "</option>";
        $select[$j] = "<select id='yearOnMonth' class='yearMonth' data-month='" . $j . "'>$yearOnMonth $nowYearOnMonth</select>";
        
        }
        
        echo "<table><tr><td><input type='checkbox' class='monthList' value='1'> Январь </td><td>" . $select[1] . "</td></tr>" . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='2'> Февраль </td><td>" . $select[2] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='3'> Март </td><td>" . $select[3] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='4'> Апрель </td><td>" . $select[4] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='5'> Май </td><td>" . $select[5] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='6'> Июнь </td><td>" . $select[6] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='7'> Июль </td><td>" . $select[7] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='8'> Август </td><td>" . $select[8] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='9'> Сентябрь </td><td>" . $select[9] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='10'> Октябрь </td><td>" . $select[10] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='11'> Ноябрь </td><td>" . $select[11] . "</td></tr>";

        echo "<tr><td><input type='checkbox' class='monthList' value='12'> Декабрь </td><td>" . $select[12] . "</td></tr></table>";

        echo "</br><span class='btn btn-primary btn-sm addNewItem'>Сравнить</span></div>";

        Modal::end();
        // Модальное окно месяца

        // Модальное окно года
        $modalListsName = Modal::begin([
            'header' => '<h5>Выбор года</h5>',
            'id' => 'showListsYear',
            'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
            'size'=>'modal-sm',
        ]);


        for ($i = 10; $i > 0; $i--) {
            echo "<input type='checkbox' class='yearList' value='" . date('Y', strtotime("-$i year")) . "'> " . date('Y', strtotime("-$i year")) . "</br>";
        }
        echo "<input type='checkbox' class='yearList' value='" . date('Y', time()) . "'> " . date('Y', time()) . "</br>";
        echo "</br><span class='btn btn-primary btn-sm addNewYear'>Сравнить</span></div>";
        Modal::end();
        // Модальное окно сравнения

        // Модальное окно
        $modalListsName = Modal::begin([
            'header' => '<h5 class="settings_name">Сравнение</h5>',
            'id' => 'showSettingsList',
            'toggleButton' => ['label' => 'открыть окно','class' => 'btn btn-default', 'style' => 'display:none;'],
            'size'=>'modal-lg',
        ]);

        echo "<div class='place_list' style='margin-left:15px; margin-right:15px;'></div>";

        Modal::end();
        // Модальное окно

        ?>
        <hr>

        <div class="col-sm-12">
            <div id="chart_div" style="width:100%;height:500px;"></div>
            <?php
            $js = "CanvasJS.addColorSet('blue', ['#428bca']);
                var dataTable = " . $chartData . ";
                var max = 0;
                dataTable.forEach(function (value) {
                
                value.y = parseFloat(value.y);
                    if (value.y > max) max = value.y;
                });
                var options = {
                    colorSet: 'blue',
                    dataPointMaxWidth: 40,
                    title: {
                        text: 'По месяцам',
                        fontColor: '#069',
                        fontSize: 22
                    },
                    subtitles: [
                        {
                            text: 'Прибыль',
                            horizontalAlign: 'left',
                            fontSize: 14,
                            fontColor: '#069',
                            margin: 20
                        }
                    ],
                    data: [
                        {
                            type: 'column', //change it to line, area, bar, pie, etc
                            dataPoints: dataTable
                        }
                    ],
                    axisX: {
                        title: 'Месяц',
                        titleFontSize: 14,
                        titleFontColor: '#069',
                        titleFontWeight: 'bol',
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        interval: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black'
                    },

                    axisY: {
                        labelFontColor: '#069',
                        labelFontWeight: 'bold',
                        tickThickness: 1,
                        gridThickness: 1,
                        lineThickness: 1,
                        labelFontSize: 14,
                        lineColor: 'black',
                        valueFormatString: '### ### ###',
                        maximum: max + 0.1 * max
                    }
                };

                $('#chart_div').CanvasJSChart(options);
                ";
            $this->registerJs($js);
            ?>
        </div>

    </div>
</div>
