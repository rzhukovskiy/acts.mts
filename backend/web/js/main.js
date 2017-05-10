var PageTitleNotification = {
    Vars:{
        OriginalTitle: document.title,
        Interval: null
    },
    On: function(notification, intervalSpeed){
        var _this = this;
        _this.Vars.Interval = setInterval(function(){
            document.title = (_this.Vars.OriginalTitle == document.title)
                ? notification
                : _this.Vars.OriginalTitle;
        }, (intervalSpeed) ? intervalSpeed : 1000);
    },
    Off: function(){
        clearInterval(this.Vars.Interval);
        document.title = this.Vars.OriginalTitle;
    }
}

this.checkAlerts = function(options) {
    $.ajax({url: "/company-offer/get-alert"})
        .done(function(response) {
            response = JSON.parse(response);
            if(response.id) {
                PageTitleNotification.On('Срочно! Надо звонить!');
                document.getElementById('bflat').play();
                BootstrapDialog.show({
                    draggable: true,
                    closable: false,
                    nl2br: false,
                    title: response.title,
                    buttons: [{
                        label: 'Отложить на 5 минут',
                        cssClass: 'btn-warning',
                        action: function(dialogRef) {
                            PageTitleNotification.Off();
                            $.ajax({url: "/company-offer/delay?id=" + response.id})
                                .done(function(response) {
                                    response = JSON.parse(response);
                                    if(response.code == 1) {
                                        dialogRef.close();
                                    }
                                });
                            setTimeout(checkAlerts, 60000);
                        }
                    }, {
                        label: 'Сохранить',
                        cssClass: 'btn-primary',
                        action: function(dialogRef) {
                            $.post($("#offer-form").attr('action'), $("#offer-form").serialize());
                            PageTitleNotification.Off();
                            dialogRef.close();
                            setTimeout(checkAlerts, 60000);
                        }
                    }],
                    message: response.content,
                    onshown: function() {
                        $("#companyoffer-communication_str-datetime").datetimepicker({
                            "autoclose":true,
                            "format":"dd-mm-yyyy hh:ii",
                            "language":"ru"
                        });
                    }
                });
            } else {
                setTimeout(checkAlerts, 60000);
            }
        });
    //setTimeout(checkAlerts, 10000);
}
//Навигационные кнопки
function navigationButton() {
    $(function () {
        var $elem = $('body');

        $('#nav_up').fadeIn('slow');
        $('#nav_down').fadeIn('slow');

        $(window).bind('scrollstart', function () {
            $('#nav_up,#nav_down').stop();
        });
        $(window).bind('scrollstop', function () {
            $('#nav_up,#nav_down').stop();
        });

        $('#nav_down').click(
            function (e) {
                $('html, body').animate({scrollTop: $elem.height()}, 800);
            }
        );
        $('#nav_up').click(
            function (e) {
                $('html, body').animate({scrollTop: '0px'}, 800);
            }
        );
    });
}
$(document).ready(function () {
    navigationButton();
});