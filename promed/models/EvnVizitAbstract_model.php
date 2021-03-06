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
 * EvnVizitAbstract_model - Модель абстрактного посещения
 *
 * Содержит методы и свойства общие для всех объектов,
 * классы которых наследуют класс EvnVizit
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       Александр Пермяков
 * @version      08.2014
 *
 * @property int $IsInReg Признак нахождения в реестре
 * @property int $IsPaid Признак оплаты
 * @property int $MedStaffFact_id Рабочее место врача required
 * @property int $LpuSection_id Отделение required
 * @property int $MedPersonal_id Врач required
 * @property int $MedPersonal_sid Средний мед. персонал
 * @property int $PayType_id Вид оплаты required
 * @property int $EvnDirection_id Направление
 * @property int $TimetableGraf_id Запись на прием
 * @property int $Mes_id МЭС
 * @property float $Uet УЕТ
 * @property float $UetOMS УЕТ (ОМС)
 *
 * @property string $payTypeSysNick
 *
 * @property-read EvnUsluga_model $EvnUsluga_model
 */
abstract class EvnVizitAbstract_model extends EvnAbstract_model
{
	/**
	 * @var string
	 */
	private $_payTypeSysNick = '';

    /**
     * Конструктор объекта
     */
    function __construct()
    {
        parent::__construct();
    }

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['label'] = 'Идентификатор посещения';
		$arr['pid']['label'] = 'Идентификатор талона амбулаторного пациента';
		$arr['setdate']['label'] = 'Дата посещения';
		$arr['settime']['label'] = 'Время посещения';
		$arr['isinreg'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['ispaid'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['istransit'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_READ_ONLY,
				self::PROPERTY_NOT_SAFE,
			),
		);
		$arr['medstafffact_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedStaffFact_id',
			'label' => 'Рабочее место врача',
			'save' => 'trim|required',
			'type' => 'id',
			'updateTable' => 'EvnVizit'
		);
		$arr['lpusection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'LpuSection_id',
			'label' => 'Отделение',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['medpersonal_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_id',
			'label' => 'Врач',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['paytype_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'PayType_id',
			'label' => 'Вид оплаты',
			'save' => 'required',
			'type' => 'id'
		);
		$arr['evndirection_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'EvnDirection_id',
			'label' => 'Направление',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['timetablegraf_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'TimetableGraf_id',
			'label' => 'Запись на прием',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['mes_id'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'Mes_id',
			'label' => 'МЭС',
			'save' => 'trim',
			'type' => 'id'
		);
		$arr['medpersonal_sid'] = array(
			'properties' => array(
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => 'MedPersonal_sid',
			'label' => 'Средний мед. персонал',
			'save' => 'trim',
			'type' => 'id',
			'updateTable' => 'EvnVizit'
		);
		$arr['uet'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => '_Uet',//указать в наследниках
			'label' => 'УЕТ',
			'save' => 'trim',
			'type' => 'float'
		);
		$arr['uetoms'] = array(
			'properties' => array(
				self::PROPERTY_NEED_TABLE_NAME,
				self::PROPERTY_IS_SP_PARAM,
			),
			'alias' => '_UetOMS',//указать в наследниках
			'label' => 'УЕТ (ОМС)',
			'save' => 'trim',
			'type' => 'float'
		);
		return $arr;
	}

	/**
	 * Определение идентификатора класса события
	 * @return int
	 */
	static function evnClassId()
	{
		return 10;
	}

	/**
	 * Определение кода класса события
	 * @return string
	 */
	static function evnClassSysNick()
	{
		return 'EvnVizit';
	}

	/**
	 * Определение кода типа оплаты сохраняемого/сохраненного посещения
	 * @return string
	 * @throws Exception
	 */
	function getPayTypeSysNick()
	{
		if (empty($this->PayType_id)) {
			$this->_payTypeSysNick = null;
		} else if (empty($this->_payTypeSysNick)) {
			$this->_payTypeSysNick = $this->getFirstResultFromQuery('
				select PayType_SysNick
				from v_PayType with(nolock)
				where PayType_id = :PayType_id
			', array('PayType_id' => $this->PayType_id));
			if (empty($this->_payTypeSysNick)) {
				throw new Exception('Ошибка при получении кода типа оплаты', 500);
			}
		}
		return $this->_payTypeSysNick;
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updatePayTypeId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'paytype_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMesId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'mes_id', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnVizitUet($id, $value = null)
	{
		return $this->_updateAttribute($id, 'uet', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateEvnVizitUetOMS($id, $value = null)
	{
		return $this->_updateAttribute($id, 'uetoms', $value);
	}

	/**
	 * @param int $id
	 * @param mixed $value
	 * @return array
	 */
	function updateMesOldVizitId($id, $value = null)
	{
		return $this->_updateAttribute($id, 'mes_id', $value);
	}

	/**
	 * Логика после успешного сохранения объекта
	 * @throws Exception
	 */
	protected function _updateMes()
	{
		if ($this->regionNick == 'ekb' /*&& !empty($this->Mes_id)*/) {
			// обновить МЭС в других посещениях, где МЭС другой
			$query = "
				update
					ev with (rowlock)
				set
					ev.Mes_id = :Mes_id
				from
					EvnVizit ev
					inner join v_EvnVizitPL epl (nolock) on epl.EvnVizitPL_id = ev.EvnVizit_id
				where
					ISNULL(epl.Mes_id, 0) <> ISNULL(:Mes_id, 0)
					and epl.EvnVizitPL_pid = :EvnPL_id
			";
			$this->db->query($query, array(
				'Mes_id' => $this->Mes_id, 'EvnPL_id' => $this->pid,
			));
		}
	}



	/**
	 * Проверка возможности изменения МЭС посещения
	 * @throws Exception
	 */
	protected function _checkChangeMes()
	{
		if ( $this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 11
			&& empty($this->UslugaComplex_id)&& empty($this->Mes_id)
		) { // Если посещение создается автоматически, то не проверяем
			// throw new Exception('Обязательно для заполнения одно из полей "МЭС" или "Код посещения"', 400); // убрал проверку, т.к. #48996
		}
		if ( $this->regionNick == 'ekb'
			&& in_array($this->scenario, array(self::SCENARIO_DO_SAVE, self::SCENARIO_SET_ATTRIBUTE))
			&& $this->evnClassId == 13
			&& empty($this->Mes_id)
		) { // Если посещение создается автоматически, то не проверяем
			throw new Exception('Обязательно для заполнения поле "МЭС"', 400);
		}
		if ($this->regionNick == 'ekb') {
			// МЭС должен быть таким же как и в первом посещении данного ТАП
			if ( empty($this->id) && $this->index > 0 ) {
				$Mes_id = $this->getFirstResultFromQuery("
					select top 1
						Mes_id
					from
						v_EvnVizitPL (nolock)
					where
						(EvnVizitPL_id <> :EvnVizitPL_id OR :EvnVizitPL_id IS NULL)
						and EvnVizitPL_Index = 0 -- первое посещение
						and EvnVizitPL_pid = :EvnPL_id
				", array(
					'EvnVizitPL_id' => $this->id,
					'EvnPL_id' => $this->pid
				));
				$this->setAttribute('Mes_id', $Mes_id);
			}
			// Если заполнено поле «МЭС», то должны быть указаны все услуги для данного стандарта, у которых проставлен атрибут «обязательность услуги». При невыполнении данного контроля выводить сообщение «Необходимо указать все обязательные услуги для выбранного стандарта в поле «МЭС». ОК. Сохранение отменить.
			// Если заполнено поле «МЭС», то должны быть указаны только услуги из данного стандарта. При невыполнении данного контроля выводить сообщение «Указаны услуги, не входящие в выбранный стандарт». ОК. Сохранение отменить.
			if (!empty($this->Mes_id)) {
				if (empty($this->_params['ignoreMesUslugaCheck'])) {
					$resp_epl = $this->queryResult("select EvnPL_id from v_EvnPL (nolock) where EvnPL_id = :EvnPL_id and EvnPL_IsFinish = 2", array(
						'EvnPL_id' => $this->pid
					));

					if (!empty($resp_epl[0]['EvnPL_id'])) {
						$params = array(
							'Mes_id' => $this->Mes_id, 'rid' => $this->pid, 'EvnVizitPL_setDate' => $this->setDate
						);
						$query = "
							select top 1
								mu.MesUsluga_id,
								mu.UslugaComplex_id
							from
								v_MesUsluga mu (nolock)
							where
								mu.MesUslugaLinkType_id = 5
								and mu.Mes_id = :Mes_id
								and mu.MesUsluga_IsNeedUsluga = 2
								and ISNULL(mu.MesUsluga_begDT, :EvnVizitPL_setDate) <= :EvnVizitPL_setDate
								and ISNULL(mu.MesUsluga_endDT, :EvnVizitPL_setDate) >= :EvnVizitPL_setDate
								and not exists(
									select top 1 EvnUsluga_id from v_EvnUsluga (nolock) where EvnUsluga_rid = :rid and UslugaComplex_id = mu.UslugaComplex_id
								)
						";

						//echo getDebugSQL($query, $params);die;
						$result = $this->db->query($query, $params);
						if (is_object($result)) {
							$resp = $result->result('array');
							if (!empty($resp[0]['MesUsluga_id']) && $resp[0]['UslugaComplex_id'] != $this->UslugaComplex_id) {
								$this->_saveResponse['ignoreParam'] = 'ignoreMesUslugaCheck';
								$this->_saveResponse['Alert_Msg'] = 'Заполнены не все обязательные услуги для выбранного стандарта в поле "МЭС". Продолжить сохранение?';
								throw new Exception('YesNo', 114);
							}
						}
					}
				}
				$query = "
					select top 1
						eu.EvnUsluga_id
					from
						v_EvnUsluga eu (nolock)
					where
						eu.EvnUsluga_pid = :EvnVizitPL_id
						and ISNULL(eu.EvnUsluga_IsVizitCode, 1) = 1
						and eu.EvnUsluga_setDT is not null
						and not exists (
							select top 1 MesUsluga_id from v_MesUsluga (nolock)
							where UslugaComplex_id = eu.UslugaComplex_id
							and MesUslugaLinkType_id = 5
							and Mes_id = :Mes_id
						)
				";
				$result = $this->getFirstResultFromQuery($query, array(
					'Mes_id' => $this->Mes_id ,
					'EvnVizitPL_id' => $this->id
				));
				if (!empty($result)) {
					throw new Exception('Указаны услуги, не входящие в выбранный стандарт', 400);
				}
				if ($this->scenario == self::SCENARIO_SET_ATTRIBUTE) {
					// очищаем код посещения
					$this->setAttribute('uslugacomplex_id', null);
					// услуга посещения, если она есть, удалится после сохранения посещения
				}
			}

			/**
			 * Если в посещении сохранена хотя бы одна услуга из стандарта по поликлинике,
			 * у которой проставлен атрибут «обязательность услуги» и при этом не указан стандарт
			 * в поле «МЭС»,
			 * то выводить сообщение «Необходимо выбрать стандарт в поле «МЭС. ОК».
			 * Сохранение отменить.
			 *
			 * @task https://redmine.swan.perm.ru/issues/114474
			 * Условие переделано на r66.v_UslugaComplexPartitionLink
			 */
			if (empty($this->Mes_id)) {
				$filter = "";
				if ($this->evnClassId == 11) {
					$filter .= " and ucp.UslugaComplexPartition_Code IN ('300', '301', '306', '350', '351')";
				}

				// проверяем по услугам
				$query = "
					select top 1
						eu.EvnUsluga_id
					from
						v_EvnUsluga eu (nolock)
					where
						eu.EvnUsluga_pid = :EvnVizitPL_id
						and ISNULL(eu.EvnUsluga_IsVizitCode, 1) = 1
						and exists (
							select top 1
								ucpl.UslugaComplexPartitionLink_id
							from
								r66.v_UslugaComplexPartitionLink ucpl (nolock)
								inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
							where
								ucpl.UslugaComplex_id = eu.UslugaComplex_id
								and ucpl.PayType_id = eu.PayType_id
								and ucpl.UslugaComplexPartitionLink_IsMes = 2
								and ucpl.UslugaComplexPartitionLink_begDT <= eu.EvnUsluga_setDT
								and (ucpl.UslugaComplexPartitionLink_endDT is null or ucpl.UslugaComplexPartitionLink_endDT >= eu.EvnUsluga_setDT)
								and ucp.MedicalCareType_id = 3 -- Амбулаторно
								{$filter}
						)
				";
				$result = $this->getFirstResultFromQuery($query, array(
					'EvnVizitPL_id' => $this->id
				));
				if (!empty($result)) {
					throw new Exception('Необходимо выбрать стандарт в поле "МЭС"', 400);
				}

				// дополнительно проверяем код посещения
				if (!empty($this->UslugaComplex_id)) {
					$query = "
						select top 1
							ucpl.UslugaComplexPartitionLink_id
						from
							r66.v_UslugaComplexPartitionLink ucpl (nolock)
							inner join r66.v_UslugaComplexPartition ucp (nolock) on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
						where
							ucpl.UslugaComplex_id = :UslugaComplex_id
							and ucpl.PayType_id = :PayType_id
							and ucpl.UslugaComplexPartitionLink_IsMes = 2
							and ucpl.UslugaComplexPartitionLink_begDT <= :setDT
							and (ucpl.UslugaComplexPartitionLink_endDT is null or ucpl.UslugaComplexPartitionLink_endDT >= :setDT)
							and ucp.MedicalCareType_id = 3 -- Амбулаторно
							{$filter}
					";
					$result = $this->getFirstResultFromQuery($query, array(
						'UslugaComplex_id' => $this->UslugaComplex_id,
						'PayType_id' => $this->PayType_id,
						'setDT' => $this->setDT
					));
					if (!empty($result)) {
						throw new Exception('Необходимо выбрать стандарт в поле "МЭС"', 400);
					}
				}
			}
		}
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _beforeUpdateAttribute($key)
	{
		parent::_beforeUpdateAttribute($key);
		switch ($key) {
			case 'mes_id':
				$this->_params['ignoreMesUslugaCheck'] = 1;
				$this->_checkChangeMes();
				break;
		}
	}

	/**
	 * @param string $key Ключ строчными символами
	 * @throws Exception
	 */
	protected function _afterUpdateAttribute($key)
	{
		parent::_afterUpdateAttribute($key);
		switch ($key) {
			case 'mes_id':
				$this->_updateMes();
				break;
		}
	}
}