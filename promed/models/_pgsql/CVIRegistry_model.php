<?php
defined("BASEPATH") or die ("No direct script access allowed");
/**
 * CVIRegistry - Реестр КВИ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2020 Swan Ltd.
 * @author
 * @version
 */
class CVIRegistry_model extends SwPgModel {
	static function defAttributes() {
		return [
			self::ID_KEY => [
				'properties' => [
					self::PROPERTY_NEED_TABLE_NAME
				],
				'alias' => 'CVIRegistry_шв',
				'label' => 'Идентификатор',
				'save' => 'trim',
				'type' => 'int'
			],
			'personregister_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NOT_NULL
				],
				'alias' => 'PersonRegister_id',
				'label' => 'Идентификатор записи в таблице Регистр',
				'save' => 'trim|required',
				'type' => 'int'
			],
			'setdt' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NEED_TABLE_NAME
				],
				'alias' => 'CVIRegistry_SetDT',
				'label' => 'Дата начала',
				'save' => 'trim',
				'type' => 'int'
			],
			'isconfirmed' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NEED_TABLE_NAME
				],
				'alias' => 'CVIRegistry_IsConfirmed',
				'label' => 'Признак подтверждения',
				'save' => 'trim',
				'type' => 'int'
			],
			'disDT' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM,
					self::PROPERTY_NEED_TABLE_NAME
				],
				'alias' => 'CVIRegistry_disDT',
				'label' => 'Дата окончания',
				'save' => 'trim',
				'type' => 'int'
			],
			'lpu_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'save' => 'trim',
				'type' => 'int'
			],
			'evnps_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'save' => 'trim',
				'type' => 'int'
			],
			'evnpl_id' => [
				'properties' => [
					self::PROPERTY_IS_SP_PARAM
				],
				'alias' => 'EvnPL_id',
				'label' => 'Идентификатор ТАП',
				'save' => 'trim',
				'type' => 'int'
			]
		];
	}

	public function __construct() {
		parent::__construct();
	}
	public function tableName() {
		return 'CVIRegistry';
	}
	protected function _validate() {
		return true;
	}

	public function loadMainDiagCombo() {
		$query = "select
				Diag_id as \"Diag_id\",
				Diag_Code as \"Diag_Code\",
				Diag_Name as \"Diag_Name\"
			from v_Diag
			where Diag_Code in ('Z20.8', 'Z03.8', 'U07.1', 'U07.2', 'Z11.5', 'Z22.8')
		";
		try {
			return $this->queryResult($query);
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function processOpenRecords() {
		$query = "call dbo.p_CVIRegistry_Refresh()";
		try {
			return $this->db->query($query);
		} catch (Exception $e) {
			throw $e;
		}
	}

	public function loadDiagList($params) {
		if (!empty($params['EvnPS_id'])) {
			$query = "select mdiag.Diag_Code as \"Diag_Code\"
				from dbo.v_EvnPS ps
				left join dbo.v_Diag mdiag on mdiag.Diag_id = ps.Diag_id
				where ps.EvnPS_id = :EvnPS_id
				union all
				select sdiag.Diag_Code as \"Diag_Code\"
				from dbo.v_EvnDiagPS edps
				left join dbo.v_Diag sdiag on sdiag.Diag_id = edps.Diag_id
				where edps.EvnDiagPS_rid = :EvnPS_id and edps.DiagSetClass_id <> 1
			";
		} elseif (!empty($params['EvnPL_id'])) {
			$query = "select Diag.Diag_Code as \"Diag_Code\"
				from dbo.v_EvnPL pl
				inner join dbo.v_EvnVizitPL vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id and vpl.EvnVizitPL_Index = vpl.EvnVizitPL_Count - 1
				inner join dbo.v_Diag Diag on Diag.Diag_id = pl.Diag_id
				where (pl.EvnPL_setDate >= '2020-03-01') and pl.EvnPL_id = :EvnPL_id
				union all
				select Diag.Diag_Code as \"Diag_Code\"
				from dbo.v_EvnPL pl
				inner join dbo.v_EvnVizitPL vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id
				inner join dbo.v_EvnDiagPLSop DiagSop on DiagSop.EvnDiagPLSop_pid = vpl.EvnVizitPL_id and DiagSop.DiagSetClass_id = 3
				inner join dbo.v_Diag Diag on Diag.Diag_id = DiagSop.Diag_id
				where (pl.EvnPL_setDate >= '2020-03-01') and pl.EvnPL_id = :EvnPL_id
			";
		} else {
			return [];
		}
		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function loadContactedGrid($params) {
		$query = "select
				ps.Person_id as \"Person_id\",
				reg.CVIRegistry_id as \"CVIRegistry_id\",
				pq.PersonQuarantine_id as \"PersonQuarantine_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_BirthDay as \"Person_BirthDay\",
				dbo.Age(ps.Person_BirthDay, getdate()) as \"Person_Age\",
				ps.Person_Phone as \"Person_Phone\",
				ua.Address_Address as \"RegAddres\",
				pa.Address_Address as \"LiveAddress\",
				pq.PersonQuarantine_begDT as \"PersonQuarantine_begDT\",
				pq.PersonQuarantine_approveDT as \"PersonQuarantine_approveDT\",
				pc.PersonContactCVI_contactDate as \"PersonContactCVI_contactDate\",
				lpu.Lpu_Nick as \"Lpu_Nick\"
			from dbo.v_PersonContactCVI pc
			inner join dbo.v_PersonState ps on ps.Person_id = pc.Person_id
			inner join dbo.v_CVIRegistry reg on reg.CVIRegistry_id = (select CVIRegistry_id
				from dbo.v_CVIRegistry
				inner join dbo.v_PersonRegister on v_PersonRegister.PersonRegister_id = v_CVIRegistry.PersonRegister_id
				where v_PersonRegister.Person_id = pc.Person_id and v_CVIRegistry.CVIRegistry_isConfirmed = 2
				order by v_CVIRegistry.CVIRegistry_setDT desc
			    limit 1
			)
			left join dbo.PersonQuarantine pq on pq.PersonQuarantine_id = (select v_PersonQuarantine.PersonQuarantine_id
				from dbo.v_PersonQuarantine
				where v_PersonQuarantine.Person_id = ps.Person_id and (v_PersonQuarantine.PersonQuarantine_endDT is null)
				order by v_PersonQuarantine.PersonQuarantine_begDT desc
			    limit 1
			)
			left join dbo.v_EvnPS eps on eps.EvnPS_id = (select v_EvnPS.EvnPS_id
				from dbo.v_EvnPS
				where v_EvnPS.Person_id = ps.Person_id and (v_EvnPS.EvnPS_disDate is null)
				order by v_EvnPS.EvnPS_setDate desc
			    limit 1
			)
			left join dbo.v_Lpu lpu on lpu.Lpu_id = eps.Lpu_id
			left join dbo.v_Address ua on ua.Address_id = ps.UAddress_id
			left join dbo.v_Address pa on pa.Address_id = ps.PAddress_id
			left join dbo.v_PersonCardState pcs on pcs.Person_id = pc.Person_id and pcs.LpuAttachType_id = 1
			where reg.CVIRegistry_id is not null and pcs.PersonCardState_id is null
			order by pc.PersonContactCVI_contactDate desc
		";

		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function loadResearch($params) {
		$query = "select
				ut.UslugaTest_id as \"UslugaTest_id\",
				ut_uc.UslugaComplex_Name as \"TestName\",
				eup.EvnUslugaPar_id as \"EvnUslugaPar_id\",
				eup_uc.UslugaComplex_id as \"UslugaComplex_id\",
				eup_uc.UslugaComplex_Code as \"UslugaComplex_Code\",
				eup_uc.UslugaComplex_Name as \"UslugaComplex_Name\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				ut.UslugaTest_ResultUnit as \"UslugaTest_ResultUnit\"
			from dbo.v_UslugaTest ut
			inner join dbo.v_UslugaComplex ut_uc on ut_uc.UslugaComplex_id = ut.UslugaComplex_id
			inner join dbo.v_EvnUslugaPar eup on eup.EvnUslugaPar_id = ut.UslugaTest_pid
			inner join dbo.v_UslugaComplex eup_uc on eup_uc.UslugaComplex_id = eup.UslugaComplex_id
			where eup.EvnDirection_id = :EvnDirection_id
		";
		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function loadControlCard($params) {
		$where = "(1=1)";
		$filter = "(1=1)";
		switch ($params['mode']) {
			case 'suspicion':
				$where .= " and pq.PersonQuarantineOpenReason_Code in ('1', '2')";
				break;
			case 'notconfirmed':
				$where .= " and (
					(
						select count(CVIRegistry_id) from dbo.v_CVIRegistry
						inner join dbo.v_PersonRegister on v_PersonRegister.PersonRegister_id = v_CVIRegistry.PersonRegister_id
						where CVIRegistry_isConfirmed = 2 and v_PersonRegister.Person_id = ps.Person_id
					) = 0
					or
					(datediff('day', ro.RepositoryObserv_arrivalDate, getdate()) > 21 or datediff('day', ro.RepositoryObesrv_contactDate, getdate()) > 21)
				)";
				break;
			case 'confirmed':
			case 'recovered':
			case 'died':
				$where .= " and (1<>1)";
				break;
		}
		if (!empty($params['Status_id'])) {
			if ($params['Status_id'] == 1) $where .= " and pq.PersonQuarantine_endDT is null";
			else if ($params['Status_id'] == 2) $where .= " and pq.PersonQuarantine_endDT is not null";
		}
		if (!empty($params['TreatmentPlace'])) {
			if ($params['TreatmentPlace'] == 1) $filter .= " and (1<>1)";
		}
		if (!empty($params['RegistryRecordType_id'])) $where .= ' and (1<>1)';
		if (!empty($params['PersonRegister_setDateRange'][0]) && !empty($params['PersonRegister_setDateRange'][1])) {
			$where .= " and (
				(ro.RepositoryObserv_arrivalDate <= :PersonRegister_setDateEnd and (ro.RepositoryObserv_arrivalDate is null or ro.RepositoryObserv_arrivalDate >= :PersonRegister_setDateBeg))
				or
				(ro.RepositoryObesrv_contactDate <= :PersonRegister_setDateEnd and (ro.RepositoryObesrv_contactDate is null or ro.RepositoryObesrv_contactDate >= :PersonRegister_setDateBeg))
			)";
			$params['PersonRegister_setDateBeg'] = $params['PersonRegister_setDateRange'][0];
			$params['PersonRegister_setDateEnd'] = $params['PersonRegister_setDateRange'][1];
		}
		if (!empty($params['CVIRegistry_setDTRange'][0]) && !empty($params['CVIRegistry_setDTRange'][1])) {
			$where .= " and
				(
					(
						ro.RepositoryObserv_arrivalDate <= :CVIRegistry_setDTEnd
						and (ro.RepositoryObserv_arrivalDate is null or ro.RepositoryObserv_arrivalDate >= :CVIRegistry_setDTBeg)
					) or (
						ro.RepositoryObesrv_contactDate <= :CVIRegistry_setDTEnd
						and (ro.RepositoryObesrv_contactDate is null or ro.RepositoryObesrv_contactDate >= :CVIRegistry_setDTBeg)
					)
				)";
			$params['CVIRegistry_setDTBeg'] = $params['CVIRegistry_setDTRange'][0];
			$params['CVIRegistry_setDTEnd'] = $params['CVIRegistry_setDTRange'][1];
		}
		if (!empty($params['CVIRegistry_disDTRange'][0]) && !empty($params['CVIRegistry_disDTRange'][1])) {
			$where .= " and (pq.PersonQuarantine_endDT between :CVIRegistry_disDTBeg and :CVIRegistry_disDTEnd)";
			$params['CVIRegistry_disDTBeg'] = $params['CVIRegistry_disDTRange'][0];
			$params['CVIRegistry_disDTEnd'] = $params['CVIRegistry_disDTRange'][1];
		}
		if (!empty($params['ControlCard_Type'])) {
			switch ($params['ControlCard_Type']) {
				case 1: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id and v_PersonQuarantine.PersonQuarantine_endDT is null)"; break;
				case 2: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id and v_PersonQuarantine.PersonQuarantine_endDT is not null)"; break;
				case 3: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id)"; break;
				case 4: $where .= " and not exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id)"; break;
			}
		}
		if (!empty($params['ControlCard_OpenDateRange'][0]) && !empty($params['ControlCard_OpenDateRange'][1])) {
			$where .= " and exists (
				select PersonQuarantine_id from v_PersonQuarantine
				where v_PersonQuarantine.Person_id = ps.Person_id
					and (
						v_PersonQuarantine.PersonQuarantine_endDT is null
						or v_PersonQuarantine.PersonQuarantine_endDT > :ControlCard_OpenDateEnd
					)
			)";
			$params['ControlCard_OpenDateBeg'] = $params['ControlCard_OpenDateRange'][0];
			$params['ControlCard_OpenDateEnd'] = $params['ControlCard_OpenDateRange'][1];
		}
		if (!empty($params['LpuRegion_id']) && !empty($params['pmuser_id'])) {
			$filter .= " and (pc.LpuRegion_id = :LpuRegion_id or pq.pmUser_insID = :pmuser_id)";
		} elseif (!empty($params['LpuRegion_id'])) {
			$filter .= " and pc.LpuRegion_id = :LpuRegion_id";
		} elseif (!empty($params['pmuser_id'])) {
			$filter .= " and pq.pmUser_insID = :pmuser_id";
		}
		if (!empty($params['RegistryIncludeOnMSS'])) {
			$where .= " and (1<>1)";
		}
		if (!empty($params['LeaveType_id'])) {
			$where .= " and (1<>1)";
		}
		if (!empty($params['ResultClass_id'])) {
			$where .= " and (1<>1)";
		}
		if (!empty($params['Diag_Code_From']) || !empty($params['Diag_Code_To'])) {
			$where .= " and (1<>1)";
		}
		if (!empty($params['Diag_id'])) {
			$where .= " and md.Diag_id in ({$params['Diag_id']})";
		}
		if (!empty($params['Lpu_id'])) {
			$where .= " and msfc.Lpu_id = :Lpu_id";
		}
		if (!empty($params['Person_SurName'])) {
			$where .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($params['Person_FirName'])) {
			$where .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($params['Person_SecName'])) {
			$where .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}

		$query = "with FirstRepositoryObserv as (
				select * from (
					select *, row_number() over (partition by PersonQuarantine_id order by PersonQuarantine_id, RepositoryObserv_setDT desc, RepositoryObserv_insDT desc, RepositoryObserv_id desc) as row
					from dbo.v_RepositoryObserv
					where v_RepositoryObserv.RepositoryObesrv_IsFirstRecord = 2
				) t where t.row = 1
			),
			MiniDiag as (
				select Diag_id, Diag_Code
				from v_Diag
				where Diag_Code in ('Z20.8', 'Z03.8')
			),
			UslugaPar as (
				select v_EvnUslugaPar.*
				from dbo.v_EvnUslugaPar
				inner join dbo.v_UslugaComplex uc on uc.UslugaComplex_id = v_EvnUslugaPar.UslugaComplex_id
				where uc.UslugaComplex_Code in ('A26.05.066.002', 'A26.08.027.001', 'A26.08.046.002', 'A26.09.044.002', 'A26.09.060.002')
			),
			Quarantine as (
				select * from (
					select
						pq.*,
						pc.LpuRegion_id,
						pqor.PersonQuarantineOpenReason_Code,
						case
							when pqor.PersonQuarantineOpenReason_Code = '1' then 'Z20.8'
							when pqor.PersonQuarantineOpenReason_Code = '2' then 'Z03.8'
						end as Diag_Code,
						row_number() over (partition by pq.Person_id order by pq.Person_id, pq.PersonQuarantine_begDT desc, pq.PersonQuarantine_insDT desc, pq.PersonQuarantine_id desc) as row
					from dbo.v_PersonQuarantine pq
					left join dbo.v_PersonQuarantineOpenReason pqor on pqor.PersonQuarantineOpenReason_id = pq.PersonQuarantineOpenReason_id
					left join v_PersonCardState pc on pc.Person_id = pq.Person_id and pc.LpuAttachType_id = 1
					where {$filter}
				) t where t.row = 1
			)
			
			select
				'pq' as \"RecType\",
				pq.PersonQuarantine_id as \"id\",
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_BirthDay as \"Person_BirthDay\",
				dbo.Age(ps.Person_BirthDay, getdate()) as \"Person_Age\",
				coalesce(ro.RepositoryObserv_arrivalDate, ro.RepositoryObesrv_contactDate, null) as \"begDT\",
				pq.PersonQuarantine_endDT as \"endDT\",
				pq.Diag_Code as \"Diag_Code\",
				null as \"SopDiag\",
				'На дому' as \"TreatmentPlace\",
				'Легкая' as \"Heft\",
				'Самостоятельно' as \"Nutrition\",
				'Нет' as \"Ventilation\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				pqcr.PersonQuarantineCloseReason_Name as \"Result\",
				coalesce(ro.RepositoryObserv_arrivalDate, ro.RepositoryObesrv_contactDate, null) as \"setDT\",
				null as \"disDT\"
			from Quarantine pq
			inner join dbo.v_PersonState ps on ps.Person_id = pq.Person_id
			left join dbo.MedStaffFactCache msfc on msfc.MedStaffFact_id = pq.MedStaffFact_id
			left join FirstRepositoryObserv ro on ro.PersonQuarantine_id = pq.PersonQuarantine_id
			left join MiniDiag md on md.Diag_Code = pq.Diag_Code
			left join UslugaPar eup on eup.EvnUslugaPar_id = (select up.EvnUslugaPar_id from UslugaPar up where up.Person_id = ps.Person_id order by up.EvnUslugaPar_setDT desc limit 1)
			left join dbo.v_UslugaTest ut on ut.UslugaTest_id = (select UslugaTest_id from dbo.v_UslugaTest inner join dbo.v_EvnLabSample els on els.EvnLabSample_id = v_UslugaTest.EvnLabSample_id where UslugaTest_ResultValue is not null and els.EvnLabSample_id = eup.EvnLabSample_id order by UslugaTest_setDT desc limit 1)
			left join dbo.v_PersonQuarantineCloseReason pqcr on pqcr.PersonQuarantineCloseReason_id = pq.PersonQuarantineCloseReason_id
			where {$where}
		";

		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function loadEvnPL($params) {
		$where = "(1=1)";
		$with = "";
		switch ($params['mode']) {
			case 'suspicion':
				$where .= " and (reg.CVIRegistry_isConfirmed = 1 or reg.CVIRegistry_isConfirmed is null)";
				break;
			case 'confirmed':
				$where .= " and reg.CVIRegistry_isConfirmed = 2";
				break;
			case 'recovered':
				$with = "
					,lastCVI as (
						select t.CVIRegistry_id from (
							select cvireg.CVIRegistry_id,
							row_number() over ( partition by register.Person_id order by Person_id, cvireg.CVIRegistry_setDT desc) as row
							from dbo.v_CVIRegistry cvireg
							inner join dbo.v_PersonRegister register on register.PersonRegister_id = cvireg.PersonRegister_id
							where cvireg.CVIRegistry_isConfirmed = 2
						) as t
						where t.row = 1
					)
				";
				$where .= " and (ps.Person_IsDead = 1 or ps.Person_IsDead is null)";
				$where .= " and reg.CVIRegistry_isConfirmed = 2";
				$where .= " and (epl_rc.ResultClass_Code in ('1', '6') and exists (select * from lastCVI where lastCVI.CVIRegistry_id = reg.CVIRegistry_id) )
				";
				break;
			case 'died':
				$where .= ' and (1<>1)';
				break;
			case 'notconfirmed':
				$where .= " and reg.CVIRegistry_disDT is not null and (
					(
						select count(CVIRegistry_id) from dbo.v_CVIRegistry
						inner join dbo.v_PersonRegister on v_PersonRegister.PersonRegister_id = v_CVIRegistry.PersonRegister_id
						where CVIRegistry_isConfirmed = 2 and v_PersonRegister.Person_id = ps.Person_id
					) = 0
					or
					datediff('day', reg.CVIRegistry_setDT, getdate()) > 21
				)";
				break;
		}

		if (!empty($params['Status_id'])) {
			if ($params['Status_id'] == 1) $where .= " and epl.ResultClass_id is null";
			else if ($params['Status_id'] == 2) $where .= " and epl.ResultClass_id is not null";
		}
		if (!empty($params['TreatmentPlace'])) {
			if ($params['TreatmentPlace'] == 1) $where .= " and (1<>1)";
		}
		if (!empty($params['RegistryRecordType_id'])) {
			if ($params['RegistryRecordType_id'] == 1) $where .= ' and pr.PersonRegister_disDate is null';
			elseif ($params['RegistryRecordType_id'] == 2) $where .= ' and pr.PersonRegister_disDate is not null';
			elseif ($params['RegistryRecordType_id'] == 3) $where .= ' and pr.PersonRegister_Deleted is not null';
		}
		if (!empty($params['PersonRegister_setDateRange'][0]) && !empty($params['PersonRegister_setDateRange'][1])) {
			$where .= " and pr.PersonRegister_setDate <= :PersonRegister_setDateEnd";
			$where .= " and (pr.PersonRegister_setDate is null or pr.PersonRegister_setDate >= :PersonRegister_setDateBeg)";
			$params['PersonRegister_setDateBeg'] = $params['PersonRegister_setDateRange'][0];
			$params['PersonRegister_setDateEnd'] = $params['PersonRegister_setDateRange'][1];
		}
		if (!empty($params['CVIRegistry_setDTRange'][0]) && !empty($params['CVIRegistry_setDTRange'][1])) {
			$where .= " and epl.EvnPL_setDate <= :CVIRegistry_setDTEnd";
			$where .= " and (epl.EvnPL_setDate is null or epl.EvnPL_setDate >= :CVIRegistry_setDTBeg)";
			$params['CVIRegistry_setDTBeg'] = $params['CVIRegistry_setDTRange'][0];
			$params['CVIRegistry_setDTEnd'] = $params['CVIRegistry_setDTRange'][1];
		}
		if (!empty($params['CVIRegistry_disDTRange'][0]) && !empty($params['CVIRegistry_disDTRange'][1])) {
			$where .= " and (epl.EvnPL_disDate between :CVIRegistry_disDTBeg and :CVIRegistry_disDTEnd)";
			$params['CVIRegistry_disDTBeg'] = $params['CVIRegistry_disDTRange'][0];
			$params['CVIRegistry_disDTEnd'] = $params['CVIRegistry_disDTRange'][1];
		}
		if (!empty($params['ControlCard_Type'])) {
			switch ($params['ControlCard_Type']) {
				case 1: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id and v_PersonQuarantine.PersonQuarantine_endDT is null)"; break;
				case 2: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id and v_PersonQuarantine.PersonQuarantine_endDT is not null)"; break;
				case 3: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id)"; break;
				case 4: $where .= " and not exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id)"; break;
			}
		}
		if (!empty($params['ControlCard_OpenDateRange'][0]) && !empty($params['ControlCard_OpenDateRange'][1])) {
			$where .= " and exists (
				select PersonQuarantine_id from v_PersonQuarantine
				where v_PersonQuarantine.Person_id = ps.Person_id
					and (
						v_PersonQuarantine.PersonQuarantine_endDT is null
						or v_PersonQuarantine.PersonQuarantine_endDT > :ControlCard_OpenDateEnd
					)
			)";
			$params['ControlCard_OpenDateBeg'] = $params['ControlCard_OpenDateRange'][0];
			$params['ControlCard_OpenDateEnd'] = $params['ControlCard_OpenDateRange'][1];
		}
		if (!empty($params['LpuRegion_id']) && !empty($params['pmuser_id'])) {
			$where .= " and (pc.LpuRegion_id = :LpuRegion_id or epl.pmUser_insID = :pmuser_id)";
		} elseif (!empty($params['LpuRegion_id'])) {
			$where .= " and pc.LpuRegion_id = :LpuRegion_id";
		} elseif (!empty($params['pmuser_id'])) {
			$where .= " and epl.pmUser_insID = :pmuser_id";
		}
		if (!empty($params['RegistryIncludeOnMSS'])) {
			if ($params['RegistryIncludeOnMSS'] == 2) {
				$where .= " and (reg.CVIRegistry_id is not null and reg.EvnPL_id is null and reg.EvnPS_id is null)";
			} elseif ($params['RegistryIncludeOnMSS'] == 1) {
				$where .= " and (reg.CVIRegistry_id is not null and (reg.EvnPL_id is not null or reg.EvnPS_id is not null))";
			}
		}
		if (!empty($params['LeaveType_id'])) {
			$where .= " and (1<>1)";
		}
		if (!empty($params['ResultClass_id'])) {
			$where .= " and epl_rc.ResultClass_id = :ResultClass_id";
		}
		if (!empty($params['Diag_Code_From']) || !empty($params['Diag_Code_To'])) {
			$SopDiagFilters = "";
			if (!empty($params['Diag_Code_From'])) {
				$SopDiagFilters .= " and d.Diag_Code >= :Diag_Code_From";
			}
			if (!empty($params['Diag_Code_To'])) {
				$SopDiagFilters .= " and d.Diag_Code <= :Diag_Code_To";
			}
			$where .= " and exists(
				select *
				from dbo.v_EvnDiagPLSop_all edpls
				inner join v_Diag d on d.Diag_id = edpls.Diag_id
				where EvnDiagPLSop_rid = epl.EvnPL_id
				{$SopDiagFilters}
			)";
		}
		if (!empty($params['Diag_id'])) {
			$where .= " and epl_diag.Diag_id in ({$params['Diag_id']})";
		}
		if (!empty($params['Lpu_id'])) {
			$where .= " and epl.Lpu_id = :Lpu_id";
		}
		if (!empty($params['Person_SurName'])) {
			$where .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($params['Person_FirName'])) {
			$where .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($params['Person_SecName'])) {
			$where .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}

		$query = "with UslugaPar as (
				select v_EvnUslugaPar.*
				from dbo.v_EvnUslugaPar
				inner join dbo.v_UslugaComplex uc on uc.UslugaComplex_id = v_EvnUslugaPar.UslugaComplex_id
				where uc.UslugaComplex_Code in ('A26.05.066.002', 'A26.08.027.001', 'A26.08.046.002', 'A26.09.044.002', 'A26.09.060.002')
			){$with}
			select
				'pl' as \"RecType\",
				reg.CVIRegistry_id as \"id\",
				reg.CVIRegistry_id as \"CVIRegistry_id\",
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_BirthDay as \"Person_BirthDay\",
				dbo.Age(ps.Person_BirthDay, getdate()) as \"Person_Age\",
				reg.CVIRegistry_setDT as \"begDT\",
				reg.CVIRegistry_disDT as \"endDT\",
				epl_diag.Diag_id as \"Diag_id\",
				epl_diag.Diag_Code as \"Diag_Code\",
				epl_diag.Diag_Name as \"Diag_Name\",
				(
					select string_agg(Diag.Diag_Code, ', ')
					from dbo.v_EvnPL pl
					inner join dbo.v_EvnVizitPL vpl on vpl.EvnVizitPL_pid = pl.EvnPL_id
					inner join dbo.v_EvnDiagPLSop DiagSop on DiagSop.EvnDiagPLSop_pid = vpl.EvnVizitPL_id and DiagSop.DiagSetClass_id <> 1
					inner join dbo.v_Diag Diag on Diag.Diag_id = DiagSop.Diag_id
					where (pl.EvnPL_setDate >= '2020-03-01') and pl.EvnPL_id = reg.EvnPL_id
				) as \"SopDiag\",
				'На дому' as \"TreatmentPlace\",
				'Легкая' as \"Heft\",
				'Самостоятельно' as \"Nutrition\",
				'Нет' as \"Ventilation\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				epl_rc.ResultClass_Name as \"Result\",
				pr.PersonRegister_setDate as \"setDT\",
				pr.PersonRegister_disDate as \"disDT\"
			from (
				select pr.PersonRegister_id,
				   pr.Person_id,
				   pr.PersonRegister_setDate,
				   pr.PersonRegister_disDate,
				   pr.PersonRegister_Deleted
				from dbo.PersonRegister pr
				inner join dbo.v_MorbusType mt on mt.MorbusType_id = pr.MorbusType_id
				where mt.MorbusType_Code = 116
			) as pr
			inner join dbo.v_PersonState ps on ps.Person_id = pr.Person_id
			inner join dbo.v_CVIRegistry reg on reg.PersonRegister_id = pr.PersonRegister_id
			inner join dbo.v_EvnPL epl on epl.EvnPL_id = reg.EvnPL_id
			left join dbo.v_Diag epl_diag on epl_diag.Diag_id = epl.Diag_id
			left join dbo.v_ResultClass epl_rc on epl_rc.ResultClass_id = epl.ResultClass_id
			left join v_PersonCardState pc on pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
			left join UslugaPar eup on eup.EvnUslugaPar_id = (select up.EvnUslugaPar_id from UslugaPar up where up.Person_id = ps.Person_id order by up.EvnUslugaPar_setDT desc limit 1)
			left join dbo.v_UslugaTest ut on ut.UslugaTest_id = (select UslugaTest_id from dbo.v_UslugaTest inner join dbo.v_EvnLabSample els on els.EvnLabSample_id = v_UslugaTest.EvnLabSample_id where UslugaTest_ResultValue is not null and els.EvnLabSample_id = eup.EvnLabSample_id order by UslugaTest_setDT desc limit 1)
			where {$where}
		";

		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function loadEvnPS($params) {
		$where = "(1=1)"; $outerApply = ""; $with = "";
		switch ($params['mode']) {
			case 'suspicion':
				$where .= " and (reg.CVIRegistry_isConfirmed = 1 or reg.CVIRegistry_isConfirmed is null)";
				break;
			case 'confirmed':
				$where .= " and reg.CVIRegistry_isConfirmed = 2";
				break;
			case 'recovered':
				$with = "
					,lastCVI as (
						select t.CVIRegistry_id from (
							select cvireg.CVIRegistry_id,
							row_number() over ( partition by register.Person_id order by Person_id, cvireg.CVIRegistry_setDT desc) as row
							from dbo.v_CVIRegistry cvireg
							inner join dbo.v_PersonRegister register on register.PersonRegister_id = cvireg.PersonRegister_id
							where cvireg.CVIRegistry_isConfirmed = 2
						) as t
						where t.row = 1
					)
				";
				$where .= " and (ps.Person_IsDead = 1 or ps.Person_IsDead is null)";
				$where .= " and reg.CVIRegistry_isConfirmed = 2";
				$where .= " and eps_lt.LeaveType_Code in ('1', '2') and exists (select * from lastCVI where lastCVI.CVIRegistry_id = reg.CVIRegistry_id)";
				break;
			case 'died':
				$where .= ' and (1<>1)';
				break;
			case 'notconfirmed':
				$where .= " and reg.CVIRegistry_disDT is not null and (
					(
						select count(CVIRegistry_id) from dbo.v_CVIRegistry
						inner join dbo.v_PersonRegister on v_PersonRegister.PersonRegister_id = v_CVIRegistry.PersonRegister_id
						where CVIRegistry_isConfirmed = 2 and v_PersonRegister.Person_id = ps.Person_id
					) = 0
					or
					datediff('day', reg.CVIRegistry_setDT, getdate()) > 21
				)";
				break;
		}

		if (!empty($params['Status_id'])) {
			if ($params['Status_id'] == 1) $where .= " and eps.EvnPS_disDate is null";
			else if ($params['Status_id'] == 2) $where .= " and eps.EvnPS_disDate is not null";
		}
		if (!empty($params['TreatmentPlace'])) {
			if ($params['TreatmentPlace'] == 2) $where .= " and (1<>1)";
		}
		if (!empty($params['RegistryRecordType_id'])) {
			if ($params['RegistryRecordType_id'] == 1) $where .= ' and pr.PersonRegister_disDate is null';
			elseif ($params['RegistryRecordType_id'] == 2) $where .= ' and pr.PersonRegister_disDate is not null';
			elseif ($params['RegistryRecordType_id'] == 3) $where .= ' and pr.PersonRegister_Deleted is not null';
		}
		if (!empty($params['PersonRegister_setDateRange'][0]) && !empty($params['PersonRegister_setDateRange'][1])) {
			$where .= " and pr.PersonRegister_setDate <= :PersonRegister_setDateEnd";
			$where .= " and (pr.PersonRegister_setDate is null or pr.PersonRegister_setDate >= :PersonRegister_setDateBeg)";
			$params['PersonRegister_setDateBeg'] = $params['PersonRegister_setDateRange'][0];
			$params['PersonRegister_setDateEnd'] = $params['PersonRegister_setDateRange'][1];
		}
		if (!empty($params['CVIRegistry_setDTRange'][0]) && !empty($params['CVIRegistry_setDTRange'][1])) {
			$where .= " and eps.EvnPS_setDate <= :CVIRegistry_setDTEnd";
			$where .= " and (eps.EvnPS_setDate is null or eps.EvnPS_setDate >= :CVIRegistry_setDTBeg)";
			$params['CVIRegistry_setDTBeg'] = $params['CVIRegistry_setDTRange'][0];
			$params['CVIRegistry_setDTEnd'] = $params['CVIRegistry_setDTRange'][1];
		}
		if (!empty($params['CVIRegistry_disDTRange'][0]) && !empty($params['CVIRegistry_disDTRange'][1])) {
			$where .= " and (eps.EvnPS_disDate between :CVIRegistry_disDTBeg and :CVIRegistry_disDTEnd)";
			$params['CVIRegistry_disDTBeg'] = $params['CVIRegistry_disDTRange'][0];
			$params['CVIRegistry_disDTEnd'] = $params['CVIRegistry_disDTRange'][1];
		}
		if (!empty($params['ControlCard_Type'])) {
			switch ($params['ControlCard_Type']) {
				case 1: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id and v_PersonQuarantine.PersonQuarantine_endDT is null)"; break;
				case 2: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id and v_PersonQuarantine.PersonQuarantine_endDT is not null)"; break;
				case 3: $where .= " and exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id)"; break;
				case 4: $where .= " and not exists (select PersonQuarantine_id from v_PersonQuarantine where v_PersonQuarantine.Person_id = ps.Person_id)"; break;
			}
		}
		if (!empty($params['ControlCard_OpenDateRange'][0]) && !empty($params['ControlCard_OpenDateRange'][1])) {
			$where .= " and exists (
				select PersonQuarantine_id from v_PersonQuarantine
				where v_PersonQuarantine.Person_id = ps.Person_id
					and (
						v_PersonQuarantine.PersonQuarantine_endDT is null
						or v_PersonQuarantine.PersonQuarantine_endDT > :ControlCard_OpenDateEnd
					)
			)";
			$params['ControlCard_OpenDateBeg'] = $params['ControlCard_OpenDateRange'][0];
			$params['ControlCard_OpenDateEnd'] = $params['ControlCard_OpenDateRange'][1];
		}
		if (!empty($params['LpuRegion_id']) && !empty($params['pmuser_id'])) {
			$where .= " and (pc.LpuRegion_id = :LpuRegion_id or eps.pmUser_insID = :pmuser_id)";
		} elseif (!empty($params['LpuRegion_id'])) {
			$where .= " and pc.LpuRegion_id = :LpuRegion_id";
		} elseif (!empty($params['pmuser_id'])) {
			$where .= " and eps.pmUser_insID = :pmuser_id";
		}
		if (!empty($params['RegistryIncludeOnMSS'])) {
			if ($params['RegistryIncludeOnMSS'] == 2) {
				$where .= " and (reg.CVIRegistry_id is not null and reg.EvnPL_id is null and reg.EvnPS_id is null)";
			} elseif ($params['RegistryIncludeOnMSS'] == 1) {
				$where .= " and (reg.CVIRegistry_id is not null and (reg.EvnPL_id is not null or reg.EvnPS_id is not null))";
			}
		}
		if (!empty($params['LeaveType_id'])) {
			$where .= " and eps_lt.LeaveType_id = :LeaveType_id";
		}
		if (!empty($params['ResultClass_id'])) {
			$where .= " and (1<>1)";
		}
		if (!empty($params['Diag_Code_From']) || !empty($params['Diag_Code_To'])) {
			$SopDiagFilters = "";
			if (!empty($params['Diag_Code_From'])) {
				$SopDiagFilters .= " and edps_diag.Diag_Code >= :Diag_Code_From";
			}
			if (!empty($params['Diag_Code_To'])) {
				$SopDiagFilters .= " and edps_diag.Diag_Code <= :Diag_Code_To";
			}
			$where .= " and exists(
				select *
				from dbo.v_EvnDiagPS edps
				inner join dbo.v_Diag edps_diag on edps_diag.Diag_id = edps.Diag_id
				where edps.EvnDiagPS_rid = eps.EvnPS_id and DiagSetClass_id = 3 and DiagSetType_id = 3
				{$SopDiagFilters}
			)";
		}
		if (!empty($params['Diag_id'])) {
			$where .= " and eps_diag.Diag_id in ({$params['Diag_id']})";
		}
		if (!empty($params['Lpu_id'])) {
			$where .= " and section.Lpu_id = :Lpu_id";
		}
		if (!empty($params['Person_SurName'])) {
			$where .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($params['Person_FirName'])) {
			$where .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($params['Person_SecName'])) {
			$where .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}

		$query = "with Registry as (
				select pr.PersonRegister_id,
					   pr.Person_id,
					   pr.PersonRegister_setDate,
					   pr.PersonRegister_disDate,
					   pr.PersonRegister_Deleted
				from dbo.PersonRegister pr
				inner join dbo.v_MorbusType mt on mt.MorbusType_id = pr.MorbusType_id
				where mt.MorbusType_Code = 116
			),
			UslugaPar as (
				select v_EvnUslugaPar.*
				from dbo.v_EvnUslugaPar
				inner join dbo.v_UslugaComplex uc on uc.UslugaComplex_id = v_EvnUslugaPar.UslugaComplex_id
				where v_EvnUslugaPar.EvnUslugaPar_insDT > '2020-01-01' and uc.UslugaComplex_Code in ('A26.05.066.002', 'A26.08.027.001', 'A26.08.046.002', 'A26.09.044.002', 'A26.09.060.002')
			){$with}
			
			select
				'ps' as \"RecType\",
				reg.CVIRegistry_id as \"id\",
				reg.CVIRegistry_id as \"CVIRegistry_id\",
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_BirthDay as \"Person_BirthDay\",
				dbo.Age(ps.Person_BirthDay, getdate()) as \"Person_Age\",
				reg.CVIRegistry_setDT as \"begDT\",
				reg.CVIRegistry_disDT as \"endDT\",
				eps_diag.Diag_id as \"Diag_id\",
				eps_diag.Diag_Code as \"Diag_Code\",
				(
					select string_agg(sd.Diag_Code, ', ')
					from dbo.v_EvnSection
					left join dbo.v_EvnDiagPS on v_EvnDiagPS.EvnDiagPS_pid = v_EvnSection.EvnSection_id
					left join dbo.v_Diag sd on sd.Diag_id = v_EvnDiagPS.Diag_id
					where v_EvnSection.EvnSection_pid = reg.EvnPS_id and v_EvnDiagPS.DiagSetClass_id <> 1
				) as \"SopDiag\",
				eps_lpu.Lpu_Nick as \"TreatmentPlace\",
				eps_lpu.Lpu_Nick as \"Lpu_Nick\",
				case
					when eps_erp.EvnReanimatPeriod_id is null then 'Легкая'
					when (erc.ReanimConditionType_id = 7 or erc.ReanimConditionType_id is null) then 'Средняя'
					else 'Тяжелая'
				end as \"Heft\",
				case
					when eps_nutrition.EvnReanimatAction_id is not null then 'Зонд'
					else 'Самостоятельно'
				end as \"Nutrition\",
				case
					when eps_ventilation.EvnReanimatAction_setDate is not null then cast(datediff('hour', eps_ventilation.EvnReanimatAction_setDT, getdate()) as varchar)
					else 'Нет'
				end as \"Ventilation\",
				eup.EvnDirection_id as \"EvnDirection_id\",
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				eps_lt.LeaveType_Name as \"Result\",
				pr.PersonRegister_setDate as \"setDT\",
				pr.PersonRegister_disDate as \"disDT\"
			from Registry pr
			inner join dbo.v_CVIRegistry reg on reg.PersonRegister_id = pr.PersonRegister_id
			inner join dbo.v_PersonState ps on ps.Person_id = pr.Person_id
			inner join dbo.v_EvnPS eps on eps.EvnPS_id = reg.EvnPS_id
			left join v_PersonCardState pc on pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
			left join dbo.v_EvnSection section on section.EvnSection_pid = eps.EvnPS_id and  section.EvnSection_Index = section.EvnSection_Count - 1
			left join dbo.v_LeaveType eps_lt on eps_lt.LeaveType_id = section.LeaveType_id
			left join dbo.v_Diag eps_diag on eps_diag.Diag_id = section.Diag_id
			left join dbo.v_Lpu_all eps_lpu on eps_lpu.Lpu_id = section.Lpu_id
			left join dbo.v_EvnReanimatPeriod eps_erp on eps_erp.EvnReanimatPeriod_pid = section.EvnSection_id and eps_erp.EvnReanimatPeriod_disDT is null
			left join dbo.v_EvnReanimatCondition erc on erc.EvnReanimatCondition_pid = eps_erp.EvnReanimatPeriod_id and erc.EvnReanimatCondition_Index = erc.EvnReanimatCondition_Count - 1
			left join dbo.v_EvnReanimatAction eps_nutrition on eps_nutrition.EvnReanimatAction_pid = eps_erp.EvnReanimatPeriod_id and eps_nutrition.ReanimatActionType_id = 3 and eps_nutrition.EvnReanimatAction_disDT is null and eps_nutrition.NutritiousType_id <> 4
			left join dbo.v_EvnReanimatAction eps_ventilation on eps_ventilation.EvnReanimatAction_pid = eps_erp.EvnReanimatPeriod_id and eps_ventilation.ReanimatActionType_id = 1 and eps_ventilation.EvnReanimatAction_disDT is null
			left join UslugaPar eup on eup.EvnUslugaPar_id = (select up.EvnUslugaPar_id from UslugaPar up where up.Person_id = ps.Person_id order by up.EvnUslugaPar_setDT desc limit 1)
			left join dbo.v_UslugaTest ut on ut.UslugaTest_id = (select UslugaTest_id from dbo.v_UslugaTest inner join dbo.v_EvnLabSample els on els.EvnLabSample_id = v_UslugaTest.EvnLabSample_id where UslugaTest_ResultValue is not null and els.EvnLabSample_id = eup.EvnLabSample_id order by UslugaTest_setDT desc limit 1)
			{$outerApply}
			where {$where}
		";

		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function loadDied($params) {
		$where = "(1=1)"; $filter = "";
		switch ($params['mode']) {
			case 'suspicion':
			case 'confirmed':
			case 'recovered':
			case 'notconfirmed':
				return [];
				break;
		}

		if (!empty($params['PersonRegister_setDateRange'][0]) && !empty($params['PersonRegister_setDateRange'][1])) {
			$where .= " and pr.PersonRegister_setDate <= :PersonRegister_setDateEnd";
			$where .= " and (pr.PersonRegister_setDate is null or pr.PersonRegister_setDate >= :PersonRegister_setDateBeg)";
			$params['PersonRegister_setDateBeg'] = $params['PersonRegister_setDateRange'][0];
			$params['PersonRegister_setDateEnd'] = $params['PersonRegister_setDateRange'][1];
		}
		if (!empty($params['CVIRegistry_setDTRange'][0]) && !empty($params['CVIRegistry_setDTRange'][1])) {
			$where .= " and (
				(pr.PersonRegister_setDate is not null and pr.PersonRegister_setDate <= :CVIRegistry_setDTEnd and pr.PersonRegister_setDate >= :CVIRegistry_setDTBeg)
				or
				(ds.DeathSvid_DeathDate is not null and ds.DeathSvid_DeathDate <= :CVIRegistry_setDTEnd and ds.DeathSvid_DeathDate >= :CVIRegistry_setDTBeg)
			)";
			$params['CVIRegistry_setDTBeg'] = $params['CVIRegistry_setDTRange'][0];
			$params['CVIRegistry_setDTEnd'] = $params['CVIRegistry_setDTRange'][1];
		}
		if (!empty($params['CVIRegistry_disDTRange'][0]) && !empty($params['CVIRegistry_disDTRange'][1])) {
			$where .= " and (ds.DeathSvid_DeathDate between :CVIRegistry_disDTBeg and :CVIRegistry_disDTEnd)";
			$params['CVIRegistry_disDTBeg'] = $params['CVIRegistry_disDTRange'][0];
			$params['CVIRegistry_disDTEnd'] = $params['CVIRegistry_disDTRange'][1];
		}
		if (!empty($params['LpuRegion_id']) && !empty($params['pmuser_id'])) {
			$where .= " and (pc.LpuRegion_id = :LpuRegion_id or ds.pmUser_insID = :pmuser_id)";
		} elseif (!empty($params['LpuRegion_id'])) {
			$where .= " and pc.LpuRegion_id = :LpuRegion_id";
		} elseif (!empty($params['pmuser_id'])) {
			$where .= " and ds.pmUser_insID = :pmuser_id";
		}
		if (!empty($params['RegistryIncludeOnMSS'])) {
			if ($params['RegistryIncludeOnMSS'] == 2) {
				$where .= " and (pr.PersonRegister_id is null)";
			} elseif ($params['RegistryIncludeOnMSS'] == 1) {
				$where .= " and (pr.PersonRegister_id is not null)";
			}
		}
		if (!empty($params['Diag_id'])) {
			$filter .= " and (
				idiag.Diag_id in ({$params['Diag_id']})
				or tdiag.Diag_id in ({$params['Diag_id']})
				or mdiag.Diag_id in ({$params['Diag_id']})
				or ediag.Diag_id in ({$params['Diag_id']})
				or odiag.Diag_id in ({$params['Diag_id']})
			)";
		}
		if (!empty($params['Lpu_id'])) {
			$where .= " and ds.Lpu_id = :Lpu_id";
		}
		if (!empty($params['Person_SurName'])) {
			$where .= " and ps.Person_SurName ilike :Person_SurName || '%'";
		}
		if (!empty($params['Person_FirName'])) {
			$where .= " and ps.Person_FirName ilike :Person_FirName || '%'";
		}
		if (!empty($params['Person_SecName'])) {
			$where .= " and ps.Person_SecName ilike :Person_SecName || '%'";
		}

		$query = "with DeathSvid as (
				select
					t.*,
					idiag.Diag_Code as IDiag_Code,
					tdiag.Diag_Code as TDiag_Code,
					mdiag.Diag_Code as MDiag_Code,
					ediag.Diag_Code as EDiag_Code,
					odiag.Diag_Code as ODiag_Code
				from (
					select ds.*, row_number() over (partition by ds.Person_id order by ds.DeathSvid_DeathDate desc) as row
					from dbo.v_DeathSvid ds
					where DeathSvid_insDT > '2020-01-01' and (ds.DeathSvid_IsBad = 1 or ds.DeathSvid_IsBad is null) and ds.DeathSvid_IsActual = 2
				) as t
				left join dbo.v_Diag idiag on idiag.Diag_id = t.Diag_iid
				left join dbo.v_Diag tdiag on tdiag.Diag_id = t.Diag_tid
				left join dbo.v_Diag mdiag on mdiag.Diag_id = t.Diag_mid
				left join dbo.v_Diag ediag on ediag.Diag_id = t.Diag_eid
				left join dbo.v_Diag odiag on odiag.Diag_id = t.Diag_oid
				where t.row = 1 and (
					idiag.Diag_Code in ('U07.1', 'U07.2')
					or tdiag.Diag_Code in ('U07.1', 'U07.2')
					or mdiag.Diag_Code in ('U07.1', 'U07.2')
					or ediag.Diag_Code in ('U07.1', 'U07.2')
					or odiag.Diag_Code in ('U07.1', 'U07.2')
				) {$filter}
			),
			UslugaPar as (
				select v_EvnUslugaPar.*
				from dbo.v_EvnUslugaPar
				inner join dbo.v_UslugaComplex uc on uc.UslugaComplex_id = v_EvnUslugaPar.UslugaComplex_id
				where uc.UslugaComplex_Code in ('A26.05.066.002', 'A26.08.027.001', 'A26.08.046.002', 'A26.09.044.002', 'A26.09.060.002')
			),
			Reg as (
				select
					pr.PersonRegister_id,
					pr.Person_id,
					pr.PersonRegister_setDate,
					pr.PersonRegister_disDate,
					pr.PersonRegister_Deleted
				from dbo.PersonRegister pr
				inner join dbo.v_MorbusType mt on mt.MorbusType_id = pr.MorbusType_id
				where mt.MorbusType_Code = 116
			),
			CVIReg as (
				select * from (
					select
						cvireg.*,
						Reg.Person_id,
						row_number() over (partition by Reg.Person_id order by cvireg.CVIRegistry_setDT desc) as row
					from dbo.v_CVIRegistry cvireg
					inner join Reg on Reg.PersonRegister_id = CVIReg.PersonRegister_id
					where cvireg.CVIRegistry_isConfirmed = 2
				) as t
				where t.row = 1
			)
			select
				'd' as \"RecType\",
				ds.DeathSvid_id as \"id\",
				ps.Person_id as \"Person_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				ps.Person_BirthDay as \"Person_BirthDay\",
				ds.DeathSvid_DeathDate as \"begDT\",
				ds.DeathSvid_DeathDate as \"endDT\",
				ds.Lpu_id as \"Lpu_id\",
				dbo.Age(ps.Person_BirthDay, getdate()) as \"Person_Age\",
				concat(ds.IDiag_Code, ' ', ds.TDiag_Code, ' ', ds.MDiag_Code, ' ', ds.EDiag_Code, ' ', ds.ODiag_Code) as \"Diag_Code\",
				'' as \"SopDiag\",
				'' as \"TreatmentPlace\",
				'' as \"Heft\",
				'' as \"Nutrition\",
				'' as \"Ventilation\",
				eup.EvnDirection_id as EvnDirection_id,
				ut.UslugaTest_ResultValue as \"UslugaTest_ResultValue\",
				coalesce(pr.PersonRegister_setDate, DeathSvid_DeathDate) as \"setDT\",
				case
					when pr.PersonRegister_id is not null then pr.PersonRegister_disDate
					else DeathSvid_DeathDate
				end as \"disDT\",
				case
					when cvireg.CVIRegistry_id is null then 2
					else 1
				end as \"IsRed\"
			from DeathSvid ds
			inner join dbo.v_PersonState ps on ps.Person_id = ds.Person_id
			left join Reg pr on pr.Person_id = ps.Person_id
			left join CVIReg cvireg on cvireg.Person_id = ps.Person_id
			left join dbo.v_PersonCardState pc on pc.Person_id = ps.Person_id and pc.LpuAttachType_id = 1
			left join UslugaPar eup on eup.EvnUslugaPar_id = (select up.EvnUslugaPar_id from UslugaPar up where up.Person_id = ps.Person_id order by up.EvnUslugaPar_setDT desc limit 1)
			left join dbo.v_UslugaTest ut on ut.UslugaTest_id = (select UslugaTest_id from dbo.v_UslugaTest inner join dbo.v_EvnLabSample els on els.EvnLabSample_id = v_UslugaTest.EvnLabSample_id where UslugaTest_ResultValue is not null and els.EvnLabSample_id = eup.EvnLabSample_id order by UslugaTest_setDT desc limit 1)
			where {$where}
		";

		try { return $this->queryResult($query, $params);
		} catch (Exception $e) { throw $e; }
	}

	public function findClosedCVI($params) {
		$query = "select
			reg.CVIRegistry_id as \"CVIRegistry_id\",
			reg.Lpu_id as \"Lpu_id\",
			reg.EvnPL_id as \"EvnPL_id\",
			reg.EvnPS_id as \"EvnPS_id\",
			reg.CVIRegistry_setDT as \"CVIRegistry_setDT\",
			reg.CVIRegistry_disDT as \"CVIRegistry_disDT\",
			pr.Person_id as \"Person_id\"
			from dbo.v_CVIRegistry reg
			inner join dbo.v_PersonRegister pr on pr.PersonRegister_id = reg.PersonRegister_id
			where datediff('day', CVIRegistry_disDT, :Evn_setDT) <= 30 and pr.Person_id = :Person_id
			order by reg.CVIRegistry_setDT desc
			limit 1
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	public function getLastCVI($params) {
		$query = "select
			reg.CVIRegistry_id as \"CVIRegistry_id\",
			reg.Lpu_id as \"Lpu_id\",
			reg.EvnPL_id as \"EvnPL_id\",
			reg.EvnPS_id as \"EvnPS_id\",
			reg.CVIRegistry_setDT as \"CVIRegistry_setDT\",
			reg.CVIRegistry_disDT as \"CVIRegistry_disDT\",
			pr.Person_id as \"Person_id\"
			from dbo.v_CVIRegistry reg
			inner join dbo.v_PersonRegister pr on pr.PersonRegister_id = reg.PersonRegister_id
			where pr.Person_id = :Person_id
			order by reg.CVIRegistry_setDT desc
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	public function findEvnByDate($params) {
		$query = "select
				Evn_id as \"Evn_id\",
				EvnClass_SysNick as \"EvnClass_SysNick\"
			from dbo.v_Evn
			where Evn_id <> :IgnoreEvn_id and Person_id = :Person_id and (EvnClass_SysNick = 'EvnPL' or EvnClass_SysNick = 'EvnPS') and Evn_setDate = :setDT
			order by Evn_setDT desc
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	public function getOne($params) {
		$where = "(1=1)";

		if (empty($params['EvnPL_id']) && empty($params['EvnPS_id'])) {
			return false;
		}
		if (!empty($params['EvnPL_id'])) {
			$where .= " and reg.EvnPL_id = :EvnPL_id";
		}
		if (!empty($params['EvnPS_id'])) {
			$where .= " and reg.EvnPS_id = :EvnPS_id";
		}

		$query = "select
				reg.CVIRegistry_id as \"CVIRegistry_id\",
				reg.Lpu_id as \"Lpu_id\",
				reg.EvnPL_id as \"EvnPL_id\",
				reg.EvnPS_id as \"EvnPS_id\",
				reg.CVIRegistry_setDT as \"CVIRegistry_setDT\",
				reg.CVIRegistry_disDT as \"CVIRegistry_disDT\",
				pr.Person_id as \"Person_id\"
			from dbo.v_CVIRegistry reg
			inner join dbo.v_PersonRegister pr on pr.PersonRegister_id = reg.PersonRegister_id
			where {$where}
			order by reg.CVIRegistry_setDT desc
			limit 1
		";

		return $this->getFirstRowFromQuery($query, $params);
	}

	public function deleteCVI($params) {
		$obj = $params['object']; $objKey = "{$obj}_id";
		$CVIRegistry = $this->getOne([
			$objKey => $params[$objKey]
		]);

		if (!empty($CVIRegistry)) {
			$params['Person_id'] = $CVIRegistry['Person_id'];
			$params['setDT'] = date_format($CVIRegistry['CVIRegistry_setDT'], 'Y-m-d');
			$params['IgnoreEvn_id'] = $params[$objKey];

			$Evn = $this->findEvnByDate($params);
			$this->execCommonSP('p_CVIRegistry_del', [
				'CVIRegistry_id' => $CVIRegistry['CVIRegistry_id']
			]);
			if (!empty($Evn)) {
				$params['source'] = $Evn['EvnClass_SysNick'];
				$params["{$Evn['EvnClass_SysNick']}_id"] = $Evn['Evn_id'];
				$this->saveCVIEvent($params);
			}
		}
	}

	public function getCVICount($params) {
		$where = "(1=1)";
		if (!empty($params['PersonRegister_id'])) {
			$where .= "and PersonRegister_id = :PersonRegister_id";
		}
		$query = "select count(CVIRegistry_id) as count
			from dbo.v_CVIRegistry
			where {$where}
		";
		return $this->getFirstRowFromQuery($query, $params);
	}

	public function saveCVIEvent($params) {
		$params['Lpu_id'] = $params['Lpu_id'] ?? $params['session']['lpu_id'];
		$isClosed = false; $PersonRegisterOutCause_id = null;

		switch ($params['source']) {
			case 'EvnDiagPL':
				$obj = 'EvnPL'; $objKey = 'EvnPL_id';
				$this->load->model('EvnPL_model', 'EvnPL_model');
				$DiagData = $this->EvnPL_model->getDiagData(['EvnDiag_id' => $params['EvnDiag_id']]);
				if (!empty($DiagData)) {
					$params['EvnPL_id'] = $DiagData['EvnDiag_rid'];
					$EvnPL = $this->EvnPL_model->getEvnPLViewData($params);
				}
				break;
			case 'EvnVizitPL':
				$obj = 'EvnPL'; $objKey = 'EvnPL_id';
				$this->load->model('EvnVizitPL_model', 'EvnVizitPL_model');
				$EvnVizitPL = $this->EvnVizitPL_model->getOne($params);
				if (!empty($EvnVizitPL)) {
					$this->load->model('EvnPL_model', 'EvnPL_model');
					$params['EvnPL_id'] = $EvnVizitPL['EvnVizitPL_pid'];
					$EvnPL = $this->EvnPL_model->getEvnPLViewData($params);
				}
				break;
			case 'EvnPL':
				$obj = 'EvnPL'; $objKey = 'EvnPL_id';
				$this->load->model('EvnPL_model', 'EvnPL_model');
				$EvnPL = $this->EvnPL_model->getEvnPLViewData($params);
				break;
			case 'EvnDiagPS':
				$obj = 'EvnPS'; $objKey = 'EvnPS_id';
				$this->load->model('EvnDiag_model', 'EvnDiag_model');
				$DiagData = $this->EvnDiag_model->getDiagData($params);
				if (!empty($DiagData)) {
					$this->load->model('EvnPS_model', 'EvnPS_model');
					$params['EvnPS_id'] = $DiagData['EvnDiagPS_rid'];
					$EvnPS = $this->EvnPS_model->loadEvnPSEditForm($params);
				}
				break;
			case 'EvnSection':
				$obj = 'EvnPS'; $objKey = 'EvnPS_id';
				$this->load->model('EvnSection_model', 'EvnSection_model');
				$EvnSection = $this->EvnSection_model->getEvnSectionViewData($params);
				if (!empty($EvnSection) && !empty($EvnSection[0])) {
					$this->load->model('EvnPS_model', 'EvnPS_model');
					$params['EvnPS_id'] = $EvnSection[0]['EvnSection_pid'];
					$EvnPS = $this->EvnPS_model->loadEvnPSEditForm($params);
				}
				break;
			case 'EvnPS':
				$obj = 'EvnPS'; $objKey = 'EvnPS_id';
				$this->load->model('EvnPS_model', 'EvnPS_model');
				$EvnPS = $this->EvnPS_model->loadEvnPSEditForm($params);
				break;
			default:
				$obj = ''; $objKey = '';
				$sourceObjData = null;
		}

		if ($obj == 'EvnPL') {
			$sourceObjData = (!empty($EvnPL) && !empty($EvnPL[0])) ? $EvnPL[0] : null;
		} elseif ($obj == 'EvnPS') {
			$sourceObjData = (!empty($EvnPS) && !empty($EvnPS[0])) ? $EvnPS[0] : null;
		}

		if (!empty($sourceObjData) && $obj == 'EvnPL') {
			$isClosed = ($sourceObjData['EvnPL_IsFinish'] == '2') ? true : false;
			if (in_array($sourceObjData['ResultClass_id'], ['1', '6'])) {
				$PersonRegisterOutCause_id = 3;
			}
		} elseif (!empty($sourceObjData) && $obj == 'EvnPS') {
			$isClosed = (!empty($sourceObjData['LeaveType_id'])) ? true : false;
			if (in_array($sourceObjData['LeaveType_id'], ['1', '2'])) {
				$PersonRegisterOutCause_id = 3;
			} elseif ($sourceObjData['LeaveType_id'] == '3') {
				$PersonRegisterOutCause_id = 1;
			}
		}

		if (!empty($sourceObjData)) {
			$diagList = $this->loadDiagList([$objKey => $sourceObjData[$objKey]]);
			$cviEventType = null;
			$suspicionCodes = ['Z20.8', 'Z03.8', 'Z11.5', 'Z22.8'];
			$confirmedCodes = ['U07.1', 'U07.2'];
			foreach ($diagList as $diag) {
				if (in_array($diag['Diag_Code'], $suspicionCodes) && $obj == 'EvnPL') {
					$cviEventType = 'suspicion';
				}
				if (in_array($diag['Diag_Code'], $confirmedCodes)) {
					$cviEventType = 'confirmed';
					break;
				}
			}

			$setDT = !empty($sourceObjData["{$obj}_setDT"]) ? date_create_from_format('d.m.Y H:i', $sourceObjData["{$obj}_setDT"]) : date_create();
			$disDT = !empty($sourceObjData["{$obj}_disDT"]) && $isClosed ? date_create_from_format('d.m.Y H:i', $sourceObjData["{$obj}_disDT"]) : null;
			$isConfirmed = $cviEventType == 'confirmed' ? 2 : null;
			if ($isConfirmed != 2) $PersonRegisterOutCause_id = null;

			$PersonRegister_id = $this->isExistObjectRecord('PersonRegister', [
				'Person_id' => $sourceObjData['Person_id'],
				'MorbusType_id' => 116
			]);
			$closedCVIRegistry = $this->findClosedCVI([
				'Evn_setDT' => $setDT,
				'Person_id' => $sourceObjData['Person_id']
			]);
			$params['Person_id'] = $sourceObjData['Person_id'];
			$lastCVI = $this->getLastCVI($params);

			if (!empty($closedCVIRegistry) && !empty($cviEventType)) {
				// Обнаружен закрытый случай КВИ с датой окончания менее чем 30 дней назад от даты начала текущего
				if (!empty($lastCVI) && $lastCVI['CVIRegistry_id'] != $closedCVIRegistry['CVIRegistry_id']) {
					$PersonRegisterOutCause_id = null;
				}
				$PersonRegister = $this->saveObject('PersonRegister', [
					'PersonRegister_id' => $PersonRegister_id,
					'PersonRegister_disDate' => empty($PersonRegisterOutCause_id) ? null : $disDT,
					'PersonRegisterOutCause_id' => $PersonRegisterOutCause_id,
					'pmUser_id' => $params['pmUser_id']
				]);
				$cviData = [
					'CVIRegistry_id' => $closedCVIRegistry['CVIRegistry_id'],
					'EvnPS_id' => null,
					'EvnPL_id' => null,
					'CVIRegistry_disDT' => $disDT,
					'CVIRegistry_deleted' => null,
					'CVIRegistry_delDT' => null,
					'pmUser_delID' => null,
					'CVIRegistry_isConfirmed' => $isConfirmed,
					'pmUser_id' => $params['pmUser_id']
				];
				$cviData[$objKey] = $sourceObjData[$objKey];
				$CVIRegistry = $this->saveObject('CVIRegistry', $cviData);
			} else {
				$CVIRegistry_id = $this->isExistObjectRecord('CVIRegistry', [$objKey => $sourceObjData[$objKey]]);
				if (!empty($lastCVI) && $lastCVI['CVIRegistry_id'] != $CVIRegistry_id) {
					$PersonRegisterOutCause_id = null;
				}

				if (!empty($cviEventType) || (empty($cviEventType) && !empty($CVIRegistry_id))) {
					if ($PersonRegister_id) {
						$cviCount = $this->getCVICount(['PersonRegister_id' => $PersonRegister_id]);
						$regData = [
							'PersonRegister_id' => $PersonRegister_id,
							'PersonRegister_disDate' => empty($PersonRegisterOutCause_id) ? null : $disDT,
							'PersonRegisterOutCause_id' => $PersonRegisterOutCause_id,
							'pmUser_id' => $params['pmUser_id']
						];
						if ($cviCount['count'] == 0) {
							$regData['PersonRegister_setDate'] = $setDT;
						}
						$PersonRegister = $this->saveObject('PersonRegister', $regData);
					} else {
						$PersonRegister = $this->execCommonSP('p_PersonRegister_ins', [
							'PersonRegister_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
							'Person_id' => $sourceObjData['Person_id'],
							'MorbusType_id' => 116,
							'PersonRegisterOutCause_id' => $PersonRegisterOutCause_id,
							'PersonRegister_setDate' => $setDT,
							'PersonRegister_disDate' => empty($PersonRegisterOutCause_id) ? null : $disDT,
							'pmUser_id' => $params['pmUser_id']
						], 'array_assoc');
					}
					if ($PersonRegister['PersonRegister_id'] && empty($PersonRegister['Error_Msg'])) {
						if (!$CVIRegistry_id) {
							$cviData = [
								'CVIRegistry_id' => ['value' => null, 'out' => true, 'type' => 'bigint'],
								'PersonRegister_id' => $PersonRegister['PersonRegister_id'],
								'Lpu_id' => $sourceObjData['Lpu_id'],
								'EvnPL_id' => null,
								'EvnPS_id' => null,
								'CVIRegistry_setDT' => $setDT,
								'CVIRegistry_disDT' => $disDT,
								'CVIRegistry_isConfirmed' => $isConfirmed,
								'pmUser_id' => $params['pmUser_id']
							];
							$cviData[$objKey] = $sourceObjData[$objKey];
							$CVIRegistry = $this->execCommonSP('p_CVIRegistry_ins', $cviData, 'array_assoc');
						} else {
							$CVIRegistry = $this->saveObject('CVIRegistry', [
								'CVIRegistry_id' => $CVIRegistry_id,
								'CVIRegistry_isConfirmed' => $isConfirmed,
								'CVIRegistry_disDT' => $disDT,
								'CVIRegistry_deleted' => null,
								'CVIRegistry_delDT' => null,
								'pmUser_delID' => null,
								'pmUser_id' => $params['pmUser_id']
							]);

							if (empty($cviEventType)) {
								$this->deleteCVI([
									'object' => $obj,
									$objKey => $sourceObjData[$objKey]
								]);
								$delParams = $params;
								$delParams['object'] = $obj;
								$delParams[$objKey] = $sourceObjData[$objKey];
								$this->deleteCVI($delParams);
							}
						}
					}
				}
			}
		}
	}

}
