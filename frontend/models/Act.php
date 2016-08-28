<?php

namespace frontend\models;

use common\models\Act as CommonAct;

/**
 * Act model
 */
class Act extends CommonAct
{
    public $countServe; // сколько обслужено машин (кол-во актов)

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['countServe'], 'safe'],
        ];

        return array_merge(parent::rules(), $rules);
    }

    public function attributeLabels()
    {
        $labels = [
            'countServe' => 'Обслужено',
        ];

        return array_merge($labels, parent::attributeLabels());
    }

}