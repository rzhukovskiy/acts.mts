<?php
    namespace console\controllers;

    use common\models\CompanyService;
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

        public function actionFixDuplicatePrices()
        {
            foreach (CompanyService::find()->orderBy('id ASC')->all() as $companyService) {
                $companyService->save();
            }
        }

        private function generateUsers()
        {
            $users = [
                [
                    'username' => 'Gerbert88',
                    'role' => User::ROLE_ADMIN,
                    'email' => 'admin@admin.ru',
                ],
                [
                    'username' => 'client',
                    'role' => User::ROLE_CLIENT,
                    'email' => 'client@client.ru',
                ],
                [
                    'username' => 'company',
                    'role' => User::ROLE_PARTNER,
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
            $user->password_hash = \Yii::$app->security->generatePasswordHash('811601');
            $user->email = $email;
            $user->created_at = time();
            $user->updated_at = time();

            if ($user->save()) {
                $this->stdout("Generate: $username@811601, role: $role\n");

                return true;
            }

            return false;
        }
    }