<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * OrgServiceTerr - контроллер для выполнения операций с обслуживаемыми территориями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009-2013 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      14.10.2013
 */

class OrgServiceTerr extends swController 
{

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
			'saveOrgServiceTerr' => array(
				array(
					'field' => 'OrgServiceTerr_id',
					'label' => 'Идентификатор территории обслуживания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'KLCountry_id',
					'label' => 'Страна',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'KLRgn_id',
					'label' => 'Регион',
					'rules' => 'trim|required',
					'type' => 'id'
				),
				array(
					'field' => 'KLSubRgn_id',
					'label' => 'Район',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'KLCity_id',
					'label' => 'Город',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'KLTown_id',
					'label' => 'Населенный пункт',
					'rules' => 'trim',
					'type' => 'id'
				),
				array(
					'field' => 'KLAreaType_id',
					'label' => 'Тип местности',
					'rules' => 'trim',
					'type' => 'id'
				)
			),
			'loadOrgServiceTerrEditForm' => array(
				array(
					'field' => 'OrgServiceTerr_id',
					'label' => 'Идентификатор территории обслуживания',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadOrgServiceTerrGrid' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Организация',
					'rules' => '',
					'type' => 'id'
				)
			)
    );

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->load->database();
		$this->load->model('OrgServiceTerr_model', 'dbmodel');
	}
	
	/**
	 *  Функция сохранения формы редактирования территорий обслуживания
	 */
	function saveOrgServiceTerr() 
	{
		$data = $this->ProcessInputData('saveOrgServiceTerr', true);
		if ($data === false) { return false; }
		/*
		if (empty($data['KLCity_id']) && empty($data['KLTown_id'])) {
			$this->ReturnError('Необходимо заполнить город или населённый пункт');
			return false;
		}
		*/
		$response = $this->dbmodel->saveOrgServiceTerr($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении территории обслуживания')->ReturnData();
	}
	
	/**
	 *  Функция получения формы редактирования территорий обслуживания
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком расчётных квот.
	 */
	function loadOrgServiceTerrEditForm() 
	{
		$data = $this->ProcessInputData('loadOrgServiceTerrEditForm', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadOrgServiceTerrEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	 *  Функция получения списка территорий обслуживания
	 *  Входящие данные: сессия.
	 *  На выходе: JSON-строка со списком расчётных квот.
	 */
	function loadOrgServiceTerrGrid() 
	{
		$data = $this->ProcessInputData('loadOrgServiceTerrGrid', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadOrgServiceTerrGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

}

?>