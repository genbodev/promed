<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * HomeVisit6E - контроллер для вызовов врачей на дом в ExtJS 6
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      20.09.2013
 */
 
class HomeVisit6E extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		
		$this->inputRules = array(
			'getHomeVisitList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата начала периода расписания',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Идентификатор места работы',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'endDate',
					'label' => 'Дата окончания периода расписания',
					'rules' => 'required',
					'type' => 'date'
				)
			)
		);

		$this->load->helper('Reg');

		$this->load->database();
		$this->load->model('HomeVisit6E_model', 'dbmodel');
    }

	/**
	 * Получение вызовов
	 */
	function getHomeVisitList() {
		$data = $this->ProcessInputData('getHomeVisitList',true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getHomeVisitList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}