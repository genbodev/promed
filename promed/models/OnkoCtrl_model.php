<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Класс модели для работы по онкокнтролю
 *
 * @author    Nigmatullin Tagir
 * @version   12.09.2014
 */
class OnkoCtrl_model extends swModel {

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
			$filter .= " and StatusOnkoProfile_id = :StatusOnkoProfile_id";
			$queryParams['StatusOnkoProfile_id'] = $data['StatusOnkoProfile_id'];
		};

		if (isset($data['Lpu_id']))  {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
			//$filter .= " and Lpu_id = :Lpu_id";
			if (isset($data['StatusOnkoProfile_id'])) {
				if ($data['StatusOnkoProfile_id'] == 1)  //  Не заполненные анкеты
					$filter .= " and p.Lpu_id = :Lpu_id";
				if ($data['StatusOnkoProfile_id'] == 2)  //  Заполненные анкеты
					$filter .= " and LpuProfile_id = :Lpu_id";
			} else     //  Все анкеты
				$filter .= " and (LpuProfile_id = :Lpu_id or (p.Lpu_id = :Lpu_id and StatusOnkoProfile_id = 1))";
		}

		if (isset($data['SurName'])) {
			$filter .= " and p.SurName like :SurName";
			$queryParams['SurName'] = $data['SurName']."%";
		};

		if (isset($data['FirName'])) {
			$filter .= " and p.FirName like :FirName";
			$queryParams['FirName'] = $data['FirName']."%";
		};

		if (isset($data['SecName'])) {
			$filter .= " and p.SecName like :SecName";
			$queryParams['SecName'] = $data['SecName']."%";
		};

		if (isset($data['BirthDayRange'][0])) {
			$filter .= " and p.BirthDay  <= :BirthDayRangeBegin";
			$queryParams['BirthDayRangeBegin'] = $data['BirthDayRange'][0];
		}

		if (isset($data['BirthDayRange'][1])) {
			$filter .= " and p.BirthDay  <= :BirthDayRangeEnd";
			$queryParams['BirthDayRangeEnd'] = $data['BirthDayRange'][1];
		}

		if (isset($data['BirthDay'])) {
			$filter .= " and p.BirthDay  = :BirthDay";
			$queryParams['BirthDay'] = $data['BirthDay'];
		}

		if (isset($data['PeriodRange'][0])) {
			$filter .= " and p.PersonOnkoProfile_DtBeg  >= :PeriodRangeBegin";
			$queryParams['PeriodRangeBegin'] = $data['PeriodRange'][0];
		}

		if (isset($data['PeriodRange'][1])) {
			$filter .= " and p.PersonOnkoProfile_DtBeg  <= :PeriodRangeEnd";
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
				$filter .= " and (Uch = '' or p.lpu_id is null or p.lpu_id <>  LpuProfile_id)";
			} else {
				$filter .= " and Uch = :Uch";
				$queryParams['Uch'] = $data['Uch'];
			}
		};


		if (isset($data['OnkoQuestions_id'])) {
			$filter_join .= "join onko.PersonOnkoQuestions t0 on t0.PersonOnkoProfile_id = p.PersonOnkoProfile_id and t0.OnkoQuestions_id = :OnkoQuestions_id";
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
					$joinMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				else
					$whereMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				// $whereMore[] = ' ['.$this->getNameColumn($col).'] in('.$temp.')';
			}

			//$where = (isset($whereMore)) ? ' and ' .implode(' and ', $whereMore) : $where;
			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
										join onko.PersonOnkoQuestions t with(nolock) on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
										join onko.S_OnkoQuestions Q with(nolock) on q.OnkoQuestions_id = t.OnkoQuestions_id and " . implode(' and ', $joinMore);
			}
		}

		if (isset($where)) {
			$filter .= $where;
		}


            //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $filter =' . $filter);
            //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $join =' . $join);
	    $UserPortalName = $this->load->database('UserPortal', true)->database;
		 $sql = "
			   
			SELECT  
				-- select
					pop.pmUser_insID,
					pu.PMUser_Name,
					p.PersonOnkoProfile_id id
					, p.Person_id
					,p.PersonOnkoProfile_id
					,p.SurName
					,FirName
					,SecName
					,fio
					,convert(Varchar, p.BirthDay, 104) BirthDay
					,DATE_SMERT
					,Sex_id
					,sex
					,Address
					,uch
					,LpuRegion_id
					,SocStatus_id
					,SocStatus_Name
					,p.Lpu_id
					,p.Lpu_Nick
					,LpuProfile_id
					,StatusOnkoProfile_id
					, convert(Varchar, p.PersonOnkoProfile_DtBeg, 104) PersonOnkoProfile_DtBeg
					,StatusOnkoProfile
					,p.monitored
					,monitored_Name
					,p.ProfileResult
					--,MedPersonal_id
					, p.MedStaffFact_id
					,p.MedPersonal_fin
					,Person_dead
				-- end select
			  FROM 
			  -- from
				onko.v_ProfileJurnal p with(nolock)
				left join onko.v_PersonOnkoProfile pop with (nolock) on pop.PersonOnkoProfile_id = p.PersonOnkoProfile_id
				left join v_pmUserCache pu with (nolock) on pu.PMUser_id = pop.pmUser_insID
				   
				{$filter_join}
				{$join}
			  -- end from  
				WHERE 
				-- where
				 " . $filter . "
				-- end where        
				order by 
				-- order by
					p.SurName, FirName, SecName, p.BirthDay, PersonOnkoProfile_DtBeg
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
			//$filter .= " and SurName like '" . $data['SurName'] . "%'";
			$Surname = "'" . $data['SurName'] . "%'";
		};

		if (isset($data['FirName'])) {
			//$filter .= " and FirName like '" . $data['FirName'] . "%'";
			$Firname = "'" . $data['FirName'] . "%'";
		};

		if (isset($data['SecName'])) {
			//$filter .= " and SecName like '" . $data['SecName'] . "%'";
			$SecName = "'" . $data['SecName'] . "%'";
		};

		if (isset($data['BirthDayRange'][1])) {
			//$filter .= " and BirthDay  <= '" . $data['BirthDayRange'][1] . "'";
			$BirthDayEnd = "'" . $data['BirthDayRange'][1] . "'";
		}

		if (isset($data['BirthDayRange'][0])) {
			//$filter .= " and BirthDay  >= '" . $data['BirthDayRange'][0] . "'";
			$BirthDayBeg = "'" . $data['BirthDayRange'][0] . "'";
		} // PeriodRange             } 

		if (isset($data['BirthDay'])) {
			//$filter .= " and BirthDay  = '" . $data['BirthDay'] ."'";
			$BirthDay = "'" . $data['BirthDay'] . "'";
		}

		if (isset($data['PeriodRange'][1])) {
			$filter .= " and PersonOnkoProfile_DtBeg  <= '" . $data['PeriodRange'][1] . "'";
		}

		if (isset($data['PeriodRange'][0])) {
			$filter .= " and PersonOnkoProfile_DtBeg  >= '" . $data['PeriodRange'][0] . "'";
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
					$joinMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				else
					$whereMore[] = ' [' . $this->getNameColumn($col) . '] in(' . $temp . ')';
				// $whereMore[] = ' ['.$this->getNameColumn($col).'] in('.$temp.')';
			}

			//$where = (isset($whereMore)) ? ' and ' .implode(' and ', $whereMore) : $where; 
			$where = (isset($whereMore)) ? ' and ' . implode(' and ', $whereMore) : $where;

			if (isset($joinMore)) {
				$join = "
										join onko.PersonOnkoQuestions t with(nolock) on t.PersonOnkoProfile_id = p.PersonOnkoProfile_id
										join onko.S_OnkoQuestions Q with(nolock) on q.OnkoQuestions_id = t.OnkoQuestions_id and " . implode(' and ', $joinMore);
			}
		}

		if (isset($where)) {
			$filter .= $where;
		}


        //log_message('debug', 'GetOnkoCtrlProfileJurnal:  $filter =' . $filter);

        $declare = "
            Declare
                    @Lpu_id bigint = {$Lpu_id},
                    @Surname varchar(50) = {$Surname},
                    @Firname varchar(50) = {$Firname},
                    @SecName varchar(50) = {$SecName},
                    @BirthDay date = {$BirthDay},
                    @BirthDayBeg date = {$BirthDayBeg},
                    @BirthDayEnd date = {$BirthDayEnd},
                    --@Sex_id int,
                    @Empty int = {$Empty};            
            " ;   
 
         $sql = "
                {$declare}
      SELECT  
                -- select
                    p.PersonOnkoProfile_id id
                    , Person_id
                    ,p.PersonOnkoProfile_id
                    ,SurName
                    ,FirName
                    ,SecName
                    ,fio
                    ,convert(Varchar, BirthDay, 104) BirthDay
                    ,DATE_SMERT
                    ,Sex_id
                    ,sex
                    ,Address
                    ,uch
                    ,LpuRegion_id
                    ,SocStatus_id
                    ,SocStatus_Name
                    ,Lpu_id
                    ,Lpu_Nick
                    ,LpuProfile_id
                    ,StatusOnkoProfile_id
                    , convert(Varchar, PersonOnkoProfile_DtBeg, 104) PersonOnkoProfile_DtBeg
                    ,StatusOnkoProfile
                    ,monitored
                    ,monitored_Name
                    ,ProfileResult
                    --,MedPersonal_id
                    , MedStaffFact_id
                    ,MedPersonal_fin
                    ,Person_dead
                -- end select
              FROM 
              -- from
                  " . $function . " (@Lpu_id, @Surname, @Firname, @SecName, @BirthDay, @BirthDayBeg, @BirthDayEnd, @Empty) p   
                
                {$filter_join}
                {$join}
              -- end from  
                WHERE 
                -- where
                 " . $filter . "
                -- end where        
                order by 
                -- order by
                    SurName, FirName, SecName, BirthDay, PersonOnkoProfile_DtBeg 
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
            $sql = "{$declare} {$sql} ";
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
			$filters .= " and (q.OnkoQuestions_begDate is null or q.OnkoQuestions_begDate <= :OnkoCtrl_Date)";
			$filters .= " and (q.OnkoQuestions_endDate is null or q.OnkoQuestions_endDate >= :OnkoCtrl_Date)";
			$queryParams['OnkoCtrl_Date'] = $data['OnkoCtrl_Date'];
		}

		if ($this->regionNick == 'msk') {
			$filters .= " and isnull(q.Sex_id, @Sex_id) = @Sex_id";
		}

        $sql = "
			declare @PersonOnkoProfile_id bigint;
			declare @Sex_id bigint = (select top 1 Sex_id from v_PersonState (nolock) where Person_id = :Person_id);
			set @PersonOnkoProfile_id = :PersonOnkoProfile_id;
			select
				q.OnkoQuestions_id,
				q.OnkoQuestions_Name,
				at.AnswerType_Code,
				ac.AnswerClass_SysNick,
				cast(case 
					when q.AnswerType_id = 1 then isnull(p.PersonOnkoQuestions_IsTrue, 1)
					when q.AnswerType_id = 2 then p.PersonOnkoQuestions_Answer
					else p.PersonOnkoQuestions_ValueIdent
				end as varchar) as val
			from
				onko.v_S_OnkoQuestions q with(nolock)
				left join onko.v_PersonOnkoQuestions p with(nolock) on p.OnkoQuestions_id = q.OnkoQuestions_id
					and p.PersonOnkoProfile_id = @PersonOnkoProfile_id
				left join v_AnswerType at with(nolock) on at.AnswerType_id = q.AnswerType_id
				left join v_AnswerClass ac with(nolock) on ac.AnswerClass_id = q.AnswerClass_id
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
			$join = 'join onko.v_ProfileJurnalAct act WITH (NOLOCK) on act.Person_id = p.Person_id
                and act.PersonOnkoProfile_id = p.PersonOnkoProfile_id';
		}

		$query = "
			Declare
			@Person_id bigint = :Person_id;
			
			With 
			t as (
				Select @Person_id Person_id
			),
			PersZno as (
				Select top 1 Person_id, Diag_id,
				convert(varchar, Diag_setDate, 104) Diag_setDate
				,Diag_Code, Diag_Name from onko.fn_GetZNO4Person (@Person_id)
				order by Diag_setDate 
			),
			OnkoProfile as (    
				SELECT 
					p.PersonOnkoProfile_id
					,p.Person_id
					,convert(Varchar, p.PersonOnkoProfile_DtBeg, 104) PersonOnkoProfile_DtBeg
					,p.Lpu_id
					,l.Lpu_Nick
					,msf.MedStaffFact_id
					,msf.LpuSection_id
					,msf.LpuBuilding_id
					,p.Evn_id
					,convert(Varchar, evn.Evn_setDT, 104) Evn_setDT
				FROM 
					onko.PersonOnkoProfile p WITH (NOLOCK)
					left join v_MedStaffFact msf with(nolock) on msf.MedStaffFact_id = p.MedStaffFact_id
					{$join}
					left join v_Lpu l WITH (NOLOCK) on l.Lpu_id = p.lpu_id
					left join v_Evn evn WITH (NOLOCK) on evn.Evn_id = p.Evn_id
				where 
					p.Person_id = @Person_id
					and p.PersonOnkoProfile_StatusID = 0 
					{$filter} 
			)
			SELECT
				p.PersonOnkoProfile_id
				,t.Person_id
				,convert(Varchar, p.PersonOnkoProfile_DtBeg, 104) PersonOnkoProfile_DtBeg
				,p.Lpu_id
				,p.Lpu_Nick
				,p.MedStaffFact_id
				,p.LpuSection_id
				,p.LpuBuilding_id
				,z.Diag_setDate
				,z.Diag_Code
				,z.Diag_Name
				,z.Diag_id
				,p.Evn_id
				,p.Evn_setDT
			FROM t 
				left join PersZno z with(nolock) on z.Person_id = t.Person_id 
				left join OnkoProfile p with(nolock)  on p.Person_id = t.Person_id
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
					declare
						@Res bigint,
						@Error_Code bigint,
						@Error_Message varchar(4000);
					exec onko.p_PersonOnkoProfile_ins
						@PersonOnkoProfile_id   = null,
						@Person_id              = :Person_id,
						@Profile_Date           = :Profile_Date,
						@MedStaffFact_id        = :MedStaffFact_id,
						@Lpu_id                 = :Lpu_id,
						@Questions              = '',
						@PmUser_id           	= :pmUser_id,
						@Error_Code             = @Error_Code output,
						@Error_Message          = @Error_Message output,
						@Out_PersonOnkoProfile_id = @Res output;
					select @Res as PersonOnkoProfile_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
					select top 1
						q.AnswerType_id,
						q.AnswerClass_id,
						p.PersonOnkoQuestions_id
					from onko.v_S_OnkoQuestions q with(nolock)
					left join onko.v_PersonOnkoQuestions p with(nolock) on p.OnkoQuestions_id = q.OnkoQuestions_id 
						and p.PersonOnkoProfile_id = :PersonOnkoProfile_id
					where q.OnkoQuestions_id = :OnkoQuestions_id
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
							onko.PersonOnkoQuestions with(rowlock)
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
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					set nocount on;
					begin try
						{$modifyDataQuery}
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch;
					set nocount off;
					select @Error_Code as Error_Code, @Error_Message as Error_Msg;
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
            Declare
                @Dt_Srart datetime,
                @Dt_End datetime,
                @Lpu_id bigint;
                
            Set @Dt_Srart = :Dt_Srart;	
            Set @Dt_End = :Dt_End;    
            --Set @Lpu_id = :Lpu_id;
                
            with
				tmp as ( 
            Select 0 _type, Lpu_id, Lpu_Nick, Lpu_Name, Kol_Zno, Kol, 
                KolPassed, NeedOnko, NotNeedOnko, NotKolPassed
            from onko.fn_OnkoReportSetZNO (@Lpu_id, @Dt_Srart, @Dt_End )
               --order by kol desc 
               ),
              tmp_all as
                (Select 1 _type, null Lpu_id, 'Итого: ' Lpu_Nick, null Lpu_Name, Sum(Kol_Zno) Kol_Zno, Sum(Kol) Kol,
                        Sum(KolPassed) KolPassed, Sum(NeedOnko) NeedOnko, Sum(NotNeedOnko) NotNeedOnko, Sum(NotKolPassed) NotKolPassed
                      from tmp with(nolock))
            Select   _type, Lpu_id, Lpu_Nick, Lpu_Name, Kol_Zno, Kol, 
                 KolPassed, NeedOnko, NotNeedOnko, NotKolPassed
                 from (   
            Select  _type, Lpu_id, Lpu_Nick, Lpu_Name, Kol_Zno, Kol, 
                  KolPassed, NeedOnko, NotNeedOnko, NotKolPassed
                 from tmp  with(nolock)
            union
            Select  _type, Lpu_id, Lpu_Nick, Lpu_Name, Kol_Zno, Kol, 
                  KolPassed, NeedOnko, NotNeedOnko, NotKolPassed
                 from tmp_all with(nolock)) t     
                 order by _type, Kol desc  	
            ";
			
		   $query = "
            Declare
                @Dt_Srart datetime,
                @Dt_End datetime,
                @Lpu_id bigint;
                
            Set @Dt_Srart = :Dt_Srart;	
            Set @Dt_End = :Dt_End;    
            --Set @Lpu_id = :Lpu_id;
            
            Select  _type, Lpu_id, Lpu_Nick, Lpu_Name, Kol_Zno, Kol, 
                  KolPassed, NeedOnko, NotNeedOnko, NotKolPassed
                 from  onko.fn_OnkoReportSetZNO (@Lpu_id, @Dt_Srart, @Dt_End ) 
                 order by _type, Kol desc    
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

        $declare = "
            Declare 
                @Lpu_id bigint = :Lpu_id,
                @Dt_Srart date = :Dt_Srart,
                @Dt_End date = :Dt_End,
                @Field varchar (100) = :Field;                  
        "; 

        $query = "
                    {$declare}
                    SELECT 
                        -- select
                        Person_id, 
                        SurName, 
                        FirName, 
                        SecName, 
                        Fio, 
                        convert(Varchar, BirthDay, 104) BirthDay, 
                        convert(Varchar, Ds_date, 104) Ds_date, 
                        Diag_Code,  
                        Diag_Name,
                        MedPersonal_fin,
                        convert(Varchar, Profile_Date, 104) Profile_Date,  
                        ProfileResult
                        -- end select
                        FROM 
                        --from
                        {$Table}
                        (@Lpu_id, @Dt_Srart, @Dt_End, @Field)
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
            {$declare}
            Select count(1) AS cnt
            FROM 
            --from
            {$Table}
            (@Lpu_id, @Dt_Srart, @Dt_End, @Field)
            --  end from          
";


        if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
            $query = getLimitSQLPH($query, $data['start'], $data['limit']);
            $query = "{$declare} {$query} ";
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
            Declare
                @Dt_Srart datetime,
                @Dt_End datetime,
                @Lpu_id bigint;
                
            Set @Dt_Srart = :Dt_Srart;	
            Set @Dt_End = :Dt_End;    
            --Set @Lpu_id = :Lpu_id;

            with
                tmp as (
            Select 0 _type, Lpu_id, Lpu_Nick, 
                 KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket
            from onko.fn_OnkoReportMonitoring (@Lpu_id, @Dt_Srart, @Dt_End )
               --order by KolPassed desc
               ),
               tmp_all as
               (Select  1 _type, null Lpu_id, 'Итого: ' Lpu_Nick, 
                 Sum(KolPassed) KolPassed, Sum(NeedOnko) NeedOnko, Sum(NotNeedOnko) NotNeedOnko, Sum(KolZnoAll) KolZnoAll, 
                 Sum(KolZnoUnderAnket) KolZnoUnderAnket, Sum(kolZnoDespiteAnket) kolZnoDespiteAnket 
                 from tmp)
            Select   _type, Lpu_id, Lpu_Nick, 
                 KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket 
                 from (   
            Select  _type, Lpu_id, Lpu_Nick, 
                 KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket 
                 from tmp 
            union
		Select  _type, Lpu_id, Lpu_Nick, 
                 KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket 
                 from tmp_all) t     
                 order by _type, KolPassed desc   
            ";
			
		    $query = "
            Declare
                @Dt_Srart datetime,
                @Dt_End datetime,
                @Lpu_id bigint;
                
            Set @Dt_Srart = :Dt_Srart;	
            Set @Dt_End = :Dt_End;    
            --Set @Lpu_id = :Lpu_id;

  
            Select  _type, Lpu_id, Lpu_Nick, 
                 KolPassed, NeedOnko, NotNeedOnko, KolZnoAll, KolZnoUnderAnket, kolZnoDespiteAnket 
                 from onko.fn_OnkoReportMonitoring (@Lpu_id, @Dt_Srart, @Dt_End )
       
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

        $declare = "
            Declare 
                @Lpu_id bigint = :Lpu_id,
                @Dt_Srart date = :Dt_Srart,
                @Dt_End date = :Dt_End,
                @Field varchar (100) = :Field;                  
        "; 

        $query = "
                    {$declare}
                    SELECT 
                        -- select
                        Person_id, 
                        SurName, 
                        FirName, 
                        SecName, 
                        --Fio, 
                        convert(Varchar, BirthDay, 104) BirthDay, 
                        convert(Varchar, Prof_DtBeg, 104) Prof_DtBeg, 
                        Prof_MedPersonal_fio,
                        Prof_ProfileResult,
                        convert(Varchar, Diag_Date, 104) Diag_Date, 
                        Diag_Code,  
                        Diag_Name,
                        Diag_MedPersonal_fio
                        -- end select
                        FROM 
                        --from
                        {$Table}
                        (@Lpu_id, @Dt_Srart, @Dt_End, @Field)
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
            {$declare}
            Select count(1) AS cnt
            FROM 
            --from
            {$Table}
            (@Lpu_id, @Dt_Srart, @Dt_End, @Field)
            --  end from          
";


        if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
            $query = getLimitSQLPH($query, $data['start'], $data['limit']);
            $query = "{$declare} {$query} ";
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
					select ank.PersonOnkoProfile_id
					FROM onko.PersonOnkoProfile ank with (nolock)
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
				Declare
					@ErrCode bigint = 0,
					@ErrMessage varchar(4000) = '';
		 
				exec onko.p_OnkoCtrlProfile_del
					@PersonOnkoProfile_id   = :PersonOnkoProfile_id,
					@User_id                = :User_id,
				
					@Error_Code             = @ErrCode output,    -- Код ошибки
					@Error_Message          = @ErrMessage output -- Тект ошибки
				
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg 
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
        Select OnkoQuestions_id
                    ,OnkoQuestions_Nick
          from (      
            SELECT  OnkoQuestions_id
                  ,OnkoQuestions_Nick
              FROM onko.S_OnkoQuestions with(nolock)
            union
            Select -1  OnkoQuestions_id,
                    'Все' OnkoQuestions_Nick)t
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
			UPDATE onko.PersonOnkoProfile WITH (ROWLOCK)
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
			select top 1
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as Person_Age,
				case when ank.PersonOnkoProfile_id is null and ZNO is null then 1 else 0 end as IsNeedOnkoControl,
				spec.MedSpecOms_Code
			FROM dbo.v_PersonState PS  with (nolock)
			inner join dbo.v_MedStaffFact MSF with (nolock) on MSF.MedStaffFact_id = :MedStaffFact_id
			left join dbo.v_MedSpecOms Spec with (nolock) on Spec.MedSpecOms_id = MSF.MedSpecOms_id
			outer apply (
				select top 1
				t.PersonOnkoProfile_id
				FROM onko.v_PersonOnkoProfile t with (nolock)
				where t.Person_id = PS.Person_id
				and t.PersonOnkoProfile_DtBeg between convert(datetime,'1/1/'+convert(char(4),year(dbo.tzGetDate())),101)and convert(datetime,'12/31/'+convert(char(4),year(dbo.tzGetDate())),101) 
			) ank
			outer apply(
				select top 1 1 as ZNO from  onko.fn_GetZNO4Person (PS.Person_id)
			) ZNO
			where PS.Person_id = :Person_id
		";
		/*
				case when ank.PersonOnkoProfile_id is null
					OR (DATEDIFF(day,ank.PersonOnkoProfile_DtBeg,dbo.tzGetDate()) >= 365)
				then 1 else 0 end as IsNeedOnkoControl,
			outer apply (
				select top 1
				t.PersonOnkoProfile_id,
				t.PersonOnkoProfile_DtBeg
				FROM onko.PersonOnkoProfile t with (nolock)
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
			select top 1
				dbo.Age2(PS.Person_BirthDay, dbo.tzGetDate()) as Person_Age
			FROM dbo.v_PersonState PS with (nolock)
			where PS.Person_id = :Person_id
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

		//На некоторых регионах UserPortal называется по другому, поэтому вытаскиваем из настроек бд
		$UserPortalName = $this->load->database('UserPortal', true)->database;

		$queryParts = array("
			select top 100
				ank.PersonOnkoProfile_id,
				msf.MedPersonal_id,
				convert(varchar(10),ank.PersonOnkoProfile_DtBeg,104) as PersonOnkoProfile_setDate,
				'Онкология' as PersonProfileType_Name,--тип опроса, непонятно откуда брать
				'onko' ReportType,
				isnull(pj.StatusOnkoProfile,'') + ': ' + isnull(pj.monitored_Name,'') as Monitored_Name,
				pu.PMUser_Name,
				null as PalliatQuestion_CountYes,
				null as PalliatNotify_id,
				null as EvnNotifyBase_setDate,
				case when msf.MedPersonal_id = :MedPersonal_id then 'inline' else 'none' end as displayEditBtn,
				'none' as displayDelBtn
			FROM onko.v_PersonOnkoProfile ank with (nolock)
			left join v_pmUserCache pu with (nolock) on pu.PMUser_id = ank.pmUser_insID
			left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ank.MedStaffFact_id
				outer apply (
					select top 1
					monitored_Name,
					StatusOnkoProfile
					from onko.v_ProfileJurnalFull pj with (nolock)
					where ank.PersonOnkoProfile_id = pj.PersonOnkoProfile_id
				) pj
			where ank.Person_id = :Person_id
			ORDER BY ank.PersonOnkoProfile_DtBeg
		");
		if ($data['session']['region']['nick'] != 'kz') {

			$queryParams['PalliatNotify_id'] = null;
			$queryParams['allowNotifyEdit'] = 1;
			$resp = $this->getFirstRowFromQuery("
				declare @date date = dbo.tzGetDate();
				select top 1 
					PN.PalliatNotify_id,
					case
						when ENB.EvnNotifyBase_niDate is null and PR.PersonRegister_id is null
						then 1 else 0
					end as allowNotifyEdit
				from v_PalliatNotify PN with(nolock)
				inner join v_EvnNotifyBase ENB with(nolock) on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
				outer apply (
					select top 1 PR.PersonRegister_id
					from v_PersonRegister PR with(nolock)
					inner join v_PersonRegisterType PRT with(nolock) on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					where PR.EvnNotifyBase_id = ENB.EvnNotifyBase_id and PRT.PersonRegisterType_SysNick like 'palliat'
					and @date between PR.PersonRegister_setDate and isnull(PR.PersonRegister_disDate, @date) 
				) PR
				where ENB.Person_id = :Person_id
			", $queryParams, true);
			if (is_array($resp)) {
				$queryParams = array_merge($queryParams, $resp);
			}

			$queryParts[] = "
				select  
					p.PalliatQuestion_id as PersonOnkoProfile_id,
					msf.MedPersonal_id,
					convert(varchar(10),p.PalliatQuestion_setDate,104) as PersonOnkoProfile_setDate,
					'Паллиативная помощь' as PersonProfileType_Name,
					'palliat' as ReportType,
					'' as Monitored_Name,
					null as PMUser_Name,
					p.PalliatQuestion_CountYes,
					pn.PalliatNotify_id,
					pn.EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from
					PalliatQuestion p (nolock)
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = p.MedStaffFact_id
					outer apply (
						select top 1 
							PN.PalliatNotify_id,
							convert(varchar(10), ENB.EvnNotifyBase_setDate, 104) as EvnNotifyBase_setDate
						from v_PalliatNotify PN (nolock) 
						inner join v_EvnNotifyBase ENB with(nolock) on ENB.EvnNotifyBase_id = PN.EvnNotifyBase_id
						where ENB.Person_id = p.Person_id
						order by ENB.EvnNotifyBase_setDate desc
					) pn
				where 
					p.Person_id = :Person_id
			";
		}
		if ($data['session']['region']['nick'] != 'kz') {
			$queryParts[] = "
				select  
					p.GeriatricsQuestion_id as PersonOnkoProfile_id,
					msf.MedPersonal_id,
					convert(varchar(10), p.GeriatricsQuestion_setDate, 104) as PersonOnkoProfile_setDate,
					'Возраст не помеха' as PersonProfileType_Name,
					'geriatrics' as ReportType,
					anh.AgeNotHindrance_Name as Monitored_Name,
					null as PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from  
					GeriatricsQuestion p (nolock)
					inner join AgeNotHindrance anh on anh.AgeNotHindrance_id = p.AgeNotHindrance_id
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = p.MedStaffFact_id
				where 
					p.Person_id = :Person_id
			";

			$queryParts[] = "
				select top 100
					BSKRegistry.BSKRegistry_id as PersonOnkoProfile_id,
					null as MedPersonal_id,
					convert(varchar(10), BSKRegistry.BSKRegistry_setDate, 104) as PersonOnkoProfile_setDate,
					'БСК: ' + MT.MorbusType_Name as PersonProfileType_Name,
					'registrBSK' as ReportType,
					case 
							when MT.MorbusType_id = 84 then cast(replace(replace(replace(BSKRegistry.BSKRegistry_riskGroup, '1', 'I'), '2', 'II'), '3', 'III') as varchar(10)) + ' группа риска'
							when MT.MorbusType_id = 89 then 
								(select top 1 isnull(ElVal.BSKObservElementValues_data,RD.BSKRegistryData_data) + ' группа риска' as BSKRegistryData_data 
								from dbo.v_BSKRegistryData RD with (nolock)
								left join dbo.BSKObservElementValues ElVal with (nolock) on ElVal.BSKObservElementValues_id = RD.BSKObservElementValues_id
								where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 269)
							when MT.MorbusType_id = 88 then 
								(select top 1 isnull(ElVal.BSKObservElementValues_data,RD.BSKRegistryData_data) + ' функциональный класс' as BSKRegistryData_data
								from dbo.v_BSKRegistryData RD with (nolock)
								left join dbo.BSKObservElementValues ElVal with (nolock) on ElVal.BSKObservElementValues_id = RD.BSKObservElementValues_id
								where RD.BSKRegistry_id = BSKRegistry.BSKRegistry_id and RD.BSKObservElement_id = 151)
							else NULL end as Monitored_Name,
					null as PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					convert(varchar(10), BSKRegistry.BSKRegistry_nextDate, 104) as EvnNotifyBase_setDate,
					'none' as displayEditBtn,
					'none' as displayDelBtn
				from PersonRegister PR with (nolock)
				left join dbo.MorbusType MT  with (nolock) on MT.MorbusType_id = PR.MorbusType_id
				left join dbo.v_BSKRegistry BSKRegistry with (nolock) on  BSKRegistry.MorbusType_id = MT.MorbusType_id and BSKRegistry.Person_id = :Person_id
				where PR.MorbusType_id in (84,88,89,50)
				and BSKRegistry.BSKRegistry_id is not null
				and PR.Person_id = :Person_id
				order by MT.MorbusType_Name, BSKRegistry.BSKRegistry_setDate desc
			";

			$queryParts[] = "
				select  
					RO.RepositoryObserv_id as PersonOnkoProfile_id,
					msf.MedPersonal_id,
					convert(varchar(10), RO.RepositoryObserv_setDT, 104) as PersonOnkoProfile_setDate,
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
					dbo.v_RepositoryObserv RO with (nolock)
					inner join v_MedStaffFact as MSF with (nolock) on MSF.MedStaffFact_id = RO.MedStaffFact_id
					left join v_pmUserCache pu with (nolock) on pu.PMUser_id = RO.pmUser_insID
				where 
					RO.Person_id = :Person_id
			";

		}
		if ($data['session']['region']['nick'] == 'kz') {
			$queryParts[] = "
				select top 1
					p.PreVizitQuestion_id as PersonOnkoProfile_id,
					null as MedPersonal_id,
					convert(varchar(10),p.PreVizitQuestion_setDate,104) as PersonOnkoProfile_setDate,
					'Предварительное анкетирование' as PersonProfileType_Name,
					'previzit' as ReportType,
					'' as Monitored_Name,
					null as PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from
					PreVizitQuestion p (nolock)
				where 
					p.Person_id = :Person_id
				order by 
					p.PreVizitQuestion_setDate desc
					
			";
		}

		if ($data['session']['region']['nick'] != 'kz') {
			$queryParts[] = "
				select top 1
					bq.BIRADSQuestion_id as PersonOnkoProfile_id,
					msf.MedPersonal_id,
					convert(varchar(10), bq.BIRADSQuestion_setDate, 104) as PersonOnkoProfile_setDate,
					'Оценка BI_RADS' as PersonProfileType_Name,
					'birads' as ReportType,
					cbr.CategoryBIRADS_Name as Monitored_Name,
					null as PMUser_Name,
					null as PalliatQuestion_CountYes,
					null as PalliatNotify_id,
					null as EvnNotifyBase_setDate,
					'inline' as displayEditBtn,
					'inline' as displayDelBtn
				from v_BIRADSQuestion bq (nolock)
				left join v_CategoryBIRADS cbr with (nolock) on cbr.CategoryBIRADS_id = bq.CategoryBIRADS_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = bq.MedStaffFact_id
				where
					bq.Person_id = :Person_id
				order by
					bq.BIRADSQuestion_setDate desc
			";
		}

		$query = "
			select * from (
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
					select count(*) [cnt]
					from onko.v_PersonOnkoQuestions p (nolock) 
					inner join onko.v_S_OnkoQuestions q (nolock) on q.OnkoQuestions_id = p.OnkoQuestions_id
					where p.PersonOnkoProfile_id = ? and p.OnkoQuestions_id != 68
				", [$row['PersonOnkoProfile_id']]) > 0 ? 'Необходим онкоконтроль' : 'Онкоконтроль не требуется';
			}
		}
        $response[] = array('PersonOnkoProfile_id' => -1, 'Person_Age'=>$person_age);

		//todo доработать
		$queryMedicalForm = "
			select
				'MedicalForm' as type,
				convert(varchar(10), MFP.MedicalFormPerson_insDT,104) as PersonOnkoProfile_setDate,
				MFP.MedicalForm_id,
				MFP.MedicalFormPerson_id,
				MFP.MedicalFormPerson_id as PersonOnkoProfile_id,
				MF.MedicalForm_Name,
				MF.MedicalForm_Description,
				MF.MedicalForm_Name as PersonProfileType_Name
			from v_MedicalFormPerson MFP with(nolock)
			left join v_MedicalForm MF with(nolock) on MF.MedicalForm_id = MFP.MedicalForm_id
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
               Declare
                @Person_id bigint = :Person_id;
                
               Select Person_id, convert(varchar, Diag_setDate, 104) Diag_setDate, Diag_Code, Diag_Name 
                    from onko.fn_GetZNO4Person (@Person_id)
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
			select top 1
				case when PersonPhoneStatus_id = 3 then PP.PersonPhone_Phone else null end as PersonPhone_Phone
			from
				v_PersonPhoneHist PPH (nolock)
				inner join v_PersonPhone PP (nolock) on PP.PersonPhone_id = PPH.PersonPhone_id
			where 
				PPH.Person_id = :Person_id
			order by 
				PPH.PersonPhoneHist_insDT desc
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