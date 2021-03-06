<?php

namespace common\models\search;

use common\models\Contact;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * CarSearch represents the model behind the search form about `common\models\Car`.
 * @property string $period
 */
class ContactSearch extends Contact
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['id', 'company_id'], 'integer'],
            [['id'], 'integer'],
            [['name', 'phone', 'email', 'position'], 'safe'],
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
        $query = Contact::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        $query->andFilterWhere([
            'id'   => $this->id,
            'type' => $this->type,
            //'company_id' => $this->company_id,
        ]);
        /*
        $query->joinWith([
            'company company',
            'company.serviceTypes service_type',
        ]);

        $query->andFilterWhere(['company.parent_id' => $this->company_id])
            ->orFilterWhere(['company_id' => $this->company_id]);
        $query->andFilterWhere(['company.type' => $this->type]);
        $query->orFilterWhere(['service_type.type' => $this->type]);
        */

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'phone', $this->phone]);
        $query->andFilterWhere(['like', 'email', $this->email]);
        $query->andFilterWhere(['like', 'position', $this->position]);

        return $dataProvider;
    }
}
