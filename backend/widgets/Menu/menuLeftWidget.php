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

        $searchModel->status = Company::STATUS_TENDER;
        $countTender = $searchModel->search()->count;

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

        $countOwner = TenderOwnerSearch::find()->where(['tender_user' => 0])->count();
        $countTaskU = TaskUser::find()->where(['task_user.for_user' => Yii::$app->user->identity->id])->andwhere(['!=', 'task_user.status', 2])->count();
        $countTaskL = TaskUserLink::find()->innerJoin('task_user', '`task_user_link`.`task_id` = `task_user`.`id`')->where(['task_user_link.for_user_copy' => Yii::$app->user->identity->id])->andwhere(['!=', 'task_user.status', 2])->count();

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
                    'label' => 'Заявки' . ($countNew ? '<span class="label label-success">' . $countNew . '</span>' : ''),
                    'url' => ['/company/new', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
                [
                    'label' => 'Архив' . ($countNew ? '<span class="label label-success">' . $countArchive . '</span>' : ''),
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
                    'label' => 'Архив 2' . ($countNew ? '<span class="label label-success">' . $countRefuse . '</span>' : ''),
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
                    'label' => 'Архив 3' . ($countNew ? '<span class="label label-success">' . $countArchive3 . '</span>' : ''),
                    'url' => ['/company/archive3', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE3]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
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
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'tasklist') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskadd') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskfull'),
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
                            'label'  => 'Статистика<br />архива',
                            'url' => ['/activity/archive', 'type' => Service::TYPE_WASH],
                            'active' => ((Yii::$app->controller->id == 'activity') &&
                                ((Yii::$app->controller->action->id == 'archive') || (Yii::$app->controller->action->id == 'showarchive'))),
                        ],
                    ],
                ],
                [
                    'label' => 'Почтовые<br />шаблоны',
                    'url' => ['/email/list'],
                    'active' => Yii::$app->controller->id == 'email'
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
                    'label' => 'Архив' . ($countNew ? '<span class="label label-success">' . $countArchive . '</span>' : ''),
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
                    'visible'    => ((Yii::$app->user->identity->id == 238) || (Yii::$app->user->identity->id == 256) || (Yii::$app->user->identity->id == 654) || (Yii::$app->user->identity->id == 756) || (Yii::$app->user->identity->id == 708)  || (Yii::$app->user->identity->id == 176)) ? true : false,
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
                    'label' => 'Заявки' . ($countNew ? '<span class="label label-success">' . $countNew . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_NEW]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_NEW)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
                ],
                [
                    'label' => 'Архив' . ($countNew ? '<span class="label label-success">' . $countArchive . '</span>' : ''),
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
                    'label' => 'Архив 2' . ($countNew ? '<span class="label label-success">' . $countRefuse . '</span>' : ''),
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
                    'label' => 'Архив 3' . ($countNew ? '<span class="label label-success">' . $countArchive3 . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_ARCHIVE3]['en'], 'type' => $currentUser->getFirstCompanyTypeMenu(Company::STATUS_ARCHIVE3)],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ARCHIVE3]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fullcontroltender')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'fulltendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'tendermembers')
                        || ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ARCHIVE3 && Yii::$app->controller->action->id != 'newtendermembers')
                    ),
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
                    'label' => 'Планирование',
                    'url' => ['/plan/list'],
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'list'),
                ],
                [
                    'label' => 'Задачи' . (($countTaskU || $countTaskL) ? '<span class="label label-success">' . ($countTaskU + $countTaskL) . '</span>' : ''),
                    'url' => ['/plan/tasklist?type=2'],
                    'active' => (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'tasklist') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskadd') || (Yii::$app->controller->id == 'plan' && Yii::$app->controller->action->id == 'taskfull'),
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