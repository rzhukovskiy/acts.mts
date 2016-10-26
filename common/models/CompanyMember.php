<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%company_member}}".
 *
 * @property integer $id
 * @property string $company_id
 * @property string $position
 * @property string $phone
 * @property string $email
 */
class CompanyMember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%company_member}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id'], 'required'],
            [['company_id'], 'integer'],
            [['position', 'phone', 'email'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'company_id' => 'Company ID',
            'position'   => 'Position',
            'phone'      => 'Phone',
            'email'      => 'Email',
        ];
    }
}
