<?php

class EvnForensicGenetic_model extends BSME_model {
	
	/**
	 * Функция сохранения заявки для службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
	 * @param array $data
	 * @return boolean
	 */
	public function saveEvnForensicGeneticRequest($data) {
		
		/* Сохранение заявки */
		$this->db->trans_begin();
		
		
		$insertEvnForensicGeneticResult = $this->saveEvnForensicGenetic($data);
		if (!$this->isSuccessful($insertEvnForensicGeneticResult)) {
			$this->db->trans_rollback();
			return $insertEvnForensicGeneticResult;
		}
		
		$data['EvnForensicGenetic_id'] = $insertEvnForensicGeneticResult[0]['EvnForensicGenetic_id'];
		
		/*Проверка наличия и полноты записей журналов*/
		
		
		$journalsCount = 0; //Счетчик заполненных журналов
		
		
		/****************************************************************************************** */
		/* Проверка журнала регистрации вещественных доказательств и документов к ним в лаборатории */
		/****************************************************************************************** */
		$journalSingleFields = array('EvnForensicGeneticEvid_AccDocNum','EvnForensicGeneticEvid_AccDocDate','EvnForensicGeneticEvid_AccDocNumSheets','Org_id','EvnForensicGeneticEvid_Facts','EvnForensicGeneticEvid_Goal');
		$journalArrayFields = array(array('key'=>'Person','val'=>'Person_id'),array('key'=>'Evidence','val'=>'Evidence_Name'));
		
		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);
		
		if ($journalFilledStatus == 'empty') {
			//Ничего
		} elseif ($journalFilledStatus == 'unfinished') {
			
			$this->db->trans_rollback();
			return $this->createError('', 'Не все поля журнала регистрации вещественных </br> доказательств и документов к ним в лаборатории заполнены. </br> Пожалуйста, заполните все поля');
			
		} elseif ($journalFilledStatus == 'filled') {
			
			$saveJournalResult = $this->_saveEvnForensicGeneticEvidJournal($data);
			
			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			}
		}
		
		/****************************************************************************************** */
		/* Проверка журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории */
		/****************************************************************************************** */
		
		$journalSingleFields = array('EvnForensicGeneticSampleLive_TakeDate','Person_zid','EvnForensicGeneticSampleLive_Basis');
		$journalArrayFields = array(array('key'=>'BioSample','val'=>'BioSample_Name'));
		
		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);
		
		if ($journalFilledStatus == 'empty') {
			//Ничего
		} elseif ($journalFilledStatus == 'unfinished') {
			
			$this->db->trans_rollback();
			return $this->createError('', 'Не все поля журнала регистрации биологических </br> образцов, изъятых у живых лиц в лаборатории заполнены. </br> Пожалуйста, заполните все поля');
			
		} elseif ($journalFilledStatus == 'filled') {
			
			$saveJournalResult = $this->_saveEvnForensicGeneticSampleLiveJournal($data);
			
			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			}
		}
		
		/*******************************************************************************************/
		/* Проверка журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования */
		/****************************************************************************************** */

		$journalSingleFields = array('EvnForensicGeneticGenLive_TakeDate','EvnForensicGeneticGenLive_Facts');
		$journalArrayFields = array(array('key'=>'PersonBioSample','val'=>'Person_id'),array('key'=>'BioSampleForMolGenRes','val'=>'BioSampleForMolGenRes_Name'));
		
		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);
		
		if ($journalFilledStatus == 'empty') {
			//Ничего
		} elseif ($journalFilledStatus == 'unfinished') {
			$this->db->trans_rollback();
			return $this->createError('','Не все поля журнала регистрации биологических образцов, </br> изъятых у живых лиц в лаборатории для молекулярно-генетического исследования заполнены. </br> Пожалуйста, заполните все поля');
		} elseif ($journalFilledStatus == 'filled') {
			$saveJournalResult = $this->_saveEvnForensicGeneticGenLiveJournal($data);
			
			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			} 
		}
		
		/****************************************************************************************** */
		/* Проверка журнала регистрации исследований мазков и тампонов в лаборатории */
		/****************************************************************************************** */
		
		$journalSingleFields = array('ReasearchedPerson_id','EvnForensicGeneticSmeSwab_Basis');
		$journalArrayFields = array(array('key'=>'Sample','val'=>'Sample_Name'));
		$journalFilledStatus = $this->_checkJournalFieldsEmptyness($data, $journalSingleFields, $journalArrayFields);
		
		if ($journalFilledStatus == 'empty') {
			//Ничего
		} elseif ($journalFilledStatus == 'unfinished' || !$journalFilledStatus) {
			
			$this->db->trans_rollback();
			return array(array('succes'=>false,'Error_Msg'=>'Не все поля журнала регистрации исследований </br> мазков и тампонов в лаборатории заполнены. </br> Пожалуйста, заполните все поля'));
			
		} elseif ($journalFilledStatus == 'filled') {
			
			$saveJournalResult = $this->_saveEvnForensicGeneticSmeSwabJournal($data);
			
			if (!$this->isSuccessful($saveJournalResult)) {
				$this->db->trans_rollback();
				return $saveJournalResult;
			} 
			
		}
		
		//if ($journalsCount == 0) {
		//	return array(array('succes'=>false,'Error_Msg'=>'Не заполнено ни одного журнала </br> Пожалуйста, заполните хотя бы один журнал.'));
		//}
		
		$this->db->trans_commit();
		return $insertEvnForensicGeneticResult;
		
	}
	
	/**
	 * Функция сохранения заявки  службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
	 */
	protected function saveEvnForensicGenetic($data) {
		if (empty($data['MedService_id'])) {
			if (empty($data['MedService_pid'])) {
				if (empty($data['session']['CurMedService_id'])) {
					return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
				} else {
					$data['MedService_id'] = $data['session']['CurMedService_id'];
				}
			} else {
				//Если передан MedService_fid - значит создается направление из другой службы и из сессии получить MedService_id службы, 
				//которой направление назначается, не получится
				return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
			}
		}
		
		$rules = array(
			array('field'=>'Evn_pid','label'=>'Идентификатор родительского события', 'type' => 'id'),
			array('field'=>'EvnForensic_ResDate','label'=>'Дата постановления', 'rules'=>'','type' => 'string'),
			array('field'=>'Evn_rid', 'label'=>'Идентификатор получателя документа', 'type' => 'id'),
			array('field'=>'Person_cid','label'=>'Направившее лицо', 'rules'=>'required', 'type' => 'id'),
			array('field'=>'MedService_id','label'=>'Идентификатор службы', 'rules'=>'required', 'type' => 'id'),
			array('field'=>'MedService_pid','label'=>'Идентификатор службы - родителя', 'type' => 'id'),
			array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
			array('field'=>'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			declare
				@Res bigint,
				@EvnForensic_Num int,
				@ErrCode int,
				@ErrMessage varchar(4000)
				
			set @Res = NULL;
			set @EvnForensic_Num = ISNULL((SELECT MAX(ISNULL(EF.EvnForensic_Num,0)+1) as EvnForensic_Num FROM v_EvnForensic EF with (nolock)),1);

			exec p_EvnForensicGenetic_ins
				@EvnForensicGenetic_id = @Res output,
				@EvnForensicGenetic_Num = @EvnForensic_Num,
				@EvnForensicGenetic_ResDate = :EvnForensic_ResDate,
				@MedService_id = :MedService_id,
				@Person_cid = :Person_cid,
				@MedService_pid = :MedService_pid,
				@EvnForensicGenetic_pid =:Evn_pid,
				@EvnForensicGenetic_rid =:Evn_rid,
				@pmUser_id = :pmUser_id,
				@Lpu_id = :Lpu_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGenetic_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
		return $this->queryResult($query,$queryParams);
	}
	
	/**
	 * Функция сохранения вещественного докозательства или био образца
	 * @param int $data
	 * @return boolean
	 */
	protected function _saveEvnForensicGeneticGenLiveLink($data) {
		
		$rules = array(
			array('field'=>'EvnForensicGeneticGenLive_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
			array('field'=>'Person_id','label'=>'Идентификатор обвиняемого/потерпевшего', 'rules'=>'required' , 'type' => 'id'),
			array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = NULL;

			exec p_EvnForensicGeneticGenLiveLink_ins
				@EvnForensicGeneticGenLiveLink_id = @Res output,
				@Person_id = :Person_id,
				@EvnForensicGeneticGenLive_id = :EvnForensicGeneticGenLive_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticGenLiveLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
		
		return $this->queryResult($query, $queryParams);
	}
	/**
	 * Функция удаления всех подозреваемых/обвиняемых для записи журнала регистрации вещественных доказательств и документов к ним в лаборатории
	 * @param type $data
	 * @return type
	 */
	protected function _deleteEvnForensicGeneticEvidLink($data) {
		$rules = array(
			array('field'=>'EvnForensicGeneticEvid_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			exec p_EvnForensicGeneticEvidLink_delByEvnForensicId
				@EvnForensic_id = :EvnForensic_id,
				
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
		
		return $this->queryResult($query,$queryParams);
	}
	
	/**
	 * Функция сохранения подозреваемого/обвиняемого для журнала регистрации вещественных доказательств и документов к ним в лаборатории
	 * @param type $data
	 * @return boolean
	 */
	protected function _saveEvnForensicGeneticEvidLink($data) {
		$rules = array(
			array('field'=>'EvnForensicGeneticEvid_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
			array('field'=>'Person_id','label'=>'Идентификатор обвиняемого/потерпевшего', 'rules'=>'required' , 'type' => 'id'),
			array('field'=>'EvnForensicGeneticEvidLink_IsVic','label'=>'флаг потерпевшего', 'rules'=>'required' , 'type' => 'int'),
			array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = NULL;

			exec p_EvnForensicGeneticEvidLink_ins
				@EvnForensicGeneticEvidLink_id = @Res output,
				@EvnForensicGeneticEvid_id = :EvnForensicGeneticEvid_id,
				@Person_id = :Person_id,
				@EvnForensicGeneticEvidLink_IsVic = :EvnForensicGeneticEvidLink_IsVic,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticEvidLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
		
		return $this->queryResult($query, $queryParams);
		
	}
	/**
	* Функция удаления всех исследуемых лиц для записи журнала регистрации биологичесеских образцов живых лиц для мол/ген исследования
	*/
	protected function _deleteEvnForensicGeneticGenLiveLink($data) {
		$rules = array(
			array('field'=>'EvnForensicGeneticGenLive_id','label'=>'Идентификатор записи журнала', 'rules'=>'required' , 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			exec p_EvnForensicGeneticGenLiveLink_delByEvnForensicId
				@EvnForensic_id = :EvnForensic_id,
				
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
		
		return $this->queryResult($query,$queryParams);
	}
	
	/**
	 * Функция получения записи журнала мазков и тампонов
	 * @param int $data
	 * @return boolean
	 */
	protected function getEvnForensicGeneticSmeSwabJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGeneticSmeSwab_id', 'label' => 'Идентификатор записи в журнале', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			SELECT 
				EFGSS.EvnForensicGeneticSmeSwab_DelivDate,
				EFGSS.EvnForensicGeneticSmeSwab_Basis,
				EFGSS.EvnForensicGeneticSmeSwab_BegDate,
				EFGSS.EvnForensicGeneticSmeSwab_EndDate,
				EFGSS.EvnForensicGeneticSmeSwab_Comment,
			FROM
				v_EvnForensicGeneticSmeSwab EFGSS with (nolock)
			WHERE
				EFGSS.EvnForensicGeneticSmeSwab_id = :EvnForensicGeneticSmeSwab_id
			';
		
		return $this->queryResult($query,$queryParams);
		
	}
	/**
	 * Функция сохранения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории
	 * @param type $data
	 */
	protected function _saveEvnForensicGeneticSmeSwabJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSmeSwab_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReasearchedPerson_id', 'label' => 'Исследуемое лицо журнала регистрации исследований мазков и тампонов в лаборатории', 'rules' => '', 'type' => 'id'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSmeSwab_Basis', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticSmeSwab_DelivDate', 'label' => 'Дата и время поступления образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticSmeSwab_BegDate', 'label' => 'Дата начала исследования', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticSmeSwab_EndDate', 'label' => 'Дата окончания исследования', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticSmeSwab_Comment', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'Sample', 'label' => 'Список образцов', 'rules' => '', 'type' => 'array'),
			array('field' => 'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
			array('field'=>  'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$proc = 'p_EvnForensicGeneticSmeSwab_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicGeneticSmeSwab_id'])) {
			$proc = 'p_EvnForensicGeneticSmeSwab_upd';
			$currentDataResult = $this->getEvnForensicGeneticSmeSwabJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
			}
			
			//Если переданы новые образцы сначала удаляем их из последней сохраненной записи журнала
			if (!empty($queryParams['Sample'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticSmeSwab_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		} else {
			
			// У вновь создаваемой записи журнала этого типа PersonEvn_id и Server_id обязательны и получаются из ReasearchedPerson_id
			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан ReasearchedPerson_id - идентификатор исследуемого лица
				if (empty($queryParams['ReasearchedPerson_id'])) {
					return  $this->createError('','Не задан обязательный параметр: Исследуемое лицо');
				} else {
					$personState = $this->_getPersonStateByPersonId(array('Person_id'=>$queryParams['ReasearchedPerson_id']));
					if (!$this->isSuccessful($personState) || sizeof($personState)==0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams,$personState[0]);
					}
				}
			}
		} 
		
		$query = '
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000),
				@CurrentDate datetime

			set @Res = :EvnForensicGeneticSmeSwab_id;
			
			exec p_EvnForensicGeneticSmeSwab_ins
				@EvnForensicGeneticSmeSwab_id = @Res output,
				@EvnForensicGeneticSmeSwab_pid = :EvnForensicGenetic_id,
				
				@EvnForensicGeneticSmeSwab_DelivDate=:EvnForensicGeneticSmeSwab_DelivDate,
				@EvnForensicGeneticSmeSwab_Basis=:EvnForensicGeneticSmeSwab_Basis,
				@EvnForensicGeneticSmeSwab_BegDate=:EvnForensicGeneticSmeSwab_BegDate,
				@EvnForensicGeneticSmeSwab_EndDate=:EvnForensicGeneticSmeSwab_EndDate,
				@EvnForensicGeneticSmeSwab_Comment=:EvnForensicGeneticSmeSwab_Comment,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Lpu_id = :Lpu_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticSmeSwab_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			';
		
		$result = $this->queryResult($query,$queryParams);
		
		if (!$this->isSuccessful($result)) {
			return $result;
		}
		
		foreach ($queryParams['Sample'] as $evidence) {
			$saveEvidenceResult = $this->_saveEvidence(array(
				'EvnForensic_id'=>$result[0]['EvnForensicGeneticSmeSwab_id'],
				'Evidence_Name'=>$evidence['Sample_Name'],
				'EvidenceType_id'=>1,
				'pmUser_id' => $queryParams['pmUser_id'],
			));
			if (!$this->isSuccessful($saveEvidenceResult)) {
				return $saveEvidenceResult;
			}
		}

		return $result;
	}
	/**
	 * Функция получения записи журнала регистрации биообразцов живых лиц для мол/ген исследования
	 * @param int $data
	 * @return boolean
	 */
	protected function getEvnForensicGeneticGenLiveJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGeneticGenLive_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$query = '
			SELECT
				EFGGL.EvnForensicGenetic_id,
				EFGGL.EvnForensicGeneticGenLive_id,
				EFGGL.EvnForensicGeneticGenLive_TakeDate,
				EFGGL.EvnForensicGeneticGenLive_Facts,
				EFGGL.Person_eid,
				EFGGL.Lpu_id
			FROM
				v_EvnForensicGeneticGenLive EFGGL with (nolock)	
			WHERE 
				EFGGL.EvnForensicGeneticGenLive_id = EvnForensicGeneticGenLive_id
		';
		return $this->queryResult($query,$queryParams);

	}
	/**
	 * Функция сохранения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
	 * @param type $data
	 */
	protected function _saveEvnForensicGeneticGenLiveJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticGenLive_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticGenLive_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticGenLive_TakeDate', 'label' => 'Дата изъятия образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonBioSample', 'label' => 'Список исследуемых лиц', 'rules' => '', 'type' => 'array'),
			array('field' => 'BioSampleForMolGenRes', 'label' => 'Список биологических образцов', 'rules' => '', 'type' => 'array'),
			array('field' => 'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
			array('field' => 'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		
		$proc = 'p_EvnForensicGeneticGenLive_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicGeneticGenLive_id'])) {
			$proc = 'p_EvnForensicGeneticGenLive_upd';
			$currentDataResult = $this->getEvnForensicGeneticGenLiveJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
			}
			
			//Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
			if (!empty($queryParams['BioSampleForMolGenRes'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticGenLive_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
			if (!empty($queryParams['PersonBioSample'])) {
				$deleteEvidResult = $this->_deleteEvnForensicGeneticGenLiveLink(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticGenLive_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :EvnForensicGeneticGenLive_id;

			exec {$proc}
				@EvnForensicGeneticGenLive_id = @Res output,
				@EvnForensicGeneticGenLive_pid = :EvnForensicGenetic_id,
				@EvnForensicGeneticGenLive_TakeDate =:EvnForensicGeneticGenLive_TakeDate,
				@EvnForensicGeneticGenLive_Facts = :EvnForensicGeneticGenLive_Facts,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticGenLive_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			
		$result = $this->queryResult($query, $queryParams);
		if (!$this->isSuccessful($result)) {
			return $result;
		}
		
		//Сохраняем исследуемые лица
		foreach ($queryParams['PersonBioSample'] as $person) {
			$saveEvnForensicGeneticGenLiveLinkResult = $this->_saveEvnForensicGeneticGenLiveLink(array(
				'EvnForensicGeneticGenLive_id' => $result[0]['EvnForensicGeneticGenLive_id'],
				'Person_id' => $person['Person_id'],
				'pmUser_id' => $queryParams['pmUser_id'],
			));
			if (!$this->isSuccessful($saveEvnForensicGeneticGenLiveLinkResult)) {
				return $saveEvnForensicGeneticGenLiveLinkResult;
			}
		}

		//Сохраняем биологические образцы
		foreach ($queryParams['BioSampleForMolGenRes'] as $evidence) {
			$saveEvidenceResult = $this->_saveEvidence(array(
				'EvnForensic_id'=>$result[0]['EvnForensicGeneticGenLive_id'],
				'Evidence_Name'=>$evidence['BioSampleForMolGenRes_Name'],
				'EvidenceType_id'=>1,
				'pmUser_id' => $queryParams['pmUser_id'],
			));
			if (!$this->isSuccessful($saveEvidenceResult)) {
				return $saveEvidenceResult;
			}
		}

		return $result;
	}
	/**
	 * Функция сохранения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории
	 * @param type $data
	 */
	protected function _saveEvnForensicGeneticSampleLiveJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSampleLive_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSampleLive_TakeDate', 'label' => 'дата и время взятия образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
			array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSampleLive_Basis', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticSampleLive_VerifyingDoc', 'label' => 'Основания для получения образцов', 'rules' => '', 'type' => 'string'),
			array('field' => 'MedPersonal_id', 'label' => 'Идентификатор работника изъявшего', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticSampleLive_IsConsent', 'label' => ' ', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosTestEA', 'label' => 'Тест-эритроцит А', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosTestEB', 'label' => 'Тест-эритроцит B', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosCyclAntiA', 'label' => 'Циклон Анти-A', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicGeneticSampleLive_IsIsosCyclAntiB', 'label' => 'Циклон Анти-B', 'rules' => '', 'type' => 'int'),
			array('field' => 'EvnForensicGeneticSampleLive_IsosOtherSystems', 'label' => 'Другие системы (изосерология)', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticSampleLive_Result', 'label' => 'Результаты определения групп по исследованым системам', 'rules' => '', 'type' => 'string'),
			array('field' => 'BioSample', 'label' => 'Биологические образцы', 'rules' => '', 'type' => 'array'),
			array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
			array('field'=>'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		
		$proc = 'p_EvnForensicGeneticSampleLive_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicGeneticSampleLive_id'])) {
			$proc = 'p_EvnForensicGeneticSampleLive_upd';
			$currentDataResult = $this->getEvnForensicGeneticSampleLiveJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams[$key] = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
			}
			//Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
			if (!empty($queryParams['BioSample'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticSampleLive_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			} else {
				$queryParams['BioSample'] = array();
			}
		} else {
			
			// У вновь создаваемой записи журнала этого типа PersonEvn_id и Server_id обязательны и получаются из Person_zid
			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан Person_zid - идентификатор исследуемого лица
				if (empty($queryParams['Person_zid'])) {
					return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Исследуемое лицо'));
				} else {
					$personState = $this->_getPersonStateByPersonId(array('Person_id'=>$queryParams['Person_zid']));
					if (!$this->isSuccessful($personState) || sizeof($personState)==0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams,$personState[0]);
					}
				}
			}
		}
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :EvnForensicGeneticSampleLive_id;

			exec {$proc}
				@EvnForensicGeneticSampleLive_id = @Res output,
				@EvnForensicGeneticSampleLive_pid = :EvnForensicGenetic_id,
				
				@EvnForensicGeneticSampleLive_TakeDate=:EvnForensicGeneticSampleLive_TakeDate,
				@EvnForensicGeneticSampleLive_Basis=:EvnForensicGeneticSampleLive_Basis,
				@EvnForensicGeneticSampleLive_VerifyingDoc=:EvnForensicGeneticSampleLive_VerifyingDoc,
				@EvnForensicGeneticSampleLive_IsosOtherSystems=:EvnForensicGeneticSampleLive_IsosOtherSystems,
				@EvnForensicGeneticSampleLive_Result=:EvnForensicGeneticSampleLive_Result,
				
				@MedPersonal_id=:MedPersonal_id,
				@EvnForensicGeneticSampleLive_IsConsent=:EvnForensicGeneticSampleLive_IsConsent,
				@EvnForensicGeneticSampleLive_IsIsosTestEA=:EvnForensicGeneticSampleLive_IsIsosTestEA,
				@EvnForensicGeneticSampleLive_IsIsosTestEB=:EvnForensicGeneticSampleLive_IsIsosTestEB,
				@EvnForensicGeneticSampleLive_IsIsosCyclAntiA=:EvnForensicGeneticSampleLive_IsIsosCyclAntiA,
				@EvnForensicGeneticSampleLive_IsIsosCyclAntiB=:EvnForensicGeneticSampleLive_IsIsosCyclAntiB,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticSampleLive_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		//$journalSingleFields = array('EvnForensicGeneticSampleLive_TakeDT','EvnForensicGeneticSampleLive_TakeTime','Person_zid','EvnForensicGeneticSampleLive_Basis');

		$result = $this->queryResult($query,$queryParams);

		if (!$this->isSuccessful($result)) {
			return $result;
		}
		
		//После сохранения записи в журнале сохраняем все биологические образцы
		foreach ($queryParams['BioSample'] as $evidence) {
			if (!empty($evidence['BioSample_Name'])) {
				$evidence['Evidence_Name'] = $evidence['BioSample_Name'];
			}
			$evidence['EvnForensic_id'] = $result[0]['EvnForensicGeneticSampleLive_id'];
			$evidence['EvidenceType_id'] = 1;
			$evidence['pmUser_id'] = $queryParams['pmUser_id'];

			$saveEvidenceResult = $this->_saveEvidence($evidence);
			if (!$this->isSuccessful($saveEvidenceResult)) {
				return $saveEvidenceResult;
			}
		}
		
		return $result;
	}
	/**
	 * Функция получения записи в журнале регистрации биологических образцов, изъятых у живых лиц в лаборатории
	 * @param type $data
	 */
	protected function getEvnForensicGeneticSampleLiveJournal($data)  {
		$rules = array(
			array('field' => 'EvnForensicGeneticSampleLive_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			SELECT
				EFSL.EvnForensicGeneticSampleLive_pid as EvnForensicGenetic_id,
				EFSL.EvnForensicGeneticSampleLive_id,
				EFSL.EvnForensicGeneticSampleLive_TakeDate,
				EFSL.EvnForensicGeneticSampleLive_Basis,
				EFSL.EvnForensicGeneticSampleLive_Result,
				EFSL.EvnForensicGeneticSampleLive_VerifyingDoc,
				EFSL.EvnForensicGeneticSampleLive_IsosOtherSystems,
				EFSL.MedPersonal_id,
				EFSL.EvnForensicGeneticSampleLive_IsConsent,
				EFSL.EvnForensicGeneticSampleLive_IsIsosTestEA,
				EFSL.EvnForensicGeneticSampleLive_IsIsosTestEB,
				EFSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiA,
				EFSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiB,
				EFSL.Lpu_id,
				EFSL.PersonEvn_id,
				EFSL.Server_id
			FROM
				v_EvnForensicGeneticSampleLive EFSL with (nolock)
			WHERE
				EFSL.EvnForensicGeneticSampleLive_id = :EvnForensicGeneticSampleLive_id
			';

		return $this->queryResult($query, $queryParams);
	}
			
	/**
	 * Функция сохранения записи в журнале регистрации вещественных доказательств и документов к ним в лаборатории
	 * для службы судебно биологической экспертизы
	 * @param type $data
	 * @return boolean
	 */
	protected function _saveEvnForensicGeneticEvidJournal($data) {

		$rules = array(
			array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticEvid_id', 'label' => 'Идентификатор записи в журнале', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticEvid_AccDocNum', 'label' => '№ основного сопроводительного документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticEvid_AccDocDate', 'label' => 'Дата основного сопроводительного документа', 'rules' => '', 'type' => 'string'),//string, т.к. уже преобразованов контроллере
			array('field' => 'EvnForensicGeneticEvid_AccDocNumSheets', 'label' => 'Кол-во листов документов', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_id', 'label' => 'Идентификатор учреждения направившего', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticEvid_Facts', 'label' => 'Краткие обстоятельства дела', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticEvid_Goal', 'label' => 'Цель экспертизы', 'rules' => '', 'type' => 'string'),
			
			array('field' => 'Person', 'label' => 'Потерпевшие/обвиняемые', 'rules' => '', 'type' => 'array'),// преобразовано из string в array контроллере
			array('field' => 'Evidence', 'label' => 'Вещественные доказательства', 'rules' => '', 'type' => 'array'),
			
			array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
			array('field'=>'Lpu_id','rules' =>'', 'label'=>'Идентификатор МО', 'type' => 'id'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$proc = 'p_EvnForensicGeneticEvid_ins';
		// Если происходит обновление записи журнала, пришедшие пустые значения заменяем на последние сохранённые
		// и удаляем прикреплённые биоматериалы
		if (!empty($queryParams['EvnForensicGeneticEvid_id'])) {
			$proc = 'p_EvnForensicGeneticEvid_upd';
			$currentDataResult = $this->getEvnForensicGeneticEvidJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
			}
			
			//Если переданы новые вещдоки и потерпевшие/обвиняемые сначала удаляем их из последней сохраненной записи журнала
			if (!empty($queryParams['Evidence'])) {
				$deleteEvidResult = $this->_deleteEvidence(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticEvid_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
			if (!empty($queryParams['Person'])) {
				$deleteEvidResult = $this->_deleteEvnForensicGeneticEvidLink(array('EvnForensic_id'=>$queryParams['EvnForensicGeneticEvid_id']));
				if (!$this->isSuccessful($deleteEvidResult)) {
					return $deleteEvidResult;
				}
			}
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)

			set @Res = :EvnForensicGeneticEvid_id;

			exec {$proc}
				@EvnForensicGeneticEvid_id = @Res output,
				@EvnForensicGeneticEvid_pid = :EvnForensicGenetic_id,
				@EvnForensicGeneticEvid_AccDocNum = :EvnForensicGeneticEvid_AccDocNum,
				@EvnForensicGeneticEvid_AccDocDate = :EvnForensicGeneticEvid_AccDocDate,
				@EvnForensicGeneticEvid_AccDocNumSheets = :EvnForensicGeneticEvid_AccDocNumSheets,
				@Org_id = :Org_id,
				@EvnForensicGeneticEvid_Facts = :EvnForensicGeneticEvid_Facts,
				@EvnForensicGeneticEvid_Goal = :EvnForensicGeneticEvid_Goal,
				@pmUser_id = :pmUser_id,
				@Lpu_id = :Lpu_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticEvid_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		
		$result = $this->queryResult($query,$queryParams);
		//Сохраняем потерпевших/обвиняемых
				
		foreach ($queryParams['Person'] as $person) {
			$person['EvnForensicGeneticEvid_id'] = $result[0]['EvnForensicGeneticEvid_id'];
			$person['pmUser_id'] = $queryParams['pmUser_id'];
			$saveEvnForensicGeneticEvidLinkResult = $this->_saveEvnForensicGeneticEvidLink($person);
			if (!$this->isSuccessful($saveEvnForensicGeneticEvidLinkResult)) {
				return $saveEvnForensicGeneticEvidLinkResult;
			}
		}

		//Сохраняем вещественные доказательства
		foreach ($queryParams['Evidence'] as $evidence) {
			$evidence = array_merge($evidence,array(
				'EvnForensic_id'=>$result[0]['EvnForensicGeneticEvid_id'],
				'pmUser_id'=>$queryParams['pmUser_id'],
				'EvidenceType_id'=>2
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
	 * для службы судебно биологической экспертизы
	 * @param type $data
	 */
	protected function getEvnForensicGeneticEvidJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGeneticEvid_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			SELECT
				EFGE.EvnForensicGenetic_id,
				EFGE.EvnForensicGeneticEvid_id,
				
				EFGE.EvnForensicGeneticEvid_AccDocDate,--дата основного сопроводительного документа
				EFGE.EvnForensicGeneticEvid_AccDocNum,--номер основного сопроводительного документа
				EFGE.EvnForensicGeneticEvid_AccDocNumSheets,--количество  листов документов
				EFGE.Org_id,--учреждение направившего
				EFGE.EvnForensicGeneticEvid_Facts,--Кратко обстоятельства дела
				EFGE.EvnForensicGeneticEvid_Goal,--цель экспертизы

				EFGE.Lpu_id
			FROM
				v_EvnForensicGeneticEvid EFGE with (nolock)
			WHERE
				EFGE.EvnForensicGeneticEvid_id = :EvnForensicGeneticEvid_id
			';

		return $this->queryResult($query, $queryParams);
	}
	
	/**
	 * Получение списка заявок службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicGeneticList($data) {
		
		$data['MedService_id'] = (empty($data['MedService_id'])) 
				?((empty($data['session']['CurMedService_id'])) 
					? null 
					: $data['session']['CurMedService_id']) 
			: $data['MedService_id'];
		
		if (empty($data['MedService_id'])) {
			if (empty($data['session']['CurMedService_id'])) {
				return $this->createError('','Не задан обязательный параметр: Идентификатор службы');
			} else {
				$data['MedService_id'] = $data['session']['CurMedService_id'];
			}
		}
		
		if (empty($data['MedPersonal_id']) && !empty($data['session']['medpersonal_id'])) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		
		$rules = array(
			array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
			array('field'=>'ARMType', 'label'=>'Тип АРМ','rules' => '', 'type' => 'string'),
			array('field'=>'MedPersonal_id', 'label'=>'Идентификатор эксперта','rules' => '', 'type' => 'id'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'ISNULL(EFG.EvnClass_id,0)=122',
			'EFG.MedService_id=:MedService_id',
		);
		if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
			if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}
		
		if ($queryParams['ARMType'] == 'expert') {
			if (empty($queryParams['MedPersonal_id'])) {
				return $this->createError('','Не задан обязательны параметр: Идентификатор эксперта');
			}
			$where[] = "EDF.MedPersonal_id=:MedPersonal_id";
		}
		
		$query = '
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as EvnForensic_Num,
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				convert(varchar(20), EFG.EvnForensicGenetic_insDT, 103) as Evn_insDT,
				ISNULL(MP.Person_Fin,\'Не назначен\') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,\'Не определён\') as EvnForensicType_Name,
				\'Группа лиц\' as Person_Fio,
				ES.EvnStatus_SysNick,
				ISNULL(AVF.ActVersionForensic_id,0) AS ActVersionForensic_id
			-- end select
			FROM
			-- from
				v_EvnForensicGenetic EFG with (nolock)
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFG.EvnForensicGenetic_id
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				OUTER APPLY (
                	SELECT TOP 1
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP with (nolock)
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                ) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				LEFT JOIN v_EvnStatus ES with (nolock) ON( ES.EvnStatus_id=EFG.EvnStatus_id )
			-- end from
			WHERE 
			-- where
				'.implode( " AND ", $where ).'
			-- end where	
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
			';

		$result = array();

		$count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount']=$count_result[0]['cnt'];
		}
		
		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data']=$data_result;
		}
		
		return $result;
	}
	/**
	 * Получение списка заявок журнала регистрации вещественных доказательств и документов к ним в лаборатории в службе СБЭ
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicGeneticEvidList($data) {
		
		$data['MedService_id'] = (empty($data['MedService_id'])) 
				?((empty($data['session']['CurMedService_id'])) 
					? null 
					: $data['session']['CurMedService_id']) 
			: $data['MedService_id'];
		
		$rules = array(
			array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'EFG.MedService_id=:MedService_id',
		);
		if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
			if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}
		
		$query = '
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as EvnForensic_Num, 
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				convert(varchar(20), EFGE.EvnForensicGeneticEvid_insDT, 103) as Evn_insDT,
				ISNULL(MP.Person_Fin,\'Не назначен\') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,\'Не определён\') as EvnForensicType_Name,				
				ES.EvnStatus_SysNick,
				CASE WHEN (PEC.PersonEvidCount>1) THEN \'Группа лиц\' ELSE
					CASE WHEN (PEC.PersonEvidCount=1) THEN PersonEvid.Person_Fio 
					ELSE \'Лицо отсутствует\' END
				END as Person_Fio,
				ISNULL(AVF.ActVersionForensic_id,0) AS ActVersionForensic_id 
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticEvid EFGE with (nolock)
				left join v_EvnForensicGenetic EFG with (nolock) on EFG.EvnForensicGenetic_id = EFGE.EvnForensicGeneticEvid_pid
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFGE.EvnForensicGeneticEvid_id
				LEFT JOIN v_EvnStatus ES with (nolock) ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				OUTER APPLY (
                	SELECT TOP 1
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP with (nolock)
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                ) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				OUTER APPLY (
					SELECT
						COUNT(EFGEL.Person_id) as PersonEvidCount
					FROM
						v_EvnForensicGeneticEvidLink EFGEL with (nolock)
					WHERE
						EFGEL.EvnForensicGeneticEvid_id = EFGE.EvnForensicGeneticEvid_id
				) as PEC
				OUTER APPLY (
					SELECT
						TOP 1
						EFGEL.Person_id,
						ISNULL(P.Person_SurName, \'\') + CAST(ISNULL(\' \' + P.Person_FirName, \'\') as varchar(2)) + CAST(ISNULL(\' \' + P.Person_SecName, \'\')as varchar(2)) AS Person_Fio
					FROM
						v_EvnForensicGeneticEvidLink EFGEL with (nolock)
						left join v_Person_all P with (nolock) on EFGEL.Person_id = P.Person_id
					WHERE
						EFGEL.EvnForensicGeneticEvid_id = EFGE.EvnForensicGeneticEvid_id
				) as PersonEvid
			-- end from
			WHERE
			-- where
				 '.implode( " AND ", $where ).'
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
			';

		$result = array();
		
		$count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount']=$count_result[0]['cnt'];
		}
		
		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data']=$data_result;
		}
		
		return $result;
	}
	/**
	 * Получение списка заявок журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicGeneticSampleLiveList($data) {
		
		$data['MedService_id'] = (empty($data['MedService_id'])) 
				?((empty($data['session']['CurMedService_id'])) 
					? null 
					: $data['session']['CurMedService_id']) 
			: $data['MedService_id'];
		
		$rules = array(
			array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'EFG.MedService_id=:MedService_id',
		);
		if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
			if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}
		
		$query = '
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as EvnForensic_Num,
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				convert(varchar(20), EFGSL.EvnForensicGeneticSampleLive_insDT, 103) as Evn_insDT,
				ISNULL(MP.Person_Fin,\'Не назначен\') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,\'Не определён\') as EvnForensicType_Name,
				ISNULL(P.Person_SurName, \'\') + CAST(ISNULL(\' \' + P.Person_FirName, \'\') as varchar(2)) + CAST(ISNULL(\' \' + P.Person_SecName, \'\')as varchar(2)) AS Person_Fio,
				ES.EvnStatus_SysNick,
				ISNULL(AVF.ActVersionForensic_id,0) AS ActVersionForensic_id 
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticSampleLive EFGSL with (nolock)
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFGSL.EvnForensicGeneticSampleLive_id
				left join v_EvnForensicGenetic EFG with (nolock) on EFG.EvnForensicGenetic_id = EFGSL.EvnForensicGeneticSampleLive_pid
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				LEFT JOIN v_EvnStatus ES with (nolock) ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				OUTER APPLY (
                	SELECT TOP 1
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP with (nolock)
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                ) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P with (nolock) on EFGSL.PersonEvn_id = P.PersonEvn_id and EFGSL.Server_id = P.Server_id
			-- end from
			WHERE
			-- where
				 '.implode( " AND ", $where ).'
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
			';
		
		$result = array();
		
		$count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount']=$count_result[0]['cnt'];
		}
		
		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data']=$data_result;
		}
		
		return $result;
	}
	/**
	 * Получение списка заявок журнала регистрации трупной крови в лаборатории
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicGeneticCadBloodList($data) {
		$data['MedService_id'] = (empty($data['MedService_id'])) 
				?((empty($data['session']['CurMedService_id'])) 
					? null 
					: $data['session']['CurMedService_id']) 
			: $data['MedService_id'];
		
		$rules = array(
			array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'EFG.MedService_id=:MedService_id',
		);
		if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
			if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}
		
		$query = '
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as EvnForensic_Num,
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				convert(varchar(20), EFGSL.EvnForensicGeneticCadBlood_insDT, 103) as Evn_insDT,
				ISNULL(MP.Person_Fin,\'Не назначен\') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,\'Не определён\') as EvnForensicType_Name,
				ISNULL(P.Person_SurName, \'\') + CAST(ISNULL(\' \' + P.Person_FirName, \'\') as varchar(2)) + CAST(ISNULL(\' \' + P.Person_SecName, \'\')as varchar(2)) AS Person_Fio,
				ES.EvnStatus_SysNick,
				ISNULL(AVF.ActVersionForensic_id,0) AS ActVersionForensic_id 
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticCadBlood EFGSL with (nolock)
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFGSL.EvnForensicGeneticCadBlood_id
				left join v_EvnForensicGenetic EFG with (nolock) on EFG.EvnForensicGenetic_id = EFGSL.EvnForensicGeneticCadBlood_pid
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				LEFT JOIN v_EvnStatus ES with (nolock) ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				OUTER APPLY (
                	SELECT TOP 1
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP with (nolock)
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                ) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P with (nolock) on EFGSL.PersonEvn_id = P.PersonEvn_id AND EFGSL.Server_id = P.Server_id
			-- end from
			WHERE
			-- where
				'.implode( " AND ", $where ).'
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
			';
		
		$result = array();
		
		$count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount']=$count_result[0]['cnt'];
		}
		
		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data']=$data_result;
		}
		
		return $result;
	}
	
	/**
	 * Получение списка заявок журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicGeneticGenLiveList($data) {
		
		$data['MedService_id'] = (empty($data['MedService_id'])) 
				?((empty($data['session']['CurMedService_id'])) 
					? null 
					: $data['session']['CurMedService_id']) 
			: $data['MedService_id'];
		
		$rules = array(
			array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'EFG.MedService_id=:MedService_id',
		);
		if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
			if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}
		
		$query = '
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as EvnForensic_Num,
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				convert(varchar(20), EFGGL.EvnForensicGeneticGenLive_insDT, 103) as Evn_insDT,
				ISNULL(MP.Person_Fin,\'Не назначен\') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,\'Не определён\') as EvnForensicType_Name,
				ISNULL(AVF.ActVersionForensic_id,0) AS ActVersionForensic_id,
				ES.EvnStatus_SysNick,
				CASE WHEN (PEC.PersonEvidCount>1) THEN \'Группа лиц\' ELSE
					CASE WHEN (PEC.PersonEvidCount=1) THEN Person.Person_Fio 
					ELSE \'Лицо отсутствует\' END
				END as Person_Fio
				
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticGenLive EFGGL with (nolock)
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFGGL.EvnForensicGeneticGenLive_id
				OUTER APPLY (
                	SELECT TOP 1
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP with (nolock)
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                ) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicGenetic EFG with (nolock) on EFG.EvnForensicGenetic_id = EFGGL.EvnForensicGeneticGenLive_pid
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				LEFT JOIN v_EvnStatus ES with (nolock) ON( ES.EvnStatus_id=EFG.EvnStatus_id )
				OUTER APPLY (
					SELECT
						COUNT(EFGGLL.Person_id) as PersonEvidCount
					FROM
						v_EvnForensicGeneticGenLiveLink EFGGLL with (nolock)
					WHERE
						EFGGLL.EvnForensicGeneticGenLive_id = EFGGL.EvnForensicGeneticGenLive_id
				) as PEC
				OUTER APPLY (
					SELECT TOP 1
						EFGGLL.Person_id,
						ISNULL(P.Person_SurName, \'\') + CAST(ISNULL(\' \' + P.Person_FirName, \'\') as varchar(2)) + CAST(ISNULL(\' \' + P.Person_SecName, \'\')as varchar(2)) AS Person_Fio
					FROM
						v_EvnForensicGeneticGenLiveLink EFGGLL with (nolock)
						left join v_Person_all P with (nolock) on EFGGLL.Person_id = P.Person_id
					WHERE
						EFGGLL.EvnForensicGeneticGenLive_id = EFGGL.EvnForensicGeneticGenLive_id
				) as Person
			-- end from
			WHERE
			-- where
				'.implode( " AND ", $where ).'
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
			';
		
		$result = array();
		
		$count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount']=$count_result[0]['cnt'];
		}
		
		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data']=$data_result;
		}
		
		return $result;
	}
	/**
	 * Получение списка заявок журнала регистрации исследований мазков и тампонов в лаборатории
	 * @param null $data
	 * @return boolean
	 */
	public function getEvnForensicGeneticSmeSwabList($data) {
		
		$data['MedService_id'] = (empty($data['MedService_id'])) 
				?((empty($data['session']['CurMedService_id'])) 
					? null 
					: $data['session']['CurMedService_id']) 
			: $data['MedService_id'];
		
		$rules = array(
			array('field'=>'MedService_id', 'rules'=>'required','label'=>'Идентификатор службы', 'type' => 'id'),
			array('field'=>'start', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>0),
			array('field'=>'limit', 'label'=>'','rules' => '', 'type' => 'int', 'default'=>10),
			array('field'=>'EvnStatus_SysNick', 'label'=>'Статус заявки','rules' => '', 'type' => 'string'),
		);
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;

		$where = array(
			'EFG.MedService_id=:MedService_id',
		);
		if ( isset( $queryParams['EvnStatus_SysNick'] ) && !empty( $queryParams['EvnStatus_SysNick'] ) && $queryParams['EvnStatus_SysNick'] != 'All' ) {
			if ( $queryParams['EvnStatus_SysNick'] == 'New' ) {
				$where[] = "(ES.EvnStatus_id IS NULL OR ES.EvnStatus_SysNick=:EvnStatus_SysNick)";
			} else {
				$where[] = "ES.EvnStatus_SysNick=:EvnStatus_SysNick";
			}
		}
		
		$query = '
			SELECT
			-- select
				EFG.EvnForensicGenetic_Num as EvnForensic_Num,
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				convert(varchar(20), EFGSS.EvnForensicGeneticSmeSwab_insDT, 103) as Evn_insDT,
				ISNULL(MP.Person_Fin,\'Не назначен\') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,\'Не определён\') as EvnForensicType_Name,
				ISNULL(P.Person_SurName, \'\') + CAST(ISNULL(\' \' + P.Person_FirName, \'\') as varchar(2)) + CAST(ISNULL(\' \' + P.Person_SecName, \'\')as varchar(2)) AS Person_Fio,
				ES.EvnStatus_SysNick,
				ISNULL(AVF.ActVersionForensic_id,0) AS ActVersionForensic_id 
			-- end select
			FROM
			-- from
				v_EvnForensicGeneticSmeSwab EFGSS with (nolock)
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFGSS.EvnForensicGeneticSmeSwab_id
				OUTER APPLY (
                	SELECT TOP 1
                    	MP.Person_Fin as Person_Fin
                    FROM
                    	v_MedPersonal MP with (nolock)
                    WHERE
                    	MP.MedPersonal_id = EDF.MedPersonal_id
                ) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_Person_all P with (nolock) on EFGSS.PersonEvn_id = P.PersonEvn_id and EFGSS.Server_id = P.Server_id
				left join v_EvnForensicGenetic EFG with (nolock) on EFG.EvnForensicGenetic_id = EFGSS.EvnForensicGeneticSmeSwab_pid
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				LEFT JOIN v_EvnStatus ES with (nolock) ON( ES.EvnStatus_id=EFG.EvnStatus_id )
			-- end from
			WHERE
			-- where
				'.implode( " AND ", $where ).'
			-- end where
			ORDER BY
			-- order by
				EFG.EvnForensicGenetic_Num DESC
			-- end order by
			';
		
		$result = array();
		
		$count_result = $this->queryResult(getCountSQLPH($query),$queryParams);
		if (!$this->isSuccessful($count_result)) {
			return $count_result;
		} else {
			$result['totalCount']=$count_result[0]['cnt'];
		}
		
		$data_result = $this->queryResult(getLimitSQLPH($query, $queryParams['start'], $queryParams['limit']),$queryParams);
		if (!$this->isSuccessful($data_result)) {
			return $data_result;
		} else {
			$result['data']=$data_result;
		}
		
		return $result;
	}
	
	/**
	 * Сохранение экспертизы в судебно-биологическом отделении с молекулярно-генетической лабораторией
	 * @param $data
	 * @return bool
	 */
	public function saveEvnForensicGeneticExpertiseProtocol($data) {
		/**
		 * Обработка значений чекбоксов
		 */
		function processCheckboxValues(&$params, $checkbox_list) {
			foreach($checkbox_list as $field) {
				if ($params[$field] === 0) {
					$params[$field] = 1;
				} else if ($params[$field] === 1) {
					$params[$field] = 2;
				} else {
					$params[$field] = null;
				}
			}
		};

		$data['EvnForensicGenetic_id'] = $data['EvnForensic_id'];

		$this->db->trans_begin();

		if (!empty($data['EvnForensicGeneticCadBlood_id']) && $data['EvnForensicGeneticCadBlood_id'] > 0) {
			processCheckboxValues($data, array(
				'EvnForensicGeneticCadBlood_IsIsosTestEA',
				'EvnForensicGeneticCadBlood_IsIsosTestEB',
				'EvnForensicGeneticCadBlood_IsIsosTestIsoB',
				'EvnForensicGeneticCadBlood_IsIsosTestIsoA',
				'EvnForensicGeneticCadBlood_IsIsosAntiA',
				'EvnForensicGeneticCadBlood_IsIsosAntiB',
				'EvnForensicGeneticCadBlood_IsIsosAntiH',
			));
			$saveCadBloodResult = $this->saveEvnForensicGeneticCadBlood($data);
			if (!$this->isSuccessful($saveCadBloodResult)) {
				$this->db->trans_rollback();
				return $saveCadBloodResult;
			}
		}
		if (!empty($data['EvnForensicGeneticSampleLive_id']) && $data['EvnForensicGeneticSampleLive_id'] > 0) {
			processCheckboxValues($data, array(
				'EvnForensicGeneticSampleLive_IsIsosTestEA',
				'EvnForensicGeneticSampleLive_IsIsosTestEB',
				'EvnForensicGeneticSampleLive_IsIsosCyclAntiA',
				'EvnForensicGeneticSampleLive_IsIsosCyclAntiB',
			));
			$saveSampleLiveResult = $this->_saveEvnForensicGeneticSampleLiveJournal($data);
			if (!$this->isSuccessful($saveSampleLiveResult)) {
				$this->db->trans_rollback();
				return $saveSampleLiveResult;
			}
		}

		$saveActVersionForensicResult = $this->_saveActVersionForensic($data);
		if (!$this->isSuccessful($saveActVersionForensicResult)) {
			$this->db->trans_rollback();
		} else {
			$this->db->trans_commit();
		}

		return $saveActVersionForensicResult;
	}

	/**
	 * Функция получения записи журнала регистрации трупной крови
	 * @param type $data
	 * @return type
	 */
	protected function getEvnForensicGeneticCadBloodJournal($data) {
		$rules = array(
			array('field' => 'EvnForensicGeneticCadBlood_id', 'label' => 'Идентификатор заявки', 'rules' => 'required', 'type' => 'id'),
		);

		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = '
			SELECT
				EFGCB.EvnForensicGeneticCadBlood_id,
				EFGCB.EvnForensicGeneticCadBlood_pid,
				EFGCB.EvnForensicGeneticCadBlood_rid,
				EFGCB.MedPersonal_id,
				EFGCB.EvnForensicGeneticCadBlood_TakeDate,
				EFGCB.EvnForensicGeneticCadBlood_ForDate,
				EFGCB.EvnForensicGeneticCadBlood_StudyDate,
				EFGCB.EvnForensicGeneticCadBlood_Result,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEA,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEB,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoB,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoA,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiA,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiB,
				EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiH,
				EFGCB.EvnForensicGeneticCadBlood_MatCondition,
				EFGCB.EvnForensicGeneticCadBlood_IsosOtherSystems,
				EFGCB.Lpu_id,
				EFGCB.PersonEvn_id,
				EFGCB.Server_id
			FROM
				v_EvnForensicGeneticCadBlood EFGCB with (nolock)
			WHERE
				EFGCB.EvnForensicGeneticCadBlood_id = :EvnForensicGeneticCadBlood_id
			';
		
		return $this->queryResult($query, $queryParams);
	}
	
	/**
	 * Сохранение записи в журнале регистрации трупной крови
	 * @param type $data
	 */
	public function saveEvnForensicGeneticCadBlood($data) {
		
		if (empty($data['MedPersonal_id']) || empty($data['EvnForensicGeneticCadBlood_id'])) {
			if (empty($data['session']['medpersonal_id'])) {
				return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор назначившего эксперта'));
			} else {
				$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			}
		}
		
		$rules = array(
			array('field' => 'EvnForensicGenetic_id', 'label' => 'Идентификатор родительской заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_id', 'label' => 'Идентификатор заявки', 'rules' => '', 'type' => 'id'),
			array('field' => 'Evn_rid', 'label'=>'Идентификатор получателя документа', 'rules' => '',  'type' => 'id'),
			array('field' => 'MedPersonal_id', 'label' => 'Cотрудник назначивший экспертизу', 'rules' => '', 'type' => 'id'),
			array('field' => 'ReasearchedPerson_id', 'label' => 'Исследуемое лицо', 'rules' => '', 'type' => 'id'),
			//array('field' => 'PersonEvn_id', 'label' => 'Идентификатор состояния', 'rules' => '', 'type' => 'id'),
			//array('field' => 'Server_id', 'label' => 'Идентификатор сервера', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_TakeDate', 'label' => 'Дата взятия', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_ForDate', 'label' => 'Дата поступления', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_StudyDate', 'label' => 'Дата исследования', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_Result', 'label' => 'Результат определения групп по исследованным системам', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestEA', 'label' => 'Тест-эритроцит А', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestEB', 'label' => '', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestIsoB', 'label' => 'Изосыворотка бетта', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosTestIsoA', 'label' => 'Изосыворотка альфа', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiA', 'label' => 'Имунная сыворотка Анти-А', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiB', 'label' => 'Имунная сыворотка Анти-B', 'rules' => '', 'type' => 'id'),
			array('field' => 'EvnForensicGeneticCadBlood_IsIsosAntiH', 'label' => 'Имунная сыворотка Анти-H', 'rules' => '', 'type' => 'id'),
			
			array('field' => 'EvnForensicGeneticCadBlood_MatCondition', 'label' => 'Упаковка, состояние, количество материала', 'rules' => '', 'type' => 'string'),
			array('field' => 'EvnForensicGeneticCadBlood_IsosOtherSystems', 'label' => 'Другие системы (изосерология)', 'rules' => '', 'type' => 'string'),
			
			array('field'=>'Lpu_id','rules' =>'required', 'label'=>'Идентификатор МО', 'type' => 'id'),
			array('field'=>'pmUser_id','rules' =>'required', 'label'=>'Идентификатор пользователя', 'type' => 'id'),
		);
		
		
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$proc = 'p_EvnForensicGeneticCadBlood_ins';
		if (!empty($queryParams['EvnForensicGeneticCadBlood_id'])) {
			$proc = 'p_EvnForensicGeneticCadBlood_upd';
			$currentDataResult = $this->getEvnForensicGeneticCadBloodJournal($queryParams);
			if (!$this->isSuccessful($currentDataResult)) {
				return $currentDataResult;
			}
			//Те поля, что не переданы, восполняем из последней сохраненной записи
			foreach ($queryParams as $key => $value) {
				$queryParams[$key] = (($value == null)&&(!empty($currentDataResult[0]["$key"])))?$currentDataResult[0]["$key"]:$value;
			}
		} else {
			
			if (empty($queryParams['PersonEvn_id']) || empty($queryParams['Server_id'])) {
				//Если PersonEvn_id и Server_id не переданы, должен быть передан ReasearchedPerson_id - идентификатор исследуемого лица
				if (empty($queryParams['ReasearchedPerson_id'])) {
					return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Исследуемое лицо'));
				} else {
					$personState = $this->_getPersonStateByPersonId(array('Person_id'=>$queryParams['ReasearchedPerson_id']));
					if (!$this->isSuccessful($personState) || sizeof($personState)==0) {
						return $this->createError('', 'Ошибка получения идентификатора состояния');
					} else {
						$queryParams = array_merge($queryParams,$personState[0]);
					}
				}
			}
			
		}
		
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000)
				
			set @Res = :EvnForensicGeneticCadBlood_id;

			exec {$proc}
				@EvnForensicGeneticCadBlood_id = @Res output,
				@EvnForensicGeneticCadBlood_pid = :EvnForensicGenetic_id,
				@EvnForensicGeneticCadBlood_rid = :Evn_rid,
				
				@MedPersonal_id=:MedPersonal_id,
				@EvnForensicGeneticCadBlood_MatCondition =:EvnForensicGeneticCadBlood_MatCondition,
				@EvnForensicGeneticCadBlood_IsosOtherSystems =:EvnForensicGeneticCadBlood_IsosOtherSystems,
				@EvnForensicGeneticCadBlood_TakeDate=:EvnForensicGeneticCadBlood_TakeDate,
				@EvnForensicGeneticCadBlood_StudyDate=:EvnForensicGeneticCadBlood_StudyDate,
				@EvnForensicGeneticCadBlood_ForDate=:EvnForensicGeneticCadBlood_ForDate,
				@EvnForensicGeneticCadBlood_Result= :EvnForensicGeneticCadBlood_Result,
				@EvnForensicGeneticCadBlood_IsIsosTestEA=:EvnForensicGeneticCadBlood_IsIsosTestEA,
				@EvnForensicGeneticCadBlood_IsIsosTestEB=:EvnForensicGeneticCadBlood_IsIsosTestEB,
				@EvnForensicGeneticCadBlood_IsIsosTestIsoB=:EvnForensicGeneticCadBlood_IsIsosTestIsoB,
				@EvnForensicGeneticCadBlood_IsIsosTestIsoA=:EvnForensicGeneticCadBlood_IsIsosTestIsoA,
				@EvnForensicGeneticCadBlood_IsIsosAntiA=:EvnForensicGeneticCadBlood_IsIsosAntiA,
				@EvnForensicGeneticCadBlood_IsIsosAntiB=:EvnForensicGeneticCadBlood_IsIsosAntiB,
				@EvnForensicGeneticCadBlood_IsIsosAntiH=:EvnForensicGeneticCadBlood_IsIsosAntiH,
				
				@Lpu_id = :Lpu_id,
				@PersonEvn_id = :PersonEvn_id,
				@Server_id = :Server_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as EvnForensicGeneticCadBlood_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		return $this->queryResult($query, $queryParams);
	}
	
	/**
	 * Функция получения заявки службы судебно-биологической экспертизы с молекулярно-генетической лабораторией для просмотра
	 * @return boolean
	 */
	public function getEvnForensicGeneticRequest($data) {
		$rules = array(
			array('field' => 'EvnForensic_id', 'label' => 'Идентификатор случая', 'rules' => 'required', 'type' => 'id'),
		);
		$queryParams = $this->_checkInputData($rules, $data, $err, false);
		if (!$queryParams) return $err;
		
		$query = "
			SELECT
				-- Общий блок

				EFG.EvnForensicGenetic_Num as EvnForensic_Num,
				EFG.EvnForensicGenetic_id as EvnForensic_id,
				ISNULL(EDF.EvnDirectionForensic_id,0) as EvnDirectionForensic_id,
				convert(varchar(20), EFG.EvnForensicGenetic_insDT, 104) as EvnForensic_insDT,
				ISNULL(MP.Person_Fin,'Не назначен') as Expert_Fin,
				ISNULL(EFT.EvnForensicType_Name,'Не определён') as EvnForensicType_Name,

				-- Журнал регистрации вещественных доказательств
				ISNULL(EFGE.EvnForensicGeneticEvid_id,0) as EvnForensicGeneticEvid_id,
				EFGE.EvnForensicGeneticEvid_AccDocNum,
				convert(varchar(20), EFGE.EvnForensicGeneticEvid_AccDocDate, 104) as EvnForensicGeneticEvid_AccDocDate,
				EFGE.EvnForensicGeneticEvid_AccDocNumSheets,
				O.Org_Name,
				EFGE.EvnForensicGeneticEvid_Facts,
				EFGE.EvnForensicGeneticEvid_Goal,

				-- Журнал регистрации биообразцов
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_id,0) as EvnForensicGeneticSampleLive_id,
				convert(varchar(10), EFGSL.EvnForensicGeneticSampleLive_TakeDate, 104) + ' ' + convert(varchar(5), EFGSL.EvnForensicGeneticSampleLive_TakeDate, 108) as EvnForensicGeneticSampleLive_TakeDate,
				EFGSL_P.Person_Fio as EvnForensicGeneticSampleLive_Person_FIO,
				EFGSL.EvnForensicGeneticSampleLive_Basis,
				EFGSL_MP.Person_Fin as EvnForensicGeneticSampleLive_MedPersonal_Fin,--фИО работника изъявшего
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_IsConsent,0) as EvnForensicGeneticSampleLive_IsConsent,--согласие
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_IsIsosTestEA-1,0) as EvnForensicGeneticSampleLive_IsIsosTestEA,--Тест-эритроцит А
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_IsIsosTestEB-1,0) as EvnForensicGeneticSampleLive_IsIsosTestEB,--Тест-эритроцит B
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiA-1,0) as EvnForensicGeneticSampleLive_IsIsosCyclAntiA,--Циклон Анти-А
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_IsIsosCyclAntiB-1,0) as EvnFoEvnForensicGeneticSampleLive_ResultrensicGeneticSampleLive_IsIsosCyclAntiB,--Циклон Анти-B
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_VerifyingDoc,'') as EvnForensicGeneticSampleLive_VerifyingDoc,
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_IsosOtherSystems,'') as EvnForensicGeneticSampleLive_IsosOtherSystems,
				ISNULL(EFGSL.EvnForensicGeneticSampleLive_Result,'') as EvnForensicGeneticSampleLive_Result,

				-- Журнал регистрации биообразцов для мол.ген. иссл
				ISNULL(EFGGL.EvnForensicGeneticGenLive_id,0) as EvnForensicGeneticGenLive_id ,
				convert(varchar(10), EFGGL.EvnForensicGeneticGenLive_TakeDate, 104) as EvnForensicGeneticGenLive_TakeDate,
				EFGGL.EvnForensicGeneticGenLive_Facts,
				EFGGL_MP.Person_Fin as EvnForensicGeneticGenLive_MedPersonal_Fin,

				--Журнал регистрации трупной крови в лаборатории
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_id,0) as EvnForensicGeneticCadBlood_id ,
				EFGCB_P.Person_Fio as EvnForensicGeneticCadBlood_Person_Fin, --исследуемое лицо
				EFGCB_MP.Person_Fin as EvnForensicGeneticCadBlood_MedPersonal_Fin, --EFGCB.MedPersonal_id,--фио эксперта направившего
				convert(varchar(20), EFGCB.EvnForensicGeneticCadBlood_ForDate, 104) as EvnForensicGeneticCadBlood_ForDate,--Дата поступления
				convert(varchar(20), EFGCB.EvnForensicGeneticCadBlood_TakeDate, 104) as EvnForensicGeneticCadBlood_TakeDate,--дата взятия
				convert(varchar(20), EFGCB.EvnForensicGeneticCadBlood_StudyDate, 104) as EvnForensicGeneticCadBlood_StudyDate,--дата исследования
				EFGCB.EvnForensicGeneticCadBlood_Result,--Результат определения групп по исследованным системам
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEA-1,0) as EvnForensicGeneticCadBlood_IsIsosTestEA,--Тест-эритроцит А
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestEB-1,0) as EvnForensicGeneticCadBlood_IsIsosTestEB,--Тест-эритроцит B
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoB-1,0) as EvnForensicGeneticCadBlood_IsIsosTestIsoB,--Изосыворотка бетта
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosTestIsoA-1,0) as EvnForensicGeneticCadBlood_IsIsosTestIsoA,--Изосыворотка альфа
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiA-1,0) as EvnForensicGeneticCadBlood_IsIsosAntiA,--Имунная сыворотка Анти-А
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiB-1,0) as EvnForensicGeneticCadBlood_IsIsosAntiB,--Имунная сыворотка Анти-B
				ISNULL(EFGCB.EvnForensicGeneticCadBlood_IsIsosAntiH-1,0) as EvnForensicGeneticCadBlood_IsIsosAntiH,--Имунная сыворотка Анти-H
				EFGCB.EvnForensicGeneticCadBlood_MatCondition, --Упаковка, состояние, количество материала
				EFGCB.EvnForensicGeneticCadBlood_IsosOtherSystems, --Другие системы (изосерология)

				
				-- Журнал регистрации мазков и тампонов
				ISNULL(EFGSS.EvnForensicGeneticSmeSwab_id,0) as EvnForensicGeneticSmeSwab_id ,
				EFGSS_P.Person_Fio as EvnForensicGeneticSmeSwab_Person_Fio,
				EFGSS.EvnForensicGeneticSmeSwab_Basis,
				convert(varchar(10), EFGSS.EvnForensicGeneticSmeSwab_DelivDate, 104) + ' ' + convert(varchar(5), EFGSS.EvnForensicGeneticSmeSwab_DelivDate, 108) as EvnForensicGeneticSmeSwab_DelivDate,
				convert(varchar(20), EFGSS.EvnForensicGeneticSmeSwab_BegDate, 104) as EvnForensicGeneticSmeSwab_BegDate,--дата начала исследования
				convert(varchar(20), EFGSS.EvnForensicGeneticSmeSwab_EndDate, 104) as EvnForensicGeneticSmeSwab_EndDate,--дата окончания исследования
				EFGSS.EvnForensicGeneticSmeSwab_Comment, --примечание
								
				-- Комментарий, если есть
				ISNULL(ESH.EvnStatusHistory_Cause,'') as EvnStatusHistory_Cause,
				
				-- Акт экспертизы
				ISNULL(AVF.ActVersionForensic_id,0) as ActVersionForensic_id ,
				AVF.ActVersionForensic_Text,
				AVF.ActVersionForensic_Num,
				convert(varchar(10), AVF.ActVersionForensic_FactBegDT, 104) as ActVersionForensic_FactBegDT,
				convert(varchar(10), AVF.ActVersionForensic_FactEndDT, 104) as ActVersionForensic_FactEndDT

			FROM
				v_EvnForensicGenetic EFG with (nolock)
				left join v_EvnDirectionForensic EDF with (nolock) on EDF.EvnForensic_id = EFG.EvnForensicGenetic_id
				OUTER APPLY (
					SELECT TOP 1
						MP.Person_Fin as Person_Fin
					FROM
						v_MedPersonal MP with (nolock)
					WHERE
						MP.MedPersonal_id = EDF.MedPersonal_id
				) as MP
				left join v_EvnForensicType EFT with (nolock) on EDF.EvnForensicType_id = EFT.EvnForensicType_id
				left join v_EvnForensicGeneticEvid EFGE with (nolock) on EFGE.EvnForensicGeneticEvid_pid = EFG.EvnForensicGenetic_id
				left join v_Org O with (nolock) on EFGE.Org_id = O.Org_id
				left join v_EvnForensicGeneticCadBlood EFGCB with (nolock) on EFGCB.EvnForensicGeneticCadBlood_pid = EFG.EvnForensicGenetic_id
				left join v_Person_all EFGCB_P with (nolock) on EFGCB.PersonEvn_id = EFGCB_P.PersonEvn_id AND EFGCB.Server_id = EFGCB_P.Server_id
				OUTER APPLY (
					SELECT TOP 1
						MP.Person_Fin
					FROM
						v_MedPersonal MP with (nolock)
					WHERE
						MP.MedPersonal_id = EFGCB.MedPersonal_id
				) as EFGCB_MP
				left join v_EvnForensicGeneticGenLive  EFGGL with (nolock) on EFGGL.EvnForensicGeneticGenLive_pid = EFG.EvnForensicGenetic_id
				OUTER APPLY (
					SELECT TOP 1
						MP.Person_Fin
					FROM
						v_MedPersonal MP with (nolock)
					WHERE
						MP.MedPersonal_id = EFGGL.MedPersonal_id
				) as EFGGL_MP
				left join v_EvnForensicGeneticSampleLive  EFGSL with (nolock) on EFGSL.EvnForensicGeneticSampleLive_pid = EFG.EvnForensicGenetic_id
				OUTER APPLY (
					SELECT TOP 1
						MP.Person_Fin
					FROM
						v_MedPersonal MP with (nolock)
					WHERE
						MP.MedPersonal_id = EFGSL.MedPersonal_id
				) as EFGSL_MP
				left join v_Person_all EFGSL_P with (nolock) on EFGSL.PersonEvn_id = EFGSL_P.PersonEvn_id AND EFGSL.Server_id = EFGSL_P.Server_id
				left join v_EvnForensicGeneticSmeSwab  EFGSS with (nolock) on EFGSS.EvnForensicGeneticSmeSwab_pid = EFG.EvnForensicGenetic_id
				left join v_Person_all EFGSS_P with (nolock) on EFGSS.PersonEvn_id = EFGSS_P.PersonEvn_id AND EFGSS.Server_id = EFGSS_P.Server_id
				outer apply (
					SELECT TOP 1
						AVF.ActVersionForensic_id,
						AVF.ActVersionForensic_Num,
						AVF.ActVersionForensic_Text,
						AVF.ActVersionForensic_FactBegDT,
						AVF.ActVersionForensic_FactEndDT
					FROM
						v_ActVersionForensic AVF with (nolock)
					WHERE
						AVF.EvnForensic_id = EFG.EvnForensicGenetic_id
					ORDER BY
						AVF.ActVersionForensic_insDT DESC
				) as AVF
				OUTER APPLY (
					SELECT TOP 1
						ESH.EvnStatusHistory_Cause
					FROM
						v_EvnStatusHistory ESH with (nolock)
					WHERE
						ESH.Evn_id = EFG.EvnForensicGenetic_id
					ORDER BY
						ESH.EvnStatusHistory_insDT DESC
				) as ESH
			WHERE 
				EFG.EvnForensicGenetic_id = :EvnForensic_id
			";
		//echo getDebugSQL($query, $queryParams);exit;
		$result = $this->queryResult($query, $queryParams);
		if (!$this->isSuccessful($result)) {
			return $result;
		}
		

		if (!empty($result[0])) {
			if (!empty($result[0]['EvnForensicGeneticEvid_id'])) {
				//
				// Получаем список потерпевших/обвиняемых
				//
				$evid_link_result = $this->getEvnForensicGeneticEvidLinkList(array(
					'EvnForensicGeneticEvid_id' => $result[0]['EvnForensicGeneticEvid_id']
				));
				
				if (!$this->isSuccessful($evid_link_result)) {
					return $evid_link_result;
				} else {
					$result[0]['EvnForensicGeneticEvidLink'] = $evid_link_result;
				}
				//
				// Получаем список вещдоков
				//
				$evidence_result = $this->getEvidenceList(array(
					'EvnForensic_id' => $result[0]['EvnForensicGeneticEvid_id']
				));
				
				if (!$this->isSuccessful($evidence_result)) {
					return $evidence_result;
				} else {
					$result[0]['EvnForensicGeneticEvid_Evidence'] = $evidence_result;
				}
			}
			if (!empty($result[0]['EvnForensicGeneticSampleLive_id'])) {
				//
				// Получаем список биологических образцов
				//
				$evidence_result = $this->getEvidenceList(array(
					'EvnForensic_id' => $result[0]['EvnForensicGeneticSampleLive_id']
				));
				if (!$this->isSuccessful($evidence_result)) {
					return $evidence_result;
				} else {
					$result[0]['EvnForensicGeneticSampleLive_BioSample'] = $evidence_result;
				}
			}
			if (!empty($result[0]['EvnForensicGeneticGenLive_id'])) {
				//
				// Получаем список исследуемых лиц
				//
				$genetic_gen_live_result = $this->getEvnForensicGeneticGenLiveLinkList(array(
					'EvnForensicGeneticGenLive_id' => $result[0]['EvnForensicGeneticGenLive_id']
				));
				if (!$this->isSuccessful($genetic_gen_live_result)) {
					return $genetic_gen_live_result;
				} else {
					$result[0]['EvnForensicGeneticGenLiveLink'] = $genetic_gen_live_result;
				}
				//
				// Получаем список биологических образцов
				//
				$biosample_result = $this->getEvidenceList(array(
					'EvnForensic_id' => $result[0]['EvnForensicGeneticGenLive_id']
				));
				if (!$this->isSuccessful($biosample_result)) {
					return $biosample_result;
				} else {
					$result[0]['EvnForensicGeneticGenLive_BioSample'] = $biosample_result;
				}
			}
			if (!empty($result[0]['EvnForensicGeneticSmeSwab_id'])) {
				//
				// Получаем список образцов
				//
				$biosample_result = $this->getEvidenceList(array(
					'EvnForensic_id' => $result[0]['EvnForensicGeneticSmeSwab_id']
				));
				if (!$this->isSuccessful($biosample_result)) {
					return $biosample_result;
				} else {
					$result[0]['EvnForensicGeneticSmeSwab_Sample'] = $biosample_result;
				}
			}
		
		}
		if (!empty($result[0])) {
				$result[0]['success']=true;
		}
		return $result;
	}
	/**
	 * Функция получения списка потерпевших/обвиняемых для журнала регистрации вещественных доказательств и документов к нимы
	 * службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
	 * @param type $data
	 * @return boolean
	 */
	protected function getEvnForensicGeneticEvidLinkList($data) {
		if (empty($data['EvnForensicGeneticEvid_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор случая'));
		}
		
		$query ='
			SELECT
				EFGEL.EvnForensicGeneticEvidLink_id,
				EFGEL.Person_id,
				EFGEL.EvnForensicGeneticEvidLink_IsVic,
				EFGEL_P.Person_Fio
			FROM 
				v_EvnForensicGeneticEvidLink EFGEL with (nolock)
				OUTER APPLY (
					SELECT TOP 1
					P.Person_Fio
					FROM
						v_Person_all P  with (nolock)
					WHERE P.Person_id = EFGEL.Person_id
					ORDER BY P.PersonEvn_id ASC
				) As EFGEL_P
			WHERE
				EFGEL.EvnForensicGeneticEvid_id = :EvnForensicGeneticEvid_id
			';
		$result = $this->db->query($query,array(
			'EvnForensicGeneticEvid_id' => $data['EvnForensicGeneticEvid_id']
		));
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}
	
	/**
	 * Функция получения списка исследуемых лиц для журнала регистрации биологических образцов, изъятых у живых лиц в лаборатории для молекулярно-генетического исследования
	 * службы судебно-биологической экспертизы с молекулярно-генетической лабораторией
	 * @param type $data
	 * @return boolean
	 */
	protected function getEvnForensicGeneticGenLiveLinkList($data) {
		
		if (empty($data['EvnForensicGeneticGenLive_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор случая'));
		}
		
		$query = '
			SELECT
				EFGGLL.EvnForensicGeneticGenLiveLink_id,
				EFGGLL.Person_id,
				EFGGLL_P.Person_Fio
			FROM 
				v_EvnForensicGeneticGenLiveLink EFGGLL with (nolock)
				OUTER APPLY (
					SELECT TOP 1
					P.Person_Fio
					FROM
						v_Person_all P with (nolock)
					WHERE P.Person_id = EFGGLL.Person_id
					ORDER BY P.PersonEvn_id ASC
				) As EFGGLL_P
			WHERE
				EFGGLL.EvnForensicGeneticGenLive_id = :EvnForensicGeneticGenLive_id
			';
		$result = $this->db->query($query,array(
			'EvnForensicGeneticGenLive_id' => $data['EvnForensicGeneticGenLive_id']
		));
		if (!is_object($result)) {
			return false;
		}
		
		return $result->result('array');
		
	}
}