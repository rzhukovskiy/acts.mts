<?php

namespace common\models\query;
use Yii;
use common\models\Company;

/**
 * This is the ActiveQuery class for [[\common\models\Car]].
 *
 * @see \common\models\Car
 */
class CarQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    public function after($id)
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * @inheritdoc
     * @return \common\models\Car[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Car|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byType($id)
    {
        return $this->andWhere(['type_id' => $id]);
    }

    /**
     * @param $number
     * @return $this
     */
    public function byNumber($number)
    {
        return $this->andWhere(['number' => $number]);
    }

    /**
     * Формируе запрос на кол-во ТС для компании
     *
     * @param null|int $companyId
     * @return CarQuery $query
     */
    public function carsCountByTypes($companyId = null)
    {
        $query = $this
            ->alias('car')
            ->select('count(car.id) as carsCountByType, car.type_id')
            ->join('INNER JOIN', '{{%type}} type', 'car.type_id = type.id')
            ->groupBy('car.type_id');

        if (!is_null($companyId))

            // ищем дочерние дочерних
            $queryPar = Company::find()->where(['parent_id' => $companyId])->select('id')->column();

        $arrParParIds = [];

        for ($i = 0; $i < count($queryPar); $i++) {

            $arrParParIds[] = $queryPar[$i];

            $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]])->select('id')->column();

            for ($j = 0; $j < count($queryParPar); $j++) {
                $arrParParIds[] = $queryParPar[$j];
            }

        }

        $query->andWhere(['car.company_id' => $companyId])->orWhere(['car.company_id' => $arrParParIds]);

        return $query;
    }

    public function carsCount($companyId = null)
    {
        if (is_null($companyId))
            $companyId = $this->company_id;

        $query = $this
            ->andWhere();

        return $this;
    }
}
