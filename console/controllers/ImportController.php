<?php
    namespace console\controllers;

    use common\models\Act;
    use common\models\Company;
    use Yii;
    use common\models\User;
    use yii\console\Controller;

    class ImportController extends Controller
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
            $this->stdout("Import controller \n");
            $this->stdout("\nActions: \n");
            $this->stdout('   import/base-data' . " — imports base data from previous version.\n");
            $this->stdout('   import/price-data' . " — imports data about services and prices from previous version.\n");
            $this->stdout('   import/act-data' . " — imports acts from previous version.\n");
            $this->stdout("\n");
        }

        public function actionBaseData()
        {
            $this->importCompanies();
            $this->importMarks();
            $this->importTypes();
            $this->importCars();
            $this->importCards();
            $this->importRequisites();
        }

        private function importCompanies()
        {
            $listType = [
                'company' => Company::TYPE_OWNER,
                'carwash' => Company::TYPE_WASH,
                'service' => Company::TYPE_SERVICE,
                'tires' => Company::TYPE_TIRES,
                'disinfection' => Company::TYPE_DISINFECT,
                'universal' => Company::TYPE_UNIVERSAL,
            ];

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}company")->queryAll();
            foreach ($rows as $rowData) {
                $now = time();
                $rowData['parent_id'] = $rowData['parent_id'] ? $rowData['parent_id'] : 'NULL';
                $type = $listType[$rowData['type']];
                $insert = "({$rowData['id']},
                {$rowData['parent_id']},
                '{$rowData['name']}',
                '{$rowData['address']}',
                '{$rowData['phone']}',
                '{$rowData['contact']}',
                $type,
                {$rowData['is_split']},
                {$rowData['is_infected']},
                {$rowData['is_main']},
                {$rowData['is_sign']},
                10,
                $now,
                $now)";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company VALUES $insert")->execute();

                if ($type != Company::TYPE_OWNER && !empty($rowData['contract'])) {
                    $insert = "(
                        NULL,
                        {$rowData['id']},
                        $type,
                        '{$rowData['contract']}',
                        '{$rowData['header']}'
                    )";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}requisites VALUES $insert")->execute();
                }
            }

            $this->stdout("Companies done!\n");
        }

        private function importMarks()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}mark")->queryAll();
            foreach ($rows as $rowData) {
                $insert = "({$rowData['id']},
                '{$rowData['name']}')";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}mark VALUES $insert")->execute();
            }

            $this->stdout("Marks done!\n");
        }

        private function importTypes()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}type")->queryAll();
            foreach ($rows as $rowData) {
                $insert = "({$rowData['id']},
                '{$rowData['name']}')";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}type VALUES $insert")->execute();
            }

            $this->stdout("Types done!\n");
        }

        private function importCars()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}car")->queryAll();
            foreach ($rows as $rowData) {
                $rowData['type_id'] = $rowData['type_id'] ? $rowData['type_id'] : 1;
                $rowData['mark_id'] = $rowData['mark_id'] ? $rowData['mark_id'] : 1;
                $insert = "({$rowData['id']},
                {$rowData['company_id']},
                '{$rowData['number']}',
                {$rowData['mark_id']},
                {$rowData['type_id']},
                {$rowData['is_infected']})";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}car VALUES $insert")->execute();
            }

            $this->stdout("Cars done!\n");
        }

        private function importCards()
        {
            $now = time();

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}card")->queryAll();
            foreach ($rows as $rowData) {
                $insert = "({$rowData['id']},
                {$rowData['company_id']},
                {$rowData['number']},
                10,
                $now,
                $now)";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}card VALUES $insert")->execute();
            }

            $this->stdout("Cards done!\n");
        }

        private function importRequisites()
        {
            $listType = [
                'company' => Company::TYPE_OWNER,
                'carwash' => Company::TYPE_WASH,
                'service' => Company::TYPE_SERVICE,
                'tires' => Company::TYPE_TIRES,
                'disinfection' => Company::TYPE_DISINFECT,
                'universal' => Company::TYPE_UNIVERSAL,
            ];

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}requisites")->queryAll();
            foreach ($rows as $rowData) {
                $type = $listType[$rowData['service_type']];
                $insert = "(
                        NULL,
                        {$rowData['id']},
                        $type,
                        '{$rowData['contract']}',
                        '{$rowData['header']}'
                    )";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}requisites VALUES $insert")->execute();
            }

            $this->stdout("Requisites done!\n");
        }

        public function actionPriceData()
        {
            $this->importServices();
            $this->importPrices();
            $this->importTiresServices();
        }

        private function importServices()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}tires_service ORDER BY pos")->queryAll();
            $now = time();

            $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}service VALUES (NULL, 1, " . Company::TYPE_WASH . ", 'внутри', $now, $now),
                  (NULL, 1, " . Company::TYPE_WASH . ", 'снаружи', $now, $now),
                  (NULL, 1, " . Company::TYPE_WASH . ", 'двигатель', $now, $now),
                  (NULL, 1, " . Company::TYPE_DISINFECT . ", 'дезинфекция', $now, $now)
                ")->execute();

            foreach ($rows as $rowData) {
                $now = time();

                $type = Company::TYPE_TIRES;
                $insert = "(NULL,
                {$rowData['is_fixed']},
                $type,
                '{$rowData['description']}',
                $now,
                $now)";

                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}service VALUES $insert")->execute();
            }

            $this->stdout("Services done!\n");
        }

        private function importPrices()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}price")->queryAll();

            foreach ($rows as $rowData) {
                if (!empty($rowData['inside'])) {
                    $now = time();
                    $insert = "(NULL,
                        {$rowData['company_id']},
                        1,
                        {$rowData['type_id']},
                        {$rowData['inside']},
                        $now,
                        $now)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_service VALUES $insert")->execute();
                }

                if (!empty($rowData['outside'])) {
                    $now = time();
                    $insert = "(NULL,
                        {$rowData['company_id']},
                        2,
                        {$rowData['type_id']},
                        {$rowData['outside']},
                        $now,
                        $now)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_service VALUES $insert")->execute();
                }

                if (!empty($rowData['engine'])) {
                    $now = time();
                    $insert = "(NULL,
                        {$rowData['company_id']},
                        3,
                        {$rowData['type_id']},
                        {$rowData['outside']},
                        $now,
                        $now)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_service VALUES $insert")->execute();
                }

                if (!empty($rowData['disinfection'])) {
                    $now = time();
                    $insert = "(NULL,
                        {$rowData['company_id']},
                        4,
                        {$rowData['type_id']},
                        {$rowData['disinfection']},
                        $now,
                        $now)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_service VALUES $insert")->execute();
                }
            }

            $this->stdout("Prices done!\n");
        }

        private function importTiresServices()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}company_tires_service")->queryAll();
            foreach ($rows as $rowData) {
                $now = time();
                $oldServiceData = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}tires_service WHERE id = {$rowData['tires_service_id']}")->queryOne();
                $description = $oldServiceData['description'];
                $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE '$description'")->queryOne();
                if(!empty($serviceData)) {
                    $insert = "(NULL,
                        {$rowData['company_id']},
                        {$rowData['id']},
                        {$rowData['type_id']},
                        {$rowData['price']},
                        $now,
                        $now)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_service VALUES $insert")->execute();
                }
            }

            $this->stdout("Tires services done!\n");
        }

        public function actionActData()
        {
            $this->importActs();
        }

        private function importActs()
        {
            $listType = [
                'company' => Company::TYPE_OWNER,
                'carwash' => Company::TYPE_WASH,
                'service' => Company::TYPE_SERVICE,
                'tires' => Company::TYPE_TIRES,
                'disinfection' => Company::TYPE_DISINFECT,
                'universal' => Company::TYPE_UNIVERSAL,
            ];

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}act")->queryAll();
            foreach ($rows as $rowData) {
                $now = time();
                if (in_array($listType[$rowData['service']], [Company::TYPE_TIRES, Company::TYPE_SERVICE])) {
                    $type = $listType[$rowData['service']];
                    $status = $rowData['is_fixed'] ? Act::STATUS_FIXED : ($rowData['is_closed'] ? Act::STATUS_CLOSED : Act::STATUS_NEW);
                    $served_at = strtotime($rowData['service_date']);
                    $insert = "(NULL,
                        {$rowData['partner_id']},
                        {$rowData['client_id']},
                        {$rowData['type_id']},
                        {$rowData['card_id']},
                        {$rowData['mark_id']},
                        {$rowData['expense']},
                        {$rowData['income']},
                        {$rowData['profit']},
                        $type,
                        $status,
                        '{$rowData['number']}',
                        '{$rowData['extra_number']}',
                        '{$rowData['check']}',
                        $now,
                        $now,
                        $served_at)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act VALUES $insert")->execute();

                    $act_id = $this->new_db->lastInsertID;

                    $scopes = $this->old_db->createCommand("SELECT * FROM {$this->old_db->tablePrefix}act_scope WHERE act_id = {$rowData['id']}")->queryAll();
                    foreach ($scopes as $scopeData) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE '{$scopeData['description']}'")->queryOne();

                        if (empty($serviceData['id'])) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = $scopeData['expense'];
                            $serviceData['description'] = $scopeData['description'];
                        } else {
                            $companyServiceData = $this->new_db
                                ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['partner_id']} AND service_id = {$serviceData['id']}")
                                ->queryOne();

                            if (empty($companyServiceData)) {
                                $companyServiceData['id'] = 'NULL';
                                $companyServiceData['price'] = 0;
                            }
                        }

                        $insert = "(NULL,
                            $act_id,
                            {$rowData['partner_id']},
                            {$companyServiceData['id']},
                            1,
                            {$companyServiceData['price']},
                            '{$serviceData['description']}',
                            $now,
                            $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();

                        if (empty($serviceData['id'])) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = $scopeData['income'];
                            $serviceData['description'] = $scopeData['description'];
                        } else {
                            $companyServiceData = $this->new_db
                                ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['client_id']} AND service_id = {$serviceData['id']}")
                                ->queryOne();

                            if (empty($companyServiceData)) {
                                $companyServiceData['id'] = 'NULL';
                                $companyServiceData['price'] = 0;
                            }
                        }

                        $insert = "(NULL,
                            $act_id,
                            {$rowData['client_id']},
                            {$companyServiceData['id']},
                            1,
                            {$companyServiceData['price']},
                            '{$serviceData['description']}',
                            $now,
                            $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }
                } else {
                    $type = $listType[$rowData['service']];
                    $status = $rowData['is_fixed'] ? Act::STATUS_FIXED : ($rowData['is_closed'] ? Act::STATUS_CLOSED : Act::STATUS_NEW);
                    $served_at = strtotime($rowData['service_date']);
                    $insert = "(NULL,
                        {$rowData['partner_id']},
                        {$rowData['client_id']},
                        {$rowData['type_id']},
                        {$rowData['card_id']},
                        {$rowData['mark_id']},
                        {$rowData['expense']},
                        {$rowData['income']},
                        {$rowData['profit']},
                        $type,
                        $status,
                        '{$rowData['number']}',
                        '{$rowData['extra_number']}',
                        '{$rowData['check']}',
                        $now,
                        $now,
                        $served_at)";

                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act VALUES $insert")->execute();

                    $act_id = $this->new_db->lastInsertID;

                    if (in_array($rowData['partner_service'], [0,2,6,8])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'снаружи'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['partner_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['partner_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['partner_service'], [1,2,7,8])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'внутри'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['partner_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['partner_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['partner_service'], [6,7,8])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'двигатель'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['partner_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['partner_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['client_service'], [0,2,6,8])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'снаружи'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['client_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['client_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['client_service'], [1,2,7,8])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'внутри'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['client_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['client_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['client_service'], [6,7,8])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'двигатель'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['client_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['client_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['partner_service'], [5])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'дезинфекция'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['partner_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['partner_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }

                    if (in_array($rowData['client_service'], [5])) {
                        $serviceData = $this->new_db->createCommand("SELECT * FROM {$this->new_db->tablePrefix}service WHERE description LIKE 'дезинфекция'")->queryOne();
                        $companyServiceData = $this->new_db
                            ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company_service WHERE type_id = {$rowData['type_id']} AND company_id = {$rowData['client_id']} AND service_id = {$serviceData['id']}")
                            ->queryOne();

                        if (empty($companyServiceData)) {
                            $companyServiceData['id'] = 'NULL';
                            $companyServiceData['price'] = 0;
                        }

                        $insert = "(NULL,
                        $act_id,
                        {$rowData['client_id']},
                        {$companyServiceData['id']},
                        1,
                        {$companyServiceData['price']},
                        '{$serviceData['description']}',
                        $now,
                        $now)";

                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}act_scope VALUES $insert")->execute();
                    }
                }
            }

            $this->stdout("Acts done!\n");
        }
    }