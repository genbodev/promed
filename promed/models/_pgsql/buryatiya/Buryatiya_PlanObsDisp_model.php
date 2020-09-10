<?php
defined('BASEPATH') or die('No direct script access allowed');

/**
 * PlanObsDisp_model - модель для работы с планами ДН по контрольным посещениям (Бурятия)
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
require(APPPATH . 'models/_pgsql/PlanObsDisp_model.php');

class Buryatiya_PlanObsDisp_model extends PlanObsDisp_model {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Ошибки данных. Для грида на форме план КП ДН		
	 */
	function loadPlanErrorData($data) {
		$filter = "";
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id']
		);
		$sql ="
			SELECT POD.Lpu_id as \"Lpu_id\", POD.DispCheckPeriod_id as \"DispCheckPeriod_id\", DCP.PeriodCap_id as \"PeriodCap_id\", 
				to_char(DCP.DispCheckPeriod_begDate, 'YYYY-MM-DD') as \"DispCheckPeriod_begDate\", 
				to_char(DCP.DispCheckPeriod_endDate, 'YYYY-MM-DD') as \"DispCheckPeriod_endDate\"
			FROM v_PlanObsDisp POD 
			left join v_DispCheckPeriod DCP  on DCP.DispCheckPeriod_id=POD.DispCheckPeriod_id
			WHERE POD.PlanObsDisp_id=:PlanObsDisp_id
		";
		$par = $this->queryResult($sql, $params);
		$params['Lpu_id'] = $par[0]['Lpu_id'];
		$params['PeriodCap_id'] = $par[0]['PeriodCap_id'];
		$params['DispCheckPeriod_id'] = $par[0]['DispCheckPeriod_id'];
		$params['DispCheckPeriod_begDate'] = $par[0]['DispCheckPeriod_begDate'];
		$params['DispCheckPeriod_endDate'] = $par[0]['DispCheckPeriod_endDate'];
		
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
			$filter .= " and lower(PS.Person_SurName{$podfilter}) LIKE lower(:Person_FIO)";

			$params['Person_FIO'] = $data['Person_FIO'].'%';
		}
		$select = "
			PD.Person_id as \"Person_id\", 
			PD.PersonDisp_id as \"PersonDisp_id\",
			RTRIM(PS.Person_SurName) || ' ' || COALESCE(PS.Person_FirName, '') || ' ' || COALESCE(PS.Person_SecName, '') as \"Person_FIO\",
			to_char(PS.Person_Birthday, 'DD.MM.YYYY') as \"Person_Birthday\",
			PD.PersonDisp_NumCard as \"CardNumber\",
			to_char(PD.PersonDisp_begDate, 'DD.MM.YYYY') as \"begDate\",
			to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') as \"endDate\",
			D.Diag_Code||' '||D.Diag_Name as \"Diagnoz\",
			to_char(vizit.PersonDispVizit_NextDate, 'DD.MM.YYYY') as \"VizitDate\"
		";
		$from = "
			v_PersonDisp PD 
			left join v_PersonState PS  on PS.Person_id = PD.Person_id
			left join v_Diag D  on D.Diag_id = PD.Diag_id
			--left join v_PersonDispVizit vizit  on vizit.PersonDisp_id = PD.PersonDisp_id
			LEFT JOIN LATERAL (
				select PersonDispVizit_NextDate
				from v_PersonDispVizit PDV 
				where PDV.PersonDisp_id = PD.PersonDisp_id
				order by PDV.PersonDispVizit_NextDate DESC
				limit 1
			) vizit ON true
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
						(COALESCE(date_part('YEAR',PD.PersonDisp_begDate), 0) < date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)) OR

						COALESCE(date_part('YEAR',PD.PersonDisp_endDate), date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))) and
						not exists(
							SELECT PDV.PersonDisp_id
							FROM v_PersonDispVizit PDV 
							WHERE PDV.PersonDisp_id=PD.PersonDisp_id AND date_part('YEAR', PDV.PersonDispVizit_NextDate)=date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						) and
						not exists(select * from PlanObsDispLink PODL  where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id)
						{$filter}
						-- end where
					ORDER BY
						-- order by
						PS.Person_SurName, PS.Person_FirName, PS.Person_SecName, PD.PersonDisp_id, vizit.PersonDispVizit_NextDate
						-- end order by
				";
				break;
			case '3': //квартал
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
						(COALESCE(PD.PersonDisp_endDate, :DispCheckPeriod_endDate) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate) and
						COALESCE(PD.PersonDisp_begDate, :DispCheckPeriod_endDate) <= :DispCheckPeriod_endDate and
						not exists(
							SELECT PDV.PersonDisp_id
							FROM v_PersonDispVizit PDV 
							WHERE PDV.PersonDisp_id=PD.PersonDisp_id AND date_part('YEAR', PDV.PersonDispVizit_NextDate)=date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						) and
						not exists(select PODL.PlanObsDispLink_id from PlanObsDispLink PODL  where PODL.PlanObsDisp_id=:PlanObsDisp_id and PODL.PersonDisp_id=PD.PersonDisp_id)
						{$filter}
						-- end where
					ORDER BY
						-- order by
						PS.Person_SurName, PS.Person_FirName, PS.Person_SecName, PD.PersonDisp_id, vizit.PersonDispVizit_NextDate
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
	 * Сформировать план
	 */
	function makePlanObsDispLink($data) {
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'], 
			'Lpu_id'=>$data['Lpu_id'],
			'DispCheckPeriod_begDate'=>$data['DispCheckPeriod_begDate'],
			'DispCheckPeriod_endDate'=>$data['DispCheckPeriod_endDate'],
			'pmUser_id'=>$data['pmUser_id']
		);
		
		switch($data['PeriodCap_id']) {
			case '1': //год
				/* берутся все карты ДН, открытые в МО пользователя на начало года, и контрольные посещения в отчетном году (карты без контрольных посещений не берутся).
				*/
				$sql = "
					WITH cte AS (
						SELECT CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (COALESCE(date_part('YEAR', PD.PersonDisp_begDate), 0) <
							   date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)) OR
							   COALESCE(date_part('YEAR', PD.PersonDisp_endDate),
										date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))) =
							   date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)))
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						  and not exists(
								select PODL.PlanObsDisp_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  AND PODL.PersonDisp_id = PD.PersonDisp_id
								  and PersonDispVizit_id = PDV.PersonDispVizit_id
							)
					)
					INSERT
					INTO PlanObsDispLink(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID,
										 PlanObsDispLink_insDT, PlanObsDispLink_updDT)
					SELECT tmp.PlanObsDisp_id,
						   tmp.PersonDisp_id,
						   tmp.PersonDispVizit_id,
						   :pmUser_id,
						   :pmUser_id,
						   dbo.tzGetdate(),
						   dbo.tzGetdate()
					FROM cte tmp;
			
					WITH cte AS (
						SELECT CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (COALESCE(date_part('YEAR', PD.PersonDisp_begDate), 0) <
							   date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)) OR
							   COALESCE(date_part('YEAR', PD.PersonDisp_endDate),
										date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))) =
							   date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)))
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						  and not exists(
								select PODL.PlanObsDisp_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  AND PODL.PersonDisp_id = PD.PersonDisp_id
								  and PersonDispVizit_id = PDV.PersonDispVizit_id
							)
					)
					INSERT
					INTO PlanObsDispLinkStatus(PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID,
											   pmUser_updID, PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
					SELECT 1,
						   PODL.PlanObsDispLink_id,
						   dbo.tzGetdate(),
						   :pmUser_id,
						   :pmUser_id,
						   dbo.tzGetdate(),
						   dbo.tzGetdate()
					FROM PlanObsDispLink PODL
							 inner join cte tmp on tmp.PlanObsDisp_id = PODL.PlanObsDisp_id AND tmp.PersonDisp_id = PODL.PersonDisp_id AND
												   tmp.PersonDispVizit_id = PODL.PersonDispVizit_id;
						";
				break;
			case '3': //квартал
				/* берутся все карты ДН, у которых:
				
				--	Дата закрытия входит в квартал, за  который формируется план; и контрольные посещения отчетного, а при их отсутствии – предыдущего года, даты которых меньше даты закрытия карты.
				
				--	открытые на дату окончания квартала;  и контрольные посещения в отчетном году.
				
				*/
				$sql = "
					WITH cte AS (
						SELECT CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM --открытые на дату окончания квартала;  и контрольные посещения в отчетном году.
							 v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (COALESCE(PD.PersonDisp_endDate, :DispCheckPeriod_endDate) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate)
						  and COALESCE(PD.PersonDisp_begDate, :DispCheckPeriod_endDate) <= :DispCheckPeriod_endDate
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						  and not exists(
								select PODL.PlanObsDisp_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  and PODL.PersonDisp_id = PD.PersonDisp_id
								  and PODL.PersonDispVizit_id = PDV.PersonDispVizit_id
							)
						UNION ALL
						SELECT --Дата закрытия входит в квартал, за  который формируется план; и контрольные посещения отчетного, даты которых меньше даты закрытия карты.
							   CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (PD.PersonDisp_endDate between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate)
						  and PDV.PersonDispVizit_NextDate is not null
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						  and PDV.PersonDispVizit_NextDate < PD.PersonDisp_endDate
						  and not exists(
								select PODL.PlanObsDisp_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  and PODL.PersonDisp_id = PD.PersonDisp_id
								  and PODL.PersonDispVizit_id = PDV.PersonDispVizit_id
							)
						UNION ALL
						SELECT --Дата закрытия входит в квартал, за  который формируется план; и контрольные посещения предыдущего года, при отсутствии отчетного, даты которых меньше даты закрытия карты.
							   CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (PD.PersonDisp_endDate between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate)
						  and PDV.PersonDispVizit_NextDate is not null
						  and PDV.PersonDispVizit_NextDate < PD.PersonDisp_endDate
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) =
							  date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)) - 1
						  and not exists(
								select PDV2.PersonDispVizit_id
								from v_PersonDispVizit PDV2
								where PDV2.PersonDisp_id = PD.PersonDisp_id
								  and date_part('YEAR', PDV2.PersonDispVizit_NextDate) =
									  date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
							)
						  and not exists(
								select PODL.PlanObsDispLink_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  and PODL.PersonDisp_id = PD.PersonDisp_id
								  and PODL.PersonDispVizit_id = PDV.PersonDispVizit_id
							)
					)
					INSERT
					INTO PlanObsDispLink(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID,
										 PlanObsDispLink_insDT, PlanObsDispLink_updDT)
					SELECT tmp.PlanObsDisp_id,
						   tmp.PersonDisp_id,
						   tmp.PersonDispVizit_id,
						   :pmUser_id,
						   :pmUser_id,
						   dbo.tzGetdate(),
						   dbo.tzGetdate()
					FROM cte tmp;
					WITH cte AS (
						SELECT CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM --открытые на дату окончания квартала;  и контрольные посещения в отчетном году.
							 v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (COALESCE(PD.PersonDisp_endDate, :DispCheckPeriod_endDate) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate)
						  and COALESCE(PD.PersonDisp_begDate, :DispCheckPeriod_endDate) <= :DispCheckPeriod_endDate
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						  and not exists(
								select PODL.PlanObsDisp_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  and PODL.PersonDisp_id = PD.PersonDisp_id
								  and PODL.PersonDispVizit_id = PDV.PersonDispVizit_id
							)
						UNION ALL
						SELECT --Дата закрытия входит в квартал, за  который формируется план; и контрольные посещения отчетного, даты которых меньше даты закрытия карты.
							   CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (PD.PersonDisp_endDate between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate)
						  and PDV.PersonDispVizit_NextDate is not null
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
						  and PDV.PersonDispVizit_NextDate < PD.PersonDisp_endDate
						  and not exists(
								select PODL.PlanObsDisp_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  and PODL.PersonDisp_id = PD.PersonDisp_id
								  and PODL.PersonDispVizit_id = PDV.PersonDispVizit_id
							)
						UNION ALL
						SELECT --Дата закрытия входит в квартал, за  который формируется план; и контрольные посещения предыдущего года, при отсутствии отчетного, даты которых меньше даты закрытия карты.
							   CAST(:PlanObsDisp_id as bigint) as PlanObsDisp_id,
							   PD.PersonDisp_id,
							   PDV.PersonDispVizit_id
						FROM v_PersonDisp PD
								 inner join v_PersonDispVizit PDV on PDV.PersonDisp_id = PD.PersonDisp_id
						WHERE PD.Lpu_id = :Lpu_id
						  and (PD.PersonDisp_endDate between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate)
						  and PDV.PersonDispVizit_NextDate is not null
						  and PDV.PersonDispVizit_NextDate < PD.PersonDisp_endDate
						  and date_part('YEAR', PDV.PersonDispVizit_NextDate) =
							  date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)) - 1
						  and not exists(
								select PDV2.PersonDispVizit_id
								from v_PersonDispVizit PDV2
								where PDV2.PersonDisp_id = PD.PersonDisp_id
								  and date_part('YEAR', PDV2.PersonDispVizit_NextDate) =
									  date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
							)
						  and not exists(
								select PODL.PlanObsDispLink_id
								from PlanObsDispLink PODL
								where PODL.PlanObsDisp_id = :PlanObsDisp_id
								  and PODL.PersonDisp_id = PD.PersonDisp_id
								  and PODL.PersonDispVizit_id = PDV.PersonDispVizit_id
							)
					)
					INSERT
					INTO PlanObsDispLinkStatus(PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID,
											   pmUser_updID, PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
					SELECT 1,
						   PODL.PlanObsDispLink_id,
						   dbo.tzGetdate(),
						   :pmUser_id,
						   :pmUser_id,
						   dbo.tzGetdate(),
						   dbo.tzGetdate()
					FROM PlanObsDispLink PODL
							 inner join cte tmp on tmp.PlanObsDisp_id = PODL.PlanObsDisp_id AND tmp.PersonDisp_id = PODL.PersonDisp_id AND
												   tmp.PersonDispVizit_id = PODL.PersonDispVizit_id;";
				break;
		}
		$result = $this->db->query($sql, $params);
		return $result;
	}

	/**
	 * Экспорт плана
	 */
	function exportPlanObsDisp($data) {
		set_time_limit(0);
		
		$PlanInfo = $this->queryResult("
			SELECT
				case when DCP.PeriodCap_id=3
					then (date_part('MONTH', DCP.DispCheckPeriod_begDate)+2)/3
				end as \"period_quart\",
				date_part('YEAR',DCP.DispCheckPeriod_begDate) as \"period_year\"
			FROM
				v_PlanObsDisp POD 
				inner join v_DispCheckPeriod DCP  on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
			WHERE
				POD.PlanObsDisp_id = :PlanObsDisp_id
		", array('PlanObsDisp_id'=>$data['PlanObsDisp_id']));
		$PlanInfo = $PlanInfo[0];
		// занимаем порядковый номер пакета по МО на текущей дате, или генерим ошибку о превышении лимита по номеру
		$sql = "
			CREATE OR REPLACE FUNCTION pg_temp.exp_Query(out _packnum int, out _PersonDopDispPlanExport_id bigint,
														 out _Error_Code int, out _Error_Message text)
				LANGUAGE 'plpgsql'
			AS
			$$
			DECLARE
				v_packnum int;
			BEGIN
			
				SELECT MAX(PE.PersonDopDispPlanExport_PackNum)
				INTO
					v_packnum
				FROM v_PersonDopDispPlanExport PE
				WHERE PE.Lpu_id = :Lpu_id
				  AND date_part('YEAR', PE.PersonDopDispPlanExport_insDT) = date_part('YEAR', dbo.tzGetDate())
				  and date_part('MONTH', PE.PersonDopDispPlanExport_insDT) = date_part('MONTH', dbo.tzGetDate())
				  and date_part('DAY', PE.PersonDopDispPlanExport_insDT) = date_part('DAY', dbo.tzGetDate());
				IF v_packnum is null THEN
					v_packnum := 0;
				END IF;
				IF v_packnum < 99 THEN
					v_packnum := v_packnum + 1;
					SELECT PersonDopDispPlanExport_id,
						   Error_Code,
						   Error_Message
					INTO
						_PersonDopDispPlanExport_id,
						_Error_Code,
						_Error_Message
					FROM p_PersonDopDispPlanExport_ins(
								 PersonDopDispPlanExport_id := null,
								 PersonDopDispPlanExport_PackNum := v_packnum,
								 pmUser_id := :pmUser_id,
								 Lpu_id := :Lpu_id,
								 PersonDopDispPlanExport_expDate := dbo.tzGetDate(),
								 PersonDopDispPlanExport_Year := :period_year,
								 PersonDopDispPlanExport_DownloadQuarter := :period_quart
							 );
				ELSE
					_Error_Message := 'limit99';
				END IF;
				_packnum := v_packnum;
			END;
			$$;
			
			SELECT _PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
				_Error_Code as \"Error_Code\",
				_Error_Message as \"Error_Message\",
				_packnum as \"PacketNumber\"
			FROM pg_temp.exp_Query();
		";
		$this->beginTransaction();
		$getPackNum = $this->queryResult($sql, array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'period_year' => $PlanInfo['period_year'],
			'period_quart' => $PlanInfo['period_quart']
		));
		$getPackNum = $getPackNum[0];
		
		if (!empty($getPackNum['Error_Message'])) {
			$this->rollbackTransaction();
			if($getPackNum['Error_Message']=='limit99')
				return array('Error_Msg' => "Файл не может быть сформирован, так как количество пакетов в день не может превышать 99, и 99 пакетов уже сформированы");
			else return array('Error_Msg' => $getPackNum['Error_Message']);
		}
		
		if(empty($getPackNum['PacketNumber'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения данных экспорта');
		} else $data['PacketNumber'] = $getPackNum['PacketNumber'];
		
		if(empty($getPackNum['PersonDopDispPlanExport_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка сохранения данных экспорта');
		} else $data['PersonDopDispPlanExport_id'] = $getPackNum['PersonDopDispPlanExport_id'];
		
		$this->commitTransaction();
		
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
		
		$LpuInfo = $this->queryResult("
			select Lpu_f003mcod  as \"Lpu_f003mcod\" from v_Lpu  where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = $LpuInfo[0]['Lpu_f003mcod'];
		} else {
			$Ni = '';
		}
		
		$N = str_pad($data['PacketNumber'], 2, '0', STR_PAD_LEFT);

		$filename = 'DN_M'.$Ni.'_'.date('Y_m_d').'_'.$N;
		
		$xmlfilename = $filename . '.xml';
		
		$out_dir = "pod_xml_".time()."_".$data['Lpu_id'];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$xmlfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$xmlfilename;
				
		$this->beginTransaction();
					
		// Блокируем файл
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => 1
		));
		
		// данные сгруппированные по пациентам
		$sql = "
			select distinct PS.Person_id                               as \"ZL_ID\",
							PS.Person_EdNum                            as \"ENP\",
							PS.Person_SurName                          as \"FAM\",
							PS.Person_FirName                          as \"IM\",
							PS.Person_Phone                            as \"TEL\",
							case
								when PS.Person_SecName is not null then PS.Person_SecName
								else 'НЕТ'
								end                                    as \"OT\",
							to_char(PS.Person_BirthDay, 'YYYY-MM-DD')  as \"DR\",
							case
								when PS.Person_Snils IS NOT NULL and length(PS.Person_Snils) = 11 then (
														SUBSTRING(PS.Person_Snils, 1, 3) || '-' ||
														SUBSTRING(PS.Person_Snils, 4, 3) || '-' ||
														SUBSTRING(PS.Person_Snils, 7, 3) || ' ' ||
														SUBSTRING(PS.Person_Snils, 10, 2))
								else ''
								end                                    as \"SNILS\",
							pt.PolisType_CodeF008                      as \"TPOLIS\",
							p.Polis_Ser                                as \"SPOLIS\",
							p.Polis_Num                                as \"NPOLIS\",
							A.Address_id                               as \"_ADDRES_P_ID\",
							--coalesce(kls.KLStreet_AOID, KLA.KLArea_AOID) as \"_FIAS_AOID\",
							KLA.KLArea_AOID                            as \"_FIAS_AOID\",
							''                                         as \"_FIAS_HOUSEID\",
							A.Address_Address                          as \"_FULL_NAME\",
							A.Address_House                            as \"house_number\",
							A.Address_Corpus                           as \"corpus_number\",
							A.Address_Flat                             as \"_KV\",
							PS2.PersonPhone_id                         as \"phone_id\",
							rtrim(COALESCE(PS2.PersonPhone_Phone, '')) as \"_PHONE\",
							pi.PersonInfo_Email                        as \"_EMAIL\"
			from v_PlanObsDispLink POD
					 inner join v_PersonDisp PD on PD.PersonDisp_id = POD.PersonDisp_id
					 inner join v_PersonState PS on PS.Person_id = PD.Person_id
					 left join PersonState PS2 on PS2.Person_id = PS.Person_id
					 left join v_Polis p on p.Polis_id = PS.Polis_id
					 left join v_PolisType pt on pt.PolisType_id = p.PolisType_id
					 left join v_Address A on A.Address_id = PS.PAddress_id
					 left join v_KLArea KLA on KLA.KLArea_id = coalesce(a.KLStreet_id, a.KLCity_id, a.KLTown_id)
				--left join v_KLStreet kls  on kls.KLStreet_id = a.KLStreet_id
					 LEFT JOIN LATERAL
				(
				select PersonInfo_id,
					   PersonInfo_InternetPhone,
					   PersonInfo_Email
				from v_PersonInfo
				where Person_id = PS.Person_id
				order by PersonInfo_id desc
				limit 1
				) pi ON true
			where POD.PlanObsDisp_id = :PlanObsDisp_id
		";
		$params = array('PlanObsDisp_id'=>$data['PlanObsDisp_id']);
		$persons = $this->queryResult($sql, $params);
		
		
		// для всех записей устанавливается статус «Отправлен в ТФОМС»
		$sql = "
			UPDATE PlanObsDispLinkStatus SET PlanPersonListStatusType_id = 2, PlanObsDispLinkStatus_setDate = dbo.tzGetDate()
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink  where PlanObsDisp_id = :PlanObsDisp_id);
			UPDATE PlanObsDispLink SET PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink  where PlanObsDisp_id = :PlanObsDisp_id);
			UPDATE PersonDopDispPlanExport SET PersonDopDispPlanExport_FileName = :filename WHERE PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id;
		";
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'filename' => $filename,
			'pmUser_id' => $data['pmUser_id']
		);
		$this->db->query($sql,$params);
		
		$this->load->library('parser');
		
		$recordNumber = 0;
		foreach ($persons as $k => &$pers) {
			if($pers['TPOLIS']==3) { //код единого образца (ENP). id=4
				if(empty($pers['NPOLIS'])) $pers['NPOLIS']=$pers['ENP'];
				$pers['SPOLIS']=null;
			}
			//оформляем контактные данные пациента:
			if(!empty($pers['house_number'])) $pers['house_number'] = 'д.'.$pers['house_number'].', ';
			if(!empty($pers['corpus_number'])) $pers['corpus_number'] = 'к.'.$pers['corpus_number'].', ';
			$pers['_DOM'] = $pers['house_number'].$pers['corpus_number'];
			
			$pers['CONTACT'] = array();
			if(!empty($pers['_PHONE'])) {
				$pers['_PHONE'] = str_replace(array('-','+',' ','(',')'), '', $pers['_PHONE']);//убираем корректные, но лишние знаки
				if (mb_strlen($pers['_PHONE']) >= 10 && preg_match('/^[0-9]+$/ui', $pers['_PHONE'])) {
					$pers['_PHONE'] = mb_substr($pers['_PHONE'], mb_strlen($pers['_PHONE']) - 10);//берем номер (по тз 10 цифр), ведущие 7 или 8 отбрасываются
					//добавляем блок с телефоном
					$pers['CONTACT'][] = array(
						'CONTACT_ID'=>$pers['phone_id'],
						'TYPE'=>1,
						'PHONE'=>$pers['_PHONE'],
						'EMAIL'=>null,
						'ADDRES_P'=>array()
					);
				}
			}
			
			if(!empty($pers['_ADDRES_P_ID']) and !empty($pers['_FULL_NAME']) and !empty($pers['_DOM'])) {
				//добавляем блок с адресом
				$addr = array(
						'ADDRES_P_ID'=>$pers['_ADDRES_P_ID'],
						'FIAS_AOID'=>$pers['_FIAS_AOID'],
						'FIAS_HOUSEID'=>$pers['_FIAS_HOUSEID'],
						'FULL_NAME'=>$pers['_FULL_NAME'],
						'DOM'=>$pers['_DOM'],
						'KV'=>$pers['_KV']);
				array_walk($addr, 'ConvertFromUTF8ToWin1251', true);
				$persnew = $pers['CONTACT'][] = array(
					'CONTACT_ID'=>$pers['_ADDRES_P_ID'],
					'TYPE' => 4,
					'ADDRES_P'=>array(),
					'ADDRES_P'=>array(0=>$addr
					),
					'EMAIL'=>null,
					'PHONE'=>null
				);
				
			}

			//email не выгружаем

			//проверяем наличие всех обязательных полей
			if(
				empty($pers['FAM']) or 
				empty($pers['IM']) or 
				empty($pers['DR']) or
				empty($pers['ENP']) or
				empty($pers['TPOLIS']) or
				empty($pers['NPOLIS'])
			) {
				unset($persons[$k]);
				continue;
			}
			$recordNumber += 1;
			//достаем дисп.карты пациента
			$sql ="
				SELECT DISTINCT
					PD.PersonDisp_id as \"DN_ID\",
					D.Diag_Code as \"DS\",
					to_char(PD.PersonDisp_DiagDate, 'YYYY-MM-DD') \"DS1_PR_DATE\",
					PD.DiagDetectType_id as \"DS1_PR_SOURCE\",
					to_char(PD.PersonDisp_endDate, 'YYYY-MM-DD') as \"DATE_OUT\"
				FROM
					v_PlanObsDispLink POD 
					left join v_PersonDisp PD  on PD.PersonDisp_id = POD.PersonDisp_id
					left join v_Diag D  on D.Diag_id=PD.Diag_id
				WHERE
					POD.PlanObsDisp_id = :PlanObsDisp_id AND PD.Person_id = :Person_id
			";
			$DN = $this->queryResult($sql, array('PlanObsDisp_id'=>$data['PlanObsDisp_id'], 'Person_id'=>$pers['ZL_ID']));
			
			foreach ($DN as $DNi => &$card) {
				if($card['DS1_PR_SOURCE']==2) $card['DS1_PR_SOURCE'] = 1; //профосмотр
				else if($card['DS1_PR_SOURCE']==1) $card['DS1_PR_SOURCE'] = 2; //выявлено при диспансеризации определенных групп взрослого населения
				else $card['DS1_PR_SOURCE'] = 3; // другое
				//достаем посещения для дисп.карты
				$sql ="
					SELECT DISTINCT
						PDV.PersonDispVizit_id as \"APPOINTMENT_ID\",
						PODL.PlanObsDispLink_id as \"PlanObsDispLink_id\",
						to_char(PDV.PersonDispVizit_NextDate, 'YYYY-MM-DD') as \"DATE_PLAN\",
						medPS.Person_Snils as \"CODE_MD\",
						(CASE WHEN PDV.PersonDispVizit_IsHomeDN = 2 THEN 1 ELSE 0 END) as \"PLACE\"
					FROM
						v_PersonDispVizit PDV 
						inner join v_PlanObsDispLink PODL  on PODL.PersonDispVizit_id=PDV.PersonDispVizit_id AND PODL.PlanObsDisp_id=:PlanObsDisp_id
						LEFT JOIN LATERAL(
							SELECT PDH1.MedPersonal_id
							FROM v_PersonDispHist PDH1 
							WHERE PDH1.PersonDisp_id = :PersonDisp_id AND
								(PDH1.PersonDispHist_endDate is null OR
								PDV.PersonDispVizit_NextDate between PDH1.PersonDispHist_begDate and PDH1.PersonDispHist_endDate
								)
                            LIMIT 1
						) PDH ON true
						LEFT JOIN LATERAL(
							SELECT MPC.Person_id
							FROM MedPersonalCache MPC 
							WHERE MPC.MedPersonal_id = PDH.MedPersonal_id
                            LIMIT 1
						) medpers ON true
						left join v_PersonState medPS  on medPS.Person_id = medpers.Person_id
					WHERE
						PDV.PersonDisp_id = :PersonDisp_id
				";
				$vizits = $this->queryResult($sql, array('PersonDisp_id'=>$card['DN_ID'], 'PlanObsDisp_id'=>$data['PlanObsDisp_id']));
				
				foreach($vizits as $vi => $vizit) {
					if(	empty($vizit['CODE_MD']) or empty($vizit['DATE_PLAN'])) {
						unset($vizits[$vi]);
						continue;
					}
					//для каждого посещения/записи плана добавляем статус и вписываем порядковый номер записи
					$sql = "
					UPDATE PlanObsDispLink SET PlanObsDispLink_Num = :recordNumber WHERE PlanObsDispLink_id = :PlanObsDispLink_id;
					SELECT	PersonDopDispPlanExportLink_id as \"PersonDopDispPlanExportLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Message\"            
					FROM	p_PersonDopDispPlanExportLink_ins(
							PersonDopDispPlanExportLink_id := null,
							PersonDopDispPlanExport_id := :PersonDopDispPlanExport_id,
							PlanObsDispLink_id := :PlanObsDispLink_id,
							PersonDopDispPlanExportLink_Num := :recordNumber,
							pmUser_id := :pmUser_id);
					";
					$params = array(
						'recordNumber'=>$recordNumber,
						'PlanObsDispLink_id'=> $vizit['PlanObsDispLink_id'],
						'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->db->query($sql, $params);
				}
				
				$card['APPOINTMENT'] = $vizits;

				if(!empty($vizits)) $card['APPOINTMENT'] = $vizits;
				else unset($DN[$DNi]);
			}
			
			$pers['DN'] = $DN;
			
			if(empty($pers['DN'])) unset($persons[$k]);

			array_walk($pers, 'ConvertFromUTF8ToWin1251', true);
		}
		
		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/export_planobsdisp_buryatiya', array(
				'ZGLV' => array(array(
					'FILENAME' => $filename,
					'MCOD' => $Ni
				)),
				'ZL' => $persons
			), true, false, array(), 
			false);//пустые теги не выводить

		$xml = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+|[\n]/", "\r\n", $xml); // удаляем пустые строки и приводим перевод строки к одному виду
		file_put_contents($xmlfilepath, $xml);
		
		// Пишем ссылку
		$query = "update PersonDopDispPlanExport set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id";
		$this->db->query($query, array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_DownloadLink' => $xmlfilepath
		));
		
		// Снимаем блокировку
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => null
		));
		
		$this->commitTransaction();

		return array('Error_Msg' => '', 'link' => $xmlfilepath);
	}
}
