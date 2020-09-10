<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Api - контроллер API для работы с мед.свидетельствами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			API
 * @access			public
 * @copyright		Copyright (c) 2016 Swan Ltd.
 * @author			Alexander Kurakin
 * @version			11.2016
 */

require(APPPATH.'libraries/SwREST_Controller.php');

class MedSvid extends SwREST_Controller {

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();

		$this->checkAuth();
		$this->load->database();
		$this->load->model('MedSvid_model', 'dbmodel');
		$this->inputRules = $this->dbmodel->inputRules;
	}

	/**
	 * Получение информации по Свидетельству о рождении
	 */
	function BirthSvid_get() {
		$data = $this->ProcessInputData('loadBirthSvid');

		$resp = $this->dbmodel->loadBirthSvid($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание Свидетельства о рождении
	 */
	function BirthSvid_post() {
		$data = $this->ProcessInputData('createBirthSvid');
		$params = array('BirthSvid_IsMnogoplod','BirthSvid_IsFromMother');
		foreach ($data as $key => $value) {
			if(in_array($key, $params)){
				if($value == 1){
					$data[$key] = 2;
				} else {
					$data[$key] = 1;
				}
			}
		}
		
		$fields = [
			'ReceptType_id',
			'BirthEmployment_id',
			'BirthEducation_id',
			'BirthFamilyStatus_id',
			'LpuSection_id',
			'MedStaffFact_id',
			'BirthMedPersonalType_id',
			'OrgHead_id',
			'LpuLicence_id',
			'BirthPlace_id',
			'BirthSpecialist_id',
			'BirthChildResult_id',
			'Sex_id',
			'Address_rid',
			'Person_rid',
			'DeputyKind_id'
		];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		
		if ($this->checkPersonId($data['Person_id']) === false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Пациент не найден в системе'));
		}
		if(getRegionNick() != 'vologda'){
			$fields = ['Person_id', 'ReceptType_id', 'BirthSvid_Ser', 'BirthSvid_Num', 'BirthSvid_GiveDate', 'BirthSvid_RcpDate', 'Sex_id', 'LpuLicence_id'];
			if ($this->commonCheckDoubles('BirthSvid', $fields, $data) !== false) {
				$this->response(array('error_code' => 6, 'error_msg' =>'Данные документа не прошли проверку на дублирование'));
			}
		}
		else{
			$fields = ['BirthSvid_Ser', 'BirthSvid_Num'];
			if ($this->commonCheckDoubles('BirthSvid', $fields, $data) !== false) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Свидетельство с данными номером и серией уже существует'));
			}

			$fields = ['Person_id', 'BirthSvid_BirthDT', 'BirthSvid_Mass', 'BirthSvid_Height'];
			if ($this->commonCheckDoubles('BirthSvid', $fields, $data) !== false) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Данные документа не прошли проверку на дублирование'));
			}
		}
		
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->saveMedSvidAPI($data,'birth');
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['svid_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$response = array('BirthSvid_id'=>$resp['svid_id'], 'Person_id'=>$data['Person_id']);
		$this->response(array(
			'error_code' => 0,
			'data' => $response
		));
	}

	/**
	 * Редактирование Свидетельства о рождении
	 */
	function BirthSvid_put() {
		$data = $this->ProcessInputData('editBirthSvid');
		$params = array('BirthSvid_IsMnogoplod','BirthSvid_IsFromMother');
		foreach ($data as $key => $value) {
			if(in_array($key, $params)){
				if($value == 1){
					$data[$key] = 2;
				} else {
					$data[$key] = 1;
				}
			}
		}
		$resp = $this->dbmodel->loadBirthSvidData($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['BirthSvid_id'])) {
			$this->response(array(
				'error_msg' => 'Свидетельство о рождении не найдено в системе',
				'error_code' => '6'
			));
		}
		
		$fields = [
			'ReceptType_id',
			'BirthEmployment_id',
			'BirthEducation_id',
			'BirthFamilyStatus_id',
			'LpuSection_id',
			'MedStaffFact_id',
			'BirthMedPersonalType_id',
			'OrgHead_id',
			'LpuLicence_id',
			'BirthPlace_id',
			'BirthSpecialist_id',
			'BirthChildResult_id',
			'Sex_id',
			'Address_rid',
			'Person_rid',
			'DeputyKind_id'
		];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}

		// В ТЗ этого нет, пока убрал
		/*$fields = ['Person_id', 'ReceptType_id', 'BirthSvid_Ser', 'BirthSvid_Num', 'BirthSvid_GiveDate', 'BirthSvid_RcpDate', 'Sex_id', 'LpuLicence_id'];
		if ($this->commonCheckDoubles('BirthSvid', $fields, $data) !== false) {
			$this->response(array('error_code' => 6, 'error_msg' =>'Данные документа не прошли проверку на дублирование'));
		}*/
		
		$data['Person_id'] = $resp[0]['Person_id'];
		$data['Lpu_id'] = $resp[0]['Lpu_id'];
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];

		if (getRegionNick() == 'vologda') {
			if ($resp[0]['BirthSvid_IsBad'] == 2) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Редактирование свидетельства с отметкой «Испорченный» невозможно.'));
			}

			$query = "
				select 
					BirthSvid_id
				from
					v_BirthSvid with (nolock)
				where
					ISNULL(BirthSvid_IsBad, 1) = 1
					and BirthSvid_Ser = :BirthSvid_Ser
					and BirthSvid_Num = :BirthSvid_Num
					and BirthSvid_id != :BirthSvid_id
			";
			$result = $this->dbmodel->queryResult($query, array(
				'BirthSvid_Ser' => !empty($data['BirthSvid_Ser']) ? $data['BirthSvid_Ser'] : $resp[0]['BirthSvid_Ser'],
				'BirthSvid_Num' => !empty($data['BirthSvid_Num']) ? $data['BirthSvid_Num'] : $resp[0]['BirthSvid_Num'],
				'BirthSvid_id' =>  $data['BirthSvid_id']
			));
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на существование свидетельств с теми же номером и серией'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Свидетельство с данными номером и серией уже существует'));
			}

			$query = "
				select
					BirthSvid_id
				from
					v_BirthSvid with (nolock)
				where
					ISNULL(BirthSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and BirthSvid_Mass = :BirthSvid_Mass
					and BirthSvid_Height = :BirthSvid_Height
					and BirthSvid_BirthDT = :BirthSvid_BirthDT
					and BirthSvid_id != :BirthSvid_id
			";
			$result = $this->dbmodel->queryResult($query, array(
				'Person_id' => $data['Person_id'],
				'BirthSvid_BirthDT' => !empty($data['BirthSvid_BirthDT']) ? $data['BirthSvid_BirthDT'] : ($resp[0]['BirthSvid_BirthDT']->format('Y-m-d H:i:s')),
				'BirthSvid_Mass' => !empty($data['BirthSvid_Mass']) ? $data['BirthSvid_Mass'] : $resp[0]['BirthSvid_Mass'],
				'BirthSvid_Height' => !empty($data['BirthSvid_Height']) ? $data['BirthSvid_Height'] : $resp[0]['BirthSvid_Height'],
				'BirthSvid_id' =>  $data['BirthSvid_id']
			));
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на дублирование'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Не удалось изменить данные о свидетельстве, т.к. данные документа не прошли проверку на дублирование'));
			}

		}

		$resp = $this->dbmodel->saveMedSvidAPI($data,'birth');
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['svid_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else {
			$this->response(array(
				'error_code' => 0
			));
		}
	}

	/**
	 * Получение списка свидетельств о рождении по человеку
	 */
	function BirthSvidList_get() {
		$data = $this->ProcessInputData('loadBirthSvidListByPerson');

		$resp = $this->dbmodel->loadBirthSvidListByPerson($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение информации по Свидетельству о смерти
	 */
	function DeathSvid_get() {
		$data = $this->ProcessInputData('loadDeathSvid');

		$resp = $this->dbmodel->loadDeathSvid($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание Свидетельства о смерти
	 */
	function DeathSvid_post() {
		$data = $this->ProcessInputData('createDeathSvid');
		if (!empty($data['MedStaffFact_id'])) {
			$this->load->model('MedPersonal_model');			
			$MedPersonal_id = $this->MedPersonal_model->getMedPersonalIdByMedStaffFactId($data);
			if (!empty($MedPersonal_id)) {
				$data['MedPersonal_id'] = $MedPersonal_id;
			}
		}

		$this->load->model('Person_model');			
		$pers_data = $this->Person_model->getPersonForMedSvid($data);	
		if (!$pers_data) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Пациент не найден в системе'
			));
		}
		
		$fieldsDiag = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid'];
		if(getRegionNick() == 'msk') {
			$fieldsIsPrimDiag = ['DeathSvid_IsPrimDiagEID', 'DeathSvid_IsPrimDiagIID', 'DeathSvid_IsPrimDiagMID', 'DeathSvid_IsPrimDiagTID'];
			$countIsPrimDiag = 0; $labelIsPrimDiag = [];
			foreach($fieldsIsPrimDiag as $field) {
				$key = array_search($field, array_column($this->inputRules['createDeathSvid'], 'field'));
				if($key) $labelIsPrimDiag[] = $this->inputRules['createDeathSvid'][$key]['label'];
				if(!empty($data[$field]) && $data[$field] == 2) $countIsPrimDiag++;
			}
			
			if($countIsPrimDiag > 1){
				$this->response(array('error_code' => 6, 'error_msg' => 'Нельзя указать несколько причин смерти в качестве основной причины. Только один из параметров может принимать значение «Да»: '.implode(', ', $labelIsPrimDiag)));
			}
			$countFieldsDiag = 0; $labelFieldsDiag = [];
			foreach($fieldsDiag as $field) {
				$key = array_search($field, array_column($this->inputRules['createDeathSvid'], 'field'));
				if($key) $labelFieldsDiag[] = $this->inputRules['createDeathSvid'][$key]['label'];
				if(!empty($data[$field])) $countFieldsDiag++;
			}
			if($countFieldsDiag == 0){
				$this->response(array('error_code' => 6, 'error_msg' => 'Должен быть обязательно заполнен как минимум один параметр из списка: '.implode(', ', $labelFieldsDiag)));
			}
		}else{
			$labelFieldsDiag = [];
			foreach($fieldsDiag as $field) {
				if(empty($data[$field])) $labelFieldsDiag[] = $field;
			}
			if(count($labelFieldsDiag) > 0){
				$this->response(array('error_code' => 6, 'error_msg' => 'Отсутствуют обязательные параметры: '.implode(', ', $labelFieldsDiag)));
			}
		}
		
		$fields = [
			'ReceptType_id',
			'DeathSvidType_id',
			'LpuSection_id',
			'MedStaffFact_id',
			'OrgHead_id',
			'Person_mid',
			'Address_bid',
			'ChildTermType_id',
			'DeathEmployment_id',
			'DeathEducation_id',
			'DeathPlace_id',
			'Address_did',
			'DeathFamilyStatus_id',
			'DeathCause_id',
			'DeathTrauma_id',
			'DtpDeathTime_id',
			'DeathSetType_id',
			'DeathSetCause_id',
			'Diag_iid',
			'Diag_tid',
			'Diag_mid',
			'Diag_eid',
			'Diag_oid',
			'Person_rid'
		];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}
		if(getRegionNick() != 'vologda') {
			$fields = ['Person_id', 'ReceptType_id', 'DeathSvid_Ser', 'DeathSvid_Num', 'DeathSvid_GiveDate', 'DeathSvid_RcpDate', 'LpuSection_id', 'MedStaffFact_id'];
			if ($this->commonCheckDoubles('DeathSvid', $fields, $data) !== false) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Данные документа не прошли проверку на дублирование'));
			}
		}
		else{
			$fields = ['DeathSvid_Ser', 'DeathSvid_Num'];
			if ($this->commonCheckDoubles('DeathSvid', $fields, $data) !== false) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Свидетельство с данными номером и серией уже существует'));
			}

			$filter =!empty($data['DeathSvid_BirthDateStr']) ? 'and DeathSvid_BirthDateStr = :DeathSvid_BirthDateStr ':
				'and DeathSvid_BirthDateStr is null ';
			$filter.=!empty($data['DeathSvid_DeathDateStr']) ? 'and DeathSvid_DeathDateStr = :DeathSvid_DeathDateStr ':
				'and DeathSvid_DeathDateStr is null ';
			$filter.= !empty($data['DeathSvid_DeathDate_Date']) ? 'and cast(DeathSvid_DeathDate as date) = :DeathSvid_DeathDate ':
				'and DeathSvid_DeathDate is null ';

			$query = "
				select
					DeathSvid_id
				from
					v_DeathSvid with (nolock)
				where
					ISNULL(DeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					{$filter}
				";
			$result = $this->dbmodel->queryResult($query, array(
				'Person_id' => $data['Person_id'],
				'DeathSvidType_id' => $data['DeathSvidType_id'],
				'DeathSvid_BirthDateStr' => $data['DeathSvid_BirthDateStr'],
				'DeathSvid_DeathDateStr' => $data['DeathSvid_DeathDateStr'],
				'DeathSvid_DeathDate' => $data['DeathSvid_DeathDate_Date']
			));
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на дублирование'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Данные документа не прошли проверку на дублирование'));
			}
		}

		if (
			!empty($pers_data['Person_BirthDay']) && 
			!empty($data['DeathSvid_DeathDate_Date']) && 
			$pers_data['Person_BirthDay'] > $data['DeathSvid_DeathDate_Date']
		) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата смерти должна быть больше даты рождения'
			));
		}

		if (
			!empty($data['DeathSvid_RcpDate']) && 
			!empty($data['DeathSvid_GiveDate']) && 
			$data['DeathSvid_RcpDate'] < $data['DeathSvid_GiveDate']
		) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата получения не может быть меньше даты выдачи'
			));
		}
	
		if (
			!empty($pers_data['Person_BirthDay']) && 
			!empty($data['DeathSvid_TraumaDate_Date']) && 
			$pers_data['Person_BirthDay'] > $data['DeathSvid_TraumaDate_Date']
		) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата/год травмы не может быть меньше даты/года рождения'
			));
		}
			
		if (getRegionNick() == 'ekb') {
			if (
				!empty($data['DeathSvid_DeathDate_Date']) && 
				!empty($data['DeathSvid_GiveDate']) && 
				(strtotime($data['DeathSvid_GiveDate']) - strtotime($data['DeathSvid_DeathDate_Date'])) / (3600 * 24) > 4
			) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Период между датой смерти и датой выдачи свидетельства должен быть меньше 5 дней' 
				));
			}
		}
		
		$diag_fields = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid'];
		
		foreach($diag_fields as $df) {
			
			$diag_code = empty($data[$df]) ? null : $this->dbmodel->getFirstResultFromQuery("select Diag_Code from v_Diag (nolock) where Diag_id = ?", [$data[$df]]);
			
			if (!empty($pers_data['Person_BirthDay']) && !empty($data['DeathSvid_DeathDate_Date'])) {
				$age_days = (strtotime($data['DeathSvid_DeathDate_Date']) - strtotime($pers_data['Person_BirthDay'])) / (3600 * 24);
				if (in_array($diag_code, ['R95.0', 'R95.9']) && $age_days > 364) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Для установки диагнозов «R95.0 Синдром внезапной смерти младенца с упоминанием о вскрытии» и «R95.9 Синдром внезапной смерти младенца без упоминания о вскрытии» возраст пациента должен быть не больше 11 месяцев 30 дней на дату смерти' 
					));
				}
			}
			
			if (!empty($pers_data['Person_AgeEndYear'])) {
				if (substr($diag_code, 0, 3) == 'R54' && $pers_data['Person_AgeEndYear'] < 81) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Выбор диагноза «R54. Старость» возможен, только если в год смерти пациента ему(ей) исполнилось или должно было исполнится минимум 81 лет' 
					));
				}
			}
			
			if (!empty($data[$df])) {
				switch ($df) {
					case 'Diag_iid': $filter_name = 'DeathDiag_IsDiagIID'; break;
					case 'Diag_tid': $filter_name = 'DeathDiag_IsDiagTID'; break;
					case 'Diag_mid': $filter_name = 'DeathDiag_IsDiagMID'; break;
					case 'Diag_eid': $filter_name = 'DeathDiag_IsDiagEID'; break;
				}
				$chk = $this->dbmodel->getFirstResultFromQuery("
					select 
						DeathDiag_id
					from v_DeathDiag (nolock)
					where 
						Diag_id = :Diag_id
						and DeathDiag_IsNotUsed = 2
						and {$filter_name} = 2
						and (Sex_id is null or Sex_id = :Sex_id)
						and isnull(DeathDiag_YearFrom, 0) <= :Person_Age
						and isnull(DeathDiag_YearTo, 200) >= :Person_Age
						and isnull(DeathDiag_MonthFrom, 0) <= :Person_AgeMonths
						and isnull(DeathDiag_MonthTo, 12) >= :Person_AgeMonths
						and isnull(DeathDiag_DayFrom, 0) <= :Person_AgeDays
						and isnull(DeathDiag_DayTo, 31) >= :Person_AgeDays
				", [
					'Diag_id' => $data[$df],
					'Sex_id' => $pers_data['Sex_id'],
					'Person_Age' => $pers_data['Person_Age'],
					'Person_AgeMonths' => $pers_data['Person_AgeMonths'],
					'Person_AgeDays' => $pers_data['Person_AgeDays']
				]);
				if ($chk !== false) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Параметр '.$df.' не прошел проверку на разрешенный диагноз'
					));
				}
			}
		}
		
		if ($data['DeathSvid_IsUnknownDeathDate'] != 2 && empty($data['DeathSvid_DeathDate_Date']) && empty($data['DeathSvid_DeathDateStr'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Должна быть указана дата смерти либо неуточненная дата смерти, либо указано, что дата смерти неизвестна' 
			));
		}
		
		if ($data['DeathSvid_IsUnknownDeathTime'] != 2 && empty($data['DeathSvid_DeathDate_Time'])) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Не указано время смерти. Необходимо указать точное время смерти, либо указать, что время смерти неизвестно' 
			));
		}

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];

		$resp = $this->dbmodel->saveMedSvidAPI($data,'death');
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['svid_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$response = array('DeathSvid_id'=>$resp['svid_id'], 'Person_id'=>$data['Person_id']);
		$this->response(array(
			'error_code' => 0,
			'data' => $response
		));
	}

	/**
	 * Редактирование Свидетельства о смерти
	 */
	function DeathSvid_put() {
		$data = $this->ProcessInputData('editDeathSvid');
		
		$resp = $this->dbmodel->loadDeathSvidData($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['DeathSvid_id'])) {
			$this->response(array(
				'error_msg' => 'Свидетельство о смерти не найдено в системе',
				'error_code' => '6'
			));
		}
		
		$data['Person_id'] = $resp[0]['Person_id'];
		$data['Lpu_id'] = $resp[0]['Lpu_id'];
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];

		$pers_data = $this->dbmodel->getFirstRowFromQuery("
			declare
				@Person_id bigint = :Person_id,
				@date datetime, @tmpdate datetime, @years int, @months int, @days int,
				@Year bigint = YEAR(dbo.tzGetDate()),
				@curDate datetime = dbo.tzGetDate(),
				@YearLastDay datetime = cast(YEAR(dbo.tzGetDate()) as varchar) + '-12-31';
			select @date = Person_BirthDay from v_PersonState (nolock) where Person_id = @Person_id;
			select @tmpdate = @date;
			select @years = DATEDIFF(yy, @tmpdate, @curDate) - CASE WHEN (MONTH(@date) > MONTH(@curDate)) OR (MONTH(@date) = MONTH(@curDate) AND DAY(@date) > DAY(@curDate)) THEN 1 ELSE 0 END;
			select @tmpdate = DATEADD(yy, @years, @tmpdate);
			select @months = DATEDIFF(m, @tmpdate, @curDate) - CASE WHEN DAY(@date) > DAY(@curDate) THEN 1 ELSE 0 END;
			select @tmpdate = DATEADD(m, @months, @tmpdate);
			select @days = DATEDIFF(d, @tmpdate, @curDate);
			select top 1 
				PersonEvn_id,  
				convert(varchar(10), Person_BirthDay, 120) as Person_BirthDay,
				dbo.Age2(ps.Person_BirthDay, @curDate) as Person_Age,
				dbo.Age2(ps.Person_BirthDay, @YearLastDay) as Person_AgeEndYear,
				@months as Person_AgeMonths, 
				@days as Person_AgeDays,
				Sex_id
			from v_PersonState PS (nolock) 
			where PS.Person_id = @Person_id;
		", $data);
		
		if(getRegionNick() == 'msk') {
			$fieldsIsPrimDiag = ['DeathSvid_IsPrimDiagEID', 'DeathSvid_IsPrimDiagIID', 'DeathSvid_IsPrimDiagMID', 'DeathSvid_IsPrimDiagTID'];
			$countIsPrimDiag = 0; $labelIsPrimDiag = [];
			foreach($fieldsIsPrimDiag as $field) {
				$key = array_search($field, array_column($this->inputRules['createDeathSvid'], 'field'));
				if($key) $labelIsPrimDiag[] = $this->inputRules['createDeathSvid'][$key]['label'];
				if(!empty($data[$field])) $countIsPrimDiag++;
			}
			
			if($countIsPrimDiag > 1){
				$this->response(array('error_code' => 6, 'error_msg' => 'Нельзя указать несколько причин смерти в качестве основной причины. Только один из параметров может принимать значение «Да»: '.implode(', ', $labelIsPrimDiag)));
			}
		}
		
		$fields = [
			'ReceptType_id',
			'DeathSvidType_id',
			'LpuSection_id',
			'MedStaffFact_id',
			'OrgHead_id',
			'Person_mid',
			'Address_bid',
			'ChildTermType_id',
			'DeathEmployment_id',
			'DeathEducation_id',
			'DeathPlace_id',
			'Address_did',
			'DeathFamilyStatus_id',
			'DeathCause_id',
			'DeathTrauma_id',
			'DtpDeathTime_id',
			'DeathSetType_id',
			'DeathSetCause_id',
			'Diag_iid',
			'Diag_tid',
			'Diag_mid',
			'Diag_eid',
			'Diag_oid',
			'Person_rid'
		];
		$checkFieldsData = $this->checkFieldsData($fields, $data);
		if ($checkFieldsData !== false) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => $checkFieldsData
			));
		}

		if(getRegionNick() =='vologda'){

			$params['Person_id'] = $data['Person_id'];
			$params['DeathSvidType_id'] = !empty($data['DeathSvidType_id']) ? $data['DeathSvidType_id'] : $resp[0]['DeathSvidType_id'];
			$params['DeathSvid_BirthDateStr'] = !empty($data['DeathSvid_BirthDateStr']) ? $data['DeathSvid_BirthDateStr'] : $resp[0]['DeathSvid_BirthDateStr'];
			$params['DeathSvid_DeathDateStr'] = !empty($data['DeathSvid_DeathDateStr']) ? $data['DeathSvid_DeathDateStr'] : $resp[0]['DeathSvid_DeathDateStr'];
			$params['DeathSvid_DeathDate'] = !empty($data['DeathSvid_DeathDate_Date']) ? $data['DeathSvid_DeathDate_Date'] :
				(!empty($resp[0]['DeathSvid_DeathDate']) ? ($resp[0]['DeathSvid_DeathDate']->format('Y-m-d H:i:s')) : null);
			$params['DeathSvid_id'] =  $data['DeathSvid_id'];
			$params['DeathSvid_Ser'] = !empty($data['DeathSvid_Ser']) ? $data['DeathSvid_Ser'] : $resp[0]['DeathSvid_Ser'];
			$params['DeathSvid_Num'] = !empty($data['DeathSvid_Num']) ? $data['DeathSvid_Num'] : $resp[0]['DeathSvid_Num'];

			if ($resp[0]['DeathSvid_IsActual'] != 2) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Редактирование свидетельства, которое не является актуальным, невозможно.'));
			}

			$query = "
				select 
					DeathSvid_id
				from
					v_DeathSvid with (nolock)
				where
					ISNULL(DeathSvid_IsBad, 1) = 1
					and DeathSvid_Ser = :DeathSvid_Ser
					and DeathSvid_Num = :DeathSvid_Num
					and DeathSvid_id != :DeathSvid_id
			";
			$result = $this->dbmodel->queryResult($query, $params);
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на существование свидетельств с теми же номером и серией'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Свидетельство с данными номером и серией уже существует'));
			}

			$filter =!empty($params['DeathSvid_BirthDateStr']) ? 'and DeathSvid_BirthDateStr = :DeathSvid_BirthDateStr ':
				'and DeathSvid_BirthDateStr is null ';
			$filter.=!empty($params['DeathSvid_DeathDateStr']) ? 'and DeathSvid_DeathDateStr = :DeathSvid_DeathDateStr ':
				'and DeathSvid_BirthDateStr is null ';
			$filter.= !empty($params['DeathSvid_DeathDate']) ? 'and cast(DeathSvid_DeathDate as date) = :DeathSvid_DeathDate ':
				'and DeathSvid_DeathDate is null ';

			$query = "
				select
						DeathSvid_id
				from
					v_DeathSvid with (nolock)
				where
					ISNULL(DeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					and DeathSvid_id != :DeathSvid_id
					{$filter}
			";

			$result = $this->dbmodel->queryResult($query, $params);
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на дублирование'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Не удалось изменить данные о свидетельстве, т.к. данные документа не прошли проверку на дублирование'));
			}
		}
		
		if (
			!empty($pers_data['Person_BirthDay']) && 
			!empty($data['DeathSvid_DeathDate_Date']) && 
			$pers_data['Person_BirthDay'] > $data['DeathSvid_DeathDate_Date']
		) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата смерти должна быть больше даты рождения'
			));
		}

		if (
			!empty($data['DeathSvid_RcpDate']) && 
			!empty($data['DeathSvid_GiveDate']) && 
			$data['DeathSvid_RcpDate'] < $data['DeathSvid_GiveDate']
		) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата получения не может быть меньше даты выдачи'
			));
		}
	
		if (
			!empty($pers_data['Person_BirthDay']) && 
			!empty($data['DeathSvid_TraumaDate_Date']) && 
			$pers_data['Person_BirthDay'] > $data['DeathSvid_TraumaDate_Date']
		) {
			$this->response(array(
				'error_code' => 6,
				'error_msg' => 'Дата/год травмы не может быть меньше даты/года рождения'
			));
		}
			
		if (getRegionNick() == 'ekb') {
			if (
				!empty($data['DeathSvid_DeathDate_Date']) && 
				!empty($data['DeathSvid_GiveDate']) && 
				(strtotime($data['DeathSvid_GiveDate']) - strtotime($data['DeathSvid_DeathDate_Date'])) / (3600 * 24) > 4
			) {
				$this->response(array(
					'error_code' => 6,
					'error_msg' => 'Период между датой смерти и датой выдачи свидетельства должен быть меньше 5 дней' 
				));
			}
		}
		
		$diag_fields = ['Diag_iid', 'Diag_tid', 'Diag_mid', 'Diag_eid'];
		
		foreach($diag_fields as $df) {
			
			$diag_code = empty($data[$df]) ? null : $this->dbmodel->getFirstResultFromQuery("select Diag_Code from v_Diag (nolock) where Diag_id = ?", [$data[$df]]);
			
			if (!empty($pers_data['Person_BirthDay']) && !empty($data['DeathSvid_DeathDate_Date'])) {
				$age_days = (strtotime($data['DeathSvid_DeathDate_Date']) - strtotime($pers_data['Person_BirthDay'])) / (3600 * 24);
				if (in_array($diag_code, ['R95.0', 'R95.9']) && $age_days > 364) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Для установки диагнозов «R95.0 Синдром внезапной смерти младенца с упоминанием о вскрытии» и «R95.9 Синдром внезапной смерти младенца без упоминания о вскрытии» возраст пациента должен быть не больше 11 месяцев 30 дней на дату смерти' 
					));
				}
			}
			
			if (!empty($pers_data['Person_AgeEndYear'])) {
				if (substr($diag_code, 0, 3) == 'R54' && $pers_data['Person_AgeEndYear'] < 81) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Выбор диагноза «R54. Старость» возможен, только если в год смерти пациента ему(ей) исполнилось или должно было исполнится минимум 81 лет' 
					));
				}
			}
			
			if (!empty($data[$df])) {
				switch ($df) {
					case 'Diag_iid': $filter_name = 'DeathDiag_IsDiagIID'; break;
					case 'Diag_tid': $filter_name = 'DeathDiag_IsDiagTID'; break;
					case 'Diag_mid': $filter_name = 'DeathDiag_IsDiagMID'; break;
					case 'Diag_eid': $filter_name = 'DeathDiag_IsDiagEID'; break;
				}
				$chk = $this->dbmodel->getFirstResultFromQuery("
					select 
						DeathDiag_id
					from v_DeathDiag (nolock)
					where 
						Diag_id = :Diag_id
						and DeathDiag_IsNotUsed = 2
						and {$filter_name} = 2
						and (Sex_id is null or Sex_id = :Sex_id)
						and isnull(DeathDiag_YearFrom, 0) <= :Person_Age
						and isnull(DeathDiag_YearTo, 200) >= :Person_Age
						and isnull(DeathDiag_MonthFrom, 0) <= :Person_AgeMonths
						and isnull(DeathDiag_MonthTo, 12) >= :Person_AgeMonths
						and isnull(DeathDiag_DayFrom, 0) <= :Person_AgeDays
						and isnull(DeathDiag_DayTo, 31) >= :Person_AgeDays
				", [
					'Diag_id' => $data[$df],
					'Sex_id' => $pers_data['Sex_id'],
					'Person_Age' => $pers_data['Person_Age'],
					'Person_AgeMonths' => $pers_data['Person_AgeMonths'],
					'Person_AgeDays' => $pers_data['Person_AgeDays']
				]);
				if ($chk !== false) {
					$this->response(array(
						'error_code' => 6,
						'error_msg' => 'Параметр '.$df.' не прошел проверку на разрешенный диагноз'
					));
				}
			}
		}

		$resp = $this->dbmodel->saveMedSvidAPI($data,'death');
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['svid_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else {
			$this->response(array(
				'error_code' => 0
			));
		}
	}

	/**
	 * Получение списка свидетельств о смерти по человеку
	 */
	function DeathSvidList_get() {
		$data = $this->ProcessInputData('loadDeathSvidListByPerson');

		$resp = $this->dbmodel->loadDeathSvidListByPerson($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Получение информации по Свидетельству о перинатальной смерти
	 */
	function PntDeathSvid_get() {
		$data = $this->ProcessInputData('loadPntDeathSvid');

		$resp = $this->dbmodel->loadPntDeathSvid($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}

	/**
	 * Создание Свидетельства о перинатальной смерти
	 */
	function PntDeathSvid_post() {
		$data = $this->ProcessInputData('createPntDeathSvid');

		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];
		$data['Lpu_id'] = $sp['Lpu_id'];

		if(getRegionNick() == 'vologda'){
			$fields = ['PntDeathSvid_Ser', 'PntDeathSvid_Num'];
			if ($this->commonCheckDoubles('PntDeathSvid', $fields, $data) !== false) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Свидетельство с данными номером и серией уже существует'));
			}

			$filter = !empty($data['PntDeathSvid_DeathDate']) ? 'and cast(PntDeathSvid_DeathDate as date) = :PntDeathSvid_DeathDate ':
				'and PntDeathSvid_DeathDate is null ';
			$filter.=!empty($data['PntDeathSvid_DeathDateStr']) ? 'and PntDeathSvid_DeathDateStr = :PntDeathSvid_DeathDateStr ':
				'and PntDeathSvid_DeathDateStr is null ';
			$filter.= !empty($data['PntDeathSvid_ChildBirthDT']) ? 'and cast(PntDeathSvid_ChildBirthDT as date) = :PntDeathSvid_ChildBirthDT ':
				'and PntDeathSvid_ChildBirthDT is null ';
			$filter.=!empty($data['PntDeathSvid_ChildBirthDateStr']) ? 'and PntDeathSvid_BirthDateStr = :PntDeathSvid_BirthDateStr ':
				'and PntDeathSvid_BirthDateStr is null ';
			$filter.=!empty($data['PntDeathSvid_Mass']) ? 'and PntDeathSvid_Mass = :PntDeathSvid_Mass ':
				'and PntDeathSvid_Mass is null ';
			$filter.=!empty($data['PntDeathSvid_Height']) ? 'and PntDeathSvid_Height = :PntDeathSvid_Height ':
				'and PntDeathSvid_Height is null ';

			$query = "
				select
					PntDeathSvid_id
				from
					v_PntDeathSvid with (nolock)
				where
					ISNULL(PntDeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					{$filter}
			";
			$result = $this->dbmodel->queryResult($query, array(
				'Person_id' => $data['Person_id'],
				'DeathSvidType_id' => $data['DeathSvidType_id'],
				'PntDeathSvid_DeathDate' => $data['PntDeathSvid_DeathDate'],
				'PntDeathSvid_DeathDateStr' => $data['PntDeathSvid_DeathDateStr'],
				'PntDeathSvid_ChildBirthDT' => $data['PntDeathSvid_ChildBirthDT'],
				'PntDeathSvid_BirthDateStr' => $data['PntDeathSvid_ChildBirthDateStr'],
				'PntDeathSvid_Mass' => $data['PntDeathSvid_Mass'],
				'PntDeathSvid_Height' => $data['PntDeathSvid_Height'],
			));
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на дублирование'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Данные документа не прошли проверку на дублирование'));
			}
		}
		$resp = $this->dbmodel->saveMedSvidAPI($data,'pntdeath');
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['svid_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$response = array('PntDeathSvid_id'=>$resp['svid_id'], 'Person_id'=>$data['Person_id']);
		$this->response(array(
			'error_code' => 0,
			'data' => $response
		));
	}

	/**
	 * Редактирование Свидетельства о перинатальной смерти
	 */
	function PntDeathSvid_put() {
		$data = $this->ProcessInputData('editPntDeathSvid');
		
		$resp = $this->dbmodel->loadPntDeathSvidData($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}
		if (empty($resp[0]['PntDeathSvid_id'])) {
			$this->response(array(
				'error_msg' => 'Не существует строки для переданного идентификатора',
				'error_code' => '6'
			));
		}
		$data['Person_id'] = $resp[0]['Person_id'];
		$data['Lpu_id'] = $resp[0]['Lpu_id'];
		$sp = getSessionParams();
		$data['pmUser_id'] = $sp['pmUser_id'];
		$data['Server_id'] = $sp['Server_id'];

		if(getRegionNick() == 'vologda'){

			$params['Person_id'] = $data['Person_id'];
			$params['DeathSvidType_id'] = !empty($data['DeathSvidType_id']) ? $data['DeathSvidType_id'] : $resp[0]['DeathSvidType_id'];
			$params['PntDeathSvid_DeathDate'] = !empty($data['PntDeathSvid_DeathDate']) ? $data['PntDeathSvid_DeathDate'] :
				(!empty($resp[0]['PntDeathSvid_DeathDate']) ? ($resp[0]['PntDeathSvid_DeathDate']->format('Y-m-d H:i:s')) : null);
			$params['PntDeathSvid_DeathDateStr'] = !empty($data['PntDeathSvid_DeathDateStr']) ? $data['PntDeathSvid_DeathDateStr'] : $resp[0]['PntDeathSvid_DeathDateStr'];
			$params['PntDeathSvid_ChildBirthDT'] = !empty($data['PntDeathSvid_ChildBirthDT']) ? $data['PntDeathSvid_ChildBirthDT'] :
				(!empty($resp[0]['PntDeathSvid_ChildBirthDT']) ? ($resp[0]['PntDeathSvid_ChildBirthDT']->format('Y-m-d H:i:s')) : null);
			$params['PntDeathSvid_BirthDateStr'] = !empty($data['PntDeathSvid_ChildBirthDateStr']) ? $data['PntDeathSvid_ChildBirthDateStr'] : $resp[0]['PntDeathSvid_BirthDateStr'];
			$params['PntDeathSvid_Mass'] = !empty($data['PntDeathSvid_Mass']) ? $data['PntDeathSvid_Mass'] : $resp[0]['PntDeathSvid_Mass'];
			$params['PntDeathSvid_Height'] = !empty($data['PntDeathSvid_Height']) ? $data['PntDeathSvid_Height'] : $resp[0]['PntDeathSvid_Height'];
			$params['PntDeathSvid_Ser'] = !empty($data['PntDeathSvid_Ser']) ? $data['PntDeathSvid_Ser'] : $resp[0]['PntDeathSvid_Ser'];
			$params['PntDeathSvid_Num'] = !empty($data['PntDeathSvid_Num']) ? $data['PntDeathSvid_Num'] : $resp[0]['PntDeathSvid_Num'];
			$params['PntDeathSvid_id'] =  $data['PntDeathSvid_id'];


			if ($resp[0]['PntDeathSvid_IsActual'] != 2) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Редактирование свидетельства, которое не является актуальным, невозможно.'));
			}

			$query = "
				select 
					PntDeathSvid_id
				from
					v_PntDeathSvid with (nolock)
				where
					ISNULL(PntDeathSvid_IsBad, 1) = 1
					and PntDeathSvid_Ser = :PntDeathSvid_Ser
					and PntDeathSvid_Num = :PntDeathSvid_Num
					and PntDeathSvid_id != :PntDeathSvid_id
			";
			$result = $this->dbmodel->queryResult($query, $params);
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на существование свидетельств с теми же номером и серией'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Свидетельство с данными номером и серией уже существует'));
			}

			$filter = !empty($params['PntDeathSvid_DeathDate']) ? 'and cast(PntDeathSvid_DeathDate as date) = :PntDeathSvid_DeathDate ':
				'and PntDeathSvid_DeathDate is null ';
			$filter.=!empty($params['PntDeathSvid_DeathDateStr']) ? 'and PntDeathSvid_DeathDateStr = :PntDeathSvid_DeathDateStr ':
				'and PntDeathSvid_DeathDateStr is null ';
			$filter.= !empty($params['PntDeathSvid_ChildBirthDT']) ? 'and cast(PntDeathSvid_ChildBirthDT as date) = :PntDeathSvid_ChildBirthDT ':
				'and PntDeathSvid_ChildBirthDT is null ';
			$filter.=!empty($params['PntDeathSvid_BirthDateStr']) ? 'and PntDeathSvid_BirthDateStr = :PntDeathSvid_BirthDateStr ':
				'and PntDeathSvid_BirthDateStr is null ';
			$filter.=!empty($params['PntDeathSvid_Mass']) ? 'and PntDeathSvid_Mass = :PntDeathSvid_Mass ':
				'and PntDeathSvid_Mass is null ';
			$filter.=!empty($params['PntDeathSvid_Height']) ? 'and PntDeathSvid_Height = :PntDeathSvid_Height ':
				'and PntDeathSvid_Height is null ';

			$query = "
				select top 1
					PntDeathSvid_id
				from
					v_PntDeathSvid with (nolock)
				where
					ISNULL(PntDeathSvid_IsBad, 1) = 1
					and Person_id = :Person_id
					and DeathSvidType_id = :DeathSvidType_id
					and PntDeathSvid_id != :PntDeathSvid_id
					{$filter}
			";
			$result = $this->dbmodel->queryResult($query, $params);
			if (!is_array($result)) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Ошибка проверки на дублирование'));
			}
			if (count($result) > 0) {
				$this->response(array('error_code' => 6, 'error_msg' => 'Данные документа не прошли проверку на дублирование'));
			}
		}
		$resp = $this->dbmodel->saveMedSvidAPI($data,'pntdeath');
		if(!empty($resp['Error_Msg'])){
			$this->response(array(
				'error_msg' => $resp['Error_Msg'],
				'error_code' => '6'
			));
		}
		if (!is_array($resp) || empty($resp['svid_id'])) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		} else {
			$this->response(array(
				'error_code' => 0
			));
		}
	}

	/**
	 * Получение списка свидетельств о перинатальной смерти по человеку
	 */
	function PntDeathSvidList_get() {
		$data = $this->ProcessInputData('loadPntDeathSvidListByPerson');

		$resp = $this->dbmodel->loadPntDeathSvidListByPerson($data);
		if (!is_array($resp)) {
			$this->response(null, self::HTTP_INTERNAL_SERVER_ERROR);
		}

		$this->response(array(
			'error_code' => 0,
			'data' => $resp
		));
	}
}