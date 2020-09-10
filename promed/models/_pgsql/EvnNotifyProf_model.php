<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnNotifyAbstract_model.php');

/**
 * EvnNotifyProf_model - Модель "Извещение по профзаболеванию"
 *
 * @package      Prof
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      12.2014
 *
 * @property-read int $Diag_id Диагноз, справочник МКБ-10
 * @property-read DateTime $diagDate Дата установки
 * @property-read int $MedPersonal_hid Заведующий отделением
 *
 */
class EvnNotifyProf_model extends EvnNotifyAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnNotifyProf_id';
		$arr['pid']['alias'] = 'EvnNotifyProf_pid';
		$arr['setdate']['alias'] = 'EvnNotifyProf_setDate';
		$arr['disdt']['alias'] = 'EvnNotifyProf_disDT';
		$arr['diddt']['alias'] = 'EvnNotifyProf_didDT';
		$arr['nidate']['alias'] = 'EvnNotifyProf_niDate';
		$arr['diag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_id',
			'label' => 'Диагноз',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['medpersonal_hid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_hid',
			'label' => 'Главный врач',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['morbusprofdiag_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MorbusProfDiag_id',
			'label' => 'Заболевание',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['org_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Org_id',
			'label' => 'Организация',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['evnnotifyprof_section'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnNotifyProf_Section',
			'label' => 'Наименование цеха',
			'save' => 'trim|required',
			'type' => 'string'
		);
		$arr['post_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Post_id',
			'label' => 'Профессия',
			'save' => 'trim|required',
			'type' => 'id'
		);
		$arr['lpu_did'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_did',
			'label' => 'МО',
			'save' => 'trim|required',
			'type' => 'id'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 172;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnNotifyProf';
	}

	/**
	 * Определение типа заболевания
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'prof';
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Извлечение даты из входящих параметров
	 * @param array $data
	 * @return bool
	 */
	protected function _applyDiagDate($data)
	{
		return $this->_applyDate($data, 'diagdate');
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		// тут не проверяю обязательные поля, т.к. сохранение только из формы и обязательные поля указаны в правилах
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			$this->_checkIfAlreadyExists();
		}
	}

	/**
	 * Проверка на сущетсвование с таким диагнозом и заболеванием
	 */
	function _checkIfAlreadyExists() {
		// В системе уж сохранено Извещение с аналогичными указанными атрибутами  «Диагноз», «Заболевание»,  «Организация, в которой работает пациент». Сохранение невозможно.
		$query = "
			select
				EvnNotifyProf_id as \"EvnNotifyProf_id\"
			from
				v_EvnNotifyProf enp
			where
				enp.Person_id = :Person_id
				and enp.Diag_id = :Diag_id
				and enp.MorbusProfDiag_id = :MorbusProfDiag_id
				and enp.Org_id = :Org_id
		";

		$result = $this->db->query($query, array(
			'Person_id' => $this->Person_id,
			'Diag_id' => $this->Diag_id,
			'MorbusProfDiag_id' => $this->MorbusProfDiag_id,
			'Org_id' => $this->Org_id
		));

		if (is_object($result)) {
			$resp = $result->result('array');
			if (!empty($resp[0]['EvnNotifyProf_id'])) {
				throw new Exception('В системе уже сохранено извещение с аналогичными указанными атрибутами  «Диагноз», «Заболевание»,  «Организация, в которой работает пациент». Сохранение невозможно.');
			}
		}
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 * @param array $data Массив входящих параметров
	 * @throws Exception
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);
		$this->load->library('swMorbus');
		$this->_params['Morbus_Diag_id'] = $data['Diag_id'];
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		//parent::_afterSave($result);
		$this->load->model('MorbusProf_model');

		// Сохраняем организацию и профессию
		if (array_key_exists('org_id', $this->attributes) && array_key_exists('post_id', $this->attributes)) {
			// обновляем организацию
			$query = "
				select
					job.Job_id as \"Job_id\"
				from
					v_PersonState ps
					inner join v_Job job on job.Job_id = ps.Job_id
				where
					Person_id = :Person_id
					and coalesce(job.Org_id, 0) = coalesce(:Org_id, 0)
					and coalesce(job.Post_id, 0) = coalesce(:Post_id, 0)
				limit 1
			";

			$result = $this->db->query($query, array(
				'Person_id' => $this->attributes['person_id'],
				'Org_id' => $this->attributes['org_id'],
				'Post_id' => $this->attributes['post_id']
			));
			if (is_object($result)) {
				$resp = $result->result('array');
				if (empty($resp[0]['Job_id'])) {
					// добавляем периодику
					$query = "
						select
							Error_Message as \"Error_Msg\",
							Error_Code as \"Error_Code\"
						from p_PersonJob_ins(
							Server_id = :Server_id,
							Person_id = :Person_id,
							PersonJob_insDT = dbo.tzGetDate(),
							Org_id = :Org_id,
							Post_id = :Post_id,
							pmUser_id = :pmUser_id
						)
					";
					$this->db->query($query, array(
						'Server_id' => $this->server_id,
						'Person_id' => $this->attributes['person_id'],
						'Org_id' => $this->attributes['org_id'],
						'Post_id' => $this->attributes['post_id'],
						'pmUser_id' => $this->promedUserId
					));
				}
			}
		}
	}

	/**
	 * Получение данных для формы
	 */
	function doLoadEditForm($data)
	{
		$response = parent::doLoadEditForm($data);
		$response[0]['Diag_id'] = $this->Diag_id;
		$response[0]['MedPersonal_hid'] = $this->MedPersonal_hid;
		$response[0]['MorbusProfDiag_id'] = $this->MorbusProfDiag_id;
		$response[0]['Org_id'] = $this->Org_id;
		$response[0]['EvnNotifyProf_Section'] = $this->EvnNotifyProf_Section;
		$response[0]['Post_id'] = $this->Post_id;
		$response[0]['Lpu_did'] = $this->Lpu_did;
		return $response;
	}

	/**
	 * Получаем данные для проверки наличия извещения/записи регистра
	 * Проверка выполняется из Common_model
	 */
	function loadDataCheckExistsExtended($data)
	{
		$query = "
			select
				M.Morbus_id as \"Morbus_id\"
				,EN.EvnNotifyProf_id as \"EvnNotifyProf_id\"
				,PR.PersonRegister_id as \"PersonRegister_id\"
				,PR.PersonRegisterOutCause_id as \"PersonRegisterOutCause_id\"
				,to_char(EN.EvnNotifyProf_setDT, 'dd.mm.yyyy') as \"EvnNotifyProf_setDate\" -- дата заполнения
				,MPD.MorbusProfDiag_Name as \"MorbusProfDiag_Name\" -- Заболевание
				,org.Org_Name as \"Org_Name\" -- Организация, в которой работает пациент
				,en.EvnNotifyProf_Section as \"EvnNotifyProf_Section\" -- Наименование цеха, отделения, участка, текст 50
				,p.Post_name as \"Post_name\" -- Профессия
				,Lpu.Lpu_Nick as \"Lpu_Name\" -- МО установившая диагноз
			from
				v_MorbusBase MB
				inner join v_Morbus M on MB.MorbusBase_id = M.MorbusBase_id
				inner join v_EvnNotifyProf EN on M.Morbus_id = EN.Morbus_id
				left join v_Lpu Lpu on Lpu.Lpu_id = EN.Lpu_did
				left join v_Org org on org.Org_id = en.Org_id
				left join v_Post p on p.Post_id = en.Post_id
				left join v_PersonRegister PR on M.Morbus_id = PR.Morbus_id
				left join v_MorbusProfDiag MPD on MPD.MorbusProfDiag_id = EN.MorbusProfDiag_id
			where
				MB.Person_id = :Person_id
				and MB.MorbusType_id = :MorbusType_id
				and EN.Diag_id = :Diag_id
			order by
				M.Morbus_disDT ASC, M.Morbus_setDT DESC
			limit 1
		";
		$res = $this->db->query($query, array(
			'Person_id' => $data['Person_id'],
			'Diag_id' => $data['Diag_id'],
			'MorbusType_id' => $this->MorbusType_id,
		));
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем строку печатной формы
	 */
	function doPrint($data)
	{
		$parse_data = $this->loadPrintData($data['EvnNotifyProf_id']);
		if (empty($parse_data)) {
			return 'Не удалось получить данные извещения';
		}
		$this->load->library('parser');
		return $this->parser->parse('print_evnnotifyProf', $parse_data, true);
	}

	/**
	 * Получаем данные для печатной формы
	 */
	protected function loadPrintData($id)
	{
		//Отделение врача, заполнившего Извещение (если несколько, то погружать отделение из посещения / карты ДУ из которого создано Извещение)
		$query = "
			select
				to_char(EN.EvnNotifyProf_setDT, 'dd.mm.yyyy') as \"EvnNotifyProf_setDate\",
				Lpu.Lpu_Nick as \"Lpu_Name\",
				null as \"LpuSection_Name\",
				MP.Person_Fin as \"MedPersonal_Fin\",
				MPH.Person_Fin as \"MedPersonal_Fih\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
				Sex.Sex_Code as \"Sex_Code\",
				Sex.Sex_Name as \"Sex_Name\",
				a.Address_Address as \"Person_Address\",
				to_char(PS.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
				PS.Person_Phone as \"Person_Phone\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
				org.Org_Name as \"Org_Name\",
				en.EvnNotifyProf_Section as \"EvnNotifyProf_Section\",
				p.Post_Name as \"Post_Name\",
				HWFT.HarmWorkFactorType_Name as \"HarmWorkFactorType_Name\"
			from
				v_EvnNotifyProf EN
				inner join v_PersonState PS on PS.Person_id = EN.Person_id
				left join v_Org org on org.Org_id = en.Org_id
				left join v_Post p on p.Post_id = en.Post_id
				left join Sex on Sex.Sex_id = PS.Sex_id
				left join v_MorbusProfDiag MPD on MPD.MorbusProfDiag_id = EN.MorbusProfDiag_id
				left join v_HarmWorkFactorType HWFT on HWFT.HarmWorkFactorType_id = MPD.HarmWorkFactorType_id
				left join v_Address a on a.Address_id = coalesce(PS.PAddress_id,PS.UAddress_id)
				left join Diag on Diag.Diag_id = EN.Diag_id
				left join v_Lpu Lpu on Lpu.Lpu_id = EN.Lpu_did
				left join v_MedPersonal MP on MP.MedPersonal_id = EN.MedPersonal_id
				left join v_MedPersonal MPH on MPH.MedPersonal_id = EN.MedPersonal_hid
			where
				EN.EvnNotifyProf_id = ?
			limit 1
		";
		$res = $this->db->query($query, array($id));
		if ( is_object($res) ) {
			$tmp = $res->result('array');
			if (is_array($tmp) && !empty($tmp)) {
				return $tmp[0];
			}
		}
		return array();
	}
}