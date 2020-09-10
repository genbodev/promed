<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * UslugaComplexMedService_model - Модель для работы с услугами на службе
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009-2018 Swan Ltd.
 * @author       Dmitriy Vlasenko
 * @version      08.2018
 */
class UslugaComplexMedService_model extends swModel {
	/**
	 * Конструктор объекта
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_DO_SAVE
		));
	}

	/**
	 * Возвращает список всех используемых ключей атрибутов объекта
	 * @return array
	 */
	static function defAttributes()
	{
		return array(
			self::ID_KEY => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_NOT_NULL,
				),
				'alias' => 'UslugaComplexMedService_id',
				'label' => 'Идентификатор услуги на службе',
				'save' => 'trim',
				'type' => 'id'
			),
			'pid' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_pid',
				'label' => 'Идентификатор родительской услуги на службе',
				'save' => 'trim|required',
				'type' => 'id'
			),
			'medservice_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'MedService_id',
				'type' => 'id'
			),
			'uslugacomplex_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplex_id',
				'type' => 'id'
			),
			'begdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_begDT',
				'type' => 'date'
			),
			'enddt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_endDT',
				'type' => 'date'
			),
			'refsample_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'RefSample_id',
				'type' => 'id'
			),
			'lpuequipment_id' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'LpuEquipment_id',
				'type' => 'id'
			),
			'time' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_Time',
				'type' => 'int'
			),
			'uslugacomplex_name' => array(
				'properties' => array(
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplex_Name',
				'type' => 'string'
			),
			'isportalrec' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_IsPortalRec',
				'type' => 'id'
			),
			'ispay' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_IsPay',
				'type' => 'id'
			),
			'iselectronicqueue' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_IS_SP_PARAM,
				),
				'alias' => 'UslugaComplexMedService_IsElectronicQueue',
				'type' => 'id'
			),
			'insdt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_insid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'upddt' => array(
				'properties' => array(
					self::PROPERTY_NEED_TABLE_NAME,
					self::PROPERTY_DATE_TIME,
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			),
			'pmuser_updid' => array(
				'properties' => array(
					self::PROPERTY_READ_ONLY,
					self::PROPERTY_NOT_SAFE,
				),
			)
		);
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'UslugaComplexMedService';
	}

	/**
	 * Проверки и другая логика перед сохранением объекта
	 */
	protected function _beforeSave($data = array())
	{
		parent::_beforeSave($data);

		// проверка на дубли
		$filter = "";
		if (!empty($this->id)) {
			$filter .= " and ucms.UslugaComplexMedService_id <> :UslugaComplexMedService_id";
		}
		$resp = $this->queryResult("
			select top 1
				ucms.UslugaComplexMedService_id
			from
				v_UslugaComplexMedService ucms (nolock)
			where
				ucms.UslugaComplex_id = :UslugaComplex_id
				and ucms.MedService_id = :MedService_id
				and ISNULL(ucms.UslugaComplexMedService_pid, 0) = ISNULL(:UslugaComplexMedService_pid, 0)
				{$filter}
		", array(
			'UslugaComplex_id' => $this->UslugaComplex_id,
			'MedService_id' => $this->MedService_id,
			'UslugaComplexMedService_pid' => $this->pid,
			'UslugaComplexMedService_id' => $this->id
		));

		if (!empty($resp[0]['UslugaComplexMedService_id'])) {
			throw new Exception('Обнаружено дублирование услуги на службе, сохранение невозможно', 400);
		}
	}

	/**
	 * Сохранение для postgre
	*/
	function doSaveUslugaComplexMedService($data) {
		// проверка на дубли
		$filter = "";

		$query = "
			select top 1
				uslugacomplexmedservice_id as \"UslugaComplexMedService_id\",
				uslugacomplexmedservice_pid as \"UslugaComplexMedService_pid\",
				medservice_id as \"MedService_id\",
				uslugacomplex_id as \"UslugaComplex_id\",
				uslugacomplexmedservice_begdt as \"UslugaComplexMedService_begDT\",
				uslugacomplexmedservice_enddt as \"UslugaComplexMedService_endDT\",
				refsample_id as \"RefSample_id\",
				lpuequipment_id as \"LpuEquipment_id\",
				uslugacomplexmedservice_time as \"UslugaComplexMedService_Time\",
				uslugacomplex_name as \"UslugaComplex_name\",
				uslugacomplexmedservice_isportalrec as \"UslugaComplexMedService_IsPortalRec\",
				uslugacomplexmedservice_ispay as \"UslugaComplexMedService_IsPay\",
				uslugacomplexmedservice_iselectronicqueue as \"UslugaComplexMedService_IsElectronicQueue\",
				uslugacomplexmedservice_insdt as \"UslugaComplexMedService_insDT\",
				pmuser_insid as \"pmUser_insID\",
				uslugacomplexmedservice_upddt as \"UslugaComplexMedService_updDT\",
				pmuser_updid as \"pmUser_updID\"
			from
				v_UslugaComplexMedService with(nolock)
			where
				UslugaComplexMedService_id = :UslugaComplexMedService_id
		";

		if (!empty($data['UslugaComplexMedService_id'])) {
			$filter .= " and ucms.UslugaComplexMedService_id <> :UslugaComplexMedService_id";
		}

		$res = $this->queryResult($query, $data);

		if (empty($res)) {
			$res = [];
		} else {
			$res = $res[0];
		}
		$data = array_merge($res, $data);

		$resp = $this->queryResult("
			select top 1
				ucms.UslugaComplexMedService_id as \"UslugaComplexMedService_id\"
			from
				v_UslugaComplexMedService ucms with(nolock)
			where
				ucms.UslugaComplex_id = :UslugaComplex_id
				and ucms.MedService_id = :MedService_id
				and coalesce(ucms.UslugaComplexMedService_pid, 0) = coalesce(:UslugaComplexMedService_pid, 0)
				{$filter}
		", $data);
		if (!empty($resp[0]['UslugaComplexMedService_id'])) {
			throw new Exception('Обнаружено дублирование услуги на службе, сохранение невозможно', 400);
		}

		if ($data['UslugaComplexMedService_id'])
			$procedure = 'p_UslugaComplexMedService_upd';
		else $procedure = 'p_UslugaComplexMedService_ins';

		$begDT = ($data['UslugaComplexMedService_begDT'] == '@curDT')
			? "dbo.tzgetdate()"
			: ":UslugaComplexMedService_begDT";
		$query = "
			declare
				@Res bigint,
				@Error_Code bigint,
				@Error_Msg varchar(4000),
				@begDT datetime;
			set @Res = :UslugaComplexMedService_id;
			set @begDT = {$begDT};
			exec {$procedure}
				@UslugaComplexMedService_id = @Res output,
				@MedService_id = :MedService_id,
				@UslugaComplex_id = :UslugaComplex_id,
				@UslugaComplexMedService_IsSeparateSample = :UslugaComplexMedService_IsSeparateSample,
				@UslugaComplexMedService_begDT = @begDT,
				@UslugaComplexMedService_endDT = :UslugaComplexMedService_endDT,
				@RefSample_id = :RefSample_id,
				@UslugaComplexMedService_pid = :UslugaComplexMedService_pid,
				@LpuEquipment_id = :LpuEquipment_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @Res as UslugaComplexMedService_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$result = $result->result('array');
			if ($result[0]) {
				$result = $result[0];
				if ($result['Error_Msg']) {
					throw new Exception($result['Error_Msg'], $result['Error_Code']);
				} else {
					return $result;
				}
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}