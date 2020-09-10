<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Класс модели для работы по иммунопрофилактики
 *
 * @package		Common
 * @author		Nigmatullin Tagir (Ufa)
 *
 */

class Vaccine_List_model extends CI_Model {

    	/**
	 * Constructor
	 */
	 
	function __construct()
	{
		parent::__construct();
	}
        /**
	 * Получение списка Вакцин из справочника вакцин
	 * Используется: окно просмотра и редактирования справочника вакцин
         * 
	 */

	public function getVaccineGridDetail($data) {
                
		//Фильтр грида
		$json = isset($data['Filter']) ? trim($data['Filter'], '"') : false;

		$filter_mode = isset($data['Filter']) ? json_decode($json,1) : false; 

		$where = '';
		$join = '';
		
		if (isset($data['Filter'])) {
			log_message('debug', '$json='.$json);
			log_message('debug', '$filter_mode='.var_export($filter_mode, true));
		   
			foreach($filter_mode as $col=>$val){
						
				foreach($val as $v){
					$tempIn[] = "'".$v."'"; 
				}
			
				$temp = implode(',', $tempIn);
			
				if ($col == 'Vaccine_NameInfection')
					$joinMore[] = ' ['.$this->getNameColumn($col).'] in('.$temp.')';
				else
					$whereMore[] = ' ['.$this->getNameColumn($col).'] in('.$temp.')';
		}
			
			$where = (isset($whereMore)) ? ' where ' .implode(' and ', $whereMore) : $where; 
			
			if (isset($joinMore)) {
				$join = "
					join	vac.S_VaccineRelType rel   WITH (NOLOCK) on vac.Vaccine_id = rel.Vaccine_id
					join vac.S_VaccineType tp  WITH (NOLOCK) on rel.VaccineType_id = tp.VaccineType_id and ".implode(' and ', $joinMore); 
									      
			}
        }
                
            $sql = "
			SELECT vac.Vaccine_id
                              ,Vaccine_Name
                              ,Vaccine_SignComb
                              ,Vaccine_Nick
                              ,Vaccine_FullName
                              ,Vaccine_NameInfection
                              ,Vaccine_AgeRange2Sim
                              ,Vaccine_WayPlace
                              ,Vaccine_dose
                              , Vaccine_WayPlace
                             -- ,Vaccine_WayPlace + replace(replace(comment, '&', ', <br />'), 'amp;', '') Vaccine_WayPlace 
                              ,Comment
                          FROM vac.v_Vaccine vac  WITH (NOLOCK)
                          {$join}
                              {$where}
                            order by Vaccine_FullName;
                          ";

	    $result = $this->db->query($sql);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else 
			return false;
		
	} //end getVaccineGridDetail()
 
      /**
	 * Получение списка  из справочника 
	 * Национальный календарь проф. прививок
	 */      

	public function getNCGrid() {

		$sql = "
			SELECT NationalCalendarVac_id, 
                            NationalCalendarVac_vaccineTypeName vaccineTypeName, 
                            NationalCalendarVac_typeName, 
                            NationalCalendarVac_PeriodVacName PeriodVacName, 
                            NationalCalendarVac_comment NationalCalendarVac_AgeRange,
                            NationalCalendarVac_Scheme_id,
                            NationalCalendarVac_SequenceVac SequenceVac,
                            max_SequenceVac,
                            case
                                when isnull(VaccineType_SignScheme, 2) = 1
                                    then 'true'
                                else 'false'  
                            end VaccineType_SignScheme, 
                            case
                                when isnull(VaccineType_SignEmergency, 2) = 1
                                    then 'true'
                                else 'false'  
                            end VaccineType_SignEmergency,
                            case
                                when isnull(VaccineType_SignEpidem, 2) = 1
                                    then 'true'
                                else 'false'  
                            end VaccineType_SignEpidem
                        from vac.v_NationalCalendarVac  WITH (NOLOCK)
                        Where VaccineType_SignNatCal = 1
                          -- vaccineType_id < 100
                        order by vaccineType_id, VaccineAgeBorders_AgeTypeS, VaccineAgeBorders_AgeS, NoVaccinations desc,  
                        NationalCalendarVac_Scheme_id
                          ";
                
             
                
                
                 log_message('debug', 'getNCGrid='.$sql);
                 
                
                 
	    $result = $this->db->query($sql);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else 
			return false;
		
	} //end getNCGrid()
    
	  /**
	 * Получение списка данных по манту, включая план и исполнение
	 * Используется: Карта профилактических прививок
	 */
	public function getPersonVacMantuAll($data) {

		$filter = " (1=1)";
		$inner = "";
		if (isset($data['Id']))
                {
                    $filter .= " and PlanTuberkulin_id = ".$data['Id'];           
                }
                
        if (isset($data['Person_id']))
                {
                    $filter .= " and Person_id = ".$data['Person_id'];           
                }
              
            $sql = "
                SELECT m.JournalMantuAll_id id
								  ,Person_id
								  ,m.idInCurrentTable
									,convert(varchar,  JournalMantuAll_DatePlan, 104)  DatePlan
									, convert(varchar,  JournalMantuAll_DatePurpose, 104) DatePurpose
									,convert(varchar,  JournalMantuAll_DateVac, 104) DateVac
									,JournalMantuAll_Seria  Seria
									,convert(varchar,  JournalMantuAll_Period, 104)  Period
									,JournalMantuAll_Manufacturer Manufacturer
									,MantuReactionType_name
									,JournalMantuAll_ReactDescription ReactDescription
									,convert(varchar,  JournalMantuAll_DateReact, 104) DateReact
									,JournalMantuAll_uch uch
									, fio
									,convert(varchar,  vac_Person_BirthDay, 104) BirthDay
									,vac_Person_sex sex
									, JournalMantuAll_age
									, JournalMantuAll_StatusType_id StatusType_id
									, JournalMantuAll_StatusName Status_Name
									,JournalMantuAll_Sort_id
									,vac_Person_Lpu_Name [Lpu_Name]
								  ,ReactionSize
								  ,Reaction30min
                                                                  , TubDiagnosisType_id
                                                                  , TubDiagnosisType_Name
								
									FROM vac.v_JournalMantuAll m  WITH (NOLOCK)
									WHERE   {$filter}  
										order by JournalMantuAll_Sort_id, JournalMantuAll_DatePurpose, JournalMantuAll_DateVac
                ";
                            
            log_message('debug', 'getPersonVacMantuAll='.$sql);
            
	    $result = $this->db->query($sql);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else 
			return false;
		
	} //end getPersonVacMantuAll()
    
	/**
	 * Получение списка журналов вакцинации
	 */
	
	public function GetListJournals() {

		$sql = "
                        SELECT List_Journals_Id
                            ,Name
                        FROM vac.Vac_List_Journals  WITH (NOLOCK)
                        order by List_Journals_Id";
                
                $result = $this->db->query($sql);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else 
			return false;
                
        } //end List_Journals
        
        
 	/**
	 * Получение списка типов периода
	 */       

	public function GetVaccineTypePeriod($data) {  
		$query = "
			select
                            TipPeriod_id,
                            TipPeriod_name
			from vac.S_VaccineTipPeriod  WITH (NOLOCK)";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end GetVaccineTypePeriod

	
	/**
	* Запуск задания на  формирование плана для пациента
	*/ 
	
	public function formPlanVac($data) {
		$queryParams = array();

		// @DateStart,  -- Начало периода планирования
		// @DateEnd, -- Окончание периода планирования
		// @Person_id = null,  --  Идентификатор пациента  

		// @Error_Code int = null output,  --  Код ошибки
		// @Error_Message varchar(4000) = null output --  Тект ошибки

		//         --       @d1         = convert(varchar, :DateStart, 104),
		//    --        @d2         = convert(varchar, :DateEnd, 104), 
		//   
		$query = "
			declare
				@Error_Code int,
				@Error_Message varchar(4000),
				@VacPresence_id bigint;
				
			exec vac.Vac_FormPlan
				@d1         = :DateStart, 
				@d2         = :DateEnd,                 
				@Pers_id    = :Person_id,
				@Lpu_id    = null,
				@pmUser_id  = :pmUser_id,
				@Error_Code             = @Error_Code output,    -- Код ошибки
				@Error_Message          = @Error_Message output -- Тект ошибки
				
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		   
		";

		$queryParams['DateStart']   = $data['DateStart'];
		$queryParams['DateEnd']     = $data['DateEnd'];
		$queryParams['Person_id']   = $data['Person_id'];
		$queryParams['pmUser_id']   = $data['pmUser_id'];
    
    
		log_message('debug', 'formPlanVac='.$query);
		log_message('debug', 'pmUser_id='.$data['pmUser_id']);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
    	}
  	}
	 /**
	 * Получение списка заданий
	 */ 
  
	public function GetVacListTasks($data) {

		$filter = " (1=1)";
        if (isset($data['Date_View'][0]))
        {
            $filter .= " and FormPlan_runDT >= '". $data['Date_View'][0] ."'";           
        }

        if (isset($data['Date_View'][1]))
        {
            $filter .= " and FormPlan_runDT < dateadd(day, 1, '". $data['Date_View'][1]. "' )";  
                //$data['Date_View'][1]  ."' + 1";  
        }
        
		if ((!isSuperadmin()) and isset($data['Lpu_id']))
        {
            $filter .= " and Lpu_id = ".$data['Lpu_id'];
        }
        $sql = "                       
        	SELECT
        		vacFormPlanRun_id,
        		Lpu_id,
        		Lpu_Nick,
        		--pmUser_id,
        		convert(Varchar, Plan_begDT, 104) as Plan_begDT,
        		convert(Varchar, Plan_endDT, 104) as Plan_endDT,
        		--Params,
        		convert(Varchar, FormPlan_runDT, 120) as FormPlan_runDT,  -- поставлено задание
        		convert(Varchar, FormPlan_begDT, 120) as FormPlan_begDT,  --  начало обработки
        		convert(Varchar, FormPlan_endDT, 120) as FormPlan_endDT,  --  окончание обработки
        		RecStatus,  --  Статус
        		RecStatus_Name, -- Наименование статуса
        		Kol,	--   количество
        		Mode_Name,
        		Comment  --  комментарий
        	FROM
        		vac.v_vacFormPlanRun WITH(NOLOCK)
        	WHERE
        		{$filter}
        	order by FormPlan_runDT desc   
        ";
                   log_message('debug', 'GetVacListTasks='.$sql);
                
                $result = $this->db->query($sql);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else 
			return false;
                
        } //end List_Journals
        
        
        /**
             * Снять задание с выполнения
             */
    public function DelVacRecTasks($data) {
        $errorMsg = 'Ошибка при выполнении запроса к базе данных (Удаление медотвода)';

        $queryParams = array();

        $query = "
    		Declare
    		   @ErrCode bigint = 0,
    		   @ErrMessage varchar(4000) = '';
    	
    		exec vac.p_vacFormPlanRun_remove
    			@vacFormPlanRun_id = :vacFormPlanRun_id,
    			@Error_Code             = @ErrCode output,   -- Код ошибки
    			@Error_Message          = @ErrMessage output -- Тект ошибки

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
    	";

        $queryParams['vacFormPlanRun_id'] = $this->nvl($data['vacFormPlanRun_id']);
        log_message('debug', 'DelVacRecTasks vacFormPlanRun_id=' . $queryParams['vacFormPlanRun_id']);

        $result = $this->db->query($query, $queryParams);

        if (is_object($result)) {
            return $result->result('array');
        } else {
            return array(array('Error_Msg' => $errorMsg, 'success' => false));
        }
    }  //  end DelVacRecTasks
        
    /**
	 * Запуск задания на  формирование плана по ЛПУ
	 */    
	
	public function RunformPlanVac($data) {

		$queryParams = array();
				 
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
				
			exec vac.p_vacFormPlanRun_ins
				@Lpu_id     = :Lpu_id,
				@pmUser_id  = :pmUser_id,
				@Plan_begDT = :Plan_begDT,
				@Plan_endDT = :Plan_endDT,
				@Mode     = :Mode,
				@Org_id    = :Org_id; 

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg
		";

		$queryParams['Lpu_id']          = $data['Lpu_id'];
		$queryParams['pmUser_id']       = $data['pmUser_id'];
		$queryParams['Plan_begDT']      = $data['Plan_begDT'];
		$queryParams['Plan_endDT']      = $data['Plan_endDT'];
		$queryParams['Mode']          = $data['Mode'];
		$queryParams['Org_id']          = $data['Org_id'];
		
		 log_message('debug', 'pmUser_id2='.$queryParams['pmUser_id']);
            
        log_message('debug', 'pmUser_id='.$data['pmUser_id']);
        
		log_message('debug', 'RunformPlanVac='.$query);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
    	}
  	}
	 /**
	 * Запуск задания на  формирование плана по ЛПУ
	 */ 
	public function vacFormReport_5($data) {     
        $queryParams = array();   ;
                
        $query = "
        	Declare
        	    @DateStart datetime = :DateStart,
        	    @DateEnd datetime = :DateEnd,
        	    @Lpu_id bigint = :Lpu_id,
        	    @lpuMedService_id bigint = :lpuMedService_id,
        	    @MedService_id bigint = :MedService_id,
        	    @Organized int = :Organized,
        	    @Param_Territory int,
        	    @LpuBuilding_id bigint = :LpuBuilding_id,
        	    @LpuUnit_id bigint,
        	    @LpuSection_id bigint = :LpuSection_id,
        	    @lpuRegion_id bigint = :LpuRegion_id;

        	SELECT * 
        	FROM vac.fn_reportF5_New (@Param_Territory, @Lpu_id, @lpuMedService_id, @MedService_id, @LpuBuilding_id, @LpuUnit_id,@LpuSection_id, 
				@lpuRegion_id, @DateStart, @DateEnd, @Organized)
			order by vacReportF5_id
        ";

		$queryParams['DateStart']       = $data['DateStart'];
		$queryParams['DateEnd']         = $data['DateEnd'];
		$queryParams['Lpu_id']          = $data['Lpu_id'];
        $queryParams['lpuMedService_id']    = $data['lpuMedService_id'];
        $queryParams['LpuBuilding_id']  = $data['LpuBuilding_id'];
        $queryParams['LpuSection_id']   = $data['LpuSection_id'];
        $queryParams['LpuRegion_id']   = $data['LpuRegion_id'];
        $queryParams['Organized']       = $data['Organized'];
        $queryParams['MedService_id']    = $data['MedService_id'];
        log_message('debug', 'Lpu_id2='.$queryParams['Lpu_id']);

		$result = $this->db->query($query, $queryParams);
                
                log_message('debug', 'vacFormReport_5='.$query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
    	}
  	}
  
	/**
	 * Запуск задания на  формирование плана по ЛПУ
	 */ 
	public function vacFormReport_5Detail($data) {     
       
		$queryParams = array();       
                //log_message('debug', 'vacFormReport_5Detail model');
                //$DateStart = null;
                //$DateEnd;
                

                
                //if (isset($data['DateStart'])) {
                    $DateStart = $data['DateStart'];
                //}
                //if (isset($data['DateEnd'])) {
                    $DateEnd = $data['DateEnd'];
                //}
                //if (isset($data['Lpu_id'])) {
                 //   $Lpu_id = $data['Lpu_id'];
                //}
                    
		if (isset($data['Lpu_id'])) {
			$Lpu_id = $data['Lpu_id'];
		}
		else { $Lpu_id = 'null';}
                
		if (isset($data['LpuBuilding_id'])) {
                    $LpuBuilding_id = $data['LpuBuilding_id'];
                }
		else { $LpuBuilding_id = 'null';}
                
		if (isset($data['LpuUnit_id'])) {
                    $LpuUnit_id = $data['LpuUnit_id'];
                }
		else { $LpuUnit_id = 'null';}
                
		if (isset($data['LpuRegion_id'])) {
			$LpuRegion_id = $data['LpuRegion_id'];
		}
		else { $LpuRegion_id = 'null';}
                
		if (isset($data['LpuSection_id'])) {
                    $LpuSection_id = $data['LpuSection_id'];
                }
		else { $LpuSection_id = 'null';}
		if (isset($data['Organized'])) {
                    $Organized = $data['Organized'];
		}   else { $Organized = 0;}
                
                //if (isset($data['Num_Str'])) {
                    $Num_Str = $data['Num_Str'];
                //}
                    
		if (isset($data['lpuMedService_id'])) {
			$lpuMedService_id = $data['lpuMedService_id'];
		}
		else { $lpuMedService_id = 'null';}
		if (isset($data['MedService_id'])) {
			$MedService_id = $data['MedService_id'];
		}
		else { $MedService_id = 'null';}
                
            log_message('debug', 'lpuMedService_id='.$data['lpuMedService_id']);
            log_message('debug', '$LpuRegion_id='.$LpuRegion_id);
            $query = "
                    SELECT 
                         -- select 
                        Inoculation_id,
                        Person_id, NumStr, convert(Varchar, date_vac, 104) date_vac,  fio, convert(Varchar, BirthDay, 104) BirthDay,
                        lpu_attach_name, LpuRegion_id, LpuRegion_Name, vaccine_id, Vaccine_Name
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
            
                log_message('debug', 'vacFormReport_5Detail='.$query);
		//vac.fn_reportF5_Detail (@Param_Territory, @Lpu_id, @LpuBuilding_id, @LpuUnit_id,@LpuSection_id, 
		//                               @lpuRegion_id, @DateStart, @DateEnd, @Organized, @Num_Str)
		//		$queryParams['DateStart']       = $data['DateStart'];
		//                $queryParams['DateStart']       = $data['DateStart'];
		//		$queryParams['DateEnd']         = $data['DateEnd'];
		//		$queryParams['Lpu_id']          = $data['Lpu_id'];
		//                $queryParams['LpuBuilding_id']  = $data['LpuBuilding_id'];
		//                $queryParams['LpuSection_id']   = $data['LpuSection_id'];
		//                $queryParams['Organized']       = $data['Organized'];
		//                $queryParams['Num_Str']         = $data['Num_Str'];
                
                //log_message('debug', 'LpuBuilding_id='.$queryParams['LpuBuilding_id']);
		/*
		$result = $this->db->query($query, $queryParams);
                
                log_message('debug', 'vacFormReport_5Detail='.$query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
		}
		*/
  
                                       
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
	 * Получение списка вакцин справочника "Наличие вакцин"
	 */ 
	public function GetVacPresence() {

         $filter = ""; 
         $lpu_id = $this->nvl($_SESSION['lpu_id'] );
        if (isset($lpu_id))
                    {
                        $filter = " Where Lpu_id = ".$lpu_id ;   
                        
                    };
					//    {$filter}

        $sql = "
                            SELECT  VacPresence_id
                                ,Vaccine_id
                                ,Vaccine_Name
                                ,VacPresence_Seria Seria
                                ,convert(Varchar, VacPresence_Period, 104) Period
                                ,VacPresence_Manufacturer Manufacturer
                                ,VacPresence_toHave toHave
                                ,VacPresence_NameToHave Name_toHave
                                ,lpu_id
                            FROM vac.v_VacPresence  WITH (NOLOCK)
							 {$filter} 
                            order by Vaccine_Name";
                
				 log_message('debug', 'GetVacPresence='.$sql);
				 
                $result = $this->db->query($sql);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else 
			return false;
                
        } //end GetVacPresence
        
    /**
	 * Получение списка вакцин для комбобокса
	 */ 
	public function getVaccine4Combo() {

		$query = "
			SELECT Vaccine_id
                              ,Vaccine_FullName Vaccine_Name
                              FROM vac.v_Vaccine vac  WITH (NOLOCK)
                        order by Vaccine_FullName      
							 
		";
		
                
              $result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}

                
          }
      
	 /**
	 * Сохранение записи справочника "Наличие вакцин"
	 */ 
	
	public function Vac_Presence_save($data) {
      
		$queryParams = array();
       
		if ($data['action'] == 'add')
		{
			$proc = 'vac.p_Vac_Presence_ins';
           
			$query = "
				 declare
					@Error_Code int,
					@Error_Message varchar(4000),
					@NewVacPresence_id bigint;
				

				exec  $proc

						@Vaccine_id = :Vaccine_id ,
						@Seria = :Seria,
						@Period = :Period,
						@Manufacturer = :Manufacturer,
						@Lpu_id = :Lpu_id,     
						@toHave = :toHave,
						@pmUser_id = :pmUser_id,
						@NewVacPresence_id = @NewVacPresence_id output,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					
						
					 select @NewVacPresence_id as NewVacPresence_id, 
							@Error_Code as Error_Code, @Error_Message as Error_Msg;
				 
			";
       }   
    
		else {
			$proc = 'vac.p_Vac_Presence_upd';
            
			$query = "
				 declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec  $proc

						@Vaccine_id = :Vaccine_id ,
						@Seria = :Seria,
						@Period = :Period,
						@Manufacturer = :Manufacturer,
						@Lpu_id = :Lpu_id,     
						@toHave = :toHave,
						@pmUser_id = :pmUser_id,
						@VacPresence_id = :VacPresence_id; 
						
					 select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
				 
			";
			$queryParams['VacPresence_id']   = $data['VacPresence_id'];
       }
        
        $queryParams['Vaccine_id']     = $data['Vaccine_id'];
        $queryParams['Seria']   = $data['Seria']; 
        $queryParams['Period']   = $data['Period'];
        $queryParams['Manufacturer']   = $data['Manufacturer'];
        $queryParams['toHave']     = $data['toHave'];
        $queryParams['pmUser_id']   = $data['pmUser_id']; 
        $queryParams['Lpu_id']   = $this->nvl($_SESSION['lpu_id'] );

		log_message('debug', 'Vac_Presence_save='.$query);   

		$result = $this->db->query($query, $queryParams);
  
		if ( is_object($result) ) {
			return $result->result('array');
     
		}
		else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
		}
  
   
      
        } //  end Vac_Presence_save


      /**
        * Получаем список прививок
        */
        
	public function GetSprInoculation($data) {

		log_message('debug', '$data[Trunc]='.$data['Trunc']);  
		$filter = '1=1 ';
       
		if (isset($data['Trunc'])) {
			if ($data['Trunc'] == 1) {
                            $filter .= ' and VaccineType_id < 1000'; 
                            //$filter .= ' and VaccineType_SignNatCal = 1 and VaccineType_SignScheme = 1'; 
           }
       }
      
     
            $query = "SElect  * from vac.v_Inoculation4Combo  WITH (NOLOCK)
                where ($filter)
                    order by VaccineType_name";
    

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end GetSprInoculation
        
	/**
	* Получаем список номеров схем
	*/
            
	Public function GetListNumSchemeCombo($data){
              
        $filter = " (1=1)";          
		if (isset($data['VaccineType_id']))
				{
                    $filter .= " and VaccineType_id = ".$data['VaccineType_id'];           
				}
				$query = "     Select distinct vaccineType_id, nc.NationalCalendarVac_Scheme_Num Scheme_Num,
                    nc.NationalCalendarVac_Scheme_Num Scheme_Num2
						FROM vac.v_NationalCalendarVac nc   WITH (NOLOCK)   
                    Where {$filter}		
                            order by NationalCalendarVac_Scheme_Num 
                        ";
              
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end GetListNumSchemeCombo
                   
          
     /**
     * Получаем список типов иммунизации
     */
            
	Public function getVaccineTypeImmunization(){                       
		$query = "SELECT type_id, typeName, type_Nick  FROM vac.v_vacTypeImmunization  WITH (NOLOCK)";             
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	} // end getVaccineTypeImmunization
        
        
    /**
    * Сохранение записи справочника "Национальный календаль проф. прививок"
    */
	public function Vac_saveSprNC($data) {
      
			$queryParams = array();
       
           
			$query = "
		Declare
                @Error_Code int,
                @Error_Message varchar(4000),
                @NewVacPresence_id bigint;
            

            exec  vac.p_S_NationalCalendarVac_ins
                    @NationalCalendarVac_id = :NationalCalendarVac_id ,
                    @VaccineType_id = :VaccineType_id,
                    @SequenceVac = :SequenceVac,
                    @Type_id = :Type_id,
                    @SignPurpose = :SignPurpose,     
                    @Scheme_id = :Scheme_id,
                    @AgeTypeS = :AgeTypeS,
                    @AgeS = :AgeS,
                    @AgeTypeE = :AgeTypeE,
                    @AgeE = :AgeE,
                    @PeriodVac = :PeriodVac,
                    @PeriodVacType = :PeriodVacType,
                    @pmUser_id = :pmUser_id,
                    @Scheme_Num = :Scheme_Num,
                    @Additional = :Additional,
                    @Error_Code = @Error_Code output,
                    @Error_Message = @Error_Message output;

                    
                 select @NewVacPresence_id as NewVacPresence_id, 
                        @Error_Code as Error_Code, @Error_Message as Error_Msg;
            ";		
        
                $queryParams['NationalCalendarVac_id']     = $data['NationalCalendarVac_id'];
                $queryParams['VaccineType_id']   = $data['VaccineType_id'];
                $queryParams['SequenceVac']   = $data['SequenceVac'];
                $queryParams['Type_id']   = $data['Type_id'];
                $queryParams['SignPurpose']     = $data['SignPurpose'];
                $queryParams['Scheme_id']     = $data['Scheme_id'];
                $queryParams['Scheme_Num']     = $data['Scheme_Num'];
                $queryParams['AgeTypeS']   = $data['AgeTypeS']; 
                $queryParams['AgeS']   = $data['AgeS']; 
                $queryParams['AgeTypeE']   = $data['AgeTypeE']; 
                $queryParams['AgeE']   = $data['AgeE']; 
                $queryParams['PeriodVac']   = $data['PeriodVac']; 
                $queryParams['PeriodVacType']   = $data['PeriodVacType']; 
                $queryParams['pmUser_id']   = $data['pmUser_id'];  
                $queryParams['Additional']   = $data['Additional'];
                
                   
               
                //log_message('debug', 'Additional2='.$data['Additional']);   

                $result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
                  return $result->result('array');

                }
		else {
                  return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (назначение прививки)'));
            //    return false;
                }  
        } //  end Vac_saveSprNC
     

	 
	/**
	 * Аналог NVL в оракле
	 */
	 
	public function nvl($var) {
		if (isset($var)) {
			return $var;
		} else {
			return null;
		}
	}
}
