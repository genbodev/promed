<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* EvnDiagDopDisp - контроллер для управления записями в 'Ранее известные имеющиеся заболевания' / 'Впервые выявленные заболевания'
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

class EvnDiagDopDisp extends swController
{
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDiagDopDisp_model', 'dbmodel');
		
		$this->inputRules = array(
			'saveEvnDiagDopDispAndRecomendation' => array(
				array(
					'field' => 'EvnDiagDopDisp_id',
					'label' => 'Идентификатор сохраняемого объекта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagDopDisp_pid',
					'label' => 'Идентификатор родительского объекта',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DeseaseDispType_id',
					'label' => 'Характер заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DispSurveilType_id',
					'label' => 'Диспансерное наблюдение',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'ConditMedCareType1_nid',
					'label' => 'Назначено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PlaceMedCareType1_nid',
					'label' => 'Место назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ConditMedCareType1_id',
					'label' => 'Проведено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PlaceMedCareType1_id',
					'label' => 'Место проведения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ConditMedCareType2_nid',
					'label' => 'Назначено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PlaceMedCareType2_nid',
					'label' => 'Место назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ConditMedCareType2_id',
					'label' => 'Проведено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PlaceMedCareType2_id',
					'label' => 'Место проведения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LackMedCareType2_id',
					'label' => 'Причина невыполнения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ConditMedCareType3_nid',
					'label' => 'Назначено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PlaceMedCareType3_nid',
					'label' => 'Место назначения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'ConditMedCareType3_id',
					'label' => 'Проведено',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PlaceMedCareType3_id',
					'label' => 'Место проведения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LackMedCareType3_id',
					'label' => 'Причина невыполнения лечения',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'HTMRecomType_id',
					'label' => 'ВМП',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				)
			),
			'delEvnDiagDopDisp' => array(
				array(
					'field' => 'EvnDiagDopDisp_id',
					'label' => 'Идентификатор удаляемого объекта',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnDiagDopDispEditForm' => array(
				array(
					'field' => 'EvnDiagDopDisp_id',
					'label' => 'Идентификатор',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkDiagDisp' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Diag_id',
					'label' => 'Идентификатор диагноза',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Date',
					'label' => 'Дата проверки диагноза',
					'rules' => '',
					'type' => 'date'
				)
			),
			'saveEvnDiagDopDisp' => array(
				array(
					'field' => 'EvnDiagDopDisp_id',
					'label' => 'Идентификатор сохраняемого объекта',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagDopDisp_setDate',
					'label' => 'Дата постановки',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDiagDopDisp_pid',
					'label' => 'Идентификатор родительского события',
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
					'field' => 'DiagSetClass_id',
					'label' => 'Тип',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'DeseaseDispType_id',
					'label' => 'Характер заболевания',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'PersonEvn_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Server_id',
					'label' => '',
					'rules' => '',
					'type' => 'int'
				)
			),
			'loadEvnDiagDopDispGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'DeseaseDispType_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagDopDisp_id',
					'label' => 'Идентификатор одного объекта',
					'rules' => '',
					'type' => 'id'
				)	
			),
			'loadEvnDiagDopDispSoputGrid' => array(
				array(
					'field' => 'EvnDiagDopDisp_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnDiagDopDispAndRecomendationGrid' => array(
				array(
					'field' => 'EvnPLDisp_id',
					'label' => 'Идентификатор талона по доп. диспансеризации',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDiagDopDisp_id',
					'label' => 'Идентификатор одного объекта',
					'rules' => '',
					'type' => 'id'
				)
			)
		);
	}
	
	/**
	 * Удаление диагноза
	 */
	function delEvnDiagDopDisp() {
		$data = $this->ProcessInputData('delEvnDiagDopDisp', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->delEvnDiagDopDisp($data);
		$this->ProcessModelSave($response, true, 'Ошибка при удалении')->ReturnData();
	}
	
	/**
	 * Проверка наличия карты диспансеризации при определенной группе диагноза
	 */
	function checkDiagDisp() {
		$data = $this->ProcessInputData('checkDiagDisp', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->checkDiagDisp($data);
		$this->ProcessModelSave($response, true, 'Ошибка при првоерке наличия карты диспансеризации по диагнозу')->ReturnData();
	}

	/**
	 * Получение данных формы
	 */
	function loadEvnDiagDopDispEditForm() {
		$data = $this->ProcessInputData('loadEvnDiagDopDispEditForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDiagDopDispEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Сохранения 'Ранее известные имеющиеся заболевания' / 'Впервые выявленные заболевания'
	*/	
	function saveEvnDiagDopDisp() {
		$data = $this->ProcessInputData('saveEvnDiagDopDisp', true);
		if ($data === false) { return false; }
	
		if (!$this->dbmodel->checkEvnDiagDopDispExists($data)) {
			$this->ReturnError('Указанное заболевание уже добавлено');
			return false;
		}
		
		$response = $this->dbmodel->saveEvnDiagDopDisp($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
	}
	
	/**
	*  Получение списка 'Состояние здоровья до проведения диспансеризации / профосмотра'
	*  Входящие данные: EvnPLDisp_id
	*/	
	function loadEvnDiagDopDispAndRecomendationGrid() {
		$data = $this->ProcessInputData('loadEvnDiagDopDispAndRecomendationGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnDiagDopDispAndRecomendationGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	/**
	*  Сохранение 'Состояние здоровья до проведения диспансеризации / профосмотра'
	*/	
	function saveEvnDiagDopDispAndRecomendation() {
		$data = $this->ProcessInputData('saveEvnDiagDopDispAndRecomendation', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->saveEvnDiagDopDispAndRecomendation($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении')->ReturnData();
	}
	
	/**
	*  Получение списка 'Ранее известные имеющиеся заболевания' / 'Впервые выявленные заболевания'
	*  Входящие данные: EvnPLDisp_id
	*/	
	function loadEvnDiagDopDispGrid() {
		$data = $this->ProcessInputData('loadEvnDiagDopDispGrid', true);
		if ($data === false) { return false; }
	
		$response = $this->dbmodel->loadEvnDiagDopDispGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}

	/**
	*  Получение списка 'Сопутствующие заболевания'
	*/
	function loadEvnDiagDopDispSoputGrid() {
		$data = $this->ProcessInputData('loadEvnDiagDopDispSoputGrid', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDiagDopDispSoputGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
}
?>