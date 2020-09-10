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
				EPS.Lpu_id as \"Lpu_id\",
				EPS.EvnPS_id as \"EvnPS_id\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				ES.EvnSection_id as \"EvnSection_id\",
				to_char(ES.EvnSection_setDate, 'dd.mm.yyyy') as \"EvnSection_setDate\",
				ES.EvnSection_setTime as \"EvnSection_setTime\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				D.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\", 
				D.Diag_Name as \"Diag_Name\",
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Name as \"MedService_Name\",
				to_char(Evn.Evn_setDate, 'dd.mm.yyyy') as \"EvnParent_setDate\",
				to_char(Evn.Evn_disDate, 'dd.mm.yyyy') as \"EvnParent_disDate\"
			
			from v_EvnPS EPS
			inner join v_Evn Evn on Evn.Evn_rid = EPS.EvnPS_id
			left join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_disDT is null
			left join v_LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
			left join dbo.Diag D on D.Diag_id = COALESCE(ES.Diag_id, EPS.Diag_pid)
			left join dbo.EvnReanimatPeriod ERP on ERP.Evn_id = Evn.Evn_id
			left join dbo.MedService MS on  MS.MedService_id = ERP.MedService_id
			where Evn.Evn_id = :EvnNeonatalSurvey_pid
			";
		}else{
			//в случае специфики
			$query = "
				select
					EPS.Lpu_id as \"Lpu_id\",
					EPS.EvnPS_id as \"EvnPS_id\",
					EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
					ES.EvnSection_id as \"EvnSection_id\",
					to_char(ES.EvnSection_setDate, 'dd.mm.yyyy') as \"EvnSection_setDate\",
					ES.EvnSection_setTime as \"EvnSection_setTime\",
					LS.LpuSection_id as \"LpuSection_id\",
					LS.LpuSection_Name as \"LpuSection_Name\",
					D.Diag_id as \"Diag_id\",
					D.Diag_Code as \"Diag_Code\",
					D.Diag_Name as \"Diag_Name\"

				from v_EvnPS EPS
				inner join v_Evn Evn on Evn.Evn_id = EPS.EvnPS_id
				left join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_disDT is null
				left join v_LpuSection LS on LS.LpuSection_id = ES.LpuSection_id
				left join dbo.Diag D on D.Diag_id = COALESCE(ES.Diag_id, EPS.Diag_pid)
				where Evn_id = :EvnNeonatalSurvey_pid
			";
		}
		$result = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));

		if ( !is_object($result) ) return false;
		$par_data = $result->result('array');

		//признак наличия "Поступления"
		$query = "
			select count(*) as \"CNT\" 
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
			select
			case
				when  ReanimStageType_id = 2 then to_char(EvnNeonatalSurvey_disDate, 'dd.mm.yyyy') 
				else to_char(EvnNeonatalSurvey_setDate, 'dd.mm.yyyy') 
			end \"Previous_setDate\",
			case
				when  ReanimStageType_id = 2 then EvnNeonatalSurvey_disTime
				else  EvnNeonatalSurvey_setTime
			end \"Previous_setTime\",
			case  ReanimStageType_id
				when 2 then to_char(EvnNeonatalSurvey_disDT, 'yyyy-mm-dd hh24:mi:ss')
				else to_char(EvnNeonatalSurvey_setDT, 'yyyy-mm-dd hh24:mi:ss')
			end as \"EvnNeonatalSurvey_disDT\"
			from v_EvnNeonatalSurvey
			where EvnNeonatalSurvey_pid = :EvnNeonatalSurvey_pid".$where_."
			order by EvnNeonatalSurvey_setDT desc
			LIMIT 1
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
				select MSM.MedPersonal_id as \"MedPersonal_id\",  
						MPC.Person_id as \"Person_id\", 
						LEFT(PS.PersonSurName_SurName, 1) || LOWER(SUBSTRING(PS.PersonSurName_SurName, 2, 100)) || ' ' || SUBSTRING(PS.PersonFirName_FirName, 1, 1) || '.' || SUBSTRING(PS.PersonSecName_SecName, 1, 1)  || '.' as \"EvnReanimatCondition_Doctor\", 
						PS.PersonSurName_SurName as \"PersonSurName_SurName\", 
						PS.PersonFirName_FirName as \"PersonFirName_FirName\", 
						PS.PersonSecName_SecName as \"PersonSecName_SecName\"
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
				select MSF.MedPersonal_id as \"MedPersonal_id\",  
						MSF.Person_id as \"Person_id\", 
						LEFT(PS.PersonSurName_SurName, 1) || LOWER(SUBSTRING(PS.PersonSurName_SurName, 2, 100)) || ' ' || SUBSTRING(PS.PersonFirName_FirName, 1, 1) || '.' || SUBSTRING(PS.PersonSecName_SecName, 1, 1)  || '.' as \"EvnReanimatCondition_Doctor\", 
						PS.PersonSurName_SurName as \"PersonSurName_SurName\", 
						PS.PersonFirName_FirName as \"PersonFirName_FirName\", 
						PS.PersonSecName_SecName as \"PersonSecName_SecName\"
				from v_MedPersonal MP
					inner join v_MedStaffFact MSF on MSF.MedPersonal_id = MP.MedPersonal_id and MP.Lpu_id = :Lpu_id
					inner join PersonState PS on PS.Person_id = MP.Person_id
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
			  select SideType_id as \"SideType_id\", 
			  		SideType_Name as \"SideType_Name\", 
			  		SideType_SysNick as \"SideType_SysNick\"
				from dbo.SideType
			   order by SideType_id
	        ";
		$ReanimConditParam_SideType = $this->db->query($query)->result('array');

		/*получение данных шкал и мероприятий для нового наблюдения*******************************************************************************************************/
		//для новых записей наблюдения
		$Add_Params = array();
		if ($arg['action'] == 'add'){
			//pSOFA 
			$query = "
				select ES.EvnScale_id as \"EvnScale_id\", 
						ES.EvnScale_setDT as \"EvnScale_setDT\", 
						ES.EvnScale_disDT as \"EvnScale_disDT\", 
						ES.EvnScale_Result as \"EvnScale_Result\", 
						ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
				from dbo.v_EvnScale ES
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'psofa'
				order by ES.EvnScale_setDT desc	
				LIMIT 1";
			$psofa = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($psofa) ) return false;
			$Add_Params['psofa'] = $psofa->result('array');
			//PELOD-2 
			$query = "
				select ES.EvnScale_id as \"EvnScale_id\", 
						ES.EvnScale_setDT as \"EvnScale_setDT\", 
						ES.EvnScale_disDT as \"EvnScale_disDT\", 
						ES.EvnScale_Result as \"EvnScale_Result\", 
						ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
				from dbo.v_EvnScale ES
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'pelod'
				order by ES.EvnScale_setDT desc	
				LIMIT 1";
			$pelod = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($pelod) ) return false;
			$Add_Params['pelod'] = $pelod->result('array');
			//Glasgow 
			$query = "
				select ES.EvnScale_id as \"EvnScale_id\", 
						ES.EvnScale_setDT as \"EvnScale_setDT\", 
						ES.EvnScale_disDT as \"EvnScale_disDT\", 
						ES.EvnScale_Result as \"EvnScale_Result\", 
						ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
				from dbo.v_EvnScale ES
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick in ('glasgow','glasgow_ch','glasgow_neonat')
				order by ES.EvnScale_setDT desc	
				LIMIT 1";
			$glasgow = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($glasgow) ) return false;
			$Add_Params['glasgow'] = $glasgow->result('array');
			//COMFORT 
			$query = "
				select ES.EvnScale_id as \"EvnScale_id\", 
						ES.EvnScale_setDT as \"EvnScale_setDT\", 
						ES.EvnScale_disDT as \"EvnScale_disDT\", 
						ES.EvnScale_Result as \"EvnScale_Result\", 
						ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
				from dbo.v_EvnScale ES
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'comfort'
				order by ES.EvnScale_setDT desc	
				LIMIT 1";
			$comfort = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($comfort) ) return false;
			$Add_Params['comfort'] = $comfort->result('array');
			//N-PASS 
			$query = "
				select ES.EvnScale_id as \"EvnScale_id\", 
						ES.EvnScale_setDT as \"EvnScale_setDT\", 
						ES.EvnScale_disDT as \"EvnScale_disDT\", 
						ES.EvnScale_Result as \"EvnScale_Result\", 
						ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
				from dbo.v_EvnScale ES
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'npass'
				order by ES.EvnScale_setDT desc	
				LIMIT 1";
			$npass = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($npass) ) return false;
			$Add_Params['npass'] = $npass->result('array');
			//NIPS 
			$query = "
				select ES.EvnScale_id as \"EvnScale_id\", 
					ES.EvnScale_setDT as \"EvnScale_setDT\", 
					ES.EvnScale_disDT as \"EvnScale_disDT\", 
					ES.EvnScale_Result as \"EvnScale_Result\", 
					ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
				from dbo.v_EvnScale ES
				where EvnScale_pid = :EvnNeonatalSurvey_pid
				and ES.ScaleType_SysNick = 'nips'
				order by ES.EvnScale_setDT desc	
				LIMIT 1";
			$nips = $this->db->query($query, array('EvnNeonatalSurvey_pid' => $arg['EvnNeonatalSurvey_pid']));
			if ( !is_object($nips) ) return false;
			$Add_Params['nips'] = $nips->result('array');

			//ИВЛ //BOB - 09.04.2020
			$params['EvnNeonatalSurvey_disDT'] = $EvnNeonatalSurvey_disDT;
			$query = "
				select IVLParameter_id as \"IVLParameter_id\",
						IVLParameter_Apparat as \"IVLParameter_Apparat\",
						IVLP.IVLRegim_id as \"IVLRegim_id\",
						IVLR.IVLRegim_SysNick as \"IVLRegim_SysNick\", 
						IVLR.IVLRegim_Name as \"IVLRegim_Name\", 
						IVLParameter_TubeDiam as \"IVLParameter_TubeDiam\",
						IVLParameter_FiO2 as \"IVLParameter_FiO2\",
						IVLParameter_FrequSet as \"IVLParameter_FrequSet\",
						IVLParameter_VolInsp as \"IVLParameter_VolInsp\",
						IVLParameter_PressInsp as \"IVLParameter_PressInsp\",
						IVLParameter_PressSupp as \"IVLParameter_PressSupp\",
						IVLParameter_FrequTotal as \"IVLParameter_FrequTotal\",
						IVLParameter_VolTe as \"IVLParameter_VolTe\",
						IVLParameter_VolE as \"IVLParameter_VolE\",
						IVLParameter_TinTet as \"IVLParameter_TinTet\",
						IVLParameter_VolTrig as \"IVLParameter_VolTrig\",
						IVLParameter_PressTrig as \"IVLParameter_PressTrig\",
						IVLParameter_PEEP as \"IVLParameter_PEEP\",
						IVLParameter_PcentMinVol as \"IVLParameter_PcentMinVol\",
						IVLParameter_TwoASVMax as \"IVLParameter_TwoASVMax\",
						IVLParameter_VolTi as \"IVLParameter_VolTi\",
						IVLParameter_Peak as \"IVLParameter_Peak\",
						IVLParameter_MAP as \"IVLParameter_MAP\",
						IVLParameter_Tins as \"IVLParameter_Tins\",
						IVLParameter_FlowMax as \"IVLParameter_FlowMax\",
						IVLParameter_FlowMin as \"IVLParameter_FlowMin\",
						IVLParameter_deltaP as \"IVLParameter_deltaP\",
						IVLParameter_Other as \"IVLParameter_Other\"  
				 from v_EvnReanimatAction ERA
				 inner join v_IVLParameter IVLP on ERA.EvnReanimatAction_id = IVLP.EvnReanimatAction_id
				 inner join IVLRegim IVLR on IVLP.IVLRegim_id = IVLR.IVLRegim_id
				 where EvnReanimatAction_pid = :EvnNeonatalSurvey_pid 
				   and ReanimatActionType_SysNick = 'lung_ventilation'
				   and (EvnReanimatAction_disDT >= :EvnNeonatalSurvey_disDT or EvnReanimatAction_disDT is null  )
				 order by ERA.EvnReanimatAction_id desc
				 LIMIT 1";
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
			select	ENS.EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\", 
				ENS.EvnNeonatalSurvey_pid as \"EvnNeonatalSurvey_pid\", 
				ENS.Person_id as \"Person_id\", 
				ENS.PersonEvn_id as \"PersonEvn_id\", 
				ENS.Server_id as \"Server_id\",    
				to_char(ENS.EvnNeonatalSurvey_setDate, 'dd.mm.yyyy') as \"EvnNeonatalSurvey_setDate\",
				ENS.EvnNeonatalSurvey_setTime as \"EvnNeonatalSurvey_setTime\",
				to_char(ENS.EvnNeonatalSurvey_disDate, 'dd.mm.yyyy') as \"EvnNeonatalSurvey_disDate\",
				ENS.EvnNeonatalSurvey_disTime as \"EvnNeonatalSurvey_disTime\",
				cast(ENS.ReanimStageType_id as integer) as \"ReanimStageType_id\", 
				ENS.ReanimConditionType_id as \"ReanimConditionType_id\",
				ReanimArriveFromType_id as \"ReanimArriveFromType_id\",
				EvnNeonatalSurvey_Conclusion as \"EvnNeonatalSurvey_Conclusion\",
				EvnNeonatalSurvey_Doctor as \"EvnNeonatalSurvey_Doctor\"
			from dbo.v_EvnNeonatalSurvey ENS
			where ENS.EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id']));
		if ( !is_object($result) ) return false;
		$EvnNeonatalSurvey = $result->result('array')[0];

		$query = "
			select NSPT.NeonatalSurveyParamType_Name as \"NeonatalSurveyParamType_Name\",	
					NSPT.NeonatalSurveyParamType_SysNick as \"NeonatalSurveyParamType_SysNick\",	
					NSPT.AnswerType_id as \"AnswerType_id\", 
					A_T.AnswerType_Name as \"AnswerType_Name\",
					NSP.EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\", 
					NSP.NeonatalSurveyParamType_id as \"NeonatalSurveyParamType_id\", 
					NSP.NeonatalSurveyParam_Is as \"NeonatalSurveyParam_Is\", 
					NSP.NeonatalSurveyParam_varchar as \"NeonatalSurveyParam_varchar\", 
					NSP.NeonatalSurveyParam_Sprav as \"NeonatalSurveyParam_Sprav\", 
					NSP.NeonatalSurveyParam_SpravName as \"NeonatalSurveyParam_SpravName\", 
					NSP.NeonatalSurveyParam_numeric as \"NeonatalSurveyParam_numeric\", 
					NSP.NeonatalSurveyParam_int as \"NeonatalSurveyParam_int\", 
					NSP.NeonatalSurveyParam_Radio as \"NeonatalSurveyParam_Radio\"
			from v_NeonatalSurveyParam NSP
			inner join dbo.v_NeonatalSurveyParamType NSPT on NSPT.NeonatalSurveyParamType_id = NSP.NeonatalSurveyParamType_id
			inner join dbo.v_AnswerType A_T  on A_T.AnswerType_id = NSPT.AnswerType_id
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
			select BreathAuscultative_id as \"BreathAuscultative_id\"
				,EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\"
				,BA.SideType_id as \"SideType_id\"
				,ST.SideType_SysNick as \"SideType_SysNick\"
				,BreathAuscultative_Auscult as \"BreathAuscultative_Auscult\"
				,BreathAuscultative_AuscultTxt as \"BreathAuscultative_AuscultTxt\"
				,BreathAuscultative_Rale as \"BreathAuscultative_Rale\"
				,BreathAuscultative_IsPleuDrain as \"BreathAuscultative_IsPleuDrain\"
				,2 as \"BA_RecordStatus\"
			from v_BreathAuscultative BA
			inner join SideType ST on BA.SideType_id = ST.SideType_id
			where BA.EvnNeonatalSurvey_id = :EvnNeonatalSurvey_id
			order by SideType_id
		";
		$result = $this->db->query($query, array('EvnNeonatalSurvey_id' => $data['EvnNeonatalSurvey_id']));
		if ( !is_object($result) ) return false;
		$BreathAuscultative = $result->result('array');

		//Травмы
		$query = "
			select NeonatalTrauma_id as \"NeonatalTrauma_id\",
				EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\",
				NeonatalTrauma_Fracture as \"NeonatalTrauma_Fracture\",
				NeonatalTrauma_IsRigidMusclClavicle as \"NeonatalTrauma_IsRigidMusclClavicle\",
				NeonatalTrauma_IsLossLimbMov as \"NeonatalTrauma_IsLossLimbMov\",
				NeonatalTrauma_IsLimitMov as \"NeonatalTrauma_IsLimitMov\",
				NeonatalTrauma_IsCrepitation as \"NeonatalTrauma_IsCrepitation\",
				NeonatalTrauma_IsPain as \"NeonatalTrauma_IsPain\",
				NeonatalTrauma_IsPseudoParalys as \"NeonatalTrauma_IsPseudoParalys\",
				2 as \"NT_RecordStatus\"
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

				select
					EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\",
					Error_Code  as \"Error_Code\",
					Error_Message as \"Error_Message\" 
				from   dbo.p_EvnNeonatalSurvey_ins (
					EvnNeonatalSurvey_id := :EvnNeonatalSurvey_id,
					EvnNeonatalSurvey_pid := :EvnNeonatalSurvey_pid, 
					EvnNeonatalSurvey_rid := :EvnNeonatalSurvey_rid, 
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id, 
					EvnNeonatalSurvey_setDT := :EvnNeonatalSurvey_setDT, 
					EvnNeonatalSurvey_disDT := :EvnNeonatalSurvey_disDT, 

					ReanimStageType_id := :ReanimStageType_id,
					ReanimArriveFromType_id:= :ReanimArriveFromType_id,
					ReanimConditionType_id := :ReanimConditionType_id,
					EvnNeonatalSurvey_Conclusion := :EvnNeonatalSurvey_Conclusion,
					EvnNeonatalSurvey_Doctor := :EvnNeonatalSurvey_Doctor,

					pmUser_id := :pmUser_id
					);

			";
			$result = $this->db->query($query, $params);
			//sql_log_message('error', 'p_EvnNeonatalSurvey_ins exec query: ', getDebugSql($query, $params));	


			if ( !is_object($result) )
				return false;
		}
		else {
			$query = "
				select
					EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\",
					Error_Code  as \"Error_Code\",
					Error_Message as \"Error_Message\" 
				from p_EvnNeonatalSurvey_upd   (					
					EvnNeonatalSurvey_id := :EvnNeonatalSurvey_id,
					EvnNeonatalSurvey_pid := :EvnNeonatalSurvey_pid,
					EvnNeonatalSurvey_rid := :EvnNeonatalSurvey_rid,
					Lpu_id := :Lpu_id, 
					Server_id := :Server_id, 
					PersonEvn_id := :PersonEvn_id, 
					EvnNeonatalSurvey_setDT := :EvnNeonatalSurvey_setDT, 
					EvnNeonatalSurvey_disDT := :EvnNeonatalSurvey_disDT, 
		
					ReanimStageType_id := :ReanimStageType_id,
					ReanimArriveFromType_id := :ReanimArriveFromType_id,
					ReanimConditionType_id := :ReanimConditionType_id,
					EvnNeonatalSurvey_Conclusion := :EvnNeonatalSurvey_Conclusion,
					EvnNeonatalSurvey_Doctor := :EvnNeonatalSurvey_Doctor,
		
					pmUser_id := :pmUser_id
					);
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
				select NeonatalSurveyParamType_id as \"NeonatalSurveyParamType_id\",
						NeonatalSurveyParamType_SysNick as \"NeonatalSurveyParamType_SysNick\",
						AnswerType_id as \"AnswerType_id\"
				from dbo.NeonatalSurveyParamType
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
			insert into dbo.NeonatalSurveyParam (EvnNeonatalSurvey_id, NeonatalSurveyParamType_id, NeonatalSurveyParam_Is, NeonatalSurveyParam_varchar, NeonatalSurveyParam_Sprav, NeonatalSurveyParam_SpravName,
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
							select
								BreathAuscultative_id as \"BreathAuscultative_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Message\"
							from dbo.p_BreathAuscultative_ins (
								BreathAuscultative_id := :BreathAuscultative_id,
								EvnNeonatalSurvey_id := :EvnNeonatalSurvey_id,
								SideType_id := :SideType_id,
								BreathAuscultative_Auscult := :BreathAuscultative_Auscult,
								BreathAuscultative_AuscultTxt := :BreathAuscultative_AuscultTxt,
								BreathAuscultative_Rale := :BreathAuscultative_Rale,
								BreathAuscultative_RaleTxt := null,
								BreathAuscultative_IsPleuDrain := :BreathAuscultative_IsPleuDrain,
								BreathAuscultative_PleuDrainTxt := null,
								pmUser_id := :pmUser_id
								);
						";
						$result = $this->db->query($query, $BreathAuscult);
						sql_log_message('error', 'p_BreathAuscultative_ins exec query: ', getDebugSql($query, $BreathAuscult));
						break;
					case 2:
						//изменение 
						$query = "
							select
								BreathAuscultative_id as \"BreathAuscultative_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Message\"
							from   dbo.p_BreathAuscultative_upd (
								BreathAuscultative_id := :BreathAuscultative_id,
								EvnNeonatalSurvey_id := :EvnNeonatalSurvey_id,
								SideType_id := :SideType_id,
								BreathAuscultative_Auscult := :BreathAuscultative_Auscult,
								BreathAuscultative_AuscultTxt := :BreathAuscultative_AuscultTxt,
								BreathAuscultative_Rale := :BreathAuscultative_Rale,
								BreathAuscultative_RaleTxt := null,
								BreathAuscultative_IsPleuDrain := :BreathAuscultative_IsPleuDrain,
								BreathAuscultative_PleuDrainTxt := null,
								pmUser_id := :pmUser_id
								);
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
			select BreathAuscultative_id as \"BreathAuscultative_id\"
				,EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\"
				,BA.SideType_id as \"SideType_id\"
				,ST.SideType_SysNick as \"SideType_SysNick\"
				,BreathAuscultative_Auscult as \"BreathAuscultative_Auscult\"
				,BreathAuscultative_AuscultTxt as \"BreathAuscultative_AuscultTxt\"
				,BreathAuscultative_Rale as \"BreathAuscultative_Rale\"
				,BreathAuscultative_IsPleuDrain as \"BreathAuscultative_IsPleuDrain\"
				,2 as \"BA_RecordStatus\" 
			from v_BreathAuscultative BA
			inner join SideType ST on BA.SideType_id = ST.SideType_id
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
							select
								NeonatalTrauma_id as \"NeonatalTrauma_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Message\"
							from dbo.p_NeonatalTrauma_ins (
								NeonatalTrauma_id := :NeonatalTrauma_id,
								EvnNeonatalSurvey_id  := :EvnNeonatalSurvey_id,
							
								NeonatalTrauma_Fracture  := :NeonatalTrauma_Fracture,
								NeonatalTrauma_IsRigidMusclClavicle := :NeonatalTrauma_IsRigidMusclClavicle,
								NeonatalTrauma_IsLossLimbMov := :NeonatalTrauma_IsLossLimbMov,
								NeonatalTrauma_IsLimitMov := :NeonatalTrauma_IsLimitMov,
								NeonatalTrauma_IsCrepitation := :NeonatalTrauma_IsCrepitation,
								NeonatalTrauma_IsPain := :NeonatalTrauma_IsPain,
								NeonatalTrauma_IsPseudoParalys := :NeonatalTrauma_IsPseudoParalys,
							
								pmUser_id := :pmUser_id
								);
						";
						$result = $this->db->query($query, $NeonatalTrauma);
						sql_log_message('error', 'p_NeonatalTrauma_ins exec query: ', getDebugSql($query, $NeonatalTrauma));
						break;
					case 2:
						//изменение 
						$query = "
							select
								NeonatalTrauma_id as \"NeonatalTrauma_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Message\"
							from dbo.p_NeonatalTrauma_upd (
								NeonatalTrauma_id := :NeonatalTrauma_id ,
								EvnNeonatalSurvey_id  := :EvnNeonatalSurvey_id,
							
								NeonatalTrauma_Fracture  := :NeonatalTrauma_Fracture,
								NeonatalTrauma_IsRigidMusclClavicle := :NeonatalTrauma_IsRigidMusclClavicle,
								NeonatalTrauma_IsLossLimbMov := :NeonatalTrauma_IsLossLimbMov,
								NeonatalTrauma_IsLimitMov := :NeonatalTrauma_IsLimitMov,
								NeonatalTrauma_IsCrepitation := :NeonatalTrauma_IsCrepitation,
								NeonatalTrauma_IsPain := :NeonatalTrauma_IsPain,
								NeonatalTrauma_IsPseudoParalys := :NeonatalTrauma_IsPseudoParalys,
							
								pmUser_id := :pmUser_id
								);
						";
						$result = $this->db->query($query, $NeonatalTrauma);
						sql_log_message('error', 'p_NeonatalTrauma_upd exec query: ', getDebugSql($query, $NeonatalTrauma));
						break;
					case 3:
						//удаление
						$query = "
							select
								NeonatalTrauma_id as \"NeonatalTrauma_id\",
								Error_Code as \"Error_Code\",
								Error_Message as \"Error_Message\" 			
							from dbo.p_NeonatalTrauma_del (
								NeonatalTrauma_id := :NeonatalTrauma_id,
								--@IsRemove  = 2,
								pmUser_id := :pmUser_id
								);		
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
			select NeonatalTrauma_id as \"NeonatalTrauma_id\",
				EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\",
				NeonatalTrauma_Fracture as \"NeonatalTrauma_Fracture\",
				NeonatalTrauma_IsRigidMusclClavicle as \"NeonatalTrauma_IsRigidMusclClavicle\",
				NeonatalTrauma_IsLossLimbMov as \"NeonatalTrauma_IsLossLimbMov\",
				NeonatalTrauma_IsLimitMov as \"NeonatalTrauma_IsLimitMov\",
				NeonatalTrauma_IsCrepitation as \"NeonatalTrauma_IsCrepitation\",
				NeonatalTrauma_IsPain as \"NeonatalTrauma_IsPain\",
				NeonatalTrauma_IsPseudoParalys as \"NeonatalTrauma_IsPseudoParalys\",
				2 as \"NT_RecordStatus\"
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
			select BreathAuscultative_id as \"BreathAuscultative_id\" from BreathAuscultative
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
				select
				 	BreathAuscultative_id \"BreathAuscultative_id\",
				 	Error_Code as \"Error_Code\",
					Error_Message as \"Error_Message\" 
				from dbo.p_BreathAuscultative_del
					 BreathAuscultative_id := :BreathAuscultative_id,
					 pmUser_id := :pmUser_id
					 );		
			";

			$result = $this->db->query($query, $queryParams);
			sql_log_message('error', 'EvnNeonatalSurvey_model p_BreathAuscultative_del query: ', getDebugSql($query, $queryParams));
			if ( !is_object($result) )
				return false;
		}

		//ТРАВМЫ
		$query = "
			select NeonatalTrauma_id as \"NeonatalTrauma_id\" from dbo.NeonatalTrauma
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
				select
					NeonatalTrauma_id as \"NeonatalTrauma_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Message\" 
				from dbo.p_NeonatalTrauma_del (
					NeonatalTrauma_id := :NeonatalTrauma_id,
					pmUser_id := :pmUser_id
					);		
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
			select
				EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Message\" 
			from dbo.p_EvnNeonatalSurvey_del (
				EvnNeonatalSurvey_id := :EvnNeonatalSurvey_id,
				pmUser_id := :pmUser_id
				);		
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
			select RDT.ReanimDrugType_id as \"ReanimDrugType_id\", 
					ReanimDrugType_Name as \"ReanimDrugType_Name\", 
					ReanimDrug_Dose as \"ReanimDrug_Dose\",	
					ReanimDrug_Unit as \"ReanimDrug_Unit\"
			  from v_ReanimDrug RD
			 inner join ReanimDrugType RDT on RDT.ReanimDrugType_id = RD.ReanimDrugType_id
			 where EvnReanimatAction_id IN (
			 	select EvnReanimatAction_id 
				  from v_EvnReanimatAction
				 where EvnReanimatAction_pid = :EvnNeonatalSurvey_pid
				   and ReanimatActionType_SysNick = :ReanimatActionType_SysNick
				   and EvnReanimatAction_setDT >= :EvnNeonatalSurvey_setDate
				   and EvnReanimatAction_setDT <= COALESCE(:EvnNeonatalSurvey_disDate, GetDate())
				order by EvnReanimatAction_id desc
				LIMIT 1
				)
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
			$where .= 'where EVN.Evn_pid=EVN.Evn_rid and EVN.Evn_pid in (select EvnPS0.EvnPS_id from v_EvnPS EvnPS0 where EvnPS0.person_id=:Person_id)';

		$query = "
			select
				NS.EvnNeonatalSurvey_id as \"EvnNeonatalSurvey_id\",
				to_char(EVN.Evn_setDT, 'dd.mm.yyyy') as \"Evn_setD\",
				to_char(EVN.Evn_setDT, 'hh24:mi') as \"Evn_setT\",
				wei.PersonWeight_Weight as \"PersonWeight_Weight\",
				tem.NeonatalSurveyParam_numeric as \"PersonTemperature\",
				BreathFrequency.neonatalsurveyparam_integer as \"BreathFrequency\",
				HeartFrequency.neonatalsurveyparam_integer as \"HeartFrequency\",
				RCT.ReanimConditionType_Name as \"ReanimConditionType_Name\",
				(case CheckReact.NeonatalSurveyParam_radio::integer when 1 then 'адекватная'
					when 2 then 'снижена'
					when 3 then 'беспокойство'
					when 4 then CheckReactUser.NeonatalSurveyParam_varchar
					else ''
				end) as \"CheckReact\",
				(case MuscleTone.NeonatalSurveyParam_radio::integer when 1 then 'атония'
					when 2 then 'гипотонус'
					when 3 then 'гипертонус'
					when 4 then 'нормотонус'
					when 5 then MuscleToneUser.NeonatalSurveyParam_varchar
					else ''
				end) as \"MuscleTone\",
				(case Oedemata.NeonatalSurveyParam_radio::integer when 00 then ''
					when 11 then 'пастозность'
					when 12 then 'пастозность'
					when 13 then 'пастозность'
					when 14 then 'пастозность'
					when 20 then 'склерема'
					when 30 then 'позиционные'
					when 40 then OedemataUser.NeonatalSurveyParam_varchar
					else ''
				end) as \"Oedemata\",
				(case HeartTones.val1::integer when 1 then 'ритмичный'
					when 2 then 'тахиаритмия'
					when 3 then 'брадиаритмия'
					when 4 then 'дополнительный тон'
					else ''
				end) as \"HeartTones1\",
				(case HeartTones.val2::integer when 1 then 'ясные'
					when 2 then 'приглушены'
					when 3 then 'глухие'
					else ''
				end) as \"HeartTones2\",
				(case RemainUmbilCord.NeonatalSurveyParam_radio::integer when 0 then ''
					when 1 then 'в скобе'
					when 2 then 'сухой'
					when 3 then 'отслаивается'
					when 4 then 'катетер в вене пуповины'
					when 5 then RemainUmbilCordUser.NeonatalSurveyParam_varchar
					else ''
				end) as \"RemainUmbilCord\",
				(case UmbilicWound.NeonatalSurveyParam_radio::integer when 0 then ''
					when 1 then 'сухая'
					when 2 then 'эпителизируется'
					when 3 then 'катетер в вене пуповины'
					when 4 then UmbilicWoundUser.NeonatalSurveyParam_varchar
					else ''
				end) as \"UmbilicWound\",
				PS.Person_SurName as \"Person_SurName\",
				PS.Person_FirName as \"Person_FirName\",
				PS.Person_SecName as \"Person_SecName\",
				PS.Person_BirthDay as \"Person_BirthDay\",
				PS.Sex_id as \"Sex_Code\",
				EVN.EVN_pid as \"EvnSection_pid\",
				EvnSection.LpuSection_id as \"LpuSection_id\"
			from v_EvnNeonatalSurvey NS
				inner join dbo.Evn EVN on EVN.evn_id=NS.EvnNeonatalSurvey_id
				inner join v_PersonState PS on PS.Person_id=EVN.Person_id
				left join lateral (
					select MSF.LpuSection_id
					from v_EvnSection ES
						inner join v_MedStaffFact MSF on MSF.LpuSection_id=ES.LpuSection_id
					where ES.EvnSection_pid=EVN.EVN_pid and MSF.MedPersonal_id=(case isnumeric(EvnNeonatalSurvey_Doctor)::integer when 1 then EvnNeonatalSurvey_Doctor::integer else 1 end)
					order by ES.EvnSection_id desc
					limit 1
				) EvnSection on true
				left join lateral (
					select tem.NeonatalSurveyParam_numeric
					from dbo.NeonatalSurveyParam tem
					where tem.NeonatalSurveyParamType_id=1 and tem.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
					order by tem.NeonatalSurveyParam_updDT desc
					limit 1
				) tem on true
				left join lateral (
					select
						CAST(pw.PersonWeight_Weight AS decimal (6,2)) as PersonWeight_Weight
					  from v_PersonWeight PW
							left join lateral (
								select PersonHeight_Height
								from v_PersonHeight
								where Person_id = EVN.Person_id
									and HeightMeasureType_id is not null
									and PersonHeight_setDT < EVN.Evn_disDT
								order by PersonHeight_setDT desc, PersonHeight_id desc
								limit 1
							) PH on true
					 where PW.Person_id = EVN.Person_id
					   and PW.PersonWeight_setDT < EVN.Evn_disDT
					 order by PW.PersonWeight_setDT desc, PW.PersonWeight_id desc
					 limit 1
			 	) wei on true
			left join lateral (
				select BF.neonatalsurveyparam_integer
				from dbo.NeonatalSurveyParam BF
				where BF.NeonatalSurveyParamType_id=3 and BF.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by BF.NeonatalSurveyParam_updDT desc
				limit 1
			) BreathFrequency on true
			left join lateral (
				select tem.neonatalsurveyparam_integer
				from dbo.NeonatalSurveyParam tem
				where tem.NeonatalSurveyParamType_id=4 and tem.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by tem.NeonatalSurveyParam_updDT desc
				limit 1
			) HeartFrequency on true
			left join dbo.ReanimConditionType RCT  on RCT.ReanimConditionType_id=NS.ReanimConditionType_id
			left join lateral (
				select CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR
				where CR.NeonatalSurveyParamType_id=17 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
				limit 1
			) CheckReact on true
			left join lateral (
				select CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR
				where CR.NeonatalSurveyParamType_id=18 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
				limit 1
			) CheckReactUser on true
			left join lateral (
				select CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR
				where CR.NeonatalSurveyParamType_id=45 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
				limit 1
			) MuscleTone on true
			left join lateral (
				select CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR
				where CR.NeonatalSurveyParamType_id=46 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
				limit 1
			) MuscleToneUser on true
			left join lateral (
				select CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR
				where CR.NeonatalSurveyParamType_id=91 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
				limit 1
			) Oedemata on true
			left join lateral (
				select CR.NeonatalSurveyParam_radio, CR.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam CR
				where CR.NeonatalSurveyParamType_id=92 and CR.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by CR.NeonatalSurveyParam_updDT desc
				limit 1
			) OedemataUser on true
			left join lateral (
				select CAST(HT.NeonatalSurveyParam_radio::integer/10 as int) As val1, (HT.NeonatalSurveyParam_radio::integer % 10) As val2
				from dbo.NeonatalSurveyParam HT
				where HT.NeonatalSurveyParamType_id=158 and HT.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by HT.NeonatalSurveyParam_updDT desc
				limit 1
			) HeartTones on true
			left join lateral (
				select RUC.NeonatalSurveyParam_radio, RUC.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam RUC
				where RUC.NeonatalSurveyParamType_id=207 and RUC.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by RUC.NeonatalSurveyParam_updDT desc
				limit 1
			) RemainUmbilCord on true
			left join lateral (
				select RUC.NeonatalSurveyParam_radio, RUC.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam RUC
				where RUC.NeonatalSurveyParamType_id=208 and RUC.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by RUC.NeonatalSurveyParam_updDT desc
				limit 1
			) RemainUmbilCordUser on true
			left join lateral (
				select UW.NeonatalSurveyParam_radio, UW.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam UW
				where UW.NeonatalSurveyParamType_id=209 and UW.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by UW.NeonatalSurveyParam_updDT desc
				limit 1
			) UmbilicWound on true
			left join lateral(
				select UW.NeonatalSurveyParam_radio, UW.NeonatalSurveyParam_varchar
				from dbo.NeonatalSurveyParam UW
				where UW.NeonatalSurveyParamType_id=210 and UW.EvnNeonatalSurvey_id=NS.EvnNeonatalSurvey_id
				order by UW.NeonatalSurveyParam_updDT desc
				limit 1
			) UmbilicWoundUser on true
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