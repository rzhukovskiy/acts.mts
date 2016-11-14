<?php

namespace common\models\search;

use common\models\CompanyOffer;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Company;
use yii\db\Expression;

/**
 * CardSearch represents the model behind the search form about `common\models\Card`.
 */
class CompanySearch extends Company
{
    public $user_id;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'address'], 'string'],
            [['user_id'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return [
            self::SCENARIO_OFFER => [
                'user_id', 'name', 'address', 'fullAddress'
            ],
            'default' => [],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params=[])
    {
        $query = Company::find();

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
        $query->alias('company');

        switch ($this->scenario) {
            case self::SCENARIO_OFFER:
                /** @var User $currentUser */
                $query->joinWith(['info info', 'offer offer']);
                if ($this->user_id) {
                    $currentUser = User::findOne($this->user_id);
                    if ($currentUser) {
                        $query->where(['or', ['offer.user_id' => null], ['offer.user_id' => $currentUser->id]]);
                        $query->where(['in', 'type', array_keys($currentUser->getAllCompanyType($this->status))]);
                    }
                }
                if ($this->status == Company::STATUS_NEW) {
                    $query->orderBy('communication_at ASC');
                } else {
                    $query->orderBy('address ASC');
                }

                break;
        }
        
        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'company.status' => $this->status,
        ]);
        $query->andFilterWhere(['like', 'address', $this->address]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        switch ($this->scenario) {
            case self::SCENARIO_OFFER:
                $query->andFilterWhere(['like', 'CONCAT_WS(",",info.index,info.city,info.street,info.house)', $this->getFullAddress()]);
                break;
        }

        return $dataProvider;
    }
}
