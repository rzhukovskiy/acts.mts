<?php

namespace common\models;

use common\models\query\DepartmentCompanyTypeQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "department_company_type".
 *
 * @property integer $id
 * @property string $department_id
 * @property string $company_type
 * @property string $company_status
 */
class DepartmentCompanyType extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%department_company_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['department_id', 'company_type', 'company_status'], 'required'],
            [['department_id', 'company_type', 'company_status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'department_id' => 'Department ID',
            'company_type' => 'Company Type',
            'company_status' => 'Company Status',
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\DepartmentCompanyTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DepartmentCompanyTypeQuery(get_called_class());
    }
}
