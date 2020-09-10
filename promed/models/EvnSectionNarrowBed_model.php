<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('EvnAbstract_model.php');

/**
 * EvnSectionNarrowBed_model - Модель "Движение по узким койкам"
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       swan developers
 * @version      09.2014
 *
 * @property-read int $rid КВС
 * @property-read int $pid Движение в отделении
 * @property-read DateTime $setDT Дата и время поступления
 * @property-read string $setDate Дата поступления в формате Y-m-d
 * @property-read string $setTime Время поступления в формате H:i
 * @property-read DateTime $disDT Дата и время исхода из отделения
 * @property-read string $disDate Дата исхода из отделения в формате Y-m-d
 * @property-read string $disTime Время исхода из отделения в формате H:i
 * @property-read int $LpuSection_id Отделение
 *
 * @property-read EvnSection_model $parent
 */
class EvnSectionNarrowBed_model extends EvnAbstract_model
{
	protected $_parentClass = 'EvnSection_model';

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['alias'] = 'EvnSectionNarrowBed_id';
		$arr[self::ID_KEY]['label'] = 'Идентификатор случая движения пациента по узким койкам в стационаре';
		$arr['pid']['alias'] = 'EvnSectionNarrowBed_pid';
		$arr['pid']['label'] = 'Идентификатор случая движения пациента в стационаре';
		$arr['person_id']['save'] = 'trim';
		$arr['setdate']['label'] = 'Дата поступления';
		$arr['setdate']['alias'] = 'EvnSectionNarrowBed_setDate';
		$arr['settime']['label'] = 'Время поступления';
		$arr['settime']['alias'] = 'EvnSectionNarrowBed_setTime';
		$arr['settime']['save'] = 'trim|required';
		$arr['diddt']['alias'] = 'EvnSectionNarrowBed_didDT';
		$arr['disdt'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
				self::PROPERTY_DATE_TIME,
			),
			'alias' => 'EvnSectionNarrowBed_disDT',
			'applyMethod'=>'_applyDisDT',
		);
		$arr['disdate'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyDisDT
			'alias' => 'EvnSectionNarrowBed_disDate',
			'label' => 'Дата выписки',
			'save' => 'trim',
			'type' => 'date'
		);
		$arr['distime'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
				self::PROPERTY_NOT_LOAD,
			),
			// только для извлечения из POST и обработки методом _applyDisDT
			'alias' => 'EvnSectionNarrowBed_disTime',
			'label' => 'Время выписки',
			'save' => 'trim',
			'type' => 'time'
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_id',
			'label' => 'Отделение',
			'save' => 'trim|required',
			'type' => 'id'
		);
		return $arr;
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
		$this->_processingDtValue($column, $value, 'dis');
		return parent::_processingSavedValue($column, $value);
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 57;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnSectionNarrowBed';
	}

	/**
	 * construct
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_DELETE,
		));
	}

	/**
	 * @param $data
	 * @return array
	 */
	function deleteEvnSectionNarrowBed($data)
	{
		return array($this->doDelete($data));
	}


	/**
	 * @param $data
	 * @return bool
	 */
	function loadEvnSectionNarrowBedGrid($data) {
		$query = "
			select
				ESU.EvnSectionNarrowBed_id,
				ESU.EvnSectionNarrowBed_pid,
				ESU.PersonEvn_id,
				ESU.Server_id,
				LS.LpuSection_id,
				LS.LpuSectionBedProfile_id,
				convert(varchar(10), ESU.EvnSectionNarrowBed_disDate, 104) as EvnSectionNarrowBed_disDate,
				ISNULL(ESU.EvnSectionNarrowBed_disTime, '') as EvnSectionNarrowBed_disTime,
				convert(varchar(10), ESU.EvnSectionNarrowBed_setDate, 104) as EvnSectionNarrowBed_setDate,
				ISNULL(ESU.EvnSectionNarrowBed_setTime, '') as EvnSectionNarrowBed_setTime,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name
			from v_EvnSectionNarrowBed ESU with (nolock)
				inner join LpuSection LS with (nolock) on LS.LpuSection_id = ESU.LpuSection_id
				inner join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
			where
				ESU.EvnSectionNarrowBed_pid = :EvnSectionNarrowBed_pid
		";
		$result = $this->db->query($query, array('EvnSectionNarrowBed_pid' => $data['EvnSectionNarrowBed_pid']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		parent::_validate();
		if (self::SCENARIO_DO_SAVE == $this->scenario) {
			// Проверка на пересечения периодов (refs #4984)
			$queryParams = array(
				'id' => empty($this->id) ? null : $this->id,
				'pid' => $this->pid,
				'setDT' => isset($this->setDT) ? $this->setDT->format('Y-m-d H:i') : null,
				'disDT' => isset($this->disDT) ? $this->disDT->format('Y-m-d H:i') : null,
			);
			$query = "
				declare
					@setDT datetime = cast(:setDT as datetime),
					@disDT datetime = cast(:disDT as datetime);

				select
					COUNT(ESU.EvnSectionNarrowBed_id) as CNT
				from v_EvnSectionNarrowBed ESU with (nolock)
					inner join LpuSection LS with (nolock) on LS.LpuSection_id = ESU.LpuSection_id
					inner join LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LS.LpuSectionProfile_id
				where
					ESU.EvnSectionNarrowBed_pid = :pid
					AND (
						(ESU.EvnSectionNarrowBed_setDT < @setDT
						AND (ESU.EvnSectionNarrowBed_disDT is null OR ESU.EvnSectionNarrowBed_disDT > @setDT))
						OR (ESU.EvnSectionNarrowBed_setDT < @disDT
						AND (ESU.EvnSectionNarrowBed_disDT is null OR ESU.EvnSectionNarrowBed_disDT > @disDT))
						OR (ESU.EvnSectionNarrowBed_setDT > @setDT
						AND (ESU.EvnSectionNarrowBed_disDT is null OR ESU.EvnSectionNarrowBed_disDT < @disDT))
					)
			";
			if (isset($this->id)){
				$query .= " AND ESU.EvnSectionNarrowBed_id <> :id";
			}
			//throw new Exception(getDebugSQL($query, $queryParams));
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$count = $result->result('array');
				if ( $count[0]['CNT'] > 0 ) {
					throw new Exception('Периоды узких коек не могут пересекаться');
				}
			}
			if ( empty($this->disDT) && isset($this->parent->disDT) ) {
				throw new Exception('Дата выписки должна быть указана');
			}
			// Проверка что дата выписки движения больше чем дата выписки узкой койки. (refs #4984)
			if ( isset($this->disDT) && isset($this->parent->disDT) && $this->parent->disDT < $this->disDT ) {
				throw new Exception('Дата выписки с узкой койки должна быть меньше даты выписки в движении');
			}
		}
	}

	/**
	 * Старый метод сохранения, не годен для использования внутри транзакции
	 * @param $data
	 * @param bool $savingLpuSectionBedProfile
	 * @return array
	 */
	function saveEvnSectionNarrowBed($data, $savingLpuSectionBedProfile = false)
	{
		if ($savingLpuSectionBedProfile) {
			$data['scenario'] = self::SCENARIO_AUTO_CREATE;
			return array($this->doSave($data, false));
		} else {
			$data['scenario'] = self::SCENARIO_DO_SAVE;
			return array($this->doSave($data, false));
		}
	}
}
