<?php
    namespace console\controllers;

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
        public $new_prefix = "";
        public $old_prefix = "";

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
            $this->stdout('   import/base-data new_prefix old_prefix' . " — imports data from previous version.\n");
            $this->stdout("\n");
        }

        public function actionBaseData($new_prefix = '', $old_prefix = '')
        {
            $this->new_prefix = $new_prefix;
            $this->old_prefix = $old_prefix;
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

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_prefix}company")->queryAll();
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

                $this->new_db->createCommand("INSERT into {$this->new_prefix}company VALUES $insert")->execute();

                if ($type != Company::TYPE_OWNER && !empty($rowData['contract'])) {
                    $insert = "(
                        NULL,
                        {$rowData['id']},
                        $type,
                        '{$rowData['contract']}',
                        '{$rowData['header']}'
                    )";

                    $this->new_db->createCommand("INSERT into {$this->new_prefix}requisites VALUES $insert")->execute();
                }
            }

            $this->stdout("Companies done!\n");
        }

        private function importMarks()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_prefix}mark")->queryAll();
            foreach ($rows as $rowData) {
                $insert = "({$rowData['id']},
                '{$rowData['name']}')";

                $this->new_db->createCommand("INSERT into {$this->new_prefix}mark VALUES $insert")->execute();
            }

            $this->stdout("Marks done!\n");
        }

        private function importTypes()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_prefix}type")->queryAll();
            foreach ($rows as $rowData) {
                $insert = "({$rowData['id']},
                '{$rowData['name']}')";

                $this->new_db->createCommand("INSERT into {$this->new_prefix}type VALUES $insert")->execute();
            }

            $this->stdout("Types done!\n");
        }

        private function importCars()
        {
            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_prefix}car")->queryAll();
            foreach ($rows as $rowData) {
                $rowData['type_id'] = $rowData['type_id'] ? $rowData['type_id'] : 1;
                $rowData['mark_id'] = $rowData['mark_id'] ? $rowData['mark_id'] : 1;
                $insert = "({$rowData['id']},
                {$rowData['company_id']},
                '{$rowData['number']}',
                {$rowData['mark_id']},
                {$rowData['type_id']},
                {$rowData['is_infected']})";

                $this->new_db->createCommand("INSERT into {$this->new_prefix}car VALUES $insert")->execute();
            }

            $this->stdout("Cars done!\n");
        }

        private function importCards()
        {
            $now = time();

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_prefix}card")->queryAll();
            foreach ($rows as $rowData) {
                $insert = "({$rowData['id']},
                {$rowData['company_id']},
                {$rowData['number']},
                10,
                $now,
                $now)";

                $this->new_db->createCommand("INSERT into {$this->new_prefix}card VALUES $insert")->execute();
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

            $rows = $this->old_db->createCommand("SELECT * FROM {$this->old_prefix}requisites")->queryAll();
            foreach ($rows as $rowData) {
                $type = $listType[$rowData['service_type']];
                $insert = "(
                        NULL,
                        {$rowData['id']},
                        $type,
                        '{$rowData['contract']}',
                        '{$rowData['header']}'
                    )";

                $this->new_db->createCommand("INSERT into {$this->new_prefix}requisites VALUES $insert")->execute();
            }

            $this->stdout("Requisites done!\n");
        }
    }