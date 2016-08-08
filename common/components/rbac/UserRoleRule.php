<?php
namespace common\components\rbac;
use Yii;
use yii\rbac\Rule;
use yii\helpers\ArrayHelper;
use common\models\User;


class UserRoleRule extends Rule
{
    public $name = 'userRole';
    public function execute($user, $item, $params)
    {
        //Получаем массив пользователя из базы
        $user = ArrayHelper::getValue($params, 'user', User::findOne($user));
        if ($user) {
            $role = $user->role;
            if ($item->name === User::ROLE_ADMIN) {
                return $role == User::ROLE_ADMIN;
            } elseif ($item->name === User::ROLE_WATCHER) {
                return $role == User::ROLE_ADMIN || $role == User::ROLE_WATCHER;
            } elseif ($item->name === User::ROLE_PARTNER) {
                return $role == User::ROLE_ADMIN || $role == User::ROLE_PARTNER;
            } elseif ($item->name === User::ROLE_CLIENT) {
                return $role == User::ROLE_ADMIN || $role == User::ROLE_CLIENT;
            }
        }
        return false;
    }
}