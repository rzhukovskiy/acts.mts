<?php

namespace common\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "department_user_company_type".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $company_type
 * @property string $company_status
 */
class DepartmentUserCompanyType extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%department_user_company_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'company_type', 'company_status'], 'required'],
            [['user_id', 'company_type', 'company_status'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'             => 'ID',
            'user_id'        => 'User ID',
            'company_type'   => 'Company Type',
            'company_status' => 'Company Status',
        ];
    }

}
