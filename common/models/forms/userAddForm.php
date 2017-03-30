<?php

namespace common\models\forms;

use common\models\Department;
use common\models\DepartmentCompanyType;
use common\models\DepartmentUserCompanyType;
use Yii;
use common\models\User;
use yii\base\Model;

class userAddForm extends Model
{
    public $username;
    public $code;
    public $password;
    public $role;
    public $email;
    public $company_id;
    public $is_account;

    public function rules()
    {
        return [
            [['username', 'password', 'company_id'], 'required', 'message' => 'Поле обязательно для заполнения {attribute}.'],
            ['password', 'string', 'min' => 4, 'tooShort' => 'Длинна пароля должна быть более {min, number} символов'],
            ['password', 'string', 'max' => 24, 'tooLong' => 'Максимальная длинна пароля {max, number} символа.'],
            [['code', 'is_account', 'role'], 'safe'],
            ['email', 'email', 'message' => 'Пожалуйста укажите реальный адрес электронной почты, на него будут отпарвленны письма.'],
            ['username', 'unique', 'targetClass' => User::className(), 'targetAttribute' => 'username', 'comboNotUnique' => 'Попробуйте другое имя. Такой логин уже используется.']
        ];
    }

    public function attributeLabels()
    {
        return [
            'username' => 'Имя пользователя',
            'code' => 'Номер телефонии',
            'password' => 'Пароль',
            'email' => 'Почта',
            'company_id' => 'Компания',
            'is_account' => 'Может записывать',
        ];
    }

    public function save()
    {
        $values = $this->attributes;
        $model = new User($values);
        $model->auth_key = '';
        $model->salt = Yii::$app->security->generateRandomString();
        $model->created_at = time();
        $model->updated_at = time();

        if (!empty($this->password)) {
            $model->password_hash = md5($model->salt . $this->password);
        }

        if ($model->save())
            return true;

        return false;
    }

    /**
     * @param $department_id
     * @return bool
     */
    public function saveToDepartment($department_id)
    {
        $values = $this->attributes;
        $model = new User($values);
        $model->auth_key = '';
        $model->salt = Yii::$app->security->generateRandomString();
        $model->created_at = time();
        $model->updated_at = time();

        $department = Department::findOne(['id' => $department_id]);
        if ($department) {
            $model->role = $department->role;
        }

        if (!empty($this->password)) {
            $model->password_hash = md5($model->salt . $this->password);
        }

        if ($model->save()) {
            if ($department) {
                $model->link('departments', $department);
            }
            return $model;
        }

        return false;
    }

    /**
     * @param $departmentId
     * @param $user
     */
    public function getCompanyFromDepartment($departmentId,$user){
        $departmentCompany = DepartmentCompanyType::findAll([
            'department_id'  => $departmentId,
        ]);
        foreach ($departmentCompany as $company) {
            $userCompany=new DepartmentUserCompanyType();
            $userCompany->user_id=$user->id;
            $userCompany->company_status=$company->company_status;
            $userCompany->company_type=$company->company_type;
            $userCompany->save();
        }
    }
}