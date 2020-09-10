<?php
/**
* MPQueue_model - модель для работы с записями в очереди
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
*/

class MPQueue_model extends SwPgModel {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Функция сохранения признака "В архив"
	 */
	function sendToArchive($data) {
		$query = "
			update EvnQueue set
				EvnQueue_IsArchived = 2
			where 
				Evn_id = :EvnQueue_id
		";
		
		$result = $this->db->query($query, $data);
		
		return [
			['Error_Msg' => '']
		];
	}

	/**
	 * Загрузка данных
	 */
	function loadEvnDirectionEditForm($data) {
		$query = "
			select
				ED.EvnDirection_id as \"EvnDirection_id\",
				ED.Diag_id as \"Diag_id\",
				ED.DirType_id as \"DirType_id\",
				LU.Lpu_id as \"Lpu_did\",
				LU.LpuUnitType_SysNick as \"LpuUnitType_SysNick\",
				ED.LpuSectionProfile_did as \"LpuSectionProfile_id\",
				ED.Direction_Num as \"EvnDirection_Num\",
				to_char(coalesce(coalesce(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 'dd.mm.yyyy')
					|| ' ' || to_char(coalesce(coalesce(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 'HH24:MI:SS')
				as \"EvnDirection_setDateTime\",
				to_char(ED.EvnQueue_setDate, 'dd.mm.yyyy') as \"EvnDirection_setDate\",
				ED.EvnDirection_Descr as \"EvnDirection_Descr\",
				ED.MedPersonal_id as \"MedStaffFact_id\",
				ED.MedPersonal_zid as \"MedStaffFact_zid\",
				ED.MedPersonal_id as \"MedPersonal_id\",
				ED.LpuSection_id as \"LpuSection_id\",
				--ED.Post_id as \"Post_id\",
				ED.MedPersonal_zid as \"MedPersonal_zid\",
				ED.EvnQueue_pid as \"EvnDirection_pid\",
				ED.TimetableGraf_id as \"TimetableGraf_id\",
				ED.TimetablePar_id as \"TimetablePar_id\",
				ED.TimetableStac_id as \"TimetableStac_id\",
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				ps.Person_Surname as \"Person_Surname\",
				ps.Person_Firname as \"Person_Firname\",
				ps.Person_Secname as \"Person_Secname\",
				to_char(ps.Person_Birthday, 'dd.mm.yyyy') as \"Person_Birthday\"
			from v_EvnQueue ED
				left join v_LpuUnit LU on ED.LpuUnit_did = LU.LpuUnit_id
				left join v_PersonState PS on ED.Person_id = PS.Person_id
				left join LpuSectionProfile LSP on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_did
				left join Diag on Diag.Diag_id = ED.Diag_id
				left join TimetableGraf TTG on TTG.EvnDirection_id = ED.EvnDirection_id
				left join TimetablePar TTP on TTP.TimetablePar_id = ED.TimetablePar_id
				left join TimetableStac TTS on TTS.EvnDirection_id = ED.EvnDirection_id
			where ED.EvnQueue_id =  :EvnQueue_id 
		";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
	
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Вывод данных списка очереди в грид
	 */
	function loadQueueListGrid($data) {
		$query = "";
		$where = "";
		$fd_where = "";
		$queryParams = array();
		
		$filters = array(
			'Start_Date' => 'cast(EvnQueue_insDT as date) >= cast(:Start_Date as date)',
			'End_Date' => 'cast(EvnQueue_insDT as date) <= cast(:End_Date as date)',
			'DirType_id' => 'ed.DirType_id = :DirType_id',
			'LpuSectionProfile_id' => 'eq.LpuSectionProfile_did = :LpuSectionProfile_id',
			'Lpu_id' => 'l1.Lpu_id = :Lpu_id',
            'Person_FIO' => "rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) ilike :Person_FIO",
			'Person_birthDay' => 'ps.Person_birthDay = cast(:Person_birthDay as date)'
		);

		$msfFilter = '';

		if ( !empty($data['MedStaffFact_id']) ) {
			$msfFilter = ' and ttg.MedStaffFact_id = :MedStaffFact_id ';
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		foreach($filters as $filter => $sql_part) {
			if (isset($data['f_'.$filter]) && !empty($data['f_'.$filter]) && $data['f_'.$filter] != "" && $sql_part != "") {
				$wh = ' and '.$sql_part;
				
				$where .= $wh;
				if (!in_array($filter, array('Person_FIO', 'Person_birthDay')))
					$fd_where .= str_replace('l1.Lpu_id', 'ls.Lpu_id', $wh);
				
				if (in_array($filter, array('Start_Date', 'End_Date', 'Person_birthDay'))) {
					$data['f_'.$filter] = substr($data['f_'.$filter], 0, strpos($data['f_'.$filter], 'T'));
				}
				$queryParams[$filter] = strpos($sql_part, 'ilike') ? $data['f_'.$filter].'%' : $data['f_'.$filter];
			}
		}

		$query = "
			-- variables
			with mv as (
				select
					dbo.tzgetdate() as dt
			)
			-- end variables

			select
				-- select
				eq.EvnQueue_id as \"EvnQueue_id\",
				ps.Person_id as \"Person_id\",
				ps.Server_id as \"Server_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Person_Surname as \"Person_Surname\",
				ps.Person_Firname as \"Person_Firname\",
				ps.Person_Secname as \"Person_Secname\",
				(ps.Person_Surname || ' ' || ps.Person_Firname || ' ' || ps.Person_Secname) as \"name\",
				ps.Person_Phone as \"Person_Phone\",
				case 
					when adr1.Address_id is not null
					then adr1.Address_Address
					else adr.Address_Address
				end as \"Person_Address\",
				to_char(cast(ps.Person_birthDay as timestamp), 'dd.mm.yyyy') as \"birthdate\",
				to_char(eq.EvnQueue_insDT, 'dd.mm.yyyy HH24:MI') as \"EvnQueue_insDT\",
				lsp.LpuSectionProfile_id as \"LpuSectionProfile_id\",
				lsp.LpuSectionProfile_Name as \"LpuSectionProfile_Name\",
				coalesce(d.Diag_Code, '') as \"Diag_Code\",
				coalesce(dt.DirType_Name, '') as \"DirType_Name\",
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.EvnDirection_Num as \"EvnDirection_Num\",
				coalesce(ed.EvnDirection_Descr, '') as \"EvnDirection_Descr\",
				coalesce(to_char(TT.min_time, 'dd.mm.yyyy'), 'нет') ||' '|| coalesce(SUBSTRING(to_char(TT.min_time, 'HH24:mi:ss'), 1, 5), '') as \"FreeRec\", --Первое свободное время
				to_char(ed.EvnDirection_desDT, 'dd.mm.yyyy') as \"EvnDirection_desDT\",
				l.Lpu_Nick as \"Lpu_Name\",
				l1.Lpu_Nick as \"Lpu_dName\",
				lu1.LpuUnit_Name as \"LpuUnit_dName\",
				l1.Lpu_id as \"Lpu_id\",
				eq.LpuSectionProfile_did as \"LpuSectionProfile_did\",
				eq.pmUser_updId as \"pmUser_id\",
				eq.EvnQueue_updDT as \"updDT\",
				to_char(ed.EvnDirection_failDT, 'dd.mm.yyyy') as \"EvnDirection_failDate\",
				dft.DirFailType_Name as \"DirFailType_Name\",
				fLpu.Lpu_Nick as \"LpuFail_Nick\",
				fMP.Person_Fio as \"MedPersonalFail_Fio\"
				-- end select
			from
				-- from
				v_EvnQueue eq
				left outer join v_PersonState_all ps on eq.Person_id = ps.Person_id
				left outer join Address adr on ps.UAddress_id = adr.Address_id
				left outer join Address adr1 on ps.PAddress_id = adr1.Address_id
				
				left join v_EvnDirection ed on ed.EvnQueue_id = eq.EvnQueue_id
				left join v_DirFailType dft on dft.DirFailType_id = ed.DirFailType_id
				left join v_pmUserCache fUser on fUser.PMUser_id = ED.pmUser_failID
				left join v_Lpu fLpu on fLpu.Lpu_id = fUser.Lpu_id
				left join lateral(
					select
						MP.MedPersonal_id,
						MP.Person_Fio
					from v_MedPersonal MP
					where MP.MedPersonal_id = fUser.MedPersonal_id
						and MP.WorkType_id = 1
					limit 1
				) fMP on true
				left join v_Diag d on ed.Diag_id=d.Diag_id
				left join v_DirType dt on ed.DirType_id=dt.DirType_id

				left outer join v_Lpu l on l.Lpu_id = eq.Lpu_id
				left outer join v_LpuUnit_ER lu1 on lu1.LpuUnit_id = eq.LpuUnit_did
				left outer join v_Lpu l1 on l1.Lpu_id = lu1.Lpu_id

				left join lateral(
					select
						MIN(ttg.TimetableGraf_begTime) as min_time
					from v_TimetableGraf ttg
					left join v_MedStaffFact_er msf on msf.MedStaffFact_id = ttg.MedStaffFact_id
					where ttg.Person_id is null
						and ttg.TimetableType_id not in (2, 3, 4)
						and msf.Lpu_id = l1.Lpu_id
						and msf.LpuSectionProfile_id = eq.LpuSectionProfile_did
						and ttg.TimetableGraf_begTime >= (select dt from mv)
						" . $msfFilter . "
				) TT on true
				left join v_LpuSectionProfile lsp on lsp.LpuSectionProfile_id = eq.LpuSectionProfile_did
				-- end from
			where 
				-- where
				(1=1) 
				and ps.Person_isDead = 0
				and EvnQueue_failDT is null
				and EvnQueue_recDT is null
				".$where." 
				-- end where
			order by 
				-- order by
				eq.EvnQueue_id DESC
				-- end order by
		";

		//echo getDebugSql($query, $queryParams); die;

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

		if ( false )
		{
			//получение первых дат для записей в очереди
			$first_dates = array();
			
			// Первые даты по поликлинике
			$query="
				select 
					EvnQueue_id as \"EvnQueue_id\",
					to_char(min(d.day_date), 'dd.mm.yyyy') as \"MinDate\"
				from v_EvnQueue eq
					inner join v_LpuSection ls on eq.LpuUnit_did = ls.LpuUnit_id
						and ls.LpuSectionProfile_id = eq.LpuSectionProfile_did 
					inner join v_Medstafffact_ER msf on msf.LpuSection_id = ls.LpuSection_id 
						and ((eq.Direction_Num is not null and coalesce(msf.MedstaffFact_IsDirRec, 2) = 2) or eq.Direction_Num is null) 
						and RecType_id in (1, 4)
					inner join MedPersonalDay mpd on msf.MedStaffFact_id = mpd.MedStaffFact_id 
						and mpd.MedPersonalDay_FreeRec is not null
						and mpd.MedPersonalDay_FreeRec != 0
					inner join v_Day d on d.day_id = mpd.Day_id - 1
						and cast(to_char(day_date, 'yyyymmdd') as timestamp) >= cast(to_char(dateadd('day', 1, dbo.tzGetDate()), 'yyyymmdd') as timestamp)
						and cast(to_char(day_date, 'yyyymmdd') as timestamp) <= cast(to_char(dateadd('day', 15, dbo.tzGetDate()), 'yyyymmdd') as timestamp)
				where eq.QueueFailCause_id is null
					and eq.EvnQueue_recDT is null ".$fd_where."
				group by eq.EvnQueue_id";
	 
			
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$res = $result->result('array');
			}
			
			for($i = 0; $i < count($response['data']); $i++) {
				$response['data'][$i]['FreeRec'] = isset($first_dates[$response['data'][$i]['EvnQueue_id']]) ? $first_dates[$response['data'][$i]['EvnQueue_id']] : 'нет';
			}
		}
		return $response;
	}
	
	/**
	* Проверка, что запись в очереди существует и еще не назначена ни на какую бирку
	*/
	function checkQueueRecordFree($data) {
		$sql = "
			SELECT 
				EvnQueue_id as \"EvnQueue_id\",
				TimetableGraf_id as \"TimetableGraf_id\",
				TimetableStac_id as \"TimetableStac_id\",
				TimetablePar_id as \"TimetablePar_id\",
				EvnDirection_id as \"EvnDirection_id\"
			FROM 
				v_EvnQueue
			WHERE
				EvnQueue_id = :Id
		";
		$res = $this->db->query(
			$sql,
			array(
				'Id' => $data['EvnQueue_id']
			)
		);
		if ( is_object($res) )
			$res = $res->result('array');
		if ($res[0]['EvnQueue_id'] == null) {
			return array(
				'success' => false,
				'Error_Msg' => 'Записи с таким идентификатором не существует.'
			);
		}
		if ($res[0]['TimetableGraf_id'] != null || $res[0]['TimetableStac_id'] != null || $res[0]['TimetablePar_id'] != null) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранной вами записи уже назначена бирка.'
			);
		}
		return true;
	}
	/**
	 * Возвращает данные для направления
	 */
	function getDataForDirection($data) {
		//print_r($data);
		$res_array = array();

		$query = "
			select
				ps.Person_id as \"Person_id\",
				ps.PersonEvn_id as \"PersonEvn_id\",
				ps.Server_id as \"Server_id\",
				ps.Person_SurName as \"Person_SurName\",
				ps.Person_FirName as \"Person_FirName\",
				ps.Person_SecName as \"Person_SecName\",
				to_char(cast(ps.Person_deadDT as timestamp), 'dd.mm.yyyy') as \"Person_deadDT\",
				to_char(cast(ps.Person_birthDay as timestamp), 'dd.mm.yyyy') as \"Person_BirthDay\",
				eq.Diag_id as \"Diag_id\",
				eq.MedPersonal_id as \"MedPersonal_id\",
				eq.MedPersonal_zid as \"MedPersonal_zid\",
				eq.Lpu_id as \"Lpu_did\",
				eq.LpuUnit_did as \"LpuUnit_did\",
				eq.LpuSectionProfile_did as \"LpuSectionProfile_id\",
				eq.EvnDirection_Descr as \"EvnDirection_Descr\",
				eq.DirType_id as \"DirType_id\"
			from
				v_EvnQueue eq
					left join v_PersonState ps on eq.Person_id = ps.Person_id
			where
				eq.EvnQueue_id = :EvnQueue_id
		";
				
		$result = $this->db->query($query, array('EvnQueue_id' => $data['EvnQueue_id'], 'TimetableGraf_id' => $data['TimetableGraf_id']));

		if ( is_object($result) ) {
			$res = $result->result('array');
			if (isset($res[0]))
				$res_array = array_merge($res_array, $res[0]);
		}
		
		$query = "
			select
				TimetableGraf_id as \"TimetableGraf_id\",
				(
					to_char(ttg.TimetableGraf_begTime, 'dd.mm.yyyy')
					|| ' ' 
					|| substring(to_char(ttg.TimetableGraf_begTime, 'HH24:MI:SS'), 1, 5)	
				) as \"time\",
				to_char(ttg.TimetableGraf_begTime, 'dd.mm.yyyy') as \"date\",
				ttg.Person_id as \"ttgPerson_id\",
				msf.Lpu_id as \"Lpu_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.MedPersonal_id as \"MedPersonal_id\"
			from
				v_TimetableGraf ttg
				left join v_MedStaffFact msf on ttg.MedStaffFact_id = msf.MedStaffFact_id
				left join v_LpuSection ls on msf.LpuSection_id = ls.LpuSection_id
			where
				TimetableGraf_id = :TimetableGraf_id	
		";
				
		$result = $this->db->query($query, array('EvnQueue_id' => $data['EvnQueue_id'], 'TimetableGraf_id' => $data['TimetableGraf_id']));

		if ( is_object($result) ) {
			$res = $result->result('array');

			if (empty($res[0]["TimetableGraf_id"])) {
				return array(
					'success' => false,
					'Error_Msg' => 'Бирка с таким идентификатором не существует.'
				);
			}
			if (isset($res[0]) AND !empty($res[0]['ttgPerson_id'])) {
				return array(
					'success' => false,
					'Error_Msg' => 'Выбранная вами бирка уже занята.'
				);
			}
			if ( isset($res[0]['date']) ) {
				$cur_date = new DateTime(date('d.m.Y'));
				$check_date = new DateTime($res[0]['date']);
				if ( $check_date < $cur_date )
				{
					return array(
						'success' => false,
						'Error_Msg' => 'Вы не можете записать пациента на дату раньше текущего дня.'
					);
				}
			}
			else
			{
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибка при получении даты бирки.'
				);
			}

			if (isset($res[0]))
				$res_array = array_merge($res_array, $res[0]);
		}
		
		//print_r($res_array);
		
		return count($res_array) > 0 ? array($res_array) : false;
	}

	/**
	 * Отмена записи в очереди по профилю
	 */
	function cancelQueueRecord($data) {
		$query = "
			update dbo.EvnQueue
			set
				EvnQueue_failDT = dbo.tzgetdate(),
				pmUser_failID = :pmUser_id,
				QueueFailCause_id = :QueueFailCause_id
			where
				Evn_id = :EvnQueue_id
		";

		$this->queryResult($query, $data);

		$res = $this->getFirstResultFromQuery("
			select
				EvnComment_id as \"EvnComment_id\"
			from v_EvnComment
			where Evn_id = :EvnQueue_id
			limit 1
		", $data);

		if (!empty($res)) {
			$data['EvnComment_id'] = $res;
			$query = "
				update dbo.EvnComment
				set
					EvnComment_Comment = :EvnComment_Comment,
					pmUser_updID = :pmUser_id,
					EvnComment_updDT = dbo.tzgetdate()
				where EvnComment_id = :EvnComment_id
			";
		} else {
			$query = "
				insert into dbo.EvnComment (Evn_id, EvnComment_Comment, pmUser_insID, pmUser_updID, EvnComment_insDT, EvnComment_updDT)
					values (:EvnQueue_id, :EvnComment_Comment, :pmUser_id, :pmUser_id, dbo.tzgetdate(), dbo.tzgetdate())
			";
		}
		if (empty($data['EvnComment_Comment'])) {
			$data['EvnComment_Comment'] = '';
		}

		if ( strlen($data['EvnComment_Comment']) > 2048 ) {
			$data['EvnComment_Comment'] = substr($data['EvnComment_Comment'], 0, 2048);
		}

		$result = $this->db->query($query, array(
			'pmUser_id' => $data['pmUser_id'],
			'QueueFailCause_id' => $data['QueueFailCause_id'],
			'EvnComment_Comment' => $data['EvnComment_Comment'],
			'EvnQueue_id' => $data['EvnQueue_id']
		));
		
		if ( is_object($result) ) {
			if($data['EvnDirection_id']){
				$this->load->model('Evn_model', 'Evn_model');
				$this->Evn_model->updateEvnStatus(array(
					'Evn_id' => $data['EvnDirection_id'],
					'EvnStatus_id' => 13,
					'pmUser_id' => $data['pmUser_id']
				));
			}
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 * Удаление записи из очереди
	 */
	
	function deleteQueueRecord($data) {
		$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
		$response = $this->TimetableGraf_model->getEvnDataByRecord(array('object' => 'EvnQueue','object_id' => $data['EvnQueue_id']));
		if(!empty($response))
		{
			if(isset($response[0]['Error_Msg']))
			{
				return $response;
			}
			return array(array('Error_Msg' => 'Найдены события, связанные с направлением. Удаление невозможно!'));
		}

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_EvnQueue_del(
				EvnQueue_id := :EvnQueue_id,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($query, array(
			'EvnQueue_id' => $data['EvnQueue_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			$response = $result->result('array');
		}
		else {
			$response = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (удаление из очереди)'));
		}
		
		return $response;
	}

	/**
	* Получение данных: "ЛПУ прикрепления", "Тип прикрепления:", "Тип участка:", "Участок:", на котором врач АРМа является врачом на участке
	* Входящие данные: $_POST['MedPersonal_id'] $_POST['Lpu_id']
	*/
	function getAttachData($data) {
		$sql = "
			SELECT
				msr.LpuRegion_id as \"LpuRegion_id\", -- Участок
				lr.LpuRegionType_id as \"LpuRegionType_id\", -- Тип участка
				(case when lr.LpuRegionType_id = 6 
					then 4 
				else 
					case when lr.LpuRegionType_id = 5 
						then 3 
					else 
						case when lr.LpuRegionType_id = 3 
							then 2 
						else
							1
						end 
					end 
				end) as \"LpuAttachType_id\" -- Тип прикрепления
			FROM
				v_MedStaffRegion msr
				inner join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id
					AND msr.MedPersonal_id = :MedPersonal_id
					AND msr.Lpu_id = :Lpu_id
				inner join v_LpuRegionType lrt on lrt.LpuRegionType_id = lr.LpuRegionType_id
		";
		
		$res = $this->db->query(
			$sql,
			array(
				'MedPersonal_id' => $data['MedPersonal_id'],
				'Lpu_id' => $data['Lpu_id']
			)
		);
		if ( is_object($res) )
			$res = $res->result('array');
		else
			return false;
		return $res;
	}
}
