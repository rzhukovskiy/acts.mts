<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use common\models\Company;

/**
 * This is the model class for table "{{card}}".
 *
 * The followings are the available columns in table '{{card}}':
 * @property int $id
 * @property int $company_id
 * @property int $number
 * @property int $active
 * @property string $create_date
 * @property Company $cardCompany
 */
class Card extends ActiveRecord
{

    const STATUS_NOT_ACTIVE = 0;
    const STATUS_ACTIVE = 1;

    public $cardStatus = array(
        'Активен',
        'Заблокирована'
    );

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Card the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function tableName()
    {
        return '{{card}}';
    }

    public function rules()
    {
        return array(
            array('company_id, number', 'required'),
            array('id, number, type, active, create_date, cardCompany', 'safe', 'on' => 'search'),
        );
    }

    public function relations()
    {
        return array(
            'cardCompany' => array(self::BELONGS_TO, 'Company', 'company_id'),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;
        $criteria->with = array('cardCompany');

        if (!Yii::app()->user->checkAccess(User::ADMIN_ROLE)) {
            $criteria->compare('company_id', Yii::app()->user->model->company_id);
            $criteria->compare('cardCompany.parent_id', Yii::app()->user->model->company_id, false, 'OR');
        }

        $criteria->compare('id', $this->id);
        $criteria->compare('number', $this->number);
        $criteria->compare('company_id', $this->company_id);
        $criteria->compare('active', $this->active);

        $criteria->compare('cardCompany.name', isset($this->cardCompany->name) ? $this->cardCompany->name : $this->cardCompany, true);

        $sort = new CSort;
        $sort->defaultOrder = 'company_id, number';
        $sort->applyOrder($criteria);

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
            'pagination' => false,
        ));
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'number' => 'Номер карты',
            'company_id' => 'Компания',
            'active' => 'Active',
            'create_date' => 'Create Date',
            'card_company' => 'Компания',
        );
    }

    public function beforeSave()
    {
        if ($this->isNewRecord && !$this->number) {
            $salt = self::randomSalt();
            $this->number = $salt . str_pad($this->company_id, 4, "0", STR_PAD_LEFT);
        } elseif($this->IsNewRecord) {
            $numPointList = explode('-', $this->number);
            if(count($numPointList) > 1) {
                for ($num = intval($numPointList[0]); $num < intval($numPointList[1]); $num++) {
                    $card = clone $this;
                    $card->number = $num;
                    $card->save();
                }
                $this->number = intval($numPointList[1]);
            }
            $existed = Card::model()->find('number = :number', [':number' => $this->number]);
            if ($existed) {
                Act::model()->updateAll(['is_fixed' => 1], 'card_id = :card_id', [':card_id' => $existed->id]);
                $existed->company_id = $this->company_id;
                $existed->save();
                return false;
            }
        }
        return true;
    }

    public function randomSalt()
    {
        return str_pad(rand(1, 9999), 4, "0", STR_PAD_RIGHT);
    }
}