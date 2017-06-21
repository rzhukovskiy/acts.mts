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
 */
class DepartmentCompany extends ActiveRecord
{

    public $companyNum;

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
            [['company_id', 'user_id'], 'integer'],
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
            'companyNum' => 'Количество',
            'remove_date' => 'Дата переноса',
        ];
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

}
