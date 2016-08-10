<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.08.16
 * Time: 17:41
 */

namespace common\models;

use yii\db\ActiveRecord;

/**
 * Requisites model
 *
 * @property int $id
 * @property int $company_id
 * @property string $contract
 * @property string $header
 * @property int $type
 *
 * @property Company $company
 */
class Requisites extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public static function tableName()
    {
        return '{{%requisites}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            [['company_id', 'contract'],'required'],
            ['type', 'default', 'value' => Service::TYPE_WASH],

        ];
    }

    /**
     * @return Company
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'company_id']);
    }

    public function attributeLabels()
    {
        return array(
            'id'       => 'ID',
            'contract' => 'Договор',
            'header'   => 'Заголовок',
        );
    }
}