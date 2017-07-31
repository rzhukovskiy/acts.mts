<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "company_sub_type".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $sub_type
 */
class CompanySubType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company_sub_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'sub_type'], 'required'],
            [['company_id', 'sub_type'], 'integer'],
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
            'sub_type' => 'Под тип',
        ];
    }
}
