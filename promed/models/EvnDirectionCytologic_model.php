<?php
class EvnDirectionCytologic_model extends swModel {
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
	function deleteEvnDirectionCytologic($data) {
		if(empty($data['EvnDirectionCytologic_id'])) return false;
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirectionCytologic_del
				@EvnDirectionCytologic_id = :EvnDirectionCytologic_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление направления на цитологическое исследование)'));
		}
	}

	/**
	 * Генерация серии и номера
	 */
	function getEvnDirectionCytologicNumber($data) {
		$this->load->model('Numerator_model');
		$val = array();
		$params = array(
			'NumeratorObject_SysName' => 'EvnDirectionCytologic',
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->Numerator_model->getNumeratorNum($params, null);

		if (!empty($resp['Numerator_Num'])) {
			$val['EvnDirectionCytologic_Ser'] = $resp['Numerator_Ser'];
			$val['EvnDirectionCytologic_Num'] = $resp['Numerator_Num'];
			if(!empty($resp['Numerator_id'])) $val['Numerator_id'] = $resp['Numerator_id'];
			return $val;
		} else {
			if (!empty($resp['Error_Msg'])) return array('Error_Msg' => $resp['Error_Msg']);
			else return array('Error_Msg' => 'Не задан активный нумератор для направления на цитологическое исследование. Обратитесь к администратору системы.');
		}
	}


	/**
	 * Получение данных для редактирования
	 */
	function loadEvnDirectionCytologicEditForm($data) {
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			SELECT TOP 1
				'edit' as accessType,
				EDC.Numerator_id,
				EDC.EvnDirectionCytologic_id,
				EDC.EvnDirectionCytologic_pid,
				ECP.EvnCytologicProto_id,
				EDC.EvnDirectionCytologic_id,
				EDC.EvnDirectionCytologic_Ser,
				EDC.EvnDirectionCytologic_Num,
				--EDC.EvnDirectionCytologic_setDate,
				convert(varchar(10), EDC.EvnDirectionCytologic_setDate, 104) as EvnDirectionCytologic_setDate,
				EDC.EvnDirectionCytologic_setTime,
				--EDC.EvnDirectionCytologic_didDate,
				--convert(varchar(10), EDC.EvnDirectionCytologic_didDate, 104) as EvnDirectionCytologic_didDate,
				EDC.Lpu_id,
				EDC.Lpu_sid,
				EDC.Lpu_did,
				EDC.LpuSection_did,
				EDC.LpuSection_id,
				EDC.EvnDirectionCytologic_LpuSectionName,
				EDC.MedPersonal_id,
				EDC.MedPersonal_did,
				EDC.EvnDirectionCytologic_MedPersonalFIO,
				EDC.EvnDirectionCytologic_IsFirstTime, --Тип направления
				EDC.EvnDirectionCytologic_IsCito,
				EDC.PayType_id,
				EDC.EvnDirectionCytologic_NumKVS,
				EDC.EvnPS_id,
				EDC.EvnDirectionCytologic_NumCard,
				EDC.BiopsyReceive_id,
				convert(varchar(10), EDC.EvnDirectionCytologic_MaterialDT, 104) as EvnDirectionCytologic_MaterialDT,
				--UslugaCategory_did   категория услуги
				EDC.UslugaComplex_id,
				UC.UslugaCategory_id,
				EDC.Diag_id,
				EDC.EvnDirectionCytologic_ClinicalDiag,
				EDC.EvnDirectionCytologic_Anamnes,
				EDC.EvnDirectionCytologic_GynecologicAnamnes,
				EDC.EvnDirectionCytologic_Data,
				EDC.EvnDirectionCytologic_OperTherapy,
				EDC.EvnDirectionCytologic_RadiationTherapy,
				EDC.EvnDirectionCytologic_ChemoTherapy,
				EDC.Person_id,
				EDC.PersonEvn_id,
				EDC.Server_id,
				EDC.MedService_id,
				MS.MedServiceType_id
				
				--BSTL.BiopsyStudyType_ids
			FROM
				v_EvnDirectionCytologic EDC with (nolock)
				left join v_UslugaComplex UC with (nolock) ON UC.UslugaComplex_id = EDC.UslugaComplex_id
				left join v_UslugaCategory UCAT with (nolock) on UCAT.UslugaCategory_id = UC.UslugaCategory_id
				left join v_MedService MS with(nolock) on MS.MedService_id = EDC.MedService_id
				outer apply (
					select top 1
						EvnCytologicProto_id
					from
						v_EvnCytologicProto with (nolock)
					where
						EvnDirectionCytologic_id = EDC.EvnDirectionCytologic_id
				) ECP				
			WHERE (1 = 1)
				and EDC.EvnDirectionCytologic_id = :EvnDirectionCytologic_id
				and (EDC.Lpu_did = :Lpu_id or EDC.Lpu_id = :Lpu_id)
		";
		$result = $this->db->query($query, array(
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id'],
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
	function loadEvnDirectionCytologicGrid($data) {
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$filter = "(EDC.Lpu_id = :Lpu_id or EDC.Lpu_did = :Lpu_id)";
		if ( isset($data['begDate']) ) {
			$filter .= " and EDC.EvnDirectionCytologic_setDate >= cast(:begDate as datetime)";
			$queryParams['begDate'] = $data['begDate'];
		}

		if ( isset($data['endDate']) ) {
			$filter .= " and EDC.EvnDirectionCytologic_setDate <= cast(:endDate as datetime)";
			$queryParams['endDate'] = $data['endDate'];
		}
		
		if( isset($data['EvnDirectionCytologic_IsCito']) ){
			$filter .= " and isnull(EDC.EvnDirectionCytologic_IsCito, 1) = :EvnDirectionCytologic_IsCito";
			$queryParams['EvnDirectionCytologic_IsCito'] = $data['EvnDirectionCytologic_IsCito'];
		}
		
		if( isset($data['EvnDirectionCytologic_Ser'])) {
			$filter .= " and EDC.EvnDirectionCytologic_Ser = :EvnDirectionCytologic_Ser";
			$queryParams['EvnDirectionCytologic_Ser'] = $data['EvnDirectionCytologic_Ser'];
		}
		
		if( isset($data['EvnDirectionCytologic_Num'])) {
			$filter .= " and EDC.EvnDirectionCytologic_Num = :EvnDirectionCytologic_Num";
			$queryParams['EvnDirectionCytologic_Num'] = $data['EvnDirectionCytologic_Num'];
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
		
		if ( isset($data['EvnType_id']) && $data['EvnType_id']>1 ) {
			if($data['EvnType_id'] == 3){
				$filter .= " AND EDC.DirFailType_id is not null";
			}else if($data['EvnType_id'] == 2){
				$filter .= " AND EDC.DirFailType_id is null";
			}
		}
		//$this->load->helper('MedStaffFactLink');
		//$med_personal_list = getMedPersonalListWithLinks();
		
		$query = "
			select
				-- select
				'edit'as accessType,
				EDC.EvnDirectionCytologic_id,
				EHP.EvnCytologicProto_id,
				EDC.Person_id,
				EDC.PersonEvn_id,
				EDC.DirFailType_id,
				EDC.EvnStatus_id,
				EDC.Server_id,
				case when EHP.EvnCytologicProto_id is not null then 'true' else 'false' end as EvnDirectionCytologic_HasProto,
				EDC.EvnDirectionCytologic_Ser,
				EDC.EvnDirectionCytologic_Num,
				convert(varchar(10), EDC.EvnDirectionCytologic_setDT, 104) as EvnDirectionCytologic_setDate,
				RTRIM(ISNULL(LS.LpuSection_Name, EDC.EvnDirectionCytologic_LpuSectionName)) as LpuSection_Name,
				RTRIM(MP.Person_Fio) as MedPersonal_Fio,
				EDC.EvnDirectionCytologic_NumCard,
				PS.Person_Surname,
				PS.Person_Firname,
				PS.Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(Lpu.Lpu_Nick) as Lpu_Name,
				EDC.EvnDirectionCytologic_IsFirstTime,
				case when EDC.EvnDirectionCytologic_IsCito = 2 then 'Да' else 'Нет' end as EvnDirectionCytologic_IsCito
				-- end select
			from
				-- from
				v_PersonState PS with (nolock)
				inner join v_EvnDirectionCytologic EDC with (nolock) on EDC.Person_id = PS.Person_id
				left join LpuSection LS with (nolock) on LS.LpuSection_id = EDC.LpuSection_did
				left join v_MedPersonal MP with (nolock) on MP.MedPersonal_id = EDC.MedPersonal_did and MP.Lpu_id = EDC.Lpu_id				
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDC.Lpu_did
				outer apply(
					select top 1 EvnCytologicProto_id
					from v_EvnCytologicProto with (nolock)
					where EvnDirectionCytologic_id = EDC.EvnDirectionCytologic_id
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
	 * Получение списка направлений
	 */
	function loadEvnDirectionCytologicList($data) {
		$filter = '(1 = 1)';
		$queryParams = array();

		$filter .= " and EDC.Person_id = :Person_id";
		$queryParams['Person_id'] = $data['Person_id'];

		if ( $data['Lpu_id'] > 0 ) {
			$filter .= " and EDC.Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				EDC.EvnDirectionCytologic_id,
				RTRIM(ISNULL(EDC.EvnDirectionCytologic_Ser, '')) as EvnDirectionCytologic_Ser,
				RTRIM(ISNULL(EDC.EvnDirectionCytologic_Num, '')) as EvnDirectionCytologic_Num,
				EDC.UslugaComplex_id,
				EDC.PayType_id,
				convert(varchar(10), EDC.EvnDirectionCytologic_setDT, 104) as EvnDirectionCytologic_setDate
			from v_EvnDirectionCytologic EDC with (nolock)
				outer apply (
					select top 1
						EvnCytologicProto_id
					from v_EvnCytologicProto with (nolock)
					where EvnDirectionCytologic_id = EDC.EvnDirectionCytologic_id
				) EHP
			where " . $filter . "
				and EHP.EvnCytologicProto_id is null
			order by
				EDC.EvnDirectionCytologic_setDT desc
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
	public function saveEvnDirectionCytologic($data) {
		$response = array(array('Error_Msg' => ''));
		
		try {
			$this->beginTransaction();
			
			if ( isset($data['EvnDirectionCytologic_setTime']) ) {
				$data['EvnDirectionCytologic_setDate'] .= ' ' . $data['EvnDirectionCytologic_setTime'] . ':00.000';
			}
			
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnDirectionCytologic_id;
				exec p_EvnDirectionCytologic_" . (!empty($data['EvnDirectionCytologic_id']) ? "upd" : "ins") . "
					@EvnDirectionCytologic_id = @Res output,
					@Numerator_id = :Numerator_id,
					@EvnDirectionCytologic_pid = :EvnDirectionCytologic_pid,
					@EvnDirectionCytologic_Ser = :EvnDirectionCytologic_Ser,
					@EvnDirectionCytologic_Num = :EvnDirectionCytologic_Num,
					@EvnDirectionCytologic_setDT = :EvnDirectionCytologic_setDT,
					@Lpu_id = :Lpu_id,
					@Lpu_sid = :Lpu_sid,
					@Lpu_did = :Lpu_did,
					@LpuSection_did = :LpuSection_did,
					@LpuSection_id = :LpuSection_id,
					@EvnDirectionCytologic_IsCito = :EvnDirectionCytologic_IsCito,
					@PayType_id = :PayType_id,
					@EvnDirectionCytologic_NumKVS = :EvnDirectionCytologic_NumKVS,
					@EvnDirectionCytologic_NumCard = :EvnDirectionCytologic_NumCard,
					@BiopsyReceive_id = :BiopsyReceive_id,
					@EvnDirectionCytologic_MaterialDT = :EvnDirectionCytologic_MaterialDT,
					@UslugaComplex_id = :UslugaComplex_id,
					@DirType_id = 29,
					@Diag_id = :Diag_id,
					@EvnDirectionCytologic_ClinicalDiag = :EvnDirectionCytologic_ClinicalDiag,
					@EvnDirectionCytologic_Anamnes = :EvnDirectionCytologic_Anamnes,
					@EvnDirectionCytologic_GynecologicAnamnes = :EvnDirectionCytologic_GynecologicAnamnes,
					@EvnDirectionCytologic_Data = :EvnDirectionCytologic_Data,
					@EvnDirectionCytologic_OperTherapy = :EvnDirectionCytologic_OperTherapy,
					@EvnDirectionCytologic_RadiationTherapy = :EvnDirectionCytologic_RadiationTherapy,
					@EvnDirectionCytologic_ChemoTherapy = :EvnDirectionCytologic_ChemoTherapy,
					@MedPersonal_id = :MedPersonal_id,
					@MedPersonal_did = :MedPersonal_did,
					@EvnPS_id = :EvnPS_id,
					@EvnStatus_id = :EvnStatus_id,
					@EvnDirectionCytologic_IsFirstTime = :EvnDirectionCytologic_IsFirstTime,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@DirFailType_id = :DirFailType_id,
					@pmUser_id = :pmUser_id,
					@EvnDirectionCytologic_LpuSectionName = :EvnDirectionCytologic_LpuSectionName,
					@EvnDirectionCytologic_MedPersonalFIO = :EvnDirectionCytologic_MedPersonalFIO,
					@MedService_id = :MedService_id,

					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnDirectionCytologic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
			$server_id = $this->getServerID($data);
			$queryParams = array(
				'EvnDirectionCytologic_id' => (!empty($data['EvnDirectionCytologic_id'])) ? $data['EvnDirectionCytologic_id'] : null,
				'EvnDirectionCytologic_pid' => (!empty($data['EvnDirectionCytologic_pid'])) ? $data['EvnDirectionCytologic_pid'] : null,
				'Numerator_id' => (!empty($data['Numerator_id'])) ? $data['Numerator_id'] : null,
				'EvnDirectionCytologic_Ser' => $data['EvnDirectionCytologic_Ser'],
				'EvnDirectionCytologic_Num' => $data['EvnDirectionCytologic_Num'],
				'EvnDirectionCytologic_setDT' => $data['EvnDirectionCytologic_setDate'],
				'Lpu_id' => $data['Lpu_id'],
				'Lpu_sid' => (!empty($data['Lpu_sid'])) ? $data['Lpu_sid'] : null,
				'Lpu_did' => $data['Lpu_did'],
				'LpuSection_did' => $data['LpuSection_did'],
				'LpuSection_id' => $data['LpuSection_id'],
				'EvnDirectionCytologic_IsCito' => (!empty($data['EvnDirectionCytologic_IsCito']) && ($data['EvnDirectionCytologic_IsCito'] == 'on' || $data['EvnDirectionCytologic_IsCito'] == '2')) ? 2 : 1,
				'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : null,
				'EvnPS_id' => (!empty($data['EvnPS_id'])) ? $data['EvnPS_id'] : null,
				'EvnDirectionCytologic_NumKVS' => (!empty($data['EvnDirectionCytologic_NumKVS'])) ? $data['EvnDirectionCytologic_NumKVS'] : null,
				'EvnDirectionCytologic_NumCard' => (!empty($data['EvnDirectionCytologic_NumCard'])) ? $data['EvnDirectionCytologic_NumCard'] : null,
				'BiopsyReceive_id' => (!empty($data['BiopsyReceive_id'])) ? $data['BiopsyReceive_id'] : null,
				'EvnDirectionCytologic_MaterialDT' => (!empty($data['EvnDirectionCytologic_MaterialDT'])) ? $data['EvnDirectionCytologic_MaterialDT'] : null,
				'UslugaComplex_id' => (!empty($data['UslugaComplex_id'])) ? $data['UslugaComplex_id'] : null,
				'Diag_id' => (!empty($data['Diag_id']) ? $data['Diag_id'] : NULL),
				'EvnDirectionCytologic_ClinicalDiag' => (!empty($data['EvnDirectionCytologic_ClinicalDiag'])) ? $data['EvnDirectionCytologic_ClinicalDiag'] : null,
				'EvnDirectionCytologic_Anamnes' => (!empty($data['EvnDirectionCytologic_Anamnes'])) ? $data['EvnDirectionCytologic_Anamnes'] : null,
				'EvnDirectionCytologic_GynecologicAnamnes' => (!empty($data['EvnDirectionCytologic_GynecologicAnamnes'])) ? $data['EvnDirectionCytologic_GynecologicAnamnes'] : null,
				'EvnDirectionCytologic_Data' => (!empty($data['EvnDirectionCytologic_Data'])) ? $data['EvnDirectionCytologic_Data'] : null,
				'EvnDirectionCytologic_OperTherapy' => (!empty($data['EvnDirectionCytologic_OperTherapy'])) ? $data['EvnDirectionCytologic_OperTherapy'] : null,
				'EvnDirectionCytologic_RadiationTherapy' => (!empty($data['EvnDirectionCytologic_RadiationTherapy'])) ? $data['EvnDirectionCytologic_RadiationTherapy'] : null,
				'EvnDirectionCytologic_ChemoTherapy' => (!empty($data['EvnDirectionCytologic_ChemoTherapy'])) ? $data['EvnDirectionCytologic_ChemoTherapy'] : null,
				'MedPersonal_id' => $data['MedPersonal_id'],
				'MedPersonal_did' => $data['MedPersonal_did'],
				'Server_id' => (isset($server_id)) ? $server_id : $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnDirectionCytologic_IsFirstTime' => (!empty($data['EvnDirectionCytologic_IsFirstTime'])) ? $data['EvnDirectionCytologic_IsFirstTime'] : 1,
				'EvnStatus_id' => (!empty($data['EvnStatus_id'])) ? $data['EvnStatus_id'] : null,
				'DirFailType_id' => (!empty($data['DirFailType_id'])) ? $data['DirFailType_id'] : null,
				'EvnDirectionCytologic_LpuSectionName' => (!empty($data['EvnDirectionCytologic_LpuSectionName'])) ? $data['EvnDirectionCytologic_LpuSectionName'] : null,
				'EvnDirectionCytologic_MedPersonalFIO' => (!empty($data['EvnDirectionCytologic_MedPersonalFIO'])) ? $data['EvnDirectionCytologic_MedPersonalFIO'] : null,
				'MedService_id' => (!empty($data['MedService_id'])) ? $data['MedService_id'] : null,
				'pmUser_id' => $data['pmUser_id']
			);
			//echo getDebugSQL($query, $queryParams); die();
			$response = $this->queryResult($query, $queryParams);

			if ( $response === false ) {
				throw new Exception('Ошибка при выполнении запроса к БД');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				throw new Exception($response[0]['Error_Msg']);
			}

			$EvnDirectionCytologic_id = $response[0]['EvnDirectionCytologic_id'];
			$data['EvnDirectionCytologic_id'] = $EvnDirectionCytologic_id;
			
			$resSaveVolumeAndMacroscopicDescription = $this->saveVolumeAndMacroscopicDescriptionData($data);
			$resSaveLocalizationNatureProcessAndMethod = $this->saveLocalizationNatureProcessAndMethodData($data);
			
			$this->commitTransaction();
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}
	
	/**
	 * Получение server_id по PersonEvn_id
	 * без верного server_id процедуры могут выдавать ошибки типа p_EvnDirectionCytologic_ins : Не найден человек с переданным PersonEvn
	 */
	private function getServerID($data) {
		if(empty($data['PersonEvn_id'])) return false;
		$query = "
			SELECT server_id FROM  v_personevn WHERE  PersonEvn_id = :PersonEvn_id
		";
		
		$res = $this->getFirstRowFromQuery($query, $data);
		if(empty($res['server_id'])){
			return false;
		}else{
			return $res['server_id'];
		}
	}
	
	/**
	 * Сохранение списка данных с блока "Описание биологического материала"
	 */
	function saveVolumeAndMacroscopicDescriptionData($data){
		if(empty($data['VolumeAndMacroscopicDescriptionData']) || !is_array($data['VolumeAndMacroscopicDescriptionData']) || empty($data['EvnDirectionCytologic_id'])) return false;
		foreach ( $data['VolumeAndMacroscopicDescriptionData'] as $row ) {
			if ( $row['RecordStatus_Code'] == 1 || empty($row['MacroMaterialCytologic_id']) ) {
				continue;
			}

			if ( $row['MacroMaterialCytologic_id'] <= 0 ) {
				$row['MacroMaterialCytologic_id'] = null;
			}

			$row['EvnDirectionCytologic_id'] = $data['EvnDirectionCytologic_id'];
			$row['pmUser_id'] = $data['pmUser_id'];

			switch ( $row['RecordStatus_Code'] ) {
				case 0:
				case 2:
					$queryResponse = $this->saveVolumeAndMacroscopicDescription($row);
				break;

				case 3:
					$queryResponse = $this->deleteVolumeAndMacroscopicDescription($row);
				break;
			}

			if ( !is_array($queryResponse) ) {
				throw new Exception('Ошибка при ' . ($row['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' данных описания биологического материала');
			}
			else if ( !empty($queryResponse[0]['Error_Msg']) ) {
				throw new Exception($queryResponse[0]['Error_Msg']);
			}
		}
	}
	
	/**
	 * сохранение данных описания биологического материала
	 */
	function saveVolumeAndMacroscopicDescription($data){
		if(empty($data['EvnDirectionCytologic_id'])) return false;
		$result = $this->queryResult("
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :MacroMaterialCytologic_id;

			exec p_MacroMaterialCytologic_" . (!empty($data['MacroMaterialCytologic_id']) ? 'upd' : 'ins') . "
				@MacroMaterialCytologic_id = @Res output,
				@EvnDirectionCytologic_id = :EvnDirectionCytologic_id,
				
				@MacroMaterialCytologic_Mark = :MacroMaterialCytologic_Mark,
				@MacroMaterialCytologic_Size = :MacroMaterialCytologic_Size,
				@MacroMaterialCytologic_CountObject = :MacroMaterialCytologic_CountObject,
				@BiologycalMaterialType_id = :BiologycalMaterialType_id,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as MacroMaterialCytologic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'MacroMaterialCytologic_id' => (!empty($data['MacroMaterialCytologic_id']) ? $data['MacroMaterialCytologic_id'] : null),
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id'],
			'MacroMaterialCytologic_Mark' => $data['MacroMaterialCytologic_Mark'],
			'MacroMaterialCytologic_Size' => $data['MacroMaterialCytologic_Size'],
			'MacroMaterialCytologic_CountObject' => $data['MacroMaterialCytologic_CountObject'],
			'BiologycalMaterialType_id' => $data['BiologycalMaterialType_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
		
		return $result;
	}
	
	/**
	 * удаление данных описания биологического материала
	 */
	function deleteVolumeAndMacroscopicDescription($data){
		if(empty($data['MacroMaterialCytologic_id'])) return false;
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_MacroMaterialCytologic_del
				@MacroMaterialCytologic_id = :MacroMaterialCytologic_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'MacroMaterialCytologic_id' => $data['MacroMaterialCytologic_id']
		));
	}
	
	/**
	 * Получение списка записей для раздела "Объем и макроскопическое описание материала"
	 */
	public function loadVolumeAndMacroscopicDescriptionGrid($data) {
		if(empty($data['EvnDirectionCytologic_id'])) return array();
		return $this->queryResult("
			select
				MMC.EvnDirectionCytologic_id,
				MMC.MacroMaterialCytologic_id,
				MMC.BiologycalMaterialType_id,
				MMC.MacroMaterialCytologic_Mark,
				MMC.MacroMaterialCytologic_Size,
				MMC.MacroMaterialCytologic_CountObject,
				BMT.BiologycalMaterialType_Name,
				1 as RecordStatus_Code
			from
				v_MacroMaterialCytologic MMC with (nolock)
				LEFT JOIN v_BiologycalMaterialType BMT WITH(nolock) ON BMT.BiologycalMaterialType_id = MMC.BiologycalMaterialType_id
			where
				MMC.EvnDirectionCytologic_id = :EvnDirectionCytologic_id
		", array(
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id']
		));
	}
	
	/**
	 * Сохранение списка данных с блока "Локализация, характер процесса и способ получения материала"
	 */
	function saveLocalizationNatureProcessAndMethodData($data){
		if(empty($data['LocalizationNatureProcessAndMethodData']) || !is_array($data['LocalizationNatureProcessAndMethodData']) || empty($data['EvnDirectionCytologic_id'])) return false;
		foreach ( $data['LocalizationNatureProcessAndMethodData'] as $row ) {
			if ( $row['RecordStatus_Code'] == 1 || empty($row['LocalProcessCytologic_id']) ) {
				continue;
			}

			if ( $row['LocalProcessCytologic_id'] <= 0 ) {
				$row['LocalProcessCytologic_id'] = null;
			}

			$row['EvnDirectionCytologic_id'] = $data['EvnDirectionCytologic_id'];
			$row['pmUser_id'] = $data['pmUser_id'];

			switch ( $row['RecordStatus_Code'] ) {
				case 0:
				case 2:
					$queryResponse = $this->saveLocalizationNatureProcessAndMethod($row);
				break;

				case 3:
					$queryResponse = $this->deleteLocalizationNatureProcessAndMethod($row);
				break;
			}

			if ( !is_array($queryResponse) ) {
				throw new Exception('Ошибка при ' . ($row['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' данных локализации, характера процесса и способ получения материала');
			}
			else if ( !empty($queryResponse[0]['Error_Msg']) ) {
				throw new Exception($queryResponse[0]['Error_Msg']);
			}
		}
	}
	
	/**
	 * сохранение данных Локализация, характер процесса и способ получения материала
	 */
	function saveLocalizationNatureProcessAndMethod($data){
		if(empty($data['EvnDirectionCytologic_id'])) return false;
		$result = $this->queryResult("
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :LocalProcessCytologic_id;

			exec p_LocalProcessCytologic_" . (!empty($data['LocalProcessCytologic_id']) ? 'upd' : 'ins') . "
				@LocalProcessCytologic_id = @Res output,
				@EvnDirectionCytologic_id = :EvnDirectionCytologic_id,
				
				@PathologicProcessType_id = :PathologicProcessType_id,
				@LocalProcessCytologic_FeatureForm = :LocalProcessCytologic_FeatureForm,
				@LocalProcessCytologic_Localization = :LocalProcessCytologic_Localization,
				@BiopsyReceive_id = :BiopsyReceive_id,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as LocalProcessCytologic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'LocalProcessCytologic_id' => (!empty($data['LocalProcessCytologic_id']) ? $data['LocalProcessCytologic_id'] : null),
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id'],
			'PathologicProcessType_id' => $data['PathologicProcessType_id'],
			'LocalProcessCytologic_FeatureForm' => $data['LocalProcessCytologic_FeatureForm'],
			'LocalProcessCytologic_Localization' => $data['LocalProcessCytologic_Localization'],
			'BiopsyReceive_id' => (!empty($data['BiopsyReceive_id'])) ? $data['BiopsyReceive_id'] : null,
			'pmUser_id' => $data['pmUser_id'],
		));
		
		return $result;
	}
	
	/**
	 * удаление данных описания Локализация, характер процесса и способ получения материала
	 */
	function deleteLocalizationNatureProcessAndMethod($data){
		if(empty($data['LocalProcessCytologic_id'])) return false;
		return $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_LocalProcessCytologic_del
				@LocalProcessCytologic_id = :LocalProcessCytologic_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'LocalProcessCytologic_id' => $data['LocalProcessCytologic_id']
		));
	}
	
	/**
	 * Получение списка записей для раздела "Локализация, характер процесса и способ получения материала"
	 */
	public function loadLocalizationNatureProcessAndMethodGrid($data) {
		if(empty($data['EvnDirectionCytologic_id'])) return array();
		return $this->queryResult("
			select
				LPC.EvnDirectionCytologic_id,
				LPC.LocalProcessCytologic_id,
				LPC.PathologicProcessType_id,
				LPC.BiopsyReceive_id,
				LPC.LocalProcessCytologic_FeatureForm,
				LPC.LocalProcessCytologic_Localization,
				PPT.PathologicProcessType_Name,
				BR.BiopsyReceive_Name,
				1 as RecordStatus_Code
			from
				v_LocalProcessCytologic LPC with (nolock)
				LEFT JOIN v_PathologicProcessType PPT WITH(nolock) ON LPC.PathologicProcessType_id = PPT.PathologicProcessType_id
				LEFT JOIN v_BiopsyReceive BR WITH (NOLOCK) ON BR.BiopsyReceive_id = LPC.BiopsyReceive_id
			where
				LPC.EvnDirectionCytologic_id = :EvnDirectionCytologic_id
		", array(
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id']
		));
	}
	
	/**
	 * проверка номера направления
	 */
	function directionNumberCheck($data){
		if(empty($data['Numerator_id'])) return array('Error_Msg' => 'Не указан идентификатор нумератора');
		if(empty($data['EvnDirectionCytologic_Ser'])) return array('Error_Msg' => 'Не укзана серия направления');
		if(empty($data['EvnDirectionCytologic_Num'])) return array('Error_Msg' => 'Не указан номер направления');
		
		//		$data['Numerator_id'] = 9650;
		//		$data['EvnDirectionCytologic_Ser'] = 570500;
		//		$data['EvnDirectionCytologic_Num'] = 29;
		
		$result = $this->queryResult("
			select top 1
				EDC.EvnDirectionCytologic_id,
				convert(varchar(10), EDC.EvnDirectionCytologic_setDT, 104) as EvnDirectionCytologic_setDate
			from
				v_EvnDirectionCytologic EDC with (nolock)
			where 1=1
				AND EDC.Numerator_id = :Numerator_id
				AND EDC.EvnDirectionCytologic_Ser = :EvnDirectionCytologic_Ser
				AND EDC.EvnDirectionCytologic_Num = :EvnDirectionCytologic_Num
		", array(
			'Numerator_id' => $data['Numerator_id'],
			'EvnDirectionCytologic_Ser' => $data['EvnDirectionCytologic_Ser'],
			'EvnDirectionCytologic_Num' => $data['EvnDirectionCytologic_Num']
		));
		
		if (is_array($result) && count($result) > 0 && !empty($result[0]['EvnDirectionCytologic_id'])) {
			return array(array('Error_Msg' => 'В рамках серии '.$data['EvnDirectionCytologic_Ser'].' уже существует направление с номером '.$data['EvnDirectionCytologic_Num'].' от '.$result[0]['EvnDirectionCytologic_setDate'].' числа'));
		}else{
			return false;
		}
	}
	
	/**
	 * Загрузка раздела "Обследование" формы Направление на цитологическое диагностическое исследование
	 */
	function loadProcessingResultsGrid($data){
		if(empty($data['EvnDirectionCytologic_pid'])) return array();	
		/*
		echo getDebugSQL("
			SELECT 
				EU.EvnUsluga_id,
				EU.EvnUsluga_pid,
				UCAC.UslugaComplexAttributeType_Code,
				SR.StudyResult_Name,
				convert(varchar(10), EU.EvnUsluga_setDT, 104) as EvnUsluga_setDT,
				UC.UslugaComplex_Code + '. ' + UC.UslugaComplex_Name AS UslugaComplex_CodeName,
				Usluga.Usluga_Code + '. ' + Usluga.Usluga_Name AS Usluga_CodeName
			FROM v_EvnUsluga EU with (nolock)
				LEFT JOIN EvnUslugaPar EUP  with (nolock) on EUP.EvnUsluga_id = EU.EvnUsluga_id
				LEFT JOIN v_StudyResult SR with (nolock) on SR.StudyResult_id = EUP.StudyResult_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
				OUTER APPLY(
					SELECT TOP 1 UCAT.UslugaComplexAttributeType_Code
					from v_UslugaComplexAttribute uca with (nolock)
						inner join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						inner join v_AttributeValueType avt with (nolock) on avt.AttributeValueType_id = ucat.AttributeValueType_id
						left join pmUserCache pu with (nolock) on pu.pmUser_id = uca.pmUser_updID
					where
						uca.UslugaComplex_id = EU.UslugaComplex_id
				) UCAC
			WHERE 1=1
				and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
				--AND EU.EvnUsluga_pid = :EvnUsluga_pid
				AND EU.Lpu_id = :Lpu_id
				--AND EU.Person_id = :Person_id
				AND EU.Person_id = 3727637
				AND UCAC.UslugaComplexAttributeType_Code IN (8,9, 13)
		", array(
			'EvnUsluga_pid' => $data['EvnDirectionCytologic_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		)); die();
		*/
		$ss = $this->queryResult("
			SELECT top 10
				EU.EvnUsluga_id,
				EU.EvnUsluga_pid,
				UCAC.UslugaComplexAttributeType_Code,
				SR.StudyResult_Name,
				convert(varchar(10), EU.EvnUsluga_setDT, 104) as EvnUsluga_setDT,
				UC.UslugaComplex_Code + '. ' + UC.UslugaComplex_Name AS UslugaComplex_CodeName,
				Usluga.Usluga_Code + '. ' + Usluga.Usluga_Name AS Usluga_CodeName
			FROM v_EvnUsluga EU with (nolock)
				LEFT JOIN EvnUslugaPar EUP  with (nolock) on EUP.EvnUsluga_id = EU.EvnUsluga_id
				LEFT JOIN v_StudyResult SR with (nolock) on SR.StudyResult_id = EUP.StudyResult_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
				left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
				OUTER APPLY(
					SELECT TOP 1 UCAT.UslugaComplexAttributeType_Code
					from v_UslugaComplexAttribute uca with (nolock)
						inner join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
						inner join v_AttributeValueType avt with (nolock) on avt.AttributeValueType_id = ucat.AttributeValueType_id
						left join pmUserCache pu with (nolock) on pu.pmUser_id = uca.pmUser_updID
					where
						uca.UslugaComplex_id = EU.UslugaComplex_id
				) UCAC
			WHERE 1=1
				--and ISNULL(EU.EvnUsluga_IsVizitCode, 1) = 1
				--AND EU.EvnUsluga_pid = :EvnUsluga_pid
				--AND EU.Lpu_id = :Lpu_id
				--AND EU.Person_id = :Person_id
				--AND EU.Person_id = 3727637
				--AND UCAC.UslugaComplexAttributeType_Code IN (8,9, 13)
		", array(
			'EvnUsluga_pid' => $data['EvnDirectionCytologic_pid'],
			'Lpu_id' => $data['Lpu_id'],
			'Person_id' => $data['Person_id']
		));
		//$ss = $this->queryResult("SELECT top 10 * FROM v_EvnUsluga EU");
		return $ss;
	}
	
	/**
	 * Отмена направления
	 */
	function cancelEvnDirectionCytologic($data){
		if(empty($data['EvnDirectionCytologic_id']) || empty($data['EvnDirectionCytologic_id']) || empty($data['EvnDirectionCytologic_id'])) return false;
		try {
			$this->beginTransaction();
			$query =  "
				declare
					@Res bigint,
					@ErrCode int,
					@date datetime = dbo.tzGetDate(),
					@ErrMessage varchar(4000);

				exec p_EvnStatusHistory_ins
					@EvnStatusHistory_id = @res output,
					@Evn_id = :Evn_id,
					@EvnStatus_id = 13,					-- отклонено
					@EvnStatusHistory_begDate = @date,
					@EvnStatusCause_id = :EvnStatusCause_id,
					@EvnStatusHistory_Cause = :EvnStatusHistory_Cause,
					@pmUser_id = :pmUser_id,

					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnStatusHistory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'Evn_id' => $data['EvnDirectionCytologic_id'],
				'EvnStatusCause_id' => $data['EvnStatusCause_id'],
				'EvnStatusHistory_Cause' => (!empty($data['EvnStatusHistory_Cause'])) ? $data['EvnStatusHistory_Cause'] : null,
				'pmUser_id' => $data['pmUser_id']
			);
			
			$res = $this->db->query($query, $queryParams);
			if(!is_object($res)) {
				throw new Exception('Ошибка при выполнении запроса к базе данных (отмена направления)');
			} 
			$result = $res->result('array');

			if(!empty($result['Error_Code'])) {
				throw new Exception($result['Error_Msg']);	
			}
			
			//ставим статус в самом направлении
			$direction = $this->getEvnDirectionCytologic($data);
			if(!$direction || isset($direction[0]['Error_Msg'])){
				throw new Exception('Ошибка при получении данных по направлению');
			}
			$direction['EvnStatus_id'] = 13; //отклонено
			$direction['DirFailType_id'] = 11; //Ошибочное направление
			$resSaveStatus = $this->saveEvnDirectionCytologic($direction);
			if(!$resSaveStatus || empty($resSaveStatus[0]['EvnDirectionCytologic_id'])){
				$errorMsg = (!empty($resSaveStatus[0]['Error_Msg'])) ? $resSaveStatus[0]['Error_Msg'] : 'Ошибка при выполнении запроса к базе данных (статус отменено)';
				throw new Exception($errorMsg);
			}				
				
			$response = array(array(
				'success' => true,
				'EvnStatusHistory_id' => $result[0]['EvnStatusHistory_id'],
				'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id']
			));
			
			$this->commitTransaction();

		} catch (Exception $e) {
			$response = array(array('success' => false, 'Error_Msg' => $e->getMessage()));
			$this->rollbackTransaction();
		}

		return $response;
	}
	
	/**
	 * Получение данных по направлению
	 */
	function getEvnDirectionCytologic($data){
		if(empty($data['EvnDirectionCytologic_id'])) return false;
		$ret = array();
		$query = "
			select top 1
				EDC.EvnDirectionCytologic_id,
				EDC.Numerator_id,
				EDC.EvnDirectionCytologic_pid,
				RTRIM(ISNULL(EDC.EvnDirectionCytologic_Ser, '')) AS EvnDirectionCytologic_Ser,
				RTRIM(ISNULL(EDC.EvnDirectionCytologic_Num, '')) AS EvnDirectionCytologic_Num,
				CONVERT(VARCHAR(10), EDC.EvnDirectionCytologic_setDT, 120) AS EvnDirectionCytologic_setDT,
				CONVERT(VARCHAR(10), EDC.EvnDirectionCytologic_setDate, 120) AS EvnDirectionCytologic_setDate,
				EDC.Lpu_id,
				EDC.Lpu_sid, 
				EDC.Lpu_did,
				EDC.LpuSection_did,
				EDC.LpuSection_id,
				EDC.EvnDirectionCytologic_IsCito,
				EDC.PayType_id,
				EDC.EvnDirectionCytologic_NumKVS,
				EDC.EvnDirectionCytologic_NumCard,
				EDC.BiopsyReceive_id,
				CONVERT(VARCHAR(10), EDC.EvnDirectionCytologic_MaterialDT, 120) AS EvnDirectionCytologic_MaterialDT,
				EDC.UslugaComplex_id,
				EDC.DirType_id,
				EDC.Diag_id,
				EDC.EvnDirectionCytologic_ClinicalDiag,
				EDC.EvnDirectionCytologic_Anamnes,
				EDC.EvnDirectionCytologic_GynecologicAnamnes,
				EDC.EvnDirectionCytologic_Data,
				EDC.EvnDirectionCytologic_OperTherapy,
				EDC.EvnDirectionCytologic_RadiationTherapy,
				EDC.EvnDirectionCytologic_ChemoTherapy,
				EDC.MedPersonal_id,
				EDC.MedPersonal_did,
				EDC.EvnPS_id,
				EDC.Server_id,
				EDC.PersonEvn_id,
				EDC.EvnStatus_id,
				EDC.EvnDirectionCytologic_IsFirstTime,
				ISNULL(EDC.pmUser_updID, EDC.pmUser_insID) as pmUser_id
			from v_EvnDirectionCytologic EDC with (nolock)
			where EDC.EvnDirectionCytologic_id = :EvnDirectionCytologic_id
		";

		$queryParams = array(
			'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id']
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
		
		return $res[0];
	}
	
	/**
	 * Загрузка услуг
	 */
	function loadUslugaList($data){
		if(empty($data['EvnDirectionCytologic_pid'])) return false;
		$result = array();
		$resOnkoChem = array();
		$resUsluga = array();
		
		//оперативное лечение
		$result['oper'] = $this->getUslugaList($data, 'oper');
		//лучевое лечение
		$result['ray'] = $this->getUslugaList($data, 'ray');
		//химиотерапия
		$result['onkochem'] = $this->getUslugaList($data, 'onkochem');
		if ( $result['onkochem'] && is_array($result['onkochem']) && count($result['onkochem']) > 0 ) {
			//получаем препараты
			$this->load->model('MorbusOnkoDrug_model');
			foreach ($result['onkochem'] as $key => $value) {
				$resPrep = $this->MorbusOnkoDrug_model->readList(array('Evn_id' => $value['EvnUsluga_id']));
				$result['onkochem'][$key]['prep'] = $resPrep;
			}
		}		
		return array($result);
	}
	
	/**
	 * Загрузка услуг
	 */
	function getUslugaList($data, $sysnick = false){
		if(empty($data['EvnDirectionCytologic_pid'])) return false;
		$fields = '';
		$join = '';
		$where = '';
		$nick = '';
		$res = array();
		
		if($sysnick == 'oper'){
			$fields .= '
				,convert(varchar(10), EUO.EvnUslugaOper_setDate, 104) as EvnUslugaOper_setDate
				,OT.OperType_Name
			';
			$join .= '
				LEFT JOIN v_EvnUslugaOper EUO with (nolock) ON EUO.EvnUslugaOper_id = EU.EvnUsluga_id
				LEFT JOIN v_OperType OT  with (nolock) ON EUO.OperType_id = OT.OperType_id
			';
			$where .= " AND EUO.EvnUslugaOper_setDate IS NOT NULL AND EU.EvnClass_SysNick in ('EvnUslugaOper') ";
			$nick = "'oper'";
		}else if($sysnick == 'ray'){
			$fields = "
				,OUBIT.OnkoUslugaBeamIrradiationType_Name --Способ облучения
				,convert(varchar(10), EUOB.EvnUslugaOnkoBeam_setDate, 104) as EvnUslugaOnkoBeam_setDate --Дата выполнения
				,OUBFT.OnkoUslugaBeamFocusType_Name --Преимущественная направленность
				,STR(EUOB.EvnUslugaOnkoBeam_TotalDoseTumor) + ' ' + OUBUT.OnkoUslugaBeamUnitType_Name AS EvnUslugaOnkoBeam_TotalDoseTumor	--Суммарная доза облучения опухоли
				,STR(EUOB.EvnUslugaOnkoBeam_TotalDoseRegZone) + ' ' + OUBUT_ZONE.OnkoUslugaBeamUnitType_Name AS EvnUslugaOnkoBeam_TotalDoseRegZone  --Суммарная доза облучения зон регионального метастазирования
			";
			$join = '
				LEFT JOIN v_EvnUslugaOnkoBeam EUOB with (nolock) ON EUOB.EvnUslugaOnkoBeam_id = EU.EvnUsluga_id
				LEFT JOIN v_OnkoUslugaBeamIrradiationType OUBIT WITH(nolock) ON EUOB.OnkoUslugaBeamIrradiationType_id = OUBIT.OnkoUslugaBeamIrradiationType_id
				LEFT JOIN v_OnkoUslugaBeamFocusType OUBFT WITH(NOLOCK) ON OUBFT.OnkoUslugaBeamFocusType_id = EUOB.OnkoUslugaBeamFocusType_id
				LEFT JOIN v_OnkoUslugaBeamUnitType OUBUT WITH(NOLOCK) ON OUBUT.OnkoUslugaBeamUnitType_id = EUOB.OnkoUslugaBeamUnitType_id
				LEFT JOIN v_OnkoUslugaBeamUnitType OUBUT_ZONE WITH(NOLOCK) ON OUBUT_ZONE.OnkoUslugaBeamUnitType_id = EUOB.OnkoUslugaBeamUnitType_did
			';
			$where = " AND EU.EvnClass_SysNick in ('EvnUslugaOnkoBeam', 'EvnUslugaCommon') ";
			$nick = "'ray', 'LuchLech'";
		}else if($sysnick == 'onkochem'){
			$fields = '
				,OUCKT.OnkoUslugaChemKindType_Name --Вид химиотерапии
				,OUCFT.OnkoUslugaChemFocusType_Name --Преимущественная направленность
			';
			$join = "
				LEFT JOIN v_EvnUslugaOnkoChem OnkoChem with (nolock) on EU.EvnClass_SysNick = 'EvnUslugaOnkoChem' and OnkoChem.EvnUslugaOnkoChem_id = EU.EvnUsluga_id
				LEFT JOIN v_OnkoUslugaChemKindType OUCKT WITH(NOLOCK) ON OUCKT.OnkoUslugaChemKindType_id = OnkoChem.OnkoUslugaChemKindType_id
				LEFT JOIN v_OnkoUslugaChemFocusType OUCFT WITH(NOLOCK) ON OUCFT.OnkoUslugaChemFocusType_id = OnkoChem.OnkoUslugaChemFocusType_id
			";
			$where = " and EU.EvnClass_SysNick in ('EvnUslugaOnkoChem') ";
			$nick = "'XimLech'";
		}else{
			return false;
		}
		$query = "
			SELECT 
				EU.EvnUsluga_id
				,EU.UslugaComplex_id
			   ,RTRIM(EU.EvnClass_SysNick) as EvnClass_SysNick
			   ,convert(varchar(10), EU.EvnUsluga_setDate, 104) as EvnUsluga_setDate
			   ,ISNULL(EU.EvnUsluga_setTime, '') as EvnUsluga_setTime
			   ,ISNULL(Usluga.Usluga_Code, UC.UslugaComplex_Code) as Usluga_Code
			   ,ISNULL(Usluga.Usluga_Name, UC.UslugaComplex_Name) as Usluga_Name
			   ,convert(varchar(10), EU.EvnUsluga_disDate, 104) as EvnUsluga_disDate
			   {$fields}
		   FROM 
			   v_EvnUsluga_all EU with (nolock)
			   left join v_Usluga Usluga with (nolock) on Usluga.Usluga_id = EU.Usluga_id
			   left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EU.UslugaComplex_id
			  {$join}
			WHERE EU.EvnUsluga_pid = :EvnUsluga_pid
			   {$where}
				AND exists (
					SELECT ucat.UslugaComplexAttributeType_Code
					from v_UslugaComplexAttribute uca with (nolock)
						inner join v_UslugaComplexAttributeType ucat with (nolock) on ucat.UslugaComplexAttributeType_id = uca.UslugaComplexAttributeType_id
					where
						uca.UslugaComplex_id = EU.UslugaComplex_id
						--AND ucat.UslugaComplexAttributeType_Code = 11
						AND ucat.UslugaComplexAttributeType_SysNick IN (".$nick.")
				)
		";
		$queryParams = array(
			'EvnUsluga_pid' => $data['EvnDirectionCytologic_pid']
		);

		$resEvnUsluga = $this->db->query($query, $queryParams);
		if ( is_object($resEvnUsluga) ) {
			$res = $resEvnUsluga->result('array');	
		}
		return $res;
	}
}