<?php
/**
 * Контроллер назначений
 *
 * Контроллер редактора опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 *
 * Получает (проверенные) идентификатр (и ФИО) медработника
 *   и перенаправляет запрос на сервер АПИ проекта
 *   Экспертная система (назначения)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @since        27.08.2018
 * @version      7.12.2018
 *
 */
class DSSEditorESRecommendation2Result extends swController {

    public $inputRules = [

        //рекомендации для заключения (назначения)
        'getRecommendations2Result' => [[ // получить рекомендации для заключения 1

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
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'postRecommendation2Result' => [[ // добавить рекомендацию для заключения2
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
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendationId',
                'label' => 'Идентификатор рекомендации',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'deleteRecommendation2Result' => [[ // удалить рекомендацию для заключения 3
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
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendation2ResultId',
                'label' => 'Идентификатор рекомендации для заключения',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'updateRecommendation2Result' => [[ // изменить рекомендацию для заключения 4
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
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendation2ResultId',
                'label' => 'Идентификатор рекомендации для заключения',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'isConditional',
                'label' => 'необходимость добавки "по назначению"',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'getRecommendations2Result2restore' => [[ // получить удалённые рекомендации для заключения для восстановления 5
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
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'putRecommendation2Result2restore' => [[ // восстановить рекомендацию для заключения 6
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
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendation2ResultId',
                'label' => 'Идентификатор рекомендации для заключения',
                'rules' => 'required',
                'type' => 'int'
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
     * Получить рекомендации 1
     *
     */
    function getRecommendations2Result() {
        $data = $this->ProcessInputData('getRecommendations2Result', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}/recommendations";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $recommendaitons = $this->helper->getRequest($URI, $params);
            if (count($recommendaitons) == 0) {
                $recommendaitons = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($recommendaitons)->ReturnData();
        return true;
    }


    /**
     * Создать рекомендацию 2
     *
     */
    function postRecommendation2Result() {
        $data = $this->ProcessInputData('postRecommendation2Result', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $recommendationId = $this->getRecommendationId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}/recommendations";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "recommendationId": '.$recommendationId.'
            }';
            $result = $this->helper->postRequest($params);
        }
        catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Удалить рекомендацию 3
     */
    public function deleteRecommendation2Result()
    {
        $data = $this->ProcessInputData('deleteRecommendation2Result', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $recommendation2ResultId = $this->getRecommendation2ResultId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}/recommendations/{$recommendation2ResultId}";
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
     * Изменить рекомендацию 4
     *
     */
    function updateRecommendation2Result() {
        $data = $this->ProcessInputData('updateRecommendation2Result', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $recommendation2ResultId = $this->getRecommendation2ResultId($data);
            $isConditional = $this->getIsConditional($data) ? 'true' : 'false';
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}/recommendations/{$recommendation2ResultId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "isConditional": '.$isConditional.'
            }';
            $result = $this->helper->putRequest($params, 'PATCH');
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Получить удалённые рекомендации (для восстановления) 5
     */
    public function getRecommendations2Result2restore()
    {
        $data = $this->ProcessInputData('getRecommendations2Result2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}/recommendations2restore";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $answerVariants = $this->helper->getRequest($URI, $params);
            if (count($answerVariants) == 0) {
                $answerVariants = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($answerVariants)->ReturnData();
        return true;
    }


    /**
     * Восстановить рекомендацию 6
     */
    public function putRecommendation2Result2restore()
    {
        $data = $this->ProcessInputData('putRecommendation2Result2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $recommendation2ResultId = $this->getRecommendation2ResultId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}/recommendations2restore/{$recommendation2ResultId}/restore";
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
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Получить из параметров запроса module_id
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
            'moduleId' => intval($data['moduleId']),
            'moduleName' => $data['moduleName']
        ];
    }


    /**
     * Получить из параметров запроса recommendation_id
     * @throws Exception
     */
    private function getResultId($data)//: int
    {
        if (
            (!isset($data['resultId']))
            || (filter_var($data['resultId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get resultId');
        }
        return intval($data['resultId']);
    }


    /**
     * Получить из параметров запроса recommendation_id
     * @throws Exception
     */
    private function getRecommendation2ResultId($data)//: int
    {
        if (
            (!isset($data['recommendation2ResultId']))
            || (filter_var($data['recommendation2ResultId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get recommendation2ResultId');
        }
        return intval($data['recommendation2ResultId']);
    }


    /**
     * Получить из параметров запроса isConditional
     *
     * @throws Exception
     */
    private function getIsConditional($data)//: boolean
    {
        if (!isset($data['isConditional'])) {
            throw new Exception('Failed to get isConditional. not set');
        }

        switch ($data['isConditional']) {

            case 't': {
                return true;
                break;
            }

            case 'f': {
                return false;
                break;
            }

            default: {
                throw new Exception('Failed to get isConditional. wrong value');
                break;
            }

        }
    }


    /**
     * Получить из параметров запроса recommendation_id
     * @throws Exception
     */
    private function getRecommendationId($data)//: int
    {
        if (
            (!isset($data['recommendationId']))
            || (filter_var($data['recommendationId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get recommendationId');
        }
        return intval($data['recommendationId']);
    }
}
