<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* HeadMedSpec - методы для работы с главными внештатными специалистами при МЗ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2016 Swan Ltd.
* @author       Alexander Kurakin
* @version      05.2016
*/

class HeadMedSpec extends swController {

	/**
	 *  Конструктор
	 */
	function __construct() {
		parent::__construct();
        $this->load->database();
		$this->load->model('HeadMedSpec_model', 'dbmodel');
		$this->inputRules = array(
			'loadHeadMedSpecList' => array(
				array(
					'field' => 'Search_Day',
					'label' => 'На дату',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'HeadMedSpecType_Name',
					'label' => 'Специальность',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'start',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'limit',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				)
			),
			'saveHeadMedSpec' => array(
				array(
					'field' => 'HeadMedSpec_id',
					'label' => 'Идентификатор специалиста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HeadMedSpecType_id',
					'label' => 'Идентификатор специальности специалиста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HeadMedSpec_begDT',
					'label' => 'Дата начала',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'HeadMedSpec_endDT',
					'label' => 'Дата окончания',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'MedWorker_id',
					'label' => 'Идентификатор врача',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkHeadMedSpec' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор специалиста',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteHeadMedSpec' => array(
				array(
					'field' => 'HeadMedSpec_id',
					'label' => 'Идентификатор специалиста',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadHeadMedSpecTypeList' => array(
				array(
					'field' => 'HeadMedSpec_id',
					'label' => 'Идентификатор специалиста',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'action',
					'label' => 'Тип открытия формы',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Запрос',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'HeadMedSpecType_Name',
					'label' => 'Наименование специальности',
					'rules' => '',
					'type' => 'string'
				)
			),
			'saveHeadMedSpecType' => array(
				array(
					'field' => 'HeadMedSpecType_id',
					'label' => 'Идентификатор специальности',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HeadMedSpecType_Name',
					'label' => 'Наименование специальности',
					'rules' => 'required',
					'type' => 'string'
				),
				array(
					'field' => 'Post_id',
					'label' => 'Идентификатор профиля',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkHeadMedSpecType' => array(
				array(
					'field' => 'HeadMedSpecType_id',
					'label' => 'Идентификатор специальности',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteHeadMedSpecType' => array(
				array(
					'field' => 'HeadMedSpecType_id',
					'label' => 'Идентификатор специальности',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 * Получение списка специалистов 
	 */
	function loadHeadMedSpecList() {
        $data = $this->ProcessInputData('loadHeadMedSpecList', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadHeadMedSpecList($data);
		
		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as $row)
			{
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val['data'][] = $row;
			}
			$val['totalCount'] = $response['totalCount'];
		} else {
			$val['data'] = array();
			$val['totalCount'] = 0;
		}
		$this->ReturnData($val);

		return true;
	}

	/**
	 * Сохранение специалиста 
	 */
	function saveHeadMedSpec() {
        $data = $this->ProcessInputData('saveHeadMedSpec', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->saveHeadMedSpec($data);

		$this->ProcessModelSave($response,true,'Ошибка при сохранении данных')->ReturnData();

		return true;
	}

	/**
	 * Проверка наличия заявок, связанных со специалистом 
	 */
	function checkHeadMedSpec() {
        $data = $this->ProcessInputData('checkHeadMedSpec', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->checkHeadMedSpec($data);

		$this->ReturnData($response);

		return true;
	}

	/**
	 * Удаление специалиста 
	 */
	function deleteHeadMedSpec() {
        $data = $this->ProcessInputData('deleteHeadMedSpec', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->deleteHeadMedSpec($data);

		$this->ProcessModelSave($response,true,'Ошибка при удалении данных')->ReturnData();

		return true;
	}

	/**
	 * Получение списка специальностей 
	 */
	function loadHeadMedSpecTypeList() {
        $data = $this->ProcessInputData('loadHeadMedSpecTypeList', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->loadHeadMedSpecTypeList($data);
		
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
		
	}

	/**
	 * Сохранение специальности 
	 */
	function saveHeadMedSpecType() {
        $data = $this->ProcessInputData('saveHeadMedSpecType', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->saveHeadMedSpecType($data);

		$this->ProcessModelSave($response,true,'Ошибка при сохранении данных')->ReturnData();

		return true;
	}

	/**
	 * Проверка наличия специалистов, связанных со специалистью
	 */
	function checkHeadMedSpecType() {
        $data = $this->ProcessInputData('checkHeadMedSpecType', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->checkHeadMedSpecType($data);

		$this->ReturnData($response);

		return true;
	}

	/**
	 * Удаление специальности 
	 */
	function deleteHeadMedSpecType() {
        $data = $this->ProcessInputData('deleteHeadMedSpecType', true);
        if ($data === false) {return false;}

		$response = $this->dbmodel->deleteHeadMedSpecType($data);

		$this->ProcessModelSave($response,true,'Ошибка при удалении данных')->ReturnData();

		return true;
	}
}