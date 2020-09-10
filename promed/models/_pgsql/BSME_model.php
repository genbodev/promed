<?php

/**
 * Class BSME_model
 */
class BSME_model extends swPgModel
{

	/**
	 * Получение следующего за последним номера заявки
	 */
	public function getNextRequestNumber($data)
	{
		$query = '
			SELECT 
				COALESCE(MAX(EF.EvnForensic_Num),0)+1 as \"EvnForensic_Num\"
			FROM 
				v_EvnForensic EF
			';

		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/*
	array('field' => 'EvnForensic', 'label' => 'Идентификатор ', 'rules' => '', 'type' => 'id'),
	array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'string'),
	array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'datetime'),
	array('field' => 'EvnForensic', 'label' => 'Дата', 'rules' => '', 'type' => 'date'),
	array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'int'),
	array('field' => 'EvnForensic', 'label' => ' ', 'rules' => '', 'type' => 'time'),
	 */


	/**
	 * Функция проверки полноты заполнения журнала
	 * @param type $data проверяемые данные
	 * @param type $journalSingleFields массив имён полей простого типа : string, int
	 * @param type $journalArrayFields массив массивов для полей сложного типа : array:
	 *                    ['key'] - ключ в общем наборе данных $data (напр. 'Person')
	 *                    ['val'] - Имя поля, в котором хранится значение (напр. 'Person_id')
	 *
	 * @return string:
	 *        'filled' - указанный список полей полностью заполнен ,
	 *        'empty' - указанный список полей полностью незаполнен,
	 *        'unfinished' - указанный список полей заполнен частично
	 */
	protected function _checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields)
	{

		//Простейшая проверка входных данных
		if (!is_array($data) || !is_array($journalSingleFields) || !is_array($journalArrayFields)) {
			return false;
		}

		$hasFilledFields = false; // В наборе полей для журнала существуют пустые
		$hasEmptyFields = false; // В наборе полей для журнала существуют заполненные

		foreach ($journalSingleFields as $key) {
			$hasFilledFields = $hasFilledFields || !empty($data["$key"]);
			$hasEmptyFields = $hasEmptyFields || empty($data["$key"]);
		}

		foreach ($journalArrayFields as $item) {
			if (empty($item['key']) || empty($item['val'])) {
				return false;
			}
			$key = $item['key'];
			$value_key = $item['val'];
			$hasFilledFields = $hasFilledFields || ((sizeof($data["$key"]) > 0) && (!empty($data["$key"][0]["$value_key"])));
			$hasEmptyFields = $hasEmptyFields || ((sizeof($data["$key"]) <= 0) || (empty($data["$key"][0]["$value_key"])));
		}

		return ($hasFilledFields && $hasEmptyFields) ? 'unfinished' : ($hasFilledFields ? 'filled' : 'empty');

	}

	/**
	 * Функция сохранения вещественного докозательства или био образца
	 * @param int $data
	 * @return boolean
	 */
	protected function _saveEvidence($data)
	{

		$rules = array(
			array('field' => 'Evidence_Name', 'label' => 'наименование вещдока', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'EvidenceType_id', 'label' => 'Идентификатор типа экспертизы', 'rules' => '', 'type' => 'id', 'default' => 2),
			array('field' => 'Evidence_CorpStateName', 'label' => 'Состояние образца', 'rules' => '', 'type' => 'string', 'default' => null),
			array('field' => 'Evidence_CorpStatePack', 'label' => 'Упаковка', 'rules' => '', 'type' => 'string', 'default' => null),
			array('field' => 'Evidence_CorpStateKol', 'label' => 'Количество материала', 'rules' => '', 'type' => 'string', 'default' => null),
			array('field' => 'Evidence_ResearchDate', 'label' => 'Дата исследования', 'rules' => '', 'type' => 'date'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$selectString = "
            evidence_id as \"Evidence_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";

		$query = "
			select {$selectString} 
			from p_Evidence_ins (
				Evidence_Name := :Evidence_Name,
				EvnForensic_id := :EvnForensic_id,
				EvidenceType_id := :EvidenceType_id,
				Evidence_CorpStateName := :Evidence_CorpStateName,
				Evidence_CorpStatePack := :Evidence_CorpStatePack,
				Evidence_CorpStateKol := :Evidence_CorpStateKol,
				Evidence_ResearchDate := :Evidence_ResearchDate,
				pmUser_id := :pmUser_id
			);";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция удаления вещественного докозательства по идентификатору заявки
	 * @param int $data
	 * @return boolean
	 */
	protected function _deleteEvidence($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select 
			 	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_Evidence_delByEvnForensicId (
				EvnForensic_id = :EvnForensic_id
			);
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция получения PersonEvn_id и Server_id по Person_id
	 * @param type $data
	 * @return boolean
	 */
	protected function _getPersonStateByPersonId($data)
	{
		$rules = array(
			array('field' => 'Person_id', 'label' => 'Идетификатор человека', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				COALESCE(PS.PersonEvn_id,0) as \"PersonEvn_id\",
				COALESCE(PS.Server_id,0) as \"Server_id\"
			FROM
				v_PersonState PS
			WHERE 
				PS.Person_id = :Person_id
			LIMIT 1
			";

		return $this->queryResult($query, $data);
	}

	/**
	 * обработка входных данных файлов для дальнейшего сохранения
	 * @param type $data
	 * @return type
	 */
	protected function _processFileArray($data)
	{
		$rules = array(
			array('field' => 'Evn_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'comment', 'label' => 'Описание файлов', 'rules' => 'required', 'type' => 'assoc_array'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$files = array();

		foreach ($_FILES as $key => $value) {
			// $key = 'AttachmentField_[id]' обрезаем до '[id]'
			if (!empty($value['name'])) {
				$key = str_replace('AttachmentField_', '', $key);
				$files["$key"] = array();
				$files["$key"]['file'] = $value;
				$files["$key"]['filterType'] = 'all';
				$files["$key"]['Evn_id'] = $queryParams['Evn_id'];
				$files["$key"]['pmUser_id'] = $queryParams['pmUser_id'];
				$files["$key"]['saveOnce'] = 'true';
				$files["$key"]['FileDescr'] = (!empty($queryParams['comment']["$key"])) ? $queryParams['comment']["$key"] : null;
			}
		}

		return $files;
	}

	/**
	 * Функция "выдачи на руки" результатов экспертизы
	 */
	public function saveEvnForensicResultOut($data)
	{

		//
		// 1. Обновляем заявку
		//

		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_gid', 'label' => 'Лицо, получающее заключение', 'rules' => '', 'type' => 'id'),
			array('field' => 'RecipientIdentity_Num', 'label' => 'Номер удостоверения получающего заключение', 'rules' => 'max_length[100]', 'type' => 'string'),
			array('field' => 'PostTicket_Num', 'label' => 'Номер почтовой квитанции, куда отправлено заключение', 'rules' => 'max_length[100]', 'type' => 'string'),
			array('field' => 'PostTicket_Date', 'label' => 'Дата почтовой квитанции, куда отправлено заключение', 'rules' => '', 'type' => 'string'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$currentDataResult = $this->_getEvnForensic($queryParams);
		if (!$this->isSuccessful($currentDataResult)) {
			return $currentDataResult;
		}
		$queryParams = array_merge($currentDataResult[0], $queryParams);

		$query = "
			select 
			 	evnforensic_id as \"EvnForensic_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensic_upd (
				EvnForensic_id := :EvnForensic_id,
				EvnForensic_pid := :EvnForensic_pid,
				EvnForensic_rid := :EvnForensic_rid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonEvn_id,
				EvnForensic_setDT := :EvnForensic_setDT,
				EvnForensic_disDT := :EvnForensic_disDT,
				EvnForensic_didDT := :EvnForensic_didDT,
				EvnForensic_insDT := :EvnForensic_insDT,
				EvnForensic_updDT := :EvnForensic_updDT,
				EvnForensic_Index := :EvnForensic_Index,
				EvnForensic_Count := :EvnForensic_Count,
				Morbus_id := :Morbus_id,
				EvnForensic_IsSigned := :EvnForensic_IsSigned,
				pmUser_signID := :pmUser_signID,
				EvnForensic_signDT := :EvnForensic_signDT,
				EvnStatus_id := :EvnStatus_id,
				EvnForensic_statusDate := :EvnForensic_statusDate,
				EvnForensic_Num := :EvnForensic_Num,
				EvnForensic_ResDate := :EvnForensic_ResDate,
				MedService_id := :MedService_id,
				MedService_pid := :MedService_pid,
				EvnForensic_IsDistrict := :EvnForensic_IsDistrict,
				Person_gid := :Person_gid,
				EvnForensic_ResultOutDT := :EvnForensic_ResultOutDT,
				Person_cid := :Person_cid,
				RecipientIdentity_Num := :RecipientIdentity_Num,
				PostTicket_Num := :PostTicket_Num,
				PostTicket_Date := :PostTicket_Date,
				pmUser_id := :pmUser_id
			);
			";

		$this->db->trans_begin();

		$result = $this->queryResult($query, $queryParams);

		if (!$this->isSuccessful($result)) {
			$this->db->trans_rollback();
			return $result;
		}

		//
		// 2. Проставляем статус
		//

		$sataus_result = $this->changeEvnForensicStatus($data, 'Done');
		if (!$this->isSuccessful($sataus_result)) {
			$this->db->trans_rollback();
			return $sataus_result;
		}

		$this->db->trans_commit();
		return $result;

	}

	/**
	 * Функция получения данных родительского класса заявки
	 * @param type $data
	 * @return type
	 */
	protected function _getEvnForensic($data)
	{

		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				EF.EvnForensic_id as \"EvnForensic_id\", 
				EF.EvnForensic_pid as \"EvnForensic_pid\", 
				EF.EvnForensic_rid as \"EvnForensic_rid\", 
				EF.Lpu_id as \"Lpu_id\", 
				EF.Server_id as \"Server_id\", 
				EF.PersonEvn_id as \"PersonEvn_id\", 
				EF.EvnForensic_setDT as \"EvnForensic_setDT\", 
				EF.EvnForensic_disDT as \"EvnForensic_disDT\", 
				EF.EvnForensic_didDT as \"EvnForensic_didDT\", 
				EF.EvnForensic_insDT as \"EvnForensic_insDT\", 
				EF.EvnForensic_updDT as \"EvnForensic_updDT\", 
				EF.EvnForensic_Index as \"EvnForensic_Index\", 
				EF.EvnForensic_Count as \"EvnForensic_Count\", 
				EF.Morbus_id as \"Morbus_id\", 
				EF.EvnForensic_IsSigned as \"EvnForensic_IsSigned\", 
				EF.pmUser_signID as \"pmUser_signID\", 
				EF.EvnForensic_signDT as \"EvnForensic_signDT\", 
				EF.EvnStatus_id as \"EvnStatus_id\", 
				EF.EvnForensic_statusDate as \"EvnForensic_statusDate\", 
				EF.EvnForensic_Num as \"EvnForensic_Num\", 
				EF.EvnForensic_ResDate as \"EvnForensic_ResDate\", 
				EF.MedService_id as \"MedService_id\", 
				EF.MedService_pid as \"MedService_pid\", 
				EF.EvnForensic_IsDistrict as \"EvnForensic_IsDistrict\", 
				EF.Person_gid as \"Person_gid\", 
				EF.EvnForensic_ResultOutDT as \"EvnForensic_ResultOutDT\", 
				EF.Person_cid as \"Person_cid\", 
				EF.RecipientIdentity_Num as \"RecipientIdentity_Num\", 
				EF.PostTicket_Num as \"PostTicket_Num\", 
				EF.PostTicket_Date as \"PostTicket_Date\",
				EF.EvnForensic_Inherit as \"EvnForensic_Inherit\"
			FROM
				v_EvnForensic EF
			WHERE
				EF.EvnForensic_id = :EvnForensic_id
			";

		return $this->queryResult($query, $queryParams);

	}

	/**
	 * Функция сохранения заявки для службы судебно-химической экспертизы
	 * @return boolean
	 */
	public function saveForenChemRequest($data)
	{

		if (empty($data['MedService_id'])) {
			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
				} else {
					$data['MedService_id'] = $data['session']['CurMedService_id'];
				}
			} else {
				//Если передан MedService_fid - значит создается направление из другой службы и из сессии получить MedService_id службы,
				//которой направление назначается, не получится
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			}
		}

		$rules = array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительского события', 'type' => 'id'),
			array('field' => 'Evn_rid', 'label' => 'Идентификатор получателя документа', 'type' => 'id'),
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'MedService_pid', 'label' => 'Идентификатор службы - родителя', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => 'required', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select 
				hereditydiag_id as \"HeredityDiag_id\", 
				error_code as \"Error_Code\", 
				error_message as \"Error_Msg\"
			from p_EvnForensicChem_ins (
				EvnForensicChem_id := :EvnForensicChem_id,
				EvnForensicChem_Num := COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				MedService_id := :MedService_id,
				MedService_pid := :MedService_pid,
				EvnForensicChem_pid :=:Evn_pid,
				EvnForensicChem_rid :=:Evn_rid,
				pmUser_id := :pmUser_id,
				Lpu_id := :Lpu_id
			);
			";
		return $this->queryResult($query, $queryParams);

	}

	/**
	 * Получение связанной службы
	 * @param type $data
	 */
	protected function getLinkedForenMedService($data)
	{
		$rules = array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'reules' => 'required', 'type' => 'id'),
			array('field' => 'MedServiceLinkType_id', 'label' => 'Идентификатор связи', 'reules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$this->load->model('MedServiceLink_model', 'msl_model');
		$msl = $this->msl_model->loadList(array(
			'top1' => true,
			'MedSrervice_id' => $data['MedService_id'],
			'MedServiceLinkType_id' => $data['MedServiceLinkType_id']
		));

		if (!$this->isSuccessful($msl)) {
			return $this->createError('', 'Ошибка получения связанного отделения. Обратитесь к администратору');
		} elseif (empty($msl[0]['MedService_lid'])) {
			return $this->createError('', 'Не обнаружено связанного отделения указанного типа. </br> Обратитесь к администратору');
		}

		return $msl;
	}

	/**
	 * Функция сохранения направления на судебно-химическое исследование
	 * @return boolean
	 */
	public function saveForenChemDirection($data)
	{


		$this->db->trans_begin();

		if (empty($data['EvnForensicChem_id'])) {


			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор родительской службы');
				} else {
					$data['MedService_pid'] = $data['session']['CurMedService_id'];
				}
			}

			//Получаем связанную службу, в которую отправитяс направление

			$msl = $this->getLinkedForenMedService(array(
				'MedService_id' => $data['MedService_pid'],
				'MedServiceLinkType_id' => 11  //Отделение БСМЭ - Судебно-химическое отделение
			));
			if (!$this->isSuccessful($msl)) {
				return $msl;
			}

			$data['MedService_id'] = $msl[0]['MedService_lid'];


			//Сохраняем заявку
			$saveRequestResult = $this->saveForenChemRequest($data);

			if (!$this->isSuccessful($saveRequestResult)) {
				$this->db->trans_rollback();
				return $saveRequestResult;
			}

			$data = array_merge($data, $saveRequestResult[0]);
		}

		$data['Evn_rid'] = $data['Evn_pid'];

		$saveForenChemDirectionResult = $this->saveForenChemDirectionJournal($data);
		if (!$this->isSuccessful($saveForenChemDirectionResult)) {
			$this->db->trans_rollback();
			return $saveForenChemDirectionResult;
		}

		$this->db->trans_commit();
		return $saveRequestResult;

	}

	/**
	 * Функция сохранения записи в журнале направлений судебно-медицинских экспертов отдела судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @param type $data
	 */
	public function saveForenChemDirectionJournal($data)
	{

		//Если создается заявка-направление в ней уже может быть PersonEvn_id и Server_id
		if (empty($data['PersonEvn_id']) || empty($data['Server_id'])) {
			//Если PersonEvn_id и Server_id не переданы, должен быть передан Person_zid - идентификатор исследуемого лица
			if (empty($data['Person_zid'])) {
				return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр: Исследуемое лицо'));
			} else {
				$personState = $this->_getPersonStateByPersonId(array('Person_id' => $data['Person_zid']));
				if (!$this->isSuccessful($personState) || sizeof($personState) == 0) {
					return $this->createError('', 'Ошибка получения идентификатора состояния');
				} else {
					$data = array_merge($data, $personState[0]);
				}
			}
		}

		if (empty($data['MedPerson_sid'])) {
			if (empty($data['session']['medpersonal_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Назначивший эксперт');
			} else {
				$data['MedPerson_sid'] = $data['session']['medpersonal_id'];
			}
		}

		$rules = array(
			array('field' => 'EvnForensicChemDirection_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChem_id', 'label' => 'Идентификатор записи в журнале', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPerson_sid', 'label' => 'Идентификатор эксперта назначившего', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Evn_rid', 'label' => 'Идентификатор получающего результат события', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Jar', 'label' => 'Банки', 'rules' => '', 'type' => 'array'),
			array('field' => 'Flak', 'label' => 'Флаконы', 'rules' => '', 'type' => 'array'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicChemDirection_DeathDate', 'label' => 'Дата смерти', 'rules' => 'required', 'type' => 'string'),//string - т.к. уже преобразован в контроллере
			array('field' => 'EvnForensicChemDirection_DissectionDate', 'label' => 'Дата вскрытия', 'rules' => 'required', 'type' => 'string'),//string - т.к. уже преобразован в контроллере
			array('field' => 'EvnForensicChemDirection_Facts', 'label' => 'Обстоятельства дела', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicChemDirection_CauseOfDeath', 'label' => 'Предполагаемая причина смерти', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'EvnForensicChemDirection_Resolve', 'label' => 'Вопросы для разрешения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => 'required', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;


		$proc = 'p_EvnForensicChemDirection_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicChemDirection_id'])) {
			$proc = 'p_EvnForensicChemDirection_upd';
			$currentDataResult = $this->getForenChemDirectionJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}

			if (!empty($queryParams['Flak']) && !empty($queryParams['Jar'])) {
				$deleteEvidResult = $this->_deleteContainer(array('EvnForensic_id' => $queryParams['EvnForensicChemDirection_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		} else {
			if (empty($data['Evn_rid'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор получающего результат события');
			}
		}

		$query = "
			select 
				evnforensicchemdirection_id as \"EvnForensicChemDirection_id\", 
				error_code as \"Error_Code\", 
				error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicChemDirection_id :=  :EvnForensicChemDirection_id,
				
				EvnForensicChemDirection_pid := :EvnForensicChem_id,
				EvnForensicChemDirection_rid := :Evn_rid,
				EvnForensicChemDirection_DeathDate := :EvnForensicChemDirection_DeathDate,
				EvnForensicChemDirection_DissectionDate := :EvnForensicChemDirection_DissectionDate,
				EvnForensicChemDirection_Facts := :EvnForensicChemDirection_Facts,
				EvnForensicChemDirection_CauseOfDeath := :EvnForensicChemDirection_CauseOfDeath,
				MedPerson_sid := :MedPerson_sid,
				EvnForensicChemDirection_Resolve := :EvnForensicChemDirection_Resolve,
				
				Server_id :=  :Server_id,
				PersonEvn_id :=  :PersonEvn_id,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
			";

		$result = $this->queryResult($query, $queryParams);

		if (!$this->isSuccessful($result)) {
			return $result;
		}

		$containerType_arr = array(
			array('name' => 'Flak', 'EvnForensicChemDirectionMaterialsType_id' => 1),
			array('name' => 'Jar', 'EvnForensicChemDirectionMaterialsType_id' => 2)
		);

		foreach ($containerType_arr as $containerType) {
			foreach ($data["{$containerType['name']}"] as $container) {

				$container['EvnForensicChemDirection_id'] = $result[0]['EvnForensicChemDirection_id'];
				$container['EvnForensicChemDirectionMaterialsType_id'] = $containerType['EvnForensicChemDirectionMaterialsType_id'];
				$container['pmUser_id'] = $data['pmUser_id'];

				$saveContainerResult = $this->_saveContainer($container);
				if (!$this->isSuccessful($saveContainerResult)) {
					return $saveContainerResult;
				}
			}
		}

		return $result;
	}

	/**
	 * Функция удаления всех банок/флаконов из журнала направлений на судебно-химическую экспертизу
	 * @param type $data
	 * @return type
	 */
	protected function _deleteContainer($data)
	{
		$rules = array(
			array('field' => 'EvnForensicChemDirection_id', 'label' => 'Идентификатор записи в журнале', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select 
			 	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicChemDirectionMaterials_delByEvnForensicId (
				EvnForensicChemDirection_id := :EvnForensicChemDirection_id
			);
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция сохранения баноки/флакона для журнала направлений на судебно-химическую экспертизу
	 * @param type $data
	 * @return type
	 */

	protected function _saveContainer($data)
	{
		$rules = array(
			array('field' => 'EvnForensicChemDirection_id', 'label' => 'Идентификатор записи в журнале', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicChemDirectionMaterialsType_id', 'label' => 'Идентификатор формы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'description', 'label' => 'Описание', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'organ', 'label' => 'Орган', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'weight', 'label' => 'Вес', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'c2h5ohPerc', 'label' => 'Спирт (промили)', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'c2h5ohMl', 'label' => 'Спирт (мл)', 'rules' => 'required', 'type' => 'float'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);


		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select 
			 	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicChemDirectionMaterials_ins (
				EvnForensicChemDirection_id := :EvnForensicChemDirection_id,
				EvnForensicChemDirectionMaterialsType_id := :EvnForensicChemDirectionMaterialsType_id,
				EvnForensicChemDirectionMaterials_C2H5OHProof := :c2h5ohPerc,
				EvnForensicChemDirectionMaterials_C2H5OHVol	:= :c2h5ohMl,
				EvnForensicChemDirectionMaterials_Desc := :description,
				EvnForensicChemDirectionMaterials_Limb	:= :organ,
				EvnForensicChemDirectionMaterials_Weight := :weight,
				pmUser_id =:pmUser_id
				);
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * * Функция получения данных о записи в журнале направлений службы судебно-химической экспертизы
	 * @param type $data
	 * @return type
	 */
	public function getForenChemDirectionJournal($data)
	{
		$rules = array(
			array('field' => 'EvnForensicChemDirection_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				EFCD.EvnForensicChemDirection_id as \"EvnForensicChemDirection_id\",
				EFCD.EvnForensicChem_id as \"EvnForensicChem_id\",
				EFCD.EvnForensicChemDirection_DeathDate as \"EvnForensicChemDirection_DeathDate\",
				EFCD.EvnForensicChemDirection_DissectionDate as \"EvnForensicChemDirection_DissectionDate\",
				EFCD.EvnForensicChemDirection_Facts as \"EvnForensicChemDirection_Facts\",
				EFCD.EvnForensicChemDirection_CauseOfDeath as \"EvnForensicChemDirection_CauseOfDeath\",
				EFCD.MedPerson_sid as \"MedPerson_sid\",
				EFCD.EvnForensicChemDirection_Resolve as \"EvnForensicChemDirection_Resolve\",
				E.Person_Evn_id as \"Person_Evn_id\",
				E.Server_id as \"Server_id\"
			FROM
				v_EvnForenChemDirection EFCD 
				left join v_Evn E on E.Evn_id = EFCD.EvnForenChemDirection_id 
			WHERE
				EFCD.EvnForenChemDirection_id = :EvnForenChemDirection
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция сохранения заявки созданной внутри службы судебно-химической экспертизы
	 * @return boolean
	 */
	public function saveForenChemOwnRequest($data)
	{

		$this->db->trans_begin();

		//Заявку сохраняем только в том случа, если она ещё не сохранена (т.е. у неё не может быть идетификатора)
		if (empty($data['EvnForensicChem_id'])) {
			$saveRequestResult = $this->saveForenChemRequest($data);
			if (!$this->isSuccessful($saveRequestResult)) {
				$this->db->trans_rollback();
				return $saveRequestResult;
			}
			$data = array_merge($data, $saveRequestResult[0]);
		}

		//Сохраняем запись в журнале регистрации биоматериалов

		$saveBiomatResult = $this->saveForensicChemBiomatJournal($data);

		if (!$this->isSuccessful($saveBiomatResult)) {
			$this->db->trans_rollback();
			return $saveBiomatResult;
		}

		$this->db->trans_commit();
		return $saveRequestResult;
		//@TODO: Сохранение журналов направлений
	}

	/**
	 * Функция получения данных о записи в журнале биоматериалов службы судебно-химической экспертизы
	 * @return boolean
	 */
	public function getForensicChemBiomatJournal($data)
	{

		$rules = array(
			array('field' => 'EvnForensicChemBiomat_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		);
		$err = false;
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				EFCB.EvnForensicChemBiomat_id as \"EvnForensicChemBiomat_id\",
				EFCB.EvnForensicChem_id as \"EvnForensicChem_id\",
				EFCB.Person_sid as \"Person_sid\",
				EFCB.EvnForensicChemBiomat_Facts as \"EvnForensicChemBiomat_Facts\",
				EFCB.EvnForensicChemBiomat_Objective as \"EvnForensicChemBiomat_Objective\",
				EFCB.EvnForensicChemBiomat_EndDate as \"EvnForensicChemBiomat_EndDate\",
				EFCB.EvnForensicChemBiomat_Results as \"EvnForensicChemBiomat_Results\",
				EFCB.EvnForensicChemBiomat_IssueDate as \"EvnForensicChemBiomat_IssueDate\",
				EFCB.Person_rid as \"Person_rid\",
				EFCB.EvnForensicChemBiomat_ArchiveDate as \"EvnForensicChemBiomat_ArchiveDate\",
				EFCB.EvnForensicChemBiomat_ReceivedDate as \"EvnForensicChemBiomat_ReceivedDate\",
				E.Person_Evn_id as \"Person_Evn_id\",
				E.Server_id as \"Server_id\"
			FROM
				v_EvnForensicChemBiomat EFCB
				left join v_Evn E on E.Evn_id = EFCB.EvnForensicChemBiomat_id
			WHERE
				EFCB.EvnForensicChemBiomat_id = :EvnForensicChemBiomat_id
			";

		return $this->queryResult($query, $queryParams);

	}

	/**
	 * Функция сохранения записи в Журнале регистрации биоматериала в судебно-химическом отделении
	 * @param type $data
	 * @return type
	 */
	protected function saveForensicChemBiomatJournal($data)
	{


		$rules = array(
			array('field' => 'EvnForensicChemBiomat_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChem_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemBiomat_ReceivedDate', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'string'),//string - т.к. уже преобразовано
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_sid', 'label' => 'Лицо, направившее объекты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemBiomat_Facts', 'label' => '', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicChemBiomat_Objective', 'label' => 'Цель экспертизы', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicChemBiomat_EndDate', 'label' => 'Дата окончания экспертизы', 'rules' => '', 'type' => 'date'),
			array('field' => 'EvnForensicChemBiomat_IssueDate', 'label' => 'Дата окончания экспертизы', 'rules' => '', 'type' => 'date'),
			array('field' => 'Person_rid', 'label' => 'Лицо, направившее объекты', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicChemBiomat_ArchiveDate', 'label' => 'Дата передачи биоматериала в архив', 'rules' => '', 'type' => 'date'),
			array('field' => 'BioSample', 'label' => 'Биологические образцы', 'rules' => '', 'type' => 'array'),//array - т.к. уже преобразовано из JSON
			//array('field' => 'Evn_rid', 'label'=>'Идентификатор получающего результат события', 'type' => 'id'), //Журнал биоматериалов - исключительно внутренний журнал службы
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => 'required', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);
		/**
		 * EvnForensicChemBiomat_Results    varchar(1000)    Кратко результаты
		 */
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;


		$proc = 'p_EvnForensicChemBiomat_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if ($queryParams['EvnForensicChemBiomat_id']) {
			$proc = 'p_EvnForensicChemBiomat_upd';
			$currentDataResult = $this->getForensicChemBiomatJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}

			if (!empty($queryParams['BioSample'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id' => $queryParams['EvnForensicChemBiomat_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		} else {

			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан Person_zid - идентификатор исследуемого лица
				if (empty($queryParams['Person_zid'])) {
					return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр: Исследуемое лицо'));
				} else {
					$personState = $this->_getPersonStateByPersonId(array('Person_id' => $queryParams['Person_zid']));
					if (!$this->isSuccessful($personState) || sizeof($personState) == 0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams, $personState[0]);
					}
				}
			}
		}

		$query = "
			select 
			 	hereditydiag_id as \"HeredityDiag_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicChemBiomat_id :=  :EvnForensicChemBiomat_id,
				EvnForensicChemBiomat_pid := :EvnForensicChem_id,
				MedService_id :=  :MedService_id,
				EvnForensicChemBiomat_ReceivedDate :=  :EvnForensicChemBiomat_ReceivedDate,
				Person_sid :=  :Person_sid,
				EvnForensicChemBiomat_Facts :=  :EvnForensicChemBiomat_Facts,
				EvnForensicChemBiomat_Objective :=  :EvnForensicChemBiomat_Objective,
				EvnForensicChemBiomat_EndDate :=  :EvnForensicChemBiomat_EndDate,
				--@EvnForensicChemBiomat_Results :=  EvnForensicChemBiomat_Results,
				EvnForensicChemBiomat_IssueDate :=  :EvnForensicChemBiomat_IssueDate,
				Person_rid :=  :Person_rid,
				EvnForensicChemBiomat_ArchiveDate :=  :EvnForensicChemBiomat_ArchiveDate,
				Server_id :=  :Server_id,
				PersonEvn_id :=  :PersonEvn_id,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);";

		$result = $this->queryResult($query, $queryParams);

		if (!$this->isSuccessful($result)) {
			$this->db->trans_rollback();
			return $result;
		}

		foreach ($data['BioSample'] as $evidence) {
			if (empty($evidence['BioSample_Name'])) {
				return $this->createError('', 'Не указан обязательный параметр: Наименование вещдока');
			}
			$saveEvidenceResult = $this->_saveEvidence(array(
				'EvnForensic_id' => $result[0]['EvnForensicChemBiomat_id'],
				'Evidence_Name' => $evidence['BioSample_Name'],
				'EvidenceType_id' => 1,
				'pmUser_id' => $data['pmUser_id'],
			));
			if (!$this->isSuccessful($saveEvidenceResult)) {
				return $saveEvidenceResult;
			}
		}

		return $result;
	}

	/**
	 * Функция первичного сохранения заявки (серкетарём) внутри службы медико-криминалистической экспертизы
	 */
	public function saveEvnForenCrimeOwnRequest($data)
	{

		/* Сохранение заявки */
		$this->db->trans_begin();

		if (empty($data['EvnForensicCrime_id'])) {

			$result = $this->_saveEvnForenCrimeRequest($data);
			if (!$this->isSuccessful($result)) {
				$this->db->trans_rollback();
				return $result;
			}

			$data['EvnForensicCrime_id'] = $result[0]['EvnForensicCrime_id'];

		}

		$journalsCount = 0; //Счетчик заполненных журналов

		/****************************************************************************************** */
		/* Сохранение журнала регистрации вещественных доказательств и документов к ним в лаборатории */
		/****************************************************************************************** */

		// Если запись в журнале формируется впервые необходимо проверить наличие обязательных полей

		$journalSingleRequiredFields = array(
			'EvnForensicCrimeEvid_ForDate',
			'EvnForensicCrimeEvid_AccDocNum',
			'EvnForensicCrimeEvid_AccDocDate',
			'EvnForensicCrimeEvid_AccDocNumSheets',
			'Org_id',
			'EvnForensicCrimeEvid_Facts',
			'EvnForensicCrimeEvid_Goal'
		);

		$journalArrayRequiredFields = array(
			array('key' => 'Person', 'val' => 'Person_id'),
			array('key' => 'Evidence', 'val' => 'Evidence_Name')
		);

		//Получаем статус заполненности журнала [empty | unfinished | filled]
		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleRequiredFields, $journalArrayRequiredFields);

		if (!$journalFilledStatus) {
			$this->db->trans_rollback();
			return $this->createError('', 'Ошибка проверки существования обязательных полей');
		}

		if ($journalFilledStatus == 'unfinished') {

			$this->db->trans_rollback();
			return $this->createError('', 'Не все поля журнала регистрации вещественных </br> доказательств и документов к ним в лаборатории заполнены. </br> Пожалуйста, заполните все поля');

		} elseif ($journalFilledStatus == 'filled') {

			$saveJournalResult = $this->_saveEvnForensicCrimeEvid($data);

			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			}

			$journalsCount++;
		}


		/****************************************************************************************** */
		/* Сохранение журнала регистрации фоторабот													*/
		/****************************************************************************************** */

		// Если запись в журнале формируется впервые необходимо проверить наличие обязательных полей
		$journalSingleFields = array(
			'EvnForensicCrimePhot_ActNum',
			'EvnForensicCrimePhot_ShoDate',
			'EvnForensicCrimePhot_Person_zid',
			'Diag_id',
			'EvnForensicCrimePhot_PosKol',
			'EvnForensicCrimePhot_NegKol',
			'EvnForensicCrimePhot_SighSho',
			'EvnForensicCrimePhot_Micro',
			'EvnForensicCrimePhot_Macro',
		);

		$journalArrayFields = array();

		//Получаем статус заполненности журнала [empty | unfinished | filled]
		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

		if (!$journalFilledStatus) {
			$this->db->trans_rollback();
			return $this->createError('', 'Ошибка проверки существования обязательных полей');
		}

		if ($journalFilledStatus == 'unfinished') {

			$this->db->trans_rollback();
			return $this->createError('', 'Не все поля журнала регистрации фоторабот заполнены. </br> Пожалуйста, заполните все поля');

		} elseif ($journalFilledStatus == 'filled') {

			$saveJournalResult = $this->_saveEvnForensicCrimePhot($data);

			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			}

			$journalsCount++;
		}

		/********************************************************************************************/
		/* Проверка журнала регистрации разрушений почки на планктон								*/
		/****************************************************************************************** */

		$journalSingleFields = array(
			'EvnForensicCrimeDesPlan_ForDate',
			'Person_eid',
			//'Person_zid', - необязательный, т.к. труп может быть неидентифицирован
			'EvnForensicCrimeDesPlan_ActCorpNum',
			'EvnForensicCrimeDesPlan_ActCorpDate',
			'EvnForensicCrimeDesPlan_Facts',
		);
		$journalArrayFields = array();

		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

		if (!$journalFilledStatus) {
			$this->db->trans_rollback();
			return $this->createError('', 'Ошибка проверки существования обязательных полей');
		}

		if ($journalFilledStatus == 'unfinished') {
			$this->db->trans_rollback();
			return $this->createError('', 'Не все поля журнала регистрации разрушений почки на планктон заполнены. </br> Пожалуйста, заполните все поля');
		} elseif ($journalFilledStatus == 'filled') {
			$saveJournalResult = $this->_saveEvnForensicCrimeDesPlan($data);

			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			}

			$journalsCount++;
		}

		if ($journalsCount == 0) {
			return array(array('succes' => false, 'Error_Msg' => 'Не заполнено ни одного журнала </br> Пожалуйста, заполните хотя бы один журнал.'));
		}

		$this->db->trans_commit();
		return $result;

	}

	/**
	 * Функция сохранения сущности заявки службы медико-криминалистической экспертизы
	 * @param type $data
	 * @return type
	 */
	protected function _saveEvnForenCrimeRequest($data)
	{
		if (empty($data['MedService_id'])) {
			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
				} else {
					$data['MedService_id'] = $data['session']['CurMedService_id'];
				}
			} else {
				//Если передан MedService_fid - значит создается направление из другой службы и из сессии получить MedService_id службы,
				//которой направление назначается, не получится
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			}
		}

		$rules = array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительского события', 'type' => 'id'),
			array('field' => 'EvnForensic_ResDate', 'label' => 'Дата постановления', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evn_rid', 'label' => 'Идентификатор получателя документа', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_pid', 'label' => 'Идентификатор службы - родителя', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => '', 'label' => 'Идентификатор МО', 'type' => 'id'),
			array('field' => 'Person_cid', 'label' => 'Назначившее лицо', 'rules' => '', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = '
			select 
			    evnforensiccrime_id as \"EvnForensicCrime_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicCrime_ins (
				EvnForensicCrime_id :=  :EvnForensicCrime_id,
				EvnForensicCrime_Num :=  COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				EvnForensicCrime_ResDate :=  :EvnForensic_ResDate,
				MedService_id :=  :MedService_id,
				MedService_pid :=  :MedService_pid,
				EvnForensicCrime_pid := :Evn_pid,
				EvnForensicCrime_rid := :Evn_rid,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
			';
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция сохранения записи в журнале регистрации вещественных доказательств и документов к ним в лаборатории
	 * для службы медико-криминалистической экспертизы
	 * @param type $data
	 * @return boolean
	 */
	protected function _saveEvnForensicCrimeEvid($data)
	{

		$rules = array(
			array('field' => 'EvnForensicCrime_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeEvid_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),

			array('field' => 'EvnForensicCrimeEvid_ForDate', 'label' => 'Дата поступления вещественных доказательств', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_AccDocNum', 'label' => '№ основного сопроводительного документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_AccDocDate', 'label' => 'Дата основного сопроводительного документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_AccDocNumSheets', 'label' => 'Кол-во листов документов', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_id', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person', 'label' => 'Потерпевшие/обвиняемые', 'rules' => '', 'type' => 'array'),// преобразовано из string в array контроллере
			array('field' => 'Evidence', 'label' => 'Вещественные доказательства', 'rules' => '', 'type' => 'array'),
			array('field' => 'EvnForensicCrimeEvid_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeEvid_Goal', 'label' => 'Цель экспертизы', 'rules' => '', 'type' => 'string'),

			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => '', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$proc = 'p_EvnForensicCrimeEvid_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicCrimeEvid_id'])) {
			$proc = 'p_EvnForensicCrimeEvid_upd';
			$currentDataResult = $this->_getEvnForensicCrimeEvid($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}

			//Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
			if (!empty($queryParams['Evidence'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id' => $queryParams['EvnForensicCrimeEvid_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
			if (!empty($queryParams['Person'])) {
				$deleteEvidResult = $this->_deleteEvnForensicCrimeEvidLink(array('EvnForensicCrimeEvid_id' => $queryParams['EvnForensicCrimeEvid_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		}

		$query = "
			select
			 	evnforensiccrimeevid_id as \"EvnForensicCrimeEvid_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicCrimeEvid_id :=  :EvnForensicCrimeEvid_id,
				EvnForensicCrimeEvid_pid :=  :EvnForensicCrime_id,
				EvnForensicCrimeEvid_ForDate :=  :EvnForensicCrimeEvid_ForDate,
				EvnForensicCrimeEvid_AccDocNum :=  :EvnForensicCrimeEvid_AccDocNum,
				EvnForensicCrimeEvid_AccDocDate :=  :EvnForensicCrimeEvid_AccDocDate,
				EvnForensicCrimeEvid_AccDocNumSheets :=  :EvnForensicCrimeEvid_AccDocNumSheets,
				Org_id :=  :Org_id,
				EvnForensicCrimeEvid_Facts :=  :EvnForensicCrimeEvid_Facts,
				EvnForensicCrimeEvid_Goal :=  :EvnForensicCrimeEvid_Goal,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
			";

		$result = $this->queryResult($query, $queryParams);
		//Сохраняем потерпевших/обвиняемых

		foreach ($queryParams['Person'] as $person) {
			$person['EvnForensicCrimeEvid_id'] = $result[0]['EvnForensicCrimeEvid_id'];
			$person['pmUser_id'] = $queryParams['pmUser_id'];
			$saveEvnForensicCrimeEvidLinkResult = $this->_saveEvnForensicCrimeEvidLink($person);
			if (!$this->isSuccessful($saveEvnForensicCrimeEvidLinkResult)) {
				return $saveEvnForensicCrimeEvidLinkResult;
			}
		}

		//Сохраняем вещественные доказательства
		foreach ($queryParams['Evidence'] as $evidence) {
			$evidence = array_merge($evidence, array(
				'EvnForensic_id' => $result[0]['EvnForensicCrimeEvid_id'],
				'pmUser_id' => $queryParams['pmUser_id'],
				'EvidenceType_id' => 2
			));

			$saveEvidence = $this->_saveEvidence($evidence);
			if (!$this->isSuccessful($saveEvidence)) {
				return $saveEvidence;
			}
		}

		return $result;

	}

	/**
	 * Функция сохранения записи в журнале регистрации вещественных доказательств и документов к ним в лаборатории
	 * для службы медико-криминалистической экспертизы
	 * @param type $data
	 */
	protected function _getEvnForensicCrimeEvid($data)
	{
		$rules = array(
			array('field' => 'EvnForensicCrimeEvid_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = '
			SELECT
				EFCE.EvnForensicCrime_id as \"EvnForensicCrime_id\",
				EFCE.EvnForensicCrimeEvid_id as \"EvnForensicCrimeEvid_id\",
				EFCE.EvnForensicCrimeEvid_ForDate as \"EvnForensicCrimeEvid_ForDate\",--Дата поступления вещдока
				EFCE.EvnForensicCrimeEvid_AccDocDate as \"EvnForensicCrimeEvid_AccDocDate\",--дата основного сопроводительного документа
				EFCE.EvnForensicCrimeEvid_AccDocNum as \"EvnForensicCrimeEvid_AccDocNum\",--номер основного сопроводительного документа
				EFCE.EvnForensicCrimeEvid_AccDocNumSheets as \"EvnForensicCrimeEvid_AccDocNumSheets\",--количество  листов документов
				EFCE.Org_id as \"Org_id\",--учреждение направившего
				EFCE.EvnForensicCrimeEvid_Facts as \"EvnForensicCrimeEvid_Facts\",--Кратко обстоятельства дела
				EFCE.EvnForensicCrimeEvid_Goal as \"EvnForensicCrimeEvid_Goal\",--цель экспертизы
				EFCE.MedPersonal_id as \"MedPersonal_id\",--фио эксперта
				EFCE.Lpu_id as \"Lpu_id\"
			FROM
				v_EvnForensicCrimeEvid EFCE
			WHERE
				EFCE.EvnForensicCrime_id = :EvnForensicCrimeEvid_id
			';

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция сохранения записи журанла регистрации фото работ в мед./крим. отделении
	 * @param type $data
	 * @return type
	 */
	protected function _saveEvnForensicCrimePhot($data)
	{

		$rules = array(
			array('field' => 'EvnForensicCrime_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicCrimePhot_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),

			array('field' => 'EvnForensicCrimePhot_ActNum', 'label' => '№ Акта', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_ShoDate', 'label' => 'Дата съёмки', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimePhot_Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'Diag_id', 'label' => 'Судмед диагноз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimePhot_PosKol', 'label' => 'Количество позитивов', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_NegKol', 'label' => 'Количество негативов', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_SighSho', 'label' => 'Обзорная съемка', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_Macro', 'label' => 'Макро съемка', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimePhot_Micro', 'label' => 'Микро съемка', 'rules' => '', 'type' => 'int'),

			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => '', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$proc = 'p_EvnForensicCrimePhot_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicCrimePhot_id'])) {
			$proc = 'p_EvnForensicCrimePhot_upd';
			$currentDataResult = $this->getEvnForensicCrimePhot($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}
		} else {
			// У вновь создаваемой записи журнала этого типа PersonEvn_id и Server_id обязательны и получаются из Person_zid
			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан Person_zid - идентификатор исследуемого лица
				if (empty($queryParams['EvnForensicCrimePhot_Person_zid'])) {
					return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр: Исследуемое лицо'));
				} else {
					$personState = $this->_getPersonStateByPersonId(array('Person_id' => $queryParams['EvnForensicCrimePhot_Person_zid']));
					if (!$this->isSuccessful($personState) || sizeof($personState) == 0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams, $personState[0]);
					}
				}
			}
		}

		$query = "
			select
			 	evnforensiccrimephot_id as \"EvnForensicCrimePhot_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicCrimePhot_id :=  :EvnForensicCrimePhot_id,
				EvnForensicCrimePhot_pid :=  :EvnForensicCrime_id,
				
				EvnForensicCrimePhot_ActNum:= :EvnForensicCrimePhot_ActNum,
				EvnForensicCrimePhot_ShoDate:= :EvnForensicCrimePhot_ShoDate,
				Diag_id:= :Diag_id,
				EvnForensicCrimePhot_NegKol:= :EvnForensicCrimePhot_NegKol,
				EvnForensicCrimePhot_PosKol:= :EvnForensicCrimePhot_PosKol,
				EvnForensicCrimePhot_SighSho:= :EvnForensicCrimePhot_SighSho,
				EvnForensicCrimePhot_Macro:= :EvnForensicCrimePhot_Macro,
				EvnForensicCrimePhot_Micro:= :EvnForensicCrimePhot_Micro,

				PersonEvn_id :=  :PersonEvn_id,
				Server_id :=  :Server_id,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция получения записи журанла регистрации фото работ в мед./крим. отделении
	 * @param type $data
	 * @return type
	 */
	protected function getEvnForensicCrimePhot($data)
	{
		$rules = array(
			array('field' => 'EvnForensicCrimePhot_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				EFCP.EvnForensicCrime_id as \"EvnForensicCrime_id\",
				EFCP.EvnForensicCrimePhot_id as \"EvnForensicCrimePhot_id\",

				EFCP.EvnForensicCrimePhot_ActNum as \"EvnForensicCrimePhot_ActNum\",--№ акта
				EFCP.EvnForensicCrimePhot_ShoDate as \"EvnForensicCrimePhot_ShoDate\",--Дата съемки 
				EFCP.Diag_id as \"Diag_id\",--Судмед диагноз
				EFCP.EvnForensicCrimePhot_NegKol as \"EvnForensicCrimePhot_NegKol\",--Кол-во негативов
				EFCP.EvnForensicCrimePhot_PosKol as \"EvnForensicCrimePhot_PosKol\",--Кол-во позитивов
				EFCP.EvnForensicCrimePhot_SighSho as \"EvnForensicCrimePhot_SighSho\",--Обзорная съемка
				EFCP.EvnForensicCrimePhot_Macro as \"EvnForensicCrimePhot_Macro\",--Макро съемка
				EFCP.EvnForensicCrimePhot_Micro as \"EvnForensicCrimePhot_Micro\",--Микро съемка

				EFCP.PersonEvn_id as \"PersonEvn_id\",
				EFCP.Server_id as \"Server_id\",
				EFCP.Lpu_id as \"Lpu_id\"
			FROM
				v_EvnForensicCrimePhot EFCP
			WHERE
				EFCE.EvnForensicCrimePhot_id = :EvnForensicCrimePhot_id
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция сохранения записи журанла регистрации разрушений почки на планктон в мед./крим. отделении
	 * @param type $data
	 * @return type
	 */
	protected function _saveEvnForensicCrimeDesPlan($data)
	{

		//Назначивший эксперт может быть только в случае, если сохраняется направление
		//А если передано назначившее лицо значит сохраняется внутри службы с постановлением от правоохранительных органов
		if (empty($data['MedPersonal_id']) && empty($data['Person_eid'])) {
			if (empty($data['session']['medpersonal_id'])) {
				return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор назначившего эксперта'));
			} else {
				$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			}
		}

		$rules = array(
			array('field' => 'EvnForensicCrime_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeDesPlan_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),

			array('field' => 'MedPersonal_id', 'label' => 'Cотрудник назначивший экспертизу', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeDesPlan_ForDate', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'string'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_cid', 'label' => 'Назначившее лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeDesPlan_ActCorpNum', 'label' => '№ акта вскрытия', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicCrimeDesPlan_ActCorpDate', 'label' => 'Дата вскрытия', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeDesPlan_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),

			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => '', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$proc = 'p_EvnForensicCrimeDesPlan_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicCrimeDesPlan_id'])) {
			$proc = 'p_EvnForensicCrimeDesPlan_upd';
			$currentDataResult = $this->getEvnForensicCrimeDesPlan($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}
		} else {
			// У вновь создаваемой записи журнала этого типа PersonEvn_id и Server_id обязательны и получаются из Person_zid
			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан Person_zid - идентификатор исследуемого лица
				if (!empty($queryParams['Person_zid'])) {

					$personState = $this->_getPersonStateByPersonId(array('Person_id' => $queryParams['Person_zid']));
					if (!$this->isSuccessful($personState) || sizeof($personState) == 0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams, $personState[0]);
					}
				}
			}
		}

		$query = "
			select
			 	evnforensiccrimedesplan_id as \"EvnForensicCrimeDesPlan_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicCrimeDesPlan_id :=  :EvnForensicCrimeDesPlan_id,
				EvnForensicCrimeDesPlan_pid :=  :EvnForensicCrime_id,
				
				EvnForensicCrimeDesPlan_ForDate:= :EvnForensicCrimeDesPlan_ForDate,
				MedPersonal_id:= :MedPersonal_id,
				EvnForensicCrimeDesPlan_ActCorpNum:= :EvnForensicCrimeDesPlan_ActCorpNum,
				EvnForensicCrimeDesPlan_ActCorpDate:= :EvnForensicCrimeDesPlan_ActCorpDate,
				EvnForensicCrimeDesPlan_Facts:= :EvnForensicCrimeDesPlan_Facts,
				Person_cid:= :Person_cid,

				PersonEvn_id :=  :PersonEvn_id,
				Server_id :=  :Server_id,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
				);
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция получения записи журанла регистрации разрушений почки на планктон в мед./крим. отделении
	 * @param type $data
	 * @return type
	 */
	protected function getEvnForensicCrimeDesPlan($data)
	{
		$rules = array(
			array('field' => 'EvnForensicCrimeDesPlan_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				EFCDP.EvnForensicCrime_id as \"EvnForensicCrime_id\",
				EFCDP.EvnForensicCrimeDesPlan_id as \"EvnForensicCrimeDesPlan_id\",

				EFCDP.EvnForensicCrimeDesPlan_ForDate as \"EvnForensicCrimeDesPlan_ForDate\",--Дата поступления
				EFCDP.MedPersonal_id as \"MedPersonal_id\",--ФИО эксперта назначившего
				EFCDP.EvnForensicCrimeDesPlan_isIden as \"EvnForensicCrimeDesPlan_isIden\",--Признак идентифицированного трупа
				EFCDP.EvnForensicCrimeDesPlan_ActCorpNum as \"EvnForensicCrimeDesPlan_ActCorpNum\",--№ акта вскрытия
				EFCDP.EvnForensicCrimeDesPlan_ActCorpDate as \"EvnForensicCrimeDesPlan_ActCorpDate\",--дата акта вскрытия
				EFCDP.EvnForensicCrimeDesPlan_Facts as \"EvnForensicCrimeDesPlan_Facts\",--Обстоятельства дела
				EFCDP.Person_cid as \"Person_cid\",--ФИО направившего лица

				EFCDP.PersonEvn_id as \"PersonEvn_id\",
				EFCDP.Server_id as \"Server_id\",
				EFCDP.Lpu_id as \"Lpu_id\"
			FROM
				v_EvnForensicCrimeDesPlan EFCDP
			WHERE
				EFCDP.EvnForensicCrimeDesPlan_id = :EvnForensicCrimeDesPlan_id
		";

		return $this->queryResult($query, $queryParams);
	}


	/**
	 * Функция сохранения подозреваемого/обвиняемого для журнала регистрации вещественных доказательств и документов к ним в лаборатории
	 * для службы мед.-крим. отделения
	 * @param type $data
	 * @return boolean
	 */
	protected function _saveEvnForensicCrimeEvidLink($data)
	{
		$rules = array(
			array('field' => 'EvnForensicCrimeEvid_id', 'label' => 'Идентификатор записи журнала', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Person_id', 'label' => 'Идентификатор обвиняемого/потерпевшего', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeEvidLink_IsVic', 'label' => 'флаг потерпевшего', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select
			 	evnforensiccrimeevidlink_id as \"EvnForensicCrimeEvidLink_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicCrimeEvidLink_ins (
				EvnForensicCrimeEvidLink_id :=  :EvnForensicCrimeEvidLink_id,
				EvnForensicCrimeEvid_id :=  :EvnForensicCrimeEvid_id,
				Person_id :=  :Person_id,
				EvnForensicCrimeEvidLink_IsVic :=  :EvnForensicCrimeEvidLink_IsVic,
				
				pmUser_id :=  :pmUser_id
			);";

		return $this->queryResult($query, $queryParams);

	}

	/**
	 * Функция удаления всех подозреваемых/обвиняемых для записи журнала регистрации вещественных доказательств и документов к ним в лаборатории
	 * для службы мед.-крим. отделения
	 * @param type $data
	 * @return type
	 */
	protected function _deleteEvnForensicCrimeEvidLink($data)
	{
		$rules = array(
			array('field' => 'EvnForensicCrimeEvid_id', 'label' => 'Идентификатор записи журнала', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select 
			 	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicCrimeEvidLink_delByEvnForensicId (
				EvnForensic_id := :EvnForensic_id
			);
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция сохранения направления на исследования трупной крови в службе судебно-биологического
	 * отделения для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @param type $data
	 */
	public function saveForenCorpBloodDirection($data)
	{

		$this->db->trans_begin();

		// Если направление ещё не создано, создаём заявку в судебно-биологической службе

		if (empty($data['EvnForensicGenetic_id'])) {

			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор родительской службы');
				} else {
					$data['MedService_pid'] = $data['session']['CurMedService_id'];
				}
			}

			//Получаем связанную службу, в которую отправитяс направление

			$msl = $this->getLinkedForenMedService(array(
				'MedService_id' => $data['MedService_pid'],
				'MedServiceLinkType_id' => 12  //Отделение БСМЭ - Судебно-химическое отделение
			));
			if (!$this->isSuccessful($msl)) {
				return $msl;
			}

			$data['MedService_id'] = $msl[0]['MedService_lid'];

			//Сохраняем заявку
			$saveRequestResult = $this->saveEvnForensicGenetic($data);

			if (!$this->isSuccessful($saveRequestResult)) {
				$this->db->trans_rollback();
				return $saveRequestResult;
			}

			$data = array_merge($data, $saveRequestResult[0]);
		}

		$data['Evn_rid'] = $data['Evn_pid'];
		$this->load->model('EvnForensicGenetic_model', 'efgmodel');
		$resultEvnForensicGeneticCadBloodSave = $this->efgmodel->saveEvnForensicGeneticCadBlood($data);

		if (!$this->isSuccessful($resultEvnForensicGeneticCadBloodSave)) {
			$this->db->trans_rollback();
		}

		$this->db->trans_commit();
		return $resultEvnForensicGeneticCadBloodSave;

	}

	/**
	 * Функция сохранения направления на исследования вещественных доказательств в службе медико-криминалистического
	 * отделения для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @return boolean
	 */
	public function saveForenEvidDirection($data)
	{

	}

	/**
	 * Функция сохранения направления на исследования разрушения почки на планктон в службе медико-криминалистического
	 * отделения для службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @return boolean
	 */
	public function saveForenKidneyPlanktDirection($data)
	{

	}

	/**
	 * Функция сохранения направления на на наличие диатомового планктона
	 * @return boolean
	 */
	public function saveForenDiamPlanktDirection($data)
	{

	}

	/**
	 * Функция сохранения направления на биохимическое исследование
	 * @return boolean
	 */
	public function saveForenBioChemDirection($data)
	{

	}

	/**
	 * Функция сохранения направления на исследование образцов крови в ИФА на антитела к ВИЧ
	 * @return boolean
	 */
	public function saveForenBludSampleDirection($data)
	{

	}

	/**
	 * Функция получения данных для заполнения вьюхи печатной формы для направления на исследование образцов крови в ИФА на антитела к ВИЧ
	 */
	public function printBludSampleResearchDirection($data)
	{

	}

	/**
	 * Функция сохранения направления на вирусологическое исследование
	 * @return boolean
	 */
	public function saveForenVirusologicDirection($data)
	{

	}

	/**
	 * Функция получения данных для заполнения вьюхи печатной формы для направления на вирусологическое исследование
	 */
	public function printVirusologicResearchDirection($data)
	{

	}


	/**
	 * Функция получения списка вещдоков/биологичеких образцов
	 * @param type $data
	 * @return boolean
	 */
	protected function getEvidenceList($data)
	{

		if (empty($data['EvnForensic_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор случая'));
		}

		$query = "
			SELECT
				E.Evidence_id as \"Evidence_id\",
				E.Evidence_Name as \"Evidence_Name\",
				E.Evidence_CorpStateName as \"Evidence_CorpStateName\",
				E.Evidence_CorpStatePack as \"Evidence_CorpStatePack\",
				E.Evidence_CorpStateKol as \"Evidence_CorpStateKol\",
				to_char(E.Evidence_ResearchDate, 'dd.mm.yyyy') as \"Evidence_ResearchDate\",
				E.EvnForensic_id as \"EvnForensic_id\"
			FROM 
				v_Evidence E
			WHERE
				E.EvnForensic_id = :EvnForensic_id
			";
		$result = $this->db->query($query, array(
			'EvnForensic_id' => $data['EvnForensic_id']
		));
		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}


	/**
	 * Функция сохранения заявки созданной внутри службы судебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @param type $data
	 * @return boolean
	 */
	public function saveForenCorpOwnRequest($data)
	{

		if (empty($data['Person_zid'])) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Доставившее лицо'));
		}
		if (empty($data['Person_id'])) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Исследуемое лицо'));
		}
		if (empty($data['Evidence'])) {
			$data['Evidence'] = array();
		}
		if (empty($data['ValueStuff'])) {
			$data['ValueStuff'] = array();
		}
		if (empty($data['Lpu_id'])) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор МО'));
		}


		if (empty($data['MedService_id'])) {
			if (empty($data['session']) && empty($data['session']['CurMedService_id'])) {
				return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор службы'));
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$personState = $this->_getPersonStateByPersonId(array('Person_id' => $data['Person_id']));
		if (!$personState || empty($personState[0]) || !isset($personState[0]['PersonEvn_id']) || !isset($personState[0]['Server_id'])) {
			return array(array('success' => false, 'Error_Msg' => 'Ошибка получения идентификатора состояния'));
		}
		$data['PersonEvn_id'] = $personState[0]['PersonEvn_id'];
		$data['Server_id'] = $personState[0]['Server_id'];

		/* Сохранение заявки */
		$this->db->trans_begin();

		$insertEvnForensicCorpHistQuery = '
			select
				evnforensiccorphist_id as \"EvnForensicCorpHist_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicCorpHist_ins (
				EvnForensicCorpHist_id :=  :EvnForensicCorpHist_id,
				EvnForensicCorpHist_Num :=  COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				PersonEvn_id :=  :PersonEvn_id,
				MedService_id :=  :MedService_id,
				Server_id :=  :Server_id,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
			';


		$insertEvnForensicCorpHistResult = $this->db->query($insertEvnForensicCorpHistQuery, array(
			'EvnForensicCorpHist_id' => null,
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'MedService_id' => $data['MedService_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
		));

		if (!is_object($insertEvnForensicCorpHistResult)) {
			$this->db->trans_rollback();
			return false;
		}

		$insertEvnForensicCorpHistResult = $insertEvnForensicCorpHistResult->result('array');

		if (empty($insertEvnForensicCorpHistResult) || empty($insertEvnForensicCorpHistResult[0]) || strlen($insertEvnForensicCorpHistResult[0]['Error_Msg'] > 0)) {
			$this->db->trans_rollback();
			return $insertEvnForensicCorpHistResult;
		}

		$data['EvnForensicCorpHistJourMorg_pid'] = $insertEvnForensicCorpHistResult[0]['EvnForensicCorpHist_id'];

		/* Сохранение журнала приема трупов и вещественных доказательств в морг */

		$insertEvnForensicCorpHistJourMorgResult = $this->saveForenCorpHistJourMorgJournal(array(
			'EvnForensicCorpHistJourMorg_pid' => $data['EvnForensicCorpHistJourMorg_pid'],
			'Person_zid' => $data['Person_zid'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id']
		));

		if (empty($insertEvnForensicCorpHistJourMorgResult) || empty($insertEvnForensicCorpHistJourMorgResult[0]) || strlen($insertEvnForensicCorpHistJourMorgResult[0]['Error_Msg']) > 0) {
			$this->db->trans_rollback();
			return $insertEvnForensicCorpHistJourMorgResult;
		}

		$data['Evn_pid'] = $insertEvnForensicCorpHistJourMorgResult[0]['EvnForensicCorpHistJourMorg_id'];

		//Сохраняем вещдоки и одежду

		foreach ($data['Evidence'] as $evidence) {
			$saveEvidenceResult = $this->_saveEvidence(array(
				'EvnForensic_id' => $data['Evn_pid'],
				'Evidence_Name' => $evidence['Evidence_Name'],
				'EvidenceType_id' => 1,
				'pmUser_id' => $data['pmUser_id'],
			));
			if (empty($saveEvidenceResult) || empty($saveEvidenceResult[0]) || (strlen($saveEvidenceResult[0]['Error_Msg']) > 0)) {
				$this->db->trans_rollback();
				return $saveEvidenceResult;
			}
		}
		//Сохраняем ценности и доки

		foreach ($data['ValueStuff'] as $evidence) {
			$saveValueStuffResult = $this->_saveEvidence(array(
				'EvnForensic_id' => $data['Evn_pid'],
				'Evidence_Name' => $evidence['ValueStuff_Name'],
				'EvidenceType_id' => 3,
				'pmUser_id' => $data['pmUser_id'],
			));
			if (empty($saveValueStuffResult) || empty($saveValueStuffResult[0]) || (strlen($saveValueStuffResult[0]['Error_Msg']) > 0)) {
				$this->db->trans_rollback();
				return $saveValueStuffResult;
			}
		}

		$this->db->trans_commit();

		return $insertEvnForensicCorpHistResult;

	}

	/**
	 * Получение списка заявок службы cудебно-медицинской экспертизы трупов с судебно-гистологическим отделением
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicCorpHistList($data)
	{
		if (empty($data['JournalStatus'])) {
			$data['JournalStatus'] = NULL;
		}
		$query = "
			SELECT
			-- select
				EFCH.EvnForensicCorpHist_Num as \"EvnForensic_Num\",
				EFCH.EvnForensicCorpHist_id as \"EvnForensic_id\",
				to_char(E.Evn_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS \"Person_Fio\"
			-- end select
			FROM
			-- from
				v_EvnForensicCorpHist EFCH
				left join v_Evn E on E.Evn_id = EFCH.EvnForensicCorpHist_id
				left join v_EvnDirectionForensic EDF on EDF.EvnDirectionForensic_id = E.Evn_id
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
					LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P on E.PersonEvn_id = P.PersonEvn_id and E.Server_id = P.Server_id
			-- end from
			WHERE
			-- where
				EFCH.EvnClass_id = 151
			";
		//@TODO: Добавим статусы, когда разберёмся
		$result = $this->db->query($query, array());
		if (!is_object($result)) {
			return false;
		}

		return array('data' => $result->result('array'));
	}

	/**
	 * Функция сохранения ;журнала приема трупов и вещественных доказательств в морг
	 * @param type $data
	 * @return boolean
	 */
	public function saveForenCorpHistJourMorgJournal($data)
	{

		if (empty($data['Person_zid'])) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Доставившее лицо'));
		}
		if (empty($data['PersonEvn_id'])) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор случая'));
		}
		if (empty($data['Server_id']) && $data['Server_id'] != 0) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор сервера'));
		}


		if (empty($data['MedService_id'])) {
			if (empty($data['session']) && empty($data['session']['CurMedService_id'])) {
				return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор службы'));
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		if (empty($data['Lpu_id'])) {
			return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор МО'));
		}
		if (empty($data['EvnForensicCorpHistJourMorg_pid'])) {
			return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор родительской заявки'));
		}


		$query = "
			SELECT
				evnforensiccorphistjourmorg_id as \"EvnForensicCorpHistJourMorg_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicCorpHistJourMorg_ins (
				EvnForensicCorpHistJourMorg_id :=  :EvnForensicCorpHistJourMorg_id,
				EvnForensicCorpHistJourMorg_pid :=  :EvnForensicCorpHistJourMorg_pid,
				Person_zid :=  :Person_zid,
				PersonEvn_id :=  :PersonEvn_id,
				Server_id :=  :Server_id,
				MedService_id :=  :MedService_id,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
			";


		$result = $this->db->query($query, array(
			'EvnForensicCorpHistJourMorg_id' => null,
			'EvnForensicCorpHistJourMorg_pid' => $data['EvnForensicCorpHistJourMorg_pid'],

			'Person_zid' => $data['Person_zid'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id']
		));

		if (!is_object($result)) {
			$this->db->trans_rollback();
			return false;
		}
		return $result->result('array');

	}

	/**
	 * Сохранение заявки в службе комиссионных и комплексных экспертиз
	 * @param $data
	 * @return bool|type
	 */
	protected function _saveForenComplexRequest($data)
	{
		if (empty($data['MedService_id'])) {
			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
				} else {
					$data['MedService_id'] = $data['session']['CurMedService_id'];
				}
			} else {
				//Если передан MedService_fid - значит создается направление из другой службы и из сессии получить MedService_id службы,
				//которой направление назначается, не получится
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			}
		}

		$rules = array(
			array('field' => 'Evn_pid', 'label' => 'Идентификатор родительского события', 'type' => 'id'),
			array('field' => 'EvnForensic_ResDate', 'label' => 'Дата постановления', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evn_rid', 'label' => 'Идентификатор получателя документа', 'type' => 'id'),
			array('field' => 'Person_cid', 'label' => 'Направившее лицо', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_pid', 'label' => 'Идентификатор службы - родителя', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => '', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select
			    evnforensiccomplex_id as \"EvnForensicComplex_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensicComplex_ins (
				EvnForensicComplex_id :=  :EvnForensicComplex_id,
				EvnForensicComplex_Num :=  COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				EvnForensicComplex_ResDate :=  :EvnForensic_ResDate,
				MedService_id :=  :MedService_id,
				Person_cid :=  :Person_cid,
				MedService_pid :=  :MedService_pid,
				EvnForensicComplex_pid := :Evn_pid,
				EvnForensicComplex_rid := :Evn_rid,
				pmUser_id :=  :pmUser_id,
				Lpu_id :=  :Lpu_id
			);
		";
		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение журнала регистрации судебно-медицинских исследований и медицинских судебных экспертиз
	 * @param $data
	 * @return array|bool|type
	 */
	public function _saveEvnForensicComplexResearchJournal($data)
	{
		if (empty($data['MedService_id'])) {
			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
				} else {
					$data['MedService_id'] = $data['session']['CurMedService_id'];
				}
			} else {
				//Если передан MedService_fid - значит создается направление из другой службы и из сессии получить MedService_id службы,
				//которой направление назначается, не получится
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			}
		}

		$rules = array(
			array('field' => 'EvnForensicComplex_id', 'label' => 'Идентификатор заявки', 'rules' => 'request', 'type' => 'id'),
			array('field' => 'EvnForensicComplexResearch_id', 'label' => 'Идентификатор журнала регистрации судебно-медицинских исследований и медицинских судебных экспертиз', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicComplexResearch_Base', 'label' => 'Основание для проведения экспертизы', 'rules' => '', 'type' => 'string'),
			array('field' => 'Evidence', 'label' => 'Перечень документов', 'rules' => '', 'type' => 'array'),
			array('field' => 'EvnForensicComplexResearchDopMat', 'label' => 'Дополнительно затребованные материалы', 'rules' => '', 'type' => 'array'),
			array('field' => 'EvnForensicComplexResearchComission', 'label' => 'Состав комиссии', 'rules' => '', 'type' => 'array'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedService_pid', 'label' => 'Идентификатор службы - родителя', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id', 'rules' => '', 'label' => 'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$proc = 'p_EvnForensicComplexResearch_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicComplexResearch_id'])) {
			$proc = 'p_EvnForensicComplexResearch_upd';
			$currentDataResult = $this->getEvnForensicComplexResearchJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams[$key] = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}
			//Если переданы новые новые документы сначала удаляем их из последней сохраненной записи журнала
			if (!empty($queryParams['Evidence'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id' => $queryParams['EvnForensicComplexResearch_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			} else {
				$queryParams['Evidence'] = array();
			}
		}

		$query = "
			select
			       	evnforensiccomplexresearch_id as \"EvnForensicComplexResearch_id\", 
            		error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicComplexResearch_id = :EvnForensicComplexResearch_id,
				EvnForensicComplexResearch_pid = :EvnForensicComplex_id,
				EvnForensicComplexResearch_Num = COALESCE((SELECT MAX(COALESCE(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF),1),
				EvnForensicComplexResearch_Base = :EvnForensicComplexResearch_Base,
				MedService_id = :MedService_id,
				MedService_pid = :MedService_pid,
				Lpu_id = :Lpu_id,
				pmUser_id = :pmUser_id
			);
		";

		$result = $this->queryResult($query, $queryParams);

		if (!$this->isSuccessful($result)) {
			return $result;
		}

		//После сохранения записи в журнале сохраняем перечень документов
		foreach ($queryParams['Evidence'] as $evidence) {
			$saveEvidenceResult = $this->_saveEvidence(array(
				'EvnForensic_id' => $result[0]['EvnForensicComplexResearch_id'],
				'Evidence_Name' => $evidence['Evidence_Name'],
				'EvidenceType_id' => 5,
				'pmUser_id' => $queryParams['pmUser_id'],
			));
			if (!$this->isSuccessful($saveEvidenceResult)) {
				return $saveEvidenceResult;
			}
		}

		return $result;
	}

	/**
	 * Функция получения заявки службы медико-криминалистической экспертизы
	 * @return boolean
	 */
	public function getForenComplexRequest($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				-- Общий блок

				EFC.EvnForensicComplex_Num as \"EvnForensic_Num\",
				EFC.EvnForensicComplex_id as \"EvnForensic_id\",
				COALESCE(EDF.EvnDirectionForensic_id,0) as \"EvnDirectionForensic_id\",
				to_char(EFC.EvnForensicComplex_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensic_insDT\",
				EFC.Person_cid as \"Person_cid\",
				rtrim(cPS.Person_SurName||' '||cPS.Person_FirName||' '||COALESCE(cPS.Person_SecName,'')) as \"Person_cFIO\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",

				-- Журнал регистрации судебно-медицинских исследований
				COALESCE(EFCR.EvnForensicComplexResearch_id,0) as \"EvnForensicComplexResearch_id\",
				EFCR.EvnForensicComplexResearch_Base as \"EvnForensicComplexResearch_Base\",

				-- Заключение
				COALESCE(AVF.ActVersionForensic_id,0) as \"ActVersionForensic_id\",
				AVF.ActVersionForensic_Num as \"ActVersionForensic_Num\",
				AVF.ActVersionForensic_Text as \"ActVersionForensic_Text\",
				to_char(AVF.ActVersionForensic_FactBegDT, 'yyyy-mm-ddThh24:mi:ss') as \"ActVersionForensic_FactBegDT\",
				to_char(AVF.ActVersionForensic_FactEndDT, 'yyyy-mm-ddThh24:mi:ss') as \"ActVersionForensic_FactEndDT\",

				-- Комментарий, если есть
				COALESCE(ESH.EvnStatusHistory_Cause,'') as \"EvnStatusHistory_Cause\"

			FROM
				v_EvnForensicComplex EFC
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFC.EvnForensicComplex_id
				left join v_PersonState cPS on cPS.Person_id = EFC.Person_cid
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin as Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EDF.MedPersonal_id
					LIMIT 1
				) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicComplexResearch EFCR on EFCR.EvnForensicComplexResearch_pid = EFC.EvnForensicComplex_id
				LEFT JOIN LATERAL (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_FactBegDT,
						AVF.ActVersionForensic_FactEndDT
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFC.EvnForensicComplex_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on TRUE
				LEFT JOIN LATERAL (
					SELECT
						ESH.EvnStatusHistory_Cause
					FROM
						v_EvnStatusHistory ESH
					WHERE
						ESH.Evn_id = EFC.EvnForensicComplex_id
					ORDER BY
						ESH.EvnStatusHistory_insDT DESC
					LIMIT 1
				) as ESH on TRUE
			WHERE
				EFC.EvnForensicComplex_id = :EvnForensic_id
			";
		//echo getDebugSQL($query,$queryParams);exit;
		$result = $this->queryResult($query, $queryParams);
		if (!$this->isSuccessful($result)) {
			return $result;
		}


		if (!empty($result[0])) {
			if (!empty($result[0]['EvnForensicComplexResearch_id'])) {
				//
				// Получаем список документов
				//
				$evidence_result = $this->getEvidenceList(array(
					'EvnForensic_id' => $result[0]['EvnForensicComplexResearch_id']
				));

				if (!$this->isSuccessful($evidence_result)) {
					return $evidence_result;
				} else {
					$result[0]['EvnForensicComplexResearch_Evidence'] = $evidence_result;
				}
			}
		}
		if (!empty($result[0])) {
			$result[0]['success'] = true;
		}
		return $result;
	}

	/**
	 * Функция получения записи в журнале регистрации судебно-медицинских исследований и медицинских судебных экспертиз
	 * @param $data
	 * @return array
	 */
	protected function getEvnForensicComplexResearchJournal($data)
	{
		$rules = array(
			array('field' => 'EvnForensicComplexResearch_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				EFSL.EvnForensicComplexResearch_pid as \"EvnForensicGenetic_id\",
				EFSL.EvnForensicComplexResearch_id as \"EvnForensicComplexResearch_id\",
				EFSL.EvnForensicComplexResearch_Basis as \"EvnForensicComplexResearch_Basis\",
				EFSL.Lpu_id as \"Lpu_id\"
			FROM
				v_EvnForensicComplexResearche EFCR
			WHERE
				EFCR.EvnForensicComplexResearch_id = :EvnForensicComplexResearch_id
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение зявки внутри службы комиссионных и комплексных экспертиз
	 * @param $data
	 * @return bool|type
	 */
	public function saveForenComplexOwnRequest($data)
	{
		/* Сохранение заявки */
		$this->db->trans_begin();

		$insertEvnForensicComplexResult = $this->_saveForenComplexRequest($data);
		if (!$this->isSuccessful($insertEvnForensicComplexResult)) {
			$this->db->trans_rollback();
			return $insertEvnForensicComplexResult;
		}

		$data['EvnForensicComplex_id'] = $insertEvnForensicComplexResult[0]['EvnForensicComplex_id'];


		/************************************************************************************************ */
		/* Проверка журнала регистрации судебно-медицинских исследований и медицинских судебных экспертиз */
		/************************************************************************************************ */
		$journalSingleFields = array(
			'EvnForensicComplexResearch_id', 'EvnForensicComplexResearch_Base'
		);
		$journalArrayFields = array(/*array('key'=>'Person','val'=>'Person_id'),*/
			array('key' => 'Evidence', 'val' => 'Evidence_Name'));

		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);

		if ($journalFilledStatus == 'empty') {
			//Ничего
		} elseif ($journalFilledStatus == 'unfinished') {

			$this->db->trans_rollback();
			return $this->createError('', 'Не все поля журнала регистрации судебно-медицинских<br/> исследований и медицинских судебных экспертиз. <br/> Пожалуйста, заполните все поля');

		} elseif ($journalFilledStatus == 'filled') {

			$saveJournalResult = $this->_saveEvnForensicComplexResearchJournal($data);

			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			}
		}

		$this->db->trans_commit();
		return $insertEvnForensicComplexResult;
	}

	/**
	 * Получение списка заявок службы судебно-химического отделения
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicChemList($data)
	{
		if (empty($data['JournalStatus'])) {
			$data['JournalStatus'] = NULL;
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicChem_Num as \"EvnForensic_Num\",
				EFC.EvnForensicChem_id as \"EvnForensic_id\",
				to_char(E.Evn_insDT, 'yyyy-mm-ddthh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(EFCB.EvnForensicChemBiomat_id,0) as \"EvnForensicChemBiomat_id\",
				COALESCE(EFCD.EvnForensicChemDirection_id,0) as \"EvnForensicChemDirection_id\",
				COALESCE(EFCKD.EvnForensicChemKidneyDestruct_id,0) as \"EvnForensicChemKidneyDestruct_id\",
				CASE WHEN COALESCE(EFCB.EvnForensicChemBiomat_id,0) != 0
					 THEN COALESCE(EFCB_P.Person_SurName, '') || CAST(COALESCE(' ' || EFCB_P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || EFCB_P.Person_SecName, '') as varchar(2))
					 ELSE CASE WHEN COALESCE(EFCD.EvnForensicChemDirection_id,0) != 0
						THEN COALESCE(EFCD_P.Person_SurName, '') || CAST(COALESCE(' ' || EFCD_P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || EFCD_P.Person_SecName, '') as varchar(2))
						ELSE COALESCE(EFCKD_P.Person_SurName, '') || CAST(COALESCE(' ' || EFCKD_P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || EFCKD_P.Person_SecName, '') as varchar(2))
					 END
				END as \"Person_Fio\"	
			-- end select
			FROM
			-- from
				v_EvnForensicChem EFC
				left join v_Evn E on E.Evn_id = EFC.EvnForensicChem_id
				left join v_EvnDirectionForensic EDF on EDF.EvnDirectionForensic_id = E.Evn_id
				LEFT JOIN LATERAL (
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT   on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicChemBiomat EFCB   on EFCB.EvnForensicChemBiomat_pid = EFC.EvnForensicChem_id
				left join v_Person_all EFCB_P   on EFCB.PersonEvn_id = EFCB_P.PersonEvn_id and EFCB.Server_id = EFCB_P.Server_id
				left join v_EvnForensicChemDirection EFCD   on EFCD.EvnForensicChemDirection_pid = EFC.EvnForensicChem_id 
				left join v_Person_all EFCD_P   on EFCD.PersonEvn_id = EFCD_P.PersonEvn_id and EFCD.Server_id = EFCD_P.Server_id
				left join v_EvnForensicChemKidneyDestruct EFCKD    on EFCKD.EvnForensicChemKidneyDestruct_pid = EFC.EvnForensicChem_id
				left join v_Person_all EFCKD_P   on EFCKD.PersonEvn_id = EFCKD_P.PersonEvn_id and EFCKD.Server_id = EFCKD_P.Server_id
			-- end from
			WHERE
			-- where
				EFC.EvnClass_id = 147
			";

		//@TODO: Добавим статусы, когда разберёмся

		$result = $this->db->query($query, array());

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение списка заявок службы Медико-криминалистическое отделение
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicCrimeList($data)
	{

		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'string'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'COALESCE(EFC.EvnClass_id,0)=140',
			'EFC.MedService_id=:MedService_id',
		);
		if (isset($queryParams['EvnStatus_SysNick']) && !empty($queryParams['EvnStatus_SysNick']) && $queryParams['EvnStatus_SysNick'] != 'All') {
			if ($queryParams['EvnStatus_SysNick'] == 'New') {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicCrime_Num as \"EvnForensic_Num\",
				EFC.EvnForensicCrime_id as \"EvnForensic_id\",
				to_char(EFC.EvnForensicCrime_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				'Группа лиц' as \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			-- end select
			FROM
			-- from
				v_EvnForensicCrime EFC
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFC.EvnForensicCrime_id
				LEFT JOIN LATERAL (
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
				    LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON ES.EvnStatus_id = EFC.EvnStatus_id 
			-- end from
			WHERE 
			-- where
				" . implode(" AND ", $where) . "
			-- end where	
			ORDER BY
			-- order by
				EFC.EvnForensicCrime_Num DESC
			-- end order by
			";

		$result = array();

		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
	 * регистрации вещественных доказательств и документов к ним
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicCrimeEvidList($data)
	{

		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'string'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'COALESCE(EFCE.EvnClass_id,0)=143',
			'EFC.MedService_id=:MedService_id',
		);
		if (isset($queryParams['EvnStatus_SysNick']) && !empty($queryParams['EvnStatus_SysNick']) && $queryParams['EvnStatus_SysNick'] != 'All') {
			if ($queryParams['EvnStatus_SysNick'] == 'New') {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicCrime_Num as \"EvnForensic_Num\",
				EFC.EvnForensicCrime_id as \"EvnForensic_id\",
				to_char(EFCE.EvnForensicCrimeEvid_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				
				CASE WHEN (PEC.PersonEvidCount>1) THEN 'Группа лиц' ELSE
					CASE WHEN (PEC.PersonEvidCount=1) THEN PersonEvid.Person_Fio 
					ELSE 'Лицо отсутствует' END
				END as \"Person_Fio\",

				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			-- end select
			FROM
			-- from
				v_EvnForensicCrimeEvid EFCE
				left join v_EvnForensicCrime EF on EFC.EvnForensicCrime_id = EFCE.EvnForensicCrimeEvid_pid
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFCE.EvnForensicCrimeEvid_id
				LEFT JOIN LATERAL (
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
				    LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON ES.EvnStatus_id=EFCE.EvnStatus_id 
				LEFT JOIN LATERAL  (
					SELECT
						COUNT(EFGEL.Person_id) as PersonEvidCount
					FROM
						v_EvnForensicCrimeEvidLink EFGEL
					WHERE
						EFGEL.EvnForensicCrimeEvid_id = EFCE.EvnForensicCrimeEvid_id
				) as PEC on TRUE
				LEFT JOIN LATERAL  (
					SELECT
						
						EFGEL.Person_id,
						COALESCE(P.Person_SurName, '') || CAST(COALESCE(' ' || P.Person_FirName, '') as varchar(2)) || CAST(COALESCE(' ' || P.Person_SecName, '')as varchar(2)) AS Person_Fio
					FROM
						v_EvnForensicCrimeEvidLink EFGEL
						left join v_Person_all P  on EFGEL.Person_id = P.Person_id
					WHERE
						EFGEL.EvnForensicCrimeEvid_id = EFCE.EvnForensicCrimeEvid_id
					LIMIT 1
				) as PersonEvid
			-- end from
			WHERE 
			-- where
				" . implode(" AND ", $where) . "
			-- end where	
			ORDER BY
			-- order by
				EFC.EvnForensicCrime_Num DESC
			-- end order by
			";

		$result = array();

		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
	 * регистрации фоторабот
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicCrimePhotList($data)
	{

		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'string'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'COALESCE(EFCP.EvnClass_id,0)=142',
			'EFC.MedService_id=:MedService_id',
		);
		if (isset($queryParams['EvnStatus_SysNick']) && !empty($queryParams['EvnStatus_SysNick']) && $queryParams['EvnStatus_SysNick'] != 'All') {
			if ($queryParams['EvnStatus_SysNick'] == 'New') {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicCrime_Num as \"EvnForensic_Num\",
				EFC.EvnForensicCrime_id as \"EvnForensic_id\",
				to_char(EFCP.EvnForensicCrimePhot_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_Fio,'') as \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			-- end select
			FROM
			-- from
				v_EvnForensicCrimePhot EFCP
				LEFT JOIN v_EvnForensicCrime EFC on EFC.EvnForensicCrime_id = EFCP.EvnForensicCrimePhot_pid
				LEFT JOIN v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFCP.EvnForensicCrimePhot_id
				LEFT JOIN LATERAL (
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
				    LIMIT 1
                ) as MP on TRUE
				LEFT JOIN v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFCP.EvnStatus_id )
				LEFT JOIN v_Person_all P ON P.PersonEvn_id = EFCP.PersonEvn_id AND P.Server_id = EFCP.Server_id
				
			-- end from
			WHERE 
			-- where
				" . implode(" AND ", $where) . "
			-- end where	
			ORDER BY
			-- order by
				EFC.EvnForensicCrime_Num DESC
			-- end order by
			";

		$result = array();

		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Список всех заявок медико-криминалистического отделения в которых присутствует запись в журнале
	 * регистрации разрушения почки на планктон
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicCrimeDesPlanList($data)
	{

		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'string'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'COALESCE(EFCDP.EvnClass_id,0)=145',
			'EFC.MedService_id=:MedService_id',
		);
		if (isset($queryParams['EvnStatus_SysNick']) && !empty($queryParams['EvnStatus_SysNick']) && $queryParams['EvnStatus_SysNick'] != 'All') {
			if ($queryParams['EvnStatus_SysNick'] == 'New') {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicCrime_Num as \"EvnForensic_Num\",
				EFC.EvnForensicCrime_id as \"EvnForensic_id\",
				to_char(EFCDP.EvnForensicCrimeDesPlan_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				COALESCE(P.Person_Fio,'Не идентифицирован') as \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\"
			-- end select
			FROM
			-- from
				v_EvnForensicCrimeDesPlan EFCDP
				LEFT JOIN v_EvnForensicCrime EFC on EFC.EvnForensicCrime_id = EFCDP.EvnForensicCrimeDesPlan_pid
				LEFT JOIN v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFCDP.EvnForensicCrimeDesPlan_id
				LEFT JOIN LATERAL (
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP on TRUE
				LEFT JOIN v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON ES.EvnStatus_id=EFCDP.EvnStatus_id 
				LEFT JOIN v_Person_all P ON P.PersonEvn_id = EFCDP.PersonEvn_id AND P.Server_id = EFCDP.Server_id
				
			-- end from
			WHERE 
			-- where
				" . implode(" AND ", $where) . "
			-- end where	
			ORDER BY
			-- order by
				EFC.EvnForensicCrime_Num DESC
			-- end order by
			";

		$result = array();

		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Получение списка заявок службы Судебно-гистологическое отделение
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicHistList($data)
	{
		if (empty($data['JournalStatus'])) {
			$data['JournalStatus'] = NULL;
		}

		$query = "
			SELECT
			-- select
				EFH.EvnForensicHist_Num as \"EvnForensic_Num\",
				EFH.EvnForensicHist_id as \"EvnForensic_id\",
				to_char(E.Evn_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\"
			-- end select
			FROM
			-- from
				v_EvnForensicHist EFH
				left join v_Evn E on E.Evn_id = EFH.EvnForensicHist_id
				left join v_EvnDirectionForensic EDF on EDF.EvnDirectionForensic_id = E.Evn_id
				LEFT JOIN LATERAL (
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
				    LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
			";

		//@TODO: Добавим статусы, когда разберёмся

		$result = $this->db->query($query, array());

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение списка заявок службы комиссионных и комплексных экспертиз
	 *
	 * @return array
	 */
	public function getEvnForensicComplexList($data)
	{

		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		if (empty($data['MedPersonal_id']) && !empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'string'),
			array('field' => 'ARMType', 'label' => 'Тип АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'COALESCE(EFC.EvnClass_id,0)=158',
			'EFC.MedService_id=:MedService_id',
		);
		if (isset($queryParams['EvnStatus_SysNick']) && !empty($queryParams['EvnStatus_SysNick']) && $queryParams['EvnStatus_SysNick'] != 'All') {
			if ($queryParams['EvnStatus_SysNick'] == 'New') {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}

		if ($queryParams['ARMType'] == 'expert') {
			if (empty($queryParams['MedPersonal_id'])) {
				return $this->createError('', 'Не задан обязательны параметр: Идентификатор эксперта');
			}
			$where[] = "EDF.MedPersonal_id=:MedPersonal_id";
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicComplex_Num as \"EvnForensic_Num\",
				EFC.EvnForensicComplex_id as \"EvnForensic_id\",
				to_char(EFC.EvnForensicComplex_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				'Группа лиц' as \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\",
				EFC.EvnClass_id as \"EvnClass_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicComplex EFC
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFC.EvnForensicComplex_id
				LEFT JOIN LATERAL (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFC.EvnForensicComplex_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on TRUE
				LEFT JOIN LATERAL (
                	SELECT 
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                    LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT  on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON ES.EvnStatus_id=EFC.EvnStatus_id 
			-- end from
			WHERE
			-- where
				" . implode(' AND ', $where) . "
			-- end where
			ORDER BY
			-- order by
				EFC.EvnForensicComplex_Num DESC
			-- end order by
		";
		//echo getDebugSQL($query,$queryParams);exit;
		$result = array();

		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Список всех заявок отделения комиссионных и комплексных экспертиз, в которых присутствует запись в журнале
	 * регистрации судебно-медицинских исследований и медицинских судебных экспертиз
	 * @params null data
	 * @return array
	 */
	public function getEvnForensicComplexResearchList($data)
	{

		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		if (empty($data['MedPersonal_id']) && !empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'EvnStatus_SysNick', 'label' => 'Статус заявки', 'rules' => '', 'type' => 'string'),
			array('field' => 'ARMType', 'label' => 'Тип АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'COALESCE(EFCR.EvnClass_id,0)=159',
			'EFC.MedService_id=:MedService_id',
		);
		if (isset($queryParams['EvnStatus_SysNick']) && !empty($queryParams['EvnStatus_SysNick']) && $queryParams['EvnStatus_SysNick'] != 'All') {
			if ($queryParams['EvnStatus_SysNick'] == 'New') {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}

		if ($queryParams['ARMType'] == 'expert') {
			if (empty($queryParams['MedPersonal_id'])) {
				return $this->createError('', 'Не задан обязательны параметр: Идентификатор эксперта');
			}
			$where[] = "EDF.MedPersonal_id=:MedPersonal_id";
		}

		$query = "
			SELECT
			-- select
				EFC.EvnForensicComplex_Num as \"EvnForensic_Num\",
				EFC.EvnForensicComplex_id as \"EvnForensic_id\",
				EFCR.EvnForensicComplexResearch_Num as \"EvnForensicComplexResearch_Num\",
				EFCR.EvnForensicComplexResearch_id as \"EvnForensicComplexResearch_id\",
				to_char(EFC.EvnForensicComplex_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"Evn_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
				'Группа лиц' as \"Person_Fio\",
				ES.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				COALESCE(AVF.ActVersionForensic_id,0) AS \"ActVersionForensic_id\",
				EFCR.EvnClass_id as \"EvnClass_id\"
			-- end select
			FROM
			-- from
				v_EvnForensicComplexResearch EFCR
				left join v_EvnForensicComplex EFC on EFC.EvnForensicComplex_id = EFCR.EvnForensicComplexResearch_pid
				left join v_EvnDirectionForensic EDF  on EDF.EvnForensic_id = EFC.EvnForensicComplex_id
				LEFT JOIN LATERAL (
					SELECT
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF
					WHERE
						AVF.EvnForensic_id = EFC.EvnForensicComplex_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
					LIMIT 1
				) as AVF on TRUE
				LEFT JOIN LATERAL(
                	SELECT
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
					LIMIT 1
                ) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFC.EvnStatus_id )
			-- end from
			WHERE
			-- where
				" . implode(' AND ', $where) . "
			-- end where
			ORDER BY
			-- order by
				EFC.EvnForensicComplex_Num DESC
			-- end order by
			";

		$result = array();
		//echo getDebugSQL($query,$queryParams);exit;
		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Возвращает список типов экспертизы
	 *
	 * @return array
	 */
	public function loadEvnForensicTypeList()
	{
		$sql = "
			SELECT
				EvnForensicType_id as \"EvnForensicType_id\",
				EvnForensicType_Name as \"EvnForensicType_Name\",
				EvnForensicType_Code as \"EvnForensicType_Code\",
				pmUser_insID as \"pmUser_insID\",
				pmUser_updID as \"pmUser_updID\",
				EvnForensicType_insDT as \"EvnForensicType_insDT\",
				EvnForensicType_updDT as \"EvnForensicType_updDT\"
			FROM
				v_EvnForensicType
		";

		$query = $this->db->query($sql);
		if (is_object($query)) {
			return $query->result_array();
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для шаблона печати направлений
	 *
	 * @param array $data
	 * @return array
	 */
	public function printEvnDirectionForensic($data)
	{
		$sql = "
			SELECT
				edf.EvnDirectionForensic_id, as \"EvnDirectionForensic_id\"
				edf.EvnDirectionForensic_Num as \"EvnDirectionForensic_Num\",
				-- ФИО эксперта:
				ps.Person_SurNameR || ' ' || ps.Person_FirNameR || COALESCE( ' ' || ps.Person_SecNameR, '' ) as \"Expert_Fio\",
				to_char(edf.EvnDirectionForensic_begDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnDirectionForensic_begDate\",
                to_char(edf.EvnDirectionForensic_endDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnDirectionForensic_endDate\",
				eft.EvnForensicType_Name as \"EvnForensicType_Name\"
			FROM
				v_EvnForensic as ef
                LEFT JOIN v_EvnDirectionForensic as edf  ON edf.EvnForensic_id=ef.EvnForensic_id
				LEFT JOIN v_MedPersonal as mp ON( mp.MedPersonal_id=edf.MedPersonal_id )
				LEFT JOIN v_PersonState as ps ON( ps.Person_id=mp.Person_id )
				LEFT JOIN v_EvnForensicType as eft ON( edf.EvnForensicType_id=eft.EvnForensicType_id )
			WHERE
				ef.EvnForensic_id =:EvnForensic_id
		";

		$query = $this->db->query($sql, array(
			'EvnForensic_id' => $data['EvnForensic_id']
		));
		if (is_object($query)) {
			return $query->row_array();
		}

		return array();
	}

	/**
	 * Возвращает указанную заявку
	 *
	 * @param $EvnForensic_id ID заявки
	 * @return array or false
	 */
	public function getEvnForensic($EvnForensic_id)
	{
		$sql = "
			SELECT
				EF.*
			FROM
				v_EvnForensic EF
			WHERE
				EvnForensic_id=:EvnForensic_id
			LIMIT 1
		";
		$query = $this->db->query($sql, array('EvnForensic_id' => $EvnForensic_id));
		if (is_object($query)) {
			return $query->row_array();
		}

		return false;
	}


	/**
	 * Изменение статуса заявки
	 *
	 * @param array $data
	 * @return boolean Результат изменения статуса
	 */
	public function changeEvnForensicStatus($data, $EvnStatus_SysNick)
	{
		$this->load->model('Evn_model');
		$result = $this->Evn_model->updateEvnStatus(array(
			'EvnClass_SysNick' => 'EvnForensic',
			'Evn_id' => $data['EvnForensic_id'],
			'EvnStatus_SysNick' => $EvnStatus_SysNick,
			'EvnStatusHistory_Cause' => isset($data['EvnStatusHistory_Cause']) && !empty($data['EvnStatusHistory_Cause']) ? $data['EvnStatusHistory_Cause'] : null,
			'pmUser_id' => $data['pmUser_id'],
		));

		if ($result) {
			return array('Error_Msg' => '');
		} else {
			return array('Error_Msg' => 'Во время изменения статуса заявки произошла непредвиденная ошибка.');
		}
	}

	/**
	 * Загрузка формы
	 * @param $data
	 * @return bool
	 */
	public function loadForenBioOwnRequestForm($data)
	{
		return false;
	}

	/**
	 * Сохранение экспертизы потерпевших, обвиняемых и других лиц
	 * @param $data
	 * @return bool
	 */
	public function saveForenPersExpertiseProtocol($data)
	{
		$saveActVersionForensicResult = $this->_saveActVersionForensic($data);
		return $saveActVersionForensicResult;
	}

	/**
	 * Сохранение акта экспертизы
	 * @param $data
	 * @return bool|array
	 */
	protected function _saveActVersionForensic($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'ActVersionForensic_id', 'label' => 'Идентификатор версии акта заключения', 'rules' => '', 'type' => 'id'),
			array('field' => 'ActVersionForensic_Num', 'label' => 'Номер акта заключения', 'rules' => '', 'type' => 'int'),
			//array('field' => 'ActVersionForensic_Text','label' => 'Акт заключение эксперта','rules' => 'required','type' => 'string'),
			array('field' => 'ActVersionForensic_FactBegDT', 'label' => 'Акт заключение эксперта', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'ActVersionForensic_FactEndDT', 'label' => 'Акт заключение эксперта', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'pmUser_id', 'label' => 'Идентификатор пользователя', 'rules' => 'required', 'type' => 'id')
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;


		$selectString = "
            evnforensic_id as \"EvnForensic_id\", 
            error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
        ";


		$query = "
			select {$selectString}
			from p_ActVersionForensic_ins (
				ActVersionForensic_nid := (
								select ActVersionForensic_id
								from v_ActVersionForensic
								where EvnForensic_id = :EvnForensic_id
								order by ActVersionForensic_insDT desc
								limit 1
							),
				ActVersionForensic_Num := COALESCE((
								select ActVersionForensic_Num
								from v_ActVersionForensic
								where EvnForensic_id = :EvnForensic_id
								order by ActVersionForensic_insDT desc
								limit 1
							),COALESCE((SELECT MAX(COALESCE(AVF.ActVersionForensic_Num,0)+1) as ActVersionForensic_Num FROM v_ActVersionForensic AVF),1)),
				EvnForensic_id = :EvnForensic_id,
				--@ActVersionForensic_Text = ActVersionForensic_Text,
				ActVersionForensic_FactBegDT := :ActVersionForensic_FactBegDT,
				ActVersionForensic_FactEndDT := :ActVersionForensic_FactEndDT,
				pmUser_id := :pmUser_id
		);
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение номера акта заключения
	 */
	public function getActVersionForensicNum($data)
	{
		$queryParams = array('EvnForensic_id' => $data['EvnForensic_id']);

		$query = "
			select
				COALESCE(max(AVF.ActVersionForensic_Num),0)+1 as \"ActVersionForensic_Num\"
			from v_ActVersionForensic AVF
			LIMIT 1
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция получения заявки службы медико-криминалистической экспертизы
	 * @return boolean
	 */
	public function getEvnForenCrimeRequest($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				-- Общий блок

				EFC.EvnForensicCrime_Num as \"EvnForensic_Num\",
				EFC.EvnForensicCrime_id as \"EvnForensic_id\",
				COALESCE(EDF.EvnDirectionForensic_id,0) as \"EvnDirectionForensic_id\",
				to_char(EFC.EvnForensicCrime_insDT, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensic_insDT\",
				COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
				COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",

				-- Журнал регистрации вещественных доказательств
				COALESCE(EFCE.EvnForensicCrimeEvid_id,0) as \"EvnForensicCrimeEvid_id\",
				EFCE.EvnForensicCrimeEvid_AccDocNum as \"EvnForensicCrimeEvid_AccDocNum\",
				to_char(EFCE.EvnForensicCrimeEvid_AccDocDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeEvid_AccDocDate\",
				EFCE.EvnForensicCrimeEvid_AccDocNumSheets as \"EvnForensicCrimeEvid_AccDocNumSheets\",
				O.Org_Name as \"Org_Name\",
				EFCE.EvnForensicCrimeEvid_Facts as \"EvnForensicCrimeEvid_Facts\",
				EFCE.EvnForensicCrimeEvid_Goal as \"EvnForensicCrimeEvid_Goal\",

				-- Журнал регистрации биообразцов
				COALESCE(EFCSL.EvnForensicCrimeSampleLive_id,0) as EvnForensicCrimeSampleLive_id,
				to_char(EFCSL.EvnForensicCrimeSampleLive_TakeDate, 'dd.mm.yyyy') || ' ' || to_char(EFCSL.EvnForensicCrimeSampleLive_TakeDate, 'hh24:mi') as \"EvnForensicCrimeSampleLive_TakeDate\",
				EFCSL_P.Person_Fio as \"EvnForensicCrimeSampleLive_Person_FIO\",
				EFCSL.EvnForensicCrimeSampleLive_Basis as \"EvnForensicCrimeSampleLive_Basis\",
				to_char(EFCSL.EvnForensicCrimeSampleLive_StudyDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeSampleLive_StudyDate\",--дата исследования образца
				--EFCSL.EvnForensicCrimeSampleLive_Result,--Результат
				EFCSL_MP.Person_Fin as \"EvnForensicCrimeSampleLive_MedPersonal_Fin\",--фИО работника изъявшего
				COALESCE(EFCSL.EvnForensicCrimeSampleLive_IsConsent,0) as \"EvnForensicCrimeSampleLive_IsConsent\",--согласие
				--EFCSL.Person_sid,--фио получившего результат
				to_char(EFCSL.EvnForensicCrimeSampleLive_ResultDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeSampleLive_ResultDate\",--дата выдачи результата
				COALESCE(EFCSL.EvnForensicCrimeSampleLive_IsIsosTestEA,0) as \"EvnForensicCrimeSampleLive_IsIsosTestEA\",--Тест-эритроцит А
				COALESCE(EFCSL.EvnForensicCrimeSampleLive_IsIsosTestEB,0) as \"EvnForensicCrimeSampleLive_IsIsosTestEB\",--Тест-эритроцит B
				COALESCE(EFCSL.EvnForensicCrimeSampleLive_IsIsosCyclAntiA,0) as \"EvnForensicCrimeSampleLive_IsIsosCyclAntiA\",--Циклон Анти-А
				COALESCE(EFCSL.EvnForensicCrimeSampleLive_IsIsosCyclAntiB,0) as \"EvnForensicCrimeSampleLive_IsIsosCyclAntiB\",--Циклон Анти-B

				-- Журнал регистрации биообразцов для мол.ген. иссл
				COALESCE(EFCGL.EvnForensicCrimeGenLive_id,0) as \"EvnForensicCrimeGenLive_id\",
				to_char(EFCGL.EvnForensicCrimeGenLive_TakeDate, 'dd.mm.yyyy') as \"EvnForensicCrimeGenLive_TakeDate\",
				EFCGL.EvnForensicCrimeGenLive_Facts as \"EvnForensicCrimeGenLive_Facts\",
				EFCGL_MP.Person_Fin as \"EvnForensicCrimeGenLive_MedPersonal_Fin\",

				--Журнал регистрации трупной крови в лаборатории
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_id,0) as \"EvnForensicCrimeCadBlood_id\",
				--EFCCB.EvnForensicCrimeCadBlood_isIden,--Признак идентифицированного трупа
				--EFCCB.Person_zid,--фио получившего
				EFCCB_P.Person_Fio as \"EvnForensicCrimeCadBlood_Person_Fin\", --исследуемое лицо
				EFCCB_MP.Person_Fin as \"EvnForensicCrimeCadBlood_MedPersonal_Fin\", --EFCCB.MedPersonal_id,--фио эксперта направившего
				--EFCCB.EvnForensicCrimeCadBlood_OpinNum,--номер заключения
				to_char(EFCCB.EvnForensicCrimeCadBlood_ForDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeCadBlood_ForDate\",--Дата поступления
				to_char(EFCCB.EvnForensicCrimeCadBlood_TakeDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeCadBlood_TakeDate\",--дата взятия
				to_char(EFCCB.EvnForensicCrimeCadBlood_StudyDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeCadBlood_StudyDate\",--дата исследования
				--convert(varchar(20), EFCCB.EvnForensicCrimeCadBlood_ResultDate, 104) as EvnForensicCrimeCadBlood_ResultDate, --Дата когда передали результат
				EFCCB.EvnForensicCrimeCadBlood_Result as \"EvnForensicCrimeCadBlood_Result\",--Результат определения групп по исследованным системам
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosTestEA,0) as \"EvnForensicCrimeCadBlood_IsIsosTestEA\",--Тест-эритроцит А
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosTestEB,0) as \"EvnForensicCrimeCadBlood_IsIsosTestEB\",--Тест-эритроцит B
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosTestIsoB,0) as \"EvnForensicCrimeCadBlood_IsIsosTestIsoB\",--Изосыворотка бетта
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosTestIsoA,0) as \"EvnForensicCrimeCadBlood_IsIsosTestIsoA\",--Изосыворотка альфа
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosAntiA,0) as \"EvnForensicCrimeCadBlood_IsIsosAntiA\",--Имунная сыворотка Анти-А
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosAntiB,0) as \"EvnForensicCrimeCadBlood_IsIsosAntiB\",--Имунная сыворотка Анти-B
				COALESCE(EFCCB.EvnForensicCrimeCadBlood_IsIsosAntiH,0) as \"EvnForensicCrimeCadBlood_IsIsosAntiH\",--Имунная сыворотка Анти-H


				-- Журнал регистрации мазков и тампонов
				COALESCE(EFCSS.EvnForensicCrimeSmeSwab_id,0) as \"EvnForensicCrimeSmeSwab_id\",
				EFCSS_P.Person_Fio as \"EvnForensicCrimeSmeSwab_Person_Fio\",
				EFCSS.EvnForensicCrimeSmeSwab_Basis as \"EvnForensicCrimeSmeSwab_Basis\",
				to_char(EFCSS.EvnForensicCrimeSmeSwab_DelivDate, 'dd.mm.yyyy') || ' ' || to_char(EFCSS.EvnForensicCrimeSmeSwab_DelivDate, 'hh24:mi') as \"EvnForensicCrimeSmeSwab_DelivDate\",
				EFCSS_MP.Person_Fin as \"EvnForensicCrimeSmeSwab_MedPersonal_Fin\", --EFCSS.MedPersonal_id,--фио сотрудника проводившего изъятие
				to_char(EFCSS.EvnForensicCrimeSmeSwab_BegDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeSmeSwab_BegDate\",--дата начала исследования
				to_char(EFCSS.EvnForensicCrimeSmeSwab_EndDate, 'yyyy-mm-ddThh24:mi:ss') as \"EvnForensicCrimeSmeSwab_EndDate\",--дата окончания исследования
				--EFCSS.EvnForensicCrimeSmeSwab_Result,--результат
				EFCSS.EvnForensicCrimeSmeSwab_Comment as \"EvnForensicCrimeSmeSwab_Comment\" --примечание
				--EFCSS.Person_sid,--фио получившего результат
				--EFCSS.EvnForensicCrimeSmeSwab_ResultDate--дата получения результата

			FROM
				v_EvnForensicCrime EFC
				left join v_EvnDirectionForensic EDF on EDF.EvnForensic_id = EFC.EvnForensicCrime_id
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin as Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EDF.MedPersonal_id
					LIMIT 1
				) as MP on TRUE
				left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicCrimeEvid EFCE on EFCE.EvnForensicCrimeEvid_pid = EFC.EvnForensicCrime_id
				left join v_Org O on EFCE.Org_id = O.Org_id
				left join v_EvnForensicCrimeCadBlood EFCCB on EFCCB.EvnForensicCrimeCadBlood_pid = EFC.EvnForensicCrime_id
				left join v_Person_all EFCCB_Pon EFCCB.PersonEvn_id = EFCCB_P.PersonEvn_id AND EFCCB.Server_id = EFCCB_P.Server_id
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EFCCB.MedPersonal_id
				) as EFCCB_MP on true
				left join v_EvnForensicCrimeGenLive  EFCGL on EFCGL.EvnForensicCrimeGenLive_pid = EFC.EvnForensicCrime_id
				LEFT JOIN LATERAL(
					SELECT
						MP.Person_Fin
					FROM
						v_MedPersonal MP 
					WHERE
						MP.MedPersonal_id = EFCGL.MedPersonal_id
				    LIMIT 1
				) as EFCGL_MP on true
				left join v_EvnForensicCrimeSampleLive  EFCSL on EFCSL.EvnForensicCrimeSampleLive_pid = EFC.EvnForensicCrime_id
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EFCSL.MedPersonal_id
				    LIMIT 1
				) as EFCSL_MP on TRUE
				left join v_Person_all EFCSL_P on EFCSL.PersonEvn_id = EFCSL_P.PersonEvn_id AND EFCSL.Server_id = EFCSL_P.Server_id
				left join v_EvnForensicCrimeSmeSwab  EFCSS  on EFCSS.EvnForensicCrimeSmeSwab_pid = EFC.EvnForensicCrime_id
				left join v_Person_all EFCSS_P on EFCSS.PersonEvn_id = EFCSS_P.PersonEvn_id AND EFCSS.Server_id = EFCSS_P.Server_id
				LEFT JOIN LATERAL (
					SELECT
						MP.Person_Fin
					FROM
						v_MedPersonal MP
					WHERE
						MP.MedPersonal_id = EFCSS.MedPersonal_id
				    LIMIT 1
				) as EFCSS_MP on TRUE
			WHERE 
				EFC.EvnForensicCrime_id = :EvnForensic_id
			";

		$result = $this->queryResult($query, $queryParams);
		if (!$this->isSuccessful($result)) {
			return $result;
		}


		if (!empty($result[0])) {
			if (!empty($result[0]['EvnForensicCrimeEvid_id'])) {
				//
				// Получаем список потерпевших/обвиняемых
				//
				$evid_link_result = $this->getEvnForensicCrimeEvidLinkList(array(
					'EvnForensicCrimeEvid_id' => $result[0]['EvnForensicCrimeEvid_id']
				));

				if (!$this->isSuccessful($evid_link_result)) {
					return $evid_link_result;
				} else {
					$result[0]['EvnForensicCrimeEvidLink'] = $evid_link_result;
				}
				//
				// Получаем список вещдоков
				//
				$evidence_result = $this->getEvidenceList(array(
					'EvnForensic_id' => $result[0]['EvnForensicCrimeEvid_id']
				));

				if (!$this->isSuccessful($evidence_result)) {
					return $evidence_result;
				} else {
					$result[0]['EvnForensicCrimeEvid_Evidence'] = $evidence_result;
				}
			}
		}
		if (!empty($result[0])) {
			$result[0]['success'] = true;
		}
		return $result;
	}

	/**
	 * Сохранение направления в медико-криминалистическое отделение
	 * @param type $data
	 * @return type
	 */
	public function saveForenMedCrimDirection($data)
	{

		$this->db->trans_begin();

		// Если направление ещё не создано, создаём заявку в судебно-биологической службе

		if (empty($data['EvnForensicCrime_id'])) {

			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('', 'Не задан обязательный параметр: Идентификатор родительской службы');
				} else {
					$data['MedService_pid'] = $data['session']['CurMedService_id'];
				}
			}

			//Получаем связанную службу, в которую отправитяс направление

			$msl = $this->getLinkedForenMedService(array(
				'MedService_id' => $data['MedService_pid'],
				'MedServiceLinkType_id' => 7  //Отделение БСМЭ - Медико-криминалистическое отделение
			));
			if (!$this->isSuccessful($msl)) {
				return $msl;
			}

			$data['MedService_id'] = $msl[0]['MedService_lid'];

			//Сохраняем заявку
			$saveRequestResult = $this->_saveEvnForenCrimeRequest($data);

			if (!$this->isSuccessful($saveRequestResult)) {
				$this->db->trans_rollback();
				return $saveRequestResult;
			}

			$data = array_merge($data, $saveRequestResult[0]);
		}

		$resultEvnForensicCrimeExCorpSave = $this->_saveEvnForensicCrimeExCorp($data);

		if (!$this->isSuccessful($resultEvnForensicCrimeExCorpSave)) {
			$this->db->trans_rollback();
		}

		$this->db->trans_commit();
		return $resultEvnForensicCrimeExCorpSave;
	}

	/**
	 * Сохранение направления на медико-криминалистичексое исследование в журнале
	 * @param type $data
	 * @return type
	 */
	protected function _saveEvnForensicCrimeExCorp($data)
	{

		if (empty($data['MedPersonal_id']) || empty($data['EvnForensicCrimeExCorp_id'])) {
			if (empty($data['session']['medpersonal_id'])) {
				return array(array('succes' => false, 'Error_Msg' => 'Не задан обязательный параметр: Идентификатор назначившего эксперта'));
			} else {
				$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			}
		}

		$rules = array(
			array('field' => 'EvnForensicCrime_id', 'label' => 'Идентификатор родительской заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicCrimeExCorp_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Cотрудник назначивший экспертизу', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),

			array('field' => 'CrymeStudyType_id', 'label' => 'Тип исследования', 'rules' => '', 'type' => 'id'),
			array('field' => 'Person_zid', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evidence', 'label' => 'Материалы', 'rules' => '', 'type' => 'array'),
			array('field' => 'EvnForensicCrimeExCorp_Liquid', 'label' => 'Фиксирующая жидкость', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_OpinNum', 'label' => 'Заключение №', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_TakeDate', 'label' => 'Дата взятия', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_Seal', 'label' => 'Опечатано печатью и оттиском', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicCrimeExCorp_Ques', 'label' => 'Вопросы подлежащие разрешению', 'rules' => '', 'type' => 'string'),

			array('field' => 'Lpu_id', 'rules' => 'required', 'label' => 'Идентификатор МО', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);


		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$proc = 'p_EvnForensicCrimeExCorp_ins';
		if (!empty($queryParams['EvnForensicCrimeExCorp_id'])) {
			$proc = 'p_EvnForensicCrimeExCorp_upd';
			$currentDataResult = $this->getEvnForensicCrimeExCorp($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null) && (!empty($currentDataResult[0]["$key"]))) ? $currentDataResult[0]["$key"] : $value;
			}

			//Если переданы новые материалы
			if (!empty($queryParams['Evidence'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id' => $queryParams['EvnForensicCrimeExCorp_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		} else {

			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан ReasearchedPerson_id - идентификатор исследуемого лица
				if (empty($queryParams['Person_zid'])) {
					return array(array('success' => false, 'Error_Msg' => 'Не задан обязательный параметр: Исследуемое лицо'));
				} else {
					$personState = $this->_getPersonStateByPersonId(array('Person_id' => $queryParams['Person_zid']));
					if (!$this->isSuccessful($personState) || sizeof($personState) == 0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams, $personState[0]);
					}
				}
			}

		}

		$query = "
			select 
			 	evnforensiccrimeexcorp_id as \"EvnForensicCrimeExCorp_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from {$proc} (
				EvnForensicCrimeExCorp_id :=  :EvnForensicCrimeExCorp_id,
				EvnForensicCrimeExCorp_pid :=  :EvnForensicCrime_id,
				CrymeStudyType_id:= :CrymeStudyType_id,
				EvnForensicCrimeExCorp_OpinNum:= :EvnForensicCrimeExCorp_OpinNum,
				EvnForensicCrimeExCorp_TakeDate:= :EvnForensicCrimeExCorp_TakeDate,
				EvnForensicCrimeExCorp_Liquid:= :EvnForensicCrimeExCorp_Liquid,
				EvnForensicCrimeExCorp_Facts:= :EvnForensicCrimeExCorp_Facts,
				EvnForensicCrimeExCorp_Ques:= :EvnForensicCrimeExCorp_Ques,
				EvnForensicCrimeExCorp_Seal:= :EvnForensicCrimeExCorp_Seal,
				MedPersonal_id:= :MedPersonal_id,
				
				Lpu_id :=  :Lpu_id,
				PersonEvn_id :=  :PersonEvn_id,
				Server_id :=  :Server_id,
				pmUser_id :=  :pmUser_id
			);
			";

		$result = $this->queryResult($query, $queryParams);

		foreach ($queryParams['Evidence'] as $evidence) {
			$evidence = array_merge($evidence, array(
				'EvnForensic_id' => $result[0]['EvnForensicCrimeExCorp_id'],
				'pmUser_id' => $queryParams['pmUser_id'],
				'EvidenceType_id' => 4
			));

			$saveEvidence = $this->_saveEvidence($evidence);
			if (!$this->isSuccessful($saveEvidence)) {
				return $saveEvidence;
			}
		}

		return $result;
	}

	/**
	 * Получение записи журнала направления на медико-криминалистичексое исследование
	 * @param type $data
	 * @return type
	 */
	public function getEvnForensicCrimeExCorp($data)
	{
		$rules = array(
			array('field' => 'EvnForensicCrimeExCorp_id', 'label' => 'Идентификатор записи в журнале', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT 
				EFCEC.EvnForensicCrimeExCorp_id as \"EvnForensicCrimeExCorp_id\",
				EFCEC.EvnForensicCrimeExCorp_pid as \"EvnForensicCrimeExCorp_pid\",
				EFCEC.EvnForensicCrimeExCorp_rid as \"EvnForensicCrimeExCorp_rid\",
				
				EFCEC.EvnForensicCrimeExCorp_pid as \"EvnForensicCrimeExCorp_pid\",--
				EFCEC.EvnForensicCrimeExCorp_id as \"EvnForensicCrimeExCorp_id\",--
				EFCEC.CrymeStudyType_id as \"CrymeStudyType_id\",--Тип исследования
				EFCEC.EvnForensicCrimeExCorp_OpinNum as \"EvnForensicCrimeExCorp_OpinNum\",--Заключение №
				EFCEC.EvnForensicCrimeExCorp_TakeDate as \"EvnForensicCrimeExCorp_TakeDate\",--Дата и время взятия материала
				EFCEC.EvnForensicCrimeExCorp_Liquid as \"EvnForensicCrimeExCorp_Liquid\",--Фиксирующая жидкость
				EFCEC.EvnForensicCrimeExCorp_Facts as \"EvnForensicCrimeExCorp_Facts\",--Краткие обстоятельства дела
				EFCEC.EvnForensicCrimeExCorp_Ques as \"EvnForensicCrimeExCorp_Ques\",--Вопросы подлежащие разрешению
				EFCEC.EvnForensicCrimeExCorp_Seal as \"EvnForensicCrimeExCorp_Seal\",--Опечатано печатью с оттиском
				EFCEC.MedPersonal_id as \"MedPersonal_id\",--Назначивший эксперт
				EFCEC.Lpu_id as \"Lpu_id\",
				EFCEC.PersonEvn_id as \"PersonEvn_id\"
				EFCEC.Server_id as \"Server_id\"
			FROM
				v_EvnForensicCrimeExCorp EFCEC
			WHERE
				EFCEC.EvnForensicCrimeExCorp_id = :EvnForensicCrimeExCorp_id
		";

		return $this->queryResult($query, $queryParams);
	}


	/**
	 * Функция получения прикрепленых файлов
	 * @param type $data
	 * @return type
	 */
	protected function _getAttachment($data)
	{

		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$this->load->model('EvnMediaFiles_model', 'emfmodel');

		return $this->emfmodel->loadEvnMediaFilesListGrid(array(
			'Evn_id' => $queryParams['EvnForensic_id'],
			'filterType' => 'all'
		));

	}

	/**
	 * Функция получения количества заявок для вкладок армов БСМЕ
	 * @return array
	 */
	public function getRequestCount($data)
	{


		if (empty($data['MedPersonal_id'])) {
			if (empty($data['session']['medpersonal_id'])) {

			} else {
				$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			}
		}

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$rules = array(
			array('field' => 'ARMType', 'label' => 'Тип АРМ', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор эксперта', 'rules' => '', 'type' => 'id'),
			array('field' => 'ForensicSubType_id', 'label' => 'Тип заявки', 'rules' => '', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		//@TODO: Почему-то при выборке из v_EvnForensic количество записей по группам не совпадает с выборкой из потомков
		$medServiceNickResult = $this->getCurrentMedServiceSysNick($queryParams);
		if (!$this->isSuccessful($medServiceNickResult)) {
			return $medServiceNickResult;
		}
		$obj = '';
		switch ($medServiceNickResult[0]['MedServiceType_SysNick']) {
			case 'forenbiodprtwithmolgenlab':
				$obj = 'EvnForensicGenetic';
				break;
			case 'forenchemdprt':
				$obj = 'EvnForensicChem';
				break;
			case 'medforendprt':
				$obj = 'EvnForensicCrim';
				break;
			case 'medforendprt':
				$obj = 'EvnForensicCrim';
				break;
			case 'forenhistdprt':
				$obj = 'EvnForensicHist';
				break;
			case 'organmethdprt':
				$obj = '?';
				break;
			case 'forenmedcorpsexpdprt':
				$obj = 'EvnForensicCorpHist';
				break;
			case 'forenmedexppersdprt':
				$obj = 'EvnForensicSub';
				break;
			case 'commcomplexp':
				$obj = 'EvnForensicComplex';
				break;
			case 'forenareadprt':
				$obj = '?';
				break;
			default:
				return $this->createError('', 'Неверно указанный тип службы');
				break;
		}

		if (empty($obj)) {
			return $this->createError('', 'Ошибка получения объекта выборки');
		}

		$where = '';
		$from = '';

		// Эксперт видит вкладки, назначенные только ему
		if ($queryParams['ARMType'] == 'expert') {
			if (!$queryParams['MedPersonal_id']) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор эксперта');
			}
			$where .= 'AND EF.MedPersonal_eid = :MedPersonal_id';

		}

		//Дополнительные параметры фильтрации для отдела потерпевших обвиняемых и других лиц
		if ($obj == 'EvnForensicSub' && !empty($queryParams['ForensicSubType_id'])) {
			$where .= ' AND EF.ForensicSubType_id =:ForensicSubType_id';
		}

		$query = "
			SELECT
				sum(case when coalesce(ES.EvnStatus_SysNick,'New') != 'Done' then 1 else 0 end) as \"All\",
				sum(case when coalesce(ES.EvnStatus_SysNick,'New') = 'New' then 1 else 0 end) as \"New\",
				sum(case when coalesce(ES.EvnStatus_SysNick,'New') = 'Appoint' then 1 else 0 end) as \"Appoint\",
				sum(case when coalesce(ES.EvnStatus_SysNick,'New') = 'Check' then 1 else 0 end) as \"Check\",
				sum(case when coalesce(ES.EvnStatus_SysNick,'New') = 'Approved' then 1 else 0 end) as \"Approved\"

			FROM
				v_{$obj} as EF
				left join v_EvnStatus ES  on EF.EvnStatus_id = ES.EvnStatus_id
				$from
			WHERE
				EF.MedService_id = :MedService_id
				$where
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение сисника службы
	 */
	protected function getCurrentMedServiceSysNick($data)
	{

		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('', 'Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}

		$rules = array(
			array('field' => 'MedService_id', 'label' => 'Идентификатор службы', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				MST.MedServiceType_SysNick as \"MedServiceType_SysNick\"
			FROM
				v_MedService MS
				left join v_MedServiceType MST on MS.MedServiceType_id = MST.MedServiceType_id
			WHERE
				MS.MedService_id = :MedService_id
			";

		return $this->queryResult($query, $queryParams);

	}

	/**
	 * Функция удаления заявки
	 * @return boolean
	 */
	public function deleteEvnForensic($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			select
			 	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"
            from p_EvnForensic_del (
				EvnForensic_id := :EvnForensic_id);
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Функция получения журнала службы судебно-медицинской экспертизы потерпевших обвиняемых и других лиц
	 */
	function getEvnForensicComplexArchive($data)
	{
		$data['MedService_id'] = (empty($data['MedService_id']))
			? ((empty($data['session']['CurMedService_id']))
				? null
				: $data['session']['CurMedService_id'])
			: $data['MedService_id'];

		$rules = array(
			array('field' => 'MedService_id', 'rules' => 'required', 'label' => 'Идентификатор службы', 'type' => 'id'),
			array('field' => 'start', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'limit', 'label' => '', 'rules' => '', 'type' => 'int', 'default' => 10),
			array('field' => 'JournalType', 'label' => 'Тип журнала', 'rules' => 'required', 'type' => 'string'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$whereClause = '';
		$selectClause = '';
		$filter = "
			EFC.MedService_id:=:MedService_id
			AND EFC.EvnClass_id := :EvnClass_id
			AND ES.EvnStatus_SysNick := 'Done'
		";

		if ((array_key_exists('begDate', $data) && $data['begDate'] != NULL) &&
			(array_key_exists('endDate', $data) && $data['endDate'] != NULL)) {
			$date_start = DateTime::createFromFormat('d.m.Y', $data['begDate']);
			$date_finish = DateTime::createFromFormat('d.m.Y', $data['endDate']);

			$queryParams['begDate'] = $date_start->format('Y-m-d') . ' 00:00:00';
			$queryParams['endDate'] = $date_finish->format('Y-m-d') . ' 23:59:59';

			$filter .= "
				AND EFC.EvnForensicComplex_insDT >= :begDate
				AND EFC.EvnForensicComplex_insDT <= :endDate
			";
		}

		switch ($queryParams['JournalType']) {
			case 'EvnForensicComplexResearch':
				$queryParams['EvnClass_id'] = 159;
				$whereClause = '
					left join v_EvnForensicComplexResearch EFCR on EFCR.EvnForensicComplexResearch_id = EFC.EvnForensicComplex_id
					left join v_EvnForensicComplex pEFC on pEFC.EvnForensicComplex_id = EFC.EvnForensicComplex_pid
					left join v_PersonState PS on PS.Person_id = pEFC.Person_cid
					left join v_Job Jc on PS.Job_id = Jc.Job_id
					left join v_Post Pc  on Pc.Post_id = Jc.Post_id
					';
				$selectClause = "
					,NULLIF (COALESCE(PS.Person_SurName, '') || COALESCE(' ' || PS.Person_FirName, '') || COALESCE(' '  || PS.Person_SecName, ''), '') as \"Person_FIO\"
					,EFCR.EvnForensicComplexResearch_Base as \"EvnForensicComplexResearch_Base\"
				";
				break;
			default:
				return $this->createError('', 'Неверно задано поле "Тип журнала"');
				break;
		}

		$query = "
			SELECT
			 -- select
                EFC.EvnForensicComplex_id  as \"EvnForensic_id\",
                EFC.EvnForensicComplex_Num as \"EvnForensic_Num\",
                to_char(AVF.ActVersionForensic_insDT , 'dd.mm.yyyy') as \"ActVersionForensic_insDT\", -- дата проведения эксп
                COALESCE(EFT.EvnForensicType_Name,'Не определён') as \"EvnForensicType_Name\",
                '' as \"EvnForensicComplex_Result\", -- NULLNULLNULL
                AVF.ActVersionForensic_Num as \"ActVersionForensic_Num\",
                COALESCE(MP.Person_Fin,'Не назначен') as \"Expert_Fin\",
                case when (COALESCE(EFC.Person_gid,0)=0)
                    then '№ квитанции: '||EFC.PostTicket_Num || '</br>' || 'Дата: '||to_char(EFC.PostTicket_Date , 'dd.mm.yyyy')
                    else NULLIF (COALESCE(PSg.Person_SurName, '') || COALESCE(' ' || PSg.Person_FirName, '') || COALESCE(' ' || PSg.Person_SecName, ''), '') || '</br>' || 'Удостоверение: ' || EFC.RecipientIdentity_Num
                end as \"Receiver\"
				{$selectClause}
            -- end select
            FROM
            -- from
                v_EvnForensicComplex EFC
                left join v_EvnDirectionForensic EDF on  EDF.EvnForensic_id = EFC.EvnForensicComplex_id
                LEFT JOIN LATERAL (
                    SELECT 
                        MP.Person_Fin as Person_Fin
                    FROM
                        v_MedPersonal MP
                    WHERE
                        MP.MedPersonal_id = EDF.MedPersonal_id
					LIMIT 1
                ) as MP on true
                left join v_EvnForensicType EFT on EDF.EvnForensicType_id = EFT.EvnForensicType_id

                left join v_PersonState PSg on PSg.Person_id = EFC.Person_gid

                LEFT JOIN v_EvnStatus ES ON( ES.EvnStatus_id=EFC.EvnStatus_id )
                LEFT JOIN LATERAL (
                    SELECT
                        AVF.ActVersionForensic_id,
                        AVF.ActVersionForensic_Num,
                        AVF.ActVersionForensic_Text,
                        AVF.ActVersionForensic_insDT
                    FROM
                        v_ActVersionForensic AVF
                    WHERE
                        AVF.EvnForensic_id = EFC.EvnForensicComplex_id
                    ORDER BY
                        AVF.ActVersionForensic_insDT DESC
                    LIMIT 1
                ) as AVF on true
				{$whereClause}
            -- end from
            WHERE
            -- where
				{$filter}
            -- end where
			";
		//@TODO: Добавим статусы, когда разберёмся
		$result = array();

		$orderClause = "
			ORDER BY
			-- order by
                EFC.EvnForensicComplex_Num DESC
            -- end order by
		";

		if (!empty($data['filterField']) && !empty($data['filterVal'])) {
			$query = "Select * FROM(" . $query . ") as a
				WHERE a." . $data["filterField"] . " LIKE '%" . $data['filterVal'] . "%'
				 ORDER BY
				-- order by
					EvnForensic_Num DESC
				-- end order by
				";
			$result['totalCount'] = 1;
			$result['data'] = $this->queryResult($query, $queryParams);
			return $result;
		} else {
			$query = $query . $orderClause;
		}
		//die(getDebugSQL($query,$queryParams));
		$count_result = $this->queryResult(getCountSQLPH($query), $queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount'] = $count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']), $queryParams);

		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data'] = $data_result;
		}

		return $result;
	}

	/**
	 * Функция получения списка должностей инициатора
	 *
	 */
	public function getAssignedPersonPostList()
	{
		$query = "
			SELECT *
			from v_Post P
			where P.Post_id in (77300268893, 77300268895, 77300268896, 77300268897, 77300268898, 77300268899, 77300268900, 77300268901, 77300268902, 77300268903)
			";

		$result = $this->db->query($query, array());

		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Функция сохранения пустого документа в БСМЕ
	 * @return boolean
	 */
	public function createEmpty($data)
	{
		$this->load->library('swXmlTemplate');
		$doc = swXmlTemplate::getEvnXmlModelInstance();
		$rules = $doc->getInputRules('createEmpty');
		$rules[] = array('field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id');
		$rules[] = array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => 'required', 'type' => 'id');
		$rules[] = array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id');
		$queryParams = $this->_checkInputData($rules, $data, $err, true);
		if (!$queryParams) return $err;

		$queryParams['Evn_id'] = $queryParams['EvnForensic_id'];

		$this->db->trans_begin();
		$createEmpty_response = $doc->createEmpty($queryParams, false);
		if (!empty($createEmpty_response['Error_Msg'])) {
			$this->db->trans_rollback();
			return $createEmpty_response;
		}

		$EvnForensic = $this->getEvnForensic($queryParams['EvnForensic_id']);
		if ($EvnForensic['EvnForensic_Inherit'] == 2 && !empty($EvnForensic['EvnForensic_pid'])) {
			// Получаем ID последней версии документа
			$query = $this->db->query("SELECT EvnXml_id as \"EvnXml_id\" FROM v_ForensicEvnXmlVersion WHERE EvnForensic_id=:EvnForensic_id ORDER BY ForensicEvnXmlVersion_Num DESC", array(
				'EvnForensic_id' => $EvnForensic['EvnForensic_pid']
			));
			$result = $query->row_array();
			if (!empty($result['EvnXml_id'])) {
				$fields = swXmlTemplate::getEvnXmlModelInstance()->getEvnXmlFormFields(array_merge($data, array(
					'EvnXml_id' => $result['EvnXml_id']
				)));
				if (!$this->isSuccessful($fields)) {
					$this->db->trans_rollback();
					return $fields;
				}

				$allowed_fields = array(
					'researchpart',
					'conclude'
				);

				$list = array();
				if (array_key_exists('response', $fields)) {
					foreach ($fields['response'] as $v) if (in_array($v['name'], $allowed_fields)) {
						$list[$v['name']] = $v['value'];
					}
				}
				if (sizeof($list)) {
					$tmp = swXmlTemplate::getEvnXmlModelInstance()->updateSectionContent(array_merge($data, array(
						'EvnXml_id' => $createEmpty_response['EvnXml_id'],
						'XmlData' => json_encode($list),
					)));
					if (!empty($tmp['Error_Msg'])) {
						$this->db->trans_rollback();
						return $tmp;
					}
				}
				unset($query, $result, $fields, $allowed_fields, $list);
			}
		}

		/*
		 * При создании документа создаётся запись о первой версии заключения для заявки (_createEvnXmlVersion)
		 * Если передан EvnXml_id, значит документ уже был сохранен и версию для него создавать нет нужды
		 */
		if (empty($queryParams['EvnXml_id'])) {
			$queryParams['EvnXml_id'] = $createEmpty_response['EvnXml_id'];
			$createVersion_response = $this->_createEvnXmlVersion($queryParams);
			if (!$this->isSuccessful($createVersion_response)) {
				$this->db->trans_rollback();
				return $createVersion_response;
			}
		}


		$this->db->trans_commit();
		return $createEmpty_response;
	}

	/**
	 * Сохранение версии документа
	 * @param type $data
	 */
	protected function _createEvnXmlVersion($data)
	{

		$rules = array(
			array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => '', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				evnforensic_id as \"EvnForensic_id\", 
            	error_code as \"Error_Code\", 
            	error_message as \"Error_Msg\"

			FROM p_ForensicEvnXmlVersion_ins (
				ForensicEvnXmlVersion_id = :EvnForensic_id,
				ForensicEvnXmlVersion_Num = COALESCE((
							SELECT MAX(COALESCE(FEXV.ForensicEvnXmlVersion_Num,0)+1) as ForensicEvnXmlVersion_Num 
							FROM v_ForensicEvnXmlVersion FEXV
							WHERE FEXV.EvnForensic_id = :EvnForensic_id
							),1),
				EvnForensic_id = :EvnForensic_id,
				EvnXml_id = :EvnXml_id,
				pmUser_id = :pmUser_id
				);
			";

		return $this->queryResult($query, $queryParams);

	}

	/**
	 * Возвращение заявки в работу
	 */
	public function revisionEvnForensic($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnXml_id', 'label' => 'Идентификатор документа', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'pmUser_id', 'rules' => 'required', 'label' => 'Идентификатор пользователя', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, true);
		if (!$queryParams || !empty($err)) return $err;

		$this->db->trans_begin();

		/*
		 * 1. Создаём копию (следующую версию) документа
		 */
		$this->load->library('swXmlTemplate');
		$queryParams['Evn_id'] = $queryParams['EvnForensic_id'];
		$queryParams['copyMethod'] = 'withDoc';
		$createCopy_response = swXmlTemplate::getEvnXmlModelInstance()->doCopy($queryParams, false);
		if (!empty($createCopy_response['Error_Msg'])) {
			$this->db->trans_rollback();
			return $createCopy_response;
		}
		$queryParams['EvnXml_id'] = $createCopy_response['EvnXml_id'];

		/*
		 * 2. Создаём запись о следующей версии документа
		 */

		$createVersion_response = $this->_createEvnXmlVersion($queryParams);
		if (!$this->isSuccessful($createVersion_response)) {
			$this->db->trans_rollback();
			return $createVersion_response;
		}

		/*
		 * 3. Устанавливаем статус заявке
		 */

		$changeStatus_result = $this->dbmodel->changeEvnForensicStatus($data, 'Appoint');
		if (!$this->isSuccessful($changeStatus_result)) {
			$this->db->trans_rollback();
		}

		$this->db->trans_commit();
		return $changeStatus_result;
	}

	/**
	 * Получение списка версий документа (заключения) для заявки
	 * @return boolean
	 */
	public function getForensicXmlVersionList($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				FEXV.ForensicEvnXmlVersion_id as \"ForensicEvnXmlVersion_id\",
				FEXV.ForensicEvnXmlVersion_Num as \"ForensicEvnXmlVersion_Num\",
				FEXV.EvnXml_id as \"EvnXml_id\",
				to_char(FEXV.ForensicEvnXmlVersion_insDT, 'dd.mm.yyyy')||' '||to_char(FEXV.ForensicEvnXmlVersion_insDT, 'hh24:mi') as \"ForensicEvnXmlVersion_insDT\",
				PUC.PMUser_surName||' '||PUC.PMUser_firName||' '||PUC.PMUser_secName as \"pmUser_Name\"
			FROM
				v_ForensicEvnXmlVersion FEXV
				LEFT JOIN pmUserCache PUC on FEXV.pmUser_insID = PUC.PMUser_id
			WHERE
				FEXV.EvnForensic_id = :EvnForensic_id
			";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение списка версий документа (заключения) для заявки
	 * @return boolean
	 */
	public function getForensicXmlVersionLast($data)
	{
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор события', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = "
			SELECT
				FEXV.ForensicEvnXmlVersion_id as \"ForensicEvnXmlVersion_id\",
				FEXV.ForensicEvnXmlVersion_Num as \"ForensicEvnXmlVersion_Num\",
				FEXV.EvnXml_id as \"EvnXml_id\",
				to_char(FEXV.ForensicEvnXmlVersion_insDT, 'dd.mm.yyyy')||' '||to_char(FEXV.ForensicEvnXmlVersion_insDT, 'hh24:mi') as \"ForensicEvnXmlVersion_insDT\",
				PUC.PMUser_surName||' '||PUC.PMUser_firName||' '||PUC.PMUser_secName as \"pmUser_Name\"
			FROM
				v_ForensicEvnXmlVersion FEXV
				LEFT JOIN pmUserCache PUC on FEXV.pmUser_insID = PUC.PMUser_id
			WHERE
				FEXV.EvnForensic_id = :EvnForensic_id
			";

		return $this->queryResult($query, $queryParams);
	}
}
