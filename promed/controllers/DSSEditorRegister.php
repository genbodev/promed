<?php
/**
 * Контроллер регистров
 *
 * Контроллер редактора опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 * Получает (проверенные) идентификатр (и ФИО) медработника
 *    и перенаправляет запрос на сервер АПИ проекта
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @since        10.12.2018
 * @version      10.12.2018
 *
 */
class DSSEditorRegister extends swController {

    public $inputRules = [
        'getRegisters' => [[ // получить список регистров
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'patchRegister' => [[ // переименовать регистр
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
                'field' => 'registerName',
                'label' => 'Название регистра',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'registerId',
                'label' => 'Идентификатор регистра',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'postRegister' => [[   // новый регистр
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
                'field' => 'registerName',
                'label' => 'Название регистра',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteRegister' => [[   // удалить регистр
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
            ], [
                'field' => 'registerName',
                'label' => 'Название регистра',
                'rules' => 'required',
                'type' => 'string'
        ]],
        // пользователи, имеющие право на просмотр данных регистров
        'getRegisterViewers' => [[   // список пользователей, имеющих право на просмотр данных этого регистра
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
            ], [
                'field' => 'registerName',
                'label' => 'Название регистра',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'limit',
                'label' => 'Пагинация. Количество',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'start',
                'label' => 'Пагинация. Отступ',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'putRegisterViewer' => [[   // добавить пользователя в список
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
            ], [
                'field' => 'registerName',
                'label' => 'Название регистра',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'doctorId',
                'label' => 'Идентификатор пользователя pmuser_id, которого надо добавить в список',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'doctorLogin',
                'label' => 'Логин пользователя',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteRegisterViewer' => [[   // добавить пользователя в список
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
            ], [
                'field' => 'registerName',
                'label' => 'Название регистра',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'doctorId',
                'label' => 'Идентификатор пользователя pmuser_id, которого надо удалить из списка',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'doctorLogin',
                'label' => 'Логин пользователя',
                'rules' => 'required',
                'type' => 'string'
        ]],
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
        $this->helper = new DSSHelper('editor', $this->config->item('DSS_API_URL'));
    }


    /**
     * Получить список регистров
     *
     */
    function getRegisters() {
        $data = $this->ProcessInputData('getRegisters', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $pagination = $this->helper->getPagination($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $registers = $this->helper->getRequest($URI, $params);
            if (count($registers) === 0) {
                $registers = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($registers)->ReturnData();
		return true;
    }


    /**
     * Переименовать регистр
     *
     */
    function patchRegister() {
        $data = $this->ProcessInputData('patchRegister', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'"
            }';
            $result = $this->helper->putRequest($params, 'PUT');
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$result])->ReturnData();
        return true;
    }


    /**
     * Создать регистр
     *
     */
    function postRegister() {
        $data = $this->ProcessInputData('postRegister', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data, 'name');
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'"
            }';
            $result = $this->helper->postRequest($params);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$result])->ReturnData();
        return true;
    }


    /**
     * Удалить регистр
     *
     */
    function deleteRegister() {
        $data = $this->ProcessInputData('deleteRegister', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $result = $this->helper->putRequest($params, 'DELETE');
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Получить список пользователей, у которых есть право просмотра данных регистра
     *
     */
    function getRegisterViewers() {
        $data = $this->ProcessInputData('getRegisterViewers', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/viewers";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $editors = $this->helper->getRequest($URI, $params);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$editors])->ReturnData();
        return true;
    }


    /**
     * Внести пользователя в список пользователей, имеющих право просмотра данных регистра
     *
     */
    function putRegisterViewer() {
        $data = $this->ProcessInputData('putRegisterViewer', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $grantee = $this->getGrantee($data);
            $register = $this->getRegister($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/viewers/{$grantee->doctorId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "targetDoctorLogin": "'.$grantee->doctorLogin.'"
            }';
            $result = $this->helper->putRequest($params, 'PUT');
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Удалить пользователя из списка пользователей, имеющих право просмотра данных регистра
     *
     */
    function deleteRegisterViewer() {
        $data = $this->ProcessInputData('deleteRegisterViewer', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $grantee = $this->getGrantee($data);
            $register = $this->getRegister($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/viewers/{$grantee->doctorId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "targetDoctorLogin": "'.$grantee->doctorLogin.'"
            }';
            $result = $this->helper->putRequest($params, 'DELETE');
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Получить из параметров запроса module_id или module_name (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getModule($data)//: object
    {
        if (
            (!isset($data['moduleId']))
            || (filter_var($data['moduleId'], FILTER_VALIDATE_INT) === false)
            || (empty($data['moduleName']))
        ) {
            throw new Exception('Failed to get module');
        }

        return (object) [
            'moduleId' => filter_var($data['moduleId'], FILTER_VALIDATE_INT),
            'moduleName' => $data['moduleName']
        ];
    }


    /**
     * Получить из параметров запроса registerId или registerName (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getRegister($data, $mode = null)//: object
    {
        switch ($mode) {

            case 'name':
                if (empty($_REQUEST['registerName'])) {
                    throw new Exception('Failed to get register');
                }
                return (object) ['registerName' => $data['registerName']];

            default:
                if (
                    (!isset($data['registerId']))
                    || (filter_var($data['registerId'], FILTER_VALIDATE_INT) === false)
                    || (empty($data['registerName']))
                ) {
                    throw new Exception('Failed to get register');
                }
                return (object) [
                    'registerId' => intval($data['registerId']),
                    'registerName' => $data['registerName']
                ];

        }
    }


    /**
     * Получить doctor_id - идентификатор медработника,
     * которому изменяют права доступа, из параметров запроса
     *
     * @throws Exception
     */
    private function getGrantee($data)//: object
    {
        if (
            (!isset($data['doctorId']))
            || (filter_var($data['doctorId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get grantee id');
        }

        if (
            (empty($data['doctorLogin']))
            || (filter_var($data['doctorLogin'], FILTER_SANITIZE_STRING) === false)
        ) {
            throw new Exception('Failed to get grantee last name');
        }

        return (object) [
            'doctorId' => intval($data['doctorId']),
            'doctorLogin' => filter_var($data['doctorLogin'], FILTER_SANITIZE_STRING)
        ];
    }
}
