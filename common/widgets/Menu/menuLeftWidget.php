<?php
    /**
     * Created by PhpStorm.
     * User: ruslanzh
     * Date: 09/08/16
     * Time: 19:23
     */

    namespace common\widgets\Menu;

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
        public function getItems()
        {
            if ( !empty( $this->items ) )
                return $this->items;

            $items = [
                [
                    'label' => 'Услуги',
                ],
                [
                    'label' => '—',
                ],
                [
                    'label' => 'Пользователи',
                    'url' => [ '/user' ],
                ],
                [
                    'label' => 'Карты',
                    'url' => [ '/card' ],
                ],
                [
                    'label' => 'Типы и марки ТС',
                    'url' => [ '/mark/list' ],
                ],
                [
                    'label' => 'Типы ТС',
                    'url' => [ '/car/type' ],
                ],
                [
                    'label' => 'История машин',
                    'url' => [ '/car/history' ],
                ],
                [
                    'label' => 'Кол-во ТС',
                    'url' => [ '/car/list/count' ],
                ],
                [
                    'label' => 'Статистика партнеров',
                    'url' => [ '/statistic/partner' ],
                ],
                [
                    'label' => 'Статистика компаний',
                    'url' => [ '/statistic/company' ],
                ],
                [
                    'label' => 'Акты',
                    'url' => [ '/act' ],
                ],
                [
                    'label' => 'Ошибочные акты',
                    'url' => [ '/act/list/error' ],
                ],
                [
                    'label' => 'Выход',
                    'url' => [ '/site/logout' ],
                    'visible' => !(\Yii::$app->user->isGuest),
                ],
                [
                    'label' => 'Вход',
                    'url' => [ '/site/login' ],
                    'visible' => \Yii::$app->user->isGuest,
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