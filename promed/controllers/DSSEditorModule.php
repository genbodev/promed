<?php
/**
 * Контроллер модулей
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
 * @since        29.05.2018
 * @version      7.12.2018
 *
 */
class DSSEditorModule extends swController {

    public $inputRules = [
        // модули
        'getModules' => [],
        'patchModule' => [[ // переименовать модуль
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
                'field' => 'moduleStatus',
                'label' => 'Статус модуля',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'newModuleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'patchModuleStatus' => [[// переименовать модуль
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
                'field' => 'moduleStatus',
                'label' => 'Статус модуля',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'postModule' => [[   // новый модуль
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteModule' => [[   // удалить модуль
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
        // блокировка модуля
        'putModuleLock' => [[   // заблокировать модуль
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
        'deleteModuleLock' => [[  // разблокировать модуль
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
        // редакторы модуля
        'getModuleEditors' => [[   // получить список редакторов модуля
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
        'putModuleEditor' => [[   // добавить редактора модуля
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
                'field' => 'doctorId',
                'label' => 'Идентификатор медработника (которому нужно дать право редактирования модуля)',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'doctorLogin',
                'label' => 'Логин медработника (которому нужно дать право редактирования модуля)',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteModuleEditor' => [[   // удалить редактора модуля
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
                'field' => 'doctorId',
                'label' => 'Идентификатор медработника (которому нужно дать право редактирования модуля)',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'doctorLogin',
                'label' => 'Фамилия медработника (которому нужно дать право редактирования модуля)',
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
        $this->helper = new DSSHelper('editor', $this->config->item('DSS_API_URL'));
    }


    /**
     * Получить список модулей
     *
     */
    function getModules() {
        $data = $this->ProcessInputData('getModules', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            //$pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "noLimit": "t"
            }';
            $modules = $this->helper->getRequest($URI, $params);
            if (count($modules) == 0) {
                $modules = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($modules)->ReturnData();
		return true;
    }


    /**
     * Переименовать модуль
     *
     */
    function patchModule() {
        $data = $this->ProcessInputData('patchModule', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data, 'status');
            $newModuleName = $data['newModuleName'];

            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "newModuleName": "'.$newModuleName.'",
                "moduleStatus": "'.$module->moduleStatus.'"
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
     * Изменить видимость модуля
     *
     */
    function patchModuleStatus() {
        $data = $this->ProcessInputData('patchModuleStatus', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data, 'status');

            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "newModuleName": "'.$module->moduleName.'",
                "moduleStatus": "'.$module->moduleStatus.'"
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
     * Создать модуль
     *
     */
    function postModule() {
        $data = $this->ProcessInputData('postModule', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data, 'name'); // только название модуля
            $URI = "/v{$this->helper->apiVersion}/modules";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $result = $this->helper->postRequest($params);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Удалить модуль
     *
     */
    function deleteModule()
    {
        $data = $this->ProcessInputData('deleteModule', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            // блокировка модуля
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/lock";
            $lockParams = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $this->helper->putRequest($lockParams, 'PUT');
            //удаление
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}";
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
     * Заблокировать модуль
     *
     */
    public function putModuleLock()
    {
        $data = $this->ProcessInputData('putModuleLock', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/lock";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
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
     * Разблокировать модуль
     *
     */
    function deleteModuleLock()
    {
        $data = $this->ProcessInputData('deleteModuleLock', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/lock";
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
        $this->ProcessModelList([$result])->ReturnData();
        return true;
    }


    /**
     * Дать право редактирования модуля
     *
     */
    function putModuleEditor() {
        $data = $this->ProcessInputData('putModuleEditor', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $grantee = $this->getGrantee($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/editors/{$grantee->doctorId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
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
     * Отозвать право редактирования модуля
     *
     */
    function deleteModuleEditor() {
        $data = $this->ProcessInputData('deleteModuleEditor', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $grantee = $this->getGrantee($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/editors/{$grantee->doctorId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
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
     * Получить список редакторов модуля
     *
     */
    function getModuleEditors() {
        $data = $this->ProcessInputData('getModuleEditors', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/editors";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $editors = $this->helper->getRequest($URI, $params);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($editors)->ReturnData();
        return true;
    }


    /**
     * Получить из параметров запроса module_id или module_name (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getModule($data, $mode = null)//: object
    {
        switch ($mode) {

            case 'id':
                if (
                    (!isset($data['moduleId']))
                    || (filter_var($data['moduleId'], FILTER_VALIDATE_INT) === false)
                ) {
                    throw new Exception('Could not get module');
                }
                return (object) ['moduleId' => intval($data['moduleId'])];

            case 'name':
                if (empty($_REQUEST['moduleName'])) {
                    throw new Exception('Could not get module');
                }
                return (object) ['moduleName' => $data['moduleName']];

            case 'both':
                if (
                    (!isset($data['moduleId']))
                    || (filter_var($data['moduleId'], FILTER_VALIDATE_INT) === false)
                    || (empty($data['moduleName']))
                ) {
                    throw new Exception('Could not get module');
                }
                return (object) [
                    'moduleId' => intval($data['moduleId']),
                    'moduleName' => $data['moduleName']
                ];

            case 'status':
                if (
                    (!isset($data['moduleId']))
                    || (filter_var($data['moduleId'], FILTER_VALIDATE_INT) === false)
                    || (empty($data['moduleName']))
                    || (empty($data['moduleStatus']))
                ) {
                    throw new Exception('Failed to get module');
                }
                return (object) [
                    'moduleId' => intval($data['moduleId']),
                    'moduleName' => $data['moduleName'],
                    'moduleStatus' => $data['moduleStatus']
                ];

            default:
                if (
                    (!isset($data['moduleId']))
                    || (filter_var($data['moduleId'], FILTER_VALIDATE_INT) === false)
                    || (empty($data['moduleName']))
                ) {
                    throw new Exception('Failed to get module');
                }
                return (object) [
                    'moduleId' => intval($data['moduleId']),
                    'moduleName' => $data['moduleName']
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
