<?php
namespace common\models;

use common\models\query\DepartmentQuery;
use yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%department}}".
 *
 * @property string $id
 * @property string $name
 * @property integer $role
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * 
 * @property User[] $users
 */
class Department extends ActiveRecord
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    static $listRole = [
        User::ROLE_WATCHER => 'Обычный',
        User::ROLE_MANAGER => 'Менеджер',
        User::ROLE_ACCOUNT => 'Работа с клиентами',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%department}}';
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
            [['name', 'role'], 'required'],
            [['role', 'status', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 255],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['role', 'default', 'value' => User::ROLE_WATCHER],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название',
            'role' => 'Роль',
            'status' => 'Статус',
            'created_at' => 'Создан',
            'updated_at' => 'Редактирован',
        ];
    }

    /**
     * @inheritdoc
     * @return DepartmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new DepartmentQuery(get_called_class());
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])
            ->viaTable('{{%department_user}}', ['department_id' => 'id']);
    }

    public function can($companyType, $companyStatus)
    {
        return count(DepartmentCompanyType::findAll([
            'department_id'  => $this->id,
            'company_type'   => $companyType,
            'company_status' => $companyStatus,
        ]));
    }

    /**
     * @return int
     */
    public static function getFirstId()
    {
        $firstDepartment = self::find()->orderBy('id ASC')->one();
        if ($firstDepartment) {
            return $firstDepartment->id;
        }

        return 0;
    }
}
