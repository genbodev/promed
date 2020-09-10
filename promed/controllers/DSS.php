<?php
/**
 * Контроллер приложения врача
 *
 * Контроллер - сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 *
 * Получает (проверенные) идентификатры (и ФИО) медработника и пациента
 *   и перенаправляет запрос на сервер АПИ проекта
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @version      03.07.2019
 *
 */
class DSS extends swController {

    public $inputRules = [
        // При развёртывании на бете возникла проблема
        //     с получением значения из конфигов.
        // Временный метод для диагностики проблемы
        'debugProblem' => [[
            'field' => 'key',
            'label' => 'Ограничение доступа',
            'rules' => 'required',
            'type' => 'int'
        ]],

        'putPatient' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ]],
        'getAllModules' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ]],
        'getQuestions' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'sessionId',
            'label' => 'Идентификатор сессии пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'sessionStartDT',
            'label' => 'Время открытия сессии пациента',
            'rules' => 'required',
            'type' => 'string'
        ]],

        'getRecentData' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ]],
        'getRegister' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'start',
            'label' => 'Пагинация. Отступ',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'limit',
            'label' => 'Пагинация. Количество',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'registerId',
            'label' => 'Идентификатор регистра',
            'rules' => 'required',
            'type' => 'int'
        ]],
        'getRegisterStructure' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'registerId',
            'label' => 'Идентификатор регистра',
            'rules' => 'required',
            'type' => 'int'
        ]],
        'getPatientConclusions' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'sessionId',
            'label' => 'Идентификатор сессии пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'sessionStartDT',
            'label' => 'Время открытия сессии пациента',
            'rules' => 'required',
            'type' => 'string'
        ]],
        'getAnswers' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'sessionId',
            'label' => 'Идентификатор сессии пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'sessionStartDT',
            'label' => 'Время открытия сессии пациента',
            'rules' => 'required',
            'type' => 'string'
        ]],
        'getResultBalls' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'resultId',
            'label' => 'Идентификатор варианта заключения',
            'rules' => 'required',
            'type' => 'int'
        ]],
        'getSessionAsFile' => [[
            'field' => 'Person_id',
            'label' => 'Идентификатор пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleId',
            'label' => 'Идентификатор модуля',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'moduleName',
            'label' => 'Название модуля',
            'rules' => 'required',
            'type' => 'string'
        ], [
            'field' => 'sessionId',
            'label' => 'Идентификатор сессии пациента',
            'rules' => 'required',
            'type' => 'int'
        ], [
            'field' => 'sessionStartDT',
            'label' => 'Время открытия сессии пациента',
            'rules' => 'required',
            'type' => 'string'
        ]]
    ];


    /**
     * Конструктор
     *
     */
    function __construct() {
        parent::__construct();

        if (!$this->config->item('DSS_API_URL')) {
            // попробовать явно загрузить конфиг
            $this->config->load('promed');
            if (!$this->config->item('DSS_API_URL')) {
                // всё равно не найден
                throw new Exception('Не найден адрес сервера АПИ СППР');
            }
        }

        require_once 'DSSHelper.php';
        $this->helper = new DSSHelper('doctor', $this->config->item('DSS_API_URL'));
    }


    /**
     * Получить информацию о доступных конфигах
     *
     * При развёртывании на бете возникла проблема
     *     с получением значения из конфигов.
     * Временный метод для диагностики проблемы
     */
    public function debugProblem() {
        try {
            $data = $this->ProcessInputData('debugProblem', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            if ($data['key'] !== '366') {
                throw new Exception('Not authorized for this action');
            }

            $response = [];
            $response['available_config_paths'] = implode(', ', $this->config->_config_paths);
            $response['loaded_config_files'] = implode(', ', $this->config->is_loaded);
            $response['available_values'] = var_export($this->config->config, true);
            $constants = get_defined_constants(true);
            $response['constants'] = (isset($constants['user']) ? $constants['user'] : []);

            $this->ProcessModelList([0 => $response])->ReturnData();
        } catch(Exception $e) {
            $this->ProcessModelList([['error' => $e->getMessage()]])->ReturnData();
        }
    }


    /**
     * Убедиться, что пациент есть в БД. Получить данные пациента
     *
     */
    public function putPatient() {
        try {
            $data = $this->ProcessInputData('putPatient', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);

            // выполнить запросы для добавления при необходимости
            // врача и пациента в БД приложения
            // сделать один раз за время работы с приложением
            // при первом запросе от приложения (этот запрос (getRecentData))
            $this->helper->putUser($user);
            $this->putPerson($user, $person);

            $params = '{
                "URI": "'."/v{$this->helper->apiVersion}/patients/{$person->person_id}".'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "patientLogin": "'.$person->person_surname.'"
            }';

            $recentData = $this->helper->putRequest($params, 'PUT');
            $response = [];
            $response['patient'] = (object) [
                'patientId' => $person->person_id,
                'patientLogin' => $person->person_surname,
                'patientFullName' => $person->person_fio,
                'patientAge' => $person->person_age
            ];

            $this->ProcessModelList($response)->ReturnData();
        } catch(Exception $e) {
            $this->ProcessModelList([['error' => $e->getMessage()]])->ReturnData();
        }
    }


    /**
     * Получить данные последних сессий пациента
     *
     */
    public function getRecentData() {
        try {
            $data = $this->ProcessInputData('getRecentData', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);

            // выполнить запросы для добавления при необходимости
            // врача и пациента в БД приложения
            // сделать один раз за время работы с приложением
            // при первом запросе от приложения (этот запрос (getRecentData))
            $this->helper->putUser($user);
            $this->putPerson($user, $person);

            // собственно запрос получения сессии пациента, привязанной к указанному посещению
            $URI = "/v{$this->helper->apiVersion}/patients/{$person->person_id}/recentdata";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "patientLogin": "'.$person->person_surname.'"
            }';

            $recentData = $this->helper->getRequest($URI, $params);
            $response = [];
            $response['recentData'] = $recentData;

            $this->ProcessModelList($response)->ReturnData();
        } catch(Exception $e) {
            $this->ProcessModelList([['error' => $e->getMessage()]])->ReturnData();
        }
    }


    /**
     * Получить вопросы
     */
    public function getQuestions()
    {
        try {
            $data = $this->ProcessInputData('getQuestions', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);
            $patientSession = $this->getPatientSessionFromData($data);

            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions";
            $params = '{
                "doctorLogin": "'.$user->pmuser_surname.'",
                "doctorId": '.$user->pmuser_id.',
                "patientId": '.$person->person_id.',
                "patientLogin": "'.$person->person_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "sessionId": '.$patientSession->sessionId.',
                "sessionStartDT": "'.$patientSession->sessionStartDT.'"
            }';

            $questions = $this->helper->getRequest($URI, $params);
            if (count($questions) === 0) {
                $questions = ['result' => 'empty'];
            }

        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }

        $this->ProcessModelList($questions)->ReturnData();
    }


    /**
     * Получить предварительные заключения (и рекомендации)
     */
    public function getPatientConclusions()
    {
        try {
            $data = $this->ProcessInputData('getPatientConclusions', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);
            $patientSession = $this->getPatientSessionFromData($data);

            $URI = "/v{$this->helper->apiVersion}/patients/{$person->person_id}/sessions/{$patientSession->sessionId}/conclusions";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "patientId": '.$person->person_id.',
                "patientLogin": "'.$person->person_surname.'",
                "moduleId": '.$module->moduleId.',
                "moduleName": "'.$module->moduleName.'",
                "sessionId": '.$patientSession->sessionId.',
                "sessionStartDT": "'.$patientSession->sessionStartDT.'"
            }';

            $conclusions = $this->helper->getRequest($URI, $params);
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }

        $this->ProcessModelList([$conclusions])->ReturnData();
    }


    /**
     * Получить ответы в сессии
     */
    public function getAnswers()
    {
        try {
            $data = $this->ProcessInputData('getAnswers', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);
            $patientSession =$this->getPatientSessionFromData($data);

            $URI = "/v{$this->helper->apiVersion}/patients/{$person->person_id}/sessions/{$patientSession->sessionId}/answers";
            $params = '{
                "doctorLogin": "'.$user->pmuser_surname.'",
                "doctorId": '.$user->pmuser_id.',
                "patientLogin": "'.$person->person_surname.'",
                "moduleId": '.$module->moduleId.',
                "moduleName": "'.$module->moduleName.'",
                "sessionStartDT": "'.$patientSession->sessionStartDT.'"
            }';

            $answers = $this->helper->getRequest($URI, $params);
            if (count($answers) === 0) {
                $answers = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage().' '.$URI.' '.$params])->ReturnData();
            return false;
        }

        $this->ProcessModelList($answers)->ReturnData();
    }


    /*
     * Получить заключения
     *
     *
    public function getResults()
    {
        try {
            $data = $this->ProcessInputData('getResults', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);

            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results";
            $params = '{
                "doctorLogin": "'.$user->pmuser_surname.'",
                "doctorId": '.$user->pmuser_id.',
                "patientLogin": "'.$person->person_surname.'"
            }';

            $answers = $this->helper->getRequest($URI, $params);
            if (count($answers) === 0) {
                $answers = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }

        $this->ProcessModelList($answers)->ReturnData();
    }
    */


    /**
     * Получить вектор баллов для варианта заключения
     *
     */
    public function getResultBalls()
    {
        try {
            $data = $this->ProcessInputData('getResultBalls', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);
            $result = $this->getResultFromData($data);

            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$result->resultId}/balls";
            $params = '{
                "doctorLogin": "'.$user->pmuser_surname.'",
                "doctorId": '.$user->pmuser_id.',
                "moduleName": "'.$module->moduleName.'",
                "patientLogin": "'.$person->person_surname.'"
            }';

            $answers = $this->helper->getRequest($URI, $params);
            if (count($answers) === 0) {
                $answers = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(
                ['error' => $e->getMessage().' '.$URI.' '.$params]
            )->ReturnData();
            return false;
        }

        $this->ProcessModelList($answers)->ReturnData();
    }


    /**
     * Получить мини-регистр - регистр по одному пациенту
     */
    public function getRegister()
    {
        try {
            $data = $this->ProcessInputData('getRegister', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);

            $module = $this->getModuleFromData($data);
            $register = $this->getRegisterFromData($data);
            $pagination = (object) [
                'limit' => $data['limit'],
                'offset' => $data['start'] // нестандартная пагинация
            ];

            $URI = "/v{$this->helper->apiVersion}/patients/{$person->person_id}/registers/{$register->registerId}";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "patientLogin": "'.$person->person_surname.'",
                "moduleId": '.$module->moduleId.',
                "moduleName": "'.$module->moduleName.'",
                "limit": '.$pagination->limit.',
                "offset": '.$pagination->offset.'
            }';

            $sessions = $this->helper->getRequest($URI, $params);
            if (count($sessions) === 0) {
                $sessions = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => ['error' => $e->getMessage()]])->ReturnData();
            return false;
        }

        $this->ProcessModelList([$sessions])->ReturnData();
    }


    /**
     * Получить структуру мини-регистра - регистр по одному пациенту
     */
    public function getRegisterStructure()
    {
        try {
            $data = $this->ProcessInputData('getRegisterStructure', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);
            $register = $this->getRegisterFromData($data);

            $this->helper->putUser($user);
            $this->putPerson($user, $person);

            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/structure";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "patientId": '.$person->person_id.',
                "patientLogin": "'.$person->person_surname.'",
                "moduleId": '.$module->moduleId.',
                "moduleName": "'.$module->moduleName.'"
            }';

            $sessions = $this->helper->getRequest($URI, $params);
            if (count($sessions) === 0) {
                $sessions = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => ['error' => $e->getMessage()]])->ReturnData();
            return false;
        }

        $this->ProcessModelList([$sessions])->ReturnData();
    }


    /**
     * Получить список модулей
     */
    public function getAllModules()
    {
        try {
            $data = $this->ProcessInputData('getAllModules', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);

            $URI = "/v{$this->helper->apiVersion}/modules";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "noLimit": "t"
            }';
            $modules = $this->helper->getRequest($URI, $params);
            if (count($modules) === 0) {
                $modules = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList([['error' => $e->getMessage()]])->ReturnData();
            return false;
        }

        $this->ProcessModelList([$modules])->ReturnData();

        return true;
    }


    /**
     * Получить анкету как файл (экспорт)
     *
     */
    public function getSessionAsFile()
    {
        try {
            $data = $this->ProcessInputData('getSessionAsFile', true);
            if (!is_array($data)) {
                throw new Exception('Failed to process input data');
            }

            $user = $this->helper->getUserFromData($data);
            $person = $this->getPersonFromData($data);
            $module = $this->getModuleFromData($data);
            $patientSession = $this->getPatientSessionFromData($data);

            $URI = "/v{$this->helper->apiVersion}/patients/{$person->person_id}/sessions/{$patientSession->sessionId}/export";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "patientId": '.$person->person_id.',
                "patientLogin": "'.$person->person_surname.'",
                "moduleId": '.$module->moduleId.',
                "moduleName": "'.$module->moduleName.'",
                "sessionId": '.$patientSession->sessionId.',
                "sessionStartDT": "'.$patientSession->sessionStartDT.'"
            }';

            $file = $this->helper->fileRequest($URI, $params);

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'."Анкета {$patientSession->sessionId}.html".'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($file));
            echo $file;

        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }

        /*$this->ProcessModelList($conclusions)->ReturnData();*/
        return true;
    }


    /**
     * Убедиться, что пациент зарегистрирован на сервере АПИ проекта
     *
     * @param StdClass user - врач
     * @param StdClass person - пациент
     * @throws Exception
     */
    private function putPerson($user, $person)//: void
    {
        if (!is_object($user)) {
            throw new Exception('Parameter User has wrong type');
        }
        if (!is_object($person)) {
            throw new Exception('Parameter Person has wrong type');
        }

        $URI = "/v{$this->helper->apiVersion}/patients/{$person->person_id}";

        $params = '{
            "URI": "'.$URI.'",
            "doctorId": '.$user->pmuser_id.',
            "doctorLogin": "'.$user->pmuser_surname.'",
            "patientLogin": "'.$person->person_surname.'"
        }';

        $patientData = $this->helper->putRequest($params, 'PUT');

        if (
            (!isset($patientData['patientId']))
            || ((string)$patientData['patientId'] !== $person->person_id)
            || (empty($patientData['patientLogin']))
            // регистронезависимое сравнение
            || ($patientData['patientLogin'] !== $person->person_surname)
            //|| (preg_match("/^{$person->person_surname}/iu", $patientData['patientLogin']) !== 1)
        ) {
            $response = json_encode($patientData);
            throw new Exception(
                "Failed to put person to API server of the project.
                Request parameters: $params.
                API server response: $response"
            );
        }
    }


    /**
     * Получить данные о пациенте, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass patient - объект пациента
     * @throws Exception
     */
    private function getPersonFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
        // можно не проверять - проверяется входными правилами
        $person_id = $this->helper->bigInt($data['Person_id'], 'Person_id');
        // фамилия пациента получается из БД.
        $person_fio = $this->_getPersonFullName($person_id);
        $person_surname = $this->_getPersonSurnameFromFullName($person_fio);
        return (object) [
            'person_id' => $person_id,
            'person_surname' => $person_surname,
            'person_fio' => $person_fio,
            'person_age' => $this->_getPersonAge($person_id)
        ];
    }


    /**
     * Получить ФИО пациента из БД
     *
     *  из Person_Fio - ФИО пациента из Person_model->getPersonCombo()
     * @param string Person_id - идентификатор пациента (идентификаторы обрабатываются как строки)
     * @return string Person_surname - фамилия пациента
     * @throws Exception
     */
    private function _getPersonFullName($Person_id)//: string
    {
        if (!is_string($Person_id)) {
            throw new Exception('Parameter Person_id has wrong type. String expected.');
        }

        $this->load->database();
        $this->load->model('Person_model', 'dbmodel');
        $personCombo = $this->dbmodel->getPersonCombo(['Person_id' => $Person_id]);
        if ($personCombo === false) {
            // запрос вернул ошибку
            throw new Exception('Failed to get person name. Query failed with error');
        }

        if (!is_array($personCombo)) {
            // запрос должен вернуть либо массив, либо false
            throw new Exception('Failed to get person name. Query result has wrong type');
        }

        if (empty($personCombo[0])) {
            // первый элемент массива должен содержать первую запись относительно первого найденного пациента
            throw new Exception('Failed to get person name. Empty set');
        }

        $fio = $personCombo[0]['Person_Fio'];
        if (empty($fio)) {
            // единственное поле с строке пациента - ФИО - не должно быть пустым
            throw new Exception('Failed to get person name. Empty name');
        }

        return $fio;
    }


    /**
     * Получить возраст пациента
     *
     */
    private function _getPersonAge($Person_id)
    {
        if (!is_string($Person_id)) {
            throw new Exception('Parameter Person_id has wrong type. String expected.');
        }

        $this->load->database();
        $this->load->model('Polka_PersonCard_model', 'dbmodel');
        $personAge = $this->dbmodel->getFirstResultFromQuery("
			SELECT top 1 dbo.Age2(Person_BirthDay, dbo.tzGetDate()) as PersonAge
			FROM v_PersonState (nolock)
			WHERE Person_id = :Person_id
		", ['Person_id' => $Person_id]);

        return $personAge;
    }


    /**
     * Получить фамилию пациента из ФИО
     *
     * @param fullName: string
     * @return surname: string
     */
    private function _getPersonSurnameFromFullName($fullName)// : string
    {
        if (!is_string($fullName)) {
            throw new Exception('Parameter fullName has wrong type. String expected.');
        }
        $parts = explode(' ', $fullName);
        $surname = $parts[0];

        if (empty($surname)) {
            // В общем случае фамилия пациента может быть пустой строкой.
            // Такой пациент не подходит для работы приложения
            throw new Exception('Ошибка при получении фамилии пациента');
        }

        return $surname;
    }


    /**
     * Получить данные о модуле, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass module - объект модуля
     * @throws Exception
     */
    private function getModuleFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }

        if (!empty($data['moduleName'])) {
            return (object) [
                'moduleId' => $this->helper->bigInt($data['moduleId'], 'moduleId'),
                'moduleName' => filter_var($data['moduleName'], FILTER_SANITIZE_STRING)
            ];
        }

        return (object) [
            'moduleId' => $this->helper->bigInt($data['moduleId'], 'moduleId')
        ];
    }

    /**
     * Получить данные о модуле, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass module - объект модуля
     * @throws Exception
     */
    private function getRegisterFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }

        return (object) [
            'registerId' => $this->helper->bigInt($data['registerId'], 'registerId')
        ];
    }


    /**
     * Получить данные о варианте заключения из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass module - объект модуля
     * @throws Exception
     */
    private function getResultFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
        return (object) [
            'resultId' => $this->helper->bigInt($data['resultId'], 'resultId')
        ];
    }


    /**
     * Получить данные о сессии пациента, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass module - объект сессии пациента
     * @throws Exception
     */
    private function getPatientSessionFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
        return (object) [
            'sessionId' => $this->helper->bigInt($data['sessionId'], 'sessionId'),
            'sessionStartDT' => $data['sessionStartDT']
        ];
    }


    /**
     * Получить данные о варианте ответа, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass module - объект варианта ответа
     * @throws Exception
     */
    private function getAnswerVariantFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
        return (object) ['answerVariant_id' => $this->helper->bigInt($data['answerVariant_id'], 'answerVariant_id')];
    }


    /**
     * Получить параметры пагинации, из данных, полученных от ProcessInputData
     *
     * @param array data - данные, полученные от ProcessInputData
     * @return StdClass pagination - объект параметров пагинации
     * @throws Exception
     */
    private function getPaginationFromData($data)// : StdClass
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
        return (object) [
            'offset' => filter_var($data['offset'], FILTER_VALIDATE_INT),
            'limit' => filter_var($data['limit'], FILTER_VALIDATE_INT)
        ];
    }
}
