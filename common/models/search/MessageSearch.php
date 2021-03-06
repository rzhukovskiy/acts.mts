<?php

namespace common\models\search;

use common\models\Message;
use common\models\User;
use yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * MessageSearch represents the model behind the search form about `common\models\Message`.
 */
class MessageSearch extends Message
{
    public $department_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_from', 'user_to', 'topic_id', 'created_at', 'updated_at', 'is_read'], 'integer'],
            [['text'], 'safe'],
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
        $query = Message::find();

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
            'user_from' => $this->user_from,
            'user_to' => $this->user_to,
            'topic_id' => $this->topic_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_read' => $this->is_read,
        ]);

        $query->andFilterWhere(['like', 'text', $this->text]);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param User $user
     *
     * @return ActiveDataProvider
     */
    public function searchInboxByUser($user)
    {
        $query = Message::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $expression = new Expression('message_id = message.id');
        $query->joinWith(['topic', 'author.department'])
            ->alias('message')
            ->where([
                'department_id' => $this->department_id,
                'user_to' => $user->id,
            ])->andWhere($expression);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param User $user
     *
     * @return ActiveDataProvider
     */
    public function searchOutboxByUser($user)
    {
        $query = Message::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $expression = new Expression('message_id = message.id');
        $query->joinWith(['topic', 'recipient', 'recipient.department'])
            ->alias('message')
            ->where([
                'department_id' => $this->department_id,
                'user_from' => $user->id,
            ])->andWhere($expression);

        return $dataProvider;
    }
}
