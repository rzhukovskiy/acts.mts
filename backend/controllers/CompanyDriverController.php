<?php

namespace backend\controllers;

use Yii;
use common\models\CompanyDriver;
use common\models\Car;
use common\models\Company;
use common\models\search\CompanyDriverSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet;
use yii\web\UploadedFile;

/**
 * CompanyDriverController implements the CRUD actions for CompanyDriver model.
 */
class CompanyDriverController extends Controller
{
    /**
     * Lists all CompanyDriver models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CompanyDriverSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CompanyDriver model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CompanyDriver model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $company_id
     * @return mixed
     */
    public function actionCreate($company_id = null)
    {
        $model = new CompanyDriver();
        $model->company_id = $company_id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['company/driver', 'id' => $model->company_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CompanyDriver model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['company/driver', 'id' => $model->company_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing CompanyDriver model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['/company/driver', 'id' => $model->company_id]);
    }

    // Скачиваем файл Excel с ТС для заполнения водителей
    public function actionCarsexcel($id, $undriver = false)
    {

        $resExcel = self::createExcelCars($id, $undriver);

        if($resExcel == false) {
        } else {
            $pathFile = \Yii::getAlias('@webroot/files/phones/' . $resExcel);

            header("Content-Type: application/octet-stream");
            header("Accept-Ranges: bytes");
            header("Content-Length: ".filesize($pathFile));
            header("Content-Disposition: attachment; filename=" . $resExcel);
            readfile($pathFile);

        }

        return $this->redirect(['/company/driver', 'id' => $id]);

    }

    // Формирование Excel файла со списком ТС для заполнения водителей
    public static function createExcelCars($id, $undriver) {

        // Название компании
        $company = Company::findOne($id);
        $companyName = $company->name;

        // Список ТС
        $arrCars = [];

        if($undriver == true) {
            $arrCars = Car::find()->leftJoin('company_driver', '`company_driver`.`car_id` = `car`.`id`')->where(['`car`.`company_id`' => $id])->andWhere('`company_driver`.`id` IS NULL')->innerJoin('type', '`type`.`id` = `car`.`type_id`')->innerJoin('mark', '`mark`.`id` = `car`.`mark_id`')->select('car.number, type.name as type, mark.name as mark')->orderBy('car.type_id ASC, car.number ASC')->asArray()->all();
        } else {
            $arrCars = Car::find()->where(['company_id' => $id])->innerJoin('type', '`type`.`id` = `car`.`type_id`')->innerJoin('mark', '`mark`.`id` = `car`.`mark_id`')->select('car.number, type.name as type, mark.name as mark')->asArray()->all();
        }

        $objPHPExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        // Creating a workbook
        $objPHPExcel->getProperties()->setCreator('Mtransservice');
        $objPHPExcel->getProperties()->setTitle('Список ТС');
        $objPHPExcel->getProperties()->setSubject('Список ТС');
        $objPHPExcel->getProperties()->setDescription('');
        $objPHPExcel->getProperties()->setCategory('');
        $objPHPExcel->removeSheetByIndex(0);

        //adding worksheet
        $companyWorkSheet = new PHPExcel_Worksheet($objPHPExcel, 'Список ТС');
        $objPHPExcel->addSheet($companyWorkSheet);

        $companyWorkSheet->getPageMargins()->setTop(2);
        $companyWorkSheet->getPageMargins()->setLeft(0.5);
        $companyWorkSheet->getRowDimension(1)->setRowHeight(1);
        $companyWorkSheet->getRowDimension(10)->setRowHeight(100);
        $companyWorkSheet->getColumnDimension('A')->setWidth(2);
        $companyWorkSheet->getDefaultRowDimension()->setRowHeight(20);

        //headers;
        $companyWorkSheet->mergeCells('A1:I1');
        $companyWorkSheet->getStyle('A1:I1')->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        ));

        $companyWorkSheet->getStyle("A1:I1")->applyFromArray([
                'font' => [
                    'size' => 13,
                    'name'  => 'Times New Roman'
                ],
            ]
        );

        $companyWorkSheet->getRowDimension(1)->setRowHeight(23);

        $companyWorkSheet->setCellValue('A1', $companyName);

        $row = 2;

        // Body
        if(count($arrCars) > 0) {

            $companyWorkSheet->getColumnDimension('A')->setWidth(14);
            $companyWorkSheet->getColumnDimension('B')->setWidth(35);
            $companyWorkSheet->getColumnDimension('C')->setWidth(13);
            $companyWorkSheet->getColumnDimension('D')->setWidth(20);
            $companyWorkSheet->getColumnDimension('E')->setWidth(20);
            $companyWorkSheet->getColumnDimension('F')->setWidth(20);
            $companyWorkSheet->getColumnDimension('G')->setWidth(20);
            $companyWorkSheet->getColumnDimension('H')->setWidth(20);
            $companyWorkSheet->getColumnDimension('I')->setWidth(20);

            // Заголовки
            $companyWorkSheet->setCellValue('A' . $row, 'Марка ТС');
            $companyWorkSheet->setCellValue('B' . $row, 'Тип ТС');
            $companyWorkSheet->setCellValue('C' . $row, 'Номер ТС');
            $companyWorkSheet->setCellValue('D' . $row, 'ФИО водителя 1');
            $companyWorkSheet->setCellValue('E' . $row, 'Номер водителя 1');
            $companyWorkSheet->setCellValue('F' . $row, 'ФИО водителя 2');
            $companyWorkSheet->setCellValue('G' . $row, 'Номер водителя 2');
            $companyWorkSheet->setCellValue('H' . $row, 'ФИО водителя 3');
            $companyWorkSheet->setCellValue('I' . $row, 'Номер водителя 3');

            $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray(array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            ));

            $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'name'  => 'Times New Roman'
                    ],
                ]
            );

            $companyWorkSheet->getRowDimension($row)->setRowHeight(17);

            $row++;

            for ($i = 0; $i < count($arrCars); $i++) {
                if((isset($arrCars[$i]['number'])) && (isset($arrCars[$i]['mark'])) && (isset($arrCars[$i]['type']))) {
                    $companyWorkSheet->setCellValue('A' . $row, $arrCars[$i]['mark']);
                    $companyWorkSheet->setCellValue('B' . $row, $arrCars[$i]['type']);
                    $companyWorkSheet->setCellValue('C' . $row, $arrCars[$i]['number']);

                    $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray(array(
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        )
                    ));

                    $companyWorkSheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                            'font' => [
                                'size' => 12,
                                'name'  => 'Times New Roman'
                            ],
                        ]
                    );

                    $companyWorkSheet->getRowDimension($row)->setRowHeight(17);

                    $row++;
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setSelectedCells('A1');

        //saving document
        $pathFile = \Yii::getAlias('@webroot/files/phones/');

        if (!is_dir($pathFile)) {
            mkdir($pathFile, 0755, 1);
        }

        $companyName = trim($companyName);
        $companyName = str_replace('«', '', $companyName);
        $companyName = str_replace('»', '', $companyName);
        $companyName = str_replace('"', '', $companyName);
        $companyName = str_replace(' ', '_', $companyName);

        $filename = $companyName . '.xls';

        $objWriter->save($pathFile . $filename);
        return $filename;
    }

    public function actionUpload($company_id)
    {

        $uploadFile = UploadedFile::getInstanceByName('uploadPhones');

        // Проверяем что загружен Excel файл
        $arrFileName = explode('.', $uploadFile->name);
        $countArrFileName = count($arrFileName) - 1;

        if(($arrFileName[$countArrFileName] == 'xlsx') || ($arrFileName[$countArrFileName] == 'xls')) {
            $pExcel = PHPExcel_IOFactory::load($uploadFile->tempName);

            // Загружаем только первую страницу
            $firstPage = false;
            $tables = [];

            foreach ($pExcel->getWorksheetIterator() as $worksheet) {

                if ($firstPage == false) {
                    $tables[] = $worksheet->toArray();
                    $firstPage = true;
                }

            }

            // Вывод
            $tables = $tables[0];
            $companyName = $tables[0][0];

            // Цикл по строкам
            $numRows = count($tables);

            if ($numRows > 2) {

                for ($i = 0; $i < $numRows; $i++) {

                    // Цикл по столбцам
                    if ($i > 1) {

                        $numCol = count($tables[$i]);

                        if ($numCol > 4) {
                            // Проверка и запись номера водителя

                            if (isset($tables[$i][2])) {

                                if ((isset($tables[$i][3]) && (isset($tables[$i][4]))) || (isset($tables[$i][5]) && (isset($tables[$i][6]))) || (isset($tables[$i][7]) && (isset($tables[$i][8])))) {

                                    $carNumber = $tables[$i][2];

                                    $carArray = Car::find()->where(['number' => $carNumber])->andWhere(['company_id' => $company_id])->select('id')->column();

                                    if (count($carArray) > 0) {
                                        if (isset($carArray[0])) {

                                            $carID = $carArray[0];

                                            $modelDriver = null;
                                            $arrDriver = [];

                                            if (isset($tables[$i][3]) && (isset($tables[$i][4]))) {
                                                if (($tables[$i][3]) && ($tables[$i][4])) {

                                                    // Проверка на повторного водителя
                                                    $arrDriver = CompanyDriver::find()->where(['name' => $tables[$i][3]])->andWhere(['phone' => $tables[$i][4]])->andWhere(['company_id' => $company_id])->andWhere(['car_id' => $carID])->select('id')->column();

                                                    if(count($arrDriver) == 0) {

                                                        $phoneVal = (String) $tables[$i][4];
                                                        $phoneVal = str_replace(" ", '', $phoneVal);
                                                        $phoneVal = str_replace("-", '', $phoneVal);

                                                        $modelDriver = new CompanyDriver();
                                                        $modelDriver->company_id = $company_id;
                                                        $modelDriver->name = (String) $tables[$i][3];
                                                        $modelDriver->phone = $phoneVal;
                                                        $modelDriver->car_id = $carID;
                                                        $modelDriver->save();
                                                    }

                                                }
                                            }

                                            if (isset($tables[$i][5]) && (isset($tables[$i][6]))) {
                                                if (($tables[$i][5]) && ($tables[$i][6])) {
                                                    $modelDriver = null;
                                                    $phoneVal = '';
                                                    $arrDriver = [];

                                                    // Проверка на повторного водителя
                                                    $arrDriver = CompanyDriver::find()->where(['name' => $tables[$i][5]])->andWhere(['phone' => $tables[$i][6]])->andWhere(['company_id' => $company_id])->andWhere(['car_id' => $carID])->select('id')->column();

                                                    if(count($arrDriver) == 0) {

                                                        $phoneVal = (String) $tables[$i][6];
                                                        $phoneVal = str_replace(" ", '', $phoneVal);
                                                        $phoneVal = str_replace("-", '', $phoneVal);

                                                        $modelDriver = new CompanyDriver();
                                                        $modelDriver->company_id = $company_id;
                                                        $modelDriver->name = (String) $tables[$i][5];
                                                        $modelDriver->phone = $phoneVal;
                                                        $modelDriver->car_id = $carID;
                                                        $modelDriver->save();
                                                    }


                                                }
                                            }

                                            if (isset($tables[$i][7]) && (isset($tables[$i][8]))) {
                                                if (($tables[$i][7]) && ($tables[$i][8])) {
                                                    $modelDriver = null;
                                                    $phoneVal = '';
                                                    $arrDriver = [];

                                                    // Проверка на повторного водителя
                                                    $arrDriver = CompanyDriver::find()->where(['name' => $tables[$i][7]])->andWhere(['phone' => $tables[$i][8]])->andWhere(['company_id' => $company_id])->andWhere(['car_id' => $carID])->select('id')->column();

                                                    if(count($arrDriver) == 0) {

                                                        $phoneVal = (String) $tables[$i][8];
                                                        $phoneVal = str_replace(" ", '', $phoneVal);
                                                        $phoneVal = str_replace("-", '', $phoneVal);

                                                        $modelDriver = new CompanyDriver();
                                                        $modelDriver->company_id = $company_id;
                                                        $modelDriver->name = (String) $tables[$i][7];
                                                        $modelDriver->phone = $phoneVal;
                                                        $modelDriver->car_id = $carID;
                                                        $modelDriver->save();
                                                    }

                                                }
                                            }

                                        }
                                    }

                                }

                            }

                        }

                    }

                }

            }

        }
        // Проверяем что загружен Excel файл

        return $this->redirect(['/company/driver', 'id' => $company_id]);
    }

    /**
     * Finds the CompanyDriver model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CompanyDriver the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CompanyDriver::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
