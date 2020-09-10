<?php
defined('BASEPATH') or die('No direct script access allowed');
/**
 * RegistryUfaFilterGrid_model - модель для работы фильтра грида
 * клиентская часть swFilterGridPluginUfa.js
 * 
 * @package      Admin
 * @access       public
 * @version      26/04/2013
 */

require("RegistryUfa_model.php");

class RegistryUfaFilterGrid_model extends RegistryUfa_model
{
    
    var $scheme = "r2";
    var $isufa = true;
    var $region = 'ufa';
    /**
     * comments
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Определение вьюхи записей реестра
     */
    function getRegistryDataObject($data)
    {
        $this->setRegistryParamsByType($data);
        return $this->scheme . '.v_' . $this->RegistryDataObject;
    }
    
    /**
     * 2 метода из хелпера SQL Sql_helper.php - переписаны под себя - для пагинации данных в окне фильтра
     */
    function _getLimitSQLPH($query, $distinct, $field, $start = 0, $limit = 1000, $like, $order_row = '')
    {
        $start = ($start == 0) ? (int) 0 : $start;
        
        $exp    = preg_match("/--[\s]*select([\w\W]*)--[\s]*end select/i", $query, $maches);
        $select = $maches[1];
        
        $exp  = preg_match("/--[\s]*from([\w\W]*)--[\s]*end from/i", $query, $maches);
        $from = $maches[1];
        
        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $query, $maches);
        if (isset($maches[1])) {
            $where = $maches[1] . " " . $like;
        }
        
        $query = "WITH temptable AS
				(
					SELECT  distinct " . $order_row . " field,
					DENSE_RANK() OVER (ORDER BY " . $order_row . ") AS RowNumber
					FROM " . $from . "  
					-- WHERE " . $order_row . " IS NOT NULL 
					" . (empty($where) ? "" : "WHERE " . $where) . " 
				) 
				SELECT  " . $distinct . " field
					FROM temptable with(nolock)
				--WHERE RowNumber BETWEEN " . $start . " AND " . $limit . " " . (!empty($order_row) ? "" : "field") . ";";
        
        
        return $query;
    }
    /**
     * comments
     */
    function _getCountSQLPH($sql, $field, $distinct, $orderBy)
    {
        $sql = preg_replace("/--[\s]*select[\w\W]*--[\s]*end select/i", " distinct count( " . $distinct . " isnull(" . $orderBy . ",'') ) AS cnt ", $sql);
        
        $exp = preg_match("/--[\s]*where([\w\W]*)--[\s]*end where/i", $sql, $maches);
        if (isset($maches[1])) {
            $where = $maches[1];
        }
        
        $sql = preg_replace("/ORDER BY[\s]*--[\s]*order by[\w\W]*--[\s]*end order by/i", "", $sql);
        
        $sql = preg_replace("/GROUP BY[\s]*--[\s]*group by[\w\W]*--[\s]*end group by/i", "", $sql);
        
        return $sql;
        
    }
    
    /** end*/
    
    ////refs #86094
    /**
     * 
     * comment
     */
    function loadUnionRegistryErrorTFOMSFilter($data)
    {
        if ($data['Registry_id'] <= 0) {
            return false;
        }
        
        if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
            return false;
        }
        
        
        //Фильтр грида
        $json = isset($data['Filter']) ? toUTF(trim($data['Filter'], '"')) : false;
        
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Lpu_id' => $data['session']['lpu_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value'])) . "%"
        );
        
        $filter = "(1=1)";
        
        $join   = "";
        $fields = "";
        
        if ($filter_mode['type'] == 'unicFilter') {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if ($filter_mode['cell'] == 'EvnPL_NumCard') {
                $field   = 'EvnPL_NumCard';
                $orderBy = 'RD.NumCard';
            } else if ($filter_mode['cell'] == 'Diag_Code') {
                $field   = 'D.Diag_Code';
                $orderBy = 'D.Diag_Code';
            } else if ($filter_mode['cell'] == 'LpuSection_name') {
                $field   = 'RD.LpuSection_name';
                $orderBy = 'RD.LpuSection_name';
            } else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
                $field   = 'LB.LpuBuilding_Name';
                $orderBy = 'LB.LpuBuilding_Name';
            }
            ////refs #86094
            else if ($filter_mode['cell'] == 'VolumeType_Code') {
                $field   = 'VT.VolumeType_Code';
                $orderBy = 'VT.VolumeType_Code';
            } else if ($filter_mode['cell'] == 'RegistryErrorType_Code') {
                $field   = 'ret.RegistryErrorType_Code';
                $orderBy = 'ret.RegistryErrorType_Code';
            } else if ($filter_mode['cell'] == 'Person_FIO') {
                $field   = 'ps.Person_FIO';
                $orderBy = 'ps.Person_FIO';
            } else if ($filter_mode['cell'] == 'Evn_id') {
                $field   = 'RE.Evn_id';
                $orderBy = 'RE.Evn_id';
            } else if ($filter_mode['cell'] == 'Person_id') {
                $field   = 'ps.Person_id';
                $orderBy = 'ps.Person_id';
            } else if ($filter_mode['cell'] == 'LpuSection_Name') {
                $field   = 'LS.LpuSection_Name';
                $orderBy = 'LS.LpuSection_Name';   
            }    
            //
            else if ($filter_mode['cell'] == 'Paid') {
                $field   = 'RD.Paid_id';
                $orderBy = 'RD.Paid_id';
            } else if ($filter_mode['cell'] == 'Usluga_Code') {
                
                if ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) {
                    $field   = 'U.UslugaComplex_Code';
                    $orderBy = 'U.UslugaComplex_Code';
                } else {
                    $field   = 'm.Mes_Code';
                    $orderBy = 'm.Mes_Code';
                }
            }
            //var_dump($field);
            $field    = $filter_mode['cell'];
            $orderBy  = isset($orderBy) ? $orderBy : $filter_mode['cell'];
            $Like     = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
            $with     = "WITH";
            $distinct = 'DISTINCT';
        } else {
            return false;
        }
        
        $orderBy = isset($orderBy) ? $orderBy : null;
        
        $distinct = isset($distinct) ? $distinct : '';
        $with     = isset($with) ? $with : '';
        
        $this->setRegistryParamsByType($data);
        
        $params = array(
            'Registry_id' => $data['Registry_id']
        );
        $filter = "(1=1)";
        if (isset($data['Person_SurName'])) {
            $filter .= " and ps.Person_SurName like :Person_SurName ";
            $params['Person_SurName'] = $data['Person_SurName'] . "%";
        }
        if (isset($data['Person_FirName'])) {
            $filter .= " and ps.Person_FirName like :Person_FirName ";
            $params['Person_FirName'] = $data['Person_FirName'] . "%";
        }
        if (isset($data['Person_SecName'])) {
            $filter .= " and ps.Person_SecName like :Person_SecName ";
            $params['Person_SecName'] = $data['Person_SecName'] . "%";
        }
        if (isset($data['RegistryErrorType_Code'])) {
            $filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
            $params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
        }
        if (isset($data['Person_FIO'])) {
            $filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
            $params['Person_FIO'] = $data['Person_FIO'] . "%";
        }
        if (!empty($data['Evn_id'])) {
            $filter .= " and RE.Evn_id = :Evn_id ";
            $params['Evn_id'] = $data['Evn_id'];
        }
        
        $regData = $this->queryResult("select Registry_IsNotInsur, OrgSmo_id from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id", array(
            'Registry_id' => $data['Registry_id']
        ));
        
        if (empty($regData[0])) {
            return array(
                'Error_Msg' => 'Ошибка получения данных по реестру'
            );
        }
        $Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];
        $OrgSmo_id           = $regData[0]['OrgSmo_id'];
        $filter_rd           = " and RD.OrgSmo_id = RF.OrgSmo_id";
        if ($Registry_IsNotInsur == 2) {
			// если реестр по СМО, то не зависимо от соц. статуса
			if ($this->RegistryType_id == 6) {
				$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
			} else {
				$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
			}
        } else if ($OrgSmo_id == 8) {
            // инотеры
            $filter_rd = " and RD.Polis_id IS NOT NULL";
            $filter .= " and IsNull(os.OrgSMO_RegNomC,'')=''";
        }
        
        $query = "
			Select
				-- select
				RE.RegistryErrorTFOMS_id,
				RE.Registry_id,
				R.RegistryType_id,
				Evn.Evn_rid,
				RE.Evn_id,
				Evn.EvnClass_id,
				ret.RegistryErrorType_Code,
				ret.RegistryErrorType_Name,
				RegistryErrorType_Descr + ' (' +RETF.RegistryErrorTFOMSField_Name + ')' as RegistryError_Comment,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				re.RegistryErrorTFOMS_FieldName,
				re.RegistryErrorTFOMS_BaseElement,
				re.RegistryErrorTFOMS_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RE.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				retl.RegistryErrorTFOMSLevel_Name,
				null as IsGroupEvn,
				LS.LpuSection_Name,
				LB.LpuBuilding_Name,
				RD.MedPersonal_Fio,
				convert(varchar,cast(RD.Evn_setDate as datetime),104) as Evn_setDate,
				convert(varchar,cast(RD.Evn_disDate as datetime),104) as Evn_disDate
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryGroupLink RGL (nolock)
				inner join {$this->scheme}.v_Registry RF (nolock) on RF.Registry_id = RGL.Registry_pid
				inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id
				inner join {$this->scheme}.v_RegistryErrorTFOMS RE with (nolock) on RE.Registry_id = R.Registry_id
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id {$filter_rd}
				left join v_OrgSmo os (nolock) on os.OrgSmo_id = rd.OrgSmo_id
				left join v_LpuSection ls with(nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit lu with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
				left join RegistryErrorTFOMSField RETF with (nolock) on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join r2.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_RegistryErrorTFOMSLevel retl with (nolock) on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_id
				and
				{$filter}
				-- end where
			order by
                                -- order by
                                {$field}
                                -- end order by    
		";
        /*
        echo getDebugSql($query, $params);
        exit;
        */
        
        $result       = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
        $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    
    /**
     * comment
     */
    function loadUnionRegistryErrorTFOMS($data)
    {
        if ($data['Registry_id'] <= 0) {
            return false;
        }
        
        if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
            return false;
        }
        
        $filterAddQueryTemp = null;
        
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);
            
            if (is_array($filterData)) {
                
                foreach ($filterData as $column => $value) {
                    
                    if (is_array($value)) {
                        $r = null;
                        
                        foreach ($value as $d) {
                            $r .= "'" . trim(toAnsi($d)) . "',";
                        }
                        
                        if ($column == 'Diag_Code')
                            $column = 'D.' . $column;
                        elseif ($column == 'EvnPL_NumCard')
                            $column = 'RD.NumCard';
                        elseif ($column == 'LpuSection_name')
                            $column = 'RD.' . $column;
                        elseif ($column == 'LpuBuilding_Name')
                            $column = 'LB.' . $column;
                        elseif ($column == 'Usluga_Code')
                            $column = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
                        //refs #86094
                        elseif ($column == 'VolumeType_Code')
                            $column = 'vt.' . $column;
                        //
                        elseif ($column == 'Evn_id')
                            $column = 'RE.' . $column;    
                        elseif ($column == 'Person_id')
                            $column = 'ps.' . $column;      
                        //Составное поле - надо отменить его по фильтру
                        elseif ($column == 'Person_FIO')
                            $column = "rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, ''))";   
                        elseif ($column == 'LpuSection_Name')
                            $column = 'LS.' . $column;                             
                        //refs #86094
                        elseif ($column == 'OrgSmo_Nick') {
                            $column = "case when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
                                                                    when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные'
                                                                    else '' end ";
                        } elseif ($column == 'RegistryErrorType_Code')
                            $column = 'ret.' . $column;
                        //
                        elseif ($column == 'Paid')
                            $column = 'RD.Paid_id';
                        elseif ($column == 'Polis_Num')
                            $column = 'RD.Polis_Num';

                        
                        
                        $r = rtrim($r, ',');
                        
                        //Костыль для фильтра Оплата = NULL
                        if ($r == "''") {
                            $filterAddQueryTemp[] = "(rtrim(ltrim(" . $column . ")) is null or  rtrim(ltrim(" . $column . ")) ='')";
                        } else {
                            $filterAddQueryTemp[] = $column . ' IN (' . $r . ')';
                        }
                        
                        //var_dump($column);
                        //var_dump($r);
                        //exit;
                        
                    }
                }
            }
            
            if (is_array($filterAddQueryTemp)) {
                $filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
            } else
                $filterAddQuery = "";
        }
        
        $filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
        
        $this->setRegistryParamsByType($data);
        
        $params = array(
            'Registry_id' => $data['Registry_id']
        );
        $filter = "(1=1)";
        if (isset($data['Person_SurName'])) {
            $filter .= " and ps.Person_SurName like :Person_SurName ";
            $params['Person_SurName'] = $data['Person_SurName'] . "%";
        }
        if (isset($data['Person_FirName'])) {
            $filter .= " and ps.Person_FirName like :Person_FirName ";
            $params['Person_FirName'] = $data['Person_FirName'] . "%";
        }
        if (isset($data['Person_SecName'])) {
            $filter .= " and ps.Person_SecName like :Person_SecName ";
            $params['Person_SecName'] = $data['Person_SecName'] . "%";
        }
        if (isset($data['RegistryErrorType_Code'])) {
            $filter .= " and RET.RegistryErrorType_Code = :RegistryErrorType_Code ";
            $params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
        }
        if (isset($data['Person_FIO'])) {
            $filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
            $params['Person_FIO'] = $data['Person_FIO'] . "%";
        }
        if (!empty($data['Evn_id'])) {
            $filter .= " and RE.Evn_id = :Evn_id ";
            $params['Evn_id'] = $data['Evn_id'];
        }
        if (!empty($data['Polis_Num'])) {
            $filter .= " and RD.Polis_Num = :Polis_Num ";
            $params['Polis_Num'] = $data['Polis_Num'];
        }

        $regData = $this->queryResult("select Registry_IsNotInsur, OrgSmo_id from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id", array(
            'Registry_id' => $data['Registry_id']
        ));
        
        if (empty($regData[0])) {
            return array(
                'Error_Msg' => 'Ошибка получения данных по реестру'
            );
        }
        $Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];
        $OrgSmo_id           = $regData[0]['OrgSmo_id'];
        $filter_rd           = " and RD.OrgSmo_id = RF.OrgSmo_id";
        if ($Registry_IsNotInsur == 2) {
			// если реестр по СМО, то не зависимо от соц. статуса
			if ($this->RegistryType_id == 6) {
				$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
			} else {
				$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
			}
        } else if ($OrgSmo_id == 8) {
            // инотеры
            $filter_rd = " and RD.Polis_id IS NOT NULL";
            $filter .= " and IsNull(os.OrgSMO_RegNomC,'')=''";
        }
        
        $query = "
			Select
				-- select
				RE.RegistryErrorTFOMS_id,
				RE.Registry_id,
				R.RegistryType_id,
				Evn.Evn_rid,
				RE.Evn_id,
				Evn.EvnClass_id,
				ret.RegistryErrorType_Code,
				ret.RegistryErrorType_Name,
				RegistryErrorType_Descr + ' (' +RETF.RegistryErrorTFOMSField_Name + ')' as RegistryError_Comment,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				re.RegistryErrorTFOMS_FieldName,
				re.RegistryErrorTFOMS_BaseElement,
				re.RegistryErrorTFOMS_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RE.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				retl.RegistryErrorTFOMSLevel_Name,
				null as IsGroupEvn,
				LS.LpuSection_Name,
				LB.LpuBuilding_Name,
				RD.MedPersonal_Fio,
				convert(varchar,cast(RD.Evn_setDate as datetime),104) as Evn_setDate,
				convert(varchar,cast(RD.Evn_disDate as datetime),104) as Evn_disDate
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryGroupLink RGL (nolock)
				inner join {$this->scheme}.v_Registry RF (nolock) on RF.Registry_id = RGL.Registry_pid
				inner join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RGL.Registry_id
				inner join {$this->scheme}.v_RegistryErrorTFOMS RE with (nolock) on RE.Registry_id = R.Registry_id
				inner join {$this->scheme}.v_{$this->RegistryDataObject} RD (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id {$filter_rd}
				left join v_OrgSmo os (nolock) on os.OrgSmo_id = rd.OrgSmo_id
				left join v_LpuSection ls with(nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit lu with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_Evn Evn with (nolock) on Evn.Evn_id = RE.Evn_id
				left join RegistryErrorTFOMSField RETF with (nolock) on RETF.RegistryErrorTFOMSField_Code = RE.RegistryErrorTFOMS_FieldName
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = Evn.PersonEvn_id and ps.Server_id = Evn.Server_id
				left join r2.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_RegistryErrorTFOMSLevel retl with (nolock) on retl.RegistryErrorTFOMSLevel_id = RE.RegistryErrorTFOMSLevel_id
				-- end from
			where
				-- where
				RGL.Registry_pid = :Registry_id
				and
				{$filter}
                                {$filterAddQuery}    
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";
        /*
        echo getDebugSql($query, $params);
        exit;
        */

        $result       = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    //
    
    /**
     * comments
     */
    function loadRegistryData($data)
    {
        if ($data['Registry_id'] == 0) {
            return false;
        }
        if (isset($data['RegistryType_id']) && $data['RegistryType_id'] == 0) {
            return false;
        }
        
        if ((isset($data['start']) && (isset($data['limit']))) && (!(($data['start'] >= 0) && ($data['limit'] >= 0)))) {
            return false;
        }
        
        $filterAddQueryTemp = null;
        
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);
            
            if (is_array($filterData)) {
                
                foreach ($filterData as $column => $value) {
                    
                    if (is_array($value)) {
                        $r = null;
                        
                        foreach ($value as $d) {
                            $r .= "'" . trim(toAnsi($d)) . "',";
                        }
                        
                        if ($column == 'Diag_Code')
                            $column = 'D.' . $column;
                        elseif ($column == 'EvnPL_NumCard')
                            $column = 'RD.NumCard';
                        elseif ($column == 'LpuSection_name')
                            $column = 'RD.' . $column;
                        elseif ($column == 'LpuBuilding_Name')
                            $column = 'LB.' . $column;
                        elseif ($column == 'Usluga_Code')
                            $column = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
                        //refs #86094
                        elseif ($column == 'VolumeType_Code')
                            $column = 'vt.' . $column;
                        elseif ($column == 'OrgSmo_Nick') {
                            $column = "case when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
                                                                    when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные'
                                                                    else '' end ";
                        } elseif ($column == 'RegistryData_Uet')
                            $column = 'RD.RegistryData_KdFact';
                        elseif ($column == 'HTMedicalCareClass_GroupCode')
                            $column = 'htm.' . $column;   
                        elseif ($column == 'Mes_Code_KSG')
                            $column = 'Mes.Mes_Code';
                        elseif ($column == 'LpuSectionProfile_Name')
                            $column = 'LSP.LpuSectionProfile_Name';
                        elseif ($column == 'MedSpecOms_Name')
                            $column = 'MSO.MedSpecOms_Name';
                        //
                        elseif ($column == 'Paid')
                            $column = 'RD.Paid_id';
                        elseif(strripos($column, '_id') !== false)
                            $column   = 'RD.' . $column;
                         
                        $r = rtrim($r, ',');
                        
                        //Костыль для фильтра Оплата = NULL
                        if ($r == "''") {
                            $filterAddQueryTemp[] = "(rtrim(ltrim(" . $column . ")) is null or  rtrim(ltrim(" . $column . ")) ='')";
                        } else {
                            $filterAddQueryTemp[] = $column . ' IN (' . $r . ')';
                        }
                        
                        //var_dump($column);
                        //var_dump($r);
                        //exit;
                        
                    }
                }
            }
            
            if (is_array($filterAddQueryTemp)) {
                $filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
            } else
                $filterAddQuery = "";
        }
        
        $filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
        
        $this->setRegistryParamsByType($data);
        
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Lpu_id' => $data['session']['lpu_id']
        );
        $filter = "(1=1)";
        if (isset($data['Person_SurName'])) {
            $filter .= " and RD.Person_SurName like :Person_SurName ";
            $params['Person_SurName'] = rtrim($data['Person_SurName']) . "%";
        }
        if (isset($data['Person_FirName'])) {
            $filter .= " and RD.Person_FirName like :Person_FirName ";
            $params['Person_FirName'] = rtrim($data['Person_FirName']) . "%";
        }
        if (isset($data['Person_SecName'])) {
            $filter .= " and RD.Person_SecName like :Person_SecName ";
            $params['Person_SecName'] = rtrim($data['Person_SecName']) . "%";
        }
        if (!empty($data['Polis_Num'])) {
            $filter .= " and RD.Polis_Num = :Polis_Num";
            $params['Polis_Num'] = $data['Polis_Num'];
        }
        
        if (!empty($data['MedPersonal_id'])) {
            $filter .= " and RD.MedPersonal_id = :MedPersonal_id";
            $params['MedPersonal_id'] = $data['MedPersonal_id'];
        }
        
        if (!empty($data['Evn_id'])) {
            $filter .= " and RD.Evn_id = :Evn_id";
            $params['Evn_id'] = $data['Evn_id'];
        }
        
        if (in_array($this->region, array(
            'ufa', null
        ))) {
            if (!empty($data['RegistryData_IsBadVol']) && $data['RegistryData_IsBadVol'] == 2) {
                $filter .= " and RD.RegistryData_IsBadVol = 2";
            } else {
                $filter .= " and ISNULL(RD.RegistryData_IsBadVol, 1) = 1";
            }
            
            if (!empty($data['VolumeType_id'])) {
                $filter .= " and RD.VolumeType_id = :VolumeType_id";
                $params['VolumeType_id'] = $data['VolumeType_id'];
            }
            
            if ($data['filterRecords'] == 2) {
                $filter .= " and ISNULL(RD.Paid_id,1) = 2";
            } elseif ($data['filterRecords'] == 3) {
                $filter .= " and ISNULL(RD.Paid_id,1) = 1";
            }
        }
        
        $join   = "";
        $fields = "";
        
        
        if (!in_array($this->region, array(
            'ufa',
            'pskov',
            null
        ))) {
            $join = "
                    outer apply (
                            select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
                    ) RDL
            ";
            $join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
            $fields .= "case when RDL.Person_id is null then 0 else 1 end as IsRDL, ";
            $fields .= "RD.needReform, RD.checkReform, RD.timeReform, ";
            $fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
                       
        } else {
            
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
				left join v_UslugaComplex U with (NOLOCK) on ISNULL(RD.Usluga_id,RD.UslugaComplex_id) =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_Diag D with (NOLOCK) on RD.Diag_id =  D.Diag_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RD.Evn_id
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
            $fields .= "
				case when RD.RegistryType_id in (1, 14) then
					m.Mes_Code
				else
					U.UslugaComplex_Code
				end as Usluga_Code, 
			";
            $fields .= "D.Diag_Code, case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as Paid, ";
            $fields .= "LB.LpuBuilding_Name, ";
            
            if ($this->region == 'ufa') {
                $fields .= "Mes.Mes_Code + ISNULL(' ' + Mes.MesOld_Num, '') as Mes_Code_KSG, RD.Mes_Code_KPG, ";
                $join .= "left join v_MesOld Mes with (nolock) on Mes.Mes_id = rd.MesItog_id ";
            }
        }
        
        if ($data['session']['region']['nick'] == 'perm') {
            if ($data['filterRecords'] == 2) {
                $filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 2";
            } elseif ($data['filterRecords'] == 3) {
                $filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 1";
            }
            
            // в реестрах со статусом частично принят помечаем оплаченные случаи
            $join .= "left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id ";
            $join .= "left join v_RegistryCheckStatus RCS with (nolock) on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id ";
            $fields .= "case when RCS.RegistryCheckStatus_Code = 3 then ISNULL(RD.RegistryData_IsPaid,1) else 0 end as RegistryData_IsPaid, ";
        }
        
        if ($data['session']['region']['nick'] == 'ufa') {
            $fields .= "isnull(RD.RegistryData_Sum_R, 0) as RegistryData_Sum_R,";
        }
        if ($data['session']['region']['nick'] == 'pskov') {
            $fields .= "0 as RegistryData_Sum_R,";
        }
        $select_uet = "RD.RegistryData_KdFact as RegistryData_Uet, ";
        // Определение УЕТ по регионам (для поликлиники)
        if ($data['RegistryType_id'] == 2) {
            switch ($this->region) {
                case 'ufa':
                    $select_uet = "RD.EvnVizit_UetOMS as RegistryData_Uet, ";
                    break;
                
                /*case 'kareliya':
                //В региональной модели
                break;*/
                
                case 'khak':
                    $select_uet = "case when (VT.VizitType_id=4 and dbo.AgeYMD(RD.Person_BirthDay,RD.Evn_disDate ,1)<18) then 1 else RD.RegistryData_KdPay end as RegistryData_Uet, ";
                    $join .= "left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id ";
                    $join .= "left join v_VizitType VT with (NOLOCK)on VT.VizitType_id = EVPL.VizitType_id ";
                    break;
                
                /*case 'astra':
                //В региональной модели
                break;*/
                
                case 'pskov':
                    $select_uet = "
                            case when (RD.LpuSectionProfile_Code in ('529', '530', '629', '630', '829', '830') or Usluga.UslugaComplex_id is not null)
                            then EVPL.EvnVizitPL_UetOMS else 1
                            end as RegistryData_Uet,
                    ";
                    $join .= "left join v_EvnVizitPL EVPL with (NOLOCK)on EVPL.EvnVizitPL_id = RD.Evn_id ";
                    $join .= "
						outer apply (
							select top 1
								UslugaComplex.UslugaComplex_id,
								UslugaComplex.UslugaComplex_Code
							from
								v_EvnUsluga EvnUsluga with (nolock)
								left join UslugaComplex with(nolock) on UslugaComplex.UslugaComplex_id = EvnUsluga.UslugaComplex_id
							where
								EvnUsluga.EvnUsluga_pid = RD.Evn_id
								and LEFT(UslugaComplex.UslugaComplex_Code,4) = 'A.07'
								and rd.LpuSectionProfile_Code in ('577','677','877')
							order by EvnUsluga_id
						) as Usluga
					";
                    break;
            }
        }
        $fields .= $select_uet;
        
        if (in_array($data['RegistryType_id'], array(
            7,
            12
        ))) {
            $join .= "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
            $fields .= "epd.DispClass_id, ";
        }

		if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
            $source_table = 'v_RegistryDeleted_Data';
        } else {
            $source_table = 'v_' . $this->RegistryDataObject;
        }
        
        // https://redmine.swan.perm.ru/issues/35331
        $evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');
        
        //refs #86094    
        $view_db = $this->getRegistryDataObject($data);
        $join .= "left join v_OrgSmo os with (nolock) on os.OrgSmo_id = RD.OrgSmo_id ";
        
        
        $alias = (isset($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) ? 'R' : 'RD';

		if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
            $source_table = 'v_RegistryDeleted_Data';
        } else {
            $source_table = 'v_' . $this->RegistryDataObject;
        }
        
        if (isset($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) {
            // для финального берём по другому
            
            $regData = $this->queryResult("select Registry_IsNotInsur, OrgSmo_id, convert(varchar(10), Registry_accDate, 120) as Registry_accDate from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id", array(
                'Registry_id' => $data['Registry_id']
            ));
            
            if (empty($regData[0])) {
                return array(
                    'Error_Msg' => 'Ошибка получения данных по реестру'
                );
            }
			$Registry_accDate = $regData[0]['Registry_accDate'];
			$Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];
			$OrgSmo_id = $regData[0]['OrgSmo_id'];
            if ($Registry_IsNotInsur == 2) {
				// если реестр по СМО, то не зависимо от соц. статуса
				if ($this->RegistryType_id == 6) {
					$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				} else {
					$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
				}
            } else if ($OrgSmo_id == 8) {
                // инотеры
                $filter_rd = " and RD.Polis_id IS NOT NULL";
                $filter .= " and IsNull(os.OrgSMO_RegNomC,'')=''";
            }
			else {
				// @task https://redmine.swan.perm.ru//issues/109876
				if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8000233 ) {
					return false;
				}
				else if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8001229 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
				}
				else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8000227 ) {
					return false;
				}
				else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8001750 ) {
					$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
				}
				else {
					$filter_rd = " and RD.OrgSmo_id = R.OrgSmo_id";
				}
			}

            if ($this->region == 'ufa') {
                if($this->RegistryDataObject == 'RegistryDataEvnPS'){
                    $join .= "left join v_HTMedicalCareClass htm (nolock) on htm.HTMedicalCareClass_id = ES.HTMedicalCareClass_id ";
                }
            }
            
            $from = "
                                {$this->scheme}.Registry R
                                inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_pid = R.Registry_id
                                inner join {$view_db} RD with (NOLOCK) on RGL.Registry_id = RD.Registry_id {$filter_rd}
                        ";
            
            if ($data['filterRecords'] == 2) {
                $filter .= " and ISNULL(RD.Paid_id,1) = 2";
            } elseif ($data['filterRecords'] == 3) {
                $filter .= " and ISNULL(RD.Paid_id,1) = 1";
            }
            
        } else {
            if($this->RegistryDataObject == 'RegistryDataEvnPS'){
                $join .= "left join v_HTMedicalCareClass htm (nolock) on htm.HTMedicalCareClass_id = RD.HTMedicalCareClass_id ";    
            }
            $from = "
                             {$view_db} RD with (NOLOCK) 
                    ";
        }
                 
        if ($this->RegistryDataObject == 'RegistryDataEvnPS') {
            $fields .= "htm.HTMedicalCareClass_GroupCode, ";
            $fields .= "htm.HTMedicalCareClass_Name, ";
        }        
        //      
        $query = "
			Select 
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				{$fields}
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name,
				RD.RegistryData_deleted,
				RTrim(RD.NumCard) as EvnPL_NumCard,
				RTrim(RD.Person_FIO) as Person_FIO,
				RD.Polis_Num,
				RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_id,
				RTrim(RD.LpuSection_name) as LpuSection_name,
				RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
				RTrim(IsNull(convert(varchar,cast(RD.{$evnVizitPLSetDateField} as datetime),104),'')) as EvnVizitPL_setDate,
				RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RD.RegistryData_Tariff RegistryData_Tariff,
				--RD.RegistryData_KdFact as RegistryData_Uet,
				RD.RegistryData_KdPay as RegistryData_KdPay,
				RD.RegistryData_KdPlan as RegistryData_KdPlan,
				RD.RegistryData_ItogSum as RegistryData_ItogSum,
				RegistryError.Err_Count as Err_Count,
				RHDCR.RegistryHealDepResType_id,
				vt.VolumeType_Code,
                                --refs #86094
                                case
                                when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
                                when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные'
                                else ''
				end as OrgSmo_Nick,
				case 
					when RETLast.RegistryErrorTFOMS_id is not null and e.Evn_updDT >= RETLast.RegistryErrorTFOMS_insDT then 3
					when RETLast.RegistryErrorTFOMS_id is not null and e.Evn_updDT < RETLast.RegistryErrorTFOMS_insDT then 2
					else 1
				end as RegistryData_IsEarlier
				--
				-- end select
			from
				-- from
				{$from}
				left join v_Evn e (nolock) on e.Evn_id = rd.Evn_id
				{$join}
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
				left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
				left join v_VolumeType vt (nolock) on vt.VolumeType_id = rd.VolumeType_id
				outer apply
				(
					Select count(*) as Err_Count
					from {$this->scheme}.v_RegistryError RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
				) RegistryError
				outer apply
				(
					Select top 1 PersonEvn_id
					from v_PersonEvn PE with (NOLOCK)
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
				) PersonEvn
				outer apply (
					select top 1 RegistryErrorTFOMS_id, RegistryErrorTFOMS_insDT
					from {$this->scheme}.v_RegistryErrorTFOMS RET with (NOLOCK)
					where RD.Evn_id = RET.Evn_id
						and RD.Registry_id <> RET.Registry_id
					order by RegistryErrorTFOMS_insDT desc
				) RETLast
			-- end from
			where
				-- where
				{$alias}.Registry_id=:Registry_id
				and
				{$filter}
                                {$filterAddQuery}
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";
        //echo getDebugSQL($query, $params);
        //echo  toAnsi($query);
        //exit;
        
        $result       = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            
            return $response;
        } else {
            return false;
        }
    }
    /**
     * comments
     */
    function loadRegistryDataFilter($data)
    {
        
        if ($data['Registry_id'] == 0) {
            return false;
        }
        if ($data['RegistryType_id'] == 0) {
            return false;
        }

        //Фильтр грида
        $json = isset($data['Filter']) ? toUTF(trim($data['Filter'], '"')) : false;
        
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

		$this->setRegistryParamsByType($data);
        // Взависимости от типа реестра возвращаем разные наборы данных
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Lpu_id' => $data['session']['lpu_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value'])) . "%"
        );
        
        $filter = "(1=1)";
        
        $join   = "";
        $fields = "";
        
        if (empty($this->isufa)) {
            $join = "
				outer apply (
					select top 1 RDLT.Person_id from RegistryDataLgot RDLT with (NOLOCK) where RD.Person_id = RDLT.Person_id and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
				) RDL
			";
            $join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
            $fields = "case when RDL.Person_id is null then 0 else 1 end as IsRDL, ";
            $fields .= "RD.needReform, RD.checkReform, RD.timeReform, ";
            $fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
        } else {
            
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
                    left join v_UslugaComplex U with (NOLOCK) on ISNULL(RD.Usluga_id,RD.UslugaComplex_id) =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
                    left join v_Diag D with (NOLOCK) on RD.Diag_id =  D.Diag_id
                    left join v_EvnSection es (nolock) on ES.EvnSection_id = RD.Evn_id
                    left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
                    left join v_LpuSection LS (nolock) on LS.LpuSection_id = RD.LpuSection_id
                    left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
                    left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
            ";
            $fields .= "
				case when RD.RegistryType_id in (1, 14) then
					m.Mes_Code
				else
					U.UslugaComplex_Code
				end as Usluga_Code, 
			";
            $fields .= "D.Diag_Code, case when RD.Paid_id = 1 then 'Нет' when RD.Paid_id = 2 then 'Да' else '' end as Paid, ";
            $fields .= "LB.LpuBuilding_Name, ";
        }
        
        if ($data['session']['region']['nick'] == 'perm') {
            if ($data['filterRecords'] == 2) {
                $filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 2";
            } elseif ($data['filterRecords'] == 3) {
                $filter .= " and ISNULL(RD.RegistryData_IsPaid,1) = 1";
            }
            
            // в реестрах со статусом частично принят помечаем оплаченные случаи
            $join .= "left join {$this->scheme}.v_Registry R with (nolock) on R.Registry_id = RD.Registry_id ";
            $join .= "left join v_RegistryCheckStatus RCS with (nolock) on R.RegistryCheckStatus_id = RCS.RegistryCheckStatus_id ";
            $fields .= "case when RCS.RegistryCheckStatus_Code = 3 then ISNULL(RD.RegistryData_IsPaid,1) else 0 end as RegistryData_IsPaid, ";
        }
        
        if (in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes())) {
            
            if ($filter_mode['type'] == 'unicFilter') {
                $prefix = '';
                //Подгоняем поля под запрос с WITH
                if ($filter_mode['cell'] == 'EvnPL_NumCard') {
                    $field   = 'EvnPL_NumCard';
                    $orderBy = 'RD.NumCard';
                } else if ($filter_mode['cell'] == 'Diag_Code') {
                    $field   = 'D.Diag_Code';
                    $orderBy = 'D.Diag_Code';
                } else if ($filter_mode['cell'] == 'LpuSection_name') {
                    $field   = 'RD.LpuSection_name';
                    $orderBy = 'RD.LpuSection_name';
                } else if ($filter_mode['cell'] == 'LpuBuilding_Name') {
                    $field   = 'LB.LpuBuilding_Name';
                    $orderBy = 'LB.LpuBuilding_Name';
                }
                ////refs #86094
                else if ($filter_mode['cell'] == 'VolumeType_Code') {
                    $field   = 'VT.VolumeType_Code';
                    $orderBy = 'VT.VolumeType_Code';
                } else if ($filter_mode['cell'] == 'RegistryData_Uet') {
                    $field   = 'RD.RegistryData_KdFact';
                    $orderBy = 'RD.RegistryData_KdFact';
                }
                else if ($filter_mode['cell'] == 'Mes_Code_KSG') {
                    $field   = 'Mes.Mes_Code';
                    $orderBy = 'Mes.Mes_Code';
                }
                else if ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
                    $field   = 'LSP.LpuSectionProfile_Name';
                    $orderBy = 'LSP.LpuSectionProfile_Name';
                }
                else if ($filter_mode['cell'] == 'MedSpecOms_Name') {
                    $field   = 'MSO.MedSpecOms_Name';
                    $orderBy = 'MSO.MedSpecOms_Name';
                }
                //
                else if ($filter_mode['cell'] == 'Paid') {
                    $field   = 'RD.Paid_id';
                    $orderBy = 'RD.Paid_id';
                } else if ($filter_mode['cell'] == 'Usluga_Code') {
                    
                    if ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) {
                        $field   = 'U.UslugaComplex_Code';
                        $orderBy = 'U.UslugaComplex_Code';
                    } else {
                        $field   = 'm.Mes_Code';
                        $orderBy = 'm.Mes_Code';
                    }
                }else if(strripos($filter_mode['cell'], '_id') !== false){
                    $field   = 'RD.' . $filter_mode['cell'];
                    $orderBy = 'RD.' . $filter_mode['cell'];
                }
                
                $field    = isset($field) ? $field : $filter_mode['cell'];
                $orderBy  = isset($orderBy) ? $orderBy : $filter_mode['cell'];
                $Like     = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
                $with     = "WITH";
                $distinct = 'DISTINCT';
            } else {
                return false;
            }
            
            $orderBy = isset($orderBy) ? $orderBy : null;
            
            $distinct = isset($distinct) ? $distinct : '';
            $with     = isset($with) ? $with : '';
            
            $view_db = $this->getRegistryDataObject($data);
            
            $evnVizitPLSetDateField = ($this->RegistryType_id == 7 ? 'Evn_didDate' : 'Evn_setDate');
            
            //refs #86094    
            $join .= "left join v_VolumeType vt (nolock) on vt.VolumeType_id = RD.VolumeType_id ";
            $join .= "left join v_OrgSmo os with (nolock) on os.OrgSmo_id = RD.OrgSmo_id ";
            
            /*if(in_array($data['RegistryType_id'], array(1,14))){
                $join .= " left join v_HTMedicalCareClass htm (nolock) on htm.HTMedicalCareClass_id = rd.HTMedicalCareClass_id ";
            }*/
            
            $alias = (isset($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) ? 'R' : 'RD';

			if (isset($data['RegistryStatus_id']) && (12 == $data['RegistryStatus_id'])) {
                $source_table = 'v_RegistryDeleted_Data';
            } else {
                $source_table = 'v_' . $this->RegistryDataObject;
            }
            
            if (isset($data['RegistrySubType_id']) && $data['RegistrySubType_id'] == 2) {
                // для финального берём по другому
                
                $regData = $this->queryResult("select Registry_IsNotInsur, OrgSmo_id, convert(varchar(10), Registry_accDate, 120) as Registry_accDate from {$this->scheme}.v_Registry (nolock) where Registry_id = :Registry_id", array(
                    'Registry_id' => $data['Registry_id']
                ));
                
                if (empty($regData[0])) {
                    return array(
                        'Error_Msg' => 'Ошибка получения данных по реестру'
                    );
                }
				$Registry_accDate = $regData[0]['Registry_accDate'];
				$Registry_IsNotInsur = $regData[0]['Registry_IsNotInsur'];
				$OrgSmo_id = $regData[0]['OrgSmo_id'];
                if ($Registry_IsNotInsur == 2) {
					// если реестр по СМО, то не зависимо от соц. статуса
					if ($this->RegistryType_id == 6) {
						$filter_rd = " and (RD.Polis_id IS NULL or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
					} else {
						$filter_rd = " and ((RD.Polis_id IS NULL and rd.SocStatus_id in (10000079,112)) or (RD.OrgSmo_id = 8 and rd.OmsSprTerr_Code='61'))";
					}
                } else if ($OrgSmo_id == 8) {
                    // инотеры
                    $filter_rd = " and RD.Polis_id IS NOT NULL";
                    $filter .= " and IsNull(os.OrgSMO_RegNomC,'')=''";
                }
				else {
					// @task https://redmine.swan.perm.ru//issues/109876
					if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8000233 ) {
						return false;
					}
					else if ( $Registry_accDate >= '2017-05-25' && $OrgSmo_id == 8001229 ) {
						$filter_rd = " and RD.OrgSmo_id in (8000233, 8001229)";
					}
					else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8000227 ) {
						return false;
					}
					else if ( $Registry_accDate >= '2018-10-25' && $OrgSmo_id == 8001750 ) {
						$filter_rd = " and RD.OrgSmo_id in (8000227, 8001750)";
					} else {
						$filter_rd = " and RD.OrgSmo_id = R.OrgSmo_id";
					}
				}

                if ($this->region == 'ufa') {
                    $fields .= "Mes.Mes_Code + ISNULL(' ' + Mes.MesOld_Num, '') as Mes_Code_KSG, RD.Mes_Code_KPG, ";
                    $join .= "
                                                    left join v_MesOld Mes with (nolock) on Mes.Mes_id = rd.MesItog_id
                                            ";
                    if ($this->RegistryDataObject == 'RegistryDataEvnPS') {
                        $fields .= "htm.HTMedicalCareClass_GroupCode, ";
                        $fields .= "htm.HTMedicalCareClass_Name, ";
                        $join .= "left join v_HTMedicalCareClass htm (nolock) on htm.HTMedicalCareClass_id = rd.HTMedicalCareClass_id";
                    }
                    
       
                    if (!empty($data['RegistryData_IsBadVol']) && $data['RegistryData_IsBadVol'] == 2) {
                        
                        $filter .= " and RD.RegistryData_IsBadVol = 2";
                    } else {
                        $filter .= " and ISNULL(RD.RegistryData_IsBadVol, 1) = 1";
                    }
                    
                    if (!empty($data['VolumeType_id'])) {
                        $filter .= " and RD.VolumeType_id = :VolumeType_id";
                        $params['VolumeType_id'] = $data['VolumeType_id'];
                    }
                    
                    if ($data['filterRecords'] == 2) {
                        $filter .= " and ISNULL(RD.Paid_id,1) = 2";
                    } elseif ($data['filterRecords'] == 3) {
                        $filter .= " and ISNULL(RD.Paid_id,1) = 1";
                    }
                }
                
                $from = "
                                                {$this->scheme}.Registry R
                                                inner join {$this->scheme}.v_RegistryGroupLink RGL (nolock) on RGL.Registry_pid = R.Registry_id
                                                inner join {$view_db} RD with (NOLOCK) on RGL.Registry_id = RD.Registry_id {$filter_rd}
                                        ";
                
                if ($data['filterRecords'] == 2) {
                    $filter .= " and ISNULL(RD.Paid_id,1) = 2";
                } elseif ($data['filterRecords'] == 3) {
                    $filter .= " and ISNULL(RD.Paid_id,1) = 1";
                }
                
            } else {
                $from = "
                                             {$view_db} RD with (NOLOCK) 
                                    ";
            }
            
            if (!empty($data['RegistryData_IsBadVol']) && $data['RegistryData_IsBadVol'] == 2) {

                $filter .= " and RD.RegistryData_IsBadVol = 2";
                $join .= "left join v_MesOld Mes with (nolock) on Mes.Mes_id = rd.MesItog_id ";
            } else {
                $filter .= " and ISNULL(RD.RegistryData_IsBadVol, 1) = 1";
                
            }
            
            $query = " 
				Select
				-- select
				RD.Evn_id,
				RD.Evn_rid,
				RD.EvnClass_id,
				RD.Registry_id,
				RD.RegistryType_id,
				RD.Person_id,
				RD.Server_id,
				PersonEvn.PersonEvn_id,
				{$fields}
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name,
				RD.RegistryData_deleted,
				RTrim(RD.NumCard) as EvnPL_NumCard,
				RTrim(RD.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RD.LpuSection_id,
				RTrim(RD.LpuSection_name) as LpuSection_name,
				RTrim(RD.MedPersonal_Fio) as MedPersonal_Fio,
				RTrim(IsNull(convert(varchar,cast(RD.{$evnVizitPLSetDateField} as datetime),104),'')) as EvnVizitPL_setDate,
				RTrim(IsNull(convert(varchar,cast(RD.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RD.RegistryData_Tariff RegistryData_Tariff,
				--RD.RegistryData_KdFact as RegistryData_Uet,
				RD.RegistryData_KdPay as RegistryData_KdPay,
				RD.RegistryData_KdPlan as RegistryData_KdPlan,
				RD.RegistryData_ItogSum as RegistryData_ItogSum,
				RegistryError.Err_Count as Err_Count,
				RHDCR.RegistryHealDepResType_id,
                                --refs #86094
                                                case
                                when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick
                                when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные'
                                else ''
				end as OrgSmo_Nick  
				--                              
				-- end select
				from
				-- from
				{$from}
				{$join}
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
				left join v_RegistryHealDepCheckRes RHDCR (nolock) on RHDCR.Registry_id = RD.Registry_id and RHDCR.Evn_id = RD.Evn_id
				outer apply
				(
					Select count(*) as Err_Count
					from {$this->scheme}.v_RegistryError RE with (NOLOCK) where RD.Evn_id = RE.Evn_id and RD.Registry_id = RE.Registry_id
				) RegistryError
				outer apply
				(
					Select top 1 PersonEvn_id
					from v_PersonEvn PE with (NOLOCK)
					where RD.Person_id = PE.Person_id and PE.PersonEvn_insDT <= isnull(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
				) PersonEvn
                                -- end from
				where
					-- where
					{$alias}.Registry_id=:Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					{$field}
					-- end order by              
			";
        }
        
        //echo getDebugSQL($query, $params);
        //exit;
        $data['limit'] = 10000;
        
        //refs #86094
        if ($field == 'OrgSmo_Nick') {
            $field = " case when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные' else '' end ";
            
            $orderBy = " case when OS.OrgSMO_RegNomC is not null then OS.OrgSmo_Nick  when RD.Polis_id IS NOT NULL and IsNull(os.OrgSMO_RegNomC,'')='' then 'Инотерриториальные' else '' end ";
        }
        //                
        
        $result       = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
        $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    /**
     * commetns
     */
    function loadRegistryErrorCom($data)
    {
        $filterAddQueryTemp = null;
        
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);
            
            if (is_array($filterData)) {
                
                foreach ($filterData as $column => $value) {
                    
                    if (is_array($value)) {
                        $r = null;
                        
                        foreach ($value as $d) {
                            $r .= "'" . trim(toAnsi($d)) . "',";
                        }
                        
                        //if($column == 'Diag_Code')
                        //    $column = 'D.'.$column;
                        //elseif($column == 'EvnPL_NumCard')
                        //    $column = 'RD.NumCard'; 
                        
                        $r = rtrim($r, ',');
                        
                        $filterAddQueryTemp[] = $column . ' IN (' . $r . ')';
                    }
                }
                
            }
            
            
            if (is_array($filterAddQueryTemp)) {
                $filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
            } else
                $filterAddQuery = "";
        }
        
        $filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
        
        if ($data['Registry_id'] == 0) {
            return false;
        }
        if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
            return false;
        }
        
        $params = array(
            'Registry_id' => $data['Registry_id']
        );
        if (empty($this->isufa)) {
            $tempscheme = $this->scheme;
        } else {
            $tempscheme = 'dbo';
        }
        $query = "
		Select
			RE.Registry_id,
			RE.RegistryErrorType_id,
			RE.RegistryErrorType_Code,
			RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
			RE.RegistryErrorType_Descr,
			RE.RegistryErrorClass_id,
			RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name
		from {$tempscheme}.v_RegistryErrorCom RE with (NOLOCK)
		where
			RE.Registry_id=:Registry_id 
			{$filterAddQuery}
		order by 
			RE.RegistryErrorType_Code
			";
        
        $result = $this->db->query(getLimitSQL($query, $data['start'], $data['limit']), $params);
        
        $result_count = $this->db->query(getCountSQL($query), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    /**
     * commetns
     */
    function loadRegistryErrorComFilter($data)
    {
        if ($data['Registry_id'] == 0) {
            return false;
        }
        if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
            return false;
        }
        
        //Фильтр грида
        $json        = isset($data['Filter']) ? toUTF(trim($data['Filter'], '"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value'])) . "%"
        );
        
        if ($filter_mode['type'] == 'unicFilter') {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if ($filter_mode['cell'] == 'EvnPL_NumCard') {
                $field   = 'EvnPL_NumCard';
                $orderBy = 'RD.NumCard';
            } elseif ($filter_mode['cell'] == 'Diag_Code') {
                $field   = 'D.Diag_Code';
                $orderBy = 'D.Diag_Code';
            } elseif ($filter_mode['cell'] == 'LpuSection_name') {
                $field   = 'RD.LpuSection_name';
                $orderBy = 'RD.LpuSection_name';
            } elseif ($filter_mode['cell'] == 'LpuBuilding_Name') {
                $field   = 'LB.LpuBuilding_Name';
                $orderBy = 'LB.LpuBuilding_Name';
            } elseif ($filter_mode['cell'] == 'Usluga_Code') {
                
                if ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) {
                    $field   = 'U.UslugaComplex_Code';
                    $orderBy = 'U.UslugaComplex_Code';
                } else {
                    $field   = 'm.Mes_Code';
                    $orderBy = 'm.Mes_Code';
                }
            }
            
            $field    = $filter_mode['cell'];
            $orderBy  = isset($orderBy) ? $orderBy : $filter_mode['cell'];
            $Like     = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
            $with     = "WITH";
            $distinct = 'DISTINCT';
        } else {
            return false;
        }
        
        $orderBy = isset($orderBy) ? $orderBy : null;
        
        $distinct = isset($distinct) ? $distinct : '';
        $with     = isset($with) ? $with : '';
        
        if (empty($this->isufa)) {
            $tempscheme = $this->scheme;
        } else {
            $tempscheme = 'dbo';
        }
        $query = "
		Select
		--select
			RE.Registry_id,
			RE.RegistryErrorType_id,
			RE.RegistryErrorType_Code,
			RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
			RE.RegistryErrorType_Descr,
			RE.RegistryErrorClass_id,
			RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name
		--end select
		from 
			--from
			{$tempscheme}.v_RegistryErrorCom RE with (NOLOCK)
			--end from
		where
			--where
			RE.Registry_id=:Registry_id

			--end where  
		order by 
		--order by
		RE.RegistryErrorType_Code
		--end order by
		";
        
        $result       = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
        $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    /**
     * comments
     */
    function loadRegistryError($data)
    {
        $filterAddQueryTemp = null;
        
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);
            
            if (is_array($filterData)) {

                foreach ($filterData as $column => $value) {
                    
                    if (is_array($value)) {
                        $r = null;
                        
                        foreach ($value as $d) {
                            $r .= "'" . trim(toAnsi($d)) . "',";
                        }
                        
                        if ($column == 'Evn_id')
                            $column = 'RE.' . $column;
                        elseif ($column == 'Person_FIO')
                            $column = 'RE.' . $column;
                        elseif ($column == 'LpuSection_name')
                            $column = 'RD.' . $column;
                        elseif ($column == 'LpuBuilding_Name')
                            $column = 'LB.' . $column;
                        elseif ($column == 'LpuSectionProfile_Name')
                            $column = 'LSP.' . $column;
                        elseif ($column == 'MedSpecOms_Name')
                            $column = 'MSO.' . $column;
                        elseif ($column == 'Usluga_Code')
                            $column = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
                        
                        $r = rtrim($r, ',');
                        
                        if ($r == "''") {
                            $filterAddQueryTemp[] = "(rtrim(ltrim(" . $column . ")) is null or  rtrim(ltrim(" . $column . ")) ='')";
                        } else {
                            $filterAddQueryTemp[] = $column . ' IN (' . $r . ')';
                        }
                    }
                }
                
            }
            
            if (is_array($filterAddQueryTemp)) {
                $filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
            } else
                $filterAddQuery = "";
            
            
            
        }
        
        $filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
        
        if ($data['Registry_id'] <= 0) {
            return false;
        }
        if (empty($data['nopaging'])) {
            if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
                return false;
            }
        }
        
        $this->setRegistryParamsByType($data);
        
        $params = array(
            'Registry_id' => $data['Registry_id']
        );
        
        $filter = "(1=1)";
        if (isset($data['Person_SurName'])) {
            $filter .= " and RE.Person_SurName like :Person_SurName ";
            $params['Person_SurName'] = $data['Person_SurName'] . "%";
        }
        if (isset($data['Person_FirName'])) {
            $filter .= " and RE.Person_FirName like :Person_FirName ";
            $params['Person_FirName'] = $data['Person_FirName'] . "%";
        }
        if (isset($data['Person_SecName'])) {
            $filter .= " and RE.Person_SecName like :Person_SecName ";
            $params['Person_SecName'] = $data['Person_SecName'] . "%";
        }
        if (isset($data['RegistryError_Code'])) {
            $filter .= " and RE.RegistryErrorType_Code = :RegistryError_Code ";
            $params['RegistryError_Code'] = $data['RegistryError_Code'];
        }
        if (isset($data['RegistryErrorType_id'])) {
            $filter .= " and RE.RegistryErrorType_id = :RegistryErrorType_id ";
            $params['RegistryErrorType_id'] = $data['RegistryErrorType_id'];
        }
        if (!empty($data['Evn_id'])) {
            $filter .= " and RE.Evn_id = :Evn_id";
            $params['Evn_id'] = $data['Evn_id'];
        }
        
        $join   = "";
        $fields = "";
        
        if (!in_array($this->region, array(
            'ufa',
            'pskov',
            'buryatiya'
        ))) {
            if (!empty($data['MedPersonal_id'])) {
                $filter .= " and RE.MedPersonal_id = :MedPersonal_id";
                $params['MedPersonal_id'] = $data['MedPersonal_id'];
            }
            
            $join .= "
				left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = RE.MedPersonal_id
				) MP
			";
            $fields .= "RD.needReform, RE.RegistryErrorType_Form, RE.MedStaffFact_id,"; // , RD.checkReform, RD.timeReform
            $fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
            $fields .= "RE.LpuUnit_id, RE.MedPersonal_id, MP.Person_Fio as MedPersonal_Fio, ";
        } else {
            if (!empty($data['MedPersonal_id'])) {
                $filter .= " and RD.MedPersonal_id = :MedPersonal_id";
                $params['MedPersonal_id'] = $data['MedPersonal_id'];
            }
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
				left join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RE.Registry_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id and R.RegistryType_id in (1, 14)
				left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id and R.RegistryType_id in (2, 17)
				left join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = RE.Evn_id and R.RegistryType_id in (4, 7)
				left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
				left join v_EvnVizitDispOrp evdo (nolock) on evdo.EvnVizitDispOrp_id = RE.Evn_id and R.RegistryType_id in (5, 9)
				left join v_EvnUslugaDispOrp eudo (nolock) on eudo.EvnUslugaDispOrp_pid = evdo.EvnVizitDispOrp_id
				outer apply (
					select top 1
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1 with (nolock)
						left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = evpl.EvnVizitPL_id
						and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
					order by
						t1.EvnUslugaCommon_setDT desc
				) EU
				left join v_UslugaComplex U (nolock) on COALESCE(eudd.UslugaComplex_id, eudo.UslugaComplex_id, EU.UslugaComplex_uid) =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RE.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = COALESCE(ES.MedPersonal_id, evpl.MedPersonal_id, eudd.MedPersonal_id, evdo.MedPersonal_id)
				) as MP
			";
            $fields .= "
				ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
				m.Mes_Code,
				U.UslugaComplex_Code as Usluga_Code, 
				R.RegistryType_id, 
				LB.LpuBuilding_Name, 
			";
        }
        
        $view_db = $this->getRegistryDataObject($data);
        
        
        if (in_array($data['RegistryType_id'], array(
            7,
            9,
            12,
			17
        ))) {
            $join .= "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
            $fields .= "epd.DispClass_id, ";
        }
        
        $query = "
			Select
				-- select
				RTrim(cast(RE.Registry_id as char))+RTrim(cast(IsNull(RE.Evn_id,0) as char))+RTrim(cast(RE.RegistryErrorType_id as char)) as RegistryError_id,
				RE.Registry_id,
				RE.Evn_id,
				RE.Evn_rid,
				RE.EvnClass_id,
				RE.RegistryErrorType_id,
				RE.RegistryErrorType_Code,
				{$fields}
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name,
				RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
				RE.RegistryErrorType_Descr,
				RE.Person_id,
				RE.Server_id,
				RE.PersonEvn_id,
				RTrim(RE.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RE.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RE.LpuSection_id,
				RTrim(RE.LpuSection_name) as LpuSection_name,
				RTrim(IsNull(convert(varchar,cast(RE.Evn_setDate as datetime),104),'')) as Evn_setDate,
				RTrim(IsNull(convert(varchar,cast(RE.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RE.RegistryErrorClass_id,
				RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryErrorObject} RE with (NOLOCK)
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
				{$join}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
                {$filterAddQuery}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";
        
        if (!empty($data['nopaging'])) {
            $result = $this->db->query($query, $params);
            if (is_object($result)) {
                return $result->result('array');
            } else {
                return false;
            }
        }
        
        $result       = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);
        
        //echo getDebugSQL($query, $params); exit(); 
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    /**
     * comments
     */
    function loadRegistryErrorFilter($data)
    {
        if ($data['Registry_id'] <= 0) {
            return false;
        }
        if (empty($data['nopaging'])) {
            if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
                return false;
            }
        }
        
        //Фильтр грида
        $json        = isset($data['Filter']) ? toUTF(trim($data['Filter'], '"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        
        
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'RegistryType_id' => $data['RegistryType_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value'])) . "%"
        );
        $filter = "(1=1)";
        
        $join   = "";
        $fields = "";
        if (!in_array($this->region, array(
            'ufa',
            'pskov'
        ))) {
            $join .= "left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id ";
            $fields .= "RD.needReform, RE.RegistryErrorType_Form, RE.MedStaffFact_id,"; // , RD.checkReform, RD.timeReform
            $fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
            $fields .= "RE.LpuUnit_id, RE.MedPersonal_id, ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted, ";
        } else {
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id
				left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id
				outer apply (
					select top 1
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1 with (nolock)
						left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = evpl.EvnVizitPL_id
						and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
					order by
						t1.EvnUslugaCommon_setDT desc
				) EU
				left join v_UslugaComplex U (nolock) on EU.UslugaComplex_uid =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RE.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RE.Registry_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
				) as MP
			";
            $fields .= "
				MP.Person_Fio as MedPersonal_Fio, 
				case when R.RegistryType_id in (1, 14) then
					m.Mes_Code
				else
					U.UslugaComplex_Code
				end as Usluga_Code, 
				R.RegistryType_id, 
				LB.LpuBuilding_Name, 
			";
        }
        
        if ($filter_mode['type'] == 'unicFilter') {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if ($filter_mode['cell'] == 'Person_FIO') {
                $field   = 'RE.Person_FIO';
                $orderBy = 'RE.Person_FIO';
            } elseif ($filter_mode['cell'] == 'Usluga_Code') {
                if ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) {
                    $field   = 'U.UslugaComplex_Code';
                    $orderBy = 'U.UslugaComplex_Code';
                } else {
                    $field   = 'm.Mes_Code';
                    $orderBy = 'm.Mes_Code';
                }
            } elseif ($filter_mode['cell'] == 'LpuSection_name') {
                $field   = 'RD.LpuSection_name';
                $orderBy = 'RD.LpuSection_name';
            } elseif ($filter_mode['cell'] == 'LpuBuilding_Name') {
                $field   = 'LB.LpuBuilding_Name';
                $orderBy = 'LB.LpuBuilding_Name';
            } elseif ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
                $field   = 'LSP.LpuSectionProfile_Name';
                $orderBy = 'LSP.LpuSectionProfile_Name';
            } elseif ($filter_mode['cell'] == 'MedSpecOms_Name') {
                $field   = 'MSO.MedSpecOms_Name';
                $orderBy = 'MSO.MedSpecOms_Name';
            } elseif ($filter_mode['cell'] == 'Evn_id') {
                $field   = 'RE.Evn_id';
                $orderBy = 'RE.Evn_id';
            } else {
                $field = $filter_mode['cell'];
            }
            
            $orderBy  = isset($orderBy) ? $orderBy : $filter_mode['cell'];
            $Like     = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
            $with     = "WITH";
            $distinct = 'DISTINCT';
        } else {
            return false;
        }
        
        
        
        $orderBy = isset($orderBy) ? $orderBy : null;
        
        $distinct = isset($distinct) ? $distinct : '';
        $with     = isset($with) ? $with : '';
        
        $view_db = $this->getRegistryDataObject($data);
        
        $join   = "";
        $fields = "";
        if (!in_array($this->region, array(
            'ufa',
            'pskov',
            'buryatiya'
        ))) {
            if (!empty($data['MedPersonal_id'])) {
                $filter .= " and RE.MedPersonal_id = :MedPersonal_id";
                $params['MedPersonal_id'] = $data['MedPersonal_id'];
            }
            
            $join .= "
				left join {$this->scheme}.RegistryQueue with (nolock) on RegistryQueue.Registry_id = RD.Registry_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = RE.MedPersonal_id
				) MP
			";
            $fields .= "RD.needReform, RE.RegistryErrorType_Form, RE.MedStaffFact_id,"; // , RD.checkReform, RD.timeReform
            $fields .= "case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end isNoEdit, ";
            $fields .= "RE.LpuUnit_id, RE.MedPersonal_id, MP.Person_Fio as MedPersonal_Fio, ";
        } else {
            if (!empty($data['MedPersonal_id'])) {
                $filter .= " and RD.MedPersonal_id = :MedPersonal_id";
                $params['MedPersonal_id'] = $data['MedPersonal_id'];
            }
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
				left join {$this->scheme}.v_Registry R (nolock) on R.Registry_id = RE.Registry_id
				left join v_EvnSection es (nolock) on ES.EvnSection_id = RE.Evn_id and R.RegistryType_id in (1, 14)
				left join v_EvnVizitPL evpl (nolock) on evpl.EvnVizitPL_id = RE.Evn_id and R.RegistryType_id in (2, 17)
				left join v_EvnVizitDispDop evdd (nolock) on evdd.EvnVizitDispDop_id = RE.Evn_id and R.RegistryType_id in (4, 7)
				left join v_EvnUslugaDispDop eudd (nolock) on eudd.EvnUslugaDispDop_pid = evdd.EvnVizitDispDop_id
				left join v_EvnVizitDispOrp evdo (nolock) on evdo.EvnVizitDispOrp_id = RE.Evn_id and R.RegistryType_id in (5, 9)
				left join v_EvnUslugaDispOrp eudo (nolock) on eudo.EvnUslugaDispOrp_pid = evdo.EvnVizitDispOrp_id
				outer apply (
					select top 1
						t1.EvnUslugaCommon_id,
						t1.UslugaComplex_id as UslugaComplex_uid
					from
						v_EvnUslugaCommon t1 with (nolock)
						left join v_UslugaComplex t2 with (nolock) on t2.UslugaComplex_id = t1.UslugaComplex_id
						left join v_UslugaCategory t3 with (nolock) on t3.UslugaCategory_id = t2.UslugaCategory_id
					where
						t1.EvnUslugaCommon_pid = evpl.EvnVizitPL_id
						and t3.UslugaCategory_SysNick in ('tfoms', 'lpusection')
					order by
						t1.EvnUslugaCommon_setDT desc
				) EU
				left join v_UslugaComplex U (nolock) on COALESCE(eudd.UslugaComplex_id, eudo.UslugaComplex_id, EU.UslugaComplex_uid) =  U.UslugaComplex_id -- связь стала с UslugaComplex_id (refs #13509)
				left join v_MesOld m (nolock) on m.Mes_id = ES.Mes_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = RE.LpuSection_id
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				outer apply(
					select top 1 Person_Fio from v_MedPersonal with (nolock) where MedPersonal_id = COALESCE(ES.MedPersonal_id, evpl.MedPersonal_id, eudd.MedPersonal_id, evdo.MedPersonal_id)
				) as MP
			";
            $fields .= "
				ISNULL(MP.Person_Fio, RD.MedPersonal_Fio) as MedPersonal_Fio,
				m.Mes_Code,
				U.UslugaComplex_Code as Usluga_Code, 
				R.RegistryType_id, 
				LB.LpuBuilding_Name, 
			";
        }
        
        if (in_array($data['RegistryType_id'], array(
            7,
            12
        ))) {
            $join .= "left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
            $fields .= "epd.DispClass_id, ";
        }
        
        $query = "
			Select
				-- select
				RTrim(cast(RE.Registry_id as char))+RTrim(cast(IsNull(RE.Evn_id,0) as char))+RTrim(cast(RE.RegistryErrorType_id as char)) as RegistryError_id,
				RE.Registry_id,
				RE.Evn_id,
				RE.Evn_rid,
				RE.EvnClass_id,
				RE.RegistryErrorType_id,
				RE.RegistryErrorType_Code,
				{$fields}
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name,
				RTrim(RE.RegistryErrorType_Name) as RegistryErrorType_Name,
				RE.RegistryErrorType_Descr,
				RE.Person_id,
				RE.Server_id,
				RE.PersonEvn_id,
				RTrim(RE.Person_FIO) as Person_FIO,
				RTrim(IsNull(convert(varchar,cast(RE.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				CASE WHEN RE.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as Person_IsBDZ,
				RE.LpuSection_id,
				RTrim(RE.LpuSection_name) as LpuSection_name,
				RTrim(IsNull(convert(varchar,cast(RE.Evn_setDate as datetime),104),'')) as Evn_setDate,
				RTrim(IsNull(convert(varchar,cast(RE.Evn_disDate as datetime),104),'')) as Evn_disDate,
				RE.RegistryErrorClass_id,
				RTrim(RE.RegistryErrorClass_Name) as RegistryErrorClass_Name,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryError RE with (NOLOCK)
				left join {$view_db} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
				{$join}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				and
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";
        
        if (!empty($data['nopaging'])) {
            $result = $this->db->query($query, $params);
            if (is_object($result)) {
                return $result->result('array');
            } else {
                return false;
            }
        }
        
        $result       = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], 1000, $Like, $orderBy), $params);
        $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);
        
        //echo getDebugSQL($query, $params);
        //exit();
        
        //$t = $result->result('array');
        //echo '<pre>' . print_r($t, 1) . '</pre>';
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            return $response;
        } else {
            return false;
        }
    }
    
    /**
     * Подучение списка ошибок ТФОМС
     */
    function loadRegistryErrorTFOMS($data)
    {
        $filterAddQueryTemp = null;
        
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);
            
            if (is_array($filterData)) {
                
                foreach ($filterData as $column => $value) {
                    
                    if (is_array($value)) {
                        $r = null;
                        
                        foreach ($value as $d) {
                            $r .= "'" . trim(toAnsi($d)) . "',";
                        }
                        
                        if ($column == 'Evn_id')
                            $column = 'rd.' . $column;
                        elseif ($column == 'LpuSection_Name')
                            $column = 'rd.' . $column;
                        elseif ($column == 'LpuBuilding_Name')
                            $column = 'lb.' . $column;
                        elseif ($column == 'LpuBuilding_Name')
                            $column = 'lb.' . $column;
                        elseif ($column == 'LpuSectionProfile_Name')
                            $column = 'lsp.' . $column;
                        elseif ($column == 'MedSpecOms_Name')
                            $column = 'mso.' . $column;
                        elseif ($column == 'Person_id')
                            $column = 'rd.' . $column;
                        elseif ($column == 'RegistryErrorType_Code')
                            $column = 'ret.' . $column;
                        elseif ($column == 'RegistryErrorType_Name')
                            $column = 'ret.' . $column;
                        elseif ($column == 'Person_FIO')
                            $column = 'rd.' . $column;
                        elseif ($column == 'MedPersonal_Fio')
                            $column = 'rd.' . $column;
                        elseif ($column == 'Polis_Num')
                            $column = 'rd.' . $column;

                        $r                    = rtrim($r, ',');
                        $filterAddQueryTemp[] = $column . ' IN (' . $r . ')';
                        
                    }
                }
            }
            
            if (is_array($filterAddQueryTemp)) {
                $filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
            } else
                $filterAddQuery = "";
        }
        
        $filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
        
        if ($data['Registry_id'] == 0) {
            return false;
        }
        if (isset($data['RegistryType_id']) && $data['RegistryType_id'] == 0) {
            return false;
        }
        
        if ((isset($data['start']) && (isset($data['limit']))) && (!(($data['start'] >= 0) && ($data['limit'] >= 0)))) {
            return false;
        }
        
        $this->setRegistryParamsByType($data);
        
        $params = array(
            'Registry_id' => $data['Registry_id']
        );
        $filter = "";
        if (isset($data['Person_SurName'])) {
            $filter .= " and ps.Person_SurName like :Person_SurName ";
            $params['Person_SurName'] = $data['Person_SurName'] . "%";
        }
        if (isset($data['Person_FirName'])) {
            $filter .= " and ps.Person_FirName like :Person_FirName ";
            $params['Person_FirName'] = $data['Person_FirName'] . "%";
        }
        if (isset($data['Person_SecName'])) {
            $filter .= " and ps.Person_SecName like :Person_SecName ";
            $params['Person_SecName'] = $data['Person_SecName'] . "%";
        }
        if (isset($data['RegistryErrorType_Code'])) {
            $filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
            $params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
        }
        if (isset($data['Person_FIO'])) {
            $filter .= " and rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO ";
            $params['Person_FIO'] = $data['Person_FIO'] . "%";
        }
        if (isset($data['Polis_Num'])) {
            $filter .= " and RD.Polis_Num = :Polis_Num ";
            $params['Polis_Num'] = $data['Polis_Num'];
        }

        $addToSelect = "";
        $leftjoin    = "";
        
        if (in_array($data['RegistryType_id'], array(
            7,
            12
        ))) {
            $leftjoin .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
            $addToSelect .= ", epd.DispClass_id";
        }
        
        $evn_object = 'Evn';
        if ($data['RegistryType_id'] == 6) {
            $evn_object = 'CmpCallCard';
        }
        
        if ($data['RegistryType_id'] == 6) {
            $evn_object = 'CmpCallCard';
            $evn_fields = "
				null as Evn_rid,
				null as EvnClass_id,
			";
        } else {
            $evn_object = 'Evn';
            $evn_fields = "
				Evn.Evn_rid,
				Evn.EvnClass_id,
			";
        }
        
        $query = "
			Select
				-- select
				RegistryErrorTFOMS_id,
				RE.Registry_id,
				RE.Evn_id,
				{$evn_fields}
				RegistryErrorType_Name as RegistryError_FieldName,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				rd.MedPersonal_Fio,
				ls.LpuSection_Name,
				lu.LpuBuilding_Name,
				ret.RegistryErrorType_Code,
				ret.RegistryErrorType_Name,
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
				left join {$this->scheme}.v_{$this->RegistryDataObject} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_{$evn_object} Evn with (nolock) on Evn.{$evn_object}_id = RE.Evn_id
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
				left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_LpuSection ls with(nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit lu with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
				{$leftjoin}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				{$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by";
        
        $result       = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
        $result_count = $this->db->query(getCountSQLPH($query), $params);
        
        if (is_object($result_count)) {
            $cnt_arr = $result_count->result('array');
            $count   = $cnt_arr[0]['cnt'];
            unset($cnt_arr);
        } else {
            $count = 0;
        }
        if (is_object($result)) {
            $response               = array();
            $response['data']       = $result->result('array');
            $response['totalCount'] = $count;
            
            return $response;
        } else {
            return false;
        }
    }
    
    /**
     * comments
     */
    function loadRegistryErrorTFOMSFilter($data)
    {
        //Фильтр грида
        $json        = isset($data['Filter']) ? toUTF(trim($data['Filter'], '"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        
        
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'RegistryType_id' => $data['RegistryType_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value'])) . "%"
        );
        
        $join   = "";
        $fields = "";
        $filter = "";
        
        if (!empty($data['Person_FIO'])) {
            $filter               = " and rd.Person_FIO like '%'+:Person_FIO+'%'";
            $params['Person_FIO'] = $data['Person_FIO'];
        }
        if (!empty($data['RegistryErrorType_Code'])) {
            $filter                           = " and ret.RegistryErrorType_Code = :RegistryErrorType_Code";
            $params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
        }
        
        if ($filter_mode['type'] == 'unicFilter') {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if ($filter_mode['cell'] == 'Person_FIO') {
                $field   = 'rd.Person_FIO';
                $orderBy = 'rd.Person_FIO';
            } elseif ($filter_mode['cell'] == 'MedPersonal_Fio') {
                $field   = 'rd.MedPersonal_Fio';
                $orderBy = 'rd.MedPersonal_Fio';
            } elseif ($filter_mode['cell'] == 'LpuSection_Name') {
                $field   = 'rd.LpuSection_Name';
                $orderBy = 'rd.LpuSection_Name';
            } elseif ($filter_mode['cell'] == 'LpuBuilding_Name') {
                $field   = 'lb.LpuBuilding_Name';
                $orderBy = 'lb.LpuBuilding_Name';
            } elseif ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
                $field   = 'lsp.LpuSectionProfile_Name';
                $orderBy = 'lsp.LpuSectionProfile_Name';
            } elseif ($filter_mode['cell'] == 'MedSpecOms_Name') {
                $field   = 'mso.MedSpecOms_Name';
                $orderBy = 'mso.MedSpecOms_Name';
            } elseif ($filter_mode['cell'] == 'Person_id') {
                $field   = 'rd.Person_id';
                $orderBy = 'rd.Person_id';
            } elseif ($filter_mode['cell'] == 'Evn_id') {
                $field   = 'rd.Evn_id';
                $orderBy = 'rd.Evn_id';
            } elseif ($filter_mode['cell'] == 'RegistryErrorType_Name') {
                $field   = 'ret.RegistryErrorType_Name';
                $orderBy = 'ret.RegistryErrorType_Name';
            } elseif ($filter_mode['cell'] == 'RegistryErrorType_Code') {
                $field   = 'ret.RegistryErrorType_Code';
                $orderBy = 'ret.RegistryErrorType_Code';
            }
            
            $field    = $filter_mode['cell'];
            $orderBy  = isset($orderBy) ? $orderBy : $filter_mode['cell'];
            $Like     = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
            $with     = "WITH";
            $distinct = 'DISTINCT';
        } else {
            return false;
        }
        
        $orderBy = isset($orderBy) ? $orderBy : null;
        
        $distinct = isset($distinct) ? $distinct : '';
        $with     = isset($with) ? $with : '';
        
        $addToSelect = "";
        $leftjoin    = "";
        
        if (in_array($data['RegistryType_id'], array(
            7,
            12
        ))) {
            $leftjoin .= " left join v_EvnPLDisp epd with (nolock) on epd.EvnPLDisp_id = RD.Evn_rid ";
            $addToSelect .= ", epd.DispClass_id";
        }
        
        if ($data['RegistryType_id'] == 6) {
            $evn_object = 'CmpCallCard';
            $evn_fields = "
				null as Evn_rid,
				null as EvnClass_id,
			";
        } else {
            $evn_object = 'Evn';
            $evn_fields = "
				Evn.Evn_rid,
				Evn.EvnClass_id,
			";
        }
        
        $view_db = $this->getRegistryDataObject($data);
        
        $query = "
			Select
				-- select
				RegistryErrorTFOMS_id,
				RE.Registry_id,
				RE.Evn_id,
				{$evn_fields}
				ret.RegistryErrorType_Code,
				RegistryErrorType_Name as RegistryError_FieldName,
				rtrim(isnull(ps.Person_SurName,'')) + ' ' + rtrim(isnull(ps.Person_FirName,'')) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as Person_FIO,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				RTrim(IsNull(convert(varchar,cast(ps.Person_BirthDay as datetime),104),'')) as Person_BirthDay,
				RegistryErrorTFOMS_FieldName,
				RegistryErrorTFOMS_BaseElement,
				RegistryErrorTFOMS_Comment,
				ISNULL(RD.RegistryData_deleted, 1) as RegistryData_deleted,
				case when RD.Evn_id IS NOT NULL then 1 else 2 end as RegistryData_notexist,
				rd.MedPersonal_Fio,
				ls.LpuSection_Name,
				lu.LpuBuilding_Name,
				ret.RegistryErrorType_Code,
				ret.RegistryErrorType_Name,
				ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name,
				ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name
				{$addToSelect}
				-- end select
			from
				-- from
				{$this->scheme}.v_RegistryErrorTFOMS RE with (nolock)
				left join {$view_db} RD with (nolock) on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.Evn_id
				left join v_{$evn_object} Evn with (nolock) on Evn.{$evn_object}_id = RE.Evn_id
				left join v_Person_bdz ps with (nolock) on ps.PersonEvn_id = rd.PersonEvn_id and ps.Server_id = rd.Server_id
				left join {$this->scheme}.RegistryErrorType ret with (nolock) on ret.RegistryErrorType_id = RE.RegistryErrorType_id
				left join v_LpuSection ls with(nolock) on LS.LpuSection_id = RD.LpuSection_id
				left join v_LpuUnit lu with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding lb with(nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = rd.LpuSectionProfile_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = rd.MedSpec_id
				{$leftjoin}
				-- end from
			where
				-- where
				RE.Registry_id=:Registry_id
				{$filter}
				-- end where
			order by
				-- order by
				RE.RegistryErrorType_Code
				-- end order by
		";
        
        if (!empty($data['withoutPaging'])) {
            $res = $this->db->query($query, $data);
            if (is_object($res)) {
                return $res->result('array');
            } else {
                return false;
            }
        } else {
            
            $result       = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
            $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);
            
            if (is_object($result_count)) {
                $cnt_arr = $result_count->result('array');
                $count   = $cnt_arr[0]['cnt'];
                unset($cnt_arr);
            } else {
                $count = 0;
            }
            if (is_object($result)) {
                $response               = array();
                $response['data']       = $result->result('array');
                $response['totalCount'] = $count;
                return $response;
            } else {
                return false;
            }
        }
    }
    
    /**
     * Получение данных Дубли посещений (RegistryDouble) для поликлин. реестров
     */
    function loadRegistryDouble($data)
    {
        $filterAddQueryTemp = null;
        
        if (isset($data['Filter'])) {
            $filterData = json_decode(toUTF(trim($data['Filter'], '"')), 1);
            
            if (is_array($filterData)) {
                
                foreach ($filterData as $column => $value) {
                    
                    if (is_array($value)) {
                        $r = null;
                        
                        foreach ($value as $d) {
                            $r .= "'" . trim(toAnsi($d)) . "',";
                        }
                        
                        if ($column == 'Person_FIO')
                            $column = 'RE.' . $column;
                        elseif ($column == 'LpuSection_name')
                            $column = 'RD.' . $column;
                        elseif ($column == 'LpuBuilding_Name')
                            $column = 'RD.' . $column;
                        elseif ($column == 'LpuSectionProfile_Name')
                            $column = 'LSP.' . $column;
                        elseif ($column == 'MedSpecOms_Name')
                            $column = 'MSO.' . $column;
                        elseif ($column == 'Usluga_Code')
                            $column = ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) ? 'U.UslugaComplex_Code' : 'm.Mes_Code';
                        elseif(strripos($column, '_id') !== false)
                            $column   = 'RD.' . $column;
                        
                        $r = rtrim($r, ',');
                        
                        $filterAddQueryTemp[] = $column . ' IN (' . $r . ')';
                    }
                }
                
            }
            
            if (is_array($filterAddQueryTemp)) {
                $filterAddQuery = "and " . implode(" and ", $filterAddQueryTemp);
            } else
                $filterAddQuery = "";
        }
        
        $filterAddQuery = isset($filterAddQuery) ? $filterAddQuery : null;
        
        $join   = "";
        $fields = "";
        $filter = "";
        
        if (!empty($data['MedPersonal_id'])) {
            $filter .= " and MP.MedPersonal_id = :MedPersonal_id";
            $params['MedPersonal_id'] = $data['MedPersonal_id'];
        }
        
        if (in_array($this->region, array(
            'ufa',
            'pskov'
        ))) {
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
            $fields = "
				, LB.LpuBuilding_Name
			";
        }
        
        $query = "
			Select
				-- select
				RD.Registry_id
				,RD.Evn_id
				,EPL.EvnPL_id as Evn_rid
				,RD.Person_id
				--,RD.Server_id
				--,RD.PersonEvn_id
				,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
				,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
				,EPL.EvnPL_NumCard
				,LS.LpuSection_FullName
				,MP.Person_Fio as MedPersonal_Fio
				,convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
				,ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name
				,ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name
				{$fields}
				-- end select
			from
				-- from
				{$this->scheme}.RegistryDouble RD with (NOLOCK)
				left join v_EvnVizitPL EVPL with (nolock) on EVPL.EvnVizitPL_id = RD.Evn_id
					--and EVPL.Person_id = RD.Person_id
				left join v_EvnPL EPL with (nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = EVPL.LpuSectionProfile_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = EVPL.MedStaffFact_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
				outer apply(
					select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = EVPL.MedPersonal_id
				) as MP
				{$join}
				-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				{$filter}
				{$filterAddQuery}
				-- end where
			order by
				-- order by
				RD.Person_SurName, RD.Person_FirName, RD.Person_SecName
				-- end order by
		";
        
        if (!empty($data['withoutPaging'])) {
            $res = $this->db->query($query, $data);
            if (is_object($res)) {
                return $res->result('array');
            } else {
                return false;
            }
        } else {
            $result       = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
            $result_count = $this->db->query(getCountSQLPH($query), $data);
            
            if (is_object($result_count)) {
                $cnt_arr = $result_count->result('array');
                $count   = $cnt_arr[0]['cnt'];
                unset($cnt_arr);
            } else {
                $count = 0;
            }
            if (is_object($result)) {
                $response               = array();
                $response['data']       = $result->result('array');
                $response['totalCount'] = $count;
                return $response;
            } else {
                return false;
            }
        }
    }
    /**
     * comments
     */
    function loadRegistryDoubleFilter($data)
    {
        //Фильтр грида
        $json = isset($data['Filter']) ? toUTF(trim($data['Filter'], '"')) : false;
        //echo $json.'<br/>';
        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;
        
        
        $params = array(
            'Registry_id' => $data['Registry_id'],
            'RegistryType_id' => $data['RegistryType_id'],
            'Value' => ($filter_mode['value'] == "_") ? "%%" : trim(toAnsi($filter_mode['value'])) . "%"
        );
        
        $join   = "";
        $fields = "";
        $filter = "";
        
        if (!empty($data['MedPersonal_id'])) {
            $filter .= " and MP.MedPersonal_id = :MedPersonal_id";
            $params['MedPersonal_id'] = $data['MedPersonal_id'];
        }
        
        if (in_array($this->region, array(
            'ufa',
            'pskov'
        ))) {
            if (!empty($data['LpuBuilding_id'])) {
                $filter .= " and LB.LpuBuilding_id = :LpuBuilding_id";
                $params['LpuBuilding_id'] = $data['LpuBuilding_id'];
            }
            
            $join .= "
				left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
			";
            $fields = "
				, LB.LpuBuilding_Name
			";
        }
        
        if ($filter_mode['type'] == 'unicFilter') {
            $prefix = '';
            //Подгоняем поля под запрос с WITH
            if ($filter_mode['cell'] == 'Person_FIO') {
                $field   = 'Person_FIO';
                $orderBy = 'Person_FIO';
            } elseif ($filter_mode['cell'] == 'MedPersonal_Fio') {
                $field   = 'MP.Person_Fio';
                $orderBy = 'MP.Person_Fio';
            } elseif ($filter_mode['cell'] == 'Usluga_Code') {
                if ($data['RegistryType_id'] != 1 && $data['RegistryType_id'] != 14) {
                    $field   = 'U.UslugaComplex_Code';
                    $orderBy = 'U.UslugaComplex_Code';
                } else {
                    $field   = 'm.Mes_Code';
                    $orderBy = 'm.Mes_Code';
                }
            } elseif ($filter_mode['cell'] == 'LpuSection_name') {
                $field   = 'RD.LpuSection_name';
                $orderBy = 'RD.LpuSection_name';
            } elseif ($filter_mode['cell'] == 'LpuBuilding_Name') {
                $field   = 'LB.LpuBuilding_Name';
                $orderBy = 'LB.LpuBuilding_Name';
            } elseif ($filter_mode['cell'] == 'LpuSectionProfile_Name') {
                $field   = 'LSP.LpuSectionProfile_Name';
                $orderBy = 'LSP.LpuSectionProfile_Name';
            } elseif ($filter_mode['cell'] == 'MedSpecOms_Name') {
                $field   = 'MSO.MedSpecOms_Name';
                $orderBy = 'MSO.MedSpecOms_Name';
            } elseif ($filter_mode['cell'] == 'Person_id') {
                $field   = 'RD.Person_id';
                $orderBy = 'RD.Person_id';
            } elseif ($filter_mode['cell'] == 'Evn_id') {
                $field   = 'RD.Evn_id';
                $orderBy = 'RD.Evn_id';
            }
            
            $field    = $filter_mode['cell'];
            $orderBy  = isset($orderBy) ? $orderBy : $filter_mode['cell'];
            $Like     = ($filter_mode['specific'] === false) ? "" : " and " . $orderBy . " like  :Value";
            $with     = "WITH";
            $distinct = 'DISTINCT';
        } else {
            return false;
        }
        
        $orderBy = isset($orderBy) ? $orderBy : null;
        
        $distinct = isset($distinct) ? $distinct : '';
        $with     = isset($with) ? $with : '';
        
        $query = "
			Select
				-- select
				RD.Registry_id
				,RD.Evn_id
				,EPL.EvnPL_id as Evn_rid
				,RD.Person_id
				--,RD.Server_id
				--,RD.PersonEvn_id
				,rtrim(IsNull(RD.Person_SurName,'')) + ' ' + rtrim(IsNull(RD.Person_FirName,'')) + ' ' + rtrim(isnull(RD.Person_SecName, '')) as Person_FIO
				,RTrim(IsNull(convert(varchar,cast(RD.Person_BirthDay as datetime),104),'')) as Person_BirthDay
				,EPL.EvnPL_NumCard
				,LS.LpuSection_FullName
				,MP.Person_Fio as MedPersonal_Fio
				,convert(varchar(10), EVPL.EvnVizitPL_setDT, 104) as EvnVizitPL_setDate
				,ISNULL(LSP.LpuSectionProfile_Code + '. ', '') + LSP.LpuSectionProfile_Name as LpuSectionProfile_Name
				,ISNULL(MSO.MedSpecOms_Code + '. ', '') + MSO.MedSpecOms_Name as MedSpecOms_Name
				{$fields}
				-- end select
			from
				-- from
				{$this->scheme}.RegistryDouble RD with (NOLOCK)
				left join v_EvnVizitPL EVPL with(nolock) on EVPL.EvnVizitPL_id = RD.Evn_id
					--and EVPL.Person_id = RD.Person_id
				left join v_EvnPL EPL with(nolock) on EPL.EvnPL_id = EVPL.EvnVizitPL_pid
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = EVPL.LpuSection_id
				left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = EVPL.LpuSectionProfile_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = EVPL.MedStaffFact_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
				outer apply(
					select top 1 Person_Fio, MedPersonal_id from v_MedPersonal with(nolock) where MedPersonal_id = EVPL.MedPersonal_id
				) as MP
				{$join}
				-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				{$filter}
				-- end where
			order by
				-- order by
				{$field}
				-- end order by
		";
        
        if (!empty($data['withoutPaging'])) {
            $res = $this->db->query($query, $data);
            if (is_object($res)) {
                return $res->result('array');
            } else {
                return false;
            }
        } else {
            
            $result       = $this->db->query($this->_getLimitSQLPH($query, $distinct, $field, $data['start'], $data['limit'], $Like, $orderBy), $params);
            $result_count = $this->db->query($this->_getCountSQLPH($query, $field, $distinct, $orderBy), $params);
            
            if (is_object($result_count)) {
                $cnt_arr = $result_count->result('array');
                $count   = $cnt_arr[0]['cnt'];
                unset($cnt_arr);
            } else {
                $count = 0;
            }
            if (is_object($result)) {
                $response               = array();
                $response['data']       = $result->result('array');
                $response['totalCount'] = $count;
                return $response;
            } else {
                return false;
            }
        }
    }
}
