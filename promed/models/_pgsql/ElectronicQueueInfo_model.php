<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ElectronicQueueInfo_model - модель для работы с очередями
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 */

class ElectronicQueueInfo_model extends swPgModel {

	/**
	 * Удаление очереди
	 */
	function delete($data) {

		$this->load->model("ElectronicService_model", "es_model");
		$this->beginTransaction();

		$resp = $this->queryResult("
			select
				ElectronicService_id as \"ElectronicService_id\"
			from v_ElectronicService
			where ElectronicQueueInfo_id = :ElectronicQueueInfo_id
		", $data);

		foreach($resp as $item) {
			$res = $this->es_model->delete(array(
				'ElectronicService_id' => $item['ElectronicService_id']
			));
			if (!empty($res[0]['Error_Msg'])) {
				$this->rollbackTransaction();
				return $res;
			}
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ElectronicQueueInfo_del(
				ElectronicQueueInfo_id := :ElectronicQueueInfo_id
			)
		";

		$this->commitTransaction();
		return $this->queryResult($query, $data);
	}

	/**
	 * Включение/Выключение очереди
	 */
	function setElectronicQueueInfoIsOff($data) {
		$query = "
			update ElectronicQueueInfo
				set
					ElectronicQueueInfo_IsOff = :ElectronicQueueInfo_IsOff,
					ElectronicQueueInfo_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					ElectronicQueueInfo_id = :ElectronicQueueInfo_id;
			
			Select :ElectronicQueueInfo_IsOff as \"ElectronicQueueInfo_IsOff\"
		";

		$resp = $this->queryResult($query, $data);

		if (empty($resp[0]['Error_Msg'])) {
			// отправляем в ноджс
			$this->load->helper('NodeJS');
			$params = array(
				'action' => 'setElectronicQueueInfoIsOff',
				'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
				'ElectronicQueueInfo_IsOff' => $data['ElectronicQueueInfo_IsOff']
			);
			$config = null;
			if (defined('NODEJS_PORTAL_PROXY_HOSTNAME') && defined('NODEJS_PORTAL_PROXY_HTTPPORT')) {
				// берём хост и порт из конфига, если есть
				$config = array(
					'host' => NODEJS_PORTAL_PROXY_HOSTNAME,
					'port' => NODEJS_PORTAL_PROXY_HTTPPORT
				);
			}
			$postSendResult = NodePostRequest($params, $config);
			if (!empty($postSendResult[0]['Error_Msg'])) {
				return $postSendResult[0];
			}
		}

		return $resp;
	}

	/**
	 * Возвращает список очередей
	 */
	function loadList($data) {

		$filter = "";
		$queryParams = array();

		if (!empty($data['f_Lpu_id'])) {
			$filter .= " and eqi.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['f_Lpu_id'];
		}

		if (!empty($data['LpuBuilding_id'])) {
			$filter .= " and eqi.LpuBuilding_id = :LpuBuilding_id";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if (!empty($data['MedService_id'])) {
			$filter .= " and eqi.MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if (!empty($data['ElectronicQueueInfo_Code'])) {
			$filter .= " and eqi.ElectronicQueueInfo_Code = :ElectronicQueueInfo_Code";
			$queryParams['ElectronicQueueInfo_Code'] = $data['ElectronicQueueInfo_Code'];
		}

		if (!empty($data['ElectronicQueueInfo_Name'])) {
			$filter .= " and eqi.ElectronicQueueInfo_Name like '%'||:ElectronicQueueInfo_Name||'%'";
			$queryParams['ElectronicQueueInfo_Name'] = $data['ElectronicQueueInfo_Name'];
		}

		if (!empty($data['ElectronicQueueInfo_Nick'])) {
			$filter .= " and eqi.ElectronicQueueInfo_Nick like '%'||:ElectronicQueueInfo_Nick||'%'";
			$queryParams['ElectronicQueueInfo_Nick'] = $data['ElectronicQueueInfo_Nick'];
		}

		if (isset($data['ElectronicQueueInfo_WorkRange'])) {

			list($begDate, $endDate) = explode('-', $data['ElectronicQueueInfo_WorkRange']);

			if (!empty($begDate) && !empty($endDate)) {

				$filter .= " and eqi.ElectronicQueueInfo_begDate >= :ElectronicQueueInfo_begDate
                            and (eqi.ElectronicQueueInfo_endDate <= :ElectronicQueueInfo_endDate or eqi.ElectronicQueueInfo_endDate IS NULL)
                ";

				$queryParams['ElectronicQueueInfo_begDate'] = date('Y-m-d', strtotime(trim($begDate)));
				$queryParams['ElectronicQueueInfo_endDate'] = date('Y-m-d', strtotime(trim($endDate)));
			}
		}

		$query = "
			select
				-- select
				 eqi.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,eqi.Lpu_id as \"Lpu_id\"
				,eqi.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\"
				,eqi.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
				,eqi.ElectronicQueueInfo_Nick as \"ElectronicQueueInfo_Nick\"
				,to_char(eqi.ElectronicQueueInfo_begDate, 'dd.mm.yyyy') as \"ElectronicQueueInfo_begDate\"
				,to_char(eqi.ElectronicQueueInfo_endDate, 'dd.mm.yyyy') as \"ElectronicQueueInfo_endDate\"
				,l.Lpu_Nick as \"Lpu_Nick\"
				,ms.MedService_Nick as \"MedService_Nick\"
				,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
				,case when eqi.ElectronicQueueInfo_IsOff = 2 then 'false' else 'true' end as \"ElectronicQueueInfo_IsOn\"
				-- end select
			from
				-- from
				v_ElectronicQueueInfo eqi
				left join v_Lpu l on l.Lpu_id = eqi.Lpu_id
				left join v_MedService ms on ms.MedService_id = eqi.MedService_id
				left join v_LpuBuilding lb on lb.LpuBuilding_id = eqi.LpuBuilding_id
				-- end from
			where
				-- where
				(1=1)
				{$filter}
				-- end where
			order by
				-- order by
				eqi.ElectronicQueueInfo_begDate desc
				-- end order by
		";

		return $this->getPagingResponse($query, $queryParams, $data['start'], $data['limit'], true);
	}



	/**
	 * Возвращает очередь
	 */
	function load($data) {

		$query = "
			select
				 EQI.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\"
				,EQI.Lpu_id as \"Lpu_id\"
				,EQI.MedService_id as \"MedService_id\"
				,EQI.LpuBuilding_id as \"LpuBuilding_id\"
				,EQI.LpuSection_id as \"LpuSection_id\"
				,EQI.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\"
				,EQI.ElectronicQueueInfo_Name as \"ElectronicQueueInfo_Name\"
				,EQI.ElectronicQueueInfo_Nick as \"ElectronicQueueInfo_Nick\"
				,to_char(EQI.ElectronicQueueInfo_begDate, 'dd.mm.yyyy') as \"ElectronicQueueInfo_begDate\"
				,to_char(EQI.ElectronicQueueInfo_endDate, 'dd.mm.yyyy') as \"ElectronicQueueInfo_endDate\"
				,EQI.ElectronicQueueInfo_CallTimeSec as \"ElectronicQueueInfo_CallTimeSec\"
				,EQI.ElectronicQueueInfo_QueueTimeMin as \"ElectronicQueueInfo_QueueTimeMin\"
				,EQI.ElectronicQueueInfo_LateTimeMin as \"ElectronicQueueInfo_LateTimeMin\"
				,EQI.ElectronicQueueInfo_PersCallDelTimeMin as \"ElectronicQueueInfo_PersCallDelTimeMin\"
				,EQI.ElectronicQueueInfo_CallCount as \"ElectronicQueueInfo_CallCount\"
				,MST.MedServiceType_SysNick as \"MedServiceType_SysNick\"
				,case when EQI.ElectronicQueueInfo_IsOff = 2 then 'false' else 'true' end as \"ElectronicQueueInfo_IsOn\"
				,case when EQI.ElectronicQueueInfo_IsIdent = 2 then 'true' else 'false' end as \"ElectronicQueueInfo_IsIdent\"
				,case when EQI.ElectronicQueueInfo_IsCurDay = 2 then 'true' else 'false' end as \"ElectronicQueueInfo_IsCurDay\"
				,case when EQI.ElectronicQueueInfo_IsAutoReg = 2 then 'true' else 'false' end as \"ElectronicQueueInfo_IsAutoReg\"
				,case when EQI.ElectronicQueueInfo_IsNoTTGInfo = 2 then 'true' else 'false' end as \"ElectronicQueueInfo_IsNoTTGInfo\"
			from
				v_ElectronicQueueInfo EQI
				left join MedService MS on MS.MedService_id = EQI.MedService_id
				left join MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
			where
				EQI.ElectronicQueueInfo_id = :ElectronicQueueInfo_id
				
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * Сохраняет очередь
	 */
	function save($data) {

		$procedure = empty($data['ElectronicQueueInfo_id']) ? 'p_ElectronicQueueInfo_ins' : 'p_ElectronicQueueInfo_upd';
		$field = "";
		$filter = "";
		$join = "";
		if ( !empty($data['ElectronicQueueAssign']) && $data['ElectronicQueueAssign'] == 'lpubuilding' ) {
			$field .= 'LB.LpuBuilding_Name as "LpuBuilding_Name",';
			$join .= 'left join v_LpuBuilding LB on LB.LpuBuilding_id = EQI.LpuBuilding_id ';
		}

		$query = "
			select
				{$field}
				EQI.ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				EQI.ElectronicQueueInfo_Code as \"ElectronicQueueInfo_Code\",
				EQI.LpuBuilding_id as \"LpuBuilding_id\"
			from
				v_ElectronicQueueInfo EQI
				{$join}
			where
				(1=1)
				and EQI.Lpu_id = :Lpu_id
				and EQI.ElectronicQueueInfo_Code = :ElectronicQueueInfo_Code
				and (EQI.ElectronicQueueInfo_endDate is null OR EQI.ElectronicQueueInfo_endDate >= dbo.tzGetDate())
				{$filter}
			limit 1
		";

		$resp = $this->queryResult($query, $data);

		// проверка на дублирование кода очереди
		if (!empty($resp[0])) {

			if (!empty($data['ElectronicQueueInfo_id'])
				&& $data['ElectronicQueueInfo_id'] == $resp[0]['ElectronicQueueInfo_id']
				&& $data['ElectronicQueueInfo_Code'] == $resp[0]['ElectronicQueueInfo_Code']
			) {
				// nothing to do
			} else if (!empty($data['ElectronicQueueAssign']) && $data['ElectronicQueueAssign'] == 'lpubuilding' ) {

				if ( !empty($resp[0]['LpuBuilding_id']) && $data['LpuBuilding_id'] == $resp[0]['LpuBuilding_id'] ) {

					$response[0]['Alert_Msg'] = 'Данный код ЭО уже используется для указанного подразделения';
					$response[0]['Error_Code'] = 101;
					return $response;
				} else if( empty($data['IgnoreEQCodeDuplicate']) || $data['IgnoreEQCodeDuplicate'] == 'false' ) {
					$response[0]['Alert_Msg'] = 'Данный код ЭО уже используется' . (empty($resp[0]['LpuBuilding_Name']) ? '.' : ' для подразделения ' . $resp[0]['LpuBuilding_Name']) . '. Продолжить?';
					$response[0]['Error_Code'] = 102;
					return $response;
				}

			} else {
				$response[0]['Alert_Msg'] = 'Данный код ЭО очереди уже используется';
				$response[0]['Error_Code'] = 103;
				$response[0]['success'] = false;
				return $response;
			}
		}

		// установим флаги
		$data['ElectronicQueueInfo_IsIdent'] = (($data['ElectronicQueueInfo_IsIdent']) ? 2 : 1);
		$data['ElectronicQueueInfo_IsCurDay'] = (($data['ElectronicQueueInfo_IsCurDay']) ? 2 : 1);
		$data['ElectronicQueueInfo_IsAutoReg'] = (($data['ElectronicQueueInfo_IsAutoReg']) ? 2 : 1);
		$data['ElectronicQueueInfo_IsNoTTGInfo'] = (($data['ElectronicQueueInfo_IsNoTTGInfo']) ? 2 : 1);

		$query = "
			select
				ElectronicQueueInfo_id as \"ElectronicQueueInfo_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				ElectronicQueueInfo_id := :ElectronicQueueInfo_id,
				Lpu_id := :Lpu_id,
				MedService_id := :MedService_id,
				LpuBuilding_id := :LpuBuilding_id,
				LpuSection_id := :LpuSection_id,
				ElectronicQueueInfo_Code := :ElectronicQueueInfo_Code,
				ElectronicQueueInfo_Name := :ElectronicQueueInfo_Name,
				ElectronicQueueInfo_Nick := :ElectronicQueueInfo_Nick,
				ElectronicQueueInfo_begDate := :ElectronicQueueInfo_begDate,
				ElectronicQueueInfo_endDate := :ElectronicQueueInfo_endDate,
				ElectronicQueueInfo_CallTimeSec := :ElectronicQueueInfo_CallTimeSec,
				ElectronicQueueInfo_QueueTimeMin := :ElectronicQueueInfo_QueueTimeMin,
				ElectronicQueueInfo_LateTimeMin := :ElectronicQueueInfo_LateTimeMin,
				ElectronicQueueInfo_IsOff := :ElectronicQueueInfo_IsOff,
				ElectronicQueueInfo_CallCount := :ElectronicQueueInfo_CallCount,
				ElectronicQueueInfo_IsIdent := :ElectronicQueueInfo_IsIdent,
				ElectronicQueueInfo_IsCurDay := :ElectronicQueueInfo_IsCurDay,
				ElectronicQueueInfo_IsAutoReg := :ElectronicQueueInfo_IsAutoReg,
				ElectronicQueueInfo_PersCallDelTimeMin := :ElectronicQueueInfo_PersCallDelTimeMin,
				ElectronicQueueInfo_IsNoTTGInfo := :ElectronicQueueInfo_IsNoTTGInfo,
				pmUser_id := :pmUser_id
			)
		";

		$resp = $this->queryResult($query, $data);

		// вынес отправку сообщения ноду в отдельный метод метод
		return $resp;
	}

	/**
	 * Отправляем сообщение НОДУ
	 */
	function sendNodeRequest($params) {

		// инициализируем настройки соединения
		$config = null;
		if (defined('NODEJS_PORTAL_PROXY_HOSTNAME') && defined('NODEJS_PORTAL_PROXY_HTTPPORT')) {
			// берём хост и порт из конфига, если есть
			$config = array(
				'host' => NODEJS_PORTAL_PROXY_HOSTNAME,
				'port' => NODEJS_PORTAL_PROXY_HTTPPORT
			);

			$this->load->helper('NodeJS');
			$response = NodePostRequest($params, $config);

			if (!empty($response[0]['Error_Msg'])) {
				return $response[0];
			}
		}
	}

	/**
	 * Сохраняем связь очередь-пункты обслуживания для всех записей
	 */
	function updateQueueElectronicServices($data) {

		$result = array();
		$error = array();

		if (!empty($data['jsonData']) && $data['ElectronicQueueInfo_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['jsonData']);
			$records = (array) json_decode($data['jsonData']);

			// сохраняем\удаляем все записи из связанного грида по очереди
			foreach($records as $record) {

				if (count($error) == 0) {

					switch($record->state) {

						case 'add':
						case 'edit':

							$response = $this->saveObject('ElectronicService', array(
								'ElectronicService_id' => $record->state == 'add' ? null : $record->ElectronicService_id,
								'ElectronicQueueInfo_id' => $data['ElectronicQueueInfo_id'],
								'ElectronicService_Code' => $record->ElectronicService_Code,
								'ElectronicService_Num' =>  !empty($record->ElectronicService_Num) ? $record->ElectronicService_Num : NULL,
								'ElectronicService_Name' => $record->ElectronicService_Name,
								'ElectronicService_Nick' => $record->ElectronicService_Nick,
								'SurveyType_id' => !empty($record->SurveyType_id) ? $record->SurveyType_id : null,
								'ElectronicService_tid' => !empty($record->ElectronicService_tid) ? $record->ElectronicService_tid : NULL,
								'ElectronicService_isShownET' => (!empty($record->ElectronicService_isShownET) ? 2 : 1),
								'pmUser_id' => $data['pmUser_id']
							));
							break;

						case 'delete':

							$response = $this->deleteQueueElectronicService(array(
								'ElectronicService_id' => $record->ElectronicService_id
							));
							if (!empty($response['Error_Msg'])) {
								$error[] = $response['Error_Msg'];
							}
							break;
					}
					if (!empty($response['Error_Msg'])) {
						$error[] = $response['Error_Msg'];
					}
				}

				if (count($error) > 0) {
					break;
				}
			}
		}

		if (count($error) > 0) {
			$result['success'] = false;
			$result['Error_Msg'] = $error[0];
		} else {
			$result['success'] = true;
		}

		return array($result);
	}

	/**
	 * Удаление связи очередь-пункт обслуживания
	 */
	function deleteQueueElectronicService($data) {

		$result = array();
		$error = array();

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_ElectronicService_del(
				ElectronicService_id := :ElectronicService_id
			)
		";

		$result = $this->getFirstRowFromQuery($query, $data);

		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			$response = $result;
		} else {
			$response = array('Error_Msg' => 'При удалении произошла ошибка');
		}

		if (!empty($response['Error_Msg'])) {
			$error[] = $response['Error_Msg'];
		}

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
		}

		return $result;
	}

	/**
	 * Возвращает список всех связанных c очередями ЛПУ
	 */
	function loadAllRelatedLpu() {

		$query = "
			select distinct
				 lpu.Lpu_id as \"Lpu_id\"
				,lpu.Lpu_Name as \"Lpu_Name\"
				,lpu.Lpu_Nick as \"Lpu_Nick\"
			from
				v_ElectronicQueueInfo eqi
				inner join v_Lpu_all lpu on lpu.Lpu_id = eqi.Lpu_id
		";

		$resp = $this->queryResult($query);
		return $resp;
	}

	/**
	 * Получение списка талонов ЭО для грида
	 */
	function getElectronicQueueGrid($data) {
		if (empty($data['begDate'])) {

			$begDay = date('Y-m-d H:i:s',mktime( 0,
				0,
				0,
				date( "Y" ),
				date( "m" ),
				date( "d" )));

			$endDay =  date('Y-m-d H:i:s',mktime( 23,
				59,
				59,
				date( "m" ),
				date( "d" ) + 15,
				date( "Y" )));

		} else {
			$begDay = date('Y-m-d H:i:s', strtotime($data['begDate']));
			$endDay = date('Y-m-d H:i:s', strtotime($data['endDate']. ' + 23 hours 59 minutes 59 seconds'));
		}

		$filter = "(1 = 1)";
		$params = array();

        $filter .= " and ElectronicTalon_insDT between :begDay and :endDay";

		$params['begDay'] = $begDay;
		$params['endDay'] = $endDay;

		if (!empty($data['ElectronicService_id'])) {
			$params['ElectronicService_id'] = $data['ElectronicService_id'];
		} else return false;

		$sql = "
			SELECT
				et.ElectronicTalon_id as \"ElectronicTalon_id\",
				et.ElectronicTalon_Num as \"ElectronicTalon_Num\",
				et.Person_id as \"Person_id\",
				et.ElectronicTalonStatus_id as \"ElectronicTalonStatus_id\",
				ets.ElectronicTalonStatus_Name as \"ElectronicTalonStatus_Name\",
				et.ElectronicService_id as \"ElectronicService_id\",
				ed.EvnQueue_id as \"EvnQueue_id\",
				ed.EvnStatus_id as \"EvnStatus_id\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				et.ElectronicTalon_insDT as \"ElectronicTalon_insDT\",
				eth.ElectronicTalon_ProcessedTime as \"ElectronicTalon_ProcessedTime\",
				et.ElectronicTreatment_id as \"ElectronicTreatment_id\",
                etre.ElectronicTreatment_Name as \"ElectronicTreatment_Name\",
				to_char(ElectronicTalon_insDT, 'HH24:MI:SS') as \"ElectronicTalon_Time\",
				to_char(ElectronicTalon_insDT, 'dd.mm.yyyy') as \"ElectronicTalon_Date\",
				case when et.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as \"pmUser_Name\",
				et.pmUser_updId as \"pmUser_updId\",
				et.pmUser_insId as \"pmUser_insId\",
				rtrim(rtrim(p.Person_Surname) || ' ' || coalesce(rtrim(p.Person_Firname),'') || ' ' || coalesce(rtrim(p.Person_Secname),'')) as \"Person_FIO\",
				case when ed.EvnDirection_isAuto != 2 then 'true' else 'false' end as \"IsEvnDirection\"
			FROM
				v_EvnDirection_all ed
				inner join v_EvnQueue evnq on (evnq.EvnDirection_id = ed.EvnDirection_id)
				inner join v_ElectronicQueueInfo eqi on eqi.MedService_id = evnq.MedService_did
				inner join v_ElectronicTalon et on (et.EvnDirection_id = ed.EvnDirection_id or et.EvnDirection_uid = ed.EvnDirection_id)
				inner join v_ElectronicService es on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
				left join v_ElectronicTalonStatus ets on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_pmUser pu on pu.pmUser_id = et.pmUser_updId
				left join v_PersonState_all p on p.Person_id = et.Person_id
				left join v_ElectronicTreatment etre on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				left join lateral (
					select
						to_char(etho.ElectronicTalonHist_insDT, 'HH24:MI:SS') as ElectronicTalon_ProcessedTime
					from
						v_ElectronicTalonHist etho
					where
						(1=1)
						and etho.ElectronicTalon_id = et.ElectronicTalon_id
						and etho.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
					order by ElectronicTalonHist_id desc
					limit 1
				) as eth on true
			WHERE
				{$filter}
				and ed.DirType_id = 24
				and es.ElectronicService_id = :ElectronicService_id
				-- показываем талоны которые еще никем не обработаны, либо только талоны своего пункта обслуживания
				and (et.ElectronicService_id is null or et.ElectronicService_id = :ElectronicService_id)
			";

		$response = $this->db->query($sql, $params);
		//echo getDebugSql($sql,$params);

		if (is_object($response)) {
			$result = $response->result('array');
			$newResult = array();
			foreach ($result as $item) {
                if(!empty($item['ElectronicTalon_insDT'])) $item['ElectronicTalon_Time'] = DateTime::createFromFormat('Y-m-d H:i:s.u',$item['ElectronicTalon_insDT'])->format('H:i');
                if(!empty($item['ElectronicTalon_insDT'])) $item['ElectronicTalon_Date'] = DateTime::createFromFormat('Y-m-d H:i:s.u',$item['ElectronicTalon_insDT'])->format('d.m.Y');
                if(!empty($item['ElectronicTalon_ProcessedTime'])) $item['ElectronicTalon_ProcessedTime'] = DateTime::createFromFormat('H:i:s',$item['ElectronicTalon_ProcessedTime'])->format('H:i');
				array_push($newResult, $item);
			}
			return $newResult;
		} else return false;
	}
}
