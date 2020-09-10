<?php
class CmpCallCard_model4E extends SwPgModel {

	protected $schema = "dbo";  //региональная схема
	protected $comboSchema = "dbo";  //Казахстанский мод

	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();

		//установка региональной схемы
		$config = get_config();

		if($this->regionNick == 'kz'){
			$this->schema = $config['regions'][getRegionNumber()]['schema'];
		}

		if($this->regionNick == 'kz'){
			$this->comboSchema = $config['regions'][getRegionNumber()]['schema'];
		}
	}

	/**
	 * @desc Диспетчер направлений
	 */
	public function loadSMPDispatchDirectWorkPlace($data){
		if ( $this->db->dbdriver == 'postgre' ) {
			$queryParams = array();

			$filter = "
				-- Скрываем вызовы принятые в ППД
				CCC.CmpCallCard_IsReceivedInPPD!=2
				-- Временно только открытые карты
				AND COALESCE(CCC.CmpCallCard_IsOpen,1)=2
				-- только первичные вызовы
				AND CCC.CmpCallType_id=2
				-- @todo тут придумать чтото с ППД
				AND CCC.Lpu_id=:Lpu_id

				-- Отображаем только вызовы переданные от диспетчера вызовов СМП
				-- AND CCC.CmpCallCardStatusType_id IS NOT NULL
			";

			$queryParams['Lpu_id'] = $data['session']['lpu_id'];

			if ( !empty( $data[ 'Search_SurName' ] ) ) {
				$filter .= " and COALESCE(PS.Person_SurName,CCC.Person_SurName) ILIKE :Person_SurName";
				$queryParams[ 'Person_SurName' ] = $data[ 'Search_SurName' ].'%';
			}

			if ( !empty( $data[ 'Search_FirName' ] ) ) {
				$filter .= " and COALESCE(PS.Person_FirName, CCC.Person_FirName) ilike :Person_FirName";
				$queryParams[ 'Person_FirName' ] = $data[ 'Search_FirName' ].'%';
			}

			if ( !empty( $data[ 'Search_SecName' ] ) ) {
				$filter .= " and COALESCE(PS.Person_SecName, CCC.Person_SecName) ilike :Person_SecName";
				$queryParams[ 'Person_SecName' ] = $data[ 'Search_SecName' ].'%';
			}

			if ( !empty( $data[ 'Search_BirthDay' ] ) ) {
				$filter .= " and COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
				$queryParams[ 'Person_BirthDay' ] = $data[ 'Search_BirthDay' ];
			}

			if ( !empty( $data[ 'CmpLpu_id' ] ) ) {
				$filter .= " and CCC.CmpLpu_id=:CmpLpu_id";
				$queryParams[ 'CmpLpu_id' ] = $data[ 'CmpLpu_id' ];
			}

			if ( !empty( $data[ 'CmpCallCard_Ngod' ] ) ) {
				$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
				$queryParams[ 'CmpCallCard_Ngod' ] = $data[ 'CmpCallCard_Ngod' ];
			}

			if ( !empty( $data[ 'CmpCallCard_Numv' ] ) ) {
				$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$queryParams[ 'CmpCallCard_Numv' ] = $data[ 'CmpCallCard_Numv' ];
			}

			$isToday = strtotime( $data[ 'begDate' ] ) == mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) );

			if ( !empty( $data[ 'begDate' ] ) && !empty( $data[ 'endDate' ] ) && $data[ 'begDate' ] == $data[ 'endDate' ] && !empty( $data[ 'hours' ] ) && $isToday ) {

				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= CAST(:begDate as date) - INTERVAL '1 day'";
				$queryParams[ 'begDate' ] = $data[ 'begDate' ];

				$filter .= " and CCC.CmpCallCard_prmDT > dbo.tzGetDate() + INTERVAL '1 hour'";
				switch( $data[ 'hours' ] ){
					case '1':
					case '2':
					case '3':
					case '6':
					case '12':
					case '24':
						$queryParams[ 'hours' ] = '-'.$data[ 'hours' ];
					break;
					default:
						$queryParams[ 'hours' ] = '-24';
					break;
				}
			}

			if ( !empty( $data[ 'dispatchCallPmUser_id' ] ) ) {
				$filter .= " and CCC.pmUser_insID = :dispatchCallPmUser_id";
				$queryParams[ 'dispatchCallPmUser_id' ] = $data[ 'dispatchCallPmUser_id' ];
			}

			// Для получения изменений одного талона вызова
			if ( !empty( $data[ 'CmpCallCard_id' ] ) ) {
				$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
				$queryParams[ 'CmpCallCard_id' ] = $data[ 'CmpCallCard_id' ];
			}

			// Отображаем только те вызовы, которые переданы на эту подстанцию (refs #38949)
			if ( $data[ 'session' ][ 'CurMedService_id' ] ) {
				$lpuBuildingQuery = "
					SELECT
						COALESCE(MS.LpuBuilding_id,0) as \"LpuBuilding_id\"
					FROM
						dbo.v_MedService MS
					WHERE
						MS.MedService_id = ?
				";
				$lpuBuildingResult = $this->db->query( $lpuBuildingQuery, array( $data[ 'session' ][ 'CurMedService_id' ] ) );
				if ( is_object( $lpuBuildingResult ) ) {
					$lpuBuildingResult = $lpuBuildingResult->result( 'array' );
					if ( isset( $lpuBuildingResult[ 0 ] ) && (!empty( $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ] )) ) {
						$filter .=" and CCC.LpuBuilding_id = :LpuBuilding_id";
						$queryParams[ 'LpuBuilding_id' ] = $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
					}
				}
			}

			//Получаем дополнительно те вызовы, от которых отказался диспетчер подстанции
			if ( !empty( $data[ 'CmpGroup_id' ] ) ) {
				if ( $data[ 'CmpGroup_id' ] == 1 ) {
					$filter .= " AND (CCC.CmpCallCardStatusType_id = :CmpGroup_id OR CCC.CmpCallCardStatusType_id = 8)";
				} else {
					$filter .= " AND CCC.CmpCallCardStatusType_id = :CmpGroup_id";
				}

				$queryParams[ 'CmpGroup_id' ] = $data[ 'CmpGroup_id' ];
			} else {
				$filter .= " AND CCC.CmpCallCardStatusType_id IS NOT NULL";
			}

			$query = "
				SELECT
					-- selects
					CCC.CmpCallCard_id as \"CmpCallCard_id\",
					PS.Person_id as \"Person_id\",
					PS.Sex_id as \"Sex_id\",
					CCC.CmpReason_id as \"CmpReason_id\",
					PS.PersonEvn_id as \"PersonEvn_id\",
					PS.Server_id as \"Server_id\",
					COALESCE(PS.Person_SurName, CCC.Person_SurName) as \"Person_Surname\",
					COALESCE(PS.Person_FirName, CCC.Person_FirName) as \"Person_Firname\",
					COALESCE(PS.Person_SecName, CCC.Person_SecName) as \"Person_Secname\",
					COALESCE(CCC.Person_Age,0) as \"Person_Age\",
					CCC.pmUser_insID as \"pmUser_insID\",

					--to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\",
					TO_CHAR(CCC.CmpCallCard_prmDT, 'DD Mon YYYY HH24: MI: SS') as \"CmpCallCard_prmDate\",

					CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
					CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
					case when COALESCE(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\",
					case when COALESCE(CCCLL.CmpCallCardLockList_id,0) = 0 then
						COALESCE(PS.Person_SurName, CCC.Person_SurName,'') || ' ' || COALESCE(PS.Person_FirName, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_SecName, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
					else
						'<img src=\"../img/grid/lock.png\">' || COALESCE(PS.Person_SurName,CCC.Person_SurName,'') || ' ' || COALESCE(PS.Person_FirName, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_SecName, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
					end as \"Person_FIO\",

					--to_char (COALESCE(PS.Person_BirthDay,CCC.Person_BirthDay), 'DD Mon YYYY HH24:MI:SS') as \"Person_Birthday\",
					to_char(COALESCE(PS.Person_BirthDay,CCC.Person_BirthDay), 'DD.MM.YYYY') as \"Person_Birthday\",
					--COALESCE(PS.Person_BirthDay,CCC.Person_BirthDay\") as \"Person_Birthday\",

					RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code  || '. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\",
					RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar) || '. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\",
					RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\",
					RTRIM(COALESCE(CLD.Diag_Code, '') || ' ' || COALESCE(CLD.Diag_Name, '')) as \"CmpDiag_Name\",
					RTRIM(COALESCE(D.\"Diag_Code\", '')) as \"StacDiag_Name\",
					CCC.EmergencyTeam_id,
					ET.EmergencyTeam_Num,
					CCC.CmpCallCard_prmDT,
					CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
					CCC.CmpSecondReason_id as \"CmpSecondReason_id\",
					RTRIM(case when CSecondR.CmpReason_id is not null then CSecondR.CmpReason_Code || '. ' else '' end || COALESCE(CSecondR.CmpReason_Name, '')) as \"CmpSecondReason_Name\",

					--to_char (cast(CCC.CmpCallCard_BoostTime as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_BoostTime\",
					to_char(CCC.CmpCallCard_BoostTime, 'DD Mon YYYY HH24: MI: SS') as \"CmpCallCard_BoostTime\",

					case when CCC.CmpCallCardStatusType_id=1 and CCC.Lpu_ppdid is not null

							--then TO_CHAR (COALESCE( * INTERVAL '1 mi(DS.DV - DATEDIFF(mi,CCC.CmpCallCard_updDT, dbo.tzGetDate()),20)' || to_char(0), 'HH24:MI:SS'))

							--@todo Доработать условие закомментированное строкой выше, вместо условия строкой ниже
							--@todo Проверить целесообразность преобразования
							-- then COALESCE(DS.DV - EXTRACT(EPOCH FROM AGE(dbo.tzGetDate(), CCC.CmpCallCard_updDT)),'20') + dbo.GetDate()
							then 'Функционал в разработке'

							else '00:00'
					end as \"PPD_WaitingTime\",

					SLPU.Lpu_Nick as \"SendLpu_Nick\",

					case when City.KLCity_Name is not null then 'г. ' || City.KLCity_Name else SRGN.KLSubRgn_FullName end
						||
						case when Town.KLTown_FullName is not null then ', ' || Town.KLTown_FullName else '' end
						||
						case when Street.KLStreet_FullName is not null then ', ' || LOWER(socrStreet.KLSocr_Nick) || '. ' else '' end
						||
						case when CCC.CmpCallCard_Dom is not null then ', д.' || CCC.CmpCallCard_Dom else '' end
						||
						case when CCC.CmpCallCard_Kvar is not null then ', кв.' || CCC.CmpCallCard_Kvar else '' end
						||
						case when CCC.CmpCallCard_Room is not null then ', ком. ' || CCC.CmpCallCard_Room else '' end
						||
						case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: ' || UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Name\",


					UAD.UnformalizedAddressDirectory_id as \"UnAdress_Name\",
					UAD.UnformalizedAddressDirectory_lat as \"UnAdress_lat\",
					UAD.UnformalizedAddressDirectory_lng as \"UnAdress_lng\",

					case
						when CCC.CmpCallCardStatusType_id=3
						then case
							when COALESCE(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0
							then CCC.CmpCallCardStatus_Comment
							else CCCStatusHist.CmpMoveFromNmpReason_Name end
						when CCC.CmpCallCardStatusType_id=5
						then CCC.CmpCallCardStatus_Comment
						when CCC.CmpCallCardStatusType_id=4
						then case
							when EPLD.Diag_FullName is not null
							then 'Диагноз: ' || EPLD.Diag_FullName else '' end
							||
							case
							when RC.ResultClass_Name is not null
							then '<br />Результат: ' || RC.ResultClass_Name else '' end
							||
							case
							when DT.DirectType_Name is not null
							then '<br />Направлен: ' || DT.DirectType_Name else '' end
					end	as \"PPDResult\",

					--to_char(cast(ServeDT.ServeDT as datetime), 'DD.MM.YYYY') as \"ServeDT\",

					to_char(ServeDT.ServeDT, 'DD.MM.YYYY') as \"ServeDT\",

					case
						when CCC.CmpCallCardStatusType_id IN(2,3,4)
						then PMC.PMUser_Name
							--|| CAST( CCC.CmpCallCard_updDT as varchar )
							|| to_char(CCC.CmpCallCard_updDT, 'DD.MM.YYYY')
						else ''
					end as \"PPDUser_Name\",

					case when COALESCE(CCC.CmpCallCard_IsOpen,1)=2
						then case
							when CCC.Lpu_ppdid IS NULL
							THEN CASE
								WHEN CCC.CmpCallCardStatusType_id=4 THEN 3
								WHEN CCC.CmpCallCardStatusType_id=6 THEN 9
								WHEN CCC.CmpCallCardStatusType_id=3 THEN 7
								WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id
								ELSE CCC.CmpCallCardStatusType_id+3
								END
							ELSE
								CASE
									WHEN CmpCallCardStatusType_id=4 THEN 6
									WHEN CmpCallCardStatusType_id=3 THEN 7
									ELSE CCC.CmpCallCardStatusType_id+3
								END
							END
						ELSE 9
					END as \"CmpGroup_id\"
					-- end select
				from
					-- from
					dbo.v_CmpCallCard CCC
					left join lateral (
						SELECT
							CmpCallCardStatus_insDT as ServeDT
						FROM
							dbo.v_CmpCallCardStatus
						WHERE
							CmpCallCardStatusType_id=4
							AND CmpCallCard_id=CCC.CmpCallCard_id
						ORDER BY
							CmpCallCardStatus_insDT DESC
						LIMIT 1
					) as ServeDT on true
					left join lateral (
						SELECT
							CmpCallCardStatus_insDT as ToDT
						FROM
							dbo.v_CmpCallCardStatus
						WHERE
							CmpCallCardStatusType_id=2
							AND CmpCallCard_id=CCC.CmpCallCard_id
						ORDER BY
							CmpCallCardStatus_insDT DESC
						LIMIT 1
					) as ToDT on true
					left join lateral (
						SELECT
							cmfnr.CmpMoveFromNmpReason_id as CmpMoveFromNmpReason_id,
							cmfnr.CmpMoveFromNmpReason_Name as CmpMoveFromNmpReason_Name
						FROM
							dbo.v_CmpCallCardStatus cccs
							LEFT JOIN dbo.v_CmpMoveFromNmpReason cmfnr on( cmfnr.CmpMoveFromNmpReason_id=cccs.CmpMoveFromNmpReason_id)
						WHERE
							CmpCallCardStatusType_id=3
							AND CmpCallCard_id=CCC.CmpCallCard_id
						ORDER BY
							CmpCallCardStatus_insDT DESC
						LIMIT 1
					) as CCCStatusHist on true
					left join lateral (
						SELECT COALESCE( (SELECT DS.DataStorage_Value as DataStorage_Value FROM dbo.DataStorage DS WHERE DS.DataStorage_Name='cmp_waiting_ppd_time' AND DS.Lpu_id=0), '20' ) as DV
					) as DS on true
					left join dbo.v_PersonState PS on( PS.Person_id=CCC.Person_id)
					left join dbo.v_CmpReason CR on( CR.CmpReason_id=CCC.CmpReason_id)
					left join dbo.v_CmpReason CSecondR on( CSecondR.CmpReason_id=CCC.CmpSecondReason_id)
					left join dbo.v_CmpCallType CCT on( CCT.CmpCallType_id=CCC.CmpCallType_id)
					left join dbo.CmpLpu CL on( CL.CmpLpu_id=CCC.CmpLpu_id)
					left join dbo.v_Lpu L on( L.Lpu_id=CCC.CmpLpu_id)
					left join dbo.CmpDiag CD on( CD.CmpDiag_id=CCC.CmpDiag_oid)
					left join dbo.Diag D on( D.Diag_id=CCC.Diag_sid )
					left join dbo.v_Lpu SLPU on( SLPU.Lpu_id=CCC.Lpu_ppdid )
					left join dbo.v_EvnPL EPL on( EPL.CmpCallCard_id=CCC.CmpCallCard_id
													and EPL.Lpu_id=CCC.Lpu_ppdid
													and CCC.Lpu_ppdid is not null
													and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date) )
					left join dbo.v_Diag EPLD on( EPLD.Diag_id=EPL.Diag_id)
					left join dbo.v_ResultClass RC on( RC.ResultClass_id=EPL.ResultClass_id)
					left join dbo.v_DirectType DT on( DT.DirectType_id=EPL.DirectType_id)
					left join dbo.v_pmUserCache PMC on( PMC.PMUser_id=CCC.pmUser_updID)
					left join dbo.v_EmergencyTeam ET on( CCC.EmergencyTeam_id=ET.EmergencyTeam_id)
					left join dbo.v_CmpCloseCard CLC on( CCC.CmpCallCard_id=CLC.CmpCallCard_id)
					left join dbo.v_Diag CLD on( CLC.Diag_id=CLD.Diag_id)

					left join dbo.v_KLRgn RGN on( RGN.KLRgn_id=CCC.KLRgn_id)
					left join dbo.v_KLSubRgn SRGN on( SRGN.KLSubRgn_id=CCC.KLSubRgn_id)
					left join dbo.v_KLCity City on( City.KLCity_id=CCC.KLCity_id)
					left join dbo.v_KLTown Town on( Town.KLTown_id=CCC.KLTown_id)
					left join dbo.v_KLStreet Street on(Street.KLStreet_id=CCC.KLStreet_id)
					left join dbo.v_KLSocr socrStreet on( Street.KLSocr_id=socrStreet.KLSocr_id)
					left join dbo.v_UnformalizedAddressDirectory UAD on( UAD.UnformalizedAddressDirectory_id=CCC.UnformalizedAddressDirectory_id)
					left join dbo.v_CmpCallCardLockList CCCLL on( CCCLL.CmpCallCard_id=CCC.CmpCallCard_id
																	--@todo Заменил выражение, необходимо убедиться в работоспособности
																	--and (60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate())) >0 )
																	AND ( EXTRACT(EPOCH FROM AGE(dbo.tzGetDate(),CmpCallCardLockList_updDT))/60 ) > 0 )

					-- end from
				WHERE
					-- where
					" . $filter . "
					-- end where
				ORDER BY
					-- order by
					(case when COALESCE(CCC.CmpCallCard_IsOpen,1) = 2
						then case
								when CCC.CmpCallCardStatusType_id IN(1,2,3,4)
								then CCC.CmpCallCardStatusType_id+2
								else case
									when CCC.Lpu_ppdid is not null
									then 1
									else 2 end
								end
						else 7 end),
					CCC.CmpCallCard_prmDT desc
					-- end order by
			";
		} else {

			$queryParams = array();

			$filter = "
				-- Скрываем вызовы принятые в ППД
				COALESCE(CCC.CmpCallCard_IsReceivedInPPD,1)!=2
				-- Временно только открытые карты
				AND COALESCE(CCC.CmpCallCard_IsOpen,1)=2
				-- только первичные вызовы
				AND CCC.CmpCallType_id=2

				-- @todo тут придумать чтото с ППД
				AND CCC.Lpu_id=:Lpu_id

				-- Отображаем только вызовы переданные от диспетчера вызовов СМП
				-- AND CCC.CmpCallCardStatusType_id IS NOT NULL
			";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];

			if ( !empty($data['Search_SurName']) ) {
				$filter .= " and COALESCE(PS.Person_Surname, CCC.Person_SurName) ilike :Person_SurName";

				$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
			}

			if ( !empty($data['Search_FirName']) ) {
				$filter .= " and COALESCE(PS.Person_Firname, CCC.Person_FirName) ilike :Person_FirName";

				$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
			}

			if ( !empty($data['Search_SecName']) ) {
				$filter .= " and COALESCE(PS.Person_Secname, CCC.Person_SecName) ilike :Person_SecName";

				$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
			}

			if ( !empty($data['Search_BirthDay']) ) {
				$filter .= " and COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";

				$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
			}

			if ( !empty($data['CmpLpu_id']) ) {
				$filter .= " and CCC.CmpLpu_id = :CmpLpu_id";
				$queryParams['CmpLpu_id'] = $data['CmpLpu_id'];
			}

			if ( !empty($data['CmpCallCard_Ngod']) ) {
				$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
				$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
			}

			if ( !empty($data['CmpCallCard_Numv']) ) {
				$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
			}

			$isToday = strtotime($data['begDate']) ==  mktime(0,0,0,date('m'),date('d'),date('Y'));

			if ( !empty( $data[ 'begDate' ] ) && !empty( $data[ 'endDate' ] ) && ($data[ 'begDate' ] == $data[ 'endDate' ]) && (!empty( $data[ 'hours' ] )) && $isToday ) {

				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= DATEADD('day', -1, CAST(:begDate as date))";
				$queryParams[ 'begDate' ] = $data[ 'begDate' ];
				$filter .= " and CCC.CmpCallCard_prmDT > dbo.tzGetDate() + INTERVAL '1 hour'";
				switch( $data[ 'hours' ] ){
					case '1':
					case '2':
					case '3':
					case '6':
					case '12':
					case '24':
						$queryParams[ 'hours' ] = '-'.$data[ 'hours' ];
					break;
					default:
						$queryParams[ 'hours' ] = '-24';
					break;
				}
			}

			if ( !empty($data['dispatchCallPmUser_id']) ) {
				$filter .= " and CCC.pmUser_insID = :dispatchCallPmUser_id";
				$queryParams['dispatchCallPmUser_id'] = $data['dispatchCallPmUser_id'];
			}

			//Для получения изменений одного талона вызова
			if ( !empty($data['CmpCallCard_id']) ) {
				$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
				$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
			}

			/*
			// Отображаем только те вызовы, которые переданы на эту подстанцию (refs #38949)
			if ($data['session']['region']['number'] != 59 && $_SESSION['region']['number'] != 59 && $data[ 'session' ][ 'CurMedService_id' ] ) {
				$lpuBuildingQuery = "
					SELECT
						COALESCE(MS.LpuBuilding_id,0) as LpuBuilding_id

					FROM
						v_MedService MS

					WHERE
						MS.MedService_id = :MedService_id
					";
				$lpuBuildingResult = $this->db->query( $lpuBuildingQuery, array(
					'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
				) );

				if ( is_object( $lpuBuildingResult ) ) {
					$lpuBuildingResult = $lpuBuildingResult->result( 'array' );
					if ( isset( $lpuBuildingResult[ 0 ] ) && (!empty( $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ] )) ) {
						$filter .=" and (CCC.LpuBuilding_id = :LpuBuilding_id)";
						$queryParams[ 'LpuBuilding_id' ] = $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
					}
				}
			}*/

			//теперь же отображаем вызовы которые подчинены оперативному отделу refs #80059
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0]['LpuBuilding_id'])){
				return $this->createError(null, 'Не определена подстанция');
			}
			else{
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
			}
			$smpUnitsNested = $this->loadSmpUnitsNested( $data );

			if ( !(empty( $smpUnitsNested)) ) {
				$filter .=" AND CCC.LpuBuilding_id in (";
				foreach ($smpUnitsNested as &$value) {
					$filter .= $value['LpuBuilding_id'].',';
				}
				$filter = substr($filter, 0, -1).')';
				}
			else{
				return $this->createError(null, 'Не определена подстанция');
			}


			//Получаем дополнительно те вызовы, от которых отказался диспетчер подстанции
			$order = "";
			if ( !empty( $data[ 'CmpGroup_id' ] ) ) {
				if ( $data[ 'CmpGroup_id' ] == 1 ) {
					$filter .= " AND (CCC.CmpCallCardStatusType_id = :CmpGroup_id OR CCC.CmpCallCardStatusType_id = 8)";
				} elseif ( $data[ 'CmpGroup_id' ] < 0 ) {
					$data[ 'CmpGroup_id' ] *= -1;
					$filter .= " AND (CCC.CmpCallCardStatusType_id<>:CmpGroup_id)";
				} else {
					$filter .= " AND CCC.CmpCallCardStatusType_id = :CmpGroup_id";
				}

				$queryParams[ 'CmpGroup_id' ] = $data[ 'CmpGroup_id' ];
			} else {
				$filter .= " AND CCC.CmpCallCardStatusType_id IS NOT NULL";
			}

			// Добавить к выводу карты находящиеся в обслуживании у бригад СМП
			if ( !empty( $data['appendExceptClosed'] ) && $data['appendExceptClosed'] ) {
				$order .= " CASE WHEN CCC.CmpCallCardStatusType_id=1 THEN 1 ELSE 2 END, ";
				$filter .= "
					-- Все не закрытые
					AND CCC.CmpCallCardStatusType_id<>6
					-- Все у которых есть бригады
					AND ( CCC.CmpCallCardStatusType_id=1 OR (CCC.CmpCallCardStatusType_id<>1 AND CCC.EmergencyTeam_id IS NOT NULL ) )
				";
			}

			$query = "
				with cmp_waiting_ppd as (
				    select COALESCE((
				    select
				        DS.DataStorage_Value as DataStorage_Value 
				    FROM 
				        DataStorage DS  
				    where
				        DS.DataStorage_Name = 'cmp_waiting_ppd_time'
				    and 
				        DS.Lpu_id = 0 limit 1), 20)
				)

				select
					-- select
					CCC.CmpCallCard_id as \"CmpCallCard_id\"
					,PS.Person_id as \"Person_id\"
					,PS.Sex_id as \"Sex_id\"
					,CCC.CmpReason_id as \"CmpReason_id\"
					,case when to_char (COALESCE(CR.CmpReason_Code,'0')) in ('313', '53', '298', '326', '231', '343', '232', '233', '155', '329', '321', '314', '344', '319', '36', '114', '40', '156', '277', '88', '153', '127', '121', '89', '305', '327', '56', '273', '102', '176', '351', '307', '338', '52', '339', '331', '191', '345', '323', '337', '302', '341', '310') then 'НП' else '' end as \"Urgency\"

					,PS.PersonEvn_id as \"PersonEvn_id\"
					,PS.Server_id as \"Server_id\"
					,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

					,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

					,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

					,COALESCE(CCC.Person_Age,0) as \"Person_Age\"

					,CCC.pmUser_insID as \"pmUser_insID\"
					,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
					,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
					,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
					,case when COALESCE(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\"

					,case when COALESCE(CCCLL.CmpCallCardLockList_id,0) = 0 then

						COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
					else
						'<img src=\"../img/grid/lock.png\">'||COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
					end as \"Person_FIO\"
					,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"

					,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

					,RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"

					--,case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '2' end as \"CmpCallType_Name\"
					,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_||', ' '), '')) as \"CmpLpu_Name\"
					,RTRIM(COALESCE(CLD.Diag_Code, '') ||' '|| COALESCE(CLD.Diag_Name, '')) as \"CmpDiag_Name\"

					,RTRIM(COALESCE(D.Diag_Code, '')) as \"StacDiag_Name\"

					,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
					,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
					,CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\"
					,CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",

					CCC.CmpSecondReason_id as \"CmpSecondReason_id\",
					COALESCE(CSecondR.CmpReason_Code || '. ', '') || CSecondR.CmpReason_Name as \"CmpSecondReason_Name\",

					to_char (cast(CCC.CmpCallCard_BoostTime as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_BoostTime\"
					,case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
							then TO_CHAR(DATEADD('minute', COALESCE( (select DataStorage_Value as DataStorage_Value from cmp_waiting_ppd) - DATEDIFF('minute',CCC.CmpCallCard_updDT,dbo.tzGetDate()),20)  ,to_char(0)), 'HH24:MI:SS')


							else '00'||':'||'00'
					end as \"PPD_WaitingTime\"

					,SLPU.Lpu_Nick as \"SendLpu_Nick\"

					,case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end ||
					case when Town.KLTown_FullName is not null then
                    	case when City.KLCity_Name is not null then ', ' else '' end
                         ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

					end ||
					case when Street.KLStreet_FullName is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else '' end ||
					case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
					case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
					case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Name\"

					,case when UAD.UnformalizedAddressDirectory_id is not null then UAD.UnformalizedAddressDirectory_id else null end as \"UnAdress_Name\"
					,case when UAD.UnformalizedAddressDirectory_lat is not null then UAD.UnformalizedAddressDirectory_lat else null end as \"UnAdress_lat\"
					,case when UAD.UnformalizedAddressDirectory_lng is not null then UAD.UnformalizedAddressDirectory_lng else null end as \"UnAdress_lng\"

					--,EPL.Diag_id as \"EPLDiag_id\"


					,case
					when CCC.CmpCallCardStatusType_id = 3 then
						case
							when COALESCE(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment

							else CCCStatusHist.CmpMoveFromNmpReason_Name
						end
					when CCC.CmpCallCardStatusType_id = 5 then
						CCC.CmpCallCardStatus_Comment
					when CCC.CmpCallCardStatusType_id = 4 then
						case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end ||
						case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end ||
						case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
					end	as \"PPDResult\"
					,to_char(cast(ServeDT.ServeDT as timestamp(3)), 'DD.MM.YYYY') as \"ServeDT\"

					,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name || to_char(cast(CCC.CmpCallCard_updDT as timestamp(3)), 'DD.MM.YYYY') else '' end as \"PPDUser_Name\"


					,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

						then
							case
								WHEN CCC.Lpu_ppdid IS NULL THEN
									CASE
										WHEN CCC.CmpCallCardStatusType_id=4 THEN 3
										WHEN CCC.CmpCallCardStatusType_id=6 THEN 9
										WHEN CCC.CmpCallCardStatusType_id=3 THEN 7
										WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id
										ELSE CCC.CmpCallCardStatusType_id+3
									END
								ELSE
									CASE
										WHEN CmpCallCardStatusType_id=4 THEN 6
										WHEN CmpCallCardStatusType_id=3 THEN 7
										ELSE CCC.CmpCallCardStatusType_id+3
									END
								END
						else 9
					end as \"CmpGroup_id\",
					CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
					CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
					-- end select
				from
					-- from
					v_CmpCallCard CCC 


					LEFT JOIN LATERAL(

						select
							CmpCallCardStatus_insDT as ServeDT
						from
							v_CmpCallCardStatus 

						where
							CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
						order by CmpCallCardStatus_insDT desc
						limit 1
					) as ServeDT ON TRUE
					LEFT JOIN LATERAL(

						select
							CmpCallCardStatus_insDT as ToDT
						from
							v_CmpCallCardStatus 

						where
							CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
						order by CmpCallCardStatus_insDT desc
						limit 1
					) as ToDT ON TRUE
					LEFT JOIN LATERAL(

						select
							v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id as CmpMoveFromNmpReason_id,
							v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name as CmpMoveFromNmpReason_Name
						from
							v_CmpCallCardStatus 

							left join v_CmpMoveFromNmpReason  on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id

						where
							CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
						order by CmpCallCardStatus_insDT desc
						limit 1
					) as CCCStatusHist ON TRUE
					left join v_PersonState PS  on PS.Person_id = CCC.Person_id

					left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

					left join v_CmpReason CSecondR  on CSecondR.CmpReason_id = CCC.CmpSecondReason_id

					left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

					left join CmpLpu CL  on CL.CmpLpu_id = CCC.CmpLpu_id

					left join v_Lpu L  on L.Lpu_id = CCC.CmpLpu_id

					left join CmpDiag CD  on CD.CmpDiag_id = CCC.CmpDiag_oid

					left join Diag D  on D.Diag_id = CCC.Diag_sid

					left join v_Lpu SLPU  on SLPU.Lpu_id = CCC.Lpu_ppdid

					LEFT JOIN LATERAL (

						SELECT 
						    evnclass_id as evnclass_id,
                            evnclass_name as evnclass_name,
                            evnpl_id as evnpl_id,
                            evnpl_setdate as evnpl_setdate,
                            evnpl_settime as evnpl_settime,
                            evnpl_disdate as evnpl_disdate,
                            evnpl_distime as evnpl_distime,
                            evnpl_diddate as evnpl_diddate,
                            evnpl_didtime as evnpl_didtime,
                            evnpl_pid as evnpl_pid,
                            evnpl_rid as evnpl_rid,
                            lpu_id as lpu_id,
                            server_id as server_id,
                            personevn_id as personevn_id,
                            evnpl_setdt as evnpl_setdt,
                            evnpl_disdt as evnpl_disdt,
                            evnpl_diddt as evnpl_diddt,
                            evnpl_insdt as evnpl_insdt,
                            evnpl_upddt as evnpl_upddt,
                            evnpl_index as evnpl_index,
                            evnpl_count as evnpl_count,
                            pmuser_insid as pmuser_insid,
                            pmuser_updid as pmuser_updid,
                            person_id as person_id,
                            morbus_id as morbus_id,
                            evnpl_issigned as evnpl_issigned,
                            pmuser_signid as pmuser_signid,
                            evnpl_signdt as evnpl_signdt,
                            evnpl_isarchive as evnpl_isarchive,
                            evnpl_guid as evnpl_guid,
                            evnpl_indexminusone as evnpl_indexminusone,
                            evnstatus_id as evnstatus_id,
                            evnpl_statusdate as evnpl_statusdate,
                            evnpl_istransit as evnpl_istransit,
                            evnpl_vizitcount as evnpl_vizitcount,
                            evnpl_isfinish as evnpl_isfinish,
                            person_age as person_age,
                            evnpl_ismsedirected as evnpl_ismsedirected,
                            evndirection_id as evndirection_id,
                            evnpl_numcard as evnpl_numcard,
                            prehospdirect_id as prehospdirect_id,
                            lpu_did as lpu_did,
                            org_did as org_did,
                            lpusection_did as lpusection_did,
                            diag_did as diag_did,
                            prehosptrauma_id as prehosptrauma_id,
                            evnpl_isunlaw as evnpl_isunlaw,
                            evnpl_isunport as evnpl_isunport,
                            resultclass_id as resultclass_id,
                            evnpl_ukl as evnpl_ukl,
                            directtype_id as directtype_id,
                            directclass_id as directclass_id,
                            lpu_oid as lpu_oid,
                            lpusection_oid as lpusection_oid,
                            diag_id as diag_id,
                            deseasetype_id as deseasetype_id,
                            evndirection_num as evndirection_num,
                            evndirection_setdt as evndirection_setdt,
                            evnpl_isfirsttime as evnpl_isfirsttime,
                            evnpl_complexity as evnpl_complexity\" 
                            medpersonal_id as medpersonal_id,
                            lpusection_id as lpusection_id,
                            diag_agid as diag_agid,
                            cmpcallcard_id as cmpcallcard_id,
                            resultdeseasetype_id as resultdeseasetype_id,
                            evnpl_isinreg as evnpl_isinreg,
                            evnpl_ispaid as evnpl_ispaid,
                            medicalcarekind_id as medicalcarekind_id,
                            evnpl_iscons as evnpl_iscons,
                            evnpl_indexrep as evnpl_indexrep,
                            evnpl_indexrepinreg as evnpl_indexrepinreg,
                            leavetype_fedid as leavetype_fedid,
                            resultdeseasetype_fedid as resultdeseasetype_fedid,
                            lpu_codesmo as lpu_codesmo,
                            evnpl_firstvizitdt as evnpl_firstvizitdt,
                            evnpl_lastvizitdt as evnpl_lastvizitdt,
                            evnpl_lastuslugadt as evnpl_lastuslugadt,
                            diag_preid as diag_preid,
                            interruptleavetype_id as interruptleavetype_id,
                            diag_concid as diag_concid,
                            server_omsid as server_omsid,
                            personevn_omsid as personevn_omsid,
                            evnvizitpl_omsid as evnvizitpl_omsid,
                            medstafffact_did as medstafffact_did,
                            diag_fid as diag_fid,
                            diag_lid as diag_lid,
                            evnpl_isfirstdisable as evnpl_isfirstdisable,
                            privilegetype_id as privilegetype_id,
                            evnpl_firstvizitcapitadt as evnpl_firstvizitcapitadt,
                            evnpl_firstvizitscapitadt as evnpl_firstvizitscapitadt,
                            evnvizitpl_cid as evnvizitpl_cid,
                            evnvizitpl_scid as evnvizitpl_scid,
                            evnpl_lastuslugacapitadt as evnpl_lastuslugacapitadt,
                            evnpl_lastuslugascapitadt as evnpl_lastuslugascapitadt,
                            evnpl_iscrossed as evnpl_iscrossed,
                            medicalcarebudgtype_id as medicalcarebudgtype_id,
                            evnpl_isinregzno as evnpl_isinregzno,
                            evnpl_issurveyrefuse as evnpl_issurveyrefuse,
                            registry_sid as registry_sid,
                            evnpl_medpersonalcode as evnpl_medpersonalcode,
                            evnpl_firstuslugacapitadt as evnpl_firstuslugacapitadt,
                            evnpl_firstuslugascapitadt
						FROM v_EvnPL AS t1 
						WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
							AND t1.Lpu_id = CCC.Lpu_ppdid
							and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
							and CCC.Lpu_ppdid is not null
                        LIMIT 1
					) EPL ON TRUE
					/*left join v_EvnPL EPL  on 1=1

						--and CCC.CmpCallCardStatusType_id=4
						and EPL.CmpCallCard_id = CCC.CmpCallCard_id
						and EPL.Lpu_id=CCC.Lpu_ppdid
						and CCC.Lpu_ppdid is not null
						and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

					left join v_Diag EPLD  on EPLD.Diag_id = EPL.Diag_id

					left join v_ResultClass RC  on RC.ResultClass_id = EPL.ResultClass_id

					left join v_DirectType DT  on DT.DirectType_id = EPL.DirectType_id

					left join v_pmUserCache PMC  on PMC.PMUser_id = CCC.pmUser_updID

					left join v_EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

					left join v_CmpCloseCard CLC  on CCC.CmpCallCard_id = CLC.CmpCallCard_id

					left join v_Diag CLD  on CLC.Diag_id = CLD.Diag_id

					left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id


					left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

					left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

					left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

					left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

					left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id

					left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

					left join v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

						and (60 - DATEDIFF('second', CCCLL.CmpCallCardLockList_updDT::timestamp, dbo.tzGetDate()::timestamp)) >0

					-- end from
				where
					-- where
					" . $filter . "
					-- end where
				order by
					-- order by
					".$order."
					(case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

						then
							case
								when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id+2
								else
									case when CCC.Lpu_ppdid is not null
										then 1
										else 2
									end
							end
						else 7
					end),

					CCC.CmpCallCard_prmDT desc
					-- end order by
			";
		}
		//var_dump(getDebugSQL($query, $queryParams)); exit;
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			'data' => $val,
			'totalCount' => sizeof($val)
		);
	}

	/**
	 * default desc
	 */
	function checkLockCmpCallCard($data) {
		return false;
		if (!isset($data['CmpCallCard_id']) || !isset($data['pmUser_id'])) {
			return false;
		}
		if ( $this->db->dbdriver == 'postgre' ) {
			$query = "
				SELECT DISTINCT
					CCCLL.CmpCallCard_id as \"CmpCallCard_id\",
					CCCLL.CmpCallCardLockList_id as \"CmpCallCardLockList_id\",
					'' as \"Error_Msg\"
				FROM
					dbo.v_CmpCallCardLockList CCCLL
				WHERE
					CCCLL.CmpCallCard_id = :CmpCallCard_id
					and ( 60 - EXTRACT(EPOCH FROM AGE(dbo.tzGetDate(), CmpCallCardLockList_updDT))/60) > 0
					and CCCLL.pmUser_insID != :pmUser_id
			";
		} else {
			$query = "
				SELECT DISTINCT
					CCCLL.CmpCallCard_id as \"CmpCallCard_id\"
					,CCCLL.CmpCallCardLockList_id as \"CmpCallCardLockList_id\"
					,'' as \"Error_Msg\"
				FROM
					v_CmpCallCardLockList CCCLL 

				WHERE
					CCCLL.CmpCallCard_id = :CmpCallCard_id
					and 60 - DATEDIFF('second',CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate()) >0
					and CCCLL.pmUser_insID != :pmUser_id
				";
		}
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$retrun = $result->result('array');
			return $retrun;
		} else {
			return false;
		}
	}


	/**
		* Тестовый эксперимент по получению параметров для инсерта в sql запрос
		* inputProcedure - процедура для инсерта
		* params - параметры для вставки
		* exceptedFields исключающие поля (поля не для сохранения)
		* isPostgresql - параметр для конвертации запроса в Postgresql формат

		* возвращает список параметров(array/string(Postgresql)), значения параметров в sql (string)
	*/
	private function getParamsForSQLQuery( $inputProcedure, $params, $exceptedFields=null, $isPostgresql=false ){

		$paramsArray = array();
		$sqlParams = "";
		$paramsPosttgress = "";

		//автоматический сбор полей с процедуры
		$queryFields = $this->db->query("
			select
				name as \"Parameter_name\",
				type.typname as \"Type\"
			from (
				select 
					unnest(p.proargnames) as name,
					unnest(p.proargtypes) as type_oid
				from 
					pg_catalog.pg_proc p
				where 
					lower(p.proname) = lower('{$inputProcedure}')
			) params
			left join pg_catalog.pg_type type on type.oid = params.type_oid
		");
		$allFields = $queryFields->result_array();

		//получаем список всех возможных полей
		foreach ($allFields as $fieldVal)
		{
			$field = ltrim($fieldVal["Parameter_name"], ":");

			//получение значений параметров
			if( isset($params[$field]) && !empty($params[$field]) ){
				//небольшая ремарка для полей boolean-овского типа
				if($params[$field] == 'true') $params[$field] = 2;
				if($params[$field] == 'false') $params[$field] = 1;
				//
				$paramsArray[$field] = $params[$field];
				//список полей и значений которые определены
				if( empty($exceptedFields) || !(in_array($field, $exceptedFields)) ) {
					if($isPostgresql){
						$paramsPosttgress .= $params[$field].",\r\n";
						$sqlParams .= $p.",\r\n";
					}
					else{
						$sqlParams .= $field." := :".$field.",\r\n";
					}
				}
			}
		}

		//список параметров, значения параметров
		return array(
			"paramsArray" => ($isPostgresql)?$paramsPosttgress:$paramsArray,
			"sqlParams" => $sqlParams
		);
	}

	/**
	 * @desc Сохранение карты(талона) вызова
	 */
	function saveCmpCallCard( $data, $cccConfig = null ){
		$procedure = '';
		$statuschange = true;
		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));
		$checkLock = $this->checkLockCmpCallCard( $data );
		if ( $checkLock != false && is_array( $checkLock ) && isset( $checkLock[ 0 ] ) && isset( $checkLock[ 0 ][ 'CmpCallCard_id' ] ) ) {
			return array( array( 'Error_Msg' => 'Невозможно сохранить. Карта вызова редактируется другим пользователем' ) );
		}
		$this->db->trans_begin();
		//при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
		//перенес из контроллера
		if(empty($data['CmpCallCardInputType_id'])){
			$Person_id = $this->checkUnknownPerson($data);
			if ($Person_id) {
				$data['Person_IsUnknown'] = 2;
				$data['Person_id'] = ($Person_id!==true)?$Person_id:null;
			}
		}

		if (!isset($data['CmpCallCard_prmDT'])) {
			$curdate = new DateTime();
			$data['CmpCallCard_prmDT'] = $curdate->format('Y-m-d H:i:s');
		}

		$data['AcceptTime'] = $data['CmpCallCard_prmDT'];

		$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
		$CmpCallCard_Numv = 'null';
		$CmpCallCard_Ngod = 'null';

		/*
		$CmpCallCard_Numv = (
			($data[ 'ARMType' ] == 'smpheaddoctor') ||
			($data[ 'ARMType' ] == 'smpdispatchcall' && !empty( $data[ 'CmpCallCard_id' ] )) ) ? ':CmpCallCard_Numv' : '@UnicCmpCallCard_Numv';
		$CmpCallCard_Ngod = (!empty($data['CmpCallCard_Ngod']) && !empty( $data[ 'CmpCallCard_id' ] )) ? ':CmpCallCard_Ngod' : '@UnicCmpCallCard_Ngod';
		*/
		if(getRegionNick() != 'krym') {

			/* определяем степень срочности */
			$Ufilter = 'CCCUAPS.Lpu_id = :Lpu_id';
			$UqueryParams = array('Lpu_id' => $data['Lpu_id']);

			if (!empty($data['CmpReason_id'])) {
				$Ufilter .= " and CCCUAPS.CmpReason_id = :CmpReason_id";
				$UqueryParams['CmpReason_id'] = $data['CmpReason_id'];
			}
			if (!empty($data['Person_Age'])) {
				$Ufilter .= " and ((CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf > :Person_Age) or (CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf is null))";
				$UqueryParams['Person_Age'] = $data['Person_Age'];
			}
			if (!empty($data['CmpCallPlaceType_id'])) {
				$Ufilter .= " and CUAPSRP.CmpCallPlaceType_id = :CmpCallPlaceType_id";
				$UqueryParams['CmpCallPlaceType_id'] = $data['CmpCallPlaceType_id'];
			} else {
				$Ufilter .= " and CUAPSRP.CmpCallPlaceType_id is null";
			}

			$Uquery = "
					SELECT
						CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
                        CmpUrgencyAndProfileStandart_Urgency as \"CmpUrgencyAndProfileStandart_Urgency\",
                        CmpUrgencyAndProfileStandart_UntilAgeOf as \"CmpUrgencyAndProfileStandart_UntilAgeOf\",
                        pmUser_insID as \"pmUser_insID\",
                        pmUser_updID as \"pmUser_updID\",
                        CmpUrgencyAndProfileStandart_insDT as \"CmpUrgencyAndProfileStandart_insDT\",
                        CmpUrgencyAndProfileStandart_updDT as \"CmpUrgencyAndProfileStandart_updDT\",
                        CmpCallCardAcceptor_id as \"CmpCallCardAcceptor_id\",
                        CmpUrgencyAndProfileStandart_HeadDoctorObserv as \"CmpUrgencyAndProfileStandart_HeadDoctorObserv\",
                        CmpUrgencyAndProfileStandart_MultiVictims as \"CmpUrgencyAndProfileStandart_MultiVictims\",
                        CmpUrgencyAndProfileStandart_GUID as \"CmpUrgencyAndProfileStandart_GUID\",
                        CmpReason_id as \"CmpReason_id\",
                        Lpu_id as \"Lpu_id\"
					FROM
						v_CmpUrgencyAndProfileStandart as CCCUAPS 
						LEFT JOIN v_CmpUrgencyAndProfileStandartRefPlace CUAPSRP  on CUAPSRP.CmpUrgencyAndProfileStandart_id = CCCUAPS.CmpUrgencyAndProfileStandart_id
					WHERE
						$Ufilter
				";

			$Uresult = $this->db->query($Uquery, $UqueryParams);

			if (is_object($Uresult)) {
				$res = $Uresult->result('array');
				if (isset($res[0]['CmpUrgencyAndProfileStandart_Urgency'])) {
					$urgency = $res[0]['CmpUrgencyAndProfileStandart_Urgency'];
					if (isset($urgency) && $urgency > 0) {
						$data['CmpCallCard_Urgency'] = $urgency;
					}
				}
			} else {
				return false;
			}
		}

			/* определили срочность */

		if ( empty( $data[ 'CmpCallCard_id' ] ) ) {
			$procedure = 'p_CmpCallCard_ins';

			if (empty($data['CmpCallCard_storDT'])) {
				// Убрал условие на пустые номера, теперь пересчитываются всегда (#143245)
				// Кроме ситуации, когда они приходят из смп (#143598) (вынес ниже)

				$nums = $this->CmpCallCard_model->getCmpCallCardNumber($data);
				$CmpCallCard_Numv = $nums[0]["CmpCallCard_Numv"];
				$CmpCallCard_Ngod = $nums[0]["CmpCallCard_Ngod"];

			}

			$exceptedFields = array('CmpCallCard_id', 'CmpCallCard_Ngod', 'CmpCallCard_Numv');

		} else {


			if (empty($data['CmpCallCard_Numv']) || empty($data['CmpCallCard_Ngod'])) {
				$nums = $this->CmpCallCard_model->getCmpCallCardNumber($data);
				$CmpCallCard_Numv = $nums[0]["CmpCallCard_Numv"];
				$CmpCallCard_Ngod = $nums[0]["CmpCallCard_Ngod"];
			} else {
				$data['Day_num'] = $data['CmpCallCard_Numv'];
				$data['Year_num'] = $data['CmpCallCard_Ngod'];
				$nums = $this->CmpCallCard_model->existenceNumbersDayYear($data);
				if (is_array($nums)) {
					$CmpCallCard_Numv = $nums["nextNumberDay"];
					$CmpCallCard_Ngod = $nums["nextNumberYear"];
				} else {
					return array(array('Error_Msg' => 'Ошибка при определении номера вызова'));
				}
			}


			/* 1 - выбираем старую запись */
			$query = "
					SELECT Person_id as \"Person_id\",
                           CmpCallCard_id as \"CmpCallCard_id\",
                           Person_SurName as \"Person_SurName\",
                           CmpCallCard_Numv as \"CmpCallCard_Numv\",
                           CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                           CmpCallCard_Prty as \"CmpCallCard_Prty\",
                           CmpCallCard_Sect as \"CmpCallCard_Sect\",
                           CmpArea_id as \"CmpArea_id\",
                           CmpCallCard_City as \"CmpCallCard_City\",
                           CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
                           CmpCallCard_Dom as \"CmpCallCard_Dom\",
                           CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                           CmpLpu_aid as \"CmpLpu_aid\",
                           CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                           cmpCallCard_Medc as \"cmpCallCard_Medc\",
                           CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
                           CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
                           CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
                           CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
                           CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
                           CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
                           CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
                           CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
                           CCC.pmUser_insID as \"pmUser_insID\",
                           CCC.pmUser_updID as \"pmUser_updID\",
                           CmpCallCard_insDT as \"CmpCallCard_insDT\",
                           CmpCallCard_updDT as \"CmpCallCard_updDT\",
                           KLRgn_id as \"KLRgn_id\",
                           KLSubRgn_id as \"KLSubRgn_id\",
                           CmpCallCard_Podz as \"CmpCallCard_Podz\",
                           CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                           CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                           CmpCallCard_Telf as \"CmpCallCard_Telf\",
                           CmpPlace_id as \"CmpPlace_id\",
                           CmpCallCard_Comm as \"CmpCallCard_Comm\",
                           CmpReason_id as \"CmpReason_id\",
                           CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
                           CmpCallCard_Condition as \"CmpCallCard_Condition\",
                           Lpu_cid as \"Lpu_cid\",
                           CmpCallCard_Tend as \"CmpCallCard_Tend\",
                           CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
                           CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                           CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
                           CmpRejectionReason_id as \"CmpRejectionReason_id\",
                           CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
                           CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
                           CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
                           CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
                           CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
                           CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                           CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
                           CmpCallCard_GUID as \"CmpCallCard_GUID\",
                           CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
                           KLCity_id as \"KLCity_id\",
                           KLTown_id as \"KLTown_id\",
                           KLStreet_id as \"KLStreet_id\",
                           Lpu_ppdid as \"Lpu_ppdid\",
                           CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
                           CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
                           CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                           CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
                           CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
                           CmpPPDResult_id as \"CmpPPDResult_id\",
                           EmergencyTeam_id as \"EmergencyTeam_id\",
                           CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
                           CCC.Lpu_id as \"Lpu_id\",
                           CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
                           CCC.MedPersonal_id as \"MedPersonal_id\",
                           ResultDeseaseType_id as \"ResultDeseaseType_id\",
                           CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
                           UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                           CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
                           CmpCallCard_Korp as \"CmpCallCard_Korp\",
                           CmpCallCard_Room as \"CmpCallCard_Room\",
                           CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
                           UslugaComplex_id as \"UslugaComplex_id\",
                           LpuBuilding_id as \"LpuBuilding_id\",
                           CmpCallerType_id as \"CmpCallerType_id\",
                           CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                           CmpCallCard_rid as \"CmpCallCard_rid\",
                           CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                           CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
                           CmpSecondReason_id as \"CmpSecondReason_id\",
                           CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
                           CmpCallReasonType_id as \"CmpCallReasonType_id\",
                           CmpReasonNew_id as \"CmpReasonNew_id\",
                           CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
                           CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
                           CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
                           CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
                           MedStaffFact_id as \"MedStaffFact_id\",
                           RankinScale_id as \"RankinScale_id\",
                           RankinScale_sid as \"RankinScale_sid\",
                           LeaveType_id as \"LeaveType_id\",
                           CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
                           LpuSection_id as \"LpuSection_id\",
                           CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
                           CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
                           EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
                           CmpCallCard_storDT as \"CmpCallCard_storDT\",
                           CmpCallCard_defCom as \"CmpCallCard_defCom\",
                           MedService_id as \"MedService_id\",
                           CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
                           CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                           Diag_sopid as \"Diag_sopid\",
                           CmpLeaveType_id as \"CmpLeaveType_id\",
                           CmpLeaveTask_id as \"CmpLeaveTask_id\",
                           CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
                           CmpTransportType_id as \"CmpTransportType_id\",
                           CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
                           CmpCallCardResult_id as \"CmpCallCardResult_id\",
                           Person_IsUnknown as \"Person_IsUnknown\",
                           CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
                           Lpu_smpid as \"Lpu_smpid\",
                           Lpu_hid as \"Lpu_hid\",
                           UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
                           PayType_id as \"PayType_id\",
                           CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
                           CmpCallCard_sid as \"CmpCallCard_sid\",
                           CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
                           CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
                           CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
                           CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
                           CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
                           CmpCallSignType_id as \"CmpCallSignType_id\",
                           Lpu_CodeSMO as \"Lpu_CodeSMO\",
                           Registry_sid as \"Registry_sid\",
                           Diag_gid as \"Diag_gid\",
                           MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
                           CmpCommonState_id as \"CmpCommonState_id\",
                           Person_FirName as \"Person_FirName\",
                           Person_SecName as \"Person_SecName\",
                           Person_Age as \"Person_Age\",
                           Person_BirthDay as \"Person_BirthDay\",
                           Person_PolisSer as \"Person_PolisSer\",
                           Person_PolisNum as \"Person_PolisNum\",
                           Sex_id as \"Sex_id\",
                           CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                           CmpCallType_id as \"CmpCallType_id\",
                           CmpProfile_cid as \"CmpProfile_cid\",
                           CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
                           CmpCallCard_Stan as \"CmpCallCard_Stan\",
                           CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
                           CmpCallCard_Line as \"CmpCallCard_Line\",
                           CmpResult_id as \"CmpResult_id\",
                           CmpArea_gid as \"CmpArea_gid\",
                           CmpLpu_id as \"CmpLpu_id\",
                           CmpDiag_oid as \"CmpDiag_oid\",
                           CmpDiag_aid as \"CmpDiag_aid\",
                           CmpTrauma_id as \"CmpTrauma_id\",
                           CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
                           Diag_uid as \"Diag_uid\",
                           CmpCallCard_Numb as \"CmpCallCard_Numb\",
                           CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
                           CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
                           CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
                           CmpProfile_bid as \"CmpProfile_bid\",
                           CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
                           CmpCallCard_RCod as \"CmpCallCard_RCod\",
                           CmpCallCard_TabN as \"CmpCallCard_TabN\",
                           CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
                           CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
                           CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
                           CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
                           Diag_sid as \"Diag_sid\",
                           CmpTalon_id as \"CmpTalon_id\",
                           CmpCallCard_Expo as \"CmpCallCard_Expo\",
                           CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
                           CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
                           CmpCallCard_D201 as \"CmpCallCard_D201\",
                           CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
                           CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
                           CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
                           CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
                           CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
                           CmpCallCard_Tper as \"CmpCallCard_Tper\",
                           CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
                           CmpCallCard_Przd as \"CmpCallCard_Przd\",
                           CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
                           CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
                           CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
                           CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
                           CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
                           CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
                           CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
                           CmpArea_pid as \"CmpArea_pid\",
                           CmpCallCard_PCity as \"CmpCallCard_PCity\",
                           CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
                           CmpCallCard_PDom as \"CmpCallCard_PDom\",
                           CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
                           RTRIM(PUC.PMUser_surName || ' ' || SUBSTRING(PUC.PMUser_firName, 1, 1) || ' ' || SUBSTRING(PUC.PMUser_secName, 1, 1)) as \"pmUser_FIO\"
                    FROM v_CmpCallCard CCC
                         LEFT JOIN v_pmUserCache as PUC ON (PUC.PMUser_id = CCC.pmUser_updID)
                    WHERE CCC.CmpCallCard_id =:CmpCallCard_id
				";

			$result = $this->db->query( $query, array(
				'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
			) );

			if ( !is_object( $result ) ) {
				return false;
			}
			$result = $result->result( 'array' );

			/* Делаем копию исходной записи, а измененную копию сохраняем на место старой */
			if (isset($result[0])) {
				$oldCard = $result[0];

				//если не пустое поле CmpCallCard_updDT - значит нам нужна проверка, была ли изменена карта
				//пока мы копошились с ее редактированием
				if (
					!empty($data['CmpCallCard_updDT']) &&
					$oldCard['CmpCallCard_updDT'] != new DateTime($data['CmpCallCard_updDT'])
				) {
					//$this->db->trans_rollback();
					//return array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Вызов был отредактирован пользователем "' . $oldCard['pmUser_FIO'] . '". Обновите информацию по вызову и повторите действия');
				}

				$data['CmpCallCard_IsReceivedInPPD'] = !empty($data['CmpCallCard_IsReceivedInPPD']) ? $data['CmpCallCard_IsReceivedInPPD'] : $oldCard['CmpCallCard_IsReceivedInPPD'];
				/* 2 - сохраняем старую запись в новую */

				$res = $this->resaveCmpCallCard($oldCard, null);
				$newfield = $res->result('array');

				/* 2.2 - удаляем(скрываем) эту карту */
				$q = "
					from p_CmpCallCard_del
					    CmpCallCard_id = " . $newfield[0]['CmpCallCard_id'] . ",
					    pmUser_id = " . $data['pmUser_id'] . ";

					from p_CmpCallCard_setFirstVersion
							CmpCallCard_id = " . $newfield[0]['CmpCallCard_id'] . ",
							CmpCallCard_firstVersion = " . $oldCard['CmpCallCard_id'] . ",
							pmUser_id = " . $data['pmUser_id'] . ";
				";
				if ($dolog) $this->textlog->add('ccc_m4E_3 удаление:' . $newfield[0]['CmpCallCard_id'] . 'ver(' . $data['CmpCallCard_id'] . ')');
				$r = $this->db->query($q, $data);

				//Оставим без изменений время приема вызова и статус
				$data["CmpCallCard_prmDT"] = $oldCard['CmpCallCard_prmDT'];
				if(!empty($oldCard['CmpCallCardStatusType_id']) && $oldCard['CmpCallCardStatusType_id'] != 20){
					$data["Lpu_id"] = $oldCard['Lpu_id'];
                    $data["CmpCallCardStatus_id"] = $oldCard['CmpCallCardStatus_id'];
                }



				$procedure = 'p_CmpCallCard_setCardUpd';
			}else{
					//Ситуация когда изменился вид вызова и талона еще нет на основном сервере
					//Либо на основном сервере нет талона связанного с 112
					$procedure = 'p_CmpCallCard_ins';

			}

			/* 3 - заменяем старую запись текущими изменениями */

			//Отдельное условие для 112 т.к вызов уже сущетсвует и ему нужно пересчитать дату приема вызова и номера
			if(!empty($oldCard) && $oldCard['CmpCallCardStatusType_id'] == 20 && $oldCard['CmpCallCardStatusType_id'] != $data['CmpCallCardStatusType_id']){
				$data['CmpCallCard_prmDT'] = $this->getCurrentDT()->format('Y-m-d H:i:s');

				$nums = $this->CmpCallCard_model->getCmpCallCardNumber($data);
				$CmpCallCard_Numv = $nums[0]["CmpCallCard_Numv"];
				$CmpCallCard_Ngod = $nums[0]["CmpCallCard_Ngod"];
			}


			$statuschange = false;
			$exceptedFields[] = 'CmpCallCard_Numv';
			$exceptedFields[] = 'CmpCallCard_Ngod';
			//$exceptedFields[] = 'CmpCallCard_prmDT';
			$exceptedFields[] = 'CmpCallCard_id';
		}

		if ( isset($data['Person_Birthday']) ) {
			$data['Person_BirthDay'] = $data['Person_Birthday'];
		}

		if(!empty($oldCard) && $oldCard['CmpCallCardStatusType_id'] == 20){
			//Разрешенные статусы для 112, иначе - Передано
			$data['CmpCallCardStatusType_id'] = (!empty($data['CmpCallCardStatusType_id']) && in_array($data['CmpCallCardStatusType_id'], array(1,6,16,18,19,21)) ) ? $data['CmpCallCardStatusType_id'] : 1;
		}
		elseif(empty($oldCard)){
			$data['CmpCallCardStatusType_id'] = !empty($data['CmpCallCardStatusType_id'])?$data['CmpCallCardStatusType_id']:null;
		}
		else{
			$data['CmpCallCardStatusType_id'] = !empty($oldCard['CmpCallCardStatusType_id'])?$oldCard['CmpCallCardStatusType_id']:null;
		}

        if (!empty($oldCard)){
            //проставляем время старого талона, если не обновили на форме
            if (empty($data['CmpCallCard_Tper']) && !empty($oldCard['CmpCallCard_Tper'])) {
                $data['CmpCallCard_Tper'] = date_format($oldCard['CmpCallCard_Tper'], 'Y-m-d H:i:s');
            }
            if (empty($data['CmpCallCard_Vyez']) && !empty($oldCard['CmpCallCard_Vyez'])) {
                $data['CmpCallCard_Vyez'] = date_format($oldCard['CmpCallCard_Vyez'], 'Y-m-d H:i:s');
            }
            if (empty($data['CmpCallCard_Przd']) && !empty($oldCard['CmpCallCard_Przd'])) {
                $data['CmpCallCard_Przd'] = date_format($oldCard['CmpCallCard_Przd'], 'Y-m-d H:i:s');
            }
            if (empty($data['CmpCallCard_Tisp']) && !empty($oldCard['CmpCallCard_Tisp'])) {
                $data['CmpCallCard_Tisp'] = date_format($oldCard['CmpCallCard_Tisp'], 'Y-m-d H:i:s');
            }
            if (empty($data['CmpCallCard_HospitalizedTime']) && !empty($oldCard['CmpCallCard_HospitalizedTime'])) {
                $data['CmpCallCard_HospitalizedTime'] = date_format($oldCard['CmpCallCard_HospitalizedTime'], 'Y-m-d H:i:s');
            }
        }


		//$exceptedFields[] = 'EmergencyTeam_id';
		//4 замена / вставка карты
		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, $exceptedFields, false);
		$genQueryParams = $genQuery["paramsArray"];
		$genQuerySQL = $genQuery["sqlParams"];

		//доп параметры
		$genQueryParams["CmpCallCard_id"] = isset($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null;
		$genQueryParams["Lpu_id_forUnicNumRequest"] = $data["Lpu_id"];
		$genQueryParams["CmpCallCard_saveDT"] = $data[ 'CmpCallCard_prmDT' ];

		if(!empty($oldCard) && $oldCard['CmpCallCardStatusType_id'] != 20){
			$this -> CmpCallCard_model -> checkChangesCmpCallCard($oldCard, $genQueryParams);

			if($oldCard['LpuBuilding_id'] != $data['LpuBuilding_id']){
				//поменяли подстанцию
				$this -> sendCmpCallCardToLpuBuilding($data);
			}
		};

		//если пришел гуид, то значит мы сохраняем во 2 базу и они должны быть одинаковые с 1-й
		$genQueryParams[ 'CmpCallCard_GUID' ] = null;
		
		if(!empty($cccConfig)){
			$CmpCallCard_Numv = $cccConfig["CmpCallCard_Numv"];
			$CmpCallCard_Ngod = $cccConfig["CmpCallCard_Ngod"];
			$genQueryParams[ 'CmpCallCard_GUID' ] = $cccConfig[ 'CmpCallCard_GUID' ];
			$genQueryParams[ 'CmpCallCard_id' ] = $cccConfig[ 'CmpCallCard_id' ];
			if(!empty($cccConfig[ 'CmpCallCard_prmDT' ])){
				$genQueryParams[ 'CmpCallCard_prmDT' ] = $cccConfig[ 'CmpCallCard_prmDT' ];
			}
		} else if (!empty($data['CmpCallCard_insID'])) {
			$genQueryParams['CmpCallCard_id'] = $data['CmpCallCard_insID'];
		}

		$query = "
            select 
                CmpCallCard_id as \"CmpCallCard_id\",
                CmpCallCard_GUID as \"CmpCallCard_GUID\",
                ErrCode as \"Error_Code\",
                ErrMessage as \"Error_Msg\"
			from ".$procedure." (
				CmpCallCard_id := :CmpCallCard_id,
				CmpCallCard_GUID := :CmpCallCard_GUID,
				CmpCallCard_Numv := ".$CmpCallCard_Numv.",
				CmpCallCard_Ngod := ".$CmpCallCard_Ngod.",
				$genQuerySQL
				)
		";		

		$queryParams = $genQueryParams;
		//var_dump(getDebugSQL($query, $queryParams)); exit;
		$result = $this->db->query( $query, $queryParams );

		//получаем ИД вставленной записи

		$resultforstatus = array();
		$resultforstatus = $result->result( 'array' );

		if ( is_object( $result ) ) {
			$armType = '';
			if(!empty($data['ARMType']))
				$armType = $data['ARMType'];
			$hostname = $this->db->hostname;
			$database = $this->db->database;
			if(is_object($queryParams['CmpCallCard_prmDT']))
				$queryParams['CmpCallCard_prmDT'] = date_format($queryParams['CmpCallCard_prmDT'], 'Y-m-d H:i:s');
			if($dolog)$this->textlog->add('ccc_m4E_1 сохранение:'.$resultforstatus[0]['CmpCallCard_id'].' / '.$CmpCallCard_Numv.' / '.$CmpCallCard_Ngod.'/'. $queryParams['CmpCallCard_prmDT'].' arm:'.$armType.' proc:'.$procedure.'/'.$hostname.'/'.$database);
			if($dolog)$this->textlog->add('повтор проверки для CmpCallCard_id '.$resultforstatus[0]['CmpCallCard_id']);
			//повторная проверка на уникальность номеров карты

			$query = "
					SELECT Person_id as \"Person_id\",
                           CmpCallCard_id as \"CmpCallCard_id\",
                           Person_SurName as \"Person_SurName\",
                           CmpCallCard_Numv as \"CmpCallCard_Numv\",
                           CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                           CmpCallCard_Prty as \"CmpCallCard_Prty\",
                           CmpCallCard_Sect as \"CmpCallCard_Sect\",
                           CmpArea_id as \"CmpArea_id\",
                           CmpCallCard_City as \"CmpCallCard_City\",
                           CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
                           CmpCallCard_Dom as \"CmpCallCard_Dom\",
                           CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                           CmpLpu_aid as \"CmpLpu_aid\",
                           CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                           cmpCallCard_Medc as \"cmpCallCard_Medc\",
                           CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
                           CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
                           CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
                           CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
                           CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
                           CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
                           CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
                           CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
                           CCC.pmUser_insID as \"pmUser_insID\",
                           CCC.pmUser_updID as \"pmUser_updID\",
                           CmpCallCard_insDT as \"CmpCallCard_insDT\",
                           CmpCallCard_updDT as \"CmpCallCard_updDT\",
                           KLRgn_id as \"KLRgn_id\",
                           KLSubRgn_id as \"KLSubRgn_id\",
                           CmpCallCard_Podz as \"CmpCallCard_Podz\",
                           CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                           CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                           CmpCallCard_Telf as \"CmpCallCard_Telf\",
                           CmpPlace_id as \"CmpPlace_id\",
                           CmpCallCard_Comm as \"CmpCallCard_Comm\",
                           CmpReason_id as \"CmpReason_id\",
                           CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
                           CmpCallCard_Condition as \"CmpCallCard_Condition\",
                           Lpu_cid as \"Lpu_cid\",
                           CmpCallCard_Tend as \"CmpCallCard_Tend\",
                           CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
                           CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                           CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
                           CmpRejectionReason_id as \"CmpRejectionReason_id\",
                           CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
                           CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
                           CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
                           CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
                           CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
                           CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                           CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
                           CmpCallCard_GUID as \"CmpCallCard_GUID\",
                           CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
                           KLCity_id as \"KLCity_id\",
                           KLTown_id as \"KLTown_id\",
                           KLStreet_id as \"KLStreet_id\",
                           Lpu_ppdid as \"Lpu_ppdid\",
                           CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
                           CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
                           CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                           CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
                           CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
                           CmpPPDResult_id as \"CmpPPDResult_id\",
                           EmergencyTeam_id as \"EmergencyTeam_id\",
                           CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
                           CCC.Lpu_id as \"Lpu_id\",
                           CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
                           CCC.MedPersonal_id as \"MedPersonal_id\",
                           ResultDeseaseType_id as \"ResultDeseaseType_id\",
                           CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
                           UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                           CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
                           CmpCallCard_Korp as \"CmpCallCard_Korp\",
                           CmpCallCard_Room as \"CmpCallCard_Room\",
                           CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
                           UslugaComplex_id as \"UslugaComplex_id\",
                           LpuBuilding_id as \"LpuBuilding_id\",
                           CmpCallerType_id as \"CmpCallerType_id\",
                           CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                           CmpCallCard_rid as \"CmpCallCard_rid\",
                           CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                           CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
                           CmpSecondReason_id as \"CmpSecondReason_id\",
                           CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
                           CmpCallReasonType_id as \"CmpCallReasonType_id\",
                           CmpReasonNew_id as \"CmpReasonNew_id\",
                           CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
                           CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
                           CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
                           CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
                           MedStaffFact_id as \"MedStaffFact_id\",
                           RankinScale_id as \"RankinScale_id\",
                           RankinScale_sid as \"RankinScale_sid\",
                           LeaveType_id as \"LeaveType_id\",
                           CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
                           LpuSection_id as \"LpuSection_id\",
                           CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
                           CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
                           EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
                           CmpCallCard_storDT as \"CmpCallCard_storDT\",
                           CmpCallCard_defCom as \"CmpCallCard_defCom\",
                           MedService_id as \"MedService_id\",
                           CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
                           CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                           Diag_sopid as \"Diag_sopid\",
                           CmpLeaveType_id as \"CmpLeaveType_id\",
                           CmpLeaveTask_id as \"CmpLeaveTask_id\",
                           CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
                           CmpTransportType_id as \"CmpTransportType_id\",
                           CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
                           CmpCallCardResult_id as \"CmpCallCardResult_id\",
                           Person_IsUnknown as \"Person_IsUnknown\",
                           CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
                           Lpu_smpid as \"Lpu_smpid\",
                           Lpu_hid as \"Lpu_hid\",
                           UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
                           PayType_id as \"PayType_id\",
                           CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
                           CmpCallCard_sid as \"CmpCallCard_sid\",
                           CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
                           CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
                           CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
                           CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
                           CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
                           CmpCallSignType_id as \"CmpCallSignType_id\",
                           Lpu_CodeSMO as \"Lpu_CodeSMO\",
                           Registry_sid as \"Registry_sid\",
                           Diag_gid as \"Diag_gid\",
                           MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
                           CmpCommonState_id as \"CmpCommonState_id\",
                           Person_FirName as \"Person_FirName\",
                           Person_SecName as \"Person_SecName\",
                           Person_Age as \"Person_Age\",
                           Person_BirthDay as \"Person_BirthDay\",
                           Person_PolisSer as \"Person_PolisSer\",
                           Person_PolisNum as \"Person_PolisNum\",
                           Sex_id as \"Sex_id\",
                           CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                           CmpCallType_id as \"CmpCallType_id\",
                           CmpProfile_cid as \"CmpProfile_cid\",
                           CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
                           CmpCallCard_Stan as \"CmpCallCard_Stan\",
                           CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
                           CmpCallCard_Line as \"CmpCallCard_Line\",
                           CmpResult_id as \"CmpResult_id\",
                           CmpArea_gid as \"CmpArea_gid\",
                           CmpLpu_id as \"CmpLpu_id\",
                           CmpDiag_oid as \"CmpDiag_oid\",
                           CmpDiag_aid as \"CmpDiag_aid\",
                           CmpTrauma_id as \"CmpTrauma_id\",
                           CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
                           Diag_uid as \"Diag_uid\",
                           CmpCallCard_Numb as \"CmpCallCard_Numb\",
                           CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
                           CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
                           CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
                           CmpProfile_bid as \"CmpProfile_bid\",
                           CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
                           CmpCallCard_RCod as \"CmpCallCard_RCod\",
                           CmpCallCard_TabN as \"CmpCallCard_TabN\",
                           CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
                           CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
                           CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
                           CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
                           Diag_sid as \"Diag_sid\",
                           CmpTalon_id as \"CmpTalon_id\",
                           CmpCallCard_Expo as \"CmpCallCard_Expo\",
                           CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
                           CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
                           CmpCallCard_D201 as \"CmpCallCard_D201\",
                           CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
                           CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
                           CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
                           CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
                           CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
                           CmpCallCard_Tper as \"CmpCallCard_Tper\",
                           CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
                           CmpCallCard_Przd as \"CmpCallCard_Przd\",
                           CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
                           CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
                           CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
                           CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
                           CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
                           CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
                           CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
                           CmpArea_pid as \"CmpArea_pid\",
                           CmpCallCard_PCity as \"CmpCallCard_PCity\",
                           CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
                           CmpCallCard_PDom as \"CmpCallCard_PDom\",
                           CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
                           to_char(cast (CCC.CmpCallCard_prmDT as timestamp (3)), 'DD.MM.YYYY') || ' ' || to_char(cast (CCC.CmpCallCard_prmDT as timestamp (3)), 'HH24:MI:SS') as \"CmpCallCard_prmDT\"
                    FROM v_CmpCallCard CCC
                    WHERE CCC.CmpCallCard_id =:CmpCallCard_id;
				";

			$newcardresult = $this->db->query( $query, array(
				'CmpCallCard_id' => $resultforstatus[0]['CmpCallCard_id']
			) );
			$newcard = $newcardresult->result( 'array' );
			if(is_array($newcard) && count($newcard) > 0){
				$newcard[0]['Day_num'] = $newcard[0]['CmpCallCard_Numv'];
				$newcard[0]['Year_num'] = $newcard[0]['CmpCallCard_Ngod'];
				$newcard[0]['AcceptTime'] = $newcard[0]['CmpCallCard_prmDT'];
				$nums = $this->CmpCallCard_model->existenceNumbersDayYear($newcard[0]);
				if(is_array($nums) && ($nums['existenceNumbersDay'] || $nums['existenceNumbersYear'])
					&& (!empty($nums['double_insDT']) && $newcard[0]['CmpCallCard_insDT'] > $nums['double_insDT'])){
					$updateParams = array(
						'CmpCallCard_id' => $resultforstatus[0]['CmpCallCard_id'],
						'CmpCallCard_Numv' => $nums['nextNumberDay'],
						'CmpCallCard_Ngod' => $nums['nextNumberYear'],
						'pmUser_id' => $data['pmUser_id']
					);
					$hostname = $this->db->hostname;
					$database = $this->db->database;
					$this->swUpdate('CmpCallCard', $updateParams, false);
					if($dolog)$this->textlog->add('ccc_m4E_2 обновление дубл.парам:'.$resultforstatus[0]['CmpCallCard_id'].' / '.$nums['nextNumberDay'].' / '.$nums['nextNumberYear'].'/'.$hostname.'/'.$database);
					// По задаче #137883 после смены номера на СМП, нужно обновить также на основном сервере
					if(!empty($cccConfig)){
						//значит мы на основной БД main, нужно пересохранить и на СМП
						$IsMainServer = $this->config->item('IsMainServer');
						$IsSMPServer = $this->config->item('IsSMPServer');
						unset($this->db);

						try{
							if($IsSMPServer){
								$this->db = $this->load->database('default', true);
							}
							else{
								$this->db = $this->load->database('smp', true);
							}
						} catch (Exception $e) {
							$this->load->database();
							$errMsg = "Нет связи с сервером: создание нового вызова недоступно";
							$this->ReturnError($errMsg);
							return false;
						}
						$hostname = $this->db->hostname;
						$database = $this->db->database;
						//сохраняем на СМП
						$this->swUpdate('CmpCallCard', $updateParams, false);
						if($dolog)$this->textlog->add('ccc_m4E_2 smp обновление дубл.парам:'.$resultforstatus[0]['CmpCallCard_id'].' / '.$nums['nextNumberDay'].' / '.$nums['nextNumberYear'].'/'.$hostname.'/'.$database);
						unset($this->db);
						//возвращаемся на рабочую (она main на СМП сервере или default на основном
						if($IsMainServer === true) {
							$this->db = $this->load->database('main', true);
						}
						else{
							$this->db = $this->load->database('default', true);
						}

					}
					$CmpCallCard_Numv = $nums['nextNumberDay'];
					$CmpCallCard_Ngod = $nums['nextNumberYear'];
				}
			}

			$data['CmpCallCard_id'] = $resultforstatus[0]['CmpCallCard_id'];

            if ($data['CmpCallCard_IsActiveCall'] == 2){
                $this->setCmpCallCard_isTimeExceeded(array(array('CmpCallCard_id' => $data['CmpCallCard_id'])),$data['pmUser_id'],1);
            }

			if(!isset($data['withoutChangeStatus']) || !$data['withoutChangeStatus']){
				$statusResult = $this->checkCallStatusOnSave($data);
				if(!empty($statusResult[0])){
					$data['CmpCallCardStatus_id'] = $statusResult[0]['CmpCallCardStatus_id'];
					$data['CmpCallCardEvent_id'] = $statusResult[0]['CmpCallCardEvent_id'];
				}
			}

			//Для множественных вызовов регистрируем события
			if(!empty($data['CmpCallCard_sid']) && ($procedure == 'p_CmpCallCard_ins')){

				$query = "
					SELECT
						CmpCallCard_id as \"CmpCallCard_id\",
						CmpCallCard_Numv as \"CmpCallCard_Numv\",
						COALESCE(Person_SurName,'') || ' ' || COALESCE(Person_FirName,'') || ' ' || COALESCE(Person_SecName,'')

						 as \"Person_FIO\"
					FROM v_CmpCallCard CCC 

					WHERE CCC.CmpCallCard_id = :CmpCallCard_sid or (CCC.CmpCallCard_sid = :CmpCallCard_sid and CCC.CmpCallCard_id <> :CmpCallCard_id )
				";

				$connectedCallsRes = $this->db->query( $query, array(
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'CmpCallCard_sid' => $data['CmpCallCard_sid']
				) );
				$connectedCalls = $connectedCallsRes->result( 'array' );

				foreach($connectedCalls as $call){

					//Регистрация нового вызова для старого
					$eventParams = array(
						"CmpCallCard_id" => $call["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 26,
						"CmpCallCardEvent_Comment" => $CmpCallCard_Numv . ', ' . $data['Person_SurName'] . ' ' . $data['Person_FirName'] . ' ' . $data['Person_SecName'],
						"pmUser_id" => $data["pmUser_id"]
					);
					$this->CmpCallCard_model->setCmpCallCardEvent( $eventParams );

					//Регистрация старого вызова для нового
					$eventParams = array(
						"CmpCallCard_id" => $data["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 26,
						"CmpCallCardEvent_Comment" => $call['CmpCallCard_Numv'] . ', ' . $call['Person_FIO'],
						"pmUser_id" => $data["pmUser_id"]
					);
					$this->CmpCallCard_model->setCmpCallCardEvent( $eventParams );

				}

			}

			//связка выбранного нода в дереве принятия решений с картой
			if(!empty($data['AmbulanceDecigionTree_id'])){
				//@todo создать метод связки
				$this -> appendCardToAmbulanceDT(array(
					'AmbulanceDecigionTree_id' => $data['AmbulanceDecigionTree_id'],
					'CmpCallCard_id' => $resultforstatus[0]['CmpCallCard_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}

			$out = $result->result( 'array' );
			$out['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
			$out['CmpCallCardStatus_id'] = !empty($data['CmpCallCardStatus_id']) ? $data['CmpCallCardStatus_id'] : null;
			$out['CmpCallCardEvent_id'] = !empty($data['CmpCallCardEvent_id']) ? $data['CmpCallCardEvent_id'] : null;
			$out['CmpCallCard_Numv'] = !empty($CmpCallCard_Numv) ? $CmpCallCard_Numv : null;
			$out['CmpCallCard_Ngod'] = !empty($CmpCallCard_Ngod) ? $CmpCallCard_Ngod : null;
			$out['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDT'];
            $out['CmpCallCardStatusType_id'] = !empty($data['CmpCallCardStatusType_id']) ? $data['CmpCallCardStatusType_id'] : null;
            $this->db->trans_commit();
			return $out;

		} else {
			if($dolog)$this->textlog->add('error proc '.$procedure);
			$this->db->trans_rollback();
			return false;
		}
	}


	/**
	* функция либо возвращает ид персон, либо создает оный при его отсутствиипри
	* при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
	*/
	private function checkUnknownPerson($data){

		if (
			(!empty($data['Person_IsUnknown']) && $data['Person_IsUnknown'] == 1) ||
			(empty($data['Person_Age']) && empty($data['Person_Birthday'])) ||
			empty($data['Person_SurName'])
		) {
			return false;
		}
		
		//при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
		// #126097 Сейчас если человек имеет признак «Неизвестный человек» (Person_IsUnknown = 2) поле «Социальный статус» на всех регионах необязательное для заполнения
		/*$socstatus_Ids = array("ufa" => 2, "buryatiya" => 10000083, "kareliya" => 51, "khak" => 32,
			"astra" => 10000053, "kaluga" => 231, "penza" => 224, "perm" => 2, "pskov" => 25,
			"saratov" => 10000035, "ekb" => 10000072, "msk" => 60, "krym" => 262, "kz" => 91, "by" => 201);*/

		
		if ( empty($data[ 'Person_id' ]) ){
			$this->load->model( 'Person_model', 'Person_model' );

			$Person_BirthDay = null;
			if ($data['Person_Age'] == 0 && !empty($data['Person_Birthday'])) {
				$Person_BirthDay = $data['Person_Birthday'];
			} else {
				$Person_BirthDay = '01.01.' . (date("Y") - $data['Person_Age']);
			}
			
			/*$params = array(
				'Server_id' => $data['Server_id'],
				'Person_SurName' => $data['Person_SurName'],
				'Person_FirName' => $data['Person_FirName'],
				'Person_SecName' => $data['Person_SecName'],
				'Person_BirthDay'=> $Person_BirthDay,
				'Person_IsUnknown' => 2,
				'PersonSex_id' => $data['Sex_id'],
				//'SocStatus_id' => $socstatus_Ids[getRegionNick()],
				'session' => $data['session'],
				'mode' => 'add',
				'pmUser_id' =>  $data['pmUser_id'],
				'Person_id' => null,
				'Polis_begDate' => null
			);
			
			$query = "
				declare
					@Pers_id bigint = NULL,
					@Person_Guid varchar(1000) = NULL,
					@ErrCode int,
					@ErrMessage varchar(4000);
								
					exec p_PersonAll_ins
					@Person_id = @Pers_id OUTPUT,
					@Person_Guid = @Person_Guid OUTPUT,
					@Server_id = :Server_id,
					@Person_Comment = NULL,		
					@Person_IsInErz = NULL,
					@PersonSurName_SurName = :Person_SurName,
					@PersonFirName_FirName = :Person_FirName,
					@PersonSecName_SecName = :Person_SecName,
					@PersonBirthDay_BirthDay = :Person_BirthDay,
					@Sex_id = :PersonSex_id,
					@SocStatus_id = :SocStatus_id,
					@Person_IsUnknown = 2,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
					
					select @Pers_id as Person_id, @Person_Guid as Person_Guid, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					";
					
			$result = $this->db->query( $query, $params );
			$result = $result->result( 'array' );*/
			
			$result = $this->Person_model->savePersonEditWindow(array(
				'Server_id' => $data['Server_id'],
				'NationalityStatus_IsTwoNation' => false,
				'Polis_CanAdded' => 0,
				'Person_SurName' => $data['Person_SurName'],
				'Person_FirName' => $data['Person_FirName'],
				'Person_SecName' => $data['Person_SecName'],
				'Person_BirthDay'=> $Person_BirthDay,
				'Person_IsUnknown' => 2,
				'PersonSex_id' => $data['Sex_id'],
				//'SocStatus_id' => $socstatus_Ids[getRegionNick()],
				'SocStatus_id' => null,
				'session' => $data['session'],
				'mode' => 'add',
				'pmUser_id' =>  $data['pmUser_id'],
				'Person_id' => null,
				'Polis_begDate' => null
			));

			if (!empty($result[0]['Person_id'])) {
				return $result[0]['Person_id'];
			}
		}
		else{
			return $data[ 'Person_id' ];
		}
		
		return true;	//Неизвестный, но не удалось сохранить человека
	}

	/**
	* Проверка и устнановка статуса карте при ее сохранении
	*/
	private function checkCallStatusOnSave($data, $checkOptions = true){

		$statusResult = false;
        $CmpReason_Code = false;
        $headDocObserveFlag = false;
        $OperDepartamentOptions = false;

        if ($checkOptions){
            //получаем настройки оперативного отдела
            $OperDepartamentOptions = $this->getOperDepartamentOptions($data);

            $headDocObserveFlag = $this->getHeadDocObservFlag($data);


            //проверка - повод является наблюдением св или нет
            $CmpReason_Code = $this->getFirstResultFromQuery("
            SELECT CmpReason_Code as \"CmpReason_Code\" 
            FROM v_CmpReason 

            WHERE CmpReason_id = :CmpReason_id
            limit 1
        ", array('CmpReason_id' => $data['CmpReason_id']), true);
        }


        //если установлен флаг наблюдения старшим врачом и по правилам проходим, то статус карты 18 (к СВ)
        //если повод - решение св тоже
        if ( $checkOptions && (
            ((!empty($data['CmpCallType_Code']) && $data['CmpCallType_Code'] != 6 && $data['CmpCallType_Code'] != 15  && $data['CmpCallType_Code'] != 16)
                && $CmpReason_Code !== false && in_array($CmpReason_Code, array('02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999')))
            || (
                isset($headDocObserveFlag[0]) &&
                $headDocObserveFlag[0]["CmpUrgencyAndProfileStandart_HeadDoctorObserv"] == 2 &&
                $headDocObserveFlag[0]["LpuBuilding_IsCallReason"] == 2
            ))
        ) {
            $data['CmpCallCardStatusType_id'] = 18; // "Решение страшего врача"
        }
        else {
            // Если Тип вызова «Консультативное», «Консультативный», «Справка», «Абонент отключился»,
            //то автоматически вызову присваивается статус «Закрыто»
            if (!empty($data['CmpCallType_Code']) && in_array($data['CmpCallType_Code'], array(6,15,16,17))){
                $data['CmpCallCardStatusType_id'] = 6;
            }

            //ДУБЛЬ
            if (!empty($data['CmpCallType_Code']) && $data['CmpCallType_Code'] == 14)
            {
                //и если в настройках опер отдела есть соотв. флаг
                //то автоматически вызову присваивается статус «Решение старшего врача».
                if($checkOptions && isset($OperDepartamentOptions["LpuBuilding_IsCallDouble"]) && $OperDepartamentOptions["LpuBuilding_IsCallDouble"] == "true" )
                {
                    $data['CmpCallCardStatusType_id'] = 18; // "Решение страшего врача"
                }
                else{
                    //Если дублирующие вызовы НЕ требуют решения старшего врача, то

                    //Статус первичного вызова НЕ меняется;

                    //Возьмем информацию о первичном
                    $firstCallInfoQuery =
                        "select
                                    ET.EmergencyTeam_id as \"EmergencyTeam_id\",
                                    ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\"
                                from v_CmpCallCard CCC
                                LEFT JOIN v_EmergencyTeam ET  ON CCC.EmergencyTeam_id = ET.EmergencyTeam_id

                                LEFT JOIN v_EmergencyTeamStatus  ETS  ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )

                                where CCC.CmpCallCard_id = :CmpCallCard_rid
                                limit 1
                            ";

                    $firstCallInfo = $this->db->query($firstCallInfoQuery, array('CmpCallCard_rid' => $data['CmpCallCard_rid']))->row_array();

                    //Если дублирующий вызов имеет признак «Ухудшение состояния»
                    if((!empty($data['CmpCallCard_IsDeterior']) && (int)$data['CmpCallCard_IsDeterior'] == 2) && ($firstCallInfo['EmergencyTeamStatus_Code'] != 2)){
                        // i.	Меняется повод первичного вызова – на значение повода дублирующего вызова;
                        // ii.	Пересчитывается срочность первичного вызова с учетом нового повода;

                        if( isset($data["CmpCallCard_rid"]) && $data["CmpCallCard_rid"] > 0 ) {
                            $queryParams = array(
                                'CmpCallCard_id' => $data['CmpCallCard_rid'],
                                'CmpReason_id' => $data['CmpReason_id'],
                                'CmpCallPlaceType_id' => $data['CmpCallPlaceType_id'],
                                'CmpCallCard_IsExtra' => $data['CmpCallCard_IsExtra'],
                                'CmpCallCard_IsPassSSMP' => $data['CmpCallCard_IsPassSSMP'],
                                'Lpu_smpid' => $data['Lpu_smpid'],
                                'LpuBuilding_id' => $data['LpuBuilding_id'],
                                'pmUser_id' => $data["pmUser_id"]
                            );
                            $this->updateReasonAndUrgencyInCmpCallCard($queryParams);

                        }
                    }

                        //Статус дублирующего вызова определяется как «Дубль» (и перестает отображаться в АРМ ДП).

                    $data['CmpCallCardStatusType_id'] = 16;
                }
            }

            //ДЛЯ СПЕЦ БРИГАДЫ СМП
            if (!empty($data['CmpCallType_Code']) && $data['CmpCallType_Code'] == 9)
            {
                //и если в настройках опер отдела есть соотв. флаг
                //то автоматически вызову присваивается статус «Решение старшего врача».
                if($checkOptions && isset($OperDepartamentOptions["LpuBuilding_IsCallSpecTeam"]) && $OperDepartamentOptions["LpuBuilding_IsCallSpecTeam"] == "true" )
                {
                    $data['CmpCallCardStatusType_id'] = 18; // "Решение страшего врача"
                }
                else{
                    //Если дублирующие вызовы НЕ требуют решения старшего врача, то

                    //Статус первичного вызова НЕ меняется;
                    //Статус вызова на спец. бригаду СМП определяется как «Передано».

                    $data['CmpCallCardStatusType_id'] = 1;
                }
            }

            //Попутный
            if (!empty($data['CmpCallType_Code']) && $data['CmpCallType_Code'] == 4){

                $prms = array(
                    'CmpCallCard_id' => $data['CmpCallCard_id'],
                    'CmpCallCard_Dspp' => $data['pmUser_id'],
                    'pmUser_id' => $data['pmUser_id']
                );
                $this->changeCmpCallCardCommonParams($prms);

                if(!empty($data['EmergencyTeam_id'])){
                    return $this->setEmergencyTeam(array(
                        'EmergencyTeam_id' => $data['EmergencyTeam_id'],
                        'CmpCallCard_id' => $data['CmpCallCard_id'],
                        'pmUser_id' => $data['pmUser_id'],
                        'CmpCallType_Code' => $data['CmpCallType_Code']
                    ));
                }else{
                    $data['CmpCallCardStatusType_id'] = 2;
                }


            }

			//Подстанция передачи вызова
			if(!empty($data["LpuBuilding_id"])){
				$LpuBuildingData = $this->LpuStructure->getLpuBuildingData(array("LpuBuilding_id"=>$data["LpuBuilding_id"]));
			}


            //ОТМЕНА ВЫЗОВА
            if (!empty($data['CmpCallType_Code']) && $data['CmpCallType_Code'] == 17)
            {

				//или если Тип вызова «Отмена вызова» и в настройках оперативного отдела
				//отменяющие вызовы являются вызовами, требующие решения диспетчера удаленной подстанции
				if($checkOptions && isset($LpuBuildingData) && $LpuBuildingData[0]['SmpUnitType_Code'] == 5
					&& isset($OperDepartamentOptions["SmpUnitParam_IsCancldDisp"]) && $OperDepartamentOptions["SmpUnitParam_IsCancldDisp"] == "true" )
				{
					$data['CmpCallCardStatusType_id'] = 22; // "Решение диспетчера подстанции"
				}
           		    //Если Тип вызова «Отмена вызова» и в настройках оперативного отдела
                //отменяющие вызовы являются вызовами, требующими решения старшего врача
				elseif($checkOptions && isset($OperDepartamentOptions["LpuBuilding_IsCallCancel"]) && $OperDepartamentOptions["LpuBuilding_IsCallCancel"] == "true" )
                {
                    $data['CmpCallCardStatusType_id'] = 18; // "Решение страшего врача"
                }
                //или если Тип вызова «Отмена вызова» и в настройках оперативного отдела
                //  отменяющие вызовы являются вызовами, требующими решения диспетчера отправляющей части
                elseif($checkOptions && isset($OperDepartamentOptions["SmpUnitParam_IsCancldCall"]) && $OperDepartamentOptions["SmpUnitParam_IsCancldCall"] == "true" )
                {
                    $data['CmpCallCardStatusType_id'] = 21; // "Решение диспетчера отправляющей части"
                }
                else
                {
                    //Если отменяющие вызовы НЕ требуют решения старшего врача, то автоматически производятся следующие действия
                    if( isset($data["CmpCallCard_rid"]) && $data["CmpCallCard_rid"] > 0 ){

                        //собираем данные о бригаде на вызове
                        //в том числе и о кол-ве вызовов на бригаде
                        $firstCallInfoQuery =
                            "select
                                CCC.CmpCallCard_id as \"CmpCallCard_id\",
                                CCC.CmpReason_id as \"CmpReason_id\",
                                CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
                                callsOnTeam.countCalls as \"countcallsOnTeam\",
                                CASE WHEN (COALESCE(CCC.EmergencyTeam_id,0) !=0 AND COALESCE(ETS.EmergencyTeamStatus_Code,0) != 36) THEN 'true' ELSE 'false' END as \"waitingForAccept\"

                                from v_CmpCallCard CCC
                                LEFT JOIN v_EmergencyTeam ET  ON CCC.EmergencyTeam_id = ET.EmergencyTeam_id

                                LEFT JOIN v_EmergencyTeamStatus  ETS  ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )

                                LEFT JOIN LATERAL (

                                    select count(1) as countCalls
                                    from v_CmpCallCard c 

                                    where c.EmergencyTeam_id = ET.EmergencyTeam_id
                                        AND c.CmpCallCardStatusType_id = 2
                                ) callsOnTeam ON TRUE
                                where CCC.CmpCallCard_id = :CmpCallCard_rid
                                limit 1
                            ";

                        $firstCallInfo = $this->db->query($firstCallInfoQuery, array('CmpCallCard_rid' => $data['CmpCallCard_rid']))->row_array();

                        if( isset($firstCallInfo['waitingForAccept']) && ($firstCallInfo['waitingForAccept'] == 'true') ){

                            //Если на первичный (отменяемый) вызов выехала бригада (то есть бригада на вызов назначена и находится в любом статусе, кроме «Ожидание принятия»)

                            //@todo	В мобильном АРМ Старшего бригады, назначенной на данный вызов, выводится сообщение «Вызов № <Номер вызова>,
                            //<ФИО пациента> отменен! Хотите заполнить Карту вызова сейчас? Да / Нет»

                            $this->load->model('CmpCallCard_model', 'CmpCallCard_model');

                            $this->CmpCallCard_model->setResult(array(
                                "CmpPPDResult_id" => 0,
                                "CmpPPDResult_Code" => 8, //отказ от вызова
                                "CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                "pmUser_id" => $data["pmUser_id"]
                            ));

                            //Статус первичного вызова меняется на «Обслужено» (и далее подлежит оформлению Карты вызова с признаком «Безрезультатный вызов»).
                            $this->setStatusCmpCallCard(array(
                                "CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                "CmpCallCardStatusType_id" => 4,
                                "CmpCallCardStatus_Comment" => '',
                                "CmpReason_id" => $firstCallInfo['CmpReason_id'],
                                "pmUser_id" => $data["pmUser_id"]
                            ));

                            //Статус отменяющего вызова меняется на «Закрыто»
                            $data["CmpCallCardStatusType_id"] = 6;

                            //Статус бригады меняется на «Конец обслуживания» и далее на «Свободна»;
                            if(
                                isset($firstCallInfo["EmergencyTeam_id"])
                                && $firstCallInfo["EmergencyTeam_id"]> 0
                            ){

                                $this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');

                                //на «Конец обслуживания»
                                $etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode( 4 );
                                $this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
                                    "CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                    "EmergencyTeam_id" => $firstCallInfo["EmergencyTeam_id"],
                                    "EmergencyTeamStatus_id" => $etStatusId,
                                    "pmUser_id" => $data["pmUser_id"]
                                ));

                                //Изменится в setEmergencyTeamStatus при статусе «Конец обслуживания»
                                //на «Свободна»
                                /*$etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode( 13 );
                                $this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
                                    //"CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                    "EmergencyTeam_id" => $firstCallInfo["EmergencyTeam_id"],
                                    "EmergencyTeamStatus_id" => $etStatusId,
                                    "pmUser_id" => $data["pmUser_id"]
                                ));
                                */

                                //@todo	В мобильном АРМ Старшего бригады первичный (отменяемый) вызов перестает отображаться.

                            }

                        }
                        else{
                            //Если на первичный (отменяемый) вызов НЕ выехала бригада
                            //(то есть бригада на вызов не назначена или назначена, но находится в статусе «Ожидание принятия»)

                            $this->load->model('CmpCallCard_model', 'CmpCallCard_model');

                            $this->CmpCallCard_model->setResult(array(
                                "CmpPPDResult_id" => 0,
                                "CmpPPDResult_Code" => 8, //отказ от вызова
                                "CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                "pmUser_id" => $data["pmUser_id"]
                            ));

                            //Статус первичного (отменяемого) вызова меняется на «Отказ»
                            $this->setStatusCmpCallCard(array(
                                "CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                "CmpCallCardStatusType_id" => 5,
                                "CmpCallCardStatus_Comment" => '',
                                "CmpReason_id" => $firstCallInfo['CmpReason_id'],
                                "pmUser_id" => $data["pmUser_id"]
                            ));

                            //Статус отменяющего вызова меняется на «Закрыто»
                            $data["CmpCallCardStatusType_id"] = 6;

                            //Если бригада на вызов назначена, то Статус бригады меняется на «Конец обслуживания» и далее на «Свободна»;
                            if(
                                isset($firstCallInfo["EmergencyTeam_id"])
                                && $firstCallInfo["EmergencyTeam_id"]> 0
                            ){

                                $this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');

                                //на «Конец обслуживания»
                                $etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode( 4 );
                                $this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
                                    //"CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                    "EmergencyTeam_id" => $firstCallInfo["EmergencyTeam_id"],
                                    "EmergencyTeamStatus_id" => $etStatusId,
                                    "pmUser_id" => $data["pmUser_id"]
                                ));

                                //Изменится в setEmergencyTeamStatus при статусе «Конец обслуживания»
                                //на «Свободна»
                                /*
                                $etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode( 13 );
                                $this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
                                    //"CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
                                    "EmergencyTeam_id" => $firstCallInfo["EmergencyTeam_id"],
                                    "EmergencyTeamStatus_id" => $etStatusId,
                                    "pmUser_id" => $data["pmUser_id"]
                                ));
                                */
                                //@todo	В мобильном АРМ Старшего бригады первичный (отменяемый) вызов перестает отображаться.

                            }
                        }

                        //Если с первичным (отменяемым) вызовом связаны (по полю CmpCallCard_rid) вызовы с типом «Попутный» в статусе «Принято», то

                        $callssql = "
                            SELECT Person_id as \"Person_id\",
                                    CmpCallCard_id as \"CmpCallCard_id\",
                                    Person_SurName as \"Person_SurName\",
                                    CmpCallCard_Numv as \"CmpCallCard_Numv\",
                                    CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                                    CmpCallCard_Prty as \"CmpCallCard_Prty\",
                                    CmpCallCard_Sect as \"CmpCallCard_Sect\",
                                    CmpArea_id as \"CmpArea_id\",
                                    CmpCallCard_City as \"CmpCallCard_City\",
                                    CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
                                    CmpCallCard_Dom as \"CmpCallCard_Dom\",
                                    CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                                    CmpLpu_aid as \"CmpLpu_aid\",
                                    CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                                    cmpCallCard_Medc as \"cmpCallCard_Medc\",
                                    CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
                                    CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
                                    CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
                                    CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
                                    CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
                                    CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
                                    CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
                                    CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
                                    c.pmUser_insID as \"pmUser_insID\",
                                    c.pmUser_updID as \"pmUser_updID\",
                                    CmpCallCard_insDT as \"CmpCallCard_insDT\",
                                    CmpCallCard_updDT as \"CmpCallCard_updDT\",
                                    KLRgn_id as \"KLRgn_id\",
                                    KLSubRgn_id as \"KLSubRgn_id\",
                                    CmpCallCard_Podz as \"CmpCallCard_Podz\",
                                    CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                                    CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                                    CmpCallCard_Telf as \"CmpCallCard_Telf\",
                                    CmpPlace_id as \"CmpPlace_id\",
                                    CmpCallCard_Comm as \"CmpCallCard_Comm\",
                                    CmpReason_id as \"CmpReason_id\",
                                    CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
                                    CmpCallCard_Condition as \"CmpCallCard_Condition\",
                                    Lpu_cid as \"Lpu_cid\",
                                    CmpCallCard_Tend as \"CmpCallCard_Tend\",
                                    CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
                                    CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                                    CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
                                    CmpRejectionReason_id as \"CmpRejectionReason_id\",
                                    CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
                                    CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
                                    CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
                                    CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
                                    CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
                                    CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                                    CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
                                    CmpCallCard_GUID as \"CmpCallCard_GUID\",
                                    CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
                                    KLCity_id as \"KLCity_id\",
                                    KLTown_id as \"KLTown_id\",
                                    KLStreet_id as \"KLStreet_id\",
                                    Lpu_ppdid as \"Lpu_ppdid\",
                                    CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
                                    CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
                                    CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                                    CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
                                    CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
                                    CmpPPDResult_id as \"CmpPPDResult_id\",
                                    EmergencyTeam_id as \"EmergencyTeam_id\",
                                    CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
                                    Lpu_id as \"Lpu_id\",
                                    CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
                                    MedPersonal_id as \"MedPersonal_id\",
                                    ResultDeseaseType_id as \"ResultDeseaseType_id\",
                                    CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
                                    UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                                    CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
                                    CmpCallCard_Korp as \"CmpCallCard_Korp\",
                                    CmpCallCard_Room as \"CmpCallCard_Room\",
                                    CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
                                    UslugaComplex_id as \"UslugaComplex_id\",
                                    LpuBuilding_id as \"LpuBuilding_id\",
                                    CmpCallerType_id as \"CmpCallerType_id\",
                                    CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                                    CmpCallCard_rid as \"CmpCallCard_rid\",
                                    CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                                    CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
                                    CmpSecondReason_id as \"CmpSecondReason_id\",
                                    CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
                                    CmpCallReasonType_id as \"CmpCallReasonType_id\",
                                    CmpReasonNew_id as \"CmpReasonNew_id\",
                                    CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
                                    CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
                                    CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
                                    CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
                                    MedStaffFact_id as \"MedStaffFact_id\",
                                    RankinScale_id as \"RankinScale_id\",
                                    RankinScale_sid as \"RankinScale_sid\",
                                    LeaveType_id as \"LeaveType_id\",
                                    CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
                                    LpuSection_id as \"LpuSection_id\",
                                    CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
                                    CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
                                    EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
                                    CmpCallCard_storDT as \"CmpCallCard_storDT\",
                                    CmpCallCard_defCom as \"CmpCallCard_defCom\",
                                    MedService_id as \"MedService_id\",
                                    CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
                                    CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                                    Diag_sopid as \"Diag_sopid\",
                                    CmpLeaveType_id as \"CmpLeaveType_id\",
                                    CmpLeaveTask_id as \"CmpLeaveTask_id\",
                                    CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
                                    CmpTransportType_id as \"CmpTransportType_id\",
                                    CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
                                    CmpCallCardResult_id as \"CmpCallCardResult_id\",
                                    Person_IsUnknown as \"Person_IsUnknown\",
                                    CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
                                    Lpu_smpid as \"Lpu_smpid\",
                                    Lpu_hid as \"Lpu_hid\",
                                    UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
                                    PayType_id as \"PayType_id\",
                                    CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
                                    CmpCallCard_sid as \"CmpCallCard_sid\",
                                    CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
                                    CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
                                    CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
                                    CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
                                    CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
                                    CmpCallSignType_id as \"CmpCallSignType_id\",
                                    Lpu_CodeSMO as \"Lpu_CodeSMO\",
                                    Registry_sid as \"Registry_sid\",
                                    Diag_gid as \"Diag_gid\",
                                    MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
                                    CmpCommonState_id as \"CmpCommonState_id\",
                                    Person_FirName as \"Person_FirName\",
                                    Person_SecName as \"Person_SecName\",
                                    Person_Age as \"Person_Age\",
                                    Person_BirthDay as \"Person_BirthDay\",
                                    Person_PolisSer as \"Person_PolisSer\",
                                    Person_PolisNum as \"Person_PolisNum\",
                                    Sex_id as \"Sex_id\",
                                    CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                                    c.CmpCallType_id as \"CmpCallType_id\",
                                    CmpProfile_cid as \"CmpProfile_cid\",
                                    CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
                                    CmpCallCard_Stan as \"CmpCallCard_Stan\",
                                    CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
                                    CmpCallCard_Line as \"CmpCallCard_Line\",
                                    CmpResult_id as \"CmpResult_id\",
                                    CmpArea_gid as \"CmpArea_gid\",
                                    CmpLpu_id as \"CmpLpu_id\",
                                    CmpDiag_oid as \"CmpDiag_oid\",
                                    CmpDiag_aid as \"CmpDiag_aid\",
                                    CmpTrauma_id as \"CmpTrauma_id\",
                                    CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
                                    Diag_uid as \"Diag_uid\",
                                    CmpCallCard_Numb as \"CmpCallCard_Numb\",
                                    CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
                                    CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
                                    CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
                                    CmpProfile_bid as \"CmpProfile_bid\",
                                    CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
                                    CmpCallCard_RCod as \"CmpCallCard_RCod\",
                                    CmpCallCard_TabN as \"CmpCallCard_TabN\",
                                    CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
                                    CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
                                    CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
                                    CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
                                    Diag_sid as \"Diag_sid\",
                                    CmpTalon_id as \"CmpTalon_id\",
                                    CmpCallCard_Expo as \"CmpCallCard_Expo\",
                                    CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
                                    CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
                                    CmpCallCard_D201 as \"CmpCallCard_D201\",
                                    CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
                                    CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
                                    CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
                                    CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
                                    CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
                                    CmpCallCard_Tper as \"CmpCallCard_Tper\",
                                    CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
                                    CmpCallCard_Przd as \"CmpCallCard_Przd\",
                                    CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
                                    CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
                                    CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
                                    CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
                                    CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
                                    CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
                                    CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
                                    CmpArea_pid as \"CmpArea_pid\",
                                    CmpCallCard_PCity as \"CmpCallCard_PCity\",
                                    CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
                                    CmpCallCard_PDom as \"CmpCallCard_PDom\",
                                    CmpCallCard_PKvar as \"CmpCallCard_PKvar\"
                             FROM v_CmpCallCard C
                                  left join v_CmpCallType CT on CT.CmpCallType_id = C.CmpCallType_id
                             WHERE CmpCallCard_rid =:CmpCallCard_rid and
                                   CT.CmpCallType_Code::int = 4 and
                                   C.CmpCallCardStatusType_id = 2
                             ORDER BY C.CmpCallCard_prmDT
                            ";
                        $callsres = $this->db->query($callssql, array('CmpCallCard_rid' => $data['CmpCallCard_rid']));

                        $calls = $callsres->result( 'array' );

                        if(is_array($calls) && count($calls) > 0){

                            //1)	Тип первого принятого «Попутного» вызова, меняется на «Первичный»
                            $firstCall = $calls[0]['CmpCallCard_id'];

                            $typeCardQuery = "
                                SELECT CmpCallType_id as \"CmpCallType_id\",
                                       CmpCallType_Code as \"CmpCallType_Code\",
                                       CmpCallType_Name as \"CmpCallType_Name\",
                                       pmUser_insID as \"pmUser_insID\",
                                       pmUser_updID as \"pmUser_updID\",
                                       CmpCallType_insDT as \"CmpCallType_insDT\",
                                       CmpCallType_updDT as \"CmpCallType_updDT\",
                                       Region_id as \"Region_id\",
                                       CmpCallType_begDate as \"CmpCallType_begDate\",
                                       CmpCallType_endDate as \"CmpCallType_endDate\"
                                FROM v_CmpCallType
                                WHERE CmpCallType_Code =:CmpCallType_Code
                                LIMIT 1";

                            $typeCard = $this->db->query($typeCardQuery, array('CmpCallType_Code' => 1))->row_array(); //Первичный

                            $prms = array(
                                'CmpCallCard_id' => $firstCall,
                                'CmpCallType_id' => $typeCard['CmpCallType_id'],
                                'CmpCallCard_rid' => null,
                                'pmUser_id' => $data['pmUser_id']
                            );
                            $this->swUpdate('CmpCallCard', $prms, false);

                            //2)	Оставшиеся «Попутные» вызовы связываются с ним по идентификатору первичного вызова (CmpCallCard_rid)
                            foreach($calls as $call){

                                if($call['CmpCallCard_id'] != $firstCall){
                                    $prms = array(
                                        'CmpCallCard_id' => $call['CmpCallCard_id'],
                                        'CmpCallCard_rid' => $firstCall,
                                        'pmUser_id' => $data['pmUser_id']
                                    );
                                    $this->swUpdate('CmpCallCard', $prms, false);
                                }

                            }


                        }
                    }
                }
            }

            //Первичный, Повторный
            //Контроль передачи вызова на подчиненную подстанцию
            if ( !in_array($_SESSION['CurArmType'], array('dispcallnmp', 'dispdirnmp', 'nmpgranddoc', 'dispnmp')) &&
                !empty($data['CmpCallType_Code']) && in_array($data['CmpCallType_Code'], array(1,2))){
                $this->load->model('LpuStructure_model', 'LpuStructure');
                //Текущая подстанци
                $lpuBuildingOptions = $this->getLpuBuildingOptions(array('session' => $_SESSION));

                //В поле «Подразделение СМП» указано значение подразделения с типом «Подчиненная подстанция»
                //Вызов создан Диспетчером по приему вызовов Подчиненной подстанции
                //В настройках подразделения на вкладке «Разное» установлен флаг «Вызов утверждается и передается оперативным отделом»
                if($checkOptions && isset($LpuBuildingData[0]) && isset($LpuBuildingData[0]['SmpUnitType_Code']) && ($LpuBuildingData[0]['SmpUnitType_Code'] == 2)
                    && isset($lpuBuildingOptions[0]) && $lpuBuildingOptions[0]['SmpUnitType_Code'] == 2
                    && isset($OperDepartamentOptions["SmpUnitParam_IsCallApproveSend"]) && $OperDepartamentOptions["SmpUnitParam_IsCallApproveSend"] == "true")
                {
                    $data['CmpCallCardStatusType_id'] = 21; // "Решение диспетчера отправляющей части"
                }
            }

            if(!empty($data['CmpCallCard_storDT'])){
                $data['CmpCallCardStatusType_id'] = 19; //Отложенно
            }

            if(empty( $data[ 'CmpCallCardStatusType_id' ]) || $data[ 'CmpCallCardStatusType_id' ] == '20'){
                $data['CmpCallCardStatusType_id'] = 1;
            }

            //гениально! оставлю здесь, чтоб время от времени поржать
            // if(!empty( $data[ 'CmpCallCardStatusType_id' ])){
                // $data['CmpCallCardStatusType_id'] = $data[ 'CmpCallCardStatusType_id' ];
            // }
        }

        if($data[ 'ARMType' ] != 'smpdispatchcall')
            $data['CmpCallCardStatus_Comment'] = '';

        $statusResult = $this->setStatusCmpCallCard($data);

        //событие активный звонок
        if(!empty($data['CmpCallCard_IsActiveCall'])){
            $eventParams = array(
                "CmpCallCard_id" =>  $data['CmpCallCard_id'],
                "CmpCallCardEventType_Code" => 31,
                "CmpCallCardEvent_Comment" => 'Совершен активный звонок',
                "pmUser_id" => $data["pmUser_id"]
            );

            $this->load->model('CmpCallCard_model', 'CmpCallCard_model');
        }

		return $statusResult;
	}

	/**
	* Получение флага наблюдение старшим врачом
	*/
	private function getHeadDocObservFlag($data){

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		//получаем ид подстанции опер отдела
		$operDpt = $this->getOperDepartament($data);

		if ( isset( $operDpt["LpuBuilding_pid"] ) ){
			$queryParams["LpuBuilding_pid"] = $operDpt["LpuBuilding_pid"];
		}
		else{
			return false;
		}

		//для нмп армов берем настройки из оо
		if ( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'nmpgranddoc', 'dispnmp')) ){
			$data['Lpu_id'] = $operDpt['Lpu_id'];
		}

		//на входе нужно CmpReason_id, Person_Age, CmpCallPlaceType_id, Lpu_id
		$query = "
			SELECT
				CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv as \"CmpUrgencyAndProfileStandart_HeadDoctorObserv\",
				COALESCE(operDPT.LpuBuilding_IsCallReason, 1) as \"LpuBuilding_IsCallReason\"
			FROM
				v_CmpUrgencyAndProfileStandart CUPS 
				left join v_CmpUrgencyAndProfileStandartRefPlace CUPSRP  on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRP.CmpUrgencyAndProfileStandart_id
				left join v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP  on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRSP.CmpUrgencyAndProfileStandart_id
				left join v_EmergencyTeamSpec ETS  on ETS.EmergencyTeamSpec_id = CUPSRSP.EmergencyTeamSpec_id
				left join v_CmpCallCardAcceptor CCCA  on CCCA.CmpCallCardAcceptor_id = CUPS.CmpCallCardAcceptor_id
				LEFT JOIN LATERAL (
					select 
					    lpubuilding_iscallreason
                    from v_LpuBuilding lb 
                    where lb.LpuBuilding_id = :LpuBuilding_pid 
                    limit 1
				) as operDPT ON TRUE
			WHERE
				CUPS.CmpReason_id = :CmpReason_id
				AND (COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) > :Person_Age)
				AND CUPSRP.CmpCallPlaceType_id = :CmpCallPlaceType_id
				AND CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority  = 1
				AND COALESCE(CUPS.Lpu_id,0) in (0,:Lpu_id)
			ORDER BY
				COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150),
				COALESCE(CUPS.Lpu_id,0) DESC
            LIMIT 1
		";
		$params = array(
			'CmpReason_id' => $data['CmpReason_id'],
			'Person_Age' => $data['Person_Age'],
			'CmpCallPlaceType_id' => $data['CmpCallPlaceType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuilding_pid' => $operDpt["LpuBuilding_pid"]
		);

		$result = $this->db->query( $query, $params );

		return $result->result( 'array' );
	}


	/**
	 * Связывает карту и ноду дерева принятия решения
	 */
	public function appendCardToAmbulanceDT($data){

		$query = "
            select
                Res as \"AmbulanceDecigionTreeLink_id\",
                ErrCode as \"Error_Code\",
                ErrMessage as \"Error_Msg\"
			from p_AmbulanceDecigionTreeLink_ins (
                AmbulanceDecigionTree_id := :AmbulanceDecigionTree_id,
				CmpCallCard_id := :CmpCallCard_id,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query( $query, $data );

		return $result->result( 'array' );
		//AmbulanceDecigionTree_id
	}


		/**
	* Получение флага наблюдение старшим врачом
	*/
	public function getOperDepartamentOptions($data){

		$od = $this->getOperDepartament($data);

		if ( isset( $od["LpuBuilding_pid"] ) ){
			$operDpt = $od["LpuBuilding_pid"];
			
			//Если в настройках оперативного отдела вызов являются вызовами, требующими решения старшего врача...
			$this->load->model('LpuStructure_model', 'LpuStructure');
			$LpuBuildingData = $this->LpuStructure->getLpuBuildingData(array("LpuBuilding_id"=>$operDpt));
			if(isset($LpuBuildingData[0])){
				return $LpuBuildingData[0];
			}
			else{
				return false;
			}
		}
		else return false;
	}
	
	/**
	* Получение настроек подстанции
	*/
	public function getLpuBuildingOptions($data){
		$lpuBuilding = $this->getLpuBuildingBySessionData($data);
		if(!empty($lpuBuilding[0]["LpuBuilding_id"])){
			//Если в настройках оперативного отдела вызов являются вызовами, требующими решения старшего врача...
			$this->load->model('LpuStructure_model', 'LpuStructure');
			$LpuBuildingData = $this->LpuStructure->getSmpUnitData(array("LpuBuilding_id"=>$lpuBuilding[0]["LpuBuilding_id"]));
			$LpuBuildingAddiction = $this->LpuStructure->getLpuBuildingData(array("LpuBuilding_id"=>$lpuBuilding[0]["LpuBuilding_id"]));

			$operDepartamentOptions = $this->getOperDepartamentOptions($data);

			$Data = $LpuBuildingData[0]+$LpuBuildingAddiction[0];
			$Data["SmpUnitParam_IsCall112"] = $operDepartamentOptions["SmpUnitParam_IsCall112"];
			$Data["SmpUnitParam_IsShowAllCallsToDP"] = $operDepartamentOptions["SmpUnitParam_IsShowAllCallsToDP"];
			$Data["Lpu_eid"] = $operDepartamentOptions["Lpu_eid"];
			$Data["LpuBuilding_eid"] = $operDepartamentOptions["LpuBuilding_eid"];
			$Data["SmpUnitParam_IsSaveTreePath"] = $operDepartamentOptions["SmpUnitParam_IsSaveTreePath"];
		}
		if(isset($Data)){
			return array($Data);
		}
		else{
			return false;
		}
	}

	/**
	 * Метод передачи вызова в другое МО
	 */
	public function copyCmpCallCardToLpu($data) {
		if(empty($data['CmpCallCard_id']) || empty($data['Lpu_did']))
			return;
		$params = [ "CmpCallCard_id" => $data["CmpCallCard_id"] ];
		$query = "SELECT 
                      Person_id as \"Person_id\",
                      CmpCallCard_id as \"CmpCallCard_id\",
                      Person_SurName as \"Person_SurName\",
                      CmpCallCard_Numv as \"CmpCallCard_Numv\",
                      CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                      CmpCallCard_Prty as \"CmpCallCard_Prty\",
                      CmpCallCard_Sect as \"CmpCallCard_Sect\",
                      CmpArea_id as \"CmpArea_id\",
                      CmpCallCard_City as \"CmpCallCard_City\",
                      CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
                      CmpCallCard_Dom as \"CmpCallCard_Dom\",
                      CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                      CmpLpu_aid as \"CmpLpu_aid\",
                      CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                      cmpCallCard_Medc as \"cmpCallCard_Medc\",
                      CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
                      CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
                      CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
                      CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
                      CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
                      CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
                      CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
                      CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
                      pmUser_insID as \"pmUser_insID\",
                      pmUser_updID as \"pmUser_updID\",
                      CmpCallCard_insDT as \"CmpCallCard_insDT\",
                      CmpCallCard_updDT as \"CmpCallCard_updDT\",
                      KLRgn_id as \"KLRgn_id\",
                      KLSubRgn_id as \"KLSubRgn_id\",
                      CmpCallCard_Podz as \"CmpCallCard_Podz\",
                      CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                      CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                      CmpCallCard_Telf as \"CmpCallCard_Telf\",
                      CmpPlace_id as \"CmpPlace_id\",
                      CmpCallCard_Comm as \"CmpCallCard_Comm\",
                      CmpReason_id as \"CmpReason_id\",
                      CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
                      CmpCallCard_Condition as \"CmpCallCard_Condition\",
                      Lpu_cid as \"Lpu_cid\",
                      CmpCallCard_Tend as \"CmpCallCard_Tend\",
                      CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
                      CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                      CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
                      CmpRejectionReason_id as \"CmpRejectionReason_id\",
                      CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
                      CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
                      CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
                      CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
                      CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
                      CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                      CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
                      CmpCallCard_GUID as \"CmpCallCard_GUID\",
                      CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
                      KLCity_id as \"KLCity_id\",
                      KLTown_id as \"KLTown_id\",
                      KLStreet_id as \"KLStreet_id\",
                      Lpu_ppdid as \"Lpu_ppdid\",
                      CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
                      CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
                      CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                      CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
                      CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
                      CmpPPDResult_id as \"CmpPPDResult_id\",
                      EmergencyTeam_id as \"EmergencyTeam_id\",
                      CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
                      Lpu_id as \"Lpu_id\",
                      CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
                      MedPersonal_id as \"MedPersonal_id\",
                      ResultDeseaseType_id as \"ResultDeseaseType_id\",
                      CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
                      UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                      CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
                      CmpCallCard_Korp as \"CmpCallCard_Korp\",
                      CmpCallCard_Room as \"CmpCallCard_Room\",
                      CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
                      UslugaComplex_id as \"UslugaComplex_id\",
                      LpuBuilding_id as \"LpuBuilding_id\",
                      CmpCallerType_id as \"CmpCallerType_id\",
                      CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                      CmpCallCard_rid as \"CmpCallCard_rid\",
                      CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                      CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
                      CmpSecondReason_id as \"CmpSecondReason_id\",
                      CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
                      CmpCallReasonType_id as \"CmpCallReasonType_id\",
                      CmpReasonNew_id as \"CmpReasonNew_id\",
                      CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
                      CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
                      CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
                      CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
                      MedStaffFact_id as \"MedStaffFact_id\",
                      RankinScale_id as \"RankinScale_id\",
                      RankinScale_sid as \"RankinScale_sid\",
                      LeaveType_id as \"LeaveType_id\",
                      CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
                      LpuSection_id as \"LpuSection_id\",
                      CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
                      CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
                      EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
                      CmpCallCard_storDT as \"CmpCallCard_storDT\",
                      CmpCallCard_defCom as \"CmpCallCard_defCom\",
                      MedService_id as \"MedService_id\",
                      CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
                      CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                      Diag_sopid as \"Diag_sopid\",
                      CmpLeaveType_id as \"CmpLeaveType_id\",
                      CmpLeaveTask_id as \"CmpLeaveTask_id\",
                      CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
                      CmpTransportType_id as \"CmpTransportType_id\",
                      CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
                      CmpCallCardResult_id as \"CmpCallCardResult_id\",
                      Person_IsUnknown as \"Person_IsUnknown\",
                      CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
                      Lpu_smpid as \"Lpu_smpid\",
                      Lpu_hid as \"Lpu_hid\",
                      UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
                      PayType_id as \"PayType_id\",
                      CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
                      CmpCallCard_sid as \"CmpCallCard_sid\",
                      CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
                      CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
                      CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
                      CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
                      CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
                      CmpCallSignType_id as \"CmpCallSignType_id\",
                      Lpu_CodeSMO as \"Lpu_CodeSMO\",
                      Registry_sid as \"Registry_sid\",
                      Diag_gid as \"Diag_gid\",
                      MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
                      CmpCommonState_id as \"CmpCommonState_id\",
                      Person_FirName as \"Person_FirName\",
                      Person_SecName as \"Person_SecName\",
                      Person_Age as \"Person_Age\",
                      Person_BirthDay as \"Person_BirthDay\",
                      Person_PolisSer as \"Person_PolisSer\",
                      Person_PolisNum as \"Person_PolisNum\",
                      Sex_id as \"Sex_id\",
                      CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                      CmpCallType_id as \"CmpCallType_id\",
                      CmpProfile_cid as \"CmpProfile_cid\",
                      CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
                      CmpCallCard_Stan as \"CmpCallCard_Stan\",
                      CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
                      CmpCallCard_Line as \"CmpCallCard_Line\",
                      CmpResult_id as \"CmpResult_id\",
                      CmpArea_gid as \"CmpArea_gid\",
                      CmpLpu_id as \"CmpLpu_id\",
                      CmpDiag_oid as \"CmpDiag_oid\",
                      CmpDiag_aid as \"CmpDiag_aid\",
                      CmpTrauma_id as \"CmpTrauma_id\",
                      CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
                      Diag_uid as \"Diag_uid\",
                      CmpCallCard_Numb as \"CmpCallCard_Numb\",
                      CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
                      CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
                      CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
                      CmpProfile_bid as \"CmpProfile_bid\",
                      CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
                      CmpCallCard_RCod as \"CmpCallCard_RCod\",
                      CmpCallCard_TabN as \"CmpCallCard_TabN\",
                      CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
                      CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
                      CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
                      CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
                      Diag_sid as \"Diag_sid\",
                      CmpTalon_id as \"CmpTalon_id\",
                      CmpCallCard_Expo as \"CmpCallCard_Expo\",
                      CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
                      CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
                      CmpCallCard_D201 as \"CmpCallCard_D201\",
                      CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
                      CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
                      CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
                      CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
                      CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
                      CmpCallCard_Tper as \"CmpCallCard_Tper\",
                      CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
                      CmpCallCard_Przd as \"CmpCallCard_Przd\",
                      CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
                      CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
                      CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
                      CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
                      CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
                      CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
                      CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
                      CmpArea_pid as \"CmpArea_pid\",
                      CmpCallCard_PCity as \"CmpCallCard_PCity\",
                      CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
                      CmpCallCard_PDom as \"CmpCallCard_PDom\",
                      CmpCallCard_PKvar as \"CmpCallCard_PKvar\"
                  FROM 
                    v_CmpCallCard
                  WHERE 
                    CmpCallCard_id = :CmpCallCard_id 
                  LIMIT 1";

		$card = $this->getFirstRowFromQuery($query,$params);

		if(!$card) return;

		$card['Lpu_id'] = $data['Lpu_did'];
		$card['LpuBuilding_id'] = $data['LpuBuilding_did'];
		$card['CmpCallCard_rid'] = $card['CmpCallCard_id'];
		$card['CmpCallType_id'] = $this->getFirstResultFromQuery("select CmpCallType_id as \"CmpCallType_id\" from v_CmpCallType where CmpCallType_Code = 1 limit 1",[],true);

		unset(
			$data['CmpCallCard_id'],
			$data['Lpu_did'],
			$data['LpuBuilding_did'],
			$card['CmpCallCard_id'],
			$card['CmpCallCard_Numv'],
			$card['CmpCallCard_Ngod'],
			$card['CmpCallCard_updDT'],
			$card['CmpCallCard_prmDT'],
			$card['CmpCallCard_GUID'],
			$card['UnformalizedAddressDirectory_id'],
			$card['CmpCallCardStatus_id'],
			$card['Person_id']
		);
		$card['ARMType'] = 'smpheaddoctor';
		$card['pmUser_id'] = $card['pmUser_insID'];
		$data = array_merge($data, $card);
		$result = $this->saveCmpCallCard($data);
		if($result) {
			if(!empty($data['LpuDid_Nick'])) {
				$params['CmpCallCard_Comm'] = $card['CmpCallCard_Comm']." Передан в МО (".$data['LpuDid_Nick']."). ";
				$this->swUpdate('CmpCallCard',$params,false);
			}
			return $result;
		}
		return false;

	}

	/**
	 * @desc Установка статуса карты вызова
	 * @param array $data
	 * @return boolean
	 */
	public function setStatusCmpCallCard($data) {

		$checkLock = $this->checkLockCmpCallCard($data);

		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}
		
		//потому что может быть и нулем и ''
		if( empty($data['CmpCallCardStatusType_id']) ) {
			$data['CmpCallCardStatusType_id'] = null;
		}
		
		//возможность проставлять статус по коду
		if( empty($data['CmpCallCardStatusType_id']) && !empty($data['CmpCallCardStatusType_Code']) ) {
			$statusQuery = "SELECT CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                                   CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\",
                                   CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\",
                                   pmUser_insID as \"pmUser_insID\",
                                   pmUser_updID as \"pmUser_updID\",
                                   CmpCallCardStatusType_insDT as \"CmpCallCardStatusType_insDT\",
                                   CmpCallCardStatusType_updDT as \"CmpCallCardStatusType_updDT\"
                            FROM v_CmpCallCardStatusType
                            WHERE CmpCallCardStatusType_Code =:CmpCallCardStatusType_Code
                            LIMIT 1";

			$status = $this->db->query($statusQuery, $data)->row_array();
			if(!empty($status["CmpCallCardStatusType_id"])){
				$data['CmpCallCardStatusType_id'] = $status['CmpCallCardStatusType_id'];
			}
			else{
				return array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Не код или id статуса карты');
			}
		}

		//возможность проставлять тип вызова по коду
		if( empty($data['CmpCallType_id']) && !empty($data['CmpCallType_Code']) ) {
			$typeCardQuery = "SELECT 
			                       CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                                   CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\",
                                   CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\",
                                   pmUser_insID as \"pmUser_insID\",
                                   pmUser_updID as \"pmUser_updID\",
                                   CmpCallCardStatusType_insDT as \"CmpCallCardStatusType_insDT\",
                                   CmpCallCardStatusType_updDT as \"CmpCallCardStatusType_updDT\"
                            FROM v_CmpCallCardStatusType
                            WHERE CmpCallCardStatusType_Code =:CmpCallCardStatusType_Code
                            LIMIT 1";

			$typeCard = $this->db->query($typeCardQuery, $data)->row_array();
			if(!empty($typeCard["CmpCallType_id"])){
				$data['CmpCallType_id'] = $typeCard['CmpCallType_id'];
			}
		}

		if ((isset($data['CmpCallCardStatusType_id']) && $data['CmpCallCardStatusType_id'] == 5) || (isset($data['CmpCallCardStatusType_Code']) && $data['CmpCallCardStatusType_Code'] == 5) ){

			// При отказе вызова c назначенной на него бригадой
			$queryET = "
				SELECT
					CCC.EmergencyTeam_id as \"EmergencyTeam_id\",
					CCC.CmpCallCard_rid as \"CmpCallCard_rid\",
					callsOnTeam.countCalls as \"countcallsOnTeam\",
					CASE WHEN (COALESCE(CCC.EmergencyTeam_id,0) !=0 AND COALESCE(ETS.EmergencyTeamStatus_Code,0) != 36) THEN 'true' ELSE 'false' END as \"waitingForAccept\"
				FROM
					v_CmpCallCard CCC 
					LEFT JOIN v_EmergencyTeam ET  ON CCC.EmergencyTeam_id = ET.EmergencyTeam_id
					LEFT JOIN v_EmergencyTeamStatus  ETS  ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )
				LEFT JOIN LATERAL (
					select count(1) as countCalls
					from v_CmpCallCard c 
					where c.EmergencyTeam_id = CCC.EmergencyTeam_id
						AND c.CmpCallCardStatusType_id = 2
				) callsOnTeam ON TRUE
				WHERE
					CmpCallCard_id = :CmpCallCard_id
                LIMIT 1
			";
			$resultET = $this->db->query($queryET, $data)->row_array();
			if(!empty($resultET["countcallsOnTeam"])){

				// Если бригада выехала Статус первичного вызова меняется на «Обслужено» иначе "Отказ"
				$data['CmpCallCardStatusType_id'] = $resultET['waitingForAccept'] === 'true' ? 4 : 5;

				//если вызов один на бригаде
				if($resultET["countcallsOnTeam"] == 1) {
					// Статус бригады меняется на «Конец обслуживания» и далее на «Свободна»;
					$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');

					//на «Конец обслуживания»
					$etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode(4);
					$this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
						"EmergencyTeam_id" => $resultET["EmergencyTeam_id"],
						"EmergencyTeamStatus_id" => $etStatusId,
						"pmUser_id" => $data["pmUser_id"]
					));

					//на «Свободна»
					$etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode(13);
					$this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
						//"CmpCallCard_id" => $firstCallInfo["CmpCallCard_id"],
						"EmergencyTeam_id" => $resultET["EmergencyTeam_id"],
						"EmergencyTeamStatus_id" => $etStatusId,
						"pmUser_id" => $data["pmUser_id"]
					));
					//@todo	В мобильном АРМ Старшего бригады первичный (отменяемый) вызов перестает отображаться.
				}
			}
		}
		$data['CmpCallCard_IsReceivedInPPD'] = null;

		if ( $data['CmpCallCardStatusType_id'] == 3 ) {
			$data['CmpCallCard_IsReceivedInPPD'] = 1;
		}
		if (!isset($data['CmpCallCardStatus_Comment'])) {
			$data['CmpCallCardStatus_Comment']=null;
		}
		if (!isset($data['CmpMoveFromNmpReason_id'])) {
			$data['CmpMoveFromNmpReason_id']=null;
		}
		if (!isset($data['CmpReturnToSmpReason_id'])) {
			$data['CmpReturnToSmpReason_id']=null;
		}
		if (!isset($data['CmpRejectionReason_id'])) {
			$data['CmpRejectionReason_id']=null;
		}
		if(!empty($data['CmpCallCardStatus_Comment'])){
			$data['CmpCallCardEvent_Comment'] = $data['CmpCallCardStatus_Comment'];
		}
		//получаем первоначальные данные карты
		$preQuery = "SELECT 
                      Person_id as \"Person_id\",
                      CmpCallCard_id as \"CmpCallCard_id\",
                      Person_SurName as \"Person_SurName\",
                      CmpCallCard_Numv as \"CmpCallCard_Numv\",
                      CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                      CmpCallCard_Prty as \"CmpCallCard_Prty\",
                      CmpCallCard_Sect as \"CmpCallCard_Sect\",
                      CmpArea_id as \"CmpArea_id\",
                      CmpCallCard_City as \"CmpCallCard_City\",
                      CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
                      CmpCallCard_Dom as \"CmpCallCard_Dom\",
                      CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                      CmpLpu_aid as \"CmpLpu_aid\",
                      CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
                      cmpCallCard_Medc as \"cmpCallCard_Medc\",
                      CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
                      CmpCallCard_Tiz1 as \"CmpCallCard_Tiz1\",
                      CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
                      CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
                      CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
                      CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
                      CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
                      CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
                      pmUser_insID as \"pmUser_insID\",
                      pmUser_updID as \"pmUser_updID\",
                      CmpCallCard_insDT as \"CmpCallCard_insDT\",
                      CmpCallCard_updDT as \"CmpCallCard_updDT\",
                      KLRgn_id as \"KLRgn_id\",
                      KLSubRgn_id as \"KLSubRgn_id\",
                      CmpCallCard_Podz as \"CmpCallCard_Podz\",
                      CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                      CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                      CmpCallCard_Telf as \"CmpCallCard_Telf\",
                      CmpPlace_id as \"CmpPlace_id\",
                      CmpCallCard_Comm as \"CmpCallCard_Comm\",
                      CmpReason_id as \"CmpReason_id\",
                      CmpCallCard_Recomendations as \"CmpCallCard_Recomendations\",
                      CmpCallCard_Condition as \"CmpCallCard_Condition\",
                      Lpu_cid as \"Lpu_cid\",
                      CmpCallCard_Tend as \"CmpCallCard_Tend\",
                      CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\",
                      CmpCallCard_CallLng as \"CmpCallCard_CallLng\",
                      CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
                      CmpRejectionReason_id as \"CmpRejectionReason_id\",
                      CmpCallCard_HospitalizedTime as \"CmpCallCard_HospitalizedTime\",
                      CmpCallCard_saveDT as \"CmpCallCard_saveDT\",
                      CmpCallCard_PlanDT as \"CmpCallCard_PlanDT\",
                      CmpCallCard_FactDT as \"CmpCallCard_FactDT\",
                      CmpCallCardInputType_id as \"CmpCallCardInputType_id\",
                      CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
                      CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
                      CmpCallCard_GUID as \"CmpCallCard_GUID\",
                      CmpCallCard_rGUID as \"CmpCallCard_rGUID\",
                      KLCity_id as \"KLCity_id\",
                      KLTown_id as \"KLTown_id\",
                      KLStreet_id as \"KLStreet_id\",
                      Lpu_ppdid as \"Lpu_ppdid\",
                      CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\",
                      CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
                      CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
                      CmpCallCardStatus_Comment as \"CmpCallCardStatus_Comment\",
                      CmpCallCard_IsReceivedInPPD as \"CmpCallCard_IsReceivedInPPD\",
                      CmpPPDResult_id as \"CmpPPDResult_id\",
                      EmergencyTeam_id as \"EmergencyTeam_id\",
                      CmpCallCard_IsInReg as \"CmpCallCard_IsInReg\",
                      Lpu_id as \"Lpu_id\",
                      CmpCallCard_IsMedPersonalIdent as \"CmpCallCard_IsMedPersonalIdent\",
                      MedPersonal_id as \"MedPersonal_id\",
                      ResultDeseaseType_id as \"ResultDeseaseType_id\",
                      CmpCallCard_firstVersion as \"CmpCallCard_firstVersion\",
                      UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
                      CmpCallCard_IsPaid as \"CmpCallCard_IsPaid\",
                      CmpCallCard_Korp as \"CmpCallCard_Korp\",
                      CmpCallCard_Room as \"CmpCallCard_Room\",
                      CmpCallCard_DiffTime as \"CmpCallCard_DiffTime\",
                      UslugaComplex_id as \"UslugaComplex_id\",
                      LpuBuilding_id as \"LpuBuilding_id\",
                      CmpCallerType_id as \"CmpCallerType_id\",
                      CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
                      CmpCallCard_rid as \"CmpCallCard_rid\",
                      CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                      CmpCallCard_BoostTime as \"CmpCallCard_BoostTime\",
                      CmpSecondReason_id as \"CmpSecondReason_id\",
                      CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
                      CmpCallReasonType_id as \"CmpCallReasonType_id\",
                      CmpReasonNew_id as \"CmpReasonNew_id\",
                      CmpCallCard_EmergencyTeamDiscardReason as \"CmpCallCard_EmergencyTeamDiscardReason\",
                      CmpCallCard_IndexRep as \"CmpCallCard_IndexRep\",
                      CmpCallCard_IndexRepInReg as \"CmpCallCard_IndexRepInReg\",
                      CmpCallCard_IsArchive as \"CmpCallCard_IsArchive\",
                      MedStaffFact_id as \"MedStaffFact_id\",
                      RankinScale_id as \"RankinScale_id\",
                      RankinScale_sid as \"RankinScale_sid\",
                      LeaveType_id as \"LeaveType_id\",
                      CmpCallCard_isShortEditVersion as \"CmpCallCard_isShortEditVersion\",
                      LpuSection_id as \"LpuSection_id\",
                      CmpCallCard_firstVersionGUID as \"CmpCallCard_firstVersionGUID\",
                      CmpCallCardStatus_GUID as \"CmpCallCardStatus_GUID\",
                      EmergencyTeam_GUID as \"EmergencyTeam_GUID\",
                      CmpCallCard_storDT as \"CmpCallCard_storDT\",
                      CmpCallCard_defCom as \"CmpCallCard_defCom\",
                      MedService_id as \"MedService_id\",
                      CmpCallCard_PolisEdNum as \"CmpCallCard_PolisEdNum\",
                      CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\",
                      Diag_sopid as \"Diag_sopid\",
                      CmpLeaveType_id as \"CmpLeaveType_id\",
                      CmpLeaveTask_id as \"CmpLeaveTask_id\",
                      CmpMedicalCareKind_id as \"CmpMedicalCareKind_id\",
                      CmpTransportType_id as \"CmpTransportType_id\",
                      CmpResultDeseaseType_id as \"CmpResultDeseaseType_id\",
                      CmpCallCardResult_id as \"CmpCallCardResult_id\",
                      Person_IsUnknown as \"Person_IsUnknown\",
                      CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
                      Lpu_smpid as \"Lpu_smpid\",
                      Lpu_hid as \"Lpu_hid\",
                      UnformalizedAddressDirectory_wid as \"UnformalizedAddressDirectory_wid\",
                      PayType_id as \"PayType_id\",
                      CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
                      CmpCallCard_sid as \"CmpCallCard_sid\",
                      CmpCallCard_IsActiveCall as \"CmpCallCard_IsActiveCall\",
                      CmpCallCard_isControlCall as \"CmpCallCard_isControlCall\",
                      CmpCallCard_isTimeExceeded as \"CmpCallCard_isTimeExceeded\",
                      CmpCallCard_NumvPr as \"CmpCallCard_NumvPr\",
                      CmpCallCard_NgodPr as \"CmpCallCard_NgodPr\",
                      CmpCallSignType_id as \"CmpCallSignType_id\",
                      Lpu_CodeSMO as \"Lpu_CodeSMO\",
                      Registry_sid as \"Registry_sid\",
                      Diag_gid as \"Diag_gid\",
                      MedicalCareBudgType_id as \"MedicalCareBudgType_id\",
                      CmpCommonState_id as \"CmpCommonState_id\",
                      Person_FirName as \"Person_FirName\",
                      Person_SecName as \"Person_SecName\",
                      Person_Age as \"Person_Age\",
                      Person_BirthDay as \"Person_BirthDay\",
                      Person_PolisSer as \"Person_PolisSer\",
                      Person_PolisNum as \"Person_PolisNum\",
                      Sex_id as \"Sex_id\",
                      CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                      CmpCallType_id as \"CmpCallType_id\",
                      CmpProfile_cid as \"CmpProfile_cid\",
                      CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
                      CmpCallCard_Stan as \"CmpCallCard_Stan\",
                      CmpCallCard_prmDT as \"CmpCallCard_prmDT\",
                      CmpCallCard_Line as \"CmpCallCard_Line\",
                      CmpResult_id as \"CmpResult_id\",
                      CmpArea_gid as \"CmpArea_gid\",
                      CmpLpu_id as \"CmpLpu_id\",
                      CmpDiag_oid as \"CmpDiag_oid\",
                      CmpDiag_aid as \"CmpDiag_aid\",
                      CmpTrauma_id as \"CmpTrauma_id\",
                      CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
                      Diag_uid as \"Diag_uid\",
                      CmpCallCard_Numb as \"CmpCallCard_Numb\",
                      CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
                      CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
                      CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
                      CmpProfile_bid as \"CmpProfile_bid\",
                      CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
                      CmpCallCard_RCod as \"CmpCallCard_RCod\",
                      CmpCallCard_TabN as \"CmpCallCard_TabN\",
                      CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
                      CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
                      CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
                      CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
                      Diag_sid as \"Diag_sid\",
                      CmpTalon_id as \"CmpTalon_id\",
                      CmpCallCard_Expo as \"CmpCallCard_Expo\",
                      CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
                      CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
                      CmpCallCard_D201 as \"CmpCallCard_D201\",
                      CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
                      CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
                      CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
                      CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
                      CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
                      CmpCallCard_Tper as \"CmpCallCard_Tper\",
                      CmpCallCard_Vyez as \"CmpCallCard_Vyez\",
                      CmpCallCard_Przd as \"CmpCallCard_Przd\",
                      CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\",
                      CmpCallCard_Tsta as \"CmpCallCard_Tsta\",
                      CmpCallCard_Tisp as \"CmpCallCard_Tisp\",
                      CmpCallCard_Tvzv as \"CmpCallCard_Tvzv\",
                      CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
                      CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
                      CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
                      CmpArea_pid as \"CmpArea_pid\",
                      CmpCallCard_PCity as \"CmpCallCard_PCity\",
                      CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
                      CmpCallCard_PDom as \"CmpCallCard_PDom\",
                      CmpCallCard_PKvar as \"CmpCallCard_PKvar\"
                     FROM v_CmpCallCard  
                     WHERE CmpCallCard_id = :CmpCallCard_id 
                     LIMIT 1";

		$result = $this->db->query($preQuery, $data);

		if(!$result) return false;

		$card = $result->row_array();

		if (!isset($data['CmpCallType_id'])) {
			//если нет типа вызова, то сохраняем тот который на карте
			$data['CmpCallType_id'] = (!empty($card['CmpCallType_id'])) ? $card['CmpCallType_id'] : null;
		}

		if (isset($data['CmpCallCard_rid']) && $data['CmpCallCard_rid'] == false) {
			$data['CmpCallCard_rid'] = null;
		} elseif (empty($data['CmpCallCard_rid'])) {
			//если нет родительского вызова, то сохраняем тот который на карте
			$data['CmpCallCard_rid'] = (!empty($card['CmpCallCard_rid'])) ? $card['CmpCallCard_rid'] : null;
		}
		if (!isset($data['CmpReason_id'])) {
			//если нет повода вызова, то сохраняем тот который на карте
			$data['CmpReason_id'] = (!empty($card['CmpReason_id'])) ? $card['CmpReason_id'] : null;
		}
        if (!isset($data['CmpCallCard_IsNMP'])) {
			$data['CmpCallCard_IsNMP']= null;
		}
        if (empty($data['EmergencyTeam_id'])) {
			$data['EmergencyTeam_id'] = null;
		}
		if(!empty($data['CmpRejectionReason_Name'])){
			$cccParams = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'CmpCallCard_Comm' => $card['CmpCallCard_Comm'] . ' Причина отказа:' . $data['CmpRejectionReason_Name'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->changeCmpCallCardCommonParams($cccParams);
		}

		$data['CmpCallCardStatus_id'] = !empty($data['CmpCallCardStatus_id']) ? $data['CmpCallCardStatus_id'] : null;
		$data['CmpCallCardEvent_id'] = !empty($data['CmpCallCardEvent_id']) ? $data['CmpCallCardEvent_id'] : null;

		$query = "
			select 
			    CmpCallCard_id as \"CmpCallCard_id\",
				ErrCode as \"Error_Code\",
				ErrMessage as \"Error_Msg\",
				CmpCallCardStatus_id as \"CmpCallCardStatus_id\",
				CmpCallCardEvent_id as \"CmpCallCardEvent_id\"
				
			from p_CmpCallCard_setStatus (
				CmpCallCard_id := :CmpCallCard_id,
				CmpCallCardStatusType_id := :CmpCallCardStatusType_id,
				CmpCallCardStatus_Comment := :CmpCallCardStatus_Comment,
				CmpCallCard_IsReceivedInPPD := :CmpCallCard_IsReceivedInPPD,
				CmpReason_id := :CmpReason_id,
				CmpRejectionReason_id := :CmpRejectionReason_id,
				CmpCallCard_rid := :CmpCallCard_rid,
				pmUser_id := :pmUser_id,
				CmpMoveFromNmpReason_id := :CmpMoveFromNmpReason_id,
				CmpReturnToSmpReason_id := :CmpReturnToSmpReason_id,
				CmpCallCard_IsNMP := :CmpCallCard_IsNMP,
				CmpCallType_id := :CmpCallType_id,
				EmergencyTeam_id := :EmergencyTeam_id
                )
		";
		//@todo надо вернуть CmpCallCardStatus_id и CmpCallCardEvent_id
		
		//var_dump(getDebugSQL($query, $data)); exit;
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			//$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
			//$this->CmpCallCard_model->setCmpCallCardEvent($data);

			if(defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE ){
				$this->load->model('CmpCallCard_model', 'cccmodel');
				$this->cccmodel->checkSendReactionToActiveMQ(array('CmpCallCard_id' => $data['CmpCallCard_id']));
			}

			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @desc Загрузка карт вызовов
	 * @param array $data
	 * @return boolean
	 */
	function loadSMPCmpCallCardsList($data) {

		if(
			empty($data['cmpCallCardList']) &&
			empty($data['showByDp']) &&
			(empty($data['Search_SurName']) || empty($data['Search_FirName'])) &&
			((empty($data['KLCity_id']) && empty($data['Town_id'])) || (empty($data['KLStreet_id']) && empty($data['UnformalizedAddressDirectory_id'])))
		) return false;

		$filter = '(1 = 1)';
		$queryParams = array();

		//$this->RefuseOnTimeout($data);

		// Скрываем вызовы принятые в ППД
		//$filter .= " and CCC.CmpCallCard_IsReceivedInPPD!=2";


		//$filter .= " and COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2"; // временно только открытые карты


		$filter .= " and CCT.CmpCallType_Code in (1,2,4,9)";

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and CCC.Person_SurName ilike :Person_SurName";
			$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and CCC.Person_FirName ilike :Person_FirName";
			$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and CCC.Person_SecName ilike :Person_SecName";
			$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and CCC.Person_BirthDay = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['CmpLpu_id']) ) {
			$filter .= " and CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams['CmpLpu_id'] = $data['CmpLpu_id'];
		}

		if ( !empty($data['CmpCallCard_Ngod']) ) {
			$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
		}

		if ( !empty($data['CmpCallCard_Numv']) ) {
			$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
		}

		if ( !empty($data['dispatchCallPmUser_id']) ) {
			$filter .= " and CCC.pmUser_insID = :dispatchCallPmUser_id";
			$queryParams['dispatchCallPmUser_id'] = $data['dispatchCallPmUser_id'];
		}
		if ( !empty($data['EmergencyTeam_id']) ) {
			$filter .= " and CCC.EmergencyTeam_id = :EmergencyTeam_id";
			$queryParams['EmergencyTeam_id'] = $data['EmergencyTeam_id'];
		}

		//Для получения изменений одного талона вызова
		if ( !empty($data['CmpCallCard_id']) ) {
			$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}


		if ( !empty($data['KLCity_id'])) {
			$filter .= " and CCC.KLCity_id = :KLCity_id";
			$queryParams['KLCity_id'] = $data['KLCity_id'];
		}

		if ( !empty($data['Town_id'])) {
			$filter .= " and CCC.KLTown_id = :Town_id";
			$queryParams['Town_id'] = $data['Town_id'];
		}

		if ( !empty($data['KLStreet_id'])) {
			$filter .= " and CCC.KLStreet_id = :KLStreet_id";
			$queryParams['KLStreet_id'] = $data['KLStreet_id'];
		}

		if ( !empty($data['UnformalizedAddressDirectory_id'])) {
			$filter .= " and CCC.UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id";
			$queryParams['UnformalizedAddressDirectory_id'] = $data['UnformalizedAddressDirectory_id'];
		}

		$CurArmType = (!empty($_SESSION['CurArmType']) ? $_SESSION['CurArmType'] : '');
		if (in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp'))) {

			$lpuBuildingOptions = $this->getLpuBuildingOptions(array('session' => $_SESSION));
			if (empty($lpuBuildingOptions[0]["LpuBuilding_eid"])) {
				$filter .= " and CCC.CmpCallCard_IsExtra != 1";
			}
		}
		// TODO тут придумать чтото с ППД..
		$filter .=" and CCC.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];

		// Отображаем только вызовы переданные от диспетчера вызовов СМП
		//$filter .= " AND CCC.CmpCallCardStatusType_id IS NOT NULL";

		//старое
		//,RTRIM(COALESCE(CD.CmpDiag_Code, '')) as CmpDiag_Name


		if ( !empty($data['cmpCallCardList']) && count(json_decode($data['cmpCallCardList'], true)) > 0) {
			$cardsArray = json_decode($data['cmpCallCardList'], true);
			//Если пришел массив карт, то грузим только их
			count($cardsArray) > 0 ? $filter =  " CCC.CmpCallCard_id in (" . implode(',', $cardsArray) . ")" : $filter .= '';
		}
		else{
			if (!empty($data['begDate']) && !empty($data['endDate']) && ($data['begDate'] == $data['endDate']) && (!empty($data['hours']))) {

				$filter .= " and cast(CCC.CmpCallCard_prmDT as datetime) >= DATEADD('day', -1, CAST(:begDate as date))";
				$queryParams['begDate'] = $data['begDate'];
				//$filter .= " and CCC.CmpCallCard_prmDT <= @getdate";
				switch ($data['hours']) {
					case '1':
					case '2':
					case '3':
					case '6':
					case '12':
					case '24':
						$queryParams['hours'] = '-' . $data['hours'];
						break;
					default:
						$queryParams['hours'] = '-24';
						break;
				}
			} else {
				if (!empty($data['begDate'])) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= CAST(:begDate as date)";
					$queryParams['begDate'] = $data['begDate'];
				}

				if (!empty($data['endDate'])) {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= CAST(:endDate as date)";
					$queryParams['endDate'] = $data['endDate'];
				}
			}
		}

		$filter .=" and (C112.CmpCallCard112StatusType_id is null or C112.CmpCallCard112StatusType_id in (3,4,5))";
		$filter .=" and COALESCE(CCC.CmpCallCardStatusType_id, 0) <> 5";


		$query = "
			with cmp_waiting_ppd_time bigint as (
			    select COALESCE((select DS.DataStorage_Value as DataStorage_Value FROM DataStorage DS where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0 limit 1), 20)
			    );
			
            select CCC.CmpCallCard_id as CmpCallCard_id, CCC.Lpu_ppdid as Lpu_ppdid
            from v_CmpCallCard CCC 

                left join v_PersonState PS  on PS.Person_id = CCC.Person_id

                left join v_CmpCallCard112 C112  on CCC.CmpCallCard_id = C112.CmpCallCard_id

                left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

            where " . $filter . "

			with activeEventArray as (
				select
					CCCA.CmpCallCard_id as CmpCallCard_id,
					CCCET.CmpCallCardEventType_Name as CmpCallCardEventType_Name,
					CCCET.CmpCallCardEventType_Code as CmpCallCardEventType_Code,
					CCCE.CmpCallCardEvent_updDT as CmpCallCardEvent_updDT
				from
					v_CmpCallCardEvent CCCE 

					inner join #tmp CCCA on CCCA.CmpCallCard_id = CCCE.CmpCallCard_id
					LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

				where
					CCCET.CmpCallCardEventType_IsKeyEvent = 2
			)

			select
				-- select
				 CCC.CmpCallCard_id as \"CmpCallCard_id\"
				 ,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') || ' ' || to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'HH24:MI:SS') as \"CmpCallCard_prmDT\"


				,CCC.Person_id as \"Person_id\"
				,CCC.CmpCallCard_storDT as \"CmpCallCard_storDT\"
				,CCC.Person_IsUnknown as \"Person_IsUnknown\"
				,PS.PersonEvn_id as \"PersonEvn_id\"
				,PS.Server_id as \"Server_id\"
				,CCC.Person_SurName as \"Person_Surname\"
				,CCC.Person_FirName as \"Person_Firname\"
				,CCC.Person_SecName as \"Person_Secname\"
				,CCC.pmUser_insID as \"pmUser_insID\"
				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
				,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,case when COALESCE(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as \"CmpCallCard_isLocked\"

				,case when COALESCE(CCCLL.CmpCallCardLockList_id,0) = 0 then

					COALESCE(CCC.Person_SurName, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				else
					'<img src=\"../img/grid/lock.png\">'||COALESCE(CCC.Person_SurName, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as \"Person_FIO\"
				,to_char(CCC.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\"

				,CR.CmpReason_id as \"CmpReason_id\"
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"

				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as \"CmpLpu_Name\"
				,RTRIM(COALESCE(CLD.Diag_Code, '') ||' '|| COALESCE(CLD.Diag_Name, '')) as \"CmpDiag_Name\"

				,RTRIM(COALESCE(D.Diag_Code, '')) as \"StacDiag_Name\"

				,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
				,ET.EmergencyTeam_id as \"EmergencyTeam_id\"
				,CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
				,activeEvent.CmpCallCardEventType_Name as \"CmpCallCardEventType_Name\"

				,case when CCC.CmpCallCardStatusType_id = 1
						then TO_CHAR (select DataStorage_Value as DataStorage_Value from cmp_waiting_ppd_time) ( * INTERVAL '1 \'minute\' - DATEDIFF('minute',CCC.CmpCallCard_updDT, dbo.tzGetDate())  ' ||to_char(0), 'HH24:MI:SS')

						else '00'||':'||'00'
				end as \"PPD_WaitingTime\"

				,SLPU.Lpu_Nick as \"SendLpu_Nick\",

				OVERLAY(
					CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' || SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' || City.KLCity_Name, '') END
					|| COALESCE(', ' || Town.KLTown_FullName, '')
					|| case when Street.KLStreet_FullName is not null then
						case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
						', '||Street.KLStreet_FullName  end
					else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
					end
					|| case when SecondStreet.KLStreet_FullName is not null then
						case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
						', '||SecondStreet.KLStreet_FullName end
						else ''
					end
					|| COALESCE(', д.' || CCC.CmpCallCard_Dom, '')
					|| COALESCE(', к.' || CCC.CmpCallCard_Korp, '')
					|| COALESCE(', кв.' || CCC.CmpCallCard_Kvar, '')
					|| COALESCE(', комн.' || CCC.CmpCallCard_Comm, '')
					|| COALESCE(', место: ' || UAD.UnformalizedAddressDirectory_Name, '')
					-- параметры STUFF
					    placing '' from 1 for 2
				) as \"Adress_Name\",
				RGN.KLRgn_id as \"KLRgn_id\",
				SRGN.KLSubRgn_id as \"KLSubRgn_id\",
				City.KLCity_id as \"KLCity_id\",
				COALESCE(City.KLSocr_Nick || ' ' || City.KLCity_Name, '') as \"KLCity_Name\",
				Town.KLTown_id as \"KLTown_id\",
				COALESCE(Town.KLSocr_Nick || ' ' || Town.KLTown_Name, '') as \"KLTown_Name\",
				Street.KLStreet_id as \"KLStreet_id\",
				Street.KLStreet_FullName as \"KLStreet_FullName\",
				CCC.CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",
				SecondStreet.KLStreet_FullName as SecondStreet_FullName,
				CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
				CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",
				CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
				CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
				CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
				CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
				CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
				CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
				CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
				CCC.Lpu_smpid as \"Lpu_smpid\",
				CCC.CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\",
				CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.Lpu_ppdid as \"Lpu_ppdid\",
				CCC.MedService_id as \"MedService_id\",
				CCC.Person_Age as \"Person_Age\",
				CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, 0) !=0 ) THEN
					CASE WHEN DATEDIFF('minute',CCC.Person_BirthDay ,dbo.tzGetDate() ) > 12 THEN
						to_char (DATEDIFF('year' ,CCC.Person_BirthDay ,dbo.tzGetDate() )  ) || ' лет'
					ELSE
						CASE WHEN DATEDIFF('day',CCC.Person_BirthDay ,dbo.tzGetDate() ) <=30 THEN
							to_char (DATEDIFF('day',CCC.Person_BirthDay ,dbo.tzGetDate() ) ) || ' дн. '
						ELSE
							to_char (DATEDIFF('minute',CCC.Person_BirthDay ,dbo.tzGetDate() )  ) || ' мес.'
						END
					END
				ELSE
					CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
					ELSE cast(CCC.Person_Age as varchar) || ' лет'
					END
				END as \"Person_AgeText\",
				CCC.Sex_id as \"Sex_id\",
				CCC.CmpCallerType_id as \"CmpCallerType_id\",
				CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
				UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\",
				UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\",
				UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\",

				case when COALESCE(CCC.KLStreet_id,0) = 0 then

							case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

							else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
							else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as StreetAndUnformalizedAddressDirectory_id,
				case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when COALESCE(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment

						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end ||
					case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end ||
					case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
				end	as \"PPDResult\"
				,to_char(cast(ServeDT.ServeDT as timestamp(3)), 'DD.MM.YYYY') as \"ServeDT\"

				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name || to_char(cast(CCC.CmpCallCard_updDT as timestamp(3)), 'DD.MM.YYYY') else '' end as \"PPDUser_Name\"


				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then 1
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN 4
									WHEN CCC.CmpCallCardStatusType_id=6 THEN 10
									WHEN CCC.CmpCallCardStatusType_id=3 THEN 8
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id+1
									WHEN CmpCallCardStatusType_id=7 THEN 3
									WHEN CmpCallCardStatusType_id=8 THEN 2
									ELSE CCC.CmpCallCardStatusType_id+4
								END
							ELSE
								CASE

									WHEN CmpCallCardStatusType_id=4 THEN 7
									WHEN CmpCallCardStatusType_id=3 THEN 8
                                    WHEN CmpCallCardStatusType_id=7 THEN 3
									WHEN CmpCallCardStatusType_id=8 THEN 2
									ELSE CCC.CmpCallCardStatusType_id+4
								END
							END
					else 9
				end as \"CmpGroup_id\"
				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '04'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CmpCallCardStatusType_id=7 THEN '03'
									WHEN CmpCallCardStatusType_id=8 THEN '02'
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN '0'||cast(CCC.CmpCallCardStatusType_id+1 as varchar(1))
									ELSE  '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar(2))
								END
							ELSE
								CASE
									WHEN CmpCallCardStatusType_id=4 THEN '07'
									WHEN CmpCallCardStatusType_id=3 THEN '08'
									WHEN CmpCallCardStatusType_id=6 THEN '10'
                                    WHEN CmpCallCardStatusType_id=7 THEN '03'
									WHEN CmpCallCardStatusType_id=8 THEN '02'
									ELSE '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar(2))
								END
							END
					else '09'
				end as \"CmpGroupName_id\"
				-- end select
			from
				-- from
				v_CmpCallCard CCC 

				inner join #tmp CCCA on CCCA.CmpCallCard_id = CCC.CmpCallCard_id

				LEFT JOIN LATERAL(

					select
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus 

					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ServeDT ON TRUE
				LEFT JOIN LATERAL(

					select
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus 

					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ToDT ON TRUE
				LEFT JOIN LATERAL(

					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id as CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name as CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus 

						left join v_CmpMoveFromNmpReason  on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id

					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as CCCStatusHist ON TRUE

				LEFT JOIN LATERAL (

					select
						CmpCallCardEventType_Name as CmpCallCardEventType_Name,
						CmpCallCardEventType_Code as CmpCallCardEventType_Code,
						CmpCallCardEvent_updDT as CmpCallCardEvent_updDT
					from activeEventArray
					where CmpCallCard_id = CCC.CmpCallCard_id
					ORDER BY CmpCallCardEvent_updDT desc
					limit 1
				) as activeEvent ON TRUE

				left join v_PersonState PS  on PS.Person_id = CCC.Person_id

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				left join CmpLpu CL  on CL.CmpLpu_id = CCC.CmpLpu_id

				left join v_Lpu L  on L.Lpu_id = CCC.CmpLpu_id

				left join CmpDiag CD  on CD.CmpDiag_id = CCC.CmpDiag_oid

				left join Diag D  on D.Diag_id = CCC.Diag_sid

				left join v_Lpu SLPU  on SLPU.Lpu_id = CCC.Lpu_ppdid

				LEFT JOIN LATERAL (

					SELECT
					evnclass_id as evnclass_id,
                    evnclass_name as evnclass_name,
                    evnpl_id as evnpl_id,
                    evnpl_setdate as evnpl_setdate,
                    evnpl_settime as evnpl_settime,
                    evnpl_disdate as evnpl_disdate,
                    evnpl_distime as evnpl_distime,
                    evnpl_diddate as evnpl_diddate,
                    evnpl_didtime as evnpl_didtime,
                    evnpl_pid as evnpl_pid,
                    evnpl_rid as evnpl_rid,
                    lpu_id as lpu_id,
                    server_id as server_id,
                    personevn_id as personevn_id,
                    evnpl_setdt as evnpl_setdt,
                    evnpl_disdt as evnpl_disdt,
                    evnpl_diddt as evnpl_diddt,
                    evnpl_insdt as evnpl_insdt,
                    evnpl_upddt as evnpl_upddt,
                    evnpl_index as evnpl_index,
                    evnpl_count as evnpl_count,
                    pmuser_insid as pmuser_insid,
                    pmuser_updid as pmuser_updid,
                    person_id as person_id,
                    morbus_id as morbus_id,
                    evnpl_issigned as evnpl_issigned,
                    pmuser_signid as pmuser_signid,
                    evnpl_signdt as evnpl_signdt,
                    evnpl_isarchive as evnpl_isarchive,
                    evnpl_guid as evnpl_guid,
                    evnpl_indexminusone as evnpl_indexminusone,
                    evnstatus_id as evnstatus_id,
                    evnpl_statusdate as evnpl_statusdate,
                    evnpl_istransit as evnpl_istransit,
                    evnpl_vizitcount as evnpl_vizitcount,
                    evnpl_isfinish as evnpl_isfinish,
                    person_age as person_age,
                    evnpl_ismsedirected as evnpl_ismsedirected,
                    evndirection_id as evndirection_id,
                    evnpl_numcard as evnpl_numcard,
                    prehospdirect_id as prehospdirect_id,
                    lpu_did as lpu_did,
                    org_did as org_did,
                    lpusection_did as lpusection_did,
                    diag_did as diag_did,
                    prehosptrauma_id as prehosptrauma_id,
                    evnpl_isunlaw as evnpl_isunlaw,
                    evnpl_isunport as evnpl_isunport,
                    resultclass_id as resultclass_id,
                    evnpl_ukl as evnpl_ukl,
                    directtype_id as directtype_id,
                    directclass_id as directclass_id,
                    lpu_oid as lpu_oid,
                    lpusection_oid as lpusection_oid,
                    diag_id as diag_id,
                    deseasetype_id as deseasetype_id,
                    evndirection_num as evndirection_num,
                    evndirection_setdt as evndirection_setdt,
                    evnpl_isfirsttime as evnpl_isfirsttime,
                    evnpl_complexity as evnpl_complexity,
                    medpersonal_id as medpersonal_id,
                    lpusection_id as lpusection_id,
                    diag_agid as diag_agid,
                    cmpcallcard_id as cmpcallcard_id,
                    resultdeseasetype_id as resultdeseasetype_id,
                    evnpl_isinreg as evnpl_isinreg,
                    evnpl_ispaid as evnpl_ispaid,
                    medicalcarekind_id as medicalcarekind_id,
                    evnpl_iscons as evnpl_iscons,
                    evnpl_indexrep as evnpl_indexrep,
                    evnpl_indexrepinreg as evnpl_indexrepinreg,
                    leavetype_fedid as leavetype_fedid,
                    resultdeseasetype_fedid as resultdeseasetype_fedid,
                    lpu_codesmo as lpu_codesmo,
                    evnpl_firstvizitdt as evnpl_firstvizitdt,
                    evnpl_lastvizitdt as evnpl_lastvizitdt,
                    evnpl_lastuslugadt as evnpl_lastuslugadt,
                    diag_preid as diag_preid,
                    interruptleavetype_id as interruptleavetype_id,
                    diag_concid as diag_concid,
                    server_omsid as server_omsid,
                    personevn_omsid as personevn_omsid,
                    evnvizitpl_omsid as evnvizitpl_omsid,
                    medstafffact_did as medstafffact_did,
                    diag_fid as diag_fid,
                    diag_lid as diag_lid,
                    evnpl_isfirstdisable as evnpl_isfirstdisable,
                    privilegetype_id as privilegetype_id,
                    evnpl_firstvizitcapitadt as evnpl_firstvizitcapitadt,
                    evnpl_firstvizitscapitadt as evnpl_firstvizitscapitadt,
                    evnvizitpl_cid as evnvizitpl_cid,
                    evnvizitpl_scid as evnvizitpl_scid,
                    evnpl_lastuslugacapitadt as evnpl_lastuslugacapitadt,
                    evnpl_lastuslugascapitadt as evnpl_lastuslugascapitadt,
                    evnpl_iscrossed as evnpl_iscrossed,
                    medicalcarebudgtype_id as medicalcarebudgtype_id,
                    evnpl_isinregzno as evnpl_isinregzno,
                    evnpl_issurveyrefuse as evnpl_issurveyrefuse,
                    registry_sid as registry_sid,
                    evnpl_medpersonalcode as evnpl_medpersonalcode,
                    evnpl_firstuslugacapitadt as evnpl_firstuslugacapitadt,
                    evnpl_firstuslugascapitadt as evnpl_firstuslugascapitadt
					FROM v_EvnPL AS t1

					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
                    LIMIT 1
				) EPL ON TRUE
				/*left join v_EvnPL EPL  on 1=1

					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

				left join v_Diag EPLD  on EPLD.Diag_id = EPL.Diag_id

				left join v_ResultClass RC  on RC.ResultClass_id = EPL.ResultClass_id

				left join v_DirectType DT  on DT.DirectType_id = EPL.DirectType_id

				left join v_pmUserCache PMC  on PMC.PMUser_id = CCC.pmUser_updID

				left join v_EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

				left join v_CmpCloseCard CLC  on CCC.CmpCallCard_id = CLC.CmpCallCard_id

				left join v_Diag CLD  on CLC.Diag_id = CLD.Diag_id

				left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id


				left join v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

					and (60 - DATEDIFF('second',CCCLL.CmpCallCardLockList_updDT, dbo.tzGetDate())) > 0

				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id

				left join v_KLStreet SecondStreet  on 'SecondStreet.KLStreet_id' = CCC.CmpCallCard_UlicSecond

				left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id


				-- end from
			where
				-- where
				(1 = 1)
				-- end where
			order by
				-- order by
				(case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id+2
							else
								case when CCC.Lpu_ppdid is not null
									then 1
									else 2
								end
						end
					else 7
				end),

				CCC.CmpCallCard_prmDT desc
				-- end order by
            limit 10
		";

		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}

	/**
	 * default desc
	 */
	function loadCmpCallCardEditForm($data) {
		
		$query = "
			select
				'' as \"accessType\",
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				COALESCE(CCC.Person_id, 0) as \"Person_id\",

				CCC.CmpArea_gid as \"CmpArea_gid\",
				CCPT.CmpCallPlaceType_Name as \"CmpCallPlaceType_Name\",
				CCPT.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.CmpArea_id as \"CmpArea_id\",
				CCC.CmpArea_pid as \"CmpArea_pid\",
				CCC.CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
				CASE WHEN (CLC.CmpCloseCard_id is not null) THEN RL.Localize ELSE CCC.CmpCallCard_IsPoli END as \"CmpCallCard_IsPoli\",
				CCC.CmpCallType_id as \"CmpCallType_id\",
				CCC.CmpDiag_aid as \"CmpDiag_aid\",
				CCC.CmpDiag_oid as \"CmpDiag_oid\",
				CCC.CmpLpu_aid as \"CmpLpu_aid\",
				COALESCE(CCC.Lpu_hid,lsL.Lpu_id) as \"Lpu_hid\",
				CL.Lpu_id as \"Lpu_oid\",
				CCC.CmpPlace_id as \"CmpPlace_id\",
				CCC.CmpProfile_bid as \"CmpProfile_bid\",
				CCC.CmpProfile_cid as \"CmpProfile_cid\",
				CCC.MedService_id as \"MedService_id\",
				CCC.CmpReason_id as \"CmpReason_id\",
				CR.CmpReason_Code as \"CmpReason_Code\",
				CCC.CmpResult_id as \"CmpResult_id\",
				CCC.ResultDeseaseType_id as \"ResultDeseaseType_id\",
				CCC.CmpTalon_id as \"CmpTalon_id\",
				CCC.CmpTrauma_id as \"CmpTrauma_id\",
				CCC.Diag_sid as \"Diag_sid\",
				CCC.Diag_uid as \"Diag_uid\",
				--CCC.Sex_id as \"Sex_id\",
				case when COALESCE(CCC.Sex_id,0) = 0 then

					case when COALESCE(PS.Sex_id,0) != 0 then PS.Sex_id else null end

				else CCC.Sex_id end as \"Sex_id\",
				PS.Sex_id as \"SexIdent_id\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
				CCC.CmpCallCard_Prty as \"CmpCallCard_Prty\",
				CCC.CmpCallCard_Sect as \"CmpCallCard_Sect\",
				CCC.CmpCallCard_City as \"CmpCallCard_City\",
				CCC.CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
				CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
				CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",
				CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
				CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
				CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
				CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
				CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
				CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
				CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
				CCC.LpuBuilding_id as \"LpuBuilding_id\",
				CCC.CmpCallCard_IsNMP as \"CmpCallCard_IsNMP\",
				CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\",
				CCC.Person_IsUnknown as \"Person_IsUnknown\",
				RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as \"Person_SurName\",
				RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as \"Person_FirName\",
				RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as \"Person_SecName\",
				RTRIM(LTRIM(COALESCE(PS.Person_Surname, ''))) as \"PersonIdent_Surname\",

				RTRIM(LTRIM(COALESCE(PS.Person_Firname, ''))) as \"PersonIdent_Firname\",

				RTRIM(LTRIM(COALESCE(PS.Person_Secname, ''))) as \"PersonIdent_Secname\",

				to_char (COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\",

				COALESCE(CCC.Person_Age, dbo.Age2(COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_prmDT)) as \"Person_AgeInt\",

				COALESCE(CCC.Person_Age, dbo.Age2(COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_prmDT)) as \"Person_Age\",

				CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay, 0) !=0 ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) > 12 THEN

                		'1'
                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	'3'
                        ELSE
                        	'2'
                        END
                   	END
			 	ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE '1'
                    END
                END as \"ageUnit_id\",
				COALESCE(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), '') as \"PersonIdent_Age\",

				PS.Polis_Ser as \"Polis_Ser\",
				COALESCE(CCC.Person_PolisNum,PS.Polis_Num) as \"Polis_Num\",

				PS.Person_EdNum as \"Polis_EdNum\",
				PS.Polis_Num as \"PolisIdent_Num\",
				CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
				CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
				CCC.CmpCallerType_id as \"CmpCallerType_id\",
				CCC.CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
				CCC.CmpCallCard_Stan as \"CmpCallCard_Stan\",
				to_char(CCC.CmpCallCard_prmDT, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_prmDate\",

				to_char (CCC.CmpCallCard_prmDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmTime\",
				CCC.CmpCallCard_Line as \"CmpCallCard_Line\",
				CCC.CmpCallCard_Numb as \"CmpCallCard_Numb\",
				CCC.CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
				CCC.CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
				CCC.CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
				CCC.CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
				CCC.CmpCallCard_RCod as \"CmpCallCard_RCod\",
				CCC.CmpCallCard_TabN as \"CmpCallCard_TabN\",
				CCC.CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
				CCC.MedPersonal_id as \"MedPersonal_id\",
				COALESCE(CCC.CmpCallCard_IsMedPersonalIdent,1) as \"CmpCallCard_IsMedPersonalIdent\",

				CCC.CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
				CCC.CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
				CCC.CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
				CCC.CmpCallCard_Expo as \"CmpCallCard_Expo\",
				CCC.CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
				CCC.CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
				CCC.CmpCallCard_D201 as \"CmpCallCard_D201\",
				CCC.CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
				CCC.CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
				CCC.CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
				CCC.CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
				CCC.CmpCallCard_Kakp as \"CmpCallCard_Kakp\",

				to_char(CCC.CmpCallCard_Tper, 'HH24:MI:SS') as \"CmpCallCard_Tper\",

				to_char(CCC.CmpCallCard_Vyez, 'HH24:MI:SS') as \"CmpCallCard_Vyez\",

				to_char(CCC.CmpCallCard_Przd, 'HH24:MI:SS') as \"CmpCallCard_Przd\",

				to_char(CCC.CmpCallCard_Tgsp, 'HH24:MI:SS') as \"CmpCallCard_Tgsp\",

				to_char(CCC.CmpCallCard_Tsta, 'HH24:MI:SS') as \"CmpCallCard_Tsta\",

				to_char(CCC.CmpCallCard_Tisp, 'HH24:MI:SS') as \"CmpCallCard_Tisp\",

				to_char(CCC.CmpCallCard_Tvzv, 'HH24:MI:SS') as \"CmpCallCard_Tvzv\",


				to_char(CCC.CmpCallCard_Tper, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTper\",

				to_char(CCC.CmpCallCard_Vyez, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateVyez\",

				to_char(CCC.CmpCallCard_Przd, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DatePrzd\",

				to_char(CCC.CmpCallCard_Tgsp, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTgsp\",

				to_char(CCC.CmpCallCard_Tsta, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTsta\",

				to_char(CCC.CmpCallCard_Tisp, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTisp\",

				to_char(CCC.CmpCallCard_Tvzv, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTvzv\",

				to_char(CCC.CmpCallCard_HospitalizedTime, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_HospitalizedTime\",


				CCC.CmpCallCard_rid as \"CmpCallCard_rid\",
				CCC.CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
				CCC.CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
				CCC.CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
				CCC.CmpCallCard_PCity as \"CmpCallCard_PCity\",
				CCC.CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
				CCC.CmpCallCard_PDom as \"CmpCallCard_PDom\",
				CCC.CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
				CCC.cmpCallCard_Medc as \"cmpCallCard_Medc\",
				CCC.CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
				to_char(CCC.CmpCallCard_Tiz1, 'HH24:MI:SS') as \"CmpCallCard_Tiz1\",

				CCC.CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
				CCC.CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
				CCC.CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
				CCC.CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
				CCC.CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
				CCC.CmpCallCard_Inf6 as \"CmpCallCard_Inf6\"
				,CCC.Lpu_id as \"Lpu_id\"
				,CCC.Lpu_ppdid as \"Lpu_ppdid\"
				,COALESCE(L.Lpu_Nick,'') as \"CmpLpu_Name\"

				,CASE WHEN COALESCE(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as \"Person_isOftenCaller\"


				,CCC.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\"
				,CCC.CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\"
				,case when COALESCE(CCC.KLStreet_id,0) = 0 then

					case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

					else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"

				,CCC.KLRgn_id as \"KLRgn_id\"
				,CCC.KLSubRgn_id as \"KLSubRgn_id\"
				,CCC.KLCity_id as \"KLCity_id\"
				,CCC.KLTown_id as \"KLTown_id\"
				,CCC.KLStreet_id as \"KLStreet_id\"
				,CCC.CmpCallCard_CallLtd as \"CmpCallCard_CallLtd\"
				,CCC.CmpCallCard_CallLng as \"CmpCallCard_CallLng\"
				,CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"

				,ET.EmergencyTeam_id as \"EmergencyTeam_id\"
				,ETW.WialonEmergencyTeamId as \"WialonID\"
				,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
				,ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\"
				,ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\"
				,ETSpec.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\"
				,ETS.EmergencyTeamStatus_Name as \"EmergencyTeamStatus_Name\"
				,ETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\"
				,ET.EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\"
				,(case when (MP.Person_Fin is not null) then MP.Person_Fin else '' end) as \"EmergencyTeam_HeadDocName\"
				,MPDP.MedPersonal_id as \"DPMedPersonal_id\"
				,pccc.CmpCallCard_Numv as \"pcCmpCallCard_Numv\"
				,pccc.EmergencyTeam_id as \"pcEmergencyTeam_id\"
				,pccc.EmergencyTeamStatus_Code as \"pcEmergencyTeamStatus_Code\"
				,CCT.CmpCallType_Code as \"CmpCallType_Code\"
				,CCR.CmpCallRecord_id as \"CmpCallRecord_id\"
				
				,CUAPS.EmergencyTeamSpec_Code as \"LogicRulesEmergencyTeamSpec_Code\"
				,CUAPS.EmergencyTeamSpec_Name as \"LogicRulesEmergencyTeamSpec_Name\"
				,CCC.CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\"
				,CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\"
				,CCC.Lpu_smpid as \"Lpu_smpid\"
				,CCC.CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\"
				,to_char (CCC.CmpCallCard_updDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_updDT\"

				/* направлен с другого МО */
				,CCCrid.Lpu_id as \"Lpu_rid\"
				,case when COALESCE(CCC.Lpu_id,0) <> COALESCE(CCCrid.Lpu_id,0) and COALESCE(CRRrid.CmpRejectionReason_Code,0) = 5

					then 1 else 0
				end as \"directedFromAnotherLpu\"
			from
				v_CmpCallCard CCC 

				
				left join {$this->schema}.v_CmpCloseCard CLC  on CCC.CmpCallCard_id = CLC.CmpCallCard_id

                left join {$this->comboSchema}.v_CmpCloseCardCombo CLCC  on CLCC.CmpCloseCardCombo_Code = 241

                left join {$this->schema}.v_CmpCloseCardRel RL  on RL.CmpCloseCard_id = CLC.CmpCloseCard_id and CLCC.CmpCloseCardCombo_id = RL.CmpCloseCardCombo_id

				
				left join CmpLpu CL  on CL.CmpLpu_id = CCC.CmpLpu_id

				
				LEFT JOIN v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id


				LEFT JOIN v_EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

				LEFT JOIN v_EmergencyTeamWialonRel ETW  ON( ETW.EmergencyTeam_id=ET.EmergencyTeam_id )

                LEFT JOIN v_EmergencyTeamStatus AS ETS  ON( ETS.EmergencyTeamStatus_id=ET.EmergencyTeamStatus_id )

				LEFT JOIN v_EmergencyTeamSpec as ETSpec  ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)

				LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				LEFT JOIN v_MedPersonal as MP  ON( MP.MedPersonal_id=ET.EmergencyTeam_HeadShift )

				LEFT JOIN v_CmpCallCard CCCrid  on CCCrid.CmpCallCard_id = CCC.CmpCallCard_rid

				LEFT JOIN v_CmpRejectionReason CRRrid  on CCCrid.CmpRejectionReason_id = CRRrid.CmpRejectionReason_id

				LEFT JOIN LATERAL(

						select
							CCCS.pmUser_insID as pmUser_insID
						from
							v_CmpCallCardStatus CCCS 

							left join v_CmpCallCardEvent CCCE  on CCCE.CmpCallCardStatus_id = CCCS.CmpCallCardStatus_id

						where CCCE.cmpcallcard_id = CCC.cmpcallcard_id and CCCS.CmpCallCardStatusType_id in (1,4,6,16,18,19,21)
						order by CCCE.CmpCallCardEvent_insDT
						limit 1
					) as PMU ON TRUE
				left join pmUserCache PMUins on PMU.pmUser_insID = PMUins.pmUser_id
				left join v_MedPersonal MPDP  on PMUins.MedPersonal_id = MPDP.MedPersonal_id

				left join v_CmpCallRecord CCR on CCR.CmpCallCard_id = CCC.CmpCallCard_id
				
				LEFT JOIN LATERAL(

					select
						pccc.CmpCallCard_Numv as CmpCallCard_Numv,
						pccc.EmergencyTeam_id as EmergencyTeam_id,
						pETS.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code
					from
						v_CmpCallCard pccc 

						LEFT JOIN v_EmergencyTeam pET  on pccc.EmergencyTeam_id = pET.EmergencyTeam_id

						LEFT JOIN v_EmergencyTeamStatus AS pETS  ON( pETS.EmergencyTeamStatus_id=pET.EmergencyTeamStatus_id )

					where
						pccc.CmpCallCard_id = CCC.CmpCallCard_rid
                    limit 1
				) pccc ON TRUE
				
				LEFT JOIN LATERAL(

					select
						ETS.EmergencyTeamSpec_Code as EmergencyTeamSpec_Code,
						ETS.EmergencyTeamSpec_Name as EmergencyTeamSpec_Name
					from
						v_CmpUrgencyAndProfileStandart CUPS 

						left join v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP  on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRSP.CmpUrgencyAndProfileStandart_id

						left join v_EmergencyTeamSpec ETS  on ETS.EmergencyTeamSpec_id = CUPSRSP.EmergencyTeamSpec_id

					where
						CUPS.CmpReason_id = CCC.CmpReason_id
						AND (COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) > CCC.Person_Age)

						AND COALESCE(CUPS.Lpu_id,0) in (:Lpu_ppdid)
                    limit 1
				) CUAPS ON TRUE
				
				LEFT JOIN LATERAL(

					select
						 pa.Person_id as Person_id
						,COALESCE(pa.Person_SurName, '') as Person_Surname

						,COALESCE(pa.Person_FirName, '') as Person_Firname

						,COALESCE(pa.Person_SecName, '') as Person_Secname

						,pa.Person_BirthDay as Person_Birthday
						,COALESCE(pa.Sex_id, 0) as Sex_id

						,pa.Person_EdNum as Person_EdNum
						,COALESCE(p.Polis_Ser, '') as Polis_Ser

						,COALESCE(p.Polis_Num, '') as Polis_Num

					from
						v_Person_all pa 

						left join v_Polis p  on p.Polis_id = pa.Polis_id

					where
						Person_id = CCC.Person_id
						and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
					order by
						PersonEvn_insDT desc
                    limit 1
				) PS
				LEFT JOIN v_Lpu L  on L.Lpu_id = CCC.CmpLpu_id

				LEFT JOIN v_OftenCallers OC  on OC.Person_id = CCC.Person_id

				LEFT JOIN v_CmpCallPlaceType CCPT  on CCPT.CmpCallPlaceType_id = CCC.CmpCallPlaceType_id

				left join v_LpuSection LS  on LS.LpuSection_id = CCC.LpuSection_id

				left join v_LpuUnit LU  on LU.LpuUnit_id = LS.LpuUnit_id

				left join v_LpuBuilding LB  on LB.LpuBuilding_id = LU.LpuBuilding_id

				left join v_Lpu lsL  on lsL.Lpu_id = LB.Lpu_id

			where
				CCC.CmpCallCard_id = :CmpCallCard_id
            limit 1
			";
		

		$params = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'Lpu_ppdid' => $data['Lpu_id']
		);
		//echo getDebugSQL($query, $params);exit;
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			//var_dump($result->result('array')); exit;
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function loadSmpFarmacyRegisterHistory($data) {

		$queryParams = array();
		$where = array();
		$join = "";

		$where[] = 'CFBRH.CmpFarmacyBalance_id = :CmpFarmacyBalance_id';

		$query = "
			SELECT
			--select
				CFBRH.CmpFarmacyBalanceRemoveHistory_id as \"CmpFarmacyBalanceRemoveHistory_id\",
				CASE WHEN (COALESCE(D.Drug_Fas,0) = 0) then RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||' '||to_char(COALESCE(D.DrugForm_Name,''))||' '||to_char(COALESCE(D.Drug_Dose,'')))

					else RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||', '||to_char(COALESCE(D.DrugForm_Name,''))||', '||to_char(COALESCE(D.Drug_Dose,''))||', №'||TO_CHAR(D.Drug_Fas))

				end as \"DrugTorg_Name\",
				ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
				MP.Person_Fin as \"Person_Fin\",
				to_char(cast(CFBRH.CmpFarmacyBalanceRemoveHistory_insDT as timestamp(3)), 'DD.MM.YYYY') as \"CmpCallCard_prmDate\",

				CFBRH.CmpFarmacyBalanceRemoveHistory_DoseCount as \"CmpFarmacyBalanceRemoveHistory_DoseCount\",
				CFBRH.CmpFarmacyBalanceRemoveHistory_PackCount as \"CmpFarmacyBalanceRemoveHistory_PackCount\"
			--end select
			FROM
				-- from
				CmpFarmacyBalanceRemoveHistory CFBRH 

				left join v_EmergencyTeam ET  on (ET.EmergencyTeam_id = CFBRH.EmergencyTeam_id)

				--JOIN v_MedPersonal as MP  ON( MP.MedPersonal_id = ET.EmergencyTeam_HeadShift )

				LEFT JOIN LATERAL(

					select
						mpp.Person_Fin as Person_Fin
					from
						v_MedPersonal mpp 

					where
						mpp.MedPersonal_id = ET.EmergencyTeam_HeadShift
                    limit 1
				) MP ON TRUE
				LEFT JOIN v_CmpFarmacyBalance as CFB  ON( CFB.CmpFarmacyBalance_id = CFBRH.CmpFarmacyBalance_id )

				LEFT JOIN rls.v_Drug D  on (D.Drug_id = CFB.Drug_id)


				-- end from
			WHERE
				-- where
				CFBRH.CmpFarmacyBalance_id = :CmpFarmacyBalance_id
				-- end where
			ORDER BY
				-- order by
				CFBRH.CmpFarmacyBalanceRemoveHistory_id DESC
				-- end order by
			";
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * default desc
	 */
	function loadSmpFarmacyRegister($data) {

		$query = "
			SELECT
				CFB.CmpFarmacyBalance_id as \"CmpFarmacyBalance_id\",
				CFBAH_AD.AddDate as \"AddDate\",
				CFB.Drug_id as \"Drug_id\",
				D.DrugTorg_Name as \"DDFGT\",
				CASE WHEN (COALESCE(D.Drug_Fas,0) = 0) then RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||' '||to_char(COALESCE(D.DrugForm_Name,''))||' '||to_char(COALESCE(D.Drug_Dose,'')))

					else RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||', '||to_char(COALESCE(D.DrugForm_Name,''))||', '||to_char(COALESCE(D.Drug_Dose,''))||', №'||TO_CHAR(D.Drug_Fas))

				end as \"DrugTorg_Name\",
				D.Drug_PackName as \"Drug_PackName\",
				D.Drug_Fas as \"Drug_Fas\",
				CFB.CmpFarmacyBalance_PackRest as \"CmpFarmacyBalance_PackRest\",
				CFB.CmpFarmacyBalance_DoseRest as \"CmpFarmacyBalance_DoseRest\"
			FROM
				v_CmpFarmacyBalance CFB 

				LEFT JOIN LATERAL(

					select
						to_char(cast(CFBAH.CmpFarmacyBalanceAddHistory_AddDate as timestamp(3)), 'DD.MM.YYYY') as AddDate

					from
						v_CmpFarmacyBalanceAddHistory CFBAH 

					where
						CFB.CmpFarmacyBalance_id = CFBAH.CmpFarmacyBalance_id
					order by CFBAH.CmpFarmacyBalanceAddHistory_AddDate desc
					limit 1
				) as CFBAH_AD ON TRUE
				LEFT JOIN rls.v_Drug D  on D.Drug_id = CFB.Drug_id

			WHERE
				CFB.Lpu_id = :Lpu_id
			order by
				D.DrugTorg_Name
			";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		return $val;
	}

	/**
	 * default desc
	 */
	function saveSmpFarmacyDrug($data) {
		$procedure = '';
		$checkQuery = "
			SELECT
				CFB.CmpFarmacyBalance_id as \"CmpFarmacyBalance_id\",
				CFB.CmpFarmacyBalance_PackRest as \"CmpFarmacyBalance_PackRest\",
				CFB.CmpFarmacyBalance_DoseRest as \"CmpFarmacyBalance_DoseRest\"
			FROM
				v_CmpFarmacyBalance CFB 

			WHERE
				CFB.Drug_id = :Drug_id and CFB.Lpu_id = :Lpu_id
			";
		$checkResult = $this->db->query($checkQuery, $data);

		if ( !is_object($checkResult) ) {
			return false;
		}

		$checkResult = $checkResult->result('array');
		switch (count($checkResult)) {
			case 0:
				$procedure = 'p_CmpFarmacyBalance_ins';
				$data['CmpFarmacyBalance_id'] = null;
				$data['CmpFarmacyBalance_PackRest'] = $data['CmpFarmacyBalanceAddHistory_RashCount'];
				$data['CmpFarmacyBalance_DoseRest'] = $data['CmpFarmacyBalanceAddHistory_RashEdCount'];
			break;
			case 1:
				$procedure = 'p_CmpFarmacyBalance_upd';
				$data['CmpFarmacyBalance_id'] = $checkResult[0]['CmpFarmacyBalance_id'];
				$data['CmpFarmacyBalance_PackRest'] = $checkResult[0]['CmpFarmacyBalance_PackRest']+$data['CmpFarmacyBalanceAddHistory_RashCount'];
				$data['CmpFarmacyBalance_DoseRest'] = $checkResult[0]['CmpFarmacyBalance_DoseRest']+$data['CmpFarmacyBalanceAddHistory_RashEdCount'];

			break;
			default:
				return false;
			break;
		}

		$CmpFarmacyQuery = "
            select 
                CmpFarmacyBalance_id as \"CmpFarmacyBalance_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from " . $procedure . " (
				CmpFarmacyBalance_id := :CmpFarmacyBalance_id,
				Lpu_id := :Lpu_id,
				Drug_id := :Drug_id,
				CmpFarmacyBalance_PackRest := :CmpFarmacyBalance_PackRest,
				CmpFarmacyBalance_DoseRest := :CmpFarmacyBalance_DoseRest,
				pmUser_id := :pmUser_id
				)
		";

		$CmpFarmacyResult = $this->db->query($CmpFarmacyQuery, $data);

		if ( !is_object($CmpFarmacyResult) ) {
			return false;
		}

		$CmpFarmacyResult = $CmpFarmacyResult->result('array');

		if (strlen($CmpFarmacyResult[0]['Error_Msg'])>0) {
			return false;
		}

		$data['CmpFarmacyBalance_id'] = $CmpFarmacyResult[0]['CmpFarmacyBalance_id'];
		$data['CmpFarmacyBalanceAddHistory_id'] = null;
		$CmpFarmacyAddHistoryQuery = "
            select 
                CmpFarmacyBalanceAddHistory_id as \"CmpFarmacyBalanceAddHistory_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_CmpFarmacyBalanceAddHistory_ins (
				CmpFarmacyBalanceAddHistory_id := :CmpFarmacyBalanceAddHistory_id,
				CmpFarmacyBalanceAddHistory_DoseCount := :CmpFarmacyBalanceAddHistory_RashEdCount,
				CmpFarmacyBalanceAddHistory_PackCount := :CmpFarmacyBalanceAddHistory_RashCount,
				CmpFarmacyBalanceAddHistory_AddDate := :CmpFarmacyBalanceAddHistory_AddDate,
				CmpFarmacyBalance_id := :CmpFarmacyBalance_id,
				pmUser_id := :pmUser_id
                )
			";


		$CmpFarmacyAddHistory = $this->db->query($CmpFarmacyAddHistoryQuery, $data);

		if ( is_object($CmpFarmacyAddHistory) ) {
			return $CmpFarmacyAddHistory->result('array');
		}
		else {
			return false;
		}

	}

	/**
	 * default desc
	 */
	function removeSmpFarmacyDrug($data) {

        $DoseAndPackRest = "
				with SQLString as (
					SELECT
					    CFB.CmpFarmacyBalance_PackRest as CmpFarmacyBalance_PackRest,
					    CFB.CmpFarmacyBalance_DoseRest as CmpFarmacyBalance_DoseRest
					FROM
					    v_CmpFarmacyBalance CFB
					WHERE
					    CFB.CmpFarmacyBalance_id = :CmpFarmacyBalance_id
				)

                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from sp_executesql (
				    (select CmpFarmacyBalance_PackRest from SQLString),
				    (select CmpFarmacyBalance_DoseRest from SQLString),
				    CmpFarmacyBalance_id := CmpFarmacyBalance_id,
				    PackRest_OUT := PackRest,
				    DoseRest_OUT := DoseRest
				    )
			";


        $CmpFarmacyQuery = "
                select 
                    CmpFarmacyBalance_id as \"CmpFarmacyBalance_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_CmpFarmacyBalance_updRest (
					CmpFarmacyBalance_id := :CmpFarmacyBalance_id,
					CmpFarmacyBalance_PackRest := PackRest - :CmpFarmacyBalanceRemoveHistory_PackCount,
					CmpFarmacyBalance_DoseRest := DoseRest - :CmpFarmacyBalanceRemoveHistory_DoseCount,
					pmUser_id := :pmUser_id
					)
			";

		$this->db->trans_begin();
		$CmpFarmacyResult = $this->db->query($CmpFarmacyQuery, array(
				'CmpFarmacyBalance_id'=>$data['CmpFarmacyBalance_id'],
				'CmpFarmacyBalanceRemoveHistory_PackCount'=>$data['CmpFarmacyBalanceRemoveHistory_PackCount'],
				'CmpFarmacyBalanceRemoveHistory_DoseCount'=>$data['CmpFarmacyBalanceRemoveHistory_DoseCount'],
				'pmUser_id'=>$data['pmUser_id']
			));

		if ( !is_object($CmpFarmacyResult) ) {
			$this->db->trans_rollback();
			return false;
		}

		$CmpFarmacyResult = $CmpFarmacyResult->result('array');

		if (strlen($CmpFarmacyResult[0]['Error_Msg'])>0) {
			$this->db->trans_rollback();
			return $CmpFarmacyResult;
		}


		$CmpFarmacyRemoveHistoryQuery = "
            select
                CmpFarmacyBalanceRemoveHistory_id as \"CmpFarmacyBalanceRemoveHistory_id\",
                ErrCode as \"Error_Code\",
                ErrMessage as \"Error_Msg\"
			from p_CmpFarmacyBalanceRemoveHistory_ins (
				CmpFarmacyBalanceRemoveHistory_id := :CmpFarmacyBalanceRemoveHistory_id,
				CmpFarmacyBalanceRemoveHistory_DoseCount := :CmpFarmacyBalanceRemoveHistory_DoseCount,
				CmpFarmacyBalanceRemoveHistory_PackCount := :CmpFarmacyBalanceRemoveHistory_PackCount,
				CmpFarmacyBalance_id := :CmpFarmacyBalance_id,
				EmergencyTeam_id := :EmergencyTeam_id,
				CmpCallCard_id := :CmpCallCard_id,
				pmUser_id := :pmUser_id
				)
			";


		$CmpFarmacyRemoveHistory = $this->db->query($CmpFarmacyRemoveHistoryQuery, array(
			'CmpFarmacyBalanceRemoveHistory_id'=>null,
			'CmpFarmacyBalanceRemoveHistory_DoseCount' => $data['CmpFarmacyBalanceRemoveHistory_DoseCount'],
			'CmpFarmacyBalanceRemoveHistory_PackCount' => $data['CmpFarmacyBalanceRemoveHistory_PackCount'],
			'CmpFarmacyBalance_id' => $data['CmpFarmacyBalance_id'],
			'EmergencyTeam_id' => empty($data['EmergencyTeam_id'])?null:$data['EmergencyTeam_id'],
			'CmpCallCard_id' => empty($data['CmpCallCard_id'])?null:$data['CmpCallCard_id'],
			'pmUser_id' => $data['pmUser_id'],
		));

		if ( is_object($CmpFarmacyRemoveHistory) ) {

			$result = $CmpFarmacyRemoveHistory->result('array');
			if (strlen($result[0]['Error_Msg'])>0) {
				$this->db->trans_rollback();
				return $result;
			}

			$this->db->trans_commit();
			return $result;
		}
		else {
			$this->db->trans_rollback();
			return false;
		}
	}


	/**
	 * @return array список неформализованных адресов указанной ЛПУ
	 */
	 
	public function loadUnformalizedAddressDirectory($data){

		$filter = 'UAD.Lpu_id = :Lpu_id';
		if(!empty($data['UnformalizedAddressType_id'])){
			$filter .= ' and UAD.UnformalizedAddressType_id = :UnformalizedAddressType_id';
		}
		
		if(!empty($data['UnformalizedAddressDirectory_Name'])){
			$filter .= " and UAD.UnformalizedAddressDirectory_Name ilike '%' || :UnformalizedAddressDirectory_Name || '%'";
		}
		
		if(!empty($data['UnformalizedAddressDirectoryType_Name'])){
			$filter .= " and UAD.UnformalizedAddressType_id =:UnformalizedAddressDirectoryType_Name";
		}
		
		if(!empty($data['Lpu_aid'])){
			$filter .= " and UAD.Lpu_aid = :Lpu_aid";
		}
		
		if(!empty($data['LpuBuilding_Name'])){
			$filter .= " and UAD.LpuBuilding_id =:LpuBuilding_Name";
		}		
		
		if(!empty($data['UnformalizedAddressDirectory_lat'])){
			$filter .= " and UAD.UnformalizedAddressDirectory_lat ilike '%' || :UnformalizedAddressDirectory_lat || '%'";
		}
		
		if(!empty($data['UnformalizedAddressDirectory_lng'])){
			$filter .= " and UAD.UnformalizedAddressDirectory_lng ilike '%' || :UnformalizedAddressDirectory_lng || '%'";
		}

		if(!empty($data['UnformalizedAddressDirectory_Address'])){
			$filter .= " and COALESCE(RGN.KLRgn_FullName||' ','') 
                || COALESCE(SRGN.KLSubRgn_FullName||' ','') 
                || COALESCE('г.'||City.KLArea_Name||' ','') 
                || COALESCE(Town.KLTown_FullName||' ','')
                || COALESCE(Street.KLStreet_FullName, 'ул.'||Street.KLStreet_Name||' ','')
				|| case when UAD.UnformalizedAddressDirectory_Dom is not null AND UAD.UnformalizedAddressDirectory_Dom !=' ' then ', д.'||UAD.UnformalizedAddressDirectory_Dom else '' end ilike '%' || :UnformalizedAddressDirectory_Address||'%'";
		}		

		$query = "
			SELECT
			-- select
				UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\",
				UAD.UnformalizedAddressDirectory_lat as \"UnformalizedAddressDirectory_lat\",
				UAD.UnformalizedAddressDirectory_lng as \"UnformalizedAddressDirectory_lng\",
				UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\",
				UAD.UnformalizedAddressDirectory_Corpus as \"UnformalizedAddressDirectory_Corpus\",
				UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\",
				UAD.KLRgn_id as \"KLRgn_id\",
				UAD.KLSubRgn_id as \"KLSubRgn_id\",
				UAD.KLCity_id as \"KLCity_id\",
				UAD.KLTown_id as \"KLTown_id\",
				UAD.KLStreet_id as \"KLStreet_id\",
				UAD.LpuBuilding_id as \"LpuBuilding_id\",
				UAD.Lpu_id as \"Lpu_id\",
				UAD.Lpu_aid as \"Lpu_aid\",
				Lpu.Org_Nick as \"Org_Nick\",
				L.LpuBuilding_Name as \"LpuBuilding_Name\",
				--UAT.UnformalizedAddressType_id,
				UAT.UnformalizedAddressType_Name as \"UnformalizedAddressType_Name\",
                UAT.UnformalizedAddressType_SocrNick as \"UnformalizedAddressType_SocrNick\",
				COALESCE(RGN.KLRgn_FullName||' ','') 
                || COALESCE(SRGN.KLSubRgn_FullName||' ','') 
                || COALESCE('г.'||City.KLArea_Name||' ','') 
                || COALESCE(Town.KLTown_FullName||' ','')
                || COALESCE(Street.KLStreet_FullName, 'ул.'||Street.KLStreet_Name||' ','')
				|| case when UAD.UnformalizedAddressDirectory_Dom is not null AND UAD.UnformalizedAddressDirectory_Dom !=' ' then ', д.'||UAD.UnformalizedAddressDirectory_Dom else '' end
				|| case when UAD.UnformalizedAddressDirectory_Corpus is not null AND UAD.UnformalizedAddressDirectory_Corpus !=' ' then ', корп.'||UAD.UnformalizedAddressDirectory_Corpus else '' end
                as \"UnformalizedAddressDirectory_Address\"
			-- end select
			FROM
			-- from
				v_UnformalizedAddressDirectory UAD 

				left join v_KLRgn RGN  on RGN.KLRgn_id = UAD.KLRgn_id

				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = UAD.KLSubRgn_id

				left join v_KLArea City  on City.KLArea_id = UAD.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = UAD.KLTown_id

				left join v_KLStreet Street  on Street.KLStreet_id = UAD.KLStreet_id

				left join v_UnformalizedAddressType UAT  on UAD.UnformalizedAddressType_id = UAT.UnformalizedAddressType_id


				left join v_LpuBuilding L  on L.LpuBuilding_id = UAD.LpuBuilding_id

				left join v_Lpu Lpu  on Lpu.Lpu_id = UAD.Lpu_aid

			-- end from
			WHERE
			-- where
				". $filter ."
			-- end where
			order by
				-- order by
				UAD.UnformalizedAddressDirectory_Name
				-- end order by
			";
		//var_dump(getDebugSQL($query, $data)); exit();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		//$result = $this->db->query($query, $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Справочник типов неформализованных адресов
	 *
	 * @param array $data
	 * @return false or array
	 */
	public function loadUnformalizedAddressType( $data ) {
		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				SELECT
					UAT.UnformalizedAddressType_id as \"UnformalizedAddressType_id\",
					UAT.UnformalizedAddressType_Name as \"UnformalizedAddressType_Name\",
					UAT.UnformalizedAddressType_SocrNick as \"UnformalizedAddressType_SocrNick\"
				FROM
					dbo.v_UnformalizedAddressType as UAT
			";
		} else {
			$sql = "
				SELECT
					UAT.UnformalizedAddressType_id as \"UnformalizedAddressType_id\",
					UAT.UnformalizedAddressType_Name as \"UnformalizedAddressType_Name\",
					UAT.UnformalizedAddressType_SocrNick as \"UnformalizedAddressType_SocrNick\"
				FROM
					v_UnformalizedAddressType UAT 

			";
		}

		$query = $this->db->query( $sql );
		if ( is_object( $query ) ) {
			$result = $query->result_array();
		} else {
			$result = array();
		}

		return array(
			'succes' => sizeof( $result ) ? true : false,
			'data' => $result,
		);
	}

	/**
	 *
	 * @param type $data
	 */
	public function getUnformalizedAddressStreetKladrParams($data) {
		$queryParams = array();
		$filter = "area.KLAdr_Actual = 0";
		$filter .= " and street.KLAdr_Actual = 0";

		if ( $data['cityName']) {
			$filter .= "  and ((area.KLArea_Name = :cityName )
                	OR (socr.KLSocr_Nick||' '||area.KLArea_Name = :cityName)
                    OR (socr.KLSocr_Name||' '||area.KLArea_Name = :cityName)
                    )";

			$queryParams['cityName'] = $data['cityName'];
		}
		else return array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Не указан город / населенный пункт');

		if ( $data['streetName']) {
            $filter .= " and ((socrstreet.KLSocr_Name ||' '||street.KLStreet_Name = :streetName)
						OR (street.KLStreet_Name = :streetName)
						OR (street.KLStreet_Name ||' '||socrstreet.KLSocr_Name = :streetName)
						)
						";
			$queryParams['streetName'] = $data['streetName'];
		}

		$query = "
			SELECT
			area.KLArea_id as \"KLArea_id\",
			area.KLArea_pid as \"KLArea_pid\",
			area.KLAreaLevel_id as \"KLAreaLevel_id\",
			area.KLArea_Name as \"KLArea_Name\",
			area.KLAdr_Code as \"KLAdr_Code\",
			socr.KLSocr_Nick as \"KLSocr_Nick\",
			socr.KLSocr_Name as \"KLSocr_Name\",
			p.KLArea_Name as \"pKLArea_Name\",
			s.KLSocr_Name as \"region\",
			stat.KLAreaStat_id as \"KLAreaStat_id\",
			stat.Region_id as \"Region_id\",
			stat.KLSubRgn_id as \"KLSubRgn_id\",
            stat.KLTown_id as \"KLTown_id\",
            street.KLStreet_Name as \"KLStreet_Name\",
            street.KLStreet_id as \"KLStreet_id\",
			socrstreet.KLSocr_Nick as \"KLStreet_Nick\"
			from KLArea area 

			LEFT JOIN KLSocr socr  on (area.KLSocr_id = socr.KLSocr_id)

			LEFT JOIN KLArea p  ON ( p.KLArea_id=area.KLArea_pid )

			LEFT JOIN KLSocr s  on (p.KLSocr_id = s.KLSocr_id)

			LEFT JOIN KLAreaStat stat  on ( area.KLArea_id = stat.KLCity_id)

            LEFT JOIN KLStreet street  on (street.KLArea_id = area.KLArea_id)

            left join KLSocr socrstreet  on (socrstreet.KLSocr_id = street.KLSocr_id)

			WHERE
            	$filter
			ORDER BY
			LENGTH(area.KLArea_Name) ASC,
			socr.KLAreaType_id ASC
			,socr.KLAreaLevel_id DESC
			,area.KLAreaCentreType_id DESC
			limit 1
			";

		//var_dump(getDebugSQL($query, $queryParams));exit;

		$result = $this->db->query($query, $queryParams);

		//var_dump($result->result('array')); exit;

		if (is_object($result))
		{
			$response = array();
			$response['success'] = false;
			$response['data'] = $result->result('array');
			if(count($result->result('array'))>0){
				$response['success'] = true;
			}
			return $response;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Сохранение списка неформализованных адресов
	 */
	public function saveUnformalizedAddress($data){
		if (!isset($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}

		$addresses = json_decode($data['addresses'], true);
		if (!is_array($addresses)) {
			return array(array('success' => false, 'Error_Msg' => 'Неверные входящие параметры'));
		}

		$result = array();
		foreach ($addresses as &$address) {
			if (!isset($address['UnformalizedAddressDirectory_Name'])) {
				continue;
			}

			$checkParams = array(
				'UnformalizedAddressDirectory_Name' => $address['UnformalizedAddressDirectory_Name']
			);
			$checkFilter = "";
			if (!empty($address['UnformalizedAddressDirectory_id'])) {
				$checkParams['UnformalizedAddressDirectory_id'] = $address['UnformalizedAddressDirectory_id'];
				$checkFilter .= " and UnformalizedAddressDirectory_id <> :UnformalizedAddressDirectory_id";
			}
			$checkResp = $this->queryResult("
				select
					UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\"
				from
					v_UnformalizedAddressDirectory 

				where
					UnformalizedAddressDirectory_Name = :UnformalizedAddressDirectory_Name
					{$checkFilter}
			", $checkParams);
			if (!empty($checkResp[0]['UnformalizedAddressDirectory_id'])) {
				return array(array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Объект СМП с таким названием уже существует.'));
			}

			$procedure = !empty($address['UnformalizedAddressDirectory_id']) ? 'p_UnformalizedAddressDirectory_upd' : 'p_UnformalizedAddressDirectory_ins';

			$sqlArr = array(
				'UnformalizedAddressDirectory_id' => !empty($address['UnformalizedAddressDirectory_id']) ? $address['UnformalizedAddressDirectory_id'] : null,
				'UnformalizedAddressDirectory_Name' => $address['UnformalizedAddressDirectory_Name'],
				'UnformalizedAddressDirectory_Dom' => !empty($address['UnformalizedAddressDirectory_Dom']) ? $address['UnformalizedAddressDirectory_Dom'] : null,
				'UnformalizedAddressDirectory_Corpus' => !empty($address['UnformalizedAddressDirectory_Corpus']) ? $address['UnformalizedAddressDirectory_Corpus'] : null,
				'UnformalizedAddressDirectory_lat' => !empty($address['UnformalizedAddressDirectory_lat']) ? $address['UnformalizedAddressDirectory_lat'] : null,
				'UnformalizedAddressDirectory_lng' => !empty($address['UnformalizedAddressDirectory_lng']) ? $address['UnformalizedAddressDirectory_lng'] : null,
				'UnformalizedAddressType_id' => !empty($address['UnformalizedAddressType_id']) ? $address['UnformalizedAddressType_id'] : null,
				// Подстанция пока не сохраняется и не передается из грида. Решили позднее при необходимости дополнить это.
				'LpuBuilding_id' => !empty($address['LpuBuilding_id']) ? $address['LpuBuilding_id'] : null,
				'KLRgn_id' => !empty($address['KLRgn_id']) ? $address['KLRgn_id'] : null,
				'KLSubRgn_id' => !empty($address['KLSubRgn_id']) ? $address['KLSubRgn_id'] : null,
				'KLCity_id' => !empty($address['KLCity_id']) ? $address['KLCity_id'] : null,
				'KLTown_id' => !empty($address['KLTown_id']) ? $address['KLTown_id'] : null,
				'KLStreet_id' => !empty($address['KLStreet_id']) ? $address['KLStreet_id'] : null,
				'Lpu_id' => $data['Lpu_id'],
				'Lpu_aid' => !empty($address['Lpu_aid']) ? $address['Lpu_aid'] : null,
				'pmUser_id' => $data['pmUser_id'],
			);

			$sql = "
				select 
				    UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
				    ErrCode as \"Error_Code\",
				    ErrMessage as \"Error_Msg\";
				from " . $procedure . " (
					UnformalizedAddressDirectory_id := :UnformalizedAddressDirectory_id,
					UnformalizedAddressDirectory_Name := :UnformalizedAddressDirectory_Name,
					UnformalizedAddressDirectory_Dom := :UnformalizedAddressDirectory_Dom,
					UnformalizedAddressDirectory_Corpus := :UnformalizedAddressDirectory_Corpus,
					UnformalizedAddressDirectory_lat := :UnformalizedAddressDirectory_lat,
					UnformalizedAddressDirectory_lng := :UnformalizedAddressDirectory_lng,
					UnformalizedAddressType_id := :UnformalizedAddressType_id,
					LpuBuilding_id := :LpuBuilding_id,

					Lpu_id := :Lpu_id,
					Lpu_aid := :Lpu_aid,
					KLRgn_id := :KLRgn_id,
					KLSubRgn_id := :KLSubRgn_id,
					KLCity_id := :KLCity_id,
					KLTown_id := :KLTown_id,
					KLStreet_id := :KLStreet_id,

					pmUser_id := :pmUser_id
					)
			";


			$result = $this->db->query($sql, $sqlArr)->row_array();

			if (empty($result)) {
				return array(array('success' => false, 'Error_Code' => null, 'Error_Msg' => null ) );
			}
		}

		return array(array('success' => true, 'Error_Code' => null, 'Error_Msg' => null ) );

	}

	/**
	 * default desc
	 */
	function deleteUnformalizedAddress($data) {
		$query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_UnformalizedAddressDirectory_del (
				UnformalizedAddressDirectory_id := :UnformalizedAddressDirectory_id
				)
		";
		$result = $this->db->query($query, array(
			'UnformalizedAddressDirectory_id' => $data['UnformalizedAddressDirectory_id']
			)
		);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение типа подстанции, в которую передан талон вызова
	 * @param type $data
	 * @return type
	 */
	private function getCallCardSmpUnitType($data) {
		if (!$data['CmpCallCard_id']) {
			return array( array( 'Error_Msg' => 'Не определён обязательный параметр: Ид. талона вызова' ) );
		}
		$query = '
			SELECT
				COALESCE(SUP.SmpUnitType_id,0) as \"SmpUnitType_id\"

			FROM
				v_CmpCallCard CCC 

				left join v_SmpUnitParam SUP  on SUP.LpuBuilding_id = CCC.LpuBuilding_id

			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
			';

		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
			)
		);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function setEmergencyTeam($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}
		$CCCStatusType = $this->getCmpCallCardStatus($data);
		if ($CCCStatusType!= false && is_array($CCCStatusType) && isset($CCCStatusType[0])
			&& isset($CCCStatusType[0]['CmpCallCardStatusType_id'])
			&& in_array((int) $CCCStatusType[0]['CmpCallCardStatusType_id'], array(5, 6)) ) {
			return array( array( 'Error_Msg' => 'Вызов находится в статусе "Отказ". Назначение бригады недоступно.' ) );
		}

		$issetCardOnTeam = false;

		$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');

		if( (int) $data['EmergencyTeam_id'] == 0 ) {
			$data['EmergencyTeam_id'] = null;
			$data['EmergencyTeamStatus_id'] = null;
		}else{
			$data['EmergencyTeamStatus_id'] =  $this->EmergencyTeam_model4E -> getEmergencyTeamStatusIdByCode( 36 );
			$issetCardOnTeam =  $this->EmergencyTeam_model4E -> getCallOnEmergencyTeam($data);
		}

		if(isset($data['CmpCallType_Code']) && $data['CmpCallType_Code'] == 4 && $data['EmergencyTeamStatus_id'] == 61) { // Попутный
           $data['EmergencyTeamStatus_id'] = null;
        }

		$data['ARMType_id'] = (!empty($data['ARMType_id'])) ? $data['ARMType_id'] : null;

		$query = "
			    select 
			        CmpCallCard_id as \"CmpCallCard_id\",
			        Error_Code as \"Error_Code\",
			        Error_Message as \"Error_Msg\"
				from p_CmpCallCard_setEmergencyTeam (
					CmpCallCard_id := :CmpCallCard_id,
					EmergencyTeam_id := :EmergencyTeam_id,
					EmergencyTeamStatus_id := :EmergencyTeamStatus_id,
					pmUser_id := :pmUser_id,
					ARMType_id := :ARMType_id
					)
		";

		$result = $this->db->query($query, $data);

		//if(!$issetCardOnTeam){
			$this->EmergencyTeam_model4E -> setEmergencyTeamStatus($data);
    	//}

		//Если тип подстанции, в которую передается вызов - центральная, то сначала вызов попадает на согласование диспетчеру подстанции (#40564)

		// отправляем PUSH
		$this->load->model('CmpCallCard_model');
		$this->CmpCallCard_model->sendPushOnSetMergencyTeam(array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		// todo: чтоб работало локально нужно локально перенести таблицу SmpUnitParam
		//$smpUnitType = $this->getCallCardSmpUnitType($data);
		$data['CmpCallCardStatusType_id'] = /*($smpUnitType[0]['SmpUnitType_id']==2)?7:*/2;
		// Убираем отправку на нод, в существующем виде она не используется на Пскове
		/*
			if(defined('NODEJS_SERVER_HOSTNAME')&&defined('NODEJS_HTTPSERVER_PORT')) {
				$this->sendNodeCallCard($data);
			}
		 *
		 */

		if(!empty($data['EmergencyTeam_id'])){

			$prms = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'CmpCallCard_Tper' => $this->getCurrentDT()->format('Y-m-d H:i:s'),
				'pmUser_id' => $data['pmUser_id']
			);
			$this->changeCmpCallCardCommonParams($prms);

		}


		// Если бригада не указана, например отклонена, устанавливаем вызову статус 1 - Передано
		// Если бригада назначена, тогда статус 2 - Принято
		// #123571 Если отклоняем бригаду, значит о самом вызове уже побеспокоились при отклонении из АРМ-а СВ
		if(isset($data['typeSetStatusCCC']) && $data['typeSetStatusCCC'] == 'cancelmode') {
			return true;
		}
		else{
			$data['CmpCallCardStatusType_id'] = empty($data['EmergencyTeam_id']) ? 1 : 2;
			return $this->setStatusCmpCallCard($data);
		}

		$this -> setCmpCallCard_isTimeExceeded(array($data), $data['pmUser_id'], 1);

	}

	/**
	 * Возвращает дополнительную информацию по карте вызова
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function getAdditionalCallCardInfo( $data ){
		$sql = "
			select
				CCC.Person_id as \"Person_id\"
				,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

				,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

				,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

				,COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) as \"Person_BirthDay\"

				,to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY') as \"CmpCallCard_prmDate\"

				,CR.CmpReason_Name as \"CmpReason_Name\"
				,COALESCE(CCT.CmpCallType_Name,'') as \"CmpCallType_Name\"


				,case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end ||
				case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end ||
				case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end ||
				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Name\",

				CASE
					WHEN CCrT.CmpCallerType_id IS NOT NULL THEN 'Вызывает: ' || CCrT.CmpCallerType_Name
					WHEN CCC.CmpCallCard_Ktov IS NOT NULL THEN 'Вызывает: ' || CCC.CmpCallCard_Ktov
					ELSE ''
				END
				|| CASE WHEN CCC.CmpCallCard_Telf IS NOT NULL THEN 'Телефон: ' || CCC.CmpCallCard_Telf ELSE '' END as CallerInfo

				,COALESCE(CCC.Sex_id,0) as SexId

				,case when DATEDIFF('year',COALESCE(PS.Person_BirthDay, COALESCE(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id

				else case when DATEDIFF('minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id+1

					else AgeTypeValue.CmpCloseCardCombo_id+2 end
				end as AgeTypeValue
				,case when DATEDIFF('year',COALESCE(PS.Person_BirthDay, COALESCE(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then

					case when COALESCE(PS.Person_BirthDay, COALESCE(CCC.Person_BirthDay,0))=0 then ''

					else DATEDIFF('year',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end

				else case when DATEDIFF('minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then DATEDIFF(mm,COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())

							else DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end

				end as \"Age\"
			from
				v_CmpCallCard CCC 

				left join v_PersonState PS  on PS.Person_id = CCC.Person_id

				LEFT JOIN v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				LEFT JOIN v_CmpCallerType CCrT  on CCrT.CmpCallerType_id=CCC.CmpCallerType_id

				LEFT JOIN v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				LEFT JOIN v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				LEFT JOIN v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				LEFT JOIN v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				LEFT JOIN v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				LEFT JOIN LATERAL (

					select
						CCCC.CmpCloseCardCombo_id as CmpCloseCardCombo_id
					from
						v_CmpCloseCardCombo CCCC 

					where
						CCCC.Parent_id = 218
					order by
						CCCC.CmpCloseCardCombo_id asc
                    limit 1
				) as AgeTypeValue ON TRUE

			where
				CCC.CmpCallCard_id = :CmpCallCard_id
		";

		$query = $this->db->query( $sql, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * default desc
	 */
	function defineAccessoryGroupCmpCallCard($data) {
		// Проверяем тип арма из которого была запрошена смена статуса и состоит ли пользователь в соответствующей группе
		$user = pmAuthUser::find($_SESSION['login']);

		// Для диспетчера направлений СМП
		if ( array_key_exists( 'armtype', $data ) && $data['armtype'] == 'smpdispatchdirect' && $user->havingGroup( 'SMPDispatchDirections' ) ) {
			$query = "
				SELECT
					CASE WHEN COALESCE(CmpCallCard_IsOpen,1)=2

						then
							case
								WHEN Lpu_id IS NULL THEN
									CASE
										WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id
										WHEN CmpCallCardStatusType_id=4 THEN 3
										WHEN CmpCallCardStatusType_id=3 THEN 7
										ELSE CmpCallCardStatusType_id+3
									END
								ELSE
									CmpCallCardStatusType_id+3
								END
						else 9
					end as \"CmpGroup_id\"
				FROM
					v_CmpCallCard 

				WHERE
					CmpCallCard_id = :CmpCallCard_id
                LIMIT 1
			";
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			$cccData = $result[0];
			$outData = array(
				'success' => true,
				'CmpGroup_id' => $cccData['CmpGroup_id'],
			);
			return $outData;
			// Для диспетчера вызовов СМП
		} elseif ( array_key_exists( 'armtype', $data ) && ( ( $data['armtype'] == 'smpdispatchcall' && $user->havingGroup( 'SMPCallDispath' ) ) || ( $data['armtype'] == 'smpadmin' && $user->havingGroup( 'SMPAdmin' ) ) ) ) {
			$query = "
				SELECT
					CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\",
					Lpu_id as \"Lpu_id\",
					CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
					CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\"
				FROM
					v_CmpCallCard 

				WHERE
					CmpCallCard_id = :CmpCallCard_id
                LIMIT 1
			";
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');

			$cccData = $result[0];
			$outData = array();

			if( $cccData['CmpCallCard_IsOpen'] == 2 ) {
				if ($cccData['CmpCallCardStatusType_id'] == NULL) {
					$outData['CmpGroup_id'] = 1;
				} elseif (empty($cccData['Lpu_id']) ) {
					switch($cccData['CmpCallCardStatusType_id']) {
						case 1:
						case 2:
							$outData['CmpGroup_id'] = $cccData['CmpCallCardStatusType_id']+1;
							break;
						case 4:
							$outData['CmpGroup_id'] = 4;
							break;
						case 5:
							$outData['CmpGroup_id'] = 9;
							break;
					}
				} elseif (!empty($cccData['Lpu_id'])) {
					$outData['CmpGroup_id'] = $cccData['CmpCallCardStatusType_id']+4;
				}
			}
			else {
				$outData['CmpGroup_id'] = 10;
			}

			$outData['success'] = true;
			return $outData;
			// Для оператора ППД
		} elseif ( array_key_exists( 'armtype', $data ) && $data['armtype'] == 'slneotl' && $user->havingGroup( 'PPDMedServiceOper' ) ) {
			$query = "
				SELECT
					CASE WHEN COALESCE(CmpCallCard_IsOpen,1)=2 THEN

						/* Записи принятые в ППД */
						CASE WHEN CmpCallCard_IsReceivedInPPD=2 THEN
							CASE WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id+3 WHEN CmpCallCardStatusType_id=4 THEN 3+3 ELSE 7 END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_id становится равной ноля */
						ELSE
							CASE WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id WHEN CmpCallCardStatusType_id=4 THEN 3 ELSE 7 END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_id становится равной ноля */
						END
					ELSE 7 END as \"CmpGroup_id\"
				FROM
					v_CmpCallCard 
				WHERE
					CmpCallCard_id = :CmpCallCard_id
					AND Lpu_id IS NOT NULL
                LIMIT 1
			";
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			$cccData = $result[0];
			$outData = array(
				'success' => true,
				'CmpGroup_id' => $cccData['CmpGroup_id'],
			);
			return $outData;
			// Для всего остального
		} else {
			$query = "
				select
					CmpCallCard_IsOpen as \"CmpCallCard_IsOpen\"
					,Lpu_id as \"Lpu_id\"
					,CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
					,CmpCallCard_IsEmergency as \"CmpCallCard_IsEmergency\"
				from
					v_CmpCallCard 

				where
					CmpCallCard_id = :CmpCallCard_id
                limit 1
			";
			$result = $this->db->query($query, $data);
			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			//print_r($result);
			$cccData = $result[0];
			$outData = array();
			if( $cccData['CmpCallCard_IsOpen'] == 2 ) {
				if( in_array((int) $cccData['CmpCallCardStatusType_id'], array(1, 2, 3, 4, 5)) ) {
					switch($cccData['CmpCallCardStatusType_id']) {
						case 1:
							$outData['CmpGroup_id'] = 2;
							break;
						case 2:
							$outData['CmpGroup_id'] = 3;
							break;
						case 3:
							$outData['CmpGroup_id'] = 4;
							break;
						case 4:
							$outData['CmpGroup_id'] = 5;
							break;
						case 5:
							$outData['CmpGroup_id'] = 7;
							break;
					}
				} else { $outData['CmpGroup_id'] = 1;
				}
			} else {
				$outData['CmpGroup_id'] = 6;
			}
			$outData['success'] = true;
			return $outData;
		}
	}

	/**
	 * default desc
	 */
	function printControlTicket($data) {
		$query = "
                SELECT
                CCC.Person_SurName as \"Person_SurName\",
                CCC.Person_FirName as \"Person_FirName\",
                CCC.Person_SecName as \"Person_SecName\",
                CCC.Person_Age as \"Person_Age\",
                COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' '
				|| COALESCE(SUBSTRING(COALESCE(PS.Person_Firname, rtrim(CCC.Person_FirName) ),1,1 ), '')|| ' '
				|| COALESCE(SUBSTRING(COALESCE(PS.Person_Secname, rtrim(CCC.Person_SecName)),1,1 ), '') as \"Person_FIO\",
                case when COALESCE(PS.Sex_id, CCC.Sex_id, 0) != 0 then
                case when COALESCE(PS.Sex_id, CCC.Sex_id, 0) =1 then 'M' else
                  case when COALESCE(PS.Sex_id, CCC.Sex_id, 0) =2 then 'Ж' else null end
                end
                end as \"Sex_Name\",

               	CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay, 0) !=0 ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,dbo.tzGetDate() ) > 12 THEN

                		to_char (DATEDIFF('year',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' лет'

                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	to_char (DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,dbo.tzGetDate() ) ) || ' дн.'

                        ELSE
                        	to_char (dbo.AgeYMD(COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,dbo.tzGetDate(),2) ) || ' мес.'

                        END
                   	END
			 	ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE 'лет ' || to_char (CCC.Person_Age )
                    END
                END as \"Person_AgeText\",

                RGN.KLRgn_Name as \"KLRgn_Name\",
                COALESCE(City.KLCity_Name,Town.KLTown_Name) as \"KLCity_Name\",
                CASE WHEN COALESCE(CCC.KLStreet_id,0) > 0 THEN Street.KLStreet_Name ELSE CCC.CmpCallCard_Ulic END as \"streetName\",


                case when SecondStreet.KLStreet_FullName is not null then SecondStreet.KLStreet_Name else '' end as \"secondStreetName\",

                CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
                CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
                CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
                CCC.CmpCallCard_Room as \"CmpCallCard_Room\",
                CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
                CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
                CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
                CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\",

                Lpu.Lpu_name as \"Lpu_name\",

                case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

				end||

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
					', '||Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
				end ||
				--case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				--case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				--case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				--case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as Address_Name,

                to_char(CCC.CmpCallCard_prmDT, 4)||' '||to_char(cast(CmpCallCard_prmDT as time(6))) as CmpCallCard_prmDT,
                to_char(cast(CmpCallCard_prmDT as time(6))) as CmpCallCard_ImcomeTime,
                to_char(cast(CmpCallCard_updDT as time(6))) as CmpCallCard_OutcomeTime,
                to_char(CCC.CmpCallCard_Tper, 4)||' '||to_char(cast(CCC.CmpCallCard_Tper as time(6))) as CmpCallCard_Tper,
                DIAG.Diag_FullName as \"DiagName\",

                COALESCE(lpuHid.Lpu_Nick,'') as \"LpuHid_Nick\",

                to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY') as \"AcceptDate\",

                to_char(CCC.CmpCallCard_prmDT, 'HH24:MI:SS') as \"AcceptTime\",

                case when to_char(CCC.CmpCallCard_Tper, 'DD.MM.YYYY')!='01.01.1900' then to_char(CCC.CmpCallCard_Tper, 'YYYY-MM-DD HH24:MI:SS') else '' end as \"TransTime\",


                case when to_char(CCC.CmpCallCard_Vyez, 'DD.MM.YYYY')!='01.01.1900' then to_char(CCC.CmpCallCard_Vyez, 'YYYY-MM-DD HH24:MI:SS') else '' end as \"GoTime\",


                case when to_char(CCC.CmpCallCard_Przd, 'DD.MM.YYYY')!='01.01.1900' then to_char(CCC.CmpCallCard_Przd, 'YYYY-MM-DD HH24:MI:SS') else '' end as \"ArriveTime\",


                case when to_char(CCC.CmpCallCard_Tsta, 'DD.MM.YYYY')!='01.01.1900' then to_char(CCC.CmpCallCard_Tsta, 'YYYY-MM-DD HH24:MI:SS') else '' end as \"ToHospitalTime\",


                case when to_char(CCC.CmpCallCard_Tisp, 'DD.MM.YYYY')!='01.01.1900' then to_char(CCC.CmpCallCard_Tisp, 'YYYY-MM-DD HH24:MI:SS') else '' end as \"BackTime\",


                
                COALESCE(CPT.CmpCallPlaceType_Code,'') as \"CmpCallPlaceType_Code\",

                COALESCE(CPT.CmpCallPlaceType_Name,'') as \"CmpCallPlaceType_Name\",

                CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
                CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
                CR.CmpReason_Code as \"CmpReason_Code\",
                CR.CmpReason_Name as \"CmpReason_Name\",
                CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
                CCrT.CmpCallerType_Name as \"CmpCallerType_Name\",
                CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\",
                COALESCE(MS_CCC.MedService_Nick,'') as \"MedService_Nick\",

                case when CCC.CmpCallType_id is not null then CCT.CmpCallType_Code||' '||CCT.CmpCallType_Name else null end as CallType,
                to_char(cast(CCC.CmpCallCard_updDT as timestamp(3)), 'DD.MM.YYYY') as \"date_Prm\",

                date_part('dow',CCC.CmpCallCard_updDT)-1 as \"dayOfWeek\",
                
                ET.EmergencyTeam_Num as \"EmergencyTeam_Num\",
                COALESCE(L_ET.Lpu_Nick,'') as \"EmergencyTeam_Lpu_Nick\",

                COALESCE(MS_ET.MedService_Nick,'') as \"EmergencyTeam_MedService_Nick\",

                ET.EmergencyTeam_BaseStationNum as \"EmergencyTeam_BaseStationNum\",
                LB.LpuBuilding_Name as \"LpuBuilding_Name\",
                case when ETC.EmergencyTeamSpec_Code is not null then ETC.EmergencyTeamSpec_Code||' '||ETC.EmergencyTeamSpec_Name else null end as \"EmergencyTeamSpecInfo\",
                ET.EmergencyTeam_CarNum as \"EmergencyTeam_CarNum\",
                case when ET.EmergencyTeam_PortRadioNum is not null then '||' else '-' end as \"RadioEnabled\",
                case when ET.EmergencyTeam_HeadShift is not null then
                cast(ET.EmergencyTeam_HeadShift as varchar(10))||
                ' '||MPh1.Person_Fin else null end as \"HeadShift\",
                ET.EmergencyTeam_Assistant1 as \"EmergencyTeam_Assistant1\",
                ET.EmergencyTeam_Assistant2 as \"EmergencyTeam_Assistant2\",
                UCA.PMUser_Name as \"FeldsherAcceptName\" 

				FROM
					v_CmpCallCard CCC 

					LEFT JOIN v_EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

					LEFT JOIN v_CmpCallPlaceType CPT  on CPT.CmpCallPlaceType_id = CCC.CmpCallPlaceType_id

					LEFT JOIN v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

					LEFT JOIN v_lpu L_ET  on L_ET.Lpu_id = ET.Lpu_id

					LEFT JOIN v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

					LEFT JOIN v_KLCity City  on City.KLCity_id = CCC.KLCity_id

					LEFT JOIN v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

					LEFT JOIN v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

					LEFT JOIN v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id

					LEFT JOIN v_KLStreet SecondStreet  on 'SecondStreet.KLStreet_id' = CCC.CmpCallCard_UlicSecond

					LEFT JOIN v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id

					LEFT JOIN v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

					LEFT JOIN v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLCity_id

					left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLTown_id

					LEFT JOIN v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

					LEFT JOIN v_PersonState PS  on PS.Person_id = CCC.Person_id

					LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

					LEFT JOIN v_EmergencyTeamSpec ETC  on ETC.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id

					LEFT JOIN v_MedPersonal MPh1  ON( MPh1.MedPersonal_id=ET.EmergencyTeam_HeadShift )

					LEFT JOIN v_LpuBuilding LB  on LB.LpuBuilding_id = ET.LpuBuilding_id

					LEFT JOIN v_CmpCallerType CCrT  on CCrT.CmpCallerType_id=CCC.CmpCallerType_id

                    left join v_Lpu Lpu  on Lpu.Lpu_id = CCC.Lpu_id

					left join v_Lpu lpuHid  on CCC.Lpu_hid = lpuHid.Lpu_id


					LEFT JOIN v_Diag DIAG  on DIAG.Diag_id = CCC.Diag_uid

				    LEFT JOIN v_pmUserCache UCA  on UCA.PMUser_id = CCC.pmUser_insID


				LEFT JOIN LATERAL (

					SELECT
						MS.MedService_Nick as MedService_Nick
					FROM
						v_MedService MS 

					WHERE
						MS.LpuBuilding_id = ET.LpuBuilding_id
						AND MS.MedServiceType_id = 19
                    LIMIT 1
				) as MS_ET ON TRUE

				LEFT JOIN LATERAL (

					SELECT
						MS.MedService_Nick as MedService_Nick
					FROM
						v_MedService MS 

					WHERE
						MS.LpuBuilding_id = CCC.LpuBuilding_id
						AND MS.MedServiceType_id = 19
                    LIMIT 1
				) as MS_CCC ON TRUE

			WHERE
				CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query($query, array(
			'EmergencyTeam_id' => $data['teamId'],
			'CmpCallCard_id' => $data['callId'],
			'Lpu_id' => $data['Lpu_id']
			)
		);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}


	/**
	 * Получение списка подстанций СМП
	 *
	 * @return array
	 */
	public function loadSmpUnits($data) {
		if (empty($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}
		$filterList = '1=1';

		if( !empty($data['loadSelectSmp']) ){
			// возьмем ИД выбранных пользователем подразделений СМП
			$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
			$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
			if ( $arrayIdSelectSmp ) {
				$filterList .= " AND LB.LpuBuilding_id in (" . implode(',', $arrayIdSelectSmp) . ")";
			}
		}

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');
		if( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp')) ){
			$data["LpuBuildingType_id"] = 28;
			$filterList .= " AND LB.LpuBuildingType_id = :LpuBuildingType_id";
		}
		else{
			$data["LpuBuildingType_id"] = 27;
			$filterList .= " AND LB.Lpu_id = :Lpu_id";
			$filterList .= " AND LB.LpuBuildingType_id = :LpuBuildingType_id";
		}

		$sql = "
			SELECT DISTINCT
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\",
				LB.LpuBuilding_Code as \"LpuBuilding_Code\",
				LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				LB.Lpu_id as \"Lpu_id\"
			FROM
				v_LpuBuilding LB 

			WHERE
			".$filterList;

		return $this->db->query($sql, array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuildingType_id' => $data['LpuBuildingType_id'],
		))->result_array();
	}


	/**
	 * определяем оперативный отдел для данной подстанции
	 *
	 * @return array
	 */
	public function getOperDepartament($data){

		$params = array();

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');
		if(empty($data['LpuBuilding_id']) /* || in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp'))*/){ //Проблема отменяющими при этом условии
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0]['LpuBuilding_id'])){
				//return $this->createError(null, 'Не определена подстанция');
				//бывает что служба на верхнем уровне где нет подстанции
				$params['Lpu_id'] = $data['Lpu_id'];
				$where = ' lb.Lpu_id = :Lpu_id and COALESCE(sup.LpuBuilding_pid, 1) = 1 and sut.SmpUnitType_Code = 4';

			}
			else{
				$params['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
				$where = 'sup.LpuBuilding_id=:LpuBuilding_id';
			}
		}
		else{
			$params['LpuBuilding_id'] = $data["LpuBuilding_id"];
			$where = 'sup.LpuBuilding_id=:LpuBuilding_id';
		}

		$sql = "
			SELECT
				case when COALESCE(sup.LpuBuilding_pid, 1) != 1

					then sup.LpuBuilding_pid
					else sup.LpuBuilding_id
				end as \"LpuBuilding_pid\",
				case when COALESCE(plb.Lpu_id, 1) != 1

					then plb.Lpu_id
					else lb.Lpu_id
				end as \"Lpu_id\"
			FROM
				v_SmpUnitParam sup 

				LEFT JOIN v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id

				INNER JOIN v_LpuBuilding lb  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)

				LEFT JOIN v_LpuBuilding plb  ON(plb.LpuBuilding_id=sup.LpuBuilding_pid)

			WHERE
				{$where}
		";
		$OperDepartament = $this->db->query($sql, $params)->result_array();

		if ( isset($OperDepartament[0]) && !empty($OperDepartament[0]["LpuBuilding_pid"])) {
			 $result = $OperDepartament[0];
			 return $result;
		}
		return false;
	}

	/**
	 * Получение списка подчиненных подстанций СМП
	 *
	 * @return array
	 */
	public function loadSmpUnitsNested($data, $flagLpuBuildSelf = false){
		if (empty($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}
		$params = array();
		$filterList = array();

		if( $this->isCallCenterArm( $data ) == false ){
			$params['Lpu_id'] = $data['Lpu_id'];
			$filterList[] = 'lb.Lpu_id = :Lpu_id';
		}

		if(empty($data['LpuBuilding_id'])){
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0]['LpuBuilding_id'])){
				//return $this->createError(null, 'Не определена подстанция');
				//бывает что служба на верхнем уровне где нет подстанции
				$filterList[] = 'COALESCE(sup.LpuBuilding_pid, 1) != 1';

			}
			else{
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
			}
		}

		//все подчиненные оперативному отделу пользователя не зависимо от того, является ли пользователь сотрудником подстанции
		/*
		$filterList[] = 'MSMP.MedPersonal_id = :medpersonal_id';
		$params['medpersonal_id'] = $data['session']['medpersonal_id'];
		*/
		if(isset($data['LpuBuilding_id'])){
			$params['LpuBuilding_pid'] = $data['LpuBuilding_id'];
			$where = '(';
			if(!empty($data['showOwnedLpuBuiding'])){
				//только текущая (у ДВ)
				$where .= 'lb.LpuBuilding_id=:LpuBuilding_pid';
			}
			else{

				$operDpt = $this -> getOperDepartament($data);
				
				$where .= 'sup.LpuBuilding_pid = :LpuBuilding_pid';
				$params['LpuBuilding_pid'] = $operDpt["LpuBuilding_pid"];

			}
			
			if($flagLpuBuildSelf)
			{
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$where .= ' OR lb.LpuBuilding_id =:LpuBuilding_id';
			}

			$where .= ')';
			$filterList[] = $where;
		}
		//if( !empty($data['loadSelectSmp']) ){
			// возьмем ИД выбранных пользователем подразделений СМП
		//	$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
		//	$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
		//	if ( $arrayIdSelectSmp ) {
		//		$filterList[] = "sup.LpuBuilding_id in (" . implode(',', $arrayIdSelectSmp) . ")";
		//	}
		//}
		$sql = "
			SELECT
				DISTINCT lb.LpuBuilding_id as \"LpuBuilding_id\",
				case when lb.LpuBuilding_Name is not null then lb.LpuBuilding_Name ||' ('|| lpu.Lpu_Nick||')' else lb.LpuBuilding_Nick end as \"LpuBuilding_Name\",
				case when lb.LpuBuilding_Nick is not null then lb.LpuBuilding_Nick else lb.LpuBuilding_Name end as \"LpuBuilding_filterName\",
				lb.LpuBuilding_Name as \"LpuBuilding_fullName\",
				lb.LpuBuilding_Code as \"LpuBuilding_Code\",
				lb.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				lpu.Lpu_id as \"Lpu_id\"
			FROM
				v_SmpUnitParam sup 

				INNER JOIN v_LpuBuilding lb  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)

				LEFT JOIN v_lpu lpu  on lpu.Lpu_id = lb.Lpu_id

				left join v_MedService as MS  on MS.LpuBuilding_id = lb.LpuBuilding_id

				left join v_MedServiceMedPersonal as MSMP  on MSMP.MedService_id = MS.MedService_id

			WHERE
				" . implode(' and ', $filterList) . "
		";
		//var_dump(getDebugSql($sql, $params)); exit;
		return $this->db->query($sql, $params)->result_array();
	}
	
	/**
	 * Получение списка всех подстанций СМП оперативного отдела для пользователя
	 * место работы которого есть либо в оперативном отделе, либо в удаленной подстанции
	 * @return array
	 */
	public function loadSmpUnitsNestedALL($data, $flagLpuBuildSelf = false){
		if (empty($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}

		$operDpt = $this->getOperDepartament($data);
		
		$selected_by_the_user='';
		if( !empty($data['loadSelectSmp']) && $data['loadSelectSmp'] ){
			// подстанции выбранные пользователем для управления
			$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
			$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
			if ( $arrayIdSelectSmp ) {
				$selected_by_the_user = " AND sup.LpuBuilding_id in (" . implode(',', $arrayIdSelectSmp) . ")";
			}
		}

		$where = 'sut.SmpUnitType_Code in (2,5)
				and sup.LpuBuilding_pid = :LpuBuilding_pid';

		if (!in_array($this->getRegionNick(), array('astra', 'ufa', 'khak'))) {
			$where .= 'and exists (
					-- Рабочие места в удаленных подстанциях
					select MSMP.MedServiceMedPersonal_id as "MedServiceMedPersonal_id"
					from v_MedServiceMedPersonal MSMP 

						inner join v_MedService MS  on MS.MedService_id = MSMP.MedService_id

					where MS.LpuBuilding_id = LB.LpuBuilding_id
						and MSMP.MedPersonal_id = :medPersonal_id
					union all
					-- Рабочее место в оперативном отделе
					select MSMP.MedServiceMedPersonal_id as "MedServiceMedPersonal_id"
					from v_MedServiceMedPersonal MSMP 

						inner join v_MedService MS  on MS.MedService_id = MSMP.MedService_id

					where MS.LpuBuilding_id = :LpuBuilding_pid
                    limit 1
						--and MSMP.MedPersonal_id = :medPersonal_id
				)';
		}

		$params = array(
			'LpuBuilding_pid' => $operDpt["LpuBuilding_pid"],
			'medPersonal_id' => $data['session']['medpersonal_id']
		);	
		$sql = "
			SELECT DISTINCT
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				--case when LB.LpuBuilding_Name is not null then LB.LpuBuilding_Name ||' ('|| LPU.Lpu_Nick||')' else LB.LpuBuilding_Nick end as LpuBuilding_Name,
				case 
					when LB.LpuBuilding_Name is not null 
						then
						case
							when LPU.Lpu_Nick is not null
								then LB.LpuBuilding_Name ||' ('|| LPU.Lpu_Nick||')' 
							else
								LB.LpuBuilding_Name
						end
					else LB.LpuBuilding_Nick 
				end as \"LpuBuilding_Name\",
				case when lb.LpuBuilding_Nick is not null then lb.LpuBuilding_Nick else lb.LpuBuilding_Name end as \"LpuBuilding_filterName\",
				LB.LpuBuilding_Name as \"LpuBuilding_fullName\",
				LB.LpuBuilding_Code as \"LpuBuilding_Code\",
				LB.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				LPU.Lpu_id as \"Lpu_id\"
			FROM
				v_LpuBuilding LB 

				inner join v_SmpUnitParam sup  ON LB.LpuBuilding_id = sup.LpuBuilding_id

				inner join v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id

				LEFT JOIN v_lpu LPU  on lpu.Lpu_id = LB.Lpu_id

			WHERE		
		".$where.$selected_by_the_user;
		return $this->db->query($sql, $params)->result_array();
	}

	/**
	 * Получение списка подчиненных лпу подстанций СМП (отмеченных в настройках управления подстанциями)
	 *
	 * @return array
	 */
	public function loadLpuWithNestedSmpUnits($data){
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$settings['lpuBuildingsWorkAccess'];
			$fil = "lb.LpuBuilding_id in (";
			foreach ($settings['lpuBuildingsWorkAccess'] as &$value) {
				$fil .= $value.',';
			}
			$fil = substr($fil, 0, -1).')';
			$filter[] = $fil;
		}
		else {
			return array(array('success'=>false));
		}

		$sql = "
			SELECT DISTINCT
				lpu.Lpu_id as \"Lpu_id\",
                lpu.Lpu_Name as \"Lpu_Name\",
                lpu.Lpu_Nick as \"Lpu_Nick\"
			FROM
				v_SmpUnitParam sup 

				INNER JOIN v_LpuBuilding lb  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)

				LEFT JOIN v_lpu lpu  on lpu.Lpu_id = lb.Lpu_id

			WHERE
			".implode(" AND ", $filter)."
		";
		return $this->db->query($sql)->result_array();
	}


	/**
	 * Получение списка подстанций СМП из опций
	 *
	 * @return array
	 */
	public function loadSmpUnitsFromOptions($data, $withMedServises = false){
		//проверка опций
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);
		$filter = array('(1 = 1)');

		/*
		треш какой-то переписать
		if( !empty($data['loadSelectSmp']) ){
			//выбранные при входе в АРМ ДП подстанции
			$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
			$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
			if ( $arrayIdSelectSmp ) {
				$filter[] = " lb.LpuBuilding_id in (" . implode(',', $arrayIdSelectSmp) . ")";
			}
		} elseif ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$settings['lpuBuildingsWorkAccess'];

			$fil = "lb.LpuBuilding_id in (";
			foreach ($settings['lpuBuildingsWorkAccess'] as &$value) {
				$fil .= $value.',';
			}
			$fil = substr($fil, 0, -1).')';
			$filter[] = $fil;
		} else {
			return array(array('success'=>false));
		}
		*/
		/*
		if (empty($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}
		*/

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) && $settings['lpuBuildingsWorkAccess'][0]) {
			$settings['lpuBuildingsWorkAccess'];

			//@todo проверка на $settings['lpuBuildingsWorkAccess'];

			$fil = "lb.LpuBuilding_id in (";
			foreach ($settings['lpuBuildingsWorkAccess'] as &$value) {
				$fil .= $value.',';
			}
			$fil = substr($fil, 0, -1).')';
			$filter[] = $fil;
		}else{
			return array(array('success' => false, 'Error_Msg' => 'Не настроены подстанции для управления'));
		}
		
		if (!empty($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}

		//проблема вызвана дублированием значений в комбобоксе подразделение смп в форме шаблона нарядов
		//для метода getGeoserviceTransportListWithCoords нужно получать настройки служб на подстанции
		$MedServiceData = "";
		$MedServiceJoin = "";
		if($withMedServises){
			$MedServiceData = "MS.MedService_id as \"MedService_id\",";
			$MedServiceJoin = "LEFT JOIN v_MedService MS  on MS.LpuBuilding_id = lb.LpuBuilding_id";

		}
		
		$sql = "
			SELECT DISTINCT
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				{$MedServiceData}
				case when lb.LpuBuilding_Name is not null then lb.LpuBuilding_Name ||' ('|| lpu.Lpu_Nick||')' else lb.LpuBuilding_Nick end as \"LpuBuilding_Name\",
				lb.LpuBuilding_Code as \"LpuBuilding_Code\",
				lb.LpuBuilding_Nick as \"LpuBuilding_Nick\",
				lpu.Lpu_id as \"Lpu_id\"
			FROM
				v_SmpUnitParam sup 

				INNER JOIN v_LpuBuilding lb  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)

				{$MedServiceJoin}
				LEFT JOIN v_lpu lpu  on lpu.Lpu_id = lb.Lpu_id

			WHERE
			".implode(" AND ", $filter)."
		";

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Получение списка мо подстанций подчиненных опер отделу
	 *
	 * @return array
	 */
	public function loadLpuWithNestedLpuBuildings($data){
		if (empty($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр идентификатор ЛПУ'));
		}
		$params = array();
		$filterList = array();

		if( $this->isCallCenterArm( $data ) == false ){
			$params['Lpu_id'] = $data['Lpu_id'];
			$filterList[] = 'lb.Lpu_id = :Lpu_id';
		}

		if(empty($data['LpuBuilding_id'])){
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0]['LpuBuilding_id'])){
				//return $this->createError(null, 'Не определена подстанция');
				//бывает что служба на верхнем уровне где нет подстанции
				$filterList[] = 'COALESCE(sup.LpuBuilding_pid, 1) != 1';

			}
			else{
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
			}
		}

		if(isset($data['LpuBuilding_id'])){
			$params['LpuBuilding_pid'] = $data['LpuBuilding_id'];
			$where = '(';
			if(!empty($data['showOwnedLpuBuiding'])){
				$where .= 'lb.LpuBuilding_id=:LpuBuilding_pid';
			}
			else{

				$operDpt = $this -> getOperDepartament($data);

				$where .= 'sup.LpuBuilding_pid = :LpuBuilding_pid';
				$params['LpuBuilding_pid'] = $operDpt["LpuBuilding_pid"];
			}


			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$where .= ' OR lb.LpuBuilding_id =:LpuBuilding_id';


			$where .= ')';
			$filterList[] = $where;
		}

		$sql = "
			SELECT
				DISTINCT
				lpu.Lpu_id as \"Lpu_id\",
				lpu.Lpu_Nick as \"Lpu_Nick\"
			FROM
				v_SmpUnitParam sup 

				INNER JOIN v_LpuBuilding lb  ON(lb.LpuBuilding_id=sup.LpuBuilding_id)

				LEFT JOIN v_lpu lpu  on lpu.Lpu_id = lb.Lpu_id

				left join v_MedService as MS  on MS.LpuBuilding_id = lb.LpuBuilding_id

				left join v_MedServiceMedPersonal as MSMP  on MSMP.MedService_id = MS.MedService_id

			WHERE
				" . implode(' and ', $filterList) . "
		";
		//var_dump(getDebugSql($sql, $params)); exit;
		$res = $this->db->query($sql, $params)->result_array();
		
		if(count($res) == 0 && $_SESSION['region']['nick'] == 'perm'){
			// тогда все МО пользователя #159408
			$sql = "
				select distinct L.Lpu_id as \"Lpu_id\", L.Lpu_Nick as \"Lpu_Nick\"
				from
					v_pmUserCacheOrg UO 

					inner join v_Lpu_all L  on L.Org_id = UO.Org_id

				where	
					UO.pmUserCache_id = ?
			";
			$res = $this->db->query($sql,array($data['pmUser_id']))->result('array');
		}
		return array('items'=>$res);
	}

	/**
	 * Загрузка лпу с подчиненными подстанциями
	 */
	public function loadNestedLpuBuildings() {
		$query ="select Distinct 
				LB.Lpu_id as \"Lpu_id\",
				L.Lpu_Nick as \"Lpu_Nick\",
				LB.LpuBuilding_id as \"LpuBuilding_id\",
				LB.LpuBuilding_Name as \"LpuBuilding_Name\"
			from v_SmpUnitParam SUP 

			inner join v_lpubuilding LB  on LB.LpuBuilding_id = SUP.LpuBuilding_id and LpuBuilding_endDate is null

			inner join v_Lpu L  on L.Lpu_id = LB.Lpu_id

			inner join v_SmpUnitType SUT  on SUT.SmpUnitType_id = SUP.SmpUnitType_id and SUT.SmpUnitType_Code in (1,2,3,5)";

		return $this->queryResult($query,[]);
	}
	/**
	 * Загрузка комбика "общее состояние"
	 */
	public function loadCmpCommonStateCombo() {
		$query ="select DISTINCT
                    CCS.CmpCommonState_id as \"CmpCommonState_id\",
                    CCS.CmpCommonState_Code as \"CmpCommonState_Code\",
                    CCS.CmpCommonState_Name as \"CmpCommonState_Name\"
                from 
	              CmpCommonState CCS ";

		return $this->queryResult($query,[]);
	}

	/**
	 * Получение списка ранжированных по правилу профилей бригад для вызова
	 * @return boolean
	 */
	public function getEmergencyTeamPriorityFromReason($data) {
		$filter = 'CUAPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority is not null';
		$queryParams = array();

		$rule = $this->getCallUrgencyAndProfile($data);

		if (!isset($rule[0])) {
			return array(array('success'=>false,'Error_Msg'=>''));
		}

		$query = "
			SELECT
				CUAPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as \"ProfilePriority\",
				CUAPSRSP.EmergencyTeamSpec_id as \"EmergencyTeamSpec_id\"
			FROM v_CmpUrgencyAndProfileStandartRefSpecPriority CUAPSRSP 

			WHERE
				CUAPSRSP.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id
				AND CUAPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority is not null
			";

		$result = $this->db->query($query, array(
			'CmpUrgencyAndProfileStandart_id'=>$rule[0]['CmpUrgencyAndProfileStandart_id']
		));
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Получение списка карт с профильными приоритетами для бригады
	 * @return boolean
	 */
	public function getCallsPriorityFromReason($data){
		if ( !isset( $data[ 'CmpCardsArray' ] ) || !$data[ 'CmpCardsArray' ] ) {
			return array( array( 'success' => false, 'Error_Msg' => 'Не задан обязательный параметр: карты вызовов' ) );
		}

		if ( !isset( $data[ 'EmergencyTeamSpec_id' ] ) || !$data[ 'EmergencyTeamSpec_id' ] ) {
			return array( array( 'success' => false, 'Error_Msg' => 'Не задан обязательный параметр: профиль бригад' ) );
		}

		//$queryParams['CmpCardsArray'] = $data['CmpCardsArray'];
		$queryParams['EmergencyTeamSpec_id'] = $data['EmergencyTeamSpec_id'];
		/*@to_do доработать запрос чтобы выбирал записи по возрастам правильно
		 пример: если возраст пациента 8, то выбирался только интервал 0-14
		*/
		$query = "
			SELECT DISTINCT
				CCC.CmpCallCard_id as \"CmpCallCard_id\",
				--CUAPS.CmpReason_id,
				CCC.Person_Age as \"Person_Age\",
				CUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf as \"CmpUrgencyAndProfileStandart_UntilAgeOf\",
				(COALESCE(CUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150)) as \"calcAge\",

				--CUAPSRF.CmpCallPlaceType_id,
				CUAPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as \"ProfilePriority\"
				--CUAPSRSP.EmergencyTeamSpec_id,
				--CUAPS.CmpUrgencyAndProfileStandart_Urgency
			FROM
				v_CmpUrgencyAndProfileStandartRefSpecPriority CUAPSRSP 

				LEFT JOIN v_CmpUrgencyAndProfileStandart CUAPS  on CUAPSRSP.CmpUrgencyAndProfileStandart_id = CUAPS.CmpUrgencyAndProfileStandart_id

				LEFT JOIN v_CmpUrgencyAndProfileStandartRefPlace CUAPSRF  on CUAPSRSP.CmpUrgencyAndProfileStandart_id = CUAPSRF.CmpUrgencyAndProfileStandart_id

				LEFT JOIN v_CmpCallCard CCC  on CUAPS.CmpReason_id = CCC.CmpReason_id

				and (  (COALESCE(CUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) > CCC.Person_Age)

				and ( (COALESCE(CUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) - CCC.Person_Age)>0 )

				)
			WHERE
				CUAPSRSP.EmergencyTeamSpec_id = :EmergencyTeamSpec_id
				AND CUAPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority is not null
				AND CCC.CmpCallCard_id in (".$data['CmpCardsArray'].")
				and CUAPSRF.CmpCallPlaceType_id = CCC.CmpCallPlaceType_id

			GROUP BY
				CCC.CmpCallCard_id,
				CUAPS.CmpReason_id,
				CCC.Person_Age,
				CUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf,
				CUAPSRF.CmpCallPlaceType_id,
				CUAPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,
				CUAPSRSP.EmergencyTeamSpec_id,
				CUAPS.CmpUrgencyAndProfileStandart_Urgency
			ORDER BY
				calcAge ASC,
				CCC.Person_Age DESC
            limit 5
			";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Установка времени ускорения вызова
	 */
	public function setCmpCallCardBoostTime($data) {

		if ( !empty($data['CmpCallCard_id']) ) {
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}
		if ( !empty($data['pmUser_id']) ) {
			$queryParams['pmUser_id'] = $data['pmUser_id'];
		}

		$query = "
                select 
                    CmpCallCard_id as \"CmpCallCard_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_CmpCallCard_setBoostTime (
					CmpCallCard_id := :CmpCallCard_id,
                    CmpCallCard_BoostTime := dbo.tzGetDate(),
					pmUser_id := :pmUser_id
					)
		";

		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Сохранение временных полей в карту вызова
	 * @return boolean
	 */
	public function saveCmpCallCardTimes($data) {
		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));
		$params = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Tper' => !empty($data['CmpCallCard_Tper'])?$data['CmpCallCard_Tper']:null,
			'CmpCallCard_Vyez' => !empty($data['CmpCallCard_Vyez'])?$data['CmpCallCard_Vyez']:null,
			'CmpCallCard_Przd' => !empty($data['CmpCallCard_Przd'])?$data['CmpCallCard_Przd']:null,
			'CmpCallCard_Tgsp' => !empty($data['CmpCallCard_Tgsp'])?$data['CmpCallCard_Tgsp']:null,
			'CmpCallCard_Tsta' => !empty($data['CmpCallCard_Tsta'])?$data['CmpCallCard_Tsta']:null,
			'CmpCallCard_Tvzv' => !empty($data['CmpCallCard_Tvzv'])?$data['CmpCallCard_Tvzv']:null,
			'CmpCallCard_Tisp' =>!empty($data['CmpCallCard_Tisp'])?$data['CmpCallCard_Tisp']:null,
			'CmpCallCard_HospitalizedTime' => !empty($data['CmpCallCard_HospitalizedTime'])?$data['CmpCallCard_HospitalizedTime']:null,
			'CmpCallCard_IsPoli' => !empty($data['CmpCallCard_IsPoli'])?$data['CmpCallCard_IsPoli']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		$queryPars = '';

		foreach ($params as $k => $v) {
			$queryPars .= $k . ' := :'.$k . ','."\n";
		}

		$CmpCallCardNums = $this->getFirstRowFromQuery("
			select CmpCallCard_Numv as \"CmpCallCard_Numv\", CmpCallCard_Ngod as \"CmpCallCard_Ngod\", Lpu_id as \"Lpu_id\"
			from v_CmpCallCard 

			where CmpCallCard_id = :CmpCallCard_id
			limit 1
		", $params);


		if(!empty($data['CmpCallCard_prmDT']) && (empty($CmpCallCardNums['CmpCallCard_Numv']) || empty($CmpCallCardNums['CmpCallCard_Ngod']))){
			$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
			$nums = $this->CmpCallCard_model->getCmpCallCardNumber(array(
					'Lpu_id' => $CmpCallCardNums['Lpu_id'],
					'CmpCallCard_prmDT' => $data['CmpCallCard_prmDT']
				)
			);

			$prms = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'CmpCallCard_Numv' => $nums[0]["CmpCallCard_Numv"],
				'CmpCallCard_Ngod' => $nums[0]["CmpCallCard_Ngod"],
				'CmpCallCard_prmDT' => $data['CmpCallCard_prmDT'],
				'pmUser_id' => $data['pmUser_id']
			);
			$this->changeCmpCallCardCommonParams($prms);
			if($dolog)$this->textlog->add('setDefferedCall :'.$data['CmpCallCard_id'].' / '.$nums[0]["CmpCallCard_Numv"].' / '.$nums[0]["CmpCallCard_Ngod"].'/'. $data['CmpCallCard_prmDT'] . '/lpu_id=' . $CmpCallCardNums['Lpu_id']);

			$existenceParams = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'Day_num' => $nums[0]["CmpCallCard_Numv"],
				'Year_num' => $nums[0]["CmpCallCard_Ngod"],
				'AcceptTime' => $data['CmpCallCard_prmDT'],
				'Lpu_id' => $CmpCallCardNums['Lpu_id']
			);
			$nums = $this->CmpCallCard_model->existenceNumbersDayYear($existenceParams);
			if(is_array($nums) && ($nums['existenceNumbersDay'] || $nums['existenceNumbersYear'])){
				$updateParams = array(
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'CmpCallCard_Numv' => $nums['nextNumberDay'],
					'CmpCallCard_Ngod' => $nums['nextNumberYear'],
					'pmUser_id' => $data['pmUser_id']
				);
				$this->swUpdate('CmpCallCard', $updateParams, false);
				if($dolog)$this->textlog->add('setDefferedCall update:'.$data['CmpCallCard_id'].' / '.$nums['nextNumberDay'].' / '.$nums['nextNumberYear']);
				$prms['CmpCallCard_Numv'] = $nums['nextNumberDay'];
				$prms['CmpCallCard_Ngod'] = $nums['nextNumberYear'];
			}

			$IsSMPServer = $this->config->item('IsSMPServer');
			$IsLocalSMP = $this->config->item('IsLocalSMP');

			//Установка номеров отложенного на основной БД
			if (($IsLocalSMP === true || $IsSMPServer === true)) {
				if (
					defined('STOMPMQ_MESSAGE_ENABLE')
					&& defined('STOMPMQ_MESSAGE_ENABLE')
					&& STOMPMQ_MESSAGE_ENABLE === TRUE
					&& $_SESSION['region']['nick'] != 'ufa'
				){
					// отправляем карту СМП в основную БД через очередь ActiveMQ
					$this->load->model('Replicator_model');
					$this->Replicator_model->sendRecordToActiveMQ(array(
						'table' => 'CmpCallCard',
						'type' => 'update',
						'keyParam' => 'CmpCallCard_id',
						'keyValue' => $data["CmpCallCard_id"]
					));
				}else{


					//в ручном режиме
					unset($this->db);
					$this->load->database('main');
					//сейчас мы на дефолтной базе
					$this->changeCmpCallCardCommonParams($prms);

					unset($this->db);
					$this->load->database();
				}
			}
		}

		$query = '
            select 
                Error_Code as "Error_Code",
                Error_Message as "Error_Msg"
			from p_CmpCallCard_setDataTime (
				' . $queryPars . '
				)
		';

		$result = $this->queryResult($query, $params);
		if (!$this->isSuccessful($result)) {
			return $result;
		}

		$CmpCloseCard_id = $this->getFirstResultFromQuery("
			select CmpCloseCard_id as \"CmpCloseCard_id\"
			from {$this->schema}.v_CmpCloseCard 

			where CmpCallCard_id = :CmpCallCard_id
			limit 1
		", $params);
		if ($CmpCloseCard_id) {
			$params = array(
				'CmpCloseCard_id' => $CmpCloseCard_id,
				'TransTime' => $data['CmpCallCard_Tper'],
				'GoTime' => $data['CmpCallCard_Vyez'],
				'ArriveTime' => $data['CmpCallCard_Przd'],
				'TransportTime' => $data['CmpCallCard_Tgsp'],
				//'CmpCallCard_Tsta' => $data['CmpCallCard_Tsta'],
				//'CmpCallCard_Tvzv' => $data['CmpCallCard_Tvzv'],
				'EndTime' => $data['CmpCallCard_Tisp'],
				'ToHospitalTime' => $data['CmpCallCard_HospitalizedTime'],
				'pmUser_id' => $data['pmUser_id']
			);
			$resp = $this->swUpdate($this->schema.'.CmpCloseCard', $params, false);
		}

		return $result;
	}
	
	/**
	 * Сохранение адреса, комментария и временных полей в карту вызова
	 * @return boolean
	 */
	public function saveShortCmpCallCard($data) {
		/*		
		$params = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Tper' => $data['CmpCallCard_Tper'],
			'CmpCallCard_Vyez' => $data['CmpCallCard_Vyez'],
			'CmpCallCard_Przd' => $data['CmpCallCard_Przd'],
			'CmpCallCard_Tgsp' => $data['CmpCallCard_Tgsp'],
			'CmpCallCard_Tsta' => $data['CmpCallCard_Tsta'],
			'CmpCallCard_Tvzv' => $data['CmpCallCard_Tvzv'],
			'CmpCallCard_Tisp' => $data['CmpCallCard_Tisp'],
			'CmpCallCard_HospitalizedTime' => $data['CmpCallCard_HospitalizedTime'],
			'CmpCallCard_IsPoli' => $data['CmpCallCard_IsPoli'],
			'pmUser_id' => $data['pmUser_id'],
			'UnformalizedAddressDirectory_id' => $data['UnformalizedAddressDirectory_id'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Korp' => $data['CmpCallCard_Korp'],
			'CmpCallCard_Kvar' => $data['CmpCallCard_Kvar'],
			'CmpCallCard_Podz' => $data['CmpCallCard_Podz'],
			'CmpCallCard_Etaj' => $data['CmpCallCard_Etaj'],
			'CmpCallCard_Kodp' => $data['CmpCallCard_Kodp'],
			'CmpCallPlaceType_id' => $data['CmpCallPlaceType_id'],
			'KLRgn_id' => $data['KLRgn_id'],
			'KLSubRgn_id' => $data['KLSubRgn_id'],
			'KLCity_id' => $data['KLCity_id'] ,
			'KLTown_id' => $data['KLTown_id'],
			'KLStreet_id' => $data['KLStreet_id']
		);

		if ( isset($data['CmpCallCard_prmDT']) && !empty($data['CmpCallCard_prmDT']) ) {
			$params['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDT'];
		};
		*/

		if (!empty($data['CmpCallCardStatusType_id'])) {

			//идентифицируем пациента
			$data['Person_id'] = $this->checkUnknownPerson($data);

			/* определяем степень срочности */
			$Ufilter = '1=1';
			$UqueryParams = array();

			if (!empty($data['CmpReason_id'])) {
				$Ufilter .= " and CCCUAPS.CmpReason_id = :CmpReason_id";
				$UqueryParams['CmpReason_id'] = $data['CmpReason_id'];
			}
			if (!empty($data['Person_Age'])) {
				$Ufilter .= " and ((CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf > :Person_Age) or (CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf is null))";
				$UqueryParams['Person_Age'] = $data['Person_Age'];
			}
			if (!empty($data['CmpCallPlaceType_id'])) {
				$Ufilter .= " and CUAPSRP.CmpCallPlaceType_id = :CmpCallPlaceType_id";
				$UqueryParams['CmpCallPlaceType_id'] = $data['CmpCallPlaceType_id'];
			} else {
				$Ufilter .= " and CUAPSRP.CmpCallPlaceType_id is null";
			}

			$Uquery = "
					SELECT
						CCCUAPS.cmpurgencyandprofilestandart_id as \"cmpurgencyandprofilestandart_id\",
                        cmpreason_id as \"cmpreason_id\",
                        lpu_id as \"lpu_id\",
                        cmpurgencyandprofilestandart_urgency as \"cmpurgencyandprofilestandart_urgency\",
                        cmpurgencyandprofilestandart_untilageof as \"cmpurgencyandprofilestandart_untilageof\",
                        CCCUAPS.pmuser_insid as \"pmuser_insid\",
                        CCCUAPS.pmuser_updid as \"pmuser_updid\",
                        cmpurgencyandprofilestandart_insdt as \"cmpurgencyandprofilestandart_insdt\",
                        cmpurgencyandprofilestandart_upddt as \"cmpurgencyandprofilestandart_upddt\",
                        cmpcallcardacceptor_id as \"cmpcallcardacceptor_id\",
                        cmpurgencyandprofilestandart_headdoctorobserv as \"cmpurgencyandprofilestandart_headdoctorobserv\",
                        cmpurgencyandprofilestandart_multivictims as \"cmpurgencyandprofilestandart_multivictims\",
                        CCCUAPS.cmpurgencyandprofilestandart_guid as \"cmpurgencyandprofilestandart_guid\"
					FROM
						v_CmpUrgencyAndProfileStandart as CCCUAPS 

						LEFT JOIN v_CmpUrgencyAndProfileStandartRefPlace CUAPSRP  on CUAPSRP.CmpUrgencyAndProfileStandart_id = CCCUAPS.CmpUrgencyAndProfileStandart_id

					WHERE
						$Ufilter
				";

			$Uresult = $this->db->query($Uquery, $UqueryParams);

			if (is_object($Uresult)) {
				$res = $Uresult->result('array');
				if (isset($res[0]['CmpUrgencyAndProfileStandart_Urgency'])) {
					$urgency = $res[0]['CmpUrgencyAndProfileStandart_Urgency'];
					if (isset($urgency) && $urgency > 0) {
						$data['CmpCallCard_Urgency'] = $urgency;
					}
				}
			} else {
				return false;
			}
			/* определили срочность */

			$params = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'CmpCallType_id' => $data['CmpCallType_id'],
				'CmpCallCard_IsExtra' => $data['CmpCallCard_IsExtra'],
				'CmpCallCard_Urgency' => $data['CmpCallCard_Urgency'],
				'CmpReason_id' => $data['CmpReason_id'],
				'LpuBuilding_id' => $data['LpuBuilding_id'],
				'MedService_id' => $data['MedService_id'],
				'CmpCallCard_IsPassSSMP' => $data['CmpCallCard_IsPassSSMP'],
				'Lpu_smpid' => $data['Lpu_smpid'],
				'Lpu_ppdid' => $data['Lpu_ppdid'],
				'pmUser_id' => $data['pmUser_id']
			);

			if ($data['CallType'] == 'double') {
				//получаем настройки оперативного отдела
				$OperDepartamentOptions = $this->getOperDepartamentOptions($data);

				//если  в настройках оперативного отдела  дублирующие вызовы являются вызовами, требующими решения старшего врача
				if (isset($OperDepartamentOptions["LpuBuilding_IsCallDouble"]) && $OperDepartamentOptions["LpuBuilding_IsCallDouble"] == "true") {
					$data['CmpCallCardStatusType_id'] = 18; // "Решение страшего врача"
				} else {
					$data['CmpCallCardStatusType_id'] = 16; // "Дубль"
					$params['CmpCallCard_rid'] = $data['CmpCallCard_rid'];
				}
			}

			$this->setStatusCmpCallCard(array(
				"CmpCallCard_id" => $data["CmpCallCard_id"],
				"CmpCallCardStatusType_id" => $data['CmpCallCardStatusType_id'],
				"CmpCallCardStatus_Comment" => '',
				"CmpReason_id" => $data['CmpReason_id'],
				"pmUser_id" => $data["pmUser_id"]
			));


			$resUpdate = $this->swUpdate('CmpCallCard', $params);
			if (!empty($resUpdate['Error_Msg']))
				return $resUpdate;
		}


		$paramsName = array(
			'CmpCallCard_id',
			'CmpCallCard_Tper',
			'CmpCallCard_Vyez',
			'CmpCallCard_Przd',
			'CmpCallCard_Tgsp',
			'CmpCallCard_Tsta',
			'CmpCallCard_Tvzv',
			'CmpCallCard_Tisp',
			'CmpCallCard_Telf',
			'CmpCallCard_HospitalizedTime',
			'CmpCallCard_IsPoli',
			'Lpu_hid',
			'pmUser_id',
			'UnformalizedAddressDirectory_id',
			'CmpCallCard_Comm',
			'CmpCallCard_Dom',
			'CmpCallCard_Korp',
			'CmpCallCard_Kvar',
			'CmpCallCard_Podz',
			'CmpCallCard_Etaj',
			'CmpCallCard_Kodp',
			'CmpCallPlaceType_id',
			'KLRgn_id',
			'KLSubRgn_id',
			'KLCity_id',
			'KLTown_id',
			'KLStreet_id',
			'CmpCallCard_UlicSecond',
			'CmpCallCard_prmDT'
		);
		$params = array();
		foreach ($paramsName as $value) {
			$params[$value] = (isset($data[$value]) && !empty($data[$value])) ? $data[$value] : null;
		}

		$queryPars = '';

		foreach ($params as $k => $v) {
			$queryPars .= $k . ' := :'.$k . ','."\n";
		}

		$query = '
            select 
                Error_Code as "Error_Code", 
                Error_Message as "Error_Msg"
			from p_CmpCallCard_setAdressDataTime (
				'.$queryPars.'
				)
		';
		
		$result = $this->queryResult($query, $params);
		return $result;
	}

	/**
	 * Установка повода вторичного вызова
	 */
	public function setCmpCallCardSecondReason($data) {

		if ( !empty($data['CmpCallCard_id']) ) {
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}
		if ( !empty($data['pmUser_id']) ) {
			$queryParams['pmUser_id'] = $data['pmUser_id'];
		}
		if ( !empty($data['CmpCallCard_secondReason']) ) {
			$queryParams['CmpCallCard_secondReason'] = $data['CmpCallCard_secondReason'];
		}

		$query = "
                select 
                    CmpCallCard_id as \"CmpCallCard_id\",
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_CmpCallCard_setSecondReason (
					CmpCallCard_id := :CmpCallCard_id,
                    CmpSecondReason_id := :CmpCallCard_secondReason,
					pmUser_id := :pmUser_id
					)
		";

		//var_dump(getDebugSql($query, $queryParams)); exit();
		$result = $this->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Получение срочности и профиля вызова
	 */
	public function getCallUrgencyAndProfile($data){

		if ( empty($data['CmpReason_id']) ) {
			return array(array('success'=>false,'Error_Msg'=>'Не указан обязательный параметр: Ид. повода вызова'));
		}

		if (!isset($data['CmpCallPlaceType_id']) &&	!isset($data['FlagArmWithoutPlaceType']))

		{
			return array(array('success'=>false,'Error_Msg'=>'Не указан обязательный параметр: Ид. типа места вызова2'));
		}

		if ( !isset($data['Person_Age']) ) {
			return array(array('success'=>false,'Error_Msg'=>'Не указан обязательный параметр: Возраст пациента'));
		}

		$where = '';

		// Входят ли поводы в НП
		$query = "
			SELECT
				'' as \"CmpUrgencyAndProfileStandart_id\",
				'' as \"EmergencyTeamSpec_Code\",
				'' as \"EmergencyTeamSpec_Name\",
				'' as \"CmpUrgencyAndProfileStandart_Urgency\",
				'' as \"CmpCallCardAcceptor_id\",
				'' as \"CmpCallCardAcceptor_SysNick\"
			FROM
				v_CmpReason R 

			WHERE
				to_char (COALESCE(R.CmpReason_Code,0)) in ('313', '53', '298', '326', '231', '343', '232', '233', '155', '329', '321', '314', '344', '319', '36', '114', '40', '156', '277', '88', '153', '127', '121', '89', '305', '327', '56', '273', '102', '176', '351', '307', '338', '52', '339', '331', '191', '345', '323', '337', '302', '341', '310')

				AND R.CmpReason_id = :CmpReason_id
            LIMIT 1
		";
		$preresult = $this->db->query($query,array(
			'CmpReason_id'=>$data['CmpReason_id'],
		));
		$preval = $preresult->result('array');

		$queryParams = array(
			'CmpReason_id'=>$data['CmpReason_id'],
			'Person_Age'=>$data['Person_Age'],
			'Lpu_id'=>$data['Lpu_id']
		);
		if ( !empty($data['CmpCallPlaceType_id']) ) {
			$where = " AND CUPSRP.CmpCallPlaceType_id = :CmpCallPlaceType_id ";
			$queryParams['CmpCallPlaceType_id'] = $data['CmpCallPlaceType_id'];
		}

		$operDpt = $this->getOperDepartament($data);
		$OperDepartamentOptions = $this-> getOperDepartamentOptions($data);

		if($OperDepartamentOptions && $OperDepartamentOptions["LpuBuildingType_id"] == 28){
			$queryParams['Lpu_id'] = $OperDepartamentOptions["Lpu_id"];
		};

		if ( isset( $operDpt["LpuBuilding_pid"] ) ){
			$queryParams["LpuBuilding_pid"] = $operDpt["LpuBuilding_pid"];
		}
		else{
			return false;
		}


		$query = "
			SELECT
				CUPS.CmpUrgencyAndProfileStandart_id as \"CmpUrgencyAndProfileStandart_id\",
				ETS.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\",
				ETS.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\",
				CUPS.CmpUrgencyAndProfileStandart_Urgency as \"CmpUrgencyAndProfileStandart_Urgency\",
				CUPS.CmpCallCardAcceptor_id as \"CmpCallCardAcceptor_id\",
				CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv as \"HeadDoctorObservReason\",
				CCCA.CmpCallCardAcceptor_SysNick as \"CmpCallCardAcceptor_SysNick\",
				COALESCE(operDPT.LpuBuilding_IsCallReason, 1) as \"LpuBuilding_IsCallReason\"
			FROM
				v_CmpUrgencyAndProfileStandart CUPS 

				left join v_CmpUrgencyAndProfileStandartRefPlace CUPSRP  on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRP.CmpUrgencyAndProfileStandart_id

				left join v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP  on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRSP.CmpUrgencyAndProfileStandart_id

				left join v_EmergencyTeamSpec ETS  on ETS.EmergencyTeamSpec_id = CUPSRSP.EmergencyTeamSpec_id

				left join v_CmpCallCardAcceptor CCCA  on CCCA.CmpCallCardAcceptor_id = CUPS.CmpCallCardAcceptor_id

				LEFT JOIN LATERAL (

					select 
					    server_id as server_id,
                        lpubuilding_id as lpubuilding_id,
                        lpu_id as lpu_id,
                        lpubuildingtype_id as lpubuildingtype_id,
                        address_id as address_id,
                        lpubuilding_code as lpubuilding_code,
                        lpubuilding_name as lpubuilding_name,
                        lpubuilding_nick as lpubuilding_nick,
                        lpubuilding_descr as lpubuilding_descr,
                        lpubuilding_worktime as lpubuilding_worktime,
                        lpubuilding_routeplan as lpubuilding_routeplan,
                        pmuser_insid as pmuser_insid,
                        pmuser_updid as pmuser_updid,
                        lpubuilding_insdt as lpubuilding_insdt,
                        lpubuilding_upddt as lpubuilding_upddt,
                        lpubuilding_guid as lpubuilding_guid,
                        lpubuilding_begdate as lpubuilding_begdate,
                        lpubuilding_enddate as lpubuilding_enddate,
                        paddress_id as paddress_id,
                        lpulevel_id as lpulevel_id,
                        lpulevel_cid as lpulevel_cid,
                        lpubuilding_isexport as lpubuilding_isexport,
                        lpubuilding_cmpstationcode as lpubuilding_cmpstationcode,
                        lpubuilding_cmpsubstationcode as lpubuilding_cmpsubstationcode,
                        lpubuilding_latitude as lpubuilding_latitude,
                        lpubuilding_longitude as lpubuilding_longitude,
                        lpubuilding_setdefaultaddresscity as lpubuilding_setdefaultaddresscity,
                        lpubuilding_iscallcancel as lpubuilding_iscallcancel,
                        lpubuilding_iscalldouble as lpubuilding_iscalldouble,
                        lpubuilding_iscallspecteam as lpubuilding_iscallspecteam,
                        lpubuilding_isemergencyteamdelay as lpubuilding_isemergencyteamdelay,
                        lpubuilding_iscallreason as lpubuilding_iscallreason,
                        lpubuilding_isprint as lpubuilding_isprint,
                        lpubuilding_isusingmicrophone as lpubuilding_isusingmicrophone,
                        lpubuilding_iswithoutbalance as lpubuilding_iswithoutbalance,
                        lpubuildingsmstype_id as lpubuildingsmstype_id,
                        lpubuilding_issms as lpubuilding_issms,
                        lpufilial_id as lpufilial_id,
                        lpubuilding_isaidscenter as lpubuilding_isaidscenter,
                        lpubuilding_isdenycallanswerdoc as lpubuilding_isdenycallanswerdoc
                    from v_LpuBuilding lb where lb.LpuBuilding_id = :LpuBuilding_pid limit 1
				) as operDPT ON TRUE
			WHERE
				CUPS.CmpReason_id = :CmpReason_id
				AND (COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) >= :Person_Age)

				{$where}
				AND CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority  = 1
				AND COALESCE(CUPS.Lpu_id,0) in (0,:Lpu_id)

			ORDER BY
				COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150),

                COALESCE(CUPS.Lpu_id,0) DESC
            LIMIT 1
			";

		//var_dump(getDebugSQL($query, $queryParams)); exit;
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query,$queryParams);

		$val = $result->result('array');
		if (!is_object($result) || count($val) == 0) {
			if (!is_object($preresult) || count($preval) == 1) {
				return $preval;
			} else {
				return false;
			}
		} else {
			if (!is_object($preresult) || count($preval) == 1) {
				$val[0]['CmpUrgencyAndProfileStandart_Urgency'] = $preval[0]['CmpUrgencyAndProfileStandart_Urgency'];
			}
			return $val;
		}

	}
	/**
	 * Данные вызовы необходимы для отображения количества вызовов в группе, основная информация о которых не подгружается
	 * используется в АРМ-е диспетчера подстанции в гриде вызовов
	 */
	public function getCountGroupingByStatusType_id( $data, $filter, $sqlArr, $show_112 = false) {
		$groups = array(1 => 1, 3 => 3, 4 => 4, 5 => 5, 6=> 6, 7 => 7 ); // Полный список групп (всего пять) Принятые, На обслуживании, Исполненные, Закрытые, Отменены
		$by112service = "";
		if($show_112){
			$groups[2] = 2;
			$by112service = " WHEN CCC.CmpCallCardStatusType_id = 20 THEN 2 ";
		}
		$arrayCountGrouping = array();
		$sql = "select
					CC.CmpGroupTable_id as \"CmpGroupTable_id\"
					,SUM(CC.countCards) as \"countCardByGroup\"

				from (
						select
							CCC.CmpCallCardStatusType_id as CmpCallCardStatusType_id
							,count(CCC.CmpCallCard_id) as countCards
							,case
								WHEN CCC.CmpCallCardStatusType_id IN (0,1) or CCC.CmpCallCardStatusType_id is null THEN 1	-- поступившие вызовы
								".$by112service." 	-- поступившие из 112 вызовы
								WHEN CCC.CmpCallCardStatusType_id IN(2,3) THEN 3 		-- вызовы на исполнении
								WHEN CCC.CmpCallCardStatusType_id = 4 THEN 4 		-- вызовы исполненные
								WHEN CCC.CmpCallCardStatusType_id = 19 THEN 7		-- отложены
								WHEN CCC.CmpCallCardStatusType_id = 6 THEN 5		-- вызовы закрытые
								WHEN CCC.CmpCallCardStatusType_id = 5 THEN 6		-- отменены
							end
							as CmpGroupTable_id
						from
							v_CmpCallCard CCC 

						left join v_MedService MSnmp  on MSnmp.MedService_id = CCC.MedService_id and MSnmp.MedServiceType_id in (18)

						where
							COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

							AND ".implode(" AND ", $filter)."
						group by CmpCallCardStatusType_id
				) CC
				GROUP BY CC.CmpGroupTable_id
				ORDER BY CC.CmpGroupTable_id";

		//var_dump(getDebugSQL($sql, $sqlArr));
		$query = $this->db->query( $sql, $sqlArr );


		if ( is_object( $query ) ) {
			$arrayCountGrouping = $query->result_array();
			foreach($arrayCountGrouping as $group)
				if(in_array($group['CmpGroupTable_id'], $groups))
					unset($groups[$group['CmpGroupTable_id']]);
			foreach($groups as $group)
				$arrayCountGrouping[] = array('CmpGroupTable_id' => $group, 'countCardByGroup' => 0);


			return $arrayCountGrouping;
		}
		return false;
	}
	/**
	 * Диспетчер подстанции
	 */
	public function loadSMPDispatchStationWorkPlace( $data ) {
		$filter = array();
		$join = array();
		$withJoinCCC = array();
		$sqlArr = array();
		$array_count = array();
		$lpuBuildingID = array();
		$lpuBuildingsWorkAccess = array();
		$group_table_case = '';
		$timesBlock = '';
		$show_112 = false;
		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		$LpuBuildingBySessionData = $this->getLpuBuildingBySessionData($data);

		$data['SmpUnitType_Code'] = !empty($LpuBuildingBySessionData[0]['SmpUnitType_Code']) ? $LpuBuildingBySessionData[0]['SmpUnitType_Code'] : null;

		// переход в табличный вид, а также подгрузка закрытых и отмененных вызовов
		$other_mode = (isset($data['mode']) && in_array($data['mode'],array('table','cancel','closed')));

		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		}

		$regionNick = $_SESSION['region']['nick'];

		// Получение состояния только для своей ЛПУ
		if ( !in_array($regionNick, array('ufa', 'krym', 'kz', 'perm', 'ekb', 'astra', 'komi')) ) {
			$filter[] = "CCC.Lpu_id = :Lpu_id";
		}

		$filter[] = "COALESCE(CCC.CmpCallCard_IsReceivedInPPD, 1) != 2";

		// Скрываем вызовы с поводом "Решение старшего врача"
		$reason_array = $this->queryResult("SELECT CmpReason_id as \"CmpReason_id\" FROM v_CmpReason  WHERE CmpReason_Code in ('02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999')", array());


		if ( $reason_array !== false && is_array($reason_array) && count($reason_array) > 0 ) {
			$reasons = array();

			foreach($reason_array as $reason){
				$reasons[] = $reason['CmpReason_id'];
			}

			if ( count($reasons) > 0 ) {
				$filter[] = "COALESCE(CCC.CmpReason_id, 0) NOT IN (" . implode(',', $reasons) . ")";

			}
		}

		$sqlArr['Lpu_id'] = $data['session']['lpu_id'];

		$operDpt = $this->getOperDepartament($data);

		$this->load->model('LpuStructure_model', 'LpuStructure');
		$operDptParams = $this->LpuStructure->getLpuBuildingData(array("LpuBuilding_id"=>$operDpt["LpuBuilding_pid"]));

		$countCallsOnTeam = ",null as countcallsOnTeam";
		if (isset($operDptParams[0]['SmpUnitParam_IsShowCallCount']) && $operDptParams[0]['SmpUnitParam_IsShowCallCount'] == 'true') {
			$countCallsOnTeam = ',callsOnTeam.countCalls as countcallsOnTeam';
		}

		// В Пскове отображаем у диспетчера подстанций вызовы переданные диспетчеру направлений
		switch ( $regionNick ) {
			case 'pskov':
				$group_case = "
					case when CCC.CmpCallCardStatusType_id in (1,7)
						then 1
						else case when CCC.CmpCallCardStatusType_id = 1
							then 2
							else case when CCC.CmpCallCardStatusType_id = 2
								then
									case when (CCC.CmpCallCardStatusType_id = 1) OR (COALESCE(activeEvent.EmergencyTeamStatus_Code,0) not in (5,13) AND  CCC.CmpCallCard_IsOpen != 1 )

										then 2
										else 3
									end
								else 4
							end
						end
					end
				";

				if ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
					$lpuBuildingQuery = "
					SELECT
						COALESCE(MS.LpuBuilding_id,0) as \"LpuBuilding_id\"

					FROM
						v_MedService MS 

					WHERE
						MS.MedService_id = :MedService_id
					";
					$lpuBuildingResult = $this->db->query( $lpuBuildingQuery, array(
						'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
					) );
					if ( is_object( $lpuBuildingResult ) ) {
						$lpuBuildingResult = $lpuBuildingResult->result( 'array' );
						if ( isset( $lpuBuildingResult[ 0 ] ) && (!empty( $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ] )) ) {
							$filter[] = "CCC.LpuBuilding_id = :LpuBuilding_id";
							$sqlArr[ 'LpuBuilding_id' ] = $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
						}
					}
				}
				break;

			case 'buryatiya':
			case 'astra':
			case 'ekb':
			case 'krym':
			case 'kz':
			case 'perm':
			case 'penza':
			case 'ufa':
			case 'komi':
			case 'kareliya':
			case 'khak':
				$group_table_case = "
					,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

						then
							case
								WHEN CCC.CmpCallCardStatusType_id IN(0,1,3) or CCC.CmpCallCardStatusType_id is null THEN 1	-- поступившие вызовы in (null) не работает
								WHEN CCC.CmpCallCardStatusType_id = 20 THEN 2	-- поступившие из 112
								WHEN CCC.CmpCallCardStatusType_id IN(2) THEN 3 		-- вызовы на исполнении
								WHEN CCC.CmpCallCardStatusType_id = 4 THEN 4		-- вызовы исполненные
								WHEN CCC.CmpCallCardStatusType_id = 6 THEN 5 		-- вызовы закрытые
								WHEN CCC.CmpCallCardStatusType_id = 5 THEN 6 		-- отменены
								WHEN CCC.CmpCallCardStatusType_id = 19 THEN 7 		-- отложены
								WHEN CCC.CmpCallCardStatusType_id IN (21,22) THEN 0    	-- решение ДП
							end
						else 5 -- отменены
					end
					as CmpGroupTable_id
				";

				$EmergencyTeamStatus_Codes = "5,13";
				
				if ( $regionNick == 'perm' ) {
					$EmergencyTeamStatus_Codes = "13,19";
				}
				
				$group_case = "
					CASE WHEN CCC.CmpCallCardStatusType_id in (1,7)
						THEN 1
						ELSE
							CASE WHEN CCC.CmpCallCardStatusType_id = 2
								THEN
									CASE WHEN (COALESCE(activeEvent.EmergencyTeamStatus_Code,0) NOT IN ({$EmergencyTeamStatus_Codes}) AND CCC.CmpCallCard_IsOpen != 1 )

										THEN 2
										ELSE
											CASE WHEN (COALESCE(CCLC.CmpCloseCard_id, 0) > 0)

												THEN 4
												ELSE 3
											END
									END
								ELSE
									CASE WHEN CCC.CmpCallCardStatusType_id = 4
										THEN 3
										ELSE
											CASE WHEN CCC.CmpCallCardStatusType_id IN (21, 22)
												THEN 0
												ELSE
													CASE WHEN CCC.CmpCallCardStatusType_id = 19
														THEN 3
														ELSE 2
													END

											END
									END
							END
						END
				";
				
				// получаем таймеры на выполнение вызова СМП, а они у ОперОтдела

				if ( isset( $operDpt["LpuBuilding_pid"] ) ){

					$join[] = "left join v_SmpUnitTimes SUT  on SUT.LpuBuilding_id = " . $operDpt["LpuBuilding_pid"];

					/*
					$timesBlock = "
						,CASE WHEN ((SUT.minTimeSMP*60 - DATEDIFF(s, CCC.CmpCallCard_insDT, @getdate))<0 AND CCC.CmpCallCardStatusType_id=1)
							THEN 'true'
							ELSE 'false'
						END AS breakLimitMinTimeSMP

						,CASE WHEN( (SUT.maxTimeSMP*60 < ( DATEDIFF(s,CCC.CmpCallCard_insDT,@getdate) ) ) AND (CCC.CmpCallCardStatusType_id=2) )
							THEN 'true'
							ELSE 'false'
						END AS breakLimitMaxTimeSMP

						,SUT.minTimeSMP
						,SUT.maxTimeSMP
						,SUT.maxTimeSMP*60 as Maxtime60
					";
					*/

					$ArrivalTimeET = "CASE WHEN ( ( COALESCE(SUT.ServiceTimeET, 40)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END";
					$minResponseTimeET = "CASE WHEN ( ( COALESCE(SUT.minResponseTimeET, 0.25)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END";
					$maxResponseTimeET = "CASE WHEN ( ( COALESCE(SUT.maxResponseTimeET, 2)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END";
					if (in_array($regionNick, array('ufa'))) {
						$ArrivalTimeET = "CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
							--в форме неотложной помощи
							THEN CASE WHEN ( ( COALESCE(SUT.ArrivalTimeETNMP, 20)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
							--для вызовов 'выезд на вызов'
							ELSE CASE WHEN ( ( COALESCE(SUT.ArrivalTimeET, 20)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
							END";
						$minResponseTimeET = "CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
							--в форме неотложной помощи
							THEN CASE WHEN ( ( COALESCE(SUT.minResponseTimeETNMP, 0.25)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
							--для вызовов 'назначена бригада'
							ELSE CASE WHEN ( ( COALESCE(SUT.minResponseTimeET, 0.25)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
							END";
						$maxResponseTimeET = "CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
							--в форме неотложной помощи
							THEN CASE WHEN ( ( COALESCE(SUT.maxResponseTimeETNMP, 2)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
							--для вызовов 'принят бригадой'
							ELSE CASE WHEN ( ( COALESCE(SUT.maxResponseTimeET, 2)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
							END";
					}
					$timesBlock = "
						--расчет нормативного времени статусов, кроме отмененных вызовов
						,CASE WHEN (CCC.CmpCallCardStatusType_id <> 5) THEN
							CASE WHEN (activeEvent.CmpCallCardEventType_Code = 1) THEN
								--для вызовов 'принят диспетчером
								CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
									--в форме неотложной помощи
									THEN CASE WHEN ( ( COALESCE(SUT.minResponseTimeNMP, 0)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
								--в форме экстренной помощи
								ELSE CASE WHEN ( ( COALESCE(SUT.minTimeSMP, 0)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
								END
							ELSE
								CASE WHEN (activeEvent.CmpCallCardEventType_Code = 4) THEN
									".$minResponseTimeET."
								ELSE
									CASE WHEN (activeEvent.CmpCallCardEventType_Code = 5) THEN
										".$maxResponseTimeET."
									ELSE
										CASE WHEN (activeEvent.CmpCallCardEventType_Code = 7) THEN
											".$ArrivalTimeET."
										ELSE
											CASE WHEN (activeEvent.CmpCallCardEventType_Code = 8) THEN
												--для вызовов 'приезд на вызов'
												CASE WHEN ( ( COALESCE(SUT.ServiceTimeET, 40)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
											ELSE
												CASE WHEN (activeEvent.CmpCallCardEventType_Code in (9,11)) THEN
													--для вызовов 'начало госпитализации' и 'приезд в мо'
													CASE WHEN ( ( COALESCE(SUT.DispatchTimeET, 15)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
												ELSE 'false'
												END
											END
										END
									END
								END
							END
						END	as timeEventBreak
					";
				}

				if ( count( $lpuBuildingsWorkAccess) > 0 ) {
					if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
					// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
					// Значения LpuBuilding_id несколько раз подставляются.Потому для начала занесем в один массив все значения ($lpuBuildingID). 
					/*
					$lpuFilter ="CCC.LpuBuilding_id in (";
					foreach ($lpuBuildingsWorkAccess as &$value) {
						$lpuFilter .= $value.',';
					}
					$filter[] = substr($lpuFilter, 0, -1).')';
					*/
					$lpuBuildingID = array_merge($lpuBuildingID, $lpuBuildingsWorkAccess);
				}
				elseif ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {


					// Отображаем только те вызовы, которые переданы на эту подстанцию (#38949)
					// Добавлено: Если не Уфа Пермь или Крым. Так как там, может быть другая подстанция которая удаленная.
					if (!in_array($regionNick, array('ufa', 'krym', 'kz', 'perm', 'ekb', 'astra', 'penza', 'komi')) )
					{
						
						$filter[] = "MS.MedService_id = :MedService_id";
						$join[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

						$withJoinCCC[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

						
						$sqlArr[ 'MedService_id' ] = $data[ 'session' ][ 'CurMedService_id' ];
					}
					//$join[] = "left join v_SmpUnitTimes SUT  on (SUT.LpuBuilding_id in (:LpuBuilding_id))";


				} else {
					return array( array( 'success' => false, 'Error_Msg' => 'Не установлен идентификатор службы' ) );
				}
				break;

			default:
				$group_case = "
					CASE WHEN CCC.CmpCallCardStatusType_id in (1,7)
						THEN 1
						ELSE
							CASE WHEN CCC.CmpCallCardStatusType_id = 2
								THEN
									CASE WHEN (COALESCE(activeEvent.EmergencyTeamStatus_Code,0) not in (5,13) AND CCC.CmpCallCard_IsOpen != 1 )

										THEN 2
										ELSE
											CASE WHEN (COALESCE(CCLC.CmpCloseCard_id, 0) > 0)

												THEN 4
												ELSE 3
											END
									END
								ELSE
									CASE WHEN CCC.CmpCallCardStatusType_id = 4
										THEN 3
										ELSE 2
									END
							END
						END
				";
				

				if ( count( $lpuBuildingsWorkAccess) ) {
					if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
					// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
					// Значения LpuBuilding_id несколько раз подставляются.Потому для начала занесем в один массив все значения ($lpuBuildingID). 
					/*
					$lpuFilter ="CCC.LpuBuilding_id in (";
					foreach ($lpuBuildingsWorkAccess as &$value) {
						$lpuFilter .= $value.',';
					}
					$filter[] = substr($lpuFilter, 0, -1).')';
					*/
					$lpuBuildingID = array_merge($lpuBuildingID, $lpuBuildingsWorkAccess);
				}
				elseif ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
					// Отображаем только те вызовы, которые переданы на эту подстанцию (#38949)
					$filter[] = "MS.MedService_id = :MedService_id";
					$join[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

					$withJoinCCC[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

					
					$sqlArr[ 'MedService_id' ] = $data[ 'session' ][ 'CurMedService_id' ];

				}
				else {
					return array( array( 'success' => false, 'Error_Msg' => 'Не установлен идентификатор службы' ) );
				}
			break;
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			$filter[] = "((cast(CCC.CmpCallCard_prmDT as date) >= :begDate and cast(CCC.CmpCallCard_prmDT as date) <= :endDate) or CCC.CmpCallCardStatusType_id = 19)";
			$sqlArr['begDate'] = $data['begDate'];
			$sqlArr['endDate'] = $data['endDate'];
		}
		else {
			$filter[] = "((DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 24) or CCC.CmpCallCardStatusType_id = 19)";
		}

		// Отображение карт указанных статусов, еще один статус указан в запросе
		$CCCStatusTypeIds = array(1,2,3,19);

		$this->load->model("Options_model", "opmodel");
		$o = $this->opmodel->getOptionsGlobals($data);
		$g_options = $o['globals'];

		if(!empty($g_options["smp_show_112_indispstation"]) && !in_array($CurArmType, array('dispnmp', 'dispdirnmp'))  ){
			$CCCStatusTypeIds[] = 20;
			$show_112 = true;
		}

		if($data['SmpUnitType_Code'] != 2){
			$CCCStatusTypeIds[] = 21;
		}
		if($data['SmpUnitType_Code'] == 5){
			$CCCStatusTypeIds[] = 22;
		}



		if ( isset($data['mode']) ) {
			switch ( $data['mode'] ) {
				case 'cancel':
					$CCCStatusTypeIds[] = 5; // дополнительно подгружаем отмененные вызовы
					break;
				case 'closed':
					$CCCStatusTypeIds[] = 6; // дополнительно подгружаем закрытые вызовы
					break;
			}

		}

		if ( in_array($regionNick, array('ufa', 'krym', 'kz', 'perm', 'ekb', 'astra', 'komi')) ) {
			$smpUnitsNested = $this->loadSmpUnitsNested($data, true);
		}
		else {
			$smpUnitsNested = $this->loadSmpUnitsNested($data);
		}

		if ( !empty($smpUnitsNested) ) {
			// Значения LpuBuilding_id несколько раз подставляются.Потому, для начала, занесем в один массив все значения ($lpuBuildingID).
			/*
			$fil = "CCC.LpuBuilding_id in (";
			foreach ($smpUnitsNested as &$value) {
				$fil .= $value['LpuBuilding_id'].',';
			}
			}			
			$fil = substr($fil, 0, -1).')';
			$filter[] = $fil;
			 */
			foreach ( $smpUnitsNested as $value ) {
				//Отображаем только вызовы подстанций, которые были выбраны пользователем при входе в АРМ в форме «Выбор подстанций для управления».
				if(in_array($value['LpuBuilding_id'],$lpuBuildingsWorkAccess)){
					$lpuBuildingID[] = $value['LpuBuilding_id'];
				}

			}
		}
		else {
			return $this->createError(null, 'Не определена подстанция');
		}

		if ( count($lpuBuildingID) > 0 ) {
			$buildingIdUnic = array_unique($lpuBuildingID);
			$strBuildID = implode(", ", $buildingIdUnic);
			if ( in_array($CurArmType, array('dispnmp', 'dispdirnmp')) ) {
				$filter[] = "(MSnmp.LpuBuilding_id in (" . $strBuildID . ") OR CCC.LpuBuilding_id in (" . $strBuildID . "))";
			}else
			{
				if(!empty($g_options["smp_is_all_lpubuilding_with112"]) && $g_options["smp_is_all_lpubuilding_with112"] == 2){
					$filter[] = "(CCC.LpuBuilding_id in (" . $strBuildID . ") OR (CCC.LpuBuilding_id IS NULL AND CCC.CmpCallCardStatusType_id=20))";
				}
				else
					$filter[] = "CCC.LpuBuilding_id in (" . $strBuildID . ")";
			}
		}

		//определяем lpu_building_id - это значение нам понадобится позже
		if ( empty($data['LpuBuilding_id']) ) {
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);

			if ( empty($lpuBuilding[0]['LpuBuilding_id']) ) {
				return $this->createError(null, 'Не определена подстанция');
			}
			else {
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];

			}
		}

		$sqlArr['LpuBuilding_id'] = $data['LpuBuilding_id'];
		$showUnresultCalls = "";

		if ( $other_mode ) // количество закрытых и отмененных вызовов необходимо лишь для табличного вида АРМ-а
		{
			$CCCStatusTypeIds[] = 4; //обслуженные только в табличном виде (refs #113386)
			$CCCStatusTypeIds[] = 5;
			$CCCStatusTypeIds[] = 6;
			$filter_for_grouping = $filter;
			$filter_for_grouping[] = "(CCC.CmpCallCardStatusType_id IN (" . implode(',', $CCCStatusTypeIds) . ") OR CCC.CmpCallCardStatusType_id IS NULL )";
			$array_count = $this->getCountGroupingByStatusType_id($data, $filter_for_grouping, $sqlArr, $show_112);
		}else{
			if ( in_array($CurArmType, array('dispnmp', 'dispdirnmp')) ) {
				$showUnresultCalls = "OR (RES.CmpPPDResult_Code in (5,6,7,8,9,10,22) and CCC.CmpCallCardStatusType_id IN (21))";
				$withJoinCCC[] = "left join CmpPPDResult RES  on RES.CmpPPDResult_id = CCC.CmpPPDResult_id";

			}

			if ( in_array($regionNick, array('perm')) ) {
				$getStstusIds = $CCCStatusTypeIds;
				/*тестовый эксперимент*/
				$getStstusIds[] = 4; //обслуженные только в табличном виде (refs #113386)
				$getStstusIds[] = 5;
				$getStstusIds[] = 6;
				$filter_for_grouping = $filter;
				$filter_for_grouping[] = "(CCC.CmpCallCardStatusType_id IN (" . implode(',', $getStstusIds) . ") OR CCC.CmpCallCardStatusType_id IS NULL )";
				$array_count = $this->getCountGroupingByStatusType_id($data, $filter_for_grouping, $sqlArr, $show_112);
			}

		}

		$filter[] = "(CCC.CmpCallCardStatusType_id IN (" . implode(',', $CCCStatusTypeIds) . ") OR CCC.CmpCallCardStatusType_id IS NULL {$showUnresultCalls})";

		$soundSetting = '';
		$isSendCall = '';
		$isCallControll = '';
		$isDenyCall = '';
		$isNoTrans = '';
		if ( isset( $operDpt["LpuBuilding_pid"] ) ){
			$sqlArr['LpuBuilding_pid'] = $operDpt["LpuBuilding_pid"];
			$join[] = "left join lateral(select * from v_SmpUnitParam where LpuBuilding_id = :LpuBuilding_pid order by SmpUnitParam_id desc limit 1) as SUP on true";
			$join[] = "left join v_LpuBuilding pLB on pLB.LpuBuilding_id = :LpuBuilding_pid";
			//Настройки звуковых оповещений оперативного отдела
			$soundSetting = ",SUP.SmpUnitParam_IsSignalBeg as \"IsSignalBeg\"";
			//Разрешение передачи между подстанциями
			$isSendCall = ',SUP.SmpUnitParam_IsSendCall as "IsSendCall"';
			$isNoTrans = ',SUP.SmpUnitParam_IsNoTransOther as "IsNoTrans"';
			$isCallControll = ',SUP.SmpUnitParam_IsCallControll as "IsCallControll"';
			$isDenyCall = ',COALESCE(SUP.SmpUnitParam_IsDenyCallAnswerDisp,1) as "SmpUnitParam_IsDenyCallAnswerDisp"';
			$isDenyCall .= ',COALESCE(pLB.LpuBuilding_IsDenyCallAnswerDoc,1) as "LpuBuilding_IsDenyCallAnswerDoc"';
		}

		//открыта форма из нмп
		if ( in_array($CurArmType, array('dispnmp', 'dispdirnmp')) ) {
			$withJoinCCC[] = "left join v_MedService MSnmp  on MSnmp.MedService_id = CCC.MedService_id and MSnmp.MedServiceType_id in (18)";
		}else{
			$filter[] = "CCC.Lpu_ppdid IS NULL";
		}

		$sql = "
			-- addit with
			with CmpCallCardArray as (
				select CmpCallCard_id as CmpCallCard_id, Lpu_ppdid as Lpu_ppdid
				from v_CmpCallCard CCC 

					" . implode(" ", $withJoinCCC) . "
				where
					" . implode(" and ", $filter) . "
			),
			
			activeEventArray as (
				select
					CCCE.CmpCallCard_id as CmpCallCard_id,
					CCCET.CmpCallCardEventType_Name as CmpCallCardEventType_Name,
					CCCET.CmpCallCardEventType_Code as CmpCallCardEventType_Code,
					CCCE.CmpCallCardEvent_updDT as CmpCallCardEvent_updDT,
					ETS.EmergencyTeamStatus_id as EmergencyTeamStatus_id,
					ETS.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
					ETS.EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
				from
					v_CmpCallCardEvent CCCE 

					inner join CmpCallCardArray CCCA on CCCA.CmpCallCard_id = CCCE.CmpCallCard_id
					LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

					LEFT JOIN v_EmergencyTeamStatusHistory ETSH  on CCCE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

					LEFT JOIN v_EmergencyTeamStatus ETS  on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id

				where
					CCCET.CmpCallCardEventType_IsKeyEvent = 2
			)
			-- end addit with

			SELECT
				-- select
				 CCC.CmpCallCard_id as \"CmpCallCard_id\"
				 ,CCC.CmpCallCard_sid as \"CmpCallCard_sid\"
				 ,CCLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				--,CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
				,CCC.Sex_id as \"Sex_id\"
				,CCC.LpuBuilding_id as \"LpuBuilding_id\"
				,LB.LpuBuilding_Code as \"LpuBuilding_Code\"
				,COALESCE(LB.LpuBuilding_Nick, LB.LpuBuilding_Name) as \"LpuBuilding_Nick\"
				,CCC.CmpReason_id as \"CmpReason_id\"
				,case when to_char (COALESCE(CR.CmpReason_Code,'0')) in ('313', '53', '298', '326', '231', '343', '232', '233', '155', '329', '321', '314', '344', '319', '36', '114', '40', '156', '277', '88', '153', '127', '121', '89', '305', '327', '56', '273', '102', '176', '351', '307', '338', '52', '339', '331', '191', '345', '323', '337', '302', '341', '310') then 'НП' else '' end as \"Urgency\"

				,to_char (CCC.CmpCallCard_prmDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDateFormat\"
				,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') as \"CmpCallCard_prmDateStr\"

				,to_char (CCC.CmpCallCard_PlanDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_PlanDT\"
				,to_char (CCC.CmpCallCard_FactDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_FactDT\"
				,(case when CCC.CmpCallCard_FactDT > CCC.CmpCallCard_PlanDT then '1' else '' end) as \"isLate\"
				,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,CCCrid.CmpCallCard_Numv as \"ridNum\"
				,COALESCE(CCC.Person_SurName, PS.Person_Surname, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, PS.Person_Firname, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, PS.Person_Secname, '') as \"Person_FIO\"
				,to_char (COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), 'DD Mon YYYY HH24:MI:SS') as \"Person_Birthday\"

				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"


				,CCC.CmpSecondReason_id as \"CmpSecondReason_id\"
				,COALESCE(CSecondR.CmpReason_Code || '. ', '') || CSecondR.CmpReason_Name as \"CmpSecondReason_Name\"
				,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) is not null ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) > 12 THEN

                		to_char (DATEDIFF('year',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' лет'

                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	to_char (DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) ) || ' дн. '

                        ELSE
                        	to_char (DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' мес.'

                        END
                   	END
                 ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE cast(CCC.Person_Age as varchar) || ' лет'
                    END
                 END
                 as \"personAgeText\"
				,COALESCE(CCC.Person_Age, dbo.Age2(PS.Person_Birthday, dbo.tzGetDate())) as \"Person_Age\"

				,RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"

				,CCT.CmpCallType_Name as \"CmpCallType_clearName\"
				,CCT.CmpCallType_Code as \"CmpCallType_Code\"
				,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
				,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
				,activeEvent.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\"
				,ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\"
				,ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
				,COALESCE(CCC.CmpCallCard_Urgency, 99) as \"CmpCallCard_Urgency\"
				,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
				,CCC.Person_IsUnknown as \"Person_IsUnknown\"

				--,LOWER( COALESCE(City.KLSocr_Nick, Town.KLSocr_Nick, RGNCity.KLSocr_Nick) )||'. ' ||
				--COALESCE(City.KLCity_Name, Town.KLTown_Name, RGNCity.KLRgn_Name) ||

				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

				end||

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
					', '||Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '||CCC.CmpCallCard_Ulic else '' end
				end ||

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
				end ||

				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Name\"

				,case when UAD.UnformalizedAddressDirectory_lat is not null then UAD.UnformalizedAddressDirectory_lat else CCC.CmpCallCard_CallLtd end as \"UnAdress_lat\"
				,case when UAD.UnformalizedAddressDirectory_lng is not null then UAD.UnformalizedAddressDirectory_lng else CCC.CmpCallCard_CallLng end as \"UnAdress_lng\"

				,case when City.KLCity_id is not null then City.KLCity_id else Town.KLTown_id end as \"Town_id\"
				,case when COALESCE(CCC.KLStreet_id,0) = 0 then

					case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

					else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				 else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"
				,RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as \"Person_Surname\"
				,RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as \"Person_Firname\"
				,RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as \"Person_Secname\"
				,PS.Polis_Ser as \"Polis_Ser\"
				,PS.Polis_Num as \"Polis_Num\"
				,PS.Person_id as \"Person_id\"
				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

				end||

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
					', '||Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '||CCC.CmpCallCard_Ulic else '' end
				end ||

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
				end ||

				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Podz is not null then ', п.'||CAST(CCC.CmpCallCard_Podz as varchar(10)) else '' end ||
				case when CCC.CmpCallCard_Etaj is not null then ', эт.'||CAST(CCC.CmpCallCard_Etaj as varchar(10)) else '' end ||
				case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"AstraAdress_Name\"
				,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
				,CCC.CmpCallerType_id as \"CmpCallerType_id\"
				,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
				,CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\"
				,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
				,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
				,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
				,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
				,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,case CCC.CmpCallCard_IsExtra
					when 1 then 'Экстренный'
					when 2 then 'Неотложный'
					when 3 then 'Вызов врача на дом'
					when 4 then 'Обращение в поликлинику'
				end as \"CmpCallCard_IsExtraText\"
				,COALESCE(CCC.CmpCallCard_IsExtra, 1) as \"CmpCallCard_IsExtra\"

				,CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\"
				,CCC.CmpCallCard_IsPassSSMP as \"CmpCallCard_IsPassSSMP\"
				,CCC.Lpu_smpid as \"Lpu_smpid\"
				,to_char(CCC.CmpCallCard_Tper, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTper\"

				,to_char(CCC.CmpCallCard_Vyez, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateVyez\"

				,to_char(CCC.CmpCallCard_Przd, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DatePrzd\"

				,to_char(CCC.CmpCallCard_Tgsp, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTgsp\"

				,to_char(CCC.CmpCallCard_Tsta, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTsta\"

				,to_char(CCC.CmpCallCard_Tisp, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTisp\"

				,to_char(CCC.CmpCallCard_Tvzv, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTvzv\"

				,to_char(CCC.CmpCallCard_HospitalizedTime, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_HospitalizedTime\"

				,to_char(CCC.CmpCallCard_Tper, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_TimeTper\"

				".$timesBlock."

				,CCC.KLRgn_id as \"KLRgn_id\"
				,CCC.KLSubRgn_id as \"KLSubRgn_id\"
				,CCC.KLCity_id as \"KLCity_id\"
				,CCC.KLTown_id as \"KLTown_id\"
				,City.KLArea_pid as \"KLRegion_id\"

				,DATEDIFF('second', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) as \"MAXDateDiff\"
				,CCC.KLStreet_id as \"KLStreet_id\",

				".$group_case." as \"CmpGroup_id\"
				".$group_table_case."
				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '04'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id=7 THEN '03'
									WHEN CCC.CmpCallCardStatusType_id=8 THEN '02'
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN '0'||cast(CCC.CmpCallCardStatusType_id+1 as varchar)
									ELSE  '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar(2))
								END
							ELSE
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '07'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=7 THEN '03'
									WHEN CCC.CmpCallCardStatusType_id=8 THEN '02'
									ELSE '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar(2))
								END
							END
					else '09'
				end as \"CmpGroupName_id\",
				CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				CCCST.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\",
				COALESCE(CCC.CmpCallCard_isControlCall,1) as \"CmpCallCard_isControlCall\",
				CASE
					WHEN CCCST.CmpCallCardStatusType_Code in(1,12) THEN 1 --относится к группе (передано)
					WHEN CCCST.CmpCallCardStatusType_Code in(2) THEN 2 --относится к группе (принято)
					WHEN CCCST.CmpCallCardStatusType_Code in(11) THEN 4 --относится к группе (отложено)
					ELSE 3 --относится к группе всех остальных
				END as \"TransmittedOrAccepted\",
				CCC.CmpCallCard_rid as \"CmpCallCard_rid\",
				CCC.CmpCallCard_UlicSecond as \"CmpCallCard_UlicSecond\",

				CASE WHEN CCC.CmpCallCardStatusType_id = 19 THEN
					activeEvent.CmpCallCardEventType_Name || ' до ' || to_char(CCC.CmpCallCard_storDT, 4)||' '||to_char(cast(CCC.CmpCallCard_storDT as time(6)))
					ELSE activeEvent.CmpCallCardEventType_Name
				END as \"CmpCallCardEventType_Name\",
				activeEvent.CmpCallCardEvent_updDT as \"CmpCallCardEvent_updDT\",
				CASE WHEN CCC.CmpCallCardStatusType_id not in(4,5,6,16,19) THEN DATEDIFF('minute', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate()) END as \"EventWaitDuration\",
				CCC.CmpCallCard_storDT as \"CmpCallCard_storDT\",
				CASE WHEN CCC.CmpCallCardStatusType_id = 19 THEN CCC.CmpCallCard_defCom END as \"CmpCallCard_defCom\",
				CASE WHEN
					DATEDIFF('minute', dbo.tzGetDate(), CCC.CmpCallCard_storDT ) > 0
				THEN
					1
				ELSE
					2
				END as \"isTimeDefferedCall\",
				-- CASE WHEN CCC.CmpCallCardStatusType_id = 20 THEN 2 ELSE 1 END as is112, старый способ
				-- CASE WHEN COALESCE(CCC112.CmpCallCard112_id, 1) = 1 THEN 1 ELSE 2 END as is112, этот тоже устарел

				CASE WHEN COALESCE(CCC112.CmpCallCard112_id,CCC112rid.CmpCallCard112_id, 1) = 1 THEN 1 ELSE 2 END as is112,
				CASE WHEN ( (CCC.CmpCallCard_Tper is NULL) OR (CCC.EmergencyTeam_id is NULL) OR DATEDIFF('second', CCC.CmpCallCard_Tper, dbo.tzGetDate() ) < 10)
				THEN ''
				ELSE
					CASE WHEN
						lastCallMessage.CmpCallCardMessage_tabletDT is null
					THEN
						'Не доставлено'
					ELSE
						'Доставлено'
					END
				END as \"lastCallMessageText\"
				,ETDT.EmergencyTeamDelayType_Name as \"EmergencyTeamDelayType_Name\"
				,cETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\"
				,CCR.CmpCallRecord_id as \"CmpCallRecord_id\"
				,ETSrid.EmergencyTeamStatus_Code as \"ridEmergencyTeamStatus_Code\"
				,ETrid.EmergencyTeam_id as \"ridEmergencyTeam_id\"
				,cmpIllegalAct.CmpIllegalAct_prmDT as \"CmpIllegalAct_prmDT\"
				,cmpIllegalAct.CmpIllegalAct_Comment as \"CmpIllegalAct_Comment\"
				,cmpIllegalAct.CmpIllegalAct_byPerson as \"CmpIllegalAct_byPerson\"
				,CCCD.Duplicate_Count as \"Duplicate_Count\"
				,CCCAC.ActiveCall_Count as \"ActiveCall_Count\"
				,RES.CmpPPDResult_id as \"CmpPPDResult_id\"
				,RES.CmpPPDResult_Name as \"CmpPPDResult_Name\"
				,CCC.Lpu_ppdid as \"Lpu_ppdid\"
				,CCC.MedService_id as \"MedService_id\"
				,CCCEDeny.CmpCallCardEvent_id as \"hasEventDeny\"
				,case when CCC.Lpu_ppdid is not null then 'НМП' else 'СМП' end as \"CmpCallCardAcceptor_Code\"
				,to_char (CCCD.Duplicate_Count) || ' / ' || to_char (CCCAC.ActiveCall_Count) as \"DuplicateAndActiveCall_Count\"
				" . $soundSetting . "
				" . $isSendCall . "
				" . $isCallControll . "
				" . $isDenyCall . "
				" . $isNoTrans . "
				" . $countCallsOnTeam . "
				-- end select
			FROM
				-- from
				v_CmpCallCard CCC 

				inner join CmpCallCardArray CCCA on CCCA.CmpCallCard_id = CCC.CmpCallCard_id
				LEFT JOIN LATERAL (

					select 
					    cmpclosecard_id as cmpclosecard_id,
                        cmpcallcard_id as cmpcallcard_id,
                        pmuser_insid as pmuser_insid,
                        pmuser_updid as pmuser_updid,
                        cmpclosecard_insdt as cmpclosecard_insdt,
                        cmpclosecard_upddt as cmpclosecard_upddt,
                        feldsher_id as feldsher_id,
                        stationnum as stationnum,
                        emergencyteamnum as emergencyteamnum,
                        accepttime as accepttime,
                        transtime as transtime,
                        gotime as gotime,
                        arrivetime as arrivetime,
                        transporttime as transporttime,
                        tohospitaltime as tohospitaltime,
                        endtime as endtime,
                        backtime as backtime,
                        summtime as summtime,
                        area_id as area_id,
                        town_id as town_id,
                        city_id as city_id,
                        street_id as street_id,
                        house as house,
                        office as office,
                        entrance as entrance,
                        codeentrance as codeentrance,
                        level as level,
                        ktov as ktov,
                        phone as phone,
                        fam as fam,
                        name as name,
                        middle as middle,
                        age as age,
                        sex_id as sex_id,
                        work as work,
                        documentnum as documentnum,
                        feldsheraccept as feldsheraccept,
                        feldshertrans as feldshertrans,
                        callpovod_id as callpovod_id,
                        calltype_id as calltype_id,
                        isalco as isalco,
                        complaints as complaints,
                        anamnez as anamnez,
                        ismenen as ismenen,
                        isanis as isanis,
                        isnist as isnist,
                        islight as islight,
                        isacro as isacro,
                        ismramor as ismramor,
                        ishale as ishale,
                        isperit as isperit,
                        urine as urine,
                        shit as shit,
                        othersympt as othersympt,
                        workad as workad,
                        ad as ad,
                        pulse as pulse,
                        chss as chss,
                        chd as chd,
                        temperature as temperature,
                        pulsks as pulsks,
                        gluck as gluck,
                        localstatus as localstatus,
                        ekg1time as ekg1time,
                        ekg1 as ekg1,
                        ekg2time as ekg2time,
                        ekg2 as ekg2,
                        diag_id as diag_id,
                        efad as efad,
                        efchss as efchss,
                        efpulse as efpulse,
                        eftemperature as eftemperature,
                        efchd as efchd,
                        efpulsks as efpulsks,
                        efgluck as efgluck,
                        kilo as kilo,
                        lpu_id as lpu_id,
                        helpplace as helpplace,
                        helpauto as helpauto,
                        desctext as desctext,
                        cmpclosecard_isinreg as cmpclosecard_isinreg,
                        korpus as korpus,
                        room as room,
                        cmpclosecard_firstversion as cmpclosecard_firstversion,
                        cmpclosecard_ispaid as cmpclosecard_ispaid,
                        day_num as day_num,
                        year_num as year_num,
                        callpovodnew_id as callpovodnew_id,
                        emergencyteamspec_id as emergencyteamspec_id,
                        medpersonal_id as medpersonal_id,
                        socstatus_id as socstatus_id,
                        issogl as issogl,
                        isotkazmed as isotkazmed,
                        isotkazhosp as isotkazhosp,
                        lpusection_id as lpusection_id,
                        cmpclosecard_indexrep as cmpclosecard_indexrep,
                        cmpclosecard_indexrepinreg as cmpclosecard_indexrepinreg,
                        cmpclosecard_isarchive as cmpclosecard_isarchive,
                        paytype_id as paytype_id,
                        lpu_codesmo as lpu_codesmo,
                        cmpclosecardcause_id as cmpclosecardcause_id,
                        cmpclosecardwherereported_id as cmpclosecardwherereported_id,
                        cmpclosecardwhere_dt as cmpclosecardwhere_dt,
                        messagenum as messagenum,
                        acceptfio as acceptfio,
                        medstafffact_id as medstafffact_id,
                        rankinscale_id as rankinscale_id,
                        rankinscale_sid as rankinscale_sid,
                        isotkazsign as isotkazsign,
                        otkazsignwhy as otkazsignwhy,
                        disstart as disstart,
                        cmpcallertype_id as cmpcallertype_id,
                        lpubuilding_id as lpubuilding_id,
                        emergencyteam_id as emergencyteam_id,
                        person_id as person_id,
                        diag_uid as diag_uid,
                        cmpclosecard_epid as cmpclosecard_epid,
                        cmpclosecard_glaz as cmpclosecard_glaz,
                        cmpclosecard_e1 as cmpclosecard_e1,
                        cmpclosecard_m1 as cmpclosecard_m1,
                        cmpclosecard_v1 as cmpclosecard_v1,
                        cmpclosecard_e2 as cmpclosecard_e2,
                        cmpclosecard_m2 as cmpclosecard_m2,
                        cmpclosecard_v2 as cmpclosecard_v2,
                        cmpclosecard_topic as cmpclosecard_topic,
                        diag_sid as diag_sid,
                        cmpclosecard_glazafter as cmpclosecard_glazafter,
                        cmpclosecard_transpenddt as cmpclosecard_transpenddt,
                        cmpclosecard_street as cmpclosecard_street,
                        cmpclosecard_isextra as cmpclosecard_isextra,
                        cmpclosecard_isprofile as cmpclosecard_isprofile,
                        cmpclosecard_isnmp as cmpclosecard_isnmp,
                        cmpclosecard_acceptbrigadedt as cmpclosecard_acceptbrigadedt,
                        cmpclosecard_isintestinal as cmpclosecard_isintestinal,
                        cmpclosecard_isheartnoise as cmpclosecard_isheartnoise,
                        cmpclosecard_sat as cmpclosecard_sat,
                        cmpclosecard_aftersat as cmpclosecard_aftersat,
                        cmpclosecard_rhythm as cmpclosecard_rhythm,
                        cmpclosecard_afterrhythm as cmpclosecard_afterrhythm,
                        cmpclosecard_israttle as cmpclosecard_israttle,
                        cmpclosecard_isvomit as cmpclosecard_isvomit,
                        cmpclosecard_isdiuresis as cmpclosecard_isdiuresis,
                        cmpclosecard_isdefecation as cmpclosecard_isdefecation,
                        cmpclosecard_istrauma as cmpclosecard_istrauma,
                        cmpclosecard_begtreatdt as cmpclosecard_begtreatdt,
                        cmpclosecard_endtreatdt as cmpclosecard_endtreatdt,
                        org_mid as org_mid,
                        cmpclosecard_helpdt as cmpclosecard_helpdt,
                        cmpclosecard_lethaldt as cmpclosecard_lethaldt,
                        cmplethaltype_id as cmplethaltype_id,
                        cmpclosecard_addinfo as cmpclosecard_addinfo,
                        medstafffact_cid as medstafffact_cid,
                        cmpclosecard_clinicaleff as cmpclosecard_clinicaleff,
                        cmpclosecard_menenaddiction as cmpclosecard_menenaddiction,
                        cmpclosecard_guid as cmpclosecard_guid,
                        cmpclosecard_firstversionguid as cmpclosecard_firstversionguid,
                        emergencyteam_guid as emergencyteam_guid,
                        cmpcallcard_guid as cmpcallcard_guid,
                        person_polisser as person_polisser,
                        person_polisnum as person_polisnum,
                        cmpclosecard_polisednum as cmpclosecard_polisednum,
                        cmpresult_id as cmpresult_id,
                        unformalizedaddressdirectory_id as unformalizedaddressdirectory_id,
                        cmpclosecard_ulic as cmpclosecard_ulic,
                        cmpclosecard_issignlist as cmpclosecard_issignlist,
                        leavetype_id as leavetype_id,
                        cmpclosecard_userkilo as cmpclosecard_userkilo,
                        person_snils as person_snils,
                        cmpclosecard_passtime as cmpclosecard_passtime,
                        cmpclosecard_callbacktime as cmpclosecard_callbacktime,
                        cmpclosecard_dopinfo as cmpclosecard_dopinfo,
                        cmpclosecard_ulicsecond as cmpclosecard_ulicsecond,
                        cmpcallplacetype_id as cmpcallplacetype_id,
                        alerg as alerg,
                        epid as epid,
                        isvac as isvac,
                        iskupir as iskupir,
                        zev as zev,
                        perk as perk,
                        cmpclosecard_daynumpr as cmpclosecard_daynumpr,
                        cmpclosecard_yearnumpr as cmpclosecard_yearnumpr,
                        cmpcallsigntype_id as cmpcallsigntype_id,
                        cmpclosecard_comm as cmpclosecard_comm,
                        diag_add as diag_add,
                        cmpclosecard_statuslocalis as cmpclosecard_statuslocalis,
                        bad_dt as bad_dt,
                        mensis_dt as mensis_dt,
                        cmpclosecard_isinregzno as cmpclosecard_isinregzno,
                        registry_sid as registry_sid,
                        cmpclosecard_userkilocommon as cmpclosecard_userkilocommon
					from {$this->schema}.v_CmpCloseCard 

					where CmpCallCard_id = CmpCallCard_id
					order by CmpCloseCard_id desc
					limit 1
				) CCLC ON TRUE
				LEFT JOIN LATERAL (

					select
						CmpCallCardEvent_updDT as CmpCallCardEvent_updDT,
						CmpCallCardEventType_Name as CmpCallCardEventType_Name,
						CmpCallCardEventType_Code as CmpCallCardEventType_Code,
						EmergencyTeamStatus_id as EmergencyTeamStatus_id,
						EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
						EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
					from activeEventArray
					where CmpCallCard_id = CCC.CmpCallCard_id
					ORDER BY CmpCallCardEvent_updDT desc
					limit 1
				) as activeEvent ON TRUE

				LEFT JOIN LATERAL (

					SELECT 
						CmpCallCardEvent_id as CmpCallCardEvent_id
					FROM v_CmpCallCardEvent	CCCE 

					LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

					WHERE CCC.CmpCallCard_id = CCCE.CmpCallCard_id and CCCET.CmpCallCardEventType_Code = 34 --отлонен
					limit 1
				) CCCEDeny ON TRUE
				LEFT JOIN LATERAL(

					select
						cmpcallcardmessage_id as cmpcallcardmessage_id,
                        message_id as message_id,
                        cmpcallcard_id as cmpcallcard_id,
                        cmpcallcardmessage_webdt as cmpcallcardmessage_webdt,
                        cmpcallcardmessage_tabletdt as cmpcallcardmessage_tabletdt,
                        pmuser_insid as pmuser_insid,
                        pmuser_updid as pmuser_updid,
                        cmpcallcardmessage_insdt as cmpcallcardmessage_insdt,
                        cmpcallcardmessage_upddt as cmpcallcardmessage_upddt
					from
						v_CmpCallCardMessage CCCM 

					where
						CCCM.CmpCallCard_id = CmpCallCard_id
					order by CCCM.CmpCallCardMessage_webDT desc
					limit 1
				) as lastCallMessage ON TRUE

				LEFT JOIN LATERAL(

					select
						to_char(CIA.CmpIllegalAct_prmDT, 'DD.MM.YYYY') as CmpIllegalAct_prmDT,

						CIA.CmpIllegalAct_Comment as CmpIllegalAct_Comment,
						CASE WHEN CIA.Person_id = CCC.Person_id THEN '2' ELSE '1' END as CmpIllegalAct_byPerson
					from
						v_CmpIllegalAct CIA 

					where
						CIA.Person_id = CCC.Person_id
						OR (
							(CIA.KLRgn_id = CCC.KLRgn_id or CIA.KLRgn_id = dbo.getregion()) AND
							(CIA.KLSubRGN_id = CCC.KLSubRgn_id or CIA.KLSubRGN_id is null) AND
							(CIA.KLCity_id = CCC.KLCity_id or CIA.KLCity_id is null) AND
							(CIA.KLTown_id = CCC.KLTown_id or CIA.KLTown_id is null) AND
							CIA.KLStreet_id = CCC.KLStreet_id AND
							CIA.Address_House = CCC.CmpCallCard_Dom AND
							(CIA.Address_Corpus = CCC.CmpCallCard_Korp or CIA.Address_Corpus is null) AND
							(CIA.Address_Flat = CCC.CmpCallCard_Kvar or CIA.Address_House = CCC.CmpCallCard_Dom)
						)
					order by CIA.CmpIllegalAct_prmDT desc
					limit 1
				) as cmpIllegalAct ON TRUE

				left join v_PersonState PS  on PS.Person_id = CCC.Person_id

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				left join v_CmpReason CSecondR  on CSecondR.CmpReason_id = CCC.CmpSecondReason_id

				left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				left join EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

				left join v_LpuBuilding LB  on CCC.LpuBuilding_id = LB.LpuBuilding_id


				LEFT JOIN LATERAL (

					SELECT
						etsh.EmergencyTeamStatus_id as EmergencyTeamStatus_id,
						etsh.EmergencyTeamDelayType_id as EmergencyTeamDelayType_id,
						ets.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code
					FROM
						v_EmergencyTeamStatusHistory etsh 

						left join v_EmergencyTeamStatus ets  on ets.EmergencyTeamStatus_id = etsh.EmergencyTeamStatus_id

					WHERE
						etsh.CmpCallCard_id = CCC.CmpCallCard_id
					ORDER BY
						etsh.EmergencyTeamStatusHistory_id DESC
                    LIMIT 1
				) as cETS ON TRUE

				LEFT JOIN v_EmergencyTeamDelayType ETDT  on cETS.EmergencyTeamDelayType_id = ETDT.EmergencyTeamDelayType_id

				left join v_EmergencyTeamSpec ETSpec  on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id

				left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id


				left join v_KLRgn RGNCity  on RGNCity.KLRgn_id = CCC.KLCity_id


				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLTown_id

				left join v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLCity_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id

				left join v_KLStreet SecondStreet  on 'SecondStreet.KLStreet_id' = CCC.CmpCallCard_UlicSecond

				left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id

				left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

				left join v_CmpCallRecord CCR  on CCC.CmpCallCard_id = CCR.CmpCallCard_id

				left join v_CmpCallCard CCCrid  on CCC.CmpCallCard_rid = CCCrid.CmpCallCard_id

				left join v_CmpCallCard112 CCC112  on CCC.CmpCallCard_id = CCC112.CmpCallCard_id

				left join v_CmpCallCard112 CCC112rid  on CCCrid.CmpCallCard_id = CCC112rid.CmpCallCard_id

				left join v_EmergencyTeam ETrid  on CCCrid.EmergencyTeam_id = ETrid.EmergencyTeam_id

				left join v_EmergencyTeamStatus ETSrid  on ETrid.EmergencyTeamStatus_id = ETSrid.EmergencyTeamStatus_id

				left join CmpPPDResult RES  on RES.CmpPPDResult_id = CCC.CmpPPDResult_id

				LEFT JOIN LATERAL (

					select count(1) as countCalls
					from v_CmpCallCard c 

					where c.EmergencyTeam_id = ET.EmergencyTeam_id
						AND c.CmpCallCardStatusType_id = 2
				) callsOnTeam ON TRUE
				LEFT JOIN LATERAL (

					select
						COUNT(CCCDouble.CmpCallCard_id) as Duplicate_Count
					from
						v_CmpCallCard CCCDouble 

						left join v_CmpCallCardStatusType CCCSTDouble  on CCCSTDouble.CmpCallCardStatusType_id = CCCDouble.CmpCallCardStatusType_id

					where
						CCCDouble.CmpCallCard_rid = CCC.CmpCallCard_id
						and CCCSTDouble.CmpCallCardStatusType_Code = '9'
						and COALESCE(CCCDouble.CmpCallCard_IsActiveCall, 1) != 2
				) CCCD ON TRUE
				LEFT JOIN LATERAL(

					select
						COUNT(CCCActiveCall.CmpCallCard_id) as ActiveCall_Count
					from
						v_CmpCallCard CCCActiveCall 

					where
						CCCActiveCall.CmpCallCard_rid = CCC.CmpCallCard_id
						and COALESCE(CCCActiveCall.CmpCallCard_IsActiveCall, 1) = 2
				) CCCAC ON TRUE
				" . implode(" ", $join) . "
				-- end from
		";

		//var_dump(getDebugSQL($sql, $sqlArr )); exit;
		//echo getDebugSQL($sql, $sqlArr);exit;
		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			$total_array = $query->result_array();
			if(!empty($data['callRecords'])){
				$callRecordsBeforeLoad = json_decode($data['callRecords'],true);
				$haveNewCall = false;
				//определяем новый вызов для выделения в арме
				if ($callRecordsBeforeLoad['all'] !== NULL) {

					foreach ($total_array as $key => $call) {
						if ((!in_array($call["CmpCallCard_id"], $callRecordsBeforeLoad['new']))
							&& (!in_array($call["CmpCallCard_id"], $callRecordsBeforeLoad['all']))
							&& (in_array($call["CmpCallCardStatusType_id"], array(1,20)))
							) {

							$total_array[$key]['isNewCall'] = '1';
							$haveNewCall = true;
						}
					}
					if(!$haveNewCall){
						foreach ($total_array as $key => $call) {
							if(in_array($call["CmpCallCard_id"], $callRecordsBeforeLoad['new'])){

								$total_array[$key]['isNewCall'] = '1';
							}
						}
					}
				}
			}

			//if($_SESSION['region']['nick'] == 'ufa' && $other_mode)
				$total_array = array_merge($total_array,$array_count); //сливаем пустые вызовы" (нужны для количества неподгружаемых вызовов) и остальные
			return array(
				'data' => $total_array,
				'totalCount' => $query->num_rows()
			);
		}

		return false;
	}

	/**
	 * Получение истории списания лек. препаратов на вызов
	 * @param type $data
	 * @return boolean
	 */
	public function loadCallCardFarmacyRegisterHistory($data) {

		if (!$data || empty($data['CmpCallCard_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор талона вызова'));
		}

		$query = "
			SELECT
				CFBRH.CmpFarmacyBalanceRemoveHistory_id as \"CmpFarmacyBalanceRemoveHistory_id\",
				CASE WHEN (COALESCE(D.Drug_Fas,0) = 0) then RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||' '||to_char(COALESCE(D.DrugForm_Name,''))||' '||to_char(COALESCE(D.Drug_Dose,'')))

					else RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||', '||to_char(COALESCE(D.DrugForm_Name,''))||', '||to_char(COALESCE(D.Drug_Dose,''))||', №'||TO_CHAR(D.Drug_Fas))

				end as \"DrugTorg_Name\",
				D.Drug_PackName as \"Drug_PackName\",
				D.Drug_Code as \"Drug_Code\",
				CFBRH.CmpFarmacyBalanceRemoveHistory_DoseCount as \"CmpFarmacyBalanceRemoveHistory_DoseCount\",
				CFBRH.CmpFarmacyBalanceRemoveHistory_PackCount as \"CmpFarmacyBalanceRemoveHistory_PackCount\"
			FROM
				CmpFarmacyBalanceRemoveHistory CFBRH 

				LEFT JOIN v_CmpFarmacyBalance as CFB  ON( CFB.CmpFarmacyBalance_id = CFBRH.CmpFarmacyBalance_id )

				LEFT JOIN rls.v_Drug D  on (D.Drug_id = CFB.Drug_id)

			WHERE
				CFBRH.CmpCallCard_id = :CmpCallCard_id
			ORDER BY
				CFBRH.CmpFarmacyBalanceRemoveHistory_id DESC
			";
		$result = $this->db->query($query,array(
			'CmpCallCard_id'=>$data['CmpCallCard_id']
		));

		if (is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}

	}

	/**
	 * Получение срочности и профиля вызова
	 */
	public function removeCmpCallCardFarmacyDrugHistory($data){
		if (!$data || empty($data['CmpFarmacyBalanceRemoveHistory_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор истории списания лек. препарата на вызов'));
		}

		$CmpFarmacyQuery = "
		        select
                    CmpFarmacyBalance_id as \"CmpFarmacyBalance_id\",
                    ErrCode as \"Error_Code\",
                    ErrMessage as \"Error_Msg\"
				from p_CmpFarmacyBalance_updRest (
					CmpFarmacyBalance_id := CmpFarmacyBalance_id,
					CmpFarmacyBalance_PackRest := CmpFarmacyBalance_PackCount,
					CmpFarmacyBalance_DoseRest := CmpFarmacyBalance_DoseCount,
					pmUser_id := :pmUser_id
                )
			    
			    with ParamDefinition as (
				SELECT
					CFB.CmpFarmacyBalance_PackRest - CFBRH.CmpFarmacyBalanceRemoveHistory_PackCount as \"CmpFarmacyBalance_PackRest\",
					CmpFarmacyBalance_DoseCount_OUT = CFB.CmpFarmacyBalance_DoseRest - CFBRH.CmpFarmacyBalanceRemoveHistory_DoseCount as \"CmpFarmacyBalance_DoseCount_OUT\",
					CmpFarmacyBalance_id_OUT = CFBRH.CmpFarmacyBalance_id as \"CmpFarmacyBalance_id_OUT\" 
                FROM v_CmpFarmacyBalanceRemoveHistory CFBRH
					LEFT JOIN v_CmpFarmacyBalance CFB  on CFBRH.CmpFarmacyBalance_id = CFB.CmpFarmacyBalance_id
                WHERE CFBRH.CmpFarmacyBalanceRemoveHistory_id = :CmpFarmacyBalanceRemoveHistory_id
                )

                select
                    ErrCode as \"Error_Code\",
                    ErrMessage as \"Error_Msg\"
				from sp_executesql (
				    (select CmpFarmacyBalance_PackRest from SQLString),
				    (select CmpFarmacyBalance_DoseRest from SQLString),
				    (select CmpFarmacyBalance_PackRest from ParamDefinition),
				    (select CmpFarmacyBalance_DoseCount_OUT from ParamDefinition),
				    (select CmpFarmacyBalance_id_OUT from ParamDefinition),
				    mpFarmacyBalanceRemoveHistory_id := :CmpFarmacyBalanceRemoveHistory_id,
				    CmpFarmacyBalance_id_OUT := CmpFarmacyBalance_id,
				    CmpFarmacyBalance_PackCount_OUT := CmpFarmacyBalance_PackCount,
				    CmpFarmacyBalance_DoseCount_OUT := CmpFarmacyBalance_DoseCount
				)
			";

		$this->db->trans_begin();
		$result = $this->db->query($CmpFarmacyQuery,array(
			'CmpFarmacyBalanceRemoveHistory_id'=>$data['CmpFarmacyBalanceRemoveHistory_id'],
			'pmUser_id'=>$data['pmUser_id']
		));

		if (!is_object($result)) {
			$this->db->trans_rollback();
			return false;

		}
		$result =  $result->result('array');
		if (strlen($result[0]['Error_Msg'])>0) {
			$this->db->trans_rollback();
			return $result;
		}

		$removeFarmacyHistoryQuery = '
            select 
                Error_Code as "Error_Code",
                Error_Message as "Error_Msg"
			from p_CmpFarmacyBalanceRemoveHistory_del (
				CmpFarmacyBalanceRemoveHistory_id := :CmpFarmacyBalanceRemoveHistory_id
				)
		';

		$result = $this->db->query($removeFarmacyHistoryQuery,array(
			'CmpFarmacyBalanceRemoveHistory_id'=>$data['CmpFarmacyBalanceRemoveHistory_id'],
			'pmUser_id'=>$data['pmUser_id']
		));

		if (!is_object($result)) {
			$this->db->trans_rollback();
			return false;
		}
		$result =  $result->result('array');
		if (strlen($result[0]['Error_Msg'])>0) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}
		return $result;
	}

	/**
	 * Загрузка формы закрытия талона вызова
	 * @param type $data
	 * @return boolean
	 */
	public function loadCmpCloseCardShort($data) {
		if (!$data || empty($data['CmpCallCard_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор талона вызова'));
		}

		$prequery = "
			SELECT
				CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
			FROM
				{$this->schema}.v_CmpCloseCard CLC 

			WHERE
				CLC.CmpCallCard_id = :CmpCallCard_id
		";
		$preresult = $this->db->query( $prequery, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );
		$preretrun = $preresult->result( 'array' );

		$varparams = "
			CCC.CmpDiag_oid as \"CmpDiag_oid\",
			CCC.CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
			CCC.CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
			to_char(CCC.CmpCallCard_Tper, 'HH24:MI:SS') as \"CmpCallCard_Tper\",

			to_char(CCC.CmpCallCard_Vyez, 'HH24:MI:SS') as \"CmpCallCard_Vyez\",

			to_char(CCC.CmpCallCard_Przd, 'HH24:MI:SS') as \"CmpCallCard_Przd\",

			to_char(CCC.CmpCallCard_Tgsp, 'HH24:MI:SS') as \"CmpCallCard_Tgsp\",

			to_char(CCC.CmpCallCard_Tsta, 'HH24:MI:SS') as \"CmpCallCard_Tsta\",

			to_char(CCC.CmpCallCard_Tisp, 'HH24:MI:SS') as \"CmpCallCard_Tisp\",

			to_char(CCC.CmpCallCard_Tvzv, 'HH24:MI:SS') as \"CmpCallCard_Tvzv\"

		";
		if ( sizeof( $preretrun ) ) {
			//есть закрытая карта
			//значит значения берем из нее
			$varparams = "
				CClC.Diag_id as \"CmpDiag_oid\",
				CASE WHEN COALESCE(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as \"CmpCallCard_IsAlco\",

				CClC.Kilo as \"CmpCallCard_Kilo\",
				to_char(CClC.GoTime, 'HH24:MI:SS') as \"CmpCallCard_Vyez\",

				to_char(CClC.TransTime, 'HH24:MI:SS') as \"CmpCallCard_Tper\",

				to_char(CClC.ArriveTime, 'HH24:MI:SS') as \"CmpCallCard_Przd\",

				to_char(CClC.BackTime, 'HH24:MI:SS') as \"CmpCallCard_Tvzv\",

				to_char(CClC.EndTime, 'HH24:MI:SS') as \"CmpCallCard_Tisp\",

				to_char(CClC.ToHospitalTime, 'HH24:MI:SS') as \"CmpCallCard_Tsta\",

				to_char(CClC.AcceptTime, 'HH24:MI:SS') as \"CmpCallCard_prmDT\",

				to_char(CClC.TransportTime, 'HH24:MI:SS') as \"CmpCallCard_Tgsp\"

			";
		}

		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				SELECT
					'' as \"accessType\",
					CCC.CmpCallCard_id as \"CmpCallCard_id\",
					COALESCE(CCC.Person_id, 0) as \"Person_id\",
					CCC.CmpArea_gid as \"CmpArea_gid\",
					CCC.CmpArea_id as \"CmpArea_id\",
					CCC.CmpArea_pid as \"CmpArea_pid\",
					CCC.CmpCallCard_IsAlco as \"CmpCallCard_IsAlco\",
					CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
					CCC.CmpCallType_id as \"CmpCallType_id\",
					CCC.CmpDiag_aid as \"CmpDiag_aid\",
					CCC.CmpDiag_oid as \"CmpDiag_oid\",
					CCC.CmpLpu_aid as \"CmpLpu_aid\",
					CCC.CmpLpu_id as \"CmpLpu_id\",
					CL.Lpu_id as \"Lpu_oid\",
					CCC.CmpPlace_id as \"CmpPlace_id\",
					CCC.CmpProfile_bid as \"CmpProfile_bid\",
					CCC.CmpProfile_cid as \"CmpProfile_cid\",
					CCC.CmpReason_id as \"CmpReason_id\",
					CCC.CmpResult_id as \"CmpResult_id\",
					CCC.ResultDeseaseType_id as \"ResultDeseaseType_id\",
					CCC.CmpTalon_id as \"CmpTalon_id\",
					CCC.CmpTrauma_id as \"CmpTrauma_id\",
					CCC.Diag_sid as \"Diag_sid\",
					CCC.Diag_uid as \"Diag_uid\",
					CCC.Sex_id as \"Sex_id\",
					PS.Sex_id as \"SexIdent_id\",
					CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
					CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
					CCC.CmpCallCard_Prty as \"CmpCallCard_Prty\",
					CCC.CmpCallCard_Sect as \"CmpCallCard_Sect\",
					CCC.CmpCallCard_City as \"CmpCallCard_City\",
					CCC.CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
					CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
					CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
					CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
					CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
					CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
					CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
					CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
					CmpCallReasonType_id as \"CmpCallReasonType_id\",
					CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
					CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
					RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as \"Person_Surname\",
					RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as \"Person_Firname\",
					RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as \"Person_Secname\",
					RTRIM(LTRIM(COALESCE(PS.Person_Surname, ''))) as \"PersonIdent_Surname\",
					RTRIM(LTRIM(COALESCE(PS.Person_Firname, ''))) as \"PersonIdent_Firname\",
					RTRIM(LTRIM(COALESCE(PS.Person_Secname, ''))) as \"PersonIdent_Secname\",
					--to_char(CCC.\"Person_BirthDay\", 'DD.MM.YYYY') as \"Person_Birthday\",

					TO_CHAR(CCC.\"Person_BirthDay\", 'DD.MM.YYYY') as \"Person_Birthday\",
					CCC.Person_Age as \"Person_Age\",

					--Возраст на дату вызова
					--COALESCE(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), '') as PersonIdent_Age,

					EXTRACT( YEAR FROM age( CCC.CmpCallCard_prmDT, PS.Person_Birthday ) ) as \"PersonIdent_Age\",

					CCC.Person_PolisSer as \"Polis_Ser\",
					CCC.Person_PolisNum as \"Polis_Num\",
					PS.Person_EdNum as \"Polis_EdNum\",
					PS.Polis_Num as \"PolisIdent_Num\",
					CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
					CCC.CmpCallerType_id as \"CmpCallerType_id\",
					CCC.CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
					CCC.CmpCallCard_Stan as \"CmpCallCard_Stan\",
					--to_char(CCC.\"CmpCallCard_prmDT\", 'DD.MM.YYYY') as \"CmpCallCard_prmDate\",

					TO_CHAR(CCC.CmpCallCard_prmDT, 'DD Mon YYYY HH24: MI: SS') as \"CmpCallCard_prmDate\",
					--to_char(CCC.\"CmpCallCard_prmDT\", 'HH24:MI:SS') as \"CmpCallCard_prmTime\",

					TO_CHAR(CCC.CmpCallCard_prmDT, 'HH24: MI: SS') as \"CmpCallCard_prmTime\",
					CCC.CmpCallCard_Line as \"CmpCallCard_Line\",
					CCC.CmpCallCard_Numb as \"CmpCallCard_Numb\",
					CCC.CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
					CCC.CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
					CCC.CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
					CCC.CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
					CCC.CmpCallCard_RCod as \"CmpCallCard_RCod\",
					CCC.CmpCallCard_TabN as \"CmpCallCard_TabN\",
					CCC.CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
					CCC.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(CCC.\"CmpCallCard_IsMedPersonalIdent\",1) as \"CmpCallCard_IsMedPersonalIdent\",
					CCC.CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
					CCC.CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
					CCC.CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
					CCC.CmpCallCard_Expo as \"CmpCallCard_Expo\",
					CCC.CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
					CCC.CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
					CCC.CmpCallCard_D201 as \"CmpCallCard_D201\",
					CCC.CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
					CCC.CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
					CCC.CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
					CCC.CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
					CCC.CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
					--to_char(CCC.\"CmpCallCard_Tper\", 'HH24:MI:SS') as \"CmpCallCard_Tper\",

					TO_CHAR(CCC.CmpCallCard_Tper, 'HH24: MI: SS') as \"CmpCallCard_Tper\",
					--to_char(CCC.\"CmpCallCard_Vyez\", 'HH24:MI:SS') as \"CmpCallCard_Vyez\",

					TO_CHAR(CCC.CmpCallCard_Vyez, 'HH24: MI: SS') as \"CmpCallCard_Vyez\",
					--to_char(CCC.\"CmpCallCard_Przd\", 'HH24:MI:SS') as \"CmpCallCard_Przd\",

					TO_CHAR(CCC.\"CmpCallCard_Przd\", 'HH24: MI: SS') as \"CmpCallCard_Przd\",
					--to_char(CCC.\"CmpCallCard_Tgsp\", 'HH24:MI:SS') as \"CmpCallCard_Tgsp\",

					TO_CHAR(CCC.CmpCallCard_Tgsp, 'HH24: MI: SS') as \"CmpCallCard_Tgsp\",
					--to_char(CCC.\"CmpCallCard_Tsta\", 'HH24:MI:SS') as \"CmpCallCard_Tsta\",

					TO_CHAR(CCC.CmpCallCard_Tsta, 'HH24: MI: SS') as \"CmpCallCard_Tsta\",
					--to_char(CCC.\"CmpCallCard_Tisp\", 'HH24:MI:SS') as \"CmpCallCard_Tisp\",

					TO_CHAR(CCC.\"CmpCallCard_Tisp\", 'HH24: MI: SS') as \"CmpCallCard_Tisp\",
					--to_char(CCC.\"CmpCallCard_Tvzv\", 'HH24:MI:SS') as \"CmpCallCard_Tvzv\",

					TO_CHAR(CCC.CmpCallCard_Tvzv, 'HH24: MI: SS') as \"CmpCallCard_Tvzv\",
					CCC.CmpCallCard_Kilo as \"CmpCallCard_Kilo\",
					CCC.CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
					CCC.CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
					CCC.CmpCallCard_PCity as \"CmpCallCard_PCity\",
					CCC.CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
					CCC.CmpCallCard_PDom as \"CmpCallCard_PDom\",
					CCC.CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
					CCC.cmpCallCard_Medc as \"cmpCallCard_Medc\",
					CCC.CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
					--to_char(CCC.\"CmpCallCard_Tiz1\", 'HH24:MI:SS') as CmpCallCard_Tiz1\",

					TO_CHAR(CCC.\"CmpCallCard_Tiz1\", 'HH24: MI: SS') as \"CmpCallCard_Tiz1\",
					CCC.CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
					CCC.CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
					CCC.CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
					CCC.CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
					CCC.CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
					CCC.CmpCallCard_Inf6 as \"CmpCallCard_Inf6\",
					CCC.Lpu_id as \"Lpu_id\",
					CCC.Lpu_ppdid as \"Lpu_ppdid\",
					COALESCE(L.Lpu_Nick,'') as \"CmpLpu_Name\",
					CASE WHEN OftenCallers_id IS NULL THEN 1 ELSE 2 END as \"Person_isOftenCaller\",

					CCC.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\",
					case when CCC.KLStreet_id IS NULL then
						case when UnformalizedAddressDirectory_id IS NULL THEN NULL
						ELSE 'UA.' || CAST( CCC.UnformalizedAddressDirectory_id as varchar ) END
					else 'ST.' || CAST( CCC.KLStreet_id as varchar ) END as \"StreetAndUnformalizedAddressDirectory_id\",

					CCC.KLRgn_id as \"KLRgn_id\",
					CCC.KLSubRgn_id as \"KLSubRgn_id\",
					CCC.KLCity_id as \"KLCity_id\",
					CCC.KLTown_id as \"KLTown_id\",
					CCC.KLStreet_id as \"KLStreet_id\"
				FROM
					dbo.v_CmpCallCard CCC
					left join dbo.CmpLpu CL on CL.CmpLpu_id = CCC.CmpLpu_id
					-- left join dbo.\"v_PersonState\" PS on PS.\"Person_id\" = CCC.\"Person_id\"
					left join lateral (
						SELECT
							pa.Person_id as Person_id,
							COALESCE(pa.Person_SurName, '') as Person_Surname,
							COALESCE(pa.Person_FirName, '') as Person_Firname,
							COALESCE(pa.Person_SecName, '') as Person_Secname,
							pa.Person_BirthDay as Person_Birthday,
							COALESCE(pa.Sex_id, 0) as Sex_id,
							pa.Person_EdNum as Person_EdNum,
							COALESCE(p.Polis_Num, '') as Polis_Num
						from
							dbo.v_Person_all pa
							left join dbo.v_Polis p on p.Polis_id = pa.Polis_id
						where
							Person_id = CCC.Person_id
							and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
						order by
							PersonEvn_insDT desc
						LIMIT 1
					) PS on true
					LEFT JOIN dbo.v_Lpu L on L.Lpu_id = CCC.CmpLpu_id
					LEFT JOIN dbo.v_OftenCallers OC on OC.Person_id = CCC.Person_id
				WHERE
					CCC.CmpCallCard_id = :CmpCallCard_id
				LIMIT 1
			";
		} else {
			$sql = "
				select
					'' as \"accessType\",
					CCC.CmpCallCard_id as \"CmpCallCard_id\",
					COALESCE(CCC.Person_id, 0) as \"Person_id\",

					CCC.CmpArea_gid as \"CmpArea_gid\",
					CCC.CmpArea_id as \"CmpArea_id\",
					CCC.CmpArea_pid as \"CmpArea_pid\",
					CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\",
					CCC.CmpCallType_id as \"CmpCallType_id\",
					CCC.CmpDiag_aid as \"CmpDiag_aid\",
					CCC.CmpLpu_aid as \"CmpLpu_aid\",
					CCC.CmpLpu_id as \"CmpLpu_id\",
					CL.Lpu_id as \"Lpu_oid\",
					CCC.CmpPlace_id as \"CmpPlace_id\",
					CCC.CmpProfile_bid as \"CmpProfile_bid\",
					CCC.CmpProfile_cid as \"CmpProfile_cid\",
					CCC.CmpReason_id as \"CmpReason_id\",
					CCC.CmpResult_id as \"CmpResult_id\",
					CCC.ResultDeseaseType_id as \"ResultDeseaseType_id\",
					CCC.CmpTalon_id as \"CmpTalon_id\",
					CCC.CmpTrauma_id as \"CmpTrauma_id\",
					CCC.Diag_sid as \"Diag_sid\",
					CCC.Diag_uid as \"Diag_uid\",
					CCC.Sex_id as \"Sex_id\",
					PS.Sex_id as \"SexIdent_id\",
					CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
					CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
					CCC.CmpCallCard_Prty as \"CmpCallCard_Prty\",
					CCC.CmpCallCard_Sect as \"CmpCallCard_Sect\",
					CCC.CmpCallCard_City as \"CmpCallCard_City\",
					CCC.CmpCallCard_Ulic as \"CmpCallCard_Ulic\",
					CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\",
					CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\",
					CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\",
					CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\",
					CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\",
					CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\",
					CmpDiseaseAndAccidentType_id as \"CmpDiseaseAndAccidentType_id\",
					CmpCallReasonType_id as \"CmpCallReasonType_id\",
					CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\",
					CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\",
					RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as \"Person_Surname\",
					RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as \"Person_Firname\",
					RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as \"Person_Secname\",
					RTRIM(LTRIM(COALESCE(PS.Person_Surname, ''))) as \"PersonIdent_Surname\",

					RTRIM(LTRIM(COALESCE(PS.Person_Firname, ''))) as \"PersonIdent_Firname\",

					RTRIM(LTRIM(COALESCE(PS.Person_Secname, ''))) as \"PersonIdent_Secname\",

					to_char(CCC.Person_BirthDay, 'DD.MM.YYYY') as \"Person_Birthday\",

					CCC.Person_Age as \"Person_Age\",
					COALESCE(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), '') as \"PersonIdent_Age\",

					CCC.Person_PolisSer as \"Polis_Ser\",
					CCC.Person_PolisNum as \"Polis_Num\",
					PS.Person_EdNum as \"Polis_EdNum\",
					PS.Polis_Num as \"PolisIdent_Num\",
					CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\",
					CCC.CmpCallerType_id as \"CmpCallerType_id\",
					CCC.CmpCallCard_Smpt as \"CmpCallCard_Smpt\",
					CCC.CmpCallCard_Stan as \"CmpCallCard_Stan\",
					to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY') as \"CmpCallCard_prmDate\",

					to_char(CCC.CmpCallCard_prmDT, 'HH24:MI:SS') as \"CmpCallCard_prmTime\",

					CCC.CmpCallCard_Line as \"CmpCallCard_Line\",
					CCC.CmpCallCard_Numb as \"CmpCallCard_Numb\",
					CCC.CmpCallCard_Smpb as \"CmpCallCard_Smpb\",
					CCC.CmpCallCard_Stbr as \"CmpCallCard_Stbr\",
					CCC.CmpCallCard_Stbb as \"CmpCallCard_Stbb\",
					CCC.CmpCallCard_Ncar as \"CmpCallCard_Ncar\",
					CCC.CmpCallCard_RCod as \"CmpCallCard_RCod\",
					CCC.CmpCallCard_TabN as \"CmpCallCard_TabN\",
					CCC.CmpCallCard_Dokt as \"CmpCallCard_Dokt\",
					CCC.MedPersonal_id as \"MedPersonal_id\",
					COALESCE(CCC.CmpCallCard_IsMedPersonalIdent,1) as \"CmpCallCard_IsMedPersonalIdent\",

					CCC.CmpCallCard_Tab2 as \"CmpCallCard_Tab2\",
					CCC.CmpCallCard_Tab3 as \"CmpCallCard_Tab3\",
					CCC.CmpCallCard_Tab4 as \"CmpCallCard_Tab4\",
					CCC.CmpCallCard_Expo as \"CmpCallCard_Expo\",
					CCC.CmpCallCard_Smpp as \"CmpCallCard_Smpp\",
					CCC.CmpCallCard_Vr51 as \"CmpCallCard_Vr51\",
					CCC.CmpCallCard_D201 as \"CmpCallCard_D201\",
					CCC.CmpCallCard_Dsp1 as \"CmpCallCard_Dsp1\",
					CCC.CmpCallCard_Dsp2 as \"CmpCallCard_Dsp2\",
					CCC.CmpCallCard_Dsp3 as \"CmpCallCard_Dsp3\",
					CCC.CmpCallCard_Dspp as \"CmpCallCard_Dspp\",
					CCC.CmpCallCard_Kakp as \"CmpCallCard_Kakp\",
					CCC.CmpCallCard_Dlit as \"CmpCallCard_Dlit\",
					CCC.CmpCallCard_Prdl as \"CmpCallCard_Prdl\",
					CCC.CmpCallCard_PCity as \"CmpCallCard_PCity\",
					CCC.CmpCallCard_PUlic as \"CmpCallCard_PUlic\",
					CCC.CmpCallCard_PDom as \"CmpCallCard_PDom\",
					CCC.CmpCallCard_PKvar as \"CmpCallCard_PKvar\",
					CCC.cmpCallCard_Medc as \"cmpCallCard_Medc\",
					CCC.CmpCallCard_Izv1 as \"CmpCallCard_Izv1\",
					to_char(CCC.CmpCallCard_Tiz1, 'HH24:MI:SS') as \"CmpCallCard_Tiz1\",

					CCC.CmpCallCard_Inf1 as \"CmpCallCard_Inf1\",
					CCC.CmpCallCard_Inf2 as \"CmpCallCard_Inf2\",
					CCC.CmpCallCard_Inf3 as \"CmpCallCard_Inf3\",
					CCC.CmpCallCard_Inf4 as \"CmpCallCard_Inf4\",
					CCC.CmpCallCard_Inf5 as \"CmpCallCard_Inf5\",
					CCC.CmpCallCard_Inf6 as \"CmpCallCard_Inf6\"
					,CCC.Lpu_id as \"Lpu_id\"
					,CCC.Lpu_ppdid as \"Lpu_ppdid\"
					,COALESCE(L.Lpu_Nick,'') as \"CmpLpu_Name\"

					,CASE WHEN COALESCE(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as \"Person_isOftenCaller\"


					,CCC.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\" 
					,case when COALESCE(CCC.KLStreet_id,0) = 0 then

						case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

						else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
					else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"

					,CCC.KLRgn_id as \"KLRgn_id\"
					,CCC.KLSubRgn_id as \"KLSubRgn_id\"
					,CCC.KLCity_id as \"KLCity_id\"
					,CCC.KLTown_id as \"KLTown_id\"
					,CCC.KLStreet_id as \"KLStreet_id\"
					,CClC.CmpCloseCard_id as \"CmpCloseCard_id\",
					{$varparams}
				from
					v_CmpCallCard CCC 

					left join CmpLpu CL  on CL.CmpLpu_id = CCC.CmpLpu_id

					-- left join v_PersonState PS  on PS.Person_id = CCC.Person_id

					LEFT JOIN LATERAL(

						select
							 pa.Person_id as Person_id
							,COALESCE(pa.Person_SurName, '') as Person_Surname

							,COALESCE(pa.Person_FirName, '') as Person_Firname

							,COALESCE(pa.Person_SecName, '') as Person_Secname

							,pa.Person_BirthDay as Person_Birthday
							,COALESCE(pa.Sex_id, 0) as Sex_id

							,pa.Person_EdNum as Person_EdNum
							,COALESCE(p.Polis_Num, '') as Polis_Num
						from
							v_Person_all pa 

							left join v_Polis p  on p.Polis_id = pa.Polis_id

						where
							Person_id = CCC.Person_id
							and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
						order by
							PersonEvn_insDT desc
                        limit 1
					) PS ON TRUE
					LEFT JOIN v_Lpu L  on L.Lpu_id = CCC.CmpLpu_id

					LEFT JOIN v_OftenCallers OC  on OC.Person_id = CCC.Person_id

					LEFT JOIN {$this->schema}.v_CmpCloseCard CClC  on CCC.CmpCallCard_id = CClC.CmpCallCard_id

				where
					CCC.CmpCallCard_id = :CmpCallCard_id
                limit 1
			";
		}

		$query = $this->db->query( $sql, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * Получение списка диагнозов
	 * @return boolean
	 */
	public function getDiags($data) {
		$top = "";

		if(isset($data['top']) && !empty($data['top']))
			$limit = " limit 100 ";

		$data['where'] = "1 = 1";
		$data['where'] .= " and DiagLevel_id in (4) ";
		$data['where'] .= " and (Diag_begDate is null or Diag_begDate <= dbo.tzGetDate())";
		$data['where'] .= " and (Diag_endDate is null or Diag_endDate >= dbo.tzGetDate())";

		if(isset($data['query'])){
			if (!preg_match('/[а-яА-Я]/', $data['query']))
			{
				$data['where'] .= " and Diag_Code ilike '" .$data['query']. "%'";
			}else{
				$data['where'] .= " and Diag_Name ilike '" .$data['query']. "%'";
			}
		}

		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "
				SELECT
					Diag_id as \"Diag_id\",
					Diag_Code as \"Diag_Code\",
					Diag_Name as \"Diag_Name\",
					Diag_id as \"id\"
				FROM
					dbo.v_Diag
				WHERE
					DiagLevel_id=4
			";
		} else {
			$sql = "
				SELECT
					Diag_id as \"Diag_id\",
					Diag_Code as \"Diag_Code\",
					Diag_Name as \"Diag_Name\",
					Diag_id as \"id\"
				FROM
					v_Diag 

				WHERE					
					".$data['where']."
                ".$limit."
			";
		}

		$query = $this->db->query( $sql );

		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * default desc
	 */
	function getCmpCallCardSmpInfo($data){
		$filter = '(1=1)';
		if ( !empty($data['CmpCallCard_id']) ) {
			$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		} else {
			return false;
		}

		$query = "
			with cmp_waiting_ppd_time = (select COALESCE(
			    (select DS.DataStorage_Value as DataStorage_Value FROM DataStorage DS  where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0 limit 1),
             20));

			select
				-- select
				 CCC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				,PS.Person_id as \"Person_id\"
				,PS.PersonEvn_id as \"PersonEvn_id\"
				,PS.Server_id as \"Server_id\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

				,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

				,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

				,COALESCE(CCC.Person_Age, 0) as \"Person_Age\"

				,CCC.pmUser_insID as \"pmUser_insID\"
				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
				,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\"
				,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"

				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"

				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_||', ' '), '')) as \"CmpLpu_Name\"
				,RTRIM(COALESCE(CD.CmpDiag_Code, '')) as \"CmpDiag_Name\"

				,RTRIM(COALESCE(D.Diag_Code, '')) as \"StacDiag_Name\"

				,CCC.CmpCallCard_prmDT as \"CmpCallCard_prmDT\"
				,SLPU.Lpu_Nick as \"SendLpu_Nick\"
				,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
				,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
				,CCC.Lpu_id as \"Lpu_id\"
				,CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\"
				,to_char (cast(CCC.CmpCallCard_BoostTime as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_BoostTime\"
				,CCC.CmpSecondReason_id as \"CmpSecondReason_id\"
				,RTRIM(case when CSecondR.CmpReason_id is not null then CSecondR.CmpReason_Code||'. ' else '' end || COALESCE(CSecondR.CmpReason_Name, '')) as \"CmpSecondReason_Name\"

				,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
				,CCC.CmpCallerType_id as \"CmpCallerType_id\"
				,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
				,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
				,COALESCE(CPT.CmpCallPlaceType_Code,'') as \"CmpCallPlaceType_Code\"

				,COALESCE(CPT.CmpCallPlaceType_Name,'') as \"CmpCallPlaceType_Name\"

				,CCC.KLCity_id as \"KLCity_id\"
				,City.KLCity_Name as \"KLCity_Name\"
				,CCC.KLTown_id as \"KLTown_id\"
				,Town.KLTown_Name as \"KLTown_Name\"
				,CCC.KLStreet_id as \"KLStreet_id\"
				,Street.KLStreet_Name as \"KLStreet_Name\"
				,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
				,CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\"
				,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
				,CCC.CmpCallCard_Room as \"CmpCallCard_Room\"
				,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
				,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"

				,1 as \"CmpCallCard_isLocked\"
				,COALESCE( RGN.KLRgn_FullName,'') ||

				case when SRGN.KLSubRgn_FullName is not null then ', '||SRGN.KLSubRgn_FullName else ', г.'||City.KLCity_Name end ||
				case when Town.KLTown_FullName is not null then ', '||Town.KLTown_FullName else '' end ||
				case when Street.KLStreet_FullName is not null then ', ул.'||Street.KLStreet_Name else '' end ||
				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Comm is not null then '</br>'||CCC.CmpCallCard_Comm else '' end as \"Adress_Name\"
				
				,case when Street.KLStreet_FullName is not null then 'ул.'||Street.KLStreet_Name else '' end as \"streetValue\"

				--,EPL.Diag_id as \"EPLDiag_id\"

				,case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
						then TO_CHARCOALESCE( * INTERVAL '1 'minute'((select DataStorage_Value as DataStorage_Value from cmp_waiting_ppd_time) - DATEDIFF('minute', CCC.CmpCallCard_updDT,dbo.tzGetDate()),20)  ' ||to_char(0), 'HH24:MI:SS')


						else '00'||':'||'00'
				end as \"PPD_WaitingTime\"

				,case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when COALESCE(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment

						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '||EPLD.diag_FullName else '' end ||
					case when RC.ResultClass_Name is not null then '<br />Результат: '||RC.ResultClass_Name else '' end ||
					case when DT.DirectType_Name is not null then '<br />Направлен: '||DT.DirectType_Name else '' end
				end	as \"PPDResult\"
				,to_char(cast(ServeDT.ServeDT as timestamp(3)), 'DD.MM.YYYY') as \"ServeDT\"

				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name || to_char(cast(CCC.CmpCallCard_updDT as timestamp(3)), 'DD.MM.YYYY') else '' end as \"PPDUser_Name\"


				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then 1
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then CCC.CmpCallCardStatusType_id+1
										when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
										when CCC.CmpCallCardStatusType_id in (6) then 10
										when CCC.CmpCallCardStatusType_id in (5) then 9
										when CCC.CmpCallCardStatusType_id in (3) then 7
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5,6) then CCC.CmpCallCardStatusType_id+4
									end
						END
					else 10
				end as \"Admin_CmpGroup_id\"
				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then '0'||cast (CCC.CmpCallCardStatusType_id+1 as varchar)
										when CCC.CmpCallCardStatusType_id in (4) then '0'||cast (CCC.CmpCallCardStatusType_id as varchar)
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then ('0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar))
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
									end
						END
					else '10'
				end as \"Admin_CmpGroupName_id\"

				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then 1
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN 4
									WHEN CCC.CmpCallCardStatusType_id=6 THEN 10
									WHEN CCC.CmpCallCardStatusType_id=3 THEN 8
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id+1
									ELSE CCC.CmpCallCardStatusType_id+4
								END
							ELSE
								CASE

									WHEN CmpCallCardStatusType_id=4 THEN 7
									WHEN CmpCallCardStatusType_id=3 THEN 8
									ELSE CCC.CmpCallCardStatusType_id+4
								END
							END
					else 9
				end as \"HeadDuty_CmpGroup_id\"
				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '04'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN '0'||cast(CCC.CmpCallCardStatusType_id+1 as varchar)
									ELSE  '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar)
								END
							ELSE
								CASE

									WHEN CmpCallCardStatusType_id=4 THEN '07'
									WHEN CmpCallCardStatusType_id=3 THEN '08'
									WHEN CmpCallCardStatusType_id=6 THEN '10'
									ELSE '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar)
								END
							END
					else '09'
				end as \"HeadDuty_CmpGroupName_id\"


				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN 3
									WHEN CCC.CmpCallCardStatusType_id=6 THEN 9
									WHEN CCC.CmpCallCardStatusType_id=3 THEN 7
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id
									ELSE CCC.CmpCallCardStatusType_id+3
								END
							ELSE
								CASE
									WHEN CmpCallCardStatusType_id=4 THEN 6
									WHEN CmpCallCardStatusType_id=3 THEN 7
									ELSE CCC.CmpCallCardStatusType_id+3
								END
							END
					else 9
				end as \"DispatchDirect_CmpGroup_id\"

				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then 1
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then CCC.CmpCallCardStatusType_id+1
										when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
										when CCC.CmpCallCardStatusType_id in (6) then 10
										when CCC.CmpCallCardStatusType_id in (5) then 9
										when CCC.CmpCallCardStatusType_id in (3) then 7
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5,6) then CCC.CmpCallCardStatusType_id+4
									end
						END
					else 10
				end as \"DispatchCall_CmpGroup_id\"
				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then '0'||cast (CCC.CmpCallCardStatusType_id+1 as varchar)
										when CCC.CmpCallCardStatusType_id in (4) then '0'||cast (CCC.CmpCallCardStatusType_id as varchar)
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then ('0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar))
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
									end
						END
					else '10'
				end as \"DispatchCall_CmpGroupName_id\"

				,case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as \"Owner\"
				,CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\"
				,CCC.CmpCallCard_Tper as \"CmpCallCard_Tper\"
				,CCC.CmpCallCard_Vyez as \"CmpCallCard_Vyez\"
				,CCC.CmpCallCard_Przd as \"CmpCallCard_Przd\"
				,CCC.CmpCallCard_Tgsp as \"CmpCallCard_Tgsp\"
				,CCC.CmpCallCard_Tisp as \"CmpCallCard_Tisp\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) is not null ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) > 12 THEN

                		'1'
                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	'3'
                        ELSE
                        	'2'
                        END
                   	END
			 	ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE '1'
                    END
                END as \"ageUnit_id\"
				,COALESCE(PS.Sex_id, 0) as Sex_id

				-- end select
			from
				-- from
				v_CmpCallCard CCC 

				LEFT JOIN LATERAL(

					select
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus 

					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ServeDT ON TRUE
				LEFT JOIN LATERAL(

					select
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus 

					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as ToDT ON TRUE
				LEFT JOIN LATERAL(

					select
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id as CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name as CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus 

						left join v_CmpMoveFromNmpReason  on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id

					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
					limit 1
				) as CCCStatusHist ON TRUE
				left join v_PersonState PS  on PS.Person_id = CCC.Person_id

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				left join v_CmpReason CSecondR  on CSecondR.CmpReason_id = CCC.CmpSecondReason_id

				left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				left join CmpLpu CL  on CL.CmpLpu_id = CCC.CmpLpu_id

				left join v_Lpu L  on L.Lpu_id = CCC.CmpLpu_id

				left join CmpDiag CD  on CD.CmpDiag_id = CCC.CmpDiag_oid

				left join Diag D  on D.Diag_id = CCC.Diag_sid

				left join v_Lpu SLPU  on SLPU.Lpu_id = CCC.Lpu_ppdid

				LEFT JOIN LATERAL (

					SELECT 
					    evnclass_id as evnclass_id,
                        evnclass_name as evnclass_name,
                        evnpl_id as evnpl_id,
                        evnpl_setdate as evnpl_setdate,
                        evnpl_settime as evnpl_settime,
                        evnpl_disdate as evnpl_disdate,
                        evnpl_distime as evnpl_distime,
                        evnpl_diddate as evnpl_diddate,
                        evnpl_didtime as evnpl_didtime,
                        evnpl_pid as evnpl_pid,
                        evnpl_rid as evnpl_rid,
                        lpu_id as lpu_id,
                        server_id as server_id,
                        personevn_id as personevn_id,
                        evnpl_setdt as evnpl_setdt,
                        evnpl_disdt as evnpl_disdt,
                        evnpl_diddt as evnpl_diddt,
                        evnpl_insdt as evnpl_insdt,
                        evnpl_upddt as evnpl_upddt,
                        evnpl_index as evnpl_index,
                        evnpl_count as evnpl_count,
                        pmuser_insid as pmuser_insid,
                        pmuser_updid as pmuser_updid,
                        person_id as person_id,
                        morbus_id as morbus_id,
                        evnpl_issigned as evnpl_issigned,
                        pmuser_signid as pmuser_signid,
                        evnpl_signdt as evnpl_signdt,
                        evnpl_isarchive as evnpl_isarchive,
                        evnpl_guid as evnpl_guid,
                        evnpl_indexminusone as evnpl_indexminusone,
                        evnstatus_id as evnstatus_id,
                        evnpl_statusdate as evnpl_statusdate,
                        evnpl_istransit as evnpl_istransit,
                        evnpl_vizitcount as evnpl_vizitcount,
                        evnpl_isfinish as evnpl_isfinish,
                        person_age as person_age,
                        evnpl_ismsedirected as evnpl_ismsedirected,
                        evndirection_id as evndirection_id,
                        evnpl_numcard as evnpl_numcard,
                        prehospdirect_id as prehospdirect_id,
                        lpu_did as lpu_did,
                        org_did as org_did,
                        lpusection_did as lpusection_did,
                        diag_did as diag_did,
                        prehosptrauma_id as prehosptrauma_id,
                        evnpl_isunlaw as evnpl_isunlaw,
                        evnpl_isunport as evnpl_isunport,
                        resultclass_id as resultclass_id,
                        evnpl_ukl as evnpl_ukl,
                        directtype_id as directtype_id,
                        directclass_id as directclass_id,
                        lpu_oid as lpu_oid,
                        lpusection_oid as lpusection_oid,
                        diag_id as diag_id,
                        deseasetype_id as deseasetype_id,
                        evndirection_num as evndirection_num,
                        evndirection_setdt as evndirection_setdt,
                        evnpl_isfirsttime as evnpl_isfirsttime,
                        evnpl_complexity as evnpl_complexity,
                        medpersonal_id as medpersonal_id,
                        lpusection_id as lpusection_id,
                        diag_agid as diag_agid,
                        cmpcallcard_id as cmpcallcard_id,
                        resultdeseasetype_id as resultdeseasetype_id,
                        evnpl_isinreg as evnpl_isinreg,
                        evnpl_ispaid as evnpl_ispaid,
                        medicalcarekind_id as medicalcarekind_id,
                        evnpl_iscons as evnpl_iscons,
                        evnpl_indexrep as evnpl_indexrep,
                        evnpl_indexrepinreg as evnpl_indexrepinreg,
                        leavetype_fedid as leavetype_fedid,
                        resultdeseasetype_fedid as resultdeseasetype_fedid,
                        lpu_codesmo as lpu_codesmo,
                        evnpl_firstvizitdt as evnpl_firstvizitdt,
                        evnpl_lastvizitdt as evnpl_lastvizitdt,
                        evnpl_lastuslugadt as evnpl_lastuslugadt,
                        diag_preid as diag_preid,
                        interruptleavetype_id as interruptleavetype_id,
                        diag_concid as diag_concid,
                        server_omsid as server_omsid,
                        personevn_omsid as personevn_omsid,
                        evnvizitpl_omsid as evnvizitpl_omsid,
                        medstafffact_did as medstafffact_did,
                        diag_fid as diag_fid,
                        diag_lid as diag_lid,
                        evnpl_isfirstdisable as evnpl_isfirstdisable,
                        privilegetype_id as privilegetype_id,
                        evnpl_firstvizitcapitadt as evnpl_firstvizitcapitadt,
                        evnpl_firstvizitscapitadt as evnpl_firstvizitscapitadt,
                        evnvizitpl_cid as evnvizitpl_cid,
                        evnvizitpl_scid as evnvizitpl_scid,
                        evnpl_lastuslugacapitadt as evnpl_lastuslugacapitadt,
                        evnpl_lastuslugascapitadt as evnpl_lastuslugascapitadt,
                        evnpl_iscrossed as evnpl_iscrossed,
                        medicalcarebudgtype_id as medicalcarebudgtype_id,
                        evnpl_isinregzno as evnpl_isinregzno,
                        evnpl_issurveyrefuse as evnpl_issurveyrefuse,
                        registry_sid as registry_sid,
                        evnpl_medpersonalcode as evnpl_medpersonalcode,
                        evnpl_firstuslugacapitadt as evnpl_firstuslugacapitadt,
                        evnpl_firstuslugascapitadt as evnpl_firstuslugascapitadt
					FROM v_EvnPL AS t1 

					WHERE t1.CmpCallCard_id = CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
                    LIMIT 1
				) EPL ON TRUE
				/*left join v_EvnPL EPL  on 1=1

					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

				left join v_Diag EPLD  on EPLD.Diag_id = EPL.Diag_id

				left join v_ResultClass RC  on RC.ResultClass_id = EPL.ResultClass_id

				left join v_DirectType DT  on DT.DirectType_id = EPL.DirectType_id

				left join v_pmUserCache PMC  on PMC.PMUser_id = CCC.pmUser_updID


				left join v_CmpCloseCard CLC  on CCC.CmpCallCard_id = CLC.CmpCallCard_id


				left join v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

					and (60 - DATEDIFF('second',CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate())) >0

				left join v_EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id


				left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_CmpCallPlaceType CPT  on CPT.CmpCallPlaceType_id = CCC.CmpCallPlaceType_id

				-- end from
			where
				-- where
				" . $filter . "
				-- end where
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Закрытие карты вызова СМП
	 * @param type $data
	 */
	public function saveCmpCallCardClose($data) {
		//return array(array('success'=>true,'CmpCallCard_id'=>'1086226', 'Error_Code'=>null, 'Error_Msg'=>null));
		if (!isset($data['CmpCallCard_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор талона вызова'));
		}

		//Выбираем все-все поля, чтобы сохранить только изменения, внесённые ДП
		$prevCardQuery = '
			SELECT
				cmpcallcard_id as "cmpcallcard_id",
                cmpcallcard_numv as "cmpcallcard_numv",
                cmpcallcard_ngod as "cmpcallcard_ngod",
                cmpcallcard_prty as "cmpcallcard_prty",
                cmpcallcard_sect as "cmpcallcard_sect",
                cmparea_id as "cmparea_id",
                cmpcallcard_city as "cmpcallcard_city",
                cmpcallcard_ulic as "cmpcallcard_ulic",
                cmpcallcard_dom as "cmpcallcard_dom",
                cmpcallcard_kvar as "cmpcallcard_kvar",
                cmpcallcard_podz as "cmpcallcard_podz",
                cmpcallcard_etaj as "cmpcallcard_etaj",
                cmpcallcard_kodp as "cmpcallcard_kodp",
                cmpcallcard_telf as "cmpcallcard_telf",
                cmpplace_id as "cmpplace_id",
                cmpcallcard_comm as "cmpcallcard_comm",
                cmpreason_id as "cmpreason_id",
                person_id as "person_id",
                person_surname as "person_surname",
                person_firname as "person_firname",
                person_secname as "person_secname",
                person_age as "person_age",
                person_birthday as "person_birthday",
                person_polisser as "person_polisser",
                person_polisnum as "person_polisnum",
                sex_id as "sex_id",
                cmpcallcard_ktov as "cmpcallcard_ktov",
                cmpcalltype_id as "cmpcalltype_id",
                cmpprofile_cid as "cmpprofile_cid",
                cmpcallcard_smpt as "cmpcallcard_smpt",
                cmpcallcard_stan as "cmpcallcard_stan",
                cmpcallcard_prmdt as "cmpcallcard_prmdt",
                cmpcallcard_line as "cmpcallcard_line",
                cmpresult_id as "cmpresult_id",
                cmparea_gid as "cmparea_gid",
                cmplpu_id as "cmplpu_id",
                cmpdiag_oid as "cmpdiag_oid",
                cmpdiag_aid as "cmpdiag_aid",
                cmptrauma_id as "cmptrauma_id",
                cmpcallcard_isalco as "cmpcallcard_isalco",
                diag_uid as "diag_uid",
                cmpcallcard_numb as "cmpcallcard_numb",
                cmpcallcard_smpb as "cmpcallcard_smpb",
                cmpcallcard_stbr as "cmpcallcard_stbr",
                cmpcallcard_stbb as "cmpcallcard_stbb",
                cmpprofile_bid as "cmpprofile_bid",
                cmpcallcard_ncar as "cmpcallcard_ncar",
                cmpcallcard_rcod as "cmpcallcard_rcod",
                cmpcallcard_tabn as "cmpcallcard_tabn",
                cmpcallcard_dokt as "cmpcallcard_dokt",
                cmpcallcard_tab2 as "cmpcallcard_tab2",
                cmpcallcard_tab3 as "cmpcallcard_tab3",
                cmpcallcard_tab4 as "cmpcallcard_tab4",
                diag_sid as "diag_sid",
                cmptalon_id as "cmptalon_id",
                cmpcallcard_expo as "cmpcallcard_expo",
                cmpcallcard_smpp as "cmpcallcard_smpp",
                cmpcallcard_vr51 as "cmpcallcard_vr51",
                cmpcallcard_d201 as "cmpcallcard_d201",
                cmpcallcard_dsp1 as "cmpcallcard_dsp1",
                cmpcallcard_dsp2 as "cmpcallcard_dsp2",
                cmpcallcard_dspp as "cmpcallcard_dspp",
                cmpcallcard_dsp3 as "cmpcallcard_dsp3",
                cmpcallcard_kakp as "cmpcallcard_kakp",
                cmpcallcard_tper as "cmpcallcard_tper",
                cmpcallcard_vyez as "cmpcallcard_vyez",
                cmpcallcard_przd as "cmpcallcard_przd",
                cmpcallcard_tgsp as "cmpcallcard_tgsp",
                cmpcallcard_tsta as "cmpcallcard_tsta",
                cmpcallcard_tisp as "cmpcallcard_tisp",
                cmpcallcard_tvzv as "cmpcallcard_tvzv",
                cmpcallcard_kilo as "cmpcallcard_kilo",
                cmpcallcard_dlit as "cmpcallcard_dlit",
                cmpcallcard_prdl as "cmpcallcard_prdl",
                cmparea_pid as "cmparea_pid",
                cmpcallcard_pcity as "cmpcallcard_pcity",
                cmpcallcard_pulic as "cmpcallcard_pulic",
                cmpcallcard_pdom as "cmpcallcard_pdom",
                cmpcallcard_pkvar as "cmpcallcard_pkvar",
                cmplpu_aid as "cmplpu_aid",
                cmpcallcard_ispoli as "cmpcallcard_ispoli",
                cmpcallcard_medc as "cmpcallcard_medc",
                cmpcallcard_izv1 as "cmpcallcard_izv1",
                cmpcallcard_tiz1 as "cmpcallcard_tiz1",
                cmpcallcard_inf1 as "cmpcallcard_inf1",
                cmpcallcard_inf2 as "cmpcallcard_inf2",
                cmpcallcard_inf3 as "cmpcallcard_inf3",
                cmpcallcard_inf4 as "cmpcallcard_inf4",
                cmpcallcard_inf5 as "cmpcallcard_inf5",
                cmpcallcard_inf6 as "cmpcallcard_inf6",
                pmuser_insid as "pmuser_insid",
                pmuser_updid as "pmuser_updid",
                cmpcallcard_insdt as "cmpcallcard_insdt",
                cmpcallcard_upddt as "cmpcallcard_upddt",
                klrgn_id as "klrgn_id",
                klsubrgn_id as "klsubrgn_id",
                klcity_id as "klcity_id",
                kltown_id as "kltown_id",
                klstreet_id as "klstreet_id",
                lpu_ppdid as "lpu_ppdid",
                cmpcallcard_isemergency as "cmpcallcard_isemergency",
                cmpcallcard_isopen as "cmpcallcard_isopen",
                cmpcallcardstatustype_id as "cmpcallcardstatustype_id",
                cmpcallcardstatus_comment as "cmpcallcardstatus_comment",
                cmpcallcard_isreceivedinppd as "cmpcallcard_isreceivedinppd",
                cmpppdresult_id as "cmpppdresult_id",
                emergencyteam_id as "emergencyteam_id",
                cmpcallcard_isinreg as "cmpcallcard_isinreg",
                lpu_id as "lpu_id",
                cmpcallcard_ismedpersonalident as "cmpcallcard_ismedpersonalident",
                medpersonal_id as "medpersonal_id",
                resultdeseasetype_id as "resultdeseasetype_id",
                cmpcallcard_firstversion as "cmpcallcard_firstversion",
                unformalizedaddressdirectory_id as "unformalizedaddressdirectory_id",
                cmpcallcard_ispaid as "cmpcallcard_ispaid",
                cmpcallcard_korp as "cmpcallcard_korp",
                cmpcallcard_room as "cmpcallcard_room",
                cmpcallcard_difftime as "cmpcallcard_difftime",
                uslugacomplex_id as "uslugacomplex_id",
                lpubuilding_id as "lpubuilding_id",
                cmpcallertype_id as "cmpcallertype_id",
                cmpcallplacetype_id as "cmpcallplacetype_id",
                cmpcallcard_rid as "cmpcallcard_rid",
                cmpcallcard_urgency as "cmpcallcard_urgency",
                cmpcallcard_boosttime as "cmpcallcard_boosttime",
                cmpsecondreason_id as "cmpsecondreason_id",
                cmpdiseaseandaccidenttype_id as "cmpdiseaseandaccidenttype_id",
                cmpcallreasontype_id as "cmpcallreasontype_id",
                cmpreasonnew_id as "cmpreasonnew_id",
                cmpcallcard_emergencyteamdiscardreason as "cmpcallcard_emergencyteamdiscardreason",
                cmpcallcard_indexrep as "cmpcallcard_indexrep", 
                cmpcallcard_indexrepinreg as "cmpcallcard_indexrepinreg",
                cmpcallcard_isarchive as "cmpcallcard_isarchive",
                medstafffact_id as "medstafffact_id",
                rankinscale_id as "rankinscale_id",
                rankinscale_sid as "rankinscale_sid",
                leavetype_id as "leavetype_id",
                cmpcallcard_isshorteditversion as "cmpcallcard_isshorteditversion",
                lpusection_id as "lpusection_id",
                cmpcallcard_recomendations as "cmpcallcard_recomendations",
                cmpcallcard_condition as "cmpcallcard_condition",
                lpu_cid as "lpu_cid",
                cmpcallcard_tend as "cmpcallcard_tend",
                cmpcallcard_callltd as "cmpcallcard_callltd",
                cmpcallcard_calllng as "cmpcallcard_calllng",
                cmpcallcard_isnmp as "cmpcallcard_isnmp",
                cmprejectionreason_id as "cmprejectionreason_id",
                cmpcallcard_hospitalizedtime as "cmpcallcard_hospitalizedtime",
                cmpcallcard_savedt as "cmpcallcard_savedt",
                cmpcallcard_plandt as "cmpcallcard_plandt",
                cmpcallcard_factdt as "cmpcallcard_factdt",
                cmpcallcardinputtype_id as "cmpcallcardinputtype_id",
                cmpcallcard_isextra as "cmpcallcard_isextra",
                cmpcallcardstatus_id as "cmpcallcardstatus_id",
                cmpcallcard_guid as "cmpcallcard_guid",
                cmpcallcard_rguid as "cmpcallcard_rguid",
                cmpcallcard_firstversionguid as "cmpcallcard_firstversionguid",
                cmpcallcardstatus_guid as "cmpcallcardstatus_guid",
                emergencyteam_guid as "emergencyteam_guid",
                cmpcallcard_stordt as "cmpcallcard_stordt",
                cmpcallcard_defcom as "cmpcallcard_defcom",
                medservice_id as "medservice_id",
                cmpcallcard_polisednum as "cmpcallcard_polisednum",
                cmpcallcard_isdeterior as "cmpcallcard_isdeterior",
                diag_sopid as "diag_sopid",
                cmpleavetype_id as "cmpleavetype_id",
                cmpleavetask_id as "cmpleavetask_id",
                cmpmedicalcarekind_id as "cmpmedicalcarekind_id",
                cmptransporttype_id as "cmptransporttype_id",
                cmpresultdeseasetype_id as "cmpresultdeseasetype_id",
                cmpcallcardresult_id as "cmpcallcardresult_id",
                person_isunknown as "person_isunknown",
                cmpcallcard_ispassssmp as "cmpcallcard_ispassssmp",
                lpu_smpid as "lpu_smpid",
                lpu_hid as "lpu_hid",
                unformalizedaddressdirectory_wid as "unformalizedaddressdirectory_wid",
                paytype_id as "paytype_id",
                cmpcallcard_ulicsecond as "cmpcallcard_ulicsecond",
                cmpcallcard_sid as "cmpcallcard_sid",
                cmpcallcard_isactivecall as "cmpcallcard_isactivecall",
                cmpcallcard_iscontrolcall as "cmpcallcard_iscontrolcall",
                cmpcallcard_istimeexceeded as "cmpcallcard_istimeexceeded",
                cmpcallcard_numvpr as "cmpcallcard_numvpr",
                cmpcallcard_ngodpr as "cmpcallcard_ngodpr",
                cmpcallsigntype_id as "cmpcallsigntype_id",
                lpu_codesmo as "lpu_codesmo",
                registry_sid as "registry_sid",
                diag_gid as "diag_gid",
                medicalcarebudgtype_id as "medicalcarebudgtype_id",
                cmpcommonstate_id as "cmpcommonstate_id"
			FROM
				v_CmpCallCard 

			WHERE
				CmpCallCard_id = :CmpCallCard_id
			';

		$prevCardResult = $this->db->query($prevCardQuery,array(
			'CmpCallCard_id'=>$data['CmpCallCard_id']
		));
		if (!is_object($prevCardResult)) {
			return false;
		}
		else {
			$prevCardResult = $prevCardResult->result('array');
			if (!isset($prevCardResult[0])) {
				return array(array('Error_Msg'=>'Талон вызова был удалён'));
			}
		}

		$prevCardData = $prevCardResult[0];
		//Логика сохранения та же, что при редактировании: сохраняем старый талон новой записью,
		//талон с отредактированными данными сохраняем в старой записи

		$saveRecordQuery = "
            select 
                CmpCallCard_id as \"CmpCallCard_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {procedure} (
                CmpCallCard_id := :CmpCallCard_id,
                CmpCallCard_Numv := :CmpCallCard_Numv,
                CmpCallCard_Ngod  := :CmpCallCard_Ngod ,
                CmpCallCard_Prty  :=:CmpCallCard_Prty ,
                CmpCallCard_Sect  :=:CmpCallCard_Sect ,
                CmpArea_id  :=:CmpArea_id ,
                CmpCallCard_City  :=:CmpCallCard_City ,
                CmpCallCard_Ulic :=:CmpCallCard_Ulic ,
                CmpCallCard_Dom  :=:CmpCallCard_Dom ,
                CmpCallCard_Kvar :=:CmpCallCard_Kvar ,
                CmpCallCard_Podz  :=:CmpCallCard_Podz ,
                CmpCallCard_Etaj  :=:CmpCallCard_Etaj ,
                CmpCallCard_Kodp  :=:CmpCallCard_Kodp ,
                CmpCallCard_Telf  :=:CmpCallCard_Telf ,
                CmpPlace_id :=:CmpPlace_id ,
                CmpCallCard_Comm  :=:CmpCallCard_Comm ,
                CmpReason_id :=:CmpReason_id ,
                Person_id :=:Person_id ,
                Person_SurName :=:Person_SurName ,
                Person_FirName :=:Person_FirName ,
                Person_SecName :=:Person_SecName ,
                Person_Age :=:Person_Age ,
                Person_BirthDay := :Person_BirthDay ,
                Person_PolisSer :=:Person_PolisSer ,
                Person_PolisNum :=:Person_PolisNum ,
                Sex_id :=:Sex_id ,
                CmpCallCard_Ktov :=:CmpCallCard_Ktov,
                CmpCallType_id :=:CmpCallType_id ,
                CmpProfile_cid :=:CmpProfile_cid ,
                CmpCallCard_Smpt :=:CmpCallCard_Smpt ,
                CmpCallCard_Stan :=:CmpCallCard_Stan ,
                CmpCallCard_prmDT :=:CmpCallCard_prmDT ,
                CmpCallCard_Line :=:CmpCallCard_Line,
                CmpResult_id :=:CmpResult_id ,
                CmpArea_gid :=:CmpArea_gid ,
                CmpLpu_id :=:CmpLpu_id ,
                CmpDiag_oid :=:CmpDiag_oid ,
                CmpDiag_aid :=:CmpDiag_aid ,
                CmpTrauma_id :=:CmpTrauma_id ,
                CmpCallCard_IsAlco :=:CmpCallCard_IsAlco ,
                Diag_uid :=:Diag_uid ,
                CmpCallCard_Numb :=:CmpCallCard_Numb ,
                CmpCallCard_Smpb :=:CmpCallCard_Smpb ,
                CmpCallCard_Stbr :=:CmpCallCard_Stbr ,
                CmpCallCard_Stbb :=:CmpCallCard_Stbb ,
                CmpProfile_bid :=:CmpProfile_bid ,
                CmpCallCard_Ncar :=:CmpCallCard_Ncar ,
                CmpCallCard_RCod :=:CmpCallCard_RCod ,
                CmpCallCard_TabN :=:CmpCallCard_TabN ,
                CmpCallCard_Dokt :=:CmpCallCard_Dokt ,
                CmpCallCard_Tab2 :=:CmpCallCard_Tab2 ,
                CmpCallCard_Tab3 :=:CmpCallCard_Tab3 ,
                CmpCallCard_Tab4 :=:CmpCallCard_Tab4 ,
                Diag_sid :=:Diag_sid ,
                CmpTalon_id :=:CmpTalon_id ,
                CmpCallCard_Expo :=:CmpCallCard_Expo ,
                CmpCallCard_Smpp :=:CmpCallCard_Smpp ,
                CmpCallCard_Vr51 :=:CmpCallCard_Vr51 ,
                CmpCallCard_D201 :=:CmpCallCard_D201 ,
                CmpCallCard_Dsp1 :=:CmpCallCard_Dsp1 ,
                CmpCallCard_Dsp2 :=:CmpCallCard_Dsp2 ,
                CmpCallCard_Dspp :=:CmpCallCard_Dspp ,
                CmpCallCard_Dsp3 :=:CmpCallCard_Dsp3 ,
                CmpCallCard_Kakp :=:CmpCallCard_Kakp ,
                CmpCallCard_Tper :=:CmpCallCard_Tper ,
                CmpCallCard_Vyez :=:CmpCallCard_Vyez ,
                CmpCallCard_Przd :=:CmpCallCard_Przd ,
                CmpCallCard_Tgsp :=:CmpCallCard_Tgsp ,
                CmpCallCard_Tsta :=:CmpCallCard_Tsta ,
                CmpCallCard_Tisp :=:CmpCallCard_Tisp ,
                CmpCallCard_Tvzv :=:CmpCallCard_Tvzv ,
                CmpCallCard_Kilo :=:CmpCallCard_Kilo ,
                CmpCallCard_Dlit :=:CmpCallCard_Dlit ,
                CmpCallCard_Prdl :=:CmpCallCard_Prdl ,
                CmpArea_pid :=:CmpArea_pid ,
                CmpCallCard_PCity :=:CmpCallCard_PCity ,
                CmpCallCard_PUlic :=:CmpCallCard_PUlic ,
                CmpCallCard_PDom :=:CmpCallCard_PDom ,
                CmpCallCard_PKvar :=:CmpCallCard_PKvar ,
                CmpLpu_aid :=:CmpLpu_aid ,
                CmpCallCard_IsPoli :=:CmpCallCard_IsPoli ,
                cmpCallCard_Medc :=:cmpCallCard_Medc ,
                CmpCallCard_Izv1 :=:CmpCallCard_Izv1 ,
                CmpCallCard_Inf1 :=:CmpCallCard_Inf1 ,
                CmpCallCard_Inf2 :=:CmpCallCard_Inf2 ,
                CmpCallCard_Inf3 :=:CmpCallCard_Inf3 ,
                CmpCallCard_Inf4 :=:CmpCallCard_Inf4 ,
                CmpCallCard_Inf5 :=:CmpCallCard_Inf5,
                CmpCallCard_Inf6 :=:CmpCallCard_Inf6 ,
                KLRgn_id :=:KLRgn_id ,
                KLSubRgn_id :=:KLSubRgn_id ,
                KLCity_id :=:KLCity_id ,
                KLTown_id :=:KLTown_id ,
                KLStreet_id :=:KLStreet_id ,
                Lpu_ppdid :=:Lpu_ppdid ,
                CmpCallCard_IsEmergency :=:CmpCallCard_IsEmergency ,
                CmpCallCard_IsOpen :=:CmpCallCard_IsOpen ,
                CmpCallCard_IsReceivedInPPD :=:CmpCallCard_IsReceivedInPPD ,
                CmpPPDResult_id :=:CmpPPDResult_id ,
                Lpu_id :=:Lpu_id ,
                CmpCallCard_IsMedPersonalIdent :=:CmpCallCard_IsMedPersonalIdent ,
                MedPersonal_id :=:MedPersonal_id,
                ResultDeseaseType_id :=:ResultDeseaseType_id ,
                UnformalizedAddressDirectory_id :=:UnformalizedAddressDirectory_id ,
                CmpCallCard_Korp :=:CmpCallCard_Korp ,
                CmpCallCard_Room :=:CmpCallCard_Room ,
                UslugaComplex_id :=:UslugaComplex_id ,
                LpuBuilding_id :=:LpuBuilding_id ,
                CmpCallerType_id :=:CmpCallerType_id ,:
                CmpCallPlaceType_id :=:CmpCallPlaceType_id ,
        
                CmpCallCard_Urgency :=:CmpCallCard_Urgency ,
                CmpCallCard_BoostTime :=:CmpCallCard_BoostTime ,
                CmpSecondReason_id :=:CmpSecondReason_id ,
                CmpDiseaseAndAccidentType_id :=:CmpDiseaseAndAccidentType_id ,
                CmpCallReasonType_id :=:CmpCallReasonType_id ,
                pmUser_id := :pmUser_id,
                CmpCallCard_rid :=:CmpCallCard_rid ,
        
                --begin fields that not in _upd
                CmpCallCardStatusType_id :=:CmpCallCardStatusType_id ,
                CmpCallCardStatus_Comment :=:CmpCallCardStatus_Comment ,
                EmergencyTeam_id :=:EmergencyTeam_id ,
                CmpCallCard_IsNMP :=:CmpCallCard_IsNMP ,
                CmpCallCard_DiffTime :=:CmpCallCard_DiffTime ,
                CmpCallCard_firstVersion :=:CmpCallCard_firstVersion
			)
			";

		$savePrevRecordQuery = str_replace('{procedure}', 'p_CmpCallCard_ins', $saveRecordQuery);

		$prevCardData['CmpCallCard_id'] = NULL;
		$prevCardData['pmUser_id'] = $data['pmUser_id'];

		$this->db->trans_begin();

		$savePrevRecordResult = $this->db->query($savePrevRecordQuery,$prevCardData);

		if (!is_object($savePrevRecordResult)) {
			$this->db->trans_rollback();
			return false;
		}
		$savePrevRecordResult = $savePrevRecordResult->result('array');

		if (strlen($savePrevRecordResult[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $savePrevRecordResult;
		}

		//Заменим в старом талоне те поля, которые были отредактированы ДП. Для понимания перенесем данные в новую переменную
		$newCardData = $prevCardData;

		$newCardData['CmpCallCard_firstVersion'] = $savePrevRecordResult[0]['CmpCallCard_id'];
		foreach ($newCardData as $key => $value) {
			if (isset($data[$key])) {
				$newCardData[$key] = $data[$key];
			}
		}

		$saveNewRecordQuery = str_replace('{procedure}', 'p_CmpCallCard_setCardUpd', $saveRecordQuery);
		$saveNewRecordQuery = preg_replace("/--begin\s+([\w\W]*)\s+--end/i", '', $saveNewRecordQuery);

		$saveNewRecordResult = $this->db->query($saveNewRecordQuery,$newCardData);

		if (!is_object($saveNewRecordResult)) {
			$this->db->trans_rollback();
			return false;
		}

		$saveNewRecordResult = $saveNewRecordResult->result('array');

		$query = "
			from p_CmpCallCard_setFirstVersion
			CmpCallCard_id = " . $saveNewRecordResult[0]['CmpCallCard_id'] . ",
			CmpCallCard_firstVersion = " . $savePrevRecordResult[0]['CmpCallCard_id'] . ",
			pmUser_id = " . $data['pmUser_id'] . ";
		";
		$res = $this->db->query($query, $data);

		if (strlen($saveNewRecordResult[0]['Error_Msg'])) {
			$this->db->trans_rollback();
			return $saveNewRecordResult;
		}

		$setStatusResult = $this->setStatusCmpCallCard(array(
			'CmpCallCard_id' => $saveNewRecordResult[0]['CmpCallCard_id'],
			'CmpCallCardStatusType_id' => 6,
			//'CmpCallType_id' => $saveNewRecordResult[0]['CmpCallType_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!$setStatusResult || strlen($setStatusResult[0]['Error_Msg'])>0) {
			$this->db->trans_rollback();
			return $setStatusResult;
		}
		if (!empty($data['EmergencyTeamDrugPackMoveList']) && is_array($data['EmergencyTeamDrugPackMoveList'])) {
			$saveEmergencyTeamDrugPackMoveListResult = $this->saveEmergencyTeamDrugPackMoveList(array(
				'CmpCallCard_id'=>$saveNewRecordResult[0]['CmpCallCard_id'],
				'EmergencyTeamDrugPackMoveList'=>$data['EmergencyTeamDrugPackMoveList'],
				'pmUser_id'=>$data['pmUser_id']
			));

			if (!$this->isSuccessful( $saveEmergencyTeamDrugPackMoveListResult )) {
				$this->db->trans_rollback();
				return $saveEmergencyTeamDrugPackMoveListResult;
			}
		}

		$this->db->trans_commit();
		return $setStatusResult;

		//$this->db->trans_commit();$this->db->trans_rollback();$this->db->trans_begin();
	}
	/**
	 * Сохранение списка медикаментов с укладки на талон вызова
	 * @param type $data
	 * @return type
	 */
	public function saveEmergencyTeamDrugPackMoveList($data) {

		$rules = array(
			array( 'field' => 'CmpCallCard_id' , 'label' => 'Идентификатор карты вызова СМП' , 'rules' => 'required' , 'type' => 'id' ),
			array( 'field' => 'EmergencyTeamDrugPackMoveList' , 'label' => 'Список медикаментов' , 'rules' => 'required' , 'type' => 'array', 'default' => array() ),
			array( 'field' => 'pmUser_id' , 'rules' => 'required' , 'label' => 'Идентификатор пользователя' , 'type' => 'id' ),
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;

		$this->load->model('EmergencyTeam_model4E', 'etmodel');

		$this->beginTransaction();

		if (  is_array( $queryParams['EmergencyTeamDrugPackMoveList'] ) && sizeof( $queryParams['EmergencyTeamDrugPackMoveList'] )) {

			foreach ( $queryParams['EmergencyTeamDrugPackMoveList'] as $EmergencyTeamDrugPackMove ) {
				$EmergencyTeamDrugPackMove['CmpCallCard_id'] = $data['CmpCallCard_id'];
				$EmergencyTeamDrugPackMove['pmUser_id'] = $data['pmUser_id'];
				$EmergencyTeamDrugPackMove['EmergencyTeamDrugPackMove_Quantity'] = (-1)*$EmergencyTeamDrugPackMove['EmergencyTeamDrugPackMove_Quantity'];

				$status = (empty($EmergencyTeamDrugPackMove['status']))?null:$EmergencyTeamDrugPackMove['status'];

				switch ($EmergencyTeamDrugPackMove['status']) {
					case 'added':
					case 'edited':
						if ( empty($EmergencyTeamDrugPackMove['EmergencyTeamDrugPackMove_id']) || $EmergencyTeamDrugPackMove['EmergencyTeamDrugPackMove_id'] <= 0 ) {
							$EmergencyTeamDrugPackMove['EmergencyTeamDrugPackMove_id'] = null;
						}

						$saveEmergencyTeamDrugPackMoveResult = $this->etmodel->saveEmergencyTeamDrugPackMove($EmergencyTeamDrugPackMove);

						break;
					case 'deleted':
						$saveEmergencyTeamDrugPackMoveResult = $this->etmodel->deleteEmergencyTeamPackMove($EmergencyTeamDrugPackMove);
						break;

				}
				if (!$this->isSuccessful( $saveEmergencyTeamDrugPackMoveResult)) {
					$this->rollbackTransaction();
					return $saveEmergencyTeamDrugPackMoveResult;
				}
			}

		}

		$this->commitTransaction();

		return array(array('success'=>true, 'Error_Msg'=>false));
	}


	/**
	 * Печать контрольного талона закрытого вызова
	 */
	public function printCmpCallCardCloseTicket($data) {
		$query = "
			SELECT
				---------------------- Доп инфо --------------------------------
				CCC.CmpReason_id as \"CmpReason_id\"
				,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"

				---------------------- БЛОК 1 --------------------------------

				-- р-н
				,RGN.KLRgn_Name as \"KLRgn_Name\"

				--пункт
				,case when Town.KLTown_FullName is not null
					then Town.KLTown_FullName
					else COALESCE(City.KLCity_Name,'')

				end as \"CmpCallCard_PunktViezd\"

				-- улица
				,case when Street.KLStreet_FullName is not null
					then Street.KLStreet_Name
					else  COALESCE(UAD.UnformalizedAddressDirectory_Name,'')

				end as \"CmpCallCard_Street\"

				--дом, квартира,...
				,COALESCE(CCC.CmpCallCard_Dom,'') as \"CmpCallCard_Dom\"

				,COALESCE(CCC.CmpCallCard_Kvar,'') as \"CmpCallCard_Kvar\"

				,COALESCE(CCC.CmpCallCard_Podz,'') as \"CmpCallCard_Podz\"

				,COALESCE(CCC.CmpCallCard_Etaj,'') as \"CmpCallCard_Etaj\"

				,COALESCE(CCC.CmpCallCard_Kodp,'') as \"CmpCallCard_Kodp\"

				,COALESCE(CCC.CmpCallCard_Telf,'') as \"CmpCallCard_Telf\"


				-- код, наим. типа места вызова
				,COALESCE(CPT.CmpCallPlaceType_Code,'') as \"CmpCallPlaceType_Code\"

				,COALESCE(CPT.CmpCallPlaceType_Name,'') as \"CmpCallPlaceType_Name\"


				-- допинфо
				,COALESCE(CCC.CmpCallCard_Comm,'') as \"CmpCallCard_Comm\"


				-- код, наим. повода
				,COALESCE(CR.CmpReason_Code,'') as \"CmpReason_Code\"

				,COALESCE(CR.CmpReason_Name,'') as \"CmpReason_Name\"


				-- пол
				,case
					when COALESCE(Sc.Sex_Code,COALESCE(Sp.Sex_Code,0)) = 1 then 'М'

					when COALESCE(Sc.Sex_Code,COALESCE(Sp.Sex_Code,0)) = 2 then 'Ж'

					when COALESCE(Sc.Sex_Code,COALESCE(Sp.Sex_Code,0)) = 3 then 'Н/О'

					else ''
				end as \"Sex_Code\",

				-- фио пациента
				COALESCE(PS.Person_Surname,CCC.Person_SurName,'') as \"Person_Surname\",
				COALESCE(PS.Person_Firname,CCC.Person_FirName,'') as \"Person_Firname\",
				COALESCE(PS.Person_Secname,CCC.Person_SecName,'') as \"Person_Secname\",

				-- возраст
				COALESCE(CCC.Person_Age,0) as \"Person_Age\",


				-- вызывающий
				COALESCE(CClrT.CmpCallerType_Name,CCC.CmpCallCard_Ktov,'') as \"CmpCallCard_Ktov\",
				CClrT.CmpCallerType_Name as \"CmpCallerType_Name\",

				---------------------- БЛОК 2 --------------------------------

				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,COALESCE(CCT.CmpCallType_Name,'') as \"CmpCallType_Name\"

				,CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\"
				,COALESCE(MS_CCC.MedService_Nick,'') as \"MedService_Nick\"

				---------------------- БЛОК 3 --------------------------------
				,to_char(CCC.CmpCallCard_insDT, 'DD.MM.YYYY') as \"CmpCallCard_prmDate\"

				,to_char(CCC.CmpCallCard_insDT, 'HH24:MI:SS') as \"CmpCallCard_prmTime\"

				,case when DATEPART (dw,CCC.CmpCallCard_insDT)-1 = 0 then 7 else DATEPART (dw,CCC.CmpCallCard_insDT)-1 end as \"CmpCallCard_Weekday\"
				,to_char (COALESCE(CCCStoDP.CmpCallCardStatus_insDT,CCCStoDD.CmpCallCardStatus_insDT), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_Tper\"

				,to_char(CCC.CmpCallCard_Tisp, 'HH24:MI:SS') as \"CmpCallCard_Tisp\"




				---------------------- БЛОК 4 --------------------------------
				,COALESCE(CCRT.CmpCallReasonType_Name,'') as \"CmpCallReasonType_Name\"

				,COALESCE(CCRT.CmpCallReasonType_Code,'') as \"CmpCallReasonType_Code\"

				,COALESCE(CDAAT.CmpDiseaseAndAccidentType_Name,'') as \"CmpDiseaseAndAccidentType_Name\"

				,COALESCE(CDAAT.CmpDiseaseAndAccidentType_Code,'') as \"CmpDiseaseAndAccidentType_Code\"

				,COALESCE(Lt.Lpu_Nick,'') as \"LpuTransmit_Nick\"

				,COALESCE(Do.Diag_Name,'') as \"CmpDiagFirst_Name\"

				,COALESCE(Do.Diag_Code,'') as \"CmpDiagFirst_Code\"

				,COALESCE(Da.Diag_Name,'') as \"CmpDiagSecond_Name\"

				,COALESCE(Da.Diag_Code,'') as \"CmpDiagSecond_Code\"

				,COALESCE(Alco.YesNo_Name,'') as \"CmpDiagSecond_isAlco\"


				---------------------- БЛОК 5 --------------------------------
				,COALESCE(ET.EmergencyTeam_Num,'') as \"EmergencyTeam_Num\"

				,COALESCE(L_ET.Lpu_Nick,'') as \"EmergencyTeam_Lpu_Nick\"

				,COALESCE(MS_ET.MedService_Nick,'') as \"EmergencyTeam_MedService_Nick\"

				,COALESCE(ETS.EmergencyTeamSpec_Code,'') as \"EmergencyTeamSpec_Code\"

				,COALESCE(ETS.EmergencyTeamSpec_Name,'') as \"EmergencyTeamSpec_Name\"

				,COALESCE(ET.EmergencyTeam_CarNum,'') as \"EmergencyTeam_CarNum\"

				,COALESCE(ET.EmergencyTeam_PortRadioNum,'') as \"EmergencyTeam_PortRadioNum\"


				,COALESCE(MP_HS.MedPersonal_Code,'') as \"EmergencyTeam_HeadShift_Code\"

				,RTRIM(MP_HS.Person_SurName||' '||SUBSTRING(MP_HS.Person_FirName,1,1) ||' '||SUBSTRING(MP_HS.Person_SecName,1,1)) as \"EmergencyTeam_HeadShift_FIO\"
				,COALESCE(MP_A1.MedPersonal_Code,'') as \"EmergencyTeam_Assistant1_Code\"

				,COALESCE(MP_A2.MedPersonal_Code,'') as \"EmergencyTeam_Assistant2_Code\"

				,COALESCE(MP_D.MedPersonal_Code,'') as \"EmergencyTeam_Driver_Code\"


				---------------------- БЛОК 6 --------------------------------
				,COALESCE(MP_DC.MedPersonal_Code,'') as \"DispatchCall_MedPersonal_Code\"

				,COALESCE(MP_DC.MedPersonal_FIO,'') as \"DispatchCall_MedPersonal_FIO\"

				,COALESCE(MP_DD.MedPersonal_Code,'') as \"DispatchDirect_MedPersonal_Code\"

				,COALESCE(MP_DD.MedPersonal_FIO,'') as \"DispatchDirect_MedPersonal_FIO\"

				,COALESCE(MP_DP.MedPersonal_Code,'') as \"DispatchStation_MedPersonal_Code\"

				,COALESCE(MP_DP.MedPersonal_FIO,'') as \"DispatchStation_MedPersonal_FIO\"

				,COALESCE(CCC.CmpCallCard_Kakp,0) as \"CmpCallCard_Kakp\"

				---------------------- БЛОК 7 --------------------------------
				,to_char(CCC.CmpCallCard_Vyez, 'HH24:MI:SS') as \"CmpCallCard_Vyez\"

				,to_char(CCC.CmpCallCard_Przd, 'HH24:MI:SS') as \"CmpCallCard_Przd\"

				,to_char(CCC.CmpCallCard_Tgsp, 'HH24:MI:SS') as \"CmpCallCard_Tgsp\"

				,to_char(CCC.CmpCallCard_Tsta, 'HH24:MI:SS') as \"CmpCallCard_Tsta\"

				--,to_char(CCC.CmpCallCard_Tisp, 'HH24:MI:SS') as \"CmpCallCard_Tisp\"

				,to_char(CCC.CmpCallCard_Tvzv, 'HH24:MI:SS') as \"CmpCallCard_Tvzv\"

				,to_char (CCC.CmpCallCard_Kilo) as \"CmpCallCard_Kilo\"

				---------------------- БЛОК 8 --------------------------------

				,COALESCE(A.KLRgn_Name ,'') as \"PersonAddress_RgnName\"

                ,case when A.KLTown_Name is not null
                	then A.KLTown_Name
                    else A.KLCity_Name
                end as \"PersonAddress_Punkt\"
				,COALESCE(A.KLStreet_Name ,'') as \"PersonAddress_StreetName\"

				,COALESCE(A.Address_Flat,'') as \"PersonAddress_Kvar\"

				,COALESCE(A.Address_House,'') as \"PersonAddress_House\"

				,COALESCE(PS.Person_EdNum,'') as \"Person_EdNum\"

				,COALESCE(Lpers.Lpu_Nick,'' ) as \"Person_LpuAttach_Nick\"


			FROM
				v_CmpCallCard CCC 

				LEFT JOIN v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id


				left join v_PersonState_all PS  on PS.Person_id = CCC.Person_id

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				--left join v_CmpReason CSecondR  on CSecondR.CmpReason_id = CCC.CmpSecondReason_id

				left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				left join v_Lpu L  on L.Lpu_id = CCC.CmpLpu_id

				left join CmpDiag CD  on CD.CmpDiag_id = CCC.CmpDiag_oid

				left join Diag D  on D.Diag_id = CCC.Diag_sid

				left join v_Lpu SLPU  on SLPU.Lpu_id = CCC.Lpu_ppdid

				left join v_pmUserCache PMC  on PMC.PMUser_id = CCC.pmUser_updID

				left join v_KLRgn RGN   on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN   on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_CmpCallPlaceType CPT  on CPT.CmpCallPlaceType_id = CCC.CmpCallPlaceType_id

				left join v_CmpCallerType CClrT  on CClrT.CmpCallerType_id = CCC.CmpCallerType_id

				left join v_Sex Sp  on Sp.Sex_id = PS.Sex_id

				left join v_Sex Sc  on Sc.Sex_id = PS.Sex_id

				left join v_CmpCallReasonType CCRT  on CCRT.CmpCallReasonType_id = CCC.CmpCallReasonType_id

				left join v_CmpDiseaseAndAccidentType CDAAT  on CDAAT.CmpDiseaseAndAccidentType_id = CCC.CmpDiseaseAndAccidentType_id

				left join v_Lpu Lt  on Lt.Lpu_id = CCC.CmpLpu_id

				left join v_Diag Do  on Do.Diag_id = CCC.Diag_gid --1

				left join v_Diag Da  on Da.Diag_id = CCC.CmpDiag_aid --2

				left join v_YesNo Alco  on Alco.YesNo_id = CCC.CmpCallCard_IsAlco

                left join v_lpu L_ET  on L_ET.Lpu_id = ET.Lpu_id

				left join v_EmergencyTeamSpec ETS  on ETS.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id

				left join v_MedPersonal MP_HS  on ET.EmergencyTeam_HeadShift = MP_HS.MedPersonal_id

				left join v_MedPersonal MP_A1  on ET.EmergencyTeam_Assistant1 = MP_A1.MedPersonal_id

				left join v_MedPersonal MP_A2  on ET.EmergencyTeam_Assistant2 = MP_A2.MedPersonal_id

				left join v_MedPersonal MP_D  on ET.EmergencyTeam_Driver = MP_D.MedPersonal_id

                left join v_Lpu Lpers  on Lpers.Lpu_id = PS.Lpu_id

				left join v_Address_all A  on PS.PAddress_id = A.Address_id


				LEFT JOIN LATERAL (

					SELECT
						MS.MedService_Nick as MedService_Nick 
					FROM
						v_MedService MS 

					WHERE
						MS.LpuBuilding_id = CCC.LpuBuilding_id
						AND MS.MedServiceType_id = 19
                    LIMIT 1
				) as MS_CCC ON TRUE

				LEFT JOIN LATERAL (

					SELECT
						CCCS.CmpCallCardStatus_insDT as CmpCallCardStatus_insDT
					FROM
						v_CmpCallCardStatus CCCS 

					WHERE
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
						AND CCCS.CmpCallCardStatusType_id = 7
					ORDER BY
						CCCS.CmpCallCardStatus_insDT desc
                    LIMIT 1
				) as CCCStoDP ON TRUE
				LEFT JOIN LATERAL (

					SELECT
						CCCS.CmpCallCardStatus_insDT as CmpCallCardStatus_insDT
					FROM
						v_CmpCallCardStatus CCCS 

					WHERE
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
						AND CCCS.CmpCallCardStatusType_id = 2
					ORDER BY
						CCCS.CmpCallCardStatus_insDT desc
                    LIMIT 1
				) as CCCStoDD ON TRUE
				LEFT JOIN LATERAL (

					SELECT
						MS.MedService_Nick as MedService_Nick
					FROM
						v_MedService MS 
					WHERE
						MS.LpuBuilding_id = ET.LpuBuilding_id
						AND MS.MedServiceType_id = 19
                    LIMIT 1
				) as MS_ET ON TRUE


				LEFT JOIN LATERAL (

					SELECT
						MP.MedPersonal_Code as MedPersonal_Code
						,RTRIM(MP.Person_SurName||' '||SUBSTRING(MP.Person_FirName,1,1) ||' '||SUBSTRING(MP.Person_SecName,1,1)) as MedPersonal_FIO
					FROM
						pmUserCache puc 

						left join v_MedPersonal MP  on puc.MedPersonal_id = MP.MedPersonal_id

					WHERE
						puc.pmUser_id = CCC.pmUser_insID
				) as MP_DC ON TRUE

				LEFT JOIN LATERAL (


					SELECT
						MP.MedPersonal_Code as MedPersonal_Code
						,RTRIM(MP.Person_SurName||' '||SUBSTRING(MP.Person_FirName,1,1) ||' '||SUBSTRING(MP.Person_SecName,1,1)) as MedPersonal_FIO
					FROM
						pmUserCache puc 

						left join v_MedPersonal MP  on puc.MedPersonal_id = MP.MedPersonal_id

					WHERE
						puc.pmUser_id = to_char(bigint,CCC.CmpCallCard_Dspp)
				) as MP_DD ON TRUE

				LEFT JOIN LATERAL (


					SELECT
						MP.MedPersonal_Code as MedPersonal_Code
						,RTRIM(MP.Person_SurName||' '||SUBSTRING(MP.Person_FirName,1,1) ||' '||SUBSTRING(MP.Person_SecName,1,1)) as MedPersonal_FIO
					FROM
						pmUserCache puc 

						left join v_MedPersonal MP  on puc.MedPersonal_id = MP.MedPersonal_id

					WHERE
						puc.pmUser_id = :pmUser_id
				) as MP_DP ON TRUE
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
		";
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'pmUser_id' => $data['pmUser_id'],
			)
		);

		if ( is_object($result) ) {
			$result = $result->result('array');
		} else {
			return false;
		}

		$urgency_prof_result = $this->getCallUrgencyAndProfile(array(
			'Person_Age'=>$result[0]['Person_Age'],
			'CmpReason_id'=>$result[0]['CmpReason_id'],
			'CmpCallPlaceType_id'=>$result[0]['CmpCallPlaceType_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (!is_array($urgency_prof_result) || !isset($urgency_prof_result[0])) {
			$result[0]['CmpProfile_Name'] = '';
			$result[0]['CmpProfile_Code'] = '';
			//return array(array('success'=>false,'Error_Msg'=>'Ошибка при определении профиля вызова'));
		}
		else{
			$result[0]['CmpProfile_Name'] = $urgency_prof_result[0]['EmergencyTeamSpec_Name'];
			$result[0]['CmpProfile_Code'] = $urgency_prof_result[0]['EmergencyTeamSpec_Code'];
		}



		return $result;


	}

	/**
	 * Определение подстанции по городу
	 */
	public function getLpuBuildingOne($data) {
		if ( $this->db->dbdriver == 'postgre' ) {
			$query ="
				SELECT
					LB.LpuBuilding_id as \"LpuBuilding_id\"
				FROM
					dbo.v_LpuBuilding LB
				WHERE
					LB.Lpu_id = :Lpu_id
					AND LB.LpuBuildingType_id = 27
				LIMIT 1
			";
		} else {
			$query ='
			SELECT
				LB.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				v_LpuBuilding LB 

			WHERE
				LB.Lpu_id = :Lpu_id
				AND LB.LpuBuildingType_id = 27
            LIMIT 1
			';
		}
		$result = $this->db->query($query,array(
			'Lpu_id'=>$data['Lpu_id']
		));
		if (!is_object($result)) return false; else return $result->result('array');
	}

	/**
	 * Определение подстанции по адресу (дополнительно)
	 */
	public function getLpuBuildingByAddressAdditional($data) {
		if (empty($data['CmpCallCard_Dom'])&&empty($data['KLStreet_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не переданы обязательные параметры'));
		}
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		if ($is_pg) {
			$query = "
			SELECT
				KL.KLHouse_Name as \"KLHouse_Name\",
				LBCR.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				dbo.v_KLHouse KL
				LEFT JOIN dbo.v_KLHouseCoords KLC on KL.KLHouse_id = KLC.KLHouse_id
				LEFT JOIN dbo.LpuBuildingKLHouseCoordsRel LBCR  on LBCR.KLHouseCoords_id = KLC.KLHouseCoords_id
			WHERE
				KL.KLStreet_id = :KLStreet_id
			";
		} else {
			$query ='
			SELECT
				KL.KLHouse_Name as \"KLHouse_Name\",
				LBCR.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				v_KLHouse KL 

				LEFT JOIN v_KLHouseCoords KLC  on KL.KLHouse_id = KLC.KLHouse_id

			LEFT JOIN v_LpuBuildingKLHouseCoordsRel LBCR   on LBCR.KLHouseCoords_id = KLC.KLHouseCoords_id


			WHERE
				KL.KLStreet_id = :KLStreet_id
			';
		}
		$result = $this->db->query($query,array(
			//'CmpCallCard_Dom'=>$data['CmpCallCard_Dom'],
			'KLStreet_id'=>$data['KLStreet_id']
		));


		if (!is_object($result)) {
			return false;
		} else {
			$items = $result->result('array');

			if (count($items) > 0) foreach ($items as $item) {
				$name = $item['KLHouse_Name'];
				$pieces = explode(',',$name);
				if (count($pieces) > 0) foreach ($pieces as $piece) {
					//var_dump($piece);
					if ($piece == $data['CmpCallCard_Dom']) return array(array('LpuBuilding_id' => $item['LpuBuilding_id']));

					preg_match('/Н\((.*)\-(.*)\)/s', $piece, $odd);
					preg_match('/Ч\((.*)\-(.*)\)/s', $piece, $even);

					if (count($odd) > 0) {
						for ($i=$odd[1]; $i<=$odd[2]; $i++) {
							if ($i % 2 != 0 && $i == $data['CmpCallCard_Dom']) {
								$res = array(array('LpuBuilding_id' => $item['LpuBuilding_id']));
								return $res;
							}
						}
					}
					if (count($even) > 0) {
						for ($i=$even[1]; $i<=$even[2]; $i++) {
							if ($i % 2 == 0 && $i == $data['CmpCallCard_Dom']) {
								 $res = array(array('LpuBuilding_id' => $item['LpuBuilding_id']));
								 return $res;
							 }
						}
					}

				}
			}
			return false;
			//return $result->result('array');
		}
	}

	/**
	 * Определение подстанции по адресу
	 */

	public function getLpuBuildingByAddress($data) {

		if (empty($data['CmpCallCard_Dom'])&&empty($data['UnformalizedAddressDirectory_id'])&&empty($data['KLStreet_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не переданы обязательные параметры'));
		}

		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;
		if ($is_pg) {
			$query ="
			SELECT
				LBCR.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				dbo.v_KLHouseCoords KL
				left join dbo.LpuBuildingKLHouseCoordsRel LBCR  on LBCR.KLHouseCoords_id = KL.KLHouseCoords_id
			WHERE
				KL.KLStreet_id = :KLStreet_id
				AND KL.KLHouseCoords_Name ilike :CmpCallCard_Dom

			UNION

			SELECT
				UAD.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				dbo.v_UnformalizedAddressDirectory UAD
			WHERE
				UAD.UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id
			";
		} else {
			$query ='
			SELECT
				LBCR.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				v_KLHouseCoords KL 

				left join v_LpuBuildingKLHouseCoordsRel LBCR   on LBCR.KLHouseCoords_id = KL.KLHouseCoords_id

			WHERE
				KL.KLStreet_id = :KLStreet_id
				AND KL.KLHouseCoords_Name ilike :CmpCallCard_Dom

			UNION

			SELECT
				UAD.LpuBuilding_id as \"LpuBuilding_id\"
			FROM
				v_UnformalizedAddressDirectory UAD 

			WHERE
				UAD.UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id
			';
		}

		$result = $this->db->query($query,array(
			'CmpCallCard_Dom'=>$data['CmpCallCard_Dom'],
			'KLStreet_id'=>$data['KLStreet_id'],
			'UnformalizedAddressDirectory_id'=>$data['UnformalizedAddressDirectory_id'],
		));

		if (!is_object($result)) {
			return false;
		} else {
			$arr = $result->result('array');
			if (count($arr)>0 && $arr[0]['LpuBuilding_id'] != null) {
				return $arr;
			} else {
				$add = $this->getLpuBuildingByAddressAdditional($data);
				if ($add && $add[0]['LpuBuilding_id']!=null) return $add; else return $this->getLpuBuildingOne($data);
			}
		}
	}

	/**
	 * Определение подстанции по CurMedService_id из сессии
	 */
	public function getLpuBuildingBySessionData( $data ){

		if($this->isCallCenterArm( $data )){
			return $this -> getLpuBuildingForCallCenterArm($data);
		};

		if ( empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор службы' ) );
		}

		return $this->getLpuBuildingByMedServiceId(array(
			'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
		));

	}

	/**
	 * Определение группы пользователей нмп
	 */
	public function isCallCenterArm( $data ){

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		//особая группа
		if ( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'nmpgranddoc')) ){
			return true;
		}
		return false;
	}

	/**
	 * Определение подразделения группы пользователей колл центра из сессии
	 */
	public function getLpuBuildingForCallCenterArm( $data ){

		$this->load->model('MedPersonal_model4E', 'mpmodel');
		if (!empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		$response = $this->mpmodel->getMedPersonalGridDetail($data);

		if($response && $response[0]){
			return $response;
		}

		return false;
	}
	/**
	 * Получение идентификатора подстанции (LpuBuilding_id) по идентификатору службы(MedService_id)
	 * @param type $data
	 * @return type
	 */
	public function getLpuBuildingByMedServiceId($data) {

		$rules = array(
			array( 'field' => 'MedService_id' , 'label' => 'Идентификатор службы' , 'rules' => 'required' , 'type' => 'id' ) ,
		) ;

		/*
		 * закладка
		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		if ( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp')) ){
			$data["LpuBuildingType_id"] = 28;
		}
		*/
		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;

		$sql = "
			SELECT
				COALESCE( MS.LpuBuilding_id, 0 ) as \"LpuBuilding_id\",

				SUT.SmpUnitType_Code as \"SmpUnitType_Code\",
				SUT.SmpUnitType_id as \"SmpUnitType_id\"
			FROM
				v_MedService MS 

			LEFT JOIN v_LpuBuilding LB  on LB.LpuBuilding_id = MS.LpuBuilding_id

			LEFT JOIN LATERAL (

				select 
				    smpunitparam_id as smpunitparam_id,
                    lpubuilding_id as lpubuilding_id,
                    smpunittype_id as smpunittype_id,
                    lpubuilding_pid as lpubuilding_pid,
                    smpunitparam_insdt as smpunitparam_insdt,
                    smpunitparam_upddt as smpunitparam_upddt,
                    pmuser_insid as pmuser_insid,
                    pmuser_updid as pmuser_updid,
                    smpunitparam_iscancelling as smpunitparam_iscancelling,
                    smpunitparam_isduplicate as smpunitparam_isduplicate,
                    smpunitparam_isspecopsteam as smpunitparam_isspecopsteam,
                    smpunitparam_isautobuilding as smpunitparam_isautobuilding,
                    smpunitparam_iscall112 as smpunitparam_iscall112,
                    smpunitparam_issignalbeg as smpunitparam_issignalbeg,
                    smpunitparam_issignalend as smpunitparam_issignalend,
                    smpunitparam_isovercall as smpunitparam_isovercall,
                    smpunitparam_isautoemergduty as smpunitparam_isautoemergduty,
                    smpunitparam_isautoemergdutyclose as smpunitparam_isautoemergdutyclose,
                    smpunitparam_issendcall as smpunitparam_issendcall,
                    smpunitparam_isviewother as smpunitparam_isviewother,
                    smpunitparam_isktprint as smpunitparam_isktprint,
                    smpunitparam_iscallsendoc as smpunitparam_iscallsendoc,
                    smpunitparam_iscancldcall as smpunitparam_iscancldcall,
                    smpunitparam_iscallcontroll as smpunitparam_iscallcontroll,
                    smpunitparam_isshowallcallstodp as smpunitparam_isshowallcallstodp,
                    smpunitparam_isshowcallcount as smpunitparam_isshowcallcount,
                    smpunitparam_isnomoreassigncall as smpunitparam_isnomoreassigncall,
                    smpunitparam_maxcallcount as smpunitparam_maxcallcount,
                    lpu_eid as lpu_eid,
                    lpubuilding_eid as lpubuilding_eid,
                    smpunitparam_isautohome as smpunitparam_isautohome,
                    smpunitparam_isprescrhome as smpunitparam_isprescrhome,
                    smpunitparam_isgroupsubstation as smpunitparam_isgroupsubstation,
                    smpunitparam_isdispnocontrol as smpunitparam_isdispnocontrol,
                    smpunitparam_isdocnocontrol as smpunitparam_isdocnocontrol,
                    smpunitparam_isdispothercontrol as smpunitparam_isdispothercontrol,
                    smpunitparam_issavetreepath as smpunitparam_issavetreepath,
                    smpunitparam_iscallapprovesend as smpunitparam_iscallapprovesend,
                    smpunitparam_isnotransother as smpunitparam_isnotransother,
                    smpunitparam_isdenycallanswerdisp as smpunitparam_isdenycallanswerdisp,
                    smpunitparam_isshowcallnoteam as smpunitparam_isshowcallnoteam
				from v_SmpUnitParam 

				where LpuBuilding_id = LpuBuilding_id
				order by SmpUnitParam_id desc
				limit 1
			) SUP ON TRUE
			LEFT JOIN v_SmpUnitType SUT  on SUP.SmpUnitType_id = SUT.SmpUnitType_id

			WHERE
				MS.MedService_id = :MedService_id
				--AND LB.LpuBuildingType_id = 27
		";

		//var_dump(getDebugSQL($sql, $queryParams)); exit;
		$result = $this->queryResult($sql, $queryParams);


		return $result;

	}

	/**
	 * Сохранение типа передачи талона вызова бригаде
	 * @return boolean
	 */
	public function setCmpCallCardTransType($data) {
		if (empty($data['CmpCallCard_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор талона вызова'));
		}
		if (!isset($data['CmpCallCard_Kakp'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: тип передачи талона вызова бригаде'));
		}

		$query = '
            select 
                Error_Code as "Error_Code",
                Error_Message as "Error_Msg"
			from p_CmpCallCard_setTransToETType (
				CmpCallCard_id := :CmpCallCard_id,
				CmpCallCard_Kakp := :CmpCallCard_Kakp,
				pmUser_id := :pmUser_id
				)
		';

		$result = $this->db->query($query,array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Kakp' => $data['CmpCallCard_Kakp'],
			'pmUser_id'=>$data['pmUser_id']
		));

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}

	}


	/**
	 * Функция отправки сообщения на NodeJS с назначенным вызовом
	 */
	public function sendNodeCallCard($data) {
		$this->load->helper('NodeJS');
		$AdditionalCallCardInfo = $this->getAdditionalCallCardInfo($data);
		if (!isset($AdditionalCallCardInfo[0])) {
			return false;
		}
		$AdditionalCallCardInfo = $AdditionalCallCardInfo[0];
		$params = array('action'=>'set',
			'CmpCallCardId'=>$data['CmpCallCard_id'],
			'EmergencyTeamId'=>$AdditionalCallCardInfo['EmergencyTeam_id'],
			'PersonId'=>(!empty($AdditionalCallCardInfo['Person_id']))?$AdditionalCallCardInfo['Person_id']:'',
			'PersonFIO'=>$AdditionalCallCardInfo['Person_Surname'].' '.$AdditionalCallCardInfo['Person_Firname'].' '.$AdditionalCallCardInfo['Person_Secname'],
			'PersonFir'=>($AdditionalCallCardInfo['Person_Firname'])?$AdditionalCallCardInfo['Person_Firname']:'',
			'PersonSec'=>($AdditionalCallCardInfo['Person_Secname'])?$AdditionalCallCardInfo['Person_Secname']:'',
			'PersonSur'=>($AdditionalCallCardInfo['Person_Surname'])?$AdditionalCallCardInfo['Person_Surname']:'',
			'PersonBirthday'=>(!empty($AdditionalCallCardInfo['Person_Birthday']))?$AdditionalCallCardInfo['Person_Birthday']:'',
			'CmpCallCardPrmDate'=>$AdditionalCallCardInfo['CmpCallCard_prmDate'],
			'CmpReasonName'=>$AdditionalCallCardInfo['CmpReason_Name'],
			'CmpCallTypeName'=>(!empty($AdditionalCallCardInfo['CmpCallType_Name']))?$AdditionalCallCardInfo['CmpCallType_Name']:'',
			'AdressName'=>$AdditionalCallCardInfo['Adress_Name']
			);
		$params['CallerInfo'] = (isset($AdditionalCallCardInfo['CallerInfo']))?$AdditionalCallCardInfo['CallerInfo']:'';
		$params['Age'] = (isset($AdditionalCallCardInfo['Age']))?$AdditionalCallCardInfo['Age']:'';
		$params['AgeTypeValue'] = (isset($AdditionalCallCardInfo['AgeTypeValue']))?$AdditionalCallCardInfo['AgeTypeValue']:'';
		$params['SexId'] = (isset($AdditionalCallCardInfo['SexId']))?$AdditionalCallCardInfo['SexId']:'';
		array_walk($params, 'ConvertFromWin1251ToUTF8');

		$postSendResult = NodePostRequest($params);
		return $postSendResult;
	}

	/**
	 * Метод получения списка медикаментов с количеством из укладки наряда по идентификатору талона вызова
	 * @param type $data
	 * @return type
	 */
	public function loadEmergencyTeamDrugPackByCmpCallCardId( $data) {

		$rules = array(
			array( 'field' => 'CmpCallCard_id' , 'label' => 'Идентификатор карты вызова СМП' , 'rules' => 'required' , 'type' => 'id' ) ,
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;

		$query = "
			SELECT
				ETDP.Drug_id as \"Drug_id\",
				ETDP.EmergencyTeamDrugPack_id as \"EmergencyTeamDrugPack_id\",
				ETDP.EmergencyTeamDrugPack_Total as \"EmergencyTeamDrugPack_Total\",
				CASE WHEN (COALESCE(D.Drug_Fas,0) = 0) then RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||' '||to_char(COALESCE(D.DrugForm_Name,''))||' '||to_char(COALESCE(D.Drug_Dose,'')))

					else RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||', '||to_char(COALESCE(D.DrugForm_Name,''))||', '||to_char(COALESCE(D.Drug_Dose,''))||', №'||TO_CHAR(D.Drug_Fas))

				end as \"DrugTorg_Name\"
			FROM
				v_CmpCallCard CCC 

				LEFT JOIN v_EmergencyTeamDrugPack ETDP  ON ETDP.EmergencyTeam_id = CCC.EmergencyTeam_id

				LEFT JOIN rls.v_Drug D  ON (D.Drug_id = ETDP.Drug_id)

			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
				AND ETDP.EmergencyTeamDrugPack_Total != 0
			";

		return $this->queryResult($query , $queryParams);
	}
	/**
	 * Метод получения списка медикаментов с количеством из укладки наряда по идентификатору талона вызова
	 * @param type $data
	 * @return type
	 */
	public function loadEmergencyTeamDrugPackMoveList( $data) {

		$rules = array(
			array( 'field' => 'CmpCallCard_id' , 'label' => 'Идентификатор карты вызова СМП' , 'rules' => 'required' , 'type' => 'id' ) ,
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;

		$query = "
			SELECT
				ETDPM.EmergencyTeamDrugPackMove_id as \"EmergencyTeamDrugPackMove_id\",
				ETDPM.EmergencyTeamDrugPack_id as \"EmergencyTeamDrugPack_id\",
				ETDP.Drug_id as \"Drug_id\",
				CAST(ABS(ETDPM.EmergencyTeamDrugPackMove_Quantity) as CHAR(20)) as \"EmergencyTeamDrugPackMove_Quantity\",
				CASE WHEN (COALESCE(D.Drug_Fas,0) = 0) then RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||' '||to_char(COALESCE(D.DrugForm_Name,''))||' '||to_char(COALESCE(D.Drug_Dose,'')))

					else RTRIM(to_char(COALESCE(D.DrugTorg_Name,''))||', '||to_char(COALESCE(D.DrugForm_Name,''))||', '||to_char(COALESCE(D.Drug_Dose,''))||', №'||TO_CHAR(D.Drug_Fas))

				end as \"DrugTorg_Name\",
				COALESCE(D.DrugForm_Name,'') as \"DrugForm_Name\",

				D.Drug_Code as \"Drug_Code\",
				'unchanged' as \"status\"
			FROM
				v_EmergencyTeamDrugPackMove ETDPM 

				LEFT JOIN v_EmergencyTeamDrugPack ETDP  ON ETDP.EmergencyTeamDrugPack_id = ETDPM.EmergencyTeamDrugPack_id

				LEFT JOIN rls.v_Drug D  ON (D.Drug_id = ETDP.Drug_id)

			WHERE
				ETDPM.CmpCallCard_id = :CmpCallCard_id
			";

		return $this->queryResult($query , $queryParams);
	}

	/**
	 * Метод снятия бригады с вызова.
	 * @param type $data
	 * @return type
	 */
	public function cancelCmpCallCardFromEmergencyTeam( $data ) {
		$rules = array(
			array( 'field' => 'CmpCallCard_id' , 'label' => 'Идентификатор карты вызова СМП' , 'rules' => 'required' , 'type' => 'id' ) ,
			array( 'field' => 'pmUser_id' , 'rules' => 'required' , 'label' => 'Идентификатор пользователя' , 'type' => 'id' ) ,
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , false ) ;
		if ( !empty( $err ) )
			return $err ;

		// 1. Проверяем статус вызова

		$query = "
			SELECT
				CCCS.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				CCCST.CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\"
			FROM
				v_CmpCallCardStatus CCCS 

				LEFT JOIN v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id

			WHERE
				CCCS.CmpCallCard_id = :CmpCallCard_id
			ORDER BY
				CCCS.CmpCallCardStatus_updDT DESC
            LIMIT 1
		";

		$get_status_type_result = $this->queryResult($query, $queryParams);
		if (!$this->isSuccessful($get_status_type_result)) {
			return $get_status_type_result;
		} else {
			if (empty($get_status_type_result[0]) || empty($get_status_type_result[0]['CmpCallCardStatusType_id'])) {
				return $this->createError(null, 'У текущего вызова бригады нет статуса');
			} else {
				if (in_array($get_status_type_result[0]['CmpCallCardStatusType_id'], array(3,4,5,6))) {
					return $this->createError(null, 'У текущего вызова бригады установлен статус '.$get_status_type_result[0]['CmpCallCardStatusType_Name']);
				}
			}
		}

		$this->beginTransaction() ;

		// 2. Снимаем бригаду с вызова

		$queryParams['EmergencyTeam_id'] = null;
		$cancel_emergency_team_result = $this->swUpdate( 'CmpCallCard' , $queryParams ) ;

		if ( !$this->isSuccessful( $cancel_emergency_team_result ) ) {
			$this->rollbackTransaction() ;
			return $cancel_emergency_team_result ;
		}

		// 3. Проставляем вызову статус, чтобы он отобразился в диспетчере направлений

		$queryParams[ 'CmpCallCardStatusType_id' ] = 1 ;
		$set_status_result = $this->setStatusCmpCallCard( $queryParams ) ;

		if ( !$this->isSuccessful( $set_status_result ) ) {
			$this->rollbackTransaction() ;
			return $set_status_result ;
		}

		$this->commitTransaction() ;

		return $set_status_result ;
	}

	/**
	 * @desc Старший врач - вызовы
	 */
	public function loadSMPHeadDoctorWorkPlace($data){

		$lpuBuildingsWorkAccess = null;
		$filter = array();
		$tplfilter = array();
		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');
		$regionNick = $_SESSION['region']['nick'];

		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		}

		//определяем lpu_building_id - это значение нам понадобится позже
		if(empty($data['LpuBuilding_id'])){
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);
			if (empty($lpuBuilding[0]['LpuBuilding_id'])){
				return $this->createError(null, 'Не определена подстанция');
			}
			else{
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
			}
		}
		
		//Скрываем вызовы принятые в ППД
		$ppdFilter = "COALESCE(CCC.CmpCallCard_IsReceivedInPPD,1)!=2";
		//$lpuppdFilter = '';
		$displayNmpCalls = false;
		$lpuFilter = '';
		$timeExpiredReasonCode = "";

		$resOperDepartament = $this->getOperDepartament($data, true);
		$OperDepartament = (is_array($resOperDepartament) && isset($resOperDepartament["LpuBuilding_pid"]))?$resOperDepartament["LpuBuilding_pid"]:NULL;

		if($this->isCallCenterArm( $data )){
			/*
			для нмп армов усовия иные
			Внимание – в группе отображаются вызовы, по которым одновременно выполняются условия:
			o	Статус вызова «Передано» или «Принято»
			o	Вид вызова «Неотложный»
			o	С момент принятия вызова (CmpCallCard_prmDT) прошло более 1.5 часов и меньше 24 часов
			*/
			//отсечка на 24 часа идет в главном условии

			$attentionGroup = "(
								CCC.CmpCallCardStatusType_id in (1,2)
								--and COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2
								and DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) >= 90
							)";

			//группа в работе
			$inWorkGroup = "(
								CCC.CmpCallCardStatusType_id in (1,2)
								and DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) < 90
							)";

		}else{

			// Производим проверку оперативного отдела на галочку "Отображать вызовы с превышением срока обслуживания в отдельной группе АРМ СВ"
			if($this->getIsOverCallLpuBuildingData($data, false, $OperDepartament)){
				$attentionGroup = " (1 = 2) ";

			}else {
				//тут какая-то дикая табуляция идет, видимо для того чтобы запрос читался

				$attentionGroup = "(
								(
									COALESCE(CUAPS.CmpCallCardAcceptor_Code, 'СМП') = 'СМП' AND
									(
										-- превышен таймер для статуса 'Передано СМП'
										((SUT.minTimeSMP - DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()))<0 AND CCC.CmpCallCardStatusType_id in (1,20))
										OR
										-- превышен таймер для статуса 'Принято СМП'
										((SUT.maxTimeSMP - DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()))<0 AND CCC.CmpCallCardStatusType_id=2)
									)
								)
								OR
								(
									COALESCE(CUAPS.CmpCallCardAcceptor_Code, 'СМП') = 'НМП' AND
									(
										-- превышен таймер для статуса 'Передано НМП'
										((SUT.minTimeNMP - DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()))<0 AND CCC.CmpCallCardStatusType_id in (1,20)) -- превышен таймер для статуса 'Передано'
										OR
										-- превышен таймер для статуса 'Принято НМП'
										((SUT.maxTimeNMP - DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()))<0 AND CCC.CmpCallCardStatusType_id=2) -- превышен таймер для статуса 'Принято'
									)
								)
							)";

				$timeExpiredReasonCode = "
					CASE
						WHEN
						(
							COALESCE(CUAPS.CmpCallCardAcceptor_Code, 'СМП') = 'СМП'
							AND
							(SUT.minTimeSMP - DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()))<0
						)
						THEN CASE
							WHEN (CCC.CmpCallCardStatusType_id in (1,20)) THEN 1
							WHEN (CCC.CmpCallCardStatusType_id in (2)) THEN 2
						END

						WHEN
						(
							COALESCE(CUAPS.CmpCallCardAcceptor_Code, 'СМП') = 'НМП'
							AND
							(SUT.minTimeNMP - DATEDIFF('minute', CCC.CmpCallCard_prmDT, dbo.tzGetDate()))<0
						)
						THEN CASE
							WHEN (CCC.CmpCallCardStatusType_id in (1,20)) THEN 3
							WHEN (CCC.CmpCallCardStatusType_id in (2)) THEN 4
						END
					END AS \"timeSMPExpiredReasonCode\",
				";
			}

			//группа в работе
			$inWorkGroup = "(
								CCC.CmpCallCardStatusType_id in (1,2,20)
							)";
		}

		$ArrivalTimeET = "CASE WHEN ( ( COALESCE(SUT.ServiceTimeET, 40)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END";
		$minResponseTimeET = "CASE WHEN ( ( COALESCE(SUT.minResponseTimeET, 0.25)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END";
		$maxResponseTimeET = "CASE WHEN ( ( COALESCE(SUT.maxResponseTimeET, 2)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END";
		if (in_array($regionNick, array('ufa'))) {
			$ArrivalTimeET = "CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
				--в форме неотложной помощи
				THEN CASE WHEN ( ( COALESCE(SUT.ArrivalTimeETNMP, 20)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
				--для вызовов 'выезд на вызов'
				ELSE CASE WHEN ( ( COALESCE(SUT.ArrivalTimeET, 20)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
				END";
			$minResponseTimeET = "CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
				--в форме неотложной помощи
				THEN CASE WHEN ( ( COALESCE(SUT.minResponseTimeETNMP, 0.25)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
				--для вызовов 'назначена бригада'
				ELSE CASE WHEN ( ( COALESCE(SUT.minResponseTimeET, 0.25)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
				END";
			$maxResponseTimeET = "CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
				--в форме неотложной помощи
				THEN CASE WHEN ( ( COALESCE(SUT.maxResponseTimeETNMP, 2)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
				--для вызовов 'принят бригадой'
				ELSE CASE WHEN ( ( COALESCE(SUT.maxResponseTimeET, 2)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
				END";
		}

		$timesBlock = "
						--расчет нормативного времени статусов, кроме отмененных вызовов
						,CASE WHEN (CCC.CmpCallCardStatusType_id <> 5) THEN
							CASE WHEN (activeEvent.CmpCallCardEventType_Code = 1) THEN
								--для вызовов 'принят диспетчером
								CASE WHEN (COALESCE(CCC.CmpCallCard_IsExtra, 2) = 2)
									--в форме экстренной помощи
									THEN CASE WHEN ( ( COALESCE(SUT.minTimeSMP, 0)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
									--в форме неотложной помощи
									ELSE CASE WHEN ( ( COALESCE(SUT.minResponseTimeNMP, 0)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END

								END
							ELSE
								CASE WHEN (activeEvent.CmpCallCardEventType_Code = 4) THEN
									".$minResponseTimeET."
								ELSE
									CASE WHEN (activeEvent.CmpCallCardEventType_Code = 5) THEN
										".$maxResponseTimeET."
									ELSE
										CASE WHEN (activeEvent.CmpCallCardEventType_Code = 7) THEN
											".$ArrivalTimeET."
										ELSE
											CASE WHEN (activeEvent.CmpCallCardEventType_Code = 8) THEN
												--для вызовов 'приезд на вызов'
												CASE WHEN ( ( COALESCE(SUT.ServiceTimeET, 40)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
											ELSE
												CASE WHEN (activeEvent.CmpCallCardEventType_Code in (9,11)) THEN
													--для вызовов 'начало госпитализации' и 'приезд в мо'
													CASE WHEN ( ( COALESCE(SUT.DispatchTimeET, 15)*60 - DATEDIFF('second', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate() ))<0 ) THEN 'true' ELSE 'false' END
												ELSE 'false'
												END
											END
										END
									END
								END
							END
						END	as timeEventBreak
					";


		//выбираем подстанции с которыми работаем
		$lpuBuildings = null;
		if ( !(empty( $lpuBuildingsWorkAccess)) ) {
			if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
			// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
			$lpuBuildings = $lpuBuildingsWorkAccess;
		}
		else{
			//подчиненные подстанции
			if ( in_array($regionNick, array('ufa', 'krym', 'kz', 'perm', 'ekb', 'astra')) ){
				$smpUnitsNested = $this->loadSmpUnitsNested($data, true);
			}			
			else{
				$smpUnitsNested = $this->loadSmpUnitsNested($data);
			}
			$lpuBuildings = $smpUnitsNested;
		}

		if ( !(empty($lpuBuildings)) ) {
			$lpuFilter = "";
			foreach ($lpuBuildings as &$value) {
				//условие для отображения вызовов переданных в нмп
				if($value == 0){
					$ppdFilter = null;
					$displayNmpCalls = true;
				}
				$lpuFilter .= $value.',';
			}
			$lpuFilter = substr($lpuFilter, 0, -1);
		} else {
			return $this->createError(null, 'Не определена подстанция');
		}

		/*
		 * унес наверх
		$resOperDepartament = $this->getOperDepartament($data, true);
		$OperDepartament = (is_array($resOperDepartament) && isset($resOperDepartament["LpuBuilding_pid"]))?$resOperDepartament["LpuBuilding_pid"]:NULL;


		// Производим проверку оперативного отдела на галочку "Отображать вызовы с превышением срока обслуживания в отдельной группе АРМ СВ"
		if($this->getIsOverCallLpuBuildingData($data, false, $OperDepartament)){
			$attentionGroup = " (1 = 2) ";
            $timeExpiredReasonCode = "";
        }
		*/

		$queryParams = array();
		
		if(isset($ppdFilter)){$filter[] = $ppdFilter;};

		//оптимизация запроса

		$union[] = "
			SELECT
				CmpCallCard_id as \"CmpCallCard_id\"
			from
				v_CmpCallCard AS CCC 

				inner JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

			where
				CCC.lpu_id = :CmpLpu_id
				and CCC.CmpCallCardStatusType_id in (6)
				AND CCT.CmpCallType_Code in (6, 15, 16)
				AND COALESCE(CCC.CmpCallCard_IsOpen,1)=2
				AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 24
		";

		if ( in_array($CurArmType, array('nmpgranddoc')) ){
			$union[] = "
				SELECT
					CmpCallCard_id as \"CmpCallCard_id\"
				from
					v_CmpCallCard AS CCC 

					left join v_MedService MSnmp  on MSnmp.MedService_id = CCC.MedService_id and MSnmp.MedServiceType_id in (18)

					left join v_LpuBuilding LBnmp  on LBnmp.LpuBuilding_id = MSnmp.LpuBuilding_id

				where
					(LBnmp.LpuBuilding_id in ($lpuFilter) or :CmpLpu_id = CCC.Lpu_ppdid)
					and COALESCE(CCC.CmpCallCard_IsOpen,1)=2
					AND CCC.CmpCallCardStatusType_id IN (1,2,4,5,6,7,8,18,19,20)
					AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 24
			";
		}else{
			$union[] = "
				SELECT
					CmpCallCard_id as \"CmpCallCard_id\"
				from
					v_CmpCallCard AS CCC 

					inner JOIN dbo.v_LpuBuilding AS LB  ON LB.LpuBuilding_id = CCC.LpuBuilding_id

				where
					LB.LpuBuilding_id in ($lpuFilter)
					and COALESCE(CCC.CmpCallCard_IsOpen,1)=2
					AND CCC.CmpCallCardStatusType_id IN (1,2,4,5,6,7,8,18,19,20)
					AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 24
			";
		}


		//отображение вызовов в нмп
		if($displayNmpCalls || $this->isCallCenterArm( $data )){
			$union[] = "
				SELECT
					CmpCallCard_id as \"CmpCallCard_id\"
				from
					v_CmpCallCard AS CCC 

				WHERE
					CCC.lpu_id = :CmpLpu_id
					and CCC.Lpu_ppdid is not null
					AND COALESCE(CCC.CmpCallCard_IsOpen,1)=2
					AND CCC.CmpCallCardStatusType_id IN (1,2,4,5,6,7,8,18,19,20)
					AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 24
			";
		}
		//

		//Скроем закрытые карточки 112
		$filter[] = "(CCC112.CmpCallCard112_id is null or CCC112.CmpCallCard112StatusType_id != 4)";

		$queryParams[ 'LpuBuilding_id' ] = $data['LpuBuilding_id'];
		$queryParams[ 'CmpLpu_id' ] = $data['Lpu_id'] ;
		$queryParams[ 'LpuBuilding_pid' ] = $OperDepartament;

		$query = "
			select * into #tmp  from (
     		 ".implode(" UNION ", $union)."
			) as tabl

			SELECT
				CCC.CmpCallCard_id as \"CmpCallCard_id\"
				,CLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				,CCC.CmpCallCard_rid as \"CmpCallCard_rid\"
				,CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
				,CCCStatus.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\"
				,CCCStatus.CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\"
				,PS.Sex_id as \"Sex_id\"
				,CCC.CmpReason_id as \"CmpReason_id\"
				,case CCC.CmpCallCard_IsExtra
					when 1 then 'Экстренный'
					when 2 then 'Неотложный'
					when 3 then 'Вызов врача на дом'
					when 4 then 'Обращение в поликлинику'
				end as \"CmpCallCard_IsExtraText\"
				,case when to_char (COALESCE(CR.CmpReason_Code,'0')) in ('313', '53', '298', '326', '231', '343', '232', '233', '155', '329', '321', '314', '344', '319', '36', '114', '40', '156', '277', '88',

				'153', '127', '121', '89', '305', '327', '56', '273', '102', '176', '351', '307', '338', '52', '339', '331', '191', '345', '323', '337', '302', '341', '310') then 'НП' else '' end as \"Urgency\"
				,COALESCE(CCC.Person_Age,0) as \"Person_Age\"

				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
				,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') as \"CmpCallCard_prmDateStr\"

				,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName,'') || ' '
				|| COALESCE(SUBSTRING(COALESCE(PS.Person_Firname, rtrim(CCC.Person_FirName)),1,1 ), '')|| ' '
				|| COALESCE(SUBSTRING(COALESCE(PS.Person_Secname. rtrim(CCC.Person_SecName)),1,1 ), '') as \"Person_FIO\"

				,to_char (COALESCE(CCC.Person_BirthDay,PS.Person_BirthDay), 'DD Mon YYYY HH24:MI:SS') as \"Person_Birthday\"
				,CCC.Person_IsUnknown as \"Person_IsUnknown\"

				,COALESCE(CSecondR.CmpReason_Name, CR.CmpReason_Name, '') as \"CmpReason_Name\"
				,COALESCE(CSecondR.CmpReason_Code, CR.CmpReason_Code,'') as \"CmpReason_Code\"
				,CCC.CmpSecondReason_id as \"CmpSecondReason_id\"
				,CSecondR.CmpReason_Code as \"CmpSecondReason_Code\"
				,CSecondR.CmpReason_Name as \"CmpSecondReason_Name\"
				,COALESCE(CCC.CmpCallCard_isControlCall,1) as \"CmpCallCard_isControlCall\"
				,ct.CmpCallType_Name as \"CmpCallType_Name\"
				,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
				,COALESCE(ET.EmergencyTeam_Num,'') as \"EmergencyTeam_Num\"
				,ET.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\"
				,ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\"
				,COALESCE(CCC.CmpCallCard_Urgency, 99) as \"CmpCallCard_Urgency\"
				,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
				,CCC.CmpCallType_id as \"CmpCallType_id\"
				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

				end||

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
					', '||Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
				end ||

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
				end ||

				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Name\"

				,case when City.KLCity_id is not null then City.KLCity_id else Town.KLTown_id end as \"Town_id\"
				,case when COALESCE(CCC.KLStreet_id,0) = 0 then

					case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

					else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				 else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"
				,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
				,CCC.CmpCallerType_id as \"CmpCallerType_id\"
				,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
				,CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\"
				,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
				,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
				,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
				,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
				,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,CCC.KLRgn_id as \"KLRgn_id\"
				,CCC.KLSubRgn_id as \"KLSubRgn_id\"
				,CCC.KLCity_id as \"KLCity_id\"
				,CCC.KLTown_id as \"KLTown_id\"
				,CCC.KLStreet_id as \"KLStreet_id\"
				,CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\"
				,CCC.Lpu_ppdid as \"Lpu_ppdid\"
				,CCC.MedService_id as \"MedService_id\"

				,case when UAD.UnformalizedAddressDirectory_lat is not null then UAD.UnformalizedAddressDirectory_lat else CCC.CmpCallCard_CallLtd end as \"UnAdress_lat\"
				,case when UAD.UnformalizedAddressDirectory_lng is not null then UAD.UnformalizedAddressDirectory_lng else CCC.CmpCallCard_CallLng end as \"UnAdress_lng\"

				,COALESCE(L.Org_Nick,'') as \"Lpu_hNick\"

				,case when LB.LpuBuilding_Nick is not null then LB.LpuBuilding_Nick else LB.LpuBuilding_Name end as \"LpuBuilding_Name\"
				,CCC.LpuBuilding_id as \"LpuBuilding_id\"
				,SUT.minTimeSMP as \"minTimeSMP\"
				,SUT.maxTimeSMP as \"maxTimeSMP\"
				,SUT.minTimeNMP as \"minTimeNMP\"
				,SUT.maxTimeNMP as \"maxTimeNMP\"
				--за минуту до просрока выводим таймер если бригада не назначена по минТаймеру
				,CASE WHEN((CCC.CmpCallCardStatusType_id=1) and (60 > (SUT.minTimeSMP*60 - DATEDIFF('second',CCC.CmpCallCard_prmDT,dbo.tzGetDate() ) ) ) )
					THEN (SUT.minTimeSMP*60 - DATEDIFF('second',CCC.CmpCallCard_prmDT,dbo.tzGetDate() ) )
					ELSE ''
				END AS \"timeToAlertByMinTimeSMP\"
				--сразу как получили значение - отправлять, чтобы отменить таймер по минимуму
					,CASE WHEN(  (CCC.CmpCallCardStatusType_id =2)) and (SUT.maxTimeSMP*60 > ( DATEDIFF('second',CCC.CmpCallCard_prmDT, dbo.tzGetDate()) ) )  )
					THEN (SUT.maxTimeSMP*60 - DATEDIFF('second',CCC.CmpCallCard_prmDT, dbo.tzGetDate() ) )
					ELSE null
				END  AS \"timeToAlertByMaxTimeSMP\",
				ct.CmpCallType_Code as \"CmpCallType_Code\",
				CCR.CmpCallRecord_id as \"CmpCallRecord_id\",
				CUAPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv as \"HeadDoctorObservReason\",

				CASE WHEN CCC.CmpCallCardStatusType_id = 19 THEN
					activeEvent.CmpCallCardEventType_Name || ' до ' || to_char(CCC.CmpCallCard_storDT, 4)||' '||to_char(cast(CCC.CmpCallCard_storDT as time(6)))
					ELSE activeEvent.CmpCallCardEventType_Name
				END as \"CmpCallCardEventType_Name\",
				activeEvent.CmpCallCardEvent_updDT as \"CmpCallCardEvent_updDT\",
				CASE WHEN CCC.CmpCallCardStatusType_id not in(4,5,6,16,19) THEN DATEDIFF('minute', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate()) END as \"EventWaitDuration\"
				{$timesBlock},
				{$timeExpiredReasonCode}

				-- Группы вызовов
				CASE
					-- статус решение старшего врача или возвращено диспетчером подстанции
					WHEN CCC.CmpCallCardStatusType_id in (18) THEN 1
					-- статус отказ за 24 часа
					ELSE
						CASE WHEN ( CCC.CmpCallCardStatusType_id in (5)
								AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate())<=24 )
							THEN 7
						WHEN
							".$attentionGroup."
						THEN 2
						ELSE
							--приняты и переданы и уложились по времени, умнички
							CASE WHEN
								".$inWorkGroup."
							THEN 3
							ELSE
								--закрытые и за последние 24 часа и тип вызова консультатив/абонент отключился/справка
								CASE WHEN (
									CCC.CmpCallCardStatusType_id in (6)
									AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate())<=24
									AND ct.CmpCallType_Code in (6,15,16)
									)
								THEN 4
								ELSE
									--закрытые и обслуженные и за последние 24 часа и тип вызова перв/повт/попут/выз на спец бригаду
									CASE WHEN (
										CCC.CmpCallCardStatusType_id in (6)
										)
									THEN 6
									--отложенные
									ELSE
										CASE WHEN (
											CCC.CmpCallCardStatusType_id in (19)
											)
										THEN 8
										ELSE
											CASE WHEN (
												CCC.CmpCallCardStatusType_id in (4)
												)
											THEN 5
										END
									END
								END
							END
						END
					END
				END as \"CmpGroup_id\"
				--,case when MS.MedServiceType_id = 18 then 'НМП' else 'СМП' end as \"CmpCallCardAcceptor_Code\"
				,case when CCC.Lpu_ppdid is not null then 'НМП' else 'СМП' end as \"CmpCallCardAcceptor_Code\"
				--,COALESCE(CUAPS.CmpCallCardAcceptor_Code,'СМП') as \"CmpCallCardAcceptor_Code\"

				,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay , 0) !=0 ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), dbo.tzGetDate() ) > 12 THEN

                		to_char (DATEDIFF('year' ,COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), dbo.tzGetDate() )  ) || ' лет'

                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	to_char (DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), dbo.tzGetDate() ) ) || ' дн. '

                        ELSE
                        	to_char (DATEDIFF('minute' ,COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), dbo.tzGetDate() )  ) || ' мес.'

                        END
                   	END
                 ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE cast(CCC.Person_Age as varchar) || ' лет'
                    END
                 END
                 as \"personAgeText\"
                ,CCCD.Duplicate_Count as \"Duplicate_Count\"
                ,CCCAC.ActiveCall_Count as \"ActiveCall_Count\"
                ,to_char (CCCD.Duplicate_Count) || ' / ' || to_char (CCCAC.ActiveCall_Count) as \"DuplicateAndActiveCall_Count\"
                ,SUP.SmpUnitParam_IsCallControll as \"IsCallControll\"
                ,cmpIllegalAct.CmpIllegalAct_prmDT as \"CmpIllegalAct_prmDT\"
				,cmpIllegalAct.CmpIllegalAct_Comment as \"CmpIllegalAct_Comment\"
				,cmpIllegalAct.CmpIllegalAct_byPerson as \"CmpIllegalAct_byPerson\"
				,CCCEDeny.CmpCallCardEvent_id as \"hasEventDeny\",
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as \"PersonQuarantine_IsOn\",
				to_char(PQ.PersonQuarantine_begDT, 'DD.MM.YYYY') as \"PersonQuarantine_begDT\"
				-- end select
			FROM
				-- from
				#tmp tmp 

                left join v_CmpCallCard as CCC  on tmp.CmpCallCard_id=CCC.CmpCallCard_id

				left join v_CmpCallCard112 CCC112  on CCC112.CmpCallCard_id = CCC.CmpCallCard_id

				left join v_CmpCallType as CT  on ct.CmpCallType_id=ccc.cmpCallType_id

				left join v_PersonState PS  on PS.Person_id = CCC.Person_id

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				left join v_CmpReason CSecondR  on CSecondR.CmpReason_id = CCC.CmpSecondReason_id

				--left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				left join EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

				left join v_EmergencyTeamSpec as ETSpec  ON(ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id)

				left join {$this->schema}.v_CmpCloseCard CLC  on CCC.CmpCallCard_id = CLC.CmpCallCard_id

				left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_CmpCallCardStatusType CCCStatus  on CCCStatus.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id				

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond

				left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id

				left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_LpuBuilding LB  ON(LB.LpuBuilding_id=CCC.LpuBuilding_id)

				left join v_SmpUnitTimes SUT  on (SUT.LpuBuilding_id in (:LpuBuilding_pid))

				LEFT JOIN LATERAL (

					select 
					smpunitparam_id as smpunitparam_id,
                    lpubuilding_id as lpubuilding_id,
                    smpunittype_id as smpunittype_id,
                    lpubuilding_pid as lpubuilding_pid,
                    smpunitparam_insdt as smpunitparam_insdt,
                    smpunitparam_upddt as smpunitparam_upddt,
                    pmuser_insid as pmuser_insid,
                    pmuser_updid as pmuser_updid,
                    smpunitparam_iscancelling as smpunitparam_iscancelling,
                    smpunitparam_isduplicate as smpunitparam_isduplicate,
                    smpunitparam_isspecopsteam as smpunitparam_isspecopsteam,
                    smpunitparam_isautobuilding as smpunitparam_isautobuilding,
                    smpunitparam_iscall112 as smpunitparam_iscall112,
                    smpunitparam_issignalbeg as smpunitparam_issignalbeg,
                    smpunitparam_issignalend as smpunitparam_issignalend,
                    smpunitparam_isovercall as smpunitparam_isovercall,
                    smpunitparam_isautoemergduty as smpunitparam_isautoemergduty,
                    smpunitparam_isautoemergdutyclose as smpunitparam_isautoemergdutyclose,
                    smpunitparam_issendcall as smpunitparam_issendcall,
                    smpunitparam_isviewother as smpunitparam_isviewother,
                    smpunitparam_isktprint as smpunitparam_isktprint,
                    smpunitparam_iscallsendoc as smpunitparam_iscallsendoc,
                    smpunitparam_iscancldcall as smpunitparam_iscancldcall,
                    smpunitparam_iscallcontroll as smpunitparam_iscallcontroll,
                    smpunitparam_isshowallcallstodp as smpunitparam_isshowallcallstodp,
                    smpunitparam_isshowcallcount as smpunitparam_isshowcallcount,
                    smpunitparam_isnomoreassigncall as smpunitparam_isnomoreassigncall,
                    smpunitparam_maxcallcount as smpunitparam_maxcallcount,
                    lpu_eid as lpu_eid,
                    lpubuilding_eid as lpubuilding_eid,
                    smpunitparam_isautohome as smpunitparam_isautohome,
                    smpunitparam_isprescrhome as smpunitparam_isprescrhome,
                    smpunitparam_isgroupsubstation as smpunitparam_isgroupsubstation,
                    smpunitparam_isdispnocontrol as smpunitparam_isdispnocontrol,
                    smpunitparam_isdocnocontrol as smpunitparam_isdocnocontrol,
                    smpunitparam_isdispothercontrol as smpunitparam_isdispothercontrol,
                    smpunitparam_issavetreepath as smpunitparam_issavetreepath,
                    smpunitparam_iscallapprovesend as smpunitparam_iscallapprovesend,
                    smpunitparam_isnotransother as smpunitparam_isnotransother,
                    smpunitparam_isdenycallanswerdisp as smpunitparam_isdenycallanswerdisp,
                    smpunitparam_isshowcallnoteam as smpunitparam_isshowcallnoteam
					from v_SmpUnitParam 

					where LpuBuilding_id = :LpuBuilding_pid
					order by SmpUnitParam_id desc
					limit 1
				) SUP ON TRUE
				left join v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLCity_id

				left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLTown_id

				left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id

				left join v_CmpCallRecord CCR  on CCC.CmpCallCard_id = CCR.CmpCallCard_id

				LEFT JOIN LATERAL(

					select
						CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv as CmpUrgencyAndProfileStandart_HeadDoctorObserv,
						CCCA.CmpCallCardAcceptor_Code as CmpCallCardAcceptor_Code
					from
						v_CmpUrgencyAndProfileStandart CUPS 

					left join v_CmpCallCardAcceptor CCCA  on CCCA.CmpCallCardAcceptor_id = CUPS.CmpCallCardAcceptor_id

					where
						CUPS.CmpReason_id = CCC.CmpReason_id
						AND (COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) > COALESCE(CCC.Person_Age,0))

						AND COALESCE(CUPS.Lpu_id,0) in (:CmpLpu_id)
                    limit 1

				) CUAPS ON TRUE
				 LEFT JOIN LATERAL (

					SELECT
					CmpCallCardEvent_updDT as CmpCallCardEvent_updDT, CmpCallCardEventType_Name as CmpCallCardEventType_Name, CmpCallCardEventType_Code as CmpCallCardEventType_Code, ETS.EmergencyTeamStatus_id as EmergencyTeamStatus_id, EmergencyTeamStatus_Code as EmergencyTeamStatus_Code, EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
					FROM v_CmpCallCardEvent CCCE 

						inner JOIN v_CmpCallCard CCCA on CCCA.CmpCallCard_id = CCCE.CmpCallCard_id
						LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

						LEFT JOIN v_EmergencyTeamStatusHistory ETSH  on CCCE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

						LEFT JOIN v_EmergencyTeamStatus ETS  on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id

					WHERE CCCE.CmpCallCard_id = CCC.CmpCallCard_id AND CCCET.CmpCallCardEventType_IsKeyEvent = 2 ORDER BY CmpCallCardEvent_updDT desc
					limit 1
					) as activeEvent ON TRUE
				left join v_Lpu L  on L.Lpu_id = CCC.Lpu_hid

				left join v_MedService MS  on MS.MedService_id = CCC.MedService_id

				LEFT JOIN LATERAL(

					select
						COUNT(CCCDouble.CmpCallCard_id) as Duplicate_Count
					from
						v_CmpCallCard CCCDouble 

					left join v_CmpCallCardStatusType CCCSTDouble  on CCCSTDouble.CmpCallCardStatusType_id = CCCDouble.CmpCallCardStatusType_id

					where
						CCCDouble.CmpCallCard_rid = CCC.CmpCallCard_id
						and CCCSTDouble.CmpCallCardStatusType_Code = 9
						and COALESCE(CCCDouble.CmpCallCard_IsActiveCall, 1) != 2
				) CCCD ON TRUE
				LEFT JOIN LATERAL(

					select
						COUNT(CCCActiveCall.CmpCallCard_id) as ActiveCall_Count
					from
						v_CmpCallCard CCCActiveCall 

					where
						CCCActiveCall.CmpCallCard_rid = CCC.CmpCallCard_id
						and COALESCE(CCCActiveCall.CmpCallCard_IsActiveCall, 1) = 2
				) CCCAC ON TRUE
				LEFT JOIN LATERAL(

					select
						to_char(CIA.CmpIllegalAct_prmDT, 'DD.MM.YYYY') as CmpIllegalAct_prmDT,

						CIA.CmpIllegalAct_Comment,
						CASE WHEN CIA.Person_id = CCC.Person_id THEN '2' ELSE '1' END as CmpIllegalAct_byPerson
					from
						v_CmpIllegalAct CIA 

					where
						CIA.Person_id = CCC.Person_id
						OR (
							(CIA.KLRgn_id = CCC.KLRgn_id or CIA.KLRgn_id = dbo.getregion()) AND
							(CIA.KLSubRGN_id = CCC.KLSubRgn_id or CIA.KLSubRGN_id is null) AND
							(CIA.KLCity_id = CCC.KLCity_id or CIA.KLCity_id is null) AND
							(CIA.KLTown_id = CCC.KLTown_id or CIA.KLTown_id is null) AND
							CIA.KLStreet_id = CCC.KLStreet_id AND
							CIA.Address_House = CCC.CmpCallCard_Dom AND
							(CIA.Address_Corpus = CCC.CmpCallCard_Korp or CIA.Address_Corpus is null) AND
							(CIA.Address_Flat = CCC.CmpCallCard_Kvar or CIA.Address_House = CCC.CmpCallCard_Dom)
						)
					order by CIA.CmpIllegalAct_prmDT desc
					limit 1
				) as cmpIllegalAct ON TRUE
				LEFT JOIN LATERAL (

					SELECT
						CmpCallCardEvent_id as CmpCallCardEvent_id
					FROM v_CmpCallCardEvent	CCCE 

					LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

					WHERE CCC.CmpCallCard_id = CCCE.CmpCallCard_id and CCCET.CmpCallCardEventType_Code = 34 --отлонен
					limit 1
				) CCCEDeny ON TRUE
				
				left join lateral (
					select PQ.*
					from v_PersonQuarantine PQ
					where PQ.Person_id = CCC.Person_id 
					and PQ.PersonQuarantine_endDT is null
					limit 1
				) PQ on true

				-- end from
			where
				-- where
				".implode(" AND ", $filter)."
				-- end where
		";

        $emptyGroups = array();

        for($i = 1; $i <= 8; $i++){
            $arr = array(
                'CmpGroup_id' => $i,
                'CmpCloseCard_id'
            );
            array_push($emptyGroups, $arr);
        };

		//var_dump(getDebugSQL($query, $queryParams)); exit;

		$val = $this->db->query($query, $queryParams)->result('array');
        $result = array_merge($emptyGroups, $val);

		return array(
			'data' => $result,
			'totalCount' => sizeof($val)
		);
	}

	/**
	 * Метод получения списка истории статусов у талона вызова
	 * @param type $data
	 * @return type
	 */
	public function loadCmpCallCardStatusHistory( $data) {
		if (empty($data['CmpCallCard_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор талона вызова'));
		}

		$query = "
			SELECT
			CCCS.CmpCallCard_id as \"CmpCallCard_id\",
			(to_char(cast(CCCS.CmpCallCardStatus_insDT as timestamp(3)), 'DD.MM.YYYY')||' '||to_char(cast(CCCS.CmpCallCardStatus_insDT as timestamp(3)), 'HH24:MI:SS')) as \"CmpCallCardStatus_insDT\",


			CCCST.CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\",
			RTRIM(PUC.PMUser_surName||' '||SUBSTRING(PUC.PMUser_firName,1,1) ||' '||SUBSTRING(PUC.PMUser_secName,1,1)) as \"pmUser_FIO\"

			FROM v_CmpCallCardStatus as CCCS 


			LEFT JOIN v_CmpCallCardStatusType as CCCST  ON (CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id)

			LEFT JOIN v_pmUserCache as PUC  ON (PUC.PMUser_id = CCCS.pmUser_insID)


			WHERE CCCS.CmpCallCard_id = :CmpCallCard_id
			";
		$queryParams[ 'CmpCallCard_id' ] = $data['CmpCallCard_id'] ;

		return $this->queryResult($query , $queryParams);
	}

	/**
	 * Метод получения списка истории статусов у талона вызова
	 * @param type $data
	 * @return type
	 */
	public function loadCmpCallCardEventHistory($data)
	{
		if (empty($data['CmpCallCard_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор талона вызова'));
		}

		$query = "
			SELECT DISTINCT
				CmpCallCardEvent_id as \"event_id\",
				to_char(CCCE.CmpCallCardEvent_insDT, 'YYYY-MM-DD HH24:MI:SS') as \"EventDT\",

				CCCET.CmpCallCardEventType_Name as \"CmpCallCardEventType_Name\",
				CCCE.CmpCallCardEvent_insDT as \"CmpCallCardEvent_insDT\",
				RTRIM(PUC.PMUser_surName||' '||SUBSTRING(PUC.PMUser_firName,1,1) ||' '||SUBSTRING(PUC.PMUser_secName,1,1)) as \"pmUser_FIO\",

				--подстанция из комментария
				CASE WHEN CmpCallCardEventType_Code in (27) THEN COALESCE(СLB.LpuBuilding_Nick, СLB.LpuBuilding_Name, '')
				ELSE
					--подстанция
					CASE WHEN CmpCallCardEventType_Code in (1,6) THEN COALESCE(LB.LpuBuilding_Nick, LB.LpuBuilding_Name, '')
						ELSE
							--отделение
							CASE WHEN CmpCallCardEventType_Code in (2) THEN LS.LpuSection_Name
								ELSE
									--номер бригады
									CASE WHEN CmpCallCardEventType_Code in (4,5,7,8,9,10,11,12) THEN ET.EmergencyTeam_Num
										ELSE
											--номер вызова за день
											CASE WHEN CmpCallCardEventType_Code in (16,17,18,19) THEN COALESCE( cast( childCCC.CmpCallCard_Numv as varchar ), CCCE.CmpCallCardEvent_Comment)
												ELSE
													--все остальное
													CCCE.CmpCallCardEvent_Comment
											END
									END
							END
					END
				END as \"EventValue\"
			FROM v_CmpCallCardEvent CCCE 

			LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

			LEFT JOIN v_pmUserCache PUC  ON (PUC.PMUser_id = CCCE.pmUser_insID)

			LEFT JOIN v_LpuBuilding LB  on CCCE.LpuBuilding_id = LB.LpuBuilding_id

			LEFT JOIN v_LpuBuilding СLB on CCCE.CmpCallCardEvent_Comment = CAST(СLB.LpuBuilding_id AS VARCHAR(20))
			LEFT JOIN v_LpuSection LS  on CCCE.LpuSection_id = LS.LpuSection_id

			LEFT JOIN v_EmergencyTeam ET  on CCCE.EmergencyTeam_id = ET.EmergencyTeam_id

			LEFT JOIN v_CmpCallCard childCCC  on CCCE.CmpCallCard_cid = childCCC.CmpCallCard_id

			WHERE CCCE.CmpCallCard_id = :CmpCallCard_id

			ORDER BY EventDT
			";
		$queryParams[ 'CmpCallCard_id' ] = $data['CmpCallCard_id'] ;
		//var_dump(getDebugSQL($query, $queryParams)); exit;
		
		return $this->queryResult($query , $queryParams);
	}

	/**
	 * Метод получения количества закрытых вызовов за смену указанной бригады
	*/
	public function getCountCateredCmpCallCards( $data) {
		if (empty($data['EmergencyTeam_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор бригады'));
		}

		$query = "
			SELECT
			COUNT(*) as \"countCateredCmpCallCards\"
			FROM v_CmpCallCard ccc 

			WHERE ccc.EmergencyTeam_id = :EmergencyTeam_id
			and CmpCallCardStatusType_id IN (4,6,7,8,18)";

		$queryParams[ 'EmergencyTeam_id' ] = $data['EmergencyTeam_id'] ;

		return $this->queryResult($query , $queryParams);
	}

	/**
	 * Проверка наличия закрытых вызовов за последние сутки по указанному адресу
	 *
	 * @param array $data
	 * @return array
	 */
	public function checkLastDayClosedCallsByAddress($data){
		if (empty($data['KLStreet_id']) || empty($data['CmpCallCard_Dom']) || empty($data['CmpCallCard_Kvar'])) {
			return array();
		}

		$sql = "
			SELECT

			PS.Person_id as \"Person_id\"
			,PS.PersonEvn_id as \"PersonEvn_id\"
			,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

			,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

			,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

			,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"

			,RGN.KLRgn_id as \"KLRgn_id\"
			,SRGN.KLSubRgn_id as \"KLSubRgn_id\"
			,City.KLCity_id as \"KLCity_id\"
			,COALESCE(City.KLSocr_Nick || ' ' || City.KLCity_Name, '') as \"KLCity_Name\"
			,Town.KLTown_id as \"KLTown_id\"
			,COALESCE(Town.KLSocr_Nick || ' ' || Town.KLTown_Name, '') as \"KLTown_Name\"
			,Street.KLStreet_id as \"KLStreet_id\"
			,Street.KLStreet_FullName as \"KLStreet_FullName\"
			,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
			,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
			,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
			,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
			,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
			,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
			,CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\"
			,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
			,CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\"
			,CCC.Person_Age as \"Person_Age\"
			,CCC.Sex_id as \"Sex_id\"
			,CCC.CmpCallerType_id as \"CmpCallerType_id\"
			,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
			,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
			,CCC.CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\"
			,UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\"
			,UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\"
			,UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\"
			,UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\"

			,case when COALESCE(CCC.KLStreet_id,0) = 0 then

				case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

				else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"

			,CCC.CmpLpu_id as \"lpuLocalCombo\"
			,CCC.LpuBuilding_id as \"LpuBuilding_id\"
			-- end new select

			,CCC.CmpCallCard_id as \"CallCard_id\"
			,to_char (cast(CCCST_T.CmpCallCardStatus_insDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as CmpCallCardStatus_insDT
			,to_char (cast(CCC.CmpCallCard_Tper as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_Tper\"
			,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
			,COALESCE(CCC.Person_SurName, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\"
			,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
			,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
			,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
			,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

			,CR.CmpReason_id as \"CmpReason_id\"
			,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"


			,overlay(
				COALESCE(', ' || RGN.KLRgn_FullName, '')
				|| CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' || SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' || City.KLCity_Name, '') END
				|| COALESCE(', ' || Town.KLTown_FullName, '')
				|| COALESCE(', ' || Street.KLStreet_FullName, '')
				|| COALESCE(', д.' || CCC.CmpCallCard_Dom, '')
				|| COALESCE(', к.' || CCC.CmpCallCard_Korp, '')
				|| COALESCE(', кв.' || CCC.CmpCallCard_Kvar, '')
				|| COALESCE(', комн.' || CCC.CmpCallCard_Comm, '')
				|| COALESCE(', место: ' || UAD.UnformalizedAddressDirectory_Name, '')
					-- параметры STUFF
					placing '' from 1 for 2
				) as \"Adress_Name\"

			FROM
				v_CmpCallCard CCC 

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

						left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

						left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

						left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

						left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

						left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

						left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

						left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

                        left join (
							  SELECT
									  		MIN(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
									 		CCCS.CmpCallCard_id as CmpCallCard_id
							  from 			v_CmpCallCardStatus CCCS 

							  inner join 	v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id

							  left join		v_CmpCallCard VCCC  on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id

							  where 		CCCST.CmpCallCardStatusType_Code::int = 2
							  				and VCCC.Lpu_ppdid IS NOT NULL
											--and VCCC.CmpCallCard_IsReceivedInPPD = 1

							  group by 		CCCS.CmpCallCard_id
									)
									CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
						left join dbo.v_PersonState PS  on PS.Person_id = CCC.Person_id

						left join dbo.v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

			WHERE
				CCC.KLStreet_id=:KLStreet_id
				AND CCC.CmpCallCard_Dom=:CmpCallCard_Dom
				AND COALESCE(CCC.CmpCallCard_Korp, '-1')=COALESCE(:CmpCallCard_Korp, '-1')
				AND CCC.CmpCallCard_Kvar=:CmpCallCard_Kvar
				AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, getdate()) <= 24
				and CCC.CmpCallCardStatusType_id in (4,6)
		";

		return $this->db->query($sql, array(
			'KLStreet_id' => $data['KLStreet_id'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Korp' => !empty($data['CmpCallCard_Korp']) ? $data['CmpCallCard_Korp'] : null,
			'CmpCallCard_Kvar' => $data['CmpCallCard_Kvar'],
		))->result_array();
	}

	/**
	 * Проверка наличия закрытых вызовов за последние сутки по указанному пациенту
	 *
	 * @param int $Person_id
	 * @return array
	 */
	public function checkLastDayClosedCallsByPersonId($Person_id){
		if (empty($Person_id)) {
			return array();
		}

		$sql = "
			SELECT
				PS.Person_id as \"Person_id\"
						,PS.PersonEvn_id as \"PersonEvn_id\"
						,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

						,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

						,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

						,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"

						,RGN.KLRgn_id as \"KLRgn_id\"
						,SRGN.KLSubRgn_id as \"KLSubRgn_id\"
						,City.KLCity_id as \"KLCity_id\"
						,COALESCE(City.KLSocr_Nick || ' ' || City.KLCity_Name, '') as \"KLCity_Name\"
						,Town.KLTown_id as \"KLTown_id\"
						,COALESCE(Town.KLSocr_Nick || ' ' || Town.KLTown_Name, '') as \"KLTown_Name\"
						,Street.KLStreet_id as \"KLStreet_id\"
						,Street.KLStreet_FullName as \"KLStreet_FullName\"
						,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
						,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
						,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
						,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
						,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
						,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
						,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
						,CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\"
						,CCC.Person_Age as \"Person_Age\"
						,CCC.Sex_id as \"Sex_id\" 
						,CCC.CmpCallerType_id as \"CmpCallerType_id\"
						,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
						,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
						,CCC.CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\"
						,UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\"
						,UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\"
						,UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\"
						,UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\"

						,case when COALESCE(CCC.KLStreet_id,0) = 0 then

							case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

							else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
							else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"

						,CCC.CmpLpu_id as \"lpuLocalCombo\"
						,CCC.LpuBuilding_id as \"LpuBuilding_id\"
						-- end new select

						,CCC.CmpCallCard_id as \"CallCard_id\"
						,to_char (cast(CCCST_T.CmpCallCardStatus_insDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCardStatus_insDT\"
						,to_char (cast(CCC.CmpCallCard_Tper as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_Tper\"
						,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
						,COALESCE(CCC.Person_SurName, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\"
						,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
						,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
						,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
						,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

						,CR.CmpReason_id as \"CmpReason_id\"
						,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"


						,OVERLAY(
     						COALESCE(', ' || RGN.KLRgn_FullName, '')
     						|| CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' || SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' || City.KLCity_Name, '') END
     						|| COALESCE(', ' || Town.KLTown_FullName, '')
     						|| COALESCE(', ' || Street.KLStreet_FullName, '')
     						|| COALESCE(', д.' || CCC.CmpCallCard_Dom, '')
							|| COALESCE(', к.' || CCC.CmpCallCard_Korp, '')
     						|| COALESCE(', кв.' || CCC.CmpCallCard_Kvar, '')
     						|| COALESCE(', комн.' || CCC.CmpCallCard_Comm, '')
                            || COALESCE(', место: ' || UAD.UnformalizedAddressDirectory_Name, '')
     							-- параметры STUFF
     						    placing '' from 1 for 2
    						) as Adress_Name

			FROM
				v_CmpCallCard CCC 

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

						left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

						left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

						left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

						left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

						left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

						left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

						left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

                        left join (
							  SELECT
									  		MIN(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
									 		CCCS.CmpCallCard_id as CmpCallCard_id
							  from 			v_CmpCallCardStatus CCCS 

							  inner join 	v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id

							  left join		v_CmpCallCard VCCC  on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id

							  where 		CCCST.CmpCallCardStatusType_Code::int = 2
							  				and VCCC.Lpu_ppdid IS NOT NULL
											--and VCCC.CmpCallCard_IsReceivedInPPD = 1

							  group by 		CCCS.CmpCallCard_id
									)
									CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
						left join dbo.v_PersonState PS  on PS.Person_id = CCC.Person_id

						left join dbo.v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

			WHERE
				CCC.Person_id=:Person_id
				AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, getdate()) <= 24
				and CCC.CmpCallCardStatusType_id in (4,6)

		";

		return $this->db->query($sql, array(
			'Person_id' => $Person_id
		))->result_array();
	}
	
	/**
	 * Проверка наличия закрытых вызовов за последние сутки по указанному адресу и по пациенту
	 *
	 * @param array $data
	 * @return array
	 */
	public function checkLastDayClosedCallsByAddressAndPersonId($data){

		$filter = array();
		$params = array(
			'Person_Surname' => $data['Person_Surname'],
			'Person_Firname' => $data['Person_Firname'],
			'Person_Secname' => $data['Person_Secname'],
		);

		if(!empty($data['dStreetsCombo']) && empty($data['UnformalizedAddressDirectory_id']) && empty($data['KLStreet_id']) && $data['KLStreet_id'] == 0){
			if (preg_match("/UA|ST/i", $data['dStreetsCombo']))
			{
				$str_del = array("UA.", "ST.");
				$str_val = str_replace($str_del, "", $data['dStreetsCombo']);
				if(intval($str_val))
				{
					$data['UnformalizedAddressDirectory_id'] = intval($str_val);
					$filter[] = 'CCC.UnformalizedAddressDirectory_id=:UnformalizedAddressDirectory_id';
				}
			}
		}

		if(!empty($data['UnformalizedAddressDirectory_id']))
		{
			$filter[] = 'CCC.UnformalizedAddressDirectory_id=:UnformalizedAddressDirectory_id';
			$params['UnformalizedAddressDirectory_id'] = $data['UnformalizedAddressDirectory_id'];
		}
		else{
			$filter[] = 'CCC.UnformalizedAddressDirectory_id is null';
		}

		if(!empty($data['KLStreet_id']) && $data['KLStreet_id'] != 0){
			$filter[] = 'CCC.KLStreet_id=:KLStreet_id';
			$params['KLStreet_id'] = $data['KLStreet_id'];
		}else{
			$filter[] = 'CCC.KLStreet_id is null';
		}

		if(!empty($data['CmpCallCard_UlicSecond']) && $data['CmpCallCard_UlicSecond'] != 0){
			$filter[] = 'CCC.CmpCallCard_UlicSecond=:CmpCallCard_UlicSecond';
			$params['CmpCallCard_UlicSecond'] = $data['CmpCallCard_UlicSecond'];
		}

		if(!empty($data['CmpCallCard_Dom'])){
			$filter[] = 'CCC.CmpCallCard_Dom=:CmpCallCard_Dom';
			$params['CmpCallCard_Dom'] = $data['CmpCallCard_Dom'];
		}else{
			$filter[] = 'CCC.CmpCallCard_Dom is null';
		}

		if(!empty($data['CmpCallCard_Korp'])){
			$filter[] = "COALESCE(CCC.CmpCallCard_Korp, '-1')=COALESCE(:CmpCallCard_Korp, '-1')";
			$params['CmpCallCard_Korp'] = $data['CmpCallCard_Korp'];
		}else{
			$filter[] = 'CCC.CmpCallCard_Korp is null';
		}

		if(!empty($data['CmpCallCard_Kvar'])){
			$filter[] = 'CCC.CmpCallCard_Kvar=:CmpCallCard_Kvar';
			$params['CmpCallCard_Kvar'] = $data['CmpCallCard_Kvar'];
		}else{
			$filter[] = 'CCC.CmpCallCard_Kvar is null';
		}

		$filter[] ="(C112.CmpCallCard112StatusType_id is null or C112.CmpCallCard112StatusType_id in (3,4,5))";


		$sql = "
			SELECT
				PS.Person_id as \"Person_id\"
				,PS.PersonEvn_id as \"PersonEvn_id\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

				,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

				,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

				,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"

				,RGN.KLRgn_id as \"KLRgn_id\"
				,SRGN.KLSubRgn_id as \"KLSubRgn_id\"
				,City.KLCity_id as \"KLCity_id\"
				,COALESCE(City.KLSocr_Nick || ' ' || City.KLCity_Name, '') as \"KLCity_Name\"
				,Town.KLTown_id as \"KLTown_id\"
				,COALESCE(Town.KLSocr_Nick || ' ' || Town.KLTown_Name, '') as \"KLTown_Name\"
				,Street.KLStreet_id as \"KLStreet_id\"
				,Street.KLStreet_FullName as \"KLStreet_FullName\"
				,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
				,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
				,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
				,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
				,CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\"
				,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
				,CCC.CmpCallCard_IsExtra as \"CmpCallCard_IsExtra\"
				,CCC.Person_Age as \"Person_Age\"
				,CCC.Sex_id as \"Sex_id\"
				,CCC.CmpCallerType_id as \"CmpCallerType_id\"
				,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
				,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
				,CCC.CmpCallCard_IsDeterior as \"CmpCallCard_IsDeterior\"
				,UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\"
				,UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\"
				,UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\"
				,UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\"

				,case when COALESCE(CCC.KLStreet_id,0) = 0 then

					case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

					else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
					else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"

				,CCC.CmpLpu_id as \"lpuLocalCombo\"
				,CCC.LpuBuilding_id as \"LpuBuilding_id\"
				-- end new select

				,CCC.CmpCallCard_id as \"CallCard_id\"
				,to_char (cast(CCCST_T.CmpCallCardStatus_insDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCardStatus_insDT\"
				,to_char (cast(CCC.CmpCallCard_Tper as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_Tper\"
				,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
				,COALESCE(CCC.Person_SurName, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"
				,CR.CmpReason_id as \"CmpReason_id\"
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"


				,OVERLAY(
					COALESCE(', ' || RGN.KLRgn_FullName, '')
					|| CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' || SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' || City.KLCity_Name, '') END
					|| COALESCE(', ' || Town.KLTown_FullName, '')
					|| COALESCE(', ' || Street.KLStreet_FullName, '')
					|| COALESCE(', д.' || CCC.CmpCallCard_Dom, '')
					|| COALESCE(', к.' || CCC.CmpCallCard_Korp, '')
					|| COALESCE(', кв.' || CCC.CmpCallCard_Kvar, '')
					|| COALESCE(', комн.' || CCC.CmpCallCard_Comm, '')
					|| COALESCE(', место: ' || UAD.UnformalizedAddressDirectory_Name, '')
						-- параметры STUFF
					    placing '' from 1 for 2
					) as \"Adress_Name\"

			FROM
				v_CmpCallCard CCC 

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

				left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join (
					  SELECT
									MIN(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
									CCCS.CmpCallCard_id as CmpCallCard_id
					  from 			v_CmpCallCardStatus CCCS 

					  inner join 	v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id

					  left join		v_CmpCallCard VCCC  on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id

					  where 		CCCST.CmpCallCardStatusType_Code::int = 2
									and VCCC.Lpu_ppdid IS NOT NULL
									--and VCCC.CmpCallCard_IsReceivedInPPD = 1

					  group by 		CCCS.CmpCallCard_id
							)
							CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
				left join dbo.v_PersonState PS  on PS.Person_id = CCC.Person_id

				left join dbo.v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

				left join v_CmpCallCard112 C112  on CCC.CmpCallCard_id = C112.CmpCallCard_id


			WHERE
				".implode(" AND ", $filter)."
				AND DATEDIFF('hour', CCC.CmpCallCard_prmDT, getdate()) <= 24
				and CCC.CmpCallCardStatusType_id in (4,6)
				AND CCC.Person_SurName=:Person_Surname
				AND CCC.Person_FirName=:Person_Firname
				AND CCC.Person_SecName=:Person_Secname
		";

		return $this->db->query($sql, $params)->result_array();
	}
	
	/**
	 * Получение типов повода отказа
	 *
	 * @return array
	 */
	public function getRejectionReason(){

		$sql = "
			SELECT
				СRR.CmpRejectionReason_id as \"CmpRejectionReason_id\",
				СRR.CmpRejectionReason_code as \"CmpRejectionReason_code\",
				СRR.CmpRejectionReason_name as \"CmpRejectionReason_name\"
			FROM
				v_CmpRejectionReason СRR ";

		//var_dump(getDebugSql($sql, array()));
		//var_dump($this->db); exit;
 		return $this->db->query($sql, array())->result_array();
	}

	/**
	 * Получение списка карт вызовов для выбора в дубли
	 *
	 * @return array
	 */
	public function getCmpCallCardListForDoubleChoose($data){
		$lpuBuildingsWorkAccess = null;

		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);
		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		}

		$filter = array();
		$filter[] = "CCC.Lpu_id = :Lpu_id";
		$filter[] = "COALESCE(CCC.CmpCallCard_IsReceivedInPPD,1)!=2";

		switch ($_SESSION['region']['nick']) {
			case 'pskov':
			{
				if ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
					$lpuBuildingQuery = "
					SELECT
						COALESCE(MS.LpuBuilding_id,0) as \"LpuBuilding_id\"

					FROM
						v_MedService MS 

					WHERE
						MS.MedService_id = :MedService_id
					";
					$lpuBuildingResult = $this->db->query( $lpuBuildingQuery, array(
						'MedService_id' => $data[ 'session' ][ 'CurMedService_id' ]
					) );
					if ( is_object( $lpuBuildingResult ) ) {
						$lpuBuildingResult = $lpuBuildingResult->result( 'array' );
						if ( isset( $lpuBuildingResult[ 0 ] ) && (!empty( $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ] )) ) {
							$filter[] = "CCC.LpuBuilding_id = :LpuBuilding_id";
							$sqlArr[ 'LpuBuilding_id' ] = $lpuBuildingResult[ 0 ][ 'LpuBuilding_id' ];
						}
					}
				}
				break;
			}
			case 'ufa':
			{
				if ( !(empty( $lpuBuildingsWorkAccess)) ) {
					if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );

					// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
					$lpuFilter ="CCC.LpuBuilding_id in (";
					foreach ($lpuBuildingsWorkAccess as &$value) {
						$lpuFilter .= $value.',';
					}
					$filter[] = substr($lpuFilter, 0, -1).')';
				}
				elseif ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
					// Отображаем только те вызовы, которые переданы на эту подстанцию (#38949)
					if ( $is_pg ) {
						$filter[] = "MS.\"MedService_id\" = :MedService_id";
						$join[] = "LEFT JOIN dbo.\"v_MedService\" MS on MS.\"LpuBuilding_id\" = CCC.\"LpuBuilding_id\"";
					} else {
						$filter[] = "MS.MedService_id = :MedService_id";
						$join[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

					}
					$sqlArr[ 'MedService_id' ] = $data[ 'session' ][ 'CurMedService_id' ];

				} else {
					return array( array( 'success' => false, 'Error_Msg' => 'Не установлен идентификатор службы' ) );
				}
				break;
			}

			default:
			{
				if ( !(empty( $lpuBuildingsWorkAccess)) ) {
					if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
					// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
					$lpuFilter ="CCC.LpuBuilding_id in (";
					foreach ($lpuBuildingsWorkAccess as &$value) {
						$lpuFilter .= $value.',';
					}
					$filter[] = substr($lpuFilter, 0, -1).')';
				}
				elseif ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
					// Отображаем только те вызовы, которые переданы на эту подстанцию (#38949)
					if ( $is_pg ) {
						$filter[] = "MS.\"MedService_id\" = :MedService_id";
						$join[] = "LEFT JOIN dbo.\"v_MedService\" MS on MS.\"LpuBuilding_id\" = CCC.\"LpuBuilding_id\"";
					} else {
						$filter[] = "MS.MedService_id = :MedService_id";
						$join[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

					}
					$sqlArr[ 'MedService_id' ] = $data[ 'session' ][ 'CurMedService_id' ];

				} else {
					return array( array( 'success' => false, 'Error_Msg' => 'Не установлен идентификатор службы' ) );
				}
				break;
			}
		}

		$filter[] = "CCC.CmpCallCardStatusType_id IN(1,2,4,7)";
		$filter[] = "CCT.CmpCallType_Code in(1,2)";

		if ( !(empty( $data['doubleCmpCallCard_id'] )) ) {
			$filter[] = "CCC.CmpCallCard_id != :doubleCmpCallCard_id";
		}

		$query = "SELECT
			--begin new select

			PS.Person_id as \"Person_id\"
			,PS.PersonEvn_id as \"PersonEvn_id\"
			,COALESCE(PS.Person_Surname, CCC.Person_SurName) as \"Person_Surname\"

			,COALESCE(PS.Person_Firname, CCC.Person_FirName) as \"Person_Firname\"

			,COALESCE(PS.Person_Secname, CCC.Person_SecName) as \"Person_Secname\"

			,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"
			,RGN.KLRgn_id as \"KLRgn_id\"
			,SRGN.KLSubRgn_id as \"KLSubRgn_id\"
			,City.KLCity_id as \"KLCity_id\"
			,COALESCE(City.KLSocr_Nick || ' ' || City.KLCity_Name, '') as \"KLCity_Name\"
			,Town.KLTown_id as \"KLTown_id\"
			,COALESCE(Town.KLSocr_Nick || ' ' || Town.KLTown_Name, '') as \"KLTown_Name\"
			,Street.KLStreet_id as \"KLStreet_id\"
			,Street.KLStreet_FullName as \"KLStreet_FullName\"
			,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
			,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
			,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
			,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
			,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
			,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
			,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
			,CCC.Person_Age as \"Person_Age\"
			,CCC.Sex_id as \"Sex_id\"
			,CCC.CmpCallerType_id as \"CmpCallerType_id\"
			,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
			,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
			,UAD.UnformalizedAddressDirectory_id as \"UnformalizedAddressDirectory_id\"
			,UAD.UnformalizedAddressType_id as \"UnformalizedAddressType_id\"
			,UAD.UnformalizedAddressDirectory_Dom as \"UnformalizedAddressDirectory_Dom\"
			,UAD.UnformalizedAddressDirectory_Name as \"UnformalizedAddressDirectory_Name\"

			-- end new select

			,CCC.CmpCallCard_id as \"CmpCallCard_id\"
			,CCC.CmpCallCard_rid as \"CmpCallCard_rid\"
			,to_char (cast(CCCST_T.CmpCallCardStatus_insDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCardStatus_insDT\"
			,to_char (cast(CCC.CmpCallCard_Tper as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_Tper\"
			,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
			,COALESCE(CCC.Person_SurName, '') || ' ' || COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\"
			,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
			,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
			,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
			,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

			,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"


			,OVERLAY(
				COALESCE(', ' || RGN.KLRgn_FullName, '')
				|| CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' || SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' || City.KLCity_Name, '') END
				|| COALESCE(', ' || Town.KLTown_FullName, '')
				|| COALESCE(', ' || Street.KLStreet_FullName, '')
				|| COALESCE(', д.' || CCC.CmpCallCard_Dom, '')
				|| COALESCE(', к.' || CCC.CmpCallCard_Korp, '')
				|| COALESCE(', кв.' || CCC.CmpCallCard_Kvar, '')
				|| COALESCE(', комн.' || CCC.CmpCallCard_Comm, '')
				|| COALESCE(', место: ' || UAD.UnformalizedAddressDirectory_Name, '')
					-- параметры STUFF
				    placing '' from 1 for 2
				) as \"Adress_Name\"

		from
			-- from
			v_CmpCallCard CCC 

			left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

			left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

			left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

			left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

			left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

			left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

			left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

			left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

			left join (
				  SELECT
								MIN(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
								CCCS.CmpCallCard_id as CmpCallCard_id
				  from 			v_CmpCallCardStatus CCCS 

				  inner join 	v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id

				  left join		v_CmpCallCard VCCC  on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id

				  where 		CCCST.CmpCallCardStatusType_Code::int = 2
								and VCCC.Lpu_ppdid IS NOT NULL
								--and VCCC.CmpCallCard_IsReceivedInPPD = 1

				  group by 		CCCS.CmpCallCard_id
						)
						CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
			left join dbo.v_PersonState PS  on PS.Person_id = CCC.Person_id

			left join dbo.v_CmpCallCardLockList CCCLL  on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id

		where
		-- where
		".implode(" AND ", $filter)."
		-- end where
		";

		//var_dump(getDebugSQL($query, $data)); exit;

		return $this->db->query($query, $data)->result_array();

	}


	/**
	 * Метод сохранения записи с названием аудио файла вызова и прочей информации
	*/
	public function saveCallAudio( $data ) {

		$audio = str_replace('data:audio/mp3;base64,', '', $data['callAudio']);

		$decoded = base64_decode($audio);

		$date = date("d-m-Y");
		$time = date("H-i-s");

		$dirAudioRecords = "./uploads/audioCalls";
		if(!is_dir($dirAudioRecords)) mkdir($dirAudioRecords, 0777, true);

		$dirPath = $dirAudioRecords .'/'. $date;

		$fileName = $time. '_' .$data['session']['pmuser_id'] . '.mp3';

		if(!is_dir($dirPath)) mkdir($dirPath);

		$file_location = $dirPath .'/'.$fileName ;

		file_put_contents($file_location, $decoded);

		$data['CmpCallRecord_RecordPlace'] = $date .'/'. $fileName;

		$query = "
            SELECT 
                Res as \"CmpCallRecord_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            
			FROM p_CmpCallRecord_ins (
				CmpCallRecord_id := Res,
				CmpCallRecord_begDT := dbo.tzGetDate(),
				Lpu_id := :Lpu_id,
				LpuBuilding_id := :LpuBuilding_id,
				MedStaffFact_id := :MedStaffFact_id,
				CmpCallRecord_RecordPlace := :CmpCallRecord_RecordPlace,
				CmpCallCard_id := :CmpCallCard_id,
				pmUser_id := :pmUser_id
				)
		";

		$queryParams = array(
			'Lpu_id' => isset($data['session']['lpu_id'])?$data['session']['lpu_id']:null,
			'LpuBuilding_id' => isset($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'MedStaffFact_id' => isset($data['session']['MedStaffFact'][0])?$data['session']['MedStaffFact'][0]:null,
			'CmpCallRecord_RecordPlace' => $data['CmpCallRecord_RecordPlace'],
			'CmpCallCard_id' => isset($data['CmpCallCard_id'])?$data['CmpCallCard_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);

		//return $this->db->query($query, $data)->result_array();
		return $this->queryResult($query , $queryParams);
	}

	/**
	 *  Метод получения записи с названием аудио файла вызова и прочей информацией
	*/
	public function getCallAudio( $data ) {
		$sql = "
			SELECT
				CCR.CmpCallRecord_id as \"CmpCallRecord_id\",
				CCR.Lpu_id as \"Lpu_id\",
				CCR.MedStaffFact_id as \"MedStaffFact_id\",
				CCR.CmpCallRecord_RecordPlace as \"CmpCallRecord_RecordPlace\",
				CCR.CmpCallCard_id as \"CmpCallCard_id\",
                CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
                CCC.CmpCallCard_sid as \"CmpCallCard_sid\",
                COALESCE(CCC.Person_SurName, '') || ' '
				|| COALESCE(SUBSTRING(CCC.Person_FirName,1,1 ), '')|| ' '
				|| COALESCE(SUBSTRING(CCC.Person_SecName,1,1 ), '') as \"Person_FIO\",
				to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY')||' '||to_char(CCC.CmpCallCard_prmDT, 'HH24:MI:SS') as \"CmpCallCard_prmDT\"


			FROM
				v_CmpCallCard ccc 
                left join v_CmpCallRecord CCR on CCR.CmpCallCard_id = ccc.CmpCallCard_id
			WHERE
				CCR.CmpCallRecord_id = :CmpCallRecord_id
            limit 1
		";

		$connectedCallInfo = $this->db->query($sql, array("CmpCallRecord_id" => $data["CmpCallRecord_id"]))->result_array();

		$firstCall_id = !empty($connectedCallInfo[0]['CmpCallCard_sid']) ? $connectedCallInfo[0]['CmpCallCard_sid'] : $connectedCallInfo[0]['CmpCallCard_id'];

		$sql = "
			SELECT
				CCR.CmpCallRecord_id as \"CmpCallRecord_id\",
				CCR.Lpu_id as \"Lpu_id\",
				CCR.MedStaffFact_id as \"MedStaffFact_id\",
				CCR.CmpCallRecord_RecordPlace as \"CmpCallRecord_RecordPlace\",
				CCR.CmpCallCard_id as \"CmpCallCard_id\",
                CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
                CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
                COALESCE(CCC.Person_SurName, '') || ' '
				|| COALESCE(SUBSTRING(CCC.Person_FirName,1,1 ), '')|| ' '
				|| COALESCE(SUBSTRING(CCC.Person_SecName,1,1 ), '') as \"Person_FIO\",
				to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY')||' '||to_char(CCC.CmpCallCard_prmDT, 'HH24:MI:SS') as \"CmpCallCard_prmDT\"


			FROM
				v_CmpCallCard ccc 

                left join v_CmpCallRecord CCR on CCR.CmpCallCard_id = ccc.CmpCallCard_id
			WHERE
				CCR.CmpCallRecord_id is not null and ccc.CmpCallCard_id != :CmpCallCard_id and (ccc.CmpCallCard_id = :firstCall_id or ccc.CmpCallCard_sid = :firstCall_id)
			";

 		$calls = $this->db->query($sql, array("CmpCallCard_id" => $connectedCallInfo[0]['CmpCallCard_id'], "firstCall_id" => $firstCall_id))->result_array();
		return array_merge($connectedCallInfo, $calls);
	}

	/**
	 *  Метод получения списка записей с названием аудио файла вызова и прочей информацией
	*/
	public function getCallAudioList( $data ) {

		$this->load->model("User_model", "User_model");
		$groups = $this->User_model->getGroupsDB();

		$user = pmAuthUser::find($_SESSION['login']);
		if (!$user)
			die();

		$recordCallsAuditGroup = $user->havingGroup('recordCallsAudit');

		$sqlArr = array();
		$filter = 'WHERE (1=1)';
		if ( !empty( $data[ 'dateStart' ] ) && !empty( $data[ 'dateFinish' ] ) ) {
			$sqlArr['dateStart'] = date( 'Y-m-d', strtotime( $data[ 'dateStart' ] ) );
			$sqlArr['dateFinish'] = date( 'Y-m-d', strtotime( $data[ 'dateFinish' ] ) );

			$filter .= " and CAST(CCR.CmpCallRecord_insDT as date) >= :dateStart AND CAST(CCR.CmpCallRecord_insDT as date) <= :dateFinish";
		}

		if ( isset($data[ 'audioIds' ]) && !empty( $data[ 'audioIds' ] ) ){
			$sqlArr['audioIds'] = $data[ 'audioIds' ];

			$filter .= " and CCR.CmpCallRecord_id in (".$data[ 'audioIds' ].")";
		}

		if(!$recordCallsAuditGroup){
			//проверка опций
			$user = pmAuthUser::find($_SESSION['login']);
			$settings = @unserialize($user->settings);

			if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
				$settings['lpuBuildingsWorkAccess'];

				$fil = "lb.LpuBuilding_id in (";
				foreach ($settings['lpuBuildingsWorkAccess'] as &$value) {
					$fil .= $value.',';
				}
				$fil = substr($fil, 0, -1).')';
				$filter .= ' and ' . $fil;
			} else {
				return array(array('success'=>false));
			}
		}else{
			$filter .= ' and lpu.Lpu_id is not null';
		}

		$sql = "
			SELECT
				CCR.CmpCallRecord_id as \"CmpCallRecord_id\",
				to_char(cast(CCR.CmpCallRecord_insDT as timestamp(3)), 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallRecord_insDT\",

				lpu.Lpu_Nick as \"Lpu_Nick\",
				lpu.Lpu_id as \"Lpu_id\",
				lb.LpuBuilding_Name as \"LpuBuilding_Name\",
				lb.LpuBuilding_id as \"LpuBuilding_id\",
				CCR.CmpCallRecord_RecordPlace as \"CmpCallRecord_RecordPlace\",
				CCR.CmpCallCard_id as \"CmpCallCard_id\",
				COALESCE(PS.Person_SurName, '') || ' ' || COALESCE(PS.Person_FirName, '') || ' ' || COALESCE(PS.Person_SecName, '') as \"MedPerson_FIO\",

				msfc.MedPersonal_id as \"MedPersonal_id\",
				COALESCE(cccps.Person_Surname, CCC.Person_SurName) as \"Person_Surname\",

				COALESCE(cccps.Person_Firname, CCC.Person_FirName) as \"Person_Firname\",

				COALESCE(cccps.Person_Secname, CCC.Person_SecName) as \"Person_Secname\",

				COALESCE(cccps.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(cccps.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(cccps.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\",
				CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
				CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
			FROM
				v_CmpCallRecord CCR 

				left join v_CmpCallCard ccc on ccc.CmpCallCard_id = CCR.CmpCallCard_id
				left join v_Lpu lpu on CCR.Lpu_id = lpu.Lpu_id
				left join v_LpuBuilding lb on ccc.LpuBuilding_id = lb.LpuBuilding_id
				left join v_MedStaffFactCache msfc on CCR.MedStaffFact_id = msfc.MedStaffFact_id
				left join v_PersonState ps on msfc.Person_id = ps.Person_id
				left join v_PersonState cccps  on cccps.Person_id = CCC.Person_id

				$filter
			";
		//var_dump(getDebugSQL($sql, $sqlArr)); exit;
 		return $this->db->query( $sql, $sqlArr ) -> result_array();
	}

	/**
	* Удаление аудио
	*/
	public function removeCallAudio( $data ) {

		//@todo подумать над правами
		$dirAudioRecords = "./uploads/audioCalls";

		//удалене конкретного аудиофайла и ссылки
		if(isset($data["CmpCallRecord_id"])){
			//выбираем нужную инфу о аудиозаписи
			$sql = "
				SELECT
					CCR.CmpCallRecord_id as \"CmpCallRecord_id\",
					CCR.Lpu_id as \"Lpu_id\",
					CCR.MedStaffFact_id as \"MedStaffFact_id\",
					CCR.CmpCallRecord_RecordPlace as \"CmpCallRecord_RecordPlace\",
					CCR.CmpCallCard_id as \"CmpCallCard_id\",
					CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
					to_char(CCC.CmpCallCard_prmDT, 'DD.MM.YYYY')||' '||to_char(CCC.CmpCallCard_prmDT, 'HH24:MI:SS') as \"CmpCallCard_prmDT\"
				FROM
					v_CmpCallRecord CCR 

					left join v_CmpCallCard ccc on ccc.CmpCallCard_id = CCR.CmpCallCard_id
				WHERE
					CCR.CmpCallRecord_id = :CmpCallRecord_id
				";

			$record = $this->db->query($sql, array("CmpCallRecord_id" => $data["CmpCallRecord_id"]))->result_array();

			if(isset($record[0])){
				$path = $dirAudioRecords .'/'. $record[0]["CmpCallRecord_RecordPlace"];
				//удаляем файл
				if(file_exists ( $path )){ unlink($path); }
			}
			else{
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}

			//удаляем запись в бд
			$query = "
                SELECT 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\";
				FROM p_CmpCallRecord_del (
					CmpCallRecord_id := :CmpCallRecord_id,
					)
			";

			$queryParams = array(
				'CmpCallRecord_id' => $data['CmpCallRecord_id']
			);
		}
		else{
			return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
		}

		return $this->queryResult($query , $queryParams);
	}

	/**
	* Удаление аудио по таймеру и таймауту
	*/
	public function removeCallAudioBytimer() {

		//берем информацию о просроченном таймауте
		$sql = "
			select DataStorage_Value as \"DataStorage_Value\"
			from DataStorage 

			where Lpu_id is null
			and DataStorage_Name = 'audioRecordTimelimit'
			limit 1
		";

		//значение по умолчанию
		$audioRecordTimelimit = 3;

		$opts = $this->db->query($sql)->result_array();

		if( isset($opts[0]) && isset($opts[0]["DataStorage_Value"]) && ($opts[0]["DataStorage_Value"]>2) ){
			$audioRecordTimelimit = $opts[0]["DataStorage_Value"];
		}

		//выбираем просроченные аудиозаписи
		$sql = "
			SELECT
				CCR.CmpCallRecord_id as \"CmpCallRecord_id\",
				CCR.Lpu_id as \"Lpu_id\",
				CCR.MedStaffFact_id as \"MedStaffFact_id\",
				CCR.CmpCallRecord_RecordPlace as \"CmpCallRecord_RecordPlace\"
			FROM
				v_CmpCallRecord CCR 

			WHERE
				cast(CCR.CmpCallRecord_insDT as date) <= DATEADD('month', :audioRecordTimelimit*-1, dbo.tzGetDate())
			";

		//тестовая - cast(CCR.CmpCallRecord_insDT as date) <= DATEADD(day, -73, dbo.tzGetDate())

		$params = array(
			"audioRecordTimelimit" => $audioRecordTimelimit
		);

		$dieMarkedRecords = $this->db->query($sql, $params)->result_array();

		foreach($dieMarkedRecords as $value){
			$this->removeCallAudio($value);
		}

		return array(array('success' => true, 'Error_Code' => null, 'Error_Msg' => null));
	}


	/**
	* Удаление аудио по таймеру и таймауту
	*/
	public function getExportCallAudios($data) {
		$dirAudioRecords = "./uploads/audioCalls";

		$tempZipAudioFolder = $dirAudioRecords . '/' . 'tempZipAudioFolder';
		if(!is_dir($tempZipAudioFolder)) mkdir($tempZipAudioFolder);

		//получаем файлы
		$audios = $this -> getCallAudioList($data);

		$collectAudioFiles = array();

		foreach($audios as $audio){
			array_push( $collectAudioFiles, $dirAudioRecords . '/' . $audio["CmpCallRecord_RecordPlace"] );
		}

		$zipname = $tempZipAudioFolder . '/' . 'audioCalls.zip';

		if(file_exists ( $zipname )){ unlink($zipname); }

		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE);
		foreach ($collectAudioFiles as $file) {
			$zip->addFile($file);
		}

		if ( ($zip -> numFiles) == 0 ){
			$zip->close();
			return array('success'=>false,'Error_Msg'=>'Запрос не вернул результатов. Запрашиваемых файлов нет.');
		}
		$zip->close();

		return array('success'=>true,'Link'=>$zipname);

		/*
		///Then download the zipped file.
		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$zipname);
		header('Content-Length: ' . filesize($zipname));
		readfile($zipname);
		*/
	}

	/**
	 * история статусов бригады
	 */
	public function loadBrigadesHistory($data){
		// если отсутсвует ИД бригады, то выходим
		if(empty($data['EmergencyTeam_id'])) {return false;}
		$query = "
			SELECT
				ETSH.EmergencyTeam_id AS \"id\",
				to_char(CAST( ETSH.EmergencyTeamStatusHistory_insDT as timestamp(3) ), 'YYYY-MM-DD HH24:MI:SS') AS \"setTime\",

				ETS.EmergencyTeamStatus_Name AS \"nameStatus\",
				ET.EmergencyTeam_Num AS \"EmergencyTeam_Num\",
				ED.EmergencyData_CallNum AS \"callNum\"
			FROM
				v_EmergencyTeamStatusHistory ETSH
				left join v_EmergencyTeamStatus ETS  on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id

				left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = ETSH.EmergencyTeam_id

				left join EmergencyData ED  on ED.EmergencyData_BrigadeNum = ET.EmergencyTeam_Num

			WHERE
				ETSH.EmergencyTeam_id = :EmergencyTeam_id
		";
		$sqlArr = array(
			'EmergencyTeam_id' => $data['EmergencyTeam_id'],
		);
		$result = $this->db->query( $query, $sqlArr );
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	* Определение статуса карты старшим врачом
	*/
	public function setStatusCmpCallCardByHD($data){
		$region = getRegionNick();
		//сразу берем наш вызов
		//secondCard - тк карта CmpCallCard_id - не первичная (дубль, на спец бр, отменяющая и прочее)
		$secondCardQuery = "
			SELECT 
			    cmpcallcard_id as \"cmpcallcard_id\",
                cmpcallcard_numv as \"cmpcallcard_numv\",
                cmpcallcard_ngod as \"cmpcallcard_ngod\",
                cmpcallcard_prty as \"cmpcallcard_prty\",
                cmpcallcard_sect as \"cmpcallcard_sect\",
                cmparea_id as \"cmparea_id\",
                cmpcallcard_city as \"cmpcallcard_city\",
                cmpcallcard_ulic as \"cmpcallcard_ulic\",
                cmpcallcard_dom as \"cmpcallcard_dom\",
                cmpcallcard_kvar as \"cmpcallcard_kvar\",
                cmpcallcard_podz as \"cmpcallcard_podz\",
                cmpcallcard_etaj as \"cmpcallcard_etaj\",
                cmpcallcard_kodp as \"cmpcallcard_kodp\",
                cmpcallcard_telf as \"cmpcallcard_telf\",
                cmpplace_id as \"cmpplace_id\",
                cmpcallcard_comm as \"cmpcallcard_comm\",
                CCC.cmpreason_id as \"cmpreason_id\",
                person_id as \"person_id\",
                person_surname as \"person_surname\",
                person_firname as \"person_firname\",
                person_secname as \"person_secname\",
                person_age as \"person_age\",
                person_birthday as \"person_birthday\",
                person_polisser as \"person_polisser\",
                person_polisnum as \"person_polisnum\",
                sex_id as \"sex_id\",
                cmpcallcard_ktov as \"cmpcallcard_ktov\",
                CCC.cmpcalltype_id as \"cmpcalltype_id\",
                cmpprofile_cid as \"cmpprofile_cid\",
                cmpcallcard_smpt as \"cmpcallcard_smpt\",
                cmpcallcard_stan as \"cmpcallcard_stan\",
                cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
                cmpcallcard_line as \"cmpcallcard_line\",
                cmpresult_id as \"cmpresult_id\",
                cmparea_gid as \"cmparea_gid\",
                cmplpu_id as \"cmplpu_id\",
                cmpdiag_oid as \"cmpdiag_oid\",
                cmpdiag_aid as \"cmpdiag_aid\",
                cmptrauma_id as \"cmptrauma_id\",
                cmpcallcard_isalco as \"cmpcallcard_isalco\",
                diag_uid as \"diag_uid\",
                cmpcallcard_numb as \"cmpcallcard_numb\",
                cmpcallcard_smpb as \"cmpcallcard_smpb\",
                cmpcallcard_stbr as \"cmpcallcard_stbr\",
                cmpcallcard_stbb as \"cmpcallcard_stbb\",
                cmpprofile_bid as \"cmpprofile_bid\",
                cmpcallcard_ncar as \"cmpcallcard_ncar\",
                cmpcallcard_rcod as \"cmpcallcard_rcod\",
                cmpcallcard_tabn as \"cmpcallcard_tabn\",
                cmpcallcard_dokt as \"cmpcallcard_dokt\",
                cmpcallcard_tab2 as \"cmpcallcard_tab2\",
                cmpcallcard_tab3 as \"cmpcallcard_tab3\",
                cmpcallcard_tab4 as \"cmpcallcard_tab4\",
                diag_sid as \"diag_sid\",
                cmptalon_id as \"cmptalon_id\",
                cmpcallcard_expo as \"cmpcallcard_expo\",
                cmpcallcard_smpp as \"cmpcallcard_smpp\",
                cmpcallcard_vr51 as \"cmpcallcard_vr51\",
                cmpcallcard_d201 as \"cmpcallcard_d201\",
                cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
                cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
                cmpcallcard_dspp as \"cmpcallcard_dspp\",
                cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
                cmpcallcard_kakp as \"cmpcallcard_kakp\",
                cmpcallcard_tper as \"cmpcallcard_tper\",
                cmpcallcard_vyez as \"cmpcallcard_vyez\",
                cmpcallcard_przd as \"cmpcallcard_przd\",
                cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
                cmpcallcard_tsta as \"cmpcallcard_tsta\",
                cmpcallcard_tisp as \"cmpcallcard_tisp\",
                cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
                cmpcallcard_kilo as \"cmpcallcard_kilo\",
                cmpcallcard_dlit as \"cmpcallcard_dlit\",
                cmpcallcard_prdl as \"cmpcallcard_prdl\",
                cmparea_pid as \"cmparea_pid\",
                cmpcallcard_pcity as \"cmpcallcard_pcity\",
                cmpcallcard_pulic as \"cmpcallcard_pulic\",
                cmpcallcard_pdom as \"cmpcallcard_pdom\",
                cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
                cmplpu_aid as \"cmplpu_aid\",
                cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
                cmpcallcard_medc as \"cmpcallcard_medc\",
                cmpcallcard_izv1 as \"cmpcallcard_izv1\",
                cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
                cmpcallcard_inf1 as \"cmpcallcard_inf1\",
                cmpcallcard_inf2 as \"cmpcallcard_inf2\",
                cmpcallcard_inf3 as \"cmpcallcard_inf3\",
                cmpcallcard_inf4 as \"cmpcallcard_inf4\",
                cmpcallcard_inf5 as \"cmpcallcard_inf5\",
                cmpcallcard_inf6 as \"cmpcallcard_inf6\",
                pmuser_insid as \"pmuser_insid\",
                pmuser_updid as \"pmuser_updid\",
                cmpcallcard_insdt as \"cmpcallcard_insdt\",
                cmpcallcard_upddt as \"cmpcallcard_upddt\",
                klrgn_id as \"klrgn_id\",
                klsubrgn_id as \"klsubrgn_id\",
                klcity_id as \"klcity_id\",
                kltown_id as \"kltown_id\",
                klstreet_id as \"klstreet_id\",
                lpu_ppdid as \"lpu_ppdid\",
                cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
                cmpcallcard_isopen as \"cmpcallcard_isopen\",
                cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
                cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
                cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
                cmpppdresult_id as \"cmpppdresult_id\",
                emergencyteam_id as \"emergencyteam_id\",
                cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
                lpu_id as \"lpu_id\",
                cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
                medpersonal_id as \"medpersonal_id\",
                resultdeseasetype_id as \"resultdeseasetype_id\",
                cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
                unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
                cmpcallcard_korp as \"cmpcallcard_korp\",
                cmpcallcard_room as \"cmpcallcard_room\",
                cmpcallcard_difftime as \"cmpcallcard_difftime\",
                uslugacomplex_id as \"uslugacomplex_id\",
                lpubuilding_id as \"lpubuilding_id\",
                cmpcallertype_id as \"cmpcallertype_id\",
                cmpcallplacetype_id as \"cmpcallplacetype_id\",
                cmpcallcard_rid as \"cmpcallcard_rid\",
                cmpcallcard_urgency as \"cmpcallcard_urgency\",
                cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
                cmpsecondreason_id as \"cmpsecondreason_id\",
                cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
                cmpcallreasontype_id as \"cmpcallreasontype_id\",
                cmpreasonnew_id as \"cmpreasonnew_id\",
                cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
                cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
                cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
                cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
                medstafffact_id as \"medstafffact_id\",
                rankinscale_id as \"rankinscale_id\",
                rankinscale_sid as \"rankinscale_sid\",
                leavetype_id as \"leavetype_id\",
                cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
                lpusection_id as \"lpusection_id\",
                cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
                cmpcallcard_condition as \"cmpcallcard_condition\",
                lpu_cid as \"lpu_cid\",
                cmpcallcard_tend as \"cmpcallcard_tend\",
                cmpcallcard_callltd as \"cmpcallcard_callltd\",
                cmpcallcard_calllng as \"cmpcallcard_calllng\",
                cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
                cmprejectionreason_id as \"cmprejectionreason_id\",
                cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
                cmpcallcard_savedt as \"cmpcallcard_savedt\",
                cmpcallcard_plandt as \"cmpcallcard_plandt\",
                cmpcallcard_factdt as \"cmpcallcard_factdt\",
                cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
                cmpcallcard_isextra as \"cmpcallcard_isextra\",
                cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
                cmpcallcard_guid as \"cmpcallcard_guid\",
                cmpcallcard_rguid as \"cmpcallcard_rguid\",
                cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
                cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
                emergencyteam_guid as \"emergencyteam_guid\",
                cmpcallcard_stordt as \"cmpcallcard_stordt\",
                cmpcallcard_defcom as \"cmpcallcard_defcom\",
                medservice_id as \"medservice_id\",
                cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
                cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
                diag_sopid as \"diag_sopid\",
                cmpleavetype_id as \"cmpleavetype_id\",
                cmpleavetask_id as \"cmpleavetask_id\",
                cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
                cmptransporttype_id as \"cmptransporttype_id\",
                cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
                cmpcallcardresult_id as \"cmpcallcardresult_id\",
                person_isunknown as \"person_isunknown\",
                cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
                lpu_smpid as \"lpu_smpid\",
                lpu_hid as \"lpu_hid\",
                unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
                paytype_id as \"paytype_id\",
                cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
                cmpcallcard_sid as \"cmpcallcard_sid\",
                cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
                cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
                cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
                cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
                cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
                cmpcallsigntype_id as \"cmpcallsigntype_id\",
                lpu_codesmo as \"lpu_codesmo\",
                registry_sid as \"registry_sid\",
                diag_gid as \"diag_gid\",
                medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
                cmpcommonstate_id as \"cmpcommonstate_id \"
			FROM v_CmpCallCard as CCC  

			LEFT JOIN v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

			LEFT JOIN v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

			LEFT JOIN v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

			WHERE CCC.CmpCallCard_id = :CmpCallCard_id LIMIT 1";
			
		$secondCard = $this->db->query($secondCardQuery, $data)->row_array();
		
		//коды:
		
		//статус карты
		$secondCardStatusCode = $secondCard["CmpCallCardStatusType_Code"];
		//тип вызова
		$secondTypeCardCode = $secondCard["CmpCallType_Code"];
		//повод вызова
		$secondCardReasonCode = $secondCard["CmpReason_Code"];
		$secondCardReasonID = $secondCard["CmpReason_id"];
		//флаг признак ухудшения состояния
		$flagIsDeterior = false;

		//ид первичныого вызова
		$firstCardId = $secondCard["CmpCallCard_rid"];
		
		//Доп события для первичного и повторного вызова
		$eventForFirstCard = null;
		$eventForSecondCard = null;

		switch($data["callType"]){
			
			//вызовы с поводом «Решение старшего врача»
			case 'hdobserve':{
				//принять
				if($data["action"] == 'accept'){
					
					//в данном случае $secondCard - первичный, тк вторичного вызова у него нет
					
					// Если выбран Повод вызова «Консультация по телефону», то Тип вызова меняется на «Консультативный»;
					// Статус вызова меняется следующим образом:
					// Если Повод вызова «Консультация по телефону», то статус вызова меняется на «Закрыто»;
					// Иначе статус вызова меняется на «Передано».
					// Уфа: если тип вызова «Справка», «Консультация», «Аб. отключился» то статус вызова меняется на "Закрыто"
					$isUfa = $region == 'ufa';
					switch(true) {
						case in_array($secondCardReasonCode, array('91К')):
							$secondTypeCardCode = 6;
							$secondCardStatusCode = 6;
							break;

						case $isUfa && in_array($secondTypeCardCode, array('6','15','16')):
							$secondCardStatusCode = 6;
							break;

						default:
							$secondCardStatusCode = 1;
							break;
					}
					//Событие регистрируется в Истории вызова				
					// В поле «Событие» вносится значение «Передан для решения старшего врача»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение «Вызов принят»
					$eventForSecondCard = array(
						"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 3,
						"CmpCallCardEvent_Comment" => 'Вызов принят',
						"pmUser_id" => $data["pmUser_id"]
					);
					
				}
				break;
			}
			
			//дублирующий вызов
			case 'double': {
				
				//принять
				if($data["action"] == 'accept'){
					//Статус повторного вызова меняется с «Решение старшего врача» на Дубль.
					$secondCardStatusCode = 9;
					
					if(!empty($data['CmpCallCard_IsDeterior']) && (int)$data['CmpCallCard_IsDeterior'] == 2){
						// Если дублирующий вызов имеет признак «Ухудшение состояния»
						$flagIsDeterior = true;
					}

					//Статус первичного вызова не меняется. НО Событие регистрируется в Истории вызова
					// В поле «Событие» вносится значение «Дублирующее обращение, решение старшего врача СМП»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение «Согласовано»;

					$eventForFirstCard = array(
						"CmpCallCard_id" => $firstCardId,
						"CmpCallCardEventType_Code" => 16,
						"CmpCallCardEvent_Comment" => 'Согласовано',
						"pmUser_id" => $data["pmUser_id"]
					);
					
					//Событие secondCard регистрируется в Истории вызова					
					// В поле «Событие» вносится значение «Передан для решения старшего врача»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение «Разрешено»;

					$eventForSecondCard = array(
						"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 3,
						"CmpCallCardEvent_Comment" => 'Разрешено',
						"pmUser_id" => $data["pmUser_id"]
					);
				}
				
				//отмена
				if($data["action"] == 'discard'){
					// Тип вызова меняется с «Дублирующее» на «Повторное» 
					// (если в течение 24 часов на этот же адрес или на этого же пациента был завершен вызов (прим. т.е. в статусе "обслужено" или "Закрыто")) 
					// или «Первичное» (в иных случаях);
					
					//определяем был ли такой вызов
					$checkQuery = "
						SELECT CCCST.CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\"
						FROM v_CmpCallCard as CCC 

						LEFT JOIN v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

						WHERE 
							(
								(
									CCC.KLSubRgn_id = :KLSubRgn_id AND
									CCC.KLCity_id = :KLCity_id AND
									CCC.KLTown_id = :KLTown_id AND
									CCC.KLStreet_id = :KLStreet_id AND
									CCC.CmpCallCard_Dom = :CmpCallCard_Dom AND
									CCC.CmpCallCard_Korp = :CmpCallCard_Korp AND
									CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar
								)
								OR CCC.Person_id = :Person_id
							)
							AND CCC.CmpCallCard_id != :CmpCallCard_id
							AND DATEDIFF('hour',CAST(CCC.CmpCallCard_prmDT as timestamp(3)), dbo.tzGetDate())<=24
							AND CCCST.CmpCallCardStatusType_Code IN (4,6)
						";
					
					$checkQueryResult = $this->db->query($checkQuery, $secondCard)->result();
					
					//если вызов был то Тип вызова меняется с «Дублирующее» на «Повторное» 
					if(count($checkQueryResult) > 0){
						$secondTypeCardCode = 2;
					}
					else{
						//иначе на первичный
						$secondTypeCardCode = 1;
						
						//Если тип вызова определен как «Первичное», то удаляется связь данного вызова с первичным вызовом;
						$firstCardId = false;
					}
					
					//Статус повторного вызова меняется с «Решение старшего врача» на «Передано».
					$secondCardStatusCode = 1;
					
					//Статус первичного вызова не меняется. НО Событие регистрируется в Истории вызова
					// В поле «Событие» вносится значение «Дублирующее обращение, решение старшего врача СМП»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение «Не согласовано»;

					$eventForFirstCard = array(
						"CmpCallCard_id" => $firstCardId,
						"CmpCallCardEventType_Code" => 18,
						"CmpCallCardEvent_Comment" => 'Не согласовано',
						"pmUser_id" => $data["pmUser_id"]
					);
					
					//Событие secondCard регистрируется в Истории вызова					
					// В поле «Событие» вносится значение «Передан для решения старшего врача»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение «Отклонено»;

					$eventForSecondCard = array(
						"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 3,
						"CmpCallCardEvent_Comment" => 'Не согласовано',
						"pmUser_id" => $data["pmUser_id"]
					);
				}

				break;
			}
			//для спец бригады
			case 'specteam' : {
				
				//принять
				if($data["action"] == 'accept'){
					
					//Статус повторного вызова меняется с «Решение старшего врача» на «Передано».
					$secondCardStatusCode = 1;
					
					//Статус первичного вызова не меняется. НО Событие регистрируется в Истории вызова
					// В поле «Событие» вносится «Создание вызова спец. бригады, регистрация»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение «Разрешено»;

					$eventForFirstCard = array(
						"CmpCallCard_id" => $firstCardId,
						"CmpCallCardEventType_Code" => 18,
						"CmpCallCardEvent_Comment" => 'Разрешено',
						"pmUser_id" => $data["pmUser_id"]
					);
					
					//Событие secondCard регистрируется в Истории вызова					
					//В поле «Событие» вносится «Передан для решения старшего врача»;
					//В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					//В поле «Значение события» вносится значение «Разрешено»;
					$eventForSecondCard = array(
						"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 3,
						"CmpCallCardEvent_Comment" => 'Разрешено',
						"pmUser_id" => $data["pmUser_id"]
					);
				};
				
				//отменить
				if($data["action"] == 'discard'){
					
					//Статус повторного вызова меняется с «Решение старшего врача» на Закрыто.
					$secondCardStatusCode = 6;
					
					//Статус первичного вызова не меняется. НО Событие регистрируется в Истории вызова
					// В поле «Событие» вносится «Создание вызова спец. бригады, регистрация»;
					// В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					// В поле «Значение события» вносится значение Отклонено;

					$eventForFirstCard = array(
						"CmpCallCard_id" => $firstCardId,
						"CmpCallCardEventType_Code" => 18,
						"CmpCallCardEvent_Comment" => 'Отклонено',
						"pmUser_id" => $data["pmUser_id"]
					);
					
					//Событие secondCard регистрируется в Истории вызова					
					//В поле «Событие» вносится «Передан для решения старшего врача»;
					//В поле «ФИО» вносится ФИО старшего врача, согласовавшего вызов;
					//В поле «Значение события» вносится значение Отклонено;
					$eventForSecondCard = array(
						"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 3,
						"CmpCallCardEvent_Comment" => 'Отклонено',
						"pmUser_id" => $data["pmUser_id"]
					);
					
				}
				
				break;
			}

			//отмена вызова
			case 'cancel': {
				//принять
				if($data['action'] == 'accept'){

					//Событие согласование СВ только при Решении СВ
					if($secondCardStatusCode == 10){
						//Событие согласования для первичного вызова
						$eventParams = array(
							'CmpCallCard_id' => $firstCardId,
							'CmpCallCardEventType_Code' => 22,
							'CmpCallCardEvent_Comment' => 'Cогласовано',
							'pmUser_id' => $data['pmUser_id']
						);
						$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
						$this->CmpCallCard_model->setCmpCallCardEvent( $eventParams );
					}

					$params = array(
						'CmpCallType_Code' => 17, //отменен
						'ARMType' => 'smpheaddoctor',
						'CmpCallCard_rid' => $firstCardId,
						'CmpCallCard_id' => $data['CmpCallCard_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					return $this->checkCallStatusOnSave($params, false);
				}
				//отменить
				if($data["action"] == 'discard'){
					//Тип вызова изменяется на "Дублирующее"
					$secondTypeCardCode = 14;

					//Событие согласование СВ только при Решении СВ
					if($secondCardStatusCode == 10) {
						$eventForFirstCard = array(
							"CmpCallCard_id" => $firstCardId,
							"CmpCallCardEventType_Code" => 22,
							"CmpCallCardEvent_Comment" => 'Не согласовано',
							"pmUser_id" => $data["pmUser_id"]
						);
					}
					//Статус отменяющего вызова меняется с «Решение старшего врача» на «Дубль»;
					$secondCardStatusCode = 9;

					$eventForSecondCard = array(
						"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
						"CmpCallCardEventType_Code" => 16,
						"CmpCallCardEvent_Comment" => 'Не согласовано',
						"pmUser_id" => $data["pmUser_id"]
					);

				}
				break;
			}
			default: {break;}
		};
		
		//перед установкой статусов регистрируем события
		$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
		
		//событие для первичного вызова
		if( !empty($eventForFirstCard) && !empty($firstCardId) ){
			$this->CmpCallCard_model->setCmpCallCardEvent( $eventForFirstCard );

			if($flagIsDeterior){
				//Если дублирующий вызов имеет признак «Ухудшение состояния»
				$queryParams = array(
					'CmpCallCard_id' => $firstCardId,
					'CmpReason_id' => $secondCardReasonID,
					'CmpCallCard_IsExtra' => $secondCard["CmpCallCard_IsExtra"],
					'CmpCallPlaceType_id' => $secondCard['CmpCallPlaceType_id'],
					'CmpCallCard_IsPassSSMP' => $secondCard['CmpCallCard_IsPassSSMP'],
					'Lpu_smpid' => $secondCard['Lpu_smpid'],
					'LpuBuilding_id' => $secondCard['LpuBuilding_id'],
					'pmUser_id' => $data["pmUser_id"]
				);
				$this->updateReasonAndUrgencyInCmpCallCard($queryParams);
			}
		}
		
		//событие для повторного вызова
		//if(!empty($eventForSecondCard)){
		//	$this->CmpCallCard_model->setCmpCallCardEvent( $eventForSecondCard );
		//}
		
		
		//устанавливаем статус secondCard		
		$result = $this->setStatusCmpCallCard(array(
			"CmpCallCard_id" => $secondCard["CmpCallCard_id"],
			"CmpCallCard_rid" => $firstCardId,
			"CmpCallCardStatusType_Code" => $secondCardStatusCode,
			"CmpCallCardStatus_Comment" => $eventForSecondCard["CmpCallCardEvent_Comment"],
			"CmpCallType_Code" => $secondTypeCardCode,
			"CmpReason_id" => $secondCard['CmpReason_id'],
			"pmUser_id" => $data["pmUser_id"]
		));
		
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
		
	}

	/**
	 * Получение службы НМП для вызова по адресу с учетом даты/времени вызова
	 */
	function getNmpMedService($data) {
		$postNmpJoin = '';
		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		$SmpUnitParam = $this -> getOperDepartamentOptions($data);

		if (!$SmpUnitParam && !in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp'))) {
			return $this->createError('','Ошибка при получении настроек для подразделения СМП');
		} else {
			$SmpUnitParam['SmpUnitParam_IsAutoBuilding'] = 2;
		}

		$response = array(
			'success' => true,
			'MedService_id' => null,
			'Lpu_id' => null,
			'Alert_Msg' => ''
		);

		if (empty($SmpUnitParam['SmpUnitParam_IsAutoBuilding']) || $SmpUnitParam['SmpUnitParam_IsAutoBuilding'] == 1) {
			return $response;
		}

		$filter = array();
		//		if ( in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp')) ){
		//			$filter[] = "LB.LpuBuildingType_id = 28";
		//			$postNmpJoin = "left join v_LpuBuilding LB  on MS.LpuBuilding_id = LB.LpuBuilding_id";

		//		}

		$days = array('Mo','Tu','We','Th','Fr','Sa','Su');
		$day_num = date_create($data['CmpCallCard_prmDate'] . ' ' . $data['CmpCallCard_prmTime'])->format('w');
		$day_num = ($day_num == 0)?7:$day_num;
		$day = $days[$day_num - 1];
		$LSACode = $data['Person_Age'] < 18 ? 2 : 1;
		$filter[] = "LSA.LpuSectionAge_Code in (3,{$LSACode})";
		$data['houseNum'] = $data["CmpCallCard_Dom"] . (!empty($data['CmpCallCard_Korp']) ? '/' . $data['CmpCallCard_Korp'] : '');

		if(!empty($data['KLArea_id'])){
			$filter[] = " t.KLArea_id = :KLArea_id";
		}

		//Поиск службы НМП, обслужывающей переданный адрес
		$query = "
			select
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Name as \"MedService_Name\",
				lpu.Lpu_Nick as \"Lpu_Nick\",
				MS.Lpu_id as \"Lpu_id\"
			from
				v_MedServiceKLHouseCoordsRel MSHC 

				inner join v_MedService MS  on MS.MedService_id = MSHC.MedService_id

				inner join v_MedServiceType MST  on MST.MedServiceType_id = MS.MedServiceType_id

				left join v_KLHouseCoords HC  on HC.KLHouseCoords_id = MSHC.KLHouseCoords_id

				left join v_KLHouse H  on H.KLHouse_id = HC.KLHouse_id

				left join v_LpuSectionAge LSA  on LSA.LpuSectionAge_id = MS.LpuSectionAge_id

				left join KLArea t  on t.KLArea_id = HC.KLArea_id

				left join v_Lpu lpu  on lpu.Lpu_id = MS.Lpu_id

				{$postNmpJoin}
			where

				--логика следующая: если в настройках службы указана улица - ищем только по ней, иначе - по нас пункту
				(
					(
						HC.KLStreet_id is not null
						and HC.KLStreet_id = :KLStreet_id
						and
						(
							dbo.GetHouse(H.KLHouse_Name, :houseNum) = 1
							OR
							MSHC.MedServiceStreet_isAll = 2
						)
					) OR (
						HC.KLStreet_id is null
						and	MSHC.MedServiceStreet_isAll = 2
					)
				)
				and MST.MedServiceType_SysNick ilike 'slneotl'
				and (MS.MedService_endDT is null or MS.MedService_endDT > dbo.tzGetDate())
				and ".implode(" AND ", $filter)."
			order by MS.LpuSectionAge_id asc
			limit 1
		";
		$params = array(
			'KLStreet_id' => $data['KLStreet_id'],
			'houseNum' => $data['houseNum'],
			'CmpCallCard_prmDT' => date_create($data['CmpCallCard_prmDate'] . ' ' . $data['CmpCallCard_prmTime']),
			'KLArea_id' => !empty($data['KLArea_id']) ? $data['KLArea_id'] : null
		);
		//var_dump(getDebugSQL($query, $params)); exit;
		$MedService = $this->getFirstRowFromQuery($query, $params, true);

		if ($MedService === false) {
			return $this->createError('','Ошибка при поиске службы НМП');
		}
		if (empty($MedService)) {
			$response['Alert_Msg'] = "Служба НМП, обслуживающая территорию места вызова, не найдена. Поэтому неотложный вызов направлен в подразделение СМП";
			return array($response);
		}

		$data['MedService_id'] = $MedService['MedService_id'];
		$this->load->model('LpuStructure_model', 'LpuStructure');
		$nmpParams = $this->LpuStructure->getNmpParams($data);

		if (empty($nmpParams[0]["LpuHMPWorkTime_{$day}From"]) || empty($nmpParams[0]["LpuHMPWorkTime_{$day}To"])) {
			$response['Alert_Msg'] = "Время работы службы НМП не задано. Поэтому неотложный вызов направлен в подразделение СМП";
			return array($response);
		}

		$begWorkTime = date_create($data['CmpCallCard_prmDate'] . ' ' . $nmpParams[0]["LpuHMPWorkTime_{$day}From"]);
		$endWorkTime = date_create($data['CmpCallCard_prmDate'] . ' ' . $nmpParams[0]["LpuHMPWorkTime_{$day}To"]);
		$prmDT = date_create($data['CmpCallCard_prmDate'] . ' ' . $data['CmpCallCard_prmTime']);

		if ($prmDT < $begWorkTime || $prmDT > $endWorkTime) {
			$response['Alert_Msg'] = "Время работы службы НМП закончилось. Поэтому неотложный вызов направлен в подразделение СМП";
			return array($response);
		}

		$response['MedService_id'] = $MedService['MedService_id'];
		$response['Lpu_id'] = !empty($MedService['Lpu_id'])?$MedService['Lpu_id']:null;
		$response['Lpu_Nick'] = !empty($MedService['Lpu_Nick'])?$MedService['Lpu_Nick']:null;
		$response['MedService_Name'] = !empty($MedService['MedService_Name'])?$MedService['MedService_Name']:null;

		return array($response);
	}

	/**
	* Копирование полей с 1 карты на несколько
	*/
	function copyParamsCmpCallCard($data){

		//выбираем донора
		$query = "
			SELECT 
			    cmpcallcard_id as \"cmpcallcard_id\",
                cmpcallcard_numv as \"cmpcallcard_numv\",
                cmpcallcard_ngod as \"cmpcallcard_ngod\",
                cmpcallcard_prty as \"cmpcallcard_prty\",
                cmpcallcard_sect as \"cmpcallcard_sect\",
                cmparea_id as \"cmparea_id\",
                cmpcallcard_city as \"cmpcallcard_city\",
                cmpcallcard_ulic as \"cmpcallcard_ulic\",
                cmpcallcard_dom as \"cmpcallcard_dom\",
                cmpcallcard_kvar as \"cmpcallcard_kvar\",
                cmpcallcard_podz as \"cmpcallcard_podz\",
                cmpcallcard_etaj as \"cmpcallcard_etaj\",
                cmpcallcard_kodp as \"cmpcallcard_kodp\",
                cmpcallcard_telf as \"cmpcallcard_telf\",
                cmpplace_id as \"cmpplace_id\",
                cmpcallcard_comm as \"cmpcallcard_comm\",
                cmpreason_id as \"cmpreason_id\",
                person_id as \"person_id\",
                person_surname as \"person_surname\",
                person_firname as \"person_firname\",
                person_secname as \"person_secname\",
                person_age as \"person_age\",
                person_birthday as \"person_birthday\",
                person_polisser as \"person_polisser\",
                person_polisnum as \"person_polisnum\",
                sex_id as \"sex_id\",
                cmpcallcard_ktov as \"cmpcallcard_ktov\",
                cmpcalltype_id as \"cmpcalltype_id\",
                cmpprofile_cid as \"cmpprofile_cid\",
                cmpcallcard_smpt as \"cmpcallcard_smpt\",
                cmpcallcard_stan as \"cmpcallcard_stan\",
                cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
                cmpcallcard_line as \"cmpcallcard_line\",
                cmpresult_id as \"cmpresult_id\",
                cmparea_gid as \"cmparea_gid\",
                cmplpu_id as \"cmplpu_id\",
                cmpdiag_oid as \"cmpdiag_oid\",
                cmpdiag_aid as \"cmpdiag_aid\",
                cmptrauma_id as \"cmptrauma_id\",
                cmpcallcard_isalco as \"cmpcallcard_isalco\",
                diag_uid as \"diag_uid\",
                cmpcallcard_numb as \"cmpcallcard_numb\",
                cmpcallcard_smpb as \"cmpcallcard_smpb\",
                cmpcallcard_stbr as \"cmpcallcard_stbr\",
                cmpcallcard_stbb as \"cmpcallcard_stbb\",
                cmpprofile_bid as \"cmpprofile_bid\",
                cmpcallcard_ncar as \"cmpcallcard_ncar\",
                cmpcallcard_rcod as \"cmpcallcard_rcod\",
                cmpcallcard_tabn as \"cmpcallcard_tabn\",
                cmpcallcard_dokt as \"cmpcallcard_dokt\",
                cmpcallcard_tab2 as \"cmpcallcard_tab2\",
                cmpcallcard_tab3 as \"cmpcallcard_tab3\",
                cmpcallcard_tab4 as \"cmpcallcard_tab4\",
                diag_sid as \"diag_sid\",
                cmptalon_id as \"cmptalon_id\",
                cmpcallcard_expo as \"cmpcallcard_expo\",
                cmpcallcard_smpp as \"cmpcallcard_smpp\",
                cmpcallcard_vr51 as \"cmpcallcard_vr51\",
                cmpcallcard_d201 as \"cmpcallcard_d201\",
                cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
                cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
                cmpcallcard_dspp as \"cmpcallcard_dspp\",
                cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
                cmpcallcard_kakp as \"cmpcallcard_kakp\",
                cmpcallcard_tper as \"cmpcallcard_tper\",
                cmpcallcard_vyez as \"cmpcallcard_vyez\",
                cmpcallcard_przd as \"cmpcallcard_przd\",
                cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
                cmpcallcard_tsta as \"cmpcallcard_tsta\",
                cmpcallcard_tisp as \"cmpcallcard_tisp\",
                cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
                cmpcallcard_kilo as \"cmpcallcard_kilo\",
                cmpcallcard_dlit as \"cmpcallcard_dlit\",
                cmpcallcard_prdl as \"cmpcallcard_prdl\",
                cmparea_pid as \"cmparea_pid\",
                cmpcallcard_pcity as \"cmpcallcard_pcity\",
                cmpcallcard_pulic as \"cmpcallcard_pulic\",
                cmpcallcard_pdom as \"cmpcallcard_pdom\",
                cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
                cmplpu_aid as \"cmplpu_aid\",
                cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
                cmpcallcard_medc as \"cmpcallcard_medc\",
                cmpcallcard_izv1 as \"cmpcallcard_izv1\",
                cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
                cmpcallcard_inf1 as \"cmpcallcard_inf1\",
                cmpcallcard_inf2 as \"cmpcallcard_inf2\",
                cmpcallcard_inf3 as \"cmpcallcard_inf3\",
                cmpcallcard_inf4 as \"cmpcallcard_inf4\",
                cmpcallcard_inf5 as \"cmpcallcard_inf5\",
                cmpcallcard_inf6 as \"cmpcallcard_inf6\",
                CCC.pmuser_insid as \"pmuser_insid\",
                pmuser_updid as \"pmuser_updid\",
                cmpcallcard_insdt as \"cmpcallcard_insdt\",
                cmpcallcard_upddt as \"cmpcallcard_upddt\",
                klrgn_id as \"klrgn_id\",
                klsubrgn_id as \"klsubrgn_id\",
                klcity_id as \"klcity_id\",
                kltown_id as \"kltown_id\",
                klstreet_id as \"klstreet_id\",
                lpu_ppdid as \"lpu_ppdid\",
                cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
                cmpcallcard_isopen as \"cmpcallcard_isopen\",
                cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
                cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
                cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
                cmpppdresult_id as \"cmpppdresult_id\",
                emergencyteam_id as \"emergencyteam_id\",
                cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
                lpu_id as \"lpu_id\",
                cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
                medpersonal_id as \"medpersonal_id\",
                resultdeseasetype_id as \"resultdeseasetype_id\",
                cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
                unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
                cmpcallcard_korp as \"cmpcallcard_korp\",
                cmpcallcard_room as \"cmpcallcard_room\",
                cmpcallcard_difftime as \"cmpcallcard_difftime\",
                uslugacomplex_id as \"uslugacomplex_id\",
                lpubuilding_id as \"lpubuilding_id\",
                cmpcallertype_id as \"cmpcallertype_id\",
                cmpcallplacetype_id as \"cmpcallplacetype_id\",
                cmpcallcard_rid as \"cmpcallcard_rid\",
                cmpcallcard_urgency as \"cmpcallcard_urgency\",
                cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
                cmpsecondreason_id as \"cmpsecondreason_id\",
                cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
                cmpcallreasontype_id as \"cmpcallreasontype_id\",
                cmpreasonnew_id as \"cmpreasonnew_id\",
                cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
                cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
                cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
                cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
                medstafffact_id as \"medstafffact_id\",
                rankinscale_id as \"rankinscale_id\",
                rankinscale_sid as \"rankinscale_sid\",
                leavetype_id as \"leavetype_id\",
                cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
                lpusection_id as \"lpusection_id\",
                cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
                cmpcallcard_condition as \"cmpcallcard_condition\",
                lpu_cid as \"lpu_cid\",
                cmpcallcard_tend as \"cmpcallcard_tend\",
                cmpcallcard_callltd as \"cmpcallcard_callltd\",
                cmpcallcard_calllng as \"cmpcallcard_calllng\",
                cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
                cmprejectionreason_id as \"cmprejectionreason_id\",
                cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
                cmpcallcard_savedt as \"cmpcallcard_savedt\",
                cmpcallcard_plandt as \"cmpcallcard_plandt\",
                cmpcallcard_factdt as \"cmpcallcard_factdt\",
                cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
                cmpcallcard_isextra as \"cmpcallcard_isextra\",
                cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
                cmpcallcard_guid as \"cmpcallcard_guid\",
                cmpcallcard_rguid as \"cmpcallcard_rguid\",
                cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
                cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
                emergencyteam_guid as \"emergencyteam_guid\",
                cmpcallcard_stordt as \"cmpcallcard_stordt\",
                cmpcallcard_defcom as \"cmpcallcard_defcom\",
                medservice_id as \"medservice_id\",
                cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
                cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
                diag_sopid as \"diag_sopid\",
                cmpleavetype_id as \"cmpleavetype_id\",
                cmpleavetask_id as \"cmpleavetask_id\",
                cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
                cmptransporttype_id as \"cmptransporttype_id\",
                cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
                cmpcallcardresult_id as \"cmpcallcardresult_id\",
                person_isunknown as \"person_isunknown\",
                cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
                lpu_smpid as \"lpu_smpid\",
                lpu_hid as \"lpu_hid\",
                unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
                paytype_id as \"paytype_id\",
                cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
                cmpcallcard_sid as \"cmpcallcard_sid\",
                cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
                cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
                cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
                cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
                cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
                cmpcallsigntype_id as \"cmpcallsigntype_id\",
                lpu_codesmo as \"lpu_codesmo\",
                registry_sid as \"registry_sid\",
                diag_gid as \"diag_gid\",
                medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
                cmpcommonstate_id as \"cmpcommonstate_id \"
			FROM v_CmpCallCard CCC 

			WHERE CCC.CmpCallCard_id = :CmpCallCard_id;
		";

		$result = $this->db->query( $query, array(
			'CmpCallCard_id' => $data[ 'donorCard' ]
		) );

		if ( !is_object( $result ) ) {return false;}
		$result = $result->result( 'array' );

		$result = $result[ 0 ];

		$procedure = "p_CmpCallCard_setCardUpd";

		//список параметров, которые обяательно надо забрать с донора
		$listParams = array_keys($result);
		//список карт-реципиентов
		$recipientCards = json_decode($data['recipientCards'], true);

		$params = array();
		$params['Lpu_id'] = $data['Lpu_id'];

		$exceptedFields = array(
			'CmpCallCard_id',
			'CmpCallCard_Numv',
			'CmpCallCard_Ngod',
			'Person_SurName',
			'Person_FirName',
			'Person_SecName',
			'Person_Age',
			'Person_IsUnknown',
			'Sex_id',
			'Lpu_id',
			'Person_id'
		);

		//собираем измененные параметры
		foreach ($listParams as $fieldVal)
		{
			if(!(in_array($fieldVal, $exceptedFields))) $params[$fieldVal] = $result[$fieldVal];
		}

		//запускаем изменения для карт-реципиентов
		foreach ($recipientCards as $recipientCard)
		{
			//выбираем реципиента
			$queryRC = "
				SELECT 
				    cmpcallcard_id as \"cmpcallcard_id\",
                    cmpcallcard_numv as \"cmpcallcard_numv\",
                    cmpcallcard_ngod as \"cmpcallcard_ngod\",
                    cmpcallcard_prty as \"cmpcallcard_prty\",
                    cmpcallcard_sect as \"cmpcallcard_sect\",
                    cmparea_id as \"cmparea_id\",
                    cmpcallcard_city as \"cmpcallcard_city\",
                    cmpcallcard_ulic as \"cmpcallcard_ulic\",
                    cmpcallcard_dom as \"cmpcallcard_dom\",
                    cmpcallcard_kvar as \"cmpcallcard_kvar\",
                    cmpcallcard_podz as \"cmpcallcard_podz\",
                    cmpcallcard_etaj as \"cmpcallcard_etaj\",
                    cmpcallcard_kodp as \"cmpcallcard_kodp\",
                    cmpcallcard_telf as \"cmpcallcard_telf\",
                    cmpplace_id as \"cmpplace_id\",
                    cmpcallcard_comm as \"cmpcallcard_comm\",
                    cmpreason_id as \"cmpreason_id\",
                    person_id as \"person_id\",
                    person_surname as \"person_surname\",
                    person_firname as \"person_firname\",
                    person_secname as \"person_secname\",
                    person_age as \"person_age\",
                    person_birthday as \"person_birthday\",
                    person_polisser as \"person_polisser\",
                    person_polisnum as \"person_polisnum\",
                    sex_id as \"sex_id\",
                    cmpcallcard_ktov as \"cmpcallcard_ktov\",
                    cmpcalltype_id as \"cmpcalltype_id\",
                    cmpprofile_cid as \"cmpprofile_cid\",
                    cmpcallcard_smpt as \"cmpcallcard_smpt\",
                    cmpcallcard_stan as \"cmpcallcard_stan\",
                    cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
                    cmpcallcard_line as \"cmpcallcard_line\",
                    cmpresult_id as \"cmpresult_id\",
                    cmparea_gid as \"cmparea_gid\",
                    cmplpu_id as \"cmplpu_id\",
                    cmpdiag_oid as \"cmpdiag_oid\",
                    cmpdiag_aid as \"cmpdiag_aid\",
                    cmptrauma_id as \"cmptrauma_id\",
                    cmpcallcard_isalco as \"cmpcallcard_isalco\",
                    diag_uid as \"diag_uid\",
                    cmpcallcard_numb as \"cmpcallcard_numb\",
                    cmpcallcard_smpb as \"cmpcallcard_smpb\",
                    cmpcallcard_stbr as \"cmpcallcard_stbr\",
                    cmpcallcard_stbb as \"cmpcallcard_stbb\",
                    cmpprofile_bid as \"cmpprofile_bid\",
                    cmpcallcard_ncar as \"cmpcallcard_ncar\",
                    cmpcallcard_rcod as \"cmpcallcard_rcod\",
                    cmpcallcard_tabn as \"cmpcallcard_tabn\",
                    cmpcallcard_dokt as \"cmpcallcard_dokt\",
                    cmpcallcard_tab2 as \"cmpcallcard_tab2\",
                    cmpcallcard_tab3 as \"cmpcallcard_tab3\",
                    cmpcallcard_tab4 as \"cmpcallcard_tab4\",
                    diag_sid as \"diag_sid\",
                    cmptalon_id as \"cmptalon_id\",
                    cmpcallcard_expo as \"cmpcallcard_expo\",
                    cmpcallcard_smpp as \"cmpcallcard_smpp\",
                    cmpcallcard_vr51 as \"cmpcallcard_vr51\",
                    cmpcallcard_d201 as \"cmpcallcard_d201\",
                    cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
                    cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
                    cmpcallcard_dspp as \"cmpcallcard_dspp\",
                    cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
                    cmpcallcard_kakp as \"cmpcallcard_kakp\",
                    cmpcallcard_tper as \"cmpcallcard_tper\",
                    cmpcallcard_vyez as \"cmpcallcard_vyez\",
                    cmpcallcard_przd as \"cmpcallcard_przd\",
                    cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
                    cmpcallcard_tsta as \"cmpcallcard_tsta\",
                    cmpcallcard_tisp as \"cmpcallcard_tisp\",
                    cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
                    cmpcallcard_kilo as \"cmpcallcard_kilo\",
                    cmpcallcard_dlit as \"cmpcallcard_dlit\",
                    cmpcallcard_prdl as \"cmpcallcard_prdl\",
                    cmparea_pid as \"cmparea_pid\",
                    cmpcallcard_pcity as \"cmpcallcard_pcity\",
                    cmpcallcard_pulic as \"cmpcallcard_pulic\",
                    cmpcallcard_pdom as \"cmpcallcard_pdom\",
                    cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
                    cmplpu_aid as \"cmplpu_aid\",
                    cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
                    cmpcallcard_medc as \"cmpcallcard_medc\",
                    cmpcallcard_izv1 as \"cmpcallcard_izv1\",
                    cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
                    cmpcallcard_inf1 as \"cmpcallcard_inf1\",
                    cmpcallcard_inf2 as \"cmpcallcard_inf2\",
                    cmpcallcard_inf3 as \"cmpcallcard_inf3\",
                    cmpcallcard_inf4 as \"cmpcallcard_inf4\",
                    cmpcallcard_inf5 as \"cmpcallcard_inf5\",
                    cmpcallcard_inf6 as \"cmpcallcard_inf6\",
                    CCC.pmuser_insid as \"pmuser_insid\",
                    pmuser_updid as \"pmuser_updid\",
                    cmpcallcard_insdt as \"cmpcallcard_insdt\",
                    cmpcallcard_upddt as \"cmpcallcard_upddt\",
                    klrgn_id as \"klrgn_id\",
                    klsubrgn_id as \"klsubrgn_id\",
                    klcity_id as \"klcity_id\",
                    kltown_id as \"kltown_id\",
                    klstreet_id as \"klstreet_id\",
                    lpu_ppdid as \"lpu_ppdid\",
                    cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
                    cmpcallcard_isopen as \"cmpcallcard_isopen\",
                    cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
                    cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
                    cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
                    cmpppdresult_id as \"cmpppdresult_id\",
                    emergencyteam_id as \"emergencyteam_id\",
                    cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
                    lpu_id as \"lpu_id\",
                    cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
                    medpersonal_id as \"medpersonal_id\",
                    resultdeseasetype_id as \"resultdeseasetype_id\",
                    cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
                    unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                    cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
                    cmpcallcard_korp as \"cmpcallcard_korp\",
                    cmpcallcard_room as \"cmpcallcard_room\",
                    cmpcallcard_difftime as \"cmpcallcard_difftime\",
                    uslugacomplex_id as \"uslugacomplex_id\",
                    lpubuilding_id as \"lpubuilding_id\",
                    cmpcallertype_id as \"cmpcallertype_id\",
                    cmpcallplacetype_id as \"cmpcallplacetype_id\",
                    cmpcallcard_rid as \"cmpcallcard_rid\",
                    cmpcallcard_urgency as \"cmpcallcard_urgency\",
                    cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
                    cmpsecondreason_id as \"cmpsecondreason_id\",
                    cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
                    cmpcallreasontype_id as \"cmpcallreasontype_id\",
                    cmpreasonnew_id as \"cmpreasonnew_id\",
                    cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
                    cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
                    cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
                    cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
                    medstafffact_id as \"medstafffact_id\",
                    rankinscale_id as \"rankinscale_id\",
                    rankinscale_sid as \"rankinscale_sid\",
                    leavetype_id as \"leavetype_id\",
                    cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
                    lpusection_id as \"lpusection_id\",
                    cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
                    cmpcallcard_condition as \"cmpcallcard_condition\",
                    lpu_cid as \"lpu_cid\",
                    cmpcallcard_tend as \"cmpcallcard_tend\",
                    cmpcallcard_callltd as \"cmpcallcard_callltd\",
                    cmpcallcard_calllng as \"cmpcallcard_calllng\",
                    cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
                    cmprejectionreason_id as \"cmprejectionreason_id\",
                    cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
                    cmpcallcard_savedt as \"cmpcallcard_savedt\",
                    cmpcallcard_plandt as \"cmpcallcard_plandt\",
                    cmpcallcard_factdt as \"cmpcallcard_factdt\",
                    cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
                    cmpcallcard_isextra as \"cmpcallcard_isextra\",
                    cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
                    cmpcallcard_guid as \"cmpcallcard_guid\",
                    cmpcallcard_rguid as \"cmpcallcard_rguid\",
                    cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
                    cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
                    emergencyteam_guid as \"emergencyteam_guid\",
                    cmpcallcard_stordt as \"cmpcallcard_stordt\",
                    cmpcallcard_defcom as \"cmpcallcard_defcom\",
                    medservice_id as \"medservice_id\",
                    cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
                    cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
                    diag_sopid as \"diag_sopid\",
                    cmpleavetype_id as \"cmpleavetype_id\",
                    cmpleavetask_id as \"cmpleavetask_id\",
                    cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
                    cmptransporttype_id as \"cmptransporttype_id\",
                    cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
                    cmpcallcardresult_id as \"cmpcallcardresult_id\",
                    person_isunknown as \"person_isunknown\",
                    cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
                    lpu_smpid as \"lpu_smpid\",
                    lpu_hid as \"lpu_hid\",
                    unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
                    paytype_id as \"paytype_id\",
                    cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
                    cmpcallcard_sid as \"cmpcallcard_sid\",
                    cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
                    cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
                    cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
                    cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
                    cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
                    cmpcallsigntype_id as \"cmpcallsigntype_id\",
                    lpu_codesmo as \"lpu_codesmo\",
                    registry_sid as \"registry_sid\",
                    diag_gid as \"diag_gid\",
                    medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
                    cmpcommonstate_id as \"cmpcommonstate_id \"
				FROM v_CmpCallCard CCC 

				WHERE CCC.CmpCallCard_id = :CmpCallCard_id;
			";

			$resultRC = $this->db->query( $queryRC, array(
				'CmpCallCard_id' => $recipientCard
			) );

			if ( !is_object( $resultRC ) ) {return false;}
			$resultRC = $resultRC->result( 'array' );

			$resultRC = $resultRC[ 0 ];
            //Расчет новых номеров день\год
			$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
			$nums = $this->CmpCallCard_model->getCmpCallCardNumber(array('Lpu_id' => $params['Lpu_id'], 'CmpCallCard_prmDT' => $params['CmpCallCard_prmDT']->format('Y-m-d H:i:s')));
			$params['CmpCallCard_Numv'] = $nums[0]["CmpCallCard_Numv"];
			$params['CmpCallCard_Ngod'] = $nums[0]["CmpCallCard_Ngod"];

			//сливаем данные в 1 массив, тк при передаче только измененных данных, остальные затираются
			$mergedData = array_merge($resultRC, $params);

			//получаем параметры запроса
			$genQuery = $this -> getParamsForSQLQuery($procedure, $mergedData, array('CmpCallCard_id') );
			$genQueryParams = $genQuery["paramsArray"];
			$genQueryParams['pmUser_id'] = $data['pmUser_id'];

			$genQuerySQL = $genQuery["sqlParams"];

			$query = "
				select 
				    Res as \"CmpCallCard_id\", 
				    Error_Code as \"Error_Code\", 
				    Error_Message as \"Error_Msg\"
				from ".$procedure." (
					CmpCallCard_id := :CmpCallCard_id,
					$genQuerySQL
					pmUser_id := :pmUser_id
					)
			";
			//$genQueryParams['CmpCallCard_id'] = $recipientCard;

			$result = $this->db->query( $query, $genQueryParams );
			//Смена статуса для регистрации события
			$this->checkCallStatusOnSave(array(
				'CmpCallCard_id' => $genQueryParams['CmpCallCard_id'],
				'Lpu_id' => $genQueryParams['Lpu_id'],
				'CmpReason_id' => $genQueryParams['CmpReason_id'],
				'Person_Age' => !empty($genQueryParams['Person_Age']) ? $genQueryParams['Person_Age'] : 0,
				'CmpCallPlaceType_id' => $genQueryParams['CmpCallPlaceType_id'],
				'pmUser_id' => $genQueryParams['pmUser_id'],
				'ARMType' => 'smpdispatchstation',
				)
			);
		}
	}

	/**
	 * Обновление полей cmpcallcard
	 */
	function changeCmpCallCardCommonParams($data){
		$this->swUpdate('CmpCallCard', $data, false);
	}

	/**
	 * Обновление полей cmpcallcard у отложенного вызова
	 */
	function setDefferedCmpCallCardParams($data){

		if (empty($data['CmpCallCard_id']))
			return false;

		$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		$params['CmpCallCard_storDT'] = (!empty($data['CmpCallCard_storDate']) && !empty($data['CmpCallCard_storTime'])) ?
			$data['CmpCallCard_storDate'] . ' ' . $data['CmpCallCard_storTime'] . '.000' : null;
		$params['CmpCallCard_defCom'] = !empty($data['CmpCallCard_defCom']) ? $data['CmpCallCard_defCom'] : null;
		$params['CmpCallCard_Numv'] = null;
		$params['CmpCallCard_Ngod'] = null;

		$params['pmUser_id'] = $data['pmUser_id'];

		return $this->swUpdate('CmpCallCard', $params);
	}
	
	/**
	 * Обновление полей cmpcallcard у отложенного вызова
	 */
	function setLpuHospitalized($data){
		$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		$params['Lpu_hid'] = $data['Lpu_hid'];
		$params['pmUser_id'] = $data['pmUser_id'];
		$params['Diag_gid'] = !empty($data['Diag_id']) ? $data['Diag_id'] : null;
		$params['cmpcommonstate_id'] = !empty($data['cmpcommonstate_id']) ? $data['cmpcommonstate_id'] : null;
		$result = $this->swUpdate('CmpCallCard', $params);
		return $result;
	}

	/**
	 * Сохранение шкалы LAMS
	 */
	function saveScaleLams($data) {
		$params = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'FaceAsymetry_id' => $data['FaceAsymetry_id'],
			'HandHold_id' => $data['HandHold_id'],
			'SqueezingBrush_id' => $data['SqueezingBrush_id'],
			'ScaleLams_Value' => $data['ScaleLams_Value']
		);
		return $this->saveObjectWithCheckForUniqueness('ScaleLams',$params,'CmpCallCard_id');
	}

	/**
	 * Сохранение шкалы оценки тяжести
	 */
	function savePrehospTraumaScale($data) {
		$params = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'PainResponse_id' => $data['PainResponse_id'],
			'ExternalRespirationType_id' => $data['ExternalRespirationType_id'],
			'SystolicBloodPressure_id' => $data['SystolicBloodPressure_id'],
			'InternalBleedingSigns_id' => $data['InternalBleedingSigns_id'],
			'LimbsSeparation_id' => $data['LimbsSeparation_id'],
			'PrehospTraumaScale_Value' => $data['PrehospTraumaScale_Value']
		);
		return $this->saveObjectWithCheckForUniqueness('PrehospTraumaScale',$params, 'CmpCallCard_id');
	}

	/**
	 * меняем значения "повод" и срочность первичного вызова
	 */
	public function updateReasonAndUrgencyInCmpCallCard($firstCard) {
		if(!$firstCard['CmpReason_id'] || !$firstCard['CmpCallCard_id']) return false;

		$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');

		//возьмем данные карты для просчета срочности вызова
		$selectOldquery = "SELECT
								CCC.cmpcallcard_id as \"cmpcallcard_id\",
                                CCC.cmpcallcard_numv as \"cmpcallcard_numv\",
                                CCC.cmpcallcard_ngod as \"cmpcallcard_ngod\",
                                CCC.cmpcallcard_prty as \"cmpcallcard_prty\",
                                CCC.cmpcallcard_sect as \"cmpcallcard_sect\",
                                CCC.cmparea_id as \"cmparea_id\",
                                CCC.cmpcallcard_city as \"cmpcallcard_city\",
                                CCC.cmpcallcard_ulic as \"cmpcallcard_ulic\",
                                CCC.cmpcallcard_dom as \"cmpcallcard_dom\",
                                CCC.cmpcallcard_kvar as \"cmpcallcard_kvar\",
                                CCC.cmpcallcard_podz as \"cmpcallcard_podz\",
                                CCC.cmpcallcard_etaj as \"cmpcallcard_etaj\",
                                CCC.cmpcallcard_kodp as \"cmpcallcard_kodp\",
                                CCC.cmpcallcard_telf as \"cmpcallcard_telf\",
                                CCC.cmpplace_id as \"cmpplace_id\",
                                CCC.cmpcallcard_comm as \"cmpcallcard_comm\",
                                CCC.cmpreason_id as \"cmpreason_id\",
                                CCC.person_id as \"person_id\",
                                CCC.person_surname as \"person_surname\",
                                CCC.person_firname as \"person_firname\",
                                CCC.person_secname as \"person_secname\",
                                CCC.person_age as \"person_age\",
                                CCC.person_birthday as \"person_birthday\",
                                CCC.person_polisser as \"person_polisser\",
                                CCC.person_polisnum as \"person_polisnum\",
                                CCC.sex_id as \"sex_id\",
                                CCC.cmpcallcard_ktov as \"cmpcallcard_ktov\",
                                CCC.cmpcalltype_id as \"cmpcalltype_id\",
                                CCC.cmpprofile_cid as \"cmpprofile_cid\",
                                CCC.cmpcallcard_smpt as \"cmpcallcard_smpt\",
                                CCC.cmpcallcard_stan as \"cmpcallcard_stan\",
                                CCC.cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
                                CCC.cmpcallcard_line as \"cmpcallcard_line\",
                                CCC.cmpresult_id as \"cmpresult_id\",
                                CCC.cmparea_gid as \"cmparea_gid\",
                                CCC.cmplpu_id as \"cmplpu_id\",
                                CCC.cmpdiag_oid as \"cmpdiag_oid\",
                                CCC.cmpdiag_aid as \"cmpdiag_aid\",
                                CCC.cmptrauma_id as \"cmptrauma_id\",
                                CCC.cmpcallcard_isalco as \"cmpcallcard_isalco\",
                                CCC.diag_uid as \"diag_uid\",
                                CCC.cmpcallcard_numb as \"cmpcallcard_numb\",
                                CCC.cmpcallcard_smpb as \"cmpcallcard_smpb\",
                                CCC.cmpcallcard_stbr as \"cmpcallcard_stbr\",
                                CCC.cmpcallcard_stbb as \"cmpcallcard_stbb\",
                                CCC.cmpprofile_bid as \"cmpprofile_bid\",
                                CCC.cmpcallcard_ncar as \"cmpcallcard_ncar\",
                                CCC.cmpcallcard_rcod as \"cmpcallcard_rcod\",
                                CCC.cmpcallcard_tabn as \"cmpcallcard_tabn\",
                                CCC.cmpcallcard_dokt as \"cmpcallcard_dokt\",
                                CCC.cmpcallcard_tab2 as \"cmpcallcard_tab2\",
                                CCC.cmpcallcard_tab3 as \"cmpcallcard_tab3\",
                                CCC.cmpcallcard_tab4 as \"cmpcallcard_tab4\",
                                CCC.diag_sid as \"diag_sid\",
                                CCC.cmptalon_id as \"cmptalon_id\",
                                CCC.cmpcallcard_expo as \"cmpcallcard_expo\",
                                CCC.cmpcallcard_smpp as \"cmpcallcard_smpp\",
                                CCC.cmpcallcard_vr51 as \"cmpcallcard_vr51\",
                                CCC.cmpcallcard_d201 as \"cmpcallcard_d201\",
                                CCC.cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
                                CCC.cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
                                CCC.cmpcallcard_dspp as \"cmpcallcard_dspp\",
                                CCC.cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
                                CCC.cmpcallcard_kakp as \"cmpcallcard_kakp\",
                                CCC.cmpcallcard_tper as \"cmpcallcard_tper\",
                                CCC.cmpcallcard_vyez as \"cmpcallcard_vyez\",
                                CCC.cmpcallcard_przd as \"cmpcallcard_przd\",
                                CCC.cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
                                CCC.cmpcallcard_tsta as \"cmpcallcard_tsta\",
                                CCC.cmpcallcard_tisp as \"cmpcallcard_tisp\",
                                CCC.cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
                                CCC.cmpcallcard_kilo as \"cmpcallcard_kilo\",
                                CCC.cmpcallcard_dlit as \"cmpcallcard_dlit\",
                                CCC.cmpcallcard_prdl as \"cmpcallcard_prdl\",
                                CCC.cmparea_pid as \"cmparea_pid\",
                                CCC.cmpcallcard_pcity as \"cmpcallcard_pcity\",
                                CCC.cmpcallcard_pulic as \"cmpcallcard_pulic\",
                                CCC.cmpcallcard_pdom as \"cmpcallcard_pdom\",
                                CCC.cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
                                CCC.cmplpu_aid as \"cmplpu_aid\",
                                CCC.cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
                                CCC.cmpcallcard_medc as \"cmpcallcard_medc\",
                                CCC.cmpcallcard_izv1 as \"cmpcallcard_izv1\",
                                CCC.cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
                                CCC.cmpcallcard_inf1 as \"cmpcallcard_inf1\",
                                CCC.cmpcallcard_inf2 as \"cmpcallcard_inf2\",
                                CCC.cmpcallcard_inf3 as \"cmpcallcard_inf3\",
                                CCC.cmpcallcard_inf4 as \"cmpcallcard_inf4\",
                                CCC.cmpcallcard_inf5 as \"cmpcallcard_inf5\",
                                CCC.cmpcallcard_inf6 as \"cmpcallcard_inf6\",
                                CCC.pmuser_insid as \"pmuser_insid\",
                                CCC.pmuser_updid as \"pmuser_updid\",
                                CCC.cmpcallcard_insdt as \"cmpcallcard_insdt\",
                                CCC.cmpcallcard_upddt as \"cmpcallcard_upddt\",
                                CCC.klrgn_id as \"klrgn_id\",
                                CCC.klsubrgn_id as \"klsubrgn_id\",
                                CCC.klcity_id as \"klcity_id\",
                                CCC.kltown_id as \"kltown_id\",
                                CCC.klstreet_id as \"klstreet_id\",
                                CCC.lpu_ppdid as \"lpu_ppdid\",
                                CCC.cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
                                CCC.cmpcallcard_isopen as \"cmpcallcard_isopen\",
                                CCC.cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
                                CCC.cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
                                CCC.cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
                                CCC.cmpppdresult_id as \"cmpppdresult_id\",
                                CCC.emergencyteam_id as \"emergencyteam_id\",
                                CCC.cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
                                CCC.lpu_id as \"lpu_id\",
                                CCC.cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
                                CCC.medpersonal_id as \"medpersonal_id\",
                                CCC.resultdeseasetype_id as \"resultdeseasetype_id\",
                                CCC.cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
                                CCC.unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                                CCC.cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
                                CCC.cmpcallcard_korp as \"cmpcallcard_korp\",
                                CCC.cmpcallcard_room as \"cmpcallcard_room\",
                                CCC.cmpcallcard_difftime as \"cmpcallcard_difftime\",
                                CCC.uslugacomplex_id as \"uslugacomplex_id\",
                                CCC.lpubuilding_id as \"lpubuilding_id\",
                                CCC.cmpcallertype_id as \"cmpcallertype_id\",
                                CCC.cmpcallplacetype_id as \"cmpcallplacetype_id\",
                                CCC.cmpcallcard_rid as \"cmpcallcard_rid\",
                                CCC.cmpcallcard_urgency as \"cmpcallcard_urgency\",
                                CCC.cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
                                CCC.cmpsecondreason_id as \"cmpsecondreason_id\",
                                CCC.cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
                                CCC.cmpcallreasontype_id as \"cmpcallreasontype_id\",
                                CCC.cmpreasonnew_id as \"cmpreasonnew_id\",
                                CCC.cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
                                CCC.cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
                                CCC.cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
                                CCC.cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
                                CCC.medstafffact_id as \"medstafffact_id\",
                                CCC.rankinscale_id as \"rankinscale_id\",
                                CCC.rankinscale_sid as \"rankinscale_sid\",
                                CCC.leavetype_id as \"leavetype_id\",
                                CCC.cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
                                CCC.lpusection_id as \"lpusection_id\",
                                CCC.cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
                                CCC.cmpcallcard_condition as \"cmpcallcard_condition\",
                                CCC.lpu_cid as \"lpu_cid\",
                                CCC.cmpcallcard_tend as \"cmpcallcard_tend\",
                                CCC.cmpcallcard_callltd as \"cmpcallcard_callltd\",
                                CCC.cmpcallcard_calllng as \"cmpcallcard_calllng\",
                                CCC.cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
                                CCC.cmprejectionreason_id as \"cmprejectionreason_id\",
                                CCC.cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
                                CCC.cmpcallcard_savedt as \"cmpcallcard_savedt\",
                                CCC.cmpcallcard_plandt as \"cmpcallcard_plandt\",
                                CCC.cmpcallcard_factdt as \"cmpcallcard_factdt\",
                                CCC.cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
                                CCC.cmpcallcard_isextra as \"cmpcallcard_isextra\",
                                CCC.cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
                                CCC.cmpcallcard_guid as \"cmpcallcard_guid\",
                                CCC.cmpcallcard_rguid as \"cmpcallcard_rguid\",
                                CCC.cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
                                CCC.cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
                                CCC.emergencyteam_guid as \"emergencyteam_guid\",
                                CCC.cmpcallcard_stordt as \"cmpcallcard_stordt\",
                                CCC.cmpcallcard_defcom as \"cmpcallcard_defcom\",
                                CCC.medservice_id as \"medservice_id\",
                                CCC.cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
                                CCC.cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
                                CCC.diag_sopid as \"diag_sopid\",
                                CCC.cmpleavetype_id as \"cmpleavetype_id\",
                                CCC.cmpleavetask_id as \"cmpleavetask_id\",
                                CCC.cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
                                CCC.cmptransporttype_id as \"cmptransporttype_id\",
                                CCC.cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
                                CCC.cmpcallcardresult_id as \"cmpcallcardresult_id\",
                                CCC.person_isunknown as \"person_isunknown\",
                                CCC.cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
                                CCC.lpu_smpid as \"lpu_smpid\",
                                CCC.lpu_hid as \"lpu_hid\",
                                CCC.unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
                                CCC.paytype_id as \"paytype_id\",
                                CCC.cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
                                CCC.cmpcallcard_sid as \"cmpcallcard_sid\",
                                CCC.cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
                                CCC.cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
                                CCC.cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
                                CCC.cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
                                CCC.cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
                                CCC.cmpcallsigntype_id as \"cmpcallsigntype_id\",
                                CCC.lpu_codesmo as \"lpu_codesmo\",
                                CCC.registry_sid as \"registry_sid\",
                                CCC.diag_gid as \"diag_gid\",
                                CCC.medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
                                CCC.cmpcommonstate_id as \"cmpcommonstate_id \"
								,cETS.EmergencyTeamStatus_Code as \"EmergencyTeamStatus_Code\"
							FROM v_CmpCallCard CCC  

							LEFT JOIN LATERAL (

									SELECT
										etsh.EmergencyTeamStatus_id as EmergencyTeamStatus_id,
										etsh.EmergencyTeamDelayType_id as EmergencyTeamDelayType_id,
										ets.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code
									FROM
										v_EmergencyTeamStatusHistory etsh 

										left join v_EmergencyTeamStatus ets  on ets.EmergencyTeamStatus_id = etsh.EmergencyTeamStatus_id

									WHERE
										etsh.CmpCallCard_id = CCC.CmpCallCard_id
									ORDER BY
										etsh.EmergencyTeamStatusHistory_id DESC
                                    LIMIT 1
							) as cETS ON TRUE
							WHERE CCC.CmpCallCard_id = :CmpCallCard_id";
		$result = $this->db->query($selectOldquery, $firstCard);
		if ( is_object($result) ) {
			$oldCard = $result->row_array('array');
		}else{
			return false;
		}


		if(!$oldCard['CmpCallPlaceType_id'] && $firstCard['CmpCallPlaceType_id']) {
			$oldCard['CmpCallPlaceType_id'] = $firstCard['CmpCallPlaceType_id'];
		}

		//Пересчитаем срочнось
		$getCallUrgParams = array(
			'CmpCallPlaceType_id' => $oldCard['CmpCallPlaceType_id'],
			'Person_Age' => $oldCard['Person_Age'],
			'CmpReason_id' => $firstCard['CmpReason_id'],
			'Lpu_id' => $oldCard['Lpu_id']
		);
		$urgencyObj = $this->getCallUrgencyAndProfile($getCallUrgParams);
		$urgency = $urgencyObj[0]['CmpUrgencyAndProfileStandart_Urgency'];


		$queryParams = array(
			'CmpCallCard_id' => $oldCard['CmpCallCard_id'],
			'CmpCallCard_Urgency' => ($urgency) ? $urgency : $oldCard['CmpCallCard_Urgency'],
			'CmpReason_id' => $firstCard['CmpReason_id']
		);

		$updateFields = '';
		$CurArmType = (!empty($_SESSION['CurArmType']) ? $_SESSION['CurArmType'] : '');
		if (in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp'))) {


			$lpuBuildingOptions = $this->getLpuBuildingOptions(array('session' => $_SESSION));

			if (isset($firstCard['CmpCallCard_IsExtra']) && $firstCard['CmpCallCard_IsExtra'] == 1) {

				if (!empty($oldCard['EmergencyTeam_id'])) {

					// Удаляется связь между вызовом и бригадой
					$this->setEmergencyTeam(array(
						'EmergencyTeam_id' => 0,
						'CmpCallCard_id' => $oldCard['CmpCallCard_id'],
						'pmUser_id' => $firstCard['pmUser_id']
					));

					//Статус бригады изменяется на «Конец обслуживания»
					$etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode(4);
					$this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
						"EmergencyTeam_id" => $oldCard["EmergencyTeam_id"],
						"EmergencyTeamStatus_id" => $etStatusId,
						"pmUser_id" => $firstCard["pmUser_id"]
					));

				}

				if (!empty($lpuBuildingOptions[0]["Lpu_eid"]) && !empty($lpuBuildingOptions[0]["LpuBuilding_eid"])) {
					$updateFields .= 'Lpu_smpid = :Lpu_eid,LpuBuilding_id = :LpuBuilding_eid,';
					$queryParams["Lpu_eid"] = $lpuBuildingOptions[0]["Lpu_eid"];
					$queryParams["LpuBuilding_eid"] = $lpuBuildingOptions[0]["LpuBuilding_eid"];

					$updateFields .= 'Lpu_ppdid = :Lpu_ppdid, MedService_id = :MedService_id,';
					$queryParams["Lpu_ppdid"] = null;
					$queryParams["MedService_id"] = null;

					// Статус первичного вызова меняется на «Передано»
					$this->setStatusCmpCallCard(array(
						'CmpCallCardStatusType_Code' => 1,
						'CmpCallCard_id' => $oldCard['CmpCallCard_id'],
						'pmUser_id' => $firstCard['pmUser_id']
					));
				} else {
					// Статус первичного вызова меняется на «Закрыто»
					$this->setStatusCmpCallCard(array(
						'CmpCallCardStatusType_Code' => 6,
						'CmpCallCard_id' => $oldCard['CmpCallCard_id'],
						'pmUser_id' => $firstCard['pmUser_id']
					));
					// Тип первичного вызова меняется «Консультативный
					$typeCardQuery = "SELECT 
                                        cmpcalltype_id as \"cmpcalltype_id\",
                                        cmpcalltype_code as \"cmpcalltype_code\",
                                        cmpcalltype_name as \"cmpcalltype_name\",
                                        pmuser_insid as \"pmuser_insid\",
                                        pmuser_updid as \"pmuser_updid\",
                                        cmpcalltype_insdt as \"cmpcalltype_insdt\",
                                        cmpcalltype_upddt as \"cmpcalltype_upddt\",
                                        region_id as \"region_id\",
                                        cmpcalltype_begdate as \"cmpcalltype_begdate\",
                                        cmpcalltype_enddate as \"cmpcalltype_enddate\",
                                        cmpcalltype_rowversion as \"cmpcalltype_rowversion\"
                                      FROM v_CmpCallType  WHERE CmpCallType_Code = :CmpCallType_Code LIMIT 1";

					$typeCard = $this->db->query($typeCardQuery, array('CmpCallType_Code' => 6))->row_array();
					if (!empty($typeCard["CmpCallType_id"])) {
						$updateFields .= 'CmpCallType_id = :CmpCallType_id,';
						$queryParams['CmpCallType_id'] = $typeCard['CmpCallType_id'];
					}
				}

			}

			$queryParams['CmpCallCard_IsExtra'] = isset($firstCard['CmpCallCard_IsExtra']) ? $firstCard['CmpCallCard_IsExtra'] : $oldCard['CmpCallCard_IsExtra'];

			$updateFields .= 'CmpCallCard_IsExtra = :CmpCallCard_IsExtra,';
		} else {
			if(($oldCard['CmpCallCard_IsExtra'] == 2) && ($firstCard['CmpCallCard_IsExtra'] == 1) && empty($oldCard['EmergencyTeam_id'])){

				$updateFields .= "CmpCallCard_IsPassSSMP = :CmpCallCard_IsPassSSMP, Lpu_smpid = :Lpu_smpid, LpuBuilding_id = :LpuBuilding_id,CmpCallCard_IsExtra = :CmpCallCard_IsExtra,Lpu_ppdid = null,MedService_id = null, ";
				$queryParams['CmpCallCard_IsPassSSMP'] = !empty($firstCard['CmpCallCard_IsPassSSMP']) ? $firstCard['CmpCallCard_IsPassSSMP'] : null;
				$queryParams['Lpu_smpid'] = !empty($firstCard['Lpu_smpid']) ? $firstCard['Lpu_smpid'] : null;
				$queryParams['LpuBuilding_id'] = !empty($firstCard['LpuBuilding_id']) ? $firstCard['LpuBuilding_id'] : null;
				$queryParams['CmpCallCard_IsExtra'] = $firstCard['CmpCallCard_IsExtra'];

				//2.	Статус первичного вызова меняется на «Возврат»
				$this->setStatusCmpCallCard(array(
					'CmpCallCardStatusType_Code' => 3,
					'CmpCallCard_id' => $firstCard['CmpCallCard_id'],
					'pmUser_id' => $firstCard['pmUser_id']
				));
			}

		}


		$query = "
            update CmpCallCard
            set
                {$updateFields}
                CmpCallCard_Urgency = :CmpCallCard_Urgency,
                CmpReason_id = :CmpReason_id
            where CmpCallCard_id = :CmpCallCard_id
		";

		try {
			$result = $this->queryResult($query, $queryParams);

			if ( is_object($result) ) {
				$tables = $result->result('array');
			} else {
				$tables = array();
			}
		} catch (Exception $e) {
			return false;
		}
	}
	
	/**
	 * Получение из структуры МО настроек 
	 */	
	public function getSettingsChallengesRequiringTheDecisionOfSeniorDoctor($param) {
		if(empty($param['LpuBuilding_id'])) return false;
		// Получение флага наблюдение старшим врачом
		$OperDepartamentOptions = $this -> getOperDepartamentOptions($param);
		return $OperDepartamentOptions;
	}

	/**
	 * Получение вызовов принятых диспетчером
	 */
	public function loadDispatcherCallsList($data){
		if(empty($data["pmUser_id"]) || empty($data['Lpu_id']) )
			return false;

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		$arrFilterStr = array();
		$nmpBuildings = '';
		$arrFilterStr[] = ' (1=1) ';
		if($data['filter'])
			$arrFilter = json_decode($data['filter'], true);
		if(isset($arrFilter) && is_array($arrFilter) && count($arrFilter) > 0) {
			foreach ($arrFilter as $one_filter) {
				if (!empty($one_filter['value']) && $one_filter['value'] != '')
					$arrFilterStr[] = " tabl." . $one_filter['property'] . " ILIKE '%" . $one_filter['value'] . "%' ";
			}
		}

		$filter = array();
		$queryParams = array();

		//$filter[] = "CCC.pmUser_insID = :pmUser_id";
		//$filter[] = "CCC.CmpCallCardStatusType_id in (1,2,4,19,20)";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		$queryParams[ 'CmpLpu_id' ] = $data['Lpu_id'] ;

		//костыль для казахстана
		$table = "CCLC";
		$korp_select = "case when CCLC.Korpus is not null then ', к.'||CCLC.Korpus else '' end";
		$ccl_extra = "CCLC.CmpCloseCard_IsExtra";
		$reason = "CCLC.CallPovod_id";
		if(getRegionNick() == 'kz'){
			$table = 'CCC';
			$korp_select = '';
			$ccl_extra = "CCC.CmpCallCard_IsExtra";
			$reason = "CCC.CmpReason_id";
		}

		$fields = "";

		if (!empty($data['searchType']) && in_array($data['searchType'], array('2', '3'))) {
			// ищем по карте вызова
			// пациент
            $fields .= " ,COALESCE( CCLC.Fam, CCC.Person_SurName, PS.Person_Surname, '') || ' ' || COALESCE(rtrim(CCLC.Name), rtrim(CCC.Person_FirName), PS.Person_Firname, ' ' )|| ' ' || COALESCE( rtrim(CCLC.Middle), rtrim(CCC.Person_SecName), PS.Person_Secname, ' ') as Person_FIO ";
            // возраст
            $fields .= "				,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay , 0) !=0 ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) > 12 THEN

                		to_char (DATEDIFF( 'year',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' лет'

                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE( CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	to_char (DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) ) || ' дн. '

                        ELSE
                        	to_char (DATEDIFF( 'minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' мес.'

                        END
                   	END
                 ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE to_char ( CCC.Person_Age ) || ' лет'
                    END
                 END
                 as personAgeText
			";
            // адрес
            $fields .= ",
				case when CCLC.CmpCallCard_id is not null then
					case when KL_AR.KLArea_Name is not null then KL_AR.KLArea_Name || ', ' else 
						case when SRGNTownCl.KLSubRgn_Name is not null then SRGNTownCl.KLSocr_Nick || ' '||SRGNTownCl.KLSubRgn_Name || ', ' else 
							case when KL_AR.KLArea_Name is not null then KL_AR.KLArea_Name || ', ' else '' 
							end 
						end 
					end ||
					
					case when CLCity.KLCity_Name is not null then 'г. ' || CLCity.KLCity_Name else '' end ||
					case when CLTown.KLTown_FullName is not null then
						case when CLCity.KLCity_Name is not null then ', ' else '' end
							|| coalesce(LOWER(CLTown.KLSocr_Nick) || '. ','') || CLTown.KLTown_Name else ''
					end ||
					case when CLStreet.KLStreet_FullName is not null then ', ' || LOWER(CLsocrStreet.KLSocr_Nick) || '. ' || CLStreet.KLStreet_Name else '' end ||

					--todo сделать из CmpCloseCard_UlicSecond
					case when SecondStreet.KLStreet_FullName is not null then
						case when socrSecondStreet.KLSocr_Nick is not null then ', ' || LOWER(socrSecondStreet.KLSocr_Nick) || '. ' || SecondStreet.KLStreet_Name else
						', ' || SecondStreet.KLStreet_FullName end
						else ''
					end ||

					case when CCLC.House is not null then ', д.' || CCLC.House else '' end ||
					{$korp_select} ||
					case when CCLC.Office is not null then ', кв.'|| CCLC.Office else '' end ||
					case when CCLC.Room is not null then ', ком. '|| CCLC.Room else '' end ||
					case when CLUAD.UnformalizedAddressDirectory_Name is not null then ', Место: ' || CLUAD.UnformalizedAddressDirectory_Name else '' end
				else
					case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick || ' ' || SRGNCity.KLSubRgn_Name || ', '
					else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick || ' ' || SRGNTown.KLSubRgn_Name || ', '
					else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick || ' ' || SRGN.KLSubRgn_Name || ', ' else '' end end end||
					case when City.KLCity_Name is not null then 'г. ' || City.KLCity_Name else '' end||
					case when Town.KLTown_FullName is not null then
						case when City.KLCity_Name is not null then ', ' else '' end
						 ||coalesce(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''
					end+

					case when Street.KLStreet_FullName is not null then
						case when socrStreet.KLSocr_Nick is not null then ', ' || LOWER(socrStreet.KLSocr_Nick) || '. ' || Street.KLStreet_Name else
						', ' || Street.KLStreet_FullName  end
					else case when CCC.CmpCallCard_Ulic is not null then ', ' || CmpCallCard_Ulic else '' end
					end ||

					case when SecondStreet.KLStreet_FullName is not null then
						case when socrSecondStreet.KLSocr_Nick is not null then ', ' || LOWER(socrSecondStreet.KLSocr_Nick) || '. ' || SecondStreet.KLStreet_Name else
						', '||SecondStreet.KLStreet_FullName end
						else ''
					end ||
					case when CCC.CmpCallCard_Dom is not null then ', д.' || CCC.CmpCallCard_Dom else '' end ||
					case when CCC.CmpCallCard_Korp is not null then ', к.' || CCC.CmpCallCard_Korp else '' end ||
					case when CCC.CmpCallCard_Kvar is not null then ', кв.' ||CCC.CmpCallCard_Kvar else '' end ||
					case when CCC.CmpCallCard_Room is not null then ', ком. ' || CCC.CmpCallCard_Room else '' end ||
					case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: ' || UAD.UnformalizedAddressDirectory_Name else '' end
				end as \"Adress_Name\"
			";
			// тип вызова
			$fields .= ",
				case when CCLC.CmpCallCard_id is not null then
					RTRIM(case when CCLT.CmpCallType_id is not null then CAST(CCLT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCLT.CmpCallType_Name, ''))

				else
					RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, ''))

				end as CmpCallType_Name
			";
			// вид вызова
			$fields .= ",
				 case COALESCE($ccl_extra, CCC.CmpCallCard_IsExtra)

					when 1 then 'экстренный'
					when 2 then 'неотложный'
					when 3 then 'вызов врача на дом'
					when 4 then 'обращение в поликлинику'
				end as CmpCallCard_IsExtraText
			";
			// повод
			$fields .= ",
				case when CCLC.CmpCallCard_id is not null then
					CCLR.CmpReason_Code || '. ' || CCLR.CmpReason_Name
				else
					CR.CmpReason_Code || '. ' || CR.CmpReason_Name
				end as CmpReason_Name
			";


			// фильтры
			if(!empty($data['Person_Birthday_From'])){
				$filter[] = "COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) >= :Person_Birthday_From";

				$queryParams[ 'Person_Birthday_From' ] = $data['Person_Birthday_From'] ;
			}
			if(!empty($data['Person_Birthday_To'])){
				$filter[] = "COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) <= :Person_Birthday_To";

				$queryParams[ 'Person_Birthday_To' ] = $data['Person_Birthday_To'] ;
			}
			if(!empty($data['Person_Age_From'])){
				$filter[] = ":Person_Age_From <= COALESCE(CCLC.Age, CCC.Person_Age)";

				$queryParams[ 'Person_Age_From' ] = $data['Person_Age_From'] ;
			}
			if(!empty($data['Person_Age_To'])){
				$filter[] = ":Person_Age_To >= COALESCE(CCLC.Age, CCC.Person_Age)";

				$queryParams[ 'Person_Age_To' ] = $data['Person_Age_To'] ;
			}
			if(!empty($data['Person_Fam'])){
				$filter[] = ":Person_Fam = COALESCE(CCLC.Fam, CCC.Person_SurName)";

				$queryParams[ 'Person_Fam' ] = $data['Person_Fam'] ;
			}
			if(!empty($data['Person_Name'])){
				$filter[] = ":Person_Name = COALESCE(CCLC.Name, CCC.Person_FirName)";

				$queryParams[ 'Person_Name' ] = $data['Person_Name'] ;
			}
			if(!empty($data['Person_Middle'])){
				$filter[] = ":Person_Middle = COALESCE(CCLC.Middle, CCC.Person_SecName)";

				$queryParams[ 'Person_Middle' ] = $data['Person_Middle'] ;
			}

			//по воходящим в район/область подразделениям
			if(!empty($data['KLAreaLevel_id']) && !empty($data['KLCity_id'])&& $data['KLAreaLevel_id']<3){
				if($data['KLAreaLevel_id'] == 1){
					$filter[] = "(pArea.KLArea_pid = :KLArea_id or Area.KLArea_pid = :KLArea_id)";
				}
				else{
					$filter[] = "Area.KLArea_pid = :KLArea_id";
				}
				$queryParams[ 'KLArea_id' ] = $data['KLCity_id'];
			}
			else{
				//по населенному пункту
				if( !empty($data['KLTown_id']) ){
					$filter[] = "COALESCE(CCLC.Town_id, CCC.KLTown_id) = :KLTown_id";

					$queryParams[ 'KLTown_id' ] = $data['KLTown_id'];
					//если региона нет тогда нас пункт не относится к городу
					if( !empty($data['KLSubRgn_id']) ){
						$filter[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
						$queryParams[ 'KLSubRgn_id' ] = $data['KLSubRgn_id'] ;
					}elseif ( !empty($data['KLCity_id']) ) {
						$filter[] = "COALESCE(CCLC.City_id, CCC.KLCity_id) = :KLCity_id";

						$queryParams[ 'KLCity_id' ] = $data['KLCity_id'] ;
					}
				} elseif ( !empty($data['KLCity_id'])) {
					$filter[] = "COALESCE(CCLC.City_id, CCC.KLCity_id) = :KLCity_id";

					$queryParams[ 'KLCity_id' ] = $data['KLCity_id'] ;
					//если город верхнего уровня
					if( !empty($data['KLSubRgn_id']) ){
						$filter[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
						$queryParams[ 'KLSubRgn_id' ] = $data['KLSubRgn_id'] ;
					}
				}
			}
		} else {
			// ищем по талону вызова
			// пациент
			$fields .= " ,COALESCE( CCC.Person_SurName, PS.Person_Surname, '') || ' ' || COALESCE(rtrim(CCC.Person_FirName), PS.Person_Firname, ' ' )|| ' ' || COALESCE( rtrim(CCC.Person_SecName), PS.Person_Secname, ' ') as Person_FIO";
            // возраст
			$fields .= "
				,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 AND COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay , 0) !=0 ) THEN
                	CASE WHEN DATEDIFF('minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) > 12 THEN

                		to_char (DATEDIFF( 'year',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' лет'

                    ELSE
                    	CASE WHEN DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) <=30 THEN

                        	to_char (DATEDIFF('day',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() ) ) || ' дн. '

                        ELSE
                        	to_char (DATEDIFF( 'minute',COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay) ,dbo.tzGetDate() )  ) || ' мес.'

                        END
                   	END
                 ELSE
                 	CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
                    ELSE cast(CCC.Person_Age as varchar) || ' лет'
                    END
                 END
                 as personAgeText
			";
			// адрес
			$fields .= "
				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

				end||

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
					', '||Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
				end ||

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
				end ||

				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
				end as Adress_Name
			";
			// тип вызова
			$fields .= ", RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as CmpCallType_Name";

			// вид вызова
			//$fields .= ", case when COALESCE(CCC.CmpCallCard_IsExtra,1) = 1 then 'экстренный' else 'неотложный' end as CmpCallCard_IsExtraText";
			$fields .= "
				 , case CCC.CmpCallCard_IsExtra
					when 1 then 'экстренный'
					when 2 then 'неотложный'
					when 3 then 'вызов врача на дом'
					when 4 then 'обращение в поликлинику'
				end as CmpCallCard_IsExtraText
			";
			// повод
			$fields .= ", CR.CmpReason_Code || '. ' || CR.CmpReason_Name as CmpReason_Name";


			// фильтры
			if(!empty($data['Person_Birthday_From'])){
				$filter[] = "CCC.Person_BirthDay >= :Person_Birthday_From";
				$queryParams[ 'Person_Birthday_From' ] = $data['Person_Birthday_From'] ;
			}
			if(!empty($data['Person_Birthday_To'])){
				$filter[] = "CCC.Person_BirthDay <= :Person_Birthday_To";
				$queryParams[ 'Person_Birthday_To' ] = $data['Person_Birthday_To'] ;
			}
			if(!empty($data['Person_Age_From'])){
				$filter[] = ":Person_Age_From <= CCC.Person_Age";
				$queryParams[ 'Person_Age_From' ] = $data['Person_Age_From'] ;
			}
			if(!empty($data['Person_Age_To'])){
				$filter[] = ":Person_Age_To >= COALESCE(CCC.Person_Age,0)";
				$queryParams[ 'Person_Age_To' ] = $data['Person_Age_To'] ;
			}
			if(!empty($data['Person_Fam'])){
				$filter[] = ":Person_Fam = CCC.Person_SurName";
				$queryParams[ 'Person_Fam' ] = $data['Person_Fam'] ;
			}
			if(!empty($data['Person_Name'])){
				$filter[] = ":Person_Name = CCC.Person_FirName";
				$queryParams[ 'Person_Name' ] = $data['Person_Name'] ;
			}
			if(!empty($data['Person_Middle'])){
				$filter[] = ":Person_Middle = CCC.Person_SecName";
				$queryParams[ 'Person_Middle' ] = $data['Person_Middle'] ;
			}

			//по воходящим в район/область подразделениям
			if(!empty($data['KLAreaLevel_id']) && !empty($data['KLCity_id'])&& $data['KLAreaLevel_id']<3){
				if($data['KLAreaLevel_id'] == 1){
					$filter[] = "(pArea.KLArea_pid = :KLArea_id or Area.KLArea_pid = :KLArea_id)";
				}
				else{
					$filter[] = "Area.KLArea_pid = :KLArea_id";
				}
				$queryParams[ 'KLArea_id' ] = $data['KLCity_id'];
			}
			else{
				//по населенному пункту
				if( !empty($data['KLTown_id']) ){
					$filter[] = "CCC.KLTown_id = :KLTown_id";
					$queryParams[ 'KLTown_id' ] = $data['KLTown_id'] ;
					//если региона нет тогда нас пункт не относится к городу
					if( !empty($data['KLSubRgn_id']) ){
						$filter[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
						$queryParams[ 'KLSubRgn_id' ] = $data['KLSubRgn_id'] ;
					}elseif ( !empty($data['KLCity_id']) ) {
						$filter[] = "CCC.KLCity_id = :KLCity_id";
						$queryParams[ 'KLCity_id' ] = $data['KLCity_id'] ;
					}
				} elseif ( !empty($data['KLCity_id'])) {
					$filter[] = "CCC.KLCity_id = :KLCity_id";
					$queryParams[ 'KLCity_id' ] = $data['KLCity_id'] ;
					//если город верхнего уровня
					if( !empty($data['KLSubRgn_id']) ){
						$filter[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
						$queryParams[ 'KLSubRgn_id' ] = $data['KLSubRgn_id'] ;
					}
				}
			}
		}

		if(!empty($data['begDate']) && !empty($data['endDate'])){
			$filter[] = 'CCC.CmpCallCard_prmDT BETWEEN :begDate AND :endDate';
			$begDate = date_create($data["begDate"]);
			$endDate = date_create($data['endDate']);
			$queryParams['begDate'] = $begDate->format('Y-m-d').' ' . ((!empty($data['begTime']) ? $data['begTime'] : ' 00:00'));
			$queryParams['endDate'] = $endDate->format('Y-m-d').' ' . ((!empty($data['endTime']) ? $data['endTime'] : ' 23:59'));
		}
		if(!empty($data['CmpCallCardStatusType_id'])){
			$filter[] = "CCC.CmpCallCardStatusType_id = :CmpCallCardStatusType_id ";
			$queryParams[ 'CmpCallCardStatusType_id' ] = $data['CmpCallCardStatusType_id'] ;
		}

		if(!empty($data['useLdapLpuBuildings']) && $data['useLdapLpuBuildings'] == 'true'){
			$lpuBuildingsWorkAccess = null;
			// здесь мы получаем список доступных подстанций для работы из лдапа
			$user = pmAuthUser::find($_SESSION['login']);
			$settings = @unserialize($user->settings);

			if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
				$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
			}

			if ( !(empty( $lpuBuildingsWorkAccess)) ) {
				if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
				// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)

				$lpuFilter ="CCC.LpuBuilding_id in (";

				foreach ($lpuBuildingsWorkAccess as &$value) {
					$lpuFilter .= $value.',';
				}
				$filter[] = substr($lpuFilter, 0, -1).')';
			}
		}

		if(!empty($data['MedPersonal_id'])){
			$filter[] = "PMUins.pmUser_Medpersonal_id = :MedPersonal_id";

			$queryParams[ 'MedPersonal_id' ] = $data['MedPersonal_id'] ;
		}
		if(!empty($data['UAD_id'])){
			$filter[] = "UAD.UnformalizedAddressDirectory_id = :UAD_id";
			$queryParams[ 'UAD_id' ] = $data['UAD_id'] ;
		}
		if(!empty($data['EmergencyTeam_Num'])){
			$filter[] = "ET.EmergencyTeam_Num = :EmergencyTeam_Num";
			$queryParams[ 'EmergencyTeam_Num' ] = $data['EmergencyTeam_Num'] ;
		}
		if(!empty($data['LpuBuilding_id'])){
			$filter[] = ":LpuBuilding_id =	case when (CCLC.CmpCallCard_id is not null) then CCLC.LpuBuilding_id else CCC.LpuBuilding_id end";
			$queryParams[ 'LpuBuilding_id' ] = $data['LpuBuilding_id'] ;
		}elseif($CurArmType == 'smpdispatchcall'){

			$smpUnitsNested = $this->loadSmpUnitsNestedALL($data);
			$operDepartament = $this->getOperDepartament($data);
			$queryParams[ 'LpuBuilding_pid' ] = $operDepartament[ 'LpuBuilding_pid' ] ;

			$fields .= ", LB.LpuBuilding_Name as LpuBuilding_Name";

			if ( !(empty( $smpUnitsNested)) ) {
				$lpuFilter ="(CCC.LpuBuilding_id in (";
				foreach ($smpUnitsNested as &$value) {
					$lpuFilter .= $value['LpuBuilding_id'].',';
				}

				/* #111030 пост 14
				 * Вызов, переданный в службу НМП, должен отображаться только 
				 * в журнале вызовов той МО, где был принят вызов. 
				 * В журнале вызовов той МО, куда он (этот вызов) был перенаправлен, он не должен отображаться - по той причине, 
				 * что он не имеет отношения к службам СМП этой МО.
				 */

				/* #130550
					В табличной области формы «Журнал вызовов», если она открыта из АРМ Диспетчера по приему вызовов,
					отображать вызовы всех подстанций, подчиненных Оперативному отделу текущего пользователя (п. 2.1.3)
				*/

				//$filter[] = substr($lpuFilter, 0, -1) . '))';
				$filter[] = substr($lpuFilter, 0, -1).') or CCC.LpuBuilding_id = :LpuBuilding_pid or CCC.Lpu_id = :CmpLpu_id )';
				//$filter[] = substr($lpuFilter, 0, -1).') or CCC.Lpu_ppdid = :CmpLpu_id or CCC.LpuBuilding_id = :LpuBuilding_pid or CCC.Lpu_id = :CmpLpu_id )';

			}
		}else {
			/* #116450
			 * Если форма «Журнал вызовов» открыта из любого АРМ, 
			 * кроме АРМ Диспетчера по приему вызовов, то отображаются вызовы, переданные на те подстанции, 
			 * которые выбраны на форме "Выбор подстанции для управления"
			 */

			// новая вводная
			/*
			 * Если форма «Журнал вызовов» открыта из любого АРМ, кроме АРМ Диспетчера по приему вызовов, то отображаются
			 * вызовы, которые не переданы ни на подстанцию, ни в службу НМП (не заполнены поля
			 * «МО передачи (НМП)», «Подразделение СМП») МО пользователя. Это, к примеру, вызовы с Типом вызова «Консультативный»,
			 * «Справка», «Абонент отключился», которые сохраняются без передачи на обслуживание.
			 * */
			// доп. вводная уфа опять вносит смуту
			/*
			 * теперь для региона уфы дп и св отображают разные данные
			 * */

			$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
			$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
			$lpuppdFilter = '';

			if(!$arrayIdSelectSmp ) {
				return array(array('success' => false, 'Error_Msg' => 'Не настроены подстанции для управления'));
			}

			if (in_array($CurArmType, array('dispnmp', 'dispdirnmp', 'nmpgranddoc', 'dispcallnmp'))) {
				$lpuFilter = "(MSnmp.LpuBuilding_id in (" . implode( ', ', $arrayIdSelectSmp) . ") OR CCC.LpuBuilding_id in (" . implode( ', ', $arrayIdSelectSmp);
				$nmpBuildings .= " left join v_MedService MSnmp  on MSnmp.MedService_id = CCC.MedService_id and MSnmp.MedServiceType_id in (18)";

				$nmpBuildings .= " left join v_LpuBuilding LBnmp  on LBnmp.LpuBuilding_id = MSnmp.LpuBuilding_id";

				$fields .= ", COALESCE(LBnmp.LpuBuilding_Name, LB.LpuBuilding_Name) as LpuBuilding_Name";

			} else {
				// подразделение
				if (!empty($data['searchType']) && in_array($data['searchType'], array('2', '3'))) {
					$fields .= ",
					COALESCE(CLLB.LpuBuilding_Name, LB.LpuBuilding_Name) as LpuBuilding_Name

				";
				} else{
					$fields .= ", LB.LpuBuilding_Name as LpuBuilding_Name";
				}

				$lpuFilter = "(CCC.LpuBuilding_id in (" . implode( ', ', $arrayIdSelectSmp);
			}


			if (
				( getRegionNick() != 'ufa' ) ||
				( $CurArmType == 'smpheaddoctor' && getRegionNick() == 'ufa' )
			) {
				$lpuppdFilter = ' or (CCC.lpu_id = :CmpLpu_id and CCC.Lpu_ppdid is not null)';
				$lpuppdFilter .= ' or (CCC.lpu_id = :CmpLpu_id and CCC.Lpu_ppdid is null and CCC.LpuBuilding_id is null)';

			}
			//echo($lpuFilter); exit;
			//$filter[] = substr($lpuFilter, 0, -1) . ') ' . $lpuppdFilter . ' )';
			//substr - резал ид подстанции
			$filter[] = $lpuFilter . ') ' . $lpuppdFilter . ' )';

		}


		/*
		elseif ( !empty($data['Person_FIO']) ){
			$person = explode(" ", $data['Person_FIO']);
			$n = count($person);
			if($n == 3){
				$filter[] = "( PS.Person_SurName = :Person_SurName AND PS.Person_FirName = :Person_FirName AND PS.Person_SecName = :Person_SecName )";
				$queryParams[ 'Person_SurName' ] = $person[0] ;
				$queryParams[ 'Person_FirName' ] = $person[1] ;
				$queryParams[ 'Person_SecName' ] = $person[2] ;
			}elseif($n == 2){
				$filter[] = "( (PS.Person_SurName = :Person_SurName AND PS.Person_FirName = :Person_FirName) OR (PS.Person_FirName = :Person_SurName AND PS.Person_SecName = :Person_FirName) )";
				$queryParams[ 'Person_SurName' ] = $person[0] ;
				$queryParams[ 'Person_FirName' ] = $person[1] ;
			}elseif($n == 1){
				$filter[] = "( PS.Person_SurName = :Person_Name OR PS.Person_FirName = :Person_Name OR PS.Person_SecName = :Person_Name )";
				$queryParams[ 'Person_Name' ] = $person[0] ;
			}
		}
		*/
		if(!empty($data['Sex_id'])){
			$filter[] = ":Sex_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.Sex_id else CCC.Sex_id end";
			$queryParams[ 'Sex_id' ] = $data['Sex_id'] ;
		}
		if(!empty($data['CmpCallType_id'])){
			$filter[] = ":CmpCallType_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.CallType_id else CCC.CmpCallType_id end";
			$queryParams[ 'CmpCallType_id' ] = $data['CmpCallType_id'] ;
		}
		if(!empty($data['CmpCallCard_IsExtra'])){
			$filter[] = ":CmpCallCard_IsExtra =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.CmpCloseCard_IsExtra else CCC.CmpCallCard_IsExtra end";
			$queryParams[ 'CmpCallCard_IsExtra' ] = $data['CmpCallCard_IsExtra'] ;
		}
		if(!empty($data['CmpReason_id'])){
			$filter[] = ":CmpReason_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.CallPovod_id else CCC.CmpReason_id end";
			$queryParams[ 'CmpReason_id' ] = $data['CmpReason_id'] ;
		}
		if(!empty($data['Diag_id_from'])){
			$filter[] = "D.Diag_Code >= DF.Diag_code";
		}
		$queryParams[ 'Diag_id_from' ] = $data['Diag_id_from'] ;

		if(!empty($data['Diag_id_to'])){
			$filter[] = "D.Diag_Code <= DT.Diag_code";
		}
		$queryParams[ 'Diag_id_to' ] = $data['Diag_id_to'] ;

		if(!empty($data['CmpResult_id'])){
			$filter[] = "CCLC.CmpResult_id = :CmpResult_id";
			$queryParams[ 'CmpResult_id' ] = $data['CmpResult_id'] ;
		}

		if(!empty($data['KLStreet_id'])){
			$filter[] = "CCC.KLStreet_id = :KLStreet_id";
			$queryParams[ 'KLStreet_id' ] = $data['KLStreet_id'] ;
		}

		if(!empty($data['UnformalizedAddressDirectory_id'])){
			$filter[] = "CCC.UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id";
			$queryParams[ 'UnformalizedAddressDirectory_id' ] = $data['UnformalizedAddressDirectory_id'] ;
		}

		if(!empty($data['CmpCallCard_Dom'])){
			$filter[] = "CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
			$queryParams[ 'CmpCallCard_Dom' ] = $data['CmpCallCard_Dom'] ;
		}

		if(!empty($data['CmpCallCard_Korp'])){
			$filter[] = "CCC.CmpCallCard_Korp = :CmpCallCard_Korp";
			$queryParams[ 'CmpCallCard_Korp' ] = $data['CmpCallCard_Korp'] ;
		}

		if(!empty($data['CmpCallCard_Kvar'])){
			$filter[] = "CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
			$queryParams[ 'CmpCallCard_Kvar' ] = $data['CmpCallCard_Kvar'] ;
		}

		if (!empty($data['searchType']) && in_array($data['searchType'], array('3'))){
			$filter[] = "CCLC.CmpCloseCard_id is not null";
		}

		if (!empty($data['hasHdMark'])){
			$beOrNot = $data['hasHdMark']==2 ? 'not' : '';
			$filter[] = "recordHasHdMark.id is {$beOrNot} null";
		}

		$this->load->model("Options_model", "opmodel");
		$o = $this->opmodel->getOptionsGlobals($data);
		$g_options = $o['globals'];
		if(isset($g_options["smp_call_time_format"]) && $g_options["smp_call_time_format"] == '2')
			$formatDateinList = 'varchar(5)';
		else
			$formatDateinList = 'varchar(8)';

		$declare = "
			with CmpCloseCardCombo_MO as (SELECT CmpCloseCardCombo_id as CmpCloseCardCombo_id FROM v_CmpCloseCardCombo WHERE ComboName = 'МО' LIMIT 1);";

		$query = "
		SELECT 
		--select 
		 * 
		--end select
		FROM 
		--from
		(
			SELECT 
				
					CCC.CmpCallCard_id as CmpCallCard_id
					,CCC.EmergencyTeam_id as EmergencyTeam_id
					,CCC112.CmpCallCard112_id as CmpCallCard112_id
					,CCLC.CmpCloseCard_id as CmpCloseCard_id
					,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') || ' ' || to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'HH24:MI:SS') as CmpCallCard_prmDate


					,CCC.CmpCallCard_Numv as CmpCallCard_Numv
					,CCC.CmpCallCard_Ngod as CmpCallCard_Ngod
					,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') as CmpCallCard_prmDateStr

					{$fields}
					,CCC.CmpCallCardStatusType_id as CmpCallCardStatusType_id
					,COALESCE(CCC.CmpCallCard_isControlCall,1) as CmpCallCard_isControlCall
					,case when recordHasHdMark.id is not null then 2 else 1 end as hasHdMark
					,CCCST.CmpCallCardStatusType_Name as CmpCallCardStatusType_Name
					,CCC.CmpCallCard_Comm as CmpCallCard_Comm

	                ,case when CCC.Lpu_ppdid is not null then 'НМП' else 'СМП' end as CmpCallCard_IsExtra
					--,case when MS.MedServiceType_id = 18 then 'НМП' else 'СМП' end as CmpCallCard_IsExtra
					,to_char (COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), 'DD Mon YYYY HH24:MI:SS') as \"Person_Birthday\"

	
					,D.diag_FullName as Diag
					,COALESCE(CLET.EmergencyTeam_Num,ET.EmergencyTeam_Num,'') as EmergencyTeam_Num
					,CCR.CmpCallRecord_id as CmpCallRecord_id
					,L.Lpu_Nick as Lpu_NMP_Name 
					,CCCRel.Lpu_Nick as ActiveVisitLpu_Nick
					,CCC.Lpu_id as Lpu_id


			FROM
				
					v_CmpCallCard CCC 

					left join {$this->schema}.v_CmpCloseCard CCLC  on CCLC.CmpCallCard_id = CCC.CmpCallCard_id

					left join v_CmpCallCard112 CCC112  on CCC112.CmpCallCard_id = CCC.CmpCallCard_id

					left join v_PersonState PS  on PS.Person_id = CCC.Person_id

					left join v_LpuBuilding LB  on LB.LpuBuilding_id = CCC.LpuBuilding_id

					left join v_LpuBuilding CLLB  on CLLB.LpuBuilding_id = CCLC.LpuBuilding_id

					left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id

					left join v_EmergencyTeam CLET  on CLET.EmergencyTeam_id = {$table}.EmergencyTeam_id

					left join v_MedService MS  on MS.MedService_id = CCC.MedService_id

					left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

					left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

					left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

					left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

		
					left join v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLSubRgn_id

					left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLSubRgn_id

					
					
					left join v_KLArea Area  on Area.KLArea_id = COALESCE(CCC.KLTown_id,CCC.KLCity_id)


					left join v_KLArea pArea  on pArea.KLArea_id = Area.KLArea_pid

					
					left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

					left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id


					left join v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond

				 	left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id


					left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

	
					left join v_KLSubRgn SRGNCityCl  on SRGNCityCl.KLSubRgn_id = CCLC.City_id

					left join v_KLSubRgn SRGNTownCl  on SRGNTownCl.KLSubRgn_id = CCLC.Town_id

					
					left join v_KLCity CLCity  on CLCity.KLCity_id = CCLC.City_id

					left join v_KLTown CLTown  on CLTown.KLTown_id = CCLC.Town_id

					LEFT JOIN KLArea KL_AR  on KL_AR.KLArea_id = CCLC.Area_id

					left join v_KLStreet CLStreet  on CLStreet.KLStreet_id = CCLC.Street_id

					left join v_KLSocr CLsocrStreet  on CLStreet.KLSocr_id = CLsocrStreet.KLSocr_id


					left join v_UnformalizedAddressDirectory CLUAD  on CLUAD.UnformalizedAddressDirectory_id = {$table}.UnformalizedAddressDirectory_id

	
					left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

					left join v_CmpCallType CCLT  on CCLT.CmpCallType_id = CCLC.CallType_id

					left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

					left join v_CmpReason CCLR  on CCLR.CmpReason_id = {$reason}

					left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

					left join v_Diag D  on D.Diag_id = CCLC.Diag_id

					left join v_Diag DF  on DF.Diag_id = :Diag_id_from

					left join v_Diag DT  on DT.Diag_id = :Diag_id_to


					left join v_CmpCallRecord CCR  on CCC.CmpCallCard_id = CCR.CmpCallCard_id

					
					left join v_Lpu L  on L.Lpu_id = CCC.Lpu_ppdid

					{$nmpBuildings}
					LEFT JOIN LATERAL(

						select
							CCCA.CmpCallCardAcceptor_Code as CmpCallCardAcceptor_Code
						from
							v_CmpUrgencyAndProfileStandart CUPS

						left join v_CmpCallCardAcceptor CCCA  on CCCA.CmpCallCardAcceptor_id = CUPS.CmpCallCardAcceptor_id

						where
							CUPS.CmpReason_id = CCC.CmpReason_id
							AND (COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) > CCC.Person_Age)

							AND COALESCE(CUPS.Lpu_id,0) in (:CmpLpu_id)
                        limit 1
					) CUAPS ON TRUE

					LEFT JOIN LATERAL(

						SELECT CER.CMPCloseCardExpertResponse_id as id
							FROM v_CMPCloseCardExpertResponse CER 

							LEFT JOIN v_CMPCloseCardExpertResponseType CERT  on CERT.CMPCloseCardExpertResponseType_id = CER.CMPCloseCardExpertResponseType_id

						WHERE
							CER.CMPCloseCard_id = CCLC.CMPCloseCard_id
							AND CERT.CMPCloseCardExpertResponseType_Code = 1
                        LIMIT 1
					) as recordHasHdMark ON TRUE

					LEFT JOIN LATERAL(

						select
							CCCE.pmUser_insID as \"pmUser_insID\"
						from 
						    v_CmpCallCardEvent CCCE
							left join v_CmpCallCardStatusTypeLink STL on STL.CmpCallCardEventType_id = CCCE.CmpCallCardEventType_id
						where 
						    CCCE.CmpCallCard_id = CCC.CmpCallCard_id
							and STL.CmpCallCardStatusType_id in (1,4,6,16,18,19,21)
						order by CCCE.CmpCallCardEvent_insDT
						limit 1
					) as PMU ON TRUE
					left join v_pmUser PMUins on PMU.pmUser_insID = PMUins.pmUser_id
					
					LEFT JOIN LATERAL (

						SELECT
							CmpCloseCard_id as CmpCloseCard_id
						FROM
							{$this->schema}.v_CmpCloseCard 

						WHERE
							CmpCallCard_id = CCC.CmpCallCard_id
                        LIMIT 1
					) as CCC_close ON TRUE
					
					LEFT JOIN LATERAL (

						SELECT
							LL.Lpu_Nick as Lpu_Nick
						FROM
							{$this->schema}.v_CmpCloseCardRel CCCR
						INNER JOIN v_Lpu LL  on LL.Lpu_id = CCCR.Localize

						WHERE
							CCCR.CmpCloseCard_id = CCC_close.CmpCloseCard_id AND
							CCCR.CmpCloseCardCombo_id = (SELECT CmpCloseCardCombo_id FROM CmpCloseCardCombo_MO) 
						LIMIT 1
					) as CCCRel ON TRUE
				
			WHERE
				
					" . implode(" AND ", $filter) . "
		) as tabl 
			--end from
			WHERE  
			--where
			" . implode(" AND ", $arrFilterStr) . " 
			--end where	
			order by
				--order by
				tabl.CmpCallCard_prmDate DESC
				-- end order by 	
		
		";
		//echo getDebugSQL($query, $queryParams);die();
		//var_dump(getDebugSQL(getLimitSQLPH($query, $data['start'], $data['limit'], 'DISTINCT'), $queryParams)); exit;
	    //echo getDebugSQL(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);die();
		//$res = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);
		//$queryWithLimit = $declare . getLimitSQLPH($query, 0, 1000);

		$querySQL = $declare . $query;
		$res = $this->db->query($querySQL, $queryParams);
		return $response = $res->result('array');

		/*$queryForCount = $declare . getCountSQLPH($query);

		$result_count = $this->db->query($queryForCount, $queryParams);
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if(is_object($res)){
			$resArr = $res->result('array');

			$response = array(
				'data' => $resArr,
				'totalCount' => $count
			);
			return $response;
		}*/

	}
	
	/**
	 * Изменение статуса карточки 112
	 */
	public function setStatusCmpCallCard112($data){

		$status112 = $this->getFirstRowFromQuery("SELECT CmpCallCard112StatusType_id as CmpCallCard112StatusType_id FROM CmpCallCard112  WHERE CmpCallCard112_id =:CmpCallCard112_id LIMIT 1", $data);


		//Нельзя менять статус обработанных и закрытых карт на "новая" и "в обработке"
		if(in_array($data['CmpCallCard112StatusType_id'], array('1','2')) && in_array($status112['CmpCallCard112StatusType_id'], array('3','4')))
		{
			return false;
		}

		$status = $this->getFirstRowFromQuery("SELECT C.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
			FROM CmpCallCard C 

			left join CmpCallCard112 C112  on C112.CmpCallCard_id = C.CmpCallCard_id

			WHERE CmpCallCard112_id =:CmpCallCard112_id LIMIT 1", $data);

		//Нельзя менять статус на "Обработана" если талон в статусе "Передано из 112"
		if(in_array($data['CmpCallCard112StatusType_id'], array('3')) && in_array($status['CmpCallCardStatusType_id'], array('20')))
		{
			return false;
		}

		//Если статус талона не "Передан из 112" то меняем статус карточки 112 на "Обработана"
		if(!in_array($status['CmpCallCardStatusType_id'], array('20')) && in_array($data['CmpCallCard112StatusType_id'], array('1','2'))){
			$data['CmpCallCard112StatusType_id'] = 3;
		}

		$query = "
			select 
			    Res as \"CmpCallCard112_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from p_CmpCallCard112_setStatus (
				CmpCallCard112_id := :CmpCallCard112_id,
				CmpCallCard112StatusType_id := :CmpCallCard112StatusType_id,
				pmUser_id := :pmUser_id
				)
		";
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Изменение статуса карточки 112
	 */
	public function find112CardsProcessing($data){
		$result = array();
		$query = "
			SELECT
				CCC112.CmpCallCard112_id as \"CmpCallCard112_id\"
			FROM
				v_CmpCallCard112 CCC112 

			WHERE
			CCC112.CmpCallCard112StatusType_id = 2
			AND CCC112.pmUser_updID = :pmUser_id
			AND cast(CCC112.CmpCallCard112_insDT as date) >= DATEADD('day', -2, dbo.tzGetDate())
				
		";
		//echo getDebugSQL($query, $data);die();
		$resp = $this->queryResult($query, array(
			'pmUser_id' => $data['pmUser_id']
		));
		if (count($resp) > 0) {
			foreach ($resp as $id112Card)
				$result[] = $id112Card['CmpCallCard112_id'];
		}
		return $result;

	}
	/**
	 * Получение карточек 112
	 */
	public function loadCmpCallCard112List($data){
		if(!empty($data['begDate']) && !empty($data['endDate'])){
			$filter[] = 'CCC112.Ier_IerIsoTime BETWEEN :begDate AND :endDate';
			$begDate = date_create($data["begDate"]);
			$endDate = date_create($data['endDate']);
			$queryParams['begDate'] = $begDate->format('Y-m-d').' 00:00';
			$queryParams['endDate'] = $endDate->format('Y-m-d').' 23:59';
		}
		if(!empty($data['Ier_AcceptOperatorStr'])){
			$filter[] = "CCC112.Ier_AcceptOperatorStr = :Ier_AcceptOperatorStr";
			$queryParams[ 'Ier_AcceptOperatorStr' ] = $data['Ier_AcceptOperatorStr'] ;
		}
		if(!empty($data['CmpCallCard112StatusType_id'])){
			$filter[] = "CCC112.CmpCallCard112StatusType_id = :CmpCallCard112StatusType_id";
			$queryParams[ 'CmpCallCard112StatusType_id' ] = $data['CmpCallCard112StatusType_id'] ;
		}
		$this->load->model("Options_model", "opmodel");
		$isAll112 = $this->opmodel->getOptionsGlobals($data,'smp_is_all_lpubuilding_with112');
		if(!empty($data['Lpu_id']) && $isAll112 != 2){
			$filter[] = "CCC.Lpu_id = :Lpu_id";
			$queryParams[ 'Lpu_id' ] = $data['Lpu_id'] ;
		}
		$query = "
		SELECT
			--select
			CCC112.CmpCallCard112_id as \"CmpCallCard112_id\",
			CCC112.CmpCallCard_id as \"CmpCallCard_id\",
			CCC112.CmpCallCard112StatusType_id as \"CmpCallCard112StatusType_id\",
			to_char(cast(CCC112.Ier_IerIsoTime as timestamp(3)), 'DD.MM.YYYY') || ' ' || to_char(cast(CCC112.Ier_IerIsoTime as timestamp(3)), 'HH24:MI:SS') as \"Ier_IerIsoTime\",


			CCC112.Ier_AcceptOperatorStr as \"Ier_AcceptOperatorStr\",
			CCC112.ExtPatientPerson_LastName || COALESCE(' ' || CCC112.ExtPatientPerson_FirstName,'') || COALESCE(' ' || CCC112.ExtPatientPerson_MiddleName,'') as \"ExtPatientPerson_Fio\",

			to_char(ExtPatientPerson_BirthdateIsoStr, 'YYYY-MM-DD HH24:MI:SS') as \"ExtPatientPerson_BirthdateIsoStr\",

			CCC112.ExtPatientPerson_Age as \"ExtPatientPerson_Age\",
			COALESCE(CCC112.Address_CityShort||'. ','') 

				|| COALESCE(CCC112.Address_City,'')

				|| case when nullif(CCC112.Address_District,'') is null then '' else ', '||CCC112.Address_District end
				|| COALESCE(', '||CCC112.Address_StreetShort||'. ',', ') || COALESCE(CCC112.Address_Street,'') 

				|| COALESCE(', д. ' || CCC112.Address_HouseNumber,'') 

				|| COALESCE(', корп. ' || CCC112.Address_HouseFraction,'') 

				|| COALESCE(', стр. ' || CCC112.Address_Building,'') 

				|| COALESCE(', владение ' || CCC112.Address_Ownership,'') 

				|| COALESCE(', адресный участок вне населенного пункта ' || CCC112.Address_TargetArea,'') 

				|| COALESCE(', улица вне населенного пункта ' || CCC112.Address_TargetAreaStreet,'') 

				|| COALESCE(', дорога ' || CCC112.Address_Road,'') 

				|| COALESCE(', уточн. ' || CCC112.Address_Clarification,'') 

				|| COALESCE(', пд. ' || CCC112.Address_Porch,'') 

				|| COALESCE(', эт. ' || CCC112.Address_Floor,'') 

				|| COALESCE(', кв. ' || CCC112.Address_Flat,'') 

			as \"Adress_Name\",
			CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\",
			CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\",
			CCC112ST.CmpCallCard112StatusType_Name as \"CmpCallCard112StatusType_Name\"
			--end select
		FROM
			--from
			v_CmpCallCard112 CCC112 

			left join v_CmpCallCard112StatusType CCC112ST  on CCC112ST.CmpCallCard112StatusType_id = CCC112.CmpCallCard112StatusType_id

			left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCC112.CmpCallCard_id

			--end from
		WHERE
			--where
				".implode(" AND ", $filter)."
			--end where
		order by
			-- order by
			CCC112.Ier_IerIsoTime DESC
			-- end order by
		";
		//var_dump(getDebugSQL($query, $queryParams)); exit;
	    //echo getDebugSQL(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);die();
		$res = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);

		$result_count = $this->db->query(getCountSQLPH($query), $queryParams);
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if(is_object($res)){
			$resArr = $res->result('array');

			$response = array(
				'data' => $resArr,
				'totalCount' => $count
			);
			return $response;
		}
	}

	/**
	 * Поиск карты вызова 112
	 */
	public function findCmpCallCard112($data){

		$filters = array();
		$filters[] = "CCC112.CmpCallCard112StatusType_id = 1";
		$filters[] = "CCC112.Ier_IerIsoTime is not NULL";

		if($data['Ier_AcceptOperatorStr']){
			$filters[] = "CCC112.Ier_AcceptOperatorStr = :Ier_AcceptOperatorStr";
		}
		$query = "
			SELECT
		  	    CCC112.CmpCallCard112_id as \"CmpCallCard112_id\",
				DATEDIFF('day',CCC112.Ier_IerIsoTime,dbo.tzGetDate()) as \"DateDiff\",
				to_char (cast(CCC112.Ier_IerIsoTime as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"Ier_IerIsoTime\",
				CCC112.CmpCallCard_id as \"CmpCallCard_id\"
			FROM
				v_CmpCallCard112 CCC112 

			WHERE
			" . implode(' and ', $filters) . "
			ORDER BY CCC112.CmpCallCard112_id ASC";

		$resp = $this->queryResult($query, array(
			'Ier_AcceptOperatorStr' => $data['Ier_AcceptOperatorStr']
		));

		if (count($resp) > 1) {
			return array('Error_Msg' => '', 'cnt' => 2, 'minDate' => $resp[0]['Ier_IerIsoTime']);
		} else if (empty($resp)) {
			return array('Error_Msg' => '', 'cnt' => 0);
		} else {

			if ($resp[0]['DateDiff'] = 0) {
				return array('Error_Msg' => '', 'cnt' => 1, 'CmpCallCard112_id' => $resp[0]['CmpCallCard112_id'], 'minDate' => 'toDay', 'CmpCallCard_id' => $resp[0]['CmpCallCard_id']);
			} else {
				return array('Error_Msg' => '', 'cnt' => 1, 'CmpCallCard112_id' => $resp[0]['CmpCallCard112_id'], 'minDate' => $resp[0]['Ier_IerIsoTime'], 'CmpCallCard_id' => $resp[0]['CmpCallCard_id']);
			}

		}
	}

	/**
	 * Получение данных карты 112
	 */
	function loadCmpCallCard112EditForm($data){
		if(empty($data['CmpCallCard_id']))
			return false;

		$sql = "
		SELECT

			Address_Building as \"Address_Building\"
			,Address_City as \"Address_City\"
			,Address_CityCode as \"Address_CityCode\"
			,Address_CityShort as \"Address_CityShort\"
			,Address_Clarification as \"Address_Clarification\"
			,Address_Code as \"Address_Code\"
			,Address_DisrtrCenterCode as \"Address_DisrtrCenterCode\"
			,Address_DistanceInKm as \"Address_DistanceInKm\"
			,Address_DistanceInM as \"Address_DistanceInM\"
			,Address_District as \"Address_District\"
			,Address_DistrictCode as \"Address_DistrictCode\"
			,Address_Flat as \"Address_Flat\"
			,Address_Floor as \"Address_Floor\"
			,Address_HouseCode as \"Address_HouseCode\"
			,Address_HouseFraction as \"Address_HouseFraction\"
			,Address_HouseNumber as \"Address_HouseNumber\"
			,Address_Ownership as \"Address_Ownership\"
			,Address_Porch as \"Address_Porch\"
			,Address_Road as \"Address_Road\"
			,Address_Street as \"Address_Street\"
			,Address_StreetCode as \"Address_StreetCode\"
			,Address_StreetShort as \"Address_StreetShort\"
			,Address_TargetArea as \"Address_TargetArea\"
			,Address_TargetAreaStreet as \"Address_TargetAreaStreet\"
			,Address_isNear as \"Address_isNear\"
			,case when COALESCE(Address_isNear,1) = 1 then 'нет' else 'да' end as \"Address_isNearText\"
			,CmpCallCard112_GUID as \"CmpCallCard112_GUID\"
			,CmpCallCard112_id as \"CmpCallCard112_id\"
			,CmpCallCard_id as \"CmpCallCard_id\"
			,CmpCallCard112StatusType_id as \"CmpCallCard112StatusType_id\"
			,CommonData_HrId as \"CommonData_HrId\"
			,CommonData_InjuredNumber as \"CommonData_InjuredNumber\"
			,CommonData_IsChemFlood as \"CommonData_IsChemFlood\"
			,CommonData_IsMalicious as \"CommonData_IsMalicious\"
			,case when COALESCE(CommonData_IsChemFlood,1) = 1 then 'нет' else 'да' end as \"CommonData_IsChemFloodText\"
			,case when COALESCE(CommonData_IsMalicious,1) = 1 then 'нет' else 'да' end as \"CommonData_IsMaliciousText\"
			,CommonData_Level as \"CommonData_Level\"
			,CommonData_LostNumber as \"CommonData_LostNumber\"
			,CommonData_RegionStr as \"CommonData_RegionStr\"
			,case when ISDATE(ExtPatientPerson_BirthdateIsoStr) = 1 then to_char(cast(ExtPatientPerson_BirthdateIsoStr as timestamp(3)),'DD Mon YYYY HH24:MI:SS')
			 else to_char(ExtPatientPerson_BirthdateIsoStr,'DD Mon YYYY HH24:MI:SS') end as \"ExtPatientPerson_BirthdateIsoStr\"
			,CommonData_TypeStr as \"CommonData_TypeStr\"
			,CommonData_description as \"CommonData_description\"
			,CommonData_isBlocking as \"CommonData_isBlocking\"
			,CommonData_isDanger as \"CommonData_isDanger\"
			,case when COALESCE(CommonData_isBlocking,1) = 1 then 'нет' else 'да' end as \"CommonData_isBlockingText\"
			,case when COALESCE(CommonData_isDanger,1) = 1 then 'нет' else 'да' end as \"CommonData_isDangerText\"
			,Coords_LapseRadius as \"Coords_LapseRadius\"
			,Coords_Latitude as \"Coords_Latitude\"
			,Coords_Longitude as \"Coords_Longitude\"
			,DdsData03_CallerTypeStr as \"DdsData03_CallerTypeStr\"
			,DdsData03_DdsTypeStr as \"DdsData03_DdsTypeStr\"
			,DdsData03_IsConsultation as \"DdsData03_IsConsultation\"
			,case when COALESCE(DdsData03_IsConsultation,1) = 1 then 'нет' else 'да' end as \"DdsData03_IsConsultationText\"
			,ExtPatientPerson_Age as \"ExtPatientPerson_Age\"
			,case when ISDATE(ExtPatientPerson_BirthdateIsoStr) = 1 then to_char(cast(ExtPatientPerson_BirthdateIsoStr as timestamp(3)),'DD Mon YYYY HH24:MI:SS')
			 else to_char(ExtPatientPerson_BirthdateIsoStr,'DD Mon YYYY HH24:MI:SS') end as \"ExtPatientPerson_BirthdateIsoStr\"
			,ExtPatientPerson_CallReasonStr as \"ExtPatientPerson_CallReasonStr\"
			,ExtPatientPerson_ExtId as \"ExtPatientPerson_ExtId\"
			,ExtPatientPerson_FirstName as \"ExtPatientPerson_FirstName\"
			,ExtPatientPerson_Gender as \"ExtPatientPerson_Gender\"
			,ExtPatientPerson_LastName as \"ExtPatientPerson_LastName\"
			,ExtPatientPerson_MiddleName as \"ExtPatientPerson_MiddleName\"
			,ExtPatientPerson_MoveAbility as \"ExtPatientPerson_MoveAbility\"
			,ExtPatientPerson_id as \"ExtPatientPerson_id\"
			,Ier_AcceptOperatorStr as \"Ier_AcceptOperatorStr\"
			,Ier_AcceptOreratorFio as \"Ier_AcceptOreratorFio\"
			,Ier_Building as \"Ier_Building\"
			,Ier_CardID as \"Ier_CardID\"
			,Ier_CdPn as \"Ier_CdPn\"
			,Ier_CgPn as \"Ier_CgPn\"
			,Ier_City as \"Ier_City\"
			,Ier_CityCode as \"Ier_CityCode\"
			,Ier_CityShort as \"Ier_CityShort\"
			,Ier_Clarification as \"Ier_Clarification\"
			,Ier_Code as \"Ier_Code\"
			,Ier_DistanceInKm as \"Ier_DistanceInKm\"
			,Ier_DistanceInM as \"Ier_DistanceInM\"
			,Ier_DistrCenterCode as \"Ier_DistrCenterCode\"
			,Ier_District as \"Ier_District\"
			,Ier_DistrictCode as \"Ier_DistrictCode\"
			,Ier_FirstName as \"Ier_FirstName\"
			,Ier_Flat as \"Ier_Flat\"
			,Ier_Floor as \"Ier_Floor\"
			,Ier_HouseCode as \"Ier_HouseCode\"
			,Ier_HouseFraction as \"Ier_HouseFraction\"
			,Ier_HouseNumber as \"Ier_HouseNumber\"
			,case when ISDATE(Ier_IerIsoTime) = 1 then to_char(cast(Ier_IerIsoTime as timestamp(3)),'DD Mon YYYY HH24:MI:SS')
			 else to_char(Ier_IerIsoTime,'DD Mon YYYY HH24:MI:SS') end as \"Ier_IerIsoTime\"
			,Ier_LapseRadius as \"Ier_LapseRadius\"
			,Ier_LastName as \"Ier_LastName\"
			,Ier_Latitude as \"Ier_Latitude\"
			,Ier_Longitude as \"Ier_Longitude\"
			,Ier_MiddleName as \"Ier_MiddleName\"
			,Ier_Ownership as \"Ier_Ownership\"
			,Ier_Porch as \"Ier_Porch\"
			,Ier_Road as \"Ier_Road\"
			,Ier_Street as \"Ier_Street\"
			,Ier_StreetCode as \"Ier_StreetCode\"
			,Ier_StreetShort as \"Ier_StreetShort\"
			,Ier_TargetArea as \"Ier_TargetArea\"
			,Ier_TargetAreaStreet as \"Ier_TargetAreaStreet\"
			,Ier_Text as \"Ier_Text\"
			,Ier_id as \"Ier_id\"
			,Ier_isNear as \"Ier_isNear\"
			,case when COALESCE(Ier_isNear,1) = 1 then 'нет' else 'да' end as \"Ier_isNearText\"
			,Smsler_Text as \"Smsler_Text\"

		FROM v_CmpCallCard112 

		WHERE
		CmpCallCard_id = :CmpCallCard_id";

		return $this->db->query($sql, array('CmpCallCard_id' => $data['CmpCallCard_id']))->result('array');
	}

	/**
	 * Загрузка статусов карты
	 */
	public function loadCmpCallCardStatusTypes($data){
		$query = "
			SELECT
				CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",
				CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\",
				CmpCallCardStatusType_Code as \"CmpCallCardStatusType_Code\"
			FROM
				v_CmpCallCardStatusType 
		";

		return $this->queryResult($query, array());


	}

	/**
	 * Получение информации о выводе группы "Внимание" в АРМ-е СВ подстанции СМП
	 */
	public function getIsOverCallLpuBuildingData($data, $fromJS = false, $operDepartment_id){
		$getIsOverCall = false;


		if($fromJS)
		{

			if(empty($data['LpuBuilding_id'])){
				if(isset($data['session']['CurARM']['LpuBuilding_id']) && isset($data['session']['CurARM']['LpuBuilding_id']) != '')
					$data['LpuBuilding_id'] = $data['session']['CurARM']['LpuBuilding_id'];
				else{
					$lpuBuilding = $this->getLpuBuildingBySessionData($data);
					if (empty($lpuBuilding[0]['LpuBuilding_id'])){
						return $this->createError(null, 'Не определена подстанция');
					}
					else{
						$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
					}
				}
			}
			$resOperDepartament = $this->getOperDepartament($data, true);
			$operDepartment_id = (is_array($resOperDepartament) && isset($resOperDepartament["LpuBuilding_pid"]))?$resOperDepartament["LpuBuilding_pid"]:NULL;
		}

		if(is_null($operDepartment_id))
			return $getIsOverCall;

		$sql = "
			SELECT
				CASE WHEN COALESCE(SUP.SmpUnitParam_IsOverCall, 2) = 1 THEN 'false' else 'true' END as \"SmpUnitParam_IsOverCall\"

			FROM
				v_LpuBuilding LB 

				LEFT JOIN LATERAL (

					select 
					    smpunitparam_id as smpunitparam_id,
                        lpubuilding_id as lpubuilding_id,
                        smpunittype_id as smpunittype_id,
                        lpubuilding_pid as lpubuilding_pid,
                        smpunitparam_insdt as smpunitparam_insdt,
                        smpunitparam_upddt as smpunitparam_upddt,
                        pmuser_insid as pmuser_insid,
                        pmuser_updid as pmuser_updid,
                        smpunitparam_iscancelling as smpunitparam_iscancelling,
                        smpunitparam_isduplicate as smpunitparam_isduplicate,
                        smpunitparam_isspecopsteam as smpunitparam_isspecopsteam,
                        smpunitparam_isautobuilding as smpunitparam_isautobuilding,
                        smpunitparam_iscall112 as smpunitparam_iscall112,
                        smpunitparam_issignalbeg as smpunitparam_issignalbeg,
                        smpunitparam_issignalend as smpunitparam_issignalend,
                        smpunitparam_isovercall as smpunitparam_isovercall,
                        smpunitparam_isautoemergduty as smpunitparam_isautoemergduty,
                        smpunitparam_isautoemergdutyclose as smpunitparam_isautoemergdutyclose,
                        smpunitparam_issendcall as smpunitparam_issendcall,
                        smpunitparam_isviewother as smpunitparam_isviewother,
                        smpunitparam_isktprint as smpunitparam_isktprint,
                        smpunitparam_iscallsendoc as smpunitparam_iscallsendoc,
                        smpunitparam_iscancldcall as smpunitparam_iscancldcall,
                        smpunitparam_iscallcontroll as smpunitparam_iscallcontroll,
                        smpunitparam_isshowallcallstodp as smpunitparam_isshowallcallstodp,
                        smpunitparam_isshowcallcount as smpunitparam_isshowcallcount,
                        smpunitparam_isnomoreassigncall as smpunitparam_isnomoreassigncall,
                        smpunitparam_maxcallcount as smpunitparam_maxcallcount,
                        lpu_eid as lpu_eid,
                        lpubuilding_eid as lpubuilding_eid,
                        smpunitparam_isautohome as smpunitparam_isautohome,
                        smpunitparam_isprescrhome as smpunitparam_isprescrhome,
                        smpunitparam_isgroupsubstation as smpunitparam_isgroupsubstation,
                        smpunitparam_isdispnocontrol as smpunitparam_isdispnocontrol,
                        smpunitparam_isdocnocontrol as smpunitparam_isdocnocontrol,
                        smpunitparam_isdispothercontrol as smpunitparam_isdispothercontrol,
                        smpunitparam_issavetreepath as smpunitparam_issavetreepath,
                        smpunitparam_iscallapprovesend as smpunitparam_iscallapprovesend,
                        smpunitparam_isnotransother as smpunitparam_isnotransother,
                        smpunitparam_isdenycallanswerdisp as smpunitparam_isdenycallanswerdisp,
                        smpunitparam_isshowcallnoteam as smpunitparam_isshowcallnoteam
					from v_SmpUnitParam 

					where LpuBuilding_id = LpuBuilding_id
					order by SmpUnitParam_id desc
					limit 1
				) SUP ON TRUE
			WHERE
				LB.LpuBuilding_id = :LpuBuilding_id
            limit 1
		";

		$result = $this->db->query($sql, array(
			'LpuBuilding_id' => $operDepartment_id
		))->result_array();
		if(isset($result[0]['SmpUnitParam_IsOverCall']) && $result[0]['SmpUnitParam_IsOverCall'] == 'false')
			$getIsOverCall = true;

		return $getIsOverCall;
	}

	/**
	 * Получение списка активов в поликлиннику
	 */
	public function loadAktivJournalList($data){

		$lpuBuildingsWorkAccess = null;
		$filter = array();

		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);
		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		};

		if ( !(empty( $lpuBuildingsWorkAccess)) ) {
			if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );

			// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
			$lpuFilter ="CCC.LpuBuilding_id in (";
			foreach ($lpuBuildingsWorkAccess as &$value) {
				$lpuFilter .= $value.',';
			}
			$filter[] = substr($lpuFilter, 0, -1).')';
		}

		if(!empty($data['begDate']) && !empty($data['endDate'])){

			//$filter[] = 'CCC.CmpCallCard_prmDT BETWEEN :begDate AND :endDate';
			//$filter[] = 'CCC.CmpCallCard_prmDT BETWEEN :begDate AND :endDate';

			$filter[] = "HV.HomeVisit_setDT BETWEEN :begDate AND :endDate";
			$begDate = date_create($data["begDate"]);
			$endDate = date_create($data['endDate']);
			$queryParams['begDate'] = $begDate->format('Y-m-d').' 00:00';
			$queryParams['endDate'] = $endDate->format('Y-m-d').' 23:59';
		}

		$queryParams['activeComboCode'] = 693;
		$filter[] = 'CLV.CmpCloseCardCombo_id is not null';
		$filter[] = 'HV.HomeVisit_id is not null';

		$query = "
		SELECT
			CCC.CmpCallCard_id as \"CmpCallCard_id\"
			,CCLC.CmpCloseCard_id as \"CmpCloseCard_id\"
 			,to_char(cast(CCLC.AcceptTime as timestamp(3)), 'DD.MM.YYYY') || ' ' || to_char(cast(CCLC.AcceptTime as timestamp(3)), 'HH24:MI:SS') as \"AcceptTime\"


			,CCLC.Day_num as \"Day_num\"
			,CCLC.Year_num as \"Year_num\"
			,COALESCE(CCLC.Fam, '') || ' ' || COALESCE(CCLC.Name, '') || ' ' || COALESCE(CCLC.Middle, '') as \"Person_FIO\"
			,CCLC.Age as \"Person_Age\"
			,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"

			,D.diag_FullName as \"Diag\"
			,l.Lpu_Nick as \"Lpu_Nick\"
			,COALESCE(HVS.HomeVisitStatus_Name, '') as \"HomeVisitStatus_Name\"
			--,HV.HomeVisit_Phone
			,RTrim(lu.LpuUnit_Phone) as \"Lpu_Phone\"
			,rtrim(HV.Address_Address) as \"HomeVisit_Address\"
			,lb.LpuBuilding_id as \"LpuBuilding_id\"
			,lb.LpuBuilding_Name as \"LpuBuilding_Name\"
			,mp.Person_Fin as \"Person_Fin\"
			--,RTRIM(COALESCE(LTRIM(RTRIM(msf.MedPersonal_TabCode)+ ' '), '') + COALESCE(LTRIM(RTRIM(mp.Person_FIO)+ ' '), '') + COALESCE(LTRIM(RTRIM('[' + LTRIM(RTRIM(msfls.LpuSection_Code)) + '. ' + LTRIM(RTRIM(msfls.LpuSection_Name)) + ']')+ ' '), '') + COALESCE(LTRIM(RTRIM(post.name)), '')) as MedStaff_Comp

		FROM
			v_CmpCallCard CCC 

			INNER JOIN {$this->schema}.v_CmpCloseCard CCLC  on CCLC.CmpCallCard_id = CCC.CmpCallCard_id

			LEFT JOIN LATERAL (

				SELECT
					CCLCR.CmpCloseCardCombo_id as CmpCloseCardCombo_id,
					CCLCR.Localize as Localize
				FROM {$this->schema}.v_CmpCloseCardRel CCLCR 

				LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo CCLCB  on CCLCB.CmpCloseCardCombo_id = CCLCR.CmpCloseCardCombo_id

				WHERE CCLCR.CmpCloseCard_id = CCLC.CmpCloseCard_id and CCLCB.CmpCloseCardCombo_Code = :activeComboCode
				ORDER BY CCLCR.CmpCloseCardRel_id desc
				LIMIT 1
			) CLV ON TRUE

			LEFT JOIN v_Diag D  on D.Diag_id = CCLC.Diag_id

			LEFT JOIN v_CmpReason CR  on CR.CmpReason_id = CCLC.CallPovod_id

			LEFT JOIN v_HomeVisit HV  on CCC.CmpCallCard_id = HV.CmpCallCard_id

			LEFT JOIN v_HomeVisitStatus HVS  on HVS.HomeVisitStatus_id = HV.HomeVisitStatus_id


			LEFT JOIN v_Lpu l  on l.Lpu_id = HV.Lpu_id

			left join v_LpuBuilding lb  on lb.LpuBuilding_id = CCC.LpuBuilding_id

			LEFT JOIN LATERAL (

				SELECT
					lu.LpuUnit_Phone as LpuUnit_Phone
				FROM v_LpuUnit lu 

				WHERE lu.Lpu_id = l.Lpu_id
				AND lu.LpuUnit_Phone is not null
				AND lu.LpuUnitType_id=2   -- Тип группы отделений (Поликлиника)
				AND lu.LpuUnit_IsEnabled = 2
				LIMIT 1
			) lu ON TRUE
			LEFT JOIN LATERAL (

				SELECT
					hvst.MedPersonal_id as MedPersonal_id
				FROM v_HomeVisitStatusHist hvst 

				WHERE hvst.HomeVisit_id = HV.HomeVisit_id
				order by hvst.HomeVisitStatusHist_id desc
				LIMIT 1
			) hvstMedstaffFact ON TRUE
			LEFT JOIN LATERAL (

				select MedPersonal_id as MedPersonal_id, Person_FIO as Person_FIO, Person_Fin as Person_Fin
				from v_MedPersonal 

				where MedPersonal_id = hvstMedstaffFact.MedPersonal_id
				limit 1
			) mp ON TRUE
		WHERE
				".implode(" AND ", $filter)."
		";

		//var_dump(getDebugSQL($query, $queryParams)); exit;

		$res = $this->db->query($query, $queryParams);


		if(is_object($res)){
			$resArr = $res->result('array');

			return $resArr;
		}
	}
	/**
	 * создание направления из формы госпитализации смп
	 */
	function createEvnDirection($data, $IsSMPServer = false){


		if ($data) {
			$data['object'] = 'TimetableStac';
			$data['TimetableObject_id'] = 2;
			$data['Evn_pid'] = null;
			$data['date'] = date('d.m.Y');
			$data['ignoreCanRecord'] = true; // признак игнора записи на бирку
			$data['EmergencyData_id'] = null;

			$this->load->model('TimetableGraf_model', 'ttgmodel');
			$this->load->model('CmpCallCard_model', 'cccmodel');
			$this->load->model('EvnDirection_model', 'edmodel');

			/*$LpuSection = $this->getLpuSectionByMO($data, true); // В зависимости от наличия профиля отделения находит отделение на которое будет создана бирка и направление
			if(empty($LpuSection))
				$LpuSection = $this->getLpuSectionByMO($data, false); // отчаянная попытка найти отделение с типом группы отделений "Круглосуточный стационар"*/

			if(!$data['LpuSection_id'] || !(intval($data['LpuSection_id'],10) > 0))
				return false;


			$data['TimetableStac_id'] = $this->ttgmodel->getFreeTimetable($data);

			// 3. если свободной бирки нет - создаем новую бирку И записываем на нее сразу
			if ($data['TimetableStac_id']==0) {
				$response = $this->ttgmodel->Create($data);
				if (!( is_array($response) && count($response) > 0 )) {
					// Ошибка
					$this->textlog->add('Ошибка при создании экстренной койки');
					$this->ReturnData(array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка при создании экстренной койки')));
					return false;
				}
				$data['TimetableStac_id'] = $response[0]['TimetableStac_id'];

				// 3. Записываем на бирку
				$response = $this->ttgmodel->Apply($data, false);
			} elseif ($data['TimetableStac_id']>0) {

				// 3. Записываем на бирку
				$response = $this->ttgmodel->Apply($data, false);
				if ( !empty($response['Error_Msg']) ) {
					// Ошибка
					return array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка при записи на экстренную койку. ' . $response['Error_Msg']));
				}
			} else {
				// Ошибка
				return array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка при получении свободных экстренных коек'));
			}
			//}

			$personRes = $this->db->query("
				select PersonEvn_id as \"PersonEvn_id\", Server_id as \"Server_id\", Person_IsUnknown as \"Person_IsUnknown\"
				from v_PersonState 
				where Person_id = :Person_id
				limit 1
			", array('Person_id' => $data['Person_id']));
			$personState = $personRes->result('array');

			if (isset($personState[0]) && $personState[0]['Person_IsUnknown'] != 2) {

				$EvnDirectionNum = $this->edmodel->getEvnDirectionNumber(array('Lpu_id' => $data['Lpu_hid']));
				$omsPayTypeId = $this->getFirstResultFromQuery("
					select PayType_id as \"PayType_id\"
					from v_PayType 
					where PayType_SysNick = 'oms'
					limit 1
				");
				$EmergencyTeam_HeadShiftRes = $this->db->query("
					select EmergencyTeam_HeadShift as \"EmergencyTeam_HeadShift\", EmergencyTeam_HeadShiftWorkPlace as \"EmergencyTeam_HeadShiftWorkPlace\"
					from v_EmergencyTeam 
					where EmergencyTeam_id = :EmergencyTeam_id
					limit 1
				", array('EmergencyTeam_id' => $data['EmergencyTeam_id']));
				$EmergencyTeam_HeadShift = $EmergencyTeam_HeadShiftRes->result('array');

				$LpuSectionProfile_id = $this->getFirstResultFromQuery("
					select LpuSectionProfile_id as \"LpuSectionProfile_id\"
					from v_LpuSection 

					where LpuSection_id = :LpuSection_id
					limit 1
				", array('LpuSection_id' => $data['LpuSection_id']));


				$evnData = array(
					'Person_id' => $data['Person_id'],
					'PersonEvn_id' => $personState[0]['PersonEvn_id'],
					'EvnDirection_pid' => null,
					'Lpu_id' => $data['Lpu_id'],
					'EvnDirection_Descr' => null,
					'Server_id' => $personState[0]['Server_id'],
					'EvnDirection_IsAuto' => 2,
					'EvnDirection_setDate' => date('Y-m-d H:i:s'),
					'PayType_id' => $omsPayTypeId,
					'DirType_id' => 5,
					'LpuSection_id' => null,
					'LpuSection_did' => $data['LpuSection_id'],
					'Diag_id' => $data['Diag_id'],
					'EvnDirection_Num' => $EvnDirectionNum[0]['EvnDirection_Num'],
					'From_MedStaffFact_id' => $EmergencyTeam_HeadShift[0]['EmergencyTeam_HeadShiftWorkPlace'],
					'MedPersonal_id' => $EmergencyTeam_HeadShift[0]['EmergencyTeam_HeadShift'],
					'MedPersonal_zid' => null,
					'Lpu_did' => $data['Lpu_hid'],
					'pmUser_id' => $data["pmUser_id"],
					'TimetableStac_id' => $data['TimetableStac_id'],
					'LpuSectionProfile_id' => $LpuSectionProfile_id
				);

				$EvnDirection = $this->edmodel->saveEvnDirection($evnData);
				if (!empty($EvnDirection[0]['Error_Msg'])) {
					return array('success' => false, 'Error_Code' => $EvnDirection[0]['Error_Code'], 'Error_Msg' => $EvnDirection[0]['Error_Msg']);
				}

			}
			$cmpCloseCardTimeTable = $this->cccmodel->setCmpCloseCardTimetable($data);
			if ($IsSMPServer) {
				$query = "
					SELECT 
					cmpcallcard_id as \"cmpcallcard_id\",
                    cmpcallcard_numv as \"cmpcallcard_numv\",
                    cmpcallcard_ngod as \"cmpcallcard_ngod\",
                    cmpcallcard_prty as \"cmpcallcard_prty\",
                    cmpcallcard_sect as \"cmpcallcard_sect\",
                    cmparea_id as \"cmparea_id\",
                    cmpcallcard_city as \"cmpcallcard_city\",
                    cmpcallcard_ulic as \"cmpcallcard_ulic\",
                    cmpcallcard_dom as \"cmpcallcard_dom\",
                    cmpcallcard_kvar as \"cmpcallcard_kvar\",
                    cmpcallcard_podz as \"cmpcallcard_podz\",
                    cmpcallcard_etaj as \"cmpcallcard_etaj\",
                    cmpcallcard_kodp as \"cmpcallcard_kodp\",
                    cmpcallcard_telf as \"cmpcallcard_telf\",
                    cmpplace_id as \"cmpplace_id\",
                    cmpcallcard_comm as \"cmpcallcard_comm\",
                    cmpreason_id as \"cmpreason_id\",
                    person_id as \"person_id\",
                    person_surname as \"person_surname\",
                    person_firname as \"person_firname\",
                    person_secname as \"person_secname\",
                    person_age as \"person_age\",
                    person_birthday as \"person_birthday\",
                    person_polisser as \"person_polisser\",
                    person_polisnum as \"person_polisnum\",
                    sex_id as \"sex_id\",
                    cmpcallcard_ktov as \"cmpcallcard_ktov\",
                    cmpcalltype_id as \"cmpcalltype_id\",
                    cmpprofile_cid as \"cmpprofile_cid\",
                    cmpcallcard_smpt as \"cmpcallcard_smpt\",
                    cmpcallcard_stan as \"cmpcallcard_stan\",
                    cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
                    cmpcallcard_line as \"cmpcallcard_line\",
                    cmpresult_id as \"cmpresult_id\",
                    cmparea_gid as \"cmparea_gid\",
                    cmplpu_id as \"cmplpu_id\",
                    cmpdiag_oid as \"cmpdiag_oid\",
                    cmpdiag_aid as \"cmpdiag_aid\",
                    cmptrauma_id as \"cmptrauma_id\",
                    cmpcallcard_isalco as \"cmpcallcard_isalco\",
                    diag_uid as \"diag_uid\",
                    cmpcallcard_numb as \"cmpcallcard_numb\",
                    cmpcallcard_smpb as \"cmpcallcard_smpb\",
                    cmpcallcard_stbr as \"cmpcallcard_stbr\",
                    cmpcallcard_stbb as \"cmpcallcard_stbb\",
                    cmpprofile_bid as \"cmpprofile_bid\",
                    cmpcallcard_ncar as \"cmpcallcard_ncar\",
                    cmpcallcard_rcod as \"cmpcallcard_rcod\",
                    cmpcallcard_tabn as \"cmpcallcard_tabn\",
                    cmpcallcard_dokt as \"cmpcallcard_dokt\",
                    cmpcallcard_tab2 as \"cmpcallcard_tab2\",
                    cmpcallcard_tab3 as \"cmpcallcard_tab3\", 
                    cmpcallcard_tab4 as \"cmpcallcard_tab4\",
                    diag_sid as \"diag_sid\",
                    cmptalon_id as \"cmptalon_id\",
                    cmpcallcard_expo as \"cmpcallcard_expo\",
                    cmpcallcard_smpp as \"cmpcallcard_smpp\",
                    cmpcallcard_vr51 as \"cmpcallcard_vr51\",
                    cmpcallcard_d201 as \"cmpcallcard_d201\",
                    cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
                    cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
                    cmpcallcard_dspp as \"cmpcallcard_dspp\",
                    cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
                    cmpcallcard_kakp as \"cmpcallcard_kakp\",
                    cmpcallcard_tper as \"cmpcallcard_tper\",
                    cmpcallcard_vyez as \"cmpcallcard_vyez\",
                    cmpcallcard_przd as \"cmpcallcard_przd\",
                    cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
                    cmpcallcard_tsta as \"cmpcallcard_tsta\",
                    cmpcallcard_tisp as \"cmpcallcard_tisp\",
                    cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
                    cmpcallcard_kilo as \"cmpcallcard_kilo\",
                    cmpcallcard_dlit as \"cmpcallcard_dlit\",
                    cmpcallcard_prdl as \"cmpcallcard_prdl\",
                    cmparea_pid as \"cmparea_pid\",
                    cmpcallcard_pcity as \"cmpcallcard_pcity\",
                    cmpcallcard_pulic as \"cmpcallcard_pulic\",
                    cmpcallcard_pdom as \"cmpcallcard_pdom\",
                    cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
                    cmplpu_aid as \"cmplpu_aid\",
                    cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
                    cmpcallcard_medc as \"cmpcallcard_medc\",
                    cmpcallcard_izv1 as \"cmpcallcard_izv1\",
                    cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
                    cmpcallcard_inf1 as \"cmpcallcard_inf1\",
                    cmpcallcard_inf2 as \"cmpcallcard_inf2\",
                    cmpcallcard_inf3 as \"cmpcallcard_inf3\",
                    cmpcallcard_inf4 as \"cmpcallcard_inf4\",
                    cmpcallcard_inf5 as \"cmpcallcard_inf5\",
                    cmpcallcard_inf6 as \"cmpcallcard_inf6\",
                    pmuser_insid as \"pmuser_insid\",
                    pmuser_updid as \"pmuser_updid\",
                    cmpcallcard_insdt as \"cmpcallcard_insdt\",
                    cmpcallcard_upddt as \"cmpcallcard_upddt\",
                    klrgn_id as \"klrgn_id\",
                    klsubrgn_id as \"klsubrgn_id\",
                    klcity_id as \"klcity_id\",
                    kltown_id as \"kltown_id\",
                    klstreet_id as \"klstreet_id\",
                    lpu_ppdid as \"lpu_ppdid\",
                    cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
                    cmpcallcard_isopen as \"cmpcallcard_isopen\",
                    cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
                    cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
                    cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
                    cmpppdresult_id as \"cmpppdresult_id\",
                    emergencyteam_id as \"emergencyteam_id\",
                    cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
                    lpu_id as \"lpu_id\",
                    cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
                    medpersonal_id as \"medpersonal_id\",
                    resultdeseasetype_id as \"resultdeseasetype_id\",
                    cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
                    unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                    cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
                    cmpcallcard_korp as \"cmpcallcard_korp\",
                    cmpcallcard_room as \"cmpcallcard_room\",
                    cmpcallcard_difftime as \"cmpcallcard_difftime\",
                    uslugacomplex_id as \"uslugacomplex_id\",
                    lpubuilding_id as \"lpubuilding_id\",
                    cmpcallertype_id as \"cmpcallertype_id\",
                    cmpcallplacetype_id as \"cmpcallplacetype_id\",
                    cmpcallcard_rid as \"cmpcallcard_rid\",
                    cmpcallcard_urgency as \"cmpcallcard_urgency\",
                    cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
                    cmpsecondreason_id as \"cmpsecondreason_id\",
                    cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
                    cmpcallreasontype_id as \"cmpcallreasontype_id\",
                    cmpreasonnew_id as \"cmpreasonnew_id\",
                    cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
                    cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
                    cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
                    cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
                    medstafffact_id as \"medstafffact_id\",
                    rankinscale_id as \"rankinscale_id\",
                    rankinscale_sid as \"rankinscale_sid\",
                    leavetype_id as \"leavetype_id\",
                    cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
                    lpusection_id as \"lpusection_id\",
                    cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
                    cmpcallcard_condition as \"cmpcallcard_condition\",
                    lpu_cid as \"lpu_cid\",
                    cmpcallcard_tend as \"cmpcallcard_tend\",
                    cmpcallcard_callltd as \"cmpcallcard_callltd\",
                    cmpcallcard_calllng as \"cmpcallcard_calllng\",
                    cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
                    cmprejectionreason_id as \"cmprejectionreason_id\",
                    cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
                    cmpcallcard_savedt as \"cmpcallcard_savedt\",
                    cmpcallcard_plandt as \"cmpcallcard_plandt\",
                    cmpcallcard_factdt as \"cmpcallcard_factdt\",
                    cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
                    cmpcallcard_isextra as \"cmpcallcard_isextra\",
                    cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
                    cmpcallcard_guid as \"cmpcallcard_guid\",
                    cmpcallcard_rguid as \"cmpcallcard_rguid\",
                    cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
                    cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
                    emergencyteam_guid as \"emergencyteam_guid\",
                    cmpcallcard_stordt as \"cmpcallcard_stordt\",
                    cmpcallcard_defcom as \"cmpcallcard_defcom\",
                    medservice_id as \"medservice_id\",
                    cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
                    cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
                    diag_sopid as \"diag_sopid\",
                    cmpleavetype_id as \"cmpleavetype_id\",
                    cmpleavetask_id as \"cmpleavetask_id\",
                    cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
                    cmptransporttype_id as \"cmptransporttype_id\",
                    cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
                    cmpcallcardresult_id as \"cmpcallcardresult_id\",
                    person_isunknown as \"person_isunknown\",
                    cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
                    lpu_smpid as \"lpu_smpid\",
                    lpu_hid as \"lpu_hid\",
                    unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
                    paytype_id as \"paytype_id\",  
                    cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
                    cmpcallcard_sid as \"cmpcallcard_sid\",
                    cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
                    cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
                    cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
                    cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
                    cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
                    cmpcallsigntype_id as \"cmpcallsigntype_id\",
                    lpu_codesmo as \"lpu_codesmo\",
                    registry_sid as \"registry_sid\",
                    diag_gid as \"diag_gid\",
                    medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
                    cmpcommonstate_id as \"cmpcommonstate_id\"
					FROM v_CmpCallCard CCC 

					WHERE CCC.CmpCallCard_id = :CmpCallCard_id;
				";
				$result = $this->db->query($query, array(
					'CmpCallCard_id' => $data['CmpCallCard_id']
				));
				if (!is_object($result)) {
					return false;
				}
				$result = $result->result('array');


				if (!isset($result[0]['CmpCallCard_id'])) {

					$smpDB = $this->load->database('default', true);
					$result = $smpDB->query($query, array(
						'CmpCallCard_id' => $data['CmpCallCard_id']
					));
					if (!is_object($result)) {
						return false;
					}
					$result = $result->result('array');

					$res = $this->resaveCmpCallCard($result[0], $data['CmpCallCard_id']);
					if (!is_object($result)) {
						return false;
					}
				}

				if (defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE) {

					if (is_array($cmpCloseCardTimeTable)) {
						$this->load->model('Replicator_model');
						$this->Replicator_model->sendRecordToActiveMQ(array(
							'table' => 'CmpCloseCardTimetable',
							'type' => 'insert',
							'keyParam' => 'CmpCloseCardTimetable_id',
							'keyValue' => $cmpCloseCardTimeTable[0]["CmpCloseCardTimetable_id"]
						));
					}

				}

			}
			return array('success' => true, 'Error_Code' => 0, 'Error_Msg' => '');
		} else {
			// Ошибка
			return array('success' => false, 'Error_Code' => -1, 'Error_Msg' => toUTF('Ошибка входящих данных'));
		}

	}
	/**
	 *  Получение отделения для создания бирки и направления при госпитализации
	 */
	public function getLpuSectionByMO($data, $withFilter) {
		$noLpuSecProfile = "";
		$filter = "";
		if($withFilter){
			if(!empty($data['LpuSectionProfile_id'])){
				$filter .= " AND LS.LpuSectionProfile_id = :LpuSectionProfile_id ";
			}
			else{
				$noLpuSecProfile = " inner join v_LpuUnit as LU  on LU.LpuUnit_id = LS.LpuUnit_id and LU.LpuUnitType_id = 1 ";

			}
		}
		$query = 	"SELECT
                        LS.LpuSection_id as \"LpuSection_id\"
						FROM v_LpuSection LS 
						". $noLpuSecProfile ."
						WHERE
						LS.lpu_id = :Lpu_hid
						LIMIT 1
						". $filter;
		//echo getDebugSQL($query, $data);die();
		$LpuSection =  $this->queryResult($query, $data);

		return $LpuSection;
	}
	/**
	 *
	 * Пересохранение талона вызова
	 *
	 */
	public function resaveCmpCallCard($result, $CmpCallCard_id = null)
	{
		$procedure = 'p_CmpCallCard_ins';

		$exceptedFields = array('CmpCallCard_id');
		$result['pmUser_id'] = $result['pmUser_insID'];

		$genQuery = $this -> getParamsForSQLQuery($procedure, $result, $exceptedFields, false);
		$genQueryParams = $genQuery["paramsArray"];
		$genQuerySQL = $genQuery["sqlParams"];
		if(!isset($CmpCallCard_id) || is_null($CmpCallCard_id))
			$CmpCallCard_id = 'Res';
		$query = "
						select
						    Res as \"CmpCallCard_id\",
						    Error_Code as \"Error_Code\",
						    Error_Message as \"Error_Msg\"
						from ".$procedure." (
						CmpCallCard_id := ".$CmpCallCard_id.",
						$genQuerySQL
						)
				";

		$queryParams = $genQueryParams;

		//echo(getDebugSql($query, $queryParams)); die;

		$res = $this->db->query( $query, $queryParams );

		if(isset($res) && is_object($res)) {
			$resArr = $res->result('array');

			$this->load->model('CmpCallCard_model', 'CmpCallCard_model');

			$eventParams = array(
				"CmpCallCard_id" => $resArr[0]["CmpCallCard_id"],
				"CmpCallCardEventType_Code" => 1,
				"CmpCallCardEvent_Comment" => '',
				"pmUser_id" => $result["pmUser_id"]
			);

			$this->CmpCallCard_model->setCmpCallCardEvent( $eventParams );
		}else{
			return false;
		}
		return $res;
	}
	/**
	 * Загрузка списка МО
	 * @param $data
	 * @return bool
	 */
	function loadCmpCallCardEventType($data)
	{
		$query = 'select
				CCCET.CmpCallCardEventType_id as \"CmpCallCardEventType_id\",
				CCCET.CmpCallCardEventType_Name as \"CmpCallCardEventType_Name\",
				CCCET.CmpCallCardEventType_Code as \"CmpCallCardEventType_Code\"
			from
				v_CmpCallCardEventType CCCET 

			where
				CCCET.CmpCallCardEventType_IsKeyEvent = 2';
		return $this->queryResult($query, $data);
	}

	/**
	 * Передача вызова на другую подстанцию
	 */
	function sendCmpCallCardToLpuBuilding($data)
	{
		if(empty($data['CmpCallCard_id']) || empty($data['LpuBuilding_id'])) return false;

		//Регистрация события передачи вызова
		$eventParams = array(
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"CmpCallCardEventType_Code" => 27,
			"CmpCallCardEvent_Comment" => $data['LpuBuilding_id'],
			"pmUser_id" => $data["pmUser_id"]
		);
		$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
		$this->CmpCallCard_model->setCmpCallCardEvent( $eventParams );

		$updateParams = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		return $this->swUpdate('CmpCallCard', $updateParams, false);
	}
	/**
	 * Получение вызовов под контролем
	 */
	public function loadCallsUnderControlList($data){
		if(empty($data["pmUser_id"]) || empty($data['Lpu_id']) )
			return false;

		$arrFilterStr = array();
		$arrFilterStr[] = ' (1=1) ';
		if($data['filter'])
			$arrFilter = json_decode($data['filter'], true);
		if(isset($arrFilter) && is_array($arrFilter) && count($arrFilter) > 0) {
			foreach ($arrFilter as $one_filter) {
				if (!empty($one_filter['value']) && $one_filter['value'] != '')
					$arrFilterStr[] = " tabl." . $one_filter['property'] . " ILIKE '%" . $one_filter['value'] . "%' ";
			}
		}
		$filter = array();
		$queryParams = array();
		//$filter[] = "CCC.pmUser_insID = :pmUser_id";
		//$filter[] = "CCC.CmpCallCardStatusType_id in (1,2,4,19,20)";
		$queryParams["pmUser_id"] = $data["pmUser_id"];
		$queryParams[ 'CmpLpu_id' ] = $data['Lpu_id'] ;

		//костыль для казахстана
		$table = "CCLC";
		$korp_select = "case when CCLC.Korpus is not null then ', к.'||CCLC.Korpus else '' end";
		$ccl_extra = "CCLC.CmpCloseCard_IsExtra";
		$reason = "CCLC.CallPovod_id";
		if(getRegionNick() == 'kz'){
			$table = 'CCC';
			$korp_select = '';
			$ccl_extra = "CCC.CmpCallCard_IsExtra";
			$reason = "CCC.CmpReason_id";
		}

		$fields = "";
		// ищем по талону вызова
		// пациент
		$fields .= " ,COALESCE( CCC.Person_SurName, PS.Person_Surname, '') || ' ' || COALESCE(rtrim(CCC.Person_FirName), PS.Person_Firname, ' ' )|| ' ' || COALESCE( rtrim(CCC.Person_SecName), PS.Person_Secname, ' ') as Person_FIO ";
		// возраст
		$fields .= "
			,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 OR COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay, 0) !=0 ) THEN
				CASE WHEN DATEDIFF('minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  ) > 12 THEN

					to_char (DATEDIFF( 'year',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  )  ) || ' лет'

				ELSE
					CASE WHEN DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  ) <=30 THEN

						 to_char (DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  ) ) || ' дн.'

					ELSE
						 to_char (DATEDIFF( 'minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  )  ) || ' мес.'

					END
				END
			 ELSE
				CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
				ELSE  to_char (CCC.Person_Age ) || ' лет'
				END
			 END
			 as personAgeText
		";
		// адрес
		$fields .= "
			,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
			else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
			else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
			case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
			case when Town.KLTown_FullName is not null then
				case when City.KLCity_Name is not null then ', ' else '' end
				 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

			end||

			case when Street.KLStreet_FullName is not null then
				case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
				', '||Street.KLStreet_FullName  end
			else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
			end ||

			case when SecondStreet.KLStreet_FullName is not null then
				case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
				', '||SecondStreet.KLStreet_FullName end
				else ''
			end ||

			case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
			case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
			case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
			case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
			case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
			end as Adress_Name
		";
		// тип вызова
		$fields .= ", RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as CmpCallType_Name";

		// вид вызова
		$fields .= ", case when COALESCE(CCC.CmpCallCard_IsExtra,1) = 1 then 'экстренный' else 'неотложный' end as CmpCallCard_IsExtraText";
		// повод
		$fields .= ", CR.CmpReason_Code || '. ' || CR.CmpReason_Name as CmpReason_Name";
		// подразделение
		$fields .= ", LB.LpuBuilding_Name as LpuBuilding_Name";

		if(!empty($data['Person_Age_From'])){
			$filter[] = ":Person_Age_From <= CCC.Person_Age";
			$queryParams[ 'Person_Age_From' ] = $data['Person_Age_From'] ;
		}
		if(!empty($data['Person_Age_To'])){
			$filter[] = ":Person_Age_To >= CCC.Person_Age";
			$queryParams[ 'Person_Age_To' ] = $data['Person_Age_To'] ;
		}

		//по воходящим в район/область подразделениям
		if(!empty($data['KLAreaLevel_id']) && !empty($data['KLCity_id'])&& $data['KLAreaLevel_id']<3){
			if($data['KLAreaLevel_id'] == 1){
				$filter[] = "(pArea.KLArea_pid = :KLArea_id or Area.KLArea_pid = :KLArea_id)";
			}
			else{
				$filter[] = "Area.KLArea_pid = :KLArea_id";
			}
			$queryParams[ 'KLArea_id' ] = $data['KLCity_id'];
		}
		else{
			//по населенному пункту
			if( !empty($data['KLTown_id']) ){
				$filter[] = "CCC.KLTown_id = :KLTown_id";
				$queryParams[ 'KLTown_id' ] = $data['KLTown_id'] ;
				//если региона нет тогда нас пункт не относится к городу
				if( !empty($data['KLSubRgn_id']) ){
					$filter[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
					$queryParams[ 'KLSubRgn_id' ] = $data['KLSubRgn_id'] ;
				}elseif ( !empty($data['KLCity_id']) ) {
					$filter[] = "CCC.KLCity_id = :KLCity_id";
					$queryParams[ 'KLCity_id' ] = $data['KLCity_id'] ;
				}
			} elseif ( !empty($data['KLCity_id'])) {
				$filter[] = "CCC.KLCity_id = :KLCity_id";
				$queryParams[ 'KLCity_id' ] = $data['KLCity_id'] ;
				//если город верхнего уровня
				if( !empty($data['KLSubRgn_id']) ){
					$filter[] = "CCC.KLSubRgn_id = :KLSubRgn_id";
					$queryParams[ 'KLSubRgn_id' ] = $data['KLSubRgn_id'] ;
				}
			}
		}

		/*if(!empty($data['begDate']) && !empty($data['endDate'])){
			$filter[] = 'CCC.CmpCallCard_prmDT BETWEEN :begDate AND :endDate';
			$begDate = date_create($data["begDate"]);
			$endDate = date_create($data['endDate']);
			$queryParams['begDate'] = $begDate->format('Y-m-d').' ' . ((!empty($data['begTime']) ? $data['begTime'] : ' 00:00'));
			$queryParams['endDate'] = $endDate->format('Y-m-d').' ' . ((!empty($data['endTime']) ? $data['endTime'] : ' 23:59'));
		}*/
		if(!empty($data['CmpCallCardStatusType_id'])){
			$filter[] = "CCC.CmpCallCardStatusType_id = :CmpCallCardStatusType_id ";
			$queryParams[ 'CmpCallCardStatusType_id' ] = $data['CmpCallCardStatusType_id'] ;
		}

		if(!empty($data['useLdapLpuBuildings']) && $data['useLdapLpuBuildings'] == 'true'){
			$lpuBuildingsWorkAccess = null;
			// здесь мы получаем список доступных подстанций для работы из лдапа
			$user = pmAuthUser::find($_SESSION['login']);
			$settings = @unserialize($user->settings);

			if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
				$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
			}

			if ( !(empty( $lpuBuildingsWorkAccess)) ) {
				if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
				// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
				$lpuFilter ="CCC.LpuBuilding_id in (";
				foreach ($lpuBuildingsWorkAccess as &$value) {
					$lpuFilter .= $value.',';
				}
				$filter[] = substr($lpuFilter, 0, -1).')';
			}
		}

		if(!empty($data['MedPersonal_id'])){
			$filter[] = "PMUins.pmUser_Medpersonal_id = :MedPersonal_id";

			$queryParams[ 'MedPersonal_id' ] = $data['MedPersonal_id'] ;
		}
		if(!empty($data['UAD_id'])){
			$filter[] = "UAD.UnformalizedAddressDirectory_id = :UAD_id";
			$queryParams[ 'UAD_id' ] = $data['UAD_id'] ;
		}
		if(!empty($data['EmergencyTeam_Num'])){
			$filter[] = "ET.EmergencyTeam_Num = :EmergencyTeam_Num";
			$queryParams[ 'EmergencyTeam_Num' ] = $data['EmergencyTeam_Num'] ;
		}
		if(!empty($data['LpuBuilding_id'])){
			$filter[] = ":LpuBuilding_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.LpuBuilding_id else CCC.LpuBuilding_id end";
			$queryParams[ 'LpuBuilding_id' ] = $data['LpuBuilding_id'] ;
		}elseif(!empty($data['session']['CurArmType']) && $data['session']['CurArmType'] == 'smpdispatchcall'){
			$smpUnitsNested = $this->loadSmpUnitsNested($data);
			$operDepartament = $this->getOperDepartament($data);
			$queryParams[ 'LpuBuilding_pid' ] = $operDepartament[ 'LpuBuilding_pid' ] ;

			if ( !(empty( $smpUnitsNested)) ) {
				$lpuFilter ="(CCC.LpuBuilding_id in (";
				foreach ($smpUnitsNested as &$value) {
					$lpuFilter .= $value['LpuBuilding_id'].',';
				}

				/* #111030 пост 14
				 * Вызов, переданный в службу НМП, должен отображаться только
				 * в журнале вызовов той МО, где был принят вызов.
				 * В журнале вызовов той МО, куда он (этот вызов) был перенаправлен, он не должен отображаться - по той причине,
				 * что он не имеет отношения к службам СМП этой МО.
				 */
				$filter[] = substr($lpuFilter, 0, -1).') or CCC.LpuBuilding_id = :LpuBuilding_pid or CCC.Lpu_id = :CmpLpu_id )';
				//$filter[] = substr($lpuFilter, 0, -1).') or CCC.Lpu_ppdid = :CmpLpu_id or CCC.LpuBuilding_id = :LpuBuilding_pid or CCC.Lpu_id = :CmpLpu_id )';

			}
		}else{
			/* #116450
			 * Если форма «Журнал вызовов» открыта из любого АРМ,
			 * кроме АРМ Диспетчера по приему вызовов, то отображаются вызовы, переданные на те подстанции,
			 * которые выбраны на форме "Выбор подстанции для управления"
			 */
			$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
			$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
			$lpuppdFilter = '';
			$lpuFilter ="(CCC.LpuBuilding_id in (";
			foreach ($arrayIdSelectSmp as &$value) {

				if($value == 0){
					$lpuppdFilter = ' or (CCC.lpu_id = :CmpLpu_id and CCC.Lpu_ppdid is not null)';
				}

				$lpuFilter .= $value.',';
			}

			$filter[] = substr($lpuFilter, 0, -1).') ' . $lpuppdFilter . ' )';

		}

		if(!empty($data['Sex_id'])){
			$filter[] = ":Sex_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.Sex_id else CCC.Sex_id end";
			$queryParams[ 'Sex_id' ] = $data['Sex_id'] ;
		}
		if(!empty($data['CmpCallType_id'])){
			$filter[] = ":CmpCallType_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.CallType_id else CCC.CmpCallType_id end";
			$queryParams[ 'CmpCallType_id' ] = $data['CmpCallType_id'] ;
		}
		if(!empty($data['CmpReason_id'])){
			$filter[] = ":CmpReason_id =
			case when (CCLC.CmpCallCard_id is not null) then CCLC.CallPovod_id else CCC.CmpReason_id end";
			$queryParams[ 'CmpReason_id' ] = $data['CmpReason_id'] ;
		}

		if(!empty($data['CmpResult_id'])){
			$filter[] = "CCLC.CmpResult_id = :CmpResult_id";
			$queryParams[ 'CmpResult_id' ] = $data['CmpResult_id'] ;
		}

		if(!empty($data['KLStreet_id'])){
			$filter[] = "CCC.KLStreet_id = :KLStreet_id";
			$queryParams[ 'KLStreet_id' ] = $data['KLStreet_id'] ;
		}

		if(!empty($data['UnformalizedAddressDirectory_id'])){
			$filter[] = "CCC.UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id";
			$queryParams[ 'UnformalizedAddressDirectory_id' ] = $data['UnformalizedAddressDirectory_id'] ;
		}

		if(!empty($data['CmpCallCard_Dom'])){
			$filter[] = "CCC.CmpCallCard_Dom = :CmpCallCard_Dom";
			$queryParams[ 'CmpCallCard_Dom' ] = $data['CmpCallCard_Dom'] ;
		}

		if(!empty($data['CmpCallCard_Korp'])){
			$filter[] = "CCC.CmpCallCard_Korp = :CmpCallCard_Korp";
			$queryParams[ 'CmpCallCard_Korp' ] = $data['CmpCallCard_Korp'] ;
		}

		if(!empty($data['CmpCallCard_Kvar'])){
			$filter[] = "CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
			$queryParams[ 'CmpCallCard_Kvar' ] = $data['CmpCallCard_Kvar'] ;
		}

		//$filter[] = "CCCST.CmpCallCardStatusType_Code in(1,3)";
		$filter[] = "DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 100";

		$this->load->model("Options_model", "opmodel");
		$o = $this->opmodel->getOptionsGlobals($data);
		$g_options = $o['globals'];
		if(isset($g_options["smp_call_time_format"]) && $g_options["smp_call_time_format"] == '2')
			$formatDateinList = 'varchar(5)';
		else
			$formatDateinList = 'varchar(8)';

		$filterACRulesSTR = '';
		$ActiveCallRules = $this -> loadActiveCallRules($data);
		if(!empty($ActiveCallRules)) {
			$filterACRule = array();
			$filterACRules = array();
			$filterACRulesSTR = 'AND (';
			foreach ($ActiveCallRules as $rule) {
				unset($filterACRule);
				$filterACRule = array();
				$filterACRuleSTR = ' (';
				if (isset($rule['ActiveCallRule_From']))
					$filterACRule[] = ' CCC.Person_Age >= ' . $rule['ActiveCallRule_From'];
				if (isset($rule['ActiveCallRule_To']))
					$filterACRule[] = ' COALESCE(CCC.Person_Age,0) < ' . $rule['ActiveCallRule_To'];

				if (isset($rule['ActiveCallRule_UrgencyFrom']))
					$filterACRule[] = ' CCC.CmpCallCard_Urgency >= ' . $rule['ActiveCallRule_UrgencyFrom'];
				if (isset($rule['ActiveCallRule_UrgencyTo']))
					$filterACRule[] = ' CCC.CmpCallCard_Urgency <= ' . $rule['ActiveCallRule_UrgencyTo'];
				if (isset($rule['ActiveCallRule_WaitTime']))
					$filterACRule[] = ' DATEDIFF(\'minute\', COALESCE(ActCall.ActCallDT, CCC.CmpCallCard_prmDT),dbo.tzGetDate())  >= ' . $rule['ActiveCallRule_WaitTime'];
				$filterACRuleSTR .= implode(" AND ", $filterACRule) . ' )';
				$filterACRules[] = $filterACRuleSTR;
			}
			$filterACRulesSTR .= implode(" OR ", $filterACRules) . ' or CCC.CmpCallCard_isControlCall = 2 )';
		}

		$query = "
			-- addit with
			with CmpCallCardArray as (
				select CmpCallCard_id as CmpCallCard_id, Lpu_ppdid as Lpu_ppdid
				from v_CmpCallCard CCC 

				left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

				LEFT JOIN LATERAL(

							select
								CCCAC.CmpCallCard_prmDT as ActCallDT
							from
								v_CmpCallCard CCCAC 

							where
								CCCAC.CmpCallCard_rid = CCC.CmpCallCard_id
							--AND
								--CCCAC.CmpCallCard_isActiveCall = 2
							order by
								CCCAC.CmpCallCard_prmDT desc
                            limit 1
						) as ActCall ON TRUE
				where
				" . implode(" AND ", $filter) . " " . $filterACRulesSTR . "
			),
			activeEventArray as (
				select
					CCCE.CmpCallCard_id as CmpCallCard_id,
					CCCET.CmpCallCardEventType_Name as CmpCallCardEventType_Name,
					CCCET.CmpCallCardEventType_Code as CmpCallCardEventType_Code,
					CCCE.CmpCallCardEvent_updDT as CmpCallCardEvent_updDT,
					ETS.EmergencyTeamStatus_id as EmergencyTeamStatus_id,
					ETS.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
					ETS.EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
				from
					v_CmpCallCardEvent CCCE 

					inner join CmpCallCardArray CCCA on CCCA.CmpCallCard_id = CCCE.CmpCallCard_id
					LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

					LEFT JOIN v_EmergencyTeamStatusHistory ETSH  on CCCE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

					LEFT JOIN v_EmergencyTeamStatus ETS  on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id

				where
					CCCET.CmpCallCardEventType_IsKeyEvent = 2
			)
			-- end addit with

			SELECT
			--select
			 *
			--end select
			FROM
			--from
			(

				SELECT

						CCC.CmpCallCard_id as \"CmpCallCard_id\"
						,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') || ' ' || to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'HH24:MI:SS') as \"CmpCallCard_prmDate\"


						,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') as \"CmpCallCard_prmDateStr\"

						,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
						,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
						,CCC.CmpCallCard_Urgency as \"CmpCallCard_Urgency\"
						{$fields}
						,CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
						,to_char (COALESCE(CCC.Person_BirthDay, PS.Person_BirthDay), 'DD Mon YYYY HH24:MI:SS') as \"Person_Birthday\"

						,COALESCE(CCC.CmpCallCard_isControlCall,1) as \"CmpCallCard_isControlCall\"
						,CCCST.CmpCallCardStatusType_Name as \"CmpCallCardStatusType_Name\"
						,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
						,CASE WHEN CCC.CmpCallCardStatusType_id = 19 THEN
							activeEvent.CmpCallCardEventType_Name || ' до ' || to_char(CCC.CmpCallCard_storDT, 4)||' '||to_char(cast(CmpCallCard_storDT as time(6)))
							ELSE activeEvent.CmpCallCardEventType_Name
						END as \"CmpCallCardEventType_Name\"

						,COALESCE(CLET.EmergencyTeam_Num,ET.EmergencyTeam_Num,'') as \"EmergencyTeam_Num\"
						,CCR.CmpCallRecord_id as \"CmpCallRecord_id\"
						,to_char (CCCD.Duplicate_Count) || ' / ' || to_char (CCCAC.ActiveCall_Count) as \"DuplicateAndActiveCall_Count\"
						,COALESCE(CmpResult.CmpResult_Name,CRCB.ComboName) as \"CmpResult_Name\"
						,CCLC.CmpCloseCard_id as \"CmpCloseCard_id\"
						,CASE
							WHEN ". implode(" OR ", $filterACRules) ." THEN 1
							ELSE 2
						END
						as \"CmpGroup_id\"
				FROM

						v_CmpCallCard CCC 

						LEFT JOIN LATERAL (

						select
							CmpCallCardEvent_updDT as CmpCallCardEvent_updDT,
							CmpCallCardEventType_Name as CmpCallCardEventType_Name,
							CmpCallCardEventType_Code as CmpCallCardEventType_Code,
							EmergencyTeamStatus_id as EmergencyTeamStatus_id,
							EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
							EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
						from activeEventArray
						where CmpCallCard_id = CCC.CmpCallCard_id
						ORDER BY CmpCallCardEvent_updDT desc
						limit 1
						  ) as activeEvent ON TRUE
						left join {$this->schema}.v_CmpCloseCard CCLC  on CCLC.CmpCallCard_id = CCC.CmpCallCard_id

						LEFT JOIN v_CmpResult as CmpResult on CCLC.CmpResult_id = CmpResult.CmpResult_id
						left join v_CmpCallCard112 CCC112  on CCC112.CmpCallCard_id = CCC.CmpCallCard_id

						left join v_PersonState PS  on PS.Person_id = CCC.Person_id

						left join v_LpuBuilding LB  on LB.LpuBuilding_id = CCC.LpuBuilding_id

						left join v_LpuBuilding CLLB  on CLLB.LpuBuilding_id = CCLC.LpuBuilding_id

						left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id

						left join v_EmergencyTeam CLET  on CLET.EmergencyTeam_id = {$table}.EmergencyTeam_id

						left join v_MedService MS  on MS.MedService_id = CCC.MedService_id

						left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

						left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

						left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

						left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id


						left join v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLSubRgn_id

						left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLSubRgn_id



						left join v_KLArea Area  on Area.KLArea_id = COALESCE(CCC.KLTown_id,CCC.KLCity_id)


						left join v_KLArea pArea  on pArea.KLArea_id = Area.KLArea_pid


						left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

						left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id


						left join v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond

						left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id


						left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id


						left join v_KLSubRgn SRGNCityCl  on SRGNCityCl.KLSubRgn_id = CCLC.City_id

						left join v_KLSubRgn SRGNTownCl  on SRGNTownCl.KLSubRgn_id = CCLC.Town_id


						left join v_KLCity CLCity  on CLCity.KLCity_id = CCLC.City_id

						left join v_KLTown CLTown  on CLTown.KLTown_id = CCLC.Town_id

						LEFT JOIN KLArea KL_AR  on KL_AR.KLArea_id = CCLC.Area_id

						left join v_KLStreet CLStreet  on CLStreet.KLStreet_id = CCLC.Street_id

						left join v_KLSocr CLsocrStreet  on CLStreet.KLSocr_id = CLsocrStreet.KLSocr_id


						left join v_UnformalizedAddressDirectory CLUAD  on CLUAD.UnformalizedAddressDirectory_id = {$table}.UnformalizedAddressDirectory_id


						left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id

						left join v_CmpCallType CCLT  on CCLT.CmpCallType_id = CCLC.CallType_id

						left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

						left join v_CmpReason CCLR  on CCLR.CmpReason_id = {$reason}

						left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id


						left join v_CmpCallRecord CCR  on CCC.CmpCallCard_id = CCR.CmpCallCard_id


						LEFT JOIN LATERAL (

							select
								CLCCB.ComboName
							from {$this->schema}.v_CmpCloseCardRel CLCR
							left join {$this->comboSchema}.v_CmpCloseCardCombo CLCCB  on CLCCB.CmpCloseCardCombo_id = CLCR.CmpCloseCardCombo_id

							left join {$this->comboSchema}.v_CmpCloseCardCombo pCLCCB  on pCLCCB.CmpCloseCardCombo_id = CLCCB.Parent_id

							where CLCR.CmpCloseCard_id = CCLC.CmpCloseCard_id and pCLCCB.CmpCloseCardCombo_Code = 223
							limit 1
						) CRCB ON TRUE

						LEFT JOIN LATERAL(

							select
								CCCA.CmpCallCardAcceptor_Code as CmpCallCardAcceptor_Code
							from
								v_CmpUrgencyAndProfileStandart CUPS 

							left join v_CmpCallCardAcceptor CCCA  on CCCA.CmpCallCardAcceptor_id = CUPS.CmpCallCardAcceptor_id

							where
								CUPS.CmpReason_id = CCC.CmpReason_id
								AND (COALESCE(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,150) > CCC.Person_Age)

								AND COALESCE(CUPS.Lpu_id,0) in (:CmpLpu_id)
                            limit 1

						) CUAPS ON TRUE

						LEFT JOIN LATERAL(

							select
								CCCS.pmUser_insID as pmUser_insID
							from
								v_CmpCallCardStatus CCCS 

							where CCCS.cmpcallcard_id = CCC.cmpcallcard_id and CCCS.CmpCallCardStatusType_id != 20
							limit 1
						) as PMU ON TRUE
						left join v_pmUser PMUins on PMU.pmUser_insID = PMUins.pmUser_id
						LEFT JOIN LATERAL(

							select
								CCCAC.CmpCallCard_prmDT as ActCallDT
							from
								v_CmpCallCard CCCAC 

							where
								CCCAC.CmpCallCard_rid = CCC.CmpCallCard_id
							AND
								CCCAC.CmpCallCard_isActiveCall = 2
							order by
								CCCAC.CmpCallCard_prmDT desc
                            limit 1
						) as ActCall
						LEFT JOIN LATERAL(

							select
								COUNT(CCCDouble.CmpCallCard_id) as Duplicate_Count
							from
								v_CmpCallCard CCCDouble 

							left join v_CmpCallCardStatusType CCCSTDouble  on CCCSTDouble.CmpCallCardStatusType_id = CCCDouble.CmpCallCardStatusType_id

							where
								CCCDouble.CmpCallCard_rid = CCC.CmpCallCard_id
								and CCCSTDouble.CmpCallCardStatusType_Code = 9
								and COALESCE(CCCDouble.CmpCallCard_IsActiveCall, 1) != 2
						) CCCD ON TRUE
						LEFT JOIN LATERAL(

							select
								COUNT(CCCActiveCall.CmpCallCard_id) as ActiveCall_Count
							from
								v_CmpCallCard CCCActiveCall 

							where
								CCCActiveCall.CmpCallCard_rid = CCC.CmpCallCard_id
								and COALESCE(CCCActiveCall.CmpCallCard_IsActiveCall, 1) = 2
						) CCCAC ON TRUE

				WHERE
						" . implode(" AND ", $filter) . "
						" . $filterACRulesSTR . "
						 AND CCT.CmpCallType_Code != 14
			) as tabl
				--end from
				WHERE
				--where
				" . implode(" AND ", $arrFilterStr) . "
				--end where
				order by
					--order by
					tabl.CmpCallCard_prmDate DESC
					-- end order by

			";
			//echo getDebugSQL($query, $queryParams);die();
			//var_dump(getDebugSQL(getLimitSQLPH($query, $data['start'], $data['limit'], 'DISTINCT'), $queryParams)); exit;
			//echo getDebugSQL(getLimitSQLPH($query, 0, 100), $queryParams);die();
			//$res = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);

			$res = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $queryParams);

			$result_count = $this->db->query(getCountSQLPH($query), $queryParams);

		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}

		//}

		if(isset($res) && is_object($res)){
			$resArr = $res->result('array');

			$response = array(
				'data' => $resArr,
				'totalCount' => $count
			);
		}
		else{
			$response = array(
				'data' => array(),
				'totalCount' => 0
			);
		}
		return $response;
	}
	/**
	 * Загрузка списка правил вызовов на контроле
	 * @param $data
	 * @return array
	 */
	function loadActiveCallRules($data)
	{

		$operDepartament = $this->getOperDepartament($data);

		$query = '
				select
					ACR.ActiveCallRule_id as "ActiveCallRule_id",
					ACR.LpuBuilding_id as "LpuBuilding_id",
					ACR.ActiveCallRule_From as "ActiveCallRule_From",
					ACR.ActiveCallRule_To as "ActiveCallRule_To",
					ACR.ActiveCallRule_UrgencyFrom as "ActiveCallRule_UrgencyFrom",
					ACR.ActiveCallRule_UrgencyTo as "ActiveCallRule_UrgencyTo",
					ACR.ActiveCallRule_WaitTime as "ActiveCallRule_WaitTime"
				from dbo.ActiveCallRule ACR 

				where
					ACR.LpuBuilding_id = :LpuBuilding_id';

		$res = $this->queryResult($query, array('LpuBuilding_id' => $operDepartament['LpuBuilding_pid']));

		return $res;
	}
	/**
	 * Сохранение правила контроля вызовов с превышением времени назначения на бригаду
	 *
	 * @param array $data
	 * @return array|null
	 */
	public function saveActiveCallRules($data){

		if (empty($data['LpuBuilding_id'])) {
			return;
		}

		$params = array(
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'ActiveCallRule_id' => (!empty($data['ActiveCallRule_id']) || $data['ActiveCallRule_id']==0) ? $data['ActiveCallRule_id'] : null,
			'ActiveCallRule_From' => (!empty($data['ActiveCallRule_From']) || $data['ActiveCallRule_From']==0) ? $data['ActiveCallRule_From'] : null,
			'ActiveCallRule_To' => (!empty($data['ActiveCallRule_To']) || $data['ActiveCallRule_To']==0) ? $data['ActiveCallRule_To'] : null,
			'ActiveCallRule_UrgencyFrom' => (!empty($data['ActiveCallRule_UrgencyFrom']) || $data['ActiveCallRule_UrgencyFrom']==0) ? $data['ActiveCallRule_UrgencyFrom'] : null,
			'ActiveCallRule_UrgencyTo' => (!empty($data['ActiveCallRule_UrgencyTo']) || $data['ActiveCallRule_UrgencyTo']==0) ? $data['ActiveCallRule_UrgencyTo'] : null,
			'ActiveCallRule_WaitTime' => (!empty($data['ActiveCallRule_WaitTime']) || $data['ActiveCallRule_WaitTime']==0) ? $data['ActiveCallRule_WaitTime'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		if($this->checkActiveCallRuleAnalog($data, $params))
			return array( array('success' => false, 'Error_Msg' => 'Правило пересекается с другим правилом. Измените параметры правила и повторите действие.') );

		if(isset($data['ActiveCallRule_id'])){
			$proc = 'p_ActiveCallRule_upd';
			$p = 'ActiveCallRule_id = :ActiveCallRule_id,';
			$params['ActiveCallRule_id'] = $data['ActiveCallRule_id'];
			$h = $data['ActiveCallRule_id'];
		}
		else{
			$proc = 'p_ActiveCallRule_ins';
			$p = 'ActiveCallRule_id := ActiveCallRule_id,';
			$h = 'ActiveCallRule_id';
		}

		$sql = "
            SELECT 
                $h as \"ActiveCallRule_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			FROM $proc (
				$p
				LpuBuilding_id := :LpuBuilding_id,
				ActiveCallRule_From := :ActiveCallRule_From,
				ActiveCallRule_To := :ActiveCallRule_To,
				ActiveCallRule_UrgencyFrom := :ActiveCallRule_UrgencyFrom,
				ActiveCallRule_UrgencyTo := :ActiveCallRule_UrgencyTo,
				ActiveCallRule_WaitTime := :ActiveCallRule_WaitTime,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($sql,$params)->row_array();

		return $result;

	}
	/**
	 * Сохранение правила контроля вызовов с превышением времени назначения на бригаду
	 *
	 * @param $ActiveCallRule_id
	 * @param $LpuBuilding_id
	 * @return array|null
	 */
	public function getActiveCallRuleEdit($ActiveCallRule_id, $LpuBuilding_id){

		if (empty($ActiveCallRule_id) || empty($LpuBuilding_id)) {
			return;
		}

		$sql = "
			SELECT
			
					ACR.ActiveCallRule_id as \"ActiveCallRule_id\",
					ACR.LpuBuilding_id as \"LpuBuilding_id\",
					ACR.ActiveCallRule_From as \"ActiveCallRule_From\",
					ACR.ActiveCallRule_To as \"ActiveCallRule_To\",
					ACR.ActiveCallRule_UrgencyFrom as \"ActiveCallRule_UrgencyFrom\",
					ACR.ActiveCallRule_UrgencyTo as \"ActiveCallRule_UrgencyTo\",
					ACR.ActiveCallRule_WaitTime as \"ActiveCallRule_WaitTime\"
				FROM dbo.ActiveCallRule ACR 

				WHERE
					ACR.LpuBuilding_id = :LpuBuilding_id
					AND ACR.ActiveCallRule_id = :ActiveCallRule_id
                LIMIT 1
		";

		return $this->db->query($sql, array(
			'ActiveCallRule_id' => $ActiveCallRule_id,
			'LpuBuilding_id' => $LpuBuilding_id
		))->result_array();
	}
	/**
	 * Проверка на пересечение правила контроля вызовов с превышением времени назначения на бригаду
	 *
	 * @param array $data
	 * @param array $params
	 * @return bool
	 */
	public function checkActiveCallRuleAnalog($data, $params){
		$filterACRule = array();
		if (empty($params['LpuBuilding_id'])) {
			return false;
		}
		else{
			$filterACRule[] = "ACR.LpuBuilding_id = :LpuBuilding_id ";
		}
		if(isset($params['ActiveCallRule_From']))
			$filterACRule[] = " (
									(COALESCE(ACR.ActiveCallRule_From, '') = '' OR ACR.ActiveCallRule_From <= ".$params['ActiveCallRule_From'].") 

									AND (COALESCE(ACR.ActiveCallRule_To, '') = '' OR ACR.ActiveCallRule_To >= ".$params['ActiveCallRule_From'].") 

								)";
		if(isset($params['ActiveCallRule_To']))
			$filterACRule[] = " (
									(COALESCE(ACR.ActiveCallRule_From, '') = '' OR ACR.ActiveCallRule_From <= ".$params["ActiveCallRule_To"].") 

									AND (COALESCE(ACR.ActiveCallRule_To, '') = '' OR ACR.ActiveCallRule_To >= ".$params["ActiveCallRule_To"].") 

								)";

		if(isset($params['ActiveCallRule_UrgencyFrom']))
			$filterACRule[] = " (
									(COALESCE(ACR.ActiveCallRule_UrgencyFrom, '') = '' OR ACR.ActiveCallRule_UrgencyFrom <= ".$params["ActiveCallRule_UrgencyFrom"].") 

									AND (COALESCE(ACR.ActiveCallRule_UrgencyTo, '') = '' OR ACR.ActiveCallRule_UrgencyTo >= ".$params["ActiveCallRule_UrgencyFrom"].") 

								)";
		if(isset($params['ActiveCallRule_UrgencyTo']))
			$filterACRule[] = " (
									(COALESCE(ACR.ActiveCallRule_UrgencyFrom, '') = '' OR ACR.ActiveCallRule_UrgencyFrom <= ".$params["ActiveCallRule_UrgencyTo"].") 

									AND (COALESCE(ACR.ActiveCallRule_UrgencyTo, '') = '' OR ACR.ActiveCallRule_UrgencyTo >= ".$params["ActiveCallRule_UrgencyTo"].") 

								)";
		if(isset($params['ActiveCallRule_id']))
			$filterACRule[] = " ACR.ActiveCallRule_id <> :ActiveCallRule_id ";

		/*elseif(!isset($params['ActiveCallRule_UrgencyFrom'])){
			$filterACRule[] = ' ( ACR.ActiveCallRule_UrgencyFrom IS NOT NULL OR ACR.ActiveCallRule_UrgencyTo IS NOT NULL )';
		}*/

		$sql = "
			SELECT			
					ACR.ActiveCallRule_id as \"ActiveCallRule_id\",
					ACR.LpuBuilding_id as \"LpuBuilding_id\",
					ACR.ActiveCallRule_From as \"ActiveCallRule_From\",
					ACR.ActiveCallRule_To as \"ActiveCallRule_To\",
					ACR.ActiveCallRule_UrgencyFrom as \"ActiveCallRule_UrgencyFrom\",
					ACR.ActiveCallRule_UrgencyTo as \"ActiveCallRule_UrgencyTo\",
					ACR.ActiveCallRule_WaitTime as \"ActiveCallRule_WaitTime\"
				FROM dbo.ActiveCallRule ACR 

				WHERE
					".implode(" AND ", $filterACRule)."
                LIMIT 1
		";
		//echo getDebugSQL($sql, $data);die();
		$res = $this->db->query($sql, $params)->result_array();
		return (!empty($res));
	}
	/**
	 * Получение вызовов под контролем
	 * @param $data
	 * @return array
	 */
	public function checkNeedActiveCall($data){
		if(empty($data["pmUser_id"]) || empty($data['Lpu_id']) )
			return false;

		$filter = array();
		$queryParams = array();
		$success = false;
		if(!empty($data['useLdapLpuBuildings']) && $data['useLdapLpuBuildings'] == 'true'){
			$lpuBuildingsWorkAccess = null;
			// здесь мы получаем список доступных подстанций для работы из лдапа
			$user = pmAuthUser::find($_SESSION['login']);
			$settings = @unserialize($user->settings);

			if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
				$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
			}

			if ( !(empty( $lpuBuildingsWorkAccess)) ) {
				if($lpuBuildingsWorkAccess[0]=='') return array( array( 'success' => false, 'Error_Msg' => 'Не настроен список доступных для работы подстанций' ) );
				// Отображаем только те вызовы, которые доступны подстанций для работы из лдапа (#85307)
				$lpuFilter ="CCC.LpuBuilding_id in (";
				foreach ($lpuBuildingsWorkAccess as &$value) {
					$lpuFilter .= $value.',';
				}
				$filter[] = substr($lpuFilter, 0, -1).')';
			}
		}

		if(empty($queryParams['CmpLpu_id'])){
			$queryParams['CmpLpu_id'] = $data['Lpu_id'];
		}

		$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
		$arrayIdSelectSmp = $this->EmergencyTeam_model4E->loadIdSelectSmp();
		$lpuppdFilter = '';
		$lpuFilter ="(CCC.LpuBuilding_id in (";
		foreach ($arrayIdSelectSmp as &$value) {

			if($value == 0){
				$lpuppdFilter = ' or (CCC.lpu_id = :CmpLpu_id and CCC.Lpu_ppdid is not null)';
			}

			$lpuFilter .= $value.',';
		}

		$lpuppdFilter .= ' or (CCC.CmpLpu_id is null and CCC.LpuBuilding_id is null)';

		$filter[] = substr($lpuFilter, 0, -1).') ' . $lpuppdFilter . ' )';

		$filter[] = "CCCST.CmpCallCardStatusType_Code in(1,3)";

		$filterACRulesSTR = '';
		$filterACRule = array();
		$ActiveCallRules = $this -> loadActiveCallRules($data);
		if(!empty($ActiveCallRules)){
			$filterACRules = array();
			$filterACRulesSTR = 'AND (';
			foreach ($ActiveCallRules as $rule){
				unset($filterACRule);
				$filterACRule = array();
				$filterACRuleSTR = ' (';
				if(isset($rule['ActiveCallRule_From']))
					$filterACRule[] = ' CCC.Person_Age >= '.$rule['ActiveCallRule_From'];
				if(isset($rule['ActiveCallRule_To']))
					$filterACRule[] = ' COALESCE(CCC.Person_Age,0) < '.$rule['ActiveCallRule_To'];

				if(isset($rule['ActiveCallRule_UrgencyFrom']))
					$filterACRule[] = ' CCC.CmpCallCard_Urgency >= '.$rule['ActiveCallRule_UrgencyFrom'];
				if(isset($rule['ActiveCallRule_UrgencyTo']))
					$filterACRule[] = ' CCC.CmpCallCard_Urgency <= '.$rule['ActiveCallRule_UrgencyTo'];
				if(isset($rule['ActiveCallRule_WaitTime']))
					$filterACRule[] = ' DATEDIFF(\'minute\', COALESCE(ActCall.ActCallDT, CCC.CmpCallCard_prmDT),dbo.tzGetDate())  >= '.$rule['ActiveCallRule_WaitTime'];
				$filterACRuleSTR .= implode(" AND ", $filterACRule) . ' )';
				$filterACRules[] = $filterACRuleSTR;
			}
			$filterACRulesSTR .= implode(" OR ", $filterACRules) . ' )';


			$query = "
				SELECT
					CCC.CmpCallCard_id as \"CmpCallCard_id\"
				FROM
					v_CmpCallCard CCC 

					left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

					LEFT JOIN LATERAL(

						select
							CCCAC.CmpCallCard_prmDT as ActCallDT
						from
							v_CmpCallCard CCCAC 

						where
							CCCAC.CmpCallCard_rid = CCC.CmpCallCard_id
						AND 
							CCCAC.CmpCallCard_isActiveCall = 2
						order by
							CCCAC.CmpCallCard_prmDT desc
                        limit 1
					) as ActCall ON TRUE
				WHERE
					" . implode(" AND ", $filter) . "
					".$filterACRulesSTR."
				order by
					CCC.CmpCallCard_prmDT DESC
                LIMIT 1
			";

			$res = $this->db->query($query, $queryParams);
			if (is_object($res)) {
				$res = $res->result('array');
				if (!empty($res)){
					$this -> setCmpCallCard_isTimeExceeded($res, $data['pmUser_id'], 2);
					$success = true;
				}
			}
		}
		$arr = array( array( 'success' => $success));
		return $arr;
	}

	/**
	 * Установка флага для карт в просроченном состоянии
	 * @param $cardsArray
	 * @return array|bool
	 */
	private function setCmpCallCard_isTimeExceeded( $cardsArray, $pmUser_id, $exceeded ) {
		foreach ($cardsArray as &$value) {
			//var_dump($value['CmpCallCard_id']); exit;
			$updateParams = array(
				'CmpCallCard_id' => $value['CmpCallCard_id'],
				'CmpCallCard_isTimeExceeded' => $exceeded,
				'pmUser_id' => $pmUser_id
			);
			$this->swUpdate('CmpCallCard', $updateParams, false);
		}

	}

	/**
	 * Установка флага для карт в просроченном состоянии
	 * @param $data
	 * @return array|bool
	 */
	public function setCmpCallCardToControl( $data ) {

		$updateParams = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_isControlCall' => $data['CmpCallCard_isControlCall'],
			'pmUser_id' => $data['pmUser_id']
		);
		$res = $this->swUpdate('CmpCallCard', $updateParams, false);

		if (empty($res)) {
			return false;
		} else {

			$eventParams = array(
				"CmpCallCard_id" =>  $data['CmpCallCard_id'],
				"CmpCallCardEventType_Code" => $data['CmpCallCard_isControlCall'] == 2 ? 29 : 30,
				"CmpCallCardEvent_Comment" => $data['CmpCallCard_isControlCall'] == 2 ? 'Вызов поставлен на контроль' : 'Вызов снят с контроля',
				"pmUser_id" => $data["pmUser_id"]
			);

			$this->load->model('CmpCallCard_model', 'CmpCallCard_model');

			$this->CmpCallCard_model->setCmpCallCardEvent( $eventParams );
		}

		return $res;
	}


	/**
	 * Загрузка РМ интерактивной карты
	 * @param $data
	 * @return array|bool
	 */
	public function loadSMPInteractiveMapWorkPlace( $data ) {
		$filter = array();
		$join = array();
		$withJoinCCC = array();
		$sqlArr = array();
		$lpuBuildingID = array();
		$lpuBuildingsWorkAccess = array();

		// здесь мы получаем список доступных подстанций для работы из лдапа
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);

		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$lpuBuildingsWorkAccess = $settings['lpuBuildingsWorkAccess'];
		}

		$regionNick = $_SESSION['region']['nick'];

		// Скрываем вызовы принятые в ППД
		$filter[] = "COALESCE(CCC.CmpCallCard_IsReceivedInPPD, 1) != 2";

		$sqlArr['Lpu_id'] = $data['session']['lpu_id'];


		if ( count( $lpuBuildingsWorkAccess) > 0 ) {
			if ($lpuBuildingsWorkAccess[0] == '') {
				return $this->createError(null, 'Не настроен список доступных для работы подстанций');
			}

			$lpuBuildingID = array_merge($lpuBuildingID, $lpuBuildingsWorkAccess);
		}
		elseif ( !empty( $data[ 'session' ][ 'CurMedService_id' ] ) ) {
			// Отображаем только те вызовы, которые переданы на эту подстанцию (#38949)
			// Добавлено: Если не Уфа Пермь или Крым. Так как там, может быть другая подстанция которая удаленная.
			if (!in_array($regionNick, array('ufa', 'krym', 'kz', 'perm', 'ekb', 'astra', 'penza')) )
			{
				$filter[] = "MS.MedService_id = :MedService_id";
				$join[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";

				$withJoinCCC[] = "left join v_MedService MS  on MS.LpuBuilding_id = CCC.LpuBuilding_id";


				$sqlArr[ 'MedService_id' ] = $data[ 'session' ][ 'CurMedService_id' ];
			}

		} else {
			return $this->createError( null, 'Не установлен идентификатор службы' );
		}

		if ( !empty($data['begDate']) && !empty($data['endDate']) ) {
			$filter[] = "cast(CCC.CmpCallCard_prmDT as date) >= CAST(:begDate as date)";
			$sqlArr['begDate'] = $data['begDate'];

			$filter[] = "cast(CCC.CmpCallCard_prmDT as date) <= CAST(:endDate as date)";
			$sqlArr['endDate'] = $data['endDate'];
		}
		else {
			$filter[] = "DATEDIFF('hour', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) <= 24";
		}

		// Отображение карт указанных статусов, еще один статус указан в запросе
		$CCCStatusTypeIds = array(2);

		$this->load->model("Options_model", "opmodel");
		$globalOpts = $this->opmodel->getOptionsGlobals($data);
		$g_options = $globalOpts['globals'];

		if(!empty($g_options["smp_show_112_indispstation"])){
			$CCCStatusTypeIds[] = 20;
		}

		$CCCStatusTypeIds[] = 1;
		$CCCStatusTypeIds[] = 19;

		if ( count($lpuBuildingID) > 0 ) {
			$buildingIdUnic = array_unique($lpuBuildingID);
			$strBuildID = implode(", ", $buildingIdUnic);
			if(!empty($g_options["smp_is_all_lpubuilding_with112"]) && $g_options["smp_is_all_lpubuilding_with112"] == 2){
				$filter[] = "(CCC.LpuBuilding_id in (" . $strBuildID . ") OR (CCC.LpuBuilding_id IS NULL AND CCC.CmpCallCardStatusType_id=20))";
			}
			else
				$filter[] = "CCC.LpuBuilding_id in (" . $strBuildID . ")";

		}

		if ( empty($data['LpuBuilding_id']) ) {
			$lpuBuilding = $this->getLpuBuildingBySessionData($data);

			if ( empty($lpuBuilding[0]['LpuBuilding_id']) ) {
				return $this->createError(null, 'Не определена подстанция');
			}

			$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
		}

		$sqlArr['LpuBuilding_id'] = $data['LpuBuilding_id'];

		if (!empty($data['CmpCallCardStatusType_id'])) {
			$filter[] = "CCC.CmpCallCardStatusType_id = :CmpCallCardStatusType_id";
			$sqlArr['CmpCallCardStatusType_id'] = $data['CmpCallCardStatusType_id'];
		} else {
			$filter[] = "(CCC.CmpCallCardStatusType_id IN (" . implode(',', $CCCStatusTypeIds) . "))";
		}

		$filter[] = "CCC.Lpu_ppdid IS NULL";

		if (!empty($data['CmpCallType_id'])) {
			$filter[] = "CCC.CmpCallType_id = :CmpCallType_id";
			$sqlArr['CmpCallType_id'] = $data['CmpCallType_id'];
		}

		$sql = "
			with CmpCallCardArray as (
				select CmpCallCard_id, Lpu_ppdid
				from v_CmpCallCard CCC 
					" . implode(" ", $withJoinCCC) . "
				where
					" . implode(" and ", $filter) . "
			),
			
			activeEventArray as (
				select
					CCCE.CmpCallCard_id as CmpCallCard_id,
					CCCET.CmpCallCardEventType_Name as CmpCallCardEventType_Name,
					CCCET.CmpCallCardEventType_Code as CmpCallCardEventType_Code,
					CCCE.CmpCallCardEvent_updDT as CmpCallCardEvent_updDT,
					ETS.EmergencyTeamStatus_id as EmergencyTeamStatus_id,
					ETS.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
					ETS.EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
				from
					v_CmpCallCardEvent CCCE

					inner join CmpCallCardArray CCCA on CCCA.CmpCallCard_id = CCCE.CmpCallCard_id
					LEFT JOIN v_CmpCallCardEventType CCCET  on CCCE.CmpCallCardEventType_id = CCCET.CmpCallCardEventType_id

					LEFT JOIN v_EmergencyTeamStatusHistory ETSH  on CCCE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

					LEFT JOIN v_EmergencyTeamStatus ETS  on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id

				where
					CCCET.CmpCallCardEventType_IsKeyEvent = 2
			)

			SELECT
				-- select
				 CCC.CmpCallCard_id as \"CmpCallCard_id\"
				 ,CCLC.CmpCloseCard_id as \"CmpCloseCard_id\"
				--,CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
				,CCC.Sex_id as \"Sex_id\"
				,CCC.LpuBuilding_id as \"LpuBuilding_id\"
				,CCC.CmpReason_id as \"CmpReason_id\"
				,case when to_char (COALESCE(CR.CmpReason_Code,'0')) in ('313', '53', '298', '326', '231', '343', '232', '233', '155', '329', '321', '314', '344', '319', '36', '114', '40', '156', '277', '88', '153', '127', '121', '89', '305', '327', '56', '273', '102', '176', '351', '307', '338', '52', '339', '331', '191', '345', '323', '337', '302', '341', '310') then 'НП' else '' end as \"Urgency\"

				,to_char (CCC.CmpCallCard_prmDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDate\"
				,to_char (cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_prmDateFormat\"
				,to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') as \"CmpCallCard_prmDateStr\"

				,to_char (CCC.CmpCallCard_PlanDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_PlanDT\"
				,to_char (CCC.CmpCallCard_FactDT, 'DD Mon YYYY HH24:MI:SS') as \"CmpCallCard_FactDT\"
				,(case when CCC.CmpCallCard_FactDT > CCC.CmpCallCard_PlanDT then '1' else '' end) as \"isLate\"
				,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
				,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') || ' ' || COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') || ' ' || COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as \"Person_FIO\"
				,to_char (COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay), 'dd.mm.yyyy') as \"Person_Birthday\"

				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code||'. ' else '' end || COALESCE(CR.CmpReason_Name, '')) as \"CmpReason_Name\"


				,CCC.CmpSecondReason_id as \"CmpSecondReason_id\"
				,COALESCE(CSecondR.CmpReason_Code || '. ', '') || CSecondR.CmpReason_Name as \"CmpSecondReason_Name\"
				,CASE WHEN ( COALESCE(CCC.Person_Age,0) = 0 OR COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) is not null ) THEN
					CASE WHEN DATEDIFF('minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  ) > 12 THEN

						to_char (DATEDIFF( 'year',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  )  ) || ' лет'

					ELSE
						CASE WHEN DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  ) <=30 THEN

							 to_char (DATEDIFF('day',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  ) ) || ' дн.'

						ELSE
							 to_char (DATEDIFF( 'minute',COALESCE(PS.Person_BirthDay, CCC.Person_BirthDay) ,CCC.CmpCallCard_prmDT  )  ) || ' мес.'

						END
					END
				ELSE
					CASE WHEN COALESCE(CCC.Person_Age,0) = 0 THEN ''
					ELSE  to_char (CCC.Person_Age ) || ' лет'
					END
				END
				as personAgeText
				,COALESCE(CCC.Person_Age, dbo.Age2(PS.Person_Birthday, dbo.tzGetDate())) as \"Person_Age\"

				,RTRIM(case when CCT.CmpCallType_id is not null then CAST(CCT.CmpCallType_Code as varchar(2))||'. ' else '' end || COALESCE(CCT.CmpCallType_Name, '')) as \"CmpCallType_Name\"

				,CCT.CmpCallType_Code as \"CmpCallType_Code\"
				,CCC.EmergencyTeam_id as \"EmergencyTeam_id\"
				,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
				,activeEvent.EmergencyTeamStatus_id as \"EmergencyTeamStatus_id\"
				,ETSpec.EmergencyTeamSpec_Code as \"EmergencyTeamSpec_Code\"
				,ETSpec.EmergencyTeamSpec_Name as \"EmergencyTeamSpec_Name\"
				,COALESCE(CCC.CmpCallCard_Urgency,0) as \"CmpCallCard_Urgency\"

				,CCC.CmpCallPlaceType_id as \"CmpCallPlaceType_id\"
				,CCC.Person_IsUnknown as \"Person_IsUnknown\"

				--,LOWER( COALESCE(City.KLSocr_Nick, Town.KLSocr_Nick, RGNCity.KLSocr_Nick) )||'. ' ||
				--COALESCE(City.KLCity_Name, Town.KLTown_Name, RGNCity.KLRgn_Name) ||

				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
				case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

				end||

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
					', '||Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
				end ||

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
					', '||SecondStreet.KLStreet_FullName end
					else ''
				end ||

				case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
				case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
				case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else '' end as \"Adress_Name\"

				,case when UAD.UnformalizedAddressDirectory_lat is not null then UAD.UnformalizedAddressDirectory_lat else null end as \"UnAdress_lat\"
				,case when UAD.UnformalizedAddressDirectory_lng is not null then UAD.UnformalizedAddressDirectory_lng else null end as \"UnAdress_lng\"

				,case when City.KLCity_id is not null then City.KLCity_id else Town.KLTown_id end as \"Town_id\"
				,case when COALESCE(CCC.KLStreet_id,0) = 0 then

					case when COALESCE(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL

					else 'UA.'||CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				 else 'ST.'||CAST(CCC.KLStreet_id as varchar(8)) end as \"StreetAndUnformalizedAddressDirectory_id\"
				,RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as \"Person_Surname\"
				,RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as \"Person_Firname\"
				,RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as \"Person_Secname\"
				,PS.Polis_Ser as \"Polis_Ser\"
				,PS.Polis_Num as \"Polis_Num\"
				,PS.Person_id as \"Person_id\"
				,CCC.CmpCallCard_Ktov as \"CmpCallCard_Ktov\"
				,CCC.CmpCallerType_id as \"CmpCallerType_id\"
				,CCC.CmpCallCard_Dom as \"CmpCallCard_Dom\"
				,CCC.CmpCallCard_Korp as \"CmpCallCard_Korp\"
				,CCC.CmpCallCard_Kvar as \"CmpCallCard_Kvar\"
				,CCC.CmpCallCard_Podz as \"CmpCallCard_Podz\"
				,CCC.CmpCallCard_Etaj as \"CmpCallCard_Etaj\"
				,CCC.CmpCallCard_Kodp as \"CmpCallCard_Kodp\"
				,CCC.CmpCallCard_Telf as \"CmpCallCard_Telf\"
				,CCC.CmpCallCard_Comm as \"CmpCallCard_Comm\"
				,case when COALESCE(CCC.CmpCallCard_IsExtra,1) = 1 then 'Экстренный' else 'Неотложный' end as \"CmpCallCard_IsExtraText\"
				,COALESCE(CCC.CmpCallCard_IsExtra, 1) as \"CmpCallCard_IsExtra\"

				,CCC.CmpCallCard_IsPoli as \"CmpCallCard_IsPoli\"
				,to_char(CCC.CmpCallCard_Tper, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTper\"

				,to_char(CCC.CmpCallCard_Vyez, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateVyez\"

				,to_char(CCC.CmpCallCard_Przd, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DatePrzd\"

				,to_char(CCC.CmpCallCard_Tgsp, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTgsp\"

				,to_char(CCC.CmpCallCard_Tsta, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTsta\"

				,to_char(CCC.CmpCallCard_Tisp, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTisp\"

				,to_char(CCC.CmpCallCard_Tvzv, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_DateTvzv\"

				,to_char(CCC.CmpCallCard_HospitalizedTime, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_HospitalizedTime\"

				,to_char(CCC.CmpCallCard_Tper, 'YYYY-MM-DD HH24:MI:SS') as \"CmpCallCard_TimeTper\"

				
				,CCC.KLRgn_id as \"KLRgn_id\"
				,CCC.KLSubRgn_id as \"KLSubRgn_id\"
				,CCC.KLCity_id as \"KLCity_id\"
				,CCC.KLTown_id as \"KLTown_id\"

				,DATEDIFF('second', CCC.CmpCallCard_prmDT, dbo.tzGetDate()) as \"MAXDateDiff\"
				,CCC.KLStreet_id as \"KLStreet_id\"
				,case when COALESCE(CCC.CmpCallCard_IsOpen, 1) = 2

					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '04'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id=7 THEN '03'
									WHEN CCC.CmpCallCardStatusType_id=8 THEN '02'
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN '0'||cast(CCC.CmpCallCardStatusType_id+1 as varchar(1))
									ELSE  '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar(2))
								END
							ELSE
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '07'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=7 THEN '03'
									WHEN CCC.CmpCallCardStatusType_id=8 THEN '02'
									ELSE '0'||cast(CCC.CmpCallCardStatusType_id+4 as varchar(2))
								END
							END
					else '09'
				end as \"CmpGroupName_id\",
				CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\",

				CASE
					WHEN CCCST.CmpCallCardStatusType_Code in(1,12) THEN 1 --относится к группе (передано)
					WHEN CCCST.CmpCallCardStatusType_Code in(2) THEN 2 --относится к группе (принято)
					WHEN CCCST.CmpCallCardStatusType_Code in(11) THEN 4 --относится к группе (отложено)
					ELSE 3 --относится к группе всех остальных
				END as \"TransmittedOrAccepted\",
				CCC.CmpCallCard_rid as \"CmpCallCard_rid\",

				CASE WHEN CCC.CmpCallCardStatusType_id = 19 THEN
					activeEvent.CmpCallCardEventType_Name || ' до ' || to_char(CCC.CmpCallCard_storDT, 4)||' '||to_char(cast(CmpCallCard_storDT as time(6)))
					ELSE activeEvent.CmpCallCardEventType_Name
				END as \"CmpCallCardEventType_Name\",
				activeEvent.CmpCallCardEvent_updDT as \"CmpCallCardEvent_updDT\",
				CASE WHEN CCC.CmpCallCardStatusType_id not in(4,5,6,16,19) THEN DATEDIFF('minute', activeEvent.CmpCallCardEvent_updDT, dbo.tzGetDate()) END as \"EventWaitDuration\",
				CCC.CmpCallCard_storDT as \"CmpCallCard_storDT\",
				CASE WHEN CCC.CmpCallCardStatusType_id = 19 THEN CCC.CmpCallCard_defCom END as \"CmpCallCard_defCom\",
				CASE WHEN
					DATEDIFF('minute', dbo.tzGetDate(), CCC.CmpCallCard_storDT ) > 0
				THEN
					1
				ELSE
					2
				END as \"isTimeDefferedCall\",
				CASE WHEN CCC.CmpCallCardStatusType_id = 20 THEN 2 ELSE 1 END as is112,
				
				CASE WHEN ( (CCC.CmpCallCard_Tper is NULL) OR (CCC.EmergencyTeam_id is NULL) OR DATEDIFF('second', CCC.CmpCallCard_Tper, dbo.tzGetDate() ) < 10)
				THEN ''
				ELSE
					CASE WHEN
						lastCallMessage.CmpCallCardMessage_tabletDT is null
					THEN
						'Не доставлено'
					ELSE
						'Доставлено'
					END
				END as \"lastCallMessageText\"
				,CCCD.Duplicate_Count as \"Duplicate_Count\"
				,ETDT.EmergencyTeamDelayType_Name as \"EmergencyTeamDelayType_Name\"
				,CCR.CmpCallRecord_id as \"CmpCallRecord_id\"

				-- end select
			FROM
				-- from
				v_CmpCallCard CCC 

				inner join CmpCallCardArray CCCA on CCCA.CmpCallCard_id = CCC.CmpCallCard_id
				LEFT JOIN LATERAL (

					select 
					cmpclosecard_id as \"cmpclosecard_id\",
                    cmpcallcard_id as \"cmpcallcard_id\",
                    pmuser_insid as \"pmuser_insid\",
                    pmuser_updid as \"pmuser_updid\",
                    cmpclosecard_insdt as \"cmpclosecard_insdt\",
                    cmpclosecard_upddt as \"cmpclosecard_upddt\",
                    feldsher_id as \"feldsher_id\",
                    stationnum as \"stationnum\",
                    emergencyteamnum as \"emergencyteamnum\",
                    accepttime as \"accepttime\",
                    transtime as \"transtime\",
                    gotime as \"gotime\",
                    arrivetime as \"arrivetime\",
                    transporttime as \"transporttime\",
                    tohospitaltime as \"tohospitaltime\",
                    endtime as \"endtime\",
                    backtime as \"backtime\",
                    summtime as \"summtime\",
                    area_id as \"area_id\",
                    town_id as \"town_id\",
                    city_id as \"city_id\",
                    street_id as \"street_id\",
                    house as \"house\",
                    office as \"office\",
                    entrance as \"entrance\",
                    codeentrance as \"codeentrance\",
                    level as \"level\",
                    ktov as \"ktov\",
                    phone as \"phone\",
                    fam as \"fam\",
                    name as \"name\",
                    middle as \"middle\",
                    age as \"age\",
                    sex_id as \"sex_id\",
                    work as \"work\",
                    documentnum as \"documentnum\",
                    feldsheraccept as \"feldsheraccept\",
                    feldshertrans as \"feldshertrans\",
                    callpovod_id as \"callpovod_id\",
                    calltype_id as \"calltype_id\",
                    isalco as \"isalco\",
                    complaints as \"complaints\",
                    anamnez as \"anamnez\",
                    ismenen as \"ismenen\",
                    isanis as \"isanis\",
                    isnist as \"isnist\",
                    islight as \"islight\",
                    isacro as \"isacro\",
                    ismramor as \"ismramor\",
                    ishale as \"ishale\",
                    isperit as \"isperit\",
                    urine as \"urine\",
                    shit as \"shit\",
                    othersympt as \"othersympt\",
                    workad as \"workad\",
                    ad as \"ad\",
                    pulse as \"pulse\",
                    chss as \"chss\",
                    chd as \"chd\",
                    temperature as \"temperature\",
                    pulsks as \"pulsks\",
                    gluck as \"gluck\",
                    localstatus as \"localstatus\",
                    ekg1time as \"ekg1time\",
                    ekg1 as \"ekg1\",
                    ekg2time as \"ekg2time\",
                    ekg2 as \"ekg2\",
                    diag_id as \"diag_id\",
                    efad as \"efad\",
                    efchss as \"efchss\",
                    efpulse as \"efpulse\",
                    eftemperature as \"eftemperature\",
                    efchd as \"efchd\",
                    efpulsks as \"efpulsks\",
                    efgluck as \"efgluck\",
                    kilo as \"kilo\",
                    lpu_id as \"lpu_id\",
                    helpplace as \"helpplace\",
                    helpauto as \"helpauto\",
                    desctext as \"desctext\",
                    cmpclosecard_isinreg as \"cmpclosecard_isinreg\",
                    korpus as \"korpus\",
                    room as \"room\",
                    cmpclosecard_firstversion as \"cmpclosecard_firstversion\",
                    cmpclosecard_ispaid as \"cmpclosecard_ispaid\",
                    day_num as \"day_num\",
                    year_num as \"year_num\",
                    callpovodnew_id as \"callpovodnew_id\",
                    emergencyteamspec_id as \"emergencyteamspec_id\",
                    medpersonal_id as \"medpersonal_id\",
                    socstatus_id as \"socstatus_id\",
                    issogl as \"issogl\",
                    isotkazmed as \"isotkazmed\",
                    isotkazhosp as \"isotkazhosp\",
                    lpusection_id as \"lpusection_id\",
                    cmpclosecard_indexrep as \"cmpclosecard_indexrep\",
                    cmpclosecard_indexrepinreg as \"cmpclosecard_indexrepinreg\",
                    cmpclosecard_isarchive as \"cmpclosecard_isarchive\",
                    paytype_id as \"paytype_id\",
                    lpu_codesmo as \"lpu_codesmo\",
                    cmpclosecardcause_id as \"cmpclosecardcause_id\",
                    cmpclosecardwherereported_id as \"cmpclosecardwherereported_id\",
                    cmpclosecardwhere_dt as \"cmpclosecardwhere_dt\",
                    messagenum as \"messagenum\",
                    acceptfio as \"acceptfio\",
                    medstafffact_id as \"medstafffact_id\",
                    rankinscale_id as \"rankinscale_id\",
                    rankinscale_sid as \"rankinscale_sid\",
                    isotkazsign as \"isotkazsign\",
                    otkazsignwhy as \"otkazsignwhy\",
                    disstart as \"disstart\",
                    cmpcallertype_id as \"cmpcallertype_id\",
                    lpubuilding_id as \"lpubuilding_id\",
                    emergencyteam_id as \"emergencyteam_id\",
                    person_id as \"person_id\",
                    diag_uid as \"diag_uid\",
                    cmpclosecard_epid as \"cmpclosecard_epid\",
                    cmpclosecard_glaz as \"cmpclosecard_glaz\",
                    cmpclosecard_e1 as \"cmpclosecard_e1\",
                    cmpclosecard_m1 as \"cmpclosecard_m1\",
                    cmpclosecard_v1 as \"cmpclosecard_v1\",
                    cmpclosecard_e2 as \"cmpclosecard_e2\",
                    cmpclosecard_m2 as \"cmpclosecard_m2\",
                    cmpclosecard_v2 as \"cmpclosecard_v2\",
                    cmpclosecard_topic as \"cmpclosecard_topic\",
                    diag_sid as \"diag_sid\",
                    cmpclosecard_glazafter as \"cmpclosecard_glazafter\",
                    cmpclosecard_transpenddt as \"cmpclosecard_transpenddt\",
                    cmpclosecard_street as \"cmpclosecard_street\",
                    cmpclosecard_isextra as \"cmpclosecard_isextra\",
                    cmpclosecard_isprofile as \"cmpclosecard_isprofile\",
                    cmpclosecard_isnmp as \"cmpclosecard_isnmp\",
                    cmpclosecard_acceptbrigadedt as \"cmpclosecard_acceptbrigadedt\",
                    cmpclosecard_isintestinal as \"cmpclosecard_isintestinal\",
                    cmpclosecard_isheartnoise as \"cmpclosecard_isheartnoise\",
                    cmpclosecard_sat as \"cmpclosecard_sat\",
                    cmpclosecard_aftersat as \"cmpclosecard_aftersat\",
                    cmpclosecard_rhythm as \"cmpclosecard_rhythm\",
                    cmpclosecard_afterrhythm as \"cmpclosecard_afterrhythm\",
                    cmpclosecard_israttle as \"cmpclosecard_israttle\",
                    cmpclosecard_isvomit as \"cmpclosecard_isvomit\",
                    cmpclosecard_isdiuresis as \"cmpclosecard_isdiuresis\",
                    cmpclosecard_isdefecation as \"cmpclosecard_isdefecation\",
                    cmpclosecard_istrauma as \"cmpclosecard_istrauma\",
                    cmpclosecard_begtreatdt as \"cmpclosecard_begtreatdt\",
                    cmpclosecard_endtreatdt as \"cmpclosecard_endtreatdt\",
                    org_mid as \"org_mid\",
                    cmpclosecard_helpdt as \"cmpclosecard_helpdt\",
                    cmpclosecard_lethaldt as \"cmpclosecard_lethaldt\",
                    cmplethaltype_id as \"cmplethaltype_id\",
                    cmpclosecard_addinfo as \"cmpclosecard_addinfo\",
                    medstafffact_cid as \"medstafffact_cid\",
                    cmpclosecard_clinicaleff as \"cmpclosecard_clinicaleff\",
                    cmpclosecard_menenaddiction as \"cmpclosecard_menenaddiction\",
                    cmpclosecard_guid as \"cmpclosecard_guid\",
                    cmpclosecard_firstversionguid as \"cmpclosecard_firstversionguid\",
                    emergencyteam_guid as \"emergencyteam_guid\",
                    cmpcallcard_guid as \"cmpcallcard_guid\",
                    person_polisser as \"person_polisser\",
                    person_polisnum as \"person_polisnum\",
                    cmpclosecard_polisednum as \"cmpclosecard_polisednum\",
                    cmpresult_id as \"cmpresult_id\",
                    unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
                    cmpclosecard_ulic as \"cmpclosecard_ulic\",
                    cmpclosecard_issignlist as \"cmpclosecard_issignlist\",
                    leavetype_id as \"leavetype_id\",
                    cmpclosecard_userkilo as \"cmpclosecard_userkilo\",
                    person_snils as \"person_snils\",
                    cmpclosecard_passtime as \"cmpclosecard_passtime\",
                    cmpclosecard_callbacktime as \"cmpclosecard_callbacktime\",
                    cmpclosecard_dopinfo as \"cmpclosecard_dopinfo\",
                    cmpclosecard_ulicsecond as \"cmpclosecard_ulicsecond\",
                    cmpcallplacetype_id as \"cmpcallplacetype_id\",
                    alerg as \"alerg\",
                    epid as \"epid\",
                    isvac as \"isvac\",
                    iskupir as \"iskupir\",
                    zev as \"zev\",
                    perk as \"perk\",
                    cmpclosecard_daynumpr as \"cmpclosecard_daynumpr\",
                    cmpclosecard_yearnumpr as \"cmpclosecard_yearnumpr\",
                    cmpcallsigntype_id as \"cmpcallsigntype_id\",
                    cmpclosecard_comm as \"cmpclosecard_comm\",
                    diag_add as \"diag_add\",
                    cmpclosecard_statuslocalis as \"cmpclosecard_statuslocalis\",
                    bad_dt as \"bad_dt\",
                    mensis_dt as \"mensis_dt\",
                    cmpclosecard_isinregzno as \"cmpclosecard_isinregzno\",
                    registry_sid as \"registry_sid\",
                    cmpclosecard_userkilocommon as \"cmpclosecard_userkilocommon\"
					from {$this->schema}.v_CmpCloseCard 

					where CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCloseCard_id desc
					limit 1
				) CCLC ON TRUE

				LEFT JOIN LATERAL (

					select
						CmpCallCardEvent_updDT as CmpCallCardEvent_updDT,
						CmpCallCardEventType_Name as CmpCallCardEventType_Name,
						CmpCallCardEventType_Code as CmpCallCardEventType_Code,
						EmergencyTeamStatus_id as EmergencyTeamStatus_id,
						EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
						EmergencyTeamStatus_Name as EmergencyTeamStatus_Name
					from activeEventArray
					where CmpCallCard_id = CCC.CmpCallCard_id
					ORDER BY CmpCallCardEvent_updDT desc
					limit 1
				) as activeEvent ON TRUE
				
				LEFT JOIN LATERAL(

					select
						cmpcallcardmessage_id as cmpcallcardmessage_id,
                        message_id as message_id,
                        cmpcallcard_id as cmpcallcard_id,
                        cmpcallcardmessage_webdt as cmpcallcardmessage_webdt,
                        cmpcallcardmessage_tabletdt as cmpcallcardmessage_tabletdt,
                        pmuser_insid as pmuser_insid,
                        pmuser_updid as pmuser_updid,
                        cmpcallcardmessage_insdt as cmpcallcardmessage_insdt,
                        cmpcallcardmessage_upddt as cmpcallcardmessage_upddt
					from
						v_CmpCallCardMessage CCCM 

					where
						CCCM.CmpCallCard_id = CCC.CmpCallCard_id
					order by CCCM.CmpCallCardMessage_webDT desc
					limit 1
				) as lastCallMessage ON TRUE

				left join v_PersonState PS  on PS.Person_id = CCC.Person_id

				left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

				left join v_CmpReason CSecondR  on CSecondR.CmpReason_id = CCC.CmpSecondReason_id

				left join v_CmpCallType CCT  on CCT.CmpCallType_id = CCC.CmpCallType_id


				left join EmergencyTeam ET  on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

				left join v_EmergencyTeamStatus ETS  on ET.EmergencyTeamStatus_id = ETS.EmergencyTeamStatus_id

				LEFT JOIN v_EmergencyTeamStatusHistory ETSH  on ET.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

				LEFT JOIN v_EmergencyTeamDelayType ETDT  on ETSH.EmergencyTeamDelayType_id = ETDT.EmergencyTeamDelayType_id


				left join v_EmergencyTeamSpec ETSpec  on ET.EmergencyTeamSpec_id = ETSpec.EmergencyTeamSpec_id

				left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id


				left join v_KLRgn RGNCity  on RGNCity.KLRgn_id = CCC.KLCity_id


				left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

				left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

				left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id

				left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLTown_id

				left join v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLCity_id

				left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

				left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id

				left join v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond

				left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id

				left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id

				left join v_CmpCallRecord CCR  on CCC.CmpCallCard_id = CCR.CmpCallCard_id

				LEFT JOIN LATERAL (

					select
						COUNT(CCCDouble.CmpCallCard_id) as Duplicate_Count
					from
						v_CmpCallCard CCCDouble 

						left join v_CmpCallCardStatusType CCCSTDouble  on CCCSTDouble.CmpCallCardStatusType_id = CCCDouble.CmpCallCardStatusType_id

					where
						CCCDouble.CmpCallCard_rid = CCC.CmpCallCard_id
						and CCCSTDouble.CmpCallCardStatusType_Code = 9
				) CCCD ON TRUE
				" . implode(" ", $join) . "
				-- end from
		";

		$query = $this->db->query( $sql, $sqlArr );
		if ( is_object( $query ) ) {
			return array(
				'data' => $query->result_array(),
				'totalCount' => $query->num_rows()
			);
		}

		return false;
	}

	/**
	 * Перезапись копирования карты из EmergencyTeam
	 */
	function copyCmpCallCard($data) {

		/* 1 - выбираем исходную запись */
		$query = "
			SELECT
			CCC.cmpcallcard_id as \"cmpcallcard_id\",
            CCC.cmpcallcard_numv as \"cmpcallcard_numv\",
            CCC.cmpcallcard_ngod as \"cmpcallcard_ngod\",
            CCC.cmpcallcard_prty as \"cmpcallcard_prty\",
            CCC.cmpcallcard_sect as \"cmpcallcard_sect\",
            CCC.cmparea_id as \"cmparea_id\",
            CCC.cmpcallcard_city as \"cmpcallcard_city\",
            CCC.cmpcallcard_ulic as \"cmpcallcard_ulic\",
            CCC.cmpcallcard_dom as \"cmpcallcard_dom\",
            CCC.cmpcallcard_kvar as \"cmpcallcard_kvar\",
            CCC.cmpcallcard_podz as \"cmpcallcard_podz\",
            CCC.cmpcallcard_etaj as \"cmpcallcard_etaj\",
            CCC.cmpcallcard_kodp as \"cmpcallcard_kodp\",
            CCC.cmpcallcard_telf as \"cmpcallcard_telf\",
            CCC.cmpplace_id as \"cmpplace_id\",
            CCC.cmpcallcard_comm as \"cmpcallcard_comm\",
            CCC.cmpreason_id as \"cmpreason_id\",
            CCC.person_id as \"person_id\",
            CCC.person_surname as \"person_surname\",
            CCC.person_firname as \"person_firname\",
            CCC.person_secname as \"person_secname\",
            CCC.person_age as \"person_age\",
            CCC.person_birthday as \"person_birthday\",
            CCC.person_polisser as \"person_polisser\",
            CCC.person_polisnum as \"person_polisnum\",
            CCC.sex_id as \"sex_id\",
            CCC.cmpcallcard_ktov as \"cmpcallcard_ktov\",
            CCC.cmpcalltype_id as \"cmpcalltype_id\",
            CCC.cmpprofile_cid as \"cmpprofile_cid\",
            CCC.cmpcallcard_smpt as \"cmpcallcard_smpt\",
            CCC.cmpcallcard_stan as \"cmpcallcard_stan\",
            CCC.cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
            CCC.cmpcallcard_line as \"cmpcallcard_line\",
            CCC.cmpresult_id as \"cmpresult_id\",
            CCC.cmparea_gid as \"cmparea_gid\",
            CCC.cmplpu_id as \"cmplpu_id\",
            CCC.cmpdiag_oid as \"cmpdiag_oid\",
            CCC.cmpdiag_aid as \"cmpdiag_aid\",
            CCC.cmptrauma_id as \"cmptrauma_id\",
            CCC.cmpcallcard_isalco as \"cmpcallcard_isalco\",
            CCC.diag_uid as \"diag_uid\",
            CCC.cmpcallcard_numb as \"cmpcallcard_numb\",
            CCC.cmpcallcard_smpb as \"cmpcallcard_smpb\",
            CCC.cmpcallcard_stbr as \"cmpcallcard_stbr\",
            CCC.cmpcallcard_stbb as \"cmpcallcard_stbb\",
            CCC.cmpprofile_bid as \"cmpprofile_bid\",
            CCC.cmpcallcard_ncar as \"cmpcallcard_ncar\",
            CCC.cmpcallcard_rcod as \"cmpcallcard_rcod\",
            CCC.cmpcallcard_tabn as \"cmpcallcard_tabn\",
            CCC.cmpcallcard_dokt as \"cmpcallcard_dokt\",
            CCC.cmpcallcard_tab2 as \"cmpcallcard_tab2\",
            CCC.cmpcallcard_tab3 as \"cmpcallcard_tab3\",
            CCC.cmpcallcard_tab4 as \"cmpcallcard_tab4\",
            CCC.diag_sid as \"diag_sid\",
            CCC.cmptalon_id as \"cmptalon_id\",
            CCC.cmpcallcard_expo as \"cmpcallcard_expo\",
            CCC.cmpcallcard_smpp as \"cmpcallcard_smpp\",
            CCC.cmpcallcard_vr51 as \"cmpcallcard_vr51\",
            CCC.cmpcallcard_d201 as \"cmpcallcard_d201\",
            CCC.cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
            CCC.cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
            CCC.cmpcallcard_dspp as \"cmpcallcard_dspp\",
            CCC.cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
            CCC.cmpcallcard_kakp as \"cmpcallcard_kakp\",
            CCC.cmpcallcard_tper as \"cmpcallcard_tper\",
            CCC.cmpcallcard_vyez as \"cmpcallcard_vyez\",
            CCC.cmpcallcard_przd as \"cmpcallcard_przd\",
            CCC.cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
            CCC.cmpcallcard_tsta as \"cmpcallcard_tsta\",
            CCC.cmpcallcard_tisp as \"cmpcallcard_tisp\",
            CCC.cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
            CCC.cmpcallcard_kilo as \"cmpcallcard_kilo\",
            CCC.cmpcallcard_dlit as \"cmpcallcard_dlit\",
            CCC.cmpcallcard_prdl as \"cmpcallcard_prdl\",
            CCC.cmparea_pid as \"cmparea_pid\",
            CCC.cmpcallcard_pcity as \"cmpcallcard_pcity\",
            CCC.cmpcallcard_pulic as \"cmpcallcard_pulic\",
            CCC.cmpcallcard_pdom as \"cmpcallcard_pdom\",
            CCC.cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
            CCC.cmplpu_aid as \"cmplpu_aid\",
            CCC.cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
            CCC.cmpcallcard_medc as \"cmpcallcard_medc\",
            CCC.cmpcallcard_izv1 as \"cmpcallcard_izv1\",
            CCC.cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
            CCC.cmpcallcard_inf1 as \"cmpcallcard_inf1\",
            CCC.cmpcallcard_inf2 as \"cmpcallcard_inf2\",
            CCC.cmpcallcard_inf3 as \"cmpcallcard_inf3\",
            CCC.cmpcallcard_inf4 as \"cmpcallcard_inf4\",
            CCC.cmpcallcard_inf5 as \"cmpcallcard_inf5\",
            CCC.cmpcallcard_inf6 as \"cmpcallcard_inf6\",
            CCC.pmuser_insid as \"pmuser_insid\",
            CCC.pmuser_updid as \"pmuser_updid\",
            CCC.cmpcallcard_insdt as \"cmpcallcard_insdt\",
            CCC.cmpcallcard_upddt as \"cmpcallcard_upddt\",
            CCC.klrgn_id as \"klrgn_id\",
            CCC.klsubrgn_id as \"klsubrgn_id\",
            CCC.klcity_id as \"klcity_id\",
            CCC.kltown_id as \"kltown_id\",
            CCC.klstreet_id as \"klstreet_id\",
            CCC.lpu_ppdid as \"lpu_ppdid\",
            CCC.cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
            CCC.cmpcallcard_isopen as \"cmpcallcard_isopen\",
            CCC.cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
            CCC.cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
            CCC.cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
            CCC.cmpppdresult_id as \"cmpppdresult_id\",
            CCC.emergencyteam_id as \"emergencyteam_id\",
            CCC.cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
            CCC.lpu_id as \"lpu_id\",
            CCC.cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
            CCC.medpersonal_id as \"medpersonal_id\",
            CCC.resultdeseasetype_id as \"resultdeseasetype_id\",
            CCC.cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
            CCC.unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
            CCC.cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
            CCC.cmpcallcard_korp as \"cmpcallcard_korp\",
            CCC.cmpcallcard_room as \"cmpcallcard_room\",
            CCC.cmpcallcard_difftime as \"cmpcallcard_difftime\",
            CCC.uslugacomplex_id as \"uslugacomplex_id\",
            CCC.lpubuilding_id as \"lpubuilding_id\",
            CCC.cmpcallertype_id as \"cmpcallertype_id\",
            CCC.cmpcallplacetype_id as \"cmpcallplacetype_id\",
            CCC.cmpcallcard_rid as \"cmpcallcard_rid\",
            CCC.cmpcallcard_urgency as \"cmpcallcard_urgency\",
            CCC.cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
            CCC.cmpsecondreason_id as \"cmpsecondreason_id\",
            CCC.cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
            CCC.cmpcallreasontype_id as \"cmpcallreasontype_id\",
            CCC.cmpreasonnew_id as \"cmpreasonnew_id\",
            CCC.cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
            CCC.cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
            CCC.cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
            CCC.cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
            CCC.medstafffact_id as \"medstafffact_id\",
            CCC.rankinscale_id as \"rankinscale_id\",
            CCC.rankinscale_sid as \"rankinscale_sid\",
            CCC.leavetype_id as \"leavetype_id\",
            CCC.cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
            CCC.lpusection_id as \"lpusection_id\",
            CCC.cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
            CCC.cmpcallcard_condition as \"cmpcallcard_condition\",
            CCC.lpu_cid as \"lpu_cid\",
            CCC.cmpcallcard_tend as \"cmpcallcard_tend\",
            CCC.cmpcallcard_callltd as \"cmpcallcard_callltd\",
            CCC.cmpcallcard_calllng as \"cmpcallcard_calllng\",
            CCC.cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
            CCC.cmprejectionreason_id as \"cmprejectionreason_id\",
            CCC.cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
            CCC.cmpcallcard_savedt as \"cmpcallcard_savedt\",
            CCC.cmpcallcard_plandt as \"cmpcallcard_plandt\",
            CCC.cmpcallcard_factdt as \"cmpcallcard_factdt\",
            CCC.cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
            CCC.cmpcallcard_isextra as \"cmpcallcard_isextra\",
            CCC.cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
            CCC.cmpcallcard_guid as \"cmpcallcard_guid\",
            CCC.cmpcallcard_rguid as \"cmpcallcard_rguid\",
            CCC.cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
            CCC.cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
            CCC.emergencyteam_guid as \"emergencyteam_guid\",
            CCC.cmpcallcard_stordt as \"cmpcallcard_stordt\",
            CCC.cmpcallcard_defcom as \"cmpcallcard_defcom\",
            CCC.medservice_id as \"medservice_id\",
            CCC.cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
            CCC.cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
            CCC.diag_sopid as \"diag_sopid\",
            CCC.cmpleavetype_id as \"cmpleavetype_id\",
            CCC.cmpleavetask_id as \"cmpleavetask_id\",
            CCC.cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
            CCC.cmptransporttype_id as \"cmptransporttype_id\",
            CCC.cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
            CCC.cmpcallcardresult_id as \"cmpcallcardresult_id\",
            CCC.person_isunknown as \"person_isunknown\",
            CCC.cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
            CCC.lpu_smpid as \"lpu_smpid\",
            CCC.lpu_hid as \"lpu_hid\",
            CCC.unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
            CCC.paytype_id as \"paytype_id\",
            CCC.cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
            CCC.cmpcallcard_sid as \"cmpcallcard_sid\",
            CCC.cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
            CCC.cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
            CCC.cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
            CCC.cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
            CCC.cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
            CCC.cmpcallsigntype_id as \"cmpcallsigntype_id\",
            CCC.lpu_codesmo as \"lpu_codesmo\",
            CCC.registry_sid as \"registry_sid\",
            CCC.diag_gid as \"diag_gid\",
            CCC.medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
            CCC.cmpcommonstate_id as cmpcommonstate_id,
			RTRIM(PUC.PMUser_surName||' '||SUBSTRING(PUC.PMUser_firName,1,1) ||' '||SUBSTRING(PUC.PMUser_secName,1,1)) as \"pmUser_FIO\"
			FROM v_CmpCallCard CCC 

			LEFT JOIN v_pmUserCache as PUC  ON (PUC.PMUser_id = CCC.pmUser_updID)

			WHERE CCC.CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query( $query, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
		) );

		if ( !is_object( $result ) ) {
			return false;
		}
		$oldCard = $result->result( 'array' );
		$oldCard = $oldCard[0];
		$oldCard["pmUser_insID"] = $data["pmUser_id"];
		$oldCard["CmpCallCardStatusType_id"] = 1;

		$curdate =  new DateTime();
		$oldCard[ 'CmpCallCard_prmDT' ] = $curdate->format('Y-m-d H:i:s');
		$oldCard['AcceptTime'] = $oldCard[ 'CmpCallCard_prmDT' ];

		$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
		$nums = $this->CmpCallCard_model->getCmpCallCardNumber($oldCard);
		$oldCard["CmpCallCard_Numv"] = $nums[0]["CmpCallCard_Numv"];
		$oldCard["CmpCallCard_Ngod"] = $nums[0]["CmpCallCard_Ngod"];
		$oldCard["EmergencyTeam_id"] = null;
		$oldCard["CmpPPDResult_id"] = null;
		$oldCard["CmpCallCard_Tper"] = null;
		$oldCard["CmpCallCard_Vyez"] = null;
		$oldCard["CmpCallCard_Przd"] = null;
		$oldCard["CmpCallCard_Tgsp"] = null;
		$oldCard["CmpCallCard_Tsta"] = null;
		$oldCard["CmpCallCard_Tvzv"] = null;
		$oldCard["CmpCallCard_Tisp"] = null;
		$oldCard["CmpCallCard_HospitalizedTime"] = null;
		$oldCard["CmpCallCard_IsPoli"] = null;
		$oldCard["CmpCallCard_rid"] = $data[ 'CmpCallCard_id' ];
		$oldCard["ARMType"] = (!empty($data[ 'ARMType' ])?$data[ 'ARMType' ]:null);

		$res = $this->resaveCmpCallCard($oldCard, null);

		$newCard = $res->result('array');

		if ($newCard && $newCard[0]) {
			return $newCard[0];
		}
		return false;
	}

	/**
	 * default desc
	 */

	function getCmpCallCardStatus($data) {

		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}
		if ( $this->db->dbdriver == 'postgre' ) {
			$query = "
				SELECT
					CCC.\"CmpCallCardStatusType_id\" as \"CmpCallCardStatusType_id\"
				FROM
					dbo.\"v_CmpCallCard\" CCC
				WHERE
					CCC.\"CmpCallCard_id\" = :CmpCallCard_id
			";
		} else {
			$query = "
				SELECT
					CCC.CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\"
				FROM
					v_CmpCallCard CCC

				WHERE
					CCC.CmpCallCard_id = :CmpCallCard_id
				";
		}
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$return = $result->result('array');
			return $return;
		} else {
			return false;
		}
	}

	/**
	 * Загрузка вызовов и статусов для формы "Анализ работы с вызовами"
	 */
	function loadAnalysisCmpCalls($data){

		$rescalls = array();
		$resteams = array();

		$ts = strtotime($data['begDate'] .' '. $data['begTime']);

		$begDate = new DateTime();
		$endDate = new DateTime();

		//Начало шкалы времени определяется следующим образом: Дата и время минус Количество часов/2.
		//Окончание шкалы времени определяется следующим образом: Дата и время плюс Количество часов/2.
		$begDate->setTimestamp($ts - ($data['hours']/2 * 60 * 60));
		$endDate->setTimestamp($ts + ($data['hours']/2 * 60 * 60));
		$params['begDate'] =  $begDate;
		$params['endDate'] =  $endDate;

		//общие фильтры
		//$filterC[] = "CCCE.CmpCallCardEvent_insDT >= :begDate and CCCE.CmpCallCardEvent_insDT <= :endDate";
		$filterC[] = "CCCS.CmpCallCardStatus_insDT between CAST(:begDate as timestamp(3)) and CAST(:endDate as timestamp(3))";

		$filterC[] = "CCCET.CmpCallCardEventType_IsKeyEvent = 2";
		$filterC[] = "CCCS.CmpCallCard_id is not null";

		if(!empty($data['LpuBuilding_id'])){
			$filterC[] = "CCC.LpuBuilding_id = :LpuBuilding_id";
			$filterT[] = "CCC.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] =  $data['LpuBuilding_id'];
		}

		//фильтры грида вызовов
		$gridC[] = '1 = 1';
		if(!empty($data['cCmpCallCard_Numv'])){
			$gridC[] = "sel.CmpCallCard_Numv ILIKE '%".$data['cCmpCallCard_Numv']."%'";
		}

		if(!empty($data['cLpuBuilding_Name'])){
			$gridC[] = "sel.LpuBuilding_Name ILIKE '%".$data['cLpuBuilding_Name']."%'";
		}

		if(!empty($data['cCmpReason_Name'])){
			$gridC[] = "sel.CmpReason_Name ILIKE '%".$data['cCmpReason_Name']."%'";
		}

		$sqlEventCalls = "
		SELECT
			*
		FROM
		(
			SELECT
				CCCE.CmpCallCardEvent_id as CmpCallCardEvent_id,
				CCCE.EmergencyTeamStatusHistory_id as EmergencyTeamStatusHistory_id,
				CCCET.CmpCallCardEventType_Code as CmpCallCardEventType_Code,
				CCCET.CmpCallCardEventType_Name as CmpCallCardEventType_Name,
				CCCS.CmpCallCard_id as CmpCallCard_id,
				to_char (CCCS.CmpCallCardStatus_insDT, 'DD Mon YYYY HH24:MI:SS' ) as CmpCallCardStatus_insDT,
				to_char(CCCS.CmpCallCardStatus_insDT, 'HH24:MI:SS') as CmpCallCardStatus_insTime,

				RTRIM(PMC.PMUser_surName||' '||SUBSTRING(PMC.PMUser_firName,1,1) ||'.'||SUBSTRING(PMC.PMUser_secName,1,1)||'.') as PMUser_Name,

				CR.CmpReason_Code || '. ' || CR.CmpReason_Name as CmpReason_Name,
				LB.LpuBuilding_Name as LpuBuilding_Name,
				LB.LpuBuilding_id as LpuBuilding_id,
				CCC.CmpCallCard_Numv as CmpCallCard_Numv,
				CLC.CmpCloseCard_id as CmpCloseCard_id,
				COALESCE(ET.EmergencyTeam_id, '') as EmergencyTeam_id,

				ET.EmergencyTeam_Num as EmergencyTeam_Num,
				CCCST.CmpCallCardStatusType_Name as CmpCallCardStatusType_Name
			FROM v_CmpCallCardEvent CCCE 

					left join v_EmergencyTeamStatusHistory ETSH  on CCCE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

					left join v_CmpCallCardStatus CCCS  on CCCE.CmpCallCardStatus_id = CCCS.CmpCallCardStatus_id

					left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = CCCE.CmpCallCard_id

					left join {$this->schema}.v_CmpCloseCard CLC  on CLC.CmpCallCard_id = CCC.CmpCallCard_id

					LEFT JOIN LATERAL(

						select *
						from v_CmpCallCardStatusTypeLink stl 

						where stl.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
						limit 1
					) CCCSTL ON TRUE
					left join v_CmpCallCardEventType CCCET  on CCCET.CmpCallCardEventType_id = CCCE.CmpCallCardEventType_id

					left join v_CmpReason CR  on CR.CmpReason_id = CCC.CmpReason_id

					left join v_LpuBuilding LB  on LB.LpuBuilding_id = CCC.LpuBuilding_id

					left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCCE.EmergencyTeam_id

					left join v_CmpCallCardStatusType CCCST  on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id

					left join v_pmUserCache PMC  on PMC.PMUser_id = CCCS.pmUser_insID

			WHERE
					" . implode(" and ", $filterC) . "

		) as sel
		WHERE " . implode(" and ", $gridC) . "
		ORDER BY CmpCallCard_Numv ASC
		";

		//echo getDebugSQL($sqlEventCalls, $params);
		$resEventCalls = $this->db->query($sqlEventCalls, $params)->result_array();

		foreach($resEventCalls as $evt){
			$rescalls[$evt['CmpCallCard_id']]['CmpCallCard_id'] = $evt['CmpCallCard_id'];
			$rescalls[$evt['CmpCallCard_id']]['CmpReason_Name'] = $evt['CmpReason_Name'];
			$rescalls[$evt['CmpCallCard_id']]['LpuBuilding_Name'] = $evt['LpuBuilding_Name'];
			$rescalls[$evt['CmpCallCard_id']]['CmpCallCard_Numv'] = $evt['CmpCallCard_Numv'];
			$rescalls[$evt['CmpCallCard_id']]['CmpCloseCard_id'] = $evt['CmpCloseCard_id'];
			$rescalls[$evt['CmpCallCard_id']]['events'][] = $evt;
		}

		$filterT[] = "ETSH.EmergencyTeamStatusHistory_insDT >= :begDate and ETSH.EmergencyTeamStatusHistory_insDT <= :endDate";
		$filterT[] = "ET.EmergencyTeam_id is not null";

		//фильтры грида бригад
		$gridT[] = '1 = 1';
		if(!empty($data['tEmergencyTeam_Num'])){
			$gridT[] = "sel.EmergencyTeam_Num ILIKE '%".$data['tEmergencyTeam_Num']."%'";
		}

		if(!empty($data['tLpuBuilding_Name'])){
			$gridT[] = "sel.LpuBuilding_Name ILIKE '%".$data['tLpuBuilding_Name']."%'";
		}

		$sqlStatTeams = "
		SELECT
			*
		FROM
		(
			SELECT
				to_char (ETSH.EmergencyTeamStatusHistory_insDT, 'DD Mon YYYY HH24:MI:SS' ) as EmergencyTeamStatusHistory_insDT,
				to_char(ETSH.EmergencyTeamStatusHistory_insDT, 'HH24:MI:SS') as EmergencyTeamStatusHistory_insTime,

				ETSH.EmergencyTeamStatusHistory_id as EmergencyTeamStatusHistory_id,
				ET.EmergencyTeam_id as EmergencyTeam_id,
				ET.EmergencyTeam_Num as EmergencyTeam_Num,
				LB.LpuBuilding_id as LpuBuilding_id,
				LB.LpuBuilding_Name as LpuBuilding_Name,
				ETS.EmergencyTeamStatus_Code as EmergencyTeamStatus_Code,
				ETS.EmergencyTeamStatus_Name as EmergencyTeamStatus_Name,
				COALESCE(CCC.CmpCallCard_id, '') as CmpCallCard_id,

				CCC.CmpCallCard_Numv,
				RTRIM(PMC.PMUser_surName||' '||SUBSTRING(PMC.PMUser_firName,1,1) ||'.'||SUBSTRING(PMC.PMUser_secName,1,1)||'.') as PMUser_Name

			FROM v_EmergencyTeamStatusHistory ETSH
				left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = ETSH.EmergencyTeam_id

				left join v_LpuBuilding LB  on LB.LpuBuilding_id = ET.LpuBuilding_id

				left join v_EmergencyTeamStatus ETS  on ETSH.EmergencyTeamStatus_id = ETS.EmergencyTeamStatus_id

				left join v_CmpCallCard CCC  on CCC.CmpCallCard_id = ETSH.CmpCallCard_id

				left join v_pmUserCache PMC  on PMC.PMUser_id = ETSH.pmUser_insID

			WHERE
					" . implode(" and ", $filterT) . "

		) as sel
		WHERE " . implode(" and ", $gridT) . "
		ORDER BY EmergencyTeam_Num ASC
		";
		$resStatTeams = $this->db->query($sqlStatTeams, $params)->result_array();

		foreach($resStatTeams as $stat){
			$resteams[$stat['EmergencyTeam_id']]['EmergencyTeam_id'] = $stat['EmergencyTeam_id'];
			$resteams[$stat['EmergencyTeam_id']]['EmergencyTeam_Num'] = $stat['EmergencyTeam_Num'];
			$resteams[$stat['EmergencyTeam_id']]['LpuBuilding_Name'] = $stat['LpuBuilding_Name'];
			$resteams[$stat['EmergencyTeam_id']]['stats'][] = $stat;

		}

		return array('calls' => $rescalls, 'teams' => $resteams, 'success' => true);
	}

	/**
	 * Загрузка вызовов для формы "Результат обслуживания НМП"
	 */
	function loadSelectNmpReasonWindow($data){

		$sql = "SELECT C.CmpCallCard_rid as \"CmpCallCard_rid\", CT.CmpCallType_Code as \"CmpCallType_Code\"
		FROM v_CmpCallCard  C

		 left join v_CmpCallType CT  on C.CmpCallType_id = CT.CmpCallType_id

		 WHERE CmpCallCard_id = :CmpCallCard_id limit 1";

		$primaryCall = $this->getFirstRowFromQuery($sql, $data);

		if(!empty($primaryCall['CmpCallCard_rid']) && $primaryCall['CmpCallType_Code'] == 4){
			$data['CmpCallCard_id'] = $primaryCall['CmpCallCard_rid'];
		}

		$sql = "
		SELECT
			CCC.CmpCallCard_id as \"CmpCallCard_id\"
			,CCC.CmpCallCard_Numv as \"CmpCallCard_Numv\"
			,CCC.CmpCallCard_Ngod as \"CmpCallCard_Ngod\"
			,ET.EmergencyTeam_id as \"EmergencyTeam_id\"
			,ET.EmergencyTeam_Num as \"EmergencyTeam_Num\"
			,COALESCE( CCC.Person_SurName, '') || ' ' || COALESCE(rtrim(CCC.Person_FirName), ' ' )|| ' ' || COALESCE( rtrim(CCC.Person_SecName), ' ') as \"Person_FIO\"
			,CCC.Person_Age as \"Person_Age\"
			,CCT.CmpCallType_Name as \"CmpCallType_Name\"
			,R.CmpReason_Name as \"CmpReason_Name\"
			,RES.CmpPPDResult_id as \"CmpPPDResult_id\"
			,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick||' '||SRGNCity.KLSubRgn_Name||', '
			else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick||' '||SRGNTown.KLSubRgn_Name||', '
			else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick||' '||SRGN.KLSubRgn_Name||', ' else '' end end end||
			case when City.KLCity_Name is not null then 'г. '||City.KLCity_Name else '' end||
			case when Town.KLTown_FullName is not null then
				case when City.KLCity_Name is not null then ', ' else '' end
				 ||COALESCE(LOWER(Town.KLSocr_Nick)||'. ','') || Town.KLTown_Name else ''

			end||

			case when Street.KLStreet_FullName is not null then
				case when socrStreet.KLSocr_Nick is not null then ', '||LOWER(socrStreet.KLSocr_Nick)||'. '||Street.KLStreet_Name else
				', '||Street.KLStreet_FullName  end
			else case when CCC.CmpCallCard_Ulic is not null then ', '||CmpCallCard_Ulic else '' end
			end ||

			case when SecondStreet.KLStreet_FullName is not null then
				case when socrSecondStreet.KLSocr_Nick is not null then ', '||LOWER(socrSecondStreet.KLSocr_Nick)||'. '||SecondStreet.KLStreet_Name else
				', '||SecondStreet.KLStreet_FullName end
				else ''
			end ||

			case when CCC.CmpCallCard_Dom is not null then ', д.'||CCC.CmpCallCard_Dom else '' end ||
			case when CCC.CmpCallCard_Korp is not null then ', к.'||CCC.CmpCallCard_Korp else '' end ||
			case when CCC.CmpCallCard_Kvar is not null then ', кв.'||CCC.CmpCallCard_Kvar else '' end ||
			case when CCC.CmpCallCard_Room is not null then ', ком. '||CCC.CmpCallCard_Room else '' end ||
			case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '||UAD.UnformalizedAddressDirectory_Name else ''
			end as \"CmpCallCard_Address\"

		FROM v_CmpCallCard CCC 

			left join v_CmpCallType CCT  on CCC.CmpCallType_id = CCT.CmpCallType_id

			left join v_EmergencyTeam ET  on ET.EmergencyTeam_id = CCC.EmergencyTeam_id

			left join v_CmpReason R  on R.CmpReason_id = CCC.CmpReason_id

			left join v_KLRgn RGN  on RGN.KLRgn_id = CCC.KLRgn_id

			left join CmpPPDResult RES  on RES.CmpPPDResult_id = CCC.CmpPPDResult_id

			left join v_KLSubRgn SRGN  on SRGN.KLSubRgn_id = CCC.KLSubRgn_id

			left join v_KLCity City  on City.KLCity_id = CCC.KLCity_id

			left join v_KLTown Town  on Town.KLTown_id = CCC.KLTown_id


			left join v_KLSubRgn SRGNCity  on SRGNCity.KLSubRgn_id = CCC.KLSubRgn_id

			left join v_KLSubRgn SRGNTown  on SRGNTown.KLSubRgn_id = CCC.KLSubRgn_id


			left join v_KLArea Area  on Area.KLArea_id = COALESCE(CCC.KLTown_id,CCC.KLCity_id)


			left join v_KLArea pArea  on pArea.KLArea_id = Area.KLArea_pid


			left join v_KLStreet Street  on Street.KLStreet_id = CCC.KLStreet_id

			left join v_KLSocr socrStreet  on Street.KLSocr_id = socrStreet.KLSocr_id


			left join v_KLStreet SecondStreet  on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond

			left join v_KLSocr socrSecondStreet  on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id


			left join v_UnformalizedAddressDirectory UAD  on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id


		WHERE CmpCallCard_id = :CmpCallCard_id or (CCC.CmpCallCard_rid = :CmpCallCard_id and CCT.CmpCallType_Code = 4) --Первичный и все попутные
		ORDER BY CmpCallCard_id asc
		";

		return $this->db->query($sql, $data)->result_array();

	}

	/**
	 * Сохранение карточки 112
	 */

	public function saveCmpCallCard112($data){
		$jsonParams = $this->getJsonParamsForTable($data, 'dbo', 'CmpCallCard112', function($column, $value) {
			if ($value == 'true') $value = 2;
			if ($value == 'false') $value = 1;
			return $value;
		});
		
		$queryParams = [
			'CmpCallCard112_id' => $data['CmpCallCard112_id'],
			'params' => json_encode(array_change_key_case($jsonParams, CASE_LOWER)),
			'pmUser_id' => $data['pmUser_id'],
		];

		$query = "
            select 
                CmpCallCard112_id as \"CmpCallCard112_id\",
                ErrCode as \"Error_Code\",
                ErrMessage as \"Error_Msg\"
			from p_CmpCallCard112_ins (
				CmpCallCard112_id := :CmpCallCard112_id,
				params := :params,
				pmUser_id := :params
			)
		";

		//var_dump(getDebugSQL($query, $queryParams)); exit;
		return $this->db->query( $query, $queryParams );
	}

	/**
	 * Смена типа вызова у попутных
	 * Отклонение бригады у попутных
	 * Связь попутных при отмене первичного
	 */
	public function cancelEmergencyTeamFromCalls($data){
		$calls = json_decode($data['calls'], true);

		if(count($calls) == 0) return false;

		//Первичный будет первым в списке
		$firstCall = $calls[0];
		$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');

		if($firstCall['reset']){
			//Если отменяется бригада с первичного вызова возьмем первый оставшийся попутный для связи далее

			foreach($calls as $call) {
				if(!$call['reset']){
					$followingCall_id = $call['CmpCallCard_id'];
					break;
				}
			}
		}


		$typeCardQuery = "SELECT 
                          cmpcalltype_id as \"cmpcalltype_id\",
                          cmpcalltype_code as \"cmpcalltype_code\",
                          cmpcalltype_name as \"cmpcalltype_name\",
                          pmuser_insid as \"pmuser_insid\",
                          pmuser_updid as \"pmuser_updid\",
                          cmpcalltype_insdt as \"cmpcalltype_insdt\",
                          cmpcalltype_upddt as \"cmpcalltype_upddt\",
                          region_id as \"region_id\",
                          cmpcalltype_begdate as \"cmpcalltype_begdate\",
                          cmpcalltype_enddate as \"cmpcalltype_enddate\",
                          cmpcalltype_rowversion as \"cmpcalltype_rowversion\"
                          FROM v_CmpCallType  WHERE CmpCallType_Code = :CmpCallType_Code LIMIT 1";

		$typeCard = $this->db->query($typeCardQuery, array('CmpCallType_Code' => 1))->row_array(); //Первичный

		foreach($calls as $call){
			//Вызов отмечен для отмены бригады или является первым попутным для связи с остальными попутными
			if(($call['reset'] && !empty($call['CmpCallCard_id']) && !empty($call['EmergencyTeam_id'])) ||
				(isset($followingCall_id) && $call['CmpCallCard_id'] == $followingCall_id)){

				//У первого попутного не нужно отменять бригаду
				if(!isset($followingCall_id) || $call['CmpCallCard_id'] != $followingCall_id){
					$this->EmergencyTeam_model4E->cancelEmergencyTeamFromCall(array('CmpCallCard_id' => $call['CmpCallCard_id'], 'EmergencyTeam_id' => $call['EmergencyTeam_id'], 'pmUser_id' => $data['pmUser_id'], 'ARMType_id' => $data['ARMType_id']));
				}

				//Меняем тип вызова на Первичный и обнуляем связь CmpCallCard_rid
				$updateParams = array(
					'CmpCallCard_id' => $call['CmpCallCard_id'],
					'CmpCallType_id' => $typeCard['CmpCallType_id'],
					'CmpCallCard_rid' => null,
					'pmUser_id' => $data['pmUser_id']
				);
				$this->swUpdate('CmpCallCard', $updateParams, false);

			}elseif($firstCall['reset']){
				//Если отмена у первичного вызова, то меняем связь для всех попутных
				$updateParams = array(
					'CmpCallCard_id' => $call['CmpCallCard_id'],
					'CmpCallCard_rid' => isset($followingCall_id) ? $followingCall_id : null,
					'pmUser_id' => $data['pmUser_id']
				);
				$this->swUpdate('CmpCallCard', $updateParams, false);
			}
		}

		return array();

	}

	/**
	 * Сохранение результата НМП для несольких вызовов, смена статуса вызова и бригады в зависимости от результата
	 */
	function setResultCmpCallCards($data){

		$calls = json_decode($data['calls'], true);

		if(count($calls) > 0) {

			$this->load->model('EmergencyTeam_model4E', 'EmergencyTeam_model4E');
			$etStatusId = $this->EmergencyTeam_model4E->getEmergencyTeamStatusIdByCode( 4 );

			$this->load->model('CmpCallCard_model', 'cccmodel');
			$ppdResults = $this->cccmodel->getResults();

			//Сформируем результаты по кодам, будем обращаться к ним внутри цикла
			foreach($ppdResults as $ppdResult){
				$ppdCodes[$ppdResult['CmpPPDResult_id']] = $ppdResult['CmpPPDResult_Code'];
			}

			foreach($calls as $call){
				if(!empty($call['CmpCallCard_id']) && !empty($call['CmpPPDResult_id'])){
					$this->cccmodel->setResult(array('CmpCallCard_id' => $call['CmpCallCard_id'], 'CmpPPDResult_id' => $call['CmpPPDResult_id'], 'pmUser_id' => $data['pmUser_id']));

					/**
					 * Коды для решения диспетчера
					 * 1 - Обслужен ППД, оставлен на месте
					 * 8 - Отказ от осмотра
					 * 9 - Адрес не найден
					 * 10 - Пациент не обращался
					 * 12 - Высокая занятость бригады НМП
					 */
					if(in_array($ppdCodes[$call['CmpPPDResult_id']], array(7,8,9,10,22))){
						$status = 13;
					}else{
						$status = 4;
					}

					$this->dbmodel->setStatusCmpCallCard(array(
						"CmpCallCard_id" => $call['CmpCallCard_id'],
						"CmpCallCardStatusType_Code" => $status,
						"CmpCallCardStatus_Comment" => '',
						"pmUser_id" => $data["pmUser_id"]
					));

					if(!empty($call["EmergencyTeam_id"])){
						//Меняем статус бригады на «Конец обслуживания»
						$this->EmergencyTeam_model4E->setEmergencyTeamStatus(array(
							"EmergencyTeam_id" => $call["EmergencyTeam_id"],
							"CmpCallCard_id" => $call["CmpCallCard_id"],
							"EmergencyTeamStatus_id" => $etStatusId,
							"pmUser_id" => $data["pmUser_id"]
						));
					}



				}
			}
		}

		return array();

	}


	/**
	 * Вывод вызова из отложенных
	 */
	function setDefferedCallToTransmitted($data){
		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');

		$cccquery = "
			SELECT 
            cmpcallcard_id as \"cmpcallcard_id\",
            cmpcallcard_numv as \"cmpcallcard_numv\",
            cmpcallcard_ngod as \"cmpcallcard_ngod\",
            cmpcallcard_prty as \"cmpcallcard_prty\",
            cmpcallcard_sect as \"cmpcallcard_sect\",
            cmparea_id as \"cmparea_id\",
            cmpcallcard_city as \"cmpcallcard_city\",
            cmpcallcard_ulic as \"cmpcallcard_ulic\",
            cmpcallcard_dom as \"cmpcallcard_dom\",
            cmpcallcard_kvar as \"cmpcallcard_kvar\",
            cmpcallcard_podz as \"cmpcallcard_podz\",
            cmpcallcard_etaj as \"cmpcallcard_etaj\",
            cmpcallcard_kodp as \"cmpcallcard_kodp\",
            cmpcallcard_telf as \"cmpcallcard_telf\",
            cmpplace_id as \"cmpplace_id\",
            cmpcallcard_comm as \"cmpcallcard_comm\",
            cmpreason_id as \"cmpreason_id\",
            person_id as \"person_id\",
            person_surname as \"person_surname\",
            person_firname as \"person_firname\",
            person_secname as \"person_secname\",
            person_age as \"person_age\",
            person_birthday as \"person_birthday\",
            person_polisser as \"person_polisser\",
            person_polisnum as \"person_polisnum\",
            sex_id as \"sex_id\",
            cmpcallcard_ktov as \"cmpcallcard_ktov\",
            cmpcalltype_id as \"cmpcalltype_id\",
            cmpprofile_cid as \"cmpprofile_cid\",
            cmpcallcard_smpt as \"cmpcallcard_smpt\",
            cmpcallcard_stan as \"cmpcallcard_stan\",
            cmpcallcard_prmdt as \"cmpcallcard_prmdt\",
            cmpcallcard_line as \"cmpcallcard_line\",
            cmpresult_id as \"cmpresult_id\",
            cmparea_gid as \"cmparea_gid\",
            cmplpu_id as \"cmplpu_id\",
            cmpdiag_oid as \"cmpdiag_oid\",
            cmpdiag_aid as \"cmpdiag_aid\",
            cmptrauma_id as \"cmptrauma_id\",
            cmpcallcard_isalco as \"cmpcallcard_isalco\",
            diag_uid as \"diag_uid\",
            cmpcallcard_numb as \"cmpcallcard_numb\",
            cmpcallcard_smpb as \"cmpcallcard_smpb\",
            cmpcallcard_stbr as \"cmpcallcard_stbr\",
            cmpcallcard_stbb as \"cmpcallcard_stbb\",
            cmpprofile_bid as \"cmpprofile_bid\",
            cmpcallcard_ncar as \"cmpcallcard_ncar\",
            cmpcallcard_rcod as \"cmpcallcard_rcod\",
            cmpcallcard_tabn as \"cmpcallcard_tabn\",
            cmpcallcard_dokt as \"cmpcallcard_dokt\",
            cmpcallcard_tab2 as \"cmpcallcard_tab2\",
            cmpcallcard_tab3 as \"cmpcallcard_tab3\",
            cmpcallcard_tab4 as \"cmpcallcard_tab4\",
            diag_sid as \"diag_sid\",
            cmptalon_id as \"cmptalon_id\",
            cmpcallcard_expo as \"cmpcallcard_expo\",
            cmpcallcard_smpp as \"cmpcallcard_smpp\",
            cmpcallcard_vr51 as \"cmpcallcard_vr51\",
            cmpcallcard_d201 as \"cmpcallcard_d201\",
            cmpcallcard_dsp1 as \"cmpcallcard_dsp1\",
            cmpcallcard_dsp2 as \"cmpcallcard_dsp2\",
            cmpcallcard_dspp as \"cmpcallcard_dspp\",
            cmpcallcard_dsp3 as \"cmpcallcard_dsp3\",
            cmpcallcard_kakp as \"cmpcallcard_kakp\",
            cmpcallcard_tper as \"cmpcallcard_tper\",
            cmpcallcard_vyez as \"cmpcallcard_vyez\",
            cmpcallcard_przd as \"cmpcallcard_przd\",
            cmpcallcard_tgsp as \"cmpcallcard_tgsp\",
            cmpcallcard_tsta as \"cmpcallcard_tsta\",
            cmpcallcard_tisp as \"cmpcallcard_tisp\",
            cmpcallcard_tvzv as \"cmpcallcard_tvzv\",
            cmpcallcard_kilo as \"cmpcallcard_kilo\",
            cmpcallcard_dlit as \"cmpcallcard_dlit\",
            cmpcallcard_prdl as \"cmpcallcard_prdl\",
            cmparea_pid as \"cmparea_pid\",
            cmpcallcard_pcity as \"cmpcallcard_pcity\",
            cmpcallcard_pulic as \"cmpcallcard_pulic\",
            cmpcallcard_pdom as \"cmpcallcard_pdom\",
            cmpcallcard_pkvar as \"cmpcallcard_pkvar\",
            cmplpu_aid as \"cmplpu_aid\",
            cmpcallcard_ispoli as \"cmpcallcard_ispoli\",
            cmpcallcard_medc as \"cmpcallcard_medc\",
            cmpcallcard_izv1 as \"cmpcallcard_izv1\",
            cmpcallcard_tiz1 as \"cmpcallcard_tiz1\",
            cmpcallcard_inf1 as \"cmpcallcard_inf1\",
            cmpcallcard_inf2 as \"cmpcallcard_inf2\",
            cmpcallcard_inf3 as \"cmpcallcard_inf3\",
            cmpcallcard_inf4 as \"cmpcallcard_inf4\",
            cmpcallcard_inf5 as \"cmpcallcard_inf5\",
            cmpcallcard_inf6 as \"cmpcallcard_inf6\",
            pmuser_insid as \"pmuser_insid\",
            pmuser_updid as \"pmuser_updid\",
            cmpcallcard_insdt as \"cmpcallcard_insdt\",
            cmpcallcard_upddt as \"cmpcallcard_upddt\",
            klrgn_id as \"klrgn_id\",
            klsubrgn_id as \"klsubrgn_id\",
            klcity_id as \"klcity_id\",
            kltown_id as \"kltown_id\",
            klstreet_id as \"klstreet_id\",
            lpu_ppdid as \"lpu_ppdid\",
            cmpcallcard_isemergency as \"cmpcallcard_isemergency\",
            cmpcallcard_isopen as \"cmpcallcard_isopen\",
            cmpcallcardstatustype_id as \"cmpcallcardstatustype_id\",
            cmpcallcardstatus_comment as \"cmpcallcardstatus_comment\",
            cmpcallcard_isreceivedinppd as \"cmpcallcard_isreceivedinppd\",
            cmpppdresult_id as \"cmpppdresult_id\",
            emergencyteam_id as \"emergencyteam_id\",
            cmpcallcard_isinreg as \"cmpcallcard_isinreg\",
            lpu_id as \"lpu_id\",
            cmpcallcard_ismedpersonalident as \"cmpcallcard_ismedpersonalident\",
            medpersonal_id as \"medpersonal_id\",
            resultdeseasetype_id as \"resultdeseasetype_id\",
            cmpcallcard_firstversion as \"cmpcallcard_firstversion\",
            unformalizedaddressdirectory_id as \"unformalizedaddressdirectory_id\",
            cmpcallcard_ispaid as \"cmpcallcard_ispaid\",
            cmpcallcard_korp as \"cmpcallcard_korp\",
            cmpcallcard_room as \"cmpcallcard_room\",
            cmpcallcard_difftime as \"cmpcallcard_difftime\",
            uslugacomplex_id as \"uslugacomplex_id\",
            lpubuilding_id as \"lpubuilding_id\",
            cmpcallertype_id as \"cmpcallertype_id\",
            cmpcallplacetype_id as \"cmpcallplacetype_id\",
            cmpcallcard_rid as \"cmpcallcard_rid\",
            cmpcallcard_urgency as \"cmpcallcard_urgency\",
            cmpcallcard_boosttime as \"cmpcallcard_boosttime\",
            cmpsecondreason_id as \"cmpsecondreason_id\",
            cmpdiseaseandaccidenttype_id as \"cmpdiseaseandaccidenttype_id\",
            cmpcallreasontype_id as \"cmpcallreasontype_id\",
            cmpreasonnew_id as \"cmpreasonnew_id\",
            cmpcallcard_emergencyteamdiscardreason as \"cmpcallcard_emergencyteamdiscardreason\",
            cmpcallcard_indexrep as \"cmpcallcard_indexrep\",
            cmpcallcard_indexrepinreg as \"cmpcallcard_indexrepinreg\",
            cmpcallcard_isarchive as \"cmpcallcard_isarchive\",
            medstafffact_id as \"medstafffact_id\",
            rankinscale_id as \"rankinscale_id\",
            rankinscale_sid as \"rankinscale_sid\",
            leavetype_id as \"leavetype_id\",
            cmpcallcard_isshorteditversion as \"cmpcallcard_isshorteditversion\",
            lpusection_id as \"lpusection_id\",
            cmpcallcard_recomendations as \"cmpcallcard_recomendations\",
            cmpcallcard_condition as \"cmpcallcard_condition\",
            lpu_cid as \"lpu_cid\",
            cmpcallcard_tend as \"cmpcallcard_tend\",
            cmpcallcard_callltd as \"cmpcallcard_callltd\",
            cmpcallcard_calllng as \"cmpcallcard_calllng\",
            cmpcallcard_isnmp as \"cmpcallcard_isnmp\",
            cmprejectionreason_id as \"cmprejectionreason_id\",
            cmpcallcard_hospitalizedtime as \"cmpcallcard_hospitalizedtime\",
            cmpcallcard_savedt as \"cmpcallcard_savedt\",
            cmpcallcard_plandt as \"cmpcallcard_plandt\",
            cmpcallcard_factdt as \"cmpcallcard_factdt\",
            cmpcallcardinputtype_id as \"cmpcallcardinputtype_id\",
            cmpcallcard_isextra as \"cmpcallcard_isextra\",
            cmpcallcardstatus_id as \"cmpcallcardstatus_id\",
            cmpcallcard_guid as \"cmpcallcard_guid\",
            cmpcallcard_rguid as \"cmpcallcard_rguid\",
            cmpcallcard_firstversionguid as \"cmpcallcard_firstversionguid\",
            cmpcallcardstatus_guid as \"cmpcallcardstatus_guid\",
            emergencyteam_guid as \"emergencyteam_guid\",
            cmpcallcard_stordt as \"cmpcallcard_stordt\",
            cmpcallcard_defcom as \"cmpcallcard_defcom\",
            medservice_id as \"medservice_id\",
            cmpcallcard_polisednum as \"cmpcallcard_polisednum\",
            cmpcallcard_isdeterior as \"cmpcallcard_isdeterior\",
            diag_sopid as \"diag_sopid\",
            cmpleavetype_id as \"cmpleavetype_id\",
            cmpleavetask_id as \"cmpleavetask_id\",
            cmpmedicalcarekind_id as \"cmpmedicalcarekind_id\",
            cmptransporttype_id as \"cmptransporttype_id\",
            cmpresultdeseasetype_id as \"cmpresultdeseasetype_id\",
            cmpcallcardresult_id as \"cmpcallcardresult_id\",
            person_isunknown as \"person_isunknown\",
            cmpcallcard_ispassssmp as \"cmpcallcard_ispassssmp\",
            lpu_smpid as \"lpu_smpid\",
            lpu_hid as \"lpu_hid\",
            unformalizedaddressdirectory_wid as \"unformalizedaddressdirectory_wid\",
            paytype_id as \"paytype_id\",
            cmpcallcard_ulicsecond as \"cmpcallcard_ulicsecond\",
            cmpcallcard_sid as \"cmpcallcard_sid\",
            cmpcallcard_isactivecall as \"cmpcallcard_isactivecall\",
            cmpcallcard_iscontrolcall as \"cmpcallcard_iscontrolcall\",
            cmpcallcard_istimeexceeded as \"cmpcallcard_istimeexceeded\",
            cmpcallcard_numvpr as \"cmpcallcard_numvpr\",
            cmpcallcard_ngodpr as \"cmpcallcard_ngodpr\",
            cmpcallsigntype_id as \"cmpcallsigntype_id\",
            lpu_codesmo as \"lpu_codesmo\",
            registry_sid as \"registry_sid\",
            diag_gid as \"diag_gid\",
            medicalcarebudgtype_id as \"medicalcarebudgtype_id\",
            cmpcommonstate_id as cmpcommonstate_id,
			to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'DD.MM.YYYY') || ' ' || to_char(cast(CCC.CmpCallCard_prmDT as timestamp(3)), 'HH24:MI:SS') as \"CmpCallCard_prmDT\"
			FROM v_CmpCallCard CCC 

			WHERE CCC.CmpCallCard_id = :CmpCallCard_id;
		";

		$cccresult = $this->db->query( $cccquery, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		) );

		if ( !is_object( $cccresult ) ) {
			return false;
		}
		$cccresult = $cccresult->result( 'array' );

		if(empty($cccresult[0]))return false;

		$cccresult = $cccresult[0];

		$statusRes = $this->dbmodel->setStatusCmpCallCard(array(
			"CmpCallCard_id" => $data['CmpCallCard_id'],
			"CmpCallCardStatusType_Code" => 1, //Передано
			"pmUser_id" => $data["pmUser_id"]
		));

		if(!empty($statusRes[0])){
			$timesRes = $this->dbmodel->saveCmpCallCardTimes(array(
				"CmpCallCard_id" => $data['CmpCallCard_id'],
				"CmpCallCard_prmDT" => $this->getCurrentDT()->format('Y-m-d H:i:s'),
				"pmUser_id" => $data["pmUser_id"]
			));

			if(!empty($cccresult['MedService_id'])) {
				$lpuBuilding = $this->dbmodel->getLpuBuildingByMedServiceId(array('MedService_id' => $cccresult['MedService_id']));

				if(!empty($lpuBuilding[0]['LpuBuilding_id'])){
					$this->load->model('LpuStructure_model', 'LpuStructure');
					$LpuBuildingData = $this->LpuStructure->getSmpUnitData(array("LpuBuilding_id"=>$lpuBuilding[0]['LpuBuilding_id']));
				}

				//был отложенный - и с сохранением вызова на дом
				if(
					//!empty($LpuBuildingData[0]['SmpUnitParam_IsAutoHome']) &&
					//($LpuBuildingData[0]['SmpUnitParam_IsAutoHome'] == "true") &&
					$cccresult['CmpCallCard_IsExtra'] == 3
					&& !empty($cccresult['CmpCallCard_storDT']) )//отложенные сохраняем потом
				{
					$this->load->model('CmpCallCard_model', 'CmpCallCard_model');
					$nums = $this->CmpCallCard_model->addHomeVisitFromSMP($cccresult);

					return $nums;
				}
			}
		}

		return false;
	}

	/**
	 * Список диспетчеров, управляющих подстанциями
	 * Или подстанций, по диспетчеру
	 */
	function getDispControlLpuBuilding($arData = array(), $type, $byCurArm = true){

		if(!is_array($arData) && intval($arData)){
			$arData = array($arData);
		}

		if(!is_array($arData) || count($arData) == 0) return array();

		switch($type){
			case 'LpuBuilding_id': {
				$filter = "suh.LpuBuilding_id in (" . implode(',',$arData) . ")";

				if($byCurArm){
					$filter .= " and MP.MedPersonal_id != :MedPersonal_id";
				}

				break;
			}
			case 'MedPersonal_id': {
				$filter = "MP.MedPersonal_id in (" . implode(',',$arData) . ")";
				break;
			}
			default: {
				return array();
			}
		}

		$sqlMP = "SELECT
					suh.LpuBuilding_id as \"LpuBuilding_id\"
					,MP.Person_Fin as \"Person_Fin\"
					,MP.MedPersonal_id as \"MedPersonal_id\"
					,LB.LpuBuilding_Name as \"LpuBuilding_Name\"
					,suh.SmpUnitHistory_id as \"SmpUnitHistory_id\"
					,suh.SmpUnitHistory_begDT as \"SmpUnitHistory_begDT\"
				FROM v_SmpUnitHistory suh 

				LEFT JOIN LATERAL (select 
                                    medpersonal_id as medpersonal_id,
                                    medpersonal_code as medpersonal_code,
                                    medpersonal_tabcode as medpersonal_tabcode,
                                    person_id as person_id,
                                    person_surname as person_surname,
                                    person_firname as person_firname,
                                    person_secname as person_secname,
                                    person_birthday as person_birthday,
                                    person_fio as person_fio,
                                    person_fin as person_fin,
                                    lpu_id as lpu_id,
                                    worktype_id as worktype_id,
                                    worktype_name as worktype_name,
                                    dolgnost_id as dolgnost_id,
                                    dolgnost_name as dolgnost_name,
                                    dolgnost_code as dolgnost_code,
                                    workdata_begdate as workdata_begdate,
                                    workdata_enddate as workdata_enddate,
                                    workdata_isdlo as workdata_isdlo,
                                    medpersonal_isnointernship as medpersonal_isnointernship,
                                    person_snils as person_snils,
                                    medpersonal_guid as medpersonal_guid,
                                    medpersonal_soccardnum as medpersonal_soccardnum
                                   from v_MedPersonal  where MedPersonal_id =  MedPersonal_id limit 1) as MP ON TRUE


				left join v_LpuBuilding LB  on LB.LpuBuilding_id =  suh.LpuBuilding_id

				WHERE {$filter} and suh.SmpUnitHistory_endDT is null";

		return $this->queryResult( $sqlMP, array('MedPersonal_id' => $_SESSION['CurARM']['MedPersonal_id']) );
	}

	/**
	 * Сохранение истории управления подстанциями
	 */
	function saveSmpUnitHistory($data){

		if(empty($data['LpuBuilding_id']) || empty($data['MedPersonal_id'])) return false;

		$SmpUnitHistory_id = null;
		$procedure = 'p_SmpUnitHistory_ins';


		//Если передан SmpUnitHistory_id закрываем управление подстанцией
		if(!empty($data['SmpUnitHistory_id'])){
			$procedure = 'p_SmpUnitHistory_upd';
			$SmpUnitHistory_id = $data['SmpUnitHistory_id'];
			$data['SmpUnitHistory_endDT'] = $this->getCurrentDT()->format('Y-m-d H:i:s');
		}else{
			$SmpUnitHistory_id = 'Res';
			$data['SmpUnitHistory_begDT'] = $this->getCurrentDT()->format('Y-m-d H:i:s');
		}

		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, array('SmpUnitHistory_id'), false);
		$genQueryParams = $genQuery["paramsArray"];
		$genQuerySQL = $genQuery["sqlParams"];

		$sql = "
            select 
                Res as \"SmpUnitHistory_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				SmpUnitHistory_id := {$SmpUnitHistory_id},
				{$genQuerySQL}
				)
            ";
				
		return $this->db->query( $sql, $genQueryParams );
	}

	/**
	 * Обновление данных об управлении подстанциями
	 */
	function updateSmpUnitHistoryData($data){

		if((!is_array($data['lpuBuildings']) || count($data['lpuBuildings']) == 0) && !$data['closeAll']){

			$user = pmAuthUser::find($_SESSION['login']);
			$settings = @unserialize($user->settings);

			$data['lpuBuildings'] = $settings['lpuBuildingsWorkAccess'];
		}

		$smpUnitHistory = $this->getDispControlLpuBuilding($data['session']['CurARM']['MedPersonal_id'], 'MedPersonal_id');

		foreach($smpUnitHistory as $item){
			$key = array_search($item['LpuBuilding_id'], $data['lpuBuildings']);

			if($key !== false){
				//Если подстанция уже под управлением, то пропускаем
				unset($data['lpuBuildings'][$key]);
			}else{
				//Закрываем контроль подстанцией
				$this->saveSmpUnitHistory(array(
					'SmpUnitHistory_id' => $item['SmpUnitHistory_id'],
					'LpuBuilding_id' => $item['LpuBuilding_id'],
					'MedPersonal_id' => $item['MedPersonal_id'],
					'SmpUnitHistory_begDT' => $item['SmpUnitHistory_begDT'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}


		foreach($data['lpuBuildings'] as $item){
			//Добавляем контроль подстанцией
			$this->saveSmpUnitHistory(array(
				'LpuBuilding_id' => $item,
				'MedPersonal_id' => $data['session']['CurARM']['MedPersonal_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return $smpUnitHistory;
	}
	
	/**
	 * @desc Проверка перед сохранением карты(талона) вызова
	 */
	function checkSaveCmpCallCard( $data, $cccConfig = null ){
		$procedure = '';
		$statuschange = true;

		$query = "select CmpCallCardStatusType_id as \"CmpCallCardStatusType_id\" from v_CmpCallCard where CmpCallCard_id= :CmpCallCard_id";
		
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$retrun = $result->result('array');
			return $retrun;
		} else {
			return false;
		}
				
	}	
}