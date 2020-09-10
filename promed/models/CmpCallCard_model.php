<?php
class CmpCallCard_model extends swModel {

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
		// Казахстан использует схему 101 для таблицы CmpCloseCardCombo а все остальные - дбо
		// на тесте у Казахстана установлена дефолтная схема 101 (и можно подумать что этот код не нужен), НО - на рабочем дефолтная DBO
		// @todo подумать над этими схемами, сделать единообразие какое-то
		// а сейчас пока костыль:
		if($this->regionNick == 'kz'){
			$this->comboSchema = $config['regions'][getRegionNumber()]['schema'];
		}
	}

	/**
	* Удаление записи о статусе карты
	*/
	function deleteCmpCallCardStatus($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется' ) );
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_CmpCallCardStatus_del
				@CmpCallCardStatus_id = :CmpCallCardStatus_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'CmpCallCardStatus_id' => $data['CmpCallCardStatus_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function importSMPCardsTest($data)
	{
		if (empty($data['Lpu_Name'])) {
			$data['Lpu_Name'] = '';
		}

		$query = "
			select count(*) as cnt from
				v_CmpCallCard (nolock) C
				left join v_Lpu L (nolock) on L.Lpu_id = C.Lpu_id
			where C.CmpCallCard_insDT between :CmpCallCard_insDT1 and :CmpCallCard_insDT2 and L.Lpu_Name like '%'+ :Lpu_Name +'%'
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return array('success' => true, 'cnt' => $resp[0]['cnt']);
			}
		}

		return false;
	}

	/**
	 * Удаление карты вызова
	 *
	 * @param array $data
	 * @param bool $ignoreRegistryCheck
	 * @return bool
	 */
	function deleteCmpCallCard($data = array(), $ignoreRegistryCheck = false, $delCallCard = true) {
        $result = array();
		$response = false;
        $error = array();
        $doc_array = array();
		// CmpCloseCard - карта вызова
		// CmpCallCard - талон вызова
		$isCloseCard = false; 

        if (!array_key_exists('CmpCallCard_id', $data) || !$data['CmpCallCard_id']) {
            $error[] = 'Не указан идентификатор карты вызова.';
        }

        if (count($error) == 0) {
            $checkLock = $this->checkLockCmpCallCard($data);
            if (isset($checkLock[0]['CmpCallCard_id'])) {
                $error[] = 'Карта вызова редактируется и не может быть удалена.';
            }
        }
		//признак источника
		$callCardInputTypeCode = $this->getCallCardInputTypeCode($data['CmpCallCard_id']);

        if (count($error) == 0 && $ignoreRegistryCheck === false) {
			$checkRegistryParam = $data;
			$query = "
                select
                    CmpCloseCard_id
                from
                    v_CmpCloseCard with (nolock)
                where
                    CmpCallCard_id = :CmpCallCard_id;
            ";
			$cclc_array = $this->queryResult($query, array(
				'CmpCallCard_id' => $data['CmpCallCard_id']
			));
			if(is_array($cclc_array) && count($cclc_array) > 0){
				$isCloseCard = (int)$cclc_array[0]['CmpCloseCard_id'];
				$checkRegistryParam['CmpCloseCard_id'] = $cclc_array[0]['CmpCloseCard_id'];
				$checkRegistryParam['CmpCallCard_id'] = null;
			}
            // Проверку наличия карты вызова в реестре
			$this->load->model('Registry' . (getRegionNick() == 'ufa' ? 'Ufa' : '') . '_model', 'Reg_model');
			$registryData = $this->Reg_model->checkEvnAccessInRegistry($checkRegistryParam);
			if ( is_array($registryData) ) {
				if(isset($registryData['Error_Msg'])){
					$registryData['Error_Msg'] = str_replace('Удаление записи невозможно', ' Удалите Карту вызова из реестра и повторите действие', $registryData['Error_Msg']);
				}
				return $registryData;
			}
			unset($checkRegistryParam);
        }

        //удаление информации о использовании медикаментов
        if (count($error) == 0) {
            $query = "
                select
                    CmpCallCardDrug_id,
                    DocumentUcStr_id
                from
                    v_CmpCallCardDrug with (nolock)
                where
                    CmpCallCard_id = :CmpCallCard_id;
            ";
            $cccd_array = $this->queryResult($query, array(
                'CmpCallCard_id' => $data['CmpCallCard_id']
            ));
            if (is_array($cccd_array)) {
                foreach($cccd_array as $cccd_data) {
                    if (!empty($cccd_data['DocumentUc_id']) && !in_array($cccd_data['DocumentUc_id'], $doc_array)) { //сбор идентификаторов документов
                        $doc_array[] = $cccd_data['DocumentUc_id'];
                    }
                    $response = $this->deleteCmpCallCardDrug(array(
                        'CmpCallCardDrug_id' => $cccd_data['CmpCallCardDrug_id'],
                        'DocumentUcStr_id' => $cccd_data['DocumentUcStr_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ));
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                        break;
                    }
                }
            }
        }

        //удаление пустых документов учета
        if (count($error) == 0 && count($doc_array) > 0) {
            $response = $this->deleteEmptyDocumentUc($doc_array);
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

        if (count($error) == 0 && $delCallCard) {
            $query = "
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
            $response = $this->getFirstRowFromQuery($query, array(
                'CmpCallCard_id' => $data['CmpCallCard_id'],
                'pmUser_id' => $data['pmUser_id']
            ));
            if ($response === false) {
                $error[] = "Во время удаления талона вызова произошла ошибка. При повторении ошибки обратитесь к администратору.";
            } else {
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }
		
		if ( $isCloseCard && !in_array($callCardInputTypeCode, array(1,2)) && count($error) == 0) {
            $query = "
                declare
                    @ErrCode int,
                    @ErrMessage varchar(4000);

                exec p_CmpCloseCard_del
                    @CmpCloseCard_id = :CmpCloseCard_id,
                    @pmUser_id = :pmUser_id,
                    @Error_Code = @ErrCode output,
                    @Error_Message = @ErrMessage output;

                select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
            ";
            $response = $this->getFirstRowFromQuery($query, array(
                'CmpCloseCard_id' => $isCloseCard,
                'pmUser_id' => $data['pmUser_id']
            ));
            if ($response === false){
                $error[] = "Во время удаления карты вызова произошла ошибка. При повторении ошибки обратитесь к администратору.";
            } elseif ( !empty($response['Error_Msg']) ){
				$error[] = $response['Error_Msg'];
            }
        }

        if (count($error) > 0) {
            $response = $this->createError(null, $error[0]);
        }

        return $response;
	}

	/**
	 * default desc
	 */
	function loadCmpCallCardJournalGrid($data) {
		$queryParams = array();
		$where = array();
		$join = "";

		if ( isset($data['CmpCallCard_prmDT_From']) )
		{
			$where[] = ' CmpCallCard_prmDT >= :CmpCallCard_prmDT_From ';
			$queryParams['CmpCallCard_prmDT_From'] = $data['CmpCallCard_prmDT_From'];
		}

		if ( isset($data['CmpCallCard_prmDT_To']) )
		{
			$where[] = ' CmpCallCard_prmDT <= :CmpCallCard_prmDT_To ';
			$queryParams['CmpCallCard_prmDT_To'] = $data['CmpCallCard_prmDT_To'] . ' 23:59:59.999';
		}

		if ( isset($data['CmpCallCard_IsPoli']) )
		{
			$where[] = ' ISNULL(CmpCallCard_IsPoli, 1) = :CmpCallCard_IsPoli ';
			$queryParams['CmpCallCard_IsPoli'] = $data['CmpCallCard_IsPoli'];
		}

		if ( isset($data['CmpLpu_id']) )
		{
			$where[] = ' CmpLpu.Lpu_id = :CmpLpu_id ';
			$queryParams['CmpLpu_id'] = $data['CmpLpu_id'];
		}

		if ( isset($data['Lpu_aid']) || isset($data['LpuRegion_id']) || isset($data['MedPersonal_id']) )
		{
			$attach_where = "";

			if ( isset($data['Lpu_aid']) &&  $data['Lpu_aid'] > 0)
			{
				$attach_where .= ' and pc.Lpu_id = :Lpu_aid ';
				$queryParams['Lpu_aid'] = $data['Lpu_aid'];
			}

			if ( isset($data['LpuRegion_id']) )
			{
				$attach_where .= ' and pc.LpuRegion_id = :LpuRegion_id ';
				$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
			}

			$msfreg_join = "";
			if ( isset($data['MedPersonal_id']) )
			{
				$msfreg_join = " inner join v_MedStaffRegion msr with (nolock) on msr.MedPersonal_id = :MedPersonal_id and msr.LpuRegion_id = pc.LpuRegion_id ";
				$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
			}

			$table = 'v_PersonCard';
			if ( isset($data['LpuAttachType_id']) && $data['LpuAttachType_id'] == 2 )
			{
				$table = 'v_PersonCard_all';
				$attach_where .= ' and pc.PersonCard_begDate < ccc.CmpCallCard_prmDT and (pc.PersonCard_endDate > ccc.CmpCallCard_prmDT or pc.PersonCard_endDate is null) ';
			}

			$where[] = '
				exists (
					select top 1 1
					from
						' . $table . ' pc  with (nolock)
						' . $msfreg_join . '
					where
						pc.Person_id = ps.Person_id
						' . $attach_where . '
				)
			';
		}
		$curMedpersonal_id = isset($data['session']['medpersonal_id']) ? $data['session']['medpersonal_id'] : 0;
		if ($curMedpersonal_id > 0) {
			$lastVizitSql = "
				outer apply (
					select
						top
						1
						EvnVizitPL_setDate,
						Diag_id
					from
						v_EvnVizitPL vpl1 (nolock)
					where
						vpl1.Person_id = ps.Person_id
						and MedPersonal_id = '{$curMedpersonal_id}'
						and EvnVizitPL_setDate <= CmpCallCard_prmDT
					order by
						EvnVizitPL_setDate desc
				) as vpl
			";
		} else {
			$lastVizitSql = "
				outer apply (
					select
						top
						1
						null as EvnVizitPL_setDate,
						null as Diag_id
				) as vpl
			";
		}

		$sql = "
			select
			-- select
				ccc.CmpCallCard_id,
				ps.Person_id,
				ps.Server_id,
				ps.PersonEvn_id,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar(10), ps.Person_BirthDay, 104) as Person_Birthday,
				convert(varchar,cast(CmpCallCard_prmDT as datetime),104) + ' ' +  SUBSTRING(convert(varchar,cast(CmpCallCard_prmDT as datetime),108), 1, 5) as CmpCallCard_prmDT,
				CmpReason_Name,
				isnull(Lpu.Lpu_Nick, replace(replace(CmpLpu.CmpLpu_Name, '=', ''), '_+', ' ')) as CmpLpu_Name,
				rtrim(udiag.Diag_Code) + ' ' + rtrim(udiag.Diag_Name) Diag_UName,
				rtrim(sdiag.Diag_Code) + ' ' + rtrim(sdiag.Diag_Name) Diag_SName,
				CASE WHEN ISNULL(CmpCallCard_IsPoli, 1) = 1 THEN 'false' else 'true' END as CmpCallCard_IsPoli,
				convert(varchar(10), vpl.EvnVizitPL_setDate, 104) as EvnVizitPL_setDate,
				rtrim(vdiag.Diag_Code) + ' ' + rtrim(vdiag.Diag_Name) as Diag_VName
			-- end select
			from
			-- from
				v_CmpCallCard ccc with (nolock)
				inner join v_PersonState ps with (nolock) on ps.Person_id = ccc.Person_id
				" . $join . "
				left join v_CmpReason cr (nolock) on cr.CmpReason_id = ccc.CmpReason_id
				left join v_CmpLpu CmpLpu (nolock) on CmpLpu.CmpLpu_id = ccc.CmpLpu_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CmpLpu.Lpu_id
				left join v_Diag udiag (nolock) on udiag.Diag_id = ccc.Diag_uid
				left join v_Diag sdiag (nolock) on sdiag.Diag_id = ccc.Diag_sid
				" .$lastVizitSql. "
				left join v_Diag vdiag (nolock) on vdiag.Diag_id = vpl.Diag_id
			-- end from
			" . ImplodeWherePH($where) . "
			ORDER BY
			-- order by
			ccc.Person_SurName,
			ccc.Person_FirName
			-- end order by
		";

		return $this->getPagingResponse($sql, $queryParams, $data['start'], $data['limit'], true);
	}

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
				CLC.EmergencyTeamNum,
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

				CLC.Fam,
				CLC.Name,
				CLC.Middle,
				CLC.Age,
				COALESCE(CLC.Person_Snils, PS.Person_Snils) as Person_Snils,
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

				CLC.CallType_id,
				CCT.CmpCallType_Name as CallType,
				CCT.CmpCallType_Code,

				CASE WHEN ISNULL(CLC.isAlco,1) = 2 THEN 'Да' ELSE 'Нет' END as isAlco,
				CLC.Complaints,
				CLC.Anamnez,
				CASE WHEN ISNULL(CLC.isMenen,1) = 2 THEN 'Да' ELSE 'Нет' END as isMenen,
				CASE WHEN ISNULL(CLC.isNist,1) = 2 THEN 'Да' ELSE 'Нет' END as isNist,
				CASE WHEN ISNULL(CLC.isAnis,1) = 2 THEN 'Да' ELSE 'Нет' END as isAnis,
				CASE WHEN ISNULL(CLC.isLight,1) = 2 THEN 'Да' ELSE 'Нет' END as isLight,
				CASE WHEN ISNULL(CLC.isAcro,1) = 2 THEN 'Да' ELSE 'Нет' END as isAcro,
				CASE WHEN ISNULL(CLC.isMramor,1) = 2 THEN 'Да' ELSE 'Нет' END as isMramor,
				CASE WHEN ISNULL(CLC.isHale,1) = 2 THEN 'Да' ELSE 'Нет' END as isHale,
				CASE WHEN ISNULL(CLC.isPerit,1) = 2 THEN 'Да' ELSE 'Нет' END as isPerit,

				CASE WHEN ISNULL(CLC.isSogl,1) = 2 THEN 'Да' ELSE 'Нет' END as isSogl,
				CASE WHEN ISNULL(CLC.isOtkazMed,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazMed,
				CASE WHEN ISNULL(CLC.isOtkazHosp,1) = 2 THEN 'Да' ELSE 'Нет' END as isOtkazHosp,

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
	 * Проверка наличия CmpCallCard у пациента
	 */
	public function checkPersonCmpCallCard($Person_id = null) {
		if ( empty($Person_id) ) {
			return null;
		}

		return $this->getFirstResultFromQuery("
			select top 1 CmpCallCard_id from v_CmpCallCard with (nolock) where Person_id = :Person_id
		", array(
			'Person_id' => $Person_id,
		), null);		
	}

	/**
	 * Проверка наличия CmpCloseCard у пациента
	 */
	public function checkPersonCmpCloseCard($Person_id = null) {
		if ( empty($Person_id) ) {
			return null;
		}

		return $this->getFirstResultFromQuery("
			select top 1 CmpCloseCard_id from {$this->schema}.v_CmpCloseCard with (nolock) where Person_id = :Person_id
		", array(
			'Person_id' => $Person_id,
		), null);		
	}

	/**
	 * для ЭМК
	 * @param type $data
	 * @return array
	 */
	function printCmpCloseCardEMK($data) {
		$query = "
			select top 1
				CLC.CmpCallCard_id
				,CLC.CmpCloseCard_id
				--,convert(varchar, CLC.CmpCloseCard_insDT, 104) as CardDate
				,convert(varchar, CC.CmpCallCard_insDT, 104) as CallCardDate
				,CLC.Day_num
				,CLC.Year_num
				,ClC.CmpCloseCard_DayNumPr
				,ClC.CmpCloseCard_YearNumPr
				--,CLC.Feldsher_id
				--,CLC.StationNum
				--,CLC.EmergencyTeamNum
				,convert(varchar, CLC.AcceptTime, 104)+' '+convert(varchar, CLC.AcceptTime, 108) as AcceptDateTime
				,SX.Sex_name
				,CLC.SummTime
				,CLC.Fam
				,CLC.Name
				,CLC.Middle
				--,CLC.Age
				,DIAG.Diag_FullName as Diag
				,rtrim(coalesce(UCA.PMUser_surName,'')) +' '+ rtrim(isnull(UCA.PMUser_firName,'')) +' '+ rtrim(isnull(UCA.PMUser_secName,'')) as FeldsherAcceptName
				--,UCA.PMUser_Name as FeldsherAcceptName
				--,UCT.PMUser_Name as FeldsherTransName
				,convert(varchar(10), ccp.CmpCallCardCostPrint_setDT, 104) as CmpCallCardCostPrint_setDT
				,ccp.CmpCallCardCostPrint_IsNoPrint
				,STR(ccp.CmpCallCardCostPrint_Cost, 19, 2) as CostPrint
				,COALESCE(msfC.Person_Fio, msfE.Person_Fio) as EmergencyTeam_HeadShift_Name
			from
				{$this->schema}.v_CmpCloseCard CLC with (nolock)
				LEFT JOIN v_CmpCallCard CC (nolock) on CC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_CmpCallCardCostPrint ccp (nolock) on ccp.CmpCallCard_id = cc.CmpCallCard_id
				left join v_EmergencyTeam ET with (nolock) on CC.EmergencyTeam_id = ET.EmergencyTeam_id
				LEFT JOIN v_MedStaffFact msfE with (nolock) on msfE.MedPersonal_id = ET.EmergencyTeam_HeadShift
				LEFT JOIN v_MedStaffFact msfC with (nolock) on msfC.MedStaffFact_id = CLC.MedStaffFact_id
				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CLC.Sex_id
				left join v_Diag DIAG (nolock) on DIAG.Diag_id = CLC.Diag_id
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CLC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CLC.FeldsherTrans
			where
				CLC.CmpCloseCard_id = :CmpCloseCard_id
		";


		$result = $this->db->query($query, array(
			'CmpCloseCard_id' => $data['CmpCloseCard_id']
		));


		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * для ЭМК карты вызова
	 * @param type $data
	 * @return array
	 */
	function printCmpCallCardEMK($data) {
		$query = "
			select top 1
				 CC.CmpCallCard_id
				,convert(varchar, CC.CmpCallCard_insDT, 104) as CallCardDate
				,CC.CmpCallCard_Numv as Day_num
				,CC.CmpCallCard_Ngod as Year_num
				,CC.CmpCallCard_NumvPr
				,CC.CmpCallCard_NgodPr
				,convert(varchar, CC.CmpCallCard_prmDT, 104)+' '+convert(varchar, CC.CmpCallCard_prmDT, 108) as AcceptDateTime
				,SX.Sex_name
				,CC.CmpCallCard_Dlit as SummTime

				--,case when CLC.Fam is not null then CLC.Fam else CC.Person_SurName end as Fam
				--,case when CLC.Name is not null then CLC.Name else CC.Person_FirName end as Name
				--,case when CLC.Middle is not null then CLC.Middle else CC.Person_SecName end as Middle

				,CC.Person_SurName as Fam
				,CC.Person_FirName as Name
				,CC.Person_SecName as Middle

				--,case when CLDIAG.Diag_FullName is not null then CLDIAG.Diag_FullName else DIAG.Diag_FullName end as Diag
				,DIAG.Diag_FullName as Diag
				,CR.CmpReason_Name
				,CMPD.CmpDiag_Name
				--,case when CLMP.Person_Fio is not null then CLMP.Person_Fio else MP.Person_Fio end as MedPersonal_Name
				--,MP.Person_Fio as MedPersonal_Name
				,MSF.Person_Fio as MedPersonal_Name
				,CRes.CmpResult_Name
				,RDT.ResultDeseaseType_Name
				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CC.CmpCallCard_Dom is not null then ', д.'+CC.CmpCallCard_Dom else '' end +
				case when CC.CmpCallCard_Kvar is not null then ', кв.'+CC.CmpCallCard_Kvar else '' end +
				case when CC.CmpCallCard_Comm is not null then '</br>'+CC.CmpCallCard_Comm else '' end as Adress_Name,

				case when ISNULL(CC.CmpCallCard_City, '') != '' then 'Нас. пункт ' + CAST(CC.CmpCallCard_City as varchar) else '' end +
				case when ISNULL(CC.CmpCallCard_Ulic, '') != '' then ', ул. ' + CAST(CC.CmpCallCard_Ulic as varchar) else '' end +
				case when ISNULL(CC.CmpCallCard_Dom, '') != '' then ', дом ' + CAST(CC.CmpCallCard_Dom as varchar) else '' end +
				case when ISNULL(CC.CmpCallCard_Kvar, '') != '' then ', кварт. ' + CAST(CC.CmpCallCard_Kvar as varchar) else '' end +
				case when ISNULL(CC.CmpCallCard_Room, '') != '' then ', комната ' + CAST(CC.CmpCallCard_Room as varchar) else '' end +
				case when ISNULL(CC.CmpCallCard_Podz, '') != '' then ', подъезд ' + CAST(CC.CmpCallCard_Podz as varchar) else '' end +
				case when ISNULL(CC.CmpCallCard_Etaj, '') != '' then ', этаж ' + CAST(CC.CmpCallCard_Etaj as varchar) else ''
				end as CmpCallPlace
			from
				v_CmpCallCard CC with (nolock)
				--LEFT JOIN CmpCloseCard CLC with (nolock) on CLC.CmpCallCard_id = CC.CmpCallCard_id
				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CC.Sex_id
				left join v_Diag DIAG (nolock) on DIAG.Diag_id = CC.Diag_uid
				--left join v_Diag CLDIAG (nolock) on CLDIAG.Diag_id = CLC.Diag_id
				left join CmpDiag CMPD (nolock) on CMPD.CmpDiag_id = CC.CmpDiag_oid
				--left join v_MedPersonal CLMP with (nolock) on CLMP.MedPersonal_id = CLC.MedPersonal_id
				left join v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = CC.MedStaffFact_id
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = CC.MedPersonal_id
				LEFT JOIN v_CmpReason CR with (nolock) on CR.CmpReason_id = CC.CmpReason_id
				left JOIN v_CmpResult CRes with (nolock) on CRes.CmpResult_id = CC.CmpResult_id
				left JOIN fed.v_ResultDeseaseType RDT with (nolock) on RDT.ResultDeseaseType_id = CC.ResultDeseaseType_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CC.KLRgn_id
                left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CC.KLStreet_id
			where
				CC.CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * печать шапки
	 * @param type $data
	 * @return array
	 */
	function printCmpCallCardHeader($data) {
		return false;
	}


	/**
	 * печать карты вызова как 110
	 * @param type $data
	 * @return array
	 */
	function printCmpCallCard($data) {
		$query = "
			select top 1
				 CC.CmpCallCard_id
				,convert(varchar, CC.CmpCallCard_insDT, 104) as CallCardDate
				,CC.CmpCallCard_Numv as Day_num
				,CC.CmpCallCard_Ngod as Year_num
				,convert(varchar, CC.CmpCallCard_prmDT, 104)+' '+convert(varchar, CC.CmpCallCard_prmDT, 108) as AcceptDateTime
				,convert(varchar, CC.CmpCallCard_prmDT, 104) as AcceptDate
				,convert(varchar, CC.CmpCallCard_prmDT, 108) as AcceptTime
				,case when convert(varchar, CC.CmpCallCard_Tper, 104)!='01.01.1900' then convert(varchar, CC.CmpCallCard_Tper, 120) else '' end as TransTime
				,case when convert(varchar, CC.CmpCallCard_Vyez, 104)!='01.01.1900' then convert(varchar, CC.CmpCallCard_Vyez, 120) else '' end as GoTime
				,case when convert(varchar, CC.CmpCallCard_Przd, 104)!='01.01.1900' then convert(varchar, CC.CmpCallCard_Przd, 120) else '' end as ArriveTime
				,case when convert(varchar, CC.CmpCallCard_Tsta, 104)!='01.01.1900' then convert(varchar, CC.CmpCallCard_Tsta, 120) else '' end as ToHospitalTime
				,case when convert(varchar, CC.CmpCallCard_Tisp, 104)!='01.01.1900' then convert(varchar, CC.CmpCallCard_Tisp, 120) else '' end as BackTime
				,'' as TransportTime
				,'' as EndTime
				,SX.Sex_name
				,CC.CmpCallCard_Dlit as SummTime
				,CC.Person_SurName as Fam
				,CC.Person_FirName as Name
				,CC.Person_SecName as Middle
				,CC.Person_Age as Age
				,DIAG.Diag_FullName as Diag
				,CR.CmpReason_Name
				,CC.CmpCallCard_Numb as EmergencyTeamNum
				,CC.CmpCallCard_Stan as StationNum

				,Lpu.Lpu_name
				,Lpu.UAddress_Address
				,Lpu.Lpu_Phone
				,CCT.CmpCallType_Name

				,ODIAG.Diag_FullName as oDiag
				,UCA.PMUser_Name as FeldsherAcceptName
				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CC.CmpCallCard_Dom is not null then ', д.'+CC.CmpCallCard_Dom else '' end +
				case when CC.CmpCallCard_Kvar is not null then ', кв.'+CC.CmpCallCard_Kvar else '' end +
				case when CC.CmpCallCard_Comm is not null then '</br>'+CC.CmpCallCard_Comm else '' end as Adress_Name
			from
				v_CmpCallCard CC with (nolock)
				LEFT JOIN Sex SX with (nolock) on SX.Sex_id = CC.Sex_id
				left join v_Diag DIAG (nolock) on DIAG.Diag_id = CC.Diag_uid
				left join v_Diag ODIAG (nolock) on DIAG.Diag_id = CC.Diag_gid
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CC.MedPersonal_id
				LEFT JOIN v_CmpReason CR with (nolock) on CR.CmpReason_id = CC.CmpReason_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CC.KLRgn_id
                left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CC.KLStreet_id
				left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = CC.Lpu_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CC.CmpCallType_id
			where
				CC.CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query($query, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Суточный рапорт
	 * @param type $data
	 * @return array
	 */
	function printReportCmp($data) {
		$res = array();

		// Отказаны
		$query = "
			select count(1) as reject
			from
				v_CmpCallCard CCC with (nolock)
			where
				CCC.CmpCallCardStatusType_id = 5
				and CCC.Lpu_id = :Lpu_id
				and CCC.pmUser_insID > 1
				and cast(CCC.CmpCallCard_prmDT as date) >= :Daydate1
				and cast(CCC.CmpCallCard_prmDT as date) <= :Daydate2
		";

		$result = $this->db->query($query, array(
			'Daydate1' => $data['daydate1'],
			'Daydate2' => $data['daydate2'],
			'Lpu_id' => $data['Lpu_id']
		));
		if ( is_object($result) ) {
			$preres = $result->result('array');
			$preres = $preres[0]['reject'];
			$res['reject'] = $preres;
		}

		// переданы в НМП
		$query = "
			select count(1) as transmit_nmp
			from
				v_CmpCallCard CCC with (nolock)
			where
				CCC.Lpu_ppdid IS NOT NULL
				and CCC.Lpu_id = :Lpu_id
				and CCC.pmUser_insID > 1
				and cast(CCC.CmpCallCard_prmDT as date) >= :Daydate1
				and cast(CCC.CmpCallCard_prmDT as date) <= :Daydate2
		";

		$result = $this->db->query($query, array(
			'Daydate1' => $data['daydate1'],
			'Daydate2' => $data['daydate2'],
			'Lpu_id' => $data['Lpu_id']
		));
		if ( is_object($result) ) {
			$preres = $result->result('array');
			$preres = $preres[0]['transmit_nmp'];
			$res['transmit_nmp'] = $preres;
		}

		// переданы в НМП
		$query = "
			select count(1) as allcall
			from
				v_CmpCallCard CCC with (nolock)
			where
				CCC.Lpu_id = :Lpu_id
				and CCC.CmpCallCardStatusType_id IS NOT NULL
				and CCC.pmUser_insID > 1
				and cast(CCC.CmpCallCard_prmDT as date) >= :Daydate1
				and cast(CCC.CmpCallCard_prmDT as date) <= :Daydate2
		";
		//var_dump($data['Lpu_id']);exit;
		$result = $this->db->query($query, array(
			'Daydate1' => $data['daydate1'],
			'Daydate2' => $data['daydate2'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			$preres = $result->result('array');
			$preres = $preres[0]['allcall'];
			$res['allcall'] = $preres;
		}


		if ( is_array($res) ) {
			return $res;
		} else {
			return false;
		}


	}

	/**
	 * default desc
	 */
	function reportDayDiag($data) {
		//Показатели по заболеваниям
		$query = "
			select
				--count(1) as cnt,
				--CLD.Diag_Name
				count(1) as cnt,
				--D.Diag_Name
				RTRIM(ISNULL(D.Diag_Code, '') +' '+ ISNULL(D.Diag_Name, '')) as CmpDiag_Name

			from
				{$this->schema}.v_CmpCloseCard CLC with (nolock)

			--inner join v_CmpCallCard CCC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
			--inner join v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
			--left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id
			left join v_Diag D with (nolock) on D.Diag_id = CLC.Diag_id

			where
				cast(CLC.CmpCloseCard_insDT as date) >= :Daydate1
				and cast(CLC.CmpCloseCard_insDT as date) <= :Daydate2
			GROUP BY
				CLC.Diag_id, D.Diag_Code, D.Diag_Name
		";

		$result = $this->db->query($query, array(
			'Daydate1' => $data['daydate1'],
			'Daydate2' => $data['daydate2'],
			'Lpu_id' => $data['Lpu_id']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function reportBrig($data) {
		//Показатели по заболеваниям
		/*
		$query = "
			select
				convert(varchar(10), ETH.EmergencyTeamStatusHistory_insDT, 104) as Date,
				convert(varchar(5), ETH.EmergencyTeamStatusHistory_insDT, 108) as Time,
				ET.EmergencyTeam_Num as Num,
				ES.EmergencyTeamStatus_Name as StatusName,
				ESP.EmergencyTeamSpec_Name as Spec
			from
				v_EmergencyTeamStatusHistory ETH with (nolock)

			left join v_EmergencyTeam ET on ET.EmergencyTeam_id = ETH.EmergencyTeam_id
			left join v_EmergencyTeamStatus ES on ES.EmergencyTeamStatus_id = ETH.EmergencyTeamStatus_id
			left join v_EmergencyTeamSpec ESP on ESP.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id

			where
				cast(ETH.EmergencyTeamStatusHistory_insDT as date) >= :Daydate1
				and cast(ETH.EmergencyTeamStatusHistory_insDT as date) <= :Daydate2
			order by
				ETH.EmergencyTeamStatusHistory_insDT
		";
		*/
		$query = "
			select
				ET.EmergencyTeam_Num as Num,
				ESP.EmergencyTeamSpec_Name as Spec
			from
				v_EmergencyTeamStatusHistory ETH with (nolock)

			left join v_EmergencyTeam ET (nolock) on ET.EmergencyTeam_id = ETH.EmergencyTeam_id
			left join v_EmergencyTeamSpec ESP (nolock) on ESP.EmergencyTeamSpec_id = ET.EmergencyTeamSpec_id

			where
				cast(ETH.EmergencyTeamStatusHistory_insDT as date) >= :Daydate1
				and cast(ETH.EmergencyTeamStatusHistory_insDT as date) <= :Daydate2
			Group by
				ET.EmergencyTeam_Num, ESP.EmergencyTeamSpec_Name
		";

		$result = $this->db->query($query, array(
			'Daydate1' => $data['daydate1'],
			'Daydate2' => $data['daydate2']
		));
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные талона вызова для карты закрытия вызова
	 * Используется для первичного наполнения карты закрытия вызова
	 *
	 * @params array $data
	 * @return array or false
	 */
	public function loadCmpCloseCardEditForm( $data ) {
		$sql = "
			SELECT top 1
				'' as accessType,
				CCC.CmpCallCard_id,
				CCC.CmpCallCard_Numv as Day_num,
				CCC.CmpCallCard_Ngod as Year_num,
				CCC.CmpCallCard_NumvPr as CmpCloseCard_DayNumPr,
				CCC.CmpCallCard_NgodPr as CmpCloseCard_YearNumPr,
				RTRIM(PMC.PMUser_Name) as FeldsherAcceptName,
				RTRIM(PMC.PMUser_Name) as Feldsher_id,
				--CCC.pmUser_updID as FeldsherAcceptName,
				CCC.CmpCallCard_IsAlco as isAlco,
				CCC.CmpCallType_id as CallType_id,
				CCC.CmpReason_id as CallPovod_id,
				CCC.Sex_id,
				isnull(CCC.KLSubRgn_id, UAD.KLSubRgn_id) as Area_id,
				isnull(CCC.KLCity_id, UAD.KLCity_id) as City_id,
				isnull(CCC.KLTown_id, UAD.KLTown_id) as Town_id,
				isnull(CCC.KLStreet_id, UAD.KLStreet_id) as Street_id,
				CCC.CmpCallCard_Dom as House,
				CCC.CmpCallCard_Korp as Korpus,
				CCC.CmpCallCard_Room as Room,
				CCC.CmpCallCard_Kvar as Office,
				CCC.CmpCallCard_Podz as Entrance,
				CCC.CmpCallCard_Etaj as Level,
				CCC.CmpCallCard_Kodp as CodeEntrance,
				CCC.CmpCallCard_Telf as Phone,
				CCC.CmpCallPlaceType_id,
				CCC.CmpCallCard_Ulic as CmpCloseCard_Street,
				CCC.CmpCallCard_UlicSecond as CmpCloseCard_UlicSecond,
				CCC.CmpCallCard_Comm as CmpCloseCard_DopInfo,
				CCC.LpuBuilding_id,
				CCC.Lpu_hid,
				CCC.CmpCallCard_IsNMP,
				CCC.CmpCallCard_IsExtra as CmpCloseCard_IsExtra,
				case when PS.Document_Ser is not null then PS.Document_Ser end + ' ' +
				case when PS.Document_Num is not null then PS.Document_Num end
				as DocumentNum,
				PS.Person_Snils,
				convert(varchar,cast(PS.Person_deadDT as datetime),120) as Person_deadDT,
				COALESCE( CLC.Person_PolisSer, CCC.Person_PolisSer, PS.Polis_Ser, null) as Person_PolisSer,
				COALESCE( CLC.Person_PolisNum, CCC.Person_PolisNum, PS.Polis_Num, null) as Person_PolisNum,
				COALESCE( CLC.CmpCloseCard_PolisEdNum, CCC.CmpCallCard_PolisEdNum, PS.Person_EdNum, null) as CmpCloseCard_PolisEdNum,
				CCC.Person_IsUnknown,
				org1.Org_Name as Work,
				dbfss.SocStatus_SysNick as SocStatusNick,
				dbfss.SocStatus_id as SocStatus_id,

				case
					when CCC.Person_Age > 0 then 219
					when DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 365 then 219
					when DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 31 then 220
					when DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 0 then 221
					else 219
				end as AgeType_id2,

				case
					when CCC.Person_Age > 0 then CCC.Person_Age
					when DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 365 then DATEDIFF(year, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT)
					when DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 31 then DATEDIFF(month, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT)
					when DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT) > 0 then DATEDIFF(DAY, isnull(CCC.Person_BirthDay, PS.Person_BirthDay), CCC.CmpCallCard_insDT)
					else null
				end as Age,

				CCC.CmpReasonNew_id as CallPovodNew_id,

				--cast(CCC.CmpCallCard_prmDT as varchar) as AcceptTime,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104)+' '+convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as AcceptTime,
				convert(varchar(10), CCCStatusData.TransTime, 104)+' '+convert(varchar(5),CCCStatusData.TransTime,108) as TransTime,
				convert(varchar(10), CCC.CmpCallCard_Vyez, 104)+' '+convert(varchar(5),CCC.CmpCallCard_Vyez,108) as GoTime,
				convert(varchar(10), CCC.CmpCallCard_Przd, 104)+' '+convert(varchar(5),CCC.CmpCallCard_Przd,108) as ArriveTime,
				convert(varchar(10), CCC.CmpCallCard_Tgsp, 104)+' '+convert(varchar(5),CCC.CmpCallCard_Tgsp,108) as TransportTime,
				convert(varchar(10), CCC.CmpCallCard_HospitalizedTime, 104)+' '+convert(varchar(5),CCC.CmpCallCard_HospitalizedTime,108) as ToHospitalTime,
				convert(varchar(10), CCC.CmpCallCard_Tisp, 104)+' '+convert(varchar(5),CCC.CmpCallCard_Tisp,108) as EndTime,
				convert(varchar(10), CCC.CmpCallCard_Tisp, 104)+' '+convert(varchar(5),CCC.CmpCallCard_Tisp,108) as CmpCloseCard_PassTime,

				ISNULL(PS.Person_Surname, case when RTRIM(CCC.Person_SurName) = 'null' then '' else CCC.Person_SurName end) as Fam,
				ISNULL(PS.Person_Firname, case when RTRIM(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end) as Name,
				ISNULL(PS.Person_Secname, case when RTRIM(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end) as Middle,
				CCC.Person_id,

				CCC.CmpCallCard_Ktov as Ktov,
				CCC.CmpCallerType_id,
				CCC.Lpu_id,
				CCC.Lpu_ppdid,
				ISNULL(L.Lpu_Nick,'') as CmpLpu_Name,
				CCC.KLRgn_id,
				CCC.KLSubRgn_id,
				CCC.KLCity_id,
				CCC.KLTown_id,
				CCC.KLStreet_id,

				case when isnull(CCC.KLStreet_id,0) = 0 then
					case when isnull(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL
					else 'UA.'+CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'+CAST(CCC.KLStreet_id as varchar(20)) end as StreetAndUnformalizedAddressDirectory_id,

				EMT.EmergencyTeam_Num as EmergencyTeamNum,
				EMT.EmergencyTeamSpec_id,
				EMT.EmergencyTeam_HeadShift as MedPersonal_id,
				EMT.EmergencyTeam_Assistant1 as MedPersonalAssistant_id,
				EMT.EmergencyTeam_HeadShift2 as EmergencyTeam_HeadShift2_id,
				PostKind.code as EmergencyTeam_HeadShift_Code,
				EMT.EmergencyTeam_Driver as MedPersonalDriver_id,
				COALESCE(EMT.EmergencyTeam_HeadShiftWorkPlace, CLC.MedStaffFact_id, msf.MedStaffFact_id, null) as MedStaffFact_id,
				L.Lpu_Nick as StationNum,
				--CCC.pmUser_insID as FeldsherAccept,
				PMCins.MedPersonal_id as FeldsherAccept,
				PMCinsTrans.MedPersonal_id as FeldsherTrans,
				CLC.CmpCloseCard_id,
				CCC.EmergencyTeam_id,

				LB.LpuBuilding_IsWithoutBalance,
				CCC.CmpCallCard_isControlCall,
			    MPh1.Person_Fio as EmergencyTeam_HeadShiftFIO,
				MPh2.Person_Fio as EmergencyTeam_HeadShift2FIO,
				MPd1.Person_Fio  as EmergencyTeam_DriverFIO,
				MPa1.Person_Fio as EmergencyTeam_Assistant1FIO,
				case when hospEvent.EmergencyTeamStatus_Code = 53 then '225' else
					case when hospEvent.EmergencyTeamStatus_Code = 3 then '226' else '' end
				end as ComboCheck_ResultUfa_id,
				case when hospEvent.EmergencyTeamStatus_Code = 53 then CCC.Diag_gid else '' end as ComboValue_854,
				case when hospEvent.EmergencyTeamStatus_Code = 3 then CCC.Diag_gid else '' end as ComboValue_243


				--,CONVERT( varchar, CCC.CmpCallCard_TEnd, 104 ) + ' ' + SUBSTRING( CONVERT( varchar, CCC.CmpCallCard_TEnd, 108 ), 0, 6 ) as EndTime
			FROM
				v_CmpCallCard CCC with (nolock)
				LEFT JOIN {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				LEFT JOIN v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Job as job1 with (nolock) ON PS.Job_id=job1.Job_id
				LEFT JOIN v_Org as org1 with (nolock) ON job1.Org_id=org1.Org_id
				LEFT JOIN SocStatus as dbfss with (nolock) on dbfss.SocStatus_id = PS.SocStatus_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CCC.Lpu_id
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
				LEFT join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				LEFT join v_pmUserCache PMCins with (nolock) on PMCins.PMUser_id = CCC.pmUser_insID
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
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
				outer apply (
					select top 1
						ETS.EmergencyTeamStatus_Code
					from v_EmergencyTeamStatusHistory ETSH (nolock)
					left join v_EmergencyTeamStatus ETS (nolock) on ETS.EmergencyTeamStatus_id = ETSH.EmergencyTeamStatus_id
					where ETSH.CmpCallCard_id = CCC.CmpCallCard_id and ETSH.EmergencyTeam_id = CCC.EmergencyTeam_id and ETS.EmergencyTeamStatus_Code in (3,53)
					order by EmergencyTeamStatusHistory_id desc
				) as hospEvent
				LEFT join v_pmUserCache PMCinsTrans with (nolock) on PMCinsTrans.PMUser_id = CCCStatusData.FeldsherTransPmUser_id
				LEFT JOIN v_MedStaffFact msf on (msf.MedStaffFact_id = EMT.EmergencyTeam_HeadShiftWorkPlace)
				LEFT JOIN v_MedPersonal MPh1 with(nolock) ON( MPh1.MedPersonal_id=EMT.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal MPh2 with(nolock) ON( MPh2.MedPersonal_id=EMT.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal MPd1 with(nolock) ON( MPd1.MedPersonal_id=EMT.EmergencyTeam_Driver )
				LEFT JOIN v_MedPersonal MPa1 with(nolock) ON( MPa1.MedPersonal_id=EMT.EmergencyTeam_Assistant1 )
				left join v_UnformalizedAddressDirectory UAD with(nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_MedPersonal mp with (nolock) ON( mp.MedPersonal_id=EMT.EmergencyTeam_HeadShift)
				left join persis.v_Post Post with(nolock) on Post.id = MP.Dolgnost_id
				left join persis.v_PostKind PostKind with(nolock) on PostKind.id = Post.PostKind_id
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
		";
		$params = array('CmpCallCard_id' => $data['CmpCallCard_id']);
		//echo getDebugSQL($sql, $params);exit;
		$query = $this->db->query($sql, $params);

		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * Возвращает данные карты закрытия вызова
	 *
	 * @params array $data
	 * @return array or false
	 */
	public function loadCmpCloseCardViewForm( $data ){
		if ( ( empty( $data[ 'CmpCallCard_id' ] ) || !$data[ 'CmpCallCard_id' ] )
			&& ( empty( $data[ 'CmpCloseCard_id' ] ) || !$data[ 'CmpCloseCard_id' ] )
		) {
			return array( array( 'Error_Msg' => 'Невозможно открыть карту закрытия вызова, т.к. не передан ни один идентификатор' ) );
		}

		$where = array();
		$params = array();
		
		//поля Bad_DT и Mensis_DT есть только на Уфе
		$Bad_end_Mensis = '';
		if(getRegionNick() == 'ufa'){
			$Bad_end_Mensis = "
				convert(varchar(10), CClC.Bad_DT, 104)+' '+convert(varchar(5), cast(CClC.Bad_DT as datetime), 108) as Bad_DT,
				convert(varchar(10), CClC.Mensis_DT, 104)+' '+convert(varchar(5), cast(CClC.Mensis_DT as datetime), 108) as Mensis_DT,
			";
		}
		if ( !empty( $data[ 'CmpCallCard_id' ] ) ) {
			$where[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params[ 'CmpCallCard_id' ] = $data[ 'CmpCallCard_id' ];
		} elseif ( !empty( $data[ 'CmpCloseCard_id' ] ) ) {
			$where[] = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$params[ 'CmpCloseCard_id' ] = $data[ 'CmpCloseCard_id' ];
		}

		$sql = "
			SELECT TOP 1
				CClC.CmpCallCard_id,
				--CClC.CmpCallCard_IsAlco as isAlco,
				CClC.CmpCloseCard_id,
				CClC.LpuSection_id,
				CCC.CmpReason_id,
				CClC.PayType_id,
				CClC.Year_num,
				CClC.Day_num,
				CClC.CmpCloseCard_DayNumPr,
				CClC.CmpCloseCard_YearNumPr,
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
				CCC.CmpCallCard_Comm,
				isnull(PS.Person_SurName,CClC.Fam) as Fam,
				isnull(PS.Person_FirName,CClC.Name) as Name,
				isnull(PS.Person_SecName,CClC.Middle) as Middle,
				CClC.Age,
				CCC.Person_id,
				case when PS.Person_id is not null and isnull(PS.Person_IsUnknown,1) != 2 then 1 else CCC.Person_IsUnknown end as Person_IsUnknown,
				CClC.Ktov,
				CClC.CmpCallerType_id,
				CClC.SocStatus_id,
				COALESCE(CClC.MedStaffFact_id, msf.MedStaffFact_id, null) as MedStaffFact_id,
				CClC.MedStaffFact_cid,
				CClC.MedPersonal_id,
				CCC.Lpu_hid,
				CCC.KLRgn_id,
				CCC.KLSubRgn_id,
				CCC.KLCity_id ,
				CCC.KLTown_id,
				CClC.CmpCallPlaceType_id,
				CClC.Street_id as Street_id,
				CClC.CmpCloseCard_Street as CmpCloseCard_Street,
				CClC.CmpCloseCard_UlicSecond,
				CClC.Room,
				CClC.Korpus as Korpus,

				case when isnull(CClC.Street_id,0) = 0 then
					case when isnull(CClC.UnformalizedAddressDirectory_id,0) = 0 then CClC.CmpCloseCard_Street
					else 'UA.'+CAST(CClC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'+CAST(CClC.Street_id as varchar(20)) end as StreetAndUnformalizedAddressDirectory_id,

				COALESCE(CClC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as EmergencyTeamNum,
				COALESCE(CClC.EmergencyTeam_id, EMT.EmergencyTeam_id, null) as EmergencyTeam_id,
				COALESCE(CClC.EmergencyTeamSpec_id, EMT.EmergencyTeamSpec_id, null) as EmergencyTeamSpec_id,

				CClC.StationNum as StationNum,
				CClC.LpuBuilding_id,
				LB.LpuBuilding_IsPrint,
				CClC.pmUser_insID as Feldsher_id,
				--CClC.pmUser_insID as FeldsherAccept,
				CCLC.FeldsherAccept,
				CClC.FeldsherTrans,
				CClC.CmpCloseCard_IsNMP,
				CClC.CmpCloseCard_IsExtra,
				CClC.CmpCloseCard_IsProfile,
				CClC.CmpCloseCard_IsSignList,
				CClC.CallPovodNew_id,
				CClC.CmpResult_id,

				COALESCE( CClC.Person_PolisSer, PS.Polis_Ser, null) as Person_PolisSer,
				COALESCE( CClC.Person_PolisNum, PS.Polis_Num, null) as Person_PolisNum,
				COALESCE( CClC.CmpCloseCard_PolisEdNum, PS.Person_EdNum, null) as CmpCloseCard_PolisEdNum,
				convert(varchar,cast(PS.Person_deadDT as datetime),120) as Person_deadDT,
				COALESCE(CClC.Person_Snils, PS.Person_SNILS) as Person_Snils,

				convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), cast(CClC.AcceptTime as datetime), 108) as AcceptTime,
				convert(varchar(10), CClC.TransTime, 104)+' '+convert(varchar(5), cast(CClC.TransTime as datetime), 108) as TransTime,
				convert(varchar(10), CClC.GoTime, 104)+' '+convert(varchar(5), cast(CClC.GoTime as datetime), 108) as GoTime,

				convert(varchar(10), CClC.ArriveTime, 104)+' '+convert(varchar(5), cast(CClC.ArriveTime as datetime), 108) as ArriveTime,
				convert(varchar(10), CClC.TransportTime, 104)+' '+convert(varchar(5), cast(CClC.TransportTime as datetime), 108) as TransportTime,
				convert(varchar(10), CClC.CmpCloseCard_TranspEndDT, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_TranspEndDT as datetime), 108) as CmpCloseCard_TranspEndDT,
				convert(varchar(10), CClC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(CClC.ToHospitalTime as datetime), 108) as ToHospitalTime,
				convert(varchar(10), CClC.EndTime, 104)+' '+convert(varchar(5), cast(CClC.EndTime as datetime), 108) as EndTime,
				convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), cast(CClC.BackTime as datetime), 108) as BackTime,
				convert(varchar(10), CClC.CmpCloseCard_PassTime, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_PassTime as datetime), 108) as CmpCloseCard_PassTime,

				".$Bad_end_Mensis."

				CClC.SummTime,
				CClC.Work,
				CClC.DocumentNum,
				CClC.CallType_id,
				CClC.CallPovod_id,
				CClC.Alerg,
				CClC.Epid,
				CClC.isVac,
				CClC.Zev,
				CClC.Perk,
				CClC.isKupir,
				CASE WHEN ISNULL(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as isAlco,
				CClC.Complaints,
				CClC.Anamnez,
				CASE WHEN ISNULL(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as isMenen,
				CASE WHEN ISNULL(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as isAnis,
				CASE WHEN ISNULL(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as isNist,
				CASE WHEN ISNULL(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as isLight,
				CASE WHEN ISNULL(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as isAcro,
				CASE WHEN ISNULL(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as isMramor,
				CASE WHEN ISNULL(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as isHale,
				CASE WHEN ISNULL(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as isPerit,
				CClC.Urine,
				CClC.Shit,
				CClC.OtherSympt,
				CClC.CmpCloseCard_AddInfo,
				CClC.WorkAD,
				CClC.AD,
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as Pulse,
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as Chss,
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as Chd,
				CClC.Temperature,
				CClC.Pulsks,
				CClC.Gluck,
				CClC.LocalStatus,
				convert(varchar(5), cast(CClC.Ekg1Time as datetime), 108) as Ekg1Time,
				CClC.Ekg1,
				convert(varchar(5), cast(CClC.Ekg2Time as datetime), 108) as Ekg2Time,
				CClC.Ekg2,
				CClC.Diag_id,
				CClC.Diag_uid,
				CClC.Diag_sid,
				CClC.EfAD,
				CClC.CmpCloseCard_Epid,
				CClC.CmpCloseCard_Glaz,
				CClC.CmpCloseCard_GlazAfter,
				CClC.CmpCloseCard_m1,
				CClC.CmpCloseCard_e1,
				CClC.CmpCloseCard_v1,
				CClC.CmpCloseCard_m2,
				CClC.CmpCloseCard_e2,
				CClC.CmpCloseCard_v2,
				CClC.CmpCloseCard_Topic,
				CCC.CmpCallCard_IsNMP,
				CASE WHEN ISNULL(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as EfChss,
				CASE WHEN ISNULL(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as EfPulse,
				CClC.EfTemperature,
				CASE WHEN ISNULL(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as EfChd,
				CClC.EfPulsks,
				CClC.EfGluck,
				CClC.Kilo,
				CClC.CmpCloseCard_UserKilo,
				CClC.CmpCloseCard_UserKiloCommon,
				CClC.Lpu_id,
				CClC.HelpPlace,
				CClC.HelpAuto,
				CClC.CmpCloseCard_ClinicalEff,
				CClC.CmpCloseCard_DopInfo,
				CClC.DescText,
				CClC.isSogl,
				CClC.isOtkazMed,
				CClC.isOtkazHosp,
				CClC.isOtkazSign,
				CClC.OtkazSignWhy,
				CClC.CmpCloseCard_IsHeartNoise,
				CClC.CmpCloseCard_IsIntestinal,
				convert(varchar(5), cast(CClC.CmpCloseCard_BegTreatDT as datetime), 108) as CmpCloseCard_BegTreatDT,
				convert(varchar(5), cast(CClC.CmpCloseCard_EndTreatDT as datetime), 108) as CmpCloseCard_EndTreatDT,
				convert(varchar(5), cast(CClC.CmpCloseCard_HelpDT as datetime), 108) as CmpCloseCard_HelpDT,
				CClC.CmpCloseCard_Sat,
				CClC.CmpCloseCard_Rhythm,
				CClC.CmpCloseCard_AfterRhythm,
				CClC.CmpCloseCard_AfterSat,
				CClC.CmpCloseCard_IsDefecation,
				CClC.CmpCloseCard_IsDiuresis,
				CClC.CmpCloseCard_IsVomit,
				CClC.CmpCloseCard_IsTrauma,
				CClC.CmpLethalType_id,
				CClC.CmpCloseCard_MenenAddiction,
				CClC.LeaveType_id,
				MPh1.Person_Fio as EmergencyTeam_HeadShiftFIO,
				MPh2.Person_Fio as EmergencyTeam_HeadShift2FIO,
				MPd1.Person_Fio  as EmergencyTeam_DriverFIO,
				MPa1.Person_Fio as EmergencyTeam_Assistant1FIO,
				convert(varchar(10), CClC.CmpCloseCard_LethalDT, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_LethalDT as datetime), 108) as CmpCloseCard_LethalDT,
				UCA.PMUser_Name as pmUser_insName,
				UCT.PMUser_Name as FeldsherTransName,
				LB.LpuBuilding_IsWithoutBalance,
				ISNULL(CCC.CmpCallCard_IsPaid, 1) as CmpCallCard_IsPaid,
				ISNULL(CCC.CmpCallCard_IndexRep, 0) as CmpCallCard_IndexRep,
				ISNULL(CCC.CmpCallCard_IndexRepInReg, 1) as CmpCallCard_IndexRepInReg,
				ISNULL(CCC.CmpCallCard_isControlCall, 1) as CmpCallCard_isControlCall,
				CASE WHEN CCC.CmpCallCard_IndexRep >= isnull(CCC.CmpCallCard_IndexRepInReg, 0) THEN 'true' ELSE 'false' END as CmpCallCard_RepFlag,
				CASE WHEN DATEDIFF(ss,CCC.CmpCallCard_insDT,CClC.CmpCloseCard_insDT) < 10 THEN 'true' ELSE 'false' END as addedFromStreamMode
			FROM
				{$this->schema}.v_CmpCloseCard CClC with (nolock)
				LEFT JOIN v_CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				LEFT JOIN v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CClC.FeldsherTrans
				LEFT JOIN v_MedStaffFact msf on (msf.MedStaffFact_id= EMT.EmergencyTeam_HeadShiftWorkPlace)
				LEFT JOIN v_MedPersonal MPh1 with(nolock) ON( MPh1.MedPersonal_id=EMT.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal MPh2 with(nolock) ON( MPh2.MedPersonal_id=EMT.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal MPd1 with(nolock) ON( MPd1.MedPersonal_id=EMT.EmergencyTeam_Driver )
				LEFT JOIN v_MedPersonal MPa1 with(nolock) ON( MPa1.MedPersonal_id=EMT.EmergencyTeam_Assistant1 )
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
			".ImplodeWherePH( $where )."
		";

		//var_dump(getDebugSql($sql, $params ));exit;
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * Возвращает данные карты закрытия вызова
	 *
	 * @params array $data
	 * @return array or false
	 */
	public function loadCmpCloseCardViewFormForDelDocs($data){
		if ( ( empty( $data[ 'CmpCallCard_id' ] ) || !$data[ 'CmpCallCard_id' ] )
			&& ( empty( $data[ 'CmpCloseCard_id' ] ) || !$data[ 'CmpCloseCard_id' ] )
		) {
			return array( array( 'Error_Msg' => 'Невозможно открыть карту закрытия вызова, т.к. не передан ни один идентификатор' ) );
		}

		$where = array();
		$params = array();

		//поля Bad_DT и Mensis_DT есть только на Уфе
		$Bad_end_Mensis = '';
		if(getRegionNick() == 'ufa'){
			$Bad_end_Mensis = "
				convert(varchar(10), CClC.Bad_DT, 104)+' '+convert(varchar(5), cast(CClC.Bad_DT as datetime), 108) as Bad_DT,
				convert(varchar(10), CClC.Mensis_DT, 104)+' '+convert(varchar(5), cast(CClC.Mensis_DT as datetime), 108) as Mensis_DT,
			";
		}
		if ( !empty( $data[ 'CmpCallCard_id' ] ) ) {
			$where[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params[ 'CmpCallCard_id' ] = $data[ 'CmpCallCard_id' ];
		} elseif ( !empty( $data[ 'CmpCloseCard_id' ] ) ) {
			$where[] = "CClC.CmpCloseCard_id = :CmpCloseCard_id";
			$params[ 'CmpCloseCard_id' ] = $data[ 'CmpCloseCard_id' ];
		}

		$sql = "
			SELECT TOP 1
				CClC.CmpCallCard_id,
				CClC.CmpCloseCard_id,
				CClC.LpuSection_id,
				CCC.CmpReason_id,
				CClC.PayType_id,
				CClC.Year_num,
				CClC.Day_num,
				CClC.CmpCloseCard_DayNumPr,
				CClC.CmpCloseCard_YearNumPr,
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
				CCC.CmpCallCard_Comm,
				isnull(PS.Person_SurName,CClC.Fam) as Fam,
				isnull(PS.Person_FirName,CClC.Name) as Name,
				isnull(PS.Person_SecName,CClC.Middle) as Middle,
				CClC.Age,
				CCC.Person_id,
				case when PS.Person_id is not null and isnull(PS.Person_IsUnknown,1) != 2 then 1 else CCC.Person_IsUnknown end as Person_IsUnknown,
				CClC.Ktov,
				CClC.CmpCallerType_id,
				CClC.SocStatus_id,
				COALESCE(CClC.MedStaffFact_id, msf.MedStaffFact_id, null) as MedStaffFact_id,
				CClC.MedStaffFact_cid,
				CClC.MedPersonal_id,
				CCC.Lpu_hid,
				CCC.KLRgn_id,
				CCC.KLSubRgn_id,
				CCC.KLCity_id ,
				CCC.KLTown_id,
				CClC.CmpCallPlaceType_id,
				CClC.Street_id as Street_id,
				CClC.CmpCloseCard_Street as CmpCloseCard_Street,
				CClC.CmpCloseCard_UlicSecond,
				CClC.Room,
				CClC.Korpus as Korpus,

				case when isnull(CClC.Street_id,0) = 0 then
					case when isnull(CClC.UnformalizedAddressDirectory_id,0) = 0 then CClC.CmpCloseCard_Street
					else 'UA.'+CAST(CClC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'+CAST(CClC.Street_id as varchar(20)) end as StreetAndUnformalizedAddressDirectory_id,

				COALESCE(CClC.EmergencyTeamNum, EMT.EmergencyTeam_Num, null) as EmergencyTeamNum,
				COALESCE(CClC.EmergencyTeam_id, EMT.EmergencyTeam_id, null) as EmergencyTeam_id,
				COALESCE(CClC.EmergencyTeamSpec_id, EMT.EmergencyTeamSpec_id, null) as EmergencyTeamSpec_id,

				CClC.StationNum as StationNum,
				CClC.LpuBuilding_id,
				LB.LpuBuilding_IsPrint,
				CClC.pmUser_insID as Feldsher_id,
				--CClC.pmUser_insID as FeldsherAccept,
				CCLC.FeldsherAccept,
				CClC.FeldsherTrans,
				CClC.CmpCloseCard_IsNMP,
				CClC.CmpCloseCard_IsExtra,
				CClC.CmpCloseCard_IsProfile,
				CClC.CmpCloseCard_IsSignList,
				CClC.CallPovodNew_id,
				CClC.CmpResult_id,

				COALESCE( CClC.Person_PolisSer, PS.Polis_Ser, null) as Person_PolisSer,
				COALESCE( CClC.Person_PolisNum, PS.Polis_Num, null) as Person_PolisNum,
				COALESCE( CClC.CmpCloseCard_PolisEdNum, PS.Person_EdNum, null) as CmpCloseCard_PolisEdNum,
				convert(varchar,cast(PS.Person_deadDT as datetime),120) as Person_deadDT,
				COALESCE(CClC.Person_Snils, PS.Person_SNILS) as Person_Snils,

				convert(varchar(10), CClC.AcceptTime, 104)+' '+convert(varchar(5), cast(CClC.AcceptTime as datetime), 108) as AcceptTime,
				convert(varchar(10), CClC.TransTime, 104)+' '+convert(varchar(5), cast(CClC.TransTime as datetime), 108) as TransTime,
				convert(varchar(10), CClC.GoTime, 104)+' '+convert(varchar(5), cast(CClC.GoTime as datetime), 108) as GoTime,

				convert(varchar(10), CClC.ArriveTime, 104)+' '+convert(varchar(5), cast(CClC.ArriveTime as datetime), 108) as ArriveTime,
				convert(varchar(10), CClC.TransportTime, 104)+' '+convert(varchar(5), cast(CClC.TransportTime as datetime), 108) as TransportTime,
				convert(varchar(10), CClC.CmpCloseCard_TranspEndDT, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_TranspEndDT as datetime), 108) as CmpCloseCard_TranspEndDT,
				convert(varchar(10), CClC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(CClC.ToHospitalTime as datetime), 108) as ToHospitalTime,
				convert(varchar(10), CClC.EndTime, 104)+' '+convert(varchar(5), cast(CClC.EndTime as datetime), 108) as EndTime,
				convert(varchar(10), CClC.BackTime, 104)+' '+convert(varchar(5), cast(CClC.BackTime as datetime), 108) as BackTime,
				convert(varchar(10), CClC.CmpCloseCard_PassTime, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_PassTime as datetime), 108) as CmpCloseCard_PassTime,

				".$Bad_end_Mensis."

				CClC.SummTime,
				CClC.Work,
				CClC.DocumentNum,
				CClC.CallType_id,
				CClC.CallPovod_id,
				CClC.Alerg,
				CClC.Epid,
				CClC.isVac,
				CClC.Zev,
				CClC.Perk,
				CClC.isKupir,
				CASE WHEN ISNULL(CClC.isAlco,0) = 0 THEN NULL ELSE CClC.isAlco END as isAlco,
				CClC.Complaints,
				CClC.Anamnez,
				CASE WHEN ISNULL(CClC.isMenen,0) = 0 THEN NULL ELSE CClC.isMenen END as isMenen,
				CASE WHEN ISNULL(CClC.isAnis,0) = 0 THEN NULL ELSE CClC.isAnis END as isAnis,
				CASE WHEN ISNULL(CClC.isNist,0) = 0 THEN NULL ELSE CClC.isNist END as isNist,
				CASE WHEN ISNULL(CClC.isLight,0) = 0 THEN NULL ELSE CClC.isLight END as isLight,
				CASE WHEN ISNULL(CClC.isAcro,0) = 0 THEN NULL ELSE CClC.isAcro END as isAcro,
				CASE WHEN ISNULL(CClC.isMramor,0) = 0 THEN NULL ELSE CClC.isMramor END as isMramor,
				CASE WHEN ISNULL(CClC.isHale,0) = 0 THEN NULL ELSE CClC.isHale END as isHale,
				CASE WHEN ISNULL(CClC.isPerit,0) = 0 THEN NULL ELSE CClC.isPerit END as isPerit,
				CClC.Urine,
				CClC.Shit,
				CClC.OtherSympt,
				CClC.CmpCloseCard_AddInfo,
				CClC.WorkAD,
				CClC.AD,
				CASE WHEN COALESCE(CClC.Pulse,0)=0 THEN NULL ELSE CClC.Pulse END as Pulse,
				CASE WHEN COALESCE(CClC.Chss,0)=0 THEN NULL ELSE CClC.Chss END as Chss,
				CASE WHEN COALESCE(CClC.Chd,0)=0 THEN NULL ELSE CClC.Chd END as Chd,
				CClC.Temperature,
				CClC.Pulsks,
				CClC.Gluck,
				CClC.LocalStatus,
				convert(varchar(5), cast(CClC.Ekg1Time as datetime), 108) as Ekg1Time,
				CClC.Ekg1,
				convert(varchar(5), cast(CClC.Ekg2Time as datetime), 108) as Ekg2Time,
				CClC.Ekg2,
				CClC.Diag_id,
				CClC.Diag_uid,
				CClC.Diag_sid,
				CClC.EfAD,
				CClC.CmpCloseCard_Epid,
				CClC.CmpCloseCard_Glaz,
				CClC.CmpCloseCard_GlazAfter,
				CClC.CmpCloseCard_m1,
				CClC.CmpCloseCard_e1,
				CClC.CmpCloseCard_v1,
				CClC.CmpCloseCard_m2,
				CClC.CmpCloseCard_e2,
				CClC.CmpCloseCard_v2,
				CClC.CmpCloseCard_Topic,
				CCC.CmpCallCard_IsNMP,
				CASE WHEN ISNULL(CClC.EfChss,0) = 0 THEN NULL ELSE CClC.EfChss END as EfChss,
				CASE WHEN ISNULL(CClC.EfPulse,0) = 0 THEN NULL ELSE CClC.EfPulse END as EfPulse,
				CClC.EfTemperature,
				CASE WHEN ISNULL(CClC.EfChd,0) = 0 THEN NULL ELSE CClC.EfChd END as EfChd,
				CClC.EfPulsks,
				CClC.EfGluck,
				CClC.Kilo,
				CClC.CmpCloseCard_UserKilo,
				CClC.CmpCloseCard_UserKiloCommon,
				CClC.Lpu_id,
				CClC.HelpPlace,
				CClC.HelpAuto,
				CClC.CmpCloseCard_ClinicalEff,
				CClC.CmpCloseCard_DopInfo,
				CClC.DescText,
				CClC.isSogl,
				CClC.isOtkazMed,
				CClC.isOtkazHosp,
				CClC.isOtkazSign,
				CClC.OtkazSignWhy,
				CClC.CmpCloseCard_IsHeartNoise,
				CClC.CmpCloseCard_IsIntestinal,
				convert(varchar(5), cast(CClC.CmpCloseCard_BegTreatDT as datetime), 108) as CmpCloseCard_BegTreatDT,
				convert(varchar(5), cast(CClC.CmpCloseCard_EndTreatDT as datetime), 108) as CmpCloseCard_EndTreatDT,
				convert(varchar(5), cast(CClC.CmpCloseCard_HelpDT as datetime), 108) as CmpCloseCard_HelpDT,
				CClC.CmpCloseCard_Sat,
				CClC.CmpCloseCard_Rhythm,
				CClC.CmpCloseCard_AfterRhythm,
				CClC.CmpCloseCard_AfterSat,
				CClC.CmpCloseCard_IsDefecation,
				CClC.CmpCloseCard_IsDiuresis,
				CClC.CmpCloseCard_IsVomit,
				CClC.CmpCloseCard_IsTrauma,
				CClC.CmpLethalType_id,
				CClC.CmpCloseCard_MenenAddiction,
				CClC.LeaveType_id,
				MPh1.Person_Fio as EmergencyTeam_HeadShiftFIO,
				MPh2.Person_Fio as EmergencyTeam_HeadShift2FIO,
				MPd1.Person_Fio  as EmergencyTeam_DriverFIO,
				MPa1.Person_Fio as EmergencyTeam_Assistant1FIO,
				convert(varchar(10), CClC.CmpCloseCard_LethalDT, 104)+' '+convert(varchar(5), cast(CClC.CmpCloseCard_LethalDT as datetime), 108) as CmpCloseCard_LethalDT,
				UCA.PMUser_Name as pmUser_insName,
				UCT.PMUser_Name as FeldsherTransName,
				LB.LpuBuilding_IsWithoutBalance,
				ISNULL(CCC.CmpCallCard_IsPaid, 1) as CmpCallCard_IsPaid,
				ISNULL(CCC.CmpCallCard_IndexRep, 0) as CmpCallCard_IndexRep,
				ISNULL(CCC.CmpCallCard_IndexRepInReg, 1) as CmpCallCard_IndexRepInReg,
				ISNULL(CCC.CmpCallCard_isControlCall, 1) as CmpCallCard_isControlCall,
				CASE WHEN CCC.CmpCallCard_IndexRep >= isnull(CCC.CmpCallCard_IndexRepInReg, 0) THEN 'true' ELSE 'false' END as CmpCallCard_RepFlag,
				CASE WHEN DATEDIFF(ss,CCC.CmpCallCard_insDT,CClC.CmpCloseCard_insDT) < 10 THEN 'true' ELSE 'false' END as addedFromStreamMode
			FROM
				{$this->schema}.CmpCloseCard CClC with (nolock)
				LEFT JOIN CmpCallCard CCC (nolock) on CCC.CmpCallCard_id = CClC.CmpCallCard_id
				LEFT JOIN v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CClC.Lpu_id
				LEFT JOIN v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = CCC.LpuBuilding_id
				LEFT JOIN v_EmergencyTeam EMT with (nolock) on EMT.EmergencyTeam_id = CCC.EmergencyTeam_id
				LEFT JOIN v_pmUserCache UCA with (nolock) on UCA.PMUser_id = CClC.pmUser_insID
				LEFT JOIN v_pmUserCache UCT with (nolock) on UCT.PMUser_id = CClC.FeldsherTrans
				LEFT JOIN v_MedStaffFact msf on (msf.MedStaffFact_id= EMT.EmergencyTeam_HeadShiftWorkPlace)
				LEFT JOIN v_MedPersonal MPh1 with(nolock) ON( MPh1.MedPersonal_id=EMT.EmergencyTeam_HeadShift )
				LEFT JOIN v_MedPersonal MPh2 with(nolock) ON( MPh2.MedPersonal_id=EMT.EmergencyTeam_HeadShift2 )
				LEFT JOIN v_MedPersonal MPd1 with(nolock) ON( MPd1.MedPersonal_id=EMT.EmergencyTeam_Driver )
				LEFT JOIN v_MedPersonal MPa1 with(nolock) ON( MPa1.MedPersonal_id=EMT.EmergencyTeam_Assistant1 )
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
			".ImplodeWherePH( $where )."
		";

		//var_dump(getDebugSql($sql, $params ));exit;
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}
	
	/**
	 * Возвращает карту вызова на редактирование
	 */
	public function loadCmpCallCardEditForm( $data ){
		$sql = "
			SELECT TOP 1
				'' as accessType,
				CCC.CmpCallCard_id,
				cclc.CmpCloseCard_id,
				ISNULL(CCC.Person_id, 0) as Person_id,
				CCC.CmpArea_gid,
				CCC.CmpArea_id,
				CCC.CmpArea_pid,
				CCC.CmpCallCard_IsAlco,
				CCC.RankinScale_id,
				CCC.CmpCallCard_IsPoli,
				CCC.MedService_id,
				CCC.CmpCallType_id,
				CCC.CmpDiag_aid,
				CCC.CmpDiag_oid,
				CCC.CmpLpu_aid,
				CCC.CmpLpu_id,
				CCC.Lpu_hid,
				CL.Lpu_id as Lpu_oid,
				CCC.CmpPlace_id,
				CCC.CmpProfile_bid,
				CCC.CmpProfile_cid,
				CCC.CmpReason_id,
				CCC.CmpReasonNew_id,
				CCC.CmpResult_id,
				CCC.CmpCallCardStatus_Comment,
				CCC.ResultDeseaseType_id,
				CCC.LeaveType_id,
				CCC.CmpTalon_id,
				CCC.CmpTrauma_id,
				CCC.Diag_sid,
				CCC.Diag_uid,
				CCC.Diag_sopid,
				case when ISNULL(CCC.Sex_id,0) = 0 then (case when ISNULL(PS.Sex_id,0) = 0 then '' else PS.Sex_id end) else CCC.Sex_id end as Sex_id,
				PS.Sex_id as SexIdent_id,
				PS.Person_deadDT as Person_deadDT,
				CCC.CmpCallCard_Numv,
				CCC.CmpCallCard_Ngod,
				CCC.CmpCallCard_NumvPr,
				CCC.CmpCallCard_NgodPr,
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
				CCC.CmpCallCard_IsExtra as CmpCallCard_IsExtra,
				CCC.Person_IsUnknown,
				RTRIM(LTRIM(case when ISNULL(CCC.Person_SurName,'') = '' then (case when ISNULL(PS.Person_Surname,'') = '' then '' else PS.Person_Surname end) else CCC.Person_SurName end)) as Person_SurName,
				RTRIM(LTRIM(case when ISNULL(CCC.Person_FirName,'') = '' then (case when ISNULL(PS.Person_Firname,'') = '' then '' else PS.Person_Firname end) else CCC.Person_FirName end)) as Person_FirName,
				RTRIM(LTRIM(case when ISNULL(CCC.Person_SecName,'') = '' then (case when ISNULL(PS.Person_Secname,'') = '' then '' else PS.Person_Secname end) else CCC.Person_SecName end)) as Person_SecName,
				RTRIM(LTRIM(ISNULL(PS.Person_Surname, ''))) as PersonIdent_Surname,
				RTRIM(LTRIM(ISNULL(PS.Person_Firname, ''))) as PersonIdent_Firname,
				RTRIM(LTRIM(ISNULL(PS.Person_Secname, ''))) as PersonIdent_Secname,
				convert(varchar(10), isnull(CCC.Person_BirthDay, PS.Person_Birthday), 104) as Person_BirthDay,
				CCC.Person_Age as Person_Age,
				ISNULL(dbo.Age2(PS.Person_Birthday, CCC.CmpCallCard_prmDT), '') as PersonIdent_Age,
				COALESCE(CCC.Person_PolisSer, PS.Polis_Ser, null) as Polis_Ser,
				COALESCE(CCC.Person_PolisNum, PS.Polis_Num, null) as Polis_Num,
				COALESCE(CCC.CmpCallCard_PolisEdNum, PS.Person_EdNum, null) as Polis_EdNum,
				PS.Polis_Num as PolisIdent_Num,
				CCC.CmpCallCard_Ktov,
				CCC.CmpCallerType_id,
				CCC.CmpCallCard_Smpt,
				CCC.CmpCallCard_Stan,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
				convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
				-- текстовая выборка, ибо затесавшиеся в CmpCallCard_prmDT мешают проверке при сохранении услуг (#100697)
				convert(varchar(16), CCC.CmpCallCard_prmDT, 120) as CmpCallCard_prmDT,
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

				convert(varchar, CCC.CmpCallCard_Tper, 120) as CmpCallCard_Tper,
				convert(varchar, CCC.CmpCallCard_Vyez, 120) as CmpCallCard_Vyez,
				convert(varchar, CCC.CmpCallCard_Przd, 120) as CmpCallCard_Przd,
				convert(varchar, CCC.CmpCallCard_Tgsp, 120) as CmpCallCard_Tgsp,
				convert(varchar, CCC.CmpCallCard_Tsta, 120) as CmpCallCard_Tsta,
				convert(varchar, CCC.CmpCallCard_Tisp, 120) as CmpCallCard_Tisp,
				convert(varchar, CCC.CmpCallCard_Tvzv, 120) as CmpCallCard_Tvzv,

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
				,CCC.UslugaComplex_id
				,CCC.Lpu_id
				,CCC.LpuBuilding_id
				,CCC.CmpCallCard_IsNMP
				,CCC.CmpCallCard_IsExtra
				,CCC.Lpu_ppdid
				,ISNULL(L.Lpu_Nick,'') as CmpLpu_Name
				,CASE WHEN ISNULL(OC.OftenCallers_id,0) = 0 THEN 1 ELSE 2 END as Person_isOftenCaller

				,CCC.UnformalizedAddressDirectory_id
				,case when isnull(CCC.KLStreet_id,0) = 0 then
					case when isnull(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL
					else 'UA.'+CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
				else 'ST.'+CAST(CCC.KLStreet_id as varchar(20)) end as StreetAndUnformalizedAddressDirectory_id

				,CCC.CmpCallCard_UlicSecond

				,CASE WHEN CArea.KLAreaLevel_id = 1 THEN CCC.KLCity_id ELSE CCC.KLRgn_id END as KLRgn_id
				,CASE WHEN CArea.KLAreaLevel_id = 2 THEN CCC.KLCity_id ELSE CCC.KLSubRgn_id END as KLSubRgn_id
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
				,CRR.CmpRejectionReason_Name
				,CCC.Lpu_cid
				,CCC.EmergencyTeam_id
				,CCC.CmpCallCard_IsPassSSMP
				,CCC.Lpu_smpid
				,CCC.CmpLeaveType_id
				,CCC.CmpLeaveTask_id
				,CCC.CmpMedicalCareKind_id
				,CCC.CmpResultDeseaseType_id
				,CCC.CmpCallCardResult_id
				,CCC.CmpMedicalCareKind_id
				,CCC.CmpTransportType_id
				,CCC.PayType_id
				,CCC.CmpCallCard_isControlCall
				,cccst.CmpCallCardStatusType_Code
			FROM
				CmpCallCard CCC with (nolock)
				left join {$this->schema}.v_CmpCloseCard cclc (nolock) on CCC.CmpCallCard_id = cclc.CmpCallCard_id
				left join v_CmpCallCardCostPrint ccp (nolock) on ccp.CmpCallCard_id = CCC.CmpCallCard_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_KLArea CArea with (nolock) on CCC.KLCity_id = CArea.KLArea_id
				left join v_CmpCallCardStatusType cccst with (nolock) on cccst.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id
				-- left join v_PersonState PS (nolock) on PS.Person_id = CCC.Person_id
				outer apply(
					select top 1
						*
					from v_CmpCallCardStatus CCCS
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
					order by CCCS.CmpCallCardStatus_updDT desc
				) as lastStatus
				left join v_CmpRejectionReason CRR with (nolock) on CRR.CmpRejectionReason_id = lastStatus.CmpReason_id
				outer apply(
					select top 1
						 pa.Person_id
						,ISNULL(pa.Person_SurName, '') as Person_Surname
						,ISNULL(pa.Person_FirName, '') as Person_Firname
						,ISNULL(pa.Person_SecName, '') as Person_Secname
						,pa.Person_BirthDay as Person_Birthday
						,convert(varchar,cast(pa.Person_deadDT as datetime),120) as Person_deadDT
						,ISNULL(pa.Sex_id, 0) as Sex_id
						,pa.Person_EdNum
						,ISNULL(p.Polis_Ser, '') as Polis_Ser
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
						and Lpu_id = CCC.Lpu_id
						and (WorkData_begDate is null or WorkData_begDate <= cast(CCC.CmpCallCard_prmDT as date))
						and (WorkData_endDate is null or WorkData_endDate > cast(CCC.CmpCallCard_prmDT as date))
					order by PostOccupationType_id asc
				) msf1
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				LEFT JOIN v_OftenCallers OC with (nolock) on OC.Person_id = CCC.Person_id
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query( $sql, array(
			'CmpCallCard_id' => $data[ 'CmpCallCard_id' ],
		) );

		if ( is_object( $result ) ) {
			return $result->result_array();
		}

		return false;
	}

	/**
	 * default desc
	 */
	function loadCmpStation($data) {
		$query = "
			select
				CmpStation.Lpu_id,
				CmpStation_id,
				CmpStation_Code,
				CmpStation_Name
			from
				v_CmpStation CmpStation with (nolock)
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = CmpStation.Lpu_id
			where
				CmpStation.Lpu_id = :Lpu_id or :Lpu_id is null
		";
		$result = $this->db->query($query, array(
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
	 * default desc
	 */
	function RefuseOnTimeout($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return false;
		}

		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_updDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		$query = "
			declare @cmp_waiting_ppd_time bigint = (select ISNULL((select top 1 DS.DataStorage_Value FROM DataStorage DS (nolock) where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0), 20));

			select
				CCC.CmpCallCard_id
			from
				CmpCallCard CCC with (nolock)
			where
				{$filter}
				and @cmp_waiting_ppd_time - DATEDIFF(mi,CCC.CmpCallCard_updDT,dbo.tzGetDate()) <0
				and CCC.CmpCallCard_IsReceivedInPPD!=2
				and CCC.CmpCallCardStatusType_id=1
				and CCC.Lpu_ppdid is not null
			";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$RefuseData = array();
		$val = $result->result('array');
		for ($i=0;$i<count($val);$i++) {
			$RefuseData[$i]['CmpCallCard_id']=$val[$i]['CmpCallCard_id'];
			$RefuseData[$i]['pmUser_id'] = $data['pmUser_id'];
			$RefuseData[$i]['CmpCallCardStatusType_id'] = 3;
			$RefuseData[$i]['CmpCallCardStatus_Comment'] = 'Время ожидания истекло';
			$this->setStatusCmpCallCard($RefuseData[$i]);
		}
	}
	/**
	 * default desc
	 */
	function loadSMPWorkPlace($data) {

		$this->RefuseOnTimeout($data);

		$filter = '(1 = 1)';
		$queryParams = array();

		//$filter .= " and CL.Lpu_id = :Lpu_id";
		//$queryParams['Lpu_id'] = $data['Lpu_id'];

		$filter .= " and isnull(CCC.CmpCallCard_IsOpen, 1) = 2 and COALESCE(CCC.CmpCallCard_IsReceivedInPPD,1)!=2"; // временно только открытые карты

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['Search_SurName']) . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['Search_FirName']) . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['Search_SecName']) . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
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

		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}

		$filter .=" and CCC.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];

		$filter .=" and CCC.CmpCallCardStatusType_id in(1,2,3,4,5)";

		$query = "
			-- variables
			declare @cmp_waiting_ppd_time bigint = (select ISNULL((select top 1 DS.DataStorage_Value FROM DataStorage DS (nolock) where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0), 20));
			-- end variables

			select
				-- select
				 CCC.CmpCallCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,CCC.CmpCallCard_Numv
				,CCC.CmpLpu_id
				,CCC.CmpCallCard_Ngod
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,COALESCE(L.Lpu_Nick,'') as CmpLpu_Name
				,RTRIM(ISNULL(CD.Diag_Code, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Code, '')) as StacDiag_Name
				,CCC.CmpCallCard_prmDT
				,case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
						then CONVERT(varchar(5),DATEADD(mi, @cmp_waiting_ppd_time - DATEDIFF(mi,CCC.CmpCallCard_updDT,dbo.tzGetDate())  ,CONVERT(datetime,0)),108)
						else '00'+':'+'00'
				end as PPD_WaitingTime
				,SLPU.Lpu_Nick as SendLpu_Nick

				--,EPL.Diag_id as EPLDiag_id


				,case
				when CCC.CmpCallCardStatusType_id in(5) then
					CCC.CmpCallCardStatus_Comment
				/* во втором запросе
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				*/
				end	as PPDDiag
				/* ,case when RC.ResultClass_Name is not null then 'Результат: '+RC.ResultClass_Name else '' end as PPDResult */
				,case
					when RES.CmpPPDResult_Name is not null
						then 'Результат: '+RES.CmpPPDResult_Name
						else case when CCC.CmpCallCardStatusType_id = 3
							then COALESCE(CMFNR.CmpMoveFromNmpReason_Name,CRTSR.CmpReturnToSmpReason_Name,CCC.CmpCallCardStatus_Comment,'')
							else '' end
				end as PPDResult
				/* во втором запросе
				,case when CCC.CmpCallCardStatusType_id in (4) then MP.Person_SurName+' '+MP.Person_FirName +' '+ convert(varchar(10), cast(ServeDT.ServeDT as datetime), 108) else '' end as ServedBy
				*/
				,'' as ServedBy
				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name + convert(varchar(10), cast(ToDT.ToDT as datetime), 108) else '' end as PPDUser_Name
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							/* во втором запросе
							when CCC.CmpCallCardStatusType_id=4 AND EPLD.diag_FullName IS NOT NULL then CCC.CmpCallCardStatusType_id+2
							*/
							when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id+1
							when CCC.CmpCallCardStatusType_id=5 then CCC.CmpCallCardStatusType_id+2
							else 1
						end
					else 6
				end as CmpGroup_id,
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn
				-- end select
			from
				-- from
				v_CmpCallCard CCC with (nolock)
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT

				outer apply(
					select top 1
						CmpMoveFromNmpReason_id,
						CmpReturnToSmpReason_id
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as CmpNmpToSmpReason
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				left join v_Diag CD with (nolock) on CD.Diag_id = CCC.Diag_gid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES (nolock) on RES.CmpPPDResult_id = CCC.CmpPPDResult_id

				left join v_Lpu SLPU with (nolock) on SLPU.Lpu_id = CCC.Lpu_ppdid
				left join v_CmpMoveFromNmpReason CMFNR with (nolock) on CMFNR.CmpMoveFromNmpReason_id = CmpNmpToSmpReason.CmpMoveFromNmpReason_id
				left join v_CmpReturnToSmpReason CRTSR with (nolock) on CRTSR.CmpReturnToSmpReason_id = CmpNmpToSmpReason.CmpReturnToSmpReason_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = CCC.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				(case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id+1
							else 1
						end
					else 7
				end),

				CCC.CmpCallCard_prmDT desc
				-- end order by
		";

		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$val = $result->result('array');
		// Добавляем ТАПы к вызовам
		// Собираем id cmpcallcard
		$CmpId = array();
		foreach ($val as $v) {
			$CmpId[] = $v['CmpCallCard_id'];
		}
		if (count($CmpId)>0) {
			// Выполняем запрос, получая дополнительные поля
			$list_CmpId = implode(",", $CmpId);
			$query2 = "
				Select
					EPL.EvnPL_id,
					EPL.CmpCallCard_id,
					case
						when CCC.CmpCallCardStatusType_id in(5) then
							CCC.CmpCallCardStatus_Comment
						when CCC.CmpCallCardStatusType_id = 4 then
							case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
							case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
					end	as PPDDiag,
					case when CCC.CmpCallCardStatusType_id in (4) then MP.Person_SurName+' '+MP.Person_FirName +' '+ convert(varchar(10), cast(ServeDT.ServeDT as datetime), 108) else '' end as ServedBy,
					case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
						then
							case
								when CCC.CmpCallCardStatusType_id=4 AND EPLD.diag_FullName IS NOT NULL then CCC.CmpCallCardStatusType_id+2 /* Закрыто если обслужено и создан случай АПЛ */
								when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id+1
								when CCC.CmpCallCardStatusType_id=5 then CCC.CmpCallCardStatusType_id+2
								else 1
							end
						else 6
					end as CmpGroup_id
				from EvnPL EPL 
				inner join Evn Evn on Evn.Evn_id = EPL.EvnPL_id and Evn.Evn_deleted = 1
				inner join v_CmpCallCard CCC on CCC.CmpCallCard_id = EPL.CmpCallCard_id and CCC.Lpu_ppdid is not null
				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_pid = EPL.EvnPL_id
				left join v_MedPersonal MP with (nolock) on 1=1 and MP.MedPersonal_id = EVPL.MedPersonal_id and MP.Lpu_id = 1
				outer apply(
							select top 1
								CmpCallCardStatus_insDT as ServeDT
							from
								v_CmpCallCardStatus with(nolock)
							where
								CmpCallCardStatusType_id = 4 and CmpCallCard_id = EPL.CmpCallCard_id
							order by CmpCallCardStatus_insDT desc
						) as ServeDT
				where EPL.CmpCallCard_id in ({$list_CmpId})
					AND Evn.Lpu_id = CCC.Lpu_ppdid
					and Evn.Evn_setDT >= cast(CCC.CmpCallCard_prmDT as date)
			";

			$r = $this->db->query($query2, array());
			if (is_object($r) ) {
				// разбираем данные 
				$ea = $r->result('array');
				$evnpls = array();
				// преобразуем массив для дальнейшего сведения данных
				foreach ($ea as $v) {
					$evnpls[$v['CmpCallCard_id']] = $v;
				}
				// объединяем по строкам, если есть какие то данные
				foreach ($val as &$v) {
					if (!empty($evnpls[$v['CmpCallCard_id']])) {
						$v['PPDDiag'] = $evnpls[$v['CmpCallCard_id']]['PPDDiag'];
						$v['ServedBy'] = $evnpls[$v['CmpCallCard_id']]['ServedBy'];
						$v['CmpGroup_id'] = $evnpls[$v['CmpCallCard_id']]['CmpGroup_id'];
					}
				}
			} 
		}
		
		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}

	/**
	 * default desc
	 */
	function loadSMPDispatchCallWorkPlace($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and isnull(CCC.CmpCallCard_IsOpen, 1) = 2"; // временно только открытые карты
		// Скрываем вызовы принятые в ППД
		$filter .= " and coalesce(CCC.CmpCallCard_IsReceivedInPPD,1)!=2";
		$queryParams['pmUser_id'] = (!empty($data['pmUser_id'])) ? $data['pmUser_id'] : 0;

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
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

		if (!empty($data['begDate'])&&!empty($data['endDate'])&&($data['begDate']==$data['endDate'])&&(!empty($data['hours']))&&$isToday) {

			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= DATEADD(day, -1, :begDate)";
			$queryParams['begDate'] = $data['begDate'];
			$filter .= " and CCC.CmpCallCard_prmDT> DATEADD(hour, CAST(:hours as integer), @curdate)";
			switch ($data['hours']) {
				case '1':
				case '2':
				case '3':
				case '6':
				case '12':
				case '24':
					$queryParams['hours'] = '-'.$data['hours'];
					break;
				default:
					$queryParams['hours'] = '-24';
					break;
			}
		}
		else {
			if ( !empty($data['begDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
				$queryParams['begDate'] = $data['begDate'];
			}

			if ( !empty($data['endDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
				$queryParams['endDate'] = $data['endDate'];
			}
		}

		if ( !empty($data['CmpCallCard_id']) ) {
			$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}

		// TODO тут придумать чтото с ППД..
		$filter .=" and CCC.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];


		$query = "
			-- variables
			declare @curdate datetime = dbo.tzGetDate();
			-- end variables

			select
				-- select
				 CCC.CmpCallCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,ISNULL(CCC.Person_Age,0) as Person_Age
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as CmpCallCard_isLocked
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then
					COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				else
					'<img src=\"../img/grid/lock.png\">'+COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name
				,RTRIM(case when CLD.diag_FullName is not null then CLD.diag_FullName else RTRIM(ISNULL(CLD.Diag_Code, '') +' '+ ISNULL(CLD.Diag_Name, '')) end) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Code, '')) as StacDiag_Name
				,CCC.CmpCallCard_prmDT
				,SLPU.Lpu_Nick as SendLpu_Nick

				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name

				--,EPL.Diag_id as EPLDiag_id
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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
				end as CmpGroup_id
				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name + convert(varchar(10), cast(CCC.CmpCallCard_updDT as datetime), 104) else '' end as PPDUser_Name
				,case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when ISNULL(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when RC.ResultClass_Name is not null then '<br />Результат: '+RC.ResultClass_Name else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				end	as PPDResult
				,convert(varchar(10), cast(ServeDT.ServeDT as datetime), 104) as ServeDT
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then '0'+cast (CCC.CmpCallCardStatusType_id+1 as varchar)
										when CCC.CmpCallCardStatusType_id in (4) then '0'+cast (CCC.CmpCallCardStatusType_id as varchar)
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then ('0'+cast(CCC.CmpCallCardStatusType_id+4 as varchar))
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
									end
						END
					else '10'
				end as CmpGroupName_id
				,case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as Owner
				-- end select
			from
				-- from
				v_CmpCallCard CCC with (nolock)
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT

				outer apply(
					select top 1
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_CmpMoveFromNmpReason with (nolock) on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as CCCStatusHist

				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU with (nolock) on SLPU.Lpu_id = CCC.Lpu_ppdid
				OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
				) EPL
				/*left join v_EvnPL EPL with (nolock) on 1=1
					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id

				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_CmpCallCardlockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
					and (60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,@curdate)) >0
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				(case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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


		// echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');
		//var_dump($val); exit;
		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}
	/**
	 * default desc
	 */
	function getCmpCallCardSmpInfo($data){
		$this->RefuseOnTimeout($data);
		$this->unlockCmpCallCard($data);
		$filter = '(1=1)';
		if ( !empty($data['CmpCallCard_id']) ) {
			$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		} else {
			return false;
		}

		$query = "
			-- variables
			declare @cmp_waiting_ppd_time bigint = (select ISNULL((select top 1 DS.DataStorage_Value FROM DataStorage DS (nolock) where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0), 20));
			-- end variables

			select
				-- select
				 CCC.CmpCallCard_id
				,CLC.CmpCloseCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,ISNULL(CCC.Person_Age, 0) as Person_Age
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,'<img src=\"../img/grid/lock.png\">' + COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name
				,RTRIM(ISNULL(CD.CmpDiag_Code, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Code, '')) as StacDiag_Name
				,CCC.CmpCallCard_prmDT
				,SLPU.Lpu_Nick as SendLpu_Nick
				,ET.EmergencyTeam_Num
				,CCC.Lpu_id

				,1 as CmpCallCard_isLocked
				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end as Adress_Name

				--,EPL.Diag_id as EPLDiag_id

				,case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
						then CONVERT(varchar(5),DATEADD(mi, ISNULL(@cmp_waiting_ppd_time - DATEDIFF(mi,CCC.CmpCallCard_updDT,dbo.tzGetDate()),20)  ,CONVERT(datetime,0)),108)
						else '00'+':'+'00'
				end as PPD_WaitingTime

				,case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when ISNULL(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when RC.ResultClass_Name is not null then '<br />Результат: '+RC.ResultClass_Name else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				end	as PPDResult
				,convert(varchar(10), cast(ServeDT.ServeDT as datetime), 104) as ServeDT
				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name + convert(varchar(10), cast(CCC.CmpCallCard_updDT as datetime), 104) else '' end as PPDUser_Name

				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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
				end as Admin_CmpGroup_id
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then '0'+cast (CCC.CmpCallCardStatusType_id+1 as varchar)
										when CCC.CmpCallCardStatusType_id in (4) then '0'+cast (CCC.CmpCallCardStatusType_id as varchar)
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then ('0'+cast(CCC.CmpCallCardStatusType_id+4 as varchar))
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
									end
						END
					else '10'
				end as Admin_CmpGroupName_id

				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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
				end as HeadDuty_CmpGroup_id
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id=4 THEN '04'
									WHEN CCC.CmpCallCardStatusType_id=6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id=3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN '0'+cast(CCC.CmpCallCardStatusType_id+1 as varchar)
									ELSE  '0'+cast(CCC.CmpCallCardStatusType_id+4 as varchar)
								END
							ELSE
								CASE

									WHEN CmpCallCardStatusType_id=4 THEN '07'
									WHEN CmpCallCardStatusType_id=3 THEN '08'
									WHEN CmpCallCardStatusType_id=6 THEN '10'
									ELSE '0'+cast(CCC.CmpCallCardStatusType_id+4 as varchar)
								END
							END
					else '09'
				end as HeadDuty_CmpGroupName_id


				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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
				end as DispatchDirect_CmpGroup_id

				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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
				end as DispatchCall_CmpGroup_id
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid IS NULL
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then '0'+cast (CCC.CmpCallCardStatusType_id+1 as varchar)
										when CCC.CmpCallCardStatusType_id in (4) then '0'+cast (CCC.CmpCallCardStatusType_id as varchar)
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then ('0'+cast(CCC.CmpCallCardStatusType_id+4 as varchar))
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
									end
						END
					else '10'
				end as DispatchCall_CmpGroupName_id

				,case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as Owner
				-- end select
			from
				-- from
				v_CmpCallCard CCC with (nolock)
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT
				outer apply(
					select top 1
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_CmpMoveFromNmpReason with (nolock) on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as CCCStatusHist
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU with (nolock) on SLPU.Lpu_id = CCC.Lpu_ppdid
				OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
				) EPL
				/*left join v_EvnPL EPL with (nolock) on 1=1
					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID

				left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id

				left join v_CmpCallCardlockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
					and (60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate())) >0

				left join v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id

				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id

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
	 * Возвращает список вызовов для грида администратора СМП
	 */
	public function loadSMPAdminWorkPlace($data){
		$filter = '(1 = 1)';
		$queryParams = array();

		$procedure = "v_CmpCallCard";

		// Скрываем вызовы принятые в ППД
		$filter .= " and COALESCE(CCC.CmpCallCard_IsReceivedInPPD,1)!=2";
		//$filter .= " and CL.Lpu_id = :Lpu_id";
		//$queryParams['Lpu_id'] = $data['Lpu_id'];

		$filter .= " and isnull(CCC.CmpCallCard_IsOpen, 1) = 2"; // временно только открытые карты

		$queryParams['pmUser_id'] = (!empty($data['pmUser_id'])) ? $data['pmUser_id'] : 0;

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
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

		$deletedField = "NULL";
		if ( !empty($data['displayDeletedCards']) && $data['displayDeletedCards'] == 'on' ) {
			$deletedField = "CCC.CmpCallCard_deleted";
			$procedure = "CmpCallCard";
			$filter .= " and CCC.CmpCallCard_firstVersion is null";
		}

		if ( !empty($data['LpuBuilding_id']) ) {
			$filter .= " and (CCC.LpuBuilding_id = :LpuBuilding_id)";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		} else {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (!empty($lpuBuilding[0]) && !empty($lpuBuilding[0]["LpuBuilding_id"])) {
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];

				$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
				$operLpuBuilding = $this->CmpCallCard_model4E->getOperDepartament($data);
				if (!empty($operLpuBuilding["LpuBuilding_pid"]) && $operLpuBuilding["LpuBuilding_pid"] == $data['LpuBuilding_id']){
					//опер отдел
					$smpUnitsNested = $this->CmpCallCard_model4E->loadSmpUnitsNested( $data, true );
					if (!empty($smpUnitsNested)) {
						$list = array();
						foreach($smpUnitsNested as $value) {
							$list[] = $value['LpuBuilding_id'];
						}
						$list_str = implode(",", $list);
						$filter .= " and (CCC.LpuBuilding_id in ($list_str) OR (CCC.CmpCallCard_IsExtra = 2 AND CCC.Lpu_id = :Lpu_id))";
					}
				}
				else{
					//подчиненные подстанции
					$filter .= " and (CCC.LpuBuilding_id = :LpuBuilding_id OR (CCC.CmpCallCard_IsExtra = 2 AND CCC.Lpu_id = :Lpu_id))";
					$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
				}
			}

		}

		$isToday = strtotime($data['begDate']) ==  mktime(0,0,0,date('m'),date('d'),date('Y'));

		if (!empty($data['begDate'])&&!empty($data['endDate'])&&($data['begDate']==$data['endDate'])&&(!empty($data['hours']))&&$isToday) {

			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= DATEADD(day, -1, :begDate)";
			$queryParams['begDate'] = $data['begDate'];
			$filter .= " and CCC.CmpCallCard_prmDT> DATEADD(hour, CAST(:hours as integer), dbo.tzGetDate())";
			switch ($data['hours']) {
				case '1':
				case '2':
				case '3':
				case '6':
				case '12':
				case '24':
					$queryParams['hours'] = '-'.$data['hours'];
					break;
				default:
					$queryParams['hours'] = '-24';
					break;
			}
		}
		else {
			if ( !empty($data['begDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
				$queryParams['begDate'] = $data['begDate'];
			}

			if ( !empty($data['endDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
				$queryParams['endDate'] = $data['endDate'];
			}
		}

		//Для получения изменений одного талона вызова
		if ( !empty($data['CmpCallCard_id']) ) {
			$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}

		if (getRegionNick() == 'kz') {
			$filter .=" and CCC.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}

		$this->load->model("Options_model", "opmodel");
		$o = $this->opmodel->getOptionsGlobals($data);
		$g_options = $o['globals'];
		if(isset($g_options["smp_call_time_format"]) && $g_options["smp_call_time_format"] == '2')
			$formatDateinList = 'varchar(5)';
		else
			$formatDateinList = 'varchar(8)';

		$query = "
			declare @region bigint = dbo.GetRegion()

			select
				-- select
				 CCC.CmpCallCard_id
				,CCC.CmpCallCard_IsNMP
				,CCC.CmpCallCard_IsReceivedInPPD
				,CLC.CmpCloseCard_id
				,COALESCE(CCCL.Lpu_Nick ,lsL.Lpu_Nick) as Lpu_Nick
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(CCC.Person_Surname, PS.Person_SurName) as Person_Surname
				,ISNULL(CCC.Person_Firname, PS.Person_FirName) as Person_Firname
				,ISNULL(CCC.Person_Secname, PS.Person_SecName) as Person_Secname
				,ISNULL(CCC.Person_Age, 0) as Person_Age
				,CCC.pmUser_insID
				--,convert(varchar(10), CCC.CmpCallCard_prmDT, 120) + ' ' + convert(" . $formatDateinList . ", CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmDate
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 120) as CmpCallCard_prmDate
				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,CCC.LpuBuilding_id
				,CRR.CmpRejectionReason_Name
				--,CASE WHEN (CmpCallCard_firstVersion is null) THEN COALESCE({$deletedField}, 1) else 1 end as CmpCallCard_isDeleted
				,{$deletedField} as CmpCallCard_isDeleted
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as CmpCallCard_isLocked
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then
					COALESCE(CCC.Person_SurName, PS.Person_Surname, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, PS.Person_Firname, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, PS.Person_Secname, '')
				else
					'<img src=\"../img/grid/lock.png\">'+COALESCE(CCC.Person_SurName, PS.Person_Surname, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, PS.Person_Firname, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, PS.Person_Secname, '')
				end as Person_FIO
				--,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) , 104) as Person_Birthday,
				,convert(varchar(20), cast(ISNULL(CCC.Person_BirthDay, PS.Person_Birthday) as datetime), 113) as Person_Birthday,

				-- ,RTRIM(case when CR.CmpReason_id is not null then convert(varchar(20), CR.CmpReason_Code)+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				CCC.CmpReason_id,
				COALESCE(CR.CmpReason_Code + '. ', '') + CR.CmpReason_Name as CmpReason_Name,
				CCC.CmpSecondReason_id,
				COALESCE(CSecondR.CmpReason_Code + '. ', '') + CSecondR.CmpReason_Name as CmpSecondReason_Name,

				RTRIM(case when CCT.CmpCallType_id is not null then convert(varchar(20), CCT.CmpCallType_Code)+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,CL.Lpu_Nick as CmpLpu_Name
				,RTRIM(case when CLD.diag_FullName is not null then CLD.diag_FullName else CD.Diag_Code end) as CmpDiag_Name

				--,RTRIM(ISNULL(D.Diag_Code, '')) as StacDiag_Name
				,CASE WHEN (CLC.CmpCloseCard_id is not null) THEN DiagStacFromCombo.Diag_FullName ELSE RTRIM(ISNULL(D.Diag_FullName, '')) END as StacDiag_Name

				,ET.EmergencyTeam_Num
				,CCC.CmpCallCard_prmDT
				,SLPU.Lpu_Nick as SendLpu_Nick

				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick+' '+SRGNCity.KLSubRgn_Name+', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick+' '+SRGNTown.KLSubRgn_Name+', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick+' '+SRGN.KLSubRgn_Name+', ' else '' end end end+
				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end+
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else ''
				end+

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else
					', '+Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '+CmpCallCard_Ulic else '' end
				end +
				+ case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '+LOWER(socrSecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
					', '+SecondStreet.KLStreet_FullName end
					else ''
				end +
				--case when Street.KLStreet_FullName is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Room is not null then ', ком. '+CCC.CmpCallCard_Room else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name

				--,EPL.Diag_id as EPLDiag_id

				,CCC.CmpCallCardStatusType_id
				,case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when ISNULL(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when RC.ResultClass_Name is not null then '<br />Результат: '+RC.ResultClass_Name else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				end	as PPDResult
				--,convert(varchar(10), cast(ServeDT.ServeDT as datetime), 104) as ServeDT
				,cast(ServeDT.ServeDT as datetime) as ServeDT
				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name + convert(varchar(10), cast(CCC.CmpCallCard_updDT as datetime), 104) else '' end as PPDUser_Name
				,CCT.CmpCallType_Code
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when @region = 2 and CCT.CmpCallType_Code = 14 then 10
							when CCC.CmpCallCardStatusType_id is NULL then 1
							when CCC.Lpu_ppdid is null
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then CCC.CmpCallCardStatusType_id+1
										when CCC.CmpCallCardStatusType_id in (4) then CCC.CmpCallCardStatusType_id
										when CCC.CmpCallCardStatusType_id in (6) then 10
										when CCC.CmpCallCardStatusType_id in (5) then 9
										when CCC.CmpCallCardStatusType_id in (3) then 7
										WHEN CCC.CmpCallCardStatusType_id in (7) THEN 3
										WHEN CCC.CmpCallCardStatusType_id in (8) THEN 2
										WHEN CCC.CmpCallCardStatusType_id > 8 THEN 1
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5,6) then CCC.CmpCallCardStatusType_id+4
										WHEN CCC.CmpCallCardStatusType_id in (7) THEN 3
										WHEN CCC.CmpCallCardStatusType_id in (8) THEN 2
										WHEN CCC.CmpCallCardStatusType_id in (20) THEN 5
										WHEN CCC.CmpCallCardStatusType_id > 8 THEN 1
									end
						END
					else 10
				end as CmpGroup_id
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when @region = 2 and CCT.CmpCallType_Code = 14 then '10'
							when CCC.CmpCallCardStatusType_id is NULL then '01'
							when CCC.Lpu_ppdid is null
								then
									case
										when CCC.CmpCallCardStatusType_id in (1,2) then '0'+cast (CCC.CmpCallCardStatusType_id+1 as varchar)
										when CCC.CmpCallCardStatusType_id in (4) then '0'+cast (CCC.CmpCallCardStatusType_id as varchar)
										when CCC.CmpCallCardStatusType_id in (6) then '10'
										when CCC.CmpCallCardStatusType_id in (5) then '09'
										when CCC.CmpCallCardStatusType_id in (3) then '07'
										WHEN CCC.CmpCallCardStatusType_id in (7) THEN ('03')
										WHEN CCC.CmpCallCardStatusType_id in (8) THEN ('02')
										WHEN CCC.CmpCallCardStatusType_id > 8 THEN ('01')
									end
								else
									case
										when CCC.CmpCallCardStatusType_id in (1,2,3,4,5) then ('0'+cast(CCC.CmpCallCardStatusType_id+4 as varchar(2)))
										when CCC.CmpCallCardStatusType_id in (6) then ('10')
										WHEN CCC.CmpCallCardStatusType_id in (7) THEN ('03')
										WHEN CCC.CmpCallCardStatusType_id in (8) THEN ('02')
										WHEN CCC.CmpCallCardStatusType_id in (20) THEN ('05')
										WHEN CCC.CmpCallCardStatusType_id > 8 THEN ('01')
									end
						END
					else '10'
				end as CmpGroupName_id
				,case when CCC.pmUser_insID = :pmUser_id then 1 else 0 end as Owner
				,CCC.Lpu_ppdid
				-- end select
			from
				-- from
				$procedure CCC with (nolock)
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT
				outer apply(
					select top 1
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_CmpMoveFromNmpReason with (nolock) on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as CCCStatusHist
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpReason CSecondR with (nolock) on CSecondR.CmpReason_id = CCC.CmpSecondReason_id
				outer apply (
					select top 1
						*
					from v_CmpCallCardStatus CCCS
					where
						CCCS.CmpCallCard_id = CCC.CmpCallCard_id
					order by CCCS.CmpCallCardStatus_updDT desc
				) as lastStatus
				left join v_CmpRejectionReason CRR with (nolock) on CRR.CmpRejectionReason_id = lastStatus.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				outer apply (select top 1
						pc.Person_id as PersonCard_Person_id,
						pc.Lpu_id
					from v_PersonCard_all pc with (nolock)
					where
						pc.Person_id = CCC.Person_id
						and LpuAttachType_id = 1
					order by PersonCard_begDate desc
				) as pcard
				left join v_Lpu CL with (nolock) on pcard.Lpu_id=CL.Lpu_id
				left join v_Diag CD with (nolock) on CD.Diag_id = CCC.Diag_gid
				left join v_Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU with (nolock) on SLPU.Lpu_id = CCC.Lpu_ppdid
				/*OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
				) EPL
				*/
				left join v_EvnPL EPL with (nolock) on 1=1
					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				left join v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				
				left join {$this->comboSchema}.v_CmpCloseCardCombo CLCC with (nolock) on CLCC.CmpCloseCardCombo_Code = 243
                --left join {$this->schema}.v_CmpCloseCardRel RL with (nolock) on RL.CmpCloseCard_id = CLC.CmpCloseCard_id
				--	and CLCC.CmpCloseCardCombo_id = RL.CmpCloseCardCombo_id
				--	and isnumeric(RL.Localize + 'e0') = 1

				outer apply (select top 1
					rel.Localize
					from {$this->schema}.v_CmpCloseCardRel rel with (nolock)
					where
						 rel.CmpCloseCard_id = CLC.CmpCloseCard_id
					and CLCC.CmpCloseCardCombo_id = rel.CmpCloseCardCombo_id
					and isnumeric(rel.Localize + 'e0') = 1
					order by CmpCloseCardRel_insDT desc
				) as RL

				left join v_Diag as DiagStacFromCombo with (nolock) on DiagStacFromCombo.Diag_id = RL.Localize
				left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_Diag CLD with (nolock) on CLD.Diag_id = CLC.Diag_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				left join v_KLSocr socrSecondStreet with (nolock) on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				left join v_KLSubRgn SRGNTown with(nolock) on SRGNTown.KLSubRgn_id = CCC.KLTown_id
				left join v_KLSubRgn SRGNCity with(nolock) on SRGNCity.KLSubRgn_id = CCC.KLCity_id
				left join v_CmpCallCardlockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
					and (60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate())) >0
				left join v_Lpu CCCL with (nolock) on CCCL.Lpu_id=CCC.Lpu_hid
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = CCC.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_Lpu lsL with (nolock) on lsL.Lpu_id = LB.Lpu_id
				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				(case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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


		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return false;
		}

		$val = $result->result('array');

		for ($i = 0; $i < count($val); $i++) {
			if ($val[$i]['ServeDT']) {
				$val[$i]['ServeDT'] = date_format($val[$i]['ServeDT'], 'm.d.Y');
			}
		}

		return array(
			 'data' => $val
			,'totalCount' => count($val)
		);
	}


	/**
	 * @desc Диспетчер направлений
	 */
	function loadSMPDispatchDirectWorkPlace($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$this->RefuseOnTimeout($data);

		// Скрываем вызовы принятые в ППД
		//$filter .= " and CCC.CmpCallCard_IsReceivedInPPD!=2";

		//$filter .= " and CL.Lpu_id = :Lpu_id";
		//$queryParams['Lpu_id'] = $data['Lpu_id'];


		$filter .= " and isnull(CCC.CmpCallCard_IsOpen, 1) = 2"; // временно только открытые карты

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
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

		if (!empty($data['begDate'])&&!empty($data['endDate'])&&($data['begDate']==$data['endDate'])&&(!empty($data['hours']))&&$isToday) {

			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= DATEADD(day, -1, :begDate)";
			$queryParams['begDate'] = $data['begDate'];
			$filter .= " and CCC.CmpCallCard_prmDT> DATEADD(hour, CAST(:hours as integer), @curdate)";
			switch ($data['hours']) {
				case '1':
				case '2':
				case '3':
				case '6':
				case '12':
				case '24':
					$queryParams['hours'] = '-'.$data['hours'];
					break;
				default:
					$queryParams['hours'] = '-24';
					break;
			}
		}
		else {
			if ( !empty($data['begDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
				$queryParams['begDate'] = $data['begDate'];
			}

			if ( !empty($data['endDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
				$queryParams['endDate'] = $data['endDate'];
			}
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

		// TODO тут придумать чтото с ППД..
		$filter .=" and CCC.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];

		// Отображаем только вызовы переданные от диспетчера вызовов СМП
		$filter .= " AND CCC.CmpCallCardStatusType_id IS NOT NULL";

		//старое
		//,RTRIM(ISNULL(CD.CmpDiag_Code, '')) as CmpDiag_Name
		$query = "
			-- variables
			declare @curdate datetime = dbo.tzGetDate();
			declare @cmp_waiting_ppd_time bigint = (select ISNULL((select top 1 DS.DataStorage_Value FROM DataStorage DS (nolock) where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0), 20));
			-- end variables

			select
				-- select
				 CCC.CmpCallCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,ISNULL(CCC.Person_Age,0) as Person_Age
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as CmpCallCard_isLocked
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then
					COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				else
					'<img src=\"../img/grid/lock.png\">'+COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name
				,RTRIM(ISNULL(CLD.Diag_Code, '') +' '+ ISNULL(CLD.Diag_Name, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Code, '')) as StacDiag_Name
				,ET.EmergencyTeam_Num
				,CCC.CmpCallCard_prmDT

				,case when CCC.CmpCallCardStatusType_id = 1 and CCC.Lpu_ppdid is not null
						then CONVERT(varchar(5),DATEADD(mi, ISNULL(@cmp_waiting_ppd_time - DATEDIFF(mi,CCC.CmpCallCard_updDT,@curdate),20)  ,CONVERT(datetime,0)),108)
						else '00'+':'+'00'
				end as PPD_WaitingTime

				,SLPU.Lpu_Nick as SendLpu_Nick

				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name

				--,EPL.Diag_id as EPLDiag_id


				,case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when ISNULL(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when RC.ResultClass_Name is not null then '<br />Результат: '+RC.ResultClass_Name else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				end	as PPDResult
				,convert(varchar(10), cast(ServeDT.ServeDT as datetime), 104) as ServeDT
				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name + convert(varchar(10), cast(CCC.CmpCallCard_updDT as datetime), 104) else '' end as PPDUser_Name

				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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
				end as CmpGroup_id
				/*
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							when CCC.CmpCallCardStatusType_id in(1,2,3,4) then CCC.CmpCallCardStatusType_id+2
							when CCC.CmpCallCardStatusType_id in(5) then CCC.CmpCallCardStatusType_id+3
							else
								case when CCC.Lpu_ppdid is not null
									then 1
									else 2
								end
						end
					else 7
				end as CmpGroup_id
				*/
				-- end select
			from
				-- from
				v_CmpCallCard CCC with (nolock)

				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT
				outer apply(
					select top 1
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_CmpMoveFromNmpReason with (nolock) on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as CCCStatusHist
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU with (nolock) on SLPU.Lpu_id = CCC.Lpu_ppdid
				OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
				) EPL
				/*left join v_EvnPL EPL with (nolock) on 1=1
					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				left join v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id
				left join v_KLRgn RGN with(nolock) on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_CmpCallCardlockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
					and (60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,@curdate)) >0

				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				(case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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

		// echo getDebugSQL($query, $queryParams);
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
	function loadSmpFarmacyRegisterHistory($data) {

		$queryParams = array();
		$where = array();
		$join = "";

		$where[] = 'CFBRH.CmpFarmacyBalance_id = :CmpFarmacyBalance_id';

		$query = "
			SELECT
			--select
				CFBRH.CmpFarmacyBalanceRemoveHistory_id,
				CASE WHEN (ISNULL(D.Drug_Fas,0) = 0) then RTRIM(convert(varchar,isnull(D.DrugTorg_Name,''))+' '+convert(varchar,isnull(D.DrugForm_Name,''))+' '+convert(varchar,isnull(D.Drug_Dose,'')))
					else RTRIM(convert(varchar,isnull(D.DrugTorg_Name,''))+', '+convert(varchar,isnull(D.DrugForm_Name,''))+', '+convert(varchar,isnull(D.Drug_Dose,''))+', №'+CONVERT(varchar,D.Drug_Fas))
				end as DrugTorg_Name,
				ET.EmergencyTeam_Num,
				MP.Person_Fin,
				convert(varchar(20), cast(CFBRH.CmpFarmacyBalanceRemoveHistory_insDT as datetime), 104) as CmpCallCard_prmDate,
				CFBRH.CmpFarmacyBalanceRemoveHistory_DoseCount,
				CFBRH.CmpFarmacyBalanceRemoveHistory_PackCount
			--end select
			FROM
				-- from
				CmpFarmacyBalanceRemoveHistory CFBRH with (nolock)
				left join v_EmergencyTeam ET with (nolock) on (ET.EmergencyTeam_id = CFBRH.EmergencyTeam_id)
				LEFT JOIN v_MedPersonal as MP with (nolock) ON( MP.MedPersonal_id = ET.EmergencyTeam_HeadShift )
				LEFT JOIN v_CmpFarmacyBalance as CFB with (nolock) ON( CFB.CmpFarmacyBalance_id = CFBRH.CmpFarmacyBalance_id )
				LEFT JOIN rls.v_Drug D with (nolock) on (D.Drug_id = CFB.Drug_id)

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
				CFB.CmpFarmacyBalance_id,
				CFBAH_AD.AddDate,
				CFB.Drug_id,
				D.DrugTorg_Name as DDFGT,
				CASE WHEN (ISNULL(D.Drug_Fas,0) = 0) then RTRIM(convert(varchar,isnull(D.DrugTorg_Name,''))+' '+convert(varchar,isnull(D.DrugForm_Name,''))+' '+convert(varchar,isnull(D.Drug_Dose,'')))
					else RTRIM(convert(varchar,isnull(D.DrugTorg_Name,''))+', '+convert(varchar,isnull(D.DrugForm_Name,''))+', '+convert(varchar,isnull(D.Drug_Dose,''))+', №'+CONVERT(varchar,D.Drug_Fas))
				end as DrugTorg_Name,
				D.Drug_PackName,
				D.Drug_Fas,
				CFB.CmpFarmacyBalance_PackRest,
				CFB.CmpFarmacyBalance_DoseRest
			FROM
				v_CmpFarmacyBalance CFB with (nolock)
				outer apply(
					select top 1
						convert(varchar(20), cast(CFBAH.CmpFarmacyBalanceAddHistory_AddDate as datetime), 104) as AddDate
					from
						v_CmpFarmacyBalanceAddHistory CFBAH with(nolock)
					where
						CFB.CmpFarmacyBalance_id = CFBAH.CmpFarmacyBalance_id
					order by CFBAH.CmpFarmacyBalanceAddHistory_AddDate desc
				) as CFBAH_AD
				LEFT JOIN rls.v_Drug D with (nolock) on D.Drug_id = CFB.Drug_id
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
	function loadUnformalizedAddressDirectory($data) {

		$query = "
			SELECT
			-- select
				UAD.UnformalizedAddressDirectory_id,
				UAD.UnformalizedAddressDirectory_Name,
				UAD.UnformalizedAddressDirectory_lat,
				UAD.UnformalizedAddressDirectory_lng,
				UAD.UnformalizedAddressDirectory_Dom,
				UAD.UnformalizedAddressDirectory_Corpus,
				UAD.KLRgn_id,
				UAD.KLSubRgn_id,
				UAD.KLCity_id,
				UAD.KLTown_id,
				UAD.KLStreet_id

				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when UAD.UnformalizedAddressDirectory_Corpus is not null then ', корп.'+UAD.UnformalizedAddressDirectory_Corpus else '' end +
				case when UAD.UnformalizedAddressDirectory_Dom is not null then ', д.'+UAD.UnformalizedAddressDirectory_Dom else '' end as UnformalizedAddressDirectory_Address
			-- end select
			FROM
			-- from
				v_UnformalizedAddressDirectory UAD with (nolock)
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = UAD.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = UAD.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = UAD.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = UAD.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = UAD.KLStreet_id
			-- end from
			WHERE
			-- where
				UAD.Lpu_id = :Lpu_id
			-- end where
			order by
				-- order by
				UAD.UnformalizedAddressDirectory_Name
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
	 * Загрузка комбинированного справочника улиц и неформализованных адресов СМП
	 */
	function loadStreetsAndUnformalizedAddressDirectoryCombo($data) {
		
		$UADFilter = "";
		$where = "";
		
		$params = array();
		$params["Lpu_id"] = $data["Lpu_id"];
		
		
		if (!empty($data['town_id'])) {
			//пример - Пермский край, неформализованный адрес может быть и не привязан к нас пункту
			//$UADFilter .= " and (UAD.KLTown_id = :town_id or UAD.KLSubRgn_id = :town_id or UAD.KLCity_id = :town_id)";
			$params["town_id"] = $data["town_id"];
			$where = "AND KLArea_id = :town_id";
		}

		if (!empty($data['StreetAndUnformalizedAddressDirectory_id'])) {
			$params["StreetAndUnformalizedAddressDirectory_id"] = $data["StreetAndUnformalizedAddressDirectory_id"];
			$UADFilter .= " AND 'UA.'+CAST(UAD.UnformalizedAddressDirectory_id as varchar(20)) = :StreetAndUnformalizedAddressDirectory_id";
			$where .= " AND 'ST.'+CAST([KLStreet].[KLStreet_id] as varchar(20)) = :StreetAndUnformalizedAddressDirectory_id";
		}

		$query = "
			SELECT
				'UA.'+CAST(UAD.UnformalizedAddressDirectory_id as varchar(20)) as StreetAndUnformalizedAddressDirectory_id,
				UAD.UnformalizedAddressDirectory_Name as StreetAndUnformalizedAddressDirectory_Name,
				'СМП' as Socr_Nick,
				CAST(UAD.UnformalizedAddressDirectory_lat as varchar(20)) as lat,
				CAST(UAD.UnformalizedAddressDirectory_lng as varchar(20)) as lng,
				UAD.UnformalizedAddressDirectory_id,
				'' as KLStreet_id
			FROM
				v_UnformalizedAddressDirectory UAD with (nolock)
			where
				UAD.Lpu_id = :Lpu_id
				{$UADFilter}
			UNION ALL
			SELECT " . (empty($where) ? "top 0" : "") . "
				'ST.'+CAST([KLStreet].[KLStreet_id] as varchar(20)) as StreetAndUnformalizedAddressDirectory_id,
            	RTRIM([KLStreet].[KLStreet_Name]) as StreetAndUnformalizedAddressDirectory_Name,
				[KLSocr].[KLSocr_Nick] as [Socr_Nick],
				null as lat,
				null as lng,
				null as UnformalizedAddressDirectory_id,
				[KLStreet].[KLStreet_id]
			from KLStreet with (nolock)
				left join [KLSocr] with (nolock) on [KLSocr].[KLSocr_id] = [KLStreet].[KLSocr_id]
			where
				KLAdr_Actual = 0
				{$where}
		";
		
		//var_dump(getDebugSQL($query, $data)); exit;
		
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function saveUnformalizedAddress($data) {
		$procedure = '';
		if ( (!isset($data['UnformalizedAddressDirectory_id'])) || ($data['UnformalizedAddressDirectory_id'] <= 0) ) {
			$procedure = 'p_UnformalizedAddressDirectory_ins';

			$query = "
				select
				-- select
					count(*)
				-- end select
				from
				-- from
					v_UnformalizedAddressDirectory UAD with (nolock)
				-- end from
				where
				-- where
					UAD.UnformalizedAddressDirectory_Name = :UnformalizedAddressDirectory_Name
				-- end where


			";
			$result_count = $this->db->query(getCountSQLPH($query), $data);
			if (is_object($result_count))
			{
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			}
			if ($count && $count>0) {
				return array(array('success' => false, 'Error_Msg' => 'Неформализованный адрес с таким названием уже существует'));
			}


		} else {
			$procedure = 'p_UnformalizedAddressDirectory_upd';
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)
			set @Res = :UnformalizedAddressDirectory_id;
			exec " . $procedure . "
				@UnformalizedAddressDirectory_id = @Res output,
				@UnformalizedAddressDirectory_Name = :UnformalizedAddressDirectory_Name,
				@UnformalizedAddressDirectory_Dom = :UnformalizedAddressDirectory_Dom,
				@UnformalizedAddressDirectory_lat = :UnformalizedAddressDirectory_lat,
				@UnformalizedAddressDirectory_lng = :UnformalizedAddressDirectory_lng,

				@Lpu_id = :Lpu_id,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@KLStreet_id = :KLStreet_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as UnformalizedAddressDirectory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo(getDebugSQL($query, $data)); exit();
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function deleteUnformalizedAddress($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_UnformalizedAddressDirectory_del
				@UnformalizedAddressDirectory_id = :UnformalizedAddressDirectory_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
	 * default desc
	 */
	function loadCmpIllegalActList($data)
	{
		$query = "
			select
				CIA.CmpIllegalAct_id,
				LPU.Lpu_Nick as Lpu_Nick,
				rtrim(PS.Person_Surname) + ' ' + rtrim(PS.Person_FirName) + ' ' +rtrim(PS.Person_SecName) as Person_FIO,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_BirthDay,
				convert(varchar(10), CIA.CmpIllegalAct_prmDT, 104) as CmpIllegalAct_prmDT,
				CIA.CmpIllegalAct_Comment,

				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end+
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else ''
				end +
				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else
					', '+Street.KLStreet_FullName  end
				end +
				case when CIA.Address_House is not null then ', д.'+CIA.Address_House else '' end +
				case when CIA.Address_Corpus is not null then ', к.'+CIA.Address_Corpus else '' end +
				case when CIA.Address_Flat is not null then ', кв.'+ convert(varchar(3), CIA.Address_Flat) else '' end as Address_Name
			from
				v_CmpIllegalAct as CIA with(nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = CIA.Person_id
				left join v_Lpu LPU with (nolock) on LPU.Lpu_id = CIA.Lpu_id

				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CIA.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CIA.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CIA.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CIA.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CIA.KLStreet_id
				left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id

		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function loadCmpIllegalActForm( $data )
	{
		$query = "
			select
				CIA.CmpIllegalAct_id,
				CIA.Lpu_id,
				rtrim(PS.Person_Surname) + ' ' + rtrim(PS.Person_FirName) + ' ' +rtrim(PS.Person_SecName) as Person_Fio,
				CIA.Person_id,
				CIA.CmpCallCard_id,
				convert(varchar(10), CIA.CmpIllegalAct_prmDT, 104) as CmpIllegalAct_prmDT,
				CIA.CmpIllegalAct_Comment,

				CIA.Address_Zip,
				CIA.KLCountry_id,
				CIA.KLRgn_id,
				CIA.KLSubRGN_id,
				CIA.KLCity_id,
				CIA.KLTown_id,
				CIA.KLStreet_id,
				CIA.Address_House,
				CIA.Address_Corpus,
				CIA.Address_Flat,

				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end+
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else ''
				end +
				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else
					', '+Street.KLStreet_FullName  end
				end +
				case when CIA.Address_House is not null then ', д.'+CIA.Address_House else '' end +
				case when CIA.Address_Corpus is not null then ', к.'+CIA.Address_Corpus else '' end +
				case when CIA.Address_Flat is not null then ', кв.'+ convert(varchar(3), CIA.Address_Flat) else '' end as AddressText
			from
				v_CmpIllegalAct as CIA with(nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = CIA.Person_id
				left join v_Lpu LPU with (nolock) on LPU.Lpu_id = CIA.Lpu_id

				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CIA.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CIA.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CIA.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CIA.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CIA.KLStreet_id
				left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id
			where
				CIA.CmpIllegalAct_id = :CmpIllegalAct_id

		";

		//var_dump(getDebugSQL($query, $data)); exit;
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function saveCmpIllegalActForm($data)
	{
		if(!empty($data["CmpIllegalAct_id"])){
			$procedure = 'p_CmpIllegalAct_upd';
		}
		else{
			$procedure = 'p_CmpIllegalAct_ins';

		}
		$exceptedFields = array('CmpIllegalAct_id');

		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, $exceptedFields, false);
		$queryParams = $genQuery["paramsArray"];
		$queryFields = $genQuery["sqlParams"];

		$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)

				set @Res = :CmpIllegalAct_id;

				exec " . $procedure . "
					{$queryFields}
					@CmpIllegalAct_id = @Res output,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as CmpIllegalAct_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		if(empty($data["CmpIllegalAct_id"])){
			$queryParams["CmpIllegalAct_id"] = null;
		}

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function deleteCmpIllegalAct($data)
	{
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_CmpIllegalAct_del
				@CmpIllegalAct_id = :CmpIllegalAct_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
				'CmpIllegalAct_id' => $data['CmpIllegalAct_id']
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
	function findCmpIllegalAct($data)
	{

		$exceptedFields = array();

		$procedure = "v_CmpIllegalAct";

		$exceptedFields = array('Person_id');

		$genQuery = $this -> getParamsForSQLQuery('p_CmpIllegalAct_upd', $data, $exceptedFields, false);
		$queryParams = $genQuery["paramsArray"];
		$filterSqlParams = $genQuery["filterSqlParams"];
		$filterSqlParams[] = '1=1';
		$fields = '';
		$filter = '';

		if(empty($data['Address_House']) && empty($data['Person_id'])){
			return false;
		}

		if(!empty($data["Person_id"])){
			$queryParams["Person_id"] = $data["Person_id"];

			if(count($filterSqlParams)>0){
				$filter = '(' . implode(" AND ", $filterSqlParams) . ') OR ';
			}
			$filter = 'Person_id = :Person_id';
			$fields .= ',Person_id';
		}
		else{
			if(empty($data['Address_Flat'])){
				$filterSqlParams[] = "Address_Flat is null";
			}
			$filter = implode(" AND ", $filterSqlParams) ;
		}

		$query = "
			select top 1
				convert(varchar(10), CmpIllegalAct_prmDT, 104) as CmpIllegalAct_prmDT,
				CmpIllegalAct_Comment
				{$fields}
				from {$procedure}
				where
				".$filter."
		";

		//var_dump(getDebugSQL($query, $queryParams)); exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}



	/**
	 * default desc
	 */
	function saveSmpFarmacyDrug($data) {
		$procedure = '';
		$checkQuery = "
			SELECT
				CFB.CmpFarmacyBalance_id,
				CFB.CmpFarmacyBalance_PackRest,
				CFB.CmpFarmacyBalance_DoseRest
			FROM
				v_CmpFarmacyBalance CFB with (nolock)
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :CmpFarmacyBalance_id;

			exec " . $procedure . "
				@CmpFarmacyBalance_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Drug_id = :Drug_id,
				@CmpFarmacyBalance_PackRest = :CmpFarmacyBalance_PackRest,
				@CmpFarmacyBalance_DoseRest = :CmpFarmacyBalance_DoseRest,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpFarmacyBalance_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :CmpFarmacyBalanceAddHistory_id;

			exec p_CmpFarmacyBalanceAddHistory_ins
				@CmpFarmacyBalanceAddHistory_id = @Res output,
				@CmpFarmacyBalanceAddHistory_DoseCount = :CmpFarmacyBalanceAddHistory_RashEdCount,
				@CmpFarmacyBalanceAddHistory_PackCount = :CmpFarmacyBalanceAddHistory_RashCount,
				@CmpFarmacyBalanceAddHistory_AddDate = :CmpFarmacyBalanceAddHistory_AddDate,
				@CmpFarmacyBalance_id = :CmpFarmacyBalance_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpFarmacyBalanceAddHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
		$CmpFarmacyQuery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)

				set @Res = :CmpFarmacyBalance_id;

				exec p_CmpFarmacyBalance_upd
					@CmpFarmacyBalance_id = @Res output,
					@Lpu_id = :Lpu_id,
					@Drug_id = :Drug_id,
					@CmpFarmacyBalance_PackRest = :CmpFarmacyBalance_PackRest,
					@CmpFarmacyBalance_DoseRest = :CmpFarmacyBalance_DoseRest,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as CmpFarmacyBalance_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		$CmpFarmacyResult = $this->db->query($CmpFarmacyQuery, $data);

		if ( !is_object($CmpFarmacyResult) ) {
			return false;
		}

		$CmpFarmacyResult = $CmpFarmacyResult->result('array');

		if (strlen($CmpFarmacyResult[0]['Error_Msg'])>0) {
			return false;
		}

		$data['CmpFarmacyBalanceRemoveHistory_id'] = null;
		$CmpFarmacyRemoveHistoryQuery = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :CmpFarmacyBalanceRemoveHistory_id;

			exec p_CmpFarmacyBalanceRemoveHistory_ins
				@CmpFarmacyBalanceRemoveHistory_id = @Res output,
				@CmpFarmacyBalanceRemoveHistory_DoseCount = :CmpFarmacyBalanceRemoveHistory_DoseCount,
				@CmpFarmacyBalanceRemoveHistory_PackCount = :CmpFarmacyBalanceRemoveHistory_PackCount,
				@CmpFarmacyBalance_id = :CmpFarmacyBalance_id,
				@EmergencyTeam_id = :EmergencyTeam_id,
				@CmpCallCard_id = NULL,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpFarmacyBalanceRemoveHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";


		$CmpFarmacyRemoveHistory = $this->db->query($CmpFarmacyRemoveHistoryQuery, $data);

		if ( is_object($CmpFarmacyRemoveHistory) ) {
			return $CmpFarmacyRemoveHistory->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function loadSmpStacDiffDiagJournal($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		// Скрываем вызовы принятые в ППД

		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}
		if (!empty($data['diffDiagView']) && $data['diffDiagView'] != 'false') {
			$filter .= " and isnull(EPS.Diag_pid,0) != isnull(CLC.Diag_id,0) ";
		}

		/*
		if ( !empty($data['LpuBuilding_id']) ) {
			$filter .= " and (CCC.LpuBuilding_id = :LpuBuilding_id)";
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
		} else {
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (!empty($lpuBuilding[0]) && !empty($lpuBuilding[0]["LpuBuilding_id"])) {
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];

				$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
				$operLpuBuilding = $this->CmpCallCard_model4E->getOperDepartament($data);
				if (!empty($operLpuBuilding["LpuBuilding_pid"]) && $operLpuBuilding["LpuBuilding_pid"] == $data['LpuBuilding_id']){
					//опер отдел
					$smpUnitsNested = $this->CmpCallCard_model4E->loadSmpUnitsNested( $data, true );
					if (!empty($smpUnitsNested)) {
						$list = array();
						foreach($smpUnitsNested as $value) {
							$list[] = $value['LpuBuilding_id'];
						}
						$list_str = implode(",", $list);
						$filter .= " and (CCC.LpuBuilding_id is null or CCC.LpuBuilding_id in ($list_str))";
					}
				}
				else{
					//подчиненные подстанции
					$filter .= " and (CCC.LpuBuilding_id is null or CCC.LpuBuilding_id = :LpuBuilding_id)";
					$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
				}
			}
		}
		*/
		$filter .=" and CCC.Lpu_id = :Lpu_id ";
		if ( !empty($data['Lpu_id']) ) {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}else{
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}

		$query = "
			select
				-- select
				CLC.CmpCloseCard_id
				,CCC.CmpCallCard_id
				,CCC.CmpCallCard_Numv
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,convert(varchar(20), cast(CCC.CmpCallCard_HospitalizedTime as datetime), 113) as CmpCallCard_HospitalizedTime
				,CCC.CmpCallCard_Ngod
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,RTRIM(ISNULL(CLD.Diag_Code, '') +' '+ ISNULL(CLD.Diag_Name, '')) as CmpDiag_Name
				,RTRIM(ISNULL(EPSD.Diag_Code, '') +' '+ ISNULL(EPSD.Diag_Name, '')) as StacDiag_Name
				,RTRIM(ISNULL(L.Lpu_Nick,'')+' '+ISNULL(LS.LpuSection_Name,'')) as Stac_Name
				-- end select
			from
				-- from
				v_CmpCallCard CCC with(nolock)
				left join v_EvnPS EPS with(nolock)  on EPS.CmpCallCard_id = CCC.CmpCallCard_id
				left join dbo.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
			
				left join v_Diag EPSD with (nolock) on EPS.Diag_pid = EPSD.Diag_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EPS.LpuSection_id
				left join v_Lpu L with (nolock) on L.Lpu_id = EPS.Lpu_id
				-- end from
			where
				-- where
				" . $filter . "
				and CCC.Lpu_hid is not null
				-- end where
			order by
				CmpCallCard_prmDate
		";

		// echo getDebugSQL($query, $queryParams);
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
	 * @desc Диспетчер направлений
	 */
	function loadSMPHeadDutyWorkPlace($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		//$this->RefuseOnTimeout($data);

		// Скрываем вызовы принятые в ППД
		//$filter .= " and CCC.CmpCallCard_IsReceivedInPPD!=2";


		//$filter .= " and isnull(CCC.CmpCallCard_IsOpen, 1) = 2"; // временно только открытые карты

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
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

		if (!empty($data['begDate'])&&!empty($data['endDate'])&&($data['begDate']==$data['endDate'])&&(!empty($data['hours']))&&$isToday) {

			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= DATEADD(day, -1, :begDate)";
			$queryParams['begDate'] = $data['begDate'];
			$filter .= " and CCC.CmpCallCard_prmDT> DATEADD(hour, CAST(:hours as integer), dbo.tzGetDate())";
			switch ($data['hours']) {
				case '1':
				case '2':
				case '3':
				case '6':
				case '12':
				case '24':
					$queryParams['hours'] = '-'.$data['hours'];
					break;
				default:
					$queryParams['hours'] = '-24';
					break;
			}
		}
		else {
			if ( !empty($data['begDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
				$queryParams['begDate'] = $data['begDate'];
			}

			if ( !empty($data['endDate']) ) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
				$queryParams['endDate'] = $data['endDate'];
			}
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

		// TODO тут придумать чтото с ППД..
		$filter .=" and CCC.Lpu_id = :Lpu_id";
		$queryParams['Lpu_id'] = $data['session']['lpu_id'];

		// Отображаем только вызовы переданные от диспетчера вызовов СМП
		//$filter .= " AND CCC.CmpCallCardStatusType_id IS NOT NULL";

		//старое
		//,RTRIM(ISNULL(CD.CmpDiag_Code, '')) as CmpDiag_Name
		$query = "
			-- variables
			declare @cmp_waiting_ppd_time bigint = (select ISNULL((select top 1 DS.DataStorage_Value FROM DataStorage DS (nolock) where DS.DataStorage_Name = 'cmp_waiting_ppd_time' and DS.Lpu_id = 0), 20));
			-- end variables

			select
				-- select
				 CCC.CmpCallCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then 0 else 1 end as CmpCallCard_isLocked
				,case when ISNULL(CCCLL.CmpCallCardLockList_id,0) = 0 then
					COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				else
					'<img src=\"../img/grid/lock.png\">'+COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '')
				end as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name
				,RTRIM(ISNULL(CLD.Diag_Code, '') +' '+ ISNULL(CLD.Diag_Name, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Code, '')) as StacDiag_Name
				,ET.EmergencyTeam_Num
				,CCC.CmpCallCard_prmDT

				,case when CCC.CmpCallCardStatusType_id = 1
						then CONVERT(varchar(5),DATEADD(mi, @cmp_waiting_ppd_time - DATEDIFF(mi,CCC.CmpCallCard_updDT,dbo.tzGetDate())  ,CONVERT(datetime,0)),108)
						else '00'+':'+'00'
				end as PPD_WaitingTime

				,SLPU.Lpu_Nick as SendLpu_Nick

				,ISNULL( RGN.KLRgn_FullName,'') +
				case when SRGN.KLSubRgn_FullName is not null then ', '+SRGN.KLSubRgn_FullName else ', г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name

				--,EPL.Diag_id as EPLDiag_id


				,case
				when CCC.CmpCallCardStatusType_id = 3 then
					case
						when ISNULL(CCCStatusHist.CmpMoveFromNmpReason_id,0) = 0 then  CCC.CmpCallCardStatus_Comment
						else CCCStatusHist.CmpMoveFromNmpReason_Name
					end
				when CCC.CmpCallCardStatusType_id = 5 then
					CCC.CmpCallCardStatus_Comment
				when CCC.CmpCallCardStatusType_id = 4 then
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when RC.ResultClass_Name is not null then '<br />Результат: '+RC.ResultClass_Name else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				end	as PPDResult
				,convert(varchar(10), cast(ServeDT.ServeDT as datetime), 104) as ServeDT
				,case when CCC.CmpCallCardStatusType_id in(2,3,4) then PMC.PMUser_Name + convert(varchar(10), cast(CCC.CmpCallCard_updDT as datetime), 104) else '' end as PPDUser_Name

				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then 1
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id+1
									WHEN CCC.CmpCallCardStatusType_id=4 THEN 4
									WHEN CCC.CmpCallCardStatusType_id=6 THEN 10
									WHEN CCC.CmpCallCardStatusType_id=3 THEN 8
									WHEN CCC.CmpCallCardStatusType_id=7 THEN 11
									WHEN CCC.CmpCallCardStatusType_id=8 THEN 12
									WHEN CCC.CmpCallCardStatusType_id=16 THEN 13
									WHEN CCC.CmpCallCardStatusType_id=18 THEN 14
									WHEN CCC.CmpCallCardStatusType_id=19 THEN 15
									WHEN CCC.CmpCallCardStatusType_id=20 THEN 16
									ELSE CCC.CmpCallCardStatusType_id+4
								END
							ELSE
								CASE

									WHEN CmpCallCardStatusType_id=4 THEN 7
									WHEN CmpCallCardStatusType_id=3 THEN 8
									WHEN CCC.CmpCallCardStatusType_id=7 THEN 11
									WHEN CCC.CmpCallCardStatusType_id=8 THEN 12
									WHEN CCC.CmpCallCardStatusType_id=16 THEN 13
									WHEN CCC.CmpCallCardStatusType_id=18 THEN 14
									WHEN CCC.CmpCallCardStatusType_id=19 THEN 15
									WHEN CCC.CmpCallCardStatusType_id=20 THEN 16
									ELSE CCC.CmpCallCardStatusType_id+4
								END
							END
					else 9
				end as CmpGroup_id
				,case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
					then
						case
							WHEN CCC.CmpCallCardStatusType_id is NULL then '01'
							WHEN CCC.Lpu_ppdid IS NULL THEN
								CASE
									WHEN CCC.CmpCallCardStatusType_id IN (1, 2) THEN '0' + cast(CCC.CmpCallCardStatusType_id + 1 as varchar)
									WHEN CCC.CmpCallCardStatusType_id = 3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id = 4 THEN '04'
									WHEN CCC.CmpCallCardStatusType_id = 6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id = 7 THEN '11'
									WHEN CCC.CmpCallCardStatusType_id = 8 THEN '12'
									WHEN CCC.CmpCallCardStatusType_id = 16 THEN '13'
									WHEN CCC.CmpCallCardStatusType_id = 18 THEN '14'
									WHEN CCC.CmpCallCardStatusType_id = 19 THEN '15'
									WHEN CCC.CmpCallCardStatusType_id = 20 THEN '16'
									ELSE right('0' + cast(CCC.CmpCallCardStatusType_id + 4 as varchar(2)), 2)
								END
							ELSE
								CASE

									WHEN CCC.CmpCallCardStatusType_id = 4 THEN '07'
									WHEN CCC.CmpCallCardStatusType_id = 3 THEN '08'
									WHEN CCC.CmpCallCardStatusType_id = 6 THEN '10'
									WHEN CCC.CmpCallCardStatusType_id = 7 THEN '11'
									WHEN CCC.CmpCallCardStatusType_id = 8 THEN '12'
									WHEN CCC.CmpCallCardStatusType_id = 16 THEN '13'
									WHEN CCC.CmpCallCardStatusType_id = 18 THEN '14'
									WHEN CCC.CmpCallCardStatusType_id = 19 THEN '15'
									WHEN CCC.CmpCallCardStatusType_id = 20 THEN '16'
									ELSE right('0' + cast(CCC.CmpCallCardStatusType_id + 4 as varchar(2)), 2)
								END
							END
					else '09'
				end as CmpGroupName_id,
				convert(varchar(10), PQ.PersonQuarantine_begDT, 104) as PersonQuarantine_begDT,
				CASE WHEN PQ.PersonQuarantine_id is not null THEN 'true' ELSE 'false' END as PersonQuarantine_IsOn
				-- end select
			from
				-- from
				v_CmpCallCard CCC with (nolock)

				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT
				outer apply(
					select top 1
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id,
						v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_CmpMoveFromNmpReason with (nolock) on v_CmpCallCardStatus.CmpMoveFromNmpReason_id = v_CmpMoveFromNmpReason.CmpMoveFromNmpReason_id
					where
						CmpCallCardStatusType_id = 3 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as CCCStatusHist
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CCC.CmpLpu_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join v_Lpu SLPU with (nolock) on SLPU.Lpu_id = CCC.Lpu_ppdid
				OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
				) EPL
				/*left join v_EvnPL EPL with (nolock) on 1=1
					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				left join v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_CmpCallCardlockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
					and (60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate())) >0

				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				outer apply (
					select top 1 
						PQ.PersonQuarantine_id,
						PQ.PersonQuarantine_begDT
					from v_PersonQuarantine PQ with(nolock)
					where PQ.Person_id = CCC.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) PQ

				-- end from
			where
				-- where
				" . $filter . "
				-- end where
			order by
				-- order by
				(case when isnull(CCC.CmpCallCard_IsOpen, 1) = 2
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

		// echo getDebugSQL($query, $queryParams);
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
	function loadSMPHeadBrigWorkPlace($data) {
		$filter = "(1 = 1)";
		$queryParams = array();

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = $data['Search_SurName'] . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = $data['Search_FirName'] . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = $data['Search_SecName'] . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['CmpLpu_id']) ) {
			$filter .= " and CCC.Lpu_hid = :CmpLpu_id";
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

		if ( !empty($data['session']['medpersonal_id']) ) {
			$filter .= " and ET.EmergencyTeam_HeadShift = :MedPersonal_id";
			$queryParams['MedPersonal_id']=$data['session']['medpersonal_id'];
			if (!empty($data['session']['CurrentEmergencyTeam_id']))
			{
				$filter .= " and ET.EmergencyTeam_id = :EmergencyTeam_id";
				$queryParams['EmergencyTeam_id']=$data['session']['CurrentEmergencyTeam_id'];
			}
		}
		else {
			return false;
		}

		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}
		//Для получения изменений одного талона вызова
		if ( !empty($data['CmpCallCard_id']) ) {
			$filter .= " and CCC.CmpCallCard_id = :CmpCallCard_id";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}

		//старое
		//,RTRIM(ISNULL(CD.CmpDiag_Name, '')) as CmpDiag_Name
		$query = "
			select
				-- select
				 CCC.CmpCallCard_id
				,CLC.CmpCloseCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate

				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then convert(varchar(20), CCT.CmpCallType_Code)+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(ISNULL(CLD.Diag_Code, '') +' '+ ISNULL(CLD.Diag_Name, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Name, '')) as StacDiag_Name
				,CCC.CmpCallCard_prmDT

				,case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
				/* case when RC.ResultClass_Name is not null then '<br />Результат: '+RC.ResultClass_Name else '' end + */
				case when RES.CmpPPDResult_Name is not null then '<br />Результат: '+RES.CmpPPDResult_Name else '' end +
				case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end as PPDResult

				,case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end +

				case when Town.KLTown_FullName is not null then
					case when (City.KLCity_Name is not null) then ', '+LOWER(Town.KLSocr_Nick)+'. '+Town.KLTown_Name else LOWER(Town.KLSocr_Nick)+'. '+Town.KLTown_Name end
				else '' end +

				case when Street.KLStreet_FullName is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Ulic is not null then ', '+ CCC.CmpCallCard_Ulic+'. ' else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Comm is not null then '</br>'+CCC.CmpCallCard_Comm else '' end as Adress_Name

				,LR.LpuRegion_Name
				,EPL.EvnPL_id
				,convert(varchar(20), cast(EPL.EvnPL_setDT as datetime), 113) as EvnPL_setDT
				,case when CCC.CmpCallCardStatusType_id in(2,4) then ToDT.PMUser_Name + convert(varchar(10), cast(ToDT.ToDT as datetime), 104) else '' end as PPDUser_Name
				,CASE WHEN CmpCallCard_IsOpen = 2 AND CCC.CmpCallCardStatusType_id in(6) THEN 2 ELSE 1 END as CmpGroup_id
				,CASE WHEN (CmpCallCardStatusType_id=4) THEN
					CASE WHEN ISNULL(ServeDT.ServeDT,-1)=-1 THEN convert(varchar(20), cast(CmpCallCard_updDT as datetime), 113)
						ELSE convert(varchar(20), cast(ServeDT.ServeDT as datetime), 113)
					END
				END as ServeDT
				,ISNULL(MSF.Person_Fio,'') as MedStaffFact_FIO
				-- end select
			from
				-- from
				v_CmpCallCard CCC with (nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES (nolock) on RES.CmpPPDResult_id = CCC.CmpPPDResult_id

				OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						--and t1.EvnPL_setDate >= cast(CCC.CmpCallCard_prmDT as date)
						and CCC.Lpu_ppdid is not null
				) EPL
				/*left join v_EvnPL EPL with (nolock) on 1=1
					--and CCC.CmpCallCardStatusType_id=4
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null
					--and EvnPL_setDate>=cast(CCC.CmpCallCard_prmDT as date)*/


				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id
				left join {$this->schema}.v_CmpCloseCard CLC with (nolock) on CCC.CmpCallCard_id = CLC.CmpCallCard_id
				left join v_Diag CLD with (nolock) on CLC.Diag_id = CLD.Diag_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID

				LEFT JOIN v_MedPersonal MSF with (nolock) ON( MSF.MedPersonal_id=EPL.MedPersonal_id )

				outer apply(
					select top 1
						EvnPL_setDT as ServeDT
					from
						v_EvnPL with(nolock)
					where
						CmpCallCard_id = CCC.CmpCallCard_id
				) as ServeDT

				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT,
						PU.PMUser_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_PmUser PU with(nolock) on PU.PMUser_id = pmUser_insID
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT



				outer apply(
					select top 1
						LpuRegion_id
					from
						v_LpuRegionStreet with(nolock)
					where
						KLCountry_id = 643 -- Россия
						and KLRGN_id = CCC.KLRgn_id
						and isnull(KLSubRGN_id, '') = isnull(CCC.KLSubRgn_id, '')
						and isnull(KLCity_id, '') = isnull(CCC.KLCity_id, '')
						and isnull(KLTown_id, '') = isnull(CCC.KLTown_id, '')
						and KLStreet_id = CCC.KLStreet_id
				) as LRS

				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = LRS.LpuRegion_id
				"
				.((!empty($data['session']['medpersonal_id']))?
				"left join v_EmergencyTeam ET (nolock) on (CCC.EmergencyTeam_id = ET.EmergencyTeam_id)":"")."

				-- end from
			where
				-- where
				" . $filter . " and CCC.CmpCallCardStatusType_id >0
				-- end where
			order by
				-- order by
				(case when CCC.CmpCallCardStatusType_id = 4 then 3 else CCC.CmpCallCardStatusType_id end)
				,LR.LpuRegion_Name asc
				,CCC.CmpCallCard_prmDT desc
				-- end order by
		";

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
	function loadPPDWorkPlace($data) {
		$filter = "(1 = 1)";
		$queryParams = array();
		//var_dump($data); exit;
		$filter .= " and PPDL.Lpu_id = :Lpu_ppdid";
		$queryParams['Lpu_ppdid'] = $data['Lpu_id'];

		if ( !empty($data['Search_SurName']) ) {
			$filter .= " and ISNULL(PS.Person_Surname, CCC.Person_SurName) like :Person_SurName";
			$queryParams['Person_SurName'] = rtrim($data['Search_SurName']) . '%';
		}

		if ( !empty($data['Search_FirName']) ) {
			$filter .= " and ISNULL(PS.Person_Firname, CCC.Person_FirName) like :Person_FirName";
			$queryParams['Person_FirName'] = rtrim($data['Search_FirName']) . '%';
		}

		if ( !empty($data['Search_SecName']) ) {
			$filter .= " and ISNULL(PS.Person_Secname, CCC.Person_SecName) like :Person_SecName";
			$queryParams['Person_SecName'] = rtrim($data['Search_SecName']) . '%';
		}

		if ( !empty($data['Search_BirthDay']) ) {
			$filter .= " and ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay) = :Person_BirthDay";
			$queryParams['Person_BirthDay'] = $data['Search_BirthDay'];
		}

		if ( !empty($data['CmpLpu_id']) ) {
			$filter .= " and CCC.CmpLpu_id = :CmpLpu_id";
			$queryParams['CmpLpu_id'] = $data['CmpLpu_id'];
		}

		if ( !empty($data['MedService_id']) ) {
			$filter .= " and (MS.MedService_id = :MedService_id or MS.MedService_id is null)";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if ( !empty($data['CmpCallCard_Ngod']) ) {
			$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
			$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
		}

		if ( !empty($data['CmpCallCard_Numv']) ) {
			$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
			$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
		}

		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filter .= " and cast(CCC.CmpCallCard_prmDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}

		// Скрываем вызовы с поводом "Решение старшего врача"
		$reason_array = $this->queryResult("SELECT CmpReason_id FROM v_CmpReason with (nolock) WHERE CmpReason_Code in ('02?', '06?', '09?', '10?', '11?', '12?', '13?', '15?', '16?', '40?','999')", array());

		if ( $reason_array !== false && is_array($reason_array) && count($reason_array) > 0 ) {
			$reasons = array();

			foreach($reason_array as $reason){
				$reasons[] = $reason['CmpReason_id'];
			}

			if ( count($reasons) > 0 ) {
				$filter .= " and ISNULL(CCC.CmpReason_id, 0) NOT IN (" . implode(',', $reasons) . ")";
			}
		}

		//$filter .= " and isnull(CCC.CmpCallCard_IsEmergency, 1) = 2";

		//$filter .= " and CCC.CmpCallCardStatusType_id <> 3"; // кроме возвращенных

		$filter .= " and CCC.CmpCallCardStatusType_id <> 18"; // кроме вызовов на решении старшего врача

		$query = "
			with cc as (
				select
					-- select
					CCC.*,
					PPDL.Lpu_id as PPDL_Lpu_id
				from
					-- from
					v_CmpCallCard CCC with (nolock)
					left join v_MedService MS with(nolock) on MS.MedService_id = CCC.MedService_id
					left join v_Lpu PPDL with(nolock) on PPDL.Lpu_id = isnull(CCC.Lpu_ppdid, MS.Lpu_id)
					left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
					-- end from
				where
					-- where
					" . $filter . "  and CCC.CmpCallCardStatusType_id >0
			)
			select
				-- select
				 CCC.CmpCallCard_id
				,P.Person_id
				,P.Person_IsUnknown
				,PS.PersonEvn_id
				,PS.Server_id
				,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
				,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
				,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
				,CCC.pmUser_insID
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 120) as CmpCallCard_prmDate

				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,convert(varchar(10), ISNULL(CCC.Person_BirthDay, PS.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,CR.CmpReason_Code
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name
				,RTRIM(ISNULL(CD.CmpDiag_Name, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Name, '')) as StacDiag_Name
				,CCC.CmpCallCard_prmDT


				,case when SRGNCity.KLSubRgn_Name is not null then SRGNCity.KLSocr_Nick+' '+SRGNCity.KLSubRgn_Name+', '
				else case when SRGNTown.KLSubRgn_Name is not null then SRGNTown.KLSocr_Nick+' '+SRGNTown.KLSubRgn_Name+', '
				else case when SRGN.KLSubRgn_Name is not null then SRGN.KLSocr_Nick+' '+SRGN.KLSubRgn_Name+', ' else '' end end end+
				case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end+
				case when Town.KLTown_FullName is not null then
					case when City.KLCity_Name is not null then ', ' else '' end
					 +isnull(LOWER(Town.KLSocr_Nick)+'. ','') + Town.KLTown_Name else ''
				end+

				case when Street.KLStreet_FullName is not null then
					case when socrStreet.KLSocr_Nick is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else
					', '+Street.KLStreet_FullName  end
				else case when CCC.CmpCallCard_Ulic is not null then ', '+CmpCallCard_Ulic else '' end
				end +
				+ case when SecondStreet.KLStreet_FullName is not null then
					case when socrSecondStreet.KLSocr_Nick is not null then ', '+LOWER(socrSecondStreet.KLSocr_Nick)+'. '+SecondStreet.KLStreet_Name else
					', '+SecondStreet.KLStreet_FullName end
					else ''
				end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end +
				case when CCC.CmpCallCard_Room is not null then ', ком. '+CCC.CmpCallCard_Room else '' end +
				case when UAD.UnformalizedAddressDirectory_Name is not null then ', Место: '+UAD.UnformalizedAddressDirectory_Name else '' end as Adress_Name

				,LR.LpuRegion_Name
				,EPL.EvnPL_id
				,MedService_id
				,convert(varchar(20), cast(EPL.EvnPL_setDT as datetime), 113) as EvnPL_setDT
				--,case when CCC.CmpCallCardStatusType_id in(2,4) then ToDT.PMUser_Name + convert(varchar(10), cast(ToDT.ToDT as datetime), 104) else '' end as PPDUser_Name
				,ToDT.PMUser_Name + convert(varchar(10), cast(ToDT.ToDT as datetime), 104) + ' ' + convert(varchar(5), cast(ToDT.ToDT as datetime), 108) as PPDUser_Name
				,CASE WHEN ISNULL(CmpCallCard_IsOpen,1)=2 THEN
					/* Записи принятые в ППД */
					CASE
						WHEN CmpCallCard_IsReceivedInPPD=2 THEN
							CASE
								WHEN EvnPL_id IS NOT NULL THEN 6
								WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id+3
								WHEN CCC.CmpCallCardStatusType_id=4 THEN 6
								ELSE 7
							END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_id становится равной ноля */
					ELSE
						CASE
							WHEN EvnPL_id IS NOT NULL THEN 3
							WHEN CCC.CmpCallCardStatusType_id IN(1,2) THEN CCC.CmpCallCardStatusType_id
							WHEN CCC.CmpCallCardStatusType_id=3 THEN 8
							WHEN CCC.CmpCallCardStatusType_id=4 THEN 3
							WHEN CCC.CmpCallCardStatusType_id in (16,18) THEN 9
							WHEN CCC.CmpCallCardStatusType_id=20 THEN 1
							ELSE 7
						END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_id становится равной ноля */

					END
				ELSE 7 END as CmpGroup_id
				,CASE WHEN (CCC.CmpCallCardStatusType_id in (3,5,6,7,8)) THEN
					case when CCC.CmpCallCardStatusType_id=3 then
						case when CCCS.CmpMoveFromNmpReason_id > 0 then 'Отклонено: '+CMFNR.CmpMoveFromNmpReason_Name
							else 'Отклонено: '+CCC.CmpCallCardStatus_Comment
						end
					else '' end +
					case when (ISNULL(CmpCallCard_IsOpen,1)=1 OR (CCC.CmpCallCardStatusType_id in (5,6,7,8))) then
						case when CCCS.CmpMoveFromNmpReason_id > 0 then 'Отказ: '+CMFNR.CmpMoveFromNmpReason_Name
							else 'Отказ: '+CCC.CmpCallCardStatus_Comment end
					else '' end
				ELSE
					case when EPLD.diag_FullName is not null then 'Диагноз: '+EPLD.diag_FullName else '' end +
					case when RES.CmpPPDResult_Name is not null then '<br />Результат: '+RES.CmpPPDResult_Name else '' end +
					case when DT.DirectType_Name is not null then '<br />Направлен: '+DT.DirectType_Name else '' end
				END as PPDResult
				,CASE WHEN (CCC.CmpCallCardStatusType_id=4) THEN
					CASE WHEN ISNULL(EPL.EvnPL_setDT,-1)=-1 THEN convert(varchar(20), cast(CmpCallCard_updDT as datetime), 120)
						ELSE convert(varchar(20), cast(EPL.EvnPL_setDT as datetime), 120)
					END
				END as ServeDT

				,ISNULL(MSF.Person_Fio,'') as MedStaffFact_FIO
				-- end select
			from
				-- from
				cc CCC with (nolock)
				left join v_Person P with (nolock) on P.Person_id = CCC.Person_id
				left join v_PersonState PS with (nolock) on PS.Person_id = P.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES (nolock) on RES.CmpPPDResult_id = CCC.CmpPPDResult_id

				outer apply(
					select top 1
						e1.*,
						e2.Evn_setDT as EvnPL_setDT
                    from
                        EvnPL e1 with (nolock)
                        inner join Evn e2 (nolock) on e2.Evn_id = e1.EvnPL_id and e2.Evn_deleted = 1
                    where
                        e1.CmpCallCard_id = CCC.CmpCallCard_id
				) as EPL

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id

				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id
				left join v_KLStreet SecondStreet (nolock) on SecondStreet.KLStreet_id = CCC.CmpCallCard_UlicSecond
				left join v_KLSocr socrSecondStreet with (nolock) on SecondStreet.KLSocr_id = socrSecondStreet.KLSocr_id
				left join v_KLSubRgn SRGNTown with(nolock) on SRGNTown.KLSubRgn_id = CCC.KLTown_id
				left join v_KLSubRgn SRGNCity with(nolock) on SRGNCity.KLSubRgn_id = CCC.KLCity_id
				left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id

				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID
				left join CmpCallCardStatus CCCS with (nolock) on CCCS.CmpCallCardStatus_id = CCC.CmpCallCardStatus_id

				LEFT JOIN v_MedPersonal MSF with (nolock) ON ( MSF.MedPersonal_id=EPL.MedPersonal_id and MSF.Lpu_id = CCC.Lpu_ppdid)
				left join v_CmpMoveFromNmpReason CMFNR with (nolock) ON CCCS.CmpMoveFromNmpReason_id = CMFNR.CmpMoveFromNmpReason_id

				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT,
						PU.PMUser_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_PmUser PU with(nolock) on PU.PMUser_id = pmUser_insID
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT

				outer apply(
					select top 1
						LpuRegion_id
					from
						v_LpuRegionStreet with(nolock)
					where
						KLCountry_id = 643 -- Россия
						and KLRGN_id = CCC.KLRgn_id
						and isnull(KLSubRGN_id, '') = isnull(CCC.KLSubRgn_id, '')
						and isnull(KLCity_id, '') = isnull(CCC.KLCity_id, '')
						and isnull(KLTown_id, '') = isnull(CCC.KLTown_id, '')
						and KLStreet_id = CCC.KLStreet_id
				) as LRS

				left join v_LpuRegion LR with(nolock) on LR.LpuRegion_id = LRS.LpuRegion_id

				-- end from

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
	 * @desc Получение оперативной обстановки по ЛПУ со службой ППД
	 * @param type $data
	 * @return boolean
	 */
	function loadLpuOperEnv( $data ){

		$filter = "(1 = 1)";
		$queryParams = array();

		$filter .= " AND CCC.Lpu_ppdid = :Lpu_ppdid";
		$queryParams['Lpu_ppdid'] = $data['Lpu_ppdid'];

		// Получаем оперативную обстановку за прошедшие сутки
		$filter .= "
			AND CAST(CCC.CmpCallCard_prmDT as date) >= :begDate
			AND CAST(CCC.CmpCallCard_prmDT as date) <= :endDate
		";
		$queryParams['begDate'] = date('Y-m-d',time()-86400);
		$queryParams['endDate'] = date('Y-m-d');

		// Выводим только необходимые статусы
		$filter .= " AND (
				(CCC.CmpCallCard_IsReceivedInPPD=2 AND CCC.CmpCallCardStatusType_id IN(1,2,4)) /* Приняты из ППД: поступившие, принятые, обслуженые (создан случай АПЛ - пока не проверяется) */
				OR (CCC.CmpCallCard_IsReceivedInPPD!=2 AND CCC.CmpCallCardStatusType_id IN(1,2,4)) /* Переданы из СМП: ожидают, принятые, обслуженные */
			)
		";

		$query = "
			SELECT
				-- select
				 CCC.CmpCallCard_id
				,PS.Person_id
				,PS.PersonEvn_id
				,PS.Server_id
				,CCC.pmUser_insID
				,CONVERT(VARCHAR(20), CAST(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate

				,CCC.CmpCallCard_Numv
				,CCC.CmpCallCard_Ngod
				,COALESCE(PS.Person_Surname, CCC.Person_SurName, '') + ' ' + COALESCE(PS.Person_Firname, case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(PS.Person_Secname, case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name
				,RTRIM(COALESCE(L.Lpu_Nick, replace(replace(CL.CmpLpu_Name, '=', ''), '_+', ' '), '')) as CmpLpu_Name
				,RTRIM(ISNULL(CD.CmpDiag_Name, '')) as CmpDiag_Name
				,RTRIM(ISNULL(D.Diag_Name, '')) as StacDiag_Name
				,CCC.CmpCallCard_prmDT

				,LR.LpuRegion_Name
				,EPL.EvnPL_id
				,convert(varchar(20), cast(ServeDT.ServeDT as datetime), 113) as ServeDT
				,convert(varchar(20), cast(EPL.EvnPL_setDT as datetime), 113) as EvnPL_setDT
				,case when CCC.CmpCallCardStatusType_id in(2,4) then ToDT.PMUser_Name + convert(varchar(10), cast(ToDT.ToDT as datetime), 104) else '' end as PPDUser_Name
				,CASE WHEN ISNULL(CmpCallCard_IsOpen,1)=2 THEN
					/* Записи принятые в ППД */
					CASE WHEN CmpCallCard_IsReceivedInPPD=2 THEN
						CASE WHEN EvnPL_id IS NOT NULL THEN 6
							WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id+3
							WHEN CmpCallCardStatusType_id=4 THEN 6
							ELSE 7 END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_ppdid становится равной ноля */
					ELSE
						CASE WHEN EvnPL_id IS NOT NULL THEN 3
							WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id
							WHEN CmpCallCardStatusType_id=4 THEN 3
							ELSE 7 END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_ppdid становится равной ноля */
					END
				ELSE 7 END as CmpGroup_id
				,ISNULL(MSF.Person_Fio,'') as MedStaffFact_FIO
				-- end select
			FROM
				-- from
				v_CmpCallCard CCC with (nolock)
				left join v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join CmpLpu CL with (nolock) on CL.CmpLpu_id = CCC.CmpLpu_id
				left join v_Lpu L with (nolock) on L.Lpu_id = CL.Lpu_id
				left join CmpDiag CD with (nolock) on CD.CmpDiag_id = CCC.CmpDiag_oid
				left join Diag D with (nolock) on D.Diag_id = CCC.Diag_sid
				left join CmpPPDResult RES (nolock) on RES.CmpPPDResult_id = CCC.CmpPPDResult_id

				OUTER APPLY (
					SELECT TOP 1 *
					FROM v_EvnPL AS t1 with (nolock)
					WHERE t1.CmpCallCard_id = CCC.CmpCallCard_id
						AND t1.Lpu_id = CCC.Lpu_ppdid
						and CCC.Lpu_ppdid is not null
				) EPL
				/*left join v_EvnPL EPL with (nolock) on 1=1
					and EPL.CmpCallCard_id = CCC.CmpCallCard_id
					and EPL.Lpu_id=CCC.Lpu_ppdid
					and CCC.Lpu_ppdid is not null*/

				left join v_Diag EPLD with (nolock) on EPLD.Diag_id = EPL.Diag_id
				left join v_ResultClass RC with (nolock) on RC.ResultClass_id = EPL.ResultClass_id
				left join v_DirectType DT with (nolock) on DT.DirectType_id = EPL.DirectType_id

				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id

				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id

				left join v_pmUserCache PMC with (nolock) on PMC.PMUser_id = CCC.pmUser_updID

				LEFT JOIN v_MedPersonal MSF with (nolock) ON( MSF.MedPersonal_id=EPL.MedPersonal_id )

				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ServeDT
					from
						v_CmpCallCardStatus with(nolock)
					where
						CmpCallCardStatusType_id = 4 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ServeDT
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as ToDT,
						PU.PMUser_Name
					from
						v_CmpCallCardStatus with(nolock)
						left join v_PmUser PU with(nolock) on PU.PMUser_id = pmUser_insID
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as ToDT

				outer apply(
					select top 1
						LpuRegion_id
					from
						v_LpuRegionStreet with(nolock)
					where
						KLCountry_id = 643 -- Россия
						and KLRGN_id = CCC.KLRgn_id
						and isnull(KLSubRGN_id, '') = isnull(CCC.KLSubRgn_id, '')
						and isnull(KLCity_id, '') = isnull(CCC.KLCity_id, '')
						and isnull(KLTown_id, '') = isnull(CCC.KLTown_id, '')
						and KLStreet_id = CCC.KLStreet_id
				) as LRS

				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = LRS.LpuRegion_id
				-- end from
			WHERE
				-- where
				".$filter."
				-- end where
			ORDER BY
				-- order by
				CCC.CmpCallCard_Ngod DESC
				-- end order by
		";

		$countQuery = getCountSQLPH( $query );

		$countResult = $this->db->query( $countQuery, $queryParams );

		if ( !is_object( $countResult ) ) {
			return false;
		}

		$cnt_arr = $countResult->result('array');
		$count = $cnt_arr[0]['cnt'];

		$result = $this->db->query($query, $queryParams);

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
			return false;
		}

		/*
		if ( !is_object( $result ) ) {
			return false;
		}

		$val = $result->result('array');

		return array(
			'data' => $val,
			'totalCount' => $count
		);
		*/
	}
	/**
	 * default desc
	 */
	function checkDuplicateCmpCallCard($data){
		$is_pg = $this->db->dbdriver == 'postgre' ? true : false;

		$filter = '(1 = 1)';
		$queryParams = array();
		$addressResponse = array();
		$personResponse = array();

		//если не заполнен ни город ни нас пункт - не тужимся
		if(!empty($data['KLCity_id']) || !empty($data['KLTown_id'])) {

			if (!empty($data['CmpCallCard_prmDate'])) {
				$queryParams['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDate'];
				if (!empty($data['CmpCallCard_prmTime'])) {
					$queryParams['CmpCallCard_prmDT'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= dateadd(day,-1,:CmpCallCard_prmDT)";
				} else {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) = :CmpCallCard_prmDT";
				}
			}
			if (!empty($data['CmpCallCard_id'])) {
				$filter .= " and (CCC.CmpCallCard_id != :CmpCallCard_id)";
				$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
			}

			if (!empty($data['CmpCallCard_Numv'])) {
				$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
			}

			if (!empty($data['CmpCallCard_Ngod'])) {
				$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
				$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
			}

			if (!empty($data['KLSubRgn_id'])) {
				$filter .= " and CCC.KLSubRgn_id = :KLSubRgn_id";
				$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
			}

			if (!empty($data['KLCity_id'])) {
				$filter .= " and CCC.KLCity_id = :KLCity_id";
				$queryParams['KLCity_id'] = $data['KLCity_id'];
			}

			if (!empty($data['KLTown_id'])) {
				$filter .= " and CCC.KLTown_id = :KLTown_id";
				$queryParams['KLTown_id'] = $data['KLTown_id'];
			}

			if (!empty($data['KLStreet_id'])) {
				$filter .= " and CCC.KLStreet_id = :KLStreet_id";
				$queryParams['KLStreet_id'] = $data['KLStreet_id'];
			}

			if (!empty($data['CmpCallCard_Dom'])) {
				$filter .= " and ((CCC.CmpCallCard_Dom = :CmpCallCard_Dom) or (CCC.CmpCallCard_Dom is null))";
				$queryParams['CmpCallCard_Dom'] = $data['CmpCallCard_Dom'];
			}
			if (!empty($data['CmpCallCard_Kvar'])) {
				//$filter .= " and ((CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar) or (CCC.CmpCallCard_Kvar is null))";
				$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
				$queryParams['CmpCallCard_Kvar'] = $data['CmpCallCard_Kvar'];
			} else {
				$filter .= " and CCC.CmpCallCard_Kvar is null";
			}
			if (!empty($data['CmpCallCard_Podz'])) {
				$filter .= " and ((CCC.CmpCallCard_Podz = :CmpCallCard_Podz) or (CCC.CmpCallCard_Podz is null))";
				$queryParams['CmpCallCard_Podz'] = $data['CmpCallCard_Podz'];
			}
			if (!empty($data['CmpCallCard_Etaj'])) {
				$filter .= " and ((CCC.CmpCallCard_Etaj = :CmpCallCard_Etaj) or (CCC.CmpCallCard_Etaj is null))";
				$queryParams['CmpCallCard_Etaj'] = $data['CmpCallCard_Etaj'];
			}

			$query = "
			SELECT
				CCC.CmpCallCard_id as CallCard_id
				,COALESCE(CCC.Person_SurName, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,CCC.CmpCallCard_Ngod as CmpCallCard_Ngod
				,CCC.CmpCallCard_Numv as CmpCallCard_Numv
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name

				,case when RGN.KLRgn_FullName is not null then RGN.KLRgn_FullName+', ' else '' end +
				case when SRGN.KLSubRgn_FullName is not null then SRGN.KLSubRgn_FullName+', ' else ' г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end as Adress_Name

			from
				-- from
				v_CmpCallCard CCC with (nolock)
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
			where
			" . $filter;

			//var_dump(getDebugSql($query, $queryParams));exit;

			$result = $this->db->query($query, $queryParams);
			$addressResponse = $result->result('array');
		}

		//если не нашли по адресу, ищем по пациенту
		if( count($addressResponse) == 0 ){
			$filter = '(1 = 1)';
			$queryParams = array();
			if ( !empty($data['CmpCallCard_prmDate']) ) {
				$queryParams['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDate'];
				if ( !empty($data['CmpCallCard_prmTime']) ) {
					$queryParams['CmpCallCard_prmDT'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) >= dateadd(day,-1,:CmpCallCard_prmDT)";
				}  else {
					$filter .= " and cast(CCC.CmpCallCard_prmDT as date) = :CmpCallCard_prmDT";
				}
			}
			if ( !empty($data['CmpCallCard_id'])) {
				$filter .= " and (CCC.CmpCallCard_id != :CmpCallCard_id)";
				$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
			}
			if ( !empty($data['Person_SurName']) ) {
				$filter .= " and ((CCC.Person_SurName like :Person_SurName) or (CCC.Person_SurName is null))";
				$queryParams['Person_SurName'] = $data['Person_SurName'] . '%';
			}
			if ( !empty($data['Person_FirName']) ) {
				$filter .= " and ((CCC.Person_FirName like :Person_FirName) or (CCC.Person_FirName is null))";
				$queryParams['Person_FirName'] = $data['Person_FirName'] . '%';
			}
			if ( !empty($data['Person_SecName']) ) {
				$filter .= " and ((CCC.Person_SecName like :Person_SecName) or (CCC.Person_SecName is null))";
				$queryParams['Person_SecName'] = $data['Person_SecName'] . '%';
			}
			if ( !empty($data['Person_BirthDay']) ) {
				$filter .= " and ((CCC.Person_BirthDay = :Person_BirthDay) or (CCC.Person_BirthDay is null))";
				$queryParams['Person_BirthDay'] = $data['Person_BirthDay'];
			}
			if ( !empty($data['Person_Age']) ) {
				$filter .= " and ((CCC.Person_Age = :Person_Age) or (CCC.Person_Age is null))";
				$queryParams['Person_Age'] = $data['Person_Age'];
			}
			if ( !empty($data['Sex_id']) ) {
				$filter .= " and CCC.Sex_id = :Sex_id";
				$queryParams['Sex_id'] = $data['Sex_id'];
			}
			if ( !empty($data['Person_PolisSer']) ) {
				$filter .= " and ((CCC.Person_PolisSer = :Person_PolisSer) or (CCC.Person_PolisSer is null))";
				$queryParams['Person_PolisSer'] = $data['Person_PolisSer'];
			}
			if ( !empty($data['Person_PolisNum']) ) {
				$filter .= " and ((CCC.Person_PolisNum = :Person_PolisNum) or (CCC.Person_PolisNum is null))";
				$queryParams['Person_PolisNum'] = $data['Person_PolisNum'];
			}
			if ( !empty($data['CmpCallCard_Numv']) ) {
				$filter .= " and CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$queryParams['CmpCallCard_Numv'] = $data['CmpCallCard_Numv'];
			}
			if ( !empty($data['CmpCallCard_Ngod']) ) {
				$filter .= " and CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
				$queryParams['CmpCallCard_Ngod'] = $data['CmpCallCard_Ngod'];
			}


			$query = "SELECT
					CCC.CmpCallCard_id as CallCard_id
					,COALESCE(CCC.Person_SurName, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
					,CCC.CmpCallCard_Ngod as CmpCallCard_Ngod
					,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
					,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
					,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name

					,case when RGN.KLRgn_FullName is not null then RGN.KLRgn_FullName+', ' else '' end +
					case when SRGN.KLSubRgn_FullName is not null then SRGN.KLSubRgn_FullName+', ' else ' г.'+City.KLCity_Name end +
					case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
					case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
					case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
					case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
					case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end as Adress_Name

				from
					-- from
					v_CmpCallCard CCC with (nolock)
					left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
					left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
					left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
					left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
					left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
					left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
					left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
				where
				".$filter;


			$result = $this->db->query($query, $queryParams);
			$personResponse = $result->result('array');
		}

		$response = array();

		if($personResponse || $addressResponse) {
			$response = $personResponse ? $personResponse : $addressResponse;
		}

		//var_dump(getDebugSql($query, $queryParams)); exit;
		if ( count($response) > 0 ) {
			return array(
				'data' => $response
			);
		} else {
			return false;
		}

	}

	/**
	 * Загрука комбобокса случаев противоправных действий
	 */
	public function loadIllegalActCmpCards($data){

		$filter = '(1 = 1)';
		$queryParams = array();

		if(empty($data['Person_id']) && empty($data['CmpCallCard_id']) && empty($data['KLCity_id']) && empty($data['KLTown_id']) ){
			return false;
		}

		if (!empty($data['CmpCallCard_id'])) {
			$filter .= " and (CCC.CmpCallCard_id = :CmpCallCard_id)";
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
		}else{
			//если не заполнен ни город ни нас пункт - не тужимся
			if(!empty($data['KLCity_id']) || !empty($data['KLTown_id'])) {
				if (!empty($data['KLSubRgn_id'])) {
					$filter .= " and CCC.KLSubRgn_id = :KLSubRgn_id";
					$queryParams['KLSubRgn_id'] = $data['KLSubRgn_id'];
				}

				if (!empty($data['KLCity_id'])) {
					$filter .= " and CCC.KLCity_id = :KLCity_id";
					$queryParams['KLCity_id'] = $data['KLCity_id'];
				}

				if (!empty($data['KLTown_id'])) {
					$filter .= " and CCC.KLTown_id = :KLTown_id";
					$queryParams['KLTown_id'] = $data['KLTown_id'];
				}

				if (!empty($data['KLStreet_id'])) {
					$filter .= " and CCC.KLStreet_id = :KLStreet_id";
					$queryParams['KLStreet_id'] = $data['KLStreet_id'];
				}

				if (!empty($data['CmpCallCard_Dom'])) {
					$filter .= " and ((CCC.CmpCallCard_Dom = :CmpCallCard_Dom) or (CCC.CmpCallCard_Dom is null))";
					$queryParams['CmpCallCard_Dom'] = $data['CmpCallCard_Dom'];
				}

				if (!empty($data['CmpCallCard_Kvar'])) {
					$filter .= " and CCC.CmpCallCard_Kvar = :CmpCallCard_Kvar";
					$queryParams['CmpCallCard_Kvar'] = $data['CmpCallCard_Kvar'];
				} else {
					$filter .= " and CCC.CmpCallCard_Kvar is null";
				}
			}


			if (!empty($data['Person_id'])) {
				$filter .= " and CCC.Person_id = :Person_id";
				$queryParams['Person_id'] = $data['Person_id'];
			}

			if (!empty($data['CmpCallCard_prmDate'])) {
				$filter .= " and cast(CCC.CmpCallCard_prmDT as date) = :CmpCallCard_prmDate";
				$queryParams['CmpCallCard_prmDate'] = $data['CmpCallCard_prmDate'];
			}
		}

		$query = "
			SELECT
				CCC.CmpCallCard_id as CallCard_id
				,COALESCE(CCC.Person_SurName, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
				,CCC.CmpCallCard_Ngod as CmpCallCard_Ngod
				,CCC.CmpCallCard_Numv as CmpCallCard_Numv
				,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
				,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
				,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name

				,case when RGN.KLRgn_FullName is not null then RGN.KLRgn_FullName+', ' else '' end +
				case when SRGN.KLSubRgn_FullName is not null then SRGN.KLSubRgn_FullName+', ' else ' г.'+City.KLCity_Name end +
				case when Town.KLTown_FullName is not null then ', '+Town.KLTown_FullName else '' end +
				case when Street.KLStreet_FullName is not null then ', ул.'+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end as Adress_Name

			from
				-- from
				v_CmpCallCard CCC with (nolock)
				left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
				left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
			where
			" . $filter;

		//var_dump(getDebugSql($query, $queryParams)); exit;

		$result = $this->db->query($query, $queryParams);
		$result = $result->result('array');

		if ( count($result) > 0 ) {
			return array(
				'data' => $result
			);
		} else {
			return false;
		}
	}

	/**
	 * Поточный ввод талонов вызова
	 */
	public function saveCmpStreamCard( $data, $cccConfig = null) {
		//person инсертится при создании талона
		//собираем поля (которые расходятся по именам) из 110 в карту
		//слева - значение карты, справа 110
		$translateFromCloseCardToCallCardFields = array(
			array('Lpu_id_forUnicNumRequest', 'Lpu_id'),
			array('CmpCallCard_Numv', 'Day_num'),
			array('CmpCallCard_Ngod', 'Year_num'),
			array('CmpCallCard_City', 'City_id'),
			array('CmpCallCard_Ulic', 'CmpCloseCard_Street'),
			array('CmpCallCard_Dom', 'House'),
			array('CmpCallCard_Korp', 'Korpus'),
			array('CmpCallCard_Room', 'Room'),
			array('CmpCallCard_Kvar', 'Office'),
			array('CmpCallCard_Podz', 'Entrance'),
			array('CmpCallCard_Etaj', 'Level'),
			array('CmpCallCard_Kodp', 'CodeEntrance'),
			array('CmpCallCard_Telf', 'Phone'),
			array('CmpReason_id', 'CallPovod_id'),
			array('Person_SurName', 'Fam'),
			array('Person_FirName', 'Name'),
			array('Person_SecName', 'Middle'),
			array('Person_Age', 'Age'),
			array('Person_PolisSer', 'Polis_Ser'),
			array('Person_PolisNum', 'Polis_Num'),
			array('CmpCallCard_Ktov', 'Ktov'),
			array('CmpCallType_id', 'CallType_id'),
			array('KLRgn_id', 'KLRgn_id'),
			array('KLSubRgn_id', 'Area_id'),
			array('KLCity_id', 'City_id'),
			array('KLTown_id', 'Town_id'),
			array('KLStreet_id', 'Street_id'),
			array('MedStaffFact_id', 'MedStaffFact_id', 'MedStaffFact_uid'),
			array('CmpCallCard_prmDT', 'AcceptTime'),
			array('Lpu_hid', 'ComboValue_241'),
			array('Person_BirthDay', 'Person_BirthDay', null),
			array('Diag_uid', 'Diag_id'),
		);

		foreach($translateFromCloseCardToCallCardFields as $fieldName){
			if( isset($data[$fieldName[1]]) && !empty($data[$fieldName[1]]) ){
				$data[$fieldName[0]] = $data[$fieldName[1]];
			}
			elseif( isset($fieldName[2]) && isset($data[$fieldName[2]]) && !empty($data[$fieldName[2]]) ){
				$data[$fieldName[0]] = $data[$fieldName[2]];
			}
		};

		//еще немного правок
		$acceptDate = DateTime::createFromFormat('d.m.Y H:i', $data['AcceptTime']);

		$data['CmpCallCard_IsReceivedInPPD'] = (array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) && $data[ 'CmpCallCard_IsReceivedInPPD' ] == 'on') ? 2 : 1;
		$data['CmpCallCard_prmDate'] = $acceptDate -> format('Y-m-d');
		$data['CmpCallCard_prmTime'] = $acceptDate -> format('H:i');
		$data['CmpCallCard_IsOpen'] = 2;
		$data['CmpCallCardStatusType_id'] = 6;
		$data['CmpCloseCard_Street'] = !empty($data['CmpCallCard_Ulic']) ? $data['CmpCallCard_Ulic'] : null;

		if( (!isset($data['LpuBuilding_id'])) || (empty($data['LpuBuilding_id'])) ){
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingBySessionData($data);
			if (!empty($lpuBuilding[0]) && !empty($lpuBuilding[0]["LpuBuilding_id"])){
				$data['LpuBuilding_id'] = $lpuBuilding[0]["LpuBuilding_id"];
			}
		}

		//существовуют ли номера вызовов за день и за год
		$existenceNums = $this -> existenceNumbersDayYear($data);

		//$existenceNums = ($existenceNums) ? $existenceNums[0] : false;
		if(is_array($existenceNums)){
			// если нет такого номера, то позволим пользователю установить номер вызова введенный вручную
			$data['setDay_num'] = ($existenceNums['existenceNumbersDay'] == 1) ? $existenceNums['nextNumberDay'] : $data['Day_num'];
			$data['setYear_num'] = ($existenceNums['existenceNumbersYear'] == 1) ? $existenceNums['nextNumberYear'] : $data['Year_num'];
		}
		
		//собрали поля
		//var_dump($$data['CmpCallCard_prmDate'] = $acceptDate -> format('Y-m-d');
		//$data['CmpCallCard_prmTime']);
		$this->beginTransaction();

		//$resultCallCard = $this->saveCmpCallCard( $queryParams ) ;

		$data['action'] = 'add';

		$resultCallCard = $this->saveCmpCallCard( $data, $cccConfig ) ;

		//var_dump($resultCallCard); exit;
		if ( isset($resultCallCard) && $resultCallCard[0] &&  $resultCallCard[ 0 ][ 'CmpCallCard_id' ] > 0) {
			$data['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : $resultCallCard['Person_id'];
			$result110 = $this->saveCmpCloseCard110( array_merge( $data , array( 'CmpCallCard_id' => $resultCallCard[ 0 ][ 'CmpCallCard_id' ] ) ) ) ;
			if (!$this->isSuccessful( $result110 )) {
				$this->rollbackTransaction();
				return false;
			}
			$this->commitTransaction();

			$result = array_merge($resultCallCard[0], $result110[0]);
			$result['Person_id'] = $resultCallCard['Person_id'];
			return array($result);

		}

		//var_dump(getDebugSql($query, $queryParams)); exit;
		//$result = $this->db->query( $query, $queryParams );
		/*if ( is_object( $result ) ) {
			$result = $result->result( 'array' );
			if ( $result[ 0 ][ 'CmpCallCard_id' ] > 0 ) {
				$result110 = $this->saveCmpCloseCard110( array_merge( $data , array( 'CmpCallCard_id' => $result[ 0 ][ 'CmpCallCard_id' ] ) ) ) ;
				if (!$this->isSuccessful( $result110 )) {
					$this->rollbackTransaction();
				}
				$this->commitTransaction();
				return array( array_merge( $result[ 0 ], $result110[ 0 ] ) ) ;
			}
		}*/
		$this->rollbackTransaction();

		return false;
	}

	/**
		* Тестовый эксперимент по получению параметров для инсерта в sql запрос
		* inputProcedure - процедура для инсерта
		* params - параметры для вставки
		* exceptedFields исключающие поля (поля не для сохранения)
		* isPostgresql - параметр для конвертации запроса в Postgresql формат
	 	* filterSqlParams - массив для фильтров

		* возвращает список параметров(array/string(Postgresql)), значения параметров в sql (string)
	*/
	private function getParamsForSQLQuery( $inputProcedure, $params, $exceptedFields=null, $isPostgresql=false ){

		$paramsArray = array();
		$sqlParams = "";
		$paramsPosttgress = "";
		$filterSqlParams = array();

		//автоматический сбор полей с процедуры
		$queryFields = $this->db->query("select 'Parameter_name' = name, 'Type' = type_name(user_type_id) from sys.parameters where object_id = object_id('".$inputProcedure."')");
		$allFields = $queryFields->result_array();

		//получаем список всех возможных полей
		foreach ($allFields as $fieldVal)
		{
			$field = ltrim($fieldVal["Parameter_name"], "@");

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
						//$sqlParams .= $p.",\r\n";
					}
					else{
						$sqlParams .= "@".$field." = :".$field.",\r\n";
						$filterSqlParams[] = $field ." = :" . $field."\r\n";
					}
				}
			}
		}

		//список параметров, значения параметров
		return array(
			"paramsArray" => ($isPostgresql)?$paramsPosttgress:$paramsArray,
			"sqlParams" => $sqlParams,
			"filterSqlParams" => $filterSqlParams
		);
	}

	/**
	 * Сохранение формы 110у
	 *
	 * @param array $data
	 * @return array
	 */
	public function saveCmpCloseCard110( $data ){

		//#162427 проверка на корректность вида оплаты и диагноза
		if(getRegionNick() == 'ufa' && !$this->validDiagFinance($data)) {
			throw new Exception('Внимание! Введенный диагноз для данного случая не оплачивается по ОМС. Измените диагноз или вид оплаты.');
		}

		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));

		$action = (!empty($data['action'])) ? $data['action'] : null;

		$oldresult = null;
		$NewCmpCloseCard_id = null;
		/*
		$timeBlockFields = array('TransTime' => 'Время предачи вызова бригаде СМП', 'GoTime' => 'Время выезда на вызов',
			'ArriveTime' => 'Время прибытия на место вызова', 'TransportTime' => 'Время начала транспортировки больного',
			'ToHospitalTime' => 'Время прибытия в медицинскую организацию', 'EndTime' => 'Время окончания вызова',
			'BackTime' => 'Время возвращения на станцию', 'CmpCloseCard_PassTime' => 'Время отзвона');
		$now = $this->getCurrentDT();
		$timeError = false;

		foreach($timeBlockFields as $key => $time){
			if(!empty($data[$key]) && new DateTime($data[$key]) > $now){
				$timeError = $timeBlockFields[$key] . ' не может быть больше текущей даты';
			}
		}
		if($timeError){
			return array(array('success' => false, 'Error_Msg' => $timeError));
		}
		*/
		if( !empty($data['setDay_num']) && !empty($data['setYear_num']) ){
			// значения setDay_num и setYear_num создаются при поточном вводе талонов вызова
			// для сохранения номера вызовов за день и год одинаковыми
			$data['Day_num'] = $data['setDay_num'];
			$data['Year_num'] = $data['setYear_num'];
		}else{
			//существуют ли номера вызовов за день и за год
			$existenceNums = $this -> existenceNumbersDayYear($data);
			if(is_array($existenceNums)){

				// если нет такого номера, то позволим пользователю установить номер вызова введенный вручную
				$data['Day_num'] = ($existenceNums['existenceNumbersDay']) ? $existenceNums['nextNumberDay'] : $data['Day_num'];
				$data['Year_num'] = ($existenceNums['existenceNumbersYear']) ? $existenceNums['nextNumberYear'] : $data['Year_num'];
			}
		}

		if(!empty($data['PayType_Code'])){
			$payTypesql = "select PayType_id from v_PayType where PayType_Code = :PayType_Code";
			$payTypeId = $this->getFirstResultFromQuery($payTypesql, array('PayType_Code' => $data['PayType_Code']));
			if(!empty($payTypeId)){
				$data["PayType_id"] = $payTypeId;
			}
		}

		if ( isset( $data[ 'CmpCloseCard_id' ] ) && $data[ 'CmpCloseCard_id' ] && $action != 'add') {
			$action = 'edit';
			$closeCard = '@CmpCloseCard_id :CmpCloseCard_id';

			$procedure = "{$this->schema}.p_CmpCloseCard_upd";
			$relProcedure = "{$this->schema}.p_CmpCloseCardRel_ins";
		} else {
			$query = "
				SELECT
					CLC.CmpCloseCard_id
				FROM
					{$this->schema}.v_CmpCloseCard CLC with (nolock)
				WHERE
					CLC.CmpCallCard_id = :CmpCallCard_id
			";
			$result = $this->db->query( $query, array(
				'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
			) );
			$retrun = $result->result( 'array' );

			if ( sizeof( $retrun ) && !empty($retrun[ 0 ]) && !empty($retrun[ 0 ][ 'CmpCloseCard_id' ]) ) {
				$data[ 'CmpCloseCard_id' ] = $retrun[ 0 ][ 'CmpCloseCard_id' ];
				$action = 'edit';
				$closeCard = '@CmpCloseCard_id :CmpCloseCard_id';
				$procedure = "{$this->schema}.p_CmpCloseCard_upd";
				$relProcedure = "{$this->schema}.p_CmpCloseCardRel_ins";
			} else {
				$action = 'add';
				$closeCard = '';

				$procedure = "{$this->schema}.p_CmpCloseCard_ins";
				$relProcedure = "{$this->schema}.p_CmpCloseCardRel_ins";
			}
		}

		$UnicNums = ';';
		if ( isset( $data[ 'CmpCloseCard_prmTime' ] ) ) {
			$data[ 'CmpCloseCard_prmDate' ] .= ' '.$data[ 'CmpCloseCard_prmTime' ].':00.000';
		}

		if (empty($data['MedPersonal_id']) && !empty($data['MedStaffFact_id'])) {
			$query = "
				select top 1 MedPersonal_id
				from v_MedStaffFact with(nolock)
				where MedStaffFact_id = :MedStaffFact_id
			";

			$queryParams = array(
				'MedStaffFact_id' => $data['MedStaffFact_id'],
			);

			$data['MedPersonal_id'] = $this->getFirstResultFromQuery($query, $queryParams);
			if (!$data['MedPersonal_id']) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при определении врача'));
			}
		}elseif( $this->regionNick == 'ufa' && empty($data['MedPersonal_id']) && empty($data['MedStaffFact_id'])  && $data['EmergencyTeam_id']){
			$query = "
				SELECT top 1
					COALESCE( msf.MedStaffFact_id, null) as MedStaffFact_id,
					msf.MedPersonal_id
				FROM
					v_EmergencyTeam EMT with (nolock) 
					LEFT JOIN v_MedStaffFact msf on (msf.MedStaffFact_id = EMT.EmergencyTeam_HeadShiftWorkPlace)
				WHERE
					EMT.EmergencyTeam_id = :EmergencyTeam_id
			";
			$resStaffPerson = $this->getFirstRowFromQuery($query, array('EmergencyTeam_id' => $data['EmergencyTeam_id']), true);
			if( $resStaffPerson['MedStaffFact_id'] ) $data['MedStaffFact_id'] = $resStaffPerson['MedStaffFact_id'];
			if( $resStaffPerson['MedPersonal_id'] ) $data['MedPersonal_id'] = $resStaffPerson['MedPersonal_id'];
		}

		//Проверка совпадения МО вызова и МО врача
		if($this->regionNick == 'ufa' && !empty($data['MedStaffFact_id']) && !empty($data['EmergencyTeam_id'])){
			$query = "
				SELECT MF.Lpu_id
				FROM v_MedStaffFact MF
				WHERE MedStaffFact_id = :MedStaffFact_id
			";
			$resStaff = $this->getFirstRowFromQuery($query, array('MedStaffFact_id' => $data['MedStaffFact_id']), true);

			if(!empty($resStaff['Lpu_id']) && $resStaff['Lpu_id'] != $data['Lpu_id']){
				$query = "
				SELECT ET.EmergencyTeam_HeadShiftWorkPlace
				FROM v_EmergencyTeam ET
				WHERE EmergencyTeam_id = :EmergencyTeam_id
			";
				$resStaff = $this->getFirstRowFromQuery($query, array('EmergencyTeam_id' => $data['EmergencyTeam_id']), true);


				if($dolog)$this->textlog->add('change MedStaffFact_id old = ' . $data['MedStaffFact_id'] . ' new = ' . $resStaff['EmergencyTeam_HeadShiftWorkPlace'] . '/ET=' . $data['EmergencyTeam_id']);
				$data['MedStaffFact_id'] = $resStaff['EmergencyTeam_HeadShiftWorkPlace'];
			}

		}
		//переделка автоматического сбора полей с таблицы
		$exceptedFields = array(
			'CmpCloseCard_id',
			'CmpCloseCard_IsPaid'
		);

		//попробуем из Wialon получить киллометраж за время GoTime EndTime
		/* теперь определяется на форме
		if(!empty($data['EmergencyTeam_id']) && !empty($data['GoTime']) && !empty($data['EndTime'])){
			if ( $this->regionNick == 'perm' || $this->regionNick == 'krym') {
				//пока только для Перми и Крыма
				$summaryReportStd = array(
					'EmergencyTeam_id' => $data['EmergencyTeam_id'],
					'GoTime' => DateTime::createFromFormat('d.m.Y H:i', $data['GoTime']),
					'EndTime' => DateTime::createFromFormat('d.m.Y H:i', $data['EndTime'])
				);
				$summary = $this->getTheDistanceInATimeInterval( $summaryReportStd ); 
				// если значение получено, то добавим его в массив значений
				if( $summary ) $data['Kilo'] = $summary;
			}
		}
		*/

		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, $exceptedFields, false);
		$cccQueryParams = $genQuery["paramsArray"];
		$txt = $genQuery["sqlParams"];


		foreach($cccQueryParams as $key => $value) {
			if (!empty($value) && in_array($key, array('AcceptTime', 'ArriveTime', 'BackTime', 'EndTime', 'GoTime', 'ToHospitalTime',
					'TransTime', 'TransportTime', 'CmpCloseCard_TranspEndDT', 'Birthday', 'NextTime','ServiceDT', 'Bad_DT', 'Mensis_DT', 'CmpCloseCard_PassTime' ))) {
				$cccQueryParams[$key] = date('Y-m-d H:i:s', strtotime($value));
			}
			if (!empty($value) && in_array($key, array('Diag_id'))) {
				if (!preg_match('/^[0-9]+$/ui', $value)) {
					return array(array('Error_Msg' => 'Неверный идентификатор в поле Diag_id'));
				}
			}
		}
		if($this->regionNick == 'kz'){
			$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)
				".$UnicNums."
			set @Res = null;
			exec ".$procedure."
				@CmpCloseCard_id = @Res output,
				".$txt."
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		}else{
			$query = "
			declare
				@Res bigint,
				@ResGUID uniqueidentifier,
				@ErrCode int,
				@ErrMessage varchar(4000)
				".$UnicNums."
			set @Res = null;
			set @ResGUID = null;
			exec ".$procedure."
				@CmpCloseCard_id = @Res output,
				@CmpCloseCard_GUID = @ResGUID output,
				".$txt."
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCloseCard_id, @ResGUID as CmpCloseCard_GUID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		}


		if ( $action == 'edit' ) {
			$NewCmpCloseCard_id = null;

			/* Делаем копию исходной записи, а измененную копию сохраняем на место старой */
			/* 1 - выбираем старую запись */

			$squery = "
				SELECT *
				FROM {$this->schema}.v_CmpCloseCard CLC (nolock)
				WHERE CLC.CmpCloseCard_id = ".$data[ 'CmpCloseCard_id' ]."
			";

			$result = $this->db->query( $squery, $data );

			if ( !is_object( $result ) ) {
				return false;
			}
			$oldresult = $result->result( 'array' );
			$oldresult = $oldresult[ 0 ];

			/* 2 - сохраняем страую запись в новую */
			
			//$genQuery = $this -> getParamsForSQLQuery($procedure, $data, $exceptedFields, false);
			$oldresult = array_merge($data, $oldresult);
			$genQuery = $this -> getParamsForSQLQuery($procedure, $oldresult, $exceptedFields, false);
			
			$squeryParams = $genQuery["paramsArray"];
			$txt = $genQuery["sqlParams"];

			$squery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = 0;

				exec {$this->schema}.p_CmpCloseCard_ins
					".$txt."
					@CmpCloseCard_id = @Res output,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				if ( @ErrMessage is null )
					exec {$this->schema}.p_CmpCloseCard_del
						@CmpCloseCard_id = @Res,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					exec {$this->schema}.p_CmpCloseCard_setFirstVersion
						@CmpCloseCard_id = @Res,
						@CmpCloseCard_firstVersion = " . $oldresult['CmpCloseCard_id'] . ",
						@pmUser_id = :pmUser_id;

				select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			/*
			foreach($squeryParams as $key => $value) {
				if (!empty($value) && in_array($key, array('AcceptTime', 'ArriveTime', 'BackTime', 'EndTime', 'GoTime', 'ToHospitalTime', 'TransTime', 'TransportTime', 'CmpCloseCard_TranspEndDT', 'Birthday', 'NextTime','ServiceDT','Bad_DT', 'Mensis_DT' ))) {
					$squeryParams[$key] = date('Y-m-d H:i:s', strtotime($value));
				}
			}
			*/
			$result = $this->db->query( $squery, $squeryParams );

			if ( !is_object( $result ) ) {
				return false;
			}

			$result = $result->result( 'array' );
			$result = $result[ 0 ];

			$NewCmpCloseCard_id = $result[ 'CmpCloseCard_id' ];

			// 3 - заменяем старую запись текущими изменениями*/

			$newParams = $cccQueryParams;
			//$newParams = $queryParams;
			$newParams[ 'CmpCloseCard_id' ] = $oldresult[ 'CmpCloseCard_id' ];

			if ( (!isset( $newParams[ 'CmpCloseCard_id' ] )) || ($newParams[ 'CmpCloseCard_id' ] == null ) ) {
				$newParams[ 'CmpCallCard_id' ] = $oldresult[ 'CmpCallCard_id' ];				
			}
			
			$newParams[ 'CmpCloseCard_GUID' ] = $oldresult[ 'CmpCloseCard_GUID' ];

			$txt = "";
			foreach( $newParams as $q => $p ){
				$txt .= "@".$q." = :".$q.",\r\n";
			}

			$squery = "
				declare
					@Res bigint,
					@ResGUID uniqueidentifier,
					@ErrCode int,
					@ErrMessage varchar(4000);

				set @Res = :CmpCloseCard_id;
				set @ResGUID = :CmpCloseCard_GUID;

				exec {$this->schema}.p_CmpCloseCard_upd
				".$txt."
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCloseCard_id, @ResGUID as CmpCloseCard_GUID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$result = $this->db->query( $squery, $newParams );
			$resArray = $result->result( 'array' );


			//Проверим на дубли карты вызова
			$dupquery = "
				SELECT
					*
				FROM
					{$this->schema}.v_CmpCloseCard CLC with (nolock)
				WHERE
					CLC.CmpCallCard_id = :CmpCallCard_id and CLC.CmpCloseCard_id != :CmpCloseCard_id
			";
			$dupresult = $this->db->query( $dupquery, array(
				'CmpCallCard_id' => $data[ 'CmpCallCard_id' ],
				'CmpCloseCard_id' => $newParams[ 'CmpCloseCard_id' ]
			) );
			$dupArray = $dupresult->result( 'array' );
			if(count($dupArray) > 0){
				foreach($dupArray as $dup){
					$this->deleteCmpCallCard(array(
						'CmpCallCard_id' => $dup["CmpCallCard_id"],
						'CmpCloseCard_id' => $dup["CmpCloseCard_id"],
						'Lpu_id' => $dup['Lpu_id'],
						'pmUser_id' => $data['pmUser_id']
					), false, false);
				}
			}
			/*
			// 4 - устанавливаем значение старого id в перезаписанной записи
			$squery = "
				exec {$this->schema}.p_CmpCloseCard_setFirstVersion
				@CmpCloseCard_id = ".$oldresult[ 'CmpCloseCard_id' ].",
				@CmpCloseCard_firstVersion = ".$NewCmpCloseCard_id.",
				@pmUser_id = ".$data[ 'pmUser_id' ].";
			";

			$this->db->query( $squery );
			*/
			// сохраним номера вызова за год и день в CmpCallCard, чтоб совпадали
			if ( !empty($data['Day_num']) && !empty($data['Year_num']) ){
				$numYearDayQuery = "
					exec p_CmpCallCard_setNgodNumv
					@CmpCallCard_id = " . $data['CmpCallCard_id'] . ",
					@pmUser_id = " . $data['pmUser_id'] . ",
					@CmpCallCard_Numv = ".$data['Day_num'].",
					@CmpCallCard_Ngod = ".$data['Year_num']."
				";
				$resSetYD = $this->db->query($numYearDayQuery);
			}
		} else { // add
			//var_dump(getDebugSQL($query, $cccQueryParams)); exit;
			$result = $this->db->query( $query, $cccQueryParams );

			$resArray = $result->result( 'array' );
		}

		// Связь документа списания медикаментов на пациента и талона закрытия вызова
		if ( isset( $data['DocumentUc_id'] ) && $data['DocumentUc_id'] ) {
			$this->saveCmpCloseCardDocumentUcRel( array_merge( $data, array( 'CmpCloseCard_id' => $resArray[ 0 ][ 'CmpCloseCard_id' ] ) ) );
		}

		if ( isset( $data['CmpEquipment'] ) && $data['CmpEquipment'] ) {
			// Использованное оборудование
			$this->saveCmpCloseCardEquipmentRel( array_merge( $data, array( 'CmpCloseCard_id' => $resArray[ 0 ][ 'CmpCloseCard_id' ] ) ) );
		}

		if (!empty($data['CmpCallCard_id'])) {
			$pars = array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'pmUser_id' => $data['pmUser_id']
			);

			if (!empty($data['AcceptTime'])) {
				$pars['CmpCallCard_prmDT'] = ConvertDateTimeFormat($data['AcceptTime'].':00');
				if($dolog)$this->textlog->add('update CmpCallCard_id '.$data['CmpCallCard_id'] . ' CmpCallCard_prmDT ' . $pars['CmpCallCard_prmDT']);
			}
			if (!empty($data['TransTime'])) {
				$pars['CmpCallCard_Tper'] = ConvertDateTimeFormat($data['TransTime'].':00');
			}
			if (!empty($data['GoTime'])) {
				$pars['CmpCallCard_Vyez'] = ConvertDateTimeFormat($data['GoTime'].':00');
			}
			if (!empty($data['ArriveTime'])) {
				$pars['CmpCallCard_Przd'] = ConvertDateTimeFormat($data['ArriveTime'].':00');
			}
			if (!empty($data['TransportTime'])) {
				$pars['CmpCallCard_Tgsp'] = ConvertDateTimeFormat($data['TransportTime'].':00');
			}
			if (!empty($data['ToHospitalTime'])) {
				$pars['CmpCallCard_HospitalizedTime'] = ConvertDateTimeFormat($data['ToHospitalTime'].':00');
			}
			if (!empty($data['EndTime'])) {
				$pars['CmpCallCard_Tisp'] = ConvertDateTimeFormat($data['EndTime'].':00');
			}
			if (!empty($data['EmergencyTeam_id'])) {
				$pars['EmergencyTeam_id'] = $data['EmergencyTeam_id'];
			}
			if (!empty($data['CmpCloseCard_IsExtra'])) {
				$pars['CmpCallCard_IsExtra'] = $data['CmpCloseCard_IsExtra'];
			}
			//if (!empty($data['CmpCallPlaceType_id'])) {
				//$pars['CmpCallPlaceType_id'] = $data['CmpCallPlaceType_id'];
			//}

			$update_CmpCallCard_prmDT_result = $this->swUpdate('CmpCallCard', $pars, false);

			if (!$this->isSuccessful( $update_CmpCallCard_prmDT_result )) {
				return $update_CmpCallCard_prmDT_result;
			}
		}

		//сохранение person_id в CmpCallCard
		$this -> savePersonToCmpCallCard($data);
		
		//унес сохранение комбос в функцию
		$res = $this->saveCmpCloseCardComboValues($data, $action, $oldresult, $resArray, $NewCmpCloseCard_id, $UnicNums, $relProcedure);

		//установка статуса закрытой карты
		$statusData = array(
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCardStatusType_id' => 6,
			'CmpCallCard_IsOpen' => 1,
			'armtype' => $data['ARMType'],
			'CmpReason_id' => 0,
			'pmUser_id' => $data['pmUser_id']
		);

		$this->setStatusCmpCallCard($statusData);

		return $res;
	}

	/**
	 * Проверка финансируемости диагноза по ОМС для СМП
	 */
	public function validDiagFinance($data) {

		if(empty($data['Diag_id']) || empty($data['PayType_id'])) return true;

		//если тип оплаты не ОМС, то проверять ничего не нужно
		$PayType_id = $this->getFirstResultFromQuery("select top 1 PayType_id from v_PayType with(nolock) where PayType_SysNick = 'oms'");
		if($PayType_id != $data['PayType_id']) return true;

		//проверим финансируемость диагноза по ОМС для СМП

		$params = [ 'Diag_id' => $data['Diag_id'] ];
		$DiagFinance_isOmsSmp = $this->getFirstResultFromQuery('select top 1 DiagFinance_isOmsCmp from v_DiagFinance with(nolock) where Diag_id=:Diag_id', $params);
		return $DiagFinance_isOmsSmp == '2';
	}
	/**
	* сохранение person_id в CmpCallCard
	*
	*/
	protected function savePersonToCmpCallCard($data){

		$selectOldquery = "
			SELECT top 1
				CCC.*,
			 	P.Person_IsUnknown,
			 	PS.PersonEvn_id,
			 	PS.Server_id
			FROM v_CmpCallCard CCC with (nolock)
			left join v_person P on P.Person_id = CCC.Person_id
			left join v_personstate PS on PS.Person_id = P.Person_id
			WHERE CCC.CmpCallCard_id = :CmpCallCard_id";
		$result = $this->db->query($selectOldquery, $data);

		if (!is_object($result)) { return false; }
		
		$result = $result->result('array');

		if( !$result[0] ){ return false; }

		$oldCard = $result[0];

		if ( !empty($data['Person_id']) && $oldCard['Person_id'] != $data['Person_id'] )
		{
			if ( empty($data['Person_IsUnknown']) ) {
				$data['Person_IsUnknown'] = null;
			}





			$personQuery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :CmpCallCard_id;
				exec p_CmpCallCard_setPerson
					@CmpCallCard_id = @Res,
					@Person_id = :Person_id,
					@Person_IsUnknown = :Person_IsUnknown,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				";

			$persRes = $this->db->query($personQuery,$data);

			if( $data['Person_IsUnknown'] != 2 && $oldCard['Person_IsUnknown'] == 2){

				$personRes = $this->db->query("
				select top 1 PersonEvn_id,Server_id
				from v_PersonState with (nolock)
				where Person_id = :Person_id
			", array('Person_id' => $data['Person_id']));
				$personState = $personRes->result('array');

				$evnQuery = "
					SELECT top 1 Evn_id
					FROM v_Evn with (nolock)
					WHERE Person_id = :Person_id
				";

				$evnRes = $this->getFirstRowFromQuery($evnQuery,array('Person_id' => $oldCard['Person_id']));

				$this->load->model('Common_model', 'Common_model');

				if(!empty($evnRes['Evn_id'])){
					//Меняем пациента в докуменах
					$response = $this->Common_model->setAnotherPersonForDocument(array(
						'Person_id'=> $data['Person_id'],
						'PersonEvn_id'=> $personState[0]['PersonEvn_id'],
						'Server_id'=> $personState[0]['Server_id'],
						'pmUser_id'=> $data['pmUser_id'],
						'Evn_id' => $evnRes['Evn_id']
					));

					//и в событиях
					$mergeDataSql = "
						declare
							@ErrCode int,
							@ErrMessage varchar(4000);
						exec pd.xp_PersonMergeData
							@Person_id = :Person_id,
							@Person_did = :Person_did,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output
						select @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
					$result = $this->db->query($mergeDataSql, array(
						'Person_id' => $data['Person_id'],
						'Person_did' => $oldCard['Person_id'],
						'pmUser_id'=> $data['pmUser_id']
					));
				}

				$stacQuery = "
					SELECT top 1 TimeTableStac_id
					FROM v_TimeTableStac_lite with (nolock)
					WHERE Person_id = :Person_id
				";

				$stacRes = $this->getFirstRowFromQuery($stacQuery,array('Person_id' => $oldCard['Person_id']));

				//Обновляем пациента в бирке, если госпитализировали неизвестного
				if(!empty($stacRes['TimeTableStac_id'])){
					$updateParams = array(
						'TimeTableStac_id' => $stacRes['TimeTableStac_id'],
						'Person_id' => $data['Person_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$this->swUpdate('TimeTableStac', $updateParams, false);
				}

				//если был неизвестный человек и пришел идентифицированный
				//то удаляем того неизвестного
				//если у него нет других учетных документов
				$this->load->model( 'Person_model', 'Person_model' );
				$toDel = $this->Person_model->checkToDelPerson(array(
					'Person_id' => $oldCard['Person_id']
				));

				if(empty($toDel['Person_id'])){
					$this->Person_model->deletePerson(array(
						'Person_id' => $oldCard['Person_id'],
						'pmUser_id' => $data['pmUser_id']
					));
				}


			}
		}
	}

	/**
	* Сохранение значений комбо (чеки, радио и инпуты) 110
	*
	*/
	protected function saveCmpCloseCardComboValues($data, $action, $oldresult=null, $resArray, $NewCmpCloseCard_id=null, $UnicNums, $relProcedure){
		$relComboFields = array();
		$queryRelParams = array(
			'CmpCloseCard_id' => ($action == 'add') ? ($resArray[ 0 ][ 'CmpCloseCard_id' ]) : ($oldresult[ 'CmpCloseCard_id' ]),
			'pmUser_id' => $data[ 'pmUser_id' ]
		);
		$relResult = array();

		$comboFields = array();
		foreach( $data as $cName => $cValue ){

			if(strstr($cName, "ComboCheck_")){
				$comboFields[$cName] = $cValue;
			}
		}

		// собираем значения в relComboFields
		foreach( $comboFields as $cName => $cValue ){
			if ( isset( $data[ $cName ] ) ) {
				//Если значений несколько, собираем значения отмеченных
				if ( is_array( $data[ $cName ] ) ) {
					foreach( $data[ $cName ] as $dataField ){
						if((int)$dataField)
						{
							$relComboFields[] = $this->getComboIdByCode($dataField);
						}
					}
				}
				elseif( (int)$data[ $cName ] && ($data[ $cName ] > 0) ) {
					$relComboFields[] = $this->getComboIdByCode($data[ $cName ]);
				}
			}
		}

		//здесь магия сохранения
		if ( $action == 'add' ) {
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
					select @Res as CmpCloseCardRel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";

				$relResult[$relComboField] = $this->db->query($query, $queryRelParams);
			}

			if ( isset($resArray[0]) && $resArray[0]['CmpCloseCard_id']) {

				$query = "
					exec dbo.p_Registry_CMP_storage
					@CmpCloseCard_id = :CmpCloseCard_id,
					@pmUser_id = :pmUser_id;";
				$queryParams = array(
					'CmpCloseCard_id' => $resArray[0]['CmpCloseCard_id'],
					'pmUser_id' => $data['pmUser_id']
				);
				$this->db->query($query, $queryParams);

				//return $resArray;
			}
			//else {
				//return false;
			//}
		} else {
			// action edit
			//заменяем id комбобоксов на свежий
			foreach( $relComboFields as $relComboField ){
				$queryRelParams[ 'relComboField' ] = $relComboField;
				$query = "
					declare
						@pmUser_id bigint,
						@Error_Code int,
						@Error_Message varchar(4000);

					exec {$this->schema}.p_CmpCloseCardRel_updVersion
						@CmpCloseCard_oldId = :CmpCloseCard_id,
						@CmpCloseCard_newId = ".$NewCmpCloseCard_id.",
						@pmUser_id = ".$data[ 'pmUser_id' ]."
				";
				//var_dump(getDebugSQL($query, $queryRelParams)); exit;
				$relResult[$relComboField] = $this->db->query( $query, $queryRelParams );
			}
			
			//записываем новые значения комбиков в стрый id
			foreach( $relComboFields as $relComboField ){
				$queryRelParams[ 'relComboField' ] = $relComboField;
				$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."
					set @Res = 0;
					exec ".$relProcedure."
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = ".$oldresult[ 'CmpCloseCard_id' ].",
						@CmpCloseCardCombo_id = :relComboField,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCardRel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$relResult[$relComboField] = $this->db->query( $query, $queryRelParams );
			}
		}

		//ч2 обработка остальных полей

		//обработка текстовых полей
		$txtFields = array();
		foreach( $data as $cName => $cValue ){
			if(strstr($cName, "ComboValue")){
				$txtFields[$cName] = strval($cValue);
			}
		}
		if ( is_array( $txtFields ) ) {
			$relFieldsResult[] = $this->saveOtherFields($txtFields, $UnicNums, $relProcedure, $queryRelParams, $relResult);
		};

		//обработка комбо
		$cmbFields = array();
		foreach( $data as $cName => $cValue ){
			if(strstr($cName, "ComboCmp")){
				$cmbFields[$cName] = $cValue;
			}
		}

		if ( is_array( $cmbFields ) ) {
			$relFieldsResult[] = $this->saveOtherFields($cmbFields, $UnicNums, $relProcedure, $queryRelParams);
		};

		return $resArray;
	}

	/**
	 * Функция для сохранения разных типов компонентов
	 *
	 * @param type $Fields
	 * @param type $UnicNums
	 * @param type $relProcedure
	 * @param type $queryRelParams
	 * @return type array
	 */
	function saveOtherFields($Fields, $UnicNums, $relProcedure, $queryRelParams, $relResult = null){
		foreach( $Fields as $cName => $cValue ){
			$code = preg_replace("/[^0-9]/", '', $cName) ;
			if ( $cValue != '' ) {
				try {
					$queryRelParams[ 'cKey' ] = $this->getComboIdByCode($code);
					$queryRelParams[ 'cValue' ] = $cValue;

					$relProcedure = "{$this->schema}.p_CmpCloseCardRel_ins";

					$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)
						".$UnicNums."

					set @Res = 0;

					exec ".$relProcedure."
						@CmpCloseCardRel_id = @Res output,
						@CmpCloseCard_id = :CmpCloseCard_id,
						@CmpCloseCardCombo_id = :cKey,
						@Localize = :cValue,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @Res as CmpCloseCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";

					//m.sysolin: если текстовое поле привязано к компоненту,
					//то обновляем а не инсертим, иначе создается дубликат поля
                    //не отрабатывает сохранение - разобраться на досуге
					if ($relResult) {
						foreach ($relResult as $relComboFieldKey => $relResultOutput) {
							//если у компонента есть поле со значением							
							if ($relComboFieldKey == $queryRelParams['cKey'])
							{
								//получаем результат выполнения из хранимки выше (CmpCloseCardRel_id)
								//и обновляем компонент
								$x = $relResultOutput->result();
								$queryRelParams['CmpCloseCardRel_id'] = $x[0]->CmpCloseCardRel_id;
								$relProcedure = "{$this->schema}.p_CmpCloseCardRel_upd";
								$query = "
									declare
										@Res bigint,
										@ErrCode int,
										@ErrMessage varchar(4000);

									set @Res = :CmpCloseCardRel_id;

									exec " . $relProcedure . "
										@CmpCloseCardRel_id = @Res output,
										@CmpCloseCard_id =  :CmpCloseCard_id,
										@CmpCloseCardCombo_id = :cKey,
										@Localize = :cValue,
										@pmUser_id = :pmUser_id,
										@Error_Code = @ErrCode output,
										@Error_Message = @ErrMessage output;
									select @Res as CmpCloseCardRel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;";
							}
						}
					}
					$this->db->query($query, $queryRelParams);
				}
				catch (Exception $e) {
					return array(array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'При сохранении произошла ошибка'));
				}
			}
		}
	}

	/**
	 * Сохранение списка использованного оборудования
	 *
	 * @param array $data
	 * @todo Надо подумать, что возвращать bool или сообщения с ошибками или идентификаторы
	 * @return void
	 */
	public function saveCmpCloseCardEquipmentRel( $data ){
		if ( !isset( $data['CmpEquipment'] ) || !sizeof( $data['CmpEquipment'] ) ) {
			return;
		}

		// Очищаем старые связи перед сохранением
		$sql = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_CmpCloseCardEquipmentRel_delByCmpCloseCardId
				@CmpCloseCard_id = :CmpCloseCard_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$this->db->query( $sql, array( 'CmpCloseCard_id' => $data[ 'CmpCloseCard_id' ] ) );
		//var_dump($data['CmpEquipment']); exit;
		// Сохраняем связи
		foreach( $data['CmpEquipment'] as $CmpEquipment_id => $item ){
			//if ( empty( $item[ 'UsedOnSpotCnt' ] ) && empty( $item[ 'UsedInCarCnt' ] ) ) {
				//continue;
			//}

			$sql = "
				DECLARE
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000)

				SET @Res = null;

				EXEC p_CmpCloseCardEquipmentRel_ins
					@CmpCloseCardEquipmentRel_id = @Res output,
					@CmpCloseCard_id = :CmpCloseCard_id,
					@CmpEquipment_id = :CmpEquipment_id,
					@CmpCloseCardEquipmentRel_UsedOnSpotCnt = :CmpCloseCardEquipmentRel_UsedOnSpotCnt,
					@CmpCloseCardEquipmentRel_UsedInCarCnt = :CmpCloseCardEquipmentRel_UsedInCarCnt,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				SELECT @Res as CmpCloseCardEquipmentRel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$this->db->query( $sql, array(
				'CmpCloseCard_id' => $data['CmpCloseCard_id'],
				'CmpEquipment_id' => $CmpEquipment_id,
				'CmpCloseCardEquipmentRel_UsedOnSpotCnt' => $item['UsedOnSpotCnt'],
				'CmpCloseCardEquipmentRel_UsedInCarCnt' => $item['UsedInCarCnt'],
				'pmUser_id' => $data['pmUser_id'],
			) );
		}

		return;
	}

	/**
	 * Возвращает использованное оборудование для указанной карты закрытия вызова
	 *
	 * @param array $data 'CmpCallCard_id'
	 * @return boolean
	 */
	public function loadCmpCloseCardEquipmentViewForm( $data ) {
		// @todo Переделать если возможно использовать CmpCloseCard_id
		if ( !isset( $data[ 'CmpCloseCard_id' ] ) ) {
			return false;
		}

		$sql = "
			SELECT
				CCCER.CmpCloseCardEquipmentRel_id,
				CCCER.CmpEquipment_id,
				CCCER.CmpCloseCardEquipmentRel_UsedOnSpotCnt,
				CCCER.CmpCloseCardEquipmentRel_UsedInCarCnt
			FROM
				v_CmpCloseCardEquipmentRel as CCCER with (nolock)
			WHERE
				CCCER.CmpCloseCard_id=:CmpCloseCard_id
			";
		$query = $this->db->query( $sql, array(
			'CmpCloseCard_id' => $data['CmpCloseCard_id'],
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * Возвращает использованное оборудование для указанной карты закрытия вызова
	 *
	 * @param array $data 'CmpCallCard_id'
	 * @return boolean
	 */
	public function loadCmpCloseCardEquipmentPrintForm( $data ) {
		// @todo Переделать если возможно использовать CmpCloseCard_id
		if ( !isset( $data[ 'CmpCloseCard_id' ] ) ) {
			return false;
		}

		$sql = "
			SELECT
				CCCER.CmpCloseCardEquipmentRel_id,
				CCCER.CmpEquipment_id,
				CCCER.CmpCloseCardEquipmentRel_UsedOnSpotCnt,
				CCCER.CmpCloseCardEquipmentRel_UsedInCarCnt,
				CE.CmpEquipment_Name
			FROM
				v_CmpCloseCardEquipmentRel as CCCER with (nolock)
				LEFT JOIN v_CmpEquipment as CE with (nolock) ON( CE.CmpEquipment_id=CCCER.CmpEquipment_id )
			WHERE
				CCCER.CmpCloseCard_id=:CmpCloseCard_id
			";
		$query = $this->db->query( $sql, array(
			'CmpCloseCard_id' => $data['CmpCloseCard_id'],
		) );
		if ( is_object( $query ) ) {
			return $query->result_array();
		}

		return false;
	}

	/**
	 * Сохранение связи документа списания медикаментов на пациента и талона закрытия вызова
	 *
	 * @param array $data
	 * @todo Надо подумать, что возвращать bool или сообщения с ошибками или идентификаторы
	 * @return void
	 */
	public function saveCmpCloseCardDocumentUcRel( $data ){
		// Проверим были ли уже связаны данные
		$sql = "
			SELECT
				CmpCloseCardDocumentUcRel_id,
				NULL as Error_Code,
				NULL as Error_Msg
			FROM
				v_CmpCloseCardDocumentUcRel with (nolock)
			WHERE
				CmpCloseCard_id = :CmpCloseCard_id
				AND DocumentUc_id = :DocumentUc_id
		";
		$query = $this->db->query( $sql, array(
			'CmpCloseCard_id' => $data[ 'CmpCloseCard_id' ],
			'DocumentUc_id' => $data[ 'DocumentUc_id' ],
		) );
		if ( $query->num_rows() ) {
			//return $query->first_row();
			return;
		}

		$sql = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			SET @Res = null;

			EXEC p_CmpCloseCardDocumentUcRel_ins
				@CmpCloseCardDocumentUcRel_id = @Res output,
				@CmpCloseCard_id = :CmpCloseCard_id,
				@DocumentUc_id = :DocumentUc_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			SELECT @Res as CmpCloseCardDocumentUcRel_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$this->db->query( $sql, array(
			'CmpCloseCard_id' => $data['CmpCloseCard_id'],
			'DocumentUc_id' => $data['DocumentUc_id'],
			'pmUser_id' => $data['pmUser_id'],
		) );
	}

	/**
	 * @deprecated
	 */
	function saveCmpCallCloseCard($data) {
		$procedure = 'p_CmpCallCard_setCardUpd';
		$UnicNums = ';';
		if ( isset($data['CmpCallCard_prmTime']) ) {
			$data['CmpCallCard_prmDate'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
		}

		//var_dump($data['CmpArea_pid']); exit;
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
				@CmpCallCard_Numv = :CmpCallCard_Numv,
				@CmpCallCard_Ngod = :CmpCallCard_Ngod,
				@CmpCallCard_Prty = :CmpCallCard_Prty,
				@CmpCallCard_Sect = :CmpCallCard_Sect,
				@CmpArea_id = :CmpArea_id,
				@CmpCallCard_City = :CmpCallCard_City,
				@CmpCallCard_Ulic = :CmpCallCard_Ulic,
				@CmpCallCard_Dom = :CmpCallCard_Dom,
				@CmpCallCard_Kvar = :CmpCallCard_Kvar,
				@CmpCallCard_Podz = :CmpCallCard_Podz,
				@CmpCallCard_Etaj = :CmpCallCard_Etaj,
				@CmpCallCard_Kodp = :CmpCallCard_Kodp,
				@CmpCallCard_Telf = :CmpCallCard_Telf,
				@CmpPlace_id = :CmpPlace_id,
				@CmpCallCard_Comm = :CmpCallCard_Comm,
				@CmpReason_id = :CmpReason_id,
				@CmpReasonNew_id = :CmpReasonNew_id,
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
				@CmpCallType_id = :CmpCallType_id,
				@CmpProfile_cid = :CmpProfile_cid,
				@CmpCallCard_Smpt = :CmpCallCard_Smpt,
				@CmpCallCard_Stan = :CmpCallCard_Stan,
				@CmpCallCard_prmDT = :CmpCallCard_prmDT,
				@CmpCallCard_Line = :CmpCallCard_Line,
				@CmpResult_id = :CmpResult_id,
				@LeaveType_id=:LeaveType_id,
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

				@LpuBuilding_id = :LpuBuilding_id,

				@Lpu_id = :Lpu_id,
				@Lpu_ppdid = :Lpu_ppdid,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
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
			'CmpCallCard_Kvar' => $data['CmpCallCard_Kvar'],
			'CmpCallCard_Podz' => $data['CmpCallCard_Podz'],
			'CmpCallCard_Etaj' => $data['CmpCallCard_Etaj'],
			'CmpCallCard_Kodp' => $data['CmpCallCard_Kodp'],
			'CmpCallCard_Telf' => $data['CmpCallCard_Telf'],
			'CmpPlace_id' => $data['CmpPlace_id'],
			'CmpCallCard_Comm' => $data['CmpCallCard_Comm'],
			'CmpReason_id' => $data['CmpReason_id'],
			'CmpReasonNew_id' => $data['CmpReasonNew_id'],
			'Person_id' => $data['Person_id'],
			'Person_Surname' => $data['Person_Surname'],
			'Person_Firname' => $data['Person_Firname'],
			'Person_Secname' => $data['Person_Secname'],
			'Person_Age' => $data['Person_Age'],
			'Person_Birthday' => $data['Person_Birthday'],
			'Person_PolisSer' => $data['Polis_Ser'],
			'Person_PolisNum' => $data['Polis_Num'],
			'Sex_id' => $data['Sex_id'],
			'CmpCallCard_Ktov' => $data['CmpCallCard_Ktov'],
			'CmpCallType_id' => $data['CmpCallType_id'],
			'CmpProfile_cid' => $data['CmpProfile_cid'],
			'CmpCallCard_Smpt' => $data['CmpCallCard_Smpt'],
			'CmpCallCard_Stan' => $data['CmpCallCard_Stan'],
			'CmpCallCard_prmDT' => $data['CmpCallCard_prmDate'],
			'CmpCallCard_Line' => $data['CmpCallCard_Line'],
			'CmpResult_id' => $data['CmpResult_id'],
			'ResultDeseaseType_id' => $data['ResultDeseaseType_id'],
			'LeaveType_id' => $data['LeaveType_id'],
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
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_ppdid' => $data['Lpu_ppdid'],
			'LpuBuilding_id' => (!empty($data['LpuBuilding_id']))?$data['LpuBuilding_id']:null,
			'CmpCallCard_IsOpen' => $data['CmpCallCard_IsOpen'],
			'CmpCallCard_IsReceivedInPPD' =>  array_key_exists( 'CmpCallCard_IsReceivedInPPD', $data ) ? $data['CmpCallCard_IsReceivedInPPD'] : 1,
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);
		$resultforstatus = array();
		$resultforstatus = $result->result('array');
		if ( is_object($result) ) {
			if (($data['ARMType']=='smpreg')||($data['ARMType']=='smpdispatchdirect')) {
				$resultforstatus = array();
				$resultforstatus = $result->result('array');
				$data['CmpCallCard_id'] = $resultforstatus[0]['CmpCallCard_id'];
				$data['CmpCallCardStatusType_id'] = 1;
				$data['CmpCallCardStatus_Comment'] = '';
				$this->setStatusCmpCallCard($data);
			}
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * default desc
	 */
	function saveCmpCallCard($data, $cccConfig = null){
		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));
		$data['CmpCallCard_prmDT'] = date( 'Y-m-d H:i',  strtotime($data['CmpCallCard_prmDate'] . ' ' . $data['CmpCallCard_prmTime']) ).':00';
		$CmpCallCard_prmDT = DateTime::createFromFormat('Y-m-d H:i', $data['CmpCallCard_prmDate'] . ' ' . $data['CmpCallCard_prmTime']);
		
		//при неизвестном пациенте сохраняем неизвестного и вставляет новый ид в талон
		//перенес из контроллера
		//не сохранять неизвестного из карт с признаком АДИС #108064
		if (empty($data['CmpCallCardInputType_id'])){

			$Person_id = $this->checkUnknownPerson($data);

			if ($Person_id) {
				$data['Person_IsUnknown'] = 2;
				$data['Person_id'] = ($Person_id !== true) ? $Person_id : null;
			}
		}
		//если карта вызова пришла сохраняться из поточного ввода то тип вызова берем из параметров
		if (!empty($data['ComboCheck_CallPlace_id']) && $data['ComboCheck_CallPlace_id'] > 0) {
			$CallPlace_Code = null;
			if ($data['ComboCheck_CallPlace_id'] == '180') $CallPlace_Code = '2';
			if ($data['ComboCheck_CallPlace_id'] == '181') $CallPlace_Code = '1';
			if ($data['ComboCheck_CallPlace_id'] == '182') $CallPlace_Code = '4';
			if ($data['ComboCheck_CallPlace_id'] == '183') $CallPlace_Code = '3';
			if ($data['ComboCheck_CallPlace_id'] == '184') $CallPlace_Code = '6';
			if ($data['ComboCheck_CallPlace_id'] == '185') $CallPlace_Code = '6';
			if ($data['ComboCheck_CallPlace_id'] == '186') $CallPlace_Code = '6';
			if ($data['ComboCheck_CallPlace_id'] == '187') $CallPlace_Code = '6';
			if ($data['ComboCheck_CallPlace_id'] == '188') $CallPlace_Code = '10';
			if ($data['ComboCheck_CallPlace_id'] == '189') $CallPlace_Code = '11';
			if ($data['ComboCheck_CallPlace_id'] == '190') $CallPlace_Code = '8';
			if ($data['ComboCheck_CallPlace_id'] == '191') $CallPlace_Code = '9';
			
			if(!empty($CallPlace_Code)){
				$CallPlacesql = "select CmpCallPlaceType_id from v_CmpCallPlaceType where CmpCallPlaceType_Code = :CmpCallPlaceType_Code";
				$CallPlaceparams = array();
				$CallPlaceparams["CmpCallPlaceType_Code"] = $CallPlace_Code;
				$CallPlaceId = $this->getFirstResultFromQuery($CallPlacesql, $CallPlaceparams);
				if(!empty($CallPlaceId)){
					$data['CmpCallPlaceType_id'] = $CallPlaceId;
				}
			}
		}

		if(getRegionNick() != 'krym'){

			/* определяем степень срочности */
			$Ufilter = 'CCCUAPS.Lpu_id = :Lpu_id';
			$UqueryParams = array('Lpu_id' => $data['Lpu_id']);

			if ( !empty( $data[ 'CmpReason_id' ] ) ) {
				$Ufilter .= " and CCCUAPS.CmpReason_id = :CmpReason_id";
				$UqueryParams[ 'CmpReason_id' ] = $data[ 'CmpReason_id' ];
			}
			if ( !empty( $data[ 'Person_Age' ] ) ) {
				$Ufilter .= " and ((CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf > :Person_Age) or (CCCUAPS.CmpUrgencyAndProfileStandart_UntilAgeOf is null))";
				$UqueryParams[ 'Person_Age' ] = $data[ 'Person_Age' ];
			}
			if ( !empty( $data[ 'CmpCallPlaceType_id' ] ) ) {
				$Ufilter .= " and CUAPSRP.CmpCallPlaceType_id = :CmpCallPlaceType_id";
				$UqueryParams[ 'CmpCallPlaceType_id' ] = $data[ 'CmpCallPlaceType_id' ];
			} else {
				$Ufilter .= " and CUAPSRP.CmpCallPlaceType_id is null";
			}

			$Uquery = "
					SELECT
						*
					FROM
						v_CmpUrgencyAndProfileStandart as CCCUAPS with(nolock)
						LEFT JOIN v_CmpUrgencyAndProfileStandartRefPlace CUAPSRP with(nolock) on CUAPSRP.CmpUrgencyAndProfileStandart_id = CCCUAPS.CmpUrgencyAndProfileStandart_id
					WHERE
						$Ufilter
				";

			$Uresult = $this->db->query( $Uquery, $UqueryParams );

			if ( is_object( $Uresult ) ) {
				$res = $Uresult->result( 'array' );
				if ( isset( $res[ 0 ][ 'CmpUrgencyAndProfileStandart_Urgency' ] ) ) {
					$urgency = $res[ 0 ][ 'CmpUrgencyAndProfileStandart_Urgency' ];
					if ( isset( $urgency ) && $urgency > 0 ) {
						$data[ 'CmpCallCard_Urgency' ] = $urgency;
					}
				}
			} else {
				return false;
			}
			/* определили срочность */
		}
		$this->db->trans_begin();
		//проверка на совпадение номера за год и номера за день
		if( !empty($data['setDay_num']) && !empty($data['setYear_num']) ){
			// если устанавливаем значения введенные пользователем
			$data["CmpCallCard_Numv"] = $data['setDay_num'];
			$data["CmpCallCard_Ngod"] = $data['setYear_num'];
		}else{
			if(empty($data["CmpCallCard_Numv"]) || empty($data["CmpCallCard_Ngod"])){
				$newNumValues = $this->getCmpCallCardNumber($data);
				if(!empty($newNumValues[0])){
					$data['CmpCallCard_Numv'] = $newNumValues[0]["CmpCallCard_Numv"];
					$data['CmpCallCard_Ngod'] = $newNumValues[0]["CmpCallCard_Ngod"];
				}else{
					return array( array( 'Error_Msg' => 'Ошибка при определении номера вызова' ) );
				}
			}else{
				//#119325 (Регион:Пенза)
				//Контроль уникальности номера вызова не осуществляется

				$data['Day_num'] = $data["CmpCallCard_Numv"];
				$data['Year_num'] = $data["CmpCallCard_Ngod"];
				$data['AcceptTime'] = $data["CmpCallCard_prmDate"];
				$nums = $this->existenceNumbersDayYear($data);
				if(is_array($nums)){
					$data["CmpCallCard_Numv"] = $nums["nextNumberDay"];
					$data["CmpCallCard_Ngod"] = $nums["nextNumberYear"];
				}else{
					return array( array( 'Error_Msg' => 'Ошибка при определении номера вызова' ) );
				}
			}
		}
		if(!empty($cccConfig['CmpCallCard_Numv']) && !empty($cccConfig['CmpCallCard_Ngod'])){
			$data["CmpCallCard_Numv"] = $cccConfig["CmpCallCard_Numv"];
			$data["CmpCallCard_Ngod"] = $cccConfig["CmpCallCard_Ngod"];
		}

		//if(!empty($data["CmpCallCard_id"])){
		if( !empty($data['action']) && $data['action'] == 'edit' && !empty($data["CmpCallCard_id"]) ){
			
			//редактирование
			$procedure = 'p_CmpCallCard_upd';

			//проверка на блокировку карты
			$checkLock = $this->checkLockCmpCallCard($data);
			if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
				return array( array( 'Error_Msg' => 'Невозможно сохранить. Карта вызова редактируется другим пользователем' ) );
			}

			// Если случай закрыт и задана дата справки, то сохраняем справку.
			if (!empty($data['CmpResult_id']) && !empty($data['CmpCallCardCostPrint_setDT']))
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
			$exceptedFields = array();
		}
		else{
			//добавление
			$procedure = 'p_CmpCallCard_ins';


			/* не используется
			if ($data['ARMType']=='smpadmin') {
				$CmpCallCard_Numv = ':CmpCallCard_Numv';
				$CmpCallCard_Ngod = ':CmpCallCard_Ngod';
			} else {
				$CmpCallCard_Numv = '@UnicCmpCallCard_Numv';
				$CmpCallCard_Ngod = '@UnicCmpCallCard_Ngod';
			}
			*/
			$exceptedFields = array(
				'CmpCallCard_id'
			);
		}
		if(!empty($data["CmpCallCard_id"])) {
			$selectOldquery = "SELECT * FROM v_CmpCallCard CCC with (nolock) WHERE CCC.CmpCallCard_id = :CmpCallCard_id";
			$result = $this->db->query($selectOldquery, $data);
			if (!is_object($result)) {
				return false;
			}
			$oldCard = $result->row_array('array');
			if(empty($oldCard['CmpCallCard_id'])){
				$procedure = 'p_CmpCallCard_ins';
				$exceptedFields = array(
					'CmpCallCard_id'
				);
			}

		}
		//автоматический сбор полей с таблицы

		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, $exceptedFields, false);
		$cccQueryParams = $genQuery["paramsArray"];
		$cccQueryFields = $genQuery["sqlParams"];

		foreach($cccQueryParams as $key => $value) {
			if (!empty($value) && in_array( $key, array(
				'CmpCallCard_Tper', 'CmpCallCard_Vyez', 'CmpCallCard_Przd', 'CmpCallCard_Tgsp', 'CmpCallCard_Tsta',
				'CmpCallCard_Tisp', 'CmpCallCard_Tvzv', 'CmpCallCard_Tiz1'
				)
			)) {

				$parsed = DateTime::createFromFormat('Y-m-d H:i', $data['CmpCallCard_prmDate'] . ' ' . $value);

				if ( is_object($parsed) ) {
					if ( $parsed < $CmpCallCard_prmDT ) {
						$parsed->add(new DateInterval('P1D'));
					}

					$cccQueryParams[$key] = $parsed->format('Y-m-d H:i:s');
				}
				else {
                    if(DateTime::createFromFormat('Y-m-d H:i:s', $value)){
                        $cccQueryParams[$key] = $value;
                    }
                    else
					    $cccQueryParams[$key] = $data['CmpCallCard_prmDate'] . ' ' . $value;
				}
			}
		}


		//продолжение
		//if(!empty($data["CmpCallCard_id"])){
		if( !empty($data['action']) && $data['action'] == 'edit' && !empty($oldCard["CmpCallCard_id"]) ){
			//редактирование

			//1 - выбираем старую запись


			//Делаем копию исходной записи, а измененную копию сохраняем на место старой
			//Дас ист версионность

			//2 - сохраняем старую запись в новую и отмечаем удаленной

			$oldCard['pmUser_id'] = $oldCard['pmUser_insID'];

			$oldCardExceptedFields = array(
				'CmpCallCard_id'
			);

			$oldGenQuery = $this->getParamsForSQLQuery('p_CmpCallCard_ins', $oldCard, $oldCardExceptedFields, false);
			$saveOldQueryParams = $oldGenQuery["paramsArray"];
			$saveOldQueryFields = $oldGenQuery["sqlParams"];

			$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)

					set @Res = null;

					exec p_CmpCallCard_ins
						{$saveOldQueryFields}
						@CmpCallCard_id = @Res output,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

						if ( @ErrMessage is null )
						exec p_CmpCallCard_del
							@CmpCallCard_id = @Res,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;

						exec p_CmpCallCard_setFirstVersion
							@CmpCallCard_id = @Res,
							@CmpCallCard_firstVersion = " . $oldCard['CmpCallCard_id'] . ",
							@pmUser_id = :pmUser_id;

					select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
			$oldCardInNewRec = $this->db->query($query, $saveOldQueryParams);
			$oldCardInNewRec = $oldCardInNewRec->result('array');

			//смысл значения CmpCallCard_firstVersion в том, чтобы хранить ид карты предыдущей версии,
			//те при сохранении копии карты (данных старой карты) она должна содержать ссылку на действующую карту (на ее место)
			//таким самым у нас есть 1 активная карта и несколько ссылающихся на нее (с разными ид) с признаками удаления и ссылками на активную
			//пришлось переделать логику, из-за того что карты стали отображаться как удаленные

			//$cccQueryParams['CmpCallCard_firstVersion'] = $oldCardInNewRec[0]['CmpCallCard_id'];

			//не забываем забрать id бригады СМП из старой записи
			$cccQueryParams['EmergencyTeam_id'] = (
			isset($data['EmergencyTeam_id'])
				? $data['EmergencyTeam_id']
				: (isset($oldCard['EmergencyTeam_id'])
				? $oldCard['EmergencyTeam_id']
				: null));

			//не забываем добавить поле для бригады СМП
			// строка уже может быть, исключим дублирование ее
			if (stristr($cccQueryFields, '@EmergencyTeam_id') === FALSE) {
				$cccQueryFields .= '@EmergencyTeam_id = :EmergencyTeam_id,';
			}

			$cccQueryParams['CmpCallCardStatusType_id'] = $oldCard['CmpCallCardStatusType_id'];
			$cccQueryParams['CmpCallCard_GUID'] = $oldCard['CmpCallCard_GUID'];
			
			/*
			 * 3 - заменяем старую запись текущими изменениями
			 * пояснение: теперь у нас 2 одинаковые записи, на место старой записи вставляем новые данные
			 * */
			
			if(!empty($cccConfig)){
				$cccQueryParams[ 'CmpCallCard_GUID' ] = $cccConfig[ 'CmpCallCard_GUID' ];
				$cccQueryParams[ 'CmpCallCard_id' ] = $cccConfig[ 'CmpCallCard_id' ];
			} else if (!empty($data['CmpCallCard_insID'])) {
				$cccQueryParams['CmpCallCard_id'] = $data['CmpCallCard_insID'];				
			}
			
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ResGUID uniqueidentifier,
					@ErrMessage varchar(4000)

				set @Res = :CmpCallCard_id;
				set @ResGUID = :CmpCallCard_GUID;

				exec " . $procedure . "
					{$cccQueryFields}
					@Error_Code = @ErrCode output,					
					@Error_Message = @ErrMessage output;

					--if ( @ErrMessage is null )

					--exec p_CmpCallCard_setStatus
						--@CmpCallCard_id = @Res,
						--@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,
						--@pmUser_id = :pmUser_id,
						--@Error_Code = @ErrCode output,
						--@Error_Message = @ErrMessage output;

				select @Res as CmpCallCard_id, @ResGUID as CmpCallCard_GUID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			//при редактировании карты не надо постоянно дергать p_CmpCallCard_setStatus, тк на него завязана история статусов

			//var_dump(getDebugSQL($query, $cccQueryParams)); exit;
			$result = $this->db->query($query, $cccQueryParams);

			if(!empty($oldCard)){
				$this -> checkChangesCmpCallCard($oldCard, $cccQueryParams);

				if($oldCard['LpuBuilding_id'] != $data['LpuBuilding_id']){
					//поменяли подстанцию
					$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
					$this->CmpCallCard_model4E->sendCmpCallCardToLpuBuilding($data);
				};
				if($oldCard['CmpCallCardStatusType_id'] != $cccQueryParams['CmpCallCardStatusType_id']){
					$this->setStatusCmpCallCard($cccQueryParams);
				}
			};

			$CmpCallCard_Numv = $cccQueryParams['CmpCallCard_Numv'];
			$CmpCallCard_Ngod = $cccQueryParams['CmpCallCard_Ngod'];
			if ( is_object( $result ) ) {
				$resp = $result->result('array');
				$this->db->trans_commit();
				$ccc_id = '';
				if(!empty($cccQueryParams['CmpCallCard_insID']))
					$ccc_id = $cccQueryParams['CmpCallCard_insID'];
				if($dolog)$this->textlog->add('ccc_m_3 сохранение:'.$resp[0]['CmpCallCard_id'].' / '.$CmpCallCard_Numv.' / '.$CmpCallCard_Ngod.' / '.$ccc_id.' proc:'.$procedure);
				$resp['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
				$resp['CmpCallCard_Numv'] = !empty($CmpCallCard_Numv) ? $CmpCallCard_Numv : null;
				$resp['CmpCallCard_Ngod'] = !empty($CmpCallCard_Ngod) ? $CmpCallCard_Ngod : null;
				$resp['CmpCallCard_prmDT'] = !empty($CmpCallCard_prmDT) ? $CmpCallCard_prmDT : null;

				//если указали отказ при редактировании
				if (!empty($data['CmpRejectionReason_id'])) {
					$statusParams = array(
						'CmpCallCard_id' => $resp[0]['CmpCallCard_id'],
						'CmpCallCardStatusType_id' => '5',
						'CmpCallCardStatus_Comment' => $data['CmpCallCardRejection_Comm'],
						'armtype' => $data['ARMType'],
						'CmpReason_id' => $data['CmpRejectionReason_id'],
						'CmpCallCard_isNMP' => $data['CmpCallCard_IsExtra'],
						'pmUser_id' => $data['pmUser_id']
					);

					$this->setStatusCmpCallCard($statusParams);
				}

				return $resp;
			} else {
				$this->db->trans_rollback();
				return false;
			}
		}
		else{
			//добавление
			$cccQueryParams['CmpCallCard_insID'] = null;
			if (!empty($data['CmpCallCard_insID'])) {
				$cccQueryParams['CmpCallCard_insID'] = $data['CmpCallCard_insID'];
			}

			$cccQueryParams[ 'CmpCallCard_GUID' ] = null;
			
			if(!empty($cccConfig)){
				$cccQueryParams[ 'CmpCallCard_GUID' ] = $cccConfig[ 'CmpCallCard_GUID' ];
				$cccQueryParams[ 'CmpCallCard_insID' ] = $cccConfig[ 'CmpCallCard_id' ];
				$cccQueryParams[ 'CmpCallCard_prmDT' ] = $cccConfig[ 'CmpCallCard_prmDT' ];
			} else if (!empty($data['CmpCallCard_insID'])) {
				$cccQueryParams['CmpCallCard_insID'] = $data['CmpCallCard_insID'];
			}
		
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ResGUID uniqueidentifier,
					@ErrMessage varchar(4000)

				set @Res = :CmpCallCard_insID;
				set @ResGUID = :CmpCallCard_GUID;

				exec " . $procedure . "
					{$cccQueryFields}
					@CmpCallCard_id = @Res output,
					@CmpCallCard_GUID = @ResGUID output,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;

				select @Res as CmpCallCard_id, @ResGUID as CmpCallCard_GUID, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			//var_dump(getDebugSQL($query, $cccQueryParams)); exit;
			$result = $this->db->query($query, $cccQueryParams);
			//return $result->result('array');
		}

		if ( is_object( $result ) ) {

			$resultforstatus = array();
			$resultforstatus = $result->result( 'array' );
			$armType = '';
			if(!empty($data['ARMType']))
				$armType = $data['ARMType'];
			$CmpCallCard_Numv = $cccQueryParams['CmpCallCard_Numv'];
			$CmpCallCard_Ngod = $cccQueryParams['CmpCallCard_Ngod'];
			if($dolog)$this->textlog->add('ccc_m_1 сохранение:'.$resultforstatus[0]['CmpCallCard_id'].' / '.$CmpCallCard_Numv.' / '.$CmpCallCard_Ngod.' / '.$cccQueryParams['CmpCallCard_insID'].' arm:'.$armType.' proc:'.$procedure);
			if($dolog)$this->textlog->add('повтор проверки для CmpCallCard_id '.$resultforstatus[0]['CmpCallCard_id']);
			//повторная проверка на уникальность номеров карты

			$query = "
					SELECT *,
					convert(varchar(10), cast(CCC.CmpCallCard_prmDT as datetime), 104) + ' ' + convert(varchar(8), cast(CCC.CmpCallCard_prmDT as datetime), 108) as CmpCallCard_prmDT
					FROM v_CmpCallCard CCC with (nolock)
					WHERE CCC.CmpCallCard_id = :CmpCallCard_id;
				";

			$newcardresult = $this->db->query( $query, array(
				'CmpCallCard_id' => $resultforstatus[0]['CmpCallCard_id']
			) );
			$newcard = $newcardresult->result( 'array' );
			$newcard[0]['Day_num'] = $newcard[0]['CmpCallCard_Numv'];
			$newcard[0]['Year_num'] = $newcard[0]['CmpCallCard_Ngod'];
			$newcard[0]['AcceptTime'] = $newcard[0]['CmpCallCard_prmDT'];
			$nums = $this->existenceNumbersDayYear($newcard[0]);
			if(is_array($nums) && ($nums['existenceNumbersDay'] || $nums['existenceNumbersYear'])
				&& (!empty($nums['Double_insDT']) && $newcard[0]['CmpCallCard_insDT'] > $nums['double_insDT'])){

				$updateParams = array(
					'CmpCallCard_id' => $resultforstatus[0]['CmpCallCard_id'],
					'CmpCallCard_Numv' => $nums['nextNumberDay'],
					'CmpCallCard_Ngod' => $nums['nextNumberYear'],
					'pmUser_id' => $data['pmUser_id']
				);
				$this->swUpdate('CmpCallCard', $updateParams, false);
				if($dolog)$this->textlog->add('ccc_m_2 smp обновление дубл.парам:'.$resultforstatus[0]['CmpCallCard_id'].' / '.$nums['nextNumberDay'].' / '.$nums['nextNumberYear']);
				// По задаче #137883 после смены номера на СМП, нужно обновить также на основном сервере
				if(!empty($cccConfig)){
					//значит мы на основной БД main, нужно пересохранить и на СМП
					$IsMainServer = $this->config->item('IsMainServer');
					$IsSMPServer = $this->config->item('IsSMPServer');
					unset($this->db);

					try{
						if($IsSMPServer){
							$this->load->database();
						}
						else{
							$this->load->database('smp');
						}
					} catch (Exception $e) {
						$this->load->database();
						$errMsg = "Нет связи с сервером: создание нового вызова недоступно";
						$this->ReturnError($errMsg);
						return false;
					}

					//сохраняем на СМП
					$this->swUpdate('CmpCallCard', $updateParams, false);
					if($dolog)$this->textlog->add('ccc_m_2 main обновление дубл.парам:'.$resultforstatus[0]['CmpCallCard_id'].' / '.$nums['nextNumberDay'].' / '.$nums['nextNumberYear']);
					unset($this->db);
					//возвращаемся на рабочую (она main на СМП сервере или default на основном
					if($IsMainServer === true) {
						$this->load->database();
					}
					else{
						$this->load->database('main');
					}

				}

				$CmpCallCard_Numv = $nums['nextNumberDay'];
				$CmpCallCard_Ngod = $nums['nextNumberYear'];
			}

			$data['CmpCallCard_id'] = $resultforstatus[0]['CmpCallCard_id'];

			$this->checkCallStatusOnSave($data);

			if(getRegionNick() == 'ufa' && !empty( $data['isSavedCVI']) ) {
				$this->load->model('ApplicationCVI_model', 'ApplicationCVI_model');
				$params = [
					'Person_id' => $data['Person_id'],
					'CmpCallCard_id' => !empty($data["CmpCallCard_id"]) ? $data["CmpCallCard_id"] : null,
					'PlaceArrival_id' => $data['PlaceArrival_id'],
					'KLCountry_id' => $data['KLCountry_id'],
					'OMSSprTerr_id' => $data['OMSSprTerr_id'],
					'ApplicationCVI_arrivalDate' => $data['ApplicationCVI_arrivalDate'],
					'ApplicationCVI_flightNumber' => $data['ApplicationCVI_flightNumber'],
					'ApplicationCVI_isContact' => $data['ApplicationCVI_isContact'],
					'ApplicationCVI_isHighTemperature' => $data['ApplicationCVI_isHighTemperature'],
					'Cough_id' => $data['Cough_id'],
					'Dyspnea_id' => $data['Dyspnea_id'],
					'ApplicationCVI_Other' => $data['ApplicationCVI_Other']
				];
				$res = $this->ApplicationCVI_model->doSave($params, false);
				if( !$this->isSuccessful($res) ) {
					throw new Exception($res['Error_Msg']);
				}
			}

			$out = $result->result('array');
			$out['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
			$out['CmpCallCard_Numv'] = !empty($CmpCallCard_Numv) ? $CmpCallCard_Numv : null;
			$out['CmpCallCard_Ngod'] = !empty($CmpCallCard_Ngod) ? $CmpCallCard_Ngod : null;
			$out['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDT'];
			$this->db->trans_commit();
			return $out;
			
		} else {
			$this->db->trans_rollback();
			return false;
		}
	}
	
		/**
	* Проверка и устнановка статуса карте при ее сохранении
	*/
	private function checkCallStatusOnSave($data){
		
		//получаем код типа вызова
		if( !empty($data['CmpCallType_id']) && empty($data['CmpCallType_Code']) ) {
			$typeCardQuery = "SELECT TOP 1 * FROM v_CmpCallType with(nolock) WHERE CmpCallType_id = :CmpCallType_id";
			$typeCard = $this->db->query($typeCardQuery, $data)->row_array();
			if(!empty($typeCard["CmpCallType_Code"])){
				$data['CmpCallType_Code'] = $typeCard['CmpCallType_Code'];
			}
		}

		// Если Тип вызова «Консультативное», «Консультативный», «Справка», «Абонент отключился», 
		//то автоматически вызову присваивается статус «Закрыто»
		if (!empty($data['CmpCallType_Code']) && in_array($data['CmpCallType_Code'], array(6,15,16,17))){
			$data['CmpCallCardStatusType_id'] = 6;
		}
		$this->setStatusCmpCallCard($data);
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
		
		$socstatus_Ids = array(
			"vologda" => 304,
			"ufa" => 2,
			"krasnoyarsk" => 10000173,
			"yaroslavl" => 10000266,
			"buryatiya" => 10000083,
			"kareliya" => 51,
			"khak" => 32,
			"astra" => 10000053,
			"kaluga" => 231,
			"penza" => 224,
			"perm" => 2,
			"pskov" => 25,
			"saratov" => 10000035,
			"ekb" => 10000072,
			"msk" => 60,
			"krym" => 262,
			"kz" => 91,
			"by" => 201
		);

		if ( empty($data[ 'Person_id' ]) ){
			$this->load->model( 'Person_model', 'Person_model' );

			$Person_BirthDay = null;
			if (/*$data['Person_Age'] == 0 && */!empty($data['Person_BirthDay'])) {
				$Person_BirthDay = $data['Person_BirthDay'];
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
				'SocStatus_id' => $socstatus_Ids[getRegionNick()],
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
				'Person_SurName' => !empty($data['Person_SurName'])?$data['Person_SurName']:'',
				'Person_FirName' => !empty($data['Person_FirName'])?$data['Person_FirName']:'',
				'Person_SecName' => !empty($data['Person_SecName'])?$data['Person_SecName']:'',
				'Person_BirthDay'=> $Person_BirthDay,
				'Person_IsUnknown' => 2,
				'PersonSex_id' => $data['Sex_id'],
				'SocStatus_id' => $socstatus_Ids[getRegionNick()],
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
	* default desc
	*/
	public function checkCmpCallCardNumber( $data ) {
		$where = array();
		$params = array();
		
		$where[] = "CCC.Lpu_id=:Lpu_id";
		$where[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
		$where[] = "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
		
		
		$params['Lpu_id'] = $data[ 'Lpu_id' ];
		$params['CmpCallCard_Numv'] = $data[ 'CmpCallCard_Numv' ];
		$params['CmpCallCard_Ngod'] = $data[ 'CmpCallCard_Ngod' ];
		
		$sql = "
			SELECT
				CmpCallCard_Numv,
				CmpCallCard_Ngod
			FROM
				v_CmpCallCard CCC with (nolock)
			".ImplodeWherePH( $where )."
		";
		
		$query = $this->db->query( $sql, $params );
		
		if ( is_object( $query ) ) {
			$res = $this->getCmpCallCardNumber($data);
			return $res[0];
		} else {
			return $data;
		}
	}

	/**
	 * default desc
	 */
	public function getCmpCallCardNumber( $data ) {
		$where = array();
		$params = array();

		//сквозная нумерация
		//для уфы получаем список из подчиненных подстанций
		//и фильтруем по нему
		//этот участок кода законсервирован пока на рабочих мо не создадут
		//нормальную структуру подчиненности подстанций
		/*
		if ( $this->regionNick == 'ufa' || $this->regionNick == 'krym') {
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$smpUnitsNested = $this->CmpCallCard_model4E->loadSmpUnitsNested($data);
			//var_dump($smpUnitsNested); exit;
			if ( !(empty( $smpUnitsNested)) ) {
				$whereBuildings = "CCC.LpuBuilding_id in (";
				foreach ($smpUnitsNested as &$value) {
					$whereBuildings .= $value['LpuBuilding_id'].',';
				}
				$whereBuildings = substr($whereBuildings, 0, -1).')';
				$where[] = $whereBuildings;
				}
			else{
				return $this->createError(null, 'Не определена подстанция');
			}
		}
		else{
			$where[] = "CCC.Lpu_id=:Lpu_id";
			$params['Lpu_id'] = $data[ 'Lpu_id' ];
		}
		*/
		$params = $this->getDatesToNumbersDayYear($data);

		$where[] = "CCC.Lpu_id=:Lpu_id";
		$params['Lpu_id'] = $data[ 'Lpu_id' ] ? $data[ 'Lpu_id' ] : $data['session'][ 'lpu_id' ];
		

		$sql = "
			SELECT
				ISNULL(max(case when (CCC.CmpCallCard_prmDT >= :startDateTime AND CCC.CmpCallCard_prmDT < :endDateTime) then CmpCallCard_Numv else null end), 0) + 1 as CmpCallCard_Numv,
				ISNULL(max(case when (CCC.CmpCallCard_prmDT >= :firstDayCurrentYearDateTime AND CCC.CmpCallCard_prmDT < :firstDayNextYearDateTime) then CmpCallCard_Ngod else null end), 0) + 1 as CmpCallCard_Ngod
			FROM
				v_CmpCallCard CCC with (nolock)
			".ImplodeWherePH( $where )."
		";
		
		/*
		$sql = "
			SELECT
				ISNULL(max(case when cast(CCC.CmpCallCard_prmDT as date) = '".$prmDate."' then CmpCallCard_Numv else null end), 0) + 1 as CmpCallCard_Numv,
				ISNULL(max(case when YEAR(CCC.CmpCallCard_prmDT) = ".$prmYear." then CmpCallCard_Ngod else null end), 0) + 1 as CmpCallCard_Ngod
			FROM
				v_CmpCallCard CCC with (nolock)
			".ImplodeWherePH( $where )."
		";
		*/

		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}
	
	/**
	 * проверка на существование номера вызовов за год и за день на определенную дату
	 * возвращает existenceNumbersYear, existenceNumbersDay: 1 - есть занчение, 0 - отсутствует
	 * nextNumberDay, nextNumberYear: следующие значения номера вызова
	 */
	function existenceNumbersDayYear( $data ){
		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if ($dolog !== true)
			$dolog = false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));
		if( !$data[ 'Lpu_id' ] || !$data[ 'Day_num' ] || !$data[ 'Year_num' ] || !$data['AcceptTime']) return false;
		$where= array();
		$existenceNumbersDay = false;
		$existenceNumbersYear = false;
		$Double_insDT = false;

		$params = array(
			'Lpu_id' => $data[ 'Lpu_id' ],
			'CmpCallCard_Numv' => $data[ 'Day_num' ],
			'CmpCallCard_Ngod' => $data[ 'Year_num' ],
			'CmpCallCard_id' => !empty($data['CmpCallCard_id']) ? $data['CmpCallCard_id'] : null
		);
		$armType = '';
		if(!empty($data['ARMType']))
			$armType = $data['ARMType'];
		if($dolog)$this->textlog->add("проверка:".$params['CmpCallCard_id']." / ".$params['CmpCallCard_Numv'].' / '.$params['CmpCallCard_Ngod'].' arm:'.$armType);
		$where[] =  "CCC.Lpu_id = :Lpu_id";
		$where[] =  "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
		$where[] =  "CCC.CmpCallCard_prmDT >= :startDateTime";
		$where[] =  "CCC.CmpCallCard_prmDT < :endDateTime";
		if(!empty($data['CmpCallCard_id'])){
			$where[] =  "CCC.CmpCallCard_id <> :CmpCallCard_id";
		}
		$timestamp = strtotime($data['AcceptTime']);
		if($timestamp === false) return false;
		
		$data["CmpCallCard_prmDate"] = $data['AcceptTime'];

		$params = array_merge($params,$this->getDatesToNumbersDayYear($data));

		$sql = "
			SELECT top 1
				CmpCallCard_id,
				CmpCallCard_Numv,
				CmpCallCard_insDT
			FROM
				v_CmpCallCard CCC with (nolock)
			".ImplodeWherePH( $where )."
		";
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {

			$resNumv = $query->result_array();
			if(!empty($resNumv[0])){
				if($dolog)$this->textlog->add("дубль по номеру за день:".$resNumv[0]['CmpCallCard_id']." / ".$resNumv[0]['CmpCallCard_Numv']." / ".date_format($resNumv[0]['CmpCallCard_insDT'], 'Y-m-d H:i:s:u'));
				$Double_insDT = $resNumv[0]['CmpCallCard_insDT'];
				$existenceNumbersDay = true;
			}

		} else {
			return false;
		}
		$where = array();
		$where[] =  "CCC.Lpu_id = :Lpu_id";
		$where[] =  "CCC.CmpCallCard_Ngod = :CmpCallCard_Ngod";
		$where[] =  "CCC.CmpCallCard_prmDT >= :firstDayCurrentYearDateTime";
		$where[] =  "CCC.CmpCallCard_prmDT < :firstDayNextYearDateTime";
		if(!empty($data['CmpCallCard_id'])){
			$where[] =  "CCC.CmpCallCard_id <> :CmpCallCard_id";
		}else{
			$data['CmpCallCard_id'] = null;
		}
		$sql = "
			SELECT top 1
				CmpCallCard_id,
				CmpCallCard_Ngod,
				CmpCallCard_insDT
			FROM
				v_CmpCallCard CCC with (nolock)
			".ImplodeWherePH( $where )."
		";
		$query = $this->db->query( $sql, $params );
		if ( is_object( $query ) ) {

			$resNgod = $query->result_array();

			if(!empty($resNgod[0])){
				if($dolog)$this->textlog->add("дубль по номеру за год:".$resNgod[0]['CmpCallCard_id']." / ".$resNgod[0]['CmpCallCard_Ngod']." / ".date_format($resNgod[0]['CmpCallCard_insDT'], 'Y-m-d H:i:s:u'));
				if($Double_insDT && $Double_insDT > $resNumv[0]['CmpCallCard_insDT'])
					$Double_insDT = $resNgod[0]['CmpCallCard_insDT'];
				$existenceNumbersYear = true;
			}

		} else {
			return false;
		}

		if($existenceNumbersDay || $existenceNumbersYear){
			$newNumValues = $this->getCmpCallCardNumber($data);

			if(empty($newNumValues[0]))return false;
		}

		$res_arr =  array(
			'success' => true, 
			'existenceNumbersDay' => $existenceNumbersDay,
			'existenceNumbersYear' => $existenceNumbersYear,
			'nextNumberDay' => $existenceNumbersDay ? $newNumValues[0]["CmpCallCard_Numv"] : $data[ 'Day_num' ],
			'nextNumberYear' => $existenceNumbersYear ? $newNumValues[0]["CmpCallCard_Ngod"] : $data[ 'Year_num' ],
			'double_insDT' => $Double_insDT
		);
		if($dolog)$this->textlog->add('проверка окончена:'.$data['CmpCallCard_id'].' / '.$res_arr['nextNumberDay'].' / '.$res_arr['nextNumberYear']);
		return $res_arr;
		/*
		$sql = "
		SELECT
			case
				when
					EXISTS (
						SELECT 
							CmpCallCard_Numv
						FROM v_CmpCallCard
						WHERE
							cast(CmpCallCard_prmDT as date) = '".date( 'Y-m-d', $timestamp )."'
							and CmpCallCard_Numv = :CmpCallCard_Numv
							and Lpu_id = :Lpu_id
					)
					then
						1
					else
						0
			end AS existenceNumbersDay,
			case
				when
					EXISTS (
						SELECT 
							CmpCallCard_Ngod
						FROM v_CmpCallCard
						WHERE
							YEAR(CmpCallCard_prmDT) = ".date( 'Y', $timestamp )."
							and CmpCallCard_Ngod = :CmpCallCard_Ngod
							and Lpu_id = :Lpu_id
					)
					then
						1
					else
						0
			end AS existenceNumbersYear,
			ISNULL(max(case when cast(CCC.CmpCallCard_prmDT as date) = '".date( 'Y-m-d', $timestamp )."' then CmpCallCard_Numv else null end), 0) + 1 as nextNumberDay,
			ISNULL(max(case when YEAR(CCC.CmpCallCard_prmDT) = ".date( 'Y', $timestamp )." then CmpCallCard_Ngod else null end), 0) + 1 as nextNumberYear
		FROM
			v_CmpCallCard CCC with (nolock)
		WHERE
			Lpu_id = :Lpu_id
			
		";
		
		$query = $this->db->query( $sql, $params );
		
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
		*/
	}

	/**
	 * Возвращает параметры начала и окончания дня/года из настроек
	 * startDateTime - начало дня
	 * endDateTime - конец дня
	 * firstDayCurrentYearDateTime - начало года
	 * firstDayNextYearDateTime - конец года
	 */
	function getDatesToNumbersDayYear($data){
		$this->load->model("Options_model", "opmodel");
		$o = $this->opmodel->getOptionsGlobals($data);

		$g_options = $o['globals'];

		//дата приема вызова
		$prmDateVal = !empty($data['CmpCallCard_prmDT'])? $data['CmpCallCard_prmDT'] : (!empty($data['CmpCallCard_prmDate']) ? $data['CmpCallCard_prmDate'] : '');
		if(!empty($prmDateVal)){
			$prmDateObj = new DateTime($prmDateVal);
		}else{
			$prmDateQuery = $this->dbmodel->getFirstRowFromQuery('select convert(varchar, dbo.tzGetDate(), 104) + convert(varchar, dbo.tzGetDate(), 108) as datetime');
			$prmDateObj = new DateTime($prmDateQuery['datetime']);
		}

		$prmDate = $prmDateObj->format('Y-m-d');
		$prmYear = $prmDateObj->format('Y');

		//по задачке #112257 день у нас начинается с времени в настройках, вот так то
		//дата приема вызова с часами из опций
		$optionsDateTime = $prmDate . ' ' . $g_options["day_start_call_time"] . ':00';

		//если дата еще не наступила по времени - ищем между "вчера" и "сегодня"
		$dateTime = new DateTime($optionsDateTime);
		if($prmDateObj < $dateTime){
			$start = $dateTime->modify('-1 day');
			$params["startDateTime"] = $start->format('Y-m-d H:i:s');
			$params["endDateTime"] = $optionsDateTime;
		}
		else{
			//если дата еще наступила по времени - ищем между "сегодня" и "завтра"
			$params["startDateTime"] = $optionsDateTime;
			$end = $dateTime->modify('+1 day');
			$params["endDateTime"] = $end->format('Y-m-d H:i:s');
		}

		//для выборки по году - ищем между 1 января текущего года с временем и 1 января след. года с временем
		$params["firstDayCurrentYearDateTime"] = $prmYear . '-01-01 ' . $g_options["day_start_call_time"] . ':00';
		$params["firstDayNextYearDateTime"] = $prmYear+1 . '-01-01 ' . $g_options["day_start_call_time"] . ':00';
		return $params;
	}

	/**
	 * default desc
	 */
	function getResults() {
		$query = "
			select
				RES.CmpPPDResult_id
				,RES.CmpPPDResult_Name
				,RES.CmpPPDResult_Code
			from
				v_CmpPPDResult RES with(nolock)
			--where
				--RES.CmpPPDResult_Code <= 10
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function getRejectPPDReasons() {
		$query = "
			select
				RES.CmpPPDResult_id
				,RES.CmpPPDResult_Code - 10 as CmpPPDResult_Code
				,RES.CmpPPDResult_Name
			from
				CmpPPDResult RES with(nolock)
			where CmpPPDResult_Code IN (11,12,13,14,15,16)
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function getMoveFromNmpReasons() {
		$query = "
			select
				CMFNR.CmpMoveFromNmpReason_id
				,CMFNR.CmpMoveFromNmpReason_Name
				,0 as requiredTextField
			from
				v_CmpMoveFromNmpReason CMFNR with(nolock)
			union select
				null as CmpMoveFromNmpReason_id
				,'Другая причина (указать)' as CmpMoveFromNmpReason_Name
				,1 as requiredTextField
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function getReturnToSmpReasons() {
		$query = "
			select
				CRTSR.CmpReturnToSmpReason_id
				,CRTSR.CmpReturnToSmpReason_Name
				,0 as requiredTextField
			from
				v_CmpReturnToSmpReason CRTSR with(nolock)
			union select
				null as CmpReturnToSmpReason_id
				,'Другая причина (указать)' as CmpReturnToSmpReason_Name
				,1 as requiredTextField
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function getCombox($data) {
		$query = "
			select
				CMB.CmpCloseCardCombo_id
				, CMB.CmpCloseCardCombo_Code
				, CMB.ComboName
				, CMB.isLoc
			from
				{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
			where
				Parent_id = '0'
				AND ComboSys = :combo_id
		";

		$queryParams = array('combo_id' => $data['combo_id']);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			 $res = $result->result('array');
			 $title = $res[0]['ComboName'];
			 $query = "
				select
					CMB.CmpCloseCardCombo_id
					, CMB.CmpCloseCardCombo_Code
					, CMB.ComboName
					, CMB.ComboAdd
					, CMB.isLoc
				from
					{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
				where
					Parent_id = '".$res[0]['CmpCloseCardCombo_id']."'
			";
			$result = $this->db->query($query);
			$res2 = $result->result('array');
			$ret = array();
			foreach ($res2 as $combo) {
				if ($combo['isLoc'] == '1') {
					if ($res[0]['isLoc'] == '2') {
						$ret[] = array("boxLabel"=>$combo['ComboName'].' '.$combo['ComboAdd'], "id"=>"CMPCLOSE_CB_{$combo['CmpCloseCardCombo_Code']}", "name"=>$data['combo_id'], "inputValue"=>$combo['CmpCloseCardCombo_Code']);
					} else {
						$ret[] = array("boxLabel"=>$combo['ComboName'].' '.$combo['ComboAdd'], "id"=>"CMPCLOSE_CB_{$combo['CmpCloseCardCombo_Code']}", "name"=>$data['combo_id'].'[]', "inputValue"=>$combo['CmpCloseCardCombo_Code']);
					}

				} else {
					$wid = strlen($combo['ComboName'].' '.$combo['ComboAdd']);
					if ($wid < 10) $wl = 50;
					if ($wid >= 10) $wl = 120;
					if ($wid > 20) $wl = 400;

					if ($res[0]['isLoc'] == '2') {
						$add = ($combo['ComboAdd'] != '')?', <i>'.$combo['ComboAdd'].'</i>':'';
						$ret[] = array(
							"boxLabel"	=>	$combo['ComboName'].$add,
							"id"		=>	"CMPCLOSE_CB_{$combo['CmpCloseCardCombo_Code']}",
							"name"		=>	$data['combo_id'],
							"inputValue"=>	'2',
							"value"		=>	'2'
						);
					}
					switch ($combo['CmpCloseCardCombo_id']) {
						default:
							$ret[] = array(
								"labelWidth" => $wl,
								"labelAlign" => "left",
								"name"=>'ComboValue['.$combo['CmpCloseCardCombo_Code'].']',
								//"hiddenName"=>'bgg_'.$combo['CmpCloseCardCombo_id'],
								"xtype" => 'textfield',
								//"hidden" => true,
								"ctCls" => "left",
								"id"=>"CMPCLOSE_ComboValue_{$combo['CmpCloseCardCombo_Code']}",
								"style" => "text-align: left",
								"fieldLabel" =>  ($res[0]['isLoc'] != '2')?($combo['ComboName'].' '.$combo['ComboAdd']):''
							);
					}

				}

				// 3 level additional
				if ($data['combo_id'] == "ResultUfa_id") {
					$query3 = "
						select
							CMB.CmpCloseCardCombo_id
							, CMB.CmpCloseCardCombo_Code
							, CMB.ComboName
							, CMB.ComboAdd
							, CMB.isLoc
						from
							{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
						where
							Parent_id = '".$combo['CmpCloseCardCombo_id']."'
					";
					$result3 = $this->db->query($query3);
					$res3 = $result3->result('array');
					foreach ($res3 as $r3) {
						$ret[] = array(
							"labelWidth" => "200",
							"name"=>'ComboValue['.$r3['CmpCloseCardCombo_Code'].']',
							"xtype" => "textfield",
							//"hidden" => true,
							"ctCls" => "left",
							"id"=>"CMPCLOSE_ComboValue_{$r3['CmpCloseCardCombo_Code']}",
							"style" => "text-align: left;",
							"styleLabel" => "width: 200px;",
							"labelStyle" => "width: 280px;",
							"fieldLabel" =>  $r3['ComboName'].' '.$r3['ComboAdd']
						);
					}
				}
			}

			return $ret;
		} else {
			return false;
		}
	}


	/**
	 * default desc
	 */
	public function getComboxAll(){

		//переделал sql для вывода родителя родителей(дедушки и немного прадеда)
		$sql = "
			SELECT
				CMB.CmpCloseCardCombo_id,
				CMB.CmpCloseCardCombo_Code,
				CMB.ComboName,
				CMB.ComboAdd,
				COALESCE(parentCMB.ComboSys,grandParentCMB.ComboSys,grandGrandParentCMB.ComboSys, null) as ComboSys,
				CMB.Parent_id,
				parentCMB.Parent_id as grandParent_id,
				parentCMB.CmpCloseCardCombo_Code as ParentCombo_Code,
				parentCMB.isLoc as parentLoc,
				CMB.isLoc,
				CMB.CmpCloseCardCombo_ItemType,
				CMB.CmpCloseCardCombo_ItemSort
			FROM
				{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
                left JOIN {$this->comboSchema}.v_CmpCloseCardCombo parentCMB with(nolock) on(parentCMB.CmpCloseCardCombo_id = CMB.Parent_id)
                left JOIN {$this->comboSchema}.v_CmpCloseCardCombo grandParentCMB with(nolock) on(grandParentCMB.CmpCloseCardCombo_id = parentCMB.Parent_id)
				left JOIN {$this->comboSchema}.v_CmpCloseCardCombo grandGrandParentCMB with(nolock) on(grandGrandParentCMB.CmpCloseCardCombo_id = grandParentCMB.Parent_id)
			WHERE
				CMB.Parent_id > 0
				and (CMB.CmpCloseCardCombo_IsClose = 1 or CMB.CmpCloseCardCombo_IsClose is null)
			ORDER BY
				ComboSys ASC,
				CMB.CmpCloseCardCombo_ItemSort ASC
			";

		//var_dump($sql); exit;
		$query = $this->db->query( $sql );
		if ( !is_object( $query ) ) {
			return false;
		}

		// Пояснение того, что такое происходит:
		//
		// Выбираем элементы подчиненные родителю, CMB.Parent_id > 0
		// Элементы у которых нет деда grandParent_id - 2го уровня
		// Обрабатываем их в соответствии с полями isLoc
		// Элементы у которых есть дед grandParent_id - 3го уровня
		// Далее пояснения:

		$ret = array();
		foreach ($query->result_array() as $combo) {

			//обработка 2 уровня
			if($combo[ 'grandParent_id' ] == 0){

				//заготовка поля для ввода
				$wid = strlen( $combo[ 'ComboName' ].' '.$combo[ 'ComboAdd' ] );
					if ( $wid < 10 ) $wl = 50;
					elseif ( $wid >= 10 ) $wl = 120;
					elseif ( $wid > 20 ) $wl = 400;

				//текстовое поле, чтобы его не создавать 100500 раз
				$txtField = array(
					'labelWidth' => $wl,
					'width' => 300,
					'labelAlign' => 'left',
					'name' => 'ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
					'xtype' => 'textfield',
					'ctCls' => 'left',
					'maxLength' => 50,
					'id' => 'CMPCLOSE_ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
					'style' => 'text-align: left',
					'fieldLabel' => ( $combo[ 'parentLoc' ] != '2' ? $combo[ 'ComboName' ].' '.$combo[ 'ComboAdd' ] : '' ),
					'hidden' => ( $combo[ 'parentLoc' ] != '2' ? true : false ),
				);
				//конец заготовки поля для ввода

				switch($combo[ 'isLoc' ]){
					case '0':{
						//чекбокс/комбобокс с текстовым полем
						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'boxLabel' => $combo[ 'ComboName' ].( $combo[ 'ComboAdd' ] != '' ? ', <i>'.$combo[ 'ComboAdd' ].'</i>' : '' ),
							'id' => 'CMPCLOSE_CB_'.$combo[ 'CmpCloseCardCombo_Code' ],
							'name' => "ComboCheck_" . $combo[ 'ComboSys' ],
							"code" => $combo['CmpCloseCardCombo_Code'],
							"inputValue"=>$combo['CmpCloseCardCombo_Code']
						);
						$txtField['labelStyle'] = 'height: 0;';
						$txtField['parent_code'] = $combo['CmpCloseCardCombo_Code'];
						$ret[ $combo[ 'ComboSys' ] ][] = $txtField;
						break;
					}
					case '1':{
						// обычный чекбокс или радио
						$ret[$combo['ComboSys']][] = array(
							"boxLabel"=>$combo['ComboName'].' '.$combo['ComboAdd'],
							"id"=>"CMPCLOSE_CB_{$combo['CmpCloseCardCombo_Code']}",
							"name"=> "ComboCheck_" . $combo['ComboSys'],
							"inputValue"=>$combo['CmpCloseCardCombo_Code'],
							"code" => $combo['CmpCloseCardCombo_Code']
						);
						break;
					}
					case '2':{
						//просто текстовое поле
						$ret[ $combo[ 'ComboSys' ] ][] = $txtField;
						break;
					}
					case '3':{
						//текстовое поле с лейблом
						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'xtype' => 'label',
							'text' => $combo[ 'ComboName' ].':',
							'style' => 'display: block; text-align: left;',
						);
						$ret[ $combo[ 'ComboSys' ] ][] = $txtField;
						break;
					}
					case '4':{
						//комбобокс с 2 лейблами DS
						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'boxLabel' => $combo[ 'ComboName' ].( $combo[ 'ComboAdd' ] != '' ? ', <i>'.$combo[ 'ComboAdd' ].'</i>' : '' ),
							'id' => 'CMPCLOSE_CB_'.$combo[ 'CmpCloseCardCombo_Code' ],
							'name' => "ComboCheck_" . $combo[ 'ComboSys' ],
							'inputValue'=> $combo['CmpCloseCardCombo_Code'],
							'code' => $combo['CmpCloseCardCombo_Code'],
							'type' => 'dsComboRadioCmpParent'
							//'name' => $combo[ 'ComboSys' ]
						);

						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'xtype' => 'label',
							'text' => 'D',
							'id' => 'CMPCLOSE_CBCD_'.$combo[ 'CmpCloseCardCombo_Code' ],
							'style' => 'text-align: right; width: 15px;',
							'parent_code' => $combo['CmpCloseCardCombo_Code'],
							'type' => 'dsComboRadioCmp'
						);
						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'xtype' => 'swequalitytypecombo',
							'width' => 30,
							'id' => 'CMPCLOSE_CBC_'.$combo[ 'CmpCloseCardCombo_Code' ],
							'name' => "ComboCmp_" . $combo[ 'CmpCloseCardCombo_Code' ],
							'hiddenName' => "ComboCmp_" . $combo[ 'CmpCloseCardCombo_Code' ],
							'parent_code' => $combo['CmpCloseCardCombo_Code'],
							'type' => 'dsComboRadioCmp'
						);
						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'xtype' => 'label',
							'text' => 'S',
							'id' => 'CMPCLOSE_CBCS_'.$combo[ 'CmpCloseCardCombo_Code' ],
							'style' => 'text-align: right; width: 15px;',
							'parent_code' => $combo['CmpCloseCardCombo_Code'],
							'type' => 'dsComboRadioCmp'
						);
						break;
					}
					case '5': {
						//radiogroup
						//формируем радиогруппу
						$radioItems = array();

						foreach ($ret[ $combo[ 'ComboSys' ] ] as $key => $value){
							if (isset($value['parent_code']) && $value['parent_code'] == $combo['CmpCloseCardCombo_Code']){
								$radioItems[] = $value;
								unset($ret[ $combo[ 'ComboSys' ] ][$key]);
							}
						};

						$ret[ $combo[ 'ComboSys' ] ][] = array(
							'xtype'		=> 'radiogroup',
							'columns'	=> 2,
							'vertical'	=> true,
							'width'		=> '100%',
							'items'		=> $radioItems
						);

						$ret[ $combo[ 'ComboSys' ] ] = array_values($ret[ $combo[ 'ComboSys' ] ]);
						break;
					}
                    case '6':{
                        //чекбокс/комбобокс с комбобоксом осложнение
                        $ret[ $combo[ 'ComboSys' ] ][] = array(
                            'boxLabel' => $combo[ 'ComboName' ].( $combo[ 'ComboAdd' ] != '' ? ', <i>'.$combo[ 'ComboAdd' ].'</i>' : '' ),
                            'id' => 'CMPCLOSE_CB_'.$combo[ 'CmpCloseCardCombo_Code' ],
                            'name' => "ComboCheck_" . $combo[ 'ComboSys' ],
                            "code" => $combo['CmpCloseCardCombo_Code'],
                            'itemCls' => 'leftSequelaCheck',
                            "inputValue"=>$combo['CmpCloseCardCombo_Code']
                        );

						if(getRegionNick() == 'perm') {
							$comboField = array(
								'comboSubject' => 'SequelaDegreeType',
								'allowBlank' => false,
								'labelWidth' => 100,
								'width' => 100,
								'listWidth' => 130,
								'labelAlign' => 'left',
								'name' => 'ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
								'hiddenName' => 'ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
								'xtype' => 'swcommonsprcombo',
								'itemCls' => 'rightSequelaCombo',
								'id' => 'CMPCLOSE_ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
								'hidden' => ( $combo[ 'parentLoc' ] != '2' ? true : false ),
								'parent_code' => $combo['CmpCloseCardCombo_Code'],
								'style' => 'margin-left: 10px',
								'value' => 2
							);

							$ret[ $combo[ 'ComboSys' ] ][] = $comboField;
						}
                        break;
                    }
					default:{
						//просто текстовое поле
						$ret[ $combo[ 'ComboSys' ] ][] = $txtField;
						break;
					}
				}
			}
			else{
				//3 уровень

				//элемент родителя radioGroup - опрелеляются как радио
				//parentLoc = 5 - радиогруппа
				if($combo["parentLoc"] == 5 ){
					$ret[$combo['ComboSys']][] = array(
						"boxLabel"=>$combo['ComboName'].' '.$combo['ComboAdd'],
						"id"=>"CMPCLOSE_CB_{$combo['CmpCloseCardCombo_Code']}",
						//"name"=> $combo['ComboSys'] . '_' . $combo[ 'ParentCombo_Code' ],
						'name' => "ComboCheck_" . $combo[ 'ParentCombo_Code' ],
						"inputValue"=>$combo['CmpCloseCardCombo_Code'],
						"code" => $combo['CmpCloseCardCombo_Code'],
						'parent_code' => $combo[ 'ParentCombo_Code' ]
					);
					continue;
				}

				//далее самоопределяющиеся по типу элементы
				$item = array(
					'xtype' => 'textfield',
					'name' => 'ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
					'hiddenName' => 'ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
					'id' => 'CMPCLOSE_ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
					'cls' => 'ResultUfa-parent-'.$combo[ 'CmpCloseCardCombo_Code' ],
					'parent_code' => $combo[ 'ParentCombo_Code' ],
					'code' => $combo['CmpCloseCardCombo_Code']
				);

				switch( $combo['CmpCloseCardCombo_ItemType'] ){
					case 'swdatetimefield':
						$item['xtype'] = 'swdatetimefield';
						$item['dateFieldWidth'] = 80; // ширина поля
						$item['dateLabelWidth1'] = '235px'; // ширина обертки лэйбла и поля
						$item['dateLabelStyle'] = 'width: 115px;';
						$item['dateLabel'] = $combo[ 'ComboName' ].' '.$combo[ 'ComboAdd' ];
						$item['timeLabelWidth'] = 50; // ширина лэйбла
						$item['timeLabelWidth1'] = '145px'; // ширина обертки лэйбла и поля
						$item['timeLabel'] = 'Время';

						//индивидуальная правка ширины лейбла для поля остальное
						if($combo["ComboSys"] == "ResultOther_id"){
							$item['dateLabelStyle'] = '';
							$item['dateLabelWidth1'] = '220px';
						}

						//индивидуальная правка ширины лейбла для поля остальное
						if($combo["ComboSys"] == "Result_id"){
							$item['hiddenName'] = 'ComboValue_'.$combo[ 'CmpCloseCardCombo_Code' ];
							$item['dateLabel'] = 'Дата';
							$item['dateLabelStyle'] = '';
							$item['dateLabelWidth1'] = '220px';
						}
					break;

					case 'swtimefield':
						$item['xtype'] = 'swtimefield';
						$item['dateFieldWidth'] = 80; // ширина поля
						$item['timeLabelWidth1'] = '140px'; // ширина обертки лэйбла и поля
						$item['timeLabel'] = 'Время';
					break;

					case 'textfield':
						$item['fieldLabel'] = $combo[ 'ComboName' ].' '.$combo[ 'ComboAdd' ];
						$item['labelStyle'] = 'width: 130px';
					break;

					case 'swlpucombo':
						$item['fieldLabel'] = $combo[ 'ComboName' ];
						$item['xtype'] = 'swlpuopenedcombo';
						$item['forceselection'] = true;
						$item['editable'] = true;
						$item['ctxSerach'] = true;
						$item['listWidth'] = 400;
						$item['autoLoad'] = true;
						$item['labelStyle'] = ($combo['ComboName'] == 'МО') ? 'width: 110px' : 'width: 200px';
					break;

					case 'sworgcombo':
						$item['fieldLabel'] = $combo[ 'ComboName' ];
						$item['labelStyle'] = 'width: 180px;';
						$item['xtype'] = 'sworgcomboex';
						$item['enableKeyEvents'] = true;
						$item['triggerAction'] = 'none';
						$item['width'] = 320;
						$item['enableOrgType'] = false;
						$item['defaultOrgType'] = 11;
						$item['autoLoad'] = true;
					break;

					//адресный триггер
					case 'addresstriggerfield':
						$item['fieldLabel'] = 'Адрес посещения';
						$item['enableKeyEvents'] = true;
						$item['width'] = 320;
						$item['ctCls'] = 'addresstriggerfield';
						$item['cls'] = 'addresstriggerfield';
						$item['xtype'] = 'swtripletriggerfield';
					break;

					case 'hidden':
						$item['xtype'] = 'hidden';
						break;

					//в результате оказания смп
					case 'swdieplace':
						$item['fieldLabel'] = 'Место';
						$item['xtype'] = 'swcommonsprcombo';
						$item['comboSubject'] = 'CmpLethalType';
						$item['listWidth'] = 300;
						//$item['name'] = 'CmpLethalType_id';
						//$item['hiddenName'] = 'CmpLethalType_id';
						$item['autoLoad'] = true;
					break;

					//для состава бригады
					case 'swmedpersonalcombo':
						$item['fieldLabel'] = $combo['ComboName'];
						$item['xtype'] = 'swmedpersonalcombo';
						$item['listWidth'] = 400;
						$item['labelStyle'] = 'width: 50px; display: none;';
						$item['labelWidth'] = 50;
						$item['allowBlank'] = 'true';
					break;

					case 'swemergencyteamorepenvcombo':
						$item['fieldLabel'] = 'Номер бригады СМП';
						$item['labelStyle'] = 'width: 200px';
						$item['xtype'] = 'swemergencyteamorepenvcombo';
						$item['allowBlank'] = 'true';
						$item['listWidth'] = 400;
					break;

					case 'swEmergencyTeamCCC':
						$item['fieldLabel'] = 'Номер бригады СМП';
						$item['labelStyle'] = 'width: 200px';
						$item['xtype'] = 'swEmergencyTeamCCC';
						$item['allowBlank'] = 'true';
						$item['listWidth'] = 400;
					break;

					case 'swdiagcombo':
						$item['xtype'] = 'swdiagcombo';
						$item['checkAccessRights'] = true;
						$item['labelStyle'] = 'width: 200px';
					break;

					case 'checkbox':
						$item['xtype'] = 'checkbox';
						$item['boxLabel'] = $combo['ComboName'];
						$item['id'] = "CMPCLOSE_CB_{$combo['CmpCloseCardCombo_Code']}";
						$item['name'] = "ComboCheck_" . $combo['ComboSys'];
						$item['style'] = 'margin-left: 50px';
						$item['inputValue'] = $combo['CmpCloseCardCombo_Code'];
					break;
					/*
					отменено
					case 'dsradiogroup':
						$item['xtype'] = 'checkboxgroup';
						$item['style'] = 'padding-left: 20px;';
						$item['items'] = array(
							array(
								'boxLabel'		=> 'D',
								'name'			=> 'DsRadioValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
								'inputValue' 	=> 'D',
								'id'			=> 'CMPCLOSE_DSD_'.$combo['CmpCloseCardCombo_Code']
							),
							array(
								'boxLabel'		=> 'S',
								'name'			=> 'DsRadioValue_'.$combo[ 'CmpCloseCardCombo_Code' ],
								'inputValue' 	=> 'S',
								'id'			=> 'CMPCLOSE_DSS_'.$combo['CmpCloseCardCombo_Code']
							)
						);
						//$item['id'] = "CMPCLOSE_DS_{$combo['CmpCloseCardCombo_Code']}";
						$item['name'] = $combo['ComboSys'];
					break;
					*/

					default:
						$item['labelStyle'] = 'width: 200px';
					break;
				}

				$ret[ $combo['ComboSys'] ][] = $item;
			}
		}

		return $ret;
	}

	/**
	 * Список значений для комбика по ComboSys или CmpCloseCardCombo_Code
	 */
	public function getComboValuesList($data){
		if(empty($data['ComboSys']) && empty($data['CmpCloseCardCombo_Code'])){
			return false;
		}

		if($data['ComboSys']){
			$parent_id = $this->getComboIdByComboSys($data);
		}

		if($data['CmpCloseCardCombo_Code']){
			$parent_id = $this->getComboIdByCode($data);
		}

		if(empty($parent_id)) return false;

		$query = "
			select
				CMB.CmpCloseCardCombo_id
				,CMB.CmpCloseCardCombo_Code
				,CMB.ComboName
				,CMB.CmpCloseCardCombo_ItemSort
			from
				{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
			where
				Parent_id = :combo_id
		";

		$queryParams = array('combo_id' => $parent_id);
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			return $result->result('array');
		}

	}
	/**
	 * Печать результата
	 */
	function getResultCmpForPrint($CmpCloseCard) {
		$content = '<div class="wrapper110">';

		$query2 = "
			select
				cr.CmpResult_Name as ComboName,
				cr.CmpResult_id as CmpCloseCardCombo_id,
				cr.CmpResult_Code as CmpCloseCardCombo_Code,
				CASE WHEN CR.CmpResult_id = ccc.CmpResult_id THEN 1 ELSE 0 END as flag
			from
				{$this->comboSchema}.v_CmpResult cr (nolock)
				LEFT JOIN {$this->schema}.v_CmpCloseCard ccc with (nolock) on ccc.CmpCloseCard_id = :CmpCloseCard_id
		";
		$queryParams2 = array(
			'CmpCloseCard_id' => $CmpCloseCard
		);

		$result2 = $this->db->query($query2, $queryParams2);

		if ( is_object($result2) ) {
			$result2 = $result2->result('array');
			foreach ($result2 as $res2) {
				$fflag2 = (($res2['flag'] == 1)?'<div class="v_ok"></div>':'<div class="v_no"></div>');
				$content .= '<div class="innerwrapper">' . $res2['ComboName'] . ' ' . $fflag2 . '</div>';
			}
		}

		$content .= '</div>';
		
		return $content;
	}

	/**
	 * default desc
	 */
	function getComboRel($CmpCloseCard, $SysName) {

		$query = "
			select
				CMB.CmpCloseCardCombo_id
				,CMB.CmpCloseCardCombo_Code
				,CMB.ComboName
			from
				{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
			where
				Parent_id = '0'
				AND ComboSys = :combo_id
			order by Region_id desc
		";


		$queryParams = array('combo_id' => $SysName);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if(empty($res[0])){
				return false;
			}

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
			";

			$queryParams = array(
				'CmpCloseCard_id' => $CmpCloseCard,
				'ComboId' => $comboid
			);


			$result = $this->db->query($query, $queryParams);


			if ( is_object($result) ) {
				$result = $result->result('array');

				$content = '<div class="wrapper110">';
				foreach ($result as $res) {

					if ($res['CmpCloseCardCombo_Code'] == '111' && (int)$res['Localize'] > 0) {
						$query3 = "
							select
								L.Lpu_Name
							from
								v_Lpu as L (nolock)
							where
								L.Lpu_id = :Lpuid
						";
						$queryParams3 = array(
							'Lpuid' => $res['Localize']
						);
						$result3 = $this->db->query($query3, $queryParams3);
						if ( is_object($result3) ) {
							$result3 = $result3->result('array');
							if (count($result3) > 0) $res['Localize'] = $result3[0]['Lpu_Name'];
						}
					}


				    $fflag = (($res['flag'] == 1)?'<div class="v_ok"></div>':'<div class="v_no"></div>');
					if ($SysName == 'AgeType_id') {
						if ($res['flag'] == 1) $content .= $res['ComboName'].' <u>'.$res['Localize'].'</u>';
					} else {
						$content .= '<div class="innerwrapper">'.$res['ComboName'].' '.$fflag.'<u>'.$res['Localize'].'</u></div>';
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

				return $content;
			} else {
				return false;
			}
		}
	}

	/**
	 * Загрузка справочника, формирование чекбоксов
	 */
	function getCombo($data,$object) {

		$sql = "
			select
				{$object}_Name,
			    {$object}_id
			from
				{$this->comboSchema}.v_{$object}";

		$query = $this->db->query($sql);
		$result = $query->result_array();

		$content = '';
		foreach ($result as $value){

			if($value[$object.'_id']==$data){
				$content.='<div class="innerwrapper">'.$value[$object.'_Name']. ' <div class="v_ok"></div></div>';
			}
			else $content.='<div class="innerwrapper">'.$value[$object.'_Name']. ' <div class="v_no"></div></div> ';
		}

		return $content;
	}

	/**
	 * default desc
	 */
	function getComboRelEMK($CmpCloseCard, $SysName) {

		$query = "
			select
				CMB.CmpCloseCardCombo_id
				,CMB.CmpCloseCardCombo_Code
				,CMB.ComboName
			from
				{$this->comboSchema}.v_CmpCloseCardCombo CMB with(nolock)
			where
				Parent_id = '0'
				AND ComboSys = :combo_id
		";

		$queryParams = array('combo_id' => $SysName);
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$res = $result->result('array');

			if ( count($res) > 0 ) {
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
				";

				$queryParams = array(
					'CmpCloseCard_id' => $CmpCloseCard,
					'ComboId' => $comboid
				);

				$result = $this->db->query($query, $queryParams);

				if ( is_object($result) ) {
					$result = $result->result('array');
					$content = '';
					foreach ($result as $res) {

						if ($res['CmpCloseCardCombo_Code'] == '111' && (int)$res['Localize'] > 0) {
							$query3 = "
								select
									L.Lpu_Name
								from
									v_Lpu as L (nolock)
								where
									L.Lpu_id = :Lpuid
							";
							$queryParams3 = array(
								'Lpuid' => $res['Localize']
							);
							$result3 = $this->db->query($query3, $queryParams3);
							if ( is_object($result3) ) {
								$result3 = $result3->result('array');
								if (count($result3) > 0) $res['Localize'] = $result3[0]['Lpu_Name'];
							}
						}
						if ($res['flag'] == 1) $content = $res['ComboName'].' <u>'.$res['Localize'].'</u>';
					}
					return $content;
				} else {
					return false;
				}
			}
		}
	}


	/**
	 * @desc Вспомогательная функция для преобразование полной даты записсаной в виде строки в дату человекоподобную
	 * @param string $str
	 * @return string
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

	/**
	 * @desc Обновление параметров CmpCallCard при закрычии 110у
	 * @param array $data
	 * @return boolean
	 */
	function updateCmpCallCardByClose($data) {
		$dolog = (defined('DOLOGSAVECARD') && DOLOGSAVECARD === true) ? true : false;
		if($dolog)$this->load->library('textlog', array('file'=>'saveCmpCallCardNumbers_'.date('Y-m-d').'.log'));

		if (!empty($data['CmpCallCard_id']) && $data['CmpCallCard_id'] > 0) {

			$params = array('CmpCallCard_id'=>$data['CmpCallCard_id']);
			$setAdd = '';

			if (!empty($data['AcceptTime'])) {
				$AcceptDate = DateTime::createFromFormat('d.m.Y H:i', $data['AcceptTime']);
				$aDate = $AcceptDate->format('Y-m-d H:i');
				$setAdd .= ", CmpCallCard_prmDT = :CmpCallCard_prmDT";
				$params['CmpCallCard_prmDT'] = $aDate;
				if($dolog)$this->textlog->add('update CmpCallCard_id='.$data['CmpCallCard_id'] . ' CmpCallCard_prmDT=' .$params['CmpCallCard_prmDT']);
			}
			if (!empty($data['Person_id']) && $data['Person_id'] > 0) {
				$setAdd .= ", Person_id = :Person_id";
				$params['Person_id'] = $data['Person_id'];
			}
			if (!empty($data['Diag_id']) && $data['Diag_id'] > 0) {
				$setAdd .= ", Diag_uid = :Diag_uid";
				$params['Diag_uid'] = $data['Diag_id'];
			}
			if (!empty($data['CallPovod_id']) && $data['CallPovod_id'] > 0) {
				$setAdd .= ", CmpReason_id = :CmpReason_id";
				$params['CmpReason_id'] = $data['CallPovod_id'];
			}
			if (!empty($data['CmpReasonNew_id']) && $data['CmpReasonNew_id'] > 0) {
				$setAdd .= ", CmpReasonNew_id = :CmpReasonNew_id";
				$params['CmpReasonNew_id'] = $data['CmpReasonNew_id'];
			}
			if (!empty($data['Lpu_hid']) && $data['Lpu_hid'] > 0) {
				$setAdd .= ", Lpu_hid = :Lpu_hid";
				$params['Lpu_hid'] = $data['Lpu_hid'];
			}
			if (!empty($data['MedPersonal_id']) && $data['MedPersonal_id'] > 0) {
				$setAdd .= ", MedPersonal_id = :MedPersonal_id";
				$params['MedPersonal_id'] = $data['MedPersonal_id'];
			}
			if (!empty($data['MedStaffFact_id']) && $data['MedStaffFact_id'] > 0) {
				$setAdd .= ", MedStaffFact_id = :MedStaffFact_id";
				$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			}
			if (!empty($data['CmpCallCard_IndexRep']) && $data['CmpCallCard_IndexRep'] > 0) {
				$setAdd .= ", CmpCallCard_IndexRep = :CmpCallCard_IndexRep";
				$params['CmpCallCard_IndexRep'] = $data['CmpCallCard_IndexRep'];
			}
			if (!empty($data['CmpCallCard_isControlCall']) && $data['CmpCallCard_isControlCall'] > 0) {
				$setAdd .= ", CmpCallCard_isControlCall = :CmpCallCard_isControlCall";
				$params['CmpCallCard_isControlCall'] = $data['CmpCallCard_isControlCall'];
			}

			// Место вызова
			if (!empty($data['CallPlace_id']) && $data['CallPlace_id'] > 0) {
				if ($data['CallPlace_id'] == '180') $params['CmpPlace_id'] = '3';
				if ($data['CallPlace_id'] == '181') $params['CmpPlace_id'] = '2';
				if ($data['CallPlace_id'] == '182') $params['CmpPlace_id'] = '5';
				if ($data['CallPlace_id'] == '183') $params['CmpPlace_id'] = '4';
				if ($data['CallPlace_id'] == '184') $params['CmpPlace_id'] = '7';
				if ($data['CallPlace_id'] == '185') $params['CmpPlace_id'] = '7';
				if ($data['CallPlace_id'] == '186') $params['CmpPlace_id'] = '7';
				if ($data['CallPlace_id'] == '187') $params['CmpPlace_id'] = '7';
				if ($data['CallPlace_id'] == '188') $params['CmpPlace_id'] = '4';
				if ($data['CallPlace_id'] == '189') $params['CmpPlace_id'] = '4';
				if ($data['CallPlace_id'] == '190') $params['CmpPlace_id'] = '8';
				$setAdd .= ", CmpPlace_id = :CmpPlace_id";
			}

			// Результат вызова
			if (!empty($data['ResultUfa_id']) && $data['ResultUfa_id'] > 0) {
				if ($data['ResultUfa_id'] == '224') $params['CmpResult_id'] = '21';
				if ($data['ResultUfa_id'] == '225') $params['CmpResult_id'] = '13';
				if ($data['ResultUfa_id'] == '226') $params['CmpResult_id'] = '11';
				if ($data['ResultUfa_id'] == '227') $params['CmpResult_id'] = '26';
				if ($data['ResultUfa_id'] == '228') $params['CmpResult_id'] = '22';
				if ($data['ResultUfa_id'] == '229') {
					$params['LeaveType_id'] = '3';
					$setAdd .= ", LeaveType_id = :LeaveType_id";
					$params['CmpResult_id'] = '25';
				}
				if ($data['ResultUfa_id'] == '230') {
					$params['LeaveType_id'] = '3';
					$setAdd .= ", LeaveType_id = :LeaveType_id";
					$params['CmpResult_id'] = '19';
				}
				if (!empty($data['CallPlace_id']) && $data['CallPlace_id'] > 0) {
					if ($data['CallPlace_id'] == '231') $params['CmpResult_id'] = '3';
				}
				if ($data['ResultUfa_id'] == '232') $params['CmpResult_id'] = '6';
				if ($data['ResultUfa_id'] == '233') $params['CmpResult_id'] = '4';
				if ($data['ResultUfa_id'] == '234') $params['CmpResult_id'] = '36';
				if ($data['ResultUfa_id'] == '235') {
					$params['LeaveType_id'] = '3';
					$setAdd .= ", LeaveType_id = :LeaveType_id";
					$params['CmpResult_id'] = '24';
				}
				if ($data['ResultUfa_id'] == '236') $params['CmpResult_id'] = '3';
				if ($data['ResultUfa_id'] == '237') $params['CmpResult_id'] = '7';
				if ($data['ResultUfa_id'] == '238') $params['CmpResult_id'] = '5';
				if ($data['ResultUfa_id'] == '239') $params['CmpResult_id'] = '21';
				$setAdd .= ", CmpResult_id = :CmpResult_id";
			}


			$query = "
				update
					CmpCallCard
				set
					CmpCallCard_updDT = dbo.tzGetDate()".$setAdd."
				where
					CmpCallCard_id = :CmpCallCard_id
			";

			$this->db->query($query, $params);

			$result = $this->swUpdate('CmpCallCard' , $params);

			return $this->isSuccessful( $result );

		}
		return false;
	}
	/**
	 * @desc Установка статуса карты вызова
	 * @param array $data
	 * @return boolean
	 */
	function setStatusCmpCallCard($data, $dbSMP = null) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

		if( !isset($data['CmpCallCardStatusType_id']) || $data['CmpCallCardStatusType_id'] == 0 ) {
			$data['CmpCallCardStatusType_id'] = null;
		}

		if (!isset($data['CmpCallCard_IsReceivedInPPD'])) {
			$data['CmpCallCard_IsReceivedInPPD'] = null;
		}

		if ( isset($data['CmpCallCardStatusType_id']) && $data['CmpCallCardStatusType_id'] == 3 ) {
			$data['CmpCallCard_IsReceivedInPPD'] = 1;
		}

		if ( !isset($data['CmpCallCard_isNMP']) ) {
			$prequery = 'select CmpCallCard_isNMP from v_CmpCallCard where CmpCallCard_id = :CmpCallCard_id';
			$preres = $this->db->query($prequery,$data);
			$preres = $preres->row_array();
			if(!empty($preres["CmpCallCard_isNMP"])){
				$data['CmpCallCard_isNMP'] = $preres["CmpCallCard_isNMP"];
			}
			else{
				$data['CmpCallCard_isNMP'] = 1;
			}
		}
			
		if ( isset($data['CmpReason_id']) ) {
			if( $data['CmpReason_id']== 0){
				$data['CmpReason_id'] = null;
			}
		} else {
			$data['CmpReason_id'] = null;
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
		//var_dump($data);		exit;
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpCallCard_id;
			exec p_CmpCallCard_setStatus
				@CmpCallCard_id = @Res,
				@CmpCallCardStatusType_id = :CmpCallCardStatusType_id,
				@CmpCallCardStatus_Comment = :CmpCallCardStatus_Comment,
				@CmpCallCard_IsReceivedInPPD = :CmpCallCard_IsReceivedInPPD,
				@CmpReason_id = :CmpReason_id,
				@CmpCallCard_isNMP = :CmpCallCard_isNMP,
				@pmUser_id = :pmUser_id,
				@CmpMoveFromNmpReason_id = :CmpMoveFromNmpReason_id,
				@CmpReturnToSmpReason_id = :CmpReturnToSmpReason_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = !empty($dbSMP)? $dbSMP->query($query, $data): $this->db->query($query, $data);

		//установка службы нмп
		if(!empty($data['MedService_id'])){
			$postSql = "
				update dbo.CmpCallCard with (ROWLOCK) set
				MedService_id = :MedService_id,
				pmUser_updID = :pmUser_id,
				CmpCallCard_updDT = getdate()
				WHERE CmpCallCard_id  = :CmpCallCard_id
			";
			$res = $this->db->query($postSql, $data);
		}

		if ( is_object($result) ) {

			if(defined('STOMPMQ_MESSAGE_ENABLE') && STOMPMQ_MESSAGE_ENABLE === TRUE ){
				$this->checkSendReactionToActiveMQ(array('CmpCallCard_id' => $data['CmpCallCard_id']));
			}
			//$this->setCmpCallCardEvent( $data );

			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает идентификатор из справочника статусов бригад по его коду
	 *
	 * @param $emergencyTeamStatus_id $code Код статуса
	 * @return int в случае успеха или false
	 */
	public function getCmpCallCardEventTypeIdByEmergencyTeamStatusId( $emergencyTeamStatus_id ){

		$sql = "SELECT TOP 1 CmpCallCardEventType_id FROM v_EmergencyTeamStatus with(nolock) WHERE EmergencyTeamStatus_id=:EmergencyTeamStatus_id";
		$query = $this->db->query( $sql, array(
			'EmergencyTeamStatus_id' => $emergencyTeamStatus_id
		) );
		if ( is_object( $query ) ) {
			$result = $query->first_row('array');
			return $result['CmpCallCardEventType_id'];
		}

		return false;
	}

	/**
	* Запись события карты в журнал. Обработка данных. Выявление статуса
	*/
	public function setCmpCallCardEvent($data){

		if ( !isset($data['CmpCallCard_id']) ) {
			return false;
		}

		//необходимая информация о вторичной карте
		$cardInfo = $this->getCardParamsForEvent($data);

		if ( $cardInfo === false ) {
			return false;
		}

		$CmpCallCardEventType_id = !empty($data['CmpCallCardEventType_id']) ? $data['CmpCallCardEventType_id'] : null;

		//@todo если приходит код события, записываем по коду события
		//тк бывают случаи, что событие должно сохраняться без смены статуса
		if(!empty($data["CmpCallCardEventType_Code"])){
			//возможность проставлять событие по коду
			$evtTypeCardQuery = "SELECT TOP 1 * FROM v_CmpCallCardEventType with(nolock) WHERE CmpCallCardEventType_Code = :CmpCallCardEventType_Code";
			$evtTypeCard = $this->db->query($evtTypeCardQuery, $data)->row_array();
			if(!empty($evtTypeCard["CmpCallCardEventType_id"])){
				$data['CmpCallCardEventType_id'] = $evtTypeCard['CmpCallCardEventType_id'];
				$CmpCallCardEventType_id = $evtTypeCard['CmpCallCardEventType_id'];
			}
		}



		$comment = '';
		if(!empty($data['CmpCallCardEvent_Comment'])){
			$comment = $data['CmpCallCardEvent_Comment'];
		}

		if(empty($CmpCallCardEventType_id)){

			//определяем CmpCallCardEventType_id
			switch($cardInfo["CmpCallCardStatusType_id"]){
				case null:
				case 1:
				{
					//Передан на подстанцию
					if( isset($cardInfo['Lpu_ppdid']) ){
						//НМП
						$CmpCallCardEventType_id = 2;
					}
					else{
						//СМП
						$CmpCallCardEventType_id = 1;
					}
					break;
				}
				case 2:{
					if( isset($cardInfo['Lpu_ppdid']) ){
						//Принято НМП
						$CmpCallCardEventType_id = 6;
					}
					else{
						//Назначена бригада
						$CmpCallCardEventType_id = 4;

						if(isset($cardInfo['EmergencyTeamStatus_Code'])){
							if($cardInfo['EmergencyTeamStatus_Code'] == 48){
								//Статус бригады, назначенной на вызов, изменился на «Принял вызов»
								$CmpCallCardEventType_id = 5;
							};
							if($cardInfo['EmergencyTeamStatus_Code'] == 14 || $cardInfo['EmergencyTeamStatus_Code'] == 1){
								//Статус бригады, назначенной на вызов, изменился на «Выехал на вызов»
								$CmpCallCardEventType_id = 7;
							};
							if($cardInfo['EmergencyTeamStatus_Code'] == 15 || $cardInfo['EmergencyTeamStatus_Code'] == 2){
								//Статус бригады, назначенной на вызов, изменился на «Прибыл на место вызова»
								$CmpCallCardEventType_id = 8;
							};
							if($cardInfo['EmergencyTeamStatus_Code'] == 3){
								//Статус бригады, назначенной на вызов, изменился на «Госпитализация/Перевозка»
								$CmpCallCardEventType_id = 9;
							};
							if($cardInfo['EmergencyTeamStatus_Code'] == 4){
								//Статус бригады, назначенной на вызов, изменился на «Конец обслуживания»
								$CmpCallCardEventType_id = 10;
							};
							if($cardInfo['EmergencyTeamStatus_Code'] == 17){
								//Статус бригады, назначенной на вызов, изменился на «Прибытие в МО»
								$CmpCallCardEventType_id = 11;
							};
						}
					}
					break;
				}
				case 4:{
					//Вызов принял статус «4. Обслужено»
					$CmpCallCardEventType_id = 13;
					break;
				}
				case 5:{
					//Вызов принял статус «5. Отказ»
					$CmpCallCardEventType_id = 15;
					break;
				}
				case 6:
				{
					//Закрытие карты

					//Возвращение бригады
					//При сохранении даты и времени в поле «Возвращения на станцию» формы «Информация о вызове»
					$dparams = array(
						'CmpCallCardEventType_id' => 12,
						'CmpCallCardStatus_id' => (!empty($cardInfo['CmpCallCardStatus_id']) && $cardInfo["CmpCallCardStatus_id"] != '')?$cardInfo['CmpCallCardStatus_id']:null,
						'EmergencyTeamStatusHistory_id' => (!empty($cardInfo['EmergencyTeamStatusHistory_id']) && $cardInfo["EmergencyTeamStatusHistory_id"] != '')?$cardInfo['EmergencyTeamStatusHistory_id']:null,
						'LpuBuilding_id' => (!empty($cardInfo['LpuBuilding_id']) && $cardInfo["LpuBuilding_id"] != '')?$cardInfo['LpuBuilding_id']:null,
						'LpuSection_id' => (!empty($cardInfo['LpuSection_id']) && $cardInfo["LpuSection_id"] != '')?$cardInfo['LpuSection_id']:null,
						'EmergencyTeam_id' => (!empty($cardInfo['EmergencyTeam_id']) && $cardInfo["EmergencyTeam_id"] != '')?$cardInfo['EmergencyTeam_id']:null,
						'CmpCallCardEvent_Comment' => $comment,
						'pmUser_id' => $data['pmUser_id'],
						'CmpCallCardEvent_setDT' => (!empty($data["BackTime"]) && $data["BackTime"] != '')?$data["BackTime"]:null,
						'CmpCallCard_id' => (!empty($cardInfo['CmpCallCard_id']) && $cardInfo["CmpCallCard_id"] != '')?$cardInfo['CmpCallCard_id']:null
					);

					$this -> saveCmpCallCardEvent($dparams);

					//Вызов принял статус «6. Закрыто»
					$CmpCallCardEventType_id = 14;

					break;
				}
				case 16:
				{
					//Дублирующее обращение, регистрация
					//Здесь регистрируем события, произошедшие с повторным вызовом, на первичный вызов
					//Чтобы потом не запутаться - событие произошло с дублирующим а мы регистрируем событие для первичного
					$CmpCallCardEventType_id = 20;

					break;
				}
				case 18:
				{
					//Дублирующий вызов - Передан для решения старшего врача
					$CmpCallCardEventType_id = 3;

					//Первичный - Дублирующее обращение, регистрация
					//Здесь регистрируем события, произошедшие с повторным вызовом, на первичный вызов
					//Чтобы потом не запутаться - событие произошло с дублирующим а мы регистрируем событие для первичного
					if($cardInfo['CmpCallCard_rid']){
						//по умолчанию
						$ParentCmpCallCardEventType_id = 16;

						//а тут идет ранжирование статусов по типу обращения

						if($cardInfo['CmpCallType_Code'] == 14 ){
							//Дублирующее обращение, регистрация
							$ParentCmpCallCardEventType_id = 16;
						};

						if($cardInfo['CmpCallType_Code'] == 17 ){
							//Отмена вызова, регистрация
							$ParentCmpCallCardEventType_id = 17;
						};

						if($cardInfo['CmpCallType_Code'] == 9 ){
							//Создание вызова спец. бригады, регистрация
							$ParentCmpCallCardEventType_id = 18;
						};

						if($cardInfo['CmpCallType_Code'] == 4 ){
							//Создание попутного вызова
							$ParentCmpCallCardEventType_id = 19;
						};

						//сохранение события для первичного вызова
						$parentCardInfo = $this->getCardParamsForEvent(array('CmpCallCard_id'=>$data['CmpCallCard_rid']));

						$parentParams = array(
							'CmpCallCardEventType_id' => $ParentCmpCallCardEventType_id,
							'CmpCallCardStatus_id' => (!empty($parentCardInfo["CmpCallCardStatus_id"]) && $parentCardInfo["CmpCallCardStatus_id"] != '')?$parentCardInfo["CmpCallCardStatus_id"]:null,
							'EmergencyTeamStatusHistory_id' => (!empty($parentCardInfo["EmergencyTeamStatusHistory_id"]) && $parentCardInfo["EmergencyTeamStatusHistory_id"] != '')?$parentCardInfo["EmergencyTeamStatusHistory_id"]:null,
							'LpuBuilding_id' => (!empty($parentCardInfo["LpuBuilding_id"]) && $parentCardInfo["LpuBuilding_id"] != '')?$parentCardInfo["LpuBuilding_id"]:null,
							'LpuSection_id' => (!empty($parentCardInfo["LpuSection_id"]) && $parentCardInfo["LpuSection_id"] != '')?$parentCardInfo["LpuSection_id"]:null,
							'EmergencyTeam_id' => (!empty($parentCardInfo["EmergencyTeam_id"]) && $parentCardInfo["EmergencyTeam_id"] != '')?$parentCardInfo["EmergencyTeam_id"]:null,
							'pmUser_id' => $data['pmUser_id'],
							'CmpCallCard_id' => $data['CmpCallCard_rid'],
							'CmpCallCard_cid' => $data['CmpCallCard_id'],
							'CmpCallCardEvent_Comment' => $comment
						);
						$this -> saveCmpCallCardEvent($parentParams);
					};

					break;
				}
			}
		}

		$params = array(
			'CmpCallCardEventType_id' => $CmpCallCardEventType_id,
			'CmpCallCardStatus_id' => (!empty($cardInfo["CmpCallCardStatus_id"]) && $cardInfo["CmpCallCardStatus_id"] != '')?$cardInfo["CmpCallCardStatus_id"]:null,
			'EmergencyTeamStatusHistory_id' => (!empty($cardInfo["EmergencyTeamStatusHistory_id"]) && $cardInfo["EmergencyTeamStatusHistory_id"] != '')?$cardInfo["EmergencyTeamStatusHistory_id"]:null,
			'LpuBuilding_id' => (!empty($cardInfo["LpuBuilding_id"]) && $cardInfo["LpuBuilding_id"] != '')?$cardInfo["LpuBuilding_id"]:null,
			'LpuSection_id' => (!empty($cardInfo["LpuSection_id"]) && $cardInfo["LpuSection_id"] != '')?$cardInfo["LpuSection_id"]:null,
			'EmergencyTeam_id' => (!empty($cardInfo["EmergencyTeam_id"]) && $cardInfo["EmergencyTeam_id"] != '')?$cardInfo["EmergencyTeam_id"]:null,
			'pmUser_id' => $data['pmUser_id'],
			'CmpCallCardEvent_Comment' => $comment,
			'CmpCallCard_id' => (!empty($cardInfo["CmpCallCard_id"]) && $cardInfo["CmpCallCard_id"] != '')?$cardInfo["CmpCallCard_id"]:null,
		);

		return $this -> saveCmpCallCardEvent($params);
	}

	/**
	 * функция проверяет изменения по карте
	 * и регистрирует событие Корректировка вызова
	 */
	public function checkChangesCmpCallCard($oldCard, $newCard){
		$changed = false;
		$editableParameters = array(
			'CmpCallCard_Tper',
			'CmpCallCard_Vyez',
			'CmpCallCard_Przd',
			'CmpCallCard_Tisp',
			'CmpCallCard_HospitalizedTime',
			'CmpCallCard_Comm'
		);
		foreach ($newCard as $key => $value)
		{
			if(isset($oldCard[$key])){

				if($oldCard[$key] instanceof DateTime && !($newCard[$key] instanceof DateTime)){
					$newCard[$key] = new DateTime($newCard[$key]);
				}
				if($oldCard[$key] != $newCard[$key])
				{
					$changed = true;
					break;
				}
			}
			elseif(in_array ($key, $editableParameters)) {
				$changed = true;
				break;
			}
		};

		if($changed){

			$eventParams = array(
				"CmpCallCard_id" => $newCard["CmpCallCard_id"],
				"CmpCallCardEventType_Code" => 32,
				"CmpCallCardEvent_Comment" => 'Корректировка вызова',
				"pmUser_id" => $newCard["pmUser_id"]
			);

			$this->setCmpCallCardEvent( $eventParams );

		}

		return $changed;
	}

	/**
	* Запрос на выборку параметров карты для последующей обработки
	*/
	private function getCardParamsForEvent($data){

		//время возвращения на подстанцию в разных регионах своё
		if ( $this->regionNick == 'kz' ) {
			$backToHospitalTime = "convert(varchar(10), ClCCC.ToHospitalTime, 104)+' '+convert(varchar(5), cast(ClCCC.ToHospitalTime as datetime), 108) as ToHospitalTime";
		}
		else{
			$backToHospitalTime = "convert(varchar(10), ClCCC.BackTime, 104)+' '+convert(varchar(5), cast(ClCCC.BackTime as datetime), 108) as BackTime";
		}

		//выбираем карту
		$query = "
			SELECT
				CCC.CmpCallCard_id,
				CCC.CmpCallCard_rid,
				CCC.CmpCallCardStatus_id,
				CCC.LpuBuilding_id,
				CCC.LpuSection_id,
				CCC.EmergencyTeam_id,
				CCC.Lpu_ppdid,
				CCC.CmpCallCardStatusType_id,
				ET.EmergencyTeamStatusHistory_id,
				ETS.EmergencyTeamStatus_Code,
				CCT.CmpCallType_Code,
				{$backToHospitalTime}
			FROM v_CmpCallCard CCC with (nolock)
				LEFT JOIN v_EmergencyTeam ET with (nolock) on CCC.EmergencyTeam_id = ET.EmergencyTeam_id
				LEFT JOIN v_EmergencyTeamStatus ETS with (nolock) on ET.EmergencyTeamStatus_id = ETS.EmergencyTeamStatus_id
				LEFT JOIN v_CmpCloseCard ClCCC with (nolock) on CCC.CmpCallCard_id = ClCCC.CmpCallCard_id
				LEFT JOIN v_CmpCallType CCT with (nolock) on CCC.CmpCallType_id = CCT.CmpCallType_id
			WHERE CCC.CmpCallCard_id = :CmpCallCard_id
		";

		$preres = $this->db->query($query, $data);
		$preres = $preres->result('array');

		if ( is_array($preres) && count($preres) > 0 ) {
			return $preres[0];
		}
		else {
			return false;
		}
	}

	/**
	* Запрос на запись события карты
	*/
	private function saveCmpCallCardEvent($qparams){

		$fields = "";
		if(isset($qparams['LpuSection_id']) && $qparams['LpuSection_id'] != '' && $qparams['LpuSection_id']>0)
			$fields .= " @LpuSection_id = :LpuSection_id, ";
		else
			$fields .= " @LpuSection_id = null, ";
		if(!empty($qparams['CmpCallCardEvent_Comment']))
			$fields .= " @CmpCallCardEvent_Comment = :CmpCallCardEvent_Comment, ";
		else
			$fields .= " @CmpCallCardEvent_Comment = null, ";

		$query = "
			declare
			@Res bigint,
			@ErrCode int,
			@ErrMessage varchar(4000);
		exec p_CmpCallCardEvent_ins
			@CmpCallCardEventType_id = :CmpCallCardEventType_id,
			@CmpCallCardStatus_id = :CmpCallCardStatus_id,
			@EmergencyTeamStatusHistory_id = :EmergencyTeamStatusHistory_id,
			@LpuBuilding_id = :LpuBuilding_id,
			".$fields."
			@EmergencyTeam_id = :EmergencyTeam_id,
			@pmUser_id = :pmUser_id,
			@CmpCallCard_id = :CmpCallCard_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output;
		select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $qparams);

		return $result;
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
				SELECT TOP 1
					CASE WHEN isnull(CmpCallCard_IsOpen,1)=2
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
					end as CmpGroup_id
				FROM
					v_CmpCallCard with(nolock)
				WHERE
					CmpCallCard_id = :CmpCallCard_id
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
				SELECT TOP 1
					CmpCallCard_IsOpen,
					Lpu_id,
					CmpCallCardStatusType_id,
					CmpCallCard_IsEmergency
				FROM
					v_CmpCallCard with(nolock)
				WHERE
					CmpCallCard_id = :CmpCallCard_id
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
				SELECT TOP 1
					CASE WHEN ISNULL(CmpCallCard_IsOpen,1)=2 THEN
						/* Записи принятые в ППД */
						CASE WHEN CmpCallCard_IsReceivedInPPD=2 THEN
							CASE WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id+3 WHEN CmpCallCardStatusType_id=4 THEN 3+3 ELSE 7 END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_id становится равной ноля */
						ELSE
							CASE WHEN CmpCallCardStatusType_id IN(1,2) THEN CmpCallCardStatusType_id WHEN CmpCallCardStatusType_id=4 THEN 3 ELSE 7 END /* в случае вовзрата в СМП здесь не должно быть записи, т.к. Lpu_id становится равной ноля */
						END
					ELSE 7 END as CmpGroup_id
				FROM
					v_CmpCallCard with(nolock)
				WHERE
					CmpCallCard_id = :CmpCallCard_id
					AND Lpu_id IS NOT NULL
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
				select top 1
					CmpCallCard_IsOpen
					,Lpu_id
					,CmpCallCardStatusType_id
					,CmpCallCard_IsEmergency
				from
					v_CmpCallCard with(nolock)
				where
					CmpCallCard_id = :CmpCallCard_id
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
	function setIsOpenCmpCallCard($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpCallCard_id;
			exec p_CmpCallCard_setIsOpen
				@CmpCallCard_id = @Res,
				@CmpCallCard_IsOpen = :CmpCallCard_IsOpen,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function setResult($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

		if( (int) $data['CmpPPDResult_id'] == 0 ) {
			$data['CmpPPDResult_id'] = null;
		}

		//возможность проставлять статус по коду
		if( empty($data['CmpPPDResult_id']) && !empty($data['CmpPPDResult_Code']) ) {
			$statusQuery = "SELECT TOP 1 * FROM v_CmpPPDResult with(nolock) WHERE CmpPPDResult_Code = :CmpPPDResult_Code";
			$status = $this->db->query($statusQuery, $data)->row_array();
			if(!empty($status["CmpPPDResult_id"])){
				$data['CmpPPDResult_id'] = $status['CmpPPDResult_id'];
			}
			else{
				return array('success' => false, 'Error_Code' => null, 'Error_Msg' => 'Не код или id статуса карты');
			}
		}

		//var_dump($data['CmpPPDResult_id']); exit;
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpCallCard_id;
			exec p_CmpCallCard_setPPDResult
				@CmpCallCard_id = @Res,
				@CmpPPDResult_id = :CmpPPDResult_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $data); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает дополнительную информацию по карте вызова
	 *
	 * @param array $data
	 * @return array or false
	 */
	public function getAdditionalCallCardInfo( $data ){
		$sql = "
			SELECT
				CASE
					WHEN CCrT.CmpCallerType_id IS NOT NULL THEN 'Вызывает: ' + CCrT.CmpCallerType_Name
					WHEN CCC.CmpCallCard_Ktov IS NOT NULL THEN 'Вызывает: ' + CCC.CmpCallCard_Ktov
					ELSE ''
				END
				+ CASE WHEN CCC.CmpCallCard_Telf IS NOT NULL THEN 'Телефон: ' + CCC.CmpCallCard_Telf ELSE '' END as CallerInfo,

				ISNULL(CCC.Sex_id,0) as SexId
				,case when DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id
				else case when DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then AgeTypeValue.CmpCloseCardCombo_id+1
					else AgeTypeValue.CmpCloseCardCombo_id+2 end
				end as AgeTypeValue
				,case when DATEDIFF(yy,ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,'01.01.2000')),dbo.tzGetDate())>1 then
					case when ISNULL(PS.Person_BirthDay, ISNULL(CCC.Person_BirthDay,0))=0 then ''
					else DATEDIFF(yy,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end
				else case when DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())>1 then DATEDIFF(mm,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate())
							else DATEDIFF(dd,ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay),dbo.tzGetDate()) end
				end as Age
			FROM
				v_CmpCallCard CCC with (nolock)
				LEFT JOIN v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
				LEFT JOIN v_CmpCallerType CCrT with (nolock) on CCrT.CmpCallerType_id=CCC.CmpCallerType_id
				outer apply (
					select top 1
						CCCC.CmpCloseCardCombo_id
					from
						{$this->comboSchema}.v_CmpCloseCardCombo CCCC with (nolock)
					where
						CCCC.Parent_id = 218
					order by
						CCCC.CmpCloseCardCombo_id asc
				) as AgeTypeValue
			WHERE
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
	 * Отправка пуша при назначении бригады на вызов
	 * @data:
	 * - CmpCallCard_id
	 * - EmergencyTeam_id
	 * - pmUser_id
	 */
	function sendPushOnSetMergencyTeam($data) {
		$this->load->library('textlog', array('file'=>'sendPushOnSetMergencyTeam_'.date('Y-m-d').'.log'));
		$this->load->helper('Push');

		// получаем данные
		$resp_cc = $this->queryResult("
			select top 1
				ccc.CmpCallCard_id, -- 1. id вызова
				ccc.CmpCallCard_Numv, -- 2. номер вызова за день
				ccc.CmpCallCard_Telf, -- 3. телефон вызывающего
				ISNULL(cct.CmpCallerType_Name, ccc.CmpCallCard_Ktov) as CmpCallCard_Ktov, -- 4. кто вызывает
				ccpt.CmpCallPlaceType_Name, -- 5. тип места вызова
				ccc.CmpCallCard_defCom, -- 6. комментарий
				UAD.UnformalizedAddressDirectory_lat as lat, -- 7. координаты места вызова
				UAD.UnformalizedAddressDirectory_lng as lng,
				case when ccc.CmpCallCard_IsExtra = 2 then 'true' else 'false' end as CmpCallCard_IsExtra, -- 8. тип вызова - экстренный или неотложный (тип Boolean)
				ccc.KLCity_id, -- 9. место вызова - город (id, name)
				KLC.KLCity_Name,
				ccc.KLStreet_id, -- 10. место вызова - улица (id, name)
				KLS.KLStreet_Name,
				ccc.KLTown_id, -- 11. место вызова - нас. Пункт (id, name)
				KLT.KLTown_Name,
				ccc.CmpCallCard_Dom, -- 12. место вызова - дом
				ccc.CmpCallCard_Kvar, -- 13. место вызова - квартира
				ccc.CmpCallCard_Urgency, -- 14. срочность вызова
				ccc.CmpCallCardStatusType_id, -- 15. статус вызова
				cccst.CmpCallCardStatusType_Code,
				cccst.CmpCallCardStatusType_Name,
				ccc.Person_id, -- 16. Person(id)
				ccc.CmpReason_id, -- 17. Повод вызова (id, code, name)
				cr.CmpReason_Code,
				cr.CmpReason_Name,
				ccc.CmpCallType_id, -- 18. вид вызова (id, code, name)
				ccallt.CmpCallType_Code,
				ccallt.CmpCallType_Name,
				convert(varchar(19), ccc.CmpCallCard_prmDT, 120) as CmpCallCard_prmDT, -- 19. время приёма вызова
				convert(varchar(19), trans.CmpCallCard_transDT, 120) as CmpCallCard_transDT -- 20. время передачи вызова
			from
				v_CmpCallCard ccc (nolock)
				left join v_CmpCallerType cct with (nolock) on cct.CmpCallerType_id = ccc.CmpCallerType_id
				left join v_CmpCallPlaceType ccpt with (nolock) on ccpt.CmpCallPlaceType_id = ccc.CmpCallPlaceType_id
				left join v_UnformalizedAddressDirectory UAD with (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
				left join v_CmpCallCardStatusType cccst with (nolock) on cccst.CmpCallCardStatusType_id = CCC.CmpCallCardStatusType_id
				left join v_CmpReason cr with (nolock) on cr.CmpReason_id = ccc.CmpReason_id
				left join v_CmpCallType ccallt with (nolock) on ccallt.CmpCallType_id = ccc.CmpCallType_id
				left join v_KLCity KLC (nolock) on KLC.KLCity_id = CCC.KLCity_id
				left join v_KLTown KLT (nolock) on KLT.KLTown_id = CCC.KLTown_id
				left join v_KLStreet KLS (nolock) on KLS.KLStreet_id = CCC.KLStreet_id
				outer apply(
					select top 1
						CmpCallCardStatus_insDT as CmpCallCard_transDT
					from
						v_CmpCallCardStatus with(nolock)
						left join v_PmUser PU with(nolock) on PU.PMUser_id = pmUser_insID
					where
						CmpCallCardStatusType_id = 2 and CmpCallCard_id = CCC.CmpCallCard_id
					order by CmpCallCardStatus_insDT desc
				) as trans
			where
				ccc.CmpCallCard_id = :CmpCallCard_id
		", array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

		if (!empty($resp_cc[0]['CmpCallCard_id'])) {
			// ищем последний токен у старшего бригады
			$resp_pucd = $this->queryResult("
				select top 1
					pucd.pmUserCacheDevice_DeviceID
				from
					v_EmergencyTeam et (nolock)
					inner join v_pmUserCache puc (nolock) on puc.MedPersonal_id = et.EmergencyTeam_HeadShift
					inner join v_pmUserCacheDevice pucd (nolock) on puc.pmUser_id = pucd.PMUserCache_id
				where
					et.EmergencyTeam_id = :EmergencyTeam_id
					and pucd.pmUserCacheDevice_DeviceID is not null
				order by
					pucd.pmUserCacheDevice_insDT
			", array(
				'EmergencyTeam_id' => $data['EmergencyTeam_id']
			));
			foreach($resp_pucd as $one_pucd) {
				$this->textlog->add("");
				$this->textlog->add("");
				$to = $one_pucd['pmUserCacheDevice_DeviceID'];
				$params = array(
					'message' => json_encode($resp_cc[0])
				);

				$apiKey = $this->config->item('FCM_API_KEY');
				$result = sendPush($to, $params, $apiKey);
				$this->textlog->add("to: " . $to . " apiKey: " . $apiKey);
				$message_id = null;
				if (!empty($result) && mb_strpos($result, 'message_id') !== false) {
					$push_result = json_decode($result, true);
					if (!empty($push_result['results'][0]['message_id'])) {
						$message_id = $push_result['results'][0]['message_id'];
					}
				}
				$this->saveCmpCallCardMessage(array(
					'Message_id' => $message_id,
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				$this->textlog->add("result: " . $result);

				$apiKey = $this->config->item('FCM_API_KEY_NMP');
				$result = sendPush($to, $params, $apiKey);
				$this->textlog->add("to: " . $to . " apiKey: " . $apiKey);
				$message_id = null;
				if (!empty($result) && mb_strpos($result, 'message_id') !== false) {
					$push_result = json_decode($result, true);
					if (!empty($push_result['results'][0]['message_id'])) {
						$message_id = $push_result['results'][0]['message_id'];
					}
				}
				$this->saveCmpCallCardMessage(array(
					'Message_id' => $message_id,
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				$this->textlog->add("result: " . $result);
			}
		} else {
			$this->textlog->add("Ошибка получения данных по CmpCallCard_id=".$data['CmpCallCard_id']);
		}
	}

	/**
	 * Сохранение пуш-уведомления в историю
	 */
	function saveCmpCallCardMessage($data) {
		$query = "
			declare
				@curdate datetime = dbo.tzGetDate(),
				@CmpCallCardMessage_id bigint = null,
				@Error_Code int,
				@Error_Message varchar(4000);

			exec p_CmpCallCardMessage_ins
				@CmpCallCardMessage_id = @CmpCallCardMessage_id output,
				@Message_id = :Message_id,
				@CmpCallCard_id = :CmpCallCard_id,
				@CmpCallCardMessage_webDT = @curdate,
				@CmpCallCardMessage_tabletDT = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, $data);
	}

	/**
	 * default desc
	 */
	function setEmergencyTeam($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

		if( (int) $data['EmergencyTeam_id'] == 0 ) {
			$data['EmergencyTeam_id'] = null;
		}
		$query = "

			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Status int;
			set @Res = :CmpCallCard_id;
			set @Status=(select CmpCallCardStatusType_id from CmpCallCard with(nolock) where CmpCallCard_id = @Res);
		--	if @Status in (2,4)
		--		begin
		--			set @ErrCode = 0;
		--			set @ErrMessage = 'Вызов принят или обслужен';
		--		end
		--	else
		--		begin
				exec p_CmpCallCard_setEmergencyTeam
					@CmpCallCard_id = @Res,
					@EmergencyTeam_id = :EmergencyTeam_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
		--		end
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo getDebugSQL($query, $data); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {

			$setCmpCallCardTper = "
				update CmpCallCard
				set CmpCallCard_Tper = dbo.tzGetDate()
				where CmpCallCard_id = :CmpCallCard_id
			";
			$this->db->query($setCmpCallCardTper, $data);

			$resp = $result->result('array');
			if (!empty($resp[0]['Error_Msg'])) {
				return $resp;
			} else {
				// отправляем PUSH
				$this->sendPushOnSetMergencyTeam(array(
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'EmergencyTeam_id' => $data['EmergencyTeam_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
			return $resp;
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function setLpuTransmit($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

		if( (int) $data['Lpu_ppdid'] == 0 ) {
			$data['Lpu_ppdid'] = null;
		}
		$query = "

			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Status int,
				@PersonIdentifyed int,
				@PersonAge int;

			set @Res = :CmpCallCard_id;

			select  @Status = CmpCallCardStatusType_id, @PersonIdentifyed = ISNULL(Person_id,0), @PersonAge = ISNULL(Person_Age,1)
			from
				v_CmpCallCard (nolock)
			where
				CmpCallCard_id = @Res;

			if @PersonIdentifyed = 0
				begin
					set @ErrCode = 0;
					set @ErrMessage = 'Вызов не может быть принят в НМП. Пациент не идентифицирован';
				end
			else begin
				if @PersonAge = 0 begin
					set @ErrCode = 0;
					set @ErrMessage = 'Вызов не может быть передан в НМП. Пациенты до года обслуживаются в СМП';
				end
				else begin
					if @Status in (2,4)	begin
							set @ErrCode = 0;
							set @ErrMessage = 'Вызов принят или обслужен';
					end
					else begin
						exec p_CmpCallCard_setLpuPpd
							@CmpCallCard_id = @Res,
							@Lpu_ppdid = :Lpu_ppdid,
							@pmUser_id = :pmUser_id,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMessage output;
					end
				end
			end
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo getDebugSQL($query, $data); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function setLpuId($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@Status int;
			set @Res = :CmpCallCard_id;
			exec p_CmpCallCard_setLpu
				@CmpCallCard_id = @Res,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCard_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function setPerson($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Карта вызова редактируется другим пользователем' ) );
		}

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

		//echo getDebugSQL($query, $data); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function identifiPerson($data) {
		$filter = "1=1";

		if( !empty($data['Person_Surname']) ) {
			$filter .= " and Person_SurName like :Person_Surname + '%'";
		}
		if( !empty($data['Person_Firname']) ) {
			$filter .= " and Person_FirName like :Person_Firname + '%'";
		}
		if( !empty($data['Person_Secname']) ) {
			$filter .= " and Person_SecName like :Person_Secname + '%'";
		}
		if( !empty($data['Person_Birthday']) ) {
			$filter .= " and Person_BirthDay = :Person_Birthday";
		}
		if( !empty($data['Person_Age']) ) {
			$filter .= " and dbo.Age(Person_BirthDay, dbo.tzGetDate()) = :Person_Age";
		}
		if( !empty($data['Polis_Ser']) ) {
			$filter .= " and Polis_Ser like :Polis_Ser";
		}
		if( !empty($data['Polis_Num']) ) {
			$filter .= " and Polis_Num = :Polis_Num";
		}
		if( !empty($data['Sex_id']) ) {
			$filter .= " and Sex_id = :Sex_id";
		}

		$query = "
			select top 101
				Person_id
				,Person_SurName as Person_Surname
				,Person_FirName as Person_Firname
				,Person_SecName as Person_Secname
				,convert(varchar(10), cast(Person_BirthDay as datetime), 104) as Person_Birthday
				,dbo.Age(Person_BirthDay, dbo.tzGetDate()) as Person_Age
				,Polis_Ser
				,Polis_Num
				,Sex_id
			from
				v_PersonState with(nolock)
			where
				{$filter}
		";
		//echo getDebugSQL($query, $data); exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Снятие статус "отказ" карты вызова
	*/
	function unrefuseCmpCallCard($data) {
		// Находим последнюю запись о статусе
		$q = "
			select top 1
				CmpCallCardStatus_id
			from
				v_CmpCallCardStatus with(nolock)
			where
				CmpCallCard_id = :CmpCallCard_id
			order by
				CmpCallCardStatus_insDT desc
		";
		$result = $this->db->query($q, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		//print_r($result); exit();
		if( count($result) > 0 ) {
			if( $this->deleteCmpCallCardStatus($result[0]) === false ) {
				return array(array('success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
			}
		}
		$this->setStatusCmpCallCard(array(
			'CmpCallCard_id'			=> $data['CmpCallCard_id'],
			'CmpCallCardStatusType_id'	=> 0,
			'CmpCallCardStatus_Comment'	=> null,
			'pmUser_id'					=> $data['pmUser_id']
		));
		return array(array('success' => true));


	}
	/**
	 * default desc
	 */
	function setPPDWaitingTime($data) {
		if (!$data['PPD_WaitingTime']) {
			 return array(array('success' => false, 'Error_Msg' => 'Не введено время ожидания'));
		}
		elseif (!$data['Password']) {
			return array(array('success' => false, 'Error_Msg' => 'Не введен пароль от учётной записи'));
		}
		elseif (!$data['session']['login']) {
			return array(array('success' => false, 'Error_Msg' => 'Пользователь не идентифицирован'));
		}

		$user = pmAuthUser::find($data['session']['login']);
		if (substr($data['Password'],0,5)<>"{MD5}")
			$data['Password'] = "{MD5}".base64_encode(md5($data['Password'],TRUE));
		if ($user->pass !==  $data['Password']) {
			return array(array('success' => false, 'Error_Msg' => 'Пароль от учётной записи введён неверно'));
		}

		$SetValueQuery = "
			Declare @DataStorage_id bigint;
			Declare @Error_Code int;
			Declare @Error_Message varchar(4000);
			Declare @Lpu_id bigint;
			Set @DataStorage_id = Null;
			Set @Error_Code = 0;
			Set @Error_Message = '';
			exec p_DataStorage_set @DataStorage_id=@DataStorage_id output, @Lpu_id = 0, @DataStorage_Name = 'cmp_waiting_ppd_time', @DataStorage_Value= :PPD_WaitingTime, @pmUser_id=:pmUser_id, @Error_Code = @Error_Code output, @Error_Message = @Error_Message output;
			select @DataStorage_id as DataStorage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		$result = $this->db->query($SetValueQuery,$data);
		$result_arr = $result->result('array');
		//$result_arr[0]['success'] = ( ($result_arr[0]['Error_Code']==0)&&($result_arr[0]['Error_Message']=="") )?true:false;
		//echo $result_arr[0]['success'].' '.$result_arr[0]['Error_Code'].' '.$result_arr[0]['Error_Message'];
		return $result_arr;
	}

	/**
	 *	Смена пациента в карте вызова
	 */
	function setAnotherPersonForCmpCallCard($data) {

		$checkLock = $this->checkLockCmpCallCard($data);
		if ($checkLock!= false && is_array($checkLock) && isset($checkLock[0]) && isset($checkLock[0]['CmpCallCard_id'])) {
			return array( array( 'Error_Msg' => 'Невозможно сохранить. Карта вызова редактируется другим пользователем' ) );
		}

		$query = "
			declare
				@Error_Code bigint = null,
				@Error_Message varchar(4000) = '';

			set nocount on

			begin try
				update CmpCallCard
				set  Person_id = :Person_id
					,pmUser_updID = :pmUser_id
					,CmpCallCard_updDT = getdate()
					,CmpCallCard_IsInReg = 1
				where CmpCallCard_id = :CmpCallCard_id
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
			end catch

			set nocount off

			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$queryParams = array(
			 'CmpCallCard_id' => $data['CmpCallCard_id']
			,'Person_id' => $data['Person_id']
			,'pmUser_id' => $data['pmUser_id']
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
	 *	Привязка забронированной экстренной койки к карте вызова
	 */
	function setCmpCloseCardTimetable($data) {
		if (!(isset($data['CmpCallCard_id'])&&isset($data['TimetableStac_id'])&&($data['CmpCallCard_id']>0)&&($data['TimetableStac_id']>0))) {
			return false;
		}
		if (!isset($data['CmpCloseCardTimetable_id'])) {
			$data['CmpCloseCardTimetable_id'] = null;
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpCloseCardTimetable_id;
			exec p_CmpCloseCardTimetable_ins
				@CmpCloseCardTimetable_id = @Res,
				@CmpCallCard_id = :CmpCallCard_id,
				@TimetableStac_id = :TimetableStac_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCloseCardTimetable_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	/**
	 * default desc
	 */
	function getCmpCallCardNgod($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}
		$query = "select
					CCC.CmpCallCard_Ngod
				from
					v_CmpCallCard CCC with (nolock)
				where
					CCC.CmpCallCard_id = :CmpCallCard_id";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$retrun = $result->result('array');
			return $retrun[0]['CmpCallCard_Ngod'];
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function getLpuAddressTerritory($data) {
		if (!isset($data['Lpu_id'])) {
			return false;
		}
		/*
		$query = "select
					ISNULL(PAD.KLRGN_id,'0') as KLRGN_id,
					ISNULL(PAD.KLSubRGN_id,'0') as KLSubRGN_id,
					ISNULL(PAD.KLCity_id,'0') as KLCity_id,
					ISNULL(PAD.KLTown_id,'0') as KLTown_id
				from
					v_Lpu Lpu with (nolock)
					left join v_Org Org on Lpu.Org_id = Org.Org_id
					left join [Address] PAD with (nolock) on PAD.Address_id = Org.PAddress_id
				where
					Lpu.Lpu_id = :Lpu_id";
		*/
		$query = "select top 1

                    ISNULL(RGN.KLRGN_id,'0') as KLRGN_id,
					ISNULL(SRGN.KLSubRGN_id,'0') as KLSubRGN_id,
					ISNULL(City.KLCity_id,'0') as KLCity_id,
					ISNULL(Town.KLTown_id,'0') as KLTown_id
				from
					v_Lpu Lpu with (nolock)
					left join v_OrgServiceTerr OST with(nolock) on OST.Org_id = Lpu.Org_id
					left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = OST.KLRgn_id
					left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = OST.KLSubRgn_id
					left join v_KLCity City (nolock) on City.KLCity_id = OST.KLCity_id
					left join v_KLTown Town (nolock) on Town.KLTown_id = OST.KLTown_id
				where
					Lpu.Lpu_id = :Lpu_id";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function loadCmpCloseCardComboboxesViewForm($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}

		$query = "
			SELECT
				--CCC.CmpCloseCard_id,
				COALESCE(dboCCCMCode.CmpCloseCardCombo_Code,CCCM.CmpCloseCardCombo_Code,null) as CmpCloseCardCombo_id,
				CCCR.Localize
			FROM
				{$this->schema}.v_CmpCloseCard CCC with (nolock)
				LEFT JOIN {$this->schema}.v_CmpCloseCardRel CCCR with (nolock) on CCCR.CmpCloseCard_id = CCC.CmpCloseCard_id
				LEFT JOIN dbo.v_CmpCloseCardCombo dboCCCM with (nolock) on dboCCCM.CmpCloseCardCombo_id = CCCR.CmpCloseCardCombo_id
				LEFT JOIN dbo.v_CmpCloseCardCombo dboCCCMCode with (nolock) on dboCCCMCode.CmpCloseCardCombo_Code = CCCR.CmpCloseCardCombo_id
				LEFT JOIN {$this->comboSchema}.v_CmpCloseCardCombo CCCM with (nolock) on CCCM.CmpCloseCardCombo_Code = dboCCCM.CmpCloseCardCombo_Code
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
			";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$retrun = $result->result('array');
			return $retrun;
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function lockCmpCallCard($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		};
		$data['CmpCallCardLockList_id']=null;

		$queryForGettingId = "
			SELECT
				CCCLL.CmpCallCardLockList_id,
				CCCLL.pmUser_insID
			FROM
				v_CmpCallCardLockList CCCLL with (nolock)
			WHERE
				CCCLL.CmpCallCard_id = :CmpCallCard_id
				and 60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate()) >0
			";
		$resultGettingId = $this->db->query($queryForGettingId, $data);
		if ( is_object($resultGettingId) ) {
			$isLockedResult = $resultGettingId->result('array');
		} else {
			return false;
		}

		if (!$isLockedResult||!isset($isLockedResult[0])||!isset($isLockedResult[0]['CmpCallCardLockList_id'])||$isLockedResult[0]['CmpCallCardLockList_id']==null) {
			$procedure = 'p_CmpCallCardLockList_ins';
		} else {
			if ($isLockedResult[0]['pmUser_insID']!=$data['pmUser_id']){
				return array( array( 'success'=>false, 'Error_Msg' => 'Невозможно сохранить. Карта вызова редактируется другим пользователем' ) );
			}

			$procedure = 'p_CmpCallCardLockList_upd';
			$data['CmpCallCardLockList_id']=$isLockedResult[0]['CmpCallCardLockList_id'];
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpCallCardLockList_id;
			exec {$procedure}
				@CmpCallCardLockList_id = @Res,
				@CmpCallCard_id = :CmpCallCard_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCardLockList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	/**
	 * default desc
	 */
	function unlockCmpCallCard($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}

		$queryForGettingId = "
			SELECT
				CCCLL.CmpCallCardLockList_id
			FROM
				v_CmpCallCardLockList CCCLL with (nolock)
			WHERE
				CCCLL.CmpCallCard_id = :CmpCallCard_id
				and 60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate()) >0
				and CCCLL.pmUser_insID = :pmUser_id
			";
		$resultGettingId = $this->db->query($queryForGettingId, $data);
		if ( is_object($resultGettingId) ) {
			$isLockedResult = $resultGettingId->result('array');
		} else {
			return false;
		}

		if (!$isLockedResult||!$isLockedResult[0]||!$isLockedResult[0]['CmpCallCardLockList_id']||$isLockedResult[0]['CmpCallCardLockList_id']==null) {
			return false;
		}


		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_CmpCallCardLockList_del
				@CmpCallCardLockList_id = :CmpCallCardLockList_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'CmpCallCardLockList_id' => $isLockedResult[0]['CmpCallCardLockList_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Проверяет заблокирована или нет карта вызова
	 *
	 * @return массив в случае успеха или false
	 */
	function checkLockCmpCallCard($data) {
		if (!isset($data['CmpCallCard_id']) || !isset($data['pmUser_id'])) {
			return false;
		}
		$query = "
			SELECT DISTINCT
				CCCLL.CmpCallCard_id
				,CCCLL.CmpCallCardLockList_id
				,'' as Error_Msg
			FROM
				v_CmpCallCardLockList CCCLL with (nolock)
			WHERE
				CCCLL.CmpCallCard_id = :CmpCallCard_id
				and 60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate()) >0
				and CCCLL.pmUser_insID != :pmUser_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * default desc
	 */
	function clearCmpCallCardList() {
		$queryDeleteList = "
			SELECT
				CCCLL.CmpCallCardLockList_id
			FROM
				v_CmpCallCardLockList CCCLL with (nolock)
			WHERE
				60 - DATEDIFF(ss,CCCLL.CmpCallCardLockList_updDT,dbo.tzGetDate()) <=0
			";
		$resultDeleteList = $this->db->query($queryDeleteList);


		if ( is_object($resultDeleteList) ) {
			$resultDeleteList = $resultDeleteList->result('array');
		} else {
			return false;
		}

		if (count($resultDeleteList)==0) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}
		foreach ($resultDeleteList as $value) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_CmpCallCardLockList_del
					@CmpCallCardLockList_id = :CmpCallCardLockList_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, $value);
		}
		return array(array('success' => true, 'Error_Msg' => ''));
	}
	/**
	 * default desc
	 */
	function getDispatchCallUsers($data) {

		if (!isset($data['Lpu_id'])) {
			return false;
		}

		$query = '
			select
				PM.pmUser_id,
				PM.pmUser_Name
			from
				pmusercache PM with (nolock)
			where
				PM.pmUser_groups like \'%{"name":"smpcalldispath"}%\'
				and PM.Lpu_id = :Lpu_id
			';
		$result = $this->db->query($query,$data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	/**
	 * default desc
	 */
	function getAddressForNavitel($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}

		$query = "
			SELECT
				CCC.CmpCallCard_id
				,ISNULL(RGN.KLRgn_FullName,'') +
				ISNULL(', '+SRGN.KLSubRgn_FullName+' район', '') +
				ISNULL(' город '+City.KLCity_Name,'') +
				ISNULL(', '+Town.KLTown_FullName,'') +
				ISNULL(', улица '+Street.KLStreet_Name,'') +
				ISNULL(', дом '+CCC.CmpCallCard_Dom,'') as Address_Name
			FROM
				v_CmpCallCard CCC with (nolock)
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query($query,$data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * default desc
	 */
	function getAddressForOsmGeocode($data) {
		if (!isset($data['CmpCallCard_id'])) {
			return false;
		}

		$query = "
			SELECT
				CCC.CmpCallCard_id
				,ISNULL( RGN.KLRgn_FullName,'') as Rgn_Name
				,ISNULL( City.KLCity_Name,ISNULL( Town.KLTown_FullName,'')) as City_Name
				,ISNULL( Street.KLStreet_Name,'') as Street_Name
				,ISNULL( CCC.CmpCallCard_Dom,'') as House_Name
			FROM
				v_CmpCallCard CCC with (nolock)
				left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
				left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
				left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
				left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
				left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
			WHERE
				CCC.CmpCallCard_id = :CmpCallCard_id
		";

		$result = $this->db->query($query,$data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает адрес из талона вызова, в т.ч. неформализованные
	 *
	 * @param array $data Входящие параметры
	 *
	 * @return array
	 */
	public function getCmpCallCardAddress( $data ){
		if ( !isset( $data['CmpCallCard_id'] ) || !$data['CmpCallCard_id'] ) {
			return false;
		}
		$sql = "
			SELECT
				/* формализованные адрес */
				ISNULL( RGN.KLRgn_FullName, '' ) as Rgn_Name,
				ISNULL( City.KLCity_Name, ISNULL( Town.KLTown_FullName, '' ) ) as City_Name,
				ISNULL( Street.KLStreet_Name, '' ) as Street_Name,
				ISNULL( CCC.CmpCallCard_Dom, '' ) as House_Name,

				/* неформализованные адреса */
				UnformalizedAddressDirectory_Name as UName,
				UnformalizedAddressDirectory_lat as ULat,
				UnformalizedAddressDirectory_lng as ULng,
				ISNULL( URGN.KLRgn_FullName, '' ) as URgn_Name,
				ISNULL( UCITY.KLCity_Name, ISNULL( UTOWN.KLTown_FullName, '' ) ) as UCity_Name,
				ISNULL( USTREET.KLStreet_Name, '' ) as UStreet_Name,
				ISNULL( UAD.UnformalizedAddressDirectory_Dom, '' ) as UHouse_Name
			FROM
				v_CmpCallCard CCC with (nolock)
				LEFT JOIN v_KLRgn RGN (nolock) ON( RGN.KLRgn_id=CCC.KLRgn_id )
				LEFT JOIN v_KLSubRgn SRGN (nolock) ON( SRGN.KLSubRgn_id=CCC.KLSubRgn_id )
				LEFT JOIN v_KLCity CITY (nolock) ON( CITY.KLCity_id=CCC.KLCity_id )
				LEFT JOIN v_KLTown TOWN (nolock) ON( TOWN.KLTown_id=CCC.KLTown_id )
				LEFT JOIN v_KLStreet STREET (nolock) ON( STREET.KLStreet_id=CCC.KLStreet_id )

				LEFT JOIN v_UnformalizedAddressDirectory UAD (nolock) ON( UAD.UnformalizedAddressDirectory_id=CCC.UnformalizedAddressDirectory_id )
				LEFT JOIN v_KLRgn URGN (nolock) ON( URGN.KLRgn_id=UAD.KLRgn_id )
				LEFT JOIN v_KLSubRgn USRGN (nolock) ON( USRGN.KLSubRgn_id=UAD.KLSubRgn_id )
				LEFT JOIN v_KLCity UCITY (nolock) ON( UCITY.KLCity_id=UAD.KLCity_id )
				LEFT JOIN v_KLTown UTOWN (nolock) ON( UTOWN.KLTown_id=UAD.KLTown_id )
				LEFT JOIN v_KLStreet USTREET (nolock) ON( USTREET.KLStreet_id=UAD.KLStreet_id )
			WHERE
				CCC.CmpCallCard_id=:CmpCallCard_id
		";

		$query = $this->db->query($sql,array('CmpCallCard_id'=>$data['CmpCallCard_id']));
		if (is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}


	/**
	 *
	 * @param type $data
	 */
	public function getUnformalizedAddressStreetKladrParams($data) {
		if (
				!isset( $data['administrative_area_level_1'] ) || !$data['administrative_area_level_1'] ||
				!isset( $data['administrative_area_level_2'] ) || !$data['administrative_area_level_2'] ||
				!isset( $data['route'] ) || !$data['route'] ||
				!isset( $data['street_number'] ) || !$data['street_number']
			) {
			return false;
		}

		$addressComponents = array(
			'administrative_area_level_1'=>  explode(' ', $data['administrative_area_level_1']),
			'administrative_area_level_2'=>explode(' ',$data['administrative_area_level_2']),
			'route'=>explode(' ',$data['route']),
			'street_number'=>explode(' ',$data['street_number'])
		);


		$addresses = array(
			'administrative_area_level_1'=>  $data['administrative_area_level_1'],
			'administrative_area_level_2'=>$data['administrative_area_level_2'],
			'route'=>$data['route'],
			'street_number'=>$data['street_number']
		);

		//Первый вариант
		$querySocr = "
			SELECT
				KLS.KLSocr_Name
			FROM
				v_KLSocr KLS with (nolock)
			";

		$result = $this->db->query($querySocr);
		if ( !is_object($result) ) {
			return false;
		}
		$resultSocr = $result->result('array');
		$resultSocr=toUTFR($resultSocr);
		$addresses = toUTFR($addresses);

		foreach ($resultSocr as $socr) {
			foreach ($addresses as $type => $comp) {
				$addresses["$type"] = mb_strtoupper(preg_replace('#\s?'.$socr['KLSocr_Name'].'\s?#ui', '', $comp));
			}
		}
		$addresses = toAnsiR($addresses);

		//Получаем код территории
		$queryTerritoryParams = array(
			'KLArea_Name'=>$addresses['administrative_area_level_1']
		);
		$queryTerritory = "
			SELECT
				KLA.KLArea_id
			FROM
				v_KLArea KLA with(nolock)
			WHERE
				KLA.KLArea_Name like :KLArea_Name AND KLAreaLevel_id = 1
			";
		$result = $this->db->query($queryTerritory,$queryTerritoryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$resultTerritory = $result->result('array');
		if (sizeof($resultTerritory)!= 1) {
			return false;
		}

		//Получаем код города
		$querySubTerritoryParams = array(
			'KLArea_Name'=>$addresses['administrative_area_level_2'],
			'KLArea_pid'=>$resultTerritory[0]['KLArea_id']
		);
		$querySubTerrytory = "
			SELECT
				KLA.KLArea_id
			FROM
				v_KLArea KLA with(nolock)
			WHERE
				KLA.KLArea_Name like :KLArea_Name AND
				KLA.KLArea_pid = :KLArea_pid AND
				KLA.KLAreaLevel_id = 3
			";
		$result = $this->db->query($querySubTerrytory,$querySubTerritoryParams);
		if ( !is_object($result) ) {
			return false;
		}
		$resultSubTerritory = $result->result('array');
		if (sizeof($resultSubTerritory)!= 1) {
			return false;
		}
		//Получаем код улицы
		$queryStreetParams = array(
			'KLStreet_Name'=>$addresses['route'],
			'KLArea_pid'=>$resultSubTerritory[0]['KLArea_id']
		);
		$queryStreet = "
			SELECT
				KLS.KLStreet_id
			FROM
				v_KLStreet KLS with(nolock)
			WHERE
				KLS.KLStreet_Name like :KLStreet_Name AND KLS.KLArea_id = :KLArea_pid
			";
		$result = $this->db->query($queryStreet,$queryStreetParams);
		if ( !is_object($result) ) {
			return false;
		}
		$resultStreet = $result->result('array');
		if (sizeof($resultStreet)!= 1) {
			return false;
		}

		return array(array(
			'success'=>true,
			'KL'=>$resultTerritory[0]['KLArea_id'] ,
			'KLCity_id'=>$resultSubTerritory[0]['KLArea_id'],
			'KLStreet_id'=>$resultStreet[0]['KLStreet_id']
		));

	}

	/**
	 * Возвращает ранжированные причины вызова для текущей лпу
	 *
	 * @param array $data Входящие параметры
	 *
	 * @return array
	 */
	 /*
	public function getCmpRangeReasonList( $data ){
		if (!isset($data['Lpu_id'])) {
			return false;
		}

		$sql = 'select
			reason.CmpReason_id,
			reason.CmpReason_Code,
			reason.CmpReason_Name,
			rangeReason.CmpReasonRange_Value,
			rangeReason.CmpReasonRange_id
			from v_CmpReason as reason with(nolock)
			left join v_CmpReasonRange rangeReason on rangeReason.CmpReason_id = reason.CmpReason_id and rangeReason.Lpu_id = :Lpu_id
			';

		$result = $this->db->query($sql, array(
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	*/

	/**
	 * Сохраняет ранжированные причины вызова для текущей лпу
	 *
	 * @param array $data Входящие параметры
	 *
	 * @return array
	 */
	 /*
	public function saveCmpRangeReasonList( $data ){
		if (!isset($data['Lpu_id'])) {
			return false;
		}

		$procedure = null;

		if ( array_key_exists( 'SmpCallRange', $data ) && !empty( $data['SmpCallRange'] ) && $data['SmpCallRange'] != '[]' ) {
			$cmpRangeReasonList = json_decode( toUTF( $data['SmpCallRange'] ), true );
			foreach( $cmpRangeReasonList as $k => $rangeReason ) {

				if( ( $rangeReason['CmpReasonRange_id'] != '' ) && ($rangeReason['CmpReasonRange_id'] != 0) && ($rangeReason['CmpReasonRange_id'] != null)){
					$procedure = 'p_CmpReasonRange_upd';
				}
				else{
					$procedure = 'p_CmpReasonRange_ins';
				}

				$sqlArr = array(
					'CmpReason_id'			=> $rangeReason['CmpReason_id'],
					'CmpReasonRange_id'		=> $rangeReason['CmpReasonRange_id'],
					'CmpReasonRange_Value'	=> $rangeReason['CmpReasonRange_Value'],
					'pmUser_id'				=> $data['pmUser_id'],
					'Lpu_id'				=> $data['Lpu_id']
				);

				$query = "
					DECLARE
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)

					SET @Res = :CmpReasonRange_id;

					EXEC ".$procedure."
						@CmpReasonRange_id = @Res output,
						@CmpReason_id = :CmpReason_id,
						@CmpReasonRange_Value = :CmpReasonRange_Value,
						@Lpu_id = :Lpu_id,

						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					SELECT @Res as CmpReasonRange_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";

				$result = $this->db->query($query, $sqlArr);

				if ( !is_object($result) ) {
					$this->db->trans_rollback();
					return $result->result('array');
				}
			}
		}
		return array( array( 'Err_Msg' => NULL, 'Error_Code' => NULL, 'success' => true) );

	}
	*/


	/**
	 * Сохранение дерева решений
	 * @param type $data
	 * @return boolean
	 */
	public function saveDecigionTree($data) {
		if (!isset($data['data']) || !isset($data['Lpu_id'])) {
			return false;
		}

		set_time_limit(3000);

		$decigionTreeData = json_decode($data['data'], true);
		if ($decigionTreeData == null || !is_array($decigionTreeData) || sizeof($decigionTreeData)==0) {
			return array(array('Error_Msg'=>'Неверный формат данных дерева'));
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_AmbulanceDecigionTree_delAllByLpuId
				@Lpu_id = :Lpu_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query,array(
			'Lpu_id'=>$data['Lpu_id']
		));

		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		//Удаляем элементы предыдущего дерева решений, если оно существовало

		foreach ($decigionTreeData as $treeItem) {
			//Сохраняем элементы нового дерева решений
			$saveItemQuery = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :AmbulanceDecigionTree_id;
				exec p_AmbulanceDecigionTree_ins
					@AmbulanceDecigionTree_id = @Res,
					@AmbulanceDecigionTree_nodeid = :AmbulanceDecigionTree_nodeid,
					@AmbulanceDecigionTree_nodepid = :AmbulanceDecigionTree_nodepid,
					@AmbulanceDecigionTree_Type = :AmbulanceDecigionTree_Type,
					@AmbulanceDecigionTree_Text = :AmbulanceDecigionTree_Text,
					@CmpReason_id = :CmpReason_id,
					@Lpu_id = :Lpu_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as CmpCallCardLockList_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$saveItemResult = $this->db->query($saveItemQuery, array(
				'AmbulanceDecigionTree_id'=>null,
				'AmbulanceDecigionTree_nodeid' => $treeItem['AmbulanceDecigionTree_nodeid'],
				'AmbulanceDecigionTree_nodepid' => $treeItem['AmbulanceDecigionTree_nodepid'],
				'AmbulanceDecigionTree_Type' => $treeItem['AmbulanceDecigionTree_Type'],
				'AmbulanceDecigionTree_Text' => toAnsi($treeItem['AmbulanceDecigionTree_Text']),
				'CmpReason_id'=> ($treeItem['CmpReason_id']>1)?$treeItem['CmpReason_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if ( !is_object($saveItemResult) ) {
				return false;
			} else {
				$saveItemResult = $saveItemResult->result('array');
				if (is_array($saveItemResult)&&isset($saveItemResult[0])&&isset($saveItemResult[0]['Error_Msg'])&&$saveItemResult[0]['Error_Msg']!='') {
					return $saveItemResult;
				}
			}
		}

		return array(array('success'=>true,'Error_Msg'=>''));

	}
	/**
	 *
	 * @param type $data
	 */
	public function getDecigionTree($data) {

		$CurArmType = (!empty($data['session']['CurArmType']) ? $data['session']['CurArmType'] : '');
		if(!in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp'))){
			$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
			$OperDepartamentOptions = $this->CmpCallCard_model4E-> getOperDepartamentOptions($data);

			if($OperDepartamentOptions && $OperDepartamentOptions["LpuBuildingType_id"] == 28){
				$data['Lpu_id'] = $OperDepartamentOptions["Lpu_id"];
			};
		}


		if (!isset($data['Lpu_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор ЛПУ'));
		}

		if ( $this->db->dbdriver == 'postgre' ) {
			$query = "
				SELECT
					ADT.\"AmbulanceDecigionTree_id\",
					ADT.\"AmbulanceDecigionTree_nodeid\",
					ADT.\"AmbulanceDecigionTree_nodepid\",
					ADT.\"AmbulanceDecigionTree_Type\",
					ADT.\"AmbulanceDecigionTree_Text\",
					ADT.\"CmpReason_id\"
				FROM
					dbo.\"v_AmbulanceDecigionTree\" ADT with(nolock)
				WHERE
					COALESCE(ADT.\"Lpu_id\",0) = :Lpu_id
				ORDER BY
					ADT.\"AmbulanceDecigionTree_nodeid\"
			";
		} else {
			$query = "
				SELECT
					ADT.AmbulanceDecigionTree_id,
					ADT.AmbulanceDecigionTree_nodeid,
					ADT.AmbulanceDecigionTree_nodepid,
					ADT.AmbulanceDecigionTree_Type,
					ADT.AmbulanceDecigionTree_Text,
					ADT.CmpReason_id
				FROM
					v_AmbulanceDecigionTree ADT with(nolock)
				WHERE
					ISNULL(ADT.Lpu_id,0) = :Lpu_id
				ORDER BY
					ADT.AmbulanceDecigionTree_nodeid
			";
		}

		if (!in_array($CurArmType, array('dispcallnmp', 'dispdirnmp', 'dispnmp'))) {
			$params = array(
				'Lpu_id'=>$data['Lpu_id']
			);
		} else {
			if (empty($data['session']['CurARM']['MedService_id'])) {
				$params = array(
					'Lpu_id'=>$data['Lpu_id']
				);
			} else {
				$params = array(
					'Lpu_id' => $this->getNMPLpu($data)
				);
			}
		}

		$result_count = $this->db->query(getCountSQL($query), $data);

		$count = 0;
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			if (is_array($cnt_arr)&&isset($cnt_arr[0])&&isset($cnt_arr[0]['cnt'])) {
				$count = $cnt_arr[0]['cnt'];
			}
			unset($cnt_arr);
		}
		$params['Lpu_id'] = ($count==0)?0:$params['Lpu_id'];

		$result = $this->db->query($query,$params);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}

	}

	/**
	 * Полчение дерева решений приналижащего определнное структуре
	 */
	public function getConcreteDecigionTree($data){

		$query = "
			SELECT
				ADT.AmbulanceDecigionTree_id,
				ADT.AmbulanceDecigionTree_nodeid,
				ADT.AmbulanceDecigionTree_nodepid,
				ADT.AmbulanceDecigionTree_Type,
				ADT.AmbulanceDecigionTree_Text,
				ADT.CmpReason_id,
			    ADTR.AmbulanceDecigionTreeRoot_id
			FROM
				v_AmbulanceDecigionTree ADT
			left join v_AmbulanceDecigionTreeRoot ADTR on ADT.AmbulanceDecigionTreeRoot_id = ADTR.AmbulanceDecigionTreeRoot_id
			WHERE
				ADTR.AmbulanceDecigionTreeRoot_id = :AmbulanceDecigionTreeRoot_id
			";

		$result = $this->db->query($query, $data);

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Создание структуры дерева
	 */
	public function createDecigionTree($data){

		$queryTreeRoot = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :AmbulanceDecigionTreeRoot_id;
			exec  p_AmbulanceDecigionTreeRoot_ins
				@AmbulanceDecigionTreeRoot_id = @Res output,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id;   
			select @Res as AmbulanceDecigionTreeRoot_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg; 
            ";

		$resultTreeRootQuery = $this->db->query($queryTreeRoot, array(
			'AmbulanceDecigionTreeRoot_id' => null,
			'Lpu_id' =>  !empty($data['Lpu_id']) ? $data['Lpu_id']: null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'Region_id' => $this->getRegionNumber(),
			'pmUser_id'=> $data['pmUser_id']
		));

		$resultTreeRoot = $resultTreeRootQuery ->result( 'array' )[0];

		$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :AmbulanceDecigionTree_id;
				exec p_AmbulanceDecigionTree_ins
					@AmbulanceDecigionTree_id = @Res output,
					@AmbulanceDecigionTree_nodeid = :AmbulanceDecigionTree_nodeid,
					@AmbulanceDecigionTree_nodepid = :AmbulanceDecigionTree_nodepid,
					@AmbulanceDecigionTree_Type = :AmbulanceDecigionTree_Type,
					@AmbulanceDecigionTree_Text = :AmbulanceDecigionTree_Text,
					@Lpu_id = :Lpu_id,
					@AmbulanceDecigionTreeRoot_id = :AmbulanceDecigionTreeRoot_id,
					@LpuBuilding_id = :LpuBuilding_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as AmbulanceDecigionTree_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		$result = $this->db->query($query, array(
			'AmbulanceDecigionTree_id' => null,
			'AmbulanceDecigionTree_nodeid' => 1,
			'AmbulanceDecigionTree_nodepid' => 1,
			'AmbulanceDecigionTree_Type' => '1',
			'AmbulanceDecigionTree_Text' => 'ЧТО СЛУЧИЛОСЬ? БОЛЬНОЙ В СОЗНАНИИ?',
			'AmbulanceDecigionTreeRoot_id' => $resultTreeRoot['AmbulanceDecigionTreeRoot_id'],
			'pmUser_id' => $data['pmUser_id'],
			//Для поддержки старого фукнционала, после полного перехода - выпилить
			'Lpu_id' =>  isset($data['Lpu_id']) ? $data['Lpu_id']: null,
			'LpuBuilding_id' => isset($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'Region_id' => $this->getRegionNumber()
		));

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}

	}

	/**
	 * Копирование структуры дерева
	 */
	public function copyDecigionTree($data){
		$tree = $this->getConcreteDecigionTree($data);

		$queryTreeRoot = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :AmbulanceDecigionTreeRoot_id;
			exec  p_AmbulanceDecigionTreeRoot_ins
				@AmbulanceDecigionTreeRoot_id = @Res output,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id;   
			select @Res as AmbulanceDecigionTreeRoot_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg; 
            ";

		$resultTreeRootQuery = $this->db->query($queryTreeRoot, array(
			'AmbulanceDecigionTreeRoot_id' => null,
			'Lpu_id' =>  !empty($data['Lpu_id']) ? $data['Lpu_id']: null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
			'Region_id' => $this->getRegionNumber(),
			'pmUser_id'=> $data['pmUser_id']
		));

		$resultTreeRoot = $resultTreeRootQuery ->result( 'array' )[0];

		foreach($tree as $value){
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :AmbulanceDecigionTree_id;
				exec p_AmbulanceDecigionTree_ins
					@AmbulanceDecigionTree_id = @Res output,
					@AmbulanceDecigionTree_nodeid = :AmbulanceDecigionTree_nodeid,
					@AmbulanceDecigionTree_nodepid = :AmbulanceDecigionTree_nodepid,
					@AmbulanceDecigionTree_Type = :AmbulanceDecigionTree_Type,
					@AmbulanceDecigionTree_Text = :AmbulanceDecigionTree_Text,
					@CmpReason_id = :CmpReason_id,
					@Lpu_id = :Lpu_id,
					@AmbulanceDecigionTreeRoot_id = :AmbulanceDecigionTreeRoot_id,
					@LpuBuilding_id = :LpuBuilding_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as AmbulanceDecigionTree_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$result = $this->db->query($query, array(
				'AmbulanceDecigionTree_id' => null,
				'AmbulanceDecigionTree_nodeid' => $value['AmbulanceDecigionTree_nodeid'],
				'AmbulanceDecigionTree_nodepid' => $value['AmbulanceDecigionTree_nodepid'],
				'AmbulanceDecigionTree_Type' => $value['AmbulanceDecigionTree_Type'],
				'AmbulanceDecigionTree_Text' => $value['AmbulanceDecigionTree_Text'],
				'CmpReason_id'=> !empty($value['CmpReason_id']) ? $value['CmpReason_id']:null,
				'AmbulanceDecigionTreeRoot_id' => $resultTreeRoot['AmbulanceDecigionTreeRoot_id'],
				'pmUser_id' => $data['pmUser_id'],
				//Для поддержки старого фукнционала, после полного перехода - выпилить
				'Lpu_id' =>  isset($data['Lpu_id']) ? $data['Lpu_id']: null,
				'LpuBuilding_id' => isset($data['LpuBuilding_id'])?$data['LpuBuilding_id']: null,
				'Region_id' => $this->getRegionNumber()
			));
		}


		return $result;
	}

	/**
	 * Получение стркутуры деревьев МО
	 */
	public function getDecigionTreeLpu($data){

		$filter = '';
		if($data['adminRegion'] == 'false'){
			$this->load->model('CmpCallCard_model4E');
			$OperDepartament = $this->CmpCallCard_model4E->getOperDepartament($data);
			$filter = " and LB.LpuBuilding_id = {$OperDepartament['LpuBuilding_pid']}";
		}

		$sql = "
			SELECT DISTINCT
					lpu.Lpu_id,
					lpu.Lpu_Name,
			        lpu.Lpu_Name as text,
					lpu.Lpu_Nick, 
			        ADTR.AmbulanceDecigionTreeRoot_id,
			        case when ADTR.AmbulanceDecigionTreeRoot_id is not null then 'true' else 'false' end as issetTree
				FROM
					v_SmpUnitParam sup 
					LEFT JOIN v_SmpUnitType sut  ON sup.SmpUnitType_id = sut.SmpUnitType_id
					INNER JOIN v_LpuBuilding lb ON(lb.LpuBuilding_id=sup.LpuBuilding_id)
					LEFT JOIN v_lpu lpu on lpu.Lpu_id = lb.Lpu_id
					OUTER APPLY(
						SELECT TOP 1
							AmbulanceDecigionTreeRoot_id
						FROM v_AmbulanceDecigionTreeRoot 
						WHERE Lpu_id = lpu.Lpu_id and LpuBuilding_id is null
					) as ADTR 
				WHERE
					COALESCE(sup.LpuBuilding_pid, 1) = 1
					AND lpu.Lpu_id is not null 
					AND sut.SmpUnitType_Code = 4
					AND LB.LpuBuildingType_id in (27, 28)
					AND lb.LpuBuilding_begDate <= dbo.tzGetDate()
					AND (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate()) {$filter}
		";


		$result = $this->db->query($sql);

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}
	}
	/**
	 * Получение стркутуры деревьев подстанции
	 */
	public function getDecigionTreeRegion($data){
		$sql = "
			SELECT top 1
				 KLA.KLArea_FullName  as text,
			     ADTR.AmbulanceDecigionTreeRoot_id,
		    	 case when ADTR.AmbulanceDecigionTreeRoot_id is not null then 'true' else 'false' end as issetTree
			FROM v_KLArea KLA with (nolock)
			OUTER APPLY(
				SELECT TOP 1 
					AmbulanceDecigionTreeRoot_id
				FROM AmbulanceDecigionTreeRoot 
				WHERE Region_id = KLA.KLArea_id AND LpuBuilding_id IS NULL AND Lpu_id IS NULL
			) as ADTR
			WHERE KLA.KLArea_id = :Region_id
		";

		$result = $this->db->query($sql,array('Region_id' => $data['session']['region']['number']));

		if (is_object($result)) {
			return $result->result_array()[0];
		} else {
			return false;
		}
	}

	/**
	 * Получение стркутуры деревьев подстанции
	 */
	public function getDecigionTreeLpuBuilding($data){

		$filter = '';
		if($data['adminRegion'] == 'false'){
			$this->load->model('CmpCallCard_model4E');
			$OperDepartament = $this->CmpCallCard_model4E->getOperDepartament($data);
			$filter = " and LB.LpuBuilding_id = {$OperDepartament['LpuBuilding_pid']}";
		}

		$sql = "
		SELECT DISTINCT 
		     LB.LpuBuilding_id,
		     LB.LpuBuilding_Name as text,
		     LB.Lpu_id,
		     ADTR.AmbulanceDecigionTreeRoot_id,
		     case when ADTR.AmbulanceDecigionTreeRoot_id is not null then 'true' else 'false' end as issetTree
		from v_LpuBuilding LB 
			LEFT JOIN v_SmpUnitParam sup ON sup.LpuBuilding_id = LB.LpuBuilding_id
			LEFT JOIN v_SmpUnitType sut ON sup.SmpUnitType_id = sut.SmpUnitType_id
			OUTER APPLY(
				SELECT TOP 1 
					AmbulanceDecigionTreeRoot_id
				FROM AmbulanceDecigionTreeRoot 
				WHERE LpuBuilding_id = LB.LpuBuilding_id
			) as ADTR
		where sut.SmpUnitType_Code = 4 				 
		and lb.LpuBuilding_begDate <= dbo.tzGetDate()
		and (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate()) {$filter}";

		$result = $this->db->query($sql);

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}

	}

	/**
	 * Получение структуры для которых существует дерево решений
	 */
	public function getStructuresIssetTree($data){



		switch ($data['level']){
			case 'LpuBuilding':
				$filter[] ='ADTR.LpuBuilding_id is not null';
				$filter[] ='ADTR.Region_id = dbo.GetRegion()';
				break;
			case 'Lpu':
				$filter[] ='ADTR.Lpu_id is not null';
				$filter[] ='ADTR.LpuBuilding_id IS NULL';
				$filter[] ='ADTR.Region_id = dbo.GetRegion()';

				break;
			case 'Region':
				$filter[] ='ADTR.Lpu_id IS NULL';
				$filter[] ='ADTR.LpuBuilding_id IS NULL';
				$filter[] ='ADTR.Region_id = dbo.GetRegion()';
				break;
			default:
				$filter[] ='ADTR.Lpu_id IS NULL';
				$filter[] ='ADTR.LpuBuilding_id IS NULL';
				$filter[] ='ADTR.Region_id IS NULL';
				break;
		}

		$sql = "
			SELECT 
				ADTR.Lpu_id,
				ADTR.LpuBuilding_id,
				ADTR.Region_id,
			    ADTR.AmbulanceDecigionTreeRoot_id,
				coalesce(LB.lpubuilding_name, Lpu.Lpu_name, KLA.KLArea_FullName, 'Базовое дерево') as text
			from AmbulanceDecigionTreeRoot ADTR
			left join v_Lpu Lpu on Lpu.Lpu_id = ADTR.Lpu_id  
			left join v_LpuBuilding LB on LB.LpuBuilding_id = ADTR.LpuBuilding_id  
			left join v_KLArea KLA on  ADTR.Region_id = KLA.KLArea_id
			WHERE
			".implode(' and ', $filter);

		$result = $this->db->query($sql);

		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}
	}

	/**
	 * Сохранение ноды дерева решений
	 * @param type $data
	 * @return boolean
	 */
	public function saveDecigionTreeNode($data) {

		$output = '';
		if(empty($data['AmbulanceDecigionTree_id'])){
			//add
			$output = ' output';
			$procedure = "p_AmbulanceDecigionTree_ins";
		}else{
			//edit
			$procedure = "p_AmbulanceDecigionTree_upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :AmbulanceDecigionTree_id;
			exec {$procedure}
				@AmbulanceDecigionTree_id = @Res {$output},
				@AmbulanceDecigionTree_nodeid = :AmbulanceDecigionTree_nodeid,
				@AmbulanceDecigionTree_nodepid = :AmbulanceDecigionTree_nodepid,
				@AmbulanceDecigionTree_Type = :AmbulanceDecigionTree_Type,
				@AmbulanceDecigionTree_Text = :AmbulanceDecigionTree_Text,
				@AmbulanceDecigionTreeRoot_id = :AmbulanceDecigionTreeRoot_id,
				@CmpReason_id = :CmpReason_id,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@Region_id = :Region_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as AmbulanceDecigionTree_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'AmbulanceDecigionTree_id'=> !empty($data['AmbulanceDecigionTree_id']) ? $data['AmbulanceDecigionTree_id']:null,
			'AmbulanceDecigionTree_nodeid' => $data['AmbulanceDecigionTree_nodeid'],
			'AmbulanceDecigionTree_nodepid' => $data['AmbulanceDecigionTree_nodepid'],
			'AmbulanceDecigionTree_Type' => $data['AmbulanceDecigionTree_Type'],
			'AmbulanceDecigionTree_Text' => $data['AmbulanceDecigionTree_Text'],
			'AmbulanceDecigionTreeRoot_id' => $data['AmbulanceDecigionTreeRoot_id'],
			'CmpReason_id'=> !empty($data['CmpReason_id']) ? $data['CmpReason_id']:null,
			'Lpu_id' => !empty($data['Lpu_id']) ? $data['Lpu_id']:null,
			'LpuBuilding_id' => !empty($data['LpuBuilding_id']) ? $data['LpuBuilding_id']:null,
			'Region_id' => $data['session']['region']['number'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			return false;
		}
		return $result->result('array');
	}


	/**
	 * Удаление ноды дерева решений
	 * @param type $data
	 * @return boolean
	 */
	public function deleteDecigionTreeNode($data) {
		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');

		$params = array(
			'AmbulanceDecigionTree_id'=> !empty($data['AmbulanceDecigionTree_id']) ? $data['AmbulanceDecigionTree_id']:null
		);

		$prequery = "
			select ac.AmbulanceDecigionTree_id from
				v_AmbulanceDecigionTree (nolock) ap
				left join v_AmbulanceDecigionTree ac on ac.AmbulanceDecigionTree_nodepid = ap.AmbulanceDecigionTree_nodeid and ac.AmbulanceDecigionTreeRoot_id = ap.AmbulanceDecigionTreeRoot_id 
				where ap.AmbulanceDecigionTree_id = :AmbulanceDecigionTree_id
		";

		$preresult = $this->db->query($prequery, $params);
		//echo(getDebugSql($prequery, $params)); exit;

		if ( is_object($preresult) ) {
			$preresult = $preresult->result('array');
			if(count($preresult) && $preresult[0]['AmbulanceDecigionTree_id']){
				return array(array('success'=>false,'Error_Msg'=>'Элемент содержит дочерние значения. Удаление невозможно.'));
			}
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :AmbulanceDecigionTree_id;
			exec p_AmbulanceDecigionTree_del
				@AmbulanceDecigionTree_id = @Res,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as AmbulanceDecigionTree_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";



		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return false;
		}
		return $result->result('array');
	}

	/**
	* Возвращает массив ID МО выбранных в АРМ
	*/
	public function getSelectedLpuId() {
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = unserialize($user->settings);

		if ( isset($settings['lpuWorkAccess']) && is_array($settings['lpuWorkAccess']) && $settings['lpuWorkAccess'][0] != "") {
			return $settings['lpuWorkAccess'];
		}else{
			return false;
		}
	}

	/**
	 * Получение списка подстанций СМП
	 * @return boolean
	 */
	public function loadSmpUnits($data) {
		if (!isset($data['Lpu_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор ЛПУ'));
		}

		$filter = array(
			'LB.LpuBuildingType_id = 27'
		);

		if(!empty($data['form']) && $data['form'] == 'cmk') {
			$Lpu_ids = $this->getSelectedLpuId();
			if ( $Lpu_ids ) {
				$filter[] = "LB.Lpu_id in (".implode(',',$Lpu_ids).')';
			} else {
				return false;
			}
		} else {
			$filter[] = 'LB.Lpu_id ='.$data['Lpu_id'];
		}

		if (!empty($data['SmpUnitType_Code'])) {
			$filter[] = 'sut.SmpUnitType_Code = '.$data['SmpUnitType_Code'];
		}

		if (isset($data['showOperDpt']) && $data['showOperDpt'] == 1) {
			$filter[] = 'sut.SmpUnitType_Code != 4';
		};

		$query = "
			SELECT DISTINCT
				LB.LpuBuilding_id,
				LB.LpuBuilding_Name,
				LB.LpuBuilding_Nick,
				L.Lpu_id,
				L.Lpu_Nick
			FROM
				v_LpuBuilding LB with(nolock)
				inner join v_SmpUnitParam sup with (nolock) ON LB.LpuBuilding_id = sup.LpuBuilding_id
				left join v_SmpUnitType sut with (nolock) on sut.SmpUnitType_id = sup.SmpUnitType_id
				left join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
			WHERE
				".implode(' and ', $filter)."
			";

		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Получение списка отделений
	 * @return boolean
	 */
	public function loadLpuCmpUnits($data) {
		$query = "
			SELECT
				LS.LpuSection_id,
				LS.LpuSection_Name,
				LS.LpuSection_Code
			FROM
				v_LpuSection LS with(nolock)
			JOIN LpuUnit LU with(nolock) on (LU.LpuUnitType_id = 13 and LU.LpuUnit_id = LS.LpuUnit_id)
			JOIN LpuBuilding LB with(nolock) on (LB.LpuBuilding_id = LU.LpuBuilding_id and LB.Lpu_id = :Lpu_id)
		";

		$result = $this->db->query($query, $data);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}


	/**
	 * Получение списка подстанций СМП
	 * @return boolean
	 */
	public function getCmpCallPlaces($data) {

		$query = "
			SELECT
				CCPT.CmpCallPlaceType_id,
				CCPT.CmpCallPlaceType_Name,
				CCPT.CmpCallPlaceType_Code,
				case when (ISNULL(CmpUrgencyAndProfileStandartRefPlace_id,0) =0) then 'false' else 'true' end as is_checked
			FROM
				v_CmpCallPlaceType CCPT with(nolock)
				left join v_CmpUrgencyAndProfileStandartRefPlace CUPSRF with (nolock) on
					CUPSRF.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id
					AND CUPSRF.CmpCallPlaceType_id = CCPT.CmpCallPlaceType_id
			-- Такая вот странная сортировка, т.к. нужно сортировать по коду, а тип поля Код - строковый
			order by
				case when ISNUMERIC(CCPT.CmpCallPlaceType_Code + 'e0') = 1 then right('000' + CCPT.CmpCallPlaceType_Code, 3) else CCPT.CmpCallPlaceType_Code end
			";

		$result = $this->db->query($query,array(
			'CmpUrgencyAndProfileStandart_id'=>(empty($data['CmpUrgencyAndProfileStandart_id']))?0:$data['CmpUrgencyAndProfileStandart_id']
		));
		if (!is_object($result)) {
			return false;
		} else {
			$result = $result->result('array');
			return array(
				'data'=>$result,
				'totalCount'=>  sizeof($result)
			);
		}
	}


	/**
	 * Получение справочника нормативов назначения профилей бригад и срочности вызова
	 */
	function getCmpUrgencyAndProfileStandart($data) {
		$accFilter = '(1=1)';

		if (!empty($data['Lpu_id'])) {
			$accFilter .= ' and CUPS.Lpu_id = :Lpu_id';
		}

		if (!empty($data['CmpCallCardAcceptor_id'])) {
			$accFilter .= ' and CCCA.CmpCallCardAcceptor_id in (:CmpCallCardAcceptor_id)';
		}

		$query = "
			-- addit with
			WITH CUPS_A AS (
				SELECT
					CUPS.Lpu_id,
					CUPS.CmpUrgencyAndProfileStandart_id,
					CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,
					CUPS.CmpUrgencyAndProfileStandart_Urgency,
					CUPS.CmpReason_id,
					CR.CmpReason_Code,
					CCCA.CmpCallCardAcceptor_id,
					CCCA.CmpCallCardAcceptor_Code,
					CCCA.CmpCallCardAcceptor_Name,
					CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv,
					CASE WHEN COALESCE(CUPS.CmpUrgencyAndProfileStandart_HeadDoctorObserv,1)=1 THEN 'Нет' ELSE 'Да' END CmpUrgencyAndProfileStandart_HeadDoctorObserv_YesNo,
					CUPS.CmpUrgencyAndProfileStandart_MultiVictims,
					CASE WHEN COALESCE(CUPS.CmpUrgencyAndProfileStandart_MultiVictims,1)=1 THEN 'Нет' ELSE 'Да' END CmpUrgencyAndProfileStandart_MultiVictims_YesNo
				FROM
					v_CmpUrgencyAndProfileStandart CUPS with(nolock)
					left join v_CmpReason CR with(nolock) on CUPS.CmpReason_id = CR.CmpReason_id
					LEFT JOIN v_CmpCallCardAcceptor CCCA  with(nolock)ON(CCCA.CmpCallCardAcceptor_id=CUPS.CmpCallCardAcceptor_id)
				WHERE
					{$accFilter}
			), CCP AS (
				SELECT
					CUPS_A.CmpUrgencyAndProfileStandart_id,
					STUFF(
						(SELECT
							' '+CCPT.CmpCallPlaceType_Code
						FROM
							v_CmpUrgencyAndProfileStandartRefPlace CUPSRP WITH (nolock)
							left join v_CmpCallPlaceType CCPT with (nolock) on CCPT.CmpCallPlaceType_id = CUPSRP.CmpCallPlaceType_id
						WHERE
							CUPSRP.CmpUrgencyAndProfileStandart_id = CUPS_A.CmpUrgencyAndProfileStandart_id
						FOR XML PATH ('')
						), 1, 1, ''
					) as CmpUrgencyAndProfileStandart_PlaceSequence
				FROM
					CUPS_A with(nolock)
			), CUPSRSP_A AS (
				SELECT
					CUPS_A.CmpUrgencyAndProfileStandart_id,
					STUFF(
						(SELECT
							CASE WHEN
								ISNULL(PREV.ProfilePriority,0) = ISNULL(CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,0)
							THEN
								CASE WHEN
									ISNULL(PREV.ProfilePriority,0) = 0
								THEN
									' '+ETS.EmergencyTeamSpec_Code
								ELSE
									' + '+ETS.EmergencyTeamSpec_Code
								END
							ELSE
								CASE WHEN
									ISNULL(CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,0) =0
								THEN
									' - '+ETS.EmergencyTeamSpec_Code
								ELSE
									' '+ ETS.EmergencyTeamSpec_Code
								END
							END
						FROM
							v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP WITH (nolock)
							left join v_EmergencyTeamSpec ETS with (nolock) on CUPSRSP.EmergencyTeamSpec_id = ETS.EmergencyTeamSpec_id
							outer apply (
								select top 1
									CUPSRSP_PREV.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as ProfilePriority
								from
									v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP_PREV with (nolock)
								where
									CUPSRSP_PREV.CmpUrgencyAndProfileStandartRefSpecPriority_id = CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_id-1
							) as PREV
						WHERE
							CUPSRSP.CmpUrgencyAndProfileStandart_id = CUPS_A.CmpUrgencyAndProfileStandart_id
						ORDER BY
							ISNULL(CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,999) ASC
						FOR XML PATH ('')
					) , 1, 1, '') as CmpUrgencyAndProfileStandart_ProfileSequence
				FROM
					CUPS_A with(nolock)
			)
			-- end addit with

			SELECT
			--select
				CUPS_A.Lpu_id,
				CUPS_A.CmpUrgencyAndProfileStandart_id,
				CUPS_A.CmpUrgencyAndProfileStandart_UntilAgeOf,
				CUPS_A.CmpUrgencyAndProfileStandart_Urgency,
				CUPS_A.CmpCallCardAcceptor_id,
				CUPS_A.CmpCallCardAcceptor_Code,
				CUPS_A.CmpCallCardAcceptor_Name,
				CUPS_A.CmpUrgencyAndProfileStandart_HeadDoctorObserv,
				CUPS_A.CmpUrgencyAndProfileStandart_HeadDoctorObserv_YesNo,
				CUPS_A.CmpUrgencyAndProfileStandart_MultiVictims,
				CUPS_A.CmpUrgencyAndProfileStandart_MultiVictims_YesNo,
				CUPS_A.CmpReason_id,
				CUPS_A.CmpReason_Code,
				CCP.CmpUrgencyAndProfileStandart_PlaceSequence,
				CUPSRSP_A.CmpUrgencyAndProfileStandart_ProfileSequence
			--end select
			FROM
			-- from
				CUPS_A with(nolock)
				left join CUPSRSP_A with(nolock) on CUPS_A.CmpUrgencyAndProfileStandart_id = CUPSRSP_A.CmpUrgencyAndProfileStandart_id
				left join CCP with(nolock) on CUPS_A.CmpUrgencyAndProfileStandart_id = CCP.CmpUrgencyAndProfileStandart_id
			--end from
			ORDER BY
			-- order by
				CUPS_A.CmpReason_id ASC
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
			return false;
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
	 * Инициализация дефолтной логиги предложения бригад на вызов и назначения срочности вызова
	 */
	public function initiateProposalLogicForLpu($data) {
		if (empty($data['Lpu_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор ЛПУ'));
		}

		set_time_limit(3000);

		//Выбираем дефолтную логику
		$rulesQuery = '
			SELECT
				CUPS.CmpUrgencyAndProfileStandart_id,
				CUPS.CmpReason_id,
				CUPS.CmpUrgencyAndProfileStandart_Urgency,
				CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf
			FROM
				v_CmpUrgencyAndProfileStandart CUPS with (nolock)
			WHERE
				ISNULL(CUPS.Lpu_id,0) = 0
			';

		$placesQuery = 'SELECT
				--CUPSRP.CmpUrgencyAndProfileStandartRefPlace_id,
				CUPSRP.CmpUrgencyAndProfileStandart_id,
				CUPSRP.CmpCallPlaceType_id
			FROM
				v_CmpUrgencyAndProfileStandartRefPlace CUPSRP with (nolock)
				left join v_CmpUrgencyAndProfileStandart CUPS with (nolock) on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRP.CmpUrgencyAndProfileStandart_id
			WHERE ISNULL(CUPS.Lpu_id,0) = 0
			';

		$ETSpecQuery = 'SELECT
				--CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_id,
				CUPSRSP.CmpUrgencyAndProfileStandart_id,
				CUPSRSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,
				CUPSRSP.EmergencyTeamSpec_id
			FROM
				v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSRSP with (nolock)
				left join v_CmpUrgencyAndProfileStandart CUPS with (nolock) on CUPS.CmpUrgencyAndProfileStandart_id = CUPSRSP.CmpUrgencyAndProfileStandart_id
			WHERE ISNULL(CUPS.Lpu_id,0) = 0
			';


		$rulesResult = $this->db->query($rulesQuery);
		$placesResult = $this->db->query($placesQuery);
		$ETSpecResult = $result = $this->db->query($ETSpecQuery);
		if (!is_object($rulesResult) || !is_object($placesResult) || !is_object($ETSpecResult)) {
			return false;
		} else {
			$rulesResult = $rulesResult->result('array');
			$placesResult = $placesResult->result('array');
			$ETSpecResult = $ETSpecResult->result('array');
		}


		//Собираем логику в один многомерный массив для удобства
		$rules = array();
		foreach ($rulesResult as $rule) {
			$rules["{$rule['CmpUrgencyAndProfileStandart_id']}"] = $rule;
		}

		foreach ($placesResult as $place) {
			if (!isset($rules["{$place['CmpUrgencyAndProfileStandart_id']}"]['Places'])) {
				$rules["{$place['CmpUrgencyAndProfileStandart_id']}"]['Places'] = array();
			}
			$rules["{$place['CmpUrgencyAndProfileStandart_id']}"]['Places'][] = $place;
		}

		foreach ($ETSpecResult as $spec) {
			if (!isset($rules["{$spec['CmpUrgencyAndProfileStandart_id']}"]['Spec'])) {
				$rules["{$spec['CmpUrgencyAndProfileStandart_id']}"]['Spec'] = array();
			}
			$rules["{$spec['CmpUrgencyAndProfileStandart_id']}"]['Spec'][] = $spec;
		}



		$queryInsertRule = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_CmpUrgencyAndProfileStandart_ins
				@CmpUrgencyAndProfileStandart_id = @Res output,
				@CmpReason_id = :CmpReason_id,
				@Lpu_id = :Lpu_id,
				@CmpUrgencyAndProfileStandart_Urgency = :CmpUrgencyAndProfileStandart_Urgency,
				@CmpUrgencyAndProfileStandart_UntilAgeOf =:CmpUrgencyAndProfileStandart_UntilAgeOf,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';

		$queryInsertRefPlace = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_CmpUrgencyAndProfileStandartRefPlace_ins
				@CmpUrgencyAndProfileStandartRefPlace_id = @Res output,
				@CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id,
				@CmpCallPlaceType_id = :CmpCallPlaceType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';

		$queryInsertRefETSpec = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_CmpUrgencyAndProfileStandartRefSpecPriority_ins
				@CmpUrgencyAndProfileStandartRefSpecPriority_id = @Res output,
				@CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id,
				@EmergencyTeamSpec_id = :EmergencyTeamSpec_id,
				@CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority = :CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';


		//Сохраняем дефолтные правила с проставленным Lpu_id
		$this->db->trans_begin();
		foreach ($rules as $rule) {


			//Сначала сохраняем само правило
			$resultInsertRule = $this->db->query($queryInsertRule, array(
				'CmpReason_id'=>$rule['CmpReason_id'],
				'CmpUrgencyAndProfileStandart_Urgency'=>$rule['CmpUrgencyAndProfileStandart_Urgency'],
				'CmpUrgencyAndProfileStandart_UntilAgeOf'=>$rule['CmpUrgencyAndProfileStandart_UntilAgeOf'],
				'Lpu_id'=>$data['Lpu_id'],
				'pmUser_id'=>$data['pmUser_id']
			));

			if (!is_object($resultInsertRule)) {
				$this->db->trans_rollback();
				return false;
			}
			$resultInsertRule = $resultInsertRule->result('array');

			if (!empty($resultInsertRule[0]['Error_msg'])) {
				$this->db->trans_rollback();
				return $resultInsertRule;
			}
			//Затим  сохраняем привязанные к правилу места
			foreach ($rule['Places'] as $place) {
				$resultInsertRuleRefPlace = $this->db->query($queryInsertRefPlace, array(
					'CmpUrgencyAndProfileStandart_id'=>$resultInsertRule[0]['CmpUrgencyAndProfileStandart_id'],
					'CmpCallPlaceType_id'=>$place['CmpCallPlaceType_id'],
					'pmUser_id'=>$data['pmUser_id']
				));

				if (!is_object($resultInsertRuleRefPlace)) {
					$this->db->trans_rollback();
					return false;
				}

				$resultInsertRuleRefPlace = $resultInsertRuleRefPlace->result('array');

				if (!empty($resultInsertRuleRefPlace[0]['Error_msg'])) {
					$this->db->trans_rollback();
					return $resultInsertRuleRefPlace;
				}
			}

			if(!empty($rule['Spec'])){
				//Затим  сохраняем привязанные к правилу профили бригад и их приоритеты
				foreach ($rule['Spec'] as $spec) {

					$resultInsertRuleRefSpec = $this->db->query($queryInsertRefETSpec, array(
						'CmpUrgencyAndProfileStandart_id'=>$resultInsertRule[0]['CmpUrgencyAndProfileStandart_id'],
						'EmergencyTeamSpec_id'=>$spec['EmergencyTeamSpec_id'],
						'CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority'=>$spec['CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority'],
						'pmUser_id'=>$data['pmUser_id']
					));

					if (!is_object($resultInsertRuleRefSpec)) {
						$this->db->trans_rollback();
						return false;
					}

					$resultInsertRuleRefSpec = $resultInsertRuleRefSpec->result('array');

					if (!empty($resultInsertRuleRefSpec[0]['Error_msg'])) {
						$this->db->trans_rollback();
						return $resultInsertRuleRefSpec;
					}
				}
			}
		}

		$this->db->trans_commit();
		return array(array('Error_Msg'=>''));
	}

	/**
	 * Удаление правила логики предложения бригады на вызов
	 * @return boolean
	 */
	public function deleteCmpUrgencyAndProfileStandartRule($data) {
		if (empty($data['CmpUrgencyAndProfileStandart_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор правила'));
		}

		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_CmpUrgencyAndProfileStandart_delWithRefs
				@CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';


		$result = $this->db->query($query, array(
			'CmpUrgencyAndProfileStandart_id'=>$data['CmpUrgencyAndProfileStandart_id']
		));

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}

	}

	/**
	 * Получене списка мест, привязанных к правилу
	 * @return boolean
	 */
	public function getCmpUrgencyAndProfileStandartPlaces($data) {
		if (empty($data['CmpUrgencyAndProfileStandart_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор правила'));
		}

		$query = '
			SELECT
				CCPT.CmpCallPlaceType_id,
				CCPT.CmpCallPlaceType_Name,
				CCPT.CmpCallPlaceType_Code
			FROM
				v_CmpUrgencyAndProfileStandartRefPlace CUPSRF with (nolock)
				left join v_CmpCallPlaceType CCPT with(nolock) on CUPSRF.CmpCallPlaceType_id = CCPT.CmpCallPlaceType_id
			WHERE
				CUPSRF.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id
			';


		$result = $this->db->query($query, array(
			'CmpUrgencyAndProfileStandart_id'=>$data['CmpUrgencyAndProfileStandart_id']
		));

		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Получене списка мест, привязанных к правилу
	 * @return boolean
	 */
	public function getCmpUrgencyAndProfileStandartSpecPriority($data) {

		$queryParams = array();
		if (empty($data['CmpUrgencyAndProfileStandart_id'])) {

			$query = '
				SELECT
					ETS.EmergencyTeamSpec_id,
					ETS.EmergencyTeamSpec_Code,
					ETS.EmergencyTeamSpec_Name,
					1 as ProfilePriority

				FROM
					v_EmergencyTeamSpec ETS with (nolock)
				';
				/*WHERE ETS.EmergencyTeamSpec_id in (16,21,22,23,24,25,26,27,28,29,30)*/
		} else {
			$queryParams['CmpUrgencyAndProfileStandart_id'] = $data['CmpUrgencyAndProfileStandart_id'];
			$query = '
				SELECT
					ETS.EmergencyTeamSpec_id,
					ETS.EmergencyTeamSpec_Code,
					ETS.EmergencyTeamSpec_Name,
					CUPSSP.CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority as ProfilePriority

				FROM
					v_CmpUrgencyAndProfileStandart CUPS with (nolock)
					left join v_CmpUrgencyAndProfileStandartRefSpecPriority CUPSSP with (nolock) on CUPSSP.CmpUrgencyAndProfileStandart_id= CUPS.CmpUrgencyAndProfileStandart_id
					left join v_EmergencyTeamSpec ETS with (nolock) on CUPSSP.EmergencyTeamSpec_id = ETS.EmergencyTeamSpec_id

				WHERE
					CUPS.CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id

				';
		}

		$result = $this->db->query($query,$queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение правила предложения бригады на вызов, и срочность вызова в соответствии с указанными местами вызова
	 * @return boolean
	 */

	public function saveCmpUrgencyAndProfileStandartRule($data) {

		$queryParams = array(
			'pmUser_id'=>$data['pmUser_id'],
			'CmpCallCardAcceptor_id' => !empty($data['CmpCallCardAcceptor_id']) ? $data['CmpCallCardAcceptor_id'] : null,
			'CmpUrgencyAndProfileStandart_HeadDoctorObserv' => !empty($data['CmpUrgencyAndProfileStandart_HeadDoctorObserv']) ? $data['CmpUrgencyAndProfileStandart_HeadDoctorObserv'] : null,
			'CmpUrgencyAndProfileStandart_MultiVictims' => !empty($data['CmpUrgencyAndProfileStandart_MultiVictims']) ? $data['CmpUrgencyAndProfileStandart_MultiVictims'] : null,
		);

		if (empty($data['Lpu_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор ЛПУ'));
		} else {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (empty($data['CmpReason_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор повода вызова'));
		} else {
			$queryParams['CmpReason_id'] = $data['CmpReason_id'];
		}

		if (empty($data['CmpUrgencyAndProfileStandart_Urgency'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: базовая срочность'));
		} else {
			$queryParams['CmpUrgencyAndProfileStandart_Urgency'] = $data['CmpUrgencyAndProfileStandart_Urgency'];
		}

		if (!isset($data['CmpCallPlaceType_Array']) || !is_array($data['CmpCallPlaceType_Array']) || sizeof($data['CmpCallPlaceType_Array']) == 0) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: массив мест вызова'));
		}

		if (!isset($data['CmpUrgencyAndProfileStandartRefSpecPriority_Array']) || !is_array($data['CmpUrgencyAndProfileStandartRefSpecPriority_Array']) || sizeof($data['CmpUrgencyAndProfileStandartRefSpecPriority_Array']) == 0) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: массив профилей бригад и их приоритетов'));
		}


		$checkResult = $this->checkRuleUniqueness($data);
		if (!is_array($checkResult)) {
			return false;
		}

		//Если размер возвращаемого массива = 0, значит не найдено ни одно конфликтующее правило
		if (sizeof($checkResult) != 0) {
			//Если в результате проверки на конфликт с другими правилами произошла ошибка, возвращаем результат

			if (isset($checkResult[0]['success']) && $checkResult[0]['success'] == false) {
				return $checkResult;
			}
			//Если в результате проверки на конфликт с другими правилами нашли конфликтующее правило, возвращаем его идентификатор и соответствующий код ошибки

			return array(array('success'=>false,'Error_Code'=>'ruleconflict','CmpUrgencyAndProfileStandart_id' => $checkResult[0]['CmpUrgencyAndProfileStandart_id']));

		}


		$queryParams['CmpUrgencyAndProfileStandart_UntilAgeOf'] = (!empty($data['CmpUrgencyAndProfileStandart_UntilAgeOf']))?$data['CmpUrgencyAndProfileStandart_UntilAgeOf']: NULL;

		$this->db->trans_begin();
		if (empty($data['CmpUrgencyAndProfileStandart_id'])) {
			$procedure = 'p_CmpUrgencyAndProfileStandart_ins';
			$queryParams['CmpUrgencyAndProfileStandart_id'] = NULL;
		} else {

			$procedure = 'p_CmpUrgencyAndProfileStandart_upd';
			$queryParams['CmpUrgencyAndProfileStandart_id'] = $data['CmpUrgencyAndProfileStandart_id'];
			$deleteRefsQuery = '
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_CmpUrgencyAndProfileStandart_delRefsPlacesAndSpec
					@CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				';
			$deleteRefsResult = $this->db->query($deleteRefsQuery,array(
				'CmpUrgencyAndProfileStandart_id'=>$data['CmpUrgencyAndProfileStandart_id']
			));

			if (!is_object($deleteRefsResult)) {
				$this->db->trans_rollback();
				return false;
			} else {
				$deleteRefsResult = $deleteRefsResult->result('array');
				if (strlen($deleteRefsResult[0]['Error_Msg'])!=0) {
					$this->db->trans_rollback();
					return $deleteRefsResult;
				}
			}

		}

		$querySaveRule = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :CmpUrgencyAndProfileStandart_id;
			exec {$procedure}
				@CmpUrgencyAndProfileStandart_id = @Res output,
				@CmpUrgencyAndProfileStandart_Urgency = :CmpUrgencyAndProfileStandart_Urgency,
				@CmpUrgencyAndProfileStandart_UntilAgeOf = :CmpUrgencyAndProfileStandart_UntilAgeOf,
				@CmpCallCardAcceptor_id = :CmpCallCardAcceptor_id,
				@CmpUrgencyAndProfileStandart_HeadDoctorObserv = :CmpUrgencyAndProfileStandart_HeadDoctorObserv,
				@CmpUrgencyAndProfileStandart_MultiVictims = :CmpUrgencyAndProfileStandart_MultiVictims,
				@CmpReason_id = :CmpReason_id,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;

			";
		$queryInsertRefPlace = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_CmpUrgencyAndProfileStandartRefPlace_ins
				@CmpUrgencyAndProfileStandartRefPlace_id = @Res output,
				@CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id,
				@CmpCallPlaceType_id = :CmpCallPlaceType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';

		$queryInsertRefETSpec = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = null;
			exec p_CmpUrgencyAndProfileStandartRefSpecPriority_ins
				@CmpUrgencyAndProfileStandartRefSpecPriority_id = @Res output,
				@CmpUrgencyAndProfileStandart_id = :CmpUrgencyAndProfileStandart_id,
				@EmergencyTeamSpec_id = :EmergencyTeamSpec_id,
				@CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority = :CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpUrgencyAndProfileStandart_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';

		//Сначала сохраняем само правило
		$resultSaveRule = $this->db->query($querySaveRule , $queryParams);

		if (!is_object($resultSaveRule)) {
			$this->db->trans_rollback();
			return false;
		}
		$resultSaveRule = $resultSaveRule->result('array');

		if (!empty($resultSaveRule[0]['Error_msg'])) {
			$this->db->trans_rollback();
			return $resultSaveRule;
		}
		//Затим  сохраняем привязанные к правилу места
		foreach ($data['CmpCallPlaceType_Array'] as $place) {

			if (empty($place['CmpCallPlaceType_id'])) {
				$this->db->trans_rollback();
				return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор места вызова'));
			}

			$resultInsertRuleRefPlace = $this->db->query($queryInsertRefPlace, array(
				'CmpUrgencyAndProfileStandart_id'=>$resultSaveRule[0]['CmpUrgencyAndProfileStandart_id'],
				'CmpCallPlaceType_id'=>$place['CmpCallPlaceType_id'],
				'pmUser_id'=>$data['pmUser_id']
			));

			if (!is_object($resultInsertRuleRefPlace)) {
				$this->db->trans_rollback();
				return false;
			}

			$resultInsertRuleRefPlace = $resultInsertRuleRefPlace->result('array');

			if (!empty($resultInsertRuleRefPlace[0]['Error_msg'])) {
				$this->db->trans_rollback();
				return $resultInsertRuleRefPlace;
			}
		}

		//Затим  сохраняем привязанные к правилу профили бригад и их приоритеты
		foreach ($data['CmpUrgencyAndProfileStandartRefSpecPriority_Array'] as $spec) {
			if (empty($spec['EmergencyTeamSpec_id'])) {
				$this->db->trans_rollback();
				return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор профиля бригады'));
			}

			if (!array_key_exists('CmpUrgencyAndProfileStandartRefSpecPriority_Priority', $spec)) {
				$this->db->trans_rollback();
				return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: приоритет назначения профиля на вызов'));
			}

			$resultInsertRuleRefSpec = $this->db->query($queryInsertRefETSpec, array(
				'CmpUrgencyAndProfileStandart_id'=>$resultSaveRule[0]['CmpUrgencyAndProfileStandart_id'],
				'EmergencyTeamSpec_id'=>$spec['EmergencyTeamSpec_id'],
				'CmpUrgencyAndProfileStandartRefSpecPriority_ProfilePriority'=>$spec['CmpUrgencyAndProfileStandartRefSpecPriority_Priority'],
				'pmUser_id'=>$data['pmUser_id']
			));

			if (!is_object($resultInsertRuleRefSpec)) {
				$this->db->trans_rollback();
				return false;
			}

			$resultInsertRuleRefSpec = $resultInsertRuleRefSpec->result('array');

			if (!empty($resultInsertRuleRefSpec[0]['Error_msg'])) {
				$this->db->trans_rollback();
				return $resultInsertRuleRefSpec;
			}
		}
		$this->db->trans_commit();
		return $resultSaveRule;
	}

	/**
	 * Получения ID комбобокса по его коду
	 * @param type $data
	 */
	public function getComboIdByCode($data) {
		$query = "
			SELECT
				CMB.CmpCloseCardCombo_id
			FROM
				dbo.v_CmpCloseCardCombo CMB with (nolock)
			WHERE
				CMB.CmpCloseCardCombo_Code = :CmpCloseCardCombo_Code
		";
		$ComboResult = $this->db->query( $query, array(
			'CmpCloseCardCombo_Code' => $data
		));
		$ComboRetrun = $ComboResult->result( 'array' );
		if ( sizeof( $ComboRetrun ) ) {
			return $ComboRetrun[ 0 ][ 'CmpCloseCardCombo_id' ];
		}
		return false;
	}

	/**
	 * Получение ID комбобокса по ComboSys
	 */
	public function getComboIdByComboSys($data) {
		if(empty($data['ComboSys'])){
			return false;
		}

		$query = "
			SELECT
				CMB.CmpCloseCardCombo_id
			FROM
				dbo.v_CmpCloseCardCombo CMB with (nolock)
			WHERE
				CMB.ComboSys = :ComboSys
		";
		$ComboResult = $this->db->query( $query, array(
			'ComboSys' => $data['ComboSys']
		));
		$ComboRetrun = $ComboResult->result( 'array' );
		if ( sizeof( $ComboRetrun ) ) {
			return $ComboRetrun[ 0 ][ 'CmpCloseCardCombo_id' ];
		}
		return false;
	}

	/**
	 * Проверка уникальности правила.
	 * @param type $data
	 */
	private function checkRuleUniqueness($data) {

		if (empty($data['Lpu_id']) || empty($data['CmpReason_id']) || !isset($data['CmpCallPlaceType_Array']) || !is_array($data['CmpCallPlaceType_Array']) || sizeof($data['CmpCallPlaceType_Array']) == 0 ) {
			return array(array('success'=>false,'Error_Msg'=>'Проверка уникальости правила: Не указаны обязательные параметры'));
		}
		$addWhere = '';
		$queryParams = array(
			'CmpReason_id'=>$data['CmpReason_id'],
			'CmpUrgencyAndProfileStandart_UntilAgeOf'=>$data['CmpUrgencyAndProfileStandart_UntilAgeOf'],
			'Lpu_id'=>$data['Lpu_id'],
		);

		$editRuleClause = '';
		//В случае редактирования, а не добавления правила, необходимо исключить из выборки конфликтных правил редактируемое правило
		if (!empty($data['CmpUrgencyAndProfileStandart_id'])) {
			$editRuleClause = 'AND CUPS.CmpUrgencyAndProfileStandart_id != :CmpUrgencyAndProfileStandart_id';
			$queryParams['CmpUrgencyAndProfileStandart_id'] = $data['CmpUrgencyAndProfileStandart_id'];
		}

		foreach ($data['CmpCallPlaceType_Array'] as $key => $value) {
			$addWhere .= " OR CUPSRF.CmpCallPlaceType_id = :CmpCallPlaceType_$key";
			$queryParams["CmpCallPlaceType_$key"] = $value['CmpCallPlaceType_id'];
		}

		$query = "
			SELECT TOP 1
				CUPS.CmpUrgencyAndProfileStandart_id,
				CUPSRF.CmpCallPlaceType_id,
				CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf
			FROM
				v_CmpUrgencyAndProfileStandartRefPlace CUPSRF with (nolock)
				left join v_CmpUrgencyAndProfileStandart CUPS with (nolock) on CUPSRF.CmpUrgencyAndProfileStandart_id = CUPS.CmpUrgencyAndProfileStandart_id
			WHERE

				CUPS.CmpReason_id = :CmpReason_id
				AND CUPS.Lpu_id = :Lpu_id
				AND ISNULL(CUPS.CmpUrgencyAndProfileStandart_UntilAgeOf,0) = ISNULL(:CmpUrgencyAndProfileStandart_UntilAgeOf,0)
				AND ((1=0) $addWhere)
				$editRuleClause
			";

		$result = $this->db->query($query,$queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохранение услуги в карте вызова СМП
	 */
	function saveCmpCallCardUsluga($data) {
		$params = array(
			'CmpCallCardUsluga_id' => (empty($data['CmpCallCardUsluga_id']) || $data['CmpCallCardUsluga_id'] < 0) ? null:$data['CmpCallCardUsluga_id'],
			'CmpCallCard_id' => $data['CmpCallCard_id'],
			'CmpCallCardUsluga_setDate' => $data['CmpCallCardUsluga_setDate'],
			'CmpCallCardUsluga_setTime' => $data['CmpCallCardUsluga_setTime'],
			'MedStaffFact_id' => !empty($data['MedStaffFact_id']) ? $data['MedStaffFact_id']: null,
			'PayType_id' => !empty($data['PayType_id']) ? $data['PayType_id']: null,
			'PayType_Code' => !empty($data['PayType_Code']) ? $data['PayType_Code']: null,
			'UslugaCategory_id' => $data['UslugaCategory_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_id' => empty($data['UslugaComplexTariff_id'])?null:$data['UslugaComplexTariff_id'],
			'CmpCallCardUsluga_Cost' => empty($data['CmpCallCardUsluga_Cost'])?null:$data['CmpCallCardUsluga_Cost'],
			'CmpCallCardUsluga_Kolvo' => empty($data['CmpCallCardUsluga_Kolvo'])?null:$data['CmpCallCardUsluga_Kolvo'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		if(!empty($data['PayType_Code'])){
			$payTypesql = "select PayType_id from v_PayType where PayType_Code = :PayType_Code";
			$payTypeId = $this->getFirstResultFromQuery($payTypesql, $params);
			if(!empty($payTypeId)){
				$params["PayType_id"] = $payTypeId;
			}
		}

		$regionNick = $this->regionNick;

		//Проверяем на Перми, что услуга выполнена в ближайшие 24 часа ПОСЛЕ вызова
		if ( in_array($regionNick, array('perm', 'ekb')) ) {
			$CmpCallCard_data = $this->loadCmpCallCardEditForm( array(
				'CmpCallCard_id' => $data[ 'CmpCallCard_id' ]
					) ) ;

			if ( !$this->isSuccessful( $CmpCallCard_data ) ) {
				return $CmpCallCard_data ;
			}

			$CmpCallCardUsluga_setDT = DateTime::createFromFormat( "Y-m-d H:i" , $data[ 'CmpCallCardUsluga_setDate' ] . " " . $data[ 'CmpCallCardUsluga_setTime' ] ) ;
			// текстовая выборка, ибо затесавшиеся в CmpCallCard_prmDT мешают проверке при сохранении услуг
			// @task https://redmine.swan.perm.ru/issues/100697
			$CmpCallCard_prmDT = DateTime::createFromFormat( "Y-m-d H:i" , $CmpCallCard_data[ 0 ][ 'CmpCallCard_prmDT' ] );

			if ( $CmpCallCardUsluga_setDT === FALSE ) {
				return $this->createError( false , 'Неверные значения в поле дата/время вызова' ) ;
			}

			$date_diff = $CmpCallCard_prmDT->diff( $CmpCallCardUsluga_setDT ) ;

			if ( ($date_diff === FALSE) || ($CmpCallCard_prmDT > $CmpCallCardUsluga_setDT) || (( $date_diff->y * 24 * 30 * 12 + $date_diff->m * 24 * 30 + $date_diff->d * 24 + $date_diff->h ) > 24 ) ) {
				return $this->createError( false , 'Услуга должна быть выполнена не раньше даты и времени приема и не позднее, чем 24 часа.' ) ;
			}
		}

		if ( in_array($regionNick, array('perm', 'ekb')) ) {
			$query = "
				select top 1
					count(UCT.UslugaComplexTariff_id) as Count
				from v_UslugaComplexTariff UCT with(nolock)
				where
					UCT.UslugaComplex_id = :UslugaComplex_id
					and UCT.UslugaComplexTariff_begDate <= :CmpCallCardUsluga_setDate
					and (UCT.UslugaComplexTariff_endDate > :CmpCallCardUsluga_setDate or UCT.UslugaComplexTariff_endDate is null)
			";
			$tariff_count = $this->getFirstResultFromQuery($query, $params);
			if ($tariff_count === false) {
				return $this->createError('', 'Ошибка при проверке наличия тарифов');
			}
			if ($tariff_count == 0) {
				$this->addWarningMsg('Карта СМП: На данную услугу нет тарифа!');
			}
		}

		//При пустом месте работы пробуем достать из карты
		if(empty($data['MedStaffFact_id'])){
			$query = "
				select top 1
					MedStaffFact_id
				from {$this->schema}.v_CmpCloseCard with(nolock)
				where
					CmpCallCard_id = :CmpCallCard_id
			";
			$params['MedStaffFact_id'] = $this->getFirstResultFromQuery($query, $data);
		}

		$procedure = 'p_CmpCallCardUsluga_ins';
		if (!empty($data['CmpCallCardUsluga_id']) && $data['CmpCallCardUsluga_id'] > 0) {
			$procedure = 'p_CmpCallCardUsluga_upd';
		}

		$query = "
			declare
				@Res bigint = :CmpCallCardUsluga_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$procedure}
				@CmpCallCardUsluga_id = @Res output,
				@CmpCallCard_id = :CmpCallCard_id,
				@CmpCallCardUsluga_setDate = :CmpCallCardUsluga_setDate,
				@CmpCallCardUsluga_setTime = :CmpCallCardUsluga_setTime,
				@MedStaffFact_id = :MedStaffFact_id,
				@PayType_id = :PayType_id,
				@UslugaCategory_id = :UslugaCategory_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexTariff_id = :UslugaComplexTariff_id,
				@CmpCallCardUsluga_Cost = :CmpCallCardUsluga_Cost,
				@CmpCallCardUsluga_Kolvo = :CmpCallCardUsluga_Kolvo,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpCallCardUsluga_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//var_dump(getDebugSQL($query, $params)); exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО с обслуживанием на дому
	 * @return boolean
	 */
	public function getLpuWithOperSmp($data){
		$sql = "
			SELECT DISTINCT
					lpu.Lpu_id,
					lpu.Lpu_Name,
					lpu.Lpu_Nick
				FROM
					v_SmpUnitParam sup with(nolock)
					LEFT JOIN v_SmpUnitType sut with(nolock) ON sup.SmpUnitType_id = sut.SmpUnitType_id
					INNER JOIN v_LpuBuilding lb with(nolock) ON(lb.LpuBuilding_id=sup.LpuBuilding_id)
					LEFT JOIN v_lpu lpu with (nolock) on lpu.Lpu_id = lb.Lpu_id
				WHERE
				 ISNULL(sup.LpuBuilding_pid, 1) = 1
				 and sut.SmpUnitType_Code = 4
				 AND LB.LpuBuildingType_id in (27, 28)
				 and lb.LpuBuilding_begDate <= dbo.tzGetDate()
				 and (lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate())
		";
		$result = $this->db->query($sql);
		if (is_object($result)) {
			return $result->result_array();
		} else {
			return false;
		}
	}


	/**
	 * Удаление услуги, прикреплённой к карте вызова
	 * @param type $data
	 * @return type
	 */
	public function deleteCmpCallCardUsluga($data) {

		if (empty($data['CmpCallCardUsluga_id'])) {
			return $this->createError('', 'Не задан обязательный параметр: идентификатор услуги');
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_CmpCallCardUsluga_del
				@CmpCallCardUsluga_id = :CmpCallCardUsluga_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		return $this->queryResult($query, $data);

	}

	/**
	 * Получение списка услуг в карте вызова СМП
	 */
	function loadCmpCallCardUslugaGrid($data) {
		$params = array('CmpCallCard_id' => $data['CmpCallCard_id']);

		$query = "
			select
				CCC.Person_id,
				CCCU.CmpCallCardUsluga_id,
				CCCU.CmpCallCard_id,
				CCCU.CmpCallCardUsluga_Kolvo,
				CCCU.CmpCallCardUsluga_Cost,
				CCCU.UslugaComplexTariff_id,
				CCCU.MedStaffFact_id,
				CCCU.PayType_id,
				CCCU.UslugaCategory_id,
				CCCU.UslugaComplex_id,
				convert(varchar(10), CCCU.CmpCallCardUsluga_setDate, 104) as CmpCallCardUsluga_setDate,
				convert(varchar(5), CCCU.CmpCallCardUsluga_setTime, 108) as CmpCallCardUsluga_setTime,
				UC.UslugaComplex_Code,
				UC.UslugaComplex_Name,
				UCT.UslugaComplexTariff_Name,
				'unchanged' as status
			from
				v_CmpCallCardUsluga CCCU with(nolock)
				left join v_UslugaComplex UC with(nolock) on UC.UslugaComplex_id = CCCU.UslugaComplex_id
				left join v_CmpCallCard CCC with(nolock) on CCC.CmpCallCard_id = CCCU.CmpCallCard_id
				left join v_UslugaComplexTariff UCT with (nolock) on CCCU.UslugaComplexTariff_id = UCT.UslugaComplexTariff_id
			where
				CCCU.CmpCallCard_id = :CmpCallCard_id
		";
        //var_dump($query, $params); exit;
		$response = $this->queryResult($query, $params);
		//return array('data' => $response);
		return $response;
	}

	/**
	 * Получение данных для формы редактирования услуги в карте вызова СМП
	 */
	function loadCmpCallCardUslugaForm($data) {
		$params = array('CmpCallCardUsluga_id' => $data['CmpCallCardUsluga_id']);

		$query = "
			select top 1
				CCCU.CmpCallCardUsluga_id,
				CCCU.CmpCallCard_id,
				convert(varchar(10), CCCU.CmpCallCardUsluga_setDate, 120) as CmpCallCardUsluga_setDate,
				convert(varchar(5), CCCU.CmpCallCardUsluga_setTime, 108) as CmpCallCardUsluga_setTime,
				CCCU.MedStaffFact_id,
				CCCU.PayType_id,
				CCCU.UslugaCategory_id,
				CCCU.UslugaComplex_id,
				CCCU.UslugaComplexTariff_id,
				CCCU.CmpCallCardUsluga_Cost,
				CCCU.CmpCallCardUsluga_Kolvo,
				CCC.Person_id
			from
				v_CmpCallCardUsluga CCCU with(nolock)
				left join v_CmpCallCard CCC with(nolock) on CCC.CmpCallCard_id = CCCU.CmpCallCard_id
			where
				CCCU.CmpCallCardUsluga_id = :CmpCallCardUsluga_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка комбинированного справочника улиц и неформализованных адресов СМП
	 */
	public function loadCmpEquipmentCombo( $data ) {
		$sql = "SELECT * FROM v_CmpEquipment with(nolock)";
		$query = $this->db->query( $sql );
		if ( is_object( $query ) ) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 * Метод сохранения списка услуг для карты вызова
	 * @param type $data
	 * @return boolean|array
	 */

	public function saveCmpCallCardUslugaList( $data ) {

		$rules = array(
			array( 'field' => 'CmpCallCard_id' , 'label' => 'Идентификатор карты вызова СМП' , 'rules' => 'required' , 'type' => 'id' ) ,
			array( 'field' => 'usluga_array' , 'label' => 'Список услуг' , 'rules' => '' , 'type' => 'array', 'default' => array() ) ,
			array( 'field' => 'pmUser_id' , 'rules' => 'required' , 'label' => 'Идентификатор пользователя' , 'type' => 'id' ) ,
		) ;
		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;
		if ( !$queryParams || !empty( $err ) )
			return $err ;
		$queryParams[ 'usluga_array' ] = $data[ 'usluga_array' ];

        $existUslugaList = $this->loadCmpCallCardUslugaGrid($data);

		//пробегаемся по услугам, которые пришли с формы
		for ( $i = 0 ; $i < sizeof( $queryParams[ 'usluga_array' ] ) ; $i++ ) {

			$usluga_data = (array)$queryParams[ 'usluga_array' ][ $i ] ;

			if (!empty($usluga_data['status']) && ($usluga_data['status'] == 'deleted')) {
				continue;
			}

            $unchangedUsluga = false;

            //если услуга в списке существующих в базе, то удаляем из списка элемент $existUslugaList
            //статус записи проставляем на редактирование, если нет - на удаление
            foreach ($existUslugaList as $key => $value){
                if($value['UslugaComplex_id'] == $usluga_data['UslugaComplex_id'] ){
                    $usluga_data['CmpCallCardUsluga_id'] = $value['CmpCallCardUsluga_id'];

                    if($usluga_data['CmpCallCardUsluga_Kolvo'] == $value['CmpCallCardUsluga_Kolvo']){
                        $unchangedUsluga = true;
                    }
                    unset($existUslugaList[$key]);

                    break;
                }

                //для Адиса
                if (!empty($usluga_data['status']) && ($usluga_data['status'] == 'edited')) {
                    unset($existUslugaList[$key]);
                }
            }

			if (!empty($usluga_data['status']) && $usluga_data['status'] == 'edited') {
				// с формы редактирования карт АДИС приходит status=edited если услуга изменена
			} elseif ($unchangedUsluga) continue; //если услуга не была изменена пропускаем ее

            $usluga_data[ 'CmpCallCardUsluga_id' ] = empty($usluga_data[ 'CmpCallCardUsluga_id' ]) ? NULL : $usluga_data[ 'CmpCallCardUsluga_id' ] ;
            $usluga_data[ 'pmUser_id' ] = $queryParams[ 'pmUser_id' ] ;
            $usluga_data[ 'CmpCallCard_id' ] = $queryParams[ 'CmpCallCard_id' ] ;

            //Преобразуем формат даты для метода сохранения услуги в модели
            $CmpCallCardUsluga_setDate = DateTime::createFromFormat( 'd.m.Y' , $usluga_data[ 'CmpCallCardUsluga_setDate' ] ) ;
            if ( $CmpCallCardUsluga_setDate === FALSE ) {
                return $this->createError( '' , 'Ошибка преобразования даты выполнения услуги. Дата выполнения услуги должна передаваться в формате дд.мм.гггг' );
            }
            $usluga_data[ 'CmpCallCardUsluga_setDate' ] = $CmpCallCardUsluga_setDate->format( 'Y-m-d' ) ;

            $save_usluga_response = $this->saveCmpCallCardUsluga( $usluga_data ) ;

			if ( !$this->isSuccessful( $save_usluga_response ) ) {
				return $save_usluga_response ;
			}

		}

        //те услуги которые не пришли удаляются
        foreach ($existUslugaList as $value){
            $this->deleteCmpCallCardUsluga( $value );
        }

		return array(array('success' => true , 'Error_Msg' => NULL));
	}

	/**
	 * Получение списка подстанций СМП
	 * @return boolean
	 */
	public function loadCmpCallCardAcceptorList($data){
		$sql = "
			SELECT
				CmpCallCardAcceptor_id,
				CmpCallCardAcceptor_SysNick,
				CmpCallCardAcceptor_Code,
				CmpCallCardAcceptor_Name
			FROM
				v_CmpCallCardAcceptor with(nolock)
		";
		return $this->db->query($sql, array(
			'pmUser_id' => $data['pmUser_id']
		))->result_array();
	}
	
	/**
	 * Получение списка подстанций СМП
	 * @return boolean
	 */
	public function getCmpCallDiagnosesFields($data){
		$sql = "
			select
				ClCCC.Diag_id,
				D.Diag_FullName as d_name,
				ClCCC.Diag_uid,
				DU.Diag_FullName as du_name,
				ClCCC.Diag_sid,
				DS.Diag_FullName as ds_name,
				CCC.Lpu_hid,
				LB.Lpu_Nick as mh_name,
				CR.CmpResult_id,
				COALESCE(CR.CmpResult_Name,CRCB.ComboName) as cr_name
			from v_CmpCallCard CCC with (nolock)
			LEFT JOIN {$this->schema}.v_CmpCloseCard ClCCC with (nolock) on CCC.CmpCallCard_id = ClCCC.CmpCallCard_id
			LEFT JOIN v_Diag D on ClCCC.Diag_id = D.Diag_id
			LEFT JOIN v_Diag DU on ClCCC.Diag_uid = DU.Diag_id
			LEFT JOIN v_Diag DS on ClCCC.Diag_sid = DS.Diag_id
			LEFT JOIN v_CmpResult CR on ClCCC.CmpResult_id = CR.CmpResult_id
			outer apply (
				select top 1
					CLCCB.ComboName
				from {$this->schema}.v_CmpCloseCardRel CLCR
				left join {$this->comboSchema}.v_CmpCloseCardCombo CLCCB with (nolock) on CLCCB.CmpCloseCardCombo_id = CLCR.CmpCloseCardCombo_id
				left join {$this->comboSchema}.v_CmpCloseCardCombo pCLCCB with (nolock) on pCLCCB.CmpCloseCardCombo_id = CLCCB.Parent_id
				where CLCR.CmpCloseCard_id = ClCCC.CmpCloseCard_id and pCLCCB.CmpCloseCardCombo_Code = 223
			) CRCB
			LEFT JOIN v_Lpu LB on CCC.Lpu_hid = LB.Lpu_id
			where CCC.CmpCallCard_id = :CmpCallCard_id
		";
		
		return $this->db->query($sql, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		))->result_array();
	}

	/**
	 * Получение списка МО с обслуживанием на дому
	 * @return boolean
	 */
	public function loadLpuHomeVisit($data){

		$where = array();
		$where[] = "ds.DataStorage_Name = :DataStorage_Name";
		$where[] = "ds.DataStorage_Value = 1";
		$where[] = "ds.DataStorageGroup_SysNick = :DataStorageGroup_SysNick";

		$params = array(
			'DataStorage_Name' => 'homevizit_isallowed',
			'DataStorageGroup_SysNick' => 'homevizit'
		);

		if(!empty($data) && !empty($data["Lpu_id"])){
			//если задан лпу ид то вытягиваем только нужную мо - чтобы узнать есть ли настройка на конкретной мо
			$params["Lpu_id"] = $data["Lpu_id"];
			$where[] = "lpu.Lpu_id = :Lpu_id";
		}

		$sql = "
			SELECT
				lpu.Lpu_id,
				lpu.lpu_Nick as Lpu_Nick
			FROM dbo.v_Lpu lpu with(nolock)
			LEFT JOIN dbo.v_DataStorage ds with(nolock) on ds.Lpu_id = lpu.Lpu_id
				".ImplodeWherePH( $where )."
		";

		//var_dump($this->db->query($sql)->result_array()); exit;
		//return $this->db->query($sql)->result_array();
		$result = $this->db->query($sql, $params);

		if (is_object($result)) {
			return $result->result_array();
			//return $result;
		} else {
			return false;
		}
	}

	/**
		*  Функция используется для доп.аутентификации пользователя при socket-соединении NodeJS для армов СМП
		*  Входящие данные: session
		*  На выходе: JSON-строка
		*  Также получаем building_id Опер отдела для создания room (уфа и крым)
	 */
	public function getPmUserInfo($data){
		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
		$OperDepartament = $this->CmpCallCard_model4E->getOperDepartament($data);
		return array(
			array(
				'pmuser_id'=>$data['pmUser_id'],
				'Lpu_id'=>$data['Lpu_id'],
				'CurMedService_id'=>isset( $data[ 'session' ][ 'CurMedService_id' ] ) ? $data[ 'session' ][ 'CurMedService_id' ] : null,
				'OperDepartament'=>isset( $OperDepartament["LpuBuilding_pid"] ) ? $OperDepartament["LpuBuilding_pid"] : null
			)
		);
	}


	/**
	 * Поиск дублей по населенному пункту, улице, дому, квартире (за последние 24 часа)
	 */
	function checkDuplicateCmpCallCardByAddress($data){

		$filter = '(1 = 1)';
		$queryParams = array();

		if ( !empty($data['CmpCallCard_prmDate']) ) {
			$queryParams['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDate'];
			if ( !empty($data['CmpCallCard_prmTime']) ) {
                $queryParams['CmpCallCard_prmDT'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
                $filter .= " and cast(CCC.CmpCallCard_prmDT as datetime) >= dateadd(hour,-24,:CmpCallCard_prmDT)";
			}
		}

		if (!empty($data['CmpCallCard_id'])) {
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
			$filter .= " and CCC.CmpCallCard_id != :CmpCallCard_id";
		}


		if( !empty($data['UnformalizedAddressDirectory_id']) && (int)$data['UnformalizedAddressDirectory_id']){
			// если искомый адрес - объект, то будем искать по его ID
			$arrFields = array(
				'UnformalizedAddressDirectory_id'
			);
		}else{
			$arrFields = array(
				'KLSubRgn_id',
				'KLCity_id',
				'KLTown_id',
				'KLStreet_id',
				'CmpCallCard_UlicSecond',
				'CmpCallCard_Dom',
				'CmpCallCard_Korp',
				'CmpCallCard_Kvar',
			);
		}
		foreach($arrFields as $field)
		{
			if ( !empty($data[$field]) )
			{
				$filter .= " and CCC.$field = :$field ";
				$queryParams[$field] = $data[$field];
			}
			else
			{
				$filter .= " and CCC.$field IS NULL";
			}
		}

		$filter .=" and CTYP.CmpCallCardStatusType_Code not in (4,5,6,9)";

		$filter .=" and CCT.CmpCallType_Code in (1, 2, 4, 9)";

		$filter .=" and (C112.CmpCallCard112StatusType_id is null or C112.CmpCallCard112StatusType_id in (3,4,5))";

        $query = "
            SELECT
                --begin new select

                PS.Person_id
                ,PS.PersonEvn_id
                ,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
                ,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
                ,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
                ,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
                ,RGN.KLRgn_id
                ,SRGN.KLSubRgn_id
                ,City.KLCity_id
                ,COALESCE(City.KLSocr_Nick + ' ' + City.KLCity_Name, '') as KLCity_Name
                ,Town.KLTown_id
                ,COALESCE(Town.KLSocr_Nick + ' ' + Town.KLTown_Name, '') as KLTown_Name
                ,Street.KLStreet_id
                ,Street.KLStreet_FullName
                ,CCC.CmpCallCard_Dom
                ,CCC.CmpCallCard_Korp
                ,CCC.CmpCallCard_Kvar
                ,CCC.CmpCallCard_Comm
                ,CCC.CmpCallCard_Podz
                ,CCC.CmpCallCard_Etaj
                ,CCC.CmpCallCard_Kodp
                ,CCC.CmpCallCard_Telf
                ,CCC.CmpCallCard_IsExtra
                ,CCC.CmpCallCard_IsPoli
                ,CCC.Person_Age
                ,CCC.Sex_id
                ,CCC.CmpCallerType_id
                ,CCC.CmpCallPlaceType_id
                ,CCC.CmpCallCard_Ktov
                ,CCC.CmpCallCard_IsDeterior
                ,CCC.MedService_id
                ,UAD.UnformalizedAddressDirectory_id
                ,UAD.UnformalizedAddressType_id
                ,UAD.UnformalizedAddressDirectory_Dom
                ,UAD.UnformalizedAddressDirectory_Name

                ,case when isnull(CCC.KLStreet_id,0) = 0 then
                    case when isnull(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL
                    else 'UA.'+CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
                    else 'ST.'+CAST(CCC.KLStreet_id as varchar(20)) end as StreetAndUnformalizedAddressDirectory_id

                ,CCC.CmpLpu_id as lpuLocalCombo
                ,CCC.LpuBuilding_id
                -- end new select

                ,CCC.CmpCallCard_id as CallCard_id
                ,convert(varchar(20), cast(CCCST_T.CmpCallCardStatus_insDT as datetime), 113) as CmpCallCardStatus_insDT
                ,convert(varchar(20), cast(CCC.CmpCallCard_Tper as datetime), 113) as CmpCallCard_Tper
                ,CCC.EmergencyTeam_id as EmergencyTeam_id
                ,COALESCE(CCC.Person_SurName, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
                ,CCC.CmpCallCard_Ngod as CmpCallCard_Ngod
                ,CCC.CmpCallCard_Numv as CmpCallCard_Numv
                ,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
                ,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
                ,CR.CmpReason_id
                ,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name

                ,STUFF(
                    CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' + SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' + City.KLCity_Name, '') END
                    + COALESCE(', ' + Town.KLTown_FullName, '')
                    + COALESCE(', ' + Street.KLStreet_FullName, '')
                    + COALESCE(', д.' + CCC.CmpCallCard_Dom, '')
                    + COALESCE(', к.' + CCC.CmpCallCard_Korp, '')
                    + COALESCE(', кв.' + CCC.CmpCallCard_Kvar, '')
                    + COALESCE(', комн.' + CCC.CmpCallCard_Comm, '')
                    + COALESCE(', место: ' + UAD.UnformalizedAddressDirectory_Name, ''),
                        -- параметры STUFF
                         1, 2, ''
                    ) as Adress_Name

            from
                -- from
                v_CmpCallCard CCC with (nolock)
                left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
                left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
                left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
                left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
                left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
                left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
                left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
                left join v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
                left join (
                      SELECT
                                    MIN(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
                                    CCCS.CmpCallCard_id as CmpCallCard_id
                      from 			v_CmpCallCardStatus CCCS with(nolock)
                      inner join 	v_CmpCallCardStatusType CCCST (nolock) on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
                      left join		v_CmpCallCard VCCC (nolock) on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id
                      where 		CCCST.CmpCallCardStatusType_Code = 2
                                    and VCCC.Lpu_ppdid IS NOT NULL
                                    --and VCCC.CmpCallCard_IsReceivedInPPD = 1

                      group by 		CCCS.CmpCallCard_id
                            )
                            CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
                left join dbo.v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
                left join dbo.v_CmpCallCardLockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
                left join v_CmpCallCardStatusType CTYP (nolock) on CCC.CmpCallCardStatusType_id = CTYP.CmpCallCardStatusType_id
                left join v_CmpCallCard112 C112 (nolock) on CCC.CmpCallCard_id = C112.CmpCallCard_id

            where
            ".$filter;


		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			$val = $result->result('array');
			return array(
				'data' => $val
			);
		} else {
			return false;
		}

	}


	/**
	 * Поиск дублей по id пользователя (за последние 24 часа)
	 */
	function checkDuplicateCmpCallCardByFIO($data){

		$filter = '(1 = 1)';
		$queryParams = array();
		if ( !empty($data['CmpCallCard_prmDate']) ) {
			$queryParams['CmpCallCard_prmDT'] = $data['CmpCallCard_prmDate'];
			if ( !empty($data['CmpCallCard_prmTime']) ) {
                $queryParams['CmpCallCard_prmDT'] .= ' ' . $data['CmpCallCard_prmTime'] . ':00.000';
                $filter .= " and cast(CCC.CmpCallCard_prmDT as datetime) >= dateadd(day,-1,:CmpCallCard_prmDT)";
			}
		}

		if (!empty($data['CmpCallCard_id'])) {
			$queryParams['CmpCallCard_id'] = $data['CmpCallCard_id'];
			$filter .= " and CCC.CmpCallCard_id != :CmpCallCard_id";
		}


        //$filter .=" and CTYP.CmpCallCardStatusType_Code in (4,6)";
        $filter .=" and CTYP.CmpCallCardStatusType_Code not in (4,5,6,9)";
        $filter .=" and CCT.CmpCallType_Code in (1, 2, 4, 9)";

        $filter .=" and (C112.CmpCallCard112StatusType_id is null or C112.CmpCallCard112StatusType_id in (3,4,5))";


		if ( !empty($data['Person_id']) ) {
			$filter .=" and CCC.Person_id = :Person_id";
			$queryParams['Person_id'] = $data['Person_id'];
		}
		else
			return false;

			$query = "
			    SELECT
                    --begin new select

                    PS.Person_id
                    ,PS.PersonEvn_id
                    ,ISNULL(PS.Person_Surname, CCC.Person_SurName) as Person_Surname
                    ,ISNULL(PS.Person_Firname, CCC.Person_FirName) as Person_Firname
                    ,ISNULL(PS.Person_Secname, CCC.Person_SecName) as Person_Secname
                    ,convert(varchar(10), ISNULL(PS.Person_BirthDay, CCC.Person_BirthDay), 104) as Person_Birthday
                    ,RGN.KLRgn_id
                    ,SRGN.KLSubRgn_id
                    ,City.KLCity_id
                    ,COALESCE(City.KLSocr_Nick + ' ' + City.KLCity_Name, '') as KLCity_Name
                    ,Town.KLTown_id
                    ,COALESCE(Town.KLSocr_Nick + ' ' + Town.KLTown_Name, '') as KLTown_Name
                    ,Street.KLStreet_id
                    ,Street.KLStreet_FullName
                    ,CCC.CmpCallCard_Dom
                    ,CCC.CmpCallCard_Korp
                    ,CCC.CmpCallCard_Kvar
                    ,CCC.CmpCallCard_Comm
                    ,CCC.CmpCallCard_Podz
                    ,CCC.CmpCallCard_Etaj
                    ,CCC.CmpCallCard_Kodp
                    ,CCC.CmpCallCard_Telf
                    ,CCC.CmpCallCard_IsExtra
                    ,CCC.CmpCallCard_IsPoli
                    ,CCC.MedService_id
                    ,CCC.Person_Age
                    ,CCC.Sex_id
                    ,CCC.CmpCallerType_id
                    ,CCC.CmpCallPlaceType_id
                    ,CCC.CmpCallCard_Ktov
                    ,UAD.UnformalizedAddressDirectory_id
                    ,UAD.UnformalizedAddressType_id
                    ,UAD.UnformalizedAddressDirectory_Dom
                    ,UAD.UnformalizedAddressDirectory_Name

                    ,case when isnull(CCC.KLStreet_id,0) = 0 then
                        case when isnull(CCC.UnformalizedAddressDirectory_id,0) = 0 then NULL
                        else 'UA.'+CAST(CCC.UnformalizedAddressDirectory_id as varchar(20)) end
                        else 'ST.'+CAST(CCC.KLStreet_id as varchar(20)) end as StreetAndUnformalizedAddressDirectory_id

                    ,CCC.CmpLpu_id as lpuLocalCombo
                    ,CCC.LpuBuilding_id
                    -- end new select

                    ,CCC.CmpCallCard_id as CallCard_id
                    ,convert(varchar(20), cast(CCCST_T.CmpCallCardStatus_insDT as datetime), 113) as CmpCallCardStatus_insDT
                    ,convert(varchar(20), cast(CCC.CmpCallCard_Tper as datetime), 113) as CmpCallCard_Tper
                    ,CCC.EmergencyTeam_id as EmergencyTeam_id
                    ,COALESCE(CCC.Person_SurName, '') + ' ' + COALESCE(case when rtrim(CCC.Person_FirName) = 'null' then '' else CCC.Person_FirName end, '') + ' ' + COALESCE(case when rtrim(CCC.Person_SecName) = 'null' then '' else CCC.Person_SecName end, '') as Person_FIO
                    ,CCC.CmpCallCard_Ngod as CmpCallCard_Ngod
                    ,CCC.CmpCallCard_Numv as CmpCallCard_Numv
                    ,convert(varchar(20), cast(CCC.CmpCallCard_prmDT as datetime), 113) as CmpCallCard_prmDate
                    ,RTRIM(case when CR.CmpReason_id is not null then CR.CmpReason_Code+'. ' else '' end + ISNULL(CR.CmpReason_Name, '')) as CmpReason_Name
                    ,CR.CmpReason_id
                    ,RTRIM(case when CCT.CmpCallType_id is not null then CCT.CmpCallType_Code+'. ' else '' end + ISNULL(CCT.CmpCallType_Name, '')) as CmpCallType_Name

                    ,STUFF(
                        CASE WHEN SRGN.KLSubRgn_FullName IS NOT NULL THEN ', ' + SRGN.KLSubRgn_FullName ELSE COALESCE(', г.' + City.KLCity_Name, '') END
                        + COALESCE(', ' + Town.KLTown_FullName, '')
                        + COALESCE(', ' + Street.KLStreet_FullName, '')
                        + COALESCE(', д.' + CCC.CmpCallCard_Dom, '')
                        + COALESCE(', к.' + CCC.CmpCallCard_Korp, '')
                        + COALESCE(', кв.' + CCC.CmpCallCard_Kvar, '')
                        + COALESCE(', комн.' + CCC.CmpCallCard_Comm, '')
                        + COALESCE(', место: ' + UAD.UnformalizedAddressDirectory_Name, ''),
                            -- параметры STUFF
                             1, 2, ''
                        ) as Adress_Name
                from
                    -- from
                    v_CmpCallCard CCC with (nolock)
                    left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
                    left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id
                    left join v_KLRgn RGN (nolock) on RGN.KLRgn_id = CCC.KLRgn_id
                    left join v_KLSubRgn SRGN (nolock) on SRGN.KLSubRgn_id = CCC.KLSubRgn_id
                    left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
                    left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
                    left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
                    left join (
                          SELECT
                                        MIN(CCCS.CmpCallCardStatus_insDT) as CmpCallCardStatus_insDT,
                                        CCCS.CmpCallCard_id as CmpCallCard_id
                          from 			v_CmpCallCardStatus CCCS with(nolock)
                          inner join 	v_CmpCallCardStatusType CCCST (nolock) on CCCST.CmpCallCardStatusType_id = CCCS.CmpCallCardStatusType_id
                          left join		v_CmpCallCard VCCC (nolock) on CCCS.CmpCallCard_id = VCCC.CmpCallCard_id
                          where 		CCCST.CmpCallCardStatusType_Code = 2
                                        and VCCC.Lpu_ppdid IS NOT NULL
                                        --and VCCC.CmpCallCard_IsReceivedInPPD = 1

                          group by 		CCCS.CmpCallCard_id
                                )
                                CCCST_T on CCCST_T.CmpCallCard_id = CCC.CmpCallCard_id
                    left join dbo.v_PersonState PS with (nolock) on PS.Person_id = CCC.Person_id
                    left join dbo.v_CmpCallCardLockList CCCLL (nolock) on CCCLL.CmpCallCard_id = CCC.CmpCallCard_id
                    left join dbo.v_UnformalizedAddressDirectory UAD (nolock) on UAD.UnformalizedAddressDirectory_id = CCC.UnformalizedAddressDirectory_id
                    left join v_CmpCallCardStatusType CTYP (nolock) on CCC.CmpCallCardStatusType_id = CTYP.CmpCallCardStatusType_id
                    left join v_CmpCallCard112 C112 (nolock) on CCC.CmpCallCard_id = C112.CmpCallCard_id
                where
                ".$filter;


		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$val = $result->result('array');
			return array(
				'data' => $val
			);
		} else {
			return false;
		}

	}
	/**
	 * Сохранение актива СМП (создание вызова на дом из АРМ-а администратора СМП)
	 */
	function addHomeVisitFromSMP($data){
		$Error_Msg = 'Редактирование активного посещения невозможно, т.к. параметры посещения были изменены в МО передачи актива';
		//сохранение актива СМП
		$query = "select
						hv.HomeVisit_id,
						hv.HomeVisitStatus_id,
						hv.Address_Flat,
						hv.Address_House,
						convert(varchar, hv.HomeVisit_setDT, 104) + ' ' + convert(varchar(5), hv.HomeVisit_setDT, 108) as HomeVisit_setDT,
						hv.KLCity_id,
						hv.KLRgn_id,
						hv.KLStreet_id,
						hv.Lpu_id,
						hv.Address_Address,
						CLC.CmpCloseCard_id
					from
						v_HomeVisit hv with (nolock)
						left join {$this->schema}.v_CmpCloseCard CLC (nolock) on hv.CmpCallCard_id = CLC.CmpCallCard_id
					where
						hv.CmpCallCard_id = :CmpCallCard_id";

		$queryParams = array('CmpCallCard_id' => $data['CmpCallCard_id']);
		$result = $this->db->query($query, $queryParams);
		$ret_arr = $result->result('array');
		//if ( is_object($result) && count($ret_arr) > 0) {
		//	return array(array('success'=>false, 'Error_Code'=>'','Error_Msg'=>(string)$Error_Msg));
		//}

		$this->load->model('HomeVisit_model', 'HomeVisit_model');

		$q = "
			SELECT TOP 1
				CCC.KLCity_id
				,CCC.KLTown_id
				,CCC.KLTown_id
				,CCC.KLStreet_id
				,CCC.KLRgn_id
				,CCC.Person_id
				,CCC.CmpCallCard_Telf as HomeVisit_Phone
				,CCC.CmpCallCard_Dom as Address_House
				,CCC.CmpCallCard_Kvar as Address_Flat
				,CCC.Lpu_ppdid as Lpu_id
				,CCC.MedService_id
				,CCC.pmUser_insID as pmUser_id
				,HV.HomeVisit_id
				,case when CCT.CmpCallType_Code = 3 then 2 else 1 end as HomeVisitCallType_id
				,CCC.Person_Age
				,case when CCC.Person_Age >= 18 then 1 else 2 end as HomeVisitWhoCall_id
				,CCC.CmpCallCard_Comm as HomeVisit_Comment
				,CCC.CmpCallCard_prmDT
				,case when City.KLCity_Name is not null then 'г. '+City.KLCity_Name else '' end +
				case when Town.KLTown_FullName is not null then
					case when (City.KLCity_Name is not null) then ', '+LOWER(Town.KLSocr_Nick)+'. '+Town.KLTown_Name else LOWER(Town.KLSocr_Nick)+'. '+Town.KLTown_Name end
				else '' end +
				case when Street.KLStreet_FullName is not null then ', '+LOWER(socrStreet.KLSocr_Nick)+'. '+Street.KLStreet_Name else '' end +
				case when CCC.CmpCallCard_Dom is not null then ', д.'+CCC.CmpCallCard_Dom else '' end +
				case when CCC.CmpCallCard_Korp is not null then ', к.'+CCC.CmpCallCard_Korp else '' end +
				case when CCC.CmpCallCard_Kvar is not null then ', кв.'+CCC.CmpCallCard_Kvar else '' end as Address_Address

				,COALESCE(CR.CmpReason_Code + '. ', '') + CR.CmpReason_Name as HomeVisit_Symptoms
				,HV.KLStreet_id as HVKLStreet_id
			FROM v_CmpCallCard CCC (nolock)

			left join v_KLCity City (nolock) on City.KLCity_id = CCC.KLCity_id
			left join v_KLTown Town (nolock) on Town.KLTown_id = CCC.KLTown_id
			left join v_KLStreet Street (nolock) on Street.KLStreet_id = CCC.KLStreet_id
			left join v_KLSocr socrStreet with (nolock) on Street.KLSocr_id = socrStreet.KLSocr_id
			left join v_CmpReason CR with (nolock) on CR.CmpReason_id = CCC.CmpReason_id
			left join v_HomeVisit HV with (nolock) on HV.CmpCallCard_id = CCC.CmpCallCard_id
			left join v_CmpCallType CCT with (nolock) on CCT.CmpCallType_id = CCC.CmpCallType_id

			WHERE CCC.CmpCallCard_id = " . $data['CmpCallCard_id'] . ";
		";

		$r = $this->db->query($q, $data);
		$arrayRes = $r->result('array');

		if(count($ret_arr) > 0 && $ret_arr[0]['HomeVisitStatus_id'] != 1){

			$comboParams = array(
				'ComboCheck_Patient_id' => 111, //ComboSys комбо-родителя => Код комбо актива в поликлинику
				'ComboValue_710' => $ret_arr[0]['Address_Flat'],
				'ComboValue_708' => $ret_arr[0]['Address_House'],
				'ComboValue_694' => $ret_arr[0]['HomeVisit_setDT'],
				'ComboValue_705' => $ret_arr[0]['KLCity_id'],
				'ComboValue_703' => $ret_arr[0]['KLRgn_id'],
				'ComboValue_707' => $ret_arr[0]['KLStreet_id'],
				'ComboValue_693' => $ret_arr[0]['Lpu_id'],
				'ComboValue_711' => $ret_arr[0]['Address_Address'],
				'ComboValue_695' => $ret_arr[0]['Address_Address'],
				'pmUser_id' => $data['pmUser_id']
			);

			$this->saveCmpCloseCardComboValues($comboParams, 'edit', array('CmpCloseCard_id' => $ret_arr[0]['CmpCloseCard_id']), array(), $ret_arr[0]['CmpCloseCard_id'], ';', "{$this->schema}.p_CmpCloseCardRel_ins");

			return array('success'=>true, 'Error_Msg'=>(string)$Error_Msg);
		}else{
			$paramsToActive = array();

			$paramsToActive['HomeVisit_id'] = (count($ret_arr) > 0 ) ?  $ret_arr[0]['HomeVisit_id'] : null;
			$paramsToActive['Address_Flat'] = (!empty( $data['ComboValue_710'] ) ? $data[ 'ComboValue_710' ] : null);
			$paramsToActive['Address_House'] = (!empty( $data['ComboValue_708'] ) ? $data[ 'ComboValue_708' ] : null);
			$paramsToActive['HomeVisitStatus_id'] = 1; //Новый
			//$paramsToActive['HomeVisitSource_id'] = (!empty( $data['HomeVisitSource_id'] ) ? $data[ 'HomeVisitSource_id' ] : 5); //ЕДЦ
			$paramsToActive['HomeVisitSource_id'] = 10; // #133286 «СМП» (HomeVisitSource_id = 10);
			$paramsToActive['HomeVisit_Phone'] = (!empty( $data['Phone'] ) ? $data[ 'Phone' ] : null);
			$paramsToActive['HomeVisit_setDT'] = !empty($data['ComboValue_694']) ? $data['ComboValue_694'] : null;
			$paramsToActive['KLCity_id'] = (!empty( $data['ComboValue_705'] ) ? $data[ 'ComboValue_705' ] : null);
			$paramsToActive['KLRgn_id'] = (!empty( $data['ComboValue_703'] ) ? $data[ 'ComboValue_703' ] : null);
			$paramsToActive['KLStreet_id'] = (!empty( $data['ComboValue_707'] ) ? $data[ 'ComboValue_707' ] : null);
			$paramsToActive['Lpu_id'] = !empty($data['ComboValue_693']) ? $data['ComboValue_693'] : null;
			//профиль Терапевтический/педиатрический #78690
			$paramsToActive['CallProfType_id'] = 1;
			$paramsToActive['pmUser_id'] = !empty($data['pmUser_id']) ? $data['pmUser_id'] : null;
			$paramsToActive['CmpCallCard_id'] = !empty( $data['CmpCallCard_id'] ) ? $data['CmpCallCard_id'] : null;
			$paramsToActive['Person_id'] = !empty($data['Person_id']) ? $data['Person_id'] : null;
			$paramsToActive['Address_Address'] =  !empty( $data['ComboValue_711'] ) ? $data['ComboValue_711'] : null;
			$paramsToActive['HomeVisit_Comment'] =  !empty( $data['HomeVisit_Comment'] ) ? $data['HomeVisit_Comment'] : null;
			$paramsToActive['Person_Age'] =  !empty( $data['Age'] ) ? $data['Age'] : null;

			//Соединим массивы, но удалим пустые параметры
			$mergedArray = !empty($arrayRes[0]) ? array_merge ( $arrayRes[0], array_diff($paramsToActive, array(''))   ) : $paramsToActive;

			//$mergedArray['HomeVisit_setDT'] = !empty($mergedArray['HomeVisit_setDT']) ? DateTime::createFromFormat('d.m.Y H:i', $mergedArray['HomeVisit_setDT']) : $this->HomeVisit_model->getHomeVisitNearestWorkDay(array('Lpu_id' => $mergedArray['Lpu_id']));
			$mergedArray['HomeVisit_setDT'] = !empty($mergedArray['HomeVisit_setDT']) ? DateTime::createFromFormat('d.m.Y H:i', $mergedArray['HomeVisit_setDT']) : $mergedArray['CmpCallCard_prmDT'];

			//Этот метод сохраняет так же активы из 110у
			if(empty($data['saveActive'])){
				$nearestDateToHomeVisit = $this->HomeVisit_model->getHomeVisitNearestWorkDay(
					array('Lpu_id' => $mergedArray['Lpu_id']),
					$mergedArray['HomeVisit_setDT']
				);

				if(isset($nearestDateToHomeVisit['DateInPeriod'])){
					if(!$nearestDateToHomeVisit['DateInPeriod'])
						return array(array('Error_Msg' => $nearestDateToHomeVisit['NearestDate']->format('d.m.Y H:i'), 'success' => false));
				}else{
					return array(array('Error_Msg' => 'Не удалось определить ближайшую дату записи', 'success' => false));
				}
			}

			if($mergedArray['Person_Age'] >= 18){
				$LpuRegionTypeFilter = "and LRT.LpuRegionType_SysNick in ('ter', 'vop')";
			}else{
				$LpuRegionTypeFilter = "and LRT.LpuRegionType_SysNick in ('ped', 'vop')";
			}

			//поиск участка в мо передачи
			$sql = "
					select
						LR.LpuRegion_id
						,MSR.MedStaffFact_id
					from
						v_LpuRegionStreet LRS with(nolock)
						left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = LRS.LpuRegion_id
						outer apply(
							select top 1
								MedStaffFact_id
							from v_MedStaffRegion msf (nolock)
							where msf.LpuRegion_id = LR.LpuRegion_id
								and msf.MedStaffRegion_begDate <= dbo.tzGetDate()
								and (msf.MedStaffRegion_endDate is null or msf.MedStaffRegion_endDate >= dbo.tzGetDate())
							order by msf.MedStaffRegion_isMain desc
						) MSR
						left join v_LpuRegionType LRT (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
					where
						LRS.KLCountry_id = 643 -- Россия
						and isnull(LRS.KLCity_id, '') = isnull(:KLCity_id, '')
						and LRS.KLStreet_id = :KLStreet_id
						and LR.Lpu_id = :Lpu_id
						and (dbo.GetHouse(LRS.LpuRegionStreet_HouseSet, :Address_House) = 1)
						{$LpuRegionTypeFilter}
				";

			$r = $this->db->query($sql, $mergedArray);
			$res = $r->result('array');

			//нашли участок в мо передачи
			if (is_array($res) && count($res) > 0) {

				if (!empty($res[0]['LpuRegion_id'])) {
					$mergedArray['LpuRegion_cid'] = $res[0]['LpuRegion_id'];
				}
				/*
				$MedService_id = !empty($data['MedService_id']) ? $data['MedService_id'] : (!empty($arrayRes[0]) ? $arrayRes[0]['MedService_id'] : null);

				//если служба нмп у нас не определена - определяем
				if(empty($MedService_id)){
					$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
					$serviceNmp = $this->CmpCallCard_model4E->getNmpMedService($data);
					if(!empty($serviceNmp[0]) && !empty($serviceNmp[0]["MedService_id"])){
						$MedService_id = $serviceNmp[0]["MedService_id"];
					}
				}

				if(!empty($MedService_id)){

					$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
					$lpuBuilding = $this->CmpCallCard_model4E->getLpuBuildingByMedServiceId(array('MedService_id' => $MedService_id));

					if(!empty($lpuBuilding[0]['LpuBuilding_id'])){
						$this->load->model('LpuStructure_model', 'LpuStructure');
						$LpuBuildingData = $this->LpuStructure->getSmpUnitData(array("LpuBuilding_id"=>$lpuBuilding[0]['LpuBuilding_id']));
					}

					if (!empty($res[0]['MedStaffFact_id']) && !empty($LpuBuildingData[0]['SmpUnitParam_IsPrescrHome']) && $LpuBuildingData[0]['SmpUnitParam_IsPrescrHome'] == "true") {
						$MedStaffFact_id = $res[0]['MedStaffFact_id'];
						//$mergedArray['HomeVisitStatus_id'] = 6; //Назначен врач
					}
				}
				*/
			}

			$r = $this->HomeVisit_model->addHomeVisit($mergedArray, true);

			if($this->regionNick != 'ufa' && !empty($res[0]['MedStaffFact_id']) && !empty($r[0]['HomeVisit_id'])){
				//назначение врача
				$this->HomeVisit_model->takeMP(array(
					'HomeVisit_id' => $r[0]['HomeVisit_id'],
					//'MedStaffFact_id' => $MedStaffFact_id,
					'MedStaffFact_id' => $res[0]['MedStaffFact_id'],
					'MedPersonal_id' => isset($_SESSION['medpersonal_id']) ? $_SESSION['medpersonal_id'] : null,
					'pmUser_id' => $data['pmUser_id']
				));
			}


			if ( is_array($r) && isset($r['success']) && $r['success'] == true ) {
				return array($r);
			} else {
				return false;
			}
		}

	}

    /**
     *	Получение списка параметров хранимой процедуры
     */
    function getStoredProcedureParamsList($sp, $schema) {
        $query = "
			select
				ps.[name]
			from
				sys.all_parameters ps with(nolock)
				left join sys.types t with(nolock) on t.system_type_id = ps.system_type_id and t.user_type_id = ps.user_type_id
			where
				ps.[object_id] = (
					select
						top 1 [object_id]
					from
						sys.objects with(nolock)
					where
						[type_desc] = 'SQL_STORED_PROCEDURE' and
						[name] = :name and
						(
							:schema is null or
							[schema_id] = (select top 1 [schema_id] from sys.schemas with(nolock) where [name] = :schema)
						)
				) and
				ps.[name] not in ('@pmUser_id', '@Error_Code', '@Error_Message', '@isReloadCount') and
				t.[is_user_defined] = 0;
		";

        $queryParams = array(
            'name' => $sp,
            'schema' => $schema
        );

        $result = $this->db->query($query, $queryParams);

        if ( !is_object($result) ) {
            return false;
        }

        $outputData = array();
        $response = $result->result('array');

        foreach ( $response as $row ) {
            $outputData[] = str_replace('@', '', $row['name']);
        }

        return $outputData;
    }

    /**
     * Сохранение произвольного обьекта (без повреждения предыдущих данных).
     */
    function saveObject($object_name, $data) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

        $key_field = !empty($data['key_field']) ? $data['key_field'] : "{$object_name}_id";

        if (!isset($data[$key_field])) {
            $data[$key_field] = null;
        }

        $action = $data[$key_field] > 0 ? "upd" : "ins";
        $proc_name = "p_{$object_name}_{$action}";
        $params_list = $this->getStoredProcedureParamsList($proc_name, $schema);
		foreach($params_list as $key => $param){
			$params_list[$key] = mb_strtolower($param);
		}
        $save_data = array();
        $query_part = "";

        //получаем существующие данные если апдейт
        if ($action == "upd") {
            $query = "
				select
					*
				from
					{$schema}.{$object_name} with (nolock)
				where
					{$key_field} = :id;
			";
            $result = $this->getFirstRowFromQuery($query, array(
                'id' => $data[$key_field]
            ));
            if (is_array($result)) {
                foreach($result as $key => $value) {
                    if (in_array($key, $params_list)) {
                        $save_data[$key] = $value;
                    }
                }
            }
			else
				$proc_name = "p_{$object_name}_ins";
        }
		$returnDocumentUc = "";
		$declareDocumentUc = "";
		$executeDocumentUc = ";";
		if($proc_name == 'p_EvnDrug_ins') {
			$returnDocumentUc = ", @DocumentUcStr_cid as DocumentUcStr_cid, @DocumentUc_cid as DocumentUc_cid";

			if(isset($data['DocumentUcStr_cid']) && $data['DocumentUcStr_cid'] > 0) {
				$declareDocumentUc .= "@DocumentUcStr_cid bigint = '" . $data['DocumentUcStr_cid'] . "',";
				unset($data['DocumentUcStr_cid']);
			}
			else
				$declareDocumentUc .=  "@DocumentUcStr_cid bigint = NULL, ";

			if(isset($data['DocumentUc_cid']) && $data['DocumentUc_cid'] > 0) {
				$declareDocumentUc .= "@DocumentUc_cid bigint = '" . $data['DocumentUc_cid'] . "',";
				unset($data['DocumentUc_cid']);
			}
			else
				$declareDocumentUc .=  "@DocumentUc_cid bigint = NULL, ";

			foreach($data as $key => $value) {
				if (!is_object($value) && in_array(mb_strtolower($value), array("documentuc_cid","documentucstr_cid"))) {
					unset($data[$key]);
				}
			}
			$executeDocumentUc = ", @DocumentUcStr_cid = @DocumentUcStr_cid output,
			@DocumentUc_cid = @DocumentUc_cid output;";

		}

        foreach($data as $key => $value) {
            if (in_array(mb_strtolower($key), $params_list)) {
                $save_data[$key] = $value;
            }
        }

        foreach($save_data as $key => $value) {
            if (in_array(mb_strtolower($key), $params_list) && $key != $key_field) {
                //перобразуем даты в строки
                if (is_object($save_data[$key]) && get_class($save_data[$key]) == 'DateTime') {
                    $save_data[$key] = $save_data[$key]->format('Y-m-d H:i:s');
                }
                $query_part .= "@{$key} = :{$key}, ";
            }
        }

        $save_data['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;

        $query = "
			declare
				@{$key_field} bigint = :{$key_field},
				".$declareDocumentUc."
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.{$proc_name}
				@{$key_field} = @{$key_field} output,
				{$query_part}
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
				".$executeDocumentUc."
				

			select @{$key_field} as {$key_field} ".$returnDocumentUc." , @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

        if (isset($data['debug_query'])) {
            print getDebugSQL($query, $save_data);
        }
        $result = $this->getFirstRowFromQuery($query, $save_data);
        if ($result && is_array($result)) {
            if($result[$key_field] > 0) {
                $result['success'] = true;
            }
            return $result;
        } else {
            return array('Error_Msg' => 'При сохранении произошла ошибка');
        }
    }

    /**
     * Удаление произвольного обьекта.
     */
    function deleteObject($object_name, $data) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

        $query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute {$schema}.p_{$object_name}_del
				@{$object_name}_id = :{$object_name}_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

        $result = $this->getFirstRowFromQuery($query, $data);
        if ($result && is_array($result)) {
            if(empty($result['Error_Msg'])) {
                $result['success'] = true;
            }
            return $result;
        } else {
            return array('Error_Msg' => 'При удалении произошла ошибка');
        }
    }

    /**
     * Получение идентификатора типа документа по коду
     */
    function getObjectIdByCode($object_name, $code) {
        $schema = "dbo";

        //при необходимости выделяем схему из имени обьекта
        $name_arr = explode('.', $object_name);
        if (count($name_arr) > 1) {
            $schema = $name_arr[0];
            $object_name = $name_arr[1];
        }

        $query = "
			select top 1
				{$object_name}_id
			from
				{$schema}.{$object_name} with (nolock)
			where
				{$object_name}_Code = :code;
		";
        $result = $this->getFirstResultFromQuery($query, array(
            'code' => $code
        ));

        return $result && $result > 0 ? $result : false;
    }

    /**
     * Получение следующего номера произвольного обьекта.
     */
    function getObjectNextNum($object_name, $num_field) {
        $query = "
			select
				isnull(max(cast({$num_field} as int)), 0)+1 as num
			from
				{$object_name} (nolock)
			where
				len({$num_field}) <= 6 and
				IsNull((
					Select Case When CharIndex('.', {$num_field}) > 0 Then 0 Else 1 End
					Where IsNumeric({$num_field} + 'e0') = 1
				), 0) = 1
		";
        $num = $this->getFirstResultFromQuery($query);
        return !empty($num) && $num > 0 ? $num : 0;
    }

    /**
     * Поиск подходящего документа по заданнам параметрам. Если документ не найден - создается новый.
     */
    function getDocSMPForCmpCallCardDrug($data) {
        $id = null;
        $type_id = $this->getObjectIdByCode('DrugDocumentType', (!empty($data['DrugDocumentType_Code'])?$data['DrugDocumentType_Code']:25)); //25 - Списание медикаментов со склада на пациента. СМП
        if(empty($type_id)){
        	return null;
        }
        $query = "
            select
                du.DocumentUc_id
            from
                v_DocumentUc du with (nolock)
            where
                du.DrugDocumentType_id = :DrugDocumentType_id and
                du.DrugDocumentStatus_id = :DrugDocumentStatus_id and
                du.Contragent_sid = :Contragent_sid and
                du.Storage_sid = :Storage_sid and
                isnull(du.StorageZone_sid, 0) = isnull(:StorageZone_sid, 0) and
                isnull(du.DrugFinance_id, 0) = isnull(:DrugFinance_id, 0) and
                isnull(du.WhsDocumentCostItemType_id, 0) = isnull(:WhsDocumentCostItemType_id, 0) and
                du.DocumentUc_setDate = :DocumentUc_setDate
        ";
        $idParams = array(
            'DrugDocumentType_id' => $type_id,
            'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), //1 - Новый
            'Contragent_sid' => $data['Contragent_id'],
            'Storage_sid' => $data['Storage_id'],
            'StorageZone_sid' => (!empty($data['StorageZone_id'])?$data['StorageZone_id']:null),
            'DrugFinance_id' => $data['DrugFinance_id'],
            'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
            'DocumentUc_setDate' => date('Y-m-d')
        );
        if($type_id == 26 && !empty($data['StorageZoneLiable'])){
       		$idParams['DrugDocumentStatus_id'] = $this->getObjectIdByCode('DrugDocumentStatus', 4);
        }
        $id = $this->getFirstResultFromQuery($query, $idParams);

        if (empty($id)) {
        	$docParams = array(
                'DrugDocumentType_id' => $type_id,
                'DrugDocumentStatus_id' => $this->getObjectIdByCode('DrugDocumentStatus', 1), //1 - Новый
                'DocumentUc_Num' => $this->getObjectNextNum('DocumentUc', 'DocumentUc_Num'),
                'DocumentUc_setDate' => date('Y-m-d'),
                'DocumentUc_didDate' => date('Y-m-d'),
                'Lpu_id' => $data['Lpu_id'],
                'Contragent_id' => $data['Contragent_id'],
                'Contragent_sid' => $data['Contragent_id'],
                'Org_id' => $data['Org_id'],
                'Storage_sid' => $data['Storage_id'],
                'StorageZone_sid' => (!empty($data['StorageZone_id'])?$data['StorageZone_id']:null),
                'DrugFinance_id' => $data['DrugFinance_id'],
                'WhsDocumentCostItemType_id' => $data['WhsDocumentCostItemType_id'],
                'pmUser_id' => $data['pmUser_id']
            );
            if($type_id == 26){
            	$docParams['EmergencyTeam_id'] = (!empty($data['EmergencyTeam_id'])?$data['EmergencyTeam_id']:null);
            	if(!empty($data['StorageZoneLiable'])){
	        		$docParams['DrugDocumentStatus_id'] = $this->getObjectIdByCode('DrugDocumentStatus', 4); // Исполнен
	        	}
            }
            $response = $this->saveObject('DocumentUc', $docParams);
            if (is_array($response) && !empty($response['DocumentUc_id'])) {
                $id = $response['DocumentUc_id'];
            }
        }

        return $id;
    }

    /**
     * Удаление пустых докуиментов учета (только со статусом 'Новый')
     */
    function deleteEmptyDocumentUc($doc_id) {
        $result = array();
        $error = array();
        $doc_id_array = is_array($doc_id) ? $doc_id : array($doc_id);

        if (count($doc_id_array) > 0) {
            $query = "
                select
                    du.DocumentUc_id
                from
                    DocumentUc du with (nolock)
                    left join DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
                    left join DocumentUcStr dus with (nolock) on dus.DocumentUc_id = du.DocumentUc_id
                where
                    du.DocumentUc_id in (".join(',', $doc_id_array).") and
                    (
                    	dds.DrugDocumentStatus_Code = 1 -- 1 - Новый
                    	or (du.DrugDocumentType_id = 26 and dds.DrugDocumentStatus_Code = 4)
                    ) and 
                    dus.DocumentUcStr_id is null;
            ";

            $del_array = $this->queryResult($query);
            if (is_array($del_array)) {
                foreach($del_array as $del_data) {
                    $response = $this->deleteObject('DocumentUc', $del_data);
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                    }
                }
            }
        }

        if (count($error) > 0) {
            $result['success'] = false;
            $result['Error_Msg'] = $error[0];
        } else {
            $result['success'] = true;
        }

        return $result;
    }

    /**
     * Сохранение спецификации из JSON
     */
    function saveCmpCallCardDrugFromJSON($data) {
        $result = array();
        $error = array();
        $doc_array_check = array();
        $doc_array = array();

        $this->load->model("DocumentUc_model", "du_model");
        $this->load->model("StorageZone_model", "sz_model");

        if (!empty($data['json_str']) && $data['CmpCallCard_id'] > 0) {
            ConvertFromWin1251ToUTF8($data['json_str']);
            $dt = (array) json_decode($data['json_str']);

            foreach($dt as $record) {
                if (!empty($record->DocumentUc_id) && !in_array($record->DocumentUc_id, $doc_array_check)) { //собираем идентификаторы документов которые участвуют в изменении данных
                    $doc_array_check[] = $record->DocumentUc_id;
                    $doc_array[] = array('doc_id'=>$record->DocumentUc_id);
                }
                //проверка статуса документа учета, редактирвоать можно только документы со статусом Новый
                if (!empty($record->DocumentUc_id)) {
                    $query = "
                        select
                            dds.DrugDocumentStatus_Code
                        from
                            v_DocumentUc du with (nolock)
                            left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
                        where
                            du.DocumentUc_id = :DocumentUc_id;
                    ";
                    $status_code = $this->getFirstResultFromQuery($query, array(
                        'DocumentUc_id' => $record->DocumentUc_id
                    ));
                    if (empty($status_code)) {
                        $error[] = 'Не удалось получить статус документа списания';
                    }
                    if ($status_code != 1) {
                        $error[] = 'Редактирование запрещено';
                    }
                    if (count($error) > 0) {
	                    break;
	                }
                }

                $drugDocumentType_Code = 25; // По умолчанию тип документа Списание медикаментов со склада на пациента. СМП
                $szLiable = false; // по дефолту считаем что укладка не передана на подотчет
                if (!empty($record->StorageZone_id)){
                	$drugDocumentType_Code = 26;// тип документа Списание медикаментов из укладки на пациента
                }
                //проверка - передано ли место хранения и связано ли оно с бригадой
                if (!empty($record->StorageZone_id) && !empty($record->EmergencyTeam_id)) {
                    $query = "
                        select top 1
                            szl.StorageZoneLiable_id
                        from
                            v_StorageZoneLiable szl with (nolock)
                        where
                            szl.StorageZone_id = :StorageZone_id
                            -- and szl.StorageZoneLiable_ObjectId = :EmergencyTeam_id
                            and szl.StorageZoneLiable_ObjectId is not null
                            and szl.StorageZoneLiable_ObjectName = 'Бригада СМП'
                            and szl.StorageZoneLiable_endDate is null;
                    ";
                    $szl = $this->queryResult($query, array(
                        'StorageZone_id' => $record->StorageZone_id,
                        'EmergencyTeam_id' => $record->EmergencyTeam_id
                    ));
                    if (!is_array($szl)) {
                        $error[] = 'Не удалось проверить связь места хранения с бригадой';
                    }
                    if (count($error) > 0) {
	                    break;
	                }
                    if (count($szl) > 0) {
                        $szLiable = true;
                    }
                }

                //ищем подходящий документ учета
                $doc_id = $this->getDocSMPForCmpCallCardDrug(array(
                    'Lpu_id' => !empty($record->Lpu_id) ? $record->Lpu_id : null,
                    'Contragent_id' => !empty($record->Contragent_id) ? $record->Contragent_id : null,
                    'Org_id' => !empty($record->Org_id) ? $record->Org_id : null,
                    'Storage_id' => !empty($record->Storage_id) ? $record->Storage_id : null,
                    'StorageZone_id' => !empty($record->StorageZone_id) ? $record->StorageZone_id : null,
                    'EmergencyTeam_id' => !empty($record->EmergencyTeam_id) ? $record->EmergencyTeam_id : null,
                    'DrugFinance_id' => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
                    'WhsDocumentCostItemType_id' => !empty($record->WhsDocumentCostItemType_id) ? $record->WhsDocumentCostItemType_id : null,
                    'DrugDocumentType_Code' => $drugDocumentType_Code,
                    'StorageZoneLiable' => ($szLiable) ? 1 : null,
                    'pmUser_id' => $data['pmUser_id']
                ));

                if (empty($doc_id)) {
                    $error[] = 'Не удалось определить данные документа списания.';
                }
                $exist = false;
                foreach ($doc_array as $doc) {
                	if($doc['doc_id'] == $doc_id){
                		$exist = true;
                	}
                }
                if (!$exist) {
                    $doc_array[] = array('doc_id'=>$doc_id,'doc_type'=>$drugDocumentType_Code,'szLiable'=>$szLiable);
                }
                if (count($error) > 0) {
                    break;
                }

                $str_id = !empty($record->DocumentUcStr_id) ? $record->DocumentUcStr_id : null;

                // Если документ учета Списание медикаментов из укладки на пациента и укладка передана на подотчет
                // необходимо корректировать данные о медикаменте в связи документа передачи на подотчет и регистра остатков
                /*if($drugDocumentType_Code == 26 && $szLiable){
                	$correctParams = array(
                		'StorageZone_id' => !empty($record->StorageZone_id) ? $record->StorageZone_id : null,
                    	'EmergencyTeam_id' => !empty($record->EmergencyTeam_id) ? $record->EmergencyTeam_id : null,
                    	'DocumentUcStr_oid' => $record->DocumentUcStr_oid,
                    	'Drug_id' => $record->Drug_id,
                    	'DocumentUcStr_id' => $str_id,
                    	'DocumentUcStr_Count' => $record->CmpCallCardDrug_Kolvo,
                    	'pmUser_id' => $data['pmUser_id']
                	);
                	$response = $this->du_model->correctDrugOstatRegistryLink($correctParams);
                	if(!empty($response['Error_Msg'])){
                		$error[] = $response['Error_Msg'];
                	}
                	if (count($error) > 0) {
	                    break;
	                }
                }*/

                //сохранение или перезапись строки документа учета
                $docUcStrParams = array(
                    'DocumentUcStr_id' => $str_id,
                    'DocumentUcStr_oid' => !empty($record->DocumentUcStr_oid) ? $record->DocumentUcStr_oid : null,
                    'DocumentUc_id' => $doc_id,
                    'Drug_id' => $record->Drug_id,
                    'DrugFinance_id' => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
                    'DrugNds_id' => 1, //1 - Без НДС
                    'DocumentUcStr_Price' => !empty($record->CmpCallCardDrug_Cost) ? $record->CmpCallCardDrug_Cost : null,
                    'DocumentUcStr_PriceR' => !empty($record->CmpCallCardDrug_Cost) ? $record->CmpCallCardDrug_Cost : null,
                    'DocumentUcStr_EdCount' => !empty($record->CmpCallCardDrug_KolvoUnit) ? $record->CmpCallCardDrug_KolvoUnit : null,
                    'DocumentUcStr_Count' => !empty($record->CmpCallCardDrug_Kolvo) ? $record->CmpCallCardDrug_Kolvo : null,
                    'DocumentUcStr_Sum' => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
                    'DocumentUcStr_SumR' => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
                    'DocumentUcStr_SumNds' => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
                    'DocumentUcStr_SumNdsR' => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
                    //'DocumentUcStr_Ser' => null,
                    'PrepSeries_id' => !empty($record->PrepSeries_id) ? $record->PrepSeries_id : null,
                    'DocumentUcStr_IsNDS' => 1, //1 - Нет
                    'GoodsUnit_id' => $record->GoodsUnit_id,
                    'GoodsUnit_bid' => $record->GoodsUnit_bid,
                    'DocumentUcStr_Reason' => 'использование медикаментов на выезде бригады',
                    'pmUser_id' => $data['pmUser_id']
                );
				if ($drugDocumentType_Code == 26 && $szLiable) {
					$docUcStrParams['DrugDocumentStatus_id'] = $this->getObjectIdByCode('DrugDocumentStatus', 4); // Статус строки - испонена
				}
                $response = $this->saveObject('DocumentUcStr', $docUcStrParams);

                if (is_array($response)) {
                    if(!empty($response['DocumentUcStr_id'])) {
                        $str_id = $response['DocumentUcStr_id'];
                    }
                    if(!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                    }
                }

                if (empty($str_id)) {
                    $error[] = 'Не удалось определить данные строки документа списания.';
                }

                if (count($error) == 0) {
                    switch($record->state) {
                        case 'add':
                        case 'edit':
                            $response = $this->saveObject('CmpCallCardDrug', array(
                                'CmpCallCardDrug_id' => $record->state == 'add' ? 0 : $record->CmpCallCardDrug_id,
                                'CmpCallCard_id' => $data['CmpCallCard_id'],
                                'MedStaffFact_id' => !empty($record->MedStaffFact_id) ? $record->MedStaffFact_id : null,
                                'CmpCallCardDrug_setDate' => !empty($record->CmpCallCardDrug_setDate) ? $this->formatDate($record->CmpCallCardDrug_setDate) : null,
                                'CmpCallCardDrug_setTime' => !empty($record->CmpCallCardDrug_setTime) ? $record->CmpCallCardDrug_setTime : null,
                                'LpuBuilding_id' => !empty($record->LpuBuilding_id) ? $record->LpuBuilding_id : null,
                                'Storage_id' => !empty($record->Storage_id) ? $record->Storage_id : null,
                                'DrugFinance_id' => !empty($record->DrugFinance_id) ? $record->DrugFinance_id : null,
                                'WhsDocumentCostItemType_id' => !empty($record->WhsDocumentCostItemType_id) ? $record->WhsDocumentCostItemType_id : null,
                                'Mol_id' => !empty($record->Mol_id) ? $record->Mol_id : null,
                                'Drug_id' => $record->Drug_id,
                                'CmpCallCardDrug_Cost' => !empty($record->CmpCallCardDrug_Cost) ? $record->CmpCallCardDrug_Cost : null,
                                'CmpCallCardDrug_Kolvo' => !empty($record->CmpCallCardDrug_Kolvo) ? $record->CmpCallCardDrug_Kolvo : null,
                                'GoodsUnit_id' => !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null,
                                'CmpCallCardDrug_KolvoUnit' => !empty($record->CmpCallCardDrug_KolvoUnit) ? $record->CmpCallCardDrug_KolvoUnit : null,
                                'CmpCallCardDrug_Sum' => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
                                'DocumentUcStr_oid' => !empty($record->DocumentUcStr_oid) ? $record->DocumentUcStr_oid : null,
                                'DocumentUc_id' => $doc_id,
                                'DocumentUcStr_id' => $str_id,
                                'pmUser_id' => $data['pmUser_id']
                            ));
                            break;
                        case 'delete':
                            $response = $this->deleteCmpCallCardDrug(array(
                                'CmpCallCardDrug_id' => $record->CmpCallCardDrug_id,
                                'DocumentUcStr_id' => $record->DocumentUcStr_id,
                                'pmUser_id' => $data['pmUser_id']
                            ));
                            if (!empty($response['Error_Msg'])) {
                                $error[] = $response['Error_Msg'];
                            }
                            break;
                    }
                    if (!empty($response['Error_Msg'])) {
                        $error[] = $response['Error_Msg'];
                    }
                }

                if (count($error) > 0) {
                    break;
                }
            }

            //резервируем медикаменты либо снимаем резерв
            if (count($error) == 0) {
                foreach($doc_array as $document) {
                	if(!empty($document['doc_type']) && $document['doc_type'] == 26){
                		$response = $this->du_model->removeDrugsFromPack(array(
	                        'DocumentUc_id' => $document['doc_id'],
	                        'pmUser_id' => $data['pmUser_id']
	                    ));
	                    if (!empty($response['Error_Msg'])) {
	                        $error[] = $response['Error_Msg'];
	                        break;
	                    }
                	} else {
                		$response = $this->du_model->createReserve(array(
	                        'DocumentUc_id' => $document['doc_id'],
	                        'pmUser_id' => $data['pmUser_id']
	                    ));
	                    if (!empty($response['Error_Msg'])) {
	                        $error[] = $response['Error_Msg'];
	                        break;
	                    }
                	}
                }

            }

            //удаляем пустые документы учета
            if (count($error) == 0 && count($doc_array) > 0) {
				$docs_to_del = array();
				foreach($doc_array as $doc){
					$docs_to_del[] = $doc['doc_id'];
				}
                $response = $this->deleteEmptyDocumentUc($docs_to_del);
                if (!empty($response['Error_Msg'])) {
                    $error[] = $response['Error_Msg'];
                }
            }
        }

        if (count($error) > 0) {
            $result['success'] = false;
            $result['Error_Msg'] = $error[0];
        } else {
            $result['success'] = true;
        }

        return array($result);
    }

	/**
	 * Сохранение спецификации из JSON
	 */
	function saveCmpCallCardEvnOneDrugFromJSON($record, $data) {
		$IsSMPServer = $this->config->item('IsSMPServer');
		$needWorkWithMainDB = ($IsSMPServer) ? true : false;
		if(!isset($res['success']) || !$res['success'])
			$res['success'] = true;
		// если получили признак использования базы СМП при сохранении метода
		// (используем DEFAULT базу - мы переключили её внизу метода!)
		if ($IsSMPServer && !empty($data['useSMP']))
			$needWorkWithMainDB = false;

		// сохраняем в основную базу если это СМП сервер (переключаемся на MAIN базу)
		if ($needWorkWithMainDB) {
			unset($this->db);
			try{
				// переключение базы в модели работает только так!
				// так просто не работает: $this->load->database('main');
				$this->db = $this->load->database('main', true);
			} catch (Exception $e) {
				//return $this->createError($e->getCode(), 'db_unable_to_connect');
			}
		}

		switch($record->state) {
			case 'add':
			case 'edit':

				$EvnDrug_Lpu_id = $record->Lpu_id;

				if ($data['LpuBuilding_id'] > 0 && (!isset($EvnDrug_Lpu_id) || !($EvnDrug_Lpu_id > 0))) {

					$query = "
									select top 1
										lb.Lpu_id
									from
										v_LpuBuilding lb with (nolock)
									where
										lb.LpuBuilding_id = :LpuBuilding_id
								";

					$LpuBuildingLpu = $this->queryResult($query, array(
						'LpuBuilding_id' => $data['LpuBuilding_id']
					));

					if (!empty($LpuBuildingLpu[0]) && isset($LpuBuildingLpu[0]['Lpu_id'])) {
						$EvnDrug_Lpu_id = $LpuBuildingLpu[0]['Lpu_id'];
					}
				}
				$params = array(
					'EvnDrug_id' => $record->state == 'add' ? null : $record->EvnDrug_id,
					'EvnDrug_setDT' => DateTime::createFromFormat('d.m.Y H:i', $record->EvnDrug_setDate.' '.$record->EvnDrug_setTime),
					'Lpu_id' => $EvnDrug_Lpu_id,
					'LpuSection_id' => (!empty($record->LpuSection_id) && $record->LpuSection_id > 0) ? $record->LpuSection_id : null,
					'DrugNomen_id' => $record->DrugNomen_id,
					'Drug_id' => (!empty($record->Drug_id) && $record->Drug_id > 0) ? $record->Drug_id : null,
					'GoodsUnit_id' => $record->GoodsUnit_id,
					'CmpCallCard_id' => (!empty($record->CmpCallCard_id))?$record->CmpCallCard_id:$data['CmpCallCard_id'],
					'EmergencyTeam_id' => (!empty($record->EmergencyTeam_id) && $record->EmergencyTeam_id > 0) ? $record->EmergencyTeam_id : null,
					'EvnDrug_Comment' => $record->EvnDrug_Comment,
					'EvnDrug_Kolvo' => $record->EvnDrug_Kolvo,
					'EvnDrug_KolvoEd' => $record->EvnDrug_Kolvo, // копипаста по ТЗ #110814
					'EvnDrug_RealKolvo' => $record->EvnDrug_Kolvo, // копипаста по ТЗ #110814
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $data['Server_id']
				);

				if(isset($data['EvnDrug_id']) && $data['EvnDrug_id']>0)
					$params['EvnDrug_id'] = $data['EvnDrug_id'];
				if(isset($data['DocumentUcStr_cid']) && $data['DocumentUcStr_cid']>0)
					$params['DocumentUcStr_cid'] = $data['DocumentUcStr_cid'];
				if(isset($data['DocumentUc_cid']) && $data['DocumentUc_cid']>0)
					$params['DocumentUc_cid'] = $data['DocumentUc_cid'];
				$response = $this->saveObject('EvnDrug',$params);
				break;
			case 'delete':
				$response = $this->deleteCmpCallCardEvnDrug(array(
					'EvnDrug_id' => $record->EvnDrug_id,
					'pmUser_id' => $data['pmUser_id']
				));
				break;
		}
		if (!empty($response['Error_Msg'])) {
			$res['error'][] = $response['Error_Msg'];
			$res['success'] = false;
		}

		if ($needWorkWithMainDB) {
			unset($this->db);
			$this->db = $this->load->database('default', true);
			$this->db->throw_exception = false;
			switch($record->state) {
				case 'add':
				case 'edit':
					// медикамент с таким же EvnDrug_id создаем в БД СМП.
					if (!empty($response['EvnDrug_id'])) {
						$data['useSMP'] = true;
						$data['EvnDrug_id'] = $response['EvnDrug_id'];
						$data['DocumentUcStr_cid'] = (isset($response['DocumentUcStr_cid']) && !empty($response['DocumentUc_cid']))?$response['DocumentUcStr_cid']:null;
						$data['DocumentUc_cid'] = (isset($response['DocumentUc_cid']) && !empty($response['DocumentUc_cid']))?$response['DocumentUc_cid']:null;
						$add_error = $this->saveCmpCallCardEvnOneDrugFromJSON($record, $data);
						if(!$add_error['success'])
						{
							$res['success'] = false;
							foreach($add_error['error'] as $err)
								$res['error'][] = $err;
						}
					}
					break;
				case 'delete':
					// медикамент удаляем также в БД СМП.
					$data['useSMP'] = true;
					$add_error = $this->deleteCmpCallCardEvnDrug(array(
						'EvnDrug_id' => $record->EvnDrug_id,
						'pmUser_id' => $data['pmUser_id']
					));
					if(!$add_error['success'])
					{
						$res['error'][] = $add_error['Error_Msg'];
						$res['success'] = false;
					}
					break;
			}
		}
		return $res;
	}

	/**
	 * Сохранение спецификации из JSON
	 */
	function saveCmpCallCardEvnDrugFromJSON($data) {
		$result = array();
		$error = array();
		if (!empty($data['json_str']) && $data['CmpCallCard_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);
			foreach($dt as $record) {
				if(isset($record->EvnDrug_id) && $record->EvnDrug_id > 0) {
					$res = $this->saveCmpCallCardEvnOneDrugFromJSON($record, $data);
					if(!$res['success'])
						$error = $res['error'];
					unset($data['useSMP']);
					unset($data['EvnDrug_id']);
				}
			}
		}
		if (count($error) > 0) {
			$result['success'] = false;
			$result['Error_Msg'] = $error[0];
		} else {
			$result['success'] = true;
		}

		return array($result);
	}

    /**
     * Удаление информации о использовании медикаментов CМП
     */
    function deleteCmpCallCardDrug($data) {
        $result = array();
        $error = array();

        $this->load->model("DocumentUc_model", "du_model");

        if (empty($data['DocumentUcStr_id'])) {
            $query = "
                select
                    DocumentUcStr_id
                from
                    v_CmpCallCardDrug with (nolock)
                where
                    CmpCallCardDrug_id = :CmpCallCardDrug_id;
            ";
            $cccd_data = $this->getFirstRowFromQuery($query, array(
                'CmpCallCardDrug_id' => $data['CmpCallCardDrug_id']
            ));
            if (!empty($cccd_data['DocumentUcStr_id'])) {
                $data['DocumentUcStr_id'] = $cccd_data['DocumentUcStr_id'];
            }
        }

        if (!empty($data['DocumentUcStr_id'])) { //снятие резерва по строке документа учета
        	$query = "
                select top 1
                    du.DrugDocumentType_Code
                from
                    v_DocumentUcStr dus with (nolock)
                    inner join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                    inner join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                where
                    dus.DocumentUcStr_id = :DocumentUcStr_id;
            ";
            $ddt_data = $this->getFirstRowFromQuery($query, array(
                'DocumentUcStr_id' => $data['DocumentUcStr_id']
            ));
            if (!empty($ddt_data['DrugDocumentType_Code']) && $ddt_data['DrugDocumentType_Code'] == 26) {
            	// Тип документа 26 - Списание медикаментов из укладки на пациента - вернуть медикаменты в резерв (в укладку)
                $response = $this->du_model->returnDrugsToPack(array(
	                'DocumentUcStr_id' => $data['DocumentUcStr_id'],
	                'pmUser_id' => $data['pmUser_id']
	            ));
	            if (!empty($response['Error_Msg'])) {
	                $error[] = $response['Error_Msg'];
	            }
            } else {
            	$response = $this->du_model->removeReserve(array(
	                'DocumentUcStr_id' => $data['DocumentUcStr_id'],
	                'pmUser_id' => $data['pmUser_id']
	            ));
	            if (!empty($response['Error_Msg'])) {
	                $error[] = $response['Error_Msg'];
	            }
            }
        }

        $response = $this->deleteObject('CmpCallCardDrug', array(
            'CmpCallCardDrug_id' => $data['CmpCallCardDrug_id']
        ));
        if (!empty($response['Error_Msg'])) {
            $error[] = $response['Error_Msg'];
        } else if (!empty($data['DocumentUcStr_id'])) {
            $response = $this->deleteObject('DocumentUcStr', array(
                'DocumentUcStr_id' => $data['DocumentUcStr_id']
            ));
            if (!empty($response['Error_Msg'])) {
                $error[] = $response['Error_Msg'];
            }
        }

        if (count($error) > 0) {
            $result['Error_Msg'] = $error[0];
        }

        return $result;
    }

	/**
	 * Удаление информации о использовании медикаментов (простой учет)
	 */
	function deleteCmpCallCardEvnDrug($data) {

		$result = array();
		$error = array();

		//не стал использовать стандартный метод, т.к. там нет использования pmUser
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);

			execute dbo.p_EvnDrug_del
				@EvnDrug_id = :EvnDrug_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->getFirstRowFromQuery($query, $data);

		if ($result && is_array($result)) {
			if(empty($result['Error_Msg'])) {
				$result['success'] = true;
			}
			$response = $result;
		} else {
			$response = array('Error_Msg' => 'При удалении произошла ошибка');
		}

		if (!empty($response['Error_Msg'])) {
			$error[] = $response['Error_Msg'];
		}

		if (count($error) > 0) {
			$result['Error_Msg'] = $error[0];
			$result['success'] = false;
		}

		return $result;
	}

    /**
     * Получение информации о использовании медикаментов CМП
     */
    function loadCmpCallCardDrugList($data) {
        $params = array('CmpCallCard_id' => $data['CmpCallCard_id']);

        $query = "
			select
                cccd.CmpCallCardDrug_id,
                cccd.CmpCallCard_id,
                convert(varchar, cccd.CmpCallCardDrug_setDate, 104) as CmpCallCardDrug_setDate,
                convert(varchar(5), cccd.CmpCallCardDrug_setTime, 108) as CmpCallCardDrug_setTime,
                cccd.MedStaffFact_id,
                cccd.Drug_id,
                cccd.CmpCallCardDrug_Ser,
                cccd.CmpCallCardDrug_Kolvo,
                cccd.GoodsUnit_id,
                cccd.CmpCallCardDrug_KolvoUnit,
                cccd.CmpCallCardDrug_Cost,
                cccd.CmpCallCardDrug_Sum,
                cccd.DrugFinance_id,
                cccd.WhsDocumentCostItemType_id,
                cccd.Storage_id,
                cccd.Mol_id,
                cccd.DocumentUc_id,
                cccd.DocumentUcStr_id,
                cccd.DocumentUcStr_oid,
                cccd.LpuBuilding_id,
                d.DrugPrepFas_id,
                d.Drug_Name,
                d.DrugTorg_Name,
                gu.GoodsUnit_Name,
                du.Contragent_sid as Contragent_id,
                du.Lpu_id,
                du.Org_id,
                du.StorageZone_sid as StorageZone_id,
                dus.PrepSeries_id,
                dcm.DrugComplexMnn_RusName,
                dds.DrugDocumentStatus_Code,
		        dn.DrugNomen_Code
			from
				v_CmpCallCardDrug cccd with(nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = cccd.Drug_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = cccd.GoodsUnit_id
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = cccd.DocumentUc_id
				left join v_DocumentUcStr dus with (nolock) on dus.DocumentUcStr_id = cccd.DocumentUcStr_id
				left join v_DrugDocumentStatus dds with (nolock) on dds.DrugDocumentStatus_id = du.DrugDocumentStatus_id
				left join rls.v_DrugPrep dpf with (nolock) on dpf.DrugPrepFas_id = d.DrugPrepFas_id
				left join rls.v_DrugComplexMnn dcm with (nolock) on dcm.DrugComplexMnn_id = d.DrugComplexMnn_id
                outer apply (
                    select top 1
                        i_dn.DrugNomen_Code
                    from
                        rls.v_DrugNomen i_dn with (nolock)
                    where
                        i_dn.Drug_id = d.Drug_id
                    order by
                        i_dn.DrugNomen_Code desc
                ) dn
			where
				cccd.CmpCallCard_id = :CmpCallCard_id;
		";

        $response = $this->queryResult($query, $params);
        return $response;
    }

	/**
	 * Получение информации о использовании медикаментов CМП (простой учет)
	 */
	function loadCmpCallCardEvnDrugList($data) {
		$params = array('CmpCallCard_id' => $data['CmpCallCard_id']);

		$query = "
			select
                ed.EvnDrug_id,
                ed.CmpCallCard_id,
                ed.EvnDrug_Comment,
                convert(varchar, ed.EvnDrug_setDate, 104) as EvnDrug_setDate,
                convert(varchar(5), ed.EvnDrug_setTime, 108) as EvnDrug_setTime,
                ed.Drug_id,
                ed.DrugNomen_id,
                ed.EvnDrug_Kolvo,
                ed.GoodsUnit_id,
                gu.GoodsUnit_Name,
                ed.Lpu_id,
		        dn.DrugNomen_Code,
		        dn.DrugNomen_Name
			from
				v_EvnDrug ed with(nolock)
				--left join rls.v_Drug d with (nolock) on d.Drug_id = ed.Drug_id
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = ed.GoodsUnit_id
                outer apply (
                    select top 1
                        i_dn.DrugNomen_Code,
                        i_dn.DrugNomen_Name
                    from
                        rls.v_DrugNomen i_dn with (nolock)
                    where
                        i_dn.DrugNomen_id = ed.DrugNomen_id
                    order by
                        i_dn.DrugNomen_Code desc
                ) dn
			where
				ed.CmpCallCard_id = :CmpCallCard_id;
		";

		$response = $this->queryResult($query, $params);
		return $response;
	}

    /**
     * Вспомогательная функция преобразования формата даты
     * Получает строку c датой в формате d.m.Y, возвращает строку с датой в формате Y-m-d
     */
    function formatDate($date) {
        $d_str = null;
        if (!empty($date)) {
            $date = preg_replace('/\//', '.', $date);
            $d_arr = explode('.', $date);
            if (is_array($d_arr)) {
                $d_arr = array_reverse($d_arr);
            }
            if (count($d_arr) == 3) {
                $d_str = join('-', $d_arr);
            }
        }
        return $d_str;
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadMedStaffFactCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['MedStaffFact_id'])) {
            $where[] = 'msf.MedStaffFact_id = :MedStaffFact_id';
            $params['MedStaffFact_id'] = $data['MedStaffFact_id'];
        } else {
            if (!empty($data['Lpu_id'])) {
                $where[] = 'mp.Lpu_id = :Lpu_id';
                $params['Lpu_id'] = $data['Lpu_id'];
            }
            if (!empty($data['EmergencyTeam_id'])) {
                $where[] = 'et.EmergencyTeam_id = :EmergencyTeam_id';
                $params['EmergencyTeam_id'] = $data['EmergencyTeam_id'];
            }
            if (!empty($data['query'])) {
                $where[] = 'msf.Person_Fio like :query';
                $params['query'] = "%".$data['query']."%";
            }
            $where[] = "mp.Person_Fio is not null";
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select distinct top 250
                msf.MedStaffFact_id,
                msf.Person_Fio as MedStaffFact_Name,
                mp.Lpu_id,
                isnull(mp.MedPersonal_Code, '') as MedPersonal_DloCode,
                isnull(mp.MedPersonal_TabCode, '') as MedPersonal_TabCode,
                ls.LpuSection_Name,
                p.name as PostMed_Name,
                cast(msf.MedStaffFact_Stavka as varchar) as MedStaffFact_Stavka,
                convert(varchar(10), msf.WorkData_begDate, 104) as WorkData_begDate,
                convert(varchar(10), msf.WorkData_endDate, 104) as WorkData_endDate
            from
                v_EmergencyTeam et with (nolock)
                inner join v_MedPersonal mp with (nolock) on
                    mp.MedPersonal_id = et.EmergencyTeam_HeadShift or
                    mp.MedPersonal_id = et.EmergencyTeam_Assistant1 or
                    mp.MedPersonal_id = et.EmergencyTeam_Assistant2
                left join v_MedStaffFact msf with (nolock) on msf.MedPersonal_id = mp.MedPersonal_id
                left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
                left join persis.Post p with (nolock) on p.id = msf.Post_id
		    {$where_clause}
		    order by
		        msf.Person_Fio
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadLpuBuildingCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['LpuBuilding_id'])) {
            $where[] = 'lb.LpuBuilding_id = :LpuBuilding_id';
            $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
        } else {
            if (!empty($data['Lpu_id'])) {
                $where[] = 'lb.Lpu_id = :Lpu_id';
                $params['Lpu_id'] = $data['Lpu_id'];
            }
            if (!empty($data['LpuBuildingType_id'])) {
                $where[] = 'lb.LpuBuildingType_id = :LpuBuildingType_id';
                $params['LpuBuildingType_id'] = $data['LpuBuildingType_id'];
            }
            if (!empty($data['query'])) {
                $where[] = 'lb.LpuBuilding_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        lb.LpuBuilding_id,
		        lb.LpuBuilding_Name
		    from
                v_LpuBuilding lb with (nolock)
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadStorageCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['Storage_id'])) {
            $where[] = 's.Storage_id = :Storage_id';
            $params['Storage_id'] = $data['Storage_id'];
        } else {
            if (!empty($data['LpuBuilding_id'])) {
                $where[] = "s.Storage_id in (
                    select
                        ssl.Storage_id
                    from
                        v_StorageStructLevel ssl with (nolock)
                        left join v_Storage s with (nolock) on s.Storage_id = ssl.Storage_id or s.Storage_pid = ssl.Storage_id 
                        outer apply (
							select top 1
								i_ms.MedService_id
							from
								v_StorageStructLevel i_ssl with (nolock)
								left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id			
								left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
							where
								i_ssl.Storage_id = s.Storage_id and
								i_mst.MedServiceType_SysNick = 'merch'
						) ms
                    where                    	
                        ssl.LpuBuilding_id = :LpuBuilding_id and
                        ( --проверка на отсутствие связи со службой с типом  АРМ товароведа только для дочерних складов
                        	ms.MedService_id is null or 
                        	s.Storage_id = ssl.Storage_id 
                        )
                )";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            if (!empty($data['query'])) {
                $where[] = 's.Storage_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        s.Storage_id,
		        s.Storage_Name,
		        sz_cnt.StorageZone_Count
		    from
                v_Storage s with (nolock)
                outer apply (
                    select
                        count(sz.StorageZone_id) as StorageZone_Count
                    from
                        v_StorageZone sz with (nolock)
                    where
                        sz.Storage_id = s.Storage_id
                ) sz_cnt
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadMolCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['Mol_id'])) {
            $where[] = 'm.Mol_id = :Mol_id';
            $params['Mol_id'] = $data['Mol_id'];
        } else {
            if (!empty($data['Storage_id'])) {
                $where[] = 'm.Storage_id = :Storage_id';
                $params['Storage_id'] = $data['Storage_id'];
            } else {
                return false;
            }
            if (!empty($data['query'])) {
                $where[] = 'mn.Mol_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        m.Mol_id,
		        mn.Mol_Name
		    from
                v_Mol m with (nolock)
                outer apply (
                    select top 1
                        *
                    from
                        v_MedPersonal i_mp with (nolock)
                    where
                        i_mp.MedPersonal_id = m.MedPersonal_id
                ) mp
                outer apply (
                    select
                        (
                            case
                                when
                                    m.Person_id is not null
                                then
                                    isnull(m.Person_SurName+' ', '')+
                                    isnull(m.Person_FirName+' ', '')+
                                    isnull(m.Person_SecName+' ', '')
                                else
                                    isnull(mp.Person_SurName+' ', '')+
                                    isnull(mp.Person_FirName+' ', '')+
                                    isnull(mp.Person_SecName+' ', '')
                            end
                        ) as Mol_Name
                ) mn
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadStorageZoneCombo($data) {
        $where = array();
        $params = array();

        if (!empty($data['StorageZone_id'])) {
            $where[] = 'sz.StorageZone_id = :StorageZone_id';
            $params['StorageZone_id'] = $data['StorageZone_id'];
        } else {
            if (!empty($data['Storage_id'])) {
                $where[] = 'sz.Storage_id = :Storage_id';
                $params['Storage_id'] = $data['Storage_id'];
            } else {
                return false;
            }
            if (!empty($data['query'])) {
                //$where[] = 'mn.Mol_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    select top 250
		        sz.StorageZone_id,
		        case 
		        	when liable.EmergencyTeam_Num is null then sz.StorageZone_Code
		        	else rtrim(sz.StorageZone_Code +' / '+liable.EmergencyTeam_Num)
		        end as StorageZone_Name
		    from
                v_StorageZone sz with (nolock)
                outer apply (
                	select top 1
                		et.EmergencyTeam_Num
                	from v_StorageZoneLiable szl with (nolock)
                	left join v_EmergencyTeam et with (nolock) on et.EmergencyTeam_id = szl.StorageZoneLiable_ObjectId
                	where 
                		szl.StorageZone_id = sz.StorageZone_id
                		and szl.StorageZoneLiable_ObjectName = 'Бригада СМП'
                		and szl.StorageZoneLiable_endDate is null
                ) liable
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDrugPrepFasCombo($data) {
        $where = array();
        $with = array();
        $join = array();
        $params = array();

        if (!empty($data['DrugPrepFas_id'])) {
            $where[] = 'dpf.DrugPrepFas_id = :DrugPrepFas_id';
            $params['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
        } else {
            if (!empty($data['Storage_id'])) {
            	// По дефолту берем медикаменты, которые есть на субсчете достуно 
            	$sz = " and sat.SubAccountType_Code = 1 ";
            	if(!empty($data['StorageZone_id'])){
            		$sz = " and exists(
            			select top 1 dsz.DrugStorageZone_id
            			from v_DrugStorageZone dsz with (nolock)
            			inner join v_StorageZone sz with (nolock) on sz.StorageZone_id = dsz.StorageZone_id
            			where 
            				dsz.StorageZone_id = :StorageZone_id
            				and dsz.Drug_id = dor.Drug_id
            				and isnull(dsz.DrugShipment_id,0) = isnull(dor.DrugShipment_id,0)
            				and sz.Storage_id = dor.Storage_id
            				and dsz.DrugStorageZone_Count > 0
            		) ";
					// Если указано место хранения то берем медикаменты:
					// с субсчета доступно если место хранения не передано на подотчет
					// с субсчета зарезервировано если место хранения подотчетное - при передаче на подотчет все медикаменты резервируются
					$sz .= " 
						and (
								(
									sat.SubAccountType_Code = 2
									and exists(
				            			select top 1 szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl with (nolock)
				            			where 
				            				szl.StorageZone_id = :StorageZone_id
				            				and szl.StorageZoneLiable_endDate is null
				            		)
								)
								or
								(
									sat.SubAccountType_Code = 1
									and not exists(
				            			select top 1 szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl with (nolock)
				            			where 
				            				szl.StorageZone_id = :StorageZone_id
				            				and szl.StorageZoneLiable_endDate is null
				            		)
								)
							)
					";
					$params['StorageZone_id'] = $data['StorageZone_id'];
            	}
                $with[] = " ost as (
					select
						dor.Drug_id,
						d.DrugPrepFas_id,
						isnull(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join rls.v_Drug d with (nolock) on d.Drug_id = dor.Drug_id
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						dor.Storage_id = :Storage_id and
                        (ps.PrepSeries_GodnDate is null or ps.PrepSeries_GodnDate >= @Curr_Date) and
                        isnull(ps.PrepSeries_IsDefect, 1) = 1
                    ".$sz."
					group by
						dor.Drug_id, d.DrugPrepFas_id
				)";
                $join[] = "left join ost on ost.DrugPrepFas_id = dpf.DrugPrepFas_id";
                $where[] = 'ost.Drug_id is not null';
                $params['Storage_id'] = $data['Storage_id'];
            }
            if (!empty($data['query'])) {
                $where[] = 'dpf.DrugPrep_Name like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $with_clause = implode(', ', $with);
        if (strlen($with_clause)) {
            $with_clause = "
				with {$with_clause}
			";
        }

        $join_clause = implode(' ', $join);

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            declare
                @Curr_Date date;

            set @Curr_Date = dbo.tzGetDate();

            {$with_clause}
		    select top 250
		        dpf.DrugPrepFas_id,
		        dpf.DrugPrep_Name as DrugPrepFas_Name
		    from
                rls.v_DrugPrep dpf with (nolock)
                {$join_clause}
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDrugCombo($data) {
        $where = array();
        $with = array();
        $join = array();
        $params = array();

        if (!empty($data['Drug_id'])) {
            $where[] = 'd.Drug_id = :Drug_id';
            $params['Drug_id'] = $data['Drug_id'];
        } else {
            if (!empty($data['Storage_id'])) {
            	// По дефолту берем медикаменты, которые есть на субсчете достуно 
            	$sz = " and sat.SubAccountType_Code = 1 ";
                if (!empty($data['DrugShipment_setDT_max'])) {
                    $sz .= " and cast(ds.DrugShipment_setDT as date) <= :DrugShipment_setDT_max ";
                    $params['DrugShipment_setDT_max'] = $data['DrugShipment_setDT_max'];
                }
            	if(!empty($data['StorageZone_id'])){
            		$sz = " and exists(
            			select top 1 dsz.DrugStorageZone_id
            			from v_DrugStorageZone dsz with (nolock)
            			inner join v_StorageZone sz with (nolock) on sz.StorageZone_id = dsz.StorageZone_id
            			where 
            				dsz.StorageZone_id = :StorageZone_id
            				and dsz.Drug_id = dor.Drug_id
            				and isnull(dsz.DrugShipment_id,0) = isnull(dor.DrugShipment_id,0)
            				and sz.Storage_id = dor.Storage_id
            				and dsz.DrugStorageZone_Count > 0
            		) ";
					// Если указано место хранения то берем медикаменты:
					// с субсчета доступно если место хранения не передано на подотчет
					// с субсчета зарезервировано если место хранения подотчетное - при передаче на подотчет все медикаменты резервируются
					$sz .= " 
						and (
								(
									sat.SubAccountType_Code = 2
									and exists(
				            			select top 1 szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl with (nolock)
				            			where 
				            				szl.StorageZone_id = :StorageZone_id
				            				and szl.StorageZoneLiable_endDate is null
				            		)
								)
								or
								(
									sat.SubAccountType_Code = 1
									and not exists(
				            			select top 1 szl.StorageZoneLiable_id
				            			from v_StorageZoneLiable szl with (nolock)
				            			where 
				            				szl.StorageZone_id = :StorageZone_id
				            				and szl.StorageZoneLiable_endDate is null
				            		)
								)
							)
					";
					$params['StorageZone_id'] = $data['StorageZone_id'];
            	}
                $with[] = " ost as (
					select
						dor.Drug_id,
						isnull(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
					from
						v_DrugOstatRegistry dor with (nolock)
						left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
						left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
						left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
					where
						dor.DrugOstatRegistry_Kolvo > 0 and
						dor.Storage_id = :Storage_id and
                        (ps.PrepSeries_GodnDate is null or ps.PrepSeries_GodnDate >= @Curr_Date) and
                        isnull(ps.PrepSeries_IsDefect, 1) = 1
                    	".$sz."
					group by
						dor.Drug_id
				)";
                $join[] = "left join ost on ost.Drug_id = d.Drug_id";
                $where[] = 'ost.Drug_id is not null';
                $params['Storage_id'] = $data['Storage_id'];
            }
            if (!empty($data['DrugPrepFas_id'])) {
                $where[] = 'd.DrugPrepFas_id = :DrugPrepFas_id';
                $params['DrugPrepFas_id'] = $data['DrugPrepFas_id'];
            }
            if (!empty($data['query'])) {
                $where[] = 'd.Drug_Nomen like :query';
                $params['query'] = "%".$data['query']."%";
            }
        }

        $with_clause = implode(', ', $with);
        if (strlen($with_clause)) {
            $with_clause = "
				with {$with_clause}
			";
        }

        $join_clause = implode(' ', $join);

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            declare
                @Curr_Date date;

            set @Curr_Date = dbo.tzGetDate();

            {$with_clause}
		    select top 250
		        d.Drug_id,
		        d.Drug_Nomen,
		        d.Drug_Name,
		        dn.DrugNomen_Code
		    from
                rls.v_Drug d with (nolock)
                outer apply (
                    select top 1
                        i_dn.DrugNomen_Code
                    from
                        rls.v_DrugNomen i_dn with (nolock)
                    where
                        i_dn.Drug_id = d.Drug_id
                    order by
                        i_dn.DrugNomen_Code desc
                ) dn
                {$join_clause}
		    {$where_clause}
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadDocumentUcStrOidCombo($data) {
        $this->load->model('DocumentUc_model', 'DocumentUc_model');

        $where = array();
        $join = array();
        $params = array();
        $count = "ost.cnt";
        // По дефолту берем медикаменты, которые есть на субсчете достуно 
        $sub = " sat.SubAccountType_Code = 1 and ";

        $params['DefaultGoodsUnit_id'] = $this->DocumentUc_model->getDefaultGoodsUnitId();

        if (!empty($data['DocumentUcStr_id'])) {
            $where[] = 'dus.DocumentUcStr_id = :DocumentUcStr_id';
            $params['DocumentUcStr_id'] = $data['DocumentUcStr_id'];
            $params['Storage_id'] = null;
            $join[] = " outer apply(
        		select top 1 
        			dsz.DrugStorageZone_id, 
        			dsz.DrugStorageZone_Count,
        			dsl.DocumentUcStr_id  
        		from v_DrugStorageZone dsz with (nolock) 
        		left join v_DrugShipmentLink dsl with (nolock) on dsz.DrugShipment_id = dsl.DrugShipment_id
        		where 
        			dsz.Drug_id = ost.Drug_id
        			and dsz.DrugShipment_id = ost.DrugShipment_id
        			and dsz.StorageZone_id = du.StorageZone_sid
        	) dsz_c ";
			$count = "isnull(dsz_c.DrugStorageZone_Count,ost.cnt)";
        } else {
            $params['DocumentUcStr_id'] = null;

            //должны учитываться только партии из приходных документов учета
            $dd_type_where = 'ddt.DrugDocumentType_Code in (3, 6)'; //3 - Документ ввода остатков; 6 - Приходная накладная.

            if (!empty($data['StorageZone_id'])) {
            	// Если указано место хранения то берем медикаменты:
				// с субсчета доступно если место хранения не передано на подотчет
				// с субсчета зарезервировано если место хранения подотчетное - при передаче на подотчет все медикаменты резервируются
            	$sub = " 
					(
						(
							sat.SubAccountType_Code = 2
							and exists(
		            			select top 1 szl.StorageZoneLiable_id
		            			from v_StorageZoneLiable szl with (nolock)
		            			where 
		            				szl.StorageZone_id = :StorageZone_id
		            				and szl.StorageZoneLiable_endDate is null
		            		)
						)
						or
						(
							sat.SubAccountType_Code = 1
							and not exists(
		            			select top 1 szl.StorageZoneLiable_id
		            			from v_StorageZoneLiable szl with (nolock)
		            			where 
		            				szl.StorageZone_id = :StorageZone_id
		            				and szl.StorageZoneLiable_endDate is null
		            		)
						)
					) and 
				";
            	$join[] = " outer apply(
            		select top 1 
            			dsz.DrugStorageZone_id, 
            			dsz.DrugStorageZone_Count,
            			dsl.DocumentUcStr_id  
            		from v_DrugStorageZone dsz with (nolock) 
            		left join v_DrugShipmentLink dsl with (nolock) on dsz.DrugShipment_id = dsl.DrugShipment_id
            		where 
            			dsz.Drug_id = ost.Drug_id
            			and dsz.DrugShipment_id = ost.DrugShipment_id
            			and dsz.StorageZone_id = :StorageZone_id
            	) dsz_c ";
				$count = "isnull(dsz_c.DrugStorageZone_Count,0)";
                $where[] = "dus.DocumentUcStr_id = isnull(dsz_c.DocumentUcStr_id,0)";
                $params['StorageZone_id'] = $data['StorageZone_id'];
            }
            if (!empty($data['Storage_id'])) {
                $where[] = 'ost.Drug_id is not null';
                $dd_type_where = "({$dd_type_where} or (ddt.DrugDocumentType_Code = 15 and du.Storage_tid = :Storage_id))"; //15 - Накладная на внутреннее перемещение
                $params['Storage_id'] = $data['Storage_id'];
            } else {
                return false;
            }
            if (!empty($data['Drug_id'])) {
                $where[] = 'dus.Drug_id = :Drug_id';
                $params['Drug_id'] = $data['Drug_id'];
            } else {
                //$where[] = 'd.Drug_id is not null';
                return false;
            }
            if (!empty($data['DrugShipment_setDT_max'])) {
                $sub .= ' cast(ds.DrugShipment_setDT as date) <= :DrugShipment_setDT_max and ';
                $params['DrugShipment_setDT_max'] = $data['DrugShipment_setDT_max'];
            }
            if (!empty($data['query'])) {
                $where[] = 'ps.PrepSeries_Ser like :query';
                $params['query'] = "%".$data['query']."%";
            }
            if (!empty($dd_type_where)) {
                $where[] = $dd_type_where;
            }
        }

        $join_clause = implode(' ', $join);

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
            declare
                @Curr_Date date,
                @Storage_id bigint = :Storage_id;

            set @Curr_Date = dbo.tzGetDate();

            if (@Storage_id is null and :DocumentUcStr_id is not null)
            begin
                set @Storage_id = (
                    select
                        i_du.Storage_tid
                    from
                        v_DocumentUcStr i_dus with (nolock)
                        left join v_DocumentUc i_du with (nolock) on i_du.DocumentUc_id = i_dus.DocumentUc_id
                    where
                        i_dus.DocumentUcStr_id = :DocumentUcStr_id
                )
            end;

            with ost as (
                select
                    dor.Drug_id,
                    dor.PrepSeries_id,
                    dor.DrugShipment_id,
                    isnull(max(dor.GoodsUnit_id), :DefaultGoodsUnit_id) as GoodsUnit_id,
                    isnull(sum(dor.DrugOstatRegistry_Kolvo), 0) as cnt
                from
                    v_DrugOstatRegistry dor with (nolock)
                    left join v_SubAccountType sat with (nolock) on sat.SubAccountType_id = dor.SubAccountType_id
                    left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dor.PrepSeries_id
                    left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dor.DrugShipment_id
                where
                    dor.DrugOstatRegistry_Kolvo > 0 and
                    ".$sub."
                    dor.Storage_id = @Storage_id and
                    (ps.PrepSeries_GodnDate is null or ps.PrepSeries_GodnDate >= @Curr_Date) and
                    isnull(ps.PrepSeries_IsDefect, 1) = 1
                group by
                    dor.Drug_id, dor.PrepSeries_id, dor.DrugShipment_id
            )
		    select top 250
		        dus.DocumentUcStr_id,
		        dus.PrepSeries_id,
		        cast(dus.DocumentUcStr_Price as decimal(14,2)) as DocumentUcStr_Price,
		        (
		            isnull(ps.PrepSeries_Ser+' ', '')+
		            isnull(convert(varchar(10), ps.PrepSeries_GodnDate, 104)+' ', '')+
		            isnull(cast(dus.DocumentUcStr_Price as varchar)+' ', '')+
		            isnull(cast({$count} as varchar)+isnull(' '+b_gu.GoodsUnit_Nick+' ',''), '')+
		            isnull(df.DrugFinance_Name+' ', '')+
		            isnull(wdcit.WhsDocumentCostItemType_Name, '')
		        ) as DocumentUcStr_Name,
		        {$count} as DrugOstatRegistry_Kolvo,
		        du.DrugFinance_id,
		        df.DrugFinance_Name,
                du.WhsDocumentCostItemType_id,
                wdcit.WhsDocumentCostItemType_Name,
                ps.PrepSeries_Ser,
                convert(varchar(10), ps.PrepSeries_GodnDate, 104) as PrepSeries_GodnDate,
                coalesce(dus.GoodsUnit_bid, ost.GoodsUnit_id, :DefaultGoodsUnit_id) as GoodsUnit_bid,
				coalesce(dus.GoodsUnit_id, p_dus.GoodsUnit_id, :DefaultGoodsUnit_id) as GoodsUnit_id,
				b_gu.GoodsUnit_Nick as GoodsUnit_bNick,
				isnull(b_gpc.GoodsPackCount_Count, 1) as GoodsPackCount_bCount
		    from
                v_DocumentUcStr dus with (nolock)
                left join v_DocumentUc du with (nolock) on du.DocumentUc_id = dus.DocumentUc_id
                left join v_DrugShipmentLink dsll with (nolock) on dsll.DocumentUcStr_id = dus.DocumentUcStr_id
                left join v_DrugShipment ds with (nolock) on ds.DrugShipment_id = dsll.DrugShipment_id
				left join v_DrugShipmentLink p_dsll with (nolock) on p_dsll.DrugShipment_id = ds.DrugShipment_pid -- получение партии прихода
				left join v_DocumentUcStr p_dus with (nolock) on p_dus.DocumentUcStr_id = p_dsll.DocumentUcStr_id
                left join v_DrugDocumentType ddt with (nolock) on ddt.DrugDocumentType_id = du.DrugDocumentType_id
                left join rls.v_Drug d with (nolock) on d.Drug_id = dus.Drug_id
                left join rls.v_PrepSeries ps with (nolock) on ps.PrepSeries_id = dus.PrepSeries_id
                left join v_DrugFinance df with (nolock) on df.DrugFinance_id = du.DrugFinance_id
                left join v_WhsDocumentCostItemType wdcit with (nolock) on wdcit.WhsDocumentCostItemType_id = du.WhsDocumentCostItemType_id
                left join ost on ost.Drug_id = dus.Drug_id and ost.PrepSeries_id = dus.PrepSeries_id and ost.DrugShipment_id = dsll.DrugShipment_id
                left join v_GoodsUnit b_gu with (nolock) on b_gu.GoodsUnit_id = isnull(dus.GoodsUnit_bid, :DefaultGoodsUnit_id)
                outer apply (
                    select top 1
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = coalesce(dus.GoodsUnit_bid, ost.GoodsUnit_id) and
                        i_gpc.DrugComplexMnn_id = d.DrugComplexMnn_id and
                        (
                            d.DrugTorg_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = d.DrugTorg_id
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc, i_gpc.Org_id
                ) b_gpc
		    	{$join_clause}
		    {$where_clause}
		    order by
		        ps.PrepSeries_GodnDate

		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Загрузка списка для комбобокса
     */
    function loadGoodsUnitCombo($data) {
        $where = array();
        $params = $data;

        if (!empty($data['GoodsUnit_id'])) {
            $where[] = 'gu.GoodsUnit_id = @GoodsUnit_id';
        } else {
            if (!empty($data['Drug_id'])) {
                $where[] = 'gpc.GoodsUnit_id is not null';
            } else {
                return false;
            }
            if (!empty($data['query'])) {
                $where[] = 'gu.GoodsUnit_Name like :query';
                $params['query'] = $data['query']."%";
            }
        }

        $where_clause = implode(' and ', $where);
        if (strlen($where_clause)) {
            $where_clause = "
				where
					{$where_clause}
			";
        }

        $query = "
		    declare
                @GoodsUnit_id bigint = :GoodsUnit_id,
                @Drug_id bigint = :Drug_id,
                @DrugComplexMnn_id bigint = null,
                @Tradnames_id bigint = null;

            if (@Drug_id is not null)
            begin
                select
                    @DrugComplexMnn_id = DrugComplexMnn_id,
                    @Tradnames_id = d.DrugTorg_id
                from
                    rls.v_Drug d with (nolock)
                where
                    Drug_id = @Drug_id
            end;

            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                (
                    case
                        when gu.GoodsUnit_Name = 'упаковка' then 1
                        else gpc.GoodsPackCount_Count
                    end
                ) GoodsPackCount_Count
            from
                v_GoodsUnit gu with (nolock)
                outer apply (
                    select top 1
                        i_gpc.GoodsUnit_id,
                        i_gpc.GoodsPackCount_Count
                    from
                        v_GoodsPackCount i_gpc with (nolock)
                    where
                        i_gpc.GoodsUnit_id = gu.GoodsUnit_id and
                        i_gpc.DrugComplexMnn_id = @DrugComplexMnn_id and
                        (
                            @Tradnames_id is null or
                            i_gpc.TRADENAMES_ID is null or
                            i_gpc.TRADENAMES_ID = @Tradnames_id
                        )
                    order by
                        i_gpc.TRADENAMES_ID desc
                ) gpc
            {$where_clause}
            union
            select
                gu.GoodsUnit_id,
                gu.GoodsUnit_Name,
                1 as GoodsPackCount_Count
            from
                v_GoodsUnit gu with (nolock)
            where
                @GoodsUnit_id is null and -- упаковка добавляется в список только если не передан id конкретной записи
                gu.GoodsUnit_Name = 'упаковка' and
                (
                    :query is null or
                    'упаковка' like :query
                )
		";

        $result = $this->db->query($query, $params);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Получение значений по умолчанию для формы использования медикаментов
     */
    function getCmpCallCardDrugDefaultValues($data){
        $query = "
             select
                msf.MedStaffFact_id,
                et.LpuBuilding_id,
                s.Storage_id,
                m.Mol_id,
                case 
                	when sz.StorageZone_id is not null then sz.StorageZone_id
                	else sz_last.StorageZone_id
                end as StorageZone_id
            from
                v_EmergencyTeam et with (nolock)
                outer apply (
                    select top 1
                        i_msf.MedStaffFact_id
                    from
                        v_MedPersonal i_mp with (nolock)
                        left join v_MedStaffFact i_msf with (nolock) on i_msf.MedPersonal_id = i_mp.MedPersonal_id
                    where
                        i_mp.MedPersonal_id = et.EmergencyTeam_HeadShift
                    order by
                        i_msf.Person_Fio
                ) msf
                outer apply (
                    select top 1
                        Storage_id
                    from
                        v_StorageStructLevel i_ssl with (nolock)
                    where
                        i_ssl.LpuBuilding_id = et.LpuBuilding_id
                    order by
                        i_ssl.StorageStructLevel_id
                ) s
                outer apply (
                    select top 1
                        Mol_id
                    from
                        v_Mol i_m with (nolock)
                    where
                        i_m.Storage_id = s.Storage_id
                    order by
                        i_m.Mol_id
                ) m
				outer apply (
                    select top 1
                        i_sz.StorageZone_id
                    from
                        v_StorageZoneLiable i_sz with (nolock)
                    where
                        i_sz.StorageZoneLiable_ObjectName = 'Бригада СМП'
                        and i_sz.StorageZoneLiable_ObjectId = et.EmergencyTeam_id
                        and i_sz.StorageZoneLiable_endDate is null
                    order by
                        i_sz.StorageZone_id
                ) sz
				outer apply (
                    select top 1
                        i_szl.StorageZone_id
                    from
                        v_StorageZoneLiable i_szl with (nolock)
                    where
                        i_szl.StorageZoneLiable_ObjectName = 'Бригада СМП'
                        and i_szl.StorageZoneLiable_ObjectId = et.EmergencyTeam_id
                        and i_szl.StorageZoneLiable_endDate is not null
                        and exists (
                        	select top 1 dsz.DrugStorageZone_id 
                        	from v_DrugStorageZone dsz with (nolock) 
                        	where dsz.StorageZone_id = i_szl.StorageZone_id and dsz.DrugStorageZone_Count > 0
                        )
                    order by
                        i_szl.StorageZoneLiable_endDate desc
                ) sz_last
            where
                EmergencyTeam_id = :EmergencyTeam_id;
		";

        $result = $this->db->query($query, $data);
        if ( is_object($result) ) {
            $result = $result->result('array');
            $result[0]['success'] = true;
            return $result;
        } else {
            return false;
        }
    }

	/**
	 * Загрузка списка талонов вызова СМП
	 */
	function loadCmpCallCardList($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['CmpCallCard_id'])) {
			$filters[] = "CCC.CmpCallCard_id = :CmpCallCard_id";
			$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		} else {
			$filters[] = "CCC.CmpCallCard_Numv is not null";

			$date_str = !empty($data['date'])?$data['date']:$this->currentDT->format('Y-m-d');

			$begDate = date_modify(date_create($date_str), '-1 day');
			$endDate = date_create($date_str);

			$filters[] = "CCC.CmpCallCard_prmDT between :begDate and :endDate";
			$params['begDate'] = $begDate->format('Y-m-d').' 00:00';
			$params['endDate'] = $endDate->format('Y-m-d').' 23:59';

			if (!empty($data['query']) && is_numeric($data['query'])) {
				$filters[] = "CCC.CmpCallCard_Numv = :CmpCallCard_Numv";
				$params['CmpCallCard_Numv'] = $data['query'];
			}
		}

		$filters_str = implode("\nand ", $filters);
		$query = "
			select
				CCC.CmpCallCard_id,
				CCC.CmpCallCard_Numv,
				convert(varchar(10), CCC.CmpCallCard_prmDT, 104) as CmpCallCard_prmDate,
				convert(varchar(5), CCC.CmpCallCard_prmDT, 108) as CmpCallCard_prmTime,
				CCC.Person_id,
				rtrim(CCC.Person_SurName) as Person_SurName,
				rtrim(CCC.Person_FirName) as Person_FirName,
				rtrim(CCC.Person_SecName) as Person_SecName,
				convert(varchar(10), BirthDay.Value, 104) as Person_BirthDay,
				dbo.AgeYMD(BirthDay.Value, CCC.CmpCallCard_prmDT, 1) as PersonAgeYears,
				dbo.AgeYMD(BirthDay.Value, CCC.CmpCallCard_prmDT, 2) as PersonAgeMonths,
				dbo.AgeYMD(BirthDay.Value, CCC.CmpCallCard_prmDT, 3) as PersonAgeDays
			from
				v_CmpCallCard CCC with(nolock)
				left join v_PersonState PS with(nolock) on PS.Person_id = CCC.Person_id
				outer apply(
					select isnull(CCC.Person_BirthDay,PS.Person_BirthDay) as Value
				) BirthDay
			where {$filters_str}
		";
		//echo getDebugSQL($query, $params);exit;
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		foreach($response as &$item) {
			$item['PersonAgeStr'] = '';
			switch(true) {
				case $item['PersonAgeYears'] > 0:
					$item['PersonAgeStr'] = $item['PersonAgeYears'].' '.ru_word_case('год','года','лет', $item['PersonAgeYears']);
					break;
				case $item['PersonAgeMonths'] > 0:
					$item['PersonAgeStr'] = $item['PersonAgeMonths'].' '.ru_word_case('месяц','месяца','месяцев', $item['PersonAgeMonths']);
					break;
				case $item['PersonAgeDays'] > 0:
					$item['PersonAgeStr'] = $item['PersonAgeDays'].' '.ru_word_case('день','дня','дней', $item['PersonAgeDays']);
					break;
			}
		}
		return $response;
    }
	/**
	 *  Изменение статуса бригады СМП
	 */
	function setEmergencyTeamStatus( $data, $status='Свободна' ){
		$TeamStatusID='';
		if( empty($data['pmUser_id']) ) $data['pmUser_id'] = $_SESSION['pmuser_id'];
		if( empty($data['ARMType_id']) ) {
			$this->load->database();
			$this->load->model("User_model", "User_model");
			$m = $this->User_model->getARMList();
			$result = $this->User_model->getARMinDB(array('ARMType_Code'=>$m[$data['ARMType']]['Arm_id']));
			$data['ARMType_id'] = $result[0]['ARMType_id'];
		}

		$this->load->model("EmergencyTeam_model4E", "EmergencyTeam_model4E");
		// получим список возможных статусов
		$statuses = $this->EmergencyTeam_model4E->loadEmergencyTeamStatuses($data);
		foreach ($statuses as $n) {
			if(mb_strtolower($n['EmergencyTeamStatus_Name']) == mb_strtolower($status)){
				$TeamStatusID = (int)$n['EmergencyTeamStatus_id'];
				break;
			}
		}
		if($TeamStatusID){
			$data['EmergencyTeamStatus_id'] = $TeamStatusID;
			return $this->EmergencyTeam_model4E->setEmergencyTeamStatus($data);
		}else{
			return false;
		}
	}
	
	
	/**
	 * Проверка стандарта мед помощии
	 */
	function checkEmergencyStandart($data){

		//poliseIsOvertime - полиса нет или он просрочен
		
		$query = "
			select top 1
				df.DiagFinance_id,	
				df.DiagFinance_IsOms,
				person.poliseIsOvertime,
				CASE WHEN (df.DiagFinance_IsOms = 2 AND person.poliseIsOvertime = 2) THEN 817 ELSE 
					CASE WHEN (df.DiagFinance_IsOms = 2) THEN null ELSE 818 END
				END
				as EmergencyStandart_Code
			from
				v_DiagFinance df with (nolock)				
				left join YesNo as IsOms with (nolock) on IsOms.YesNo_id = df.DiagFinance_IsOms
				outer apply (
					select top 1
						pls.Polis_begDate,
						pls.Polis_endDate,
						ps.Polis_id,
						case when (ps.Polis_id IS NOT NULL) THEN
							case when (  ( (pls.Polis_begDate IS NULL) OR  (pls.Polis_begDate <= dbo.tzGetDate()) )
								AND ( (pls.Polis_endDate IS NULL) OR  (pls.Polis_endDate >= dbo.tzGetDate()) )  ) THEN 1 ELSE 2 END
							ELSE 2 END
						as poliseIsOvertime
					from
						v_PersonState as ps with (nolock)
						left join v_Polis as pls with (nolock) on pls.Polis_id = ps.Polis_id
					where ps.Person_id = :Person_id
				) person
			where
				df.Diag_id = :Diag_id
		";
		$queryParams = array(
			'Diag_id' => $data['Diag_id'],
			'Person_id' => $data['Person_id']
		);
		//var_dump(getDebugSQL($query, $queryParams)); exit;
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array('Error_Msg' => "Ошибка при выполнении запроса к базе данных");
		}

		return $result->result('array');
	}

	/**
	 * Печать справки СМП
	 * @param  [integer] $data [CmpCallCard_id]
	 */
	function printCmpCall($data)
	{
		$query = "SELECT * FROM [rpt2].[pan_Spravka_SMPCall] (:CmpCallCard_id)";

		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$result = $result->result('array');
			$result[0]['success'] = true;
			return $result;
		} else {
			return false;
		}
	}
	
	/**
	 *  Получение из Wialon пройденного расстояния бригадой за промежуток времени
	 * EmergencyTeam_id - id бригады
	 * GoTime, EndTime - промежуток времени (объект)
	 */
	public function getTheDistanceInATimeInterval( $data ){
		if ( empty($data['EmergencyTeam_id']) ) return false;

		$this->load->model("GeoserviceTransport_model", "GeoserviceTransport_model");
		// узнаем использует ли служба геосервис «Wialon»
		$geoservis = $this->GeoserviceTransport_model->getGeoserviceType();
		if( count($geoservis) == 0 || $geoservis[0]['ApiServiceType_Name'] != 'Wialon') {
			return false;
		}
		$this->load->model("EmergencyTeam_model4E", "EmergencyTeam_model4E");
		// получим Id транспорта бригады
		$emergencyTeamFields = $this->EmergencyTeam_model4E->loadEmergencyTeam(array('EmergencyTeam_id' => $data['EmergencyTeam_id']));
		$transportID = $emergencyTeamFields[0]['GeoserviceTransport_id'];
		//$transportID = 180;
		if( !$transportID ){
			return false;
		}
		
		$param = array(
			'tarnsportID' => $transportID,
			'GoTime' => DateTime::createFromFormat('d.m.Y H:i', $data['GoTime']),
			'EndTime' => DateTime::createFromFormat('d.m.Y H:i', $data['EndTime'])
		);

		$this->load->model('Wialon_model');
		try {
			$result = $this->Wialon_model->init();
			$result = $this->Wialon_model->getTheDistanceTraveled($param);
			if( $result ){
				return array(
					'success' => true,
					'data' => floatval($result)
				);
			}else{
				return false;
			}
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Получение всех подстанций СМП региона
	 */
	public function loadRegionSmpUnits($data) {
		$params = array('Region_id'=>$data['session']['region']['number']);
		$orderBy = '';
		if(isset($data['Lpu_id'])) {
			$orderBy = 'ORDER BY CASE WHEN LB.Lpu_id = :Lpu_id THEN 1 ELSE 2 END, LB.Lpu_id ASC';
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
		SELECT 
		       * 
		FROM(
			SELECT DISTINCT
				LB.LpuBuilding_id,
				LB.LpuBuilding_Name,
				LB.LpuBuilding_Code,
				LB.LpuBuilding_Nick,
				L.Lpu_Nick,
				L.Lpu_id
			FROM
				v_LpuBuilding LB with(nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = LB.Lpu_id
				left join v_SmpUnitParam sup with (nolock) ON LB.LpuBuilding_id = sup.LpuBuilding_id
				left join v_SmpUnitType sut with (nolock) ON sup.SmpUnitType_id = sut.SmpUnitType_id
			WHERE
				L.Region_id = :Region_id
				AND LB.LpuBuildingType_id = 27
		  		AND (sut.SmpUnitType_Code <>  4 or sut.SmpUnitType_Code is null )
			)LB			
			".$orderBy."
			";

		$result = $this->db->query($query,$params);
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
	}

	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 */
	public function autoCreateCmpPerson($data) {
		set_time_limit(0);

		try {
			//Попытка подключиться к основной БД. Если не удасться, то выполнение фунцкии прекратится.
			$mainDB = $this->load->database('main', true);

			$this->load->model('Person_model');

			$socstatus_Ids = array(
				"ufa" => 2, "buryatiya" => 10000083, "kareliya" => 51, "khak" => 32, "yaroslavl" => 10000266,
				"astra" => 10000053, "krasnoyarsk" => 10000173, "kaluga" => 231, "penza" => 224, "perm" => 2, "pskov" => 25,
				"saratov" => 10000035, "ekb" => 10000072, "msk" => 60, "krym" => 262, "kz" => 91, "by" => 201
			);

			$query = "
				select
					-- select
					CCC.CmpCallCard_id,
					IsUnknown.YesNo_Code as Person_IsUnknown,
					CCC.Person_SurName,
					CCC.Person_SecName,
					CCC.Person_FirName,
					convert(varchar(10), CCC.Person_BirthDay, 120) as Person_BirthDay,
					CCC.Sex_id
					-- end select
				from
					-- from
					v_CmpCallCard CCC with(nolock)
					left join v_YesNo IsUnknown with(nolock) on IsUnknown.YesNo_id = CCC.Person_IsUnknown
					-- end from
				WHERE
					-- where
					CCC.Person_id is null
					and CCC.Person_IsUnknown is not null
					-- end where
				order by
					-- order by
					CCC.CmpCallCard_id
					-- end order by
			";

			$limit = 200;
			$count = $this->getFirstResultFromQuery(getCountSQLPH($query));
			if ($count === false) {
				throw new Exception('Ошибка при получении данных пациентов из карт СМП');
			}

			for($start = 0; $start <= $count; $start += $limit) {
				$PersonData = $this->queryResult(getLimitSQLPH($query, $start, $limit));
				if (!is_array($PersonData)) {
					throw new Exception('Ошибка при получении данных пациентов из карт СМП');
				}

				foreach($PersonData as $person) {
					$resp = $this->Person_model->savePersonEditWindow(array(
						'Server_id' => $data['Server_id'],
						'NationalityStatus_IsTwoNation' => false,
						'Polis_CanAdded' => 0,
						'Person_SurName' => $person['Person_SurName'],
						'Person_FirName' => $person['Person_FirName'],
						'Person_SecName' => $person['Person_SecName'],
						'Person_BirthDay'=> $person['Person_BirthDay'],
						'Person_IsUnknown' => $person['Person_IsUnknown'],
						'PersonSex_id' => $person['Sex_id'],
						'SocStatus_id' => $socstatus_Ids[getRegionNick()],
						'session' => $data['session'],
						'mode' => 'add',
						'pmUser_id' =>  $data['pmUser_id'],
						'Person_id' => null,
						'Polis_begDate' => null
					));
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}

					$this->db->query("
						update CmpCallCard with(rowlock)
						set Person_id = :Person_id
						where CmpCallCard_id = :CmpCallCard_id
					", array(
						'Person_id' => $resp[0]['Person_id'],
						'CmpCallCard_id' => $person['CmpCallCard_id']
					));
				}
			}
		} catch(Exception $e) {
			return $this->createError($e->getCode(), $e->getMessage());
		}
		return array(array('success' => true));
	}
	
	/**
	 * получение списка полей в разделе Услуги для формы 110
	 * 
	 */
	public function getUslugaFields($data){

        $countSql = "";
        $counParam = "null as CmpCallCardUsluga_Kolvo,";
		$where = array();

        $params = array(
            'Lpu_id' => $data['Lpu_id'],
            'acceptTime' => date('Y-m-d H:i:s', strtotime($data["acceptTime"]))
        );

        if(!empty($data['CmpCallCard_id'])){
            $countSql = "outer apply (
                select top 1 cccu.CmpCallCardUsluga_Kolvo
					from v_CmpCallCardUsluga cccu (nolock)
					where CCCU.CmpCallCard_id = :CmpCallCard_id and uc.UslugaComplex_id = CCCU.UslugaComplex_id
				) kolvo";
			$counParam = "kolvo.CmpCallCardUsluga_Kolvo,";
            $params["CmpCallCard_id"] = $data['CmpCallCard_id'];
        }

		if(!empty($data['UslugaComplex_Code'])){
			$where[] = "uc.UslugaComplex_Code = :UslugaComplex_Code";
			$params['UslugaComplex_Code'] = $data['UslugaComplex_Code'];
		}

		if(!empty($data['Lpu_id'])){
			$where[] = "ISNULL(Lpu.AttributeValue_ValueIdent, :Lpu_id) = :Lpu_id";
		}

		$where[] = "avis.AttributeVision_TableName = 'dbo.VolumeType'";

		if (!empty($data['PayType_Code'])) {
			switch (+$data['PayType_Code']) {
				case 10: {
					$where[] = "vt.VolumeType_Code in ('UslugaSMP', 'УслугиСМП–МБТ(незастрахованные)')";
					break;
				}
				case 11: {
					$where[] = "vt.VolumeType_Code in ('UslugaSMP', 'УслугиСМП–МБТ(СЗЗ)')";
					break;
				}
				default: {
					$where[] = "vt.VolumeType_Code = 'UslugaSMP'";
					break;
				}
			}
		} else {
			$where[] = "vt.VolumeType_Code = 'UslugaSMP'";
		}

		$counParam .= 'vt.VolumeType_Code,';

		$where[] = "avis.AttributeVision_IsKeyValue = 2";
		$where[] = "ISNULL(av.AttributeValue_begDate, :acceptTime) <= :acceptTime";
		$where[] = "ISNULL(av.AttributeValue_endDate, :acceptTime) >= :acceptTime";
		$where[] = "ISNULL(uc.UslugaComplex_begDT, :acceptTime) <= :acceptTime";
		$where[] = "ISNULL(uc.UslugaComplex_endDT, :acceptTime) >= :acceptTime";
		$where[] = "COALESCE( uc.UslugaComplex_Nick, uc.UslugaComplex_Name, 'none') != 'none'";

		$sql = "
			select
				av.AttributeValue_id,
                Lpu.Attribute_id,
                av.AttributeValue_ValueIdent as UslugaComplex_id,
                COALESCE( uc.UslugaComplex_Nick, uc.UslugaComplex_Name, null) as UslugaComplex_Name,
				uc.UslugaCategory_id,
				uc.UslugaComplex_Code,
                Lpu.AttributeValue_ValueIdent as Lpu_id,
                {$counParam}
				case
					when a.AttributeValueType_id = 1 then cast(av.AttributeValue_ValueInt as varchar)
					when a.AttributeValueType_id = 2 then cast(av.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 3 then cast(av.AttributeValue_ValueFloat as varchar)
					when a.AttributeValueType_id = 4 then cast(av.AttributeValue_ValueBoolean as varchar)
					when a.AttributeValueType_id = 5 then cast(av.AttributeValue_ValueString as varchar)
					when a.AttributeValueType_id = 6 then cast(av.AttributeValue_ValueIdent as varchar)
					when a.AttributeValueType_id = 7 then convert(varchar(10), av.AttributeValue_ValueDate, 104)
					when a.AttributeValueType_id = 8 then cast(av.AttributeValue_ValueIdent as varchar)
				end as AttributeValue_Value,
				convert(varchar(10), av.AttributeValue_begDate, 104) as AttributeValue_begDate,
				convert(varchar(10), av.AttributeValue_endDate, 104) as AttributeValue_endDate,
				case
					when vt.VolumeType_Code = 'УслугиСМП–МБТ(незастрахованные)' then 10
					when vt.VolumeType_Code = 'УслугиСМП–МБТ(СЗЗ)' then 11
					else 1
				end as PayType_Code,
				av.AttributeValue_ValueText
			from
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
                left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = av.AttributeValue_ValueIdent
				left join v_VolumeType vt (nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
				outer apply (
					select top 1 t1.AttributeValue_ValueIdent, t1.Attribute_id
					from v_AttributeValue t1 (nolock)
						inner join v_Attribute t2 (nolock) on t2.Attribute_id = t1.Attribute_id
					where t1.AttributeValue_rid = av.AttributeValue_id
						and t2.Attribute_SysNick = 'Lpu'
				) Lpu
				{$countSql}
			where
				" . implode(' and ', $where) . "
			order by
				vt.VolumeType_Code desc,
				uc.UslugaComplex_Nick
		";
		


        //var_dump(getDebugSQL($sql, $params)); exit;

		$result = $this->db->query($sql, $params);
		
		if (!is_object($result)) {
			return false;
		} else {
			return $result->result('array');
		}
		
	}
	
	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 */
	function sendCmpCallCardToActiveMQ($data) {
		
		$params = array(		
			"id" => $data["CmpCallCard_id"],
			"rid" => !empty($data["CmpCallCard_rid"])?$data["CmpCallCard_rid"]:null,
			"numberYear" => !empty($data["CmpCallCard_Ngod"])?$data["CmpCallCard_Ngod"]:null,
			"number" => !empty($data["CmpCallCard_Numv"])?$data["CmpCallCard_Numv"]:null,
			"phone" => !empty($data["CmpCallCard_Telf"])?$data["CmpCallCard_Telf"]:null,
			"urgency" => !empty($data["CmpCallCard_Urgency"])?$data["CmpCallCard_Urgency"]:null,
			"comment" => !empty($data["CmpCallCard_Comm"])?$data["CmpCallCard_Comm"]:null,
			"callPlaceType" => array(
				"id" => !empty($data["CmpCallPlaceType_id"])?$data["CmpCallPlaceType_id"]:null
			),
			"callType" => array(
				"id" => !empty($data["CmpCallType_id"])?$data["CmpCallType_id"]:null
			),
			"whoCallType" => array(
				"id" => !empty($data["CmpCallerType_id"])?$data["CmpCallerType_id"]:null
			),
			"reason" => array(
				"id" => !empty($data["CmpReason_id"])?$data["CmpReason_id"]:null
			),
			"refusalReason" => array(
				"id" => !empty($data["CmpRejectionReason_id"])?$data["CmpRejectionReason_id"]:null
			),
			"region" => array(
				"id" => !empty($data["KLRgn_id"])?$data["KLRgn_id"]:null
			),
			"city" => array(
				"id" => !empty($data["KLCity_id"])?$data["KLCity_id"]:null
			),
			"street" => array(
				"id" => !empty($data["KLStreet_id"])?$data["KLStreet_id"]:null
			),
			"house" => !empty($data["CmpCallCard_Dom"])?$data["CmpCallCard_Dom"]:null,
			"corpus" => !empty($data["CmpCallCard_Korp"])?$data["CmpCallCard_Korp"]:null,
			"entrance" => !empty($data["CmpCallCard_Podz"])?$data["CmpCallCard_Podz"]:null,
			"entranceCode" => !empty($data["CmpCallCard_Kodp"])?$data["CmpCallCard_Kodp"]:null,
			"floor" => !empty($data["CmpCallCard_Etaj"])?$data["CmpCallCard_Etaj"]:null,
			"flat" => !empty($data["CmpCallCard_Kvar"])?$data["CmpCallCard_Kvar"]:null,
			"unformalizedAddress" => array(
				"id" => !empty($data["UnformalizedAddressDirectory_id"])?$data["UnformalizedAddressDirectory_id"]:null
			),
			"lat" => !empty($data["CmpCallCard_CallLtd"])?$data["CmpCallCard_CallLtd"]:null,
			"lon" => !empty($data["CmpCallCard_CallLng"])?$data["CmpCallCard_CallLng"]:null,
			"acceptTime" => !empty($data["CmpCallCard_prmDT"])?$data["CmpCallCard_prmDT"]:null,
			"transferTime" => !empty($data["CmpCallCard_Tper"])?$data["CmpCallCard_Tper"]:null,
			"departureTime" => !empty($data["CmpCallCard_Vyez"])?$data["CmpCallCard_Vyez"]:null,
			"arrivalTime" => !empty($data["CmpCallCard_Przd"])?$data["CmpCallCard_Przd"]:null,
			"transportTime" => !empty($data["CmpCallCard_Tgsp"])?$data["CmpCallCard_Tgsp"]:null,
			"hospitalArrivalTime" => !empty($data["CmpCallCard_HospitalizedTime"])?$data["CmpCallCard_HospitalizedTime"]:null,
			"returnTime" => !empty($data["CmpCallCard_Tvzv"])?$data["CmpCallCard_Tvzv"]:null,
			"endingTime" => !empty($data["CmpCallCard_Tisp"])?$data["CmpCallCard_Tisp"]:null,
			"fillDuration" => !empty($data["CmpCallCard_DiffTime"])?$data["CmpCallCard_DiffTime"]:null,
			"callDuration" => !empty($data["CmpCallCard_Dlit"])?$data["CmpCallCard_Dlit"]:null,
			"isEmergency" => !empty($data["CmpCallCard_IsExtra"])?$data["CmpCallCard_IsExtra"]:null,
			"isUrgent" => !empty($data["CmpCallCard_IsNMP"])?$data["CmpCallCard_IsNMP"]:null,
			"isActiveToPolyclinic" => !empty($data["CmpCallCard_IsPoli"])?$data["CmpCallCard_IsPoli"]:null,
			"lpu" => array(
				"id" => !empty($data["Lpu_id"])?$data["Lpu_id"]:null
			),
			"lpuBuilding" => array(
				"id" => !empty($data["LpuBuilding_id"])?$data["LpuBuilding_id"]:null
			),
			"emergencyTeam" => array(
				"id" => !empty($data["EmergencyTeam_id"])?$data["EmergencyTeam_id"]:null
			),
			"person" => array(
				"id" => !empty($data["Person_id"])?$data["Person_id"]:null,
				"birthday" => !empty($data["Person_Birthday"])?$data["Person_Birthday"]:null,
				"firname" => !empty($data["Person_FirName"])?$data["Person_FirName"]:null,
				"secname" => !empty($data["Person_SecName"])?$data["Person_SecName"]:null,
				"surname" => !empty($data["Person_SurName"])?$data["Person_SurName"]:null,
				"sex" => array(
					"id" => !empty($data["Sex_id"])?$data["Sex_id"]:null
				)
			),
			"age" => !empty($data["Person_Age"])?$data["Person_Age"]:null,
			"isOftenCaller" => !empty($data["Person_isOftenCaller"])?$data["Person_isOftenCaller"]:null,
			"diag" => array(
				"id" => !empty($data["Diag_uid"])?$data["Diag_uid"]:null
			),
			"lpuTransferUrgent" => array(
				"id" => !empty($data["Lpu_ppdid"])?$data["Lpu_ppdid"]:null
			),
			"status" => array(
				"id" => !empty($data["CmpCallCardStatusType_id"])?$data["CmpCallCardStatusType_id"]:null,
				"comment" => !empty($data["CmpCallCardStatus_Comment"])?$data["CmpCallCardStatus_Comment"]:null,
			),
			"armType" => array(
				"sysNick" => !empty($data["ARMType"])?$data["ARMType"]:null
			)
		);

		sendStompMQMessage($params, 'Rule', '/queue/ru.swan.emergency.urgentCallCard');
	}
	
	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 */
	function sendCmpCloseCardToActiveMQ($data) {
		
		$params = array(
			"id" => !empty($data["CmpCloseCard_id"])?$data["CmpCloseCard_id"]:null,
			"emergencyCallCard" => array(
				"id" => !empty($data["CmpCallCard_id"])?$data["CmpCallCard_id"]:null,
			),
			"lpu" => array(
				"id" => !empty($data["Lpu_id"])?$data["Lpu_id"]:null,
			),
			"lpuBuilding" => array(
				"id" => !empty($data["LpuBuilding_id"])?$data["LpuBuilding_id"]:null,
			),
			"number" => !empty($data["Day_num"])?$data["Day_num"]:null,
			"numberYear" => !empty($data["Year_num"])?$data["Year_num"]:null,
			"acceptTime" => !empty($data["AcceptTime"])?$data["AcceptTime"]:null,
			"transferTime" => !empty($data["TransTime"])?$data["TransTime"]:null,
			"departureTime" => !empty($data["GoTime"])?$data["GoTime"]:null,
			"arrivalTime" => !empty($data["ArriveTime"])?$data["ArriveTime"]:null,
			"transportTime" => !empty($data["TransportTime"])?$data["TransportTime"]:null,
			"hospitalArrivalTime" => !empty($data["ToHospitalTime"])?$data["ToHospitalTime"]:null,
			"returnTime" => !empty($data["BackTime"])?$data["BackTime"]:null,
			"endingTime" => !empty($data["EndTime"])?$data["EndTime"]:null,
			"totalTime" => !empty($data["SummTime"])?$data["SummTime"]:null,
			"kilometrage" => !empty($data["Kilo"])?$data["Kilo"]:null,
			"subRegion" => array(
				"id" => !empty($data["Area_id"])?$data["Area_id"]:null
			),
			"city" => array(
				"id" => !empty($data["City_id"])?$data["City_id"]:null
			),
			"town" => array(
				"id" => !empty($data["Town_id"])?$data["Town_id"]:null
			),
			"street" => array(
				"id" => !empty($data["Street_id"])?$data["Street_id"]:null
			),
			"house" => !empty($data["House"])?$data["House"]:null,
			"corpus" => !empty($data["Korpus"])?$data["Korpus"]:null,
			"floor" => !empty($data["Level"])?$data["Level"]:null,
			"entrance" => !empty($data["Entrance"])?$data["Entrance"]:null,
			"entranceCode" => !empty($data["CodeEntrance"])?$data["CodeEntrance"]:null,
			"flat" => !empty($data["Office"])?$data["Office"]:null,
			"room" => !empty($data["Room"])?$data["Room"]:null,
			"phone" => !empty($data["Phone"])?$data["Phone"]:null,
			"reason" => array(
				"id" => !empty($data["CallPovod_id"])?$data["CallPovod_id"]:null,
			),
			"callType" => array(
				"id" => !empty($data["CallType_id"])?$data["CallType_id"]:null,
			),
			"whoCallType" => array(
				"id" => !empty($data["CmpCallerType_id"])?$data["CmpCallerType_id"]:null,
			),
			"isEmergency" => !empty($data["CmpCloseCard_IsExtra"])?$data["CmpCloseCard_IsExtra"]:null,
			"acceptMedPersonal" => array(
				"id" => !empty($data["FeldsherAccept"])?$data["FeldsherAccept"]:null,
			),
			"transferMedPersonal" => array(
				"id" => !empty($data["FeldsherTrans"])?$data["FeldsherTrans"]:null,
			),
			"emergencyTeamNumber" => !empty($data["EmergencyTeamNum"])?$data["EmergencyTeamNum"]:null,
			"emergencyTeamSpec" => array(
				"id" => !empty($data["EmergencyTeamSpec_id"])?$data["EmergencyTeamSpec_id"]:null,
			),
			"emergencyTeam" => array(
				"id" => !empty($data["EmergencyTeam_id"])?$data["EmergencyTeam_id"]:null,
			),
			"medStaffFact" => array(
				"id" => !empty($data["MedStaffFact_id"])?$data["MedStaffFact_id"]:null,
			),
			"person" => array(
				"id" => !empty($data["Person_id"])?$data["Person_id"]:null,
			),
			"surname" => !empty($data["Fam"])?$data["Fam"]:null,
			"firname" => !empty($data["Name"])?$data["Name"]:null,
			"secname" => !empty($data["Middle"])?$data["Middle"]:null,
			"sex" => array(
				"id" => !empty($data["Sex_id"])?$data["Sex_id"]:null,
			),
			"polis" => array(
				"series" => !empty($data["Person_PolisSer"])?$data["Person_PolisSer"]:null,
			),				
			"number" => !empty($data["Person_PolisNum"])?$data["Person_PolisNum"]:null,
			"federalNumber" => !empty($data["CmpCloseCard_PolisEdNum"])?$data["CmpCloseCard_PolisEdNum"]:null,
			"age" => !empty($data["Age"])?$data["Age"]:null,
			"document" => !empty($data["DocumentNum"])?$data["DocumentNum"]:null,
			"job" => !empty($data["Work"])?$data["Work"]:null,
			"respiratoryRate" => !empty($data["Chd"])?$data["Chd"]:null,
			"cardiacRate" => !empty($data["Chss"])?$data["Chss"]:null,
			"feces" => !empty($data["Shit"])?$data["Shit"]:null,
			"temperature" => !empty($data["Temperature"])?$data["Temperature"]:null,
			"urination" => !empty($data["Urine"])?$data["Urine"]:null,
			"arterialPressure" => !empty($data["AD"])?$data["AD"]:null,
			"arterialPressureWork" => !empty($data["WorkAD"])?$data["WorkAD"]:null,
			"glucometry" => !empty($data["Gluck"])?$data["Gluck"]:null,
			"pulse" => !empty($data["Pulse"])?$data["Pulse"]:null,
			"pulseOximetry" => !empty($data["Pulsks"])?$data["Pulsks"]:null,
			"complaints" => !empty($data["Complaints"])?$data["Complaints"]:null,
			"otherSign" => !empty($data["OtherSympt"])?$data["OtherSympt"]:null,
			"additionalInfo" => !empty($data["CmpCloseCard_AddInfo"])?$data["CmpCloseCard_AddInfo"]:null,
			"anamnesis" => !empty($data["Anamnez"])?$data["Anamnez"]:null,
			"note" => !empty($data["DescText"])?$data["DescText"]:null,
			"localStatus" => !empty($data["LocalStatus"])?$data["LocalStatus"]:null,
			"arterialPressureEfficiency" => !empty($data["EfAD"])?$data["EfAD"]:null,
			"respiratoryRateEfficiency" => !empty($data["EfChd"])?$data["EfChd"]:null,
			"cardiacRateEfficiency" => !empty($data["EfChss"])?$data["EfChss"]:null,
			"glucometryEfficiency" => !empty($data["EfGluck"])?$data["EfGluck"]:null,
			"pulseEfficiency" => !empty($data["EfPulse"])?$data["EfPulse"]:null,
			"pulseOximetryEfficiency" => !empty($data["EfPulsks"])?$data["EfPulsks"]:null,
			"temperatureEfficiency" => !empty($data["EfTemperature"])?$data["EfTemperature"]:null,
			"ecgBefore" => !empty($data["Ekg1"])?$data["Ekg1"]:null,
			"ecgBeforeDate" => !empty($data["Ekg1Time"])?$data["Ekg1Time"]:null,
			"ecgAfter" => !empty($data["Ekg2"])?$data["Ekg2"]:null,
			"ecgAfterDate" => !empty($data["Ekg2Time"])?$data["Ekg2Time"]:null,
			"helpOnAuto" => !empty($data["HelpAuto"])?$data["HelpAuto"]:null,
			"helpOnPlace" => !empty($data["HelpPlace"])?$data["HelpPlace"]:null,
			"isAcrocyanosis" => !empty($data["isAcro"])?$data["isAcro"]:null,
			"isAlco" => !empty($data["isAlco"])?$data["isAlco"]:null,
			"isAnisocoria" => !empty($data["isAnis"])?$data["isAnis"]:null,
			"isBreathingProcessing" => !empty($data["isHale"])?$data["isHale"]:null,
			"isLight" => !empty($data["isLight"])?$data["isLight"]:null,
			"isMeningeal" => !empty($data["isMenen"])?$data["isMenen"]:null,
			"isMottledSkin" => !empty($data["isMramor"])?$data["isMramor"]:null,
			"isNystagmus" => !empty($data["isNist"])?$data["isNist"]:null,
			"isPeritoneumIrritation" => !empty($data["isPerit"])?$data["isPerit"]:null,
			"isNonHospitalization" => !empty($data["isOtkazHosp"])?$data["isOtkazHosp"]:null,
			"isNonMedicalCare" => !empty($data["isOtkazMed"])?$data["isOtkazMed"]:null,
			"isConsentToMedicalIntervention" => !empty($data["isSogl"])?$data["isSogl"]:null,
			"diag" => array(
				"id" => !empty($data["Diag_id"])?$data["Diag_id"]:null
			),
			"diagAccomp" => array(
				"id" => !empty($data["Diag_sid"])?$data["Diag_sid"]:null
			),
			"diagExact" => array(
				"id" => !empty($data["Diag_uid"])?$data["Diag_uid"]:null
			),
			"emergencyResult" => array(
				"id" => !empty($data["CmpResult_id"])?$data["CmpResult_id"]:null
			)
		);
		
		sendStompMQMessage($params, 'Rule', '/queue/ru.swan.emergency.emergencyCloseCard');
	}	
	
	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 */
	function sendStatusCmpCallCardToActiveMQ($data) {
		
		$params = array(
			"id" => !empty($data["CmpCallCard_id"])?$data["CmpCallCard_id"]:null,
			"status" => array(
				"id" => !empty($data["CmpCallCardStatusType_id"])?$data["CmpCallCardStatusType_id"]:null,
			),
			"comment" => !empty($data["CmpCallCardStatus_Comment"])?$data["CmpCallCardStatus_Comment"]:null,

			"reason" => array(
				"id" => !empty($data["CmpReason_id"])?$data["CmpReason_id"]:null
			),
			"isUrgent" => array(
				"id" => !empty($data["CmpCallCard_isNMP"])?$data["CmpCallCard_isNMP"]:null
			),			
			"isReceivedInPPD" => !empty($data["CmpCallCard_IsReceivedInPPD "])?$data["CmpCallCard_IsReceivedInPPD "]:null,
			"transferFromUrgentReason" => array(
				"id" => !empty($data["CmpMoveFromNmpReason_id"])?$data["CmpMoveFromNmpReason_id"]:null,
			),
			"returnToEmergencyReason" => array(
				"id" => !empty($data["CmpReturnToSmpReason_id"])?$data["CmpReturnToSmpReason_id"]:null,
			)
		);
		
		sendStompMQMessage($params, 'Rule', '/queue/ru.swan.emergency.urgentCallCard.status');
	}
	
	/**
	 * Автоматическое создание записей о пациентах СМП, которые не были созданы из-за отсутсвия связи с основным сервером.
	 * Должен запускаться по заданию на сервере СМП.
	 */
	function sendLpuTransmitToActiveMQ($data) {
		
		$params = array(
			"id" => !empty($data["CmpCallCard_id"])?$data["CmpCallCard_id"]:null,
			"lpuTransferUrgent" => array(
				"id" => !empty($data["Lpu_ppdid"])?$data["Lpu_ppdid"]:null
			)
		);
		
		sendStompMQMessage($params, 'Rule', '/queue/ru.swan.emergency.urgentCallCard.changeLpu');
	}

	/**
	 * Возвращает поля экспертной оценки для карты закрытия вызова 110у
	 */
	public function getExpertResponseFields() {

		$query = "
		SELECT
			CMPCloseCardExpertResponseType_id as ExpertResponseType_id,
     		CMPCloseCardExpertResponseType_Name as ExpertResponseType_Name
		FROM v_CMPCloseCardExpertResponseType
		";
		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		$response['ExpertResponseTypes'] = $result->result('array');

		$sql = "
			declare @curdate datetime = dbo.tzGetDate();

			SELECT
				av.AttributeValue_id,
				av.AttributeValue_ValueString as AttributeValue_Value,
				a.Attribute_id,
				a.Attribute_Code,
				avchild.AttributeValue_ValueString as AttributeValue_Text
			FROM
				v_AttributeVision avis (nolock)
				inner join v_AttributeValue av (nolock) on av.AttributeVision_id = avis.AttributeVision_id
				inner join v_Attribute a (nolock) on a.Attribute_id = av.Attribute_id
				left join v_VolumeType vt (nolock) on vt.VolumeType_id = avis.AttributeVision_TablePKey
				left join v_AttributeValue avchild (nolock) on avchild.AttributeValue_rid = av.AttributeValue_id
			WHERE
				avis.AttributeVision_TableName = 'dbo.VolumeType'
				and vt.VolumeType_Code = 'CMPCloseCardExpResp'
				and ISNULL(av.AttributeValue_endDate, @curdate) >= @curdate
				and avis.AttributeVision_IsKeyValue = 2
			ORDER BY av.AttributeValue_ValueString
		";


		$result = $this->db->query($sql);

		if (!is_object($result)) {
			return false;
		}
		$res = $result->result('array');

		//сгруппируем по атрибуту
		/*
		foreach ($res as $attr) {
			$response['Attributes'][$attr["Attribute_Code"]][] = $attr;
		}
		*/
		$response['Attributes'] = $res;

		return $response;

	}

	/**
	 * Сохранение экспертных оценок карты закрытия вызова 110у
	 */
	public function saveCmpCloseCardExpertResponseList($data){

		$rules = array(
			array( 'field' => 'CmpCloseCard_id' , 'label' => 'Идентификатор карты закрытия вызова СМП' , 'rules' => 'required' , 'type' => 'id' ) ,
			array( 'field' => 'ExpertResponseList' , 'label' => 'Список оценок' , 'rules' => '' , 'type' => 'array', 'default' => array() ) ,
			array( 'field' => 'pmUser_id' , 'rules' => 'required' , 'label' => 'Идентификатор пользователя' , 'type' => 'id' ) ,
		) ;

		$queryParams = $this->_checkInputData( $rules , $data , $err , true ) ;

		if ( !empty( $err ) )
			return $err ;

		foreach($queryParams['ExpertResponseList'] as $expertResponse){
			switch($expertResponse['action']){
				case 'add':
				case 'edit':
					if($expertResponse['value']){
						$expertResponse['CMPCloseCard_id'] = $queryParams['CmpCloseCard_id'];
						$expertResponse['pmUser_id'] = $queryParams['pmUser_id'];
						$saveResponse = $this->saveCmpCloseCardExpertResponse($expertResponse);
					}
				break;
				case 'del':
					$this->delCmpCloseCardExpertResponse($expertResponse);
				break;
			}
		}
	}

	/**
	 * Сохранение экспертной оценки
	 */
	function saveCmpCloseCardExpertResponse($data){

		$procedure = 'p_CMPCloseCardExpertResponse_ins';
		if (!empty($data['CMPCloseCardExpertResponse_id'])) {
			$procedure = 'p_CMPCloseCardExpertResponse_upd';
		}

		$genQuery = $this -> getParamsForSQLQuery($procedure, $data, false, false);
		$QueryParams = $genQuery["paramsArray"];
		$QueryFields = $genQuery["sqlParams"];



		$query = "
					declare
						@Res bigint,
						@ErrCode int,
						@ErrMessage varchar(4000)

					set @Res = null;

					exec {$this->schema}.{$procedure}
						{$QueryFields}
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;

					select @Res as CMPCloseCardExpertResponse_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
		$result = $this->db->query($query, $QueryParams);
		return $result->result('array');
	}

	/**
	 * Удаление экспертной оценки
	 */
	function delCmpCloseCardExpertResponse($data){

		if (empty($data['CMPCloseCardExpertResponse_id'])) {
			return $this->createError('', 'Не задан обязательный параметр: идентификатор оценки');
		}

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec {$this->schema}.p_CMPCloseCardExpertResponse_del
				@CMPCloseCardExpertResponse_id = :CMPCloseCardExpertResponse_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		return $this->queryResult($query, $data);

	}

	/**
	 * Возвращает оценки карты 110у
	 */
	public function getCmpCloseCardExpertResponses($data){

		if(empty($data['CmpCloseCard_id'])) return false;

		$sql = "
		SELECT
			CER.CMPCloseCardExpertResponse_id,
      		CER.AttributeValue_id,
      		CER.CMPCloseCardExpertResponseType_id,
      		CER.CMPCloseCardExpertResponse_Comment,
      		COALESCE(MP.Person_SurName, '') + ' ' +
      			COALESCE(case when rtrim(MP.Person_FirName) = 'null' then ' ' else SUBSTRING(MP.Person_FirName,1,1) + '.' end, '') +
      			COALESCE(case when rtrim(MP.Person_SecName) = 'null' then ' ' else SUBSTRING(MP.Person_SecName,1,1) + '.' end, '') as Person_FIO,
      		convert(varchar(10), CER.CMPCloseCardExpertResponse_insDT, 104) + ' ' + convert(varchar(5), CER.CMPCloseCardExpertResponse_insDT, 108) as ResponseDT
		FROM {$this->schema}.v_CMPCloseCardExpertResponse CER (nolock)
		left join v_pmUser pmU (nolock) on pmU.pmUser_id = CER.pmUser_updID
		left join v_Medpersonal MP (nolock) on pmU.pmUser_Medpersonal_id = MP.MedPersonal_id
		WHERE CER.CMPCloseCard_id = :CmpCloseCard_id
		";

		$result = $this->db->query($sql, $data);
		return $result->result('array');
	}

	/**
	 * Возвращает список федеральных результатов для карты 110у
	 */
	public function getFedLeaveTypeList(){

		$sql = "SELECT LeaveType_id,
			LeaveType_Name,
			LeaveType_Code
		FROM fed.v_LeaveType
		";
		//echo getDebugSQL($sql);exit;
		$result = $this->db->query($sql);
		return $result->result('array');
	}

	/**
	 * возвращает признак источника карты CmpCallCard
	 */
	public function getCallCardInputTypeCode($cmpCallCardID){

		$query = "
			select top 1
				CT.CmpCallCardInputType_Code
			from
				v_CmpCallCard CCC (nolock)
				left join v_CmpCallCardInputType CT (nolock) on CT.CmpCallCardInputType_id = CCC.CmpCallCardInputType_id
			where
				CmpCallCard_id = :CmpCallCard_id
				and CT.CmpCallCardInputType_Code is not null
		";
		$result = $this->queryResult($query, array(
			'CmpCallCard_id' => $cmpCallCardID
		));
		if(is_array($result) && count($result) > 0){
			return (int)$result[0]['CmpCallCardInputType_Code'];
		}else{
			return false;
		}
	}

	/**
	 * Передача сообщений в зависимости от параметра reactionType:
	 * finish - FinishReaction «Завершение реагирования ДДС»
	 * add - AddReaction «Добавление реагирования»
	 * Все параметры проверяем внутри метода checkSendReactionToActiveMQ
	 */
	private function sendReactionToActiveMQ($data, $reactionType){

		if(empty($data['CmpCallCard_id']) || empty($data['Card112_Guid'])) return false;

		if(!in_array($reactionType, array('add', 'finish'))) return false;

		switch($reactionType){
			case 'add':
				$paramsMQ = array(
					'id' => $data['CmpCallCard_id'],
					'guid' => $data['Card112_Guid'],
					'emergencyTeamStatus' => array(
						'id' => !empty($data['EmergencyTeamStatus_id']) ? $data['EmergencyTeamStatus_id'] : null,
						'name' => !empty($data['EmergencyTeamStatus_Name']) ? $data['EmergencyTeamStatus_Name'] : null,
						'actionType' => $data['actionType'],
						'remark' => $data['remark'],
					),
					'emergencyTeam' => array(
						'id' => !empty($data['EmergencyTeam_id']) ? $data['EmergencyTeam_id'] : null,
						'number' => !empty($data['EmergencyTeam_Num']) ? $data['EmergencyTeam_Num'] : ' ', //отправил пробел т.к поле обязательное
					),
					/*'lpuNmp' => array(
						'id' => !empty($data['Lpu_ppdid']) ? $data['Lpu_ppdid'] : null,
						'name' => !empty($data['LpuNmp_Nick']) ? $data['LpuNmp_Nick'] : null,
					),*/
					'insDate' => !empty($data['EmergencyTeamStatusHistory_insDT']) ? $data['EmergencyTeamStatusHistory_insDT'] : null,
					'operator' => $data['MedPersonal_TabCode']
				);
				break;
			case 'finish':
				$this->load->model( 'EmergencyTeam_model4E', 'ETModel' );
				$ETstatus_id = $this->ETModel->getEmergencyTeamStatusIdByCode(4);

				$paramsMQ = array(
					'id' => $data['CmpCallCard_id'],
					'guid' => $data['Card112_Guid'],
					'emergencyTeamStatus' => array(
						'id' => $ETstatus_id,
						'code' => 4,
						'name' => 'Конец обслуживания'
					),
					'emergencyTeam' => array(
						'id' => !empty($data['EmergencyTeam_id']) ? $data['EmergencyTeam_id'] : null,
						'number' => !empty($data['EmergencyTeam_Num']) ? $data['EmergencyTeam_Num'] : null,
					),
					'insDate' => !empty($data['EmergencyTeamStatusHistory_insDT']) ? $data['EmergencyTeamStatusHistory_insDT'] : null,
					'operator' => !empty($data['operator']) ? $data['operator'] : null
				);
				break;
		}


		if(defined('STOMPMQ_MESSAGE_DESTINATION_EMERGENCY')){
			sendStompMQMessageOld($paramsMQ, 'Rule', STOMPMQ_MESSAGE_DESTINATION_EMERGENCY);
		}

	}

	/**
	 * Отправка реагирования в зависимости от события вызова
	 */
	public function checkSendReactionToActiveMQ($data){

		$related112Call = $this->checkRelated112Call($data);

		if(empty($related112Call['Card112_id']) && empty($related112Call['secondCard112_id'])) return false;

		if(empty($data['EmergencyTeamStatus_id']) && isset($data['EmergencyTeamStatus_Code'])){
			$this->load->model( 'EmergencyTeam_model4E', 'ETModel' );
			$data['EmergencyTeamStatus_id'] = $this->ETModel->getEmergencyTeamStatusIdByCode($data['EmergencyTeamStatus_Code']);
		}
		//События смены статуса бригады
		if(!empty($data['EmergencyTeamStatus_id']) && !empty($data['EmergencyTeam_id'])){
			$resp_ets = $this->queryResult("
						select top 1
							ets.EmergencyTeamStatus_id,
							ets.EmergencyTeamStatus_Code,
							ets.EmergencyTeamStatus_Name,
							et.EmergencyTeam_id,
							et.EmergencyTeam_Num,
							convert(varchar(19), dbo.tzGetDate(), 126) as EmergencyTeamStatusHistory_insDT,
							mp.MedPersonal_TabCode
						from
							v_EmergencyTeamStatus ets (nolock)
							inner join v_CmpCallCard ccc (nolock) on ccc.CmpCallCard_id = :CmpCallCard_id
							left join v_EmergencyTeam et (nolock) on et.EmergencyTeam_id = :EmergencyTeam_id
							left join v_pmUserCache puc (nolock) on puc.pmUser_id = :pmUser_id
							left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = puc.MedPersonal_id
							outer apply (
								select
									ISNULL(MP_HS.Person_Fin+', ', '') +
									ISNULL(MP_A1.Person_Fin+', ', '') +
									ISNULL(MP_A2.Person_Fin+', ', '') +
									ISNULL(MP_D.Person_Fin+', ', '') as Membership
								from v_MedPersonal MP_HS (nolock)
								left join v_MedPersonal MP_A1 with (nolock) on ET.EmergencyTeam_Assistant1 = MP_A1.MedPersonal_id
								left join v_MedPersonal MP_A2 with (nolock) on ET.EmergencyTeam_Assistant2 = MP_A2.MedPersonal_id
								left join v_MedPersonal MP_D with (nolock) on ET.EmergencyTeam_Driver = MP_D.MedPersonal_id
								where ET.EmergencyTeam_HeadShift = MP_HS.MedPersonal_id
							) mbs
						where
							ets.EmergencyTeamStatus_id = :EmergencyTeamStatus_id
					", array(
				'EmergencyTeamStatus_id' => $data['EmergencyTeamStatus_id'],
				'EmergencyTeam_id' => $data['EmergencyTeam_id'],
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'pmUser_id' => $_SESSION['pmuser_id']
			));

			if (!empty($resp_ets[0])) {
				switch ($resp_ets[0]['EmergencyTeamStatus_Code']) {
					case 36:
						$ActionType = 'Notification';
						$Remark = 'Назначена бригада';
						$reactionType = 'add';
						break;
					case 1:
						$ActionType = 'Departure';
						$Remark = 'Выезд бригады на вызов';
						$reactionType = 'add';
						break;
					case 2:
						$ActionType = 'Arrival';
						$Remark = 'Прибытие бригады на вызов';
						$reactionType = 'add';
						break;
					case 4:
						if ( isset($related112Call['secondCardStatus_Code']) && !in_array($related112Call['secondCardStatus_Code'], array(4, 5, 6, 9)) ) { //если нет связанного с Карточкой 112 вызова который еще НЕ обслужен.
							$ActionType = 'Solution';
							$Remark = 'Продолжение обслуживания вызова спецбригадой';
							$reactionType = 'add';
						}else{
							//в таком случае отправляем FinishReaction
							$reactionParams = array(
								'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
								'EmergencyTeam_id' => $data['EmergencyTeam_id'],
								'EmergencyTeam_Num' => $resp_ets[0]['EmergencyTeam_Num'],
								'operator' => $resp_ets[0]['MedPersonal_TabCode'],
								'EmergencyTeamStatusHistory_insDT' => $resp_ets[0]['EmergencyTeamStatusHistory_insDT'],
								'CmpCallCard_id' => $data['CmpCallCard_id'],
							);
							$reactionType = 'finish';
						}
						break;

				}
				if(isset($ActionType) && isset($Remark)){
					$reactionParams = array(
						'CmpCallCard_id' => $data['CmpCallCard_id'],
						'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
						'EmergencyTeamStatus_id' => $resp_ets[0]['EmergencyTeamStatus_id'],
						'EmergencyTeamStatus_Name' => $resp_ets[0]['EmergencyTeamStatus_Name'],
						'actionType' => $ActionType,
						'remark' => $Remark,
						'EmergencyTeam_id' => $resp_ets[0]['EmergencyTeam_id'],
						'EmergencyTeam_Num' => $resp_ets[0]['EmergencyTeam_Num'],
						'EmergencyTeamStatusHistory_insDT' => $resp_ets[0]['EmergencyTeamStatusHistory_insDT'],
						'MedPersonal_TabCode' => $resp_ets[0]['MedPersonal_TabCode']
					);
				}
			}
		} //События смены статуса вызова
		else {
			$resp_ets = $this->queryResult("
						select top 1
							ccc.Lpu_ppdid,
							ccc.EmergencyTeam_id,
							st.CmpCallCardStatusType_Code,
							ct.CmpCallType_Code,
							l.lpu_Nick as lpuNmp_Nick,
							mp.MedPersonal_TabCode,
							convert(varchar(19), dbo.tzGetDate(), 126) as EmergencyTeamStatusHistory_insDT
						from
							v_CmpCallCard ccc (nolock)
							left join v_CmpCallCardStatusType st (nolock) on st.CmpCallCardStatusType_id = ccc.CmpCallCardStatusType_id
							left join v_CmpCallType ct (nolock) on ct.CmpCallType_id = ccc.CmpCallType_id
							left join v_Lpu l (nolock) on l.Lpu_id = ccc.lpu_ppdid
							left join v_pmUserCache puc (nolock) on puc.pmUser_id = :pmUser_id
							left join v_MedPersonal mp (nolock) on mp.MedPersonal_id = puc.MedPersonal_id
						where
							ccc.CmpCallCard_id = :CmpCallCard_id
					", array(
				'CmpCallCard_id' => $data['CmpCallCard_id'],
				'pmUser_id' => $_SESSION['pmuser_id']
			));

			$this->load->model("Options_model", "opmodel");
			$o = $this->opmodel->getOptionsGlobals(getSessionParams());
			$g_options = $o['globals'];
			//События смены статуса вызова в НМП
			if (!empty($resp_ets[0]['Lpu_ppdid']) && empty($resp_ets[0]['EmergencyTeam_id'])) {
				switch ($resp_ets[0]['CmpCallCardStatusType_Code']) {
					case 1:
						$ActionType = (!empty($g_options['smp_default_system112']) && $g_options['smp_default_system112'] == 2) ? 'Departure' : 'Notification';
						$Remark = 'Передан на обслуживание в службу НМП';
						$reactionType = 'add';
						break;
					case 2:
						$ActionType = (!empty($g_options['smp_default_system112']) && $g_options['smp_default_system112'] == 2) ? 'Arrival' : 'Solution';
						$Remark = 'Подтвержен прием вызова службой НМП».';
						$reactionType = 'add';
						break;
					case 4:
						$reactionParams = array(
							'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
							'operator' => $resp_ets[0]['MedPersonal_TabCode'],
							'CmpCallCard_id' => $data['CmpCallCard_id'],
						);
						$reactionType = 'finish';
						break;

				}
				if (isset($ActionType) && isset($Remark)) {
					$reactionParams = array(
						'CmpCallCard_id' => $data['CmpCallCard_id'],
						'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
						'actionType' => $ActionType,
						'remark' => $Remark,
						'lpuNmp_Nick' => $resp_ets[0]['lpuNmp_Nick'],
						'Lpu_ppdid' => $resp_ets[0]['Lpu_ppdid'],
						'MedPersonal_TabCode' => $resp_ets[0]['MedPersonal_TabCode'],
						'EmergencyTeamStatusHistory_insDT' => $resp_ets[0]['EmergencyTeamStatusHistory_insDT'] //отправим текущую дату т.к поле обязательное
					);

				}
			}
			//Общие события с вызовом
			switch ($resp_ets[0]['CmpCallCardStatusType_Code']) {
				case 1:
					//только для "ЕДДС-ПРОТЕЙ"
					if(!empty($g_options['smp_default_system112']) && $g_options['smp_default_system112'] == 2){

						$reactionParams = array(
							'CmpCallCard_id' => $data['CmpCallCard_id'],
							'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
							'actionType' => 'Notification',
							'remark' => 'Передан из 112',
							'MedPersonal_TabCode' => $resp_ets[0]['MedPersonal_TabCode'],
							'EmergencyTeamStatusHistory_insDT' => $resp_ets[0]['EmergencyTeamStatusHistory_insDT'] //отправим текущую дату т.к поле обязательное
						);
						$reactionType = 'add';
					}

					//для астрахани при сохранении передаем
					if(!empty($g_options['smp_default_system112']) && $g_options['smp_default_system112'] == 3){
						$reactionParams = array(
							'CmpCallCard_id' => $data['CmpCallCard_id'],
							'actionType' => 'Notification',
							'remark' => 'Передан из 112',
							'MedPersonal_TabCode' => $resp_ets[0]['MedPersonal_TabCode'],
							'EmergencyTeamStatusHistory_insDT' => $resp_ets[0]['EmergencyTeamStatusHistory_insDT'],
							'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid']
							);
						$reactionType = 'add';
					}

					break;
				case 6:
					//Вызов закрыт с типом Справка/Консультативный/Абонент отключился
					if (!in_array($resp_ets[0]['CmpCallType_Code'], array(6, 15, 16, 17))) {
						break;
					}
				case 5:
					//Отказ от вызова
					$reactionParams = array(
						'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
						'operator' => $resp_ets[0]['MedPersonal_TabCode'],
						'CmpCallCard_id' => $data['CmpCallCard_id'],
					);
					$reactionType = 'finish';
					break;
				case 9:
					//При дубле необходимо отправить реагирование на последнее событие первичного вызова

					//Получим последнее событие первичного
					$sql = "
					SELECT top 1
						C.CmpCallCard_id,
						ETSH.EmergencyTeamStatus_id,
						ETSH.EmergencyTeam_id
					FROM v_CmpCallCard C (nolock)
					inner join v_CmpCallCard rC (nolock) on rC.CmpCallCard_rid = C.CmpCallCard_id
					left join v_CmpCallCardEvent CE (nolock) on C.CmpCallCard_id = CE.CmpCallCard_id
					left join v_EmergencyTeamStatusHistory ETSH (nolock) on CE.EmergencyTeamStatusHistory_id = ETSH.EmergencyTeamStatusHistory_id

					WHERE rC.CmpCallCard_id = :CmpCallCard_id
					ORDER BY CE.CmpCallCardEvent_updDT desc
					";
					$event =  $this->queryResult($sql, array(
						'CmpCallCard_id' => $data['CmpCallCard_id']
					));

					if(count($event) > 0){
						//Вызовем метод проверки отправки реагирования на последнее событие
						$this->checkSendReactionToActiveMQ(array('CmpCallCard_id' => $event[0]['CmpCallCard_id'], 'EmergencyTeamStatus_id' => $event[0]['EmergencyTeamStatus_id'], 'EmergencyTeam_id' => $event[0]['EmergencyTeam_id'], 'duplicate' => $data['CmpCallCard_id']));
					}

				break;

			}

			//Отдельное условие для отклонения бригады
			if(!empty($data['resetTeam']) && $data['resetTeam'] === true){

				//Название бригады
				$team = $this->getFirstRowFromQuery("SELECT EmergencyTeam_id,EmergencyTeam_Num FROM v_EmergencyTeam (nolock) WHERE EmergencyTeam_id = :EmergencyTeam_id", $data);

				//статус Свободно
				$status = $this->getFirstRowFromQuery("SELECT EmergencyTeamStatus_id,EmergencyTeamStatus_Name FROM v_EmergencyTeamStatus (nolock) WHERE EmergencyTeamStatus_Code = 13");

				$reactionParams = array(
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'Card112_Guid' => $related112Call['Card112_Guid'] ? $related112Call['Card112_Guid'] : $related112Call['secondCard112_Guid'],
					'EmergencyTeamStatus_id' => $status['EmergencyTeamStatus_id'],
					'EmergencyTeamStatus_Name' => $status['EmergencyTeamStatus_Name'],
					'actionType' => 'Solution',
					'remark' => 'Отклонение бригады с вызова',
					'EmergencyTeam_id' => $team['EmergencyTeam_id'],
					'EmergencyTeam_Num' => $team['EmergencyTeam_Num'],
					'MedPersonal_TabCode' => $resp_ets[0]['MedPersonal_TabCode'],
					'EmergencyTeamStatusHistory_insDT' => $resp_ets[0]['EmergencyTeamStatusHistory_insDT'] //отправим текущую дату т.к поле обязательное
				);
				$reactionType = 'add';
			}
		}

		if(!isset($reactionParams) || !isset($reactionType)) return false;


		//Фильтр на случай рекурсивного вызова метода для конкретного дубля (не отправляем реагирование повторно для других дублей)
		$dupFilter = "";
		if(isset($data['duplicate'])){
			$dupFilter = " and C.CmpCallCard_id = :CmpCallCard_id";
		}

		//Проверим на количество дублирующих (и отменяющих) вызовов из 112
		$sql = "
		SELECT
			C112.CmpCallCard112_Guid as Card112_Guid
		FROM v_CmpCallCard C (nolock)
		LEFT JOIN v_CmpCallCard112 C112 (nolock) on C112.CmpCallCard_id = C.CmpCallCard_id
		LEFT JOIN v_CmpCallCardStatusType CST (nolock) on CST.CmpCallCardStatusType_id = C.CmpCallCardStatusType_id
		WHERE C.CmpCallCard_rid = :CmpCallCard_rid and C112.CmpCallCard112_id is not null and CST.CmpCallCardStatusType_Code <> 10
		{$dupFilter}
		";

		$Duplicates =  $this->queryResult($sql, array(
			'CmpCallCard_rid' => $data['CmpCallCard_id'],
			'CmpCallCard_id' => isset($data['duplicate']) ? $data['duplicate'] : null,
		));

		//Если есть дубли то отправляем реагирование для каждого
		if (count($Duplicates) > 0) {
			foreach ($Duplicates as $dup) {
				$reactionParams['Card112_Guid'] = $dup['Card112_Guid'];
				$this->sendReactionToActiveMQ($reactionParams, $reactionType);
			}

			//Если первичный тоже из 112 (но не отправляем повторно при регистрации дубля)
			if (!empty($related112Call['Card112_Guid']) && !isset($data['duplicate'])) {
				$reactionParams['Card112_Guid'] = $related112Call['Card112_Guid'];
				$this->sendReactionToActiveMQ($reactionParams, $reactionType);
			}
		} else {

			$this->sendReactionToActiveMQ($reactionParams, $reactionType);
			//В случае когда и первичный и потомок из 112 отправляем второе реагирование с guid потомка
			if (!empty($related112Call['Card112_Guid']) && !empty($related112Call['secondCard112_Guid']) && $related112Call['secondCardStatus_Code'] != 10) {
				$reactionParams['Card112_Guid'] = $related112Call['secondCard112_Guid'];
				$this->sendReactionToActiveMQ($reactionParams, $reactionType);
			}
		}

	}

	/**
	 * Проверка вызова на связь с карточкой 112
	 */
	public function checkRelated112Call($data){
		if(empty($data['CmpCallCard_id'])){
			return false;
		}

		$sql = "
		SELECT
			C112.CmpCallCard112_id as Card112_id,
			C112.CmpCallCard112_Guid as Card112_Guid,

			COALESCE(rC112.CmpCallCard112_id, pC112.CmpCallCard112_id) as secondCard112_id,
			COALESCE(rC112.CmpCallCard112_Guid, pC112.CmpCallCard112_Guid) as secondCard112_Guid,
			COALESCE(rCST.CmpCallCardStatusType_Code, pCST.CmpCallCardStatusType_Code) as secondCardStatus_Code

			,rC.CmpCallCard_id as rCmpCallCard_id
			,pC.CmpCallCard_id as pCmpCallCard_id

		FROM v_CmpCallCard C (nolock)
		LEFT JOIN v_CmpCallType CCT (nolock) on CCT.CmpCallType_id = C.CmpCallType_id
		LEFT JOIN v_CmpCallCard112 C112 (nolock) on C112.CmpCallCard_id = C.CmpCallCard_id
		LEFT JOIN v_CmpCallCard rC (nolock) on (rC.CmpCallCard_id = C.CmpCallCard_rid and CCT.CmpCallType_Code in (1, 9) and C112.CmpCallCard_id is null) -- на первичный не должно уходить реагирование если текущий из 112
		LEFT JOIN v_CmpCallCard112 rC112 (nolock) on rC112.CmpCallCard_id = rC.CmpCallCard_id
		LEFT JOIN v_CmpCallCard pC (nolock) on pC.CmpCallCard_rid = C.CmpCallCard_id
		LEFT JOIN v_CmpCallCard112 pC112 (nolock) on pC112.CmpCallCard_id = pC.CmpCallCard_id


		LEFT JOIN v_CmpCallCardStatusType CST (nolock) on CST.CmpCallCardStatusType_id = C.CmpCallCardStatusType_id --статус текущего
		LEFT JOIN v_CmpCallCardStatusType rCST (nolock) on rCST.CmpCallCardStatusType_id = rC.CmpCallCardStatusType_id -- статус первичного
		LEFT JOIN v_CmpCallCardStatusType pCST (nolock) on pCST.CmpCallCardStatusType_id = pC.CmpCallCardStatusType_id -- статус потомка

		LEFT JOIN v_CmpCallType pCCT (nolock) on pCCT.CmpCallType_id = pC.CmpCallType_id -- тип вызова потомка
		WHERE C.CmpCallCard_id = :CmpCallCard_id and (C112.CmpCallCard112_id is not null or (pC112.CmpCallCard_id is not null and pCST.CmpCallCardStatusType_Code is null) or (pCST.CmpCallCardStatusType_Code <> 10 or pCCT.CmpCallType_Code = 17) or rC112.CmpCallCard112_id is not null)
		";
		return $this->getFirstRowFromQuery($sql, array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

	}

	/**
	 * список пациентов для журнала расхождения
	 */
	public function getPatientDiffList($data){

		$filter = '';
		$queryParams = array();
		$queryParams['Lpu_id'] = $data['Lpu_id'];
		
		if ( !empty($data['begDate']) ) {
			$filter .= " and cast(C.CmpCallCard_prmDT as date) >= :begDate";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( !empty($data['endDate']) ) {
			$filter .= " and cast(C.CmpCallCard_prmDT as date) <= :endDate";
			$queryParams['endDate'] = $data['endDate'];
		}

		$sql = "
			select
				C.CmpCallCard_id,
				EPS.Person_id,
				EPS.PersonEvn_id,
				EPS.Server_id,
				CC.CmpCloseCard_id,
				convert(varchar,C.CmpCallCard_prmDT,104) + ' ' +  SUBSTRING(convert(varchar,C.CmpCallCard_prmDT,108), 1, 5) as CmpCallCard_prmDT,
				C.CmpCallCard_Ngod,
				isnull(MP.Person_SurName + ' ', '') + isnull(MP.Person_FirName + ' ', '') + isnull(MP.Person_SecName,'') as CmpCallCard_Dspp,
				isnull(PSv.Person_SurName + ' ', '') + isnull(PSv.Person_FirName + ' ', '') + isnull(PSv.Person_SecName,'') as Person_Fio_v,
				Lpu.Lpu_Nick,
				isnull(PS.Person_SurName + ' ', '') + isnull(PS.Person_FirName + ' ', '') + isnull(PS.Person_SecName,'') as Person_Fio
			from v_CmpCallCard C (nolock)
			left join v_CmpCloseCard CC (nolock) on CC.CmpCallCard_id = C.CmpCallCard_id
			left join v_MedPersonal MP (nolock) on MP.MedPersonal_id = C.MedPersonal_id
			inner join v_PersonState PSv (nolock) on PSv.Person_id = C.Person_id
			inner join v_EvnPS EPS with(nolock) on EPS.CmpCallCard_id = C.CmpCallCard_id
			inner join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = EPS.Lpu_id
			inner join v_PersonState PS (nolock) on PS.Person_id = EPS.Person_id
			where 
				C.Person_id != EPS.Person_id and
				C.Lpu_id = :Lpu_id
				{$filter}
		";
		
		return $this->queryResult($sql, $queryParams);
	}
	
	/**
	 * Проверка оплаты диагноза по ОМС
	 */
	function checkDiagFinance($data){
		//if(empty($data['Person_id']) || empty($data['Diag_id'])) return false;
		if(empty($data['Diag_id'])) return false;
		if(isset($data['Person_id'])){
			$query = "
			declare @history_date datetime = dbo.tzGetDate();
				select top 1
					IsOms.YesNo_Code as DiagFinance_IsOms,
					IsAlien.YesNo_Code as DiagFinance_IsAlien,
					df.Sex_id as Diag_Sex,
					a.PersonAgeGroup_Code as DiagFinanceAgeGroup_Code,
					case when dbo.Age2(p.Person_BirthDay, dbo.tzGetDate()) < 18 then 2 else 1 end as PersonAgeGroup_Code,
					p.Sex_id,
					dbo.Age2(p.Person_BirthDay, @history_date) as Age
				from
					v_DiagFinance df with (nolock)
					left join PersonAgeGroup a with (nolock) on a.PersonAgeGroup_id = df.PersonAgeGroup_id
					left join YesNo IsAlien with (nolock) on IsAlien.YesNo_id = df.DiagFinance_IsAlien
					left join YesNo IsOms with (nolock) on IsOms.YesNo_id = df.DiagFinance_IsOms
					outer apply (
						select top 1
							ost.OmsSprTerr_Code,
							ps.Sex_id,
							ps.Person_BirthDay
						from
							[v_PersonState] [ps] with (nolock)
							left join [v_Polis] pls with (nolock) on [pls].[Polis_id] = [ps].[Polis_id]
							left join [v_OmsSprTerr] ost with (nolock) on [ost].[OmsSprTerr_id] = [pls].[OmsSprTerr_id]
						where ps.Person_id = :Person_id
					) p
				where
					df.Diag_id = :Diag_id
			";
			$queryParams = array(
				'Diag_id' => $data['Diag_id'],
				'Person_id' => $data['Person_id']
			);
		}elseif (isset($data['Age']) && isset($data['Sex_id'])) {
			$query = "
				select top 1
					IsOms.YesNo_Code as DiagFinance_IsOms,
					IsAlien.YesNo_Code as DiagFinance_IsAlien,
					df.Sex_id as Diag_Sex,
					a.PersonAgeGroup_Code as DiagFinanceAgeGroup_Code,
					case when '".$data['Age']."' < 18 then 2 else 1 end as PersonAgeGroup_Code,
					'".$data['Sex_id']."' as Sex_id,
					'".$data['Age']."' as Age
				from
					v_DiagFinance df with (nolock)
					left join PersonAgeGroup a with (nolock) on a.PersonAgeGroup_id = df.PersonAgeGroup_id
					left join YesNo IsAlien with (nolock) on IsAlien.YesNo_id = df.DiagFinance_IsAlien
					left join YesNo IsOms with (nolock) on IsOms.YesNo_id = df.DiagFinance_IsOms
				where
					df.Diag_id = :Diag_id
			";
			$queryParams = array(
				'Diag_id' => $data['Diag_id']
			);
		}else{
			return false;
		}
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array('Error_Msg' => "Ошибка при выполнении запроса к базе данных");
		}

		return $result->result('array');
	}

	/**
	 * для тестов отправки запросов в ActiveMQ
	 */
	function testAM() {
		$rand = rand(1,3);
		switch($rand) {
			case 1:
				sendStompMQMessage(array(
					'type' => 'insert', // тип (insert/update/delete)
					'table' => 'tmp._ck', // таблица
					'params' => array( // список полей и значений
						'CureStandart_id' => 2,
						'id' => 1,
						'DiagFedMes_FileName' => 1,
						'Duration' => 1
					),
					'keyParam' => null // для инсерта не нужно
				), 'Rule', '/queue/ru.swan.emergency.tomaindb');
				break;
			case 2:
				sendStompMQMessage(array(
					'type' => 'update', // тип (insert/update/delete)
					'table' => 'tmp._ck', // таблица
					'params' => array( // список полей и значений
						'CureStandart_id' => 2,
						'id' => 1,
						'DiagFedMes_FileName' => 3,
						'Duration' => 1
					),
					'keyParam' => 'CureStandart_id' // имя поля для апдейта
				), 'Rule', '/queue/ru.swan.emergency.tomaindb');
				break;
			case 3:
				sendStompMQMessage(array(
					'type' => 'delete', // тип (insert/update/delete)
					'table' => 'tmp._ck', // таблица
					'params' => array( // список полей и значений
						'CureStandart_id' => 2
					),
					'keyParam' => 'CureStandart_id' // имя поля для удаления
				), 'Rule', '/queue/ru.swan.emergency.tomaindb');
				break;
		}
	}
	/**
	 * Получение информации о использовании медикаментов CМП (простой учет)
	 */
	function loadCmpCallCardSimpleDrugList($data) {

		$params = array('CmpCallCard_id' => $data['CmpCallCard_id']);

		$query = "
			select
                cccd.CmpCallCardDrug_id,
                cccd.CmpCallCard_id,
                cccd.CmpCallCardDrug_Comment,
                convert(varchar, cccd.CmpCallCardDrug_setDate, 104) as CmpCallCardDrug_setDate,
                convert(varchar(5), cccd.CmpCallCardDrug_setTime, 108) as CmpCallCardDrug_setTime,
                cccd.Drug_id,
                cccd.CmpCallCardDrug_Kolvo,
                cccd.GoodsUnit_id,
                gu.GoodsUnit_Name,
                du.Lpu_id,
                cccd.DrugNomen_id,
                cccd.MedStaffFact_id,
		        dn.DrugNomen_Code,
		        dn.DrugNomen_Name
			from
				v_CmpCallCardDrug cccd with(nolock)
				left join rls.v_Drug d with (nolock) on d.Drug_id = cccd.Drug_id			
				left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = cccd.GoodsUnit_id
				left join v_DocumentUc du with (nolock) on du.DocumentUc_id = cccd.DocumentUc_id
                outer apply (
                    select top 1
                        i_dn.DrugNomen_Code,
                        i_dn.DrugNomen_Name,
                        i_dn.DrugNomen_id
                    from
                        rls.v_DrugNomen i_dn with (nolock)
                    where
                        i_dn.DrugNomen_id = cccd.DrugNomen_id
                    order by
                        i_dn.DrugNomen_Code desc
                ) dn
			where
				cccd.CmpCallCard_id = :CmpCallCard_id;
		";

		$response = $this->queryResult($query, $params);
		return $response;
	}
	/**
	 * Сохранение спецификации из JSON
	 */
	function saveCmpCallCardSimpleDrugFromJSON($data) {

		$result = array();
		$error = array();
		if (!empty($data['json_str']) && $data['CmpCallCard_id'] > 0) {
			ConvertFromWin1251ToUTF8($data['json_str']);
			$dt = (array) json_decode($data['json_str']);
			foreach($dt as $record) {
				if(isset($record->CmpCallCardDrug_id) && $record->CmpCallCardDrug_id > 0) {
					$res = $this->saveCmpCallCardSimpleOneDrugFromJSON($record, $data);
					if(!$res['success'])
						$error = $res['error'];
					unset($data['useSMP']);
					unset($data['EvnDrug_id']);
				}
			}
		}
		if (count($error) > 0) {
			$result['success'] = false;
			$result['Error_Msg'] = $error[0];
		} else {
			$result['success'] = true;
		}

		return array($result);
	}

	/**
	 * Сохранение спецификации из JSON
	 */
	function saveCmpCallCardSimpleOneDrugFromJSON($record, $data) {

		$res['success'] = true;
		switch($record->state) {
			case 'add':
			case 'edit':
				$response = $this->saveObject('CmpCallCardDrug', array(
					'CmpCallCardDrug_id' => $record->state == 'add' ? null : $record->CmpCallCardDrug_id,
					'CmpCallCard_id' => $data['CmpCallCard_id'],
					'CmpCallCardDrug_setDate' => !empty($record->CmpCallCardDrug_setDate) ? $this->formatDate($record->CmpCallCardDrug_setDate) : null,
					'CmpCallCardDrug_setTime' => !empty($record->CmpCallCardDrug_setTime) ? $record->CmpCallCardDrug_setTime : null,
					'LpuBuilding_id' => !empty($record->LpuBuilding_id) ? $record->LpuBuilding_id : null,
					'Drug_id' => !empty($record->Drug_id) ? $record->Drug_id : null,
					'DrugNomen_id' => $record->DrugNomen_id,
					'MedStaffFact_id' => !empty($record->MedStaffFact_id) ? $record->MedStaffFact_id : null,
					'CmpCallCardDrug_Comment' => $record->CmpCallCardDrug_Comment,
					'CmpCallCardDrug_Kolvo' => !empty($record->CmpCallCardDrug_Kolvo) ? $record->CmpCallCardDrug_Kolvo : null,
					'GoodsUnit_id' => !empty($record->GoodsUnit_id) ? $record->GoodsUnit_id : null,
					'CmpCallCardDrug_Sum' => !empty($record->CmpCallCardDrug_Sum) ? $record->CmpCallCardDrug_Sum : null,
					'pmUser_id' => $data['pmUser_id']
				));
				break;
			case 'delete':
				if(empty($record->CmpCallCardDrug_id))
					return false;
				$response = $this->deleteObject('CmpCallCardDrug', array(
					'CmpCallCardDrug_id' => $record->CmpCallCardDrug_id
				));
				if (!empty($response['Error_Msg'])) {
					$error[] = $response['Error_Msg'];
				}
				break;
		}
		if (!empty($response['Error_Msg'])) {
			$error[] = $response['Error_Msg'];
		}

		if (!empty($response['Error_Msg'])) {
			$res['error'][] = $response['Error_Msg'];
			$res['success'] = false;
		}

		return $res;
	}
	/**
	 * Сохранение множественных диагнозов
	 */
	function saveCmpCallCardDiagArr($data) {
		if (  $this->regionNick == 'kz' ||  !isset($data['CmpCloseCard_id']) || empty($data['CmpCloseCard_id'])) {
			return false;
		}
		$res['success'] = true;
		$squery = "
				SELECT 
					CLCD.Diag_id,
					CLCD.CmpCloseCardDiag_id,
					CLCD.DiagSetClass_id
				FROM {$this->schema}.CmpCloseCardDiag CLCD (nolock)
				WHERE CLCD.CmpCloseCard_id = :CmpCloseCard_id
				AND (CLCD.DiagSetClass_id = 3 OR CLCD.DiagSetClass_id = 2)";
		$result = $this->db->query( $squery, $data );
		if ( !is_object( $result ) ) {
			return false;
		}

		$oldDiags = $result->result('array');
		if((count($data['arrDiag_sid']) > 0) || (count($data['arrDiag_ooid']) > 0)){

			// диагнозы имеются? Добавляем  удаляем не найден
			$oldDiag_ids = array();
			$oldDelDiag_ids = array();
			foreach($oldDiags as $Diag) {
				$oldDiag_ids[$Diag['Diag_id']] = $Diag['Diag_id']; // Имеющиеся дигнозы
				$oldDelDiag_ids[$Diag['Diag_id']] = $Diag['CmpCloseCardDiag_id'];
			}
			foreach($data['arrDiag_sid'] as $diag)
			{
				if(in_array($diag,$oldDiag_ids)) {
					unset($oldDelDiag_ids[$diag]);
				}
				else{
					$params = array(
						'Diag_id' => $diag,
						'DiagSetClass_id' => 3, // сопутствующий
						'CmpCloseCard_id' => $data['CmpCloseCard_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->actionCmpCloseCardDiag('add',$params);
				}
			}
			foreach($data['arrDiag_ooid'] as $diag)
			{
				if(in_array($diag,$oldDiag_ids)) {
					unset($oldDelDiag_ids[$diag]);
				}
				else{
					$params = array(
						'Diag_id' => $diag,
						'DiagSetClass_id' => 2, // осложнение основного
						'CmpCloseCard_id' => $data['CmpCloseCard_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					$response = $this->actionCmpCloseCardDiag('add',$params);
				}
			}

			foreach($oldDelDiag_ids as $Diag) {
				$params = array(
					'CmpCloseCardDiag_id' => $Diag
				);
				$response = $this->actionCmpCloseCardDiag('del',$params);
				//Удаляем элементы предыдущего дерева решений, если оно существовало
			}

		}
		else {
			// диагнозы не пришли? удаляем все присутствующие
			foreach($oldDiags as $Diag) {
				$params = array(
					'CmpCloseCardDiag_id' => $Diag['CmpCloseCardDiag_id']
				);
				$response = $this->actionCmpCloseCardDiag('del',$params);
				//Удаляем элементы предыдущего дерева решений, если оно существовало
			}
		}

		if (!empty($response['Error_Msg'])) {
			$error[] = $response['Error_Msg'];
		}

		if (!empty($response['Error_Msg'])) {
			$res['error'][] = $response['Error_Msg'];
			$res['success'] = false;
		}
		return $res;

	}

	/**
	 * Удаление диагноза
	 */
	function actionCmpCloseCardDiag($action, $params) {

		switch($action){
			case 'add':
				if(empty($params['Diag_id']) || empty($params['CmpCloseCard_id']) || empty($params['DiagSetClass_id']))
					return false;
				$query = "
					declare
						@CmpCloseCardDiag_id bigint,
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec {$this->schema}.p_CmpCloseCardDiag_ins
						@CmpCloseCard_id = :CmpCloseCard_id,
						@Diag_id = :Diag_id,
						@DiagSetClass_id = :DiagSetClass_id,
						@pmUser_id = :pmUser_id,
						@CmpCloseCardDiag_id = @CmpCloseCardDiag_id output,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @CmpCloseCardDiag_id as CmpCloseCardDiag_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, $params);
				break;
			case 'del':
				if(empty($params['CmpCloseCardDiag_id']))
					return false;
				$query = "
					declare
						@ErrCode int,
						@ErrMessage varchar(4000);
					exec {$this->schema}.p_CmpCloseCardDiag_del
						@CmpCloseCardDiag_id = :CmpCloseCardDiag_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMessage output;
					select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				";
				$result = $this->db->query($query, $params);
				break;
		}

		if (!is_object($result)) {
			return false;
		}
		$result = $result->result('array');

	}

	/**
	 *  Получение информации о диагнозах
	 */
	function getSidOoidDiags($data) {

		$params = array('CmpCloseCard_id' => $data['CmpCloseCard_id']);

		$query = "
			select
                cccd.Diag_id,
                cccd.DiagSetClass_id               
			from
				{$this->schema}.v_CmpCloseCardDiag cccd with(nolock)				
			where
				cccd.CmpCloseCard_id = :CmpCloseCard_id;
		";

		$response = $this->queryResult($query, $params);
		return $response;
	}
        
        /**
	 * Получение списка номеров карт(110/у), которых запросил СМО
	 */
	function getSmoQueryCallCards($data) {
		
		$query = "
			select
                                CmpSmoQueryCardNumbers_id as id,
                                CmpSmoQueryCardNumbers_CardNumber as CardNumber,
                                --CmpSmoQueryCardNumbers_SmoID as OrgSmo_id,
                                --CmpSmoQueryCardNumbers_insLpuID,
                                cast(cast(CmpSmoQueryCardNumbers_insDT as date) as char(10)) as insDate                                
			from
				r2.v_CmpSmoQueryCardNumber with(nolock)				
			where
				CmpSmoQueryCardNumbers_SmoID = :OrgSmo_id;
		";
               // $response = $this->queryResult($query, $data);
                //return $response;
                $result = $this->db->query($query, $data);
        if (is_object($result)) {
                    return $result->result('array');
        } else {
                    return false;
        }
        }
        
        /**
	 * Добавление списка номеров карт(110/у), которых запросил СМО
	 */
	function setSmoQueryCallCards($data) {
                $query = "
                        declare
                                @Error_Code int,
                                @Error_Message varchar(4000);

                        exec r2.p_CmpSmoQueryCardNumbers_ins

                        @CmpSmoQueryCardNumbers_CardNumber = :CardNumber,
                        @CmpSmoQueryCardNumbers_SmoID = :OrgSmo_id,
                        @CmpSmoQueryCardNumbers_insLpuID = :Lpu_id,
                        @pmUser_insID = :pmUser_id,
                        @CmpSmoQueryCardNumbers_insDT = :insDT;

                        select @Error_Code as Error_Code, 
                               @Error_Message as Error_Message;
                ";
                
                $response = $this->queryResult($query, $data);
                return $response;
	}
        
        /**
        * Удаляем прошлый запрос карт от СМО
        */
	function delSmoQueryCallCards($data){
                $query = "
            declare
                                @Error_Code int,
                                @Error_Message varchar(4000);
                        exec r2.p_CmpSmoQueryCardNumbers_del

                        @CmpSmoQueryCardNumbers_SmoID = :OrgSmo_id

                        select @Error_Code as Error_Code, @Error_Message as Error_Message;
                ";
                $response = $this->queryResult($query, $data);
                return $response;
	}

	/**
	 * Получаем флаг опер отдела "Включить функцию «Контроль вызовов»"
	 */
	function getIsCallControllFlag($data){

		$this->load->model('CmpCallCard_model4E', 'CmpCallCard_model4E');
		$operLpuBuilding = $this->CmpCallCard_model4E->getOperDepartament($data);

		if(empty($operLpuBuilding["LpuBuilding_pid"]))
			return false;

		$query = "
		select top 1 CASE WHEN ISNULL(SmpUnitParam_IsCallControll, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCallControll
					from v_SmpUnitParam with (nolock)
					where LpuBuilding_id = :LpuBuilding_pid
					order by SmpUnitParam_id desc
		";
		return $this->queryResult($query, $operLpuBuilding);
	}

	/*
	 * получаем МО опер отдела НМП, на котором текущая служба
	 */
	function getNMPLpu($data) {

		$MedService_id = $data['session']['CurARM']['MedService_id'];

		if (empty($MedService_id)) {
			return false;
		}

		$query = "
			SELECT
				LB.Lpu_id
			FROM v_MedService MS
			left join v_LpuBuilding MSLB on MSLB.LpuBuilding_id = MS.LpuBuilding_id
			left join v_SmpUnitParam SUP on SUP.LpuBuilding_id = MSLB.LpuBuilding_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = SUP.LpuBuilding_pid
			WHERE MS.MedService_id = :MedService_id
		";

		$result = $this->db->query($query, array('MedService_id' => $MedService_id))->result_array();

		if (isset($result[0]) && isset($result[0]['Lpu_id'])) {
			return $result[0]['Lpu_id'];
		}

		return false;
	}

	/*
	 * получаем адрес талона вызова
	 */
	function getAdressByCardId($data) {
		$query = "
			select
				ccc.KLStreet_id,
				ccc.CmpCallCard_Dom,
				ccc.CmpCallCard_Korp,
				ccc.KLCity_id,
				ccc.KLTown_id
			from CmpCallCard ccc
			where CmpCallCard_id = :CmpCallCard_id
		";

		return $this->db->query($query, $data)->result_array();
	}

	/*
	 * проставляем МО передачи СМП и подстанцию
	 */
	function sendCallToSmp($data) {
		$params = array();

		$params['pmUser_id'] = $data['pmUser_id'];
		$params['Lpu_smpid'] = $data['Lpu_id'];
		$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		$params['CmpCallCard_id'] = $data['CmpCallCard_id'];
		$params['Lpu_ppdid'] = null;

		$eventParams = array(
			"CmpCallCard_id" => $data["CmpCallCard_id"],
			"CmpCallCardEventType_Code" => 1,
			"CmpCallCardEvent_Comment" => 'Передан в СМП',
			"pmUser_id" => $data["pmUser_id"]
		);

		$this->setCmpCallCardEvent( $eventParams );

		return $this->swUpdate('CmpCallCard', $params);
	}
}

