<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * PlanObsDisp_model - модель для работы с планами ДН по контрольным посещениям (Екатеринбург)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
require(APPPATH . 'models/PlanObsDisp_model.php');

class Ekb_PlanObsDisp_model extends PlanObsDisp_model {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Данные грида для плана / перенес из свн
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
	 * Получить справочник направлений работы
	 */
	function getWorkDirectionSpr() {
		$sql = "
			SELECT
				TFOMSWorkDirection_id,
				TFOMSWorkDirection_Code,
				TFOMSWorkDirection_Name,			 
				convert(varchar,cast(TFOMSWorkDirection_begDT as datetime),104) as TFOMSWorkDirection_begDT,
				convert(varchar,cast(TFOMSWorkDirection_endDT as datetime),104) as TFOMSWorkDirection_endDT
			FROM r66.TFOMSWorkDirection
			WHERE TFOMSWorkDirection_Division=2
		";
		$result = $this->db->query($sql, array());
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Ошибки данных. Для грида на форме план КП ДН		
	 */
	function loadPlanErrorData($data) {
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id']
		);
		$sql ="
			SELECT top 1 POD.Lpu_id, POD.DispCheckPeriod_id, DCP.PeriodCap_id, 
				convert(varchar(10), DCP.DispCheckPeriod_begDate, 120) as DispCheckPeriod_begDate, 
				convert(varchar(10), DCP.DispCheckPeriod_endDate, 120) as DispCheckPeriod_endDate,
				WDP.TFOMSWorkDirection_id
			FROM v_PlanObsDisp POD with(nolock)
			left join v_DispCheckPeriod DCP (nolock) on DCP.DispCheckPeriod_id=POD.DispCheckPeriod_id
			left join r66.TFOMSWorkDirectionPlanObsDispLink WDP (nolock) on WDP.PlanObsDisp_id=POD.PlanObsDisp_id
			WHERE POD.PlanObsDisp_id=:PlanObsDisp_id
		";
		$par = $this->queryResult($sql, $params);
		$params['Lpu_id'] = $par[0]['Lpu_id'];
		$params['PeriodCap_id'] = $par[0]['PeriodCap_id'];
		$params['DispCheckPeriod_id'] = $par[0]['DispCheckPeriod_id'];
		$params['DispCheckPeriod_begDate'] = $par[0]['DispCheckPeriod_begDate'];
		$params['DispCheckPeriod_endDate'] = $par[0]['DispCheckPeriod_endDate'];
		$params['TFOMSWorkDirection_id'] = $par[0]['TFOMSWorkDirection_id'];
		
		$filter = " AND exists(
			select top 1 WDDL.TFOMSWorkDirection_id
			from r66.TFOMSWorkDirectionDiagLink WDDL (nolock)
			where WDDL.TFOMSWorkDirection_id = :TFOMSWorkDirection_id AND WDDL.Diag_id=PD.Diag_id
			and (WDDL.TFOMSWorkDirectionDiagLink_endDT is null or WDDL.TFOMSWorkDirectionDiagLink_endDT > getdate())
		)";
		
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
			convert(varchar(10), vizit.vizitdate, 104) as LastVizitDate,
			case 
				when PODL.ErrorType IS NULL THEN 'Для данной карты ДН не заведено ни одного контр. посещения'
				when PODL.ErrorType IS NOT NULL THEN 'У пациента неизвестен идентификатор ЗЛ в РСЕРЗ'
			end as Errors
		";
		$from = "
			v_PersonDisp PD with(nolock)
			left join v_PersonState PS with(nolock) on PS.Person_id = PD.Person_id
			left join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
			outer apply (
				select top 1 PersonDispVizit_NextDate as vizitdate
				from v_PersonDispVizit PDV with(nolock)
				where PDV.PersonDisp_id = PD.PersonDisp_id
				order by PDV.PersonDispVizit_NextDate DESC
			) vizit
			left join v_Person P with (nolock) on P.Person_id = PD.Person_id
			left join v_Polis po (nolock) on po.Polis_id = PS.Polis_id
			outer apply (
				select top 1 
					1 as ErrorType
				from v_PersonDispVizit PDV with(nolock)
				where PDV.PersonDisp_id=PD.PersonDisp_id AND YEAR(PDV.PersonDispVizit_NextDate)=YEAR(:DispCheckPeriod_begDate)
			) as MVY
			outer apply (
				select top 1
					1 as ErrorType
				from v_PlanObsDispLink PODL with(nolock)
				where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id
			) as PODL
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
						(
							(
								MVY.ErrorType IS NULL
								and PODL.ErrorType IS null
								and (isnull(YEAR(PD.PersonDisp_begDate), 0) < YEAR(:DispCheckPeriod_begDate)
								OR isnull(YEAR(PD.PersonDisp_endDate), YEAR(:DispCheckPeriod_begDate)) = YEAR(:DispCheckPeriod_begDate)) 
							) 
							or 
							(COALESCE(P.BDZ_id, po.BDZ_id,0)=0 and PODL.ErrorType IS NOT null)
						) 
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
						(
							(
								MVY.ErrorType IS NULL 
								and PODL.ErrorType IS NULL
								and (isnull(PD.PersonDisp_endDate, :DispCheckPeriod_endDate) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate) 
								and isnull(PD.PersonDisp_begDate, :DispCheckPeriod_endDate) <= :DispCheckPeriod_endDate 
							) 
							or 
							(COALESCE(P.BDZ_id, po.BDZ_id,0)=0 and PODL.ErrorType IS NOT null)
						) 
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
	 * Проверка дублирования планов
	 */
	function checkPlanObsDispDoubles($data) {
		$params= array('Lpu_id'=>$data['Lpu_id'], 'DispCheckPeriod_id'=>$data['DispCheckPeriod_id'], 'TFOMSWorkDirection_id'=>$data['TFOMSWorkDirection_id']);
		$sql = "
			select count(*) 
			from v_PlanObsDisp POD with(nolock)
				left join r66.TFOMSWorkDirectionPlanObsDispLink WD (nolock) on WD.PlanObsDisp_id=POD.PlanObsDisp_id
			where POD.DispCheckPeriod_id = :DispCheckPeriod_id AND POD.Lpu_id = :Lpu_id AND WD.TFOMSWorkDirection_id = :TFOMSWorkDirection_id
		";
		//~ exit(getDebugSQL($sql, $params));
		$cnt = $this->getFirstResultFromQuery($sql, $params);
		
		if($cnt) throw new Exception('Сохранение плана невозможно т.к. план с таким периодом и направлением работы уже существует');
		
		return $cnt;
	}
	
	/**
	 * Сохранить план
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
				$setwd = "";
				$procedure = "[r66].p_TFOMSWorkDirectionPlanObsDispLink_";
				if(empty($params['PlanObsDisp_id'])) {
					$procedure .= "ins";
				} else {
					$setwd = "SELECT top 1 @TFOMSWorkDirectionPlanObsDispLink_id = WD.TFOMSWorkDirectionPlanObsDispLink_id
						FROM r66.TFOMSWorkDirectionPlanObsDispLink WD with (nolock) where WD.PlanObsDisp_id = :PlanObsDisp_id";
					$procedure .= "upd";
				}
				$sql = "
				DECLARE @PlanObsDisp_id bigint = :PlanObsDisp_id,
						@TFOMSWorkDirectionPlanObsDispLink_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)
				{$setwd}
				EXEC	{$procedure}
						@TFOMSWorkDirectionPlanObsDispLink_id = @TFOMSWorkDirectionPlanObsDispLink_id,
						@TFOMSWorkDirection_id = :TFOMSWorkDirection_id,
						@PlanObsDisp_id = :PlanObsDisp_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT

				SELECT	@Error_Code as Error_Code,
						@Error_Message as Error_Message
				";
				$params['PlanObsDisp_id'] = $result[0]['PlanObsDisp_id'];
				$resultwd = $this->db->query($sql, $params);
				
				if (is_object($resultwd)) {
					$resultwd = $resultwd->result('array');
					if(count($resultwd)>0) {
						$resultwd = array_merge($result[0], $resultwd[0]);
						$this->commitTransaction();
						return $resultwd;
					}
				}
			}
		}
		$this->rollbackTransaction();
		return false;
	}
	
	/**
	 * Сформировать план
	 */
	function makePlanObsDispLink($data) {
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'], 
			'Lpu_id'=>$data['Lpu_id'],
			'DispCheckPeriod_begDate'=>$data['DispCheckPeriod_begDate'],
			'DispCheckPeriod_endDate'=>$data['DispCheckPeriod_endDate'],
			'TFOMSWorkDirection_id'=>$data['TFOMSWorkDirection_id'],
			'pmUser_id'=>$data['pmUser_id']
		);
		//Периодом плана считается период от первого числа месяца, выбранного в поле «Период», до конца года.
		$sql = "
			DECLARE @curDT date = dbo.tzGetdate();
			DECLARE @tmpPlanLink TABLE (PlanObsDisp_id bigint, PersonDisp_id bigint, PersonDispVizit_id bigint);
			
			INSERT INTO @tmpPlanLink (PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id)
			SELECT
				:PlanObsDisp_id, PD.PersonDisp_id, PDV.PersonDispVizit_id
			FROM 
				v_PersonDisp PD with(nolock)
				inner join v_PersonState PS on PS.Person_id = PD.Person_id
				left join v_PersonDispVizit PDV with(nolock) on PDV.PErsonDisp_id=PD.PersonDisp_id
			WHERE 
				PD.Lpu_id=:Lpu_id
				and dbo.Age2(PS.Person_Birthday, :DispCheckPeriod_begDate) >= 18
				and not exists( --еще не было этого посещения в плане
					select PODL.PlanObsDisp_id
					from v_PlanObsDispLink PODL with(nolock) 
					where PODL.PlanObsDisp_id=:PlanObsDisp_id AND PODL.PersonDisp_id=PD.PersonDisp_id and PersonDispVizit_id=PDV.PersonDispVizit_id
				)
				and ( --пункт 1
					(PD.PersonDisp_begDate is null OR PD.PersonDisp_begDate <= :DispCheckPeriod_endDate) 
					AND (PD.PersonDisp_endDate is null OR YEAR(PD.PersonDisp_endDate) = YEAR(:DispCheckPeriod_begDate))
				)
				and exists ( --пункт 2
					select top 1 WDDL.TFOMSWorkDirection_id
					from r66.TFOMSWorkDirectionDiagLink WDDL with(nolock)
					where WDDL.Diag_id = PD.Diag_id and  WDDL.TFOMSWorkDirection_id = :TFOMSWorkDirection_id
					and (WDDL.TFOMSWorkDirectionDiagLink_endDT is null or WDDL.TFOMSWorkDirectionDiagLink_endDT > getdate())
				)
				and (PDV.PersonDispVizit_NextDate >= :DispCheckPeriod_begDate AND YEAR(PDV.PersonDispVizit_NextDate) = YEAR(:DispCheckPeriod_endDate)) --пункт 3.1
				and not exists( --пункт 3.2
					select PDV3.PersonDispVizit_NextDate
					from v_PersonDispVizit PDV3 with(nolock)
					inner join v_PersonDisp PD3 with(nolock) on PD3.PersonDisp_id=PDV3.PersonDisp_id
					inner join v_PlanObsDispLink PODL with(nolock) on PODL.PersonDispVizit_id=PDV3.PersonDispVizit_id
					where MONTH(PDV.PersonDispVizit_NextDate) = MONTH(PDV3.PersonDispVizit_NextDate) AND PODL.PlanObsDisp_id = :PlanObsDisp_id AND PD3.Person_id=PD.Person_id
				)
				and (PD.PersonDisp_endDate is null OR PDV.PersonDispVizit_NextDate < PD.PersonDisp_endDate) --пункт 4
			
			INSERT INTO PlanObsDispLink
				(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID, PlanObsDispLink_insDT, PlanObsDispLink_updDT)
			SELECT tmp.PlanObsDisp_id,tmp.PersonDisp_id,tmp.PersonDispVizit_id,:pmUser_id,:pmUser_id,@curDT,@curDT
			FROM @tmpPlanLink tmp
			
			INSERT INTO PlanObsDispLinkStatus (PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID, pmUser_updID, PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
			SELECT 1, PODL.PlanObsDispLink_id, @curDT, :pmUser_id, :pmUser_id, @curDT, @curDT
			FROM v_PlanObsDispLink PODL with(nolock)
			inner join @tmpPlanLink tmp on tmp.PlanObsDisp_id=PODL.PlanObsDisp_id AND tmp.PersonDisp_id=PODL.PersonDisp_id AND tmp.PersonDispVizit_id=PODL.PersonDispVizit_id
		";
		//~ exit(getDebugSQL($sql, $params));
		$result = $this->db->query($sql, $params);
		return $result;
	}

	/**
	 * Экспорт плана
	 */
	function exportPlanObsDisp($data) {
		set_time_limit(0);
		//собираем информацию о плане
		$PlanInfo = $this->queryResult("
			SELECT
				YEAR(DCP.DispCheckPeriod_begDate) as Export_Year,
				case when DCP.PeriodCap_id=4
					then (MONTH(DCP.DispCheckPeriod_begDate)+2)/3
					else 1
				end as Export_Quart,
				MONTH(DCP.DispCheckPeriod_begDate) as Export_Month,
				tfomsWD.TFOMSWorkDirection_Code,
				DCP.PeriodCap_id
			FROM
				v_PlanObsDisp POD with(nolock)
				inner join v_DispCheckPeriod DCP with(nolock) on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
				outer apply (
					select top 1 WD.TFOMSWorkDirection_Code
					from r66.TFOMSWorkDirectionPlanObsDispLink WDL with(nolock)
					inner join r66.TFOMSWorkDirection WD with(nolock) on WD.TFOMSWorkDirection_id=WDL.TFOMSWorkDirection_id
					where WDL.PlanObsDisp_id=POD.PlanObsDisp_id
				) tfomsWD
			WHERE
				POD.PlanObsDisp_id = :PlanObsDisp_id
		", array('PlanObsDisp_id'=>$data['PlanObsDisp_id']));
		$PlanInfo = $PlanInfo[0];
		//проверяем не занят ли номер пакета
		$resp_check = $this->queryResult("
			select top 1
				pddpe.PersonDopDispPlanExport_id,
				convert(varchar(10), pddpe.PersonDopDispPlanExport_insDT, 104) + ' ' + convert(varchar(5), pddpe.PersonDopDispPlanExport_insDT, 108) as PersonDopDispPlanExport_Date
			from
				v_PersonDopDispPlanExport pddpe (nolock)
			where
				pddpe.PersonDopDispPlanExport_Year = :PersonDopDispPlanExport_Year
				and pddpe.PersonDopDispPlanExport_Month = :PersonDopDispPlanExport_Month
				and pddpe.Lpu_id = :Lpu_id
				and pddpe.PersonDopDispPlanExport_PackNum = :PersonDopDispPlanExport_PackNum
		", array(
			'PersonDopDispPlanExport_PackNum' => $data['PacketNumber'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonDopDispPlanExport_Year' => $PlanInfo['Export_Year'],
			'PersonDopDispPlanExport_Month' => $PlanInfo['Export_Month'],
		));
		if (!empty($resp_check[0]['PersonDopDispPlanExport_id'])) {
			return array('Error_Msg' => "Указанный порядковый номер пакета в данном периоде занят. Укажите другой номер");
		}
		
		$sql = "
			DECLARE @curDate datetime = dbo.tzGetDate()
			 
			DECLARE
					@PersonDopDispPlanExport_id bigint,
					@Error_Code int,
					@Error_Message varchar(4000)

			EXEC	p_PersonDopDispPlanExport_ins
					@PersonDopDispPlanExport_id = @PersonDopDispPlanExport_id OUTPUT,
					@PersonDopDispPlanExport_PackNum = :PacketNumber,
					@pmUser_id = :pmUser_id,
					@Lpu_id = :Lpu_id,
					@PersonDopDispPlanExport_expDate = @curDate,
					@PersonDopDispPlanExport_Year = :Export_Year,
					@PersonDopDispPlanExport_Month = :Export_Month,
					@PersonDopDispPlanExport_DownloadQuarter = :Export_Quart,
					@Error_Code = @Error_Code OUTPUT,
					@Error_Message = @Error_Message OUTPUT

			SELECT	@PersonDopDispPlanExport_id as PersonDopDispPlanExport_id,
					@Error_Code as Error_Code,
					@Error_Message as Error_Message
		";
		$year = date('Y');
		$kvartal = intval((date('n')+2)/3);
		$res = $this->db->query($sql,array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Export_Year' => $year,//$PlanInfo['Export_Year'],
			'Export_Quart' => $kvartal,// ТЗ: номер отчетного квартала. Определяется по дате экспорта
			'Export_Month' => $PlanInfo['Export_Month'],
			'PacketNumber' => $data['PacketNumber']
		));
		
		if ( is_object($res) ) {
			$resp = $res->result('array');
			if ( count($resp) > 0 && !empty($resp[0]['PersonDopDispPlanExport_id']) ) {
				$data['PersonDopDispPlanExport_id'] = $resp[0]['PersonDopDispPlanExport_id'];
			}
		}
		
		$data['Export_Year'] = $PlanInfo['Export_Year'];
		$data['Export_Quart'] = $PlanInfo['Export_Quart'];
		$data['Export_Month'] = $PlanInfo['Export_Month'];
		$data['workdirection'] = $PlanInfo['TFOMSWorkDirection_Code'];
		$data['PeriodCap_id'] = $PlanInfo['PeriodCap_id'];

		if(empty($data['PersonDopDispPlanExport_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения данных экспорта');
		}
		
		$res = $this->_exportPlanObsDisp($data);
		
		if (!empty($res['Error_Msg'])) {
			return $res;
		}
		return array('Error_Msg' => '', 'link' => $res['link']);
	}
	
	/**
	 * Экспорт
	 */
	function _exportPlanObsDisp($data) {
		$X = 'DN';
		$LpuInfo = $this->queryResult("
			select top 1 Lpu_f003mcod from v_Lpu with(nolock) where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = sprintf("%06d", $LpuInfo[0]['Lpu_f003mcod']);
		} else {
			$Ni = '000000';
		}
		
		$N = $data['PacketNumber']; //str_pad($data['PacketNumber'], 2, '0', STR_PAD_LEFT);
		
		$K = $data['Export_Month'];//номер отчетного периода (по новому тз теперь месяц, а не квартал!). И определяется не по дате экспорта, а по полю "период" (обсудил с проектировщиком, в тз не совсем правильно) 

		$filename = $X.$Ni.'_'.substr(date('Y'), 2,2).sprintf("%02d", $K).$N;
		
		$xmlfilename = $filename . '.xml';
		$zipfilename = $filename . '.zip';
		
		$out_dir = "pod_xml_".time()."_".$data['Lpu_id'];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$xmlfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$xmlfilename;
		$zipfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$zipfilename;
				
		$this->beginTransaction();
					
		// Блокируем файл
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => 1
		));
		$union="";
		if($data['PeriodCap_id']==4) {//нужно считать также месяц снятия с ДН если в этом месяце нет посещений. Если есть, то нормально достанет и в основном запросе
			$union = "
			union 
			select distinct
				P.Person_id,
				COALESCE(P.BDZ_id, po.BDZ_id,0) as ID
			from
				v_PlanObsDispLink PODL with (nolock)
				left join v_PersonDisp PD with (nolock) on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_PersonDispVizit PDV (nolock) on PDV.PersonDispVizit_id = PODL.PersonDispVizit_id
				left join v_Person P with (nolock) on P.Person_id = PD.Person_id
				left join v_PersonState PS (nolock) on PS.Person_id = P.Person_id
				left join v_Polis po (nolock) on po.Polis_id = PS.Polis_id
			where
				PODL.PlanObsDisp_id = :PlanObsDisp_id
				and PD.PersonDisp_endDate is not null
				and MONTH(PDV.PersonDispVizit_NextDate)!=MONTH(PD.PersonDisp_endDate)
				and dbo.Age2(PS.Person_Birthday, '" . $data['Export_Year'] . "-" . sprintf('%02d', $data['Export_Month']) . "-01') >= 18
				and COALESCE(P.BDZ_id, po.BDZ_id,0)!=0
			";
		}
		// достаём данные
		$ZAPS = $this->queryResult("
			select distinct
				P.Person_id,
				COALESCE(P.BDZ_id, po.BDZ_id,0) as ID
			from
				v_PlanObsDispLink PODL with (nolock)
				left join v_PersonDisp PD with (nolock) on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_Person P with (nolock) on P.Person_id = PD.Person_id
				left join v_PersonState PS (nolock) on PS.Person_id = P.Person_id
				left join v_Polis po (nolock) on po.Polis_id = PS.Polis_id
			where
				PODL.PlanObsDisp_id = :PlanObsDisp_id
				and COALESCE(P.BDZ_id, po.BDZ_id,0)!=0
				and dbo.Age2(PS.Person_Birthday, '" . $data['Export_Year'] . "-" . sprintf('%02d', $data['Export_Month']) . "-01') >= 18
			{$union}
		", array('PlanObsDisp_id'=>$data['PlanObsDisp_id']));

		// для всех записей устанавливается статус «Отправлен в ТФОМС»
		$sql = "
			UPDATE PlanObsDispLinkStatus with(rowlock) SET PlanPersonListStatusType_id = 2
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink with(nolock) where PlanObsDisp_id = :PlanObsDisp_id)

			UPDATE PlanObsDispLink with(rowlock) SET PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink with(nolock) where PlanObsDisp_id = :PlanObsDisp_id)
		";
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'filename' => $filename,
			'pmUser_id' => $data['pmUser_id']
		);
		
		$this->db->query($sql,$params);
		
		$this->load->library('parser');
		$N_ZAP=0;
		foreach ($ZAPS as &$ZAP) {
			$N_ZAP++;
			$ZAP['N_ZAP']=$N_ZAP;
			
			//достаем дисп.карты пациента (каждая карта - один раздел DS)
			$sql ="
				SELECT DISTINCT
					PD.PersonDisp_id,
					D.Diag_Code AS MKB,
					convert(varchar(10), PD.PersonDisp_DiagDate, 120) MKBDT,
					convert(varchar(10), PD.PersonDisp_endDate, 120) as EXCLDS,
					MP.MedPersonal_Code as DOCTOR
				FROM
					v_PlanObsDispLink POD with (nolock)
					left join v_PersonDisp PD with (nolock) on PD.PersonDisp_id = POD.PersonDisp_id
					left join v_Diag D with (nolock) on D.Diag_id=PD.Diag_id
					left join v_MedPersonal MP on MP.MedPersonal_id = PD.MedPersonal_id
				WHERE
					POD.PlanObsDisp_id = :PlanObsDisp_id AND PD.Person_id = :Person_id
			";
			$DS = $this->queryResult($sql, array('PlanObsDisp_id'=>$data['PlanObsDisp_id'], 'Person_id'=>$ZAP['Person_id']));
			
			foreach ($DS as $DSi => &$card) {
				//достаем посещения для карты
				$sql ="
					SELECT
						MONTH(PDV.PersonDispVizit_NextDate) as PLAN_P_MONTH,
						YEAR(PDV.PersonDispVizit_NextDate) as PLAN_P_YEAR,
						PDV.PersonDispVizit_IsHomeDN as SIGNHOME
					FROM
						v_PersonDispVizit PDV with(nolock)
					WHERE
						PDV.PersonDisp_id = :PersonDisp_id
				";
				$vizits = $this->queryResult($sql, array('PersonDisp_id'=>$card['PersonDisp_id']));
				$IS_SIGNHOME = false;
				$plans = array();
				if(is_array($vizits)) {
					foreach ($vizits as &$vizit) if($vizit['PLAN_P_MONTH'] >= $data['Export_Month'] && $vizit['PLAN_P_YEAR'] == $data['Export_Year'] ) {
						$plans[] = array('PLAN_P'=> $vizit['PLAN_P_MONTH']);
						if($vizit['SIGNHOME'] == '2') $IS_SIGNHOME = true;
					}
				}
				$card['PLAN_MONTH'] = $plans;
				$card['PERIOD'] = count($plans);
				$card['SIGNHOME'] = $IS_SIGNHOME;
			}
			
			$ZAP['DS'] = $DS;
			
			$params = array(
				'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
				'record_number'=>$N_ZAP,
				'Lpu_id'=>$data['Lpu_id'],
				'Person_id'=>$ZAP['Person_id'],
				'PersonDopDispPlanExport_id'=>$data['PersonDopDispPlanExport_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			//получаем идешники сущностей плана по текущей записи
			$sql = "
				select distinct PODL.PlanObsDispLink_id
				from v_PlanObsDispLink PODL
					inner join v_PersonDisp PD on PD.PersonDisp_id=PODL.PersonDisp_id
					inner join v_PersonDispVizit PDV on PDV.PersonDisp_id=PD.PersonDisp_id and PODL.PersonDispVizit_id=PDV.PersonDispVizit_id
				where PD.Person_id=:Person_id and PD.Lpu_id=:Lpu_id and PODL.PlanObsDisp_id=:PlanObsDisp_id
			";
			
			$resp = $this->queryResult($sql, $params);
			foreach($resp as $item) {
				$params['PlanObsDispLink_id'] = $item['PlanObsDispLink_id'];
				//по всем сущностям проставляем порядковый номер записи и создаем связь на файл экспорта
				$sql = "
					UPDATE PlanObsDispLink with(rowlock) SET PlanObsDispLink_Num=:record_number WHERE PlanObsDispLink_id=:PlanObsDispLink_id
					
					DECLARE	@PersonDopDispPlanExportLink_id bigint,
							@Error_Code int,
							@Error_Message varchar(4000)

					EXEC	p_PersonDopDispPlanExportLink_ins
							@PersonDopDispPlanExportLink_id = @PersonDopDispPlanExportLink_id OUTPUT,
							@PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id,
							@PlanObsDispLink_id = :PlanObsDispLink_id,
							@PersonDopDispPlanExportLink_Num = :record_number,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code OUTPUT,
							@Error_Message = @Error_Message OUTPUT

					SELECT	@PersonDopDispPlanExportLink_id as PersonDopDispPlanExportLink_id,
							@Error_Code as Error_Code,
							@Error_Message as Error_Message
				";
				$this->db->query($sql,$params);
			}
		}
		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse_ext('export_xml/export_planobsdisp_ekb', array(
				'FILENAME' => $filename,
				'CODE_ORG' => $Ni,
				'YEAR' => $data['Export_Year'],
				'MONTH' => $data['Export_Month'],
				'NRECORDS'=>$N_ZAP,
				'ZAP'=>$ZAPS
			), true, false, array(), false);//пустые теги не выводить
		file_put_contents($xmlfilepath, $xml);
		
		// запаковываем
		$zip = new ZipArchive();
		$zip->open($zipfilepath, ZIPARCHIVE::CREATE);
		$zip->AddFile($xmlfilepath, $xmlfilename);
		$zip->close();
		
		// Пишем ссылку
		$query = "
			update PersonDopDispPlanExport with(rowlock) set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink,
				PersonDopDispPlanExport_FileName = :Export_FileName
			where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
		";
		$res = $this->db->query($query, array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_DownloadLink' => $zipfilepath,
			'Export_FileName' => $filename
		));
		
		if($res!==true) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка при формировании ссылки на файл экспорта');
		}
		
		// Снимаем блокировку
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => null
		));
		
		$this->commitTransaction();

		return array('Error_Msg' => '', 'link' => $zipfilepath);
	}
	
	/**
	 * Импорт данных плана
	 */
	function importPlanObsDisp($data) {
		$LpuInfo = $this->queryResult("
			select top 1 Lpu_f003mcod from v_Lpu (nolock) where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Lpu_f003mcod = $LpuInfo[0]['Lpu_f003mcod'];
		} else {
			return false;
		}
		
		$upload_path = './'.IMPORTPATH_ROOT.$data['Lpu_id'].'/';
		$allowed_types = explode('|','zip|xml');

		set_time_limit(0);

		if ( !isset($_FILES['File'])) {
			return array('Error_Msg' => 'Не выбран файл!');
		}

		if ( !is_uploaded_file($_FILES['File']['tmp_name']) ) {
			$error = (!isset($_FILES['File']['error'])) ? 4 : $_FILES['File']['error'];

			switch ( $error ) {
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}

			return array('Error_Msg' => $message);
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['File']['name']);
		$file_data['file_ext'] = end($x);
		if ( !in_array(strtolower($file_data['file_ext']), $allowed_types) ) {
			return array('Error_Msg' => 'Данный тип файла не разрешен.');
		}

		// Правильно ли указана директория для загрузки?
		if ( !@is_dir($upload_path) ) {
			mkdir( $upload_path );
		}

		if ( !@is_dir($upload_path) ) {
			return array('Error_Msg' => 'Путь для загрузки файлов некорректен.');
		}

		// Имеет ли директория для загрузки права на запись?
		if ( !is_writable($upload_path) ) {
			return array('Error_Msg' => 'Загрузка файла невозможна из-за прав пользователя.');
		}

		$fileList = array();

		if ( strtolower($file_data['file_ext']) == 'xml' ) {
			$fileList[] = $_FILES['File']['name'];

			if ( !move_uploaded_file($_FILES["File"]["tmp_name"], $upload_path.$_FILES['File']['name']) ) {
				return array('Error_Msg' => 'Не удаётся переместить файл.');
			}
		}
		else {
			$zip = new ZipArchive;

			if ( $zip->open($_FILES["File"]["tmp_name"]) === TRUE ) {
				for ( $i = 0; $i < $zip->numFiles; $i++ ) {
					$fileList[] = $zip->getNameIndex($i);
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}

			unlink($_FILES["File"]["tmp_name"]);
		}

		$xmlfile = '';

		libxml_use_internal_errors(true);

		foreach ( $fileList as $filename ) {
			$xmlfile = $filename;
		}

		if ( empty($xmlfile) ) {
			return array('Error_Msg' => 'Файл не является файлом для импорта ошибок плана контрольных посещений.');
		}
		
		if (!preg_match('/PDN'.$Lpu_f003mcod.'\_([0-9]{4})/ui', $xmlfile, $match)) {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Имя файла не соответствует установленному формату. Выберите другой файл.');
		}

		$xml_string = file_get_contents($upload_path . $xmlfile);

		$xml = new SimpleXMLElement($xml_string);
		
		$fname = $xml->FILENAME->__toString();

		// Поиск в БД записи сущности «Файл экспорта». по тегу FILENAME ( ==$fname)
		if (preg_match('/PDN'.$Lpu_f003mcod.'\_([0-9]{2})([0-9]{2})([0-9]{1})/ui', $fname, $match)) {

			$PlanExport_Year = $match[1];
			$PlanExport_DownloadQuarter = $match[2];
			$PlanExport_PackNum = $match[3];
			
			$sql = "
				SELECT top 1
					pddpe.PersonDopDispPlanExport_id
				FROM
					v_PersonDopDispPlanExport pddpe (nolock)
				WHERE
					PersonDopDispPlanExport_FileName = 'DN{$Lpu_f003mcod}_{$PlanExport_Year}{$PlanExport_DownloadQuarter}{$PlanExport_PackNum}'
				ORDER BY pddpe.PersonDopDispPlanExport_id DESC
			";
			
			/*$sql = "
				select top 1
					pddpe.PersonDopDispPlanExport_id
				from
					v_PersonDopDispPlanExport pddpe (nolock)
					inner join v_Lpu l (nolock) on l.Lpu_id = pddpe.Lpu_id 
				where
					l.Lpu_f003mcod = :Lpu_f003mcod
					and l.Lpu_id = :Lpu_id
					and pddpe.PersonDopDispPlanExport_Year = :PlanExport_Year
					and pddpe.PersonDopDispPlanExport_DownloadQuarter = :PlanExport_DownloadQuarter
					and pddpe.PersonDopDispPlanExport_PackNum = :PlanExport_PackNum
			";*/
			$params = array(
				'Lpu_f003mcod' => $Lpu_f003mcod,
				'Lpu_id' => $data['Lpu_id'],
				'PlanExport_Year' => $PlanExport_Year,
				'PlanExport_DownloadQuarter' => $PlanExport_DownloadQuarter,
				'PlanExport_PackNum' => $PlanExport_PackNum
			);
			//~ exit(getDebugSQL($sql, $params));
			$resp_pddpe = $this->queryResult($sql, $params);
			
			if (!empty($resp_pddpe[0]['PersonDopDispPlanExport_id'])) {
				$PersonDopDispPlanExport_id = $resp_pddpe[0]['PersonDopDispPlanExport_id'];
				// Если запись сущности найдена, то устанавливается дата импорта=текущая дата.
				$this->db->query("update PersonDopDispPlanExport with (rowlock) set PersonDopDispPlanExport_impDate = dbo.tzGetDate() where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id", array(
					'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id
				));

				foreach ( $xml->REP as $oneoshib ) {
					// Для записей из плана (поиск по порядковому номеру по записям сущности «Человек в плане» значения тега N_ZAP)
					$N_ZAP = $oneoshib->N_ZAP->__toString();
					$CODE_ERP = $oneoshib->CODE_ERP->__toString();
					$COMMENT = $oneoshib->COMMENT->__toString();
					if (preg_match('/([0-9]+)\s*\-\s*(.*)/ui', $COMMENT, $match)) {//определилась ошибка в теге comment	
						$error_code = $match[1];
						$error_text = $match[2];
						// Сохранить ошибки
						$this->saveExportErrorPlanObsDisp(array(
							'error_code' => $error_code,
							'error_text' => $error_text,
							'Region_id' => empty($data['Region_id']) ? null : $data['Region_id'],
							'N_ZAP' => $N_ZAP,
							'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
							'pmUser_id' => $data['pmUser_id']
						));
						// статус ошибка
						$this->setPlanRecordStatus(array(
							'error_code' => $error_code,
							'error_text' => $error_text,
							'Region_id' => empty($data['Region_id']) ? null : $data['Region_id'],
							'N_ZAP' => $N_ZAP,
							'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
							'PlanPersonListStatusType_id' => 4,
							'pmUser_id' => $data['pmUser_id']
						));
						
					} else {
						// статус принято ТФОМС
						$this->setPlanRecordStatus(array(
							'error_code' => '',
							'error_text' => '',
							'N_ZAP' => $N_ZAP,
							'PersonDopDispPlanExport_id' => $PersonDopDispPlanExport_id,
							'PlanPersonListStatusType_id' => 3,
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}
				return array('Error_Msg' => '');
			} else {
				// Иначе показать сообщение об ошибке «Файл экспорта не найден или удален»
				return array('Error_Msg' => 'Файл экспорта не найден или удален');
			}
		} else {
			return array('Error_Msg' => 'Ошибка при загрузке файла. Имя или структура файла не соответствует установленному формату. Выберите другой файл');
		}
		return array('Error_Msg' => '');
	}
	
	/**
	 * Сохранить ошибки экспорта, полученные из импорта
	 */
	function saveExportErrorPlanObsDisp($data) {
		$sql = "
			select PlanObsDispErrorType_id 
			from PlanObsDispErrorType with(nolock)
			where PlanObsDispErrorType_Code = :error_code
		";
		$error_id = $this->getFirstResultFromQuery($sql, $data);
		if(empty($error_id)) {
			$sql = "
				DECLARE	@PlanObsDispErrorType_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000)

				EXEC	p_PlanObsDispErrorType_ins
						@PlanObsDispErrorType_id = @PlanObsDispErrorType_id OUTPUT,
						@PlanObsDispErrorType_Code = :error_code,
						@PlanObsDispErrorType_Name = :error_text,
						@PlanObsDispErrorType_Descr = :error_text,
						@Region_id = :Region_id,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT

				SELECT	@PlanObsDispErrorType_id as PlanObsDispErrorType_id,
						@Error_Code as Error_Code,
						@Error_Message as Error_Message
			";
			$res = $this->queryResult($sql,$data);
			$error_id = $res[0]['PlanObsDispErrorType_id'];
		}
		
		$sql = "
			select distinct PODL2.PlanObsDispLink_id, PODEE.PlanObsDispErrorExport_id
			from v_PlanObsDispLink PODL2 with(nolock)
			inner join v_PersonDopDispPlanExportLink EL2 (nolock) on EL2.PlanObsDispLink_id=PODL2.PlanObsDispLink_id
			left join v_PlanObsDispErrorExport PODEE (nolock) on PODEE.PlanObsDispLink_id = PODL2.PlanObsDispLink_id
			where EL2.PersonDopDispPlanExportLink_Num = :N_ZAP and EL2.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
		";
		$record_ids = $this->queryResult($sql,$data);
		
		foreach($record_ids as $record) {
			$proc = "p_PlanObsDispErrorExport_ins";
			$params = $data;
			$params['PlanObsDispLink_id'] = $record['PlanObsDispLink_id'];
			$params['PlanObsDispErrorExport_id'] = $record['PlanObsDispErrorExport_id'];
			$params['error_id'] = $error_id;
			if(!empty($record['PlanObsDispErrorExport_id'])) {
				$proc = "p_PlanObsDispErrorExport_upd";
			}
			
			$sql = "
				DECLARE @PlanObsDispErrorExport_id bigint = :PlanObsDispErrorExport_id,
						@Error_Code int,
						@Error_Message varchar(4000)

				EXEC	{$proc}
						@PlanObsDispErrorExport_id = @PlanObsDispErrorExport_id OUTPUT,
						@PlanObsDispLink_id = :PlanObsDispLink_id,
						@PlanObsDispErrorType_id = :error_id,
						@PlanObsDispErrorExport_Descr = :error_text,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code OUTPUT,
						@Error_Message = @Error_Message OUTPUT

				SELECT	@PlanObsDispErrorExport_id as PlanObsDispErrorExport_id,
						@Error_Code as Error_Code,
						@Error_Message as Error_Message
			";
			//~ exit(getDebugSQL($sql, $params));
			$res = $this->queryResult($sql,$params);
			if(!empty($res['Error_Message'])) return array('Error_Msg'=>$res['Error_Message']);
		}
		return true;
	}
	
	/**
	 * Установить статус записи плана (в импорте ошибок)
	 */
	function setPlanRecordStatus($data) {
		$sql = "
			UPDATE PlanObsDispLinkStatus with(rowlock) SET PlanPersonListStatusType_id = :PlanPersonListStatusType_id
			WHERE PlanObsDispLink_id in (
				select PODL2.PlanObsDispLink_id 
				from v_PlanObsDispLink PODL2 with(nolock)
				inner join v_PersonDopDispPlanExportLink EL2 (nolock) on EL2.PlanObsDispLink_id=PODL2.PlanObsDispLink_id
				where EL2.PersonDopDispPlanExportLink_Num = :N_ZAP and EL2.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
			)
		";
		$res = $this->db->query($sql,$data);
		return $res===true;
	}
	
	/**
	 * Установить статус записи плана
	 */
	function setPlanObsDispLinkStatus($data) {
		$sql = "
			UPDATE PlanObsDispLinkStatus with(rowlock) 
			SET PlanPersonListStatusType_id = :PlanPersonListStatusType_id, 
				PlanObsDispLinkStatus_setDate = dbo.tzGetdate()
			WHERE PlanObsDispLink_id in (
				select PlanObsDispLink_id
				from v_PlanObsDispLink PODL with(nolock)
				where PODL.PersonDisp_id = :PersonDisp_id AND PODL.PlanObsDisp_id = :PlanObsDisp_id
			)
		";
		$res = $this->db->query($sql,$data);
		return $res;
	}
	
	/**
	 * Данные грида ошибок планов КП ДН
	 */
	function loadExportErrorPlanList($data) {
		$params = array('PersonDopDispPlanExport_id'=>$data['PersonDopDispPlanExport_id']);
		$sql = "
			SELECT
				-- select
				PEL.PersonDopDispPlanExportLink_Num as record_number,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_FIO,
				PET.PlanObsDispErrorType_Code as Error_Code,
				PET.PlanObsDispErrorType_Descr as Error_Descr
				-- end select
			FROM
				-- from
				v_PersonDopDispPlanExport PE
				inner join v_PersonDopDispPlanExportLink PEL with(nolock) on PEL.PersonDopDispPlanExport_id=PE.PersonDopDispPlanExport_id
				inner join v_PlanObsDispErrorExport PEE on PEE.PlanObsDispLink_id = PEL.PlanObsDispLink_id
				left join v_PlanObsDispErrorType PET on PET.PlanObsDispErrorType_id = PEE.PlanObsDispErrorType_id
				left join v_PlanObsDispLink PODL on PODL.PlanObsDispLink_id=PEL.PlanObsDispLink_id
				left join v_PersonDisp PD on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_PersonState PS on PS.Person_id = PD.Person_id
				-- end from
			WHERE
				-- where
				PE.PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
				-- end where
			ORDER BY
				-- order by
				PEL.PersonDopDispPlanExportLink_Num
				-- end order by
		";
		//~ exit(getDebugSQL($sql, $params));
		return $this->getPagingResponse($sql, $params, $data['start'], $data['limit'], true);
	}
}
