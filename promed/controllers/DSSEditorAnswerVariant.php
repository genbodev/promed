<?php
/**
 * Контроллер действий с вариантаи ответов
 *
 * Контроллер редактора опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 *
 * Получает (проверенные) идентификатр (и ФИО) медработника
 *   и перенаправляет запрос на сервер АПИ проекта
 *   Варианты ответов
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      All
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Yaroslav Mishlnov <ya.mishlanov@swan-it.ru>
 * @since        28.05.2018
 * @version      7.12.2018
 *
 */
class DSSEditorAnswerVariant extends swController
{
    public $inputRules = [
        // варианты ответа на вопрос 6
        'postAnswerVariant' => [[ // создать вариант ответа
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
                'field' => 'answerVariantText',
                'label' => 'Формулировка варианта ответа',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteAnswerVariant' => [[ // удалить вариант ответа
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
                'label' => 'Идентификатор варианта ответа',
                'rules' => 'required',
                'type' => 'int'
        ]],
        'patchAnswerVariant' => [[ // изменить формулировку варианта ответа
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
                'label' => 'Идентификатор варианта ответа',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'answerVariantText',
                'label' => 'Формулировка варианта ответа',
                'rules' => 'required',
                'type' => 'string'
            ], [
                'field' => 'answerVariantStatement',
                'label' => 'Формулировка варианта ответа',
                'rules' => 'required',
                'type' => 'string'
        ]],
        // позиция варианта ответа 7
        'postAnswerVariantPositionUp' => [[ // поднять вариант ответа
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
                'label' => 'Идентификатор варианта ответа',
                'rules' => 'required',
                'type' => 'int'
        ]],
        // удалённые варианты ответов 8
        'getAnswerVariants2restore' => [[ // получить ранее удалённые варианты ответа
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
        'putAnswerVariant2restore' => [[ // восстановить вариант ответа
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
                'label' => 'Идентификатор варианта ответа',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'answerVariantText',
                'label' => 'Текст варианта ответа',
                'rules' => 'required',
                'type' => 'string'
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
     * Создать вариант ответа
     *
     */
    function postAnswerVariant() {
        $data = $this->ProcessInputData('postAnswerVariant', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariantText = $this->getAnswerVariantText($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/answerVariants";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "answerVariantText": "'.$answerVariantText.'"
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
     * Изменить вариант ответа
     *
     */
    function patchAnswerVariant() {
        $data = $this->ProcessInputData('patchAnswerVariant', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariant = $this->getAnswerVariant($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/answerVariants/{$answerVariant->answerVariantId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "answerVariantText": "'.$answerVariant->answerVariantText.'",
                "answerVariantStatement": "'.$answerVariant->answerVariantStatement.'"
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
     * Удалить вариант ответа
     *
     */
    function deleteAnswerVariant() {
        $data = $this->ProcessInputData('deleteAnswerVariant', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariantId = $this->getAnswerVariantId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/answerVariants/{$answerVariantId}";
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
     * Поднять вариант ответа
     *
     */
    function postAnswerVariantPositionUp() {
        $data = $this->ProcessInputData('postAnswerVariantPositionUp', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariantId = $this->getAnswerVariantId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/answerVariants/{$answerVariantId}/up";
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
     * Получить удалённые варианты ответа (для восстановления)
     *
     */
    function getAnswerVariants2restore() {
        $data = $this->ProcessInputData('getAnswerVariants2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/answerVariants2restore";
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
     * Восстановить вариант ответа
     *
     */
    function putAnswerVariant2restore() {
        $data = $this->ProcessInputData('putAnswerVariant2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $question = $this->getQuestion($data);
            $answerVariantId = $this->getAnswerVariantId($data);
            $answerVariantText = $this->getAnswerVariantText($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/questions/{$question->questionId}/answerVariants2restore/{$answerVariantId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "answerVariantText": "'.$answerVariantText.'"
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
     * Получить из параметров запроса questionId, question_text, question_multi
     * @throws Exception
     */
    private function getQuestion($data)//: object
    {
        if (
            (!isset($data['questionId']))
            || (filter_var($data['questionId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Could not get question');
        }
        return (object) [ 'questionId' => intval($data['questionId'])  ];
    }


    /**
     * Получить из параметров запроса answerVariantText отдельно
     * @throws Exception
     */
    private function getAnswerVariantText($data)//: string
    {
        if (empty($data['answerVariantText'])) {
            throw new Exception('Could not get question text');
        }
        return $data['answerVariantText'];
    }


    /**
     * Получить из параметров запроса answerVariantText отдельно
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
     * Получить из параметров запроса answerVariant
     * @throws Exception
     */
    private function getAnswerVariant($data)//: object
    {
        if (
            (!isset($data['answerVariantId']))
            || (filter_var($data['answerVariantId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get answer variant');
        }

        if (empty($data['answerVariantText'])) {
            throw new Exception('Failed to get answer variant text');
        }

        if (!isset($data['answerVariantStatement'])) {
            throw new Exception('Failed to get answer variant statement');
        }

        return (object) [
            'answerVariantId' => intval($data['answerVariantId']),
            'answerVariantText' => $data['answerVariantText'],
            'answerVariantStatement' => $data['answerVariantStatement']
        ];
    }


    /**
     * Получить из параметров запроса answerVariantStatement
     * @throws Exception
     */
    private function getAnswerVariantStatement($data)//: str
    {
        if (empty($data['answerVariantStatement'])) {
            throw new Exception('Failed to get answer variant statement');
        }
        return $data['answerVariantStatement'];
    }
}
