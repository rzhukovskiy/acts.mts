<?php
namespace common\components;

use common\models\Act;
use common\models\ActScope;
use common\models\Company;
use common\models\MonthlyAct;
use common\models\Car;
use common\models\Requisites;
use common\models\search\ActSearch;
use common\models\search\ServiceSearch;
use common\models\Service;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Worksheet;
use PHPExcel_Writer_IWriter;
use yii;
use ZipArchive;
use common\models\ActData;

class ActExporter
{
    private $company= false;
    private $serviceType = Company::TYPE_WASH;
    private $time = null;
    public $objPHPExcel = null;

    // Записываем какие мфп есть в выгрузке
    private $arrMFPids = [];

    // Записываем какие мфп есть в выгрузке доп дизенфекция
    private $arrMFPidsDp = [];
    
    // сделали ли выгрузку для мфп?
    private $checkMFP = false;

    // сделали ли выгрузку для мфп доп дизенфекция?
    private $checkMFPdp = false;

    // сделали ли выгрузку для мфп анализ?
    private $checkMFPStat = false;

    // Проверка на статус акта (без акта)
    private $noActArr = [];

    // Массив содержащий номера актов и счетов
    private $arrActNumbers = [];
    private $checkDoQueryNumber = false;

    /**
     * @param ActSearch $searchModel
     * @param bool $company
     */
    public function exportCSV($searchModel, $company)
    {
        $this->time = $this->time = \DateTime::createFromFormat('m-Y-d H:i:s', $searchModel->period . '-01 00:00:00')->getTimestamp();
        $this->company = $company;
        $this->serviceType = $searchModel->service_type;

        $zip = new ZipArchive();
        $type = Service::$listType[$this->serviceType]['en'];
        $filename = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time) . "/all.zip";

        if ($zip->open($filename, ZipArchive::OVERWRITE) !== TRUE) {
            $zip = null;
        }

        // Получаем из базы тех у кого без акта
        $dataformat = explode('-', $searchModel->period);
        if($dataformat[0] > 9) {
            $dataformat = $dataformat[1] . '-' . $dataformat[0] . '-00';
        } else {
            $dataformat = $dataformat[1] . '-0' . $dataformat[0] . '-00';
        }

        $queryMountActs = MonthlyAct::find()->where(['type_id' => $this->serviceType])->andWhere(['act_status' => 5])->andWhere(['act_date' => $dataformat])->all();

        for($za = 0; $za < count($queryMountActs); $za++) {
            $index = $queryMountActs[$za]['client_id'];
            $this->noActArr[$index] = 5;
            $index = 0;
        }

        if ($this->company) {
            $listAct = $searchModel->searchClient()->getModels();

            foreach($listAct as $actClient) {

                if (isset($this->noActArr[$actClient->client->id])) {
                } else {

                    $this->noActArr[$actClient->client->id] = 1;

                    if (($actClient->client->id == 154) || ($actClient->client->id == 849) || ($actClient->client->id == 850) || ($actClient->client->id == 851) || ($actClient->client->id == 852) || ($actClient->client->id == 900)) {

                        $searchmfp = $searchModel;

                        switch ($this->serviceType) {
                            case Company::TYPE_SERVICE:
                                $dataList = $searchModel->search([])->getModels();

                                foreach ($dataList as $data) {

                                    $this->arrMFPids[$actClient->client->id][0] = $actClient->client;
                                    $searchmfp->client_id = $actClient->client_id;

                                    $this->arrMFPids[$actClient->client->id][1] = array($data);

                                }
                                break;
                            case Company::TYPE_DISINFECT:

                                $searchmfp->client_id = $actClient->client_id;

                                /*$listService = ServiceSearch::getServiceList(Company::TYPE_DISINFECT);
                                foreach ($listService as $serviceId => $serviceDescription) {

                                    $dataProvider = $searchmfp->search([]);
                                    $dataProvider->query->andWhere(['clientScopes.service_id' => $serviceId])
                                        ->groupBy('clientScopes.act_id')
                                        ->joinWith('clientScopes clientScopes');

                                    $dataList = $dataProvider->getModels();
                                    $this->arrMFPids[$actClient->client->id][1] = $dataList;
                                }*/

                                $dataProvider = $searchmfp->search([]);
                                $dataProvider->query->andWhere(['clientScopes.service_id' => 4])
                                    ->groupBy('clientScopes.act_id')
                                    ->joinWith('clientScopes clientScopes');

                                $dataList = $dataProvider->getModels();

                                if (count($dataList) > 0) {
                                    $this->arrMFPids[$actClient->client->id][0] = $actClient->client;
                                    $this->arrMFPids[$actClient->client->id][1] = $dataList;
                                }

                                $dataProvider = $searchmfp->search([]);
                                $dataProvider->query->andWhere(['clientScopes.service_id' => 5])
                                    ->groupBy('clientScopes.act_id')
                                    ->joinWith('clientScopes clientScopes');

                                $dataList = $dataProvider->getModels();

                                if (count($dataList) > 0) {
                                    $this->arrMFPidsDp[$actClient->client->id][0] = $actClient->client;
                                    $this->arrMFPidsDp[$actClient->client->id][1] = $dataList;
                                }

                                break;
                            default:
                                $this->arrMFPids[$actClient->client->id][0] = $actClient->client;
                                $searchmfp->client_id = $actClient->client_id;

                                $dataList = $searchmfp->search([])->getModels();
                                $this->arrMFPids[$actClient->client->id][1] = $dataList;
                        }

                        $searchmfp = '';

                    }

                }

            }

            foreach($listAct as $actClient) {
                $searchModel->client_id = $actClient->client_id;
                $this->fillAct($searchModel, $actClient->client, $zip);
            }
        } else {
            $listAct = $searchModel->searchPartner()->getModels();

            foreach($listAct as $actPartner) {

                if (isset($this->noActArr[$actPartner->partner->id])) {
                } else {

                    $this->noActArr[$actPartner->partner->id] = 1;

                    if (($actPartner->partner->id == 154) || ($actPartner->partner->id == 849) || ($actPartner->partner->id == 850) || ($actPartner->partner->id == 851) || ($actPartner->partner->id == 852) || ($actPartner->partner->id == 900)) {

                        $searchmfp = $searchModel;

                        switch ($this->serviceType) {
                            case Company::TYPE_SERVICE:
                                $dataList = $searchModel->search([])->getModels();

                                foreach ($dataList as $data) {

                                    $this->arrMFPids[$actPartner->partner->id][0] = $actPartner->partner;
                                    $searchmfp->client_id = $actPartner->partner_id;

                                    $this->arrMFPids[$actPartner->partner->id][1] = array($data);

                                }
                                break;
                            case Company::TYPE_DISINFECT:

                                $searchmfp->client_id = $actPartner->partner_id;

                                /*$listService = ServiceSearch::getServiceList(Company::TYPE_DISINFECT);
                                foreach ($listService as $serviceId => $serviceDescription) {
                                    $dataProvider = $searchmfp->search([]);
                                    $dataProvider->query->andWhere(['partnerScopes.service_id' => $serviceId])
                                        ->groupBy('partnerScopes.act_id')
                                        ->joinWith('partnerScopes partnerScopes');

                                    $dataList = $dataProvider->getModels();
                                    $this->arrMFPids[$actPartner->partner->id][1] = $dataList;
                                }*/

                                $dataProvider = $searchmfp->search([]);
                                $dataProvider->query->andWhere(['partnerScopes.service_id' => 4])
                                    ->groupBy('partnerScopes.act_id')
                                    ->joinWith('partnerScopes partnerScopes');

                                $dataList = $dataProvider->getModels();

                                if (count($dataList) > 0) {
                                    $this->arrMFPids[$actPartner->partner->id][0] = $actPartner->partner;
                                    $this->arrMFPids[$actPartner->partner->id][1] = $dataList;
                                }

                                $dataProvider = $searchmfp->search([]);
                                $dataProvider->query->andWhere(['partnerScopes.service_id' => 5])
                                    ->groupBy('partnerScopes.act_id')
                                    ->joinWith('partnerScopes partnerScopes');

                                $dataList = $dataProvider->getModels();

                                if (count($dataList) > 0) {
                                    $this->arrMFPidsDp[$actPartner->partner->id][0] = $actPartner->partner;
                                    $this->arrMFPidsDp[$actPartner->partner->id][1] = $dataList;
                                }

                                break;
                            default:
                                $this->arrMFPids[$actPartner->partner->id][0] = $actPartner->partner;
                                $searchmfp->client_id = $actPartner->partner_id;

                                $dataList = $searchmfp->search([])->getModels();
                                $this->arrMFPids[$actPartner->partner->id][1] = $dataList;

                        }
                        $searchmfp = '';

                    }

                }

            }

            foreach($listAct as $actPartner) {
                $searchModel->partner_id = $actPartner->partner_id;
                $this->fillAct($searchModel, $actPartner->partner, $zip);
            }
        }

        if ($zip) $zip->close();
    }

    /**
     * @param ActSearch $searchModel
     * @param Company $company
     * @param ZipArchive $zip
     */
    private function fillAct($searchModel, $company, &$zip)
    {

        if ($this->noActArr[$company->id] == 1) {

            // Прикрепить еще один файл "Статистика и анализ"
            if ($this->company) {
                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $dataList = $searchModel->search([])->getModels();
                        if ($dataList) {
                            foreach ($dataList as $data) {

                                if (($company->id == 154) || ($company->id == 849) || ($company->id == 850) || ($company->id == 851) || ($company->id == 852) || ($company->id == 900)) {

                                    if ($this->checkMFPStat == false) {
                                        if (count($this->arrMFPids) > 0) {
                                            $this->generateStatMFP($company, $zip);

                                            // Говорим что мы уже сделали выгрузку для мфп
                                            $this->checkMFPStat = true;
                                        }

                                    }

                                } else {
                                    $this->generateStat($company, array($data), $zip);
                                }

                            }
                        }
                        break;
                    case Company::TYPE_DISINFECT:
                        /*$listService = ServiceSearch::getServiceList(Company::TYPE_DISINFECT);
                        foreach ($listService as $serviceId => $serviceDescription) {
                            $dataProvider = $searchModel->search([]);
                            if ($this->company) {
                                $dataProvider->query->andWhere(['clientScopes.service_id' => $serviceId])
                                    ->groupBy('clientScopes.act_id')
                                    ->joinWith('clientScopes clientScopes');
                            } else {
                                $dataProvider->query->andWhere(['partnerScopes.service_id' => $serviceId])
                                    ->groupBy('partnerScopes.act_id')
                                    ->joinWith('partnerScopes partnerScopes');
                            }
                            $dataList = $dataProvider->getModels();
                            if ($dataList) {
                                $this->generateStat($company, $dataList, $zip);
                            }
                        }
                        break;*/
                    default:
                        $dataList = $searchModel->search([])->getModels();
                        if ($dataList) {

                            if (($company->id == 154) || ($company->id == 849) || ($company->id == 850) || ($company->id == 851) || ($company->id == 852) || ($company->id == 900)) {

                                if ($this->checkMFPStat == false) {
                                    if (count($this->arrMFPids) > 0) {
                                        $this->generateStatMFP($company, $zip);

                                        // Говорим что мы уже сделали выгрузку для мфп
                                        $this->checkMFPStat = true;
                                    }

                                }

                            } else {
                                $this->generateStat($company, $dataList, $zip);
                            }

                        }

                }
            }
            // END Прикрепить еще один файл "Статистика и анализ"

            switch ($this->serviceType) {
                case Company::TYPE_SERVICE:
                    $dataList = $searchModel->search([])->getModels();
                    if (!$dataList) {
                        return;
                    }
                    foreach ($dataList as $data) {

                        /*if(($company->id == 154) || ($company->id == 849) || ($company->id == 850) || ($company->id == 851) || ($company->id == 852) || ($company->id == 900)) {

                            if($this->checkMFP == false) {
                        if(count($this->arrMFPids) > 0) {
                                $this->generateMFPAct($company, false, $zip);

                                // Говорим что мы уже сделали выгрузку для мфп
                                $this->checkMFP = true;
    }
                            }

                        } else {
                            $this->generateAct($company, array($data), $zip);
                        }*/

                        $this->generateAct($company, array($data), $zip);

                    }
                    break;
                case Company::TYPE_DISINFECT:
                    $listService = ServiceSearch::getServiceList(Company::TYPE_DISINFECT);
                    foreach ($listService as $serviceId => $serviceDescription) {
                        $dataProvider = $searchModel->search([]);
                        if ($this->company) {
                            $dataProvider->query->andWhere(['clientScopes.service_id' => $serviceId])
                                ->groupBy('clientScopes.act_id')
                                ->joinWith('clientScopes clientScopes');
                        } else {
                            $dataProvider->query->andWhere(['partnerScopes.service_id' => $serviceId])
                                ->groupBy('partnerScopes.act_id')
                                ->joinWith('partnerScopes partnerScopes');
                        }
                        $dataList = $dataProvider->getModels();
                        if ($dataList) {
                            $this->generateDisinfectCertificate($company, $dataList, $zip, $serviceDescription);

                            if (($company->id == 154) || ($company->id == 849) || ($company->id == 850) || ($company->id == 851) || ($company->id == 852) || ($company->id == 900)) {

                                if ($this->checkMFP == false) {

                                    if (count($this->arrMFPids) > 0) {
                                        $this->generateMFPAct($company, false, $zip);

                                        // Говорим что мы уже сделали выгрузку для мфп
                                        $this->checkMFP = true;
                                    }

                                }

                            } else {
                                $this->generateAct($company, $dataList, $zip, $serviceDescription);
                            }

                        }
                    }
                    break;
                default:
                    $dataList = $searchModel->search([])->getModels();
                    if (!$dataList) {
                        return;
                    }

                    if (($company->id == 154) || ($company->id == 849) || ($company->id == 850) || ($company->id == 851) || ($company->id == 852) || ($company->id == 900)) {

                        if ($this->checkMFP == false) {
                            if (count($this->arrMFPids) > 0) {
                                $this->generateMFPAct($company, false, $zip);

                                // Говорим что мы уже сделали выгрузку для мфп
                                $this->checkMFP = true;
                            }

                        }

                    } else {
                        $this->generateAct($company, $dataList, $zip);
                    }

            }

            // Выгрузка МФП Доп дизенфекция
            if ($this->serviceType == Company::TYPE_DISINFECT) {
                if ($this->checkMFPdp == false) {
                    if (count($this->arrMFPidsDp) > 0) {
                        $this->generateMFPAct($company, true, $zip);

                        // Говорим что мы уже сделали выгрузку для мфп
                        $this->checkMFPdp = true;
                    }

                }
            }

        }
    }

    /**
     * @param Company $company
     * @param Act[] $dataList
     * @param ZipArchive $zip
     */
    private function generateDisinfectCertificate($company, $dataList, &$zip, $serviceDescription = null)
    {
        $files = 0;
        $totalCount = 0;
        $cols = ['A','B','C','D','E','F','G','H','I','J','K'];

        /** @var PHPExcel $objPHPExcel */
        $objPHPExcel = null;
        /** @var PHPExcel_Writer_IWriter $objWriter */
        $objWriter = null;
        /** @var PHPExcel_Worksheet $worksheet */
        $worksheet = null;

        // Получаем номер договора
        $arrContract = Requisites::find()->where(['AND', ['company_id' => $company->id], ['type' => 5]])->select('contract')->column();

        $contractNum = '';

        if(count($arrContract) > 0) {
            $contractNum = $arrContract[0];
        }

        // Получаем номер договора

        $cnt = 1;
        $startRow = 8;
        foreach ($dataList as $act) {
            if (!$totalCount || !($totalCount % 80)) {
                $startRow = 8;
                $files++;

                $objPHPExcel = new PHPExcel();
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

                // Creating a workbook
                $objPHPExcel->getProperties()->setCreator('Mtransservice');
                $objPHPExcel->getProperties()->setTitle('Справка');
                $objPHPExcel->getProperties()->setSubject('Справка');
                $objPHPExcel->getProperties()->setDescription('');
                $objPHPExcel->getProperties()->setCategory('');
                $objPHPExcel->removeSheetByIndex(0);

                //adding worksheet
                $worksheet = new PHPExcel_Worksheet($objPHPExcel, 'справки');
                $objPHPExcel->addSheet($worksheet);

                $worksheet->getPageMargins()->setTop(0.1);
                $worksheet->getPageMargins()->setLeft(0.6);
                $worksheet->getPageMargins()->setRight(0.5);
                $worksheet->getPageMargins()->setBottom(0);

                $objPHPExcel->getDefaultStyle()->applyFromArray([
                    'font' => [
                        'size' => 10,
                    ]
                ]);

                $worksheet->getColumnDimension('A')->setWidth(13);
                $worksheet->getColumnDimension('B')->setWidth(15);
                $worksheet->getColumnDimension('C')->setWidth(10);
                $worksheet->getColumnDimension('D')->setWidth(10);
                $worksheet->getColumnDimension('E')->setWidth(3);
                $worksheet->getColumnDimension('F')->setWidth(3);
                $worksheet->getColumnDimension('G')->setWidth(13);
                $worksheet->getColumnDimension('H')->setWidth(15);
                $worksheet->getColumnDimension('I')->setWidth(10);
                $worksheet->getColumnDimension('J')->setWidth(10);
                $worksheet->getColumnDimension('K')->setWidth(3);
            }

            $endDate = new \DateTime();
            $endDate->setTimestamp($act->served_at);
            $startDate = clone($endDate);
            date_add($endDate, date_interval_create_from_date_string("1 month"));

            $startCol = 0;
            if ($cnt == 2 || $cnt == 4) {
                $startCol = 6;
            }
            if ($cnt == 3) {
                $startRow += 27;
            }
            $row = $startRow;

            $objDrawing = null;
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setPath('images/top.jpg');
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $range = $cols[$startCol] . ($row - 7);
            $objDrawing->setCoordinates($range);
            $objDrawing->setWorksheet($worksheet);
            $objDrawing = null;

            $row--;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray([
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $worksheet->mergeCells($range);
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'СПРАВКА');

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray([
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $worksheet->getRowDimension($row)->setRowHeight(3);

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray([
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $worksheet->mergeCells($range);
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Произведена дезинфекция');

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray([
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ]
            ]);
            $worksheet->mergeCells($range);
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'на основании договора ' . $contractNum);

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Заказчик:');
            $range = $cols[$startCol + 1] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->mergeCells($range);
            $worksheet->getStyle($range)->getAlignment()->setWrapText(true);
            $worksheet->setCellValueByColumnAndRow($startCol + 1, $row, $company->name);
            $worksheet->getRowDimension($row)->setRowHeight(28);

            $worksheet->getStyleByColumnAndRow($startCol + 1, $row)->applyFromArray([
                    'font' => [
                        'size' => 9,
                    ],
                ]
            );

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Марка:');
            $worksheet->setCellValueByColumnAndRow($startCol + 1, $row, isset($act->mark->name) ? $act->mark->name : '');
            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray([
                    'font' => [
                        'color' => ['argb' => 'FF006699'],
                    ],
                ]
            );

            $worksheet->setCellValueByColumnAndRow($startCol + 2, $row, 'Гос. номер:');
            $worksheet->setCellValueByColumnAndRow($startCol + 3, $row, $act->car_number);
            $worksheet->getStyleByColumnAndRow($startCol + 2, $row)->applyFromArray([
                    'font' => [
                        'color' => ['argb' => 'FF006699'],
                    ],
                ]
            );
            $worksheet->getStyleByColumnAndRow($startCol + 2, $row)->applyFromArray([
                    'font' => [
                        'size' => 9,
                    ],
                ]
            );

            $worksheet->getStyleByColumnAndRow($startCol + 3, $row)->applyFromArray([
                    'font' => [
                        'size' => 9,
                    ],
                ]
            );

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Срок действия:');

            $range = $cols[$startCol + 1] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->mergeCells($range);

            $text = "C " . $startDate->format('d.m.Y') . " по " . $endDate->format('d.m.Y') . " (один месяц)";
            $worksheet->setCellValueByColumnAndRow($startCol + 1, $row, $text);

            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray([
                    'font' => [
                        'size' => 9,
                    ],
                ]
            );

            $worksheet->getStyleByColumnAndRow($startCol + 1, $row)->applyFromArray([
                    'font' => [
                        'size' => 9,
                    ],
                ]
            );

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray([
                'alignment' => [
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ]
            ]);
            $worksheet->mergeCells($range);
            $worksheet->setCellValueByColumnAndRow($startCol, $row, '1% Дезинфицирующее средство: Демос (рекомендовано Роспотребнадзором)');

            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray([
                    'font' => [
                        'name' => 'Arial',
                        'size' => 6,
                    ],
                ]
            );

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 1] . $row;
            $worksheet->mergeCells($range);

            $range = $cols[$startCol + 2] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->mergeCells($range);

            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Лицензия № ЛО-36-01-002839');
            $worksheet->setCellValueByColumnAndRow($startCol + 2, $row, 'г. ' . (isset($act->client->address) ? $act->client->address : ''));

            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray([
                    'font' => [
                        'size' => 9,
                    ],
                ]
            );

            $worksheet->getStyleByColumnAndRow($startCol + 2, $row)->applyFromArray([
                    'font' => [
                        'size' => 8,
                    ],
                ]
            );

            $row++;

            $worksheet->getRowDimension($row)->setRowHeight(7);

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Исполнитель');

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'ООО «Международный Транспортный Сервис»');

            $row++; $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Петросян А.Р.___________');
            $objDrawing = null;
            if($company->is_act_sign == 1) {
                //печать
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/post-small.png');
                $objDrawing->setName($act->car_number);
                $range = $cols[$startCol + 2] . ($row - 3);
                $objDrawing->setCoordinates($range);
                $objDrawing->setWorksheet($worksheet);
                $objDrawing->setOffsetX(-40);
                $objDrawing = null;
                //подпись
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setName($act->car_number);
                $objDrawing->setPath('images/sign.png');
                $range = $cols[$startCol + 1] . ($row - 2);
                $objDrawing->setCoordinates($range);
                $objDrawing->setWorksheet($worksheet);
                $objDrawing->setOffsetY(-10);
                $objDrawing->setOffsetX(-30);
                $objDrawing = null;
            } else if($company->is_act_sign == 2) {
                //подпись
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setName($act->car_number);
                $objDrawing->setPath('images/sign.png');
                $range = $cols[$startCol + 1] . ($row - 2);
                $objDrawing->setCoordinates($range);
                $objDrawing->setWorksheet($worksheet);
                $objDrawing->setOffsetY(-10);
                $objDrawing->setOffsetX(-30);
                $objDrawing = null;
            }
            $row++;

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->mergeCells($range);
            $worksheet->getStyle($range)->getAlignment()->setWrapText(true);
            $text = "ИНН 3665100480 КПП 366501001 ОГРН 1143668022266 394065, Россия, Воронежская область," .
                "г. Воронеж, ул. Героев Сибиряков, д. 24, кв. 116 \n Тел.: 8 800 55 008 55 \n " .
                "E-Mail: mtransservice@mail.ru, Web.: mtransservice.ru";
            $worksheet->setCellValueByColumnAndRow($startCol, $row, $text);
            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray([
                    'font' => [
                        'size' => 6,
                    ],
                ]
            );
            $worksheet->getRowDimension($row)->setRowHeight(38);

            if ($cnt == 2) {
                $row += 5;
                $worksheet->getStyle("A$row:K$row")
                    ->applyFromArray([
                            'borders' => [
                                'top' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]
                    );
                $borderStart = $startRow - 7;
                $borderEnd = $borderStart + 48;
                $worksheet->getStyle("E$borderStart:E$borderEnd")
                    ->applyFromArray([
                            'borders' => [
                                'right' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['argb' => 'FF000000'],
                                ],
                            ],
                        ]
                    );
            }

            $cnt++;
            $totalCount++;
            if ($cnt == 5) {

                $cnt = 1;
                $worksheet->setBreak( "A$row" , PHPExcel_Worksheet::BREAK_ROW );
                $startRow += 22;
            }

            if (!($totalCount % 80) || $totalCount == count($dataList)) {
                $type = Service::$listType[$this->serviceType]['en'];
                $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
                if (!is_dir($path)) {
                    mkdir($path, 0755, 1);
                }

                $filename = $serviceDescription . " Справка $company->name от " . date('m-Y', $this->time) . "-$files.xlsx";

                $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
                $objWriter->save($fullFilename);
                if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));
            }
        }
    }

    /**
     * @param Company $company
     * @param array $dataList
     * @param ZipArchive $zip
     */
    private function generateAct($company, $dataList, &$zip, $serviceDescription = null)
    {
        $this->objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        // Creating a workbook
        $this->objPHPExcel->getProperties()->setCreator('Mtransservice');
        $this->objPHPExcel->getProperties()->setTitle('Акт');
        $this->objPHPExcel->getProperties()->setSubject('Акт');
        $this->objPHPExcel->getProperties()->setDescription('');
        $this->objPHPExcel->getProperties()->setCategory('');
        $this->objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'акт');
        $this->objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getPageMargins()->setTop(2);
        $companyWorkSheet->getPageMargins()->setLeft(0.5);
        $companyWorkSheet->getRowDimension(1)->setRowHeight(1);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers;
        $monthName = DateHelper::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));
        $currentMonthName = DateHelper::getMonthName($date->getTimestamp());

        // Высчитываем номер для акта и счета
        $fileNameCheck = '';
        $numberAct = '';
        $dataExpl = '';

        if ($this->serviceType == Company::TYPE_SERVICE) {
            $firstCheck = $dataList[0];
            $fileNameCheck = "Акт {$company->name} - {$firstCheck->car_number} - {$firstCheck->id} от " . date('d-m-Y', $firstCheck->served_at) . ".xls";
            $dataExpl = date('m-Y', $firstCheck->served_at);
        } else {
            $fileNameCheck = $serviceDescription. " Акт $company->name от " . date('m-Y', $this->time) . ".xls";;
            $dataExpl = date('m-Y', $this->time);
        }
        $fileNameCheck = str_replace('"', '', $fileNameCheck);
        $fileNameCheck = str_replace(' ', '_', $fileNameCheck);

        $companyInt = 0;

        if($this->company) {
            $companyInt = 1;
        }

        if($this->checkDoQueryNumber == false) {
            $this->arrActNumbers = ActData::find()->where(['type' => $this->serviceType, 'company' => $companyInt, 'period' => $dataExpl])->select('number, name')->all();
            $this->checkDoQueryNumber = true;
        }

        if (count($this->arrActNumbers) > 0) {

            for($i = 0; $i < count($this->arrActNumbers); $i++) {
                if($this->arrActNumbers[$i]['name'] == $fileNameCheck) {
                    $numberAct = $this->arrActNumbers[$i]['number'];
                }
            }

        }

        if($numberAct == '') {
            $newActData = new ActData();
            $newActData->type = $this->serviceType;
            $newActData->company = $companyInt;
            $newActData->period = $dataExpl;
            $newActData->name = $fileNameCheck;

            if($newActData->save()) {

                if($serviceDescription == 'доп. дезинфекция') {
                    $numberAct = 'DD' . $newActData->id;
                } else {
                    switch ($this->serviceType) {
                        case 2:
                            $numberAct = 'M' . $newActData->id;
                            break;
                        case 3:
                            $numberAct = 'S' . $newActData->id;
                            break;
                        case 4:
                            $numberAct = 'T' . $newActData->id;
                            break;
                        case 5:
                            $numberAct = 'D' . $newActData->id;
                            break;
                    }
                }

                $newActData->number = $numberAct;
                $newActData->save();

            }

        }

        // Высчитываем номер для акта и счета
        $row = 8;

        if ($this->serviceType == Company::TYPE_DISINFECT) {
            $companyWorkSheet->getStyle('B2:F4')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $companyWorkSheet->mergeCells('B2:F2');
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)" . " № " . $numberAct;
            $companyWorkSheet->setCellValue('B2', $text);
            $companyWorkSheet->mergeCells('B3:F3');
            $text = "по договору на оказание услуг " . $company->getRequisitesByType($this->serviceType, 'contract');
            $companyWorkSheet->setCellValue('B3', $text);
            $companyWorkSheet->mergeCells('B4:F4');
            $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
            $companyWorkSheet->setCellValue('B4', $text);

            $companyWorkSheet->setCellValue('B5', 'г.Воронеж');
            $companyWorkSheet->getStyle('E5')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                )
            ));
            if ($this->company) {
                $companyWorkSheet->setCellValue('F5', '1 ' . $monthName[1] . date(' Y', $this->time));
            } else {
                $companyWorkSheet->setCellValue('F5', date('d ') . $currentMonthName[1] . date(' Y'));
            }

            $companyWorkSheet->mergeCells('B' . $row . ':F' . $row);
            $companyWorkSheet->mergeCells('B7:F7');
            if ($this->company) {
                $companyWorkSheet->setCellValue('B' . $row, "Исполнитель: ООО «Международный Транспортный Сервис»");
                $companyWorkSheet->setCellValue('B7', "Заказчик: $company->name");

                $row++;

                $companyWorkSheet->mergeCells('B' . $row . ':F' . $row);
                $companyWorkSheet->setCellValue('B' . $row, "Лицензия № ЛО-36-01-002839");
                $row++;

            } else {
                $companyWorkSheet->setCellValue('B7', "Исполнитель: $company->name");
                $companyWorkSheet->setCellValue('B' . $row, "Заказчик: ООО «Международный Транспортный Сервис»");
                $row++;
            }

            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(100);

            $companyWorkSheet->mergeCells('B' . $row . ':F' . $row);
            $companyWorkSheet->getStyle('B' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('B' . $row . ':F' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                )
            ));
            $companyWorkSheet->setCellValue('B' . $row, $company->getRequisitesByType($this->serviceType, 'header'));

        } else {
            $companyWorkSheet->getStyle('B2:I4')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $companyWorkSheet->mergeCells('B2:I2');
            if($company->is_split) {
                $companyWorkSheet->mergeCells('B2:J2');
            }
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)" . " № " . $numberAct;
            $companyWorkSheet->setCellValue('B2', $text);
            $companyWorkSheet->mergeCells('B3:I3');
            $text = "по договору на оказание услуг " . $company->getRequisitesByType($this->serviceType, 'contract');
            $companyWorkSheet->setCellValue('B3', $text);
            $companyWorkSheet->mergeCells('B4:I4');
            $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
            $companyWorkSheet->setCellValue('B4', $text);

            $companyWorkSheet->setCellValue('B5', 'г.Воронеж');
            $companyWorkSheet->getStyle('H5:I5')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                )
            ));
            $companyWorkSheet->mergeCells('H5:I5');
            if($company->is_split) {
                $companyWorkSheet->mergeCells('H5:J5');
            }
            if ($this->company) {
                $companyWorkSheet->setCellValue('H5', date("t ", $this->time) . $monthName[1] . date(' Y', $this->time));
            } else {
                $companyWorkSheet->setCellValue('H5', date('d ') . $currentMonthName[1] . date(' Y'));
            }

            $companyWorkSheet->mergeCells('B8:I8');
            $companyWorkSheet->mergeCells('B7:I7');
            if ($this->company) {
                $companyWorkSheet->setCellValue('B8', "Исполнитель: ООО «Международный Транспортный Сервис»");
                $companyWorkSheet->setCellValue('B7', "Заказчик: $company->name");
            } else {
                $companyWorkSheet->setCellValue('B7', "Исполнитель: $company->name");
                $companyWorkSheet->setCellValue('B8', "Заказчик: ООО «Международный Транспортный Сервис»");
            }

            $companyWorkSheet->mergeCells('B10:I10');
            $companyWorkSheet->getStyle('B10:I10')->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('B10:I10')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                )
            ));
            $companyWorkSheet->getRowDimension(10)->setRowHeight(100);
            $companyWorkSheet->setCellValue('B10', $company->getRequisitesByType($this->serviceType, 'header'));
            $row = 10;
        }

        $row++; $row++;

        $rowStarts = $row;

        //main values
        $num = 0;
        $total = 0;
        $count = 0;
        $checkService = false;
        switch($this->serviceType) {
            case Company::TYPE_SERVICE:
                $checkService = true;
                $first = $dataList[0];
                $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
            case Company::TYPE_TIRES:

                $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    )
                ));
                $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                $iNumTires = 0;

                /** @var Act $data */
                foreach ($dataList as $data) {
                    $num = 0;

                    // Отступ перед новым ТС шиномонтажа
                    if($iNumTires > 0) {
                        $row++;
                    }
                    $iNumTires++;

                    $companyWorkSheet->mergeCells("B$row:C$row");
                    $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                    $companyWorkSheet->mergeCells("D$row:E$row");
                    $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                    $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                    if ($this->company) {
                        $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                        $companyWorkSheet->mergeCells("H$row:I$row");
                        $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                    } else {
                        $companyWorkSheet->mergeCells("G$row:I$row");
                        $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                    }
                    $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'color' => array('argb' => 'FF006699'),
                                'size' => 12,
                            ),
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('argb' => 'FF000000'),
                                ),
                            ),
                        )
                    );

                    $row++;
                    $date = new \DateTime();
                    $date->setTimestamp($data->served_at);
                    $companyWorkSheet->mergeCells("B$row:C$row");
                    $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                    $companyWorkSheet->mergeCells("D$row:E$row");
                    $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                    $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                    if ($this->company) {
                        $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                        $companyWorkSheet->mergeCells("H$row:I$row");
                        $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                    } else {
                        $companyWorkSheet->mergeCells("G$row:I$row");
                        $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                    }
                    $companyWorkSheet->getStyle("B$row:I$row")
                        ->applyFromArray(array(
                                'borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => 'FF000000'),
                                    ),
                                ),
                                'font' => array(
                                    'bold' => true,
                                ),
                            )
                        );

                    if($checkService == false) {

                        $row++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                        $companyWorkSheet->setCellValue("G$row", "Стоимость");
                        $companyWorkSheet->setCellValue("H$row", "Кол-во");
                        $companyWorkSheet->setCellValue("I$row", "Сумма");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );

                        /** @var ActScope $scope */
                        $subtotal = 0;
                        $subcount = 0;
                        if ($this->company) {
                            $listScope = $data->clientScopes;
                        } else {
                            $listScope = $data->partnerScopes;
                        }
                        foreach ($listScope as $scope) {
                            $row++;
                            $num++;
                            $companyWorkSheet->mergeCells("B$row:F$row");
                            $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                            if (mb_strlen($scope->description) > 30) {
                                $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                            }
                            $companyWorkSheet->setCellValue("G$row", $scope->price);
                            $companyWorkSheet->setCellValue("H$row", $scope->amount);
                            $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                            $total += $scope->amount * $scope->price;
                            $subtotal += $scope->amount * $scope->price;
                            $subcount += $scope->amount;
                            $count += $scope->amount;
                        }
                        $row++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "Итого:");
                        $companyWorkSheet->setCellValue("G$row", '');
                        $companyWorkSheet->setCellValue("H$row", $subcount);
                        $companyWorkSheet->setCellValue("I$row", $subtotal);
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                ),
                            )
                        );

                        $companyWorkSheet->getStyle("B" . $rowStarts . ":I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                    } else {

                        // Запасные части
                        $row++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "Запасные части");
                        $companyWorkSheet->setCellValue("G$row", "Стоимость");
                        $companyWorkSheet->setCellValue("H$row", "Кол-во");
                        $companyWorkSheet->setCellValue("I$row", "Сумма");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );

                        /** @var ActScope $scope */
                        $subtotal = 0;
                        $subcount = 0;
                        if ($this->company) {
                            $listScope = $data->clientScopes;
                        } else {
                            $listScope = $data->partnerScopes;
                        }
                        foreach ($listScope as $scope) {
                            if($scope->parts == 1) {
                                $row++;
                                $num++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                if (mb_strlen($scope->description) > 30) {
                                    $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                }
                                $companyWorkSheet->setCellValue("G$row", $scope->price);
                                $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                $total += $scope->amount * $scope->price;
                                $subtotal += $scope->amount * $scope->price;
                                $subcount += $scope->amount;
                                $count += $scope->amount;
                            }
                        }
                        $row++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "Итого:");
                        $companyWorkSheet->setCellValue("G$row", '');
                        $companyWorkSheet->setCellValue("H$row", $subcount);
                        $companyWorkSheet->setCellValue("I$row", $subtotal);
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                ),
                            )
                        );

                        // Услуги
                        $row++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "Услуги");
                        $companyWorkSheet->setCellValue("G$row", "Стоимость");
                        $companyWorkSheet->setCellValue("H$row", "Кол-во");
                        $companyWorkSheet->setCellValue("I$row", "Сумма");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );

                        /** @var ActScope $scope */
                        $subtotal = 0;
                        $subcount = 0;
                        if ($this->company) {
                            $listScope = $data->clientScopes;
                        } else {
                            $listScope = $data->partnerScopes;
                        }
                        foreach ($listScope as $scope) {
                            if($scope->parts == 0) {
                                $row++;
                                $num++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                if (mb_strlen($scope->description) > 30) {
                                    $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                }
                                $companyWorkSheet->setCellValue("G$row", $scope->price);
                                $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                $total += $scope->amount * $scope->price;
                                $subtotal += $scope->amount * $scope->price;
                                $subcount += $scope->amount;
                                $count += $scope->amount;
                            }
                        }
                        $row++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "Итого:");
                        $companyWorkSheet->setCellValue("G$row", '');
                        $companyWorkSheet->setCellValue("H$row", $subcount);
                        $companyWorkSheet->setCellValue("I$row", $subtotal);
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                ),
                            )
                        );

                        $companyWorkSheet->getStyle("B" . $rowStarts . ":I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                    }

                }
                break;

            case Company::TYPE_WASH:
                $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                if($company->is_split) {
                    $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                }

                $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                if($company->is_split) {
                    $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                }
                $companyWorkSheet->fromArray($headers, null, 'B12');
                /** @var Act $data */
                $currentId = 0;
                $isParent = false;
                if ($this->company && count($company->children) > 0) {
                    $isParent = true;
                }
                foreach ($dataList as $data) {
                    if ($isParent && $currentId != $data->client_id) {
                        $row++;

                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );

                        $companyWorkSheet->mergeCells("B$row:I$row");
                        $companyWorkSheet->setCellValue("B$row", $data->client->name);
                        $currentId = $data->client_id;
                    }

                    $row++;
                    $num++;
                    $column = 1;
                    $date = new \DateTime();
                    $date->setTimestamp($data->served_at);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                    if($company->is_split) {
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                    }

                    if ($this->company) {
                        $services = [];
                        foreach ($data->clientScopes as $scope) {
                            $services[] = $scope->description;
                        }

                        $showServiceName = implode('+', $services);

                        // заменяем внутри+снаружи на снаружи+внутри
                        if(mb_strpos($showServiceName, 'внутри+снаружи') !== false) {
                            $showServiceName = str_replace('внутри+снаружи', 'снаружи+внутри', $showServiceName);
                        }

                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . $showServiceName);
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                        $total += $data->income;
                    } else {
                        $services = [];
                        foreach ($data->partnerScopes as $scope) {
                            $services[] = $scope->description;
                        }

                        $showServiceName = implode('+', $services);

                        // заменяем внутри+снаружи на снаружи+внутри
                        if(mb_strpos($showServiceName, 'внутри+снаружи') !== false) {
                            $showServiceName = str_replace('внутри+снаружи', 'снаружи+внутри', $showServiceName);
                        }

                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . $showServiceName);
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                        $total += $data->expense;
                    }
                    $companyWorkSheet->getCellByColumnAndRow($column, $row)
                        ->getStyle()
                        ->getNumberFormat()
                        ->setFormatCode(
                            PHPExcel_Style_NumberFormat::FORMAT_TEXT
                        );
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                }

                $companyWorkSheet->getStyle('B' . $rowStarts . ':I' . $rowStarts)->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
                if($company->is_split) {
                    $companyWorkSheet->getStyle('J12')->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'color' => array('argb' => 'FF006699'),
                            ),
                        )
                    );
                }

                $companyWorkSheet->getStyle("B" . $rowStarts . ":I$row")
                    ->applyFromArray(array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('argb' => 'FF000000'),
                                ),
                            ),
                        )
                    );
                if($company->is_split) {
                    $companyWorkSheet->getStyle("J12:J$row")
                        ->applyFromArray(array(
                                'borders' => array(
                                    'allborders' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                                        'color' => array('argb' => 'FF000000'),
                                    ),
                                ),
                            )
                        );
                }
                break;

            case Company::TYPE_DISINFECT:
                $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                $companyWorkSheet->fromArray($headers, null, 'B' . $row);
                /** @var Act $data */
                $currentId = 0;
                $isParent = false;
                if ($this->company && count($company->children) > 0) {
                    $isParent = true;
                }
                foreach ($dataList as $data) {
                    if ($isParent && $currentId != $data->client_id) {
                        $row++;

                        $companyWorkSheet->getStyle("B$row:F$row")->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );

                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", $data->client->name);
                        $currentId = $data->client_id;
                    }

                    $row++;
                    $num++;
                    $column = 1;
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                    if ($this->company) {
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                        $total += $data->income;
                    } else {
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                        $total += $data->expense;
                    }
                    $companyWorkSheet->getCellByColumnAndRow($column, $row)
                        ->getStyle()
                        ->getNumberFormat()
                        ->setFormatCode(
                            PHPExcel_Style_NumberFormat::FORMAT_TEXT
                        );
                    $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                }

                $companyWorkSheet->getStyle('B' . $rowStarts . ':F' . $rowStarts)
                    ->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'color' => array('argb' => 'FF006699'),
                            ),
                        )
                    );

                $companyWorkSheet->getStyle("B" . $rowStarts . ":F$row")
                    ->applyFromArray(array(
                            'borders' => array(
                                'allborders' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => array('argb' => 'FF000000'),
                                ),
                            ),
                        )
                    );

                break;
        }

        //footer
        if ($this->serviceType == Company::TYPE_DISINFECT) {
            $row++;
            $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
            $companyWorkSheet->setCellValue("F$row", "$total");

            $row++;$row++;
            $companyWorkSheet->mergeCells("B$row:F$row");
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $total (" . DigitHelper::num2str($total) . ") рублей. НДС нет.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++;
            $companyWorkSheet->mergeCells("B$row:F$row");
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
            $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "Работу сдал");
            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", "Работу принял");

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "Исполнитель");
            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", "Заказчик");

            $row++;
            if($company->is_act_sign == 1) {
                //подпись
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/sign.png');
                $objDrawing->setCoordinates("C$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(50);
                //печать
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/post.png');
                $objDrawing->setCoordinates("C$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(100);
            } else if($company->is_act_sign == 2) {
                //подпись
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/sign.png');
                $objDrawing->setCoordinates("C$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(50);
            }

            $row++;
            $companyWorkSheet->setCellValue("B$row", "Петросян А.Р. ____________");
            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", "$company->director ____________");

            $row++;
            $row++;

            $companyWorkSheet->setCellValue("E$row", "М.П.");
        } else {
            $row++;
            if ($this->serviceType == Company::TYPE_WASH) {
                if($company->is_split) {
                    $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                    $companyWorkSheet->setCellValue("I$row", "$total");
                } else {
                    $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                    $companyWorkSheet->setCellValue("H$row", "$total");
                }
            } else {
                $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                $companyWorkSheet->setCellValue("G$row", "");
                $companyWorkSheet->setCellValue("H$row", "$count");
                $companyWorkSheet->setCellValue("I$row", "$total");
                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                    ]
                );
            }

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("B$row:J$row");
            }
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $total (" . DigitHelper::num2str($total) . ") рублей. НДС нет.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++;
            $companyWorkSheet->mergeCells("B$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("B$row:J$row");
            }
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            if ($this->company) {
                $companyWorkSheet->setCellValue("B$row", "Работу сдал");
                $companyWorkSheet->setCellValue("G$row", "Работу принял");
            } else {
                $companyWorkSheet->setCellValue("B$row", "Работу принял");
                $companyWorkSheet->setCellValue("G$row", "Работу сдал");
            }

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            if ($this->company) {
                $companyWorkSheet->setCellValue("B$row", "Исполнитель");
                $companyWorkSheet->setCellValue("G$row", "Заказчик");
            } else {
                $companyWorkSheet->setCellValue("B$row", "Заказчик");
                $companyWorkSheet->setCellValue("G$row", "Исполнитель");
            }

            $row++;
            //подпись
            if ($company->is_act_sign == 1) {
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Sample image');
                $objDrawing->setDescription('Sample image');
                $objDrawing->setPath('images/sign.png');

                if ($this->serviceType == Company::TYPE_WASH) {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(50);
                } else {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(10);
                }
                //печать
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/post.png');
                $objDrawing->setCoordinates("D$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(30);
            } else if ($company->is_act_sign == 2) {
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Sample image');
                $objDrawing->setDescription('Sample image');
                $objDrawing->setPath('images/sign.png');

                if ($this->serviceType == Company::TYPE_WASH) {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(50);
                } else {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(10);
                }
            }
            $row++;
            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            $companyWorkSheet->setCellValue("B$row", "Петросян А.Р. ____________");
            $companyWorkSheet->setCellValue("G$row", "$company->director ____________");

            $row++; $row++;

            $companyWorkSheet->setCellValue("G$row", "М.П.");
        }

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::TYPE_SERVICE) {
            $first = $dataList[0];
            $filename = "Акт {$company->name} - {$first->car_number} - {$first->id} от " . date('d-m-Y', $first->served_at) . ".xls";
        } else {
            $filename = $serviceDescription. " Акт $company->name от " . date('m-Y', $this->time) . ".xls";
        }
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));

        if (!$this->company) {
            return;
        }


        ///////////// check
        $this->objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        // Creating a workbook
        $this->objPHPExcel->getProperties()->setCreator('Mtransservice');
        $this->objPHPExcel->getProperties()->setTitle('Счет');
        $this->objPHPExcel->getProperties()->setSubject('Счет');
        $this->objPHPExcel->getProperties()->setDescription('');
        $this->objPHPExcel->getProperties()->setCategory('');
        $this->objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Счет');
        $this->objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getRowDimension(1)->setRowHeight(100);
        $companyWorkSheet->getColumnDimension('A')->setWidth(5);
        $companyWorkSheet->getColumnDimension('B')->setWidth(20);
        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
        $companyWorkSheet->getColumnDimension('D')->setWidth(10);
        $companyWorkSheet->getColumnDimension('E')->setWidth(30);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers
        $monthName = DateHelper::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));

        $companyWorkSheet->mergeCells('B2:E2');
        $companyWorkSheet->setCellValue('B2', "ООО «Международный Транспортный Сервис»");

        $companyWorkSheet->mergeCells('B3:E3');
        $companyWorkSheet->setCellValue('B3', "Адрес: 394065, г. Воронеж, ул. Героев Сибиряков, д. 24, оф. 116");

        $companyWorkSheet->getStyle("B5")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('B5', 'ИНН 366 510 0480');

        $companyWorkSheet->getStyle("C5")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('C5', 'КПП 366 501 001');

        $companyWorkSheet->mergeCells('B6:C6');
        $companyWorkSheet->getStyle("B6:C6")->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle("B6:C6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->getRowDimension(6)->setRowHeight(40);
        $companyWorkSheet->setCellValue('B6', 'Получатель:ООО«Международный Транспортный Сервис»');

        $companyWorkSheet->mergeCells('D5:D6');
        $companyWorkSheet->getStyle("D5:D6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );
        $companyWorkSheet->setCellValue('D5', 'Сч.№');

        $companyWorkSheet->mergeCells('E5:E6');
        $companyWorkSheet->getStyle("E5:E6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );
        $companyWorkSheet->getStyle('E5:E6')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $companyWorkSheet->setCellValue('E5', ' 40702810913000016607');

        $companyWorkSheet->mergeCells('B7:C8');
        $companyWorkSheet->getStyle("B7:C8")->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle("B7:C8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('B7', 'Банк получателя: Центрально-Черноземный Банк ОАО «Сбербанк России»  г. Воронеж');

        $companyWorkSheet->getStyle("D7")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('D7', 'БИК');

        $companyWorkSheet->getStyle("E7")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->getStyle('E7')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $companyWorkSheet->setCellValue('E7', ' 042007681');

        $companyWorkSheet->getStyle("D8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('D8', 'К/сч.№');

        $companyWorkSheet->getStyle("E8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->getStyle('E8')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $companyWorkSheet->setCellValue('E8', ' 30101810600000000681');

        $row = 9;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->getStyle("B$row:E$row")->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );

        // Высчитываем номер для акта и счета
        $fileNameCheck = '';

        if ($this->serviceType == Company::TYPE_SERVICE) {
            $firstCheck = $dataList[0];
            $fileNameCheck = "Счет {$company->name} - {$firstCheck->car_number} - {$firstCheck->id} от " . date('d-m-Y', $firstCheck->served_at) . ".xls";
        } else {
            $fileNameCheck = $serviceDescription . " Счет $company->name от " . date('m-Y', $this->time) . ".xls";
        }
        $fileNameCheck = str_replace('"', '', $fileNameCheck);
        $fileNameCheck = str_replace(' ', '_', $fileNameCheck);

        if($this->checkDoQueryNumber == false) {
            $this->arrActNumbers = ActData::find()->where(['type' => $this->serviceType, 'company' => $companyInt, 'period' => $dataExpl])->select('number, name')->all();
            $this->checkDoQueryNumber = true;
        }

        $checkHaveAct = false;

        if (count($this->arrActNumbers) > 0) {

            for($i = 0; $i < count($this->arrActNumbers); $i++) {
                if($this->arrActNumbers[$i]['name'] == $fileNameCheck) {
                    $checkHaveAct = true;
                }
            }

        }

        if($checkHaveAct == false) {
            $newActData = new ActData();
            $newActData->type = $this->serviceType;
            $newActData->company = $companyInt;
            $newActData->period = $dataExpl;
            $newActData->name = $fileNameCheck;
            $newActData->number = $numberAct;
            $newActData->save();
        }

        // Высчитываем номер для акта и счета

        if (in_array($this->serviceType, [Company::TYPE_SERVICE])) {
            $first = $dataList[0];
            $text = "СЧЕТ" . " № " . $numberAct . " от " . date("d ", $first->served_at) . ' ' . $monthName[1] . date(' Y', $this->time);
        } else {
            if ($this->serviceType == Company::TYPE_DISINFECT) {

                if($serviceDescription == 'дезинфекция') {
                    $text = "СЧЕТ" . " № " . $numberAct . " от 01" . ' ' . $monthName[1] . date(' Y', $this->time);
                } else {
                    $text = "СЧЕТ" . " № " . $numberAct . " от " . date("t", $this->time) . ' ' . $monthName[1] . date(' Y', $this->time);
                }

            } else {
                $text = "СЧЕТ" . " № " . $numberAct . " от " . date("t", $this->time) . ' ' . $monthName[1] . date(' Y', $this->time);
            }

        }
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->getStyle("B$row:E$row")->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );
        $text = 'За услуги ' . Service::$listType[$this->serviceType]['in'] . ', оказанные в ' . $monthName[2] . date(' Y');
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $text = "Плательщик: $company->name";
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
        $companyWorkSheet->getStyle("B$row:E$row")->getAlignment()->setWrapText(true);
        $text = "Всего наименований " .count($dataList) . ", на сумму $total (" . DigitHelper::num2str($total) . "). НДС нет.";
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        if ($company->is_act_sign == 1) {
            //печать
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setPath('images/post.png');
            $objDrawing->setCoordinates("C$row");
            $objDrawing->setWorksheet($companyWorkSheet);
            $objDrawing->setOffsetX(30);
            $row++;
            $row++;
            //подпись
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setPath('images/sign.png');
            $objDrawing->setCoordinates("B{$row}");
            $objDrawing->setWorksheet($companyWorkSheet);
            $objDrawing->setOffsetX(70);
            $row++;
        } else if ($company->is_act_sign == 2) {
            $row++;
            //подпись
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setPath('images/sign.png');
            $objDrawing->setCoordinates("B{$row}");
            $objDrawing->setWorksheet($companyWorkSheet);
            $objDrawing->setOffsetX(70);
            $row++;
        }else{
            $row++;
            $row++;
            $row++;
        }
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->setCellValue("B$row", 'Петросян А.Р.__________');

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::TYPE_SERVICE) {
            $first = $dataList[0];
            $filename = "Счет {$company->name} - {$first->car_number} - {$first->id} от " . date('d-m-Y', $first->served_at) . ".xls";
        } else {
            $filename = $serviceDescription . " Счет $company->name от " . date('m-Y', $this->time) . ".xls";
        }
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));
    }

    /** Создание файла статистики и анализа
     * @param Company $company
     * @param array $dataList
     * @param ZipArchive $zip
     */
    private function generateStat($company, $dataList, &$zip, $serviceDescription = null)
    {
        $this->objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        // Creating a workbook
        $this->objPHPExcel->getProperties()->setCreator('Mtransservice');
        $this->objPHPExcel->getProperties()->setTitle('Акт');
        $this->objPHPExcel->getProperties()->setSubject('Акт');
        $this->objPHPExcel->getProperties()->setDescription('');
        $this->objPHPExcel->getProperties()->setCategory('');
        $this->objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'акт');
        $this->objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getPageMargins()->setTop(2);
        $companyWorkSheet->getPageMargins()->setLeft(0.5);
        $companyWorkSheet->getRowDimension(1)->setRowHeight(1);
        $companyWorkSheet->getRowDimension(10)->setRowHeight(100);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers;
        $monthName = DateHelper::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));
        $currentMonthName = DateHelper::getMonthName($date->getTimestamp());

        $companyWorkSheet->getStyle('B2:I4')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        ));
        $companyWorkSheet->mergeCells('B2:I2');
        if($company->is_split) {
            $companyWorkSheet->mergeCells('B2:J2');
        }

        $textService = '';
        $textTitle = '';

        switch ($this->serviceType) {
            case 2:
                $textService = "мойка";
                $textTitle = "мойке";
                break;
            case 3:
                $textService = "сервис";
                $textTitle = "сервису";
                break;
            case 4:
                $textService = "шиномонтаж";
                $textTitle = "шиномонтажу";
                break;
        }

        $text = "Статистика и анализ по " . $textTitle . " машин";
        $companyWorkSheet->setCellValue('B2', $text);

        $styleArray = array(
            'font'  => array(
                'size'  => 14,
            ));

        $companyWorkSheet->getStyle('B2')->applyFromArray($styleArray);

        $companyWorkSheet->mergeCells('B3:I3');
        $companyWorkSheet->getRowDimension(3)->setRowHeight(45);
        $companyWorkSheet->getStyle('B3:I3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $companyWorkSheet->getStyle('B3')->getAlignment()->setWrapText(true);
        $text = "компании " . $company->name . " за " . $monthName[0] . " " . date('Y', $this->time);
        $companyWorkSheet->setCellValue('B3', $text);
        $companyWorkSheet->getStyle('B3')->applyFromArray($styleArray);
        $companyWorkSheet->mergeCells('B4:I4');

        $styleArray = array(
            'font'  => array(
                'size'  => 12,
            ));

        $companyWorkSheet->mergeCells('B5:F5');
        $companyWorkSheet->setCellValue('B5', 'г.Воронеж');
        $companyWorkSheet->getStyle('B5')->applyFromArray($styleArray);
        $companyWorkSheet->getStyle('H5:I5')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        ));
        $companyWorkSheet->mergeCells('H5:I5');
        if($company->is_split) {
            $companyWorkSheet->mergeCells('H5:J5');
        }
        $companyWorkSheet->mergeCells('G5:I5');
        if ($this->company) {
            $companyWorkSheet->setCellValue('H5', date("t ", $this->time) . $monthName[1] . date(' Y', $this->time));
        } else {
            $companyWorkSheet->setCellValue('H5', date('d ') . $currentMonthName[1] . date(' Y'));
        }
        $companyWorkSheet->getStyle('G5')->applyFromArray($styleArray);

        // Первая таблица

        $companyWorkSheet->getRowDimension(8)->setRowHeight(-1);
        $companyWorkSheet->getRowDimension(9)->setRowHeight(-1);
        $companyWorkSheet->mergeCells('B8:I8');
        $companyWorkSheet->setCellValue('B8', "1. Количество обслуженных машин");
        $companyWorkSheet->getStyle('B8')->applyFromArray($styleArray);

        //main values
        $rowStart = 10;
        $row = 10;
        $num = 0;
        $total = 0;
        $count = 0;

        $companyWorkSheet->getColumnDimension('A')->setWidth(7);
        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
        $companyWorkSheet->getColumnDimension('C')->setWidth(5);
        $companyWorkSheet->getColumnDimension('D')->setWidth(5);
        $companyWorkSheet->getColumnDimension('E')->setWidth(10);
        $companyWorkSheet->getColumnDimension('F')->setWidth(3);
        $companyWorkSheet->getColumnDimension('G')->setWidth(22);
        $companyWorkSheet->getColumnDimension('H')->setWidth(15);
        $companyWorkSheet->getColumnDimension('I')->setWidth(7);
        if($company->is_split) {
            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
        }

        $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
        $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

        // Запрос

        // Формирование параметров поиска
        $timeFrom = $this->time;

        $timeTo = $this->time + 3456000;
        $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
        // Формирование параметров поиска

        $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $company->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

        // Запрос

        $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
        /** @var Act $data */
        $currentId = 0;
        $isParent = false;
        if ($this->company && count($company->children) > 0) {
            $isParent = true;
        }

        // Количество обслуженных машин
        $numWorkCar = 0;
        $numBigWorkCar = 0;

        foreach ($resCars as $value) {
            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

            $numWorkCar += $value['carsCount'];

            if($value['actsCount'] > 2) {
                $numBigWorkCar += $value['carsCount'];
            }

        }

        $numCompanyCar = Car::find()->where(['company_id' => $company->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

        if((count($numCompanyCar) - $numWorkCar) > 0) {
            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
        }

        $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'color' => array('argb' => 'FF006699'),
                ),
            )
        );
        if($company->is_split) {
            $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
        }

        $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
            ->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                )
            );

        $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
        $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $rowStart++;

        $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
        $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $rowStart++;

        $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
        $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $rowStart++;

        if($this->serviceType == 2) {
            $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;
        }

        if(($rowStart - 1) > $row) {
            $row += (($rowStart - 1) - $row);
        }

        // END Первая таблица

        // Вторая таблица
        $row++;
        $row++;

        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
        $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

        //main values
        $row++; $row++;
        $rowStart = $row;
        $num = 0;
        $total = 0;
        $count = 0;

        $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

        // Запрос

        // Формирование параметров поиска
        $timeFrom = $this->time;

        $timeTo = $this->time + 3456000;
        $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
        // Формирование параметров поиска

        $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $company->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

        // Запрос

        $headers = ['Город', '', '', 'Кол-во операций'];
        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
        /** @var Act $data */
        $currentId = 0;
        $isParent = false;
        if ($this->company && count($company->children) > 0) {
            $isParent = true;
        }

        // Количество обслуженных машин
        $numWorkCar = 0;
        $arrCityWork = [];
        $z = 0;

        foreach ($resCity as $value) {
            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
            $column++;$column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

            $numWorkCar += $value['actsCount'];
            $arrCityWork[$z][0] = $resCompany[0];
            $arrCityWork[$z][1] = $value['actsCount'];
            $z++;

        }

        $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'color' => array('argb' => 'FF006699'),
                ),
            )
        );
        if($company->is_split) {
            $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
        }

        $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
            ->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                )
            );


        $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
        $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $rowStart++;

        $finishPerecnt = 0;

        for($z = 0; $z < count($arrCityWork); $z++) {
            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

            if(($z + 1) == count($arrCityWork)) {
                $percentGet = 100 - $finishPerecnt;
                $percentGet = number_format($percentGet, 2);
            } else {
                $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                $percentGet = number_format($percentGet, 2);
                $finishPerecnt += $percentGet;
            }

            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;
        }

        if(($rowStart - 1) > $row) {
            $row += (($rowStart - 1) - $row);
        }

        // END Вторая таблица

        // Третья таблица
        $row++;
        $row++;

        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
        $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

        //main values
        $row++; $row++;
        $rowStart = $row;
        $num = 0;
        $total = 0;
        $count = 0;

        $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
        $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
        $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
        $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
        $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

        // Запрос

        // Формирование параметров поиска
        $timeFrom = $this->time;

        $timeTo = $this->time + 3456000;
        $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
        // Формирование параметров поиска

        $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $company->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

        // Запрос

        $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
        /** @var Act $data */
        $currentId = 0;
        $isParent = false;
        if ($this->company && count($company->children) > 0) {
            $isParent = true;
        }

        foreach ($resStat as $value) {
            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] .  ' AND type_id != 7 AND type_id !=8')->all()));
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

        }

        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'color' => array('argb' => 'FF006699'),
                ),
            )
        );
        if($company->is_split) {
            $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
        }

        $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
            ->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                )
            );

        // END Третья таблица


        // Четвертая таблица
        $row++;
        $row++;

        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
        $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

        //main values
        $row++; $row++;
        $rowStart = $row;
        $num = 0;
        $total = 0;
        $count = 0;

        $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
        $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
        $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

        $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
        /** @var Act $data */
        $currentId = 0;
        $isParent = false;
        if ($this->company && count($company->children) > 0) {
            $isParent = true;
        }

        $firstIncome = 0;

        // Позапрошлый месяц

        // Запрос

        // Формирование параметров поиска
        /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
        $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
        $timeFrom .= ".000Z";

        $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
        $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
        $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
        $timeTo = date("Y-m-dT21:00:00", $timeTo);
        $timeTo .= ".000Z";*/
        // Формирование параметров поиска

        //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $company->id . ") OR (`client_id`=" . $company->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

        // Запрос

        /*$row++;
        $num++;
        $column = 1;

        $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
        $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
        $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
        $column++;

        $incomeVal = 0;
        $percentDelta = 0 . "%";

        $incomeVals = 0;

        for ($i = 0; $i < count($resIncome); $i++) {
            if(isset($resIncome[$i]['income'])) {
                $incomeVals = $resIncome[$i]['income'];
            }
        }

        if($incomeVals > 0) {
            $incomeVal = $incomeVals;
            $firstIncome = $incomeVal;
            $percentDelta = "100%";
        }

        $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

        $column++; $column++;
        $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

        $companyWorkSheet->getStyle('F' . $row)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $companyWorkSheet->getStyle('G' . $row)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

        // End Позапрошлый месяц

        // Предыдущий месяц

        // Запрос

        // Формирование параметров поиска
        $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
        $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
        $timeFrom .= ".000Z";

        $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
        $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
        $timeTo .= ".000Z";
        // Формирование параметров поиска

        $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $company->id . ") OR (`client_id`=" . $company->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

        // Запрос

        $row++;
        $num++;
        $column = 1;

        $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
        $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
        $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
        $column++;

        $incomeVal = 0;
        $percentDelta = 0 . "%";

        $incomeVals = 0;

        for ($i = 0; $i < count($resIncome); $i++) {
            if(isset($resIncome[$i]['income'])) {
                $incomeVals = $resIncome[$i]['income'];
            }
        }

        if($incomeVals > 0) {
            $incomeVal = $incomeVals;

            if($firstIncome == 0) {
                $firstIncome = $incomeVal;
                $percentDelta = 100 . "%";
            } else {

                if($firstIncome > $incomeVal) {
                    $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                    $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                } else if($firstIncome < $incomeVal) {
                    $percentDelta = $incomeVal / ($firstIncome / 100);
                    $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                }

            }

        }

        $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

        $column++; $column++;
        $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

        $companyWorkSheet->getStyle('F' . $row)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $companyWorkSheet->getStyle('G' . $row)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // End Предыдущий месяц

        // Текущий месяц

        // Запрос

        // Формирование параметров поиска
        $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
        $timeFrom .= ".000Z";

        $timeTo = $this->time + 3456000;
        $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
        $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
        $timeTo .= ".000Z";
        // Формирование параметров поиска

        $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $company->id . ") OR (`client_id`=" . $company->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

        // Запрос

        $row++;
        $num++;
        $column = 1;

        $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
        $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
        $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
        $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
        $column++;

        $incomeVal = 0;
        $percentDelta = 0 . "%";

        $incomeVals = 0;

        for ($i = 0; $i < count($resIncome); $i++) {
            if(isset($resIncome[$i]['income'])) {
                $incomeVals = $resIncome[$i]['income'];
            }
        }

        if($incomeVals > 0) {
            $incomeVal = $incomeVals;

            if($firstIncome == 0) {
                $firstIncome = $incomeVal;
                $percentDelta = 100 . "%";
            } else {

                if($firstIncome > $incomeVal) {
                    $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                    $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                } else if($firstIncome < $incomeVal) {
                    $percentDelta = $incomeVal / ($firstIncome / 100);
                    $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                }

            }

        }

        $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

        $column++; $column++;
        $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

        $companyWorkSheet->getStyle('F' . $row)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $companyWorkSheet->getStyle('G' . $row)
            ->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'color' => array('argb' => 'FF006699'),
                ),
            )
        );

        // End Текущий месяц

        $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                'font' => array(
                    'bold' => true,
                    'color' => array('argb' => 'FF006699'),
                ),
            )
        );
        if($company->is_split) {
            $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
        }

        $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
            ->applyFromArray(array(
                    'borders' => array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('argb' => 'FF000000'),
                        ),
                    ),
                )
            );

        // END Четвертая таблица

        //footer

        $row++; $row++; $row++;
        $companyWorkSheet->getRowDimension($row)->setRowHeight(35);
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValue('B' . $row . '', "Более подробную информацию Вы можете посмотреть в своем личном кабинете, на сайте http://docs.mtransservice.ru");

        $row++;
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->getRowDimension($row)->setRowHeight(35);
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValue('B' . $row . '', "При возникновении вопросов, Вы всегда можете связаться с персональным менеджером нашей компании.");

        $row++; $row++;
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "С Уважением,");

        $row++;
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "Международный Транспортный Сервис");

        $companyWorkSheet->setBreak( "A$row" , PHPExcel_Worksheet::BREAK_ROW );
        $companyWorkSheet->setBreak( "J$row" , PHPExcel_Worksheet::BREAK_COLUMN );

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::TYPE_SERVICE) {
            $first = $dataList[0];
            $filename = "Статистика_анализ_" . $textService . " {$company->name} - {$first->car_number} - {$first->id} от " . date('d-m-Y', $first->served_at) . ".xls";
        } else {
            $filename = $serviceDescription. " Статистика_анализ_" . $textService . " $company->name от " . date('m-Y', $this->time) . ".xls";
        }
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));

    }

    /**
     * @param Company $company
     * @param ZipArchive $zip
     */
    private function generateMFPAct($company, $thisDp = false, &$zip)
    {

        $dataMfpTmp = [];
        $fileNamePre = '';

        if($thisDp) {
            $dataMfpTmp = $this->arrMFPidsDp;
            $fileNamePre = 'доп. дезинфекция';
        } else {
            $dataMfpTmp = $this->arrMFPids;
            if($this->serviceType == Company::TYPE_DISINFECT) {
                $fileNamePre = 'дезинфекция';
            }
        }

        // Мета объект
        $companyMain = '';

        if((isset($dataMfpTmp[154][0])) && (isset($dataMfpTmp[154][1]))) {
            if(count($dataMfpTmp[154][1]) > 0) {
                $companyMain = $dataMfpTmp[154][0];
            }
        } else if((isset($dataMfpTmp[849][0])) && (isset($dataMfpTmp[849][1]))) {
            if(count($dataMfpTmp[849][1]) > 0) {
                $companyMain = $dataMfpTmp[849][0];
            }
        } else if((isset($dataMfpTmp[850][0])) && (isset($dataMfpTmp[850][1]))) {
            if(count($dataMfpTmp[850][1]) > 0) {
                $companyMain = $dataMfpTmp[850][0];
            }
        } else if((isset($dataMfpTmp[851][0])) && (isset($dataMfpTmp[851][1]))) {
            if(count($dataMfpTmp[851][1]) > 0) {
                $companyMain = $dataMfpTmp[851][0];
            }
        } else if((isset($dataMfpTmp[852][0])) && (isset($dataMfpTmp[852][1]))) {
            if(count($dataMfpTmp[852][1]) > 0) {
                $companyMain = $dataMfpTmp[852][0];
            }
        } else if((isset($dataMfpTmp[900][0])) && (isset($dataMfpTmp[900][1]))) {
            if(count($dataMfpTmp[900][1]) > 0) {
                $companyMain = $dataMfpTmp[900][0];
            }
        }

        $this->objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        // Creating a workbook
        $this->objPHPExcel->getProperties()->setCreator('Mtransservice');
        $this->objPHPExcel->getProperties()->setTitle('Акт');
        $this->objPHPExcel->getProperties()->setSubject('Акт');
        $this->objPHPExcel->getProperties()->setDescription('');
        $this->objPHPExcel->getProperties()->setCategory('');
        $this->objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'акт');
        $this->objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getPageMargins()->setTop(2);
        $companyWorkSheet->getPageMargins()->setLeft(0.5);
        $companyWorkSheet->getRowDimension(1)->setRowHeight(1);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers;
        $monthName = DateHelper::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));
        $currentMonthName = DateHelper::getMonthName($date->getTimestamp());

        // Высчитываем номер для акта и счета
        $fileNameCheck = '';
        $numberAct = '';
        $dataExpl = date('m-Y', $this->time);

        $fileNameCheck = $fileNamePre . " Акт ООО Агро-Авто (Москва ЮГ - МФП) от " . date('m-Y', $this->time) . ".xls";
        $fileNameCheck = str_replace('"', '', $fileNameCheck);
        $fileNameCheck = str_replace(' ', '_', $fileNameCheck);

        $companyInt = 0;

        if($this->company) {
            $companyInt = 1;
        }

        if($this->checkDoQueryNumber == false) {
            $this->arrActNumbers = ActData::find()->where(['type' => $this->serviceType, 'company' => $companyInt, 'period' => $dataExpl])->select('number, name')->all();
            $this->checkDoQueryNumber = true;
        }

        if (count($this->arrActNumbers) > 0) {

            for($i = 0; $i < count($this->arrActNumbers); $i++) {
                if($this->arrActNumbers[$i]['name'] == $fileNameCheck) {
                    $numberAct = $this->arrActNumbers[$i]['number'];
                }
            }

        }

        if($numberAct == '') {
            $newActData = new ActData();
            $newActData->type = $this->serviceType;
            $newActData->company = $companyInt;
            $newActData->period = $dataExpl;
            $newActData->name = $fileNameCheck;

            if($newActData->save()) {

                if($fileNamePre == 'доп. дезинфекция') {
                    $numberAct = 'DD' . $newActData->id;
                } else {
                    switch ($this->serviceType) {
                        case 2:
                            $numberAct = 'M' . $newActData->id;
                            break;
                        case 3:
                            $numberAct = 'S' . $newActData->id;
                            break;
                        case 4:
                            $numberAct = 'T' . $newActData->id;
                            break;
                        case 5:
                            $numberAct = 'D' . $newActData->id;
                            break;
                    }
                }

                $newActData->number = $numberAct;
                $newActData->save();

            }

        }

        // Высчитываем номер для акта и счета

        $row = 8;

        if ($this->serviceType == Company::TYPE_DISINFECT) {
            $companyWorkSheet->getStyle('B2:F4')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $companyWorkSheet->mergeCells('B2:F2');
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)" . " № " . $numberAct;
            $companyWorkSheet->setCellValue('B2', $text);
            $companyWorkSheet->mergeCells('B3:F3');
            $text = "по договору на оказание услуг " . $companyMain->getRequisitesByType($this->serviceType, 'contract');
            $companyWorkSheet->setCellValue('B3', $text);
            $companyWorkSheet->mergeCells('B4:F4');
            $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
            $companyWorkSheet->setCellValue('B4', $text);

            $companyWorkSheet->setCellValue('B5', 'г.Воронеж');
            $companyWorkSheet->getStyle('E5')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                )
            ));
            if ($this->company) {
                $companyWorkSheet->setCellValue('F5', '1 ' . $monthName[1] . date(' Y', $this->time));
            } else {
                $companyWorkSheet->setCellValue('F5', date('d ') . $currentMonthName[1] . date(' Y'));
            }

            $companyWorkSheet->mergeCells('B' . $row . ':F' . $row);
            $companyWorkSheet->mergeCells('B7:F7');
            if ($this->company) {
                $companyWorkSheet->setCellValue('B' . $row, "Исполнитель: ООО «Международный Транспортный Сервис»");
                $companyWorkSheet->setCellValue('B7', "Заказчик: ООО Агро-Авто (Москва ЮГ - МФП)");

                $row++;

                $companyWorkSheet->mergeCells('B' . $row . ':F' . $row);
                $companyWorkSheet->setCellValue('B' . $row, "Лицензия № ЛО-36-01-002839");

            } else {
                $companyWorkSheet->setCellValue('B7', "Исполнитель: ООО Агро-Авто (Москва ЮГ - МФП)");
                $companyWorkSheet->setCellValue('B' . $row, "Заказчик: ООО «Международный Транспортный Сервис»");
            }

            $row++; $row++;


            $companyWorkSheet->getRowDimension($row)->setRowHeight(100);

            $companyWorkSheet->mergeCells('B' . $row . ':F' . $row);
            $companyWorkSheet->getStyle('B' . $row . ':F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('B' . $row . ':F' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                )
            ));
            $companyWorkSheet->setCellValue('B' . $row, $companyMain->getRequisitesByType($this->serviceType, 'header'));
        } else {
            $companyWorkSheet->getStyle('B2:I4')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $companyWorkSheet->mergeCells('B2:I2');
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells('B2:J2');
            }
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)" . " № " . $numberAct;
            $companyWorkSheet->setCellValue('B2', $text);
            $companyWorkSheet->mergeCells('B3:I3');
            $text = "по договору на оказание услуг " . $companyMain->getRequisitesByType($this->serviceType, 'contract');
            $companyWorkSheet->setCellValue('B3', $text);
            $companyWorkSheet->mergeCells('B4:I4');
            $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
            $companyWorkSheet->setCellValue('B4', $text);

            $companyWorkSheet->setCellValue('B5', 'г.Воронеж');
            $companyWorkSheet->getStyle('H5:I5')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                )
            ));
            $companyWorkSheet->mergeCells('H5:I5');
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells('H5:J5');
            }
            if ($this->company) {
                $companyWorkSheet->setCellValue('H5', date("t ", $this->time) . $monthName[1] . date(' Y', $this->time));
            } else {
                $companyWorkSheet->setCellValue('H5', date('d ') . $currentMonthName[1] . date(' Y'));
            }

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row);
            $companyWorkSheet->mergeCells('B7:I7');
            if ($this->company) {
                $companyWorkSheet->setCellValue('B' . $row, "Исполнитель: ООО «Международный Транспортный Сервис»");
                $companyWorkSheet->setCellValue('B7', "Заказчик: ООО Агро-Авто (Москва ЮГ - МФП)");
            } else {
                $companyWorkSheet->setCellValue('B7', "Исполнитель: ООО Агро-Авто (Москва ЮГ - МФП)");
                $companyWorkSheet->setCellValue('B' . $row, "Заказчик: ООО «Международный Транспортный Сервис»");
            }

            $row++; $row++;


            $companyWorkSheet->getRowDimension($row)->setRowHeight(100);
            $companyWorkSheet->mergeCells('B' . $row .':I' . $row);
            $companyWorkSheet->getStyle('B' . $row .':I' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('B' . $row .':I' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                )
            ));
            $companyWorkSheet->setCellValue('B' . $row, $companyMain->getRequisitesByType($this->serviceType, 'header'));
        }

        $row++; $row++;

        //main values
        $rowStart = 0;
        $num = 0;
        $total = 0;
        $count = 0;
        $totalAll = 0;
        $countAll = 0;

        $numMFPtable = 0;

        // МФП (Основной)
        if((isset($dataMfpTmp[154][0])) && (isset($dataMfpTmp[154][1]))) {
            if (count($dataMfpTmp[154][1]) > 0) {

                $numMFPtable++;
                $num = 0;

                // Заголовок таблицы
                $companyWorkSheet->mergeCells("B$row:F$row");
                $companyWorkSheet->setCellValue("B$row", 'ООО Агро-Авто (Москва ЮГ - МФП)');
                $row++;
                $rowStart = $row;
                $checkService = false;

                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $checkService = true;

                        $dataList = $dataMfpTmp[154][1];
                        if ($dataList) {
                            foreach ($dataList as $data) {
                                $dataList = array($data);
                            }
                        }

                        $count = count($dataList);

                        $first = $dataList[0];
                        $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
                    case Company::TYPE_TIRES:

                        $dataList = $dataMfpTmp[154][1];

                        $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        ));
                        $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                        /** @var Act $data */
                        foreach ($dataList as $data) {
                            $row++;
                            $num = 0;

                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                            $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                            if ($this->company) {
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                        'size' => 12,
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                            $row++;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                            if($checkService == false) {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    $row++;
                                    $num++;
                                    $companyWorkSheet->mergeCells("B$row:F$row");
                                    $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    if (mb_strlen($scope->description) > 30) {
                                        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                    }
                                    $companyWorkSheet->setCellValue("G$row", $scope->price);
                                    $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                    $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                    $total += $scope->amount * $scope->price;
                                    $subtotal += $scope->amount * $scope->price;
                                    $subcount += $scope->amount;
                                    $count += $scope->amount;
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            } else {


                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Запасные части");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 1) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Услуги");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 0) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            }

                        }
                        break;

                    case Company::TYPE_WASH:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                        if ($dataMfpTmp[154][0]->is_split) {
                            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                        }

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[154][1];

                        $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                        if ($dataMfpTmp[154][0]->is_split) {
                            $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                        }
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[154][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            if ($dataMfpTmp[154][0]->is_split) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                            }

                            if ($this->company) {
                                $services = [];
                                foreach ($data->clientScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $services = [];
                                foreach ($data->partnerScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                        if ($dataMfpTmp[154][0]->is_split) {
                            $companyWorkSheet->getStyle('J' . $rowStart)->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );
                        }

                        $companyWorkSheet->getStyle("B$rowStart:I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );
                        if ($dataMfpTmp[154][0]->is_split) {
                            $companyWorkSheet->getStyle("J$rowStart:J$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                    )
                                );
                        }

                        $count = count($dataList);

                        break;

                    case Company::TYPE_DISINFECT:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[154][1];

                        $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[154][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':F' . $rowStart)
                            ->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );

                        $companyWorkSheet->getStyle("B$rowStart:F$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                        $count = count($dataList);

                        break;
                }

                $totalAll += $total;
                $countAll += $count;

                // Footer table
                if ($this->serviceType == Company::TYPE_DISINFECT) {
                    $row++;
                    $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
                    $companyWorkSheet->setCellValue("F$row", "$total");
                } else {
                    $row++;
                    if ($this->serviceType == Company::TYPE_WASH) {
                        if ($dataMfpTmp[154][0]->is_split) {
                            $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("I$row", "$total");
                        } else {
                            $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("H$row", "$total");
                        }
                    } else {
                        $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                        $companyWorkSheet->setCellValue("G$row", "$total");
                        $companyWorkSheet->setCellValue("H$row", "$count");
                        $companyWorkSheet->setCellValue("I$row", "$total");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],
                            ]
                        );
                    }
                }
                // Footer table

            }
        }
        // END МФП (Основной)


        // МФП 1
        if((isset($dataMfpTmp[900][0])) && (isset($dataMfpTmp[900][1]))) {
            if (count($dataMfpTmp[900][1]) > 0) {

                $num = 0;
                $total = 0;
                $count = 0;

                if ($numMFPtable != 0) {
                    $row++;
                    $row++;
                }

                $numMFPtable++;

                // Заголовок таблицы
                $companyWorkSheet->mergeCells("B$row:F$row");
                $companyWorkSheet->setCellValue("B$row", 'ООО "Агро-Авто" (Москва ЮГ - МФП 1)');
                $row++;
                $rowStart = $row;
                $checkService = false;

                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $checkService = true;

                        $dataList = $dataMfpTmp[900][1];
                        if ($dataList) {
                            foreach ($dataList as $data) {
                                $dataList = array($data);
                            }
                        }

                        $count = count($dataList);

                        $first = $dataList[0];
                        $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
                    case Company::TYPE_TIRES:

                        $dataList = $dataMfpTmp[900][1];

                        $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        ));
                        $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                        /** @var Act $data */
                        foreach ($dataList as $data) {
                            $row++;
                            $num = 0;

                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                            $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                            if ($this->company) {
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                        'size' => 12,
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                            $row++;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                            if($checkService == false) {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    $row++;
                                    $num++;
                                    $companyWorkSheet->mergeCells("B$row:F$row");
                                    $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    if (mb_strlen($scope->description) > 30) {
                                        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                    }
                                    $companyWorkSheet->setCellValue("G$row", $scope->price);
                                    $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                    $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                    $total += $scope->amount * $scope->price;
                                    $subtotal += $scope->amount * $scope->price;
                                    $subcount += $scope->amount;
                                    $count += $scope->amount;
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            } else {
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Запасные части");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 1) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Услуги");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 0) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );
                            }

                        }
                        break;

                    case Company::TYPE_WASH:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                        if ($dataMfpTmp[900][0]->is_split) {
                            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                        }

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[900][1];

                        $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                        if ($dataMfpTmp[900][0]->is_split) {
                            $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                        }
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[900][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            if ($dataMfpTmp[900][0]->is_split) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                            }

                            if ($this->company) {
                                $services = [];
                                foreach ($data->clientScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $services = [];
                                foreach ($data->partnerScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                        if ($dataMfpTmp[900][0]->is_split) {
                            $companyWorkSheet->getStyle('J' . $rowStart)->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );
                        }

                        $companyWorkSheet->getStyle("B$rowStart:I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );
                        if ($dataMfpTmp[900][0]->is_split) {
                            $companyWorkSheet->getStyle("J$rowStart:J$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                    )
                                );
                        }

                        $count = count($dataList);

                        break;

                    case Company::TYPE_DISINFECT:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[900][1];

                        $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[900][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':F' . $rowStart)
                            ->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );

                        $companyWorkSheet->getStyle("B$rowStart:F$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                        $count = count($dataList);

                        break;
                }

                $totalAll += $total;
                $countAll += $count;

                // Footer table
                if ($this->serviceType == Company::TYPE_DISINFECT) {
                    $row++;
                    $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
                    $companyWorkSheet->setCellValue("F$row", "$total");
                } else {
                    $row++;
                    if ($this->serviceType == Company::TYPE_WASH) {
                        if ($dataMfpTmp[900][0]->is_split) {
                            $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("I$row", "$total");
                        } else {
                            $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("H$row", "$total");
                        }
                    } else {
                        $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                        $companyWorkSheet->setCellValue("G$row", "$total");
                        $companyWorkSheet->setCellValue("H$row", "$count");
                        $companyWorkSheet->setCellValue("I$row", "$total");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],
                            ]
                        );
                    }
                }
                // Footer table

            }
        }
        // END МФП 1

        // МФП 2
        if((isset($dataMfpTmp[849][0])) && (isset($dataMfpTmp[849][1]))) {
            if (count($dataMfpTmp[849][1]) > 0) {

                $num = 0;
                $total = 0;
                $count = 0;

                if ($numMFPtable != 0) {
                    $row++;
                    $row++;
                }

                $numMFPtable++;

                // Заголовок таблицы
                $companyWorkSheet->mergeCells("B$row:F$row");
                $companyWorkSheet->setCellValue("B$row", 'ООО "Агро-Авто" (Москва ЮГ - МФП 2)');
                $row++;
                $rowStart = $row;
                $checkService = false;

                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $checkService = true;

                        $dataList = $dataMfpTmp[849][1];
                        if ($dataList) {
                            foreach ($dataList as $data) {
                                $dataList = array($data);
                            }
                        }

                        $count = count($dataList);

                        $first = $dataList[0];
                        $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
                    case Company::TYPE_TIRES:

                        $dataList = $dataMfpTmp[849][1];

                        $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        ));
                        $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                        /** @var Act $data */
                        foreach ($dataList as $data) {
                            $row++;
                            $num = 0;

                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                            $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                            if ($this->company) {
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                        'size' => 12,
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                            $row++;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                            if($checkService == false) {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    $row++;
                                    $num++;
                                    $companyWorkSheet->mergeCells("B$row:F$row");
                                    $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    if (mb_strlen($scope->description) > 30) {
                                        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                    }
                                    $companyWorkSheet->setCellValue("G$row", $scope->price);
                                    $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                    $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                    $total += $scope->amount * $scope->price;
                                    $subtotal += $scope->amount * $scope->price;
                                    $subcount += $scope->amount;
                                    $count += $scope->amount;
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            } else {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Запасные части");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 1) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Услуги");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 0) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );
                            }

                        }
                        break;

                    case Company::TYPE_WASH:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                        if ($dataMfpTmp[849][0]->is_split) {
                            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                        }

                        $dataList = $dataMfpTmp[849][1];

                        $rowStart++; $row++;

                        $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                        if ($dataMfpTmp[849][0]->is_split) {
                            $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                        }
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[849][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            if ($dataMfpTmp[849][0]->is_split) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                            }

                            if ($this->company) {
                                $services = [];
                                foreach ($data->clientScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $services = [];
                                foreach ($data->partnerScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                        if ($dataMfpTmp[849][0]->is_split) {
                            $companyWorkSheet->getStyle('J' . $rowStart)->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );
                        }

                        $companyWorkSheet->getStyle("B$rowStart:I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );
                        if ($dataMfpTmp[849][0]->is_split) {
                            $companyWorkSheet->getStyle("J$rowStart:J$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                    )
                                );
                        }

                        $count = count($dataList);

                        break;

                    case Company::TYPE_DISINFECT:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[849][1];

                        $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[849][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':F' . $rowStart)
                            ->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );

                        $companyWorkSheet->getStyle("B$rowStart:F$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                        $count = count($dataList);

                        break;
                }

                $totalAll += $total;
                $countAll += $count;

                // Footer table
                if ($this->serviceType == Company::TYPE_DISINFECT) {
                    $row++;
                    $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
                    $companyWorkSheet->setCellValue("F$row", "$total");
                } else {
                    $row++;
                    if ($this->serviceType == Company::TYPE_WASH) {
                        if ($dataMfpTmp[849][0]->is_split) {
                            $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("I$row", "$total");
                        } else {
                            $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("H$row", "$total");
                        }
                    } else {
                        $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                        $companyWorkSheet->setCellValue("G$row", "$total");
                        $companyWorkSheet->setCellValue("H$row", "$count");
                        $companyWorkSheet->setCellValue("I$row", "$total");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],
                            ]
                        );
                    }
                }
                // Footer table

            }
        }
        // END МФП 2

        // МФП 3
        if((isset($dataMfpTmp[850][0])) && (isset($dataMfpTmp[850][1]))) {
            if (count($dataMfpTmp[850][1]) > 0) {

                $num = 0;
                $total = 0;
                $count = 0;

                if ($numMFPtable != 0) {
                    $row++;
                    $row++;
                }

                $numMFPtable++;

                // Заголовок таблицы
                $companyWorkSheet->mergeCells("B$row:F$row");
                $companyWorkSheet->setCellValue("B$row", 'ООО "Агро-Авто" (Москва ЮГ - МФП 3)');
                $row++;
                $rowStart = $row;
                $checkService = false;

                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $checkService = true;

                        $dataList = $dataMfpTmp[850][1];
                        if ($dataList) {
                            foreach ($dataList as $data) {
                                $dataList = array($data);
                            }
                        }

                        $count = count($dataList);

                        $first = $dataList[0];
                        $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
                    case Company::TYPE_TIRES:

                        $dataList = $dataMfpTmp[850][1];

                        $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        ));
                        $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                        /** @var Act $data */
                        foreach ($dataList as $data) {
                            $row++;
                            $num = 0;

                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                            $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                            if ($this->company) {
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                        'size' => 12,
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                            $row++;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                            if($checkService == false) {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    $row++;
                                    $num++;
                                    $companyWorkSheet->mergeCells("B$row:F$row");
                                    $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    if (mb_strlen($scope->description) > 30) {
                                        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                    }
                                    $companyWorkSheet->setCellValue("G$row", $scope->price);
                                    $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                    $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                    $total += $scope->amount * $scope->price;
                                    $subtotal += $scope->amount * $scope->price;
                                    $subcount += $scope->amount;
                                    $count += $scope->amount;
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            } else {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Запасные части");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 1) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Услуги");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 0) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );
                            }

                        }
                        break;

                    case Company::TYPE_WASH:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                        if ($dataMfpTmp[850][0]->is_split) {
                            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                        }

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[850][1];

                        $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                        if ($dataMfpTmp[850][0]->is_split) {
                            $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                        }
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[850][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            if ($dataMfpTmp[850][0]->is_split) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                            }

                            if ($this->company) {
                                $services = [];
                                foreach ($data->clientScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $services = [];
                                foreach ($data->partnerScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                        if ($dataMfpTmp[850][0]->is_split) {
                            $companyWorkSheet->getStyle('J' . $rowStart)->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );
                        }

                        $companyWorkSheet->getStyle("B$rowStart:I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );
                        if ($dataMfpTmp[850][0]->is_split) {
                            $companyWorkSheet->getStyle("J$rowStart:J$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                    )
                                );
                        }

                        $count = count($dataList);

                        break;

                    case Company::TYPE_DISINFECT:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[850][1];

                        $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[850][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':F' . $rowStart)
                            ->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );

                        $companyWorkSheet->getStyle("B$rowStart:F$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                        $count = count($dataList);

                        break;
                }

                $totalAll += $total;
                $countAll += $count;

                // Footer table
                if ($this->serviceType == Company::TYPE_DISINFECT) {
                    $row++;
                    $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
                    $companyWorkSheet->setCellValue("F$row", "$total");
                } else {
                    $row++;
                    if ($this->serviceType == Company::TYPE_WASH) {
                        if ($dataMfpTmp[850][0]->is_split) {
                            $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("I$row", "$total");
                        } else {
                            $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("H$row", "$total");
                        }
                    } else {
                        $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                        $companyWorkSheet->setCellValue("G$row", "$total");
                        $companyWorkSheet->setCellValue("H$row", "$count");
                        $companyWorkSheet->setCellValue("I$row", "$total");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],
                            ]
                        );
                    }
                }
                // Footer table

            }
        }
        // END МФП 3

        // МФП 4
        if((isset($dataMfpTmp[851][0])) && (isset($dataMfpTmp[851][1]))) {
            if (count($dataMfpTmp[851][1]) > 0) {

                $num = 0;
                $total = 0;
                $count = 0;

                if ($numMFPtable != 0) {
                    $row++;
                    $row++;
                }

                $numMFPtable++;

                // Заголовок таблицы
                $companyWorkSheet->mergeCells("B$row:F$row");
                $companyWorkSheet->setCellValue("B$row", 'ООО "Агро-Авто" (Москва ЮГ - МФП 4)');
                $row++;
                $rowStart = $row;
                $checkService = false;

                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $checkService = true;

                        $dataList = $dataMfpTmp[851][1];
                        if ($dataList) {
                            foreach ($dataList as $data) {
                                $dataList = array($data);
                            }
                        }

                        $count = count($dataList);

                        $first = $dataList[0];
                        $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
                    case Company::TYPE_TIRES:

                        $dataList = $dataMfpTmp[851][1];

                        $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        ));
                        $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                        /** @var Act $data */
                        foreach ($dataList as $data) {
                            $row++;
                            $num = 0;

                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                            $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                            if ($this->company) {
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                        'size' => 12,
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                            $row++;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                            if($checkService == false) {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    $row++;
                                    $num++;
                                    $companyWorkSheet->mergeCells("B$row:F$row");
                                    $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    if (mb_strlen($scope->description) > 30) {
                                        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                    }
                                    $companyWorkSheet->setCellValue("G$row", $scope->price);
                                    $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                    $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                    $total += $scope->amount * $scope->price;
                                    $subtotal += $scope->amount * $scope->price;
                                    $subcount += $scope->amount;
                                    $count += $scope->amount;
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            } else {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Запасные части");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 1) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Услуги");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 0) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );
                            }

                        }
                        break;

                    case Company::TYPE_WASH:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                        if ($dataMfpTmp[851][0]->is_split) {
                            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                        }

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[851][1];

                        $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                        if ($dataMfpTmp[851][0]->is_split) {
                            $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                        }
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[851][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            if ($dataMfpTmp[851][0]->is_split) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                            }

                            if ($this->company) {
                                $services = [];
                                foreach ($data->clientScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $services = [];
                                foreach ($data->partnerScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                        if ($dataMfpTmp[851][0]->is_split) {
                            $companyWorkSheet->getStyle('J' . $rowStart)->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );
                        }

                        $companyWorkSheet->getStyle("B$rowStart:I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );
                        if ($dataMfpTmp[851][0]->is_split) {
                            $companyWorkSheet->getStyle("J$rowStart:J$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                    )
                                );
                        }

                        $count = count($dataList);

                        break;

                    case Company::TYPE_DISINFECT:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[851][1];

                        $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[851][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':F' . $rowStart)
                            ->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );

                        $companyWorkSheet->getStyle("B$rowStart:F$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                        $count = count($dataList);

                        break;
                }

                $totalAll += $total;
                $countAll += $count;

                // Footer table
                if ($this->serviceType == Company::TYPE_DISINFECT) {
                    $row++;
                    $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
                    $companyWorkSheet->setCellValue("F$row", "$total");
                } else {
                    $row++;
                    if ($this->serviceType == Company::TYPE_WASH) {
                        if ($dataMfpTmp[851][0]->is_split) {
                            $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("I$row", "$total");
                        } else {
                            $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("H$row", "$total");
                        }
                    } else {
                        $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                        $companyWorkSheet->setCellValue("G$row", "$total");
                        $companyWorkSheet->setCellValue("H$row", "$count");
                        $companyWorkSheet->setCellValue("I$row", "$total");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],
                            ]
                        );
                    }
                }
                // Footer table

            }
        }
        // END МФП 4

        // МФП 5
        if((isset($dataMfpTmp[852][0])) && (isset($dataMfpTmp[852][1]))) {
            if (count($dataMfpTmp[852][1]) > 0) {

                $num = 0;
                $total = 0;
                $count = 0;

                if ($numMFPtable != 0) {
                    $row++;
                    $row++;
                }

                $numMFPtable++;

                // Заголовок таблицы
                $companyWorkSheet->mergeCells("B$row:F$row");
                $companyWorkSheet->setCellValue("B$row", 'ООО "Агро-Авто" (Москва ЮГ - МФП 5)');
                $row++;
                $rowStart = $row;
                $checkService = false;

                switch ($this->serviceType) {
                    case Company::TYPE_SERVICE:
                        $checkService = true;

                        $dataList = $dataMfpTmp[852][1];
                        if ($dataList) {
                            foreach ($dataList as $data) {
                                $dataList = array($data);
                            }
                        }

                        $count = count($dataList);

                        $first = $dataList[0];
                        $companyWorkSheet->setCellValue('H5', date("d ", $first->served_at) . $monthName[1] . date(' Y', $this->time));
                    case Company::TYPE_TIRES:

                        $dataList = $dataMfpTmp[852][1];

                        $companyWorkSheet->getDefaultStyle()->applyFromArray(array(
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            )
                        ));
                        $companyWorkSheet->getColumnDimension('B')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('G')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('H')->setWidth(11);
                        $companyWorkSheet->getColumnDimension('I')->setWidth(11);

                        /** @var Act $data */
                        foreach ($dataList as $data) {
                            $row++;
                            $num = 0;

                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValue("B$row", "ЧИСЛО");
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValue("D$row", "№ КАРТЫ");
                            $companyWorkSheet->setCellValue("F$row", "МАРКА ТС");
                            if ($this->company) {
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValue("H$row", "ГОРОД");
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                        'size' => 12,
                                    ),
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                            $row++;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->mergeCells("B$row:C$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                            $companyWorkSheet->mergeCells("D$row:E$row");
                            $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                                $companyWorkSheet->mergeCells("H$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                            } else {
                                $companyWorkSheet->mergeCells("G$row:I$row");
                                $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->car_number);
                            }
                            $companyWorkSheet->getStyle("B$row:I$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                            if($checkService == false) {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    $row++;
                                    $num++;
                                    $companyWorkSheet->mergeCells("B$row:F$row");
                                    $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                    if (mb_strlen($scope->description) > 30) {
                                        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                    }
                                    $companyWorkSheet->setCellValue("G$row", $scope->price);
                                    $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                    $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                    $total += $scope->amount * $scope->price;
                                    $subtotal += $scope->amount * $scope->price;
                                    $subcount += $scope->amount;
                                    $count += $scope->amount;
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );

                            } else {

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Запасные части");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 1) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Услуги");
                                $companyWorkSheet->setCellValue("G$row", "Стоимость");
                                $companyWorkSheet->setCellValue("H$row", "Кол-во");
                                $companyWorkSheet->setCellValue("I$row", "Сумма");
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                            'color' => array('argb' => 'FF006699'),
                                        ),
                                    )
                                );

                                /** @var ActScope $scope */
                                $subtotal = 0;
                                $subcount = 0;
                                if ($this->company) {
                                    $listScope = $data->clientScopes;
                                } else {
                                    $listScope = $data->partnerScopes;
                                }
                                foreach ($listScope as $scope) {
                                    if($scope->parts == 0) {
                                        $row++;
                                        $num++;
                                        $companyWorkSheet->mergeCells("B$row:F$row");
                                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                                        if (mb_strlen($scope->description) > 30) {
                                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                                        }
                                        $companyWorkSheet->setCellValue("G$row", $scope->price);
                                        $companyWorkSheet->setCellValue("H$row", $scope->amount);
                                        $companyWorkSheet->setCellValue("I$row", $scope->price * $scope->amount);
                                        $total += $scope->amount * $scope->price;
                                        $subtotal += $scope->amount * $scope->price;
                                        $subcount += $scope->amount;
                                        $count += $scope->amount;
                                    }
                                }
                                $row++;
                                $companyWorkSheet->mergeCells("B$row:F$row");
                                $companyWorkSheet->setCellValue("B$row", "Итого:");
                                $companyWorkSheet->setCellValue("G$row", '');
                                $companyWorkSheet->setCellValue("H$row", $subcount);
                                $companyWorkSheet->setCellValue("I$row", $subtotal);
                                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                                        'font' => array(
                                            'bold' => true,
                                        ),
                                    )
                                );

                                $companyWorkSheet->getStyle("B" . ($rowStart + 1) . ":I$row")
                                    ->applyFromArray(array(
                                            'borders' => array(
                                                'allborders' => array(
                                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                    'color' => array('argb' => 'FF000000'),
                                                ),
                                            ),
                                        )
                                    );
                            }

                        }
                        break;

                    case Company::TYPE_WASH:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('D')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('E')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('F')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('G')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('H')->setAutoSize(true);
                        $companyWorkSheet->getColumnDimension('I')->setAutoSize(true);
                        if ($dataMfpTmp[852][0]->is_split) {
                            $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
                        }

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[852][1];

                        $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                        if ($dataMfpTmp[852][0]->is_split) {
                            $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                        }
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[852][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $date = new \DateTime();
                            $date->setTimestamp($data->served_at);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            if ($dataMfpTmp[852][0]->is_split) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_car_number);
                            }

                            if ($this->company) {
                                $services = [];
                                foreach ($data->clientScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $services = [];
                                foreach ($data->partnerScopes as $scope) {
                                    $services[] = $scope->description;
                                }
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Мойка ' . implode('+', $services));
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart)->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                        if ($dataMfpTmp[852][0]->is_split) {
                            $companyWorkSheet->getStyle('J' . $rowStart)->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );
                        }

                        $companyWorkSheet->getStyle("B$rowStart:I$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );
                        if ($dataMfpTmp[852][0]->is_split) {
                            $companyWorkSheet->getStyle("J$rowStart:J$row")
                                ->applyFromArray(array(
                                        'borders' => array(
                                            'allborders' => array(
                                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                                'color' => array('argb' => 'FF000000'),
                                            ),
                                        ),
                                    )
                                );
                        }

                        $count = count($dataList);

                        break;

                    case Company::TYPE_DISINFECT:
                        $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                        $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                        $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                        $rowStart++; $row++;

                        $dataList = $dataMfpTmp[852][1];

                        $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                        $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
                        /** @var Act $data */
                        $currentId = 0;
                        $isParent = false;
                        if ($this->company && count($dataMfpTmp[852][0]->children) > 0) {
                            $isParent = true;
                        }
                        foreach ($dataList as $data) {

                            $row++;
                            $num++;
                            $column = 1;
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->car_number);
                            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
                            if ($this->company) {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                                $total += $data->income;
                            } else {
                                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->expense);
                                $total += $data->expense;
                            }
                            $companyWorkSheet->getCellByColumnAndRow($column, $row)
                                ->getStyle()
                                ->getNumberFormat()
                                ->setFormatCode(
                                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                                );
                            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, ' ' . $data->check);
                        }

                        $companyWorkSheet->getStyle('B' . $rowStart . ':F' . $rowStart)
                            ->applyFromArray(array(
                                    'font' => array(
                                        'bold' => true,
                                        'color' => array('argb' => 'FF006699'),
                                    ),
                                )
                            );

                        $companyWorkSheet->getStyle("B$rowStart:F$row")
                            ->applyFromArray(array(
                                    'borders' => array(
                                        'allborders' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                                            'color' => array('argb' => 'FF000000'),
                                        ),
                                    ),
                                )
                            );

                        $count = count($dataList);

                        break;
                }

                $totalAll += $total;
                $countAll += $count;

                // Footer table
                if ($this->serviceType == Company::TYPE_DISINFECT) {
                    $row++;
                    $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
                    $companyWorkSheet->setCellValue("F$row", "$total");
                } else {
                    $row++;
                    if ($this->serviceType == Company::TYPE_WASH) {
                        if ($company->is_split) {
                            $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("I$row", "$total");
                        } else {
                            $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                            $companyWorkSheet->setCellValue("H$row", "$total");
                        }
                    } else {
                        $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                        $companyWorkSheet->setCellValue("G$row", "$total");
                        $companyWorkSheet->setCellValue("H$row", "$count");
                        $companyWorkSheet->setCellValue("I$row", "$total");
                        $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],
                            ]
                        );
                    }
                }
                // Footer table

            }
        }
        // END МФП 5

        //footer
        if ($this->serviceType == Company::TYPE_DISINFECT) {
            $row++;
            $companyWorkSheet->setCellValue("E$row", "ВСЕГО");
            $companyWorkSheet->setCellValue("F$row", "$totalAll");

            $row++;$row++;
            $companyWorkSheet->mergeCells("B$row:F$row");
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $totalAll (" . DigitHelper::num2str($totalAll) . ") рублей. НДС нет.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++;
            $companyWorkSheet->mergeCells("B$row:F$row");
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
            $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "Работу сдал");
            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", "Работу принял");

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "Исполнитель");
            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", "Заказчик");

            $row++;
            if($companyMain->is_act_sign == 1) {
                //подпись
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/sign.png');
                $objDrawing->setCoordinates("C$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(50);
                //печать
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/post.png');
                $objDrawing->setCoordinates("C$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(100);
            } else if($companyMain->is_act_sign == 2) {
                //подпись
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/sign.png');
                $objDrawing->setCoordinates("C$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(50);
            }

            $row++;
            $companyWorkSheet->setCellValue("B$row", "Петросян А.Р. ____________");
            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", $companyMain->director . " ____________");

            $row++;
            $row++;

            $companyWorkSheet->setCellValue("E$row", "М.П.");
        } else {
            $row++;
            if ($this->serviceType == Company::TYPE_WASH) {
                if($companyMain->is_split) {
                    $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                    $companyWorkSheet->setCellValue("I$row", "$totalAll");
                } else {
                    $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                    $companyWorkSheet->setCellValue("H$row", "$totalAll");
                }
            } else {
                $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                $companyWorkSheet->setCellValue("G$row", "$countAll");
                $companyWorkSheet->setCellValue("H$row", "$totalAll");
                $companyWorkSheet->setCellValue("I$row", "$totalAll");
                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                    ]
                );
            }

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:I$row");
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells("B$row:J$row");
            }
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $totalAll (" . DigitHelper::num2str($totalAll) . ") рублей. НДС нет.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++;
            $companyWorkSheet->mergeCells("B$row:I$row");
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells("B$row:J$row");
            }
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
            $companyWorkSheet->setCellValue("B$row", $text);

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            if ($this->company) {
                $companyWorkSheet->setCellValue("B$row", "Работу сдал");
                $companyWorkSheet->setCellValue("G$row", "Работу принял");
            } else {
                $companyWorkSheet->setCellValue("B$row", "Работу принял");
                $companyWorkSheet->setCellValue("G$row", "Работу сдал");
            }

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            if ($this->company) {
                $companyWorkSheet->setCellValue("B$row", "Исполнитель");
                $companyWorkSheet->setCellValue("G$row", "Заказчик");
            } else {
                $companyWorkSheet->setCellValue("B$row", "Заказчик");
                $companyWorkSheet->setCellValue("G$row", "Исполнитель");
            }

            $row++;
            //подпись
            if ($companyMain->is_act_sign == 1) {
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Sample image');
                $objDrawing->setDescription('Sample image');
                $objDrawing->setPath('images/sign.png');

                if ($this->serviceType == Company::TYPE_WASH) {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(50);
                } else {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(10);
                }
                //печать
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setPath('images/post.png');
                $objDrawing->setCoordinates("D$row");
                $objDrawing->setWorksheet($companyWorkSheet);
                $objDrawing->setOffsetX(30);
            } else if ($companyMain->is_act_sign == 2) {
                $objDrawing = new \PHPExcel_Worksheet_Drawing();
                $objDrawing->setName('Sample image');
                $objDrawing->setDescription('Sample image');
                $objDrawing->setPath('images/sign.png');

                if ($this->serviceType == Company::TYPE_WASH) {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(50);
                } else {
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($companyWorkSheet);
                    $objDrawing->setOffsetX(10);
                }
            }
            $row++;
            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($companyMain->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            $companyWorkSheet->setCellValue("B$row", "Петросян А.Р. ____________");
            $companyWorkSheet->setCellValue("G$row", $companyMain->director . " ____________");

            $row++; $row++;

            $companyWorkSheet->setCellValue("G$row", "М.П.");
        }

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }

        $filename = $fileNamePre . " Акт ООО Агро-Авто (Москва ЮГ - МФП) от " . date('m-Y', $this->time) . ".xls";

        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));

        if (!$this->company) {
            return;
        }


        ///////////// check
        $this->objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        // Creating a workbook
        $this->objPHPExcel->getProperties()->setCreator('Mtransservice');
        $this->objPHPExcel->getProperties()->setTitle('Счет');
        $this->objPHPExcel->getProperties()->setSubject('Счет');
        $this->objPHPExcel->getProperties()->setDescription('');
        $this->objPHPExcel->getProperties()->setCategory('');
        $this->objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Счет');
        $this->objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getRowDimension(1)->setRowHeight(100);
        $companyWorkSheet->getColumnDimension('A')->setWidth(5);
        $companyWorkSheet->getColumnDimension('B')->setWidth(20);
        $companyWorkSheet->getColumnDimension('C')->setWidth(20);
        $companyWorkSheet->getColumnDimension('D')->setWidth(10);
        $companyWorkSheet->getColumnDimension('E')->setWidth(30);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers
        $monthName = DateHelper::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));

        $companyWorkSheet->mergeCells('B2:E2');
        $companyWorkSheet->setCellValue('B2', "ООО «Международный Транспортный Сервис»");

        $companyWorkSheet->mergeCells('B3:E3');
        $companyWorkSheet->setCellValue('B3', "Адрес: 394065, г. Воронеж, ул. Героев Сибиряков, д. 24, оф. 116");

        $companyWorkSheet->getStyle("B5")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('B5', 'ИНН 366 510 0480');

        $companyWorkSheet->getStyle("C5")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('C5', 'КПП 366 501 001');

        $companyWorkSheet->mergeCells('B6:C6');
        $companyWorkSheet->getStyle("B6:C6")->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle("B6:C6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->getRowDimension(6)->setRowHeight(40);
        $companyWorkSheet->setCellValue('B6', 'Получатель:ООО«Международный Транспортный Сервис»');

        $companyWorkSheet->mergeCells('D5:D6');
        $companyWorkSheet->getStyle("D5:D6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );
        $companyWorkSheet->setCellValue('D5', 'Сч.№');

        $companyWorkSheet->mergeCells('E5:E6');
        $companyWorkSheet->getStyle("E5:E6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
                'alignment' => array(
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            )
        );
        $companyWorkSheet->getStyle('E5:E6')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $companyWorkSheet->setCellValue('E5', ' 40702810913000016607');

        $companyWorkSheet->mergeCells('B7:C8');
        $companyWorkSheet->getStyle("B7:C8")->getAlignment()->setWrapText(true);
        $companyWorkSheet->getStyle("B7:C8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('B7', 'Банк получателя: Центрально-Черноземный Банк ОАО «Сбербанк России»  г. Воронеж');

        $companyWorkSheet->getStyle("D7")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('D7', 'БИК');

        $companyWorkSheet->getStyle("E7")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->getStyle('E7')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $companyWorkSheet->setCellValue('E7', ' 042007681');

        $companyWorkSheet->getStyle("D8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->setCellValue('D8', 'К/сч.№');

        $companyWorkSheet->getStyle("E8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $companyWorkSheet->getStyle('E8')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $companyWorkSheet->setCellValue('E8', ' 30101810600000000681');

        $row = 9;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->getStyle("B$row:E$row")->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );

        // Высчитываем номер для акта и счета

        $fileNameCheck = $fileNamePre . " Счет ООО Агро-Авто (Москва ЮГ - МФП) от " . date('m-Y', $this->time) . ".xls";
        $fileNameCheck = str_replace('"', '', $fileNameCheck);
        $fileNameCheck = str_replace(' ', '_', $fileNameCheck);

        if($this->checkDoQueryNumber == false) {
            $this->arrActNumbers = ActData::find()->where(['type' => $this->serviceType, 'company' => $companyInt, 'period' => $dataExpl])->select('number, name')->all();
            $this->checkDoQueryNumber = true;
        }

        $checkHaveAct = false;

        if (count($this->arrActNumbers) > 0) {

            for($i = 0; $i < count($this->arrActNumbers); $i++) {
                if($this->arrActNumbers[$i]['name'] == $fileNameCheck) {
                    $checkHaveAct = true;
                }
            }

        }

        if($checkHaveAct == false) {
            $newActData = new ActData();
            $newActData->type = $this->serviceType;
            $newActData->company = $companyInt;
            $newActData->period = $dataExpl;
            $newActData->name = $fileNameCheck;
            $newActData->number = $numberAct;
            $newActData->save();
        }

        // Высчитываем номер для акта и счета

        if ($this->serviceType == Company::TYPE_DISINFECT) {

            if($fileNamePre == 'дезинфекция') {
                $text = "СЧЕТ" . " № " . $numberAct . " от 01" . ' ' . $monthName[1] . date(' Y', $this->time);
            } else {
                $text = "СЧЕТ" . " № " . $numberAct . " от " . date("t", $this->time) . ' ' . $monthName[1] . date(' Y', $this->time);
            }

        } else {
            $text = "СЧЕТ" . " № " . $numberAct . " от " . date("t", $this->time) . ' ' . $monthName[1] . date(' Y', $this->time);
        }

        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->getStyle("B$row:E$row")->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );
        $text = 'За услуги ' . Service::$listType[$this->serviceType]['in'] . ', оказанные в ' . $monthName[2] . date(' Y');
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $text = "Плательщик: ООО Агро-Авто (Москва ЮГ - МФП)";
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
        $companyWorkSheet->getStyle("B$row:E$row")->getAlignment()->setWrapText(true);
        $text = "Всего наименований " . $countAll . ", на сумму $totalAll (" . DigitHelper::num2str($totalAll) . "). НДС нет.";
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        if ($companyMain->is_act_sign == 1) {
            //печать
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setPath('images/post.png');
            $objDrawing->setCoordinates("C$row");
            $objDrawing->setWorksheet($companyWorkSheet);
            $objDrawing->setOffsetX(30);
            $row++;
            $row++;
            //подпись
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setPath('images/sign.png');
            $objDrawing->setCoordinates("B{$row}");
            $objDrawing->setWorksheet($companyWorkSheet);
            $objDrawing->setOffsetX(70);
            $row++;
        } else if ($companyMain->is_act_sign == 2) {
            $row++;
            //подпись
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setPath('images/sign.png');
            $objDrawing->setCoordinates("B{$row}");
            $objDrawing->setWorksheet($companyWorkSheet);
            $objDrawing->setOffsetX(70);
            $row++;
        } else {
            $row++;
            $row++;
            $row++;
        }
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->setCellValue("B$row", 'Петросян А.Р.__________');

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }

        $filename = $fileNamePre . " Счет ООО Агро-Авто (Москва ЮГ - МФП) от " . date('m-Y', $this->time) . ".xls";

        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));
    }

    /** Создание файла статистики и анализа для МФП
     * @param Company $company
     * @param array $dataList
     * @param ZipArchive $zip
     */
    private function generateStatMFP($company, &$zip, $serviceDescription = null)
    {

        // Мета объект
        $dataMfpTmp = $this->arrMFPids;
        $companyMain = '';
        $companyMainData = '';

        if (isset($dataMfpTmp[154][0])) {
            $companyMain = $dataMfpTmp[154][0];
            $companyMainData = $dataMfpTmp[154][1];
        } else if (isset($dataMfpTmp[849][0])) {
            $companyMain = $dataMfpTmp[849][0];
            $companyMainData = $dataMfpTmp[849][1];
        } else if (isset($dataMfpTmp[850][0])) {
            $companyMain = $dataMfpTmp[850][0];
            $companyMainData = $dataMfpTmp[850][1];
        } else if (isset($dataMfpTmp[851][0])) {
            $companyMain = $dataMfpTmp[851][0];
            $companyMainData = $dataMfpTmp[851][1];
        } else if (isset($dataMfpTmp[852][0])) {
            $companyMain = $dataMfpTmp[852][0];
            $companyMainData = $dataMfpTmp[852][1];
        } else if (isset($dataMfpTmp[900][0])) {
            $companyMain = $dataMfpTmp[900][0];
            $companyMainData = $dataMfpTmp[900][1];
        }

        $this->objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        // Creating a workbook
        $this->objPHPExcel->getProperties()->setCreator('Mtransservice');
        $this->objPHPExcel->getProperties()->setTitle('Акт');
        $this->objPHPExcel->getProperties()->setSubject('Акт');
        $this->objPHPExcel->getProperties()->setDescription('');
        $this->objPHPExcel->getProperties()->setCategory('');
        $this->objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($this->objPHPExcel, 'акт');
        $this->objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getPageMargins()->setTop(2);
        $companyWorkSheet->getPageMargins()->setLeft(0.5);
        $companyWorkSheet->getRowDimension(1)->setRowHeight(1);
        $companyWorkSheet->getRowDimension(10)->setRowHeight(100);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers;
        $monthName = DateHelper::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));
        $currentMonthName = DateHelper::getMonthName($date->getTimestamp());

        $companyWorkSheet->getStyle('B2:I4')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        ));
        $companyWorkSheet->mergeCells('B2:I2');
        if($companyMain->is_split) {
            $companyWorkSheet->mergeCells('B2:J2');
        }

        $textService = '';
        $textTitle = '';

        switch ($this->serviceType) {
            case 2:
                $textService = "мойка";
                $textTitle = "мойке";
                break;
            case 3:
                $textService = "сервис";
                $textTitle = "сервису";
                break;
            case 4:
                $textService = "шиномонтаж";
                $textTitle = "шиномонтажу";
                break;
        }

        $text = "Статистика и анализ по " . $textTitle . " машин";
        $companyWorkSheet->setCellValue('B2', $text);

        $styleArray = array(
            'font'  => array(
                'size'  => 14,
            ));

        $companyWorkSheet->getStyle('B2')->applyFromArray($styleArray);

        $companyWorkSheet->mergeCells('B3:I3');
        $companyWorkSheet->getRowDimension(3)->setRowHeight(45);
        $companyWorkSheet->getStyle('B3:I3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
        $companyWorkSheet->getStyle('B3')->getAlignment()->setWrapText(true);
        $text = "компании ООО Агро-Авто (Москва ЮГ - МФП) за " . $monthName[0] . " " . date('Y', $this->time);
        $companyWorkSheet->setCellValue('B3', $text);
        $companyWorkSheet->getStyle('B3')->applyFromArray($styleArray);
        $companyWorkSheet->mergeCells('B4:I4');

        $styleArray = array(
            'font'  => array(
                'size'  => 12,
            ));

        $companyWorkSheet->mergeCells('B5:F5');
        $companyWorkSheet->setCellValue('B5', 'г.Воронеж');
        $companyWorkSheet->getStyle('B5')->applyFromArray($styleArray);
        $companyWorkSheet->getStyle('H5:I5')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
            )
        ));
        $companyWorkSheet->mergeCells('H5:I5');
        if($companyMain->is_split) {
            $companyWorkSheet->mergeCells('H5:J5');
        }
        $companyWorkSheet->mergeCells('G5:I5');
        if ($this->company) {
            $companyWorkSheet->setCellValue('H5', date("t ", $this->time) . $monthName[1] . date(' Y', $this->time));
        } else {
            $companyWorkSheet->setCellValue('H5', date('d ') . $currentMonthName[1] . date(' Y'));
        }
        $companyWorkSheet->getStyle('G5')->applyFromArray($styleArray);

        $row = 5;

        // МФП (Основной)
        if (isset($dataMfpTmp[154][0])) {

            $row++; $row++; $row++;

            $tmpDataCompany = $dataMfpTmp[154][0];

            // Первая таблица

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "ООО \"Агро-Авто\" (Москва ЮГ - МФП)");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "1. Количество обслуженных машин");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            //main values
            $rowStart = $row;
            $num = 0;

            $companyWorkSheet->getColumnDimension('A')->setWidth(7);
            $companyWorkSheet->getColumnDimension('B')->setWidth(5);
            $companyWorkSheet->getColumnDimension('C')->setWidth(5);
            $companyWorkSheet->getColumnDimension('D')->setWidth(5);
            $companyWorkSheet->getColumnDimension('E')->setWidth(10);
            $companyWorkSheet->getColumnDimension('F')->setWidth(3);
            $companyWorkSheet->getColumnDimension('G')->setWidth(22);
            $companyWorkSheet->getColumnDimension('H')->setWidth(15);
            $companyWorkSheet->getColumnDimension('I')->setWidth(7);
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
            }

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $numBigWorkCar = 0;

            foreach ($resCars as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['carsCount'];

                if ($value['actsCount'] > 2) {
                    $numBigWorkCar += $value['carsCount'];
                }

            }

            $numCompanyCar = Car::find()->where(['company_id' => $tmpDataCompany->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

            if ((count($numCompanyCar) - $numWorkCar) > 0) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            if ($this->serviceType == 2) {
                $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Первая таблица

            // Вторая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Город', '', '', 'Кол-во операций'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $arrCityWork = [];
            $z = 0;

            foreach ($resCity as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
                $column++;
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['actsCount'];
                $arrCityWork[$z][0] = $resCompany[0];
                $arrCityWork[$z][1] = $value['actsCount'];
                $z++;

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $finishPerecnt = 0;

            for ($z = 0; $z < count($arrCityWork); $z++) {
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

                if (($z + 1) == count($arrCityWork)) {
                    $percentGet = 100 - $finishPerecnt;
                    $percentGet = number_format($percentGet, 2);
                } else {
                    $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                    $percentGet = number_format($percentGet, 2);
                    $finishPerecnt += $percentGet;
                }

                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Вторая таблица

            // Третья таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            foreach ($resStat as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
                $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] . ' AND type_id != 7 AND type_id !=8')->all()));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Третья таблица


            // Четвертая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

            $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            $firstIncome = 0;

            // Позапрошлый месяц

            // Запрос

            // Формирование параметров поиска
            /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
            $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
            $timeTo = date("Y-m-dT21:00:00", $timeTo);
            $timeTo .= ".000Z";*/
            // Формирование параметров поиска

            //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND (`client_id`=" . $tmpDataCompany->id . ") GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            /*$row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if(isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if($incomeVals > 0) {
                $incomeVal = $incomeVals;
                $firstIncome = $incomeVal;
                $percentDelta = "100%";
            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++; $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

            // End Позапрошлый месяц

            // Предыдущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
            $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND (`client_id`=" . $tmpDataCompany->id . ") GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // End Предыдущий месяц

            // Текущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeFrom .= ".000Z";

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND (`client_id`=" . $tmpDataCompany->id . ") GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            // End Текущий месяц

            $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Четвертая таблица

        }

        // МФП 1
        if (isset($dataMfpTmp[900][0])) {

            $row++; $row++; $row++;

            $tmpDataCompany = $dataMfpTmp[900][0];

            // Первая таблица

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "ООО \"Агро-Авто\" (Москва ЮГ - МФП 1)");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "1. Количество обслуженных машин");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            //main values
            $rowStart = $row;
            $num = 0;

            $companyWorkSheet->getColumnDimension('A')->setWidth(7);
            $companyWorkSheet->getColumnDimension('B')->setWidth(5);
            $companyWorkSheet->getColumnDimension('C')->setWidth(5);
            $companyWorkSheet->getColumnDimension('D')->setWidth(5);
            $companyWorkSheet->getColumnDimension('E')->setWidth(10);
            $companyWorkSheet->getColumnDimension('F')->setWidth(3);
            $companyWorkSheet->getColumnDimension('G')->setWidth(22);
            $companyWorkSheet->getColumnDimension('H')->setWidth(15);
            $companyWorkSheet->getColumnDimension('I')->setWidth(7);
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
            }

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $numBigWorkCar = 0;

            foreach ($resCars as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['carsCount'];

                if ($value['actsCount'] > 2) {
                    $numBigWorkCar += $value['carsCount'];
                }

            }

            $numCompanyCar = Car::find()->where(['company_id' => $tmpDataCompany->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

            if ((count($numCompanyCar) - $numWorkCar) > 0) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            if ($this->serviceType == 2) {
                $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Первая таблица

            // Вторая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Город', '', '', 'Кол-во операций'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $arrCityWork = [];
            $z = 0;

            foreach ($resCity as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
                $column++;
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['actsCount'];
                $arrCityWork[$z][0] = $resCompany[0];
                $arrCityWork[$z][1] = $value['actsCount'];
                $z++;

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $finishPerecnt = 0;

            for ($z = 0; $z < count($arrCityWork); $z++) {
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

                if (($z + 1) == count($arrCityWork)) {
                    $percentGet = 100 - $finishPerecnt;
                    $percentGet = number_format($percentGet, 2);
                } else {
                    $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                    $percentGet = number_format($percentGet, 2);
                    $finishPerecnt += $percentGet;
                }

                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Вторая таблица

            // Третья таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            foreach ($resStat as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
                $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] . ' AND type_id != 7 AND type_id !=8')->all()));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Третья таблица


            // Четвертая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

            $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            $firstIncome = 0;

            // Позапрошлый месяц

            // Запрос

            // Формирование параметров поиска
            /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
            $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
            $timeTo = date("Y-m-dT21:00:00", $timeTo);
            $timeTo .= ".000Z";*/
            // Формирование параметров поиска

            //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            /*$row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if(isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if($incomeVals > 0) {
                $incomeVal = $incomeVals;
                $firstIncome = $incomeVal;
                $percentDelta = "100%";
            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++; $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

            // End Позапрошлый месяц

            // Предыдущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
            $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // End Предыдущий месяц

            // Текущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeFrom .= ".000Z";

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            // End Текущий месяц

            $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Четвертая таблица

        }

        // МФП 2
        if (isset($dataMfpTmp[849][0])) {

            $row++; $row++; $row++;

            $tmpDataCompany = $dataMfpTmp[849][0];

            // Первая таблица

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "ООО \"Агро-Авто\" (Москва ЮГ - МФП 2)");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "1. Количество обслуженных машин");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            //main values
            $rowStart = $row;
            $num = 0;

            $companyWorkSheet->getColumnDimension('A')->setWidth(7);
            $companyWorkSheet->getColumnDimension('B')->setWidth(5);
            $companyWorkSheet->getColumnDimension('C')->setWidth(5);
            $companyWorkSheet->getColumnDimension('D')->setWidth(5);
            $companyWorkSheet->getColumnDimension('E')->setWidth(10);
            $companyWorkSheet->getColumnDimension('F')->setWidth(3);
            $companyWorkSheet->getColumnDimension('G')->setWidth(22);
            $companyWorkSheet->getColumnDimension('H')->setWidth(15);
            $companyWorkSheet->getColumnDimension('I')->setWidth(7);
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
            }

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $numBigWorkCar = 0;

            foreach ($resCars as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['carsCount'];

                if ($value['actsCount'] > 2) {
                    $numBigWorkCar += $value['carsCount'];
                }

            }

            $numCompanyCar = Car::find()->where(['company_id' => $tmpDataCompany->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

            if ((count($numCompanyCar) - $numWorkCar) > 0) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            if ($this->serviceType == 2) {
                $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Первая таблица

            // Вторая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Город', '', '', 'Кол-во операций'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $arrCityWork = [];
            $z = 0;

            foreach ($resCity as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
                $column++;
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['actsCount'];
                $arrCityWork[$z][0] = $resCompany[0];
                $arrCityWork[$z][1] = $value['actsCount'];
                $z++;

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $finishPerecnt = 0;

            for ($z = 0; $z < count($arrCityWork); $z++) {
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

                if (($z + 1) == count($arrCityWork)) {
                    $percentGet = 100 - $finishPerecnt;
                    $percentGet = number_format($percentGet, 2);
                } else {
                    $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                    $percentGet = number_format($percentGet, 2);
                    $finishPerecnt += $percentGet;
                }

                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Вторая таблица

            // Третья таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            foreach ($resStat as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
                $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] . ' AND type_id != 7 AND type_id !=8')->all()));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Третья таблица


            // Четвертая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

            $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            $firstIncome = 0;

            // Позапрошлый месяц

            // Запрос

            // Формирование параметров поиска
            /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
            $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
            $timeTo = date("Y-m-dT21:00:00", $timeTo);
            $timeTo .= ".000Z";*/
            // Формирование параметров поиска

            //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            /*$row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if(isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if($incomeVals > 0) {
                $incomeVal = $incomeVals;
                $firstIncome = $incomeVal;
                $percentDelta = "100%";
            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++; $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

            // End Позапрошлый месяц

            // Предыдущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
            $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // End Предыдущий месяц

            // Текущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeFrom .= ".000Z";

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            // End Текущий месяц

            $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Четвертая таблица

        }

        // МФП 3
        if (isset($dataMfpTmp[850][0])) {

            $row++; $row++; $row++;

            $tmpDataCompany = $dataMfpTmp[850][0];

            // Первая таблица

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "ООО \"Агро-Авто\" (Москва ЮГ - МФП 3)");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "1. Количество обслуженных машин");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            //main values
            $rowStart = $row;
            $num = 0;

            $companyWorkSheet->getColumnDimension('A')->setWidth(7);
            $companyWorkSheet->getColumnDimension('B')->setWidth(5);
            $companyWorkSheet->getColumnDimension('C')->setWidth(5);
            $companyWorkSheet->getColumnDimension('D')->setWidth(5);
            $companyWorkSheet->getColumnDimension('E')->setWidth(10);
            $companyWorkSheet->getColumnDimension('F')->setWidth(3);
            $companyWorkSheet->getColumnDimension('G')->setWidth(22);
            $companyWorkSheet->getColumnDimension('H')->setWidth(15);
            $companyWorkSheet->getColumnDimension('I')->setWidth(7);
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
            }

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $numBigWorkCar = 0;

            foreach ($resCars as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['carsCount'];

                if ($value['actsCount'] > 2) {
                    $numBigWorkCar += $value['carsCount'];
                }

            }

            $numCompanyCar = Car::find()->where(['company_id' => $tmpDataCompany->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

            if ((count($numCompanyCar) - $numWorkCar) > 0) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            if ($this->serviceType == 2) {
                $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Первая таблица

            // Вторая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Город', '', '', 'Кол-во операций'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $arrCityWork = [];
            $z = 0;

            foreach ($resCity as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
                $column++;
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['actsCount'];
                $arrCityWork[$z][0] = $resCompany[0];
                $arrCityWork[$z][1] = $value['actsCount'];
                $z++;

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $finishPerecnt = 0;

            for ($z = 0; $z < count($arrCityWork); $z++) {
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

                if (($z + 1) == count($arrCityWork)) {
                    $percentGet = 100 - $finishPerecnt;
                    $percentGet = number_format($percentGet, 2);
                } else {
                    $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                    $percentGet = number_format($percentGet, 2);
                    $finishPerecnt += $percentGet;
                }

                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Вторая таблица

            // Третья таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            foreach ($resStat as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
                $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] . ' AND type_id != 7 AND type_id !=8')->all()));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Третья таблица


            // Четвертая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

            $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            $firstIncome = 0;

            // Позапрошлый месяц

            // Запрос

            // Формирование параметров поиска
            /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
            $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
            $timeTo = date("Y-m-dT21:00:00", $timeTo);
            $timeTo .= ".000Z";*/
            // Формирование параметров поиска

            //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            /*$row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if(isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if($incomeVals > 0) {
                $incomeVal = $incomeVals;
                $firstIncome = $incomeVal;
                $percentDelta = "100%";
            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++; $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

            // End Позапрошлый месяц

            // Предыдущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
            $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // End Предыдущий месяц

            // Текущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeFrom .= ".000Z";

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            // End Текущий месяц

            $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Четвертая таблица

        }

        // МФП 4
        if (isset($dataMfpTmp[851][0])) {

            $row++; $row++; $row++;

            $tmpDataCompany = $dataMfpTmp[851][0];

            // Первая таблица

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "ООО \"Агро-Авто\" (Москва ЮГ - МФП 4)");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "1. Количество обслуженных машин");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            //main values
            $rowStart = $row;
            $num = 0;

            $companyWorkSheet->getColumnDimension('A')->setWidth(7);
            $companyWorkSheet->getColumnDimension('B')->setWidth(5);
            $companyWorkSheet->getColumnDimension('C')->setWidth(5);
            $companyWorkSheet->getColumnDimension('D')->setWidth(5);
            $companyWorkSheet->getColumnDimension('E')->setWidth(10);
            $companyWorkSheet->getColumnDimension('F')->setWidth(3);
            $companyWorkSheet->getColumnDimension('G')->setWidth(22);
            $companyWorkSheet->getColumnDimension('H')->setWidth(15);
            $companyWorkSheet->getColumnDimension('I')->setWidth(7);
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
            }

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $numBigWorkCar = 0;

            foreach ($resCars as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['carsCount'];

                if ($value['actsCount'] > 2) {
                    $numBigWorkCar += $value['carsCount'];
                }

            }

            $numCompanyCar = Car::find()->where(['company_id' => $tmpDataCompany->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

            if ((count($numCompanyCar) - $numWorkCar) > 0) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            if ($this->serviceType == 2) {
                $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Первая таблица

            // Вторая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Город', '', '', 'Кол-во операций'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $arrCityWork = [];
            $z = 0;

            foreach ($resCity as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
                $column++;
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['actsCount'];
                $arrCityWork[$z][0] = $resCompany[0];
                $arrCityWork[$z][1] = $value['actsCount'];
                $z++;

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $finishPerecnt = 0;

            for ($z = 0; $z < count($arrCityWork); $z++) {
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

                if (($z + 1) == count($arrCityWork)) {
                    $percentGet = 100 - $finishPerecnt;
                    $percentGet = number_format($percentGet, 2);
                } else {
                    $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                    $percentGet = number_format($percentGet, 2);
                    $finishPerecnt += $percentGet;
                }

                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Вторая таблица

            // Третья таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            foreach ($resStat as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
                $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] . ' AND type_id != 7 AND type_id !=8')->all()));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Третья таблица


            // Четвертая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

            $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            $firstIncome = 0;

            // Позапрошлый месяц

            // Запрос

            // Формирование параметров поиска
            /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
            $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
            $timeTo = date("Y-m-dT21:00:00", $timeTo);
            $timeTo .= ".000Z";*/
            // Формирование параметров поиска

            //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            /*$row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if(isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if($incomeVals > 0) {
                $incomeVal = $incomeVals;
                $firstIncome = $incomeVal;
                $percentDelta = "100%";
            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++; $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

            // End Позапрошлый месяц

            // Предыдущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
            $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // End Предыдущий месяц

            // Текущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeFrom .= ".000Z";

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            // End Текущий месяц

            $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Четвертая таблица

        }

        // МФП 5
        if (isset($dataMfpTmp[852][0])) {

            $row++; $row++; $row++;

            $tmpDataCompany = $dataMfpTmp[852][0];

            // Первая таблица

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "ООО \"Агро-Авто\" (Москва ЮГ - МФП 5)");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "1. Количество обслуженных машин");
            $companyWorkSheet->getStyle('B' . $row . '')->applyFromArray($styleArray);

            $row++;
            $companyWorkSheet->getRowDimension($row)->setRowHeight(-1);
            $row++;

            //main values
            $rowStart = $row;
            $num = 0;

            $companyWorkSheet->getColumnDimension('A')->setWidth(7);
            $companyWorkSheet->getColumnDimension('B')->setWidth(5);
            $companyWorkSheet->getColumnDimension('C')->setWidth(5);
            $companyWorkSheet->getColumnDimension('D')->setWidth(5);
            $companyWorkSheet->getColumnDimension('E')->setWidth(10);
            $companyWorkSheet->getColumnDimension('F')->setWidth(3);
            $companyWorkSheet->getColumnDimension('G')->setWidth(22);
            $companyWorkSheet->getColumnDimension('H')->setWidth(15);
            $companyWorkSheet->getColumnDimension('I')->setWidth(7);
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getColumnDimension('J')->setAutoSize(true);
            }

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCars = Yii::$app->getDb()->createCommand("SELECT COUNT(actsCount) as carsCount, `actsCount`, `client_id` FROM (SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `car_number` ORDER BY `client_id`, `actsCount` DESC) `actsCount` GROUP BY `client_id`, `actsCount` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Кол-во ТС', '', 'Кол-во обслуживаний за 1 месяц'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $numBigWorkCar = 0;

            foreach ($resCars as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['carsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['carsCount'];

                if ($value['actsCount'] > 2) {
                    $numBigWorkCar += $value['carsCount'];
                }

            }

            $numCompanyCar = Car::find()->where(['company_id' => $tmpDataCompany->id])->andWhere(['!=', 'type_id', 7])->andWhere(['!=', 'type_id', 8])->all();

            if ((count($numCompanyCar) - $numWorkCar) > 0) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, (count($numCompanyCar) - $numWorkCar));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, 0);
            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, $numBigWorkCar . " машин обслужено более 2-ух раз.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, (count($numCompanyCar) - $numWorkCar) . " машин не обслужено ни одного раза.");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            if ($this->serviceType == 2) {
                $companyWorkSheet->getRowDimension($rowStart)->setRowHeight(35);
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Рекомендованное среднее кол-во моек 1 ТС за месяц - 2 раза.");
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Первая таблица

            // Вторая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "2. Статистика обслуженных машин по городам");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resCity = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `partner`.`address` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['Город', '', '', 'Кол-во операций'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            // Количество обслуженных машин
            $numWorkCar = 0;
            $arrCityWork = [];
            $z = 0;

            foreach ($resCity as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->mergeCells('B' . $row . ':D' . $row . '');
                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $resCompany = Company::find()->select(['address'])->where(['id' => $value['partner_id']])->column();

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $resCompany[0]);
                $column++;
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $value['actsCount']);

                $numWorkCar += $value['actsCount'];
                $arrCityWork[$z][0] = $resCompany[0];
                $arrCityWork[$z][1] = $value['actsCount'];
                $z++;

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':E' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":E$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');
            $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
            $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "Итого:");
            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
            $rowStart++;

            $finishPerecnt = 0;

            for ($z = 0; $z < count($arrCityWork); $z++) {
                $companyWorkSheet->mergeCells('G' . $rowStart . ':I' . $rowStart . '');

                if (($z + 1) == count($arrCityWork)) {
                    $percentGet = 100 - $finishPerecnt;
                    $percentGet = number_format($percentGet, 2);
                } else {
                    $percentGet = $arrCityWork[$z][1] / ($numWorkCar / 100);
                    $percentGet = number_format($percentGet, 2);
                    $finishPerecnt += $percentGet;
                }

                $companyWorkSheet->getStyle('G' . $rowStart)->getAlignment()->setWrapText(true);
                $companyWorkSheet->setCellValueByColumnAndRow(6, $rowStart, "- " . $percentGet . "% " . $arrCityWork[$z][0]);
                $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                $rowStart++;
            }

            if (($rowStart - 1) > $row) {
                $row += (($rowStart - 1) - $row);
            }

            // END Вторая таблица

            // Третья таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "3. Среднее кол-во операций на 1ТС");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('H' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('I' . $row)->getAlignment()->setWrapText(true);

            // Запрос

            // Формирование параметров поиска
            $timeFrom = $this->time;

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            // Формирование параметров поиска

            $resStat = Yii::$app->getDb()->createCommand("SELECT `car_id`, `car_number`, `served_at`, `partner_id`, `client_id`, `service_type`, COUNT(act.id) as actsCount FROM `act` `act` LEFT JOIN `type` ON `act`.`type_id` = `type`.`id` LEFT JOIN `mark` ON `act`.`mark_id` = `mark`.`id` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` LEFT JOIN `company` `partner` ON `act`.`partner_id` = `partner`.`id` LEFT JOIN `car` `car` ON `act`.`car_id` = `car`.`id` WHERE (`served_at` BETWEEN " . $timeFrom . " AND " . $timeTo . ") AND (`client_id`=" . $tmpDataCompany->id . ") AND (`service_type`=" . $this->serviceType . ") AND (car.type_id != 7) AND (car.type_id != 8) GROUP BY `client_id`, `service_type` ORDER BY `client_id`, `actsCount` DESC", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $headers = ['ТС в парке', '', 'Кол-во обслуженных ТС', '', 'Кол-во операций', '', 'Среднее кол-во операций на 1ТС'];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            foreach ($resStat as $value) {
                $row++;
                $num++;
                $column = 1;

                $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
                $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
                $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
                $companyWorkSheet->mergeCells('H' . $row . ':I' . $row . '');

                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, count(Car::find()->where('company_id = ' . $value['client_id'] . ' AND type_id != 7 AND type_id !=8')->all()));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, true, 0, $timeFrom, $timeTo));
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $value['actsCount']);
                $column++;
                $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, \frontend\controllers\AnalyticsController::getWorkCars($value['client_id'], $this->serviceType, false, $value['actsCount'], $timeFrom, $timeTo));

            }

            $companyWorkSheet->getStyle('B' . $rowStart . ':I' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Третья таблица


            // Четвертая таблица
            $row++;
            $row++;

            $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
            $companyWorkSheet->setCellValue('B' . $row . '', "4. Расходы на обслуживание");
            $companyWorkSheet->getStyle('B' . $row)->applyFromArray($styleArray);

            //main values
            $row++;
            $row++;
            $rowStart = $row;
            $num = 0;
            $total = 0;
            $count = 0;

            $companyWorkSheet->getRowDimension($row)->setRowHeight(45);
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('E' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('F' . $row)->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('G' . $row)->getAlignment()->setWrapText(true);

            $headers = ['Месяц', '', 'Сумма, руб.', '', html_entity_decode('&#916;') . " по отношению к предыдущему месяцу"];
            $companyWorkSheet->fromArray($headers, null, 'B' . $rowStart);
            /** @var Act $data */
            $currentId = 0;
            $isParent = false;
            if ($this->company && count($tmpDataCompany->children) > 0) {
                $isParent = true;
            }

            $firstIncome = 0;

            // Позапрошлый месяц

            // Запрос

            // Формирование параметров поиска
            /*$timeFrom = strtotime(date("m/1/Y", ($this->time - 3456000))) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $timeTo = strtotime(date("m/1/Y", ($this->time - 3456000))) + 3024000;
            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 3456000))));
            $timeTo = strtotime(date("m/1/Y", $timeTo)) - 86400;
            $timeTo = date("Y-m-dT21:00:00", $timeTo);
            $timeTo .= ".000Z";*/
            // Формирование параметров поиска

            //$resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            /*$row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if(isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if($incomeVals > 0) {
                $incomeVal = $incomeVals;
                $firstIncome = $incomeVal;
                $percentDelta = "100%";
            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++; $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);*/

            // End Позапрошлый месяц

            // Предыдущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = strtotime(date("m/1/Y", $this->time - 86400)) - 86400;
            $timeFrom = date("Y-m-dT21:00:00", $timeFrom);
            $timeFrom .= ".000Z";

            $lastMonthName = DateHelper::getMonthName(strtotime(date("m/1/Y", ($this->time - 86400))));
            $timeTo = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $lastMonthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            // End Предыдущий месяц

            // Текущий месяц

            // Запрос

            // Формирование параметров поиска
            $timeFrom = date("Y-m-dT21:00:00", $this->time - 86400);
            $timeFrom .= ".000Z";

            $timeTo = $this->time + 3456000;
            $timeTo = mktime(21, 00, 00, date('m', $timeTo), 01, date('Y', $timeTo)) - 75600;
            $timeTo = date("Y-m-dT21:00:00", $timeTo - 86400);
            $timeTo .= ".000Z";
            // Формирование параметров поиска

            $resIncome = Yii::$app->getDb()->createCommand("SELECT DATE(FROM_UNIXTIME(served_at)) as dateMonth, COUNT(`act`.id) AS countServe, ROUND(SUM(profit)/COUNT(`act`.id)) AS ssoom, `service_type`, SUM(expense) as expense, SUM(income) as income, SUM(profit) as profit, `partner_id`, `client_id` FROM `act` LEFT JOIN `company` `client` ON `act`.`client_id` = `client`.`id` WHERE (DATE(FROM_UNIXTIME(`served_at`)) BETWEEN '" . $timeFrom . "' AND '" . $timeTo . "') AND (`service_type`=" . $this->serviceType . ") AND ((`client`.`parent_id`=" . $tmpDataCompany->id . ") OR (`client_id`=" . $tmpDataCompany->id . ")) GROUP BY DATE_FORMAT(dateMonth, \"%Y-%m\") ORDER BY `dateMonth`", [':start_date' => '1970-01-01'])->queryAll();

            // Запрос

            $row++;
            $num++;
            $column = 1;

            $companyWorkSheet->mergeCells('B' . $row . ':C' . $row . '');
            $companyWorkSheet->mergeCells('D' . $row . ':E' . $row . '');
            $companyWorkSheet->mergeCells('F' . $row . ':G' . $row . '');
            $companyWorkSheet->getStyle('B' . $row . ':I' . $row . '')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $monthName[0]);
            $column++;

            $incomeVal = 0;
            $percentDelta = 0 . "%";

            $incomeVals = 0;

            for ($i = 0; $i < count($resIncome); $i++) {
                if (isset($resIncome[$i]['income'])) {
                    $incomeVals = $resIncome[$i]['income'];
                }
            }

            if ($incomeVals > 0) {
                $incomeVal = $incomeVals;

                if ($firstIncome == 0) {
                    $firstIncome = $incomeVal;
                    $percentDelta = 100 . "%";
                } else {

                    if ($firstIncome > $incomeVal) {
                        $percentDelta = 100 - (($incomeVal * 100) / $firstIncome);
                        $percentDelta = "-" . number_format($percentDelta, 2) . "%";
                    } else if ($firstIncome < $incomeVal) {
                        $percentDelta = $incomeVal / ($firstIncome / 100);
                        $percentDelta = "+" . number_format($percentDelta, 2) . "%";
                    }

                }

            }

            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $incomeVal);

            $column++;
            $column++;
            $companyWorkSheet->setCellValueByColumnAndRow($column, $row, $percentDelta);

            $companyWorkSheet->getStyle('F' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('G' . $row)
                ->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

            $companyWorkSheet->getStyle('B' . $row . ':G' . $row . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            // End Текущий месяц

            $companyWorkSheet->getStyle('B' . $rowStart . ':G' . $rowStart . '')->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );
            if ($tmpDataCompany->is_split) {
                $companyWorkSheet->getStyle('J' . $rowStart . '')->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'color' => array('argb' => 'FF006699'),
                        ),
                    )
                );
            }

            $companyWorkSheet->getStyle("B" . $rowStart . ":G$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            // END Четвертая таблица

        }

        //footer

        $row++; $row++; $row++;
        $companyWorkSheet->getRowDimension($row)->setRowHeight(35);
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValue('B' . $row . '', "Более подробную информацию Вы можете посмотреть в своем личном кабинете, на сайте http://docs.mtransservice.ru");

        $row++;
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->getRowDimension($row)->setRowHeight(35);
        $companyWorkSheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
        $companyWorkSheet->setCellValue('B' . $row . '', "При возникновении вопросов, Вы всегда можете связаться с персональным менеджером нашей компании.");

        $row++; $row++;
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "С Уважением,");

        $row++;
        $companyWorkSheet->mergeCells('B' . $row . ':I' . $row . '');
        $companyWorkSheet->setCellValue('B' . $row . '', "Международный Транспортный Сервис");

        $companyWorkSheet->setBreak( "A$row" , PHPExcel_Worksheet::BREAK_ROW );
        $companyWorkSheet->setBreak( "J$row" , PHPExcel_Worksheet::BREAK_COLUMN );

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::TYPE_SERVICE) {
            $first = $companyMainData[0];
            $filename = "Статистика_анализ_" . $textService . " {ООО Агро-Авто (Москва ЮГ - МФП)} - {$first->car_number} - {$first->id} от " . date('d-m-Y', $first->served_at) . ".xls";
        } else {
            $filename = $serviceDescription. " Статистика_анализ_" . $textService . " ООО Агро-Авто (Москва ЮГ - МФП) от " . date('m-Y', $this->time) . ".xls";
        }
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));

    }

}