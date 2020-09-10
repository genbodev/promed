<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* HeredityDiag - контроллер для управления записями в 'Наследственность по заболеваниям'
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

class HeredityDiag extends swController
{
	/**
	 * HeredityDiag constructor.
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('HeredityDiag_model', 'dbmodel');
		
		$this->inputRules = array(
			'saveHeredityDiag' => array(
				array(
					'field' => 'HeredityDiag_id',
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
					'field' => 'Diag_id',
					'label' => 'Идентификатор диагноза',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'HeredityType_id',
					'label' => 'Наследственность',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadHeredityDiagGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HeredityDiag_id',
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
	function saveHeredityDiag() {
		$data = $this->ProcessInputData('saveHeredityDiag', true);
		if ($data === false) { return false; }
	
		if (!$this->dbmodel->checkHeredityDiagExists($data)) {
			$this->ReturnError('Указанное заболевание уже добавлено');
			return false;
		}
		
		$response = $this->dbmodel->saveHeredityDiag($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
	}
	
	/**
	*  Получение списка 'Наследственность по заболеваниям'
	*  Входящие данные: EvnPLDispDop13_id
	*/	
	function loadHeredityDiagGrid() {
		$data = $this->ProcessInputData('loadHeredityDiagGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadHeredityDiagGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>