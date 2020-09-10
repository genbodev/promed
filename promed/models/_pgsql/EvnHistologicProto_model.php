<?php
class EvnHistologicProto_model extends swPgModel {
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
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnHistologicProto_del(
				EvnHistologicProto_id := :EvnHistologicProto_id,
				pmUser_id := :pmUser_id
			)
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
				RTRIM(coalesce(EHM.EvnHistologicMicro_Descr, '')) as \"EvnHistologicMicro_Descr\",
				RTRIM(coalesce(HSP.HistologicSpecimenPlace_Name, '')) as \"HistologicSpecimenPlace_Name\",
				RTRIM(coalesce(HSS.HistologicSpecimenSaint_Name, '')) as \"HistologicSpecimenSaint_Name\"
			from v_EvnHistologicMicro EHM
				inner join HistologicSpecimenPlace HSP on HSP.HistologicSpecimenPlace_id = EHM.HistologicSpecimenPlace_id
				inner join HistologicSpecimenSaint HSS on HSS.HistologicSpecimenSaint_id = EHM.HistologicSpecimenSaint_id
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
			select
				objectid as \"EvnHistologicProto_Num\"
			from xp_GenpmID(
				ObjectName := 'EvnHistologicProto',
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
		if ( (strlen($data['PT_Diag_Code_From']) > 0) || (strlen($data['PT_Diag_Code_To']) > 0) ) {
			$queryd .= " inner join Diag PTDiag on PTDiag.Diag_id = EHP.Diag_id ";

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
				$filter .= " and dbo.Age2(PS.Person_BirthDay, cast('".date('Y-m-d')."' as timestamp)) >= :minAge
					 ";
				$queryParams['minAge'] = $data['minAge'];
			}
			if ( $data['maxAge'] > 0 ) {
				$filter .= " and dbo.Age2(PS.Person_BirthDay, cast('".date('Y-m-d')."' as timestamp)) <= :maxAge
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
						coalesce(IsBad.YesNo_Code, 0) = 0 
						and (
							(isSuperAdmin.pmUserCacheGroup_id is not null and EHP.Lpu_id = :Lpu_id)
							or (EHP.MedPersonal_id is null and (isPatb.MedServiceType_id is not null and EHP.Lpu_id = :Lpu_id))
							or (MedService.MedService_id is not null or LpuSection.LpuSection_id is not null)
						) 
					then 'edit' else 'view' end 
				as \"accessType\",
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				EHP.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				EHP.Person_id as \"Person_id\",
				EHP.PersonEvn_id as \"PersonEvn_id\",
				EHP.Server_id as \"Server_id\",
				EHP.Lpu_id as \"Lpu_id\",
				EHP.MedPersonal_id as \"MedPersonal_id\",
				EHP.EvnHistologicProto_Ser as \"EvnHistologicProto_Ser\",
				EHP.EvnHistologicProto_Num as \"EvnHistologicProto_Num\",
				to_char(EHP.EvnHistologicProto_setDT, 'dd.mm.yyyy') as \"EvnHistologicProto_setDate\",
				to_char(EHP.EvnHistologicProto_didDT, 'dd.mm.yyyy') as \"EvnHistologicProto_didDate\",
				RTRIM(coalesce(OrgLpu.Org_Nick, Org.Org_Nick, '')) as \"Lpu_Name\",
				RTRIM(coalesce(LS.LpuSection_Name, '')) as \"LpuSection_Name\",
				RTRIM(coalesce(EDH.EvnDirectionHistologic_NumCard, '')) as \"EvnDirectionHistologic_NumCard\",
				RTRIM(coalesce(PS.Person_Surname, '')) as \"Person_Surname\",
				RTRIM(coalesce(PS.Person_Firname, '')) as \"Person_Firname\",
				RTRIM(coalesce(PS.Person_Secname, '')) as \"Person_Secname\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				RTRIM(coalesce(MP.Person_Fio, '')) as \"MedPersonal_Fio\",
				IsBad.YesNo_Code as \"EvnHistologicProto_IsBad\"
				-- end select
			from
				-- from
				v_EvnHistologicProto EHP
				inner join v_EvnDirectionHistologic EDH on EDH.EvnDirectionHistologic_id = EHP.EvnDirectionHistologic_id
				inner join v_PersonState PS on PS.Person_id = EHP.Person_id
				left join LpuSection LS on LS.LpuSection_id = EDH.LpuSection_did
				left join Lpu Lpu on Lpu.Lpu_id = EDH.Lpu_sid
				left join Org OrgLpu on OrgLpu.Org_id = Lpu.Org_id
				left join Org Org on Org.Org_id = EDH.Org_sid
				left join lateral(
					select Person_Fio
					from v_MedPersonal
					where MedPersonal_id = EHP.MedPersonal_id
						and Lpu_id = EHP.Lpu_id
					limit 1
				) MP on true
				left join lateral (
					select UCG.pmUserCacheGroup_id from dbo.pmUserCache UC
					left join pmUserCacheGroupLink UCGL on UC.PMUser_id = UCGL.pmUserCache_id
					left join pmUserCacheGroup UCG on UCG.pmUserCacheGroup_id = UCGL.pmUserCacheGroup_id
					where UC.PMUser_id = :PMUser_id and UCG.pmUserCacheGroup_Code = 'SuperAdmin'
					limit 1
				) as isSuperAdmin on true
				left join lateral (
					select MS.MedServiceType_id from v_MedServiceMedPersonal MSMP
					inner join v_MedService MS on MSMP.MedService_id = MS.MedService_id
					inner join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
					where MST.MedServiceType_SysNick = 'patb' 
					and MSMP.MedPersonal_id in (".(count($med_personal_list)>0?implode(',',$med_personal_list):"null").")
					limit 1
				) as isPatb on true
				left join lateral (
					select MS.MedService_id from v_MedServiceMedPersonal MSMP
					inner join v_MedService MS on MSMP.MedService_id = MS.MedService_id
					inner join v_MedServiceType MST on MST.MedServiceType_id = MS.MedServiceType_id
					left join v_MedServiceMedPersonal MSMP1 on MSMP1.MedService_id = MS.MedService_id
					where MST.MedServiceType_SysNick = 'patb' and MSMP.MedPersonal_id = EHP.MedPersonal_id
						" . (count($med_personal_list)>0 ? "and MSMP1.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
					limit 1
				) as MedService on true
				left join lateral (
					select MSS.LpuSection_id from v_MedServiceMedPersonal MSMP
					inner join v_MedServiceSection MSS on MSMP.MedService_id = MSS.MedService_id
					inner join v_MedStaffFact MSF on MSS.LpuSection_id = MSF.LpuSection_id
					inner join persis.v_Post P on P.id = MSF.Post_id
					where 
						MSMP.MedPersonal_id = EHP.MedPersonal_id
						" . (count($med_personal_list)>0 ? "and MSF.MedPersonal_id in (".implode(',',$med_personal_list).")" : "") . "
						and frmpEntry_id = 10235
					limit 1
				) as LpuSection on true
				left join YesNo IsBad on IsBad.YesNo_id = EHP.EvnHistologicProto_IsBad
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
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				L.Lpu_Name as \"Lpu_Name\",
				to_char(EHP.EvnHistologicProto_setDT, 'dd.mm.yyyy') as \"EvnHistologicProto_setDate\",
				EHP.EvnHistologicProto_Num as \"EvnHistologicProto_Num\",
				EHP.EvnHistologicProto_HistologicConclusion as \"EvnHistologicProto_HistologicConclusion\"
			from
				v_EvnHistologicProto EHP
				inner join v_Lpu L on L.Lpu_id = EHP.Lpu_id
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
				select
					EvnHistologicProto_id as \"EvnHistologicProto_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_EvnHistologicProto_" . (!empty($data['EvnHistologicProto_id']) ? "upd" : "ins") . "(
					EvnHistologicProto_id := :EvnHistologicProto_id,
					Lpu_id := :Lpu_id,
					Server_id := :Server_id,
					PersonEvn_id := :PersonEvn_id,
					EvnHistologicProto_setDT := :EvnHistologicProto_setDate,
					EvnHistologicProto_didDT := :EvnHistologicProto_didDate,
					EvnDirectionHistologic_id := :EvnDirectionHistologic_id,
					EvnHistologicProto_CutDate := :EvnHistologicProto_CutDate,
					EvnHistologicProto_CutTime := :EvnHistologicProto_CutTime,
					EvnHistologicProto_BiopsyDT := :EvnHistologicProto_BiopsyDT,
					EvnHistologicProto_Ser := :EvnHistologicProto_Ser,
					EvnHistologicProto_Num := :EvnHistologicProto_Num,
					EvnHistologicProto_IsDiag := :EvnHistologicProto_IsDiag,
					EvnHistologicProto_IsOper := :EvnHistologicProto_IsOper,
					EvnHistologicProto_BitCount := :EvnHistologicProto_BitCount,
					EvnHistologicProto_BlockCount := :EvnHistologicProto_BlockCount,
					EvnHistologicProto_MacroDescr := :EvnHistologicProto_MacroDescr,
					EvnHistologicProto_CategoryDiff := :EvnHistologicProto_CategoryDiff,
					EvnHistologicProto_HistologicConclusion := :EvnHistologicProto_HistologicConclusion,
					Diag_id := :Diag_id,
					MedPersonal_id := :MedPersonal_id,
					MedPersonal_sid := :MedPersonal_sid,
					MedStaffFact_id := :MedStaffFact_id,
					EvnHistologicProto_IsBad := :EvnHistologicProto_IsBad,
					EvnHistologicProto_IsDelivSolFormalin := :EvnHistologicProto_IsDelivSolFormalin,
					EvnHistologicProto_IsPolluted := :EvnHistologicProto_IsPolluted,
					MarkSavePack_id := :MarkSavePack_id,
					EvnHistologicProto_Comments := :EvnHistologicProto_Comments,
					OnkoDiag_id := :OnkoDiag_id,
					pmUser_pid := :pmUser_pid,
					pmUser_id := :pmUser_id
				)
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
				'EvnHistologicProto_BiopsyDT' => (!empty($data['EvnHistologicProtoBiopsy_setDate']) && !empty($data['EvnHistologicProtoBiopsy_setTime']) ? $data['EvnHistologicProtoBiopsy_setDate'].' '.$data['EvnHistologicProtoBiopsy_setTime']: NULL),
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
				'MedStaffFact_id' => $data['MedStaffFact_id'] ?? null,
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
						PrescrReactionTypeLink_id as \"PrescrReactionTypeLink_id\",
						PrescrReactionType_id as \"PrescrReactionType_id\"
					from
						v_PrescrReactionTypeLink
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
							select
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Msg\"
							from p_PrescrReactionTypeLink_del(
								PrescrReactionTypeLink_id := :PrescrReactionTypeLink_id
							)
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
						select
							PrescrReactionTypeLink_id as \"PrescrReactionTypeLink_id\",
							Error_Code as \"Error_Code\",
							Error_Message as \"Error_Msg\"
						from p_PrescrReactionTypeLink_ins(
							EvnHistologicProto_id := :EvnHistologicProto_id,
							PrescrReactionType_id := :PrescrReactionType_id,
							pmUser_id := :pmUser_id
						)
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
			select
				RTRIM(coalesce(Diag.Diag_Code, '')) as \"Diag_Code\",
				coalesce(EHP.EvnHistologicProto_BitCount, 0) as \"EvnHistologicProto_BitCount\",
				coalesce(EHP.EvnHistologicProto_BlockCount, 0) as \"EvnHistologicProto_BlockCount\",
				date_part('day', EHP.EvnHistologicProto_didDT) as \"EvnHistologicProto_didDay\",
				date_part('month', EHP.EvnHistologicProto_didDT) as \"EvnHistologicProto_didMonth\",
				date_part('year', EHP.EvnHistologicProto_didDT) as \"EvnHistologicProto_didYear\",
				RTRIM(coalesce(EHP.EvnHistologicProto_HistologicConclusion, '')) as \"EvnHistologicProto_HistologicConclusion\",
				RTRIM(coalesce(EHPID.YesNo_Name, '')) as \"EvnHistologicProto_IsDiag\",
				RTRIM(coalesce(EHPIO.YesNo_Name, '')) as \"EvnHistologicProto_IsOper\",
				RTRIM(coalesce(EHPIU.YesNo_Name, '')) as \"EvnHistologicProto_IsUrgent\",
				RTRIM(coalesce(EHP.EvnHistologicProto_MacroDescr, '')) as \"EvnHistologicProto_MacroDescr\",
				RTRIM(coalesce(EHP.EvnHistologicProto_Num, '')) as \"EvnHistologicProto_Num\",
				RTRIM(coalesce(EHP.EvnHistologicProto_Ser, '')) as \"EvnHistologicProto_Ser\",
				to_char(EHP.EvnHistologicProto_setDT, 'dd.mm.yyyy') as \"EvnHistologicProto_setDate\",
				EHP.EvnHistologicProto_setTime as \"EvnHistologicProto_setTime\",
				RTRIM(coalesce(MP.Person_Fio, '')) as \"MedPersonal_Fio\",
				RTRIM(coalesce(MPS.Person_Fio, '')) as \"MedPersonalS_Fio\"
			from v_EvnHistologicProto EHP
				inner join v_EvnDirectionHistologic EDH on EDH.EvnDirectionHistologic_id = EHP.EvnDirectionHistologic_id
				inner join v_MedPersonal MP on MP.MedPersonal_id = EHP.MedPersonal_id
					and MP.Lpu_id = EHP.Lpu_id
				left join v_MedPersonal MPS on MPS.MedPersonal_id = EHP.MedPersonal_sid
					and MPS.Lpu_id = EHP.Lpu_id
				left join Diag on Diag.Diag_id = EHP.Diag_id
				left join YesNo EHPID on EHPID.YesNo_id = EHP.EvnHistologicProto_IsDiag
				left join YesNo EHPIO on EHPIO.YesNo_id = EHP.EvnHistologicProto_IsOper
				left join YesNo EHPIU on EHPIU.YesNo_id = EDH.EvnDirectionHistologic_IsUrgent
			where EHP.EvnHistologicProto_id = :EvnHistologicProto_id
			limit 1
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
			select
				case when EHP.Lpu_id = :Lpu_id then 'edit' else 'view' end as \"accessType\",
				--'edit' as accessType,
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				EDH.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				EHP.Person_id as \"Person_id\",
				EHP.Server_id as \"Server_id\",
				EHP.PersonEvn_id as \"PersonEvn_id\",
				LTRIM(RTRIM(coalesce(EDH.EvnDirectionHistologic_Ser, '') || ' ' || coalesce(EDH.EvnDirectionHistologic_Num, '') || ', ' || coalesce(to_char(EDH.EvnDirectionHistologic_setDate, 'dd.mm.yyyy'), ''))) as \"EvnDirectionHistologic_SerNum\",
				RTRIM(coalesce(EHP.EvnHistologicProto_Ser, '')) as \"EvnHistologicProto_Ser\",
				RTRIM(coalesce(EHP.EvnHistologicProto_Num, '')) as \"EvnHistologicProto_Num\",
				to_char(EHP.EvnHistologicProto_setDate, 'dd.mm.yyyy') as \"EvnHistologicProto_setDate\",
				to_char(EHP.EvnHistologicProto_CutDate, 'dd.mm.yyyy') as \"EvnHistologicProto_CutDate\",
				to_char(cast(EHP.EvnHistologicProto_CutTime as time), 'HH24:MI') as \"EvnHistologicProto_CutTime\",
				to_char(EHP.EvnHistologicProto_setTime, 'HH24:MI') as \"EvnHistologicProto_setTime\",
				EHP.EvnHistologicProto_IsDiag as \"EvnHistologicProto_IsDiag\",
				EHP.EvnHistologicProto_IsOper as \"EvnHistologicProto_IsOper\",
				EHP.EvnHistologicProto_BitCount as \"EvnHistologicProto_BitCount\",
				EHP.EvnHistologicProto_BlockCount as \"EvnHistologicProto_BlockCount\",
				EHP.EvnHistologicProto_MacroDescr as \"EvnHistologicProto_MacroDescr\",
				EHP.EvnHistologicProto_HistologicConclusion as \"EvnHistologicProto_HistologicConclusion\",
				EHP.EvnHistologicProto_CategoryDiff as \"EvnHistologicProto_CategoryDiff\",
				EHP.Diag_id as \"Diag_id\",
				to_char(EHP.EvnHistologicProto_didDate, 'dd.mm.yyyy') as \"EvnHistologicProto_didDate\",
				EHP.MedPersonal_id as \"MedPersonal_id\",
				EHP.MedPersonal_sid as \"MedPersonal_sid\",
				EHP.EvnHistologicProto_IsDelivSolFormalin as \"EvnHistologicProto_IsDelivSolFormalin\",
				EHP.MarkSavePack_id as \"MarkSavePack_id\",
				EHP.EvnHistologicProto_Comments as \"EvnHistologicProto_Comments\",
				EHP.OnkoDiag_id as \"OnkoDiag_id\",
				PRTL.PrescrReactionType_ids as \"PrescrReactionType_ids\",
				RTRIM(LTRIM(coalesce(pmUserCache.pmUser_Name, ''))) as \"pmUser_Name\"
			from
				v_EvnHistologicProto EHP
				inner join v_EvnDirectionHistologic EDH on EDH.EvnDirectionHistologic_id = EHP.EvnDirectionHistologic_id
				left join YesNo IsBad on IsBad.YesNo_id = EHP.EvnHistologicProto_IsBad
				left join pmUserCache on pmUserCache.pmUser_id = EHP.pmUser_pid
				left join lateral(
					select
						string_agg(PrescrReactionType_id::text, ',') as PrescrReactionType_ids
					from
						v_PrescrReactionTypeLink
					where
						EvnHistologicProto_id = EHP.EvnHistologicProto_id
				) PRTL on true
			where (1 = 1)
				and EHP.EvnHistologicProto_id = :EvnHistologicProto_id
				and (EHP.Lpu_id = :Lpu_id or EDH.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
			limit 1
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
			select
				EHP.EvnHistologicProto_id as \"EvnHistologicProto_id\",
				EHP.Lpu_id as \"Lpu_id\",
				EHP.Server_id as \"Server_id\",
				EHP.PersonEvn_id as \"PersonEvn_id\",
				to_char(EHP.EvnHistologicProto_setDT, 'yyyy-mm-dd HH24:MI:SS') as \"EvnHistologicProto_setDate\",
				to_char(EHP.EvnHistologicProto_didDT, 'yyyy-mm-dd HH24:MI:SS') as \"EvnHistologicProto_didDate\",
				EHP.EvnDirectionHistologic_id as \"EvnDirectionHistologic_id\",
				RTRIM(coalesce(EHP.EvnHistologicProto_Ser, '')) as \"EvnHistologicProto_Ser\",
				RTRIM(coalesce(EHP.EvnHistologicProto_Num, '')) as \"EvnHistologicProto_Num\",
				EHP.EvnHistologicProto_IsDiag as \"EvnHistologicProto_IsDiag\",
				EHP.EvnHistologicProto_IsOper as \"EvnHistologicProto_IsOper\",
				EHP.EvnHistologicProto_BitCount as \"EvnHistologicProto_BitCount\",
				EHP.EvnHistologicProto_BlockCount as \"EvnHistologicProto_BlockCount\",
				EHP.EvnHistologicProto_MacroDescr as \"EvnHistologicProto_MacroDescr\",
				EHP.EvnHistologicProto_HistologicConclusion as \"EvnHistologicProto_HistologicConclusion\",
				EHP.EvnHistologicProto_CategoryDiff as \"EvnHistologicProto_CategoryDiff\",
				EHP.Diag_id as \"Diag_id\",
				EHP.MedPersonal_id as \"MedPersonal_id\",
				EHP.MedPersonal_sid as \"MedPersonal_sid\",
				EHP.EvnHistologicProto_IsDelivSolFormalin as \"EvnHistologicProto_IsDelivSolFormalin\",
				EHP.MarkSavePack_id as \"MarkSavePack_id\",
				EHP.EvnHistologicProto_Comments as \"EvnHistologicProto_Comments\",
				EHP.OnkoDiag_id as \"OnkoDiag_id\",
				to_char(EHP.EvnHistologicProto_CutDate, 'dd.mm.yyyy') as \"EvnHistologicProto_CutDate\",
				to_char(cast(EHP.EvnHistologicProto_CutTime as time) , 'HH24:MI') as \"EvnHistologicProto_CutTime\",
				coalesce(EHP.pmUser_updID, EHP.pmUser_insID) as \"pmUser_id\"
			from v_EvnHistologicProto EHP
			where EHP.EvnHistologicProto_id = :EvnHistologicProto_id
				and EHP.Lpu_id = :Lpu_id
				and coalesce(EHP.EvnHistologicProto_IsBad, 1) <> :EvnHistologicProto_IsBad
			limit 1
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
