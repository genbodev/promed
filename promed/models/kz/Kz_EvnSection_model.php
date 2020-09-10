<?php
/**
 * Class Kz_EvnSection_model
 */
require_once(APPPATH.'models/EvnSection_model.php');

class Kz_EvnSection_model extends EvnSection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes() {
		$arr = parent::defAttributes();
		$arr['paytypeersb_id']['save'] = 'trim';
		return $arr;
	}

	/**
	 * Получение системного наименования для вида оплаты "ОМС"
	 * @task https://redmine.swan.perm.ru/issues/39841
	 */
	function getPayTypeSysNickOMS() {
		return 'Resp';
	}

	/**
	 * поиск ксг/кпг/коэф
	 */
	function loadKSGKPGKOEF($data) {
		$KSGFromUsluga = false;
		$KSGFromDiag = false;

		if (empty($data['EvnSection_setDate'])) {
			$data['EvnSection_setDate'] = date('Y-m-d');
		}

		if (empty($data['EvnSection_disDate'])) {
			$data['EvnSection_disDate'] = $data['EvnSection_setDate'];
		}

		$query = "
			select
				dbo.Age2(PS.Person_BirthDay, :EvnSection_setDate) as Person_Age,
				datediff(day, PS.Person_BirthDay, :EvnSection_setDate) as Person_AgeDays,
				PS.Sex_id
			from
				v_PersonState PS (nolock)
			where
				Person_id = :Person_id
		";
		$result = $this->db->query($query, $data);

		if (is_object($result))
		{
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$data['Person_Age'] = $resp[0]['Person_Age'];
				$data['Person_AgeDays'] = $resp[0]['Person_AgeDays'];
				$data['Sex_id'] = $resp[0]['Sex_id'];
				$data['MesPayType_Code'] = ($resp[0]['Person_Age']<18)?8:7;
			} else {
				return array('Error_Msg' => 'Ошибка получения данных по человеку');
			}
		} else {
			return array('Error_Msg' => 'Ошибка получения данных по человеку');
		}

		$DiagSopList = array();
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select EDPS.Diag_id
				from v_EvnDiagPS EDPS with(nolock)
				inner join v_DiagSetClass DSC with(nolock) on DSC.DiagSetClass_id = EDPS.DiagSetClass_id
				where EDPS.EvnDiagPS_pid = :EvnSection_id and DSC.DiagSetClass_SysNick in ('osl','sop')
			";
			$resp = $this->queryResult($query, $data);
			if (!is_array($resp)) {
				return array('Error_Msg' => 'Ошибка получения сопутствующих диагнозов');
			}
			foreach($resp as $item) {
				$DiagSopList[] = $item['Diag_id'];
			}
		}

		$DiagNidFitler = "mu.Diag_nid is null";
		if (count($DiagSopList) > 0) {
			$DiagNidFitler = "(mu.Diag_nid is null or mu.Diag_nid in (".implode(",",$DiagSopList)."))";
		}

		// 1.	Пробуем определить КСГ по наличию услуги
		if (!empty($data['EvnSection_id'])) {
			$query = "
				select top 1
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					cast(mt.MesTariff_Value as float) as KOEF,
					mt.MesTariff_id,
					mu.MesOldUslugaComplex_id
				from v_EvnUsluga eu (nolock)
					inner join v_MesOldUslugaComplex mu with(nolock) on mu.UslugaComplex_id = eu.UslugaComplex_id
					inner join v_MesOld mo with(nolock) on mo.Mes_id = mu.Mes_id and mo.MesType_id IN (2,3,5) -- КСГ
					inner join v_MesTariff mt with(nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					inner join v_MesPayType mpt with(nolock) on mpt.MesPayType_id = mt.MesPayType_id
				where
					eu.EvnUsluga_pid = :EvnSection_id
					and {$DiagNidFitler}
					and mo.MesType_id = 2
					and mpt.MesPayType_Code = :MesPayType_Code
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					mt.MesTariff_Value desc
			";
			//echo getDebugSQL($query, $data);
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromUsluga = $resp[0];
				}
			}
		}

		// 2.	Пробуем определить КСГ по наличию диагноза
		if (!empty($data['Diag_id'])) {
			$query = "
				select top 1
					mo.Mes_Code + ISNULL('. ' + mo.Mes_Name, '') as KSG,
					mo.Mes_id,
					cast(mt.MesTariff_Value as float) as KOEF,
					mt.MesTariff_id,
					mu.MesOldUslugaComplex_id
				from v_MesOldUslugaComplex mu with(nolock)
					inner join v_MesOld mo with(nolock) on mo.Mes_id = mu.Mes_id
					inner join v_MesTariff mt with(nolock) on mt.Mes_id = mo.Mes_id -- Коэффициент КСГ
					inner join v_MesPayType mpt with(nolock) on mpt.MesPayType_id = mt.MesPayType_id
				where
					mu.Diag_id = :Diag_id
					and {$DiagNidFitler}
					and mo.MesType_id = 3
					and mpt.MesPayType_Code = :MesPayType_Code
					and mu.MesOldUslugaComplex_begDT <= :EvnSection_disDate
					and (IsNull(mu.MesOldUslugaComplex_endDT, :EvnSection_disDate) >= :EvnSection_disDate)
					and mo.Mes_begDT <= :EvnSection_disDate
					and (IsNull(mo.Mes_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
					and mt.MesTariff_begDT <= :EvnSection_disDate
					and (IsNull(mt.MesTariff_endDT, :EvnSection_disDate)>= :EvnSection_disDate)
				order by
					mt.MesTariff_Value desc
			";
			//echo getDebugSQL($query, $data);
			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');
				if (count($resp) > 0) {
					$KSGFromDiag = $resp[0];
				}
			}
		}

		$response = array('KSG' => '', 'KPG' => '', 'KOEF' => '', 'Mes_tid' => null, 'Mes_sid' => null, 'Mes_kid' => null, 'MesTariff_id' => null, 'MesOldUslugaComplex_id' => null, 'success' => true);

		if ($KSGFromUsluga && $KSGFromDiag) {
			$response['Mes_tid'] = $KSGFromDiag['Mes_id'];
			$response['Mes_sid'] = $KSGFromUsluga['Mes_id'];
			if ($KSGFromUsluga['KOEF'] > $KSGFromDiag['KOEF']) {
				$response['KSG'] = $KSGFromUsluga['KSG'];
				$response['KOEF'] = $KSGFromUsluga['KOEF'];
				$response['MesTariff_id'] = $KSGFromUsluga['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGFromUsluga['MesOldUslugaComplex_id'];
			} else {
				$response['KSG'] = $KSGFromDiag['KSG'];
				$response['KOEF'] = $KSGFromDiag['KOEF'];
				$response['MesTariff_id'] = $KSGFromDiag['MesTariff_id'];
				$response['MesOldUslugaComplex_id'] = $KSGFromDiag['MesOldUslugaComplex_id'];
			}
		} else if($KSGFromUsluga) {
			$response['Mes_sid'] = $KSGFromUsluga['Mes_id'];
			$response['KSG'] = $KSGFromUsluga['KSG'];
			$response['KOEF'] = $KSGFromUsluga['KOEF'];
			$response['MesTariff_id'] = $KSGFromUsluga['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGFromUsluga['MesOldUslugaComplex_id'];
		} else {
			$response['Mes_tid'] = $KSGFromDiag['Mes_id'];
			$response['KSG'] = $KSGFromDiag['KSG'];
			$response['KOEF'] = $KSGFromDiag['KOEF'];
			$response['MesTariff_id'] = $KSGFromDiag['MesTariff_id'];
			$response['MesOldUslugaComplex_id'] = $KSGFromDiag['MesOldUslugaComplex_id'];
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getLpuSectionPatientList($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionWard_id' => $data['object_value'],
			'date' => $data['date']
		);
		$filters = '';
		
		if ($data['object_value'] <= 0) {
			$filters .= ' and EvnSection.LpuSection_id = :LpuSection_id ';
		}

		switch ($data['object_value']) {
			case 0: //Вновь поступившие (присвоена палата)
				$filters .= ' and cast(EvnSection.EvnSection_setDate as DATE) = cast(:date as DATE)
							and gr.GetRoom_id is not null
				';
				$queryParams['date'] = $data['date'];
				break;

			case -1: //Без палаты and (EvnSection.EvnSection_disDate is null or cast(EvnSection.EvnSection_disDate as DATE) >= cast(:date as DATE))
				$filters .= ' and gr.GetRoom_id is null
						and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
						and EvnSection.EvnSection_disDate is null
				';
				break;

			case -2: //Вновь поступившие и без палаты
				$filters .= ' and cast(EvnSection.EvnSection_setDate as DATE) = cast(:date as DATE)
							and gr.GetRoom_id is null
							and EvnSection.EvnSection_disDate is null
				';
				$queryParams['date'] = $data['date'];
				break;

			case -3: //Все пациенты							and isnull(cast(EvnSection.EvnSection_disDate as DATE), dbo.tzGetDate()) >= cast(:date as DATE)
				$filters .= ' and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
							and EvnSection.EvnSection_disDate is null
				';
				$queryParams['date'] = $data['date'];
				break;

			case -4: //Выбывшие пациенты
				$filters .= '
							and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
							and EvnSection.EvnSection_disDate is not null
							and cast(EvnSection.EvnSection_disDate as DATE) >= cast(:date as DATE)
				';
				$queryParams['date'] = $data['date'];
				break;

			default: //Находящиеся в палате
				$filters .= ' and gr.GetRoom_id = :LpuSectionWard_id
							and cast(EvnSection.EvnSection_setDate as DATE) <= cast(:date as DATE)
							and EvnSection.EvnSection_disDate is null
				';
				$queryParams['date'] = $data['date'];
				$queryParams['LpuSectionWard_id'] = $data['object_value'];
				break;
		}

		if (!empty($data['filter_Person_F'])) {
			if (allowPersonEncrypHIV()) {
				$filters .= " and (Person.Person_SurName LIKE :Person_F or PEH.PersonEncrypHIV_Encryp LIKE :Person_F)";
			} else {
				$filters .= " and Person.Person_SurName LIKE :Person_F";
			}
			$queryParams['Person_F'] = $data['filter_Person_F'] . '%';
		}
		if (!empty($data['filter_Person_I'])) {
			$filters .= ' and Person.Person_FirName LIKE :Person_I';
			$queryParams['Person_I'] = $data['filter_Person_I'] . '%';
		}
		if (!empty($data['filter_Person_O'])) {
			$filters .= ' and Person.Person_SecName LIKE :Person_O';
			$queryParams['Person_O'] = $data['filter_Person_O'] . '%';
		}

		if (!empty($data['filter_MedStaffFact_id'])) {
			$filters .= ' and (MedStaffFact.MedStaffFact_id = :MedStaffFact_id or EvnSection.MedStaffFact_id = :MedStaffFact_id)';
			$queryParams['MedStaffFact_id'] = $data['filter_MedStaffFact_id'];
		}
		if (!empty($data['filter_Person_BirthDay'])) {
			$filters .= ' and cast(Person.Person_BirthDay as date) = cast(:Person_BirthDay as date)';
			$queryParams['Person_BirthDay'] = $data['filter_Person_BirthDay'];
		}
		$diagFilter = getAccessRightsDiagFilter('Diag.Diag_Code');
		if (!empty($diagFilter)) {
			$filters .= " and $diagFilter";
		}
		$allow_encryp = allowPersonEncrypHIV()?'1':'0';

		$query = "
			SELECT
				EvnSection.EvnSection_id,
				EvnSection.EvnSection_rid,
				EvnSection.LpuSection_id,
				Person.Sex_id,
				case when {$allow_encryp}=1 then PEH.PersonEncrypHIV_Encryp end as PersonEncrypHIV_Encryp,
				case when {$allow_encryp}=1 and PEH.PersonEncrypHIV_id is not null
					then PEH.PersonEncrypHIV_Encryp else NULLIF(ISNULL(Person.Person_SurName, '') + ISNULL(' ' + Person.Person_FirName, '') + ISNULL(' ' + Person.Person_SecName, ''), '')
				end as Person_Fio,
				convert(varchar(10), Person.Person_BirthDay, 104) as Person_BirthDay,
				dbo.Age2(Person.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				dbo.Age_newborn(Person.Person_BirthDay, dbo.tzGetDate()) as Person_AgeMonth,
				Diag.Diag_Code,
				Diag.Diag_Name,
				ISNULL(convert(varchar(10), EvnSection.EvnSection_setDate, 104), '') as EvnSection_setDate, 
				ISNULL(convert(varchar(10), EvnSection.EvnSection_disDate, 104), '') as EvnSection_disDate,
				ISNULL(convert(varchar(10), EvnSection.EvnSection_PlanDisDT, 104), '') as EvnSection_PlanDisDT,
				EvnSection.Person_id,
				EvnSection.Server_id,
				EvnSection.PersonEvn_id,
				EvnPS.EvnPS_NumCard,
				Mes.Mes_id,
				Mes.Mes_Code,
				ISNULL(Mes.Mes_KoikoDni, 0) as KoikoDni,
				EvnPS.EvnPS_id,
				LSW.LpuSectionWard_id,
				EvnSection.MedPersonal_id,
				EvnSection.PayType_id,
				MedStaffFact.Person_Fin as MedPersonal_Fin,
				datediff(\"d\", EvnSection.EvnSection_setDate, case when (EvnSection.EvnSection_disDate > dbo.tzGetDate()) then :date else isnull(EvnSection.EvnSection_disDate, :date) end) as EvnSecdni,
				isnull(ERP.EvnReanimatPeriod_id, 0) as EvnReanimatPeriod_id
			FROM
				v_EvnSection EvnSection with (nolock)
				left join r101.GetBedEvnLink gbel with (nolock) on gbel.Evn_id = EvnSection.EvnSection_id
				left join r101.GetBed gb (nolock) on gb.GetBed_id = gbel.GetBed_id
				left join r101.GetRoom gr (nolock) on gr.ID = gb.RoomID
				left join v_PersonState Person with (nolock) on Person.Person_id = EvnSection.Person_id
				LEFT JOIN v_Diag Diag with (nolock) on Diag.Diag_id = EvnSection.Diag_id
				LEFT JOIN v_EvnPS EvnPS with (nolock) on EvnPS.EvnPS_id = EvnSection.EvnSection_pid
				LEFT JOIN v_MesOld Mes with (nolock) on Mes.Mes_id = EvnSection.Mes_id
				LEFT JOIN v_MedStaffFact MedStaffFact with (nolock) on MedStaffFact.MedStaffFact_id = EvnSection.MedStaffFact_id and MedStaffFact.Lpu_id = EvnSection.Lpu_id --and MedStaffFact.LpuSection_id = EvnSection.LpuSection_id
				LEFT JOIN v_PersonEncrypHIV PEH with(nolock) on PEH.Person_id = Person.Person_id
				LEFT JOIN v_LpuSectionWard LSW with(nolock) on LSW.LpuSectionWard_id = EvnSection.LpuSectionWard_id
					and LSW.LpuSection_id = EvnSection.LpuSection_id
				left join v_EvnReanimatPeriod ERP with(nolock) on ERP.EvnReanimatPeriod_pid = EvnSection.EvnSection_id and ERP.EvnReanimatPeriod_disDT is null
			WHERE
				EvnSection.Lpu_id = :Lpu_id
				AND EvnPS.EvnPS_id is not null
				{$filters}
			ORDER BY 
				EvnSection.EvnSection_setDate desc
		";
		//echo getDebugSQL($query, $queryParams); die;
		return $this->queryResult($query, $queryParams);
	}
	
	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateLpuSectionWardId($id, $value = null, $curvalue = null) {
		
		$getbedevnlink_id = $this->getFirstResultFromQuery("select GetBedEvnLink_id from r101.GetBedEvnLink (nolock) where Evn_id = ?", [$id]);
		if ($value != null) {
			$proc = !$getbedevnlink_id ? 'r101.p_GetBedEvnLink_ins' : 'r101.p_GetBedEvnLink_upd';
			
			return $this->execCommonSP($proc, [
				'GetBedEvnLink_id' => $getbedevnlink_id ? $getbedevnlink_id : null,
				'Evn_id' => $id,
				'GetBed_id' => $value,
				'pmUser_id' => $this->promedUserId
			], 'array_assoc');
		} elseif ($getbedevnlink_id != false) {
			return $this->execCommonSP('r101.p_GetBedEvnLink_del', [
				'GetBedEvnLink_id' => $getbedevnlink_id
			], 'array_assoc');
		}
	}
}
