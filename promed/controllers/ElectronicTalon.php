<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* ElectronicTalon - контроллер для работы c талонами
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
* @package			Admin
* @access			public
* @copyright		Copyright (c) 2017 Swan Ltd.
*/

class ElectronicTalon extends swController {

    public $inputRules = array(
        'loadRedirectedTalonServices' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'currentElectronicService_id',
                'label' => 'Идентификатор текущего пункта обслуживания',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'getPrimaryElectronicService' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            )
        ),
        'loadLpuBuildingElectronicServices' => array(
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор ЛПУ',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuBuilding_id',
                'label' => 'Идентификатор подразделения',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'CurrentElectronicService_id',
                'label' => 'Текущий пункт обслуживания',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Номер талона',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'noLoad',
                'label' => 'Без загрузки',
                'rules' => '',
                'type' => 'boolean'
            )
        ),
        'redirectElectronicTalon' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'Lpu_id',
                'label' => 'Идентификатор ЛПУ',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'pmUser_id',
                'label' => 'Идентификатор пользователя',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор рабочего места врача',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'UslugaComplexMedService_id',
                'label' => 'Идентификатор услуги на службе',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedServiceType_SysNick',
                'label' => 'Краткое наименование типа службы',
                'rules' => '',
                'type' => 'string'
            ),
            array(
                'field' => 'EvnDirection_pid',
                'label' => 'Идентификатор текущего направления',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSectionProfile_id',
                'label' => 'Идентификатор профиля отделения',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'LpuSection_id',
                'label' => 'Идентификатор отделения',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'From_MedStaffFact_id',
                'label' => 'Идентификатор текущего места работы',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedPersonal_id',
                'label' => 'Идентификатор персонала',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedService_id',
                'label' => 'Идентификатор службы',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'redirectBack',
                'label' => 'Признак вовзрата талона на предыдущий ПО',
                'rules' => '',
                'type' => 'boolean'
            )
        ),
        'setElectronicTalonStatus' => array(
            array(
                'field' => 'ElectronicTalon_id',
                'label' => 'Идентификатор талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicService_id',
                'label' => 'Идентификатор пункта обслуживания',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'ElectronicTalonStatus_id',
                'label' => 'Идентификатор статуса талона',
                'rules' => 'required',
                'type' => 'id'
            ),
            array(
                'field' => 'cancelCallCount',
                'label' => 'Количество вызовов (до отмены пациента)',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор места работы врача',
                'rules' => '',
                'type' => 'int'
            ),
            array(
                'field' => 'Person_id',
                'label' => 'Идентификатор пациента',
                'rules' => '',
                'type' => 'int'
            ),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя АРМ',
				'rules' => '',
				'type' => 'int'
			),
            array(
                'field' => 'pmUser_did',
                'label' => 'Идентификатор пользователя записавшего пациента',
                'rules' => '',
                'type' => 'int'
            )
        ),
		'getElectronicTalonHistory' => array(
			array(
				'field' => 'ElectronicTalon_id',
				'label' => 'Идентификатор талона',
				'rules' => 'required',
				'type' => 'id'
			)
		)
    );


    /**
     * Конструктор
     */
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('ElectronicTalon_model', 'dbmodel');
    }

    /**
     * Подгрузка комбо с пунктами обслуживания для редиректа талона
     */
    function loadLpuBuildingElectronicServices() {
        $data = $this->ProcessInputData('loadLpuBuildingElectronicServices', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadLpuBuildingElectronicServices($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Подгрузка комбо с пунктами обслуживания для редиректа талона
     */
    function loadRedirectedTalonServices() {
        $data = $this->ProcessInputData('loadRedirectedTalonServices', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->loadRedirectedTalonServices($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Перенаправление талона
     */
    function redirectElectronicTalon() {
        $data = $this->ProcessInputData('redirectElectronicTalon', true);
        if ($data === false) { return false; }

        $response = $this->dbmodel->redirectElectronicTalon($data);
        $this->ProcessModelSave($response, true, 'Ошибка установки статуса электронного талона')->ReturnData();

        return true;
    }

    /**
     * Установка статуса электронного талона
     */
    function setElectronicTalonStatus() {
        $data = $this->ProcessInputData('setElectronicTalonStatus', false);
        if ($data === false) { return false; }

        $response = $this->dbmodel->setElectronicTalonStatus($data);
        $this->ProcessModelSave($response, true, 'Ошибка установки статуса электронного талона')->ReturnData();

        return true;
    }

    /**
     * Установка статуса электронного талона
     */
    function getPrimaryElectronicService() {
        $data = $this->ProcessInputData('getPrimaryElectronicService', false);
        if ($data === false) { return false; }

        $response = $this->dbmodel->getPrimaryElectronicService($data);
        $this->ProcessModelList($response, true, true)->ReturnData();
        return true;
    }

	/**
	 * История талона электронной очереди
	 */
	function getElectronicTalonHistory() {

		$data = $this->ProcessInputData('getElectronicTalonHistory');
		if ($data === false) { return false; }

		$response = $this->dbmodel->getElectronicTalonHistory($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
}