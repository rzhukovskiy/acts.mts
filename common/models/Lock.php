<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%lock}}".
 *
 * @property integer $id
 * @property integer $type
 * @property string $period
 */
class Lock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%lock}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'period'], 'required'],
            [['type'], 'integer'],
            [['period'], 'string', 'max' => 255],
            [['company_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'period' => 'Period',
            'company_id' => 'Company_id',
        ];
    }

    public static function checkLocked($period, $type, $check_period = false)
    {

        if($check_period == false) {

            return Lock::find()->where(['type' => $type, 'period' => $period])->all();

        } else {

            $lockedLisk = Lock::find()->where(['type' => $type, 'period' => $period])->all();

            $closeAll = false;

            if(count($lockedLisk) > 0) {
                for ($c = 0; $c < count($lockedLisk); $c++) {
                    if ($lockedLisk[$c]["company_id"] == 0) {
                        $closeAll = true;
                    }
                }
            }

            return $closeAll;

        }

    }

}
