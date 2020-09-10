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
class PersonRegisterEndo_model extends swModel {
	/**
	 * Сохранение
	 */
	function savePersonRegisterEndo($data)
	{
		// Проверяем наличие человека в регистре по данному типу протезирования
		$resp = $this->queryResult("
			select
				PersonRegisterEndo_id
			from
				v_PersonRegisterEndo pre (nolock)
				inner join v_PersonRegister pr (nolock) on pr.PersonRegister_id = pre.PersonRegister_id
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
			$data['PersonRegister_id'] = $this->getFirstResultFromQuery("select PersonRegister_id from v_PersonRegisterEndo (nolock) where PersonRegisterEndo_id = :PersonRegisterEndo_id", array(
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
			declare
				@PersonRegister_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonRegister_id = :PersonRegister_id;

			exec {$proc_reg}
				@PersonRegister_id = @PersonRegister_id output,
				@PersonRegisterType_id = 32, -- Эндопротезирование
				@Person_id = :Person_id,
				@PersonRegister_Code = :PersonRegister_Code,
				@Diag_id = :Diag_id,
				@Lpu_iid = :Lpu_iid,
				@MedPersonal_iid = :MedPersonal_iid,
				@PersonRegister_setDate = :PersonRegister_setDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output

			select @PersonRegister_id as PersonRegister_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$resp = $this->queryResult($query, $data);
		if (empty($resp[0]['PersonRegister_id'])) {
			return array('Error_Msg' => 'Ошибка сохранения записи регистра');
		}

		$data['PersonRegister_id'] = $resp[0]['PersonRegister_id'];

		// затем расширенную запись регистра
		$query = "
			declare
				@PersonRegisterEndo_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @PersonRegisterEndo_id = :PersonRegisterEndo_id;

			exec {$proc}
				@PersonRegisterEndo_id = @PersonRegisterEndo_id output,
				@PersonRegister_id = :PersonRegister_id,
				@ProsthesType_id = :ProsthesType_id,
				@CategoryLifeDegreeType_id = :CategoryLifeDegreeType_id,
				@PersonRegisterEndo_obrDate = :PersonRegisterEndo_obrDate,
				@PersonRegisterEndo_callDate = :PersonRegisterEndo_callDate,
				@PersonRegisterEndo_hospDate = :PersonRegisterEndo_hospDate,
				@PersonRegisterEndo_operDate = :PersonRegisterEndo_operDate,
				@PersonRegisterEndo_Contacts = :PersonRegisterEndo_Contacts,
				@PersonRegisterEndo_Comment = :PersonRegisterEndo_Comment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output

			select @PersonRegisterEndo_id as PersonRegisterEndo_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				pre.PersonRegisterEndo_id,
				pr.Person_id,
				pr.PersonRegister_id,
				pr.PersonRegister_Code,
				pr.Diag_id,
				pre.CategoryLifeDegreeType_id,
				pre.ProsthesType_id,
				pr.Lpu_iid,
				pr.MedPersonal_iid,
				convert(varchar(10), pre.PersonRegisterEndo_obrDate, 104) as PersonRegisterEndo_obrDate,
				convert(varchar(10), pr.PersonRegister_setDate, 104) as PersonRegister_setDate,
				convert(varchar(10), pre.PersonRegisterEndo_callDate, 104) as PersonRegisterEndo_callDate,
				convert(varchar(10), pre.PersonRegisterEndo_hospDate, 104) as PersonRegisterEndo_hospDate,
				convert(varchar(10), pre.PersonRegisterEndo_operDate, 104) as PersonRegisterEndo_operDate,
				pre.PersonRegisterEndo_Contacts,
				pre.PersonRegisterEndo_Comment
			from
				v_PersonRegisterEndo pre (nolock)
				inner join v_PersonRegister pr (nolock) on pr.PersonRegister_id = pre.PersonRegister_id
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
		$data['PersonRegister_id'] = $this->getFirstResultFromQuery("select PersonRegister_id from v_PersonRegisterEndo (nolock) where PersonRegisterEndo_id = :PersonRegisterEndo_id", array(
			'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id']
		));
		if (empty($data['PersonRegister_id'])) {
			$data['PersonRegister_id'] = null;
		}

		$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_PersonRegisterEndo_del
					@PersonRegisterEndo_id = :PersonRegisterEndo_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

		$this->queryResult($query, array(
			'PersonRegisterEndo_id' => $data['PersonRegisterEndo_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!empty($data['PersonRegister_id'])) {
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);

				exec p_PersonRegister_del
					@PersonRegister_id = :PersonRegister_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output

				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$this->queryResult($query, array(
				'PersonRegister_id' => $data['PersonRegister_id'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		return array('Error_Msg' => '');
	}
}