<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonPrivilegeWOW - контроллер для выполенния операций с регистром ВОВ
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Абахри Самир
 * @version      август 2013
 */


class PersonPrivilegeWOW extends swController {

    /**
     * Описание правил для входящих параметров
     * @var array
     */
    var $inputRules = array(
        'loadPersonPrivilegeWOWEditForm'  => array(
            array(
                'field' => 'PersonPrivilegeWOW_id',
                'label' => 'Идентификатор в регистре',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'PrivilegeTypeWOW_id',
                'label' => 'Идентификатор льготы',
                'rules' => '',
                'type' => 'id'
            )
        ),
        'deletePersonPrivilegeWOW'  => array(
            array(
                'field' => 'PersonPrivilegeWOW_id',
                'label' => 'Идентификатор в регистре',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadStreamPersonPrivilegeWOW' => array(
            array(
                'field' => 'begDate',
                'label' => 'Дата начала',
                'rules' => '',
                'type' => 'date'
            ),
            array(
                'field' => 'begTime',
                'label' => 'Время начала',
                'rules' => '',
                'type' => 'string'
            )
        ),
        'savePersonPrivilegeWOW' => array(
            array(
                'field' => 'PersonPrivilegeWOW_id',
                'label' => 'Идентификатор в регистре',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'PersonPrivilegeWOW_begDate',
                'label' => 'Дата включени я регистр',
                'rules' => 'required',
                'type' => 'date'
            ),
            array(
                'field' => 'PrivilegeTypeWOW_id',
                'label' => 'Вид льготы',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор человека',
                'rules' => 'required',
                'type' => 'id'
            )
        )

    );

	/**
	 * Description
	 */
    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->model("PersonPrivilegeWOW_model", "dbmodel");
    }

    /**
     * Сохранение формы "Регистр ВОВ: Добавление"
     */
    function savePersonPrivilegeWOW()
    {
        $data = $this->ProcessInputData('savePersonPrivilegeWOW',true);
        if ($data === false) {return false;}

        $info = $this->dbmodel->getPersonData($data);
        $errors = array();
        if ( is_array($info) && count($info) > 0 ) {
                $response = $this->dbmodel->savePersonPrivilegeWOW($data);
                $this->ProcessModelSave($response, true, 'При сохранении данных произошла ошибка запроса к БД.')->ReturnData();
        }
        else {
            echo(json_encode(array('success'=>false, 'Error_Msg'=>toUTF('Не удалось получить данные о человеке'))));
        }
    }

	/**
	 * Получение данных для формы "Регистр ВОВ: Добавление"
	 */
    function loadPersonPrivilegeWOWEditForm()
    {
        $data = $this->ProcessInputData('loadPersonPrivilegeWOWEditForm',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->loadPersonPrivilegeWOWEditForm($data);
        $this->ProcessModelList($response,true,true)->ReturnData();

        return true;
    }

    /**
     *  Получение списка рецептов для потокового ввода
     *  Входящие данные: $_POST['begDate'],
     *                   $_POST['begTime']
     *  На выходе: JSON-строка
     *  Используется: форма потокового ввода
     */
    function loadStreamPersonPrivilegeWOW() {
        $data = $this->ProcessInputData('loadStreamPersonPrivilegeWOW', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadStreamPersonPrivilegeWOW($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Удаление человека из регистра по дополнительной диспансеризации
     * @return boolean
     */
    function deletePersonPrivilegeWOW() {
        $data = $this->ProcessInputData('deletePersonPrivilegeWOW', true);
        if ( $data === false ) { return false; }

        $response = $this->dbmodel->deletePersonPrivilegeWOW($data);
        $this->ProcessModelSave($response, true, 'Ошибка удаления')->ReturnData();
    }
}

?>