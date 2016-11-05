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
        $searchModel->status = Company::STATUS_NEW;
        $countNew = $searchModel->search()->count;
        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->status = Company::STATUS_ACTIVE;
        $countActive = $searchModel->search()->count;
        $searchModel = new CompanySearch(['scenario' => Company::SCENARIO_OFFER]);
        $searchModel->status = Company::STATUS_REFUSE;
        $countRefuse = $searchModel->search()->count;

        $searchModel = new MessageSearch();
        $searchModel->user_to = $currentUser->id;
        $searchModel->is_read = null;
        $countMessage = $searchModel->search([])->count;

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
                    'label' => 'Заявки'  . ($countNew ? '<span class="label label-success">' . $countNew . '</span>' : ''),
                    'url' => ['/company/new', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW)
                    ),
                ],
                [
                    'label' => 'Архив'  . ($countNew ? '<span class="label label-success">' . $countActive . '</span>' : ''),
                    'url' => ['/company/active', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ACTIVE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ACTIVE)
                    ),
                ],
                [
                    'label' => 'Отказ'  . ($countNew ? '<span class="label label-success">' . $countRefuse . '</span>' : ''),
                    'url' => ['/company/refuse', 'type' => Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_REFUSE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE)
                    ),
                ],
                [
                    'label'  => 'Акты и оплата',
                    'url'    => ['/monthly-act/list?type=' . Company::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'monthly-act'),
                ],
                [
                    'label' => 'Сообщения'  . ($countMessage ? '<span class="label label-success">' . $countMessage . '</span>' : ''),
                    'url' => ['/message/list', 'department_id' => Department::getFirstId()],
                    'active' => (Yii::$app->controller->id == 'message'),
                ],
            ];
        } // Account manager links
        elseif ($currentUser->role == User::ROLE_ACCOUNT) {
            $items = [
                [
                    'label' => 'Мойки',
                    'url' => ['/wash/list'],
                    'active' => Yii::$app->controller->id == 'wash' || Yii::$app->controller->id == 'entry',
                ],
                [
                    'label'  => 'Акты и оплата',
                    'url'    => ['/monthly-act/list?type=' . Company::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'monthly-act'),
                ],
                [
                    'label' => 'Сообщения'  . ($countMessage ? '<span class="label label-success">' . $countMessage . '</span>' : ''),
                    'url' => ['/message/list', 'department_id' => Department::getFirstId()],
                    'active' => (Yii::$app->controller->id == 'message'),
                ],
            ];
        } elseif ($currentUser && ($currentUser->role == User::ROLE_WATCHER || $currentUser->role == User::ROLE_MANAGER)) {
            $company = Company::findOne(['id' => Yii::$app->request->get('id')]);
            $items = [
                [
                    'label' => 'Заявки'  . ($countNew ? '<span class="label label-success">' . $countNew . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_NEW]['en'], 'type' => $currentUser->getFirstCompanyType()],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_NEW]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_NEW)
                    ),
                ],
                [
                    'label' => 'Архив'  . ($countNew ? '<span class="label label-success">' . $countActive . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_ACTIVE]['en'], 'type' => $currentUser->getFirstCompanyType()],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_ACTIVE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ACTIVE)
                    ),
                ],
                [
                    'label' => 'Отказ'  . ($countNew ? '<span class="label label-success">' . $countRefuse . '</span>' : ''),
                    'url' => ['/company/' . Company::$listStatus[Company::STATUS_REFUSE]['en'], 'type' => $currentUser->getFirstCompanyType()],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == Company::$listStatus[Company::STATUS_REFUSE]['en']) ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_REFUSE)
                    ),
                ],
                [
                    'label'  => 'Акты и оплата',
                    'url'    => ['/monthly-act/list?type=' . Company::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'monthly-act'),
                ],
                [
                    'label' => 'Сообщения'  . ($countMessage ? '<span class="label label-success">' . $countMessage . '</span>' : ''),
                    'url' => ['/message/list', 'department_id' => Department::getFirstId()],
                    'active' => (Yii::$app->controller->id == 'message'),
                ],
            ];
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