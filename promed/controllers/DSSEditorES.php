<?php
/**
* Контроллер вариантов заключений и таблицы баллов
*
* Контроллер редактора опросников
*
* Сбор структурированной медицинской информации
*               и поддержка принятия решений
*
*
* Получает (проверенные) идентификатр (и ФИО) медработника
*   и перенаправляет запрос на сервер АПИ проекта
*   Экспертная система (кроме рекомендаций и назначений)
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
class DSSEditorES extends swController {

    public $inputRules = [
        'getResults' => [[ // получить заключения 1
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
        'postResult' => [[ // добавить заключение 2
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
                'field' => 'resultName',
                'label' => 'Формулировка заключения',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteResult' => [[ // удалить заключение 3
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
        'patchResult' => [[ // изменить заключение 4
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
                'field' => 'resultName',
                'label' => 'Формулировка заключения',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'resultThreshold',
                'label' => 'Пороговое значение оценки',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'getResults2restore' => [[ // изменить заключение 4
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
        'putResult2restore' => [[ // изменить заключение 4
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
                'label' => 'Идкеификатор заключения',
                'rules' => 'required',
                'type' => 'int'
        ]],
        //баллы 15
        'getBalls' => [[ // получить баллы 7
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
        'putBall' => [[ // изменить баллы 8
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
                'field' => 'answerVariantId',
                'label' => 'Идентификатор варинта ответа',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'resultId',
                'label' => 'Идентификатор заключения',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'value',
                'label' => 'Балл (в виде целого числа, балл*100)',
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
     * Получить заключения 1
     *
     */
    function getResults() {
        $data = $this->ProcessInputData('getResults', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $results = $this->helper->getRequest($URI, $params);
            if (count($results) == 0) $results = ['result' => 'empty'];
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($results)->ReturnData();
        return true;
    }


    /**
     * Создать заключение 2
     *
     */
    function postResult() {
        $data = $this->ProcessInputData('postResult', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultName = $this->getResultName($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "resultName": "'.$resultName.'"
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
     * Изменить заключение 4
     *
     */
    function patchResult() {
        $data = $this->ProcessInputData('patchResult', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $result = $this->getResult($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$result->resultId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "resultName": "'.$result->resultName.'",
                "resultThreshold": '.$result->resultThreshold.'
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
     * Удалить заключение 3
     *
     */
    function deleteResult() {
        $data = $this->ProcessInputData('deleteResult', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$resultId}";
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
     * Получить баллы 7
     *
     */
    function getBalls() {
        $data = $this->ProcessInputData('getBalls', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/balls";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $balls = $this->helper->getRequest($URI, $params);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$balls])->ReturnData();
        return true;
    }


    /**
     * Изменить балл 8
     *
     */
    function putBall() {
        $data = $this->ProcessInputData('putBall', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $ball = $this->getBall($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results/{$ball->resultId}/balls/{$ball->answerVariantId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "value": '.$ball->value.'
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
     * Получить удалённые заключения (для восстановления) 5
     *
     */
    function getResults2restore() {
        $data = $this->ProcessInputData('getResults2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results2restore";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $answerVariants = $this->helper->getRequest($URI, $params);
            if (count($answerVariants) == 0) $answerVariants = ['result' => 'empty'];
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($answerVariants)->ReturnData();
        return true;
    }


    /**
     * Восстановить заключение 6
     *
     */
    function putResult2restore() {
        $data = $this->ProcessInputData('putResult2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $resultId = $this->getResultId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/results2restore/{$resultId}";
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
    private function getBall($data)//: object
    {
        if (
            (!isset($data['resultId']))
            || (filter_var($data['resultId'], FILTER_VALIDATE_INT) === false)
            || (!isset($_REQUEST['answerVariantId']))
            || (filter_var($data['answerVariantId'], FILTER_VALIDATE_INT) === false)
            || (!isset($data['value']))
            || (filter_var($data['value'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Could not get ball');
        }
        return (object) [
            'resultId' => intval($data['resultId']),
            'answerVariantId' => intval($data['answerVariantId']),
            'value' => intval($data['value'])
        ];
    }


    /**
     * Получить из параметров запроса moduleId
     * @throws Exception
     */
    private function getResultId($data)//: int
    {
        if (
            (!isset($data['resultId']))
            || (filter_var($data['resultId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Could not get result id');
        }
        return intval($data['resultId']);
    }


    /**
     * Получить из параметров запроса moduleId
     * @throws Exception
     */
    private function getResultName($data)//: int
    {
        if (empty($data['resultName'])) {
            throw new Exception('Could not get result name');
        }
        return $data['resultName'];
    }


    /**
     * Получить из параметров запроса resultName и resultThreshold
     *   resultName может быть пуст
     *   resultThreshold обязателен
     * @throws Exception
     */
    private function getResult($data)//: object
    {
        if (
            (!isset($data['resultId']))
            || (filter_var($data['resultId'], FILTER_VALIDATE_INT) === false)
            || (
                (empty($data['resultName']))
                && (
                    (!isset($data['resultThreshold']))
                    || (filter_var($data['resultThreshold'], FILTER_VALIDATE_INT) === false)
                )
            )
        ) {
            throw new Exception('Failed to get result');
        }
        $resultName = '';
        if (!empty($data['resultName'])) {
            $resultName = $data['resultName'];
        }
        return (object) [
            'resultId' => intval($data['resultId']),
            'resultName' => $resultName,
            'resultThreshold' => intval($data['resultThreshold'])
        ];
    }
}
