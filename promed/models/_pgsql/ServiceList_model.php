<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * ServiceList_model - модель для работы с внутренними сервисами для автоматизации действий промеда
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.01.2016
 */

class ServiceList_model extends SwPgModel
{
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Создание исключений по ошибкам
	 */
	function exceptionErrorHandler($errno, $errstr, $errfile, $errline) {
		switch ($errno) {
			case E_NOTICE:
			case E_USER_NOTICE:
				$errors = "Notice";
				break;
			case E_WARNING:
			case E_USER_WARNING:
				$errors = "Warning";
				break;
			case E_ERROR:
			case E_USER_ERROR:
				$errors = "Fatal Error";
				break;
			default:
				$errors = "Unknown Error";
				break;
		}

		$msg = sprintf("%s:  %s in %s on line %d", $errors, $errstr, $errfile, $errline);
		throw new ErrorException($msg, 0, $errno, $errfile, $errline);
	}

	/**
	 * Обработка Fatal Error
	 */
	function shutdownErrorHandler($func) {
		$error = error_get_last();

		if (!empty($error)) {
			switch ($error['type']) {
				case E_NOTICE:
				case E_USER_NOTICE:
					$type = "Notice";
					break;
				case E_WARNING:
				case E_USER_WARNING:
					$type = "Warning";
					break;
				case E_ERROR:
				case E_USER_ERROR:
					$type = "Fatal Error";
					break;
				default:
					$type = "Unknown Error";
					break;
			}

			$msg = sprintf("%s:  %s in %s on line %d", $type, $error['message'], $error['file'], $error['line']);

			//$func($msg);
			call_user_func($func, $msg);

			exit($error['type']);
		}
	}

	/**
	 * Получение списка сервисов
	 */
	function loadServiceListGrid($data) {
		$params = array();

		$response = $this->queryResult("
			select
				SL.ServiceList_id as \"ServiceList_id\",
				SL.ServiceList_Code as \"ServiceList_Code\",
				SL.ServiceList_Name as \"ServiceList_Name\"
			from stg.v_ServiceList SL
			where ServiceList_Code not in (16) 
		", $params);
		return $response;
	}

	/**
	 * @return array|false
	 */
	function loadServiceListPackageTypeList() {
		$params = array();
		$query = "
			select
				SLPT.ServiceListPackageType_id as \"ServiceListPackageType_id\",
				SLPT.ServiceListPackageType_Code as \"ServiceListPackageType_Code\",
				SLPT.ServiceListPackageType_Name as \"ServiceListPackageType_Name\",
				SLPT.ServiceListPackageType_Description as \"ServiceListPackageType_Description\"
			from stg.v_ServiceListPackageType SLPT
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * @return array|false
	 */
	function loadServiceListProcDataTypeList() {
		$params = array();
		$query = "
			select
				SLPDT.ServiceListProcDataType_id as \"ServiceListProcDataType_id\",
				SLPDT.ServiceListProcDataType_Code as \"ServiceListProcDataType_Code\",
				SLPDT.ServiceListProcDataType_Name as \"ServiceListProcDataType_Name\",
				SLPDT.ServiceListProcDataType_Description as \"ServiceListProcDataType_Description\"
			from stg.v_ServiceListProcDataType SLPDT
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка запусков сервиса
	 */
	function loadServiceListLogGrid($data) {
		if (!empty($data['ServiceList_Code']) && $data['ServiceList_Code'] == 11) {
			$params = array();
			$filters = array("1=1");

			if (!empty($data['Lpu_oid'])) {
				$filters[] = "FS.Lpu_id = :Lpu_oid";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			}
			if (isset($data['ServiceListLog_DateRange']) && !empty($data['ServiceListLog_DateRange'][0]) && !empty($data['ServiceListLog_DateRange'][1])) {
				$filters[] = "cast(FS.FRMOSession_begDT as date) between :ServiceListLog_begDateRange and :ServiceListLog_endDateRange";
				$params['ServiceListLog_begDateRange'] = $data['ServiceListLog_DateRange'][0];
				$params['ServiceListLog_endDateRange'] = $data['ServiceListLog_DateRange'][1];
			}

			$filters_str = implode("\nand ", $filters);

			$query = "
				select
					-- select
					FS.FRMOSession_id as \"ServiceListLog_id\",
					{$data['ServiceList_id']} as \"ServiceList_id\",
					to_char(FS.FRMOSession_begDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListLog_begDT\",
					to_char(FS.FRMOSession_endDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListLog_endDT\",
					SLR.ServiceListResult_id as \"ServiceListResult_id\",
					SLR.ServiceListResult_Code as \"ServiceListResult_Code\",
					case when SLR.ServiceListResult_id IN (1,2) then SLR.ServiceListResult_Name else FS.FRMOSession_Comment end as \"ServiceListResult_Name\",
					l.Lpu_Nick as \"Lpu_Nick\",
					FSE.cnt as \"ServiceListResult_ErrorCount\",
					lastst.FRMOSessionActionType_Code as \"FRMOSessionActionType_Code\"
					-- end select
				from
					-- from
					v_FRMOSession FS
					left join stg.v_ServiceListResult SLR on SLR.ServiceListResult_id = case when FS.FRMOSession_endDT is not null and FS.FRMOSession_success then 1 when FS.FRMOSession_endDT is null then 2 else 3 end
					left join v_Lpu L on l.Lpu_id = fs.Lpu_id
					left join lateral (
						select
							h.FRMOSessionHist_id,
							h.FRMOSessionHist_insDT
						from
							v_FRMOSessionHist h 
							inner join v_FRMOSessionActionType a on a.FRMOSessionActionType_id = h.FRMOSessionActionType_id
						where
							FS.FRMOSession_id = h.FRMOSession_id
							and a.FRMOSessionActionType_code in (60, 64)
						order by h.FRMOSessionHist_id desc
						limit 1
					) st on true
					left join lateral (
						select
							h.FRMOSessionHist_id,
							a.FRMOSessionActionType_Code
						from
							v_FRMOSessionHist h 
							inner join v_FRMOSessionActionType a on a.FRMOSessionActionType_id = h.FRMOSessionActionType_id
						where
							FS.FRMOSession_id = h.FRMOSession_id
						order by h.FRMOSessionHist_id desc
						limit 1
					) lastst on true
					left join lateral (
						select
							count(FSE.FRMOSessionError_id) as cnt
						from
							FRMOSessionError FSE
							inner join FRMOSessionHist FSH on FSE.FRMOSessionHist_id = FSH.FRMOSessionHist_id
						where
							FSE.FRMOSession_id = FS.FRMOSession_id
							and FSH.FRMOSessionHist_insDT >= coalesce(ST.FRMOSessionHist_insDT, FS.FRMOSession_insDT)
						limit 1
					) FSE on true
					-- end from
				where
					-- where
					{$filters_str}
					-- end where
				order by
					-- order by
					FS.FRMOSession_begDT desc
					-- end order by
			";
		} else {

			$params = array('ServiceList_id' => $data['ServiceList_id']);
			
			//Чтобы displan пакеты отображались в сервисе ТФОМС
			if (!empty($data['ServiceList_Code']) && $data['ServiceList_Code'] == 15) {
				//Получаем модель ТФОМСа
				$this->load->model('TFOMSAutoInteract_model');
				$serviceList_ids = [$data['ServiceList_id']];

				//Получаем доступные названия сервисов
				$serviceList_SysNick_str = "'".implode("','",$this->TFOMSAutoInteract_model->allowedServices)."'";
				
				$resultSQL = $this->queryResult("
					select SL.ServiceList_id \"ServiceList_id\"
					from stg.v_ServiceList SL
					where 
						SL.ServiceList_SysNick in ({$serviceList_SysNick_str})
						and SL.ServiceList_id != {$data['ServiceList_id']}
					limit 1
				");
				
				if(count($resultSQL)>0) {
					foreach ($resultSQL as $value) {
						$serviceList_ids[] = $value['ServiceList_id'];
					}
				}

				$serviceList_ids_str = "'".implode("','",$serviceList_ids)."'";

				$filters = array("SLL.ServiceList_id in ({$serviceList_ids_str})");
			} else {
				$filters = array("SLL.ServiceList_id = :ServiceList_id");
			}
			
			$packageFilters = array("SLP.ServiceListLog_id = SLL.ServiceListLog_id");

			if (!empty($data['Lpu_oid'])) {
				$filters[] = "exists(
					select * from stg.v_ServiceListPackage SLP
					where SLP.ServiceListLog_id = SLL.ServiceListLog_id and SLP.Lpu_id = :Lpu_oid
				)";
				$packageFilters[] = "SLP.Lpu_id = :Lpu_oid";
				$params['Lpu_oid'] = $data['Lpu_oid'];
			}
			if (isset($data['ServiceListLog_DateRange']) && !empty($data['ServiceListLog_DateRange'][0]) && !empty($data['ServiceListLog_DateRange'][1])) {
				$filters[] = "cast(SLL.ServiceListLog_begDT as date) between :ServiceListLog_begDateRange and :ServiceListLog_endDateRange";
				$params['ServiceListLog_begDateRange'] = $data['ServiceListLog_DateRange'][0];
				$params['ServiceListLog_endDateRange'] = $data['ServiceListLog_DateRange'][1];
			}
			if (!empty($data['ServiceListPackageType_id'])) {
				//Получаем модель ТФОМСа
				$this->load->model('TFOMSAutoInteract_model');
				//Получем из базы название типа пакета по ID
				$ServiceListPackageType_Name = $this->getFirstResultFromQuery("
					select slpt.ServiceListPackageType_Name 
					from stg.ServiceListPackageType slpt
					where slpt.ServiceListPackageType_id = :ServiceListPackageType_id
					limit 1
				", $data);
				//Получаем алиас типа по имени
				$ServiceListPackageTypeNameAlias = $this->TFOMSAutoInteract_model->packageTypeReverseMapper($ServiceListPackageType_Name);

				$ServiceListPackageTypeNames = [$ServiceListPackageType_Name];

				//Если полученный тип отличается от исходного, добавляем в массив
				if($ServiceListPackageTypeNameAlias != $ServiceListPackageType_Name){
					$ServiceListPackageTypeNames[] = $ServiceListPackageTypeNameAlias;
				}

				$ServiceListPackageTypeNames_str = "'".implode("','",$ServiceListPackageTypeNames)."'";

				$resultSQL = $this->queryResult("
					select slpt.ServiceListPackageType_id \"ServiceListPackageType_id\"
					from stg.ServiceListPackageType slpt
					where slpt.ServiceListPackageType_Name in ({$ServiceListPackageTypeNames_str})
				");

				$ServiceListPackageTypeIds = [];
				if(count($resultSQL)>0) {
					foreach ($resultSQL as $value) {
						$ServiceListPackageTypeIds[] = $value['ServiceListPackageType_id'];
					}
				}
				
				if(!count($ServiceListPackageTypeIds)) $ServiceListPackageTypeIds = [$data['ServiceListPackageType_id']];
				
				$ServiceListPackageTypeIds_str = "'".implode("','",$ServiceListPackageTypeIds)."'";
				
				
				$filters[] = "exists (
					select SLP.ServiceListPackage_id
					from stg.v_ServiceListPackage SLP
					where 
						SLP.ServiceListLog_id = SLL.ServiceListLog_id and 
						SLP.ServiceListPackageType_id in ({$ServiceListPackageTypeIds_str})
					limit 1
				)";
				$packageFilters[] = "SLP.ServiceListPackageType_id in ({$ServiceListPackageTypeIds_str})";
			}

			$filters_str = implode("\nand ", $filters);
			$packageFilters_str = implode("\nand ", $packageFilters);

			$query = "
				select
					-- select
					SLL.ServiceListLog_id as \"ServiceListLog_id\",
					SLL.ServiceList_id as \"ServiceList_id\",
					to_char(SLL.ServiceListLog_begDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListLog_begDT\",
					to_char(SLL.ServiceListLog_endDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListLog_endDT\",
					SLR.ServiceListResult_id as \"ServiceListResult_id\",
					SLR.ServiceListResult_Code as \"ServiceListResult_Code\",
					SLR.ServiceListResult_Name as \"ServiceListResult_Name\",
					SLP_AllCount.Value as \"ServiceListPackage_AllCount\",
					SLP_ErrorCount.Value as \"ServiceListPackage_ErrorCount\"
					-- end select
				from
					-- from
					stg.v_ServiceListLog SLL
					left join stg.v_ServiceListResult SLR on SLR.ServiceListResult_id = SLL.ServiceListResult_id
					left join lateral (
						select count(*) as Value
						from stg.v_ServiceListPackage SLP
						where {$packageFilters_str}
						limit 1
					) SLP_AllCount on true
					left join lateral (
						select count(*) as Value
						from stg.v_ServiceListPackage SLP
						inner join stg.v_PackageStatus PS on PS.PackageStatus_id = SLP.PackageStatus_id
						where {$packageFilters_str}
						and PS.PackageStatus_SysNick in ('ErrFormed','ErrSent','RejectedTFOMS')
						limit 1
					) SLP_ErrorCount on true
					-- end from
				where
					-- where
					{$filters_str}
					-- end where
				order by
					-- order by
					SLL.ServiceListLog_id desc
					-- end order by
			";
		}

		//echo getDebugSQL($query, $params);exit;
		$response = array();
		$count_result = $this->queryResult(getCountSQLPH($query),$params);
		if (!is_array($count_result)) {
			return false;
		} else {
			$response['totalCount']=$count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		if (!is_array($data_result)) {
			return false;
		} else {
			$response['data']=$data_result;
		}

		return $response;
	}

	/**
	 * Получить список сообщений по работе сервиса
	 */
	function loadServiceListDetailLogGrid($data) {
		$params = array('ServiceListLog_id' => $data['ServiceListLog_id']);

		$data['ServiceList_id'] = $this->getFirstResultFromQuery("
			select ServiceList_id as \"ServiceList_id\" 
			from stg.ServiceListLog 
			where ServiceListLog_id = :ServiceListLog_id limit 1
		", $params);

		if ( $data['ServiceList_id'] == 49 ) {
			return $this->_loadServiceListDetailLogGridFromFRMOSession($data);
		}

		if ( $data['ServiceList_id'] == 113 ) {
			return $this->_loadServiceListDetailLogGridRirMo($data);
		}

		$filters = array();
		$addSelect = '';
		$addJoin = '';

		// Поиск по тексту сообщения
		if ( ! empty($data['ServiceListDetailLog_Message']))
		{
			$filters[] = "SLDL.ServiceListDetailLog_Message iLIKE '%{$data['ServiceListDetailLog_Message']}%'";
			$params['ServiceListDetailLog_Message'] = $data['ServiceListDetailLog_Message'];
		}

		// Поиск по типу сообщения
		if ( ! empty($data['ServiceListLogType_id']))
		{
			$filters[] = 'SLDL.ServiceListLogType_id = :ServiceListLogType_id';
			$params['ServiceListLogType_id'] = $data['ServiceListLogType_id'];
		}
		
		// Поиск по пакету
		if (!empty($data['ServiceListPackage_id'])) {
			$filters[] = 'SLDL.ServiceListPackage_id = :ServiceListPackage_id';
			$params['ServiceListPackage_id'] = $data['ServiceListPackage_id'];
			
			$addSelect .= '
				,SLP.ServiceListPackage_GUID as "ServiceListPackage_GUID"
			';
			
			$addJoin .= '
				inner join stg.v_ServiceListPackage SLP on SLDL.ServiceListPackage_id = SLP.ServiceListPackage_id
			';
		}


		$filters = implode(" AND ", $filters);
		$filters = $filters ? " AND $filters" : null;

		/**
		 * Чтобы достать МО из события, надо узнать MedStaffFact_id и найти его в казахской таблице GetPersonHistoryWP.
		 * Для каждого класса события он достается по разному
		 */

		// Для ТАП и Стом ТАП - по первому посещению
		$selectMedStaffFactFromPL = "
			select
				MedstaffFact_id as MedstaffFact_id
			from
				v_EvnVizit EV
			where
				EV.EvnVizit_pid = SLDL.Evn_id
			order by
				EV.EvnVizit_setDT asc
			limit 1
		";

		// Для параклинической услуги напрямую или по среднему персоналу
		$selectMedStaffFactFromUslugaPar = "
			SELECT
				coalesce(EU.MedstaffFact_id, msfs.MedstaffFact_id) as MedstaffFact_id
			FROM 
				v_EvnUsluga_all EU
				left join lateral (
					select
						MedStaffFact_id as MedStaffFact_id
					from
						v_MedStaffFact msfs
					where
						msfs.MedPersonal_id = EU.MedPersonal_sid 
						and msfs.LpuSection_id = EU.LpuSection_uid
					limit 1
				) msfs on true
			WHERE
				EU.EvnUsluga_id = SLDL.Evn_id
			limit 1
		";

		// Для скринингового исследования детей и взрослых по услуге
		$selectMedStaffFactFromDisp = "
			select
				MedstaffFact_id as MedstaffFact_id
			from
				v_EvnUslugaDispDop EUDD
			WHERE
				EUDD.EvnUslugaDispDop_pid = SLDL.Evn_id 
				AND (
					CASE EC.EvnClass_id 
						WHEN 187 then CASE when EUDD.SurveyType_id = 118 then 1 ELSE 0 end -- 118 педиатр для детей
						WHEN 183 then 1 else 0
					END
				) = 1
			ORDER BY EUDD.EvnUslugaDispDop_setDT asc
			limit 1
		";

		// Выбираем номер документа для ТАП и Стом ТАП
		$selectDocNumFromPL = "
			SELECT
				EvnPL_NumCard as MedstaffFact_id
			FROM
				v_EvnPL EPL
			WHERE
				EPL.EvnPL_id = SLDL.Evn_id
			limit 1
		";

		if (getRegionNick() == 'kz') {
			$addSelect .= '
				,gph.Lpu_id as "Lpu_id"
				,L.Lpu_Nick as "Lpu_Nick"
			';
			$addJoin .= '
				-- достаем МО из стыковочной табл с казахстаном по MedStaffFact_id
				left join lateral (
					select
						gm.Lpu_id as Lpu_id
					from
						r101.v_GetPersonalHistoryWP gphwp
						inner join r101.v_GetPersonalWork gpw on gpw.GetPersonalHistory_id = gphwp.GetPersonalHistory_id
						left join r101.GetMO gm on gm.ID = gpw.MOID
					where
						gphwp.WorkPlace_id = Event.MedStaffFact_id
					order by
						gphwp.GetPersonalHistoryWP_insDT desc
					limit 1
				) gph ON true
				left join v_Lpu L on L.Lpu_id = gph.Lpu_id
			';
		} else {
			$addSelect .= '
				,null as "Lpu_id"
				,null as "Lpu_Nick"
			';
		}

		$query = "
			select
				-- select
				SLDL.ServiceListDetailLog_id as \"ServiceListDetailLog_id\",
				to_char(SLDL.ServiceListDetailLog_insDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListDetailLog_insDT\",
				SLDL.ServiceListDetailLog_Message as \"ServiceListDetailLog_Message\",
				SLLT.ServiceListLogType_id as \"ServiceListLogType_id\",
				SLLT.ServiceListLogType_Code as \"ServiceListLogType_Code\",
				SLLT.ServiceListLogType_Name as \"ServiceListLogType_Name\",
				SLDL.Evn_id as \"Evn_id\",
				Evn.Person_id as \"Person_id\",
				EC.EvnClass_id as \"EvnClass_id\",
				EC.EvnClass_SysNick as \"EvnClass_SysNick\",
				coalesce(ps.Person_SurName, '') || coalesce(' ' || SUBSTRING(ps.Person_FirName,1,1) || '.','') || coalesce(' ' || SUBSTRING(ps.Person_SecName,1,1) || '.','')
				as \"Person_ShortFio\",
				Event.DocNum as \"Event\"
				{$addSelect}
				-- end select
			from
				-- from
				stg.v_ServiceListDetailLog SLDL
				left join stg.v_ServiceListLogType SLLT on SLLT.ServiceListLogType_id = SLDL.ServiceListLogType_id
				left join v_Evn Evn on Evn.Evn_id = SLDL.Evn_id
				left join EvnClass EC on EC.EvnClass_id = Evn.EvnClass_id
				left join v_PersonState ps on Evn.Person_id = ps.Person_id
			  
				-- Достаем MedStaffFact_id и номер документа для ТАП в зависимости от типа события
				left join lateral (
					SELECT
					CASE EC.EvnClass_id
						WHEN 3 then ($selectMedStaffFactFromPL) 
						WHEN 6 then ($selectMedStaffFactFromPL) 
						WHEN 47 then ($selectMedStaffFactFromUslugaPar) 
						WHEN 183 then ($selectMedStaffFactFromDisp)
						WHEN 187 then ($selectMedStaffFactFromDisp) else NULL 
					END as MedStaffFact_id,
					CASE 
						WHEN EC.EvnClass_id in (3,6) then ($selectDocNumFromPL) ELSE SLDL.Evn_id::varchar -- если у события нет номера документа, то используем идентификатор события
					END as DocNum
				) Event on true
			
				{$addJoin}
				-- end from
			where
			 	-- where
				SLDL.ServiceListLog_id = :ServiceListLog_id
				{$filters}
				-- end where
			order by
				-- order by
				SLDL.ServiceListDetailLog_insDT desc,
				SLDL.ServiceListDetailLog_id desc
				-- end order by
		";

		$response = array();
		$count_result = $this->queryResult(getCountSQLPH($query),$params);
		if (!is_array($count_result)) {
			return false;
		} else {
			$response['totalCount'] = $count_result[0]['cnt'];
		}
		$data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		if (!is_array($data_result) || (isset($data_result[0]) && !empty($data_result[0]['Error_Msg']))) {
			return false;
		} else {
			// Преобразуем залогированное сообщения в формат из ТЗ

			foreach ($data_result as $key => $value)
			{
				$message = '';
				if (!empty($value['Evn_id'])) {
					// ФИО, номер документа, краткое название МО - в первой строке
					$message = ($value['Person_ShortFio'] ?: 'none') . ' / № ' . ($value['DocNum'] ?: 'none') . ' / ' . ($value['Lpu_Nick'] ?: 'none') . ' <br>';
				}

				// Если в базу сохранена json-строка то преобразуем в массив и рекурсивно проходимся по ним, отображая заголовки и данные с отступами
				$jsonData = json_decode($value['ServiceListDetailLog_Message'], true, 1024, JSON_BIGINT_AS_STRING);

				if (is_array($jsonData))
				{
					$message .= 'Json-ответ от сервиса:<br>' . recursiveArrayToString($jsonData);
				} else
				{
					// Если в базе была не json-строка, то просто присоединяем имеющийся текст к заголовку
					$message .= $value['ServiceListDetailLog_Message'];
				}

				// Сохраняем новый текст вместо старых данных из результата запроса
				$data_result[$key]['ServiceListDetailLog_Message'] = $message;
			}
			$response['data'] = $data_result;
		}

		return $response;
	}
	
	/**
	 * Получить список сообщений по работе сервиса импорта данных из ФРМО
	 */
	protected function _loadServiceListDetailLogGridFromFRMOSession($data) {
		$params = array('ServiceListLog_id' => $data['ServiceListLog_id']);
		$filters = array();

		// Поиск по тексту сообщения
		if ( !empty($data['ServiceListDetailLog_Message']) ) {
			$filters[] = "LD.Comment iLIKE '%{$data['ServiceListDetailLog_Message']}%'";
			$params['ServiceListDetailLog_Message'] = $data['ServiceListDetailLog_Message'];
		}

		// Поиск по типу сообщения
		if ( !empty($data['ServiceListLogType_id']) ) {
			$filters[] = 'LD.ServiceListLogType_id = :ServiceListLogType_id';
			$params['ServiceListLogType_id'] = $data['ServiceListLogType_id'];
		}


		$filters = implode(" AND ", $filters);
		$filters = $filters ? " AND $filters" : null;

		$query = "
			-- addit with
			with LogData as (
  				select
  					 L.Lpu_Nick
    				,pt.PassportToken_tid as Lpu_oid
     				,ses.FRMOSession_Comment as Comment -- ошибка сессии, если есть
        			,a.FRMOSessionActionType_descr
        			,lb.LpuBuildingPass_Name
        			,lu.LpuUnit_Name
        			,LS.LpuStaff_Num
        			,1 as ServiceListLogType_id
        			,ses.FRMOSession_insDT as insDT
				from dbo.FRMOSession ses 
					inner join v_Lpu L on L.Lpu_id = ses.Lpu_id
					inner join fed.v_PassportToken pt on L.Lpu_id = pt.Lpu_id
					inner join dbo.FRMOSessionHist h on ses.FRMOSession_id = h.FRMOSession_id 
					inner join dbo.FRMOSessionActionType a on a.FRMOSessionActionType_id = h.FRMOSessionActionType_id --and a.FRMOSessionActionType_code in (1,3,8,13,20)
					left JOIN dbo.v_LpuBuildingPass AS lb ON lb.LpuBuildingPass_id = h.LpuBuildingPass_id
					left JOIN dbo.v_LpuUnit AS lu ON lu.LpuUnit_id = h.LpuUnit_id
					left join v_LpuStaff LS on LS.LpuStaff_Num = h.LpuStaff_Num
				where ses.ServiceListLog_id = :ServiceListLog_id

				union all

  				select
  					 null as Lpu_Nick
    				,null as Lpu_oid
     				,SLDL.ServiceListDetailLog_Message as Comment -- ошибка сессии, если есть
        			,null as FRMOSessionActionType_descr
        			,null as LpuBuildingPass_Name
        			,null as LpuUnit_Name
        			,null as LpuStaff_Num
        			,SLDL.ServiceListLogType_id
        			,SLDL.ServiceListDetailLog_insDT as insDT
				from stg.v_ServiceListDetailLog SLDL
				where SLDL.ServiceListLog_id = :ServiceListLog_id

				union all

				select 
					 L.Lpu_Nick
    				,pt.PassportToken_tid as Lpu_oid
					,coalesce(fet.FRMOSessionErrorType_Name, fe.FRMOSessionError_Message) as Comment
					,null as FRMOSessionActionType_descr
					,lb.LpuBuildingPass_Name
					,lu.LpuUnit_Name
					,fe.LpuStaff_Num
					,2 as ServiceListLogType_id
					,fe.FRMOSessionError_insDT as insDT
				from dbo.FRMOSessionError fe
					inner join dbo.FRMOSession ses on ses.FRMOSession_id = fe.FRMOSession_id
					inner join v_Lpu L on L.Lpu_id = ses.Lpu_id
					inner join fed.v_PassportToken pt on L.Lpu_id = pt.Lpu_id
					left join dbo.FRMOSessionErrorType fet on fe.FRMOSessionErrorType_id = fet.FRMOSessionErrorType_id
					left JOIN dbo.v_LpuBuildingPass AS lb ON lb.LpuBuildingPass_id = fe.LpuBuildingPass_id
					left JOIN dbo.v_LpuUnit AS lu ON lu.LpuUnit_id = fe.LpuUnit_id
				WHERE (1 = 1) and ses.ServiceListLog_id = :ServiceListLog_id
        	)
			-- end addit with

			select
				-- select
				ROW_NUMBER() OVER (ORDER BY insDT) as \"ServiceListDetailLog_id\",
				to_char(LD.insDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListDetailLog_insDT\",
				case when LD.Lpu_oid is not null then '<div>ОИД МО: ' || LD.Lpu_oid || '</div>' else '' end
					|| case when LD.LpuBuildingPass_Name is not null then '<div>Здание: ' || LD.LpuBuildingPass_Name || '</div> ' else '' end
					|| case when LD.LpuUnit_Name is not null then '<div>Подразделение: ' || LD.LpuUnit_Name || '</div> ' else '' end
					|| '<div>' || coalesce(LD.Comment, '') || '</div>' as \"ServiceListDetailLog_Message\",
				SLLT.ServiceListLogType_id as \"ServiceListLogType_id\",
				SLLT.ServiceListLogType_Code as \"ServiceListLogType_Code\",
				SLLT.ServiceListLogType_Name as \"ServiceListLogType_Name\"
				-- end select
			from
				-- from
				LogData LD
				left join stg.v_ServiceListLogType SLLT on SLLT.ServiceListLogType_id = LD.ServiceListLogType_id
				-- end from
			where
			 	-- where
				(1 = 1)
				{$filters}
				-- end where
			order by
				-- order by
				ServiceListDetailLog_id desc
				-- end order by
		";

		$response = array();
		$count_result = $this->queryResult(getCountSQLPH($query),$params);
		if (!is_array($count_result)) {
			return false;
		} else {
			$response['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		if (!is_array($data_result) || (isset($data_result[0]) && !empty($data_result[0]['Error_Msg']))) {
			return false;
		}

		$response['data'] = $data_result;

		return $response;
	}

	/**
	 * Экспорт в РИР МО
	 * @param $data
	 * @return array|bool
	 */
	private function _loadServiceListDetailLogGridRirMo($data) {
		$params = array('ServiceListLog_id' => $data['ServiceListLog_id']);

		$filters = [];

		// Поиск по тексту сообщения
		if ( ! empty($data['ServiceListDetailLog_Message'])) {
			$filters[] = "SLDL.ServiceListDetailLog_Message iLIKE '%{$data['ServiceListDetailLog_Message']}%'";
			$params['ServiceListDetailLog_Message'] = $data['ServiceListDetailLog_Message'];
		}

		// Поиск по типу сообщения
		if ( ! empty($data['ServiceListLogType_id'])) {
			$filters[] = 'SLDL.ServiceListLogType_id = :ServiceListLogType_id';
			$params['ServiceListLogType_id'] = $data['ServiceListLogType_id'];
		}

		$filters = implode(" AND ", $filters);
		$filters = $filters ? " AND $filters" : null;

		$query = "
			select
				-- select
				SLDL.ServiceListDetailLog_id,
				SLDL.ServiceListPackage_id,
				convert(varchar(10), SLDL.ServiceListDetailLog_insDT, 104)+' '+convert(varchar(9), SLDL.ServiceListDetailLog_insDT, 108) as ServiceListDetailLog_insDT,
				SLDL.ServiceListDetailLog_Message,
				SLLT.ServiceListLogType_id,
				SLLT.ServiceListLogType_Code,
				SLLT.ServiceListLogType_Name,
				SLP.ServiceListPackage_GUID,
				SLPT.ServiceListPackageType_Name,
				SLPT.ServiceListPackageType_Description,
				SP_Request.ServicePackage_Data as RequestData
				-- end select
			from
				-- from
				stg.v_ServiceListDetailLog SLDL with(nolock)
				left join stg.v_ServiceListPackage SLP with(nolock) on SLP.ServiceListPackage_id = SLDL.ServiceListPackage_id
				left join stg.v_ServiceListPackageType SLPT with(nolock) on SLPT.ServiceListPackageType_id = SLP.ServiceListPackageType_id
				left join stg.v_ServiceListLogType SLLT with(nolock) on SLLT.ServiceListLogType_id = SLDL.ServiceListLogType_id
				outer apply (
					select top 1 SP.ServicePackage_Data
					from stg.v_ServicePackage SP with(nolock)
					where SP.ServiceListPackage_id = SLP.ServiceListPackage_id
					and (SP.ServicePackage_IsResp is null or SP.ServicePackage_IsResp = 1)
				) SP_Request
				-- end from
			where
			 	-- where
				SLDL.ServiceListLog_id = :ServiceListLog_id
				{$filters}
				-- end where
			order by
				-- order by
				SLDL.ServiceListDetailLog_insDT desc,
				SLDL.ServiceListDetailLog_id desc
				-- end order by
		";

		$response = [];

		$count_result = $this->queryResult(getCountSQLPH($query), $params);

		if (!is_array($count_result)) {
			return false;
		} else {
			$response['totalCount'] = $count_result[0]['cnt'];
		}

		$this->load->helper('xml');

		$process = function($data, $result = '') use(&$process) {
			$i=0;
			foreach($data as $key => $value) {
				//Нужно четкое сравнение
				if ($key === 'ns1:ZAP') {
					$value = html_entity_decode($value);
					$value = XmlToArray("<ZAP>$value</ZAP>");
					$result = $process($value, $result);
				} elseif (is_array($value)) {
					$result = $process($value, $result);
				} else {
					$result .= "{$key}: {$value}<br/>";
				}
				
				if ($i+1 == count($data)){
					$result .= "<br/>";
				}
				$i++;
			}
			return $result;
		};

		$data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		if (!is_array($data_result) || (isset($data_result[0]) && !empty($data_result[0]['Error_Msg']))) {
			return false;
		} else {
			foreach($data_result as &$item) {
				$message = !empty($item['ServiceListDetailLog_Message']) ? $item['ServiceListDetailLog_Message'] : 'Успешно';
				$jsonData = json_decode($item['ServiceListDetailLog_Message'], true, 1024, JSON_BIGINT_AS_STRING);
				if (is_array($jsonData)) {
					/*if (isset($jsonData['STR_OUT'])) {
						$message = $jsonData['STR_OUT'];
					} else*/ {
						$message = 'Ответ от сервиса:<br>' . recursiveArrayToString($jsonData);
					}
				}
				$item['ServiceListProcDataType_Result'] = $message;

				if (!empty($item['RequestData'])) {
					$item['RequestData'] = $process(json_decode($item['RequestData'], true));
				}
			}
			$response['data'] = $data_result;
		}

		return $response;
	}

	/**
	 * Получить список пакетов, обработанных сервисом
	 */
	function loadServiceListPackageGrid($data) {
		$params = array('ServiceListLog_id' => $data['ServiceListLog_id']);
		$filters = array("SLP.ServiceListLog_id = :ServiceListLog_id");

		if (!empty($data['Lpu_oid'])) {
			$filters[] = "SLP.Lpu_id = :Lpu_oid";
			$params['Lpu_oid'] = $data['Lpu_oid'];
		}
		
		if (!empty($data['ServiceListPackageType_id'])) {
			//Получаем модель ТФОМСа
			$this->load->model('TFOMSAutoInteract_model');
			//Получем из базы название типа пакета по ID
			$ServiceListPackageType_Name = $this->getFirstResultFromQuery("
					select slpt.ServiceListPackageType_Name 
					from stg.ServiceListPackageType slpt 
					where slpt.ServiceListPackageType_id = :ServiceListPackageType_id
					limit 1
				", $data);
			//Получаем алиас типа по имени
			$ServiceListPackageTypeNameAlias = $this->TFOMSAutoInteract_model->packageTypeReverseMapper($ServiceListPackageType_Name);

			$ServiceListPackageTypeNames = [$ServiceListPackageType_Name];

			//Если полученный тип отличается от исходного, добавляем в массив
			if($ServiceListPackageTypeNameAlias != $ServiceListPackageType_Name){
				$ServiceListPackageTypeNames[] = $ServiceListPackageTypeNameAlias;
			}

			$ServiceListPackageTypeNames_str = "'".implode("','",$ServiceListPackageTypeNames)."'";

			$resultSQL = $this->queryResult("
					select slpt.ServiceListPackageType_id \"ServiceListPackageType_id\"
					from stg.ServiceListPackageType slpt
					where slpt.ServiceListPackageType_Name in ({$ServiceListPackageTypeNames_str})
				");

			$ServiceListPackageTypeIds = [];
			if(count($resultSQL)>0) {
				foreach ($resultSQL as $value) {
					$ServiceListPackageTypeIds[] = $value['ServiceListPackageType_id'];
				}
			}

			if(!count($ServiceListPackageTypeIds)) $ServiceListPackageTypeIds = [$data['ServiceListPackageType_id']];

			$ServiceListPackageTypeIds_str = "'".implode("','",$ServiceListPackageTypeIds)."'";
			$filters[] = "SLP.ServiceListPackageType_id in ({$ServiceListPackageTypeIds_str})";
			$params['ServiceListPackageType_id'] = $data['ServiceListPackageType_id'];
		}
		
		if (!empty($data['PackageStatus_id'])) {
			$filters[] = "SLP.PackageStatus_id = :PackageStatus_id";
			$params['PackageStatus_id'] = $data['PackageStatus_id'];
		}
		
		if ($data['ServiceListLogErrorType_id'] == 2 && getRegionNick() == 'kareliya') {
			$filters[] = "(
				SLDL.ServiceListProcDataType_Result ilike '%This element is not expected%' or 
				SLDL.ServiceListProcDataType_Result ilike '%Missing child element%'
			)";
		}
		
		$add_fields = '';
		$add_join = '';
		
		$ServiceList_Code = $this->getFirstResultFromQuery("
			select sl.ServiceList_Code as \"ServiceList_Code\" 
			from stg.v_ServiceList sl
			inner join stg.v_ServiceListLog sll on sl.ServiceList_id = sll.ServiceList_id
			where sll.ServiceListLog_id = :ServiceListLog_id
			limit 1
		", $data);
		
		if (in_array($ServiceList_Code, [15, 16])) {
			$add_fields = "
				,SLDL.ServiceListDetailLog_Message as ServiceListProcDataType_Result
			";
			$add_join = "
				left join lateral (
					select SLDL.*
					from stg.v_ServiceListDetailLog SLDL
					where SLDL.ServiceListPackage_id = SLP.ServiceListPackage_id
					order by SLDL.ServiceListDetailLog_id desc
					limit 1
				) SLDL on true
			";
		}
		
		if (in_array($ServiceList_Code, [85,86])) {
			$add_fields = "
				,case 
					when coalesce(SLDL.ServiceListDetailLog_Message, '') = '' then 'Успешно'
					else 'Ошибка' 
				end as \"ServiceListProcDataType_Result\"
				,SLDL.ServiceListDetailLog_Message as \"ServiceListDetailLog_Message\"
			";
			$add_join = "
				left join stg.v_ServiceListDetailLog SLDL on SLDL.ServiceListPackage_id = SLP.ServiceListPackage_id
			";
		}

		$filters_str = implode("\nand ", $filters);

		$query = "
			select
				-- select
				SLP.ServiceListPackage_id as \"ServiceListPackage_id\",
				SLP.ServiceListPackage_ObjectID as \"ServiceListPackage_ObjectID\",
				SLP.ServiceListPackage_GUID as \"ServiceListPackage_GUID\",
				to_char(SLP.ServiceListPackage_insDT, 'DD.MM.YYYY HH24:MI:SS') as \"ServiceListPackage_insDT\",
				SLP.ServiceListPackage_IsNotSend as \"ServiceListPackage_IsNotSend\",
				SLPT.ServiceListPackageType_Description as \"ServiceListPackageType_Description\",
				SLPDT.ServiceListProcDataType_Name as \"ServiceListProcDataType_Name\",
				PRT.PackageRouteType_id as \"PackageRouteType_id\",
				PRT.PackageRouteType_Name as \"PackageRouteType_Name\",
				PRT.PackageRouteType_SysNick as \"PackageRouteType_SysNick\",
				PS.PackageStatus_id as \"PackageStatus_id\",
				PS.PackageStatus_Name as \"PackageStatus_Name\",
				PS.PackageStatus_SysNick as \"PackageStatus_SysNick\",
				SP_Request.ServicePackage_Data as \"RequestData\",
				SP_Response.ServicePackage_Data as \"ResponseData\"
				{$add_fields}
				-- end select
			from
				-- from
				stg.v_ServiceListPackage SLP
				left join stg.v_ServiceListPackageType SLPT on SLPT.ServiceListPackageType_id = SLP.ServiceListPackageType_id
				left join stg.v_ServiceListProcDataType SLPDT on SLPDT.ServiceListProcDataType_id = SLP.ServiceListProcDataType_id
				left join stg.v_PackageRouteType PRT on PRT.PackageRouteType_id = SLPT.PackageRouteType_id
				left join stg.v_PackageStatus PS on PS.PackageStatus_id = SLP.PackageStatus_id
				{$add_join}
				left join lateral (
					select SP.ServicePackage_Data
					from stg.v_ServicePackage SP
					where SP.ServiceListPackage_id = SLP.ServiceListPackage_id
					and (SP.ServicePackage_IsResp is null or SP.ServicePackage_IsResp = 1)
					limit 1
				) SP_Request on true
				left join lateral (
					select SP.ServicePackage_Data
					from stg.v_ServicePackage SP
					where SP.ServiceListPackage_id = SLP.ServiceListPackage_id
					and SP.ServicePackage_IsResp = 2
					limit 1
				) SP_Response on true
				-- end from
			where
			 	-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				SLP.ServiceListPackage_insDT
				-- end order by
		";

		$response = array();
		$count_result = $this->queryResult(getCountSQLPH($query),$params);
		if (!is_array($count_result)) {
			return false;
		} else {
			$response['totalCount'] = $count_result[0]['cnt'];
		}
		$data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		if (!is_array($data_result) || (isset($data_result[0]) && !empty($data_result[0]['Error_Msg']))) {
			return false;
		} else {
			$response['data'] = $data_result;
		}

		if (in_array($ServiceList_Code, [85,86])) {
			foreach($response['data'] as &$dt) {
				if (empty($dt['ServiceListDetailLog_Message'])) continue;
				
				$msg = json_decode($dt['ServiceListDetailLog_Message'], true);
				$dt['ServiceListDetailLog_Message'] = $msg['msg'];
				$dt['ServiceListDetailLog_File'] = $msg['file'];
			}
		}

		$process = function($data, $result = '') use(&$process) {
		    foreach($data as $key => $value) {
                if (is_array($value)) {
                    $result = $process($value, $result);
                } else {
                    $result .= "{$key}: {$value}<br/>";
                }
            }
		    return $result;
        };

		foreach($response['data'] as &$item) {
		    if (!empty($item['RequestData'])) {
		        $item['RequestData'] = $process(json_decode($item['RequestData'], true));
            }
		    if (!empty($item['ResponseData'])) {
                $item['ResponseData'] = $process(json_decode($item['ResponseData'], true));
            }
        }

		return $response;
	}

	/**
	 * Добавление строки в пакет
	 * @param $data
	 * @return array|false
	 */
	function addServiceListPackage($data) {
		$params = array(
			'ServiceListPackage_id' => null,
			'ServiceListLog_id' => $data['ServiceListLog_id'],
			'ServiceListPackage_ObjectName' => $data['ServiceListPackage_ObjectName'],
			'ServiceListPackage_ObjectID' => $data['ServiceListPackage_ObjectID'],
			'ServiceListPackage_GUID' => !empty($data['ServiceListPackage_GUID'])?$data['ServiceListPackage_GUID']:null,
			'Lpu_id' => !empty($data['Lpu_oid'])?$data['Lpu_oid']:null,
			'ServiceListPackageType_id' => !empty($data['ServiceListPackageType_id'])?$data['ServiceListPackageType_id']:null,
			'ServiceListProcDataType_id' => !empty($data['ServiceListProcDataType_id'])?$data['ServiceListProcDataType_id']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			select
				ServiceListPackage_id as \"ServiceListPackage_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from stg.p_ServiceListPackage_ins (
				ServiceListPackage_id := :ServiceListPackage_id,
				ServiceListLog_id := :ServiceListLog_id,
				ServiceListPackage_ObjectName := :ServiceListPackage_ObjectName,
				ServiceListPackage_ObjectID := :ServiceListPackage_ObjectID,
				ServiceListPackage_GUID := :ServiceListPackage_GUID,
				Lpu_id := :Lpu_id,
				ServiceListPackageType_id := :ServiceListPackageType_id,
				ServiceListProcDataType_id := :ServiceListProcDataType_id,
				pmUser_id := :pmUser_id
			)
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при добавлении строки в пакет');
		}

		if (!empty($data['ServicePackage_Data'])) {
			$resp = $this->addServicePackage([
				'ServiceListPackage_id' => $response[0]['ServiceListPackage_id'],
				'ServicePackage_Data' => $data['ServicePackage_Data'],
				'ServicePackage_IsResp' => $data['ServicePackage_IsResp'] ?? 1,
				'pmUser_id' => $data['pmUser_id']
			]);
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
		}

		return $response;
	}

	/**
	 * Изменения статуса пакета
	 * @param array $data
	 * @return array
	 */
	function setServiceListPackageStatus($data) {
		$params = [
			'ServiceListPackage_id' => $data['ServiceListPackage_id'],
			'PackageStatus_SysNick' => $data['PackageStatus_SysNick'],
			'pmUser_id' => $data['pmUser_id'],
		];
		
		$query = "
			update stg.ServiceListPackage
			set PackageStatus_id = (
				select PackageStatus_id
				from stg.v_PackageStatus
				where PackageStatus_SysNick = :PackageStatus_SysNick
			)
			where ServiceListPackage_id = :ServiceListPackage_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при изменении статуса пакета');
		}
		return $resp;
	}

	/**
	 * Удаление строки из пакета для обработки сервисом
	 */
	function deleteServiceListPackage($data) {
		$params = array('ServiceListPackage_id' => $data['ServiceListPackage_id']);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from stg.p_ServiceListPackage_del (
				ServiceListPackage_id := :ServiceListPackage_id
			)
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('', 'Ошибка при удалении строки из пакета для обработки сервисом');
		}

		return $response;
	}

	/**
	 * Удаление всех строк из пакета
	 */
	function deleteAllServiceListPackage($data) {
		$params = array('ServiceListLog_id' => $data['ServiceListLog_id']);
		$query = "
			delete stg.ServiceListPackage 
			where ServiceListLog_id = :ServiceListLog_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('', 'Ошибка при удалении строк из пакета для обработки сервисом');
		}

		return $response;
	}

	/**
	 * Генерация хэша пакета для определения изменений данных
	 * @param array $data
	 * @return array|null
	 */
	function generationDataHash($data, $unsetKeys = []) {

		if(isset($_REQUEST['getDebug'])){
			echo "<pre>INPUT:".print_r($data,1);
		}

		if (!is_array($data)) return null;

		// Рекурсивное удаление элемента в масииве по ключу
		$array_recurse_unset = function(&$array, $unsetKeys) use(&$array_recurse_unset){
			if(is_array($array)){
				foreach ($array as $key => &$value) {
					if (is_array($value)) {
						$array_recurse_unset($value, $unsetKeys);
					} elseif (in_array($key, $unsetKeys)) {
						unset($array[$key]);
					}
				}
			}
		};

		// Рекурсивное удаление элемента в масииве по ключу
		$array_recurse_unset_by_key = function(&$array, $searchKeys = [], $unsetKeys = []) use(&$array_recurse_unset_by_key, &$array_recurse_unset){
			if(is_array($array)){
				foreach ($array as $key => &$value) {
					if (in_array($key, $searchKeys)) {
						$array_recurse_unset($value, $unsetKeys);
					} else {
						$array_recurse_unset_by_key($value, $searchKeys, $unsetKeys);
					}
				}
			}
		};

		//Поля для исключения, т.к. они всегда уникальны
		if (empty($unsetKeys)) {
			$unsetKeys = ["DATA", "DATE", "ID", "GIUD", "MESSAGE_ID"];
		}

		//ключи Заголовоков пакета
		$packageHeaderKeys = ["ZGLV", "HEADER"];

		$array_recurse_unset_by_key($data, $packageHeaderKeys, $unsetKeys);

		if(!empty($data)) {
			$hash = md5(json_encode($data));

			if(isset($_REQUEST['getDebug'])){
				echo "<pre>generationDataHash():".PHP_EOL;
				echo "OUTPUT:".print_r($data,1);
				echo "HASH: ".$hash;
			}
			return $hash;
		}

		return null;
	}

	function addServicePackage($data) {
		$params = array(
			'ServicePackage_id' => null,
			'ServiceListPackage_id' => $data['ServiceListPackage_id'],
			'ServicePackage_Data' => $data['ServicePackage_Data'],
			'ServicePackage_IsResp' => $data['ServicePackage_IsResp'] ?? 1,
			'pmUser_id' => $data['pmUser_id']
		);

		$this->load->helper('xml');

		$params['ServicePackage_Data'] = xml_to_array($params['ServicePackage_Data']);
		if (is_array($params['ServicePackage_Data'])) {
			$params['ServicePackage_DataHash'] = $this->generationDataHash($params['ServicePackage_Data']);
			$params['ServicePackage_Data'] = json_encode($params['ServicePackage_Data']);
		}

		$query = "
	        select
	        	ServicePackage_id as \"ServicePackage_id\",
	        	Error_Code as \"Error_Code\",
	        	Error_Message as \"Error_Msg\"
			from stg.p_ServicePackage_ins(
				ServiceListPackage_id := :ServiceListPackage_id,
				ServicePackage_Data := :ServicePackage_Data,
				ServicePackage_DataHash := :ServicePackage_DataHash,
				ServicePackage_IsResp := :ServicePackage_IsResp,
				pmUser_id := :pmUser_id
			)
	    ";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('','Ошибка при добавлении данных пакета');
		}
		return $response;
	}

	/**
	 * Сохранение инофрмации о работе сервиса
	 */
	function saveServiceListLog($data) {
		$params = array(
			'ServiceListLog_id' => !empty($data['ServiceListLog_id'])?$data['ServiceListLog_id']:null,
			'ServiceList_id' => $data['ServiceList_id'],
			'ServiceListLog_begDT' => $data['ServiceListLog_begDT'],
			'ServiceListLog_endDT' => !empty($data['ServiceListLog_endDT'])?$data['ServiceListLog_endDT']:null,
			'ServiceListResult_id' => !empty($data['ServiceListResult_id'])?$data['ServiceListResult_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['ServiceListLog_id'])) {
			$procedure = 'stg.p_ServiceListLog_ins';
		} else {
			$procedure = 'stg.p_ServiceListLog_upd';
		}

		$query = "
			select
				ServiceListLog_id as \"ServiceListLog_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				ServiceListLog_id := :ServiceListLog_id,
				ServiceList_id := :ServiceList_id,
				ServiceListLog_begDT := :ServiceListLog_begDT,
				ServiceListLog_endDT := :ServiceListLog_endDT,
				ServiceListResult_id := :ServiceListResult_id,
				pmUser_id := :pmUser_id
			)	
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('', 'Ошибка при сохранени информации о работе сервиса');
		}

		return $response;
	}

	/**
	 * @param array $data
	 * @return int|false|null
	 */
	function findServiceListPackageId($data) {
		$filters = '';
		
		$params = [
			'ServiceList_id' =>  $data['ServiceList_id'],
			'ServiceListPackageType_Name' => $data['ServiceListPackageType_Name'],
			'ServiceListPackage_ObjectID' => $data['ServiceListPackage_Object_id']
		];
		
		
		if (!empty($data['PackageStatus_SysNick'])) {
			$params['PackageStatus_SysNick'] = $data['PackageStatus_SysNick'];
			$filters = " and PS.PackageStatus_SysNick = :PackageStatus_SysNick";
		}
		
		$query = "
			select
				SLP.ServiceListPackage_id as \"ServiceListPackage_id\"
			from
				stg.v_ServiceListPackage SLP
				inner join stg.v_ServiceListLog SLL on SLL.ServiceListLog_id = SLP.ServiceListLog_id
				inner join stg.v_ServiceListPackageType SLPT on SLPT.ServiceListPackageType_id = SLP.ServiceListPackageType_id
				left join stg.v_PackageStatus PS on PS.PackageStatus_id = SLP.PackageStatus_id
			where
				SLL.ServiceList_id = :ServiceList_id
				and SLPT.ServiceListPackageType_Name = :ServiceListPackageType_Name
				and SLP.ServiceListPackage_ObjectID = :ServiceListPackage_ObjectID
				{$filters}
			order by
				SLP.ServiceListPackage_insDT desc
			limit 1
		";
		
		return $this->getFirstResultFromQuery($query, $params, true);
	}

	/**
	 * Сохранении записи в детальный лог работы сервиса
	 */
	function saveServiceListDetailLog($data) {
		$params = array(
			'ServiceListDetailLog_id' => !empty($data['ServiceListDetailLog_id'])?$data['ServiceListDetailLog_id']:null,
			'ServiceListLog_id' => $data['ServiceListLog_id'],
			'ServiceListPackage_id' => !empty($data['ServiceListPackage_id'])?$data['ServiceListPackage_id']:null,
			'ServiceListLogType_id' => $data['ServiceListLogType_id'],
			'ServiceListDetailLog_Message' => $data['ServiceListDetailLog_Message'],
			'Evn_id' => isset($data['Evn_id']) ? $data['Evn_id'] : null ,
			'pmUser_id' => $data['pmUser_id']
		);

		if (empty($data['ServiceListDetailLog_id'])) {
			$procedure = 'stg.p_ServiceListDetailLog_ins';
		} else {
			$procedure = 'stg.p_ServiceListDetailLog_ins';
		}

		$query = "		
			select
				ServiceListDetailLog_id as \"ServiceListDetailLog_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				ServiceListDetailLog_id := :ServiceListDetailLog_id,
				ServiceListLog_id := :ServiceListLog_id,
				ServiceListPackage_id := :ServiceListPackage_id,
				ServiceListLogType_id := :ServiceListLogType_id,
				ServiceListDetailLog_Message := :ServiceListDetailLog_Message,
				Evn_id := :Evn_id
			)
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return $this->createError('', 'Ошибка при сохранении записи в детальный лог работы сервиса');
		}

		return $response;
	}

	/**
	 * Сохранение ссылки на запуск сервиса в записи пакета
	 */
	function setServiceListLogInPackage($data) {
		$params = array(
			'ServiceListPackage_id' => $data['ServiceListPackage_id'],
			'ServiceListLog_id' => $data['ServiceListLog_id'],
		);
		$query = "
			update stg.ServiceListPackage
			set ServiceListLog_id = :ServiceListLog_id
			where ServiceListPackage_id = :ServiceListPackage_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($query)) {
			return $this->createError('', 'Ошибка при привязки лога к записи в пакете');
		}
		return $resp;
	}

	/**
	 * Сохранение ссылки на пакет идентификации в записи человека на идентификацию
	 */
	function setPersonIdentPackageId($data) {
		$params = array(
			'PersonIdentPackage_id' => $data['PersonIdentPackage_id'],
			'PersonIdentPackagePos_id' => $data['PersonIdentPackagePos_id'],
		);
		$query = "
			update PersonIdentPackagePos 
			set PersonIdentPackage_id = :PersonIdentPackage_id 
			where PersonIdentPackagePos_id = :PersonIdentPackagePos_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('', 'Ошибка при сохранении записи человека на идентификцию в пакете идентификации');
		}
		return $resp;
	}

	/**
	 * Запуск сервиса для проверки статусов заявлений в РПН
	 */
	function startCheckPersonCardAttachStatusService() {
		$flag = true;
		$ServiceList_id = 3;

		$ServiceListLog_id = $this->getFirstResultFromQuery("
			select ServiceListLog_id as \"ServiceListLog_id\"
			from stg.v_ServiceListLog
			where ServiceList_id = :ServiceList_id and ServiceListResult_id = 2
			limit 1
		", array('ServiceList_id' => $ServiceList_id));

		$begDT = date('Y-m-d H:i:s');

		if (empty($ServiceListLog_id)) {
			$resp = $this->saveServiceListLog(array(
				'ServiceListLog_id' => null,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListResult_id' => 2,
				'pmUser_id' => 1
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			$ServiceListLog_id = $resp[0]['ServiceListLog_id'];
			$flag = false;
		}

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			//Проверка доступности сервиса РПН
			$this->load->model('ServiceRPN_model');
			$resp = $this->ServiceRPN_model->getDateTime();
			if (!$this->isSuccessful($resp)) {
				throw new Exception('Не удалось подключиться к сервису РПН');
			}

			//Формирование пакета данных для обработки сервисом
			$query = "
				insert into stg.ServiceListPackage (
					ServiceListLog_id,
					ServiceListPackage_ObjectName,
					ServiceListPackage_ObjectID,
					pmUser_insID,
					pmUser_updID,
					ServiceListPackage_insDT,
					ServiceListPackage_updDT
				)
				select
					:ServiceListLog_id as ServiceListLog_id,
					'PersonCardAttach' as ServiceListPackage_ObjectName,
					PCA.PersonCardAttach_id as ServiceListPackage_ObjectID,
					:pmUser_id as pmUser_insID,
					:pmUser_id as pmUser_updID,
					dbo.tzGetDate() as ServiceListPackage_insDT,
					dbo.tzGetDate() as ServiceListPackage_updDT
				from
					v_PersonCardAttach PCA
					inner join lateral (
						select Object_sid
						from ObjectSynchronLog
						where ObjectSynchronLogService_id = 2 and Object_Name = 'PersonCardAttach'
							and Object_id = PCA.PersonCardAttach_id
						order by Object_setDT desc
						limit 1
					) OSL_Attach on true
					inner join r101.v_GetAttachment GA on GA.GetAttachment_id = OSL_Attach.Object_sid
					left join lateral (
						select PersonCardAttachStatusType_id
						from v_PersonCardAttachStatus
						where PersonCardAttach_id = PCA.PersonCardAttach_id
						order by PersonCardAttachStatus_setDate desc, PersonCardAttachStatus_id desc
						limit 1
					) PCAS on true
					left join v_PersonCardAttachStatusType PCAST on PCAST.PersonCardAttachStatusType_id = PCAS.PersonCardAttachStatusType_id
				where
					not exists(
						select ServiceListPackage_id
						from stg.v_ServiceListPackage
						where ServiceListLog_id = :ServiceListLog_id
						and ServiceListPackage_ObjectName = 'PersonCardAttach'
						and ServiceListPackage_ObjectID = PCA.PersonCardAttach_id
					)
					and PCAST.PersonCardAttachStatusType_Code = 5 --принято к рассмотрению
			";
			$params = array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'pmUser_id' => 1
			);
			//echo getDebugSQL($query, $params);exit;
			$this->queryResult($query, $params);


			if ($flag) {
				//Загрузка уже выполняется в другом запуске скрипта
				return array(array('success' => true, 'alredyRunning' => true));
			}

			$query = "
				select
					-- select
					SLP.ServiceListPackage_id as \"ServiceListPackage_id\",
					SLP.ServiceListPackage_ObjectID as \"ServiceListPackage_ObjectID\"
					-- end select
				from
					-- from
					stg.v_ServiceListPackage SLP
					-- end from
				where
					-- where
					SLP.ServiceListLog_id = :ServiceListLog_id
					and SLP.ServiceListPackage_ObjectName = 'PersonCardAttach'
					-- end where
				order by
					-- order by
					SLP.ServiceListPackage_id
					--end order by
			";

			$limit = 100;
			$params = array('ServiceListLog_id' => $ServiceListLog_id);
			$count = $this->getFirstResultFromQuery(getCountSQLPH($query), $params);

			$this->load->model('Polka_PersonCard_model', 'pcmodel');

			while($count > 0) {
				$resp = $this->queryResult(getLimitSQLPH($query, 0, $limit), $params);

				foreach($resp as $item) {
					$resp = $this->deleteServiceListPackage($item);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
					}

					$resp = $this->pcmodel->getPersonCardAttachStatusFromRPN(array(
						'PersonCardAttach_id' => $item['ServiceListPackage_ObjectID'],
						'Server_id' => 0,
						'pmUser_id' => 1
					));
					if (!$this->isSuccessful($resp)) {
						$this->saveServiceListDetailLog(array(
							'ServiceListLog_id' => $ServiceListLog_id,
							'ServiceListLogType_id' => 2,
							'ServiceListDetailLog_Message' => $resp[0]['Error_Msg'],
							'pmUser_id' => 1
						));
					} else if ($resp[0]['PersonCardAttachStatusType_Code'] == 5) {
						$this->saveServiceListDetailLog(array(
							'ServiceListLog_id' => $ServiceListLog_id,
							'ServiceListLogType_id' => 1,
							'ServiceListDetailLog_Message' => $item['ServiceListPackage_ObjectID'].': создано основное прикрепление',
							'pmUser_id' => 1
						));
					} else if ($resp[0]['PersonCardAttachStatusType_Code'] == 6) {
						$this->saveServiceListDetailLog(array(
							'ServiceListLog_id' => $ServiceListLog_id,
							'ServiceListLogType_id' => 1,
							'ServiceListDetailLog_Message' => $item['ServiceListPackage_ObjectID'].': в прикреплении отказано',
							'pmUser_id' => 1
						));
					}
				}

				$count = $this->getFirstResultFromQuery(getCountSQLPH($query));
			}

			$endDT = date('Y-m-d H:i:s');
			$resp = $this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 1,
				'pmUser_id' => 1
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}

		} catch(Exception $e) {
			restore_exception_handler();
			$this->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $e->getMessage(),
				'pmUser_id' => 1
			));

			$endDT = date('Y-m-d H:i:s');
			$this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => 1
			));
		}

		restore_exception_handler();

		return array(array('success' => true));
	}

	/**
	 * Запуск сервиса синхронизации участков с сервисом РПН
	 */
	function startLoadLpuRegionFromRpn() {
		$ServiceList_id = 2;

		$ServiceListLog_id = $this->getFirstResultFromQuery("
			select ServiceListLog_id as \"ServiceListLog_id\"
			from stg.v_ServiceListLog
			where ServiceList_id = :ServiceList_id and ServiceListResult_id = 2
			limit 1
		", array('ServiceList_id' => $ServiceList_id));
		if (!empty($ServiceListLog_id)) {
			return array(array('success' => true, 'alredyRunning' => true));
		}

		$begDT = date('Y-m-d H:i:s');
		$resp = $this->saveServiceListLog(array(
			'ServiceListLog_id' => null,
			'ServiceList_id' => $ServiceList_id,
			'ServiceListLog_begDT' => $begDT,
			'ServiceListResult_id' => 2,
			'pmUser_id' => 1
		));
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}
		$ServiceListLog_id = $resp[0]['ServiceListLog_id'];

		$this->load->helper('ShutdownErrorHandler');
		registerShutdownErrorHandler(array($this, 'shutdownErrorHandler'), function($error) use($ServiceListLog_id, $ServiceList_id, $begDT) {
			$this->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $error,
				'pmUser_id' => 1
			));

			$endDT = date('Y-m-d H:i:s');
			$this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => 1
			));
		});

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$this->load->model('ServiceRPN_model');

			//Проверка доступности сервиса РПН
			$resp = $this->ServiceRPN_model->getDateTime();
			if (!$this->isSuccessful($resp)) {
				throw new Exception('Не удалось подключиться к сервису РПН');
			}

			$LpuList = $this->ServiceRPN_model->getLpuListLinkedRPN();
			if (!is_array($LpuList)) {
				throw new Exception('Ошибка при получении списка МО для синхронизации участков');
			}

			foreach($LpuList as $Lpu) {
				//Загрузка данных из РПН
				$resp = $this->ServiceRPN_model->importGetTerrServiceList(array(
					'Lpu_id' => $Lpu['Lpu_id'],
					'pmUser_id' => 1,
				));
				if (!$this->isSuccessful($resp)) {
					$Error_Msg = $Lpu['Lpu_Nick'].': '.$resp[0]['Error_Msg'];
					$this->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => $Error_Msg,
						'pmUser_id' => 1
					));
					continue; //Если ошибка, то переходить к следующей МО
				}

				//Синхронизация полученных участков из РПН с участками промеда
				$resp = $this->ServiceRPN_model->syncLpuRegions(array(
					'Lpu_id' => $Lpu['Lpu_id'],
					'Server_id' => 0,
					'pmUser_id' => 1,
				));
				if (!$this->isSuccessful($resp)) {
					$Error_Msg = $Lpu['Lpu_Nick'].': '.$resp[0]['Error_Msg'];
					$this->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => $Error_Msg,
						'pmUser_id' => 1
					));
					continue; //Если ошибка, то переходить к следующей МО
				}
				$this->saveServiceListDetailLog(array(
					'ServiceListLog_id' => $ServiceListLog_id,
					'ServiceListLogType_id' => 1,
					'ServiceListDetailLog_Message' => "Синхронизованны участки МО '{$Lpu['Lpu_Nick']}'",
					'pmUser_id' => 1
				));
			}

			$endDT = date('Y-m-d H:i:s');
			$resp = $this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 1,
				'pmUser_id' => 1
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
			}
		} catch(Exception $e) {
			restore_exception_handler();

			$this->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $e->getMessage(),
				'pmUser_id' => 1
			));

			$endDT = date('Y-m-d H:i:s');
			$this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => 1
			));
		}

		restore_exception_handler();

		return array(array('success' => true));
	}

	/**
	 * Запуск сервиса автоматической идентификации
	 */
	function startPersonIdent() {
		$ServiceList_id = 1;
		$ServiceListLog_id = null;
		$PersonIdentPackage_id = null;
		$begDT = $this->currentDT->format('Y-m-d H:i:s');

		$query = "
			select
				PIPP.PersonIdentPackagePos_id as \"PersonIdentPackagePos_id\",
				PIPP.Person_id as \"Person_id\"
			from
				PersonIdentPackagePos PIPP
				inner join Person P on P.Person_id = PIPP.Person_id
			where
				PIPP.PersonIdentPackage_id is null
			order by
				case
					when PIPP.Evn_id is not null and P.Person_IsInErz is null then 1
					when PIPP.Evn_id is not null and P.Person_IsInErz is not null then 2
					else 3
				end
			limit 500
		";
		$personList = $this->queryResult($query);
		if (!is_array($personList)) {
			return $this->createError('Ошибка при запросе списка пациентов на идентификацию');
		}

		if (count($personList) > 0) {
			$resp = $this->saveServiceListLog(array(
				'ServiceListLog_id' => null,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListResult_id' => 2,
				'pmUser_id' => 1
			));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			$ServiceListLog_id = $resp[0]['ServiceListLog_id'];

			$query = "
				select
					PersonIdentPackage_id as \"PersonIdentPackage_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from (
					PersonIdentPackage_id := :PersonIdentPackage_id,
					PersonIdentPackage_Name := 'PersonIdentPackage_Name',
					PersonIdentPackage_begDate := :PersonIdentPackage_begDate,
					pmUser_id := 1
				)
			";
			$resp = $this->queryResult($query, array('PersonIdentPackage_begDate' => $begDT));
			if (!$this->isSuccessful($resp)) {
				return $resp;
			}
			$PersonIdentPackage_id = $resp[0]['PersonIdentPackage_id'];
		} else {
			return array(array('success' => true));
		}

		$personQuery = "
			select
				P.BDZ_id as \"BDZ_id\",
				PS.Person_id as \"Person_id\",
				PS.PersonEvn_id as \"PersonEvn_id\",
				PS.Server_id as \"Server_id\",
				PS.Person_IsInErz as \"Person_IsInErz\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				PS.Person_SurName||' '||PS.Person_FirName||coalesce(' '||PS.Person_SecName,'') as \"Person_Fio\",
				PS.Sex_id as \"PersonSex_id\",
				PS.Person_Inn as \"PersonInn_Inn\",
				PS.SocStatus_id as \"SocStatus_id\",
				to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				to_char(PS.Person_deadDT, 'YYYY-MM-DD') as \"Person_deadDT\",
				PI.Ethnos_id as \"Ethnos_id\",
				PIPP.PersonIdentPackage_id as \"PersonIdentPackage_id\"
			from
				PersonIdentPackagePos PIPP
				inner join v_PersonState PS on PS.Person_id = PIPP.Person_id
				inner join Person P on P.Person_id = PS.Person_id
				left join v_PersonInfo PI on PI.Person_id = PS.Person_id
			where PS.Person_id = :Person_id
			limit 1
		";

		set_error_handler(array($this, 'exceptionErrorHandler'));
		try {
			$this->load->model('ServiceRPN_model');
			$this->isAllowTransaction = false;

			//Проверка доступности сервиса РПН
			$resp = $this->ServiceRPN_model->getDateTime();
			if (!$this->isSuccessful($resp)) {
				throw new Exception('Не удалось подключиться к сервису РПН');
			}

			$person_ids = array();

			foreach($personList as $person) {
				$person['pmUser_id'] = 1;

				if (!empty($person['PersonIdentPackage_id'])) {
					continue;
				}

				$resp = $this->setPersonIdentPackageId(array(
					'PersonIdentPackagePos_id' => $person['PersonIdentPackagePos_id'],
					'PersonIdentPackage_id' => $PersonIdentPackage_id
				));
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				if (in_array($person['Person_id'], $person_ids)) {
					continue;
				}
				$person_ids[] = $person['Person_id'];

				$personData = $this->getFirstRowFromQuery($personQuery, $person);
				if (!is_array($personData)) {
					$error = "Ошибка при получении данных человека для идентификации";
					$this->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => "Person_id = {$person['Person_id']}: {$error}",
						'pmUser_id' => 1
					));
					continue;
				}
				$person = array_merge($person, $personData);

				$identResponse = $this->ServiceRPN_model->identPerson($person);

				$birthday = date_create($person['Person_BirthDay'])->format('d.m.Y');
				if (!$this->isSuccessful($identResponse)) {
					$error = isset($identResponse[0])?$identResponse[0]['Error_Msg']:'Неизвестная ошибка при идентификации';
					$this->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 2,
						'ServiceListDetailLog_Message' => "{$person['Person_Fio']}, {$birthday}: {$error}",
						'pmUser_id' => 1
					));
					continue;
				} elseif ($identResponse['Person_IsInErz'] != 2) {
					$this->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 1,
						'ServiceListDetailLog_Message' => "{$person['Person_Fio']}, {$birthday} г.р.: Пациент не идентифицирован",
						'pmUser_id' => 1
					));
				} else {
					$this->saveServiceListDetailLog(array(
						'ServiceListLog_id' => $ServiceListLog_id,
						'ServiceListLogType_id' => 1,
						'ServiceListDetailLog_Message' => "{$person['Person_Fio']}, {$birthday} г.р.: Пациент идентифицирован",
						'pmUser_id' => 1
					));

					//Загрузка истории прикреплений
					$resp = $this->ServiceRPN_model->importGetAttachmentList(array(
						'Person_id' => $person['Person_id'],
						'pmUser_id' => 1
					));
					if (!$this->isSuccessful($resp)) {
						$this->saveServiceListDetailLog(array(
							'ServiceListLog_id' => $ServiceListLog_id,
							'ServiceListLogType_id' => 2,
							'ServiceListDetailLog_Message' => "{$person['Person_Fio']}, {$birthday} г.р.: {$resp[0]['Error_Msg']}",
							'pmUser_id' => 1
						));
						continue;
					}

					$this->isAllowTransaction = true;
					$resp = $this->ServiceRPN_model->syncPersonCards(array(
						'Person_id' => $person['Person_id'],
						'Server_id' => 0,
						'pmUser_id' => 1
					));
					$this->isAllowTransaction = false;
					if (!$this->isSuccessful($resp)) {
						$this->saveServiceListDetailLog(array(
							'ServiceListLog_id' => $ServiceListLog_id,
							'ServiceListLogType_id' => 2,
							'ServiceListDetailLog_Message' => "{$person['Person_Fio']}, {$birthday} г.р.: {$resp[0]['Error_Msg']}",
							'pmUser_id' => 1
						));
						continue;
					}
				}
			}

			$this->isAllowTransaction = true;

			$endDT = $this->currentDT->format('Y-m-d H:i:s');
			$resp = $this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 1,
				'pmUser_id' => 1
			));
			if (!$this->isSuccessful($resp)) {
				throw new Exception($resp[0]['Error_Msg']);
			}
		} catch(Exception $e) {
			restore_exception_handler();

			$this->saveServiceListDetailLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceListLogType_id' => 2,
				'ServiceListDetailLog_Message' => $e->getMessage(),
				'pmUser_id' => 1
			));

			$endDT = $this->currentDT->format('Y-m-d H:i:s');
			$this->saveServiceListLog(array(
				'ServiceListLog_id' => $ServiceListLog_id,
				'ServiceList_id' => $ServiceList_id,
				'ServiceListLog_begDT' => $begDT,
				'ServiceListLog_endDT' => $endDT,
				'ServiceListResult_id' => 3,
				'pmUser_id' => 1
			));
		}
		restore_exception_handler();

		return array(array('success' => true));
	}

	/**
	 * Получение идентификатора сервиса по сис.нику
	 */
	public function getServiceListId($ServiceList_SysNick) {
		$resp = $this->queryResult("
			select
				ServiceList_id as \"ServiceList_id\"
			from
				stg.v_ServiceList
			where
				ServiceList_SysNick = :ServiceList_SysNick
			limit 1
		", array(
			'ServiceList_SysNick' => $ServiceList_SysNick
		));

		if (!empty($resp[0]['ServiceList_id'])) {
			return $resp[0]['ServiceList_id'];
		}

		return null;
	}

	function loadServiceListPackageTypeGrid($data) {

		if ($data['ServiceList_id'] == 113) {
			$list = [2, 3, 4, 58, 59, 60, 61, 62, 67];
			$filter = 'SLPT.ServiceListPackageType_Code in ('.join(', ', $list).')';
		} else {
			$filter = "SLPL.ServiceList_id = :ServiceList_id";
		}
		
		return $this->queryResult("
			select 
				SLPT.ServiceListPackageType_id as \"ServiceListPackageType_id\",
				SLPT.ServiceListPackageType_Code as \"ServiceListPackageType_Code\",
				SLPT.ServiceListPackageType_Name as \"ServiceListPackageType_Name\",
				SLPT.ServiceListPackageType_Description as \"ServiceListPackageType_Description\"
			from 
				stg.v_ServiceListPackageType SLPT
				left join stg.v_ServiceListPackageLink SLPL on SLPT.ServiceListPackageType_id = SLPL.ServiceListPackageType_id
			where {$filter}
		", $data);
	}
	
	function setServiceListPackageIsNotSend($data) {
		$params = array(
			'ServiceListPackage_id' => $data['ServiceListPackage_id'],
			'ServiceListPackage_IsNotSend' => $data['ServiceListPackage_IsNotSend'],
		);
		
		$query = "
			update stg.ServiceListPackage
			set ServiceListPackage_IsNotSend = :ServiceListPackage_IsNotSend
			where ServiceListPackage_id = :ServiceListPackage_id
			returning null as \"Error_Code\", null as \"Error_Msg\"
		";
		
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при изменении данных пакета');
		}
		
		return $resp;
	}
}
?>