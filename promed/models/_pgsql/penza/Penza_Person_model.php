<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Person
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			01.10.2017
 */

require_once(APPPATH.'models/_pgsql/Person_model.php');

class Penza_Person_model extends Person_model {
	/**
	 * Penza_Person_model constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Добавление данных человека на идентификацию в РС ЕРЗ
	 * @param array $data
	 * @return array
	 */
	function addPersonRequestData($data) {
		$fromClient = (isset($data['fromClient']) && $data['fromClient']);
		$params = array(
			'Person_id' => $data['Person_id'],
			'PersonRequestSourceType_id' => $data['PersonRequestSourceType_id'],
			'Person_identDT' => !empty($data['Person_identDT'])?$data['Person_identDT']:null,
		);

		if (empty($params['Person_identDT'])) {
			$params['Person_identDT'] = $this->getFirstResultFromQuery("select to_char(dbo.tzGetDate(), 'YYYY-MM-DD HH24:MI:SS.mi')");
			if ($params['Person_identDT'] === false) {
				return $this->createError('', 'Ошибка при получении текущей даты');
			}
		}

		if ( $params['Person_identDT'] instanceof DateTime ) {
			$params['Person_identDT'] = $params['Person_identDT']->format('Y-m-d H:i:s');
		}

		//Получение идентификатора предыдущего запроса на идентификацию
		$query = "
			select PRD.PersonRequestData_id as \"PersonRequestData_id\"
			from erz.v_PersonRequestData PRD 
			where PRD.Person_id = :Person_id and cast(PRD.Evn_disDT as date) = cast(:Person_identDT as date)
			and PRD.PersonRequestDataStatus_id <> 7
			order by PRD.PersonRequestData_insDT desc
            limit 1
		";
		$PersonRequestData_id = $this->getFirstResultFromQuery($query, $params, true);
		if ($PersonRequestData_id === false) {
			return $this->createError('','Ошибка при поиске человека в пакетах на идентификацию');
		}
		if (!empty($PersonRequestData_id)) {
			return $this->createError(302,'Уже существует запись в пакете на идентификацию человека');
		}

		$query = "
			select
				Person_IsInErz as \"Person_IsInErz\",
				PersonIdentState_id as \"PersonIdentState_id\",
				to_char(Person_identDT, 'YYYY-MM-DD HH24:MI:SS.mi') as \"Person_identDT\"
			from v_Person 
			where Person_id = :Person_id
		";
		$lastIdent = $this->getFirstRowFromQuery($query, $params);
		if (!is_array($lastIdent)) {
			return $this->createError('','Ошибка при получении статуса последней идентификации');
		}

		$fields = array(
			'Person_Surname' => 'Фамилия',
			'Person_Firname' => 'Имя',
			'Person_Secname' => 'Отчество',
			'Person_Sex' => 'Пол',
			'Person_Birthday' => 'Дата рождения',
			'Person_ENP' => 'Единый номер',
			'Person_Snils' => 'СНИЛС',
			'DocumType_Code' => 'Тип документа',
			'Docum_Ser' => 'Серия документа',
			'Docum_Num' => 'Номер документа',
			'PolisType_id' => 'Тип полиса',
			'Polis_Ser' => 'Серия',
			'Polis_Num' => 'Номер',
		);

		$person = array();

		if ($fromClient) {
			foreach($fields as $nick => $name) {
				$person[$nick] = !empty($data[$nick])?$data[$nick]:null;
			}
		} else {
			$query = "
				select 
					PersonAll.Person_id as \"Person_id\",
					PersonAll.Person_SurName as \"Person_Surname\",
					PersonAll.Person_FirName as \"Person_Firname\",
					PersonAll.Person_SecName as \"Person_Secname\",
					PersonAll.Sex_id as \"Person_Sex\",
					to_char(PersonAll.Person_Birthday, 'YYYY-MM-DD') as \"Person_Birthday\",
					PersonAll.Person_EdNum as \"Person_ENP\",
					PersonAll.Person_Snils as \"Person_Snils\",
					DocumentType.DocumentType_Code as \"DocumType_Code\",
					Document.Document_Ser as \"Docum_Ser\",
					Document.Document_Num as \"Docum_Num\",
					Polis.PolisType_id as \"PolisType_id\",
					Polis.Polis_Ser as \"Polis_Ser\",
					Polis.Polis_Num as \"Polis_Num\"
				from
					v_Person_bdz PersonAll 
					left join v_Polis Polis  on Polis.Polis_id =PersonAll.Polis_id
					left join dbo.v_Document Document  on Document.Document_id = PersonAll.Document_id
					left join dbo.v_DocumentType DocumentType  on DocumentType.DocumentType_id = Document.DocumentType_id
				where
					PersonAll.Person_id = :Person_id
					and PersonAll.PersonEvn_insDT <= :Person_identDT
				order by
					PersonAll.PersonEvn_insDT desc
            	limit 1
			";
			$person = $this->getFirstRowFromQuery($query, $params);
			if (!is_array($person)) {
				return $this->createError('','Ошибка при получении данных человека для идентификации');
			}
		}

		$requiredFields = array('Person_Birthday','Person_Sex','DocumType_Code','Docum_Num');

		$requireField = null;
		foreach($requiredFields as $field) {
			if (empty($person[$field])) {
				$requireField = $field;
				break;
			}
		}

		if (!empty($requireField)) {
			//На Пензе, не сохранять запрос в истории, сразу выводить ошибку
			return $this->createError(303, "
				Для выполнения идентификации необходимо наличие следующих данных:<br/>
				•	пол,<br/>
				•	дата рождения,<br/>
				•	документ, удостоверяющий личность.
			");
		}

		$this->beginTransaction();

		if (!empty($PersonRequestData_id)) {
			//Проставление причины отказа от идентификации "В процессе идентификации были изменены данные человека" для предыдущего запроса
			$resp = $this->setPersonRequestDataStatus(array(
				'PersonRequestData_id' => $PersonRequestData_id,
				//'PersonRequestDataStatus_id' => 7,
				'PersonNoIdentCause_id' => 3,
			));
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		//Добавление записи для идентификации службой
		$query = "
			select
				PersonRequestData_id as \"PersonRequestData_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from erz.p_PersonRequestData_ins_old(
				PersonRequestData_id := null,
				PersonRequestDataStatus_id := :PersonRequestDataStatus_id,
				PersonRequestData_ReqGUID := cast(newid() as varchar),
				Evn_id := null,
				Evn_disDT := :Evn_disDT,			--Идентификация на дату
				Person_id := :Person_id,
				Person_Surname := :Person_Surname,
				Person_Firname := :Person_Firname,
				Person_Secname := :Person_Secname,
				Person_Sex := :Person_Sex,
				Person_Birthday := :Person_Birthday,
				Person_ENP := :Person_ENP,
				Person_Snils := :Person_Snils,
				DocumType_Code := :DocumType_Code,
				Docum_Ser := :Docum_Ser,
				Docum_Num := :Docum_Num,
				PolisType_id := :PolisType_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				PersonRequestData_csDT := null,
				PersonRequestData_flcDT := :PersonRequestData_flcDT,
				PersonRequestData_Error := :PersonRequestData_Error,
				PersonRequestSourceType_id := :PersonRequestSourceType_id,
				PersonRequest_id := null,
				pmUser_id := :pmUser_id
			);
		";
		$queryParams = array(
			'PersonRequestDataStatus_id' => 1,	//Новая
			'Evn_disDT' => $params['Person_identDT'],
			'Person_id' => $params['Person_id'],
			'PersonRequestSourceType_id' => $params['PersonRequestSourceType_id'],
			'Person_Surname' => $person['Person_Surname'],
			'Person_Firname' => $person['Person_Firname'],
			'Person_Secname' => $person['Person_Secname'],
			'Person_Sex' => $person['Person_Sex'],
			'Person_Birthday' => $person['Person_Birthday'],
			'Person_ENP' => $person['Person_ENP'],
			'Person_Snils' => $person['Person_Snils'],
			'DocumType_Code' => $person['DocumType_Code'],
			'Docum_Ser' => $person['Docum_Ser'],
			'Docum_Num' => $person['Docum_Num'],
			'PolisType_id' => $person['PolisType_id'],
			'Polis_Ser' => $person['Polis_Ser'],
			'Polis_Num' => $person['Polis_Num'],
			'PersonRequestData_flcDT' => null,
			'PersonRequestData_Error' => null,
			'pmUser_id' => $data['pmUser_id'],
		);
		if (!empty($requireFieldError)) {
			$queryParams['PersonRequestDataStatus_id'] = 7;		//Ошибка
			$queryParams['PersonRequestData_flcDT'] = $this->currentDT->format('Y-m-d H:i:s');
			$queryParams['PersonRequestData_Error'] = $requireFieldError;
		}
		$resp = $this->queryResult($query, $queryParams);
		if (!is_array($resp)) {
			$this->rollbackTransaction();
			return $this->createError('Ошибка при добавлении человека на идентификацию');
		}
		if (!$this->isSuccessful($resp)) {
			$this->rollbackTransaction();
			return $resp;
		}

		if (empty($lastIdent['Person_IsInErz']) || date_create($lastIdent['Person_identDT']) <= date_create($params['Person_identDT'])) {
			if (!empty($requireFieldError)) {
				$lastIdent['PersonIdentState_id'] = 5;
				$lastIdent['Person_identDT'] = $params['Person_identDT'];
			} else {
				$lastIdent['Person_IsInErz'] = null;
				$lastIdent['PersonIdentState_id'] = 4;
				$lastIdent['Person_identDT'] = $params['Person_identDT'];
			}

			$resp = $this->updatePerson(array(
				'Person_id' => $params['Person_id'],
				'Person_IsInErz' => $lastIdent['Person_IsInErz'],
				'PersonIdentState_id' => $lastIdent['PersonIdentState_id'],
				'Person_identDT' => $lastIdent['Person_identDT'],
				'pmUser_id' => $data['pmUser_id']
			));
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при изменении статуса идентификации человека');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();

		if (!empty($requireFieldError)) {
			$response = array_merge($lastIdent, array('success' => false, 'Error_Msg' => $requireFieldError));
		} else {
			$response = array_merge($lastIdent, array('success' => true));
		}

		return array($response);
	}
}