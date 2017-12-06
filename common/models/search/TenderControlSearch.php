<?php

namespace common\models\search;

use common\models\TenderControl;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 *  TenderControlSearch model
 * @package common\models
 * @property integer $user_id
 * @property integer $site_address
 * @property integer $type_payment
 * @property integer $is_archive
 * @property float $send
 * @property float $return
 * @property float $balance_work
 * @property string $date_send
 * @property string $date_enlistment
 * @property string $money_unblocking
 * @property string $date_return
 * @property string $platform
 * @property string $customer
 * @property string $purchase
 */
class TenderControlSearch extends TenderControl
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'site_address', 'type_payment', 'is_archive'], 'integer'],
            [['send', 'return'], 'safe'],
            [['date_send', 'date_enlistment', 'money_unblocking', 'date_return', 'platform', 'customer', 'purchase'], 'string'],
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
        $query = TenderControl::find();

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

        if (isset($this->is_archive)) {
            if ($this->is_archive == 1) {

            } else {
                $this->is_archive = 0;
            }
        } else {
            $this->is_archive = 0;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'type_payment' => $this->type_payment,
            'is_archive' => $this->is_archive,
        ]);

        $query->andFilterWhere(['like', 'send', $this->send])
                ->andFilterWhere(['like', 'platform', $this->platform])
                ->andFilterWhere(['like', 'customer', $this->customer])
                ->andFilterWhere(['like', 'purchase', $this->purchase])
                ->andFilterWhere(['like', 'return', $this->return]);
        return $dataProvider;
    }
}
