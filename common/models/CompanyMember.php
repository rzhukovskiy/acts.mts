<?php

namespace common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%company_member}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $name
 * @property string $position
 * @property string $phone
 * @property string $email
 * @property string $show_member
 * 
 * @property Company $company
 */
class CompanyMember extends ActiveRecord
{
    private $show_member;
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
            [['position', 'phone', 'email', 'name'], 'string', 'max' => 255],
            [['show_member'], 'integer'],
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
            'name'       => 'Фио',
            'position'   => 'Должность',
            'phone'      => 'Телефон',
            'email'      => 'Email',
            'show_member'      => 'Выводить в личном кабинете',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getShow_member()
    {
        return $this->show_member;
    }

    public function setShow_member($value)
    {
        $this->show_member = $value;
    }
}
