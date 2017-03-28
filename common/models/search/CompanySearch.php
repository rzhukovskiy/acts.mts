<?php

namespace common\models\search;

use common\models\Card;
use common\models\Company;
use common\models\User;
use yii;
use yii\data\ActiveDataProvider;

/**
 * CardSearch represents the model behind the search form about `common\models\Card`.
 */
class CompanySearch extends Company
{
    public $user_id;
    public $card_number;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_number', 'name'], 'string'],
            [['user_id'], 'integer'],
            [['services'], 'safe'],
            [['cartypes'], 'safe'],
            [['address'], 'safe'],
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
                'user_id', 'name', 'address', 'services', 'cartypes', 'fullAddress'
            ],
            'default' => ['card_number', 'address', 'cartypes', 'services'],
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
                //если пользователь указан и смотрим заявки - то отдаем только те разделы, которые ему доступны, и только его заявки или без пользователя
                if ($this->user_id) {
                    $currentUser = User::findOne($this->user_id);
                    if ($currentUser) {
//                        if ($this->status == self::STATUS_NEW) {
//                            $query->orWhere(['offer.user_id' => null]);
//                            $query->orWhere(['offer.user_id' => $currentUser->id]);
//                        }
                        $query->andWhere(['company.type' => array_keys($currentUser->getAllCompanyType($this->status))]);
                    }
                }
                if ($this->status == Company::STATUS_NEW) {
                    $query->orderBy('communication_at ASC');
                } else {
                    $query->orderBy('address ASC');
                }

                break;
            case self::SCENARIO_DEFAULT:
                $sort = $dataProvider->getSort();
                $sort->attributes = array_merge($sort->attributes,
                    [
                        'parent_key' => [
                            'asc'  => ['is_nested' => SORT_DESC, 'parent_key' => SORT_ASC],
                            'desc' => ['is_nested' => SORT_ASC, 'parent_key' => SORT_DESC]
                        ]
                    ]);
                $dataProvider->setSort($sort);
                $query->addSelect(['company.*']);
                $query->addParentKey();

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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchOffer($params=[])
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
                //если пользователь указан и смотрим заявки - то отдаем только те разделы, которые ему доступны, и только его заявки или без пользователя
                if ($this->user_id) {
                    $currentUser = User::findOne($this->user_id);
                    if ($currentUser) {
//                        if ($this->status == self::STATUS_NEW) {
//                            $query->orWhere(['offer.user_id' => null]);
//                            $query->orWhere(['offer.user_id' => $currentUser->id]);
//                        }
                        $query->andWhere(['company.type' => array_keys($currentUser->getAllCompanyType($this->status))]);
                    }
                }
                if ($this->status == Company::STATUS_NEW) {
                    $query->orderBy('communication_at ASC');
                } else {
                    $query->orderBy('address ASC');
                }

                break;
            case self::SCENARIO_DEFAULT:
                $sort = $dataProvider->getSort();
                $sort->attributes = array_merge($sort->attributes,
                    [
                        'parent_key' => [
                            'asc'  => ['is_nested' => SORT_DESC, 'parent_key' => SORT_ASC],
                            'desc' => ['is_nested' => SORT_ASC, 'parent_key' => SORT_DESC]
                        ]
                    ]);
                $dataProvider->setSort($sort);
                $query->addSelect(['company.*']);
                $query->addParentKey();

                break;
        }

        $query->andFilterWhere([
            'type' => $this->type,
        ]);
        $query->orFilterWhere([
            'type' => 6,
        ]);
        $query->andFilterWhere([
            'id' => $this->id,
            'company.status' => $this->status,
        ]);

        $query->leftJoin('company_service', 'company.id = company_service.company_id');
        $query->andFilterWhere(['company_service.service_id' => $this->services]);
        $query->andFilterWhere(['company_service.type_id' => $this->cartypes]);

        $query->andFilterWhere(['or like', 'address', $this->address]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        switch ($this->scenario) {
            case self::SCENARIO_OFFER:
                $query->andFilterWhere(['like', 'CONCAT_WS(",",info.index,info.city,info.street,info.house)', $this->getFullAddress()]);
                break;
        }

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchWithCard($params=[])
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

        $query->alias('company');
        $query->joinWith('acts act');
        /** @var User $currentUser */
        $query->joinWith(['info info', 'offer offer']);
        $query->groupBy('company.id');
        if ($this->user_id) {
            $currentUser = User::findOne($this->user_id);
            if ($currentUser) {
                $query->orWhere(['offer.user_id' => null]);
                $query->orWhere(['offer.user_id' => $currentUser->id]);
                $query->andWhere(['company.type' => array_keys($currentUser->getAllCompanyType($this->status))]);
            }
        }
        if ($this->card_number && !$this->address) {
            $modelCard = Card::findOne(['number' => $this->card_number]);
            if ($modelCard) {
                $query->andWhere(['or', ['act.card_id' => $modelCard->id], ['address' => $modelCard->company->address]]);
                $query->select(['company.*', 'COUNT(act.id) as service_count']);
                $query->groupBy('company.id');
                $query->orderBy('service_count DESC');
            }
        } else {
            $query->andFilterWhere(['like', 'address', $this->address]);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'company.status' => $this->status,
        ]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
