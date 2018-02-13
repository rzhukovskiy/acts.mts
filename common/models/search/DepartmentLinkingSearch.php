<?php

namespace common\models\search;

use common\models\DepartmentLinking;
use yii;
use yii\data\ActiveDataProvider;

class DepartmentLinkingSearch extends DepartmentLinking
{
    public $dateFrom;
    public $dateTo;
    public $type;
    public $company_id;
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'company_id', 'user_id'], 'integer'],
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
        $query = DepartmentLinking::find();

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

        // Фильтры
        if($this->company_id) {
            $query->andWhere(['company_id' => $this->company_id]);
        }
        if($this->user_id) {
            $query->andWhere(['user_id' => $this->user_id]);
        }
        // Фильтры

        $dataProvider->sort = [
            'defaultOrder' => [
                'id'    => SORT_ASC,
            ]
        ];

        return $dataProvider;
    }

}
