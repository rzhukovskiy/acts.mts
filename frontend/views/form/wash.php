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
        <h1 class="text-center hidden">ЗАЯВКА ДЛЯ МОЙКИ</h1>
        <img src="./img/top2.png" alt="заявка для мойки">
    </div>
<?php
$form = ActiveForm::begin([
    'id' => 'act-form',
]) ?>
    <div class="row">
        <div class="col-md-offset-1 col-md-5">
            <?= $form->field($model, 'name')->textInput([]) ?>
            <?= $form->field($model, 'index')->textInput([]) ?>
            <?= $form->field($model, 'city')->textInput([]) ?>
            <?= $form->field($model, 'street')->textInput([]) ?>
            <?= $form->field($model, 'building')->textInput([]) ?>
            <?= $form->field($model, 'phone')->textInput([]) ?>
            <div class="form-group">
                <label>Часы работы</label>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'work_from')->textInput([])->label('От') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'work_to')->textInput([])->label('До') ?>
            </div>
        </div>
        <div class="col-md-5">
            <p class=""><strong>Прочтите внимательно инструкцию!</strong></p>

            <p class="">Для того, чтобы мы могли начать сотрудничество в кратчайшие сроки, просим предоставить
                максимально полную и точную информацию:</p>

            <p class="">Укажите название Вашей организации</p>

            <p class="">Адрес &ndash; укажите точный адрес нахождения автомойки и номер телефона (администратора), по
                которому производится запись</p>

            <p class="">ФИО &ndash; укажите данные руководителя и сотрудника, отвечающего за договорную работу</p>

            <p class="">E-mail &ndash; укажите адрес электронной почты для постоянной связи</p>

            <p class="">Телефон &ndash; номер телефона для записи на мойку</p>

            <p class="">Укажите названия 2,3-х организаций, транспорт которых обслуживается у вас (для получения
                рекомендаций)</p>

            <p class="">Просмотрите видео, которое находится внизу страницы. По представленной на видео системе мы
                работаем во всех странах, в которых получаем услуги автомойки</p>

            <p class="">
                Скачайте наш типовой договор
                <a href="files/wash.doc" target="_blank">
                    <button type="button" class="btn btn-primary">Скачать договор</button>
                </a>
                Он также единый. При возникновении вопросов - можете составить протокол разногласий или обсудить
                интересующие вас пункты с нашим специалистом
            </p>
            <p class="">В договоре находится приложение №1, в котором указаны вид ТС и услуги, интересующие нас. Просим
                также ознакомиться и заполнить его</p>

            <p class="">Отправьте заполненную анкету, и мы свяжемся с вами в ближайшее время.</p>
        </div>
    </div>
<?= $this->render('_director',
    [
        'model' => $model,
        'form'  => $form,
    ]); ?>
<div class="row">
    <div class="col-md-offset-1 col-md-5">
        <div class="items">
            <div class="form-group row">
                <label>Организации, транспорт которых обслуживается у вас:</label>
            </div>
            <?= $this->render('_wash_organisation',
                [
                    'isFirst' => true,
                ]);
            ?>
            <?= $this->render('_wash_organisation',
                [
                    'isFirst' => false,
                ]);
            ?>
            <?= $this->render('_wash_organisation',
                [
                    'isFirst' => false,
                ]);
            ?>
        </div>
        <div class="row">
            <div class="col-md-11">
                <button type="button" class="btn btn-primary btn-remove" title="Убрать организацию">-</button>
                <button type="button" class="btn btn-primary btn-add" title="Добавить организацию">+</button>
            </div>
        </div>
        <div class="row form-group">
            <div class="col-md-11">
                <button type="submit" class="btn btn-primary">Отправить</button>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <!-- Start EasyHtml5Video.com BODY section -->
        <style type="text/css">.easyhtml5video .eh5v_script {
                display: none
            }</style>
        <div class="easyhtml5video" style="position:relative;max-width:1280px;">
            <video controls="controls" poster="./img/video.jpg" style="width:100%" title="video">
                <source src="files/video.m4v" type="video/mp4"/>
                <source src="files/video.webm" type="video/webm"/>
                <source src="files/video.ogv" type="video/ogg"/>
                <source src="files/video.mp4"/>
                <object type="application/x-shockwave-flash" data="eh5v.files/html5video/flashfox.swf" width="1280"
                        height="768" style="position:relative;">
                    <param name="movie" value="files/flashfox.swf"/>
                    <param name="allowFullScreen" value="true"/>
                    <param name="flashVars"
                           value="autoplay=false&amp;controls=true&amp;fullScreenEnabled=true&amp;posterOnEnd=true&amp;loop=false&amp;poster=img/video.jpg&amp;src=video.m4v"/>
                    <embed src="files/flashfox.swf" width="1280" height="768" style="position:relative;"
                           flashVars="autoplay=false&amp;controls=true&amp;fullScreenEnabled=true&amp;posterOnEnd=true&amp;loop=false&amp;poster=eh5v.files/html5video/video.jpg&amp;src=video.m4v"
                           allowFullScreen="true" wmode="transparent" type="application/x-shockwave-flash"
                           pluginspage="http://www.adobe.com/go/getflashplayer_en"/>
                    <img alt="video" src="files/video.jpg" style="position:absolute;left:0;" width="100%"
                         title="Video playback is not supported by your browser"/>
                </object>
            </video>
            <script src="./js/html5ext.js" type="text/javascript"></script>
            <!-- End EasyHtml5Video.com BODY section -->
        </div>
    </div>
<?php ActiveForm::end() ?>