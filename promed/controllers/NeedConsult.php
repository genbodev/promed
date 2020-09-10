<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* NeedConsult - контроллер для управления записями в 'Показания к консультации врача-специалиста'
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

class NeedConsult extends swController
{
	/**
	 * NeedConsult constructor.
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('NeedConsult_model', 'dbmodel');
		
		$this->inputRules = array(
			'saveNeedConsult' => array(
				array(
					'field' => 'NeedConsult_id',
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
					'field' => 'Post_id',
					'label' => 'Идентификатор врача-специалиста',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ConsultationType_id',
					'label' => 'Место проведения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadNeedConsultGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'NeedConsult_id',
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
	function saveNeedConsult() {
		$data = $this->ProcessInputData('saveNeedConsult', true);
		if ($data === false) { return false; }
	
		if (!$this->dbmodel->checkNeedConsultExists($data)) {
			$this->ReturnError('Указанное показание уже добавлено');
			return false;
		}
		
		$response = $this->dbmodel->saveNeedConsult($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
	}
	
	/**
	*  Получение списка 'Показания к консультации врача-специалиста'
	*  Входящие данные: EvnPLDispDop13_id
	*/	
	function loadNeedConsultGrid() {
		$data = $this->ProcessInputData('loadNeedConsultGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadNeedConsultGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>