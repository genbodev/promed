<?php
class EvnMorfoHistologicProto_model extends CI_Model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	function deleteEvnMorfoHistologicDiagDiscrepancy($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnMorfoHistologicDiagDiscrepancy_del
				@EvnMorfoHistologicDiagDiscrepancy_id = :EvnMorfoHistologicDiagDiscrepancy_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'EvnMorfoHistologicDiagDiscrepancy_id' => $data['EvnMorfoHistologicDiagDiscrepancy_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление ошибки клинической диагностики)'));
		}
	}

	/**
	 * Удаление
	 */
	function deleteEvnMorfoHistologicMember($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnMorfoHistologicMember_del
				@EvnMorfoHistologicMember_id = :EvnMorfoHistologicMember_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'EvnMorfoHistologicMember_id' => $data['EvnMorfoHistologicMember_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление записи из списка присутствовавших при вскрытии)'));
		}
	}


	/**
	 * Удаление
	 */
	function deleteEvnMorfoHistologicProto($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnMorfoHistologicProto_del
				@EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление протокола патоморфогистологического исследования)'));
		}
	}


	/**
	 * Генерация номера
	 */
	function getEvnMorfoHistologicProtoNumber($data) {
		$query = "
			declare @GenId bigint;
			exec xp_GenpmID
				@ObjectName = 'EvnMorfoHistologicProto',
				@Lpu_id = :Lpu_id,
				@ObjectID = @GenId output;
			select @GenId as EvnMorfoHistologicProto_Num;
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
	 * Получение данных для грида
	 */
	function loadEvnMorfoHistologicProtoGrid($data) {
		$filter = "(1 = 1)";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'PMUser_id' => $data['pmUser_id']
		);

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
				case 
					when
						ISNULL(IsBad.YesNo_Code, 0) = 0 
						and (
							(isSuperAdmin.pmUserCacheGroup_id is not null and EMHP.Lpu_id = :Lpu_id)
							or (EMHP.MedPersonal_aid is null and (isPatb.MedServiceType_id is not null and EMHP.Lpu_id = :Lpu_id))
							or (MedService.MedService_id is not null or LpuSection.LpuSection_id is not null)
						) 
					then 'edit' else 'view' end 
				as accessType,
				EMHP.EvnMorfoHistologicProto_id,
				EMHP.Person_id,
				EMHP.PersonEvn_id,
				EMHP.Server_id,
				EMHP.Lpu_id,
				EMHP.MedPersonal_id,
				EMHP.MedPersonal_aid,
				EMHP.EvnMorfoHistologicProto_Ser,
				EMHP.EvnMorfoHistologicProto_Num,
				RTRIM(coalesce(OrgLpu.Org_Nick, Org.Org_Nick, '')) as Lpu_Name,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				IsBad.YesNo_Code as EvnMorfoHistologicProto_IsBad
				-- end select
			from
				-- from
				v_EvnMorfoHistologicProto EMHP with (nolock)
				inner join v_EvnDirectionMorfoHistologic EDMH with (nolock) on EDMH.EvnDirectionMorfoHistologic_id = EMHP.EvnDirectionMorfoHistologic_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = EMHP.Person_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EMHP.LpuSection_id
				left join Lpu Lpu with (nolock) on Lpu.Lpu_id = EDMH.Lpu_sid
				left join Org OrgLpu with (nolock) on OrgLpu.Org_id = Lpu.Org_id
				left join Org Org with (nolock) on Org.Org_id = EDMH.Org_sid
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EMHP.MedPersonal_aid
					and MP.Lpu_id = :Lpu_id
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EMHP.EvnMorfoHistologicProto_IsBad
				
				outer apply (
					select top 1 UCG.pmUserCacheGroup_id from dbo.pmUserCache UC with (nolock) 
					left join pmUserCacheGroupLink UCGL with (nolock) on UC.PMUser_id = UCGL.pmUserCache_id
					left join pmUserCacheGroup UCG with (nolock) on UCG.pmUserCacheGroup_id = UCGL.pmUserCacheGroup_id
					where UC.PMUser_id = :PMUser_id and UCG.pmUserCacheGroup_Code = 'SuperAdmin'
				) as isSuperAdmin
				
				outer apply (
					select top 1 MS.MedServiceType_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedService MS with (nolock) on MSMP.MedService_id = MS.MedService_id
					inner join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					where MST.MedServiceType_SysNick = 'patb' 
					and MSMP.MedPersonal_id in (".(count($med_personal_list)>0?implode(',',$med_personal_list):"null").")
				) as isPatb
				
				outer apply (
					select top 1 MS.MedService_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedService MS with (nolock) on MSMP.MedService_id = MS.MedService_id
					inner join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					left join v_MedServiceMedPersonal MSMP1 with (nolock) on MSMP1.MedService_id = MS.MedService_id
					where MST.MedServiceType_SysNick = 'patb' and MSMP.MedPersonal_id = EMHP.MedPersonal_aid
						" . (count($med_personal_list)>0 ? "and MSMP1.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
				) as MedService

				outer apply (
					select top 1 MSS.LpuSection_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedServiceSection MSS with (nolock) on MSMP.MedService_id = MSS.MedService_id
					inner join v_MedStaffFact MSF with (nolock) on MSS.LpuSection_id = MSF.LpuSection_id
					inner join persis.v_Post P with (nolock) on P.id = MSF.Post_id
					where 
						MSMP.MedPersonal_id = EMHP.MedPersonal_aid
						" . (count($med_personal_list)>0 ? "and MSF.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
						and frmpEntry_id = 10235
				) as LpuSection
				-- end from
			where
				-- where
				" . $filter . "
				and EMHP.Lpu_id = :Lpu_id
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
						$count = $get_count_result->result('array');
						$response['totalCount'] = $count[0]['cnt'];
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
	 * Сохранение
	 */
	function saveEvnMorfoHistologicProto($data) {
		$procedure = '';

		if ( (!isset($data['EvnMorfoHistologicProto_id'])) || ($data['EvnMorfoHistologicProto_id'] <= 0) ) {
			$procedure = 'p_EvnMorfoHistologicProto_ins';
		}
		else {
			$procedure = 'p_EvnMorfoHistologicProto_upd';
		}

		if ( isset($data['EvnMorfoHistologicProto_deathDate']) && isset($data['EvnMorfoHistologicProto_deathTime']) ) {
			$data['EvnMorfoHistologicProto_deathDate'] .= ' ' . $data['EvnMorfoHistologicProto_deathTime'] . ':00.000';
		}

		if($data['EvnMorfoHistologicProto_Ser'] === null){
			$data['EvnMorfoHistologicProto_Ser'] = '';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMorfoHistologicProto_id;

			exec " . $procedure . "
				@EvnMorfoHistologicProto_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@EvnMorfoHistologicProto_setDT = :EvnMorfoHistologicProto_setDate,
				@EvnDirectionMorfoHistologic_id = :EvnDirectionMorfoHistologic_id,
				@EvnMorfoHistologicProto_Ser = :EvnMorfoHistologicProto_Ser,
				@EvnMorfoHistologicProto_Num = :EvnMorfoHistologicProto_Num,
				@LpuSection_id = :LpuSection_id,
				@EvnMorfoHistologicProto_deathDT = :EvnMorfoHistologicProto_deathDT,
				@EvnMorfoHistologicProto_autopsyDT = :EvnMorfoHistologicProto_autopsyDate,
				@MedPersonal_id = :MedPersonal_id,
				@Diag_did = :Diag_did,
				@EvnMorfoHistologicProto_DiagNameDirect = :EvnMorfoHistologicProto_DiagNameDirect,
				@Diag_sid = :Diag_sid,
				@EvnMorfoHistologicProto_DiagNameSupply = :EvnMorfoHistologicProto_DiagNameSupply,
				@EvnMorfoHistologicProto_DiagSetDT = :EvnMorfoHistologicProto_DiagSetDate,
				@EvnMorfoHistologicProto_DiagDescr = :EvnMorfoHistologicProto_DiagDescr,
				@EvnMorfoHistologicProto_ResultLabStudy = :EvnMorfoHistologicProto_ResultLabStudy,
				@EvnMorfoHistologicProto_DiagPathology = :EvnMorfoHistologicProto_DiagPathology,
				@Diag_vid = :Diag_vid,
				@Diag_vid_Descr = :Diag_vid_Descr,
				@Diag_wid = :Diag_wid,
				@Diag_wid_Descr = :Diag_wid_Descr,
				@Diag_xid = :Diag_xid,
				@Diag_xid_Descr = :Diag_xid_Descr,
				@Diag_yid = :Diag_yid,
				@Diag_yid_Descr = :Diag_yid_Descr,
				@Diag_zid = :Diag_zid,
				@Diag_zid_Descr = :Diag_zid_Descr,
				@EvnMorfoHistologicProto_Epicrisis = :EvnMorfoHistologicProto_Epicrisis,
				@MedPersonal_aid = :MedPersonal_aid,
				@MedPersonal_zid = :MedPersonal_zid,
				@EvnMorfoHistologicProto_IsBad = :EvnMorfoHistologicProto_IsBad,
				@pmUser_pid = :pmUser_pid,
				@EvnMorfoHistologicProto_BrainWeight = :EvnMorfoHistologicProto_BrainWeight,
				@EvnMorfoHistologicProto_HeartWeight = :EvnMorfoHistologicProto_HeartWeight,
				@EvnMorfoHistologicProto_LungsWeight = :EvnMorfoHistologicProto_LungsWeight,
				@EvnMorfoHistologicProto_LiverWeight = :EvnMorfoHistologicProto_LiverWeight,
				@EvnMorfoHistologicProto_SpleenWeight = :EvnMorfoHistologicProto_SpleenWeight,
				@EvnMorfoHistologicProto_KidneyLeftWeight = :EvnMorfoHistologicProto_KidneyLeftWeight,
				@EvnMorfoHistologicProto_KidneyRightWeight = :EvnMorfoHistologicProto_KidneyRightWeight,
				@EvnMorfoHistologicProto_BitCount = :EvnMorfoHistologicProto_BitCount,
				@EvnMorfoHistologicProto_BlockCount = :EvnMorfoHistologicProto_BlockCount,
				@EvnMorfoHistologicProto_MethodDescr = :EvnMorfoHistologicProto_MethodDescr,
				@EvnMorfoHistologicProto_ProtocolDescr = :EvnMorfoHistologicProto_ProtocolDescr,
				@EvnPS_id = :EvnPS_id,
				@DeathSvid_id = :DeathSvid_id,
				@PntDeathSvid_id = :PntDeathSvid_id,
				@PathologicCategoryType_id = :PathologicCategoryType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnMorfoHistologicProto_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'EvnMorfoHistologicProto_setDate' => $data['EvnMorfoHistologicProto_setDate'],
			'EvnDirectionMorfoHistologic_id' => $data['EvnDirectionMorfoHistologic_id'],
			'EvnMorfoHistologicProto_Ser' => $data['EvnMorfoHistologicProto_Ser'],
			'EvnMorfoHistologicProto_Num' => $data['EvnMorfoHistologicProto_Num'],
			'LpuSection_id' => $data['LpuSection_id'],
			'EvnMorfoHistologicProto_deathDT' => $data['EvnMorfoHistologicProto_deathDate'],
			'EvnMorfoHistologicProto_autopsyDate' => $data['EvnMorfoHistologicProto_autopsyDate'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'Diag_did' => $data['Diag_did'],
			'EvnMorfoHistologicProto_DiagNameDirect' => $data['EvnMorfoHistologicProto_DiagNameDirect'],
			'Diag_sid' => $data['Diag_sid'],
			'EvnMorfoHistologicProto_DiagNameSupply' => $data['EvnMorfoHistologicProto_DiagNameSupply'],
			'EvnMorfoHistologicProto_DiagSetDate' => $data['EvnMorfoHistologicProto_DiagSetDate'],
			'EvnMorfoHistologicProto_DiagDescr' => $data['EvnMorfoHistologicProto_DiagDescr'],
			'EvnMorfoHistologicProto_ResultLabStudy' => $data['EvnMorfoHistologicProto_ResultLabStudy'],
			'EvnMorfoHistologicProto_DiagPathology' => $data['EvnMorfoHistologicProto_DiagPathology'],
			'Diag_vid' => $data['Diag_vid'],
			'Diag_vid_Descr' => $data['Diag_vid_Descr'],
			'Diag_wid' => $data['Diag_wid'],
			'Diag_wid_Descr' => $data['Diag_wid_Descr'],
			'Diag_xid' => $data['Diag_xid'],
			'Diag_xid_Descr' => $data['Diag_xid_Descr'],
			'Diag_yid' => $data['Diag_yid'],
			'Diag_yid_Descr' => $data['Diag_yid_Descr'],
			'Diag_zid' => $data['Diag_zid'],
			'Diag_zid_Descr' => $data['Diag_zid_Descr'],
			'EvnMorfoHistologicProto_Epicrisis' => $data['EvnMorfoHistologicProto_Epicrisis'],
			'MedPersonal_aid' => $data['MedPersonal_aid'],
			'MedPersonal_zid' => $data['MedPersonal_zid'],
			'EvnMorfoHistologicProto_IsBad' => (!empty($data['EvnMorfoHistologicProto_IsBad']) ? $data['EvnMorfoHistologicProto_IsBad'] : NULL),
			'pmUser_pid' => (!empty($data['pmUser_pid']) ? $data['pmUser_pid'] : NULL),
			'EvnMorfoHistologicProto_BrainWeight' => $data['EvnMorfoHistologicProto_BrainWeight'],
			'EvnMorfoHistologicProto_HeartWeight' => $data['EvnMorfoHistologicProto_HeartWeight'],
			'EvnMorfoHistologicProto_LungsWeight' => $data['EvnMorfoHistologicProto_LungsWeight'],
			'EvnMorfoHistologicProto_LiverWeight' => $data['EvnMorfoHistologicProto_LiverWeight'],
			'EvnMorfoHistologicProto_SpleenWeight' => $data['EvnMorfoHistologicProto_SpleenWeight'],
			'EvnMorfoHistologicProto_KidneyLeftWeight' => $data['EvnMorfoHistologicProto_KidneyLeftWeight'],
			'EvnMorfoHistologicProto_KidneyRightWeight' => $data['EvnMorfoHistologicProto_KidneyRightWeight'],
			'EvnMorfoHistologicProto_BitCount' => $data['EvnMorfoHistologicProto_BitCount'],
			'EvnMorfoHistologicProto_BlockCount' => $data['EvnMorfoHistologicProto_BlockCount'],
			'EvnMorfoHistologicProto_MethodDescr' => $data['EvnMorfoHistologicProto_MethodDescr'],
			'EvnMorfoHistologicProto_ProtocolDescr' => $data['EvnMorfoHistologicProto_ProtocolDescr'],
			'EvnPS_id' => $data['EvnPS_id'],
			'DeathSvid_id' => $data['DeathSvid_id'],
			'PntDeathSvid_id' => $data['PntDeathSvid_id'],
			'PathologicCategoryType_id' => $data['PathologicCategoryType_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение полей
	 * @param $data
	 * @return array|bool
	 */
	function getEvnMorfoHistologicProtoFields($data) {
		$query = "
			select top 1
				ISNULL(Lpu.Lpu_Name, '') as Lpu_Name,
				ISNULL(Lpu.UAddress_Address, '') as Lpu_Address,
				RTRIM(RTRIM(ISNULL(PS.Person_Surname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Firname, '')) + ' ' + RTRIM(ISNULL(PS.Person_Secname, ''))) as Person_Fio,
				dbo.f_getHumanFriendlyAddressString(PS.PAddress_id) as Person_PAddress,
				(datediff(year, PS.Person_BirthDay, EMHP.EvnMorfoHistologicProto_setDT)
					+ case when month(PS.Person_BirthDay) > month(EMHP.EvnMorfoHistologicProto_setDT) or (month(PS.Person_BirthDay) = month(EMHP.EvnMorfoHistologicProto_setDT) and day(PS.Person_BirthDay) > day(EMHP.EvnMorfoHistologicProto_setDT))
					then -1 else 0 end
				) as Person_Age,
				ISNULL(EMHP.EvnMorfoHistologicProto_BitCount, 0) as EvnMorfoHistologicProto_BitCount,
				ISNULL(EMHP.EvnMorfoHistologicProto_BlockCount, 0) as EvnMorfoHistologicProto_BlockCount,
				DAY(EMHP.EvnMorfoHistologicProto_setDT) as EvnMorfoHistologicProto_setDay,
				MONTH(EMHP.EvnMorfoHistologicProto_setDT) as EvnMorfoHistologicProto_setMonth,
				YEAR(EMHP.EvnMorfoHistologicProto_setDT) as EvnMorfoHistologicProto_setYear,
				RTRIM(ISNULL(EMHP.EvnMorfoHistologicProto_Num, '')) as EvnMorfoHistologicProto_Num,
				RTRIM(ISNULL(EMHP.EvnMorfoHistologicProto_Ser, '')) as EvnMorfoHistologicProto_Ser,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagNameDirect, '') as EvnMorfoHistologicProto_DiagNameDirect,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagNameSupply, '') as EvnMorfoHistologicProto_DiagNameSupply,
				ISNULL(EMHP.EvnMorfoHistologicProto_ResultLabStudy, '') as EvnMorfoHistologicProto_ResultLabStudy,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagPathology, '') as EvnMorfoHistologicProto_DiagPathology,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(MPZ.Person_Fio, '')) as MedPersonal_FioZ,
				RTRIM(ISNULL(DiagV.Diag_Code, '')) as DiagV_Code,
				RTRIM(ISNULL(DiagW.Diag_Code, '')) as DiagW_Code,
				RTRIM(ISNULL(DiagX.Diag_Code, '')) as DiagX_Code,
				RTRIM(ISNULL(DiagY.Diag_Code, '')) as DiagY_Code,
				RTRIM(ISNULL(DiagZ.Diag_Code, '')) as DiagZ_Code,
				RTRIM(ISNULL(EMHP.Diag_vid_Descr, '')) as DiagV_Descr,
				RTRIM(ISNULL(EMHP.Diag_wid_Descr, '')) as DiagW_Descr,
				RTRIM(ISNULL(EMHP.Diag_xid_Descr, '')) as DiagX_Descr,
				RTRIM(ISNULL(EMHP.Diag_yid_Descr, '')) as DiagY_Descr,
				RTRIM(ISNULL(EMHP.Diag_zid_Descr, '')) as DiagZ_Descr,
				EMHP.EvnMorfoHistologicProto_Epicrisis,
				EMHP.EvnMorfoHistologicProto_ProtocolDescr,
				EMHP.EvnMorfoHistologicProto_BrainWeight,
				EMHP.EvnMorfoHistologicProto_HeartWeight,
				EMHP.EvnMorfoHistologicProto_LungsWeight,
				EMHP.EvnMorfoHistologicProto_LiverWeight,
				EMHP.EvnMorfoHistologicProto_SpleenWeight,
				EMHP.EvnMorfoHistologicProto_KidneyLeftWeight,
				EMHP.EvnMorfoHistologicProto_KidneyRightWeight,
				EMHP.EvnMorfoHistologicProto_BitCount,
				EMHP.EvnMorfoHistologicProto_BlockCount,
				EMHP.EvnMorfoHistologicProto_MethodDescr,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_deathDT, 104) as EvnMorfoHistologicProto_deathDate,
				convert(varchar(5), EMHP.EvnMorfoHistologicProto_deathDT, 108) as EvnMorfoHistologicProto_deathTime,
				PS.Sex_id as Person_Sex,
				DiagD.Diag_Code + ' ' + DiagD.Diag_Name As Diag_Direction,
				DiagS.Diag_Code + ' ' + DiagS.Diag_Name As Diag_Income,
				RTRIM(ISNULL(EPS.EvnPS_NumCard, '')) as EvnPS_NumCard,
				RTRIM(ISNULL(DS.DeathSvid_Num, '')) as DeathSvid_Num
			from v_EvnMorfoHistologicProto EMHP with (nolock)
				inner join v_PersonState PS with (nolock) on PS.Person_id = EMHP.Person_id
				inner join v_EvnDirectionMorfoHistologic EDMH with (nolock) on EDMH.EvnDirectionMorfoHistologic_id = EMHP.EvnDirectionMorfoHistologic_id
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EMHP.Lpu_id
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EMHP.MedPersonal_id
					and MP.Lpu_id = EMHP.Lpu_id
				inner join v_MedPersonal MPZ with (nolock) on MPZ.MedPersonal_id = EMHP.MedPersonal_zid
					and MPZ.Lpu_id = EMHP.Lpu_id
				left join v_EvnPS EPS with (nolock) on EPS.EvnPS_id = EMHP.EvnPS_id
				left join v_DeathSvid DS with (nolock) on DS.DeathSvid_id = EMHP.DeathSvid_id
				left join Diag DiagV with (nolock) on DiagV.Diag_id = EMHP.Diag_vid
				left join Diag DiagW with (nolock) on DiagW.Diag_id = EMHP.Diag_wid
				left join Diag DiagX with (nolock) on DiagX.Diag_id = EMHP.Diag_xid
				left join Diag DiagY with (nolock) on DiagY.Diag_id = EMHP.Diag_yid
				left join Diag DiagZ with (nolock) on DiagZ.Diag_id = EMHP.Diag_zid
				left join Diag DiagD with (nolock) on DiagD.Diag_id = EMHP.Diag_did
				left join Diag DiagS with (nolock) on DiagS.Diag_id = EMHP.Diag_sid
			where EMHP.EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id
				and (EMHP.Lpu_id = :Lpu_id or EDMH.Lpu_id = :Lpu_id)
		";
		$result = $this->db->query($query, array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
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
	 * Получение данных для редактирования
	 */
	function loadEvnMorfoHistologicProtoEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select top 1
				case 
					when
						ISNULL(IsBad.YesNo_Code, 0) = 0 
						and (
							(isSuperAdmin.pmUserCacheGroup_id is not null and EMHP.Lpu_id = :Lpu_id)
							or (EMHP.MedPersonal_aid is null and (isPatb.MedServiceType_id is not null and EMHP.Lpu_id = :Lpu_id))
							or (MedService.MedService_id is not null or LpuSection.LpuSection_id is not null)
						) 
					then 'edit' else 'view' end 
				as accessType,
				EMHP.EvnMorfoHistologicProto_id,
				EDMH.EvnDirectionMorfoHistologic_id,
				EMHP.Person_id,
				EMHP.Server_id,
				EMHP.PersonEvn_id,
				LTRIM(RTRIM(ISNULL(EDMH.EvnDirectionMorfoHistologic_Ser, '') + ' ' + ISNULL(cast(EDMH.EvnDirectionMorfoHistologic_Num as varchar(10)), '') + ', ' + ISNULL(convert(varchar(10), EDMH.EvnDirectionMorfoHistologic_setDate, 104), ''))) as EvnDirectionMorfoHistologic_SerNum,
				RTRIM(ISNULL(EMHP.EvnMorfoHistologicProto_Ser, '')) as EvnMorfoHistologicProto_Ser,
				RTRIM(ISNULL(EMHP.EvnMorfoHistologicProto_Num, '')) as EvnMorfoHistologicProto_Num,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_setDate, 104) as EvnMorfoHistologicProto_setDate,
				EMHP.EvnPS_id,
				EMHP.DeathSvid_id,
				EMHP.PntDeathSvid_id,
				EMHP.LpuSection_id,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_deathDT, 104) as EvnMorfoHistologicProto_deathDate,
				convert(varchar(5), EMHP.EvnMorfoHistologicProto_deathDT, 108) as EvnMorfoHistologicProto_deathTime,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_autopsyDT, 104) as EvnMorfoHistologicProto_autopsyDate,
				EMHP.MedPersonal_id,
				EMHP.Diag_did,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagNameDirect, '') as EvnMorfoHistologicProto_DiagNameDirect,
				EMHP.Diag_sid,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagNameSupply, '') as EvnMorfoHistologicProto_DiagNameSupply,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_DiagSetDT, 104) as EvnMorfoHistologicProto_DiagSetDate,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagDescr, '') as EvnMorfoHistologicProto_DiagDescr,
				ISNULL(EMHP.EvnMorfoHistologicProto_ResultLabStudy, '') as EvnMorfoHistologicProto_ResultLabStudy,
				ISNULL(EMHP.EvnMorfoHistologicProto_DiagPathology, '') as EvnMorfoHistologicProto_DiagPathology,
				EMHP.Diag_vid,
				ISNULL(EMHP.Diag_vid_Descr, '') as Diag_vid_Descr,
				EMHP.Diag_wid,
				ISNULL(EMHP.Diag_wid_Descr, '') as Diag_wid_Descr,
				EMHP.Diag_xid,
				ISNULL(EMHP.Diag_xid_Descr, '') as Diag_xid_Descr,
				EMHP.Diag_yid,
				ISNULL(EMHP.Diag_yid_Descr, '') as Diag_yid_Descr,
				EMHP.Diag_zid,
				ISNULL(EMHP.Diag_zid_Descr, '') as Diag_zid_Descr,
				ISNULL(EMHP.EvnMorfoHistologicProto_Epicrisis, '') as EvnMorfoHistologicProto_Epicrisis,
				EMHP.MedPersonal_aid,
				EMHP.MedPersonal_zid,
				EMHP.EvnMorfoHistologicProto_BrainWeight,
				EMHP.EvnMorfoHistologicProto_HeartWeight,
				EMHP.EvnMorfoHistologicProto_LungsWeight,
				EMHP.EvnMorfoHistologicProto_LiverWeight,
				EMHP.EvnMorfoHistologicProto_SpleenWeight,
				EMHP.EvnMorfoHistologicProto_KidneyLeftWeight,
				EMHP.EvnMorfoHistologicProto_KidneyRightWeight,
				EMHP.EvnMorfoHistologicProto_BitCount,
				EMHP.EvnMorfoHistologicProto_BlockCount,
				EMHP.PathologicCategoryType_id,
				ISNULL(EMHP.EvnMorfoHistologicProto_MethodDescr, '') as EvnMorfoHistologicProto_MethodDescr,
				ISNULL(EMHP.EvnMorfoHistologicProto_ProtocolDescr, '') as EvnMorfoHistologicProto_ProtocolDescr,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name,
				convert(varchar(10), MHCR.MorfoHistologicCorpseReciept_setDT, 104) as MorfoHistologicCorpse_recieptDate
			from
				v_EvnMorfoHistologicProto EMHP with (nolock)
				inner join v_EvnDirectionMorfoHistologic EDMH with (nolock) on EDMH.EvnDirectionMorfoHistologic_id = EMHP.EvnDirectionMorfoHistologic_id
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EMHP.EvnMorfoHistologicProto_IsBad
				left join pmUserCache with (nolock) on pmUserCache.pmUser_id = EMHP.pmUser_pid
				
				outer apply (
					select top 1 UCG.pmUserCacheGroup_id from dbo.pmUserCache UC with (nolock)
					left join pmUserCacheGroupLink UCGL with (nolock) on UC.PMUser_id = UCGL.pmUserCache_id
					left join pmUserCacheGroup UCG with (nolock) on UCG.pmUserCacheGroup_id = UCGL.pmUserCacheGroup_id
					where UC.PMUser_id = :PMUser_id and UCG.pmUserCacheGroup_Code = 'SuperAdmin'
				) as isSuperAdmin
				
				outer apply (
					select top 1 MS.MedServiceType_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedService MS with (nolock) on MSMP.MedService_id = MS.MedService_id
					inner join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					where MST.MedServiceType_SysNick = 'patb' 
					and MSMP.MedPersonal_id in (".(count($med_personal_list)>0?implode(',',$med_personal_list):"null").")
				) as isPatb
				
				outer apply (
					select top 1 MS.MedService_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedService MS with (nolock) on MSMP.MedService_id = MS.MedService_id
					inner join v_MedServiceType MST with (nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					left join v_MedServiceMedPersonal MSMP1 with (nolock) on MSMP1.MedService_id = MS.MedService_id
					where MST.MedServiceType_SysNick = 'patb' and MSMP.MedPersonal_id = EMHP.MedPersonal_aid
						" . (count($med_personal_list)>0 ? "and MSMP1.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
				) as MedService

				outer apply (
					select top 1 MSS.LpuSection_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedServiceSection MSS with (nolock) on MSMP.MedService_id = MSS.MedService_id
					inner join v_MedStaffFact MSF with (nolock) on MSS.LpuSection_id = MSF.LpuSection_id
					inner join persis.v_Post P with (nolock) on P.id = MSF.Post_id
					where 
						MSMP.MedPersonal_id = EMHP.MedPersonal_aid
						" . (count($med_personal_list)>0 ? "and MSF.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
						and frmpEntry_id = 10235
				) as LpuSection
				left join v_MorfoHistologicCorpseReciept MHCR on MHCR.EvnDirectionMorfoHistologic_id = EMHP.EvnDirectionMorfoHistologic_id
			where (1 = 1)
				and EMHP.EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id
				and (EMHP.Lpu_id = :Lpu_id or EDMH.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		// echo getDebugSQL($query, array('EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'], 'Lpu_id' => $data['Lpu_id'])); exit();
		$result = $this->db->query($query, array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PMUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Установление флага испорченности
	 */
	function setEvnMorfoHistologicProtoIsBad($data) {
		$response = array(
			'success' => false,
			'Error_Msg' => ''
		);

		// Получаем данные по протоколу
		$query = "
			select top 1
				EMHP.EvnMorfoHistologicProto_id,
				EMHP.EvnDirectionMorfoHistologic_id,
				EMHP.Person_id,
				EMHP.Lpu_id,
				EMHP.Server_id,
				EMHP.PersonEvn_id,
				EMHP.EvnMorfoHistologicProto_Ser,
				EMHP.EvnMorfoHistologicProto_Num,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_setDate, 120) as EvnMorfoHistologicProto_setDate,
				EMHP.LpuSection_id,
				EMHP.EvnPS_id,
				convert(varchar(20), EMHP.EvnMorfoHistologicProto_deathDT, 120) as EvnMorfoHistologicProto_deathDate,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_autopsyDT, 120) as EvnMorfoHistologicProto_autopsyDate,
				EMHP.MedPersonal_id,
				EMHP.Diag_did,
				EMHP.EvnMorfoHistologicProto_DiagNameDirect,
				EMHP.Diag_sid,
				EMHP.EvnMorfoHistologicProto_DiagNameSupply,
				convert(varchar(10), EMHP.EvnMorfoHistologicProto_DiagSetDT, 120) as EvnMorfoHistologicProto_DiagSetDate,
				EMHP.EvnMorfoHistologicProto_DiagDescr,
				EMHP.EvnMorfoHistologicProto_ResultLabStudy,
				EMHP.EvnMorfoHistologicProto_DiagPathology,
				EMHP.DeathSvid_id,
				EMHP.Diag_vid,
				EMHP.Diag_vid_Descr,
				EMHP.Diag_wid,
				EMHP.Diag_wid_Descr,
				EMHP.Diag_xid,
				EMHP.Diag_xid_Descr,
				EMHP.Diag_yid,
				EMHP.Diag_yid_Descr,
				EMHP.Diag_zid,
				EMHP.Diag_zid_Descr,
				EMHP.EvnMorfoHistologicProto_Epicrisis,
				EMHP.MedPersonal_aid,
				EMHP.MedPersonal_zid,
				EMHP.EvnMorfoHistologicProto_BrainWeight,
				EMHP.EvnMorfoHistologicProto_HeartWeight,
				EMHP.EvnMorfoHistologicProto_LungsWeight,
				EMHP.EvnMorfoHistologicProto_LiverWeight,
				EMHP.EvnMorfoHistologicProto_SpleenWeight,
				EMHP.EvnMorfoHistologicProto_KidneyLeftWeight,
				EMHP.EvnMorfoHistologicProto_KidneyRightWeight,
				EMHP.EvnMorfoHistologicProto_BitCount,
				EMHP.EvnMorfoHistologicProto_BlockCount,
				EMHP.EvnMorfoHistologicProto_MethodDescr,
				EMHP.EvnMorfoHistologicProto_ProtocolDescr,
				EMHP.PntDeathSvid_id,
				EMHP.PathologicCategoryType_id,
				ISNULL(EMHP.pmUser_updID, EMHP.pmUser_insID) as pmUser_id
			from v_EvnMorfoHistologicProto EMHP with (nolock)
			where EMHP.EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id
				and EMHP.Lpu_id = :Lpu_id
				and ISNULL(EMHP.EvnMorfoHistologicProto_IsBad, 1) <> :EvnMorfoHistologicProto_IsBad
		";

		$queryParams = array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
			'EvnMorfoHistologicProto_IsBad' => $data['EvnMorfoHistologicProto_IsBad'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение данных о протоколе)';
			return $response;
		}

		$res = $result->result('array');

		if ( !is_array($res) || count($res) == 0 ) {
			$response['Error_Msg'] = 'Ошибка при получении данных о протоколе';
			return $response;
		}

		$res[0]['EvnMorfoHistologicProto_IsBad'] = $data['EvnMorfoHistologicProto_IsBad'];
		$res[0]['pmUser_pid'] = $data['pmUser_pid'];

		$res = $this->saveEvnMorfoHistologicProto($res[0]);

		if ( !is_array($res) || count($res) == 0 ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (снятие/установка признака испорченного протокола)';
			return $response;
		}

		if ( strlen($res[0]['Error_Msg']) > 0 ) {
			$response['Error_Msg'] = $res[0]['Error_Msg'];
			return $response;
		}
		
		$response['success'] = true;

		return $response;
	}

	/**
	 * Получение данных для грида
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnMorfoHistologicDiagDiscrepancyGrid($data) {
		$query = "
			select
				EMHDD.EvnMorfoHistologicDiagDiscrepancy_id,
				DCET.DiagClinicalErrType_id,
				DRD.DiagReasonDiscrepancy_id,
				ISNULL(DCET.DiagClinicalErrType_Name, '') as DiagClinicalErrType_Name,
				ISNULL(DRD.DiagReasonDiscrepancy_Name, '') as DiagReasonDiscrepancy_Name,
				ISNULL(EMHDD.EvnMorfoHistologicDiagDiscrepancy_Note, '') as EvnMorfoHistologicDiagDiscrepancy_Note,
				1 as RecordStatus_Code
			from
				v_EvnMorfoHistologicDiagDiscrepancy EMHDD with (nolock)
				inner join DiagClinicalErrType DCET with (nolock) on DCET.DiagClinicalErrType_id = EMHDD.DiagClinicalErrType_id
				inner join DiagReasonDiscrepancy DRD with (nolock) on DRD.DiagReasonDiscrepancy_id = EMHDD.DiagReasonDiscrepancy_id
			where (1 = 1)
				and EMHDD.EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id
		";

		$result = $this->db->query($query, array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id']
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
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnMorfoHistologicMemberGrid($data) {
		$query = "
			select
				EMHM.EvnMorfoHistologicMember_id,
				L.Lpu_id,
				MSF.MedStaffFact_id,
				MSF.MedPersonal_TabCode as MedPersonal_Code,
				ISNULL(MSF.Person_FIO, '') as MedPersonal_Fio,
				ISNULL(L.Lpu_Nick, '') as Lpu_Name,
				1 as RecordStatus_Code
			from
				v_EvnMorfoHistologicMember EMHM with (nolock)
				inner join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = EMHM.MedStaffFact_id
				inner join v_Lpu L with (nolock) on L.Lpu_id = MSF.Lpu_id
			where (1 = 1)
				and EMHM.EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id
		";

		$result = $this->db->query($query, array(
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id']
		));
		
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
	function saveEvnMorfoHistologicDiagDiscrepancy($data) {
		$procedure = "p_EvnMorfoHistologicDiagDiscrepancy_ins";

		if ( $data['EvnMorfoHistologicDiagDiscrepancy_id'] > 0 ) {
			$procedure = "p_EvnMorfoHistologicDiagDiscrepancy_upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMorfoHistologicDiagDiscrepancy_id;

			exec " . $procedure . "
				@EvnMorfoHistologicDiagDiscrepancy_id = @Res output,
				@EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id,
				@DiagClinicalErrType_id = :DiagClinicalErrType_id,
				@DiagReasonDiscrepancy_id = :DiagReasonDiscrepancy_id,
				@EvnMorfoHistologicDiagDiscrepancy_Note = :EvnMorfoHistologicDiagDiscrepancy_Note,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnMorfoHistologicDiagDiscrepancy_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnMorfoHistologicDiagDiscrepancy_id' => ($data['EvnMorfoHistologicDiagDiscrepancy_id'] > 0 ? $data['EvnMorfoHistologicDiagDiscrepancy_id'] : NULL),
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
			'DiagClinicalErrType_id' => $data['DiagClinicalErrType_id'],
			'DiagReasonDiscrepancy_id' => $data['DiagReasonDiscrepancy_id'],
			'EvnMorfoHistologicDiagDiscrepancy_Note' => $data['EvnMorfoHistologicDiagDiscrepancy_Note'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение ошибки клинической диагностики)'));
		}
	}

	/**
	 * Сохранение
	 */
	function saveEvnMorfoHistologicMember($data) {
		$procedure = "p_EvnMorfoHistologicMember_ins";

		if ( $data['EvnMorfoHistologicMember_id'] > 0 ) {
			$procedure = "p_EvnMorfoHistologicMember_upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnMorfoHistologicMember_id;

			exec " . $procedure . "
				@EvnMorfoHistologicMember_id = @Res output,
				@EvnMorfoHistologicProto_id = :EvnMorfoHistologicProto_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnMorfoHistologicMember_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'EvnMorfoHistologicMember_id' => ($data['EvnMorfoHistologicMember_id'] > 0 ? $data['EvnMorfoHistologicMember_id'] : NULL),
			'EvnMorfoHistologicProto_id' => $data['EvnMorfoHistologicProto_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение присутствовавшего при вскрытии)'));
		}
	}
}
?>