<?php defined('BASEPATH') or die ('No direct script access allowed');

class Extemporal_model extends swModel {
	
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Получение латинского наименования компонента
	*/

	function getLatName($data){
		$params = array();
		$query = '';
		if(!empty($data['Tradename_id'])){
			$params['Tradename_id'] = $data['Tradename_id'];
			$query = "
				select top 1 lt.LATINNAMES_NameGen as LatinName
				from rls.v_PREP prep with(nolock)
	        	left join rls.v_LATINNAMES lt on lt.LATINNAMES_ID = prep.LATINNAMEID 
	        	where prep.TRADENAMEID = :Tradename_id
        	";
		}
        if(!empty($data['Actmatters_id'])){
			$params['Actmatters_id'] = $data['Actmatters_id'];
			$query = "
				select top 1 am.ACTMATTERS_LatNameGen as LatinName
				from rls.v_ActMatters am with(nolock)
	        	where am.ACTMATTERS_ID = :Actmatters_id
        	";
		}   
		if(empty($query))
			return false;
        $res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
	 *	Возвращает параметры процедуры
	 */
	function getParamsByProcedure($data) {
		$filter = "1=1";
		$filter .= " and s.name = :scheme";
		$filter .= " and p.name = :proc";
		$filter .= " and t.is_user_defined = 0";

		$query = "
			select
				substring(ps.name, 2, len(ps.name)) as name,
				t.name as type,
				ps.is_output
			from
				sys.parameters ps with(nolock)
				inner join sys.procedures p on p.object_id = ps.object_id
				inner join sys.schemas s with(nolock) on s.schema_id = p.schema_id
				inner join sys.types t with(nolock) on t.user_type_id = ps.user_type_id
			where
				{$filter}
			order by
				ps.parameter_id
		";
		//echo getDebugSql($query, $data); die();
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение данных записи таблицы по идентификатору
	 */
	private function getRecordById($id,$schema_id = 7) {
		if( !isset($id) || empty($id) || $id < 0 || !isset($schema_id) ) {
			return null;
		}
		if($schema_id == 7){
			$schema = 'rls';
		} else {
			$schema = 'dbo';
		}
		$query = "
			select name from sys.views with(nolock) where name like 'v_{$this->objectName}' and schema_id = {$schema_id}
		";
		$result = $this->db->query($query);
		if ( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		if( count($result) > 0 ) {
			$from = $schema.".v_{$this->objectName}";
		} else {
			$from = $schema.".{$this->objectName}";
		}
		
		$query = "
			select top 1
				*
			from
				{$from}
			where
				{$this->objectName}_id = {$id}
		";
		$result = $this->db->query($query);
		if ( is_object($result) ) {
			$result = $result->result('array');
			return isset($result[0]) ? $result[0] : null;
		} else {
			return false;
		}
	}
	
	/**
	 *	Устанавливает значение объекта БД и значение ключа строки с которой работаем в дальнейшем
	 */
	function setObject($objectName) {
		$this->objectName = $objectName;
		return $this;
	}
	
	/**
	 *	Устанавливает значение ключа строки с которой работаем в дальнейшем
	 */
	function setRow($objectKey) {
		$this->objectKey = $objectKey;
		return $this;
	}
	
	/**
	 *	Устанавливает значение поля объекта БД
	 */
	function setValue($field, $value) {
		if( empty($this->objectName) || empty($this->objectKey) )
			return false;
		
		$procedure = "p_" . $this->objectName . "_upd";
		$params = $this->getParamsByProcedure(array('scheme' => 'rls', 'proc' => $procedure));
		//print_r($params);
	
		$query = "
			declare
				@{$this->objectName}_id bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @{$this->objectName}_id = :{$this->objectName}_id;
			exec rls." . $procedure . "\n";
		
		foreach($params as $k=>$param) {
			$query .= "\t\t\t\t@" . $param['name'] . " = " . ( $param['is_output'] ? "@".$param['name']." output" : ":".$param['name'] );
			$query .= ( count($params) == ++$k ? ";" : "," ) . "\n";
		}
		$query .= "\t\t\tselect @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		//var_dump($query);
		
		$record = $this->getRecordById($this->objectKey);
		if( !is_array($record) ) {
			return false;
		}
		
		$record[$this->objectName.'_id'] = $this->objectKey;
		$sp = getSessionParams();
		$record['pmUser_id'] = $sp['pmUser_id'];
		
		if( array_key_exists($field, $record) ) {
			$record[$field] = $value;
			$result = $this->db->query($query, $record);
			if ( is_object($result) ) {
				return $result->result('array');
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 *	Сохраняет запись объекта БД
	 */
	function saveRecord($data,$case=false) {
		if( empty($this->objectName) )
			return false;
		
		$procedure = "p_" . $this->objectName . ((empty($data[$this->objectName.'_id']))?"_ins":"_upd");
		$params = $this->getParamsByProcedure(array('scheme' => 'rls', 'proc' => $procedure));
		//print_r($params);
		$id = (($case)?"ID":"id");
		$query = "
			declare
				@{$this->objectName}_{$id} bigint,
				@Error_Code int,
				@Error_Message varchar(4000);
			set @{$this->objectName}_{$id} = :{$this->objectName}_{$id};
			exec rls." . $procedure . "\n";
		
		foreach($params as $k=>$param) {
			$query .= "\t\t\t\t@" . $param['name'] . " = " . ( $param['is_output'] ? "@".$param['name']." output" : ":".$param['name'] );
			$query .= ( count($params) == ++$k ? ";" : "," ) . "\n";
		}
		$query .= "\t\t\tselect @{$this->objectName}_id as {$this->objectName}_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		//var_dump($query);
		
		$record = array();
		foreach($params as $param) {
			$record[$param['name']] = (isset($data[$param['name']])?$data[$param['name']]:null);
		}

		$result = $this->db->query($query, $record);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Удаляет запись объекта БД
	 */
	function deleteRecord($data) {
		if( empty($this->objectName) )
			return false;
		
		$procedure = "p_" . $this->objectName . "_del";
		$params = $this->getParamsByProcedure(array('scheme' => 'rls', 'proc' => $procedure));
		//print_r($params);
	
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000);
			exec rls." . $procedure . "\n";
		
		foreach($params as $k=>$param) {
			$query .= "\t\t\t\t@" . $param['name'] . " = " . ( $param['is_output'] ? "@".$param['name']." output" : ":".$param['name'] );
			$query .= ( count($params) == ++$k ? ";" : "," ) . "\n";
		}
		$query .= "\t\t\tselect @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		//var_dump($query);
		
		$record = array();
		foreach($params as $param) {
			$record[$param['name']] = (isset($data[$param['name']])?$data[$param['name']]:null);
		}

		$result = $this->db->query($query, $record);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 *	Возвращает данные для постраничного вывода
	 */
	function returnPagingData($q, &$p) {
		$get_count_result = $this->db->query(getCountSQLPH($q), $p);
		if( !is_object($get_count_result) ) {
			return false;
		}
		$get_count_result = $get_count_result->result('array');
		
		$result = $this->db->query(getLimitSQLPH($q, $p['start'], $p['limit']), $p);
		if( !is_object($result) ) {
			return false;
		}
		$result = $result->result('array');
		return array(
			'data' => $result,
			'totalCount' => $get_count_result[0]['cnt']
		);
	}

	/**
	 *	Сохранение рецептуры
	 */
	function saveExtemporal($data) {
		$this->setObject('Extemporal');
		return $this->saveRecord($data);
	}

	/**
	 *	Удаление рецептуры
	 */
	function deleteExtemporal($data) {
		$query = "
			select 
			ExtemporalCompStandart_id
			from rls.v_ExtemporalCompStandart with (nolock)
			where Extemporal_id = :Extemporal_id
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if (count($res)>0) {
				return array(array('Error_Code'=>'999'));
			}
		}

		$query = "
			select 
			ExtemporalComp_id
			from rls.v_ExtemporalComp with (nolock)
			where Extemporal_id = :Extemporal_id
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			foreach ($res as $value) {
				$this->deleteExtemporalComp(array('ExtemporalComp_id'=>$value['ExtemporalComp_id']));
			}
		}

		$this->setObject('Extemporal');
		return $this->deleteRecord($data);
	}

	/**
	 *	Копирование рецептуры
	 */
	function copyExtemporal($data) {
		$this->setObject('Extemporal');
		$record = $this->getRecordById($data['Extemporal_id']);
		$record['Extemporal_id'] = null;
		$extemporal = $this->saveRecord($record);
		if($extemporal && isset($extemporal[0]) && empty($extemporal[0]['Error_Msg']) && !empty($extemporal[0]['Extemporal_id'])){
			$query = "
				select 
				ExtemporalComp_id
				from rls.v_ExtemporalComp with (nolock)
				where Extemporal_id = :Extemporal_id
			";
			
			//echo getDebugSql($query, $data); die();
			$res = $this->db->query($query, $data);
			if ( is_object($res) ) {
				$res = $res->result('array');
				$this->setObject('ExtemporalComp');
				foreach ($res as $value) {
					$rec = $this->getRecordById($value['ExtemporalComp_id']);
					$rec['ExtemporalComp_id'] = null;
					$rec['Extemporal_id'] = $extemporal[0]['Extemporal_id'];
					$this->saveRecord($rec);
				}
			}

			$query = "
				select 
				ExtemporalCompStandart_id
				from rls.v_ExtemporalCompStandart with (nolock)
				where Extemporal_id = :Extemporal_id
			";
			
			//echo getDebugSql($query, $data); die();
			$resl = $this->db->query($query, $data);
			if ( is_object($resl) ) {
				$resl = $resl->result('array');
				$this->setObject('ExtemporalCompStandart');
				foreach ($resl as $val) {
					$recr = $this->getRecordById($val['ExtemporalCompStandart_id']);
					$recr['ExtemporalCompStandart_id'] = null;
					$recr['Extemporal_id'] = $extemporal[0]['Extemporal_id'];
					$this->saveRecord($recr);
				}
			}

			return $this->loadExtemporalList(array('Extemporal_id'=>$extemporal[0]['Extemporal_id'],'start'=>0,'limit'=>1));
		} else {
			return $extemporal;
		}
	}

	/**
	 *	Сохранение компонента рецептуры
	 */
	function saveExtemporalComp($data) {
		if(!empty($data['ExtemporalComp_LatName'])){
			if(!empty($data['ACTMATTERS_ID'])){
				$params['Actmatters_id'] = $data['ACTMATTERS_ID'];
				$query = "
					select top 1 am.ACTMATTERS_LatNameGen as LatinName
					from rls.v_ActMatters am with(nolock)
		        	where am.ACTMATTERS_ID = :Actmatters_id
	        	";
		        $res = $this->db->query($query, $params);
				if ( is_object($res) ) {
					$res = $res->result('array');
					if(count($res)==0 || (count($res)>0 && (trim($data['ExtemporalComp_LatName']) != trim($res[0]['LatinName'])))){
						$this->setObject('ACTMATTERS')->setRow($data['ACTMATTERS_ID']);
						$this->setValue('ACTMATTERS_LatNameGen',trim($data['ExtemporalComp_LatName']));
					}
				}
			} else if(!empty($data['TRADENAMES_ID'])){
				$params['Tradename_id'] = $data['TRADENAMES_ID'];
				$query = "
					select top 1 
						lt.LATINNAMES_NameGen as LatinName,
						prep.Prep_id, 
						lt.LATINNAMES_ID
					from rls.v_PREP prep with(nolock)
		        	left join rls.v_LATINNAMES lt on lt.LATINNAMES_ID = prep.LATINNAMEID 
		        	where prep.TRADENAMEID = :Tradename_id
	        	";
		        $res = $this->db->query($query, $params);
				if ( is_object($res) ) {
					$res = $res->result('array');
					if(count($res)>0 && (empty($res[0]['LatinName']) || $res[0]['LATINNAMES_ID']==0) && !empty($res[0]['Prep_id'])){
						$this->setObject('LATINNAMES');
						$rec = $this->saveRecord(array('LATINNAMES_NameGen'=>$data['ExtemporalComp_LatName'],'pmUser_id'=>$data['pmUser_id']),true);
						if(isset($rec[0]['LATINNAMES_id'])){
							$this->setObject('PREP')->setRow($res[0]['Prep_id']);
							$this->setValue('LATINNAMEID',$rec[0]['LATINNAMES_id']);
						}
					} else if(count($res)>0 && !empty($res[0]['LatinName']) && (trim($data['ExtemporalComp_LatName']) != trim($res[0]['LatinName']))){
						$this->setObject('LATINNAMES')->setRow($res[0]['LATINNAMES_ID']);
						$this->setValue('LATINNAMES_NameGen',trim($data['ExtemporalComp_LatName']));
					}
				}
			}
		}
		if(!empty($data['ACTMATTERS_ID'])){
			$params['Actmatters_id'] = $data['ACTMATTERS_ID'];
			$query = "
				select top 1 
					dmc.DrugMnnCode_Code as Code
				from rls.v_DrugMnnCode dmc with(nolock)
	        	where dmc.ACTMATTERS_id = :Actmatters_id
        	";
	        $res = $this->db->query($query, $params);
	        if ( is_object($res) ) {
				$res = $res->result('array');
				if(!( count($res)>0 )){
					$query = "
						select top 1 
							MAX(cast(dmc.DrugMnnCode_Code as bigint)) as maxCode
						from rls.v_DrugMnnCode dmc with(nolock)
						where cast(dmc.DrugMnnCode_Code as bigint) > 0
		        	";
			        $resm = $this->db->query($query);
			        if ( is_object($resm) ) {
			        	$resm = $resm->result('array');
			        	$max = ($resm[0]['maxCode'] + 1);
			        } else {
			        	$max = 1;
			        }
					$this->setObject('DrugMnnCode');
					$this->saveRecord(array('DrugMnnCode_Code'=>$max,'ACTMATTERS_id'=>$data['ACTMATTERS_ID'],'pmUser_id'=>$data['pmUser_id']));
				}
			}
		}
		if(!empty($data['TRADENAMES_ID'])){
			$params['Tradename_id'] = $data['TRADENAMES_ID'];
			$query = "
				select top 1 
					dmc.DrugTorgCode_Code as Code
				from rls.v_DrugTorgCode dmc with(nolock)
	        	where dmc.TRADENAMES_id = :Tradename_id
        	";
	        $res = $this->db->query($query, $params);
	        if ( is_object($res) ) {
				$res = $res->result('array');
				if(!( count($res)>0 )){
					$query = "
						select top 1 
							MAX(cast(dmc.DrugTorgCode_Code as bigint)) as maxCode
						from rls.v_DrugTorgCode dmc with(nolock)
		        	";
			        $resm = $this->db->query($query);
			        if ( is_object($resm) ) {
			        	$resm = $resm->result('array');
			        	$max = ($resm[0]['maxCode'] + 1);
			        } else {
			        	$max = 1;
			        }
					$this->setObject('DrugTorgCode');
					$this->saveRecord(array('DrugTorgCode_Code'=>$max,'TRADENAMES_id'=>$data['TRADENAMES_ID'],'pmUser_id'=>$data['pmUser_id']));
				}
			}
		}
		$this->setObject('ExtemporalComp');
		return $this->saveRecord($data);
	}

	/**
	 *	Удаление компонента рецептуры
	 */
	function deleteExtemporalComp($data) {
		$this->setObject('ExtemporalComp');
		return $this->deleteRecord($data);
	}

	/**
	 *	Чтение списка компонентов
	 */
	function loadExtemporalCompList($data) {
		$params = array();
		$filter = "1=1";
		
		if( !empty($data['Extemporal_id']) ) {
			$params['Extemporal_id'] = $data['Extemporal_id'];
			$filter .= " and ec.Extemporal_id = :Extemporal_id";
		}
		
		$query = "
			select
				ec.ExtemporalComp_id,
				ec.Extemporal_id,
				ec.ExtemporalComp_Name,
				cast(ec.ExtemporalComp_Count as float) as ExtemporalComp_Count,
				ec.GoodsUnit_id,
				gu.GoodsUnit_Name,
				ec.ExtemporalCompType_id,
				rtrim(ect.ExtemporalCompType_LatinName +' ('+ect.ExtemporalCompType_ShortName+')' ) as ExtemporalCompType_Name,
				ec.ACTMATTERS_ID as RlsActMatters_id,
				ec.TRADENAMES_ID as RlsTradenames_id,
				isnull(am.ACTMATTERS_LatNameGen,LN.LATINNAMES_NameGen) as ExtemporalComp_LatName,
				isnull(dmc.DrugMnnCode_Code,dtc.DrugTorgCode_Code) as ExtemporalComp_Code
			from rls.v_ExtemporalComp ec with(nolock)
			left join rls.v_ExtemporalCompType ect with (nolock) on ect.ExtemporalCompType_id = ec.ExtemporalCompType_id 
			left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = ec.GoodsUnit_id 
			left join rls.v_ActMatters am with (nolock) on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
			left join rls.v_PREP prep with (nolock) on prep.TRADENAMEID = ec.TRADENAMES_ID
			left join rls.v_LATINNAMES LN with (nolock) on LN.LATINNAMES_ID = prep.LATINNAMEID 
			left join rls.v_DrugMnnCode dmc with (nolock) on dmc.ACTMATTERS_id = ec.ACTMATTERS_ID
			left join rls.v_DrugTorgCode dtc with (nolock) on dtc.TRADENAMES_id = ec.TRADENAMES_ID
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				ec.ExtemporalComp_Name --desc
				-- end order by
		";
        
        //echo getDebugSql($query, $params); die();
        $result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Чтение списка компонентов
	 */
	function loadExtemporalCompStandartList($data) {
		$params = array();
		$filter = "1=1";
		
		if( !empty($data['Extemporal_id']) ) {
			$params['Extemporal_id'] = $data['Extemporal_id'];
			$filter .= " and ecs.Extemporal_id = :Extemporal_id";
		}

		if(!isSuperAdmin()){
			$params['Org_id'] = $data['Org_id'];
			$filter .= " and ecs.Org_id = :Org_id";
		}
		
		$query = "
			select
				ecs.ExtemporalCompStandart_id,
				ecs.Extemporal_id,
				ext.Extemporal_Name,
				ecs.ExtemporalCompStandart_Tariff,
				ecs.ExtemporalCompStandart_Count,
				ecs.GoodsUnit_id,
				ltrim(isnull(CONVERT (VARCHAR(50), ecs.ExtemporalCompStandart_Count,128),'') + ' ' +isnull(gu.GoodsUnit_Name,'')) as Norma,
				ecs.Org_id,
				org.Org_Nick
			from rls.v_ExtemporalCompStandart ecs with(nolock)
			left join rls.v_Extemporal ext with (nolock) on ext.Extemporal_id = ecs.Extemporal_id
			left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = ecs.GoodsUnit_id 
			left join Org org with (nolock) on org.Org_id = ecs.Org_id
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				ecs.ExtemporalCompStandart_id --desc
				-- end order by
		";
        
        //echo getDebugSql($query, $params); die();
        $result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Сохранение тарифа рецептуры
	 */
	function saveExtemporalCompStandart($data) {
		$this->setObject('ExtemporalCompStandart');
		return $this->saveRecord($data);
	}

	/**
	 *	Удаление тарифа рецептуры
	 */
	function deleteExtemporalCompStandart($data) {
		$this->setObject('ExtemporalCompStandart');
		return $this->deleteRecord($data);
	}
	
	/**
	 *	Чтение списка рецептур
	 */
	function loadExtemporalList($data) {
		$params = array();
		$filter = "1=1";
		
		if( empty($data['Extemporal_Code']) && empty($data['Extemporal_Name']) && empty($data['ExtemporalType_id']) && empty($data['ExtemporalComp_Name']) && empty($data['Org_Name']) && (!isset($data['Extemporal_id']) || empty($data['Extemporal_id']))){
			return false;
		}
		if( !empty($data['Extemporal_Code']) ) {
			$params['Extemporal_Code'] = $data['Extemporal_Code'];
			$filter .= " and ext.Extemporal_Code = :Extemporal_Code";
		}

		if( !empty($data['Extemporal_Name']) ) {
			$params['Extemporal_Name'] = '%'.$data['Extemporal_Name'].'%';
			$filter .= " and ext.Extemporal_Name like :Extemporal_Name";
		}

		if( !empty($data['ExtemporalType_id']) ) {
			$params['ExtemporalType_id'] = $data['ExtemporalType_id'];
			$filter .= " and ext.ExtemporalType_id = :ExtemporalType_id";
		}

		if( !empty($data['ExtemporalComp_Name']) ) {
			$params['ExtemporalComp_Name'] = '%'.$data['ExtemporalComp_Name'].'%';
			$filter .= " and exists( select eco.ExtemporalComp_id from rls.v_ExtemporalComp eco with(nolock) where eco.Extemporal_id = ext.Extemporal_id and eco.ExtemporalComp_Name like :ExtemporalComp_Name )";
		}

		if( !empty($data['Org_Name']) ) {
			$params['Org_Name'] = '%'.$data['Org_Name'].'%';
			$filter .= " and (org.Org_Name like :Org_Name or org.Org_Nick like :Org_Name)";
		}

		if( isset($data['Extemporal_id']) && !empty($data['Extemporal_id']) ) {
			$params['Extemporal_id'] = $data['Extemporal_id'];
			$filter .= " and ext.Extemporal_id = :Extemporal_id";
		}
		
		$query = "
			select
			-- select
				ext.Extemporal_id,
				ext.Extemporal_Name,
				ext.ExtemporalType_id,
				et.ExtemporalType_Name,
				ext.CLSDRUGFORMS_ID as RlsClsdrugforms_id,
				ext.Extemporal_Code,
				ext.Extemporal_IsClean,
				convert(varchar(10), cast(ext.Extemporal_begDT as datetime), 104) as Extemporal_begDT,
				convert(varchar(10), cast(ext.Extemporal_endDT as datetime), 104) as Extemporal_endDT,
				(convert(varchar(10), cast(ext.Extemporal_begDT as datetime), 104) + ' - ' + convert(varchar(10), cast(ext.Extemporal_endDT as datetime), 104)) as Extemporal_daterange,
				STUFF(
					(SELECT
						', '+rtrim(isnull(ec.ExtemporalComp_Name,''))+' '+cast(cast(ec.ExtemporalComp_Count as float) as varchar)+' '+isnull(gu.GoodsUnit_Name,'')
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
						left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = ec.GoodsUnit_id
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
					FOR XML PATH ('')
					), 1, 2, ''
				) as Extemporal_Composition
			-- end select
			from
			-- from
			rls.v_Extemporal ext with (nolock)
			left join rls.v_ExtemporalCompStandart ecs with (nolock) on ecs.Extemporal_id = ext.Extemporal_id
			left join Org org with (nolock) on org.Org_id = ecs.Org_id
			left join rls.v_ExtemporalType et with (nolock) on et.ExtemporalType_id = ext.ExtemporalType_id
			-- end from
			where
				-- where
				{$filter}
				-- end where
			order by
				-- order by
				ext.Extemporal_begDT --desc
				-- end order by
		";
		$params['start'] = $data['start'];
		$params['limit'] = $data['limit'];
        
        //echo getDebugSql($query, $params); die();
        if(!empty($data['withoutPaging'])){
        	return $this->queryResult($query, $params);
        } else {
        	return $this->returnPagingData($query, $params);
        }
	}
	
	/**
	 *	Проверка наличия компонента в рецептуре
	 */
	function checkExtemporalComp($data) {
		$query = "
			select 
			ACTMATTERS_ID,
			TRADENAMES_ID,
			ExtemporalComp_id
			from rls.v_ExtemporalComp with (nolock)
			where Extemporal_id = :Extemporal_id
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			$cnt = 0;
			if(!empty($data['RlsActmatters_id'])){
				$field = 'RlsActmatters_id';
				$tField = 'ACTMATTERS_ID';
			} else if(!empty($data['Tradenames_id'])){
				$field = 'Tradenames_id';
				$tField = 'TRADENAMES_ID';
			} else {
				return array(array('cnt'=>0)); // гипотетический вариант, т.к. на форме поле компонент обязательно
			}
			foreach ($res as $value) {
				if($value[$tField] == $data[$field]){
					if(empty($data['ExtemporalComp_id']) || $data['ExtemporalComp_id'] != $value['ExtemporalComp_id'])
						return array(array('cnt'=>1));
				}
			}
			return array(array('cnt'=>0));
		} else {
			return array(array('cnt'=>0));
		}
	}

	/**
	 *	Проверка уникальности наименования
	 */
	function checkExtemporalName($data) {
		$where = "";
		if(!empty($data['Extemporal_id'])){
			$where = " and Extemporal_id <> :Extemporal_id";
		}
		$query = "
			select 
			Extemporal_id
			from rls.v_Extemporal with (nolock)
			where Extemporal_Name = :Extemporal_Name {$where}
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(count($res)>0){
				return array(array('cnt'=>1));
			} else {
				return array(array('cnt'=>0));
			}
		} else {
			return array(array('cnt'=>0));
		}
	}

	/**
	 *	Получение кода для рецептуры
	 */
	function getExtemporalCode() {
		$query = "
			select 
			MAX(Extemporal_Code) as code
			from rls.v_Extemporal with (nolock)
			where Extemporal_Code > 0
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(count($res)>0){
				return array(array('code'=>($res[0]['code']+1)));
			} else {
				return array(array('code'=>0));
			}
		} else {
			return array(array('code'=>0));
		}
	}

	/**
	 *	Проверка уникальности рецептуры
	 */
	function checkExtemporal($data) {
		$query = "
			select 
			Extemporal_id
			from rls.v_Extemporal with (nolock)
			where Extemporal_IsClean = :Extemporal_IsClean and Extemporal_id <> :Extemporal_id
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(count($res)>0){
				foreach ($res as $value) {
					$query = "
						select 
						count(*) as cnt
						from rls.v_ExtemporalComp with (nolock)
						where Extemporal_id = :Extemporal_id
					";
					$ress = $this->db->query($query, array('Extemporal_id'=>$value['Extemporal_id']));
					if ( is_object($ress) ) {
						$ress = $ress->result('array');
						if($ress[0]['cnt'] == $data['count']){
							$query = "
								select 
								ACTMATTERS_ID
								from rls.v_ExtemporalComp with (nolock)
								where Extemporal_id = :Extemporal_id
							";
							$resl = $this->db->query($query, array('Extemporal_id'=>$value['Extemporal_id']));
							if ( is_object($resl) ) {
								$resl = $resl->result('array');
								if(strlen($data['actmatters'])>0){
									$actmatters = $data['actmatters'];
									$actmatters = explode(",",rtrim($actmatters, ","));
									if(count($actmatters) == count($resl)){
										$reslt = [];
										foreach ($resl as $r) {
											array_push($reslt,$r['ACTMATTERS_ID']);
										}
										sort($actmatters);
										sort($reslt);
										if($actmatters == $reslt){
											return array(array('cnt'=>1));
										}
									} else {
										continue;
									}
								}
							}

							$query = "
								select 
								TRADENAMES_ID
								from rls.v_ExtemporalComp with (nolock)
								where Extemporal_id = :Extemporal_id
							";
							$resl = $this->db->query($query, array('Extemporal_id'=>$value['Extemporal_id']));
							if ( is_object($resl) ) {
								$resl = $resl->result('array');
								if(strlen($data['tradenames'])>0){
									$tradenames = $data['tradenames'];
									$tradenames = explode(",",rtrim($tradenames, ","));
									if(count($tradenames) == count($resl)){
										$reslt = [];
										foreach ($resl as $r) {
											array_push($reslt,$r['TRADENAMES_ID']);
										}
										sort($tradenames);
										sort($reslt);
										if($tradenames == $reslt){
											return array(array('cnt'=>1));
										}
									}
								}
							}
						}
					}
				}
				return array(array('cnt'=>0));
			} else {
				return array(array('cnt'=>0));
			}


			if(!empty($data['RlsActmatters_id'])){
				$field = 'RlsActmatters_id';
				$tField = 'ACTMATTERS_ID';
			} else if(!empty($data['Tradenames_id'])){
				$field = 'Tradenames_id';
				$tField = 'TRADENAMES_ID';
			} else {
				return array(array('cnt'=>0)); // гипотетический вариант, т.к. на форме поле компонент обязательно
			}
			foreach ($res as $value) {
				if($value[$tField] == $data[$field]){
					if(empty($data['ExtemporalComp_id']) || $data['ExtemporalComp_id'] != $value['ExtemporalComp_id'])
						return array(array('cnt'=>1));
				}
			}
			
		} else {
			return array(array('cnt'=>0));
		}


		$query = "
			select 
			ACTMATTERS_ID,
			TRADENAMES_ID
			from rls.v_ExtemporalComp with (nolock)
			where Extemporal_id = :Extemporal_id
		";
		
		//echo getDebugSql($query, $data); die();
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			$cnt = 0;
			if(!empty($data['RlsActmatters_id'])){
				$field = 'RlsActmatters_id';
				$tField = 'ACTMATTERS_ID';
			} else if(!empty($data['Tradenames_id'])){
				$field = 'Tradenames_id';
				$tField = 'TRADENAMES_ID';
			} else {
				return array(array('cnt'=>0)); // гипотетический вариант, т.к. на форме поле компонент обязательно
			}
			foreach ($res as $value) {
				if($value[$tField] == $data[$field]){
					if(empty($data['ExtemporalComp_id']) || $data['ExtemporalComp_id'] != $value['ExtemporalComp_id'])
						return array(array('cnt'=>1));
				}
			}
			return array(array('cnt'=>0));
		} else {
			return array(array('cnt'=>0));
		}
	}
	
	/**
	 *	Проверка наличия тарифа для рецептуры
	 */
	function checkExtemporalCompStandart($data) {
		$where = '';
		if(!empty($data['ExtemporalCompStandart_id'])){
			$where = " and ExtemporalCompStandart_id <> :ExtemporalCompStandart_id";
		}
		$query = "
			select 
			ExtemporalCompStandart_id
			from rls.v_ExtemporalCompStandart with (nolock)
			where Org_id = :Org_id and Extemporal_id = :Extemporal_id {$where}
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(count($res)>0){
				return array(array('cnt'=>1));
			} else {
				return array(array('cnt'=>0));
			}
		} else {
			return array(array('cnt'=>0));
		}
	}

	/**
	 *	Чтение данных рецептуры для справочника РЛС
	 */
	function loadExtemporal($data) {
		$params = array();
		$filter = "1=1";

		if( !isset($data['Extemporal_id']) || empty($data['Extemporal_id']) ) {
			return false;
		} else {
			$params['Extemporal_id'] = $data['Extemporal_id'];
		}
		
		$query = "
			select top 1
			-- select
				ext.Extemporal_Name as TRADENAMES_NAME,
				ext.CLSDRUGFORMS_ID,
				cls.NAME as CLSDRUGFORMS_NAME,
				STUFF(
					(SELECT
						', '+rtrim(isnull(ec.ExtemporalComp_Name,''))+' '+cast(cast(ec.ExtemporalComp_Count as float) as varchar)+' '+isnull(gu.GoodsUnit_Nick,'')
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
						left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = ec.GoodsUnit_id
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
					FOR XML PATH ('')
					), 1, 2, ''
				) as Composition,
				STUFF(
					(SELECT
						', '+rtrim(isnull(ec.ExtemporalComp_Name,''))+' '+cast(cast(ec.ExtemporalComp_Count as float) as varchar)+' '+isnull(gu.GoodsUnit_Nick,'')
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
						left join v_GoodsUnit gu with (nolock) on gu.GoodsUnit_id = ec.GoodsUnit_id
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id <> 1
					FOR XML PATH ('')
					), 1, 2, ''
				) as CompositionNotMain,
				STUFF(
					(SELECT
						' + '+rtrim(isnull(am.RUSNAME,''))
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
						left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
					FOR XML PATH ('')
					), 1, 3, ''
				) as Actmatters_Names,
				STUFF(
					(SELECT
						'|'+cast(ec.ACTMATTERS_ID as varchar(30))
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1 and ec.ACTMATTERS_ID is not null
					FOR XML PATH ('')
					), 1, 1, ''
				) as ACTMATTERS,
				STUFF(
					(SELECT
						' + '+rtrim(am.LATNAME)
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
						left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
					FOR XML PATH ('')
					), 1, 3, ''
				) as Actmatters_LatName,
				STUFF(
					(SELECT
						' + '+rtrim(am.ACTMATTERS_LatNameGen)
					FROM
						rls.v_ExtemporalComp ec WITH (nolock)
						left join rls.v_ACTMATTERS am with (nolock) on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
					WHERE
						ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
					FOR XML PATH ('')
					), 1, 3, ''
				) as Actmatters_LatNameGen
			-- end select
			from
			-- from
			rls.v_Extemporal ext with (nolock)
			left join rls.v_CLSDRUGFORMS cls with (nolock) on cls.CLSDRUGFORMS_ID = ext.CLSDRUGFORMS_ID
			-- end from
			where
				-- where
				{$filter}
				and ext.Extemporal_id = :Extemporal_id
				-- end where
		";
        
        //echo getDebugSql($query, $params); die();
        
		$res = $this->db->query($query, $params);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(count($res)>0){
				$res[0]['Composition'] .= (!empty($res[0]['CompositionNotMain'])?(', '.$res[0]['CompositionNotMain']):'');
				return $res;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
}