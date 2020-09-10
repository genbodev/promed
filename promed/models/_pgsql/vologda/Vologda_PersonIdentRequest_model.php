<?php

require_once APPPATH.'models/_pgsql/PersonIdentRequest_model.php';

/**
 * Class Vologda_PersonIdentRequest_model
 * @property SwPersonIdentVologda $ident
 * @property Person_model $Person_model
 */
class Vologda_PersonIdentRequest_model extends PersonIdentRequest_model {
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
		if($params['PersonIdentState_id'] == 1){
			//при положительной идентификации полю Server_id присваивается 0
			$params['Server_id'] = 0;
		}
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
		$query = "
			with cte as (
				SELECT CASE WHEN :PersonPolis_id IS NOT NULL THEN 
						(select Server_id from PersonPolis  where PersonPolis_id = :PersonPolis_id limit 1) 
						ELSE :Server_id END as srv_id
			)	
			select PersonPolis_id as \"PersonPolis_id\", Polis_id as \"Polis_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
			from {$procedure}(
				Polis_id := null,
				PersonPolis_id := :PersonPolis_id,
				Server_id := (select srv_id from cte),
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
				OST.OMSSprTerr_id as \"OMSSprTerr_id\",
				null as \"PolisFormType_id\",
				PT.PolisType_id as \"PolisType_id\",
				PP.Polis_Ser as \"Polis_Ser\",
				case when PP.PolisType_CodeF008 = 3 
					then PP.Polis_EdNum else PP.Polis_Num 
				end as \"Polis_Num\",
				PP.Polis_begDate as \"Polis_begDate\",
				PP.Polis_endDate as \"Polis_endDate\"
			from (
				select 
					:PersonId as Person_id,
					:InnerICCode as Orgsmo_f002smocod,
					CAST(:InsType as bigint) as PolisType_CodeF008,
					:InsSer as Polis_Ser, 
					:InsNum as Polis_Num,
					:Enp as Polis_EdNum,
					cast(:InsBegin as date) as Polis_begDate,
					cast(nullif(:InsEnd, '') as date) as Polis_endDate
			) PP
			left join v_OrgSMO SMO  on SMO.Orgsmo_f002smocod = PP.Orgsmo_f002smocod
			left join v_PolisType PT  on PT.PolisType_CodeF008 = PP.PolisType_CodeF008
			left join v_OmsSprTerr OST  on OST.KLRgn_id = 35	--ВОЛОГОДСКАЯ ОБЛАСТЬ
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
			return ($date instanceof DateTime)?$date->format('Y-m-d'):$date;
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
					(empty($oldPolis['Polis_endDate']) && $oldPolis['Polis_begDate'] < $newPolis['Polis_begDate'])
					||
					(!empty($oldPolis['Polis_endDate']) && $oldPolis['Polis_endDate'] >= $newPolis['Polis_begDate'])
				){
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
				}elseif (empty($oldPolis['Polis_endDate']) && $oldPolis['Polis_begDate'] >= $newPolis['Polis_begDate']) {
					if(empty($newPolis['Polis_endDate']) || $newPolis['Polis_endDate'] > $oldPolis['Polis_begDate']){
						$resDel = $this->delPersonPolis(array(
							'PersonPolis_id' => $oldPolis['PersonPolis_id'],
							'Person_id' => $oldPolis['Person_id'],
							'pmUser_id' => $data['pmUser_id'],
							'Server_id' => $oldPolis['Server_id'],
						));
					}
					if(!$resDel){
						$this->rollbackTransaction();
						return $resDel;
					}
					$newPolis_begDate->modify('+1 day');
				}
			}
			/*
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
			*/

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
			$this->db->query("SELECT xp_PersonTransferEvn (Person_id := :Person_id)", $person);
		}

		$this->commitTransaction();

		return array(array(
			'success' => true,
			'polis' => $newPolis
		));
	}

	/**
	 * Сохраняем СНИЛС
	 */
	function setSnils($person, $identData, $data) {

		if (empty($identData['Snils'])) {
			return [['success' => true]];
		}

		$old = $this->getFirstResultFromQuery("
			select Person_Snils as \"Person_Snils\"
			from v_PersonState 
			where Person_id = :Person_id
			limit 1
		", array('Person_id' => $person['Person_id']));

		if (empty($old) || str_replace(array('-', ' '), '', $identData['Snils']) != str_replace(array('-', ' '), '', $old)) {

			$query = "
				select PersonSnils_id as \"PersonEvn_id\", error_message as \"Error_Msg\"
				from p_PersonSnils_ins(
					PersonSnils_id := null,
					Server_id := :Server_id,
					Person_id := :Person_id,
					PersonSnils_Snils := :PersonSnils_Snils,
					pmUser_id := :pmUser_id);
			";

			return $this->queryResult($query, array(
				'Server_id' => $data['Server_id'],
				'Person_id' => $person['Person_id'],
				'PersonSnils_Snils' => str_replace(array('-', ' '), '', $identData['Snils']),
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return [['success' => true]];
	}

	/**
	 * Пакетная идентификация
	 * @return array
	 */
	function PersonIdentPackage() {
		set_time_limit(0);

		$data = array('Server_id' => 0, 'pmUser_id' => 1);
		$config = $this->config->item('IDENTIFY_SERVICE');
		$this->load->library('SwPersonIdentVologda', $config, 'ident');
		$this->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d').'.log'));

		try {
			$this->ident->connect();
		} catch(Exception $e) {
			return $this->createError('', $e->getMessage());
		}

		$limit = 500;	//Количество записей, обрабатываемых за один запуск скрипта

		$query = "
			select 
				PRD.PersonRequestData_id as \"PersonRequestData_id\",
				PRD.Person_id as \"Person_id\",
				PRD.Person_SurName as \"Person_SurName\",
				PRD.Person_FirName as \"Person_FirName\",
				PRD.Person_SecName as \"Person_SecName\",
				PRD.Person_SurName||' '||PRD.Person_FirName||COALESCE(' '||PRD.Person_SecName, '') as \"Person_Fio\",
				to_char(PRD.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
				to_char(PRD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDayPrint\",
				PRD.Person_Snils as \"Person_Snils\",
				PRD.PolisType_id as \"PolisType_id\",
				PRD.Polis_Ser as \"Polis_Ser\",
				PRD.Polis_Num as \"Polis_Num\",
				PRD.Person_ENP as \"Person_ENP\",
				PRD.DocumType_Code as \"DocumentType_Code\",
				PRD.Docum_Ser as \"Document_Ser\",
				PRD.Docum_Num as \"Document_Num\",
				DT.DocumentType_SysNick||COALESCE(' '||PRD.Docum_Ser, '')||' '|| Docum_Num as \"Document\",
				PC.PersonCard_id as \"PersonCard_id\"
			from
				erz.v_PersonRequestData PRD 
				left join v_DocumentType DT  on DT.DocumentType_Code = PRD.DocumType_Code
				LEFT JOIN LATERAL (
					select PC.PersonCard_id
					from v_PersonCard PC 
					where PC.Person_id = PRD.Person_id 
					and PC.LpuAttachType_id = 1
					and PC.PersonCard_endDate is null
					order by PC.PersonCard_begDate desc
                    limit 1
				) PC ON true
			where 
				PRD.PersonRequest_id is null
				and PRD.PersonRequestDataStatus_id = 1
            limit {$limit}
		";
		$personList = $this->queryResult($query);
		if (!is_array($personList)) {
			return $this->createError('','Ошибка при получении списка людей для идентификации');
		}

		$retryCount = array();

		for ($idx = 0; $idx < count($personList); $idx++) {
			$person = $personList[$idx];
			$retryCount[$idx] = isset($retryCount[$idx])?$retryCount[$idx]:0;

			try {
				$identDate = $this->getCurrentDT();
				$identData = $this->ident->getMedInsState($person, $identDate);

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
					//$identData['InsBegin'] = $identDate->format('Y-m-d'); //не понял почему тут текущая дата

					$resp = $this->processPolisIdentData($person, $identData, $data);
					if (!$this->isSuccessful($resp)) {
						throw new Exception($resp[0]['Error_Msg']);
					}

					$resp = $this->setSnils($person, $identData, $data);
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

				if ($identData && !empty($identData['Enp'])) {
					$attach = $this->ident->getAttach($identData['Enp']);

					if (!empty($attach)) {
						$polis = $this->getFirstRowFromQuery("
							select 
							PS.Person_EdNum as \"Person_EdNum\",
							(
								PT.PolisType_Name||' №'||COALESCE(P.Polis_Ser||' ', '')
								||COALESCE(PS.Person_EdNum, P.Polis_Num)
								||' от '||to_char(P.Polis_begDate, 'DD.MM.YYYY')
							) as \"Polis\"
							from v_PersonState PS 
							inner join v_Polis P  on P.Polis_id = PS.Polis_id
							inner join v_PolisType PT  on PT.PolisType_id = P.PolisType_id
							where PS.Person_id = :Person_id
                            limit 1
						", $person);

						$lpu = $this->getFirstResultFromQuery("
							select Lpu_Nick  as \"Lpu_Nick\" from v_Lpu  where Lpu_f003mcod = :code limit 1
						", array('code' => $attach), true);
						if ($lpu !== false) $lpu = $attach;

						if (!empty($lpu) && is_array($polis)) {
							$msg = "По данным ТФОМС у пациента {$person['Person_Fio']}, {$person['Person_BirthDayPrint']}, {$person['Document']},";
							$msg .= " СНИЛС {$person['Person_Snils']}, {$polis['Polis']}, ЕНП {$polis['Person_EdNum']}";
							$msg .= " есть действующее прикрепление к МО: {$lpu}";
							$this->textlog->add($msg);
						}
					}
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

	/**
	 * @param array $data
	 * @return array
	 */
	function identPerson($data) {
		$data = array(
			'Server_id' => 0,
			'pmUser_id' => $data['pmUser_id'],
			'PersonRequestData_id' => $data['PersonRequestData_id'],
		);

		$response = array(
			'success' => true,
		);

		try {
			$this->load->library('textlog', array('file'=>'PersonIdentRequest_'.date('Y-m-d').'.log'));

			$config = $this->config->item('IDENTIFY_SERVICE');
			$this->load->library('SwPersonIdentVologda', $config, 'ident');
			$this->ident->connect();

			$query = "
				select 
					PRD.PersonRequestData_id as \"PersonRequestData_id\",
					PRD.Person_id as \"Person_id\",
					PRD.Person_SurName as \"Person_SurName\",
					PRD.Person_FirName as \"Person_FirName\",
					PRD.Person_SecName as \"Person_SecName\",
					PRD.Person_SurName||' '||PRD.Person_FirName||COALESCE(' '||PRD.Person_SecName, '') as \"Person_Fio\",
					to_char(PRD.Person_BirthDay, 'YYYY-MM-DD') as \"Person_BirthDay\",
					to_char(PRD.Person_BirthDay, 'DD.MM.YYYY') as \"Person_BirthDayPrint\",
					PRD.Person_Snils as \"Person_Snils\",
					PRD.PolisType_id as \"PolisType_id\",
					PRD.Polis_Ser as \"Polis_Ser\",
					PRD.Polis_Num as \"Polis_Num\",
					PRD.Person_ENP as \"Person_ENP\",
					PRD.DocumType_Code as \"DocumentType_Code\",
					PRD.Docum_Ser as \"Document_Ser\",
					PRD.Docum_Num as \"Document_Num\",
					DT.DocumentType_SysNick||COALESCE(' '||PRD.Docum_Ser, '')||' '|| Docum_Num as \"Document\",
					PC.PersonCard_id as \"PersonCard_id\"
				from
					erz.v_PersonRequestData PRD 
					left join v_DocumentType DT  on DT.DocumentType_Code = PRD.DocumType_Code
					LEFT JOIN LATERAL (
						select PC.PersonCard_id
						from v_PersonCard PC 
						where PC.Person_id = PRD.Person_id 
						and PC.LpuAttachType_id = 1
						and PC.PersonCard_endDate is null
						order by PC.PersonCard_begDate desc
                        limit 1
					) PC ON true
				where
					PRD.PersonRequestData_id = :PersonRequestData_id
                limit 1
			";
			$person = $this->getFirstRowFromQuery($query, $data);
			if (!is_array($person)) {
				return $this->createError('','Ошибка при получении данных человека для идентификации');
			}

			$identDate = $this->getCurrentDT();
			$identData = $this->ident->getMedInsState($person, $identDate);

			if (!$identData) {
				$this->setPersonRequestDataStatus(array(
					'PersonRequestData_id' => $person['PersonRequestData_id'],
					'PersonRequestDataStatus_id' => 4,    //Не идентифицирован
					'PersonNoIdentCause_id' => 1,    // Человек не найден
					'PersonRequestData_csDT' => $identDate->format('Y-m-d'),
				));
				$this->setPersonIsInErz(array(
					'Person_id' => $person['Person_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Person_IsInErz' => 1
				));
				$response['Person_IsInErz'] = 1;
				$response['PersonIdentState_id'] = 2;
				$response['Person_identDT'] = $identDate->format('Y-m-d');
			} else {
				$identData['PersonId'] = $person['Person_id'];
				//$identData['InsBegin'] = $identDate->format('Y-m-d'); //не понял почему тут текущая дата

				$resp = $this->processPolisIdentData($person, $identData, $data);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$polis = $resp[0]['polis'];

				$resp = $this->setSnils($person, $identData, $data);
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}

				$this->setPersonRequestDataStatus(array(
					'PersonRequestData_id' => $person['PersonRequestData_id'],
					'PersonRequestDataStatus_id' => 5,    //Выполнена
					'PersonRequestData_csDT' => $identDate->format('Y-m-d'),
				));
				$this->setPersonIsInErz(array(
					'Person_id' => $person['Person_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Person_IsInErz' => 2
				));

				if ($polis['PolisType_id'] == 4) {
					$polis['Federal_Num'] = $polis['Polis_Num'];
					$polis['Polis_Num'] = null;
				} else {
					$polis['Federal_Num'] = null;
				}
				foreach($polis as $field => $value) {
					if ($value instanceof DateTime) {
						$polis[$field] = $value->format('Y-m-d');
					}
				}

				$response['Person_IsInErz'] = 2;
				$response['PersonIdentState_id'] = 1;
				$response['Person_identDT'] = $identDate->format('Y-m-d');
				if (!empty($identData['Snils'])) {
					$response['Person_SNILS'] = str_replace(' ', '-', trim($identData['Snils']));
				}
				$response = array_merge($response, $polis);
			}

			if ($identData && !empty($identData['Enp'])) {
				$attach = $this->ident->getAttach($identData['Enp']);

				if (!empty($attach)) {
					$polis = $this->getFirstRowFromQuery("
						select 
						PS.Person_EdNum as \"Person_EdNum\",
						(
							PT.PolisType_Name||' №'||COALESCE(P.Polis_Ser||' ', '')
							||COALESCE(PS.Person_EdNum, P.Polis_Num)
							||' от '||to_char(P.Polis_begDate, 'DD.MM.YYYY')
						) as \"Polis\"
						from v_PersonState PS 
						inner join v_Polis P  on P.Polis_id = PS.Polis_id
						inner join v_PolisType PT  on PT.PolisType_id = P.PolisType_id
						where PS.Person_id = :Person_id
                        limit 1
					", $person);

					$lpu = $this->getFirstResultFromQuery("
						select Lpu_Nick  as \"Lpu_Nick\" from v_Lpu  where Lpu_f003mcod = :code limit 1
					", array('code' => $attach), true);
                    
					if ($lpu !== false) {
                        $lpu = $attach;
                    }

					if (!empty($lpu) && is_array($polis)) {
						$msg = "По данным ТФОМС у пациента {$person['Person_Fio']}, {$person['Person_BirthDayPrint']}, {$person['Document']},";
						$msg .= " СНИЛС {$person['Person_Snils']}, {$polis['Polis']}, ЕНП {$polis['Person_EdNum']}";
						$msg .= " есть действующее прикрепление к МО: {$lpu}";
						$this->textlog->add($msg);
					}
				}
			}
		} catch(Exception $e) {
			$this->setPersonRequestDataStatus(array(
				'PersonRequestData_id' => $data['PersonRequestData_id'],
				'PersonRequestDataStatus_id' => 7,    //Ошибка
				'PersonRequestData_Error' => $e->getMessage()
			));
			return $this->createError('', $e->getMessage());
		}

		return array($response);
	}
	
	function delPersonPolis($data){
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
			select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"            
			from xp_PersonRemovePersonEvn(
				Person_id := :Person_id,
				Server_id := :Server_id,
				PersonEvn_id := :PersonPolis_id,
				pmUser_id := :pmUser_id);";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при удалении данных полиса');
		}
		if (!$this->isSuccessful($resp)) {
			throw new Exception($resp[0]['Error_Msg']);
		}
		return true;
	}
}