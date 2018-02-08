<?php

namespace common\models\search;

use common\models\Changes;
use yii;
use yii\data\ActiveDataProvider;

class ChangesSearch extends Changes
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $type;
    public $company_id;
    public $user_id;
    public $status;
    public $type_id;
    public $service_id;
    public $new_value;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'company_id', 'user_id', 'status', 'service_id'], 'integer'],
            [['dateFrom', 'dateTo', 'period', 'type_id', 'new_value'], 'safe'],
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

        // Фильтры
        if($this->company_id) {
            $query->andWhere(['company_id' => $this->company_id]);
        }
        if($this->user_id) {
            $query->andWhere(['user_id' => $this->user_id]);
        }
        if($this->status) {
            $query->andWhere(['status' => $this->status]);
        }
        if($this->type_id) {
            $query->andWhere(['type_id' => $this->type_id]);
        }
        if($this->service_id) {
            $query->andWhere(['service_id' => $this->service_id]);
        }
        if($this->new_value) {
            $query->andWhere(['new_value' => $this->new_value]);
        }

        $query->andWhere(['between', "DATE(FROM_UNIXTIME(`date`))", $this->dateFrom, $this->dateTo]);
        // Фильтры

        $dataProvider->sort = [
            'defaultOrder' => [
                'date'    => SORT_ASC,
            ]
        ];

        return $dataProvider;
    }

}
