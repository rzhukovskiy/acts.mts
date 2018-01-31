<?php

namespace common\models\search;

use common\models\TaskMy;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TopicSearch represents the model behind the search form about `common\models\TaskMy`.
 */
class TaskMySearch extends TaskMy
{
    public $from_user;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_user', 'status'], 'integer'],
            [['data', 'data_status', 'task'], 'string'],
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
        $query = TaskMy::find();

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

        if (Yii::$app->user->identity->role == User::ROLE_ADMIN) {
        if (!isset($this->from_user) && !$this->from_user) {
            $this->from_user = Yii::$app->user->identity->id;
            $query->andWhere(['from_user' => $this->from_user]);
        } else {
            // grid filtering conditions
            $query->andFilterWhere([
                'from_user' => $this->from_user,
            ]);
             }
        } else {
            $query->andFilterWhere([
                'from_user' => Yii::$app->user->identity->id,
            ]);
        }
        return $dataProvider;
    }
}
