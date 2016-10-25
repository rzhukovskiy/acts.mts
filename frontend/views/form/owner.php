<?php
use yii\bootstrap\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model \common\models\Act
 * @var $company bool
 */

$this->title = 'Редактирование акта';

$request = Yii::$app->request;

?>
    <div class="page-header">
        <h1 class="text-center hidden">ЗАЯВКА НА МОЙКУ</h1>
        <img class="small" src="./img/mts_zayavka_shapka.png" alt="заявка на мойку">
    </div>
<?php
$form = ActiveForm::begin([
    'id' => 'act-form',
]) ?>
    <div class="row">
        <div class="col-md-offset-1 col-md-5">
            <?= $form->field($model, 'name')->textInput([]) ?>
            <?= $form->field($model, 'company')->textInput([]) ?>
            <?= $form->field($model, 'email')->textInput([]) ?>
            <?= $form->field($model, 'phone')->textInput([]) ?>
            <?= $form->field($model, 'town')->textInput([]) ?>
            <div class="">
                <!-- Start EasyHtml5Video.com BODY section -->
                <style type="text/css">.easyhtml5video .eh5v_script {
                        display: none
                    }</style>
                <div class="easyhtml5video" style="position:relative;max-width:1280px;">
                    <video controls="controls" poster="img/video1.jpg" style="width:100%" title="video">
                        <source src="files/video1.m4v" type="video/mp4"/>
                        <source src="files/video1.webm" type="video/webm"/>
                        <source src="files/video1.ogv" type="video/ogg"/>
                        <source src="files/video1.mp4"/>
                        <object type="application/x-shockwave-flash" data="eh5v.files/html5video/flashfox.swf"
                                width="1280" height="768" style="position:relative;">
                            <param name="movie" value="files/flashfox.swf"/>
                            <param name="allowFullScreen" value="true"/>
                            <param name="flashVars"
                                   value="autoplay=false&amp;controls=true&amp;fullScreenEnabled=true&amp;posterOnEnd=true&amp;loop=false&amp;poster=img/video1.jpg&amp;src=video1.m4v"/>
                            <embed src="files/flashfox.swf" width="1280" height="768" style="position:relative;"
                                   flashVars="autoplay=false&amp;controls=true&amp;fullScreenEnabled=true&amp;posterOnEnd=true&amp;loop=false&amp;poster=eh5v.files/html5video/video1.jpg&amp;src=video1.m4v"
                                   allowFullScreen="true" wmode="transparent" type="application/x-shockwave-flash"
                                   pluginspage="http://www.adobe.com/go/getflashplayer_en"/>
                            <img alt="video" src="files/video1.jpg" style="position:absolute;left:0;" width="100%"
                                 title="Video playback is not supported by your browser"/>
                        </object>
                    </video>
                    <script src="js/html5ext.js" type="text/javascript"></script>
                    <!-- End EasyHtml5Video.com BODY section -->
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <p class=""><strong>Прочтите внимательно инструкцию!</strong></p>

            <p class="">Для того, чтобы мы могли сделать вам наиболее выгодное предложение, просим предоставить
                максимально полную и точную информацию:</p>

            <p class="">ФИО &ndash; укажите данные сотрудника, отвечающего за направление транспорта</p>

            <p class="">Компания &ndash; укажите название вашей компании</p>

            <p class="">E-mail &ndash; укажите адрес электронной почты для постоянной связи</p>

            <p class="">Телефон &ndash; укажите контактный телефон</p>

            <p class="">Города &ndash; укажите через запятую названия городов, в которых Вам было бы интересно получить
                сервис для вашего грузового транспорта</p>

            <p class="">Марка, Вид, Количество ТС &ndash; укажите состав вашего парка грузового транспорта</p>

            <p class="">Также просим обратить внимание на видео-материал, в котором подробно показана система нашей
                работы на примере услуги “Грузовая Автомойка”.</p>

            <p class="">Отправьте заполненную заявку, и мы свяжемся с вами в ближайшее время.</p>
        </div>

        <div class="col-md-offset-1 row">
            <div class="col-md-11 items">
                <?= $this->render('_owner_ts',
                    [
                        'isFirst' => true,
                    ]);
                ?>
                <?= $this->render('_owner_ts',
                    [
                        'isFirst' => false,
                    ]);
                ?>
            </div>
        </div>


        <div class="row">
            <div class="col-md-11 col-md-offset-1">
                <button type="button" class="btn btn-primary btn-remove" title="Убрать автомобиль">-</button>
                <button type="button" class="btn btn-primary btn-add" title="Добавить автомобиль">+</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-11 col-md-offset-1">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>