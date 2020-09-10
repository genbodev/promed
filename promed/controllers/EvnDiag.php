<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * @property EvnDiag_model $dbmodel
 * @property EvnDiagPLStom_model $EvnDiagPLStom_model
 */
class EvnDiag extends swController {
	public $inputRules = array(
		'deleteEvnDiag' => array(
			array('field' => 'class', 'label' => 'Класс диагноза', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id')
		),
		'loadPersonDiagPanel' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDiagSpecEditWindow'=>array(
			array(
					'field' => 'EvnDiagSpec_id',
					'label' => 'EvnDiagSpec_id',
					'rules' => 'required', 
					'type' => 'id'
			),
		),
		'delEvnDiagSpec'=>array(
			array(
					'field' => 'EvnDiagSpec_id',
					'label' => 'EvnDiagSpec_id',
					'rules' => 'required', 
					'type' => 'id'
			),
		),
		'saveDiagSpecEditWindow'=>array(
			array(
					'field' => 'Diag_id',
					'label' => 'Diag_id',
					'rules' => 'required', 
					'type' => 'id'
			),
			array(
					'field' => 'Server_id',
					'label' => 'Server_id',
					'rules' => 'required', 
					'type' => 'int'
			),
			array(
					'field' => 'EvnDiagSpec_id',
					'label' => 'EvnDiagSpec_id',
					'rules' => 'required', 
					'type' => 'id'
			),
			array(
					'field' => 'EvnDiagSpec_setDT',
					'label' => 'EvnDiagSpec_setDT',
					'rules' => 'trim', 
					'type' => 'date'
			),
			array(
					'field' => 'EvnDiagSpec_setDate',
					'label' => 'EvnDiagSpec_setDate',
					'rules' => 'required', 
					'type' => 'date'
			),
			array(
					'field' => 'Org_id',
					'label' => 'Org_id',
					'rules' => 'trim', 
					'type' => 'id'
			),
			array(
					'field' => 'MedStaffFact_id',
					'label' => 'MedStaffFact_id',
					'rules' => 'trim', 
					'type' => 'id'
			),
			array(
					'field' => 'Person_id',
					'label' => 'Person_id',
					'rules' => 'required', 
					'type' => 'id'
			),
			array(
					'field' => 'PersonEvn_id',
					'label' => 'PersonEvn_id диагноза',
					'rules' => 'required', 
					'type' => 'id'
			),
			array(
					'field' => 'EvnDiagSpec_LpuSectionProfile',
					'label' => 'Профиль',
					'rules' => 'trim', 
					'type' => 'string'
			),
			array(
					'field' => 'EvnDiagSpec_Lpu',
					'label' => 'EvnDiagSpec_Lpu',
					'rules' => 'trim', 
					'type' => 'string'
			),
			array(
					'field' => 'EvnDiagSpec_MedWorker',
					'label' => 'Врачь, которого добавили вручную',
					'rules' => 'trim', 
					'type' => 'string'
			),

			
		),
		'loadEvnDiagPLGrid' => array(
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnDiagPLStomSopEditForm' => array(
			array('field' => 'EvnDiagPLStomSop_id', 'label' => 'Идентификатор сопутствующего диагноза', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnDiagPLStomSopGrid' => array(
			array('field' => 'EvnDiagPLStomSop_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id')
		),
		'loadEvnDiagPSGrid' => array(
			array('field' => 'class', 'label' => 'Класс диагноза', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnDiagPS_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_rid', 'label' => 'Идентификатор корневого родительского события', 'rules' => '', 'type' => 'id'),
			array('field' => 'DiagSetClass', 'label' => 'Клинический диагноз', 'type' => 'int', 'rules' => '')
		),
		'saveEvnDiagPL' => array(
			array('field' => 'DeseaseType_id', 'label' => 'Характер заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPL_id', 'label' => 'Идентификатор диагноза', 'rules' => 'trim|required', 'type' => 'int'),
			array('field' => 'EvnVizitPL_id', 'label' => 'Идентификатор посещения пациентом поликлиники', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPL_setDate', 'label' => 'Дата установки диагноза', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Врач', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int')
		),
		'saveEvnDiagPLStomSop' => array(
			array('field' => 'DeseaseType_id', 'label' => 'Характер заболевания', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPLStomSop_id', 'label' => 'Идентификатор сопутствующего стоматологического диагноза', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnDiagPLStomSop_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'EvnDiagPLStomSop_setDate', 'label' => 'Дата установки диагноза', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'Tooth_Code', 'label' => 'Код зуба', 'rules' => '', 'type' => 'id'),
			array('field' => 'Tooth_id', 'label' => 'Зуб', 'rules' => '', 'type' => 'id'),
			array('field' => 'ToothSurfaceType_id_list', 'label' => 'Поверхность зуба', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'ignoreCheckMorbusOnko', 'label' => 'Признак игнорирования проверки перед удалением специфики', 'rules' => 'trim', 'type' => 'int', 'default' => 0 )
		),
		'saveEvnDiagPS' => array(
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiagSetPhase_id', 'label' => 'Фаза/стадия', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDiagPS_PhaseDescr', 'label' => 'Расшифровка', 'rules' => '', 'type' => 'string'),
			array('field' => 'DiagSetClass_id', 'label' => 'Вид диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DiagSetType_id', 'label' => 'Тип диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_setDate', 'label' => 'Дата установки диагноза', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'EvnDiagPS_setTime', 'label' => 'Время установки диагноза', 'rules' => 'trim', 'type' => 'time'),
			array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'HSNStage_id', 'label' => 'Стадия ХСН', 'type' => 'int'),
			array('field' => 'HSNFuncClass_id', 'label' => 'ФК стадии ХСН сервера', 'type' => 'int')
		),
		'loadEvnDiagForCopy' => array(
			array('field' => 'EvnDiagPS_rid', 'label' => 'Идентификатор корневого события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
		),
		'copyEvnDiagPS' => array(
			array('field' => 'class',
				'label' => 'Класс диагноза',
				'rules' => 'required', 
				'type' => 'string'),
			array('field' => 'EvnDiagPS_id', 'label' => 'Идентификатор диагноза', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDiagPS_pid', 'label' => 'Идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
		),
		'saveEvnDiagHSNDetails' => array(
			array(
				'field' => 'Evn_id',
				'label' => 'Идентификатор события',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'pmUser_id',
				'label' => 'Идентификатор пользователя',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Идентификатор основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HSNStage_id',
				'label' => 'Идентификатор стадии ХСН для основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'HSNFuncClass_id',
				'label' => 'Идентификатор функционального класса для основного диагноза',
				'rules' => '',
				'type' => 'id'
			)
		)
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDiag_model', 'dbmodel');
	}


	/**
	*  Удаление диагноза
	*  Входящие данные: $_POST['class'], $_POST['id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения пациентом поликлиники
	*/
	function deleteEvnDiag() {
		$data = array();
		$val  = array();

		// Получаем сессионные переменные
		$data = $this->ProcessInputData('deleteEvnDiag', true);

		if ( $data === false ) {
			return false;
		}

		if ($data['class'] == 'EvnDiagPS') {
			$this->load->model('EvnPL_model', 'EvnPL_model');
			$DiagData = $this->EvnPL_model->getDiagData(['EvnDiag_id' => $data['id']]);
		}
		$response = $this->dbmodel->deleteEvnDiag($data);

		if ( (is_array($response)) && (count($response) > 0) ) {
			if ( array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']) ) {
				if (!empty($DiagData)) {
					$params = $data;
					$params['EvnPS_id'] = $DiagData['EvnDiag_rid'];
					$params['source'] = 'EvnPS';
					$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
					$this->CVIRegistry_model->saveCVIEvent($params);
				}
				$val['success'] = true;
			}
			else {
				$val = $response[0];
				$val['success'] = false;
			}
		}
		else {
			$val['Error_Msg'] = 'При удалении диагноза возникли ошибки';
			$val['success'] = false;
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		$this->ReturnData($val);

		return true;
	}

	/**
	 * @type
	 * 
	 */
	function saveDiagSpecEditWindow(){
		$data = $this->ProcessInputData('saveDiagSpecEditWindow', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->saveDiagSpecEditWindow($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}
	
	/**
	 *
	 * @return type 
	 */
	function getDiagSpecEditWindow(){
		$data = $this->ProcessInputData('getDiagSpecEditWindow', true);
		$val = array();
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->getDiagSpecEditWindow($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}
	/**
	 * @type
	 * 
	 */
	function delEvnDiagSpec(){
		$data = $this->ProcessInputData('delEvnDiagSpec', true);
		$val = array();
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->delEvnDiagSpec($data);
		$val['success'] = true;
		$this->ReturnData($val);
		return true;
	}


	/**
	*  Получение списка сопутствующих диагнозов
	*  Входящие данные: $_POST['EvnVizitPL_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения пациентом поликлиники
	*/
	function loadEvnDiagPLGrid() {
		$data = $this->ProcessInputData('loadEvnDiagPLGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDiagPLGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение данных для формы редактирования сопутствующего стоматологического диагноза
	*  Входящие данные: $_POST['EvnDiagPLStomSop_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования сопутствующего стоматологического диагноза
	*/
	function loadEvnDiagPLStomSopEditForm() {
		$data = $this->ProcessInputData('loadEvnDiagPLStomSopEditForm', true);
        if ( $data === false ) { return false; }

		$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
		$response = $this->EvnDiagPLStom_model->loadEvnDiagPLStomSopEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение списка сопутствующих стоматологических диагнозов
	*  Входящие данные: $_POST['EvnDiagPLStomSop_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования посещения пациентом стоматологической поликлиники
	*/
	function loadEvnDiagPLStomSopGrid() {
		$data = $this->ProcessInputData('loadEvnDiagPLStomSopGrid', true);
		if ( $data === false ) { return false; }

		$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
		$response = $this->EvnDiagPLStom_model->loadEvnDiagPLStomSopGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Получение списка диагнозов для стационара
	*  Входящие данные: $_POST['class'], $_POST['EvnDiagPS_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования КВС
	*/
	function loadEvnDiagPSGrid() {
		$data = $this->ProcessInputData('loadEvnDiagPSGrid', true);
		if ( $data === false ) { return false; }

		if (getRegionNick() != 'perm') {
			$data['EvnDiagPS_rid'] = null;
		}

		$response = $this->dbmodel->loadEvnDiagPSGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	*  Сохранение сопутствующего диагноза
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования диагноза
	*/
	function saveEvnDiagPL() {
		$data = $this->ProcessInputData('saveEvnDiagPL', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnDiagPL($data);

		if (!empty($response) && !empty($response[0]) && !empty($response[0]['EvnDiagPL_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnDiagPL';
			$params['EvnDiag_id'] = $response[0]['EvnDiagPL_id'];
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}


	/**
	 *  Сохранение сопутствующего стоматологического диагноза
	 *  Входящие данные: ...
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования сопутствующего стоматологического диагноза
	 */
	function saveEvnDiagPLStomSop() {
		$data = $this->ProcessInputData('saveEvnDiagPLStomSop', true);
		if ( $data === false ) { return false; }

		$this->load->model('EvnDiagPLStom_model', 'EvnDiagPLStom_model');
		$response = $this->EvnDiagPLStom_model->saveEvnDiagPLStomSop($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}


	/**
	*  Сохранение диагноза для стационара
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования диагноза для стационара
	*/
	function saveEvnDiagPS() {
		$data = $this->ProcessInputData('saveEvnDiagPS', true);
		if ( $data === false ) { return false; }
		$response = $this->dbmodel->saveEvnDiagPS($data);

		if (!empty($response) && !empty($response[0]) && !empty($response[0]['EvnDiagPS_id']) && empty($response['Error_Msg'])) {
			$params = $data;
			$params['source'] = 'EvnDiagPS';
			$this->load->model('CVIRegistry_model', 'CVIRegistry_model');
			$this->CVIRegistry_model->saveCVIEvent($params);

			if ($data['EvnDiagPS_pid'])
			{
				$evnId = (!empty($data['EvnDiagPS_pid']) ? $data['EvnDiagPS_pid'] : $evnId);	
				$this->saveEvnDiagHSNDetails(
					array(
						'Evn_id' => $evnId,
						'pmUser_id' => $data['pmUser_id']
					));
			}
		}

		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Сохранение детализации диагноза ХСН по пациенту в рамках события
	 */
	function saveEvnDiagHSNDetails($params)
	{
		$data = $this->ProcessInputData('saveEvnDiagHSNDetails', false);

		if ($data === false)
			return false;

		$data['saveEvnPL'] = $params;
		$data['Evn_id']= $params['Evn_id'];
		$data['pmUser_id'] = $params['pmUser_id'];

		$this->load->model('Evn_model', 'Evn_model');
		$this->Evn_model->saveEvnDiagHSNDetails($data);
	}

	/**
	 * Загрузка списка доступных для копирования диагнозов
	 */
	function loadEvnDiagForCopy() {
		$data = $this->ProcessInputData('loadEvnDiagForCopy', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDiagForCopy($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Копирование диагноза
	 */
	function copyEvnDiagPS() {
		$data = $this->ProcessInputData('copyEvnDiagPS', true);
		if ( $data === false ) { return false; }

		if ($data['EvnDiagPS_pid'] == 0) { return false; }

		$response = $this->dbmodel->copyEvnDiagPS($data);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Получение списка уточненных диагнозов пациента для ЭМК
	 */
	function loadPersonDiagPanel() {
		$data = $this->ProcessInputData('loadPersonDiagPanel', true, true, true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadPersonDiagPanel($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}
}
