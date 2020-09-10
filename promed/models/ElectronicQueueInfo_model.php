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

class ElectronicQueueInfo_model extends swModel {

	/**
	 * Удаление очереди
	 */
	function delete($data) {
		
		$this->load->model("ElectronicService_model", "es_model");
		$this->beginTransaction();

		$resp = $this->queryResult("
			select ElectronicService_id from v_ElectronicService with(nolock) where ElectronicQueueInfo_id = :ElectronicQueueInfo_id
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
			declare
				@Error_Code int,
				@Error_Msg varchar(4000);

			exec p_ElectronicQueueInfo_del
				@ElectronicQueueInfo_id = :ElectronicQueueInfo_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;

			select @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		$this->commitTransaction();
		return $this->queryResult($query, $data);
	}

	/**
	 * Включение/Выключение очереди
	 */
	function setElectronicQueueInfoIsOff($data) {
		$query = "
			Declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '',
				@ElectronicQueueInfo_IsOff bigint = :ElectronicQueueInfo_IsOff
			
			set nocount on
			begin try
				update ElectronicQueueInfo with (rowlock)
				set
					ElectronicQueueInfo_IsOff = @ElectronicQueueInfo_IsOff,
					ElectronicQueueInfo_updDT = dbo.tzGetDate(),
					pmUser_updID = :pmUser_id
				where
					ElectronicQueueInfo_id = :ElectronicQueueInfo_id
			end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
			set nocount off
			
			Select @ElectronicQueueInfo_IsOff as ElectronicQueueInfo_IsOff, @Error_Code as Error_Code, @Error_Message as Error_Msg
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
			$filter .= " and eqi.ElectronicQueueInfo_Name like '%'+:ElectronicQueueInfo_Name+'%'";
			$queryParams['ElectronicQueueInfo_Name'] = $data['ElectronicQueueInfo_Name'];
		}

		if (!empty($data['ElectronicQueueInfo_Nick'])) {
			$filter .= " and eqi.ElectronicQueueInfo_Nick like '%'+:ElectronicQueueInfo_Nick+'%'";
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
				eqi.ElectronicQueueInfo_id
				,eqi.Lpu_id
				,eqi.ElectronicQueueInfo_Code
				,eqi.ElectronicQueueInfo_Name
				,eqi.ElectronicQueueInfo_Nick
				,convert(varchar(10), eqi.ElectronicQueueInfo_begDate, 104) as ElectronicQueueInfo_begDate
				,convert(varchar(10), eqi.ElectronicQueueInfo_endDate, 104) as ElectronicQueueInfo_endDate
				,l.Lpu_Nick
				,ms.MedService_Nick
				,lb.LpuBuilding_Name
				,case when eqi.ElectronicQueueInfo_IsOff = 2 then 'false' else 'true' end as ElectronicQueueInfo_IsOn
				-- end select
			from
				-- from
				v_ElectronicQueueInfo eqi with(nolock)
				left join v_Lpu l with (nolock) on l.Lpu_id = eqi.Lpu_id
				left join v_MedService ms with (nolock) on ms.MedService_id = eqi.MedService_id
				left join v_LpuBuilding lb with (nolock) on lb.LpuBuilding_id = eqi.LpuBuilding_id
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
				EQI.ElectronicQueueInfo_id
				,EQI.Lpu_id
				,EQI.MedService_id
				,EQI.LpuBuilding_id
				,EQI.LpuSection_id
				,EQI.ElectronicQueueInfo_Code
				,EQI.ElectronicQueueInfo_Name
				,EQI.ElectronicQueueInfo_Nick
				,convert(varchar(10), EQI.ElectronicQueueInfo_begDate, 104) as ElectronicQueueInfo_begDate
				,convert(varchar(10), EQI.ElectronicQueueInfo_endDate, 104) as ElectronicQueueInfo_endDate
				,EQI.ElectronicQueueInfo_CallTimeSec
				,EQI.ElectronicQueueInfo_QueueTimeMin
				,EQI.ElectronicQueueInfo_LateTimeMin
				,EQI.ElectronicQueueInfo_PersCallDelTimeMin
				,EQI.ElectronicQueueInfo_CallCount
				,MST.MedServiceType_SysNick
				,case when EQI.ElectronicQueueInfo_IsOff = 2 then 'false' else 'true' end as ElectronicQueueInfo_IsOn
				,case when EQI.ElectronicQueueInfo_IsIdent = 2 then 'true' else 'false' end as ElectronicQueueInfo_IsIdent
				,case when EQI.ElectronicQueueInfo_IsCurDay = 2 then 'true' else 'false' end as ElectronicQueueInfo_IsCurDay
				,case when EQI.ElectronicQueueInfo_IsAutoReg = 2 then 'true' else 'false' end as ElectronicQueueInfo_IsAutoReg
				,case when EQI.ElectronicQueueInfo_IsNoTTGInfo = 2 then 'true' else 'false' end as ElectronicQueueInfo_IsNoTTGInfo
			from
				v_ElectronicQueueInfo EQI with(nolock)
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
			$field .= 'LB.LpuBuilding_Name, ';
			$join .= 'left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = EQI.LpuBuilding_id ';
		}

		$query = "
			select top 1
				{$field}
				EQI.ElectronicQueueInfo_id,
				EQI.ElectronicQueueInfo_Code,
				EQI.LpuBuilding_id
			from
				v_ElectronicQueueInfo EQI with(nolock)
				{$join}
			where
				(1=1)
				and EQI.Lpu_id = :Lpu_id
				and EQI.ElectronicQueueInfo_Code = :ElectronicQueueInfo_Code
				and (EQI.ElectronicQueueInfo_endDate is null OR EQI.ElectronicQueueInfo_endDate >= dbo.tzGetDate())
				{$filter}
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
			declare
				@ElectronicQueueInfo_id bigint = :ElectronicQueueInfo_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec {$procedure}
				@ElectronicQueueInfo_id = @ElectronicQueueInfo_id output,
				@Lpu_id = :Lpu_id,
				@MedService_id = :MedService_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuSection_id = :LpuSection_id,
				@ElectronicQueueInfo_Code = :ElectronicQueueInfo_Code,
				@ElectronicQueueInfo_Name = :ElectronicQueueInfo_Name,
				@ElectronicQueueInfo_Nick = :ElectronicQueueInfo_Nick,
				@ElectronicQueueInfo_begDate = :ElectronicQueueInfo_begDate,
				@ElectronicQueueInfo_endDate = :ElectronicQueueInfo_endDate,
				@ElectronicQueueInfo_CallTimeSec = :ElectronicQueueInfo_CallTimeSec,
				@ElectronicQueueInfo_QueueTimeMin = :ElectronicQueueInfo_QueueTimeMin,
				@ElectronicQueueInfo_LateTimeMin = :ElectronicQueueInfo_LateTimeMin,
				@ElectronicQueueInfo_IsOff = :ElectronicQueueInfo_IsOff,
				@ElectronicQueueInfo_CallCount = :ElectronicQueueInfo_CallCount,
				@ElectronicQueueInfo_IsIdent = :ElectronicQueueInfo_IsIdent,
				@ElectronicQueueInfo_IsCurDay = :ElectronicQueueInfo_IsCurDay,
				@ElectronicQueueInfo_IsAutoReg = :ElectronicQueueInfo_IsAutoReg,
				@ElectronicQueueInfo_PersCallDelTimeMin = :ElectronicQueueInfo_PersCallDelTimeMin,
				@ElectronicQueueInfo_IsNoTTGInfo = :ElectronicQueueInfo_IsNoTTGInfo,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @ElectronicQueueInfo_id as ElectronicQueueInfo_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
								'ElectronicService_Num' => $record->ElectronicService_Num,
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
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_ElectronicService_del
				@ElectronicService_id = :ElectronicService_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
				lpu.Lpu_id
				,lpu.Lpu_Name
				,lpu.Lpu_Nick
			from
				v_ElectronicQueueInfo eqi with(nolock)
				inner join v_Lpu_all lpu with(nolock) on lpu.Lpu_id = eqi.Lpu_id
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
				et.ElectronicTalon_id,
				et.ElectronicTalon_Num,
				et.Person_id,
				et.ElectronicTalonStatus_id,
				ets.ElectronicTalonStatus_Name,
				et.ElectronicService_id,
				ed.EvnQueue_id,
				ed.EvnStatus_id,
				ed.EvnDirection_Num,
				ed.EvnDirection_id,
				et.ElectronicTalon_insDT,
				eth.ElectronicTalon_ProcessedTime,
				et.ElectronicTreatment_id,
                etre.ElectronicTreatment_Name,
                cast(ElectronicTalon_insDT AS time) as ElectronicTalon_Time,
				cast(ElectronicTalon_insDT AS DATE) as ElectronicTalon_Date,
				case when et.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as pmUser_Name,
				et.pmUser_updId,
				et.pmUser_insId,
				rtrim(rtrim(p.Person_Surname) + ' ' + isnull(rtrim(p.Person_Firname),'') + ' ' + isnull(rtrim(p.Person_Secname),'')) as Person_FIO,
				case when ed.EvnDirection_isAuto != 2 then 'true' else 'false' end as IsEvnDirection
			FROM
				v_EvnDirection_all ed (nolock)
				inner join v_EvnQueue evnq with (nolock) on (evnq.EvnDirection_id = ed.EvnDirection_id)
				inner join v_ElectronicQueueInfo eqi with (nolock) on eqi.MedService_id = evnq.MedService_did
				inner join v_ElectronicTalon et with (nolock) on (et.EvnDirection_id = ed.EvnDirection_id or et.EvnDirection_uid = ed.EvnDirection_id)
				inner join v_ElectronicService es with (nolock) on es.ElectronicQueueInfo_id = eqi.ElectronicQueueInfo_id
				left join v_ElectronicTalonStatus ets (nolock) on ets.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
				left join v_pmUser pu with (nolock) on pu.pmUser_id = et.pmUser_updId
				left join v_PersonState_all p with (nolock) on p.Person_id = et.Person_id
				left join v_ElectronicTreatment etre (nolock) on etre.ElectronicTreatment_id = et.ElectronicTreatment_id
				outer apply (
					select top 1
						cast(etho.ElectronicTalonHist_insDT AS TIME) as ElectronicTalon_ProcessedTime
					from
						v_ElectronicTalonHist etho (nolock)
					where
						(1=1)
						and etho.ElectronicTalon_id = et.ElectronicTalon_id
						and etho.ElectronicTalonStatus_id = et.ElectronicTalonStatus_id
					order by ElectronicTalonHist_id desc
				) as eth
			WHERE
				{$filter}
				and ed.DirType_id = 24
				and es.ElectronicService_id = :ElectronicService_id
				-- показываем талоны которые еще никем не обработаны, либо только талоны своего пункта обслуживания
				and (et.ElectronicService_id is null or et.ElectronicService_id = :ElectronicService_id)
			";

		$response = $this->db->query($sql, $params);
//		echo getDebugSql($sql,$params);

		if (is_object($response)) {
			$result = $response->result('array');
			$newResult = array();
			foreach ($result as $item) {
				if(!empty($item['ElectronicTalon_insDT'])) $item['ElectronicTalon_Time'] = $item['ElectronicTalon_insDT']->format('H:i');
				if(!empty($item['ElectronicTalon_insDT'])) $item['ElectronicTalon_Date'] = $item['ElectronicTalon_insDT']->format('d.m.Y');
				if(!empty($item['ElectronicTalon_ProcessedTime'])) $item['ElectronicTalon_ProcessedTime'] = $item['ElectronicTalon_ProcessedTime']->format('H:i');
				array_push($newResult, $item);
			}
			return $newResult;
		} else return false;
	}


}