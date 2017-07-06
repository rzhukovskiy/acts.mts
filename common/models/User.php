<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use common\models\DepartmentUserCompanyType;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $salt
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $role
 * @property integer $is_account
 * @property integer $company_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $code
 * @property string $code_pass
 * @property string $password write-only password
 *
 * @property Company $company
 * @property Department $department
 * @property Department[] $departments
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    const ROLE_ADMIN    = 0;
    const ROLE_CLIENT   = 1;
    const ROLE_PARTNER  = 2;
    const ROLE_WATCHER  = 3;
    const ROLE_MANAGER  = 4;
    const ROLE_ACCOUNT  = 5;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
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
            ['username', 'unique'],
            ['email', 'email'],
            [['role', 'company_id', 'code'], 'integer'],
            [['is_account', 'email', 'code_pass'], 'safe'],
            ['is_account', 'default', 'value' => 0],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Ключ',
            'username' => 'Имя пользователя',
            'password_hash' => 'Хэш пароля',
            'password_reset_token' => 'Ключ сброса пароля',
            'email' => 'Электронная почта',
            'auth_key' => 'Ключ авторизации',
            'status' => 'Статус',
            'is_account' => 'Может записывать',
            'role' => 'Роль',
            'company_id' => 'Компания',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'code' => 'Номер в телефонии',
            'code_pass' => 'Пароль телефонии',
        ];
    }

    public function getDepartments() {
        return $this->hasMany(Department::className(), ['id' => 'department_id'])
            ->viaTable('{{%department_user}}', ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $identity = static::findOne(['id' => $id]);
        return $identity->status == self::STATUS_ACTIVE ? $identity : null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return md5($this->salt . $password) == $this->password_hash;
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = md5($this->salt . $password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::className(), ['id' => 'department_id'])
            ->viaTable('{{%department_user}}', ['user_id' => 'id']);
    }

    public static function getRoleName($role)
    {
        switch ($role) {
            case static::ROLE_ADMIN :
                return 'Admin';
            case static::ROLE_CLIENT :
                return 'Client';
            case static::ROLE_PARTNER :
                return 'Partner';
            case static::ROLE_WATCHER :
                return 'Watcher';

            default :
                return 'God';
        }
    }

    public function getCode_pass()
    {
        return $this->code_pass;
    }

    public function setCode_pass($value)
    {
        $this->code_pass = $value;
    }

    /**
     * @return bool|int|string
     */
    public function getFirstCompanyType()
    {
        foreach (Company::$listType as $companyType => $serviceData) {
            if ($this->can($companyType, Company::STATUS_NEW)) {
                return $companyType;
            }
        }
        
        return false;
    }

    public function getFirstCompanyTypeMenu($status)
    {

        if($this->id == 364) {
            return 4;
        } else {

            $DepartmentUserCompany = DepartmentUserCompanyType::find()->where(['user_id' => Yii::$app->user->identity->id])->andWhere(['company_status' => $status])->select('company_type')->orderBy('company_type ASC')->limit('1')->column();

            if (count($DepartmentUserCompany) > 0) {

                if (isset($DepartmentUserCompany[0])) {
                    return $DepartmentUserCompany[0];
                } else {
                    return false;
                }

            } else {
                return false;
            }
        }

    }

    /**
     * @param $status
     * @return array
     */
    public function getAllCompanyType($status)
    {
        $res = [];
        if (!empty($this->department)) {
            foreach (Company::$listType as $companyType => $serviceData) {
                if ($this->can($companyType, $status)) {
                    $res[$companyType] = Company::$listType[$companyType];
                }
            }
        }
        return $res;

    }

    /**
     * @param $status
     * @return array
     */
    public function getAllServiceType($status)
    {
        $res = [];
        if (!empty($this->department)) {
            foreach (Service::$listType as $companyType => $serviceData) {
                if ($this->can($companyType, $status)) {
                    $res[$companyType] = Company::$listType[$companyType];
                }
            }
        }

        return $res;
    }

    /**
     * @param $companyType int|array
     * @param $companyStatus
     * @return int
     */
    public function can($companyType, $companyStatus)
    {
        if (is_array($companyType)) {
            foreach ($companyType as $type) {
               if (!count(DepartmentUserCompanyType::findAll([
                    'user_id'  => $this->id,
                    'company_type'   => $companyType,
                    'company_status' => $companyStatus,
                ]))) {
                   return false;
               }
            }

            return true;
        } else {
            return count(DepartmentUserCompanyType::findAll([
                'user_id'  => $this->id,
                'company_type'   => $companyType,
                'company_status' => $companyStatus,
            ]));
        }
    }
}
