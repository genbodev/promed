<?php
class EvnDirectionMorfoHistologic_model extends swModel {
	/**
	 * constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnDirectionMorfoHistologic($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirectionMorfoHistologic_del
				@EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление направления на патоморфогистологическое исследование)'));
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnDirectionMorfoHistologicItems($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirectionMorfoHistologicItems_del
				@EvnDirectionMorfoHistologicItems_id = :EvnDirectionMorfoHistologicItems_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnDirectionMorfoHistologicItems_id' => $data['EvnDirectionMorfoHistologicItems_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление записи о прилагаемом документе или предмете)'));
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDirectionMorfoHistologicFields($data) {
		$query = "
			select top 1
				RTRIM(ISNULL(DOsn.Diag_Code, '')) as DiagOsn_Code,
				RTRIM(ISNULL(DOsl.Diag_Code, '')) as DiagOsl_Code,
				RTRIM(ISNULL(DSop.Diag_Code, '')) as DiagSop_Code,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_deathDT, 104) as deathDate,
				DATENAME(hour, EvnDirectionMorfoHistologic_deathDT) as deathHours,
				DATENAME(minute, EvnDirectionMorfoHistologic_deathDT) as deathMinutes,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Descr, '')) as EvnDirectionMorfoHistologic_Descr,
				REPLICATE('0', 7 - LEN(ISNULL(CAST(EDMH.EvnDirectionMorfoHistologic_Num as varchar(7)), ''))) + ISNULL(CAST(EDMH.EvnDirectionMorfoHistologic_Num as varchar(7)), '') as EvnDirectionMorfoHistologic_Num,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Phone, '')) as EvnDirectionMorfoHistologic_Phone,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Ser, '')) as EvnDirectionMorfoHistologic_Ser,
				ISNULL(EPS.EvnPS_dateRange, '00.00.0000 - 00.00.0000') as EvnPS_dateRange,
				RTRIM(COALESCE(Lpu.PAddress_Address, Lpu.UAddress_Address, '')) as Lpu_Address,
				RTRIM(ISNULL(Lpu.Lpu_Name, '')) as Lpu_Name,
				RTRIM(ISNULL(Lpu.Lpu_f003mcod, '')) as Lpu_FedCode,
				RTRIM(ISNULL(Lpu.Lpu_OGRN, '')) as Lpu_OGRN,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				RTRIM(ISNULL(LSP.LpuSectionProfile_Name, '')) as LpuSectionProfile_Name,
				convert(varchar(10), Polis.Polis_begDate, 104) as Polis_begDate,
				CASE WHEN PolisType.PolisType_Code = 4 then '' ELSE RTRIM(ISNULL(Polis.Polis_Ser, '')) END as Polis_Ser,
				CASE WHEN PolisType.PolisType_Code = 4 then isnull(RTRIM(PS.Person_EdNum), '') ELSE RTRIM(ISNULL(Polis.Polis_Num, '')) END AS Polis_Num,
				--RTRIM(ISNULL(Polis.Polis_Num, '')) as Polis_Num,
				--RTRIM(ISNULL(Polis.Polis_Ser, '')) as Polis_Ser,
				RTRIM(ISNULL(MP.Dolgnost_Name, '')) as Post_Name,
				RTRIM(ISNULL(MP.Person_Surname, '')) + ' ' + ISNULL(NULLIF(LEFT(RTRIM(ISNULL(MP.Person_Firname, '')), 1) + '.', '.'), '') + ISNULL(NULLIF(LEFT(RTRIM(ISNULL(MP.Person_Secname, '')), 1) + '.', '.'), '') as MedPersonal_Fio,
				RTRIM(ISNULL(MP.Person_Snils, '')) as MedPersonal_Snils,
				ISNULL(OH.Lpu_GlVrach, '') as GlavVrach_Fio,
				RTRIM(coalesce(OA.OrgAnatom_Name, dL.Lpu_Name, '')) as OrgAnatom_Name,
				RTRIM(ISNULL(OS.OrgSmo_Name, '')) as OrgSmo_Name,
				RTRIM(COALESCE(PAddr.Address_Address, UAddr.Address_Address, '')) as Person_Address,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				RTRIM(ISNULL(PT.PrehospType_Name, '')) as PrehospType_Name
			from v_EvnDirectionMorfoHistologic EDMH with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EDMH.Person_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDMH.Lpu_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EDMH.LpuSection_id
				left join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDMH.MedPersonal_id
					and MP.Lpu_id = :Lpu_id
				outer apply (
					select top 1
						RTRIM(ISNULL(PersonState.Person_Surname, '')) + ' ' + ISNULL(NULLIF(LEFT(RTRIM(ISNULL(PersonState.Person_Firname, '')), 1) + '.', '.'), '') + ISNULL(NULLIF(LEFT(RTRIM(ISNULL(PersonState.Person_Secname, '')), 1) + '.', '.'), '') as Lpu_GlVrach
					from v_OrgHead OrgHead with (nolock)
						inner join v_PersonState PersonState with (nolock) on PersonState.Person_id = OrgHead.Person_id
					where OrgHead.Lpu_id = :Lpu_id
						and OrgHead.OrgHeadPost_id = 1
					order by
						OrgHead.OrgHead_CommissDate desc
				) OH
				left join v_Lpu dL with(nolock) on dL.Lpu_id = EDMH.Lpu_did
				left join v_OrgAnatom OA with (nolock) on OA.OrgAnatom_id = EDMH.OrgAnatom_did
				left join PrehospType PT with (nolock) on PT.PrehospType_id = EDMH.PrehospType_did
				inner join Diag DOsn with (nolock) on DOsn.Diag_id = EDMH.Diag_id
				left join Diag DOsl with (nolock) on DOsl.Diag_id = EDMH.Diag_oid
				left join Diag DSop with (nolock) on DSop.Diag_id = EDMH.Diag_sid
				left join Polis with (nolock) on Polis.Polis_id = PS.Polis_id
				left join PolisType with (nolock) on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo OS with (nolock) on OS.OrgSmo_id = Polis.OrgSmo_id
				left join [Address] PAddr with (nolock) on PAddr.Address_id = PS.PAddress_id
				left join [Address] UAddr with (nolock) on UAddr.Address_id = PS.UAddress_id
				outer apply (
					select top 1
						NULLIF(ISNULL(convert(varchar(10), EvnPS_setDT, 104), '00.00.0000') + ' - ' + ISNULL(convert(varchar(10), EvnPS_disDT, 104), '00.00.0000'), ' - ') as EvnPS_dateRange
					from v_EvnPS with (nolock)
					where EvnPS_id = EDMH.EvnPS_id
				) EPS
			where EDMH.EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
				and EDMH.Lpu_id = :Lpu_id
		";
		$result = $this->db->query($query, array(
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
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
	 * @param $data
	 * @return bool
	 */
	function getEvnDirectionMorfoHistologicItemsData($data) {
		$query = "
			select
				RTRIM(ISNULL(EDMHI.EvnDirectionMorfoHistologicItems_Descr, '')) as EvnDirectionMorfoHistologicItems_Descr,
				RTRIM(ISNULL(EDMHI.EvnDirectionMorfoHistologicItems_Count, 0)) as EvnDirectionMorfoHistologicItems_Count,
				RTRIM(ISNULL(MHIT.MorfoHistologicItemsType_Code, 0)) as MorfoHistologicItemsType_Code
			from
				v_EvnDirectionMorfoHistologicItems EDMHI with (nolock)
				inner join MorfoHistologicItemsType MHIT with (nolock) on MHIT.MorfoHistologicItemsType_id = EDMHI.MorfoHistologicItemsType_id
			where
				EDMHI.EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
		";
		$result = $this->db->query($query, array('EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDirectionMorfoHistologicNumber($data) {
		$query = "
			declare @GenId bigint;
			exec xp_GenpmID
				@ObjectName = 'EvnDirectionMorfoHistologic',
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
	 * @param $data
	 * @return bool
	 */
	function loadEvnDirectionMorfoHistologicEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			SELECT TOP 1
				case when EDMH.Lpu_id = :Lpu_id and ISNULL(IsBad.YesNo_Code, 0) = 0 " . (count($med_personal_list)>0 ? "and EDMH.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_LawDocumentDate, 104) as EvnDirectionMorfoHistologic_LawDocumentDate,
				EDMH.Org_sid,
				EDMH.EvnDirectionMorfoHistologic_id,
				EDMH.Person_id,
				EDMH.Server_id,
				EDMH.PersonEvn_id,
				EDMH.EvnPS_id,
				EDMH.LpuSection_id,
				EDMH.EvnDirectionMorfoHistologic_LpuSectionName,
				EDMH.MedPersonal_id,
				EDMH.EvnDirectionMorfoHistologic_MedPersonalFIO,
				EDMH.Lpu_did,
				EDMH.Lpu_sid,
				EDMH.OrgAnatom_did,
				isnull(L.Org_id, OA.Org_id) as Org_did,
				EDMH.PrehospType_did,
				EDMH.Diag_id,
				EDMH.Diag_oid,
				EDMH.Diag_sid,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_deathDT, 104) as EvnDirectionMorfoHistologic_deathDate,
				convert(varchar(5), EDMH.EvnDirectionMorfoHistologic_deathDT, 108) as EvnDirectionMorfoHistologic_deathTime,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_setDate, 104) as EvnDirectionMorfoHistologic_setDate,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Descr, '')) as EvnDirectionMorfoHistologic_Descr,
				REPLICATE('0', 7 - LEN(ISNULL(CAST(EDMH.EvnDirectionMorfoHistologic_Num as varchar(7)), ''))) + ISNULL(CAST(EDMH.EvnDirectionMorfoHistologic_Num as varchar(7)), '') as EvnDirectionMorfoHistologic_Num,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Phone, '')) as EvnDirectionMorfoHistologic_Phone,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Ser, '')) as EvnDirectionMorfoHistologic_Ser,
				RTRIM(RTRIM(ISNULL(EPS.EvnPS_NumCard, '')) + ', ' + convert(varchar(10), EPS.EvnPS_setDate, 104)) as EvnPS_Title,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name
			FROM
				v_EvnDirectionMorfoHistologic EDMH with (nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = EDMH.Lpu_did
				left join v_OrgAnatom OA with(nolock) on OA.OrgAnatom_id = EDMH.OrgAnatom_did
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDMH.EvnDirectionMorfoHistologic_IsBad
				left join pmUserCache with (nolock) on pmUserCache.pmUser_id = EDMH.pmUser_pid
				outer apply (
					select top 1
						EvnPS_NumCard,
						EvnPS_setDate
					from v_EvnPS with (nolock)
					where EvnPS_id = EDMH.EvnPS_id
				) EPS
			WHERE (1 = 1)
				and EDMH.EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
				and (EDMH.Lpu_id = :Lpu_id or EDMH.Lpu_did = :Lpu_id)
		";
		$result = $this->db->query($query, array(
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
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
	 * @param $data
	 * @return bool
	 */
	function loadEvnDirectionMorfoHistologicGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		$filter .= " and EDMH.Lpu_id = :Lpu_id";

		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		if ( isset($data['EvnDirectionMorfoHistologic_Num']) ) {
			$filter .= " and EDMH.EvnDirectionMorfoHistologic_Num = :EvnDirectionMorfoHistologic_Num";
			$queryParams['EvnDirectionMorfoHistologic_Num'] = $data['EvnDirectionMorfoHistologic_Num'];
		}

		if ( isset($data['EvnDirectionMorfoHistologic_Ser']) ) {
			$filter .= " and EDMH.EvnDirectionMorfoHistologic_Ser = :EvnDirectionMorfoHistologic_Ser";
			$queryParams['EvnDirectionMorfoHistologic_Ser'] = $data['EvnDirectionMorfoHistologic_Ser'];
		}

		if ( isset($data['begDate']) ) {
			$filter .= " and EDMH.EvnDirectionMorfoHistologic_setDate >= cast(:begDate as datetime)";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( isset($data['endDate']) ) {
			$filter .= " and EDMH.EvnDirectionMorfoHistologic_setDate <= cast(:endDate as datetime)";
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
		$query = "
			select
				-- select
				case when EDMH.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EDMH.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'view' end as accessType,
				EDMH.EvnDirectionMorfoHistologic_id,
				EDMH.Person_id,
				EDMH.PersonEvn_id,
				EDMH.Server_id,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Ser, '')) as EvnDirectionMorfoHistologic_Ser,
				REPLICATE('0', 7 - LEN(ISNULL(CAST(EDMH.EvnDirectionMorfoHistologic_Num as varchar(7)), ''))) + ISNULL(CAST(EDMH.EvnDirectionMorfoHistologic_Num as varchar(7)), '') as EvnDirectionMorfoHistologic_Num,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_setDT, 104) as EvnDirectionMorfoHistologic_setDate,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_deathDT, 104) as EvnDirectionMorfoHistologic_deathDate,
				RTRIM(ISNULL(Org.Org_Name, '')) as OrgAnatom_Name,
				IsBad.YesNo_Code as EvnDirectionMorfoHistologic_IsBad,
				EMHP.EvnMorfoHistologicProto_id,
				case when EMHP.EvnMorfoHistologicProto_id is not null then 'true' else 'false' end EvnDirectionMorfoHistologic_IsProto
				-- end select
			from
				-- from
				v_PersonState PS with (nolock)
				inner join v_EvnDirectionMorfoHistologic EDMH with (nolock) on EDMH.Person_id = PS.Person_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDMH.MedPersonal_id
					and MP.Lpu_id = EDMH.Lpu_id
				left join v_Lpu L with(nolock) on L.Lpu_id = EDMH.Lpu_did
				left join v_OrgAnatom OA with (nolock) on OA.OrgAnatom_id = EDMH.OrgAnatom_did
				inner join Org with (nolock) on Org.Org_id = isnull(OA.Org_id, L.Org_id)
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDMH.EvnDirectionMorfoHistologic_IsBad
				left join v_EvnMorfoHistologicProto EMHP with(nolock) on EMHP.EvnDirectionMorfoHistologic_id = EDMH.EvnDirectionMorfoHistologic_id
				-- end from
			where
				-- where
				" . $filter . "
				and EDMH.Lpu_id = :Lpu_id
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
	 * @param $data
	 * @return bool
	 */
	function loadEvnDirectionMorfoHistologicItemsGrid($data) {
		$query = "
			select
				EDMHI.EvnDirectionMorfoHistologicItems_id,
				MHIT.MorfoHistologicItemsType_id,
				MHIT.MorfoHistologicItemsType_Name,
				EDMHI.EvnDirectionMorfoHistologicItems_Descr,
				EDMHI.EvnDirectionMorfoHistologicItems_Count
			from v_EvnDirectionMorfoHistologicItems EDMHI with (nolock)
				inner join MorfoHistologicItemsType MHIT with (nolock) on MHIT.MorfoHistologicItemsType_id = EDMHI.MorfoHistologicItemsType_id
			where EDMHI.EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
		";
		$result = $this->db->query($query, array('EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * @param $data
	 * @return bool
	 * загрузка списка направлений
	 */
	function loadEvnDirectionMorfoHistologicList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EDMH.Person_id = :Person_id";
		$queryParams['Person_id'] = $data['Person_id'];

		if ( $data['Lpu_id'] > 0 ) {
			// $filter .= " and EDMH.OrgAnatom_did = :Lpu_id";
			// $queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				EDMH.EvnDirectionMorfoHistologic_id,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Ser, '')) as EvnDirectionMorfoHistologic_Ser,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Num, '')) as EvnDirectionMorfoHistologic_Num,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_setDT, 104) as EvnDirectionMorfoHistologic_setDate,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_deathDT, 104) as EvnDirectionMorfoHistologic_deathDate,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_deathDT, 108) as EvnDirectionMorfoHistologic_deathTime,
				EDMH.Diag_id as EvnDirectionMorfoHistologic_Diag,
				EDMH.MedPersonal_id as EvnDirectionMorfoHistologic_MedPersonal_id,
				EDMH.LpuSection_id as EvnDirectionMorfoHistologic_LpuSection_id
			from v_EvnDirectionMorfoHistologic EDMH with (nolock)
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EDMH.EvnDirectionMorfoHistologic_IsBad
				outer apply (
					select top 1
						EvnMorfoHistologicProto_id
					from v_EvnMorfoHistologicProto with (nolock)
					where EvnDirectionMorfoHistologic_id = EDMH.EvnDirectionMorfoHistologic_id
				) EMHP
			where " . $filter . "
				and EMHP.EvnMorfoHistologicProto_id is null
				and ISNULL(IsBad.YesNo_Code, 0) = 0
			order by
				EDMH.EvnDirectionMorfoHistologic_setDT desc
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
	 * @param $data
	 * @return array|bool
	 * Сохранение направления
	 */
	function saveEvnDirectionMorfoHistologic($data) {
		$procedure = '';
		$success = true;

		if (empty($data['Lpu_did']) && empty($data['OrgAnatom_did'])) {
			return $this->createError('','Не передан параметр Куда направлен');
		}

		if ( (!isset($data['EvnDirectionMorfoHistologic_id'])) || ($data['EvnDirectionMorfoHistologic_id'] <= 0) ) {
			$procedure = 'p_EvnDirectionMorfoHistologic_ins';
		}
		else {
			$procedure = 'p_EvnDirectionMorfoHistologic_upd';
		}

		if ( isset($data['EvnDirectionMorfoHistologic_deathTime']) ) {
			$data['EvnDirectionMorfoHistologic_deathDate'] .= ' ' . $data['EvnDirectionMorfoHistologic_deathTime'] . ':00.000';
		}

		// Стартуем транзакцию
		$this->db->trans_begin();

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDirectionMorfoHistologic_id;
			exec " . $procedure . "
				@EvnDirectionMorfoHistologic_id = @Res output,
				@Org_sid = :Org_sid,
			    @EvnDirectionMorfoHistologic_LawDocumentDate = :EvnDirectionMorfoHistologic_LawDocumentDate,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnPS_id = :EvnPS_id,
				@Diag_id = :Diag_id,
				@Diag_oid = :Diag_oid,
				@Diag_sid = :Diag_sid,
				@EvnDirectionMorfoHistologic_setDT = :EvnDirectionMorfoHistologic_setDT,
				@EvnDirectionMorfoHistologic_deathDT = :EvnDirectionMorfoHistologic_deathDT,
				@LpuSection_id = :LpuSection_id,
				@MedPersonal_id = :MedPersonal_id,
				@TimetableGraf_id = :TimetableGraf_id,
				@TimetableStac_id = :TimetableStac_id,
				@EvnDirectionMorfoHistologic_Ser = :EvnDirectionMorfoHistologic_Ser,
				@EvnDirectionMorfoHistologic_Num = :EvnDirectionMorfoHistologic_Num,
				@EvnDirectionMorfoHistologic_Descr = :EvnDirectionMorfoHistologic_Descr,
				@EvnDirectionMorfoHistologic_Phone = :EvnDirectionMorfoHistologic_Phone,
				@EvnDirectionMorfoHistologic_LpuSectionName = :EvnDirectionMorfoHistologic_LpuSectionName,
				@EvnDirectionMorfoHistologic_MedPersonalFIO = :EvnDirectionMorfoHistologic_MedPersonalFIO,
				@Lpu_did = :Lpu_did,
				@Lpu_sid = :Lpu_sid,
				@OrgAnatom_did = :OrgAnatom_did,
				@PrehospType_did = :PrehospType_did,
				@EvnDirectionMorfoHistologic_IsBad = :EvnDirectionMorfoHistologic_IsBad,
				@pmUser_pid = :pmUser_pid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDirectionMorfoHistologic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
		    'Org_sid' => $data['Org_sid'],
            'EvnDirectionMorfoHistologic_LawDocumentDate' => $data['EvnDirectionMorfoHistologic_LawDocumentDate'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnPS_id' => $data['EvnPS_id'],
			'Diag_id' => $data['Diag_id'],
			'Diag_oid' => $data['Diag_oid'],
			'Diag_sid' => $data['Diag_sid'],
			'EvnDirectionMorfoHistologic_setDT' => $data['EvnDirectionMorfoHistologic_setDate'],
			'EvnDirectionMorfoHistologic_deathDT' => $data['EvnDirectionMorfoHistologic_deathDate'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'TimetableStac_id' => $data['TimetableStac_id'],
			'EvnDirectionMorfoHistologic_Ser' => $data['EvnDirectionMorfoHistologic_Ser'],
			'EvnDirectionMorfoHistologic_Num' => $data['EvnDirectionMorfoHistologic_Num'],
			'EvnDirectionMorfoHistologic_Descr' => $data['EvnDirectionMorfoHistologic_Descr'],
			'EvnDirectionMorfoHistologic_Phone' => $data['EvnDirectionMorfoHistologic_Phone'],
			'Lpu_did' => !empty($data['Lpu_did'])?$data['Lpu_did']:null,
			'Lpu_sid' => !empty($data['Lpu_sid']) ? $data['Lpu_sid'] : NULL,
			'EvnDirectionMorfoHistologic_LpuSectionName' => !empty($data['EvnDirectionMorfoHistologic_LpuSectionName']) ? $data['EvnDirectionMorfoHistologic_LpuSectionName'] : NULL,
			'EvnDirectionMorfoHistologic_MedPersonalFIO' => !empty($data['EvnDirectionMorfoHistologic_MedPersonalFIO']) ? $data['EvnDirectionMorfoHistologic_MedPersonalFIO'] : NULL,
			'OrgAnatom_did' => !empty($data['OrgAnatom_did'])?$data['OrgAnatom_did']:null,
			'PrehospType_did' => $data['PrehospType_did'],
			'EvnDirectionMorfoHistologic_IsBad' => (!empty($data['EvnDirectionMorfoHistologic_IsBad']) ? $data['EvnDirectionMorfoHistologic_IsBad'] : NULL),
			'pmUser_pid' => (!empty($data['pmUser_pid']) ? $data['pmUser_pid'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				if ( array_key_exists('Error_Msg', $response[0]) && empty($response[0]['Error_Msg']) ) {
					$id = $response[0]['EvnDirectionMorfoHistologic_id'];

					if ( isset($data['EvnDirectionMorfoHistologicItemsList']) && !empty($data['EvnDirectionMorfoHistologicItemsList']) && $data['EvnDirectionMorfoHistologicItemsList'] != "[]" ) {
						$items_list = json_decode(toUTF($data['EvnDirectionMorfoHistologicItemsList']), true);

						foreach ( $items_list as $item ) {
							$procedure = '';

							if ( isset($item['EvnDirectionMorfoHistologicItems_id']) && is_numeric($item['EvnDirectionMorfoHistologicItems_id']) ) {
								if ( $item['EvnDirectionMorfoHistologicItems_id'] <= 0 ) {
									$procedure = 'p_EvnDirectionMorfoHistologicItems_ins';
								}
								else {
									$procedure = 'p_EvnDirectionMorfoHistologicItems_upd';
								}
							}
							else {
								continue;
							}

							$query = "
								declare
									@Res bigint,
									@ErrCode int,
									@ErrMessage varchar(4000);
								set @Res = :EvnDirectionMorfoHistologicItems_id;
								exec " . $procedure . "
									@EvnDirectionMorfoHistologicItems_id = @Res output,
									@EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id,
									@MorfoHistologicItemsType_id = :MorfoHistologicItemsType_id,
									@EvnDirectionMorfoHistologicItems_Descr = :EvnDirectionMorfoHistologicItems_Descr,
									@EvnDirectionMorfoHistologicItems_Count = :EvnDirectionMorfoHistologicItems_Count,
									@pmUser_id = :pmUser_id,
									@Error_Code = @ErrCode output,
									@Error_Message = @ErrMessage output;
								select @Res as EvnDirectionMorfoHistologicItems_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
							";

							$queryParams = array(
								'EvnDirectionMorfoHistologicItems_id' => ($item['EvnDirectionMorfoHistologicItems_id'] > 0 ? $item['EvnDirectionMorfoHistologicItems_id'] : NULL),
								'EvnDirectionMorfoHistologic_id' => $id,
								'MorfoHistologicItemsType_id' => $item['MorfoHistologicItemsType_id'],
								'EvnDirectionMorfoHistologicItems_Descr' => toAnsi($item['EvnDirectionMorfoHistologicItems_Descr']),
								'EvnDirectionMorfoHistologicItems_Count' => $item['EvnDirectionMorfoHistologicItems_Count'],
								'pmUser_id' => $data['pmUser_id']
							);

							$result = $this->db->query($query, $queryParams);

					        if ( is_object($result) ) {
					       	    $response_temp = $result->result('array');

					   			if ( is_array($response_temp) && count($response_temp) > 0 ) {
									if ( !array_key_exists('Error_Msg', $response_temp[0]) || !empty($response_temp[0]['Error_Msg']) ) {
										$success = false;
										$response = $response_temp;
										$this->db->trans_rollback();
										break;
									}
								}
								else {
									$success = false;
									$response[0]['Error_Msg'] = 'Ошибка при добавлении/обновлении записи из списка прилагаемых документов и предметов';
									$this->db->trans_rollback();
									break;
								}
							}
							else {
								$success = false;
								$response[0]['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (добавление/обновление записи из списка прилагаемых документов и предметов)';
								$this->db->trans_rollback();
								break;
							}
						}
					}
				}
				else {
					$this->db->trans_rollback();
				}
			}
			else {
				$this->db->trans_rollback();
			}

			if ( $success === true ) {
				$this->db->trans_commit();
			}

			return $response;
		}
		else {
			$this->db->trans_rollback();
			return false;
		}
	}

	/**
	 * @param $data
	 * @return array
	 * Снятие/установка признака испорченного направления
	 */
	function setEvnDirectionMorfoHistologicIsBad($data) {
		$ret = array();
		$response = array(
			'success' => false,
			'Error_Msg' => ''
		);

		// Получаем данные по направлению
		$query = "
			select top 1
				EDMH.EvnDirectionMorfoHistologic_id,
				EDMH.Lpu_id,
				EDMH.Lpu_did,
				EDMH.Server_id,
				EDMH.PersonEvn_id,
				EDMH.TimetableGraf_id,
				EDMH.TimetableStac_id,
				EDMH.EvnPS_id,
				EDMH.Diag_id,
				EDMH.Diag_oid,
				EDMH.Diag_sid,
				EDMH.Org_sid,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_LawDocumentDate, 120) as EvnDirectionMorfoHistologic_LawDocumentDate,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_setDT, 120) as EvnDirectionMorfoHistologic_setDate,
				ISNULL(EDMH.EvnDirectionMorfoHistologic_setTime, '') as EvnDirectionMorfoHistologic_setTime,
				convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_deathDT, 120) as EvnDirectionMorfoHistologic_deathDate,
				ISNULL(convert(varchar(5), EDMH.EvnDirectionMorfoHistologic_deathDT, 114), '') as EvnDirectionMorfoHistologic_deathTime,
				EDMH.LpuSection_id,
				EDMH.MedPersonal_id,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Ser, '')) as EvnDirectionMorfoHistologic_Ser,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Num, '')) as EvnDirectionMorfoHistologic_Num,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Descr, '')) as EvnDirectionMorfoHistologic_Descr,
				RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Phone, '')) as EvnDirectionMorfoHistologic_Phone,
				EDMH.OrgAnatom_did,
				EDMH.PrehospType_did,
				ISNULL(EDMH.pmUser_updID, EDMH.pmUser_insID) as pmUser_id
			from v_EvnDirectionMorfoHistologic EDMH with (nolock)
			where EDMH.EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id
				and EDMH.Lpu_id = :Lpu_id
				and ISNULL(EDMH.EvnDirectionMorfoHistologic_IsBad, 1) <> :EvnDirectionMorfoHistologic_IsBad
		";

		$queryParams = array(
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'EvnDirectionMorfoHistologic_IsBad' => $data['EvnDirectionMorfoHistologic_IsBad'],
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

		$res[0]['EvnDirectionMorfoHistologic_IsBad'] = $data['EvnDirectionMorfoHistologic_IsBad'];
		$res[0]['pmUser_pid'] = $data['pmUser_pid'];

		$res = $this->saveEvnDirectionMorfoHistologic($res[0]);

		if ( !is_array($res) || count($res) == 0 ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (снятие/установка признака испорченного направления)';
			$ret[0] = $response;
			return $ret;
		}

		if ( strlen($res[0]['Error_Msg']) > 0 ) {
			$response['Error_Msg'] = $res[0]['Error_Msg'];
			$ret[0] = $response;
			return $ret;
		}

		$response['success'] = true;
		$ret[0] = $response;
		return $ret;
	}
}
?>