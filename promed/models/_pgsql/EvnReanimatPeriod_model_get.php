<?php


class EvnReanimatPeriod_model_get
{
	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array
	 */
	public static function getAntropometrData(EvnReanimatPeriod_model $callObject, $data)
	{
		$data["Evn_disDate"] = ((($data["Evn_disDate"] != "") && $data["Evn_disTime"] != ""))?$data["Evn_disDate"] . " " . $data["Evn_disTime"] . ":00":null;
		$params = [
			"Person_id" => $data["Person_id"],
			"Evn_disDT" => isset($data["Evn_disDate"]) ? $data["Evn_disDate"] : null
		];
		$disDT = isset($params["Evn_disDT"]) ? ":Evn_disDT" : "GetDate()";
		$query = "
			select
				to_char(PH.PersonHeight_setDT, '{$callObject->dateTimeForm104}') as \"PersonHeight_setDate\",
			    PH.PersonHeight_Height::numeric as \"PersonHeight_Height\"
			from v_PersonHeight PH
			where PH.Person_id = :Person_id
			  and PH.PersonHeight_setDT < {$disDT}
			order by PH.PersonHeight_id desc
			limit 1
		";
		$PersonHeight = $callObject->db->query($query, $params)->result("array");
		//Поиск веса и Индекс массы тела
		$query = "
			select
				to_char(PW.PersonWeight_setDT, '{$callObject->dateTimeForm104}') as \"PersonWeight_setDate\",
				case when pw.Okei_id = 36
				    then pw.PersonWeight_Weight::numeric/1000
					else pw.PersonWeight_Weight
				end as \"PersonWeight_Weight\",
				case  when coalesce(PH.PersonHeight_Height, 0) > 0 and pw.PersonWeight_Weight is not null
					then round(case when pw.Okei_id = 36 then pw.PersonWeight_Weight::numeric/1000 else pw.PersonWeight_Weight end / power(0.01 * PH.PersonHeight_Height::numeric, 2), 2)
					else null
				end as \"Weight_Index\" 
			from
				v_PersonWeight PW
				left join lateral (
					select PersonHeight_Height
					from v_PersonHeight
					where Person_id = :Person_id
					  and HeightMeasureType_id is not null
					  and PersonHeight_setDT < {$disDT}
					order by PersonHeight_id desc
				    limit 1
				) as PH on true
			where PW.Person_id = :Person_id
			  and PW.PersonWeight_setDT < {$disDT}    
			order by PW.PersonWeight_id desc
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		$PersonWeight = $result->result("array");
		$ReturnObject = [
			"PersonHeight" => $PersonHeight,
			"PersonWeight" => $PersonWeight
		];
		return $ReturnObject;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetBreathAuscultative(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				BreathAuscultative_id as \"BreathAuscultative_id\",
			    EvnReanimatCondition_id as \"EvnReanimatCondition_id\",
			    BA.SideType_id as \"SideType_id\",
			    ST.SideType_SysNick as \"SideType_SysNick\",
			    BreathAuscultative_Auscult as \"BreathAuscultative_Auscult\",
			    BreathAuscultative_AuscultTxt as \"BreathAuscultative_AuscultTxt\",
			    BreathAuscultative_Rale as \"BreathAuscultative_Rale\",
			    BreathAuscultative_RaleTxt as \"BreathAuscultative_RaleTxt\",
			    BreathAuscultative_IsPleuDrain as \"BreathAuscultative_IsPleuDrain\",
			    BreathAuscultative_PleuDrainTxt as \"BreathAuscultative_PleuDrainTxt\",
			    2 as \"BA_RecordStatus\" 
			from
				v_BreathAuscultative BA
				inner join SideType ST on BA.SideType_id = ST.SideType_id
			where EvnReanimatCondition_id = :EvnReanimatCondition_id
			order by ST.SideType_SysNick
		";
		$queryParams = ["EvnReanimatCondition_id" => $data["EvnReanimatCondition_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return bool|string|null
	 */
	public static function getReanimSectionPatientList(EvnReanimatPeriod_model $callObject, $data)
	{
		$params = [
			"MedService_id" => $data["MedService_id"],
			"Lpu_id" => $data["Lpu_id"],
		];
		//Выборка отделений привязанных к службе реанимации
		$query = "
			select LS.LpuSection_id as \"LpuSection_id\"
			from
				v_MedServiceSection MSS
				inner join dbo.v_LpuSection LS on MSS.LpuSection_id = LS.LpuSection_id
			where MSS.MedService_id = :MedService_id
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		//если есть отделения прикреплённые к службе реанимации
		if (count($resp) > 0) {
			//Выборка не повторяющихся id персон, прикреплённым к отделениям, прикреплённым к службе реанимации
			$query = "
				select distinct ES.Person_id as \"Person_id\" 
				from
					v_MedServiceSection MSS 
					inner join v_LpuSection LS on MSS.LpuSection_id = LS.LpuSection_id
					inner join v_EvnSection ES on LS.LpuSection_id = ES.LpuSection_id
				where MSS.MedService_id = :MedService_id
				  and ES.EvnSection_setDT <= getdate() 
				  and (ES.EvnSection_disDT is null or ES.EvnSection_disDT > getdate())
			";
			$result = $callObject->db->query($query, $params);
		} else {
			//нет отделений прикреплённые к службе реанимации
			//Выборка не повторяющихся id персон, прикреплённым к отделениям данного стационара
			$query = "
				select distinct ES.Person_id as \"Person_id\" 
				from
					v_LpuSection LS
					inner join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
					inner join dbo.v_EvnSection ES on LS.LpuSection_id = ES.LpuSection_id
				where LS.Lpu_id = :Lpu_id
				  and LU.LpuUnitType_id = 1
				  and not exists (
				  	select 1
				  	from v_MedServiceSection MSS
				  	where MSS.LpuSection_id = LS.LpuSection_id
				  )
				  and ES.EvnSection_setDT <= getdate() 
				  and (ES.EvnSection_disDT is null or ES.EvnSection_disDT > getdate())
			";
			$result = $callObject->db->query($query, $params);
		}
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result("array");
		if (count($resp) == 0) {
			return null;
		}
		//собираю $Person_id в строку с разделителями - запятыми
		$Person_ids = "";
		foreach ($resp as $rows) {
			$Person_ids .= $rows["Person_id"] . ",";
		}
		$Person_ids = substr($Person_ids, 0, strlen($Person_ids) - 1);
		return $Person_ids;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $node
	 * @return array
	 */
	public static function getapache_TreeData(EvnReanimatPeriod_model $callObject, $node)
	{
		$params = [
			"node" => isset($node) ? $node : null
		];
		/**@var CI_DB_result $result */
		switch ($node) {
			case "root":
				//возвращает 2 строки:  Неоперированные пациенты, Послеоперационные пациенты
				$return[] = ["text" => "Неоперированные пациенты", "id" => "no_oper", "leaf" => false];
				$return[] = ["text" => "Послеоперационные пациенты", "id" => "oper", "leaf" => false];
				break;
			case "no_oper":
				//возвращает названия разделов по Неоперированным
				$query = "
					select
						SC.ScaleParameterType_id as \"num_id\",
					    SC.ScaleParameterType_SysNick as \"id\",
					    SC.ScaleParameterType_Name as \"text\",
					    0 as \"leaf\" 
					from v_Scale SC 
					where SC.ScaleType_id = 6
					  and SC.ScaleParameterType_id in (29,30,31,32,33,34)
					group by SC.ScaleParameterType_id,
					         SC.ScaleParameterType_SysNick,
					         SC.ScaleParameterType_Name 
					order by SC.ScaleParameterType_id 
				";
				$result = $callObject->db->query($query);
				$return = $result->result("array");
				break;
			case "breath_insufficiency":  //возвращает содержимое разделов по Неоперированным
			case "heart_insufficiency":
			case "trauma":
			case "neurology":
			case "other":
			case "organ_system":
			case "after_operation_plan_organ_system":////возвращает содержимое разделов "Если ничего не подходит - основная органная система" по Послеоперационным
			case "after_operation_extra_organ_system":
				$query = "
					select
						SC.ScaleParameterType_SysNick||'_'||SC.ScaleParameterResult_id::varchar as \"id\",
					    SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
					    SC.ScaleParameterResult_Name||'&nbsp;&nbsp;&nbsp;<span style=\"color: darkblue;\">'||ScaleParameterResult_Value::numeric::varchar||'</span>' as \"text\", 
						SC.ScaleParameterResult_Value::numeric::varchar as \"ScaleParameterResult_Value\",
					    1 as \"leaf\",
					    ScaleParameterType_id as \"ScaleParameterType_id\",
					    ScaleParameterResult_id as \"ScaleParameterResult_id\"
					from dbo.v_Scale SC 
					where SC.ScaleParameterType_SysNick = :node
					order by SC.ScaleParameterResult_id				
				";
				$result = $callObject->db->query($query, $params);
				$return = $result->result("array");
				break;
			case "oper":  //возвращает названия разделов по Послеоперационным
				$query = "
					select
						SC.ScaleParameterType_id as \"num_id\",
					    SC.ScaleParameterType_SysNick as \"id\",
					    SC.ScaleParameterType_Name as \"text\",
					    0 as \"leaf\" 
					from dbo.v_Scale SC 
					where SC.ScaleType_id = 6
					  and SC.ScaleParameterType_id in (35,37)
					group by SC.ScaleParameterType_id,
					         SC.ScaleParameterType_SysNick,
					         SC.ScaleParameterType_Name 
					order by SC.ScaleParameterType_id 
				";
				$result = $callObject->db->query($query);
				$return = $result->result("array");
				break;
			case "after_operation_plan":  //возвращает содержимое разделов по Послеоперационным
			case "after_operation_extra":
				$query = "
					select
						SC.ScaleParameterType_SysNick||'_'||SC.ScaleParameterResult_id::varchar as \"id\",
					    SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
						SC.ScaleParameterResult_Name||'&nbsp;&nbsp;&nbsp;<span style=\"color: darkblue;\">'||ScaleParameterResult_Value::numeric::varchar||'</span>' as \"text\", 
						SC.ScaleParameterResult_Value::numeric::varchar as \"ScaleParameterResult_Value\",
					    1 as \"leaf\",
					    ScaleParameterType_id as \"ScaleParameterType_id\",
					    ScaleParameterResult_id as \"ScaleParameterResult_id\"
					from dbo.v_Scale SC 
					where SC.ScaleParameterType_SysNick = :node
					order by SC.ScaleParameterResult_id				
				";
				$result = $callObject->db->query($query, $params);
				$return = $result->result("array");
				$return[] = [
					"id" => $node . "_organ_system",
					"text" => "Если ничего не подходит - основная органная система",
					"ScaleParameterResult_Value" => "0",
					"leaf" => false
				];
				break;
			default:
				$return[] = ["text" => "кое чё", "id" => "koe_cho", "leaf" => true];
		}
		return $return;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetCardPulm(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				ReanimatCardPulm_id as \"ReanimatCardPulm_id\",
				EvnReanimatAction_id as \"EvnReanimatAction_id\",
				to_char(ReanimatCardPulm_ClinicalDeathDate, '{$callObject->dateTimeForm104}') as \"ReanimatCardPulm_ClinicalDeathDate\",
				ReanimatCardPulm_ClinicalDeathTime as \"ReanimatCardPulm_ClinicalDeathTime\",
				ReanimatCardPulm_IsPupilDilat as \"ReanimatCardPulm_IsPupilDilat\",
				ReanimatCardPulm_IsCardMonitor as \"ReanimatCardPulm_IsCardMonitor\",
				ReanimatCardPulm_StopCardActType as \"ReanimatCardPulm_StopCardActType\",
				IVLRegim_id as \"IVLRegim_id\",
				ReanimatCardPulm_FiO2 as \"ReanimatCardPulm_FiO2\",
				ReanimatCardPulm_IsCardTonics as \"ReanimatCardPulm_IsCardTonics\",
				ReanimatCardPulm_CardTonicDose as \"ReanimatCardPulm_CardTonicDose\",
				ReanimatCardPulm_CathetVein as \"ReanimatCardPulm_CathetVein\",
				ReanimatCardPulm_TrachIntub as \"ReanimatCardPulm_TrachIntub\",
				ReanimatCardPulm_Auscultatory as \"ReanimatCardPulm_Auscultatory\",
				ReanimatCardPulm_AuscultatoryTxt as \"ReanimatCardPulm_AuscultatoryTxt\",
				ReanimatCardPulm_CardMassage as \"ReanimatCardPulm_CardMassage\",
				ReanimatCardPulm_DefibrilCount as \"ReanimatCardPulm_DefibrilCount\",
				ReanimatCardPulm_DefibrilMin as \"ReanimatCardPulm_DefibrilMin\",
				ReanimatCardPulm_DefibrilMax as \"ReanimatCardPulm_DefibrilMax\",
				ReanimDrugType_id as \"ReanimDrugType_id\",
				ReanimatCardPulm_DrugDose as \"ReanimatCardPulm_DrugDose\",
				ReanimatCardPulm_DrugSposob as \"ReanimatCardPulm_DrugSposob\",
				ReanimDrugType_did as \"ReanimDrugType_did\",
				ReanimatCardPulm_dDrugDose as \"ReanimatCardPulm_dDrugDose\",
				ReanimatCardPulm_dDrugSposob as \"ReanimatCardPulm_dDrugSposob\",
				ReanimatCardPulm_DrugTxt as \"ReanimatCardPulm_DrugTxt\",
				ReanimatCardPulm_IsEffective as \"ReanimatCardPulm_IsEffective\",
				ReanimatCardPulm_Time as \"ReanimatCardPulm_Time\",
				to_char(ReanimatCardPulm_BiologDeathDate, '{$callObject->dateTimeForm104}') as \"ReanimatCardPulm_BiologDeathDate\",
				ReanimatCardPulm_BiologDeathTime as \"ReanimatCardPulm_BiologDeathTime\",
				ReanimatCardPulm_DoctorTxt as \"ReanimatCardPulm_DoctorTxt\"
			from v_ReanimatCardPulm
			where EvnReanimatAction_id = :EvnReanimatAction_id
		";
		/**@var CI_DB_result $result */
		$queryParams = ["EvnReanimatAction_id" => $data["EvnReanimatAction_id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDataToNewCondition(EvnReanimatPeriod_model $callObject, $data)
	{
		$queryParams = ["EvnReanimatCondition_pid" => $data["EvnReanimatCondition_pid"]];
		//значени сатурации гемоглобина
		$query = "
			select RateSPO2_Value as \"EvnReanimatAction_ObservValue\"
			from
				v_EvnReanimatAction ERA
				inner join RateSPO2 SPO on ERA.EvnReanimatAction_id = SPO.EvnReanimatAction_id
			where ERA.EvnReanimatAction_pid = :EvnReanimatCondition_pid
			  and ERA.ReanimatActionType_id = 9
			  and SPO.RateSPO2_Deleted = 1
			order by RateSPO2_id desc
			limit 1
		";
		$SpO2 = $callObject->db->query($query, $queryParams);
		if (!is_object($SpO2)) {
			return false;
		}
	//	//питание
	//	$query = "
	//		select
	//			ERA.EvnReanimatAction_id as \"EvnReanimatAction_id\",
	//		    ERA.EvnReanimatAction_setDT as \"EvnReanimatAction_setDT\",
	//		    ERA.EvnReanimatAction_disDT as \"EvnReanimatAction_disDT\",
	//		    RNT.NutritiousType_id as \"NutritiousType_id\",
	//		    RNT.NutritiousType_Name as \"ReanimatNutritionType_Name\"   
	//		from
	//			v_EvnReanimatAction ERA
	//			inner join NutritiousType RNT on RNT.NutritiousType_id = ERA.NutritiousType_id
	//		where ERA.EvnReanimatAction_pid = :EvnReanimatCondition_pid
	//		  and ERA.ReanimatActionType_SysNick = 'nutrition'
	//		  and ERA.EvnReanimatAction_disDT is null
	//		order by ERA.EvnReanimatAction_setDT desc
	//		limit 1
	//	";
	//	$Nutrition = $callObject->db->query($query, $queryParams);
	//	if (!is_object($Nutrition)) {
	//		return false;
	//	}
	
		//параметры ИВЛ
		$query = "
			select
				IVLParameter_id as \"IVLParameter_id\",
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
			    IVLParameter_TwoASVMax as \"IVLParameter_TwoASVMax\"
			from
				v_EvnReanimatAction ERA
				inner join v_IVLParameter IVLP on ERA.EvnReanimatAction_id = IVLP.EvnReanimatAction_id
				inner join IVLRegim IVLR on IVLP.IVLRegim_id = IVLR.IVLRegim_id
			where EvnReanimatAction_pid = :EvnReanimatCondition_pid --	1357948
			  and ReanimatActionType_SysNick = 'lung_ventilation'
			order by ERA.EvnReanimatAction_id desc
			limit 1
		";
		$IVLParameter = $callObject->db->query($query, $queryParams);
		if (!is_object($IVLParameter)) {
			return false;
		}
		//SOFA
		$query = "
			select
				ES.EvnScale_id as \"EvnScale_id\",
			    ES.EvnScale_setDT as \"EvnScale_setDT\",
			    ES.EvnScale_disDT as \"EvnScale_disDT\",
			    ES.EvnScale_Result as \"EvnScale_Result\",
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			from v_EvnScale ES 
			where EvnScale_pid = :EvnReanimatCondition_pid
			  and ES.ScaleType_SysNick = 'sofa'
			order by ES.EvnScale_setDT desc
			limit 1
		";
		$Sofa = $callObject->db->query($query, $queryParams);
		if (!is_object($Sofa)) {
			return false;
		}
		//APACHE
		$query = "
			select
				ES.EvnScale_id as \"EvnScale_id\",
			    ES.EvnScale_setDT as \"EvnScale_setDT\",
			    ES.EvnScale_disDT as \"EvnScale_disDT\",
			    ES.EvnScale_Result as \"EvnScale_Result\",
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			from v_EvnScale ES 
			where EvnScale_pid = :EvnReanimatCondition_pid
			  and ES.ScaleType_SysNick = 'apache'
			order by ES.EvnScale_setDT desc
			limit 1
		";
		$Apache = $callObject->db->query($query, $queryParams);
		if (!is_object($Apache)) {
			return false;
		}
		//RASS
		$query = "
			select
				ES.EvnScale_id as \"EvnScale_id\",
			    ES.EvnScale_setDT as \"EvnScale_setDT\",
			    ES.EvnScale_disDT as \"EvnScale_disDT\",
			    ES.EvnScale_Result as \"EvnScale_Result\",
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			from v_EvnScale ES
			where EvnScale_pid = :EvnReanimatCondition_pid
			  and ES.ScaleType_SysNick = 'rass'
			order by ES.EvnScale_setDT desc
			limit 1
		";
		$rass = $callObject->db->query($query, $queryParams);
		if (!is_object($rass)) {
			return false;
		}
		//WATERLOW
		$query = "
			select
				ES.EvnScale_id as \"EvnScale_id\",
			    ES.EvnScale_setDT as \"EvnScale_setDT\",
			    ES.EvnScale_disDT as \"EvnScale_disDT\",
			    ES.EvnScale_Result as \"EvnScale_Result\",
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			from v_EvnScale ES 
			where EvnScale_pid = :EvnReanimatCondition_pid
			  and ES.ScaleType_SysNick = 'waterlow'
			order by ES.EvnScale_setDT desc
			limit 1
		";
		$waterlow = $callObject->db->query($query, $queryParams);
		if (!is_object($waterlow)) {
			return false;
		}
		//MRC
		$query = "
			select
				ES.EvnScale_id as \"EvnScale_id\",
			    ES.EvnScale_setDT as \"EvnScale_setDT\",
			    ES.EvnScale_disDT as \"EvnScale_disDT\",
			    ES.EvnScale_Result as \"EvnScale_Result\",
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			from v_EvnScale ES 
			where EvnScale_pid = :EvnReanimatCondition_pid
			  and ES.ScaleType_SysNick = 'mrc'
			order by ES.EvnScale_setDT desc
			limit 1
		";
		$mrc = $callObject->db->query($query, $queryParams);
		if (!is_object($mrc)) {
			return false;
		}
		
		//BOB - 16.09.2019
		//Glasgow
		$query = "
			select 
			    ES.EvnScale_id as \"EvnScale_id\", 
			    ES.EvnScale_setDT as \"EvnScale_setDT\", 
			    ES.EvnScale_disDT as \"EvnScale_disDT\", 
			    ES.EvnScale_Result as \"EvnScale_Result\", 
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\" 
			  from dbo.v_EvnScale ES 
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick in ('glasgow','glasgow_ch')
			 order by ES.EvnScale_setDT desc	
			 limit 1
			 ";	
		$Glasgow = $callObject->db->query($query, $queryParams);
		
		if ( !is_object($mrc) )
			return false;

		
		//BOB - 16.09.2019
		//FOUR
		$query = "
			select
			    ES.EvnScale_id as \"EvnScale_id\", 
			    ES.EvnScale_setDT as \"EvnScale_setDT\", 
			    ES.EvnScale_disDT as \"EvnScale_disDT\", 
			    ES.EvnScale_Result as \"EvnScale_Result\", 
			    ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			  from dbo.v_EvnScale ES
			 where EvnScale_pid = :EvnReanimatCondition_pid
			   and ES.ScaleType_SysNick = 'four'
			 order by ES.EvnScale_setDT desc
			 limit 1
			 ";	
		$FOUR = $callObject->db->query($query, $queryParams);
		
		if ( !is_object($mrc) )
			return false;
		
		
		$query = "
			select
				to_char(ERC.EvnReanimatCondition_disDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatCondition_disDate\",
				to_char(ERC.EvnReanimatCondition_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatCondition_disTime\"
			from v_EvnReanimatCondition ERC 
			where EvnReanimatCondition_pid = :EvnReanimatCondition_pid
			order by EvnReanimatCondition_setDT desc
			limit 1
		";
		$LastCondit = $callObject->db->query($query, $queryParams);
		if (!is_object($LastCondit)) {
			return false;
		}
		/**
		 * @var CI_DB_result $SpO2
		 * @var CI_DB_result $Nutrition
		 * @var CI_DB_result $IVLParameter
		 * @var CI_DB_result $Sofa
		 * @var CI_DB_result $Apache
		 * @var CI_DB_result $rass
		 * @var CI_DB_result $waterlow
		 * @var CI_DB_result $LastCondit
		 * @var CI_DB_result $mrc
		 */
		$ReturnObject = [
			"SpO2" => $SpO2->result("array"),
			//"Nutritious" => $Nutrition->result("array"),
			"IVLParameter" => $IVLParameter->result("array"),
			"Sofa" => $Sofa->result("array"),
			"Apache" => $Apache->result("array"),
			"rass" => $rass->result("array"),
			"waterlow" => $waterlow->result("array"),
			"LastCondit" => $LastCondit->result("array"),
			"mrc" => $mrc->result("array"),
			"Glasgow" => $Glasgow->result('array'),
			"FOUR" => $FOUR->result('array'),
			"Message" => ""
		];
		return $ReturnObject;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getDirectionLinkedDocs(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				EXDL.EvnXmlDirectionLink_id as \"EvnXmlDirectionLink_id\",
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.EvnDirection_pid as \"EvnDirection_pid\",
				EvnXml.EvnXml_id as \"EvnXml_id\",
				EvnXml.Evn_id as \"Evn_id\",
				Evn.Evn_pid as \"Evn_pid\",
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
				Evn.EvnClass_SysNick as \"EvnClass_SysNick\",
				EvnXml.XmlType_id as \"XmlType_id\",
				EvnXml.EvnXml_Name as \"EvnXml_Name\",
				EvnXml.EvnXml_Data as \"EvnXml_Data\",
				xts.XmlTemplateSettings_Settings as \"XmlTemplate_Settings\",
				xth.XmlTemplateHtml_HtmlTemplate as \"XmlTemplate_HtmlTemplate\",
				xtd.XmlTemplateData_Data as \"XmlTemplate_Data\",
				to_char(EvnXml.EvnXml_insDT, '{$callObject->dateTimeForm104}') as \"EvnXml_Date\",
				EvnXml.pmUser_insID as \"pmUser_insID\",
				RTRIM(LTRIM(coalesce(pmUserCache.pmUser_Name, ''))) as \"pmUser_Name\",
				0 as \"frame\",
				1 as \"readOnly\"
			from
				v_EvnXmlDirectionLink EXDL
				inner join v_EvnDirection_all ED on ED.EvnDirection_id = EXDL.EvnDirection_id
				inner join v_EvnXml EvnXml on EvnXml.EvnXml_id = EXDL.EvnXml_id 
				inner join v_Evn Evn on Evn.Evn_id = EvnXml.Evn_id
				left join pmUserCache on pmUserCache.pmUser_id = EvnXml.pmUser_insID
				left join XmlTemplateData xtd on xtd.XmlTemplateData_id = EvnXml.XmlTemplateData_id
				left join XmlTemplateHtml xth on xth.XmlTemplateHtml_id = EvnXml.XmlTemplateHtml_id
				left join XmlTemplateSettings xts on xts.XmlTemplateSettings_id = EvnXml.XmlTemplateSettings_id
			where EXDL.EvnDirection_id = :EvnDirection_id
			order by EvnXml.EvnXml_insDT desc
		";
		$queryParams = ["EvnDirection_id" => $data["EvnDirection_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getEvnScaleContent(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
  			select
  				SP.ScaleParameter_id as \"ScaleParameter_id\",
				SP.ScaleParameterType_id as \"ScaleParameterType_id\",
				SP.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				ES.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
				ES.ScaleType_SysNick as \"ScaleType_SysNick\"
			from
				ScaleParameter SP
				inner join v_Scale ES on SP.ScaleParameterType_id = ES.ScaleParameterType_id and SP.ScaleParameterResult_id = ES.ScaleParameterResult_id
			where SP.EvnScale_id = :EvnScale_id
		";
		$queryParams = ["EvnScale_id" => $data["EvnScale_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getEvnScaleContentEMK(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				S.ScaleParameterType_Name as \"ScaleParameterType_Name\",
				case when (S.ScaleType_SysNick = 'apache' and S.ScaleParameterType_id >= 29)
					then S.ScaleParameterResult_Value::varchar
					else S.ScaleParameterResult_Value::varchar
				end as \"ScaleParameterResult_Value\",
				S.ScaleParameterResult_Name as \"ScaleParameterResult_Name\"
			from
				ScaleParameter SP  
				inner join dbo.v_Scale S on S.ScaleParameterType_id = SP.ScaleParameterType_id and S.ScaleParameterResult_id = SP.ScaleParameterResult_id
			where SP.EvnScale_id = :EvnScale_id
			order by SP.ScaleParameterType_id 			
		";
		$queryParams = ["EvnScale_id" => $data["EvnScale_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetParamIVL(EvnReanimatPeriod_model $callObject, $data)
	{
		$query = "
			select
				IVLParameter_id as \"IVLParameter_id\",
			    IVLParameter_Apparat as \"IVLParameter_Apparat\",
			    IVLP.IVLRegim_id as \"IVLRegim_id\",
			    IVLR.IVLRegim_SysNick as \"IVLRegim_SysNick\",
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
			    IVLParameter_TwoASVMax as \"IVLParameter_TwoASVMax\"
			from
				v_IVLParameter IVLP
				inner join IVLRegim IVLR on IVLP.IVLRegim_id = IVLR.IVLRegim_id
			where EvnReanimatAction_id = :EvnReanimatAction_id
			limit 1
		";
		$queryParams = ["EvnReanimatAction_id" => $data["EvnReanimatAction_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function GetReanimatActionRate(EvnReanimatPeriod_model $callObject, $data)
	{
		//расчёт величины Rate_PerCent для рисования графика, это процеты поэтому *100, деление на 200 потому что принял 200 за максимум
		switch ($data["ReanimatActionType_SysNick"]) {
			case "endocranial_sensor":
				//использование датчика ВЧД
				$query = "
					select
						RateVCHD_id as \"Rate_id\",
					    RateVCHD_Value as \"Rate_Value\",
					    (RateVCHD_Value::numeric/200*100)::int8 as \"Rate_PerCent\",
					    RateVCHD_StepsToChange as \"Rate_StepsToChange\",
						to_char(RateVCHD_setDate, '{$callObject->dateTimeForm104}') as \"Rate_setDate\",
					    to_char(RateVCHD_setTime, '{$callObject->dateTimeForm108}') as \"Rate_setTime\",
						:EvnReanimatAction_id as \"EvnReanimatAction_id\",
					    1 as \"Rate_RecordStatus\",
					    RateVCHD_setDT as  \"Rate_setDT\"
					from v_RateVCHD
					where EvnReanimatAction_id = :EvnReanimatAction_id
					order by RateVCHD_id
				";
				$result = $callObject->db->query($query, ["EvnReanimatAction_id" => $data["EvnReanimatAction_id"]]);
				break;
			case "invasive_hemodynamics":
				//инвазивная гемодинамика - внутривенное измерение давления
				$query = "
					select
						RateHemodynam_id as \"Rate_id\",
					    RateHemodynam_Value as \"Rate_Value\",
					    (RateHemodynam_Value::numeric/200*100)::int8 as \"Rate_PerCent\",
					    '' as \"Rate_StepsToChange\",
					    to_char(RateHemodynam_setDate, '{$callObject->dateTimeForm104}') as \"Rate_setDate\",
					    to_char(RateHemodynam_setTime, '{$callObject->dateTimeForm108}') as \"Rate_setTime\",
						:EvnReanimatAction_id as \"EvnReanimatAction_id\",
					    1 as \"Rate_RecordStatus\",
					    RateHemodynam_setDT as  \"Rate_setDT\"
					from v_RateHemodynam
					where EvnReanimatAction_id = :EvnReanimatAction_id
					order by RateHemodynam_id
				";
				$result = $callObject->db->query($query, ["EvnReanimatAction_id" => $data["EvnReanimatAction_id"]]);
				break;
			case "observation_saturation":
				//Наблюдение сатурации гемоглобина
				$query = "
					select
						RateSPO2_id as \"Rate_id\",
					    RateSPO2_Value as \"Rate_Value\",
					    RateSPO2_Value as \"Rate_PerCent\",
					    '' as \"Rate_StepsToChange\",
						to_char(RateSPO2_setDate, '{$callObject->dateTimeForm104}') as \"Rate_setDate\",
					    RateSPO2_setTime as \"Rate_setTime\",
					    :EvnReanimatAction_id as \"EvnReanimatAction_id\",
					    1 as \"Rate_RecordStatus\",
					    RateSPO2_setDT as  \"Rate_setDT\" 
					from v_RateSPO2
					where EvnReanimatAction_id = :EvnReanimatAction_id
					order by RateSPO2_id
				";
				$result = $callObject->db->query($query, ["EvnReanimatAction_id" => $data["EvnReanimatAction_id"]]);
				break;
			case "new_rate":  //получение пустой записи для заполнения пустого грида измерений
				$query = "					
					select
						-1 as \"Rate_id\",
						0 as \"Rate_Value\",
						0 as \"Rate_PerCent\",
						'' as \"Rate_StepsToChange\", 
						to_char(getdate(), '{$callObject->dateTimeForm104}') as \"Rate_setDate\",
						to_char(getdate(), '{$callObject->dateTimeForm108}') as \"Rate_setTime\",
						case when :EvnReanimatAction_id is null
							then 'New_GUID_Id'
							else :EvnReanimatAction_id::varchar end
						as \"EvnReanimatAction_id\",
						0 as \"Rate_RecordStatus\"
				";
				$result = $callObject->db->query($query, ["EvnReanimatAction_id" => $data["EvnReanimatAction_id"]]);
				break;
		}
		/**@var CI_DB_result $result */
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $arg
	 * @return array|bool
	 */
	public static function getParamsERPWindow(EvnReanimatPeriod_model $callObject, $arg)
	{
		$query = "
			select
				ERP.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				ERP.EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
				to_char(ERP.EvnReanimatPeriod_setDate, '{$callObject->dateTimeForm104}') as \"EvnReanimatPeriod_setDate\",
				to_char(ERP.EvnReanimatPeriod_setTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatPeriod_setTime\",
				to_char(ERP.EvnReanimatPeriod_disDT, '{$callObject->dateTimeForm104}') as \"EvnReanimatPeriod_disDate\",
				to_char(ERP.EvnReanimatPeriod_disTime, '{$callObject->dateTimeForm108}') as \"EvnReanimatPeriod_disTime\",
				ERP.ReanimReasonType_id as \"ReanimReasonType_id\",
				ERP.ReanimResultType_id as \"ReanimResultType_id\",
				ERP.LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
				ERP.ReanimatAgeGroup_id as \"ReanimatAgeGroup_id\",
				LS.LpuSection_id as \"LpuSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\",
				LU.LpuUnitType_id as \"LpuUnitType_id\",
				MS.MedService_id as \"MedService_id\",
				MS.MedService_Name as \"MedService_Name\",
				to_char(ES.EvnSection_setDate, '{$callObject->dateTimeForm104}') as \"EvnSection_setDate\",
				to_char(ES.EvnSection_setTime, '{$callObject->dateTimeForm108}') as \"EvnSection_setTime\",
				ES.Diag_id as \"Diag_id\",
				D.Diag_Code as \"Diag_Code\",
				D.Diag_Name as \"Diag_Name\",
				EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				EPS.Lpu_id as \"Lpu_id\",
				EPS.EvnPS_id as \"EvnReanimatPeriod_rid\",
				EPS.Diag_pid as \"Diag_id_PS\",
				case
					when dp.Diag_Code IN ('U07.1', 'U07.2') then 3
					when dd.Diag_Code IN ('U07.1', 'U07.2') then 3
					when d.Diag_Code IN ('U07.1', 'U07.2') then 3
					when exists(
						select
							edps.EvnDiagPS_id
						from
							v_EvnDiagPS edps
							inner join v_Diag d on d.Diag_id = edps.Diag_id 
						where
							edps.EvnDiagPS_rid = EPS.EvnPS_id
							and edps.DiagSetType_id in (1, 2, 3)
							and d.Diag_Code IN ('U07.1', 'U07.2')
						limit 1
					) then 3
					else RepositoryObserv.CovidType_id
				end as \"CovidType_id\"
			from
				v_EvnReanimatPeriod ERP
				inner join v_EvnSection ES on ES.EvnSection_id = ERP.EvnReanimatPeriod_pid
				inner join v_EvnPS EPS on EPS.EvnPS_id = ES.EvnSection_pid
				left join v_LpuSection LS on LS.LpuSection_id = ERP.LpuSection_id
				left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_MedService MS on MS.MedService_id = ERP.MedService_id
				left join dbo.Diag D on D.Diag_id = coalesce(ES.Diag_id, EPS.Diag_pid)
				left join v_Diag DD on EPS.Diag_did = DD.Diag_id
				left join v_Diag DP on EPS.Diag_pid = DP.Diag_id
				left join lateral (
					select CovidType_id
					from v_RepositoryObserv
					where Evn_id = EPS.EvnPS_id
					limit 1
				) RepositoryObserv on true
			where ERP.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
		";
		$queryParams = ["EvnReanimatPeriod_id" => $arg["EvnReanimatPeriod_id"]];
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$erp_data = $result->result("array");
		$query = "
			select
				ERP.Person_id as \"Person_id\", 
				ERP.PersonEvn_id as \"PersonEvn_id\", 
				ERP.Server_id as \"Server_id\", 
				PS.PersonSurName_SurName as \"Person_Surname\",
				PS.PersonFirName_FirName as \"Person_Firname\",
				PS.PersonSecName_SecName as \"Person_Secname\",
				PS.PersonBirthDay_BirthDay as \"Person_Birthday\",
				PS.Sex_id as \"Sex_id\"
			from
				v_EvnReanimatPeriod ERP 
				inner join PersonState PS on ERP.Person_id = PS.Person_id
			where EvnReanimatPeriod_id = :EvnReanimatPeriod_id
		";
		$queryParams = ["EvnReanimatPeriod_id" => $arg["EvnReanimatPeriod_id"]];
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		$pers_data = $result->result("array");

		foreach ($pers_data as $key => $value) {
			$pers_data[$key]['Person_Birthday'] = [
					'date' => $value['Person_Birthday']
			];
		}

        //BOB - 27.09.2019
        $query = "
			select
			    MSM.MedPersonal_id as \"MedPersonal_id\",
			    MPC.Person_id as \"Person_id\",
			    LEFT(PS.PersonSurName_SurName, 1) || LOWER(SUBSTRING(PS.PersonSurName_SurName, 2, 100)) || ' ' || SUBSTRING(PS.PersonFirName_FirName, 1, 1) || '.' || SUBSTRING(PS.PersonSecName_SecName, 1, 1)  || '.' as \"EvnReanimatCondition_Doctor\",
			    PS.PersonSurName_SurName as \"PersonSurName_SurName\",
			    PS.PersonFirName_FirName as \"PersonFirName_FirName\",
			    PS.PersonSecName_SecName as \"PersonSecName_SecName\"
            from 
                v_EvnReanimatPeriod ERP
                inner join MedServiceMedPersonal MSM on ERP.MedService_id = MSM.MedService_id
                inner join MedPersonalCache MPC on MSM.MedPersonal_id = MPC.MedPersonal_id
                inner join PersonState PS on MPC.Person_id = PS.Person_id
			where
			    ERP.EvnReanimatPeriod_id = :EvnReanimatPeriod_id
            and
                MSM.MedServiceMedPersonal_endDT is NULL
			group by MSM.MedPersonal_id,  MPC.Person_id, PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
			order by PS.PersonSurName_SurName, PS.PersonFirName_FirName	, PS.PersonSecName_SecName
		";
        $result = $callObject->db->query($query, $queryParams);

        if ( !is_object($result) ) return false;

        $MS_doctors = $result->result('array');
        //BOB - 27.09.2019
        
		$ReturnObject = [
			"erp_data" => $erp_data,
			"pers_data" => $pers_data,
			"MS_doctors" => $MS_doctors
		];
		return $ReturnObject;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return string
	 */
	public static function getProfilSectionId(EvnReanimatPeriod_model $callObject, $data)
	{
		//Выборка отделений привязанных к службе реанимации
		$params = ["MedService_id" => $data["MedService_id"]];
		$query = "
			select LS.LpuSection_id as \"LpuSection_id\" 
			from
				v_MedServiceSection MSS
				inner join dbo.v_LpuSection LS on MSS.LpuSection_id = LS.LpuSection_id
			where MSS.MedService_id = :MedService_id
			  and LpuSectionProfile_SysNick = 'profil'
			limit 1
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		$result = $result->result("array");
		if (count($result) == 0) {
			$params = ["Lpu_id" => $data["Lpu_id"]];
			$query = "
				select LpuSection_id as \"LpuSection_id\"
				from
					v_LpuSection LS
					inner join v_LpuUnit LU on LS.LpuUnit_id = LU.LpuUnit_id  
				where LS.LpuSectionProfile_SysNick = 'profil'
				  and LS.Lpu_id = :Lpu_id
				  and LU.LpuUnitType_SysNick = 'stac'
				limit 1
			";
			$result = $callObject->db->query($query, $params);
			$result = $result->result("array");
		}
		$response = "";
		if (count($result) != 0) {
			$response = $result[0]["LpuSection_id"];
		}
		return $response;
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getReanimationPatientList(EvnReanimatPeriod_model $callObject, $data)
	{
		$filterArray = [];
		$queryParams = [
			"LpuSection_id" => $data["LpuSection_id"],
			"LpuSectionWard_id" => $data["object_value"],
			"MedService_id" => $data["MedService_id"],
			"date" => $data["date"]
		];
		if (!empty($data["filter_Person_F"])) {
			$filterArray[] = "Person_all.Person_SurName ilike :Person_F";
			$queryParams["Person_F"] = $data["filter_Person_F"] . "%";
		}
		if (!empty($data["filter_Person_I"])) {
			$filterArray[] = "Person_all.Person_FirName ilike :Person_I";
			$queryParams["Person_I"] = $data["filter_Person_I"] . "%";
		}
		if (!empty($data["filter_Person_O"])) {
			$filterArray[] = "Person_all.Person_SecName ilike :Person_O";
			$queryParams["Person_O"] = $data["filter_Person_O"] . "%";
		}
		if (!empty($data["filter_Person_BirthDay"])) {
			$filterArray[] = "CAST(Person_all.Person_BirthDay as date) = CAST(:Person_BirthDay as date)";
			$queryParams["Person_BirthDay"] = $data["filter_Person_BirthDay"];
		}
		$whereString = (count($filterArray) != 0) ? " and " . implode(" and ", $filterArray) : "";
		$query = "
            select distinct
				EvnReanimatPeriod.EvnReanimatPeriod_setDT as \"EvnReanimatPeriod_setDT\",
				EvnReanimatPeriod.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				EvnReanimatPeriod.EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\",
				EvnReanimatPeriod.LpuSection_id as \"LpuSection_id\",
				EvnReanimatPeriod.MedService_id as \"MedService_id\",
				Person_all.Sex_id as \"Sex_id\",
				case when 0=1 then PEH.PersonEncrypHIV_Encryp end as \"PersonEncrypHIV_Encryp\",
				case when 0=1 and PEH.PersonEncrypHIV_id is not null
					then PEH.PersonEncrypHIV_Encryp else Person_all.Person_Fio
				end as \"Person_Fio\",
				to_char(Person_all.Person_BirthDay, '{$callObject->dateTimeForm104}') as \"Person_BirthDay\",
				dbo.Age2(Person_all.Person_BirthDay, tzgetdate()) as \"Person_Age\",
				dbo.Age_newborn(Person_all.Person_BirthDay, tzgetdate()) as \"Person_AgeMonth\",
				Diag.Diag_Code as \"Diag_Code\",
				Diag.Diag_Name as \"Diag_Name\",
				coalesce(to_char(EvnReanimatPeriod.EvnReanimatPeriod_setDate, '{$callObject->dateTimeForm104}'), '') as \"EvnReanimatPeriod_setDate\", 
				coalesce(to_char(EvnReanimatPeriod.EvnReanimatPeriod_disDate, '{$callObject->dateTimeForm104}'), '') as \"EvnReanimatPeriod_disDate\",
				Person_all.Person_id as \"Person_id\",
				Person_all.Server_id as \"Server_id\",
				Person_all.PersonEvn_id as \"PersonEvn_id\",
				case when exists(
					select *
					from v_PersonQuarantine PQ
					where PQ.Person_id = Person_all.Person_id 
					and PQ.PersonQuarantine_endDT is null
				) then 2 else 1 end as \"PersonQuarantine_IsOn\",
				EvnPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				null as \"Mes_id\",
				null as \"Mes_Code\",
				null as \"KoikoDni\",
				EvnPS.EvnPS_id as \"EvnPS_id\",
				null as \"LpuSectionWard_id\", 
				null as \"MedPersonal_id\",   
				null as \"MedPersonal_Fin\",
                date_part('day', age(
                    EvnReanimatPeriod.EvnReanimatPeriod_setDate,
					case when (EvnReanimatPeriod.EvnReanimatPeriod_disDate > tzgetdate())
					    then :date
					    else coalesce(EvnReanimatPeriod.EvnReanimatPeriod_disDate, :date)
					end)
                ) as \"EvnSecdni\",
				EvnS.EvnSection_id as \"EvnSection_id\",
				LS.LpuSection_Name as \"LpuSection_Name\"
			from
				v_EvnReanimatPeriod EvnReanimatPeriod
				left join v_Person_all Person_all on Person_all.Server_id = EvnReanimatPeriod.Server_id
					and Person_all.Person_id = EvnReanimatPeriod.Person_id
					and Person_all.PersonEvn_id = EvnReanimatPeriod.PersonEvn_id
				inner join v_EvnSection EvnS on EvnS.EvnSection_id = EvnReanimatPeriod.EvnReanimatPeriod_pid
				inner join v_EvnPS EvnPS on EvnPS.EvnPS_id = EvnS.EvnSection_pid
				LEFT JOIN v_Diag Diag on Diag.Diag_id = coalesce(EvnS.Diag_id, EvnPS.Diag_pid)
				left join v_PersonEncrypHIV PEH on PEH.Person_id = Person_all.Person_id
				left join v_LpuSection as LS on LS.LpuSection_id = EvnS.LpuSection_id
			where EvnReanimatPeriod.MedService_id = :MedService_id
			  and CAST(EvnReanimatPeriod.EvnReanimatPeriod_setDate as date) <= CAST(getdate() as date)
			  and EvnReanimatPeriod.EvnReanimatPeriod_disDate is null
			  {$whereString}
			order by \"EvnReanimatPeriod_setDT\" desc
		";
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $queryParams);
		if (!is_object($result)) {
			return false;
		}
		return $result->result("array");
	}

	/**
	 * @param EvnReanimatPeriod_model $callObject
	 * @param $data
	 * @return array|bool
	 */
	public static function getToReanimationFromFewPS(EvnReanimatPeriod_model $callObject, $data)
	{
		$params = [
			"Person_id" => isset($data["Person_id"]) ? $data["Person_id"] : null,
			'Lpu_id' => isset($data['Lpu_id']) ? $data['Lpu_id'] : null,
			'MedService_id' => isset($data['MedService_id']) ? $data['MedService_id'] : null
		];
		if ($data['Status'] == 'ManyEvnPS') {
			$query = "
				select
					EPS.EvnPS_NumCard as \"EvnPS_NumCard\",
				    EPS.EvnPS_id as \"EvnPS_id\",
				    to_char(EPS.EvnPS_setDate, '{$callObject->dateTimeForm104}') as \"EvnPS_setDate\",
				    EPS.LpuSection_id as \"LpuSection_id\",
				    LS.LpuSection_FullName as \"LpuSection_FullName\",
				    PA.Person_Fio as \"Person_Fio\",
				    ES.EvnSection_id as \"EvnSection_id\"
				from
					v_EvnPS EPS 
					inner join dbo.v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id
					inner join dbo.v_LpuSection LS on EPS.LpuSection_id  = LS.LpuSection_id
					left join v_Person_all PA on PA.Server_id = EPS.Server_id
						and PA.Person_id = EPS.Person_id
					    and PA.PersonEvn_id = EPS.PersonEvn_id
				where EPS.Lpu_id = :Lpu_id
				  and EPS.Person_id = :Person_id
				  and EPS.EvnPS_setDate <= getdate()
				  and EPS.EvnPS_disDate is null
				  and EPS.LpuSection_id is not null
				order by EPS.EvnPS_setDate desc
			";
		} else {
			$query = "
				select
					MS.MedService_id as \"EvnPS_id\",
				    coalesce(LB.LpuBuilding_Name, '')||'/'||coalesce(LU.LpuUnit_Name, '')||'/'||coalesce(LS.LpuSection_Name, '')||'/'||MS.MedService_Name as \"LpuSection_FullName\",     
					MedService_id as \"EvnPS_NumCard\",
				    null as \"EvnPS_setDate\",
				    MedService_id as \"LpuSection_id\",
				    MedService_id as \"EvnSection_id\"
				from
					v_MedService MS 
					inner join dbo.MedServiceType MST on MS.MedServiceType_id = MST.MedServiceType_id
					left join v_LpuSection LS on MS.LpuSection_id = LS.LpuSection_id
					left join v_LpuUnit LU on MS.LpuUnit_id = LU.LpuUnit_id
					left join v_LpuBuilding LB on MS.LpuBuilding_id = LB.LpuBuilding_id
				where MS.Lpu_id = :Lpu_id 
				  	  and MS.MedService_endDT is null    -- BOB - 18.09.2019
					  and MST.MedServiceType_SysNick = 'reanimation'					  
			".(isset($params['MedService_id']) ? "and MS.MedService_id <> :MedService_id" : "")       ;  //BOB - 02.10.2019

		}
		/**@var CI_DB_result $result */
		$result = $callObject->db->query($query, $params);
		sql_log_message('error', 'from dbo.v_EvnPS exec query: ', getDebugSql($query, $params));		
		
		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}
}