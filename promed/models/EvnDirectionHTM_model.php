<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionHTM_model - модель для с работы с направлениями на ВМП
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Htm
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			28.07.2014
 */
require_once(APPPATH.'models/EvnDirection_model.php');

class EvnDirectionHTM_model extends EvnDirection_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение информации для направления на ВМП
	 */
	function loadPersonInfoForEvnDirectionHTM($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select top 1
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				PS.Sex_id,
				convert(varchar(10), PS.Person_BirthDay, 104) as Person_BirthDay,
				PS.Person_Snils,
				PS.Person_Phone,
				PS.Polis_Num,
				P.OrgSMO_id,
				D.Document_id,
				D.DocumentType_id,
				D.Document_Ser,
				D.Document_Num,
				D.OrgDep_id,
				convert(varchar(10), D.Document_begDate, 104) as Document_begDate,
				isnull(PA.Address_Address, '') as PersAddress_AddressText,
				case when PCitySocr.KLSocr_Nick in ('Г','ПГТ') then 1 else 2 end as PlaceKind_id,
				isnull(PI.PersonInfo_Email,'') as PersonInfo_Email
			from
				v_PersonState PS with(nolock)
				left join v_Document D with(nolock) on D.Document_id = PS.Document_id
				left join v_Polis P with(nolock) on P.Polis_id = PS.Polis_id
				left join v_Address PA with(nolock) on PA.Address_id = PS.PAddress_id
				left join v_KLRgn PRgn with(nolock) on PRgn.KLRgn_id = PA.KLRgn_id
				left join v_KLCity PCity with(nolock) on PCity.KLCity_id = PA.KLCity_id
				left join v_KLTown PTown with(nolock) on PTown.KLTown_id = PA.KLTown_id
				left join v_KLSocr PCitySocr with(nolock) on PCitySocr.KLSocr_id = coalesce(PTown.KLSocr_id, PCity.KLSocr_id, PRgn.KLSocr_id)
				left join v_PersonInfo PI with(nolock) on PI.Person_id = PS.Person_id
			where
				PS.Person_id = :Person_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение информации для направления на ВМП
	 */
	function loadOrgInfoForEvnDirectionHTM($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select top 1
				O.Org_id,
				O.Org_OKPO,
				O.Org_OKATO,
				PA.Address_Zip as OrgAddress_Zip,
				isnull(PA.Address_Address, '') as OrgAddress_AddressText,
				O.Org_Email
			from
				v_Lpu L with(nolock)
				inner join v_Org O with(nolock) on O.Org_id = L.Org_id
				left join v_Address PA with(nolock) on PA.Address_id = O.PAddress_id
			where
				L.Lpu_id = :Lpu_id
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение данных направления на ВМП для редактирования
	 */
	function loadEvnDirectionHTMForm($data) {
		$params = array('EvnDirectionHTM_id' => $data['EvnDirectionHTM_id']);
		$select = '';
		$join = '';

		if(getRegionNick() == 'penza') {
			$select .= " TTL.TreatmentType_id,";
			$join .= "	left join r58.TreatmentTypeLink TTL with (nolock) on TTL.EvnDirection_id = EDH.EvnDirectionHTM_id";
		}

		$query = "
			select top 1
				EDH.EvnDirectionHTM_id,
				EDH.EvnDirectionHTM_pid,
				EDH.Person_id,
				EDH.PersonEvn_id,
				EDH.Server_id,
				EDH.Lpu_did,
				EDH.LpuUnit_did,
				EDH.LpuSection_did,
				EDH.MedService_id,
				EDH.TimetableMedService_id,
				EDH.PrivilegeType_id,
				EDH.HTMSocGroup_id,
				convert(varchar(10), EDH.EvnDirectionHTM_setDate, 104) as EvnDirectionHTM_setDate,
				EDH.EvnDirectionHTM_VKProtocolNum,
				convert(varchar(10), EDH.EvnDirectionHTM_VKProtocolDate, 104) as EvnDirectionHTM_VKProtocolDate,
				EDH.EvnDirectionHTM_IsHTM,
				EDH.EvnDirectionHTM_Num,
				EDH.LpuSection_id,
				EDH.MedPersonal_id,
				EDH.MedStaffFact_id,
				EDH.Lpu_id,
				EDH.HTMFinance_id,
				EDH.HTMOrgDirect_id,
				EDH.LpuSectionProfile_id,
				convert(varchar(10), EDH.EvnDirectionHTM_planDate, 104) as EvnDirectionHTM_planDate,
				EDH.Diag_id,
				LpuHTM.LpuHTM_id,
				convert(varchar(10), EDH.EvnDirectionHTM_directDate, 104) as EvnDirectionHTM_directDate,
				EDH.EvnDirectionHTM_TalonNum,
				EDH.PrehospType_did,
				LpuHTM.Region_id,
				EDH.HTMedicalCareType_id,
				{$select}
				EDH.HTMedicalCareClass_id,
				EDH.EvnStatus_id,
				EL.EvnLink_id,
				EL.Evn_lid AS EvnDirection_pid
			from v_EvnDirectionHTM EDH with(nolock)
			left join v_LpuHTM LpuHTM with (nolock) on LpuHTM.LpuHTM_id = EDH.LpuHTM_id
			LEFT JOIN EvnLink EL WITH(NOLOCK) ON EL.Evn_id = EDH.EvnDirectionHTM_id
			{$join}
			where EDH.EvnDirectionHTM_id = :EvnDirectionHTM_id
		";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Сохранение направления на ВМП
	 */
	function saveEvnDirectionHTM($data) {
		$params = $data;
		$params['TimetableMedService_id'] = empty($params['TimetableMedService_id']) ? null : $params['TimetableMedService_id'];

		if (empty($params['EvnDirectionHTM_Num'])) {
			$response = $this->getEvnDirectionHTMNumber($params);
			if (is_array($response) && isset($response[0]['EvnDirection_Num'])) {
				$params['EvnDirectionHTM_Num'] = (int)$response[0]['EvnDirection_Num'];
			}
		}

		if (getRegionNick() == 'penza' && !empty($params['EvnDirectionHTM_Num'])) {
		    // если номер уже используется в системе, то появляется сообщение об ошибке «Номер направления должен быть уникален в рамках МО. Для сохранения укажите другой номер направления. ОК»
            $check_filter = "";
            if (!empty($params['EvnDirectionHTM_id'])) {
                $check_filter .= " and EvnDirectionHTM_id <> :EvnDirectionHTM_id";
            }
            $resp = $this->queryResult("
                select top 1
                    EvnDirectionHTM_id
                from
                    v_EvnDirectionHTM with (nolock)
                where
                    Lpu_id = :Lpu_id
                    and EvnDirectionHTM_Num = :EvnDirectionHTM_Num
                    {$check_filter}
            ", $params);

            if (!empty($resp[0]['EvnDirectionHTM_id'])) {
                return array(array('Error_Msg'=> 'Номер направления должен быть уникален в рамках МО. Для сохранения укажите другой номер направления.', ));
            }
        }

		if (!empty($data['withCreateDirection']) && !empty($data['EvnDirectionHTM_pid']) && !empty($data['MedService_id'])) {
			$data['LpuUnitType_SysNick'] = 'parka';
			$data['DirType_id'] = 9;
			$data['EvnDirection_IsAuto'] = 2;
			$data['EvnDirection_IsReceive'] = 1;
			$data['Lpu_sid'] = $params['Lpu_id'];
			$data['EvnDirection_Num'] = $params['EvnDirectionHTM_Num'];
			$data['EvnDirection_setDate'] = date('Y-m-d');
			$data['RemoteConsultCause_id'] = null;
			$data['EvnDirection_pid'] = null;
			$data['EvnDirection_Descr'] = null;
			$data['MedPersonal_zid'] = null;
			$data['MedStaffFact_id'] = null;
			$data['From_MedStaffFact_id'] = null;
			$data['Diag_id'] = null;
			// Получаем данные для направления из EvnVK по EvnDirectionHTM_pid
			/*
			if(option.params.Diag_id==='0'&&option.vkPar&&option.vkPar.Diag_id>0){
				option.params.Diag_id = option.vkPar.Diag_id
			}
			option.params.MedStaffFact_id = option.params.MedStaffFact_id || '0';
			option.params.From_MedStaffFact_id= option.params.MedStaffFact_id || '0';
			*/
			$query = "
				declare @today DATE = dbo.tzGetDate();
				select top 1
					v_EvnVK.Diag_id,
					MSMP.MedPersonal_id,
					MSMP.MedService_id as MedService_sid,
					MS.Lpu_id as Lpu_sid,
					MS.LpuSection_id as LpuSection_sid,
					MSD.Lpu_id as Lpu_did,
					MSD.LpuUnit_id as LpuUnit_did,
					MSD.LpuSection_id as LpuSection_did,
					MSF.MedStaffFact_id
				from v_EvnVK (nolock)
				left join v_EvnVKExpert (nolock) on v_EvnVK.EvnVK_id = v_EvnVKExpert.EvnVK_id  and v_EvnVKExpert.ExpertMedStaffType_id = 1
				left join v_VoteListVK VLVK (nolock) on VLVK.EvnPrescrVK_id = v_EvnVK.EvnPrescrVK_id
				left join v_VoteExpertVK VEK (nolock) on VEK.VoteListVK_id = VLVK.VoteListVK_id and VEK.ExpertMedStaffType_id = 1
				inner join v_MedService MS (nolock) on MS.MedService_id = v_EvnVK.MedService_id
				inner join v_MedService MSD (nolock) on MSD.MedService_id = :MedService_id
				inner join v_MedServiceMedPersonal MSMP (nolock) on MSMP.MedServiceMedPersonal_id = isnull(v_EvnVKExpert.MedServiceMedPersonal_id, VEK.MedServiceMedPersonal_id)
				outer apply (
					select top 1 msf.MedStaffFact_id, msf.LpuSection_id
					from v_MedStaffFact msf (nolock)
					where msf.MedPersonal_id = MSMP.MedPersonal_id
						and msf.Lpu_id = MS.Lpu_id
						and cast(msf.WorkData_begDate as DATE) <= @today
						and (msf.WorkData_endDate is null OR cast(msf.WorkData_endDate as DATE) >= @today)
					order by 
						case when MS.LpuBuilding_id is not null and MS.LpuBuilding_id = msf.LpuBuilding_id then 1 else 2 end,
						case when MS.LpuUnit_id is not null and MS.LpuUnit_id = msf.LpuUnit_id then 1 else 2 end,
						case when MS.LpuSection_id is not null and MS.LpuSection_id = msf.LpuSection_id then 1 else 2 end
				) MSF
				where v_EvnVK.EvnVK_id = :EvnDirectionHTM_pid
			";
			$result = $this->db->query($query, $params);
			if (is_object($result)) {
				$response = $result->result('array');
				if (empty($response) || false === is_array($response)) {
					return array(array('Error_Code'=> 500, 'Error_Msg'=> 'Не удалось получить данные председателя ВК', ));
				}
				if (empty($response[0]['MedStaffFact_id'])) {
					return array(array('Error_Code'=> 500, 'Error_Msg'=> 'У председателя ВК нет ни одного места работы', ));
				}
				$data['MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
				$data['From_MedStaffFact_id'] = $response[0]['MedStaffFact_id'];
				$data['MedPersonal_id'] = $response[0]['MedPersonal_id'];
				$data['LpuSection_id'] = $response[0]['LpuSection_sid'];
				$data['Diag_id'] = $response[0]['Diag_id'];
				//$data['Lpu_id'] = $response[0]['Lpu_id'];
				$data['Lpu_sid'] = $response[0]['Lpu_sid'];
				$data['LpuSection_did'] = $response[0]['LpuSection_did'];
				$data['Lpu_did'] = $response[0]['Lpu_did'];
				$data['LpuUnit_did'] = $response[0]['LpuUnit_did'];
			} else {
				return false;
			}
		}
		
		if (getRegionNick() != 'kz' && empty($data['Lpu_did'])) {
			$query = "
				SELECT top 1 
					l.Lpu_id
				FROM v_LpuHTM LH WITH (NOLOCK)
				inner JOIN v_Lpu l WITH (NOLOCK) ON l.Lpu_f003mcod = LH.LpuHTM_f003mcod
				WHERE lh.LpuHTM_id = :LpuHTM_id
			";
			
			$lpu_id = $this->getFirstResultFromQuery($query, ['LpuHTM_id' => $data['LpuHTM_id']]);
			
			$params['Lpu_did'] = $lpu_id;
		}

		$this->beginTransaction();
		
		$procedure = "p_EvnDirectionHTM_ins";
		if (!empty($params['EvnDirectionHTM_id'])) {
			$procedure = "p_EvnDirectionHTM_upd";
		}
		
		$params['DirType_id'] = (!empty($params['DirType_id'])) ? $params['DirType_id'] : 19; //На высокотехнологичную помощь

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @Res = :EvnDirectionHTM_id;
			exec {$procedure}
				@EvnDirectionHTM_id = @Res output,
				@EvnDirectionHTM_pid = :EvnDirectionHTM_pid,
				@EvnDirectionHTM_Num = :EvnDirectionHTM_Num,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@PrivilegeType_id = :PrivilegeType_id,
				@HTMSocGroup_id = :HTMSocGroup_id,
				@EvnDirectionHTM_setDT = :EvnDirectionHTM_setDate,
				@EvnDirectionHTM_VKProtocolNum = :EvnDirectionHTM_VKProtocolNum,
				@EvnDirectionHTM_VKProtocolDate = :EvnDirectionHTM_VKProtocolDate,
				@EvnDirectionHTM_IsHTM = :EvnDirectionHTM_IsHTM,
				@HTMFinance_id = :HTMFinance_id,
				@HTMOrgDirect_id = :HTMOrgDirect_id,
				@MedService_id = :MedService_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@TimetableMedService_id = :TimetableMedService_id,
				@Lpu_id = :Lpu_id,
				@Lpu_sid = :Lpu_id,
				@LpuSection_id = :LpuSection_id,
				@Lpu_did = :Lpu_did,
				@LpuUnit_did = :LpuUnit_did,
				@LpuSection_did = :LpuSection_did,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@EvnDirectionHTM_planDate = :EvnDirectionHTM_planDate,
				@Diag_id = :Diag_id,
				@LpuHTM_id = :LpuHTM_id,
				@EvnDirectionHTM_directDate = :EvnDirectionHTM_directDate,
				@EvnDirectionHTM_TalonNum = :EvnDirectionHTM_TalonNum,
				@PrehospType_did = :PrehospType_did,
				@HTMedicalCareType_id = :HTMedicalCareType_id,
				@HTMedicalCareClass_id = :HTMedicalCareClass_id,
				@EvnStatus_id = :EvnStatus_id,
				@DirType_id = :DirType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as EvnDirectionHTM_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$response = $result->result('array');
		} else {
			return false;
		}
		if (!empty($response[0]['Error_Msg']) || empty($response[0]['EvnDirectionHTM_id'])) {
			$this->rollbackTransaction();
			return $response;
		}
	
		if(!empty($params['EvnDirection_pid']) && !empty($response[0]['EvnDirectionHTM_id'])){
			$saveEvnLink = $this->saveEvnLink(array(
				'Evn_id' => $response[0]['EvnDirectionHTM_id'],
				'Evn_lid' => $params['EvnDirection_pid'],
				'EvnLink_id' => (!empty($params['EvnLink_id'])) ? $params['EvnLink_id'] : null,
				'pmUser_id' => $params['pmUser_id']
			));
			//$saveEvnLink[0]['EvnLink_id']
		}
		
		if( 
			getRegionNick() == 'penza'
			&& isset($data['TreatmentType_id'])
		){
			$TreatmentTypeLink_response = $this->getFirstRowFromQuery("
				select top 1 *
				from r58.TreatmentTypeLink
				where EvnDirection_id = :EvnDirection_id
			", array(
				'EvnDirection_id' => $response[0]['EvnDirectionHTM_id']
			));

			$TreatmentTypeLink_params = array(
				'TreatmentType_id' => $data['TreatmentType_id'],
				'EvnDirection_id' => $response[0]['EvnDirectionHTM_id'],
				'pmUser_id' => $data['pmUser_id'],
				'TreatmentTypeLink_id' => null
			);

			if(empty($TreatmentTypeLink_response)) {
				$TreatmentTypeLink_proc = 'r58.p_TreatmentTypeLink_ins';
			} else {
				$TreatmentTypeLink_proc = 'r58.p_TreatmentTypeLink_upd';
				$TreatmentTypeLink_params['TreatmentTypeLink_id'] = $TreatmentTypeLink_response['TreatmentTypeLink_id'];
			}
			
			$TreatmentTypeLink_response = $this->db->query("
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :TreatmentTypeLink_id;
				exec {$TreatmentTypeLink_proc}
					@TreatmentTypeLink_id = @Res output,
					@TreatmentType_id = :TreatmentType_id,
					@EvnDirection_id = :EvnDirection_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as TreatmentTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			",
				$TreatmentTypeLink_params
			);
			if(is_object($TreatmentTypeLink_response)) {
				$TreatmentTypeLink_result = $TreatmentTypeLink_response->result('array');
				
				if(!empty($TreatmentTypeLink_result['Error_Code'])) {
					$this->rollbackTransaction();
					throw new Exception($TreatmentTypeLink_result['Error_Msg']);
				}

				
			} else {
				$this->rollbackTransaction();
				throw new Exception ('Ошибка при сохранении типа предстоящего лечения');
			}
		}

		if (!empty($data['withCreateDirection']) && !empty($data['TimetableMedService_id'])) {
			// Записываем на бирку
			$data['Evn_id'] = $response[0]['EvnDirectionHTM_id'];
			$data['object'] = 'TimetableMedService';
			$this->load->helper('Reg');
			$this->load->model("TimetableMedService_model");
			$resp = $this->TimetableMedService_model->Apply($data);
			if ( $resp['success'] ) {
				$response[0]['EvnDirection_id'] = $resp['EvnDirection_id'];
			} else {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		if (!empty($data['withCreateDirection']) && empty($data['TimetableMedService_id'])) {
			// Ставим в очередь
			$data['Evn_id'] = $response[0]['EvnDirectionHTM_id'];
			$data['toQueue'] = 1;
			$data['LpuSectionProfile_did'] = $data['LpuSectionProfile_id'];
			$data['MedService_did'] = $data['MedService_id'];
			$data['MedPersonal_did'] = null;
			$resp = $this->saveEvnDirection($data);
			if ( is_array($resp) && count($resp) > 0 && empty($resp[0]['Error_Msg']) ) {
				$response[0]['EvnDirection_id'] = $resp[0]['EvnDirection_id'];
			} else {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		if($this->getRegionNick() == 'kareliya') {
			$this->load->model('Person_model', 'Person_model');
			$resp = $this->Person_model->savePersonInfo(array(
				'Person_id' => $data['Person_id'],
				'Server_id' => $data['Server_id'],
				'pmUser_id' => $data['pmUser_id'],
				'PersonInfo_Email' => $data['PersonInfo_Email']));
			if ( !is_array($resp) || !empty($resp[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $resp;
			}
		}
		if($this->getRegionNick() == 'ufa' && $procedure == 'p_EvnDirectionHTM_ins') {
			$this->load->model('HTMRegister_model','htmregister');
			$params = array();
			$params['EvnDirectionHTM_id'] = $response[0]['EvnDirectionHTM_id'];
			$params['Register_setDate'] = date("Y-m-d H:i:s");//$data['EvnDirectionHTM_setDate'];
			$params['Person_id'] = $data['Person_id'];
			$params['RegisterType_Code'] = 'HTM';
			$params['session'] = $this->getSessionParams();
			$params['scenario'] = SwModel::SCENARIO_DO_SAVE;
			$params['Diag_FirstId'] = $data['Diag_id'];
			$params['HTMRegister_PlannedHospDate'] = $data['EvnDirectionHTM_planDate'];
			$params['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
			$result = $this->htmregister->doSave($params);
			if(empty($result['HTMRegister_id']))
				throw new Exception('Произошла ошибка при добавлении в регистр');
		}
		$this->commitTransaction();
		return $response;
	}
	
	/**
	 * сохранение EvnLink
	 */
	function saveEvnLink($data){
		if(empty($data['Evn_id']) || empty($data['Evn_lid'])) return false;
		$procedure = "p_EvnLink_ins";
		if(!empty($data['EvnLink_id'])){
			$procedure = "p_EvnLink_upd";
		}else{
			$data['EvnLink_id'] = null;
		}
		$query = "
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Message varchar(4000);

			set @Res = :EvnLink_id;

			EXEC dbo.$procedure
				@EvnLink_id    = @Res    output, -- bigint
				@Evn_id        = :Evn_id       , -- bigint
				@Evn_lid       = :Evn_lid      , -- bigint
				@pmUser_id     = :pmUser_id    , -- bigint
				@Error_Code    = @Error_Code    output, -- int
				@Error_Message = @Error_Message output -- varchar(4000)

			select @Res as EvnLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		return $this->queryResult($query, $data);
	}
	
	/**
	 * удаление EvnLink
	 */
	function deleteEvnLink($data){
		if(empty($data['EvnLink_id']) && empty($data['EvnDirectionHTM_id'])) return FALSE;
		
		if(empty($data['EvnLink_id']) && !empty($data['EvnDirectionHTM_id'])){
			$query = 'SELECT top 1 EvnLink_id FROM EvnLink WHERE Evn_id = :EvnDirectionHTM_id';
			$res = $this->getFirstRowFromQuery($query, $data);
			if(!empty($res['EvnLink_id'])){
				$data['EvnLink_id'] = $res['EvnLink_id'];
			}
		}
		if(empty($data['EvnLink_id'])) return FALSE;
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnLink_del
				@EvnLink_id = :EvnLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, array('EvnLink_id' => $data['EvnLink_id']));
		return $result;
	}

	/**
	 * Получение списка направлений на ВМП (для АРМ ВМП)
	 */
	function loadEvnDirectionHTMGrid($data) {
		$filter = "";
		$params = array('MedService_id' => $data['MedService_id']);
		$dateSearchField = 'EDH.EvnDirectionHTM_directDate'; // Дата направления
		if(!empty($data['dateType']) && $data['dateType']=='issue'){

			$dateSearchField = 'EDH.EvnDirectionHTM_setDate'; // Дата оформления талона
		}

		if (!empty($data['begDate'])) {
			$filter .= " and ISNULL(cast(TMS.TimetableMedService_begTime as date), ".$dateSearchField.") >= :begDate";
			$params['begDate'] = $data['begDate'];
		}
		if (!empty($data['endDate'])) {
			$filter .= " and ISNULL(cast(TMS.TimetableMedService_begTime as date), ".$dateSearchField.") <= :endDate";
			$params['endDate'] = $data['endDate'];
		}
		if (!empty($data['Search_SurName'])) {
			$filter .= " and P.Person_SurName LIKE :Search_SurName + '%'";
			$params['Search_SurName'] = $data['Search_SurName'];
		}
		if (!empty($data['Search_FirName'])) {
			$filter .= " and P.Person_FirName LIKE :Search_FirName + '%'";
			$params['Search_FirName'] = $data['Search_FirName'];
		}
		if (!empty($data['Search_SecName'])) {
			$filter .= " and P.Person_SecName LIKE :Search_SecName + '%'";
			$params['Search_SecName'] = $data['Search_SecName'];
		}
		if (!empty($data['Search_BirthDay'])) {
			$filter .= " and P.Person_BirthDay = :Search_BirthDay";
			$params['Search_BirthDay'] = $data['Search_BirthDay'];
		}
		if (!empty($data['HTMFinance_id'])) {
			$filter .= " and EDH.HTMFinance_id = :HTMFinance_id";
			$params['HTMFinance_id'] = $data['HTMFinance_id'];
		}

		$query = "
			select
				-- select
				EDH.EvnDirectionHTM_id,
				TMS.TimetableMedService_id,
				P.Person_id,
				P.PersonEvn_id,
				P.Server_id,
				EDH.EvnDirectionHTM_IsHTM,
				LpuAttach.Lpu_id as LpuAttach_id,
				case
					when TMS.TimeTableMedService_id is null then 'б/з'
					else convert(varchar(5), TMS.TimetableMedService_begTime, 108)
				end as TimetableMedService_begTime,
				case when EDH.EvnDirectionHTM_IsHTM = 2 then 'Первично' else 'Повторно' end as EvnDirectionHTM_IsHTMName,
				P.Person_SurName+' '+P.Person_FirName+(case when P.Person_SecName is not null then ' '+P.Person_SecName else '' end) as Person_FIO,
				convert(varchar(10), P.Person_BirthDay, 104) as Person_BirthDay,
				LpuAttach.Lpu_Nick as LpuAttach_Nick,
				null as EvnDirectionHTM_IsExport,
				case when TMS.TimeTableMedService_id is null then 1 else 0 end as EvnDirectionHTM_Sort,
				convert(varchar(10), ISNULL(TMS.TimetableMedService_begTime, EDH.EvnDirectionHTM_setDate), 104) as EvnDirectionHTM_setDate
				-- end select
			from
				-- from
				v_EvnDirectionHTM EDH with(nolock)
				left join v_TimetableMedService_lite TMS with(nolock) on TMS.TimetableMedService_id = EDH.TimetableMedService_id
				left join v_Person_all P with(nolock) on P.PersonEvn_id = EDH.PersonEvn_id
				outer apply(
					select top 1 t.Lpu_id
					from v_PersonCard t with(nolock)
					where t.Person_id = EDH.Person_id and t.LpuAttachType_id = 1 and t.PersonCard_endDate is null
				) PC
				left join v_Lpu LpuAttach with(nolock) on LpuAttach.Lpu_id = PC.Lpu_id
				-- end from
			where
				-- where
				EDH.MedService_id = :MedService_id
				{$filter}
				-- end where
			order by
				-- order by
				EvnDirectionHTM_Sort,
				TMS.TimetableMedService_begTime
				-- end order by
		";

//		echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * Получение списка направлений на ВМП (для регистра ВМП)
	 */
	function loadEvnDirectionHTMRegistry($data)
	{
		$filter = "";
		$params = array();
		$EvnStatus_SysNick = '';

		if (!empty($data['EvnStatus_id']))
		{
			$EvnStatus_SysNick = '
				OUTER APPLY (
					SELECT TOP 1 
						essn.EvnStatus_SysNick 
					FROM v_EvnStatus essn WITH (NOLOCK) 
					WHERE 
						essn.EvnStatus_id = :EvnStatus_id
					) essn';
			$filter = " AND es.EvnStatus_SysNick = essn.EvnStatus_SysNick";
			$params['EvnStatus_id'] = $data['EvnStatus_id'];
		}

		if (!empty($data['HTMFinance_id']))
		{
			$filter .= " AND edh.HTMFinance_id = :HTMFinance_id";
			$params['HTMFinance_id'] = $data['HTMFinance_id'];
		}

		if (!empty($data['HTMedicalCareType_id']))
		{
			$filter .= " AND edh.HTMedicalCareType_id = :HTMedicalCareType_id";
			$params['HTMedicalCareType_id'] = $data['HTMedicalCareType_id'];
		}

		if (!empty($data['HTMedicalCareClass_id']))
		{
			$filter .= " AND edh.HTMedicalCareClass_id = :HTMedicalCareClass_id";
			$params['HTMedicalCareClass_id'] = $data['HTMedicalCareClass_id'];
		}

		if (getRegionNick() != 'kz') {
			if ($data['session']['CurARM']['ARMType'] == 'htm' && !empty($data['Lpu_id'])) {
				$filter .= " AND (edh.Lpu_sid = :Lpu_id or edh.Lpu_did = :Lpu_id)";
			} else if (in_array($data['session']['CurARM']['ARMType'], ['common', 'polka', 'vk']) && !empty($data['Lpu_id'])){
				$filter .= " AND edh.Lpu_sid = :Lpu_id";
			} else if (!empty($data['Lpu_id']))
				$filter .= " AND edh.Lpu_sid = :Lpu_id";

			if (!empty($data['Lpu_id'])) {
				$params['Lpu_id'] = $data['Lpu_id'];
			}
		} else {
			if (!empty($data['Lpu_id'])) {
				$filter .= " AND edh.Lpu_sid = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}
		}

		if (!empty($data['EvnDirectionHTM_Num']))
		{
			$filter .= " AND edh.EvnDirectionHTM_Num = :EvnDirectionHTM_Num";
			$params['EvnDirectionHTM_Num'] = $data['EvnDirectionHTM_Num'];
		}

		if (!empty($data['HTMRegion_id']))
		{
			$filter .= " AND lh.Region_id = :HTMRegion_id";
			$params['HTMRegion_id'] = $data['HTMRegion_id'];
		}

		if (!empty($data['LpuHTM_id']))
		{
			$filter .= " AND edh.LpuHTM_id = :LpuHTM_id";
			$params['LpuHTM_id'] = $data['LpuHTM_id'];
		}

		if (!empty($data['Person_SurName']))
		{
			$filter .= " AND p.Person_SurName LIKE :Person_SurName + '%'";
			$params['Person_SurName'] = $data['Person_SurName'];
		}

		if (!empty($data['Person_FirName']))
		{
			$filter .= " AND p.Person_FirName LIKE :Person_FirName + '%'";
			$params['Person_FirName'] = $data['Person_FirName'];
		}

		if (!empty($data['Person_SecName']))
		{
			$filter .= " AND p.Person_SecName LIKE :Person_SecName + '%'";
			$params['Person_SecName'] = $data['Person_SecName'];
		}

		if (!empty($data['Person_BirthDay']))
		{
			$filter .= " AND p.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}

		if (!empty($data['begDate']))
		{
			$filter .= " AND ISNULL(edh.EvnDirectionHTM_directDate, :begDate) >= :begDate";
			$params['begDate'] = $data['begDate'];
		}

		if (!empty($data['endDate']))
		{
			$filter .= " AND ISNULL(edh.EvnDirectionHTM_directDate, :endDate) <= :endDate";
			$params['endDate'] = $data['endDate'];
		}

		$query = "
			SELECT
			-- select
				edh.EvnDirectionHTM_id,
				p.Person_id,
				edh.PersonEvn_id,
					coalesce(upper(left(p.Person_SurName, 1)) + lower(stuff(p.Person_SurName, 1, 1, '')),'') + ' ' +
					case 
						when p.Person_FirName is not null 
						then ' ' + coalesce(upper(left(p.Person_FirName, 1)) + lower(stuff(p.Person_FirName, 1, 1, '')),'') 
						else '' 
					end +
					case 
						when p.Person_FirName is not null 
						then ' ' + coalesce(upper(left(p.Person_SecName, 1)) + lower(stuff(p.Person_SecName, 1, 1, '')),'') 
						else '' 
					end 
				as Person_FIO,
				CONVERT(VARCHAR(10), p.Person_BirthDay, 104) Person_BirthDay,
				d.Diag_FullName,
				CONVERT(VARCHAR(10), edh.EvnDirectionHTM_directDate, 104) EvnDirectionHTM_directDate,
				edh.EvnDirectionHTM_Num,
				hf.HTMFinance_Name,
				edh.EvnStatus_id,
				es.EvnStatus_Name,
				esc.EvnStatusCause_Name,
				CONVERT(VARCHAR(10), edh.EvnDirectionHTM_statusDate, 104) EvnDirectionHTM_statusDate,
				CONVERT(VARCHAR(10), edh.EvnDirectionHTM_disDate, 104) EvnDirectionHTM_disDate,
				edh.Lpu_sid Lpu_id,
				l.Lpu_Name,
					coalesce(upper(left(mw.Person_SurName, 1)) + lower(stuff(mw.Person_SurName, 1, 1, '')),'') + ' ' +
					case 
						when mw.Person_FirName is not null 
						then ' ' + coalesce(upper(left(mw.Person_FirName, 1)) + lower(stuff(mw.Person_FirName, 1, 1, '')),'') 
						else '' 
					end +
					case 
						when mw.Person_FirName is not null 
						then ' ' + coalesce(upper(left(mw.Person_SecName, 1)) + lower(stuff(mw.Person_SecName, 1, 1, '')),'') 
						else '' 
					end +
					case when ls.LpuSection_Name is not null then ' ' + ls.LpuSection_Name else '' end	+
					case when ps.Post_Name is not null then ' ' + ps.Post_Name else '' end
				as MedStaffFact_FullName,
				lh.Region_id,
				lh.LpuHTM_Name +
					case when dlb.LpuBuilding_Name is not null then ' ' + dlb.LpuBuilding_Name else '' end	+
					case when dls.LpuSection_Name is not null then ' ' + dls.LpuSection_Name else '' end
				as LpuHTM_FullName,
				lsp.LpuSectionProfile_Name,
				hct.HTMedicalCareType_Name,
				hcc.HTMedicalCareClass_Name
			-- end select
			FROM
			-- from
				v_EvnDirectionHTM edh WITH (NOLOCK)
				LEFT JOIN v_Person_all p WITH (NOLOCK) ON p.PersonEvn_id = edh.PersonEvn_id
				LEFT JOIN v_Diag d WITH (NOLOCK) ON d.Diag_id = edh.Diag_id
				LEFT JOIN v_HTMFinance hf WITH (NOLOCK) ON hf.HTMFinance_id = edh.HTMFinance_id
				LEFT JOIN EvnStatus es WITH (NOLOCK) ON es.EvnStatus_id = edh.EvnStatus_id
				OUTER APPLY (
					SELECT TOP 1 
						esh.EvnStatusCause_id
					FROM v_EvnStatusHistory esh WITH (NOLOCK)
					WHERE 
						esh.Evn_id = edh.EvnDirectionHTM_id  -- Проверить !!!
						AND esh.EvnStatus_id = edh.EvnStatus_id
					ORDER BY esh.EvnStatusHistory_begDate DESC
					) esh
				LEFT JOIN v_EvnStatusCause esc WITH (NOLOCK) ON esc.EvnStatusCause_id = esh.EvnStatusCause_id
				LEFT JOIN v_Lpu l WITH (NOLOCK) ON l.Lpu_id = edh.Lpu_sid
				LEFT JOIN v_LpuSection ls WITH (NOLOCK) ON ls.LpuSection_id = edh.LpuSection_id
				LEFT JOIN persis.v_WorkPlace mw WITH (NOLOCK) ON mw.WorkPlace_id = edh.MedStaffFact_id
				LEFT JOIN v_Post ps WITH (NOLOCK) ON ps.Post_id = edh.Post_id
				LEFT JOIN v_LpuHTM lh WITH (NOLOCK) ON lh.LpuHTM_id = edh.LpuHTM_id
				LEFT JOIN v_LpuSection dls WITH (NOLOCK) ON dls.LpuSection_id = edh.LpuSection_did
				LEFT JOIN v_LpuBuilding dlb WITH (NOLOCK) ON dlb.LpuBuilding_id = dls.LpuBuilding_id
				LEFT JOIN v_LpuSectionProfile lsp WITH (NOLOCK) ON lsp.LpuSectionProfile_id = edh.LpuSectionProfile_id
				LEFT JOIN v_HTMedicalCareType hct WITH (NOLOCK) ON hct.HTMedicalCareType_id = edh.HTMedicalCareType_id
				LEFT JOIN v_HTMedicalCareClass hcc WITH (NOLOCK) ON hcc.HTMedicalCareClass_id = edh.HTMedicalCareClass_id
				{$EvnStatus_SysNick}
			-- end from
			WHERE
			-- where
				1 = 1
				{$filter}
			-- end where
			ORDER BY
			-- order by
				EvnDirectionHTM_directDate DESC
			-- end order by";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result) && is_object($count))
			return array(
				'data' => $result->result('array'),
				'totalCount' => $count->result('array')[0]['cnt']);

		return false;
	}

	/**
	 * Экспорт направлений на ВМП (АРМ ЦОД)
	 */
	function exportDirectionHTM($data) {
	
		$year = substr($data['Year'], 2, 2);
		$month = str_pad($data['Month'], 2, "0", STR_PAD_LEFT);

		if(!is_dir(EXPORTPATH_EDHTM)) {
			if (!mkdir(EXPORTPATH_EDHTM)) {
				DieWithError("Ошибка при создании директории ".EXPORTPATH_EDHTM."!");
			}
		}
		$region = $this->getRegionNumber();
		
		$query = "
			select distinct PP.OrgSMO_id, OSmo.Orgsmo_f002smocod, lpu.Lpu_f003mcod
			from v_EvnDirectionHTM EDH (nolock)
				inner join v_EvnVK EVK (nolock) on EVK.EvnVK_id = EDH.EvnDirectionHTM_pid
				inner join v_PersonState P (nolock) on P.Person_id = EDH.Person_id
				inner join v_Polis PP (nolock) on PP.Polis_id = P.Polis_id
				inner join v_OrgSMO OSmo (nolock) on OSmo.OrgSMO_id = PP.OrgSMO_id
				inner join v_Lpu lpu with (nolock) on lpu.Lpu_id = EDH.Lpu_id
			where 
				MONTH(EVK.EvnVK_setDT) = :month AND
				YEAR(EVK.EvnVK_setDT) = :year AND
				EDH.Lpu_id = :Lpu_id
		";

		$res = $this->db->query($query, array(
			'month' => $data['Month'],
			'year' => $data['Year'],
			'Lpu_id' => $data['Lpu_id']
		));

		if (!is_object($res)) {
			return array('Error_Msg' => 'Ошибка получения данных по направлениям');
		}

		$smo_dir = $res->result('array');

		// Все СМО региона http://redmine.swan.perm.ru/issues/87551#note-30
		$query = "
			select distinct OSmo.OrgSMO_id, OSmo.Orgsmo_f002smocod, curLpu.Lpu_f003mcod
			from v_OrgSMO OSmo (nolock)
				outer apply(select top 1 lpu.Lpu_f003mcod from v_Lpu lpu with (nolock) where lpu.Lpu_id = :Lpu_id ) curLpu
			where 
				OSmo.KLRgn_id = :Region AND
				(OSmo.OrgSmo_endDate is null or OSmo.OrgSmo_endDate > :endDate) 
				AND isnull(OSmo.OrgSMO_isDMS,1) not in (2)
		";
		
		$result = $this->db->query($query, array(
			'endDate' => ($data['Year'].'-'.$month.'-01'),
			'Lpu_id' => $data['Lpu_id'],
			'Region' => $region
		));	
		
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка получения данных по СМО');
		}
		
		$smo_ids = $result->result('array');

		if(count($smo_dir)>0 && !(count($smo_ids)>0)){
			$smo_ids = $smo_dir;
		} else if(count($smo_dir)>0 && count($smo_ids)>0) {
			//Проверка существования СМО с списке СМО региона, в случае отсутствия добавление
			$tmp = array();
			foreach ($smo_dir as $key=>$smo) {
				$flag = 0;
				foreach ($smo_ids as $smo_id) {
					if($smo['OrgSMO_id'] == $smo_id['OrgSMO_id']){
						$flag = 1;
					}
				}
				if($flag === 0){
					array_push($tmp, $smo_dir[$key]);
				}
			}
			if(count($tmp)>0){
				array_merge($smo_ids,$tmp);
			}
		}

		$links = array();
		
		if (!count($smo_ids)) {
			return array('Error_Msg' => 'Для указанного периода в регионе нет активных СМО и не было направлений для пациентов с других территорий. Формирование выгрузки невозможно');
		} else {
			foreach ($smo_ids as $smo_id) {
			
				$query = "
					select 
						EDH.EvnDirectionHTM_id,
						convert(varchar(10), EDH.EvnDirectionHTM_planDate, 120) EvnDirectionHTM_planDate,
						D.Diag_Code,
						LSP.LpuSectionProfile_Code,
						HMCT.HTMedicalCareType_Code,
						HMCC.HTMedicalCareClass_Code,
						EDH.EvnDirectionHTM_Num,
						Lpu.Lpu_f003mcod,
						EVK.EvnVK_NumProtocol,
						convert(varchar(10), EVK.EvnVK_setDT, 120) EvnVK_setDT,
						P.Person_SurName,
						P.Person_FirName,
						P.Person_SecName,
						convert(varchar(10), P.Person_BirthDay, 120) Person_BirthDay,
						Sex.Sex_fedid,
						P.Person_Phone,
						PIinfo.PersonInfo_InternetPhone,
						PIinfo.PersonInfo_Email,
						PT.PolisType_CodeF008 as PolisType_id,
						P.Polis_Num,
						P.Polis_Ser,
						convert(varchar(5), KLArea.KLAdr_Ocatd) as LpuHTM_Okato,
						LpuHTM.LpuHTM_f003mcod as LpuHTM_f003mcod
					from v_EvnDirectionHTM EDH (nolock)
						inner join v_Diag D (nolock) on D.Diag_id = EDH.Diag_id
						inner join v_LpuSectionProfile LSP (nolock) on EDH.LpuSectionProfile_id = LSP.LpuSectionProfile_id
						inner join v_HTMedicalCareType HMCT (nolock) on HMCT.HTMedicalCareType_id = EDH.HTMedicalCareType_id
						inner join v_HTMedicalCareClass HMCC (nolock) on HMCC.HTMedicalCareClass_id = EDH.HTMedicalCareClass_id
						left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = EDH.Lpu_id
						inner join v_EvnVK EVK (nolock) on EVK.EvnVK_id = EDH.EvnDirectionHTM_pid
						inner join v_PersonState P (nolock) on P.Person_id = EDH.Person_id
						inner join v_Sex Sex (nolock) on Sex.Sex_id = P.Sex_id
						left join v_PersonInfo PIinfo (nolock) on PIinfo.Person_id = EDH.Person_id
						inner join v_PolisType PT (nolock) on PT.PolisType_id = P.PolisType_id
						inner join v_LpuHTM LpuHTM (nolock) on LpuHTM.LpuHTM_id = EDH.LpuHTM_id
						left join v_KLArea KLArea (nolock) on KLArea.KLArea_id = LpuHTM.Region_id
						inner join v_Polis PP (nolock) on PP.Polis_id = P.Polis_id
					where 
						MONTH(EVK.EvnVK_setDT) = :month AND
						YEAR(EVK.EvnVK_setDT) = :year AND
						EDH.Lpu_id = :Lpu_id AND 
						PP.OrgSMO_id = :OrgSMO_id AND
						EDH.HTMFinance_id = :HTMFinance_id AND
						LEN(RTRIM(ISNULL(LpuHTM.LpuHTM_f003mcod, ''))) > 0
				";
			
				$result = $this->db->query($query, array(
					'month' => $data['Month'],
					'year' => $data['Year'],
					'Lpu_id' => $data['Lpu_id'],
					'OrgSMO_id' => $smo_id['OrgSMO_id'],
					'HTMFinance_id' => $data['HTMFinance_id']
				));
			
				if (!is_object($result)) {
					return array('Error_Msg' => 'Ошибка получения данных по направлениям');
				}
				
				$res = $result->result('array');

				if (!is_array($res) || count($res) == 0) {

					$fname = "NT_M{$smo_id['Lpu_f003mcod']}_S{$smo_id['Orgsmo_f002smocod']}_$year$month";
					$filename = EXPORTPATH_EDHTM.$fname.'.XML';
					$archivename = EXPORTPATH_EDHTM.$fname.".zip";
					
					if(is_file($archivename)) {
						unlink($archivename);
					}
					
					$xml = simplexml_load_string('<?xml version="1.0" encoding="windows-1251"?><MED_DIRECT />');
					
					$zglv = $xml->addChild('ZGLV');		
					$zglv->addChild('VERSION', '1.0');
					$zglv->addChild('DATE', date('Y-m-d'));
					$zglv->addChild('YEAR', $data['Year']);
					$zglv->addChild('MONTH', $month);
					$zglv->addChild('S_ORG_CODE', $smo_id['Lpu_f003mcod']);
					$zglv->addChild('FILENAME', $fname);
					$zglv->addChild('Q_ZAP', 0);

					$direct = $xml->addChild('DIRECT');
					$direct->addChild('OBLM', 0);
					$direct->addChild('S_MO_CODE', $smo_id['Lpu_f003mcod']);

					$xmlfile = $xml->asXML();
					
					file_put_contents($filename, $xmlfile);
					if (!is_file($filename)) {
						return array('Error_Msg' => 'Ошибка создания xml-файла');
					}
					
					$zip = new ZipArchive();
					$zip->open($archivename, ZIPARCHIVE::CREATE);
					$zip->AddFile($filename, basename($filename));
					$zip->close();
					@unlink($filename);
					$links[] = $archivename;
					continue;
					//return array('Error_Msg' => 'Отсутствуют данные по направлениям');
				}

				$fname = "NT_M{$res[0]['Lpu_f003mcod']}_S{$smo_id['Orgsmo_f002smocod']}_$year$month";
				$filename = EXPORTPATH_EDHTM.$fname.'.XML';
				$archivename = EXPORTPATH_EDHTM.$fname.".zip";
				
				if(is_file($archivename)) {
					unlink($archivename);
				}
				
				$xml = simplexml_load_string('<?xml version="1.0" encoding="windows-1251"?><MED_DIRECT />');
				
				$zglv = $xml->addChild('ZGLV');		
				$zglv->addChild('VERSION', '1.0');
				$zglv->addChild('DATE', date('Y-m-d'));
				$zglv->addChild('YEAR', $data['Year']);
				$zglv->addChild('MONTH', $month);
				$zglv->addChild('S_ORG_CODE', $res[0]['Lpu_f003mcod']);
				$zglv->addChild('FILENAME', $fname);
				$zglv->addChild('Q_ZAP', count($res));
				
				$i = 1;
				foreach ($res as $item) {
					array_walk($item, 'ConvertFromUTF8ToWin1251');
					$direct = $xml->addChild('DIRECT');
					$direct->addChild('OBLM', 1);
					$direct->addChild('S_MO_CODE', $item['Lpu_f003mcod']);

					$zap = $direct->addChild('ZAP');
					$zap->addChild('N_ZAP', $i);
					$zap->addChild('DIRECT_ID', $item['EvnDirectionHTM_id']);
					$zap->addChild('DIRECT_NUM', $item['EvnDirectionHTM_Num']);
					$zap->addChild('PROTOCOL_NUM', $item['EvnVK_NumProtocol']);
					$zap->addChild('PROTOCOL_DATE', $item['EvnVK_setDT']);
					$zap->addChild('FAM', $item['Person_SurName']);
					$zap->addChild('IM', $item['Person_FirName']);
					if (!empty($item['Person_SecName'])) $zap->addChild('OT', $item['Person_SecName']);
					$zap->addChild('DR', $item['Person_BirthDay']);
					$zap->addChild('W', $item['Sex_fedid']);
					$zap->addChild('PACIENT_PHONE1', !empty($item['Person_Phone']) ? $item['Person_Phone'] : 'Не указан');
					$zap->addChild('PACIENT_PHONE2', !empty($item['PersonInfo_Email']) ? $item['PersonInfo_Email'] : 'Не указан');
					//if (!empty($item['PersonInfo_InternetPhone'])) $direct->addChild('PACIENT_PHONE2', $item['PersonInfo_InternetPhone']);
					$zap->addChild('VPOLIS', $item['PolisType_id']);
					$zap->addChild('NPOLIS', $item['Polis_Num']);
					if (!empty($item['Polis_Ser'])) $zap->addChild('SPOLIS', $item['Polis_Ser']);
					$zap->addChild('D_TER_OKATO', $item['LpuHTM_Okato']);
					$zap->addChild('D_MO_CODE', $item['LpuHTM_f003mcod']);
					if (!empty($item['EvnDirectionHTM_planDate'])) $zap->addChild('DIRECT_PLAN_DATE', $item['EvnDirectionHTM_planDate']);
					$zap->addChild('DIRECT_DS1', trim($item['Diag_Code'], '.'));
					$zap->addChild('DIRECT_VMP_PROFIL', $item['LpuSectionProfile_Code']);
					$zap->addChild('DIRECT_VMP_VID', $item['HTMedicalCareType_Code']);
					$zap->addChild('DIRECT_VMP_METOD', $item['HTMedicalCareClass_Code']);
					$i++;
				}

				$xmlfile = $xml->asXML();
				
				file_put_contents($filename, $xmlfile);
				if (!is_file($filename)) {
					return array('Error_Msg' => 'Ошибка создания xml-файла');
				}
				
				$zip = new ZipArchive();
				$zip->open($archivename, ZIPARCHIVE::CREATE);
				$zip->AddFile($filename, basename($filename));
				$zip->close();
				@unlink($filename);
				$links[] = $archivename;
			}
		}
		if(empty($links))
			return array('Error_Msg' => 'Ошибка при формировании выгрузки');

		return array('success' => true, 'link' => $links);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function getEvnDirectionHTMNumber($data) {
		$query = "
			declare @EvnDirectionHTM_Num bigint;
			exec xp_GenpmID @ObjectName = 'EvnDirectionHTM', @Lpu_id = :Lpu_id, @ObjectID = @EvnDirectionHTM_Num output;
			select @EvnDirectionHTM_Num as EvnDirectionHTM_Num;
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	function setEvnDirectionHTMStatus($data)
	{
		$query = "
			DECLARE  @ErrCode    INT,
				@ErrMessage VARCHAR(4000);

			EXEC p_Evn_setStatus @Evn_id                 = :EvnDirectionHTM_id,
				@EvnStatus_id           = :EvnStatus_id,
				@EvnClass_id            = 117,
				@EvnStatusCause_id      = :EvnStatusCause_id,
				@EvnStatusHistory_Cause = :EvnStatusHistory_Cause,
				@pmUser_id              = :pmUser_id,
				@Error_Code             = @ErrCode OUTPUT,
				@Error_Message          = @ErrMessage OUTPUT;

			SELECT @ErrCode Error_Code, @ErrMessage Error_Msg";

		$result = $this->db->query($query, $data);

		if (is_object($result))
			return $result->result('array');

		return false;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getEvnDirectionHTMJSON($data) {

		$resp = $this->queryResult("
			select
				EDH.Lpu_did as Lpu,
				EDH.LpuHTM_id as LpuHTM,
				EDH.Person_id as Person,
				EDH.HTMSocGroup_id as PersonSocGroup,
				EDH.PrivilegeType_id as PersonPrivilege,
				EDH.EvnDirectionHTM_id as DirectionID,
				EDH.EvnDirectionHTM_Num as DirectionNumber,
				convert(varchar(10), EDH.EvnDirectionHTM_directDate, 120) as DirectionDate,
				EDH.MedStaffFact_id as MedPersonal,
				EDH.Diag_id as Diag,
				EDH.LpuSectionProfile_id as DirectionProfile,
				EDH.HTMedicalCareType_id as HTMType,
				EDH.HTMedicalCareClass_id as HTMMethod,
				EDH.PrehospType_did as HospType,
				convert(varchar(10), EDH.EvnDirectionHTM_planDate, 120) as HospPlanDate,
				EDH.EvnDirectionHTM_TalonNum as TicketNumber,
				convert(varchar(10), EDH.EvnDirectionHTM_setDT, 120) as TicketDate,
				EDH.EvnDirectionHTM_VKProtocolNum as VKNumber,
				convert(varchar(10), EDH.EvnDirectionHTM_VKProtocolDate, 120) as VKDate,
				EDH.EvnDirectionHTM_IsHTM as HTMAppType,
				EDH.HTMFinance_id as HTMFinance,
				EDH.HTMOrgDirect_id as HTMOrgDirect
			from v_EvnDirectionHTM EDH (nolock)
			where
				EDH.EvnDirectionHTM_id = :EvnDirectionHTM_id
		", $data);

		return ['json' => json_encode($resp)];
	}
}
