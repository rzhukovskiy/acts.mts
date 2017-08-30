<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "api_token".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $token
 * @property string $expired_at
 */
class ApiToken extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%api_token}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'token', 'expired_at'], 'required'],
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 255],
            [['expired_at'], 'string', 'max' => 20],
            [['token'], 'unique'],
        ];
    }

    public function generateToken($expire)
    {
        $this->expired_at = $expire;
        $this->token = \Yii::$app->security->generateRandomString();
    }

    public function fields()
    {
        return [
            'token' => 'token',
            'expired' => function () {
                return date(DATE_RFC3339, $this->expired_at);
            },
        ];
    }

}
