<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnLeaveAbstract_model.php');

/**
 * EvnDie_model - Модель "Смерть пациента"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $MedPersonal_id Медицинский работник, установивший смерть
 * @property-read int $IsWait Умер в приемном покое
 * @property-read int $DeathPlace_id Место смерти
 * @property-read int $IsAnatom	Необходимость патологоанатомической экспертизы
 * @property-read DateTime $expDT Дата и время экспертизы
 * @property-read string $expDate Дата экспертизы в формате Y-m-d
 * @property-read string $expTime Время экспертизы в формате H:i
 * @property-read int $MedPersonal_aid	Медицинский работник, проводивший вскрытие
 * @property-read int $AnatomWhere_id Место проведения патологоанатомической экспертизы
 * @property-read int $OrgAnatom_id Патологоанатомическая организация
 * @property-read int $LpuSection_aid Отделение, где проводилась экспертиза
 * @property-read int $Lpu_aid ЛПУ, где проводилась экспертиза
 * @property-read int $Diag_aid Патологоанатомический диагноз
 * @property-read int $DiagSetPhase_id Стадия/Фаза
 * @property-read string $PhaseDescr Описание фазы
 */
class EvnDie_model extends EvnLeaveAbstract_model
{
	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnDie_id';
		$arr['pid']['alias'] = 'EvnDie_pid';
		$arr['setdate']['alias'] = 'EvnDie_setDate';
		$arr['settime']['alias'] = 'EvnDie_setTime';
		$arr['disdt']['alias'] = 'EvnDie_disDT';
		$arr['diddt']['alias'] = 'EvnDie_didDT';
		$arr['ukl']['alias'] = 'EvnDie_UKL';
		$arr['medpersonal_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_id',
			'label' => 'Медицинский работник, установивший смерть',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medstafffact_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_id',
			'label' => 'Место работы работника, установившего смерть',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['iswait'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDie_IsWait',
			'label' => 'Умер в приемном покое',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['deathplace_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DeathPlace_id',
			'label' => 'Место смерти',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['isanatom'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDie_IsAnatom',
			'label' => 'Необходимость патологоанатомической экспертизы',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['expdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnDie_expDT',
			'applyMethod'=>'_applyExpDT',
			'dateKey'=>'expdate',
			'timeKey'=>'exptime',
		);
		$arr['expdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyExpDT
			'alias' => 'EvnDie_expDate',
			'label' => 'Дата экспертизы',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['exptime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyExpDT
			'alias' => 'EvnDie_expTime',
			'label' => 'Время экспертизы',
			'save' => 'trim',
			'type' => 'time'
		);
		$arr['medpersonal_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_aid',
			'label' => 'Медицинский работник, проводивший вскрытие',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['anatomwhere_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'AnatomWhere_id',
			'label' => 'Место проведения экспертизы',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['organatom_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'OrgAnatom_id',
			'label' => 'Патологоанатомическая организация',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpu_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Lpu_aid',
			'label' => 'ЛПУ, где проводилась экспертиза',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['lpusection_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_aid',
			'label' => 'Отделение, где проводилась экспертиза',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diag_aid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Diag_aid',
			'label' => 'Патологоанатомический диагноз',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['diagsetphase_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'DiagSetPhase_id',
			'label' => 'Стадия/Фаза',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['phasedescr'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDie_PhaseDescr',
			'label' => 'Описание фазы',
			'save' => 'trim',
			'type' => 'string'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 38;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnDie';
	}

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Извлечение даты и времени экспертизы из входящих параметров
	 * @param $data
	 * @return bool
	 */
	protected function _applyExpDT($data)
	{
		return $this->_applyDT($data, 'exp');
	}

	/**
	 * Дополнительная обработка значения атрибута сохраненного объекта из БД
	 * перед записью в модель
	 * @param string $column Имя колонки в строчными символами
	 * @param mixed $value Значение. Значения, которые в БД имеют тип datetime, являются экземлярами DateTime.
	 * @return mixed
	 * @throws Exception
	 */
	protected function _processingSavedValue($column, $value)
	{
		$this->_processingDtValue($column, $value, 'exp');
		return parent::_processingSavedValue($column, $value);
	}


	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_AUTO_CREATE))) {
			if (empty($this->MedPersonal_id)) {
				throw new Exception('Не указан врач, установивший смерть', 400);
			}
			if (empty($this->IsAnatom)) {
				throw new Exception('Не указан признак необходимости проведения экспертизы', 400);
			}
			if (2 == $this->IsAnatom) {
				/*if (empty($this->expDT)) {
					throw new Exception('Не указана дата проведения экспертизы', 400);
				}
				if (empty($this->Diag_aid)) {
					throw new Exception('Не указан основной патологоанатомический диагноз', 400);
				}
				if (empty($this->AnatomWhere_id)) {
					throw new Exception('Не указано место проведения экспертизы', 400);
				}
				switch ( $this->AnatomWhere_id ) {
					case 1:
						if ( empty($this->LpuSection_aid) ) {
							throw new Exception('Не указано отделение, в котором проводилась экспертиза', 400);
						}
						if ( empty($this->MedPersonal_aid) ) {
							throw new Exception('Не указан врач-патологоанатом', 400);
						}
						break;
					case 2:
						if ( empty($this->Lpu_aid) ) {
							throw new Exception('Не указано ЛПУ, в котором проводилась экспертиза');
						}
						$this->setAttribute('organatom_id', null);
						$this->setAttribute('lpusection_aid', null);
						$this->setAttribute('medpersonal_aid', null);
						break;
					case 3:
						if ( empty($this->OrgAnatom_id) ) {
							throw new Exception('Не указана организация, в которой проводилась экспертиза');
						}
						$this->setAttribute('lpu_aid', null);
						$this->setAttribute('lpusection_aid', null);
						$this->setAttribute('medpersonal_aid', null);
						break;
				}*/
			} else {
				$this->setAttribute('isanatom', 1);
				$this->setAttribute('expdt', null);
				$this->setAttribute('diag_aid', null);
				$this->setAttribute('anatomwhere_id', null);
				$this->setAttribute('organatom_id', null);
				$this->setAttribute('lpu_aid', null);
				$this->setAttribute('lpusection_aid', null);
				$this->setAttribute('medpersonal_aid', null);
			}
		}
	}


	/**
	 * Удаление
	 */
	function deleteEvnDie($data)
	{
		return array($this->doDelete($data));
	}

	/**
	 * Сохранение
	 */
	function saveEvnDie($data)
	{
		if (empty($data['scenario'])) {
			$data['scenario'] = self::SCENARIO_DO_SAVE;
		}
		return array($this->doSave($data));
	}

	/**
	 * Получение данных для формы
	 */
	function loadEvnDieEditForm($data) {
		$query = "
			SELECT TOP 1
				ED.EvnDie_id,
				ED.EvnDie_pid,
				convert(varchar(10), ED.EvnDie_setDT, 104) as EvnDie_setDate,
				ED.EvnDie_setTime,
				ROUND(ED.EvnDie_UKL, 3) as EvnDie_UKL,
				ED.MedPersonal_id as MedStaffFact_id,
				ED.EvnDie_IsWait,
				ED.EvnDie_IsAnatom,
				convert(varchar(10), ED.EvnDie_expDT, 104) as EvnDie_expDate,
				ED.EvnDie_expTime,
				ED.AnatomWhere_id,
				ED.LpuSection_aid,
				ISNULL(ED.OrgAnatom_id, ED.Lpu_aid) as Org_aid,
				ED.MedPersonal_aid as MedStaffFact_aid,
				ED.Diag_aid,
				ED.Person_id,
				ED.PersonEvn_id,
				ED.Server_id
			FROM
				v_EvnDie ED with (nolock)
			WHERE (1 = 1)
				and ED.EvnDie_id = :EvnDie_id
				and ED.Lpu_id " . getLpuIdFilter($data) . "
		";
		$result = $this->db->query($query, array(
			'EvnDie_id' => $data['EvnDie_id'],
			'Lpu_id' => $data['Lpu_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Логика после успешного выполнения запроса сохранения объекта
	 * @param array $result Результат выполнения запроса
	 * @throws Exception
	 */
	protected function _afterSave($result)
	{
		parent::_afterSave($result);

		/*$query = "
			select top 1
				ed.Person_id,
				ed.EvnDie_setDate,
				ed.pmUser_insID
			from v_EvnDie ed with (nolock) 
			where ed.EvnDie_id = :EvnDie_id
		";
		$params = array(
			'EvnDie_id' => $result[0]['EvnDie_id']
		);
		$person_data = $this->getFirstRowFromQuery($query, $params);
		if ($person_data === false) {
			throw new Exception('Не удалось получить данные о пациенте', 500);
		}
		$this->load->model('Person_model', 'pmodel');
		$params = array(
			'Person_id' => $person_data['Person_id'],
			'Person_deadDT' => $person_data['EvnDie_setDate'],
			'pmUser_id' => $person_data['pmUser_insID'],
		);
		$this->pmodel->killPerson($params);*/
	}
}
