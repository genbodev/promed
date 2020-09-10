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

class MPQueue_model extends CI_Model {
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
			update EvnQueue with (rowlock)
				set EvnQueue_IsArchived = 2
			where 
				EvnQueue_id = :EvnQueue_id
		";
		
		$result = $this->db->query($query, $data);
		
		return array(array('Error_Msg' => ''));;
	}

	/**
	 * Загрузка данных
	 */
	function loadEvnDirectionEditForm($data) {
		$query = "
			select
				ED.EvnDirection_id,
				ED.Diag_id,
				ED.DirType_id,
				LU.Lpu_id as Lpu_did,
				LU.LpuUnitType_SysNick,
				ED.LpuSectionProfile_did as LpuSectionProfile_id,
				ED.Direction_Num as EvnDirection_Num,
				convert(varchar(10), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 104) + ' ' + 
					convert(varchar(5), ISNULL(ISNULL(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 108) as EvnDirection_setDateTime,
				convert(varchar(10), ED.EvnQueue_setDate, 104) as EvnDirection_setDate,
				ED.EvnDirection_Descr,
				ED.MedPersonal_id as MedStaffFact_id,
				ED.MedPersonal_zid as MedStaffFact_zid,
				ED.MedPersonal_id,
				ED.LpuSection_id,
				ED.Post_id,
				ED.MedPersonal_zid,
				
				ED.EvnQueue_pid as EvnDirection_pid,
				ED.TimetableGraf_id,
				ED.TimetablePar_id,
				ED.TimetableStac_id,
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				
				ps.Person_Surname,
				ps.Person_Firname,
				ps.Person_Secname,
				convert(varchar(10), ps.Person_Birthday, 104) as Person_Birthday
			from v_EvnQueue ED
				left join v_LpuUnit LU with (NOLOCK) on ED.LpuUnit_did = LU.LpuUnit_id
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
			'Start_Date' => 'cast(EvnQueue_insDT as date) >= :Start_Date',
			'End_Date' => 'cast(EvnQueue_insDT as date) <= :End_Date',
			'DirType_id' => 'ed.DirType_id = :DirType_id',
			'LpuSectionProfile_id' => 'eq.LpuSectionProfile_did = :LpuSectionProfile_id',
			'Lpu_id' => 'l1.Lpu_id = :Lpu_id',
            'Person_FIO' => "rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) like :Person_FIO",
			'Person_birthDay' => 'ps.Person_birthDay = :Person_birthDay'
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
				$queryParams[$filter] = strpos($sql_part, 'like') ? $data['f_'.$filter].'%' : $data['f_'.$filter];
			}
		}

		$query = "
			-- variables
			DECLARE @TimetableGraf_begTime DATETIME = dbo.tzGetDate();
			-- end variables

			select
				-- select
				eq.EvnQueue_id,
				ps.Person_id,
				ps.Server_id,
				ps.PersonEvn_id,
				ps.Person_Surname,
				ps.Person_Firname,
				ps.Person_Secname,
				(ps.Person_Surname + ' ' + ps.Person_Firname + ' ' + ps.Person_Secname) as name,
				ps.Person_Phone,
				case 
					when adr1.Address_id is not null
					then adr1.Address_Address
					else adr.Address_Address
				end as Person_Address,
				
				convert(varchar,cast(ps.Person_birthDay as datetime),104) as birthdate,
				(
					convert(varchar,eq.EvnQueue_insDT,104) 
					+ ' ' 
					+ substring(convert(varchar,eq.EvnQueue_insDT,108),1,5)
				) as EvnQueue_insDT,
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Name,
				isnull(d.Diag_Code,'') as Diag_Code,
				isnull(dt.DirType_Name,'') as DirType_Name,
				ed.EvnDirection_id,
				ed.EvnDirection_Num as Direction_Num,
				isnull(ed.EvnDirection_Descr,'') as EvnDirection_Descr,
				ISNULL(convert(varchar,TT.min_time,4),'нет') +' '+ ISNULL(SUBSTRING(convert(varchar,TT.min_time,108),1,5),'') as FreeRec, --Первое свободное время

				convert(varchar(10), ed.EvnDirection_desDT, 104) as EvnDirection_desDT,

				l.Lpu_Nick as Lpu_Name,
				l1.Lpu_Nick as Lpu_dName,
				lu1.LpuUnit_Name as LpuUnit_dName,

				l1.Lpu_id,
				eq.LpuSectionProfile_did,
				eq.pmUser_updId as pmUser_id,
				eq.EvnQueue_updDT as updDT,

				convert(varchar(10), ed.EvnDirection_failDT, 104) as EvnDirection_failDate,
				dft.DirFailType_Name,
				fLpu.Lpu_Nick as LpuFail_Nick,
				fMP.Person_Fio as MedPersonalFail_Fio
				-- end select
			from
				-- from
				v_EvnQueue eq with (NOLOCK)
				left outer join v_PersonState_all ps with (NOLOCK) on eq.Person_id = ps.Person_id
				left outer join [Address] adr with (NOLOCK) on ps.UAddress_id = adr.Address_id
				left outer join [Address] adr1 with (NOLOCK) on ps.PAddress_id = adr1.Address_id
				
				left join v_EvnDirection ed with (NOLOCK) on ed.EvnQueue_id = eq.EvnQueue_id
				left join v_DirFailType dft with (NOLOCK) on dft.DirFailType_id = ed.DirFailType_id
				left join v_pmUserCache fUser with(nolock) on fUser.PMUser_id = ED.pmUser_failID
				left join v_Lpu fLpu with(nolock) on fLpu.Lpu_id = fUser.Lpu_id
				outer apply(
					select top 1 MP.MedPersonal_id, MP.Person_Fio
					from v_MedPersonal MP with(nolock)
					where MP.MedPersonal_id = fUser.MedPersonal_id and MP.WorkType_id = 1
				) fMP
				left join v_Diag d with (NOLOCK) on ed.Diag_id=d.Diag_id
				left join v_DirType dt with (NOLOCK) on ed.DirType_id=dt.DirType_id

				left outer join v_Lpu l with (NOLOCK) on l.Lpu_id = eq.Lpu_id
				left outer join v_LpuUnit_ER lu1 with (NOLOCK) on lu1.LpuUnit_id = eq.LpuUnit_did
				left outer join v_Lpu l1 with (NOLOCK) on l1.Lpu_id = lu1.Lpu_id

				outer apply (
					select
						MIN(ttg.TimetableGraf_begTime) as min_time
					from v_TimetableGraf ttg with (NOLOCK)
					left join v_MedStaffFact_er msf with (NOLOCK) on msf.MedStaffFact_id = ttg.MedStaffFact_id
					where ttg.Person_id is null
						and ttg.TimetableType_id not in (2, 3, 4)
						and msf.Lpu_id = l1.Lpu_id
						and msf.LpuSectionProfile_id = eq.LpuSectionProfile_did
						and ttg.TimetableGraf_begTime >= @TimetableGraf_begTime
						" . $msfFilter . "
				) TT
				
				left join v_LpuSectionProfile lsp with (NOLOCK) on lsp.LpuSectionProfile_id = eq.LpuSectionProfile_did
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
		
		
		/*
				ps2.Person_Inn,
				(convert(varchar,cast(eq.EvnQueue_setDate as datetime),104) + ' ' + EvnQueue_setTime) as record,
				case when eq.Person_id is not null then 
					case 
						 when pu.pmUser_id is not null and (eq.pmUser_updid<1000000 or eq.pmUser_updid>5000000) then rtrim(pu.pmUser_Name) + ' (' + rtrim(l.Lpu_Nick) + ')'
						 when eq.pmUser_updid=999000 then 'Запись через КМИС' 
						 else 'Запись через интернет'
					end
				end as operator,
				
				left join v_pmUser pu on pu.pmUser_id = eq.pmUser_updId
				left join v_Lpu l on l.Lpu_id = pu.Lpu_id
		*/
		//print_r($data);
		/*$queryParams = array(
			'DirType_id' => $data['f_DirType_id'],
			'Lpu_id' => $data['f_Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionProfile_id' => $data['f_LpuSectionProfile_id']
		);*/		

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
					EvnQueue_id,
					convert(varchar,min(d.day_date),104) as MinDate 
				from v_EvnQueue eq with (nolock)
				inner join v_LpuSection ls with (nolock) on eq.LpuUnit_did = ls.LpuUnit_id and ls.LpuSectionProfile_id = eq.LpuSectionProfile_did 
				inner join v_Medstafffact_ER msf with (nolock) on msf.LpuSection_id = ls.LpuSection_id 
					and ((eq.Direction_Num is not null and isnull(msf.MedstaffFact_IsDirRec, 2) = 2) or eq.Direction_Num is null) 
					and RecType_id in (1,4)
				inner join MedPersonalDay mpd with (nolock) on msf.MedStaffFact_id = mpd.MedStaffFact_id 
					and mpd.MedPersonalDay_FreeRec is not null
					and mpd.MedPersonalDay_FreeRec != 0
				inner join v_Day d with (nolock) on d.day_id = mpd.Day_id-1
					and cast(convert(char(10), day_date, 112) as datetime)>=cast(convert(char(10), dateadd(day, 1, dbo.tzGetDate()), 112) as datetime) 
					and cast(convert(char(10), day_date, 112) as datetime)<=cast(convert(char(10), dateadd(day, 15, dbo.tzGetDate()), 112) as datetime)
				where eq.QueueFailCause_id is null and eq.EvnQueue_recDT is null ".$fd_where."
				group by eq.EvnQueue_id";
	 
			
			$result = $this->db->query($query, $queryParams);
			if ( is_object($result) ) {
				$res = $result->result('array');
				/*while (!$q->EOF) {
					$first_dates[$q->Fields['EvnQueue_id']] = $q->Fields['MinDate'];
					$q->Next();
				}*/
				//print_r($res);
			}
			
			
			/*
			
			// Первые даты по стационару
			$sql="
				select 
					EvnQueue_id,
					convert(varchar,min(TimetableStac_setDate),104) as MinDate,
					DirType_id
				from v_EvnQueue eq
				inner join v_LpuSection_ER ls on eq.LpuUnit_did = ls.LpuUnit_id
					and ((eq.Direction_Num is not null and isnull(ls.LpuSection_AllowDirRecord, 1) = 1) or eq.Direction_Num is null) 
				inner join TimetableStac tts on ls.LpuSection_id = tts.LpuSection_id
					and tts.Person_id is null
				".$where."
					and ls.LpuSectionProfile_id = eq.LpuSectionProfile_did
					and TimetableType_id not in (2)
					and cast(convert(char(10), TimetableStac_setDate, 112) as datetime)>=cast(convert(char(10), dateadd(day, 1, dbo.tzGetDate()), 112) as datetime)
					and cast(convert(char(10), TimetableStac_setDate, 112) as datetime)<=cast(convert(char(10), dateadd(day, 15, dbo.tzGetDate()), 112) as datetime)
					and eq.TimetableStac_id is null
				where eq.QueueFailCause_id is null and eq.EvnQueue_recDT is null ".$where." {$filter}
				group by EvnQueue_id, DirType_id";
			//echo "<pre>".$sql."</pre>";
			
			$q = new TQuery($sql);
			while (!$q->EOF) {
				if ($q->Fields['DirType_id'] != 6 )
					$first_dates[$q->Fields['EvnQueue_id']] = $q->Fields['MinDate'];
				$q->Next();
			}
			
			
			// Первые даты по параклинике
			$sql="
				select 
					EvnQueue_id,
					convert(varchar,min(TimetablePar_begTime),104) as MinDate 
				from v_EvnQueue eq
				inner join v_LpuSection_ER ls on eq.LpuUnit_did = ls.LpuUnit_id
					and ((eq.Direction_Num is not null and isnull(ls.LpuSection_AllowDirRecord, 1) = 1) or eq.Direction_Num is null) 
				inner join TimetablePar ttp on ls.LpuSection_id = ttp.LpuSection_id
					and ttp.Person_id is null
					and TimetableType_id not in (2, 3)
				".$where."
					and ls.LpuSectionProfile_id = eq.LpuSectionProfile_did 
					and cast(convert(char(10), TimetablePar_begTime, 112) as datetime)>=cast(convert(char(10), dateadd(day, 1, dbo.tzGetDate()), 112) as datetime)
					and cast(convert(char(10), TimetablePar_begTime, 112) as datetime)<=cast(convert(char(10), dateadd(day, 15, dbo.tzGetDate()), 112) as datetime)
					and eq.TimetablePar_id is null
				where eq.QueueFailCause_id is null and eq.EvnQueue_recDT is null ".$where." {$filter}
				group by EvnQueue_id";
			
			$q = new TQuery($sql);
			while (!$q->EOF) {
				$first_dates[$q->Fields['EvnQueue_id']] = $q->Fields['MinDate'];
				$q->Next();
			}
			*/
			
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
				EvnQueue_id,
				TimetableGraf_id,
				TimetableStac_id,
				TimetablePar_id,
				EvnDirection_id
			FROM 
				v_EvnQueue with(nolock)
				
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
		/*if ($res[0]['EvnDirection_id'] != null && $res[0]['EvnDirection_id'] != $data['EvnDirection_id']) {			
			//удаление выписанного направления
			$query = "
				declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_EvnDirection_del
					@EvnDirection_id = :EvnDirection_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query($query, array(
				'EvnDirection_id' => $data['EvnDirection_id'],
				'pmUser_id' => $data['pmUser_id']
			));

			return array(
				'success' => false,
				'Error_Msg' => 'Выбранной вами записи уже назначено направление.'
			);
		}*/
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
				ps.Person_id,
				ps.PersonEvn_id,
				ps.Server_id,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				convert(varchar,cast(ps.Person_deadDT as datetime),104) as Person_deadDT,
				convert(varchar,cast(ps.Person_birthDay as datetime),104) as Person_BirthDay,
				eq.Diag_id,
				eq.MedPersonal_id,
				eq.MedPersonal_zid,
				eq.Lpu_id as Lpu_did,
				eq.LpuUnit_did,
				eq.LpuSectionProfile_did as LpuSectionProfile_id,
				eq.EvnDirection_Descr,
				eq.DirType_id
			from
				v_EvnQueue eq
				left outer join v_PersonState ps on eq.Person_id = ps.Person_id
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
				TimetableGraf_id,
				(
					convert(varchar,ttg.TimetableGraf_begTime,104) 
					+ ' ' 
					+ substring(convert(varchar,ttg.TimetableGraf_begTime,108),1,5)	
				) as time,
				convert(varchar,ttg.TimetableGraf_begTime,104) as date,
				ttg.Person_id as ttgPerson_id,
				msf.Lpu_id,
				msf.LpuSection_id,
				msf.MedPersonal_id
			from
				v_TimetableGraf ttg with (nolock)
				left join v_MedStaffFact msf with (nolock) on ttg.MedStaffFact_id = msf.MedStaffFact_id
				left join v_LpuSection ls with (nolock) on msf.LpuSection_id = ls.LpuSection_id
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
			declare
				@datetime datetime = dbo.tzGetDate(),
				@Err_Msg varchar(400),
				@EvnComment_id bigint;

			set nocount on;

			begin try
				begin tran

				update dbo.EvnQueue
				set
					EvnQueue_failDT = @datetime,
					pmUser_failID = :pmUser_id,
					QueueFailCause_id = :QueueFailCause_id
				where
					EvnQueue_id = :EvnQueue_id

				set @EvnComment_id = (select top 1 EvnComment_id from v_EvnComment with (nolock) where Evn_id = :EvnQueue_id);

				if ( @EvnComment_id is not null )
					begin
						update dbo.EvnComment
						set EvnComment_Comment = :EvnComment_Comment,
							pmUser_updID = :pmUser_id,
							EvnComment_updDT = @datetime
						where EvnComment_id = @EvnComment_id
					end
				else
					begin
						insert into dbo.EvnComment with (ROWLOCK) (Evn_id, EvnComment_Comment, pmUser_insID, pmUser_updID, EvnComment_insDT, EvnComment_updDT)
						values (:EvnQueue_id, :EvnComment_Comment, :pmUser_id, :pmUser_id, @datetime, @datetime)
					end

				commit tran
			end try
			
			begin catch
				set @Err_Msg = error_message();
				rollback tran
			end catch

			set nocount off;

			select @Err_Msg as Error_Msg;
		";

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
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnQueue_del
				@EvnQueue_id = :EvnQueue_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
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
				msr.LpuRegion_id as LpuRegion_id, -- Участок
				lr.LpuRegionType_id as LpuRegionType_id, -- Тип участка
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
				end) as LpuAttachType_id -- Тип прикрепления
			FROM
				v_MedStaffRegion msr
				inner join v_LpuRegion lr on lr.LpuRegion_id = msr.LpuRegion_id AND msr.MedPersonal_id = :MedPersonal_id AND msr.Lpu_id = :Lpu_id
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
?>