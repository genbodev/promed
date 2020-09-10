<?php
/**
 * Контроллер приложения для просмотра клинических регистров Viewer
 *
 * DSS - Сбор структурированной медицинской информации
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
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @since        11.12.2018
 * @version      01.07.2019 - добавлен метод получения содержимого
 *     клинического регистра только для пациентов заданных участков
 *     getRegisterContentByRegions
 *
 */
class DSSViewer extends swController {

    public $inputRules = [
        // модули
        'getAllModules' => [],
        // список регистров в модуле
        'getRegisters' => [[
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
        // структура регистра (шаблон) - список полей регистра
        'getRegisterStructure' => [[
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
            ]
        ],
        // получить содержимое регистра
        'getRegisterContent' => [[
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
        // получить содержимое регистра только дял пациентов указанных участков
        'getRegisterContentByRegions' => [[
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
                'field' => 'lpuRegions',
                'label' => 'Список участков',
                'rules' => 'optional',
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
        // экспорт регистра
        'getRegisterAsFile' => [[
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
        ]]
    ];


    /**
     * Конструктор
     *
     */
    public function __construct() {
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
        $this->helper = new DSSHelper('viewer', $this->config->item('DSS_API_URL'));
    }


    /**
     * Получить список модулей
     *
     */
    public function getAllModules() {
        $data = $this->ProcessInputData('getAllModules', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
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
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($modules)->ReturnData();
		return true;
    }


    /**
     * Получить список регистров в модуле
     *
     */
    public function getRegisters() {
        $data = $this->ProcessInputData('getRegisters', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->_getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "noLimit": "t"
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
     * Получить структуру (шаблон) регистра - список полей регистра
     *
     */
    public function getRegisterStructure() {
        $data = $this->ProcessInputData('getRegisterStructure', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->_getModule($data);
            $register = $this->_getRegister($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/structure";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'"
            }';
            $registerStructure = $this->helper->getRequest($URI, $params);
            if (count($registerStructure) === 0) {
                $registerStructure = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($registerStructure)->ReturnData();
        return true;
    }


    /**
     * Получить содержание регистра
     *
     */
    public function getRegisterContent() {
        $data = $this->ProcessInputData('getRegisterContent', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->_getModule($data);
            $register = $this->_getRegister($data);
            $pagination = $this->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "limit": '.$pagination->limit.',
                "offset": '.$pagination->offset.'
            }';
            $registerData = $this->helper->getRequest($URI, $params);
            if (count($registerData) === 0) {
                $registerData = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$registerData])->ReturnData();
        return true;
    }


    /**
     * Получить содержание регистра только для пациентов заданных участков
     *
     */
    public function getRegisterContentByRegions() {
        $data = $this->ProcessInputData('getRegisterContentByRegions', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }

        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->_getModule($data);
            $register = $this->_getRegister($data);
            $pagination = $this->getPagination($data);
            $regions = $this->_getRegions($data); // участки
            $patients = $this->_getPatinents($regions, $pagination);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "patients": ['.$patients.']
            }';
            $registerData = $this->helper->getRequest($URI, $params);
            if (count($registerData) === 0) {
                $registerData = ['result' => 'empty'];
            }
        } catch(Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$registerData])->ReturnData();
        return true;
    }


    /**
     * Получить содержание регистра - экспорт файла
     *
     */
    public function getRegisterAsFile() {
        $data = $this->ProcessInputData('getRegisterAsFile', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->_getModule($data);
            $register = $this->_getRegister($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/export";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'"
            }';
            $file = $this->helper->fileRequest($URI, $params);
            $now = gmdate("Y-m-d H-i+00");

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'."{$register->registerName}_{$now}.zip".'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($file));
            echo $file;
        } catch(Exception $e) {
            /*$this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();*/
            return false;
        }
        /*$this->ProcessModelList([$registerData])->ReturnData();*/
        return true;
    }


    /**
     * Получить из параметров запроса параметры модуля: moduleId и moduleName
     *
     * @param data: array - данные, полученные от ProcessInputData
     * @return module: StdClass - объект данных модуля
     * @throws Exception
     */
    private function _getModule($data)/*: StdClass*/  {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }

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


    /**
     * Получить из параметров запроса данные регистра registerId и registerName
     *
     * @param data: array - данные, полученные от ProcessInputData
     * @return register: StdClass - объект данных регистра
     * @throws Exception
     */
    private function _getRegister($data)/*: StdClass*/  {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }

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


    /**
     * Получить из ProcessInputData список участков
     *
     * @param data: array - данные, полученные от ProcessInputData
     * @return regions: array - список участков (идентификаторы как строки)
     * @throws Exception
     */
    private function _getRegions($data)/*: array*/  {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }

        if (empty($data['lpuRegions'])) {
            return [];
        }

        if (
            (!is_string($data['lpuRegions']))
            || (filter_var($data['lpuRegions'], FILTER_SANITIZE_STRING) === false)
        ) {
            // параметр regions должен быть и должен быть валидной строкой
            throw new Exception('Failed to get lpuRegions');
        }

        return array_map(
            function($regionIdStr) {
                if (preg_match('/^[0-9]+$/', $regionIdStr) !== 1) {
                    throw new Exception('Region id has wrong type');
                }
                return $regionIdStr;
            },
            explode(',', $data['lpuRegions'])
        );
    }


    /**
     * Получить пациентов указанного участка в соответствии с пагинацией
     *
     * @param regions: array - данные, полученные от ProcessInputData
     * @param pagination: StdClass - пагинация
     * @return patients: array - список пациентов (идентификаторы как строки т фамилии)
     * @throws Exception
     */
    private function _getPatients($regions, $pagination)/*: array*/  {

        if (!is_array($regions)) {
            throw new Exception('Parameter regions has wrong type');
        }

        if (count($regions) < 1) {
            return [];
        }

        $params = [
            'SearchFormType' => 'PersonCard',
            'AttachLpu_id' => Lpu_id,
            'LpuRegion_id' => $regions[0],
            'MedPersonal_id' => MedPersonal_id
        ];

        $this->load->database();
        $this->load->model('Search_model', 'dbmodel');
        return $this->dbmodel->searchData($params, false, false, false);
    }
}
