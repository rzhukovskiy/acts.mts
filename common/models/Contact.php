<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%contact}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property string $name
 * @property string $description
 * @property integer $type
 *
 * @property Company $company
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contact}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['company_id'], 'required'],
            //[['company_id'], 'integer'],
            //[['company_id'], 'unique'],
            [['type', 'name'], 'required'],
            [['type'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1000],
            /*
            [
                ['company_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['company_id' => 'id']
            ],
            */
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            //'company_id'  => 'Компания',
            'name'        => 'Имя',
            'description' => 'Описание',
            'type'        => 'Тип',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    /*
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }
    */
}
