<?php
namespace console\controllers;

use common\models\Act;
use common\models\Company;
use common\models\CompanyService;
use common\models\CompanyTime;
use common\models\User;
use yii\console\Controller;

class UtilsController extends Controller
{
    public function actionIndex()
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
            $this->stdout("$act->id \n");
            if ((!$act->clientScopes && $act->partnerScopes) || (!$act->partnerScopes && $act->clientScopes)) {
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

    public function actionFixWorkTime()
    {
        /** @var Company $company */
        foreach (Company::find()->where(['type' => [2,3,4,5,6]])->orderBy('id ASC')->all() as $company) {
            if (count($company->companyTime) == 1) {
                $start_at = 0;
                $end_at = 86400;
                $day = 1;
                foreach ($company->companyTime as $companyTime) {
                    if (!$companyTime->start_at) {
                        $companyTime->start_at = 0;
                    }
                    if (!$companyTime->end_at) {
                        $companyTime->end_at = 86400;
                    }
                    $companyTime->save();
                    $start_at = $companyTime->start_at;
                    $end_at = $companyTime->end_at;
                    $day = $companyTime->day;
                }
                for ($i = 1; $i < 8; $i++) {
                    if ($i == $day) {
                        continue;
                    }
                    $modelCompanyTime = new CompanyTime();
                    $modelCompanyTime->company_id = $company->id;
                    $modelCompanyTime->start_at = $start_at;
                    $modelCompanyTime->end_at = $end_at;
                    $modelCompanyTime->$day = $i;
                    $modelCompanyTime->save();
                }
            } else {
                $start_at = 0;
                $end_at = 86400;
                foreach ($company->companyTime as $companyTime) {
                    if (!$companyTime->start_at) {
                        $companyTime->start_at = $start_at;
                    } else {
                        $start_at = $companyTime->start_at;
                    }
                    if (!$companyTime->end_at) {
                        $companyTime->end_at = $end_at;
                    } else {
                        $end_at = $companyTime->end_at;
                    }
                    $companyTime->save();
                }
            }
            $this->stdout(".");
        }
        $this->stdout("\n Done!");
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

        foreach ($users as $user) {
            $this->createUser($user['username'], $user['role'], $user['email']);
        }
    }

    private function createUser($username, $role, $email)
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