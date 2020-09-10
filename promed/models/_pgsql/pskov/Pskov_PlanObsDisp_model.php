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
require(APPPATH . 'models/_pgsql/PlanObsDisp_model.php');

class Pskov_PlanObsDisp_model extends PlanObsDisp_model {
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
            LIMIT 1
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
			to_char(vizit.vizitdate, 'DD.MM.YYYY') as \"LastVizitDate\"
		";
		$from = "
			v_PersonDisp PD 
			left join v_PersonState PS  on PS.Person_id = PD.Person_id
			left join v_Diag D  on D.Diag_id = PD.Diag_id
			LEFT JOIN LATERAL (
				select PersonDispVizit_NextDate as vizitdate
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
						(COALESCE(date_part('YEAR', PD.PersonDisp_begDate), 0) < date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)) OR
						COALESCE(date_part('YEAR', PD.PersonDisp_endDate), date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))) = date_part('YEAR',CAST(:DispCheckPeriod_begDate as date))) and
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
						PS.Person_SurName, PS.Person_FirName, PS.Person_SecName
						-- end order by
				";
				break;
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
						(COALESCE(PD.PersonDisp_endDate, CAST(:DispCheckPeriod_endDate as date)) between :DispCheckPeriod_begDate and :DispCheckPeriod_endDate) and
						COALESCE(PD.PersonDisp_begDate, CAST(:DispCheckPeriod_endDate as date)) <= :DispCheckPeriod_endDate and
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
		$sql = "";
		switch($data['PeriodCap_id']) {
			case '1': //год
				/* берутся все карты ДН, открытые в МО пользователя на начало года, и контрольные посещения в отчетном году (карты без контрольных посещений не берутся).
				*/
				$sql = "
					CREATE TEMP TABLE IF NOT EXISTS Pskov_PlanObsDisp_model_tmpPlanLink
					(
					    PlanObsDisp_id     bigint,
					    PersonDisp_id      bigint,
					    PersonDispVizit_id bigint
					);
					TRUNCATE TABLE Pskov_PlanObsDisp_model_tmpPlanLink;
					
					INSERT INTO Pskov_PlanObsDisp_model_tmpPlanLink
					SELECT :PlanObsDisp_id
					     , PDV.PersonDisp_id
					     , PDV.PersonDispVizit_id
					FROM v_PersonDisp PD
					         LEFT JOIN LATERAL (
					    select distinct date_part('MONTH', PDV1.PersonDispVizit_NextDate) as month_num
					    from v_PersonDispVizit PDV1
					    where PDV1.PersonDisp_id = PD.PersonDisp_id
					      and date_part('MONTH', PDV1.PersonDispVizit_NextDate) >= date_part('MONTH', CAST(:DispCheckPeriod_begDate as date)
					        )
					    limit 4
					    ) PDV4 ON true
					         LEFT JOIN LATERAL (
					    select PDV2.PErsonDisp_id, PDV2.PersonDispVizit_id
					    from v_PersonDispVizit PDV2
					    where PDV2.PersonDisp_id = PD.PersonDisp_id
					      and date_part('MONTH', PDV2.PersonDispVizit_NextDate) = PDV4.month_num
					      and date_part('YEAR', PDV2.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date)
					        )
					    ) PDV ON true
					WHERE PD.Lpu_id = :Lpu_id
					  and COALESCE(CAST(PD.PersonDisp_begDate as varchar), '') < :DispCheckPeriod_endDate --карта открыта 
					  and exists(--наличие у карты посещений с отчетного месяца
					        select PDV3.PersonDispVizit_id
					        from v_PersonDispVizit PDV3
					        where PDV3.PersonDisp_id = PD.PersonDisp_id
					          and PDV3.PersonDispVizit_NextDate > :DispCheckPeriod_begDate
					          and PDV3.PersonDispVizit_NextDate < COALESCE(PD.PersonDisp_endDate, GETDATE())
					    );
					
					INSERT INTO PlanObsDispLink
					(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID, PlanObsDispLink_insDT,
					 PlanObsDispLink_updDT)
					SELECT tmp.PlanObsDisp_id,
					       tmp.PersonDisp_id,
					       tmp.PersonDispVizit_id,
					       :pmUser_id,
					       :pmUser_id,
					       dbo.tzGetdate(),
					       dbo.tzGetdate()
					FROM Pskov_PlanObsDisp_model_tmpPlanLink tmp;
					
					INSERT INTO PlanObsDispLinkStatus
					(PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID, pmUser_updID,
					 PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
					SELECT 1,
					       PODL.PlanObsDispLink_id,
					       dbo.tzGetdate(),
					       :pmUser_id,
					       :pmUser_id,
					       dbo.tzGetdate(),
					       dbo.tzGetdate()
					FROM PlanObsDispLink PODL
					         inner join Pskov_PlanObsDisp_model_tmpPlanLink tmp
					                    on tmp.PlanObsDisp_id = PODL.PlanObsDisp_id AND tmp.PersonDisp_id = PODL.PersonDisp_id AND
					                       tmp.PersonDispVizit_id = PODL.PersonDispVizit_id;
				";
				break;
			case '4': //месяц
				/* 
				
				*/
				$sql = "
					CREATE TEMP TABLE IF NOT EXISTS Pskov_PlanObsDisp_model_tmpPlanLink
					(
					    PlanObsDisp_id     bigint,
					    PersonDisp_id      bigint,
					    PersonDispVizit_id bigint
					);
					TRUNCATE TABLE Pskov_PlanObsDisp_model_tmpPlanLink;
					INSERT INTO Pskov_PlanObsDisp_model_tmpPlanLink
					SELECT :PlanObsDisp_id
					     , PDV.PersonDisp_id
					     , PDV.PersonDispVizit_id
					FROM v_PersonDisp PD
					         INNER JOIN LATERAL (
					    select distinct date_part('MONTH', PDV1.PersonDispVizit_NextDate) as month_num
					    from v_PersonDispVizit PDV1
					    where PDV1.PersonDisp_id = PD.PersonDisp_id
					      and date_part('MONTH', PDV1.PersonDispVizit_NextDate) >= date_part('MONTH', CAST(:DispCheckPeriod_begDate as date))
					    limit 4
					    ) PDV4 ON true
					         INNER JOIN LATERAL (
					    select PDV2.PErsonDisp_id, PDV2.PersonDispVizit_id
					    from v_PersonDispVizit PDV2
					    where PDV2.PersonDisp_id = PD.PersonDisp_id
					      and date_part('MONTH', PDV2.PersonDispVizit_NextDate) = PDV4.month_num
					      and date_part('YEAR', PDV2.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
					    ) PDV ON true
					WHERE PD.Lpu_id = :Lpu_id
					  and date_part('YEAR', COALESCE(PD.PersonDisp_endDate, GETDATE())) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
					  and COALESCE(CAST(PD.PersonDisp_begDate as varchar), '') < CAST(DATEADD('MONTH', 1, CAST(:DispCheckPeriod_begDate as date)) as varchar)
					  and exists(
					        select PDV3.PersonDispVizit_id
					        from v_PersonDispVizit PDV3
					        where PDV3.PersonDisp_id = PD.PersonDisp_id
					          and date_part('YEAR', PDV3.PersonDispVizit_NextDate) = date_part('YEAR', CAST(:DispCheckPeriod_begDate as date))
					          and date_part('MONTH', PDV3.PersonDispVizit_NextDate) >= date_part('MONTH', CAST(:DispCheckPeriod_begDate as date))
					    );
					
					INSERT INTO PlanObsDispLink
					(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID, PlanObsDispLink_insDT,
					 PlanObsDispLink_updDT)
					SELECT tmp.PlanObsDisp_id,
					       tmp.PersonDisp_id,
					       tmp.PersonDispVizit_id,
					       :pmUser_id,
					       :pmUser_id,
					       dbo.tzGetdate(),
					       dbo.tzGetdate()
					FROM Pskov_PlanObsDisp_model_tmpPlanLink tmp;
					
					INSERT INTO PlanObsDispLinkStatus
					(PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID, pmUser_updID,
					 PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
					SELECT 1,
					       PODL.PlanObsDispLink_id,
					       dbo.tzGetdate(),
					       :pmUser_id,
					       :pmUser_id,
					       dbo.tzGetdate(),
					       dbo.tzGetdate()
					FROM PlanObsDispLink PODL
					         inner join Pskov_PlanObsDisp_model_tmpPlanLink tmp
					                    on tmp.PlanObsDisp_id = PODL.PlanObsDisp_id AND tmp.PersonDisp_id = PODL.PersonDisp_id AND
					                       tmp.PersonDispVizit_id = PODL.PersonDispVizit_id;
					";
				break;
		}
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
				date_part('YEAR', DCP.DispCheckPeriod_begDate) as \"Export_Year\",
				case when DCP.PeriodCap_id=4
					then (date_part('MONTH', DCP.DispCheckPeriod_begDate)+2)/3
					else 1
				end as \"Export_Quart\",
				date_part('MONTH', DCP.DispCheckPeriod_begDate) as \"Export_Month\",
				DCP.PeriodCap_id as \"PeriodCap_id\"
			FROM
				v_PlanObsDisp POD 
				inner join v_DispCheckPeriod DCP  on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
			WHERE
				POD.PlanObsDisp_id = :PlanObsDisp_id
		", array('PlanObsDisp_id'=>$data['PlanObsDisp_id']));
		$PlanInfo = $PlanInfo[0];
		$data['PacketNumber'] = 0;
		
		$sql = "
			SELECT	PersonDopDispPlanExport_id as \"PersonDopDispPlanExport_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
			FROM	p_PersonDopDispPlanExport_ins(
					PersonDopDispPlanExport_id := null,
					PersonDopDispPlanExport_PackNum := :PacketNumber,
					pmUser_id := :pmUser_id,
					Lpu_id := :Lpu_id,
					PersonDopDispPlanExport_expDate := dbo.tzGetDate(),
					PersonDopDispPlanExport_Year := :Export_Year,
					PersonDopDispPlanExport_Month := :Export_Month,
					PersonDopDispPlanExport_DownloadQuarter := :Export_Quart);
		";
		$year = date('Y');
		$kvartal = intval((date('n')+2)/3);
		$res = $this->db->query($sql,array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Export_Year' => $PlanInfo['Export_Year'],
			'Export_Quart' => $PlanInfo['Export_Quart'],
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
		$data['Export_Month'] = $PlanInfo['Export_Month'];
		
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
		
		$LpuInfo = $this->queryResult("
			select Lpu_f003mcod  as \"Lpu_f003mcod\" from v_Lpu  where Lpu_id = :Lpu_id
		", array(
			'Lpu_id' => $data['Lpu_id']
		));

		if (!empty($LpuInfo[0]['Lpu_f003mcod'])) {
			$Ni = sprintf("%06d", $LpuInfo[0]['Lpu_f003mcod']);
		} else {
			$Ni = '000000';
		}
		$YY = substr($data['Export_Year'], 2,2);
		$MM = sprintf("%02d", $data['Export_Month']);
		$filename = $Ni.$YY.$MM;

		$dbffilename = $filename . '.dbf';
		$zipfilename = $filename . '.zip';
		
		$out_dir = "pod_xml_".time()."_".$data['Lpu_id'];
		if(!is_dir(EXPORTPATH_REGISTRY.$out_dir)) mkdir( EXPORTPATH_REGISTRY.$out_dir );

		$dbffilepath = EXPORTPATH_REGISTRY.$out_dir."/".$dbffilename;
		$zipfilepath = EXPORTPATH_REGISTRY.$out_dir."/".$zipfilename;
					
		// Блокируем файл
		$this->setPersonDopDispPlanExportIsUsed(array(
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'PersonDopDispPlanExport_IsUsed' => 1
		));
		
		// достаём данные
		$sql = "
			select distinct
				PD.PersonDisp_id as \"PersonDisp_id\,
				PS.Person_SurName as \"FAM\",
				PS.Person_FirName as \"IM\",
				PS.Person_SecName as \"OT\",
				to_char(PS.Person_BirthDay, 'YYYY-MM-DD') as \"DR\",
				PS.Person_EdNum as \"ENP\",
				po.Polis_Ser as \"SPOLIS\",
				po.Polis_Num as \"NPOLIS\",
				pt.PolisType_Code as \"TPOLIS\",
				D.Diag_Code as \"DS\",
				medPS.PErson_Snils as \"CODE_MD\",
				null as \"DATE_USL1\",
				null as \"DATE_USL2\",
				null as \"DATE_USL3\",
				null as \"DATE_USL4\",
				1 as \"MESTO_P\",
				to_char(PD.PersonDisp_endDate, 'DD.MM.YYYY') as \"SDN\",
				null as \"SMO\",
				null as \"DATEINF1\",
				null as \"SPOSOB1\",
				null as \"DATEINF_P1\",
				null as \"SPOSOB_P1_2\",
				null as \"DATEINF2\",
				null as \"SPOSOB2\"
			from
				v_PlanObsDispLink PODL 
				left join v_PersonDisp PD  on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_Diag D  on D.Diag_id = PD.Diag_id
				left join v_Person P  on P.Person_id = PD.Person_id
				left join v_PersonState PS  on PS.Person_id = P.Person_id
				left join v_Polis po  on po.Polis_id = PS.Polis_id
				left join v_PolisType pt  on po.PolisType_id = pt.PolisType_id
				LEFT JOIN LATERAL(
					SELECT PDH1.MedPersonal_id
					FROM v_PersonDispHist PDH1 
					WHERE PDH1.PersonDisp_id = PD.PersonDisp_id 
						and PDH1.PersonDispHist_endDate is null
                    LIMIT 1
				) PDH ON true
				LEFT JOIN LATERAL(
					SELECT MPC.Person_id
					FROM MedPersonalCache MPC 
					WHERE MPC.MedPersonal_id = PDH.MedPersonal_id
                    LIMIT 1
				) medpers ON true
				left join v_PersonState medPS  on medPS.Person_id = medpers.Person_id
			where
				PODL.PlanObsDisp_id = :PlanObsDisp_id
		";
		$params = array('PlanObsDisp_id'=>$data['PlanObsDisp_id']);
		//~ echo getDebugSQL($sql, $params);exit;
		
		$ZAPS = $this->queryResult($sql, $params);
		
		$this->beginTransaction();
		// для всех записей устанавливается статус «Отправлен в ТФОМС»
		$sql = "
			UPDATE PlanObsDispLinkStatus SET PlanPersonListStatusType_id = 2
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink  where PlanObsDisp_id = :PlanObsDisp_id);


			UPDATE PlanObsDispLink SET PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id
			WHERE PlanObsDispLink_id in (select PlanObsDispLink_id from PlanObsDispLink  where PlanObsDisp_id = :PlanObsDisp_id);

		";
		$params = array(
			'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
			'PersonDopDispPlanExport_id' => $data['PersonDopDispPlanExport_id'],
			'filename' => $filename,
			'pmUser_id' => $data['pmUser_id']
		);
		
		$this->db->query($sql,$params);
				
		$dbfStructure = array(
			array("FAM","C",150),
			array("IM", "C",150),
			array("OT", "C",150),
			array("DR", "C",10),//Дата рождения
			array("ENP", "C",16),//Единый номер полиса. Полис старого образца - буквы пробел-цифры; временное свидетельство - цифры; ЕНП – цифры
			array("DS", "C",5),//Код диагноза из карты ДН
			array("CODE_MD", "C",30),//Код врача, осуществляющего диспансерный прием (СНИЛС)
			array("MONTH_P1", "C",5),//Месяц и год запланированного ДП. В формате MM.YY
			array("MONTH_P2", "C",5),
			array("MONTH_P3", "C",5),
			array("MONTH_P4", "C",5),
			array("DATE_USL1", "D"),//null  Дата фактического проведения ДП
			array("DATE_USL2", "D"),
			array("DATE_USL3", "D"),
			array("DATE_USL4", "D"),
			array("MESTO_P", "N",1,0),//Планируемое место проведения диспансерного приема. 1-	в поликлинике; 2-	на дому. По умолчанию – «1». в поликлинике.
			array("SDN", "C",10),//Дата снятия с диспансерного учета. В формате DD.MM.YYYY
			array("SMO", "N",20,0),//null
			array("DATEINF1", "C",10),//null
			array("SPOSOB1", "N",1,0),//null
			array("DATEINF_P1", "C",10),//null
			array("SPOSOB_P1_", "N",1,0),//null
			array("DATEINF2", "C",10),//null
			array("SPOSOB2", "N",1,0),//null
		);
		
		$DBF = @dbase_create($dbffilepath, $dbfStructure);
		
		$N_ZAP=0;
		foreach ($ZAPS as &$ZAP) {
			$N_ZAP=$N_ZAP+1;
			
			$params = array(
				'PlanObsDisp_id'=>$data['PlanObsDisp_id'],
				'record_number'=>$N_ZAP,
				'Lpu_id'=>$data['Lpu_id'],
				'PersonDisp_id'=>$ZAP['PersonDisp_id'],
				'PersonDopDispPlanExport_id'=>$data['PersonDopDispPlanExport_id'],
				'pmUser_id' => $data['pmUser_id']
			);
			//получаем идешники сущностей плана по текущей записи
			$sql = "
				select distinct PODL.PlanObsDispLink_id as \"PlanObsDispLink_id\"
				from v_PlanObsDispLink PODL
					inner join v_PersonDisp PD on PD.PersonDisp_id=PODL.PersonDisp_id
					inner join v_PersonDispVizit PDV on PDV.PersonDisp_id=PD.PersonDisp_id and PODL.PersonDispVizit_id=PDV.PersonDispVizit_id
				where PD.PersonDisp_id=:PersonDisp_id and PD.Lpu_id=:Lpu_id and PODL.PlanObsDisp_id=:PlanObsDisp_id
			";
			
			$resp = $this->queryResult($sql, $params);
			foreach($resp as $item) {
				$params['PlanObsDispLink_id'] = $item['PlanObsDispLink_id'];
				//по всем сущностям проставляем порядковый номер записи и создаем связь на файл экспорта
				$sql = "
					UPDATE PlanObsDispLink SET PlanObsDispLink_Num=:record_number WHERE PlanObsDispLink_id=:PlanObsDispLink_id;
					
					SELECT	PersonDopDispPlanExportLink_id as \"PersonDopDispPlanExportLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
					FROM	p_PersonDopDispPlanExportLink_ins(
							PersonDopDispPlanExportLink_id := null,
							PersonDopDispPlanExport_id := :PersonDopDispPlanExport_id,
							PlanObsDispLink_id := :PlanObsDispLink_id,
							PersonDopDispPlanExportLink_Num := :record_number,
							pmUser_id := :pmUser_id);
				";
				$this->db->query($sql,$params);
			}
			
			//достаем посещения для дисп.карты
			$sql ="
				SELECT
					to_char(PDV.PersonDispVizit_NextDate, 'DD.MM.YY') as \"MONTH_P\"
				FROM
					v_PersonDispVizit PDV 
					inner join v_PlanObsDispLink PODL  on PODL.PersonDispVizit_id=PDV.PersonDispVizit_id AND PODL.PlanObsDisp_id=:PlanObsDisp_id
				WHERE
					PDV.PersonDisp_id = :PersonDisp_id
				ORDER BY PDV.PersonDispVizit_NextDate ASC
			";
			$vizits = $this->queryResult($sql, array('PersonDisp_id'=>$ZAP['PersonDisp_id'], 'PlanObsDisp_id'=>$data['PlanObsDisp_id']));
			
			$k = 0;
			foreach($vizits as $vizit) {
				$k+=1;
				if($k<=4) {
					$ZAP['MONTH_P'.$k] = '';
					if(strlen($vizit['MONTH_P'])>3)
						$ZAP['MONTH_P'.$k] =  substr($vizit['MONTH_P'], 3);
				}
			}
			
			switch($ZAP['TPOLIS']) {
				case '1': $ZAP['ENP'] = $ZAP['SPOLIS'].' '.$ZAP['NPOLIS'];
					break;
				case '3': $ZAP['ENP'] = $ZAP['NPOLIS'];
					break;
			}
			array_walk($ZAP, 'ConvertFromUTF8ToWin1251', true);
			dbase_add_record($DBF,
				array_values(array(
					"FAM"=>$ZAP['FAM'],
					"IM"=>$ZAP['IM'],
					"OT"=>$ZAP['OT'],
					"DR"=>$ZAP['DR'],
					"ENP"=>$ZAP['ENP'],
					"DS"=>$ZAP['DS'],
					"CODE_MD"=>$ZAP['CODE_MD'],
					"MONTH_P1"=>empty($ZAP['MONTH_P1']) ? '':$ZAP['MONTH_P1'],
					"MONTH_P2"=>empty($ZAP['MONTH_P2']) ? '':$ZAP['MONTH_P2'],
					"MONTH_P3"=>empty($ZAP['MONTH_P3']) ? '':$ZAP['MONTH_P3'],
					"MONTH_P4"=>empty($ZAP['MONTH_P4']) ? '':$ZAP['MONTH_P4'],
					"DATE_USL1"=>'', //$ZAP['DATE_USL1'],
					"DATE_USL2"=>'', //$ZAP['DATE_USL2'],
					"DATE_USL3"=>'', //$ZAP['DATE_USL3'],
					"DATE_USL4"=>'', //$ZAP['DATE_USL4'],
					"MESTO_P"=>$ZAP['MESTO_P'],
					"SDN"=>$ZAP['SDN'],
					"SMO"=>$ZAP['SMO'],
					"DATEINF1"=>'', //$ZAP['DATEINF1'],
					"SPOSOB1"=>'',//$ZAP['SPOSOB1'],
					"DATEINF_P1"=>'',//$ZAP['DATEINF_P1'],
					"SPOSOB_P1_"=>'',//$ZAP['SPOSOB_P1_2'],
					"DATEINF2"=>'',//$ZAP['DATEINF2'],
					"SPOSOB2"=>'',//$ZAP['SPOSOB2']
				))
			);
		}
		
		@dbase_close($DBF);

		$zip = new ZipArchive();
		$res = $zip->open($zipfilepath, ZIPARCHIVE::CREATE);

		if ( $res !== true ) {
			switch ( true ) {
				case ($res == ZipArchive::ER_EXISTS):
					$errorMessage = 'Файл уже существует';
					break;

				case ($res == ZipArchive::ER_INCONS):
					$errorMessage = 'Несовместимый ZIP-архив';
					break;

				case ($res == ZipArchive::ER_INVAL):
					$errorMessage = 'Недопустимый аргумент';
					break;

				case ($res == ZipArchive::ER_MEMORY):
					$errorMessage = 'Ошибка динамического выделения памяти';
					break;

				case ($res == ZipArchive::ER_NOENT):
					$errorMessage = 'Нет такого файла';
					break;

				case ($res == ZipArchive::ER_NOZIP):
					$errorMessage = 'Не является ZIP-архивом';
					break;

				case ($res == ZipArchive::ER_OPEN):
					$errorMessage = 'Невозможно открыть файл';
					break;

				case ($res == ZipArchive::ER_READ):
					$errorMessage = 'Ошибка чтения';
					break;

				case ($res == ZipArchive::ER_SEEK):
					$errorMessage = 'Ошибка поиска';
					break;

				default:
					$errorMessage = 'Ошибка создания архива';
					break;
			}

			throw new Exception($errorMessage);
		}

		$zip->AddFile($dbffilepath, $dbffilename);
		$zip->close();
		
		unlink($dbffilepath);
		
		if ( !file_exists($zipfilepath) ) {
			throw new Exception('Ошибка создания архива');
		}		

		// Пишем ссылку
		$query = "
			update PersonDopDispPlanExport set PersonDopDispPlanExport_DownloadLink = :PersonDopDispPlanExport_DownloadLink,
				PersonDopDispPlanExport_FileName = :Export_FileName
			where PersonDopDispPlanExport_id = :PersonDopDispPlanExport_id;
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
}
