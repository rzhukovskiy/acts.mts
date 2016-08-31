<?php
namespace common\components;

use common\models\Act;
use common\models\ActScope;
use common\models\Company;
use common\models\search\ActSearch;
use common\models\Service;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Worksheet;
use PHPExcel_Writer_IWriter;
use PHPExcel_Style_NumberFormat;
use PHPExcel_Worksheet_MemoryDrawing;
use ZipArchive;
use yii;

class ActExporter
{
    private $headersSent = false;
    private $company = false;
    private $serviceType = Company::TYPE_WASH;
    private $time = null;

    /**
     * @param ActSearch $searchModel
     * @param bool $company
     */
    public function exportCSV($searchModel, $company)
    {
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $this->time = \DateTime::createFromFormat('m-Y-d H:i:s', $searchModel->period . '-01 00:00:00')->getTimestamp();
        $this->company = $company;
        $this->serviceType = $searchModel->service_type;

        $zip = new ZipArchive();
        $type = Service::$listType[$this->serviceType]['en'];
        $filename = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time) . "/all.zip";

        if ($zip->open($filename, ZipArchive::OVERWRITE) !== TRUE) {
            $zip = null;
        }

        switch ($this->serviceType) {
            case Service::TYPE_WASH:
                $this->generateWashAct($dataProvider, $zip);
                break;
            case Service::TYPE_TIRES:
                $this->generateTiresAct($dataProvider, $zip);
                break;
            case Service::TYPE_SERVICE:
                $this->generateServiceAct($dataProvider, $zip);
                break;
            case Service::TYPE_DISINFECT:
                $this->generateDisinfectAct($dataProvider, $zip);
                if ($this->company) {
                    $this->generateDisinfectCertificate($dataProvider, $zip);
                }
                break;
        }

        if ($zip) $zip->close();
    }

    /**
     * @param yii\data\ActiveDataProvider $dataProvider
     * @param ZipArchive $zip
     */
    private function generateWashAct($dataProvider, &$zip) {
        /** @var Company $company */
        $company = null;
        /** @var Act $act */
        $act = null;
        $companyId = 0;

        /** @var PHPExcel $objPHPExcel */
        $objPHPExcel = null;
        /** @var PHPExcel_Writer_IWriter $objWriter */
        $objWriter = null;
        /** @var PHPExcel_Worksheet $worksheet */
        $worksheet = null;

        $row = 12;
        $num = 0;
        $total = 0;
        $count = 0;
        /**
         * @var Act[] $listData
         */
        $listData = $dataProvider->getModels();
        for ($i = 0; $i <= count($listData); $i++) {
            $newAct = !empty($listData[$i]) ? $listData[$i] : null;
            $newCompany = $newAct ? ($this->company ? $newAct->client : $newAct->partner) : null;

            if (!$newCompany || $companyId != $newCompany->id) {
                if ($company) {
                    $worksheet->getStyle('B12:I12')->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'color' => array('argb' => 'FF006699'),
                            ),
                        )
                    );
                    if($company->is_split) {
                        $worksheet->getStyle('J12')->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                    }

                    $worksheet->getStyle("B12:I$row")
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
                        $worksheet->getStyle("J12:J$row")
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

                    //footer
                    $row++;
                    if($company->is_split) {
                        $worksheet->setCellValue("H$row", "ВСЕГО:");
                        $worksheet->setCellValue("I$row", "$total");
                    } else {
                        $worksheet->setCellValue("G$row", "ВСЕГО:");
                        $worksheet->setCellValue("H$row", "$total");
                    }

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("B$row:J$row");
                    }
                    $worksheet->getRowDimension($row)->setRowHeight(30);
                    $worksheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
                    $text = "Общая стоимость выполненных услуг составляет: $total (" . DigitHelper::num2str($total) . ") рублей. НДС нет.";
                    $worksheet->setCellValue("B$row", $text);

                    $row++;
                    $worksheet->mergeCells("B$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("B$row:J$row");
                    }
                    $worksheet->getRowDimension($row)->setRowHeight(30);
                    $worksheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
                    $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
                    $worksheet->setCellValue("B$row", $text);

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:E$row");
                    $worksheet->mergeCells("G$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("G$row:J$row");
                    }
                    if ($this->company) {
                        $worksheet->setCellValue("B$row", "Работу сдал");
                        $worksheet->setCellValue("G$row", "Работу принял");
                    } else {
                        $worksheet->setCellValue("B$row", "Работу принял");
                        $worksheet->setCellValue("G$row", "Работу сдал");
                    }

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:E$row");
                    $worksheet->mergeCells("G$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("G$row:J$row");
                    }
                    if ($this->company) {
                        $worksheet->setCellValue("B$row", "Исполнитель");
                        $worksheet->setCellValue("G$row", "Заказчик");
                    } else {
                        $worksheet->setCellValue("B$row", "Заказчик");
                        $worksheet->setCellValue("G$row", "Исполнитель");
                    }

                    $row++;
                    //подпись
                    $signImage = imagecreatefromjpeg('images/sign.jpg');
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setName('Sample image');
                    $objDrawing->setDescription('Sample image');
                    $objDrawing->setImageResource($signImage);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setCoordinates("B$row");
                    $objDrawing->setWorksheet($worksheet);
                    $row++;

                    $worksheet->mergeCells("B$row:C$row");
                    $worksheet->mergeCells("D$row:E$row");
                    $worksheet->mergeCells("G$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("G$row:J$row");
                    }
                    $worksheet->setCellValue("D$row", "Мосесян Г.А.");
                    $worksheet->setCellValue("G$row", "____________$company->director");

                    $row++; $row++;
                    //печать
                    $gdImage = imagecreatefromjpeg('images/post.jpg');
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setName('Sample image');
                    $objDrawing->setDescription('Sample image');
                    $objDrawing->setImageResource($gdImage);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($worksheet);

                    $worksheet->setCellValue("G$row", "М.П.");

                    //saving document
                    $type = Service::$listType[$this->serviceType]['en'];
                    $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
                    if (!is_dir($path)) {
                        mkdir($path, 0755, 1);
                    }
                    $filename = "Акт $company->name от " . date('m-Y', $this->time) . ".xlsx";
                    $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
                    $objWriter->save($fullFilename);
                    if ($zip) $zip->addFile($fullFilename, $filename);

                    if ($this->company) {
                        $this->generateCheck($act, $zip, $count, $total);
                    }
                }

                if (!$newAct) {
                    continue;
                }

                $company = $newCompany;
                $act = $newAct;
                $companyId = $company->id;

                $objPHPExcel = new PHPExcel();
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

                // Creating a workbook
                $objPHPExcel->getProperties()->setCreator('Mtransservice');
                $objPHPExcel->getProperties()->setTitle('Акт');
                $objPHPExcel->getProperties()->setSubject('Акт');
                $objPHPExcel->getProperties()->setDescription('');
                $objPHPExcel->getProperties()->setCategory('');
                $objPHPExcel->removeSheetByIndex(0);

                //adding worksheet
                $worksheet = new PHPExcel_Worksheet($objPHPExcel, 'акт');
                $objPHPExcel->addSheet($worksheet);

                $worksheet->getPageMargins()->setTop(2);
                $worksheet->getPageMargins()->setLeft(0.5);
                $worksheet->getRowDimension(1)->setRowHeight(1);
                $worksheet->getRowDimension(10)->setRowHeight(120);
                $worksheet->getColumnDimension('A')->setWidth(2);
                $worksheet->getDefaultRowDimension()->setRowHeight(20);

                //headers;
                $monthName = DateHelper::getMonthName($this->time);

                $worksheet->getStyle('B2:I4')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                ));
                $worksheet->mergeCells('B2:I2');
                if($company->is_split) {
                    $worksheet->mergeCells('B2:J2');
                }
                $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)";
                $worksheet->setCellValue('B2', $text);
                $worksheet->mergeCells('B3:I3');
                $text = "по договору на оказание услуг " . $company->getRequisitesByType($this->serviceType, 'contract');
                $worksheet->setCellValue('B3', $text);
                $worksheet->mergeCells('B4:I4');
                $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
                $worksheet->setCellValue('B4', $text);

                $worksheet->setCellValue('B5', 'г.Воронеж');
                $worksheet->getStyle('H5:I5')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    )
                ));
                $worksheet->mergeCells('H5:I5');
                if($company->is_split) {
                    $worksheet->mergeCells('H5:J5');
                }
                $worksheet->setCellValue('H5', date('t ', $this->time) . $monthName[1] . date(' Y', $this->time));

                $worksheet->mergeCells('B8:I8');
                $worksheet->mergeCells('B7:I7');
                if ($this->company) {
                    $worksheet->setCellValue('B8', "Исполнитель: ООО «Международный Транспортный Сервис»");
                    $worksheet->setCellValue('B7', "Заказчик: $company->name");
                } else {
                    $worksheet->setCellValue('B7', "Исполнитель: $company->name");
                    $worksheet->setCellValue('B8', "Заказчик: ООО «Международный Транспортный Сервис»");
                }

                $worksheet->mergeCells('B10:I10');
                $worksheet->getStyle('B10:I10')->getAlignment()->setWrapText(true);
                $worksheet->getStyle('B10:I10')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                    )
                ));
                $worksheet->setCellValue('B10', $company->getRequisitesByType($this->serviceType, 'header'));
                $worksheet->getRowDimension(10)->setRowHeight(-1);

                $row = 12;
                $num = 0;
                $total = 0;
                $count = 0;

                $worksheet->getColumnDimension('B')->setWidth(5);
                $worksheet->getColumnDimension('C')->setAutoSize(true);
                $worksheet->getColumnDimension('D')->setAutoSize(true);
                $worksheet->getColumnDimension('E')->setAutoSize(true);
                $worksheet->getColumnDimension('F')->setAutoSize(true);
                $worksheet->getColumnDimension('G')->setAutoSize(true);
                $worksheet->getColumnDimension('H')->setAutoSize(true);
                $worksheet->getColumnDimension('I')->setAutoSize(true);
                if($company->is_split) {
                    $worksheet->getColumnDimension('J')->setAutoSize(true);
                }

                $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость', '№ Чека'];
                if($company->is_split) {
                    $headers = ['№', 'Число', '№ Карты', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость', '№ Чека'];
                }
                $worksheet->fromArray($headers, null, 'B12');
            }

            $company = $newCompany;
            $companyId = $company->id;
            $act = $newAct;

            $row++;
            $num++;
            $column = 1;
            $worksheet->setCellValueByColumnAndRow($column++, $row, $num);
            $worksheet->setCellValueByColumnAndRow($column++, $row, date('j', $act->served_at));
            $worksheet->setCellValueByColumnAndRow($column++, $row, isset($act->card) ? $act->card->number : $act->card_id);
            $worksheet->setCellValueByColumnAndRow($column++, $row, isset($act->mark) ? $act->mark->name : "");
            $worksheet->setCellValueByColumnAndRow($column++, $row, $act->number);
            if($company->is_split) {
                $worksheet->setCellValueByColumnAndRow($column++, $row, $act->extra_number);
            }
            if ($this->company) {
                $services = [];
                foreach ($act->clientScopes as $scope) {
                    $services[] = $scope->description;
                }
                $worksheet->setCellValueByColumnAndRow($column++, $row, implode('+', $services));
                $worksheet->setCellValueByColumnAndRow($column++, $row, $act->income);
                $total += $act->income;
            } else {
                $services = [];
                foreach ($act->partnerScopes as $scope) {
                    $services[] = $scope->description;
                }
                $worksheet->setCellValueByColumnAndRow($column++, $row, implode('+', $services));
                $worksheet->setCellValueByColumnAndRow($column++, $row, $act->expense);
                $total += $act->expense;
            }
            $worksheet->getCellByColumnAndRow($column, $row)
                ->getStyle()
                ->getNumberFormat()
                ->setFormatCode(
                    PHPExcel_Style_NumberFormat::FORMAT_TEXT
                );
            $worksheet->setCellValueByColumnAndRow($column++, $row, ' ' . $act->check);
            $count++;
        }
    }

    /**
     * @param yii\data\ActiveDataProvider $dataProvider
     * @param ZipArchive $zip
     */
    private function generateServiceAct($dataProvider, &$zip)
    {
        /**
         * @var Act[] $listData
         */
        $listData = $dataProvider->getModels();
        for ($i = 0; $i <= count($listData); $i++) {
            $act = !empty($listData[$i]) ? $listData[$i] : null;

            $company = $act ? ($this->company ? $act->client : $act->partner) : null;
            if (!$company) {
                continue;
            }

            $objPHPExcel = new PHPExcel();
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            // Creating a workbook
            $objPHPExcel->getProperties()->setCreator('Mtransservice');
            $objPHPExcel->getProperties()->setTitle('Акт');
            $objPHPExcel->getProperties()->setSubject('Акт');
            $objPHPExcel->getProperties()->setDescription('');
            $objPHPExcel->getProperties()->setCategory('');
            $objPHPExcel->removeSheetByIndex(0);

            //adding worksheet
            $worksheet = new PHPExcel_Worksheet($objPHPExcel, 'акт');
            $objPHPExcel->addSheet($worksheet);

            $worksheet->getPageMargins()->setTop(2);
            $worksheet->getPageMargins()->setLeft(0.5);
            $worksheet->getRowDimension(1)->setRowHeight(1);
            $worksheet->getRowDimension(10)->setRowHeight(120);
            $worksheet->getColumnDimension('A')->setWidth(2);
            $worksheet->getDefaultRowDimension()->setRowHeight(20);

            //headers;
            $monthName = DateHelper::getMonthName($this->time);

            $worksheet->getStyle('B2:I4')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $worksheet->mergeCells('B2:I2');
            if($company->is_split) {
                $worksheet->mergeCells('B2:J2');
            }
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)";
            $worksheet->setCellValue('B2', $text);
            $worksheet->mergeCells('B3:I3');
            $text = "по договору на оказание услуг " . $company->getRequisitesByType($this->serviceType, 'contract');
            $worksheet->setCellValue('B3', $text);
            $worksheet->mergeCells('B4:I4');
            $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
            $worksheet->setCellValue('B4', $text);

            $worksheet->setCellValue('B5', 'г.Воронеж');
            $worksheet->getStyle('H5:I5')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                )
            ));
            $worksheet->mergeCells('H5:I5');
            if($company->is_split) {
                $worksheet->mergeCells('H5:J5');
            }
            $worksheet->setCellValue('H5', date("d ", $act->served_at) . $monthName[1] . date(' Y', $this->time));

            $worksheet->mergeCells('B8:I8');
            $worksheet->mergeCells('B7:I7');
            if ($this->company) {
                $worksheet->setCellValue('B8', "Исполнитель: ООО «Международный Транспортный Сервис»");
                $worksheet->setCellValue('B7', "Заказчик: $company->name");
            } else {
                $worksheet->setCellValue('B7', "Исполнитель: $company->name");
                $worksheet->setCellValue('B8', "Заказчик: ООО «Международный Транспортный Сервис»");
            }

            $worksheet->mergeCells('B10:I10');
            $worksheet->getStyle('B10:I10')->getAlignment()->setWrapText(true);
            $worksheet->getStyle('B10:I10')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                )
            ));
            $worksheet->setCellValue('B10', $company->getRequisitesByType($this->serviceType, 'header'));
            $worksheet->getRowDimension(10)->setRowHeight(-1);

            $row = 11;

            $worksheet->getDefaultStyle()->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                )
            ));
            $worksheet->getColumnDimension('B')->setWidth(11);
            $worksheet->getColumnDimension('C')->setWidth(11);
            $worksheet->getColumnDimension('D')->setWidth(11);
            $worksheet->getColumnDimension('E')->setWidth(11);
            $worksheet->getColumnDimension('F')->setWidth(11);
            $worksheet->getColumnDimension('G')->setWidth(11);
            $worksheet->getColumnDimension('H')->setWidth(11);
            $worksheet->getColumnDimension('I')->setWidth(11);
            $row++;
            $num = 0;

            $worksheet->mergeCells("B$row:C$row");
            $worksheet->setCellValue("B$row", "ЧИСЛО");
            $worksheet->mergeCells("D$row:E$row");
            $worksheet->setCellValue("D$row", "№ КАРТЫ");
            $worksheet->setCellValue("F$row", "МАРКА ТС");
            if ($this->company) {
                $worksheet->mergeCells("H$row:I$row");
                $worksheet->setCellValue("G$row", "ГОСНОМЕР");
                $worksheet->setCellValue("H$row", "ГОРОД");
            } else {
                $worksheet->mergeCells("G$row:I$row");
                $worksheet->setCellValue("G$row", "ГОСНОМЕР");
            }
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
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
            $worksheet->mergeCells("B$row:C$row");
            $worksheet->setCellValueByColumnAndRow(1, $row, date('j', $act->served_at));
            $worksheet->mergeCells("D$row:E$row");
            $worksheet->setCellValueByColumnAndRow(3, $row, isset($act->card) ? $act->card->number : $act->card_id);
            $worksheet->setCellValueByColumnAndRow(5, $row, isset($act->mark) ? $act->mark->name : "");
            if ($this->company) {
                $worksheet->mergeCells("H$row:I$row");
                $worksheet->setCellValueByColumnAndRow(6, $row, $act->number);
                $worksheet->setCellValueByColumnAndRow(7, $row, $act->partner->address);
            } else {
                $worksheet->mergeCells("G$row:I$row");
                $worksheet->setCellValueByColumnAndRow(6, $row, $act->number);
            }
            $worksheet->getStyle("B$row:I$row")
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

            $row++;
            $worksheet->mergeCells("B$row:F$row");
            $worksheet->setCellValue("B$row", "Вид услуг");
            $worksheet->setCellValue("G$row", "Кол-во");
            $worksheet->setCellValue("H$row", "Стоимость");
            $worksheet->setCellValue("I$row", "Сумма");
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            $total = 0;
            $subtotal = 0;
            $count = 0;
            if ($this->company) {
                $scopeList = $act->clientScopes;
            } else {
                $scopeList = $act->partnerScopes;
            }
            /** @var ActScope $scope */
            foreach ($scopeList as $scope) {
                $row++;
                $num++;
                $worksheet->mergeCells("B$row:F$row");
                $worksheet->setCellValue("B$row", "$num. $scope->description");
                $worksheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                if (mb_strlen($scope->description) > 55) {
                    $worksheet->getRowDimension($row)->setRowHeight(40);
                }
                $worksheet->setCellValue("G$row", $scope->amount);
                $worksheet->setCellValue("H$row", $scope->price);
                $worksheet->setCellValue("I$row", $scope->price * $scope->amount);
                $total += $scope->amount * $scope->price;
                $subtotal += $scope->price;
                $count += $scope->amount;
            }
            $row++;
            $worksheet->mergeCells("B$row:F$row");
            $worksheet->setCellValue("B$row", "Итого:");
            $worksheet->setCellValue("G$row", $count);
            $worksheet->setCellValue("H$row", $subtotal);
            $worksheet->setCellValue("I$row", $total);
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                    ),
                )
            );

            $worksheet->getStyle("B13:I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );


            $row++;
            $worksheet->setCellValue("F$row", "ВСЕГО:");
            $worksheet->setCellValue("G$row", "$count");
            $worksheet->setCellValue("H$row", "$total");
            $worksheet->setCellValue("I$row", "$total");
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'size' => 12,
                    ),
                )
            );

            $row++; $row++;
            $worksheet->mergeCells("B$row:I$row");
            if($company->is_split) {
                $worksheet->mergeCells("B$row:J$row");
            }
            $worksheet->getRowDimension($row)->setRowHeight(30);
            $worksheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $total (" . DigitHelper::num2str($total) . ") рублей. НДС нет.";
            $worksheet->setCellValue("B$row", $text);

            $row++;
            $worksheet->mergeCells("B$row:I$row");
            if($company->is_split) {
                $worksheet->mergeCells("B$row:J$row");
            }
            $worksheet->getRowDimension($row)->setRowHeight(30);
            $worksheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
            $worksheet->setCellValue("B$row", $text);

            $row++; $row++;
            $worksheet->mergeCells("B$row:E$row");
            $worksheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $worksheet->mergeCells("G$row:J$row");
            }
            if ($this->company) {
                $worksheet->setCellValue("B$row", "Работу сдал");
                $worksheet->setCellValue("G$row", "Работу принял");
            } else {
                $worksheet->setCellValue("B$row", "Работу принял");
                $worksheet->setCellValue("G$row", "Работу сдал");
            }

            $row++; $row++;
            $worksheet->mergeCells("B$row:E$row");
            $worksheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $worksheet->mergeCells("G$row:J$row");
            }
            if ($this->company) {
                $worksheet->setCellValue("B$row", "Исполнитель");
                $worksheet->setCellValue("G$row", "Заказчик");
            } else {
                $worksheet->setCellValue("B$row", "Заказчик");
                $worksheet->setCellValue("G$row", "Исполнитель");
            }


            $row++;
            //подпись
            $signImage = imagecreatefromjpeg('images/sign.jpg');
            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setImageResource($signImage);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $objDrawing->setCoordinates("B$row");
            $objDrawing->setWorksheet($worksheet);
            $row++;

            $worksheet->mergeCells("C$row:E$row");
            $worksheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $worksheet->mergeCells("G$row:J$row");
            }
            $worksheet->setCellValue("C$row", "Мосесян Г.А.");
            $worksheet->setCellValue("G$row", "____________$company->director");

            $row++; $row++;
            //печать
            $gdImage = imagecreatefromjpeg('images/post.jpg');
            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setImageResource($gdImage);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $objDrawing->setCoordinates("C$row");
            $objDrawing->setWorksheet($worksheet);

            $worksheet->setCellValue("G$row", "М.П.");

            //saving document
            $type = Service::$listType[$this->serviceType]['en'];
            $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
            if (!is_dir($path)) {
                mkdir($path, 0755, 1);
            }

            $filename = "Акт $company->name - $act->number.$act->id от " . date('d-m-Y', $act->served_at) . ".xlsx";
            $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
            $objWriter->save($fullFilename);
            if ($zip) $zip->addFile($fullFilename, $filename);

            if ($this->company) {
                $this->generateCheck($act, $zip, $count, $total);
            }
        }
    }

    /**
     * @param yii\data\ActiveDataProvider $dataProvider
     * @param ZipArchive $zip
     */
    private function generateTiresAct($dataProvider, &$zip)
    {
        /** @var Company $company */
        $company = null;
        /** @var Act $act */
        $act = null;
        $companyId = 0;

        /** @var PHPExcel $objPHPExcel */
        $objPHPExcel = null;
        /** @var PHPExcel_Writer_IWriter $objWriter */
        $objWriter = null;
        /** @var PHPExcel_Worksheet $worksheet */
        $worksheet = null;

        $row = 11;
        $fullTotal = 0;
        $fullCount = 0;

        /**
         * @var Act[] $listData
         */
        $listData = $dataProvider->getModels();
        for ($i = 0; $i <= count($listData); $i++) {
            $newAct = !empty($listData[$i]) ? $listData[$i] : null;
            $newCompany = $newAct ? ($this->company ? $newAct->client : $newAct->partner) : null;

            if (!$newCompany || $companyId != $newCompany->id) {
                if ($company) {
                    $row++;
                    $worksheet->setCellValue("F$row", "ВСЕГО:");
                    $worksheet->setCellValue("G$row", "$fullCount");
                    $worksheet->setCellValue("I$row", "$fullTotal");
                    $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'size' => 12,
                            ),
                        )
                    );

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("B$row:J$row");
                    }
                    $worksheet->getRowDimension($row)->setRowHeight(30);
                    $worksheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
                    $text = "Общая стоимость выполненных услуг составляет: $fullTotal (" . DigitHelper::num2str($fullTotal) . ") рублей. НДС нет.";
                    $worksheet->setCellValue("B$row", $text);

                    $row++;
                    $worksheet->mergeCells("B$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("B$row:J$row");
                    }
                    $worksheet->getRowDimension($row)->setRowHeight(30);
                    $worksheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
                    $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
                    $worksheet->setCellValue("B$row", $text);

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:E$row");
                    $worksheet->mergeCells("G$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("G$row:J$row");
                    }
                    if ($this->company) {
                        $worksheet->setCellValue("B$row", "Работу сдал");
                        $worksheet->setCellValue("G$row", "Работу принял");
                    } else {
                        $worksheet->setCellValue("B$row", "Работу принял");
                        $worksheet->setCellValue("G$row", "Работу сдал");
                    }

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:E$row");
                    $worksheet->mergeCells("G$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("G$row:J$row");
                    }
                    if ($this->company) {
                        $worksheet->setCellValue("B$row", "Исполнитель");
                        $worksheet->setCellValue("G$row", "Заказчик");
                    } else {
                        $worksheet->setCellValue("B$row", "Заказчик");
                        $worksheet->setCellValue("G$row", "Исполнитель");
                    }


                    $row++;
                    //подпись
                    $signImage = imagecreatefromjpeg('images/sign.jpg');
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setName('Sample image');
                    $objDrawing->setDescription('Sample image');
                    $objDrawing->setImageResource($signImage);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setCoordinates("B$row");
                    $objDrawing->setWorksheet($worksheet);
                    $row++;

                    $worksheet->mergeCells("C$row:E$row");
                    $worksheet->mergeCells("G$row:I$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("G$row:J$row");
                    }
                    $worksheet->setCellValue("C$row", "Мосесян Г.А.");
                    $worksheet->setCellValue("G$row", "____________$company->director");

                    $row++; $row++;
                    //печать
                    $gdImage = imagecreatefromjpeg('images/post.jpg');
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setName('Sample image');
                    $objDrawing->setDescription('Sample image');
                    $objDrawing->setImageResource($gdImage);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($worksheet);

                    $worksheet->setCellValue("G$row", "М.П.");

                    //saving document
                    $type = Service::$listType[$this->serviceType]['en'];
                    $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
                    if (!is_dir($path)) {
                        mkdir($path, 0755, 1);
                    }

                    $filename = "Акт $company->name от " . date('m-Y', $this->time) . ".xlsx";
                    $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
                    $objWriter->save($fullFilename);
                    if ($zip) $zip->addFile($fullFilename, $filename);

                    if ($this->company) {
                        $this->generateCheck($act, $zip, $fullCount, $fullTotal);
                    }
                }

                $row = 11;
                $fullTotal = 0;
                $fullCount = 0;

                if (!$newAct || !$newCompany) {
                    continue;
                }

                $company = $newCompany;

                $objPHPExcel = new PHPExcel();
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

                // Creating a workbook
                $objPHPExcel->getProperties()->setCreator('Mtransservice');
                $objPHPExcel->getProperties()->setTitle('Акт');
                $objPHPExcel->getProperties()->setSubject('Акт');
                $objPHPExcel->getProperties()->setDescription('');
                $objPHPExcel->getProperties()->setCategory('');
                $objPHPExcel->removeSheetByIndex(0);

                //adding worksheet
                $worksheet = new PHPExcel_Worksheet($objPHPExcel, 'акт');
                $objPHPExcel->addSheet($worksheet);

                $worksheet->getPageMargins()->setTop(2);
                $worksheet->getPageMargins()->setLeft(0.5);
                $worksheet->getRowDimension(1)->setRowHeight(1);
                $worksheet->getRowDimension(10)->setRowHeight(120);
                $worksheet->getColumnDimension('A')->setWidth(2);
                $worksheet->getDefaultRowDimension()->setRowHeight(20);

                //headers;
                $monthName = DateHelper::getMonthName($this->time);

                $worksheet->getStyle('B2:I4')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                ));
                $worksheet->mergeCells('B2:I2');
                if($company->is_split) {
                    $worksheet->mergeCells('B2:J2');
                }
                $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)";
                $worksheet->setCellValue('B2', $text);
                $worksheet->mergeCells('B3:I3');
                $text = "по договору на оказание услуг " . $company->getRequisitesByType($this->serviceType, 'contract');
                $worksheet->setCellValue('B3', $text);
                $worksheet->mergeCells('B4:I4');
                $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
                $worksheet->setCellValue('B4', $text);

                $worksheet->setCellValue('B5', 'г.Воронеж');
                $worksheet->getStyle('H5:I5')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    )
                ));
                $worksheet->mergeCells('H5:I5');
                if($company->is_split) {
                    $worksheet->mergeCells('H5:J5');
                }
                $worksheet->setCellValue('H5', date("t ", $this->time) . $monthName[1] . date(' Y', $this->time));

                $worksheet->mergeCells('B8:I8');
                $worksheet->mergeCells('B7:I7');
                if ($this->company) {
                    $worksheet->setCellValue('B8', "Исполнитель: ООО «Международный Транспортный Сервис»");
                    $worksheet->setCellValue('B7', "Заказчик: $company->name");
                } else {
                    $worksheet->setCellValue('B7', "Исполнитель: $company->name");
                    $worksheet->setCellValue('B8', "Заказчик: ООО «Международный Транспортный Сервис»");
                }

                $worksheet->mergeCells('B10:I10');
                $worksheet->getStyle('B10:I10')->getAlignment()->setWrapText(true);
                $worksheet->getStyle('B10:I10')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                    )
                ));
                $worksheet->setCellValue('B10', $company->getRequisitesByType($this->serviceType, 'header'));
                $worksheet->getRowDimension(10)->setRowHeight(-1);

                $worksheet->getDefaultStyle()->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    )
                ));
                $worksheet->getColumnDimension('B')->setWidth(11);
                $worksheet->getColumnDimension('C')->setWidth(11);
                $worksheet->getColumnDimension('D')->setWidth(11);
                $worksheet->getColumnDimension('E')->setWidth(11);
                $worksheet->getColumnDimension('F')->setWidth(11);
                $worksheet->getColumnDimension('G')->setWidth(11);
                $worksheet->getColumnDimension('H')->setWidth(11);
                $worksheet->getColumnDimension('I')->setWidth(11);
            }

            $company = $newCompany;
            $companyId = $company->id;
            $act = $newAct;

            $row++;
            $num = 0;

            $worksheet->mergeCells("B$row:C$row");
            $worksheet->setCellValue("B$row", "ЧИСЛО");
            $worksheet->mergeCells("D$row:E$row");
            $worksheet->setCellValue("D$row", "№ КАРТЫ");
            $worksheet->setCellValue("F$row", "МАРКА ТС");
            if ($this->company) {
                $worksheet->mergeCells("H$row:I$row");
                $worksheet->setCellValue("G$row", "ГОСНОМЕР");
                $worksheet->setCellValue("H$row", "ГОРОД");
            } else {
                $worksheet->mergeCells("G$row:I$row");
                $worksheet->setCellValue("G$row", "ГОСНОМЕР");
            }
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
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
            $worksheet->mergeCells("B$row:C$row");
            $worksheet->setCellValueByColumnAndRow(1, $row, date('j', $act->served_at));
            $worksheet->mergeCells("D$row:E$row");
            $worksheet->setCellValueByColumnAndRow(3, $row, isset($act->card) ? $act->card->number : $act->card_id);
            $worksheet->setCellValueByColumnAndRow(5, $row, isset($act->mark) ? $act->mark->name : "");
            if ($this->company) {
                $worksheet->mergeCells("H$row:I$row");
                $worksheet->setCellValueByColumnAndRow(6, $row, $act->number);
                $worksheet->setCellValueByColumnAndRow(7, $row, $act->partner->address);
            } else {
                $worksheet->mergeCells("G$row:I$row");
                $worksheet->setCellValueByColumnAndRow(6, $row, $act->number);
            }
            $worksheet->getStyle("B$row:I$row")
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

            $row++;
            $worksheet->mergeCells("B$row:F$row");
            $worksheet->setCellValue("B$row", "Вид услуг");
            $worksheet->setCellValue("G$row", "Кол-во");
            $worksheet->setCellValue("H$row", "Стоимость");
            $worksheet->setCellValue("I$row", "Сумма");
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            $total = 0;
            $subtotal = 0;
            $count = 0;
            if ($this->company) {
                $scopeList = $act->clientScopes;
            } else {
                $scopeList = $act->partnerScopes;
            }
            /** @var ActScope $scope */
            foreach ($scopeList as $scope) {
                $row++;
                $num++;
                $worksheet->mergeCells("B$row:F$row");
                $worksheet->setCellValue("B$row", "$num. $scope->description");
                $worksheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                if (mb_strlen($scope->description) > 55) {
                    $worksheet->getRowDimension($row)->setRowHeight(-1);
                }
                $worksheet->setCellValue("G$row", $scope->amount);
                $worksheet->setCellValue("H$row", $scope->price);
                $worksheet->setCellValue("I$row", $scope->price * $scope->amount);
                $total += $scope->amount * $scope->price;
                $subtotal += $scope->price;
                $count += $scope->amount;
            }
            $row++;
            $worksheet->mergeCells("B$row:F$row");
            $worksheet->setCellValue("B$row", "Итого:");
            $worksheet->setCellValue("G$row", $count);
            $worksheet->setCellValue("H$row", $subtotal);
            $worksheet->setCellValue("I$row", $total);
            $worksheet->getStyle("B$row:I$row")->applyFromArray(array(
                    'font' => array(
                        'bold' => true,
                    ),
                )
            );

            $worksheet->getStyle("B13:I$row")
                ->applyFromArray(array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('argb' => 'FF000000'),
                            ),
                        ),
                    )
                );

            $fullTotal += $total;
            $fullCount += $count;
        }
    }

    /**
     * @param yii\data\ActiveDataProvider $dataProvider
     * @param ZipArchive $zip
     */
    private function generateDisinfectAct($dataProvider, &$zip)
    {
        /** @var Company $company */
        $company = null;
        /** @var Act $act */
        $act = null;
        $companyId = 0;

        /** @var PHPExcel $objPHPExcel */
        $objPHPExcel = null;
        /** @var PHPExcel_Writer_IWriter $objWriter */
        $objWriter = null;
        /** @var PHPExcel_Worksheet $worksheet */
        $worksheet = null;

        $row = 12;
        $num = 0;
        $total = 0;
        $count = 0;
        /**
         * @var Act[] $listData
         */
        $listData = $dataProvider->getModels();
        for ($i = 0; $i <= count($listData); $i++) {
            $newAct = !empty($listData[$i]) ? $listData[$i] : null;
            $newCompany = $newAct ? ($this->company ? $newAct->client : $newAct->partner) : null;

            if (!$newCompany || $companyId != $newCompany->id) {
                if ($company) {
                    $worksheet->getStyle('B12:F12')->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'color' => array('argb' => 'FF006699'),
                            ),
                        )
                    );
                    if($company->is_split) {
                        $worksheet->getStyle('G12')->applyFromArray(array(
                                'font' => array(
                                    'bold' => true,
                                    'color' => array('argb' => 'FF006699'),
                                ),
                            )
                        );
                    }

                    $worksheet->getStyle("B12:F$row")
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
                        $worksheet->getStyle("J12:G$row")
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

                    //footer
                    $row++;
                    if($company->is_split) {
                        $worksheet->setCellValue("F$row", "ВСЕГО:");
                        $worksheet->setCellValue("G$row", "$total");
                    } else {
                        $worksheet->setCellValue("E$row", "ВСЕГО:");
                        $worksheet->setCellValue("F$row", "$total");
                    }

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:F$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("B$row:G$row");
                    }
                    $worksheet->getRowDimension($row)->setRowHeight(30);
                    $worksheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                    $text = "Общая стоимость выполненных услуг составляет: $total (" . DigitHelper::num2str($total) . ") рублей. НДС нет.";
                    $worksheet->setCellValue("B$row", $text);

                    $row++;
                    $worksheet->mergeCells("B$row:F$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("B$row:G$row");
                    }
                    $worksheet->getRowDimension($row)->setRowHeight(30);
                    $worksheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                    $text = "Настоящий Акт составлен в 2 (двух) экземплярах, один из которых находится у Исполнителя, второй – у Заказчика.";
                    $worksheet->setCellValue("B$row", $text);

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:C$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("F$row:G$row");
                    }
                    if ($this->company) {
                        $worksheet->setCellValue("B$row", "Работу сдал");
                        $worksheet->setCellValue("F$row", "Работу принял");
                    } else {
                        $worksheet->setCellValue("B$row", "Работу принял");
                        $worksheet->setCellValue("E$row", "Работу сдал");
                    }

                    $row++; $row++;
                    $worksheet->mergeCells("B$row:C$row");
                    if($company->is_split) {
                        $worksheet->mergeCells("F$row:G$row");
                    }
                    if ($this->company) {
                        $worksheet->setCellValue("B$row", "Исполнитель");
                        $worksheet->setCellValue("F$row", "Заказчик");
                    } else {
                        $worksheet->setCellValue("B$row", "Заказчик");
                        $worksheet->setCellValue("F$row", "Исполнитель");
                    }

                    $row++;
                    //подпись
                    $signImage = imagecreatefromjpeg('images/sign.jpg');
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setName('Sample image');
                    $objDrawing->setDescription('Sample image');
                    $objDrawing->setImageResource($signImage);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setCoordinates("B$row");
                    $objDrawing->setWorksheet($worksheet);
                    $row++;

                    $worksheet->mergeCells("C$row:D$row");
                    $worksheet->getStyle("C$row:D$row")->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                    ));
                    if($company->is_split) {
                        $worksheet->mergeCells("F$row:G$row");
                    }
                    $worksheet->setCellValue("C$row", "Мосесян Г.А.");
                    $worksheet->setCellValue("F$row", "________$company->director");

                    $row++; $row++;
                    //печать
                    $gdImage = imagecreatefromjpeg('images/post.jpg');
                    $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $objDrawing->setName('Sample image');
                    $objDrawing->setDescription('Sample image');
                    $objDrawing->setImageResource($gdImage);
                    $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $objDrawing->setCoordinates("C$row");
                    $objDrawing->setWorksheet($worksheet);

                    $worksheet->setCellValue("F$row", "М.П.");

                    //saving document
                    $type = Service::$listType[$this->serviceType]['en'];
                    $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
                    if (!is_dir($path)) {
                        mkdir($path, 0755, 1);
                    }
                    $filename = "Акт $company->name от " . date('m-Y', $this->time) . ".xlsx";
                    $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
                    $objWriter->save($fullFilename);
                    if ($zip) $zip->addFile($fullFilename, $filename);

                    if ($this->company) {
                        $this->generateCheck($act, $zip, $count, $total);
                    }
                }

                if (!$newAct) {
                    continue;
                }

                $company = $newCompany;
                $act = $newAct;
                $companyId = $company->id;

                $objPHPExcel = new PHPExcel();
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

                // Creating a workbook
                $objPHPExcel->getProperties()->setCreator('Mtransservice');
                $objPHPExcel->getProperties()->setTitle('Акт');
                $objPHPExcel->getProperties()->setSubject('Акт');
                $objPHPExcel->getProperties()->setDescription('');
                $objPHPExcel->getProperties()->setCategory('');
                $objPHPExcel->removeSheetByIndex(0);

                //adding worksheet
                $worksheet = new PHPExcel_Worksheet($objPHPExcel, 'акт');
                $objPHPExcel->addSheet($worksheet);

                $worksheet->getPageMargins()->setTop(2);
                $worksheet->getPageMargins()->setLeft(0.5);
                $worksheet->getRowDimension(1)->setRowHeight(1);
                $worksheet->getRowDimension(10)->setRowHeight(120);
                $worksheet->getColumnDimension('A')->setWidth(2);
                $worksheet->getDefaultRowDimension()->setRowHeight(20);

                //headers;
                $monthName = DateHelper::getMonthName($this->time);

                $worksheet->getStyle('B2:F4')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    )
                ));
                $worksheet->mergeCells('B2:F2');
                if($company->is_split) {
                    $worksheet->mergeCells('B2:G2');
                }
                $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)";
                $worksheet->setCellValue('B2', $text);
                $worksheet->mergeCells('B3:F3');
                $text = "по договору на оказание услуг " . $company->getRequisitesByType($this->serviceType, 'contract');
                $worksheet->setCellValue('B3', $text);
                $worksheet->mergeCells('B4:F4');
                $text = "За услуги, оказанные в $monthName[2] " . date('Y', $this->time) . ".";
                $worksheet->setCellValue('B4', $text);

                $worksheet->setCellValue('B5', 'г.Воронеж');
                $worksheet->getStyle('F5')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    )
                ));
                if($company->is_split) {
                    $worksheet->mergeCells('F5:G5');
                }
                $worksheet->setCellValue('F5', date('t ', $this->time) . $monthName[1] . date(' Y', $this->time));

                $worksheet->mergeCells('B8:F8');
                $worksheet->mergeCells('B7:F7');
                if ($this->company) {
                    $worksheet->setCellValue('B8', "Исполнитель: ООО «Международный Транспортный Сервис»");
                    $worksheet->setCellValue('B7', "Заказчик: $company->name");
                } else {
                    $worksheet->setCellValue('B7', "Исполнитель: $company->name");
                    $worksheet->setCellValue('B8', "Заказчик: ООО «Международный Транспортный Сервис»");
                }

                $worksheet->mergeCells('B10:F10');
                $worksheet->getStyle('B10:F10')->getAlignment()->setWrapText(true);
                $worksheet->getStyle('B10:F10')->applyFromArray(array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                    )
                ));
                $worksheet->setCellValue('B10', $company->getRequisitesByType($this->serviceType, 'header'));
                $worksheet->getRowDimension(10)->setRowHeight(-1);

                $row = 12;
                $num = 0;
                $total = 0;
                $count = 0;

                $worksheet->getColumnDimension('B')->setWidth(5);
                $worksheet->getColumnDimension('C')->setAutoSize(true);
                $worksheet->getColumnDimension('D')->setAutoSize(true);
                $worksheet->getColumnDimension('E')->setAutoSize(true);
                $worksheet->getColumnDimension('F')->setAutoSize(true);
                if($company->is_split) {
                    $worksheet->getColumnDimension('G')->setAutoSize(true);
                }

                $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
                if($company->is_split) {
                    $headers = ['№', 'Марка ТС', 'Госномер', 'Прицеп', 'Вид услуги', 'Стоимость'];
                }
                $worksheet->fromArray($headers, null, 'B12');
            }

            $company = $newCompany;
            $companyId = $company->id;
            $act = $newAct;

            $row++;
            $num++;
            $column = 1;
            $worksheet->setCellValueByColumnAndRow($column++, $row, $num);
            $worksheet->setCellValueByColumnAndRow($column++, $row, isset($act->mark) ? $act->mark->name : "");
            $worksheet->setCellValueByColumnAndRow($column++, $row, $act->number);
            if($company->is_split) {
                $worksheet->setCellValueByColumnAndRow($column++, $row, $act->extra_number);
            }
            $worksheet->setCellValueByColumnAndRow($column++, $row, 'Санитарная обработка кузова');
            if ($this->company) {
                $worksheet->setCellValueByColumnAndRow($column++, $row, $act->income);
                $total += $act->income;
            } else {
                $worksheet->setCellValueByColumnAndRow($column++, $row, $act->expense);
                $total += $act->expense;
            }
            $count++;
        }
    }

    /**
     * @param $act Act
     * @param $zip ZipArchive
     * @param $count int
     * @param $sum int
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    private function generateCheck($act, &$zip, $count, $sum)
    {
        $objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        // Creating a workbook
        $objPHPExcel->getProperties()->setCreator('Mtransservice');
        $objPHPExcel->getProperties()->setTitle('Счет');
        $objPHPExcel->getProperties()->setSubject('Счет');
        $objPHPExcel->getProperties()->setDescription('');
        $objPHPExcel->getProperties()->setCategory('');
        $objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $worksheet = new PHPExcel_Worksheet($this->objPHPExcel, 'Счет');
        $objPHPExcel->addSheet($worksheet);

        $worksheet->getRowDimension(1)->setRowHeight(100);
        $worksheet->getColumnDimension('A')->setWidth(5);
        $worksheet->getColumnDimension('B')->setWidth(20);
        $worksheet->getColumnDimension('C')->setWidth(20);
        $worksheet->getColumnDimension('D')->setWidth(10);
        $worksheet->getColumnDimension('E')->setWidth(30);
        $worksheet->getDefaultRowDimension()->setRowHeight(20);

        //headers
        $monthName = DateHelper::getMonthName($this->time);

        $worksheet->mergeCells('B2:E2');
        $worksheet->setCellValue('B2', "ООО «Международный Транспортный Сервис»");

        $worksheet->mergeCells('B3:E3');
        $worksheet->setCellValue('B3', "Адрес: 394065, г. Воронеж, ул. Героев Сибиряков, д. 24, оф. 116");

        $worksheet->getStyle("B5")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->setCellValue('B5', 'ИНН 366 510 0480');

        $worksheet->getStyle("C5")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->setCellValue('C5', 'КПП 366 501 001');

        $worksheet->mergeCells('B6:C6');
        $worksheet->getStyle("B6:C6")->getAlignment()->setWrapText(true);
        $worksheet->getStyle("B6:C6")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->getRowDimension(6)->setRowHeight(40);
        $worksheet->setCellValue('B6', 'Получатель:ООО«Международный Транспортный Сервис»');

        $worksheet->mergeCells('D5:D6');
        $worksheet->getStyle("D5:D6")->applyFromArray(array(
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
        $worksheet->setCellValue('D5', 'Сч.№');

        $worksheet->mergeCells('E5:E6');
        $worksheet->getStyle("E5:E6")->applyFromArray(array(
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
        $worksheet->getStyle('E5:E6')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $worksheet->setCellValue('E5', ' 40702810913000016607');

        $worksheet->mergeCells('B7:C8');
        $worksheet->getStyle("B7:C8")->getAlignment()->setWrapText(true);
        $worksheet->getStyle("B7:C8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->setCellValue('B7', 'Банк получателя: Центрально-Черноземный Банк ОАО «Сбербанк России»  г. Воронеж');

        $worksheet->getStyle("D7")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->setCellValue('D7', 'БИК');

        $worksheet->getStyle("E7")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->getStyle('E7')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $worksheet->setCellValue('E7', ' 042007681');

        $worksheet->getStyle("D8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->setCellValue('D8', 'К/сч.№');

        $worksheet->getStyle("E8")->applyFromArray(array(
                'borders' => array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => 'FF000000'),
                    ),
                ),
            )
        );
        $worksheet->getStyle('E8')
            ->getNumberFormat()
            ->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_TEXT
            );
        $worksheet->setCellValue('E8', ' 30101810600000000681');

        $row = 9;
        $row++;
        $worksheet->mergeCells("B$row:E$row");
        $worksheet->getStyle("B$row:E$row")->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );
        if ($this->serviceType == Company::TYPE_SERVICE) {
            $text = "СЧЕТ б/н от " . date("d ", $act->served_at) . ' ' . $monthName[1] . date(' Y', $this->time);
        } else {
            $text = "СЧЕТ б/н от " . date("t ", $this->time) . ' ' . $monthName[1] . date(' Y', $this->time);
        }
        $worksheet->setCellValue("B$row", $text);

        $row++;
        $worksheet->mergeCells("B$row:E$row");
        $worksheet->getStyle("B$row:E$row")->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            )
        );
        $text = 'За услуги, оказанные в ' . $monthName[2] . date(' Y');
        $worksheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $worksheet->mergeCells("B$row:E$row");
        $text = "Плательщик: {$act->client->name}";
        $worksheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $worksheet->mergeCells("B$row:E$row");
        $worksheet->getRowDimension($row)->setRowHeight(40);
        $worksheet->getStyle("B$row:E$row")->getAlignment()->setWrapText(true);
        $text = "Всего наименований " . $count . ", на сумму $sum (" . DigitHelper::num2str($sum) . "). НДС нет.";
        $worksheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $worksheet->mergeCells("B$row:E$row");
        $worksheet->setCellValue("B$row", 'Мосесян Г.А.');

        //подпись
        $signImage = imagecreatefromjpeg('images/sign.jpg');
        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Sample image');
        $objDrawing->setDescription('Sample image');
        $objDrawing->setImageResource($signImage);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setCoordinates("C$row");
        $objDrawing->setWorksheet($worksheet);
        //печать
        $gdImage = imagecreatefromjpeg('images/post.jpg');
        $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
        $objDrawing->setName('Sample image');
        $objDrawing->setDescription('Sample image');
        $objDrawing->setImageResource($gdImage);
        $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
        $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
        $objDrawing->setCoordinates("D$row");
        $objDrawing->setWorksheet($worksheet);

        //saving document
        $type = Service::$listType[$this->serviceType]['en'];
        $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::TYPE_SERVICE) {
            $filename = "Счет {$act->client->name} - $act->number.$act->id от " . date('d-m-Y', $act->served_at) . ".xls";
        } else {
            $filename = "Счет {$act->client->name} от " . date('m-Y', $this->time) . ".xlsx";
        }
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, $filename);
    }

    /**
     * @param yii\data\ActiveDataProvider $dataProvider
     * @param ZipArchive $zip
     */
    private function generateDisinfectCertificate($dataProvider, &$zip)
    {
        $cols = ['A','B','C','D','E','F','G','H','I','J','K'];
        /** @var Company $company */
        $company = null;
        /** @var Act $act */
        $act = null;
        $companyId = 0;

        /** @var PHPExcel $objPHPExcel */
        $objPHPExcel = null;
        /** @var PHPExcel_Writer_IWriter $objWriter */
        $objWriter = null;
        /** @var PHPExcel_Worksheet $worksheet */
        $worksheet = null;

        /**
         * @var Act[] $listData
         */
        $listData = $dataProvider->getModels();
        for ($i = 0; $i <= count($listData); $i++) {
            $newAct = !empty($listData[$i]) ? $listData[$i] : null;
            $newCompany = $newAct ? $newAct->client : null;

            if (!$newCompany || $companyId != $newCompany->id) {
                if ($company) {
                    $type = Service::$listType[$this->serviceType]['en'];
                    $path = "files/acts/" . ($this->company ? 'client' : 'partner') . "/$type/" . date('m-Y', $this->time);
                    if (!is_dir($path)) {
                        mkdir($path, 0755, 1);
                    }
                    $filename = "Справка $company->name от " . date('m-Y', $this->time) . ".xlsx";
                    $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
                    $objWriter->save($fullFilename);
                    if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));
                }

                if (!$newAct) {
                    continue;
                }

                $company = $newCompany;
                $act = $newAct;
                $companyId = $company->id;

                $cnt = 1;
                $startRow = 8;

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

                $worksheet->getPageMargins()->setTop(0.3);
                $worksheet->getPageMargins()->setLeft(0.5);
                $worksheet->getPageMargins()->setRight(0.5);
                $worksheet->getPageMargins()->setBottom(0.3);

                $objPHPExcel->getDefaultStyle()->applyFromArray(array(
                    'font' => array(
                        'size' => 10,
                    )
                ));

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

            $company = $newCompany;
            $act = $newAct;

            $endDate = new \DateTime();
            $endDate->setTimestamp($act->served_at);
            $startDate = clone($endDate);
            date_add($endDate, date_interval_create_from_date_string("1 month"));

            $startCol = 0;
            if ($cnt == 2 || $cnt == 4) {
                $startCol = 6;
            }
            if ($cnt == 3) {
                $startRow += 26;
            }
            $row = $startRow;

            $signImage = imagecreatefromjpeg('images/top.jpg');
            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setImageResource($signImage);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $range = $cols[$startCol] . ($row - 7);
            $objDrawing->setCoordinates($range);
            $objDrawing->setWorksheet($worksheet);

            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $worksheet->mergeCells($range);
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'СПРАВКА');

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->getStyle($range)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $worksheet->mergeCells($range);
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'о проведении дезинфекции транспорта');

            $row++;

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Выдана');
            $range = $cols[$startCol + 1] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->mergeCells($range);
            $worksheet->getStyle($range)->getAlignment()->setWrapText(true);
            $worksheet->setCellValueByColumnAndRow($startCol + 1, $row, $company->name);
            $worksheet->getRowDimension($row)->setRowHeight(-1);

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Марка');
            $worksheet->setCellValueByColumnAndRow($startCol + 1, $row, $act->mark->name);
            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray(array(
                    'font' => array(
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Гос. номер');
            $worksheet->setCellValueByColumnAndRow($startCol + 1, $row, $act->number);
            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray(array(
                    'font' => array(
                        'color' => array('argb' => 'FF006699'),
                    ),
                )
            );

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Срок действия справки 1 (один) месяц');

            $text = "C " . $startDate->format('01.m.Y') . " по " . $endDate->format('01.m.Y');
            $worksheet->setCellValueByColumnAndRow($startCol, $row, $text);

            $row++;

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Региональный директор');

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'ООО «Международный Транспортный Сервис»');

            $row++;
            $worksheet->setCellValueByColumnAndRow($startCol, $row, 'Мосесян Г.А.');
            $signImage = imagecreatefromjpeg('images/post-small.jpg');
            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setImageResource($signImage);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $range = $cols[$startCol + 2] . $row;
            $objDrawing->setCoordinates($range);
            $objDrawing->setWorksheet($worksheet);
            $signImage = imagecreatefromjpeg('images/sign.jpg');
            $objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
            $objDrawing->setName('Sample image');
            $objDrawing->setDescription('Sample image');
            $objDrawing->setImageResource($signImage);
            $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
            $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
            $range = $cols[$startCol + 1] . ($row + 1);
            $objDrawing->setCoordinates($range);
            $objDrawing->setWorksheet($worksheet);

            $row += 5;

            $row++;
            $range = $cols[$startCol] . $row . ':' . $cols[$startCol + 3] . $row;
            $worksheet->mergeCells($range);
            $worksheet->getStyle($range)->getAlignment()->setWrapText(true);
            $text = "ИНН 3665100480 КПП 366501001 ОГРН 1143668022266 394065, Россия, Воронежская область," .
                "г. Воронеж, ул. Героев Сибиряков, д. 24, кв. 116 \n Тел.: 8 800 55 008 55 \n " .
                "E-Mail: mtransservice@mail.ru \n Web.: mtransservice.ru";
            $worksheet->setCellValueByColumnAndRow($startCol, $row, $text);
            $worksheet->getStyleByColumnAndRow($startCol, $row)->applyFromArray(array(
                    'font' => array(
                        'size' => 6,
                    ),
                )
            );
            $worksheet->getRowDimension($row)->setRowHeight(-1);

            $cnt++;
            if ($cnt == 5) {
                $cnt = 0;
                $startRow += mb_strlen($company->name) > 55 ? 25 : 28;
            }
        }
    }

    private function sendHeaders() {
        if ($this->headersSent === false) {
            $this->headersSent = true;

            header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
            header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
            header ( "Cache-Control: no-cache, must-revalidate" );
            header ( "Pragma: no-cache" );
            header ( "Content-type: application/vnd.ms-excel" );
            header ( "Content-Disposition: attachment; filename=" . date('Y-m',$this->time) . "-$this->filename" );
        }
    }
}