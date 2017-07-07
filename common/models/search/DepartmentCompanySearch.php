<?php

namespace common\models\search;

use common\models\DepartmentCompany;
use yii;
use yii\data\ActiveDataProvider;

class DepartmentCompanySearch extends DepartmentCompany
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $type;
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'user_id'], 'integer'],
            [['dateFrom', 'dateTo', 'period'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return [
            'new' => ['dateFrom', 'dateTo', 'type', 'period'],
            'shownew' => ['dateFrom', 'dateTo', 'type', 'period', 'user_id'],
            'archive' => ['dateFrom', 'dateTo', 'type', 'period'],
            'showarchive' => ['dateFrom', 'dateTo', 'type', 'period', 'user_id'],
            'default' => [],
        ];
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
        $query = DepartmentCompany::find();

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

        switch ($this->scenario) {
            case 'new':

                $query->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->where(['OR', ['AND', '`department_company`.`remove_date` IS NULL', '`company`.`status` = 1'], ['AND', '`department_company`.`remove_date` IS NOT NULL', '`company`.`status` = 2']])->andWhere('`department_company`.`user_id` > 0')->andWhere(['`company`.`type`' => $this->type])->select('`department_company`.*, `company`.`id`, `company`.`name`, COUNT(Distinct `department_company`.`company_id`) as companyNum')->groupBy('`department_company`.`user_id`');

                $query->andWhere(['between', "DATE(FROM_UNIXTIME(`company`.`created_at`))", $this->dateFrom, $this->dateTo]);

                $dataProvider->sort = [
                    'defaultOrder' => [
                        'user_id'    => SORT_DESC,
                    ]
                ];

                break;

            case 'shownew':

                $query->with('company')->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->where(['OR', ['AND', '`department_company`.`remove_date` IS NULL', '`company`.`status` = 1'], ['AND', '`department_company`.`remove_date` IS NOT NULL', '`company`.`status` = 2']])->andWhere(['`department_company`.`user_id`' => $this->user_id])->andWhere(['`company`.`type`' => $this->type])->select('`department_company`.*, `company`.`id`, `company`.`name`, `company`.`created_at`')->orderBy('`company`.`created_at` ASC');

                $query->andWhere(['between', "DATE(FROM_UNIXTIME(`company`.`created_at`))", $this->dateFrom, $this->dateTo]);

                break;

            case 'archive':

                $query->where(['>', '`department_company`.`user_id`', 0])->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere(['not', ['`department_company`.`remove_date`' => null]])->andWhere(['`company`.`type`' => $this->type])->andWhere(['`company`.`status`' => 2])->select('`department_company`.*, `company`.`id`, `company`.`name`, COUNT(Distinct `department_company`.`company_id`) as companyNum')->groupBy('`department_company`.`user_id`');

                $query->andWhere(['between', "DATE(FROM_UNIXTIME(`department_company`.`remove_date`))", $this->dateFrom, $this->dateTo]);

                $dataProvider->sort = [
                    'defaultOrder' => [
                        'user_id'    => SORT_DESC,
                    ]
                ];

                break;

            case 'showarchive':

                $query->with('company')->where(['`department_company`.`remove_id`' => $this->user_id])->innerJoin('company', '`company`.`id` = `department_company`.`company_id`')->andWhere(['not', ['`department_company`.`remove_date`' => null]])->andWhere(['`company`.`type`' => $this->type])->andWhere(['`company`.`status`' => 2])->select('`department_company`.*, `company`.`id`, `company`.`name`, `company`.`created_at`')->orderBy('`company`.`created_at` ASC');

                $query->andWhere(['between', "DATE(FROM_UNIXTIME(`department_company`.`remove_date`))", $this->dateFrom, $this->dateTo]);

                break;

        }

        return $dataProvider;
    }

}
