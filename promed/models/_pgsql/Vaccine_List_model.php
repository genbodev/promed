<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Класс модели для работы по иммунопрофилактики
 *
 * @package        Common
 * @author        Nigmatullin Tagir (Ufa)
 *
 */
class Vaccine_List_model extends SwPgModel
{

    /**
     * Получение списка Вакцин из справочника вакцин
     * Используется: окно просмотра и редактирования справочника вакцин
     * @param array $data
     * @return bool|array
     */
    public function getVaccineGridDetail($data)
    {

        //Фильтр грида
        $json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

        $filter_mode = isset($data['Filter']) ? json_decode($json, 1) : false;

        $where = '';
        $join = '';

        if (isset($data['Filter'])) {
            log_message('debug', '$json=' . $json);
            log_message('debug', '$filter_mode=' . var_export($filter_mode, true));

            foreach ($filter_mode as $col => $val) {

                foreach ($val as $v) {
                    $tempIn[] = "'" . $v . "'";
                }

                $temp = implode(',', $tempIn);

                if ($col == 'Vaccine_NameInfection')
                    $joinMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
                else
                    $whereMore[] = ' ' . $this->getNameColumn($col) . ' in(' . $temp . ')';
            }

            $where = (isset($whereMore)) ? ' where ' . implode(' and ', $whereMore) : $where;

            if (isset($joinMore)) {
                $join = "
					join vac.S_VaccineRelType rel on vac.Vaccine_id = rel.Vaccine_id
					join vac.S_VaccineType tp on rel.VaccineType_id = tp.VaccineType_id and " . implode(' and ', $joinMore);
            }
        }

        $sql = "
			SELECT 
				vac.Vaccine_id as \"Vaccine_id\",
				Vaccine_Name as \"Vaccine_Name\",
				Vaccine_SignComb as \"Vaccine_SignComb\",
				Vaccine_Nick as \"Vaccine_Nick\",
				Vaccine_FullName as \"Vaccine_FullName\",
				Vaccine_NameInfection as \"Vaccine_NameInfection\",
				Vaccine_AgeRange2Sim as \"Vaccine_AgeRange2Sim\",
				Vaccine_WayPlace as \"Vaccine_WayPlace\",
				Vaccine_dose as \"Vaccine_dose\",
				Vaccine_WayPlace as \"Vaccine_WayPlace\",
			    Comment as \"Comment\"
		    FROM vac.v_Vaccine vac
				{$join}
				{$where}
			order by Vaccine_FullName;
        ";

        $result = $this->db->query($sql);

        if (is_object($result)) {
            return $result->result('array');
        } else
            return false;

    } //end getVaccineGridDetail()

    /**
     * Получение списка  из справочника
     * Национальный календарь проф. прививок
     */

    public function getNCGrid()
    {
        $sql = "
			select
			    NationalCalendarVac_id as \"NationalCalendarVac_id\", 
                NationalCalendarVac_vaccineTypeName as \"vaccineTypeName\",
                NationalCalendarVac_typeName as \"NationalCalendarVac_typeName\",
                NationalCalendarVac_PeriodVacName as \"PeriodVacName\",
                NationalCalendarVac_comment as \"NationalCalendarVac_AgeRange\",
                NationalCalendarVac_Scheme_id as \"NationalCalendarVac_Scheme_id\",
                NationalCalendarVac_SequenceVac as \"SequenceVac\",
                max_SequenceVac as \"max_SequenceVac\",
                case
                    when coalesce(VaccineType_SignScheme, 2) = 1
                            then 'true'
                    else 'false'  
                end as \"VaccineType_SignScheme\", 
                case
                    when coalesce(VaccineType_SignEmergency, 2) = 1
                        then 'true'
                    else 'false'  
                end as \"VaccineType_SignEmergency\",
                case
                    when coalesce(VaccineType_SignEpidem, 2) = 1
                        then 'true'
                    else 'false'  
                end as \"VaccineType_SignEpidem\"
            from
                vac.v_NationalCalendarVac
            Where VaccineType_SignNatCal = 1
                  -- vaccineType_id < 100
            order by
                vaccineType_id,
                VaccineAgeBorders_AgeTypeS,
                VaccineAgeBorders_AgeS,
                NoVaccinations desc,  
                NationalCalendarVac_Scheme_id
        ";


        log_message('debug', 'getNCGrid=' . $sql);


        $result = $this->db->query($sql);

        if (is_object($result)) {
            return $result->result('array');
        } else
            return false;

    } //end getNCGrid()

    /**
     * Получение списка данных по манту, включая план и исполнение
     * Используется: Карта профилактических прививок
     *
     * @param array $data
     * @return bool|array
     */
    public function getPersonVacMantuAll($data)
    {

        $filter = " (1=1)";
        if (isset($data['Id'])) {
            $filter .= " and PlanTuberkulin_id = " . $data['Id'];
        }

        if (isset($data['Person_id'])) {
            $filter .= " and Person_id = " . $data['Person_id'];
        }

        $sql = "
            SELECT
                m.JournalMantuAll_id as \"id\",
                Person_id as \"Person_id\",
                m.idInCurrentTable as \"idInCurrentTable\",
                to_char  (JournalMantuAll_DatePlan, 'dd.mm.yyyy') as \"DatePlan\",
                to_char  (JournalMantuAll_DatePurpose, 'dd.mm.yyyy') as \"DatePurpose\",
                to_char  (JournalMantuAll_DateVac, 'dd.mm.yyyy') as \"DateVac\",
                JournalMantuAll_Seria as \"Seria\",
                to_char  (JournalMantuAll_Period, 'dd.mm.yyyy') as \"Period\",
                JournalMantuAll_Manufacturer as \"Manufacturer\",
                MantuReactionType_name as \"MantuReactionType_name\",
                JournalMantuAll_ReactDescription as \"ReactDescription\",
                to_char  (JournalMantuAll_DateReact, 'dd.mm.yyyy') as \"DateReact\",
                JournalMantuAll_uch as \"uch\",
                fio as \"fio\",
                to_char  (vac_Person_BirthDay, 'dd.mm.yyyy') as \"BirthDay\",
                vac_Person_sex as \"sex\",
                JournalMantuAll_age as \"JournalMantuAll_age\",
                JournalMantuAll_StatusType_id as \"StatusType_id\",
                JournalMantuAll_StatusName as \"Status_Name\",
                JournalMantuAll_Sort_id as \"JournalMantuAll_Sort_id\",
                vac_Person_Lpu_Name as \"Lpu_Name\",
                ReactionSize as \"ReactionSize\",
                Reaction30min as \"Reaction30min\",
                TubDiagnosisType_id as \"TubDiagnosisType_id\",
                TubDiagnosisType_Name as \"TubDiagnosisType_Name\"					
            FROM
                vac.v_JournalMantuAll m
            WHERE
                {$filter}  
            order by
                JournalMantuAll_Sort_id,
                JournalMantuAll_DatePurpose,
                JournalMantuAll_DateVac
        ";

        log_message('debug', 'getPersonVacMantuAll=' . $sql);

        $result = $this->db->query($sql);

        if (is_object($result)) {
            return $result->result('array');
        } else
            return false;

    } //end getPersonVacMantuAll()

    /**
     * Получение списка журналов вакцинации
     * @return array|bool
     */
    public function GetListJournals()
    {
        $sql = "
            SELECT
                List_Journals_Id as \"List_Journals_Id\",
                Name as \"Name\"
            FROM
                vac.Vac_List_Journals
            order by List_Journals_Id";

        $result = $this->db->query($sql);

        if (is_object($result)) {
            return $result->result('array');
        } else
            return false;

    } //end List_Journals


    /**
     * Получение списка типов периода
     * @param array $data
     * @return bool|array
     */
    public function GetVaccineTypePeriod($data)
    {
        $query = "
			select
                TipPeriod_id as \"TipPeriod_id\",
                TipPeriod_name as \"TipPeriod_name\"
			from
			    vac.S_VaccineTipPeriod
		";

        $result = $this->db->query($query);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    } // end GetVaccineTypePeriod


    /**
     * Запуск задания на  формирование плана для пациента
     * @param $data
     * @return array
     * @throws Exception
     */
    public function formPlanVac($data)
    {
        $queryParams = [];

        $query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from vac.Vac_FormPlan (
				d1 := :DateStart, 
				d2 := :DateEnd,                 
				Pers_id := :Person_id,
				Lpu_id := null,
				pmUser_id := :pmUser_id
				)
		";

        $queryParams['DateStart'] = $data['DateStart'];
        $queryParams['DateEnd'] = $data['DateEnd'];
        $queryParams['Person_id'] = $data['Person_id'];
        $queryParams['pmUser_id'] = $data['pmUser_id'];


        log_message('debug', 'formPlanVac=' . $query);
        log_message('debug', 'pmUser_id=' . $data['pmUser_id']);

        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            throw new Exception('Ошибка при выполнении запроса к базе данных (назначение прививки)');
        }
    }

    /**
     * Получение списка заданий
     */

    public function GetVacListTasks($data)
    {

        $filter = " (1=1)";
        if (isset($data['Date_View'][0])) {
            $filter .= " and cast(FormPlan_runDT as date) >= cast('" . $data['Date_View'][0] . "' as date)";
        }

        if (isset($data['Date_View'][1])) {
            $filter .= " and cast(FormPlan_runDT as date) < dateadd('day', 1, CAST('" . $data['Date_View'][1] . "' as date) )";
            //$data['Date_View'][1]  ."' + 1";
        }

        if ((!isSuperadmin()) and isset($data['Lpu_id'])) {
            $filter .= " and Lpu_id = " . $data['Lpu_id'];
        }

        $sql = "                       
            SELECT
                vacFormPlanRun_id as \"vacFormPlanRun_id\",
                Lpu_id as \"Lpu_id\",
                Lpu_Nick as \"Lpu_Nick\",
                --pmUser_id,
                to_char (Plan_begDT, 'dd.mm.yyyy') as \"Plan_begDT\",
                to_char (Plan_endDT, 'dd.mm.yyyy') as \"Plan_endDT\",
                --Params,
                to_char (FormPlan_runDT, 'yyyy-mm-dd hh24:mm:ss') as \"FormPlan_runDT\",  -- поставлено задание
                to_char (FormPlan_begDT, 'yyyy-mm-dd hh24:mm:ss') as \"FormPlan_begDT\",  --  начало обработки
                to_char (FormPlan_endDT, 'yyyy-mm-dd hh24:mm:ss') as \"FormPlan_endDT\",  --  окончание обработки
                RecStatus as \"RecStatus\",  --  Статус
                RecStatus_Name as \"RecStatus_Name\", -- Наименование статуса
                Kol as \"Kol\",		--   количество
                Mode_Name as \"Mode_Name\",
                Comment as \"Comment\"  --  комментарий
            FROM
                vac.v_vacFormPlanRun
            WHERE
                {$filter}
            order by FormPlan_runDT desc   
        ";
        log_message('debug', 'GetVacListTasks=' . $sql);

        $result = $this->db->query($sql);

        if (is_object($result)) {
            return $result->result('array');
        } else
            return false;

    } //end List_Journals


    /**
     * Снять задание с выполнения
     * @param $data
     * @return array
     * @throws Exception
     */
    public function DelVacRecTasks($data)
    {
        $errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление медотвода)';

        $queryParams = [];

        $query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from vac.p_vacFormPlanRun_remove (
                vacFormPlanRun_id := :vacFormPlanRun_id
            )
        ";

        $queryParams['vacFormPlanRun_id'] = $this->nvl($data['vacFormPlanRun_id']);
        log_message('debug', 'DelVacRecTasks vacFormPlanRun_id=' . $queryParams['vacFormPlanRun_id']);

        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
           throw new Exception($errorMsg);
        }
    }  //  end DelVacRecTasks

    /**
     * Запуск задания на  формирование плана по ЛПУ
     *
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function RunformPlanVac($data)
    {

        $queryParams = [];

        $query = "
			select 
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from vac.p_vacFormPlanRun_ins (
				Lpu_id := :Lpu_id,
				pmUser_id := :pmUser_id,
				Plan_begDT := :Plan_begDT,
				Plan_endDT := :Plan_endDT,
				Mode := :Mode,
				Org_id := :Org_id
				)
		";

        $queryParams['Lpu_id'] = $data['Lpu_id'];
        $queryParams['pmUser_id'] = $data['pmUser_id'];
        $queryParams['Plan_begDT'] = $data['Plan_begDT'];
        $queryParams['Plan_endDT'] = $data['Plan_endDT'];
        $queryParams['Mode'] = $data['Mode'];
        $queryParams['Org_id'] = $data['Org_id'];

        log_message('debug', 'pmUser_id2=' . $queryParams['pmUser_id']);

        log_message('debug', 'pmUser_id=' . $data['pmUser_id']);

        log_message('debug', 'RunformPlanVac=' . $query);

        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
           throw new Exception('Ошибка при выполнении запроса к базе данных (назначение прививки)');
        }
    }

    /**
     * Запуск задания на  формирование плана по ЛПУ
     * @param $data
     * @return array
     * @throws Exception
     */
    public function vacFormReport_5($data)
    {
        $filter = "";
        $queryParams = [];

        $query = "
                    SELECT 
                    	vacReportF5_id as \"vacReportF5_id\",
						vacReportF5_Name as \"vacReportF5_Name\",
						vacReportF5_NumStr as \"vacReportF5_NumStr\",
						vacReportF5_Kol as \"vacReportF5_Kol\",
						VaccineType_id as \"VaccineType_id\" 
                    FROM vac.fn_reportF5_New (
                    	Lpu_id := :Lpu_id,
                    	lpuMedService_id := :lpuMedService_id,
                    	MedService_id := :MedService_id,
                    	LpuBuilding_id := :LpuBuilding_id,
                    	LpuSection_id := :LpuSection_id,
						lpuRegion_id := :LpuRegion_id,
						DateStart := :DateStart,
						DateEnd := :DateEnd,
						Organized := :Organized
						)
					{$filter}
				 	order by vacReportF5_id
                         ";

        $queryParams['DateStart'] = $data['DateStart'];
        $queryParams['DateEnd'] = $data['DateEnd'];
        $queryParams['Lpu_id'] = $data['Lpu_id'];
        $queryParams['lpuMedService_id'] = $data['lpuMedService_id'];
        $queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
        $queryParams['LpuSection_id'] = $data['LpuSection_id'];
        $queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
        $queryParams['Organized'] = $data['Organized'];
        $queryParams['MedService_id'] = $data['MedService_id'];

        log_message('debug', 'Lpu_id2=' . $queryParams['Lpu_id']);

        $result = $this->db->query($query, $queryParams);

        log_message('debug', 'vacFormReport_5=' . $query);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            throw new Exception('Ошибка при выполнении запроса к базе данных (назначение прививки)');
        }
    }

    /**
     * Запуск задания на  формирование плана по ЛПУ
     * @param array $data
     * @return array|bool
     */
    public function vacFormReport_5Detail($data)
    {

        $queryParams = [];

        $DateStart = $data['DateStart'];


        $DateEnd = $data['DateEnd'];


        if (isset($data['Lpu_id'])) {
            $Lpu_id = $data['Lpu_id'];
        } else {
            $Lpu_id = 'null';
        }

        if (isset($data['LpuBuilding_id'])) {
            $LpuBuilding_id = $data['LpuBuilding_id'];
        } else {
            $LpuBuilding_id = 'null';
        }

        if (isset($data['LpuUnit_id'])) {
            $LpuUnit_id = $data['LpuUnit_id'];
        } else {
            $LpuUnit_id = 'null';
        }

        if (isset($data['LpuRegion_id'])) {
            $LpuRegion_id = $data['LpuRegion_id'];
        } else {
            $LpuRegion_id = 'null';
        }

        if (isset($data['LpuSection_id'])) {
            $LpuSection_id = $data['LpuSection_id'];
        } else {
            $LpuSection_id = 'null';
        }
        if (isset($data['Organized'])) {
            $Organized = $data['Organized'];
        } else {
            $Organized = 0;
        }

        //if (isset($data['Num_Str'])) {
        $Num_Str = $data['Num_Str'];
        //}

        if (isset($data['lpuMedService_id'])) {
            $lpuMedService_id = $data['lpuMedService_id'];
        } else {
            $lpuMedService_id = 'null';
        }
        if (isset($data['MedService_id'])) {
            $MedService_id = $data['MedService_id'];
        } else {
            $MedService_id = 'null';
        }

        log_message('debug', 'lpuMedService_id=' . $data['lpuMedService_id']);
        log_message('debug', '$LpuRegion_id=' . $LpuRegion_id);
        $query = "
            SELECT 
                 -- select 
                Inoculation_id as \"Inoculation_id\",
                Person_id as \"Person_id\",
                NumStr as \"NumStr\",
                to_char (date_vac, 'dd.mm.yyyy') as \"date_vac\",
                fio as \"fio\",
                to_char (BirthDay, 'dd.mm.yyyy') as \"BirthDay\",
                lpu_attach_name as \"lpu_attach_name\",
                LpuRegion_id as \"LpuRegion_id\",
                LpuRegion_Name as \"LpuRegion_Name\",
                vaccine_id as \"vaccine_id\",
                Vaccine_Name as \"Vaccine_Name\"
                -- end select
            FROM 
                -- from
                 vac.fn_reportF5_Detail (1, $Lpu_id, $lpuMedService_id, $MedService_id, $LpuBuilding_id, $LpuUnit_id,$LpuSection_id, 
                       $LpuRegion_id, '$DateStart', '$DateEnd', $Organized, $Num_Str)
                 -- end from 
                 order by 
                  -- order by
                    date_vac, fio 
                  -- end order by 
        ";

        log_message('debug', 'vacFormReport_5Detail=' . $query);

        $count_sql = getCountSQLPH($query);
        log_message('debug', '$count_sql=' . $count_sql);


        if (isset($data['start']) && $data['start'] >= 0 && isset($data['limit']) && $data['limit'] >= 0) {
            $query = getLimitSQLPH($query, $data['start'], $data['limit']);
            log_message('debug', 'getLimitSQLPH=' . $query);
        }

        $res = $this->db->query($query, $queryParams);

        // определение общего количества записей
        $count_res = $this->db->query($count_sql, $queryParams);
        if (is_object($count_res)) {
            $cnt_arr = $count_res->result('array');
            $count = $cnt_arr[0]['cnt'];
            log_message('debug', 'countSQL=' . $count);
        } else
            return false;

        if (is_object($res)) {
            $response = $res->result('array');
            $response[] = ['__countOfAllRows' => $count];
            return $response;
        } else {
            return false;
        }
    }

    /**
     * Получение списка вакцин справочника "Наличие вакцин"
     */
    public function GetVacPresence()
    {
        $filter = "";
        $lpu_id = $this->nvl($_SESSION['lpu_id']);
        if (isset($lpu_id)) {
            $filter = " Where Lpu_id = " . $lpu_id;

        };

        $sql = "
            SELECT
                VacPresence_id as \"VacPresence_id\",
                Vaccine_id as \"Vaccine_id\",
                Vaccine_Name as \"Vaccine_Name\",
                VacPresence_Seria as \"Seria\",
                to_char (VacPresence_Period, 'dd.mm.yyyy') as \"Period\",
                VacPresence_Manufacturer as \"Manufacturer\",
                VacPresence_toHave as \"toHave\",
                VacPresence_NameToHave as \"Name_toHave\",
                lpu_id as \"lpu_id\"
            FROM
                vac.v_VacPresence
                {$filter} 
            order by Vaccine_Name
        ";

        log_message('debug', 'GetVacPresence=' . $sql);

        $result = $this->db->query($sql);

        if (is_object($result)) {
            return $result->result('array');
        } else
            return false;

    } //end GetVacPresence

    /**
     * Получение списка вакцин для комбобокса
     */
    public function getVaccine4Combo()
    {
        $query = "
			SELECT
			    Vaccine_id as \"Vaccine_id\",
			    Vaccine_FullName as \"Vaccine_Name\"
            FROM
                vac.v_Vaccine
            order by Vaccine_FullName
		";


        $result = $this->db->query($query);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Сохранение записи справочника "Наличие вакцин"
     *
     * @param $data
     * @return array
     * @throws Exception
     */
	public function Vac_Presence_save($data) 
	{
		$queryParams = [];
       
		if ($data['action'] == 'add') {
			$proc = 'vac.p_Vac_Presence_ins';
			
			$query = "				
				select
					NewVacPresence_id as \"NewVacPresence_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$proc} (
					Vaccine_id := :Vaccine_id,
					Seria := :Seria,
					Period := :Period,
					Manufacturer := :Manufacturer,
					Lpu_id := :Lpu_id,     
					toHave := :toHave,
					pmUser_id := :pmUser_id
				)
			";
		} else {
			$proc = 'vac.p_Vac_Presence_upd';
			
			$query = "
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from {$proc} (
					Vaccine_id := :Vaccine_id,
					Seria := :Seria,
					Period := :Period,
					Manufacturer := :Manufacturer,
					Lpu_id := :Lpu_id,
					toHave := :toHave,
					pmUser_id := :pmUser_id,
					VacPresence_id := :VacPresence_id
				)
			";
			$queryParams['VacPresence_id'] = $data['VacPresence_id'];
		}
        
		$queryParams['Vaccine_id'] = $data['Vaccine_id'];
		$queryParams['Seria'] = $data['Seria'];
		$queryParams['Period'] = $data['Period'];
		$queryParams['Manufacturer'] = $data['Manufacturer'];
		$queryParams['toHave'] = $data['toHave'];
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$queryParams['Lpu_id'] = $this->nvl($_SESSION['lpu_id']);
		
		log_message('debug', 'Vac_Presence_save='.$query);
		
		$result = $this->db->query($query, $queryParams);
		
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
		}
	} //  end Vac_Presence_save


    /**
     * Получаем список прививок
     * @param array $data
     * @return bool
     */
    public function GetSprInoculation($data)
    {

        log_message('debug', '$data[Trunc]=' . $data['Trunc']);
        $filter = '1=1 ';

        if (isset($data['Trunc'])) {
            if ($data['Trunc'] == 1) {
                $filter .= ' and VaccineType_id < 1000';
                //$filter .= ' and VaccineType_SignNatCal = 1 and VaccineType_SignScheme = 1';
            }
        }


        $query = "
            select
                VaccineType_id as \"VaccineType_id\",
                VaccineType_Name as \"VaccineType_Name\"
            from
                vac.v_Inoculation4Combo
            where
                ($filter)
            order by VaccineType_name
        ";


        $result = $this->db->query($query);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    } // end GetSprInoculation

    /**
     * Получаем список номеров схем
     * @param array $data
     * @return bool
     */
    Public function GetListNumSchemeCombo($data)
    {

        $filter = " (1=1)";
        if (isset($data['VaccineType_id'])) {
            $filter .= " and VaccineType_id = " . $data['VaccineType_id'];
        }
        $query = "
            Select distinct
                vaccineType_id as \"vaccineType_id\",
                nc.NationalCalendarVac_Scheme_Num as \"Scheme_Num\",
                nc.NationalCalendarVac_Scheme_Num as \"Scheme_Num2\"
            FROM
                vac.v_NationalCalendarVac nc   
            Where
                {$filter}		
            order by NationalCalendarVac_Scheme_Num 
        ";

        $result = $this->db->query($query);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    } // end GetListNumSchemeCombo


    /**
     * Получаем список типов иммунизации
     */

    Public function getVaccineTypeImmunization()
    {
        $query = "
            SELECT
                type_id as \"type_id\",
                typeName as \"typeName\",
                type_Nick as \"type_Nick\"
            FROM
                vac.v_vacTypeImmunization
        ";
        $result = $this->db->query($query);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    } // end getVaccineTypeImmunization


    /**
     * Сохранение записи справочника "Национальный календаль проф. прививок"
     * @param array $data
     * @return array
     */
    public function Vac_saveSprNC($data)
    {

        $queryParams = [];


        $query = "
			select 
				NationalCalendarVac_id as \"NewVacPresence_id\", 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
            from vac.p_S_NationalCalendarVac_ins (
				NationalCalendarVac_id := :NationalCalendarVac_id ,
				VaccineType_id := :VaccineType_id,
				SequenceVac := :SequenceVac,
				Type_id := :Type_id,
				SignPurpose := :SignPurpose,     
				Scheme_id := :Scheme_id,
				AgeTypeS := :AgeTypeS,
				AgeS := :AgeS,
				AgeTypeE := :AgeTypeE,
				AgeE := :AgeE,
				PeriodVac := :PeriodVac,
				PeriodVacType := :PeriodVacType,
				pmUser_id := :pmUser_id,
				Scheme_Num := :Scheme_Num,
				Additional := :Additional
				)
            ";

        $queryParams['NationalCalendarVac_id'] = $data['NationalCalendarVac_id'];
        $queryParams['VaccineType_id'] = $data['VaccineType_id'];
        $queryParams['SequenceVac'] = $data['SequenceVac'];
        $queryParams['Type_id'] = $data['Type_id'];
        $queryParams['SignPurpose'] = $data['SignPurpose'];
        $queryParams['Scheme_id'] = $data['Scheme_id'];
        $queryParams['Scheme_Num'] = $data['Scheme_Num'];
        $queryParams['AgeTypeS'] = $data['AgeTypeS'];
        $queryParams['AgeS'] = $data['AgeS'];
        $queryParams['AgeTypeE'] = $data['AgeTypeE'];
        $queryParams['AgeE'] = $data['AgeE'];
        $queryParams['PeriodVac'] = $data['PeriodVac'];
        $queryParams['PeriodVacType'] = $data['PeriodVacType'];
        $queryParams['pmUser_id'] = $data['pmUser_id'];
        $queryParams['Additional'] = $data['Additional'];


        //log_message('debug', 'Additional2='.$data['Additional']);

        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');

        } else {
            throw new Exception('Ошибка при выполнении запроса к базе данных (назначение прививки)');
        }
    } //  end Vac_saveSprNC


    /**
     * Аналог NVL в оракле
     * @param mixed $var
     * @return mixed|null
     */
    public function nvl($var)
    {
        if (isset($var)) {
            return $var;
        } else {
            return null;
        }
    }
}
