<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "service_replace".
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $partner_id
 * @property integer $type
 * @property integer $type_client
 * @property integer $type_partner
 * @property integer $created_at
 * @property integer $updated_at
 */
class ServiceReplace extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%service_replace}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'partner_id', 'type'], 'required'],
            [['client_id', 'partner_id', 'type', 'type_client', 'type_partner', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'client_id' => 'Клиент',
            'partner_id' => 'Партнер',
            'type' => 'Тип',
            'type_client' => 'Тип ТС клиента',
            'type_partner' => 'Тип ТС партнера',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата изменения',
        ];
    }
}
