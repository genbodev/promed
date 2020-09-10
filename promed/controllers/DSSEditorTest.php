<?php
/**
 * Контроллер анализов/исследований
 *
 * Контроллер редактора опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
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
 * @since        28.05.2018
 * @version      7.12.2018
 *
 */
class DSSEditorTest extends swController
{
    public $inputRules = [
        'getTests' => [ // получить анализы и исследования
            [
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
            ]
        ],
        'postTest' => [ // добавить анализ/исследование
            [
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
            ],
            [
                'field' => 'testName',
                'label' => 'Название',
                'rules' => 'required',
                'type' => 'string'
            ]
        ],
        'deleteTest' => [ // удалить анализ/исследование
            [
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
            ],
            [
                'field' => 'testId',
                'label' => 'Идентификатор анализа/исследования',
                'rules' => 'required',
                'type' => 'int'
            ]
        ],
        'patchTest' => [ // переименовать анализ/исследование
            [
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
            ],
            [
                'field' => 'testId',
                'label' => 'Идентификатор анализа/исследования',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'testName',
                'label' => 'Название',
                'rules' => 'required',
                'type' => 'string'
            ]
        ],
        // удалённые анализы исследования 12
        'getTests2restore' => [ // получить ранее удалённые анализы и исследования
            [
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
            ]
        ],
        'putTest2restore' => [ // восстановить анализ/исследование
            [
                'field' => 'moduleId',
                'label' => 'Идентификатор модуля',
                'rules' => 'required',
                'type' => 'int'
            ],
            [
                'field' => 'moduleName',
                'label' => 'Название модуля',
                'rules' => 'required',
                'type' => 'string'
            ],
            [
                'field' => 'testId',
                'label' => 'Идентификатор анализа/исследования',
                'rules' => 'required',
                'type' => 'int'
            ]
        ]
    ];


    /**
     *  Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        require_once 'DSSHelper.php';
        $this->helper = new DSSHelper('editor');
    }


    /**
     * Получить анализы/исследования
     */
    public function getTests()
    {
        $data = $this->ProcessInputData('getTests', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/tests";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLastName": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'"
            }';
            $tests = $this->helper->getRequest($URI, $params);
            if (count($tests) == 0) $tests = ['result' => 'empty'];
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($tests)->ReturnData();
        return true;
    }


    /**
     * Создать анализ/исследование
     */
    public function postTest()
    {
        $data = $this->ProcessInputData('postTest', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $testName = $this->getTestName($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/tests";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLastName": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "testName": "'.$testName.'"
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
     */
    public function patchTest()
    {
        $data = $this->ProcessInputData('patchTest', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $test = $this->getTest($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/tests/{$test->testId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLastName": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "testName": "'.$test->testName.'"
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
     * Удалить анализ/исследование
     */
    public function deleteTest()
    {
        $data = $this->ProcessInputData('deleteTest', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $testId = $this->getTestId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/tests/{$testId}";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLastName": "'.$user->pmuser_surname.'",
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
     * Получить удалённые анализы/исследования (для восстановления)
     */
    public function getTests2restore()
    {
        $data = $this->ProcessInputData('getTests2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/tests2restore";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLastName": "'.$user->pmuser_surname.'",
                "moduleName": "'.$module->moduleName.'",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $tests = $this->helper->getRequest($URI, $params);
            if (count($tests) == 0) $tests = ['result' => 'empty'];
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList($tests)->ReturnData();
        return true;
    }


    /**
     * Восстановить анализ/исследование
     */
    public function putTest2restore()
    {
        $data = $this->ProcessInputData('putTest2restore', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $module = $this->getModule($data);
            $testId = $this->getTestId($data);
            $URI = "/v{$this->helper->apiVersion}/modules/{$module->moduleId}/tests2restore/{$testId}/restore";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLastName": "'.$user->pmuser_surname.'",
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
     * Получить из параметров запроса answerVariant_text отдельно
     * @throws Exception
     */
    private function getTestName($data)//: string
    {
        if (empty($data['testName'])) {
            throw new Exception('Failed to get test name');
        }
        return $data['testName'];
    }


    /**
     * Получить из параметров запроса answerVariant_text отдельно
     * @throws Exception
     */
    private function getTestId($data)//: int
    {
        if (
            (!isset($data['testId']))
            || (filter_var($data['testId'], FILTER_VALIDATE_INT) === false)
        ) {
            throw new Exception('Failed to get test id');
        }
        return intval($data['testId']);
    }


    /**
     * Получить из параметров запроса answerVariant
     * @throws Exception
     */
    private function getTest($data)//: object
    {
        if (
            (!isset($data['testId']))
            || (filter_var($data['testId'], FILTER_VALIDATE_INT) === false)
            || (empty($data['testName']))
        ) {
            throw new Exception('Failed to get test');
        }
        return (object) [
            'testId' => intval($data['testId']),
            'testName' => $data['testName']
        ];
    }
}
