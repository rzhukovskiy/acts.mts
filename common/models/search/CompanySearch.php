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
    public $email;
    public $car_type;
    public $dep_user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_number', 'name'], 'string'],
            [['user_id'], 'integer'],
            [['car_type'], 'integer'],
            [['services'], 'safe'],
            [['cartypes'], 'safe'],
            [['email'], 'safe'],
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
                'user_id', 'name', 'address', 'services', 'cartypes', 'fullAddress', 'email', 'car_type'
            ],
            'default' => ['card_number', 'name', 'address', 'cartypes', 'services', 'email', 'car_type'],
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
                //???????? ???????????????????????? ???????????? ?? ?????????????? ???????????? - ???? ???????????? ???????????? ???? ??????????????, ?????????????? ?????? ????????????????, ?? ???????????? ?????? ???????????? ?????? ?????? ????????????????????????
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
                if ($this->status == Company::STATUS_NEW || $this->status == Company::STATUS_NEW2) {

                    /*if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
                        $query->leftJoin('department_company', 'department_company.company_id = company.id');

                        if(isset($params['CompanySearch']['dep_user_id'])) {
                            if($params['CompanySearch']['dep_user_id'] > 0) {
                                $this->dep_user_id = $params['CompanySearch']['dep_user_id'];
                                $query->andWhere(['department_company.user_id' => $params['CompanySearch']['dep_user_id']]);
                            }
                        }

                        $query->leftJoin('user', 'department_company.user_id = user.id');
                        $query->select('`company`.*, `department_company`.`user_id`, `department_company`.`company_id`');
                    } else {
                        $query->leftJoin('department_company', 'department_company.company_id = company.id');
                        $query->leftJoin('user', 'department_company.user_id = user.id');
                        $query->andWhere(['OR', ['department_company.user_id' => Yii::$app->user->identity->id], ['department_company.user_id' => 0]]);
                        $query->select('`company`.*, `department_company`.`user_id`, `department_company`.`company_id`');
                    }*/

                    $query->leftJoin('department_company', 'department_company.company_id = company.id');

                    $query->leftJoin('user', 'department_company.user_id = user.id');
                    $query->select('`company`.*, `department_company`.`user_id`, `department_company`.`company_id`');

                    $query->orderBy('department_company.user_id DESC, communication_at ASC');

                } else if ($this->status == Company::STATUS_TENDER) {
                    // ???? ???????????? ???????????? ???? ????????????????, ??.?? ?? ???????????? ???????????????????? ?? ??-???? searchTender

                    /*$query->leftJoin('tender_hystory', 'tender_hystory.company_id = company.id');

                    if(isset($params['CompanySearch']['dep_user_id'])) {
                        if($params['CompanySearch']['dep_user_id'] > 0) {
                            $this->dep_user_id = $params['CompanySearch']['dep_user_id'];
                            $query->andWhere(['tender_hystory.user_id' => $params['CompanySearch']['dep_user_id']]);
                        }
                    }
                    $query->leftJoin('user', 'tender_hystory.user_id = user.id');
                    $query->select('`company`.*, `tender_hystory`.`user_id`, `tender_hystory`.`company_id`');
                    $query->orderBy('tender_hystory.user_id DESC, communication_at ASC');*/

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

        if($this->email) {
            $query->innerJoin('company_member', 'company_member.company_id = company.id');
            $query->andFilterWhere(['OR', ['like', 'info.email', $this->email], ['like', 'company_member.email', $this->email]]);
        }

        if(($this->car_type == '0') || ($this->car_type == '1') || ($this->car_type == '2')) {
            $query->andFilterWhere(['car_type' => $this->car_type]);
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
                //???????? ???????????????????????? ???????????? ?? ?????????????? ???????????? - ???? ???????????? ???????????? ???? ??????????????, ?????????????? ?????? ????????????????, ?? ???????????? ?????? ???????????? ?????? ?????? ????????????????????????
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
                if ($this->status == Company::STATUS_NEW || $this->status == Company::STATUS_NEW2) {
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
            'company.type' => $this->type,
        ]);
        $query->orFilterWhere([
            'company.type' => 6,
        ]);
        $query->andFilterWhere([
            'company.id' => $this->id,
            'company.status' => $this->status,
        ]);

        $query->leftJoin('company_service', 'company.id = company_service.company_id');
        $query->andFilterWhere(['company_service.service_id' => $this->services]);
        $query->andFilterWhere(['company_service.type_id' => $this->cartypes]);

        $query->andFilterWhere(['or like', 'company.address', $this->address]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        switch ($this->scenario) {
            case self::SCENARIO_OFFER:
                $query->andFilterWhere(['like', 'CONCAT_WS(",",info.index,info.city,info.street,info.house)', $this->getFullAddress()]);
                break;
        }

        if(isset(Yii::$app->request->queryParams['sort'])) {
            $arrSelCarTypes = Yii::$app->request->queryParams['CompanySearch']['cartypes'];

            // ?????????????? ???????????? ???????????????? ???? ??????????????
            for($i = 0; $i < count($arrSelCarTypes); $i++) {
                if(isset($arrSelCarTypes[$i])) {
                    if ($arrSelCarTypes[$i] > 0) {

                    } else {
                        unset($arrSelCarTypes[$i]);
                    }
                } else {
                    if(count($arrSelCarTypes) == 1) {
                        $arrSelCarTypes = [];
                    }
                }
            }
            // ?????????????? ???????????? ???????????????? ???? ??????????????

            if(count($arrSelCarTypes) == 1) {
                $query->orderBy([new \yii\db\Expression('company_service.service_id = ' . Yii::$app->request->queryParams['sort'] . ' DESC, company_service.price ASC')]);
            } else {
            }
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
                $query->innerJoin('partner_exclude', 'partner_exclude.client_id=' . $modelCard->company->id . ' AND partner_exclude.partner_id=company.id');
                $query->andWhere(['OR',['act.card_id' => $modelCard->id], ['address' => $modelCard->company->address]]);
                $query->select(['company.*', 'COUNT(act.id) as service_count']);
                $query->groupBy('company.id');
                $query->orderBy('service_count DESC');
            }
        } else if ($this->card_number && $this->address) {
            $modelCard = Card::findOne(['number' => $this->card_number]);
            if ($modelCard) {
                $query->innerJoin('partner_exclude', 'partner_exclude.client_id=' . $modelCard->company->id . ' AND partner_exclude.partner_id=company.id');
                $query->andWhere(['act.card_id' => $modelCard->id]);
                $query->andWhere(['like', 'address', $this->address]);
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

    public function searchTender($params=[])
    {
        $query = Company::find()->innerJoin('tender_hystory', 'tender_hystory.company_id = company.id')->where(['!=', 'company.status', Company::STATUS_DELETED]);

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

        $query->joinWith(['info info', 'offer offer']);

        if(isset($params['CompanySearch']['dep_user_id'])) {
            if($params['CompanySearch']['dep_user_id'] > 0) {
                $this->dep_user_id = $params['CompanySearch']['dep_user_id'];
                $query->andWhere(['tender_hystory.user_id' => $params['CompanySearch']['dep_user_id']]);
            }
        }
        $query->leftJoin('user', 'tender_hystory.user_id = user.id');
        $query->select('`company`.*, `tender_hystory`.`user_id`, `tender_hystory`.`company_id`');
        $query->orderBy('tender_hystory.user_id DESC');

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
