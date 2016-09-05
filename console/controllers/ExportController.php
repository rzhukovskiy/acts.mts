<?php
namespace console\controllers;

use common\models\Act;
use common\models\Company;
use common\models\Service;
use Yii;
use common\models\User;
use yii\console\Controller;

class ExportController extends Controller
{
    /**
     * @var \yii\db\Connection $old_db
     */
    public $old_db;
    /**
     * @var \yii\db\Connection $new_db
     */
    public $new_db;

    public function init()
    {
        $this->old_db = Yii::$app->db_old;
        $this->new_db = Yii::$app->db;

        parent::init();
    }

    public function actionIndex()
    {
        $this->stdout("\n");
        $this->stdout("Export controller \n");
        $this->stdout("\nActions: \n");
        $this->stdout('   export/all' . " — import all tables.\n");
        $this->stdout("\n");
    }

    public function actionAll()
    {
        $this->exportCompanies();
        $this->exportMarks();
        $this->exportTypes();
        $this->exportCars();
        $this->exportCards();

        $this->exportServices();
        $this->exportPrices();

        $this->exportActs();

        $this->exportUsers();
    }

    private function exportCompanies()
    {
        $listType = [
            'company' => Company::TYPE_OWNER,
            'carwash' => Company::TYPE_WASH,
            'service' => Company::TYPE_SERVICE,
            'tires' => Company::TYPE_TIRES,
            'disinfection' => Company::TYPE_DISINFECT,
            'universal' => Company::TYPE_UNIVERSAL,
        ];
        $listType = array_flip($listType);

        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company")->queryAll();
        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}company WHERE id = {$rowData['id']}")->queryOne();
            if (!empty($existed)) {
                continue;
            }

            $rowData['parent_id'] = $rowData['parent_id'] ? $rowData['parent_id'] : 'NULL';
            $type = $listType[$rowData['type']];
            $contract = 'NULL';
            $actHeader = 'NULL';

            if ($type == 'universal') {
                $serviceTypes = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service_type WHERE company_id = {$rowData['id']}")->queryAll();
                foreach ($serviceTypes as $serviceData) {
                    $companyServiceType = $listType[$serviceData['type']];
                    $insert = "(
                        NULL,
                        {$rowData['id']},
                        '$companyServiceType'
                    )";

                    $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}company_service VALUES $insert")->execute();
                }
            }

            $requisites = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}requisites WHERE company_id = {$rowData['id']}")->queryAll();
            foreach ($requisites as $requisiteData) {
                if ($requisiteData['type'] == $rowData['type']) {
                    $contract = $requisiteData['contract'];
                    $actHeader = $requisiteData['header'];
                } else {
                    $insert = "(
                        NULL,
                        {$rowData['id']},
                        '{$rowData['header']}',
                        '{$rowData['contract']}',
                        $type)";

                    $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}requisites VALUES $insert")->execute();
                }
            }

            $insert = "({$rowData['id']},
                    {$rowData['parent_id']},
                    '{$rowData['name']}',
                    '{$rowData['address']}',
                    '{$rowData['phone']}',
                    '{$rowData['director']}',
                    '$type',
                    '$contract',
                    '$actHeader',
                    {$rowData['is_split']},
                    {$rowData['is_infected']},
                    0,
                    {$rowData['is_main']},
                    {$rowData['is_sign']})";

            $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}company VALUES $insert")->execute();
        }

        $this->stdout("Companies done!\n");
    }

    private function exportMarks()
    {
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}mark")->queryAll();
        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}mark WHERE id = {$rowData['id']}")->queryOne();
            if (!empty($existed)) {
                continue;
            }

            $insert = "({$rowData['id']},
                '{$rowData['name']}')";

            $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}mark VALUES $insert")->execute();
        }

        $this->stdout("Marks done!\n");
    }

    private function exportTypes()
    {
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}type")->queryAll();
        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}type WHERE id = {$rowData['id']}")->queryOne();
            if (!empty($existed)) {
                continue;
            }

            $insert = "({$rowData['id']},
                '{$rowData['name']}')";

            $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}type VALUES $insert")->execute();
        }

        $this->stdout("Types done!\n");
    }

    private function exportCars()
    {
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}car")->queryAll();
        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}car WHERE id = {$rowData['id']}")->queryOne();
            if (!empty($existed)) {
                continue;
            }

            $rowData['type_id'] = $rowData['type_id'] ? $rowData['type_id'] : 1;
            $rowData['mark_id'] = $rowData['mark_id'] ? $rowData['mark_id'] : 1;
            $insert = "({$rowData['id']},
                {$rowData['company_id']},
                '{$rowData['number']}',
                {$rowData['mark_id']},
                {$rowData['type_id']},
                {$rowData['is_infected']})";

            $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}car VALUES $insert")->execute();
        }

        $this->stdout("Cars done!\n");
    }

    private function exportCards()
    {
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}card")->queryAll();
        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}card WHERE id = {$rowData['id']}")->queryOne();
            if (!empty($existed)) {
                continue;
            }

            $createData = date('Y-m-d H:i:s', $rowData['created_at']);
            $insert = "({$rowData['id']},
                {$rowData['company_id']},
                {$rowData['number']},
                1,
                $createData)";

            $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}card VALUES $insert")->execute();
        }

        $this->stdout("Cards done!\n");
    }

    private function exportServices()
    {
        $typeTires = Service::TYPE_TIRES;
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE type = $typeTires")->queryAll();

        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}tires_service WHERE description = '{$rowData['description']}'")->queryOne();
            if (!empty($existed)) {
                continue;
            }

            $insert = "(NULL,
                '{$rowData['description']}',
                {$rowData['is_fixed']},
                20)";

            $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}tires_service VALUES $insert")->execute();
        }

        $this->stdout("Services done!\n");
    }

    private function exportPrices()
    {
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service")->queryAll();

        foreach ($rows as $rowData) {
            $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE id = {$rowData['service_id']}")->queryOne();
            if ($serviceData['description'] == 'Дополнительная дезинфекция') {
                $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}price WHERE company_id = {$rowData['company_id']} AND type_id = {$rowData['type_id']}")->queryOne();
                if (!empty($existed)) {
                    $this->old_db->createCommand("UPDATE {$this->old_db->tablePrefix}price SET additional = {$rowData['price']} WHERE id = {$existed['id']}")->execute();
                } else {
                    $this->old_db->createCommand("INSERT INTO {$this->old_db->tablePrefix}price(type_id, company_id, additional) VALUES({$rowData['type_id']}, {$rowData['company_id']}, {$rowData['price']})")->execute();
                }
            }
            if ($serviceData['type'] == Service::TYPE_TIRES) {
                $serviceData = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}tires_service WHERE description = '{$serviceData['description']}'")->queryOne();
                if (!empty($serviceData)) {
                    $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}company_tires_service WHERE tires_service_id = {$serviceData['id']} AND company_id = {$rowData['company_id']} AND type_id = {$rowData['type_id']}")->queryOne();
                    if (!empty($existed)) {
                        continue;
                    }

                    $this->old_db->createCommand("INSERT INTO {$this->old_db->tablePrefix}company_tires_service VALUES(NULL, {$rowData['company_id']}, {$rowData['type_id']}, {$serviceData['id']}, {$rowData['price']})")->execute();
                }
            }
        }

        $this->stdout("Prices done!\n");
    }

    private function exportActs()
    {
        $listType = [
            'company' => Company::TYPE_OWNER,
            'carwash' => Company::TYPE_WASH,
            'service' => Company::TYPE_SERVICE,
            'tires' => Company::TYPE_TIRES,
            'disinfection' => Company::TYPE_DISINFECT,
            'universal' => Company::TYPE_UNIVERSAL,
        ];
        $listType = array_flip($listType);

        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}act WHERE id > 3510")->queryAll();
        foreach ($rows as $rowData) {
            $service = $listType[$rowData['service_type']];
            $isFixed = $rowData['status'] ==  Act::STATUS_FIXED ? 1 : 0;
            $isClosed = ($rowData['status'] ==  Act::STATUS_CLOSED || $rowData['status'] ==  Act::STATUS_FIXED) ? 1 : 0;
            $serviceDate = date('Y-m-d H:i:s', $rowData['served_at']);
            $createDate = date('Y-m-d H:i:s', $rowData['created_at']);

            if (in_array($rowData['service_type'], [Company::TYPE_TIRES, Company::TYPE_SERVICE])) {
                $partnerService = $rowData['service_type'] == Company::TYPE_SERVICE ? 3 : 4;

                $rowData['extra_number'] = $rowData['extra_number'] ? "'{$rowData['extra_number']}'" : 'NULL';
                $rowData['card_id'] = $rowData['card_id'] ? $rowData['card_id'] : 0;
                $insert = "(NULL,
                        {$rowData['partner_id']},
                        {$rowData['client_id']},
                        {$rowData['type_id']},
                        {$rowData['card_id']},
                        '{$rowData['number']}',
                        {$rowData['extra_number']},
                        {$rowData['mark_id']},
                        '$serviceDate',
                        $isClosed,
                        $partnerService,
                        $partnerService,
                        '{$rowData['check']}',
                        '{$rowData['id']}.jpg',
                        {$rowData['income']},
                        {$rowData['expense']},
                        {$rowData['profit']},
                        '$createDate',
                        $isFixed,
                        '$service',
                        NULL)";

                $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}act VALUES $insert")->execute();

                $actId = $this->old_db->getLastInsertID();
                $clientScopes = $this->new_db
                    ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}act_scope WHERE act_id = {$rowData['id']} AND company_id = {$rowData['client_id']}")
                    ->queryAll();
                foreach ($clientScopes as $scopeData) {
                    $partnerScope = $this->new_db
                        ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}act_scope WHERE act_id = {$rowData['id']} AND company_id = {$rowData['partner_id']} AND service_id = {$scopeData['service_id']}")
                        ->queryOne();

                    $insert = "(NULL,
                        $actId,
                        '{$scopeData['description']}',
                        {$partnerScope['price']},
                        {$scopeData['price']},
                        {$scopeData['amount']})";

                    $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}act_scope VALUES $insert")->execute();
                }
            } else {
                $clientScopes = $this->new_db
                    ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}act_scope WHERE act_id = {$rowData['id']} AND company_id = {$rowData['client_id']}")
                    ->queryAll();
                foreach ($clientScopes as $scopeData) {
                    $listService[] = $scopeData['description'];
                }
                $clientService = 'NULL';
                if (count($listService) > 2) {
                    $clientService = 8;
                } elseif (count($listService) == 2 && in_array('снаружи', $listService) && in_array('внутри', $listService)) {
                    $clientService = 2;
                } elseif (count($listService) == 2 && in_array('снаружи', $listService) && in_array('двигатель', $listService)) {
                    $clientService = 6;
                } elseif (count($listService) == 2 && in_array('внутри', $listService) && in_array('двигатель', $listService)) {
                    $clientService = 7;
                }elseif (count($listService) == 1 && in_array('снаружи', $listService)) {
                    $clientService = 0;
                } elseif (count($listService) == 1 && in_array('внутри', $listService)) {
                    $clientService = 1;
                } elseif (count($listService) == 1 && in_array('дезинфекция', $listService)) {
                    $clientService = 5;
                } elseif (count($listService) == 1 && in_array('Дезинфекция', $listService)) {
                    $clientService = 5;
                } elseif (count($listService) == 1 && in_array('Дополнительная дезинфекция', $listService)) {
                    $clientService = 9;
                } elseif (count($listService) == 1 && in_array('дополнительная дезинфекция', $listService)) {
                    $clientService = 9;
                }

                $partnerScopes = $this->new_db
                    ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}act_scope WHERE act_id = {$rowData['id']} AND company_id = {$rowData['partner_id']}")
                    ->queryAll();
                $listService = [];
                foreach ($partnerScopes as $scopeData) {
                    $listService[] = $scopeData['description'];
                }
                $partnerService = 'NULL';
                if (count($listService) > 2) {
                    $partnerService = 8;
                } elseif (count($listService) == 2 && in_array('снаружи', $listService) && in_array('внутри', $listService)) {
                    $partnerService = 2;
                } elseif (count($listService) == 2 && in_array('снаружи', $listService) && in_array('двигатель', $listService)) {
                    $partnerService = 6;
                } elseif (count($listService) == 2 && in_array('внутри', $listService) && in_array('двигатель', $listService)) {
                    $partnerService = 7;
                }elseif (count($listService) == 1 && in_array('снаружи', $listService)) {
                    $partnerService = 0;
                } elseif (count($listService) == 1 && in_array('внутри', $listService)) {
                    $partnerService = 1;
                } elseif (count($listService) == 1 && in_array('дезинфекция', $listService)) {
                    $partnerService = 5;
                } elseif (count($listService) == 1 && in_array('Дезинфекция', $listService)) {
                    $partnerService = 5;
                } elseif (count($listService) == 1 && in_array('Дополнительная дезинфекция', $listService)) {
                    $partnerService = 9;
                } elseif (count($listService) == 1 && in_array('дополнительная дезинфекция', $listService)) {
                    $partnerService = 9;
                }

                if ($clientService == 'NULL' || $partnerService == 'NULL') {
                    continue;
                }

                $rowData['extra_number'] = $rowData['extra_number'] ? "'{$rowData['extra_number']}'" : 'NULL';
                $rowData['card_id'] = $rowData['card_id'] ? $rowData['card_id'] : 0;
                $insert = "(NULL,
                        {$rowData['partner_id']},
                        {$rowData['client_id']},
                        {$rowData['type_id']},
                        {$rowData['card_id']},
                        '{$rowData['number']}',
                        {$rowData['extra_number']},
                        {$rowData['mark_id']},
                        '$serviceDate',
                        $isClosed,
                        $partnerService,
                        $clientService,
                        '{$rowData['check']}',
                        '{$rowData['id']}.jpg',
                        {$rowData['income']},
                        {$rowData['expense']},
                        {$rowData['profit']},
                        '$createDate',
                        $isFixed,
                        '$service',
                        NULL)";

                $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}act VALUES $insert")->execute();
            }
        }

        $this->stdout("Acts done!\n");
    }

    private function exportUsers()
    {
        $rows = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}user")->queryAll();
        foreach ($rows as $rowData) {
            $existed = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}user WHERE email = '{$rowData['username']}'")->queryAll();
            if (!empty($existed)) {
                continue;
            }

            $role = $rowData['role'] == User::ROLE_CLIENT ? 'client' : ($rowData['role'] == User::ROLE_PARTNER ? 'partner' : 'admin');
            $company_id = $rowData['company_id'] ? $rowData['company_id'] : 'NULL';
            $insert = "(NULL,
                        NULL,
                        '{$rowData['username']}',
                        '{$rowData['password_hash']}',
                        '{$rowData['salt']}',
                        1,
                        NOW(),
                        '$role',
                        $company_id)";

            $this->old_db->createCommand("INSERT into {$this->old_db->tablePrefix}user VALUES $insert")->execute();
        }

        $this->stdout("Users done!\n");
    }
}