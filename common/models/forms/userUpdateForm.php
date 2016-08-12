<?php

namespace common\models\forms;

use Yii;
use common\models\User;
use yii\base\Model;

class userUpdateForm extends Model
{
    public $username;
    public $newPassword;
    public $role;
    public $email;
    public $company_id;
    public $password;

    public function rules()
    {
        return [
            [['username', 'email', 'company_id'], 'required', 'message' => 'Поле обязательно для заполнения {attribute}.'],
            ['newPassword', 'string', 'min' => 4, 'tooShort' => 'Длинна пароля должна быть более {min, number} символов'],
            ['newPassword', 'string', 'max' => 24, 'tooLong' => 'Максимальная длинна пароля {max, number} символа.'],
            [['role'], 'safe'],
            ['email', 'email', 'message' => 'Пожалуйста укажите реальный адрес электронной почты, на него будут отпарвленны письма.'],
            ['username', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'username', 'comboNotUnique' => 'Попробуйте другое имя. Такой логин уже используется.']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Имя пользователя',
            'newPassword' => 'Пароль',
            'email' => 'Почта',
            'company_id' => 'Компания',
        ];
    }

    public function update()
    {
        $values = $this->attributes;
        if (!empty($this->newPassword))
            $this->password = Yii::$app->security->generatePasswordHash($this->newPassword);

        $this->setAttributes($values);

        return true;
    }
}