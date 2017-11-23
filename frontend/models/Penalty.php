<?php
/**
 * Created by PhpStorm.
 * User: artem
 * Date: 20.11.17
 * Time: 16:55
 */

namespace frontend\models;


class Penalty
{

    /**
     * @var array
     */
    private $params;
    public function __construct($params = [])
    {
        $this->params = $params;
    }
    /**
     * @return array
     */
    public function getParams()
    {
        // array
        return $this->params;
    }
    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }
    private function sendGetRequest($url, $queryParameters = [], $headers = [])
    {
        if (!empty($queryParameters)) {
            $url .= (stripos($url, "?") === false ? "?" : "&").http_build_query($queryParameters);
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        if (($response = curl_exec($curl)) === false) {
            throw new \Exception(curl_error($curl));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeader = trim(substr($response, 0, $header_size));
        $responseBody = trim(substr($response, $header_size));
        curl_close($curl);
        return [$responseHeader, $responseBody];
    }
    private function sendPostRequest($url, $body, $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        if (($response = curl_exec($curl)) === false) {
            throw new \Exception(curl_error($curl));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeader = trim(substr($response, 0, $header_size));
        $responseBody = trim(substr($response, $header_size));
        curl_close($curl);
        return [$responseHeader, $responseBody];
    }
    private function sendPatchRequest($url, $body, $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        if (($response = curl_exec($curl)) === false) {
            throw new \Exception(curl_error($curl));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeader = trim(substr($response, 0, $header_size));
        $responseBody = trim(substr($response, $header_size));
        curl_close($curl);
        return [$responseHeader, $responseBody];
    }
    private function sendDeleteRequest($url, $body, $headers = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        if (($response = curl_exec($curl)) === false) {
            throw new \Exception(curl_error($curl));
        }
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeader = trim(substr($response, 0, $header_size));
        $responseBody = trim(substr($response, $header_size));
        curl_close($curl);
        return [$responseHeader, $responseBody];
    }
    /**
     * @return string
     */
    public function getApiBaseUrl()
    {
        // string
        if (isset($this->params['host'])) {
            return $this->params['host'];
        } else {
            return 'https://api.shtrafovnet.ru/v2/dealers';
        }
    }
    /**
     * @param string $api_base_url
     */
    public function setApiBaseUrl($api_base_url)
    {
        $this->api_base_url = $api_base_url;
    }
    public function getBasicAuthHeader()
    {
        $username = isset($this->getParams()['account']['login']) ? $this->getParams()['account']['login'] : 'aram@mtransservice.ru';
        $password = isset($this->getParams()['account']['password']) ? $this->getParams()['account']['password'] : '88881111';
        return 'Authorization: Basic '.base64_encode($username.":".$password);
    }
    public function getBearerAuthHeader()
    {
        $token = '';
        if (isset($this->getParams()['token'])) {
            $token = $this->getParams()['token'];
        } else {
            $token = 'fail_token';
        }
        return 'Authorization: Bearer '.$token;
    }
    /**
     * ===============================================================
     * ACCOUNT
     * ===============================================================
     */
    /**
     * Создание нового аккаунта
     * POST /account
     */
    public function createAccount($email, $password, $name, $contactName, $contactPhone, $companyInn, $extraData = [])
    {
        $url = $this->getApiBaseUrl()."/account";
        $headers = [
            'Content-Type: application/json',
        ];
        $data = [
            'email'                  => $email,
            'plainPassword'          => $password,
            'name'                   => $name,
            'companyContactFullname' => $contactName,
            'companyContactPhone'    => $contactPhone,
            'companyInn'             => $companyInn,
        ];
        $data = array_merge($data, $extraData);
        return $this->sendPostRequest($url, json_encode($data), $headers);
    }
    /**
     * Обновление информации аккаунта
     * PATCH /account
     */
    public function updateAccount($data = [])
    {
        $url = $this->getApiBaseUrl()."/account";
        $headers = [
            'Content-Type: application/json',
            $this->getBasicAuthHeader(),
        ];
        return $this->sendPatchRequest($url, json_encode($data), $headers);
    }
    /**
     * Сброс и отправка нового пароля от аккаунта
     * POST /account/reset-password
     */
    public function resetPasswordAccount()
    {
        $url = $this->getApiBaseUrl()."/account/reset-password";
        $headers = [
            'Content-Type: application/json',
        ];
        $data = [
            'email' => isset($this->getParams()['account']['login']) ? $this->getParams()['account']['login'] : 'aram@mtransservice.ru',
        ];
        return $this->sendPostRequest($url, json_encode($data), $headers);
    }
    /**
     * Получение информации об аккаунте
     * GET /account
     */
    public function getAccount()
    {
        $url = $this->getApiBaseUrl()."/account";
        $headers = [
            $this->getBasicAuthHeader(),
        ];
        return $this->sendGetRequest($url, [], $headers);
    }
    /**
     * ===============================================================
     * TOKENS
     * ===============================================================
     */
    /**
     * Создание токена доступа к ресурсам ШтрафовНЕТ
     * POST /tokens
     */
    public function createToken()
    {
        $url = $this->getApiBaseUrl()."/tokens";
        $headers = [
            $this->getBasicAuthHeader(),
        ];
        return $this->sendPostRequest($url, [], $headers);
    }
    /**
     * ===============================================================
     * CLIENTS
     * ===============================================================
     */
    /**
     * Получение списка клиентов
     * GET /clients
     */
    public function getClients()
    {
        $url = $this->getApiBaseUrl()."/clients";
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, [], $headers);
    }
    /**
     * Создание нового клиента
     * POST /clients
     */
    public function createClient($email, $name, $companyInn, $extraData = [])
    {
        $url = $this->getApiBaseUrl()."/clients";
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        $data = [
            'email'      => $email,
            'name'       => $name,
            'companyInn' => $companyInn,
        ];
        $data = array_merge($data, $extraData);
        return $this->sendPostRequest($url, json_encode($data), $headers);
    }
    /**
     * Получение информации о клиенте
     * GET /clients
     */
    public function getClient($email)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email;
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, [], $headers);
    }
    /**
     * Обновление клиента
     * GET /clients/{email}
     */
    public function updateClient($email, $fields = [])
    {
        $url = $this->getApiBaseUrl()."/clients/".$email;
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        return $this->sendPatchRequest($url, json_encode($fields), $headers);
    }
    /**
     * Удаление клиента
     * GET /clients/{email}
     */
    public function deleteClient($email)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email;
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        return $this->sendDeleteRequest($url, null, $headers);
    }
    /**
     * ===============================================================
     * TARIFFS
     * ===============================================================
     */
    /**
     * Получение списка тарифов информационного обслуживания
     * GET /tariffs
     */
    public function getTariffs()
    {
        $url = $this->getApiBaseUrl()."/tariffs";
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * ===============================================================
     * CLIENT CARS
     * ===============================================================
     */
    /**
     * Добавление ТС клиенту
     * POST /clients/{email}/cars
     */
    public function createClientCar($email, $data = [])
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/cars";
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        return $this->sendPostRequest($url, json_encode($data), $headers);
    }
    /**
     * Информация о ТС
     * POST /clients/{email}/cars/{car_id}
     */
    public function getClientCar($email, $car_id)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/cars/".$car_id;
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * Обновление ТС
     * PATCH /clients/{email}/cars/{car_id}
     */
    public function updateClientCar($email, $car_id, $data = [])
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/cars/".$car_id;
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        return $this->sendPatchRequest($url, json_encode($data), $headers);
    }
    /**
     * Удаление ТС
     * PATCH /clients/{email}/cars/{car_id}
     */
    public function deleteClientCar($email, $car_id)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/cars/".$car_id;
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendDeleteRequest($url, null, $headers);
    }
    /**
     * Получение списка ТС клиента
     * GET /clients/{email}/cars
     */
    public function getClientCars($email)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/cars";
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * ===============================================================
     * CLIENT FINES
     * ===============================================================
     */
    /**
     * Получение списка штрафов от всех ТС клиента
     * POST /clients/{email}/fines
     */
    public function getClientFines($email, $queryParams = [])
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/fines";
        if (!empty($queryParams)) {
            $url .= "?".http_build_query($queryParams);
        }
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * Получение информации о штрафе
     * POST /clients/{email}/fines/{fine_id}
     */
    public function getClientFine($email, $fine_id)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/fines/".$fine_id;
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * ===============================================================
     * CLIENT SERVICES
     * ===============================================================
     */
    /**
     * Получение списка услуг по информационному обслуживанию клиента
     * POST /clients/{email}/services
     */
    public function getClientServices($email)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/services";
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * Информация об услуге по информационному обслуживанию клиента
     * POST /clients/{email}/services/{service_id}
     */
    public function getClientService($email, $service_id)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/services/".$service_id;
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * Создание новой услуги по информационному обслуживанию
     * POST /clients/{email}/services
     */
    public function createService($email, $tariff_id, $quantity)
    {
        $url = $this->getApiBaseUrl()."/clients/".$email."/services";
        $headers = [
            'Content-Type: application/json',
            $this->getBearerAuthHeader(),
        ];
        $data = [
            'tariff' => $tariff_id,
            'quantity'  => $quantity,
        ];
        return $this->sendPostRequest($url, json_encode($data), $headers);
    }
    /**
     * ===============================================================
     * INVOICES
     * ===============================================================
     */
    /**
     * Получение списка счетов на оплату услуг
     * POST /invoices
     */
    public function getInvoices()
    {
        $url = $this->getApiBaseUrl()."/invoices";
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }
    /**
     * Получение счета
     * POST /invoices
     */
    public function getInvoice($id)
    {
        $url = $this->getApiBaseUrl()."/invoices/".$id;
        $headers = [
            $this->getBearerAuthHeader(),
        ];
        return $this->sendGetRequest($url, null, $headers);
    }

}