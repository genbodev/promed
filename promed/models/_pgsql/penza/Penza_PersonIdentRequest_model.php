<?php

require_once APPPATH.'models/_pgsql/PersonIdentRequest_model.php';

/**
 * Class Penza_PersonIdentRequest_model
 * @property SwPersonIdentPenza $ident
 * @property Person_model $Person_model
 */
class Penza_PersonIdentRequest_model extends PersonIdentRequest_model {
	/**
	 * Penza_PersonIdentRequest_model constructor.
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from erz.p_PersonRequestData_status(
				PersonRequestData_id := :PersonRequestData_id,
				PersonRequestDataStatus_id := :PersonRequestDataStatus_id,
				PersonRequestData_flcDT := :PersonRequestData_flcDT,
				PersonRequestData_csDT := :PersonRequestData_csDT,
				PersonRequestData_Error := :PersonRequestData_Error);
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
		$this->load->model('Person_model');
		return $this->Person_model->updatePerson($params);
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
			'OmsSprTerr_id' => $data['OmsSprTerr_id'],
			'OrgSMO_id' => $data['OrgSMO_id'],
			'PolisType_id' => $data['PolisType_id'],
			'PolisFormType_id' => !empty($data['PolisFormType_id'])?$data['PolisFormType_id']:null,
			'Polis_Ser' => !empty($data['Polis_Ser'])?$data['Polis_Ser']:null,
			'Polis_Num' => $data['Polis_Num'],
			'Polis_begDate' => $data['Polis_begDate'],
			'Polis_endDate' => !empty($data['Polis_endDate'])?$data['Polis_endDate']:null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (empty($params['PersonPolis_id'])) {
			$procedure = 'p_PersonPolis_ins';
		} else {
			$procedure = 'p_PersonPolis_upd';
		}
		$query = "
			WITH cte AS (
				SELECT CASE WHEN :PersonPolis_id IS NULL THEN :Server_id ELSE 
				(select Server_id from PersonPolis  where PersonPolis_id = :PersonPolis_id limit 1) END AS srv_id
			)
			select PersonPolis_id as \"PersonPolis_id\", Polis_id as \"Polis_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				Polis_id := null,
				PersonPolis_id := :PersonPolis_id,
				Server_id := (SELECT srv_id FROM cte),
				Person_id := :Person_id,
				OmsSprTerr_id := :OmsSprTerr_id,
				OrgSMO_id := :OrgSMO_id,
				PolisType_id := :PolisType_id,
				PolisFormType_id := :PolisFormType_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Polis_begDate := :Polis_begDate,
				Polis_endDate := :Polis_endDate,
				pmUser_id := :pmUser_id);
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"                
			from p_PersonPolisEdNum_ins(
				Server_id := :Server_id,
				Person_id := :Person_id,
				PersonPolisEdNum_insDT := :PersonPolisEdNum_insDT,
				PersonPolisEdNum_EdNum := :PersonPolisEdNum_EdNum,
				pmUser_id := :pmUser_id);
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
				OST.OmsSprTerr_id as \"OmsSprTerr_id\",
				null as \"PolisFormType_id\",
				PT.PolisType_id as \"PolisType_id\",
				PP.Polis_Ser as \"Polis_Ser\",
				PP.Polis_Num as \"Polis_Num\",
				PP.Polis_begDate as \"Polis_begDate\",
				null as \"Polis_endDate\"
			from (
				select 
					:person_id as Person_id,
					:insurance_orgcode as Orgsmo_f002smocod,
					:policy_doctype as PolisType_CodeF008,
					:policy_series as Polis_Ser, 
					:policy_number as Polis_Num,
					cast(:policy_begdate as date) as Polis_begDate
			) PP
			left join v_OrgSMO SMO  on SMO.Orgsmo_f002smocod = PP.Orgsmo_f002smocod
			left join v_PolisType PT  on PT.PolisType_CodeF008 = PP.PolisType_CodeF008
			left join v_OmsSprTerr OST  on OST.KLRgn_id = 58	--ПЕНЗЕНСКАЯ ОБЛАСТЬ
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
				PP.OmsSprTerr_id as \"OmsSprTerr_id\",
				PP.PolisFormType_id as \"PolisFormType_id\",
				PP.PolisType_id as \"PolisType_id\",
				PP.Polis_Ser as \"Polis_Ser\",
				PP.Polis_Num as \"Polis_Num\",
				PP.Polis_begDate as \"Polis_begDate\",
				PP.Polis_endDate as \"Polis_endDate\"
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
			return ($date instanceof DateTime)?$date->format('Y-m-d'):$date;
		};

		$isSame = ($oldPolis &&
			$newPolis['OrgSMO_id'] == $oldPolis['OrgSMO_id'] &&
			$newPolis['PolisType_id'] == $oldPolis['PolisType_id'] &&
			$newPolis['Polis_Ser'] == $oldPolis['Polis_Ser'] &&
			$newPolis['Polis_Num'] == $oldPolis['Polis_Num']
		);
		$transferEvn = false;

		$this->beginTransaction();

		if ($isSame) {
			if (!empty($oldPolis['Polis_endDate'])) {
				//Убрать дату закрытия старого полиса
				$resp = $this->savePersonPolis(array_merge($oldPolis, array(
					'Polis_begDate' => $convertDate($oldPolis['Polis_begDate']),
					'Polis_endDate' => null,
					'pmUser_id' => $data['pmUser_id'],
					'Server_id' => $data['Server_id'],
				)));
				if (!$this->isSuccessful($resp)) {
					$this->rollbackTransaction();
					return $resp;
				}
				$transferEvn = true;
			}
		} else {
			if ($oldPolis && (empty($oldPolis['Polis_endDate']) || $oldPolis['Polis_endDate'] >= $newPolis['Polis_begDate'])) {
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

			//Добавить новый полис
			$resp = $this->savePersonPolis(array_merge($newPolis, array(
				'Polis_begDate' => $convertDate($newPolis['Polis_begDate']),
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
			$this->db->query("SELECT xp_PersonTransferEvn( Person_id := :Person_id)", $person);
		}

		$this->commitTransaction();

		return array(array('success' => true));
	}

	/**
	 * Пакетная идентификация
	 * @return array
	 */
	function PersonIdentPackage() {
		set_time_limit(0);

		$data = array('Server_id' => 0, 'pmUser_id' => 1);
		$config = $this->config->item('IDENTIFY_SERVICE');
		$this->load->library('SwPersonIdentPenza', $config, 'ident');

		$limit = 500;	//Количество записей, обрабатываемых за один запуск скрипта

		$query = "
			select 
				PRD.PersonRequestData_id as \"PersonRequestData_id\",
				PRD.Person_id as \"Person_id\",
				PRD.Person_Sex as \"sex\",
				to_char(PRD.Person_BirthDay, 'DD.MM.YYYY') as \"birthdate\",
				PRD.DocumType_Code as \"doc_code\",
				PRD.Docum_Ser as \"doc_series\",
				PRD.Docum_Num as \"doc_number\"
			from 
				erz.v_PersonRequestData PRD 
			where 
				PRD.PersonRequest_id is null
				and PRD.PersonRequestDataStatus_id = 1
				and PRD.Person_Sex is not null
				and PRD.Person_BirthDay is not null
				and PRD.DocumType_Code is not null
				and coalesce(rtrim(PRD.Docum_Num), '') is not null
			order by
				PRD.PersonRequestData_insDT asc
			limit {$limit}
		";
		$personList = $this->queryResult($query);
		if (!is_array($personList)) {
			return $this->createError('','Ошибка при получении списка людей для идентификации');
		}

		$resp = $this->ident->login();
		if (!$this->isSuccessful($resp)) {
			return $resp;
		}

		$retryCount = array();

		for ($idx = 0; $idx < count($personList); $idx++) {
			$person = $personList[$idx];
			$retryCount[$idx] = isset($retryCount[$idx])?$retryCount[$idx]:0;

			try {
				$identDate = date_create();
				$identResponse = $this->ident->search($person);
				if (!$this->isSuccessful($identResponse)) {
					if ($identResponse[0]['Error_Code'] == 401) {
						//Закончилось время сессии. Нужно повторно залогиниться и отправить запись на идентификацию
						$resp = $this->ident->login();
						if (!$this->isSuccessful($resp)) {
							throw new Exception($resp[0]['Error_Msg'], $resp[0]['Error_Code']);
						}
						$idx--;
						continue;
					} else if ($retryCount < 1) {
						//Повторная попытка выполнить идентификацию
						$retryCount[$idx]++;
						$idx--;
						continue;
					} else {
						throw new Exception($identResponse[0]['Error_Msg'], $identResponse[0]['Error_Code']);
					}
				}

				if (!is_array($identResponse[0]['list']) || count($identResponse[0]['list']) == 0) {
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
					$identData = $identResponse[0]['list'][0];
					$identData['person_id'] = $person['Person_id'];
					$identData['policy_begdate'] = $identDate->format('Y-m-d');

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
				$this->setPersonRequestDataStatus(array(
					'PersonRequestData_id' => $person['PersonRequestData_id'],
					'PersonRequestDataStatus_id' => 7,    //Ошибка
					'PersonRequestData_Error' => $e->getMessage()
				));
			}
		}

		return array(array('success' => true));
	}
}