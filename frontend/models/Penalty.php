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
     * ???????????????? ???????????? ????????????????
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
     * ???????????????????? ???????????????????? ????????????????
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
     * ?????????? ?? ???????????????? ???????????? ???????????? ???? ????????????????
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
     * ?????????????????? ???????????????????? ???? ????????????????
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
     * ???????????????? ???????????? ?????????????? ?? ???????????????? ????????????????????
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
     * ?????????????????? ???????????? ????????????????
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
     * ???????????????? ???????????? ??????????????
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
     * ?????????????????? ???????????????????? ?? ??????????????
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
     * ???????????????????? ??????????????
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
     * ???????????????? ??????????????
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
     * ?????????????????? ???????????? ?????????????? ?????????????????????????????? ????????????????????????
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
     * ???????????????????? ???? ??????????????
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
     * ???????????????????? ?? ????
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
     * ???????????????????? ????
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
     * ???????????????? ????
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
     * ?????????????????? ???????????? ???? ??????????????
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
     * ?????????????????? ???????????? ?????????????? ???? ???????? ???? ??????????????
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
     * ?????????????????? ???????????????????? ?? ????????????
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
     * ?????????????????? ???????????? ?????????? ???? ?????????????????????????????? ???????????????????????? ??????????????
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
     * ???????????????????? ???? ???????????? ???? ?????????????????????????????? ???????????????????????? ??????????????
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
     * ???????????????? ?????????? ???????????? ???? ?????????????????????????????? ????????????????????????
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
     * ?????????????????? ???????????? ???????????? ???? ???????????? ??????????
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
     * ?????????????????? ??????????
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