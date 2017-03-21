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
        ];
    }

    public static function CheckLocked($period, $type, $check_period = false)
    {

        if(false == false) {

            return (new \yii\db\Query())
                ->select(['id', 'company_id'])
                ->from('{{%lock}}')
                ->where(['type' => $type])
                ->andWhere(['period' => $period])
                ->all();

        } else {

            $LockedLisk = (new \yii\db\Query())
                ->select(['id', 'company_id'])
                ->from('{{%lock}}')
                ->where(['type' => $type])
                ->andWhere(['period' => $period])
                ->all();

            $CloseAll = false;

            if(count($LockedLisk) > 0) {
                for ($c = 0; $c < count($LockedLisk); $c++) {
                    if ($LockedLisk[$c]["company_id"] == 0) {
                        $CloseAll = true;
                    }
                }
            }

            return $CloseAll;

        }

    }

}
