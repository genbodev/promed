<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Rate_model - модель, для работы с людьми
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       SWAN Developers
* @version      ?
*/


class Rate_model extends CI_Model {

	/**
	 * Конструктор
	 */
	function __construct()
    {
        parent::__construct();
    }

	/**
	 * @param $data
	 * @return bool
	 */
	function loadRateList($data) {
		$query = "
			select	
				RateType_id as rate_id, 
				RateType_Name as rate_name,
				rvt.RateValueType_SysNick as rate_type/*,
				RateType_SysNick,
				RateType_IsPersonDirect,
				RateType_Template,
				RateType_Min,
				RateType_Max*/
			from
				RateType rt (nolock)
				left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
		";
		
		$queryParams = array();		
		$response = array();
		
		$result = $this->db->query($query, $queryParams);		
		
		if ( is_object($result) ) {
			$response/*['data']*/ = $result->result('array');
		} else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadRateValueList($data) {
		$ratetype_id = isset($data['ratetype_id']) && $data['ratetype_id'] != "" ? $data['ratetype_id'] : 0;
	
		$query = "
			select	
				RateValue_id as value_id, 
				RateValue_Name as value_name
			from
				RateValue (nolock)
			where
				RateType_id = $ratetype_id
		";

		$queryParams = array();		
		$response = array();
		
		$result = $this->db->query($query, $queryParams);		
		
		if ( is_object($result) ) {
			$response/*['data']*/ = $result->result('array');
		} else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadRateListGrid($data) {
		$rate_type = $data["rate_type"];
		$queryParams = array();
		$response = array();
		$query = "";
		
		if ($rate_type == "person") {
			$query = "
				select 
					rt.RateType_id as id,
					rvt.RateValueType_SysNick as type,
					rt.RateType_Name as name,
					(
						case rvt.RateValueType_SysNick
						when 'float' then cast(r.Rate_ValueFloat as varchar)
						when 'string' then cast(r.Rate_ValueStr as varchar)	
						else cast(r.Rate_ValueInt as varchar) end
					) as value
				from 
					PersonRate pr (nolock)
					left join Rate r (nolock) on r.Rate_id = pr.Rate_id
					left outer join RateType rt (nolock) on rt.RateType_id = r.RateType_id
					left outer join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
				where 
					pr.PersonMeasure_id = :rate_subid
			";
			$queryParams["rate_subid"] = $data["rate_subid"];
		}
		
		if ($rate_type == "evnusluga") {
			$query = "
				select 
					rt.RateType_id as id,
					rvt.RateValueType_SysNick as type,
					rt.RateType_Name as name,
					(
						case rvt.RateValueType_SysNick
						when 'float' then cast(r.Rate_ValueFloat as varchar)
						when 'string' then cast(r.Rate_ValueStr as varchar)	
						else cast(r.Rate_ValueInt as varchar) end
					) as value
				from 
					EvnUslugaRate pr (nolock)
					left join Rate r (nolock) on r.Rate_id = pr.Rate_id
					left outer join RateType rt (nolock) on rt.RateType_id = r.RateType_id
					left outer join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
				where 
					pr.EvnUsluga_id = :rate_subid
			";
			$queryParams["rate_subid"] = $data["rate_subid"];
		}
		
		if ($query != "") {
			$result = $this->db->query($query, $queryParams);		
			
			if ( is_object($result) ) {
				$response['data'] = $result->result('array');
			} else {
				return false;
			}
		}
		
		if (isset($response['data'])) { //по необходимости ищем и пересылаем данные по справочникам			
			$ref_id = array();
			$refdata = array();
			for ($i = 0; $i < count($response['data']); $i++) if($response['data'][$i]['type'] == 'reference') {
				$ref_id[] = $response['data'][$i]['id'];
			}
			if (count($ref_id) > 0) {
				$query = "
					select
						RateType_id as rate_id,
						RateValue_id as value_id, 
						RateValue_Name as value_name
					from
						RateValue (nolock)
					where
						RateType_id in (".join($ref_id, ',').")
					order by
						RateType_id
				";
				
				$result = $this->db->query($query, array());
			
				if ( is_object($result) ) {
					$rsp = $result->result('array');
					for ($i = 0; $i < count($rsp); $i++) {
						$refdata[$rsp[$i]['rate_id']][$rsp[$i]['value_id']] = $rsp[$i]['value_name'];
					}
					for ($i = 0; $i < count($response['data']); $i++) {
						if (isset($refdata[$response['data'][$i]['id']]))
							$response['data'][$i]['refdata'] = $refdata[$response['data'][$i]['id']];
					}					
				}
			}
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getPersonMeasure($data) {
		$query = "
			select
				TOP 1
				PersonMeasure_id,
				convert(varchar,PersonMeasure_setDT,104) as PersonMeasure_setDT_Date,
				substring(convert(varchar,PersonMeasure_setDT,108),1,5) as PersonMeasure_setDT_Time,
				Lpu_id,
				LpuSection_id,
				MedPersonal_id
			from
				PersonMeasure (nolock)
			where
				Person_id = :person_id
		";

		$queryParams = array();		
		$response = array();
		
		$queryParams['person_id'] = $data['person_id'];
		
		$result = $this->db->query($query, $queryParams);		
		
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			$response['success'] = false;
		}

		return $response;
	}

	/**
	 * @param $data
	 */
	function savePersonMeasures($data) {
		ConvertFromWin1251ToUTF8($data['data']);		
		$dt = (array) json_decode($data['data']);

		foreach($dt as $id => $val) {
			$this->saveMeasure(
				$data['Person_id'],
				(array) $val,
				array(
					'Lpu_id' => $data['Lpu_id'],
					'Server_id' => $data['Server_id'],
					'pmUser_id' => $data['pmUser_id']					
				)
			);
		}

		return array(array('Error_Msg' => null));
	}

	/**
	 * @param $person_id
	 * @param $data
	 * @param $sysdata
	 * @return bool
	 */
	function saveMeasure($person_id, $data, $sysdata) { //сохранение конкретного измерения
		$queryParams = array();		
		$response = array();		
		switch ($data['Record_Status']) {
			case 0: {$action = 'add'; break;}
			case 2: {$action = 'edit'; break;}
			case 3: {$action = 'delete'; break;}
			default: {$action = 'add'; break;}
		}
		
		$data['data'] = $data['RateGrid_Data'];
		$personmeasure_id = $action != 'add' ? $data['PersonMeasure_id'] : 0;
		
		if ($action == 'delete') {
			$this->deleteRate(array('rate_type' => 'person', 'rate_subid' => $personmeasure_id));
			
			$query = "
				delete from
					PersonMeasure					
				where
					PersonMeasure_id = :PersonMeasure_id
			";
			$result = $this->db->query($query, array('PersonMeasure_id' => $personmeasure_id));
			return true;
		}
	
		$settime = '';
		if ($data['PersonMeasure_setDT_Date'] != "") {
			$settime = substr($data['PersonMeasure_setDT_Date'], 0, strpos($data['PersonMeasure_setDT_Date'],"T"));
			if ($data['PersonMeasure_setDT_Time'] != "") $settime .= " ".$data['PersonMeasure_setDT_Time'].":00";			
		}	
		
		$queryParams['Server_id'] = $sysdata['Server_id'];
		$queryParams['Person_id'] = $person_id;
		$queryParams['settime'] = $settime;
		$queryParams['Lpu_id'] = $sysdata['Lpu_id'];
		$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		$queryParams['MedPersonal_id'] = $data['MedPersonal_id'];
		$queryParams['pmUser'] = $sysdata['pmUser_id'];
		

		$this->db->trans_begin();
		switch ($action) {
			case 'add': {
				$query = "
					insert into	PersonMeasure
						(Server_id
						,Person_id
						,PersonMeasure_setDT
						,Lpu_id
						,LpuSection_id
						,MedPersonal_id
						,pmUser_insID
						,pmUser_updID
						,PersonMeasure_insDT
						,PersonMeasure_updDT)
					values
						(:Server_id
						,:Person_id
						,:settime
						,:Lpu_id
						,:LpuSection_id
						,:MedPersonal_id
						,:pmUser
						,:pmUser
						,dbo.tzGetDate()
						,dbo.tzGetDate())
				";
				break;
			}
			case 'edit': {
				$queryParams['PersonMeasure_id'] = $personmeasure_id;
				$query = "
					update PersonMeasure SET
						Server_id = :Server_id
						,Person_id = :Person_id
						,PersonMeasure_setDT = :settime
						,Lpu_id = :Lpu_id
						,LpuSection_id = :LpuSection_id
						,MedPersonal_id = :MedPersonal_id
						,pmUser_updID = :pmUser
						,PersonMeasure_updDT = dbo.tzGetDate()
					where
						PersonMeasure_id = :PersonMeasure_id
				";
				break;
			}
		}
		
		$result = $this->db->query($query, $queryParams);
		
		if ($personmeasure_id <= 0) {
			$query = "
				select top 1 
					PersonMeasure_id
				from 
					PersonMeasure (nolock)
				where
					Server_id = :Server_id and
					Person_id = :Person_id and
					PersonMeasure_setDT = :settime and
					Lpu_id = :Lpu_id and
					LpuSection_id = :LpuSection_id and
					MedPersonal_id = :MedPersonal_id and
					pmUser_insID = :pmUser
				order by
					PersonMeasure_id desc
			";
			$result = $this->db->query($query, $queryParams);		
			if (is_object($result)) {
				$response = $result->result('array');
				if (isset($response[0]['PersonMeasure_id'])) $personmeasure_id = $response[0]['PersonMeasure_id'];
			}
		}
		
		$dt = (array) json_decode($data['data']);			
		array_walk($dt, 'ConvertFromUTF8ToWin1251');
		foreach($dt as $id => $val) {
			$this->saveRate('person', $personmeasure_id, $id, $val, $sysdata);
		}
	}
	
	/**
	*  Сохранение показателей для услуг
	*  Входящие данные:
				data - строка с закодированым массивом данных
	*  На выходе: ---
	*  Используется: Polka_EvnPLDispDop_model.php
	*/
	function saveEvnUsluga($data, $sys_data) {
		for($i = 0; $i < count($data); $i++) {
			$usluga_id = $data[$i]['id'];
			
			ConvertFromWin1251ToUTF8($data[$i]['data']);		
			$dt = (array) json_decode($data[$i]['data']);
			array_walk($dt, 'ConvertFromUTF8ToWin1251');
			foreach($dt as $id => $val) {
				$this->saveRate('evnusluga', $usluga_id, $id, $val, $sys_data);
			}
		}
	}
	
	/**
	*  Сохранение  определенного значения показателя
	*  Входящие данные:
				rate_type ("person" или "evnusluga"),
				rate_subid (в зависимости от rate_type, идентификатор либо измерения либо услуги),
				ratetype_id (вид показателя),
				rate_value (значение показателя),
				sys_data (контекстная информация)
	*  На выходе: ---
	*/
	function saveRate($rate_type, $rate_subid, $ratetype_id, $rate_value, $sys_data) {	
		//определяем поле для записи в таблицу		
		$valuefield = "Rate_ValueInt";
		
		$query = "
			select 
				rvt.RateValueType_SysNick
			from 
				RateType rt (nolock)
				left join RateValueType rvt (nolock) on rvt.RateValueType_id = rt.RateValueType_id
			where
				RateType_id = :ratetype_id
		";
		$result = $this->db->query($query, array('ratetype_id' => $ratetype_id));
		if (is_object($result)) {
			$response = $result->result('array');
			if (isset($response[0]['RateValueType_SysNick'])) {
				switch ($response[0]['RateValueType_SysNick']) {
					case 'float': {$valuefield = 'Rate_ValueFloat'; break;}
					case 'string': {$valuefield = 'Rate_ValueStr'; break;}
				}
			} else {
				return false; //показателя не существует
			}
		}
		
		
		//проверяем наличие показателя в бд
		$rate_id = 0;
		$query = $rate_type == "person" ? "
			select
				pr.Rate_id
			from
				PersonRate pr (nolock)
				left join Rate r (nolock) on r.Rate_id = pr.Rate_id
			where
				PersonMeasure_id = :rate_subid 
				and RateType_id =  :ratetype_id
		" : "
			select
				pr.Rate_id
			from
				EvnUslugaRate pr (nolock)
				left join Rate r (nolock) on r.Rate_id = pr.Rate_id
			where
				EvnUsluga_id = :rate_subid 
				and RateType_id =  :ratetype_id
		";
		$result = $this->db->query($query, array('rate_subid' => $rate_subid, 'ratetype_id' => $ratetype_id));
		if (is_object($result)) {
			$response = $result->result('array');
			if (isset($response[0]['Rate_id']))
				$rate_id = $response[0]['Rate_id'];
		}
		
		if ($rate_id > 0) {
			$this->db->trans_begin();
			$query = "
				update Rate	set 
					Server_id = :Server_id
					,RateType_id = :ratetype_id
					,".$valuefield." = :rate_value
					,pmUser_updID = :pmUser
					,Rate_updDT = dbo.tzGetDate()
				where
					Rate_id = :rate_id
			";
			
			$result = $this->db->query($query, array(
				'Server_id' => $sys_data['Server_id'],
				'ratetype_id' => $ratetype_id,
				'rate_value' => $rate_value,
				'pmUser' => $sys_data['pmUser_id'],
				'rate_id' => $rate_id
			));
			$this->db->trans_commit();
		} else {
			$this->db->trans_begin();
			$query = "
				insert into Rate
					(Server_id
					,RateType_id
					,".$valuefield."
					,pmUser_insID
					,pmUser_updID
					,Rate_insDT
					,Rate_updDT)
				values
					(:Server_id
					,:ratetype_id
					,:rate_value
					,:pmUser
					,:pmUser
					,dbo.tzGetDate()
					,dbo.tzGetDate())
				
				select @@IDENTITY as rate_id
			";
			$result = $this->db->query($query, array(
				'Server_id' => $sys_data['Server_id'],
				'ratetype_id' => $ratetype_id,
				'rate_value' => $rate_value,
				'pmUser' => $sys_data['pmUser_id']
			));
			
			$result = $this->db->query("select @@IDENTITY as rate_id", array());			
			
			if (is_object($result)) {
				$response = $result->result('array');
				if (isset($response[0]['rate_id']))
					$rate_id = $response[0]['rate_id'];
			}			
			$this->db->trans_commit();
			
			if ($rate_id > 0) {
				$query = $rate_type == "person" ? "
					insert into PersonRate
						(Server_id
						,PersonMeasure_id
						,Rate_id
						,pmUser_insID
						,pmUser_updID
						,PersonRate_insDT
						,PersonRate_updDT)
					values
						(:Server_id
						,:rate_subid
						,:rate_id
						,:pmUser
						,:pmUser
						,dbo.tzGetDate()
						,dbo.tzGetDate())
				" : "
					insert into EvnUslugaRate
					   (Server_id
					   ,EvnUsluga_id
					   ,Rate_id
					   ,pmUser_insID
					   ,pmUser_updID
					   ,EvnUslugaRate_insDT
					   ,EvnUslugaRate_updDT)
					values
						(:Server_id
						,:rate_subid
						,:rate_id
						,:pmUser
						,:pmUser
						,dbo.tzGetDate()
						,dbo.tzGetDate())
				";
				$result = $this->db->query($query, array(
					'Server_id' => $sys_data['Server_id'],
					'rate_subid' => $rate_subid,
					'rate_id' => $rate_id,
					'pmUser' => $sys_data['pmUser_id']
				));
			}
		}	
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function loadPersonMeasureList($data) {
		$query = "
			select
				-- select
				PersonMeasure_id,
				convert(varchar,PersonMeasure_setDT,104) as date,
				ls.LpuSection_Name as lpusection_name,
				(isnull(cast(mp.MedPersonal_Code as varchar) + ' ','') + mp.Person_Fio) as medpersonal_fio,
				pm.MedPersonal_id,
				pm.LpuSection_id,
				convert(varchar,PersonMeasure_setDT,104) as PersonMeasure_setDT_Date,
				substring(convert(varchar,PersonMeasure_setDT,108),1,5) as PersonMeasure_setDT_Time,
				1 as Record_Status
				-- end select
			from
				-- from
				PersonMeasure pm (nolock)
				left join LpuSection ls (nolock) on ls.LpuSection_id = pm.LpuSection_id
				left outer join v_MedPersonal mp (nolock) on mp.MedPersonal_id = pm.MedPersonal_id
				-- end from
			where
				-- where
				pm.Person_id = :person_id
				-- end where
			order by
				-- order by
				PersonMeasure_setDT DESC
				-- end order by
		";
		
		$queryParams = array('person_id' => $data['person_id']);		
		$response = array();
		
		$get_count_query = getCountSQLPH($query);
		$get_count_result = $this->db->query($get_count_query, $queryParams);

		if ( is_object($get_count_result) ) {
			$response['data'] = array();
			$response['totalCount'] = $get_count_result->result('array');
			$response['totalCount'] = $response['totalCount'][0]['cnt'];
		} else {
			return false;
		}
		
		$query = getLimitSQLPH($query, $data['start'], $data['limit']);
		$result = $this->db->query($query, $queryParams);		
		
		if ( is_object($result) ) {
			$response['data'] = $result->result('array');
		} else {
			return false;
		}

		return $response;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deleteRate($data) {
		$rate_type = isset($data['rate_type']) ? $data['rate_type'] : 'person';
		$ratetype_id = isset($data['ratetype_id']) ? $data['ratetype_id'] : 0;
		$response = array();		
		$rate_id = 0;
		
		$query = $rate_type == "person" ? "
			select
				pr.Rate_id
			from
				PersonRate pr (nolock)
				left join Rate r (nolock) on r.Rate_id = pr.Rate_id
			where
				PersonMeasure_id = :rate_subid 
		" : "
			select
				ur.Rate_id
			from
				EvnUslugaRate ur (nolock)
				left join Rate r (nolock) on r.Rate_id = ur.Rate_id
			where
				EvnUsluga_id = :rate_subid 
		";
		
		if ($ratetype_id > 0)
			$query .= "and RateType_id = :ratetype_id";
		
		$result = $this->db->query($query, array('rate_subid' => $data['rate_subid'], 'ratetype_id' => $ratetype_id));
		if (is_object($result)) {
			$response = $result->result('array');
			for($i = 0; $i < count($response); $i++) {		
				$rate_id = isset($response[$i]['Rate_id']) ? $response[$i]['Rate_id'] : 0;
				
				if ($rate_id > 0) {	
					$this->db->trans_begin();
					$query = $rate_type == "person" ? "
						delete from PersonRate where Rate_id = :rate_id
						delete from Rate where Rate_id = :rate_id
					" : "
						delete from EvnUslugaRate where Rate_id = :rate_id
						delete from Rate where Rate_id = :rate_id
					";			
					$result = $this->db->query($query, array('rate_id' => $rate_id));
					$this->db->trans_commit();
				}				
			}
			return array('success' => true);
		}
		
		return false;
	}
}
?>