<?php

/**
 * @arr
 */
class NarcoRevise_model extends swPgModel {

	/**
	 *  constructor
	 */
	function __construct() {
		parent::__construct();
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadNarcoReviseEditWindow($data) {
		$sql = "
			SELECT
				RL.ReviseList_id as \"ReviseList_id\",
				RL.ReviseList_Code as \"ReviseList_Code\",
				to_char(RL.ReviseList_setDate, 'dd.mm.yyyy') as \"ReviseList_setDate\",
				RL.PermitType_id as \"PermitType_id\",
				org.Org_Nick as \"Org_id\",
				RL.ReviseList_Kolvo as \"ReviseList_Kolvo\",
				RL.ReviseList_MatchKolvo as \"ReviseList_MatchKolvo\",
				RL.ReviseList_Performer as \"ReviseList_Performer\"
			FROM r19.ReviseList RL
			left join v_Org org on RL.Org_id = org.org_id
			where ReviseList_id = :ReviseList_id
		";
		$result = $this->db->query($sql, array('ReviseList_id' => $data['ReviseList_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadNarcoReviseListDataLink($data) {
		
		if (!(($data['start'] >= 0) && ($data['limit'] >= 0)))
		{
			return false;
		}
		$query = "
			SELECT
			-- select
				PS.Person_id as \"Person_id\",
				ps.Server_id as \"Server_id\",
				RLD.ReviseList_id as \"ReviseList_id\",
				RLDL.ReviseListDataLink_id as \"ReviseListDataLink_id\",
				RLD.ReviseListData_ProcurId as \"ReviseListData_ProcurId\",
				PS.Person_SurName || ' ' || LEFT(PS.Person_FirName,1) || ' ' || LEFT(PS.Person_SecName,1) as \"Person_FIO\",
				case when Sex_id =1 then 'М' else 'Ж' end as \"Person_Sex\",
				to_char(ps.Person_birthday, 'dd.mm.yyyy') as \"Person_Birthday\",
				Diag.diag_FullName as \"Diag_Name\",
				Lpu.Lpu_Nick as \"Lpu_Nick\",
				PS.Document_Ser as \"Document_Ser\",
				PS.Document_Num as \"Document_Num\",
				CCEST.CrazyCauseEndSurveyType_Name as \"PersonRegisterOutCause_Name\",
				to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"PersonRegister_setDate\",
				to_char(PR.PersonRegister_disDate, 'dd.mm.yyyy') as \"PersonRegister_disDate\"
				-- end select
			FROM 
			-- from
				v_PersonState PS
				inner join PersonRegister PR on PR.Person_id = PS.Person_id
				inner join MorbusType mt on mt.MorbusType_id = pr.MorbusType_id and mt.MorbusType_SysNick = 'narc'
				inner join r19.v_ReviseListDataLink RLDL on RLDL.PersonRegister_id = PR.PersonRegister_id
				inner join r19.v_ReviseListData RLD on RLD.ReviseListData_id = RLDL.ReviseListData_id
				left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
				left join v_Lpu Lpu on Lpu.Lpu_id = PR.Lpu_iid
				left join v_MorbusCrazy MO on MO.Morbus_id = PR.Morbus_id
				left join v_CrazyCauseEndSurveyType CCEST on CCEST.CrazyCauseEndSurveyType_id = MO.CrazyCauseEndSurveyType_id
			-- end from
			where 
			-- where
				RLD.ReviseList_id = :ReviseList_id
			-- end where
			order by  
			-- order by
				RLDL.ReviseListDataLink_id
			-- end order by
			";
		$params = array('ReviseList_id' => $data['ReviseList_id']);
		//echo getDebugSQL(getLimitSQL($query, $data['start'], $data['limit']), $params);die();
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);
		
		if (is_object($result_count))
		{
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		}
		else
		{
			$count = 0;
		}
		if (is_object($result))
		{
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		}
		else
		{
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function loadNarcoReviseList($data) {
		$queryFilter = "Where (1=1)
			";
		$queryParams = array();
		if (!empty($data['PermitType_id'])) {
			$queryFilter.="and PT.PermitType_id = :PermitType_id
				";
			$queryParams['PermitType_id'] = $data['PermitType_id'];
		}
		
		if (isset($data['ReviseList_setDate'][0])) {
			$queryFilter .= " and RL.ReviseList_setDate >= cast(:ReviseList_setDate_0 as datetime) ";
			$queryParams['ReviseList_setDate_0'] = $data['ReviseList_setDate'][0] . ' 00:00:00';
		}

		if (isset($data['ReviseList_setDate'][1])) {
			$queryFilter .= " and RL.ReviseList_setDate <= cast(:ReviseList_setDate_1 as datetime) ";
			$queryParams['ReviseList_setDate_1'] = $data['ReviseList_setDate'][1] . ' 23:59:59';
		}
		if (!empty($data['ReviseList_Performer'])) {
			$queryFilter.="and RL.ReviseList_Performer ilike :ReviseList_Performer
				";
			$queryParams['ReviseList_Performer'] = '%' . $data['ReviseList_Performer'] . '%';
		}
		if (!empty($data['isMatch'])) {
			if ($data['isMatch'] == 1) {
				$queryFilter.="and (RL.ReviseList_MatchKolvo is null or RL.ReviseList_MatchKolvo=0)
				";
			} else {
				$queryFilter.="and RL.ReviseList_MatchKolvo > 0
				";
			}
		}

		$query = "
			SELECT
				RL.ReviseList_id as \"ReviseList_id\",
				RL.ReviseList_Code as \"ReviseList_Code\",
				to_char(RL.ReviseList_setDate, 'dd.mm.yyyy') as \"ReviseList_setDate\",
				PT.PermitType_Name as \"PermitType_Name\",
				org.Org_Nick as \"Org_Nick\",
				RL.ReviseList_Kolvo as \"ReviseList_Kolvo\",
				RL.ReviseList_MatchKolvo as \"ReviseList_MatchKolvo\",
				RL.ReviseList_Performer as \"ReviseList_Performer\"
			FROM r19.ReviseList RL
				left join v_Org org on RL.Org_id = org.org_id
				left join r19.PermitType PT on PT.permitType_id = RL.PermitType_id
			{$queryFilter}
		";
		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * @arr
	 */
	function DBFtoArray($data) {
		$fields_mapping = array();
		if (!is_file($data['file'])) {
			throw new Exception($data['file'] . " не найден");
		}
		$dbf = dbase_open($data['file'], 0);
		if ($dbf === FALSE) {
			throw new Exception("Не удалось открыть файл " . $data['file']);
		}
		$dbf_header = dbase_get_header_info($dbf);
		if ($dbf_header === FALSE) {
			throw new Exception("Информация в заголовке базы данных " . $data['file'] . " не может быть прочитана");
		}
		//$this->db->query("delete from raw.SkladOstat;");//Перед каждой загрузкой очищаем таблицу.
		$ddl = array();
		$conv = array();
		if (0 == count($fields_mapping)) {
			$fields_mapping_empty = true;
		} else {
			$fields_mapping_empty = false;
		}
		foreach ($dbf_header as $dbf_field) {
			switch ($dbf_field['type']) {
				case 'character':
					$dbf_field['type'] = 'VARCHAR(' . $dbf_field['length'] . ')';
					$conv[] = $dbf_field['name'];
					break;
				default:
					$dbf_field['type'] = 'VARCHAR(4000)';
					break;
			}
			$ddl[] = '[' . $dbf_field['name'] . '] ' . $dbf_field['type'];
			if ($fields_mapping_empty) {
				$fields_mapping[$dbf_field['name']] = $dbf_field['name'];
			}
		}
		$cnt = dbase_numrecords($dbf);
		$values = array();
		for ($i = 1; $i <= $cnt; $i++) {
			$row = dbase_get_record_with_names($dbf, $i);
			foreach ($conv as $f_idx) {
				$row[$f_idx] = iconv('cp866', 'UTF-8', $row[$f_idx]);
			}
			foreach ($fields_mapping as $source_field => $destination_field) {
				$values[$i][$source_field] = $row[$source_field];
			}
		}
		dbase_close($dbf);
		return $values;
	}
	/**
	 *
	 * @param array $data
	 * @return string 
	 */
	function saveNarcoReviseEditWindow($data) {
		$DBF = $this->DBFtoArray($data);
		$sql = "
			select
				ReviseList_id as \"ReviseList_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r19.p_ReviseList_ins(
				ReviseList_Code := :ReviseList_Code,
				ReviseList_setDate := :ReviseList_setDate,
				PermitType_id := :PermitType_id,
				ReviseList_Kolvo=:ReviseList_Kolvo,
				Org_id := :Org_id,
				ReviseList_Performer := :ReviseList_Performer,
				pmUser_id := :pmUser_id
			);		
";
		$params = array(
			'ReviseList_Code' => $data['ReviseList_Code'],
			'ReviseList_setDate' => $data['ReviseList_setDate'],
			'PermitType_id' => $data['PermitType_id'],
			'Org_id' => $data['Org_id'],
			'ReviseList_Kolvo' => count($DBF),
			'ReviseList_Performer' => $data['ReviseList_Performer'],
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($sql, $params);exit();
		$res = $this->db->query($sql, $params);
		if (!is_object($res)) {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД';
			return $response;
		}
		$tmp = $res->result('array');
		if (!empty($tmp[0]['Error_Msg'])) {
			$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			$response[0]['Error_Code'] = $tmp[0]['Error_Code'];
			return $response;
		}
		$data['ReviseList_id'] = $tmp[0]['ReviseList_id'];
		if ($data['ReviseList_id'] > 0) {
			foreach ($DBF as $val) {
				$val['ReviseList_id'] = $data['ReviseList_id'];
				$val['pmUser_id'] = $data['pmUser_id'];
				$result = $this->importReviseData($val);
				if (!empty($result[0]['Error_Msg'])) {
					return $result;
				}
			}
			$result = $this->saveLinkReviseList($data);
			if (!empty($result[0]['Error_Msg'])) {
				return $result;
			}
			return $tmp;
		} else {
			return false;
		}
	}
	
	/**
	 *
	 * @param type $date
	 * @param type $format
	 * @return type 
	 */
	function StrtoDate($date, $format = 'y-m-d')
	{
		if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $date, $matches))
		{//return 'char';
			$date = strtolower($format);

			$date = str_replace('d', $matches[1], $date);
			$date = str_replace('m', $matches[2], $date);
			$date = str_replace('y', $matches[3], $date);

			return $date;
		}elseif (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $date, $matches))
		{
			$date = strtolower($format);

			$date = str_replace('d', $matches[3], $date);
			$date = str_replace('m', $matches[2], $date);
			$date = str_replace('y', $matches[1], $date);

			return $date;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 *
	 * @param type $data
	 * @return string 
	 */
	function importReviseData($data) {
		$sql = "
			select
				ReviseList_id as \"ReviseList_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r19.p_ReviseListData_ins(
				ReviseList_id := :ReviseList_id,
				ReviseListData_ProcurId := :ReviseListData_ProcurId,
				ReviseListData_Surname := :ReviseListData_Surname,
				ReviseListData_Firname=:ReviseListData_Firname,
				ReviseListData_Secname := :ReviseListData_Secname,
				ReviseListData_Bithday := :ReviseListData_Bithday,
				pmUser_id := :pmUser_id
			)	
		";

		$midname = '';
		$bd='';
		if(isset($data['MIDDLE_NAM'])){
			$midname = trim($data['MIDDLE_NAM']);
		}else if(isset($data['MID_NAME'])){
			$midname = trim($data['MID_NAME']);
		}else{
			$midname = null;
		}
		$bd=$this->StrtoDate(trim($data['BIRTHDAY']));
		if($bd=='char'){
			return array(array('Error_Msg'=>'Поле "BIRTHDAY" имеет тип "char"'));
		}
		$params = array(
			'ReviseList_id' => $data['ReviseList_id'],
			'ReviseListData_ProcurId' => (isset($data['ID']))?trim($data['ID']):null,
			'ReviseListData_Surname' => trim($data['LAST_NAME']),
			'ReviseListData_Firname' => trim($data['FIRST_NAME']),
			'ReviseListData_Secname' => $midname,
			'ReviseListData_Bithday' => $bd,
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($sql, $params);exit();
		$res = $this->db->query($sql, $params);
		if (!is_object($res)) {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД';
			return $response;
		}
		$tmp = $res->result('array');
		if (!empty($tmp[0]['Error_Msg'])) {
			$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			$response[0]['Error_Code'] = $tmp[0]['Error_Code'];
			return $response;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return string 
	 */
	function saveLinkReviseList($data) {
		$query = "
			select distinct 
				RLD.ReviseListData_id as \"ReviseListData_id\",
				PR.PersonRegister_id as \"PersonRegister_id\"
			from v_PersonState PS
				inner join r19.v_ReviseListData RLD on RLD.ReviseListData_Firname = PS.Person_FirName
					and RLD.ReviseListData_Surname = PS.Person_SurName
					and (RLD.ReviseListData_Bithday is null
						or RLD.ReviseListData_Bithday = PS.Person_BirthDay)
					and (RLD.ReviseListData_Secname is null
						or RLD.ReviseListData_Secname = PS.Person_SecName)
				inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
				inner join MorbusType mt on mt.MorbusType_id = pr.MorbusType_id
					and mt.MorbusType_SysNick = 'narc'
				left join v_EvnNotifyNarco EN on EN.EvnNotifyNarco_id = PR.EvnNotifyBase_id
				left join v_MorbusCrazy MO on MO.Morbus_id = coalesce(EN.Morbus_id,PR.Morbus_id)
				left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
			where
				(1 = 1)
				and ( Diag_pid in (705,706,707,708,709,710,711,712,713,714) )
				and coalesce(MO.CrazyCauseEndSurveyType_id,0) != 6 
				and RLD.ReviseList_id = :ReviseList_id
		";
		$results = $this->queryResult($query, $data);
		foreach ($results as $result) {
			$sql = "
				select
					ReviseListDataLink_id as \"ReviseListDataLink_id\",
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from r19.p_ReviseListDataLink_ins(
					ReviseListData_id := :ReviseListData_id,
					PersonRegister_id := :PersonRegister_id,
					pmUser_id := :pmUser_id
				)
			";

			$res = $this->getFirstRowFromQuery($sql, [
				'pmUser_id' => $data['pmUser_id'],
				'ReviseListData_id' => $result['ReviseListData_id'],
				'PersonRegister_id' => $result['PersonRegister_id']
			]);

			if (!$this->isSuccessful($res)) {
				return [[
					'Error_Code' => $res['Error_Code'],
					'Error_Msg' => $res['Error_Msg']
				]];
			}
		}
		$sql = '
			update r19.ReviseList
			set ReviseList_MatchKolvo = (
				select COUNT(*)
				from r19.ReviseListData rld
					inner join r19.ReviseListDataLink rldl on rldl.ReviseListData_id = rld.ReviseListData_id
				where rld.ReviseList_id = :ReviseList_id
			)
			where ReviseList_id = :ReviseList_id
			returning 0 as "Error_COde", \'\' as "Error_Msg";
		';
		$result = $this->db->query($sql, array('ReviseList_id' => $data['ReviseList_id']));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * @param $data
	 * @return bool
	 * @comment Для Карелии метод вынесен в региональную модель
	 */
	function getNumber($data) {
		$query = "
			select
				ObjectID as \"Num\"
			from xp_GenpmID(
				ObjectName := 'ReviseList',
				Lpu_id := :Lpu_id
			)
		";
		$result = $this->db->query($query, array('Lpu_id' => $data['Lpu_id']));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *
	 * @param type $data
	 * @return type 
	 */
	function Export($data) {
		$select = "
			SELECT
				RLDL.ReviseListData_id as \"ID\",
				PS.Person_SurName as \"LAST_NAME\",
				PS.Person_FirName as \"FIRST_NAME\",
				coalesce(PS.Person_SecName,'НЕТ') as \"MIDDLE_NAM\",
				to_char(ps.Person_birthday, 'dd.mm.yyyy') as \"BIRTHDAY\",
				Diag.diag_FullName as \"DS\",
				Lpu.Lpu_Nick as \"LPU\",
				to_char(PR.PersonRegister_setDate, 'dd.mm.yyyy') as \"DATA_OPEN\",
				to_char(PR.PersonRegister_disDate, 'dd.mm.yyyy') as \"DATA_END\"
			FROM v_PersonState PS
				inner join v_PersonRegister PR on PR.Person_id = PS.Person_id
				inner join MorbusType mt on mt.MorbusType_id = pr.MorbusType_id and mt.MorbusType_SysNick = 'narc'
				inner join r19.v_ReviseListDataLink RLDL on RLDL.PersonRegister_id = PR.PersonRegister_id
				inner join r19.v_ReviseListData RLD on RLD.ReviseListData_id = RLDL.ReviseListData_id
				left join v_Diag Diag on Diag.Diag_id = PR.Diag_id
				left join v_Lpu Lpu on Lpu.Lpu_id = PR.Lpu_iid
			where RLD.ReviseList_id = :ReviseList_id
		";

		$p = array('ReviseList_id' => $data['ReviseList_id']);
		//echo getDebugSQL($select, $p);exit();
		$query = $this->db->query($select, $p);
		$result = $query->result('array');
		switch ($data['typeFormat']) {
			case'DBF':
				$this->ExportDBF($result, $data['ReviseList_id']);
				break;
			case 'XLS':
				$this->ExportXML($result, $data['ReviseList_id']);
				break;
		}
		return true;
	}
	/**
	 *
	 * @param type $result
	 * @param type $ReviseList_id 
	 */
	function ExportXML($result, $ReviseList_id) {
		require_once('vendor/autoload.php');
		$objPHPExcel = new PhpOffice\PhpSpreadsheet\Spreadsheet();
		$objPHPExcel->getProperties();
		$objPHPExcel->getActiveSheet()->setTitle('Демо');
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', 'ID')
				->setCellValue('B1', 'LAST_NAME')
				->setCellValue('C1', 'FIRST_NAME')
				->setCellValue('D1', 'MIDDLE_NAM')
				->setCellValue('E1', 'BIRTHDAY')
				->setCellValue('F1', 'DS')
				->setCellValue('G1', 'LPU')
				->setCellValue('H1', 'DATA_OPEN')
				->setCellValue('I1', 'DATA_END');
		$r = 2;
		foreach ($result as $item) {
			$c = 0;
			foreach ($item as $val) {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($c, $r, $val);
				$c++;
			}
			$r++;
		}
		$objWriter = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
		$file = 'export/Сверка_#' . $ReviseList_id . '.xlsx';
		$objWriter->save($file);
		$this->download($file);
		unlink($file);
	}
	/**
	 *
	 * @param type $data
	 * @return string 
	 */
	function deleteReviseListDataLink($data) {
		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r19.p_ReviseListDataLink_del(
				ReviseListDataLink_id := :ReviseListDataLink_id
			)	
				
		";
		$params = array('ReviseListDataLink_id' => $data['ReviseListDataLink_id']);
		$res = $this->db->query($sql, $params);
		if (!is_object($res)) {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД';
			return $response;
		}
		$tmp = $res->result('array');
		if (!empty($tmp[0]['Error_Msg'])) {
			$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			$response[0]['Error_Code'] = $tmp[0]['Error_Code'];
			return $response;
		} else {
			if($data['ReviseList_id']>0){
				$sql = " 
					update r19.ReviseList
					set ReviseList_MatchKolvo = (
						select COUNT(*)
						FROM r19.v_ReviseListDataLink RLDL
							inner join r19.v_ReviseListData RLD on RLD.ReviseListData_id = RLDL.ReviseListData_id
						where RLD.ReviseList_id=:ReviseList_id
					)
					where ReviseList_id = :ReviseList_id
					returning 0 as \"Error_Code\", '' as \"Error_Msg\";
				";
			
				//echo getDebugSQL($sql, array('ReviseList_id'=>$data['ReviseList_id']));exit();
				$result = $this->db->query($sql, array('ReviseList_id'=>$data['ReviseList_id']));
				//print_r($result);
				$tmp = $result->result('array');
			
			}
			
			return $tmp;
		}
		
	}
	/**
	 *
	 * @param type $data
	 * @return string 
	 */
	function deleteReviseList($data) {
		$results = $this->queryResult("
			select
				RLDL.ReviseListDataLink_id as \"ReviseListDataLink_id\"
			from r19.ReviseListDataLink RLDL
				inner join r19.ReviseListData RLD on RLD.ReviseListData_id = RLDL.ReviseListData_id
			where RLD.ReviseList_id = :ReviseList_id
		", $data);

		foreach ($results as $result) {
			$res = $this->getFirstRowFromQuery("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from r19.p_ReviseListDataLink_del(
					ReviseListDataLink_id := :ReviseListDataLink_id
				)
			", $result);

			if (!$this->isSuccessful($res)) {
				return [[
					'Error_Code' => $res['Error_Code'],
					'Error_Msg' => $res['Error_Msg']
				]];
			}
		}

		$results = $this->queryResult("
			select
				RLD.ReviseListData_id as \"ReviseListData_id\"
			from r19.ReviseListData RLD
			where RLD.ReviseList_id = :ReviseList_id
		", $data);

		foreach ($results as $result) {
			$res = $this->getFirstRowFromQuery("
				select
					Error_Code as \"Error_Code\",
					Error_Message as \"Error_Msg\"
				from r19.p_ReviseListDataLink_del(
					ReviseListData_id := :ReviseListData_id
				)
			", $result);
			if (!$this->isSuccessful($res)) {
				return [[
					'Error_Code' => $res['Error_Code'],
					'Error_Msg' => $res['Error_Msg']
				]];
			}
		}

		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from r19.p_ReviseList_del(
				ReviseList_id := :ReviseList_id
			)
		";
		$params = array('ReviseList_id' => $data['ReviseList_id']);
		$res = $this->db->query($sql, $params);
		if (!is_object($res)) {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД';
			return $response;
		}
		$tmp = $res->result('array');
		if (!empty($tmp[0]['Error_Msg'])) {
			$response[0]['Error_Msg'] = $tmp[0]['Error_Msg'];
			$response[0]['Error_Code'] = $tmp[0]['Error_Code'];
			return $response;
		} else {
			return $tmp;
		}
	}
	/**
	 *
	 * @param type $result
	 * @param type $ReviseList_id
	 * @return type 
	 */
	function ExportDBF($result, $ReviseList_id) {

		$DBFs = array(
			array("ID", "C", 11, 0),
			array("LAST_NAME", "C", 40, 0),
			array("FIRST_NAME", "C", 40, 0),
			array("MIDDLE_NAM", "C", 40, 0),
			array("BIRTHDAY", "D", 8),
			array("DS", "C", 80, 0),
			array("LPU", "C", 80, 0),
			array("DATA_OPEN", "D", 8),
			array("DATA_END", "D", 8),
		);
		$fname = "Сверка_#" . $ReviseList_id;

		$dbf = "export/" . $fname . '.dbf';
		$sd = dbase_create($dbf, $DBFs);
		foreach ($result as $row) {
			array_walk($row, 'ConvertFromUtf8toCp866');
			$row['BIRTHDAY'] = Date('Ymd',strtotime($row['BIRTHDAY']));
			$row['DATA_OPEN'] = Date('Ymd',strtotime($row['DATA_OPEN']));

			if ( !empty($row['DATA_END']) ) {
				$row['DATA_END'] = Date('Ymd',strtotime($row['DATA_END']));
			}
			dbase_add_record($sd, array_values($row));
		}
		dbase_close($sd);
		$this->download($dbf);
		unlink($dbf);
		return true;
	}
	/**
	 *
	 * @param type $file 
	 */
	function download($file) {
		if (ob_get_level()) {
			ob_end_clean();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . basename($file));
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit;
	}

}
