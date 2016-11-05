<?php
namespace console\controllers;

use common\models\Company;
use common\models\Mark;
use common\models\Type;
use Yii;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class ConnectController extends Controller
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
        $this->stdout("Connect controller \n");
        $this->stdout("\nActions: \n");
        $this->stdout('   merge' . " — merge data from both programs.\n");
        $this->stdout("\n");
    }

    public function actionMerge()
    {
        $listStatus = [
            0 => Company::STATUS_NEW,
            1 => Company::STATUS_ACTIVE,
            2 => Company::STATUS_REFUSE,
            3 => Company::STATUS_NEW,
        ];

        $rows = $this->old_db
            ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request")
            ->queryAll();

        foreach ($rows as $rowData) {
            $company_id = $this->new_db
                ->createCommand("SELECT * FROM {$this->new_db->tablePrefix}company WHERE old_id = {$rowData['id']}")
                ->queryAll();
            $company_id = ArrayHelper::getValue($company_id, ['id'], false);

            if ($oldRequestData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_company WHERE request_ptr_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $type = Company::TYPE_OWNER;
            } elseif ($oldRequestData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_wash WHERE request_ptr_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $type = Company::TYPE_WASH;
            } elseif ($oldRequestData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_tires WHERE request_ptr_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $type = Company::TYPE_TIRES;
            } elseif ($oldRequestData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_service WHERE request_ptr_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $type = Company::TYPE_SERVICE;
            } else {
                continue;
            }

            $employees = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_employee WHERE request_id = {$rowData['id']}")
                ->queryAll();
            $employees = ArrayHelper::index($employees, 'position');

            $status = Company::STATUS_NEW;
            $created_at = time();
            if ($processData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_process WHERE request_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $status = Company::STATUS_NEW;
                $created_at = strtotime($processData['updated']);
            }
            if ($archiveData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_done WHERE request_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $status = Company::STATUS_ACTIVE;
                $created_at = strtotime($archiveData['created']);
            }
            if ($refuseData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_refused WHERE request_id = {$rowData['id']}")
                ->queryOne()
            ) {
                $status = Company::STATUS_REFUSE;
                $created_at = strtotime($refuseData['created']);
            }
            $status = isset($listStatus[$rowData['state']]) ? $listStatus[$rowData['state']] : $status;

            $name = ArrayHelper::getValue($rowData, 'name');
            if(!$name) {
                $name = 'Без названия ' . $rowData['id'];
            }
            if (!$company_id) {
                $companyData = [
                    'NULL',
                    'NULL',
                    '"' . addslashes($name) . '"',
                    '"' . addslashes(ArrayHelper::getValue($rowData, 'address_city')) . '"',
                    '"' . addslashes(ArrayHelper::getValue($employees['Директор'], 'name')) . '"',
                    $type,
                    0,
                    1,
                    0,
                    0,
                    $status,
                    $created_at,
                    $created_at,
                    0,
                    $rowData['id'],
                ];

                $insert = "(" . implode(',', $companyData) . ")";
                try {
                    $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company VALUES $insert")->execute();
                } catch (Exception $e) {
                    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                        $companyData[2] = '"' . addslashes($name) . '_' . $rowData['id'] . '"';
                        $insert = "(" . implode(',', $companyData) . ")";
                        $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company VALUES $insert")->execute();
                    }
                    print_r($e->getMessage());
                    die;
                    continue;
                }
                $company_id = $this->new_db->lastInsertID;
            }

            /////////////////////////////
            $employeeData = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_process_employee WHERE request_id = {$rowData['id']}")
                ->queryOne();

            $communication_at = ArrayHelper::getValue($rowData, 'next_communication_date', false);
            if ($communication_at) {
                $communication_at = strtotime($communication_at);
            } else {
                $communication_at = 'NULL';
            }
            $requestData = [
                'NULL',
                $company_id,
                '"' . addslashes(ArrayHelper::getValue($rowData, 'status', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'mail_number', '')) . '"',
                $communication_at,
                time(),
                time(),
                ArrayHelper::getValue($employeeData, 'employee_id', 'NULL')
            ];
            $insert = "(" . implode(',', $requestData) . ")";
            $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_offer VALUES $insert")->execute();

            /////////////////////////////
            $start_at = ArrayHelper::getValue($rowData, 'time_from');
            list($hrs, $mnts) = explode(':', $start_at);
            $start_at = $start_at ? $hrs * 3600 + $mnts * 60 : 'NULL';
            $end_at = ArrayHelper::getValue($rowData, 'time_to');
            list($hrs, $mnts) = explode(':', $end_at);
            $end_at = $end_at ? $hrs * 3600 + $mnts * 60 : 'NULL';
            $requestData = [
                'NULL',
                $company_id,
                '"' . addslashes(ArrayHelper::getValue($rowData, 'phone', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'address_mail', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'email', '')) . '"',
                $start_at,
                $end_at,
                '"' . addslashes(ArrayHelper::getValue($rowData, 'address_index', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'address_city', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'address_street', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'address_house', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'payment_day', '')) . '"',
                '"' . addslashes(ArrayHelper::getValue($rowData, 'agreement_number', '')) . '"',
                '"' . (ArrayHelper::getValue($rowData, 'agreement_day') ? strtotime(ArrayHelper::getValue($rowData, 'agreement_day')) : 'NULL') . '"',
            ];
            $insert = "(" . implode(',', $requestData) . ")";
            $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_info VALUES $insert")->execute();

            /////////////////////////////
            foreach ($employees as $position => $employeeData) {
                $requestData = [
                    'NULL',
                    $company_id,
                    '"' . $position . '"',
                    '"' . addslashes(ArrayHelper::getValue($employeeData, 'phone', '')) . '"',
                    '"' . addslashes(ArrayHelper::getValue($employeeData, 'email', '')) . '"',
                    '"' . addslashes(ArrayHelper::getValue($employeeData, 'name', '')) . '"',
                ];
                $insert = "(" . implode(',', $requestData) . ")";
                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_member VALUES $insert")->execute();
            }

            $drivers = $this->old_db
                ->createCommand("SELECT * FROM {$this->old_db->tablePrefix}request_company_driver WHERE request_ptr_id = {$rowData['id']}")
                ->queryAll();
            foreach ($drivers as $driverData) {
                $mark_id = Mark::findOne(['name' => $rowData['model']]);
                $mark_id = $mark_id ? $mark_id->id : 'NULL';
                $type_id = Type::findOne(['name' => $rowData['type']]);
                $type_id = $type_id ? $type_id->id : 'NULL';
                $requestData = [
                    'NULL',
                    $company_id,
                    '"' . addslashes(ArrayHelper::getValue($driverData, 'fio', '')) . '"',
                    '"' . addslashes(ArrayHelper::getValue($driverData, 'phone', '')) . '"',
                    $mark_id,
                    $type_id,
                ];
                $insert = "(" . implode(',', $requestData) . ")";
                $this->new_db->createCommand("INSERT into {$this->new_db->tablePrefix}company_driver VALUES $insert")->execute();
            }
        }

        $this->stdout("Connection is done!\n");
    }
}