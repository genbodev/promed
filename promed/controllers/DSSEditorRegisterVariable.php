<?php
/**
 * Контроллер вариантов значений полей регистра
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
class DSSEditorRegisterVariable extends swController {

    public $inputRules = [
        'getVariables' => [[ // получить список регистров
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
        'patchVariable' => [[ // переименовать регистр
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
                'field' => 'variableName',
                'label' => 'Название значения поля регистра',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'answerVariantId',
                'label' => 'Вариант ответа, сответствующий значнию поля регистра',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'postVariable' => [[   // новый регистр
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
            ],[
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
                'field' => 'answerVariantId',
                'label' => 'Вариант ответа, соответствующий варианту значения поля регистра',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'deleteVariable' => [[   // удалить регистр
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
                'field' => 'answerVariantId',
                'label' => 'Идентификатор варианта ответа',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'variableName',
                'label' => 'Название значения поля регистра',
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
     * Получить список вариантов значения поля регистра
     *
     */
    function getVariables() {
        $data = $this->ProcessInputData('getVariables', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}/variables";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'"
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
     * Переименовать вариант значения поля регистра
     *
     */
    function patchVariable() {
        $data = $this->ProcessInputData('patchVariable', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $variable = $this->getVariable($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}/variables/{$variable->answerVariantId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'",
                "variableName": "'.$variable->variableName.'"
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
     * Создать вариант значения поля регистра
     *
     */
    function postVariable() {
        $data = $this->ProcessInputData('postVariable', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $answerVariantId = $this->_getAnswerVariantId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}/variables";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "registerName": "'.$register->registerName.'",
                "featureName": "'.$feature->featureName.'",
                "answerVariantId": '.$answerVariantId.'
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
     * Удалить вариант значения поля регистра
     *
     */
    function deleteVariable() {
        $data = $this->ProcessInputData('deleteVariable', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $register = $this->getRegister($data);
            $feature = $this->getRegisterFeature($data);
            $variable = $this->getVariable($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/registers/{$register->registerId}/features/{$feature->featureId}/variables/{$variable->answerVariantId}";
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
     * Получить из параметров запроса module_id или module_name (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getModule($data, $mode = null)//: object
    {
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
     * Получить из параметров запроса registerId или registerName (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getRegister($data, $mode = null)//: object
    {
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
     * Получить из параметров запроса registerId или registerName (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getRegisterFeature($data, $mode = null)//: object
    {
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


    /**
     * Получить из параметров запроса registerId или registerName (или и то, и другое)
     * в зависимости от указанного режима ('id', 'name', 'both', 'status')
     *
     * @throws Exception
     */
    private function getVariable($data)//: object
    {
        if (
            (!isset($data['variableName']))
            || ($data['variableName'] === '')
            || (filter_var($data['variableName'], FILTER_SANITIZE_STRING) === false)
        ) {
            throw new Exception('Failed to get register feature variable');
        }

        return (object) [
            'variableName' => $data['variableName'],
            'answerVariantId' => $this->_getAnswerVariantId($data)
        ];
    }


    /**
     * Получить идентификатор варианта ответа из параметров запроса для
     *     создания нового значения поля регистра
     *
     * @param {array} data
     * @return {int}
     * @throws Exception
     */
    private function _getAnswerVariantId($data) {
        if (
            (!isset($data['answerVariantId']))
            || (filter_var($data['answerVariantId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get answerVariantId');
        }

        return filter_var($data['answerVariantId'], FILTER_VALIDATE_INT);
    }
}
