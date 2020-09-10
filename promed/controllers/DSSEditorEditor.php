<?php
/**
 * Контроллер действий с редакторами опросников
 *
 * Редактор опросников
 *
 * Сбор структурированной медицинской информации
 *               и поддержка принятия решений
 *
 *
 * Получает (проверенные) идентификатр (и ФИО) медработника
 *   и перенаправляет запрос на сервер АПИ проекта
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
 * @version      16.04.2019
 *
 */
class DSSEditorEditor extends swController {

    public $inputRules = [
        'putDoctor' => [], // убедиться, что медработник есть в БД и получить его данные
        'getEditors' => [[ // получить список медработников, имеющих право создания модулей
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
        'putCreateModuleRight' => [[ // дать право создания модулей
                'field' => 'doctorId',
                'label' => 'Идентификатор медработника, которому нужно дать право создания модулей',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'doctorLogin',
                'label' => 'Фамилия медработника, которому нужно дать право создания модулей',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'deleteCreateModuleRight' => [[ // отозвать право создания модулей
                'field' => 'doctorId',
                'label' => 'Идентификатор медработника, которого нужно лишить права создания модулей',
                'rules' => 'required',
                'type' => 'int'
            ], [
                'field' => 'doctorLogin',
                'label' => 'Фамилия медработника, которому нужно дать право создания модулей',
                'rules' => 'required',
                'type' => 'string'
        ]],
        'getDoctorsByLogin' => [[ // получить список медработников с указанным логином
                'field' => 'doctorLogin',
                'label' => 'Фамилия медработника, которого нужно найти',
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
        $this->helper = new DSSHelper('editor', $this->config->item('DSS_API_URL'));
    }


    /**
     * Убедиться, что медработник есть в БД (добавить, если нет),
     * и получить его данные (в том числе, наличие или отсутствие права создания модулей)
     *
     */
    function putDoctor() {
        $data = $this->ProcessInputData('putDoctor', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $user = $this->helper->putUser($user);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $doctor = [
            'doctorId' => $user->pmuser_id,
            'doctorLogin' => $user->pmuser_surname,
            'doctorHasRight2CreateModules' => $user->doctorHasRight2CreateModules
        ];
        $this->ProcessModelList([$doctor])->ReturnData();
        return true;
    }


    /**
     * Получить список медработников, имеющих право создания модулей
     *
     */
    function getEditors() {
        $data = $this->ProcessInputData('getEditors', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/doctors";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "filter": "right2createModule",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $editors = $this->helper->getRequest($URI, $params);
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$editors])->ReturnData();
        return true;
    }


    /**
     * Получить список медработников с указанной фамилией (поиск по фамилии)
     *
     */
    function getDoctorsByLogin() {
        $data = $this->ProcessInputData('getDoctorsByLogin', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $wanted = $this->getWanted($data);
            $pagination = $this->helper->getPagination($data);
            $URI = "/v{$this->helper->apiVersion}/doctors";
            $params = '{
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
                "targetDoctorLogin": "'.$wanted->doctorLogin.'",
                "filter": "login",
                "offset": '.$pagination->offset.',
                "limit": '.$pagination->limit.'
            }';
            $doctors = $this->helper->getRequest($URI, $params);
            if (count($doctors) == 0) {
                $doctors = ['result' => 'empty'];
            }
        } catch (Exception $e) {
            $this->ProcessModelList(['error' => $e->getMessage()])->ReturnData();
            return false;
        }
        $this->ProcessModelList([$doctors])->ReturnData();
        return true;
    }


    /**
     * Дать право созлания модулей
     *
     */
    function putCreateModuleRight() {
        $data = $this->ProcessInputData('putCreateModuleRight', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $grantee = $this->getGrantee($data);
            $URI = "/v{$this->helper->apiVersion}/doctors/{$grantee->doctorId}/createModuleRight";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
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
     * Отозвать право создания модулей
     *
     */
    function deleteCreateModuleRight() {
        $data = $this->ProcessInputData('deleteCreateModuleRight', true);
        if (!is_array($data)) {
            throw new Exception('Failed to process input data');
        }
        try {
            $user = $this->helper->getUserFromData($data);
            $grantee = $this->getGrantee($data);
            $URI = "/v{$this->helper->apiVersion}/doctors/{$grantee->doctorId}/createModuleRight";
            $params = '{
                "URI": "'.$URI.'",
                "doctorId": '.$user->pmuser_id.',
                "doctorLogin": "'.$user->pmuser_surname.'",
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
     * Получить doctor_id - идентификатор медработника,
     *     которому изменяют права доступа, из параметров запроса
     *
     * @throws Exception
     */
    private function getGrantee($data)//: object
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
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


    /**
     * Получить pmUser_lastName - фамилия, медработника с которой нужно найти
     *
     * @throws Exception
     */
    private function getWanted($data)//: object
    {
        if (!is_array($data)) {
            throw new Exception('Parameter Data has wrong type');
        }
        if (empty($data['doctorLogin'])) {
            throw new Exception('Could not get wanted dector last name');
        }
        return (object) [ 'doctorLogin' => $data['doctorLogin']  ];
    }
}
