<?php	defined('BASEPATH') or die ('No direct script access allowed');

require(APPPATH.'models/CmpCallCard_model.php');

class Astra_CmpCallCard_model extends CmpCallCard_model {
	/**
	 * Удаление карты вызова
	 * 
	 * @param array $data
	 * @return bool
	 */
	function deleteCmpCallCard($data = array(), $ignoreRegistryCheck = false, $delCallCard = true) {
		if ( !array_key_exists( 'CmpCallCard_id', $data ) || !$data['CmpCallCard_id'] ) {
			return array( array( 'Error_Msg' => 'Не указан идентификатор карты вызова.' ) );
		}

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Невозможно удалить. Карта вызова редактируется' ) );
		}
		//для астрахани пока не проверяем на реестры
		// if ((isset($data['session']))&&(isset($data['session']['region']))&&(isset($data['session']['region']['number']))) {
		// Проверку наличия карты вызова в реестре
			// $this->load->database('registry');
			// $this->load->model('Registry_model', 'Registry_model');
			// $result = $this->Registry_model->checkCmpCallCardInRegistry($data);
			// if ( !is_bool( $result ) ) {
				// return $result;
			// } else if ( $result === true ) {
				// return array( array( 'Error_Msg' => 'Карта вызова включена в реестр и не может быть удалена.' ) );
			// }
		// }
		// Загружаем обратно БД по умолчанию
		// $this->load->database();
		
		$sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_CmpCallCard_del
				@CmpCallCard_id = :CmpCallCard_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$sqlArr = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		$resultStatus = $this->db->query( $sql, $sqlArr );
		
		// if ( !is_object( $result ) ) {
			// return array( array( 'Error_Msg' => 'Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.' ) );		
		// }
		
		
		$sql = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_CmpCallCard_del
				@CmpCallCard_id = :CmpCallCard_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$sqlArr = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$result = $this->db->query( $sql, $sqlArr );
		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
			return array( array( 'Error_Msg' => 'Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.' ) );
		}
	}
	
	
	/**
	 * default desc
	 */
	/*
	function getComboRel($CmpCloseCard, $SysName) {

		$query = "
			select
				CMB.CmpCloseCardCombo_id
				,CMB.CmpCloseCardCombo_Code
				,CMB.ComboName				
			from
				{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)				
			where
				CMB.Parent_id = '0'
				AND CMB.ComboSys = :combo_id			
		";


		$queryParams = array('combo_id' => $SysName);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');

			$comboid = $res[0]['CmpCloseCardCombo_id'];

			$query = "
				select
					--CCombo.ComboSys,
					Ccombo.CmpCloseCardCombo_id,
					Ccombo.CmpCloseCardCombo_Code,
					CCombo.ComboName,
					RL.Localize
					,CASE WHEN ISNULL(RL.CmpCloseCardRel_id,0) = 0 THEN 0 ELSE 1 END as flag
				from
					{$this->comboSchema}.v_CmpCloseCardCombo CCombo (nolock)
					LEFT JOIN {$this->schema}.v_CmpCloseCardRel RL with (nolock) on RL.CmpCloseCard_id = :CmpCloseCard_id
						and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id					
				where
					CCombo.Parent_id = :ComboId
					order by CCombo.CmpCloseCardCombo_ItemSort asc
			";

			$queryParams = array(
				'CmpCloseCard_id' => $CmpCloseCard,
				'ComboId' => $comboid
			);


			$result = $this->db->query($query, $queryParams);


			if ( is_object($result) ) {
				$result = $result->result('array');

				$content = '';
				$wrapper = '<div class="wrapper110 '; // не закрываем тег, ибо можем пихнуть сюда класс

				foreach ($result as $res) {
					if ($res['CmpCloseCardCombo_Code'] == '111') {
						
						$queryChildOfChilds = "
							select
								Ccombo.CmpCloseCardCombo_Code,
								CCombo.ComboName,
								RL.Localize
								,CASE WHEN ISNULL(RL.CmpCloseCardRel_id,0) = 0 THEN 0 ELSE 1 END as flag
								,L.Lpu_Nick
							from
								{$this->comboSchema}.v_CmpCloseCardCombo CCombo (nolock)
								LEFT JOIN {$this->schema}.v_CmpCloseCardRel RL with (nolock) on RL.CmpCloseCard_id = :CmpCloseCard_id
									and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id		
								LEFT JOIN v_Lpu as L (nolock) on L.Lpu_id = RL.Localize
							where
								CCombo.Parent_id = :ComboId
								and CCombo.CmpCloseCardCombo_Code = 693
						";

						$queryParamsChildOfChilds = array(
							'CmpCloseCard_id' => $CmpCloseCard,
							'ComboId' => $res['CmpCloseCardCombo_id']
						);
						
						$needVisitPacientLpuNick = null;
						$resultChildOfChilds = $this->db->query($queryChildOfChilds, $queryParamsChildOfChilds);

						if ( is_object($resultChildOfChilds) ) {
							$resultChildOfChilds = $resultChildOfChilds->result('array');							
							if (count($resultChildOfChilds) > 0) $needVisitPacientLpuNick = $resultChildOfChilds[0]['Lpu_Nick'];							
						}
					}
					if ($res['CmpCloseCardCombo_Code'] == '854' && ($res['flag'] == 1)) {
						$qPersonFio = "
							select Fam + ' ' + SUBSTRING(Name, 1, 1) + ' ' + SUBSTRING(Middle, 1, 1) as fio
							from v_CmpCloseCard where CmpCloseCard_id = :CmpCloseCard_id
						";
						$qPersonFioParams = array(
							'CmpCloseCard_id' => $CmpCloseCard
						);
						$PersonFio = null;
						
						$resultPersonFio = $this->db->query($qPersonFio, $qPersonFioParams);

						if ( is_object($resultPersonFio) ) {
							$resultPersonFio = $resultPersonFio->result('array');							
							if (count($resultPersonFio) > 0) $PersonFio = $resultPersonFio[0]['fio'];							
						}
					}

					// повесим спец. класс на родительский WRAPPER, чтобы определить что выбрано "Другое"
					if ($res['ComboName'] == 'Другое'  && $res['flag'] == 1) {
						$wrapper .= 'other-selected';
					}

				    $fflag = (($res['flag'] == 1)?'<div class="v_ok"></div>':'<div class="v_no"></div>');
					if ($SysName == 'AgeType_id') {
						if ($res['flag'] == 1) $content .= $res['ComboName'].' <u>'.$res['Localize'].'</u>';
					} else {
						switch ($res['CmpCloseCardCombo_Code']){
							case '111' : {
								if($res['flag'] == 1)
									$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag.' №: <u>'.$needVisitPacientLpuNick.'</u>';
								else
									$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag.'<u>'.$res['Localize'].'</u></div>';
								break;
							}
							case '853' : {
								$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag.'<u>'.$res['Localize'].'</u>';
								break;
							}
							case '854' : {
								if($res['flag'] == 1)
									$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag .'<u>'. $PersonFio.'</u></div>';
								else
									$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag.'<u>'.$res['Localize'].'</u></div>';
								break;
							}
							default: {
								$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag.'<u>'.$res['Localize'].'</u></div>';
							}
						}
					}
					if ($SysName == 'ResultUfa_id') {
						$query2 = "
							select
								--CCombo.ComboSys,
								CCombo.ComboName,
								CCombo.CmpCloseCardCombo_id,
								CCombo.CmpCloseCardCombo_Code,
								--CASE WHEN CCombo.CmpCloseCardCombo_id in ('242','245','246','247','248'	) THEN convert(varchar(100), RL.Localize, 108) ELSE RL.Localize END as Localize,
								RL.Localize,
								CASE WHEN ISNULL(RL.CmpCloseCardRel_id,0) = 0 THEN 0 ELSE 1 END as flag
							from
								{$this->comboSchema}.v_CmpCloseCardCombo CCombo (nolock)
							LEFT JOIN {$this->schema}.v_CmpCloseCardRel RL with (nolock) on RL.CmpCloseCard_id = :CmpCloseCard_id
								and RL.CmpCloseCardCombo_id = CCombo.CmpCloseCardCombo_id
							where
								CCombo.Parent_id = :ComboId";
						$queryParams2 = array(
							'CmpCloseCard_id' => $CmpCloseCard,
							'ComboId' => $res['CmpCloseCardCombo_id']
						);
						$result2 = $this->db->query($query2, $queryParams2);
						if ( is_object($result2) ) {
							$result2 = $result2->result('array');
							foreach ($result2 as $res2) {

								if ($res2['CmpCloseCardCombo_Code'] == '241') {
									$query4 = "select L.Lpu_Name from v_Lpu as L (nolock) where L.Lpu_id = :Lpuid";
									$queryParams4 = array('Lpuid' => (int)$res2['Localize']);
									$result4 = $this->db->query($query4, $queryParams4);
									if ( is_object($result4) ) {
										$result4 = $result4->result('array');
										if (count($result4) > 0) $res2['Localize'] = $result4[0]['Lpu_Name'];
									}
								}
								if ($res2['CmpCloseCardCombo_Code'] == '243') {
									$query4 = "select D.Diag_FullName from v_Diag as D (nolock) where D.Diag_id = :Diagid";
									$queryParams4 = array('Diagid' => (int)$res2['Localize']);
									$result4 = $this->db->query($query4, $queryParams4);
									if ( is_object($result4) ) {
										$result4 = $result4->result('array');
										if (count($result4) > 0) $res2['Localize'] = $result4[0]['Diag_FullName'];
									}
								}

								$fflag2 = (($res2['flag'] == 1)?'<div class="v_ok"></div>':'<div class="v_no"></div>');
								if (strpos($res2['Localize'],'GMT+') > 1) $res2['Localize']=$this->peopleDate($res2['Localize']);
								$content .= '<div class="innerwrapper">'.$res2['ComboName'].' '.$fflag2.'<u>'.$res2['Localize'].'</u></div>';
							}
						}
					}

				}

				$content .= '</div>';

				$wrapper .= '">';
				$content = $wrapper.$content;


				return $content;
			} else {
				return false;
			}
		}
	}
	*/
	/**
	 * Возвращает данные для печати карты закрытия вызова 110у
	 *
	 * @params array $data
	 * @return array or false
	 */
	public function printCmpCloseCard110( $data ){
		$sql = "
			SELECT TOP 1
				CLC.CmpCallCard_id,
				CLC.CmpCloseCard_id,
				--CC.CmpCallCard_Numv as Day_num,
				--CC.CmpCallCard_Ngod as Year_num,
				CLC.Day_num,
				CLC.Year_num,
				--convert(varchar, CLC.CmpCloseCard_insDT, 104) as CardDate,
				convert(varchar, CC.CmpCallCard_insDT, 104) as CallCardDate,
				CLC.Feldsher_id,
				CASE WHEN ISNULL(CLC.LpuBuilding_id,0) > 0 THEN LB.LpuBuilding_Name ELSE CLC.StationNum END as StationNum,
				COALESCE(CLC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as EmergencyTeamNum,
				convert(varchar(5), CLC.AcceptTime, 108) as AcceptTime,
				convert(varchar, CLC.AcceptTime, 104) as AcceptDate,
				convert(varchar(5), CLC.TransTime, 108) as TransTime,
				convert(varchar(5), CLC.GoTime, 108) as GoTime,
				convert(varchar(5), CLC.ArriveTime, 108) as ArriveTime,
				convert(varchar(5), CLC.TransportTime, 108) as TransportTime,
				convert(varchar(5), CLC.ToHospitalTime, 108) as ToHospitalTime,
				convert(varchar(5), CLC.EndTime, 108) as EndTime,
				convert(varchar(5), CLC.BackTime, 108) as BackTime,
				CLC.SummTime,
				COALESCE( CLC.Person_PolisSer, CC.Person_PolisSer, PS.Polis_Ser, null) as Person_PolisSer,
				COALESCE( CLC.Person_PolisNum, CC.Person_PolisNum, PS.Polis_Num, null) as Person_PolisNum,
				CLC.Area_id,
				KL_AR.KLArea_Name as Area,
				CLC.City_id,
				KL_CITY.KLArea_Name as City,
				CLC.Town_id,
				KL_TOWN.KLArea_Name as Town,
				CLC.Street_id,

				CASE WHEN ISNULL(CLC.Street_id,0) > 0 THEN KL_ST.KLStreet_Name ELSE ClC.CmpCloseCard_Street END as Street,

				case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then UPPER(socrSecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
					SecondStreet.KLStreet_FullName end
					else ''
				end as secondStreetName,

				CLC.House,
				Lpu.Lpu_name,
				Lpu.UAddress_Address,
				Lpu.Lpu_Phone,
				CLC.Korpus,
				CLC.Room,
				CLC.Office,
				CLC.Entrance,
				CLC.Level,
				CLC.CodeEntrance,
			    CLC.CallPovodNew_id,
				CLC.CmpCallPlaceType_id,
				CLC.Fam,
				CLC.Name,
				CLC.Middle,
				CLC.Age,
				COALESCE(CLC.Person_Snils,PS.Person_Snils) as Person_Snils,
				SX.Sex_name,
				RS.CmpReason_Name as Reason,
			
				CLC.Work,
				CLC.DocumentNum,
				CLC.Ktov,
				COALESCE(CCrT.CmpCallerType_Name,CLC.Ktov) as CmpCallerType_Name,
				CLC.Phone,

				CLC.FeldsherAccept,
				CLC.FeldsherTrans,

				--RTRIM(PMCA.PMUser_Name) as FeldsherAcceptName,
				RTRIM(MPA.Person_Fio) as FeldsherAcceptName,
				--RTRIM(PMCT.PMUser_Name) as FeldsherTransName,
				RTRIM(MPT.Person_Fio) as FeldsherTransName,
			    MPA.MedPersonal_TabCode as FeldsherAccept_TabCode ,

				CLC.CallType_id,
				CCT.CmpCallType_Name as CallType,
				CCT.CmpCallType_Code,
				COALESCE(CLC.isAlco,1) as isAlco,
				CLC.CmpCloseCard_IsSignList,
				CLC.Complaints,
				CLC.Anamnez,
				CASE WHEN ISNULL(CLC.isMenen,1) = 2 THEN 'Да' ELSE 'Нет' END as isMenen,
				CASE WHEN ISNULL(CLC.isNist,1) = 2 THEN 'Да' ELSE 'Нет' END as isNist,
				CASE WHEN ISNULL(CLC.isAnis,1) = 2 THEN 'Да' ELSE 'Нет' END as isAnis,
				CASE WHEN ISNULL(CLC.isLight,1) = 2 THEN 'Да' ELSE 'Нет' END as isLight,
				CASE WHEN ISNULL(CLC.isAcro,1) = 2 THEN 'Да' ELSE 'Нет' END as isAcro,
				CASE WHEN ISNULL(CLC.isMramor,1) = 2 THEN 'Да' ELSE 'Нет' END as isMramor,
				
				COALESCE(CLC.isHale,1) as isHale,
				COALESCE(CLC.isPerit,1) as isPerit,

				CASE WHEN ISNULL(CLC.isSogl,1) = 2 THEN 'Да' ELSE CASE WHEN ISNULL(CLC.isSogl,2) = 1 THEN 'Нет' ELSE '' END END as isSogl,
				CASE WHEN ISNULL(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE CASE WHEN ISNULL(CLC.isOtkazMed,2) = 1 THEN 'Нет' ELSE '' END END as isOtkazMed,
				CASE WHEN ISNULL(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE CASE WHEN ISNULL(CLC.isOtkazHosp,2) = 1 THEN 'Нет' ELSE '' END END as isOtkazHosp,

				CLC.Urine,
				CLC.Shit,
				CLC.OtherSympt,
				CLC.CmpCloseCard_AddInfo,
				CLC.WorkAD,
				CLC.AD,
				CLC.Chss,
				CLC.Pulse,
				CLC.Temperature,
				CLC.Chd,
				CLC.Pulsks,
				CLC.Gluck,
				CLC.LocalStatus,
				CLC.Ekg1,
				convert(varchar(5), CLC.Ekg1Time, 108) as Ekg1Time,
				CLC.Ekg2,
				convert(varchar(5), CLC.Ekg2Time, 108) as Ekg2Time,
				CLC.Diag_id,
				CLC.Diag_uid,
				DIAG.Diag_FullName as Diag,
				DIAG.Diag_Code as CodeDiag,
				UDIAG.Diag_FullName as uDiag,
				UDIAG.Diag_Code as uCodeDiag,
				CLC.HelpPlace,
				CLC.HelpAuto,
				CLC.CmpCloseCard_ClinicalEff,
				CLC.EfAD,
				CLC.EfChss,
				CLC.EfPulse,
				CLC.EfTemperature,
				CLC.EfChd,
				CLC.EfPulsks,
				CLC.EfGluck,
				CLC.Kilo,
				CLC.DescText,
				CLC.CmpCloseCard_Epid,
				CLC.CmpCloseCard_Glaz,
				CLC.CmpCloseCard_GlazAfter,
				CLC.CmpCloseCard_m1,
				CLC.CmpCloseCard_e1,
				CLC.CmpCloseCard_v1,
				CLC.CmpCloseCard_m2,
				CLC.CmpCloseCard_e2,
				CLC.CmpCloseCard_v2,
				CLC.CmpCloseCard_Topic,
				CC.CmpTrauma_id
			FROM
				{$this->schema}.v_CmpCloseCard CLC with (nolock)
				LEFT JOIN v_Sex SX with (nolock) on SX.Sex_id = CLC.Sex_id
				--LEFT JOIN v_pmUserCache PMCA with (nolock) on PMCA.PMUser_id = CLC.FeldsherAccept
				LEFT JOIN v_MedPersonal MPA with (nolock) on MPA.MedPersonal_id = CLC.FeldsherAccept
				--LEFT JOIN v_pmUserCache PMCT with (nolock) on PMCT.PMUser_id = CLC.FeldsherTrans
				LEFT JOIN v_MedPersonal MPT with (nolock) on MPT.MedPersonal_id = CLC.FeldsherTrans
				LEFT JOIN v_PersonState PS (nolock) on PS.Person_id = CLC.Person_id
				LEFT JOIN v_CmpReason RS with (nolock) on RS.CmpReason_id = CLC.CallPovod_id
				LEFT JOIN KLStreet KL_ST with (nolock) on KL_ST.KLStreet_id = CLC.Street_id
				LEFT JOIN v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CLC.CmpCloseCard_UlicSecond
				LEFT JOIN v_KLSocr socrSecondStreet with (nolock) on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				LEFT JOIN KLArea KL_AR with (nolock) on KL_AR.KLArea_id = CLC.Area_id
				LEFT JOIN KLArea KL_CITY with (nolock) on KL_CITY.KLArea_id = CLC.City_id
				LEFT JOIN KLArea KL_TOWN with (nolock) on KL_TOWN.KLArea_id = CLC.Town_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CLC.CallType_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id = CLC.CmpCallerType_id
				LEFT JOIN v_Diag DIAG (nolock) on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_Diag UDIAG (nolock) on UDIAG.Diag_id = CLC.Diag_uid
				LEFT JOIN v_CmpCallCard CC (nolock) on CC.CmpCallCard_id = CLC.CmpCallCard_id
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CC.EmergencyTeam_id
				LEFT JOIN v_Lpu Lpu (nolock) on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = CLC.LpuBuilding_id
			WHERE
				CLC.CmpCallCard_id = :CmpCallCard_id
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
	 /*
	function loadCmpCloseCardEditForm($data) {
		
		$queryParams = array();
		$filter = '(1 = 1)';
		$filter .=" and CCC.CmpCallCard_id = :CmpCallCard_id";
		
		$query = "
			select top 1
				'' as accessType,
				CCC.CmpCallCard_id,
				CCC.CmpCallCard_Numv as Day_num,
				CCC.CmpCallCard_Ngod as Year_num,
				RTRIM(PMC.PMUser_Name) as FeldsherAcceptName,
				RTRIM(PMC.PMUser_Name) as Feldsher_id,
				--CCC.pmUser_updID as FeldsherAcceptName,
				CCC.CmpCallCard_IsAlco as isAlco,
				CCC.CmpCallType_id as CallType_id,
				CCC.CmpReason_id,
				CCC.CmpReasonNew_id as CallPovodNew_id,
				CCC.Sex_id,
				CCC.KLSubRgn_id as Area_id,
				CCC.KLCity_id as City_id,
				CCC.KLTown_id as Town_id,
				CCC.KLStreet_id as Street_id,
				CCC.CmpCallCard_Dom as House,
				CCC.CmpCallCard_Korp as Korpus,
				CCC.CmpCallCard_Room as Room,
				CCC.CmpCallCard_Kvar as Office,
				CCC.CmpCallCard_Podz as Entrance,
				CCC.CmpCallCard_Etaj as Level,
				CCC.CmpCallCard_Kodp as CodeEntrance,
				CCC.CmpCallCard_Telf as Phone,				
				CCC.CmpCallCard_Comm as DescText,			
				
				case when PS.Document_Ser is not null then PS.Document_Ser end + ' ' +
				case when PS.Document_Num is not null then PS.Document_Num end						
				as DocumentNum,
				

				org1.Org_Name as Work,
				dbfss.SocStatus_SysNick as SocStatusNick,
				
				case
					when CCC.Person_Age > 0 then 219
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 365 then 219
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 31 then 220					
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 0 then 221
					else 219
				end as AgeType_id2,
				
				case
					when CCC.Person_Age > 0 then CCC.Person_Age
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 365 then DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT)/365
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 31 then DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT)/31					
					when DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT) > 0 then DATEDIFF(DAY, CCC.Person_BirthDay, CCC.CmpCallCard_insDT)					
					else null
				end as Age,
				


				CCC.CmpReason_id as CallPovod_id,
				
				--cast(CCC.CmpCallCard_prmDT as varchar) as AcceptTime,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104)+' '+convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as AcceptTime,
				convert(varchar(10), CCCStatusData.TransTime, 104)+' '+convert(varchar(5),CCCStatusData.TransTime,108) as TransTime,

				ISNULL(PS.Person_Surname, case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end) as Fam,
				ISNULL(PS.Person_Firname, case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end) as Name,
				ISNULL(PS.Person_Secname, case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end) as Middle,
			
				CCC.CmpCallCard_Ktov as Ktov,
				CCrT.CmpCallerType_id
				,CCC.Lpu_id
				,CCC.Lpu_ppdid
				,ISNULL(L.Lpu_Nick,'') as CmpLpu_Name				
				,CCC.KLRgn_id
				,CCC.KLSubRgn_id
				,CCC.KLCity_id
				,CCC.KLTown_id
				,CCC.KLStreet_id			
				
				,EMT.EmergencyTeam_Num as EmergencyTeamNum
				,L.Lpu_Nick as StationNum
				,PMCins.MedPersonal_id as FeldsherAccept
				,CCCStatusData.FeldsherTransPmUser_id as FeldsherTrans,

				CONVERT( varchar, CCC.CmpCallCard_TEnd, 104 ) + ' ' + SUBSTRING( CONVERT( varchar, CCC.CmpCallCard_TEnd, 108 ), 0, 6 ) as EndTime
				
			from
				v_CmpCallCard CCC with (nolock)
				left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				left join v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
				left join v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
				left join SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CCC.Lpu_id
				LEFT join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT join v_pmUserCache PMCins with (nolock) on PMCins.PMUser_id = CCC.pmUser_insID		
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
				outer apply (
					select top 1
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS with (nolock) 
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
				) as CCCStatusData
			where
			{$filter}				
		";
		
		//LEFT JOIN v_pmUser P with (nolock) on P.PMUser_id = CCC.pmUser_updID
			
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']		
		));

		if ( is_object($result) ) {
			//var_dump($result->result('array')); exit;
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	*/
	/**
	 * default desc 
	 */
	/*
	function loadCmpCloseCardViewForm($data) {
	
		$filter = "FALSE";
		$queryParams = array();
		
		if (!empty($data['CmpCallCard_id'])) {
			$filter = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		} elseif (!empty($data['CmpCloseCard_id'])) {
			$filter = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$queryParams['CmpCloseCard_id'] = $data['CmpCloseCard_id'];
		}
		
		$query = "
			select top 1
				CClC.CmpCallCard_id,
				--CClC.CmpCallCard_IsAlco as isAlco,
				CClC.CmpCloseCard_id,
				CCC.CmpReason_id,				
				CClC.Year_num,				
				CClC.Day_num,				
				CClC.Sex_id,				
				CClC.Area_id,
				CClC.City_id,
				CClC.Town_id,
				CClC.Street_id,
				CClC.House,
				CClC.Office,
				CClC.Entrance,
				CClC.Level,
				CClC.CodeEntrance,
				CClC.Phone,				
				CClC.DescText,
				CClC.Fam,
                CClC.Name,
                CClC.Middle,
				CClC.Age,
				CCC.Person_id,
				CClC.Ktov,
				CClC.CmpCallerType_id,
				CClC.PayType_id,

				CCC.KLRgn_id
				,CCC.KLSubRgn_id as Area_id
				,CCC.KLCity_id as City_id
				,CCC.KLTown_id as Town_id
				,CCC.KLStreet_id  as Street_id
				,CClC.Room	
				,CClC.Korpus	
				
				,CClC.EmergencyTeamNum as EmergencyTeamNum
				,CCLC.EmergencyTeam_id as EmergencyTeam_id
				
				,CClC.EmergencyTeamSpec_id as EmergencyTeamSpec_id
				,CClC.LpuSection_id as LpuSection_id
				,CClC.MedPersonal_id as MedPersonal_id
				,CClC.MedStaffFact_id as MedStaffFact_id
		
				,CClC.StationNum as StationNum
				,CClC.LpuBuilding_id
				,CClC.pmUser_insID as Feldsher_id
				--,CClC.pmUser_insID as FeldsherAccept
				,CCLC.FeldsherAccept
				,CClC.FeldsherTrans
				
		
				,convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), cast(CClC.AcceptTime as datetime), 108) as AcceptTime
				,convert(varchar(10), CClC.TransTime, 104)+' '+convert(varchar(5), cast(CClC.TransTime as datetime), 108) as TransTime
				,convert(varchar(10), CClC.GoTime, 104)+' '+convert(varchar(5), cast(CClC.GoTime as datetime), 108) as GoTime
				
				,convert(varchar(10), CClC.ArriveTime, 104)+' '+convert(varchar(5), cast(CClC.ArriveTime as datetime), 108) as ArriveTime
				,convert(varchar(10), CClC.TransportTime, 104)+' '+convert(varchar(5), cast(CClC.TransportTime as datetime), 108) as TransportTime
				,convert(varchar(10), CClC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(CClC.ToHospitalTime as datetime), 108) as ToHospitalTime
				,convert(varchar(10), CClC.EndTime, 104)+' '+convert(varchar(5), cast(CClC.EndTime as datetime), 108) as EndTime
				,convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), cast(CClC.BackTime as datetime), 108) as BackTime
		
				,CClC.SummTime
				,CClC.Work
				,CClC.DocumentNum
				,CClC.CallType_id
				,CClC.CallPovod_id
				,CClC.CallPovodNew_id				
				,CASE WHEN ISNULL(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as isAlco								
				,CClC.Complaints
				,CClC.Anamnez
				,CASE WHEN ISNULL(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as isMenen
				,CASE WHEN ISNULL(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as isAnis
				,CASE WHEN ISNULL(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as isNist
				,CASE WHEN ISNULL(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as isLight
				,CASE WHEN ISNULL(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as isAcro
				,CASE WHEN ISNULL(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as isMramor
				,CASE WHEN ISNULL(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as isHale
				,CASE WHEN ISNULL(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as isPerit		
				,CClC.Urine
				,CClC.Shit
				,CClC.OtherSympt
				,CClC.WorkAD
				,CClC.AD,
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as Pulse,
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as Chss,
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as Chd,
				CClC.Temperature
				,CClC.Pulsks
				,CClC.Gluck
				,CClC.LocalStatus
				,convert(varchar(5), cast(CClC.Ekg1Time as datetime), 108) as Ekg1Time
				,CClC.Ekg1
				,convert(varchar(5), cast(CClC.Ekg2Time as datetime), 108) as Ekg2Time
				,CClC.Ekg2
				,CClC.Diag_id
				,CClC.EfAD
				,CASE WHEN ISNULL(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as EfChss
				,CASE WHEN ISNULL(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as EfPulse
				,CClC.EfTemperature
				,CASE WHEN ISNULL(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as EfChd
				,CClC.EfPulsks
				,CClC.EfGluck
				,CClC.Kilo
				,CClC.Lpu_id
				,CClC.HelpPlace
				,CClC.HelpAuto
				,CClC.DescText
				,UCA.PMUser_Name as FeldsherAcceptName
				,UCT.PMUser_Name as FeldsherTransName
				
			from
				v_CmpCloseCard CClC with (nolock)
				left join v_CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id	
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id	
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CClC.pmUser_updID
				outer apply (
					select top 1
						CCCS.CmpCallCardStatus_insDT as TransTime,
						CCCS.pmUser_insID as FeldsherTransPmUser_id
					from
						v_CmpCallCardStatus CCCS with (nolock) 
					where
						CCCS.CmpCallCard_id = CClC.CmpCallCard_id
						and CCCS.CmpCallCardStatusType_id = 2
					order by
						CCCS.pmUser_insID desc
				) as CCCStatusData
			where
				{$filter}
		";
		
		//LEFT JOIN v_pmUser P with (nolock) on P.PMUser_id = CCC.pmUser_updID
			
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {			
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	*/
		
	/**
	 * @desc Сохранение формы 110у с набором полей для Пскова
	 * @param array $data
	 * @return boolean 
	 */
	/*
	function saveCmpCloseCard110($data) {
		
		$action = null;
		
		$rules = array(
			array( 'field' => 'Kilo' , 'label' => 'Километраж' , 'type' => 'float', 'maxValue' => '1000' ),
		);

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !empty( $err ) )
			return $err ;
		
		if ( 
				//(isset($data['ARMType']) && $data['ARMType'] == 'smpadmin') && 
				(isset($data['CmpCloseCard_id']))&&($data['CmpCloseCard_id'] != null ) 
			)
		{
			
			if (!empty($data['Person_id']) && $data['Person_id'] > 0) {
				$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :CmpCallCard_id;			
				exec p_CmpCallCard_setPerson
					@CmpCallCard_id = @Res,				
					@Person_id = :Person_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$queryParams = array(				
					'CmpCallCard_id' => $data['CmpCallCard_id'],				
					'Person_id' => (!empty($data['Person_id']))?$data['Person_id']: null,							
					'pmUser_id' => $data['pmUser_id']
				);

				$result = $this->db->query($query, $queryParams);				
			}
			
			$action = 'edit';
			$closeCard = '@CmpCloseCard_id :CmpCloseCard_id';
			
			$procedure = 'p_CmpCloseCard_upd';
			$relProcedure = 'p_CmpCloseCardRel_ins';
		}
		else 
		{		
				
			$query = "select
						CLC.CmpCloseCard_id
					from
						v_CmpCloseCard CLC with (nolock)
					where
						CLC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams = array('CmpCallCard_id' => $data['CmpCallCard_id']); 
			$result = $this->db->query($query, $queryParams);
			$retrun = $result->result('array');
			if ( is_object($result) && count($retrun) > 0) {
				$data['CmpCloseCard_id'] = $retrun[0]['CmpCloseCard_id'];
				$action = 'edit';
				$closeCard = '@CmpCloseCard_id :CmpCloseCard_id';
				$procedure = 'p_CmpCloseCard_upd';
				$relProcedure = 'p_CmpCloseCardRel_ins';				
			} else {			
				$action = 'add';
				$closeCard = '';

				$procedure = 'p_CmpCloseCard_ins';
				$relProcedure = 'p_CmpCloseCardRel_ins';
			}
			
		}
		
		$UnicNums = ';';
		if ( isset($data['CmpCloseCard_prmTime']) ) {
			$data['CmpCloseCard_prmDate'] .= ' ' . $data['CmpCloseCard_prmTime'] . ':00.000';
		}
		//Добавил проверки на новые поля, которых нет в CmpCallCard
		if (!isset($data['Korpus'])) {
			$data['Korpus'] = '';
		}
		if (!isset($data['Room'])) {
			$data['Room'] = '';
		}
		
		//Приводим данные в полях с типом datetime к виду, в котором их принимает БД
		$timeFiledsNames = array(
			'AcceptTime',
			'TransTime',
			'GoTime',
			'ArriveTime',
			'TransportTime',
			'ToHospitalTime',
			'BackTime',
			'EndTime'
		);
		
		foreach ($timeFiledsNames as $key => $timeFieldName) {
			if (!empty($data[$timeFieldName])) {
				if (isset($data[$timeFieldName])&&$data[$timeFieldName] != '') $data[$timeFieldName] = substr($data[$timeFieldName],3,3).substr($data[$timeFieldName],0,3).substr($data[$timeFieldName],6,10);
			}
		}
		
		if ($data['Entrance'] == 0 || $data['Entrance'] == '') $data['Entrance'] = null;
		if ($data['Level'] == 0 || $data['Level'] == '') $data['Level'] = null;	
		
		$queryParams = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			
			'Day_num' => $data['Day_num'],
			'Year_num' => $data['Year_num'],
			'Feldsher_id' => $data['Feldsher_id'],
			'StationNum' => $data['StationNum'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'EmergencyTeamNum' => $data['EmergencyTeamNum'],
			'EmergencyTeam_id' => (!empty( $data['EmergencyTeam_id'] ) ? $data['EmergencyTeam_id'] : null ),
			
			'EmergencyTeamSpec_id' => $data['EmergencyTeamSpec_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			
			'PayType_id' => $data['PayType_id'],
			
			'AcceptTime' => ($data['AcceptTime'] != '') ? $data['AcceptTime'] : null,
			'TransTime' => ($data['TransTime'] != '') ? $data['TransTime'] : null,
			'GoTime' => ($data['GoTime'] != '') ? $data['GoTime'] : null,
			'ArriveTime' => ($data['ArriveTime'] != '') ? $data['ArriveTime'] : null,
			'TransportTime' => ($data['TransportTime'] != '') ? $data['TransportTime'] : null,
			'ToHospitalTime' => ($data['ToHospitalTime'] != '') ? $data['ToHospitalTime'] : null,
			'EndTime' => ($data['EndTime'] != '') ? $data['EndTime'] : null,
			'BackTime' => ($data['BackTime'] != '') ? $data['BackTime'] : null,
			'SummTime' => $data['SummTime'],
			
			'Area_id' => (int)$data['Area_id'] ? (int)$data['Area_id'] : null,
			'City_id' =>  (int)$data['City_id'] ? (int)$data['City_id'] : null,
			'Town_id' => (int)$data['Town_id'] ? (int)$data['Town_id'] : null,
			'Street_id' => (isset( $data[ 'Street_id' ]) && !isset( $data[ 'CmpCloseCard_Street' ] ) && $data[ 'Street_id' ] > 0 ) ? $data[ 'Street_id' ] : null,
			'House' => $data['House'],
			'Korpus' => $data['Korpus'],
			'Office' => $data['Office'],
			'Room' => $data['Room'],
			'Entrance' => $data['Entrance'],
			'Level' => $data['Level'],
			'CodeEntrance' => $data['CodeEntrance'],
			
			'Fam' => $data['Fam'],
			'Name' => $data['Name'],
			'Middle' => $data['Middle'],
			'Age' => $data['Age'],
			'Sex_id' => $data['Sex_id'],
			'Work' => $data['Work'],
			'DocumentNum' => $data['DocumentNum'],
			
			'Ktov' => (!empty( $data['Ktov'] ) ? $data['Ktov'] : null ),
			'CmpCallerType_id' => (!empty( $data['CmpCallerType_id'] ) ? $data['CmpCallerType_id'] : null ),
			'Phone' => $data['Phone'],
			
			'FeldsherAccept' => $data['FeldsherAccept'],
			'FeldsherTrans' => $data['FeldsherTrans'],
			
			'CallType_id' => $data['CallType_id'],
			'CallPovod_id' => (isset($data['CmpReason_id']) && $data['CmpReason_id'] > 0)?$data['CmpReason_id']:((isset($data['CallPovod_id']) && $data['CallPovod_id'] > 0)?$data['CallPovod_id']:null),
			
			'isAlco' => (($data['isAlco'] > 0)?$data['isAlco']:null),
			
			'Complaints' => $data['Complaints'],
			
			'Anamnez' => $data['Anamnez'],
			
			'isMenen' => $data['isMenen'],
			'isNist' => $data['isNist'],
			'isAnis' => $data['isAnis'],
			'isLight' => $data['isLight'],
			'isAcro' => $data['isAcro'],
			'isMramor' => $data['isMramor'],
			'isHale' => $data['isHale'],
			'isPerit' => $data['isPerit'],
			
			'Urine' => $data['Urine'],
			'Shit' => $data['Shit'],
			'OtherSympt' => $data['OtherSympt'],
			'WorkAD' => $data['WorkAD'],
			'AD' => $data['AD'],
			'Chss' => $data['Chss'],
			'Pulse' => $data['Pulse'],
			'Temperature' => $data['Temperature'],
			'Chd' => $data['Chd'],
			'Pulsks' => $data['Pulsks'],
			'Gluck' => $data['Gluck'],
			'LocalStatus' => $data['LocalStatus'],
			'Ekg1' => $data['Ekg1'],
			'Ekg1Time' => ($data['Ekg1Time'] != '') ? $data['Ekg1Time'] : null,
			'Ekg2' => $data['Ekg2'],
			'Ekg2Time' => ($data['Ekg2Time'] != '') ? $data['Ekg2Time'] : null,
			
			'Diag_id' => (isset($data['Diag_id']) && $data['Diag_id'] != '') ? $data['Diag_id'] : null,
			
			'HelpPlace' => $data['HelpPlace'],
			'HelpAuto' => $data['HelpAuto'],
			
			'EfAD' => $data['EfAD'],
			'EfChss' => $data['EfChss'],
			'EfPulse' => $data['EfPulse'],
			'EfTemperature' => $data['EfTemperature'],
			'EfChd' => $data['EfChd'],
			'EfPulsks' => $data['EfPulsks'],
			'EfGluck' => $data['EfGluck'],
			
			'Kilo' => $data['Kilo'],
			'DescText' => $data['DescText'],
						
			'pmUser_id' => $data['pmUser_id']
		);	
		
		$txt = "";
		foreach ($queryParams as $q=>$p) {
			$txt .= "@".$q." = :".$q.",\r\n";
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)
				".$UnicNums."
			set @Res = 0;
			exec " . $procedure . "
				@CmpCloseCard_id = @Res output,				
				".$txt."				
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		if ( $action == 'edit' ) {
			$NewCmpCloseCard_id = null;
			//Если админ смп , то делаем копию исходной записи, а измененную копию сохраняем на место старой
			//1 - выбираем старую запись
			$squery = "
				SELECT * 
				FROM v_CmpCloseCard CLC (nolock)
				WHERE CLC.CmpCloseCard_id = " . $data['CmpCloseCard_id'] . ";
			";
			
			$result = $this->db->query($squery, $data);

			if ( !is_object($result) ) {
				return false;
			}
			$oldresult = $result->result('array');
			$oldresult = $oldresult[0];

			
			//2 - сохраняем страую запись в новую

			$squeryParams = array(

				'CmpCallCard_id' => $oldresult['CmpCallCard_id'],
				
				'Day_num' => $oldresult['Day_num'],
				'Year_num' => $oldresult['Year_num'],
				
				'Feldsher_id' => $oldresult['Feldsher_id'],
				'StationNum' => $oldresult['StationNum'],
				'LpuBuilding_id' => $oldresult['LpuBuilding_id'],
				'EmergencyTeamNum' => $oldresult['EmergencyTeamNum'],
				'EmergencyTeam_id' => $oldresult['EmergencyTeam_id'],

				'EmergencyTeamSpec_id' => $oldresult['EmergencyTeamSpec_id'],
				'MedPersonal_id' => $oldresult['MedPersonal_id'],
				
				'PayType_id' => $oldresult['PayType_id'],
				
				'AcceptTime' => ($oldresult['AcceptTime'] != '') ? $oldresult['AcceptTime'] : null,
				'TransTime' => ($oldresult['TransTime'] != '') ? $oldresult['TransTime'] : null,
				'GoTime' => ($oldresult['GoTime'] != '') ? $oldresult['GoTime'] : null,
				'ArriveTime' => ($oldresult['ArriveTime'] != '') ? $oldresult['ArriveTime'] : null,
				'TransportTime' => ($oldresult['TransportTime'] != '') ? $oldresult['TransportTime'] : null,
				'ToHospitalTime' => ($oldresult['ToHospitalTime'] != '') ? $oldresult['ToHospitalTime'] : null,
				'EndTime' => ($oldresult['EndTime'] != '') ? $oldresult['EndTime'] : null,
				'BackTime' => ($oldresult['BackTime'] != '') ? $oldresult['BackTime'] : null,
				'SummTime' => $oldresult['SummTime'],

				'Area_id' => (int)$oldresult['Area_id'] ? (int)$oldresult['Area_id'] : null,
				'City_id' =>  (int)$oldresult['City_id'] ? (int)$oldresult['City_id'] : null,
				'Town_id' => (int)$oldresult['Town_id'] ? (int)$oldresult['Town_id'] : null,
				'Street_id' => (int)$oldresult['Street_id'] ? (int)$oldresult['Street_id'] : null,
				'House' => $oldresult['House'],
				'Office' => $oldresult['Office'],
				'Entrance' => $oldresult['Entrance'],
				'Level' => $oldresult['Level'],
				'CodeEntrance' => $oldresult['CodeEntrance'],

				'Fam' => $oldresult['Fam'],
				'Name' => $oldresult['Name'],
				'Middle' => $oldresult['Middle'],
				'Age' => $oldresult['Age'],
				'Sex_id' => $oldresult['Sex_id'],
				'Work' => $oldresult['Work'],
				'DocumentNum' => $oldresult['DocumentNum'],

				'Ktov' => $oldresult['Ktov'],
				'CmpCallerType_id' => $oldresult['CmpCallerType_id'],
				'Phone' => $oldresult['Phone'],

				'FeldsherAccept' => $oldresult['FeldsherAccept'],
				'FeldsherTrans' => $oldresult['FeldsherTrans'],

				'CallType_id' => $oldresult['CallType_id'],
				'CallPovod_id' => $oldresult['CallPovod_id'],
				'CallPovodNew_id' => $oldresult['CallPovodNew_id'],

				'isAlco' => $oldresult['isAlco'],

				'Complaints' => $oldresult['Complaints'],

				'Anamnez' => $oldresult['Anamnez'],

				'Korpus' => $oldresult['Korpus'],
				'Room' => $oldresult['Room'],

				'isMenen' => $oldresult['isMenen'],
				'isNist' => $oldresult['isNist'],
				'isAnis' => $oldresult['isAnis'],
				'isLight' => $oldresult['isLight'],
				'isAcro' => $oldresult['isAcro'],
				'isMramor' => $oldresult['isMramor'],
				'isHale' => $oldresult['isHale'],
				'isPerit' => $oldresult['isPerit'],

				'Urine' => $oldresult['Urine'],
				'Shit' => $oldresult['Shit'],
				'OtherSympt' => $oldresult['OtherSympt'],
				'WorkAD' => $oldresult['WorkAD'],
				'AD' => $oldresult['AD'],
				'Chss' => $oldresult['Chss'],
				'Pulse' => $oldresult['Pulse'],
				'Temperature' => $oldresult['Temperature'],
				'Chd' => $oldresult['Chd'],
				'Pulsks' => $oldresult['Pulsks'],
				'Gluck' => $oldresult['Gluck'],
				'LocalStatus' => $oldresult['LocalStatus'],
				'Ekg1' => $oldresult['Ekg1'],
				'Ekg1Time' => ($oldresult['Ekg1Time'] != '') ? $oldresult['Ekg1Time'] : null,
				'Ekg2' => $oldresult['Ekg2'],
				'Ekg2Time' => ($oldresult['Ekg2Time'] != '') ? $oldresult['Ekg2Time'] : null,

				'Diag_id' => $oldresult['Diag_id'],

				'HelpPlace' => $oldresult['HelpPlace'],
				'HelpAuto' => $oldresult['HelpAuto'],

				'EfAD' => $oldresult['EfAD'],
				'EfChss' => $oldresult['EfChss'],
				'EfPulse' => $oldresult['EfPulse'],
				'EfTemperature' => $oldresult['EfTemperature'],
				'EfChd' => $oldresult['EfChd'],
				'EfPulsks' => $oldresult['EfPulsks'],
				'EfGluck' => $oldresult['EfGluck'],

				'Kilo' => $oldresult['Kilo'],
				'DescText' => $oldresult['DescText'],

				'pmUser_id' => $oldresult['pmUser_insID'],

				'CmpCloseCard_firstVersion' => $oldresult['CmpCloseCard_firstVersion']

			);
		
			$txt = "";
			foreach ($squeryParams as $q=>$p) {
				$txt .= "@".$q." = :".$q.",\r\n";
			}
			
			$squery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = 0;

				exec p_CmpCloseCard_ins
					".$txt."
					@CmpCloseCard_id = @Res output,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				-- здесь должен быть вызов p_CmpCloseCard_del

				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		
			$result = $this->db->query($squery, $squeryParams);

			if ( !is_object($result) ) {
				return false;
			}
			
			$result = $result->result('array');
			$result = $result[0];
			
			$NewCmpCloseCard_id = $result['CmpCloseCard_id'];

			// 3 - заменяем старую запись текущими изменениями
		
			$newParams = $queryParams;	
			$newParams['CmpCloseCard_id']  =  $oldresult['CmpCloseCard_id'];
			
			if ( (!isset($newParams['CmpCloseCard_id']))||($newParams['CmpCloseCard_id'] == null ) )
			{
				$newParams['CmpCallCard_id'] = $oldresult['CmpCallCard_id'];
			}
			
			$txt = "";
			foreach ($newParams as $q=>$p) {
				$txt .= "@".$q." = :".$q.",\r\n";
			}
		
			$squery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :CmpCloseCard_id;

				exec p_CmpCloseCard_upd
				".$txt."
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";	
		
			$result = $this->db->query($squery, $newParams);
			
			if ( !is_object($result) ) {
				return false;
			}
			$resArray = $result->result('array');;

			// 4 - устанавливаем значение старого id в перезаписанной записи		
			$squery = "
				exec p_CmpCloseCard_setFirstVersion 
				@CmpCloseCard_id = " . $oldresult['CmpCloseCard_id'] . ",
				@CmpCloseCard_firstVersion = " . $NewCmpCloseCard_id . ",
				@pmUser_id = " . $data['pmUser_id'] . ";							
			";
		
			$res = $this->db->query($squery);
					
		} else {	// add
			$result = $this->db->query($query, $queryParams);
			$resArray = $result->result('array');	
		}
		
		
		
		// Комбо которые нам нужны для сохранения
		$comboFields = array(
			'Condition_id', 'Behavior_id', 'Cons_id', 'Pupil_id', 'Kozha_id', 'Hypostas_id', 
			'Crop_id', 'Hale_id', 'Rattle_id', 'Shortwind_id', 'Heart_id', 'Noise_id', 'Pulse_id', 'Lang_id', 
			'Gaste_id', 'Liver_id', 'Complicat_id', 'ComplicatEf_id', 'Patient_id', 'AgeType_id',
			'TransToAuto_id', 
			//'DeportClose_id', 
			//'DeportFail_id', 
			'ResultUfa_id',
			'PersonRegistry_id', 'PersonSocial_id', 'CallTeamPlace_id', 
			'Delay_id', 'TeamComplect_id', 'CallPlace_id', 'AccidentReason_id', 'Trauma_id', 'Result_id'
		);
		
		$relComboFields = array();
		foreach ($comboFields as $cfield) {
			if (isset($data[$cfield])) {
				//Если это чекбокс, собираем значения отмеченных
				if (is_array($data[$cfield])) {
					foreach ($data[$cfield] as $dataField) {
						$relComboFields[] = $this->getComboIdByCode($dataField);
					}
				}
				if (((int)$data[$cfield] == $data[$cfield]) && ($data[$cfield] > 0)) {
					//Если это радиобаттон берем его значение
					$relComboFields[] = $this->getComboIdByCode($data[$cfield]);
				}
			}
		}
		
	
		$queryRelParams = array(
			'CmpCloseCard_id' => $resArray[0]['CmpCloseCard_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		//var_dump($queryRelParams);
		
		if ($action == 'add') {
			$relResult = array();
			foreach ($relComboFields as $relComboField) {
				$queryRelParams['relComboField']=$relComboField;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
					set @Res = 0;
					exec " . $relProcedure . "
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = :CmpCloseCard_id,
						@CmpCloseCardCombo_id = :relComboField,				
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";			
				$relResult[] = $this->db->query($query, $queryRelParams);			
			}
		
			//Если у нас тип - это поле ввода
			if (is_array($data['ComboValue'])) foreach ($data['ComboValue'] as $cKey => $cValue) {
				if ($cValue != '') {
					$queryRelParams['cKey'] = $cKey;
					$queryRelParams['cValue'] = $cValue;
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000)
							".$UnicNums."
						set @Res = 0;
						exec " . $relProcedure . "
							@CmpCloseCardRel_id = @Res output,
							@CmpCloseCard_id = :CmpCloseCard_id,
							@CmpCloseCardCombo_id = :cKey,
							@Localize = :cValue,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
					$relResult[] = $this->db->query($query, $queryRelParams);
				}		
			}

			if ( is_object($result) ) {			
				return $resArray;
			}
			else {
				return false;
			}		
		} else {// action edit
			$relResult = array();
			//заменяем id комбобоксов на свежий
			foreach ($relComboFields as $relComboField) {
				$queryRelParams['relComboField']=$relComboField;
				$query = "
					declare
						@pmUser_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000);

					exec p_CmpCloseCardRel_updVersion
						@CmpCloseCard_oldId = ".$oldresult['CmpCloseCard_id'].",
						@CmpCloseCard_newId = ".$NewCmpCloseCard_id.",
						@pmUser_id = " . $data['pmUser_id'] . "				
					";
				$relResult[] = $this->db->query($query, $queryRelParams);	

			}	
			//записываем новые значения комбиков в стрый id
			foreach ($relComboFields as $relComboField) {
				$queryRelParams['relComboField']=$relComboField;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
					set @Res = 0;
					exec " . $relProcedure . "
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = ".$oldresult['CmpCloseCard_id'].",
						@CmpCloseCardCombo_id = :relComboField,				
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";			
				$relResult[] = $this->db->query($query, $queryRelParams);				
			}		
		
			//Если у нас тип - это поле ввода
			if (is_array($data['ComboValue'])) foreach ($data['ComboValue'] as $cKey => $cValue) {
				if ($cValue != '') {
					$queryRelParams['cKey'] = $this->getComboIdByCode($cKey);
					$queryRelParams['cValue'] = $cValue;
					$query = "
						declare
							@Res bigint,
							@ErrCode int,
							@ErrMessage varchar(4000)
							".$UnicNums."
						set @Res = 0;
						exec " . $relProcedure . "
							@CmpCloseCardRel_id = @Res output,
							@CmpCloseCard_id = ".$oldresult['CmpCloseCard_id'].",
							@CmpCloseCardCombo_id = :cKey,
							@Localize = :cValue,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
						select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
					$relResult[] = $this->db->query($query, $queryRelParams);
				}		
			}
	
			return $resArray;
		}
	}
	*/
	
	/**
	 * Поточный ввод
	 */
	 /*
	function saveCmpStreamCard($data) {
		$newdata = $data;		
		$UnicNums ='';
		$statuschange = true;		
		$CmpCallCard_Numv ='';
		
		$procedure = 'p_CmpCallCard_ins';
		$UnicNums = ", 
		@UnicCmpCallCard_Numv bigint,
		@UnicCmpCallCard_Ngod bigint,
		@SQLstring nvarchar(500),
		@ParamDefinition nvarchar(500);

		SET @SQLString = 
			N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."'' 
			THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
			@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')." 
			THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END)+1
			FROM v_CmpCallCard CCC with (nolock)
			WHERE CCC.Lpu_id = @Lpu_id_forUnicNumRequest ';

		SET @ParamDefinition = N'@UnicCmpCallCard_NumvOUT bigint OUTPUT, 
		@UnicCmpCallCard_NgodOUT bigint OUTPUT, @Lpu_id_forUnicNumRequest bigint';

		exec sp_executesql 
		@SQLString, 
		@ParamDefinition,
		@Lpu_id_forUnicNumRequest = :Lpu_id_forUnicNumRequest,
		@UnicCmpCallCard_NumvOUT = @UnicCmpCallCard_Numv OUTPUT, 
		@UnicCmpCallCard_NgodOUT = @UnicCmpCallCard_Ngod OUTPUT
		";
		
		$CmpCallCard_Numv = ':CmpCallCard_Numv';
		$CmpCallCard_Ngod = ':CmpCallCard_Ngod';

		// Если у нас неизвестный пациент всё равно вставляем в person
		if(
		($data[ 'Fam' ] == 'НЕИЗВЕСТЕН' &&
			$data[ 'Name' ] == 'НЕИЗВЕСТЕН' &&
			$data[ 'Middle' ] == 'НЕИЗВЕСТЕН') || $data['Person_id'] == 0
		) {
			$socstatus_Ids = array("ufa" => 2, "buryatiya" => 10000083, "kareliya" => 51, "khak" => 32,
				"astra" => 10000053, "kaluga" => 231, "penza" => 224, "perm" => 2, "pskov" => 25,
				"saratov" => 10000035, "ekb" => 10000072, "msk" => 60, "krym" => 262, "kz" => 91, "by" => 201);

			$query="
				declare
					@Pers_id bigint = NULL,
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_PersonAll_ins
					@Person_id = @Pers_id OUTPUT
					,@Server_id = :Server_id

				,@Person_IsInErz = NULL
				,@PersonSurName_SurName = :PersonSurName_SurName
				,@PersonFirName_FirName = :PersonFirName_FirName
				,@PersonSecName_SecName = :PersonSecName_SecName
				,@Person_IsUnknown = 2

				,@PersonBirthDay_BirthDay = :PersonBirthDay_BirthDay
				,@PersonSex_id = :Sex_id
				,@PersonSocStatus_id = :socstatus_Ids

				,@pmUser_id = :pmUser_id
				,@Error_Code = @ErrCode output
				,@Error_Message = @ErrMessage output;

				select @Pers_id as Pid, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$queryParams = array(
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
				'Sex_id' => $data['Sex_id'],
				'socstatus_Ids' => $socstatus_Ids[getRegionNick()],
				'PersonBirthDay_BirthDay' => '01.01.' . (date("Y") - (isset($data['Person_Age']) ? $data['Person_Age'] : $data['Age']))
			);
			$queryParams['PersonSurName_SurName'] = $data[ 'Fam' ];
			$queryParams['PersonFirName_FirName'] = $data[ 'Name' ];
			$queryParams['PersonSecName_SecName'] = $data[ 'Middle' ];


			$res = $this->db->query($query, $queryParams);
			if (!is_object($res)) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных');
			}
			$rows = $res->result('array');

			if (!is_array($rows) || count($rows) == 0) {
				return array('success' => false, 'Error_Msg' => 'Ошибки сохранения человека');
			} else if (!empty($rows[0]['Error_Msg'])) {
				return array('success' => false, 'Error_Msg' => $rows[0]['Error_Msg']);
			}
			$data['Person_id'] = $rows[0]['Pid'];			
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

				".$UnicNums."
			
			set @Res = :CmpCallCard_id;
			
			exec " . $procedure . "
				@CmpCallCard_id = @Res output,
				@CmpCallCard_Numv = ".$CmpCallCard_Numv.",
				@CmpCallCard_Ngod = ".$CmpCallCard_Ngod.",
				
				@CmpCallCard_City = :CmpCallCard_City,
				@CmpCallCard_Ulic = :CmpCallCard_Ulic,
				@CmpCallCard_Dom = :CmpCallCard_Dom,
				@CmpCallCard_Korp = :CmpCallCard_Korp,
				@CmpCallCard_Room = :CmpCallCard_Room,
				@CmpCallCard_Kvar = :CmpCallCard_Kvar,
				@CmpCallCard_Podz = :CmpCallCard_Podz,
				@CmpCallCard_Etaj = :CmpCallCard_Etaj,
				@CmpCallCard_Kodp = :CmpCallCard_Kodp,
				@CmpCallCard_Telf = :CmpCallCard_Telf,
			
				@CmpCallCard_Comm = :CmpCallCard_Comm,
				@CmpReason_id = :CmpReason_id,
			
				@Person_SurName = :Person_Surname,
				@Person_FirName = :Person_Firname,
				@Person_SecName = :Person_Secname,
				@Person_Age = :Person_Age,
				@Person_id = :Person_id,
				
				@Person_PolisNum = :Person_PolisNum,
				@Person_PolisSer = :Person_PolisSer,
				@Sex_id = :Sex_id,
				@CmpCallCard_Ktov = :CmpCallCard_Ktov,
				@CmpCallerType_id = :CmpCallerType_id,
				@CmpCallType_id = :CmpCallType_id,
			
				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
			
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,				
				
				@CmpCallCard_prmDT = :CmpCallCard_prmDT,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,
				
				@Lpu_ppdid = :Lpu_ppdid,
				@Lpu_id = :Lpu_id,
				
				@LpuBuilding_id = :LpuBuilding_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$aDate = explode(" ",$data['AcceptTime']);
		$aTime = $aDate[1];
		$aDate = explode(".",$aDate[0]);
		$aDate = $aDate[2].'-'.$aDate[1].'-'.$aDate[0].' '.$aTime;	
				
		$queryParams = array(
			'Lpu_id_forUnicNumRequest' => $data['Lpu_id'],
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Numv' => $data['Day_num'],
			'CmpCallCard_Ngod' => $data['Year_num'],
			'CmpCallCard_City' => $data['City_id'],
			'CmpCallCard_Ulic' => $data['Street_id'],
			'CmpCallCard_Dom' => $data['House'],
			'CmpCallCard_Korp' => $data['Korpus'],
			'CmpCallCard_Room' => $data['Room'],			
			'CmpCallCard_Kvar' => $data['Office'],
			'CmpCallCard_Podz' => $data['Entrance'],
			'CmpCallCard_Etaj' => $data['Level'],
			'CmpCallCard_Kodp' => $data['CodeEntrance'],
			'CmpCallCard_Telf' => $data['Phone'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpReason_id' => (isset($data['CmpReason_id']) && $data['CmpReason_id'] > 0)?$data['CmpReason_id']:((isset($data['CallPovod_id']) && $data['CallPovod_id'] > 0)?$data['CallPovod_id']:null),
			'Person_Surname' => $data['Fam'],
			'Person_Firname' => $data['Name'],
			'Person_Secname' => $data['Middle'],
			'Person_Age' => $data['Age'],		
			'Person_id' => (isset($data['Person_id']) && $data['Person_id'] > 0)?$data['Person_id']:null,
			'Person_PolisSer' => $data['PolisSerial'],
			'Person_PolisNum' => $data['PolisNum'],
			'Sex_id' => $data['Sex_id'],
			'CmpCallCard_Ktov' => (!empty( $data[ 'Ktov' ] ) ? $data[ 'Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'CmpCallType_id' => $data['CallType_id'],			
			'KLRgn_id' => (isset($data['KLRgn_id']) && $data['KLRgn_id'] > 0)?$data['KLRgn_id']:((isset($data['KLAreaStat_idEdit']) && $data['KLAreaStat_idEdit'] > 0)?$data['KLAreaStat_idEdit']:null),
			'KLSubRgn_id' => (isset($data['Area_id']) && $data['Area_id'] > 0)?$data['Area_id']:null,
			'KLCity_id' => (isset($data['City_id']) && $data['City_id'] > 0)?$data['City_id']:null,
			'KLTown_id' => (isset($data['Town_id']) && $data['Town_id'] > 0)?$data['Town_id']:null,
			'KLStreet_id' => (isset($data['Street_id']) && $data['Street_id'] > 0)?$data['Street_id']:null,
			'Lpu_id' => (isset($data['Lpu_id']) && $data['Lpu_id'] > 0)?$data['Lpu_id']:null,
			'Lpu_ppdid' => (isset($data['Lpu_ppdid']) && $data['Lpu_ppdid'] > 0)?$data['Lpu_ppdid']:null,			
			'CmpCallCard_IsReceivedInPPD' => '1',
			'CmpCallCard_IsOpen' =>  '2',
			'CmpCallCardStatusType_id' => '6',
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']))?$data['LpuBuilding_id']:null,
			'CmpCallCard_prmDT' => $aDate,
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);
		$resultforstatus = array();
		$resultforstatus = $result->result('array');
		if ( is_object($result) ) {
			$result = $result->result('array');
			if ($result[0]['CmpCallCard_id'] > 0){				
				$result110 = $this->saveCmpCloseCard110( array_merge( $data , array( 'CmpCallCard_id' => $result[ 0 ][ 'CmpCallCard_id' ] ) ) ) ;
				return array( 0 => array_merge( $result110[ 0 ] , $result[ 0 ] ) ) ;
			} else return false;
		}
		else {
			return false;
		}		
	}
	*/

	/**
	 * default desc 
	 */
	 /*
	function saveCmpCallCard($data) {
		$procedure = '';
		$UnicNums ='';
		$statuschange = true;
		
		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Невозможно сохранить. Карта вызова редактируется другим пользователем' ) );
		}
		
		$CmpCallCard_Numv ='';		
		if ( (!isset($data['CmpCallCard_id'])) || ($data['CmpCallCard_id'] <= 0) ) {		
			$procedure = 'p_CmpCallCard_ins';
				$UnicNums = ", 
				@UnicCmpCallCard_Numv bigint,
				@UnicCmpCallCard_Ngod bigint,
				@SQLstring nvarchar(500),
				@ParamDefinition nvarchar(500);

				SET @SQLString = 
					N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."'' 
					THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
					@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')." 
					THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END)+1
					FROM v_CmpCallCard CCC with (nolock)
					WHERE CCC.Lpu_id = @Lpu_id_forUnicNumRequest ';

				SET @ParamDefinition = N'@UnicCmpCallCard_NumvOUT bigint OUTPUT,
				@UnicCmpCallCard_NgodOUT bigint OUTPUT, @Lpu_id_forUnicNumRequest bigint';

				exec sp_executesql
				@SQLString,
				@ParamDefinition,
				@Lpu_id_forUnicNumRequest = :Lpu_id_forUnicNumRequest,
				@UnicCmpCallCard_NumvOUT = @UnicCmpCallCard_Numv OUTPUT, 
				@UnicCmpCallCard_NgodOUT = @UnicCmpCallCard_Ngod OUTPUT
				";
			if ($data['ARMType']=='smpadmin') {
				$CmpCallCard_Numv = ':CmpCallCard_Numv';
				$CmpCallCard_Ngod = ':CmpCallCard_Ngod';
			} else {
				$CmpCallCard_Numv = '@UnicCmpCallCard_Numv';
				$CmpCallCard_Ngod = '@UnicCmpCallCard_Ngod';				
			}
		} else {
			
			//Если админ смп , то делаем копию исходной записи, а измененную копию сохраняем на место старой	
			
			//1 - выбираем старую запись
			
			if ( $data['ARMType']=='smpadmin' )	{
				$query = "
					SELECT * 
					FROM v_CmpCallCard CCC with (nolock) 
					WHERE CCC.CmpCallCard_id = " . $data['CmpCallCard_id'] . ";
				";
				$result = $this->db->query($query, $data);

				if ( !is_object($result) ) {
					return false;
				}
				$result = $result->result('array');
				$result = $result[0];

				//2 - сохраняем страую запись в новую

				$procedure = 'p_CmpCallCard_ins';
				$UnicNums = ", 
					@UnicCmpCallCard_Numv bigint,
					@UnicCmpCallCard_Ngod bigint,
					@SQLstring nvarchar(500),
					@ParamDefinition nvarchar(500);

					SET @SQLString = 
						N'SELECT @UnicCmpCallCard_NumvOUT = MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."'' 
						THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END)+1,
						@UnicCmpCallCard_NgodOUT = MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')." 
						THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END)+1
						FROM v_CmpCallCard CCC with (nolock)
						WHERE CCC.Lpu_id = @Lpu_id_forUnicNumRequest ';

					SET @ParamDefinition = N'@UnicCmpCallCard_NumvOUT bigint OUTPUT, 
					@UnicCmpCallCard_NgodOUT bigint OUTPUT, @Lpu_id_forUnicNumRequest bigint';

					exec sp_executesql 
					@SQLString, 
					@ParamDefinition,
					@Lpu_id_forUnicNumRequest = :Lpu_id_forUnicNumRequest,
					@UnicCmpCallCard_NumvOUT = @UnicCmpCallCard_Numv OUTPUT, 
					@UnicCmpCallCard_NgodOUT = @UnicCmpCallCard_Ngod OUTPUT
				";
				$CmpCallCard_Numv = '@UnicCmpCallCard_Numv';
				$CmpCallCard_Ngod = '@UnicCmpCallCard_Ngod';		

				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
						exec " . $procedure . "
						@CmpCallCard_id = @Res output,
						@CmpCallCard_Numv = ".$CmpCallCard_Numv.",
						@CmpCallCard_Ngod = ".$CmpCallCard_Ngod.",
						@CmpCallCard_Prty = :CmpCallCard_Prty,
						@CmpCallCard_Sect = :CmpCallCard_Sect,
						@CmpArea_id = :CmpArea_id,
						@CmpCallCard_City = :CmpCallCard_City,
						@CmpCallCard_Ulic = :CmpCallCard_Ulic,
						@CmpCallCard_Dom = :CmpCallCard_Dom,
						@CmpCallCard_Korp = :CmpCallCard_Korp,
						@CmpCallCard_Room = :CmpCallCard_Room,
						@CmpCallCard_Kvar = :CmpCallCard_Kvar,
						@CmpCallCard_Podz = :CmpCallCard_Podz,
						@CmpCallCard_Etaj = :CmpCallCard_Etaj,
						@CmpCallCard_Kodp = :CmpCallCard_Kodp,
						@CmpCallCard_Telf = :CmpCallCard_Telf,
						@CmpPlace_id = :CmpPlace_id,
						@CmpCallCard_Comm = :CmpCallCard_Comm,
						@CmpReason_id = :CmpReason_id,
						@Person_id = :Person_id,
						@Person_SurName = :Person_Surname,
						@Person_FirName = :Person_Firname,
						@Person_SecName = :Person_Secname,
						@Person_Age = :Person_Age,
						@Person_BirthDay = :Person_Birthday,
						@Person_PolisNum = :Person_PolisNum,
						@Person_PolisSer = :Person_PolisSer,
						@Sex_id = :Sex_id,
						@CmpCallCard_Ktov = :CmpCallCard_Ktov,
						@CmpCallerType_id = :CmpCallerType_id,
						@CmpCallType_id = :CmpCallType_id,
						@CmpProfile_cid = :CmpProfile_cid,
						@CmpCallCard_Smpt = :CmpCallCard_Smpt,
						@CmpCallCard_Stan = :CmpCallCard_Stan,
						@CmpCallCard_prmDT = :CmpCallCard_prmDT,
						@CmpCallCard_Line = :CmpCallCard_Line,
						@CmpResult_id = :CmpResult_id,
						@ResultDeseaseType_id = :ResultDeseaseType_id,
						@CmpArea_gid = :CmpArea_gid,
						@CmpLpu_id = :CmpLpu_id,
						@CmpDiag_oid = :CmpDiag_oid,
						@CmpDiag_aid = :CmpDiag_aid,
						@CmpTrauma_id = :CmpTrauma_id,
						@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
						@Diag_uid = :Diag_uid,
						@CmpCallCard_Numb = :CmpCallCard_Numb,
						@CmpCallCard_Smpb = :CmpCallCard_Smpb,
						@CmpCallCard_Stbr = :CmpCallCard_Stbr,
						@CmpCallCard_Stbb = :CmpCallCard_Stbb,
						@CmpProfile_bid = :CmpProfile_bid,
						@CmpCallCard_Ncar = :CmpCallCard_Ncar,
						@CmpCallCard_RCod = :CmpCallCard_RCod,
						@CmpCallCard_TabN = :CmpCallCard_TabN,
						@CmpCallCard_Dokt = :CmpCallCard_Dokt,
						@MedPersonal_id = :MedPersonal_id,
						@MedStaffFact_id = :MedStaffFact_id,
						@CmpCallCard_IsMedPersonalIdent = :CmpCallCard_IsMedPersonalIdent,
						@CmpCallCard_Tab2 = :CmpCallCard_Tab2,
						@CmpCallCard_Tab3 = :CmpCallCard_Tab3,
						@CmpCallCard_Tab4 = :CmpCallCard_Tab4,
						@Diag_sid = :Diag_sid,
						@CmpTalon_id = :CmpTalon_id,
						@CmpCallCard_Expo = :CmpCallCard_Expo,
						@CmpCallCard_Smpp = :CmpCallCard_Smpp,
						@CmpCallCard_Vr51 = :CmpCallCard_Vr51,
						@CmpCallCard_D201 = :CmpCallCard_D201,
						@CmpCallCard_Dsp1 = :CmpCallCard_Dsp1,
						@CmpCallCard_Dsp2 = :CmpCallCard_Dsp2,
						@CmpCallCard_Dspp = :CmpCallCard_Dspp,
						@CmpCallCard_Dsp3 = :CmpCallCard_Dsp3,
						@CmpCallCard_Kakp = :CmpCallCard_Kakp,
						@CmpCallCard_Tper = :CmpCallCard_Tper,
						@CmpCallCard_Vyez = :CmpCallCard_Vyez,
						@CmpCallCard_Przd = :CmpCallCard_Przd,
						@CmpCallCard_Tgsp = :CmpCallCard_Tgsp,
						@CmpCallCard_Tsta = :CmpCallCard_Tsta,
						@CmpCallCard_Tisp = :CmpCallCard_Tisp,
						@CmpCallCard_Tvzv = :CmpCallCard_Tvzv,
						@CmpCallCard_Kilo = :CmpCallCard_Kilo,
						@CmpCallCard_Dlit = :CmpCallCard_Dlit,
						@CmpCallCard_Prdl = :CmpCallCard_Prdl,
						@CmpArea_pid = :CmpArea_pid,
						@CmpCallCard_PCity = :CmpCallCard_PCity,
						@CmpCallCard_PUlic = :CmpCallCard_PUlic,
						@CmpCallCard_PDom = :CmpCallCard_PDom,
						@CmpCallCard_PKvar = :CmpCallCard_PKvar,
						@CmpCallCard_Izv1 = :CmpCallCard_Izv1,
						@CmpCallCard_Tiz1 = :CmpCallCard_Tiz1,
						@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
						@CmpCallCard_Inf1 = :CmpCallCard_Inf1,
						@CmpCallCard_Inf2 = :CmpCallCard_Inf2,
						@CmpCallCard_Inf3 = :CmpCallCard_Inf3,
						@CmpCallCard_Inf4 = :CmpCallCard_Inf4,
						@CmpCallCard_Inf5 = :CmpCallCard_Inf5,
						@CmpCallCard_Inf6 = :CmpCallCard_Inf6,
						@CmpCallCard_firstVersion = :CmpCallCard_firstVersion,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,		
						@UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id,
						@UslugaComplex_id = :UslugaComplex_id,

						@Lpu_ppdid = :Lpu_ppdid,
						@Lpu_id = :Lpu_id,
						@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,						
						@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				$queryParams = array(
					'Lpu_id_forUnicNumRequest' => $result['Lpu_id'],
					'CmpCallCard_id' => $result['CmpCallCard_id'],
					'CmpCallCard_Numv' => $result['CmpCallCard_Numv'],
					'CmpCallCard_Ngod' => $result['CmpCallCard_Ngod'],
					'CmpCallCard_Prty' => $result['CmpCallCard_Prty'],
					'CmpCallCard_Sect' => $result['CmpCallCard_Sect'],
					'CmpArea_id' => $result['CmpArea_id'],
					'CmpCallCard_City' => $result['CmpCallCard_City'],
					'CmpCallCard_Ulic' => $result['CmpCallCard_Ulic'],
					'CmpCallCard_Dom' => $result['CmpCallCard_Dom'],
					'CmpCallCard_Korp' => $result['CmpCallCard_Korp'],
					'CmpCallCard_Room' => $result['CmpCallCard_Room'],
					'CmpCallCard_Kvar' => $result['CmpCallCard_Kvar'],
					'CmpCallCard_Podz' => $result['CmpCallCard_Podz'],
					'CmpCallCard_Etaj' => $result['CmpCallCard_Etaj'],
					'CmpCallCard_Kodp' => $result['CmpCallCard_Kodp'],
					'CmpCallCard_Telf' => $result['CmpCallCard_Telf'],
					'CmpPlace_id' => $result['CmpPlace_id'],
					'CmpCallCard_Comm' => $result['CmpCallCard_Comm'],
					'CmpReason_id' => $result['CmpReason_id'],
					'Person_id' => $result['Person_id'],
					'Person_Surname' => $result['Person_SurName'],
					'Person_Firname' => $result['Person_FirName'],
					'Person_Secname' => $result['Person_SecName'],
					'Person_Age' => $result['Person_Age'],
					'Person_Birthday' => $result['Person_BirthDay'],
					'Person_PolisSer' => $result['Person_PolisSer'],
					'Person_PolisNum' => $result['Person_PolisNum'],
					'Sex_id' => $result['Sex_id'],
					'CmpCallCard_Ktov' => (!empty( $result[ 'CmpCallCard_Ktov' ] ) ? $result[ 'CmpCallCard_Ktov' ] : null),
					'CmpCallerType_id' => (!empty( $result[ 'CmpCallerType_id' ] ) ? $result[ 'CmpCallerType_id' ] : null),
					'CmpCallType_id' => $result['CmpCallType_id'],
					'CmpProfile_cid' => $result['CmpProfile_cid'],
					'CmpCallCard_Smpt' => $result['CmpCallCard_Smpt'],
					'CmpCallCard_Stan' => $result['CmpCallCard_Stan'],
					'CmpCallCard_prmDT' => $result['CmpCallCard_prmDT'],
					'CmpCallCard_Line' => $result['CmpCallCard_Line'],
					'CmpResult_id' => $result['CmpResult_id'],
					'ResultDeseaseType_id' => $result['ResultDeseaseType_id'],
					'CmpArea_gid' => $result['CmpArea_gid'],
					'CmpLpu_id' => $result['CmpLpu_id'],
					'CmpDiag_oid' => $result['CmpDiag_oid'],
					'CmpDiag_aid' => $result['CmpDiag_aid'],
					'CmpTrauma_id' => $result['CmpTrauma_id'],
					'CmpCallCard_IsAlco' => $result['CmpCallCard_IsAlco'],
					'Diag_uid' => $result['Diag_uid'],
					'CmpCallCard_Numb' => $result['CmpCallCard_Numb'],
					'CmpCallCard_Smpb' => $result['CmpCallCard_Smpb'],
					'CmpCallCard_Stbr' => $result['CmpCallCard_Stbr'],
					'CmpCallCard_Stbb' => $result['CmpCallCard_Stbb'],
					'CmpProfile_bid' => $result['CmpProfile_bid'],
					'CmpCallCard_Ncar' => $result['CmpCallCard_Ncar'],
					'CmpCallCard_RCod' => $result['CmpCallCard_RCod'],
					'CmpCallCard_TabN' => $result['CmpCallCard_TabN'],
					'CmpCallCard_Dokt' => $result['CmpCallCard_Dokt'],
					'MedPersonal_id' => $result['MedPersonal_id'],
					'MedStaffFact_id' => $result['MedStaffFact_id'],
					'CmpCallCard_IsMedPersonalIdent' => $result['CmpCallCard_IsMedPersonalIdent'],
					'CmpCallCard_Tab2' => $result['CmpCallCard_Tab2'],
					'CmpCallCard_Tab3' => $result['CmpCallCard_Tab3'],
					'CmpCallCard_Tab4' => $result['CmpCallCard_Tab4'],
					'Diag_sid' => $result['Diag_sid'],
					'CmpTalon_id' => $result['CmpTalon_id'],
					'CmpCallCard_Expo' => $result['CmpCallCard_Expo'],
					'CmpCallCard_Smpp' => $result['CmpCallCard_Smpp'],
					'CmpCallCard_Vr51' => $result['CmpCallCard_Vr51'],
					'CmpCallCard_D201' => $result['CmpCallCard_D201'],
					'CmpCallCard_Dsp1' => $result['CmpCallCard_Dsp1'],
					'CmpCallCard_Dsp2' => $result['CmpCallCard_Dsp2'],
					'CmpCallCard_Dspp' => $result['CmpCallCard_Dspp'],
					'CmpCallCard_Dsp3' => $result['CmpCallCard_Dsp3'],
					'CmpCallCard_Kakp' => $result['CmpCallCard_Kakp'],
					'CmpCallCard_Tper' => $result['CmpCallCard_Tper'],
					'CmpCallCard_Vyez' => $result['CmpCallCard_Vyez'],
					'CmpCallCard_Przd' => $result['CmpCallCard_Przd'],
					'CmpCallCard_Tgsp' => $result['CmpCallCard_Tgsp'],
					'CmpCallCard_Tsta' => $result['CmpCallCard_Tsta'],
					'CmpCallCard_Tisp' => $result['CmpCallCard_Tisp'],
					'CmpCallCard_Tvzv' => $result['CmpCallCard_Tvzv'],
					'CmpCallCard_Kilo' => $result['CmpCallCard_Kilo'],
					'CmpCallCard_Dlit' => $result['CmpCallCard_Dlit'],
					'CmpCallCard_Prdl' => $result['CmpCallCard_Prdl'],
					'CmpArea_pid' => $result['CmpArea_pid'],
					'CmpCallCard_PCity' => $result['CmpCallCard_PCity'],
					'CmpCallCard_PUlic' => $result['CmpCallCard_PUlic'],
					'CmpCallCard_PDom' => $result['CmpCallCard_PDom'],
					'CmpCallCard_PKvar' => $result['CmpCallCard_PKvar'],
					// 'CmpLpu_aid' => $result['CmpLpu_aid'],
					// 'CmpCallCard_Medc' => $result['CmpCallCard_Medc'],
					'CmpCallCard_Izv1' => $result['CmpCallCard_Izv1'],
					'CmpCallCard_Tiz1' => $result['CmpCallCard_Tiz1'],
					'CmpCallCard_Inf1' => $result['CmpCallCard_Inf1'],
					'CmpCallCard_Inf2' => $result['CmpCallCard_Inf2'],
					'CmpCallCard_Inf3' => $result['CmpCallCard_Inf3'],
					'CmpCallCard_Inf4' => $result['CmpCallCard_Inf4'],
					'CmpCallCard_Inf5' => $result['CmpCallCard_Inf5'],
					'CmpCallCard_Inf6' => $result['CmpCallCard_Inf6'],
					'CmpCallCard_firstVersion' => $result['CmpCallCard_firstVersion'],
					'KLRgn_id' => $result['KLRgn_id'],
					'KLSubRgn_id' => $result['KLSubRgn_id'],
					'KLCity_id' => $result['KLCity_id'],
					'KLTown_id' => $result['KLTown_id'],
					'KLStreet_id' => $result['KLStreet_id'],
					'UnformalizedAddressDirectory_id' => $result['UnformalizedAddressDirectory_id'],
					'UslugaComplex_id' => $result['UslugaComplex_id'],
					'Lpu_id' => $result['Lpu_id'],
					'CmpCallCardStatusType_id' => $result['CmpCallCardStatusType_id'],
					'Lpu_ppdid' => $result['Lpu_ppdid'],
					'CmpCallCard_IsOpen' => $result['CmpCallCard_IsOpen'],
					'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $result ) ? $result['CmpCallCard_IsReceivedInPPD'] : 1,
					'pmUser_id' => $result['pmUser_insID']
				);

				$result = $this->db->query($query, $queryParams);
				$newfield= array();
				$newfield = $result->result('array');
			}

			//3 - заменяем старую запись текущими изменениями

			if  ( ($data['ARMType']!='smpadmin') || (($data['ARMType']=='smpadmin') && (isset($data['CmpCallCard_id'])) ) )//else
			{
				$procedure = 'p_CmpCallCard_setCardUpd';
				$statuschange = false;
				$UnicNums = ';';
				$CmpCallCard_Numv = ':CmpCallCard_Numv';
				$CmpCallCard_Ngod = ':CmpCallCard_Ngod';
			}				
		}

		if ( isset($data['CmpCallCard_prmTime']) ) {
			$data['CmpCallCard_prmDate'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
		}
		
		if (isset($data['CmpCallCard_id'])){
			$query = "
				SELECT * 
				FROM v_CmpCallCard CCC with (nolock) 
				WHERE CCC.CmpCallCard_id = " . $data['CmpCallCard_id'] . ";
			";
			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				return false;
			}
			$result = $result->result('array');
			$result = $result[0];
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

				".$UnicNums."

			
			set @Res = :CmpCallCard_id;
			
			exec " . $procedure . "
				@CmpCallCard_id = @Res output,
				@CmpCallCard_Numv = ".$CmpCallCard_Numv.",
				@CmpCallCard_Ngod = ".$CmpCallCard_Ngod.",
				@CmpCallCard_Prty = :CmpCallCard_Prty,
				@CmpCallCard_Sect = :CmpCallCard_Sect,
				@CmpArea_id = :CmpArea_id,
				@CmpCallCard_City = :CmpCallCard_City,
				@CmpCallCard_Ulic = :CmpCallCard_Ulic,
				@CmpCallCard_Dom = :CmpCallCard_Dom,
				@CmpCallCard_Korp = :CmpCallCard_Korp,
				@CmpCallCard_Room = :CmpCallCard_Room,
				@CmpCallCard_Kvar = :CmpCallCard_Kvar,
				@CmpCallCard_Podz = :CmpCallCard_Podz,
				@CmpCallCard_Etaj = :CmpCallCard_Etaj,
				@CmpCallCard_Kodp = :CmpCallCard_Kodp,
				@CmpCallCard_Telf = :CmpCallCard_Telf,
				@CmpPlace_id = :CmpPlace_id,
				@CmpCallCard_Comm = :CmpCallCard_Comm,
				@CmpReason_id = :CmpReason_id,
				@Person_id = :Person_id,
				@Person_SurName = :Person_Surname,
				@Person_FirName = :Person_Firname,
				@Person_SecName = :Person_Secname,
				@Person_Age = :Person_Age,
				@Person_BirthDay = :Person_Birthday,
				@Person_PolisNum = :Person_PolisNum,
				@Person_PolisSer = :Person_PolisSer,
				@Sex_id = :Sex_id,
				@CmpCallCard_Ktov = :CmpCallCard_Ktov,
				@CmpCallerType_id = :CmpCallerType_id,
				@CmpCallType_id = :CmpCallType_id,
				@CmpProfile_cid = :CmpProfile_cid,
				@CmpCallCard_Smpt = :CmpCallCard_Smpt,
				@CmpCallCard_Stan = :CmpCallCard_Stan,
				@CmpCallCard_prmDT = :CmpCallCard_prmDT,
				@CmpCallCard_Line = :CmpCallCard_Line,
				@CmpResult_id = :CmpResult_id,
				@ResultDeseaseType_id = :ResultDeseaseType_id,
				@CmpArea_gid = :CmpArea_gid,
				@CmpLpu_id = :CmpLpu_id,
				@CmpDiag_oid = :CmpDiag_oid,
				@CmpDiag_aid = :CmpDiag_aid,
				@CmpTrauma_id = :CmpTrauma_id,
				@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
				@Diag_uid = :Diag_uid,
				@CmpCallCard_Numb = :CmpCallCard_Numb,
				@CmpCallCard_Smpb = :CmpCallCard_Smpb,
				@CmpCallCard_Stbr = :CmpCallCard_Stbr,
				@CmpCallCard_Stbb = :CmpCallCard_Stbb,
				@CmpProfile_bid = :CmpProfile_bid,
				@CmpCallCard_Ncar = :CmpCallCard_Ncar,
				@CmpCallCard_RCod = :CmpCallCard_RCod,
				@CmpCallCard_TabN = :CmpCallCard_TabN,
				@CmpCallCard_Dokt = :CmpCallCard_Dokt,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@CmpCallCard_IsMedPersonalIdent = :CmpCallCard_IsMedPersonalIdent,
				@CmpCallCard_Tab2 = :CmpCallCard_Tab2,
				@CmpCallCard_Tab3 = :CmpCallCard_Tab3,
				@CmpCallCard_Tab4 = :CmpCallCard_Tab4,
				@Diag_sid = :Diag_sid,
				@CmpTalon_id = :CmpTalon_id,
				@CmpCallCard_Expo = :CmpCallCard_Expo,
				@CmpCallCard_Smpp = :CmpCallCard_Smpp,
				@CmpCallCard_Vr51 = :CmpCallCard_Vr51,
				@CmpCallCard_D201 = :CmpCallCard_D201,
				@CmpCallCard_Dsp1 = :CmpCallCard_Dsp1,
				@CmpCallCard_Dsp2 = :CmpCallCard_Dsp2,
				@CmpCallCard_Dspp = :CmpCallCard_Dspp,
				@CmpCallCard_Dsp3 = :CmpCallCard_Dsp3,
				@CmpCallCard_Kakp = :CmpCallCard_Kakp,
				@CmpCallCard_Tper = :CmpCallCard_Tper,
				@CmpCallCard_Vyez = :CmpCallCard_Vyez,
				@CmpCallCard_Przd = :CmpCallCard_Przd,
				@CmpCallCard_Tgsp = :CmpCallCard_Tgsp,
				@CmpCallCard_Tsta = :CmpCallCard_Tsta,
				@CmpCallCard_Tisp = :CmpCallCard_Tisp,
				@CmpCallCard_Tvzv = :CmpCallCard_Tvzv,
				@CmpCallCard_Kilo = :CmpCallCard_Kilo,
				@CmpCallCard_Dlit = :CmpCallCard_Dlit,
				@CmpCallCard_Prdl = :CmpCallCard_Prdl,
				@CmpArea_pid = :CmpArea_pid,
				@CmpCallCard_PCity = :CmpCallCard_PCity,
				@CmpCallCard_PUlic = :CmpCallCard_PUlic,
				@CmpCallCard_PDom = :CmpCallCard_PDom,
				@CmpCallCard_PKvar = :CmpCallCard_PKvar,
				@CmpCallCard_Izv1 = :CmpCallCard_Izv1,
				@CmpCallCard_Tiz1 = :CmpCallCard_Tiz1,
				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
				@CmpCallCard_Inf1 = :CmpCallCard_Inf1,
				@CmpCallCard_Inf2 = :CmpCallCard_Inf2,
				@CmpCallCard_Inf3 = :CmpCallCard_Inf3,
				@CmpCallCard_Inf4 = :CmpCallCard_Inf4,
				@CmpCallCard_Inf5 = :CmpCallCard_Inf5,
				@CmpCallCard_Inf6 = :CmpCallCard_Inf6,				
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,				
				@UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id,
				@UslugaComplex_id = :UslugaComplex_id,

				@Lpu_ppdid = :Lpu_ppdid,
				@Lpu_id = :Lpu_id,
				@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		if (isset($result) && isset($data['CmpCallCard_Tper']) && $data['CmpCallCard_Tper'] != '') 
			$result['CmpCallCard_Tper'] = $result['CmpCallCard_Tper']->format('Y-m-d').' '.$data['CmpCallCard_Tper'].':00';
		if (isset($result) && isset($data['CmpCallCard_Vyez']) && $data['CmpCallCard_Vyez'] != '') 
			$result['CmpCallCard_Vyez'] = $result['CmpCallCard_Vyez']->format('Y-m-d').' '.$data['CmpCallCard_Vyez'].':00';
		if (isset($result) && isset($data['CmpCallCard_Przd']) && $data['CmpCallCard_Przd'] != '') 
			$result['CmpCallCard_Przd'] = $result['CmpCallCard_Przd']->format('Y-m-d').' '.$data['CmpCallCard_Przd'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tgsp']) && $data['CmpCallCard_Tgsp'] != '') 
			$result['CmpCallCard_Tgsp'] = $result['CmpCallCard_Tgsp']->format('Y-m-d').' '.$data['CmpCallCard_Tgsp'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tsta']) && $data['CmpCallCard_Tsta'] != '') 
			$result['CmpCallCard_Tsta'] = $result['CmpCallCard_Tsta']->format('Y-m-d').' '.$data['CmpCallCard_Tsta'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tisp']) && $data['CmpCallCard_Tisp'] != '') 
			$result['CmpCallCard_Tisp'] = $result['CmpCallCard_Tisp']->format('Y-m-d').' '.$data['CmpCallCard_Tisp'].':00';
		if (isset($result) && isset($data['CmpCallCard_Tvzv']) && $data['CmpCallCard_Tvzv'] != '') 
			$result['CmpCallCard_Tvzv'] = $result['CmpCallCard_Tvzv']->format('Y-m-d').' '.$data['CmpCallCard_Tvzv'].':00';
		$data['CmpCallCardStatusType_id'] = ( empty($data['CmpCallCardStatusType_id']) && !empty($result['CmpCallCardStatusType_id']))? 
			$result['CmpCallCardStatusType_id'] : null;
		
		$queryParams = array(
			'Lpu_id_forUnicNumRequest' => $data['Lpu_id'],
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_Numv' => $data['CmpCallCard_Numv'],
			'CmpCallCard_Ngod' => $data['CmpCallCard_Ngod'],
			'CmpCallCard_Prty' => $data['CmpCallCard_Prty'],
			'CmpCallCard_Sect' => $data['CmpCallCard_Sect'],
			'CmpArea_id' => $data['CmpArea_id'],
			'CmpCallCard_City' => $data['CmpCallCard_City'],
			'CmpCallCard_Ulic' => $data['CmpCallCard_Ulic'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Korp' => $data['CmpCallCard_Korp'],
			'CmpCallCard_Room' => $data['CmpCallCard_Room'],
			'CmpCallCard_Dom' => $data['CmpCallCard_Dom'],
			'CmpCallCard_Kvar' => $data['CmpCallCard_Kvar'],
			'CmpCallCard_Podz' => $data['CmpCallCard_Podz'],
			'CmpCallCard_Etaj' => $data['CmpCallCard_Etaj'],
			'CmpCallCard_Kodp' => $data['CmpCallCard_Kodp'],
			'CmpCallCard_Telf' => $data['CmpCallCard_Telf'],
			'CmpPlace_id' => $data['CmpPlace_id'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpReason_id' => (isset($data['CmpReason_id']) && $data['CmpReason_id'] > 0)?$data['CmpReason_id']:((isset($data['CallPovod_id']) && $data['CallPovod_id'] > 0)?$data['CallPovod_id']:null),
			'Person_id' => $data['Person_id'],
			'Person_Surname' => $data['Person_Surname'],
			'Person_Firname' => $data['Person_Firname'],
			'Person_Secname' => $data['Person_Secname'],
			'Person_Age' => $data['Person_Age'],
			'Person_Birthday' => $data['Person_Birthday'],
			'Person_PolisSer' => $data['Polis_Ser'],
			'Person_PolisNum' => $data['Polis_Num'],
			'Sex_id' => $data['Sex_id'],
			'CmpCallCard_Ktov' => (!empty( $data[ 'CmpCallCard_Ktov' ] ) ? $data[ 'CmpCallCard_Ktov' ] : null),
			'CmpCallerType_id' => (!empty( $data[ 'CmpCallerType_id' ] ) ? $data[ 'CmpCallerType_id' ] : null),
			'CmpCallType_id' => $data['CmpCallType_id'],
			'CmpProfile_cid' => $data['CmpProfile_cid'],
			'CmpCallCard_Smpt' => $data['CmpCallCard_Smpt'],
			'CmpCallCard_Stan' => $data['CmpCallCard_Stan'],
			'CmpCallCard_prmDT' => $data['CmpCallCard_prmDate'],
			'CmpCallCard_Line' => $data['CmpCallCard_Line'],
			'CmpResult_id' => $data['CmpResult_id'],
			'ResultDeseaseType_id' => $data['ResultDeseaseType_id'],
			'CmpArea_gid' => $data['CmpArea_gid'],
			'CmpLpu_id' => $data['CmpLpu_id'],
			'CmpDiag_oid' => $data['CmpDiag_oid'],
			'CmpDiag_aid' => $data['CmpDiag_aid'],
			'CmpTrauma_id' => $data['CmpTrauma_id'],
			'CmpCallCard_IsAlco' => $data['CmpCallCard_IsAlco'],
			'Diag_uid' => $data['Diag_uid'],
			'CmpCallCard_Numb' => $data['CmpCallCard_Numb'],
			'CmpCallCard_Smpb' => $data['CmpCallCard_Smpb'],
			'CmpCallCard_Stbr' => $data['CmpCallCard_Stbr'],
			'CmpCallCard_Stbb' => $data['CmpCallCard_Stbb'],
			'CmpProfile_bid' => $data['CmpProfile_bid'],
			'CmpCallCard_Ncar' => $data['CmpCallCard_Ncar'],
			'CmpCallCard_RCod' => $data['CmpCallCard_RCod'],
			'CmpCallCard_TabN' => $data['CmpCallCard_TabN'],
			'CmpCallCard_Dokt' => $data['CmpCallCard_Dokt'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'CmpCallCard_IsMedPersonalIdent' => $data['CmpCallCard_IsMedPersonalIdent'],
			'CmpCallCard_Tab2' => $data['CmpCallCard_Tab2'],
			'CmpCallCard_Tab3' => $data['CmpCallCard_Tab3'],
			'CmpCallCard_Tab4' => $data['CmpCallCard_Tab4'],
			'Diag_sid' => $data['Diag_sid'],
			'CmpTalon_id' => $data['CmpTalon_id'],
			'CmpCallCard_Expo' => $data['CmpCallCard_Expo'],
			'CmpCallCard_Smpp' => $data['CmpCallCard_Smpp'],
			'CmpCallCard_Vr51' => $data['CmpCallCard_Vr51'],
			'CmpCallCard_D201' => $data['CmpCallCard_D201'],
			'CmpCallCard_Dsp1' => $data['CmpCallCard_Dsp1'],
			'CmpCallCard_Dsp2' => $data['CmpCallCard_Dsp2'],
			'CmpCallCard_Dspp' => $data['CmpCallCard_Dspp'],
			'CmpCallCard_Dsp3' => $data['CmpCallCard_Dsp3'],
			'CmpCallCard_Kakp' => $data['CmpCallCard_Kakp'],		
			
			'CmpCallCard_Tper' => (isset($result))?$result['CmpCallCard_Tper']:'',
			'CmpCallCard_Vyez' => (isset($result))?$result['CmpCallCard_Vyez']:'',
			'CmpCallCard_Przd' => (isset($result))?$result['CmpCallCard_Przd']:'',
			'CmpCallCard_Tgsp' => (isset($result))?$result['CmpCallCard_Tgsp']:'',
			'CmpCallCard_Tsta' => (isset($result))?$result['CmpCallCard_Tsta']:'',
			'CmpCallCard_Tisp' => (isset($result))?$result['CmpCallCard_Tisp']:'',
			'CmpCallCard_Tvzv' => (isset($result))?$result['CmpCallCard_Tvzv']:'',
			'CmpCallCard_Kilo' => $data['CmpCallCard_Kilo'],
			'CmpCallCard_Dlit' => $data['CmpCallCard_Dlit'],
			'CmpCallCard_Prdl' => $data['CmpCallCard_Prdl'],
			'CmpArea_pid' => $data['CmpArea_pid'],
			'CmpCallCard_PCity' => $data['CmpCallCard_PCity'],
			'CmpCallCard_PUlic' => $data['CmpCallCard_PUlic'],
			'CmpCallCard_PDom' => $data['CmpCallCard_PDom'],
			'CmpCallCard_PKvar' => $data['CmpCallCard_PKvar'],
			// 'CmpLpu_aid' => $data['CmpLpu_aid'],
			// 'CmpCallCard_Medc' => $data['CmpCallCard_Medc'],
			'CmpCallCard_Izv1' => $data['CmpCallCard_Izv1'],
			'CmpCallCard_Tiz1' => $data['CmpCallCard_Tiz1'],
			'CmpCallCard_Inf1' => $data['CmpCallCard_Inf1'],
			'CmpCallCard_Inf2' => $data['CmpCallCard_Inf2'],
			'CmpCallCard_Inf3' => $data['CmpCallCard_Inf3'],
			'CmpCallCard_Inf4' => $data['CmpCallCard_Inf4'],
			'CmpCallCard_Inf5' => $data['CmpCallCard_Inf5'],
			'CmpCallCard_Inf6' => $data['CmpCallCard_Inf6'],
			'KLRgn_id' => $data['KLRgn_id'],
			'KLSubRgn_id' => $data['KLSubRgn_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'KLStreet_id' => $data['KLStreet_id'],
			'UnformalizedAddressDirectory_id' => $data['UnformalizedAddressDirectory_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'Lpu_id' => $data['Lpu_id'],
			'CmpCallCardStatusType_id' => $data['CmpCallCardStatusType_id'],
			'Lpu_ppdid' => $data['Lpu_ppdid'],
			'CmpCallCard_IsOpen' => $data['CmpCallCard_IsOpen'],
			'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) ? $data['CmpCallCard_IsReceivedInPPD'] : 1,
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);
		$resultforstatus = array();
		$resultforstatus = $result->result('array');
		if ( is_object($result) ) {			
			if ((($data['ARMType']=='smpreg')||($data['ARMType']=='smpdispatchdirect')) && $statuschange) {				
				$resultforstatus = array();
				$resultforstatus = $result->result('array');
				$data['CmpCallCard_id'] = $resultforstatus[0]['CmpCallCard_id'];
				$data['CmpCallCardStatusType_id'] = 1;
				$data['CmpCallCardStatus_Comment'] = '';
				$this->setStatusCmpCallCard($data);
			}
			
			//4 - апдейтим версию записи

			if (($data['ARMType']=='smpadmin') && (isset($data['CmpCallCard_id'])) )		
			{
				$query = "
						exec p_CmpCallCard_setFirstVersion 
						@CmpCallCard_id = " . $data['CmpCallCard_id'] . ",
						@CmpCallCard_firstVersion = " . $newfield[0]['CmpCallCard_id'] . ",
						@pmUser_id = " . $data['pmUser_id'] . ";							
					";
				$res = $this->db->query($query, $data);
			}
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	*/
	
	/**
	 *
	 * загрузка карты смп
	 *
	 * @param type $data
	 * @return boolean 
	 */
	/*function loadCmpCallCardEditForm($data) {
		$query = "
			select top 1
				'' as accessType,
				CCC.CmpCallCard_id,
				ISNULL(CCC.Person_id, 0) as Person_id,
				CCC.CmpArea_gid,
				CCC.CmpArea_id,
				CCC.CmpArea_pid,
				CCC.CmpCallCard_IsAlco,
				CCC.CmpCallCard_IsPoli,
				CCC.CmpCallType_id,
				CCC.CmpDiag_aid,
				CCC.CmpDiag_oid,
				CCC.CmpLpu_aid,
				CCC.CmpLpu_id,
				CL.Lpu_id as Lpu_oid,
				CCC.CmpPlace_id,
				CCC.CmpProfile_bid,
				CCC.CmpProfile_cid,
				CCC.CmpReason_id,
				CCC.CmpReasonNew_id,
				CCC.CmpResult_id,
				CCC.ResultDeseaseType_id,
				CCC.CmpTalon_id,
				CCC.CmpTrauma_id,
				CCC.Diag_sid,
				CCC.Diag_uid,
				CCC.Sex_id as Sex_id,
				PS.Sex_id as SexIdent_id,
				CCC.CmpCallCard_Numv,
				CCC.CmpCallCard_Ngod,
				CCC.CmpCallCard_Prty,
				CCC.CmpCallCard_Sect,
				CCC.CmpCallCard_City,
				CCC.CmpCallCard_Ulic,
				CCC.CmpCallCard_Dom,
				CCC.CmpCallCard_Korp,
				CCC.CmpCallCard_Room,
				CCC.CmpCallCard_Kvar,
				CCC.CmpCallCard_Podz,
				CCC.CmpCallCard_Etaj,
				CCC.CmpCallCard_Kodp,
				CCC.CmpCallCard_Telf,
				CCC.CmpCallCard_Comm,
				RTRIM(LTRIM(case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end)) as Person_Surname,
				RTRIM(LTRIM(case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end)) as Person_Firname,
				RTRIM(LTRIM(case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end)) as Person_Secname,
				RTRIM(LTRIM(ISNULL(PS.Person_Surname, ''))) as PersonIdent_Surname,
				RTRIM(LTRIM(ISNULL(PS.Person_Firname, ''))) as PersonIdent_Firname,
				RTRIM(LTRIM(ISNULL(PS.Person_Secname, ''))) as PersonIdent_Secname,
				convert(varchar(10), CCC.Person_BirthDay, 104) as Person_Birthday,
				CCC.Person_Age as Person_Age,
				ISNULL(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), '') as PersonIdent_Age,
				CCC.Person_PolisSer as Polis_Ser,
				CCC.Person_PolisNum as Polis_Num,
				PS.Person_EdNum as Polis_EdNum,
				PS.Polis_Num as PolisIdent_Num,
				CCC.CmpCallCard_Ktov,
				CCC.CmpCallerType_id,
				CCC.CmpCallCard_Smpt,
				CCC.CmpCallCard_Stan,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
				convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
				CCC.CmpCallCard_Line,
				CCC.CmpCallCard_Numb,
				CCC.CmpCallCard_Smpb,
				CCC.CmpCallCard_Stbr,
				CCC.CmpCallCard_Stbb,
				CCC.CmpCallCard_Ncar,
				CCC.CmpCallCard_RCod,
				CCC.CmpCallCard_TabN,
				CCC.CmpCallCard_Dokt,
				CCC.MedPersonal_id,
				isnull(CCC.MedStaffFact_id, msf1.MedStaffFact_id) as MedStaffFact_id,
				ISNULL(CCC.CmpCallCard_IsMedPersonalIdent,1) as CmpCallCard_IsMedPersonalIdent,
				CCC.CmpCallCard_Tab2,
				CCC.CmpCallCard_Tab3,
				CCC.CmpCallCard_Tab4,
				CCC.CmpCallCard_Expo,
				CCC.CmpCallCard_Smpp,
				CCC.CmpCallCard_Vr51,
				CCC.CmpCallCard_D201,
				CCC.CmpCallCard_Dsp1,
				CCC.CmpCallCard_Dsp2,
				CCC.CmpCallCard_Dsp3,
				CCC.CmpCallCard_Dspp,
				CCC.CmpCallCard_Kakp,
				convert(varchar(5), CCC.CmpCallCard_Tper, 108) as CmpCallCard_Tper,
				convert(varchar(5), CCC.CmpCallCard_Vyez, 108) as CmpCallCard_Vyez,
				convert(varchar(5), CCC.CmpCallCard_Przd, 108) as CmpCallCard_Przd,
				convert(varchar(5), CCC.CmpCallCard_Tgsp, 108) as CmpCallCard_Tgsp,
				convert(varchar(5), CCC.CmpCallCard_Tsta, 108) as CmpCallCard_Tsta,
				convert(varchar(5), CCC.CmpCallCard_Tisp, 108) as CmpCallCard_Tisp,
				convert(varchar(5), CCC.CmpCallCard_Tvzv, 108) as CmpCallCard_Tvzv,
				CCC.CmpCallCard_Kilo,
				CCC.CmpCallCard_Dlit,
				CCC.CmpCallCard_Prdl,
				CCC.CmpCallCard_PCity,
				CCC.CmpCallCard_PUlic,
				CCC.CmpCallCard_PDom,
				CCC.CmpCallCard_PKvar,
				CCC.cmpCallCard_Medc,
				CCC.CmpCallCard_Izv1,
				convert(varchar(5), CCC.CmpCallCard_Tiz1, 108) as CmpCallCard_Tiz1,
				CCC.CmpCallCard_Inf1,
				CCC.CmpCallCard_Inf2,
				CCC.CmpCallCard_Inf3,
				CCC.CmpCallCard_Inf4,
				CCC.CmpCallCard_Inf5,
				CCC.CmpCallCard_Inf6
				,UslugaComplex_id
				,CCC.Lpu_id
				,CCC.Lpu_ppdid
				,ISNULL(L.Lpu_Nick,'') as CmpLpu_Name
				,CASE WHEN ISNULL(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as Person_isOftenCaller
				
				,CCC.UnformalizedAddressDirectory_id
				,case when isnull(CCC.KLStreet_id,0) = 0 then
					case when isnull(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL
					else 'UA.'+CAST(CCC.UnformalizedAddressDirectory_id as varchar(8)) end
				else 'ST.'+CAST(CCC.KLStreet_id as varchar(8)) end as StreetAndUnformalizedAddressDirectory_id
				
				,CCC.KLRgn_id
				,CCC.KLSubRgn_id
				,CCC.KLCity_id
				,CCC.KLTown_id
				,CCC.KLStreet_id
			from
				v_CmpCallCard CCC with (nolock)
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				-- left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				outer apply(
					select top 1
						 pa.Person_id
						,ISNULL(pa.Person_SurName, '') as Person_Surname
						,ISNULL(pa.Person_FirName, '') as Person_Firname
						,ISNULL(pa.Person_SecName, '') as Person_Secname
						,pa.Person_BirthDay as Person_Birthday
						,ISNULL(pa.Sex_id, 0) as Sex_id
						,pa.Person_EdNum
						,ISNULL(p.Polis_Num, '') as Polis_Num
					from
						v_Person_all pa with (nolock)
						left join v_Polis p (nolock) on p.Polis_id = pa.Polis_id
					where
						Person_id = CCC.Person_id
						and PersonEvn_insDT <= CCC.CmpCallCard_prmDT
					order by
						PersonEvn_insDT desc
				) PS
				outer apply (
					select top 1 MedStaffFact_id
					from v_MedStaffFact with(nolock)
					where MedPersonal_id = CCC.MedPersonal_id
					and Lpu_id = CCC.CmpLpu_id
					order by PostOccupationType_id asc
				) msf1
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				LEFT JOIN v_OftenCallers OC with (nolock) on OC.Person_id = CCC.Person_id
			where
				CCC.CmpCallCard_id = :CmpCallCard_id
		";

		
		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'Lpu_ppdid' => $data['Lpu_id']
		));
		
		if ( is_object($result) ) {			
			//var_dump($result->result('array')); exit;
			return $result->result('array');
		}
		else {
			return false;
		}
	}*/

	/**
	 * Человеческая дата
	 */
	function peopleDate($str) {	
		$s = explode(' ',$str);
		$m=array(
			'Jan'=>'01',
			'Feb'=>'02',
			'Mar'=>'03',
			'Apr'=>'04',
			'May'=>'05',
			'Jun'=>'06',
			'Jul'=>'07',
			'Aug'=>'08',
			'Sep'=>'09',
			'Oct'=>'10',
			'Nov'=>'11',
			'Dec'=>'12'
		);
		return $s[2].'.'.$m[$s[1]].'.'.$s[3].' '.$s[4];		
	}	
	
}
