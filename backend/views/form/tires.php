<?php
use yii\bootstrap\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model \backend\models\forms\TiresForm
 * @var $company bool
 */

$this->title = 'Заявка для шиномонтажа';

$request = Yii::$app->request;

?>
    <div class="page-header">
        <h1 class="text-center hidden">ЗАЯВКА ДЛЯ ШИНОМОНТАЖА</h1>
        <img src="/images/tires-header-logo.png" alt="заявка для шиномонтажа">
    </div>
<?php
$form = ActiveForm::begin([
    'id'      => 'act-form',
    'options' => [
        'class' => 'front-form'
    ]
]) ?>
    <div class="row">
        <div class="col-md-offset-1 col-md-5">
            <?= $form->field($model, 'name')->textInput([]) ?>
            <?= $form->field($model, 'index')->textInput([]) ?>
            <?= $form->field($model, 'city')->textInput([]) ?>
            <?= $form->field($model, 'street')->textInput([]) ?>
            <?= $form->field($model, 'building')->textInput([]) ?>
            <?= $form->field($model, 'phone')->textInput([]) ?>
            <?= $this->render('_work_hours',
                [
                    'model' => $model,
                    'form'  => $form,
                ]); ?>
        </div>
        <div class="col-md-5">
            <p class=""><strong>Прочтите внимательно инструкцию!</strong></p>

            <p class="">Для того, чтобы мы могли начать сотрудничество в кратчайшие сроки, просим предоставить
                максимально полную и точную информацию:</p>

            <p class="">Укажите название Вашей организации.</p>

            <p class="">Адрес &ndash; укажите точный адрес нахождения вашего шиномонтажа и номер телефона
                (администратора), по которому производится запись ТС.</p>

            <p class="">ФИО &ndash; укажите данные руководителя и сотрудника, отвечающего за договорную работу.</p>

            <p class="">E-mail &ndash; укажите адрес электронной почты для постоянной связи.</p>

            <p class="">Телефон &ndash; укажите контактные номера телефонов.</p>

            <p class="">Укажите услуги, которые вы оказываете (нужно поставить галочку в белом квадратике).</p>

            <p class="">Укажите для какого вида ТС вы производите шиномонтаж (нужно поставить галочку в белом
                квадратике)</p>

            <p class="">Укажите для какого вида ТС у вас имеются шины и диски в продаже.</p>

            <p class="">Укажите названия и телефоны 2,3-х организаций, транспорт которых обслуживается у вас (для
                получения рекомендаций)</p>

            <p class="">
                Скачайте наш типовой договор
                <a href="/files/tires.doc" target="_blank">
                    <button type="button" class="btn btn-primary">Скачать договор</button>
                </a>
                Мы работаем по данному договору на всех станциях ТО, на которых получаем услуги автосервиса. При
                возникновении вопросов - можете составить протокол разногласий или обсудить интересующие вас пункты с
                нашим специалистом
            </p>

            <p class="">Отправьте заполненную анкету, и мы свяжемся с вами в ближайшее время</p>
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
                ->checkboxList(\backend\models\forms\TiresForm::$listService)
                ->label('Услуги, которые вы оказываете:') ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'type_car_change_tires')
                ->checkboxList(\backend\models\forms\TiresForm::$listCarType)
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
                ->checkboxList(\backend\models\forms\TiresForm::$listCarType)
                ->label('Для какого вида ТС у Вас имеются шины и диски:') ?>
        </div>
    </div>
    <br>
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