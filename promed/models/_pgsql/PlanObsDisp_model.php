<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PlanObsDisp_model - модель для работы с планами контрольных посещений в рамках диспансерного наблюдения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 */

class PlanObsDisp_model extends SwPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Получение номера пакета для экспорта
	 */
	function getPlanObsDispExportPackNum($data) {
		$params = array(
			'Export_Year' => $data['Export_Year'],
			'Export_Month' => $data['Export_Month'],
			'Lpu_id' => $data['Lpu_id']
		);
		$sql = "
			select
				MAX(PersonDopDispPlanExport_PackNum) + 1 as \"PacketNumber\"
			from
				v_PersonDopDispPlanExport
			where
				PersonDopDispPlanExport_Year = :Export_Year
				and PersonDopDispPlanExport_Month = :Export_Month
				and Lpu_id = :Lpu_id
		";
		//~ exit(getDebugSQL($sql, $params));
		$resp = $this->queryResult($sql, $params);

		if (!empty($resp[0]['PacketNumber'])) {
			return array('Error_Msg' => '', 'PacketNumber' => $resp[0]['PacketNumber']);
		}

		return array('Error_Msg' => '', 'PacketNumber' => 1);
	}
	
	/**
	 * Получить периоды за указанный год для комбо "период"
	 */
	function getDispCheckPeriod($data) {
		$PeriodCapFilter = '';
		$PeriodCaps = '';
		switch(getRegionNick()) {
			case 'buryatiya': $PeriodCaps = '1,3'; break;//год, квартал
			case 'ekb':
			case 'pskov': $PeriodCaps = '1,4'; break;//год, месяц
		}
		if($PeriodCaps!='') 
			$PeriodCaps = "and DCP.PeriodCap_id in ({$PeriodCaps})";
		$sql = "
			select
				-- select
				DCP.DispCheckPeriod_id as \"DispCheckPeriod_id\",
				DCP.DispCheckPeriod_Name as \"DispCheckPeriod_Name\",
				DCP.PeriodCap_id as \"PeriodCap_id\",
				to_char (DCP.DispCheckPeriod_begDate, 'yyyy-mm-dd hh24:mm:ss') as DispCheckPeriod_begDate,
				to_char (DCP.DispCheckPeriod_endDate, 'yyyy-mm-dd hh24:mm:ss') as DispCheckPeriod_endDate
				-- end select
			from
				-- from
				v_DispCheckPeriod DCP
				-- end from
			where
				-- where
				date_part('year', DCP.DispCheckPeriod_begDate) = :Year {$PeriodCaps}
				-- end where
			order by 
				DCP.DispCheckPeriod_begDate, DCP.PeriodCap_id
		";
		
		//exit(getDebugSQL($sql, $data));
		$result = $this->db->query($sql, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Удалить план
	 */
	function deletePlan($data) {
		set_time_limit(0);
		
		$params = array('PlanObsDisp_id' => $data['PlanObsDisp_id']);
		
		$resp = $this->queryResult("
			select
				PODL.PlanObsDisp_id as \"PlanObsDisp_id\"
			from
				v_PlanObsDispLink PODL
				left join v_PlanObsDispLinkStatus PODLS on PODLS.PlanObsDispLink_id = PODL.PlanObsDispLink_id
			where
				PODL.PlanObsDisp_id = :PlanObsDisp_id
				and PODLS.PlanPersonListStatusType_id = 2
            limit 1
		", $params);

		if (!empty($resp[0]['PlanObsDisp_id'])) {
			return array('Error_Msg' => 'Нельзя удалить план, т.к. он содержит записи со статусом "Отправлено в ТФОМС".');
		}
		
		$sql = "
		    DELETE FROM PersonDopDispPlanExportLink WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink where PlanObsDisp_id=:PlanObsDisp_id);

            DELETE FROM PlanObsDispLinkStatus
            WHERE PlanObsDispLink_id in (select PlanObsDispLink_id FROM v_PlanObsDispLink WHERE PlanObsDisp_id=:PlanObsDisp_id);
            
            DELETE FROM PlanObsDispErrorExport where PlanObsDispLink_id in (select PlanObsDispLink_id FROM v_PlanObsDispLink WHERE PlanObsDisp_id=:PlanObsDisp_id);
            
            DELETE FROM PlanObsDispLink
            WHERE PlanObsDisp_id=:PlanObsDisp_id;
            
            DELETE FROM PersonDopDispPlanExport
            WHERE PersonDopDispPlanExport_id in (select PersonDopDispPlanExport_id from PlanObsDispLink where PlanObsDisp_id=:PlanObsDisp_id);
            
            DELETE FROM PlanObsDisp
            WHERE PlanObsDisp_id=:PlanObsDisp_id;
		";
		
		if(getRegionNick()=='ekb') {
			$sql = "DELETE FROM r66.TFOMSWorkDirectionPlanObsDispLink WHERE PlanObsDisp_id=:PlanObsDisp_id;
			".$sql;
		}
		//~ exit(getDebugSQL($sql, $params));
		
		$this->beginTransaction();
		$result = $this->db->query($sql, $params);
		if($result == true) {
			$this->commitTransaction();
			return array('suspened'=>true,'Error_Message'=>'');
		} else {
			$this->rollbackTransaction();
			return array('suspened'=>true,'Error_Message'=>'Возникла ошибка');
		}
	}
	
	/**
	 * Удаляет людей из плана
	 */
	function deletePlanPersonList($data) {
		$this->beginTransaction();

		// сначала удаляем все статусы
		$this->db->query("update PlanPersonList set PlanPersonListStatus_id = null where PlanPersonList_id = :PlanPersonList_id", array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		$resp_ppls = $this->queryResult("
			select
				PlanPersonListStatus_id as \"PlanPersonListStatus_id\"
			from
				v_PlanPersonListStatus
			where
				PlanPersonList_id = :PlanPersonList_id
		", array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		if (is_array($resp_ppls)) {
			foreach ($resp_ppls as $one_ppls) {
				$query = "
                    select 
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
					from p_PlanPersonListStatus_del (
						PlanPersonListStatus_id := :PlanPersonListStatus_id
						)
				";

				$resp_del = $this->queryResult($query, array(
					'PlanPersonListStatus_id' => $one_ppls['PlanPersonListStatus_id']
				));

				if (!empty($resp_del[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					return $resp_del;
				}
			}
		}

		$query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PlanPersonList_del (
				PlanPersonList_id := :PlanPersonList_id
				)
		";

		$resp_del = $this->queryResult($query, array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		if (!empty($resp_del[0]['Error_Msg'])) {
			$this->rollbackTransaction();
		} else {
			$this->commitTransaction();
		}

		return $resp_del;
	}
	
	/**
	 * Возвращает список планов
	 */
	function loadPlans($data) {
		$select = "";
		$from = "";
		$filter = 'extract(year from DCP.DispCheckPeriod_begDate)=:PlanObsDisp_Year';
		
		$params = array('PlanObsDisp_Year' => $data['PlanObsDisp_Year']);
		if (isset($_SESSION['lpu_id']) && !empty($_SESSION['lpu_id'])) {
			$params['Lpu_id'] = $_SESSION['lpu_id'];
			$filter .= ' and POD.Lpu_id = :Lpu_id';
		}
		
		if (!empty($data['PlanObsDispExport_expDateRange'][0]) || !empty($data['PlanObsDispExport_expDateRange'][1])) {
			$filter_dt = "";
			if (!empty($data['PlanObsDispExport_expDateRange'][0])) {
				$params['PlanObsDispExport_expDate_From'] = $data['PlanObsDispExport_expDateRange'][0];
				$filter_dt .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) >= :PlanObsDispExport_expDate_From';
			}
			if (!empty($data['PlanObsDispExport_expDateRange'][1])) {
				$params['PlanObsDispExport_expDate_To'] = $data['PlanObsDispExport_expDateRange'][1];
				$filter_dt .= ' and cast(pddpe.PersonDopDispPlanExport_expDate as date) <= :PlanObsDispExport_expDate_To';
			}
			$filter .= " and exists(
				select
					pddpel.PersonDopDispPlanExport_id as PersonDopDispPlanExport_id
				from
					v_PlanObsDispLink PODL
					inner join v_PersonDopDispPlanExportLink pddpel on pddpel.PersonDopDispPlanExport_id = PODL.PersonDopDispPlanExport_id
					inner join v_PersonDopDispPlanExport pddpe on pddpe.PersonDopDispPlanExport_id = pddpel.PersonDopDispPlanExport_id
				where
					PODL.PlanObsDisp_id = POD.PlanObsDisp_id
					{$filter_dt}
                limit 1
			)";
		}
		if(getRegionNick()=='ekb') {
			$select="
				workdirection.TFOMSWorkDirection_Name as \"TFOMSWorkDirection_Name\",
				workdirection.TFOMSWorkDirection_id as \"TFOMSWorkDirection_id\",
			";
			$from = "LEFT JOIN LATERAL(
				select WD.TFOMSWorkDirection_id as TFOMSWorkDirection_id, WD.TFOMSWorkDirection_Name as TFOMSWorkDirection_Name
				from r66.TFOMSWorkDirectionPlanObsDispLink WDL
					left join r66.TFOMSWorkDirection WD on WD.TFOMSWorkDirection_id=WDL.TFOMSWorkDirection_id
				where WDL.PlanObsDisp_id = POD.PlanObsDisp_id
				limit 1
				) workdirection ON TRUE
			";
			if(!empty($data['TFOMSWorkDirection_id'])) {
				$filter .= " and workdirection.TFOMSWorkDirection_id = :TFOMSWorkDirection_id";
				$params['TFOMSWorkDirection_id'] = $data['TFOMSWorkDirection_id'];
			}
		}
		
		$sql = "
			select
				-- select
				{$select}
				POD.PlanObsDisp_id as \"PlanObsDisp_id\",
				to_char (POD.PlanObsDisp_CreateDate, 'dd.mm.yyyy') as PlanObsDisp_CreateDate,
				DCP.DispCheckPeriod_id as \"DispCheckPeriod_id\",
				extract(year from DCP.DispCheckPeriod_begDate) as DispCheckPeriod_Year,
				extract(month from DCP.DispCheckPeriod_begDate) as \"DispCheckPeriod_Month\",
				DCP.DispCheckPeriod_Name as \"DispCheckPeriod_Name\",
				PC.pcount as \"PlanObsDisp_Count\",
				PCF.pcount as \"PlanObsDisp_CountTFOMS\",
				PID.PersonDopDispPlanExport_impDate as \"PersonDopDispPlanExport_impDate\",
				case when PS.PlanObsDispLink_id is not null then 0 else 1 end as \"accessDelete\"
				-- end select
			from 
				-- from
				v_PlanObsDisp POD
				inner join v_DispCheckPeriod DCP on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
				LEFT JOIN LATERAL (
					select count(distinct PODL.PlanObsDispLink_id) as pcount
					from v_PlanObsDispLink PODL
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id
				) PC ON TRUE
				LEFT JOIN LATERAL (
					select count(distinct PODL.PlanObsDispLink_id) as pcount
					from v_PlanObsDispLink PODL
					left join v_PlanObsDispLinkStatus PODLS on PODLS.PlanObsDispLink_id=PODL.PlanObsDispLink_id
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id and PODLS.PlanPersonListStatusType_id=3
				) PCF ON TRUE
				LEFT JOIN LATERAL (
					select PE.PersonDopDispPlanExport_impDate as PersonDopDispPlanExport_impDate
					from v_PlanObsDispLink PODL
					left join v_PersonDopDispPlanExport PE on PODL.PersonDopDispPlanExport_id=PE.PersonDopDispPlanExport_id
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id limit 1
				) PID ON TRUE
				LEFT JOIN LATERAL (
					select PODL.PlanObsDispLink_id
					from v_PlanObsDispLink PODL
					left join v_PlanObsDispLinkStatus PODLS on PODL.PlanObsDispLink_id = PODLS.PlanObsDispLink_id
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id and PODLS.PlanPersonListStatusType_id=2 limit 1
				) PS ON TRUE
				{$from}
				-- end from
			where
				--where
				{$filter}
				--end where
			order by
				-- order by
				PlanObsDisp_id ASC
				-- end order by";
		//~ exit(getDebugSQL($sql, $params));

		return $this->getPagingResponse($sql, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	 * Данные грида "файлы экспорта" на форме "планы КП в рамках ДН"
	 */
	function loadPlanObsDispExportList($data) {
		
		$filter = '(1 = 1)';
		$params = array();
		
		if (empty($data['PlanObsDisp_id']) && empty($data['PlanObsDisp_ids'])) {
			return array('Error_Msg' => 'Не указан идентификатор плана');
		} else if(!empty($data['PlanObsDisp_id'])) {
			$params['PlanObsDisp_id'] = $data['PlanObsDisp_id'];
			$filter .= ' and POD.PlanObsDisp_id = :PlanObsDisp_id';
		} else if(!empty($data['PlanObsDisp_ids'])) {
			$filter .= ' and POD.PlanObsDisp_id in ('.join(',',$data['PlanObsDisp_ids']).')';
		}
		
		$query = "
			select distinct
				-- select
				PDDPE.PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
				PDDPE.PersonDopDispPlanExport_FileName as \"PersonDopDispPlanExport_FileName\",
				to_char (PDDPE.PersonDopDispPlanExport_expDate, 'dd.mm.yyyy') as \"PersonDopDispPlanExport_expDate\",
				PC.pcount as \"PersonDopDispPlanExport_Count\",
				to_char (PDDPE.PersonDopDispPlanExport_impDate, 'dd.mm.yyyy') as \"PersonDopDispPlanExport_impDate\",
				DCP.DispCheckPeriod_Name as \"DispCheckPeriod_Name\",
				PDDPE.PersonDopDispPlanExport_isUsed as \"PersonDopDispPlanExport_isUsed\",
				PDDPE.PersonDopDispPlanExport_DownloadLink as \"PersonDopDispPlanExport_DownloadLink\",
				PDDPE.PersonDopDispPlanExport_PackNum as \"PersonDopDispPlanExport_PackNum\"
				-- end select
			from
				-- from
				v_PlanObsDisp POD
				left join v_PlanObsDispLink PODL on PODL.PlanObsDisp_id=POD.PlanObsDisp_id
				left join v_DispCheckPeriod DCP on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
				inner join PersonDopDispPlanExportLink PDDPEL on PDDPEL.PlanObsDispLink_id=PODL.PlanObsDispLink_id
				left join v_PersonDopDispPlanExport PDDPE on PDDPE.PersonDopDispPlanExport_id = PDDPEL.PersonDopDispPlanExport_id
				LEFT JOIN LATERAL (
					select count(distinct PD.Person_id) pcount
					from v_PlanObsDispLink PL
					left join v_PersonDisp PD on PD.PersonDisp_id = PL.PersonDisp_id
					left join v_PersonDopDispPlanExportLink EL on El.PlanObsDispLink_id = PL.PlanObsDispLink_id
					where
					EL.PersonDopDispPlanExport_id=PDDPE.PersonDopDispPlanExport_id
				) PC ON TRUE
				-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				PDDPE.PersonDopDispPlanExport_id
				-- end order by
		";
		//~ exit(getDebugSQL($query, $params));

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	 * Сохранить план КП
	 */
	function savePlan($data) {
		if(empty($data['Lpu_id'])) return false;
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'Lpu_id'=>$data['Lpu_id'],
			'DispCheckPeriod_id'=>$data['DispCheckPeriod_id'],
			'TFOMSWorkDirection_id'=>$data['TFOMSWorkDirection_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$procedure = empty($params['PlanObsDisp_id']) ? 'p_PlanObsDisp_ins' : 'p_PlanObsDisp_upd';
		
		$sql = "
			
			with PeriodCap_id as (
			    SELECT 
			        PeriodCap_id as PeriodCap_id
			    from 
			        DispCheckPeriod 
                where 
                    DispCheckPeriod_id = :DispCheckPeriod_id
			)		
			
			
            SELECT
                PlanObsDisp_id as \"PlanObsDisp_id\",
                (select PeriodCap_id from PeriodCap_id) as \"PeriodCap_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Message\"
            FROM {$procedure} (
                PlanObsDisp_id := :PlanObsDisp_id,
                DispCheckPeriod_id := :DispCheckPeriod_id,
                PlanObsDisp_CreateDate := dbo.tzGetDate(),
                Lpu_id := :Lpu_id,
                pmUser_id := :pmUser_id
                )
		";
		
		$this->beginTransaction();
		$result = $this->db->query($sql, $params);

		if (is_object($result)) {
			$result = $result->result('array');
			if(count($result)>0) {
				$this->commitTransaction();
				return $result[0];
			}
		}
		$this->rollbackTransaction();
		return false;
	}
	
	/**
	 * Установка статуса файла экспорта
	 */
	function setPersonDopDispPlanExportIsUsed($data) {
		$query = "update PersonDopDispPlanExport set PersonDopDispPlanExport_IsUsed = :PersonDopDispPlanExport_IsUsed where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id";
		$this->db->query($query, array(
			'PersonDopDispPlanExport_id' => $data ['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => $data['PersonDopDispPlanExport_IsUsed']
		));
	}
	
	/**
	 * Сохранение файла экспорта
	 */
	function savePlanObsDispExport($data) {
		$query = "
            select
                PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PersonDopDispPlanExport_upd (
				PersonDopDispPlanExport_id := :PersonDopDispPlanExport_id,
				PersonDopDispPlanExport_FileName := :PersonDopDispPlanExport_FileName,
				PersonDopDispPlanExport_expDate := dbo.tzGetDate(),
				PersonDopDispPlanExport_PackNum := :PacketNumber,
				PersonDopDispPlanExport_impDate := null,
				Lpu_id := :Lpu_id,
				PersonDopDispPlanExport_Year := :PersonDopDispPlanExport_Year,
				PersonDopDispPlanExport_Month := :PersonDopDispPlanExport_Month,
				PersonDopDispPlanExport_DownloadQuarter := :PersonDopDispPlanExport_DownloadQuarter,
				PersonDopDispPlanExport_IsUsed := :PersonDopDispPlanExport_IsUsed,
				PersonDopDispPlanExport_IsCreatedTFOMS := :PersonDopDispPlanExport_IsCreatedTFOMS,
				pmUser_id := :pmUser_id
				)
		";

		return $this->queryResult($query, array(
			'PersonDopDispPlanExport_id' => null,
			'PersonDopDispPlanExport_FileName' => $data['PersonDopDispPlanExport_FileName'],
			'PersonDopDispPlanExport_PackNum' => !empty($data['PersonDopDispPlanExport_PackNum']) ? $data['PersonDopDispPlanExport_PackNum'] : null,
			'OrgSmo_id' => !empty($data['OrgSmo_id']) ? $data['OrgSmo_id'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_expDate' => !empty($data['PersonDopDispPlanExport_expDate']) ? $data['PersonDopDispPlanExport_expDate'] : null,
			'PersonDopDispPlanExport_Year' => !empty($data['PersonDopDispPlanExport_Year']) ? $data['PersonDopDispPlanExport_Year'] : null,
			'PersonDopDispPlanExport_Month' => !empty($data['PersonDopDispPlanExport_Month']) ? $data['PersonDopDispPlanExport_Month'] : null,
			'PersonDopDispPlanExport_DownloadQuarter' => !empty($data['PersonDopDispPlanExport_DownloadQuarter']) ? $data['PersonDopDispPlanExport_DownloadQuarter'] : null,
			'PersonDopDispPlanExport_IsUsed' => !empty($data['PersonDopDispPlanExport_IsUsed']) ? $data['PersonDopDispPlanExport_IsUsed'] : null,
			'PersonDopDispPlanExport_IsCreatedTFOMS' => !empty($data['PersonDopDispPlanExport_IsCreatedTFOMS']) ? $data['PersonDopDispPlanExport_IsCreatedTFOMS'] : null,
			'pmUser_id' => $data['pmUser_id'],
		));
	}
	
	/**
	 * Проверка дублирования плана
	 */
	function checkPlanObsDispDoubles($data) {
		$params= array('Lpu_id'=>$data['Lpu_id'], 'DispCheckPeriod_id'=>$data['DispCheckPeriod_id']);
		$sql = "
			select count(*) from v_PlanObsDisp where DispCheckPeriod_id = :DispCheckPeriod_id AND Lpu_id = :Lpu_id
		";
		$cnt = $this->getFirstResultFromQuery($sql, $params);
		if($cnt) throw new Exception('Сохранение плана невозможно т.к. план с таким периодом уже существует');
		return $cnt;
	}
	
	/**
	 * Сформировать список карт дн в план
	 * в каждом регионе по разному
	 */
	function makePlanObsDispLink($data) {
		
	}
	
	/**
	 * Сформировать план
	 */
	function makePlan($data) {
		/*if(empty($data['PlanObsDisp_id'])) {//проверка на дублирование
			$response = $this->checkPlanObsDispDoubles($data);
			if ( $response ) {
				return array(array('success' => true, 'Error_Msg' => 'Есть дубли '));
			}
		}*/
		
		$planresult = $this->savePlan($data);//сохранение параметров плана едино для всех регионов
		
		if(!empty($planresult['Error_Message']) or empty($planresult['PlanObsDisp_id']))
			return array(array('success' => true, 'Error_Msg' => 'Ошибка при сохранении параметров плана: '.$planresult['Error_Message']));
		//теперь есть id плана = $planresult['PlanObsDisp_id']
		//нужно добавить в план карты ДН (исходя из правил региона). Те карты, что уже есть в плане, не удаляем.
		$sql = "";
		$params = array(
			'PlanObsDisp_id'=>$planresult['PlanObsDisp_id'], 
			'Lpu_id'=>$data['Lpu_id'],
			'DispCheckPeriod_begDate'=>$data['DispCheckPeriod_begDate'],
			'DispCheckPeriod_endDate'=>$data['DispCheckPeriod_endDate'],
			'pmUser_id'=>$data['pmUser_id']
		);

		$this->makePlanObsDispLink(array_merge($data, $planresult));
		
		return array(array('success' => true, 'Error_Msg' => '', 'PlanObsDisp_id' => $planresult['PlanObsDisp_id']));
	}
	
	/**
	 * Данные грида для плана
	 */
	function loadPlan($data){
		$select = "";
		$filter = "";
		$from = "";
		
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id']
		);
		if(!empty($data['Diag_id'])) {
			$filter.=" AND PD.Diag_id = :Diag_id";
			$params['Diag_id'] = $data['Diag_id'];
		}
		if(!empty($data['PlanPersonListStatusType_id'])) {
			$filter.=" AND StatType.PlanPersonListStatusType_id = :PlanPersonListStatusType_id";
			$params['PlanPersonListStatusType_id'] = $data['PlanPersonListStatusType_id'];
		}
		if(!empty($data['Person_Birthday'])) {
			$filter.=" AND PS.Person_Birthday = :Person_Birthday";
			$params['Person_Birthday'] = $data['Person_Birthday'];
		}
		if (!empty($data['Person_FIO'])) {
			$podfilter = "";
			if(mb_strpos($data['Person_FIO'],' ')!==false) {
				$podfilter = "||' '||PS.Person_FirName||' '||PS.Person_SecName";
			}
			$filter .= " and PS.Person_SurName{$podfilter} ilike :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'].'%';
		}
		if(getRegionNick()=='ekb') {
			$select.=",tfomsWD.TFOMSWorkDirection_Name as \"WorkDirection\"";
			$from.=" 
				LEFT JOIN LATERAL (
					select
						WD.TFOMSWorkDirection_id as TFOMSWorkDirection_id,
						WD.TFOMSWorkDirection_Code as TFOMSWorkDirection_Code,
						WD.TFOMSWorkDirection_Name as TFOMSWorkDirection_Name
					from r66.TFOMSWorkDirectionDiagLink WDD
						inner join r66.TFOMSWorkDirection WD on WD.TFOMSWorkDirection_id=WDD.TFOMSWorkDirection_id
					where WDD.Diag_id = D.Diag_id
					limit 1
				) tfomsWD ON TRUE
				left join v_Person P on P.Person_id = PD.Person_id
				left join v_Polis po on po.Polis_id = PS.Polis_id
			";
			$filter.=" and COALESCE(P.BDZ_id, po.BDZ_id,0)!=0";
		}
		$sql = "
			SELECT
				-- select
				PS.Person_id as \"Person_id\",
				PS.Server_id as \"Server_id\",
				RTRIM(PS.Person_SurName) || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '') as \"Person_FIO\",
				to_char (PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				'' as \"Person_Work\",
				PD.PersonDisp_NumCard as \"CardNumber\",
				to_char (PD.PersonDisp_begDate, 'dd.mm.yyyy') as \"begDate\",
				to_char (PD.PersonDisp_endDate, 'dd.mm.yyyy') as \"endDate\",
				D.Diag_Code||' '||D.Diag_Name as \"Diagnoz\",
				StatType.PlanPersonListStatusType_Name as \"StatusType_Name\",
				StatType.PlanPersonListStatusType_id as \"StatusType_id\",
				to_char (PODstat.PlanObsDispLinkStatus_setDate, 'dd.mm.yyyy') as \"StatusDate\",
				to_char (vizit.PersonDispVizit_NextDate, 'dd.mm.yyyy') as \"VizitDate\",
                (SELECT DISTINCT
                    string_agg(
                        rtrim(COALESCE(to_char (PET.PlanObsDispErrorType_Code, '0'),''))
                        , ', '
                    )
                FROM
                    PlanObsDispErrorExport PEE
                    left join PlanObsDispErrorType PET on PET.PlanObsDispErrorType_id=PEE.PlanObsDispErrorType_id
                WHERE
                    PEE.PlanObsDispLink_id=PODL.PlanObsDispLink_id
                ) as \"Errors\",
                (SELECT DISTINCT
                    string_agg(
                        rtrim(COALESCE(to_char (PET.PlanObsDispErrorType_Code, '0')||' - '||rtrim(COALESCE(PET.PlanObsDispErrorType_Descr,'')),''))
                        , ', '
                    )
                FROM
                    PlanObsDispErrorExport PEE  
                    left join PlanObsDispErrorType PET on PET.PlanObsDispErrorType_id=PEE.PlanObsDispErrorType_id
                WHERE
                    PEE.PlanObsDispLink_id=PODL.PlanObsDispLink_id
				) as \"Errors_text\",
				PODL.PersonDisp_id as \"PersonDisp_id\",
				PODL.PlanObsDispLink_id as \"PlanObsDispLink_id\"
				{$select}
				-- end select
			FROM
				-- from
				v_PlanObsDispLink PODL
				left join v_PersonDisp PD on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_PersonState PS on PS.Person_id = PD.Person_id
				left join v_Diag D on D.Diag_id = PD.Diag_id
				left join v_PlanObsDispLinkStatus PODstat on PODstat.PlanObsDispLink_id = PODL.PlanObsDispLink_id
				left join v_PlanPersonListStatusType StatType on StatType.PlanPersonListStatusType_id = PODstat.PlanPersonListStatusType_id
				left join v_PersonDispVizit vizit on vizit.PersonDispVizit_id = PODL.PersonDispVizit_id
				{$from}
				-- end from
			WHERE
				-- where
				PODL.PlanObsDisp_id = :PlanObsDisp_id {$filter}
				-- end where
			ORDER BY
				-- order by
				PS.Person_id, PD.PersonDisp_id, vizit.PersonDispVizit_NextDate
				-- end order by
		";
		//~ exit(getDebugSQL($sql, $params));
		
		return $this->getPagingResponse($sql, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	 * Ошибки данных. Для грида на форме план КП ДН
	 * при наличии доп.условий переопределяется в региональной модели
	 */
	function loadPlanErrorData($data) {
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id']
		);
		$sql ="
			SELECT POD.Lpu_id as \"Lpu_id\", POD.DispCheckPeriod_id as \"DispCheckPeriod_id\", DCP.PeriodCap_id as \"PeriodCap_id\", 
				to_char (DCP.DispCheckPeriod_begDate, 'yyyy-mm-dd hh24:mm:ss') as \"DispCheckPeriod_begDate\",
				to_char (DCP.DispCheckPeriod_endDate, 'yyyy-mm-dd hh24:mm:ss') as \"DispCheckPeriod_endDate\"
			FROM v_PlanObsDisp POD
			left join v_DispCheckPeriod DCP on DCP.DispCheckPeriod_id=POD.DispCheckPeriod_id
			WHERE POD.PlanObsDisp_id=:PlanObsDisp_id
		";
		$par = $this->queryResult($sql, $params);
		$params['Lpu_id'] = $par[0]['Lpu_id'];
		$params['PeriodCap_id'] = $par[0]['PeriodCap_id'];
		$params['DispCheckPeriod_id'] = $par[0]['DispCheckPeriod_id'];
		$params['DispCheckPeriod_begDate'] = $par[0]['DispCheckPeriod_begDate'];
		$params['DispCheckPeriod_endDate'] = $par[0]['DispCheckPeriod_endDate'];
		
		$filter = "";
		
		if(!empty($data['Diag_id'])) {
			$filter.=" AND PD.Diag_id = :Diag_id";
			$params['Diag_id'] = $data['Diag_id'];
		}
		if(!empty($data['Person_Birthday'])) {
			$filter.=" AND PS.Person_Birthday = :Person_Birthday";
			$params['Person_Birthday'] = $data['Person_Birthday'];
		}
		if (!empty($data['Person_FIO'])) {
			$podfilter = "";
			if(mb_strpos($data['Person_FIO'],' ')!==false) {
				$podfilter = "||' '||PS.Person_FirName||' '||PS.Person_SecName";
			}
			$filter .= " and PS.Person_SurName{$podfilter} ilike :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'].'%';
		}
		$select = "
			PD.Person_id as \"Person_id\",
			PD.PersonDisp_id as \"PersonDisp_id\",
			RTRIM(PS.Person_SurName) || ' ' || coalesce(PS.Person_FirName, '') || ' ' || coalesce(PS.Person_SecName, '') as \"Person_FIO\",
			to_char (PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
			PD.PersonDisp_NumCard as \"CardNumber\",
			to_char (PD.PersonDisp_begDate, 'dd.mm.yyyy') as \"begDate\",
			to_char (PD.PersonDisp_endDate, 'dd.mm.yyyy') as \"endDate\",
			D.Diag_Code||' '||D.Diag_Name as \"Diagnoz\",
			to_char (PDVizit.PersonDispVizit_NextDate, 'dd.mm.yyyy') as \"VizitDate\"
			--convert(varchar(10), vizit.vizitdate, 'dd.mm.yyyy') as \"LastVizitDate\"
		";
		$from = "
			v_PersonDisp PD
			left join v_PersonState PS on PS.Person_id = PD.Person_id
			left join v_Diag D on D.Diag_id = PD.Diag_id
			left join v_PersonDispVizit PDVizit on PDVizit.PersonDisp_id=PD.PersonDisp_id
			/*LEFT JOIN LATERAL (
				select PersonDispVizit_NextDate as vizitdate
				from v_PersonDispVizit PDV
				where PDV.PersonDisp_id = PD.PersonDisp_id
				order by PDV.PersonDispVizit_NextDate DESC
				limit 1
			) vizit ON TRUE*/
		";
		$sql="";
		switch($params['PeriodCap_id']) {
			case '1': //год
				$sql = "
					SELECT
						-- select
						{$select}
						-- end select
					FROM
						-- from
						{$from}
						-- end from
					WHERE
						-- where
						PD.Lpu_id=:Lpu_id and
						(coalesce(date_part('year', PD.PersonDisp_begDate), 0) < date_part('year', CAST(:DispCheckPeriod_begDate as date)) OR
						coalesce(date_part('year', PD.PersonDisp_endDate), date_part('year', CAST(:DispCheckPeriod_begDate as date))) =  date_part('year', CAST(:DispCheckPeriod_begDate as date))) and
						not exists(
							SELECT PDV.PersonDisp_id as PersonDisp_id
							FROM v_PersonDispVizit PDV
							WHERE PDV.PersonDisp_id=PD.PersonDisp_id AND date_part('year', PDV.PersonDispVizit_NextDate) = date_part('year', CAST(:DispCheckPeriod_begDate as date))
						) and
						not exists(select
						            PlanObsDispLink_id as PlanObsDispLink_id,
                                    PlanObsDisp_id as PlanObsDisp_id,
                                    PersonDisp_id as PersonDisp_id,
                                    PersonDispVizit_id as PersonDispVizit_id,
                                    PersonDopDispPlanExport_id as PersonDopDispPlanExport_id,
                                    PlanObsDispLink_Num as PlanObsDispLink_Num,
                                    pmUser_insID as pmUser_insID,
                                    pmUser_updID as pmUser_updID,
                                    PlanObsDispLink_insDT as PlanObsDispLink_insDT,
                                    PlanObsDispLink_updDT as PlanObsDispLink_updDT
						           from PlanObsDispLink PODL where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id)
						{$filter}
						-- end where
					ORDER BY
						-- order by
						PS.Person_SurName, PS.Person_FirName, PS.Person_SecName
						-- end order by
				";
				break;
			case '3': //квартал
			case '4': //месяц
				$sql = "
					SELECT
						-- select
						{$select}
						-- end select
					FROM
						-- from
						{$from}
						-- end from
					WHERE
						-- where
						PD.Lpu_id=:Lpu_id and
						(coalesce(PD.PersonDisp_endDate, :DispCheckPeriod_endDate) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate) and
						coalesce(PD.PersonDisp_begDate, :DispCheckPeriod_endDate) <= :DispCheckPeriod_endDate and
						not exists(
							SELECT PDV.PersonDisp_id
							FROM v_PersonDispVizit PDV
							WHERE PDV.PersonDisp_id=PD.PersonDisp_id AND date_part('year', PDV.PersonDispVizit_NextDate) = date_part('year', CAST(:DispCheckPeriod_begDate as date))
						) and
						not exists(select PODL.PlanObsDispLink_id as PlanObsDispLink_id from PlanObsDispLink PODL where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id)
						{$filter}
						-- end where
					ORDER BY
						-- order by
						PS.Person_SurName, PS.Person_FirName, PS.Person_SecName
						-- end order by
					";
				break;
			default:
				$sql = "";
		}
		//~ exit(getDebugSQL($sql, $params));
		if($sql=="") return false;
		else return $this->getPagingResponse($sql, $params, $data['start'], $data['limit'], true);
	}
	
	/**
	 * Данные грида ошибок планов КП ДН
	 */
	function loadExportErrorPlanList($data) {
		return false;
	}
	
	/**
	 * Удалить запись плана
	 */
	function deletePlanLink($data) {
		$params = array('PlanObsDispLink_id'=>$data['PlanObsDispLink_id']);
		$this->beginTransaction();
		$sql = "
			DELETE FROM PlanObsDispLinkStatus 
			WHERE PlanObsDispLink_id = :PlanObsDispLink_id;
			
			DELETE FROM PlanObsDispLink
			WHERE PlanObsDispLink_id = :PlanObsDispLink_id;
		";
		$result = $this->db->query($sql, $params);
		if($result == true) {
			$this->commitTransaction();
			return array('suspened'=>true,'Error_Message'=>'');
		} else {
			$this->rollbackTransaction();
			return array('suspened'=>true,'Error_Message'=>'Возникла ошибка');
		}
	}
	
	/**
	 * Импорт данных плана
	 */
	function importPlanObsDisp($data)
	{

	}
	
	/**
	 * Установить статус записи плана
	 */
	function setPlanObsDispLinkStatus($data) {
		$sql = "
			UPDATE PlanObsDispLinkStatus as \"PlanObsDispLinkStatus\"
			SET PlanPersonListStatusType_id = :PlanPersonListStatusType_id, 
				PlanObsDispLinkStatus_setDate = dbo.tzGetdate()
			WHERE PlanObsDispLink_id = :PlanObsDispLink_id
		";
		$res = $this->db->query($sql,$data);
		return $res;
	}
	
	/**
	 * Список направлений работы
	 */
	function getWorkDirectionSpr() {
		
	}
}