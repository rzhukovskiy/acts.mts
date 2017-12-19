<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "expense".
 *
 * @property integer $id
 * @property integer $expense_company
 * @property integer $type
 * @property string $description
 * @property string $commission
 * @property string $sum
 * @property string $date
 */
class Expense extends ActiveRecord
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_STAT = 'stat';
    const SCENARIO_TOTAL = 'total';

    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%expense}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['expense_company', 'sum', 'date','type'], 'required'],
            [['expense_company','type'], 'integer'],
            [['sum'], 'number'],
            [['description'], 'string', 'max' => 255],
            [['date'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'expense_company' => 'ID expense company',
            'type' => 'Тип',
            'description' => 'Наименование услуги',
            'sum' => 'Сумма',
            'date' => 'Дата',
            'ndfl' => 'НДФЛ (13%)',
            'pfr' => 'ПФР (22%)',
            'foms' => 'ФОМС (5.1%)',
            'fss' => 'ФСС (2.9%)',
            'fssns' => 'ФССНС (0.5%)',
        ];
    }
    public function beforeSave($insert)
    {

        // Если это новая запись то обрабатываем данные из формы здесь
        if ($this->isNewRecord) {
            // переводим дату в нужный формат
            $this->date = (String)strtotime($this->date);
        }
        return parent::beforeSave($insert);
    }

    public function getExpensecompany()
    {
        return $this->hasOne(ExpenseCompany::className(), ['id' => 'expense_company']);
    }

}
