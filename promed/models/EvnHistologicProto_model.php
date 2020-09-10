<?php
class EvnHistologicProto_model extends swModel {
	private $_ignorePrescrReactionType = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление
	 */
	function deleteEvnHistologicProto($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnHistologicProto_del
				@EvnHistologicProto_id = :EvnHistologicProto_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление протокола патологогистологического исследования)'));
		}
	}


	/**
	 * Получение данных
	 */
	function getEvnHistologicMicroData($data) {
		$query = "
			select
				RTRIM(ISNULL(EHM.EvnHistologicMicro_Descr, '')) as EvnHistologicMicro_Descr,
				RTRIM(ISNULL(HSP.HistologicSpecimenPlace_Name, '')) as HistologicSpecimenPlace_Name,
				RTRIM(ISNULL(HSS.HistologicSpecimenSaint_Name, '')) as HistologicSpecimenSaint_Name
			from v_EvnHistologicMicro EHM with (nolock)
				inner join HistologicSpecimenPlace HSP with (nolock) on HSP.HistologicSpecimenPlace_id = EHM.HistologicSpecimenPlace_id
				inner join HistologicSpecimenSaint HSS with (nolock) on HSS.HistologicSpecimenSaint_id = EHM.HistologicSpecimenSaint_id
			where EHM.EvnHistologicProto_id = :EvnHistologicProto_id
		";
		$result = $this->db->query($query, array('EvnHistologicProto_id' => $data['EvnHistologicProto_id']));

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
	function getEvnHistologicProtoNumber($data) {
		$query = "
			declare @GenId bigint;
			exec xp_GenpmID
				@ObjectName = 'EvnHistologicProto',
				@Lpu_id = :Lpu_id,
				@ObjectID = @GenId output;
			select @GenId as EvnHistologicProto_Num, '' as Error_Msg;
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
	 *	Получение номера протокола из нумератора
	 */
	function getEvnHistologicProtoSerNum($data, $numerator = null) {
		$params = array(
			'NumeratorObject_SysName' => 'EvnHistologicProto',
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$name = 'Протокол патологогистологического исследования';

		if (!empty($data['showOnly']) && $data['showOnly']) {
			$params['showOnly'] = true;
		}

		$this->load->model('Numerator_model');

		if (!empty($data['generateNew'])) {
			$params['showOnly'] = false;
			$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);
			$params['showOnly'] = true;
		} else {
			$resp = $this->Numerator_model->getNumeratorNum($params, $numerator);
		}

		if (!empty($resp['Numerator_Num'])) {
			return $resp;
		} else {
			if (!empty($resp['Error_Msg'])) {
				return array('Error_Msg' => $resp['Error_Msg'], 'success' => false);
			}
			return array('Error_Msg' => 'Не задан активный нумератор для "'.$name.'". Обратитесь к администратору системы.', 'Error_Code' => 'numerator404');
		}
	}

	/**
	 * Получение данных для грида
	 */
	function loadEvnHistologicProtoGrid($data) {
		$filter = "(1 = 1)";
		$queryd="";
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
		if ( (strlen($data['PT_Diag_Code_From']) > 0) || (strlen($data['PT_Diag_Code_To']) > 0) ) {
			$queryd .= " inner join Diag PTDiag with (nolock) on PTDiag.Diag_id = EHP.Diag_id ";

			if ( strlen($data['PT_Diag_Code_From']) > 0 ) {
				$filter .= " and PTDiag.Diag_Code >= :PT_Diag_Code_From
					";
				$queryParams['PT_Diag_Code_From'] = $data['PT_Diag_Code_From'];
			}

			if ( strlen($data['PT_Diag_Code_To']) > 0 ) {
				$filter .= " and PTDiag.Diag_Code <= :PT_Diag_Code_To
					";
				$queryParams['PT_Diag_Code_To'] = $data['PT_Diag_Code_To'];
			}
		}
		if ( (strlen($data['minAge']) > 0) || (strlen($data['maxAge']) > 0) ){
			if ( $data['minAge'] > 0 ) {
				$filter .= " and dbo.Age2(PS.Person_BirthDay, cast('".date('Y-m-d')."' as datetime)) >= :minAge
					 ";
				$queryParams['minAge'] = $data['minAge'];
			}
			if ( $data['maxAge'] > 0 ) {
				$filter .= " and dbo.Age2(PS.Person_BirthDay, cast('".date('Y-m-d')."' as datetime)) <= :maxAge
					";
				$queryParams['maxAge'] = $data['maxAge'];
			}
		}
		if ( !empty($data['didRangeStart']) || !empty($data['didRangeEnd']) ){
			if ( strlen($data['didRangeStart']) > 0 ) {
				$filter .= " and EHP.EvnHistologicProto_didDate >= :didRangeStart
					";
				$queryParams['didRangeStart'] = $data['didRangeStart'];
				}
			if ( strlen($data['didRangeEnd']) > 0 ) {
				$filter .= " and EHP.EvnHistologicProto_didDate <= :didRangeEnd
					";
				$queryParams['didRangeEnd'] = $data['didRangeEnd'];
			}
		}
		if ( !empty($data['setRangeStart']) || !empty($data['setRangeEnd']) ){
			if ( strlen($data['setRangeStart']) > 0 ) {
				$filter .= " and EHP.EvnHistologicProto_setDate >= :setRangeStart
					";
				$queryParams['setRangeStart'] = $data['setRangeStart'];
				}
			if ( strlen($data['setRangeEnd']) > 0 ) {
				$filter .= " and EHP.EvnHistologicProto_setDate <= :setRangeEnd
					";
				$queryParams['setRangeEnd'] = $data['setRangeEnd'];
			}
		}

		if ( empty($data['session']['medpersonal_id']) ) {
			$filter .= " and EHP.Lpu_id = :Lpu_id";
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
							(isSuperAdmin.pmUserCacheGroup_id is not null and EHP.Lpu_id = :Lpu_id)
							or (EHP.MedPersonal_id is null and (isPatb.MedServiceType_id is not null and EHP.Lpu_id = :Lpu_id))
							or (MedService.MedService_id is not null or LpuSection.LpuSection_id is not null)
						) 
					then 'edit' else 'view' end 
				as accessType,
				EHP.EvnHistologicProto_id,
				EHP.EvnDirectionHistologic_id,
				EHP.Person_id,
				EHP.PersonEvn_id,
				EHP.Server_id,
				EHP.Lpu_id,
				EHP.MedPersonal_id,
				EHP.EvnHistologicProto_Ser,
				EHP.EvnHistologicProto_Num,
				convert(varchar(10), EHP.EvnHistologicProto_setDT, 104) as EvnHistologicProto_setDate,
				convert(varchar(10), EHP.EvnHistologicProto_didDT, 104) as EvnHistologicProto_didDate,
				RTRIM(coalesce(OrgLpu.Org_Nick, Org.Org_Nick, '')) as Lpu_Name,
				RTRIM(ISNULL(LS.LpuSection_Name, '')) as LpuSection_Name,
				RTRIM(ISNULL(EDH.EvnDirectionHistologic_NumCard, '')) as EvnDirectionHistologic_NumCard,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				IsBad.YesNo_Code as EvnHistologicProto_IsBad
				-- end select
			from
				-- from
				v_EvnHistologicProto EHP with (nolock)
				inner join v_EvnDirectionHistologic EDH with (nolock) on EDH.EvnDirectionHistologic_id = EHP.EvnDirectionHistologic_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = EHP.Person_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EDH.LpuSection_did
				left join Lpu Lpu with (nolock) on Lpu.Lpu_id = EDH.Lpu_sid
				left join Org OrgLpu with (nolock) on OrgLpu.Org_id = Lpu.Org_id
				left join Org Org with (nolock) on Org.Org_id = EDH.Org_sid
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = EHP.MedPersonal_id
						and Lpu_id = EHP.Lpu_id
				) MP
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
					where MST.MedServiceType_SysNick = 'patb' and MSMP.MedPersonal_id = EHP.MedPersonal_id
						" . (count($med_personal_list)>0 ? "and MSMP1.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
				) as MedService
				outer apply (
					select top 1 MSS.LpuSection_id from v_MedServiceMedPersonal MSMP with (nolock)
					inner join v_MedServiceSection MSS with (nolock) on MSMP.MedService_id = MSS.MedService_id
					inner join v_MedStaffFact MSF with (nolock) on MSS.LpuSection_id = MSF.LpuSection_id
					inner join persis.v_Post P with (nolock) on P.id = MSF.Post_id
					where 
						MSMP.MedPersonal_id = EHP.MedPersonal_id
						" . (count($med_personal_list)>0 ? "and MSF.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
						and frmpEntry_id = 10235
				) as LpuSection
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EHP.EvnHistologicProto_IsBad
				 ".$queryd." 
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

		 //echo getDebugSQL($query, $queryParams); exit();

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
	 * Получение списка результатов исследований пациента для формы редактирования направление на патологогистологическое исследование
	 */
	public function loadEvnHistologicProtoList($data) {
		return $this->queryResult("
			select
				EHP.EvnHistologicProto_id,
				L.Lpu_Name,
				convert(varchar(10), EHP.EvnHistologicProto_setDT, 104) as EvnHistologicProto_setDate,
				EHP.EvnHistologicProto_Num,
				EHP.EvnHistologicProto_HistologicConclusion
			from
				v_EvnHistologicProto EHP with (nolock)
				inner join v_Lpu L with (nolock) on L.Lpu_id = EHP.Lpu_id
			where
				EHP.Person_id = :Person_id
			order by
				EHP.EvnHistologicProto_setDT
		", array(
			'Person_id' => $data['Person_id']
		));
	}


	/**
	 * Сохранение
	 */
	public function saveEvnHistologicProto($data) {
		$response = array(array('Error_Msg' => ''));

		try {
			$this->beginTransaction();

			if ( isset($data['EvnHistologicProto_setTime']) ) {
				$data['EvnHistologicProto_setDate'] .= ' ' . $data['EvnHistologicProto_setTime'] . ':00.000';
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnHistologicProto_id;
				exec p_EvnHistologicProto_" . (!empty($data['EvnHistologicProto_id']) ? "upd" : "ins") . "
					@EvnHistologicProto_id = @Res output,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnHistologicProto_setDT = :EvnHistologicProto_setDate,
					@EvnHistologicProto_didDT = :EvnHistologicProto_didDate,
					@EvnDirectionHistologic_id = :EvnDirectionHistologic_id,
					@EvnHistologicProto_CutDate = :EvnHistologicProto_CutDate,
					@EvnHistologicProto_CutTime = :EvnHistologicProto_CutTime,
					@EvnHistologicProto_BiopsyDT = :EvnHistologicProto_BiopsyDT,
					@EvnHistologicProto_Ser = :EvnHistologicProto_Ser,
					@EvnHistologicProto_Num = :EvnHistologicProto_Num,
					@EvnHistologicProto_IsDiag = :EvnHistologicProto_IsDiag,
					@EvnHistologicProto_IsOper = :EvnHistologicProto_IsOper,
					@EvnHistologicProto_BitCount = :EvnHistologicProto_BitCount,
					@EvnHistologicProto_BlockCount = :EvnHistologicProto_BlockCount,
					@EvnHistologicProto_MacroDescr = :EvnHistologicProto_MacroDescr,
					@EvnHistologicProto_CategoryDiff = :EvnHistologicProto_CategoryDiff,
					@EvnHistologicProto_HistologicConclusion = :EvnHistologicProto_HistologicConclusion,
					@Diag_id = :Diag_id,
					@MedPersonal_id = :MedPersonal_id,
					@MedPersonal_sid = :MedPersonal_sid,
					@MedStaffFact_id = :MedStaffFact_id,
					@EvnHistologicProto_IsBad = :EvnHistologicProto_IsBad,
					@EvnHistologicProto_IsDelivSolFormalin = :EvnHistologicProto_IsDelivSolFormalin,
					@EvnHistologicProto_IsPolluted = :EvnHistologicProto_IsPolluted,
					@MarkSavePack_id = :MarkSavePack_id,
					@EvnHistologicProto_Comments = :EvnHistologicProto_Comments,
					@OnkoDiag_id = :OnkoDiag_id,
					@pmUser_pid = :pmUser_pid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnHistologicProto_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnHistologicProto_setDate' => $data['EvnHistologicProto_setDate'],
				'EvnHistologicProto_didDate' => $data['EvnHistologicProto_didDate'],
				'EvnDirectionHistologic_id' => $data['EvnDirectionHistologic_id'],
				'EvnHistologicProto_CutDate' => $data['EvnHistologicProto_CutDate'],
				'EvnHistologicProto_CutTime' => $data['EvnHistologicProto_CutTime'],
				'EvnHistologicProto_BiopsyDT' => $data['EvnHistologicProtoBiopsy_setDate'].' ' . $data['EvnHistologicProtoBiopsy_setTime'],
				'EvnHistologicProto_Ser' => $data['EvnHistologicProto_Ser'],
				'EvnHistologicProto_Num' => $data['EvnHistologicProto_Num'],
				'EvnHistologicProto_IsDiag' => $data['EvnHistologicProto_IsDiag'],
				'EvnHistologicProto_IsOper' => $data['EvnHistologicProto_IsOper'],
				'EvnHistologicProto_BitCount' => $data['EvnHistologicProto_BitCount'],
				'EvnHistologicProto_BlockCount' => $data['EvnHistologicProto_BlockCount'],
				'EvnHistologicProto_MacroDescr' => $data['EvnHistologicProto_MacroDescr'],
				'EvnHistologicProto_CategoryDiff'=>$data['EvnHistologicProto_CategoryDiff'],
				'EvnHistologicProto_HistologicConclusion' => $data['EvnHistologicProto_HistologicConclusion'],
				'Diag_id' => $data['Diag_id'],
				'MedPersonal_id' => $data['MedPersonal_id'],
				'MedPersonal_sid' => $data['MedPersonal_sid'],
				'MedStaffFact_id' => $data['MedStaffFact_id'],
				'EvnHistologicProto_IsBad' => (!empty($data['EvnHistologicProto_IsBad']) ? $data['EvnHistologicProto_IsBad'] : NULL),
				'EvnHistologicProto_IsDelivSolFormalin' => (!empty($data['EvnHistologicProto_IsDelivSolFormalin']) ? $data['EvnHistologicProto_IsDelivSolFormalin'] : NULL),
				'EvnHistologicProto_IsPolluted' => (!empty($data['EvnHistologicProto_IsPolluted']) ? $data['EvnHistologicProto_IsPolluted'] : NULL),
				'MarkSavePack_id' => (!empty($data['MarkSavePack_id']) ? $data['MarkSavePack_id'] : NULL),
				'EvnHistologicProto_Comments' => (!empty($data['EvnHistologicProto_Comments']) ? $data['EvnHistologicProto_Comments'] : NULL),
				'OnkoDiag_id' => (!empty($data['OnkoDiag_id']) ? $data['OnkoDiag_id'] : NULL),
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

			$EvnHistologicProto_id = $response[0]['EvnHistologicProto_id'];

			if ( $this->_ignorePrescrReactionType == false ) {
				$PrescrReactionType_ids = $data['PrescrReactionType_ids'];

				$resp = $this->queryResult("
					select
						PrescrReactionTypeLink_id,
						PrescrReactionType_id
					from
						v_PrescrReactionTypeLink (nolock)
					where
						EvnHistologicProto_id = :EvnHistologicProto_id
				", array(
					'EvnHistologicProto_id' => $EvnHistologicProto_id
				));

				$prtArray = array();
				if ( !empty($PrescrReactionType_ids) && is_array($PrescrReactionType_ids) ) {
					foreach ( $PrescrReactionType_ids as $one ) {
						$prtArray[$one] = 1;
					}
				}

				foreach ( $resp as $respone ) {
					// удаляем лишние
					if ( !isset($prtArray[$respone['PrescrReactionType_id']]) ) {
						$resp_del = $this->queryResult("
							declare
								@ErrCode int,
								@ErrMessage varchar(4000);
				
							exec p_PrescrReactionTypeLink_del
								@PrescrReactionTypeLink_id = :PrescrReactionTypeLink_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMessage output;
				
							select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
						", array(
							'PrescrReactionTypeLink_id' => $respone['PrescrReactionTypeLink_id']
						));

						if ( !empty($resp_del[0]['Error_Msg']) ) {
							throw new Exception($resp_del[0]['Error_Msg']);
						}
					}
					else {
						unset($prtArray[$respone['PrescrReactionType_id']]);
					}
				}

				// добавляем новые
				foreach ( $prtArray as $PrescrReactionType_id => $count ) {
					$resp_save = $this->queryResult("
						declare
							@ErrCode int,
							@ErrMessage varchar(4000),
							@Res bigint;
			
						exec p_PrescrReactionTypeLink_ins
							@PrescrReactionTypeLink_id = @Res output,
							@EvnHistologicProto_id = :EvnHistologicProto_id,
							@PrescrReactionType_id = :PrescrReactionType_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
			
						select @Res as PrescrReactionTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					", array(
						'PrescrReactionType_id' => $PrescrReactionType_id,
						'EvnHistologicProto_id' => $EvnHistologicProto_id,
						'pmUser_id' => $this->promedUserId
					));

					if ( !empty($resp_save[0]['Error_Msg']) ) {
						throw new Exception($resp_save[0]['Error_Msg']);
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
	 * Получение полей
	 */
	function getEvnHistologicProtoFields($data) {
		$query = "
			select top 1
				RTRIM(ISNULL(Diag.Diag_Code, '')) as Diag_Code,
				ISNULL(EHP.EvnHistologicProto_BitCount, 0) as EvnHistologicProto_BitCount,
				ISNULL(EHP.EvnHistologicProto_BlockCount, 0) as EvnHistologicProto_BlockCount,
				DAY(EHP.EvnHistologicProto_didDT) as EvnHistologicProto_didDay,
				MONTH(EHP.EvnHistologicProto_didDT) as EvnHistologicProto_didMonth,
				YEAR(EHP.EvnHistologicProto_didDT) as EvnHistologicProto_didYear,
				RTRIM(ISNULL(EHP.EvnHistologicProto_HistologicConclusion, '')) as EvnHistologicProto_HistologicConclusion,
				RTRIM(ISNULL(EHPID.YesNo_Name, '')) as EvnHistologicProto_IsDiag,
				RTRIM(ISNULL(EHPIO.YesNo_Name, '')) as EvnHistologicProto_IsOper,
				RTRIM(ISNULL(EHPIU.YesNo_Name, '')) as EvnHistologicProto_IsUrgent,
				RTRIM(ISNULL(EHP.EvnHistologicProto_MacroDescr, '')) as EvnHistologicProto_MacroDescr,
				RTRIM(ISNULL(EHP.EvnHistologicProto_Num, '')) as EvnHistologicProto_Num,
				RTRIM(ISNULL(EHP.EvnHistologicProto_Ser, '')) as EvnHistologicProto_Ser,
				convert(varchar(10), EHP.EvnHistologicProto_setDT, 104) as EvnHistologicProto_setDate,
				EHP.EvnHistologicProto_setTime,
				RTRIM(ISNULL(MP.Person_Fio, '')) as MedPersonal_Fio,
				RTRIM(ISNULL(MPS.Person_Fio, '')) as MedPersonalS_Fio
			from v_EvnHistologicProto EHP with (nolock)
				inner join v_EvnDirectionHistologic EDH with (nolock) on EDH.EvnDirectionHistologic_id = EHP.EvnDirectionHistologic_id
				inner join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EHP.MedPersonal_id
					and MP.Lpu_id = EHP.Lpu_id
				left join v_MedPersonal MPS with (nolock) on MPS.MedPersonal_id = EHP.MedPersonal_sid
					and MPS.Lpu_id = EHP.Lpu_id
				left join Diag with (nolock) on Diag.Diag_id = EHP.Diag_id
				left join YesNo EHPID with (nolock) on EHPID.YesNo_id = EHP.EvnHistologicProto_IsDiag
				left join YesNo EHPIO with (nolock) on EHPIO.YesNo_id = EHP.EvnHistologicProto_IsOper
				left join YesNo EHPIU with (nolock) on EHPIU.YesNo_id = EDH.EvnDirectionHistologic_IsUrgent
			where EHP.EvnHistologicProto_id = :EvnHistologicProto_id
		";
		// @task https://redmine.swan.perm.ru/issues/93571
		// Убрал из запроса условие на МО
		//		and (EHP.Lpu_id = :Lpu_id or EDH.Lpu_id = :Lpu_id)
		$result = $this->db->query($query, array(
			'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
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
	function loadEvnHistologicProtoEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		//case when EHP.Lpu_id = :Lpu_id " . (count($med_personal_list)>0 ? "and EHP.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . " then 'edit' else 'edit' end as accessType,
		$query = "
			select top 1
				case when EHP.Lpu_id = :Lpu_id then 'edit' else 'view' end as accessType,
				--'edit' as accessType,
				EHP.EvnHistologicProto_id,
				EDH.EvnDirectionHistologic_id,
				EHP.Person_id,
				EHP.Server_id,
				EHP.PersonEvn_id,
				LTRIM(RTRIM(ISNULL(EDH.EvnDirectionHistologic_Ser, '') + ' ' + ISNULL(cast(EDH.EvnDirectionHistologic_Num as varchar(10)), '') + ', ' + ISNULL(convert(varchar(10), EDH.EvnDirectionHistologic_setDate, 104), ''))) as EvnDirectionHistologic_SerNum,
				RTRIM(ISNULL(EHP.EvnHistologicProto_Ser, '')) as EvnHistologicProto_Ser,
				RTRIM(ISNULL(cast(EHP.EvnHistologicProto_Num as varchar(10)), '')) as EvnHistologicProto_Num,
				convert(varchar(10), EHP.EvnHistologicProto_setDate, 104) as EvnHistologicProto_setDate,
				convert(varchar(10), EHP.EvnHistologicProto_CutDate, 104) as EvnHistologicProto_CutDate,
				left(cast(EHP.EvnHistologicProto_CutTime as time),5) as EvnHistologicProto_CutTime,
				convert(varchar(10),convert(date,EHP.EvnHistologicProto_BiopsyDT), 104) as EvnHistologicProtoBiopsy_setDate,
				left(cast(convert(time,EHP.EvnHistologicProto_BiopsyDT)as time),5) as EvnHistologicProtoBiopsy_setTime,
				EHP.EvnHistologicProto_setTime,
				EHP.EvnHistologicProto_IsDiag,
				EHP.EvnHistologicProto_IsOper,
				EHP.EvnHistologicProto_BitCount,
				EHP.EvnHistologicProto_BlockCount,
				EHP.EvnHistologicProto_MacroDescr,
				EHP.EvnHistologicProto_HistologicConclusion,
				EHP.EvnHistologicProto_CategoryDiff,
				EHP.Diag_id,
				convert(varchar(10), EHP.EvnHistologicProto_didDate, 104) as EvnHistologicProto_didDate,
				EHP.MedPersonal_id,
				EHP.MedPersonal_sid,
				EHP.MedStaffFact_id,
				EHP.EvnHistologicProto_IsDelivSolFormalin,
				EHP.EvnHistologicProto_IsPolluted,
				EHP.MarkSavePack_id,
				EHP.EvnHistologicProto_Comments,
				EHP.OnkoDiag_id,
				PRTL.PrescrReactionType_ids,
				RTRIM(LTRIM(ISNULL(pmUserCache.pmUser_Name, ''))) as pmUser_Name
			from
				v_EvnHistologicProto EHP with (nolock)
				inner join v_EvnDirectionHistologic EDH with (nolock) on EDH.EvnDirectionHistologic_id = EHP.EvnDirectionHistologic_id
				left join YesNo IsBad with (nolock) on IsBad.YesNo_id = EHP.EvnHistologicProto_IsBad
				left join pmUserCache with (nolock) on pmUserCache.pmUser_id = EHP.pmUser_pid
				outer apply (
					SELECT STUFF(
					(
						select
							',' + cast(PrescrReactionType_id as varchar)
						from
							v_PrescrReactionTypeLink (nolock)
						where
							EvnHistologicProto_id = EHP.EvnHistologicProto_id
						FOR XML PATH ('')
					), 1, 1, '') as PrescrReactionType_ids
				) PRTL
			where (1 = 1)
				and EHP.EvnHistologicProto_id = :EvnHistologicProto_id
				and (EHP.Lpu_id = :Lpu_id or EDH.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		/*echo getDebugSQL($query, array(
			'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
			'Lpu_id' => $data['Lpu_id']
		)); die;*/
		$result = $this->db->query($query, array(
			'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
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
	 * Установление испорченности
	 */
	function setEvnHistologicProtoIsBad($data) {
		$this->_ignorePrescrReactionType = true;

		$response = array(
			'success' => false,
			'Error_Msg' => ''
		);
		
		$ret = array();
		
		// Получаем данные по протоколу
		$query = "
			select top 1
				EHP.EvnHistologicProto_id,
				EHP.Lpu_id,
				EHP.Server_id,
				EHP.PersonEvn_id,
				convert(varchar(10), EHP.EvnHistologicProto_setDT, 120) as EvnHistologicProto_setDate,
				convert(varchar(10), EHP.EvnHistologicProto_didDT, 120) as EvnHistologicProto_didDate,
				EHP.EvnDirectionHistologic_id,
				RTRIM(ISNULL(EHP.EvnHistologicProto_Ser, '')) as EvnHistologicProto_Ser,
				RTRIM(ISNULL(EHP.EvnHistologicProto_Num, '')) as EvnHistologicProto_Num,
				EHP.EvnHistologicProto_IsDiag,
				EHP.EvnHistologicProto_IsOper,
				EHP.EvnHistologicProto_BitCount,
				EHP.EvnHistologicProto_BlockCount,
				EHP.EvnHistologicProto_MacroDescr,
				EHP.EvnHistologicProto_HistologicConclusion,
				EHP.EvnHistologicProto_CategoryDiff,
				EHP.Diag_id,
				EHP.MedPersonal_id,
				EHP.MedPersonal_sid,
				EHP.EvnHistologicProto_IsDelivSolFormalin,
				EHP.EvnHistologicProto_IsDelivSolFormalin,
				EHP.EvnHistologicProto_IsPolluted,
				EHP.MarkSavePack_id,
				EHP.EvnHistologicProto_Comments,
				EHP.OnkoDiag_id,
				convert(varchar(10), EHP.EvnHistologicProto_CutDate, 104) as EvnHistologicProto_CutDate,
				left(cast(EHP.EvnHistologicProto_CutTime as time),5) as EvnHistologicProto_CutTime,
				ISNULL(EHP.pmUser_updID, EHP.pmUser_insID) as pmUser_id
			from v_EvnHistologicProto EHP with (nolock)
			where EHP.EvnHistologicProto_id = :EvnHistologicProto_id
				and EHP.Lpu_id = :Lpu_id
				and ISNULL(EHP.EvnHistologicProto_IsBad, 1) <> :EvnHistologicProto_IsBad
		";

		$queryParams = array(
			'EvnHistologicProto_id' => $data['EvnHistologicProto_id'],
			'EvnHistologicProto_IsBad' => $data['EvnHistologicProto_IsBad'],
			'Lpu_id' => $data['Lpu_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (получение данных о протоколе)';
			$ret[0] = $response;
			return $ret;
		}

		$res = $result->result('array');

		if ( !is_array($res) || count($res) == 0 ) {
			$response['Error_Msg'] = 'Ошибка при получении данных о протоколе';
			$ret[0] = $response;
			return $ret;
		}

		$res[0]['EvnHistologicProto_IsBad'] = $data['EvnHistologicProto_IsBad'];
		$res[0]['pmUser_pid'] = $data['pmUser_pid'];

		$res = $this->saveEvnHistologicProto($res[0]);

		if ( !is_array($res) || count($res) == 0 ) {
			$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (снятие/установка признака испорченного протокола)';
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