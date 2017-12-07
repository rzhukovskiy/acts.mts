<?php

namespace common\models\search;

use common\models\TenderLinks;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 *  TenderMemberSearch model
 * @property integer $id
 * @property integer $company_member
 * @property integer $inn
 * @property integer $city
 */
class TenderLinksSearch extends TenderLinks
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tender_id', 'member_id', 'winner'], 'integer'],
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
        $query = TenderLinks::find();

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
