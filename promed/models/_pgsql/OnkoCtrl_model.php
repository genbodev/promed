<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Класс модели для работы по онкокнтролю
 *
 * @author    Nigmatullin Tagir
 * @version   12.09.2014
 */
class OnkoCtrl_model extends swPgModel {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Журнал анкет по онкоконтролю
     */
    
    public function GetOnkoCtrlProfileJurnal($data) {
		$filters = array();
		$filter = "(1=1)";

		$filter_join = '';
		$join = '';
		$queryParams = array();

		if (isset($data['Empty']) && $data['Empty']==1) {
			return array('data'=>array(),'totalCount'=>0);
		};

		if (isset($data['StatusOnkoProfile_id'])) {
			$filter .= " and p.StatusOnkoProfile_id = :StatusOnkoProfile_id";
			$queryParams['StatusOnkoProfile_id'] = $data['StatusOnkoProfile_id'];
		};

		if (isset($data['Lpu_id']))  {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			//$filter .= " and Lpu_id = :Lpu_id";
			if (isset($data['StatusOnkoProfile_id'])) {
				if ($data['StatusOnkoProfile_id'] == 1)  //  Не заполненные анкеты
					$filter .= " and p.Lpu_id = :Lpu_id";
				if ($data['StatusOnkoProfile_id'] == 2)  //  Заполненные анкеты
					$filter .= " and p.LpuProfile_id = :Lpu_id";
			} else    //  Все анкеты
				$filter .= " and (LpuProfile_id = :Lpu_id or (p.Lpu_id = :Lpu_id and StatusOnkoProfile_id = 1))";
		}

		if (isset($data['SurName'])) {
			$filter .= " and p.SurName iLIKE :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		};

		if (isset($data['FirName'])) {
			$filter .= " and p.FirName iLIKE :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		};

		if (isset($data['SecName'])) {
			$filter .= " and p.SecName iLIKE :SecName";
			$queryParams['SecName'] = $data['SecName']."%";
		};

		if (isset($data['BirthDayRange'][0])) {
			$filter .= " and p.BirthDay  <= CAST(:BirthDayRangeBegin as date)";
			$queryParams['BirthDayRangeBegin'] = $data['BirthDayRange'][0];
		}

		if (isset($data['BirthDayRange'][1])) {
			$filter .= " and p.BirthDay  <= CAST(:BirthDayRangeEnd as date)";
			$queryParams['BirthDayRangeEnd'] = $data['BirthDayRange'][1];
		}

		if (isset($data['BirthDay'])) {
			$filter .= " and p.BirthDay  = CAST(:BirthDay as date)";
			$queryParams['BirthDay'] = $data['BirthDay'];
		}

		if (isset($data['PeriodRange'][0])) {
			$filter .= " and p.PersonOnkoProfile_DtBeg  >= CAST(:PeriodRangeBegin as date)";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}

		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.PersonOnkoProfile_DtBeg  <= CAST(:PeriodRangeEnd as date)";
			$queryParams['PeriodRangeEnd'] = $data['PeriodRange'][1];
		}


		if (isset($data['Doctor'])) {
			$filter .= " and p.MedStaffFact_id = :Doctor";
			$queryParams['Doctor'] = $data['Doctor'];
		};

		if (isset($data['Monitored'])) {
			$filter .= " and p.Monitored = :Monitored";
			$queryParams['Monitored'] = $data['Monitored'];
		};

		if (isset($data['Sex_id'])) {
			$filter .= " and p.Sex_id = :Sex_id";
			$queryParams['Sex_id'] = $data['Sex_id'];
		};


		if (isset($data['Uch'])) {
			if ($data['Uch'] == '0') {
				$filter .= " and (Uch = '' or p.lpu_id is null or p.lpu_id <> LpuProfile_id)";
			} else {
				$filter .= " and Uch = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		};


		if (isset($data['OnkoQuestions_id'])) {
			$filter_join .= "join onko.PersonOnkoQuestions t0 on t0.PersonOnkoProfile_id = p.PersonOnkoProfile_id
				and t0.OnkoQuestions_id = :OnkoQuestions_id";
			$queryParams['OnkoQuestions_id'] = $data['OnkoQuestions_id'];
		};


        $json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

		$where = '';
		// var_dump ($data);
		//$filter0 = '';
		//log_message('debug', 'GetOnkoCtrlProfileJurnal:  $filter0 =' .$data['Filter']);

		if (isset($data['Filter'])) {

			//log_message('debug', '$json=' . $json);
			//log_message('debug', '$filter_mode=' . $filter_mode);
			//log_message('debug', '$data[Filter]=' . $data['Filter']);



			foreach ($filter_mode as $col => $val) {
				if ($col == 'type')
					break;

				$fldName = $this->getNameColumn($col) .'_F';
				foreach ($val as $k=>$v) {
					//$tempIn[] = "'" . $v . "'";
					$tempIn[] = ':' .$fldName .$k;
					$queryParams[$fldName .$k] = $v;
				}

				$temp = implode(',', $tempIn);

				if ($col == 'ProfileResult')
					$joinMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				else
					$whereMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				// $whereMore[] = ' ['.$this->getNameColumn($col).'] in('.$temp.')';
			}

			//$where = (isset($whereMore)) ? ' and ' .implode(' and ', $whereMore) : $where;
			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
										join onko.PersonOnkoQuestions t  on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
										join onko.S_OnkoQuestions Q  on q.OnkoQuestions_id = t.OnkoQuestions_id and " . implode(' and ', $joinMore);
			}
		}

		if (isset($where)) {
			$filter .= $where;
		}


        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $filter =' . $filter);
        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $join =' . $join);

		$sql = "
			SELECT
				-- select
					pop.pmUser_insID as \"pmUser_insID\"
					,pu.PMUser_Name as \"PMUser_Name\"
					,p.PersonOnkoProfile_id as \"id\"
					,p.Person_id as \"Person_id\"
					,p.PersonOnkoProfile_id as \"PersonOnkoProfile_id\"
					,p.SurName as \"SurName\"
					,FirName as \"FirName\"
					,SecName as \"SecName\"
					,fio as \"fio\"
					,to_char(p.BirthDay, 'DD.MM.YYYY') as \"BirthDay\"
					,DATE_SMERT as \"DATE_SMERT\"
					,Sex_id as \"Sex_id\"
					,sex as \"sex\"
					,Address as \"Address\"
					,uch as \"uch\"
					,LpuRegion_id as \"LpuRegion_id\"
					,SocStatus_id as \"SocStatus_id\"
					,SocStatus_Name as \"SocStatus_Name\"
					,p.Lpu_id as \"Lpu_id\"
					,p.Lpu_Nick as \"Lpu_Nick\"
					,LpuProfile_id as \"LpuProfile_id\"
					,StatusOnkoProfile_id as \"StatusOnkoProfile_id\"
					,to_char(p.PersonOnkoProfile_DtBeg, 'DD.MM.YYYY') as \"PersonOnkoProfile_DtBeg\"
					,StatusOnkoProfile as \"StatusOnkoProfile\"
					,p.monitored as \"monitored\"
					,monitored_Name as \"monitored_Name\"
					,p.ProfileResult as \"ProfileResult\"
					--,MedPersonal_id
					,p.MedStaffFact_id as \"MedStaffFact_id\"
					,p.MedPersonal_fin as \"MedPersonal_fin\"
					,Person_dead as \"Person_dead\"
				-- end select
			  FROM
			  -- from
				onko.v_ProfileJurnal p
				left join onko.v_PersonOnkoProfile pop on pop.PersonOnkoProfile_id = p.PersonOnkoProfile_id
				left join v_pmUserCache pu on pu.PMUser_id = pop.pmUser_insID
				   
				{$filter_join}
				{$join}
			  -- end from
				WHERE
				-- where
				 " . $filter . "
				-- end where
				order by
				-- order by
					\"SurName\", \"FirName\", \"SecName\", \"BirthDay\", \"PersonOnkoProfile_DtBeg\"
				-- end order by
		";

		//echo getDebugSQL($sql, $queryParams);exit;

		$count_sql = getCountSQLPH($sql);
		//log_message('debug', 'GetOnkoCtrlProfileJurnal:  $count_sql =' . $count_sql);

		if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
			$sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
			//log_message('debug', 'GetOnkoCtrlProfileJurnal:  $sql (getLimitSQLPH) =' . $sql);
		}

		$res = $this->db->query($sql, $queryParams);
		//echo "<pre>" . print_r($this->db, 1) . "</pre>";

		// определение общего количества записей
		$count_res = $this->db->query($count_sql, $queryParams);
		if (is_object($count_res)) {
			$cnt_arr = $count_res->result('array');
			$count = $cnt_arr[0]['cnt'];
		}
		else
			return false;

		if (is_object($res)) {
			$response = array();
			$response['data'] = $res->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}

    }
   
    
	/**
	* Класс модели для работы по онкокнтролю
	*
	* @author    Nigmatullin Tagir
	* @version   12.09.2014
	* Старая версия
	*/
    public function GetOnkoCtrlProfileJurnal_old($data) {


        $filters = array();
        $filter = "(1=1)";
        $Lpu_id = "";
        $Surname = "NULL";
        $Firname = "NULL";
        $SecName = "NULL";
        $BirthDay = "NULL";
        $BirthDayBeg = "NULL";
        $BirthDayEnd = "NULL";
        $Sex_id = "NULL";
        $Empty = "0";
        $function = 'onko.fn_GetOnkoCtrlProfileJurnal';
        //$function = 'vactmp.fn_GetOnkoCtrlProfileJurnal_old';
        // $filter = " lpu_id = 35";
        $filter_join = '';
        $join = '';
        $queryParams = array();

        //log_message('debug', 'BirthDay=' . $data['BirthDay']);

        if (isset($data['Lpu_id'])) 
            $Lpu_id = $data['Lpu_id'];

        if (isset($data['Empty'])) {
            if ($data['Empty'] == 1) {
                //$filter = "(1>1)";
                $Empty = "1";
            }
        }; 
		if ($data['OnkoType_id'] == 2) {
			$function = 'onko.fn_GetOnkoCtrlProfileJurnalFull';
		}

		if (isset($data['SurName'])) {
			//$filter .= " and SurName iLIKE '" . $data['SurName'] . "%'";
			$Surname = "'" . $data['SurName'] . "%'";
		};

		if (isset($data['FirName'])) {
			//$filter .= " and FirName iLIKE '" . $data['FirName'] . "%'";
			$Firname = "'" . $data['FirName'] . "%'";
		};

		if (isset($data['SecName'])) {
			//$filter .= " and SecName iLIKE '" . $data['SecName'] . "%'";
			$SecName = "'" . $data['SecName'] . "%'";
		};

		if (isset($data['BirthDayRange'][1])) {
			//$filter .= " and BirthDay  <= '" . $data['BirthDayRange'][1] . "'";
			$BirthDayEnd = "CAST('" . $data['BirthDayRange'][1] . "' as date)";
		}

		if (isset($data['BirthDayRange'][0])) {
			//$filter .= " and BirthDay  >= '" . $data['BirthDayRange'][0] . "'";
			$BirthDayBeg = "CAST('" . $data['BirthDayRange'][0] . "' as date)";
		} // PeriodRange             } 

		if (isset($data['BirthDay'])) {
			//$filter .= " and BirthDay  = '" . $data['BirthDay'] ."'";
			$BirthDay = "CAST('" . $data['BirthDay'] . "' as date)";
		}

		if (isset($data['PeriodRange'][1])) {
			$filter .= " and PersonOnkoProfile_DtBeg  <= CAST('" . $data['PeriodRange'][1] . "' as date)";
		}

		if (isset($data['PeriodRange'][0])) {
			$filter .= " and PersonOnkoProfile_DtBeg  >= CAST('" . $data['PeriodRange'][0] . "' as date)";
		}


		if (isset($data['Doctor'])) {
			$filter .= " and MedStaffFact_id = " . $data['Doctor'];
		};

		if (isset($data['StatusOnkoProfile_id'])) {
			$filter .= " and StatusOnkoProfile_id = " . $data['StatusOnkoProfile_id'];
		};

		if (isset($data['Monitored'])) {
			$filter .= " and Monitored = " . $data['Monitored'];
		};

		if (isset($data['Sex_id'])) {

			if ($data['Sex_id'] == '3') {
				$filter .= " and Sex = ''";
			}
			else
				$filter .= " and Sex_id = " . $data['Sex_id'];

			//$Sex_id = $data['Sex_id'];
		};

		if (isset($data['Uch'])) {
			if ($data['Uch'] == '0') {
				$filter .= " and (Uch = '' or lpu_id is null or lpu_id <>  LpuProfile_id)";
			} else {
				$filter .= " and Uch = '" . $data['Uch'] . "'";
			}
		};


		if (isset($data['OnkoQuestions_id'])) {
			$filter_join .= "join onko.PersonOnkoQuestions t0 on t0.PersonOnkoProfile_id = p.PersonOnkoProfile_id 
								and t0.OnkoQuestions_id = " . $data['OnkoQuestions_id'];
		};



		$json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

		$where = '';
		// var_dump ($data);
		//$filter0 = '';
		//log_message('debug', 'GetOnkoCtrlProfileJurnal:  $filter0 =' .$data['Filter']);

		if (isset($data['Filter'])) {

			//log_message('debug', '$json=' . $json);
			//log_message('debug', '$filter_mode=' . $filter_mode);
			//log_message('debug', '$data[Filter]=' . $data['Filter']);



			foreach ($filter_mode as $col => $val) {
				if ($col == 'type')
					break;
				foreach ($val as $v) {
					$tempIn[] = "'" . $v . "'";
				}

				$temp = implode(',', $tempIn);

				if ($col == 'ProfileResult')
					$joinMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				else
					$whereMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
				// $whereMore[] = ' ['.$this->getNameColumn($col).'] in('.$temp.')';
			}

			//$where = (isset($whereMore)) ? ' and ' .implode(' and ', $whereMore) : $where; 
			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
										join onko.PersonOnkoQuestions t  on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
										join onko.S_OnkoQuestions Q  on q.OnkoQuestions_id = t.OnkoQuestions_id and " . implode(' and ', $joinMore);
			}
		}

		if (isset($where)) {
			$filter .= $where;
		}


        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $filter =' . $filter);


         $sql = "
      SELECT  
                -- select
                    p.PersonOnkoProfile_id as \"id\"
                    ,Person_id as \"Person_id\"
                    ,p.PersonOnkoProfile_id as \"PersonOnkoProfile_id\"
                    ,SurName as \"SurName\"
                    ,FirName as \"FirName\"
                    ,SecName as \"SecName\"
                    ,fio as \"fio\"
                    ,to_char(BirthDay, 'DD.MM.YYYY') as \"BirthDay\"
                    ,DATE_SMERT as \"DATE_SMERT\"
                    ,Sex_id as \"Sex_id\"
                    ,sex as \"sex\"
                    ,Address as \"Address\"
                    ,uch as \"uch\"
                    ,LpuRegion_id as \"LpuRegion_id\"
                    ,SocStatus_id as \"SocStatus_id\"
                    ,SocStatus_Name as \"SocStatus_Name\"
                    ,Lpu_id as \"Lpu_id\"
                    ,Lpu_Nick as \"Lpu_Nick\"
                    ,LpuProfile_id as \"LpuProfile_id\"
                    ,StatusOnkoProfile_id as \"StatusOnkoProfile_id\"
                    , to_char(PersonOnkoProfile_DtBeg, 'DD.MM.YYYY') as \"PersonOnkoProfile_DtBeg\"
                    ,StatusOnkoProfile as \"StatusOnkoProfile\"
                    ,monitored as \"monitored\"
                    ,monitored_Name as \"monitored_Name\"
                    ,ProfileResult as \"ProfileResult\"
                    --,MedPersonal_id
                    ,MedStaffFact_id as \"MedStaffFact_id\"
                    ,MedPersonal_fin as \"MedPersonal_fin\"
                    ,Person_dead as \"Person_dead\"
                -- end select
              FROM 
              -- from
                  " . $function . " (
                        CAST({$Lpu_id} as bigint), 
                        CAST({$Surname} as varchar(50)), 
                        CAST({$Firname} as varchar(50)), 
                        CAST({$SecName} as varchar(50)), 
                        CAST({$BirthDay} as date), 
                        CAST({$BirthDayBeg} as date), 
                        CAST({$BirthDayEnd} as date), 
                        CAST({$Empty} as integer)
                        ) p   
                
                {$filter_join}
                {$join}
              -- end from  
                WHERE 
                -- where
                 " . $filter . "
                -- end where        
                order by 
                -- order by
                    \"SurName\", \"FirName\", \"SecName\", \"BirthDay\", \"PersonOnkoProfile_DtBeg\"
                -- end order by   
                    ";
             
        $queryParams['Lpu_id'] = $this->nvl($data['Lpu_id']);
        $queryParams['Empty'] = $this->nvl($data['Empty']);      


        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $sql0 =' . $sql);

        // $queryParams['lpu_id'] = $this->nvl($data['lpu_id']);

        $queryParams['Lpu_id'] = $this->nvl($data['Lpu_id']);


        $count_sql = getCountSQLPH($sql);
        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $count_sql =' . $count_sql);
        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $count_sql =' . $count_sql);
        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  limit =' . $data['limit']);

        if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
            $sql = getLimitSQLPH($sql, $data['start'], $data['limit']);
            $sql = "{$sql} ";
            //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $sql (getLimitSQLPH) =' . $sql);
            //log_message('debug', 'getLimitSQLPH=' . $sql);
        }


        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $sql =' . $sql);
        $res = $this->db->query($sql, $queryParams);
        //echo "<pre>" . print_r($this->db, 1) . "</pre>"; 

        // определение общего количества записей
        $count_res = $this->db->query($count_sql, $queryParams);
        if (is_object($count_res)) {
            $cnt_arr = $count_res->result('array');
            $count = $cnt_arr[0]['cnt'];
            //log_message('debug', 'countSQL=' . $count);
        }
        else
            return false;

        if (is_object($res)) {
            $response = $res->result('array');
            $response[] = array('__countOfAllRows' => $count);
            return $response;
        } else {
            return false;
        }

    }
   
    /**
     * Получение списка Вакцин из справочника вакцин
     * Используется: окно просмотра и редактирования справочника вакцин
     */
    public function getOnkoQuestions($data) {
		$queryParams = [
			'Person_id' => $data['Person_id']
		];
		$filters = '1=1';

		if (!empty($data['OnkoCtrl_Date'])) {
			$filters .= " and (q.OnkoQuestions_begDate is null or q.OnkoQuestions_begDate <= CAST(:OnkoCtrl_Date as date))";
			$filters .= " and (q.OnkoQuestions_endDate is null or q.OnkoQuestions_endDate >= CAST(:OnkoCtrl_Date as date))";
			$queryParams['OnkoCtrl_Date'] = $data['OnkoCtrl_Date'];
		}

		if ($this->regionNick == 'msk') {
			$filters .= " and coalesce(q.Sex_id, (select sex_id from mv)) = (select sex_id from mv)";
		}

		$sql = "
			with mv as (
				select
					Sex_id
				from v_PersonState
				where Person_id = :Person_id
				limit 1
			)
			select
				q.OnkoQuestions_id as \"OnkoQuestions_id\",
				q.OnkoQuestions_Name as \"OnkoQuestions_Name\",
				at.AnswerType_Code as \"AnswerType_Code\",
				ac.AnswerClass_SysNick as \"AnswerClass_SysNick\",
				cast(case 
					when q.AnswerType_id = 1 then COALESCE(CAST(p.PersonOnkoQuestions_IsTrue as text), '1')
					when q.AnswerType_id = 2 then CAST(p.PersonOnkoQuestions_Answer as text)
					else p.PersonOnkoQuestions_ValueIdent
				end as varchar) as \"val\"
			from
				onko.v_S_OnkoQuestions q 
				left join onko.v_PersonOnkoQuestions p  on p.OnkoQuestions_id = q.OnkoQuestions_id
					and p.PersonOnkoProfile_id = :PersonOnkoProfile_id
				left join v_AnswerType at  on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac  on ac.AnswerClass_id = q.AnswerClass_id
			where
				{$filters}
			order by
				q.OnkoQuestions_id
		";

        $queryParams['PersonOnkoProfile_id'] = $this->nvl($data['PersonOnkoProfile_id']);

        return $this->queryResult($sql, $queryParams);
    }//end getOnkoQuestions()

	/**
	 * Загрузка доп инфы для формы анкетирования онкоконтроля
	 */
	public function loadOnkoContrProfileFormInfo($data) {
		$queryParams = array();
		$filter = '';
		$join = '';
		//log_message('debug', 'loadOnkoContrProfileFormInfo 2');

		if (isset($data['PersonOnkoProfile_id'])) {
			$filter = ' and PersonOnkoProfile_id = ' . $data['PersonOnkoProfile_id'];
		} else if (isset($data['Person_id'])) {
			$join = 'join onko.v_ProfileJurnalAct act  on act.Person_id = p.Person_id
                and act.PersonOnkoProfile_id = p.PersonOnkoProfile_id';
		}

		$query = "
			With t as (
				select
					cast(:Person_id as bigint) as Person_id
			),
			PersZno as (
				Select
					Person_id,
					Diag_id,
					Diag_setDate,
					Diag_Code,
					Diag_Name
				from onko.fn_GetZNO4Person (:Person_id)
				order by
					Diag_setDate 
                limit 1
			),
			OnkoProfile as (    
				SELECT 
					p.PersonOnkoProfile_id
					,p.Person_id
					,to_char(p.PersonOnkoProfile_DtBeg, 'DD.MM.YYYY') PersonOnkoProfile_DtBeg
					,p.Lpu_id
					,l.Lpu_Nick
					,msf.MedStaffFact_id
					,msf.LpuSection_id
					,msf.LpuBuilding_id
					,p.Evn_id
					,to_char(evn.Evn_setDT, 'DD.MM.YYYY') Evn_setDT
				FROM 
					onko.PersonOnkoProfile p 
					left join v_MedStaffFact msf  on msf.MedStaffFact_id = p.MedStaffFact_id
					{$join}
					left join v_Lpu l  on l.Lpu_id = p.lpu_id
					left join v_Evn evn  on evn.Evn_id = p.Evn_id
				where 
					p.Person_id = :Person_id
					and p.PersonOnkoProfile_StatusID = 0 
					{$filter} 
			)
			SELECT
				p.PersonOnkoProfile_id as \"PersonOnkoProfile_id\"
				,t.Person_id as \"Person_id\"
				,p.PersonOnkoProfile_DtBeg as \"PersonOnkoProfile_DtBeg\"
				,p.Lpu_id as \"Lpu_id\"
				,p.Lpu_Nick as \"Lpu_Nick\"
				,p.MedStaffFact_id as \"MedStaffFact_id\"
				,p.LpuSection_id as \"LpuSection_id\"
				,p.LpuBuilding_id as \"LpuBuilding_id\"
				,z.Diag_setDate as \"Diag_setDate\"
				,z.Diag_Code as \"Diag_Code\"
				,z.Diag_Name as \"Diag_Name\"
				,z.Diag_id as \"Diag_id\"
				,p.Evn_id as \"Evn_id\"
				,p.Evn_setDT as \"Evn_setDT\"
			FROM t 
				left join PersZno z  on z.Person_id = t.Person_id 
				left join OnkoProfile p   on p.Person_id = t.Person_id
		";

		$queryParams['Person_id'] = $this->nvl($data['Person_id']);
		$queryParams['PersonOnkoProfile_id'] = $this->nvl($data['PersonOnkoProfile_id']);

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (анкетирование по онкоконтролю)'));
		}
	}

    /**
     * сохранение информации об анкетировании пациента
     */
    public function savePersonOnkoProfile($data) {
    	$this->beginTransaction();

		try {
			$PersonOnkoProfile_id = !empty($data['PersonOnkoProfile_id']) ? $data['PersonOnkoProfile_id'] : null;

			if (empty($PersonOnkoProfile_id)) {
				$query = "
					select out_PersonOnkoProfile_id  as \"PersonOnkoProfile_id\", Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from onko.p_PersonOnkoProfile_ins(
						PersonOnkoProfile_id   := CAST(null as bigint),
						Person_id              := CAST(:Person_id as bigint),
						Profile_Date           := CAST(:Profile_Date as date),
						MedStaffFact_id        := CAST(:MedStaffFact_id as bigint),
						Lpu_id                 := CAST(:Lpu_id as bigint),
						PmUser_id              := CAST(:pmUser_id as bigint)
                        )
				";
				$params = array(
					'Person_id' => $data['Person_id'],
					'Profile_Date' => $data['Profile_Date'],
					'MedStaffFact_id' => $data['MedStaffFact_id'],
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id'],
				);
				//echo getDebugSQL($query, $params);exit;
				$resp = $this->queryResult($query, $params);
				if (!is_array($resp)) {
					throw new Exception('Ошибка при сохранении анкеты');
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
				$PersonOnkoProfile_id = $resp[0]['PersonOnkoProfile_id'];
				
				$this->sendMessage($data);
			}

			foreach ($data['QuestionAnswer'] as $answer) {
				$OnkoQuestions_id = $answer[0];
				$Value = $answer[1];

				$resp = $this->getFirstRowFromQuery("
					select 
						q.AnswerType_id as \"AnswerType_id\",
						q.AnswerClass_id as \"AnswerClass_id\",
						p.PersonOnkoQuestions_id as \"PersonOnkoQuestions_id\"
					from onko.v_S_OnkoQuestions q 
					left join onko.v_PersonOnkoQuestions p  on p.OnkoQuestions_id = q.OnkoQuestions_id 
						and p.PersonOnkoProfile_id = :PersonOnkoProfile_id
					where q.OnkoQuestions_id = :OnkoQuestions_id
                    limit 1
				", array(
					'OnkoQuestions_id' => $OnkoQuestions_id,
					'PersonOnkoProfile_id' => $PersonOnkoProfile_id,
				));
				if (!is_array($resp)) {
					throw new Exception('Ошибка при получении данных вопроса');
				}

				$params = array(
					'PersonOnkoQuestions_id' => !empty($resp['PersonOnkoQuestions_id'])?$resp['PersonOnkoQuestions_id']:null,
					'PersonOnkoProfile_id' => $PersonOnkoProfile_id,
					'OnkoQuestions_id' => $OnkoQuestions_id,
					'PersonOnkoQuestions_ValueIdent' => null,
					'PersonOnkoQuestions_Answer' => null,
					'PersonOnkoQuestions_IsTrue' => null,
					'AnswerClass_id' => null,
				);

				switch($resp['AnswerType_id']) {
					case 1:
						$params['PersonOnkoQuestions_IsTrue'] = $Value?2:1;
						break;
					case 2:
						$params['PersonOnkoQuestions_Answer'] = !empty($Value)?$Value:null;
						break;
					case 3:
					case 4:
						$params['PersonOnkoQuestions_ValueIdent'] = !empty($Value)?$Value:null;
						$params['AnswerClass_id'] = $resp['AnswerClass_id'];
						break;
				}

				if (empty($params['PersonOnkoQuestions_id']) && empty($Value)) {
					continue;
				} else if (!empty($params['PersonOnkoQuestions_id']) && empty($Value)) {
					$modifyDataQuery = "
						delete onko.PersonOnkoQuestions
						where PersonOnkoQuestions_id = :PersonOnkoQuestions_id
					";
				} else if (empty($params['PersonOnkoQuestions_id'])) {
					$modifyDataQuery = "
						insert into onko.PersonOnkoQuestions (
							PersonOnkoProfile_id, 
							OnkoQuestions_id, 
							PersonOnkoQuestions_ValueIdent,
							PersonOnkoQuestions_Answer,
							PersonOnkoQuestions_IsTrue,
							AnswerClass_id
						)
						values (
							:PersonOnkoProfile_id,
							:OnkoQuestions_id,
							:PersonOnkoQuestions_ValueIdent,
							:PersonOnkoQuestions_Answer,
							:PersonOnkoQuestions_IsTrue,
							:AnswerClass_id
						)
					";
				} else {
					$modifyDataQuery = "
						update 
							onko.PersonOnkoQuestions 
						set
							PersonOnkoQuestions_ValueIdent = :PersonOnkoQuestions_ValueIdent,
							PersonOnkoQuestions_Answer = :PersonOnkoQuestions_Answer,
							PersonOnkoQuestions_IsTrue = :PersonOnkoQuestions_IsTrue,
							AnswerClass_id = :AnswerClass_id
						where
							PersonOnkoQuestions_id = :PersonOnkoQuestions_id
					";
				}

				$query = "
						{$modifyDataQuery}
					RETURNING '' as \"Error_Code\", '' as \"Error_Msg\";
				";
				//echo getDebugSQL($query, $params);exit;
				$resp = $this->queryResult($query, $params);
				if (!is_array($resp)) {
					throw new Exception('Ошибка при сохранении ответа на вопрос');
				}
				if (!$this->isSuccessful($resp)) {
					throw new Exception($resp[0]['Error_Msg']);
				}
			}
		} catch(Exception $e) {
			$this->rollbackTransaction();
			return $this->createError($e->getCode(), $e->getMessage());
		}

		$this->commitTransaction();

		return array(array(
			'PersonOnkoProfile_id' => $PersonOnkoProfile_id,
			'success' => true
		));
    }

    /**
     * ф-ция проверки значения
     */
    public function nvl(&$var) {
        if (isset($var)) {
            return $var;
        } else {
            return null;
        }
    }

    /**
     * Получение Отчета "Установлено ЗНО"
     *
     */
    public function getOnkoReportSetZNO($data) {
        //log_message('debug', 'model  getOnkoReportSetZNO');

        /*
         $Dt_Srart = '';
        $Dt_End = '';

        if (isset($data['PeriodRange'][0])) {
            $Dt_Srart = "'" . $data['PeriodRange'][0] . "'";
        };

        if (isset($data['PeriodRange'][1])) {
            $Dt_End = "'" . $data['PeriodRange'][1] . "'";
        };
        */

		   $query = "
            Select _type as \"_type\",
                   Lpu_id as \"Lpu_id\",
                   Lpu_Nick as \"Lpu_Nick\",
                   Lpu_Name as \"Lpu_Name\",
                   Kol_Zno as \"Kol_Zno\",
                   Kol as \"Kol\",
                   KolPassed as \"KolPassed\",
                   NeedOnko as \"NeedOnko\",
                   NotNeedOnko as \"NotNeedOnko\",
                   NotKolPassed as \"NotKolPassed\"
            from onko.fn_OnkoReportSetZNO(null, CAST(:Dt_Srart as date), CAST(:Dt_End as date))
            order by _type,
                     Kol desc    
            ";	


        $queryParams['Lpu_id'] = $this->nvl($data['Lpu_id']);
        $queryParams['Dt_Srart'] = $this->nvl($data['PeriodRange'][0]);
        $queryParams['Dt_End'] = $this->nvl($data['PeriodRange'][1]);
        //log_message('debug', '$dbrep' );
        $dbrep = $this->load->database('bdreports', true);
        //$dbrep = $this->load->database('bdWorkReport', true);
		$dbrep->query_timeout = 3600;
        //echo "<pre>" . print_r($dbrep, 1) . "</pre>"; 
        //   log_message('debug', '$dbrep=' . implode('-',$dbrep));
        //$result = $this->db->query($query, $queryParams);
        //log_message('debug', 'getOnkoReportSetZNO: $query = ' . $query);
        $result = $dbrep->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных '));
        }
    }

    /**
     * Получение Отчета "Установлено ЗНО (детализация)"
     */
    public function getOnkoReportSetZNO_Detail($data) {

        $queryParams = array();
        $Dt_Srart = '';
        $Dt_End = '';
        $Field = '';
        $Lpu_id = '';
        $Table = '';


        /*
        if (isset($data['PeriodRange'][0]))
            $Dt_Srart = $data['PeriodRange'][0];
        if (isset($data['PeriodRange'][1]))
            $Dt_End = $data['PeriodRange'][1];
        */
        if (isset($data['Lpu_id'])) {
            $Lpu_id = $data['Lpu_id'];
            
            if ($Lpu_id != -1)
                $Table = 'onko.fn_OnkoReportSetZNO_Detail';
            else
                $Table = 'onko.fn_OnkoReportSetZNO_Detail_Zerro';
            
        }
        $Table = 'onko.fn_OnkoReportSetZNO_Detail';
        if (isset($data['Field']))
            $Field = $data['Field'];

        $query = "
                    SELECT 
                        -- select
                        Person_id as \"Person_id\", 
                        SurName as \"SurName\", 
                        FirName as \"FirName\", 
                        SecName as \"SecName\", 
                        Fio as \"Fio\", 
                        to_char(BirthDay, 'DD.MM.YYYY') as \"BirthDay\", 
                        to_char(Ds_date, 'DD.MM.YYYY') as \"Ds_date\", 
                        Diag_Code as \"Diag_Code\",  
                        Diag_Name as \"Diag_Name\",
                        MedPersonal_fin as \"MedPersonal_fin\",
                        to_char(Profile_Date, 'DD.MM.YYYY') as \"Profile_Date\",  
                        ProfileResult as \"ProfileResult\"
                        -- end select
                        FROM 
                        --from
                        {$Table}
                        (:Lpu_id, CAST(:Dt_Srart as date), CAST(:Dt_End as date), :Field)
                        --  end from    
                        order by 
                        -- order by
                        SurName, FirName, SecName 
                        -- end order by 
                    ";
        
        $queryParams['Lpu_id'] = $this->nvl($data['Lpu_id']);
        $queryParams['Field'] = $this->nvl($data['Field']);
        $queryParams['Dt_Srart'] = $this->nvl($data['PeriodRange'][0]);
        $queryParams['Dt_End'] = $this->nvl($data['PeriodRange'][1]);


        //$count_sql = getCountSQLPH($query, $queryParams);
        $count_sql = "
            Select count(1) AS \"cnt\"
            FROM 
            --from
            {$Table}
            (:Lpu_id, CAST(:Dt_Srart as date), CAST(:Dt_End as date), :Field)
            --  end from          
";


        if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
            $query = getLimitSQLPH($query, $data['start'], $data['limit']);
            $query = "{$query} ";
            //log_message('debug', 'getOnkoReportSetZNO_Detail: $count_sql=' . $count_sql);
        }

        $dbrep = $this->load->database('bdreports', true);
        //$dbrep = $this->load->database('bdWorkReport', true);
		$dbrep->query_timeout = 600;

        //$res = $this->db->query($query, $queryParams);
        $res = $dbrep->query($query, $queryParams);

        // определение общего количества записей
        //$count_res = $this->db->query($count_sql, $queryParams);
        $count_res = $dbrep->query($count_sql, $queryParams);
        if (is_object($count_res)) {
            $cnt_arr = $count_res->result('array');
            $count = $cnt_arr[0]['cnt'];
            //log_message('debug', 'countSQL=' . $count);
        }
        else
            return false;

        if (is_object($res)) {
            $response = $res->result('array');
            $response[] = array('__countOfAllRows' => $count);
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Получение Отчета "Мониторинг реализации системы "Онкоконтроль"
     *
     */
    public function getOnkoReportMonitoring($data) {
        //log_message('debug', 'model  getOnkoReportMonitoring');

        $query = "
            with
			tmp as (
				Select
					0 _type,
					Lpu_id,
					Lpu_Nick,
					KolPassed,
					NeedOnko,
					NotNeedOnko,
					KolZnoAll,
					KolZnoUnderAnket,
					kolZnoDespiteAnket
				from
					onko.fn_OnkoReportMonitoring (:Lpu_id, :Dt_Srart, :Dt_End)
				--order by KolPassed desc
			),
			tmp_all as (
				Select  1 _type, null Lpu_id, 'Итого: ' Lpu_Nick,
				Sum(KolPassed) KolPassed, Sum(NeedOnko) NeedOnko, Sum(NotNeedOnko) NotNeedOnko, Sum(KolZnoAll) KolZnoAll,
				Sum(KolZnoUnderAnket) KolZnoUnderAnket, Sum(kolZnoDespiteAnket) kolZnoDespiteAnket
				from tmp
			)
			Select
				_type as \"_type\",
				Lpu_id as \"Lpu_id\",
				Lpu_Nick as \"Lpu_Nick\",
				KolPassed as \"KolPassed\",
				NeedOnko as \"NeedOnko\",
				NotNeedOnko as \"NotNeedOnko\",
				KolZnoAll as \"KolZnoAll\",
				KolZnoUnderAnket as \"KolZnoUnderAnket\",
				kolZnoDespiteAnket as \"kolZnoDespiteAnket\"
			from (
				Select  _type, Lpu_id::bigint, Lpu_Nick,
				KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket
				from tmp
				union
				Select  _type, Lpu_id, Lpu_Nick,
				KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket
				from tmp_all
			) t
			order by \"_type\", \"KolPassed\" desc
		";

		$query = "
			Select
				_type as \"_type\",
				Lpu_id as \"Lpu_id\",
				Lpu_Nick as \"Lpu_Nick\",
				KolPassed as \"KolPassed\",
				NeedOnko as \"NeedOnko\",
				NotNeedOnko as \"NotNeedOnko\",
				KolZnoAll as \"KolZnoAll\",
				KolZnoUnderAnket as \"KolZnoUnderAnket\",
				kolZnoDespiteAnket as \"kolZnoDespiteAnket\"
			from onko.fn_OnkoReportMonitoring (:Lpu_id, :Dt_Srart, :Dt_End)
			order by _type, KolPassed desc
		";


        $queryParams['Lpu_id'] = $this->nvl($data['Lpu_id']);
        $queryParams['Dt_Srart'] = $this->nvl($data['PeriodRange'][0]);
        $queryParams['Dt_End'] = $this->nvl($data['PeriodRange'][1]);
        //log_message('debug', '$dbrep' );
        $dbrep = $this->load->database('bdreports', true);
        //$dbrep = $this->load->database('bdWorkReport', true);
        //echo "<pre>" . print_r($dbrep, 1) . "</pre>"; 
        $dbrep->query_timeout = 3600;
        //log_message('debug', 'getOnkoReportMonitoring: $query = ' . $query);
        $result = $dbrep->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных '));
        }
    }
    
     /**
     * Получение Отчета "Мониторинг реализации системы "Онкоконтроль" (детализация)"
     */
    public function getOnkoReportMonitoring_Detail($data) {

        $queryParams = array();
        $Dt_Srart = '';
        $Dt_End = '';
        $Field = '';
        $Lpu_id = '';
        $Table = '';

        if (isset($data['Lpu_id'])) {
            $Lpu_id = $data['Lpu_id'];
        }
        $Table = 'onko.OnkoReportMonitoring_Detail';
        if (isset($data['Field']))
            $Field = $data['Field'];

        $query = "
                    SELECT 
                        -- select
                        Person_id as \"Person_id\", 
                        SurName as \"SurName\", 
                        FirName as \"FirName\", 
                        SecName as \"SecName\", 
                        --Fio, 
                        to_char(BirthDay, 'DD.MM.YYYY') as \"BirthDay\", 
                        to_char(Prof_DtBeg, 'DD.MM.YYYY') as \"Prof_DtBeg\", 
                        Prof_MedPersonal_fio as \"Prof_MedPersonal_fio\",
                        Prof_ProfileResult as \"Prof_ProfileResult\",
                        to_char(Diag_Date, 'DD.MM.YYYY') as \"Diag_Date\", 
                        Diag_Code as \"Diag_Code\",  
                        Diag_Name as \"Diag_Name\",
                        Diag_MedPersonal_fio as \"Diag_MedPersonal_fio\"
                        -- end select
                        FROM 
                        --from
                        {$Table}
                        (:Lpu_id, CAST(:Dt_Srart as date), CAST(:Dt_End as date), :Field)
                        --  end from    
                        order by 
                        -- order by
                        SurName, FirName, SecName 
                        -- end order by
                    ";
        
        $queryParams['Lpu_id'] = $this->nvl($data['Lpu_id']);
        $queryParams['Field'] = $this->nvl($data['Field']);
        $queryParams['Dt_Srart'] = $this->nvl($data['PeriodRange'][0]);
        $queryParams['Dt_End'] = $this->nvl($data['PeriodRange'][1]);


        //$count_sql = getCountSQLPH($query, $queryParams);
        $count_sql = "
            Select count(1) AS \"cnt\"
            FROM 
            --from
            {$Table}
            (:Lpu_id, CAST(:Dt_Srart as date), CAST(:Dt_End as date), :Field)
            --  end from          
            ";


        if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
            $query = getLimitSQLPH($query, $data['start'], $data['limit']);
            $query = "{$query} ";
            //log_message('debug', 'getOnkoReportSetZNO_Detail: $count_sql=' . $count_sql);
        }

        $dbrep = $this->load->database('bdreports', true);
        //$dbrep = $this->load->database('bdWorkReport', true);
		$dbrep->query_timeout = 600;

        $res = $dbrep->query($query, $queryParams);

        // определение общего количества записей    
        $count_res = $dbrep->query($count_sql, $queryParams);
        
        if (is_object($count_res)) {
                $cnt_arr = $count_res->result('array');
                $count = $cnt_arr[0]['cnt'];
        }
        else
                return false;

        if (is_object($res)) {
                $response = array();
                $response['data'] = $res->result('array');
                $response['totalCount'] = $count;
                return $response;
        } else {
                return false;
        }   
       
    }

    /**
     * Удаление анкеты
     */
    public function deleteOnkoProfile($data) {
	    try {
		    if (empty($data['PersonOnkoProfile_id'])) {
			    throw new Exception('Не указан идентификатор анкеты');
		    }
		    if (empty($data['session']) || empty($data['session']['pmuser_id'])) {
			    throw new Exception('Отсутствуют параметры пользователя');
		    }
		    $queryParams = array(
			    'PersonOnkoProfile_id' => $data['PersonOnkoProfile_id'],
			    'pmUser_id' => $data['session']['pmuser_id'],
		    );
		    // проверка возможности удаления
		    if (isSuperadmin()) {
			    $isAllowDelete = true;
		    } else {
			    // Удаление доступно для пользователя, создавшего запись
			    $query = "
					select ank.PersonOnkoProfile_id as \"PersonOnkoProfile_id\"
					FROM onko.PersonOnkoProfile ank 
					where ank.PersonOnkoProfile_id = :PersonOnkoProfile_id
						and ank.pmUser_insID = :pmUser_id
				";
				//log_message('debug', 'deleteOnkoProfile: $query=' . $query);
				$result = $this->db->query($query, $queryParams);
				if (is_object($result)) {
					$isAllowDelete = (count($result->result('array')) > 0);
				} else {
					throw new Exception('Не удалось выполнить проверку возможности удаления анкеты пользователем МО');
				}
		    }
			if (false == $isAllowDelete) {
				throw new Exception('Вам не разрешено удалять эту анкету');
			}
			$query = "
				select Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
				from onko.p_OnkoCtrlProfile_del(
					PersonOnkoProfile_id   := :PersonOnkoProfile_id,
					User_id                := :User_id)
			";

			$queryParams['PersonOnkoProfile_id'] = $this->nvl($data['PersonOnkoProfile_id']);
			$queryParams['User_id'] = $this->nvl($_SESSION['pmuser_id']);

			$result = $this->db->query($query, $queryParams);

			if (is_object($result)) {
				return $result->result('array');
			} else {
				throw new Exception('Ошибка при выполнении запроса к базе данных (Удаление анкеты)');
			}
	    } catch (Exception $e) {
		    return array(array('Error_Msg' => $e->getMessage(), 'success' => false));
	    }
    }

    /**
     * Получаем результаты анкетирования по онкоконтролю
     */
    public function GetOnkoCtrlProfileResult() {
        $queryParams = array();

        $query = "
        Select OnkoQuestions_id as \"OnkoQuestions_id\",
               OnkoQuestions_Nick as \"OnkoQuestions_Nick\"
        from (
               SELECT OnkoQuestions_id,
                      OnkoQuestions_Nick
               FROM onko.S_OnkoQuestions
               union
               Select -1 as OnkoQuestions_id,
                      'Все' as OnkoQuestions_Nick
             ) t
        order by OnkoQuestions_id
            ";
        $result = $this->db->query($query, $queryParams);


        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
        }
    }

    /**
     * конвертер dataIndex в name column DB
     * 
     */
    public function getNameColumn($dataIndex) {
        //log_message('debug', '$dataIndex=' . $dataIndex);
        switch ($dataIndex) {
            case 'SurName':
                $column = 'SurName';
                break;
            case 'FirName':
                $column = 'FirName';
                break;
            case 'SecName':
                $column = 'SecName';
                break;
            case 'StatusOnkoProfile':
                $column = 'StatusOnkoProfile_tmp';
                break;
            case 'monitored_Name':
                $column = 'monitored_Name';
                break;
            case 'uch':
                $column = 'uch';
                break;
            case 'ProfileResult':
                $column = 'OnkoQuestions_Nick';
                break;
            case 'MedPersonal_fin':
                $column = 'MedPersonal_fin';
                break;
            case 'Lpu_Nick':
                $column = 'Lpu_Nick';
                break;
            case 'sex':
                $column = 'sex';
                break;
	        default:
		        $column = null;
		        break;
        }
        return $column;
    }


	/**
	 * Сохраняет идентификатор посещения в поле Evn_id таблицы onko.PersonOnkoProfile
	 */
	function updateEvnId($data)
	{
		$queryParams = array(
			'pmUser_id' => $data['pmUser_id'],
			'Evn_id' => $data['Evn_id'],
			'PersonOnkoProfile_id' => $data['PersonOnkoProfile_id'],
		);
		$query = "
			UPDATE onko.PersonOnkoProfile
			SET pmUser_updID = :pmUser_id,
			PersonOnkoProfile_updDT = dbo.tzGetDate(),
			Evn_id = :Evn_id
			WHERE PersonOnkoProfile_id = :PersonOnkoProfile_id
		";
		$result = $this->db->query($query, $queryParams);
		return ($result === TRUE) ? array('success'=>true, 'Error_Msg'=>null) : $result;
	}

	/**
	 * Возвращает результат проверки необходимо ли заполнять анкету при сохранении посещения врачом
	 */
	function checkIsNeedOnkoControl($data)
	{
		$queryParams = array(
			'Person_id' => $data['Person_id'],
			'MedStaffFact_id' => $data['MedStaffFact_id'],
		);
		$query = "
			select dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\",
                   case
                     when ank.PersonOnkoProfile_id is null and ZNO is null then 1
                     else 0
                   end as \"IsNeedOnkoControl\",
                   spec.MedSpecOms_Code as \"MedSpecOms_Code\"
            FROM v_PersonState PS
                 inner join dbo.v_MedStaffFact MSF on MSF.MedStaffFact_id = :MedStaffFact_id
                 left join dbo.v_MedSpecOms Spec on Spec.MedSpecOms_id = MSF.MedSpecOms_id
                 LEFT JOIN LATERAL
                 (
                   select t.PersonOnkoProfile_id
                   FROM onko.v_PersonOnkoProfile t
                   where t.Person_id = PS.Person_id and
                         t.PersonOnkoProfile_DtBeg between CAST('1.1.' || to_char(dbo.tzGetDate(), 'YYYY') as date) and
                         CAST('31.12.' || to_char(dbo.tzGetDate(),'YYYY') as date)
                   limit 1
                 ) ank ON true
                 LEFT JOIN LATERAL
                 (
                   select 1 as ZNO
                   from onko.fn_GetZNO4Person(PS.Person_id)
                   limit 1
                 ) ZNO ON true
            where PS.Person_id = :Person_id
            limit 1
		";
		/*
				case when ank.PersonOnkoProfile_id is null
					OR (DATEDIFF(day,ank.PersonOnkoProfile_DtBeg,dbo.tzGetDate()) >= 365)
				then 1 else 0 end as IsNeedOnkoControl,
			LEFT JOIN LATERAL (
				select top 1
				t.PersonOnkoProfile_id,
				t.PersonOnkoProfile_DtBeg
				FROM onko.PersonOnkoProfile t 

				where t.Person_id = PS.Person_id
					and t.PersonOnkoProfile_id is not null
				order by t.PersonOnkoProfile_DtBeg desc
			) ank
		 */
		$result = $this->db->query($query, $queryParams);
		if ( is_object($result) ) {
			$tmp = $result->result('array');
			if (empty($tmp)) {
				return false;
			}
			$response = array('IsNeedOnkoControl'=>0);
			/**
			 * Если нет действующей анкеты
			 * и пациент совершеннолетний
			 * и специальность врача: терапевт, хирург, гинеколог, уролог, проктолог
			 * то необходимо заполнять анкету
			 */
			$isOnkoControlMedSpec = in_array($tmp[0]['MedSpecOms_Code'], array(
				1, // акушерство и гинекология
				47, // терапия
				53, // урология
				18, // колопроктология
				57, // хирургия нужно ли ещё 24,43,49 ?
			));
			if ($data['session']['region']['nick'] == 'ufa') {
				$isOnkoControlMedSpec = in_array($tmp[0]['MedSpecOms_Code'], array(
				// Исправлено по задаче #89233
					8, // Лечебное дело. Педиатрия/Акушерство и гинекология
					139, //Хирургия/Колопроктология
					16, //Лечебное дело. Педиатрия/Общая врачебная практика (семейная медицина)
					27, // Лечебное дело. Педиатрия/Терапия
					145, //Хирургия/Урология
					30 //Лечебное дело. Педиатрия/Хирургия
					/*
					8, // 11, // ВРАЧ-АКУШЕР-ГИНЕКОЛОГ
					//12, // ВРАЧ-АКУШЕР-ГИНЕКОЛОГ ЦЕХОВОГО ВРАЧЕБНОГО УЧАСТКА
					139, //29, // ВРАЧ-КОЛОПРОКТОЛОГ
					16, //38, // ВРАЧ ОБЩЕЙ ПРАКТИКИ (СЕМЕЙНЫЙ ВРАЧ)
					27, //71, // ВРАЧ-ТЕРАПЕВТ
					//72, // ВРАЧ-ТЕРАПЕВТ УЧАСТКОВЫЙ
					//74, // ВРАЧ-ТЕРАПЕВТ УЧАСТКОВЫЙ ЦЕХОВОГО ВРАЧЕБНОГО УЧАСТКА
					145, //82, // ВРАЧ-УРОЛОГ
					30 //87, // ВРАЧ-ХИРУРГ
					*/
				));
				/*$isOnkoControlMedSpec = in_array($tmp[0]['MedSpecOms_Code'], array(
					899,// Прием специалиста-онколога РОД - 1 уровень
					556,// Детский онкологический прием - 3 уровень
					656,// Детский онкологический прием - 2 уровень
					856,// Детский онкологический прием - 1 уровень
					521,// Онкологический прием - 3 уровень
					621,// Онкологический прием - 2 уровень
					821 //Онкологический прием - 1 уровень
					
				));*/
			}
			if (1 == $tmp[0]['IsNeedOnkoControl']
				and $tmp[0]['Person_Age'] >= 18
				and $isOnkoControlMedSpec
			) {
				$response['IsNeedOnkoControl'] = 1;
			}
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает данные для вывода списка опросов в сигнальной информации ЭМК
	 */
	function loadPersonOnkoProfileList($data)
	{
        $queryParams = array(
            'Person_id' => $data['Person_id'],
        );
        $query = "
			select 
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as \"Person_Age\"
			FROM dbo.v_PersonState PS 
			where PS.Person_id = :Person_id
            limit 1
		";
        $result = $this->db->query($query, $queryParams);
        if ( false == is_object($result) ) {
            return false;
        }
        $response = $result->result('array');
        if ( empty($response) ) {
            return false;
        }
        $person_age = $response[0]['Person_Age'];

        $queryParams = array(
                'Person_id' => $data['Person_id'],
                'MedStaffFact_id' => null,
				'MedPersonal_id' => null,
                //'pmUser_id' => $data['session']['pmuser_id'], ank.pmUser_insID = :pmUser_id
                'isSuperAdmin' => isSuperadmin() ? 1 : 0,
        );
        if (isset($data['session']['CurMedStaffFact_id'])) {
            $queryParams['MedStaffFact_id'] = $data['session']['CurMedStaffFact_id'];
        }
        if (isset($data['user_MedStaffFact_id'])) {
            $queryParams['MedStaffFact_id'] = $data['user_MedStaffFact_id'];
        }
		if (isset($data['session']['medpersonal_id'])) {
			$queryParams['MedPersonal_id'] = $data['session']['medpersonal_id'];
		}
		/*
		 * недействующие Анкеты должны отображаться в списке опросов с возможностью просмотра
		 */
		$queryParts = array("
            SELECT
				PersonOnkoProfile_id as PersonOnkoProfile_id,
				MedPersonal_id as MedPersonal_id,
				PersonOnkoProfile_setDate as PersonOnkoProfile_setDate,
				PersonProfileType_Name as PersonProfileType_Name,--тип опроса, непонятно откуда брать
				ReportType as ReportType,
				Monitored_Name as Monitored_Name,
				PMUser_Name as PMUser_Name,
				PalliatQuestion_CountYes as PalliatQuestion_CountYes,
				PalliatNotify_id as PalliatNotify_id,
				EvnNotifyBase_setDate as EvnNotifyBase_setDate,
				displayEditBtn as displayEditBtn,
				displayDelBtn as displayDelBtn
			FROM (
			select
				ank.PersonOnkoProfile_id as PersonOnkoProfile_id,
				msf.MedPersonal_id as MedPersonal_id,
				to_char(ank.PersonOnkoProfile_DtBeg, 'DD.MM.YYYY') as PersonOnkoProfile_setDate,
				'Онкология' as PersonProfileType_Name,--тип опроса, непонятно откуда брать
				'onko' as ReportType,
				COALESCE(pj.StatusOnkoProfile,'') || ': ' || COALESCE(pj.monitored_Name,'') as Monitored_Name,
				case when (ank.pmUser_insID between 1000000 and 5000000) then us.surname||us.first_name else pu.PMUser_Name end PMUser_Name,
				CAST(null as integer) as PalliatQuestion_CountYes,
				CAST(null as bigint) as PalliatNotify_id,
				null as EvnNotifyBase_setDate,
				case when msf.MedPersonal_id = :MedPersonal_id then 'inline' else 'none' end as displayEditBtn,
				'none' as displayDelBtn
			FROM onko.v_PersonOnkoProfile ank
			left join v_pmUserCache pu on pu.PMUser_id = ank.pmUser_insID
			left join (select * from UserPortal.users) us on us.main_person = ank.pmUser_insID
            left join v_MedStaffFact msf on msf.MedStaffFact_id = ank.MedStaffFact_id

				LEFT JOIN LATERAL (
					select 
					monitored_Name,
					StatusOnkoProfile
					from onko.v_ProfileJurnalFull pj 
					where ank.PersonOnkoProfile_id = pj.PersonOnkoProfile_id
                    limit 1
				) pj ON true
			where ank.Person_id = :Person_id
			ORDER BY ank.PersonOnkoProfile_DtBeg
            limit 100
            ) t
		");
		if ($data['session']['region']['nick'] != 'kz') {

			$queryParams['PalliatNotify_id'] = null;
			$queryParams['allowNotifyEdit'] = 1;
			$resp = $this->getFirstRowFromQuery("
				select
					PN.PalliatNotify_id as \"PalliatNotify_id\",
					case
						when ENB.EvnNotifyBase_niDate is null and PR.PersonRegister_id is null
						then 1 else 0
					end as \"allowNotifyEdit\"
				from v_PalliatNotify PN 
				inner join v_EvnNotifyBase ENB  on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
				LEFT JOIN LATERAL (
					select PR.PersonRegister_id
					from v_PersonRegister PR 
					inner join v_PersonRegisterType PRT  on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					where PR.EvnNotifyBase_id = ENB.EvnNotifyBase_id and PRT.PersonRegisterType_SysNick iLIKE 'palliat'
					and dbo.tzGetDate() between PR.PersonRegister_setDate and COALESCE(PR.PersonRegister_disDate, dbo.tzGetDate()) 
                    limit 1
				) PR ON true
				where ENB.Person_id = :Person_id
                limit 1
			", $queryParams, true);
			if (is_array($resp)) {
				$queryParams = array_merge($queryParams, $resp);
			}

			$queryParts[] = "
				select  
					p.PalliatQuestion_id as PersonOnkoProfile_id,
					msf.MedPersonal_id as MedPersonal_id,
					to_char(p.PalliatQuestion_setDate, 'DD.MM.YYYY') as PersonOnkoProfile_setDate,
					'Паллиативная помощь' as PersonProfileType_Name,
					'palliat' as ReportType,
					'' as Monitored_Name,
                    null as PMUser_Name,
					p.PalliatQuestion_CountYes as PalliatQuestion_CountYes,
					pn.PalliatNotify_id as PalliatNotify_id,
					pn.EvnNotifyBase_setDate as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from
					PalliatQuestion p 
					left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
					LEFT JOIN LATERAL (
						select 
							PN.PalliatNotify_id,
							to_char(ENB.EvnNotifyBase_setDate, 'DD.MM.YYYY') as EvnNotifyBase_setDate
						from v_PalliatNotify PN  
						inner join v_EvnNotifyBase ENB  on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
						where ENB.Person_id = p.Person_id
						order by ENB.EvnNotifyBase_setDate desc
                        limit 1
					) pn ON true
				where 
					p.Person_id = :Person_id
			";
		}
		if ($data['session']['region']['nick'] != 'kz') {
			$queryParts[] = "
				select  
					p.GeriatricsQuestion_id as PersonOnkoProfile_id,
					msf.MedPersonal_id as MedPersonal_id,
					to_char(p.GeriatricsQuestion_setDate, 'DD.MM.YYYY') as PersonOnkoProfile_setDate,
					'Возраст не помеха' as PersonProfileType_Name,
					'geriatrics' as ReportType,
					anh.AgeNotHindrance_Name as Monitored_Name,
					null as PMUser_Name,
    				CAST(null as integer) as PalliatQuestion_CountYes,
	    			CAST(null as bigint) as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from  
					GeriatricsQuestion p 
					inner join AgeNotHindrance anh on anh.AgeNotHindrance_id = p.AgeNotHindrance_id
				    left join v_MedStaffFact msf on msf.MedStaffFact_id = p.MedStaffFact_id
				where 
					p.Person_id = :Person_id
			";

            $queryParts[] = "
				(select
					BSKRegistry.BSKRegistry_id as PersonOnkoProfile_id,
					null as MedPersonal_id,
					to_char(BSKRegistry.BSKRegistry_setDate, 'dd.mm.yyyy') as PersonOnkoProfile_setDate,
					'БСК: ' || MT.MorbusType_Name as PersonProfileType_Name,
					'registrBSK' as ReportType,
					case 
							when MT.MorbusType_id = 84 then cast(replace(replace(replace(BSKRegistry.BSKRegistry_riskGroup::varchar, '1', 'I'), '2', 'II'), '3', 'III') as varchar(10)) || ' группа риска'
							when MT.MorbusType_id = 89 then 
									(select coalesce(ElVal.BSKObservElementValues_data,RD.BSKRegistryData_data) || ' группа риска' as BSKRegistryData_data 
									from dbo.v_BSKRegistryData RD
									left join dbo.BSKObservElementValues ElVal on ElVal.BSKObservElementValues_id = RD.BSKObservElementValues_id 
									where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 269
									limit 1)
							when MT.MorbusType_id = 88 then 
									(select coalesce(ElVal.BSKObservElementValues_data,RD.BSKRegistryData_data) || ' функциональный класс' as BSKRegistryData_data 
									from dbo.BSKRegistryData RD 
									left join dbo.BSKObservElementValues ElVal on ElVal.BSKObservElementValues_id = RD.BSKObservElementValues_id
									where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 151
									limit 1)
							else NULL end as Monitored_Name,
					null as PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					to_char(BSKRegistry.BSKRegistry_nextDate, 'dd.mm.yyyy') as EvnNotifyBase_setDate,
					'none' as displayEditBtn,
					'none' as displayDelBtn
				from
				    PersonRegister PR
                    left join dbo.MorbusType MT on MT.MorbusType_id = PR.MorbusType_id
                    left join dbo.v_BSKRegistry BSKRegistry on  BSKRegistry.MorbusType_id = MT.MorbusType_id and BSKRegistry.Person_id = :Person_id
				where
				    PR.MorbusType_id in (84,88,89,50)
                    and BSKRegistry.BSKRegistry_id is not null
                    and PR.Person_id = :Person_id
				order by MT.MorbusType_Name, BSKRegistry.BSKRegistry_setDate desc
				limit 100
				)
			";

			$queryParts[] = "
				select  
					RO.RepositoryObserv_id as PersonOnkoProfile_id,
					msf.MedPersonal_id,
					to_char(RO.RepositoryObserv_setDT, 'DD.MM.YYYY') as PersonOnkoProfile_setDate,
					'Динамическое наблюдение по COVID-19' as PersonProfileType_Name,
					'repositoryobserv' as ReportType,
					'' as Monitored_Name,
					pu.PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'none' as displayEditBtn,
					'none' as displayDelBtn
				from  
					dbo.v_RepositoryObserv RO
					inner join v_MedStaffFact as MSF on MSF.MedStaffFact_id = RO.MedStaffFact_id
					left join v_pmUserCache pu on pu.PMUser_id = RO.pmUser_insID
				where 
					RO.Person_id = :Person_id
			";
		}
		if ($data['session']['region']['nick'] == 'kz') {
			$queryParts[] = "
				(select
					p.PreVizitQuestion_id as PersonOnkoProfile_id,
					to_char(p.PreVizitQuestion_setDate, 'DD.MM.YYYY') as PersonOnkoProfile_setDate,
					'Предварительное анкетирование' as PersonProfileType_Name,
					'previzit' as ReportType,
					'' as Monitored_Name,
					null as PMUser_Name,
    				CAST(null as integer) as PalliatQuestion_CountYes,
	    			CAST(null as bigint) as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from
					PreVizitQuestion p 
				where 
					p.Person_id = :Person_id
				order by 
					p.PreVizitQuestion_setDate desc
                limit 1)
			";
		}
		
		if ($data['session']['region']['nick'] != 'kz') {
			$queryParts[] = "
				(select
					bq.BIRADSQuestion_id as PersonOnkoProfile_id,
					msf.MedPersonal_id as MedPersonal_id,
					to_char(bq.BIRADSQuestion_setDate, 'DD.MM.YYYY') as PersonOnkoProfile_setDate,
					'Оценка BI_RADS' as PersonProfileType_Name,
					'birads' as ReportType,
					cbr.CategoryBIRADS_Name as Monitored_Name,
                    null as PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from v_BIRADSQuestion bq
                    left join v_CategoryBIRADS cbr on cbr.CategoryBIRADS_id = bq.CategoryBIRADS_id
                    left join v_MedStaffFact msf on msf.MedStaffFact_id = bq.MedStaffFact_id
				where
					bq.Person_id = :Person_id
				order by
					bq.BIRADSQuestion_setDate desc
				limit 1)
			";
		}

		$query = "
			select
					PersonOnkoProfile_id as \"PersonOnkoProfile_id\",
					PersonOnkoProfile_setDate as \"PersonOnkoProfile_setDate\",
					PersonProfileType_Name as \"PersonProfileType_Name\",
					ReportType as \"ReportType\",
					PMUser_Name as \"PMUser_Name\",
					Monitored_Name as \"Monitored_Name\",
					PalliatQuestion_CountYes as \"PalliatQuestion_CountYes\",
					PalliatNotify_id as \"PalliatNotify_id\",
					EvnNotifyBase_setDate as \"EvnNotifyBase_setDate\",
					displayEditBtn as \"displayEditBtn\",
					displayDelBtn as \"displayDelBtn\",
					MedPersonal_id as \"MedPersonal_id\"
			  
			from (
				" . implode(' union all ', $queryParts) . "
			) as t
		";
		// echo getDebugSQL($query, $queryParams); exit();
		$result = $this->db->query($query, $queryParams);
		if ( false == is_object($result) ) {
			return false;
		}
        $response = $result->result('array');
		if ($this->regionNick == 'msk') {
			foreach($response as &$row) {
				if ($row['ReportType'] != 'onko') continue;
				$row['Monitored_Name'] = $this->getFirstResultFromQuery("
					select count(*) as \"cnt\"
					from onko.v_PersonOnkoQuestions p
					inner join onko.v_S_OnkoQuestions q on q.OnkoQuestions_id = p.OnkoQuestions_id
					where p.PersonOnkoProfile_id = ? and p.OnkoQuestions_id != 68
				", [$row['PersonOnkoProfile_id']]) > 0 ? 'Необходим онкоконтроль' : 'Онкоконтроль не требуется';
			}
		}
        $response[] = array('PersonOnkoProfile_id' => -1, 'Person_Age'=>$person_age);

		//todo доработать
		$queryMedicalForm = "
			select
				'MedicalForm' as type,
				to_char(MFP.MedicalFormPerson_insDT, 'DD.MM.YYYY') as \"PersonOnkoProfile_setDate\",
				MFP.MedicalForm_id as \"MedicalForm_id\",
				MFP.MedicalFormPerson_id as \"MedicalFormPerson_id\",
				MFP.MedicalFormPerson_id as \"PersonOnkoProfile_id\",
				MF.MedicalForm_Name as \"MedicalForm_Name\",
				MF.MedicalForm_Description as \"MedicalForm_Description\",
				MF.MedicalForm_Name as \"PersonProfileType_Name\"
			from v_MedicalFormPerson MFP
			left join v_MedicalForm MF on MF.MedicalForm_id = MFP.MedicalForm_id
			where
				MFP.Person_id = :Person_id
		";

		$resultMedicalForm = $this->db->query($queryMedicalForm, $queryParams);
		if ( false == is_object($resultMedicalForm) ) {
			return false;
		}

		$response = array_merge($response, $resultMedicalForm->result('array'));

        return $response;
	}
        
     /**
     * Получение списка диагнозов по онкологии 
     *
     */
	
    function GetZNO4Person($data)
	{
        $queryParams = array(
            'Person_id' => $data['Person_id'],
        );
        
         $query = "
              Select
				  Person_id as \"Person_id\",
				  Diag_setDate as \"Diag_setDate\",
				  Diag_Code as \"Diag_Code\",
				  Diag_Name as \"Diag_Name\"
              from onko.fn_GetZNO4Person(:Person_id)
    ";

        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (анкетирование по онкоконтролю)'));
        }
    }
        
     /**
     * ------------
     */
    function sendMessage($data)	{
		
		if ($this->regionNick != 'msk') return false;
		
		$this->load->helper('Options');
		$this->setSessionParams($data['session']);
		$options = $this->globalOptions['globals'];
		if (empty($options['send_onkoctrl_msg']) || $options['send_onkoctrl_msg'] == false) {
			return false;
		}
		
		$PersonPhone = $this->getFirstResultFromQuery("
			select
				case when PersonPhoneStatus_id = 3 then PP.PersonPhone_Phone else null end as \"PersonPhone_Phone\"
			from
				v_PersonPhoneHist PPH
				inner join v_PersonPhone PP on PP.PersonPhone_id = PPH.PersonPhone_id
			where 
				PPH.Person_id = :Person_id
			order by 
				PPH.PersonPhoneHist_insDT desc
			limit 1
		", $data);
		
		if ($PersonPhone == false || $PersonPhone == null) {
			return false;
		}
		
		$this->load->helper('Notify');
		
		sendNotifySMS([
			'UserNotify_Phone' => $PersonPhone,
			'text' => 'За результатами анкетирования обратитесь к вашему лечащему врачу',
			'User_id' => $data['pmUser_id']
		]);
    }
}