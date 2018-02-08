<?php

namespace common\models\search;

use common\models\ServiceReplace;
use yii\data\ActiveDataProvider;

class ServiceReplaceSearch extends ServiceReplace
{

    public $type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'partner_id', 'client_id', 'type_client', 'type_partner', 'mark_client', 'mark_partner'], 'integer'],
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
        $query = ServiceReplace::find();

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

        // Разделяем по типам
        if($this->type) {
            $query->andWhere(['type' => $this->type]);
        }

        // Фильтр по партнерам
        if($this->partner_id) {
            $query->andWhere(['partner_id' => $this->partner_id]);
        }

        // Фильтр по клиентам
        if($this->client_id) {
            $query->andWhere(['client_id' => $this->client_id]);
        }

        // Фильтр по типу тс
        if($this->type_client) {
            $query->andWhere(['type_client' => $this->type_client]);
        }
        if($this->type_partner) {
            $query->andWhere(['type_partner' => $this->type_partner]);
        }

        // Фильтр по марке тс
        if($this->mark_client) {
            $query->andWhere(['mark_client' => $this->mark_client]);
        }
        if($this->mark_partner) {
            $query->andWhere(['mark_partner' => $this->mark_partner]);
        }

        $dataProvider->sort = [
            'defaultOrder' => [
                'id'    => SORT_ASC,
            ]
        ];

        return $dataProvider;
    }

}
