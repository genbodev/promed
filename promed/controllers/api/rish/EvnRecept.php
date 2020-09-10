<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnRecept - контроллер API для работы с льготными рецептами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.12.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class EvnRecept extends SwREST_Controller{
	protected $inputRules = array(
		'getEvnReceptList' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
		),
		'getEvnRecept' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор рецепта', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата рецепта', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnRecept_Num', 'label' => 'Номер рецепта', 'rules' => '', 'type' => 'string'),
		),
		'createEvnRecept' => array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор случая лечения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ReceptForm_id', 'label' => 'Форма рецепта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ReceptType_id', 'label' => 'Тип рецепта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата рецерта', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'EvnRecept_Num', 'label' => 'Номер рецепта', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnRecept_Ser', 'label' => 'Серия рецепта', 'rules' => '', 'type' => 'string'),
			array('field' => 'ReceptValid_id', 'label' => 'Срок действия рецепта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Отделение', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ReceptFinance_id', 'label' => 'Тип финансирования', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ReceptDiscount_id', 'label' => 'Скидка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnRecept_is7Noz', 'label' => 'Признак "7 нозологий"', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrivilegeType_id', 'label' => 'Тип льготы', 'rules' => 'required', 'type' => 'int'),
			//array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Комплексное МНН', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnRecept_ExtempContents', 'label' => 'Состав медикамента', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnRecept_Kolvo', 'label' => 'Количество медикамента', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'EvnRecept_Signa', 'label' => 'Signa', 'rules' => '', 'type' => 'string'),
			array('field' => 'Drug_Price', 'label' => 'Цена', 'rules' => '', 'type' => 'float'),
			array('field' => 'fromMobile', 'label' => 'признак моб. устройства', 'rules' => '', 'type' => 'boolean'),
			// обязательный только из мобильного
			array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'OrgFarmacy_id', 'label' => 'Идентификатор аптеки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnRecept_IsKEK', 'label' => 'признак КЭК (медикамент выписывается через врачебную комиссию)', 'rules' => '', 'type' => 'id'),

		),
		'updateEvnRecept' => array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор рецепта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ReceptForm_id', 'label' => 'Форма рецепта', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReceptType_id', 'label' => 'Тип рецепта', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_setDT', 'label' => 'Дата рецерта', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnRecept_Num', 'label' => 'Номер рецепта', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnRecept_Ser', 'label' => 'Серия рецепта', 'rules' => '', 'type' => 'string'),
			array('field' => 'ReceptValid_id', 'label' => 'Срок действия рецепта', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReceptFinance_id', 'label' => 'Тип финансирования', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReceptDiscount_id', 'label' => 'Скидка', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnRecept_is7Noz', 'label' => 'Признак "7 нозологий"', 'rules' => '', 'type' => 'int'),
			array('field' => 'PrivilegeType_id', 'label' => 'Тип льготы', 'rules' => '', 'type' => 'int'),
			//array('field' => 'Drug_id', 'label' => 'Медикамент', 'rules' => '', 'type' => 'id'),
			array('field' => 'DrugComplexMnn_id', 'label' => 'Комплексное МНН', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnRecept_ExtempContents', 'label' => 'Состав медикамента', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnRecept_Kolvo', 'label' => 'Количество медикамента', 'rules' => '', 'type' => 'float'),
			array('field' => 'EvnRecept_Signa', 'label' => 'Signa', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnRecept_IsMnn', 'label' => 'Выписка по МНН', 'rules' => '', 'type' => 'id')// обязательный только из мобильного
		),
		'mloadEvnReceptPanel' => array(
			array('field' => 'EvnRecept_pid', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id')
		),
		'mDeleteEvnRecept' => array(
			array('field' => 'EvnRecept_id', 'label' => 'Идентификатор рецепта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ReceptRemoveCauseType_id', 'label' => 'Идентификатор причины удаления рецепта', 'rules' => 'required', 'type' => 'id'),
			array(
					'field' => 'DeleteType',
					'label' => 'Тип удаления', // 0 - пометить рецепт к удалению, 1 - удалить рецепт
					'rules' => '',
					'type'	=> 'int',
					'default' => 1
				)
		),
		'mloadEvnReceptEditForm' => array(
			array(
				'field' => 'EvnRecept_id',
				'label' => 'Идентификатор рецепта',
				'rules' => 'trim|required',
				'type' => 'id'
			)
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
		$this->load->model('Dlo_EvnRecept_model', 'dbmodel');
		$this->dbmodel->setSessionParams($this->dbmodel->getSessionParams());
	}

	/**
	 * Получение данных льготного рецепта
	 */
	function index_get() {
		$data = $this->ProcessInputData('getEvnRecept');

		$resp = $this->dbmodel->getEvnReceptForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Загрузка списка рецептов для мобильного приложения
	 */
	function mloadEvnReceptPanel_get() {
		$data = $this->ProcessInputData('mloadEvnReceptPanel');

		$resp = $this->dbmodel->loadEvnReceptPanel($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Добавление данных льготного рецепта для мобильного приложения
	 */
	function mDeleteEvnRecept_post() {

		$data = $this->ProcessInputData('mDeleteEvnRecept', null, true);

		$resp = $this->dbmodel->deleteEvnRecept($data);
		if (!empty($resp[0])) $resp = $resp[0];

		$response = array('error_code' => 0);
		if (!empty($resp['Error_Code'])) $response['data'] = $resp;

		$this->response($response);
	}

	/**
	 * Добавление данных льготного рецепта для мобильного приложения
	 */
	function mSaveEnvRecept_post() {

		if (empty($this->_args['ReceptType_id'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не указан обязательный параметр ReceptType_id'
			));
		}

		if(empty($this->_args['EvnRecept_IsMnn'])){// redmine.swan-it.ru/issues/106230#note-184 2
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не указан обязательный параметр EvnRecept_IsMnn'
			));
		}
		$this->_args['EvnRecept_IsMnn'] = (int)$this->_args['EvnRecept_IsMnn'];

		// формируем серию и номер если рецепт "на листе"
		if ($this->_args['ReceptType_id'] == 2) {

			// обязательные параметры для генерации
			$pre_required = array(
				'Evn_setDT',  //'EvnRecept_setDate'
				'WhsDocumentCostItemType_id',
				'ReceptForm_id',
			);

			$params = array();

			foreach ($pre_required as $req_param) {
				if (empty($this->_args[$req_param])) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Не указан обязательный параметр '.$req_param
					));
				} else {
					$params[$req_param] = $this->_args[$req_param];
				}
			}

			$session = getSessionParams();

			if (empty($session['Lpu_id'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не удалось определить параметр сессии Lpu_id'
				));
			}

			if (empty($session['pmUser_id'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не удалось определить параметр сессии pmUser_id'
				));
			}

			$params['Lpu_id'] = $session['Lpu_id'];
			$params['pmUser_id'] = $session['pmUser_id'];
			
			if ( CheckDateFormat($params['Evn_setDT']) == 0 ) {
				// проверяем и устанавливаем дату
				$params['Evn_setDT'] = ConvertDateFormat($params['Evn_setDT']);
				if ( $params['Evn_setDT'] < MIN_CORRECT_DATE ) {
					$params['Evn_setDT'] = NULL;
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Неверно задана дата'
					));
				}
			}else{
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Неверно задана дата'
				));
			}
			
			$params['EvnRecept_setDate'] = $params['Evn_setDT'];

			$ser_num = $this->dbmodel->getReceptSerAndNumberForApi($params);
			if (empty($ser_num['EvnRecept_Num'])) {

				$err = "";

				if (!empty($ser_num['Error_Msg'])) {
					$err = $ser_num['Error_Msg'];
				} else if (empty($ser_num['Error_Msg']) && !empty($ser_num) && is_string($ser_num)) {
					$err = $ser_num;
				}

				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не удалось сформировать номер рецепта. '.$err
				));
			}

			$this->_args['EvnRecept_Ser'] = (!empty($ser_num['EvnRecept_Ser']) ? $ser_num['EvnRecept_Ser']:  '');
			$this->_args['EvnRecept_Num'] = (string)$ser_num['EvnRecept_Num'];// в index_post ожидается строка
		}

		$this->_args['fromMobile'] = true;
		$this->index_post();
	}

	/**
	 * Добавление данных льготного рецепта
	 */
	function index_post() {

		$data = $this->ProcessInputData('createEvnRecept', null, true, true, true);
		$this->load->helper('Options');

		$options = $this->dbmodel->getGlobalOptions();

		$query = "
			with PRTList as (
				select PersonRegisterType_id
				from v_PersonRegisterType with(nolock)
				where PersonRegisterType_SysNick like '%common%'
			)
			select top 1
				E.Lpu_id,
				E.Person_id,
				E.PersonEvn_id,
				E.Server_id,
				MSF.MedPersonal_id,
				PT.DrugFinance_id,
				CostItem.WhsDocumentCostItemType_id
			from
				v_Evn E with(nolock)
				left join v_PrivilegeType PT with(nolock) on PT.PrivilegeType_id = :PrivilegeType_id
				outer apply(
					select top 1 WDCIT.WhsDocumentCostItemType_id
					from v_WhsDocumentCostItemType WDCIT with(nolock)
					where WDCIT.PersonRegisterType_id in (select PersonRegisterType_id from PRTList)
					and (WDCIT.PersonRegisterType_id not in (select PersonRegisterType_id from PRTList) or WDCIT.DrugFinance_id = PT.DrugFinance_id)
				) CostItem
				outer apply(
					select top 1 MSF.MedPersonal_id
					from v_MedStaffFact MSF with(nolock)
					where MSF.MedStaffFact_id = :MedStaffFact_id
					and MSF.LpuSection_id = :LpuSection_id
					and MSF.WorkData_begDate <= :Evn_setDT
					and isnull(MSF.WorkData_endDate, :Evn_setDT) >= :Evn_setDT
				) MSF
			where
				E.Evn_id = :Evn_pid
		";
		$info = $this->dbmodel->getFirstRowFromQuery($query, $data);
		if (!is_array($info)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($info['MedPersonal_id'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не найден врач в указаном отделении на момент выписки рецепта'
			));
		}
		if(!empty($info['Lpu_id']) && $info['Lpu_id'] != $data['Lpu_id']){
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Данный метод доступен только для своей МО'
			));
		}

		$data = array_merge($data, $info);

		$PersonPrivilege_id = $this->dbmodel->getFirstResultFromQuery("
			SELECT TOP 1 PersonPrivilege_id
			FROM v_PersonPrivilege (nolock)
			where Person_id = :Person_id and PrivilegeType_id = :PrivilegeType_id
			order by PersonPrivilege_id desc
		", $data);

		$params = array(
			'EvnRecept_id' => null,
			'EvnRecept_pid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnRecept_setDate' => $data['Evn_setDT'],
			'EvnRecept_Num' => $data['EvnRecept_Num'],
			'EvnRecept_Ser' => $data['EvnRecept_Ser'],
			'Diag_id' => $data['Diag_id'],
			'ReceptDiscount_id' => $data['ReceptDiscount_id'],
			'ReceptFinance_id' => $data['ReceptFinance_id'],
			'DrugFinance_id' => $data['DrugFinance_id'],
			'Drug_Price' => $data['Drug_Price'],
			'ReceptValid_id' => $data['ReceptValid_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			'PersonPrivilege_id' => (!empty($PersonPrivilege_id) ? $PersonPrivilege_id : null),
			'EvnRecept_IsKEK' => $data['EvnRecept_IsKEK'],
			'EvnRecept_Kolvo' => $data['EvnRecept_Kolvo'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
			'EvnRecept_Signa' => $data['EvnRecept_Signa'],
			'ReceptForm_id' => $data['ReceptForm_id'],
			'ReceptType_id' => $data['ReceptType_id'],
			'EvnRecept_IsMnn' => (!empty($data['EvnRecept_IsMnn'])?$data['EvnRecept_IsMnn']:1),
			'EvnRecept_IsPrinted' => null,
			'OrgFarmacy_id' => $data['OrgFarmacy_id'],
			//'EvnRecept_IsNotOstat' => $data['EvnRecept_IsNotOstat'],
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'DrugRequestRow_id' => null,
			'EvnRecept_ExtempContents' => $data['EvnRecept_ExtempContents'],
			'EvnRecept_IsExtemp' => 1,
			'EvnRecept_Is7Noz' => ($data['EvnRecept_is7Noz']==1)?2:1,
			'fromAPI' => 1,
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
		);

		$compare_result = swCompareDates($params['EvnRecept_setDate'], date('d.m.Y'));
		if (-1 == $compare_result[0]) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата выписки рецепта не должна быть больше текущей даты'
			));
		}

		$settings = $options['globals'];
		//Проверка остатков медикамента по заявке врача
		if (!empty($settings['select_drug_from_list']) && in_array($settings['select_drug_from_list'], array('request'/*, 'request_and_allocation'*/))) {
			$resp = $this->dbmodel->getDrugRequestRowOstat($params);
			if ( !empty($resp[0]['Error_Msg']) ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $resp[0]['Error_Msg']
				));
			}
			//print_r(array($resp[0]['DrugRequestRowOstat_Kolvo'], $params['EvnRecept_Kolvo']));exit;
			if ( $resp[0]['DrugRequestRowOstat_Kolvo'] - $params['EvnRecept_Kolvo'] < 0 ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Недостаточное количество медикаментов на остатках по заявке врача'
				));
			}
		}
		if (!empty($settings['select_drug_from_list']) && in_array($settings['select_drug_from_list'], array('allocation'/*, 'request_and_allocation'*/))) {
			$params['DrugOstatRegistry_id'] = $this->dbmodel->getDrugOstatRegistry($params);
			if ($params['DrugOstatRegistry_id'] === false) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при поиске разнарядки'
				));
			}
			if (empty($params['DrugOstatRegistry_id'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не найдена разнарядка с достаточным количеством медикамента для списания'
				));
			}
		}

		$resp = $this->dbmodel->saveEvnReceptRls($params);
		if (!is_array($resp) || !isset($resp[0])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (!empty($resp[0]['Error_Msg'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $resp[0]['Error_Msg']
			));
		}

		$response = array('error_code' => 0);

		if (!empty($data['fromMobile'])) {
			$response['EvnRecept_id'] = $resp[0]['EvnRecept_id'];
		} else {
			$response['data'] = array(array('Evn_id' => $resp[0]['EvnRecept_id']));
		}

		$this->response($response);
	}

	/**
	 * Изменение данных льготного рецепта
	 */
	function index_put() {
		$data = $this->ProcessInputData('updateEvnRecept', null, true, true, true);
		$this->load->helper('Options');

		$options = $this->dbmodel->getGlobalOptions();

		$query = "
			select top 1
				ER.EvnRecept_id as Evn_id,
				ER.EvnRecept_pid as Evn_pid,
				ER.Server_id,
				ER.Person_id,
				ER.PersonEvn_id,
				ER.ReceptForm_id,
				ER.ReceptType_id,
				convert(varchar(10), ER.EvnRecept_setDT, 120) as Evn_setDT,
				ER.EvnRecept_Ser,
				ER.EvnRecept_Num,
				ER.ReceptValid_id,
				ER.Lpu_id,
				ER.LpuSection_id,
				ER.MedPersonal_id,
				ER.Diag_id,
				ER.ReceptFinance_id,
				ER.ReceptDiscount_id,
				is7Noz.YesNo_Code as EvnRecept_is7Noz,
				ER.PrivilegeType_id,
				ER.DrugComplexMnn_id,
				ER.EvnRecept_ExtempContents,
				ER.EvnRecept_Kolvo,
				ER.EvnRecept_Signa,
				ER.OrgFarmacy_id,
				ER.DrugFinance_id,
				ER.WhsDocumentCostItemType_id
			from 
				v_EvnRecept ER with(nolock)
				left join v_Drug D with(nolock) on D.Drug_id = ER.Drug_id
				left join v_YesNo is7Noz with(nolock) on is7Noz.YesNo_id = ER.EvnRecept_is7Noz
			where
				ER.EvnRecept_id = :Evn_id
		";
		$info = $this->dbmodel->getFirstRowFromQuery($query, $data);

		foreach($info as $key => $value) {
			if (!array_key_exists($key, $data)) {
				$data[$key] = $info[$key];
			}
		}
		$data['Lpu_id'] = $info['Lpu_id'];
		$data['Server_id'] = $info['Server_id'];

		if (!empty($data['MedStaffFact_id'])) {
			$resp = $this->dbmodel->getFirstRowFromQuery("
				select top 1 MSF.MedPersonal_id
				from v_MedStaffFact MSF with(nolock)
				where MSF.MedStaffFact_id = :MedStaffFact_id
				and MSF.LpuSection_id = :LpuSection_id
				and MSF.WorkData_begDate <= :Evn_setDT
				and isnull(MSF.WorkData_endDate, :Evn_setDT) >= :Evn_setDT
			", $data, true);
			if (!is_array($resp)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			if (empty($resp)) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не найден врач в указаном отделении на момент выписки рецепта'
				));
			}
			$data['MedPersonal_id'] = $resp['MedPersonal_id'];
		}

		if ($data['PrivilegeType_id'] != $info['PrivilegeType_id']) {
			$resp = $this->dbmodel->getFirstRowFromQuery("
				with PRTList as (
					select PersonRegisterType_id
					from v_PersonRegisterType with(nolock)
					where PersonRegisterType_SysNick like '%common%'
				)
				select top 1
					PT.DrugFinance_id,
					CostItem.WhsDocumentCostItemType_id
				from
					v_PrivilegeType PT with(nolock)
					outer apply(
						select top 1 WDCIT.WhsDocumentCostItemType_id
						from v_WhsDocumentCostItemType WDCIT with(nolock)
						where WDCIT.PersonRegisterType_id in (select PersonRegisterType_id from PRTList)
						and (WDCIT.PersonRegisterType_id not in (select PersonRegisterType_id from PRTList) or WDCIT.DrugFinance_id = PT.DrugFinance_id)
					) CostItem
				where
					PT.PrivilegeType_id = :PrivilegeType_id
			", $data);
			if (!is_array($resp)) {
				$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
			}
			$data['DrugFinance_id'] = $resp['DrugFinance_id'];
			$data['WhsDocumentCostItemType_id'] = $resp['WhsDocumentCostItemType_id'];
		}

		$params = array(
			'EvnRecept_id' => $data['Evn_id'],
			'EvnRecept_pid' => $data['Evn_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnRecept_setDate' => $data['Evn_setDT'],
			'EvnRecept_Num' => $data['EvnRecept_Num'],
			'EvnRecept_Ser' => $data['EvnRecept_Ser'],
			'Diag_id' => $data['Diag_id'],
			'ReceptDiscount_id' => $data['ReceptDiscount_id'],
			'ReceptFinance_id' => $data['ReceptFinance_id'],
			'DrugFinance_id' => $data['DrugFinance_id'],
			'ReceptValid_id' => $data['ReceptValid_id'],
			'PrivilegeType_id' => $data['PrivilegeType_id'],
			//'Drug_IsKEK' => $data['Drug_IsKEK'],
			'EvnRecept_Kolvo' => $data['EvnRecept_Kolvo'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'DrugComplexMnn_id' => $data['DrugComplexMnn_id'],
			'EvnRecept_Signa' => $data['EvnRecept_Signa'],
			'ReceptForm_id' => $data['ReceptForm_id'],
			'ReceptType_id' => $data['ReceptType_id'],
			'EvnRecept_IsMnn' => (!empty($data['EvnRecept_IsMnn'])?$data['EvnRecept_IsMnn']:1),
			'EvnRecept_IsPrinted' => null,
			'OrgFarmacy_id' => $data['OrgFarmacy_id'],
			//'EvnRecept_IsNotOstat' => $data['EvnRecept_IsNotOstat'],
			'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
			'DrugRequestRow_id' => null,
			'EvnRecept_ExtempContents' => $data['EvnRecept_ExtempContents'],
			'EvnRecept_IsExtemp' => 1,
			'EvnRecept_Is7Noz' => ($data['EvnRecept_is7Noz']==1)?2:1,
			'fromAPI' => 1,
			'pmUser_id' => $data['pmUser_id'],
			'session' => $data['session'],
		);

		$compare_result = swCompareDates($params['EvnRecept_setDate'], date('d.m.Y'));
		if (-1 == $compare_result[0]) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата выписки рецепта не должна быть больше текущей даты'
			));
		}

		$settings = $options['globals'];
		//Проверка остатков медикамента по заявке врача
		if (!empty($settings['select_drug_from_list']) && in_array($settings['select_drug_from_list'], array('request'/*, 'request_and_allocation'*/))) {
			$resp = $this->dbmodel->getDrugRequestRowOstat($params);
			if ( !empty($resp[0]['Error_Msg']) ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => $resp[0]['Error_Msg']
				));
			}
			//print_r(array($resp[0]['DrugRequestRowOstat_Kolvo'], $params['EvnRecept_Kolvo']));exit;
			if ( $resp[0]['DrugRequestRowOstat_Kolvo'] - $params['EvnRecept_Kolvo'] < 0 ) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Недостаточное количество медикаментов на остатках по заявке врача'
				));
			}
		}
		if (!empty($settings['select_drug_from_list']) && in_array($settings['select_drug_from_list'], array('allocation'/*, 'request_and_allocation'*/))) {
			$params['DrugOstatRegistry_id'] = $this->dbmodel->getDrugOstatRegistry($params);
			if ($params['DrugOstatRegistry_id'] === false) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Ошибка при поиске разнарядки'
				));
			}
			if (empty($params['DrugOstatRegistry_id'])) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Не найдена разнарядка с достаточным количеством медикамента для списания'
				));
			}
		}

		$resp = $this->dbmodel->saveEvnReceptRls($params);
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
		));
	}

	/**
	 * Получение списка выписанных льготных рецептов
	 */
	function EvnReceptList_get() {
		$data = $this->ProcessInputData('getEvnReceptList');

		$resp = $this->dbmodel->getEvnReceptListForAPI($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение данных для формы редактирования рецепта
	 *
	 * @desсription{
	 *     "input_params":{
	 *         "EvnRecept_id": "Идентификатор рецепта (required)"
	 *     },
	 *     "example":{
				"error_code": 0,
				"data": [
					{
						"EvnRecept_id": "9402690342238",
						"EvnRecept_pid": null,
						"Lpu_id": "10010833",
						"ReceptType_id": "2",
						"ReceptForm_id": null,
						"PrivilegeType_id": "81",
						"EvnRecept_setDate": "08.09.2009",
						"Diag_id": "11742",
						"ReceptFinance_id": "1",
						"DrugFinance_id": null,
						"WhsDocumentCostItemType_id": null,
						"ReceptDiscount_id": "1",
						"EvnRecept_Ser": "5900000",
						"EvnRecept_Num": "10000079",
						"Drug_IsMnn": "2",
						"EvnRecept_IsMnn": "2",
						"Drug_rlsid": null,
						"Drug_id": "176732",
						"DrugMnn_id": "178",
						"EvnRecept_Signa": "1 раз в день",
						"LpuSection_id": "9400000031",
						"MedPersonal_id": "1360",
						"OrgFarmacy_id": null,
						"ReceptValid_id": "2",
						"Lpu_rid": null,
						"MedPersonal_rid": null,
						"DrugRequestMnn_id": null,
						"EvnRecept_Kolvo": "1.0000",
						"EvnRecept_IsExtemp": "1",
						"EvnRecept_ExtempContents": null,
						"EvnRecept_Is7Noz": "1",
						"DrugComplexMnn_id": null,
						"EvnRecept_IsSigned": null,
						"ReceptWrongDelayType_id": null,
						"Recept_Result_Code": 4,
						"Recept_Result": "Удален",
						"Recept_Delay_Info": "Дата удаления: 08.09.2009\r\nПользователь: АБРАМОВА СВЕТЛАНА\r\nПричина:Неправильно выписанный медикамент в рецепте",
						"EvnRecept_Drugs": "",
						"ReceptWrong_DT": null,
						"ReceptDelay_1_days": null,
						"ReceptWrong_Decr": null,
						"Person_id": "2621345",
						"ReceptOtov_insDT": null,
						"ReceptOtov_obrDate": null,
						"ReceptOtov_otpDate": null,
						"ReceptOtov_Farmacy": "",
						"EvnRecept_deleted": "2",
						"Drug_Price": null,
						"ReceptOtov_Date": ""
					}
				]
	 *     }
	 * }
	 */
	function mloadEvnReceptEditForm_get(){
		$data = $this->ProcessInputData('mloadEvnReceptEditForm', null, true);
		if($data === false) $this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);

		$response = $this->dbmodel->loadEvnReceptEditForm($data);// плохая реализация
		// возвращает либо {success: true, Error_Code: error_cause_lpu} - запрос из другой ЛПУ,
		// либо {success: false, Error_Code: error_query_lpu} - ошибка при запросе

		if(isset($response[0]['success'])){
			if($response[0]['success']){// успех проверочного запроса, но ЛПУ другое
				$this->response(array(
					'error_code' => 3,
					'error_msg' => 'Вы не можете открыть рецепт, созданный в другой МО'
				));
			}
			else{// либо не найден ЛПУ с таким рецептом, либо ошибка при запросе (при 2м варианте никогда до сюда не дойдет практически, теоретически да)
				if(isset($response[0]['Error_Code']) && $response[0]['Error_Code'] == 'error_cause_lpu'){
					$this->response(array(
						'error_code' => 3,
						'error_msg' => 'Вы не можете открыть рецепт, созданный в другой МО'
					));
				}

				$this->response(array(
					'error_code' => 3,
					'error_msg' => 'Ошибка при поиске Lpu'
				));
			}
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $response
		));
	}
}