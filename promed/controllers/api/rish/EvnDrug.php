<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDrug - контроллер API для работы с лекарственными назначениями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			06.12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnDrug extends SwREST_Controller{
	protected $inputRules = array(
		'getEvnDrugList' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnDrug' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор лекарственного назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата назначения', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
		),
		'createEvnDrug' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата назначения', 'rules' => 'required', 'type' => 'datetime'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Mol_id', 'label' => 'МОЛ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_Kolvo', 'label' => 'Кол-во (ед. уч.)', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'EvnDrug_KolvoEd', 'label' => 'Кол-во (ед. спис.)', 'rules' => 'required', 'type' => 'float'),
		),
		'updateEvnDrug' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор лекарственного назначения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата назначения', 'rules' => '', 'type' => 'datetime'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mol_id', 'label' => 'МОЛ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDrug_Kolvo', 'label' => 'Кол-во (ед. уч.)', 'rules' => '', 'type' => 'float'),
			array('field' => 'EvnDrug_KolvoEd', 'label' => 'Кол-во (ед. спис.)', 'rules' => '', 'type' => 'float'),
		),
		'writeoffMedicament' => array(
			array('field' => 'MSF_MedPersonal_id', 'label' => 'идентификатор сотрудника (MedPersonal_id)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_Price', 'label' => 'цена за единицу товара в партии (DocumentUcStr_Price)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_Sum', 'label' => 'цена за единицу товара в партии (DocumentUcStr_Price)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_id', 'label' => 'идентификатор назначения медикаментов (null)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_rid', 'label' => 'идентификатор события-потомка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'идентификатор человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocumentUc', 'label' => 'идентификатор документа учета', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocumentUcStr_id', 'label' => 'идентификатор строки документа учета (null)', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'идентификатор события человека', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDrug_pid', 'label' => 'идентификатор родительского события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_setDate', 'label' => 'дата выполнения назначения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnDrug_setTime', 'label' => 'время выполнения назначения', 'rules' => 'required', 'type' => 'time'),
			array('field' => 'LpuSection_id', 'label' => 'идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Storage_id', 'label' => 'идентификатор склада', 'rules' => '', 'type' => 'id'),
			array('field' => 'Mol_id', 'label' => 'идентификатор ответственного лица (МОЛ)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescrTreatDrug_id', 'label' => 'идентификатор медикамента в назначении', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnPrescrTreat_Fact', 'label' => 'количество приемов (=1)', 'rules' => '', 'type' => 'id'),
			array('field' => 'PrescrFactCountDiff', 'label' => 'количество приемов (=1)', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnPrescr_id', 'label' => 'идентификатор назначения', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnCourseTreatDrug_id', 'label' => 'идентификатор медикаментов в курсе лекарственного лечения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnCourse_id', 'label' => 'идентификатор курса лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugPrepFas_id', 'label' => 'идентификатор препарата', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Drug_id', 'label' => 'идентификатор медикамента', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocumentUcStr_oid', 'label' => 'идентификатор партии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'GoodsUnit_bid', 'label' => 'базовая единица учета поля количество', 'rules' => '', 'type' => 'id'),
			array('field' => 'GoodsPackCount_bCount', 'label' => 'количество единиц измерения товара в упаковке', 'rules' => '', 'type' => 'id'),
			array('field' => 'GoodsUnit_id', 'label' => 'единица измерения товара', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnDrug_Kolvo_Show', 'label' => 'EvnDrug_Kolvo', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_Kolvo', 'label' => 'количество медикаментов', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnDrug_KolvoEd', 'label' => 'количество единиц списания', 'rules' => 'required', 'type' => 'id'),
		)
	);

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('EvnDrug_model', 'dbmodel');
	}

	/**
	 * Получене данных лекарственных назначений
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnDrug');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		
		$resp = $this->dbmodel->getEvnDrugForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных лекарственного назначения
	 */
	function index_post() {
		$data = $this->ProcessInputData('createEvnDrug', null, true, true, true);

		$info = $this->dbmodel->getFirstRowFromQuery("
			select top 1
				E.Person_id,
				E.PersonEvn_id,
				E.Server_id,
				E.Lpu_id
			from v_Evn E with(nolock)
			where E.Evn_id = :Evn_pid
		", $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		
		if($info['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		$data = array_merge($data, $info);

		$this->load->model('Farmacy_model');

		$resp = $this->Farmacy_model->loadDrugList(array(
			'Drug_id' => $data['Drug_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Contragent_id' => $data['Contragent_id'],
			'date' => $data['Evn_setDT'],
			'mode' => 'expenditure'
		));
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (count($resp) == 0) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найден медикамент'
			));
		}
		$data['GoodsUnit_id'] = $resp[0]['GoodsUnit_id'];

		$resp = $this->Farmacy_model->loadDocumentUcStrList(array(
			'Drug_id' => $data['Drug_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'date' => $data['Evn_setDT'],
			'Contragent_id' => $data['Contragent_id'],
			'is_personal' => 1,
			'DocumentUc_id' => null,
			'DocumentUcStr_id' => null,
			'mode' => 'default'
		));
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$data['DocumentUc_id'] = null;
		$data['DocumentUcStr_oid'] = null;
		$data['EvnDrug_Price'] = null;
		foreach($resp as $item) {
			if ($item['DocumentUcStr_Ost'] >= $data['EvnDrug_Kolvo']) {
				$data['DocumentUcStr_oid'] = $item['DocumentUcStr_id'];
				$data['DocumentUc_id'] = $item['DocumentUc_id'];
				$data['EvnDrug_Price'] = $item['EvnDrug_Price'];
			}
		}

		if (empty($data['DocumentUcStr_oid'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдена партия с достаточным количестом медикаментов для списания'
			));
		}

		$params = array(
			'EvnDrug_id' => null,
			'EvnDrug_pid' => $data['Evn_pid'],
			'EvnDrug_setDate' => $data['Evn_setDT'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Drug_id' => $data['Drug_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'DocumentUc_id' => $data['DocumentUc_id'],
			'DocumentUcStr_oid' => $data['DocumentUcStr_oid'],
			'Mol_id' => $data['Mol_id'],
			'EvnDrug_Kolvo' => $data['EvnDrug_Kolvo'],
			//'EvnDrug_KolvoEd' => $data['EvnDrug_KolvoEd'],
			'EvnDrug_KolvoEd' => $data['EvnDrug_Kolvo'],
			'EvnDrug_Price' => $data['EvnDrug_Price'],
			'EvnDrug_Sum' => $data['EvnDrug_Price'] * $data['EvnDrug_Kolvo'],
			'GoodsUnit_id' => $data['GoodsUnit_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->saveEvnDrug($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('Evn_id' => $resp[0]['EvnDrug_id'])
			)
		));
	}

	/**
	 * Изменение данных лекарственного назначения
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnDrug', null, true, true, true);

		$info = $this->dbmodel->getEvnDrugInfoForAPI($data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		if($info['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}
		foreach($info as $key => $value) {
			if (!empty($info[$key]) && empty($data[$key])) {
				$data[$key] = $info[$key];
			}
		}
		$data['Server_id'] = $info['Server_id'];

		$this->load->model('Farmacy_model');

		if ($data['Drug_id'] != $info['Drug_id']) {
			$resp = $this->Farmacy_model->loadDrugList(array(
				'Drug_id' => $data['Drug_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'Contragent_id' => $data['Contragent_id'],
				'date' => $data['Evn_setDT'],
				'mode' => 'expenditure'
			));
			if (!is_array($info)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			if (count($resp) == 0) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не найден медикамент'
				));
			}
			$data['GoodsUnit_id'] = $resp[0]['GoodsUnit_id'];
		}

		if ($data['Drug_id'] != $info['Drug_id'] ||
			$data['LpuSection_id'] != $info['LpuSection_id'] ||
			strtotime($data['Evn_setDT']) != strtotime($info['Evn_setDT'])
		) {
			$resp = $this->Farmacy_model->loadDocumentUcStrList(array(
				'Drug_id' => $data['Drug_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'date' => $data['Evn_setDT'],
				'Contragent_id' => $data['Contragent_id'],
				'is_personal' => 1,
				'DocumentUc_id' => null,
				'DocumentUcStr_id' => null,
				'mode' => 'default'
			));
			if (!is_array($resp)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}

			$data['DocumentUc_id'] = null;
			$data['DocumentUcStr_oid'] = null;
			$data['EvnDrug_Price'] = null;
			foreach($resp as $item) {
				if (($item['DocumentUcStr_Ost'] + $info['EvnDrug_Kolvo']) >= $data['EvnDrug_Kolvo']) {
					$data['DocumentUcStr_oid'] = $item['DocumentUcStr_id'];
					$data['DocumentUc_id'] = $item['DocumentUc_id'];
					$data['EvnDrug_Price'] = $item['EvnDrug_Price'];
				}
			}
		}

		if (empty($data['DocumentUcStr_oid'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найдена партия с достаточным количестом медикаментов для списания'
			));
		}

		$params = array(
			'EvnDrug_id' => $data['Evn_id'],
			'EvnDrug_pid' => $data['Evn_pid'],
			'EvnDrug_setDate' => $data['Evn_setDT'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Drug_id' => $data['Drug_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'DocumentUc_id' => $data['DocumentUc_id'],
			'DocumentUcStr_oid' => $data['DocumentUcStr_oid'],
			'Mol_id' => $data['Mol_id'],
			'EvnDrug_Kolvo' => $data['EvnDrug_Kolvo'],
			'EvnDrug_KolvoEd' => $data['EvnDrug_KolvoEd'],
			'EvnDrug_Price' => $data['EvnDrug_Price'],
			'EvnDrug_Sum' => $data['EvnDrug_Price'] * $data['EvnDrug_KolvoEd'],
			'GoodsUnit_id' => $data['GoodsUnit_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->dbmodel->saveEvnDrug($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0
		));
	}

	/**
	 * Получене списка лекарственных назначений
	 */
	function EvnDrugList_get() {
		$data = $this->ProcessInputData('getEvnDrugList');

		$sp = getSessionParams();
		$data['Lpu_id'] = $sp['Lpu_id'];
		$resp = $this->dbmodel->getEvnDrugListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
	/**
	@OA\post(
			path="/api/rish/EvnDrug/mWriteoffMedicament",
			tags={"EvnDrug"},
			summary="Описание метода",

		@OA\Parameter(
			name="MSF_MedPersonal_id",
			in="query",
			description="идентификатор сотрудника (MedPersonal_id)",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_Price",
			in="query",
			description="цена за единицу товара в партии (DocumentUcStr_Price)",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_Sum",
			in="query",
			description="цена за единицу товара в партии (DocumentUcStr_Price)",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_id",
			in="query",
			description="идентификатор назначения медикаментов (null)",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_rid",
			in="query",
			description="идентификатор события-потомка",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="Person_id",
			in="query",
			description="идентификатор человека",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="DocumentUc",
			in="query",
			description="идентификатор документа учета",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="DocumentUcStr_id",
			in="query",
			description="идентификатор строки документа учета (null)",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="PersonEvn_id",
			in="query",
			description="идентификатор события человека",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="Server_id",
			in="query",
			description="идентификатор сервера",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_pid",
			in="query",
			description="идентификатор родительского события",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_setDate",
			in="query",
			description="дата выполнения назначения",
			required=true,
			@OA\Schema(type="string", format="date")
		)
	,
		@OA\Parameter(
			name="EvnDrug_setTime",
			in="query",
			description="время выполнения назначения",
			required=true,
			@OA\Schema(type="string")
		)
	,
		@OA\Parameter(
			name="LpuSection_id",
			in="query",
			description="идентификатор отделения",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="Storage_id",
			in="query",
			description="идентификатор склада",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="Mol_id",
			in="query",
			description="идентификатор ответственного лица (МОЛ)",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnPrescrTreatDrug_id",
			in="query",
			description="идентификатор медикамента в назначении",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnPrescrTreat_Fact",
			in="query",
			description="количество приемов (=1)",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="PrescrFactCountDiff",
			in="query",
			description="количество приемов (=1)",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnPrescr_id",
			in="query",
			description="идентификатор назначения",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnCourseTreatDrug_id",
			in="query",
			description="идентификатор медикаментов в курсе лекарственного лечения",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnCourse_id",
			in="query",
			description="идентификатор курса лечения",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="DrugPrepFas_id",
			in="query",
			description="идентификатор препарата",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="Drug_id",
			in="query",
			description="идентификатор медикамента",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="DocumentUcStr_oid",
			in="query",
			description="идентификатор партии",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="GoodsUnit_bid",
			in="query",
			description="базовая единица учета поля количество",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="GoodsPackCount_bCount",
			in="query",
			description="количество единиц измерения товара в упаковке",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="GoodsUnit_id",
			in="query",
			description="единица измерения товара",
			required=false,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_Kolvo_Show",
			in="query",
			description="EvnDrug_Kolvo",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_Kolvo",
			in="query",
			description="количество медикаментов",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,
		@OA\Parameter(
			name="EvnDrug_KolvoEd",
			in="query",
			description="количество единиц списания",
			required=true,
			@OA\Schema(type="integer", format="int64")
		)
	,

		@OA\Response(
			response="200",
			description="JSON response",
			@OA\JsonContent(
				type="object",

		@OA\Property(
			property="error_code",
			description="код ошибки",
			type="string",

		)
	,				 
		@OA\Property(
			property="data",
			description="Данные",
			type="array",

		@OA\Items(
			type="object",

		@OA\Property(
			property="EvnDrug_id",
			description="Событие, Идентификатор события",
			type="integer",

		)

		)

		)

			)
		)

		)
	 */
	function mWriteoffMedicament_post(){
		//Метод списания медикаментов
		$data = $this->ProcessInputData('writeoffMedicament', null, true);
		$this->load->helper('Reg_helper');
		
		if ( ! empty($data['Mol_id']) && ! empty($data['EvnDrug_setDate']))
		{
			$response = $this->dbmodel->MolIsAvailable($data['Mol_id'], $data['EvnDrug_setDate']);
			if( $response == false ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Срок действия МОЛ истек. Сохранение документа невозможно'
				));
				return false;
			}
		}
		if(!empty($data['DocumentUc'])) $data['DocumentUc_id'] = $data['DocumentUc'];  // по ТЗ хотят DocumentUc почему то
		
		$response = $this->dbmodel->saveEvnDrug($data);
		if (!is_array($response) || !isset($response[0])) {
			$this->response(array('error_code' => 6, 'error_msg' => "Ошибка при выполнении списания медикаментов"));
		}
		if (!empty($response[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $response[0]['Error_Msg']
			));
		}

		$this->response(array(
			'error_code' => 0,
			'data' => array(
				array('EvnDrug_id' => $response[0]['EvnDrug_id'])
			)
		));
	}
}