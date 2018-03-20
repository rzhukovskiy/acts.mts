<?php

namespace common\models\search;

use common\models\Informing;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TopicSearch represents the model behind the search form about `common\models\Informing`.
 */
class InformingSearch extends Informing
{
    public $type;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_user', 'is_archive'], 'integer'],
            [['text', 'date_create'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Informing::find();

        // add conditions that should always apply here

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


        return $dataProvider;
    }
}
