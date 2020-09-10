<?php
/**
 * Контроллер рекомендаций, типов рекомендаций и назначений исследований
 *
 * Контроллер редактора опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 *
 * Получает (проверенные) идентификатр (и ФИО) медработника
 *   и перенаправляет запрос на сервер АПИ проекта
 *   Экспертная система (рекомендации)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2018-2019 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @since        27.08.2018
 * @version      24.04.2019
 *
 */
class DSSEditorESRecommendation extends swController {

    public $inputRules = [

        //рекомендации 14
        'getRecommendations' => [[ // получить рекомендации 1
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
        'postRecommendation' => [[ // добавить рекомендацию 2
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
                'field' => 'recommendationText',
                'label' => 'Формулировка рекомендации',
                'rules' => 'required',
                'type' => 'string'
            ],
            [
                'field' => 'recommendationTypeId',
                'label' => 'Идентификатор типа рекомендаций',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'deleteRecommendation' => [[ // удалить рекомендацию 3
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
                'field' => 'recommendationId',
                'label' => 'Идентификатор рекомендации',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'updateRecommendationText' => [[ // изменить рекомендацию 4
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
                'field' => 'recommendationId',
                'label' => 'Идентификатор рекомендации',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendationText',
                'label' => 'Формулировка рекомендации',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'recommendationTypeId',
                'label' => 'Идентификатор типа рекомендаций',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'updateRecommendationType' => [[ // изменить рекомендацию 5
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
                'field' => 'recommendationId',
                'label' => 'Идентификатор рекомендации',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendationTypeId',
                'label' => 'Идентификатор типа рекомендаций',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'getRecommendations2restore' => [[ // получить удалённые рекомендации для восстановления 6
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
                'label' => 'Пагинация. Количество записей на странице',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'putRecommendation2restore' => [[ // восстановить рекомендацию 7
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
                'field' => 'recommendationId',
                'label' => 'Идентификатор рекомендации',
                'rules' => 'required',
                'type' => 'int'
        ]],

        // типы рекомендаций
        'getRecommendationTypes' => [[ // получить типы рекомендаций 8
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
        'postRecommendationType' => [[ // добавить тип рекомендаций 9
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
                'field' => 'recommendationTypeName',
                'label' => 'Название типа рекомендаций',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteRecommendationType' => [[ // удалить тип рекомендаций 10
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
                'field' => 'recommendationTypeId',
                'label' => 'Идентификатор типа рекомендаций',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'updateRecommendationTypeName' => [[ // изменить тип рекомендаций 11
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
                'field' => 'recommendationTypeId',
                'label' => 'Идентификатор типа рекомендаций',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'recommendationTypeName',
                'label' => 'Название типа рекомендаций',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'getRecommendationTypes2restore' => [[ // получить удалённые типы рекомендаций для восстановления 12
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
                'label' => 'Пагинация. Количество записей на странице',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'putRecommendationType2restore' => [[ // восстановить тип рекомендаций 13
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
                'field' => 'recommendationTypeId',
                'label' => 'Идентификатор типа рекомендаций',
                'rules' => 'required',
                'type' => 'int'
        ]]
    ];


    /**
     *  Конструктор
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
    function getRecommendations() {
        $data = $this->ProcessInputData('getRecommendations', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations";
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
    function postRecommendation() {
        $data = $this->ProcessInputData('postRecommendation', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationText = $this->getRecommendationText($data);
            $recommendationTypeId = $this->getRecommendationTypeId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "recommendationText": "'.$recommendationText.'",
                "recommendationTypeId": '.$recommendationTypeId.'
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
    public function deleteRecommendation()
    {
        $data = $this->ProcessInputData('deleteRecommendation', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationId = $this->getRecommendationId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations/{$recommendationId}";
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
    function updateRecommendationText() {
        $data = $this->ProcessInputData('updateRecommendationText', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationId = $this->getRecommendationId($data);
            $recommendationText = $this->getRecommendationText($data);
            $recommendationTypeId = $this->getRecommendationTypeId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations/{$recommendationId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "recommendationText": "'.$recommendationText.'",
                "recommendationTypeId": '.$recommendationTypeId.'
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
     * Изменить рекомендацию 4
     *
     */
    function updateRecommendationType() {
        $data = $this->ProcessInputData('updateRecommendationType', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationId = $this->getRecommendationId($data);
            $recommendationTypeId = $this->getRecommendationTypeId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations/{$recommendationId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "recommendationTypeId": '.$recommendationTypeId.'
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
     *
     */
    function getRecommendations2restore() {
        $data = $this->ProcessInputData('getRecommendations2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations2restore";
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
     *
     */
    function putRecommendation2restore() {
        $data = $this->ProcessInputData('putRecommendation2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationId = $this->getRecommendationId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendations2restore/{$recommendationId}";
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
     * Получить типы рекомендаций 7
     */
    public function getRecommendationTypes()
    {
        $data = $this->ProcessInputData('getRecommendationTypes', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendationTypes";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $recommendaitonTypes = $this->helper->getRequest($URI, $params);
            if (count($recommendaitonTypes) === 0) {
                $recommendaitonTypes = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($recommendaitonTypes)->ReturnData();
        return true;
    }


    /**
     * Создать тип рекомендации 8
     */
    public function postRecommendationType()
    {
        $data = $this->ProcessInputData('postRecommendationType', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationTypeName = $this->getRecommendationTypeName($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendationTypes";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "recommendationTypeName": "'.$recommendationTypeName.'"
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
     * Изменить рекомендации 9
     */
    public function updateRecommendationTypeName()
    {
        $data = $this->ProcessInputData('updateRecommendationTypeName', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationTypeId = $this->getRecommendationTypeId($data);
            $recommendationTypeName = $this->getRecommendationTypeName($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendationTypes/{$recommendationTypeId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "recommendationTypeName": "'.$recommendationTypeName.'"
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
     * Удалить тип рекомендаций 10
     */
    public function deleteRecommendationType()
    {
        $data = $this->ProcessInputData('deleteRecommendationType', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationTypeId = $this->getRecommendationTypeId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendationTypes/{$recommendationTypeId}";
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
     * Получить удалённые тип рекомендаций (для восстановления) 11
     *
     */
    function getRecommendationTypes2restore() {
        $data = $this->ProcessInputData('getRecommendationTypes2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendationTypes2restore";
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
     * Восстановить тип рекомендаций 12
     *
     */
    function putRecommendationType2restore() {
        $data = $this->ProcessInputData('putRecommendationType2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $recommendationTypeId = $this->getRecommendationTypeId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/recommendationTypes2restore/{$recommendationTypeId}";
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
     * Получить из параметров запроса moduleId
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
     * Получить из параметров запроса moduleId
     * @throws Exception
     */
    private function getTestId($data)//: int
    {
        if (
            (!isset($data['testId']))
            || (filter_var($data['testId'], FILTER_VALIDATE_INT) === false)
        ) {
            //throw new Exception('Could not get module');
            return null;
        }
        return intval($data['testId']);
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


    /**
     * Получить из параметров запроса moduleId
     * @throws Exception
     */
    private function getRecommendationText($data)//: int
    {
        if (empty($data['recommendationText'])) {
            throw new Exception('Failed to get recommendationText');
        }
        return $data['recommendationText'];
    }


    /**
     * Получить из параметров запроса moduleId
     * @throws Exception
     */
    private function getRecommendationTypeId($data)//: int
    {
        if (
            (!isset($data['recommendationTypeId']))
            || (filter_var($data['recommendationTypeId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get recommendationTypeId');
        }
        return intval($data['recommendationTypeId']);
    }


    /**
     * Получить из параметров запроса moduleId
     * @throws Exception
     */
    private function getRecommendationTypeName($data)//: int
    {
        if (empty($data['recommendationTypeName'])) {
            throw new Exception('Failed to get recommendationTypeName');
        }
        return $data['recommendationTypeName'];
    }
}
