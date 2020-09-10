<?php

class EvnCytologicProto_model extends swModel {
	private $_ignorePrescrReactionType = false;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Получение серии номера протокола из нумератора
	 */
	function getEvnCytologicProtoNumber($data){
		$this->load->model('Numerator_model');
		$val = array();
		$params = array(
			'NumeratorObject_SysName' => 'EvnCytologicProto',
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$resp = $this->Numerator_model->getNumeratorNum($params, null);

		if (!empty($resp['Numerator_Num'])) {
			$val['EvnCytologicProto_Ser'] = $resp['Numerator_Ser'];
			$val['EvnCytologicProto_Num'] = $resp['Numerator_Num'];
			if(!empty($resp['Numerator_id'])) $val['Numerator_id'] = $resp['Numerator_id'];
			return $val;
		} else {
			if (!empty($resp['Error_Msg'])) return array('Error_Msg' => $resp['Error_Msg']);
			else return array('Error_Msg' => 'Не задан активный нумератор для протокола цитологических диагностических исследований. Обратитесь к администратору системы.');
		}
	}
	
	/**
	 * Сохранение протокола цитологического дигностического исследования
	 */
	function saveEvnCytologicProto($data){
		$response = array(array('Error_Msg' => ''));

		try {
			$this->beginTransaction();
			
			$query = "
				declare
					@Res bigint,
					@ErrCode int,
					@ErrMessage varchar(4000);
				set @Res = :EvnCytologicProto_id;
				exec p_EvnCytologicProto_" . (!empty($data['EvnCytologicProto_id']) ? "upd" : "ins") . "
					@EvnCytologicProto_id = @Res output,
					@Lpu_id = :Lpu_id,
					@Server_id = :Server_id,
					@PersonEvn_id = :PersonEvn_id,
					@EvnCytologicProto_CountUsluga = :EvnCytologicProto_CountUsluga,
					@EvnDirectionCytologic_id = :EvnDirectionCytologic_id,
					@EvnCytologicProto_Ser = :EvnCytologicProto_Ser,
					@EvnCytologicProto_Num = :EvnCytologicProto_Num,
					@OnkoDiag_id = :OnkoDiag_id,
					@EvnCytologicProto_DopData = :EvnCytologicProto_DopData,
					@Mkb10Code_id = :Mkb10Code_id,
					@EvnCytologicProto_MaterialDT = :EvnCytologicProto_MaterialDT,
					@UslugaComplex_id = :UslugaComplex_id,
					@EvnCytologicProto_SurveyDT = :EvnCytologicProto_SurveyDT,
					@EvnCytologicProto_CountGlass = :EvnCytologicProto_CountGlass,
					@EvnCytologicProto_CountFlacon = :EvnCytologicProto_CountFlacon,
					@EvnCytologicProto_IssueDT = :EvnCytologicProto_IssueDT,
					@PayType_id = :PayType_id,
					@EvnCytologicProto_MicroDescr = :EvnCytologicProto_MicroDescr,
					@EvnCytologicProto_Difficulty = :EvnCytologicProto_Difficulty,
					@EvnCytologicProto_Conclusion = :EvnCytologicProto_Conclusion,
					@MedPersonal_id = :MedPersonal_id,
					@Numerator_id = :Numerator_id,
					@DrugQualityCytologic_id = :DrugQualityCytologic_id,
					@ScreeningSmearType_id = :ScreeningSmearType_id,
					@EvnCytologicProto_Cytogram = :EvnCytologicProto_Cytogram,
					@EvnCytologicProto_Description = :EvnCytologicProto_Description,
					@CytologicMaterialPathology_id = :CytologicMaterialPathology_id,
					@EvnCytologicProto_Degree = :EvnCytologicProto_Degree,
					@EvnCytologicProto_Etiologic = :EvnCytologicProto_Etiologic,
					@EvnCytologicProto_OtherConcl = :EvnCytologicProto_OtherConcl,
					@EvnCytologicProto_MoreClar = :EvnCytologicProto_MoreClar,
					@pmUser_id = :pmUser_id,
					
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @Res as EvnCytologicProto_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$queryParams = array(
				'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
				'Lpu_id' => $data['Lpu_id'],
				'Server_id' => $data['Server_id'],
				'PersonEvn_id' => $data['PersonEvn_id'],
				'EvnCytologicProto_CountUsluga' => (!empty($data['EvnCytologicProto_CountUsluga'])) ? $data['EvnCytologicProto_CountUsluga'] : null,
				'EvnStatus_id' => (!empty($data['EvnStatus_id'])) ? $data['EvnStatus_id'] : null,
				'EvnDirectionCytologic_id' => $data['EvnDirectionCytologic_id'],
				'EvnCytologicProto_Ser' => $data['EvnCytologicProto_Ser'],
				'EvnCytologicProto_Num' => $data['EvnCytologicProto_Num'],
				'OnkoDiag_id' => (!empty($data['OnkoDiag_id'])) ? $data['OnkoDiag_id'] : null,
				'EvnCytologicProto_DopData' => (!empty($data['EvnCytologicProto_DopData'])) ? $data['EvnCytologicProto_DopData'] : null,
				'Mkb10Code_id' => (!empty($data['Mkb10Code_id'])) ? $data['Mkb10Code_id'] : null,
				'EvnCytologicProto_MaterialDT' => (!empty($data['EvnCytologicProto_MaterialDT'])) ? $data['EvnCytologicProto_MaterialDT'] : null,
				'UslugaComplex_id' => (!empty($data['UslugaComplex_id'])) ? $data['UslugaComplex_id'] : null,
				'EvnCytologicProto_SurveyDT' => (!empty($data['EvnCytologicProto_SurveyDT'])) ? $data['EvnCytologicProto_SurveyDT'] : null,
				'EvnCytologicProto_CountGlass' => (!empty($data['EvnCytologicProto_CountGlass'])) ? $data['EvnCytologicProto_CountGlass'] : null,
				'EvnCytologicProto_CountFlacon' => (!empty($data['EvnCytologicProto_CountFlacon'])) ? $data['EvnCytologicProto_CountFlacon'] : null,
				'EvnCytologicProto_IssueDT' => (!empty($data['EvnCytologicProto_IssueDT'])) ? $data['EvnCytologicProto_IssueDT'] : null,
				'PayType_id' => (!empty($data['PayType_id'])) ? $data['PayType_id'] : null,
				'EvnCytologicProto_MicroDescr' => (!empty($data['EvnCytologicProto_MicroDescr'])) ? $data['EvnCytologicProto_MicroDescr'] : null,
				'EvnCytologicProto_Difficulty' => (!empty($data['EvnCytologicProto_Difficulty'])) ? $data['EvnCytologicProto_Difficulty'] : null,
				'EvnCytologicProto_Conclusion' => (!empty($data['EvnCytologicProto_Conclusion'])) ? $data['EvnCytologicProto_Conclusion'] : null,
				'MedPersonal_id' => (!empty($data['LabMedPersonal_id'])) ? $data['LabMedPersonal_id'] : null,
				'Numerator_id' => (!empty($data['Numerator_id'])) ? $data['Numerator_id'] : null,
				'isReloadCount' => (!empty($data['isReloadCount'])) ? $data['isReloadCount'] : null,
				'DrugQualityCytologic_id' => (!empty($data['DrugQualityCytologic_id'])) ? $data['DrugQualityCytologic_id'] : null,
				'ScreeningSmearType_id' => (!empty($data['ScreeningSmearType_id'])) ? $data['ScreeningSmearType_id'] : null,
				'EvnCytologicProto_Cytogram' => (!empty($data['EvnCytologicProto_Cytogram'])) ? $data['EvnCytologicProto_Cytogram'] : null,
				'EvnCytologicProto_Description' => (!empty($data['EvnCytologicProto_Description'])) ? $data['EvnCytologicProto_Description'] : null,
				'CytologicMaterialPathology_id' => (!empty($data['CytologicMaterialPathology_id'])) ? $data['CytologicMaterialPathology_id'] : null,
				'EvnCytologicProto_Degree' => (!empty($data['EvnCytologicProto_Degree'])) ? $data['EvnCytologicProto_Degree'] : null,
				'EvnCytologicProto_Etiologic' => (!empty($data['EvnCytologicProto_Etiologic'])) ? $data['EvnCytologicProto_Etiologic'] : null,
				'EvnCytologicProto_OtherConcl' => (!empty($data['EvnCytologicProto_OtherConcl'])) ? $data['EvnCytologicProto_OtherConcl'] : null,
				'EvnCytologicProto_MoreClar' => (!empty($data['EvnCytologicProto_MoreClar'])) ? $data['EvnCytologicProto_MoreClar'] : null,
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
			
			if(!empty($response[0]['EvnCytologicProto_id'])) {
				$data['EvnCytologicProto_id'] = $response[0]['EvnCytologicProto_id'];
			
				$resSaveMedPersonalLinkIds = $this->saveEvnCytologicProtoMedPersonalLinkIds($data);
				$resSavePrescrReactionLinkIds = $this->saveEvnCytologicProtoPrescrReactionLinkIds($data);
				$this->commitTransaction();
			}else{
				throw new Exception('Произошла ошибка при сохранении протокола цитологического дигностического исследования.');
			}
		}
		catch ( Exception $e ) {
			$this->rollbackTransaction();
			$response[0]['Error_Msg'] = $e->getMessage();
		}

		return $response;
	}
	
	/**
	 * Сохранение связей выполнивших исследование и протокола
	 */
	function saveEvnCytologicProtoMedPersonalLinkIds($data){
		if(empty($data['EvnCytologicProto_id']) || empty($data['MedStaffFact_ids']) || !is_array($data['MedStaffFact_ids']) || count($data['MedStaffFact_ids']) == 0) return false;
		$arrMedStaffFactIds =  array_unique($data['MedStaffFact_ids']);
		$addMedStaffFact_array = array();
		$delMedStaffFact_array = array();
		$resp = $this->queryResult("
			select
				EvnCytologicProtoMedPersonalLink_id,
				MedStaffFact_id
			from
				v_EvnCytologicProtoMedPersonalLink (nolock)
			where
				EvnCytologicProto_id = :EvnCytologicProto_id
		", array(
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id']
		));
		
		//удаляем лишнее
		$not_add_arr = array();
		foreach ( $resp as $elem ) {
			$key = array_search($elem['MedStaffFact_id'], $arrMedStaffFactIds);
			if(!$key){
				//если не нашли в переданном массиве, то удаляем запись
				$delMedStaffFact_array[] = $elem['EvnCytologicProtoMedPersonalLink_id'];
				$res_del = $this->deleteEvnCytologicProtoMedPersonalLink(array('EvnCytologicProtoMedPersonalLink_id' => $elem['EvnCytologicProtoMedPersonalLink_id']));
			}else{
				//иначе не трогаем эту запись
				$not_add_arr[] = $elem['MedStaffFact_id'];
			}
		}
		$addMedStaffFact_array = array_diff($arrMedStaffFactIds, $not_add_arr);
		
		if(count($addMedStaffFact_array)>0){
			//добавляем новые записи
			foreach ($addMedStaffFact_array as $value) {
				$res_save = $this->saveEvnCytologicProtoMedPersonalLink(array(
					'MedStaffFact_id' => $value,
					'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
		return true;
	}
	
	/**
	 * Сохранение связи выполнивших исследование и протокола
	 */
	function saveEvnCytologicProtoMedPersonalLink($data){
		if(empty($data['MedStaffFact_id']) || empty($data['EvnCytologicProto_id']) || empty($data['pmUser_id'])) return false;
		$resp_save = $this->queryResult("
			declare
				@ErrCode INT,
				@ErrMessage varchar(4000),
				@MedPersonal INT,
				@Res bigint;
				SET @MedPersonal = (SELECT top 1 MedPersonal_id FROM v_MedStaffFact WHERE MedStaffFact_id = :MedStaffFact_id);

			exec p_EvnCytologicProtoMedPersonalLink_ins
				@EvnCytologicProtoMedPersonalLink_id = @Res output,
				@EvnCytologicProto_id = :EvnCytologicProto_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = @MedPersonal,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnCytologicProtoMedPersonalLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if ( !empty($resp_save[0]['Error_Msg']) ) {
			throw new Exception($resp_save[0]['Error_Msg']);
		}else{
			return $resp_save[0]['EvnCytologicProtoMedPersonalLink_id'];
		}
	}
	
	/**
	 * Удаление связи выполнивших исследование и протокола
	 */
	function deleteEvnCytologicProtoMedPersonalLink($data){
		if(empty($data['EvnCytologicProtoMedPersonalLink_id'])) return false;
		$resp_del = $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnCytologicProtoMedPersonalLink_del
				@EvnCytologicProtoMedPersonalLink_id = :EvnCytologicProtoMedPersonalLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnCytologicProtoMedPersonalLink_id' => $data['EvnCytologicProtoMedPersonalLink_id']
		));

		if ( !empty($resp_del[0]['Error_Msg']) ) {
			throw new Exception($resp_del[0]['Error_Msg']);
		}else{
			return true;
		}
	}
	
	/**
	 * Сохранение  связей вида назначенной окраски и протокола 
	 */
	function saveEvnCytologicProtoPrescrReactionLinkIds($data){
		if(empty($data['EvnCytologicProto_id']) || empty($data['PrescrReactionType_ids']) || !is_array($data['PrescrReactionType_ids']) || count($data['PrescrReactionType_ids']) == 0) return false;
		$arrPrescrReactionTypeIds = array_unique($data['PrescrReactionType_ids']);
		$addPrescrReactionType_array = array();
		$delPrescrReactionType_array = array();
		$resp = $this->queryResult("
			select
				EvnCytologicProtoPrescrReactionLink_id,
				PrescrReactionType_id
			from
				v_EvnCytologicProtoPrescrReactionLink (nolock)
			where
				EvnCytologicProto_id = :EvnCytologicProto_id
		", array(
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id']
		));
		
		//удаляем лишнее
		$not_add_arr = array();
		foreach ( $resp as $elem ) {
			$key = array_search($elem['PrescrReactionType_id'], $arrPrescrReactionTypeIds);
			if(!$key){
				//если не нашли в переданном массиве, то удаляем запись
				$delPrescrReactionType_array[] = $elem['EvnCytologicProtoPrescrReactionLink_id'];
				$res_del = $this->deleteEvnCytologicProtoPrescrReactionLink(array('EvnCytologicProtoPrescrReactionLink_id' => $elem['EvnCytologicProtoPrescrReactionLink_id']));
			}else{
				//иначе не трогаем эту запись
				$not_add_arr[] = $elem['PrescrReactionType_id'];
			}
		}
		$addPrescrReactionType_array = array_diff($arrPrescrReactionTypeIds, $not_add_arr);
		
		if(count($addPrescrReactionType_array)>0){
			//добавляем новые записи
			foreach ($addPrescrReactionType_array as $value) {
				$res_save = $this->saveEvnCytologicProtoPrescrReactionLink(array(
					'PrescrReactionType_id' => $value,
					'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
					'pmUser_id' => $data['pmUser_id']
				));
			}
		}
		return true;
	}
	
	/**
	 * Сохранение  связи вида назначенной окраски и протокола 
	 */
	function saveEvnCytologicProtoPrescrReactionLink($data){
		if(empty($data['PrescrReactionType_id']) || empty($data['EvnCytologicProto_id']) || empty($data['pmUser_id'])) return false;
		$resp_save = $this->queryResult("
			declare
				@ErrCode INT,
				@ErrMessage varchar(4000),
				@Res bigint;

			exec p_EvnCytologicProtoPrescrReactionLink_ins
				@EvnCytologicProtoPrescrReactionLink_id = @Res output,
				@EvnCytologicProto_id = :EvnCytologicProto_id,
				@PrescrReactionType_id = :PrescrReactionType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnCytologicProtoPrescrReactionLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'PrescrReactionType_id' => $data['PrescrReactionType_id'],
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
			'pmUser_id' => $data['pmUser_id']
		));
		
		if ( !empty($resp_save[0]['Error_Msg']) ) {
			throw new Exception($resp_save[0]['Error_Msg']);
		}else{
			return $resp_save[0]['EvnCytologicProtoPrescrReactionLink_id'];
		}
	}
	
	/**
	 * Удаление связи вида назначенной окраски и протокола 
	 */
	function deleteEvnCytologicProtoPrescrReactionLink($data){
		if(empty($data['EvnCytologicProtoPrescrReactionLink_id'])) return false;
		$resp_del = $this->queryResult("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec p_EvnCytologicProtoPrescrReactionLink_del
				@EvnCytologicProtoPrescrReactionLink_id = :EvnCytologicProtoPrescrReactionLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", array(
			'EvnCytologicProtoPrescrReactionLink_id' => $data['EvnCytologicProtoPrescrReactionLink_id']
		));

		if ( !empty($resp_del[0]['Error_Msg']) ) {
			throw new Exception($resp_del[0]['Error_Msg']);
		}else{
			return true;
		}
	}
	
	/**
	 * Получение списка протоколов цитологических дигностических исследований
	 */
	function loadEvnCytologicProtoGrid($data){
		$filter = "(1 = 1)";
		$queryd="";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
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
		
		if ( (strlen($data['PT_Diag_Code_From']) > 0) || (strlen($data['PT_Diag_Code_To']) > 0) ) {
			$queryd .= " inner join Diag PTDiag with (nolock) on PTDiag.Diag_id = ECP.Mkb10Code_id ";

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
		
		if ( !empty($data['setRangeStart']) || !empty($data['setRangeEnd']) ){
			if ( strlen($data['setRangeStart']) > 0 ) {
				$filter .= " and ECP.EvnCytologicProto_SurveyDT >= :setRangeStart
					";
				$queryParams['setRangeStart'] = $data['setRangeStart'];
				}
			if ( strlen($data['setRangeEnd']) > 0 ) {
				$filter .= " and ECP.EvnCytologicProto_SurveyDT <= :setRangeEnd
					";
				$queryParams['setRangeEnd'] = $data['setRangeEnd'];
			}
		}
		
		if ( empty($data['session']['medpersonal_id']) ) {
			$filter .= " and ECP.Lpu_id = :Lpu_id";
		}
		
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();

		$query = "
			select
				-- select				
				'edit' AS accessType,
				ECP.EvnCytologicProto_id,
				ECP.EvnDirectionCytologic_id,
				ECP.Person_id,
				ECP.PersonEvn_id,
				ECP.Server_id,
				ECP.Lpu_id,
				ECP.MedPersonal_id,
				ECP.EvnCytologicProto_Ser,
				ECP.EvnCytologicProto_Num,
				convert(varchar(10), ECP.EvnCytologicProto_SurveyDT, 104) as EvnCytologicProto_SurveyDT, --Дата проведения исследования
				convert(varchar(10), ECP.EvnCytologicProto_setDT, 104) as EvnCytologicProto_setDate,
				convert(varchar(10), ECP.EvnCytologicProto_didDT, 104) as EvnCytologicProto_didDate,
				RTRIM(ISNULL(LpuSID.Lpu_Nick, Lpu.Lpu_Nick)) as Lpu_Name,
				RTRIM(ISNULL(LS.LpuSection_Name, EDC.EvnDirectionCytologic_LpuSectionName)) as LpuSection_Name,
				RTRIM(ISNULL(EDC.EvnDirectionCytologic_NumCard, '')) as EvnDirectionCytologic_NumCard,
				RTRIM(ISNULL(PS.Person_Surname, '')) as Person_Surname,
				RTRIM(ISNULL(PS.Person_Firname, '')) as Person_Firname,
				RTRIM(ISNULL(PS.Person_Secname, '')) as Person_Secname,
				convert(varchar(10), PS.Person_Birthday, 104) as Person_Birthday,
				RTRIM(ISNULL(LAB_MP.Person_Fio, '')) as Lab_MedPersonal_Fio
				-- end select
			from
				-- from
				v_EvnCytologicProto ECP with (nolock)
				inner join v_EvnDirectionCytologic EDC with (nolock) on EDC.EvnDirectionCytologic_id = ECP.EvnDirectionCytologic_id
				inner join v_PersonState PS with (nolock) on PS.Person_id = ECP.Person_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EDC.LpuSection_did
				inner join v_Lpu Lpu with (nolock) on Lpu.Lpu_id = EDC.Lpu_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = ECP.MedPersonal_id AND Lpu_id = ECP.Lpu_id
				) LAB_MP
				outer apply (
					select top 1 Lpu_Nick
					from v_Lpu with (nolock)
					where Lpu_id = EDC.Lpu_sid
				) LpuSID
				".$queryd." 
				-- end from
			WHERE
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
	 * Получение данных для формы редактирования протокола цитологического дигностического исследования
	 */
	function loadEvnCytologicProtoEditForm($data){
		if(empty($data['EvnCytologicProto_id'])) return false;
		$this->load->helper('MedStaffFactLink');
		$med_personal_list = getMedPersonalListWithLinks();
		
		$query = "
			select top 1
				'edit' AS accessType,
				ECP.EvnCytologicProto_id,
				ECP.EvnDirectionCytologic_id,
				ECP.PersonEvn_id,
				ECP.Server_id,
				ECP.Person_id,
				ECP.EvnCytologicProto_setDate,
				ECP.EvnCytologicProto_setTime,
				ECP.EvnCytologicProto_setTime,
				convert(varchar(10), ECP.EvnCytologicProto_setDate, 104) as EvnCytologicProto_setDate,
				ECP.EvnCytologicProto_setTime,
				convert(varchar(10), ECP.EvnCytologicProto_MaterialDT, 104) AS EvnCytologicProto_MaterialDT,
				convert(varchar(10), ECP.EvnCytologicProto_SurveyDT, 104) AS EvnCytologicProto_SurveyDT,
				RTRIM(ISNULL(ECP.EvnCytologicProto_Num,'')) AS EvnCytologicProto_Num,
				RTRIM(ISNULL(ECP.EvnCytologicProto_Ser,'')) AS EvnCytologicProto_Ser,
				LTRIM(RTRIM(ISNULL(EDC.EvnDirectionCytologic_Ser, '') + ' ' + ISNULL(cast(EDC.EvnDirectionCytologic_Num as varchar(10)), '') + ', ' + ISNULL(convert(varchar(10), EDC.EvnDirectionCytologic_setDate, 104), ''))) as EvnDirectionCytologic_SerNum,
				ECP.PayType_id,
				ECP.UslugaComplex_id,
				ECP.EvnCytologicProto_CountUsluga,
				ECP.EvnCytologicProto_CountGlass,
				ECP.EvnCytologicProto_CountFlacon,
				convert(varchar(10), ECP.EvnCytologicProto_IssueDT, 104) AS EvnCytologicProto_IssueDT,
				ECP.OnkoDiag_id,
				ECP.EvnCytologicProto_MicroDescr,
				ECP.Mkb10Code_id,
				ECP.EvnCytologicProto_Difficulty,
				ECP.EvnCytologicProto_Conclusion,		
				ECP.MedPersonal_id AS LabMedPersonal_id,
				ECP.Numerator_id,
				firstMSF.LpuSection_id,
				ECPPRL.PrescrReactionType_ids,
				ECPMPL.MedStaffFact_ids,
				ECP.DrugQualityCytologic_id,
				ECP.ScreeningSmearType_id,
				ECP.EvnCytologicProto_Cytogram,
				ECP.EvnCytologicProto_Description,
				ECP.CytologicMaterialPathology_id,
				ECP.EvnCytologicProto_Degree,
				ECP.EvnCytologicProto_Etiologic,
				ECP.EvnCytologicProto_OtherConcl,
				ECP.EvnCytologicProto_MoreClar
			from
				v_EvnCytologicProto ECP with (nolock)
				inner join v_EvnDirectionCytologic EDC with (nolock) on EDC.EvnDirectionCytologic_id = ECP.EvnDirectionCytologic_id
				outer apply (
					SELECT STUFF(
					(
						select
							',' + cast(PrescrReactionType_id as varchar)
						from
							v_EvnCytologicProtoPrescrReactionLink (nolock)
						where
							EvnCytologicProto_id = ECP.EvnCytologicProto_id
						FOR XML PATH ('')
					), 1, 1, '') as PrescrReactionType_ids
				) ECPPRL
				outer apply (
					SELECT STUFF(
					(
						select
							',' + cast(MedStaffFact_id as varchar)
						from
							v_EvnCytologicProtoMedPersonalLink (nolock)
						where
							EvnCytologicProto_id = ECP.EvnCytologicProto_id
						ORDER BY EvnCytologicProtoMedPersonalLink_insDT
						FOR XML PATH ('')
					), 1, 1, '') as MedStaffFact_ids
				) ECPMPL
				outer apply (
						select top 1
							MSF.MedStaffFact_id,
							MSF.LpuSection_id
						from
							v_EvnCytologicProtoMedPersonalLink ECPMPL (nolock)
							inner join v_MedStaffFact MSF (nolock) on ECPMPL.MedStaffFact_id = MSF.MedStaffFact_id
						where
							EvnCytologicProto_id = ECP.EvnCytologicProto_id
						ORDER BY ECPMPL.EvnCytologicProtoMedPersonalLink_insDT
				) firstMSF

			where (1 = 1)
				and ECP.EvnCytologicProto_id = :EvnCytologicProto_id
				and (ECP.Lpu_id = :Lpu_id or EDC.Lpu_id = :Lpu_id or " . (isset($data['session']['medpersonal_id']) && $data['session']['medpersonal_id'] > 0 ? 1 : 0) . " = 1)
		";
		/*echo getDebugSQL($query, array(
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
			'Lpu_id' => $data['Lpu_id']
		)); die;*/
		$result = $this->db->query($query, array(
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
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
	*  Удаление протокола цитологического дигностического исследования
	*  Входящие данные: $_POST['EvnCytologicProto_id']
	*/
	function deleteEvnCytologicProto($data) {
		if(empty($data['EvnCytologicProto_id'])){ 
			return array(array('Error_Msg' => 'Ошибка при удалении протокола цитологического дигностического исследования. Не передан идентификатор исследования'));
		}
		
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnCytologicProto_del
				@EvnCytologicProto_id = :EvnCytologicProto_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$result = $this->db->query($query, array(
			'EvnCytologicProto_id' => $data['EvnCytologicProto_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление протокола цитологического дигностического исследования)'));
		}
	}
	
	/**
	 * combo Цитограмма соответствует.
	 * поле с выпадающим списком из справочника «Цитологические признаки патологии материала, полученного при профилактическом гинекологическом осмотре, скрининге»
	 */
	function loadCytologicMaterialPathologyCombo($data){
		$queryParams = [];
		$query = "
			SELECT 
				CMP.CytologicMaterialPathology_id,
				CMP.CytologicMaterialPathology_pid,
				CMP.CytologicMaterialPathology_Code,
				CMP.CytologicMaterialPathology_Name
			FROM v_CytologicMaterialPathology CMP
			WHERE NOT EXISTS(SELECT * FROM v_CytologicMaterialPathology C WHERE C.CytologicMaterialPathology_pid = CMP.CytologicMaterialPathology_id)
			ORDER BY CMP.CytologicMaterialPathology_id
		";
		return $this->queryResult($query, $queryParams);
	}
	
	/**
	 * combo Тип мазка.
	 */
	function loadScreeningSmearTypeCombo($data){
		$queryParams = [];
		$query = "
			SELECT 
				SST.ScreeningSmearType_id,
				SST.ScreeningSmearType_pid,
				SST.ScreeningSmearType_Code,
				SST.ScreeningSmearType_Name
			FROM v_ScreeningSmearType SST
			WHERE NOT EXISTS(SELECT * FROM v_ScreeningSmearType S WHERE S.ScreeningSmearType_pid = SST.ScreeningSmearType_id)
			ORDER BY SST.ScreeningSmearType_id
		";
		return $this->queryResult($query, $queryParams);
	}
}

