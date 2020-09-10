<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* MorbusGEBT_model - модель для MorbusGEBT
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009-2019 Swan Ltd.
 *
 * Магические свойства
 * @property-read int $morbusTypeId
 * @property-read string $morbusTypeSysNick
 * @property-read string $groupRegistry
*/
class MorbusGEBT_model extends swModel {
	protected $_MorbusType_id = 103;

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return string
	 */
	function getMorbusTypeSysNick() {
		return 'gibt';
	}

	/**
	 * @return int
	 * @throws Exception
	 */
	function getMorbusTypeId() {
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
	protected function tableName() {
		return 'MorbusGEBT';
	}

	/**
	 * Создание специфики заболевания
	 * @param array $data
	 * @param bool $isAllowTransaction
	 * @return array
	 * @throws Exception
	 */
	function autoCreate($data, $isAllowTransaction = true) {
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
	 * Загрузка списка Курс препарата
	 */
	function loadMorbusGEBTDrugList($data) {
		return $this->queryResult("
			select 
				mgb.MorbusGEBTDrug_id
				,mgb.MorbusGEBT_id
				,mgb.DrugComplexMNN_id
				,dcm.DrugComplexMnn_RusName Drug_Name
				,mgb.MorbusGEBTDrug_OneInject
				,mgb.MorbusGEBTDrug_InjectCount
				,mgb.MorbusGEBTDrug_InjectQuote
				,mgb.MorbusGEBTDrug_QuoteYear
				,mgb.MorbusGEBTDrug_BoxYear
			from v_MorbusGEBTDrug mgb (nolock)
			left join rls.v_DrugComplexMnn dcm (nolock) on dcm.DrugComplexMnn_id = mgb.DrugComplexMnn_id
			where mgb.MorbusGEBT_id = :MorbusGEBT_id
		", $data);
	}

	/**
	 * Загрузка формы Курс препарата
	 */
	function loadMorbusGEBTDrug($data) {
		return $this->queryResult("
			select 
				mgb.MorbusGEBTDrug_id
				,mgb.MorbusGEBT_id
				,mgb.DrugComplexMNN_id
				,mgb.MorbusGEBTDrug_OneInject
				,mgb.MorbusGEBTDrug_InjectCount
				,mgb.MorbusGEBTDrug_InjectQuote
				,mgb.MorbusGEBTDrug_QuoteYear
				,mgb.MorbusGEBTDrug_BoxYear
			from v_MorbusGEBTDrug mgb (nolock)
			where mgb.MorbusGEBTDrug_id = :MorbusGEBTDrug_id
		", $data);
	}

	/**
	 * Сохранение формы Курс препарата
	 */
	function saveMorbusGEBTDrug($data) {
		$proc = empty($data['MorbusGEBTDrug_id']) ? 'ins' : 'upd';
		return $this->queryResult("
			declare
				@MorbusGEBTDrug_id bigint = :MorbusGEBTDrug_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MorbusGEBTDrug_{$proc}
				@MorbusGEBTDrug_id = @MorbusGEBTDrug_id output,
				@MorbusGEBT_id = :MorbusGEBT_id,
				@DrugComplexMNN_id = :DrugComplexMNN_id,
				@MorbusGEBTDrug_OneInject = :MorbusGEBTDrug_OneInject,
				@MorbusGEBTDrug_InjectCount = :MorbusGEBTDrug_InjectCount,
				@MorbusGEBTDrug_InjectQuote = :MorbusGEBTDrug_InjectQuote,
				@MorbusGEBTDrug_QuoteYear = :MorbusGEBTDrug_QuoteYear,
				@MorbusGEBTDrug_BoxYear = :MorbusGEBTDrug_BoxYear,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MorbusGEBTDrug_id as MorbusGEBTDrug_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

	/**
	 * Загрузка списка Планируемое лечение
	 */
	function loadMorbusGEBTPlanList($data) {
		return $this->queryResult("
			set language Russian;
			select 
				mgp.MorbusGEBTPlan_id
				,mgp.MorbusGEBT_id
				,mgp.Lpu_id
				,lpu.Lpu_Nick
				,mgp.MedicalCareType_id
				,mct.MedicalCareType_Name
				,mgp.MorbusGEBTPlan_Year
				,datename(month, dateadd(month, mgp.MorbusGEBTPlan_Month, -1)) MorbusGEBTPlan_Month
				,mgp.DrugComplexMNN_id
				,dcm.DrugComplexMnn_RusName Drug_Name
				,case when mgp.MorbusGEBTPlan_Treatment = 2 then 'true' else 'false' end as MorbusGEBTPlan_Treatment
			from v_MorbusGEBTPlan mgp (nolock)
			left join rls.v_DrugComplexMnn dcm (nolock) on dcm.DrugComplexMnn_id = mgp.DrugComplexMnn_id
			left join v_Lpu lpu (nolock) on lpu.Lpu_id = mgp.Lpu_id
			left join fed.v_MedicalCareType mct (nolock) on mct.MedicalCareType_id = mgp.MedicalCareType_id
			where mgp.MorbusGEBT_id = :MorbusGEBT_id
		", $data);
	}

	/**
	 * Загрузка формы Планируемое лечение
	 */
	function loadMorbusGEBTPlan($data) {
		return $this->queryResult("
			select 
				mgp.MorbusGEBTPlan_id
				,mgp.MorbusGEBT_id
				,mgp.Lpu_id
				,mgp.MedicalCareType_id
				,mgp.MorbusGEBTPlan_Year
				,mgp.MorbusGEBTPlan_Month
				,mgp.DrugComplexMNN_id
				,mgp.MorbusGEBTPlan_Treatment
			from v_MorbusGEBTPlan mgp (nolock)
			where mgp.MorbusGEBTPlan_id = :MorbusGEBTPlan_id
		", $data);
	}

	/**
	 * Сохранение формы Планируемое лечение
	 */
	function saveMorbusGEBTPlan($data) {
		if ($data['MorbusGEBTPlan_Treatment'] == 1) {
			$chk = $this->getFirstResultFromQuery("
				select count(*) cnt 
				from v_MorbusGEBTPlan 
				where 
					MorbusGEBT_id = :MorbusGEBT_id and
					MorbusGEBTPlan_id != :MorbusGEBTPlan_id and
					MorbusGEBTPlan_Treatment = 1 
			", $data);
			if ($chk) {
				throw new Exception('В плане лечения с применением ГИБТ может быть только одно не проведенное лечение');
			}
		}
		$proc = empty($data['MorbusGEBTPlan_id']) ? 'ins' : 'upd';
		return $this->queryResult("
			declare
				@MorbusGEBTPlan_id bigint = :MorbusGEBTPlan_id,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MorbusGEBTPlan_{$proc}
				@MorbusGEBTPlan_id = @MorbusGEBTPlan_id output,
				@MorbusGEBT_id = :MorbusGEBT_id,
				@Lpu_id = :Lpu_id,
				@MedicalCareType_id = :MedicalCareType_id,
				@MorbusGEBTPlan_Year = :MorbusGEBTPlan_Year,
				@MorbusGEBTPlan_Month = :MorbusGEBTPlan_Month,
				@DrugComplexMNN_id = :DrugComplexMNN_id,
				@MorbusGEBTPlan_Treatment = :MorbusGEBTPlan_Treatment,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @MorbusGEBTPlan_id as MorbusGEBTPlan_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $data);
	}

	/**
	 * Загрузка списка препаратов 
	 */
	function getDrugList($data) {
		return $this->queryResult("
			select distinct
				mgb.DrugComplexMNN_id
				,dcm.DrugComplexMnn_RusName Drug_Name
			from v_MorbusGEBTDrug mgb (nolock)
			left join rls.v_DrugComplexMnn dcm (nolock) on dcm.DrugComplexMnn_id = mgb.DrugComplexMnn_id
			where mgb.MorbusGEBT_id = :MorbusGEBT_id
		", $data);
	}
}