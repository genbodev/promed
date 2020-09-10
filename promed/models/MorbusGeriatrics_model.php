<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MorbusGeriatrics_model - модель для MorbusGeriatrics
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2018 Swan Ltd.
* @author       Быков Станислав
* @version      12.2018
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
*/
class MorbusGeriatrics_model extends swModel
{
	protected $_MorbusType_id = 100;

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick()
	{
		return 'geriatrics';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId()
	{
		if (empty($this->_MorbusType_id)) {
			$this->load->library('swMorbus');
			$this->_MorbusType_id = swMorbus::getMorbusTypeIdBySysNick($this->getMorbusTypeSysNick());
			if (empty($this->_MorbusType_id)) {
				throw new Exception('Не удалось определить тип заболевания', 500);
			}
		}
		return $this->_MorbusType_id;
	}

	/**
	 * Определение имени таблицы с данными объекта
	 * @return string
	 */
	protected function tableName()
	{
		return 'MorbusGeriatrics';
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	public function autoCreate($data, $isAllowTransaction = true)
	{
		if (empty($data['MorbusBase_id']) || empty($data['Person_id'])
			|| empty($data['Morbus_id']) || empty($data['Diag_id']) || empty($data['Morbus_setDT'])
			|| empty($data['mode'])
			|| false == in_array($data['mode'], array('onBeforeViewData', 'onBeforeSavePersonRegister', 'onBeforeSaveEvnNotify'))
		) {
			throw new Exception('Переданы неправильные параметры', 500);
		}
		$this->setParams($data);
		$tableName = $this->tableName();
		$queryParams = array();
		$queryParams['pmUser_id'] = $this->promedUserId;
		$queryParams['Morbus_id'] = $data['Morbus_id'];
		$queryParams['Lpu_id'] = isset($data['Lpu_id'])?$data['Lpu_id']:$this->sessionParams['lpu_id'];

		$query = "
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@pmUser_id bigint = :pmUser_id,
				@Morbus_id bigint = :Morbus_id,
				@{$tableName}_id bigint = null,
				@IsCreate int = 1;

			-- должно быть одно на Morbus
			select top 1 @{$tableName}_id = {$tableName}_id from v_{$tableName} with (nolock) where Morbus_id = @Morbus_id

			if isnull(@{$tableName}_id, 0) = 0
			begin
				exec p_{$tableName}_ins
					@{$tableName}_id = @{$tableName}_id output,
					@Morbus_id = @Morbus_id,
					@pmUser_id = @pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				if isnull(@{$tableName}_id, 0) > 0
				begin
					set @IsCreate = 2;
				end
			end

			select @{$tableName}_id as {$tableName}_id, @IsCreate as IsCreate, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ) {
			throw new Exception('Ошибка БД', 500);
		}
		$resp = $result->result('array');
		if (!empty($resp[0]['Error_Msg'])) {
			throw new Exception($resp[0]['Error_Msg'], 500);
		}
		if (empty($resp[0][$tableName . '_id'])) {
			throw new Exception("Не удалось создать объект {$tableName}", 500);
		}
		$this->_saveResponse[$tableName . '_id'] = $resp[0][$tableName . '_id'];
		return $this->_saveResponse;
	}

	/**
	 * Загрузка формы редактирования записи регистра
	 */
	public function load($data) {
		return $this->queryResult("
			select
				MO.MorbusGeriatrics_id,
				MO.Morbus_id,
				M.Diag_id,
				M.Person_id,
				convert(varchar(10), PR.PersonRegister_setDate, 104) as PersonRegister_setDate,
				MO.AgeNotHindrance_id,
				MO.MorbusGeriatrics_IsKGO,
				MO.MorbusGeriatrics_IsWheelChair,
				MO.MorbusGeriatrics_IsFallDown,
				MO.MorbusGeriatrics_IsWeightDecrease,
				MO.MorbusGeriatrics_IsCapacityDecrease,
				MO.MorbusGeriatrics_IsCognitiveDefect,
				MO.MorbusGeriatrics_IsMelancholia,
				MO.MorbusGeriatrics_IsEnuresis,
				MO.MorbusGeriatrics_IsPolyPragmasy
			from
				v_MorbusGeriatrics MO with (nolock)
				left join v_Morbus M with (nolock) on M.Morbus_id = MO.Morbus_id
				left join v_PersonRegister PR on PR.Morbus_id = M.Morbus_id
			where
				MO.MorbusGeriatrics_id = :MorbusGeriatrics_id
		", array(
			'MorbusGeriatrics_id' => $data['MorbusGeriatrics_id']
		));
	}

	/**
	 * Сохранение формы редактирования записи регистра
	 */
	public function save($data) {
		if ( !empty($data['MorbusGeriatrics_id']) ) {
			$proc = 'p_MorbusGeriatrics_upd';
			$data['Diag_id'] = $this->getFirstResultFromQuery("select top 1 Diag_id from v_MorbusGeriatrics with (nolock) where MorbusGeriatrics_id = :MorbusGeriatrics_id", array('MorbusGeriatrics_id' => $data['MorbusGeriatrics_id']));
		}
		else {
			$data['MorbusGeriatrics_id'] = null;
			$proc = 'p_MorbusGeriatrics_ins';
		}

		return $this->queryResult("
			declare
				@MorbusGeriatrics_id bigint = :MorbusGeriatrics_id,
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec {$proc}
				@MorbusGeriatrics_id = @MorbusGeriatrics_id output,
				@Morbus_id = :Morbus_id,
				@AgeNotHindrance_id = :AgeNotHindrance_id,
				@MorbusGeriatrics_IsKGO = :MorbusGeriatrics_IsKGO,
				@MorbusGeriatrics_IsWheelChair = :MorbusGeriatrics_IsWheelChair,
				@MorbusGeriatrics_IsFallDown = :MorbusGeriatrics_IsFallDown,
				@MorbusGeriatrics_IsWeightDecrease = :MorbusGeriatrics_IsWeightDecrease,
				@MorbusGeriatrics_IsCapacityDecrease = :MorbusGeriatrics_IsCapacityDecrease,
				@MorbusGeriatrics_IsCognitiveDefect = :MorbusGeriatrics_IsCognitiveDefect,
				@MorbusGeriatrics_IsMelancholia = :MorbusGeriatrics_IsMelancholia,
				@MorbusGeriatrics_IsEnuresis = :MorbusGeriatrics_IsEnuresis,
				@MorbusGeriatrics_IsPolyPragmasy = :MorbusGeriatrics_IsPolyPragmasy,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MorbusGeriatrics_id as MorbusGeriatrics_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

	/**
	 * @param array $data
	 * @return array|false
	 */
	public function getIdForEmk($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
		);

		$query = "
			select top 1 MG.MorbusGeriatrics_id
			from v_MorbusGeriatrics MG with(nolock)
				inner join v_Morbus M with(nolock) on M.Morbus_id = MG.Morbus_id
			where M.Person_id = :Person_id
			order by MG.MorbusGeriatrics_id desc
		";

		return $this->queryResult($query, $params);
	}
}