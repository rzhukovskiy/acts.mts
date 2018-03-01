<?php

namespace common\models\search;

use common\models\Company;
use common\models\Service;
use common\models\User;
use yii;
use yii\data\ActiveDataProvider;
use common\models\Act;

/**
 * ActSearch represents the model behind the search form about `common\models\Act`.
 * @property string $period
 * @property integer $day
 */
class ActSearch extends Act
{
    public $dateFrom;
    public $dateTo;
    public $period;
    public $createDay;
    public $day;
    public $address;
    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_number', 'card_id', 'mark_id', 'type_id', 'day', 'service_type', 'user_id'], 'integer'],
            [['car_number', 'extra_car_number', 'period', 'address'], 'string'],
            [['partner_id'], 'safe'],
            ['period', 'default', 'value' => date('n') . '-' . date('Y'), 'on' => self::SCENARIO_CLIENT],
            ['period', 'default', 'value' => date('n') . '-' . date('Y'), 'on' => self::SCENARIO_PARTNER],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return [
            self::SCENARIO_CAR => ['card_number', 'card_id', 'car_number', 'dateFrom', 'dateTo'],
            self::SCENARIO_CLIENT => ['card_number', 'check', 'client_id', 'card_id', 'mark_id', 'type_id', 'day', 'car_number', 'extra_car_number', 'period', 'service_type', 'address'],
            self::SCENARIO_PARTNER => ['card_number', 'check', 'partner_id', 'card_id', 'mark_id', 'type_id', 'day', 'car_number', 'extra_car_number', 'period', 'service_type', 'address'],
            self::SCENARIO_ERROR => ['card_number', 'check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'car_number', 'extra_car_number', 'period', 'user_id'],
            self::SCENARIO_LOSSES => ['card_number', 'check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'car_number', 'extra_car_number', 'period', 'user_id'],
            self::SCENARIO_ASYNC => ['card_number', 'check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'car_number', 'extra_car_number', 'period', 'user_id'],
            self::SCENARIO_DOUBLE => ['card_number', 'check', 'service_type', 'client_id', 'partner_id', 'card_id', 'mark_id', 'type_id', 'car_number', 'extra_car_number', 'period', 'user_id'],
            self::SCENARIO_HISTORY => ['card_number', 'client_id', 'car_number', 'dateFrom', 'dateTo', 'service_type', 'address', 'mark_id', 'type_id'],
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
        $query = Act::find();

        // Если не выбран период то показываем только прошлый месяц
        if((!isset($params['ActSearch']['dateFrom'])) && (!isset($params['ActSearch']['dateTo']))) {

            $params['ActSearch']['dateFrom'] = date("Y-m-t", strtotime("-2 month")) . 'T21:00:00.000Z';
            $params['ActSearch']['dateTo'] = date("Y-m-t", strtotime("-1 month")) . 'T21:00:00.000Z';
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $this->load($params);

        //для не админа жестко задаем company_id
        if (!empty(Yii::$app->user->identity->company_id) && !$this->client_id && Yii::$app->user->identity->company->type == Company::TYPE_OWNER) {
            $this->client_id = Yii::$app->user->identity->company->id;
        }
        if (!empty(Yii::$app->user->identity->company_id) && !$this->partner_id && Yii::$app->user->identity->company->type != Company::TYPE_OWNER) {
            $this->partner_id = Yii::$app->user->identity->company->id;
        }


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        switch ($this->scenario) {
            case self::SCENARIO_ERROR:
                $query->joinWith([
                    'type',
                    'mark',
                    'car car',
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                    'actErrors as actErrors',
                ]);
                $query->andWhere(['is NOT', 'actErrors.act_id', null]);
                $query->andWhere(['AND', ['!=', 'actErrors.error_type', 19], ['!=', 'actErrors.error_type', 20], ['!=', 'actErrors.error_type', 21]]);

                if($this->partner_id) {
                    $query->andFilterWhere(['act.partner_id' => $this->partner_id]);
                }
                if($this->client_id) {
                    $query->andFilterWhere(['act.client_id' => $this->client_id]);
                }

                $query->andFilterWhere(['like', 'card_number', $this->card_number])->andFilterWhere(['like', 'car_number', $this->car_number]);

                // загрузка страницы с привязкой к текущему пользователю
                if (isset($params['ActSearch']['user_id'])) {
                    if ($params['ActSearch']['user_id'] > 0) {
                        $this->user_id = $params['ActSearch']['user_id'];
                        $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                        $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                    }
                } else {
                    if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                        $exists = Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('department_linking', 'department_linking.company_id = act.partner_id')->where(['AND', ['act.service_type' => $this->service_type], ['!=', 'act_error.error_type', 19], ['!=', 'act_error.error_type', 20], ['!=', 'act_error.error_type', 21], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period]])->andWhere(['AND', ['department_linking.user_id' => Yii::$app->user->identity->id], ['department_linking.type' => $this->service_type]])->exists();
                        if ($exists) {
                            $this->user_id = Yii::$app->user->identity->id;
                            $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                            $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                        }
                    }
                }
                // загрузка страницы с привязкой к текущему пользователю

                $query->orderBy('partner_id, served_at');
                break;
            case self::SCENARIO_LOSSES:
                $query->joinWith([
                    'type',
                    'mark',
                    'car car',
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                    'actErrors as actErrors',
                ]);
                $query->andWhere(['is NOT', 'actErrors.act_id', null]);
                $query->andWhere(['actErrors.error_type' => 19]);

                if($this->partner_id) {
                    $query->andFilterWhere(['act.partner_id' => $this->partner_id]);
                }
                if($this->client_id) {
                    $query->andFilterWhere(['act.client_id' => $this->client_id]);
                }

                $query->andFilterWhere(['like', 'card_number', $this->card_number])->andFilterWhere(['like', 'car_number', $this->car_number]);

                // загрузка страницы с привязкой к текущему пользователю
                if (isset($params['ActSearch']['user_id'])) {
                    if ($params['ActSearch']['user_id'] > 0) {
                        $this->user_id = $params['ActSearch']['user_id'];
                        $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                        $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                    }
                } else {
                    if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                        $exists = Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('department_linking', 'department_linking.company_id = act.partner_id')->where(['AND', ['act.service_type' => $this->service_type], ['act_error.error_type' => 19], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period]])->andWhere(['AND', ['department_linking.user_id' => Yii::$app->user->identity->id], ['department_linking.type' => $this->service_type]])->exists();
                        if ($exists) {
                            $this->user_id = Yii::$app->user->identity->id;
                            $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                            $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                        }
                    }
                }
                // загрузка страницы с привязкой к текущему пользователю

                $query->orderBy('partner_id, served_at');
                break;
            case self::SCENARIO_ASYNC:
                $query->joinWith([
                    'type',
                    'mark',
                    'car car',
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                    'actErrors as actErrors',
                ]);
                $query->andWhere(['is NOT', 'actErrors.act_id', null]);
                $query->andWhere(['actErrors.error_type' => 20]);

                if($this->partner_id) {
                    $query->andFilterWhere(['act.partner_id' => $this->partner_id]);
                }
                if($this->client_id) {
                    $query->andFilterWhere(['act.client_id' => $this->client_id]);
                }

                $query->andFilterWhere(['like', 'card_number', $this->card_number])->andFilterWhere(['like', 'car_number', $this->car_number]);

                // загрузка страницы с привязкой к текущему пользователю
                if (isset($params['ActSearch']['user_id'])) {
                    if ($params['ActSearch']['user_id'] > 0) {
                        $this->user_id = $params['ActSearch']['user_id'];
                        $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                        $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                    }
                } else {
                    if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                        $exists = Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('department_linking', 'department_linking.company_id = act.partner_id')->where(['AND', ['act.service_type' => $this->service_type], ['act_error.error_type' => 20], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period]])->andWhere(['AND', ['department_linking.user_id' => Yii::$app->user->identity->id], ['department_linking.type' => $this->service_type]])->exists();
                        if ($exists) {
                            $this->user_id = Yii::$app->user->identity->id;
                            $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                            $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                        }
                    }
                }
                // загрузка страницы с привязкой к текущему пользователю

                $query->orderBy('partner_id, served_at');
                break;
            case self::SCENARIO_DOUBLE:
                $query->joinWith([
                    'type',
                    'mark',
                    'car car',
                    'card as card',
                    'client as client',
                    'partner as partner',
                    'car as car',
                    'actErrors as actErrors',
                ]);
                $query->andWhere(['is NOT', 'actErrors.act_id', null]);
                $query->andWhere(['actErrors.error_type' => 21]);

                if($this->partner_id) {
                    $query->andFilterWhere(['act.partner_id' => $this->partner_id]);
                }
                if($this->client_id) {
                    $query->andFilterWhere(['act.client_id' => $this->client_id]);
                }

                $query->andFilterWhere(['like', 'card_number', $this->card_number])->andFilterWhere(['like', 'car_number', $this->car_number]);

                // загрузка страницы с привязкой к текущему пользователю
                if (isset($params['ActSearch']['user_id'])) {
                    if ($params['ActSearch']['user_id'] > 0) {
                        $this->user_id = $params['ActSearch']['user_id'];
                        $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                        $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                    }
                } else {
                    if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                        $exists = Act::find()->innerJoin('act_error', '`act_error`.`act_id` = `act`.`id`')->innerJoin('department_linking', 'department_linking.company_id = act.partner_id')->where(['AND', ['act.service_type' => $this->service_type], ['act_error.error_type' => 21], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period]])->andWhere(['AND', ['department_linking.user_id' => Yii::$app->user->identity->id], ['department_linking.type' => $this->service_type]])->exists();
                        if ($exists) {
                            $this->user_id = Yii::$app->user->identity->id;
                            $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                            $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                        }
                    }
                }
                // загрузка страницы с привязкой к текущему пользователю

                $query->orderBy('partner_id, served_at');
                break;
            case self::SCENARIO_CLIENT:

                if($this->service_type == Company::TYPE_PENALTY) {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'penaltyinfo',
                        'partner partner',
                        'client client',
                    ]);
                } else {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'partner partner',
                        'client client',
                    ]);
                }

                if (!empty($this->client->children)) {

                    if (!isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {

                        if ($this->client_id == 59) {
                            $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                            $arrParParIds = [];

                            for ($i = 0; $i < count($queryPar); $i++) {
                                $arrParParIds[] = $queryPar[$i]['id'];

                                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                for ($j = 0; $j < count($queryParPar); $j++) {
                                    $arrParParIds[] = $queryParPar[$j]['id'];
                                }

                            }

                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    } else {

                        if ((Yii::$app->request->queryParams['ActSearch']['client_id'] == '') || (Yii::$app->request->queryParams['ActSearch']['client_id'] <= 0)) {

                            if ($this->client_id == 59) {
                                $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                                $arrParParIds = [];

                                for ($i = 0; $i < count($queryPar); $i++) {
                                    $arrParParIds[] = $queryPar[$i]['id'];

                                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                    for ($j = 0; $j < count($queryParPar); $j++) {
                                        $arrParParIds[] = $queryParPar[$j]['id'];
                                    }

                                }

                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    }

                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }

                // загрузка страницы с привязкой к текущему пользователю
                $role = Yii::$app->user->identity->role;
                if ($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) {

                    if (isset($params['ActSearch']['user_id'])) {
                        if ($params['ActSearch']['user_id'] > 0) {
                            $this->user_id = $params['ActSearch']['user_id'];
                            $query->innerJoin('department_linking', 'department_linking.company_id = act.client_id');
                            $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => Company::TYPE_OWNER]]);
                        }
                    } else {
                        if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                            if (!(Yii::$app->controller->id == 'act' && Yii::$app->controller->action->id == 'export')) {
                                $exists = Act::find()->innerJoin('department_linking', 'department_linking.company_id = act.client_id')->where(['AND', ['act.service_type' => $this->service_type], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period]])->andWhere(['AND', ['department_linking.user_id' => Yii::$app->user->identity->id], ['department_linking.type' => Company::TYPE_OWNER]])->exists();
                                if ($exists) {
                                    $this->user_id = Yii::$app->user->identity->id;
                                    $query->innerJoin('department_linking', 'department_linking.company_id = act.client_id');
                                    $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => Company::TYPE_OWNER]]);
                                }
                            }
                        }
                    }

                }
                // загрузка страницы с привязкой к текущему пользователю

                $query->orderBy('client.parent_id, act.client_id, served_at');
                break;

            case self::SCENARIO_PARTNER:

                if($this->service_type == Company::TYPE_PENALTY) {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'penaltyinfo',
                        'partner partner',
                        'client client',
                    ]);
                } else {
                    $query->joinWith([
                        'type',
                        'mark',
                        'card',
                        'car',
                        'partner partner',
                        'client client',
                    ]);
                }

                $query->andFilterWhere(['partner_id' => $this->partner_id]);

                // загрузка страницы с привязкой к текущему пользователю
                $role = Yii::$app->user->identity->role;
                if ($role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER || $role == User::ROLE_MANAGER) {

                    if (isset($params['ActSearch']['user_id'])) {
                        if ($params['ActSearch']['user_id'] > 0) {
                            $this->user_id = $params['ActSearch']['user_id'];
                            $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                            $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                        }
                    } else {
                        if (Yii::$app->user->identity->role != User::ROLE_ADMIN) {
                            if (!(Yii::$app->controller->id == 'act' && Yii::$app->controller->action->id == 'export')) {
                                $exists = Act::find()->innerJoin('department_linking', 'department_linking.company_id = act.partner_id')->where(['AND', ['act.service_type' => $this->service_type], ['DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period]])->andWhere(['AND', ['department_linking.user_id' => Yii::$app->user->identity->id], ['department_linking.type' => $this->service_type]])->exists();
                                if ($exists) {
                                    $this->user_id = Yii::$app->user->identity->id;
                                    $query->innerJoin('department_linking', 'department_linking.company_id = act.partner_id');
                                    $query->andWhere(['AND', ['department_linking.user_id' => $this->user_id], ['department_linking.type' => $this->service_type]]);
                                }
                            }
                        }
                    }

                }
                // загрузка страницы с привязкой к текущему пользователю

                $query->orderBy('partner.parent_id, act.partner_id, served_at');
                break;

            case self::SCENARIO_HISTORY:
                $query->joinWith([
                    'type',
                    'mark',
                    'client client',
                    'partner partner',
                    'car car',
                ]);

                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
                }
                if (!empty($this->client->children)) {

                    if (!isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {

                        if ($this->client_id == 59) {
                            $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                            $arrParParIds = [];

                            for ($i = 0; $i < count($queryPar); $i++) {
                                $arrParParIds[] = $queryPar[$i]['id'];

                                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                for ($j = 0; $j < count($queryParPar); $j++) {
                                    $arrParParIds[] = $queryParPar[$j]['id'];
                                }

                            }

                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    } else {

                        if ((Yii::$app->request->queryParams['ActSearch']['client_id'] == '') || (Yii::$app->request->queryParams['ActSearch']['client_id'] <= 0)) {

                            if ($this->client_id == 59) {
                                $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                                $arrParParIds = [];

                                for ($i = 0; $i < count($queryPar); $i++) {
                                    $arrParParIds[] = $queryPar[$i]['id'];

                                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                    for ($j = 0; $j < count($queryParPar); $j++) {
                                        $arrParParIds[] = $queryParPar[$j]['id'];
                                    }

                                }

                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        } else {

                            // МФП выдает неверную детальную среднюю статистику обслуживаний на 1тс
                            if(isset(Yii::$app->request->queryParams['group'])) {
                                if (Yii::$app->request->queryParams['group'] == 'average') {

                                    if ($this->client_id == 154) {
                                        $query->andFilterWhere(['client_id' => $this->client_id]);
                                    } else {
                                        $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                                    }

                                } else {
                                    $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                                }
                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        }

                    }

                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('client.parent_id, client_id');
                break;

            case self::SCENARIO_CAR:
                $query->joinWith([
                    'type',
                    'mark',
                    'client client',
                ]);
                if ($this->dateFrom) {
                    $query->andFilterWhere(['between', 'served_at', strtotime($this->dateFrom), strtotime($this->dateTo)]);
                }
                if (!empty($this->client->children)) {

                    if (!isset(Yii::$app->request->queryParams['ActSearch']['client_id'])) {

                        if ($this->client_id == 59) {
                            $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                            $arrParParIds = [];

                            for ($i = 0; $i < count($queryPar); $i++) {
                                $arrParParIds[] = $queryPar[$i]['id'];

                                $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                for ($j = 0; $j < count($queryParPar); $j++) {
                                    $arrParParIds[] = $queryParPar[$j]['id'];
                                }

                            }

                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    } else {

                        if ((Yii::$app->request->queryParams['ActSearch']['client_id'] == '') || (Yii::$app->request->queryParams['ActSearch']['client_id'] <= 0)) {

                            if ($this->client_id == 59) {
                                $queryPar = Company::find()->where(['parent_id' => $this->client_id])->all();

                                $arrParParIds = [];

                                for ($i = 0; $i < count($queryPar); $i++) {
                                    $arrParParIds[] = $queryPar[$i]['id'];

                                    $queryParPar = Company::find()->where(['parent_id' => $queryPar[$i]['id']])->all();

                                    for ($j = 0; $j < count($queryParPar); $j++) {
                                        $arrParParIds[] = $queryParPar[$j]['id'];
                                    }

                                }

                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id], ['client_id' => $arrParParIds]]);

                            } else {
                                $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                            }

                        } else {
                            $query->andFilterWhere(['OR', ['client.parent_id' => $this->client_id], ['client_id' => $this->client_id]]);
                        }

                    }

                } else {
                    $query->andFilterWhere(['client_id' => $this->client_id]);
                }
                $query->orderBy('parent_id, client_id, served_at DESC');
                break;

            default:
                $query->joinWith([
                    'type',
                    'mark',
                    'card',
                    'partner',
                ]);
                $query->andFilterWhere([
                    'client_id' => $this->client_id,
                    'partner_id' => $this->partner_id,
                ]);

                $query->orderBy('parent_id, partner_id, served_at');
        }

        // grid filtering conditions
        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'act.type_id' => $this->type_id,
            'act.mark_id' => $this->mark_id,
            'partner.address' => $this->address,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DAY(FROM_UNIXTIME(`served_at`))' => $this->day,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);

        $query->andFilterWhere(['like', 'act.car_number', $this->car_number])
            ->andFilterWhere(['like', 'act.extra_car_number', $this->extra_car_number])
            ->andFilterWhere(['like', 'check', $this->check]);

        $dataProvider->setSort([
            'attributes' => [
                'card_number' => [
                    'label' => 'Карта',
                    'default' => SORT_ASC
                ],
            ]
        ]);

        return $dataProvider;
    }

    /**
     * @return ActiveDataProvider
     */
    public function searchClient()
    {
        $query = Act::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'act.type_id' => $this->type_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DAY(FROM_UNIXTIME(`served_at`))' => $this->day,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);
        $query->joinWith('client');
        $query->groupBy('client_id');

        return $dataProvider;
    }

    /**
     * @return ActiveDataProvider
     */
    public function searchPartner()
    {
        $query = Act::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);


        $query->alias('act');
        $query->andFilterWhere([
            'id' => $this->id,
            'card_id' => $this->card_id,
            'act.type_id' => $this->type_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'service_type' => $this->service_type,
            'DATE_FORMAT(FROM_UNIXTIME(`served_at`), "%c-%Y")' => $this->period,
            'DATE_FORMAT(FROM_UNIXTIME(`act`.`created_at`), "%Y-%m-%d")' => $this->createDay,
        ]);
        $query->joinWith('partner');
        $query->groupBy('partner_id');

        return $dataProvider;
    }
}
