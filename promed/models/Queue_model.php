<?php
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009-2012 Swan Ltd.
*/

/**
 * Class Queue_model - модель для работы с записями в очереди
 * @property EvnDirection_model $edmodel
 */
class Queue_model extends swModel {

	public $lpu_queue = array();

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

		return array(array('Error_Msg' => ''));
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
				ED.FSIDI_id,
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
			from v_EvnQueue ED with(nolock)
				left join v_LpuUnit LU with (NOLOCK) on ED.LpuUnit_did = LU.LpuUnit_id
				left join v_PersonState PS with(nolock) on ED.Person_id = PS.Person_id
				left join LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = ED.LpuSectionProfile_did
				left join Diag with(nolock) on Diag.Diag_id = ED.Diag_id
				left join v_TimetableGraf_lite TTG with(nolock) on TTG.EvnDirection_id = ED.EvnDirection_id
				left join TimetablePar TTP with(nolock) on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS with(nolock) on TTS.EvnDirection_id = ED.EvnDirection_id
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
	 *
	 * @param type $data
	 */
	function clearToQueue($data){
		$sql = "
			Select top 1
			ED.EvnDirection_id,
			TTG.TimetableGraf_id,
			TTMS.TimetableMedService_id,
			TTS.TimetableStac_id,
			EQ.EvnQueue_id,
			ED.Server_id,
			ED.PersonEvn_id,
			ED.Person_id,
			isnull(ED.LpuUnit_did, ls.LpuUnit_id) as LpuUnit_did,
			ls.LpuSectionProfile_id as LpuSectionProfile_did,
			LUdid.LpuUnitType_SysNick,
			ED.MedService_id,
			ED.LpuSectionProfile_id,
			ED.Lpu_id,
			ED.LpuSection_id,
			isnull(ED.MedPersonal_did,ED.MedPersonal_id) as MedPersonal_did,
			ED.LpuSection_did,
			uslc.UslugaComplex_id,
			ucms.MedService_id as MedService_did,
			1 as toQueue,
			ms_pzm.MedService_id as MedService_pzid,
			TTG.MedStaffFact_id,
			TTG.MedStaffFact_id as From_MedStaffFact_id,
			ED.DirType_id,
			ED.PrehospDirect_id,
			ED.EvnDirection_IsCito,
			ED.Diag_id,
			ED.Lpu_did,
			ED.Lpu_sid,
			ED.Org_sid,
			ED.EvnDirection_Descr,
			2 as EvnDirection_IsAuto,
			ED.EvnDirection_pid,
			ED.EvnDirection_Num,
			convert(varchar(10), ED.EvnDirection_setDate, 120) as EvnDirection_setDate,
			convert(varchar(10),ED.EvnDirection_desDT, 120) as EvnDirection_desDT,
			convert(varchar(10), ED.EvnDirection_setDate, 120) + ' ' + ISNULL(ED.EvnDirection_setTime,'') as EvnDirection_setDateTime,
			ep.EvnPrescr_id,
			PT.PrescriptionType_Code,
			ED.MedPersonal_id,
			ED.MedPersonal_zid
			from v_EvnDirection_all ED with(nolock)
			outer apply (
				Select top 1 TimetableGraf_id, MedStaffFact_id from v_TimetableGraf_lite TTG with (nolock) where TTG.EvnDirection_id = ED.EvnDirection_id
			) TTG
			left join v_MedStaffFact MSF with (nolock) on TTG.MedStaffFact_id = MSF.MedStaffFact_id
			 -- службы и параклиника
			outer apply (
				Select top 1 TimetableMedService_id,MedService_id,UslugaComplexMedService_id from v_TimetableMedService_lite TTMS with (nolock) where TTMS.EvnDirection_id = ED.EvnDirection_id
			) TTMS
			 -- стац
			outer apply (
				Select top 1 TimetableStac_id, LpuSection_id from v_TimetableStac_lite TTS with (nolock) where TTS.EvnDirection_id = ED.EvnDirection_id
			) TTS
			left join v_EvnQueue EQ with(nolock) on EQ.EvnDirection_id = ED.EvnDirection_id
			left join v_LpuSection ls with(nolock) on ED.LpuSection_did = ls.LpuSection_id
			left join v_LpuUnit LUdid with (NOLOCK) on isnull(ED.LpuUnit_did, ls.LpuUnit_id) = LUdid.LpuUnit_id
			left join v_UslugaComplexMedService ucms (nolock) on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
			outer apply(
				select top 1
					ms.MedService_id
				from
					v_MedService ms (nolock)
					inner join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				where
					ms.MedService_id = ISNULL(ucms.MedService_id, ttms.MedService_id)
					and mst.MedServiceType_SysNick = 'pzm'
			) ms_pzm
			left join v_UslugaComplex uslc with(nolock) on uslc.UslugaComplex_id = ucms.UslugaComplex_id
			left join v_EvnPrescrDirection epd with (nolock) on ED.EvnDirection_id = epd.EvnDirection_id
			left join v_EvnPrescr ep with(nolock) on ep.EvnPrescr_id = epd.EvnPrescr_id
			left join v_PrescriptionType PT with(nolock) on PT.PrescriptionType_id=ep.PrescriptionType_id
			where ED.EvnDirection_id = :EvnDirection_id
			
			
";
		$result = $this->db->query($sql, array('EvnDirection_id'=>$data['EvnDirection_id']));
		if ( is_object($result) ) {
			$response= $result->result('array');

			if(is_array($response)){
				$response[0]['pmUser_id']=$data['pmUser_id'];
				//print_r($response);exit();
				$this->load->model('Timetable_model', 'ttmodel');
				if($response[0]['TimetableGraf_id']!=null){
					$response[0]['object']='TimetableGraf';
					$this->ttmodel->Clear($response[0]);
					$response[0]['TimetableGraf_id']=null;
				}
				if($response[0]['TimetableMedService_id']!=null){
					$response[0]['object']='TimetableMedService';
					$this->ttmodel->Clear($response[0]);
					$response[0]['TimetableMedService_id']=null;
				}
				if($response[0]['TimetableStac_id']!=null){
					$response[0]['object']='TimetableStac';
					$this->ttmodel->Clear($response[0]);
					$response[0]['TimetableStac_id']=null;
				}
				$response[0]['EvnDirection_id']=null;
				return $response[0];
			}else{
				return false;
			}
		}else {
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

		$diagFilter = getAccessRightsDiagFilter('d.Diag_Code');
		if (!empty($diagFilter)) {
			$where .= " and $diagFilter";
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
				(ISNULL(ps.Person_Surname, '') + ' ' + ISNULL(ps.Person_Firname, '') + ' ' + ISNULL(ps.Person_Secname, '')) as name,
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
					from v_TimetableGraf_lite ttg with (NOLOCK)
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

				left join v_pmUser pu with(nolock) on pu.pmUser_id = eq.pmUser_updId
				left join v_Lpu l with(nolock) on l.Lpu_id = pu.Lpu_id
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
				declare @curdt datetime = dbo.tzGetDate();
				
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
					and cast(convert(char(10), day_date, 112) as datetime)>=cast(convert(char(10), dateadd(day, 1, @curdt), 112) as datetime) 
					and cast(convert(char(10), day_date, 112) as datetime)<=cast(convert(char(10), dateadd(day, 15, @curdt), 112) as datetime)
				where eq.QueueFailCause_id is null and eq.EvnQueue_recDT is null {$fd_where}
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
				declare @curdt datetime = dbo.tzGetDate();

				select
					EvnQueue_id,
					convert(varchar,min(TimetableStac_setDate),104) as MinDate,
					DirType_id
				from v_EvnQueue eq with(nolock)
				inner join v_LpuSection_ER ls with(nolock) on eq.LpuUnit_did = ls.LpuUnit_id
					and ((eq.Direction_Num is not null and isnull(ls.LpuSection_AllowDirRecord, 1) = 1) or eq.Direction_Num is null)
				inner join v_TimetableStac_lite tts with(nolock) on ls.LpuSection_id = tts.LpuSection_id
					and tts.Person_id is null
				".$where."
					and ls.LpuSectionProfile_id = eq.LpuSectionProfile_did
					and TimetableType_id not in (2)
					and cast(convert(char(10), TimetableStac_setDate, 112) as datetime)>=cast(convert(char(10), dateadd(day, 1, @curdt), 112) as datetime)
					and cast(convert(char(10), TimetableStac_setDate, 112) as datetime)<=cast(convert(char(10), dateadd(day, 15, @curdt), 112) as datetime)
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
				declare @curdt datetime = dbo.tzGetDate();

				select
					EvnQueue_id,
					convert(varchar,min(TimetablePar_begTime),104) as MinDate
				from v_EvnQueue eq with(nolock)
				inner join v_LpuSection_ER ls with(nolock) on eq.LpuUnit_did = ls.LpuUnit_id
					and ((eq.Direction_Num is not null and isnull(ls.LpuSection_AllowDirRecord, 1) = 1) or eq.Direction_Num is null)
				inner join TimetablePar ttp with(nolock) on ls.LpuSection_id = ttp.LpuSection_id
					and ttp.Person_id is null
					and TimetableType_id not in (2, 3)
				".$where."
					and ls.LpuSectionProfile_id = eq.LpuSectionProfile_did
					and cast(convert(char(10), TimetablePar_begTime, 112) as datetime)>=cast(convert(char(10), dateadd(day, 1, @curdt), 112) as datetime)
					and cast(convert(char(10), TimetablePar_begTime, 112) as datetime)<=cast(convert(char(10), dateadd(day, 15, @curdt), 112) as datetime)
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
				TimetableMedService_id,
				EvnDirection_id
			FROM 
				v_EvnQueue with(nolock)
				
			WHERE
				EvnQueue_id = :EvnQueue_id
		";
		$res = $this->db->query(
			$sql,
			array(
				'EvnQueue_id' => $data['EvnQueue_id']
			)
		);
		if ( is_object($res) ) {
			$res = $res->result('array');
			if ( count($res) == 0 ) {
				return array(
					'success' => false,
					'Error_Msg' => 'Записи с таким идентификатором не существует.'
				);
			} else if ($res[0]['TimetableGraf_id'] != null || $res[0]['TimetableStac_id'] != null || $res[0]['TimetableMedService_id'] != null) {
				return array(
					'success' => false,
					'Error_Msg' => 'Выбранной вами записи уже назначена бирка.'
				);
			}

			return true;
		} else {
			return array(
					'success' => false,
					'Error_Msg' => 'Ошибка выполнения запроса к БД'
				);
		}
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
				v_EvnQueue eq with(nolock)
				left outer join v_PersonState ps with(nolock) on eq.Person_id = ps.Person_id
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
				v_TimetableGraf_lite ttg with (nolock)
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
	 *
	 * @param type $EvnQueue_id
	 */
	function getEvnDirectionByQueue($EvnQueue_id){

	}

	/**
	 * Отмена записи из очереди
	 */
	function cancelQueueRecord($data)
	{
		if (empty($data['EvnDirection_id']) && !empty($data['EvnQueue_id'])) {
			$data['EvnDirection_id']=$this->getFirstResultFromQuery('
				select EvnDirection_id from v_EvnQueue with (nolock) where EvnQueue_id = :EvnQueue_id
			', array(
				'EvnQueue_id' => $data['EvnQueue_id'],
			));
		}
		if (empty($data['EvnDirection_id'])) {
			// без параметра EvnDirection_id p_EvnQueue_cancel направлению не установится статус отменено/отклонено
			return array(
				'success' => false,
				'Error_Msg' => 'Не удалось получить идентификатор направления.'
			);
		}
		$this->load->model('EvnDirection_model', 'edmodel');
		$err = $this->edmodel->checkEvnDirectionCanBeCancelled($data);
		if (!empty($err)) {
			return array(
				'success' => false,
				'Error_Msg' => $err
			);
		}

		$this->load->model('Mse_model', 'msemodel');
		$err = $this->msemodel->cancelEvnPrescrbyRecord($data);
		if (!empty($err)) {
			$this->rollbackTransaction();
			return array(
				'success' => false,
				'Error_Msg' => $err
			);
		}

		$data['DirFailType_id'] = null;
		if (!empty($data['EvnStatusCause_id'])) {
			// значит DirFailType_id вычисляем на основе EvnStatusCause_id
			$data['DirFailType_id'] = $this->getFirstResultFromQuery("select top 1 escl.DirFailType_id from v_EvnStatusCauseLink escl (nolock) where escl.EvnStatusCause_id = :EvnStatusCause_id", array(
				'EvnStatusCause_id' => $data['EvnStatusCause_id']
			));
		}

		// p_EvnQueue_cancel не записывается ни причина отмены направления ни дата отмены ни кто отменил
		// p_EvnDirection_cancel и p_EvnDirection_decline использовать для этого бесполезно, т.к. пациент останется в очереди
		$result = $this->swUpdate('EvnDirection', array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'TimetableGraf_id' => null,
			'TimetableStac_id' => null,
			'TimetableMedService_id' => null,
			'TimetablePar_id' => null,
			'TimetableResource_id' => null,
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnDirection_failDT' => $this->currentDT->format('Y-m-d H:i:s'),
			'pmUser_failID' => $data['pmUser_id'],
			'Lpu_cid' => $data['session']['lpu_id'],
			'MedStaffFact_fid' => (!empty($data['session']['CurMedStaffFact_id'])) ? $data['session']['CurMedStaffFact_id'] : null
		), false);
		if (!$this->isSuccessful($result)) {
			return array(
				'success' => false,
				'Error_Msg' => is_array($result) ? $result[0]['Error_Msg'] : 'Не удалось записать дату отмены направления'
			);
		}

		if (!empty($data['EvnStatusCause_id'])) {
			// значит QueueFailCause_id вычисляем на основе EvnStatusCause_id
			$data['QueueFailCause_id'] = $this->getFirstResultFromQuery("select top 1 escl.QueueFailCause_id from v_EvnStatusCauseLink escl (nolock) where escl.EvnStatusCause_id = :EvnStatusCause_id", array(
				'EvnStatusCause_id' => $data['EvnStatusCause_id']
			));
		}

		if (empty($data['QueueFailCause_id'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Необходимо указать причину отмены постановки в очередь'
			);
		}

		$queryParams = array(
			'EvnQueue_id' => $data['EvnQueue_id'],
			'EvnDirection_id' => !empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : null,
			'QueueFailCause_id' => $data['QueueFailCause_id'],
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : null,
			'EvnComment_Comment' => !empty($data['EvnComment_Comment']) ? substr($data['EvnComment_Comment'], 0, 2048) : '',
			'cancelType' => !empty($data['cancelType']) ? $data['cancelType'] : 'cancel',
			'pmUser_id' => $data['pmUser_id']
		);
		$result = $this->execCommonSP("p_EvnQueue_cancel", $queryParams, 'array_assoc');
		if (!empty($data['EvnQueueStatus_id'])) {
			$resp_eq = $this->queryResult("
				declare
					@Err_Code int,
					@Err_Msg varchar(4000);

				set nocount on;

				begin try
					update EvnQueue with (rowlock)
					set EvnQueueStatus_id = :EvnQueueStatus_id
					where EvnQueue_id = :EvnQueue_id
				end try

				begin catch
					set @Err_Code = error_number();
					set @Err_Msg = error_message();
				end catch

				set nocount off;

				select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
			", array(
					'EvnQueue_id' => $data['EvnQueue_id'],
					'EvnQueueStatus_id' => $data['EvnQueueStatus_id'],
				));
			if (!$this->isSuccessful($resp_eq)) {
				return array(
					'success' => false,
					'Error_Msg' => is_array($resp_eq) ? $resp_eq[0]['Error_Msg'] : 'Не удалось обновить статус записи в очереди'
				);
			}
		}
		if ($result['success']) {
			if (!empty(!empty($data['EvnDirection_id']))) {
				$this->load->model('ApprovalList_model');
				$this->ApprovalList_model->deleteApprovalList(array(
					'ApprovalList_ObjectName' => 'EvnDirection',
					'ApprovalList_ObjectId' => $data['EvnDirection_id']
				));
			}
			$this->edmodel->sendCancelEvnDirectionMessage($data);
		}
		return $result;
	}

	/**
	 * Отмена записи из очереди
	 */
	function mCancelQueueRecord($data)
	{
		if (empty($data['EvnDirection_id']) && empty($data['EvnQueue_id'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не идентификатор направления или идентификатор очереди'
			);
		}

		if (empty($data['EvnDirection_id']) && !empty($data['EvnQueue_id'])) {
			$data['EvnDirection_id']=$this->getFirstResultFromQuery('
				select EvnDirection_id from v_EvnQueue with (nolock) where EvnQueue_id = :EvnQueue_id
			', array(
				'EvnQueue_id' => $data['EvnQueue_id'],
			));
		}

		if (empty($data['EvnDirection_id'])) {
			// без параметра EvnDirection_id p_EvnQueue_cancel направлению не установится статус отменено/отклонено
			return array(
				'success' => false,
				'Error_Msg' => 'Не удалось получить идентификатор направления.'
			);
		}
		$this->load->model('EvnDirection_model', 'edmodel');
		$err = $this->edmodel->checkEvnDirectionCanBeCancelled($data);
		if (!empty($err)) {
			return array(
				'success' => false,
				'Error_Msg' => $err
			);
		}

		$this->load->model('Mse_model', 'msemodel');
		$err = $this->msemodel->cancelEvnPrescrbyRecord($data);
		if (!empty($err)) {
			$this->rollbackTransaction();
			return array(
				'success' => false,
				'Error_Msg' => $err
			);
		}

		$data['DirFailType_id'] = null;
		if (!empty($data['EvnStatusCause_id'])) {
			// значит DirFailType_id вычисляем на основе EvnStatusCause_id
			$data['DirFailType_id'] = $this->getFirstResultFromQuery("select top 1 escl.DirFailType_id from v_EvnStatusCauseLink escl (nolock) where escl.EvnStatusCause_id = :EvnStatusCause_id", array(
				'EvnStatusCause_id' => $data['EvnStatusCause_id']
			));
		}

		// p_EvnQueue_cancel не записывается ни причина отмены направления ни дата отмены ни кто отменил
		// p_EvnDirection_cancel и p_EvnDirection_decline использовать для этого бесполезно, т.к. пациент останется в очереди
		$result = $this->swUpdate('EvnDirection', array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'TimetableGraf_id' => null,
			'TimetableStac_id' => null,
			'TimetableMedService_id' => null,
			'TimetablePar_id' => null,
			'TimetableResource_id' => null,
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnDirection_failDT' => $this->currentDT->format('Y-m-d H:i:s'),
			'pmUser_failID' => $data['pmUser_id'],
			'Lpu_cid' => $data['session']['lpu_id'],
			'MedStaffFact_fid' => (!empty($data['session']['CurMedStaffFact_id'])) ? $data['session']['CurMedStaffFact_id'] : null
		), false);
		if (!$this->isSuccessful($result)) {
			return array(
				'success' => false,
				'Error_Msg' => is_array($result) ? $result[0]['Error_Msg'] : 'Не удалось записать дату отмены направления'
			);
		}

		if (!empty($data['EvnStatusCause_id'])) {
			// значит QueueFailCause_id вычисляем на основе EvnStatusCause_id
			$data['QueueFailCause_id'] = $this->getFirstResultFromQuery("select top 1 escl.QueueFailCause_id from v_EvnStatusCauseLink escl (nolock) where escl.EvnStatusCause_id = :EvnStatusCause_id", array(
				'EvnStatusCause_id' => $data['EvnStatusCause_id']
			));
		}

		if (empty($data['QueueFailCause_id'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Необходимо указать причину отмены постановки в очередь'
			);
		}

		$queryParams = array(
			'EvnQueue_id' => $data['EvnQueue_id'],
			'EvnDirection_id' => !empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : null,
			'QueueFailCause_id' => $data['QueueFailCause_id'],
			'EvnStatusCause_id' => !empty($data['EvnStatusCause_id']) ? $data['EvnStatusCause_id'] : null,
			'EvnComment_Comment' => !empty($data['EvnComment_Comment']) ? substr($data['EvnComment_Comment'], 0, 2048) : '',
			'cancelType' => !empty($data['cancelType']) ? $data['cancelType'] : 'cancel',
			'pmUser_id' => $data['pmUser_id']
		);
		$result = $this->execCommonSP("p_EvnQueue_cancel", $queryParams, 'array_assoc');
		if (!empty($data['EvnQueueStatus_id'])) {
			$resp_eq = $this->queryResult("
				declare
					@Err_Code int,
					@Err_Msg varchar(4000);

				set nocount on;

				begin try
					update EvnQueue with (rowlock)
					set EvnQueueStatus_id = :EvnQueueStatus_id
					where EvnQueue_id = :EvnQueue_id
				end try

				begin catch
					set @Err_Code = error_number();
					set @Err_Msg = error_message();
				end catch

				set nocount off;

				select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
			", array(
				'EvnQueue_id' => $data['EvnQueue_id'],
				'EvnQueueStatus_id' => $data['EvnQueueStatus_id'],
			));
			if (!$this->isSuccessful($resp_eq)) {
				return array(
					'success' => false,
					'Error_Msg' => is_array($resp_eq) ? $resp_eq[0]['Error_Msg'] : 'Не удалось обновить статус записи в очереди'
				);
			}
		}
		if ($result['success']) {
			$this->edmodel->sendCancelEvnDirectionMessage($data);
		}
		return $result;
	}

	/**
	 * Удаление записи из очереди
	 */
	function deleteQueueRecord($data) {
		$this->load->model('Evn_model', 'emodel');
		$response = $this->emodel->getLinkedEvnData(array('object' => 'EvnQueue','object_id' => $data['EvnQueue_id']));
		if(!empty($response))
		{
			if(isset($response[0]['Error_Msg']))
			{
				return $response;
			}
			return array(array('Error_Msg' => 'Найдены события, связанные с направлением. Удаление невозможно!'));
		}

		if (!empty($data['WithEvnDirection'])) {
			$data['EvnDirection_id'] = $this->getFirstResultFromQuery('
				select EvnDirection_id from v_EvnQueue with (nolock) where EvnQueue_id = :EvnQueue_id
			', array(
				'EvnQueue_id' => $data['EvnQueue_id'],
			));
			if (false === $data['EvnDirection_id']) {
				return array(array('Error_Msg' => 'Не удалось выполнить запрос направления. Удаление невозможно!'));
			}
			if ($data['EvnDirection_id']) {
				$this->load->model('EvnDirection_model', 'edmodel');
				$response = $this->edmodel->deleteEvnDirection($data);
				if (isset($response[0]['Error_Msg'])) {
					return $response;
				}
			}
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

		//echo getDebugSQL($query, array('EvnQueue_id' => $data['EvnQueue_id'],'pmUser_id' => $data['pmUser_id']));die;

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
	 * Прием человека из очереди
	 * Признаком отметки "принят" является заполненное поле EvnQueue_recDT (а также совместное с ним pmUser_recId)
	 */
	function ReceptionFromQueue( $data ) {
		// p_EvnQueue_record использовать нельзя, т.к. там записывается статус "Обслужено",
		// надо вызывать метод applyEvnDirectionFromQueue, в которой используется хранимка p_EvnDirection_recordFromQueue
		return array(
			'success' => false,
			'Error_Msg' => 'Запись из очереди на конкретную бирку производится неправильным методом'
		);
		$queryParams = array(
			'EvnQueue_id' => $data['EvnQueue_id'],
			'EvnDirection_id' => !empty($data['EvnDirection_id']) ? $data['EvnDirection_id'] : null,
			'EvnQueue_recDT' => !empty($data['EvnQueue_recDT']) ? $data['EvnQueue_recDT'] : null,
			'pmUser_id' => $data['pmUser_id'],
			'TimetableGraf_id' => !empty($data['TimetableGraf_id']) ? $data['TimetableGraf_id'] : null,
			'TimetableStac_id' => !empty($data['TimetableStac_id']) ? $data['TimetableStac_id'] : null,
			'TimetableMedService_id' => !empty($data['TimetableMedService_id']) ? $data['TimetableMedService_id'] : null,
		);

		if ( ($resp = $this->execCommonSP("p_EvnQueue_record", $queryParams)) ) {
			return $resp;
		}
		else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка запроса к БД.'
			);
		}
	}

	/**
	 * Проверка повторной постановки в очередь
	 */
	function checkQueueDuplicates( $data ) {

		$select ='';
		$join = '';
		$filters = '';
		$params = array(
			'Person_id' => $data['Person_id'],
			'LpuUnit_did' => $data['LpuUnit_did'],
			'LpuSectionProfile_did' => $data['LpuSectionProfile_did']
		);

		if($data['DirType_id']==16){

			$selectPersonFio = "PS.Person_SurName+' '+LEFT(PS.Person_FirName,1)+' '+LEFT(PS.Person_SecName,1) as Person_Fio";
			$joinPersonEncrypHIV = "";
			if (allowPersonEncrypHIV($data['session'])) {
				$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = PS.Person_id";
				$selectPersonFio = "case when peh.PersonEncrypHIV_Encryp is null 
					then PS.Person_SurName+' '+LEFT(PS.Person_FirName,1)+' '+LEFT(PS.Person_SecName,1) 
					else peh.PersonEncrypHIV_Encryp 
				end as Person_Fio";
			}
			$select.="
				,MP.Person_Fio as MP_Fio
				,LPU.Lpu_Nick
				,CONVERT(varchar, EQ.EvnQueue_setDT,104) as EvnQueue_setDT
				,{$selectPersonFio}
				";
			$join .= '
				left join v_MedPersonal MP with(nolock) on MP.MedPersonal_id=EQ.MedPersonal_did
				left join v_Lpu LPU with(nolock) on LPU.Lpu_id=EQ.Lpu_id
				left join v_PersonState PS with(nolock) on PS.Person_id = EQ.Person_id
				' . $joinPersonEncrypHIV;
		}

		// если перенаправление (указано направление), то провряем только объекты очереди которые не связаны с данным направлением.
		if (!empty($data['redirectEvnDirection']) && !empty($data['EvnDirection_id'])) {
			$filters .= " and EQ.EvnDirection_id != :EvnDirection_id";
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
		}

		$sql = "
			select
				EQ.EvnQueue_id
				,EQ.EvnDirection_id
				,ed.EvnDirection_pid
				,es.EvnStatus_SysNick
				".$select."
			from
				v_EvnQueue EQ with (NOLOCK)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = EQ.EvnDirection_id 
					and ED.Person_id = EQ.Person_id -- полезно для скорости работы запроса ограничить еще и по Person_id
					--обслуженное или удаленное направление не должно учитываться при выписке нового
					and ed.EvnDirection_failDT is null and ed.DirFailType_id is null 
				-- и при повторной постановке в очередь
				outer apply (
					select top 1 e.EvnDirection_id
					from v_EvnPS e with(nolock)
					where e.EvnDirection_id = ED.EvnDirection_id
					union 
					select top 1 e.EvnDirection_id
					from v_EvnPL e with(nolock)
					where e.EvnDirection_id = ED.EvnDirection_id
					union 
					select top 1 e.EvnDirection_id
					from v_EvnPLStom e with(nolock)
					where e.EvnDirection_id = ED.EvnDirection_id
					union 
					select top 1 e.EvnDirection_id
					from v_EvnVizitPL e with(nolock)
					where e.EvnDirection_id = ED.EvnDirection_id
					union 
					select top 1 e.EvnDirection_id
					from v_EvnVizitPLStom e with(nolock)
					where e.EvnDirection_id = ED.EvnDirection_id
				) e
				inner join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
					and es.EvnStatus_SysNick not in ('Canceled', 'Declined', 'Serviced')
				".$join."
				left join LpuUnit lu with (nolock) on lu.LpuUnit_id = EQ.LpuUnit_did
				left join LpuBuilding lb with (nolock) on lb.LpuBuilding_id = lu.LpuBuilding_id
				left join Lpu lpu2 with (nolock) on lpu2.Lpu_id = lb.Lpu_id
				left join Org org with (nolock) on org.Org_id = lpu2.Org_id
			where
				EQ.Person_id = :Person_id
				and e.EvnDirection_id is null
				and EQ.LpuUnit_did = :LpuUnit_did
				and EQ.LpuSectionProfile_did = :LpuSectionProfile_did
				and EQ.EvnQueue_recDT is null
				and EQ.EvnQueue_failDT is null
				and EQ.QueueFailCause_id is null
				and ED.DirType_id != 5 -- исключая экстренные направления
				and isnull(org.Org_IsNotForSystem,0) != 2 -- исключая огранизации с флагом Не работает в данной Системе
				{$filters}
		";

		/* echo getDebugSQL($sql, array(
		  'Person_id' => $data['Person_id'],
		  'LpuUnit_did' => $data['LpuUnit_did'],
		  'LpuSectionProfile_did' => $data['LpuSectionProfile_did'])); exit; */

		$res = $this->db->query( $sql, $params );
		if (is_object($res)) {
			$resp = $res->result('array');
			if (count($resp) > 0) {
				$resp = $resp[0];
				if ( $resp['EvnQueue_id'] != null&&$data['DirType_id']!=16 ) {
					return true;
				}
				$response = array(
					'EvnQueue_id' => $resp['EvnQueue_id'],
					'EvnDirection_id' => $resp['EvnDirection_id'],
					'EvnDirection_pid' => $resp['EvnDirection_pid'],
					'EvnStatus_SysNick' => $resp['EvnStatus_SysNick'],
				);

				$response['warning'] = "
					Пациент ".$resp['Person_Fio']." уже находится в очереди по этому профилю с ".$resp['EvnQueue_setDT']." 
					<br>в ЛПУ: ".$resp['Lpu_Nick'].", врач: ".$resp['MP_Fio'].". 
					<br>Исключить пациента из очереди по данному профилю?
				";
				return $response;
			}
		}
		return false;
	}

	/**
	 * Возвращает идентификатор направления по идентификатору очереди
	 */
	function getDirectionId($evnqueue_id) {
		//print_r($data);
		$res_array = array();

		$query = "
			select
				EvnDirection_id
			from
				v_EvnQueue eq with(nolock)
			where
				eq.EvnQueue_id = :EvnQueue_id
		";

		$result = $this->db->query($query, array('EvnQueue_id' => $evnqueue_id));

		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( isset($res[0]) && isset($res[0]['EvnDirection_id']) && !empty($res[0]['EvnDirection_id']) ) {
				return $res[0]['EvnDirection_id'];
			}
		}
		return false;
	}

	/**
	 * Возвращает идентификатор очереди по идентификатору направления
	 */
	function getQueueId($evndirection_id) {
		//print_r($data);
		$res_array = array();

		$query = "
			select
				EvnQueue_id
			from
				v_EvnDirection_all ed with(nolock)
			where
				ed.EvnDirection_id = :EvnDirection_id
		";

		$result = $this->db->query($query, array('EvnDirection_id' => $evndirection_id));

		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( isset($res[0]) && isset($res[0]['EvnQueue_id']) && !empty($res[0]['EvnQueue_id']) ) {
				return $res[0]['EvnQueue_id'];
			}
		}
		return false;
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	function checkRecordQueue( $data ) {
		$sql = "
			select top 1
				MedService_id
			from
				v_MedService ms with (nolock)
			where
				MedService_id = :MedService_id
				and RecordQueue_id = 1 
		";

		$res = $this->db->query( $sql,
			array('MedService_id' => $data['MedService_id']) );
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}
		if(count($res)>0){
			return true;
		}
		return false;
	}

	/**
	 * Проверка возможности постановки в очередь при наличии свободных бирок
	 */
	function checkMSQueueOnFree( $data ) {
		$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedService with (nolock) where MedService_id = ?", array($data['MedService_id']));
		$maxDays = GetPolDayCount($msflpu['Lpu_id'], $data['MedService_id']);
		$EndDate = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : date( "Y-m-d", strtotime( "+1 year", time()) );
		$sql = "
			declare @curdt datetime = dbo.tzGetDate();
			
			select
				tt.TimeTableMedService_id
			from
				v_TimeTableMedService_lite tt with(nolock)
				inner join v_MedService MS with(nolock) on MS.MedService_id = tt.MedService_id
			where
				tt.TimetableType_id not in (2,3,4)
				and MS.MedService_id = :MedService_id
				and (
					MS.RecordQueue_id = 1 -- Запретить всегда
					or ( -- Запретить при наличии свободных бирок
						tt.TimeTableMedService_begTime between @curdt and :EndDate
						and tt.Person_id is null
						and MS.RecordQueue_id = 2
					)
				)
		";

		$res = $this->db->query( $sql,
			array('MedService_id' => $data['MedService_id'], 'EndDate' => $EndDate) );
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}

		if ( isset( $res[0] ) && $res[0]['TimeTableMedService_id'] != null ) {
			return false;
		}
		return true;
	}

	/**
	 * Проверка возможности постановки в очередь при наличии свободных бирок
	 */
	function checkRQueueOnFree( $data ) {
		$filter = "";
		$params = array(
			'Resource_id' => !empty($data['Resource_id'])?$data['Resource_id']:null,
			'MedService_id' => !empty($data['MedService_id'])?$data['MedService_id']:null,
		);

		$msflpu = $this->getFirstRowFromQuery("
			select MS.Lpu_id 
			from v_MedService MS with (nolock) 
			inner join v_Resource R with (nolock) on R.MedService_id = MS.MedService_id
			where R.Resource_id = :Resource_id or MS.MedService_id = :MedService_id",
			$params
		);
		$maxDays = GetPolDayCount($msflpu['Lpu_id']);
		$EndDate = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : date( "Y-m-d", strtotime( "+1 year", time()) );
		$params['EndDate'] = $EndDate;

		if (!empty($data['UslugaComplex_did'])) {
			$filter .= "and UCMS.UslugaComplex_id = :UslugaComplex_id";
			$params['UslugaComplex_id'] = $data['UslugaComplex_did'];
		}

		if (!empty($data['Resource_id'])) {
			$filter .= " and UCR.Resource_id = :Resource_id ";
		}
		elseif (!empty($data['MedService_id'])) {
			$filter .= " and MS.MedService_id = :MedService_id ";
		}

		$sql = "
			declare @curdt datetime = dbo.tzGetDate();
			 
			select top 1
				count(tt.TimeTableResource_id)
			from
				v_TimeTableResource_lite tt with(nolock)
				inner join v_UslugaComplexResource UCR with(nolock) on UCR.Resource_id = tt.Resource_id
				inner join v_UslugaComplexMedService UCMS with(nolock) on UCMS.UslugaComplexMedService_id = UCR.UslugaComplexMedService_id
				inner join v_MedService MS with(nolock) on MS.MedService_id = UCMS.MedService_id
			where
				tt.TimetableType_id not in (2,3,4)
				and (
					MS.RecordQueue_id = 1 -- Запретить всегда
					or ( -- Запретить при наличии свободных бирок
						tt.TimeTableResource_begTime between @curdt and :EndDate
						and tt.Person_id is null
						and MS.RecordQueue_id = 2
					)
				)
				{$filter}
		";

		$count = $this->getFirstResultFromQuery($sql, $params);

		return $count>0?false:true;
	}

	/**
	 * Проверка возможности постановки в очередь при наличии свободных бирок
	 */
	function checkQueueOnFree( $data ) {
		$msflpu = $this->getFirstRowFromQuery("select Lpu_id from v_MedStaffFact with (nolock) where MedStaffFact_id = ?", array($data['MedStaffFact_sid']));
		$maxDays = GetPolDayCount($msflpu['Lpu_id'], $data['MedStaffFact_sid']);
		$EndDate = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : date( "Y-m-d", strtotime( "+1 year", time()) );
		$sql = "
			declare @curdt datetime = dbo.tzGetDate();
			
			select top 1
				tt.TimetableGraf_id
			from
				v_TimetableGraf_lite tt with(nolock)
				inner join v_MedStaffFact MSF with(nolock) on MSF.MedStaffFact_id = tt.MedStaffFact_id
			where
				tt.TimetableType_id not in (2,3,4)
				and tt.Person_id is null
				and cast(tt.TimetableGraf_begTime as date) between @curdt and :EndDate
				and tt.MedStaffFact_id = :MedStaffFact_id
				and MSF.MedStaffFact_IsQueueOnFree = 1
		";

		$res = $this->db->query( $sql,
			array('MedStaffFact_id' => $data['MedStaffFact_sid'], 'EndDate' => $EndDate) );
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}

		if ( isset( $res[0] ) && $res[0]['TimetableGraf_id'] != null ) {
			return false;
		}
		return true;
	}

	/**
	 * Проверка возможности постановки в очередь при наличии свободных бирок
	 */
	function checkQueueTTSOnFree( $data ) {
		$lslpu = $this->getFirstRowFromQuery("select Lpu_id from v_LpuSection with (nolock) where LpuSection_id = ?", array($data['LpuSection_did']));
		$maxDays = GetStacDayCount($lslpu['Lpu_id'], $data['LpuSection_did']);
		$EndDate = !empty($maxDays) ? date( "Y-m-d", strtotime( "+".$maxDays." days", time()) ) : date( "Y-m-d", strtotime( "+1 year", time()) );
		$sql = "
			declare @curdt datetime = dbo.tzGetDate();
			
			select top 1
				tt.TimetableStac_id
			from
				v_TimetableStac_lite tt with(nolock)
				inner join v_LpuSection LS with(nolock) on LS.LpuSection_id = tt.LpuSection_id
			where
				tt.TimetableType_id not in (2,3,4)
				and tt.Person_id is null
				and cast(tt.TimeTableStac_setDate as date) between @curdt and :EndDate
				and tt.LpuSection_id = :LpuSection_id
				and LS.LpuSection_IsQueueOnFree = 1
		";

		$res = $this->db->query( $sql,
			array('LpuSection_id' => $data['LpuSection_did'], 'EndDate' => $EndDate) );
		if ( is_object( $res ) ) {
			$res = $res->result( 'array' );
		}

		if ( isset( $res[0] ) && $res[0]['TimetableStac_id'] != null ) {
			return false;
		}
		return true;
	}

	/**
	 * Постановка направления в очередь
	 * @param array $data
	 * @return array
	 */
	function insertQueue($data){
		
		// Массив с информацией о существующих направлениях
		$EvnDirectionInfo = array();

		switch ($data['LpuUnitType_SysNick'])
		{
			case 'polka':
				if (empty($data['MedPersonal_did']))
				{
					return array(
						array(
							'success'=>false,
							'Error_Msg'=>'Не указан врач!'
						)
					);
					break;
				}
				$data['object'] = 'TimetableGraf';
				break;
			case 'stac': case 'dstac': case 'hstac': case 'pstac':
				$data['object'] = 'TimetableStac';
			break;
			case 'parka':
				if (!empty($data['Resource_id'])) {
					$data['object'] = 'TimetableResource';
				} else {
					$data['object'] = 'TimetableMedService';
				}

				break;
			default:
				return array(
					array(
						'success'=>false,
						'Error_Msg'=>'Неверно указаны входящие параметры!'
					)
				);
				break;
		}
		if(!empty($data['MedService_id'])){
			$response =$this->checkRecordQueue($data);
			if(true === $response){
				return array(
					array(
						'success'=>false,
						'Error_Msg'=>'Запись в очередь на службу запрещена'
					)
				);
			}
			// Нужно для отображения направления на службу
			$EvnDirectionInfo['MedService_Nick'] = $this->getFirstResultFromQuery("
				select top 1 MS.MedService_Name
				from v_MedService MS with(nolock)
				where MS.MedService_id = :MedService_id
			", array(
				'MedService_id' => !empty($data['MedService_id']) ? $data['MedService_id'] : null
			), true);
			if ($EvnDirectionInfo['MedService_Nick'] === false) {
				return $this->createError('', 'Ошибка при получении службы');
			}

			if($data['LpuUnitType_SysNick'] == 'parka'){
				if (!empty($data['withResource']) && $data['withResource']) {
					$response = $this->checkRQueueOnFree($data);
				} else {
					$response = $this->checkMSQueueOnFree($data);
				}
				if (false === $response)
				{
					return array(
						array(
							'success'=>false,
							'Error_Msg'=>'Постановка в очередь при наличии свободных бирок запрещена.'
						)
					);
				}
			}
		}
		if ($data['LpuUnitType_SysNick'] == 'polka' && !empty($data['MedStaffFact_sid']))
		{
			// сейчас эта проверка проверяет только бирки полки
			$response = $this->checkQueueOnFree($data);
			if (false === $response)
			{
				return array(
					array(
						'success'=>false,
						'Error_Msg'=>'Постановка в очередь при наличии свободных бирок запрещена.'
					)
				);
			}
		}
		if ($data['object'] == 'TimetableStac' && !empty($data['LpuSection_did']))
		{
			// сейчас эта проверка проверяет только бирки полки
			$response = $this->checkQueueTTSOnFree($data);
			if (false === $response)
			{
				return array(
					array(
						'success'=>false,
						'Error_Msg'=>'Постановка в очередь при наличии свободных бирок запрещена.'
					)
				);
			}
		}

		/**
		 * #27044
		 * "Повторная постановка пациента в очередь по профилю запрещена" - данное ограничение
		 * оставить только для направлений типа
		 * 1 На госпитализацию плановую,
		 * 3 На консультацию,
		 * 4 На восстановительное лечение,
		 * 5 На госпитализацию экстренную,
		 * 6 На осмотр с целью госпитализации.
		 */
		if(isset($data['Prescr'])&&$data['Prescr']=="Prescr"){

		}
		else if ( in_array($data['DirType_id'], array(1,3,4,5,6,16)) )
		{
			if (!isset($data['AnswerQueue']) || $data['AnswerQueue']) {
				$response = $this->checkQueueDuplicates($data);
				if ($response)
				{
					if(isset($response['warning'])&&!isset($data['AnswerRecord']) || $data['AnswerRecord']){
						array_walk($response, 'ConvertFromWin1251ToUTF8');
						return array(
							array(
								'success'=>false,
								'queue'=>$response
							)
						);
					}else{
						return array(
							array(
								'success'=>false,
								'Error_Msg'=>'Повторная постановка пациента в очередь по профилю запрещена.'
							)
						);
					}
				}
			}
		}
		//Проверка существования записи по профилю в отделение
		if (!isset($data['AnswerRecord']) || $data['AnswerRecord']) {
			$this->load->model('Timetable_model', 'ttmodel');
			$record = $this->ttmodel->checkRecordExists($data);
			if ($record) {
				$rightsToClear = $this->ttmodel->checkHasRightsToClearRecord(array(
					'session' => $data['session'],
					'object' => 'TimetableGraf',
					'TimetableGraf_id' => $record['TimetableGraf_id']
				));
				$record['allowClear'] = ($rightsToClear === true) ? true : false;

				array_walk($record, 'ConvertFromWin1251ToUTF8');

				return array(
					array(
						'success'=>false,
						'record'=>$record
					)
				);
			}
		}

		$MedServiceType_SysNick = null;
		if ($data['object'] == 'TimetableMedService') {
			$MedServiceType_SysNick = $this->getFirstResultFromQuery("
				select top 1 MST.MedServiceType_SysNick
				from v_MedService MS with(nolock)
				inner join v_MedServicetype MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
				where MS.MedService_id = coalesce(:MedService_pzid, :MedService_did)
			", array(
				'MedService_did' => !empty($data['MedService_did']) ? $data['MedService_did'] : null,
				'MedService_pzid' => !empty($data['MedService_pzid']) ? $data['MedService_pzid'] : null,
			), true);
			if ($MedServiceType_SysNick === false) {
				return $this->createError('', 'Ошибка при получении типа службы');
			}
		}

		$data['toQueue'] = true;
		// сохраняем направление, всегда

		if(isset($data['MedService_did'])){
			$data['LpuSectionProfile_did']=null;
		}
		$this->load->model('EvnDirection_model', 'edmodel');
		if ($this->usePostgreLis && in_array($MedServiceType_SysNick, array('lab','pzm'))) {
			$this->load->swapi('lis');
			$response = $this->lis->POST('EvnDirection', $data, 'list');
		} else {
			$response = $this->edmodel->saveEvnDirection($data);
		}
		if (!$this->isSuccessful($response)) {
			return $response;
		}
		$EvnDirectionInfo['EvnDirection_Num'] = !empty($response[0]['EvnDirection_Num'])?$response[0]['EvnDirection_Num']:null;
		$EvnDirectionInfo['EvnDirection_id'] = !empty($response[0]['EvnDirection_id'])?$response[0]['EvnDirection_id']:null;

		// сохраняем заказ, если есть необходимость
		if ($this->usePostgreLis && in_array($MedServiceType_SysNick, array('lab','pzm'))) {

			$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];
			$data['EvnLabRequest_id'] = isset($response[0]['EvnLabRequest_id']) ? $response[0]['EvnLabRequest_id'] : null;
			$this->load->swapi('lis');
			$order = $this->lis->POST('EvnUsluga/Order', $data, 'single');
			if (!$this->isSuccessful($order)) {
				return array($order);
			}
		} else {
			$this->load->model('EvnUsluga_model', 'eumodel');
			try {
				$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];
				$order = $this->eumodel->saveUslugaOrder($data);
			} catch (Exception $e) {
				return array(array('success' => false, 'Error_Msg' => $e->getMessage()));
			}
		}

		if ($this->getRegionNick() == 'ufa' && ($data['HIVContingentTypeFRMIS_id'] || $data['HormonalPhaseType_id']) || isset($data['CovidContingentType_id'])) {
			$this->load->model('PersonDetailEvnDirection_model', 'edpdmodel');
			$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];
			$data['PersonDetailEvnDirection_id'] = 0;
			$pdresponse = $this->edpdmodel->doSave($data);
		}

		if (is_array($response) && (count($response)>0)) {
			if (empty($response[0]['Error_Msg'])) {
				$data['EvnDirection_id'] = $response[0]['EvnDirection_id'];

			} else {
				return array(
					array(
						'success'=>false,
						'Error_Msg'=>$response[0]['Error_Msg']
					)
				);
			}
		} else {
			return array(
				array(
					'success'=>false,
					'Error_Msg'=>'Произошла ошибка при сохранении направления'
				)
			);
		}
		if (!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 11 && !empty($data['EvnDirection_id'])) {
			// значит нам туда дорога, зна-чит-нам-ту-да-до-ро-га (Лабораторная диагностика)
			$UslugaList = $this->getUslugaWithoutDirectoryList($data);

			if(!empty($UslugaList) && is_array($UslugaList) && count($UslugaList)>0){
				$msg = "Услуги могут быть объединены: <br>";
				foreach($UslugaList as $key => $usluga){
					$msg .= (intval($key)+1).'. '.$usluga['UslugaComplex_Name'].'<br>';
				}
				$response[0]['EvnDirection_id'] = $data['EvnDirection_id'];
				$UslugaListJSON = json_encode($UslugaList);
			}
		}

		if(!empty($msg)) $response[0]['addingMsg'] = $msg;
		if(!empty($EvnDirectionInfo)) $response[0]['EvnDirectionInfo'] = $EvnDirectionInfo;
		if(!empty($UslugaListJSON)) $response[0]['UslugaList'] = $UslugaListJSON;
		//$response[0]['EvnQueue_id'] = $this->getQueueId($response[0]['EvnDirection_id']);
		return $response;
	}

	/**
	 * Выписка направления в очередь
	 */
	function addQueue($data=null) {
		$this->load->helper('Reg_helper');
		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['Queue'] = array_merge($this->inputRules['addQueue'], $this->EvnDirection->getSaveRules(array(
			'lpuSectionProfileNotRequired' => true
		)));

		$data = $this->ProcessInputData('Queue', true);
		if ($data === false) { return false; }

		if (empty($data['LpuSectionProfile_id']) && $data['DirType_id'] != 9 && getRegionNick() != 'ekb') { // для ВК профиль не обязателен (refs #83337)
			$this->ReturnError('Поле "Профиль" обязательно для заполнения');
			return false;
		}

		$response = $this->dbmodel->insertQueue($data);

		$this->transferQueue($data, $response);
		$this->ProcessModelSave($response, true)->ReturnData();
		return true;
	}

	/**
	 * Отправка данных направлений с типом функциональная диагностика в сторонние сервисы
	 *
	 * @param array $data Данные полученные с сервера
	 * @param array $response Ответ возвращаемый методом [[\promed\controllers\Queue::insertQueue()]]
	 * @return void
	 */
	function transferQueue($data, $response){
		if (isset($response[0])) {
			$response = $response[0];
		}

		if (empty($response['EvnDirection_id'])) {
			return;
		}

		$EvnDirection_id = $response['EvnDirection_id'];

		// Получаем данные направления но только с типом функциональная диагностика
		$sql = "
			SELECT TOP 1
				ed.EvnDirection_Descr,
				ed.MedService_id,
				ps.Person_id,
				ps.Person_SurName,
				ps.Person_FirName,
				ps.Person_SecName,
				CONVERT(varchar,ps.Person_BirthDay,104) as Person_BirthDay,
				ps.Sex_id,
				a.Address_Address,
				CONVERT(varchar,ttms.TimetableMedService_begTime,104) as TimetableMedService_begTime_Date,
				CONVERT(varchar,ttms.TimetableMedService_begTime,108) as TimetableMedService_begTime_Time,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Name,
				mp.MedPersonal_id,
				mp.Person_Fio as MedPersonal_Person_Fio
			FROM
				v_EvnDirection_all ed
				INNER JOIN v_PersonState ps WITH(nolock) ON(ps.Person_id = ed.Person_id)
				LEFT JOIN v_Address a WITH(nolock) ON(a.Address_id=ps.UAddress_id)
				LEFT JOIN v_TimeTableMedService_lite ttms WITH(nolock) ON(ttms.TimeTableMedService_id=ed.TimeTableMedService_id)
				-- Только направления связанные с функциональной диагностикой
				INNER JOIN v_EvnPrescrDirection epd WITH(nolock) ON(epd.EvnDirection_id=ed.EvnDirection_id)
				-- INNER JOIN v_EvnPrescrFuncDiag epfd WITH(nolock) ON(epfd.EvnPrescrFuncDiag_id=epd.EvnPrescr_id)
				INNER JOIN v_EvnPrescrFuncDiagUsluga epfdu WITH(nolock) ON(epfdu.EvnPrescrFuncDiag_id=epd.EvnPrescr_id)
				LEFT JOIN v_UslugaComplex uc WITH(nolock) ON(uc.UslugaComplex_id=epfdu.UslugaComplex_id)
				LEFT JOIN v_MedPersonal mp WITH(nolock) ON(mp.MedPersonal_id=ed.MedPersonal_id)
			WHERE
				ed.EvnDirection_id=:EvnDirection_id
		";
		$person = $this->db->query($sql,array('EvnDirection_id'=>$EvnDirection_id))->row_array();
		if (empty($person)) {
			return;
		}

		// Отправляем в АрхиМед
		$access = $this->retrieveAccessData($person['MedService_id']);
		if (empty($access['MedService_WialonURL']) || empty($access['MedService_WialonPort'])) {
			return;
		}

		// Данные для отправки
		$send_data = array(
			'PATIENT_ID' => $person['Person_id'], // ID пациента в БД ПроМед
			'PATIENT_NAME' => trim(trim($person['Person_SurName']) . ' ' . trim($person['Person_FirName']) . ' ' . trim($person['Person_SecName'])), // ФИО пациента;
			'PATIENT_DATEOFBIRTH' => $person['Person_BirthDay'], // дата рождения пациента d.m.Y
			'PATIENT_SEX' => $person['Sex_id'] == 1 ? 'м' : ($person['Sex_id'] == 2 ? 'ж' : ''), // пол пациента м / ж
			'PATIENT_HOME_ADDRESS' => $person['Address_Address'], // домашний адрес пациента
			'PRESCRIPTIO_ID' => $EvnDirection_id, // ID направления в БД ПроМед
			'STUDY_DATE' => (string)$person['TimetableMedService_begTime_Date'], // дата, на которую назначено исследование d.m.Y
			'STUDY_TIME' => preg_replace('#([0-9]{2}.[0-9]{2}).[0-9]{2}$#', '$1', $person['TimetableMedService_begTime_Time'] ), // время, на которое назначено исследование H:i
			'STUDY_TYPE_ID' => '', // ID вида исследования ПроМеда (Рентген, УЗИ, КТ и пр.).
			'STUDY_LIST' => array( // список исследований (предоставляемых услуг?) из БД ПроМед;
				array(
					'STUDY_ID' => $person['UslugaComplex_id'], // ID исследования (услуги) в БД ПроМед;
					'STUDY_NAME' => $person['UslugaComplex_Name'] // наименование исследования (услуги) в БД ПроМед;
				)
			),
			'DOCTOR_ID' => $person['MedPersonal_id'], // ID врача, назначившего исследование;
			'DOCTOR_NAME' => $person['MedPersonal_Person_Fio'], // ФИО врача, назначившего исследование;
			'STUDY_PURPOSE' => (string)$person['EvnDirection_Descr'], // цель исследования
		);

		// JSON_UNESCAPED_UNICODE для php 5.3
		$send_data_json = json_encode($send_data);
		$send_data_json = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $send_data_json);

		$this->load->helper('CURL');
		CURL(
			$access['MedService_WialonURL'].':'.$access['MedService_WialonPort'].'/STUDY_PRESCRIPTION_PM/',
			$send_data_json,
			'POST',
			null,
			array(
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json; charset=UTF-8",
				)
			)
		);
	}

	/**
	 * Возвращает данные для аутентификации в АрхиМед и пр.
	 *
	 * @param array $MedService_id
	 * @return array or false
	 */
	protected function retrieveAccessData( $MedService_id ){
		if ( empty($MedService_id) ) {
			return false;
		}

		$sql = "
			SELECT
				ms.MedService_id,
				ms.MedService_WialonURL,
				ms.MedService_WialonPort
			FROM
				v_MedService ms
				INNER JOIN v_MedServiceType mst ON(mst.MedServiceType_id=ms.MedServiceType_id)
			WHERE
				ms.MedService_id=:MedService_id
				-- 3 - диагностика
				AND mst.MedServiceType_Code=3
		";

		return $this->db->query($sql, array('MedService_id' => $MedService_id))->row_array();
	}

	/**
	 * Выписка направления в очередь для апи
	 */
	function addToQueueForApi($data) {

		$this->load->helper('Reg_helper');
		$response = $this->insertQueue($data);

		$this->transferQueue($data, $response);
		return $response;
	}

	/**
	 * Проверка возможности объединения услуг в одно направление
	 */
	function getUslugaWithoutDirectoryList($data) {
		$uslugaFilter = '';
		$MedServiceType = $this->getFirstRowFromQuery("
			SELECT
				mst.MedServiceType_SysNick
			FROM 
				v_MedService ms with(nolock)
				INNER JOIN v_MedServiceType mst with(nolock) ON mst.MedServiceType_id = ms.MedServiceType_id
			WHERE
				MedService_id = :MedService_id
		", $data);
		if(empty($MedServiceType)) return false;

		$MedServiceType_SysNick = $MedServiceType['MedServiceType_SysNick'];
		switch ($MedServiceType_SysNick) {
			case 'lab':
				//Для лаборатории нужен список всех оказываемых услуг, для включения в направление таких же из посещения
				$sql = "
					SELECT distinct
						ucms.UslugaComplex_id
					from
						v_UslugaComplexMedService ucms with(nolock) -- услуга на службе
						inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaComplex_id -- комплексная услуга (услуга МО или ГОСТ)
						inner join v_UslugaComplex uc11 with(nolock) on uc11.UslugaComplex_id = uc.UslugaComplex_2011id -- комплексная услуга ( ГОСТ)
					where
						ucms.MedService_id = :MedService_id
				";
				$uslugaIDs = $this->queryResult($sql , $data);
				if(!empty($uslugaIDs) && count($uslugaIDs) > 0){
					$IDs = array();
					foreach($uslugaIDs as $usl)
						$IDs[] = $usl['UslugaComplex_id'];
					$uslugaFilter = " AND EPLD.UslugaComplex_id IN (".implode(",", $IDs).") ";
				}
				break;
			case 'pzm':
				//Для пункта забора Нужно проверить на способы забора оказываемые пунктом (наличие услуг)
				$sql = "
					SELECT distinct
						UC11.UslugaComplex_Code
					from
						v_UslugaComplexMedService ucms with(nolock) -- услуга на службе
						inner join v_UslugaComplex uc with(nolock) on uc.UslugaComplex_id = ucms.UslugaCOmplex_id -- комплексная услуга (услуга МО или ГОСТ)
						inner join v_UslugaComplex uc11 with(nolock) on uc11.UslugaComplex_id = uc.UslugaCOmplex_2011id -- комплексная услуга ( ГОСТ)
					where
						ucms.MedService_id = :MedService_id
						AND uc11.UslugaComplex_Code in ('A11.05.001', 'A11.12.009', 'A11.16.005')
				";
				$uslugaIDs = $this->queryResult($sql , $data);
				if(!empty($uslugaIDs) && count($uslugaIDs) > 0){
					$IDs = array();
					foreach($uslugaIDs as $usl)
						$IDs[] = "'".$usl['UslugaComplex_Code']."'";
					$uslugaFilter = " AND exists (
						SELECT top 1
							st.SamplingType_Code
						FROM
							dbo.v_UslugaComplex uc with(nolock)
							inner join v_UslugaComplex uc11 with(nolock) on uc11.UslugaComplex_id = uc.UslugaComplex_2011id 
							left JOIN UslugaComplexAttribute ua with(nolock) ON ua.UslugaComplex_id = uc.UslugaComplex_id
							left JOIN UslugaComplexAttribute ua2 with(nolock) ON ua2.UslugaComplex_id = uc.UslugaComplex_2011id
							LEFT JOIN SamplingType st with(nolock) ON (st.SamplingType_id = ua.UslugaComplexAttribute_DBTableID OR st.SamplingType_id = ua2.UslugaComplexAttribute_DBTableID)
						WHERE
							uc.UslugaComplex_id  = EPLD.UslugaComplex_id
							AND (ua.UslugaComplexAttributeType_id = 129 OR ua2.UslugaComplexAttributeType_id = 129)
							AND st.SamplingType_Code IN (".implode(",", $IDs).")
					)";
				}
				break;
			default:
		}

		$params = array(
			'Evn_id' => $data['Evn_id'],
			'EvnPrescr_id' => $data['EvnPrescr_id'],
		);
		// Запрос на все услуги лабораторной диагн. в данном посещении
		$sql = "
			declare @curdate datetime = dbo.tzGetDate();
			
			select 
				EPLD.UslugaComplex_id,
				UC.UslugaComplex_Name,
				ucms.UslugaComplexMedService_id as UslugaComplexMedService_pid,
				CAST(etr.checked as varchar(max)) as checked,
				EP.MedService_id,
				EP.EvnPrescr_id
			from v_EvnPrescr EP with (nolock)
				inner join EvnPrescrLabDiag EPLD with (nolock) on EPLD.EvnPrescrLabDiag_id = EP.EvnPrescr_id
				left join v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = EPLD.UslugaComplex_id
				left JOIN v_UslugaComplexMedService ucms WITH (NOLOCK) on ucms.UslugaComplex_id = EPLD.UslugaComplex_id AND ucms.MedService_id = EP.MedService_id
					 AND ucms.UslugaComplexMedService_pid IS NULL
                     and ucms.UslugaComplexMedService_begDT <= @curdate
					and (ucms.UslugaComplexMedService_endDT is null or ucms.UslugaComplexMedService_endDT > @curdate)
					outer apply (
					Select (
						select
							ISNULL(CAST(UC.UslugaComplex_id as VARCHAR),'') + ',' as 'data()' 
						from v_UslugaComplexMedService ucmsTemp with (nolock)
						inner join v_UslugaComplex UC with (nolock) on ucmsTemp.UslugaComplex_id = UC.UslugaComplex_id
						cross apply(
							select top 1
								at_child.AnalyzerTest_SortCode,
								at_child.AnalyzerTest_id,
								ISNULL(at_child.AnalyzerTest_SysNick, uc.UslugaComplex_Name) as AnalyzerTest_SysNick
							from
								lis.v_AnalyzerTest at_child (nolock)
								inner join lis.v_AnalyzerTest at (nolock) on at.AnalyzerTest_id = at_child.AnalyzerTest_pid
								inner join lis.v_Analyzer a (nolock) on a.Analyzer_id = at.Analyzer_id
								left join v_UslugaComplex uc (nolock) on uc.UslugaComplex_id = at_child.UslugaComplex_id
							where
								at_child.UslugaComplexMedService_id = ucmsTemp.UslugaComplexMedService_id
								and at.UslugaComplexMedService_id = ucmsTemp.UslugaComplexMedService_pid
								and ISNULL(at_child.AnalyzerTest_IsNotActive, 1) = 1
								and ISNULL(at.AnalyzerTest_IsNotActive, 1) = 1
								and ISNULL(a.Analyzer_IsNotActive, 1) = 1
								and (at_child.AnalyzerTest_endDT >= dbo.tzGetDate() or at_child.AnalyzerTest_endDT is null)
								and (uc.UslugaComplex_endDT >= dbo.tzGetDate() or uc.UslugaComplex_endDT is null)
						) ATEST -- фильтрация услуг по активности тестов связанных с ними
						where ucmsTemp.UslugaComplexMedService_pid = ucms.UslugaComplexMedService_id
						order by ISNULL(ATEST.AnalyzerTest_SortCode, 999999999)
					for xml path(''), TYPE
					) as checked
				) etr
			where
				EP.EvnPrescr_pid  = :Evn_id
				and EP.EvnPrescr_id != :EvnPrescr_id
				and EP.PrescriptionType_id = 11
				and EP.PrescriptionStatusType_id != 3
				and not exists (
					Select top 1 epd.EvnDirection_id
					from v_EvnPrescrDirection epd with (nolock)
					--inner join v_EvnDirection_all ED with (nolock) on epd.EvnDirection_id = ED.EvnDirection_id
					where epd.EvnPrescr_id = EP.EvnPrescr_id
					--and  ISNULL(ED.EvnStatus_id, 16) not in (12,13)
				)
				{$uslugaFilter}
		";
		//echo getDebugSQL($sql, $params);die();
		$res = $this->queryResult($sql, $params);

		if (empty($res[0])){
			return false;
		}
		return $res;
	}

	/**
	 * Возврат листа ожидания в предыдущее состояние
	 */
	function getBackEvnQueue($data) {

		$queue_data = $this->getFirstRowFromQuery("
			select top 1
				EvnQueue_id,
				EvnDirection_id,
				Person_id,
				EvnQueue_DeclineCount 
			from v_EvnQueue queue (nolock)
			where queue.EvnQueue_id = :EvnQueue_id		 
		", array('EvnQueue_id' => $data['EvnQueue_id']));

		if (empty($queue_data['EvnQueue_id'])) {
			return array(
				array(
					'success'=>false,
					'Error_Msg'=>'Невозможно обновить Лист ожидания. Лист ожидания не найден.'
				)
			);
		}

		if (
			!isset($data['EvnQueue_DeclineCount'])
			&& $queue_data['EvnQueue_DeclineCount'] == null
		) {
			// для того чтобы пометить отменённого,
			// и чтобы обработчик не назначал больше эту бирку
			$data['EvnQueue_DeclineCount'] = 0;
		}

		$updateResult = $this->updateEvnQueueData(array(
			'EvnQueue_id' => $queue_data['EvnQueue_id'],
			'EvnQueue_DeclineCount' => $data['EvnQueue_DeclineCount'],
			// ожидает
			'EvnQueueStatus_id' => 1,
		));

		// одновременно изменяем статус направления
		$setDirectionStatus = $this->setEvnDirectionStatus(array(
			'EvnDirection_id' => $queue_data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnStatus_SysNick' => 'Queued',
			'EvnStatusCause_id' => 1,
			'EvnClass_id' => 27,
		));

		$this->sendNotify(array(
			'notify_type' => $data['EvnQueueAction'],
			'Person_id' => $queue_data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
		));
	}

	/**
	 * Обновление состояния листа ожадния
	 */
	function updateEvnQueueData($data) {

		$params = array(
			'EvnQueue_id' => $data['EvnQueue_id'],
			'EvnQueueStatus_id' => $data['EvnQueueStatus_id'],
		);

		$additionalData = "";

		if (isset($data['EvnQueue_DeclineCount'])) {

			$additionalData .= "
				EvnQueue_DeclineCount = :EvnQueue_DeclineCount,
			";

			$params['EvnQueue_DeclineCount'] = $data['EvnQueue_DeclineCount'];
		}

		if (!empty($data['queueFailure'])) {

			$additionalData .= "
				QueueFailCause_id = :QueueFailCause_id,
				pmUser_failID = :pmUser_failID,
				EvnQueue_failDT = :EvnQueue_failDT,
			";

			$params['QueueFailCause_id'] = !empty($data['QueueFailCause_id']) ? $data['QueueFailCause_id'] : null;
			$params['pmUser_failID'] = !empty($data['pmUser_failID']) ? $data['pmUser_failID'] : null;
			$params['EvnQueue_failDT'] = !empty($data['EvnQueue_failDT']) ? $data['EvnQueue_failDT'] : null;
		}

		$query = "
			declare
				@Err_Code int,
				@Err_Msg varchar(4000);

				set nocount on;

				begin try
					update EvnQueue with (rowlock)
					set 
						EvnQueueStatus_id = :EvnQueueStatus_id,
						{$additionalData}
						TimetableGraf_id = null,
						pmUser_recID = null,
						EvnQueue_recDT = null	
					where 
						EvnQueue_id = :EvnQueue_id
				end try

				begin catch
					set @Err_Code = error_number();
					set @Err_Msg = error_message();
				end catch

				set nocount off;
	
			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		return $result;
	}

	/**
	 * Возврат направления по листу ожидания в состояние "в очереди"
	 */
	function setEvnDirectionStatus($data) {

		$params = array(
			'EvnDirection_id' => $data['EvnDirection_id'],
			'pmUser_id' => $data['pmUser_id'],
			'EvnStatus_SysNick' => $data['EvnStatus_SysNick'],
			'EvnStatusCause_id' => $data['EvnStatusCause_id'],
			'EvnClass_id' => $data['EvnClass_id'],
		);

		// устанавливаем статус направления "в очереди"
		$result = $this->getFirstRowFromQuery("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_Evn_setStatus
				@Evn_id = :EvnDirection_id,
				@EvnStatus_SysNick = :EvnStatus_SysNick,
				@EvnClass_id = :EvnClass_id,
				@EvnStatusCause_id = :EvnStatusCause_id,
				@EvnStatusHistory_Cause = null,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode OUTPUT,
				@Error_Message = @ErrMessage OUTPUT
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $params);

		return $result;
	}

	/**
	 * Рекурсия
	 */
	function processQueueProfiles($list, $lpu_id, $profile_id, $grouping_profiles = NULL) {

		// по профилю
		if (isset($list[$lpu_id][$profile_id])) {

			$list[$lpu_id][$profile_id]['count'] += 1;

			// замерживаем дочерние профили, если этот профиль группировочный
			if (!empty($grouping_profiles) && isset($grouping_profiles[$profile_id])) {

				foreach ($grouping_profiles[$profile_id] as $grouping_profile_id) {
					$list = $this->processQueueProfiles($list, $lpu_id, $grouping_profile_id);
				}
			}

		} else {

			$list[$lpu_id][$profile_id]['count'] = 1;

			if (!empty($grouping_profiles) && isset($grouping_profiles[$profile_id])) {
				foreach ($grouping_profiles[$profile_id] as $grouping_profile_id) {
					$list = $this->processQueueProfiles($list, $lpu_id, $grouping_profile_id);
				}
			}
		}

		return $list;
	}

	/**
	 * Задание для крона, на автоматическое обслуживание очереди
	 */
	function queueManager($data) {

		$data['object'] = 'TimetableGraf';

		$this->load->helper('Reg_helper');
		$this->load->library('textlog',
			array(
				'file'=>'QueueManager_'.date('Y-m-d').'.log',
				'rewrite'=>false,
				'prefixStructure' => array('date'),
				'logging' => (!empty($data['logging']) ? $data['logging'] : false )
			)
		);

		$this->load->model("Options_model", "opmodel");
		$global_options = $this->opmodel->getOptionsGlobals($data);

		// максимальное время ожидания подтверждения бирки
		$max_accept_time = (!empty($global_options['globals']['queue_max_accept_time'])
			? $global_options['globals']['queue_max_accept_time']
			: 24);

		// для отладки
		if (isset($data['max_accept_time'])) {
			$max_accept_time = intval($data['max_accept_time']);
		}


		// диапазон дней расписания
		$record_day_count = (!empty($global_options['globals']['portal_record_day_count'])
			? $global_options['globals']['portal_record_day_count']
			: 14);


		$queue_max_cancel_count = (!empty($global_options['globals']['queue_max_cancel_count'])
			? $global_options['globals']['queue_max_cancel_count']
			: 3);

		$this->textlog->add("###### ЗАПУСК ЗАДАНИЯ АВТОМАТИЧЕСКОГО РАСПРЕДЕЛЕНИЯ БИРОК В ОЧЕРЕДИ ######");

		if (!empty($global_options['globals']['grant_individual_add_to_wait_list'])) {

			$this->textlog->add("Этап 1. Получаем ЛПУ, с включенным автораспределением и обработкой записанных в очередь.");
			$raw_data = $this->queryResult("
				select
					ds1.Lpu_id
				from v_DataStorage ds1 (nolock)
				inner join v_DataStorage ds2 (nolock) on (ds2.Lpu_id = ds1.Lpu_id and ds2.DataStorage_Name = 'allow_queue_auto' and ds2.DataStorage_Value = 1)
				where 
					  ds1.DataStorage_Name = 'allow_queue'
					  and ds1.DataStorage_Value = 1
			", array());

			if (empty($raw_data)) {
				$this->textlog->add("Задание завершено на Этапе 1: Список ЛПУ пуст.");
				return true;
			}

			if (!empty($data['Lpu_list'])) {
				$lpu_list = $data['Lpu_list'];
			} else {
				$lpu_list = implode(',', array_column($raw_data, 'Lpu_id'));
			}
			$this->textlog->add("Список ЛПУ для обработки очереди: " . $lpu_list);


			$this->textlog->add("Этап 2. Получаем список для очистки очереди.");

			// проверяем не превысил ли кто-то время ожидания подтверждения бирки
			// нужно для того, чтобы освободить занятую бирку, и отдать её другому
			$query = "
				select
					q.EvnQueue_id,
					ed.EvnDirection_id,
				   	q.pmUser_insID,
				   	q.TimetableGraf_id,
				  	q.Person_id,
				  	q.EvnQueue_DeclineCount,
				    ps.Person_IsDead,
					ps.Person_deadDT,
					msf.LeaveRecordType_id,
				   	rtrim(ps.Person_Surname)+' '+rtrim(ps.Person_Firname)+isnull(' '+rtrim(ps.Person_Secname), '') as Person_Fio
				from v_EvnQueue q (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id 
				left join v_PersonState ps (nolock) on ps.Person_id = q.Person_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
				outer apply(
					select max(ttgh.TimeTableGrafHist_insDT) as TimeTableGrafHist_insDT
					from v_TimeTableGrafHist ttgh (nolock)
					where (1=1)
						and ttgh.TimetableGraf_id = q.TimetableGraf_id
						and ttgh.TimeTableGrafAction_id = 2 				-- запись пациента
						and ttgh.EvnDirection_id = q.EvnDirection_id
				) as tth
				where (1=1)
					and q.Lpu_id in ({$lpu_list})
					and q.RecMethodType_id = 1 						-- портал
					and q.EvnQueueStatus_id = 2 					-- ожидает подтверждения
					and q.QueueFailCause_id is null
					and q.Person_id is not null
					and ed.EvnStatus_id = 17						-- записано
					and q.TimetableGraf_id is not null
					and DATEDIFF(hour, tth.TimeTableGrafHist_insDT, dbo.tzGetDate()) >= :max_accept_time
			";

			$rejected_persons = $this->queryResult($query, array('max_accept_time' => $max_accept_time));

			// если такие люди найдены, выкидываем их из очереди и освобождаем бирку
			if (!empty($rejected_persons)) {

				$this->textlog->add("Этап 2. Очистка очереди:");

				$this->load->model("Timetable_model");
				foreach ($rejected_persons as $key => $person) {

					// обнулим, а то мало ли что (были прецеденты)
					$data['EvnQueue_id'] = null;
					$data['Person_id'] = null;
					$data['dontCancelDirection'] = null;

					$params = array(
						'EvnQueue_id' => $person['EvnQueue_id'],
						'Person_id' => $person['Person_id'],
						'pmUser_insID' => $person['pmUser_insID'],
						'Person_Fio' => $person['Person_Fio'],
						'TimetableGraf_id' => $person['TimetableGraf_id']
					);

					$data = array_merge($data, $params);
					$can_reject = true;

					if (!empty($person['Person_deadDT']) || (!empty($person['Person_IsDead']) && $person['Person_IsDead'] == 2)) {
						$data['EvnStatusCause_id'] = 5;
						$data['DirFailType_id'] = 13;
						$data['QueueFailCause_id'] = 4;
					} elseif (!empty($person['LeaveRecordType_id'])) {
						$data['EvnStatusCause_id'] = 8;
						$data['DirFailType_id'] = 4;
						$data['QueueFailCause_id'] = 15;
					} else {

						// смотрим EvnQueue_DeclineCount (счетчик отказов\пропусков предложенной бирки)
						// если он равен предельно допустимому то снимаем с очереди
						if (
							!empty($person['EvnQueue_DeclineCount'])
							&& intval($person['EvnQueue_DeclineCount'])+1 >= intval($queue_max_cancel_count)
						) {
							$data['EvnStatusCause_id'] = 1; // отказ пациента
							$data['DirFailType_id'] = 5; // отказ пациента
							$data['QueueFailCause_id'] = 13;
						} else {
							// во всех остальных случаях снимаем с бирки, повышаем счетчик отказов
							$can_reject = false;
						}
					}

					// чистим очередь
					if ($can_reject) {

						$params['EvnQueueStatus_id'] = 4;
						$params['QueueFailCause_id'] = $data['QueueFailCause_id'];

						$params['pmUser_failID'] = $data['pmUser_id'];
						$params['EvnQueue_failDT'] = date('Y-m-d H:i:s');

						$updateEvnQueueResult = $this->updateEvnQueueData($params);

						$this->textlog->add(($key + 1) . '.'
							. "Person_id: " . $person['Person_id'] . ','
							. "EvnQueue_id: " . $person['EvnQueue_id'] . ','
							. "EvnStatusCause_id: " . $data['EvnStatusCause_id'] . ','
							. "DirFailType_id: " . $data['DirFailType_id'] . ','
							. "QueueFailCause_id: " . $data['QueueFailCause_id'] . ','
							. "TimetableGraf_id: " . $data['TimetableGraf_id']
						);

						// отменяем направление и освобождаем бирку
						$cancelResult = $this->Timetable_model->Clear($data);

						$this->sendNotify(array(
							'notify_type' => 'reject',
							'Person_id' => $person['Person_id'],
							'pmUser_id' => $person['pmUser_insID'],
							// причина исключения
							'QueueFailCause_id' => $data['QueueFailCause_id']
						));

					} else {
						// во всех остальных случаях снимаем с бирки, повышаем счетчик отказов
						$updateEvnQueueResult = $this->getBackEvnQueue(
							array(
								'EvnQueue_id' => $person['EvnQueue_id'],
								'Person_id' => $person['Person_id'],
								'pmUser_id' => $data['pmUser_insID'],
								'EvnQueueAction' => 'clearOnInactivity',
								'EvnQueue_DeclineCount' => intval($person['EvnQueue_DeclineCount']) + 1
							)
						);

						$data['dontCancelDirection'] = true;

						$cancelResult = $this->Timetable_model->Clear($data);
						$err = "";
						if (!empty($cancelResult['Error_msg'])) {
							$err = "Возникла ошибка".$cancelResult['Error_msg']."";
						}
						$this->textlog->add("Этап 2. Пациент ".$person['Person_id']." не подтвердил бирку ".$person['TimetableGraf_id'].". Лист ожидания в статусе Ожидает.".$err."");
					}
				}
			} else {
				$this->textlog->add("Этап 2. Cписок для очистки очереди пуст.");
			}

			$this->textlog->add("Этап 3. Получение очереди пациентов.");

			// основной запрос
			$query = "
				select
					q.EvnQueue_id,
				   	q.Lpu_id,
				   	lu.LpuBuilding_id,
					q.EvnQueue_insDT,
				   	q.pmUser_insID,
					q.EvnQueue_DeclineCount,
					q.LpuSectionProfile_did as LpuSectionProfile_id,
					q.Person_id,
					rtrim(ps.Person_Surname)+' '+rtrim(ps.Person_Firname)+isnull(' '+rtrim(ps.Person_Secname), '') as Person_Fio,
					ps.Person_IsDead,
					ps.Person_deadDT,
					ed.EvnDirection_id,
					msf.MedStaffFact_id,
					msf.LeaveRecordType_id
				from v_EvnQueue q (nolock)
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = q.LpuUnit_did 
				left join v_PersonState ps (nolock) on ps.Person_id = q.Person_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
				where (1=1)
					and q.Lpu_id in ({$lpu_list})
					and q.RecMethodType_id = 1 			-- портал
					and q.EvnQueueStatus_id = 1	 		-- в очереди
					and q.QueueFailCause_id is null
					and q.Person_id is not null
					and ed.EvnStatus_id = 10	 		-- в очереди
					and q.TimetableGraf_id is null
				order by q.EvnQueue_id
			";

			$queued_persons = $this->queryResult($query, array());
			//echo '<pre>',print_r(getDebugSQL($query, array())),'</pre>'; die();
			//echo '<pre>',print_r($queued_persons),'</pre>';

			if (empty($queued_persons)) {
				$this->textlog->add("Задание завершено на Этапе 3: Очередь пациентов пуста.");
				return true;
			}

			$this->load->model("EvnDirection_model");

			// список одобренных
			$approved_list = array();

			// список "на проверку", для тех кто отказался от бирки,
			// чтобы не назначить её повторно
			$declined_list = array();

			// список для "заказа" бирок
			$order_list = array(
				'MedStaffFact_id' => array(),
				'LpuSectionProfile_id' => array()
			);

			$this->textlog->add("Этап 4. Подготовка списков распределения:");
			$approved_patients = array();

			// найдем профиля с родительским профилем
			$grouping_profiles_query = $this->queryResult("
				select 
					LpuSectionProfile_id,
					LpuSectionProfile_mainid
				from v_LpuSectionProfile (nolock) 
				where LpuSectionProfile_mainid is not null
			", array());

			$grouping_profiles = array();
			foreach ($grouping_profiles_query as $profile) {
				$grouping_profiles[$profile['LpuSectionProfile_mainid']][] = $profile['LpuSectionProfile_id'];
			}

			// массив для нагребания больничек для объединения по профилям
			$profiles_order_list = array();

			foreach ($queued_persons as $person) {

				if (!empty($person['Person_deadDT']) || (!empty($person['Person_IsDead']) && $person['Person_IsDead'] == 2)) {

					// убираем умерших из очереди

					$params = array(
						'EvnQueue_id' => $person['EvnQueue_id'],
						'EvnQueueStatus_id' => 4,
						'QueueFailCause_id' => 4,
						'pmUser_failID' => $data['pmUser_id'],
						'EvnQueue_failDT' => date('Y-m-d H:i:s')
					);

					// чистим очередь
					$updateEvnQueueResult = $this->updateEvnQueueData($params);
					$this->textlog->add("Пациент: " . $person['Person_id'] . ' убран из списка распределения по причине смерти. Идентификатор в очереди: ' . $person['EvnQueue_id']);

					// отменяем направление
					$this->EvnDirection_model->cancelEvnDirection(
						array(
							'EvnDirection_id' => $person['EvnDirection_id'],
							'DirFailType_id' => 13,
							'EvnStatusCause_id' => 5,
							'EvnComment_Comment' => null,
							'pmUser_id' => $data['pmUser_id'],
							'Lpu_cid' => $person['Lpu_id']
						)
					);

					$this->sendNotify(array(
						'notify_type' => 'reject',
						'Person_id' => $person['Person_id'],
						'pmUser_id' => $data['pmUser_id'],
						// причина исключения
						'QueueFailCause_id' => $params['QueueFailCause_id']
					));

				} elseif (!empty($person['LeaveRecordType_id'])) {

					// убираем тех у кого уволился врач

					$params = array(
						'EvnQueue_id' => $person['EvnQueue_id'],
						'Person_id' => $person['Person_id'],
						'Person_Fio' => $person['Person_Fio'],
						'pmUser_insID' => $person['pmUser_insID'],
						'EvnQueueStatus_id' => 4,
						'QueueFailCause_id' => 15,
						'pmUser_failID' => $data['pmUser_id'],
						'EvnQueue_failDT' => date('Y-m-d H:i:s'),
						'EvnDirection_id' => $person['EvnDirection_id'],
						'DirFailType_id' => 4,
						'EvnStatusCause_id' => 8,
						'EvnComment_Comment' => null,
						'pmUser_id' => $data['pmUser_id'],
						'Lpu_cid' => $person['Lpu_id']
					);

					$data = array_merge($data, $params);

					// чистим очередь
					$updateEvnQueueResult = $this->updateEvnQueueData($params);

					$update = $this->db->query($query, $params);
					$this->textlog->add("Пациент: " . $person['Person_id'] . ' убран из списка распределения по причине смены рабочего места врача. Идентификатор в очереди: ' . $person['EvnQueue_id']);
					// отменяем направление
					$this->EvnDirection_model->cancelEvnDirection($data);

					$this->sendNotify(array(
						'notify_type' => 'reject',
						'Person_id' => $person['Person_id'],
						'pmUser_id' => $data['pmUser_id'],
						// причина исключения
						'QueueFailCause_id' => $params['QueueFailCause_id']
					));
				} else {

					// для всех остальных добавляем в одобренные
					$approved_list[] = $person;

					// массив для текст-лога
					if (!empty($data['logging']) && !isset($approved_patients[$person['Person_id']])) {
						$approved_patients[$person['Person_id']] = null;
					}

					// отдельно отмечаем тех кто отказался
					// те кто помечены EvnQueue_DeclineCount = 0, назначалась бирка, но её отменили
					if (
						isset($person['EvnQueue_DeclineCount'])
						&& $person['EvnQueue_DeclineCount'] !== null
					) {
						$declined_list[] = $person['EvnDirection_id'];
					}
					// сформируем заказ бирок
					if (!empty($person['MedStaffFact_id'])) {
						// по врачу
						$order_list['MedStaffFact_id'][] = $person['MedStaffFact_id'];
					} elseif (!empty($person['LpuSectionProfile_id'])) {
						$profiles_order_list = $this->processQueueProfiles($profiles_order_list, $person['Lpu_id'], $person['LpuSectionProfile_id'], $grouping_profiles);
					}
				}
			}

			// если никого в списке подтвержденных нет - выходим
			if (empty($approved_list)) {
				$this->textlog->add("Задание завершено на Этапе 4: Очередь пациентов пуста.");
				return true;
			} else {
				$this->textlog->add("Список пациентов для распределения: " . implode(',', array_keys($approved_patients)));
				$this->textlog->add("Список проверки повторных: " . implode(',', $declined_list));
				$this->textlog->add("Список заказа по врачам: " . implode(',', $order_list['MedStaffFact_id']));

				$this->textlog->add("Список заказа по профилю: ");
				foreach ($profiles_order_list as $lpu_id => $profiles_data) {
					$this->textlog->add("Профили больницы ".$lpu_id.": ");
					foreach ($profiles_data as $profile_id => $profile) {
						$this->textlog->add("Профиль ".$profile_id." имеет ".$profile['count']." заказов");
					}
				}
			}

			// результирующий массив
			$timetables = array();
			$this->textlog->add("Этап 5: Формирование заказа на бирки");

			$order_filter = "";
			if (empty($data['bypassRecordDelay'])) {
				$order_filter = " and datediff(mi, tt.TimeTableGraf_insDT, @Current_Date) > 30 ";
			}

			if (!empty($order_list['MedStaffFact_id'])) {

				$this->textlog->add("Этап 5: Получение бирок по врачам");

				// наберем бирки по заказам для врачей
				$msf_list = implode(',', array_unique($order_list['MedStaffFact_id']));
                $MinTimeQueue = $this->config->item('MinTimeQueue');
				$msf_timetables = $this->queryResult("
				
					declare
						@Current_Date datetime;
					set @Current_Date = dbo.tzGetDate();
				
					select
						tt.TimetableGraf_id,
						tt.MedStaffFact_id,
						msf.Lpu_id,
						msf.LpuBuilding_id,
						ls.LpuSectionProfile_id,
						tt.TimetableGraf_begTime,
						tt.TimetableGraf_Day
					from v_TimetableGraf_lite tt (nolock)
					left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = tt.MedStaffFact_id
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
					where (1=1)
						and tt.MedStaffFact_id in ({$msf_list})
						and msf.Lpu_id in ({$lpu_list})
						and isnull(msf.RecType_id, 6) not in (2,5,6,8)
						and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
						and ISNULL(msf.MedStaffFactCache_IsNotShown, 0) != 2
						and tt.TimeTableType_id in (1,11)
						and tt.Person_id is null
						and tt.EvnDirection_id is null
						and (
						  CAST(tt.TimetableGraf_begTime AS DATETIME) >= CAST(dateadd(hour,:MinTimeQueue,dbo.tzGetDate()) AS DATETIME)
						  and CAST(tt.TimetableGraf_begTime AS DATE) < CAST(dateadd(day,:record_day_count,dbo.tzGetDate()) AS DATE)
						)
						{$order_filter}
						
					order by
						tt.TimetableGraf_Day, 
						tt.TimetableGraf_begTime desc
				", array('record_day_count' => intval($record_day_count),
                         'MinTimeQueue' => $MinTimeQueue
                ));

				// перераспределим бирки
				if (!empty($msf_timetables)) {
					foreach ($msf_timetables as $tt) {

						$profile_key = $tt['LpuSectionProfile_id'];

						// смотрим, является ли профиль врача потомком общего профиля
						foreach ($grouping_profiles as $group_key => $childs_profiles) {
							if (in_array($tt['LpuSectionProfile_id'],$childs_profiles)) {
								// если является подменяем ключ профиля
								$profile_key = $group_key;
								break;
							}
						}

						$key = $tt['Lpu_id'] . '_' . $profile_key;

						// сгруппируем по составному ключу лпу_профиль
						if (!isset($timetables[$key])) {
							$timetables[$key] = array();
						}

						// группируем по ключу\врачу\дню
						$timetables[$key][$tt['MedStaffFact_id']][$tt['TimetableGraf_Day']][$tt['TimetableGraf_id']] = $tt;
					}
				}
			}

			if (!empty($profiles_order_list)) {

				$this->textlog->add("Этап 5: Получение бирок по профилю");

				$msf_filter = '';

				// не включаем бирки уже набранных врачей если они есть
				if (!empty($msf_list)) {
					$msf_filter = " and msf.MedStaffFact_id not in ({$msf_list}) ";
				}

				$MinTimeQueue = $this->config->item('MinTimeQueue');
				$lsp_timetables = array();

				// генерируем запрос на каждую больницу, на каждый профиль,
				// на определенное количество записей
				foreach ($profiles_order_list as $lpu_id => $lpu_profiles) {
					foreach ($lpu_profiles as $profile_id => $profiles) {

						// берем в десять раз больше чем нужно, на всякий случай
						$top_count = $profiles['count']*10;

						$query = "
							declare @Current_Date datetime;
							set @Current_Date = dbo.tzGetDate();
						
							select * 
							from (
								select top {$top_count}
									tt.TimetableGraf_id,
									tt.TimetableGraf_begTime,
									tt.TimetableGraf_Day,
									tt.MedStaffFact_id,
									msf.LpuSectionProfile_id,
									msf.LpuBuilding_id,
									msf.Lpu_id
								from v_TimetableGraf_lite tt (nolock)
								left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = tt.MedStaffFact_id
								left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
								left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
								where (1=1)
									and msf.LpuSectionProfile_id = :LpuSectionProfile_id
									and msf.Lpu_id = :Lpu_id
									{$msf_filter}
									and isnull(msf.RecType_id, 6) not in (2,5,6,8)
									and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
									and ISNULL(lsp.LpuSectionProfile_InetDontShow, 1) = 1
									and ISNULL(msf.MedStaffFactCache_IsNotShown, 0) != 2
									and tt.TimeTableType_id in (1,11)
									and tt.Person_id is null
									and tt.EvnDirection_id is null
									and (
									  CAST(tt.TimetableGraf_begTime AS DATETIME) >= CAST(dateadd(hour,2,dbo.tzGetDate()) AS DATETIME)
									  and CAST(tt.TimetableGraf_begTime AS DATE) < CAST(dateadd(day,10,dbo.tzGetDate()) AS DATE)
									)
								{$order_filter}		
								order by 
									tt.TimetableGraf_begTime
							) as mainSelect
							order by 
								mainSelect.TimetableGraf_Day desc
						";

						//echo '<pre>',print_r(getDebugSQL($query,
						//	array(
						//		'record_day_count' => intval($record_day_count),
						//		'MinTimeQueue' => $MinTimeQueue,
						//		'LpuSectionProfile_id' => $lpu_id,
						//		'Lpu_id' => $profile_id
						//	))),'</pre>';

						$tt_profile_result = $this->queryResult($query,
							array(
								'record_day_count' => intval($record_day_count),
								'MinTimeQueue' => $MinTimeQueue,
								'LpuSectionProfile_id' => $profile_id,
								'Lpu_id' => $lpu_id
							)
						);

						if (!empty($tt_profile_result)) {
							$lsp_timetables = array_merge($lsp_timetables, $tt_profile_result);
						}
					}
				}

				// перераспределим бирки
				if (!empty($lsp_timetables)) {

					$lsp_timetables = array_reverse($lsp_timetables, TRUE);
					foreach ($lsp_timetables as $tt) {

						$profile_key = $tt['LpuSectionProfile_id'];

						// смотрим, является ли профиль врача потомком общего профиля
						foreach ($grouping_profiles as $group_key => $childs_profiles) {
							if (in_array($tt['LpuSectionProfile_id'],$childs_profiles)) {
								// если является подменяем ключ профиля
								$profile_key = $group_key;
								break;
							}
						}

						$key = $tt['Lpu_id'] . '_' . $profile_key;

						if (!isset($timetables[$key])) {
							$timetables[$key] = array();
						}

						if (!isset($timetables[$key][$tt['MedStaffFact_id']][$tt['TimetableGraf_Day']][$tt['TimetableGraf_id']])) {
							$timetables[$key][$tt['MedStaffFact_id']][$tt['TimetableGraf_Day']][$tt['TimetableGraf_id']] = $tt;
						}
					}
				}
			}

			// если доступных бирок для записи нет - выходим
			if (empty($timetables)) {
				$this->textlog->add("Завершение задания на Этапе 5: Нет бирок доступных для записи");
				return true;
			}

			//echo '<pre>',print_r($timetables),'</pre>'; die();

			$this->textlog->add("Этап 5: Бирки для записи");

			//if (!empty($data['logging'])) {
			//	foreach ($timetables as $lpu_profile => &$profiles) {
			//		$this->textlog->add('Профиль '.$lpu_profile.':');
			//		foreach ($profiles as $doctor => $days) {
			//			foreach ($days as $day_id => $tt_list) {
			//				$this->textlog->add('Список бирок для врача ' . $doctor . ', на день '.$day_id.': ' . implode(',', array_keys($tt_list)));
			//			}
			//		}
			//	}
			//}

			$this->textlog->add("Этап 6: Обработаем бирки которые не нужно назначать повторно");

			// получим те бирки которые не нужно назначать (повторно)
			$declined_timetables = array();
			if (!empty($declined_list)) {

				$this->textlog->add("Этап 6: Получим бирки по списку направлений из истории бирок");

				$declined_list = implode(',', $declined_list);
				$declined_timetables = $this->queryResult("
					select
						tth.TimetableGraf_id,
					   	tth.Person_id
					from v_TimeTableGrafHist tth (nolock)
					where (1=1)
						and tth.EvnDirection_id in ({$declined_list})
						and tth.TimeTableType_id in (1,11)
					  	and tth.TimeTableGrafAction_id = 2
						and tth.Person_id is not null
				", array());

				$tmp_arr = array();
				if (!empty($declined_timetables)) {
					foreach ($declined_timetables as $tt) {
						$tmp_arr[$tt['Person_id']][] = $tt['TimetableGraf_id'];
					}

					$declined_timetables = $tmp_arr;
					foreach ($declined_timetables as $person => $tt) {
						$this->textlog->add("Этап 6: Для пациента " . $person . ' уже назначались бирки:' . implode(',', $tt));
					}

				} else {
					$this->textlog->add("Этап 6: Проверка бирок по направлениям вернула пустой результат");
				}
			} else {
				$this->textlog->add("Этап 6: Список для проверки повторных назначений бирок пуст");
			}

			$this->textlog->add("Этап 7: Запись пациента на бирку:");

			// прогоним запись для каждого пациента в очереди
			foreach ($approved_list as $person) {

				// обнуляем данные по бирке!
				$timetable = null;
				$this->textlog->add("Пациент: " . $person['Person_id']);

				// получим дни в которые у пациента уже есть запись
				$crossing_days = $this->queryResult("
						select top 100
							tt.TimeTableGraf_Day
						from v_TimetableGraf tt 
						where tt.Person_id = :Person_id
						and tt.TimetableGraf_begTime is not null
						and tt.TimeTableGraf_Day > :Day_id
						order by TimetableGraf_id desc
					", array(
						'Person_id' => $person['Person_id'],
						'Day_id' => TimeToDay(time())
					)
				);

				if (!empty($crossing_days)) {
					$crossing_days = array_column($crossing_days,'TimeTableGraf_Day');
				}

				// определим записан ли он к врачу или по профилю
				if (!empty($person['MedStaffFact_id']) && !empty($person['LpuSectionProfile_id'])) {

					$key = $person['Lpu_id'] . '_' .  $person['LpuSectionProfile_id'];
					$this->textlog->add("В очереди к врачу. Ищем бирки врача " . $person['MedStaffFact_id'] . ' по профилю ' . $key);

					// если в доступных для записи бирках, существует профиль и врач
					if (!empty($timetables[$key][$person['MedStaffFact_id']])) {

						// все дни с бирками этого врача
						$msf_tt_days = &$timetables[$key][$person['MedStaffFact_id']];

						// если у нас уже были назначенные бирки
						if (isset($declined_timetables[$person['Person_id']])) {

							$this->textlog->add("Пациенту ранее были предложены бирки этого врача");

							//echo '<pre>',print_r($msf_tt_days),'</pre>'; die();

							// перебираем дни с бирками
							foreach ($msf_tt_days as $day_id => &$day_timetables) {

								// если набор бирок пуст
								if (empty($day_timetables)) {
									unset($msf_tt_days[$day_id]);
									continue;
								}

								if (!empty($crossing_days) && in_array($day_id, $crossing_days)) {
									// переходим к следующему дню,
									// если у пациента уже есть запись на этот день
									continue;
								}

								// собираем ключики и ревертим
								$reverted_keys = array_reverse(array_keys($day_timetables));

								// иначе смотрим каждую бирку на этом дне
								foreach ($reverted_keys as $tt_id) {
									if (!in_array($tt_id, $declined_timetables[$person['Person_id']])) {

										// назначаем бирку
										$timetable = $day_timetables[$tt_id];
										$this->textlog->add("Пациенту " .$person['Person_id']. " назначена бирка " . $tt_id);

										// убираем эту бирку и списка
										unset($day_timetables[$tt_id]);
										break;
									} else {
										$this->textlog->add("Пациенту уже была предложена бирка " . $tt_id);
									}
								}

								// если во вложенном фориче нашли бирку, выходим из этого тоже
								if (!empty($timetable)) break;
							}
						} else {

							// проходим каждый день с набором бирок
							foreach ($msf_tt_days as $day_id => &$day_timetables) {

								// если набор бирок пуст
								if (empty($day_timetables)) {
									unset($msf_tt_days[$day_id]);
									continue;
								}

								if (!empty($crossing_days) && in_array($day_id, $crossing_days)) {
									// переходим к следующему дню,
									// если у пациента уже есть запись на этот день
									continue;
								}

								// если у чела нет предстоящих записей, то возьмем первую ближайщую
								$timetable = array_pop($day_timetables);
								break;
							}

							$this->textlog->add("Пациенту " .$person['Person_id']. " назначена бирка " .  $timetable['TimetableGraf_id']);
						}

						//echo '<pre>',print_r($timetableGraf_id),'</pre>'; die('msf');
						if (!empty($timetable)) {
							$applyParams = array(
								'EvnQueue_id' => $person['EvnQueue_id'],
								'EvnDirection_id' => $person['EvnDirection_id'],
								'Person_id' => $person['Person_id'],
								'Lpu_did' => $person['Lpu_id'],
								'TimetableGraf_id' => $timetable['TimetableGraf_id']
							);

							$this->textlog->add("Записываем ". $applyParams['Person_id'] ." пациента на бирку  " . $timetable['TimetableGraf_id'] . ', EvnQueue_id:' . $applyParams['EvnQueue_id'] . ', EnvDirection_id:' . $applyParams['EvnDirection_id']);

							$data = array_merge($data, $applyParams);
							$apply_data = $this->EvnDirection_model->applyEvnDirectionFromQueue($data);

							$processed_persons[$person['Person_id']][] = $timetable;

							// обнуляем бирку!
							$timetable = null;
						}
					} else {
						$this->textlog->add("Бирки врача не найдены");
					}

				} elseif (!empty($person['LpuSectionProfile_id'])) {

					$key = $person['Lpu_id'] . '_' . $person['LpuSectionProfile_id'];
					$this->textlog->add("В очереди по профилю. Ищем бирки по профилю " . $key);

					// если в доступных для записи бирках, существует искомый профиль
					if (!empty($timetables[$key])) {

						//echo '<pre>',print_r($timetables[$key]),'</pre>'; die();

						$this->textlog->add("Перебираем врачей по профилю");

						// перебираем врачей, пока не найдем того у которого еще остались бирки
						foreach ($timetables[$key] as $doctor => &$msf_tt_days) {

							$this->textlog->add("Текущий врач по профилю ". $doctor);

							// если дни с бирками по этому врачу еще остались
							if (!empty($msf_tt_days)) {

								// если у нас уже были ранее назначенные бирки
								if (isset($declined_timetables[$person['Person_id']])) {

									$this->textlog->add("Пациенту ранее были предложены бирки по этому профилю");

									// перебираем дни с бирками
									foreach ($msf_tt_days as $day_id => &$day_timetables) {

										// если набор бирок пуст
										if (empty($day_timetables)) {
											unset($msf_tt_days[$day_id]);
											continue;
										}

										if (!empty($crossing_days) && in_array($day_id, $crossing_days)) {
											// переходим к следующему дню,
											// если у пациента уже есть запись на этот день
											continue;
										}

										// собираем ключики и ревертим
										$reverted_keys = array_reverse(array_keys($day_timetables));

										// перебираем, пока не найдем ранее не назначавшуюся
										foreach ($reverted_keys as $tt_id) {

											//echo '<pre>',print_r($msf_timetables),'</pre>';
											//echo '<pre>',print_r(!in_array($timetable_id, $declined_timetables[$person['Person_id']])),'</pre>';
											if (!in_array($tt_id, $declined_timetables[$person['Person_id']])) {

												// назначаем бирку
												$timetable = $day_timetables[$tt_id];
												$this->textlog->add("Пациенту назначена бирка " . $tt_id);

												// убираем эту бирку и списка
												unset($day_timetables[$tt_id]);
												break;
											} else {
												$this->textlog->add("Пациенту уже была предложена бирка " . $tt_id);
											}
										}

										if (!empty($timetable)) break;
									}

								} else {

									// проходим каждый день с набором бирок
									foreach ($msf_tt_days as $day_id => &$day_timetables) {

										// если набор бирок пуст
										if (empty($day_timetables)) {
											unset($msf_tt_days[$day_id]);
											continue;
										}

										if (!empty($crossing_days) && in_array($day_id, $crossing_days)) {
											// переходим к следующему дню,
											// если у пациента уже есть запись на этот день
											continue;
										}

										// если у чела нет предстоящих записей, то возьмем первую ближайщую
										$timetable = array_pop($day_timetables);
										break;
									}

									$this->textlog->add("Пациенту назначена бирка " . $timetable['TimetableGraf_id']);
								}

								//echo '<pre>',print_r($timetableGraf_id),'</pre>'; die('profile');

								if (!empty($timetable)) {
									$applyParams = array(
										'EvnQueue_id' => $person['EvnQueue_id'],
										'EvnDirection_id' => $person['EvnDirection_id'],
										'Person_id' => $person['Person_id'],
										'Lpu_did' => $person['Lpu_id'],
										'TimetableGraf_id' => $timetable['TimetableGraf_id']
									);

									$this->textlog->add("Записываем ". $applyParams['Person_id'] ." пациента на бирку  " . $timetable['TimetableGraf_id'] . ', EvnQueue_id:' . $applyParams['EvnQueue_id'] . ', EvnDirection_id:' . $applyParams['EvnDirection_id']);
									$data = array_merge($data, $applyParams);
									$apply_data = $this->EvnDirection_model->applyEvnDirectionFromQueue($data);

									// обнуляем бирку!
									$timetable = null;

									// выходим из цикла
									break;
								}
							} else {
								$this->textlog->add("По этому профилю и этому врачу свободных бирок больше нет");
							}
						}
					} else {
						$this->textlog->add("Бирки по профилю не найдены");
					}
				}
			}
		} else {
			$this->textlog->add("Работа с очередью отключена в глобальных настройках системы!");
		}

		$this->textlog->add("====== ЗАДАНИЕ ЗАВЕРШЕНО ======");

		// выход
		return true;
	}

	/**
	 * Получаем данные для передачи в сообщение об предложении свободной бирки
	 */
	function getTimetableOfferData($data){

		$tt_data = $this->getFirstRowFromQuery("
				select top 1
					tt.TimetableGraf_begTime,
					tt.Person_id,
				   	rtrim(msf.Person_Surname)+' '+rtrim(msf.Person_Firname)+isnull(' '+rtrim(msf.Person_Secname), '') as Doctor_Fio,
				   	l.Lpu_Nick as Lpu_Nick,
					mso.MedSpecOms_Name as MedSpecOms_Name,
                    lu.LpuUnit_Name as LpuUnit_Name,
                    a.Address_Nick
				from v_TimetableGraf_lite tt with (nolock)
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = tt.MedStaffFact_id
				left join v_Lpu l (nolock) on l.Lpu_id = msf.Lpu_id
				left join v_MedSpecOms mso (nolock) on mso.MedSpecOms_id = msf.MedSpecOms_id
				left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_Address a (nolock) on a.Address_id = lu.Address_id
				where TimetableGraf_id = :TimetableGraf_id
			", array('TimetableGraf_id' => $data['TimetableGraf_id']));

		if (!empty($tt_data['TimetableGraf_begTime'])) {
			$tt_data['time'] = $tt_data['TimetableGraf_begTime']->format('d.m.Y H:i');
			unset($tt_data['TimetableGraf_begTime']);
		}

		if (!empty($tt_data['Doctor_Fio'])) {
			$tt_data['Doctor_Fio'] = mb_ucfirst(mb_strtolower($tt_data['Doctor_Fio']));
		}

		if (!empty($tt_data['Address_Nick'])) {
			$tt_data['Address_Nick'] = rtrim(rtrim($tt_data['Address_Nick']), ',');
		}

		return $tt_data;
	}

	/**
	 * Менеджер остылки сообщений при работе с Листами ожидания
	 */
	function sendNotify($data){

		$assign_params = array();

		if ($data['notify_type'] === 'timetableOffer' && !empty($data['TimetableGraf_id'])) {

			$tt_data = $this->getTimetableOfferData(array('TimetableGraf_id' => $data['TimetableGraf_id']));

			$data['Person_id'] = !empty($tt_data['Person_id']) ? $tt_data['Person_id'] : null;
			$assign_params = $tt_data;

		} else if ($data['notify_type'] === 'reject' && !empty($data['QueueFailCause_id'])) {

			$cause_data = $this->getFirstRowFromQuery("
				select top 1
					QueueFailCause_Name as cause
				from v_QueueFailCause (nolock)
				where QueueFailCause_id = :QueueFailCause_id
			",array('QueueFailCause_id' => $data['QueueFailCause_id']));

			$assign_params = $cause_data;
		}

		// не отправляем ничего, если не указана персона
		if (empty($data['Person_id'])) return true;

		$person_fio = $this->getFirstResultFromQuery("
			select top 1
				rtrim(Person_Surname)+' '+rtrim(Person_Firname)+isnull(' '+rtrim(Person_Secname), '') as Person_Fio
				from v_PersonState (nolock)
			where Person_id = :Person_id		 
		", array('Person_id' => $data['Person_id']));

		$person_fio = (!empty($person_fio) ? mb_ucfirst(mb_strtolower($person_fio)) : '');
		$assign_params = array_merge($assign_params, array('person_fio' => $person_fio));

		$this->load->model("Options_model", "opmodel");
		$max_accept_time_option = $this->opmodel->getOptionsGlobals($data, 'queue_max_accept_time');

		// максимальное время ожидания подтверждения бирки
		$max_accept_time = !empty($max_accept_time_option) ? $max_accept_time_option: 24;

		$notifications = array(
			'reject' => array(
				'title' => 'Исключение из очереди',
				'push' => ':person_fio исключен(а) из очереди к врачу по причине: :cause',
				'email' => 'Пациент :person_fio исключен(а) из очереди к врачу по причине: :cause'
			),
			'clear' => array(
				'title' => 'Отмена предложенной бирки',
				'push' => 'Бирка для :person_fio была отменена администратором медицинской организации. Вам будет предложена другая бирка.',
				'email' => 'Предложенная бирка для :person_fio была отменена администратором медицинской организации. Вам будет предложена другая бирка',
			),
			'clearOnInactivity' => array(
				'title' => 'Предложенная бирка не подтверждена',
				'push' => 'Предложенная бирка для :person_fio не была подтверждена. Вам будет предложена другая бирка.',
				'email' => 'Предложенная бирка для :person_fio не была подтверждена в течении '.$max_accept_time.' часов . Вам будет предложена другая бирка',
			),
			'timetableOffer' => array(
				'title' => 'Доступна свободная бирка',
				'push' => 'Доступна свободная бирка для записи к врачу на :time. Необходимо подтвердить или отказаться от бирки в личном кабинете.',
				'email' => "Для :person_fio появилось свободное время в расписании:"
					."\xA"."Медицинская организация: :Lpu_Nick"
					."\xA"."Подразделение: :LpuUnit_Name"
					."\xA"."Адрес: :Address_Nick"
					."\xA"."Специальность врача: :MedSpecOms_Name"
					."\xA"."ФИО врача: :Doctor_Fio"
					."\xA"."Дата и время приёма: :time"
					."\xA"."Вам нужно в течение ".$max_accept_time." часов подтвердить запись в личном кабинете регионального портала записи к врачу ".KVRACHU_URL."/user/"
					."\xA"."По истечении ".$max_accept_time." часов запись будет передана другому пациенту."
					."\xA"."Подробнее о работе с листом ожидания можно ознакомится в разделе справки ".KVRACHU_URL."/help/services/record"
			)
		);

		foreach ($notifications[$data['notify_type']] as &$text) {
			$text = $this->assign($text, $assign_params);
		}

		$email_message = $notifications[$data['notify_type']]['email'];
		$push_message = $notifications[$data['notify_type']]['push'];
		$title = $notifications[$data['notify_type']]['title'];

		$this->load->helper('Notify');
		$this->load->model('UserPortal_model');

		$portal_accounts = $this->UserPortal_model->getPushNotificationTokens(
			array(
				'pmUser_did' => !empty($data['pmUser_insID']) ? $data['pmUser_insID'] : null,
				'pmUser_id' => !empty($data['pmUser_id']) ? $data['pmUser_id'] : null,
				'Person_id' => $data['Person_id'],
				'showEmptyFCM' => true
			)
		);

		$push_notice_type = 2;

		if (!empty($portal_accounts)) {

			// обычно один... но мало ли чего бывает!
			foreach ($portal_accounts as $portal_account) {

				// сохраняем в оповещения портала
				$this->UserPortal_model->savePushNotificationHistory(
					array(
						'pmUser_did' => $portal_account['pmUser_did'],
						'message' => $push_message,
						'PushNoticeType_id' => $push_notice_type
					)
				);

				$email_text = "Уважаемый(ая) ".$portal_account['first_name']." ".$portal_account['second_name']."."
					."\xA"
					.$email_message."."
					."\xA"."С уважением, администрация регионального портала медицинских услуг.";

				// отправляем email пользователю портала
				sendNotifyEmail(
					array(
						'EMail' => $portal_account['email'],
						'title' => $title,
						'body' => $email_text,
						'wordwrap' => false //в шаблоне письма не красиво ставились автопереносы, ввёл параметр, чтобы отменить их
					)
				);

			}
		}

		// todo: пока не доработано в мобильном приложении
		if (true) {
			// отправляем пуш, если есть фцм токен
			$notifyResult = sendPushNotification(
				array(
					'Person_id' => $data['Person_id'], // персона которая заходит
					'pmUser_id' => $data['pmUser_id'],
					'message' => $push_message,
					'PushNoticeType_id' => $push_notice_type,
					'action' => 'call',
					// не сохраняем в оповещения портала, так как выше уже сохранили
					'disable_history' => true
				)
			);
		}
	}

	/**
	 * Запихиваем параметры в строку
	 */
	function assign($text = "", $params = array()) {

		if (!empty($params) && !empty($text)) {
			foreach ($params as $field => $value) {
				$text = str_replace(":".$field, $value, $text);
			}
		}

		return $text;
	}

	/**
	 * Проверить разрешена ли автоматическая обработка очереди
	 */
	function checkLpuQueueIsAllowed($data)
	{
		$this->load->model('Options_model');
		$queue_options = $this->Options_model->getQueueOptions($data);

		$allowed = false;

		if (
			!empty($queue_options['grant_individual_add_to_wait_list'])
			&& !empty($queue_options['allow_queue'])
			&& !empty($queue_options['allow_queue_auto'])
		) {
			$allowed = true;
		}

		return $allowed;
	}

	/**
	 * Проверить не заблокировано ли расписание
	 */
	function isTimetableBlockedByQueue($data) {

		$timetable_blocked = false;

		// только для Вологды #144563
		if (getRegionNick() === 'vologda') {

			if (!isset($this->lpu_queue[$data['Lpu_id']])) {
				$this->lpu_queue[$data['Lpu_id']] = $this->checkLpuProfileIsBlockedByQueue($data);
			}

			// если разрешена автообработка очереди, блокируем бирки для записи
			if ($this->lpu_queue[$data['Lpu_id']]['isAllowed']) {

				$timetable_blocked = true;

				// если по профилю не заблокированы, проверяем по доктору
				if (!$this->lpu_queue[$data['Lpu_id']]['profileIsBlocked']) {

					$queue_by_doc = $this->checkQueueByDoctor($data);
					if (empty($queue_by_doc)) $timetable_blocked = false;
				}
			}
		}

		return $timetable_blocked;
	}

	/**
	 * Проверить не заблокирован ли профиль больницы очередью
	 */
	function checkLpuProfileIsBlockedByQueue($data) {

		// проверяем больничку на доступность очереди
		$result = array(
			'isAllowed' => false,
			'profileIsBlocked' => false
		);

		$result['isAllowed'] = $this->checkLpuQueueIsAllowed($data);

		// здесь же определяем блокировку записи по профилю (один раз для всей больнички)
		if ($result['isAllowed']) {

			if (empty($data['LpuSectionProfile_id']) && !empty($data['MedStaffFact_id'])) {

				$params = array('MedStaffFact_id' => $data['MedStaffFact_id']);

				$data['LpuSectionProfile_id'] = $this->getFirstResultFromQuery("
					select top 1 msf.LpuSectionProfile_id
					from v_MedStaffFact msf (nolock)
					where (1=1) and msf.MedStaffFact_id = :MedStaffFact_id
				", $params);
			}

			if (!empty($data['LpuSectionProfile_id'])) {
				// проверяем не находится ли кто-либо в очереди по профилю
				$queue_by_profile = $this->checkQueueByProfile($data);
				if (!empty($queue_by_profile)) $result['profileIsBlocked'] = true;
			}
		}

		return $result;
	}

	/**
	 * Проверка существует ли очередь к данному врачу
	 */
	function checkQueueByDoctor($data) {

		$params = array('MedStaffFact_id' => $data['MedStaffFact_id']);

		$in_queue = $this->getFirstResultFromQuery("
			select
				count(q.EvnQueue_id) as count
			from v_EvnQueue q (nolock)
			left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id
			where (1=1)
				and ed.MedStaffFact_id = :MedStaffFact_id
				and q.QueueFailCause_id is null
				and q.RecMethodType_id = 1
				and q.EvnQueueStatus_id in (1)
		", $params);

		return $in_queue;
	}

	/**
	 * Проверка существует ли очередь по данному профилю
	 */
	function checkQueueByProfile($data) {

		$params = array(
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		$in_queue = $this->getFirstResultFromQuery("
			select
				count(q.EvnQueue_id) as count
			from v_EvnQueue q (nolock)
			left join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id
			where (1=1)
				and ed.MedStaffFact_id is null
				and q.Lpu_id = :Lpu_id 
			  	and q.LpuSectionProfile_did = :LpuSectionProfile_id
			  	and ed.MedStaffFact_id is null
				and q.QueueFailCause_id is null
				and q.RecMethodType_id = 1
				and q.EvnQueueStatus_id in (1)
		", $params);

		return $in_queue;
	}

	/**
	 * Загрузка журнала листов ожидания
	 */
	function loadWaitingListJournal($data) {

		$params = array(); $filter = "";

		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and q.Lpu_id = :Lpu_id ";
		}

		if (!empty($data['LpuSectionProfile_id'])) {
			$params['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			$filter .= " and q.LpuSectionProfile_did = :LpuSectionProfile_id ";
		}

		if (!empty($data['LpuBuilding_id'])) {
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$filter .= " and lu.LpuBuilding_id = :LpuBuilding_id ";
		}

		if (!empty($data['MedStaffFact_id'])) {
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
			$filter .= " and ed.MedStaffFact_id = :MedStaffFact_id ";
		}

		if (!empty($data['EvnQueueStatus_id'])) {
			$params['EvnQueueStatus_id'] = $data['EvnQueueStatus_id'];
			$filter .= " and q.EvnQueueStatus_id = :EvnQueueStatus_id ";
		}

		if (!empty($data['EvnQueue_insDT_period'])) {

			$ins_period = explode('—',$data['EvnQueue_insDT_period']);
			$params['insBegDt'] = DateTime::createFromFormat('d.m.Y', trim($ins_period[0]))->format('Y-m-d');

			if (!empty($ins_period[1])) $params['insEndDt'] = DateTime::createFromFormat('d.m.Y', trim($ins_period[1]))->format('Y-m-d');
			else $params['insEndDt'] = $params['insBegDt'];

			$filter .= '
				and cast(q.EvnQueue_insDT as date) >= :insBegDt and cast(q.EvnQueue_insDT as date) <= :insEndDt
			';
		}

		if (!empty($data['Person_SurName'])) {
			$params['Person_SurName'] = $data['Person_SurName'];
			$filter .= " and ps.Person_SurName like :Person_SurName + '%' ";
		}

		if (!empty($data['Person_FirName'])) {
			$params['Person_FirName'] = $data['Person_FirName'];
			$filter .= " and ps.Person_FirName like :Person_FirName + '%' ";
		}

		if (!empty($data['Person_SecName'])) {
			$params['Person_SecName'] = $data['Person_SecName'];
			$filter .= " and ps.Person_SecName like :Person_SecName + '%' ";
		}

		if (!empty($data['Person_BirthDay'])) {
			$params['Person_BirthDay'] = DateTime::createFromFormat('d.m.Y', trim($data['Person_BirthDay']))->format('Y-m-d');
			$filter .= " and cast(ps.Person_BirthDay as date) = :Person_BirthDay ";
		}

		if (!empty($data['Polis_EdNum'])) {
			$params['Person_EdNum'] = $data['Polis_EdNum'];
			$filter .= " and ps.Person_EdNum = :Person_EdNum ";
		}

		$response = $this->queryResult("
			select
				q.EvnQueue_id,
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Name,
				lpu.Lpu_id,
				lpu.Lpu_Nick,
			   	eqs.EvnQueueStatus_id,
				case when q.EvnQueueStatus_id = 3 and tth.TimetableGrafHist_insDT is not null
					then eqs.EvnQueueStatus_Name + ': ' + tth.TimetableGrafHist_insDT
					else 
						case when q.EvnQueueStatus_id = 2 and tth.TimeTableGraf_begTime is not null
							then eqs.EvnQueueStatus_Name + ': ' + tth.TimeTableGraf_begTime
							else 
								case when q.EvnQueueStatus_id = 4 and eqfc.QueueFailCause_Name is not null
									then  eqs.EvnQueueStatus_Name + ': ' + eqfc.QueueFailCause_Name
									else eqs.EvnQueueStatus_Name
								end
						end
				end as EvnQueueStatus_Name,
				q.EvnQueueStatus_id,
				case when q.EvnQueueStatus_id in (1,2,4) then
					q.EvnQueue_DeclineCount
				end as EvnQueue_DeclineCount,
			   	tt.TimetableGraf_id,
			   	q.Person_id,
			   	ed.MedStaffFact_id,
			   	convert(varchar(10), ps.Person_BirthDay, 104) as Person_BirthDay,
				convert(varchar(10), q.EvnQueue_insDT, 104) as EvnQueue_insDT,
			   	rtrim(ps.Person_Surname)+' '+rtrim(ps.Person_Firname)+' '+rtrim(ps.Person_Secname) as Person_FullName,
			    msf.Person_Surname+' '+rtrim(msf.Person_Firname)+' '+rtrim(msf.Person_Secname) as MedPersonal_Name,
			    case when (q.QueueFailCause_id is null and q.EvnQueueStatus_id in (1,2)) then
			    	case when ed.MedStaffFact_id is null
						then 
							case when inQueueCounterProfile.counter > 0 
								then inQueueCounterProfile.counter 
								else null 
							end
						else 
							case when (inQueueCounterMSF.counter + inQueueCounterProfile.counter) > 0 
								then (inQueueCounterMSF.counter + inQueueCounterProfile.counter) 
								else null 
							end 
					end
				end as EvnQueue_index
			from v_EvnQueue q (nolock)
			inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id
			left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = q.LpuSectionProfile_did
			left join v_Lpu lpu (nolock) on lpu.Lpu_id = q.Lpu_id
			left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = q.LpuUnit_did
			left join v_EvnQueueStatus eqs (nolock) on eqs.EvnQueueStatus_id = q.EvnQueueStatus_id
			left join v_QueueFailCause eqfc (nolock) on eqfc.QueueFailCause_id = q.QueueFailCause_id
			left join v_TimetableGraf_lite tt (nolock) on tt.TimetableGraf_id = q.TimetableGraf_id
			outer apply (
				select top 1
					convert(varchar(10), tth.TimetableGrafHist_insDT, 104) + ' ' + convert(varchar(5), tth.TimetableGrafHist_insDT, 108)  as TimetableGrafHist_insDT,
					convert(varchar(10), tth.TimeTableGraf_begTime, 104) + ' ' + convert(varchar(5), tth.TimetableGrafHist_insDT, 108)  as TimeTableGraf_begTime
				from v_TimetableGrafHist tth (nolock)
				where tth.TimetableGraf_id = tt.TimetableGraf_id
				and tth.Person_id = q.Person_id
				and tth.TimeTableGrafAction_id = 2
				order by TimetableGrafHist_id desc
			) tth
			left join v_PersonState ps (nolock) on ps.Person_id = q.Person_id
			outer apply (
				select count(q_inner.EvnQueue_id) as counter
					from v_EvnQueue q_inner (nolock)
					inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
					where q_inner.EvnQueueStatus_id in (1,2)
						and q_inner.EvnQueue_failDT is null
						and q_inner.EvnQueue_id < (q.EvnQueue_id + 1)
						and q_inner.RecMethodType_id in (1,2)
						and q_inner.LpuSectionProfile_did = q.LpuSectionProfile_did
						and ed_inner.MedStaffFact_id = ed.MedStaffFact_id
						and q_inner.Lpu_id = ed.Lpu_sid
						and ed.RecMethodType_id in (1,2)
			) inQueueCounterMSF
			outer apply (
				select count(q_inner.EvnQueue_id) as counter
					from v_EvnQueue q_inner (nolock)
					inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
					where q_inner.EvnQueueStatus_id in (1,2)
						and q_inner.EvnQueue_failDT is null
						and q_inner.EvnQueue_id < (q.EvnQueue_id + 1)
						and q_inner.RecMethodType_id in (1,2)
						and q_inner.LpuSectionProfile_did = q.LpuSectionProfile_did
						and ed_inner.MedStaffFact_id is null
						and q_inner.Lpu_id = ed.Lpu_sid
						and ed.RecMethodType_id in (1,2)
			) inQueueCounterProfile
			where (1=1)
				and q.RecMethodType_id in (1,2)
				and q.EvnQueueStatus_id is not null
				{$filter}
		", $params);

		$prognoz = array();
		$result = array();

		if (!empty($response)) {

			$this->load->helper('Reg');
			foreach ($response as $item) {

				// собираем инфу для прогноза
				// только для ЛО со стасусом в ожидании расчитываем прогноз
				if ($item['EvnQueueStatus_id'] == 1) {
					if (empty($item['MedStaffFact_id'])) {
						$prognoz['byProfile'][] = $item['EvnQueue_id'];
					} else {
						$prognoz['byMSF'][] = $item['EvnQueue_id'];
					}
				}

				$result[$item['EvnQueue_id']] = $item;
			}

			// получаем прогноз
			$prognozByProfile = array();
			if (!empty($prognoz['byProfile'])) {

				$qProfiles = implode(',', $prognoz['byProfile']);
				$prognozByProfile = $this->queryResult("
				select
					q.EvnQueue_id,
					forecastCounter.counter as forecastCounter,
					timetableCounter.counter as TimetableCounter
				from v_EvnQueue (nolock) q
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id
				outer apply (
					select count(q_inner.EvnQueue_id) as counter
					from v_EvnQueue q_inner (nolock)
					inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
					where q_inner.EvnQueueStatus_id = 1
						and q_inner.EvnQueue_id < (q.EvnQueue_id + 1)
						and q_inner.RecMethodType_id = 1
						and q_inner.LpuSectionProfile_did = q.LpuSectionProfile_did
						and ed_inner.MedStaffFact_id is null
						and q_inner.Lpu_id = q.Lpu_id
						and q_inner.QueueFailCause_id is null
				) forecastCounter
				outer apply (
					select
						sum(total.cnt) as counter
					from (
					select 
						tt.cnt
					from v_MedStaffFact msf (nolock)
					left join v_LpuSection ls (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
					left join v_Lpu l (nolock) on l.Lpu_id = ls.Lpu_id
					left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
					outer apply(
						select top 1
							count(tt.TimeTableGraf_id) as cnt
						from v_TimetableGraf_lite tt (nolock)
						where tt.MedStaffFact_id = msf.MedStaffFact_id
							and tt.TimeTableType_id in (1,11)
							and tt.TimeTableGraf_Day > :today
					) tt
					where 
						(1=1)
						and (msf.LpuSectionProfile_id in (
							select LpuSectionProfile_id
							from v_LpuSectionProfile
							where LpuSectionProfile_mainid = q.LpuSectionProfile_did
						) or msf.LpuSectionProfile_id = q.LpuSectionProfile_did)
						and msf.Lpu_id = q.Lpu_id
						and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
						and ISNULL(lsp.LpuSectionProfile_InetDontShow, 1) = 1
						and ISNULL(msf.MedStaffFactCache_IsNotShown, 0) != 2
						and isnull(msf.RecType_id, 6) not in (2,3,5,6,8)
						and ISNULL(l.Lpu_IsTest, 1) = 1
						and l.Lpu_id is not null
						and lu.LpuUnit_IsEnabled = 2
						and lu.LpuUnitType_id in (2, 5, 10, 12)
						and lsp.ProfileSpec_Name is not null
					) as total
				) timetableCounter
				where q.EvnQueue_id in ({$qProfiles})
				", array('today' => TimeToDay(time())));
			}

			$prognozByMSF = array();
			if (!empty($prognoz['byMSF'])) {

				$qMSF = implode(',', $prognoz['byMSF']);
				$prognozByMSF = $this->queryResult("
				select
					q.EvnQueue_id,
					(forecastCounterMSF.counter + forecastCounterProfile.counter) as forecastCounter,
					timetableCounter.counter as TimetableCounter
				from v_EvnQueue (nolock) q
				inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = q.EvnDirection_id
				outer apply (
					select count(q_inner.EvnQueue_id) as counter
					from v_EvnQueue q_inner (nolock)
					inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
					where q_inner.EvnQueueStatus_id = 1
						and q_inner.EvnQueue_id < (q.EvnQueue_id + 1)
						and q_inner.RecMethodType_id = 1
						and q_inner.LpuSectionProfile_did = q.LpuSectionProfile_did
						and ed_inner.MedStaffFact_id = ed.MedStaffFact_id
						and q_inner.Lpu_id = q.Lpu_id
						and q_inner.QueueFailCause_id is null
				) forecastCounterMSF
				outer apply (
					select count(q_inner.EvnQueue_id) as counter
					from v_EvnQueue q_inner (nolock)
					inner join v_EvnDirection_all ed_inner (nolock) on ed_inner.EvnDirection_id = q_inner.EvnDirection_id
					where q_inner.EvnQueueStatus_id = 1
						and q_inner.EvnQueue_id < (q.EvnQueue_id + 1)
						and q_inner.RecMethodType_id = 1
						and q_inner.LpuSectionProfile_did = q.LpuSectionProfile_did
						and ed_inner.MedStaffFact_id is null
						and q_inner.Lpu_id = q.Lpu_id
						and q_inner.QueueFailCause_id is null
				) forecastCounterProfile
				outer apply (
					select
						count(tt.TimeTableGraf_id) as counter 
					from v_TimeTableGraf_lite tt (nolock) 
					where (1=1)
					and tt.MedStaffFact_id  = ed.MedStaffFact_id
					and tt.TimeTableType_id in (1,11)
					and tt.TimeTableGraf_Day = (
						select top 1
						tt2.TimeTableGraf_Day
						from v_TimeTableGraf_lite tt2 (nolock)
				 		where (1=1)
							and tt2.TimeTableType_id in (1,11)
							and tt2.MedStaffFact_id  = ed.MedStaffFact_id
							and tt2.TimeTableGraf_Day > :today
						order by tt2.TimeTableGraf_Day
					)
				) timetableCounter
				where q.EvnQueue_id in ({$qMSF})
			", array('today' => TimeToDay(time())));
			}

			// объединяем прогнозы
			$prognoz = array_merge($prognozByProfile, $prognozByMSF);

			if (!empty($prognoz)) {
				foreach ($prognoz as $prognozData) {
					// для ЛО со стасусом в ожидании расчитываем прогноз
					if (isset($result[$prognozData['EvnQueue_id']])) {
						$result[$prognozData['EvnQueue_id']]['RecordPrognoz'] = $this->calculateRecordPrognoz($prognozData);
					}
				}
			}
		}

		return $result;
	}

	function calculateRecordPrognoz($data) {

		$forecastMessage = "";
		if (empty($data['TimetableCounter'])) {

			$forecastMessage = "неизвестно";

		} else {

			$inQueueCounter = intval($data['forecastCounter']);
			$ttCounter = intval($data['TimetableCounter']);

			if ($inQueueCounter < $ttCounter) {
				$forecastDays = 1;
			} else {

				$division = ceil($inQueueCounter / $ttCounter);

				if ($division <= 14) {
					$forecastDays = $division;
				} else {
					$forecastMessage = "более 2 недель";
				}
			}
		}

		if (!empty($forecastDays)) {
			if ($forecastDays == 1) {
				$forecastMessage = "день";
			} else if ($forecastDays > 1 && $forecastDays < 5) {
				$forecastMessage = "дня";
			} else {
				$forecastMessage = "дней";
			}

			$forecastMessage  = $forecastDays.' '.$forecastMessage;
		}

		return $forecastMessage;
	}

	/**
	 * Создаем направление и создаем заявку на запись, ХП
	 */
	function saveRecordRequest($data) {

		$msf_data = $this->getFirstRowFromQuery("
			select top 1
				msf.MedStaffFact_id as \"MedStaffFact_id\",
				msf.Lpu_id as \"Lpu_id\",
				msf.MedPersonal_id as \"MedPersonal_id\",
				msf.LpuUnit_id as \"LpuUnit_id\",
				msf.LpuSection_id as \"LpuSection_id\",
				msf.LpuSectionProfile_id as \"LpuSectionProfile_id\"
			from v_MedStaffFact msf (nolock)
			where msf.MedStaffFact_id = :MedStaffFact_id
		", $data);;

		if (!empty($msf_data['Error_Msg'])) {
			return array('Error_Msg' => $msf_data['Error_Msg']);
		}

		if (empty($msf_data)) {
			return array('Error_Msg' => 'Не удалось получить информацию о враче');
		}

		// получаем PersonEvn_id и Server_id
		$periodic = $this->getFirstRowFromQuery("
				select top 1
					PersonEvn_id as \"PersonEvn_id\",
					Server_id as \"Server_id\"
				from v_PersonState (nolock)
				where Person_id = :Person_id
			", array('Person_id' => $data['Person_id'])
		);

		if (empty($periodic['PersonEvn_id']) && empty($periodic['Server_id'])) {
			return array('Error_Msg' => 'Не удалось определить периодику пациента');
		}

		// генерим номер EvnDirection_Num
		$EvnDirection_Num = $this->getFirstResultFromQuery("
			declare @EvnDirection_Num bigint;

			exec xp_GenpmID
				@ObjectName = 'EvnDirection',
				@Lpu_id = :Lpu_id,
				@ObjectID = @EvnDirection_Num output;

			select @EvnDirection_Num as EvnDirection_Num;
		", array('Lpu_id' => $msf_data['Lpu_id'])
		);

		if (empty($EvnDirection_Num)) {
			return array('Error_Msg' => 'Не удалось сформировать номер направления');
		}

		$params = array(
			'MedStaffFact_id' => $msf_data['MedStaffFact_id'],
			'Lpu_id' => $msf_data['Lpu_id'],
			'Person_id' => $data['Person_id'],
			'Lpu_did' => $msf_data['Lpu_id'],
			'Lpu_sid' => $msf_data['Lpu_id'],
			'LpuUnit_did' => $msf_data['LpuUnit_id'],
			'Server_id' => $periodic['Server_id'],
			'PersonEvn_id' => $periodic['PersonEvn_id'],
			'EvnDirection_Num' => $EvnDirection_Num,
			'RecMethodType_id' => !empty($data['RecMethodType_id']) ? $data['RecMethodType_id'] : 1,
			'EvnDirection_setDT' => date('Y-m-d H:i:s'),
			'pmUser_id' => $data['User_id'],
			'MedPersonal_did' => $msf_data['MedPersonal_id'],
			'LpuSection_did' => $msf_data['LpuSection_id'],
			'LpuSectionProfile_id' => $msf_data['LpuSectionProfile_id'],
			'EvnDirection_Descr' => !empty($data['EvnDirection_Descr']) ? $data['EvnDirection_Descr'] : null
			// статично в запросе
			//'DirType_id' => 16, // на прием поликлинический
			//'EvnDirection_IsAuto' => 2,
		);

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirection_insToQueue
				@EvnDirection_id = @Res output,
				@Lpu_id = :Lpu_id,
				@Lpu_did = :Lpu_did,
				@Lpu_sid = :Lpu_sid,
				@LpuSection_did = :LpuSection_did,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuUnit_did = :LpuUnit_did,
				@Server_id = :Server_id,
				@PersonEvn_id = :PersonEvn_id,
				@pmUser_id = :pmUser_id,
				@EvnDirection_Num = :EvnDirection_Num,
				@EvnDirection_setDT = :EvnDirection_setDT,
				@RecMethodType_id = :RecMethodType_id,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_did = :MedPersonal_did,
				@EvnDirection_Descr = :EvnDirection_Descr,
				@DirType_id = 16,
				@EvnDirection_IsAuto = 2,
				@EvnDirection_IsCito = 1,
				@PayType_id = 1,
				@EvnStatus_id = 1,
				@EvnDirection_IsNeedOper = 1,
				@EvnDirection_IsReceive = 1,
				-- признак заявки
				@EvnQueue_IsRecRequest = 2,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
			select @Res as EvnDirection_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->getFirstRowFromQuery($query, $params);
		return $result;
	}

	/**
	 * Отменяем заявку
	 */
	function cancelRecordRequest($data)
	{
		$this->beginTransaction();

		// отказ пациента
		$data['EvnStatusCause_id'] = 1;

		$data['DirFailType_id'] = 5;
		$data['QueueFailCause_id'] = 8;

		$directionData = $this->getFirstRowFromQuery("
			select top 1 
				ed.EvnDirection_id as \"EvnDirection_id\",
				ed.pmUser_insID as \"pmUser_insID\",
				es.EvnStatus_SysNick as \"EvnStatus_SysNick\",
				eqr.EvnQueue_id as \"EvnQueue_id\",
				tt.TimetableGraf_id as \"TimetableGraf_id\",
				eqr.pmUser_insID as \"pmUser_insID\"
			from v_EvnDirection_all ed (nolock)
			left join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
			left join v_EvnQueue_RecRequest (nolock) eqr on eqr.EvnDirection_id = ed.EvnDirection_id
			outer (
				select top 1
					tt.TimetableGraf_id
				from v_TimetableGraf_lite tt (nolock)
				where (1=1)
					and tt.TimetableGraf_id = eqr.TimetableGraf_id
					and tt.Person_id = eqr.Person_id
			) as tt
			where eqr.EvnQueue_id = :EvnQueue_id
		", $data);

		if (empty($directionData['pmUser_insID']) || (!empty($directionData['pmUser_insID'])
				&& $directionData['pmUser_insID'] != $data['User_id'])
		) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Нельзя отменить заявку созданную не из вашей картотеки');
		}

		if (empty($directionData['EvnDirection_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Ошибка получения данных по направлению');
		}

		if (in_array($directionData['EvnStatus_SysNick'], array('Declined', 'Canceled'))) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Направление уже отменено');
		}

		if (empty($directionData['EvnQueue_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Направление не связано с заявкой. Отменить заявку невозможно');
		}

		// если пациент уже записан на бирку нужно ее отменить
		if (!empty($directionData['TimetableGraf_id'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => 'Пациенту уже назначено время приема, отменить заявку нельзя');
		}

		// в начале отменим направление
		$params =  array(
			'EvnDirection_id' => $directionData['EvnDirection_id'],
			'DirFailType_id' => $data['DirFailType_id'],
			'EvnComment_Comment' => null,
			'EvnStatusCause_id' => $data['EvnStatusCause_id'],
			'pmUser_id' => $data['User_id']
		);

		$declineResult = $this->getFirstRowFromQuery("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnDirection_cancel
				@EvnDirection_id = :EvnDirection_id,
				@DirFailType_id = :DirFailType_id,
				@EvnComment_Comment = :EvnComment_Comment,
				@EvnStatusCause_id = :EvnStatusCause_id,
				@pmUser_id = :pmUser_id,
				@Lpu_cid = null,
				@MedStaffFact_fid = null,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $params);

		if (!empty($declineResult['Error_Msg'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $declineResult['Error_Msg']);
		}

		// затем отменим заявку
		$params = array(
			'EvnQueue_id' => $directionData['EvnQueue_id'],
			'QueueFailCause_id' => $data['QueueFailCause_id'],
			'EvnStatusCause_id' => $data['EvnStatusCause_id'],
			'EvnComment_Comment' => null,
			'cancelType' => 'cancel',
			'pmUser_id' => $data['User_id']
		);

		$queueCancel = $this->getFirstRowFromQuery("
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_EvnQueue_cancel
				@EvnQueue_id = :EvnQueue_id,
				@QueueFailCause_id = :QueueFailCause_id,
				@EvnStatusCause_id = :EvnStatusCause_id,
				@EvnComment_Comment = :EvnComment_Comment,
				@cancelType = :cancelType,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		", $params);

		if (!empty($queueCancel['Error_Msg'])) {
			$this->rollbackTransaction();
			return array('Error_Msg' => $queueCancel['Error_Msg']);
		}

		$this->commitTransaction();
		return array('success' => true, 'EnvDirection_id' => $directionData['EvnDirection_id']);
	}

	/**
	 * Получение списка заявок к врачу
	 */
	function getPersonRecRequest($data)
	{
		$filter = ""; $params = array();

		if (empty($data['Person_id']) && empty($data['person_list'])) {
			return array('Error_Msg' => 'Не указан Person_id или person_list');
		}

		if (!empty($data['Person_id'])) {
			$filter .= "
				and eqr.Person_id = :Person_id
			";

			$params['Person_id'] = $data['Person_id'];
		}

		if (!empty($data['person_list'])) {

			$values = explode(',',$data['person_list']);
			if (is_array($values)) {
				foreach ($values as $value) {
					$num = intval(trim($value));
					if (!is_numeric($num)) {
						return array('Error_Msg' => 'Неверный формат значений person_list');
						break;
					}
				}
			} else {
				return array('Error_Msg' => 'Не удалось преобразовать значения person_list');
			}

			$filter .= "
				and eqr.Person_id in ({$data['person_list']})
			";
		}

		if (!empty($data['declinedRequests'])) {
			$filter .= "
				and ed.EvnStatus_id in (12,13)
			";
		} else {
			$filter .= "
				and ed.EvnStatus_id in (10,51)
				and tt.TimetableGraf_begTime is null 
			";
		}

		$query = "
			select
				'RecRequest' as viewGroup,
				eqr.Person_id,
				eqr.EvnQueue_id,
				eqr.EvnQueue_insDT as sortField,
				case when ed.EvnStatus_id = 51
					then 10
					else ed.EvnStatus_id
				end as EvnStatus_id,
				es.EvnStatus_Name,
				esc.EvnStatusCause_id,
				esc.EvnStatusCause_Name,
				esc.EvnStatusHistory_Cause,
				null as RequestStatus_Name,
				eqr.QueueFailCause_id,
				tt.TimetableGraf_id,
				case when tt.TimetableGraf_begTime is null
					then tth.TimetableGraf_begTime
					else CONVERT(varchar,tt.TimetableGraf_begTime,104) + ' ' + CONVERT(varchar(5),tt.TimetableGraf_begTime,108)
				end as TimetableGraf_begTime,
				msf.MedStaffFact_id,
				rtrim(msf.Person_Surname) as MedPersonal_Surname,
				rtrim(msf.Person_Firname) as MedPersonal_Firname,
				rtrim(msf.Person_Secname) as MedPersonal_Secname,
				lsp.LpuSectionProfile_id,
			   	ed.EvnDirection_Descr,
			   	lsp.ProfileSpec_Name,
				lpu.Lpu_id,
				lpu.Lpu_Nick,
				lu.LpuUnit_id,
				lu.LpuUnit_Name,
				rtrim(str.KLStreet_Name)+' '+rtrim(a.Address_House) as LpuUnit_Address,
				msf.MedStaffFactCache_CostRec,
				'pm_main' as source_system
			from v_EvnQueue_RecRequest eqr (nolock)
			inner join v_EvnDirection_all ed (nolock) on ed.EvnDirection_id = eqr.EvnDirection_id
			left join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
			left join v_PersonState ps (nolock) on ps.Person_id = eqr.Person_id
			left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
			left join v_LpuSectionProfile lsp (nolock) on lsp.LpuSectionProfile_id = msf.LpuSectionProfile_id
			left join v_TimetableGraf_lite tt (nolock) on tt.TimetableGraf_id = eqr.TimetableGraf_id
			left join v_LpuUnit lu (nolock) on lu.LpuUnit_id = eqr.LpuUnit_did
			left join v_Lpu lpu (nolock) on lpu.Lpu_id = lu.Lpu_id
			left join v_Address a (nolock) on a.Address_id = lu.Address_id
			left join v_KLStreet str (nolock) on str.KLStreet_id = a.KLStreet_id
			outer apply (
				select top 1
					CONVERT(varchar,tth.TimetableGraf_begTime,104) + ' ' + CONVERT(varchar(5),tth.TimetableGraf_begTime,108) as TimetableGraf_begTime,
					tth.TimetableGraf_id
				from v_TimetableGrafHist tth (nolock)
				where tth.EvnDirection_id = eqr.EvnDirection_id
				order by tth.TimetableGrafHist_insDT desc
			) as tth
			outer apply (
				select top 1
					esh.EvnStatusCause_id,
					esc.EvnStatusCause_Name,
					esh.EvnStatusHistory_Cause
				from v_EvnStatusHistory esh (nolock)
				left join v_EvnStatusCause esc (nolock) on esc.EvnStatusCause_id = esh.EvnStatusCause_id
				where esh.Evn_id = ed.EvnDirection_id
				order by esh.EvnStatusHistory_insDT desc
			) as esc
			where (1=1)
				and eqr.RecMethodType_id in (1,2,3,14,15)					-- портал
				{$filter}
			order by TimetableGraf_begTime, eqr.EvnStatus_id
		";

		$result = $this->queryResult($query, array('Person_id' => $data['Person_id']));

		if (!empty($result)) {

			$this->load->model('RegPrivate_model');

			foreach ($result as &$item) {
				$item['RequestStatus_Name'] = $this->RegPrivate_model->transformStatusName($item);
				if ($item['EvnStatus_id'] == 10) {
					$item['Request_Comment'] = 'Для уточнения даты и времени приема через 5 минут с вами свяжется администратор клиники.';
				} else {
					$cause_comment = !empty($item['EvnStatusHistory_Cause']) ? '. '.$item['EvnStatusHistory_Cause'] : '';
					$item['Request_Comment'] = 'Причина отмены: '.$item['EvnStatusCause_Name'].$cause_comment;
				}
			}
		}
		
		return $result;
	}
}
?>
