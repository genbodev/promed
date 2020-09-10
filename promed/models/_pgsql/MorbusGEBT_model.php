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
class MorbusGEBT_model extends SwPgModel {
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
            CREATE OR REPLACE FUNCTION pg_temp.exp_Query
            (   out _TableName_id  bigint, out _IsCreate int, out _Error_Code int, out _Error_Message text 
            ) 
            LANGUAGE 'plpgsql'
          
            AS $$
            DECLARE

            BEGIN

                select 
                    {$tableName}_id into _TableName_id
                from
                    v_{$tableName} 
                where  
                    Morbus_id = :Morbus_id
                Limit 1;

                 if( Coalesce(_TableName_id,0)=0)  then 
                
                        Select  
                            {$tableName}_id, Error_Code, Error_Message
                           into
                              _TableName_id, _Error_Code,_Error_Message
                        from
                            p_{$tableName}_ins
                         (
                            Morbus_id := :Morbus_id,
					        pmUser_id := :pmUser_id
                         );
                            if( Coalesce(_TableName_id,0) >0) then                      
                                _IsCreate = 2;
                            end if ;

                end if;

                  exception
            	    when others then _Error_Code:=SQLSTATE; _Error_Message:=SQLERRM;
                    
            END;
            $$; 
            
            select _TableName_id as \"{$tableName}_id\", _IsCreate as \"IsCreate\" , _Error_Code as \"Error_Code\", _Error_Message as \"Error_Msg\"
            from pg_temp.exp_Query();
        ";

		$result = $this->db->query($query, $queryParams, true);
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
				mgb.MorbusGEBTDrug_id as \"MorbusGEBTDrug_id\"
				,mgb.MorbusGEBT_id as \"MorbusGEBT_id\"
				,mgb.DrugComplexMNN_id as \"DrugComplexMNN_id\"
				,dcm.DrugComplexMnn_RusName as \"Drug_Name\"
				,mgb.MorbusGEBTDrug_OneInject as \"MorbusGEBTDrug_OneInject\"
				,mgb.MorbusGEBTDrug_InjectCount as \"MorbusGEBTDrug_InjectCount\"
				,mgb.MorbusGEBTDrug_InjectQuote as \"MorbusGEBTDrug_InjectQuote\"
				,mgb.MorbusGEBTDrug_QuoteYear as \"MorbusGEBTDrug_QuoteYear\"
				,mgb.MorbusGEBTDrug_BoxYear as \"MorbusGEBTDrug_BoxYear\"
			from v_MorbusGEBTDrug mgb 
			left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = mgb.DrugComplexMnn_id
			where mgb.MorbusGEBT_id = :MorbusGEBT_id
		", $data);
	}

	/**
	 * Загрузка формы Курс препарата
	 */
	function loadMorbusGEBTDrug($data) {
		return $this->queryResult("
			select 
				mgb.MorbusGEBTDrug_id as \"MorbusGEBTDrug_id\"
				,mgb.MorbusGEBT_id as \"MorbusGEBT_id\"
				,mgb.DrugComplexMNN_id as \"DrugComplexMNN_id\"
				,mgb.MorbusGEBTDrug_OneInject as \"MorbusGEBTDrug_OneInject\"
				,mgb.MorbusGEBTDrug_InjectCount as \"MorbusGEBTDrug_InjectCount\"
				,mgb.MorbusGEBTDrug_InjectQuote as \"MorbusGEBTDrug_InjectQuote\"
				,mgb.MorbusGEBTDrug_QuoteYear as \"MorbusGEBTDrug_QuoteYear\"
				,mgb.MorbusGEBTDrug_BoxYear as \"MorbusGEBTDrug_BoxYear\"
			from v_MorbusGEBTDrug mgb 
			where mgb.MorbusGEBTDrug_id = :MorbusGEBTDrug_id
		", $data);
	}

	/**
	 * Сохранение формы Курс препарата
	 */
	function saveMorbusGEBTDrug($data) {
		$proc = empty($data['MorbusGEBTDrug_id']) ? 'ins' : 'upd';
        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            MorbusGEBTDrug_id as \"MorbusGEBTDrug_id\"
        from p_MorbusGEBTDrug_{$proc}
            (
 				MorbusGEBTDrug_id := :MorbusGEBTDrug_id,
				MorbusGEBT_id := :MorbusGEBT_id,
				DrugComplexMNN_id := :DrugComplexMNN_id,
				MorbusGEBTDrug_OneInject := :MorbusGEBTDrug_OneInject,
				MorbusGEBTDrug_InjectCount := :MorbusGEBTDrug_InjectCount,
				MorbusGEBTDrug_InjectQuote := :MorbusGEBTDrug_InjectQuote,
				MorbusGEBTDrug_QuoteYear := :MorbusGEBTDrug_QuoteYear,
				MorbusGEBTDrug_BoxYear := :MorbusGEBTDrug_BoxYear,
				pmUser_id := :pmUser_id
            )";

            return $this->queryResult($query,$data);

	}

	/**
	 * Загрузка списка Планируемое лечение
	 */
function loadMorbusGEBTPlanList($data) {
		return $this->queryResult("
			select 
				mgp.MorbusGEBTPlan_id as \"MorbusGEBTPlan_id\"
				,mgp.MorbusGEBT_id as \"MorbusGEBT_id\"
				,mgp.Lpu_id as \"Lpu_id\"
				,lpu.Lpu_Nick as \"Lpu_Nick\"
				,mgp.MedicalCareType_id as \"MedicalCareType_id\"
				,mct.MedicalCareType_Name as \"MedicalCareType_Name\"
				,mgp.MorbusGEBTPlan_Year as \"MorbusGEBTPlan_Year\"
				,to_char(to_timestamp (cast((mgp.MorbusGEBTPlan_Month-1) as text), 'MM'), 'TMmonth') as \"MorbusGEBTPlan_Month\"
				,mgp.DrugComplexMNN_id as \"DrugComplexMNN_id\"
				,dcm.DrugComplexMnn_RusName as \"Drug_Name\"
				,case when mgp.MorbusGEBTPlan_Treatment = 2 then 'true' else 'false' end as \"MorbusGEBTPlan_Treatment\"
			from v_MorbusGEBTPlan mgp 
			left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = mgp.DrugComplexMnn_id
			left join v_Lpu lpu  on lpu.Lpu_id = mgp.Lpu_id
			left join fed.v_MedicalCareType mct  on mct.MedicalCareType_id = mgp.MedicalCareType_id
			where mgp.MorbusGEBT_id = :MorbusGEBT_id
		", $data);
	}

	/**
	 * Загрузка формы Планируемое лечение
	 */
		function loadMorbusGEBTPlan($data) {
		return $this->queryResult("
			select 
				mgp.MorbusGEBTPlan_id as \"MorbusGEBTPlan_id\"
				,mgp.MorbusGEBT_id as \"MorbusGEBT_id\"
				,mgp.Lpu_id as \"Lpu_id\"
				,mgp.MedicalCareType_id as \"MedicalCareType_id\"
				,mgp.MorbusGEBTPlan_Year as \"MorbusGEBTPlan_Year\"
				,mgp.MorbusGEBTPlan_Month as \"MorbusGEBTPlan_Month\"
				,mgp.DrugComplexMNN_id as \"DrugComplexMNN_id\"
				,mgp.MorbusGEBTPlan_Treatment as \"MorbusGEBTPlan_Treatment\"
			from v_MorbusGEBTPlan mgp 
			where mgp.MorbusGEBTPlan_id = :MorbusGEBTPlan_id
		", $data);
	}

	/**
	 * Сохранение формы Планируемое лечение
	 */
		function saveMorbusGEBTPlan($data) {
		if ($data['MorbusGEBTPlan_Treatment'] == 1) {
			$chk = $this->getFirstResultFromQuery("
				select count(*) as \"cnt\"
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
		return $this->queryResult(
        "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Error_Msg\",
            MorbusGEBTPlan_id as \"MorbusGEBTPlan_id\"
        from p_MorbusGEBTPlan_{$proc}
            (
 				MorbusGEBTPlan_id := :MorbusGEBTPlan_id,
				MorbusGEBT_id := :MorbusGEBT_id,
				Lpu_id := :Lpu_id,
				MedicalCareType_id := :MedicalCareType_id,
				MorbusGEBTPlan_Year := :MorbusGEBTPlan_Year,
				MorbusGEBTPlan_Month := :MorbusGEBTPlan_Month,
				DrugComplexMNN_id := :DrugComplexMNN_id,
				MorbusGEBTPlan_Treatment := :MorbusGEBTPlan_Treatment,
				pmUser_id := :pmUser_id
            )", $data);
	}

	/**
	 * Загрузка списка препаратов 
	 */
	function getDrugList($data) {
		return $this->queryResult("
			select distinct
				mgb.DrugComplexMNN_id as \"DrugComplexMNN_id\"
				,dcm.DrugComplexMnn_RusName as \"Drug_Name\"
			from v_MorbusGEBTDrug mgb 
			left join rls.v_DrugComplexMnn dcm  on dcm.DrugComplexMnn_id = mgb.DrugComplexMnn_id
			where mgb.MorbusGEBT_id = :MorbusGEBT_id
		", $data);
	}
}