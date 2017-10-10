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
        <td class="list-label-md"><?= $model->getAttributeLabel('service_type') ?></td>
        <td>
            <?php

            $ServicesList = Company::$listType;

            $arrServices = explode(', ', $model->service_type);
            $serviceText = '';

            if (count($arrServices) > 1) {

                for ($i = 0; $i < count($arrServices); $i++) {
                    if(isset($ServicesList[$arrServices[$i]]['ru'])) {
                        $serviceText .= $ServicesList[$arrServices[$i]]['ru'] . '<br />';
                    }
                }

            } else {

                try {
                    if(isset($ServicesList[$model->service_type]['ru'])) {
                        $serviceText = $ServicesList[$model->service_type]['ru'];
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
                'data' => ['2' => 'Мойка', '3' => 'Сервис', '4' => 'Шиномонтаж', '5' => 'Дезинфекция'],
                'options' => ['class' => 'form-control', 'multiple' => 'true'],
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
                'options' => ['class' => 'form-control', 'type' => 'number'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md">Цена без НДС</td>
        <td>
            <?= sprintf("%.2f", ($model->price_nds / 1.18)) ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('first_price') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'first_price',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'type' => 'number'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
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
                'options' => ['class' => 'form-control', 'type' => 'number'],
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
                'options' => ['class' => 'form-control', 'type' => 'number'],
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
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'percent_down',
                'displayValue' => $model->percent_down . "%",
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
        <td class="list-label-md"><?= $model->getAttributeLabel('percent_max') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'percent_max',
                'displayValue' => $model->percent_max . "%",
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
        <td class="list-label-md"><?= $model->getAttributeLabel('federal_law') ?></td>
        <td>
            <?

            $arrFZ = [1 => '44', 2 => '223', 3 => 'Ком'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'federal_law',
                'displayValue' => $arrFZ[$model->federal_law],
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
        <td class="list-label-md"><?= $model->getAttributeLabel('method_purchase') ?></td>
        <td>
            <?

            $arrMethods = [1 => 'Электронный аукцион (открытый)', 2 => 'Электронный аукцион (закрытый)', 3 => 'Запрос котировок (открытый)', 4 => 'Запрос предложений (открытый)', 5 => 'Открытый редукцион', 6 => 'Запрос цен', 7 => 'Открытый аукцион'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'method_purchase',
                'displayValue' => $arrMethods[$model->method_purchase],
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
                'options' => ['class' => 'form-control', 'type' => 'number'],
                'formOptions' => [
                    'action' => ['/company/updatetender', 'id' => $model->id],
                ],
                'valueIfNull' => '<span class="text-danger">не задано</span>',
            ]); ?>
        </td>
    </tr>
    <tr>
        <td class="list-label-md"><?= $model->getAttributeLabel('participate_price') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'participate_price',
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => ['class' => 'form-control', 'type' => 'number'],
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
            <?

            $arrStatusRequest = [1 => 'Отправил на оплату', 2 => 'Оплатили', 3 => 'Списали (выиграли)', 4 => 'Вернули (проиграли)'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_request_security',
                'displayValue' => $arrStatusRequest[$model->status_request_security],
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
        <td class="list-label-md"><?= $model->getAttributeLabel('status_contract_security') ?></td>
        <td>
            <?

            $arrStatusContract = [1 => 'Отправил на оплату', 2 => 'Оплатили', 3 => 'Зачислено на счет заказчика', 4 => 'Оплатили БГ', 5 => 'Отправили БГ клиенту', 6 => 'Клиент получил БГ', 7 => 'Обеспечаение вернули (контракт закрыт)'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'status_contract_security',
                'displayValue' => $arrStatusContract[$model->status_contract_security],
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
        <td class="list-label-md"><?= $model->getAttributeLabel('key_type') ?></td>
        <td>
            <?

            $arrKeyType = [0 => 'Без ключа', 1 => 'Контакт', 2 => 'Роснефть', 3 => 'РЖД'];

            echo Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'inputType' => Editable::INPUT_DROPDOWN_LIST,
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'key_type',
                'displayValue' => $arrKeyType[$model->key_type],
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
        <td class="list-label-md"><?= $model->getAttributeLabel('date_request_start') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_request_start',
                'displayValue' => date('d.m.Y', $model->date_request_start),
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
                    'options'=>['value' => date('d.m.Y', $model->date_request_start)]
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
                'displayValue' => date('d.m.Y H:i', $model->date_request_end),
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => date('d.m.Y H:i', $model->date_request_end)],
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
                'displayValue' => date('d.m.Y H:i', $model->time_request_process),
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => date('d.m.Y H:i', $model->time_request_process)],
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
                'displayValue' => date('d.m.Y H:i', $model->time_bidding_start),
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => date('d.m.Y H:i', $model->time_bidding_start)],
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
                'displayValue' => date('d.m.Y H:i', $model->time_bidding_end),
                'inputType' => Editable::INPUT_DATETIME,
                'asPopover' => true,
                'placement' => PopoverX::ALIGN_LEFT,
                'size' => 'lg',
                'options' => [
                    'options' => ['value' => date('d.m.Y H:i', $model->time_bidding_end)],
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
        <td class="list-label-md"><?= $model->getAttributeLabel('date_contract') ?></td>
        <td>
            <?= Editable::widget([
                'model' => $model,
                'buttonsTemplate' => '{submit}',
                'submitButton' => [
                    'icon' => '<i class="glyphicon glyphicon-ok"></i>',
                ],
                'attribute' => 'date_contract',
                'displayValue' => date('d.m.Y', $model->date_contract),
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
                    'options'=>['value' => date('d.m.Y', $model->date_contract)]
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
                'displayValue' => date('d.m.Y', $model->term_contract),
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
                    'options'=>['value' => date('d.m.Y', $model->term_contract)]
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
        <td class="list-label-md">Осталось</td>
        <td>
            <?php

            $timeNow = time();

            $showTotal = '';

            if($model->term_contract > $timeNow) {

                $totalDate = $model->term_contract - $timeNow;

                $days = ((Int) ($totalDate / 86400));
                $totalDate -= (((Int) ($totalDate / 86400)) * 86400);

                if($days < 0) {
                    $days = 0;
                }

                $hours = (round($totalDate / 3600));
                $totalDate -= (round($totalDate / 3600) * 3600);

                if($hours < 0) {
                    $hours = 0;
                }

                $minutes = (round($totalDate / 60));

                if($minutes < 0) {
                    $minutes = 0;
                }

                $showTotal .= $days . ' д.';
                $showTotal .= ' ' . $hours . ' ч.';
                $showTotal .= ' ' . $minutes . ' м.';

            } else {
                $totalDate = $timeNow - $model->term_contract;

                $days = ((Int) ($totalDate / 86400));
                $totalDate -= (((Int) ($totalDate / 86400)) * 86400);

                if($days < 0) {
                    $days = 0;
                }

                $hours = (round($totalDate / 3600));
                $totalDate -= (round($totalDate / 3600) * 3600);

                if($hours < 0) {
                    $hours = 0;
                }

                $minutes = (round($totalDate / 60));

                if($minutes < 0) {
                    $minutes = 0;
                }

                $showTotal .= '- ' . $days . ' д.';
                $showTotal .= ' ' . $hours . ' ч.';
                $showTotal .= ' ' . $minutes . ' м.';
            }

            echo $showTotal;

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