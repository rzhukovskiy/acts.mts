<?php

namespace common\models\search;

use common\models\TaskUser;
use common\models\TaskUserLink;
use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TopicSearch represents the model behind the search form about `common\models\TaskUser`.
 */
class TaskUserLinkSearch extends TaskUserLink
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'for_user_copy'], 'integer'],
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
        $query = TaskUserLink::find();

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
            'for_user_copy' => $this->for_user_copy,
        ]);

        return $dataProvider;
    }
}
