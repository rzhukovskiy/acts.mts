<?php
    namespace console\controllers;

    use common\models\User;
    use yii\console\Controller;

    class TestController extends Controller
    {
        public function actionIndex(  )
        {
            $this->stdout("\n");
            $this->stdout("Test controller \n");
            $this->stdout("\nActions: \n");
            $this->stdout('   test/generate-users' . " — generate test user accounts.\n");
            $this->stdout("\n");
        }

        public function actionGenerateUsers()
        {
            $this->stdout("\n");
            $this->stdout("Generate test user accounts \n");
            $this->generateUsers();
            $this->stdout("\n");

        }

        private function generateUsers()
        {
            $users = [
                [
                    'username' => 'admin',
                    'role' => 0,
                    'email' => 'admin@admin.ru',
                ],
                [
                    'username' => 'client',
                    'role' => 1,
                    'email' => 'client@client.ru',
                ],
                [
                    'username' => 'company',
                    'role' => 2,
                    'email' => 'company@company.ru',
                ],
            ];

            foreach ( $users as $user ) {
                $this->createUser($user['username'], $user['role'], $user['email']);
            }
        }

        private function createUser( $username, $role, $email )
        {
            $user = new User();

            if ($user->findOne(['username' => $username])) {
                $this->stdout("User already exist: $username \n");

                return false;
            }

            $user->username = $username;
            $user->role = $role;
            $user->auth_key = 'test';
            $user->password_hash = \Yii::$app->security->generatePasswordHash('password');
            $user->email = $email;
            $user->created_at = time();
            $user->updated_at = time();

            if ($user->save()) {
                $this->stdout("Generate: $username@password, role: $role\n");

                return true;
            }

            return false;
        }
    }