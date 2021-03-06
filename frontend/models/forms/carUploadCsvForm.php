<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace frontend\models\forms;

use common\components\Translit;
use common\models\Car;
use common\models\Company;
use common\models\Mark;
use common\models\Type;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use \PHPExcel_IOFactory;

class CarUploadCsvForm extends Model
{

    public $company_id;
    public $type_id;
    /** @var  UploadedFile */
    public $file;

    // range of inserted id's
    public $updatedIds;

    // count updated models
    public $updatedCounter;

    // folder for store data files
    protected $folder = 'files/cars';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['company_id', 'type_id'], 'safe'],
            ['file', 'file', 'skipOnEmpty' => false, 'extensions' => ['csv'], 'checkExtensionByMimeType' => false],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'company_id' => 'Компания',
            'type_id' => 'Тип ТС',
            'file' => 'Файл',
        ];
    }

    public function save($validate = true)
    {
        if (!($validate && $this->validate())) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);

            return false;
        }

        if (!$this->saveFile()) {
            Yii::info('Model not inserted due to uploaded error.', __METHOD__);

            return false;
        }

        if (!$this->saveFromExternal()) {
            Yii::info('Model not inserted due to xls error.', __METHOD__);

            return false;
        }

        return true;
    }


    /**
     * Save uploaded data file
     *
     * @return bool
     */
    public function saveFile()
    {
        $date = date('Y_m_d_H_i_s');
        $extension = $this->file->extension;
        $baseName = $this->file->baseName;
        $newName = $date . '_' . $baseName . '.' . $extension;
        $dir = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR
            . $this->folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $file = $dir . DIRECTORY_SEPARATOR
            . $newName;

        // @see \yii\web\UploadedFile::saveAs
        // Если изагрузили, то временный файл уже не существует.
        // Добавим имя уже загруженного файла
        if ($this->file->saveAs($file)) {
            $this->file = $newName;

            return true;
        }

        return false;
    }

    /**
     * Save from xls
     *
     * @return array
     */
    public function saveFromExternal()
    {
        $file = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR
            . $this->folder . DIRECTORY_SEPARATOR
            . $this->file;

        $this->updatedCounter = 0;

        $markArray = ArrayHelper::map(Mark::find()->all(), 'id', 'name');
        $typeArray = ArrayHelper::map(Type::find()->all(), 'id', 'name');

        // загрузка из csv
        ini_set('auto_detect_line_endings', true);
        $csvFile = fopen($file, 'r');
        $data = [];
        $arrNumbers = [];
        $curentLine = 0;

        while (($line = fgetcsv($csvFile, 0, ";")) !== FALSE) {
            $data[] = $line;

            // Получаем номера всех ТС
            if($curentLine > 0) {
                $number = $line[1];
                $number = mb_strtoupper(str_replace(' ', '', $number), 'UTF-8');
                $number = strtr($number, Translit::$rules);

                $arrNumbers[] = $number;
            }

            $curentLine++;
        }

        fclose($csvFile); //Закрываем файл

        // Название компании
        $name = $data[0][0];

        if ($newCompany = Company::findOne(['name' => $name])) {
            $this->company_id = $newCompany->id;
        }
        $curentLine = 0;

        $existed = Car::findAll(['number' => $arrNumbers]);

        foreach ($data as $key => $value) {

            if($curentLine > 0) {

                if(!$this->company_id) {
                    return false;
                }

                $mark = $value[0];
                $number = $value[1];
                $number = mb_strtoupper(str_replace(' ', '', $number), 'UTF-8');
                $number = strtr($number, Translit::$rules);
                $type = $value[2];
                $is_infected = $value[3];

                $car = [];
                $haveCar = false;

                foreach ($existed as $index => $result) {

                    if ($result->number == $number) {
                        $car = $result;
                        $haveCar = true;
                    }

                }

                if ($haveCar == false) {
                    $car = new Car();
                }

                $car->attributes = $this->attributes;
                $car->number = $number;

                // ToDo: бывает ситуация, когда ошибка в типе, тогда что присваивать?
                // ToDo: ввести ошибочный тип?
                if ($typeKey = array_search($type, $typeArray)) {
                    $car->type_id = $typeKey;
                }

                $mark = explode('-', explode(' ', $mark)[0])[0];
                if ($markKey = array_search($mark, $markArray)) {
                    $car->mark_id = $markKey;
                } else {
                    if ($newMarkModel = $this->createMark($mark)) {
                        $car->mark_id = $newMarkModel->id;
                    }
                }

                if (isset($is_infected)) {
                    if ($is_infected) {
                        $car->is_infected = 1;
                    } else {
                        $car->is_infected = 0;
                    }
                } else {
                    $car->is_infected = 0;
                }

                if ($car->save()) {
                    $this->updatedCounter++;
                    $this->updatedIds[] = $car->id;
                }

            }

            $curentLine++;
        }

        // загрузка из csv

        /*$objPHPExcel = \PHPExcel_IOFactory::load($file);

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow(); // e.g. 10

            for ($row = 1; $row <= $highestRow; ++$row) {
                $name = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                $number = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $type = $worksheet->getCellByColumnAndRow(2, $row)->getValue();
                $is_infected = $worksheet->getCellByColumnAndRow(3, $row)->getValue();

                if (
                    \PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($name) == \PHPExcel_Cell_DataType::TYPE_STRING
                    && \PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($number) == \PHPExcel_Cell_DataType::TYPE_NULL
                    && \PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($type) == \PHPExcel_Cell_DataType::TYPE_NULL
                ) {
                    if ($newCompany = Company::findOne(['name' => $name]))
                        $this->company_id = $newCompany->id;

                    continue;
                }

                $number = mb_strtoupper(str_replace(' ', '', $number), 'UTF-8');
                $number = strtr($number, Translit::$rules);

                if ($existed = Car::findOne(['number' => $number]))
                    $car = $existed;
                else
                    $car = new Car();

                $car->attributes = $this->attributes;
                $car->number = $number;

                // ToDo: бывает ситуация, когда ошибка в типе, тогда что присваивать?
                // ToDo: ввести ошибочный тип?
                if ($typeKey = array_search($type, $typeArray))
                    $car->type_id = $typeKey;

                $name = explode('-', explode(' ', $name)[0])[0];
                if ($markKey = array_search($name, $markArray))
                    $car->mark_id = $markKey;
                else
                    if ($newMarkModel = $this->createMark($name))
                        $car->mark_id = $newMarkModel->id;

                if (\PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($is_infected) != \PHPExcel_Cell_DataType::TYPE_NULL) {
                    $car->is_infected = $is_infected;
                }

                if ($car->save()) {
                    $this->updatedCounter++;
                    $this->updatedIds[] = $car->id;
                }
            }
        }*/

        return true;
    }

    /**
     * @param $name
     * @return Mark|null
     */
    private function createMark($name)
    {
        $model = new Mark(['name' => $name]);
        if ($model->save())
            return $model;

        return null;
    }
}