<?php

namespace common\models\search;

use common\models\CarHistory;
use common\models\Changes;
use yii;
use yii\data\ActiveDataProvider;

class ChangesSearch extends Changes
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['dateFrom', 'dateTo', 'period'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Changes::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // Разделяем по разделам
        if($this->type) {
            $query->andWhere(['type' => $this->type]);
        }

        // Добавляем вкладки типов для цены
        if($this->type == Changes::TYPE_PRICE) {
            $query->andWhere(['sub_type' => $this->sub_type]);
        }

        // Если не выбран период то показываем только текущий год
        if(!isset($this->dateFrom)) {
            $this->dateFrom = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
        }

        if(!isset($this->dateTo)) {
            $this->dateTo = date("Y-m-t") . 'T21:00:00.000Z';
        }
        // Если не выбран период то показываем только текущий год

        $query->andWhere(['between', "DATE(FROM_UNIXTIME(`date`))", $this->dateFrom, $this->dateTo]);

        $dataProvider->sort = [
            'defaultOrder' => [
                'date'    => SORT_ASC,
            ]
        ];

        return $dataProvider;
    }

}
