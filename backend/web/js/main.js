/**
 * Created by user on 26.10.2016.
 */
$(document).ready(function() {
});

this.checkAlerts = function(options) {
    $.ajax({url: "/company-offer/get-alert"})
        .done(function(response) {
            if(response) {
                response = JSON.parse(response);
                console.log(response.title);
                BootstrapDialog.show({
                    draggable: true,
                    closable: false,
                    nl2br: false,
                    title: response.title,
                    buttons: [{
                        label: 'Отложить на 5 минут',
                        cssClass: 'btn-warning',
                        action: function(dialogRef) {
                            $.ajax({url: "/company-offer/delay?id=" + response.id})
                                .done(function(response) {
                                    response = JSON.parse(response);
                                    if(response.code == 1) {
                                        dialogRef.close();
                                    }
                                })
                        }
                    }, {
                        label: 'Сохранить',
                        cssClass: 'btn-primary',
                        action: function(dialogRef) {
                            $.post($("#offer-form").attr('action'), $("#offer-form").serialize());
                            dialogRef.close();
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
                setTimeout(checkAlerts, 10000);
            }
        });
    //setTimeout(checkAlerts, 10000);
}