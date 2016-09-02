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
            $items = [
                [
                    'label' => 'Отделы',
                    'url' => ['/department/index'],
                    'active' => Yii::$app->controller->id == 'department',
                ],
                [
                    'label' => 'Сотрудники',
                    'url' => ['/user/index', 'department' => 1],
                    'active' => Yii::$app->controller->id == 'user',
                ],
            ];
        } // Partner links
        elseif (Yii::$app->user->identity->role == User::ROLE_WATCHER) {
            /** @var Company $company */
            $company = Yii::$app->user->identity->company;
            $items = [
                [
                    'label' => 'Доходы',
                    'url' => ['/stat/view'],
                    'active' => Yii::$app->controller->id == 'stat',
                ],
                [
                    'label' => 'Добавить машину',
                    'url' => ['/act/create', 'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type : $company->type],
                    'active' => Yii::$app->controller->id == 'act' && (Yii::$app->controller->action->id == 'create' || Yii::$app->controller->action->id == 'disinfect'),
                ],
                [
                    'label' => 'Архив',
                    'url' => ['/act/list', 'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type : $company->type],
                    'active' => Yii::$app->controller->id == 'act' && Yii::$app->controller->action->id != 'create' && Yii::$app->controller->action->id != 'disinfect',
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