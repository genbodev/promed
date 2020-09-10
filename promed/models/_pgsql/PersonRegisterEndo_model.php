<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Модель для регистра по эндо
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2015 Swan Ltd.
 * @author       dimice
 * @version
 */
class PersonRegisterEndo_model extends SwPgModel {
	/**
	 * Сохранение
	 */
	function savePersonRegisterEndo($data)
	{
		// Проверяем наличие человека в регистре по данному типу протезирования
		$resp = $this->queryResult("
			select
				PersonRegisterEndo_id as \"PersonRegisterEndo_id\"
			from
				v_PersonRegisterEndo pre
				inner join v_PersonRegister pr on pr.PersonRegister_id = pre.PersonRegister_id
			where
				pr.Person_id = :Person_id
				and pre.ProsthesType_id = :ProsthesType_id
				and (pre.PersonRegisterEndo_id <> :PersonRegisterEndo_id OR :PersonRegisterEndo_id IS NULL)
		", array(
			'Person_id' => $data['Person_id'],
			'ProsthesType_id' => $data['ProsthesType_id'],
			'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id']
		));

		if (!empty($resp[0]['PersonRegisterEndo_id'])) {
			return array('Error_Msg' => 'Пациент уже находится в регистре по данному типу протезирования');
		}

		$proc = "p_PersonRegisterEndo_ins";
		if (!empty($data['PersonRegisterEndo_id'])) {
			$proc = "p_PersonRegisterEndo_upd";

			// получаем PersonRegister_id
			$data['PersonRegister_id'] = $this->getFirstResultFromQuery("select PersonRegister_id as \"PersonRegister_id\" from v_PersonRegisterEndo where PersonRegisterEndo_id = :PersonRegisterEndo_id", array(
				'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id']
			));
			if (empty($data['PersonRegister_id'])) {
				$data['PersonRegister_id'] = null;
			}
		}

		$proc_reg = "p_PersonRegister_ins";
		if (!empty($data['PersonRegister_id'])) {
			$proc_reg = "p_PersonRegister_upd";
		}

		// сначала вставляем запись регистра
		$query = "
            select 
                PersonRegister_id as \"PersonRegister_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$proc_reg} (
				PersonRegister_id := :PersonRegister_id,
				PersonRegisterType_id := 32, -- Эндопротезирование
				Person_id := :Person_id,
				PersonRegister_Code := :PersonRegister_Code,
				Diag_id := :Diag_id,
				Lpu_iid := :Lpu_iid,
				MedPersonal_iid := :MedPersonal_iid,
				PersonRegister_setDate := :PersonRegister_setDate,
				pmUser_id := :pmUser_id
				)
		";
		$resp = $this->queryResult($query, $data);
		if (empty($resp[0]['PersonRegister_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения записи регистра');
		}

		$data['PersonRegister_id'] = $resp[0]['PersonRegister_id'];

		// затем расширенную запись регистра
		$query = "
            select 
                PersonRegisterEndo_id as \"PersonRegisterEndo_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$proc} (
				PersonRegisterEndo_id := :PersonRegisterEndo_id,
				PersonRegister_id := :PersonRegister_id,
				ProsthesType_id := :ProsthesType_id,
				CategoryLifeDegreeType_id := :CategoryLifeDegreeType_id,
				PersonRegisterEndo_obrDate := :PersonRegisterEndo_obrDate,
				PersonRegisterEndo_callDate := :PersonRegisterEndo_callDate,
				PersonRegisterEndo_hospDate := :PersonRegisterEndo_hospDate,
				PersonRegisterEndo_operDate := :PersonRegisterEndo_operDate,
				PersonRegisterEndo_Contacts := :PersonRegisterEndo_Contacts,
				PersonRegisterEndo_Comment := :PersonRegisterEndo_Comment,
				pmUser_id := :pmUser_id
				)
		";

		$resp = $this->queryResult($query, $data);
		if (empty($resp[0]['PersonRegisterEndo_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения расширенной записи регистра');
		}

		return $resp;
	}

	/**
	 * Загрузка
	 */
	function loadPersonRegisterEndoEditForm($data)
	{
		$query = "
			select
				pre.PersonRegisterEndo_id as \"PersonRegisterEndo_id\",
				pr.Person_id as \"Person_id\",
				pr.PersonRegister_id as \"PersonRegister_id\",
				pr.PersonRegister_Code as \"PersonRegister_Code\",
				pr.Diag_id as \"Diag_id\",
				pre.CategoryLifeDegreeType_id as \"CategoryLifeDegreeType_id\",
				pre.ProsthesType_id as \"ProsthesType_id\",
				pr.Lpu_iid as \"Lpu_iid\",
				pr.MedPersonal_iid as \"MedPersonal_iid\",
				to_char (pre.PersonRegisterEndo_obrDate, 'dd.mm.yyyy') as \"PersonRegisterEndo_obrDate\",
				to_char (pr.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				to_char (pre.PersonRegisterEndo_callDate, 'dd.mm.yyyy') as \"PersonRegisterEndo_callDate\",
				to_char (pre.PersonRegisterEndo_hospDate, 'dd.mm.yyyy') as \"PersonRegisterEndo_hospDate\",
				to_char (pre.PersonRegisterEndo_operDate, 'dd.mm.yyyy') as \"PersonRegisterEndo_operDate\",
				pre.PersonRegisterEndo_Contacts as \"PersonRegisterEndo_Contacts\",
				pre.PersonRegisterEndo_Comment as \"PersonRegisterEndo_Comment\"
			from
				v_PersonRegisterEndo pre
				inner join v_PersonRegister pr on pr.PersonRegister_id = pre.PersonRegister_id
			where
				pre.PersonRegisterEndo_id = :PersonRegisterEndo_id
		";

		return $this->queryResult($query, array(
			'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id']
		));
	}

	/**
	 * Удаление
	 */
	function deletePersonRegisterEndo($data)
	{
		$data['PersonRegister_id'] = $this->getFirstResultFromQuery("select PersonRegister_id as \"PersonRegister_id\" from v_PersonRegisterEndo where PersonRegisterEndo_id = :PersonRegisterEndo_id", array(
			'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id']
		));
		if (empty($data['PersonRegister_id'])) {
			$data['PersonRegister_id'] = null;
		}

		$query = "
                select 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\"
				from p_PersonRegisterEndo_del (
					PersonRegisterEndo_id := :PersonRegisterEndo_id,
					pmUser_id := :pmUser_id
					)
			";

		$this->queryResult($query, array(
			'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($data['PersonRegister_id'])) {
			$query = "
                select 
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\"
				from p_PersonRegister_del (
					PersonRegister_id := :PersonRegister_id,
					pmUser_id := :pmUser_id
					)
			";

			$this->queryResult($query, array(
				'PersonRegister_id' => $data['PersonRegister_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array('Error_Msg' => '');
	}
}