<?php
require_once('Abstract_model.php');
/**
 * Микроогранизм в пробе
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Qusijue
 * @version      Сентябрь 2019
 *
 * @property int BactMicroProbe_id
 * @property int EvnLabSample_id
 * @property int BactMicro_id
 * @property int Lpu_id
 * @property int UslugaTest_id
 * @property int BactMicroProbe_IsNotShown
 * @property int BactMicroProbe_deleted
 * @property int pmUser_insID
 * @property int pmUser_updID
 * @property int pmUser_delID
 * @property datetime BactMicroProbe_insDT
 * @property datetime BactMicroProbe_updDT
 * @property datetime BactMicroProbe_delDT
 */
class BactMicroProbe_model extends Abstract_model {
	protected $fields = [
		[
			'field' => 'BactMicroProbe_id',
			'label' => 'BactMicroProbe_id',
			'rules' => '',
			'type' => 'int'
		], [
			'field' => 'EvnLabSample_id',
			'label' => 'EvnLabSample_id',
			'rules' => '',
			'type' => 'id'
		], [
			'field' => 'BactMicro_id',
			'label' => 'BactMicro_id',
			'rules' => '',
			'type' => 'id'
		], [
			'field' => 'Lpu_id',
			'label' => 'Lpu_id',
			'rules' => '',
			'type' => 'id'
		], [
			'field' => 'UslugaTest_id',
			'label' => 'UslugaTest_id',
			'rules' => '',
			'type' => 'id'
		], [
			'field' => 'BactMicroProbe_IsNotShown',
			'label' => 'BactMicroProbe_IsNotShown',
			'rules' => '',
			'type' => 'int'
		], [
			'field' =>
			'BactMicroProbe_deleted',
			'label' => 'BactMicroProbe_deleted',
			'rules' => '',
			'type' => 'int'
		], [
			'field' => 'pmUser_insID',
			'label' => 'pmUser_insID',
			'rules' => '',
			'type' => 'int'
		], [
			'field' => 'pmUser_updID',
			'label' => 'pmUser_updID',
			'rules' => '',
			'type' => 'int'
		], [
			'field' => 'pmUser_delID',
			'label' => 'pmUser_delID',
			'rules' => '',
			'type' => 'int'
		], [
			'field' => 'BactMicroProbe_insDT',
			'label' => 'BactMicroProbe_insDT',
			'rules' => '',
			'type' => 'datetime'
		], [
			'field' => 'BactMicroProbe_updDT',
			'label' => 'BactMicroProbe_updDT',
			'rules' => '',
			'type' => 'datetime'
		], [
			'field' => 'BactMicroProbe_delDT',
			'label' => 'BactMicroProbe_delDT',
			'rules' => '',
			'type' => 'datetime'
		]
	];

	function __construct() {
		parent::__construct();
	}

	function getTableName() {
		return "BactMicroProbe";
	}

	protected function canDelete() {
		return $this->canBeDeleted([
			'BactMicroProbe_id' => $this->BactMicroProbe_id
		]);
	}

	public function validate() {
		$this->valid = true;
		return $this;
	}

	function getWorkList($data) {
		try {
			$filter = ""; $msfilter = "";
			if (!empty($data['EvnDirection_IsCito'])) {
				$filter .= " and (COALESCE(elr.EvnLabRequest_IsCito, 1) = :EvnDirection_IsCito)";
			}
			if (!empty($data['EvnLabSample_IsOutNorm'])) {
				$filter .= " and (COALESCE(els.EvnLabSample_IsOutNorm, 1) = :EvnLabSample_IsOutNorm)";
			}
			if (!empty($data['Person_ShortFio'])) {
				if (allowPersonEncrypHIV()) {
					$filter .= " and (COALESCE(ps.Person_SurName, '') + COALESCE(' '+ SUBSTRING(ps.Person_FirName,1,1) + '.','') + COALESCE(' '+ SUBSTRING(ps.Person_SecName,1,1) + '.','') LIKE :Person_ShortFio + '%' or peh.PersonEncrypHIV_Encryp LIKE :Person_ShortFio + '%')";
				} else {
					$filter .= " and COALESCE(ps.Person_SurName, '') + COALESCE(' '+ SUBSTRING(ps.Person_FirName,1,1) + '.','') + COALESCE(' '+ SUBSTRING(ps.Person_SecName,1,1) + '.','') LIKE :Person_ShortFio + '%'";
				}
			}
			if (!empty($data['EvnDirection_Num'])) {
				$filter .= " and ed.EvnDirection_Num LIKE '%' + :EvnDirection_Num + '%'";
			}
			if( !empty( $data['Lpu_sid']) ) {
				$filter .= " and ed.Lpu_sid = :Lpu_sid";
			}
			if( !empty( $data['LpuSection_id'] ) ) {
				$filter .= " and ed.LpuSection_id = :LpuSection_id";
				$params['LpuSection_id'] = $data['LpuSection_id'];
			}
			if( !empty( $data['MedStaffFact_id'] ) ) {
				$filter .= " and ed.MedStaffFact_id = :MedStaffFact_id";
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
			if( !empty( $data['EvnLabRequest_RegNum'] ) ) {
				$filter .= " and elr.EvnLabRequest_RegNum = :EvnLabRequest_RegNum";
				$params['EvnLabRequest_RegNum'] = $data['EvnLabRequest_RegNum'];
			}
			if (!empty($data['EvnLabSample_BarCode'])) {
				$filter .= " and els.EvnLabSample_BarCode LIKE :EvnLabSample_BarCode + '%'";
			}
			if (!empty($data['EvnLabSample_ShortNum'])) {
				$filter .= " and substring(els.EvnLabSample_Num,9,4) = :EvnLabSample_ShortNum";
			}
			if (!empty($data['LabSampleStatus_id'])) {
				$filter .= " and els.LabSampleStatus_id = :LabSampleStatus_id";
			}
			if (!empty($data['MedServiceType_SysNick']) && $data['MedServiceType_SysNick'] == 'reglab') {
				$msfilter = " or els.MedService_id IN (select MSL.MedService_lid from MedServiceLink MSL with(nolock) where msl.MedService_id = :MedService_id)";
			}
			if(!empty($data['UslugaComplex_id'])){
				$filter.=" and exists(
				select ut1.UslugaTest_id
				from v_UslugaTest (nolock) ut1
				inner join v_UslugaComplex (nolock) uc1 on uc1.UslugaComplex_id = ut1.UslugaComplex_id
				where ut1.EvnLabSample_id = els.EvnLabSample_id and uc1.UslugaComplex_id = :UslugaComplex_id
			)";
				$params['UslugaComplex_id'] = $data['UslugaComplex_id'];
			}
			$addWhenBeg = "when 1=0 then null";
			$addWhenEnd = "when 1=0 then null";
			if (empty($data['filterNewELSByDate'])) {
				$withoutDateStatus[] = 1;
			}
			if (empty($data['filterWorkELSByDate'])) {
				$withoutDateStatus[] = 2;
				$withoutDateStatus[] = 7;
			}
			if (empty($data['filterDoneELSByDate'])) {
				$withoutDateStatus[] = 3;
			}
			if (!empty($withoutDateStatus)) {
				$addWhenBeg = "when COALESCE(els.LabSampleStatus_id, 1) IN (" . implode(",", $withoutDateStatus) . ") then :begDate";
				$addWhenEnd = "when COALESCE(els.LabSampleStatus_id, 1) IN (" . implode(",", $withoutDateStatus) . ") then :endDate";
			}
			$datefilter = "
				and (:begDate <= case
					{$addWhenBeg}
					else COALESCE(
						convert(varchar(10), els.EvnLabSample_StudyDT, 120),
						convert(varchar(10), els.EvnLabSample_setDate, 120),
						convert(varchar(10), elr.EvnLabRequest_didDate, 120)
					) end)
				and (:endDate >= case
					{$addWhenEnd}
					else COALESCE(
						convert(varchar(10), els.EvnLabSample_StudyDT, 120),
						convert(varchar(10), els.EvnLabSample_setDate, 120),
						convert(varchar(10), elr.EvnLabRequest_didDate, 120)
					) end)
			";

			$allow_encryp = allowPersonEncrypHIV() ? '1' : '0';
			$query = "select
				els.EvnLabSample_id,
				substring(els.EvnLabSample_Num,9,4) as EvnLabSample_ShortNum,
				CASE WHEN COALESCE(elr.EvnLabRequest_IsCito, 1) = 2 THEN '!' else '' END AS EvnDirection_IsCito,
				els.LabSampleStatus_id,
				els.EvnLabSample_Num,
				els.EvnLabSample_BarCode,
				elr.EvnLabRequest_id,
				els.MedService_id,
				rm.RefMaterial_id,
				rm.RefMaterial_Name,
				ed.EvnDirection_id,
				ed.EvnDirection_Num,
				case
					when 1 = ed.PrehospDirect_id then COALESCE(ls.LpuSection_Name, Lpu.Lpu_Nick) -- 1 Отделение ЛПУ (Если не выбрали то ЛПУ)
					when 2 = ed.PrehospDirect_id then Lpu.Lpu_Nick -- 2 Другое ЛПУ --Lpu_sid - Направившее ЛПУ
					when ed.PrehospDirect_id in ( 3, 4, 5, 6 ) then Org.Org_nick -- 3 Другая организация -- 4 Военкомат -- 5 Скорая помощь -- 6 Администрация -- Org_sid - Направившая организация
					when 7 = ed.PrehospDirect_id then 'Пункт помощи на дому' --7Пункт помощи на дому
					else COALESCE(ls.LpuSection_Name, Lpu_Nick)
				end as PrehospDirect_Name,
				COALESCE(lss.LabSampleStatus_SysNick, 'new') as ProbaStatus,
				ps.Person_id,
				case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
					else COALESCE(ps.Person_SurName, '') + COALESCE(' '+ ps.Person_FirName,'') + COALESCE(' '+ ps.Person_SecName,'')
				end as Person_FIO,
				case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null then PEH.PersonEncrypHIV_Encryp
					else COALESCE(ps.Person_SurName, '') + COALESCE(' '+ SUBSTRING(ps.Person_FirName, 1, 1) + '.','') + COALESCE(' '+ SUBSTRING(ps.Person_SecName,1,1) + '.','')
				end as Person_ShortFio,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
				convert(varchar(5), els.EvnLabSample_setDT, 108) + ' ' + convert(varchar(10), els.EvnLabSample_setDT, 104) as EvnLabSample_setDT,
				els.EvnLabSample_StudyDT as EvnLabSample_StudyDT,
				elr.EvnLabRequest_BarCode as EvnLabRequest_BarCode,
				elr.UslugaComplex_id as UslugaComplexTarget_id,
				elr.UslugaComplex_id,
				(select top 1 uc1.UslugaComplex_Name
					from v_UslugaTest (nolock) ut1
					inner join v_UslugaComplex (nolock) uc1 on uc1.UslugaComplex_id = ut1.UslugaComplex_id
					where EvnLabSample_id = els.EvnLabSample_id
				) as UslugaComplex_Name,
				elr.EvnLabRequest_UslugaName,
				ls.LpuSection_Code,
				ls.LpuSection_Name,
				ms.MedService_id,
				ms.MedService_Nick,
				Lpu.Lpu_Nick,
				MP.Person_SurName as EDMedPersonalSurname,
				elr.EvnLabRequest_RegNum,
				COALESCE(els.EvnLabSample_IsOutNorm, 1) as EvnLabSample_IsOutNorm
			from v_EvnLabSample (nolock) els
			inner join v_EvnLabRequest (nolock) elr on elr.EvnLabRequest_id = els.EvnLabRequest_id
			--left join v_UslugaTest (nolock) ut on ut.EvnLabSample_id = els.EvnLabSample_id
			--inner join v_BactMicroProbe (nolock) bmp on bmp.UslugaTest_id = ut.UslugaTest_id
			left join v_MedService ms with(nolock) on ms.MedService_id = elr.MedService_id
			left join v_EvnDirection_all ed with(nolock) on ed.EvnDirection_id = elr.EvnDirection_id
			left join v_LpuSection ls with(nolock) on ls.LpuSection_id = ed.LpuSection_id
			left join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = ed.Lpu_sid
			left join v_Org Org with(nolock) on Org.Org_id = ed.Org_sid
			left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id = ed.MedPersonal_id
			left join v_PersonState ps with(nolock) on elr.Person_id = ps.Person_id
			left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ps.Person_id
			left join v_RefSample rs with(nolock) on rs.RefSample_id = els.RefSample_id
			left join v_RefMaterial rm with(nolock) on rm.RefMaterial_id = rs.RefMaterial_id
			left join v_LabSampleStatus lss with(nolock) on lss.LabSampleStatus_id = els.LabSampleStatus_id
			WHERE
				els.Lpu_id = :Lpu_id
				and els.EvnLabSample_setDT is not null
				and (els.MedService_id = :MedService_id {$msfilter})
				{$filter}
				{$datefilter}
			--where elr.EvnLabRequest_id = 1393369";

			return $this->queryResult($query, $data);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function getBactMicroProbeList($data) {
		$whereClause = "";
		if (!empty($data['EvnLabSample_id'])) {
			$whereClause .= ' and els.EvnLabSample_id = :EvnLabSample_id';
		}
		if (!empty($data['BactMicroProbe_id'])) {
			$whereClause .= ' and bmp.BactMicroProbe_id = :BactMicroProbe_id';
		}
		if (!empty($data['EvnUslugaPar_id'])) {
			$whereClause .= ' and ut.UslugaTest_pid = :EvnUslugaPar_id';
		}
		try {
			$query = "select
			ut.UslugaTest_id,
			ut.UslugaTest_pid,
			ut.UslugaTest_rid,
			convert(varchar(5), ut.UslugaTest_setDT, 108) + ' ' + convert(varchar(10), ut.UslugaTest_setDT, 104) as UslugaTest_setDT,
			ut.UslugaTest_disDT,
			ut.Lpu_id,
			ut.Server_id,
			ut.PersonEvn_id,
			ut.UslugaComplex_id,
       		ut.UslugaTest_pid,
			ut.EvnDirection_id,
			ut.Usluga_id,
			ut.PayType_id,
			ut.UslugaPlace_id,
			ut.UslugaTest_ResultValue,
			IIF(bm.BactMicro_id is null, '', ut.UslugaTest_ResultUnit) as UslugaTest_ResultUnit,
			ut.UslugaTest_ResultApproved,
			ut.UslugaTest_ResultAppDate,
			ut.UslugaTest_ResultCancelReason,
			ut.UslugaTest_Comment,
			ut.Unit_id,
			ut.UslugaTest_Kolvo,
			(select count(BactMicroProbeAntibiotic_id) from v_BactMicroProbeAntibiotic (nolock) where BactMicroProbe_id = bmp.BactMicroProbe_id) as AntibioticCount,
			ut.UslugaTest_Result,
			ut.EvnLabSample_id,
			ut.EvnLabRequest_id,
			ut.UslugaTest_CheckDT,
			bmp.BactMicroProbe_id,
			bm.BactMicro_id,
			els.EvnLabSample_id,
			els.EvnLabRequest_id,
			eup.EvnDirection_id,
			COALESCE(bm.BactMicro_Name, 'Микроорганизмы не обнаружены') as BactMicro_Name,
       		COALESCE(ucms_usluga_parent.UslugaComplex_Name, uc.UslugaComplex_Name) as ResearchName,

			bm.BactMicroWorld_id,
			bmw.BactMicroWorld_Name,
			bmp.BactMicroProbe_IsNotShown,
			case
				when ut.UslugaTest_ResultApproved = 2 then 'Одобрен'
				when ut.UslugaTest_ResultValue is not null and rtrim(ut.UslugaTest_ResultValue) <> '' then 'Выполнен'
				when (ut.UslugaTest_id is not null) then 'Обнаружен'
				else 'Не обнаружен'
			end as UslugaTest_Status
			from v_BactMicroProbe (nolock) bmp
			inner join v_UslugaTest (nolock) ut on ut.UslugaTest_id = bmp.UslugaTest_id
			inner join v_EvnLabSample (nolock) els on els.EvnLabSample_id = ut.EvnLabSample_id
		    inner join v_EvnUslugaPar eup (nolock) on eup.EvnUslugaPar_id = ut.UslugaTest_pid and eup.EvnDirection_id is not null

			left join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = eup.UslugaComplex_id
			left join v_BactMicro (nolock) bm on bm.BactMicro_id = bmp.BactMicro_id
			left join v_BactMicroWorld (nolock) bmw on bmw.BactMicroWorld_id = bm.BactMicroWorld_id
			left join v_UslugaComplexMedService ucms_usluga_parent (nolock) on uc.UslugaComplex_id = ucms_usluga_parent.UslugaComplex_id and ucms_usluga_parent.MedService_id = els.MedService_id and ucms_usluga_parent.UslugaComplexMedService_pid is null -- исследование
			where 1=1 {$whereClause}
			";
			return $this->queryResult($query, $data);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function addUslugaTest($params) {
		$setDT = 'null';
		if ($params['UslugaTest_ResultValue'] != null) {
			$setDT = '@dt';
		}
		$query = "DECLARE
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@UslugaTest_id bigint = :UslugaTest_id,
				@dt datetime = dbo.tzGetDate();
			EXEC dbo.p_UslugaTest_ins
				@UslugaTest_id = @UslugaTest_id output,
				@UslugaTest_pid = :UslugaTest_pid,
				@UslugaTest_rid = :UslugaTest_rid,
				@UslugaTest_setDT = {$setDT},
				@Lpu_id = :Lpu_id, 
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@PayType_id = :PayType_id,
				@UslugaTest_Kolvo = 1,
				@Unit_id = :Unit_id,
				@UslugaTest_ResultValue = :UslugaTest_ResultValue,
				@UslugaTest_ResultUnit = :UslugaTest_ResultUnit,
				@EvnLabSample_id = :EvnLabSample_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			SELECT
				@UslugaTest_id as UslugaTest_id,
				@Error_Code as Error_Code,
				@Error_Message as Error_Msg
		";
		try {
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}

	function getAntibioticList($data) {
		try {
			$query = "select
					bmpa.BactMicroProbeAntibiotic_id,
					bmpa.UslugaTest_id,
					ba.BactAntibiotic_Name,
					ba.BactAntibiotic_POTENCY,
					bg.BactGuideline_Name,
					bm.BactMethod_Name
				from v_BactMicroProbeAntibiotic (nolock) bmpa
				inner join v_BactAntibiotic (nolock) ba on ba.BactAntibiotic_id = bmpa.BactAntibiotic_id
				inner join v_BactGuideline (nolock) bg on bg.BactGuideline_id = ba.BactGuideline_id
				inner join v_BactMethod bm on bm.BactMethod_id = bmpa.BactMethod_id
				where bmpa.BactMicroProbe_id = :BactMicroProbe_id";

			return $this->queryResult($query, $data);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
	
	function getResearchList($params) {
		try {
			$query = "select
			eup.EvnUslugaPar_id,
			COALESCE(ucms_usluga_parent.UslugaComplex_Name, uc.UslugaComplex_Name) as Research_Name
			from v_EvnUslugaPar (nolock) eup
			inner join v_UslugaComplex (nolock) uc on uc.UslugaComplex_id = eup.UslugaComplex_id
		    inner join v_EvnLabRequest (nolock) elr on elr.EvnDirection_id = eup.EvnDirection_id
			left join v_UslugaComplexMedService (nolock) ucms_usluga_parent on uc.UslugaComplex_id = ucms_usluga_parent.UslugaComplex_id and ucms_usluga_parent.MedService_id = elr.MedService_id and ucms_usluga_parent.UslugaComplexMedService_pid is null
			where eup.EvnDirection_id = :EvnDirection_id and eup.EvnLabSample_id = :EvnLabSample_id";
			
			return $this->queryResult($query, $params);
		} catch (Exception $e) {
			log_message('error', $e->getMessage());
			throw $e;
		}
	}
}