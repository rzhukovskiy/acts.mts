<?php

namespace common\models\search;

use common\models\TenderOwner;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 *  TenderOwnerSearch model
 * @property integer $id
 * @property integer $tender_id
 * @property integer $tender_user
 * @property integer $status
 * @property string $text
 * @property string $link
 * @property string $data
 */
class TenderOwnerSearch extends TenderOwner
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'data', 'link'], 'string'],
            [['tender_user', 'tender_id', 'status'], 'integer'],
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
        $query = TenderOwner::find();

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

        // grid filtering conditions
        $query->andFilterWhere([
            'status' => $this->status,
        ]);

        return $dataProvider;
    }
}
