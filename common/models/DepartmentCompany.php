<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "department_company".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_id
 * @property string $remove_date
 * @property integer $remove_id
 * @property integer $type
 */
class DepartmentCompany extends ActiveRecord
{

    public $companyNum;
    private $remove_date;
    private $remove_id;

    public static $periodList = ['все время', 'месяц', 'квартал', 'полгода', 'год'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%department_company}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_id', 'remove_id', 'type'], 'integer'],
            [['remove_date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Компания',
            'user_id' => 'Сотрудник',
            'remove_id' => 'Сотрудник',
            'companyNum' => 'Количество',
            'remove_date' => 'Дата переноса',
            'type' => 'Тип',
        ];
    }

    public function getRemove_date()
    {
        return $this->remove_date;
    }

    public function setRemove_date($value)
    {
        $this->remove_date = $value;
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

}
