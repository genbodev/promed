<?php
class EvnDirectionHistologic_model extends swPgModel {
	private $_ignoreBiopsyStudyType = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	function deleteEvnDirectionHistologic($data) {
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnDirectionHistologic_del(
				EvnDirectionHistologic_id := :EvnDirectionHistologic_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление направления на патологогистологическое исследование)'));
		}
	}


	/**
	 * Получение полей
	 */
	function getEvnDirectionHistologicFields($data) {
		$query = "
			select
				coalesce(BO.BiopsyOrder_Code, 0) as \"BiopsyOrder_Code\",
				to_char(EDH.EvnDirectionHistologic_BiopsyDT, 'dd.mm.yyyy') as \"EvnDirectionHistologic_BiopsyDate\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Ser, '')) as \"EvnDirectionHistologic_Ser\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Num, '')) as \"EvnDirectionHistologic_Num\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_BiopsyNum, '')) as \"EvnDirectionHistologic_BiopsyNum\",
				coalesce(EDH.EvnDirectionHistologic_PredOperTreat, '') as \"EvnDirectionHistologic_PredOperTreat\",
				coalesce(EDH.EvnDirectionHistologic_ClinicalData, '') as \"EvnDirectionHistologic_ClinicalData\",
				coalesce(EDH.EvnDirectionHistologic_ClinicalDiag, '') as \"EvnDirectionHistologic_ClinicalDiag\",
				RTRIM(EDH.EvnDirectionHistologic_NumCard) as \"EvnDirectionHistologic_NumCard\",
				date_part('day', EDH.EvnDirectionHistologic_setDT) as \"EvnDirectionHistologic_Day\",
				date_part('HOUR', EDH.EvnDirectionHistologic_setDT) as \"EvnDirectionHistologic_Hour\",
				date_part('month', EDH.EvnDirectionHistologic_setDT) as \"EvnDirectionHistologic_Month\",
				date_part('year', EDH.EvnDirectionHistologic_setDT) as \"EvnDirectionHistologic_Year\",
				to_char(EDH.EvnDirectionHistologic_didDate, 'dd.mm.yyyy') as \"EvnDirectionHistologic_didDate\",
				coalesce(EDH.EvnDirectionHistologic_ObjectCount, 0) as \"EvnDirectionHistologic_ObjectCount\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Operation, '')) as \"EvnDirectionHistologic_Operation\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_SpecimenSaint, '')) as \"EvnDirectionHistologic_SpecimenSaint\",
				RTRIM(coalesce(Lpu.Lpu_Name, '')) as \"Lpu_Name\",
				RTRIM(coalesce(Lpu.Lpu_f003mcod, '')) as \"Lpu_FedCode\",
				RTRIM(coalesce(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				RTRIM(coalesce(MP.Person_Fio, '')) as \"MedPersonal_Fio\",
				RTRIM(coalesce(MP.Person_Snils, '')) as \"MedPersonal_Snils\",
				(date_part('year', dbo.tzGetDate() - PS.Person_BirthDay)
					- case when date_part('month', PS.Person_BirthDay) > date_part('month', dbo.tzGetDate()) or (date_part('month', PS.Person_BirthDay) = date_part('month', dbo.tzGetDate()) and date_part('day', PS.Person_BirthDay) > date_part('day', dbo.tzGetDate()))
					then 'interval 1 year' else 'interval 0 year' end
				) as \"Person_Age\",
				RTRIM(RTRIM(coalesce(PS.Person_Surname, '')) || ' ' || RTRIM(coalesce(PS.Person_Firname, '')) || ' ' || RTRIM(coalesce(PS.Person_Secname, ''))) as \"Person_Fio\",
				coalesce(Sex.Sex_Code, 0) as \"Sex_Code\"
			from v_EvnDirectionHistologic EDH
				inner join v_Lpu Lpu on Lpu.Lpu_id = EDH.Lpu_id
				inner join LpuSection LS on LS.LpuSection_id = EDH.LpuSection_did
				inner join v_MedPersonal MP on MP.MedPersonal_id = EDH.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				inner join v_PersonState PS on PS.Person_id = EDH.Person_id
				inner join Sex on Sex.Sex_id = PS.Sex_id
				left join BiopsyOrder BO on BO.BiopsyOrder_id = EDH.BiopsyOrder_id
			where EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				and EDH.Lpu_id = :Lpu_id
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Генерация номера
	 */
	function getEvnDirectionHistologicNumber($data) {
		$query = "
			select
				ObjectID as \"rnumber\"
			from xp_GenpmID(
				ObjectName := 'EvnDirectionHistologic',
				Lpu_id := :Lpu_id
			)
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение данных для редактирования
	 */
	function loadEvnDirectionHistologicEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			SELECT
				case when EDH.Lpu_id = :Lpu_id and EHP.EvnHistologicProto_id is null and coalesce(IsBad.YesNo_Code, 0) = 0 " . (count($med_personal_list)>0 ? "and EDH.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\",
				EDH.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				EDH.EvnDirectionHistologic_pid as \"EvnDirectionHistologic_pid\",
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				to_char(EDH.EvnDirectionHistologic_setDate, 'dd.mm.yyyy') as \"EvnDirectionHistologic_setDate\",
				to_char(EDH.EvnDirectionHistologic_setTime, 'HH24:MI') as \"EvnDirectionHistologic_setTime\",
				to_char(EDH.EvnDirectionHistologic_didDate, 'dd.mm.yyyy') as \"EvnDirectionHistologic_didDate\",
				to_char(EDH.EvnDirectionHistologic_didTime, 'HH24:MI') as \"EvnDirectionHistologic_didTime\",
				EDH.Person_id as \"Person_id\",
				EDH.Server_id as \"Server_id\",
				EDH.PersonEvn_id as \"PersonEvn_id\",
				EDH.MedPersonal_id as \"MedPersonal_id\",
				EDH.EvnDirectionHistologic_Ser as \"EvnDirectionHistologic_Ser\",
				EDH.EvnDirectionHistologic_Num as \"EvnDirectionHistologic_Num\",
				EDH.EvnDirectionHistologic_IsUrgent as \"EvnDirectionHistologic_IsUrgent\",
				EDH.LpuSection_did as \"LpuSection_did\",
				EDH.Lpu_id as \"Lpu_id\",
				EDH.Lpu_aid as \"Lpu_aid\",
				EDH.Lpu_sid as \"Lpu_sid\",
				EDH.EvnDirectionHistologic_NumCard as \"EvnDirectionHistologic_NumCard\",
				EDH.HistologicMaterial_id as \"HistologicMaterial_id\",
				EDH.BiopsyOrder_id as \"BiopsyOrder_id\",
				EDH.UslugaComplex_id as \"EDHUslugaComplex_id\",
				to_char(EDH.EvnDirectionHistologic_BiopsyDT, 'dd.mm.yyyy') as \" EvnDirectionHistologic_BiopsyDate\",
				EDH.EvnDirectionHistologic_BiopsyNum as \"EvnDirectionHistologic_BiopsyNum\",
				EDH.EvnDirectionHistologic_SpecimenSaint as \"EvnDirectionHistologic_SpecimenSaint\",
				EDH.EvnDirectionHistologic_Operation as \"EvnDirectionHistologic_Operation\",
				EDH.EvnDirectionHistologic_ObjectCount as \"EvnDirectionHistologic_ObjectCount\",
				EDH.EvnDirectionHistologic_PredOperTreat as \"EvnDirectionHistologic_PredOperTreat\",
				EDH.EvnDirectionHistologic_ClinicalData as \"EvnDirectionHistologic_ClinicalData\",
				EDH.EvnDirectionHistologic_ClinicalDiag as \"EvnDirectionHistologic_ClinicalDiag\",
				EDH.EvnDirectionHistologic_MedPersonalFIO as \"EvnDirectionHistologic_MedPersonalFIO\",
				EDH.EvnDirectionHistologic_LpuSectionName as \"EvnDirectionHistologic_LpuSectionName\",
				EDH.EvnPS_id as \"EvnPS_id\",
				EDH.Diag_id as \"Diag_id\",
				EDH.BiopsyReceive_id as \"BiopsyReceive_id\",
				EDH.EvnDirectionHistologic_IsPlaceSolFormalin as \"EvnDirectionHistologic_IsPlaceSolFormalin\",
				BSTL.BiopsyStudyType_ids as \"BiopsyStudyType_ids\",
				RTRIM(LTRIM(coalesce(pmUserCache.pmUser_Name, ''))) as \"pmUser_Name\"
			FROM
				v_EvnDirectionHistologic EDH
				left join YesNo IsBad on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
				left join pmUserCache on pmUserCache.pmUser_id = EDH.pmUser_pid
				left join lateral (
					select
						EvnHistologicProto_id
					from
						v_EvnHistologicProto
					where
						EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
					limit 1
				) EHP on true
				left join lateral (
					select
						string_agg(cast(BiopsyStudyType_id as varchar), ',') as BiopsyStudyType_ids
					from
						v_BiopsyStudyTypeLink
					where
						EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
				) BSTL on true
			WHERE (1 = 1)
				and EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				and (EDH.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
			limit 1
		";
		$result = $this->db->query($query, array(
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Получение данных для грида
	 */
	function loadEvnDirectionHistologicGrid($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$filter = "(EDH.Lpu_id = :Lpu_id OR EDH.Lpu_aid = :Lpu_id)";

		if ( isset($data['EvnDirectionHistologic_IsUrgent']) ) {
			$filter .= " and EDH.EvnDirectionHistologic_IsUrgent = :EvnDirectionHistologic_IsUrgent";
			$queryParams['EvnDirectionHistologic_IsUrgent'] = $data['EvnDirectionHistologic_IsUrgent'];
		}

		if ( isset($data['EvnDirectionHistologic_Num']) ) {
			$filter .= " and EDH.EvnDirectionHistologic_Num = :EvnDirectionHistologic_Num";
			$queryParams['EvnDirectionHistologic_Num'] = $data['EvnDirectionHistologic_Num'];
		}

		if ( isset($data['EvnDirectionHistologic_Ser']) ) {
			$filter .= " and EDH.EvnDirectionHistologic_Ser = :EvnDirectionHistologic_Ser";
			$queryParams['EvnDirectionHistologic_Ser'] = $data['EvnDirectionHistologic_Ser'];
		}

		if ( isset($data['begDate']) ) {
			$filter .= " and EDH.EvnDirectionHistologic_setDate >= cast(:begDate as timestamp)";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( isset($data['endDate']) ) {
			$filter .= " and EDH.EvnDirectionHistologic_setDate <= cast(:endDate as timestamp)";
			$queryParams['endDate'] = $data['endDate'];
		}

		if ( isset($data['Person_Firname']) ) {
			$filter .= " and PS.Person_Firname ilike :Person_Firname";
			$queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		if ( isset($data['Person_Secname']) ) {
			$filter .= " and PS.Person_Secname ilike :Person_Secname";
			$queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}

		if ( isset($data['Person_Surname']) ) {
			$filter .= " and PS.Person_Surname ilike :Person_Surname";
			$queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';
		}

		if ( isset($data['EvnType_id']) ) {
			switch ( $data['EvnType_id'] ) {
				case 2:
					$filter .= " and coalesce(IsBad.YesNo_Code, 0) = 0";
				break;

				case 3:
					$filter .= " and coalesce(IsBad.YesNo_Code, 0) = 1";
				break;
			}
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		foreach ($med_personal_list as $key => $value) {
			if (empty($value)) {
				unset($med_personal_list[$key]);
			}
		}

		$query = "
			select
				-- select
				case when EDH.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EDH.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as \"accessType\",
				EDH.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				EDH.Person_id as \"Person_id\",
				EDH.PersonEvn_id as \"PersonEvn_id\",
				EDH.Server_id as \"Server_id\",
				case when EHP.EvnHistologicProto_id is not null then 'true' else 'false' end as \"EvnDirectionHistologic_HasProto\",
				EDH.EvnDirectionHistologic_Ser as \"EvnDirectionHistologic_Ser\",
				EDH.EvnDirectionHistologic_Num as \"EvnDirectionHistologic_Num\",
				EDH.EvnDirectionHistologic_LpuSectionName as \"EvnDirectionHistologic_LpuSectionName\",
				EDH.EvnDirectionHistologic_MedPersonalFIO as \"EvnDirectionHistologic_MedPersonalFIO\",
				to_char(EDH.EvnDirectionHistologic_setDT, 'dd.mm.yyyy') as \"EvnDirectionHistologic_setDate\",
				RTRIM(LS.LpuSection_Name) as \"LpuSection_Name\",
				RTRIM(MP.Person_Fio) as \"MedPersonal_Fio\",
				EDH.EvnDirectionHistologic_NumCard as \"EvnDirectionHistologic_NumCard\",
				PS.Person_Surname as \"Person_Surname\",
				PS.Person_Firname as \"Person_Firname\",
				PS.Person_Secname as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				RTRIM(Lpu.Lpu_Nick) as \"Lpu_Name\",
				IsUrgent.YesNo_Name as \"EvnDirectionHistologic_IsUrgent\",
				IsBad.YesNo_Code as \"EvnDirectionHistologic_IsBad\"
				-- end select
			from
				-- from
				v_PersonState PS
				inner join v_EvnDirectionHistologic EDH on EDH.Person_id = PS.Person_id
				left join LpuSection LS on LS.LpuSection_id = EDH.LpuSection_did
				left join v_MedPersonal MP on MP.MedPersonal_id = EDH.MedPersonal_id
					and MP.Lpu_id = EDH.Lpu_id
				inner join v_Lpu Lpu on Lpu.Lpu_id = EDH.Lpu_aid
				inner join YesNo IsUrgent on IsUrgent.YesNo_id = EDH.EvnDirectionHistologic_IsUrgent
				left join YesNo IsBad on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
				left join lateral(
					select EvnHistologicProto_id
					from v_EvnHistologicProto
					where EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
					limit 1
				) EHP on true
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname
				-- end order by
		";

		// echo getDebugSQL($query, $queryParams); exit();

		if ( $data['start'] >= 0 && $data['limit'] >= 0 ) {
			$limit_query = getLimitSQLPH($query, $data['start'], $data['limit']);
			$result = $this->db->query($limit_query, $queryParams);
		}
		else {
			$result = $this->db->query($query, $queryParams);
		}

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( is_array($res) ) {
				if ( $data['start'] == 0 && count($res) < $data['limit'] ) {
					$response['data'] = $res;
					$response['totalCount'] = count($res);
				}
				else {
					$response['data'] = $res;
					$get_count_query = getCountSQLPH($query);
					$get_count_result = $this->db->query($get_count_query, $queryParams);

					if ( is_object($get_count_result) ) {
						$response['totalCount'] = $get_count_result->result('array');
						$response['totalCount'] = $response['totalCount'][0]['cnt'];
					}
					else {
						return false;
					}
				}
				foreach ($response['data'] as $index => $row) {
					$response['data'][$index]['EvnDirectionHistologic_MedPersonalFIO'] = htmlspecialchars($row['EvnDirectionHistologic_MedPersonalFIO'], ENT_QUOTES);
					$response['data'][$index]['EvnDirectionHistologic_LpuSectionName'] = htmlspecialchars($row['EvnDirectionHistologic_LpuSectionName'], ENT_QUOTES);
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}

		return $response;
	}


	/**
	 * Получение списка направлений
	 */
	function loadEvnDirectionHistologicList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EDH.Person_id = :Person_id";
		$queryParams['Person_id'] = $data['Person_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EDH.Lpu_aid = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				EDH.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Ser, '')) as \"EvnDirectionHistologic_Ser\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Num, '')) as \"EvnDirectionHistologic_Num\",
				to_char(EDH.EvnDirectionHistologic_setDT, 'dd.mm.yyyy') as \"EvnDirectionHistologic_setDate\"
			from v_EvnDirectionHistologic EDH
				left join YesNo IsBad on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
				left join lateral (
					select
						EvnHistologicProto_id
					from v_EvnHistologicProto
					where EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
					limit 1
				) EHP on true
			where " . $filter . "
				and EHP.EvnHistologicProto_id is null
				and coalesce(IsBad.YesNo_Code, 0) = 0
			order by
				EDH.EvnDirectionHistologic_setDT desc
		";
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}


	/**
	 * Сохранение
	 */
	public function saveEvnDirectionHistologic($data) {
		$response = array(array('Error_Msg' => ''));

		try {
			$this->beginTransaction();

			if ( !empty($data['EvnDirectionHistologic_didTime']) ) {
				$data['EvnDirectionHistologic_didDate'] .= ' ' . $data['EvnDirectionHistologic_didTime'];
			}

			if ( isset($data['EvnDirectionHistologic_setTime']) ) {
				$data['EvnDirectionHistologic_setDate'] .= ' ' . $data['EvnDirectionHistologic_setTime'];
			}

			$query = "
				select
					EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnDirectionHistologic_" . (!empty($data['EvnDirectionHistologic_id']) ? "upd" : "ins") . "(
					EvnDirectionHistologic_id := :EvnDirectionHistologic_id,
					params := :params,
					pmUser_id := :pmUser_id
				)
			";

			$jsonParams = array(
				'EvnDirectionHistologic_pid' => !empty($data['EvnDirectionHistologic_pid']) ? $data['EvnDirectionHistologic_pid'] : null,
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnDirectionHistologic_setDT' => $data['EvnDirectionHistologic_setDate'],
				'EvnDirectionHistologic_didDT' => $data['EvnDirectionHistologic_didDate'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'EvnDirectionHistologic_Ser' => $data['EvnDirectionHistologic_Ser'],
				'EvnDirectionHistologic_Num' => $data['EvnDirectionHistologic_Num'],
				'EvnDirectionHistologic_IsUrgent' => $data['EvnDirectionHistologic_IsUrgent'],
				'LpuSection_did' => $data['LpuSection_did'],
				'TimetableGraf_id' => $data['TimetableGraf_id'],
				'TimetableStac_id' => $data['TimetableStac_id'],
				'Lpu_aid' => $data['Lpu_aid'],
				'Lpu_sid' => !empty($data['Lpu_sid']) ? $data['Lpu_sid'] : NULL,
				'EvnDirectionHistologic_NumCard' => $data['EvnDirectionHistologic_NumCard'],
				'HistologicMaterial_id' => $data['HistologicMaterial_id'],
				'BiopsyOrder_id' => $data['BiopsyOrder_id'],
				'EvnDirectionHistologic_BiopsyDT' => $data['EvnDirectionHistologic_BiopsyDate'],
				'EvnDirectionHistologic_BiopsyNum' => $data['EvnDirectionHistologic_BiopsyNum'],
				'EvnDirectionHistologic_SpecimenSaint' => $data['EvnDirectionHistologic_SpecimenSaint'],
				'EvnDirectionHistologic_ObjectCount' => $data['EvnDirectionHistologic_ObjectCount'],
				'EvnDirectionHistologic_ClinicalData' => $data['EvnDirectionHistologic_ClinicalData'],
				'EvnDirectionHistologic_ClinicalDiag' => $data['EvnDirectionHistologic_ClinicalDiag'],
				'EvnDirectionHistologic_LpuSectionName' => !empty($data['EvnDirectionHistologic_LpuSectionName']) ? $data['EvnDirectionHistologic_LpuSectionName']: NULL,
				'EvnDirectionHistologic_MedPersonalFIO' => !empty($data['EvnDirectionHistologic_MedPersonalFIO']) ? $data['EvnDirectionHistologic_MedPersonalFIO']: NULL,
				'EvnDirectionHistologic_Operation' => $data['EvnDirectionHistologic_Operation'],
				'DirType_id' => 7,
				'UslugaComplex_id' => $data['EDHUslugaComplex_id'],
				'EvnDirectionHistologic_IsBad' => (!empty($data['EvnDirectionHistologic_IsBad']) ? $data['EvnDirectionHistologic_IsBad'] : NULL),
				'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
				'Diag_id' => (!empty($data['Diag_id']) ? $data['Diag_id'] : NULL),
				'BiopsyReceive_id' => (!empty($data['BiopsyReceive_id']) ? $data['BiopsyReceive_id'] : NULL),
				'EvnDirectionHistologic_PredOperTreat' => $data['EvnDirectionHistologic_PredOperTreat'],
				'EvnDirectionHistologic_IsPlaceSolFormalin' => $data['EvnDirectionHistologic_IsPlaceSolFormalin'],
				'pmUser_pid' => (!empty($data['pmUser_pid']) ? $data['pmUser_pid'] : NULL),
			);
			
			$queryParams = array(
				'EvnDirectionHistologic_id' => (!isset($data['EvnDirectionHistologic_id']) || $data['EvnDirectionHistologic_id'] <= 0 ? NULL : $data['EvnDirectionHistologic_id']),
				'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
				'pmUser_id' => $data['pmUser_id']
			);

			$response = $this->queryResult($query, $queryParams);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$EvnDirectionHistologic_id = $response[0]['EvnDirectionHistologic_id'];

			if ( $this->_ignoreBiopsyStudyType == false ) {
				$BiopsyStudyType_ids = $data['BiopsyStudyType_ids'];

				$resp = $this->queryResult("
					select
						BiopsyStudyTypeLink_id as \"BiopsyStudyTypeLink_id\",
						BiopsyStudyType_id as \"BiopsyStudyType_id\"
					from
						v_BiopsyStudyTypeLink
					where
						EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				", array(
					'EvnDirectionHistologic_id' => $EvnDirectionHistologic_id
				));

				$bstArray = array();
				if ( !empty($BiopsyStudyType_ids) && is_array($BiopsyStudyType_ids) ) {
					foreach ( $BiopsyStudyType_ids as $one ) {
						$bstArray[$one] = 1;
					}
				}

				foreach ( $resp as $respone ) {
					// удаляем лишние
					if ( !isset($bstArray[$respone['BiopsyStudyType_id']]) ) {
						$resp_del = $this->queryResult("
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from p_BiopsyStudyTypeLink_del(
								BiopsyStudyTypeLink_id := :BiopsyStudyTypeLink_id
							)
						", array(
							'BiopsyStudyTypeLink_id' => $respone['BiopsyStudyTypeLink_id']
						));

						if ( !empty($resp_del[0]['Error_Msg']) ) {
							throw new Exception($resp_del[0]['Error_Msg']);
						}
					}
					else {
						unset($bstArray[$respone['BiopsyStudyType_id']]);
					}
				}

				// добавляем новые
				foreach ( $bstArray as $BiopsyStudyType_id => $count ) {
					$resp_save = $this->queryResult("
						select
							BiopsyStudyTypeLink_id as \"BiopsyStudyTypeLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_BiopsyStudyTypeLink_ins(
							EvnDirectionHistologic_id := :EvnDirectionHistologic_id,
							BiopsyStudyType_id := :BiopsyStudyType_id,
							pmUser_id := :pmUser_id
						)
					", array(
						'BiopsyStudyType_id' => $BiopsyStudyType_id,
						'EvnDirectionHistologic_id' => $EvnDirectionHistologic_id,
						'pmUser_id' => $this->promedUserId
					));

					if ( !empty($resp_save[0]['Error_Msg']) ) {
						throw new Exception($resp_save[0]['Error_Msg']);
					}
				}
			}

			if ( isset($data['MarkingBiopsyData']) && is_array($data['MarkingBiopsyData']) ) {
				$this->load->model('MarkingBiopsy_model');

				foreach ( $data['MarkingBiopsyData'] as $row ) {
					if ( $row['RecordStatus_Code'] == 1 || empty($row['MarkingBiopsy_id']) ) {
						continue;
					}

					if ( $row['MarkingBiopsy_id'] < 0 ) {
						$row['MarkingBiopsy_id'] = null;
					}

					$row['EvnDirectionHistologic_id'] = $EvnDirectionHistologic_id;
					$row['pmUser_id'] = $data['pmUser_id'];

					switch ( $row['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->MarkingBiopsy_model->saveMarkingBiopsy($row);
						break;

						case 3:
							$queryResponse = $this->MarkingBiopsy_model->deleteMarkingBiopsy($row);
						break;
					}

					if ( !is_array($queryResponse) ) {
						throw new Exception('Ошибка при ' . ($row['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' маркировки материала');
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						throw new Exception($queryResponse[0]['Error_Msg']);
					}
				}
			}

			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}


	/**
	 * Устанавливает испорченность направления
	 */
	function setEvnDirectionHistologicIsBad($data) {
		$this->_ignoreBiopsyStudyType = true;

		$ret = array();
		$response = array(
			'success' => false,
			'Error_Msg' => ''
		);

		// Получаем данные по направлению
		$query = "
			select
				EDH.Org_sid as \"Org_sid\",
				EDH.EvnDirectionHistologic_LawDocumentDate as \"EvnDirectionHistologic_LawDocumentDate\",
				EDH.EvnDirectionHistologic_Descr as \"EvnDirectionHistologic_Descr\",
				EDH.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				EDH.Lpu_id as \"Lpu_id\",
				EDH.Server_id as \"Server_id\",
				EDH.PersonEvn_id as \"PersonEvn_id\",
				EDH.TimetableGraf_id as \"TimetableGraf_id\",
				EDH.TimetableStac_id as \"TimetableStac_id\",
				to_char(EDH.EvnDirectionHistologic_setDT, 'yyyy-mm-dd') as \"EvnDirectionHistologic_setDate\",
				coalesce(to_char(EDH.EvnDirectionHistologic_setTime, 'HH24:MI:SS'), '') as \"EvnDirectionHistologic_setTime\",
				to_char(EDH.EvnDirectionHistologic_didDT, 'yyyy-mm-dd') as \"EvnDirectionHistologic_didDate\",
				coalesce(to_char(EDH.EvnDirectionHistologic_didTime, 'HH24:MI:SS'), '') as \"EvnDirectionHistologic_didTime\",
				EDH.MedPersonal_id as \"MedPersonal_id\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Ser, '')) as \"EvnDirectionHistologic_Ser\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_Num, '')) as \"EvnDirectionHistologic_Num\",
				EDH.EvnDirectionHistologic_IsUrgent as \"EvnDirectionHistologic_IsUrgent\",
				EDH.LpuSection_did as \"LpuSection_did\",
				EDH.Lpu_aid as \"Lpu_aid\",
				EDH.EvnDirectionHistologic_NumCard as \"EvnDirectionHistologic_NumCard\",
				EDH.HistologicMaterial_id as \"HistologicMaterial_id\",
				EDH.BiopsyOrder_id as \"BiopsyOrder_id\",
				to_char(EDH.EvnDirectionHistologic_BiopsyDT, 'yyyy-mm-dd') as \"EvnDirectionHistologic_BiopsyDate\",
				EDH.EvnDirectionHistologic_BiopsyNum as \"EvnDirectionHistologic_BiopsyNum\",
				EDH.EvnDirectionHistologic_SpecimenSaint as \"EvnDirectionHistologic_SpecimenSaint\",
				EDH.EvnDirectionHistologic_ObjectCount as \"EvnDirectionHistologic_ObjectCount\",
				EDH.EvnDirectionHistologic_ClinicalData as \"EvnDirectionHistologic_ClinicalData\",
				EDH.EvnDirectionHistologic_ClinicalDiag as \"EvnDirectionHistologic_ClinicalDiag\",
				EDH.EvnDirectionHistologic_Operation as \"EvnDirectionHistologic_Operation\",
				EDH.UslugaComplex_id as \"EDHUslugaComplex_id\",
				EDH.EvnPS_id as \"EvnPS_id\",
				EDH.Diag_id as \"Diag_id\",
				EDH.BiopsyReceive_id as \"BiopsyReceive_id\",
				EDH.EvnDirectionHistologic_PredOperTreat as \"EvnDirectionHistologic_PredOperTreat\",
				EDH.EvnDirectionHistologic_IsPlaceSolFormalin as \"EvnDirectionHistologic_IsPlaceSolFormalin\",
				coalesce(EDH.pmUser_updID, EDH.pmUser_insID) as \"pmUser_id\"
			from v_EvnDirectionHistologic EDH
			where EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				and EDH.Lpu_id = :Lpu_id
				and coalesce(EDH.EvnDirectionHistologic_IsBad, 1) <> :EvnDirectionHistologic_IsBad
			limit 1
		";

		$queryParams = array(
			'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
			'EvnDirectionHistologic_IsBad' => $data['EvnDirectionHistologic_IsBad'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение данных о направлении)';
			$ret[0] = $response;
			return $ret;
		}

		$res = $result->result('array');

		if ( !is_array($res) || count($res) == 0 ) {
			$response['Error_Msg'] = 'Ошибка при получении данных о направлении';
			$ret[0] = $response;
			return $ret;
		}

		$res[0]['EvnDirectionHistologic_IsBad'] = $data['EvnDirectionHistologic_IsBad'];
		$res[0]['pmUser_pid'] = $data['pmUser_pid'];

		try { // сохраняем признак аннулирования и причину аннулирования
			$this->beginTransaction();

			$response = $this->saveEvnDirectionHistologic($res[0]);
			if ( !is_array($res) || count($res) == 0 ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных (снятие/установка признака испорченного протокола)');
			}
			if($data['EvnDirectionHistologic_IsBad'] != 0) {
				$query =  "
					select
						EvnStatusHistory_id as \"EvnStatusHistory_id\",
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\"
					from p_EvnStatusHistory_ins(
						Evn_id := :EvnDirectionHistologic_id,
						EvnStatus_id := 12,	-- отменено
						EvnStatusHistory_begDate := dbo.tzGetDate(),
						EvnStatusCause_id := :EvnStatusCause_id,
						EvnStatusHistory_Cause := :EvnStatusHistory_Cause,
						pmUser_id := :pmUser_id
					)
				";

				$queryParams = array(
					'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
					'EvnStatusCause_id' => $data['EvnStatusCause_id'],
					'EvnStatusHistory_Cause' => null,
					'pmUser_id' => $data['pmUser_id']
				);
				if(!empty($data['EvnStatusHistory_Cause'])) {
					$queryParams['EvnStatusHistory_Cause'] = $data['EvnStatusHistory_Cause'];
				}
				$res = $this->db->query($query, $queryParams);
				if(!is_object($res)) {
					throw new Exception('Ошибка при выполнении запроса к базе данных (сохранение причины аннулирования)');
				} 
				$result = $res->result('array');
				
				if(!empty($result['Error_Code'])) {
					throw new Exception($result['Error_Msg']);	
				}
			}
			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$response = array(array('success' => false, 'Error_Msg' => $e->getMessage()));
			$this->rollbackTransaction();
		}

		
		if(!isset($response['success'])) {
			$response['success'] = true;
		}

		return $response;
	}


	/**
	 * Запись профиля койки
	 * @param bool $isAllowTransaction Флаг необходимости транзакции
	 * @return array
	 */
	function loadEDHUslugaComplexCombo($data) {

		$query = "
			select
				UC.UslugaComplex_id as \"UslugaComplex_id\",
				UC.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC.UslugaComplex_Code || ' ' || UC.UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_UslugaComplex UC
				inner join v_EvnUslugaCommon EUC on EUC.UslugaComplex_id = UC.UslugaComplex_id
			where
				EUC.EvnUslugaCommon_rid = :EvnPS_id

			union all

			select
				UC2.UslugaComplex_id as \"UslugaComplex_id\",
				UC2.UslugaComplex_Code as \"UslugaComplex_Code\",
				UC2.UslugaComplex_Code || ' ' || UC2.UslugaComplex_Name as \"UslugaComplex_Name\"
			from
				v_UslugaComplex UC2
				inner join v_EvnUslugaOper EUO on EUO.UslugaComplex_id = UC2.UslugaComplex_id
			where
				EUO.EvnUslugaOper_rid = :EvnPS_id

		";

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
