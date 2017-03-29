<?php

namespace frontend\models\search;

use common\models\Act;
use common\models\Car;
use common\models\Company;
use common\models\Mark;
use common\models\Type;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Card;
use common\models\search\CardSearch as CommonCardSearch;
use yii\helpers\ArrayHelper;

/**
 * CardSearch represents the model behind the search form about `common\models\Card`.
 */
class CardSearch extends CommonCardSearch
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'company_id', 'number', 'status', 'created_at', 'updated_at'], 'integer'],
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
        $query = Card::find()->orderBy('company_id');

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

        $query->joinWith(['company company']);
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'is_lost' => $this->is_lost,
            'number' => $this->number,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
        $query->andFilterWhere(['company.status' => Company::STATUS_ACTIVE]);
        $query->andFilterWhere(['parent_id' => $this->company_id])->orFilterWhere(['company_id' => $this->company_id]);

        return $dataProvider;
    }


    /**
     * Подмешиваем данные о машинах в датпровайдер карт
     * @param $dataProvider ActiveDataProvider
     * @return mixed
     */
    public static function addCarToSearch($dataProvider)
    {
        $car = Yii::$app->db->createCommand('SELECT card_id, car_id, mark.name as mark,type.name as type
            FROM (
                SELECT card_id, car_id, count(car_id) as cn FROM ' . Act::tableName() . ' GROUP BY car_id, card_id ORDER BY card_id, cn DESC
            ) as act
            LEFT JOIN ' . Car::tableName() . ' as car ON car.id=act.car_id
            LEFT JOIN ' . Type::tableName() . ' as type ON car.type_id=type.id
            LEFT JOIN ' . Mark::tableName() . ' as mark ON car.mark_id=mark.id
            GROUP BY card_id
        ')->queryAll();
        $car = ArrayHelper::index($car, 'card_id');
        foreach ($dataProvider->getModels() as &$model) {
            if (isset($car[$model->id])) {
                $model->car_number = $car[$model->id]['number'];
                $model->car_type = $car[$model->id]['type'];
                $model->car_mark = $car[$model->id]['mark'];                
            }
        }

        return $dataProvider;
    }
}
