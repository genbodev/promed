<?php defined('BASEPATH') or die ('No direct script access allowed');

class Extemporal_model extends swPgModel {

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
				select 
				  lt.LATINNAMES_NameGen as \"LatinName\"
				from 
				  rls.v_PREP prep
	        	  left join rls.v_LATINNAMES lt on lt.LATINNAMES_ID = prep.LATINNAMEID 
	        	where 
	        	  prep.TRADENAMEID = :Tradename_id
	        	limit 1
        	";
        }
        if(!empty($data['Actmatters_id'])){
            $params['Actmatters_id'] = $data['Actmatters_id'];
            $query = "
				select 
				  am.ACTMATTERS_LatNameGen as \"LatinName\"
				from 
				  rls.v_ActMatters am
	        	where 
	        	  am.ACTMATTERS_ID = :Actmatters_id
	        	limit 1
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
			select 
			  table_name as name
			from 
			  information_schema.views
			where 
			  table_name ilike 'v_{$this->objectName}' 
            and table_schema = '{$schema}'
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
			select
				*
			from
				{$from}
			where
				{$this->objectName}_id = {$id}
			limit 1
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
		$params = $this->getStoredProcedureParamsList($procedure, 'rls');
		//print_r($params);

        $query = "
            select 
                Error_Code as \"Error_Code\", 
                Error_Message as \"Error_Msg\"
             from rls." . $procedure ."
             (
        ";

        foreach($params as $k=>$param) {
            $query .= "\t\t\t\t" . $param . " := :".$param;
            $query .= ( count($params) == ++$k ? "" : "," ) . "\n";
        }
        $query .= ")";
        //var_dump($query);

        $record = $this->getRecordById($this->objectKey);
        if( !is_array($record) ) {
            return false;
        }

        $record[$this->objectName.'_id'] = $this->objectKey;
        $sp = getSessionParams();
        $record['pmuser_id'] = $sp['pmUser_id'];
		$record['error_code'] = '0';
		$record['error_message'] = '';

        if( array_key_exists(strtolower($field), $record) ) {
            $record[strtolower($field)] = $value;
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
    function saveRecord($data, $case = false) {
        if( empty($this->objectName) )
            return false;

		$type = empty($data[$this->objectName.'_id']) ? "ins" : "upd";
		$procedure = "p_" . $this->objectName . '_' . $type;
		$params = $this->getStoredProcedureParamsList($procedure, 'rls');

		$this->load->library('textlog', ['file'=>'fff.log'], 'f');
		$this->f->add(json_encode($params));
		$query = $this->getProcedureQuery($procedure, $params, $type);

		$record = $this->getProcedureData($data, $params);

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
		$params = $this->getStoredProcedureParamsList($procedure, 'rls');
		$query = $this->getProcedureQuery($procedure, $params, 'del');
		$record = $this->getProcedureData($data, $params);

		$result = $this->db->query($query, $record);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * создает запрос для процедур и возврашает
	 *
	 * @param $procedure
	 * @param $params
	 * @param $type
	 * @return string
	 */
	protected function getProcedureQuery($procedure, $params, $type)
	{
		$select = "";
		if($type != 'del') {
			$select .= "{$this->objectName}_id as \"{$this->objectName}_id\",";
		}
		$query = "
			select
				{$select}
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from rls.{$procedure}(
		";
		$paramParts = [];
		foreach($params as $param) {
			if($param == 'drugmnncode_code' || $param == 'drugtorgcode_code') { $paramParts[] = "\t\t\t\t" . $param . " := cast(:" . $param . " as varchar)"; }
			else {
				$paramParts[] = "\t\t\t\t" . $param . " := :" . $param;
			}
		}
		$query .= implode(",\n", $paramParts);
		$query .= "\n)";
		return $query;
	}

	/**
	 * возврашает параметры для процедуры
	 *
	 * @param $data
	 * @param $params
	 * @return array
	 */
	protected function getProcedureData($data, $params)
	{
		$record = [];
		$mutate_data = [];
		foreach ($data as $key => $datum) {
			$mutate_data[strtolower($key)] = $datum;
		}

		foreach($params as $param) {
			$record[$param] = (isset($mutate_data[$param]) ? $mutate_data[$param] : null);
		}
		return $record;
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
			  ExtemporalCompStandart_id as \"ExtemporalCompStandart_id\"
			from 
			  rls.v_ExtemporalCompStandart
			where 
			  Extemporal_id = :Extemporal_id
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
			  ExtemporalComp_id as \"ExtemporalComp_id\"
			from 
			  rls.v_ExtemporalComp
			where 
			  Extemporal_id = :Extemporal_id
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
				  ExtemporalComp_id as \"ExtemporalComp_id\"
				from 
				  rls.v_ExtemporalComp
				where 
				  Extemporal_id = :Extemporal_id
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
				  ExtemporalCompStandart_id as \"ExtemporalCompStandart_id\"
				from 
				  rls.v_ExtemporalCompStandart
				where 
				  Extemporal_id = :Extemporal_id
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
					select 
					  am.ACTMATTERS_LatNameGen as \"LatinName\"
					from 
					  rls.v_ActMatters am
		        	where 
		        	  am.ACTMATTERS_ID = :Actmatters_id
		        	limit 1
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
					select
						lt.LATINNAMES_NameGen as \"LatinName\",
						prep.Prep_id as \"Prep_id\", 
						lt.LATINNAMES_ID as \"LATINNAMES_ID\"
					from rls.v_PREP prep
		        	left join rls.v_LATINNAMES lt on lt.LATINNAMES_ID = prep.LATINNAMEID 
		        	where prep.TRADENAMEID = :Tradename_id
		        	limit 1
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
				select
					dmc.DrugMnnCode_Code as \"Code\"
				from 
				  rls.v_DrugMnnCode dmc
	        	where 
	        	  dmc.ACTMATTERS_id = :Actmatters_id
	        	limit 1
        	";
            $res = $this->db->query($query, $params);
            if ( is_object($res) ) {
                $res = $res->result('array');
                if(!( count($res) > 0 )){
                    $query = "
						select
							MAX(cast(dmc.DrugMnnCode_Code as bigint)) as \"maxCode\"
						from rls.v_DrugMnnCode dmc
 						where 
							cast(dmc.DrugMnnCode_Code as bigint) > 0
						limit 1
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
				select
					dmc.DrugTorgCode_Code as \"Code\"
				from 
				  rls.v_DrugTorgCode dmc
	        	where 
	        	  dmc.TRADENAMES_id = :Tradename_id
	        	limit 1
        	";
            $res = $this->db->query($query, $params);
            if ( is_object($res) ) {
                $res = $res->result('array');
                if(!( count($res)>0 )){
                    $query = "
						select
                          MAX(cast(dmc.DrugTorgCode_Code as bigint)) as \"maxCode\"
						from 
                          rls.v_DrugTorgCode dmc
						limit 1
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

        $querySelect = "
		    ec.ExtemporalComp_id as \"ExtemporalComp_id\",
            ec.Extemporal_id as \"Extemporal_id\",
            ec.ExtemporalComp_Name as \"ExtemporalComp_Name\",
            ec.ExtemporalComp_Count::float as \"ExtemporalComp_Count\",
            ec.GoodsUnit_id as \"GoodsUnit_id\",
            gu.GoodsUnit_Name as \"GoodsUnit_Name\",
            ec.ExtemporalCompType_id as \"ExtemporalCompType_id\",
            rtrim(ect.ExtemporalCompType_LatinName ||' ('||ect.ExtemporalCompType_ShortName||')' ) as \"ExtemporalCompType_Name\",
            ec.ACTMATTERS_ID as \"RlsActMatters_id\",
            ec.TRADENAMES_ID as \"RlsTradenames_id\",
            coalesce (am.ACTMATTERS_LatNameGen,LN.LATINNAMES_NameGen) as \"ExtemporalComp_LatName\",
            coalesce (dmc.DrugMnnCode_Code,dtc.DrugTorgCode_Code) as \"ExtemporalComp_Code\"
		";

        $queryFrom = "
		    rls.v_ExtemporalComp ec
		    left join rls.v_ExtemporalCompType ect on ect.ExtemporalCompType_id = ec.ExtemporalCompType_id 
			left join v_GoodsUnit gu  on gu.GoodsUnit_id = ec.GoodsUnit_id 
			left join rls.v_ActMatters am  on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
			left join rls.v_PREP prep  on prep.TRADENAMEID = ec.TRADENAMES_ID
			left join rls.v_LATINNAMES LN  on LN.LATINNAMES_ID = prep.LATINNAMEID 
			left join rls.v_DrugMnnCode dmc  on dmc.ACTMATTERS_id = ec.ACTMATTERS_ID
			left join rls.v_DrugTorgCode dtc  on dtc.TRADENAMES_id = ec.TRADENAMES_ID
		";

        $query = "
			select
				{$querySelect}
			from 
			    {$queryFrom}
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

        $querySelect = "
		    ecs.ExtemporalCompStandart_id as \"ExtemporalCompStandart_id\",
            ecs.Extemporal_id as \"Extemporal_id\",
            ext.Extemporal_Name as \"Extemporal_Name\",
            ecs.ExtemporalCompStandart_Tariff as \"ExtemporalCompStandart_Tariff\",
            ecs.ExtemporalCompStandart_Count as \"ExtemporalCompStandart_Count\",
            ecs.GoodsUnit_id as \"GoodsUnit_id\",
            ltrim(coalesce (cast(ecs.ExtemporalCompStandart_Count as varchar), '') || ' ' || coalesce(gu.GoodsUnit_Name,'')) as \"Norma\",
            ecs.Org_id as \"Org_id\",
            org.Org_Nick as \"Org_Nick\"
        ";

        $queryFrom = "
            rls.v_ExtemporalCompStandart ecs 
            left join rls.v_Extemporal ext on ext.Extemporal_id = ecs.Extemporal_id
            left join v_GoodsUnit gu on gu.GoodsUnit_id = ecs.GoodsUnit_id 
            left join Org org on org.Org_id = ecs.Org_id
		";

        $query = "
			select
				{$querySelect}
			from
			    {$queryFrom} 
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
            $filter .= " and ext.Extemporal_Name ilike :Extemporal_Name";
        }

        if( !empty($data['ExtemporalType_id']) ) {
            $params['ExtemporalType_id'] = $data['ExtemporalType_id'];
            $filter .= " and ext.ExtemporalType_id = :ExtemporalType_id";
        }

        if( !empty($data['ExtemporalComp_Name']) ) {
            $params['ExtemporalComp_Name'] = '%'.$data['ExtemporalComp_Name'].'%';
            $filter .= " and exists( select eco.ExtemporalComp_id from rls.v_ExtemporalComp eco where eco.Extemporal_id = ext.Extemporal_id and eco.ExtemporalComp_Name ilike :ExtemporalComp_Name )";
        }

        if( !empty($data['Org_Name']) ) {
            $params['Org_Name'] = '%'.$data['Org_Name'].'%';
            $filter .= " and (org.Org_Name ilike :Org_Name or org.Org_Nick ilike :Org_Name)";
        }

        if( isset($data['Extemporal_id']) && !empty($data['Extemporal_id']) ) {
            $params['Extemporal_id'] = $data['Extemporal_id'];
            $filter .= " and ext.Extemporal_id = :Extemporal_id";
        }

        $querySelect = "
            ext.Extemporal_id as \"Extemporal_id\",
            ext.Extemporal_Name as \"Extemporal_Name\",
            ext.ExtemporalType_id as \"ExtemporalType_id\",
            et.ExtemporalType_Name as \"ExtemporalType_Name\",
            ext.CLSDRUGFORMS_ID as \"RlsClsdrugforms_id\",
            ext.Extemporal_Code as \"Extemporal_Code\",
            ext.Extemporal_IsClean as \"Extemporal_IsClean\",
            to_char(ext.Extemporal_begDT::timestamp, 'DD.MM.YYYY') as \"Extemporal_begDT\",
            to_char(ext.Extemporal_endDT::timestamp, 'DD.MM.YYYY') as \"Extemporal_endDT\",
            (to_char(ext.Extemporal_begDT::timestamp, 'DD.MM.YYYY') ||' - '|| to_char(ext.Extemporal_endDT::timestamp, 'DD.MM.YYYY')) as \"Extemporal_daterange\",
            (
            SELECT
                string_agg(rtrim(coalesce(ec.ExtemporalComp_Name,''))||' '||cast(cast(ec.ExtemporalComp_Count as float) as varchar)||' '||coalesce(gu.GoodsUnit_Name,''), ', ')
            FROM
                rls.v_ExtemporalComp ec
                left join v_GoodsUnit gu on gu.GoodsUnit_id = ec.GoodsUnit_id
            WHERE
                ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
            ) as \"Extemporal_Composition\"
		";

        $queryFrom = "
		    -- from
		    rls.v_Extemporal ext
			left join rls.v_ExtemporalCompStandart ecs on ecs.Extemporal_id = ext.Extemporal_id
			left join Org org on org.Org_id = ecs.Org_id
			left join rls.v_ExtemporalType et on et.ExtemporalType_id = ext.ExtemporalType_id
			-- end from
		";

        $query = "
			select
			-- select
				{$querySelect}
			-- end select
			from
			    {$queryFrom}
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
              ACTMATTERS_ID as \"ACTMATTERS_ID\",
              TRADENAMES_ID as \"TRADENAMES_ID\",
              ExtemporalComp_id as \"ExtemporalComp_id\"
			from 
              rls.v_ExtemporalComp
			where 
              Extemporal_id = :Extemporal_id
		";

        //echo getDebugSql($query, $data); die();
        $res = $this->db->query($query, $data);
        if ( is_object($res) ) {
            $res = $res->result('array');
            if(!empty($data['RlsActmatters_id'])){
                $field = 'RlsActmatters_id';
                $tField = 'ACTMATTERS_ID';
            } else if(!empty($data['Tradenames_id'])){
                $field = 'Tradenames_id';
                $tField = 'TRADENAMES_ID';
            } else {
                return [['cnt'=>0]]; // гипотетический вариант, т.к. на форме поле компонент обязательно
            }
            foreach ($res as $value) {
                if($value[$tField] == $data[$field]){
                    if(empty($data['ExtemporalComp_id']) || $data['ExtemporalComp_id'] != $value['ExtemporalComp_id'])
                        return [['cnt' => 1]];
                }
            }
            return [['cnt'=>0]];
        } else {
            return [['cnt' => 0]];
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
              Extemporal_id as \"Extemporal_id\"
			from 
			  rls.v_Extemporal
			where 
			  Extemporal_Name = :Extemporal_Name 
            {$where}
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
			from 
			  rls.v_Extemporal
			where 
			  Extemporal_Code > 0
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
			  Extemporal_id as \"Extemporal_id\"
			from 
			  rls.v_Extemporal
			where 
			  Extemporal_IsClean = :Extemporal_IsClean 
			and 
			  Extemporal_id <> :Extemporal_id
		";
		$res = $this->db->query($query, $data);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if(count($res)>0){
				foreach ($res as $value) {
					$query = "
						select 
						  count(*) as cnt
						from 
						  rls.v_ExtemporalComp
						where 
						  Extemporal_id = :Extemporal_id
					";
					$ress = $this->db->query($query, array('Extemporal_id'=>$value['Extemporal_id']));
					if ( is_object($ress) ) {
						$ress = $ress->result('array');
						if($ress[0]['cnt'] == $data['count']){
							$query = "
								select 
								  ACTMATTERS_ID as \"ACTMATTERS_ID\"
								from 
								  rls.v_ExtemporalComp
								where 
								  Extemporal_id = :Extemporal_id
							";
							$resl = $this->db->query($query, array('Extemporal_id'=>$value['Extemporal_id']));
							if ( is_object($resl) ) {
								$resl = $resl->result('array');
								if(strlen($data['actmatters'])>0){
									$actmatters = $data['actmatters'];
									$actmatters = explode(",",rtrim($actmatters, ","));
									if(count($actmatters) == count($resl)){
										$reslt = array();
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
								  TRADENAMES_ID as \"TRADENAMES_ID\"
								from 
								  rls.v_ExtemporalComp
								where 
								  Extemporal_id = :Extemporal_id
							";
							$resl = $this->db->query($query, array('Extemporal_id'=>$value['Extemporal_id']));
							if ( is_object($resl) ) {
								$resl = $resl->result('array');
								if(strlen($data['tradenames'])>0){
									$tradenames = $data['tradenames'];
									$tradenames = explode(",",rtrim($tradenames, ","));
									if(count($tradenames) == count($resl)){
										$reslt = array();
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
			  ACTMATTERS_ID as \"ACTMATTERS_ID\",
			  TRADENAMES_ID as \"TRADENAMES_ID\"
			from 
			  rls.v_ExtemporalComp
			where 
			  Extemporal_id = :Extemporal_id
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
			  ExtemporalCompStandart_id as \"ExtemporalCompStandart_id\"
			from 
			  rls.v_ExtemporalCompStandart
			where 
			  Org_id = :Org_id 
			and 
			  Extemporal_id = :Extemporal_id 
			{$where}
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

        $querySelect = "
		    ext.Extemporal_Name as \"TRADENAMES_NAME\",
            ext.CLSDRUGFORMS_ID as \"CLSDRUGFORMS_ID\",
            cls.NAME as \"CLSDRUGFORMS_NAME\",
            (
                SELECT
                    string_agg(rtrim(coalesce(ec.ExtemporalComp_Name,''))||' '||cast(cast(ec.ExtemporalComp_Count as float) as varchar)||' '||coalesce(gu.GoodsUnit_Name,''), ', ')
                FROM
                    rls.v_ExtemporalComp ec
                    left join v_GoodsUnit gu on gu.GoodsUnit_id = ec.GoodsUnit_id
                WHERE
                    ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
			) as \"Composition\",
            (
                SELECT
                    string_agg(rtrim(coalesce(ec.ExtemporalComp_Name,''))||' '||cast(cast(ec.ExtemporalComp_Count as float) as varchar)||' '||coalesce(gu.GoodsUnit_Name,''), ', ')
                FROM
                    rls.v_ExtemporalComp ec
                    left join v_GoodsUnit gu on gu.GoodsUnit_id = ec.GoodsUnit_id
                WHERE
                    ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id <> 1
			) as \"CompositionNotMain\",
            (
                SELECT
                    string_agg(rtrim(coalesce(am.RUSNAME,'')), ' + ')
                FROM
                    rls.v_ExtemporalComp ec 
                    left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
                WHERE
                    ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
            ) as \"Actmatters_Names\",
            (
                SELECT
                    string_agg(cast(ec.ACTMATTERS_ID as varchar(30)), '|')
                FROM
                    rls.v_ExtemporalComp ec
                WHERE
                    ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1 and ec.ACTMATTERS_ID is not null
            ) as \"ACTMATTERS\", 
            (
                SELECT
                    string_agg(rtrim(am.LATNAME),  ' + ')
                FROM
                    rls.v_ExtemporalComp ec
                    left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
                WHERE
                    ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
       
            ) as \"Actmatters_LatName\",
            (
                SELECT
                    string_agg(rtrim(am.ACTMATTERS_LatNameGen), ' + ')
                FROM
                    rls.v_ExtemporalComp ec
                    left join rls.v_ACTMATTERS am on am.ACTMATTERS_ID = ec.ACTMATTERS_ID
                WHERE
                    ec.Extemporal_id = ext.Extemporal_id and ec.ExtemporalCompType_id = 1
            ) as \"Actmatters_LatNameGen\"
		";

        $query = "
			select
			-- select
				{$querySelect}
			-- end select
			from
			-- from
			rls.v_Extemporal ext 
			left join rls.v_CLSDRUGFORMS cls on cls.CLSDRUGFORMS_ID = ext.CLSDRUGFORMS_ID
			-- end from
			where
				-- where
				{$filter}
			and ext.Extemporal_id = :Extemporal_id
				-- end where
			limit 1
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