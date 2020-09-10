<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Newslatter_model - модель для работы с рассылками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Aleksandr Chebukin
 * @version			18.12.2015
 */

class Newslatter_model extends SwPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Удаление рассылок
	 */
	function delete($data) {

		$data['Newslatter_ids'] = json_decode($data['Newslatter_ids'], true);
		if (!count($data['Newslatter_ids'])) {
			return true;
		}

		$res = true;
		foreach ($data['Newslatter_ids'] as $id) {
			$query = "
                select 
                    Error_Code as \"Error_Code\",
                    Error_Message as \"Error_Msg\"
				from p_Newslatter_del (
					Newslatter_id := :Newslatter_id
					)
			";

			$result = $this->db->query($query, array('Newslatter_id' => $id));
			if (!is_object($result)) {
				return false;
			} else {
				$res = $result->result('array');
			}
		}

		return $res;
	}

	/**
	 * Отмена рассылок
	 */
	function cancel($data) {

		$data['Newslatter_ids'] = json_decode($data['Newslatter_ids'], true);
		if (!count($data['Newslatter_ids'])) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}

		$res = array(array('success' => true, 'Error_Msg' => ''));
		foreach ($data['Newslatter_ids'] as $id) {
			$query = "UPDATE Newslatter SET Newslatter_IsActive = 1 WHERE Newslatter_id = :Newslatter_id";
			$result = $this->db->query($query, array('Newslatter_id' => $id));
			if ($result != 1) {
				return false;
			}
		}

		return $res;
	}

	/**
	 * Активация рассылок
	 */
	function activate($data) {

		$data['Newslatter_ids'] = json_decode($data['Newslatter_ids'], true);
		if (!count($data['Newslatter_ids'])) {
			return array(array('success' => true, 'Error_Msg' => ''));
		}

		$res = array(array('success' => true, 'Error_Msg' => ''));
		foreach ($data['Newslatter_ids'] as $id) {
			$query = "UPDATE Newslatter SET Newslatter_IsActive = 2 WHERE Newslatter_id = :Newslatter_id";
			$result = $this->db->query($query, array('Newslatter_id' => $id));
			if ($result != 1) {
				return false;
			}
		}

		return $res;
	}

	/**
	 * Возвращает список рассылок
	 */
	function loadList($data) {

		$filter = '';
		$join = '';

		if (!empty($data['Newslatter_insDT'][0]) && !empty($data['Newslatter_insDT'][1])) {
			$filter .= ' and cast(N.Newslatter_insDT as date) between :Newslatter_insDT_1 and :Newslatter_insDT_2';
			$data['Newslatter_insDT_1'] = $data['Newslatter_insDT'][0];
			$data['Newslatter_insDT_2'] = $data['Newslatter_insDT'][1];
		}

		if (!empty($data['Newslatter_Date'][0]) && !empty($data['Newslatter_Date'][1])) {
			$filter .= ' and N.Newslatter_begDate between :Newslatter_Date_1 and :Newslatter_Date_2';
			$data['Newslatter_Date_1'] = $data['Newslatter_Date'][0];
			$data['Newslatter_Date_2'] = $data['Newslatter_Date'][1];
		}

		if (!empty($data['NewslatterType_id'])) {
			$filter .= ' and N.NewslatterType_id = :NewslatterType_id';
		}

		if (!empty($data['Newslatter_IsActive'])) {
			$filter .= ' and N.Newslatter_IsActive = :Newslatter_IsActive';
		}

		if (!empty($data['Newslatter_Text'])) {
			$filter .= ' and N.Newslatter_Text ILIKE :Newslatter_Text';
			$data['Newslatter_Text'] = "%{$data['Newslatter_Text']}%";
		}

		if (!empty($data['Person_Fio'])) {
			$filter .= ' and (
				PS.Person_SurName ILIKE :Person_Fio OR
				PS.Person_FirName ILIKE :Person_Fio OR
				PS.Person_SecName ILIKE :Person_Fio
			)';
			$join .= '
				inner join v_PersonNewslatter PN ON PN.Newslatter_id = N.Newslatter_id
				inner join v_NewslatterAccept NA ON NA.NewslatterAccept_id = PN.NewslatterAccept_id
				inner join v_PersonState PS ON PS.Person_id = NA.Person_id
			';
			$data['Person_Fio'] = "%{$data['Person_Fio']}%";
		}

		if (!empty($data['Newslatter_Text'])) {
			$filter .= ' and N.Newslatter_Text ILIKE :Newslatter_Text';
			$data['Newslatter_Text'] = "%{$data['Newslatter_Text']}%";
		}

		$query = "
			select
				-- select
				N.Newslatter_id as \"Newslatter_id\"
				,COALESCE(N.NewslatterGroupType_id,1) as \"NewslatterGroupType_id\"
				,to_char (N.Newslatter_insDT, 'dd.mm.yyyy') as \"Newslatter_insDT\"
				,to_char (N.Newslatter_begDate, 'dd.mm.yyyy') || coalesce(' - ' || to_char (N.Newslatter_endDate, 'dd.mm.yyyy'), '') as \"Newslatter_Date\"
				,to_char (N.Newslatter_begTime, 'hh:mm:ss') as \"Newslatter_Time\"
				,pm.PMUser_Name as \"PMUser_Name\"
				,N.Newslatter_Text as \"Newslatter_Text\"
				,CASE WHEN COALESCE(N.Newslatter_IsActive, 1) = 1 THEN 'false' else 'true' END as \"Newslatter_IsActive\"
				-- end select
			from
				-- from
				v_Newslatter N
				inner join v_pmUser pm ON pm.PMUser_id = N.pmUser_insID
				{$join}
				-- end from
			where
				-- where
				N.Lpu_id = :Lpu_id
				{$filter}
				-- end where
			order by
				-- order by
				N.Newslatter_id desc
				-- end order by
		";

		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}

		if (is_object($result)) {
			$response = array();
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} else {
			return false;
		}
	}

	/**
	 * Возвращает рассылку
	 */
	function load($data) {

		$query = "
			select
				-- select
				N.Newslatter_id as \"Newslatter_id\"
				,COALESCE(N.NewslatterGroupType_id,1) as \"NewslatterGroupType_id\"
				,N.Lpu_id as \"Lpu_id\"
				,CASE WHEN COALESCE(N.Newslatter_IsActive, 1) = 1 THEN 'false' else 'true' END as \"Newslatter_IsActive\"
				,CASE WHEN COALESCE(N.Newslatter_IsSMS, 1) = 1 THEN 'false' else 'true' END as \"Newslatter_IsSMS\"
				,CASE WHEN COALESCE(N.Newslatter_IsEmail, 1) = 1 THEN 'false' else 'true' END as \"Newslatter_IsEmail\"
				,to_char (N.Newslatter_insDT, 'dd.mm.yyyy') as \"Newslatter_insDT\"
				,to_char (N.Newslatter_begDate, 'dd.mm.yyyy') as \"Newslatter_begDate\"
				,to_char (N.Newslatter_endDate, 'dd.mm.yyyy') as \"Newslatter_endDate\"
				,to_char (N.Newslatter_begTime, 'hh:mm') as \"Newslatter_begTime\"
				,N.Newslatter_Text as \"Newslatter_Text\"
				,N.NewslatterType_id as \"NewslatterType_id\"
				-- end select
			from
				-- from
				v_Newslatter N
				-- end from
			where
				-- where
				N.Newslatter_id = :Newslatter_id
				-- end where
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Возвращает список пациентов рассылки
	 */
	function loadPersonNewslatterList($data) {
		//определяем тип группировки
		$groupType = $this->getNewslatterGroupTypeCode($data);
		if(!$groupType) return false;
		$select = '';
		$join = '';
		$where = '';
		switch ($groupType) {
			case 1:
				$select = " PN.PersonNewslatter_id as \"PersonNewslatter_id\" ";
				$join = "
					inner join v_PersonNewslatter PN ON PN.Newslatter_id = N.Newslatter_id
					inner join v_NewslatterAccept NA ON NA.NewslatterAccept_id = PN.NewslatterAccept_id
					inner join v_PersonState PS ON PS.Person_id = NA.Person_id
				";
				break;
			case 2:
			case 3:
				if($groupType == 2){
					$select = " LSNL.LpuSectionNewslatter_id as \"LpuSectionNewslatter_id\" ";
					$prfix = 'LS';
					$join = "
						INNER JOIN v_LpuSectionNewslatter LSNL ON N.Newslatter_id = LSNL.Newslatter_id
						INNER JOIN dbo.v_LpuSection LS ON LS.LpuSection_id = LSNL.LpuSection_id
						INNER JOIN v_LpuRegion LR ON LR.LpuSection_id = LS.LpuSection_id
					";
				}
				if($groupType == 3){
					$select = " LRNL.LpuRegionNewslatter_id as \"LpuRegionNewslatter_id\"  ";
					$prfix = 'LR';
					$join = "
						INNER JOIN v_LpuRegionNewslatter LRNL ON N.Newslatter_id = LRNL.Newslatter_id
						INNER JOIN v_LpuRegion LR ON LR.LpuRegion_id = LRNL.LpuRegion_id
					";
				}
				$join .= "
					INNER JOIN v_PersonCard PC ON PC.LpuRegion_id = LR.LpuRegion_id and PC.LpuAttachType_id = 1
					INNER JOIN v_PersonCardAttach PCA on PCA.PersonCardAttach_id = pc.PersonCardAttach_id
					INNER JOIN v_PersonState PS ON PS.Person_id = PC.Person_id
					left join lateral (
						select NA.NewslatterAccept_id
						from v_NewslatterAccept NA
						where NA.Person_id = ps.Person_id 
							AND NA.Lpu_id = {$prfix}.Lpu_id
							AND CAST(dbo.tzGetDate() AS DATE) BETWEEN cast(NA.NewslatterAccept_begDate as date) AND CAST(COALESCE(NA.NewslatterAccept_endDate, '2030-01-01') AS DATE)
						order by NA.NewslatterAccept_id desc
						limit 1
					) NA on true
				";
				$where = "
					AND pc.LpuAttachType_id = 1
					AND NA.NewslatterAccept_id IS NOT NULL
					AND CAST(dbo.tzGetDate() AS DATE) BETWEEN cast(PC.PersonCard_begDate as date) AND CAST(COALESCE(PC.PersonCard_endDate, '2030-01-01') AS DATE)
				";
				break;

			default:
				break;
		}
		$query = "
			select
				-- select
				{$select}
				,NA.NewslatterAccept_id as \"NewslatterAccept_id\"
				,'1' as \"RecordStatus_Code\"
				,PS.Person_id as \"Person_id\"
				,PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"Person_Fio\"
				,to_char (PS.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
				-- end select
			from
				-- from
				v_Newslatter N
				{$join}
				-- end from
			where
				-- where
				N.Newslatter_id = :Newslatter_id
				{$where}
				-- end where
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет рассылку
	 */
	function save($data) {



		$params = array(
			'Newslatter_id' => empty($data['Newslatter_id']) ? null : $data['Newslatter_id'],
			'Lpu_id' => $data['Lpu_id'],
			'Newslatter_IsActive' => (isset($data['Newslatter_IsActive']) && $data['Newslatter_IsActive'] > 0) ? 2 : 1,
			'Newslatter_IsSMS' => (isset($data['Newslatter_IsSMS']) && $data['Newslatter_IsSMS'] > 0) ? 2 : 1,
			'Newslatter_IsEmail' => (isset($data['Newslatter_IsEmail']) && $data['Newslatter_IsEmail'] > 0) ? 2 : 1,
			'Newslatter_begDate' => $data['Newslatter_begDate'] ?: null,
			'Newslatter_endDate' => $data['Newslatter_endDate'] ?: null,
			'Newslatter_begTime' => $data['Newslatter_begTime'] ?: null,
			'Newslatter_Text' => $data['Newslatter_Text'] ?: null,
			'NewslatterType_id' => $data['NewslatterType_id'] ?: null,
			'NewslatterGroupType_id' => $data['NewslatterGroupType_id'] ?: null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = empty($params['Newslatter_id']) ? 'p_Newslatter_ins' : 'p_Newslatter_upd';
		$previousType = (!empty($params['Newslatter_id'])) ? $this->getNewslatterGroupTypeCode($data) : null;
		$query = "
            select 
                Newslatter_id as \"Newslatter_id\", 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				Newslatter_id := :Newslatter_id,
				Lpu_id := :Lpu_id,
				NewslatterType_id := :NewslatterType_id,
				Newslatter_IsSMS := :Newslatter_IsSMS,
				Newslatter_IsEmail := :Newslatter_IsEmail,
				Newslatter_begDate := :Newslatter_begDate,
				Newslatter_endDate := :Newslatter_endDate,
				Newslatter_begTime := :Newslatter_begTime,
				Newslatter_Text := :Newslatter_Text,
				Newslatter_IsActive := :Newslatter_IsActive,
				NewslatterGroupType_id := :NewslatterGroupType_id,
				pmUser_id := :pmUser_id
				)
		";

		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$result = $result->result('array');
			$data['Newslatter_id'] = $result[0]['Newslatter_id'];
			if (isset($data['PersonNewslatterData'])) {
				$this->savePersonNewslatterList($data);
			}
			if (isset($data['LpuSectionNewslatterData'])) {
				$this->saveLpuSectionNewslatterList($data);
			}
			if (isset($data['LpuRegionNewslatterData'])) {
				$this->saveLpuRegionNewslatterList($data);
			}
			if($previousType){
				//если изменилась группировка, то удалим старые данные
				$type = false;
				if($data['NewslatterGroupType_id'] != $previousType){
					switch ($previousType) {
						case 1:
							$type = 'PersonNewslatter';
							break;
						case 2:
							$type = 'LpuSectionNewslatter';
							break;
						case 3:
							$type = 'LpuRegionNewslatter';
							break;
						default:
							break;
					}
				}
				if($type){
					$funcLoad = 'load'.$type.'List';
					$funcDel = 'delete'.$type;
					$actual = $this->$funcLoad($data);
					$actual_ids = [];
					foreach ($actual as $ac){
						if(!empty($ac[$type.'_id'])) $actual_ids[] = $ac[$type.'_id'];
					}
					foreach ($actual_ids as $id){
						$this->$funcDel([$type.'_id' => $id]);
					}
				}
			}
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * Сохраняет список пациентов в рассылке
	 */
	function savePersonNewslatterList($data) {
		$pndata = json_decode($data['PersonNewslatterData'], true);
		$actual = $this->loadPersonNewslatterList($data);
		$actual_ids = [];
		foreach ($actual as $ac){
			$actual_ids[] = $ac['PersonNewslatter_id'];
		}
		foreach ($pndata as $pn) {
			$pn['Newslatter_id'] = $data['Newslatter_id'];
			$pn['pmUser_id'] = $data['pmUser_id'];
			if(!empty($pn['PersonNewslatter_id'])){
				$key = array_search($pn['PersonNewslatter_id'], $actual_ids);
				if($key !== false) unset($actual_ids[$key]);
			}
			if ( !isset($pn['RecordStatus_Code']) || !is_numeric($pn['RecordStatus_Code']) || !in_array($pn['RecordStatus_Code'], array(0, 2, 3)) ) {
				continue;
			}
			switch ( $pn['RecordStatus_Code'] ) {
				case 0:	case 2:
					$this->savePersonNewslatter($pn);
					break;
				case 3:
					$this->deletePersonNewslatter($pn);
					break;
			}
		}
		
		foreach ($actual_ids as $id){
			//удаляем значения, которых больше нет в списке
			$this->deletePersonNewslatter(['PersonNewslatter_id' => $id]);
		}
	}

	/**
	 * Сохраняет пациента в рассылке
	 */
	function savePersonNewslatter($data) {

		$params = array(
			'PersonNewslatter_id' => empty($data['PersonNewslatter_id']) || $data['PersonNewslatter_id'] <= 0 ? null : $data['PersonNewslatter_id'],
			'Newslatter_id' => $data['Newslatter_id'] ?: null,
			'NewslatterAccept_id' => $data['NewslatterAccept_id'] ?: null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = ($params['PersonNewslatter_id'] <= 0) ? 'p_PersonNewslatter_ins' : 'p_PersonNewslatter_upd';

		$query = "
            select 
                PersonNewslatter_id as \"PersonNewslatter_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from {$procedure} (
				PersonNewslatter_id := :PersonNewslatter_id,
				Newslatter_id := :Newslatter_id,
				NewslatterAccept_id := :NewslatterAccept_id,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			throw new Exception('При сохранении пациентов произошла ошибка');
		}
	}

	/**
	 * Удаляет пациента из рассылки
	 */
	function deletePersonNewslatter($data) {

		$query = "
            select 
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Msg\"
			from p_PersonNewslatter_del (
				PersonNewslatter_id := :PersonNewslatter_id
				)
		";

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('При удалении пациентов произошла ошибка');
		}
	}

	/**
	 * Выполнение рассылки
	 */
	function send() {
		/*
		$query = "
			select
				N.Newslatter_id as \"Newslatter_id\"
				,N.Newslatter_IsSMS as \"Newslatter_IsSMS\"
				,N.Newslatter_IsEmail as \"Newslatter_IsEmail\"
				,N.Newslatter_Text as \"Newslatter_Text\"
				,NA.NewslatterAccept_IsSMS as \"NewslatterAccept_IsSMS\"
				,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
				,NA.NewslatterAccept_IsEmail as \"NewslatterAccept_IsEmail\"
				,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
				,PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"Person_Fio\"
			from v_Newslatter N
			inner join v_PersonNewslatter PN ON PN.Newslatter_id = N.Newslatter_id
			inner join v_NewslatterAccept NA ON Na.NewslatterAccept_id = PN.NewslatterAccept_id
			inner join v_PersonState PS ON PS.Person_id = NA.Person_id
			where ((
					N.Newslatter_begDate <= CAST(dbo.tzGetDate() as DATE) and
					N.Newslatter_endDate >= CAST(dbo.tzGetDate() as DATE)
				) or (
					N.Newslatter_begDate = CAST(dbo.tzGetDate() as DATE) and
					N.Newslatter_endDate is null
				)) and (
					NA.NewslatterAccept_begDate <= CAST(dbo.tzGetDate() as DATE) AND
					(NA.NewslatterAccept_endDate > CAST(dbo.tzGetDate() as DATE) OR NA.NewslatterAccept_endDate IS NULL)
				) and
				N.Newslatter_begTime between CAST(DATEADD('minute', -5, dbo.tzGetDate()) as TIME) and CAST(dbo.tzGetDate() as TIME) and
				N.Newslatter_IsActive = 2
		";
		*/
		$query = "
			with NLTMP as (
				select
					N.Newslatter_id
					,N.Newslatter_IsSMS
					,N.Newslatter_IsEmail
					,N.Newslatter_Text
					,N.NewslatterGroupType_id
					,N.Newslatter_begDate
					,N.Newslatter_endDate
				from v_Newslatter N 
				WHERE 1=1
					AND (
						(
							N.Newslatter_begDate <= CAST(dbo.tzGetDate() as DATE) and
							N.Newslatter_endDate >= CAST(dbo.tzGetDate() as DATE)
						) 
						OR 
						(
							N.Newslatter_begDate = CAST(dbo.tzGetDate() as DATE) and
							N.Newslatter_endDate is null
						)
					)
					AND N.Newslatter_begTime between CAST(dbo.tzGetDate() + interval '-455 minute' as TIME) and CAST(dbo.tzGetDate() as TIME)
					AND N.Newslatter_IsActive = 2
			)

				SELECT 
					N.Newslatter_id as \"Newslatter_id\"
					,N.Newslatter_IsSMS as \"Newslatter_IsSMS\"
					,N.Newslatter_IsEmail as \"Newslatter_IsEmail\"
					,N.Newslatter_Text as \"Newslatter_Text\"
					,NA.NewslatterAccept_IsSMS as \"NewslatterAccept_IsSMS\"
					,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
					,NA.NewslatterAccept_IsEmail as \"NewslatterAccept_IsEmail\"
					,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
					,PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"Person_Fio\"
					,N.NewslatterGroupType_id
				FROM NLTMP N
					inner join v_PersonNewslatter PN ON PN.Newslatter_id = N.Newslatter_id
					inner join v_NewslatterAccept NA ON Na.NewslatterAccept_id = PN.NewslatterAccept_id
					inner join v_PersonState PS ON PS.Person_id = NA.Person_id
				WHERE
					N.NewslatterGroupType_id = 1
					and (
						NA.NewslatterAccept_begDate <= CAST(dbo.tzGetDate() as DATE) AND
						(NA.NewslatterAccept_endDate > CAST(dbo.tzGetDate() as DATE) OR NA.NewslatterAccept_endDate IS NULL)
					)
			UNION

				SELECT 
					N.Newslatter_id as \"Newslatter_id\"
					,N.Newslatter_IsSMS as \"Newslatter_IsSMS\"
					,N.Newslatter_IsEmail as \"Newslatter_IsEmail\"
					,N.Newslatter_Text as \"Newslatter_Text\"
					,NA.NewslatterAccept_IsSMS as \"NewslatterAccept_IsSMS\"
					,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
					,NA.NewslatterAccept_IsEmail as \"NewslatterAccept_IsEmail\"
					,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
					,PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"Person_Fio\"
					,N.NewslatterGroupType_id
				FROM NLTMP N
					INNER JOIN v_LpuSectionNewslatter LSNL ON N.Newslatter_id = LSNL.Newslatter_id
					INNER JOIN dbo.v_LpuSection LS ON LS.LpuSection_id = LSNL.LpuSection_id
					INNER JOIN v_LpuRegion LR ON LR.LpuSection_id = LS.LpuSection_id
					INNER JOIN v_PersonCard PC ON PC.LpuRegion_id = LR.LpuRegion_id and PC.LpuAttachType_id = 1
					INNER JOIN v_PersonCardAttach PCA on PCA.PersonCardAttach_id = pc.PersonCardAttach_id
					INNER JOIN v_PersonState PS ON PS.Person_id = PC.Person_id
					left join lateral (
						select *
						from v_NewslatterAccept NA
						where NA.Person_id = ps.Person_id 
							AND NA.Lpu_id = LS.Lpu_id
							AND CAST(dbo.tzGetDate() AS DATE) BETWEEN cast(NA.NewslatterAccept_begDate as date) AND CAST(COALESCE(NA.NewslatterAccept_endDate, '2030-01-01') AS DATE)
						order by NA.NewslatterAccept_id desc
                        limit 1
					) NA on true
				WHERE
					N.NewslatterGroupType_id = 2
					and (
						NA.NewslatterAccept_begDate <= CAST(dbo.tzGetDate() as DATE) AND
						(NA.NewslatterAccept_endDate > CAST(dbo.tzGetDate() as DATE) OR NA.NewslatterAccept_endDate IS NULL)
					)

			UNION

				SELECT 
					N.Newslatter_id as \"Newslatter_id\"
					,N.Newslatter_IsSMS as \"Newslatter_IsSMS\"
					,N.Newslatter_IsEmail as \"Newslatter_IsEmail\"
					,N.Newslatter_Text as \"Newslatter_Text\"
					,NA.NewslatterAccept_IsSMS as \"NewslatterAccept_IsSMS\"
					,NA.NewslatterAccept_Phone as \"NewslatterAccept_Phone\"
					,NA.NewslatterAccept_IsEmail as \"NewslatterAccept_IsEmail\"
					,NA.NewslatterAccept_Email as \"NewslatterAccept_Email\"
					,PS.Person_SurName || ' ' || PS.Person_FirName || ' ' || PS.Person_SecName as \"Person_Fio\"
					,N.NewslatterGroupType_id
				FROM NLTMP N
					INNER JOIN v_LpuRegionNewslatter LRNL ON N.Newslatter_id = LRNL.Newslatter_id
					INNER JOIN v_LpuRegion LR on LR.LpuRegion_id = LRNL.LpuRegion_id
					INNER JOIN v_PersonCard PC ON PC.LpuRegion_id = LR.LpuRegion_id and PC.LpuAttachType_id = 1
					INNER JOIN v_PersonCardAttach PCA on PCA.PersonCardAttach_id = pc.PersonCardAttach_id
					INNER JOIN v_PersonState PS ON PS.Person_id = PC.Person_id
					left join lateral (
						select *
						from v_NewslatterAccept NA
						where NA.Person_id = ps.Person_id 
							AND NA.Lpu_id = LR.Lpu_id
							AND CAST(dbo.tzGetDate() AS DATE) BETWEEN cast(NA.NewslatterAccept_begDate as date) AND CAST(COALESCE(NA.NewslatterAccept_endDate, '2030-01-01') AS DATE)
						order by NA.NewslatterAccept_id desc
                        limit 1
					) NA on true
				WHERE
					N.NewslatterGroupType_id = 3
					and (
						NA.NewslatterAccept_begDate <= CAST(dbo.tzGetDate() as DATE) AND
						(NA.NewslatterAccept_endDate > CAST(dbo.tzGetDate() as DATE) OR NA.NewslatterAccept_endDate IS NULL)
					)
		";
		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}

	}
	
	
	/**
	 * Возвращает список отделений рассылки
	 */
	function loadLpuSectionNewslatterList($data) {

		$query = "
			select
				-- select
				LSN.LpuSectionNewslatter_id as \"LpuSectionNewslatter_id\"
				,LSN.LpuSection_id as \"LpuSection_id\"
				,'1' as \"RecordStatus_Code\"
				,LS.Lpu_id as \"Lpu_id\"
				,L.Lpu_Name as \"Lpu_Name\"
				,LS.LpuSection_Name as \"LpuSection_Name\"
				-- end select
			from
				-- from
				v_Newslatter N
				inner join v_LpuSectionNewslatter LSN ON LSN.Newslatter_id = N.Newslatter_id
				inner join v_LpuSection LS ON LS.LpuSection_id = LSN.LpuSection_id
				inner join v_Lpu L ON L.Lpu_id = LS.Lpu_id
				-- end from
			where
				-- where
				N.Newslatter_id = :Newslatter_id
				-- end where
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Сохраняет список отделений в рассылке
	 */
	function saveLpuSectionNewslatterList($data) {
		$pndata = json_decode($data['LpuSectionNewslatterData'], true);
		$actual = $this->loadLpuSectionNewslatterList($data);
		$actual_ids = [];
		foreach ($actual as $ac){
			$actual_ids[] = $ac['LpuSectionNewslatter_id'];
		}
		foreach ($pndata as $pn) {
			$pn['Newslatter_id'] = $data['Newslatter_id'];
			$pn['pmUser_id'] = $data['pmUser_id'];
			if(!empty($pn['LpuSectionNewslatter_id'])){
				$key = array_search($pn['LpuSectionNewslatter_id'], $actual_ids);
				if($key !== false) unset($actual_ids[$key]);
			}
			if ( !isset($pn['RecordStatus_Code']) || !is_numeric($pn['RecordStatus_Code']) || !in_array($pn['RecordStatus_Code'], array(0, 2, 3)) ) {
				continue;
			}
			switch ( $pn['RecordStatus_Code'] ) {
				case 0:	case 2:
					$this->saveLpuSectionNewslatter($pn);
					break;
				case 3:
					$this->deleteLpuSectionNewslatter($pn);
					break;
			}
		}
		
		foreach ($actual_ids as $id){
			//удаляем значения, которых больше нет в списке
			$this->deleteLpuSectionNewslatter(['LpuSectionNewslatter_id' => $id]);
		}
	}
	
	/**
	 * Сохраняет отделение в рассылке
	 */
	function saveLpuSectionNewslatter($data) {

		$params = array(
			'LpuSectionNewslatter_id' => empty($data['LpuSectionNewslatter_id']) || $data['LpuSectionNewslatter_id'] <= 0 ? null : $data['LpuSectionNewslatter_id'],
			'Newslatter_id' => $data['Newslatter_id'] ?: null,
			'LpuSection_id' => $data['LpuSection_id'] ?: null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = ($params['LpuSectionNewslatter_id'] <= 0) ? 'p_LpuSectionNewslatter_ins' : 'p_LpuSectionNewslatter_upd';

		$query = "
			select 
				LpuSectionNewslatter_id as \"LpuSectionNewslatter_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				LpuSectionNewslatter_id := :LpuSectionNewslatter_id,
				Newslatter_id := :Newslatter_id,
				LpuSection_id := :LpuSection_id,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			throw new Exception('При сохранении списка отделений произошла ошибка');
		}
		return true;
	}
	
	/**
	 * Удаляет отделение из рассылки
	 */
	function deleteLpuSectionNewslatter($data) {
		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LpuSectionNewslatter_del (
				LpuSectionNewslatter_id := :LpuSectionNewslatter_id
				)
		";

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('При удалении отделения из рассылки произошла ошибка');
		}
	}
	
	/**
	 * Возвращает список участков рассылки
	 */
	function loadLpuRegionNewslatterList($data) {

		$query = "
			select
				-- select
				LRN.LpuRegionNewslatter_id as \"LpuRegionNewslatter_id\"
				,LRN.LpuRegion_id as \"LpuRegion_id\"
				,'1' as \"RecordStatus_Code\"
				,LS.LpuSection_id as \"LpuSection_id\"
				,LR.Lpu_id as \"Lpu_id\"
				,L.Lpu_Name as \"Lpu_Name\"
				,LR.LpuRegion_Name as \"LpuRegion_Name\"
				-- end select
			from
				-- from
				v_Newslatter N
				inner join v_LpuRegionNewslatter LRN ON LRN.Newslatter_id = N.Newslatter_id
				inner join v_LpuRegion LR ON LR.LpuRegion_id = LRN.LpuRegion_id
				left join v_LpuSection LS ON LS.LpuSection_id = LR.LpuSection_id
				left join v_Lpu L ON L.Lpu_id = LR.Lpu_id
				-- end from
			where
				-- where
				N.Newslatter_id = :Newslatter_id
				-- end where
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Сохраняет список участков в рассылке
	 */
	function saveLpuRegionNewslatterList($data) {
		$pndata = json_decode($data['LpuRegionNewslatterData'], true);
		$actual = $this->loadLpuRegionNewslatterList($data);
		$actual_ids = [];
		foreach ($actual as $ac){
			$actual_ids[] = $ac['LpuRegionNewslatter_id'];
		}
		foreach ($pndata as $pn) {
			$pn['Newslatter_id'] = $data['Newslatter_id'];
			$pn['pmUser_id'] = $data['pmUser_id'];
			if(!empty($pn['LpuRegionNewslatter_id'])){
				$key = array_search($pn['LpuRegionNewslatter_id'], $actual_ids);
				if($key !== false) unset($actual_ids[$key]);
			}
			if ( !isset($pn['RecordStatus_Code']) || !is_numeric($pn['RecordStatus_Code']) || !in_array($pn['RecordStatus_Code'], array(0, 2, 3)) ) {
				continue;
			}
			switch ( $pn['RecordStatus_Code'] ) {
				case 0:	case 2:
					$this->saveLpuRegionNewslatter($pn);
					break;
				case 3:
					$this->deleteLpuRegionNewslatter($pn);
					break;
			}
		}
		foreach ($actual_ids as $id){
			//удаляем значения, которых больше нет в списке
			$this->deleteLpuRegionNewslatter(['LpuRegionNewslatter_id' => $id]);
		}
	}
	
	/**
	 * Сохраняет участок в рассылке
	 */
	function saveLpuRegionNewslatter($data) {

		$params = array(
			'LpuRegionNewslatter_id' => empty($data['LpuRegionNewslatter_id']) || $data['LpuRegionNewslatter_id'] <= 0 ? null : $data['LpuRegionNewslatter_id'],
			'Newslatter_id' => (!empty($data['Newslatter_id'])) ? $data['Newslatter_id'] : null,
			'LpuRegion_id' => (!empty($data['LpuRegion_id'])) ? $data['LpuRegion_id'] : null,
			'pmUser_id' => $data['pmUser_id']
		);

		$procedure = ($params['LpuRegionNewslatter_id'] <= 0) ? 'p_LpuRegionNewslatter_ins' : 'p_LpuRegionNewslatter_upd';
		$query = "
			select 
				LpuRegionNewslatter_id as \"PersonNewslatter_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure} (
				LpuRegionNewslatter_id := :LpuRegionNewslatter_id,
				Newslatter_id := :Newslatter_id,
				LpuRegion_id := :LpuRegion_id,
				pmUser_id := :pmUser_id
				)
		";

		$result = $this->db->query($query, $params);
		if ( !is_object($result) ) {
			throw new Exception('При сохранении списка участков произошла ошибка');
		}
		return true;
	}
	
	/**
	 * Удаляет участок из рассылки
	 */
	function deleteLpuRegionNewslatter($data) {
		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_LpuRegionNewslatter_del (
				LpuRegionNewslatter_id := :LpuRegionNewslatter_id
				)
		";

		$result = $this->db->query($query, $data);
		if ( !is_object($result) ) {
			throw new Exception('При удалении участка из рассылки произошла ошибка');
		}
	}
	
	/**
	 * Получает код типа группировки рассылки
	 */
	function getNewslatterGroupTypeCode($data){
		if(empty($data['Newslatter_id'])) return false;
		$query = "
			select
				N.Newslatter_id as \"Newslatter_id\",
				NGT.NewslatterGroupType_Code as \"NewslatterGroupType_Code\"
			from
				v_Newslatter N
				left join v_NewslatterGroupType NGT ON NGT.NewslatterGroupType_id = N.NewslatterGroupType_id
			where
				Newslatter_id = :Newslatter_id
		";
		$res = $this->getFirstRowFromQuery($query, ['Newslatter_id' => $data['Newslatter_id'] ]);
		if(empty($res['Newslatter_id'])) return false;
		$code = (!empty($res['NewslatterGroupType_Code'])) ? $res['NewslatterGroupType_Code'] : 1;
		return $code;
	}
}