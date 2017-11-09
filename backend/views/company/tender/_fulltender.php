<?php

/**
 * @var $this yii\web\View
 * @var $modelCompanyInfo common\models\CompanyInfo
 */
use kartik\editable\Editable;
use kartik\popover\PopoverX;
use yii\web\View;
use common\models\Company;

?>

<table class="table table-bordered list-data">
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('purchase_status') ?></td>
        <td>
            <?php

            $arrPurchstatus = [1 => 'Рассматриваем', 2 => 'Отказались', 3 => 'Не успели', 4 => 'Подаёмся', 5 => 'Подались', 6 => 'Отказ заказчика', 7 => 'Победили', 8 => 'Заключен договор', 9 => 'Проиграли'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'purchase_status',
                'displayValue' => $model->purchase_status ? $arrPurchstatus[$model->purchase_status] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $arrPurchstatus,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_status_proc') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_status_proc',
                'displayValue' => nl2br($model->comment_status_proc),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий к статусу закупки'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('user_id') ?></td>
        <td>
            <?php

            $usersList = ['2' => 'Алёна', '3' => 'Денис'];

            $arrUserTend = explode(', ', $model->user_id);
            $userText = '';

            if (count($arrUserTend) > 1) {

                for ($i = 0; $i < count($arrUserTend); $i++) {
                    if(isset($usersList[$arrUserTend[$i]])) {
                        $userText .= $usersList[$arrUserTend[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($usersList[$model->user_id])) {
                        $userText = $usersList[$model->user_id];
                    }
                } catch (\Exception $e) {
                    $userText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'user_id',
                'displayValue' => $userText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => ['2' => 'Алёна', '3' => 'Денис'],
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_search') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_search',
                'displayValue' => date('d.m.Y', $model->date_search),
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => date('d.m.Y', $model->date_search)]
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_start',
                'displayValue' => $model->date_request_start ? date('d.m.Y', $model->date_request_start) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => $model->date_request_start ? date('d.m.Y', $model->date_request_start) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_end') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_end',
                'displayValue' => $model->date_request_end ? date('d.m.Y H:i', $model->date_request_end) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->date_request_end ? date('d.m.Y H:i', $model->date_request_end) : ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_request_process') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_request_process',
                'displayValue' => $model->time_request_process ? date('d.m.Y H:i', $model->time_request_process) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_request_process ? date('d.m.Y H:i', $model->time_request_process) : ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_bidding_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_bidding_start',
                'displayValue' => $model->time_bidding_start ? date('d.m.Y H:i', $model->time_bidding_start) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_bidding_start ? date('d.m.Y H:i', $model->time_bidding_start) : ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('time_bidding_end') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'time_bidding_end',
                'displayValue' => $model->time_bidding_end ? date('d.m.Y H:i', $model->time_bidding_end) : '',
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => $model->time_bidding_end ? date('d.m.Y H:i', $model->time_bidding_end): ''],
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy hh:i',
                        'weekStart'=>1,
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'customer',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_customer',
                'displayValue' => nl2br($model->comment_customer),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий к полю "Заказчик"'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('inn_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'inn_customer',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('contacts_resp_customer') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'contacts_resp_customer',
                'displayValue' => nl2br($model->contacts_resp_customer),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите контакты ответственных лиц заказчика'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('method_purchase') ?></td>
        <td>
            <?php

            $arrMethods = [1 => 'Электронный аукцион (открытый)', 2 => 'Электронный аукцион (закрытый)', 3 => 'Запрос котировок (открытый)', 4 => 'Запрос предложений (открытый)', 5 => 'Открытый редукцион', 6 => 'Запрос цен', 7 => 'Открытый аукцион'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'method_purchase',
                'displayValue' => $model->method_purchase ? $arrMethods[$model->method_purchase] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $arrMethods,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('city') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'city',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('service_type') ?></td>
        <td>
            <?php

            $ServicesList = ['2' => 'Мойка', '3' => 'Сервис', '4' => 'Шиномонтаж', '5' => 'Дезинфекция', '7' => 'Стоянка', '8' => 'Эвакуация'];

            $arrServices = explode(', ', $model->service_type);
            $serviceText = '';

            if (count($arrServices) > 1) {

                for ($i = 0; $i < count($arrServices); $i++) {
                    if(isset($ServicesList[$arrServices[$i]])) {
                        $serviceText .= $ServicesList[$arrServices[$i]] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($ServicesList[$model->service_type])) {
                        $serviceText = $ServicesList[$model->service_type];
                    }
                } catch (\Exception $e) {
                    $serviceText = '-';
                }

            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'service_type',
                'displayValue' => $serviceText,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => ['2' => 'Мойка', '3' => 'Сервис', '4' => 'Шиномонтаж', '5' => 'Дезинфекция', '7' => 'Стоянка', '8' => 'Эвакуация'],
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('federal_law') ?></td>
        <td>
            <?php

            $arrFZ = [1 => '44', 2 => '223', 3 => 'Ком'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'federal_law',
                'displayValue' => $model->federal_law ? $arrFZ[$model->federal_law] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $arrFZ,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('notice_eis') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'notice_eis',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('number_purchase') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'number_purchase',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('place') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'place',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('key_type') ?></td>
        <td>
            <?php

            $arrKeyType = [0 => 'Без ключа', 1 => 'Контакт', 2 => 'Роснефть', 3 => 'РЖД', 4 => 'Сбербанк УТП', 5 => 'Сбербанк АСТ'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'key_type',
                'displayValue' => $model->key_type ? $arrKeyType[$model->key_type] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $arrKeyType,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('price_nds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'price_nds',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">Максимальная начальная стоимость закупки без НДС</td>
        <td>
            <?= sprintf("%.2f", ($model->price_nds / 1.18)) ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('final_price') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'final_price',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('cost_purchase_completion') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'cost_purchase_completion',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('pre_income') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'pre_income',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('percent_down') ?></td>
        <td>
            <?php
            // Вычисление значиния для вывода Процентное снижение по завершению закупки в процентах
            $resPerDown = '';

            if($model->percent_down === 0) {
                $resPerDown = 0 . '%';
            } else if($model->percent_down > 0) {
                $resPerDown = $model->percent_down . '%';
            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'percent_down',
                'displayValue' => $resPerDown,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100],
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_purchase_nds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_purchase_nds',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_purchase_notnds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_purchase_notnds',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('percent_max') ?></td>
        <td>
            <?php
            // Вычисление значиния для вывода Максимальное согласованное расчетное снижение в процентах
            $resPerMax = '';

            if($model->percent_max === 0) {
                $resPerMax = 0 . '%';
            } else if($model->percent_max > 0) {
                $resPerMax = $model->percent_max . '%';
            }

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'percent_max',
                'displayValue' => $resPerMax,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => [0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20, 21 => 21, 22 => 22, 23 => 23, 24 => 24, 25 => 25, 26 => 26, 27 => 27, 28 => 28, 29 => 29, 30 => 30, 31 => 31, 32 => 32, 33 => 33, 34 => 34, 35 => 35, 36 => 36, 37 => 37, 38 => 38, 39 => 39, 40 => 40, 41 => 41, 42 => 42, 43 => 43, 44 => 44, 45 => 45, 46 => 46, 47 => 47, 48 => 48, 49 => 49, 50 => 50, 51 => 51, 52 => 52, 53 => 53, 54 => 54, 55 => 55, 56 => 56, 57 => 57, 58 => 58, 59 => 59, 60 => 60, 61 => 61, 62 => 62, 63 => 63, 64 => 64, 65 => 65, 66 => 66, 67 => 67, 68 => 68, 69 => 69, 70 => 70, 71 => 71, 72 => 72, 73 => 73, 74 => 74, 75 => 75, 76 => 76, 77 => 77, 78 => 78, 79 => 79, 80 => 80, 81 => 81, 82 => 82, 83 => 83, 84 => 84, 85 => 85, 86 => 86, 87 => 87, 88 => 88, 89 => 89, 90 => 90, 91 => 91, 92 => 92, 93 => 93, 94 => 94, 95 => 95, 96 => 96, 97 => 97, 98 => 98, 99 => 99, 100 => 100],
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_agreed_calcnds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_agreed_calcnds',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('maximum_agreed_calcnotnds') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'maximum_agreed_calcnotnds',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('site_fee_participation') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'site_fee_participation',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('ensuring_application') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'ensuring_application',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('status_request_security') ?></td>
        <td>
            <?php

            $arrStatusRequest = [1 => 'Отправил на оплату', 2 => 'Оплатили', 3 => 'Списали (выиграли)', 4 => 'Вернули (проиграли)', 5 => 'Вернули (выиграли)', 6 => 'Без обеспечения'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_request_security',
                'displayValue' => $model->status_request_security ? $arrStatusRequest[$model->status_request_security] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $arrStatusRequest,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_status_request') ?></td>
        <td>
            <?= ($model->date_status_request) ? date('d.m.Y H:i', $model->date_status_request) : '-' ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('contract_security') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'contract_security',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('status_contract_security') ?></td>
        <td>
            <?php

            $arrStatusContract = [1 => 'Отправил на оплату', 2 => 'Оплатили', 3 => 'Зачислено на счет заказчика', 4 => 'Оплатили БГ', 5 => 'Отправили БГ клиенту', 6 => 'Клиент получил БГ', 7 => 'Обеспечаение вернули (контракт закрыт)', 8 => 'Без обеспечения'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_contract_security',
                'displayValue' => $model->status_contract_security ? $arrStatusContract[$model->status_contract_security] : '',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'data' => $arrStatusContract,
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id]
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_status_contract') ?></td>
        <td>
            <?= ($model->date_status_contract) ? date('d.m.Y H:i', $model->date_status_contract) : '-' ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('competitor') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'competitor',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('inn_competitors') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'inn_competitors',
                'displayValue' => nl2br($model->inn_competitors),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите ИНН конкурентов'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_contract',
                'displayValue' => ($model->date_contract) ? date('d.m.Y', $model->date_contract) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => ($model->date_contract) ? date('d.m.Y', $model->date_contract) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('term_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'term_contract',
                'displayValue' => ($model->term_contract) ? date('d.m.Y', $model->term_contract) : '',
                'inputType' => Editable::INPUT_DATE,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'class' => 'form-control',
                    'removeButton' => false,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'pickerPosition' => 'bottom-right',
                    ],
                    'options'=>['value' => ($model->term_contract) ? date('d.m.Y', $model->term_contract) : '']
                ],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]);
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment_date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment_date_contract',
                'displayValue' => nl2br($model->comment_date_contract),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>




    <tr>
        <td class="list-label-md">Осталось дней до окончания действия договора</td>
        <td>
            <?php

            if($model->date_contract and $model->term_contract) {

                $totalDate = '';

                if ($model->term_contract > $model->date_contract) {

                    $totalDate = $model->term_contract - $model->date_contract;
                    $days = ((Int)($totalDate / 86400));

                } else {
                    $totalDate = '-';
                }

                echo $days, ' дней';
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">
            <?= $model->getAttributeLabel('comment') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType'       => Editable::INPUT_TEXTAREA,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'comment',
                'displayValue' => nl2br($model->comment),
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'placeholder' => 'Введите комментарий'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
</table>