<?php

namespace common\models\forms;

use Yii;
use common\models\User;
use yii\base\Model;

class userUpdateForm extends Model
{
    public $username;
    public $code;
    public $code_pass;
    public $newPassword;
    public $role;
    public $email;
    public $company_id;
    public $password;
    public $is_account;

    public function rules()
    {
        return [
            [['username', 'company_id'], 'required', 'message' => 'Поле обязательно для заполнения {attribute}.'],
            [['email'], 'email'],
            ['newPassword', 'string', 'min' => 4, 'tooShort' => 'Длинна пароля должна быть более {min, number} символов'],
            ['newPassword', 'string', 'max' => 24, 'tooLong' => 'Максимальная длинна пароля {max, number} символа.'],
            [['code', 'code_pass', 'is_account', 'role'], 'safe'],
            ['username', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'username', 'comboNotUnique' => 'Попробуйте другое имя. Такой логин уже используется.']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Имя пользователя',
            'code' => 'Номер телефонии',
            'code_pass' => 'Пароль телефонии',
            'newPassword' => 'Пароль',
            'email' => 'Почта',
            'company_id' => 'Компания',
            'is_account' => 'Может записывать',
        ];
    }

    public function update()
    {
        $values = $this->attributes;
        if (!empty($this->newPassword))
            $this->password = $this->newPassword;

        $this->setAttributes($values);

        return true;
    }
}