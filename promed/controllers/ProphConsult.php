<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* ProphConsult - контроллер для управления записями в 'Показания к углубленному профилактическому консультированию'
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			DLO
* @access			public
* @copyright		Copyright (c) 2013 Swan Ltd.
* @author			Dmitry Vlasenko
* @version			02.07.2013
*/

class ProphConsult extends swController
{
	/**
	 * ProphConsult constructor.
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('ProphConsult_model', 'dbmodel');
		
		$this->inputRules = array(
			'saveProphConsult' => array(
				array(
					'field' => 'ProphConsult_id',
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
					'field' => 'RiskFactorType_id',
					'label' => 'Фактор риска',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadProphConsultGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ProphConsult_id',
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
	function saveProphConsult() {
		$data = $this->ProcessInputData('saveProphConsult', true);
		if ($data === false) { return false; }
	
		if (!$this->dbmodel->checkProphConsultExists($data)) {
			$this->ReturnError('Указанное показание уже добавлено');
			return false;
		}
		
		$response = $this->dbmodel->saveProphConsult($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
	}
	
	/**
	*  Получение списка 'Показания к углубленному профилактическому консультированию'
	*  Входящие данные: EvnPLDispDop13_id
	*/	
	function loadProphConsultGrid() {
		$data = $this->ProcessInputData('loadProphConsultGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadProphConsultGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>