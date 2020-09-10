<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonEncrypHIV_model - модель для работы с шифрами вич-инфецированных
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.06.2015
 *
 * @property Utils_model Utils_model
 * @property Person_model Person_model
 */

class PersonEncrypHIV_model extends swPgModel {
	protected $_terrList = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @param $EncrypHIVTerr_id
	 * @return bool|null|int
	 */
	function getEncrypHIVTerrCode($EncrypHIVTerr_id) {
		if (empty($this->_terrList)) {
			$resp = $this->queryResult("
				select
					EHT.EncrypHIVTerr_id as \"EncrypHIVTerr_id\",
					EHT.EncrypHIVTerr_Code as \"EncrypHIVTerr_Code\",
					EHT.EncrypHIVTerr_Name as \"EncrypHIVTerr_Name\",
					EHT.KLArea_id as \"KLArea_id\"
				from EncrypHIVTerr EHT	--По всем регионам
			");
			if (!is_array($resp)) {
				return false;
			}
			foreach($resp as $item) {
				$key = $item['EncrypHIVTerr_id'];
				$this->_terrList[$key] = $item;
			}
		}

		if (isset($this->_terrList[$EncrypHIVTerr_id])) {
			return $this->_terrList[$EncrypHIVTerr_id]['EncrypHIVTerr_Code'];
		}
		return null;
	}

	/**
	 * Получение шифра
	 */
	function getPersonEncrypHIVEncryp($data) {
		$params = array(
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null,
			'PersonEncrypHIV_setDT' => $data['PersonEncrypHIV_setDT'],
			'PersonEncrypHIV_Num' => !empty($data['PersonEncrypHIV_Num'])?$data['PersonEncrypHIV_Num']:null,
			'default_EncrypHIVTerr_Code' => !empty($data['default_EncrypHIVTerr_Code']) && is_numeric($data['default_EncrypHIVTerr_Code'])?$data['default_EncrypHIVTerr_Code']:21,
		);

		//Реальный пациент не может иметь территорию шифрования "Анонимно"
		if (!empty($params['Person_id']) && $params['default_EncrypHIVTerr_Code'] == 20) {
			$params['default_EncrypHIVTerr_Code'] = 21;
		}

		if (!empty($params['Person_id'])) {
			$res = $this->getFirstRowFromQuery("
				select
					case when coalesce(NS.KLCountry_id, 643) <> 643
						then '18'
					when A.Address_id is null
						then '21'
					when A.KLRgn_id <> 30 then'17'
						else coalesce((
							select
								cast(EHT.EncrypHIVTerr_id as varchar)
							from v_EncrypHIVTerr EHT
							where EHT.KLArea_id in (A.KLRgn_id, A.KLSubRgn_id, A.KLCity_id, A.KLTown_id)
							limit 1
						), cast(null as varchar))
					end as \"EncrypHIVTerr_Code\",
					PS.Person_SurName as \"Person_SurName\"
				from v_PersonState PS
				left join v_Address A on A.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
				left join v_NationalityStatus NS on NS.NationalityStatus_id = PS.NationalityStatus_id
				where PS.Person_id = :Person_id
				limit 1
			", $params);

			$params['EncrypHIVTerr_Code'] = $res['EncrypHIVTerr_Code'];

			if (!empty($res['Person_SurName'])) {
				$params['Person_SurName'] = $res['Person_SurName'];
			} else {
				$params['Person_SurName'] = '-';
			}
		} else {
			$params['EncrypHIVTerr_Code'] = 20;
		}

		if (empty($params['EncrypHIVTerr_Code'])) {
			$params['EncrypHIVTerr_Code'] = $params['default_EncrypHIVTerr_Code'];
		}

		if (empty($params['PersonEncrypHIV_Num'])) {
			$params['PersonEncrypHIV_Num'] = $this->getFirstResultFromQuery("
				select
					(coalesce(max(cast(substring(PEH.PersonEncrypHIV_Encryp, 9, 4) as integer)), 0) + 1) as \"default_EncrypHIVTerr_Code\"
				from v_PersonEncrypHIV PEH
				where date_part('year', PEH.PersonEncrypHIV_setDT) = date_part('year', cast(:PersonEncrypHIV_setDT as timestamp));
			", $params);
		}

		$query = "
			select
				(right('00' || cast(:EncrypHIVTerr_Code as varchar), 2)
					|| '-' || right(cast(date_part('year', cast(:PersonEncrypHIV_setDT as timestamp)) as varchar), 2)
					|| '-' || left(:Person_SurName, 1)
					|| '-' || right('0000' || cast(:PersonEncrypHIV_Num as varchar), 4)
				) as \"PersonEncrypHIV_Encryp\",
				(select EncrypHIVTerr_id
					from v_EncrypHIVTerr
					where EncrypHIVTerr_Code = :EncrypHIVTerr_Code
					limit 1
				) as \"EncrypHIVTerr_id\",
				:EncrypHIVTerr_Code as \"EncrypHIVTerr_Code\"
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->queryResult($query, $params);
		if (!$this->isSuccessful($result)) {
			return $result;
		}
		return array(array(
			'success' => true,
			'PersonEncrypHIV_Encryp' => $result[0]['PersonEncrypHIV_Encryp'],
			'EncrypHIVTerr_id' => $result[0]['EncrypHIVTerr_id'],
			'EncrypHIVTerr_Code' => $result[0]['EncrypHIVTerr_Code'],
			'Error_Msg' => ''
		));
	}

	/**
	 * Проверка существования шифра у пациента
	 */
	function checkPersonEncrypHIVExists($data) {
		$params = array('Person_id' => $data['Person_id']);

		$query = "
			select
				count(PersonEncrypHIV_id) as \"Count\"
			from v_PersonEncrypHIV
			where Person_id = :Person_id
			limit 1
		";

		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('', 'Ошибка при проверке существования шифра');
		}
		if ($count > 0) {
			return $this->createError('', 'Выбранному пациенту уже присвоен шифр');
		}
		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Сохранение шифра
	 */
	function savePersonEncrypHIV($data) {
		$this->beginTransaction();
		$params = array(
			'PersonEncrypHIV_id' => !empty($data['PersonEncrypHIV_id'])?$data['PersonEncrypHIV_id']:null,
			'PersonEncrypHIV_Encryp' => $data['PersonEncrypHIV_Encryp'],
			'PersonEncrypHIV_setDT' => $data['PersonEncrypHIV_setDT'],
			'EncrypHIVTerr_id' => $data['EncrypHIVTerr_id'],
			'Person_id' => !empty($data['Person_id'])?$data['Person_id']:null,
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $data['Server_id']
		);

		$EncrypHIVTerr_Code = $this->getEncrypHIVTerrCode($params['EncrypHIVTerr_id']);
		if ($EncrypHIVTerr_Code === false) {
			return $this->createError('','Ошибка при определении кода территории');
		}

		$old_data = array();
		if (!empty($params['PersonEncrypHIV_id'])) {
			$old_data = $this->queryResult("
				select
					PEH.Person_id as \"Person_id\",
					EHT.EncrypHIVTerr_Code as \"EncrypHIVTerr_Code\"
				from v_PersonEncrypHIV PEH
				left join EncrypHIVTerr EHT on EHT.EncrypHIVTerr_id = PEH.EncrypHIVTerr_id
				where PersonEncrypHIV_id = :PersonEncrypHIV_id
				limit 1
			", $params);
			if (!is_array($old_data) || count($old_data) == 0) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при получении данных шифрования');
			}
		}

		$query = "
			select
				PEH.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\"
			from v_PersonEncrypHIV PEH
			where
				substring(PEH.PersonEncrypHIV_Encryp, 4, 2) = substring(:PersonEncrypHIV_Encryp, 4, 2)
				and substring(PEH.PersonEncrypHIV_Encryp, 9, 4) = substring(:PersonEncrypHIV_Encryp, 9, 4)
				and PEH.PersonEncrypHIV_id <> coalesce(:PersonEncrypHIV_id, 0)
			limit 1
		";
		$result = $this->queryResult($query, $params);
		if (!is_array($result)) {
			$this->rollbackTransaction();
			return $this->createError('','Ошибка при проверке уникальности шифра');
		}
		if (count($result) > 0 && !empty($result[0]['PersonEncrypHIV_Encryp'])) {
			$this->rollbackTransaction();
			return $this->createError('',"В системе уже сохранен шифр {$result[0]['PersonEncrypHIV_Encryp']}, комбинация второй и последней группы цифр должна быть уникальна, сохранение невозможно");
		}

		if (empty($params['Person_id'])) {
			//Добавление анонимного пациента
			$query = "
				select
					Person_id as \"Person_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PersonAll_ins(
					PersonSurName_SurName := :PersonEncrypHIV_Encryp,
					Server_id := :Server_id,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->queryResult($query, $params);
			if (!$this->isSuccessful($result)) {
				$this->rollbackTransaction();
				return $result;
			}
			$params['Person_id'] = $result[0]['Person_id'];
		}

		//Помечает пациента как зашифрованного
		$this->load->model('Person_model');
		$result = $this->Person_model->updatePerson(array(
			'Person_id' => $params['Person_id'],
			'pmUser_id' => $params['pmUser_id'],
			'Person_IsEncrypHIV' => 2
		));
		if (!$this->isSuccessful($result)) {
			$this->rollbackTransaction();
			return $result;
		}
		
		if (isset($params['PersonEncrypHIV_id']) && $EncrypHIVTerr_Code == 20) {
			// Изменилась фамилия анонимного пациента
			$sql = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from p_PersonSurName_ins(
					Server_id := :Server_id,
					Person_id := :Person_id,
					PersonSurName_SurName := :PersonSurName_SurName,
					pmUser_id := :pmUser_id
				)
			";
			$result = $this->queryResult($sql, array(
				'Server_id' => $params['Server_id'],
				'Person_id' => $params['Person_id'],
				'PersonSurName_SurName' => $params['PersonEncrypHIV_Encryp'],
				'pmUser_id' => $params['pmUser_id'],
			));
			if (!$this->isSuccessful($result)) {
				$this->rollbackTransaction();
				return $result;
			}
		}

		if (empty($params['PersonEncrypHIV_id'])) {
			$procedure = 'p_PersonEncrypHIV_ins';
		} else {
			$procedure = 'p_PersonEncrypHIV_upd';
		}

		$query = "
			select
				PersonEncrypHIV_id as \"PersonEncrypHIV_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
            from {$procedure}(
				PersonEncrypHIV_id := :PersonEncrypHIV_id,
				PersonEncrypHIV_Encryp := :PersonEncrypHIV_Encryp,
				PersonEncrypHIV_setDT := :PersonEncrypHIV_setDT,
				EncrypHIVTerr_id := :EncrypHIVTerr_id,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->queryResult($query, $params);
		if (!$this->isSuccessful($result)) {
			$this->rollbackTransaction();
			return $result;
		}

		if (count($old_data) > 0 && $old_data[0]['Person_id'] != $params['Person_id']) {
			//Обработка смены пациента в шифре
			$this->isAllowTransaction = false;
			$resp = $this->doPersonEncrypHIVTransfer(array(
				'OldEncrypHIVTerr_Code' => $old_data[0]['EncrypHIVTerr_Code'],
				'OldPerson_id' => $old_data[0]['Person_id'],
				'NewPerson_id' => $params['Person_id'],
				'PersonEncrypHIV_setDT' => $params['PersonEncrypHIV_setDT'],
				'pmUser_id' => $data['pmUser_id'],
				'session' => $data['session']
			));
			$this->isAllowTransaction = true;
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();
		return array(array(
			'success' => true,
			'PersonEncrypHIV_id' => $result[0]['PersonEncrypHIV_id'],
			'Person_id' => $params['Person_id'],
			'Error_Msg' => ''
		));
	}

	/**
	 * Удаление шифра
	 */
	function deletePersonEncrypHIV($data) {
		$this->beginTransaction();
		$params = array(
			'PersonEncrypHIV_id' => $data['PersonEncrypHIV_id'],
			'pmUser_id' => $data['pmUser_id'],
		);

		$encrypData = $this->getFirstRowFromQuery("
			select
				PEH.Person_id as \"Person_id\",
				EHT.EncrypHIVTerr_Code as \"EncrypHIVTerr_Code\"
			from v_PersonEncrypHIV PEH
				left join EncrypHIVTerr EHT on EHT.EncrypHIVTerr_id = PEH.EncrypHIVTerr_id
			where PEH.PersonEncrypHIV_id = :PersonEncrypHIV_id
			limit 1
		", $params);
		if (!is_array($encrypData)) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при получении данных удаляемого шифра');
		}

		if ($encrypData['EncrypHIVTerr_Code'] == 20 && empty($data['ignoreAnonymWarning'])) {
			return array(array(
				'Error_Msg' => 'YesNo',
				'Error_Code' => 101,
				'Alert_Msg' => 'При удалении шифра у данного пациента будут удалены все случаи лечения, привязанные к данному шифру.'
			));
		}

		$this->load->model('Person_model');
		$result = $this->Person_model->updatePerson(array(
			'Person_id' => $encrypData['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Person_IsEncrypHIV' => 1
		));
		if (!$this->isSuccessful($result)) {
			$this->rollbackTransaction();
			return $result;
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonEncrypHIV_del(
				PersonEncrypHIV_id := :PersonEncrypHIV_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->queryResult($query, $params);
		if (!$this->isSuccessful($result)) {
			$this->rollbackTransaction();
			return $result;
		}

		$this->commitTransaction();
		return array(array('success' => true, 'Error_Msg' => ''));
	}

	/**
	 * Возвращает список шифров
	 */
	function loadPersonEncrypHIVGrid($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['Person_SurName'])) {
			$filters[] = "PS.Person_SurName ilike :Person_SurName";
			$params['Person_SurName'] = $data['Person_SurName'] . '%';
		}
		if (!empty($data['Person_FirName'])) {
			$filters[] = "PS.Person_FirName ilike :Person_FirName";
			$params['Person_FirName'] = $data['Person_FirName'] . '%';
		}
		if (!empty($data['Person_SecName'])) {
			$filters[] = "PS.Person_SecName ilike :Person_SecName";
			$params['Person_SecName'] = $data['Person_SecName'] . '%';
		}
		if (!empty($data['Person_BirthDay'])) {
			$filters[] = "PS.Person_BirthDay = :Person_BirthDay";
			$params['Person_BirthDay'] = $data['Person_BirthDay'];
		}
		if (!empty($data['EncrypHIVTerr_id'])) {
			$filters[] = "EHT.EncrypHIVTerr_id = :EncrypHIVTerr_id";
			$params['EncrypHIVTerr_id'] = $data['EncrypHIVTerr_id'];
		}
		if (!empty($data['Sex_id'])) {
			$filters[] = "PS.Sex_id = :Sex_id";
			$params['Sex_id'] = $data['Sex_id'];
		}
		if (!empty($data['PersonType_id'])) {
			switch($data['PersonType_id']) {
				case 2:
					$filters[] = "coalesce(EHT.EncrypHIVTerr_Code, 0) = 20";
					break;
				case 3:
					$filters[] = "coalesce(EHT.EncrypHIVTerr_Code, 0) <> 20";
					break;
			}
		}

		$filters_str = implode(' and ', $filters);
		$query = "
			select
				-- select
				PEH.PersonEncrypHIV_id as \"PersonEncrypHIV_id\",
				PEH.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				to_char(PEH.PersonEncrypHIV_setDT, 'dd.mm.yyyy') as \"PersonEncrypHIV_setDT\",
				PS.Person_id as \"Person_id\",
				PS.Person_SurName || coalesce(' ' || PS.Person_FirName,'') || coalesce(' ' || PS.Person_SecName,'') as \"Person_Fio\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				S.Sex_id as \"Sex_id\",
				S.Sex_Name as \"Sex_Name\",
				LpuAttach.Lpu_id as \"LpuAttach_id\",
				LpuAttach.Lpu_Nick as \"LpuAttach_Name\"
				-- end select
			from
				-- from
				v_PersonEncrypHIV PEH
				left join v_PersonState PS on PS.Person_id = PEH.Person_id
				left join v_Sex S on S.Sex_id = PS.Sex_id
				left join EncrypHIVTerr EHT on EHT.EncrypHIVTerr_id = PEH.EncrypHIVTerr_id
				left join lateral(
					select
						t.Lpu_id
					from v_PersonCard t
					where t.Person_id = PS.Person_id
						and t.LpuAttachType_id = 1
						and t.PersonCard_endDate is null
				) PC on true
				left join v_Lpu LpuAttach on LpuAttach.Lpu_id = PC.Lpu_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				PEH.PersonEncrypHIV_setDT
				-- end order by
		";

		$response = array();
		$count_result = $this->queryResult(getCountSQLPH($query),$params);
		if (!is_array($count_result)) {
			return false;
		} else {
			$response['totalCount']=$count_result[0]['cnt'];
		}

		$data_result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']),$params);
		if (!is_array($data_result)) {
			return false;
		} else {
			$response['data']=$data_result;
		}

		return $response;
	}

	/**
	 * Получения данных шифрования для редактирования
	 */
	function loadPersonEncrypHIVForm($data) {
		$params = array('PersonEncrypHIV_id' => $data['PersonEncrypHIV_id']);

		$query = "
			select
				PEH.PersonEncrypHIV_id as \"PersonEncrypHIV_id\",
				PEH.Person_id as \"Person_id\",
				PEH.EncrypHIVTerr_id as \"EncrypHIVTerr_id\",
				PEH.PersonEncrypHIV_Encryp as \"PersonEncrypHIV_Encryp\",
				to_char(PEH.PersonEncrypHIV_setDT, 'dd.mm.yyyy') as \"PersonEncrypHIV_setDT\"
			from v_PersonEncrypHIV PEH
			where PEH.PersonEncrypHIV_id = :PersonEncrypHIV_id
			limit 1
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Перенос случаев лечения при смене пациента в шифре
	 */
	function doPersonEncrypHIVEvnTransfer($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'Person_did' => $data['Person_did'],
			'PersonEncrypHIV_setDT' => $data['PersonEncrypHIV_setDT'],
		);
		$query = "
			with mv as (
				select
					Person_id,
					Server_id,
					PersonEvn_id
				from dbo.v_PersonState
				where Person_id = :Person_id
				limit 1
			)

			update dbo.Evn
			set
				Server_id = (select Server_id from mv),
				PersonEvn_id = (select PersonEvn_id from mv),
				Person_id = (select Person_id from mv),
				Evn_updDT = dbo.tzgetdate()
			from
				dbo.Evn e
				inner join dbo.v_PersonEvn pe on e.Server_id = pe.Server_id
					and e.PersonEvn_id = pe.PersonEvn_id
				inner join Lpu l on l.Lpu_id = e.Lpu_id
			where
				pe.Person_id = :Person_did
				and e.Evn_setDT >= :PersonEncrypHIV_setDT
				and coalesce(l.Lpu_IsSecret,1) = 2
			returning 0 as \"Error_Code\", '' as \"Error_Msg\";
		";
		//echo getDebugSQL($query, $params);exit;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение обновленных данных шифра при смене пациента
	 */
	function changePersonInPersonEncrypHIV($data) {
		$query = "
			select
				PEH.PersonEncrypHIV_id as \"PersonEncrypHIV_id\",
				PEH.Person_id as \"Person_id\",
				substring(PEH.PersonEncrypHIV_Encryp, 9, 4) as \"PersonEncrypHIV_Num\",
				to_char(PEH.PersonEncrypHIV_setDT, 'yyyy-mm-dd') as \"PersonEncrypHIV_setDT\",
				EHT.EncrypHIVTerr_id as \"EncrypHIVTerr_id\",
				EHT.EncrypHIVTerr_Code as \"EncrypHIVTerr_Code\"
			from v_PersonEncrypHIV PEH
				left join v_EncrypHIVTerr EHT on EHT.EncrypHIVTerr_id = PEH.EncrypHIVTerr_id
			where PEH.PersonEncrypHIV_id = :PersonEncrypHIV_id
			limit 1
		";
		$encryp_data = $this->getFirstRowFromQuery($query, $data);
		if ($encryp_data === false) {
			return $this->createError('', 'Ошибка при получении данных шифрования пациента');
		}

		$new_person_id = !empty($data['Person_nid'])?$data['Person_nid']:null;
		$old_person_id = $encryp_data['Person_id'];
		$old_terr_code = $encryp_data['EncrypHIVTerr_Code'];

		if (!empty($new_person_id)) {
			//Проверяет наличие шифра у пациента
			$result = $this->checkPersonEncrypHIVExists(array('Person_id' => $new_person_id));
			if (!$this->isSuccessful($result)) {
				return $result;
			}
		}

		$result = $this->getPersonEncrypHIVEncryp(array(
			'Person_id' => $new_person_id,
			'PersonEncrypHIV_Num' => $encryp_data['PersonEncrypHIV_Num'],
			'PersonEncrypHIV_setDT' => $encryp_data['PersonEncrypHIV_setDT'],
			'default_EncrypHIVTerr_Code' => $encryp_data['EncrypHIVTerr_Code']
		));
		if (!$this->isSuccessful($result)) {
			return $result;
		}
		$encryp_data['Person_id'] = $new_person_id;
		$encryp_data['PersonEncrypHIV_Encryp'] = $result[0]['PersonEncrypHIV_Encryp'];
		$encryp_data['PersonEncrypHIV_setDT'] = ConvertDateEx($encryp_data['PersonEncrypHIV_setDT'], '-', '.');
		$encryp_data['EncrypHIVTerr_id'] = $result[0]['EncrypHIVTerr_id'];
		$encryp_data['EncrypHIVTerr_Code'] = $result[0]['EncrypHIVTerr_Code'];

		return array(array('success' => true, 'encryp_data' => $encryp_data));
	}

	/**
	 * Обработка смены пациента в шифре
	 */
	function doPersonEncrypHIVTransfer($data) {
		$this->load->model('Utils_model');
		$this->load->model('Person_model');
		$this->beginTransaction();
		$info_msg = '';

		$new_person_id = $data['NewPerson_id'];
		$old_person_id = $data['OldPerson_id'];
		$old_terr_code = $data['OldEncrypHIVTerr_Code'];

		$records = array(
			array('IsMainRec' => 1,'Person_id' => $new_person_id),
			array('IsMainRec' => 0,'Person_id' => $old_person_id),
		);
		if ($old_terr_code == 20) {
			//Меняем шифр c анонимного на реального пациента.
			//Объединяем анонимного пациента с реальным.
			$response = $this->Utils_model->doPersonMerge(array(
				'session' => $data['session'],
				'Records' => $records,
				'pmUser_id' => $data['pmUser_id']
			));
			if (!$this->isSuccessful($response)) {
				$this->rollbackTransaction();
				return $response;
			}
			if (!empty($response[0]['Success_Msg'])) {
				$this->rollbackTransaction();
				return $this->createError('', $response[0]['Success_Msg']);
			}
		} else {
			//Меняем шифр с реального пациента.
			//Переносим случаи лечения.
			$response = $this->doPersonEncrypHIVEvnTransfer(array(
				'Person_id' => $new_person_id,
				'Person_did' => $old_person_id,
				'PersonEncrypHIV_setDT' => $data['PersonEncrypHIV_setDT']
			));
			if (!$this->isSuccessful($response)) {
				$this->rollbackTransaction();
				return $response;
			}
			if (!empty($response[0]['Success_Msg'])) {
				$this->rollbackTransaction();
				return $this->createError('', $response[0]['Success_Msg']);
			}
			if (!empty($response[0]['Info_Msg'])) {
				$info_msg = $response[0]['Info_Msg'];
			}

			//Снимаем флаг шифрования
			$result = $this->Person_model->updatePerson(array(
				'Person_id' => $old_person_id,
				'pmUser_id' => $data['pmUser_id'],
				'Person_IsEncrypHIV' => 1
			));
			if (!$this->isSuccessful($result)) {
				$this->rollbackTransaction();
				return $result;
			}
		}

		$this->commitTransaction();
		return array(array('success' => true, 'Person_id' => $new_person_id, 'Info_Msg' => $info_msg, 'Error_Msg' => ''));
	}
}