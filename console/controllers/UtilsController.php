<?php
    namespace console\controllers;

    use common\models\Act;
    use common\models\CompanyService;
    use common\models\User;
    use yii\console\Controller;

    class UtilsController extends Controller
    {
        public function actionIndex(  )
        {
            $this->stdout("\n");
            $this->stdout("Test controller \n");
            $this->stdout("\nActions: \n");
            $this->stdout('   utils/generate-users' . " — generate test user accounts.\n");
            $this->stdout('   utils/fix-duplicate-prices' . " — removes duplicate prices for services.\n");
            $this->stdout('   utils/fix-act-scopes' . " — fixes unfinished scopes in acts.\n");
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

        public function actionFixActScopes()
        {
            /** @var Act $act */
            foreach (Act::find()->all() as $act) {
                if (!$act->clientScopes || !$act->partnerScopes) {
                    $listScope = $act->clientScopes ? $act->clientScopes : $act->partnerScopes;

                    foreach ($listScope as $scope) {
                        $newScope = clone $scope;
                        $newScope->company_id = $act->clientScopes ? $act->partner_id : $act->client_id;
                        $newScope->id = null;
                        $newScope->setIsNewRecord(true);
                        $newScope->save();
                    }
                    $act->status = Act::STATUS_NEW;
                    $act->save();
                }
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