<?php
/**
 * Created by PhpStorm.
 * User: Ruslan
 * Date: 28.08.2016
 * Time: 19:50
 */

namespace frontend\models\forms;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use \PHPExcel_IOFactory;

class CarUploadXlsForm extends Model
{

    public $company;
    public $type;
    /** @var  UploadedFile */
    public $file;

    public $startId;

    // folder for store data files
    protected $folder = 'data';

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['company', 'type'], 'safe'],
            ['file', 'file', 'skipOnEmpty' => false, 'extensions' => 'xls, xlsx'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'company' => 'Компания',
            'type' => 'Тип ТС',
            'file' => 'Файл',
        ];
    }

    public function save($validate = true)
    {
        if ($validate && $this->validate($this->attributes)) {
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
        $date = date('Y_m_d__H_i_s');
        $extension = $this->file->extension;
        $baseName = $this->file->baseName;
        $newName = $date . '_' . $baseName . '.' . $extension;
        $file = Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR
            . $this->folder . DIRECTORY_SEPARATOR
            . $newName;

        return $this->file->saveAs($file);
    }

    /**
     * Save from xls
     *
     * @return array
     */
    public function saveFromExternal()
    {

        $res = [];

        $obj = \PHPExcel_IOFactory::load($this->file);
        $objPHPExcel = PHPExcel_IOFactory::load($this->external->getTempName());

        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $highestRow = $worksheet->getHighestRow(); // e.g. 10

            for ($row = 1; $row <= $highestRow; ++$row) {
                $name = $worksheet->getCellByColumnAndRow(0, $row)->getValue();
                $number = $worksheet->getCellByColumnAndRow(1, $row)->getValue();
                $type = $worksheet->getCellByColumnAndRow(2, $row)->getValue();

                if (
                    PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($name) == PHPExcel_Cell_DataType::TYPE_STRING
                    && PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($number) == PHPExcel_Cell_DataType::TYPE_NULL
                    && PHPExcel_Cell_DefaultValueBinder::dataTypeForValue($type) == PHPExcel_Cell_DataType::TYPE_NULL
                ) {
                    if ($newCompany = Company::model()->find('name = :name', [':name' => $name])) {
                        $this->company_id = $newCompany->id;
                    }
                    continue;
                }

                $car = new Car();

                $number = mb_strtoupper(str_replace(' ', '', $number), 'UTF-8');
                $number = strtr($number, Translit::$rules);
                if ($existed = Car::model()->find('number = :number', [':number' => $number])) {
                    $car = $existed;
                }

                $car->attributes = $this->attributes;
                $car->number = $number;

                if ($type = Type::model()->find('name = :name', [':name' => $type])) {
                    $car->type_id = $type->id;
                }

                $name = explode('-', explode(' ', $name)[0])[0];
                if ($mark = Mark::model()->find('name = :name', [':name' => $name])) {
                    $car->mark_id = $mark->id;
                } else {
                    $mark = new Mark();
                    $mark->name = $name;
                    if ($mark->save()) {
                        $car->mark_id = $mark->id;
                    }
                }

                $car->save();
                $res[] = $car;
            }
        }

        return $res;
    }
}