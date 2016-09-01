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
        if (!empty($this->items))
            return $this->items;

        $errorsCount = 0;
        foreach (Service::$listType as $type_id => $typeData) {
            $searchModel = new ActSearch(['scenario' => Act::SCENARIO_ERROR]);
            $searchModel->service_type = $type_id;
            $errorsCount += $searchModel->search(Yii::$app->request->queryParams)->getCount();
        }

        $items = [];
        // Admin links
        if (Yii::$app->user->identity && Yii::$app->user->identity->role == User::ROLE_ADMIN) {
            $items = [
                [
                    'label' => Company::$listType[Company::TYPE_OWNER]['ru'],
                    'url' => ['/company/list?type=' . Company::TYPE_OWNER],
                    'active' => (
                        Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_OWNER ||
                            Company::findOne(['id' => Yii::$app->request->get('id')])->type == Company::TYPE_OWNER)
                    ),
                ],
                [
                    'label' => Company::$listType[Company::TYPE_WASH]['ru'],
                    'url' => ['/company/list?type=' . Company::TYPE_WASH],
                    'active' => (
                        Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_WASH ||
                            Company::findOne(['id' => Yii::$app->request->get('id')])->type == Company::TYPE_WASH)
                    ),
                ],
                [
                    'label' => Company::$listType[Company::TYPE_SERVICE]['ru'],
                    'url' => ['/company/list?type=' . Company::TYPE_SERVICE],
                    'active' => (
                        Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_SERVICE ||
                            Company::findOne(['id' => Yii::$app->request->get('id')])->type == Company::TYPE_SERVICE)
                    ),
                ],
                [
                    'label' => Company::$listType[Company::TYPE_TIRES]['ru'],
                    'url' => ['/company/list?type=' . Company::TYPE_TIRES],
                    'active' => (
                        Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_TIRES ||
                            Company::findOne(['id' => Yii::$app->request->get('id')])->type == Company::TYPE_TIRES)
                    ),
                ],
                [
                    'label' => Company::$listType[Company::TYPE_DISINFECT]['ru'],
                    'url' => ['/company/list?type=' . Company::TYPE_DISINFECT],
                    'active' => (
                        Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_DISINFECT ||
                            Company::findOne(['id' => Yii::$app->request->get('id')])->type == Company::TYPE_DISINFECT)
                    ),
                ],
                [
                    'label' => Company::$listType[Company::TYPE_UNIVERSAL]['ru'],
                    'url' => ['/company/list?type=' . Company::TYPE_UNIVERSAL],
                    'active' => (
                        Yii::$app->controller->id == 'company' &&
                        (Yii::$app->request->get('type') == Company::TYPE_UNIVERSAL ||
                            Company::findOne(['id' => Yii::$app->request->get('id')])->type == Company::TYPE_UNIVERSAL)
                    ),
                ],

                [
                    'label' => 'Услуги',
                    'url' => ['service/index', 'ServiceSearch[type]' => Service::TYPE_WASH],
                ],

                [
                    'label' => 'Пользователи',
                    'url' => ['/user/list', 'type' => Company::TYPE_OWNER],
                    'active' => Yii::$app->controller->id == 'user',
                ],
                [
                    'label' => 'Карты',
                    'url' => ['/card/list'],
                    'active' => Yii::$app->controller->id == 'card',
                ],
                [
                    'label' => 'Марки и Типы',
                    'url' => ['/mark/list'],
                    'active' => Yii::$app->controller->id == 'mark' || Yii::$app->controller->id == 'type',
                ],
                [
                    'label' => 'История машин',
                    'url' => ['/car/list'],
                    'active' => Yii::$app->controller->id == 'car',
                ],
                [
                    'label' => 'Количество ТС',
                    'url' => ['/car-count/list'],
                    'active' => Yii::$app->controller->id == 'car-count',
                ],
                [
                    'label' => 'Статистика партнеров',
                    'url' => ['/statistic/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'statistic',
                ],
                [
                    'label' => 'Статистика компаний',
                    'url' => ['/company-statistic/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'company-statistic',
                ],
                [
                    'label' => 'Акты',
                    'url' => ['/act/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'act',
                ],
                [
                    'label' => 'Ошибочные акты ' . ($errorsCount ? '<span class="label label-danger">' . $errorsCount . '</span>' : ''),
                    'url' => ['/error/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'error',
                ],
            ];
        } // Partner links
        elseif (Yii::$app->user->identity->role == User::ROLE_PARTNER) {
            /** @var Company $company */
            $company = Yii::$app->user->identity->company;
            $items = [
                [
                    'label' => 'Доходы',
                    'url' => ['/statistic/list', 'type' => $company->type == Company::TYPE_UNIVERSAL ? $company->serviceTypes[0]->type : $company->type],
                    'active' => Yii::$app->controller->id == 'statistic',
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
        } // Client links
        elseif (Yii::$app->user->identity->role == User::ROLE_CLIENT) {
            $items = [
                [
                    'label' => 'Карты',
                    'url' => ['/card/list'],
                    'active' => Yii::$app->controller->id == 'card',
                ],
                [
                    'label' => 'История машин',
                    'url' => ['/car/list'],
                    'active' => Yii::$app->controller->id == 'car',
                ],
                [
                    'label' => 'Количество ТС',
                    'url' => ['/car-count/list'],
                    'active' => Yii::$app->controller->id == 'car-count',
                ],
                [
                    'label' => 'Расходы',
                    'url' => ['/company-statistic/list', 'type' => Company::TYPE_WASH],
                    'active' => Yii::$app->controller->id == 'company-statistic',
                ],
                [
                    'label' => 'Услуги',
                    'url' => ['/act/list', 'type' => Company::TYPE_WASH, 'company' => true],
                    'active' => Yii::$app->controller->id == 'act',
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