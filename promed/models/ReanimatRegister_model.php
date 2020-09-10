<?php defined('BASEPATH') or die ('No direct script access allowed');




/**
 * ReanimatRegister_model - модель для работы с регистром пациентов в реанимации
 *
 * @author Muskat Boris 
 * @version			18.10.2017
 */
//СПРАВОЧНИКИ
//RRW_NSI() - формирование справочников для формы регистра реанимации
//getMorbusType() - Список морбусов
//getRegisterOutCaseType() - Список причин исключения из регистра
//ДЕЙСТВИЯ
//ReanimatRegisterOut($arg) - Исключение из регистра реанимации
//ReanimatRegisterSet($data, $from) - Формирование записи регистра реанимации
//ReanimatRegisterEndRP($data)	 -  Пометка в регистре реанимации окончания реанимационного периода
//ОТЧЁТЫ
//getAliveDead($data) - Сводная информация по началам, окончаниям и исходам реанимационных периодов

class ReanimatRegister_model extends swModel {
	
	/***СПРАВОЧНИКИ*************************************************************************************************************************************/
	/**
     * BOB - 03.11.2017
     * формирование справочников для формы регистра реанимации
	 */
	function RRW_NSI() {

		//Виды шкал
		$ScalesNum = '3,4,5,6';
		$query = "		
				select SC.ScaleType_SysNick, SC.ScaleType_Name, SC.ScaleType_id 
				  from dbo.ScaleType SC
				 where SC.ScaleType_id in (".$ScalesNum.")
	        ";
        $result3 = $this->db->query($query);
		
		//Виды Реанимационных мероприятий
		$query = "		
				select ReanimatActionType_Name,ReanimatActionType_SysNick,ReanimatActionType_id 
				  from dbo.ReanimatActionType with (nolock)
				 order by ReanimatActionType_id
	        ";
        $ReanimatActionType = $this->db->query($query);

		//лекарственных средств используемых при реанимации
		$query = "		
				select ReanimDrugType_Name, ReanimDrugType_id
			      from dbo.ReanimDrugType with (nolock)
				 order by ReanimDrugType_id
	        ";
		$ReanimatDrug = $this->db->query($query);


		//МО, в которых имеются службы реанимации
		$query = "		
				select Lpu_id, Lpu_Nick from dbo.v_Lpu
				where Lpu_id in (select Lpu_id from dbo.v_MedService
				where MedServiceType_id in (select MedServiceType_id from dbo.MedServiceType
				where MedServiceType_SysNick = 'reanimation')
				group by Lpu_id)	
				order by LpuType_id, Lpu_Nick
			";
		$ReanimatLpu = $this->db->query($query);
		

		
		if (( is_object($result3) ) && ( is_object($ReanimatActionType) ) && ( is_object($ReanimatDrug) ))  {
			$ReturnObject = array(	'EvnScaleType' => $result3->result('array'),
									'ReanimatActionType' => $ReanimatActionType->result('array'),
									'ReanimatDrug' => $ReanimatDrug->result('array'),
									'ReanimatLpu' => $ReanimatLpu->result('array'),
								   'Message' => '');	
			//	echo '<pre>' . print_r($ReturnObject, 1) . '</pre>'; //BOB - 25.01.2017			
			return $ReturnObject;
		}
		else {
			return false;
		}
		
		
	}


    /**
     *  Список морбусов
	 * BOB - 18.10.2017
     */ 
    function getMorbusType(){
        $params = array();
       
        $query = "select M.MorbusType_id, M.MorbusType_name
					from dbo.v_MorbusType M with(nolock),
						(select MT.MorbusType_SysNick, max(MT.MorbusType_id)  MorbusType_id
						   from dbo.v_MorbusType MT with(nolock)
						   where MT.MorbusType_SysNick in 
('common','onko','hepa','tub','common_fl','pregnancy','diabetes','acs','nephro','after_transplant','hemolytic_uremic','implants','lung_hypert','Arter_hypert','cnsReab','cardiologyReab','travmReab','eco')
						  group by MT.MorbusType_SysNick) M0
				   where M0.MorbusType_id = M.MorbusType_id
					 and M0.MorbusType_SysNick = M.MorbusType_SysNick
				   order by M.MorbusType_Name";  
        $result = $this->db->query($query, $params);         

        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
     }
    

	
	 /**
     *  Список причин исключения из регистра
	 * BOB - 23.10.2017
     */
    function getRegisterOutCaseType()
    {
 
		
		$query = "select PersonRegisterOutCause_Name, PersonRegisterOutCause_SysNick, PersonRegisterOutCause_id 
					from  PersonRegisterOutCause";  
        $result = $this->db->query($query);         

        //echo getDebugSql($query, $params);
        //exit;
        
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
	}
	
	/***ДЕЙСТВИЯ*****************************************************************************************************************************************************/
	/**
     *  Исключение из регистра реанимации
	 * BOB - 24.10.2017
     */
    function ReanimatRegisterOut($arg)
    {
        //BOB - 23.01.2018
		
		$p = array(
			'ReanimatRegister_id' => $arg['ReanimatRegister_id'],
			'ReanimatRegister_disDate' => $arg['ReanimatRegister_disDate'],
			'PersonRegisterOutCause_id' => $arg['PersonRegisterOutCause_id'],
			'MedPersonal_did' => $arg['MedPersonal_did'],
			'Lpu_did' => $arg['Lpu_did'],
			'pmUser_id' => $arg['pmUser_id']
		);
		
		
		$q = "
				declare
					@ReanimatRegister_id bigint,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
				set @ReanimatRegister_id = :ReanimatRegister_id;
				exec dbo.p_ReanimatRegister_upd
					@ReanimatRegister_id = @ReanimatRegister_id,
					@ReanimatRegister_disDate = :ReanimatRegister_disDate,
					@PersonRegisterOutCause_id = :PersonRegisterOutCause_id,
					@MedPersonal_did = :MedPersonal_did,
					@Lpu_did = :Lpu_did,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				 select @ReanimatRegister_id as ReanimatRegister_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
		
		";
		//echo getDebugSQL($q, $p);exit;
		$result = $this->db->query($q, $p);		
		
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
				
        return $resultArray;
    }

	/**
	 *  Формирование записи регистра реанимации для МАРМ
	 */
	function mSaveReanimatRegister($data) {

		$params = array(
			'Person_id' => $data['Person_id'],
			'EvnSection_id' => $data['EvnSection_id'],
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
			'pmUser_id' => $data['pmUser_id'],
			'MedPersonal_iid' => $data['MedPersonal_id'],
			'Lpu_iid' => $data['Lpu_id'],
		);

		//проверка не находится ли данный пациент в реанимационном регистре
		$rr_data = $this->getFirstRowFromQuery("
			select top 1
				ReanimatRegister_id,
				ReanimatRegister_setDate
			from v_ReanimatRegister (nolock)
			where (1=1) 
				and Person_id = :Person_id
				and ReanimatRegister_disDate is null
			order by ReanimatRegister_id desc
		", $params);

		if (!empty($rr_data)) {
			$params['ReanimatRegister_id'] = $rr_data['ReanimatRegister_id'];
			$params['ReanimatRegister_setDate'] = $rr_data['ReanimatRegister_setDate'];
			$action = 'upd';
		} else {
			$params['ReanimatRegister_id'] = null;
			$params['ReanimatRegister_setDate'] = $this->getFirstResultFromQuery("select dbo.tzGetDate() as dt", array())->format('Y-m-d H:i:s');
			$action = 'ins';
		}


		$query = "
			declare
				@ReanimatRegister_id bigint = :ReanimatRegister_id,
				@Diag_id bigint,
				@MorbusType_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000);

			set @Diag_id = (
				select top 1
					ES.Diag_id 
				from dbo.v_EvnSection ES
				where ES.EvnSection_id = :EvnSection_id  
			)

			set @MorbusType_id = (
				select isnull(
				(
					select top 1 
						MorbusType_id 
					from v_MorbusDiag
					where Diag_id = @Diag_id
					order by MorbusDiag_insDT desc), 1
				) as MorbusType_id
			)

			exec p_ReanimatRegister_{$action}
				@ReanimatRegister_id = @ReanimatRegister_id output,
				@Person_id = :Person_id,
				@MorbusType_id = @MorbusType_id,
				@ReanimatRegister_setDate = :ReanimatRegister_setDate,
				@MedPersonal_iid = :MedPersonal_iid,
				@Lpu_iid = :Lpu_iid,
				@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
				@ReanimatRegister_IsPeriodNow = 2,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message  = @Error_Message output;
								 
			select @ReanimatRegister_id as ReanimatRegister_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->getFirstRowFromQuery($query, $params);
		//echo '<pre>',print_r(getDebugSQL($query, $params)),'</pre>'; die();
		return $result;
	}


	 /**
     *  Формирование записи регистра реанимации
	 * $data - массив необходимых реквизитых
	 * $from - от куда вызвана функция: 1 - из модели реанимационного периода EvnReanimatPeriod_model
	 * BOB - 11.11.2017
     */
	function ReanimatRegisterSet($data, $from)
    {
		
		//возвращаемый объект
		$ReturnObject = array( 'Status' => '',
							   'Message' => '');

		$params = array('Person_id' => $data['Person_id'],
						'EvnSection_id' => $data['EvnSection_id'],
						'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
						'pmUser_id' => $data['pmUser_id']
						);

		
		//проверка не находится ли данный пациент в реанимационном регистре
		$query = "
				select * from dbo.v_ReanimatRegister RR
				 where RR.Person_id = :Person_id
				   and RR.ReanimatRegister_disDate is null
				 order by RR.ReanimatRegister_id desc
		";
        $result = $this->db->query($query, $params);
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');
		
		//ЕСЛИ НЕТ - не находится
		if(count($resultArray) == 0){
			$ReturnObject['Status'] = 'DoneSuccessfully';
			$ReturnObject['Message'] = 'Создана новая запись регистра реанимации.';
		
			$params['MedPersonal_iid'] = $data['MedPersonal_id'];
			$params['Lpu_iid'] = $data['Lpu_id'];
		
			$query = "
				declare
				 @ReanimatRegister_id bigint = null,
				 @ReanimatRegister_setDate datetime = GetDate(),
				 @Diag_id bigint,
				 @MorbusType_id bigint,

				 @Error_Code int = null ,
				 @Error_Message varchar(4000) = null;

				set @Diag_id = (select top 1 ES.Diag_id 
									from dbo.v_EvnSection ES
									where ES.EvnSection_id = :EvnSection_id  )

				set @MorbusType_id = (select isnull(
                                                                    (select top 1 MorbusType_id from v_MorbusDiag
                                                                    where Diag_id = @Diag_id
                                                                    order by MorbusDiag_insDT desc), 1) as MorbusType_id  )

				exec   
				   dbo.p_ReanimatRegister_ins
					@ReanimatRegister_id = @ReanimatRegister_id output,
					@Person_id =  :Person_id,
					@MorbusType_id = @MorbusType_id,
					@ReanimatRegister_setDate = @ReanimatRegister_setDate,
					@MedPersonal_iid = :MedPersonal_iid,
					@Lpu_iid = :Lpu_iid,
					@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
					@ReanimatRegister_IsPeriodNow = 2, -- = 1, да

					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message  = @Error_Message output;

				 
				select @ReanimatRegister_id as ReanimatRegister_id, @Error_Code as Error_Code, @Error_Message as Error_Mess;
			";
			$result = $this->db->query($query, $params);
			if ( !is_object($result) ) return false;

			$resultArray = $result->result('array');
			if ( (empty($resultArray[0]['ReanimatRegister_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			}
			//sql_log_message('error', 'search_model exec query: ', getDebugSql($query, $params));
			
			//$ReturnObject['Message'] .= '~'.$resultArray[0]['ReanimatRegister_id'].'~'.$resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
		}
		else {
			//ЕСЛИ ДА - находится
			$ReturnObject['Status'] = 'DoneSuccessfully';
			$ReturnObject['Message'] = 'Обновлена существующая запись регистра реанимации.';
			
			//изменение записи регистра реанимации ReanimatRegister:
			//установка признака нахождения в реанимации в данный момент ReanimatRegister_IsPeriodNow
			//сохранение кода реанимационного периода EvnReanimatPeriod_id
			
			$params['ReanimatRegister_id'] = $resultArray[0]['ReanimatRegister_id'];
			
			$query = "
				declare
					@ReanimatRegister_id bigint = :ReanimatRegister_id,
					@Diag_id bigint,
					@MorbusType_id bigint,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
					
				set @Diag_id = (select top 1 ES.Diag_id 
									from dbo.v_EvnSection ES
									where ES.EvnSection_id = :EvnSection_id  )

				set @MorbusType_id = (select top 1 MorbusType_id from v_MorbusDiag
						where Diag_id = @Diag_id
						order by MorbusDiag_insDT desc  )

				exec dbo.p_ReanimatRegister_upd
					@ReanimatRegister_id = @ReanimatRegister_id,
					@EvnReanimatPeriod_id = :EvnReanimatPeriod_id,
					@ReanimatRegister_IsPeriodNow = 2, -- = 1, да
					@MorbusType_id = @MorbusType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
					
				 select @ReanimatRegister_id as ReanimatRegister_id, @Error_Code as Error_Code, @Error_Message as Error_Mess;
			
			";
			$result = $this->db->query($query, $params);
			if ( !is_object($result) ) return false;


			$resultArray = $result->result('array');
			if ((empty($resultArray[0]['ReanimatRegister_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
				$ReturnObject['Status'] = 'Oshibka';
				$ReturnObject['Message'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			}

			//sql_log_message('error', 'search_model exec query: ', getDebugSql($query, $params));
			
			//	$ReturnObject['Message'] .= '~'.$resultArray[0]['ReanimatRegister_id'].'~'.$resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			
			
			
			
		}
		return($ReturnObject);
	}
		
	
	 /**
     *  Пометка в регистре реанимации окончания реанимационного периода
	 * BOB - 20.11.2017
     */
    function ReanimatRegisterEndRP($data)
    {
		
		//возвращаемый объект
		$ReturnObject = array( 'Status' => 'DoneSuccessfully',
							   'Message' => 'Обновлена существующая запись регистра реанимации.');

		$params = array('Person_id' => $data['Person_id'],
						'ReanimatRegister_IsPeriodNow' => $data['ReanimatRegister_IsPeriodNow'],
						'pmUser_id' => $data['pmUser_id']
						);
		
		//проверка находится ли данный пациент в реанимационном регистре
		$query = "
				select * from dbo.v_ReanimatRegister RR
				 where RR.Person_id = :Person_id
				   and RR.ReanimatRegister_disDate is null
				 order by RR.ReanimatRegister_id desc
		";
        $result = $this->db->query($query, $params);
		if ( !is_object($result) ) return false;

		$resultArray = $result->result('array');

		//ЕСЛИ НЕТ - не находится
		if(count($resultArray) > 0){
			$params['ReanimatRegister_id'] = $resultArray[0]['ReanimatRegister_id'];
			
			$query = "
				declare
					@ReanimatRegister_id bigint = :ReanimatRegister_id,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;


				exec dbo.p_ReanimatRegister_upd
					@ReanimatRegister_id = @ReanimatRegister_id output,
					@ReanimatRegister_IsPeriodNow = :ReanimatRegister_IsPeriodNow,    --1, -- = 0, нет
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				 select @ReanimatRegister_id as ReanimatRegister_id, @Error_Code as Error_Code, @Error_Message as Error_Mess;
			";
			$result = $this->db->query($query, $params);
			if ( !is_object($result) ) return false;

			if ((empty($resultArray[0]['ReanimatRegister_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Mess']))){
				$Response['success'] = 'false';
				$Response['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Mess'];
			}
		}
		
		return($ReturnObject);
	}

	
	
	
	/***ОТЧЁТЫ*****************************************************************************************************************************************************/	
	 /**
     *  Сводная информация по началам, окончаниям и исходам реанимационных периодов
	 * BOB - 24.10.2017
     */
    function getAliveDead($data)
    {
		// 		echo '<pre> $data2 = '  . print_r($data, 1) . '</pre>'; //BOB - 20.10.2017
		
		$params = array(
			'BeginDate' => $data['BeginDate'],
			'EndDate' => $data['EndDate'],
			'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null,
		);

		$query = "
				declare 
				@BeginDate date = :BeginDate,
				@EndDate date = :EndDate,
				@Lpu_id bigint = :Lpu_id;

				select * from
				dbo.getReanimatAliveDead_Lpu(@BeginDate, @EndDate, @Lpu_id)
				order by 1 

		";
		
		//		$query = "
		//				declare 
		//				@BeginDate date = :BeginDate,
		//				@EndDate date = :EndDate;
		//
		//				select * from
		//				dbo.getReanimatAliveDead(@BeginDate, @EndDate)
		//				order by 1 
		//
		//		";



		
		
        $result = $this->db->query($query, $params);
		if ( !is_object($result) ) return false;

		//sql_log_message('error', 'ReanimatRegister_model / getAliveDead exec query: ', getDebugSql($query, $params));

		$resultArray = $result->result('array');
        
        return $resultArray;
    }
	
    /**
     *  Help my a to hana
     * BOB - 17.03.2018
     */
    function doHelp()
    {
				
        $params = array(
                'BeginDate' => $data['BeginDate'],
                'EndDate' => $data['EndDate'],
        );

        $query = "
            delete from dbo.EvnReanimatPeriod
            where EvnReanimatPeriod_id in (1357156,1357157);

            delete from dbo.Evn
            where Evn_id in (1357156,1357157);

            select count(*) as CNT from v_EvnReanimatPeriod
            where EvnReanimatPeriod_id in (1357156,1357157);
        ";
		

		
        $result = $this->db->query($query, $params);
        if ( !is_object($result) ) return false;

        //sql_log_message('error', 'ReanimatRegister_model / doHelp exec query: ', getDebugSql($query, $params));

        $resultArray = $result->result('array');
    }

	
}
