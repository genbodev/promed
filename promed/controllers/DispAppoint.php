<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* DispAppoint - контроллер для управления записями в 'Назначение'
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2016 Swan Ltd.
* @author			Dmitry Vlasenko
* @version			11.2016
*/

class DispAppoint extends swController
{
	/**
	 * DispAppoint constructor.
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('DispAppoint_model', 'dbmodel');
		
		$this->inputRules = array(
			'saveDispAppoint' => array(
				array(
					'field' => 'DispAppoint_id',
					'label' => 'Идентификатор сохраняемого объекта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DispAppointType_id',
					'label' => 'Назначение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedSpecOms_id',
					'label' => 'Специальность врача назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ExaminationType_id',
					'label' => 'Вид обследования',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionProfile_id',
					'label' => 'Профиль медицинской помощи',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSectionBedProfile_id',
					'label' => 'Профиль койки',
					'rules' => '',
					'type' => 'id'
				)
			),
			'loadDispAppointGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispAppoint_id',
					'label' => 'Идентификатор одного объекта',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
	}

	/**
	 * @return bool
	 */
	function saveDispAppoint() {
		$data = $this->ProcessInputData('saveDispAppoint', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->saveDispAppoint($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
	}
	
	/**
	*  Получение списка 'Назначения'
	*  Входящие данные: EvnPLDispDop13_id
	*/	
	function loadDispAppointGrid() {
		$data = $this->ProcessInputData('loadDispAppointGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadDispAppointGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>