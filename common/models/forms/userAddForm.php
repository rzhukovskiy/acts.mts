<?php

namespace common\models\forms;

use Yii;
use common\models\User;
use yii\base\Model;

class userAddForm extends Model
{
    public $username;
    public $password;
    public $role;
    public $email;
    public $company_id;

    public function rules()
    {
        return [
            [['username', 'password', 'email', 'company_id'], 'required', 'message' => 'Поле обязательно для заполнения {attribute}.'],
            ['password', 'string', 'min' => 4, 'tooShort' => 'Длинна пароля должна быть более {min, number} символов'],
            ['password', 'string', 'max' => 24, 'tooLong' => 'Максимальная длинна пароля {max, number} символа.'],
            ['role', 'safe'],
            ['email', 'email', 'message' => 'Пожалуйста укажите реальный адрес электронной почты, на него будут отпарвленны письма.'],
            ['username', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'username', 'comboNotUnique' => 'Попробуйте другое имя. Такой логин уже используется.']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Имя пользователя',
            'password' => 'Пароль',
            'email' => 'Почта',
            'company_id' => 'Компания',
        ];
    }

    public function save()
    {
        $values = $this->attributes;
        $values['password'] = Yii::$app->security->generatePasswordHash($this->password);
        $model = new User($values);
        $model->auth_key = '';
        $model->created_at = time();
        $model->updated_at = time();
        if ($model->save())
            return true;

        return false;
    }
}