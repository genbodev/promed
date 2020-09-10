<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		09.2013
 */
require_once('EvnPrescrAbstract_model.php');
/**
 * Модель назначения "Операционный блок"
 *
 * Назначения с типом "Операционный блок" хранятся в таблицах EvnPrescrOperBlock, EvnPrescrOperUsluga
 * В назначении должна быть указана только одна услуга.
 * Если услуга имеет состав (UslugaComplexComposition), то могут выбраны все или лишь некоторые простые услуги из её состава.
 * Для каждой выбранной простой услуги из состава создается запись в EvnPrescrOperUsluga.
 *
 * @package		EvnPrescr
 * @author		Александр Пермяков
 * @property EvnDirectionAll_model $EvnDirectionAll_model
 */
class EvnPrescrOperBlock_model extends EvnPrescrAbstract_model
{
	/**
	 * Конструктор
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Определение идентификатора типа назначения
	 * @return int
	 */
	public function getPrescriptionTypeId() {
		return 7;
	}

	/**
	 * Определение имени таблицы с данными назначения
	 * @return string
	 */
	public function getTableName() {
		return 'EvnPrescrOperBlock';
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $scenario
	 * @return array
	 */
	public function getInputRules($scenario) {
		$rules = array();
		switch ($scenario) {
			case 'doSave':
				$rules = array(
					array('field' => 'parentEvnClass_SysNick','label' => 'Системное имя род.события','rules' => '', 'default' => 'EvnSection','type' => 'string'),
					array('field' => 'signature','label' => 'Признак для подписания','rules' => '','type' => 'int'),
					array('field' => 'EvnPrescrOperBlock_id','label' => 'Идентификатор назначения','rules' => '','type' => 'id'),
					array('field' => 'EvnPrescrOperBlock_pid','label' => 'Идентификатор род.события','rules' => 'required','type' => 'id'),
					array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'id'),
					array('field' => 'EvnPrescrOperBlock_uslugaList','label' => 'Список услуг, выбранных из состава комплексной услуги','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrOperBlock_setDate','label' => 'Плановая дата','rules' => '','type' => 'date'),
					array('field' => 'EvnPrescrOperBlock_IsCito','label' => 'Cito','rules' => '','type' => 'string'),
					array('field' => 'EvnPrescrOperBlock_Descr','label' => 'Комментарий','rules' => 'trim','type' => 'string'),
					array('field' => 'EvnDirection_id','label' => 'Идентификатор','rules' => '','type' => 'id'),
					array('field' => 'PersonEvn_id','label' => 'Идентификатор','rules' => 'required','type' => 'id'),
					array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
					array('field' => 'EvnPrescrLimitData','label' => 'Ограничения','rules' => '','type' => 'string'),
					array('field' => 'EvnUslugaOrder_id','label' => 'Идентификатор заказа','rules' => '','type' => 'id'),
					array('field' => 'EvnUslugaOrder_UslugaChecked','label' => 'Измененный состав для обновления заказа','rules' => '','type' => 'string'),
				);
				break;
			case 'doLoad':
				$rules[] = array(
					'field' => 'EvnPrescrOperBlock_id',
					'label' => 'Идентификатор назначения',
					'rules' => 'required',
					'type' =>  'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Сохранение назначения
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doSave($data = array(), $isAllowTransaction = true) {
		$lData = [];
		//getAllData() возвращает данные в lower case
		//так что для корректных проверок проще ключи
		//входных данных тоже привести к lower case
		foreach ($data as $k => $v) {
			$lData[strtolower($k)] = $v;
		}

		$data = $lData;

		if(empty($data['evnprescroperblock_id']))
		{
			$action = 'ins';
			$allow_sign = true;
			$data['evnprescrpperblock_id'] = NULL;
			$data['prescriptionstatustype_id'] = 1;
			$EvnPrescr_id = NULL;
		}
		else
		{
			$action = 'upd';
			$EvnPrescr_id = $data['evnprescroperblock_id'];
			$o_data = $this->getAllData($data['evnprescroperblock_id']);
			if(!empty($o_data['Error_Msg']))
			{
				return array($o_data);
			}
			$allow_sign = (isset($o_data['prescriptionstatustype_id']) && $o_data['prescriptionstatustype_id'] == 1);
			$keys = array_keys($data);
			foreach($o_data as $k => $v) {
				if(!isset($data[$k]))
				{
					$data[$k] = $v;
				}
			}
		}
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				EvnPrescrOperBlock_id as \"EvnPrescrOperBlock_id\"
			from p_EvnPrescrOperBlock_" . $action . "(
				EvnPrescrOperBlock_id := :evnprescroperblock_id,
				EvnPrescrOperBlock_pid := :evnprescroperblock_pid,
				PrescriptionType_id := :prescriptiontype_id,
				PrescriptionStatusType_id := :prescriptionstatustype_id,
				Lpu_id := :lpu_id,
				Server_id := :server_id,
				PersonEvn_id := :personevn_id,
				UslugaComplex_id := :uslugacomplex_id,
				EvnPrescrOperBlock_setDT := :evnprescroperblock_setdate,
				EvnPrescrOperBlock_IsCito := :evnprescroperblock_iscito,
				EvnPrescrOperBlock_Descr := :evnprescroperblock_descr,
				pmUser_id := :pmuser_id
			)
		";

		$data['evnprescroperblock_iscito'] = (empty($data['evnprescroperblock_iscito']) || $data['evnprescroperblock_iscito'] != 'on')? 1 : 2;
		$data['prescriptiontype_id'] = $this->getPrescriptionTypeId();

		//echo getDebugSQL($query, $data); exit();

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$trans_result = $result->result('array');
			if(!empty($trans_result) && !empty($trans_result[0]) && !empty($trans_result[0]['EvnPrescrOperBlock_id']) && empty($trans_result[0]['Error_Msg']))
			{
				$trans_good = true;
				$EvnPrescr_id = $trans_result[0]['EvnPrescrOperBlock_id'];
			}
			else
			{
				$trans_good = false;
			}
		}
		else {
			$trans_good = false;
			$trans_result = false;
		}

		$uslugalist = array();
		if($trans_good === true && !empty($data['EvnPrescrLimitData']))
		{
			$data['EvnPrescrLimitData'] = toUtf($data['EvnPrescrLimitData']);
			$limitdata = json_decode($data['EvnPrescrLimitData'], true);
			foreach($limitdata as $limit) {
				$limit['EvnPrescrLimit_id'] = null;
				$limit['LimitType_IsCatalog'] = 1;
				$limit['EvnPrescr_id'] = $EvnPrescr_id;
				$limit['pmUser_id'] = $data['pmUser_id'];

				if (!empty($limit['LimitType_id'])) {
					// 1. ищем запись для соответвующего LimitType_id и RefValues_id
					$query = "
						select
							epl.EvnPrescrLimit_id as \"EvnPrescrLimit_id\",
							coalesce(lt.LimitType_IsCatalog, 1) as \"LimitType_IsCatalog\"
						from
							v_LimitType lt
							left join v_EvnPrescrLimit epl on epl.LimitType_id = lt.LimitType_id and epl.EvnPrescr_id = :EvnPrescr_id
						where
							lt.LimitType_id = :LimitType_id
						limit 1
					";

					$result = $this->db->query($query, $limit);
					if ( is_object($result) ) {
						$resp = $result->result('array');
						if (!empty($resp[0]['EvnPrescrLimit_id'])) {
							$limit['EvnPrescrLimit_id'] = $resp[0]['EvnPrescrLimit_id'];
						}
						if (!empty($resp[0]['LimitType_IsCatalog'])) {
							$limit['LimitType_IsCatalog'] = $resp[0]['LimitType_IsCatalog'];
						}
					}

					// 2. сохраняем
					$procedure = 'p_EvnPrescrLimit_ins';
					if ( !empty($limit['EvnPrescrLimit_id']) ) {
						$procedure = 'p_EvnPrescrLimit_upd';
					}

					if (empty($limit['EvnPrescrLimit_ValuesNum'])) { $limit['EvnPrescrLimit_ValuesNum'] = null;	}
					if (empty($limit['EvnPrescrLimit_Values'])) { $limit['EvnPrescrLimit_Values'] = null;	}

					$query = "
						select
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\",
							EvnPrescrLimit_id as \"EvnPrescrLimit_id\"
						from " . $procedure . "(
							EvnPrescrLimit_id := :EvnPrescrLimit_id,
							LimitType_id := :LimitType_id,
							EvnPrescrLimit_Values := :EvnPrescrLimit_Values,
							EvnPrescr_id := :EvnPrescr_id,
							EvnPrescrLimit_ValuesNum := :EvnPrescrLimit_ValuesNum,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, $limit);

					if ( is_object($result) ) {
						$res = $result->result('array');
						if(!empty($res) && !empty($res[0]) && empty($res[0]['Error_Msg']))
						{
							$trans_good = true;
						}
						else
						{
							$trans_good = false;
							$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
							break;//выходим из цикла
						}
					}
					else {
						$trans_good = false;
						$trans_result[0]['Error_Msg'] = 'Ошибка запроса при ';
						break;//выходим из цикла
					}
				}
			}
		}

		if($trans_good === true && !empty($data['EvnPrescrOperBlock_uslugaList']))
		{
			$uslugalist = explode(',', $data['EvnPrescrOperBlock_uslugaList']);
			if(empty($uslugalist) || !is_numeric ($uslugalist[0]))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка формата списка услуг';
				$trans_good = false;
			}
			else
			{
				$res = $this->clearEvnPrescrOperBlockUsluga(array('EvnPrescrOperBlock_id' => $EvnPrescr_id));
				if(empty($res))
				{
					$trans_result[0]['Error_Msg'] = 'Ошибка запроса при списка выбранных услуг';
					$trans_good = false;
				}
				if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
				}
			}
		}

		if($trans_good === true && !empty($uslugalist))
		{
			foreach($uslugalist as $d)
			{
				$res = $this->saveEvnPrescrOperBlockUsluga(array(
					'UslugaComplex_id' => $d
				,'EvnPrescrOperBlock_id' => $EvnPrescr_id
				,'pmUser_id' => $data['pmUser_id']
				));
				if(empty($res))
				{
					$trans_result[0]['Error_Msg'] = 'Ошибка запроса при сохранении услуги';
					$trans_good = false;
					break;
				}
				if(!empty($res) && !empty($res[0]) && !empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
					break;
				}
			}
		}

		if($trans_good === true && !empty($data['EvnUslugaOrder_id']) && !empty($data['EvnUslugaOrder_UslugaChecked']))
		{
			$uslugalist = explode(',', $data['EvnUslugaOrder_UslugaChecked']);
			if(empty($uslugalist) || !is_numeric ($uslugalist[0]))
			{
				$trans_result[0]['Error_Msg'] = 'Ошибка формата списка заказанных услуг';
				$trans_good = false;
			}
			else
			{
				$res = $this->_updateEvnUslugaOrder(array(
					'EvnUsluga_Result' => json_encode($uslugalist),
					'Evn_id' => $data['EvnUslugaOrder_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if(!empty($res[0]['Error_Msg']))
				{
					$trans_result[0]['Error_Msg'] = $res[0]['Error_Msg'];
					$trans_good = false;
				}
			}
		}

		if ($action === 'upd' && $trans_good === true
			&& !empty($data['EvnDirection_id'])
		) {
			// обновляем пометку "Cito" заявки в АРМе лаборанта
			$this->load->model('EvnDirectionAll_model');
			$this->EvnDirectionAll_model->setScenario(swModel::SCENARIO_SET_ATTRIBUTE);
			$this->EvnDirectionAll_model->setParams(array(
				'session' => $data['session'],
			));
			$response = $this->EvnDirectionAll_model->updateIsCito($data['EvnDirection_id'], $data['EvnPrescrOperBlock_IsCito']);
			if ( !empty($response['Error_Msg']) ) {
				$trans_result[0]['Error_Msg'] = $response['Error_Msg'];
				$trans_good = false;
				//throw new Exception($response['Error_Msg']);
			}
		}

		if($trans_good === true && $allow_sign && !empty($data['signature']))
		{
		}

		return $trans_result;
	}

	/**
	 *  метод обновления заказа
	 */
	function _updateEvnUslugaOrder($data) {
		$this->
		$query = "
			update Evn
			set
			Evn_updDT = dbo.tzGetDate(),
			pmUser_updID = :pmUser_id
			where Evn_id = :Evn_id;

			update EvnUsluga
			set
			EvnUsluga_Result = :EvnUsluga_Result
			where Evn_id = :Evn_id
		";
		// echo getDebugSQL($query, $data); exit();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg'=>'Ошибка обновления заказа!'));
		}
	}

	/**
	 *  метод очистки списка услуг
	 */
	function clearEvnPrescrOperBlockUsluga($data) {
		return $this->clearEvnPrescrTable(array(
			'object'=>'EvnPrescrOperUsluga'
		,'fk_pid'=>'EvnPrescrOper_id'
		,'pid'=>$data['EvnPrescrOperBlock_id']
		));
	}

	/**
	 * Сохранение назнач
	 */
	function saveEvnPrescrOperBlockUsluga($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\",
				EvnPrescrOperUsluga_id as \"EvnPrescrOperUsluga_id\"
			from p_EvnPrescrOperUsluga_" . (!empty($data['EvnPrescrOperUsluga_id']) ? "upd" : "ins") . "(
				EvnPrescrOperUsluga_id := :EvnPrescrOperUsluga_id,
				EvnPrescrOper_id := :EvnPrescrOper_id,
				UslugaComplex_id := :UslugaComplex_id,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'EvnPrescrOperUsluga_id' => (empty($data['EvnPrescrOperUsluga_id'])? NULL : $data['EvnPrescrOperUsluga_id'] ),
			'EvnPrescrOper_id' => $data['EvnPrescrOperBlock_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		// echo getDebugSQL($query, $queryParams); exit();

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных для формы редактирования
	 * @param array $data Массив, полученный методом ProcessInputData контроллера
	 * @return array|boolean
	 */
	public function doLoad($data) {
		$query = "
			select
				case when EP.PrescriptionStatusType_id = 1
				then 'edit' else 'view' end as \"accessType\",
				EP.EvnPrescrOperBlock_id as \"EvnPrescrOperBlock_id\",
				EP.EvnPrescrOperBlock_pid as \"EvnPrescrOperBlock_pid\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EP.UslugaComplex_id as \"UslugaComplex_id\",
				UCC.UslugaComplex_id as \"UslugaComplex_sid\",
				to_char(EP.EvnPrescrOperBlock_setDT, 'dd.mm.yyyy') as \"EvnPrescrOperBlock_setDate\",
				case when coalesce(EP.EvnPrescrOperBlock_IsCito,1) = 1 then 'off' else 'on' end as \"EvnPrescrOperBlock_IsCito\",
				EP.EvnPrescrOperBlock_Descr as \"EvnPrescrOperBlock_Descr\",
				EP.PersonEvn_id as \"PersonEvn_id\",
				EP.Person_id as \"Person_id\",
				EP.Server_id as \"Server_id\"
			from 
				v_EvnPrescrOperBlock EP
				-- состав услуги, если услуга комплексная
				left join v_EvnPrescrOperUsluga UCC on UCC.EvnPrescrOper_id = EP.EvnPrescrOperBlock_id
				left join lateral (
					Select ED.EvnDirection_id
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
						and ED.EvnDirection_failDT is null
						and coalesce(ED.EvnStatus_id, 16) not in (12,13)
					where epd.EvnPrescr_id = EP.EvnPrescrOperBlock_id
					order by epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
			where
				EP.EvnPrescrOperBlock_id = :EvnPrescrOperBlock_id
		";

		$queryParams = array(
			'EvnPrescrOperBlock_id' => $data['EvnPrescrOperBlock_id']
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp_arr = $result->result('array');
			if(count($tmp_arr) > 0)
			{
				$response = array();
				$uslugalist = array();
			}
			else
			{
				return $tmp_arr;
			}
			foreach($tmp_arr as $row) {
				if(!empty($row['UslugaComplex_sid']))
				{
					$uslugalist[] = $row['UslugaComplex_sid'];
				}
			}
			$response[0] = $tmp_arr[0];
			$response[0]['EvnPrescrOperBlock_uslugaList'] = implode(',',$uslugalist);
			return $response;
		}
		else {
			return false;
		}
	}


	/**
	 * Получение данных для панели просмотра ЭМК и правой панели формы добавления назначений
	 */
	public function doLoadViewData($section, $evn_pid, $sessionParams) {
		$sysnick = swPrescription::getParentEvnClassSysNickBySectionName($section);
		$addJoin = '';
		$filter = '';
		$testFilter = getAccessRightsTestFilter('UC.UslugaComplex_id');
		if (!empty($testFilter)){
			$filter .= "
				and (
					EP.MedPersonal_sid = :MedPersonal_id
					or exists (
						select Evn_id from v_Evn where Evn_id = :EvnPrescr_pid and EvnClass_sysNick = 'EvnSection' and Evn_setDT <= EP.EvnPrescr_setDT and (Evn_disDT is null or Evn_disDT >= EP.EvnPrescr_setDT) limit 1
					)
					or $testFilter
				)";
		}

		if ($sysnick) {
			$accessType = "case when {$sysnick}.Lpu_id = :Lpu_id
			AND coalesce({$sysnick}.{$sysnick}_IsSigned,1) = 1
			AND coalesce(EP.EvnPrescr_IsExec, 1) = 1
			then 'edit' else 'view' end as \"accessType\"";
			$addJoin = "left join v_{$sysnick} {$sysnick} on {$sysnick}.{$sysnick}_id = EP.EvnPrescr_pid";
		} else {
			$accessType = "'view' as \"accessType\"";
		}
		$query = "
			select
				{$accessType},
				EP.EvnPrescr_id as \"EvnPrescr_id\",
				EP.EvnPrescr_pid as \"EvnPrescr_pid\",
				EP.EvnPrescr_rid as \"EvnPrescr_rid\",
				to_char(EP.EvnPrescr_setDT, 'dd.mm.yyyy') as \"EvnPrescr_setDate\",
				null as \"EvnPrescr_setTime\",
				coalesce(EP.EvnPrescr_IsExec,1) as \"EvnPrescr_IsExec\",
				case when EU.EvnUsluga_id is null then 1 else 2 end as \"EvnPrescr_IsHasEvn\",
				case when 2 = EP.EvnPrescr_IsExec
				then to_char(EP.EvnPrescr_updDT, 'dd.mm.yyyy HH24:MI:SS') else null
				end as \"EvnPrescr_execDT\",
				EP.PrescriptionStatusType_id as \"PrescriptionStatusType_id\",
				EP.PrescriptionType_id as \"PrescriptionType_id\",
				EP.PrescriptionType_id as \"PrescriptionType_Code\",
				coalesce(EP.EvnPrescr_IsCito,1) as \"EvnPrescr_IsCito\",
				coalesce(EP.EvnPrescr_Descr,'') as \"EvnPrescr_Descr\",
				case when ED.EvnDirection_id is null OR coalesce(ED.EvnStatus_id, 16) in (12,13) then 1 else 2 end as \"EvnPrescr_IsDir\",
				case when ED.EvnStatus_id is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 12 else ED.EvnStatus_id end as \"EvnStatus_id\",
				case when EvnStatus.EvnStatus_Name is null and (ED.DirFailType_id > 0 OR EQ.QueueFailCause_id > 0 ) then 'лъЛѓл╝лхлйлхлйлЙ' else EvnStatus.EvnStatus_Name end as \"EvnStatus_Name\",
				coalesce(EvnStatusCause.EvnStatusCause_Name, DFT.DirFailType_Name, QFC.QueueFailCause_Name) as \"EvnStatusCause_Name\",
				to_char(coalesce(ED.EvnDirection_statusDate, ED.EvnDirection_failDT, EQ.EvnQueue_failDT), 'dd.mm.yyyy') as \"EvnDirection_statusDate\",
				ESH.EvnStatusCause_id as \"EvnStatusCause_id\",
				ED.DirFailType_id as \"DirFailType_id\",
				EQ.QueueFailCause_id  as \"QueueFailCause_id \",
				ESH.EvnStatusHistory_Cause as \"EvnStatusHistory_Cause\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				EQ.EvnQueue_id as \"EvnQueue_id\",
				case when ED.EvnDirection_Num is null /*or coalesce(ED.EvnDirection_IsAuto,1) = 2*/ then '' else cast(ED.EvnDirection_Num as varchar) end as \"EvnDirection_Num\",
				case
					when TTR.TimetableResource_id is not null then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(Lpu.Lpu_Nick,'')
					when EQ.EvnQueue_id is not null then
						case
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is null
							then coalesce(MS.MedService_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
							when MS.MedService_id is not null and  MS.LpuSection_id is not null and MS.LpuUnit_id is not null
							then coalesce(MS.MedService_Name,'') ||' / '|| coalesce(LSPD.LpuSectionProfile_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
							else coalesce(LSPD.LpuSectionProfile_Name,'') ||' / '|| coalesce(LU.LpuUnit_Name,'')
						end ||' / '|| coalesce(Lpu.Lpu_Nick,'')
				else '' end as \"RecTo\",
				case
					when TTR.TimetableResource_id is not null then coalesce(to_char(TTR.TimetableResource_begTime, 'dd.mm.yyyy HH24:MI:SS'),'')
					when EQ.EvnQueue_id is not null then 'В очереди с '|| coalesce(to_char(EQ.EvnQueue_setDate, 'dd.mm.yyyy'),'')
				else '' end as \"RecDate\",
				case
					when TTR.TimetableResource_id is not null then 'TimetableResource'
					when EQ.EvnQueue_id is not null then 'EvnQueue'
				else '' end as \"timetable\",
				case
					when TTR.TimetableResource_id is not null  then TTR.TimetableResource_id::text
					when EQ.EvnQueue_id is not null then EQ.EvnQueue_id::text
				else '' end as \"timetable_id\",
				EP.EvnPrescr_pid as \"timetable_pid\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				DT.DirType_Code as \"DirType_Code\",
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_2011id as \"UslugaComplex_2011id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				coalesce(UCMS.UslugaComplex_Name, UC.UslugaComplex_Name) as \"UslugaComplex_Name\",
				null as \"TableUsluga_id\"
			from v_EvnPrescr EP
				inner join EvnPrescrOperBlock EPOB on EPOB.Evn_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC on UC.UslugaComplex_id = EPOB.UslugaComplex_id
				left join lateral (
					Select ED.EvnDirection_id
						,coalesce(ED.Lpu_sid, ED.Lpu_id) Lpu_id
						,ED.EvnQueue_id
						,ED.EvnDirection_Num
						,ED.EvnDirection_IsAuto
						,ED.LpuSection_did
						,ED.LpuUnit_did
						,ED.Lpu_did
						,ED.MedService_id
						,ED.LpuSectionProfile_id
						,ED.DirType_id
						,ED.EvnStatus_id
						,ED.EvnDirection_statusDate
						,ED.DirFailType_id
						,ED.EvnDirection_failDT
					from v_EvnPrescrDirection epd
					inner join v_EvnDirection_all ED on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					order by 
						case when coalesce(ED.EvnStatus_id, 16) in (12,13) then 2 else 1 end /* первым неотмененное/неотклоненное направление */
						,epd.EvnPrescrDirection_insDT desc
					limit 1
				) ED on true
				-- службы и параклиника
				left join lateral (
					Select TimetableResource_id, TimetableResource_begTime from v_TimetableResource_lite TTR where TTR.EvnDirection_id = ED.EvnDirection_id
					limit 1
				) TTR on true
				-- очередь
				left join lateral (
					(Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where EQ.EvnDirection_id = ED.EvnDirection_id
					and EQ.EvnQueue_recDT is null
					limit 1)
					-- это костыль для того, чтобы направления у которых есть связь по EQ.EvnQueue_id = ED.EvnQueue_id, но нет реальной записи в TimetableMedService можно было отменить
					union
					(Select EQ.EvnQueue_id, EQ.LpuUnit_did, EQ.LpuSectionProfile_did, Lpu_id, EQ.EvnQueue_setDate, EQ.EvnQueue_failDT, EQ.QueueFailCause_id 
					from v_EvnQueue EQ
					where (EQ.EvnQueue_id = ED.EvnQueue_id)
					and (EQ.EvnQueue_recDT is null or TTR.TimetableResource_id is null)
					and EQ.EvnQueue_failDT is null
					limit 1)
				) EQ on true
				left join lateral(
					select ESH.EvnStatus_id, ESH.EvnStatusCause_id, ESH.pmUser_insID, ESH.EvnStatusHistory_Cause
					from EvnStatusHistory ESH
					where ESH.Evn_id = ED.EvnDirection_id
						and ESH.EvnStatus_id = ED.EvnStatus_id
					order by ESH.EvnStatusHistory_begDate desc
					limit 1
				) ESH on true
				left join EvnStatus on EvnStatus.EvnStatus_id = ESH.EvnStatus_id
				left join EvnStatusCause on EvnStatusCause.EvnStatusCause_id = ESH.EvnStatusCause_id
				left join v_DirFailType DFT on DFT.DirFailType_id = ED.DirFailType_id
				left join v_QueueFailCause QFC on QFC.QueueFailCause_id = EQ.QueueFailCause_id
				-- сама служба (todo: надо ли оно)
				left join v_MedService MS on MS.MedService_id = ED.MedService_id -- ED.MedService_did должно быть
				-- услуга на службе
				left join v_UslugaComplexMedService UCMS on UCMS.MedService_id = MS.MedService_id and UCMS.UslugaComplex_id = UC.UslugaComplex_id and UCMS.UslugaComplexMedService_pid is null
				-- отделение для полки и стаца и для очереди
				left join v_LpuSection LS on LS.LpuSection_id = coalesce(ED.LpuSection_did, MS.LpuSection_id)
				-- заказанная услуга для параклиники
				--left join v_EvnUslugaPar EUP on EUP.EvnDirection_id = ED.EvnDirection_id
				-- подразделение для очереди и служб
				left join v_LpuUnit LU on coalesce(ED.LpuUnit_did,EQ.LpuUnit_did,MS.LpuUnit_id) = LU.LpuUnit_id -- todo: в ED.LpuUnit_did пусто, чего не должно быть
				-- профиль для очереди
				left join v_LpuSectionProfile LSPD on coalesce(ED.LpuSectionProfile_id,EQ.LpuSectionProfile_did,LS.LpuSectionProfile_id) = LSPD.LpuSectionProfile_id -- todo: тут на примере оказалось что почему то ED.LpuSectionProfile_id != EQ.LpuSectionProfile_did, чего не должно быть
				-- тип направления
				left join v_DirType DT on ED.DirType_id = DT.DirType_id
				-- ЛПУ
				left join v_Lpu Lpu on Lpu.Lpu_id = coalesce(ED.Lpu_did, LS.Lpu_id, MS.Lpu_id, EQ.Lpu_id)
				left join lateral (
					select EvnUsluga_id, EvnUsluga_setDT from v_EvnUsluga
					where EP.EvnPrescr_IsExec = 2 and UC.UslugaComplex_id is not null and EvnPrescr_id = EP.EvnPrescr_id
					limit 1
				) EU on true
				{$addJoin}
			where
				EP.EvnPrescr_pid  = :EvnPrescr_pid
				and EP.PrescriptionType_id = 7
				and EP.PrescriptionStatusType_id != 3
				{$filter}
			order by
				EP.EvnPrescr_id,
				EP.EvnPrescr_setDT
		";
		$queryParams = array(
			'EvnPrescr_pid' => $evn_pid,
			'Lpu_id' => $sessionParams['lpu_id'],
			'MedPersonal_id' => $sessionParams['medpersonal_id'],
		);
		//echo getDebugSql($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			$tmp_arr = $result->result('array');
			$response = array();
			foreach ($tmp_arr as $i => $row) {
				$row['UslugaId_List'] = $row['UslugaComplex_id'];
				if ($this->options['prescription']['enable_show_service_code']) {
					$row['Usluga_List'] = $row['UslugaComplex_Code'].' '.$row['UslugaComplex_Name'];
				} else {
					$row['Usluga_List'] = $row['UslugaComplex_Name'];
				}
				$row[$section . '_id'] = $row['EvnPrescr_id'].'-0';
				$response[] = $row;
			}
			//загружаем документы
			$tmp_arr = array();
			$evnPrescrIdList = array();
			foreach ($response as $key => $row) {
				if (isset($row['EvnPrescr_IsExec'])
					&& 2 == $row['EvnPrescr_IsExec']
					&& isset($row['EvnPrescr_IsHasEvn'])
					&& 2 == $row['EvnPrescr_IsHasEvn']
				) {
					$response[$key]['EvnXml_id'] = null;
					$id = $row['EvnPrescr_id'];
					$evnPrescrIdList[] = $id;
					$tmp_arr[$id] = $key;
				}
			}
			if (count($evnPrescrIdList) > 0) {
				$evnPrescrIdList = implode(',',$evnPrescrIdList);
				$query = "
				WITH EvnPrescrEvnXml
				as (
					select doc.EvnXml_id, EU.EvnPrescr_id
					from  v_EvnUsluga EU
					inner join v_EvnXml doc on doc.Evn_id = EU.EvnUsluga_id
					where EU.EvnPrescr_id in ({$evnPrescrIdList})
				)

				select
					EvnXml_id as \"EvnXml_id\",
					EvnPrescr_id as \"EvnPrescr_id\"
				from EvnPrescrEvnXml
				order by EvnPrescr_id";
				$result = $this->db->query($query);
				if ( is_object($result) ) {
					$evnPrescrIdList = $result->result('array');
					foreach ($evnPrescrIdList as $row) {
						$id = $row['EvnPrescr_id'];
						if (isset($tmp_arr[$id])) {
							$key = $tmp_arr[$id];
							if (isset($response[$key])) {
								$response[$key]['EvnXml_id'] = $row['EvnXml_id'];
							}
						}
					}
				}
			}
			return $response;
		} else {
			return false;
		}
	}
}
