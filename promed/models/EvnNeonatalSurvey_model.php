<?php defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Description of EvnNeonatalSurvey_model
 *
 * @author Muskat Boris 
 * @version			24.01.2020
 */
//function getParamsENSWindow($arg) - Формирование параметров для окна Наблюдение состояния младенца
//function getEvnNeonatalSurvey($data) - Формирование данных Наблюдение состояния младенца
//function EvnNeonatalSurvey_Save($data) - Сохранение в БД данных конкретного реанимационного наблюдения состояния младенца - 
//function EvnNeonatalSurvey_Delete() -	удаление Наблюдения состояния младенца
//function getSedationMedicat()- извлечение параметров медикаментозной седации



require_once('EvnAbstract_model.php');



class EvnNeonatalSurvey_model extends EvnAbstract_model {

	 /**
     *  Формирование параметров для окна редактирования реанимационного периода
	 * BOB - 25.11.2017
     */
    function getParamsENSWindow($arg)
    {
		if ($arg['LpuSection_id'] == null) {
			//в случае реанимации
			$query = "
			select 
				EPS.Lpu_id,
				EPS.EvnPS_id,
				EPS.EvnPS_NumCard,
				ES.EvnSection_id,
				convert(varchar(10), ES.EvnSection_setDate  ,104) as EvnSection_setDate,
				ES.EvnSection_setTime,
				LS.LpuSection_id,
				LS.LpuSection_Name,
				D.Diag_id,
				D.Diag_Code, 
				D.Diag_Name,
				MS.MedService_id,
				MS.MedService_Name,
				convert(varchar(10), Evn.Evn_setDate  ,104) as EvnParent_setDate,
				convert(varchar(10), Evn.Evn_disDate  ,104) as EvnParent_disDate
			
			from v_EvnPS EPS with (nolock)
			inner join v_Evn Evn with (nolock) on Evn.Evn_rid = EPS.EvnPS_id
			left join v_EvnSection ES with (nolock) on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_disDT is null
			left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
			left join dbo.Diag D with(nolock) on D.Diag_id = isnull(ES.Diag_id, EPS.Diag_pid)
			left join dbo.EvnReanimatPeriod ERP   with(nolock) on ERP.Evn_id = Evn.Evn_id
			left join dbo.MedService MS  with(nolock) on  MS.MedService_id = ERP.MedService_id
			where Evn.Evn_id = :EvnNeonatalSurvey_pid
			";
		}else{
			//в случае специфики
			$query = "
				select
					EPS.Lpu_id,
					EPS.EvnPS_id,
					EPS.EvnPS_NumCard,
					ES.EvnSection_id,
					convert(varchar(10), ES.EvnSection_setDate  ,104) as EvnSection_setDate,
					ES.EvnSection_setTime,
					LS.LpuSection_id,
					LS.LpuSection_Name,
					D.Diag_id,
					D.Diag_Code,
					D.Diag_Name

				from v_EvnPS EPS with (nolock)
				inner join v_Evn Evn with (nolock) on Evn.Evn_id = EPS.EvnPS_id
				left join v_EvnSection ES with (nolock) on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_disDT is null
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = ES.LpuSection_id
				left join dbo.Diag D with(nolock) on D.Diag_id = isnull(ES.Diag_id, EPS.Diag_pid)
				where Evn_id = :EvnNeonatalSurvey_pid
			";
		}
        $result = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
		
		if ( !is_object($result) ) return false;
		$par_data = $result->result('array');

		//признак наличия "Поступления"
		$query = "
			select count(*) as CNT 
			from v_EvnNeonatalSurvey
			where EvnNeonatalSurvey_pid = :EvnNeonatalSurvey_pid
			and ReanimStageType_id = 1
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));		
		if ( !is_object($result) ) return false;
		$par_data[0]['EntryExist'] = $result->result('array')[0]['CNT'];


		//дата время последнего наблюдения для предустановки дат нового наблюдения
		$where_ = $arg['action'] != 'add' ? ' and EvnNeonatalSurvey_id < :EvnNeonatalSurvey_id' : ''; // для случая открытия на редактирование существующей записи
		$params = array(
			'EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid'],
			'EvnNeonatalSurvey_id' => $arg['EvnNeonatalSurvey_id']
		);

		$query = "
			select top 1
			case
				when  ReanimStageType_id = 2 then convert(varchar(10), EvnNeonatalSurvey_disDate  ,104) 
				else convert(varchar(10), EvnNeonatalSurvey_setDate  ,104) 
			end Previous_setDate,
			case
				when  ReanimStageType_id = 2 then EvnNeonatalSurvey_disTime
				else  EvnNeonatalSurvey_setTime
			end Previous_setTime,
			case  ReanimStageType_id
				when 2 then convert(varchar(20), EvnNeonatalSurvey_disDT, 120)
				else convert(varchar(20), EvnNeonatalSurvey_setDT, 120)
			end as EvnNeonatalSurvey_disDT
			from v_EvnNeonatalSurvey with (nolock)
			where EvnNeonatalSurvey_pid = :EvnNeonatalSurvey_pid".$where_."
			order by EvnNeonatalSurvey_setDT desc
		";
		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) return false;

		$par_data[0]['Previous_setDate'] = null;
		$par_data[0]['Previous_setTime'] = null;
		$EvnNeonatalSurvey_disDT = '3999-01-01 00:00:00';  //BOB - 09.04.2020
		if (count($result->result('array')) > 0) {
			$par_data[0]['Previous_setDate'] = $result->result('array')[0]['Previous_setDate'];
			$par_data[0]['Previous_setTime'] = $result->result('array')[0]['Previous_setTime'];	
			$EvnNeonatalSurvey_disDT = $result->result('array')[0]['EvnNeonatalSurvey_disDT'];	 //BOB - 09.04.2020
		}

		if ($arg['LpuSection_id'] == null) {
			//список врачей службы реанимации
			//!!! работать будет только для АРМ реаниматолога (обращение к РП - v_EvnReanimatPeriod),т.е. универсальности нет, из других АРМ-ов придётся дорабатывать
			$query = "
				select MSM.MedPersonal_id,  MPC.Person_id, LEFT(PS.PersonSurName_SurName, 1) + LOWER(SUBSTRING(PS.PersonSurName_SurName, 2, 100)) + ' ' + SUBSTRING(PS.PersonFirName_FirName, 1, 1) + '.' + SUBSTRING(PS.PersonSecName_SecName, 1, 1)  + '.' as EvnReanimatCondition_Doctor, PS.PersonSurName_SurName, PS.PersonFirName_FirName, PS.PersonSecName_SecName
				  from  v_EvnReanimatPeriod ERP
				  inner join MedServiceMedPersonal MSM on ERP.MedService_id = MSM.MedService_id
				  inner join MedPersonalCache MPC on MSM.MedPersonal_id = MPC.MedPersonal_id
				  inner join PersonState PS on MPC.Person_id = PS.Person_id
				 where ERP.EvnReanimatPeriod_id = :EvnNeonatalSurvey_pid
				   and MSM.MedServiceMedPersonal_endDT is NULL
				 group by MSM.MedPersonal_id,  MPC.Person_id, PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
				 order by PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
			";
			$result = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
		}else{
			$query = "
				select MSF.MedPersonal_id,  MSF.Person_id, LEFT(PS.PersonSurName_SurName, 1) + LOWER(SUBSTRING(PS.PersonSurName_SurName, 2, 100)) + ' ' + SUBSTRING(PS.PersonFirName_FirName, 1, 1) + '.' + SUBSTRING(PS.PersonSecName_SecName, 1, 1)  + '.' as EvnReanimatCondition_Doctor, PS.PersonSurName_SurName, PS.PersonFirName_FirName, PS.PersonSecName_SecName
				from v_MedPersonal MP with (nolock)
					inner join v_MedStaffFact MSF with (nolock) on MSF.MedPersonal_id = MP.MedPersonal_id and MP.Lpu_id = :Lpu_id
					inner join PersonState PS with (nolock) on PS.Person_id = MP.Person_id
				where MSF.LpuSection_id=:LpuSection_id and MSF.WorkData_endDate is null or MSF.WorkData_endDate>getdate()
				order by PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
			";
			$result = $this->db->query($query, array('LpuSection_id' => $arg['LpuSection_id'], 'Lpu_id' => $arg['Lpu_id']));
		}
		
		if ( !is_object($result) ) return false;

		$MS_doctors = $result->result('array');

		/*регулярное наблюдение состояния**********************************************************************************************************************************/		
		
		//Стороны
		$query = "		
			  select SideType_id, SideType_Name, SideType_SysNick
				from dbo.SideType  with (nolock)
			   order by SideType_id
	        ";
		$ReanimConditParam_SideType = $this->db->query($query)->result('array');

		/*получение данных шкал и мероприятий для нового наблюдения*******************************************************************************************************/ 
		//для новых записей наблюдения
		$Add_Params = array();
		if ($arg['action'] == 'add'){
			//pSOFA 
			$query = "
				select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
				from dbo.v_EvnScale ES with (nolock) 
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'psofa'
				order by ES.EvnScale_setDT desc	";	
			$psofa = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($psofa) ) return false;
			$Add_Params['psofa'] = $psofa->result('array');
			//PELOD-2 
			$query = "
				select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
				from dbo.v_EvnScale ES with (nolock) 
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'pelod'
				order by ES.EvnScale_setDT desc	";	
			$pelod = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($pelod) ) return false;
			$Add_Params['pelod'] = $pelod->result('array');
			//Glasgow 
			$query = "
				select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
				from dbo.v_EvnScale ES with (nolock) 
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick in ('glasgow','glasgow_ch','glasgow_neonat')
				order by ES.EvnScale_setDT desc	";	
			$glasgow = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($glasgow) ) return false;
			$Add_Params['glasgow'] = $glasgow->result('array');
			//COMFORT 
			$query = "
				select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
				from dbo.v_EvnScale ES with (nolock) 
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'comfort'
				order by ES.EvnScale_setDT desc	";	
			$comfort = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($comfort) ) return false;
			$Add_Params['comfort'] = $comfort->result('array');
			//N-PASS 
			$query = "
				select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
				from dbo.v_EvnScale ES with (nolock) 
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'npass'
				order by ES.EvnScale_setDT desc	";	
			$npass = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($npass) ) return false;
			$Add_Params['npass'] = $npass->result('array');
			//NIPS 
			$query = "
				select top 1 ES.EvnScale_id, ES.EvnScale_setDT, ES.EvnScale_disDT, ES.EvnScale_Result, ES.ScaleType_SysNick 
				from dbo.v_EvnScale ES with (nolock) 
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'nips'
				order by ES.EvnScale_setDT desc	";	
			$nips = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($nips) ) return false;
			$Add_Params['nips'] = $nips->result('array');

			//ИВЛ //BOB - 09.04.2020
			$params['EvnNeonatalSurvey_disDT'] = $EvnNeonatalSurvey_disDT;
			$query = "
				select top 1 IVLParameter_id,IVLParameter_Apparat,IVLP.IVLRegim_id,IVLR.IVLRegim_SysNick, IVLR.IVLRegim_Name, IVLParameter_TubeDiam,IVLParameter_FiO2,IVLParameter_FrequSet,IVLParameter_VolInsp,IVLParameter_PressInsp,IVLParameter_PressSupp,
					IVLParameter_FrequTotal,IVLParameter_VolTe,IVLParameter_VolE,IVLParameter_TinTet,IVLParameter_VolTrig,IVLParameter_PressTrig,IVLParameter_PEEP,IVLParameter_PcentMinVol,IVLParameter_TwoASVMax,
					IVLParameter_VolTi,IVLParameter_Peak,IVLParameter_MAP,IVLParameter_Tins,IVLParameter_FlowMax,IVLParameter_FlowMin,IVLParameter_deltaP,IVLParameter_Other  
				 from v_EvnReanimatAction ERA with (nolock)
				 inner join v_IVLParameter IVLP with (nolock) on ERA.EvnReanimatAction_id = IVLP.EvnReanimatAction_id
				 inner join IVLRegim IVLR  with (nolock) on IVLP.IVLRegim_id = IVLR.IVLRegim_id
				 where EvnReanimatAction_pid = :EvnNeonatalSurvey_pid 
				   and ReanimatActionType_SysNick = 'lung_ventilation'
				   and (EvnReanimatAction_disDT >= :EvnNeonatalSurvey_disDT or EvnReanimatAction_disDT is null  )
				 order by ERA.EvnReanimatAction_id desc			";	
			$IVLParameter = $this->db->query($query, $params);
			sql_log_message('error', 'lung_ventilation exec : ', getDebugSql($query, $params));
			if ( !is_object($IVLParameter) ) return false;
			$Add_Params['IVL'] = $IVLParameter->result('array');
		}

		
		$ReturnObject = array(
			'par_data' => $par_data,
			'MS_doctors' => $MS_doctors,
			'SideType' => $ReanimConditParam_SideType,
			'Add_Params' => $Add_Params
		);	
		//		echo '<pre>' . print_r($ReturnObject, 1) . '</pre>'; //BOB - 20.10.2017
		return $ReturnObject;
	}

	 /**
     *  Формирование данных Наблюдение состояния младенца
	 * BOB - 27.01.2020
     */
    function getEvnNeonatalSurvey($data)
    {
		$query = "
			select	ENS.EvnNeonatalSurvey_id, 
				ENS.EvnNeonatalSurvey_pid, 
				ENS.Person_id, 
				ENS.PersonEvn_id, 
				ENS.Server_id,    
				convert(varchar(10), ENS.EvnNeonatalSurvey_setDate  ,104) as EvnNeonatalSurvey_setDate,
				ENS.EvnNeonatalSurvey_setTime as EvnNeonatalSurvey_setTime,
				convert(varchar(10), ENS.EvnNeonatalSurvey_disDate  ,104) as EvnNeonatalSurvey_disDate,
				ENS.EvnNeonatalSurvey_disTime as EvnNeonatalSurvey_disTime,
				cast(ENS.ReanimStageType_id as int) ReanimStageType_id, 
				ENS.ReanimConditionType_id,
				ReanimArriveFromType_id,
				EvnNeonatalSurvey_Conclusion,
				EvnNeonatalSurvey_Doctor
			from dbo.v_EvnNeonatalSurvey ENS with (nolock)
			where ENS.EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
        $result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id']));		
		if ( !is_object($result) ) return false;
		$EvnNeonatalSurvey = $result->result('array')[0];

		$query = "
			select NSPT.NeonatalSurveyParamType_Name,	NSPT.NeonatalSurveyParamType_SysNick,	NSPT.AnswerType_id, A_T.AnswerType_Name,
					NSP.EvnNeonatalSurvey_id, NSP.NeonatalSurveyParamType_id, NSP.NeonatalSurveyParam_Is, NSP.NeonatalSurveyParam_varchar, NSP.NeonatalSurveyParam_Sprav, 
					NSP.NeonatalSurveyParam_SpravName, NSP.NeonatalSurveyParam_numeric, NSP.NeonatalSurveyParam_int, NSP.NeonatalSurveyParam_Radio
			from v_NeonatalSurveyParam NSP with (nolock)
			inner join dbo.v_NeonatalSurveyParamType NSPT with (nolock) on NSPT.NeonatalSurveyParamType_id = NSP.NeonatalSurveyParamType_id
			inner join dbo.v_AnswerType A_T  with (nolock)  on A_T.AnswerType_id = NSPT.AnswerType_id
			where NSP.EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
			order by NSPT.NeonatalSurveyParamType_id
		";
        $result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id']));
		
		if ( !is_object($result) ) return false;

		$NeonatalSurveyParam_Ish = $result->result('array');

		$NeonatalSurveyParam = array ();

		foreach ($NeonatalSurveyParam_Ish as $Raw) {
			switch ($Raw['AnswerType_id']) {
				case 1:   		//Да/Нет
					$NeonatalSurveyParam[$Raw['NeonatalSurveyParamType_SysNick']] = $Raw['NeonatalSurveyParam_Is'];
					break;
				case 2:			//Текст
					$NeonatalSurveyParam[$Raw['NeonatalSurveyParamType_SysNick']] = $Raw['NeonatalSurveyParam_varchar'];
					break;
				case 3:			//Справочник !!! возможно понадобится ещё и название справочника из NeonatalSurveyParam_SpravName
					$NeonatalSurveyParam[$Raw['NeonatalSurveyParamType_SysNick']] = $Raw['NeonatalSurveyParam_Sprav'];
					break;				
				case 8:   		//Радиогруппа
					$NeonatalSurveyParam[$Raw['NeonatalSurveyParamType_SysNick']] = $Raw['NeonatalSurveyParam_Radio'];
					break;
				case 12:		//Целое число
					$NeonatalSurveyParam[$Raw['NeonatalSurveyParamType_SysNick']] = $Raw['NeonatalSurveyParam_int'];
					break;
				case 13:		//Число с плавоющей запятой
					$NeonatalSurveyParam[$Raw['NeonatalSurveyParamType_SysNick']] = $Raw['NeonatalSurveyParam_numeric'];
					break;				
			}


		};


		//Аускультативно
		$query = "
			select BreathAuscultative_id
				,EvnNeonatalSurvey_id
				,BA.SideType_id
				,ST.SideType_SysNick
				,BreathAuscultative_Auscult
				,BreathAuscultative_AuscultTxt
				,BreathAuscultative_Rale
				,BreathAuscultative_IsPleuDrain
				,2 as BA_RecordStatus 
			from v_BreathAuscultative BA with(nolock)
			inner join SideType ST  with(nolock) on BA.SideType_id = ST.SideType_id
			where BA.EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
			order by SideType_id
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id']));		
		if ( !is_object($result) ) return false;
		$BreathAuscultative = $result->result('array');

		//Травмы
		$query = "
			select NeonatalTrauma_id,
				EvnNeonatalSurvey_id,
				NeonatalTrauma_Fracture,
				NeonatalTrauma_IsRigidMusclClavicle,
				NeonatalTrauma_IsLossLimbMov,
				NeonatalTrauma_IsLimitMov,
				NeonatalTrauma_IsCrepitation,
				NeonatalTrauma_IsPain,
				NeonatalTrauma_IsPseudoParalys,
				2 as NT_RecordStatus
			from v_NeonatalTrauma
			where EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id']));		
		if ( !is_object($result) ) return false;
		$NeonatalTrauma = $result->result('array');
		
		$ReturnObject = array(
			'EvnNeonatalSurvey' => $EvnNeonatalSurvey,
			'NeonatalSurveyParam' => $NeonatalSurveyParam,
			'BreathAuscultative' => $BreathAuscultative,
			'NeonatalTrauma' => $NeonatalTrauma
		);	
		//		echo '<pre>' . print_r($ReturnObject, 1) . '</pre>'; //BOB - 20.10.2017
		return $ReturnObject;
    }
	
	
	/**
	 * BOB - 29.01.2020
	 * Сохранение в БД данных конкретного реанимационного наблюдения состояния младенца - 
	 */
	function EvnNeonatalSurvey_Save($data) {
		

		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');


		$result = null;

		$data['EvnNeonatalSurvey_setDate'] .= ' '.$data['EvnNeonatalSurvey_setTime'].':00';
		if(($data['EvnNeonatalSurvey_disDate'] == '') || $data['EvnNeonatalSurvey_disTime'] == ''){
			$data['EvnNeonatalSurvey_disDate'] = null;
		}
		else {
			$data['EvnNeonatalSurvey_disDate'] =$data['EvnNeonatalSurvey_disDate']." ".$data['EvnNeonatalSurvey_disTime'].":00";
		}



		$params = array(
			'EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id'],						
			'EvnNeonatalSurvey_pid' => $data['EvnNeonatalSurvey_pid'],
			'EvnNeonatalSurvey_rid' => $data['EvnNeonatalSurvey_rid'],
			'Lpu_id' => $data['Lpu_id'],
			'PersonEvn_id' => $data['PersonEvn_id'],
			'Server_id' => isset($data['Server_id']) ? $data['Server_id'] : null,

			'EvnNeonatalSurvey_setDT' => isset($data['EvnNeonatalSurvey_setDate']) ? $data['EvnNeonatalSurvey_setDate'] : null,
			'EvnNeonatalSurvey_disDT' => isset($data['EvnNeonatalSurvey_disDate']) ? $data['EvnNeonatalSurvey_disDate'] : null,

			'ReanimStageType_id' => $data['ReanimStageType_id'],
			'ReanimArriveFromType_id' => isset($data['ReanimArriveFromType_id']) ? $data['ReanimArriveFromType_id'] : null,
			'ReanimConditionType_id' => isset($data['ReanimConditionType_id']) ? $data['ReanimConditionType_id'] : null,

			'EvnNeonatalSurvey_Conclusion' => isset($data['EvnNeonatalSurvey_Conclusion']) ? $data['EvnNeonatalSurvey_Conclusion'] : null,
			'EvnNeonatalSurvey_Doctor' => isset($data['EvnNeonatalSurvey_Doctor']) ? $data['EvnNeonatalSurvey_Doctor'] : null,	
			'pmUser_id' => $data['pmUser_id']
		);

		if($params['EvnNeonatalSurvey_id'] == null) {

			$query = "

				declare
					@EvnNeonatalSurvey_id bigint = null,
					@Error_Code int,
					@Error_Message varchar(4000); 
				exec   dbo.p_EvnNeonatalSurvey_ins
					@EvnNeonatalSurvey_id = @EvnNeonatalSurvey_id output,
					@EvnNeonatalSurvey_pid = :EvnNeonatalSurvey_pid, 
					@EvnNeonatalSurvey_rid = :EvnNeonatalSurvey_rid, 
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id, 
					@EvnNeonatalSurvey_setDT = :EvnNeonatalSurvey_setDT, 
					@EvnNeonatalSurvey_disDT = :EvnNeonatalSurvey_disDT, 

					@ReanimStageType_id = :ReanimStageType_id,
					@ReanimArriveFromType_id= :ReanimArriveFromType_id,
					@ReanimConditionType_id = :ReanimConditionType_id,
					@EvnNeonatalSurvey_Conclusion = :EvnNeonatalSurvey_Conclusion,
					@EvnNeonatalSurvey_Doctor = :EvnNeonatalSurvey_Doctor,

					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;

				select @EvnNeonatalSurvey_id as EvnNeonatalSurvey_id, @Error_Code as Error_Code, @Error_Message as Error_Message;

			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnNeonatalSurvey_ins exec query: ', getDebugSql($query, $params));	
			

			if ( !is_object($result) )
				return false;
			}
		else {
			$query = "
				declare
					@EvnNeonatalSurvey_id bigint = :EvnNeonatalSurvey_id,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
		
		
				exec p_EvnNeonatalSurvey_upd  					
					@EvnNeonatalSurvey_id = @EvnNeonatalSurvey_id output,
					@EvnNeonatalSurvey_pid = :EvnNeonatalSurvey_pid,
					@EvnNeonatalSurvey_rid = :EvnNeonatalSurvey_rid,
					@Lpu_id = :Lpu_id, 
					@Server_id = :Server_id, 
					@PersonEvn_id = :PersonEvn_id, 
					@EvnNeonatalSurvey_setDT = :EvnNeonatalSurvey_setDT, 
					@EvnNeonatalSurvey_disDT = :EvnNeonatalSurvey_disDT, 
		
					@ReanimStageType_id = :ReanimStageType_id,
					@ReanimArriveFromType_id = :ReanimArriveFromType_id,
					@ReanimConditionType_id = :ReanimConditionType_id,
					@EvnNeonatalSurvey_Conclusion = :EvnNeonatalSurvey_Conclusion,
					@EvnNeonatalSurvey_Doctor = :EvnNeonatalSurvey_Doctor,
		
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
		
				select @EvnNeonatalSurvey_id as EvnNeonatalSurvey_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnNeonatalSurvey_upd exec query: ', getDebugSql($query, $params));					
			if ( !is_object($result) )
				return false;
		}		
		$EvnScaleResult = $result->result('array');
		if (!(($EvnScaleResult[0]['EvnNeonatalSurvey_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Message'] == null))){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
			return $Response;
		} else {
			$Response['EvnNeonatalSurvey_id'] = $EvnScaleResult[0]['EvnNeonatalSurvey_id'];
		}

		//Удаление всех NeonatalSurveyParam  с EvnNeonatalSurvey_id - 
		//это вынесено перед if наличия списка параметров поскольку: 
		//если он пуст, то и не должно быть NeonatalSurveyParam БД
		//если что-то есть, то удаляю перед формированием новых
		//это подойдёт и для создания нового EvnNeonatalSurvey и для редактирования старого
		$query = "
			delete from NeonatalSurveyParam where EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, $params);			
		sql_log_message('error', 'delete query: ', getDebugSql($query, $params));	

		//сохранение нового списка параметров
		$NeonatalSurveyParam_Arr = isset($data['NeonatalSurveyParam']) ? json_decode($data['NeonatalSurveyParam'], true) : [];
		if (count($NeonatalSurveyParam_Arr) > 0) {

			//Формирую Словарь типов параметров наблюдения за младенцами	
			$query = "
				select NeonatalSurveyParamType_id,NeonatalSurveyParamType_SysNick,AnswerType_id
				from dbo.NeonatalSurveyParamType with (nolock)
			";
			$result = $this->db->query($query, $params);			
			if ( !is_object($result) )
				return false;
			$EvnScaleResult = $result->result('array');
			//Словарь типов параметров наблюдения за младенцами	
			$dicNeonatalSurveyParamType = [];
			foreach($EvnScaleResult as $Raw){
				$dicNeonatalSurveyParamType[$Raw['NeonatalSurveyParamType_SysNick']] = array('NeonatalSurveyParamType_id' => $Raw['NeonatalSurveyParamType_id'], 'AnswerType_id' => $Raw['AnswerType_id']);
			}
			//log_message('debug', 'BOB_EvnNeonatalSurvey_Save_$dicNeonatalSurveyParamType = '.print_r($dicNeonatalSurveyParamType, 1));
			//echo '<pre>'.'BOB_EvnNeonatalSurvey_Save_$NeonatalSurveyParam_Arr = ' . print_r($NeonatalSurveyParam_Arr, 1) . '</pre>'; 

			$quer_params = array(
				'EvnNeonatalSurvey_id' => $Response['EvnNeonatalSurvey_id'],
				'pmUser_id' => $data['pmUser_id'],
			);
			$query = "
			insert into dbo.NeonatalSurveyParam with (ROWLOCK) (EvnNeonatalSurvey_id, NeonatalSurveyParamType_id, NeonatalSurveyParam_Is, NeonatalSurveyParam_varchar, NeonatalSurveyParam_Sprav, NeonatalSurveyParam_SpravName,
					NeonatalSurveyParam_numeric, NeonatalSurveyParam_int, NeonatalSurveyParam_Radio, pmUser_insID, pmUser_updID, NeonatalSurveyParam_insDT, NeonatalSurveyParam_updDT, NeonatalSurveyParam_Deleted)
			values ";
			// echo '<pre>'.'BOB_$query_1 = ' . print_r($query, 1) . '</pre>'; 


			foreach (array_keys($NeonatalSurveyParam_Arr) as $key) {

				$NeonatalSurveyParamType_id = $dicNeonatalSurveyParamType[$key]['NeonatalSurveyParamType_id'];
				$NeonatalSurveyParam_Is = 'null';
				$NeonatalSurveyParam_varchar = 'null';
				$NeonatalSurveyParam_Sprav = 'null';
				$NeonatalSurveyParam_Radio = 'null';
				$NeonatalSurveyParam_int = 'null';
				$NeonatalSurveyParam_numeric = 'null';

				$ParValue = '';
				switch ($dicNeonatalSurveyParamType[$key]['AnswerType_id']) {
					case 1:   		//Да/Нет
						$NeonatalSurveyParam_Is = $NeonatalSurveyParam_Arr[$key]; 
						break; 
					case 2:			//Текст
						$NeonatalSurveyParam_varchar = "'".str_replace("'","", $NeonatalSurveyParam_Arr[$key])."'"; 
						break;
					case 3:			//Справочник !!! возможно понадобится ещё и название справочника из NeonatalSurveyParam_SpravName
						$NeonatalSurveyParam_Sprav = $NeonatalSurveyParam_Arr[$key];
						if ($NeonatalSurveyParam_Sprav == null) $NeonatalSurveyParam_Sprav = "null";
						break;				
					case 8:   		//Радиогруппа
						$NeonatalSurveyParam_Radio = "'".$NeonatalSurveyParam_Arr[$key]."'"; 
						break;
					case 12:		//Целое число
						$NeonatalSurveyParam_int = $NeonatalSurveyParam_Arr[$key];
						if ($NeonatalSurveyParam_int == null) $NeonatalSurveyParam_int = "null";
						break;
					case 13:		//Число с плавоющей запятой
						$NeonatalSurveyParam_numeric = $NeonatalSurveyParam_Arr[$key]; 
						break;				
				}


				$query .= "
				(:EvnNeonatalSurvey_id, ".$NeonatalSurveyParamType_id.", ".$NeonatalSurveyParam_Is.", ".$NeonatalSurveyParam_varchar.", ".$NeonatalSurveyParam_Sprav.", null,
					".$NeonatalSurveyParam_numeric.", ".$NeonatalSurveyParam_int.", ".$NeonatalSurveyParam_Radio.", :pmUser_id, :pmUser_id, GetDate(),GetDate(), 1),";
			}

			$query = substr($query, 0, (strlen($query)-1));
			$result = $this->db->query($query, $quer_params);
			sql_log_message('error', 'p_NeonatalSurveyParam_ins: ', getDebugSql($query, $quer_params));		

		}

		//СОХРАНЕНИЕ АУСКУЛЬТАТИВНОГО
		$BreathAuscultative_Arr = isset($data['BreathAuscultative']) ? json_decode($data['BreathAuscultative'], true) : [];
		if (count($BreathAuscultative_Arr) > 0) {

			foreach ($BreathAuscultative_Arr as $BreathAuscult) {

				$BreathAuscult['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;
				$BreathAuscult['EvnNeonatalSurvey_id'] =  $Response['EvnNeonatalSurvey_id'];
				
				//log_message('debug', 'BOB_0'.print_r($BreathAuscult, 1));
			
				switch ($BreathAuscult['BA_RecordStatus']) {
					case 0:
						//добавление нового 
						$query = "
							declare
								@BreathAuscultative_id bigint = null,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;
							exec dbo.p_BreathAuscultative_ins
								@BreathAuscultative_id = @BreathAuscultative_id output,
								@EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id,
								@SideType_id = :SideType_id,
								@BreathAuscultative_Auscult = :BreathAuscultative_Auscult,
								@BreathAuscultative_AuscultTxt = :BreathAuscultative_AuscultTxt,
								@BreathAuscultative_Rale = :BreathAuscultative_Rale,
								@BreathAuscultative_RaleTxt = null,
								@BreathAuscultative_IsPleuDrain = :BreathAuscultative_IsPleuDrain,
								@BreathAuscultative_PleuDrainTxt = null,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;

							select @BreathAuscultative_id as BreathAuscultative_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
						$result = $this->db->query($query, $BreathAuscult);
						sql_log_message('error', 'p_BreathAuscultative_ins exec query: ', getDebugSql($query, $BreathAuscult));
						break;
					case 2:
						//изменение 
						$query = "
							declare
								@BreathAuscultative_id bigint = :BreathAuscultative_id,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;
							exec   dbo.p_BreathAuscultative_upd
								@BreathAuscultative_id = @BreathAuscultative_id output,
								@EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id,
								@SideType_id = :SideType_id,
								@BreathAuscultative_Auscult = :BreathAuscultative_Auscult,
								@BreathAuscultative_AuscultTxt = :BreathAuscultative_AuscultTxt,
								@BreathAuscultative_Rale = :BreathAuscultative_Rale,
								@BreathAuscultative_RaleTxt = null,
								@BreathAuscultative_IsPleuDrain = :BreathAuscultative_IsPleuDrain,
								@BreathAuscultative_PleuDrainTxt = null,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							select @BreathAuscultative_id as BreathAuscultative_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
						$result = $this->db->query($query, $BreathAuscult);
						sql_log_message('error', 'p_BreathAuscultative_upd exec query: ', getDebugSql($query, $BreathAuscult));
						
						break;
				}
				$BreathAuscultResult = $result->result('array');
				//log_message('debug', 'BOB_0'.print_r($BreathAuscultResult, 1));
				//log_message('debug', 'BOB_0'.print_r((!(($BreathAuscultResult[0]['BreathAuscultative_id']) && ($BreathAuscultResult[0]['Error_Code'] == null) && ($BreathAuscultResult[0]['Error_Message'] == null))), 1));


				if (!(($BreathAuscultResult[0]['BreathAuscultative_id']) && ($BreathAuscultResult[0]['Error_Code'] == null) && ($BreathAuscultResult[0]['Error_Message'] == null))){
					$Response['success'] = 'false';
					$Response['Error_Msg'] = $BreathAuscultResult[0]['Error_Code'].' '.$BreathAuscultResult[0]['Error_Message'];
					return $Response;
				}				
			}
		}

		//Аускультативно
		$query = "
			select BreathAuscultative_id
				,EvnNeonatalSurvey_id
				,BA.SideType_id
				,ST.SideType_SysNick
				,BreathAuscultative_Auscult
				,BreathAuscultative_AuscultTxt
				,BreathAuscultative_Rale
				,BreathAuscultative_IsPleuDrain
				,2 as BA_RecordStatus 
			from v_BreathAuscultative BA with(nolock)
			inner join SideType ST  with(nolock) on BA.SideType_id = ST.SideType_id
			where BA.EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
			order by SideType_id
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $Response['EvnNeonatalSurvey_id']));		
		if ( !is_object($result) ) return false;
		$BreathAuscultative = $result->result('array');
		
		$Response['BreathAuscultative'] = $BreathAuscultative;


		//СОХРАНЕНИЕ ТРАВМЫ
		$NeonatalTrauma_Arr = isset($data['NeonatalTrauma']) ? json_decode($data['NeonatalTrauma'], true) : [];
		if (count($NeonatalTrauma_Arr) > 0) {

			foreach ($NeonatalTrauma_Arr as $NeonatalTrauma) {

				$NeonatalTrauma['pmUser_id'] = isset($data['pmUser_id']) ? $data['pmUser_id'] : null;
				$NeonatalTrauma['EvnNeonatalSurvey_id'] =  $Response['EvnNeonatalSurvey_id'];

				switch ($NeonatalTrauma['NT_RecordStatus']) {
					case 0:
						//добавление нового 
						$query = "
							declare
								@NeonatalTrauma_id bigint = null,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;
							exec dbo.p_NeonatalTrauma_ins
								@NeonatalTrauma_id = @NeonatalTrauma_id output,
								@EvnNeonatalSurvey_id  = :EvnNeonatalSurvey_id,
							
								@NeonatalTrauma_Fracture  = :NeonatalTrauma_Fracture,
								@NeonatalTrauma_IsRigidMusclClavicle = :NeonatalTrauma_IsRigidMusclClavicle,
								@NeonatalTrauma_IsLossLimbMov = :NeonatalTrauma_IsLossLimbMov,
								@NeonatalTrauma_IsLimitMov = :NeonatalTrauma_IsLimitMov,
								@NeonatalTrauma_IsCrepitation = :NeonatalTrauma_IsCrepitation,
								@NeonatalTrauma_IsPain = :NeonatalTrauma_IsPain,
								@NeonatalTrauma_IsPseudoParalys = :NeonatalTrauma_IsPseudoParalys,
							
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							
							select @NeonatalTrauma_id as NeonatalTrauma_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
						$result = $this->db->query($query, $NeonatalTrauma);
						sql_log_message('error', 'p_NeonatalTrauma_ins exec query: ', getDebugSql($query, $NeonatalTrauma));
						break;
					case 2:
						//изменение 
						$query = "
							declare
								@NeonatalTrauma_id bigint = :NeonatalTrauma_id,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;
							exec dbo.p_NeonatalTrauma_upd
								@NeonatalTrauma_id = @NeonatalTrauma_id output,
								@EvnNeonatalSurvey_id  = :EvnNeonatalSurvey_id,
							
								@NeonatalTrauma_Fracture  = :NeonatalTrauma_Fracture,
								@NeonatalTrauma_IsRigidMusclClavicle = :NeonatalTrauma_IsRigidMusclClavicle,
								@NeonatalTrauma_IsLossLimbMov = :NeonatalTrauma_IsLossLimbMov,
								@NeonatalTrauma_IsLimitMov = :NeonatalTrauma_IsLimitMov,
								@NeonatalTrauma_IsCrepitation = :NeonatalTrauma_IsCrepitation,
								@NeonatalTrauma_IsPain = :NeonatalTrauma_IsPain,
								@NeonatalTrauma_IsPseudoParalys = :NeonatalTrauma_IsPseudoParalys,
							
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output;
							
							select @NeonatalTrauma_id as NeonatalTrauma_id, @Error_Code as Error_Code, @Error_Message as Error_Message;
						";
						$result = $this->db->query($query, $NeonatalTrauma);
						sql_log_message('error', 'p_NeonatalTrauma_upd exec query: ', getDebugSql($query, $NeonatalTrauma));						
						break;
					case 3:
						//удаление
						$query = "
							declare
								@NeonatalTrauma_id bigint = :NeonatalTrauma_id,
								@Error_Code int = null,
								@Error_Message varchar(4000) = null;					
							exec dbo.p_NeonatalTrauma_del
								@NeonatalTrauma_id = @NeonatalTrauma_id,
								--@IsRemove  = 2,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message  = @Error_Message output;					
							select @NeonatalTrauma_id as NeonatalTrauma_id, @Error_Code as Error_Code, @Error_Message as Error_Message;		
						";
						$result = $this->db->query($query, $NeonatalTrauma);
						sql_log_message('error', 'p_NeonatalTrauma_del exec query: ', getDebugSql($query, $NeonatalTrauma));
						
						break;
				}
				$BreathAuscultResult = $result->result('array');

				if (!(($BreathAuscultResult[0]['NeonatalTrauma_id']) && ($BreathAuscultResult[0]['Error_Code'] == null) && ($BreathAuscultResult[0]['Error_Message'] == null))){
					$Response['success'] = 'false';
					$Response['Error_Msg'] = $BreathAuscultResult[0]['Error_Code'].' '.$BreathAuscultResult[0]['Error_Message'];
					return $Response;
				}				
			}
		}
		
		//Травмы
		$query = "
			select NeonatalTrauma_id,
				EvnNeonatalSurvey_id,
				NeonatalTrauma_Fracture,
				NeonatalTrauma_IsRigidMusclClavicle,
				NeonatalTrauma_IsLossLimbMov,
				NeonatalTrauma_IsLimitMov,
				NeonatalTrauma_IsCrepitation,
				NeonatalTrauma_IsPain,
				NeonatalTrauma_IsPseudoParalys,
				2 as NT_RecordStatus
			from v_NeonatalTrauma
			where EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $Response['EvnNeonatalSurvey_id']));		
		if ( !is_object($result) ) return false;
		$NeonatalTrauma = $result->result('array');
		
		$Response['NeonatalTrauma'] = $NeonatalTrauma;


		return $Response;


	}

	 /**
     *  удаление Наблюдения состояния младенца]
	 * BOB - 20.02.2020
     */
    function EvnNeonatalSurvey_Delete($data)
    {
		$Response = array (
			'success' => 'true',
			'Error_Msg' => '');
		
		$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь

		$queryParams = array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id'],
							  'pmUser_id' => $pmUser_id);
		
		//АУСКУЛЬТАТИВНО					  
		$query = "
			select BreathAuscultative_id from BreathAuscultative
			where EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ){
			$Response['success'] = false;
			$Response['Error_Msg'] = 'Ошибка поиска записей АУСКУЛЬТАТИВНО';
			return $Response;
		}
		$BreathAuscultative = $result->result('array');
		
		foreach ($BreathAuscultative as $BreathAuscult) {
			$queryParams['BreathAuscultative_id'] = $BreathAuscult['BreathAuscultative_id'];
			
			$query = "
				declare
				 @BreathAuscultative_id bigint = :BreathAuscultative_id,
				 @Error_Code int = null,
				 @Error_Message varchar(4000) = null;
				exec dbo.p_BreathAuscultative_del
				 @BreathAuscultative_id = @BreathAuscultative_id,
				 @pmUser_id = :pmUser_id,
				 @Error_Code = @Error_Code output,
				 @Error_Message  = @Error_Message output;

				select @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			
			$result = $this->db->query($query, $queryParams);
			sql_log_message('error', 'EvnNeonatalSurvey_model p_BreathAuscultative_del query: ', getDebugSql($query, $queryParams));
			if ( !is_object($result) )
				return false;
		}
		
		//ТРАВМЫ
		$query = "
			select NeonatalTrauma_id from dbo.NeonatalTrauma
			where EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, $queryParams);
		if ( !is_object($result) ){
			$Response['success'] = false;
			$Response['Error_Msg'] = 'Ошибка поиска записей ТРАВМЫ';
			return $Response;
		}
		$NeonatalTrauma = $result->result('array');

		foreach ($NeonatalTrauma as $NeonatalTrau) {
			$queryParams['NeonatalTrauma_id'] = $NeonatalTrau['NeonatalTrauma_id'];
			
			$query = "
				declare
					@NeonatalTrauma_id bigint = :NeonatalTrauma_id,
					@Error_Code int = null,
					@Error_Message varchar(4000) = null;
				exec dbo.p_NeonatalTrauma_del
					@NeonatalTrauma_id = @NeonatalTrauma_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message  = @Error_Message output;
				
				select @Error_Code as Error_Code, @Error_Message as Error_Message;		
			";
			
			$result = $this->db->query($query, $queryParams);
			sql_log_message('error', 'EvnNeonatalSurvey_model p_NeonatalTrauma_del query: ', getDebugSql($query, $queryParams));
			if ( !is_object($result) )
				return false;
		}
		
		//ПАРАМЕТРЫ НАБЛЮДЕНИЯ
		$query = "
			delete from NeonatalSurveyParam where EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, $queryParams);			

		//основная запись наблюдения младенца
		$query = "
			declare
				@EvnNeonatalSurvey_id bigint = :EvnNeonatalSurvey_id,
				@Error_Code int = null,
				@Error_Message varchar(4000) = null; 
			exec dbo.p_EvnNeonatalSurvey_del
				@EvnNeonatalSurvey_id = @EvnNeonatalSurvey_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message  = @Error_Message output;
			
			select @Error_Code as Error_Code, @Error_Message as Error_Message;		
	   ";
		
		$result = $this->db->query($query, $queryParams);
		sql_log_message('error', 'EvnNeonatalSurvey_model p_EvnNeonatalSurvey_del query: ', getDebugSql($query, $queryParams));
		
		if ( !is_object($result) ){
			$Response['success'] = false;
			$Response['Error_Msg'] = 'Ошибка удаления наблюдений младенцев';
			return $Response;
		}

		$EvnScaleResult = $result->result('array');

		if (($EvnScaleResult[0]['Error_Code'] != null) || ($EvnScaleResult[0]['Error_Message'] != null)){
			$Response['success'] = 'false';
			$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Message'];
		}
		return $Response;
    }

	 /**
     * извлечение параметров медикаментозной седации
	 * BOB - 13.03.2020
     */
    function getSedationMedicat($data)
    {
		$data['EvnNeonatalSurvey_setDate'] .= ' '.$data['EvnNeonatalSurvey_setTime'].':00';
		if(($data['EvnNeonatalSurvey_disDate'] == '') || $data['EvnNeonatalSurvey_disTime'] == ''){
			$data['EvnNeonatalSurvey_disDate'] = null;
		}
		else {
			$data['EvnNeonatalSurvey_disDate'] =$data['EvnNeonatalSurvey_disDate']." ".$data['EvnNeonatalSurvey_disTime'].":00";
		}
		$query = "
			declare @EvnReanimatAction_id bigint = null, 
			@EvnNeonatalSurvey_setDate datetime = :EvnNeonatalSurvey_setDate,
			@EvnNeonatalSurvey_disDate datetime = :EvnNeonatalSurvey_disDate;

			select top 1 @EvnReanimatAction_id = EvnReanimatAction_id 
			  from v_EvnReanimatAction with (nolock)
			 where EvnReanimatAction_pid = :EvnNeonatalSurvey_pid
			   and ReanimatActionType_SysNick = :ReanimatActionType_SysNick
			   and EvnReanimatAction_setDT >= @EvnNeonatalSurvey_setDate
			   and EvnReanimatAction_setDT <= isnull(@EvnNeonatalSurvey_disDate, GetDate())
			order by EvnReanimatAction_id desc

			select RDT.ReanimDrugType_id, ReanimDrugType_Name, ReanimDrug_Dose,	ReanimDrug_Unit
			  from v_ReanimDrug RD with (nolock)
			 inner join ReanimDrugType RDT  with (nolock) on RDT.ReanimDrugType_id = RD.ReanimDrugType_id
			 where EvnReanimatAction_id = @EvnReanimatAction_id
		";
		$result = $this->db->query($query, $data);		
		if ( !is_object($result) ) return false;
		$SedationMedicat = $result->result('array');
		return $SedationMedicat;
    }

	/**
	 * Получение списка наблюдений
	 */
	function loadNeonatalSurveyGrid($data) {

		$where = '';
		if ($data['EvnPS_id'])
			$where .= 'where EVN.Evn_pid=EVN.Evn_rid and EVN.Evn_pid=:EvnPS_id';

		if ($data['Person_id'])
			$where .= 'where EVN.Evn_pid=EVN.Evn_rid and EVN.Evn_pid in (select EvnPS0.EvnPS_id from v_EvnPS EvnPS0 with(nolock) where EvnPS0.person_id=:Person_id)';

		$query = "
			select
				NS.EvnNeonatalSurvey_id,
				convert(varchar(10), EVN.Evn_setDT  ,104) as Evn_setD,
				convert(varchar(5), EVN.Evn_setDT  ,24) as Evn_setT,
				wei.PersonWeight_Weight,
				tem.NeonatalSurveyParam_numeric as PersonTemperature,
				BreathFrequency.NeonatalSurveyParam_int as BreathFrequency,
				HeartFrequency.NeonatalSurveyParam_int as HeartFrequency,
				RCT.ReanimConditionType_Name,
				(case when CheckReact.NeonatalSurveyParam_radio = 1 then 'адекватная'
					when CheckReact.NeonatalSurveyParam_radio = 2 then 'снижена'
					when CheckReact.NeonatalSurveyParam_radio = 3 then 'беспокойство'
					when CheckReact.NeonatalSurveyParam_radio = 4 then CheckReactUser.NeonatalSurveyParam_varchar
					else ''
				end) CheckReact,
				(case when MuscleTone.NeonatalSurveyParam_radio = 1 then 'атония'
					when MuscleTone.NeonatalSurveyParam_radio = 2 then 'гипотонус'
					when MuscleTone.NeonatalSurveyParam_radio = 3 then 'гипертонус'
					when MuscleTone.NeonatalSurveyParam_radio = 4 then 'нормотонус'
					when MuscleTone.NeonatalSurveyParam_radio = 5 then MuscleToneUser.NeonatalSurveyParam_varchar
					else ''
				end) MuscleTone,
				(case when Oedemata.NeonatalSurveyParam_radio = 00 then ''
					when Oedemata.NeonatalSurveyParam_radio = 11 then 'пастозность'
					when Oedemata.NeonatalSurveyParam_radio = 12 then 'пастозность'
					when Oedemata.NeonatalSurveyParam_radio = 13 then 'пастозность'
					when Oedemata.NeonatalSurveyParam_radio = 14 then 'пастозность'
					when Oedemata.NeonatalSurveyParam_radio = 20 then 'склерема'
					when Oedemata.NeonatalSurveyParam_radio = 30 then 'позиционные'
					when Oedemata.NeonatalSurveyParam_radio = 40 then OedemataUser.NeonatalSurveyParam_varchar
					else ''
				end) Oedemata,
				(case when HeartTones.val1 = 1 then 'ритмичный'
					when HeartTones.val1 = 2 then 'тахиаритмия'
					when HeartTones.val1 = 3 then 'брадиаритмия'
					when HeartTones.val1 = 4 then 'дополнительный тон'
					else ''
				end) HeartTones1,
				(case when HeartTones.val2 = 1 then 'ясные'
					when HeartTones.val2 = 2 then 'приглушены'
					when HeartTones.val2 = 3 then 'глухие'
					else ''
				end) HeartTones2,
				(case when RemainUmbilCord.NeonatalSurveyParam_radio = 0 then ''
					when RemainUmbilCord.NeonatalSurveyParam_radio = 1 then 'в скобе'
					when RemainUmbilCord.NeonatalSurveyParam_radio = 2 then 'сухой'
					when RemainUmbilCord.NeonatalSurveyParam_radio = 3 then 'отслаивается'
					when RemainUmbilCord.NeonatalSurveyParam_radio = 4 then 'катетер в вене пуповины'
					when RemainUmbilCord.NeonatalSurveyParam_radio = 5 then RemainUmbilCordUser.NeonatalSurveyParam_varchar
					else ''
				end) RemainUmbilCord,
				(case when UmbilicWound.NeonatalSurveyParam_radio = 0 then ''
					when UmbilicWound.NeonatalSurveyParam_radio = 1 then 'сухая'
					when UmbilicWound.NeonatalSurveyParam_radio = 2 then 'эпителизируется'
					when UmbilicWound.NeonatalSurveyParam_radio = 3 then 'катетер в вене пуповины'
					when UmbilicWound.NeonatalSurveyParam_radio = 4 then UmbilicWoundUser.NeonatalSurveyParam_varchar
					else ''
				end) UmbilicWound,
				PS.Person_SurName,
				PS.Person_FirName,
				PS.Person_SecName,
				PS.Person_BirthDay,
				PS.Sex_id as Sex_Code,
				EVN.EVN_pid as EvnSection_pid,
				EvnSection.LpuSection_id
			from EvnNeonatalSurvey NS with(nolock)
				inner join dbo.Evn EVN with(nolock) on EVN.evn_id=NS.EvnNeonatalSurvey_id
				inner join v_PersonState PS with(nolock) on PS.Person_id=EVN.Person_id
				outer apply(
					select top 1 MSF.LpuSection_id
					from v_EvnSection ES with(nolock)
						inner join v_MedStaffFact MSF with (nolock) on MSF.LpuSection_id=ES.LpuSection_id
					where ES.EvnSection_pid=EVN.EVN_pid and MSF.MedPersonal_id=(case when isnumeric(EvnNeonatalSurvey_Doctor)=1 then EvnNeonatalSurvey_Doctor else 1 end)
					order by ES.EvnSection_id desc
				) EvnSection
				outer apply(
					select top 1 tem.NeonatalSurveyParam_numeric
					from dbo.NeonatalSurveyParam tem with(nolock)
					where tem.NeonatalSurveyParamType_id=1 and tem.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
					order by tem.NeonatalSurveyParam_updDT desc
				) tem
				outer apply(
					select top 1
						CAST(pw.PersonWeight_Weight AS decimal (6,2)) as PersonWeight_Weight
					  from v_PersonWeight PW with (nolock)
							outer apply (
								select top 1 PersonHeight_Height
								from v_PersonHeight with (nolock)
								where Person_id = EVN.Person_id
									and HeightMeasureType_id is not null
									and PersonHeight_setDT < EVN.Evn_disDT
								order by PersonHeight_setDT desc, PersonHeight_id desc
							) PH
					 where PW.Person_id = EVN.Person_id
					   and PW.PersonWeight_setDT < EVN.Evn_disDT
					 order by PW.PersonWeight_setDT desc, PW.PersonWeight_id desc
			 ) wei
			outer apply(
				select top 1 BF.NeonatalSurveyParam_int
				from dbo.NeonatalSurveyParam BF with(nolock)
				where BF.NeonatalSurveyParamType_id=3 and BF.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by BF.NeonatalSurveyParam_updDT desc
			) BreathFrequency
			outer apply(
				select top 1 tem.NeonatalSurveyParam_int
				from dbo.NeonatalSurveyParam tem with(nolock)
				where tem.NeonatalSurveyParamType_id=4 and tem.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by tem.NeonatalSurveyParam_updDT desc
			) HeartFrequency
			left join dbo.ReanimConditionType RCT with(nolock) on RCT.ReanimConditionType_id=NS.ReanimConditionType_id
			outer apply(
				select top 1 CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR with(nolock)
				where CR.NeonatalSurveyParamType_id=17 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
			) CheckReact
			outer apply(
				select top 1 CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR with(nolock)
				where CR.NeonatalSurveyParamType_id=18 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
			) CheckReactUser
			outer apply(
				select top 1 CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR with(nolock)
				where CR.NeonatalSurveyParamType_id=45 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
			) MuscleTone
			outer apply(
				select top 1 CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR with(nolock)
				where CR.NeonatalSurveyParamType_id=46 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
			) MuscleToneUser
			outer apply(
				select top 1 CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR with(nolock)
				where CR.NeonatalSurveyParamType_id=91 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
			) Oedemata
			outer apply(
				select top 1 CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR with(nolock)
				where CR.NeonatalSurveyParamType_id=92 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
			) OedemataUser
			outer apply(
				select top 1 CAST(HT.NeonatalSurveyParam_radio/10 as int) As val1, (HT.NeonatalSurveyParam_radio % 10) As val2
				from dbo.NeonatalSurveyParam HT with(nolock)
				where HT.NeonatalSurveyParamType_id=158 and HT.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by HT.NeonatalSurveyParam_updDT desc
			) HeartTones
			outer apply(
				select top 1 RUC.NeonatalSurveyParam_radio, RUC.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam RUC with(nolock)
				where RUC.NeonatalSurveyParamType_id=207 and RUC.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by RUC.NeonatalSurveyParam_updDT desc
			) RemainUmbilCord
			outer apply(
				select top 1 RUC.NeonatalSurveyParam_radio, RUC.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam RUC with(nolock)
				where RUC.NeonatalSurveyParamType_id=208 and RUC.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by RUC.NeonatalSurveyParam_updDT desc
			) RemainUmbilCordUser
			outer apply(
				select top 1 UW.NeonatalSurveyParam_radio, UW.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam UW with(nolock)
				where UW.NeonatalSurveyParamType_id=209 and UW.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by UW.NeonatalSurveyParam_updDT desc
			) UmbilicWound
			outer apply(
				select top 1 UW.NeonatalSurveyParam_radio, UW.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam UW with(nolock)
				where UW.NeonatalSurveyParamType_id=210 and UW.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by UW.NeonatalSurveyParam_updDT desc
			) UmbilicWoundUser
			{$where}
			order by EVN.Evn_setDT asc
		";
		//echo getDebugSQL($query, $data);exit;
		$response = $this->queryResult($query, $data);
		if (!is_array($response)) {
			return false;
		}

		return $response;
	}
}