<?php
/**
 * Created by PhpStorm.
 * User: ruslanzh
 * Date: 09/08/16
 * Time: 19:23
 */

namespace frontend\widgets\Menu;

use common\models\Act;
use common\models\Company;
use common\models\search\ActSearch;
use common\models\Service;
use common\models\User;
use Yii;
use yii\bootstrap\Widget;

class menuLeftWidget extends Widget
{
    /**
     * @var $items []
     * label: string, optional
     * encode: boolean, optional
     * url: string or array, optional
     * visible: boolean, optional
     * items: array, optional
     * active: boolean, optional
     * template: string, optional
     * submenuTemplate: string, optional
     * options: array, optional
     */
    public $items;

    //инициализация пунктов меню
    protected function getItems()
    {
        if (!empty($this->items)) {
            return $this->items;
        }

        $errorsCount = 0;
        foreach (Service::$listType as $type_id => $typeData) {
            $searchModel = new ActSearch(['scenario' => Act::SCENARIO_ERROR]);
            $searchModel->service_type = $type_id;
            $errorsCount += $searchModel->search(Yii::$app->request->queryParams)->getCount();
        }
        $lossesCount = 0;
        foreach (Service::$listType as $type_id => $typeData) {
            $searchModel = new ActSearch(['scenario' => Act::SCENARIO_LOSSES]);
            $searchModel->service_type = $type_id;
            $lossesCount += $searchModel->search(Yii::$app->request->queryParams)->getCount();
        }
        $asyncCount = 0;
        foreach (Service::$listType as $type_id => $typeData) {
            $searchModel = new ActSearch(['scenario' => Act::SCENARIO_ASYNC]);
            $searchModel->service_type = $type_id;
            $asyncCount += $searchModel->search(Yii::$app->request->queryParams)->getCount();
        }

        $items = [];
        // Admin links
        if (Yii::$app->user->identity && in_array(Yii::$app->user->identity->role,
            [
                User::ROLE_ADMIN,
                User::ROLE_MANAGER,
                User::ROLE_WATCHER
            ])
        ) {
            /** @var Company $company */
            $company = Company::findOne(['id' => Yii::$app->request->get('id')]);

            $items = [
                [
                    'label'  => 'Компании',
                    'url'    => ['/company/list?type=' . Company::TYPE_OWNER],
                    'active' => (Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_OWNER ||
                            ($company && $company->type == Company::TYPE_OWNER))),
                ],
                [
                    'label'  => 'Партнеры',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'company'&&Yii::$app->request->get('type') != Company::TYPE_OWNER,
                    'items'  => [
                        [
                            'label'  => Company::$listType[Company::TYPE_WASH]['ru'],
                            'url'    => ['/company/list?type=' . Company::TYPE_WASH],
                            'active' => (Yii::$app->controller->id == 'company' &&
                                (Yii::$app->request->get('type') == Company::TYPE_WASH ||
                                    ($company && $company->type == Company::TYPE_WASH))),
                        ],
                        [
                            'label'  => Company::$listType[Company::TYPE_SERVICE]['ru'],
                            'url'    => ['/company/list?type=' . Company::TYPE_SERVICE],
                            'active' => (Yii::$app->controller->id == 'company' &&
                                (Yii::$app->request->get('type') == Company::TYPE_SERVICE ||
                                    ($company && $company->type == Company::TYPE_SERVICE))),
                        ],
                        [
                            'label'  => Company::$listType[Company::TYPE_TIRES]['ru'],
                            'url'    => ['/company/list?type=' . Company::TYPE_TIRES],
                            'active' => (Yii::$app->controller->id == 'company' &&
                                (Yii::$app->request->get('type') == Company::TYPE_TIRES ||
                                    ($company && $company->type == Company::TYPE_TIRES))),
                        ],
                        [
                            'label'  => Company::$listType[Company::TYPE_DISINFECT]['ru'],
                            'url'    => ['/company/list?type=' . Company::TYPE_DISINFECT],
                            'active' => (Yii::$app->controller->id == 'company' &&
                                (Yii::$app->request->get('type') == Company::TYPE_DISINFECT ||
                                    ($company && $company->type == Company::TYPE_DISINFECT))),
                        ],
                        [
                            'label'  => Company::$listType[Company::TYPE_UNIVERSAL]['ru'],
                            'url'    => ['/company/list?type=' . Company::TYPE_UNIVERSAL],
                            'active' => (Yii::$app->controller->id == 'company' &&
                                (Yii::$app->request->get('type') == Company::TYPE_UNIVERSAL ||
                                    ($company && $company->type == Company::TYPE_UNIVERSAL))),
                        ],
                        [
                            'label'  => Company::$listType[Company::TYPE_PARKING]['ru'],
                            'url'    => ['/company/list?type=' . Company::TYPE_PARKING],
                            'active' => (Yii::$app->controller->id == 'company' &&
                                (Yii::$app->request->get('type') == Company::TYPE_PARKING ||
                                    ($company && $company->type == Company::TYPE_PARKING))),
                        ],
                    ],
                ],
                [
                    'label'  => 'Услуги',
                    'url'    => ['service/index', 'ServiceSearch[type]' => Service::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'service',
                ],
                [
                    'label'  => 'Статистика услуг',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'statservice',
                    'items'  => [
                        [
                            'label'  => 'Услуги по компаниям',
                            'url'    => ['/statservice/company?type=' . Company::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'statservice') && (Yii::$app->controller->action->id == 'company')),
                        ],
                        [
                            'label'  => 'Услуги по партнерам',
                            'url'    => ['/statservice/list?type=' . Company::TYPE_WASH],
                            'active' => (Yii::$app->controller->id == 'statservice') && (Yii::$app->controller->action->id == 'list'),
                        ],
                        [
                            'label'  => 'Обслуживание<br />клиентов',
                            'url'    => ['/statservice/service?type=' . Company::TYPE_WASH . '&company=1'],
                            'active' => (Yii::$app->controller->id == 'statservice') && (Yii::$app->controller->action->id == 'service') && (Yii::$app->request->get('company') == 1),
                        ],
                        [
                            'label'  => 'Обслуживание<br />у партнеров',
                            'url'    => ['/statservice/service?type=' . Company::TYPE_WASH],
                            'active' => (Yii::$app->controller->id == 'statservice') && (Yii::$app->controller->action->id == 'service') && (Yii::$app->request->get('company') != 1),
                        ],
                    ],
                ],
                [
                    'label'  => 'Пользователи',
                    'url'    => ['/user/list', 'type' => Company::TYPE_OWNER],
                    'active' => Yii::$app->controller->id == 'user',
                ],
                [
                    'label'  => 'Контакты',
                    'url'    => ['/contact/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'contact',
                ],
                [
                    'label'  => 'Карты',
                    'url'    => ['/card/list'],
                    'active' => Yii::$app->controller->id == 'card'
                        && Yii::$app->controller->action->id != 'diapason'
                        && Yii::$app->controller->action->id != 'lost',
                ],
                [
                    'label'  => 'Потерянные карты',
                    'url'    => ['/card/lost'],
                    'active' => Yii::$app->controller->id == 'card' && Yii::$app->controller->action->id == 'lost',
                ],
                [
                    'label'  => 'Диапазон карт',
                    'url'    => ['/card/diapason'],
                    'active' => Yii::$app->controller->id == 'card' && Yii::$app->controller->action->id == 'diapason',
                ],
                [
                    'label'  => 'Информация ТС',
                    'url'    => '#',
                    'active' => in_array(Yii::$app->controller->id, ['mark', 'type', 'car', 'car-count']),
                    'items'  => [
                        [
                            'label'  => 'Марки и Типы',
                            'url'    => ['/mark/list'],
                            'active' => Yii::$app->controller->id == 'mark' || Yii::$app->controller->id == 'type',
                        ],
                        [
                            'label'  => 'История ТС',
                            'url'    => ['/car/list'],
                            'active' => Yii::$app->controller->id == 'car' && Yii::$app->controller->action->id == 'list',
                        ],
                        [
                            'label'  => 'История<br />перемещений ТС',
                            'url'    => ['/car/history'],
                            'active' => (Yii::$app->controller->id == 'car' && Yii::$app->controller->action->id == 'history'),
                        ],
                        [
                            'label'  => 'Загрузка ТС',
                            'url'    => ['/car/upload'],
                            'active' => false,
                        ],
                        [
                            'label'  => 'Количество ТС',
                            'url'    => ['/car-count/list'],
                            'active' => Yii::$app->controller->id == 'car-count',
                        ],
                    ]
                ],
                [
                    'label'  => 'Статистика',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'stat',
                    'items'  => [
                        [
                            'label'  => 'Статистика партнеров',
                            'url'    => ['/stat/list', 'type' => Company::TYPE_WASH, 'group' => 'partner'],
                            'active' =>
                                Yii::$app->controller->id == 'stat' &&
                                Yii::$app->request->get('group') == 'partner',
                        ],
                        [
                            'label'  => 'Статистика компаний',
                            'url'    => ['/stat/list', 'type' => Company::TYPE_WASH, 'group' => 'company'],
                            'active' =>
                                Yii::$app->controller->id == 'stat' &&
                                Yii::$app->request->get('group') == 'company',
                        ],
                        [
                            'label'  => 'Статистика общая',
                            'url'    => ['/stat/list-common',],
                            'active' =>
                                Yii::$app->controller->id == 'stat' &&
                                Yii::$app->controller->action->id == 'list-common',
                        ],
                    ]
                ],
                [
                    'label'  => 'Контроль денежных</br> стредств',
                    'url'    => '#',
                    'visible'    => Yii::$app->user->identity->role == User::ROLE_ADMIN ? true : false,
                    'active' => Yii::$app->controller->id == 'expense',
                    'items'  => [
                        [
                            'label'  => 'Добавление',
                            'url'    => ['/expense/addexpensecomp?type=1'],
                            'active' => Yii::$app->controller->action->id == 'addexpense' || Yii::$app->controller->action->id == 'addexpensecomp' || Yii::$app->controller->action->id == 'expensecomp' || Yii::$app->controller->action->id == 'updateexpense' || Yii::$app->controller->action->id == 'fullexpense',
                        ],
                        [
                            'label'  => 'Статистика</br> денежных средств',
                            'url'    => ['/expense/statexpense?type=1'],
                            'active' => Yii::$app->controller->action->id == 'statexpense' || Yii::$app->controller->action->id == 'stattotal',
                        ],
                    ],
                ],
                [
                    'label'  => 'Закрыть загрузки',
                    'url'    => ['/load/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'load',
                ],

                [
                    'label'  => 'Акты',
                    'url'    => ['/act/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'act',
                ],
                [
                    'label'  =>
                        'Ошибочные акты ' .
                        ($errorsCount ? '<span class="label label-danger">' . $errorsCount . '</span>' : ''),
                    'url'    => ['/error/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'error' && Yii::$app->controller->action->id != 'losses' && Yii::$app->controller->action->id != 'async' && Yii::$app->controller->action->id != 'update',
                ],
                [
                    'label'  =>
                        'Убыточные акты ' .
                        ($lossesCount ? '<span class="label label-danger">' . $lossesCount . '</span>' : ''),
                    'url'    => ['/error/losses', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'error' && Yii::$app->controller->action->id == 'losses',
                ],
                [
                    'label'  =>
                        'Асинхронные акты ' .
                        ($asyncCount ? '<span class="label label-danger">' . $asyncCount . '</span>' : ''),
                    'url'    => ['/error/async', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'error' && Yii::$app->controller->action->id == 'async',
                ],

            ];
        } // Partner links
        elseif (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            /** @var Company $company */
            $company = Yii::$app->user->identity->company;
            if (!empty($company->schedule)) {
                $items = [
                    [
                        'label'  => 'Добавить машину',
                        'url'    => [
                            '/act/create-entry',
                            'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type :
                                $company->type
                        ],
                        'active' =>
                            Yii::$app->controller->id == 'act' &&
                            (Yii::$app->controller->action->id == 'create' ||
                                Yii::$app->controller->action->id == 'disinfect' ||
                                Yii::$app->controller->action->id == 'create-entry'),
                    ],
                    [
                        'label'  => 'Контакты',
                        'url'    => [
                            '/contact/list',
                            'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type :
                                $company->type
                        ],
                        'active' => Yii::$app->controller->id == 'contact' && Yii::$app->controller->action->id !== 'newyear',
                    ],
                    [
                        'label'  => 'Доходы',
                        'url'    => ['/stat/view'],
                        'active' => Yii::$app->controller->id == 'stat',
                    ],
                    [
                        'label'  => 'Архив',
                        'url'    => [
                            '/act/list',
                            'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type :
                                $company->type
                        ],
                        'active' =>
                            Yii::$app->controller->id == 'act' &&
                            Yii::$app->controller->action->id != 'create' &&
                            Yii::$app->controller->action->id != 'disinfect' &&
                            Yii::$app->controller->action->id != 'create-entry',
                    ],
                    [
                        'label'  => Yii::$app->controller->action->id == 'newyear' ? 'Новогоднее</br> Поздравление' : '<span style="color: #c72e1a">Новогоднее</br> Поздравление</span>',
                        'url'    => ['/contact/newyear'],
                        'active' => Yii::$app->controller->action->id == 'newyear',
                    ],
                ];
            } else {
                $items = [
                    [
                        'label'  => 'Добавить машину',
                        'url'    => [
                            '/act/create',
                            'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type :
                                $company->type
                        ],
                        'active' =>
                            Yii::$app->controller->id == 'act' &&
                            (Yii::$app->controller->action->id == 'create' ||
                                Yii::$app->controller->action->id == 'disinfect'),
                    ],
                    [
                        'label'  => 'Контакты',
                        'url'    => [
                            '/contact/list',
                            'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type :
                                $company->type
                        ],
                        'active' => Yii::$app->controller->id == 'contact' && Yii::$app->controller->action->id !== 'newyear',
                    ],
                    [
                        'label'  => 'Доходы',
                        'url'    => ['/stat/view'],
                        'active' => Yii::$app->controller->id == 'stat',
                    ],
                    [
                        'label'  => 'Архив',
                        'url'    => [
                            '/act/list',
                            'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type :
                                $company->type
                        ],
                        'active' =>
                            Yii::$app->controller->id == 'act' &&
                            Yii::$app->controller->action->id != 'create' &&
                            Yii::$app->controller->action->id != 'disinfect',
                    ],
                    [
                        'label'  => Yii::$app->controller->action->id == 'newyear' ? 'Новогоднее</br> Поздравление' : '<span style="color: #c72e1a">Новогоднее</br> Поздравление</span>',
                        'url'    => ['/contact/newyear'],
                        'active' => Yii::$app->controller->action->id == 'newyear',
                    ],
                ];
            }
        } // Client links
        elseif (Yii::$app->user->identity->role == User::ROLE_CLIENT) {
            $items = [
                [
                    'label'  => 'Карты',
                    'url'    => ['/card/list'],
                    'active' => Yii::$app->controller->id == 'card',
                ],
                [
                    'label'  => 'История ТС',
                    'url'    => ['/car/list'],
                    'active' => Yii::$app->controller->id == 'car' && !(Yii::$app->controller->action->id == 'drivers'),
                ],
//                [
//                    'label'  => 'Список ТС',
//                    'url'    => ['/car-count/list-full'],
//                    'active' =>
//                        Yii::$app->controller->id == 'car-count' &&
//                        Yii::$app->controller->action->id == 'list-full',
//                ],
                [
                    'label'  => 'Контакты',
                    'url'    => ['/contact/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'contact' && Yii::$app->controller->action->id !== 'newyear',
                ],
                [
                    'label'  => 'Количество ТС',
                    'url'    => ['/car-count/list'],
                    'active' =>
                        Yii::$app->controller->id == 'car-count' &&
                        Yii::$app->controller->action->id != 'list-full',
                ],
                [
                    'label'  => 'Сотрудники',
                    'url'    => ['/member/memberslist'],
                    'active' =>
                        Yii::$app->controller->id == 'member' &&
                        Yii::$app->controller->action->id = 'memberslist',
                ],
                [
                    'label'  => 'Водители',
                    'url'    => ['/car/drivers'],
                    'active' => Yii::$app->controller->id == 'car' && Yii::$app->controller->action->id == 'drivers',
                ],
                [
                    'label'  => 'Расходы',
                    'url'    => ['/stat/view', 'type' => Service::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'stat',
                ],
                [
                    'label'  => 'Статистика данных',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'analytics',
                    'items'  => [
                        [
                            'label'  => 'Статистка по<br />количеству<br />обслуженных машин',
                            'url'    => ['/analytics/list', 'type' => Company::TYPE_WASH, 'group' => 'count'],
                            'active' =>
                                Yii::$app->controller->id == 'analytics' &&
                                Yii::$app->request->get('group') == 'count',
                        ],
                        [
                            'label'  => 'Статистика<br />обслуженных<br />машин по городам',
                            'url'    => ['/analytics/list', 'type' => Company::TYPE_WASH, 'group' => 'city'],
                            'active' =>
                                Yii::$app->controller->id == 'analytics' &&
                                Yii::$app->request->get('group') == 'city',
                        ],
                        [
                            'label'  => 'Среднее кол-во<br />операций на 1ТС',
                            'url'    => ['/analytics/list', 'type' => Company::TYPE_WASH, 'group' => 'average'],
                            'active' =>
                                Yii::$app->controller->id == 'analytics' &&
                                Yii::$app->request->get('group') == 'average',
                        ],
                        /*[
                            'label'  => 'Общая статистика',
                            'url'    => ['/analytics/list', 'group' => 'type'],
                            'active' =>
                                Yii::$app->controller->id == 'analytics' &&
                                Yii::$app->request->get('group') == 'type',
                        ],*/
                    ]
                ],
                [
                    'label'  => 'Услуги',
                    'url'    => ['/act/list', 'type' => Company::TYPE_WASH, 'company' => true],
                    'active' => Yii::$app->controller->id == 'act',
                ],
                [
                    'label'  => Yii::$app->controller->action->id == 'newyear' ? 'Новогоднее</br> Поздравление' : '<span style="color: #c72e1a">Новогоднее</br> Поздравление</span>',
                    'url'    => ['/contact/newyear'],
                    'active' => Yii::$app->controller->action->id == 'newyear',
                ],
            ];
        } else {
            $items = [
                [
                    'label' => 'Вход',
                    'url'   => ['/site/index'],
                ],
            ];
        }

        return $items;
    }

    public function run()
    {
        return $this->render('menu_left', ['items' => $this->getItems()]);
    }
}