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
        <h1 class="text-center hidden">ЗАЯВКА ДЛЯ ШИНОМОНТАЖА</h1>
        <img src="./img/top4.png" alt="заявка для шиномонтажа">
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
            <?= $form->field($model, 'type_service')
                ->checkboxList(\frontend\models\forms\TiresForm::$listService)
                ->label('Услуги, которые вы оказываете:') ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'type_car_change_tires')
                ->checkboxList(\frontend\models\forms\TiresForm::$listCarType)
                ->label('Для какого вида ТС Вы производите шиномонтаж:') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-offset-1 col-md-5">
            <div class="items">
                <div class="form-group row">
                    <label><strong>Организации, которые обслуживаются у вас:</strong></label>
                </div>

                <?= $this->render('_tires_organisation',
                    [
                        'isFirst' => true,
                    ]);
                ?>
                <?= $this->render('_tires_organisation',
                    [
                        'isFirst' => false,
                    ]);
                ?>
                <?= $this->render('_tires_organisation',
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
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'type_car_sell_tires')
                ->checkboxList(\frontend\models\forms\TiresForm::$listCarType)
                ->label('Для какого вида ТС у Вас имеются шины и диски:') ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-5 col-md-offset-1">
            <div class="row">
                <div class="col-md-11">
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </div>
            </div>
        </div>
    </div>
<?php ActiveForm::end() ?>