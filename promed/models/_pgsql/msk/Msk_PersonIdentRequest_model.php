<?php

require_once APPPATH.'models/_pgsql/PersonIdentRequest_model.php';

/**
 * Class Msk_PersonIdentRequest_model
 * @property SwPersonIdentMsk $ident
 * @property Person_model $Person_model
 */
class Msk_PersonIdentRequest_model extends PersonIdentRequest_model {
	/**
	 * Msk_PersonIdentRequest_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Изменение статуса запроса на идентификацию человека
	 * @param array $data
	 * @return array
	 */
	function setPersonRequestDataStatus($data) {
		$params = array(
			'PersonRequestData_id' => $data['PersonRequestData_id'],
			'PersonRequestDataStatus_id' => $data['PersonRequestDataStatus_id'],
			'PersonRequestData_flcDT' => !empty($data['PersonRequestData_flcDT'])?$data['PersonRequestData_flcDT']:null,
			'PersonRequestData_csDT' => !empty($data['PersonRequestData_csDT'])?$data['PersonRequestData_csDT']:null,
			'PersonRequestData_Error' => !empty($data['PersonRequestData_Error'])?$data['PersonRequestData_Error']:null,
		);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from erz.p_PersonRequestData_status(
				PersonRequestData_id := :PersonRequestData_id,
				PersonRequestDataStatus_id := :PersonRequestDataStatus_id,
				PersonRequestData_flcDT := :PersonRequestData_flcDT,
				PersonRequestData_csDT := :PersonRequestData_csDT,
				PersonRequestData_Error := :PersonRequestData_Error
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('Ошибка при изменении статуса запроса на идентификацию пациента');
		}
		return $resp;
	}

	/**
	 * Изменение статуса идентификации человека
	 * @param array $data
	 * @return array
	 */
	function setPersonIsInErz($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Person_IsInErz' => $data['Person_IsInErz'],
			'PersonIdentState_id' => ($data['Person_IsInErz'] == 2)?1:2,
			'pmUser_id' => $data['pmUser_id']
		);
		if($params['PersonIdentState_id'] == 1){
			//при положительной идентификации полю Server_id присваивается 0
			$params['Server_id'] = 0;
		}
		$this->load->model('Person_model');
		return $this->Person_model->updatePerson($params);
	}
	
	/**
	 * @param array $data
	 * @return bool
	 * @throws Exception
	 */
	function deletePersonPolist($data){
		if(empty($data['PersonPolis_id']) || empty($data['Person_id']) || empty($data['pmUser_id'])){
			return false;
		}
		$params = array(
			'PersonPolis_id' => $data['PersonPolis_id'],
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from xp_PersonRemovePersonEvn(
				Person_id := :Person_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonPolis_id,
				pmUser_id := :pmUser_id
			)
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при удалении данных полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}
		return true;
	}

	/**
	 * Сохранение данных полиса
	 * @param array $data
	 * @return array
	 */
	function savePersonPolis($data) {
		$params = array(
			'PersonPolis_id' => !empty($data['PersonPolis_id'])?$data['PersonPolis_id']:null,
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'OMSSprTerr_id' => $data['OMSSprTerr_id'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'PolisType_id' => $data['PolisType_id'],
			'PolisFormType_id' => !empty($data['PolisFormType_id'])?$data['PolisFormType_id']:null,
			'Polis_Ser' => !empty($data['Polis_Ser'])?$data['Polis_Ser']:null,
			'Polis_Num' => $data['Polis_Num'],
			'Polis_begDate' => $data['Polis_begDate'],
			'PersonPolis_insDT' => $data['Polis_begDate'],
			'Polis_endDate' => !empty($data['Polis_endDate'])?$data['Polis_endDate']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['PersonPolis_id'])) {
			$procedure = 'p_PersonPolis_ins';
		} else {
			$procedure = 'p_PersonPolis_upd';
		}
		
		if (!empty($params['PersonPolis_id'])) {
			$cte = "
				select
					Server_id as srv_id
				from PersonPolis
				where PersonPolis_id = :PersonPolis_id
				limit 1
			";
		} else {
			$cte = "
				select
					:Server_id as srv_id
			";
		}
		
		$query = "
			with mv as (
				{$cte}
			)
			select
				PersonPolis_id as \"PersonPolis_id\",
				Polis_id as \"Polis_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				PersonPolis_id := :PersonPolis_id,
				Server_id := (select srv_id from mv),
				Person_id := :Person_id,
				OMSSprTerr_id := :OMSSprTerr_id,
				OrgSMO_id := :OrgSMO_id,
				PolisType_id := :PolisType_id,
				PolisFormType_id := :PolisFormType_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Polis_begDate := :Polis_begDate,
				PersonPolis_insDT := :PersonPolis_insDT,
				Polis_endDate := :Polis_endDate,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении данных полиса');
		}
		return $resp;
	}

	/**
	 * Сохренние номера ЕНП
	 * @param array $data
	 * @return array
	 */
	function savePersonPolisEdNum($data) {
		$params = array(
			'Server_id' => $data['Server_id'],
			'Person_id' => $data['Person_id'],
			'PersonPolisEdNum_insDT' => $data['PersonPolisEdNum_insDT'],
			'PersonPolisEdNum_EdNum' => $data['PersonPolisEdNum_EdNum'],
			'pmUser_id' => $data['pmUser_id'],
		);
		
		$query = "
			select
				count(*) as cnt
			from 
				v_PersonPolisEdNum
			where 
				PersonPolisEdNum_insDT = :PersonPolisEdNum_insDT
				and PersonPolisEdNum_EdNum = :PersonPolisEdNum_EdNum
			limit 1
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке существования ЕНП');	
		}
		if ($count > 0) {
			return [['success' => true]];	
		}
		
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonPolisEdNum_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonPolisEdNum_insDT := :PersonPolisEdNum_insDT,
				PersonPolisEdNum_EdNum := :PersonPolisEdNum_EdNum,
				pmUser_id := :pmUser_id
			)
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении ЕНП');
		}
		return $resp;
	}

	/**
	 * Обработка данных полиса, полученного при идентификации
	 * @param array $person
	 * @param array $identData
	 * @param array $data
	 * @return array
	 */
	function processPolisIdentData($person, $identData, $data) {
		$query = "
			select
				null as \"PersonPolis_id\",
				PP.Person_id as \"Person_id\",
				SMO.OrgSMO_id as \"OrgSMO_id\",
				OST.OMSSprTerr_id as \"OMSSprTerr_id\",
				null as \"PolisFormType_id\",
				PT.PolisType_id as \"PolisType_id\",
				PP.Polis_Ser as \"Polis_Ser\",
				case when PP.PolisType_CodeF008 = 3 
					then PP.Polis_EdNum
					else PP.Polis_Num 
				end as \"Polis_Num\",
				PP.Polis_begDate as \"Polis_begDate\",
				PP.Polis_endDate as \"Polis_endDate\"
			from (
				select
					:PersonId as Person_id,
					:SMOCOD as Orgsmo_f002smocod,
					case
						when nullif(:ENP, '') is null 
						then 1 else 3 
					end as PolisType_CodeF008,
					:SPOLIC as Polis_Ser,
					:NPOLIC as Polis_Num,
					:ENP as Polis_EdNum,
					cast(:DBEG as date) as Polis_begDate,
					cast(nullif(:DEND, '') as date) as Polis_endDate
					limit 1
				) PP
				left join v_OrgSMO SMO on SMO.Orgsmo_f002smocod = PP.Orgsmo_f002smocod
				left join v_PolisType PT on PT.PolisType_CodeF008 = PP.PolisType_CodeF008
				left join v_OmsSprTerr OST on OST.KLRgn_id = 50	-- МОСКОВСКАЯ ОБЛАСТЬ
			limit 1
		";
		$newPolis = $this->getFirstRowFromQuery($query, $identData);
		if ($newPolis === false) {
			return $this->createError('', 'Ошибка при формировании данных нового полиса');
		}

		$query = "
			select
				PP.PersonPolis_id as \"PersonPolis_id\",
				PP.Person_id as \"Person_id\",
				PP.OrgSMO_id as \"OrgSMO_id\",
				PP.OMSSprTerr_id as \"OMSSprTerr_id\",
				PP.PolisFormType_id as \"PolisFormType_id\",
				PP.PolisType_id as \"PolisType_id\",
				PP.Polis_Ser as \"Polis_Ser\",
				PP.Polis_Num as \"Polis_Num\",
				PP.Polis_begDate as \"Polis_begDate\",
				PP.Polis_endDate as \"Polis_endDate\",
				PP.Server_id as \"Server_id\"
			from v_PersonPolis PP
			where PP.Person_id = :Person_id 
				and PP.Polis_id is not null
			order by PP.Polis_begDate desc
			limit 1
		";
		$oldPolis = $this->getFirstRowFromQuery($query, $person, true);
		if ($oldPolis === false) {
			return $this->createError('', 'Ошибка при получении данных полиса человека');
		}

		$convertDate = function($date) {
			return ConvertDateFormat($date, 'Y-m-d');
		};

		$isSame = ($oldPolis &&
			$newPolis['OrgSMO_id'] == $oldPolis['OrgSMO_id'] &&
			$newPolis['PolisType_id'] == $oldPolis['PolisType_id'] &&
			$newPolis['Polis_Ser'] == $oldPolis['Polis_Ser'] &&
			$newPolis['Polis_Num'] == $oldPolis['Polis_Num'] && 
			$newPolis['Polis_begDate'] == $oldPolis['Polis_begDate']
		);
		$transferEvn = false;

		$this->beginTransaction();

		if ($isSame) {
			$resp = $this->savePersonPolis(array_merge($oldPolis, array(
				'Polis_begDate' => $convertDate($newPolis['Polis_begDate']),
				'Polis_endDate' => $convertDate($newPolis['Polis_endDate']),
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
			)));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
			$transferEvn = true;
		} else {
			$newPolis_begDate = $newPolis['Polis_begDate'];
			if($oldPolis){
				if(
					(empty($oldPolis['Polis_endDate']) && $oldPolis['Polis_begDate'] < $newPolis['Polis_begDate']) ||
					(!empty($oldPolis['Polis_endDate']) && $oldPolis['Polis_endDate'] >= $newPolis['Polis_begDate'])
				) {
					//Закрыть старый полис
					$endDate = $newPolis['Polis_begDate'];
					$endDate->modify('-1 day');
					$resp = $this->savePersonPolis(array_merge($oldPolis, array(
						'Polis_begDate' => $convertDate($oldPolis['Polis_begDate']),
						'Polis_endDate' => $convertDate($endDate),
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $data['Server_id'],
					)));
					if (!$this->isSuccessful($resp)) {
						$this->rollbackTransaction();
						return $resp;
					}
				}
			}

			//Добавить новый полис
			$resp = $this->savePersonPolis(array_merge($newPolis, array(
				'Polis_begDate' => $convertDate($newPolis_begDate),
				'Polis_endDate' => $convertDate($newPolis['Polis_endDate']),
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
			)));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}

			if ($newPolis['PolisType_id'] == 4) {
				//Добавить ЕНП
				$resp = $this->savePersonPolisEdNum(array(
					'Person_id' => $person['Person_id'],
					'PersonPolisEdNum_insDT' => $convertDate($newPolis['Polis_begDate']),
					'PersonPolisEdNum_EdNum' => $newPolis['Polis_Num'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id'],
				));
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
			}
			$transferEvn = true;
		}

		if ($transferEvn) {
			$this->db->query("exec xp_PersonTransferEvn @Person_id = :Person_id", $person);
		}

		$this->commitTransaction();

		return array(array(
			'success' => true,
			'polis' => $newPolis
		));
	}

	/**
	 * Пакетная идентификация
	 * @return array
	 */
	function PersonIdentPackage() {
		set_time_limit(0);

		$data = array('Server_id' => 0, 'pmUser_id' => 1);
		$config = $this->config->item('IDENTIFY_SERVICE');
		$this->load->library('SwPersonIdentMsk', $config, 'ident');
		$this->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d').'.log'));

		$limit = 500;	//Количество записей, обрабатываемых за один запуск скрипта

		$query = "
			select
				PRD.PersonRequestData_ReqGUID as \"PersonRequestData_ReqGUID\",
				PRD.PersonRequestData_id as \"PersonRequestData_id\",
				PRD.Person_id as \"Person_id\",
				PRD.Person_SurName as \"Person_SurName\",
				PRD.Person_FirName as \"Person_FirName\",
				PRD.Person_SecName as \"Person_SecName\",
				to_char(PRD.Person_BirthDay, 'yyyy-mm-dd') as \"Person_BirthDay\",
				PRD.Person_Snils as \"Person_Snils\",
				PRD.PolisType_id as \"PolisType_id\",
				PRD.Polis_Ser as \"Polis_Ser\",
				PRD.Polis_Num as \"Polis_Num\",
				PRD.Person_ENP as \"Person_ENP\",
				PRD.DocumType_Code as \"DocumentType_Code\",
				PRD.Docum_Ser as \"Document_Ser\",
				PRD.Docum_Num as \"Document_Num\"
			from
				erz.v_PersonRequestData PRD
				left join v_DocumentType DT on DT.DocumentType_Code = PRD.DocumType_Code
			where 
				PRD.PersonRequest_id is null
				and PRD.PersonRequestDataStatus_id = 1
			limit {$limit}
		";
		$personList = $this->queryResult($query);
		if (!is_array($personList)) {
			return $this->createError('','Ошибка при получении списка людей для идентификации');
		}

		$retryCount = [];

		for ($idx = 0; $idx < count($personList); $idx++) {
			$person = $personList[$idx];
			$retryCount[$idx] = $retryCount[$idx] ?? 0;

			try {
				$identDate = $this->getCurrentDT();
				$identData = $this->ident->getMedInsState($person);

				if (!$identData) {
					$this->setPersonRequestDataStatus(array(
						'PersonRequestData_id' => $person['PersonRequestData_id'],
						'PersonRequestDataStatus_id' => 4,    //Не идентифицирован
					));
					$this->setPersonIsInErz(array(
						'Person_id' => $person['Person_id'],
						'pmUser_id' => $data['pmUser_id'],
						'Person_IsInErz' => 1
					));
				} else {
					$identData['PersonId'] = $person['Person_id'];

					$resp = $this->processPolisIdentData($person, $identData, $data);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}
					
					$this->setPersonRequestDataStatus(array(
						'PersonRequestData_id' => $person['PersonRequestData_id'],
						'PersonRequestDataStatus_id' => 5,    //Выполнена
					));
					$this->setPersonIsInErz(array(
						'Person_id' => $person['Person_id'],
						'pmUser_id' => $data['pmUser_id'],
						'Person_IsInErz' => 2
					));
				}
			} catch(Exception $e) {
				if ($retryCount[$idx] < 1) {
					$retryCount[$idx]++;
					$idx--;
					continue;
				}
				
				$this->setPersonRequestDataStatus(array(
					'PersonRequestData_id' => $person['PersonRequestData_id'],
					'PersonRequestDataStatus_id' => 7,    //Ошибка
					'PersonRequestData_Error' => $e->getMessage()
				));
			}
		}

		return [['success' => true]];
	}
}
