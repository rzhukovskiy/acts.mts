<?php
    /**
     * Created by PhpStorm.
     * User: ruslanzh
     * Date: 09/08/16
     * Time: 19:23
     */

    namespace frontend\widgets\Menu;

    use common\models\Company;
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
            if ( !empty( $this->items ) )
                return $this->items;

            $items = [
                [
                    'label' => Company::$listType[Company::TYPE_OWNER]['ru'],
                    'url' => [ '/company/list?type=' . Company::TYPE_OWNER ],
                    'active' => (\Yii::$app->controller->id == 'company' && \Yii::$app->request->get('type') == Company::TYPE_OWNER),
                ],
                [
                    'label' => Company::$listType[Company::TYPE_WASH]['ru'],
                    'url' => [ '/company/list?type=' . Company::TYPE_WASH ],
                ],
                [
                    'label' => Company::$listType[Company::TYPE_SERVICE]['ru'],
                    'url' => [ '/company/list?type=' . Company::TYPE_SERVICE ],
                ],
                [
                    'label' => Company::$listType[Company::TYPE_TIRES]['ru'],
                    'url' => [ '/company/list?type=' . Company::TYPE_TIRES ],
                ],
                [
                    'label' => Company::$listType[Company::TYPE_DISINFECT]['ru'],
                    'url' => [ '/company/list?type=' . Company::TYPE_DISINFECT ],
                ],
                [
                    'label' => Company::$listType[Company::TYPE_UNIVERSAL]['ru'],
                    'url' => [ '/company/list?type=' . Company::TYPE_UNIVERSAL ],
                ],
                [
                    'label' => 'Пользователи',
                    'url' => [ '/user/list', 'type' => Company::TYPE_OWNER ],
                    'active' => \Yii::$app->controller->id == 'user',
                ],
                [
                    'label' => 'Карты',
                    'url' => [ '/card/list' ],
                    'active' => \Yii::$app->controller->id == 'card',
                ],
                [
                    'label' => 'Марки ТС',
                    'url' => [ '/mark/list' ],
                    'active' => \Yii::$app->controller->id == 'mark',
                ],
                [
                    'label' => 'Типы ТС',
                    'url' => [ '/type/list' ],
                    'active' => \Yii::$app->controller->id == 'type',
                ],
                [
                    'label' => 'История машин',
                    'url' => [ '/car/list' ],
                    'active' => \Yii::$app->controller->id == 'car',
                ],
                [
                    'label' => 'Кол-во ТС',
                    'url' => [ '/car-count/list' ],
                    'active' => \Yii::$app->controller->id == 'car-count',
                ],
                [
                    'label' => 'Статистика партнеров',
                    'url' => [ '/statistic/list', 'type' => 2 ],
                    'active' => \Yii::$app->controller->id == 'statistic',
                ],
                [
                    'label' => 'Статистика компаний',
                    'url' => [ '/company-statistic/list', 'type' => 2],
                    'active' => \Yii::$app->controller->id == 'company-statistic',
                ],
                [
                    'label' => 'Акты',
                    'url' => [ '/act/list', 'type' => 2 ],
                    'active' => \Yii::$app->controller->id == 'act',
                ],
                [
                    'label' => 'Ошибочные акты',
                    'url' => [ '/archive/error', 'type' => 2 ],
                    'active' => \Yii::$app->controller->id == 'archive',
                ],
            ];

            return $items;
        }

        public function run()
        {
            return $this->render( 'menu_left', [ 'items' => $this->getItems() ] );
        }


        private function getCountOfErrorActs()
        {
            return 5;
        }
    }