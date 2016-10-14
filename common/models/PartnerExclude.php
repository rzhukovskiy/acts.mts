<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "partner_exclude".
 *
 * @property integer $id
 * @property string $client_id
 * @property string $partner_id
 *
 * @property Company $client
 * @property Company $partner
 */
class PartnerExclude extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%partner_exclude}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'partner_id'], 'required'],
            [['client_id', 'partner_id'], 'integer'],
            [
                ['client_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['client_id' => 'id']
            ],
            [
                ['partner_id'],
                'exist',
                'skipOnError'     => true,
                'targetClass'     => Company::className(),
                'targetAttribute' => ['partner_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'client_id'  => 'Client ID',
            'partner_id' => 'Partner ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Company::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPartner()
    {
        return $this->hasOne(Company::className(), ['id' => 'partner_id']);
    }
}
