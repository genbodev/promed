<?php
/**
 * Контроллер действий с вопросами
 *
 * Контроллер редактора опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 * Получает (проверенные) идентификатр (и ФИО) медработника
 *   и перенаправляет запрос на сервер АПИ проекта
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
class DSSEditorQuestion extends swController {

    public $inputRules = [
        // опросник в модуле 4
        'getModuleQuestions' => [[   // получть опросник
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
        // вопросы в модуле
        'postModuleQuestion' => [[ // создать вопрос
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
                'field' => 'questionText',
                'label' => 'Формулировка вопроса',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteModuleQuestion' => [[ // удалить вопрос
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
                'field' => 'questionId',
                'label' => 'Идентификатор вопроса',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'patchModuleQuestion' => [[ // изменить формулировку вопроса
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
                'field' => 'questionId',
                'label' => 'Идентификатор вопроса',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'questionText',
                'label' => 'Формулировка вопроса',
                'rules' => 'required',
                'type' => 'string'
        ]],
        // позиция вопроса 5
        'postQuestionPositionUp' => [[ // поднять вопрос в группе вопросов
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
                'field' => 'questionId',
                'label' => 'Идентификатор вопроса',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'parentQuestionId',
                'label' => 'Идентификатор вопроса, определяющего группу вопросов',
                'rules' => 'required',
                'type' => 'string'
        ]],
        // зависимости 9
        'putQuestionDependency' => [[ // добавить зависимость
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
                'field' => 'questionId',
                'label' => 'Идентификатор вопроса',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'answerVariantId',
                'label' => 'Идентификатор варианта ответа, от которого зависит вопрос',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'parentQuestionId',
                'label' => 'Идентификатор вопроса, от которого зависит вопрос',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'deleteQuestionDependency' => [[ // удалить зависимость
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
                'field' => 'questionId',
                'label' => 'Идентификатор вопроса',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'answerVariantId',
                'label' => 'Идентификатор варианта ответа, от которого зависит вопрос',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'parentQuestionId',
                'label' => 'Идентификатор вопроса, от которого зависит вопрос',
                'rules' => 'required',
                'type' => 'int'
        ]],
        // удалённые вопросы 10
        'getQuestions2restore' => [[ // получить ранее удалённые вопросы
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
        'putQuestion2restore' => [[ // восстановить вопрос
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
                'field' => 'questionId',
                'label' => 'Идентификатор вопроса',
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
     * Получить вопросы
     *
     */
    function getModuleQuestions() {
        $data = $this->ProcessInputData('getModuleQuestions', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $questions = $this->helper->getRequest($URI, $params);
            if (count($questions) == 0) {
                $questions = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($questions)->ReturnData();
    }


    /**
     * Изменить вопрос
     *
     */
    function patchModuleQuestion() {
        $data = $this->ProcessInputData('patchModuleQuestion', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "questionText": "'.$question->questionText.'",
                "questionMulti": '.$question->questionMulti.'
            }';
            $result = $this->helper->putRequest($params, 'PATCH');
            if (empty($result)) {
                $result = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($result)->ReturnData();
        return true;
    }


    /**
     * Создать вопрос
     */
    public function postModuleQuestion()
    {
        $data = $this->ProcessInputData('postModuleQuestion', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "questionText": "'.$this->getQuestionText($data).'"
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
     * Удалить вопрос
     */
    public function deleteModuleQuestion()
    {
        $data = $this->ProcessInputData('deleteModuleQuestion', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}";
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
     * Поднять вопрос
     */
    public function postQuestionPositionUp()
    {
        $data = $this->ProcessInputData('postQuestionPositionUp', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/up";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "parentQuestionId": '.$this->getParentQuestionId($data).'
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
     * Получить удалённые вопросы (для восстановления)
     */
    public function getQuestions2restore()
    {
        $data = $this->ProcessInputData('getQuestions2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions2restore";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $questions = $this->helper->getRequest($URI, $params);
            if (count($questions) == 0) $questions = ['result' => 'empty'];
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($questions)->ReturnData();
    }


    /**
     * Восстановить вопрос
     *
     */
    function putQuestion2restore() {
        $data = $this->ProcessInputData('putQuestion2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions2restore/{$question->questionId}";
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
     * Добавить зависимость
     *
     */
    function putQuestionDependency() {
        $data = $this->ProcessInputData('putQuestionDependency', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariantId = $this->getAnswerVariantId($data);
            $parentQuestionId = $this->getParentQuestionId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/questionDependencies/{$answerVariantId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "parentQuestionId": '.$parentQuestionId.'
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
     * Удалить зависимость
     *
     */
    function deleteQuestionDependency() {
        $data = $this->ProcessInputData('deleteQuestionDependency', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariantId = $this->getAnswerVariantId($data);
            $parentQuestionId = $this->getParentQuestionId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/questionDependencies/{$answerVariantId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "parentQuestionId": '.$parentQuestionId.'
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
     * Получить из параметров запроса questionId, questionText, questionMulti
     * @throws Exception
     */
    private function getQuestion($data)//: object
    {
        if (
            (!isset($data['questionId']))
            || (filter_var($data['questionId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Could not get module');
        }
        $questionText = '';
        if (!empty($data['questionText'])) {
            $questionText = $data['questionText'];
        }
        $questionMulti = 'null';
        if (!empty($data['questionMulti'])) {
            $questionMulti = intval($data['questionMulti']);
        }
        return (object) [
            'questionId' => intval($data['questionId']),
            'questionText' => $questionText,
            'questionMulti' => $questionMulti
        ];
    }


    /**
     * Получить из параметров запроса questionText отдельно
     * @throws Exception
     */
    private function getQuestionText($data)//: string
    {
        if (empty($data['questionText'])) {
            throw new Exception('Could not get question text');
        }
        return $data['questionText'];
    }


    /**
     * Получить из параметров запроса answerVariantId отдельно
     * @throws Exception
     */
    private function getAnswerVariantId($data)//: int
    {
        if (
            (!isset($data['answerVariantId']))
            || (filter_var($data['answerVariantId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get answer variant id');
        }
        return intval($data['answerVariantId']);
    }


    /**
     * Получить из параметров запроса $_REQUEST['parentQuestionId'] отдельно
     *
     * Может быть либо root - вопрос корневой, нет родительского вопроса
     * Либо должно быть число
     * @throws Exception
     * @return 'null'
     * @return int
     */
    private function getParentQuestionId($data) /*: int*/ {
        if (!isset($data['parentQuestionId'])) {
            throw new Exception('Failed to get parentQuestionId: not set');
        }
        if ($data['parentQuestionId'] === 'root') {
            return 'null';
        } else if (filter_var($data['parentQuestionId'], FILTER_VALIDATE_INT) === false) {
            throw new Exception('Failed to get parentQuestionId: not int');
        } else {
            return filter_var($data['parentQuestionId'], FILTER_VALIDATE_INT);
        }
    }
}
