<?php

namespace common\models\search;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Topic;

/**
 * TopicSearch represents the model behind the search form about `common\models\Topic`.
 */
class TopicSearch extends Topic
{
    public $department_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'from', 'to', 'message_id', 'created_at', 'updated_at'], 'integer'],
            [['topic'], 'safe'],
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
        $query = Topic::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'from' => $this->from,
            'to' => $this->to,
            'message_id' => $this->message_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'topic', $this->topic]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param User $user
     *
     * @return ActiveDataProvider
     */
    public function searchByUser($user)
    {
        $query = Topic::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith(['author author', 'recipient recipient', 'recipient.department recipientDepartment', 'author.department authorDepartment']);
        $query->andFilterWhere(['and', ['author.id' => $user->id], ['recipientDepartment.id' => $this->department_id]]);
        $query->orFilterWhere(['and', ['recipient.id' => $user->id], ['authorDepartment.id' => $this->department_id]]);

        return $dataProvider;
    }
}
