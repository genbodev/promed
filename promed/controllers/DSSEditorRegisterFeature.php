<?php
/**
 * Контроллер полей регистра
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
class DSSEditorRegisterFeature extends swController {

    public $inputRules = [
        'getRegisterFeatures' => [[ // получить список регистров
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
        'patchRegisterFeature' => [[ // переименовать регистр
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
                'field' => 'registerFeatureId',
                'label' => 'Идентификатор поля регистра',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'registerFeatureName',
                'label' => 'Название поля регистра',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'postRegisterFeature' => [[   // новый регистр
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
                'field' => 'registerFeatureName',
                'label' => 'Название поля регистра',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteRegisterFeature' => [[   // удалить регистр
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
                'field' => 'registerFeatureId',
                'label' => 'Идентификатор поля регистра',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'registerFeatureName',
                'label' => 'Название поля регистра',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'moveRegisterFeature' => [[   // удалить регистр
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
                'field' => 'registerFeatureId',
                'label' => 'Идентификатор поля регистра',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'registerFeatureName',
                'label' => 'Название поля регистра',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'direction',
                'label' => 'Напрвление: вверх или вниз',
                'rules' => 'required',
                'type' => 'string'
            ]
        ]
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
     * Получить список полей клинического регистра
     *
     */
    function getRegisterFeatures() {
        $data = $this->ProcessInputData('getRegisterFeatures', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'"
            }';
            $registers = $this->helper->getRequest($URI, $params);
            if (count($registers) === 0) {
                $registers = ['result' => 'empty'];
            }
        } catch (Exception $e) {
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
    function patchRegisterFeature() {
        $data = $this->ProcessInputData('patchRegisterFeature', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'"
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
    function postRegisterFeature() {
        $data = $this->ProcessInputData('postRegisterFeature', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data, 'name');
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'"
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
    function deleteRegisterFeature() {
        $data = $this->ProcessInputData('deleteRegisterFeature', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'"
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
     * Переместить поле регистра
     *
     */
    function moveRegisterFeature() {
        $data = $this->ProcessInputData('moveRegisterFeature', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $direction = $this->getDirection($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}/{$direction}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'"
            }';
            $result = $this->helper->postRequest($params, 'POST');
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
    private function getModule($data, $mode = null)//: object
    {
        switch ($mode) {

            case 'name':
                if (empty($_REQUEST['moduleName'])) {
                    throw new Exception('Could not get module');
                }
                return (object) ['moduleName' => $data['moduleName']];

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
     * Получить из параметров запроса registerId или registerName (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getRegisterFeature($data, $mode = null)//: object
    {
        switch ($mode) {

            case 'name':
                if (empty($_REQUEST['registerFeatureName'])) {
                    throw new Exception('Failed to get register feature');
                }
                return (object) ['featureName' => $data['registerFeatureName']];

            default:
                if (
                    (!isset($data['registerFeatureId']))
                    || (filter_var($data['registerFeatureId'], FILTER_VALIDATE_INT) === false)
                    || (empty($data['registerFeatureName']))
                ) {
                    throw new Exception('Failed to get register feature');
                }
                return (object) [
                    'featureId' => intval($data['registerFeatureId']),
                    'featureName' => $data['registerFeatureName']
                ];

        }
    }


    /**
     * Получить из параметров запроса direction
     *
     * @throws Exception
     */
    private function getDirection($data)//: string
    {
        if (
            (empty($data['direction']))
            || (
                ($data['direction'] !== 'up')
                && ($data['direction'] !== 'down')
            )
        ) {
            throw new Exception('Failed to get register feature move direction');
        }
        return $data['direction'];
    }
}
