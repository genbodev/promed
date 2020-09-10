<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPresctTreat - контроллер API для работы с назначениями лекарственных средств
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			29.12.2016
 *
 * @property EvnPrescrTreat_model $dbmodel
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnPrescrTreat extends SwREST_Controller{
	protected $inputRules = array(
		'getEvnPrescrTreat' => array(
			array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreat_id', 'label' => 'Идентификатор назначения лекарственного средства', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор посещения/движения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_rid', 'label' => 'Идентификатор ТАП/КВС', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnClass_id', 'label' => 'Класс события', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата назначения', 'rules' => '', 'type' => 'date'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
		),
		'createEvnPrescrTreat' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор посещения/движения', 'rules' => 'required', 'type' => 'id'),
			//array('field' => 'Evn_rid', 'label' => 'Идентификатор ТАП/КВС', 'rules' => 'required', 'type' => 'id'),
			//array('field' => 'EvnClass_id', 'label' => 'Класс события', 'rules' => '', 'type' => 'id'),
			//array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата назначения', 'rules' => 'required', 'type' => 'date'),
			//array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			//array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_IsCito', 'label' => 'Признак Cito', 'rules' => 'required', 'type' => 'api_flag'),
			//array('field' => 'PrescriptionStatusType_id', 'label' => 'Статус назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescr_Descr', 'label' => 'Комментарий к назначению', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescr_isExec', 'label' => 'Признак выполнения назначения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'EvnPrescrTreat_PrescrCount', 'label' => 'Количество приемов в сутки', 'rules' => 'required', 'type' => 'int'),
			//array('field' => 'CourseType_id', 'label' => 'Тип курса лечения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnCourse_Duration', 'label' => 'Продолжительность приема', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DurationType_id', 'label' => 'Тип продолжительности приема', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnCourse_ContReception', 'label' => 'Продолжительности непрерывного приема', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DurationType_recid', 'label' => 'Тип продолжительности непрерывного приема', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnCourse_Interval', 'label' => 'Продолжительность перерыва', 'rules' => '', 'type' => 'id'),
			array('field' => 'DurationType_intid', 'label' => 'Тип продолжительности перерыва', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PrescriptionIntroType_id', 'label' => 'Способ применения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PerformanceType_id', 'label' => 'Тип исполнения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Медикмент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Торговое наименование', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatDrug_Kolvo', 'label' => 'Доза на один прием', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'GoodsUnit_id', 'label' => 'Единица измерения дозы на один прием', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatDrug_KolvoEd', 'label' => 'Кол-во лекарственного средства на один прием', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GoodsUnit_sid', 'label' => 'Единица измерения лекарственного средства', 'rules' => 'required', 'type' => 'id'),
			//array('field' => 'EvnPrescrTreatDrug_DoseDay', 'label' => 'Курсовая доза', 'rules' => '', 'type' => 'int'),
			//array('field' => 'EvnCourseTreatDrug_Kolvo', 'label' => 'Доза на один прием', 'rules' => 'required', 'type' => 'int'),
			//array('field' => 'EvnCourseTreatDrug_KolvoEd', 'label' => 'Кол-во лекарственного средства на один прием', 'rules' => 'required', 'type' => 'int'),
			//array('field' => 'EvnCourseTreatDrug_PrescrDose', 'label' => 'Курсовая доза', 'rules' => '', 'type' => 'int'),
		),
		'updateEvnPrescrTreat' => array(
			//array('field' => 'EvnPrescr_id', 'label' => 'Идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreat_id', 'label' => 'Идентфикатор назначения лекарственных средств', 'rules' => '', 'type' => 'id'),
			//array('field' => 'Evn_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор посещения/движения', 'rules' => '', 'type' => 'id'),
			//array('field' => 'Evn_rid', 'label' => 'Идентификатор ТАП/КВС', 'rules' => '', 'type' => 'id'),
			//array('field' => 'EvnClass_id', 'label' => 'Класс события', 'rules' => '', 'type' => 'id'),
			//array('field' => 'Lpu_id', 'label' => 'МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата назначения', 'rules' => '', 'type' => 'date'),
			//array('field' => 'Person_id', 'label' => 'Идентификатор человека', 'rules' => '', 'type' => 'id'),
			//array('field' => 'PrescriptionType_id', 'label' => 'Тип назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_IsCito', 'label' => 'Признак Cito', 'rules' => '', 'type' => 'api_flag'),
			//array('field' => 'PrescriptionStatusType_id', 'label' => 'Статус назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_Descr', 'label' => 'Комментарий к назначению', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnPrescr_isExec', 'label' => 'Признак выполнения назначения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'EvnPrescrTreat_PrescrCount', 'label' => 'Количество приемов в сутки', 'rules' => '', 'type' => 'int'),
			//array('field' => 'CourseType_id', 'label' => 'Тип курса лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCourse_Duration', 'label' => 'Продолжительность приема', 'rules' => '', 'type' => 'int'),
			array('field' => 'DurationType_id', 'label' => 'Тип продолжительности приема', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCourse_ContReception', 'label' => 'Продолжительности непрерывного приема', 'rules' => '', 'type' => 'id'),
			array('field' => 'DurationType_recid', 'label' => 'Тип продолжительности непрерывного приема', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCourse_Interval', 'label' => 'Продолжительность перерыва', 'rules' => '', 'type' => 'id'),
			array('field' => 'DurationType_intid', 'label' => 'Тип продолжительности перерыва', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescriptionIntroType_id', 'label' => 'Способ применения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PerformanceType_id', 'label' => 'Тип исполнения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Медикмент', 'rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Торговое наименование', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatDrug_Kolvo', 'label' => 'Доза на один прием', 'rules' => '', 'type' => 'int'),
			array('field' => 'GoodsUnit_id', 'label' => 'Единица измерения дозы на один прием', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatDrug_KolvoEd', 'label' => 'Кол-во лекарственного средства на один прием', 'rules' => '', 'type' => 'id'),
			array('field' => 'GoodsUnit_sid', 'label' => 'Единица измерения лекарственного средства', 'rules' => '', 'type' => 'id'),
			//array('field' => 'EvnPrescrTreatDrug_DoseDay', 'label' => 'Курсовая доза', 'rules' => '', 'type' => 'int'),
			//array('field' => 'EvnCourseTreatDrug_Kolvo', 'label' => 'Доза на один прием', 'rules' => '', 'type' => 'int'),
			//array('field' => 'EvnCourseTreatDrug_KolvoEd', 'label' => 'Кол-во лекарственного средства на один прием', 'rules' => '', 'type' => 'int'),
			//array('field' => 'EvnCourseTreatDrug_PrescrDose', 'label' => 'Курсовая доза', 'rules' => '', 'type' => 'int'),
		),
	);

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnPrescrTreat_model', 'dbmodel');
		$this->load->model('EvnPrescr_model', 'epmodel');
	}

	/**
	 * Получение назначений лекарственных средств
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnPrescrTreat');

		$resp = $this->dbmodel->getEvnPrescrTreatForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(isset($resp[0]) && !empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание назначения лекарственных средств
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnPrescrTreat', null, true);

		$parentEvnInfo = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				E.Evn_id as Evn_pid,
				E.EvnClass_SysNick,
				E.PersonEvn_id,
				E.Server_id,
				E.Lpu_id,
				isnull(EVPL.MedPersonal_id, ES.MedPersonal_id) as MedPersonal_id,
				isnull(EVPL.LpuSection_id, ES.LpuSection_id) as LpuSection_id
			from
				v_Evn E with(nolock)
				left join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = E.Evn_id
				left join v_EvnSection ES with(nolock) on ES.EvnSection_id = E.Evn_id
			where
				E.Evn_id = :Evn_pid
		", $data);
		if (!is_array($parentEvnInfo)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		$data = array_merge($data, $parentEvnInfo);

		$GoodsUnit_Nick = "";
		if (!empty($data['GoodsUnit_id'])) {
			$GoodsUnit_Nick = $this->dbmodel->getFirstResultFromQuery("
				select top 1 GoodsUnit_Nick from v_GoodsUnit with(nolock) where GoodsUnit_id = :GoodsUnit_id
			", $data);
			if (!$GoodsUnit_Nick) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
		}

		$dd = $data['EvnPrescrTreat_PrescrCount']*$data['EvnPrescrTreatDrug_Kolvo'];
		$DoseDay = "{$dd} {$GoodsUnit_Nick}";

		$Duration = $data['EvnCourse_Duration'];
		switch ($data['DurationType_id']) {
			case 2: $Duration *= 7; break;
			case 3: $Duration *= 30; break;
		}

		$kd = $dd*$Duration;
		$PrescrDose = "{$kd} {$GoodsUnit_Nick}";

		$DCMInfo = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				DCMD.DrugComplexMnnDose_Name,
				DCMD.DrugComplexMnnDose_Mass
			from 
				rls.v_DrugComplexMnn DCM with(nolock)
				left join rls.v_DrugComplexMnnDose DCMD with(nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
			where
				DCM.DrugComplexMnn_id = :DrugComplexMnn_id
		", $data);
		if (!is_array($DCMInfo)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$EdUnitsList = $this->dbmodel->loadEdUnitsList();
		if (!is_array($EdUnitsList)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$ed = $DCMInfo['DrugComplexMnnDose_Name'];
		$Dose_Mass = $DCMInfo['DrugComplexMnnDose_Mass'];
		$EdUnits_id = null;

		if (preg_match('/^([\d\.]+)/', $DCMInfo['DrugComplexMnnDose_Name'], $matches)) {
			$kolvo = $matches[1];
			$ed = trim(str_replace($kolvo, '', $ed));

			foreach($EdUnitsList as $EdUnits) {
				if ($EdUnits['EdUnits_Name'] == $ed) {
					$EdUnits_id = $EdUnits['EdUnits_id'];
					break;
				}
			}
		}

		$DrugListData = array(
			array(
				'MethodInputDrug_id' => empty($data['Drug_id'])?1:2,
				'Drug_Name' => null,
				'Drug_id' => !empty($data['Drug_id'])?$data['Drug_id']:null,
				'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
				'DrugForm_Name' => 'Tabl.',
				'DrugForm_Nick' => 'табл.',
				'KolvoEd' => $data['EvnPrescrTreatDrug_KolvoEd'],
				'Kolvo' => $data['EvnPrescrTreatDrug_Kolvo'],
				'EdUnits_id' => $EdUnits_id,
				'EdUnits_Nick' => null,
				'GoodsUnit_id' => $data['GoodsUnit_id'],
				'GoodsUnit_Nick' => $GoodsUnit_Nick,
				'DrugComplexMnnDose_Mass' => $Dose_Mass,
				'DoseDay' => $DoseDay,
				'PrescrDose' => $PrescrDose,
				'GoodsUnit_sid' => $data['GoodsUnit_sid'],
				'status' => 'new',
				'id' => null,
				'FactCount' => 0,
			)
		);

		$params = array(
			'parentEvnClass_SysNick' => $data['EvnClass_SysNick'],
			'Lpu_id' => $data['Lpu_id'],
			'signature' => 0,
			'EvnCourseTreat_id' => null,
			'EvnCourseTreat_pid' => $data['Evn_pid'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Morbus_id' => null,
			'EvnCourseTreat_setDate' => $data['Evn_setDT'],
			'EvnCourseTreat_CountDay' => $data['EvnPrescrTreat_PrescrCount'],
			'EvnCourseTreat_Duration' => $data['EvnCourse_Duration'],
			'DurationType_id' => $data['DurationType_id'],
			'EvnCourseTreat_ContReception' => $data['EvnCourse_ContReception'],
			'DurationType_recid' => $data['DurationType_recid'],
			'EvnCourseTreat_Interval' => $data['EvnCourse_Interval'],
			'DurationType_intid' => $data['DurationType_intid'],
			'ResultDesease_id' => null,
			'PerformanceType_id' => $data['PerformanceType_id'],
			'PrescriptionIntroType_id' => $data['PrescriptionIntroType_id'],
			'PrescriptionTreatType_id' => null,
			'DrugListData' => $DrugListData,
			'EvnPrescrTreat_IsCito' => $data['EvnPrescr_IsCito'] == 2 ? 'on' : 'off',
			'EvnPrescrTreat_Descr' => $data['EvnPrescr_Descr'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
		);

		$resp = $this->dbmodel->doSaveEvnCourseTreat($params);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(isset($resp[0]) && !empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}

		$response = array(
			'EvnPrescr_id' => $resp[0]['EvnPrescrTreat_id0'],
			'EvnPrescrTreat_id' => $resp[0]['EvnPrescrTreat_id0'],
			'Evn_id' => $resp[0]['EvnPrescrTreat_id0'],
			'EvnCourse_id' => $resp[0]['EvnCourseTreat_id'],
			'EvnCourseTreat_id' => $resp[0]['EvnCourseTreat_id'],
		);

		if ($data['EvnPrescr_isExec'] == 2) {
			$this->epmodel->execEvnPrescr(array(
				'EvnPrescr_id' => $response['EvnPrescrTreat_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array($response)
		));
	}

	/**
	 * Измение назначения лекарственных средств
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnPrescrTreat', null, true);

		$data['EvnCourseTreat_id'] = $this->dbmodel->getFirstResultFromQuery("
			select top 1 EvnCourse_id from v_EvnPrescrTreat with(nolock) where EvnPrescrTreat_id = :EvnPrescrTreat_id
		", $data, true);
		if ($data['EvnCourseTreat_id'] === false) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($data['EvnCourseTreat_id'])) {
			$this->response(array(
				'error_msg' => 'Не найден курс лечения по идентификатору назначения',
				'error_code' => '6'
			));
		}

		$info = $this->dbmodel->loadEvnPrescrTreatInfoForAPI($data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		foreach($info as $key => $value) {
			if (!array_key_exists($key, $this->_put_args)) {
				$data[$key] = $value;
			}
		}

		if ($data['Evn_pid'] != $info['Evn_pid']) {
			$parentEvnInfo = $this->dbmodel->getFirstRowFromQuery("
				select top 1
					E.Evn_id as Evn_pid,
					E.EvnClass_SysNick,
					E.PersonEvn_id,
					E.Server_id,
					E.Lpu_id,
					isnull(EVPL.MedPersonal_id, ES.MedPersonal_id) as MedPersonal_id,
					isnull(EVPL.LpuSection_id, ES.LpuSection_id) as LpuSection_id
				from
					v_Evn E with(nolock)
					left join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = E.Evn_id
					left join v_EvnSection ES with(nolock) on ES.EvnSection_id = E.Evn_id
				where
					E.Evn_id = :Evn_pid
			", $data);
			if (!is_array($parentEvnInfo)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data = array_merge($data, $parentEvnInfo);
		}

		$GoodsUnit_Nick = "";
		if (!empty($data['GoodsUnit_id'])) {
			$GoodsUnit_Nick = $this->dbmodel->getFirstResultFromQuery("
				select top 1 GoodsUnit_Nick from v_GoodsUnit with(nolock) where GoodsUnit_id = :GoodsUnit_id
			", $data);
			if (!$GoodsUnit_Nick) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
		}

		$dd = $data['EvnPrescrTreat_PrescrCount']*$data['EvnPrescrTreatDrug_Kolvo'];
		$DoseDay = "{$dd} {$GoodsUnit_Nick}";

		$Duration = $data['EvnCourse_Duration'];
		switch ($data['DurationType_id']) {
			case 2: $Duration *= 7; break;
			case 3: $Duration *= 30; break;
		}

		$kd = $dd*$Duration;
		$PrescrDose = "{$kd} {$GoodsUnit_Nick}";

		$DCMInfo = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				DCMD.DrugComplexMnnDose_Name,
				DCMD.DrugComplexMnnDose_Mass
			from 
				rls.v_DrugComplexMnn DCM with(nolock)
				left join rls.v_DrugComplexMnnDose DCMD with(nolock) on DCMD.DrugComplexMnnDose_id = DCM.DrugComplexMnnDose_id
			where
				DCM.DrugComplexMnn_id = :DrugComplexMnn_id
		", $data);
		if (!is_array($DCMInfo)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$EdUnitsList = $this->dbmodel->loadEdUnitsList();
		if (!is_array($EdUnitsList)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$ed = $DCMInfo['DrugComplexMnnDose_Name'];
		$Dose_Mass = $DCMInfo['DrugComplexMnnDose_Mass'];
		$EdUnits_id = null;

		if (preg_match('/^([\d\.]+)/', $DCMInfo['DrugComplexMnnDose_Name'], $matches)) {
			$kolvo = $matches[1];
			$ed = trim(str_replace($kolvo, '', $ed));

			foreach($EdUnitsList as $EdUnits) {
				if ($EdUnits['EdUnits_Name'] == $ed) {
					$EdUnits_id = $EdUnits['EdUnits_id'];
					break;
				}
			}
		}

		$DrugListData = array(
			array(
				'MethodInputDrug_id' => empty($data['Drug_id'])?1:2,
				'Drug_Name' => null,
				'Drug_id' => !empty($data['Drug_id'])?$data['Drug_id']:null,
				'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
				'DrugForm_Name' => 'Tabl.',
				'DrugForm_Nick' => 'табл.',
				'KolvoEd' => $data['EvnPrescrTreatDrug_KolvoEd'],
				'Kolvo' => $data['EvnPrescrTreatDrug_Kolvo'],
				'EdUnits_id' => $EdUnits_id,
				'EdUnits_Nick' => null,
				'GoodsUnit_id' => $data['GoodsUnit_id'],
				'GoodsUnit_Nick' => $GoodsUnit_Nick,
				'DrugComplexMnnDose_Mass' => $Dose_Mass,
				'DoseDay' => $DoseDay,
				'PrescrDose' => $PrescrDose,
				'GoodsUnit_sid' => $data['GoodsUnit_sid'],
				'status' => 'update',
				'id' => $data['EvnCourseTreatDrug_id'],
				'EvnPrescrTreat_id' => $data['EvnPrescrTreat_id'],
				'FactCount' => 0,
			)
		);

		$params = array(
			'parentEvnClass_SysNick' => $data['EvnClass_SysNick'],
			'Lpu_id' => $data['Lpu_id'],
			'signature' => 0,
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'EvnCourseTreat_pid' => $data['Evn_pid'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Morbus_id' => $data['Morbus_id'],
			'EvnCourseTreat_setDate' => $data['Evn_setDT'],
			'EvnCourseTreat_CountDay' => $data['EvnPrescrTreat_PrescrCount'],
			'EvnCourseTreat_Duration' => $data['EvnCourse_Duration'],
			'DurationType_id' => $data['DurationType_id'],
			'EvnCourseTreat_ContReception' => $data['EvnCourse_ContReception'],
			'DurationType_recid' => $data['DurationType_recid'],
			'EvnCourseTreat_Interval' => $data['EvnCourse_Interval'],
			'DurationType_intid' => $data['DurationType_intid'],
			'ResultDesease_id' => null,
			'PerformanceType_id' => $data['PerformanceType_id'],
			'PrescriptionIntroType_id' => $data['PrescriptionIntroType_id'],
			'PrescriptionTreatType_id' => $data['PrescriptionTreatType_id'],
			'DrugListData' => $DrugListData,
			'EvnPrescrTreat_IsCito' => $data['EvnPrescr_IsCito'] == 2 ? 'on' : 'off',
			'EvnPrescrTreat_Descr' => $data['EvnPrescr_Descr'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
		);

		$resp = $this->dbmodel->doSaveEvnCourseTreat($params);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if(isset($resp[0]) && !empty($resp[0]['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp[0]['Error_Msg'],
				'error_code' => '6'
			));
		}
		//print_r($resp);exit;

		$response = array(
			'EvnCourse_id' => $resp[0]['EvnCourseTreat_id'],
			'EvnCourseTreat_id' => $resp[0]['EvnCourseTreat_id'],
		);

		if (isset($resp[0]['EvnPrescrTreat_id0'])) {
			$response['EvnPrescr_id'] = $resp[0]['EvnPrescrTreat_id0'];
			$response['EvnPrescrTreat_id'] = $resp[0]['EvnPrescrTreat_id0'];
			$response['Evn_id'] = $resp[0]['EvnPrescrTreat_id0'];
		} else {
			$response['EvnPrescr_id'] = $data['EvnPrescrTreat_id'];
			$response['EvnPrescrTreat_id'] = $data['EvnPrescrTreat_id'];
			$response['Evn_id'] = $data['EvnPrescrTreat_id'];
		}

		if ($info['EvnPrescr_isExec'] != $data['EvnPrescr_isExec']) {
			if ($data['EvnPrescr_isExec'] == 2) {
				$this->epmodel->execEvnPrescr(array(
					'EvnPrescr_id' => $response['EvnPrescrTreat_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			} else {
				$this->epmodel->rollbackEvnPrescrExecution(array(
					'EvnPrescr_id' => $response['EvnPrescrTreat_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array($response)
		));
	}
}