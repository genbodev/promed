<?php
defined("BASEPATH") or die ("No direct script access allowed");
require_once("EvnAbstract_model.php");
require_once("EvnReanimatPeriod_model_get.php");
require_once("EvnReanimatPeriod_model_load.php");
require_once("EvnReanimatPeriod_model_save.php");

/**
 * @property EvnDirection_model $EvnDirection_model
 * @property ReanimatRegister_model $ReanimatRegister_model
 */
class EvnReanimatPeriod_model extends EvnAbstract_model
{
	public $dateTimeForm104 = "DD.MM.YYYY";
	public $dateTimeForm108 = "HH24:MI";
	public $dateTimeForm108full = "HH24:MI:SS";
	public $dateTimeForm120 = "YYYY-MM-DD";

	#region common
	/**
	 * формирование справочников для формы редактирования реанимационного периода
	 * @return array
	 */
	function ERPEW_NSI()
	{
		/**@var CI_DB_result $result */
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'glasgow' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
	    ";
		$result = $this->db->query($query);
		$EvnScaleglasgowSrc = $result->result("array");
		$EvnScaleglasgowDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScaleglasgowSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScaleglasgowDst[$ParameterType_SysNick] = [];
			}
			$EvnScaleglasgowDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы glasgow_ch
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'glasgow_ch' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
	    ";
		$result = $this->db->query($query);
		$EvnScaleglasgow_chSrc = $result->result("array");
		$EvnScaleglasgow_chDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScaleglasgow_chSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScaleglasgow_chDst[$ParameterType_SysNick] = [];
			}
			$EvnScaleglasgow_chDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы sofa
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'sofa' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
	    ";
		$result = $this->db->query($query);
		$EvnScalesofaSrc = $result->result("array");
		$EvnScalesofaDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalesofaSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalesofaDst[$ParameterType_SysNick] = [];
			}
			$EvnScalesofaDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы apache
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'apache' 
			  and SC.ScaleParameterType_id <= 28
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScaleapacheSrc = $result->result("array");
		$EvnScaleapacheDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScaleapacheSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScaleapacheDst[$ParameterType_SysNick] = [];
			}
			$EvnScaleapacheDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы waterlow
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'waterlow' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScalewaterlowSrc = $result->result("array");
		$EvnScalewaterlowDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalewaterlowSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalewaterlowDst[$ParameterType_SysNick] = [];
			}
			$EvnScalewaterlowDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы rass
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'rass' 
			order by
				SC.ScaleParameterType_id,
				SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScalerassSrc = $result->result("array");
		$EvnScalerassDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalerassSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalerassDst[$ParameterType_SysNick] = [];
			}
			$EvnScalerassDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы hunt_hess
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'hunt_hess' 
			  and SC.ScaleParameterResult_Name not ilike 'Дополнительно%'
			order by  SC.ScaleParameterType_id,  SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScalehunt_hessSrc = $result->result("array");
		$EvnScalehunt_hessDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalehunt_hessSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalehunt_hessDst[$ParameterType_SysNick] = [];
			}
			$EvnScalehunt_hessDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы four
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'four' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScalefourSrc = $result->result("array");
		$EvnScalefourDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalefourSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalefourDst[$ParameterType_SysNick] = [];
			}
			$EvnScalefourDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы mrc
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'mrc' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScalemrcSrc = $result->result("array");
		$EvnScalemrcDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalemrcSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalemrcDst[$ParameterType_SysNick] = [];
			}
			$EvnScalemrcDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы VAScale
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'VAScale' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScaleVAScaleSrc = $result->result("array");
		$EvnScaleVAScaleDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScaleVAScaleSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScaleVAScaleDst[$ParameterType_SysNick] = [];
			}
			$EvnScaleVAScaleDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы nihss
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
			    SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
			    SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
			    SC.ScaleParameterType_id as \"ScaleParameterType_id\"
			from v_Scale SC
			where SC.ScaleType_SysNick = 'nihss' 
			order by
				SC.ScaleParameterType_id,
			    SC.ScaleParameterResult_id
		";
		$result = $this->db->query($query);
		$EvnScalenihssSrc = $result->result("array");
		$EvnScalenihssDst = [];
		$ParameterType_SysNick = "";
		foreach ($EvnScalenihssSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScalenihssDst[$ParameterType_SysNick] = [];
			}
			$EvnScalenihssDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы glasgow_neonat   //BOB - 20.02.2020
		$query = "		
			select 
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'glasgow_neonat' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScaleglasgow_neonatSrc = $result->result("array");
		$EvnScaleglasgow_neonatDst = [];	
		$ParameterType_SysNick = "";
		foreach ($EvnScaleglasgow_neonatSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];
				$EvnScaleglasgow_neonatDst[$ParameterType_SysNick] = [];
			}
			$EvnScaleglasgow_neonatDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы psofa   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\", 
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'psofa' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalepsofaSrc = $result->result("array");
		$EvnScalepsofaDst = [];	
		$ParameterType_SysNick = "";
		$EvnScalepsofaDst["cardiovascular"] = [];
		$EvnScalepsofaDst["renal"] = [];
		foreach ($EvnScalepsofaSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalepsofaDst[$ParameterType_SysNick] = [];
			}
			$EvnScalepsofaDst[$ParameterType_SysNick][] = $row;

			if (strpos($row["ScaleParameterType_SysNick"], "cardiovascular") === 0)
				$EvnScalepsofaDst["cardiovascular"][] = $row;
			if (strpos($row["ScaleParameterType_SysNick"], "renal") === 0)
				$EvnScalepsofaDst["renal"][] = $row;
		}
		//Параметры шкалы psas   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\", 
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'psas' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalepsasSrc = $result->result("array");
		$EvnScalepsasDst = [];	
		$ParameterType_SysNick = "";
		foreach ($EvnScalepsasSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalepsasDst[$ParameterType_SysNick] = [];
			}
			$EvnScalepsasDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы pelod   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick  as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'pelod' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalepelodSrc = $result->result("array");
		$EvnScalepelodDst = [];	
		$ParameterType_SysNick = "";
		$EvnScalepelodDst["pressure"] = [];
		$EvnScalepelodDst["renal"] = [];
		foreach($EvnScalepelodSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalepelodDst[$ParameterType_SysNick] = [];
			}
			$EvnScalepelodDst[$ParameterType_SysNick][] = $row;

			if (strpos($row["ScaleParameterType_SysNick"], "pressure") === 0)
				$EvnScalepelodDst["pressure"][] = $row;
			if (strpos($row["ScaleParameterType_SysNick"], "renal") === 0)
				$EvnScalepelodDst["renal"][] = $row;
		}
		//Параметры шкалы npass   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick  as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'npass' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalenpassSrc = $result->result("array");
		$EvnScalenpassDst = [];	
		$ParameterType_SysNick = "";
		foreach($EvnScalenpassSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalenpassDst[$ParameterType_SysNick] = [];
			}
			$EvnScalenpassDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы comfort   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick  as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'comfort' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalecomfortSrc = $result->result("array");
		$EvnScalecomfortDst = [];	
		$ParameterType_SysNick = "";
		foreach($EvnScalecomfortSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalecomfortDst[$ParameterType_SysNick] = [];
			}
			$EvnScalecomfortDst[$ParameterType_SysNick][] = $row;
		}
		//Параметры шкалы pipp   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick  as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'pipp' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalepippSrc = $result->result("array");
		$EvnScalepippDst = [];	
		$ParameterType_SysNick = "";
		foreach($EvnScalepippSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalepippDst[$ParameterType_SysNick] = [];
			}
			$EvnScalepippDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы bind   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick  as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'bind' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalebindSrc = $result->result("array");
		$EvnScalebindDst = [];	
		$ParameterType_SysNick = "";
		foreach($EvnScalebindSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalebindDst[$ParameterType_SysNick] = [];
			}
			$EvnScalebindDst[$ParameterType_SysNick][] = $row;
		}

		//Параметры шкалы nips   //BOB - 20.02.2020
		$query = "		
			select
				SC.ScaleParameterType_SysNick  as \"ScaleParameterType_SysNick\",
				SC.ScaleParameterResult_Name as \"ScaleParameterResult_Name\",
				SC.ScaleParameterResult_id as \"ScaleParameterResult_id\",
				SC.ScaleParameterResult_Value::int8 as \"ScaleParameterResult_Value\",
				SC.ScaleParameterType_id as \"ScaleParameterType_id\"
				from v_Scale SC
				where SC.ScaleType_SysNick = 'nips' 
				order by
					SC.ScaleParameterType_id,
					SC.ScaleParameterResult_id
		";
        	$result = $this->db->query($query);
		$EvnScalenipsSrc = $result->result("array");
		$EvnScalenipsDst = [];	
		$ParameterType_SysNick = "";
		foreach($EvnScalenipsSrc as $row) {
			if ($row["ScaleParameterType_SysNick"] != $ParameterType_SysNick) {
				$ParameterType_SysNick = $row["ScaleParameterType_SysNick"];				
				$EvnScalenipsDst[$ParameterType_SysNick] = [];
			}
			$EvnScalenipsDst[$ParameterType_SysNick][] = $row;
		}
				
		/*регулярное наблюдение состояния**********************************************************************************************************************************/		
		
		//Стороны
		$query = "		
			select
				SideType_id as \"SideType_id\",
				SideType_Name as \"SideType_Name\",
				SideType_SysNick as \"SideType_SysNick\"
			from SideType
			order by SideType_id
		";
		$ReanimConditParam_SideType = $this->db->query($query)->result("array");


		/*реанимационные мероприятия**********************************************************************************************************************************/		
		//Лекарственные Средства  //BOB - 05.03.2020
		$query = "		
			select
				ReanimDrugType_id as \"ReanimDrugType_id\",
				ReanimDrugType_Name as \"ReanimDrugType_Name\"
			from v_ReanimDrugType
			order by ReanimDrugType_id
		";
		$ReanimDrugType = $this->db->query($query)->result("array");

		$ReturnObject = [		
			"EvnScaleglasgow" => $EvnScaleglasgowDst,
			"EvnScaleglasgow_ch" => $EvnScaleglasgow_chDst,
			"EvnScalesofa" => $EvnScalesofaDst,
 			"EvnScaleapache" => $EvnScaleapacheDst,
			"EvnScalewaterlow" => $EvnScalewaterlowDst,
			"EvnScalerass" => $EvnScalerassDst,
			"EvnScalehunt_hess" => $EvnScalehunt_hessDst,
			"EvnScalefour" => $EvnScalefourDst,	
			"EvnScalemrc" => $EvnScalemrcDst,	
			"EvnScaleVAScale" => $EvnScaleVAScaleDst,	
			"EvnScalenihss" => $EvnScalenihssDst,
			"EvnScaleglasgow_neonat" => $EvnScaleglasgow_neonatDst,
			"EvnScalepsofa" => $EvnScalepsofaDst,
			"EvnScalepsas" => $EvnScalepsasDst,
			"EvnScalepelod" => $EvnScalepelodDst,
			"EvnScalenpass" => $EvnScalenpassDst,
			"EvnScalecomfort" => $EvnScalecomfortDst,
			"EvnScalepipp" => $EvnScalepippDst,
			"EvnScalebind" => $EvnScalebindDst,
			"EvnScalenips" => $EvnScalenipsDst,
			"SideType" => $ReanimConditParam_SideType,
			"ReanimDrugType" => $ReanimDrugType,
			"Message" => ""
		];
		return $ReturnObject;
	}

	/**
	 * Завершение реанимационного периода проверка - а есть ли подготовка данных для окна
	 * @param $data
	 * @return array
	 */
	function endReanimatReriod($data)
	{
		$ReturnObject = ["EvnReanimatPeriod_id" => "", "Status" => "", "Message" => ""];
		//Выборка определяющая не находится ли пациент в данный момент в реанимации
		$params = [
			"Person_id" => $data["Person_id"],
			"Server_id" => $data["Server_id"],
			"PersonEvn_id" => $data["PersonEvn_id"]
		];
		$query = "
			select EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
			from v_EvnReanimatPeriod ERP 
			where ERP.Person_id = :Person_id
			  and ERP.EvnReanimatPeriod_setDate <= getdate()
			  and ERP.EvnReanimatPeriod_disDate is null
		";
		/**@var CI_DB_result $result */
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$ReturnObject["Status"] = "Oshibka";
			$ReturnObject["Message"] = "Ошибка при выполнении запроса к базе данных";
			return $ReturnObject;
		}
		$resp = $result->result("array");
		//если нет реанимационного периода
		if (count($resp) == 0) {
			$ReturnObject["Status"] = "NotInReanimation";
			$ReturnObject["Message"] = "Данный пациент не находится в реанимации";
			return $ReturnObject;
		}
		$ReturnObject["Status"] = "DoneSuccessfully";
		$ReturnObject["Message"] = "Нашли голубчика";
		$ReturnObject["EvnReanimatPeriod_id"] = $resp[0]["EvnReanimatPeriod_id"];
		return ($ReturnObject);


	}

	/**
	 * Проверка завершения реанимационных периодов и исхода последнего РП при завершении движения
	 * @param $data
	 * @return array
	 */
	function checkEvnSectionByRPClose($data)
	{
		$ReturnObject = ["Status" => "norm", "Message" => ""];
		// Ищу РП отсортировав их по дате начала
		$params = ["EvnSection_id" => $data["EvnSection_id"]];
		$query = "
			select
				EvnReanimatPeriod_disDT as \"EvnReanimatPeriod_disDT\",
			    to_char(ERP.EvnReanimatPeriod_disDT, '{$this->dateTimeForm120}') as \"EvnReanimatPeriod_disDate\",
			    EvnReanimatPeriod_disTime as \"EvnReanimatPeriod_disTime\",
			    ERP.ReanimResultType_id as \"ReanimResultType_id\",
			    RRT.ReanimResultType_Name as \"ReanimResultType_Name\" 
			from
				v_EvnReanimatPeriod ERP 
				left join dbo.ReanimResultType RRT on ERP.ReanimResultType_id  = RRT.ReanimResultType_id 
			where EvnReanimatPeriod_pid = :EvnSection_id
			order by EvnReanimatPeriod_setDT desc
		";
		$result = $this->db->query($query, $params)->result("array");
		if (count($result) == 0) {
			//	сообщение НОРМ			
			$ReturnObject["Status"] = "norm";
			$ReturnObject["Message"] = "Реанимационные периоды отсутствуют.";
			return $ReturnObject;
		}
		if ($result[0]["EvnReanimatPeriod_disDT"] == null) {
			//	сообщение НЕ НОРМ - РП не закрыт
			$ReturnObject["Status"] = "stop";
			$ReturnObject["Message"] = "Реанимационный период не закрыт!";
			return $ReturnObject;
		}
		//Сравнение дат-времён закрытия РП и Движения
		$hour = (int)substr($data["EvnSection_disTime"], 0, 2);
		$minute = (int)substr($data["EvnSection_disTime"], 3, 2);
		$month = (int)substr($data["EvnSection_disDate"], 5, 2);
		$day = (int)substr($data["EvnSection_disDate"], 8, 2);
		$year = (int)substr($data["EvnSection_disDate"], 0, 4);
		$EvnSection_disDT = mktime($hour, $minute, 0, $month, $day, $year);

		$hour = (int)substr($result[0]["EvnReanimatPeriod_disTime"], 0, 2);
		$minute = (int)substr($result[0]["EvnReanimatPeriod_disTime"], 3, 2);
		$month = (int)substr($result[0]["EvnReanimatPeriod_disDate"], 5, 2);
		$day = (int)substr($result[0]["EvnReanimatPeriod_disDate"], 8, 2);
		$year = (int)substr($result[0]["EvnReanimatPeriod_disDate"], 0, 4);
		$EvnReanimatPeriod_disDT = mktime($hour, $minute, 0, $month, $day, $year);

		//ЕСЛИ дата закрытия РП > даты закрытия движения
		if ($EvnReanimatPeriod_disDT > $EvnSection_disDT) {
			$ReturnObject["Status"] = "ask";
			$ReturnObject["Message"] = "Закрытие Реанимационного периода (" . str_pad($day, 2, "0", STR_PAD_LEFT) . "." . str_pad($month, 2, "0", STR_PAD_LEFT) . "." . $year . " " . str_pad($hour, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minute, 2, "0", STR_PAD_LEFT) . ") позднее закрытия Движения!";
			return $ReturnObject;
		}
		//СРавнение исходов РП и ДВижения
		// Код исхода движения перевожу в SysNick
		$params = ["LeaveType_id" => $data["LeaveType_id"]];
		$query = "
			select LeaveType_SysNick as \"LeaveType_SysNick\"
			from LeaveType 
			where LeaveType_id = :LeaveType_id
		";
		$LeaveType = $this->db->query($query, $params)->result('array');
		if(
			in_array($result[0]['ReanimResultType_id'],[2,3])
			&& !in_array($LeaveType[0]['LeaveType_SysNick'],['die','ksdie','ksdiepp','diepp','dsdie','dsdiepp','kslet','ksletitar'])
			|| !in_array($result[0]['ReanimResultType_id'],[2,3])
			&& in_array($LeaveType[0]['LeaveType_SysNick'],['die','ksdie','ksdiepp','diepp','dsdie','dsdiepp','kslet','ksletitar'])
		){
			$ReturnObject["Status"] = "ask";
			$ReturnObject["Message"] = "Исходы Реанимационного периода (" . $result[0]["ReanimResultType_Name"] . ") и Движения не соответствуют друг другу!";
			return $ReturnObject;
		}
		return $ReturnObject;
	}

	/**
	 * Проверка завершения реанимационных периодов и исхода последнего РП при попытке выписки
	 * @param $data
	 * @return array
	 */
	function checkBeforeLeave($data)
	{
		$ReturnObject = ["success" => true, "Error_Msg" => ""];
		// Ищу РП отсортировав их по дате начала
		$params = ["EvnSection_id" => $data["EvnSection_id"]];
		$query = "
			select
				EvnReanimatPeriod_disDT as \"EvnReanimatPeriod_disDT\",
			    to_char(ERP.EvnReanimatPeriod_disDT, '{$this->dateTimeForm120}') as \"EvnReanimatPeriod_disDate\",
			    EvnReanimatPeriod_disTime as \"EvnReanimatPeriod_disTime\",
			    ERP.ReanimResultType_id as \"ReanimResultType_id\",
			    RRT.ReanimResultType_Name as \"ReanimResultType_Name\" 
			from
				v_EvnReanimatPeriod ERP 
				left join dbo.ReanimResultType RRT on ERP.ReanimResultType_id  = RRT.ReanimResultType_id 
			where EvnReanimatPeriod_pid = :EvnSection_id
			order by EvnReanimatPeriod_setDT desc
		";
		$result = $this->db->query($query, $params)->result("array");
		//ЕСЛИ нет ни одного РП
		if (count($result) == 0) {
			//	сообщение НОРМ			
			$ReturnObject["success"] = true;
			$ReturnObject["Error_Msg"] = "";
			return $ReturnObject;
		}
		//ЕСЛИ поздняя запись незавершённая
		if (empty($result[0]["EvnReanimatPeriod_disDT"] )) {
			//	сообщение НЕ НОРМ - РП не закрыт
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "Реанимационный период не закрыт!";
			return $ReturnObject;
		}
		//СРавнение исходов РП и ДВижения
		// Код исхода движения перевожу в SysNick

		 $LeaveType_SysNick = $this->getFirstResultFromQuery("
			select LeaveType_SysNick as \"LeaveType_SysNick\"
			from LeaveType 
			where LeaveType_id = :LeaveType_id
		", [
                  'LeaveType_id' => $data['LeaveType_id']
              ]);

              if ($LeaveType_SysNick !== false && !empty($LeaveType_SysNick) )
              {
                  $deathLeaveTypes = [ 'die', 'dsdie', 'ksdie' ];

                  // ЕСЛИ исходы РП и движения не соответствуют  (код исхода движения перевести в SysNick и уже оперировать с ним)
                  if (
                      ($result[0]['ReanimResultType_id'] > 1 && !in_array($LeaveType_SysNick, $deathLeaveTypes))
                      || ($result[0]['ReanimResultType_id'] == 1 && in_array($LeaveType_SysNick, $deathLeaveTypes))
                  )
                  {
			
			//сообщение НЕ НОРМ - исходы РП и движения не соответствуют
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "Исход Реанимационного периода (" . $result[0]["ReanimResultType_Name"] . ") и вид выписки не соответствуют друг другу!";
			return $ReturnObject;
	            	}
            }
		return $ReturnObject;
	}

	/**
	 * Проверка завершения реанимационных периодов при попытке удаления КВС или движения
	 * @param $data
	 * @return array|bool
	 */
	function checkBeforeDelEvn($data)
	{
		$ReturnObject = ["success" => true, "Error_Msg" => ""];
		if ($data["Object"] == "EvnPS") {
			$EvnReanimatPeriod_ = "EvnReanimatPeriod_rid";
		} elseif ($data["Object"] == "EvnSection") {
			$EvnReanimatPeriod_ = "EvnReanimatPeriod_pid";
		} else {
			return false;
		}
		$params = ["Object_id" => $data["Object_id"]];
		$query = "
			select EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
			from v_EvnReanimatPeriod ERP 
			where {$EvnReanimatPeriod_} = :Object_id
		";
		$result = $this->db->query($query, $params)->result("array");
		//ЕСЛИ нет ни одного открытого РП
		if (count($result) == 0) {
			//сообщение НОРМ			
			$ReturnObject["success"] = true;
			$ReturnObject["Error_Msg"] = "";
		} else {
			//ЕСЛИ поздняя запись незавершённая
			//сообщение НЕ НОРМ - РП не закрыт
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "Имеется Реанимационный период!";
		}
		return $ReturnObject;
	}

	/**
	 * Удаление реанимационного периода из ЭМК
	 * @param $data
	 * @return array
	 */
	function deleteEvnReanimatPeriod($data)
	{
		$ReturnObject = ["success" => true, "Error_Msg" => ""];
		$params = ["EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"]];
		$query = "
			select count(*) as \"Evn_Count\"
			from Evn 
			where Evn_pid = :EvnReanimatPeriod_id
			  and Evn_deleted < 2
		";
		$Evn = $this->db->query($query, $params)->result('array');
		if ($Evn[0]["Evn_Count"] > 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "Реанимационный период содержит дочерние объекты. <br> Удаление невозможно.";
			return $ReturnObject;
		}
		$query = "
 			select count(*) as \"Evn_Count\"
 			from (
 				select EvnReanimatPeriod_id 
				from
					ReanimatPeriodPrescrLink RPPL
					inner join v_EvnPrescr EP on EP.EvnPrescr_id = RPPL.EvnPrescr_id
				where EvnReanimatPeriod_id=:EvnReanimatPeriod_id
				union all
				select EvnReanimatPeriod_id 
				  from dbo.ReanimatPeriodDirectLink  RPDL
				  left join dbo.v_EvnDirection_All ED on ED.EvnDirection_id = RPDL.EvnDirection_id
				where EvnReanimatPeriod_id=:EvnReanimatPeriod_id
			) as children
		";
		$Evn = $this->db->query($query, $params)->result("array");
		if ($Evn[0]["Evn_Count"] > 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "Реанимационный период содержит прикреплённые назначения и/или направления. <br> Удаление невозможно.";
			return $ReturnObject;
		}
		$pmUser_id = $this->sessionParams["pmuser_id"];
		$params = [
			"EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"],
			"pmUser_id" => $pmUser_id
		];
		$query = "
			select
			    error_code as \"Error_Code\",
			    error_message as \"Error_Message\"
			from p_evnreanimatperiod_del(
			    evnreanimatperiod_id := :EvnReanimatPeriod_id,
			    pmuser_id := :pmUser_id
			);
		";
		$this->db->query($query, $params);
		if ((!empty($response[0]["Error_Code"])) || (!empty($response[0]["Error_Msg"]))) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "Ошибка при удалении Реанимационного периода: <br>" . (!empty($response[0]["Error_Code"]) ? $response[0]["Error_Code"] : "") . (!empty($response[0]["Error_Msg"]) ? $response[0]["Error_Msg"] : "");
			return $ReturnObject;
		}
		//поиск неудалённых РП
		$params = ["EvnReanimatPeriod_id" => $data["EvnReanimatPeriod_id"]];
		$query = "
			select
				(select Person_id from Evn where Evn_id = :EvnReanimatPeriod_id) as \"Person_id\",
				(
					select EvnReanimatPeriod_id
					from v_EvnReanimatPeriod ERP 
					where ERP.Person_id = (select Person_id from Evn where Evn_id = :EvnReanimatPeriod_id)
					order by ERP.EvnReanimatPeriod_setDT desc
					limit 1
				) as \"EvnReanimatPeriod_id\",
				(
					select ReanimatRegister_id
					from ReanimatRegister RR 
					where RR.Person_id = (select Person_id from Evn where Evn_id = :EvnReanimatPeriod_id)
				) as \"ReanimatRegister_id\"
		";
		$Evn = $this->db->query($query, $params)->result("array");
		if (empty($Evn[0]["EvnReanimatPeriod_id"])) {
			//ЕСЛИ отсутствуют
			if (!empty($Evn[0]["ReanimatRegister_id"])) {
				//	удаление записи регистра реанимации
				$params = [
					"ReanimatRegister_id" => $Evn[0]["ReanimatRegister_id"],
					"pmUser_id" => $pmUser_id
				];
				$query = "
					select
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_reanimatregister_del(
					    reanimatregister_id := :ReanimatRegister_id,
					    pmuser_id := :pmUser_id,
					    isremove := 2
					);				
				";
				$this->db->query($query, $params);
			}
		} else {
			//ИНАЧЕ - присутствуют
			if (!empty($Evn[0]["ReanimatRegister_id"])) {
				//	установить предыдущий код РП и снять пометку о РП в данный момент
				$params = [
					"EvnReanimatPeriod_id" => $Evn[0]["EvnReanimatPeriod_id"],
					"ReanimatRegister_id" => $Evn[0]["ReanimatRegister_id"],
					"pmUser_id" => $pmUser_id
				];
				$query = "
					select
					    reanimatregister_id as \"ReanimatRegister_id\",
					    error_code as \"Error_Code\",
					    error_message as \"Error_Message\"
					from p_reanimatregister_upd(
					    reanimatregister_id := :ReanimatRegister_id,
					    evnreanimatperiod_id := :EvnReanimatPeriod_id,
					    reanimatregister_isperiodnow := 1,
					    pmuser_id := :pmUser_id
					);
				";
				$this->db->query($query, $params);
			}
		}
		return $ReturnObject;
	}

	/**
	 * Удаление реанимационного периода из АРМ-ов стационара и реаниматолога
	 * @param $data
	 * @return array
	 */
	function delReanimatPeriod($data)
	{
		$ReturnObject = ["success" => true, "Error_Msg" => ""];
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select ERP.EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
			from v_EvnReanimatPeriod ERP
			where ERP.Person_id = :Person_id
			order by ERP.EvnReanimatPeriod_setDT desc
		";
		$Evn = $this->db->query($query, $params)->result("array");
		if (count($Evn) === 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject["success"] = false;
			$ReturnObject["Error_Msg"] = "У данного пациента отсутствует Реанимационный период.";
			return $ReturnObject;
		}
		$data["EvnReanimatPeriod_id"] = $Evn[0]["EvnReanimatPeriod_id"];
		$ReturnObject = $this->deleteEvnReanimatPeriod($data);
		return $ReturnObject;

	}
	#endregion common
	#region get
	/**
	 * Возвращает антропометрические данные конкретного пациента за определённый период
	 * @param $data
	 * @return array
	 */
	function getAntropometrData($data)
	{
		return EvnReanimatPeriod_model_get::getAntropometrData($this, $data);
	}

	/**
	 * Возвращает данные о дыхании аускультативно
	 * @param $data
	 * @return array|bool
	 */
	function GetBreathAuscultative($data)
	{
		return EvnReanimatPeriod_model_get::GetBreathAuscultative($this, $data);
	}

	/**
	 * Формирование списка пациентов в отделениях, относящихся к реанимационной службе
	 * @param $data
	 * @return bool|string|null
	 */
	function getReanimSectionPatientList($data)
	{
		return EvnReanimatPeriod_model_get::getReanimSectionPatientList($this, $data);
	}

	/**
	 * формирование дерева корректирующих параметров шкалы APACHE
	 * @param $node
	 * @return array
	 */
	function getapache_TreeData($node)
	{
		return EvnReanimatPeriod_model_get::getapache_TreeData($this, $node);
	}

	/**
	 * Извлечение данных Сердечно-лёгочной реанимации
	 * @param $data
	 * @return array|bool
	 */
	function GetCardPulm($data)
	{
		return EvnReanimatPeriod_model_get::GetCardPulm($this, $data);
	}

	/**
	 * получение данных шкал и мероприятий для нового наблюдения
	 * @param $data
	 * @return array|bool
	 */
	function getDataToNewCondition($data)
	{
		return EvnReanimatPeriod_model_get::getDataToNewCondition($this, $data);
	}

	/**
	 * загрузка таблици дополнительных документов прикреплённых к направлению
	 * @param $data
	 * @return array|bool
	 */
	function getDirectionLinkedDocs($data)
	{
		return EvnReanimatPeriod_model_get::getDirectionLinkedDocs($this, $data);
	}

	/**
	 * Получение из БД данных конкретного расчёта (исследования) по шкале -
	 * @param $data
	 * @return array|bool
	 */
	function getEvnScaleContent($data)
	{
		return EvnReanimatPeriod_model_get::getEvnScaleContent($this, $data);
	}

	/**
	 * Получение из БД данных конкретного расчёта (исследования) по шкале для ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function getEvnScaleContentEMK($data)
	{
		return EvnReanimatPeriod_model_get::getEvnScaleContentEMK($this, $data);
	}

	/**
	 * Извлечение данных параметров ИВЛ
	 * @param $data
	 * @return array|bool
	 */
	function GetParamIVL($data)
	{
		return EvnReanimatPeriod_model_get::GetParamIVL($this, $data);
	}

	/**
	 * Извлечение данных периодических измерений, проводимых в рамках реанимационных мероприятий
	 * @param $data
	 * @return array|bool
	 */
	function GetReanimatActionRate($data)
	{
		return EvnReanimatPeriod_model_get::GetReanimatActionRate($this, $data);
	}

	/**
	 * Формирование параметров для окна редактирования реанимационного периода
	 * @param $arg
	 * @return array|bool
	 */
	function getParamsERPWindow($arg)
	{
		return EvnReanimatPeriod_model_get::getParamsERPWindow($this, $arg);
	}

	/**
	 * Возвращает Id первого попавшегося отделения обслуживаемого данной службой реанимации
	 * @param $data
	 * @return string
	 */
	function getProfilSectionId($data)
	{
		return EvnReanimatPeriod_model_get::getProfilSectionId($this, $data);
	}

	/**
	 * список пациентов переведённых в реанимацию для отображения в дереве на АРМ реаниматолога
	 * куча полей неизвестного назначения, оставил их чтобы не нарушить полноту данных в узле дерева, запрос делал по образцу
	 * @param $data
	 * @return array|bool
	 */
	function getReanimationPatientList($data)
	{
		return EvnReanimatPeriod_model_get::getReanimationPatientList($this, $data);
	}

	/**
	 * Индикация нескольких карт выбывшего из стационара для выбора для перевода в реанимацию
	 * @param $data
	 * @return array|bool
	 */
	function getToReanimationFromFewPS($data)
	{
		return EvnReanimatPeriod_model_get::getToReanimationFromFewPS($this, $data);
	}
	#endregion get
	#region load
	/**
	 * индикация реанимационных периодов на форме "движения" в рамках формы ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function loadEvnReanimatPeriodViewData($data)
	{
		return EvnReanimatPeriod_model_load::loadEvnReanimatPeriodViewData($this, $data);
	}

	/**
	 * загрузка таблици реанимационных периодов в окно КВС
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnReanimatPeriodGrid_PS($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnReanimatPeriodGrid_PS($this, $data);
	}

	/**
	 * загрузка таблици результатов расчётов (исследований) по шкалам
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnScaleGrid($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnScaleGrid($this, $data);
	}

	/**
	 * формирование справочников для формы редактирования реанимационного периода
	 * @return array|bool
	 */
	function loadReanimatSyndromeType()
	{
		return EvnReanimatPeriod_model_load::loadReanimatSyndromeType($this);
	}

	/**
	 * загрузка таблици направлений
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnDirectionGrid($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnDirectionGrid($this, $data);
	}

	/**
	 * загрузка таблици назначений
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnPrescrGrid($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnPrescrGrid($this, $data);
	}

	/**
	 * загрузка данных реанимационных мероприятий для ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnReanimatActionEMK($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnReanimatActionEMK($this, $data);
	}

	/**
	 * загрузка таблици реанимационных мероприятий
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnReanimatActionGrid($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnReanimatActionGrid($this, $data);
	}

	/**
	 * загрузка таблици регулярного наблюдения состояния
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnReanimatConditionGrid($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnReanimatConditionGrid($this, $data);
	}

	/**
	 * загрузка регулярного наблюдения состояния для ЭМК
	 * @param $data
	 * @return array|bool
	 */
	function loudEvnReanimatConditionGridEMK($data)
	{
		return EvnReanimatPeriod_model_load::loudEvnReanimatConditionGridEMK($this, $data);
	}

    /**
     * @param $data
     * @return array|bool
     */
	function loudEvnDrugCourseGrid($data)
    {
        return EvnReanimatPeriod_model_load::loudEvnDrugCourseGrid($this, $data);
    }

    /**
     * @param $data
     * @return array|bool
     */
    function loudEvnPrescrTreatDrugGrid($data)
    {
        return EvnReanimatPeriod_model_load::loudEvnPrescrTreatDrugGrid($this, $data);
    }
	#endregion load
	#region save
	/**
	 * Перевод пациента в реанимацию из АРМ-ов стационара и реаниматора проверка не находится ли пациент уже в реанимации формирование реанимационного периода
	 * @param $data
	 * @return array
	 */
	function moveToReanimation($data)
	{
		return EvnReanimatPeriod_model_save::moveToReanimation($this, $data);
	}

	/**
	 * Перевод пациента в реанимацию из АРМа мобильного стационара
	 * @param $data
	 * @return array
	 */
	function mMoveToReanimation($data)
	{
		return EvnReanimatPeriod_model_save::mMoveToReanimation($this, $data);
	}

	/**
	 * Отправка сообщения всему персоналу на службе реанимации
	 * @param $data
	 * @return array
	 */
	function sendCallReanimateTeamMessage($data)
	{
		return EvnReanimatPeriod_model_save::sendCallReanimateTeamMessage($this, $data);
	}

	/**
	 * Получение списка реанимационных служб по МО
	 * @param $data
	 * @return array
	 */
	function getReanimationServices($data)
	{
		return EvnReanimatPeriod_model_save::getReanimationServices($this, $data);
	}

	/**
	 * Перевод пациента в реанимацию из АРМ приёмного отделения проверка не находится ли пациент уже в реанимации формирование реанимационного периода запись в регистр реанимации сбор реквизитов для открытия окна реанимационного периода
	 * @param $data
	 * @return array
	 */
	function moveToReanimationFromPriem($data)
	{
		return EvnReanimatPeriod_model_save::moveToReanimationFromPriem($this, $data);
	}

	/**
	 * Перевод пациента в реанимацию минуя приёмное отделене проверка не находится ли пациент уже в реанимации нахождение Движения в переданнной КВС формирование реанимационного периода запись в регистр реанимации сбор реквизитов для открытия окна реанимационного периода
	 * @param $data
	 * @return array
	 */
	function moveToReanimationOutPriem($data)
	{
		return EvnReanimatPeriod_model_save::moveToReanimationOutPriem($this, $data);
	}

	/**
	 * создание прикрепления направления к РП
	 * @param $data
	 * @return array|bool
	 */
	function ReanimatPeriodDirectLink_Save($data)
	{
		return EvnReanimatPeriod_model_save::ReanimatPeriodDirectLink_Save($this, $data);
	}

	/**
	 * создание прикрепления назначения к РП
	 * @param $data
	 * @return array|bool
	 */
	function ReanimatPeriodPrescrLink_Save($data)
	{
		return EvnReanimatPeriod_model_save::ReanimatPeriodPrescrLink_Save($this, $data);
	}

	/**
	 * Сохранение в БД данных конкретного реанимационного наблюдения состояния
	 * 
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function EvnReanimatCondition_Save($data)
	{
		return EvnReanimatPeriod_model_save::EvnReanimatCondition_Save($this, $data);
	}

	/**
	 * удаление записи регулярного наблюдения состояния
	 * @param $data
	 * @return array|bool
	 */
	function EvnReanimatCondition_Del($data)
	{
		return EvnReanimatPeriod_model_save::EvnReanimatCondition_Del($this, $data);
	}

	/**
	 * Сохранение изменений реанимационного периода
	 * @param $arg
	 * @return array|bool
	 */
	function EvnReanimatPeriod_Save($arg)
	{
		return EvnReanimatPeriod_model_save::EvnReanimatPeriod_Save($this, $arg);
	}

	/**
	 * Сохранение в БД данных конкретного расчёта по шкале -
	 * @param $data
	 * @return array|bool
	 */
	function EvnScale_Save($data)
	{
		return EvnReanimatPeriod_model_save::EvnScale_Save($this, $data);
	}

	/**
	 * удаление записи шкалы
	 * @param $data
	 * @return array|bool
	 */
	function EvnScales_Del($data)
	{
		return EvnReanimatPeriod_model_save::EvnScales_Del($this, $data);
	}

	/**
	 * удаление записи мероприятия
	 * @param $data
	 * @return array|bool
	 * @throws Exception
	 */
	function EvnReanimatAction_Del($data)
	{
		return EvnReanimatPeriod_model_save::EvnReanimatAction_Del($this, $data);
	}


	/**
	 * Сохранение в БД данных конкретного реанимационного мероприятия -
	 * @param $data
	 * @return array|bool
	 */
	function EvnReanimatAction_Save($data)
	{
		return EvnReanimatPeriod_model_save::EvnReanimatAction_Save($this, $data);
	}
	#endregion save
	
	    /**
     * проверка можно ли переводить из одной реанимации в другую
     * BOB - 02.10.2019
     */
    function changeReanimatPeriodCheck($data)
    {
        //echo '<pre>'.'  $data  ' . print_r($data, 1) . '</pre>'; //BOB - 14.03.2017
		//возвращаемый объект
		$ReturnObject = array(   'success' => true,
								'Error_Msg' => '',
								'EvnReanimatPeriod_id' => '',
								'MedService_id' => '',
								'EvnPS_id' => '',
								'EvnSection_id' => '',
								'LpuSection_id' => ''
			);

		$params = array(
				'EvnReanimatPeriod_rid' => $data['EvnPS_id'],
				'Person_id' => $data['Person_id']
				);
		$query = "
					select 
                        EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\", 
                        EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\", 
                        EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\", 
                        LpuSection_id as \"LpuSection_id\", 
                        MedService_id as \"MedService_id\", 
                        Lpu_id as \"Lpu_id\"
					  from v_EvnReanimatPeriod 
					 where Person_id = :Person_id
					   and EvnReanimatPeriod_disDT is null
				";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>changeReanimatPeriodCheck exec query: ', getDebugSql($query, $params));

		if(count($Evn) === 0) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'У данного пациента отсутствует Реанимационный период.';
			return $ReturnObject;
		}

		$ReturnObject['EvnReanimatPeriod_id'] = $Evn[0]['EvnReanimatPeriod_id'];
		$ReturnObject['MedService_id'] = $Evn[0]['MedService_id'];
		$ReturnObject['EvnPS_id'] = $Evn[0]['EvnReanimatPeriod_rid'];
		$ReturnObject['EvnSection_id'] = $Evn[0]['EvnReanimatPeriod_pid'];
		$ReturnObject['LpuSection_id'] = $Evn[0]['LpuSection_id'];

		$params['Lpu_id'] = $Evn[0]['Lpu_id'];

		$query = "
					select 
                        * 
                    from 
                        v_MedService 
					 where Lpu_id = :Lpu_id
					   and MedServiceType_id = 67
					   and MedService_endDT is NULL
				";
		$Evn = $this->db->query($query, $params)->result('array');
		//sql_log_message('error', 'EvnReanimatPeriod_model=>changeReanimatPeriodCheck exec query: ', getDebugSql($query, $params));

		if(count($Evn) < 2) {
			//сообщение НЕ НОРМ - имеются дочерние сущности
			$ReturnObject['success'] = false;
			$ReturnObject['Error_Msg'] = 'В данной МО одна служба реанимации - переводить некуда.';
			return $ReturnObject;
		}
		return $ReturnObject;
	}

    /**
     * перевод из одной реанимации в другую
     * BOB - 02.10.2019
     * @param $data
     * @return array|bool
     */
    public function changeReanimatPeriod($data)
    {
        //возвращаемый объект
        $ReturnObject = [
            'success' => true,
            'Error_Msg' => '',
            'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
            'MedService_id' => $data['MedService_id']
        ];

        $params = [
            'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id'],
            'MedService_id' => $data['MedService_id']
        ];

        $query = "
            select
                EvnReanimatPeriod_setDT as \"EvnReanimatPeriod_setDT\",
                GetDate() as \"EvnReanimatPeriod_disDT\",
                EvnReanimatPeriod_pid as \"EvnReanimatPeriod_pid\",
                EvnReanimatPeriod_rid as \"EvnReanimatPeriod_rid\", 
                ReanimReasonType_id as \"ReanimReasonType_id\",
                LpuSectionBedProfile_id as \"LpuSectionBedProfile_id\",
                LpuSection_id as \"LpuSection_id\", 
                Lpu_id as \"Lpu_id\",
                Server_id as \"Server_id\",
                PersonEvn_id as \"PersonEvn_id\",
                Person_id as \"Person_id\",
                EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\"
            from
                v_EvnReanimatPeriod
            where
                EvnReanimatPeriod_id = :EvnReanimatPeriod_id
            limit 1
        ";
        $reanimatePeriod = $this->queryResult($query, $params);
        
        if(!$reanimatePeriod) return false;
        $reanimatePeriod = $reanimatePeriod[0];
        $reanimatePeriod_setDT = $reanimatePeriod['EvnReanimatPeriod_setDT'];
        $reanimatePeriod_disDT = $reanimatePeriod['EvnReanimatPeriod_disDT'];
        
        if ($reanimatePeriod_setDT > $reanimatePeriod_disDT) {
            return array_merge($ReturnObject, ['success' => false, 'Error_Msg' => '~Дата начало исходного РП превышает дату окончания - текущую дату!']);
        } else {
            # нахожу родительское движение
            $query = "
                select
                    EvnSection_setDT as \"EvnSection_setDT\",
                    EvnSection_disDT as \"EvnSection_disDT\" 
				from
				    dbo.v_EvnSection
				where
				    EvnSection_id = :EvnReanimatPeriod_pid
                limit 1
            ";
            $evnSection = $this->queryResult($query, $reanimatePeriod)[0];
            
            # дата окончания РП должна быть в пределах дат «движения»
            if(!(
                    $reanimatePeriod_disDT >= $evnSection['EvnSection_setDT']
                and
                    ($reanimatePeriod_disDT < $evnSection['EvnSection_disDT'] or is_null($evnSection['EvnSection_disDT']))
            )) {
                return array_merge($ReturnObject, ['success' => false, 'Error_Msg' => '~Окончание РП - текущая дата вне периода Движения']);
            }

            # нахожу максимальную дату начала дочерних сущностей
            $query = "
                select (
                   select Evn_setDT
                   from v_Evn
                   where Evn_pid = :EvnReanimatPeriod_id
                   order by Evn_setDT desc
                   limit 1
               ) as \"Child_setDT_Max\",
               (
                   select Evn_disDT
                   from v_Evn
                   where Evn_pid = :EvnReanimatPeriod_id
                   order by Evn_disDT desc
                   limit 1
               ) as \"Child_disDT_Max\"
            ";
            
            $childData = $this->queryResult($query, $reanimatePeriod)[0];

            if (
                (!is_null($childData['Child_disDT_Max'])) and ($reanimatePeriod_disDT < $childData['Child_disDT_Max']) 
                or 
                ((!is_null($childData['Child_setDT_Max'])) and ($reanimatePeriod_disDT < $childData['Child_setDT_Max']))
            ) {
                return array_merge($ReturnObject, ['success' => false, 'Error_Msg' => '~Окончание РП - текущая дата раньше окончания или начала дочернего события']);
            } 
        }
        
        $pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь


        //   Закрытие РП с исходом – «перевод в другую службу реанимации»
        $params = [
            'EvnReanimatPeriod_id' => $reanimatePeriod['EvnReanimatPeriod_id'],
            'EvnReanimatPeriod_pid' => $reanimatePeriod['EvnReanimatPeriod_pid'],
            'EvnReanimatPeriod_rid' => $reanimatePeriod['EvnReanimatPeriod_rid'],
            'EvnReanimatPeriod_setDT' => $reanimatePeriod['EvnReanimatPeriod_setDT'],
            'EvnReanimatPeriod_disDT' => $reanimatePeriod['EvnReanimatPeriod_disDT'],
            'ReanimReasonType_id' => $reanimatePeriod['ReanimReasonType_id'],
            'ReanimResultType_id' => 4,
            'LpuSectionBedProfile_id' => $reanimatePeriod['LpuSectionBedProfile_id'],
            'LpuSection_id' => $reanimatePeriod['LpuSection_id'],
            'MedService_id' => $data['MedService_id'],
            'Lpu_id' => $reanimatePeriod['Lpu_id'],
            'Server_id' => $reanimatePeriod['Server_id'],
            'PersonEvn_id' => $reanimatePeriod['PersonEvn_id'],
            'Person_id'  => $reanimatePeriod['Person_id'],
            'pmUser_id' => $pmUser_id
        ];

        $query = "
            select 
                EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_EvnReanimatPeriod_upd
			(  					
                EvnReanimatPeriod_id := :EvnReanimatPeriod_id,
                EvnReanimatPeriod_pid := :EvnReanimatPeriod_pid,
                EvnReanimatPeriod_setDT := :EvnReanimatPeriod_setDT, 
                EvnReanimatPeriod_disDT := :EvnReanimatPeriod_disDT, 
                ReanimReasonType_id := :ReanimReasonType_id,
                ReanimResultType_id := :ReanimResultType_id,
                LpuSectionBedProfile_id := :LpuSectionBedProfile_id,
                Lpu_id := :Lpu_id, 
                Server_id := :Server_id, 
                PersonEvn_id := :PersonEvn_id,
                pmUser_id := :pmUser_id
			)
		";
        
        $result = $this->db->query($query, $params);
        if ( !is_object($result) ) return false;

        $resultArray = $result->result('array');
        if ((empty($resultArray[0]['EvnReanimatPeriod_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Msg']))){
            $ReturnObject['success'] = 'false';
            $ReturnObject['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Msg'];
            return $ReturnObject;
        }

        // Закрытие всех незакрытых дочерних наблюдений и реанимационных мероприятий, может ещё и измерений.			
        $query = "
            select
                EvnReanimatAction_id as \"EvnReanimatAction_id\",
                ReanimatActionType_id as \"ReanimatActionType_id\",
                UslugaComplex_id as \"UslugaComplex_id\",
                EvnUsluga_id as \"EvnUsluga_id\",
                ReanimDrugType_id as \"ReanimDrugType_id\",
                EvnReanimatAction_DrugDose as \"EvnReanimatAction_DrugDose\",
                EvnDrug_id as \"EvnDrug_id\",
                EvnReanimatAction_MethodCode as \"EvnReanimatAction_MethodCode\",
                EvnReanimatAction_ObservValue as \"EvnReanimatAction_ObservValue\",
                ReanimatCathetVeins_id as \"ReanimatCathetVeins_id\",
                CathetFixType_id as \"CathetFixType_id\",
                EvnReanimatAction_CathetNaborName as \"EvnReanimatAction_CathetNaborName\",
                NutritiousType_id as \"NutritiousType_id\",
                EvnReanimatAction_DrugUnit as \"EvnReanimatAction_DrugUnit\",
                EvnReanimatAction_MethodTxt as \"EvnReanimatAction_MethodTxt\",
                EvnReanimatAction_NutritVol as \"EvnReanimatAction_NutritVol\",
                EvnReanimatAction_NutritEnerg as \"EvnReanimatAction_NutritEnerg\"
            from
                dbo.v_EvnReanimatAction
            where
                EvnReanimatAction_pid = :EvnReanimatPeriod_id
            and
                EvnReanimatAction_disDT is null
        ";
        $result = $this->db->query($query, $params);
        
        if ( !is_object($result) ) return false;

        $Response = $result->result('array');

        $query = "
            select
                EvnReanimatAction_id as EvnReanimatAction_id,
                Error_Code as Error_Code,
                Error_Message as Error_Msg
            from p_EvnReanimatAction_upd
            (
                EvnReanimatAction_id := :EvnReanimatAction_id,
                EvnReanimatAction_pid := :EvnReanimatPeriod_id, 
                EvnReanimatAction_disDT := :EvnReanimatPeriod_disDT,
                Lpu_id := :Lpu_id, 
                Server_id := :Server_id, 
                PersonEvn_id := :PersonEvn_id,
                ReanimatActionType_id := :ReanimatActionType_id,
                UslugaComplex_id := :UslugaComplex_id,
                EvnUsluga_id := :EvnUsluga_id,
                ReanimDrugType_id := :ReanimDrugType_id,
                EvnReanimatAction_DrugDose := :EvnReanimatAction_DrugDose,
                EvnDrug_id := :EvnDrug_id,
                EvnReanimatAction_MethodCode := :EvnReanimatAction_MethodCode,
                EvnReanimatAction_ObservValue := :EvnReanimatAction_ObservValue,
                ReanimatCathetVeins_id := :ReanimatCathetVeins_id,
                CathetFixType_id := :CathetFixType_id,
                EvnReanimatAction_CathetNaborName := :EvnReanimatAction_CathetNaborName,
                NutritiousType_id := :NutritiousType_id,
                EvnReanimatAction_DrugUnit := :EvnReanimatAction_DrugUnit,						 
                EvnReanimatAction_MethodTxt := :EvnReanimatAction_MethodTxt,
                EvnReanimatAction_NutritVol := :EvnReanimatAction_NutritVol,  
                EvnReanimatAction_NutritEnerg := :EvnReanimatAction_NutritEnerg,
                EvnReanimatAction_setDT := :EvnReanimatAction_setDT,
                pmUser_id := :pmUser_id 
            ) 
        ";
        foreach($Response as &$row ) {
            $params['EvnReanimatAction_id'] = $row['EvnReanimatAction_id'];
            $params['ReanimatActionType_id'] = $row['ReanimatActionType_id'];
            $params['UslugaComplex_id'] = $row['UslugaComplex_id'];
            $params['EvnUsluga_id'] = $row['EvnUsluga_id'];
            $params['ReanimDrugType_id'] = $row['ReanimDrugType_id'];
            $params['EvnReanimatAction_DrugDose'] = $row['EvnReanimatAction_DrugDose'];
            $params['EvnDrug_id'] = $row['EvnDrug_id'];
            $params['EvnReanimatAction_MethodCode'] = $row['EvnReanimatAction_MethodCode'];
            $params['EvnReanimatAction_ObservValue'] = $row['EvnReanimatAction_ObservValue'];
            $params['ReanimatCathetVeins_id'] = $row['ReanimatCathetVeins_id'];
            $params['CathetFixType_id'] = $row['CathetFixType_id'];
            $params['EvnReanimatAction_CathetNaborName'] = $row['EvnReanimatAction_CathetNaborName'];
            $params['NutritiousType_id'] = $row['NutritiousType_id'];
            $params['EvnReanimatAction_DrugUnit'] = $row['EvnReanimatAction_DrugUnit'];
            $params['EvnReanimatAction_MethodTxt'] = $row['EvnReanimatAction_MethodTxt'];
            $params['EvnReanimatAction_NutritVol'] = $row['EvnReanimatAction_NutritVol'];
            $params['EvnReanimatAction_NutritEnerg'] = $row['EvnReanimatAction_NutritEnerg'];
            $params['EvnReanimatAction_setDT'] = $row['EvnReanimatAction_setDT'];
            
            $result = $this->db->query($query, $params);
            //sql_log_message('error', 'p_EvnReanimatAction_upd exec query: ', getDebugSql($query, $params));
            if ( !is_object($result) )
                return false;

        }


        //   Формирование нового РП, 
	$params["ReanimatAgeGroup_id"] = $callObject->getFirstResultFromQuery(
		"select case when cast(cast(getdate() as date) as timestamp) - interval '29 day' < PS.Person_BirthDay then 1
		        when cast(cast(getdate() as date) as timestamp) - interval '29 day' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '1 year' < PS.Person_BirthDay then 2
		        when cast(cast(getdate() as date) as timestamp) - interval '1 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '4 year' < PS.Person_BirthDay then 3
		        when cast(cast(getdate() as date) as timestamp) - interval '4 year' >= PS.Person_BirthDay and cast(cast(getdate() as date) as timestamp) - interval '18 year' < PS.Person_BirthDay then 4
		        else 5 end
		 from	v_PersonState PS
		        inner join PersonEvn PE on PE.Person_id = PS.Person_id
		 where	PE.PersonEvn_id = :PersonEvn_id", $params);
		
        $query = "
			select
				EvnReanimatPeriod_id as \"EvnReanimatPeriod_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnReanimatPeriod_ins(
				EvnReanimatPeriod_pid := :EvnReanimatPeriod_pid,
				EvnReanimatPeriod_rid := :EvnReanimatPeriod_rid,
				Lpu_id := :Lpu_id,
				Server_id := :Server_id,
				MedService_id := :MedService_id,
				LpuSection_id := :LpuSection_id,
				ReanimResultType_id := null,
				ReanimReasonType_id := 1,
				LpuSectionBedProfile_id := null, 
				ReanimatAgeGroup_id := :ReanimatAgeGroup_id,
				PersonEvn_id := :PersonEvn_id,
				EvnReanimatPeriod_setDT := getdate() + interval '1 minute',
				EvnReanimatPeriod_disDT := null,
				EvnReanimatPeriod_didDT := null,
				Morbus_id := null,
				EvnReanimatPeriod_IsSigned := null,
				pmUser_signID := null,
				EvnReanimatPeriod_signDT := null,
				EvnStatus_id := null,
				EvnReanimatPeriod_statusDate := null,
				isReloadCount := null,
				pmUser_id := :pmUser_id
            )
		";
        $result = $this->db->query($query, $params); //BOB - 12.09.2018
        sql_log_message('error', 'p_EvnReanimatPeriod_ins exec query: ', getDebugSql($query, $params));
        if ( !is_object($result) ) return false;

        $resultArray = $result->result('array');
        if ((empty($resultArray[0]['EvnReanimatPeriod_id'])) || (!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Msg']))){
            $ReturnObject['success'] = 'false';
            $ReturnObject['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Msg'];
            return $ReturnObject;
        }

        $params['EvnReanimatPeriod_id'] = $resultArray[0]['EvnReanimatPeriod_id'];
        $ReturnObject['EvnReanimatPeriod_id'] = $resultArray[0]['EvnReanimatPeriod_id'];
        //   Изменение кода РП в записи регистра реанимации

        $query = "
            with cte as (
                select
                    ReanimatRegister_id
                from
                    dbo.ReanimatRegister RR 
                where
                    RR.Person_id = :Person_id
            )
            select
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
            from dbo.p_ReanimatRegister_upd
            (
                ReanimatRegister_id := (select ReanimatRegister_id from cte),
                EvnReanimatPeriod_id := :EvnReanimatPeriod_id,
                ReanimatRegister_IsPeriodNow := 2, -- да
                pmUser_id := :pmUser_id
            )
        ";

        $result = $this->db->query($query, $params); //BOB - 12.09.2018
        sql_log_message('error', 'p_EvnReanimatPeriod_ins exec query: ', getDebugSql($query, $params));
        if ( !is_object($result) ) return false;

        $resultArray = $result->result('array');
        if ((!empty($resultArray[0]['Error_Code'])) ||(!empty($resultArray[0]['Error_Msg']))){
            $ReturnObject['success'] = 'false';
            $ReturnObject['Error_Msg'] = $resultArray[0]['Error_Code'].'~'.$resultArray[0]['Error_Msg'];
            return $ReturnObject;
        }
        
        return $ReturnObject;
    }

	/**
     * Печать списка пациентов
     * BOB - 24/12/2019
     * @return bool
     */
	function printPatientList($data)
	{
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id']
		);

		$query = "
			select 	 ROW_NUMBER() OVER(ORDER BY EvnReanimatPeriod_setDT desc) AS \"Record_Num\",
			EvnPS.EvnPS_NumCard as \"EvnPS_NumCard\",
			Person_all.Person_FirName as \"Person_Firname\",
		    Person_all.Person_SecName as \"Person_Secname\",
			Person_all.Person_SurName as \"Person_Surname\",
			coalesce(to_char(Person_all.Person_BirthDay, 'dd.mm.yyyy'), '') as \"Person_Birthday\",
			coalesce(to_char(EvnReanimatPeriod.EvnReanimatPeriod_setDate, 'dd.mm.yyyy'), '') as \"EvnPS_setDate\",
			coalesce(to_char(EvnReanimatPeriod.EvnReanimatPeriod_disDate, 'dd.mm.yyyy'), '') as \"EvnPS_disDate\",
			date_part('day', EvnReanimatPeriod.EvnReanimatPeriod_setDate - now()) as \"EvnPS_KoikoDni\",
			'-' as \"LpuSectionWard_name\",
			coalesce(PT.PayType_Name, '') as \"PayType_Name\"

			from v_EvnReanimatPeriod EvnReanimatPeriod
				LEFT JOIN v_Person_all Person_all  on Person_all.Server_id = EvnReanimatPeriod.Server_id and
							Person_all.Person_id = EvnReanimatPeriod.Person_id and Person_all.PersonEvn_id = EvnReanimatPeriod.PersonEvn_id
				inner JOIN v_EvnPS EvnPS  on EvnPS.EvnPS_id = EvnReanimatPeriod.EvnReanimatPeriod_rid
				inner JOIN v_EvnSection EvnS  on EvnS.EvnSection_id = EvnReanimatPeriod.EvnReanimatPeriod_pid
				LEFT JOIN v_PayType PT  on PT.PayType_id = EvnS.PayType_id

			WHERE EvnReanimatPeriod.MedService_id = :MedService_id
				and EvnReanimatPeriod.Lpu_id = :Lpu_id
				and EvnReanimatPeriod.EvnReanimatPeriod_setDate <= now()
				and EvnReanimatPeriod.EvnReanimatPeriod_disDate is null
		";
		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}

	/**
     * BOB - 07.11.2019
     * создание прикрепления курса лекарств к РП
     */
	function ReanimatPeriodDrugCourse_Save($data) {
		$Response = array (
			'success' => 'true',
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'Error_Msg' => '');

		//$pmUser_id = $this->sessionParams['pmuser_id']; //текущий пользователь


		$params = array(
			'EvnCourseTreat_id' => $data['EvnCourseTreat_id'],
			'pmUser_id' => $this->sessionParams['pmuser_id'], //текущий пользователь,
			'EvnReanimatPeriod_id' => $data['EvnReanimatPeriod_id']
		);

		$query = "
			select EvnPrescrTreat_id as \"EvnPrescrTreat_id\" from v_EvnPrescrTreat
			where EvnCourse_id = :EvnCourseTreat_id
		";
		$result = $this->db->query($query, $params);
		sql_log_message('error', 'select EvnPrescrTreat_id exec query: ', getDebugSql($query, $params));

		if ( !is_object($result) )
			return false;

		$EvnPrescrTreatResult = $result->result('array');

		foreach ($EvnPrescrTreatResult as $row) {
			$params['EvnPrescr_id'] = $row['EvnPrescrTreat_id'];

            $query = "
                select
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\",
                    ReanimatPeriodPrescrLink_id as \"ReanimatPeriodPrescrLink_id\"
                from dbo.p_ReanimatPeriodPrescrLink_ins
                    (
                     EvnReanimatPeriod_id := :EvnReanimatPeriod_id,
					 EvnPrescr_id := :EvnPrescr_id,
      				 pmUser_id := :pmUser_id
                    )";
			
			$result = $this->db->query($query, $params);
			sql_log_message('error', 'p_ReanimatPeriodDirectLink_ins exec query: ', getDebugSql($query, $params));


			if ( !is_object($result) )
				return false;

			$EvnScaleResult = $result->result('array');

			if (!(($EvnScaleResult[0]['ReanimatPeriodPrescrLink_id']) && ($EvnScaleResult[0]['Error_Code'] == null) && ($EvnScaleResult[0]['Error_Msg'] == null))){
				$Response['success'] = 'false';
				$Response['Error_Msg'] = $EvnScaleResult[0]['Error_Code'].' '.$EvnScaleResult[0]['Error_Msg'];
				return $Response;
			}
		}

		return $Response;
	}	
    
}