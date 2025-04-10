<?php

class VdsApi {
    private $baseUrl;
    private $apiKey;
    private $headers;

    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Получить список виртуальных серверов
     *
     * @param array $filters Фильтры для запроса
     * @return array|false Массив данных или false при ошибке
     */
    public function getVdsList(array $filters = []) {
        $url = $this->baseUrl . '/vds';
        $response = $this->sendRequest('GET', $url, $filters);
        return $response['data'] ?? false;
    }

    /**
     * Удалить виртуальные серверы по их ID
     *
     * @param string|array $elid Идентификаторы серверов (можно передать строку "id1, id2" или массив [id1, id2])
     * @return bool Успешность операции
     */
    public function deleteVds($elid) {
        if (is_array($elid)) {
            $elid = implode(', ', $elid);
        }
        $url = $this->baseUrl . '/vds/delete?elid=' . urlencode($elid);
        $response = $this->sendRequest('POST', $url);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Войти в панель управления с правами пользователя
     *
     * @param int|string $userId Идентификатор пользователя
     * @return array|false Данные о пользователе или false при ошибке
     */
    public function suLogin($userId) {
        $url = $this->baseUrl . '/vds/su?user_id=' . urlencode($userId);
        $response = $this->sendRequest('GET', $url);
        return $response['data'] ?? false;
    }

    /**
     * Установить фильтр для списка VDS
     *
     * @param array $filter Параметры фильтрации
     * @return bool Успешность операции
     */
    public function setFilter(array $filter) {
        $url = $this->baseUrl . '/vds/filter';
        $response = $this->sendRequest('POST', $url, $filter);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Выключить серверы по их ID
     *
     * @param string|array $elid Идентификаторы серверов
     * @return bool Успешность операции
     */
    public function suspendVds($elid) {
        if (is_array($elid)) {
            $elid = implode(', ', $elid);
        }
        $url = $this->baseUrl . '/vds/suspend?elid=' . urlencode($elid);
        $response = $this->sendRequest('POST', $url);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Включить серверы по их ID
     *
     * @param string|array $elid Идентификаторы серверов
     * @return bool Успешность операции
     */
    public function resumeVds($elid) {
        if (is_array($elid)) {
            $elid = implode(', ', $elid);
        }
        $url = $this->baseUrl . '/vds/resume?elid=' . urlencode($elid);
        $response = $this->sendRequest('POST', $url);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Создать новый VDS или изменить существующий
     *
     * @param array $params Параметры создания/изменения
     * @return array|false Результат операции или false при ошибке
     */
    public function editVds(array $params) {
        $url = $this->baseUrl . '/vds/edit';
        if (!isset($params['sok'])) {
            $params['sok'] = 'ok';
        }
        $response = $this->sendRequest('POST', $url, $params);
        return $response['data'] ?? false;
    }

    /**
     * Открыть услугу
     *
     * @param array $params Параметры открытия услуги
     * @return array|false Результат операции или false при ошибке
     */
    public function openVds(array $params) {
        $url = $this->baseUrl . '/vds/open';
        if (!isset($params['sok'])) {
            $params['sok'] = 'ok';
        }
        $response = $this->sendRequest('POST', $url, $params);
        return $response['data'] ?? false;
    }

    /**
     * Отправить HTTP-запрос к API
     *
     * @param string $method Метод HTTP (GET, POST, ...)
     * @param string $url URL запроса
     * @param array $data Данные для отправки
     * @return array Ответ сервера
     */
    private function sendRequest($method, $url, $data = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(function ($key) {
            return $key . ': ' . $this->headers[$key];
        }, array_keys($this->headers)));

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true) ?: ['data' => null];
        }

        return ['error' => 'HTTP Error: ' . $httpCode];
    }
}

// Пример использования класса:
$apiUrl = 'https://example.com/api'; // URL API
$apiKey = 'your_api_key_here'; // Ваш API ключ

$vdsApi = new VdsApi($apiUrl, $apiKey);

// Получить список VDS
$vdsList = $vdsApi->getVdsList();
print_r($vdsList);

// Удалить VDS
$deleteResult = $vdsApi->deleteVds([123, 456]);
var_dump($deleteResult);

// Изменить параметры VDS
$editParams = [
    'elid' => 789,
    'autoprolong' => '1 month',
    'note' => 'Updated via API'
];
$editResult = $vdsApi->editVds($editParams);
print_r($editResult);
