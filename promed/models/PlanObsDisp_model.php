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

class PlanObsDisp_model extends swModel {

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
				MAX(PersonDopDispPlanExport_PackNum) + 1 as PacketNumber
			from
				v_PersonDopDispPlanExport (nolock)
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
			case 'buryatiya':
			case 'ekb':
			case 'pskov': $PeriodCaps = '1,4'; break;//год, месяц
		}
		if($PeriodCaps!='') 
			$PeriodCaps = "and DCP.PeriodCap_id in ({$PeriodCaps})";
		$sql = "
			select
				-- select
				DCP.DispCheckPeriod_id,
				DCP.DispCheckPeriod_Name,
				DCP.PeriodCap_id,
				convert(varchar(10), DCP.DispCheckPeriod_begDate, 120) as DispCheckPeriod_begDate,
				convert(varchar(10), DCP.DispCheckPeriod_endDate, 120) as DispCheckPeriod_endDate
				-- end select
			from
				-- from
				v_DispCheckPeriod DCP with(nolock)
				-- end from
			where
				-- where
				YEAR(DCP.DispCheckPeriod_begDate) = :Year {$PeriodCaps}
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
			select top 1
				PODL.PlanObsDisp_id
			from
				v_PlanObsDispLink PODL with (nolock)
				left join v_PlanObsDispLinkStatus PODLS with(nolock) on PODLS.PlanObsDispLink_id = PODL.PlanObsDispLink_id
			where
				PODL.PlanObsDisp_id = :PlanObsDisp_id
				and PODLS.PlanPersonListStatusType_id = 2	
		", $params);

		if (!empty($resp[0]['PlanObsDisp_id'])) {
			return array('Error_Msg' => 'Нельзя удалить план, т.к. он содержит записи со статусом "Отправлено в ТФОМС".');
		}
		
		$sql = "
			declare @tmp as table(id bigint, export_id bigint)
			insert into @tmp(id, export_id)
			select PlanObsDispLink_id, PersonDopDispPlanExport_id from PlanObsDispLink where PlanObsDisp_id=:PlanObsDisp_id
			
			DELETE FROM PersonDopDispPlanExportLink WHERE PlanObsDispLink_id in (select id from @tmp)
			
			DELETE FROM PlanObsDispLinkStatus 
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id FROM v_PlanObsDispLink with(nolock) WHERE PlanObsDisp_id=:PlanObsDisp_id)
			
			DELETE FROM PlanObsDispErrorExport where PlanObsDispLink_id in (select PlanObsDispLink_id FROM v_PlanObsDispLink with(nolock) WHERE PlanObsDisp_id=:PlanObsDisp_id)
			
			DELETE FROM PlanObsDispLink
			WHERE PlanObsDisp_id=:PlanObsDisp_id
			
			DELETE FROM PersonDopDispPlanExport 
			WHERE PersonDopDispPlanExport_id in (select export_id from @tmp)
			
			DELETE FROM PlanObsDisp
			WHERE PlanObsDisp_id=:PlanObsDisp_id
		";
		
		if(getRegionNick()=='ekb') {
			$sql = "DELETE FROM r66.TFOMSWorkDirectionPlanObsDispLink WHERE PlanObsDisp_id=:PlanObsDisp_id
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
		$this->db->query("update PlanPersonList with (rowlock) set PlanPersonListStatus_id = null where PlanPersonList_id = :PlanPersonList_id", array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		$resp_ppls = $this->queryResult("
			select
				PlanPersonListStatus_id
			from
				v_PlanPersonListStatus (nolock)
			where
				PlanPersonList_id = :PlanPersonList_id
		", array(
			'PlanPersonList_id' => $data['PlanPersonList_id']
		));

		if (is_array($resp_ppls)) {
			foreach ($resp_ppls as $one_ppls) {
				$query = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec p_PlanPersonListStatus_del
						@PlanPersonListStatus_id = :PlanPersonListStatus_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PlanPersonList_del
				@PlanPersonList_id = :PlanPersonList_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
		$filter = 'YEAR(DCP.DispCheckPeriod_begDate)=:PlanObsDisp_Year';
		
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
				select top 1
					pddpel.PersonDopDispPlanExport_id
				from
					v_PlanObsDispLink PODL (nolock)
					inner join v_PersonDopDispPlanExportLink pddpel (nolock) on pddpel.PersonDopDispPlanExport_id = PODL.PersonDopDispPlanExport_id
					inner join v_PersonDopDispPlanExport pddpe (nolock) on pddpe.PersonDopDispPlanExport_id = pddpel.PersonDopDispPlanExport_id
				where
					PODL.PlanObsDisp_id = POD.PlanObsDisp_id
					{$filter_dt}
			)";
		}
		if(getRegionNick()=='ekb') {
			$select="
				workdirection.TFOMSWorkDirection_Name,
				workdirection.TFOMSWorkDirection_id,
			";
			$from = "outer apply(
				select top 1 WD.TFOMSWorkDirection_id, WD.TFOMSWorkDirection_Name
				from r66.TFOMSWorkDirectionPlanObsDispLink WDL with(nolock)
					left join r66.TFOMSWorkDirection WD with(nolock) on WD.TFOMSWorkDirection_id=WDL.TFOMSWorkDirection_id
				where WDL.PlanObsDisp_id = POD.PlanObsDisp_id
				) workdirection
			";
			if(!empty($data['TFOMSWorkDirection_id'])) {
				$filter .= " and workdirection.TFOMSWorkDirection_id = :TFOMSWorkDirection_id";
				$params['TFOMSWorkDirection_id'] = $data['TFOMSWorkDirection_id'];
			}
		}

		if(getRegionNick()=='buryatiya') {
			if(!empty($data['OrgSMO_id'])) {
				$filter .= " and OS.OrgSMO_id = :OrgSMO_id";
				$params['OrgSMO_id'] = $data['OrgSMO_id'];
			}
		}
		
		$sql = "
			select
				-- select
				{$select}
				POD.PlanObsDisp_id,
				OS.OrgSMO_Nick,
				convert(varchar(10), POD.PlanObsDisp_CreateDate, 104) as PlanObsDisp_CreateDate,
				DCP.DispCheckPeriod_id,
				YEAR(DCP.DispCheckPeriod_begDate) as DispCheckPeriod_Year,
				MONTH(DCP.DispCheckPeriod_begDate) as DispCheckPeriod_Month,
				DCP.DispCheckPeriod_Name,
				PC.pcount as PlanObsDisp_Count,
				PCF.pcount as PlanObsDisp_CountTFOMS,
				PID.PersonDopDispPlanExport_impDate,
				case when PS.PlanObsDispLink_id is not null then 0 else 1 end as accessDelete
				-- end select
			from 
				-- from
				v_PlanObsDisp POD with(nolock)
				inner join v_DispCheckPeriod DCP with(nolock) on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
				outer apply (
					select count(distinct PODL.PlanObsDispLink_id) as pcount
					from v_PlanObsDispLink PODL with(nolock)
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id
				) PC
				outer apply (
					select count(distinct PODL.PlanObsDispLink_id) as pcount
					from v_PlanObsDispLink PODL with(nolock)
					left join v_PlanObsDispLinkStatus PODLS with(nolock) on PODLS.PlanObsDispLink_id=PODL.PlanObsDispLink_id
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id and PODLS.PlanPersonListStatusType_id=3
				) PCF
				outer apply (
					select top 1 PE.PersonDopDispPlanExport_impDate
					from v_PlanObsDispLink PODL with(nolock)
					left join v_PersonDopDispPlanExport PE with(nolock) on PODL.PersonDopDispPlanExport_id=PE.PersonDopDispPlanExport_id
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id
				) PID
				outer apply (
					select top 1 PODL.PlanObsDispLink_id
					from v_PlanObsDispLink PODL with(nolock)
					left join v_PlanObsDispLinkStatus PODLS with(nolock) on PODL.PlanObsDispLink_id = PODLS.PlanObsDispLink_id
					where PODL.PlanObsDisp_id=POD.PlanObsDisp_id and PODLS.PlanPersonListStatusType_id=2
				) PS
				outer apply (
					select top 1
						OS.OrgSMO_Nick,
						OS.OrgSMO_id
					from v_PlanObsDispLink PODL with(nolock)
					left join v_PersonDisp PD with(nolock) on PD.PersonDisp_id = PODL.PersonDisp_id
					left join v_PersonState PS with(nolock) on PS.Person_id = PD.Person_id
					left join v_Polis p with(nolock) on p.Polis_id = PS.Polis_id
					left join v_OrgSMO OS with(nolock) on OS.OrgSMO_id = p.OrgSMO_id
					where PODL.PlanObsDisp_id = POD.PlanObsDisp_id
				) OS
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
				PDDPE.PersonDopDispPlanExport_id,
				PDDPE.PersonDopDispPlanExport_FileName,
				convert(varchar(10), PDDPE.PersonDopDispPlanExport_expDate, 104) as PersonDopDispPlanExport_expDate,
				PC.pcount as PersonDopDispPlanExport_Count,
				convert(varchar(10), PDDPE.PersonDopDispPlanExport_impDate, 104) as PersonDopDispPlanExport_impDate,
				DCP.DispCheckPeriod_Name,
				OS.OrgSMO_Nick,
				PDDPE.PersonDopDispPlanExport_isUsed,
				PDDPE.PersonDopDispPlanExport_DownloadLink,
				PDDPE.PersonDopDispPlanExport_PackNum
				-- end select
			from
				-- from
				v_PlanObsDisp POD with(nolock)
				left join v_PlanObsDispLink PODL with(nolock) on PODL.PlanObsDisp_id=POD.PlanObsDisp_id
				left join v_DispCheckPeriod DCP with(nolock) on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
				inner join PersonDopDispPlanExportLink PDDPEL with(nolock) on PDDPEL.PlanObsDispLink_id=PODL.PlanObsDispLink_id
				left join v_PersonDopDispPlanExport PDDPE with(nolock) on PDDPE.PersonDopDispPlanExport_id = PDDPEL.PersonDopDispPlanExport_id
				outer apply (
					select count(distinct PD.Person_id) pcount
					from v_PlanObsDispLink PL with(nolock)
					left join v_PersonDisp PD with(nolock) on PD.PersonDisp_id = PL.PersonDisp_id
					left join v_PersonDopDispPlanExportLink EL with(nolock) on El.PlanObsDispLink_id = PL.PlanObsDispLink_id
					where
					EL.PersonDopDispPlanExport_id=PDDPE.PersonDopDispPlanExport_id
				) PC
				outer apply (
					select top 1
						OS.OrgSMO_Nick
					from v_PlanObsDispLink PODL with(nolock)
					left join v_PersonDisp PD with(nolock) on PD.PersonDisp_id = PODL.PersonDisp_id
					left join v_PersonState PS with(nolock) on PS.Person_id = PD.Person_id
					left join v_Polis p with(nolock) on p.Polis_id = PS.Polis_id
					left join v_OrgSMO OS with(nolock) on OS.OrgSMO_id = p.OrgSMO_id
					where PODL.PlanObsDisp_id = POD.PlanObsDisp_id
				) OS
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
			DECLARE	@PlanObsDisp_id bigint = :PlanObsDisp_id,
					@Error_Code int,
					@Error_Message varchar(4000),
					@getDT datetime = dbo.tzGetDate(),
					@PeriodCap_id int;
					
			SELECT @PeriodCap_id = PeriodCap_id from DispCheckPeriod with(nolock) where DispCheckPeriod_id = :DispCheckPeriod_id

			EXEC	{$procedure}
					@PlanObsDisp_id = @PlanObsDisp_id OUTPUT,
					@DispCheckPeriod_id = :DispCheckPeriod_id,
					@PlanObsDisp_CreateDate = @getDT,
					@Lpu_id = :Lpu_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT

			SELECT	@PlanObsDisp_id as PlanObsDisp_id,
					@PeriodCap_id as PeriodCap_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Message
					
			
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
		$query = "update PersonDopDispPlanExport with (rowlock) set PersonDopDispPlanExport_IsUsed = :PersonDopDispPlanExport_IsUsed where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id";
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
			declare @curDate datetime = dbo.tzGetDate();
			
			declare
				@PersonDopDispPlanExport_id bigint = :PersonDopDispPlanExport_id,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			exec p_PersonDopDispPlanExport_upd
				@PersonDopDispPlanExport_id = @PersonDopDispPlanExport_id,
				@PersonDopDispPlanExport_FileName = :PersonDopDispPlanExport_FileName,
				@PersonDopDispPlanExport_expDate = @curDate,
				@PersonDopDispPlanExport_PackNum = :PacketNumber,
				@PersonDopDispPlanExport_impDate = null,
				@Lpu_id = :Lpu_id,
				@PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year,
				@PersonDopDispPlanExport_Month = :PersonDopDispPlanExport_Month,
				@PersonDopDispPlanExport_DownloadQuarter = :PersonDopDispPlanExport_DownloadQuarter,
				@PersonDopDispPlanExport_IsUsed = :PersonDopDispPlanExport_IsUsed,
				@PersonDopDispPlanExport_IsCreatedTFOMS = :PersonDopDispPlanExport_IsCreatedTFOMS,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PersonDopDispPlanExport_id as PersonDopDispPlanExport_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
			select count(*) from v_PlanObsDisp with(nolock) where DispCheckPeriod_id = :DispCheckPeriod_id AND Lpu_id = :Lpu_id
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
		if(!empty($data['OrgSMO_id'])) {
			$filter.=" AND OS.OrgSMO_id = :OrgSMO_id";
			$params['OrgSMO_id'] = $data['OrgSMO_id'];
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
				$podfilter = "+' '+PS.Person_FirName+' '+PS.Person_SecName";
			}
			$filter .= " and PS.Person_SurName{$podfilter} like :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'].'%';
		}
		if(getRegionNick()=='ekb') {
			$select.=",tfomsWD.TFOMSWorkDirection_Name as WorkDirection";
			$from.=" 
				outer apply (
					select top 1
						WD.TFOMSWorkDirection_id,
						WD.TFOMSWorkDirection_Code,
						WD.TFOMSWorkDirection_Name
					from r66.TFOMSWorkDirectionDiagLink WDD with(nolock)
						inner join r66.TFOMSWorkDirection WD with(nolock) on WD.TFOMSWorkDirection_id=WDD.TFOMSWorkDirection_id
					where WDD.Diag_id = D.Diag_id
				) tfomsWD
				left join v_Person P with (nolock) on P.Person_id = PD.Person_id
				left join v_Polis po (nolock) on po.Polis_id = PS.Polis_id
			";
			$filter.=" and COALESCE(P.BDZ_id, po.BDZ_id,0)!=0";
		}
		$sql = "
			SELECT
				-- select
				PS.Person_id,
				PS.Server_id,
				RTRIM(PS.Person_SurName) + ' ' + isnull(PS.Person_FirName, '') + ' ' + isnull(PS.Person_SecName, '') as Person_FIO,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				'' as Person_Work,
				PD.PersonDisp_NumCard as CardNumber,
				convert(varchar(10), PD.PersonDisp_begDate, 104) as begDate,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as endDate,
				D.Diag_Code+' '+D.Diag_Name as Diagnoz,
				StatType.PlanPersonListStatusType_Name as StatusType_Name,
				StatType.PlanPersonListStatusType_id as StatusType_id,
				convert(varchar(10), PODstat.PlanObsDispLinkStatus_setDate, 104) as StatusDate,
				convert(varchar(10), vizit.PersonDispVizit_NextDate, 104) as VizitDate,
				STUFF(
					(SELECT DISTINCT
						', '+rtrim(isnull(convert(varchar, PET.PlanObsDispErrorType_Code, 0),''))
					FROM
						PlanObsDispErrorExport PEE (nolock)  
						left join PlanObsDispErrorType PET (nolock) on PET.PlanObsDispErrorType_id=PEE.PlanObsDispErrorType_id
					WHERE
						PEE.PlanObsDispLink_id=PODL.PlanObsDispLink_id
					FOR XML PATH ('')
					), 1, 2, ''
				) as Errors,
				STUFF(
					(SELECT DISTINCT
						', '+rtrim(isnull(convert(varchar, PET.PlanObsDispErrorType_Code, 0)+' - '+rtrim(isnull(PET.PlanObsDispErrorType_Descr,'')),''))
					FROM
						PlanObsDispErrorExport PEE (nolock)  
						left join PlanObsDispErrorType PET (nolock) on PET.PlanObsDispErrorType_id=PEE.PlanObsDispErrorType_id
					WHERE
						PEE.PlanObsDispLink_id=PODL.PlanObsDispLink_id
					FOR XML PATH ('')
					), 1, 2, ''
				) as Errors_text,
				PODL.PersonDisp_id,
				PODL.PlanObsDispLink_id
				{$select}
				-- end select
			FROM
				-- from
				v_PlanObsDispLink PODL with(nolock)
				left join v_PersonDisp PD (nolock) on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_PersonState PS (nolock) on PS.Person_id = PD.Person_id
				left join v_Polis p with(nolock) on p.Polis_id = PS.Polis_id
				left join v_OrgSMO OS with(nolock) on OS.OrgSMO_id = p.OrgSMO_id
				left join v_Diag D (nolock) on D.Diag_id = PD.Diag_id
				left join v_PlanObsDispLinkStatus PODstat (nolock) on PODstat.PlanObsDispLink_id = PODL.PlanObsDispLink_id
				left join v_PlanPersonListStatusType StatType (nolock) on StatType.PlanPersonListStatusType_id = PODstat.PlanPersonListStatusType_id
				left join v_PersonDispVizit vizit (nolock) on vizit.PersonDispVizit_id = PODL.PersonDispVizit_id
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
			SELECT POD.Lpu_id, POD.DispCheckPeriod_id, DCP.PeriodCap_id, 
				convert(varchar(10), DCP.DispCheckPeriod_begDate, 120) as DispCheckPeriod_begDate, 
				convert(varchar(10), DCP.DispCheckPeriod_endDate, 120) as DispCheckPeriod_endDate
			FROM v_PlanObsDisp POD with(nolock)
			left join v_DispCheckPeriod DCP (nolock) on DCP.DispCheckPeriod_id=POD.DispCheckPeriod_id
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
				$podfilter = "+' '+PS.Person_FirName+' '+PS.Person_SecName";
			}
			$filter .= " and PS.Person_SurName{$podfilter} like :Person_FIO";
			$params['Person_FIO'] = $data['Person_FIO'].'%';
		}
		$select = "
			PD.Person_id, 
			PD.PersonDisp_id,
			RTRIM(PS.Person_SurName) + ' ' + isnull(PS.Person_FirName, '') + ' ' + isnull(PS.Person_SecName, '') as Person_FIO,
			convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
			PD.PersonDisp_NumCard as CardNumber,
			convert(varchar(10), PD.PersonDisp_begDate, 104) as begDate,
			convert(varchar(10), PD.PersonDisp_endDate, 104) as endDate,
			D.Diag_Code+' '+D.Diag_Name as Diagnoz,
			convert(varchar(10), PDVizit.PersonDispVizit_NextDate, 104) as VizitDate
			--convert(varchar(10), vizit.vizitdate, 104) as LastVizitDate
		";
		$from = "
			v_PersonDisp PD with(nolock)
			left join v_PersonState PS with(nolock) on PS.Person_id = PD.Person_id
			left join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
			left join v_PersonDispVizit PDVizit with(nolock) on PDVizit.PersonDisp_id=PD.PersonDisp_id
			/*outer apply (
				select top 1 PersonDispVizit_NextDate as vizitdate
				from v_PersonDispVizit PDV with(nolock)
				where PDV.PersonDisp_id = PD.PersonDisp_id
				order by PDV.PersonDispVizit_NextDate DESC
			) vizit*/
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
						(isnull(YEAR(PD.PersonDisp_begDate), 0) < YEAR(:DispCheckPeriod_begDate) OR
						isnull(YEAR(PD.PersonDisp_endDate), YEAR(:DispCheckPeriod_begDate)) = YEAR(:DispCheckPeriod_begDate)) and
						not exists(
							SELECT PDV.PersonDisp_id
							FROM v_PersonDispVizit PDV with(nolock)
							WHERE PDV.PersonDisp_id=PD.PersonDisp_id AND YEAR(PDV.PersonDispVizit_NextDate)=YEAR(:DispCheckPeriod_begDate)
						) and
						not exists(select * from PlanObsDispLink PODL with(nolock) where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id)
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
						(isnull(PD.PersonDisp_endDate, :DispCheckPeriod_endDate) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate) and
						isnull(PD.PersonDisp_begDate, :DispCheckPeriod_endDate) <= :DispCheckPeriod_endDate and
						not exists(
							SELECT PDV.PersonDisp_id
							FROM v_PersonDispVizit PDV with(nolock)
							WHERE PDV.PersonDisp_id=PD.PersonDisp_id AND YEAR(PDV.PersonDispVizit_NextDate)=YEAR(:DispCheckPeriod_begDate)
						) and
						not exists(select PODL.PlanObsDispLink_id from PlanObsDispLink PODL with(nolock) where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id)
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
			WHERE PlanObsDispLink_id = :PlanObsDispLink_id
			
			DELETE FROM PlanObsDispLink
			WHERE PlanObsDispLink_id = :PlanObsDispLink_id
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
			UPDATE PlanObsDispLinkStatus with(rowlock) 
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