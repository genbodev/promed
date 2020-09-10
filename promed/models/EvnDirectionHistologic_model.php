<?php
class EvnDirectionHistologic_model extends swModel {
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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirectionHistologic_del
				@EvnDirectionHistologic_id = :EvnDirectionHistologic_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				ISNULL(BO.BiopsyOrder_Code, 0) as BiopsyOrder_Code,
				convert(varchar(10), EDH.EvnDirectionHistologic_BiopsyDT, 104) as EvnDirectionHistologic_BiopsyDate,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Ser, '')) as EvnDirectionHistologic_Ser,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Num, '')) as EvnDirectionHistologic_Num,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_BiopsyNum, '')) as EvnDirectionHistologic_BiopsyNum,
				ISNULL(EDH.EvnDirectionHistologic_PredOperTreat, '') as EvnDirectionHistologic_PredOperTreat,
				ISNULL(EDH.EvnDirectionHistologic_ClinicalData, '') as EvnDirectionHistologic_ClinicalData,
				ISNULL(EDH.EvnDirectionHistologic_ClinicalDiag, '') as EvnDirectionHistologic_ClinicalDiag,
				RTRIM(EDH.EvnDirectionHistologic_NumCard) as EvnDirectionHistologic_NumCard,
				DAY(EDH.EvnDirectionHistologic_setDT) as EvnDirectionHistologic_Day,
				DATENAME(HOUR, EDH.EvnDirectionHistologic_setDT) as EvnDirectionHistologic_Hour,
				MONTH(EDH.EvnDirectionHistologic_setDT) as EvnDirectionHistologic_Month,
				YEAR(EDH.EvnDirectionHistologic_setDT) as EvnDirectionHistologic_Year,
				convert(varchar(10), EDH.EvnDirectionHistologic_didDate, 104) as EvnDirectionHistologic_didDate,
				ISNULL(EDH.EvnDirectionHistologic_ObjectCount, 0) as EvnDirectionHistologic_ObjectCount,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Operation, '')) as EvnDirectionHistologic_Operation,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_SpecimenSaint, '')) as EvnDirectionHistologic_SpecimenSaint,
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(Lpu.Lpu_f003mcod, '')) as Lpu_FedCode,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(MP.Person_Snils, '')) as MedPersonal_Snils,
				(datediff(year, PS.Person_BirthDay, dbo.tzGetDate())
					+ case when month(PS.Person_BirthDay) > month(dbo.tzGetDate()) or (month(PS.Person_BirthDay) = month(dbo.tzGetDate()) and day(PS.Person_BirthDay) > day(dbo.tzGetDate()))
					then -1 else 0 end
				) as Person_Age,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				ISNULL(Sex.Sex_Code, 0) as Sex_Code
			from v_EvnDirectionHistologic EDH with (nolock)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDH.Lpu_id
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = EDH.LpuSection_did
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDH.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = EDH.Person_id
				inner join Sex with (nolock) on Sex.Sex_id = PS.Sex_id
				left join BiopsyOrder BO with (nolock) on BO.BiopsyOrder_id = EDH.BiopsyOrder_id
			where EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				and EDH.Lpu_id = :Lpu_id
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
			declare @GenId bigint;
			exec xp_GenpmID
				@ObjectName = 'EvnDirectionHistologic',
				@Lpu_id = :Lpu_id,
				@ObjectID = @GenId output;
			select @GenId as rnumber;
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
			SELECT TOP 1
			    convert(varchar(10), EDH.EvnDirectionHistologic_LawDocumentDate, 104) as EvnDirectionHistologic_LawDocumentDate,
				EDH.Org_sid,
				EDH.EvnDirectionHistologic_Descr,
				case when EDH.Lpu_id = :Lpu_id and EHP.EvnHistologicProto_id is null and ISNULL(IsBad.YesNo_Code, 0) = 0 " . (count($med_personal_list)>0 ? "and EDH.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EDH.EvnDirectionHistologic_id,
				EDH.EvnDirectionHistologic_pid,
				EHP.EvnHistologicProto_id,
				convert(varchar(10), EDH.EvnDirectionHistologic_setDate, 104) as EvnDirectionHistologic_setDate,
				EDH.EvnDirectionHistologic_setTime,
				convert(varchar(10), EDH.EvnDirectionHistologic_didDate, 104) as EvnDirectionHistologic_didDate,
				EDH.EvnDirectionHistologic_didTime,
				EDH.Person_id,
				EDH.Server_id,
				EDH.PersonEvn_id,
				EDH.MedPersonal_id,
				EDH.EvnDirectionHistologic_Ser,
				EDH.EvnDirectionHistologic_Num,
				EDH.EvnDirectionHistologic_IsUrgent,
				EDH.LpuSection_did,
				EDH.Lpu_id,
				EDH.Lpu_aid,
				EDH.Lpu_sid,
				EDH.EvnDirectionHistologic_NumCard,
				EDH.HistologicMaterial_id,
				EDH.BiopsyOrder_id,
				EDH.UslugaComplex_id as EDHUslugaComplex_id,
				convert(varchar(10), EDH.EvnDirectionHistologic_BiopsyDT, 104) as  EvnDirectionHistologic_BiopsyDate,
				EDH.EvnDirectionHistologic_BiopsyNum,
				EDH.EvnDirectionHistologic_SpecimenSaint,
				EDH.EvnDirectionHistologic_Operation,
				EDH.EvnDirectionHistologic_ObjectCount,
				EDH.EvnDirectionHistologic_PredOperTreat,
				EDH.EvnDirectionHistologic_ClinicalData,
				EDH.EvnDirectionHistologic_ClinicalDiag,
				EDH.EvnDirectionHistologic_MedPersonalFIO,
				EDH.EvnDirectionHistologic_LpuSectionName,
				EDH.EvnPS_id,
				EDH.Diag_id,
				EDH.BiopsyReceive_id,
				EDH.EvnDirectionHistologic_IsPlaceSolFormalin,
				BSTL.BiopsyStudyType_ids,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name
			FROM
				v_EvnDirectionHistologic EDH with (nolock)
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
				left join pmUserCache with (nolock) on pmUserCache.pmUser_id = EDH.pmUser_pid
				outer apply (
					select top 1
						EvnHistologicProto_id
					from
						v_EvnHistologicProto with (nolock)
					where
						EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
				) EHP
				outer apply (
					SELECT STUFF(
					(
						select
							',' + cast(BiopsyStudyType_id as varchar)
						from
							v_BiopsyStudyTypeLink (nolock)
						where
							EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
						FOR XML PATH ('')
					), 1, 1, '') as BiopsyStudyType_ids
				) BSTL
			WHERE (1 = 1)
				and EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				and (EDH.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
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
			$filter .= " and EDH.EvnDirectionHistologic_setDate >= cast(:begDate as datetime)";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( isset($data['endDate']) ) {
			$filter .= " and EDH.EvnDirectionHistologic_setDate <= cast(:endDate as datetime)";
			$queryParams['endDate'] = $data['endDate'];
		}

		if ( isset($data['Person_Firname']) ) {
			$filter .= " and PS.Person_Firname like :Person_Firname";
			$queryParams['Person_Firname'] = rtrim($data['Person_Firname']) . '%';
		}

		if ( isset($data['Person_Secname']) ) {
			$filter .= " and PS.Person_Secname like :Person_Secname";
			$queryParams['Person_Secname'] = rtrim($data['Person_Secname']) . '%';
		}

		if ( isset($data['Person_Surname']) ) {
			$filter .= " and PS.Person_Surname like :Person_Surname";
			$queryParams['Person_Surname'] = rtrim($data['Person_Surname']) . '%';
		}

		if ( isset($data['EvnType_id']) ) {
			switch ( $data['EvnType_id'] ) {
				case 2:
					$filter .= " and ISNULL(IsBad.YesNo_Code, 0) = 0";
				break;

				case 3:
					$filter .= " and ISNULL(IsBad.YesNo_Code, 0) = 1";
				break;
			}
		}

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select
				-- select
				case when EDH.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EDH.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EDH.EvnDirectionHistologic_id,
				EHP.EvnHistologicProto_id,
				EDH.Person_id,
				EDH.PersonEvn_id,
				EDH.Server_id,
				case when EHP.EvnHistologicProto_id is not null then 'true' else 'false' end as EvnDirectionHistologic_HasProto,
				EDH.EvnDirectionHistologic_Ser,
				EDH.EvnDirectionHistologic_Num,
				EDH.EvnDirectionHistologic_LpuSectionName,
				EDH.EvnDirectionHistologic_MedPersonalFIO,
				convert(varchar(10), EDH.EvnDirectionHistologic_setDT, 104) as EvnDirectionHistologic_setDate,
				RTRIM(LS.LpuSection_Name) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EDH.EvnDirectionHistologic_NumCard,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				IsUrgent.YesNo_Name as EvnDirectionHistologic_IsUrgent,
				IsBad.YesNo_Code as EvnDirectionHistologic_IsBad
				-- end select
			from
				-- from
				v_PersonState PS with (nolock)
				inner join v_EvnDirectionHistologic EDH with (nolock) on EDH.Person_id = PS.Person_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EDH.LpuSection_did
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDH.MedPersonal_id
					and MP.Lpu_id = EDH.Lpu_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDH.Lpu_aid
				inner join YesNo IsUrgent with (nolock) on IsUrgent.YesNo_id = EDH.EvnDirectionHistologic_IsUrgent
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
				outer apply(
					select top 1 EvnHistologicProto_id
					from v_EvnHistologicProto with (nolock)
					where EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
				) EHP
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
				EDH.EvnDirectionHistologic_id,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Ser, '')) as EvnDirectionHistologic_Ser,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Num, '')) as EvnDirectionHistologic_Num,
				convert(varchar(10), EDH.EvnDirectionHistologic_setDT, 104) as EvnDirectionHistologic_setDate
			from v_EvnDirectionHistologic EDH with (nolock)
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDH.EvnDirectionHistologic_IsBad
				outer apply (
					select top 1
						EvnHistologicProto_id
					from v_EvnHistologicProto with (nolock)
					where EvnDirectionHistologic_id = EDH.EvnDirectionHistologic_id
				) EHP
			where " . $filter . "
				and EHP.EvnHistologicProto_id is null
				and ISNULL(IsBad.YesNo_Code, 0) = 0
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
				$data['EvnDirectionHistologic_didDate'] .= ' ' . $data['EvnDirectionHistologic_didTime'] . ':00.000';
			}

			if ( isset($data['EvnDirectionHistologic_setTime']) ) {
				$data['EvnDirectionHistologic_setDate'] .= ' ' . $data['EvnDirectionHistologic_setTime'] . ':00.000';
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnDirectionHistologic_id;
				exec p_EvnDirectionHistologic_" . (!empty($data['EvnDirectionHistologic_id']) ? "upd" : "ins") . "
					@EvnDirectionHistologic_id = @Res output,
					@Org_sid = :Org_sid,
			        @EvnDirectionHistologic_LawDocumentDate = :EvnDirectionHistologic_LawDocumentDate,
			        @EvnDirectionHistologic_Descr = :EvnDirectionHistologic_Descr,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnDirectionHistologic_setDT = :EvnDirectionHistologic_setDT,
					@EvnDirectionHistologic_didDT = :EvnDirectionHistologic_didDT,
					@MedPersonal_id = :MedPersonal_id,
					@EvnDirectionHistologic_Ser = :EvnDirectionHistologic_Ser,
					@EvnDirectionHistologic_Num = :EvnDirectionHistologic_Num,
					@EvnDirectionHistologic_IsUrgent = :EvnDirectionHistologic_IsUrgent,
					@LpuSection_did = :LpuSection_did,
					@TimetableGraf_id = :TimetableGraf_id,
					@TimetableStac_id = :TimetableStac_id,
					@Lpu_aid = :Lpu_aid,
					@Lpu_sid = :Lpu_sid,
					@EvnDirectionHistologic_NumCard = :EvnDirectionHistologic_NumCard,
					@HistologicMaterial_id = :HistologicMaterial_id,
					@BiopsyOrder_id = :BiopsyOrder_id,
					@EvnDirectionHistologic_BiopsyDT = :EvnDirectionHistologic_BiopsyDT,
					@EvnDirectionHistologic_BiopsyNum = :EvnDirectionHistologic_BiopsyNum,
					@EvnDirectionHistologic_SpecimenSaint = :EvnDirectionHistologic_SpecimenSaint,
					@EvnDirectionHistologic_ObjectCount = :EvnDirectionHistologic_ObjectCount,
					@EvnDirectionHistologic_ClinicalData = :EvnDirectionHistologic_ClinicalData,
					@EvnDirectionHistologic_ClinicalDiag = :EvnDirectionHistologic_ClinicalDiag,
					@EvnDirectionHistologic_Operation = :EvnDirectionHistologic_Operation,
					@EvnDirectionHistologic_IsBad = :EvnDirectionHistologic_IsBad,
					@EvnDirectionHistologic_pid = :EvnDirectionHistologic_pid,
					@DirType_id = 7,
					@UslugaComplex_id = :EDHUslugaComplex_id,
					@EvnPS_id = :EvnPS_id,
					@Diag_id = :Diag_id,
					@BiopsyReceive_id = :BiopsyReceive_id,
					@EvnDirectionHistologic_LpuSectionName = :EvnDirectionHistologic_LpuSectionName,
					@EvnDirectionHistologic_MedPersonalFIO = :EvnDirectionHistologic_MedPersonalFIO,
					@EvnDirectionHistologic_PredOperTreat = :EvnDirectionHistologic_PredOperTreat,
					@EvnDirectionHistologic_IsPlaceSolFormalin = :EvnDirectionHistologic_IsPlaceSolFormalin,
					@pmUser_pid = :pmUser_pid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnDirectionHistologic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
                'Org_sid' => $data['Org_sid'],
                'EvnDirectionHistologic_LawDocumentDate' => $data['EvnDirectionHistologic_LawDocumentDate'],
                'EvnDirectionHistologic_Descr' => $data['EvnDirectionHistologic_Descr'],
				'EvnDirectionHistologic_id' => (!isset($data['EvnDirectionHistologic_id']) || $data['EvnDirectionHistologic_id'] <= 0 ? NULL : $data['EvnDirectionHistologic_id']),
				'EvnDirectionHistologic_pid' => (isset($data['EvnDirectionHistologic_pid']) && $data['EvnDirectionHistologic_pid']) ? $data['EvnDirectionHistologic_pid'] : null,
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
				'EDHUslugaComplex_id' => $data['EDHUslugaComplex_id'],
				'EvnDirectionHistologic_IsBad' => (!empty($data['EvnDirectionHistologic_IsBad']) ? $data['EvnDirectionHistologic_IsBad'] : NULL),
				'EvnPS_id' => (!empty($data['EvnPS_id']) ? $data['EvnPS_id'] : NULL),
				'Diag_id' => (!empty($data['Diag_id']) ? $data['Diag_id'] : NULL),
				'BiopsyReceive_id' => (!empty($data['BiopsyReceive_id']) ? $data['BiopsyReceive_id'] : NULL),
				'EvnDirectionHistologic_PredOperTreat' => $data['EvnDirectionHistologic_PredOperTreat'],
				'EvnDirectionHistologic_IsPlaceSolFormalin' => $data['EvnDirectionHistologic_IsPlaceSolFormalin'],
				'pmUser_pid' => (!empty($data['pmUser_pid']) ? $data['pmUser_pid'] : NULL),
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
						BiopsyStudyTypeLink_id,
						BiopsyStudyType_id
					from
						v_BiopsyStudyTypeLink (nolock)
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
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
				
							exec p_BiopsyStudyTypeLink_del
								@BiopsyStudyTypeLink_id = :BiopsyStudyTypeLink_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
				
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
						declare
							@ErrCode int,
							@ErrMessage varchar(4000),
							@Res bigint;
			
						exec p_BiopsyStudyTypeLink_ins
							@BiopsyStudyTypeLink_id = @Res output,
							@EvnDirectionHistologic_id = :EvnDirectionHistologic_id,
							@BiopsyStudyType_id = :BiopsyStudyType_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @Res as BiopsyStudyTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			select top 1
				EDH.Org_sid,
				EDH.EvnDirectionHistologic_LawDocumentDate,
				EDH.EvnDirectionHistologic_Descr,
				EDH.EvnDirectionHistologic_id,
				EDH.Lpu_id,
				EDH.Server_id,
				EDH.PersonEvn_id,
				EDH.TimetableGraf_id,
				EDH.TimetableStac_id,
				convert(varchar(10), EDH.EvnDirectionHistologic_setDT, 120) as EvnDirectionHistologic_setDate,
				ISNULL(EDH.EvnDirectionHistologic_setTime, '') as EvnDirectionHistologic_setTime,
				convert(varchar(10), EDH.EvnDirectionHistologic_didDT, 120) as EvnDirectionHistologic_didDate,
				EDH.EvnDirectionHistologic_didTime,
				EDH.MedPersonal_id,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Ser, '')) as EvnDirectionHistologic_Ser,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_Num, '')) as EvnDirectionHistologic_Num,
				EDH.EvnDirectionHistologic_IsUrgent,
				EDH.LpuSection_did,
				EDH.Lpu_aid,
				EDH.EvnDirectionHistologic_NumCard,
				EDH.HistologicMaterial_id,
				EDH.BiopsyOrder_id,
				convert(varchar(10), EDH.EvnDirectionHistologic_BiopsyDT, 120) as EvnDirectionHistologic_BiopsyDate,
				EDH.EvnDirectionHistologic_BiopsyNum,
				EDH.EvnDirectionHistologic_SpecimenSaint,
				EDH.EvnDirectionHistologic_ObjectCount,
				EDH.EvnDirectionHistologic_ClinicalData,
				EDH.EvnDirectionHistologic_ClinicalDiag,
				EDH.EvnDirectionHistologic_Operation,
				EDH.UslugaComplex_id as EDHUslugaComplex_id,
				EDH.EvnPS_id,
				EDH.Diag_id,
				EDH.BiopsyReceive_id,
				EDH.EvnDirectionHistologic_PredOperTreat,
				EDH.EvnDirectionHistologic_IsPlaceSolFormalin,
				ISNULL(EDH.pmUser_updID, EDH.pmUser_insID) as pmUser_id
			from v_EvnDirectionHistologic EDH with (nolock)
			where EDH.EvnDirectionHistologic_id = :EvnDirectionHistologic_id
				and EDH.Lpu_id = :Lpu_id
				and ISNULL(EDH.EvnDirectionHistologic_IsBad, 1) <> :EvnDirectionHistologic_IsBad
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
					declare
						@Res bigint,
						@ErrCode int,
						@date datetime = dbo.tzGetDate(),
						@ErrMessage varchar(4000);

					exec p_EvnStatusHistory_ins
						@EvnStatusHistory_id = @res output,
						@Evn_id = :EvnDirectionHistologic_id,
						@EvnStatus_id = 12,					-- отменено
						@EvnStatusHistory_begDate = @date,
						@EvnStatusCause_id = :EvnStatusCause_id,
						@EvnStatusHistory_Cause = :EvnStatusHistory_Cause,
						@pmUser_id = :pmUser_id,

						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as EvnStatusHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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

		if(in_array(getRegionNick(), array('kareliya','krym','khak'))){

			$query = "
				select
					UC.UslugaComplex_id,
					UC.UslugaComplex_Code,
					UC.UslugaComplex_Code + ' ' + UC.UslugaComplex_Name as UslugaComplex_Name,
					UC.UslugaCategory_id,
					UCategory.UslugaCategory_SysNick
				from
					v_UslugaComplex UC (nolock)
					inner join v_UslugaComplexAttribute uca on uca.UslugaComplex_id = uc.UslugaComplex_id
					inner join v_UslugaComplexAttributeType ucat on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					inner join v_UslugaCategory UCategory with (nolock) on UCategory.UslugaCategory_id = UC.UslugaCategory_id
				where
					ucat.UslugaComplexAttributeType_SysNick = 'gistissl'
			";

		}else {

			$query = "
				select
					UC.UslugaComplex_id,
					UC.UslugaComplex_Code,
					UC.UslugaComplex_Code + ' ' + UC.UslugaComplex_Name as UslugaComplex_Name
				from
					v_UslugaComplex UC (nolock)
					inner join v_EvnUslugaCommon EUC with (nolock) on EUC.UslugaComplex_id = UC.UslugaComplex_id
				where
					EUC.EvnUslugaCommon_rid = :EvnPS_id
	
				union all
	
				select
					UC2.UslugaComplex_id,
					UC2.UslugaComplex_Code,
					UC2.UslugaComplex_Code + ' ' + UC2.UslugaComplex_Name as UslugaComplex_Name
				from
					v_UslugaComplex UC2 (nolock)
					inner join v_EvnUslugaOper EUO with (nolock) on EUO.UslugaComplex_id = UC2.UslugaComplex_id
				where
					EUO.EvnUslugaOper_rid = :EvnPS_id
			";
		}

		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
}
