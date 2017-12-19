<?php
namespace common\models\search;

use common\models\ExpenseCompany;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

 /**
 * @property integer $type
 */
class ExpenseCompanySearch extends ExpenseCompany
{
    public $type;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['name'], 'string'],

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
        $query = ExpenseCompany::find();


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

        $query->andWhere(['type' => $this->type]);
        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}