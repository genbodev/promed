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
			SELECT top 1 POD.Lpu_id, POD.DispCheckPeriod_id, DCP.PeriodCap_id, 
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
			convert(varchar(10), vizit.vizitdate, 104) as LastVizitDate
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
					DECLARE @periodYear int = YEAR(:DispCheckPeriod_begDate),
						@periodMonth int = MONTH(:DispCheckPeriod_begDate),
						@curDT date = dbo.tzGetdate();
					DECLARE @tmpPlanLink TABLE (PlanObsDisp_id bigint, PersonDisp_id bigint, PersonDispVizit_id bigint);
					
					INSERT INTO @tmpPlanLink
					SELECT
						:PlanObsDisp_id, PDV.PersonDisp_id, PDV.PersonDispVizit_id
					FROM v_PersonDisp PD
					outer apply(
						select distinct top 4 MONTH(PDV1.PersonDispVizit_NextDate) as month_num
						from v_PersonDispVizit PDV1 
						where PDV1.PersonDisp_id=PD.PersonDisp_id and MONTH(PDV1.PersonDispVizit_NextDate)>=@periodMonth
					) PDV4
					outer apply(
						select PDV2.PErsonDisp_id, PDV2.PersonDispVizit_id
						from v_PersonDispVizit PDV2
						where PDV2.PersonDisp_id=PD.PersonDisp_id and MONTH(PDV2.PersonDispVizit_NextDate)=PDV4.month_num and YEAR(PDV2.PersonDispVizit_NextDate)=@periodYear
					) PDV
					WHERE PD.Lpu_id = :Lpu_id
						and isnull(PD.PersonDisp_begDate,'') < :DispCheckPeriod_endDate --карта открыта 
						and exists (--наличие у карты посещений с отчетного месяца
							select PDV3.PersonDispVizit_id from v_PersonDispVizit PDV3
							where PDV3.PersonDisp_id=PD.PersonDisp_id and PDV3.PersonDispVizit_NextDate > :DispCheckPeriod_begDate
								and PDV3.PersonDispVizit_NextDate < isnull(PD.PersonDisp_endDate,GETDATE())
						)
					
					INSERT INTO PlanObsDispLink
						(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID, PlanObsDispLink_insDT, PlanObsDispLink_updDT)
					SELECT tmp.PlanObsDisp_id,tmp.PersonDisp_id,tmp.PersonDispVizit_id,:pmUser_id,:pmUser_id,@curDT,@curDT
					FROM @tmpPlanLink tmp
					
					INSERT INTO PlanObsDispLinkStatus
						(PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID, pmUser_updID, PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
					SELECT 1, PODL.PlanObsDispLink_id, @curDT, :pmUser_id, :pmUser_id, @curDT, @curDT
					FROM PlanObsDispLink PODL (nolock)
					inner join @tmpPlanLink tmp on tmp.PlanObsDisp_id=PODL.PlanObsDisp_id AND tmp.PersonDisp_id=PODL.PersonDisp_id AND tmp.PersonDispVizit_id=PODL.PersonDispVizit_id
				";
				break;
			case '4': //месяц
				/* 
				
				*/
				$sql = "
					DECLARE	@curDT date = dbo.tzGetdate(),
						@periodYear int = YEAR(:DispCheckPeriod_begDate),
						@periodMonth int = MONTH(:DispCheckPeriod_begDate);
					DECLARE @tmpPlanLink TABLE (PlanObsDisp_id bigint, PersonDisp_id bigint, PersonDispVizit_id bigint);
					
					INSERT INTO @tmpPlanLink
					SELECT
						:PlanObsDisp_id, PDV.PersonDisp_id, PDV.PersonDispVizit_id
					FROM v_PersonDisp PD
					cross apply(
						select distinct top 4 MONTH(PDV1.PersonDispVizit_NextDate) as month_num
						from v_PersonDispVizit PDV1 
						where PDV1.PersonDisp_id=PD.PersonDisp_id and MONTH(PDV1.PersonDispVizit_NextDate)>=@periodMonth
					) PDV4
					cross apply(
						select PDV2.PErsonDisp_id, PDV2.PersonDispVizit_id
						from v_PersonDispVizit PDV2
						where PDV2.PersonDisp_id=PD.PersonDisp_id and MONTH(PDV2.PersonDispVizit_NextDate)=PDV4.month_num and YEAR(PDV2.PersonDispVizit_NextDate)=@periodYear
					) PDV
					WHERE PD.Lpu_id = :Lpu_id
						and YEAR(isnull(PD.PersonDisp_endDate,GETDATE()))=@periodYear
						and isnull(PD.PersonDisp_begDate,'') < DATEADD(MONTH, 1, :DispCheckPeriod_begDate)
						and exists (
							select PDV3.PersonDispVizit_id from v_PersonDispVizit PDV3
							where PDV3.PersonDisp_id=PD.PersonDisp_id and YEAR(PDV3.PersonDispVizit_NextDate)=@periodYear
								and MONTH(PDV3.PersonDispVizit_NextDate)>=@periodMonth
						)
					
					INSERT INTO PlanObsDispLink
						(PlanObsDisp_id, PersonDisp_id, PersonDispVizit_id, pmUser_insID, pmUser_updID, PlanObsDispLink_insDT, PlanObsDispLink_updDT)
					SELECT tmp.PlanObsDisp_id,tmp.PersonDisp_id,tmp.PersonDispVizit_id,:pmUser_id,:pmUser_id,@curDT,@curDT
					FROM @tmpPlanLink tmp
					
					INSERT INTO PlanObsDispLinkStatus
						(PlanPersonListStatusType_id, PlanObsDispLink_id, PlanObsDispLinkStatus_setDate, pmUser_insID, pmUser_updID, PlanObsDispLinkStatus_insDT, PlanObsDispLinkStatus_updDT)
					SELECT 1, PODL.PlanObsDispLink_id, @curDT, :pmUser_id, :pmUser_id, @curDT, @curDT
					FROM PlanObsDispLink PODL (nolock)
					inner join @tmpPlanLink tmp on tmp.PlanObsDisp_id=PODL.PlanObsDisp_id AND tmp.PersonDisp_id=PODL.PersonDisp_id AND tmp.PersonDispVizit_id=PODL.PersonDispVizit_id
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
				YEAR(DCP.DispCheckPeriod_begDate) as Export_Year,
				case when DCP.PeriodCap_id=4
					then (MONTH(DCP.DispCheckPeriod_begDate)+2)/3
					else 1
				end as Export_Quart,
				MONTH(DCP.DispCheckPeriod_begDate) as Export_Month,
				DCP.PeriodCap_id
			FROM
				v_PlanObsDisp POD with(nolock)
				inner join v_DispCheckPeriod DCP with(nolock) on DCP.DispCheckPeriod_id = POD.DispCheckPeriod_id
			WHERE
				POD.PlanObsDisp_id = :PlanObsDisp_id
		", array('PlanObsDisp_id'=>$data['PlanObsDisp_id']));
		$PlanInfo = $PlanInfo[0];
		$data['PacketNumber'] = 0;
		
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
			select top 1 Lpu_f003mcod from v_Lpu with(nolock) where Lpu_id = :Lpu_id
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
				PD.PersonDisp_id,
				PS.Person_SurName as FAM,
				PS.Person_FirName as IM,
				PS.Person_SecName as OT,
				convert(varchar(10), PS.Person_BirthDay, 120) as DR,
				PS.Person_EdNum as ENP,
				po.Polis_Ser as SPOLIS,
				po.Polis_Num as NPOLIS,
				pt.PolisType_Code as TPOLIS,
				D.Diag_Code as DS,
				medPS.PErson_Snils as CODE_MD,
				null as DATE_USL1,
				null as DATE_USL2,
				null as DATE_USL3,
				null as DATE_USL4,
				1 as MESTO_P,
				convert(varchar(10), PD.PersonDisp_endDate, 104) as SDN,
				null as SMO,
				null as DATEINF1,
				null as SPOSOB1,
				null as DATEINF_P1,
				null as SPOSOB_P1_2,
				null as DATEINF2,
				null as SPOSOB2
			from
				v_PlanObsDispLink PODL with (nolock)
				left join v_PersonDisp PD with (nolock) on PD.PersonDisp_id = PODL.PersonDisp_id
				left join v_Diag D with(nolock) on D.Diag_id = PD.Diag_id
				left join v_Person P with (nolock) on P.Person_id = PD.Person_id
				left join v_PersonState PS with(nolock) on PS.Person_id = P.Person_id
				left join v_Polis po with(nolock) on po.Polis_id = PS.Polis_id
				left join v_PolisType pt with(nolock) on po.PolisType_id = pt.PolisType_id
				
				outer apply(
					SELECT TOP 1 PDH1.MedPersonal_id
					FROM v_PersonDispHist PDH1 with(nolock)
					WHERE PDH1.PersonDisp_id = PD.PersonDisp_id 
						and PDH1.PersonDispHist_endDate is null
				) PDH
				outer apply(
					SELECT TOP 1 MPC.Person_id
					FROM MedPersonalCache MPC with(nolock)
					WHERE MPC.MedPersonal_id = PDH.MedPersonal_id
				) medpers
				left join v_PersonState medPS with(nolock) on medPS.Person_id = medpers.Person_id
			where
				PODL.PlanObsDisp_id = :PlanObsDisp_id
		";
		$params = array('PlanObsDisp_id'=>$data['PlanObsDisp_id']);
		//~ echo getDebugSQL($sql, $params);exit;
		
		$ZAPS = $this->queryResult($sql, $params);
		
		$this->beginTransaction();
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
				select distinct PODL.PlanObsDispLink_id
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
			
			//достаем посещения для дисп.карты
			$sql ="
				SELECT
					convert(varchar(10), PDV.PersonDispVizit_NextDate, 4) as MONTH_P
				FROM
					v_PersonDispVizit PDV with(nolock)
					inner join v_PlanObsDispLink PODL with(nolock) on PODL.PersonDispVizit_id=PDV.PersonDispVizit_id AND PODL.PlanObsDisp_id=:PlanObsDisp_id
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
}
