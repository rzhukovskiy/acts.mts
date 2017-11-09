<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;
use \kartik\date\DatePicker;
use kartik\editable\Editable;
use kartik\popover\PopoverX;

/* @var $this yii\web\View
 * @var $model common\models\CompanyMember
 * @var $form yii\widgets\ActiveForm
 */
$form = ActiveForm::begin([
    'action' => $model->isNewRecord ? ['/company/newtender', 'id' => $id] : ['/company/updatetender', 'id' => $model->id],
    'options' => ['accept-charset' => 'UTF-8', 'class' => 'form-horizontal col-sm-10', 'style' => 'margin-top: 20px;'],
    'fieldConfig' => [
        'template' => '{label}<div class="col-sm-6">{input}{error}</div>',
        'labelOptions' => ['class' => 'col-sm-3 control-label'],
        'inputOptions' => ['class' => 'form-control input-sm'],
    ],
]); ?>

<?= $form->field($model, 'company_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'purchase_status')->dropDownList([1 => 'Рассматриваем', 2 => 'Отказались', 3 => 'Не успели', 4 => 'Подаёмся', 5 => 'Подались', 6 => 'Отказ заказчика', 7 => 'Победили', 8 => 'Заключен договор', 9 => 'Проиграли'], ['class' => 'form-control', 'prompt' => 'Выберите статус закупки']) ?>
<?= $form->field($model, 'comment_status_proc')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к статусу закупки']) ?>
<?= $form->field($model, 'user_id')->dropDownList(['2' => 'Алёна', '3' => 'Денис'], ['class' => 'form-control', 'multiple' => 'true', /*'prompt' => 'Выберите услуги'*/]) ?>
<?= $form->field($model, 'date_search')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Дата нахождения закупки', 'value' => date('d.m.Y')],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'date_request_start')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите начало подачи заявки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'date_request_end')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите окончание подачи заявки'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'time_request_process')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время рассмотрения заявок'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'time_bidding_start')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время начала торгов'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>

<?= $form->field($model, 'time_bidding_end')->widget(DateTimePicker::className(), [
    'type' => DateTimePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату и время подведения итогов'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy hh:i',
        'autoclose'=>true,
        'weekStart'=>1,
        'todayBtn'=>true,
    ]
]) ?>
<?= $form->field($model, 'customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите название заказчика']) ?>
<?= $form->field($model, 'comment_customer')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к полю "Заказчик"']) ?>
<?= $form->field($model, 'inn_customer')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите ИНН заказчика']) ?>
<?= $form->field($model, 'contacts_resp_customer')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите контакты ответственных лиц заказчика']) ?>
<?= $form->field($model, 'method_purchase')->dropDownList([1 => 'Электронный аукцион', 2 => 'Запрос котировок (электронный открытый)', 3 => 'Запрос предложений', 4 => 'Открытый редукцион в электронной форме', 5 => 'Запрос цен', 6 => 'Открытый аукцион', 7 => 'Открытый запрос предложений в электронной форме'], ['class' => 'form-control', 'prompt' => 'Выберите способ закупки']) ?>
<?= $form->field($model, 'city')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите название города, области поставки']) ?>
<?= $form->field($model, 'service_type')->dropDownList(['2' => 'Мойка', '3' => 'Сервис', '4' => 'Шиномонтаж', '5' => 'Дезинфекция', '7' => 'Стоянка', '8' => 'Эвакуация'], ['class' => 'form-control', 'multiple' => 'true', /*'prompt' => 'Выберите услуги'*/]) ?>
<?= $form->field($model, 'federal_law')->dropDownList([1 => '44', 2 => '223', 3 => 'Ком'], ['class' => 'form-control', 'prompt' => 'Выберите федеральный закон']) ?>
<?= $form->field($model, 'notice_eis')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите номер извещения в ЕИС']) ?>
<?= $form->field($model, 'number_purchase')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите номер закупки на площадке']) ?>
<?= $form->field($model, 'place')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите адрес сайта']) ?>
<?= $form->field($model, 'key_type')->dropDownList([0 => 'Без ключа', 1 => 'Контакт', 2 => 'Роснефть', 3 => 'РЖД', 4 => 'Сбербанк УТП', 5 => 'Сбербанк АСТ'], ['class' => 'form-control', 'prompt' => 'Выберите тип ключа']) ?>
<?= $form->field($model, 'price_nds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальную стоимость закупки']) ?>
<?= $form->field($model, 'maximum_purchase_price')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальную начальную стоимость закупки без НДС']) ?>
<?= $form->field($model, 'final_price')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите стоимость закупки по завершению закупки с НДС']) ?>
<?= $form->field($model, 'cost_purchase_completion')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите стоимость закупки по завершению закупки без НДС']) ?>
<?= $form->field($model, 'pre_income')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите предварительную прибыль от закупки']) ?>
<?= $form->field($model, 'percent_down')->dropDownList([0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100], ['class' => 'form-control', 'prompt' => 'Выберите процентное снижение по завершению закупки в процентах']) ?>
<?= $form->field($model, 'maximum_purchase_nds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите снижение от максимальной начальной стоимости закупки по завершению закупки в рублях с НДС']) ?>
<?= $form->field($model, 'maximum_purchase_notnds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите снижение от максимальной начальной стоимости закупки по завершению закупки в рублях без НДС']) ?>
<?= $form->field($model, 'percent_max')->dropDownList([0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100], ['class' => 'form-control', 'prompt' => 'Выберите максимальное согласованное расчетное снижение в процентах']) ?>
<?= $form->field($model, 'maximum_agreed_calcnds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальное согласованное расчетное снижение в рублях с НДС']) ?>
<?= $form->field($model, 'maximum_agreed_calcnotnds')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите максимальное согласованное расчетное снижение в рублях без НДС']) ?>
<?= $form->field($model, 'site_fee_participation')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите плату площадке за участи']) ?>
<?= $form->field($model, 'ensuring_application')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите обеспечение заявки']) ?>
<?= $form->field($model, 'status_request_security')->dropDownList([1 => 'Отправил на оплату', 2 => 'Оплатили', 3 => 'Списали (выиграли)', 4 => 'Вернули (проиграли)', 5 => 'Вернули (выиграли)', 6 => 'Без обеспечения'], ['class' => 'form-control', 'prompt' => 'Выберите статус обеспечения заявки']) ?>
<?= $form->field($model, 'contract_security')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите обеспечение контракта']) ?>
<?= $form->field($model, 'status_contract_security')->dropDownList([1 => 'Отправил на оплату', 2 => 'Оплатили', 3 => 'Зачислено на счет заказчика', 4 => 'Оплатили БГ', 5 => 'Отправили БГ клиенту', 6 => 'Клиент получил БГ', 7 => 'Обеспечаение вернули (контракт закрыт)', 8 => 'Без обеспечения'], ['class' => 'form-control', 'prompt' => 'Выберите статус обеспечения контракта']) ?>
<?= $form->field($model, 'competitor')->input('text', ['class' => 'form-control', 'placeholder' => 'Введите потенциального конкурента']) ?>
<?= $form->field($model, 'inn_competitors')->textarea(['maxlength' => true, 'rows' => '4', 'placeholder' => 'Введите ИНН конкурентов']) ?>
<?= $form->field($model, 'date_contract')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату заключения договора'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'term_contract')->widget(DatePicker::className(), [
    'type' => DatePicker::TYPE_INPUT,
    'options' => ['placeholder' => 'Выберите дату окончания заключенного договора'],
    'pluginOptions' => [
        'format' => 'dd.mm.yyyy',
        'autoclose'=>true,
        'weekStart'=>1,
    ]
]) ?>
<?= $form->field($model, 'comment_date_contract')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий к сроку договора']) ?>
<?= $form->field($model, 'comment')->textarea(['maxlength' => true, 'rows' => '7', 'placeholder' => 'Введите комментарий']) ?>


    <div class="form-group">
        <div class="col-sm-offset-3 col-sm-6" style="padding-bottom: 10px;">
            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary btn-sm']) ?>
        </div>
    </div>

<?php ActiveForm::end(); ?>