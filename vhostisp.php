<?php

class VhostApi {
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
     * Получить список виртуальных хостингов
     *
     * @param array $filters Фильтры для запроса
     * @return array|false Массив данных или false при ошибке
     */
    public function getVhostList(array $filters = []) {
        $url = $this->baseUrl . '/vhost';
        $response = $this->sendRequest('GET', $url, $filters);
        return $response['data'] ?? false;
    }

    /**
     * Удалить виртуальные хостинги по их ID
     *
     * @param string|array $elid Идентификаторы хостингов (можно передать строку "id1, id2" или массив [id1, id2])
     * @return bool Успешность операции
     */
    public function deleteVhost($elid) {
        if (is_array($elid)) {
            $elid = implode(', ', $elid);
        }
        $url = $this->baseUrl . '/vhost/delete?elid=' . urlencode($elid);
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
        $url = $this->baseUrl . '/vhost/su?user_id=' . urlencode($userId);
        $response = $this->sendRequest('GET', $url);
        return $response['data'] ?? false;
    }

    /**
     * Установить фильтр для списка VHOST
     *
     * @param array $filter Параметры фильтрации
     * @return bool Успешность операции
     */
    public function setFilter(array $filter) {
        $url = $this->baseUrl . '/vhost/filter';
        $response = $this->sendRequest('POST', $url, $filter);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Выключить хостинги по их ID
     *
     * @param string|array $elid Идентификаторы хостингов
     * @return bool Успешность операции
     */
    public function suspendVhost($elid) {
        if (is_array($elid)) {
            $elid = implode(', ', $elid);
        }
        $url = $this->baseUrl . '/vhost/suspend?elid=' . urlencode($elid);
        $response = $this->sendRequest('POST', $url);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Включить хостинги по их ID
     *
     * @param string|array $elid Идентификаторы хостингов
     * @return bool Успешность операции
     */
    public function resumeVhost($elid) {
        if (is_array($elid)) {
            $elid = implode(', ', $elid);
        }
        $url = $this->baseUrl . '/vhost/resume?elid=' . urlencode($elid);
        $response = $this->sendRequest('POST', $url);
        return isset($response['success']) && $response['success'];
    }

    /**
     * Создать новый VHOST или изменить существующий
     *
     * @param array $params Параметры создания/изменения
     * @return array|false Результат операции или false при ошибке
     */
    public function editVhost(array $params) {
        $url = $this->baseUrl . '/vhost/edit';
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
    public function openVhost(array $params) {
        $url = $this->baseUrl . '/vhost/open';
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

$vhostApi = new VhostApi($apiUrl, $apiKey);

// Получить список VHOST
$vhostList = $vhostApi->getVhostList();
print_r($vhostList);

// Удалить VHOST
$deleteResult = $vhostApi->deleteVhost([123, 456]);
var_dump($deleteResult);

// Изменить параметры VHOST
$editParams = [
    'elid' => 789,
    'autoprolong' => '1 month',
    'note' => 'Updated via API'
];
$editResult = $vhostApi->editVhost($editParams);
print_r($editResult);
