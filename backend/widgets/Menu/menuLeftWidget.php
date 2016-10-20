<?php
/**
 * Created by PhpStorm.
 * User: ruslanzh
 * Date: 09/08/16
 * Time: 19:23
 */

namespace backend\widgets\Menu;

use common\models\Act;
use common\models\Company;
use common\models\Department;
use common\models\search\ActSearch;
use common\models\Service;
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

        $items = [];
        // Admin links
        if (Yii::$app->user->identity && Yii::$app->user->identity->role == User::ROLE_ADMIN) {
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
                    'label' => 'Заявки',
                    'url' => '#',
                ],
                [
                    'label' => 'Архив',
                    'url' => ['/company/archive?type=' . Company::TYPE_WASH],
                    'active' => (
                        (Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'archive') ||
                        ($company && Yii::$app->controller->id == 'company' && $company->status == Company::STATUS_ACTIVE)
                    ),
                ],
                [
                    'label' => 'Отказ',
                    'url' => ['/company/refuse?type=' . Company::TYPE_WASH],
                    'active' => (
                        Yii::$app->controller->id == 'company' && Yii::$app->controller->action->id == 'refuse'
                    ),
                ],
                [
                    'label' => 'Сообщения',
                    'url' => '#',
                ],
                [
                    'label'  => 'Акты и оплата',
                    'url'    => ['/monthly-act/list?type=' . Company::TYPE_WASH],
                    'active' => (Yii::$app->controller->id == 'monthly-act'),
                ],
            ];
        } // Account manager links
        elseif (Yii::$app->user->identity->role == User::ROLE_ACCOUNT) {
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