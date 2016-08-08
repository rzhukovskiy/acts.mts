<?php
namespace console\controllers;
use Yii;
use yii\console\Controller;
use common\components\rbac\UserRoleRule;
use common\models\User;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        //Включаем наш обработчик
        $rule = new UserRoleRule();
        $auth->add($rule);

        //Добавляем роли
        $watcher = $auth->createRole(User::ROLE_WATCHER);
        $watcher->description = 'Наблюдатель';
        $watcher->ruleName = $rule->name;
        $auth->add($watcher);

        $client = $auth->createRole(User::ROLE_CLIENT);
        $client->description = 'Клиент';
        $client->ruleName = $rule->name;
        $auth->add($client);

        $partner = $auth->createRole(User::ROLE_PARTNER);
        $client->description = 'Партнер';
        $client->ruleName = $rule->name;
        $auth->add($partner);

        //Добавляем потомков
        $admin = $auth->createRole(User::ROLE_ADMIN);
        $admin->description = 'Администратор';
        $admin->ruleName = $rule->name;
        $auth->add($admin);
        $auth->addChild($admin, $watcher);
        $auth->addChild($admin, $client);
        $auth->addChild($admin, $partner);
    }
}