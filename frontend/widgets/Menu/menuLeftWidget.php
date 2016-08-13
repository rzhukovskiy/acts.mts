<?php
/**
 * Created by PhpStorm.
 * User: ruslanzh
 * Date: 09/08/16
 * Time: 19:23
 */

namespace frontend\widgets\Menu;

use common\models\Company;
use common\models\User;
use Yii;
use yii\bootstrap\Widget;

class menuLeftWidget extends Widget
{
    public $msgcount;
    public $creditcount;
    public $ticketcount;
    public $paymentcount;
    public $domaincount;
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

        $items = [
            // Admin links
            [
                'label' => Company::$listType[Company::TYPE_OWNER]['ru'],
                'url' => ['/company/list?type=' . Company::TYPE_OWNER],
                'active' => (
                    Yii::$app->controller->id == 'company' &&
                    Yii::$app->request->get('type') == Company::TYPE_OWNER
                ),
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => Company::$listType[Company::TYPE_WASH]['ru'],
                'url' => ['/company/list?type=' . Company::TYPE_WASH],
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => Company::$listType[Company::TYPE_SERVICE]['ru'],
                'url' => ['/company/list?type=' . Company::TYPE_SERVICE],
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => Company::$listType[Company::TYPE_TIRES]['ru'],
                'url' => ['/company/list?type=' . Company::TYPE_TIRES],
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => Company::$listType[Company::TYPE_DISINFECT]['ru'],
                'url' => ['/company/list?type=' . Company::TYPE_DISINFECT],
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => Company::$listType[Company::TYPE_UNIVERSAL]['ru'],
                'url' => ['/company/list?type=' . Company::TYPE_UNIVERSAL],
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],

            [
                'label' => 'Услуги',
                'url' => ['/service/index'],
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],

            [
                'label' => 'Пользователи',
                'url' => ['/user/list', 'type' => Company::TYPE_OWNER],
                'active' => \Yii::$app->controller->id == 'user',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Карты',
                'url' => ['/card/list'],
                'active' => \Yii::$app->controller->id == 'card',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Марки ТС',
                'url' => ['/mark/list'],
                'active' => \Yii::$app->controller->id == 'mark',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Типы ТС',
                'url' => ['/type/list'],
                'active' => \Yii::$app->controller->id == 'type',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'История машин',
                'url' => ['/car/list'],
                'active' => \Yii::$app->controller->id == 'car',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Кол-во ТС',
                'url' => ['/car-count/list'],
                'active' => \Yii::$app->controller->id == 'car-count',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Статистика партнеров',
                'url' => ['/statistic/list', 'type' => 2],
                'active' => \Yii::$app->controller->id == 'statistic',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Статистика компаний',
                'url' => ['/company-statistic/list', 'type' => 2],
                'active' => \Yii::$app->controller->id == 'company-statistic',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Акты',
                'url' => ['/act/list', 'type' => 2],
                'active' => \Yii::$app->controller->id == 'act',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],
            [
                'label' => 'Ошибочные акты',
                'url' => ['/archive/error', 'type' => 2],
                'active' => \Yii::$app->controller->id == 'archive',
                'visible' => Yii::$app->user->identity->role == User::ROLE_ADMIN,
            ],

            // Partner links


            // Client links
            [
                'label' => 'Карты',
                'url' => ['/card/company-cards'],
                'active' => \Yii::$app->controller->id == 'card',
                'visible' => Yii::$app->user->identity->role == User::ROLE_CLIENT,
            ],
            [
                'label' => 'История машин',
                'url' => ['/car/my-cars'],
                'active' => \Yii::$app->controller->id == 'car',
                'visible' => Yii::$app->user->identity->role == User::ROLE_CLIENT,
            ],
            [
                'label' => 'Кол-во ТС',
                'url' => ['/car-count/list'],
                'active' => \Yii::$app->controller->id == 'car-count',
                'visible' => Yii::$app->user->identity->role == User::ROLE_CLIENT,
            ],
            [
                'label' => 'Расходы',
//                'url' => ['/archive/error', 'type' => 2],
                'active' => \Yii::$app->controller->id == 'archive',
                'visible' => Yii::$app->user->identity->role == User::ROLE_CLIENT,
            ],
            [
                'label' => 'Услуги',
//                'url' => ['/archive/error', 'type' => 2],
                'active' => \Yii::$app->controller->id == 'archive',
                'visible' => Yii::$app->user->identity->role == User::ROLE_CLIENT,
            ],
        ];

        return $items;
    }

    public
    function run()
    {
        return $this->render('menu_left', ['items' => $this->getItems()]);
    }


    private
    function getCountOfErrorActs()
    {
        return 5;
    }
}