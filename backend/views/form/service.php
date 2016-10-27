<?php
use yii\bootstrap\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $model \backend\models\forms\ServiceForm
 * @var $company bool
 */

$this->title = 'Заявка для сервиса';

$request = Yii::$app->request;

?>
    <div class="page-header">
        <h1 class="text-center hidden">ЗАЯВКА ДЛЯ СЕРВИСА</h1>
        <img src="/images/service-header-logo.png" alt="заявка для сервиса">
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

            <p class="">Адрес &ndash; укажите точный адрес нахождения вашего автосервиса и номер телефона
                (мастера-приемщика), по которому производится запись ТС</p>

            <p class="">ФИО &ndash; укажите данные руководителя и сотрудника, отвечающего за договорную работу</p>

            <p class="">E-mail &ndash; укажите адрес электронной почты для постоянной связи</p>

            <p class="">Телефон &ndash; укажите контактные номера телефонов</p>

            <p class="">Укажите нормо-часы на основные виды работ</p>

            <p class="">Укажите марки грузовых автомобилей, которые вы обслуживаете. Если вы являетесь официальными
                дилерами каких-либо марок ТС, просим также указать их названия через запятую</p>

            <p class="">Укажите названия и телефоны 2,3-х организаций, транспорт которых обслуживается у вас (для
                получения рекомендаций)</p>

            <p class="">
                Скачайте наш типовой договор
                <a href="/files/service.doc" target="_blank">
                    <button type="button" class="btn btn-primary">Скачать договор</button>
                </a>
                Мы работаем по данному договору на всех станциях ТО, на которых получаем услуги автосервиса. При
                возникновении вопросов - можете составить протокол разногласий или обсудить интересующие вас пункты с
                нашим специалистом
            </p>
        </div>
    </div>
<?= $this->render('_director',
    [
        'model' => $model,
        'form'  => $form,
    ]); ?>
    <div class="row">
        <div class="col-md-offset-1 col-md-10">
            <div class="form-group">
                <label>Укажите марки ТС, которые вы обслуживаете как официальные дилеры и/или как неофициальные
                    дилеры</label>
            </div>
        </div>
        <div class="col-md-offset-1 col-md-5">
            <?= $form->field($model, 'official_dealer')->textInput([]) ?>
        </div>
        <div class="col-md-5">
            <?= $form->field($model, 'nonofficial_dealer')->textInput([]) ?>
        </div>
    </div>
    <div class="row">

        <div class="col-md-offset-1 col-md-5">
            <div class="items">
                <div class="form-group row">
                    <label>Нормо-час на основные виды работ:</label>
                </div>

                <?= $this->render('_service_work_type',
                    [
                        'isFirst' => true,
                    ]);
                ?>
                <?= $this->render('_service_work_type',
                    [
                        'isFirst' => false,
                    ]);
                ?>
                <?= $this->render('_service_work_type',
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
            <div class="items">
                <div class="form-group row">
                    <label>Организации, транспорт которых обслуживается у вас:</label>
                </div>

                <?= $this->render('_service_organisation',
                    [
                        'isFirst' => true,
                    ]);
                ?>
                <?= $this->render('_service_organisation',
                    [
                        'isFirst' => false,
                    ]);
                ?>
                <?= $this->render('_service_organisation',
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