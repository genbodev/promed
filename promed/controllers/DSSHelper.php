<?php
/**
 * Вспомогательный класс
 *
 * DSS - Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      DSS
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @since        29.05.2018
 * @version      03.07.2019
 *
 */
class DSSHelper {

    private $env; // тестовая или рабочая среда - не используется

    // приложение - для врача, для редактирования опросника
    //     или для работы с клиническими регистрами
    private $config;

    private $apiURL; // адрес всервера АПИ СППР

    public $apiVersion; // версия АПИ СППР


    /**
     * Конструктор
     *
     * @param config: str - "doctor", "editor"
     * @param dss_api_url: str - адрес сервера АПИ СППР
     * @throws
     */
    function __construct($config, $dss_api_url) {

        if ((!is_string($config)) || (!is_string($dss_api_url))) {
            throw new Exception('Argument type mismatch');
        }

        switch ($config) {

            case 'doctor': {
                //$this->apiURL = "http://192.168.37.3:2071/doctor/API.php";
                $this->apiURL = $dss_api_url.'/doctor/API.php';
                $this->apiVersion = '4-0-1';

                $this->env = '/test'; // не используется
                break;
            }

            case 'editor': {
                //$this->apiURL = "http://192.168.37.3:2071/editor/API.php";
                $this->apiURL = $dss_api_url.'/editor/API.php';
                $this->apiVersion = '3-1-1';

                $this->env = '/test'; // не используется
                break;
            }

            case 'viewer': {
                //$this->apiURL = "http://192.168.37.3:2071/viewer/API.php";
                $this->apiURL = $dss_api_url.'/viewer/API.php';
                $this->apiVersion = '4-0-0';

                $this->env = '/test'; // не используется
                break;
            }

            default: {
                throw new Exception('config not found');
            }
        }

        $this->config = $config;
    }


    /**
     * Убедиться, что медработник зарегистрирован на сервере АПИ проекта
     * и получить его данные
     *
     * @param user: StdClass - врач
     * @return user
     * @throws Exception
     */
    public function putUser($user)/*: StdClass*/ {

        if (!is_object($user)) {
            throw new Exception('parameter User has wrong type');
        }

        $URI = "/v{$this->apiVersion}/doctors/{$user->pmuser_id}";

        $params = '{
            "URI": "'.$URI.'",
            "doctorLogin": "'.$user->pmuser_surname.'"
        }';
        $doctorData = $this->putRequest($params, 'PUT');

        if (
            (!isset($doctorData['doctorId']))
            || (empty($doctorData['doctorLogin']))
        ) {
            $response = json_encode($doctorData);
            throw new Exception(
                "Failed to put user to API server of the project.
                Request parameters: $params.
                API server response: $response"
            );
        }
        if ((string)$doctorData['doctorId'] !== $user->pmuser_id) {
            $doctorId = $doctorData['doctorId'];
            throw new Exception(
                "Failed to put user to API server of the project.
                Request doctorId: {$user->pmuser_id}.
                API server response doctorId: {$doctorId}"
            );
        }
        if ($doctorData['doctorLogin'] !== $user->pmuser_surname) {
            $doctorLN = $doctorData['doctorLogin'];
            throw new Exception(
                "Failed to put user to API server of the project.
                Request parameters login: {$user->pmuser_surname}.
                API server response login: {$doctorLN}"
            );
        }

        if ($this->config === 'editor') {
            // для редактора вернётся дополнительное поле - accessLevel - право создания модулей
            if (!isset($doctorData['doctorHasRight2CreateModules'])) {
                throw new Exception('Wrong answer from API server');
            }
            $user->doctorHasRight2CreateModules = $doctorData['doctorHasRight2CreateModules'];
        }

        return $user;
    }


    /**
     * Выполнить GET-запрос к АПИ серверу
     *
     * @param string URI - например, /v4/modules
     * @param string params - параметры в формате json
     * @throws Exception - если вернулся ответ не в формате json,
     *                           либо ответ содержит поле error
     * @return array - ответ сервера АПИ проекта
     */
    public function getRequest($URI, $params)/*: array*/ {

        if (!is_string($URI)) {
            throw new Exception('Parameter URI has wrong type. String expected.');
        }
        if (!is_string($params)) {
            throw new Exception('Parameter params has wrong type. String expected.');
        }
        $request = $this->apiURL.'?URI='.$this->env.$URI.'&params='.urlencode($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            if ($errorMsg = curl_error($ch)) {
                throw new Exception(
                    "GET-request to API server failed with message $errorMsg."
                );
            } else {
                throw new Exception("GET-request to API server failed.");
            }
        }

        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($code != 200) {
            throw new Exception(
                "GET-request to API server returned http error code $code."
            );
        }

        curl_close($ch);

        // Если в ответе сервера АПИ СППР указан BOM
        //     Сервер АПИ __НЕ ДОЛЖЕН__ передавать BOM в ответе.
        //     Это зона ответственности сервера АПИ СППР
        //     Но если такое случилось, записать в лог и продолжить
        $__BOM = pack('CCC', 239, 187, 191);
        // Careful about the three ='s -- they're all needed.
        while (0 === strpos($response, $__BOM)) {
            throw new Exception('BOM');
            $response = substr($response, 3);
        }

        $rawResult = json_decode($response);
        if ($rawResult === null) {
            // либо null (чего не может быть), либо ошибка преобразования
            $error = json_last_error_msg();
            throw new Exception(
                    "Got malformed response from API server GET request.
                    Request parameters: $params. API server response: $response.
                    Error: $error");
        }
        $result = (array)$rawResult;

        // в таком формате сервер АПИ передаёт ошибку
        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }

        return $result;
    }


    /**
     * Выполнить GET-запрос к АПИ серверу
     *
     * @param string URI - например, /v4/modules
     * @param string params - параметры в формате json
     * @throws Exception - если вернулся ответ не в формате json,
     *                           либо ответ содержит поле error
     * @return array - ответ сервера АПИ проекта
     */
    public function fileRequest($URI, $params)/*: array*/ {

        if (!is_string($URI)) {
            throw new Exception('Parameter URI has wrong type. String expected.');
        }
        if (!is_string($params)) {
            throw new Exception('Parameter params has wrong type. String expected.');
        }

        $request = $this->apiURL.'?URI='.$this->env.$URI.'&params='.urlencode($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        /*curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);*/
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            if ($errorMsg = curl_error($ch)) {
                throw new Exception(
                    "GET-request to API server failed with message $errorMsg."
                );
            } else {
                throw new Exception(
                    "GET-request to API server failed."
                );
            }
        }

        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($code != 200) {
            throw new Exception(
                "GET-request to API server returned http error code $code."
            );
        }

        curl_close($ch);

        return $response;
    }


    /**
     * Выполнить PUT-запрос (также PATCH- или DELETE-запрос) к АПИ-серверу
     *
     * @param string method - PUT, PATCH или DELETE
     * @param string data - в формате json, обязательно должна содержать URI
     * @throws Exception - если вернулся ответ не в формате json,
     *                           либо ответ содержит поле error
     * @return array - ответ сервера АПИ проекта
     */
    public function putRequest($data, $method)/*: array*/ {

        if (!is_string($data)) {
            throw new Exception('Parameter data has wrong type. String expected.');
        }
        if (!is_string($method)) {
            throw new Exception('Parameter method has wrong type. String expected.');
        }

        // добавить к uri env - данные о среде исполнения
        $rawData = json_decode($data);
        if (!$rawData) {
            throw new Exception('Failed to decode params json');
        }
        $rawData->URI = $this->env.$rawData->URI;
        //error_log("{$rawData->isConditional}");
        $data = json_encode($rawData);

        $dataLength = strlen($data);

        $ch = curl_init();
        if ($ch === false) {
            throw new Exception('Failed to initialize curl');
        }
        $r = curl_setopt($ch, CURLOPT_URL, $this->apiURL);
        if ($r === false) {
            throw new Exception(
                "Failed to set curl options. cURLOPT_URL, $this->apiURL"
            );
        }
        $r = curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($r === false) {
            throw new Exception(
                "Failed to set curl options. cURLOPT_CUSTOMREQUEST, $method"
            );
        }
        $r = curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            "Content-Length: {$dataLength}"
        ]);
        if ($r === false) {
            throw new Exception(
                "Failed to set curl options. cURLOPT_HTTPHEADER, $dataLength"
            );
        }
        $r = curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if ($r === false) {
            throw new Exception(
                "Failed to set curl options. cURLOPT_POSTFIELDS, $data"
            );
        }
        $r = curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($r === false) {
            throw new Exception("Failed to set curl options. cURLOPT_RETURNTRANSFER");
        }

        $response = curl_exec($ch);
        if ($response === false) {
            if ($errorMsg = curl_error($ch)) {
                throw new Exception(
                    "$method request to API server failed with message $errorMsg."
                );
            } else {
                throw new Exception(
                    "$method request to API server failed."
                );
            }
        }

        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($code != 200) {
            throw new Exception(
                "$method request to API server returned http error code $code."
            );
        }

        curl_close($ch);

        $rawResult = json_decode($response);
        if ($rawResult === null) {
            // либо null (чего не может быть), либо ошибка преобразования
            throw new Exception(
                "Got malformed response from API server $method request.
                Request parameters: $data. API server response: $response"
            );
        }
        $result = (array)$rawResult;

        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }

        return $result;
    }


    /**
     * Выполнить POST-запрос к АПИ-серверу
     *
     * @param string data - в формате json, обязательно должна содержать URI
     * @throws Exception - если вернулся ответ не в формате json,
     *                           либо ответ содержит поле error
     * @return array - ответ сервера АПИ проекта
     */
    public function postRequest($data)/*: array*/ {

        if (!is_string($data)) {
            throw new Exception('Parameter data has wrong type. String expected.');
        }

        // добавить к uri env - данные о среде исполнения
        $rawData = json_decode($data);
        $rawData->URI = $this->env.$rawData->URI;
        $data = json_encode($rawData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiURL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if ($response === false) {
            if ($errorMsg = curl_error($ch)) {
                throw new Exception(
                    "Post-request to API server failed with message $errorMsg."
                );
            } else {
                throw new Exception(
                    "Post-request to API server failed."
                );
            }
        }

        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        if ($code != 200) {
            throw new Exception(
                "Post-request to API server returned http error code $code."
            );
        }

        curl_close($ch);

        $rawResult = json_decode($response);
        if ($rawResult === null) {
            // либо null (чего не может быть), либо ошибка преобразования
            throw new Exception(
                "Got malformed response from API server $method request.
                Request parameters: $data. API server response: $response"
            );
        }
        $result = (array)$rawResult;

        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }

        return $result;
    }


    /**
     * Получить данные о пользователе, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass user - объект пользователя
     * @throws Exception
     */
    public function getUserFromData($data)/*: StdClass*/ {

        if (!is_array($data)) {
            throw new Exception('Parameter data has wrong type. String expected.');
        }
        if ((!isset($data['session'])) || (!is_array($data['session']))) {
            throw new Exception(
                'Failed to process input data. Data does not contain session'
            );
        }

        if (!isset($data['session']['login'])) {
            throw new Exception(
                'Не удалось получить логин пользователя.
                Приложение не может работать без логина пользователя'
            );
        }

        if ($data['session']['login'] === '') {
            throw new Exception(
                'Не удалось получить логин пользователя (пустой логин).
                Приложение не может работать без логина пользователя'
            );
        }

        $pmuser_login = $data['session']['login'];

        if (!isset($data['pmUser_id'])) {
            throw new Exception(
                'Не удалось получить идентификатор пользователя "'.$pmuser_login.'".
                Приложение не может работать без идентификатора пользователя'
            );
        }

        $pmuser_id = $this->bigInt($data['pmUser_id'], 'pmUser_id');

        return (object) [
            'pmuser_id' => $pmuser_id,
            'pmuser_surname' => $pmuser_login
        ];
    }


    /**
     * Проверить, что тип идентификатора - целое число. Аналог intval
     *
     * Идентификаторы имеют тип BIGINT в смысле MSSQL, то есть до 2**63.
     * Они корректно обрабатываются как целые числа в php x64.
     * В php x32 диапазон целых чисел до 2**31 и intval выдаёт
     * некорректный результат в случае выхода за границы диапазона.
     * Поэтому в общем случае с такими числами нужно работать в виде строк.
     * @param string id - идентификатор как строка
     * @param string name - название идентификатора для сообщения об ошибке
     * @return string id
     * @throws - если id - не целое
     */
    public function bigInt($id, $name)/* : string*/ {

        if (!is_string($id)) {
            throw new Exception(
                "Wrong function parameter type.
                Function bigInt expected parameter 1 to be of type string"
            );
        }
        if (!is_string($name)) {
            throw new Exception(
                "Wrong function parameter type.
                Function bigInt expected parameter 2 to be of type string"
            );
        }

        $match = preg_match('/[^0-9]/', $id);
        if (($match) || ($match === false)) {
            throw new Exception("Failed to check bigint $name. (contains non-digit symbols)");
        }

        // TODO проверить, не превышает ли число 2**63-1
        // пока проверка только на количество цифр
        if (strlen($id) > 19) {
            throw new Exception("Failed to check bigint $name. (too big)");
        }

        return $id;
    }


    /**
     * Получить параметры пагинации - offset и limit
     *
     */
    function getPagination()/*: StdClass*/  {
        
        if (
            (!isset($_REQUEST['limit']))
            || (filter_var($_REQUEST['limit'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Pagination limit is not set');
        }
        $limit = filter_var($_REQUEST['limit'], FILTER_VALIDATE_INT);

        $offset = 0;
        if (
            (!isset($_REQUEST['offset']))
            || (filter_var($_REQUEST['offset'], FILTER_VALIDATE_INT) === false)
        ) {
            if (
                (!isset($_REQUEST['start']))
                || (filter_var($_REQUEST['start'], FILTER_VALIDATE_INT) === false)
            ) {
                throw new Exception('Pagination offset is not set');
            } else {
                $offset = filter_var($_REQUEST['start'], FILTER_VALIDATE_INT);
            }
        } else {
            $offset = filter_var($_REQUEST['offset'], FILTER_VALIDATE_INT);
        }

        return (object) [ 'offset' => $offset, 'limit' => $limit ];
    }
}
