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
     * @param Act $actModel
     * @param Company $company
     * @param ZipArchive $zip
     */
    private function fillAct($actModel, $company, &$zip)
    {
        $dataList = $actModel->search()->getData();
        if (!$dataList || !$company) {
            return;
        }

        switch ($this->serviceType) {
            case Company::SERVICE_TYPE:
                foreach ($dataList as $data) {
                    $this->generateAct($company, array($data), $zip);
                }
                break;
            case Company::DISINFECTION_TYPE:
                $this->generateDisinfectionAct($company, $dataList, $zip);
            default:
                $this->generateAct($company, $dataList, $zip);
        }
    }

    /**
     * @param Company $company
     * @param Act[] $dataList
     * @param ZipArchive $zip
     */
    private function generateDisinfectionAct($company, $dataList, &$zip)
    {
        $fileType = 'Excel5';
        $fileName = 'files/dis-tpl.xls';

        // Read the file
        $objReader = PHPExcel_IOFactory::createReader($fileType);
        $this->objPHPExcel = $objReader->load($fileName);
        $this->objPHPExcel->setActiveSheetIndex(0);
        $companyWorkSheet = $this->objPHPExcel->getActiveSheet();

        $cnt = 0;
        $shts = 2;
        $files = 0;
        foreach ($dataList as $data) {
            $endDate = date_create($data->service_date);
            $startDate = clone($endDate);
            date_add($endDate, date_interval_create_from_date_string("1 month"));
            $startRow = 13;
            $startCol = 2;
            if ($cnt == 1 || $cnt == 3) {
                $startCol = 8;
            }
            if ($cnt == 2 || $cnt == 3) {
                $startRow = 40;
            }
            $companyWorkSheet->setCellValueByColumnAndRow($startCol, $startRow++, $company->name);
            $companyWorkSheet->setCellValueByColumnAndRow($startCol, $startRow++, $data->mark->name);
            $companyWorkSheet->setCellValueByColumnAndRow($startCol, $startRow++, $data->number);
            $startRow++;
            $text = "C " . $startDate->format('01.m.Y') . " по " . $endDate->format('01.m.Y');
            $companyWorkSheet->setCellValueByColumnAndRow($startCol - 1, $startRow++, $text);

            $cnt++;

            if ($cnt == 4) {
                if ($shts > 50) {
                    $files++;
                    //saving document
                    $path = "acts/" . date('m-Y', $this->time);
                    if (!is_dir($path)) {
                        mkdir($path, 0755, 1);
                    }
                    $filename = "Справка $company->name от " . date('m-Y', $this->time) . "-$files.xls";
                    $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
                    $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
                    $objWriter->save($fullFilename);
                    if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));

                    $this->objPHPExcel = $objReader->load($fileName);
                    $this->objPHPExcel->setActiveSheetIndex(0);
                    $companyWorkSheet = $this->objPHPExcel->getActiveSheet();
                } else {
                    $newCompanyWorkSheet = $companyWorkSheet->copy();
                    $newCompanyWorkSheet->setTitle('Лист ' . $shts);
                    $this->objPHPExcel->addSheet($newCompanyWorkSheet);
                    $companyWorkSheet = $newCompanyWorkSheet;
                    $cnt = 0;
                    $shts++;
                }
            }
        }

        //saving document
        $path = "acts/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        $filename = $files ? "Справка $company->name от " . date('m-Y', $this->time) . "-" . ++$files . ".xls" : "Справка $company->name от " . date('m-Y', $this->time) . ".xls";
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));
    }

    /**
     * @param Company $company
     * @param array $dataList
     * @param ZipArchive $zip
     */
    private function generateAct($company, $dataList, &$zip)
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
        $companyWorkSheet->getRowDimension(10)->setRowHeight(120);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers;
        $monthName = StringNum::getMonthName($this->time);
        $date = date_create(date('Y-m-d', $this->time));
        date_add($date, date_interval_create_from_date_string("1 month"));
        $currentMonthName = StringNum::getMonthName($date->getTimestamp());

        if ($this->serviceType == Company::DISINFECTION_TYPE) {
            $companyWorkSheet->getStyle('B2:F4')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                )
            ));
            $companyWorkSheet->mergeCells('B2:F2');
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)";
            $companyWorkSheet->setCellValue('B2', $text);
            $companyWorkSheet->mergeCells('B3:F3');
            $text = "по договору на оказание услуг " . $company->getRequisites($this->serviceType, 'contract');
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

            $companyWorkSheet->mergeCells('B8:F8');
            $companyWorkSheet->mergeCells('B7:F7');
            if ($this->company) {
                $companyWorkSheet->setCellValue('B8', "Исполнитель: ООО «Международный Транспортный Сервис»");
                $companyWorkSheet->setCellValue('B7', "Заказчик: $company->name");
            } else {
                $companyWorkSheet->setCellValue('B7', "Исполнитель: $company->name");
                $companyWorkSheet->setCellValue('B8', "Заказчик: ООО «Международный Транспортный Сервис»");
            }

            $companyWorkSheet->mergeCells('B10:F10');
            $companyWorkSheet->getStyle('B10:F10')->getAlignment()->setWrapText(true);
            $companyWorkSheet->getStyle('B10:F10')->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                )
            ));
            $companyWorkSheet->setCellValue('B10', $company->getRequisites($this->serviceType, 'header'));
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
            $text = "АКТ СДАЧИ-ПРИЕМКИ РАБОТ (УСЛУГ)";
            $companyWorkSheet->setCellValue('B2', $text);
            $companyWorkSheet->mergeCells('B3:I3');
            $text = "по договору на оказание услуг " . $company->getRequisites($this->serviceType, 'contract');
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
            if ($this->company || $this->serviceType == Company::TIRES_TYPE) {
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
            $companyWorkSheet->setCellValue('B10', $company->getRequisites($this->serviceType, 'header'));
        }


        //main values
        $row = 12;
        $num = 0;
        $total = 0;
        $count = 0;
        switch($this->serviceType) {
            case Company::SERVICE_TYPE:
                $first = $dataList[0];
                $companyWorkSheet->setCellValue('H5', date("d ", strtotime($first->service_date)) . $monthName[1] . date(' Y', $this->time));
            case Company::TIRES_TYPE:
                $first = $dataList[0];

                $row = 11;

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
                        $companyWorkSheet->mergeCells("H$row:I$row");
                        $companyWorkSheet->setCellValue("G$row", "ГОСНОМЕР");
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
                    $date = new DateTime($data->service_date);
                    $companyWorkSheet->mergeCells("B$row:C$row");
                    $companyWorkSheet->setCellValueByColumnAndRow(1, $row, $date->format('j'));
                    $companyWorkSheet->mergeCells("D$row:E$row");
                    $companyWorkSheet->setCellValueByColumnAndRow(3, $row, isset($data->card) ? $data->card->number : $data->card_id);
                    $companyWorkSheet->setCellValueByColumnAndRow(5, $row, isset($data->mark) ? $data->mark->name : "");
                    if ($this->company) {
                        $companyWorkSheet->mergeCells("H$row:I$row");
                        $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->number);
                        $companyWorkSheet->setCellValueByColumnAndRow(7, $row, $data->partner->address);
                    } else {
                        $companyWorkSheet->mergeCells("G$row:I$row");
                        $companyWorkSheet->setCellValueByColumnAndRow(6, $row, $data->number);
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

                    $row++;
                    $companyWorkSheet->mergeCells("B$row:F$row");
                    $companyWorkSheet->setCellValue("B$row", "Вид услуг");
                    $companyWorkSheet->setCellValue("G$row", "Кол-во");
                    $companyWorkSheet->setCellValue("H$row", "Стоимость");
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
                    foreach ($data->scope as $scope) {
                        $row++;
                        $num++;
                        $companyWorkSheet->mergeCells("B$row:F$row");
                        $companyWorkSheet->setCellValue("B$row", "$num. $scope->description");
                        $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
                        if (mb_strlen($scope->description) > 55) {
                            $companyWorkSheet->getRowDimension($row)->setRowHeight(40);
                        }
                        $companyWorkSheet->setCellValue("G$row", $scope->amount);
                        if ($this->company) {
                            $companyWorkSheet->setCellValue("H$row", $scope->income);
                            $companyWorkSheet->setCellValue("I$row", $scope->income * $scope->amount);
                            $total += $scope->amount * $scope->income;
                            $subtotal += $scope->amount * $scope->income;
                        } else {
                            $companyWorkSheet->setCellValue("H$row", $scope->expense);
                            $companyWorkSheet->setCellValue("I$row", $scope->expense * $scope->amount);
                            $total += $scope->amount * $scope->expense;
                            $subtotal += $scope->amount * $scope->expense;
                        }
                        $subcount += $scope->amount;
                        $count += $scope->amount;
                    }
                    $row++;
                    $companyWorkSheet->mergeCells("B$row:F$row");
                    $companyWorkSheet->setCellValue("B$row", "Итого:");
                    $companyWorkSheet->setCellValue("G$row", $subcount);
                    $companyWorkSheet->setCellValue("H$row", $subtotal);
                    $companyWorkSheet->setCellValue("I$row", $subtotal);
                    $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                            ),
                        )
                    );

                    $companyWorkSheet->getStyle("B13:I$row")
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

            case Company::CARWASH_TYPE:
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
                    $date = new DateTime($data->service_date);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $date->format('j'));
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->card) ? $data->card->number : $data->card_id);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->number);
                    if($company->is_split) {
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->extra_number);
                    }
                    if ($this->company) {
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, Act::$fullList[$data->client_service]);
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->income);
                        $total += $data->income;
                    } else {
                        $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, Act::$fullList[$data->partner_service]);
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

                $companyWorkSheet->getStyle('B12:I12')->applyFromArray(array(
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

                $companyWorkSheet->getStyle("B12:I$row")
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

            case Company::DISINFECTION_TYPE:
                $companyWorkSheet->getColumnDimension('B')->setWidth(5);
                $companyWorkSheet->getColumnDimension('C')->setWidth(20);
                $companyWorkSheet->getColumnDimension('D')->setWidth(20);
                $companyWorkSheet->getColumnDimension('E')->setWidth(26);
                $companyWorkSheet->getColumnDimension('F')->setWidth(15);

                $headers = ['№', 'Марка ТС', 'Госномер', 'Вид услуги', 'Стоимость'];
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
                    $date = new DateTime($data->service_date);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $num);
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, isset($data->mark) ? $data->mark->name : "");
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, $data->number);
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
                    $companyWorkSheet->setCellValueByColumnAndRow($column++, $row, ' ' . $data->check);
                }

                $companyWorkSheet->getStyle('B12:F12')
                    ->applyFromArray(array(
                            'font' => array(
                                'bold' => true,
                                'color' => array('argb' => 'FF006699'),
                            ),
                        )
                    );

                $companyWorkSheet->getStyle("B12:F$row")
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
        if ($this->serviceType == Company::DISINFECTION_TYPE) {
            $row++;
            $companyWorkSheet->setCellValue("F$row", "$total");

            $row++;$row++;
            $companyWorkSheet->mergeCells("B$row:F$row");
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:F$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $total (" . StringNum::num2str($total) . ") рублей. НДС нет.";
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

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "_______Мосесян Г.А.");

            $companyWorkSheet->mergeCells("E$row:F$row");
            $companyWorkSheet->setCellValue("E$row", "_______$company->contact");

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "М.П.");
            $companyWorkSheet->setCellValue("E$row", "М.П.");
        } else {
            $row++;
            if ($this->serviceType == Company::CARWASH_TYPE) {
                if($company->is_split) {
                    $companyWorkSheet->setCellValue("H$row", "ВСЕГО:");
                    $companyWorkSheet->setCellValue("I$row", "$total");
                } else {
                    $companyWorkSheet->setCellValue("G$row", "ВСЕГО:");
                    $companyWorkSheet->setCellValue("H$row", "$total");
                }
            } else {
                $companyWorkSheet->setCellValue("F$row", "ВСЕГО:");
                $companyWorkSheet->setCellValue("G$row", "$count");
                $companyWorkSheet->setCellValue("H$row", "$total");
                $companyWorkSheet->setCellValue("I$row", "$total");
                $companyWorkSheet->getStyle("B$row:I$row")->applyFromArray(array(
                        'font' => array(
                            'bold' => true,
                            'size' => 12,
                        ),
                    )
                );
            }

            $row++; $row++;
            $companyWorkSheet->mergeCells("B$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("B$row:J$row");
            }
            $companyWorkSheet->getRowDimension($row)->setRowHeight(30);
            $companyWorkSheet->getStyle("B$row:I$row")->getAlignment()->setWrapText(true);
            $text = "Общая стоимость выполненных услуг составляет: $total (" . StringNum::num2str($total) . ") рублей. НДС нет.";
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


            $row++; $row++;

            $companyWorkSheet->mergeCells("B$row:E$row");
            $companyWorkSheet->mergeCells("G$row:I$row");
            if($company->is_split) {
                $companyWorkSheet->mergeCells("G$row:J$row");
            }
            $companyWorkSheet->setCellValue("B$row", "______Мосесян Г.А.");
            $companyWorkSheet->setCellValue("G$row", "______$company->contact");

            $row++; $row++;
            $companyWorkSheet->setCellValue("B$row", "М.П.");
            $companyWorkSheet->setCellValue("G$row", "М.П.");
        }

        //saving document
        $path = "acts/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::SERVICE_TYPE) {
            $first = $dataList[0];
            $filename = "Акт $company->name - $first->number от " . date('d-m-Y', strtotime($first->service_date)) . ".xls";
        } else {
            $filename = "Акт $company->name от " . date('m-Y', $this->time) . ".xls";
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
        $monthName = StringNum::getMonthName($this->time);
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
        if ($this->serviceType != Company::CARWASH_TYPE) {
            $first = $dataList[0];
            $text = "СЧЕТ б/н от " . date("d ", strtotime($first->service_date)) . ' ' . $monthName[1] . date(' Y', $this->time);
        } else {
            $text = "СЧЕТ б/н от " . date("t", $this->time) . ' ' . $monthName[1] . date(' Y', $this->time);
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
        $text = 'За услуги, оказанные в ' . $monthName[2] . date(' Y');
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
        $text = "Всего наименований " .count($dataList) . ", на сумму $total (" . StringNum::num2str($total) . "). НДС нет.";
        $companyWorkSheet->setCellValue("B$row", $text);

        $row++;
        $row++;
        $companyWorkSheet->mergeCells("B$row:E$row");
        $companyWorkSheet->setCellValue("B$row", 'Мосесян Г.А.');

        //saving document
        $path = "acts/" . date('m-Y', $this->time);
        if (!is_dir($path)) {
            mkdir($path, 0755, 1);
        }
        if ($this->serviceType == Company::SERVICE_TYPE) {
            $first = $dataList[0];
            $filename = "Счет $company->name - $first->number от " . date('d-m-Y', strtotime($first->service_date)) . ".xls";
        } else {
            $filename = "Счет $company->name от " . date('m-Y', $this->time) . ".xls";
        }
        $fullFilename = str_replace(' ', '_', "$path/" . str_replace('"', '', "$filename"));
        $objWriter->save($fullFilename);
        if ($zip) $zip->addFile($fullFilename, iconv('utf-8', 'cp866', $filename));
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