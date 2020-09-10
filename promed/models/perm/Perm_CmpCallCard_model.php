<?php
require_once(APPPATH.'models/CmpCallCard_model.php');

class Perm_CmpCallCard_model extends CmpCallCard_model {

	/**
	 * Привязка забронированной экстренной койки к карте вызова
	 */
	/*
	function setCmpCloseCardTimetable($data) {
		return true;
	}
	*/
	
	/**
	 * default desc 
	 */
	/*
	public function saveCmpCallCard($data) {
		// Если случай закрыт и задана дата справки, то сохраняем справку.
		if (!empty($data['CmpCallCard_id']) && !empty($data['CmpResult_id']) && !empty($data['CmpCallCardCostPrint_setDT']))
		{
			// сохраняем справку
			$this->load->model('CostPrint_model');
			$this->CostPrint_model->saveCmpCallCardCostPrint(array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'CostPrint_IsNoPrint' => $data['CmpCallCardCostPrint_IsNoPrint'],
				'CostPrint_setDT' => $data['CmpCallCardCostPrint_setDT'],
				'pmUser_id' => $data['pmUser_id']
			));
		}
		if(!empty($data['CmpCallCard_prmDate'])){
			$DPRM = DateTime::createFromFormat('Y-m-d H:i', $data['CmpCallCard_prmDate'] . ' ' . $data['CmpCallCard_prmTime']);

			if ( is_object($DPRM) ) {
				foreach ( $data as $key => $val ) {
					switch ( $key ) {
						case'CmpCallCard_Tper':
						case'CmpCallCard_Vyez':
						case'CmpCallCard_Przd':
						case'CmpCallCard_Tgsp':
						case'CmpCallCard_Tsta':
						case'CmpCallCard_Tisp':
						case'CmpCallCard_Tvzv':
						case'CmpCallCard_Tiz1':
							if (!empty($data[$key])) {
								$parsed = DateTime::createFromFormat('Y-m-d H:i', $data['CmpCallCard_prmDate'] . ' ' . $data[$key]);
								//echo$parsed;
								if ( is_object($parsed) ) {
									if ( $parsed < $DPRM ) {
										$parsed->add(new DateInterval('P1D'));
									}
									$data[$key] = $parsed->format('Y-m-d H:i:s');
								}else {
									$data[$key] = $data['CmpCallCard_prmDate'] . ' ' . $data[$key];
								}
							}
							break;
					}
				}
			}
		}
		
	
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
					N'SELECT @UnicCmpCallCard_NumvOUT = ISNULL(MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."''
					THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END),0)+1,
					@UnicCmpCallCard_NgodOUT = ISNULL(MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')."
					THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END),0)+1
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
						N'SELECT @UnicCmpCallCard_NumvOUT = ISNULL(MAX(CASE WHEN CAST( CCC.CmpCallCard_prmDT as date) = ''".date('Y-m-d')."''
						THEN ISNULL(CmpCallCard_Numv,0) ELSE 0 END),0)+1,
						@UnicCmpCallCard_NgodOUT = ISNULL(MAX(CASE WHEN YEAR( CCC.CmpCallCard_prmDT ) = ".date('Y')."
						THEN ISNULL(CmpCallCard_Ngod,0) ELSE 0 END),0)+1
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
						@CmpCallCard_rid = :CmpCallCard_rid,
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
						@CmpCallPlaceType_id = :CmpCallPlaceType_id,
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
						@LeaveType_id = :LeaveType_id,
						@CmpArea_gid = :CmpArea_gid,
						@CmpLpu_id = :CmpLpu_id,
						@CmpDiag_oid = :CmpDiag_oid,
						@CmpDiag_aid = :CmpDiag_aid,
						@CmpTrauma_id = :CmpTrauma_id,
						@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
						@RankinScale_id = :RankinScale_id,
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
						@LpuBuilding_id = :LpuBuilding_id,
						@Lpu_ppdid = :Lpu_ppdid,
						@Lpu_id = :Lpu_id,
						@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
						
						@CmpCallCard_Condition = :CmpCallCard_Condition,
						@CmpCallCard_Recomendations = :CmpCallCard_Recomendations,
						@LpuSection_id = :LpuSection_id,
						@CmpCallCard_isShortEditVersion = :CmpCallCard_isShortEditVersion,
						
						@CmpCallCard_IsPoli = :CmpCallCard_IsPoli,
						@Lpu_cid = :Lpu_cid,
						
						@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,

						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				
				$queryParams = array(
					'Lpu_id_forUnicNumRequest' => $result['Lpu_id'],
					'CmpCallCard_id' => $result['CmpCallCard_id'],
					'CmpCallCard_rid' => (!empty($result['CmpCallCard_rid']) ? $result['CmpCallCard_rid'] : NULL),
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
					'CmpCallPlaceType_id' => $result['CmpCallPlaceType_id'],
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
					'LeaveType_id'=> $result['LeaveType_id'],
					'CmpArea_gid' => $result['CmpArea_gid'],
					'CmpLpu_id' => $result['CmpLpu_id'],
					'CmpDiag_oid' => $result['CmpDiag_oid'],
					'CmpDiag_aid' => $result['CmpDiag_aid'],
					'CmpTrauma_id' => $result['CmpTrauma_id'],
					'CmpCallCard_IsAlco' => $result['CmpCallCard_IsAlco'],
					'RankinScale_id' => $result['RankinScale_id'],
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
					'LpuBuilding_id' => $result['LpuBuilding_id'],
					'CmpCallCard_IsOpen' => $result['CmpCallCard_IsOpen'],
					'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $result ) ? $result['CmpCallCard_IsReceivedInPPD'] : 1,
					
					'CmpCallCard_Condition' =>   (!empty($result['CmpCallCard_Condition']))?$result['CmpCallCard_Condition']:null,
					'CmpCallCard_Recomendations' => (!empty($result['CmpCallCard_Recomendations']))?$result['CmpCallCard_Recomendations']:null,
					'LpuSection_id' => (!empty($result['LpuSection_id']))?$result['LpuSection_id']:null,
					'CmpCallCard_isShortEditVersion' => (!empty($result['CmpCallCard_isShortEditVersion']))?$result['CmpCallCard_isShortEditVersion']:null,
					
					'CmpCallCard_IsPoli'  => (!empty($result['CmpCallCard_IsPoli']))?$result['CmpCallCard_IsPoli']:null,
					'Lpu_cid'  => (!empty($result['Lpu_cid']))?$result['Lpu_cid']:null,
					
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

		//Определение MedStaffFact_id
		if (!empty($data['MedPersonal_id']) && empty($data['MedStaffFact_id'])) {
			$query = "
				select top 1
					MedStaffFact_id
				from v_MedStaffFact with(nolock)
				where
					MedPersonal_id = :MedPersonal_id
					and Lpu_id = :Lpu_id
				order by PostOccupationType_id asc
			";

			$queryParams = array(
				'MedPersonal_id' => $data['MedPersonal_id'],
				'CmpCallCard_prmDate' => $data['CmpCallCard_prmDate'],
				'Lpu_id' => $data['Lpu_id'],
			);

			$MedStaffFact_id = $this->getFirstResultFromQuery($query, $queryParams);
			if (!$MedStaffFact_id) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении рабочего места врача'));
			}
			$data['MedStaffFact_id'] = $MedStaffFact_id;
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
				@CmpCallCard_rid = :CmpCallCard_rid,
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
				@CmpCallPlaceType_id = :CmpCallPlaceType_id,
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
				@LeaveType_id = :LeaveType_id,
				@CmpArea_gid = :CmpArea_gid,
				@CmpLpu_id = :CmpLpu_id,
				@CmpDiag_oid = :CmpDiag_oid,
				@CmpDiag_aid = :CmpDiag_aid,
				@CmpTrauma_id = :CmpTrauma_id,
				@CmpCallCard_IsAlco = :CmpCallCard_IsAlco,
				@RankinScale_id = :RankinScale_id,
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
				@LpuBuilding_id = :LpuBuilding_id,
				@Lpu_ppdid = :Lpu_ppdid,
				@Lpu_id = :Lpu_id,
				@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@CmpCallCard_IndexRep = :CmpCallCard_IndexRep,
				@CmpCallCard_IndexRepInReg = :CmpCallCard_IndexRepInReg,
				
				@CmpCallCard_Condition = :CmpCallCard_Condition,
				@CmpCallCard_Recomendations = :CmpCallCard_Recomendations,
				@LpuSection_id = :LpuSection_id,
				@CmpCallCard_isShortEditVersion = :CmpCallCard_isShortEditVersion,
				
				@CmpCallCard_IsPoli = :CmpCallCard_IsPoli,
				@Lpu_cid = :Lpu_cid,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";	
		
		$data['CmpCallCard_Tper'] = ( empty($data['CmpCallCard_Tper']) && !empty($result['CmpCallCard_Tper']))? $result['CmpCallCard_Tper'] : $data['CmpCallCard_Tper'];
		$data['CmpCallCard_Vyez'] = ( empty($data['CmpCallCard_Vyez']) && !empty($result['CmpCallCard_Vyez']))? $result['CmpCallCard_Vyez'] : $data['CmpCallCard_Vyez'];
		$data['CmpCallCard_Przd'] = ( empty($data['CmpCallCard_Przd']) && !empty($result['CmpCallCard_Przd']))? $result['CmpCallCard_Przd'] : $data['CmpCallCard_Przd'];
		$data['CmpCallCard_Tgsp'] = ( empty($data['CmpCallCard_Tgsp']) && !empty($result['CmpCallCard_Tgsp']))? $result['CmpCallCard_Tgsp'] : $data['CmpCallCard_Tgsp'];
		$data['CmpCallCard_Tsta'] = ( empty($data['CmpCallCard_Tsta']) && !empty($result['CmpCallCard_Tsta']))? $result['CmpCallCard_Tsta'] : $data['CmpCallCard_Tsta'];
		$data['CmpCallCard_Tisp'] = ( empty($data['CmpCallCard_Tisp']) && !empty($result['CmpCallCard_Tisp']))? $result['CmpCallCard_Tisp'] : $data['CmpCallCard_Tisp'];
		$data['CmpCallCard_Tvzv'] = ( empty($data['CmpCallCard_Tvzv']) && !empty($result['CmpCallCard_Tvzv']))? $result['CmpCallCard_Tvzv'] : $data['CmpCallCard_Tvzv'];
		$data['CmpCallPlaceType_id'] = ( empty($data['CmpCallPlaceType_id']) && !empty($result['CmpCallPlaceType_id']))? $result['CmpCallPlaceType_id'] : $data['CmpCallPlaceType_id'];
		
		if(empty($result)){
			$data['CmpCallCardStatusType_id'] = !empty($data['CmpCallCardStatusType_id'])?$data['CmpCallCardStatusType_id']:null;
		}
		else{
			$data['CmpCallCardStatusType_id'] = !empty($result['CmpCallCardStatusType_id'])?$result['CmpCallCardStatusType_id']:null;
		}
		//$data['CmpCallCardStatusType_id'] = ( empty($data['CmpCallCardStatusType_id']) && !empty($result['CmpCallCardStatusType_id']))? ( ($result)?$result['CmpCallCardStatusType_id']:null) : $data['CmpCallCardStatusType_id'];

		$queryParams = array(
			'Lpu_id_forUnicNumRequest' => $data['Lpu_id'],
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCard_rid' => (!empty($data['CmpCallCard_rid']) ? $data['CmpCallCard_rid'] : NULL),
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
			'CmpCallPlaceType_id' => $data['CmpCallPlaceType_id'],
			'CmpPlace_id' => $data['CmpPlace_id'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpReason_id' => $data['CmpReason_id'],
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
			'LeaveType_id'=>$data['LeaveType_id'],
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
			
			'CmpCallCard_Tper' => $data['CmpCallCard_Tper'],
			'CmpCallCard_Vyez' => $data['CmpCallCard_Vyez'],
			'CmpCallCard_Przd' => $data['CmpCallCard_Przd'],
			'CmpCallCard_Tgsp' => $data['CmpCallCard_Tgsp'],
			'CmpCallCard_Tsta' => $data['CmpCallCard_Tsta'],
			'CmpCallCard_Tisp' => $data['CmpCallCard_Tisp'],
			'CmpCallCard_Tvzv' => $data['CmpCallCard_Tvzv'],
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
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']))?$data['LpuBuilding_id']: null,
			'CmpCallCard_IsOpen' => $data['CmpCallCard_IsOpen'],
			'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) ? $data['CmpCallCard_IsReceivedInPPD'] : 1,
			'CmpCallCard_IndexRep' =>  array_key_exists( 'CmpCallCard_IndexRep', $data ) ? $data['CmpCallCard_IndexRep'] : 0,
			'CmpCallCard_IndexRepInReg' =>  array_key_exists( 'CmpCallCard_IndexRepInReg', $data ) ? $data['CmpCallCard_IndexRepInReg'] : 1,
			'RankinScale_id' =>  (!empty($data['RankinScale_id']))?$data['RankinScale_id']:null,
			
			'CmpCallCard_Condition' => $data['CmpCallCard_Condition'],
			'CmpCallCard_Recomendations' => $data['CmpCallCard_Recomendations'],
			'LpuSection_id' => $data['LpuSection_id'],
			'CmpCallCard_isShortEditVersion' => $data['CmpCallCard_isShortEditVersion'],
			
			'CmpCallCard_IsPoli'  => (!empty($data['CmpCallCard_IsPoli']))?$data['CmpCallCard_IsPoli']:null,
			'Lpu_cid'  => (!empty($data['Lpu_cid']))?$data['Lpu_cid']:null,
			
			'pmUser_id' => $data['pmUser_id']
		);

		//echo getDebugSQL($query, $queryParams);exit;
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
	 * default desc
	 */
	 /*
	public function loadCmpCallCardEditForm($data) {
		$query = "
			select top 1
				'' as accessType,
				CCC.CmpCallCard_id,
				ISNULL(CCC.Person_id, 0) as Person_id,
				CCC.CmpArea_gid,
				CCC.CmpArea_id,
				CCC.CmpArea_pid,
				CCC.CmpCallCard_IsAlco,
				CCC.RankinScale_id,
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
				CCC.CmpResult_id,
				CCC.ResultDeseaseType_id,
				CCC.LeaveType_id,
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
				CCC.CmpCallPlaceType_id,
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
				CCC.CmpCallCard_prmDT,
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
				,convert(varchar(10), ccp.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDT
				,ccp.CmpCallCardCostPrint_IsNoPrint
				,ISNULL(CCC.CmpCallCard_IsPaid, 1) as CmpCallCard_IsPaid
				,ISNULL(CCC.CmpCallCard_IndexRep, 0) as CmpCallCard_IndexRep
				,ISNULL(CCC.CmpCallCard_IndexRepInReg, 1) as CmpCallCard_IndexRepInReg
				
				,CCC.LpuSection_id
				,ISNULL(CCC.CmpCallCard_isShortEditVersion,1) as CmpCallCard_isShortEditVersion
				,ISNULL(CCC.CmpCallCard_Condition,'') as CmpCallCard_Condition
				,ISNULL(CCC.CmpCallCard_Recomendations,'') as CmpCallCard_Recomendations
				,CCC.Lpu_cid
				,CCC.LpuBuilding_id
			from
				v_CmpCallCard CCC with (nolock)
				left join v_CmpCallCardCostPrint ccp (nolock) on ccp.CmpCallCard_id = CCC.CmpCallCard_id
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
			//'Lpu_ppdid' => $data['Lpu_id']
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
}
