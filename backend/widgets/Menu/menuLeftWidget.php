<?php
/**
 * Created by PhpStorm.
 * User: ruslanzh
 * Date: 09/08/16
 * Time: 19:23
 */

namespace backend\widgets\Menu;

use common\models\Company;
use common\models\Department;
use common\models\search\CompanySearch;
use common\models\search\MessageSearch;
use common\models\search\TenderOwnerSearch;
use common\models\Service;
use common\models\TaskUser;
use common\models\TaskUserLink;
use common\models\User;
use yii;
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
        if (!empty($this->items))
            return $this->items;

        /** @var User $currentUser */
        $currentUser = Yii::$app->user->identity;

        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        if ($currentUser && $currentUser->role != User::ROLE_ADMIN) {
            $searchModel->user_id = $currentUser->id;
        }
        $searchModel->status = Company::STATUS_NEW;
        $countNew = $searchModel->search()->count;

        $searchModel->status = Company::STATUS_NEW2;
        $countNew2 = $searchModel->search()->count;

        $countTender = Company::find()->innerJoin('tender_hystory', 'tender_hystory.company_id = company.id')->where(['!=', 'company.status', Company::STATUS_DELETED])->count();

        $searchModel->status = [Company::STATUS_ARCHIVE, Company::STATUS_ACTIVE];
        $countArchive = $searchModel->search()->count;

        $searchModel->status = Company::STATUS_REFUSE;
        $countRefuse = $searchModel->search()->count;

        $searchModel->status = Company::STATUS_ARCHIVE3;
        $countArchive3 = $searchModel->search()->count;

        $searchModel = new MessageSearch();
        $searchModel->user_to = $currentUser->id;
        $searchModel->is_read = null;
        $countMessage = $searchModel->search([])->count;

        $countOwner = TenderOwnerSearch::find()->where(['AND', ['tender_user' => 0], ['status' => 0]])->count();
        $countTaskU = TaskUser::find()->where(['task_user.for_user' => Yii::$app->user->identity->id])->andwhere(['!=', 'task_user.status', 2])->andwhere(['task_user.is_archive' => 0])->count();
        $countTaskL = TaskUserLink::find()->innerJoin('task_user', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->andwhere(['!=', 'task_user.status', 2])->andwhere(['task_user.is_archive' => 0])->count();

        $items = [];
        // Admin links
        if ($currentUser && $currentUser->role == User::ROLE_ADMIN) {
            $company = Company::findOne(['id' => Yii::$app->request->get('id')]);
            $items = [
                [
                    'label' => 'Отделы',
                    'url' => ['/department/index'],
                    'active' => Yii::$app->controller->id == 'department',
                ],
                [
                    'label' => 'Сотрудники',
                    'url' => ['/user/list', 'department' => Department::getFirstId()],
                    'active' => Yii::$app->controller->id == 'user',
                ],

                [
                    'label'  => 'Тендеры',
                    'url'    => '#',
                    'active' => ((Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_TENDER]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_TENDER) || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenders') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtender')) || (Yii::$app->controller->action->id == 'tenderlist') || (Yii::$app->controller->action->id == 'filtertender') || (Yii::$app->controller->action->id == 'controltender') || (Yii::$app->controller->action->id == 'newcontroltender') || (Yii::$app->controller->action->id == 'fullcontroltender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtenderlinks') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'membersontender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'archivetender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderownerlist') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderowneradd') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderownerupdate') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderownerfull'),
                    'items'  => [
                        [
                            'label'  => 'Компании' . ($countTender ? '<span class="label label-success">' . $countTender . '</span>' : ''),
                            'url' => ['/company/' . Company::$listStatus[Company::STATUS_TENDER]['en'], 'type' => Company::TYPE_OWNER],
                            'active' => (
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_TENDER]['en']) ||
                                ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_TENDER) || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenders') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'membersontender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtendermembers')
                            ),
                        ],

                        [
                            'label'  => 'Закупки',
                            'url' => ['/company/tenderlist'],
                            'active' =>
                                 (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderlist'),
                        ],
                        [
                            'label'  => 'Список<br />договоров',
                            'url' => ['/company/filtertender'],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'filtertender'),
                        ],
                        [
                            'label'  => 'Контроль<br />денежных<br />средств',
                            'url' => ['/company/controltender'],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'controltender') || (Yii::$app->controller->action->id == 'newcontroltender') || (Yii::$app->controller->action->id == 'fullcontroltender'),
                        ],
                        [
                            'label'  => 'Все участники',
                            'url' => ['/company/tendermembers'],
                            'active' =>
                                 (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtenderlinks'),
                        ],
                        [
                            'label' => 'Архив тендеров',
                            'url' => ['/company/archivetender?win=1'],
                            'active' => (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'archivetender'),
                        ],
                        [
                            'label' => 'Распределение<br />тендеров' . ($countOwner ? '<span class="label label-success">' . $countOwner . '</span>' : ''),
                            'url' => ['/company/tenderownerlist?win=1'],
                            'active' => (Yii::$app->controller->id == 'company' && (Yii::$app->controller->action->id == 'tenderownerlist' || Yii::$app->controller->action->id == 'tenderowneradd' || Yii::$app->controller->action->id == 'tenderownerupdate' || Yii::$app->controller->action->id == 'tenderownerfull')),
                        ],
                    ],
                ],
                [
                    'label'  => 'Статистика<br />тендеров',
                    'url'    => '#',
                    'active' => (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statplace') ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statprice') ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatplace') ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatprice'),
                    'items'  => [
                        [
                            'label'  => 'Статистика<br />эл.площадок',
                            'url' => ['/company/statplace', 'type' => 1],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statplace') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatplace'),
                        ],
                        [
                            'label'  => 'Статистика<br />денежных<br />средств',
                            'url' => ['/company/statprice', 'type' => 1],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statprice') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatprice'),
                        ],
                    ],
                ],
                [
                    'label'  => 'Заявки',
                    'url'    => '#',
                    'active'    => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en']) ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW2]['en'])
                    ),
                    'items'  => [
                [
                    'label' => 'Заявки' . ($countNew ? '<span class="label label-success">' . $countNew . '</span>' : ''),
                    'url' => ['/company/new', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en'])
                    ),
                ],
                [
                    'label' => 'Заявки 2' . ($countNew2 ? '<span class="label label-success">' . $countNew2 . '</span>' : ''),
                    'url' => ['/company/new2', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW2]['en'])
                    ),
                ],
                ],
                ],
                [
                    'label'  => 'Архивы',
                    'url'    => '#',
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE]['en']) ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_REFUSE]['en']) ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE3]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                    'items'  => [
                [
                    'label' => 'Архив' . ($countArchive ? '<span class="label label-success">' . $countArchive . '</span>' : ''),
                    'url' => ['/company/archive', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
                [
                    'label' => 'Архив 2' . ($countRefuse ? '<span class="label label-success">' . $countRefuse . '</span>' : ''),
                    'url' => ['/company/refuse', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_REFUSE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
                [
                    'label' => 'Архив 3' . ($countArchive3 ? '<span class="label label-success">' . $countArchive3 . '</span>' : ''),
                    'url' => ['/company/archive3', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE3]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
             ],
        ],
                [
                    'label' => 'Акты и оплата',
                    'url' => ['/monthly-act/list?type=' . Company::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'monthly-act' && Yii::$app->controller->action->id != 'archive'),
                ],
                [
                    'label' => 'Архив актов',
                    'url' => ['/monthly-act/archive?type=' . Company::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'monthly-act' && Yii::$app->controller->action->id == 'archive'),
                ],
                [
                    'label' => 'Планирование',
                    'url' => ['/plan/list'],
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'list'),
                ],
                [
                    'label' => 'Задачи' . (($countTaskU || $countTaskL) ? '<span class="label label-success">' . ($countTaskU + $countTaskL) . '</span>' : ''),
                    'url' => ['/plan/tasklist?type=1'],
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'tasklist') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskadd') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskfull') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskmylist') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskmyadd') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskmyfull'),
                ],
                [
                    'label' => 'Сообщения' . ($countMessage ? '<span class="label label-success">' . $countMessage . '</span>' : ''),
                    'url' => ['/message/list', 'department_id' => Department::getFirstId()],
                    'active' => (Yii::$app->controller->id == 'message'),
                ],
                [
                    'label' => 'Запись ТС',
                    'url' => ['/order/list', 'type' => Service::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'order' &&
                            Yii::$app->controller->action->id == 'list') ||
                        Yii::$app->controller->id == 'entry' ||
                        (Yii::$app->controller->id == 'order' &&
                            Yii::$app->controller->action->id == 'view'),
                ],
                [
                    'label' => 'Архив записей',
                    'url' => ['/order/archive', 'type' => Service::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'order' &&
                        Yii::$app->controller->action->id == 'archive',
                ],
                [
                    'label'  => 'Активность<br />сотрудников',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'activity',
                    'items'  => [
                        [
                            'label'  => 'Статистика<br />заявок',
                            'url' => ['/activity/new', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'new') || (Yii::$app->controller->action->id == 'shownew'))),
                        ],
                        [
                            'label'  => 'Статистика<br />заявок 2',
                            'url' => ['/activity/new2', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'new2') || (Yii::$app->controller->action->id == 'shownew2'))),
                        ],
                        [
                            'label'  => 'Статистика<br />архива',
                            'url' => ['/activity/archive', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'archive') || (Yii::$app->controller->action->id == 'showarchive'))),
                        ],
                        [
                            'label'  => 'Статистика<br />тендеров',
                            'url' => ['/activity/tender', 'type' => 1],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'tender') || (Yii::$app->controller->action->id == 'showtender'))),
                        ],
                    ],
                ],
                [
                    'label' => 'Почтовые<br />шаблоны',
                    'url' => ['/email/list'],
                    'active' => Yii::$app->controller->id == 'email'
                ],
                [
                    'label'  => 'Поставки',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'delivery',
                    'items'  => [
                        [
                            'label' => 'Заказ химии',
                            'url' => ['/delivery/listchemistry'],
                            'active' => (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'listchemistry') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'newchemistry') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'fullchemistry')
                        ],
                        [
                            'label' => 'Отправка<br />чеков',
                            'url' => ['/delivery/listchecks'],
                            'active' => (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'listchecks') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'newchecks') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'fullchecks')
                        ],
                    ],
                ],
            ];
        } // Account manager links
        elseif ($currentUser->role == User::ROLE_ACCOUNT) {
            $company = Company::findOne(['id' => Yii::$app->request->get('id')]);

            $items = [
                [
                    'label' => 'Запись ТС',
                    'url' => ['/order/list', 'type' => Service::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'order' &&
                            Yii::$app->controller->action->id == 'list') ||
                        Yii::$app->controller->id == 'entry' ||
                        (Yii::$app->controller->id == 'order' &&
                            Yii::$app->controller->action->id == 'view'),
                ],
                [
                    'label' => 'Архив записей',
                    'url' => ['/order/archive', 'type' => Service::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'order' &&
                        Yii::$app->controller->action->id == 'archive',
                ],
                [
                    'label' => 'Планирование',
                    'url' => ['/plan/list'],
                    'active' => (Yii::$app->controller->id == 'plan'),
                ],
                [
                    'label' => 'Сообщения' . ($countMessage ? '<span class="label label-success">' . $countMessage . '</span>' : ''),
                    'url' => ['/message/list', 'department_id' => Department::getFirstId()],
                    'active' => (Yii::$app->controller->id == 'message'),
                ],
                [
                    'label' => 'Архив' . ($countArchive ? '<span class="label label-success">' . $countArchive . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_ARCHIVE]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_ARCHIVE)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
            ];
        } elseif ($currentUser && ($currentUser->role == User::ROLE_WATCHER || $currentUser->role == User::ROLE_MANAGER)) {
            $company = Company::findOne(['id' => Yii::$app->request->get('id')]);

            // Исправил ошибку почему для шиномонтажа открывается мойки во вкладке шиномонтажа при заходе из меню
            $listTypeManager = Yii::$app->user->identity->getAllServiceType(Company::STATUS_ACTIVE);
            $managerTypeOpen = 2;

            foreach ($listTypeManager as $type_id => $typeData) {
                $managerTypeOpen = $type_id; break;
            }

            $items = [
                [
                    'label'  => 'Тендеры',
                    'url'    => '#',
                    'visible'    => (User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->where(['AND', ['department_user.department_id' => 6], ['department_user.user_id' => $currentUser->id]])->exists() || ($currentUser->id == 238) || ($currentUser->id == 708) || ($currentUser->id == 176)) ? true : false,
                    'active' => ((Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_TENDER]['en']) ||
                            ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_TENDER) || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenders') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtender')) || (Yii::$app->controller->action->id == 'tenderlist') || (Yii::$app->controller->action->id == 'filtertender') || (Yii::$app->controller->action->id == 'controltender') || (Yii::$app->controller->action->id == 'newcontroltender') || (Yii::$app->controller->action->id == 'fullcontroltender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtenderlinks') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'membersontender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'archivetender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderownerlist') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderowneradd') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderownerupdate') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderownerfull'),
                    'items'  => [
                        [
                            'label'  => 'Компании' . ($countTender ? '<span class="label label-success">' . $countTender . '</span>' : ''),
                            'url' => ['/company/' . Company::$listStatus[Company::STATUS_TENDER]['en'], 'type' => Company::TYPE_OWNER],
                            'active' => (
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_TENDER]['en']) ||
                                ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_TENDER) || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenders') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'membersontender') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtendermembers')
                            ),
                        ],

                        [
                            'label'  => 'Закупки',
                            'url' => ['/company/tenderlist'],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tenderlist'),
                        ],
                        [
                            'label'  => 'Список<br />договоров',
                            'url' => ['/company/filtertender'],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'filtertender'),
                        ],
                        [
                            'label'  => 'Контроль<br />денежных<br />средств',
                            'url' => ['/company/controltender'],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'controltender') || (Yii::$app->controller->action->id == 'newcontroltender') || (Yii::$app->controller->action->id == 'fullcontroltender'),
                        ],
                        [
                            'label'  => 'Все участники',
                            'url' => ['/company/tendermembers'],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'tendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'fulltendermembers') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'newtenderlinks'),
                        ],
                        [
                            'label' => 'Архив тендеров',
                            'url' => ['/company/archivetender?win=1'],
                            'active' => (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'archivetender'),
                        ],
                        [
                            'label' => 'Распределение<br />тендеров' . ($countOwner ? '<span class="label label-success">' . $countOwner . '</span>' : ''),
                            'url' => ['/company/tenderownerlist?win=1'],
                            'active' => (Yii::$app->controller->id == 'company' && (Yii::$app->controller->action->id == 'tenderownerlist' || Yii::$app->controller->action->id == 'tenderowneradd' || Yii::$app->controller->action->id == 'tenderownerupdate' || Yii::$app->controller->action->id == 'tenderownerfull')),
                        ],
                    ],
                ],
                [
                    'label'  => 'Статистика<br />тендеров',
                    'url'    => '#',
                    'visible'    => (User::find()->innerJoin('department_user', '`department_user`.`user_id` = `user`.`id`')->where(['AND', ['department_user.department_id' => 6], ['department_user.user_id' => $currentUser->id]])->exists() || ($currentUser->id == 238) || ($currentUser->id == 708) || ($currentUser->id == 176)) ? true : false,
                    'active' => (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statplace') ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statprice') ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatplace') ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatprice'),
                    'items'  => [
                        [
                            'label'  => 'Статистика<br />эл.площадок',
                            'url' => ['/company/statplace', 'type' => 1],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statplace') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatplace'),
                        ],
                        [
                            'label'  => 'Статистика<br />денежных<br />средств',
                            'url' => ['/company/statprice', 'type' => 1],
                            'active' =>
                                (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'statprice') || (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'showstatprice'),
                        ],
                    ],
                ],
                [
                    'label'  => 'Заявки',
                    'url'    => '#',
                    'active'    => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en']) ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW2]['en'])
                    ),
                    'items'  => [
                [
                    'label' => 'Заявки' . ($countNew ? '<span class="label label-success">' . $countNew . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_NEW]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_NEW)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en'])
                    ),
                ],
                [
                    'label' => 'Заявки 2' . ($countNew2 ? '<span class="label label-success">' . $countNew2 . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_NEW2]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_NEW2)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW2]['en'])
                    ),
                ],
                ],
                ],
                [
                    'label'  => 'Архивы',
                    'url'    => '#',
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE]['en']) ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_REFUSE]['en']) ||
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE3]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                    'items'  => [
                [
                    'label' => 'Архив' . ($countArchive ? '<span class="label label-success">' . $countArchive . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_ARCHIVE]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_ARCHIVE)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
                [
                    'label' => 'Архив 2' . ($countRefuse ? '<span class="label label-success">' . $countRefuse . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_REFUSE]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_REFUSE)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_REFUSE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
                [
                    'label' => 'Архив 3' . ($countArchive3 ? '<span class="label label-success">' . $countArchive3 . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_ARCHIVE3]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_ARCHIVE3)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE3]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                        ],
                    ],
                ],
                [
                    'label' => 'Акты и оплата',
                    'url' => ['/monthly-act/list?type=' . $managerTypeOpen],
                    'active' => (Yii::$app->controller->id == 'monthly-act' && Yii::$app->controller->action->id != 'archive'),
                ],
                [
                    'label' => 'Архив актов',
                    'url' => ['/monthly-act/archive?type=' . $managerTypeOpen],
                    'active' => (Yii::$app->controller->id == 'monthly-act' && Yii::$app->controller->action->id == 'archive'),
                ],
                [
                    'label'  => 'Активность<br />сотрудников',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'activity',
                    'items'  => [
                        [
                            'label'  => 'Статистика<br />заявок',
                            'url' => ['/activity/new', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'new') || (Yii::$app->controller->action->id == 'shownew'))),
                        ],
                        [
                            'label'  => 'Статистика<br />заявок 2',
                            'url' => ['/activity/new2', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'new2') || (Yii::$app->controller->action->id == 'shownew2'))),
                        ],
                        [
                            'label'  => 'Статистика<br />архива',
                            'url' => ['/activity/archive', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'archive') || (Yii::$app->controller->action->id == 'showarchive'))),
                        ],
                        [
                            'label'  => 'Статистика<br />тендеров',
                            'url' => ['/activity/tender', 'type' => 1],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'tender') || (Yii::$app->controller->action->id == 'showtender'))),
                        ],
                    ],
                ],
                [
                    'label' => 'Привязка<br />компаний',
                    'url' => ['/user/linking', 'type' => Company::TYPE_OWNER],
                    'visible'    => ((Yii::$app->user->identity->id == 176) || (Yii::$app->user->identity->id == 238)) ? true : false,
                    'active' => Yii::$app->controller->id == 'user',
                ],
                [
                    'label' => 'Планирование',
                    'url' => ['/plan/list'],
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'list'),
                ],
                [
                    'label' => 'Задачи' . (($countTaskU || $countTaskL) ? '<span class="label label-success">' . ($countTaskU + $countTaskL) . '</span>' : ''),
                    'url' => ['/plan/tasklist?type=2'],
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'tasklist') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskadd') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskfull') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskmylist') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskmyadd') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskmyfull'),
                    ],
                [
                    'label' => 'Сообщения' . ($countMessage ? '<span class="label label-success">' . $countMessage . '</span>' : ''),
                    'url' => ['/message/list', 'department_id' => Department::getFirstId()],
                    'active' => (Yii::$app->controller->id == 'message'),
                ],
                [
                    'label' => 'Почтовые<br />шаблоны',
                    'url' => ['/email/list'],
                    'active' => Yii::$app->controller->id == 'email'
                ],
                [
                    'label'  => 'Поставки',
                    'url'    => '#',
                    'active' => Yii::$app->controller->id == 'delivery',
                    'items'  => [
                        [
                            'label' => 'Заказ химии',
                            'url' => ['/delivery/listchemistry'],
                            'active' => (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'listchemistry') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'newchemistry') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'fullchemistry')
                        ],
                        [
                            'label' => 'Отправка<br />чеков',
                            'url' => ['/delivery/listchecks'],
                            'active' => (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'listchecks') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'newchecks') ||
                                (Yii::$app->controller->id == 'delivery' && Yii::$app->controller->action->id == 'fullchecks')
                        ],
                    ],
                ],
            ];
            if ($currentUser->is_account) {
                $items[] = [
                    'label' => 'Запись ТС',
                    'url' => ['/order/list', 'type' => Service::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'order' &&
                            Yii::$app->controller->action->id == 'list') ||
                        Yii::$app->controller->id == 'entry' ||
                        (Yii::$app->controller->id == 'order' &&
                            Yii::$app->controller->action->id == 'view'),
                ];
                $items[] = [
                    'label' => 'Архив записей',
                    'url' => ['/order/archive', 'type' => Service::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'order' &&
                        Yii::$app->controller->action->id == 'archive',
                ];
            }
        } else {
            $items = [
                [
                    'label' => 'Вход',
                    'url' => ['/site/index'],
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