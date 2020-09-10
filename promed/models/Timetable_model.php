<?php
/**
* Timetable_model - модель с базовыми методами для работы с расписанием
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Reg
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      19.03.2012
*/

class Timetable_model extends swModel {

	/**
	 * Сокращенные дни недели
	 */
	var $arShortWeekDayName = array(
			0 => "ВС",
			1 => "ПН",
			2 => "ВТ",
			3 => "СР",
			4 => "ЧТ",
			5 => "ПТ",
			6 => "СБ",
		);
	
	/**
	 * Кэш данных по бирке поликлиники
	 */
	protected $TTGData = NULL;
	
	/**
	 * Кэш данных по бирке стационара
	 */
	protected $TTSData = NULL;
	
	/**
	 * Кэш данных по бирке службы
	 */
	protected $TTMSData = NULL;

	/**
	 * Кэш данных по бирке службы
	 */
	protected $TTMSOData = NULL;
	/**
	 * Кэш данных по бирке службы
	 */
	protected $TTRData = NULL;
	/**
	 * Проверка, что бирка существует и занята
	 * проверяет любой тип бирки, в зависимости от пришедших данных
	 * @param $data
	 * @param $checkOneInGroup boolean флаг проверки при смене типа бирки (запись хотя бы одного)
	 * @return array|bool
	 */
	function checkTimetableOccupied($data, $checkOneInGroup = false) {
		//print_r($data);exit();
		$tt_data = $this->getRecordData($data);
		
		if (!isset($tt_data[$data['object'].'_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}

		// При смене типа бирки, если хотя бы один пациент на нее записан, замена типа не производится
		if($checkOneInGroup && $data['object']=='TimetableGraf'&&!empty($tt_data['TimetableType_id'])&&$tt_data['TimetableType_id']==14&&!empty($tt_data['TimeTableGraf_countRec']))
			return true;

		if (
			(!isset($tt_data['Person_id'])&&$data['object']!='TimetableMedServiceOrg'&&empty($data['TimetableGrafRecList_id']))
			||($data['object']=='TimetableMedServiceOrg'&&!isset($tt_data['Org_id']))
			||($data['object']=='TimetableGraf'&&!empty($tt_data['TimetableType_id'])&&$tt_data['TimetableType_id']==14&&empty($tt_data['TimeTableGraf_countRec']))
		) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже свободна.'
			);
		}
		/*if ( !empty($tt_data['Slot_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Удаление бирки не возможно, данные используются в федеральной электронной регистратуре.'
			);
		}*/
		if ( !empty($tt_data['EvnVizit_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Освобождение бирки невозможно, поскольку прием уже осуществлен.'
			);
		}
		if (
			(empty($data['cancelType']) || (!empty($data['cancelType']) && $data['cancelType'] != 'cancel'))
			&&$data['object']=='TimetableGraf'&&!empty($tt_data['TimetableType_id'])
			&&$tt_data['TimetableType_id']==14&&!empty($tt_data['TimeTableGraf_countRec'])
		) {
			return array(
				'success' => false,
				'Error_Msg' => 'На выбранную Вами бирку уже записан(ы) пациенты. Смена статуса невозможна.'
			);
		}
		return true;
	}
	
	/**
	 * Проверка, что все из переданного массива бирок существуют и свободны
	 * проверяет любой тип бирки, в зависимости от пришедших данных
	 */
	function checkTimetablesFree($data) {

		// Название объекта
		$prefix = $data['object'];
		
		if (!isset($data[$data['object'].'Group']) || count($data[$prefix.'Group']) == 0) {
			return array(
				'success' => false,
				'Error_Msg' => 'Не задано ни одной бирки'
			);
		}
		
		// Если задана группа бирок, то проверяем всю группу
		$sql = "
			SELECT count(*) as cnt, count(Person_id) as recorded
			FROM {$prefix}
			WHERE
				{$prefix}_id in (".implode(', ', $data[$prefix.'Group']).")
		";
		$res = $this->db->query(
			$sql
		);
		
		if ( is_object($res) )
			$res = $res->result('array');
		if (!isset($res[0]) || $res[0]['cnt'] != count($data[$prefix.'Group']) ) {
			// количество вернувшихся бирок не равно количеству проверяемых, значит одна из бирок не существует
			return array(
				'success' => false,
				'Error_Msg' => 'Одной из переданных бирок не существует'
			);
		}
		if ( isset($res[0]['recorded']) && $res[0]['recorded'] > 0 ) {
			// среди записанных бирок есть хоть одна занятая
			return array(
				'success' => false,
				'Error_Msg' => 'Среди бирок есть занятая бирка'
			);
		}
		return true;
	}
	
	/**
	 * Проверка прав на освобождение бирки
	 */
	function checkHasRightsToClearRecord($data) {
		$session = $data['session'];
		$sel = $join = '';
		if ( 'TimetableGraf' == $data['object'] && (!isSuperAdmin()))
		{
			$sel = ', ISNULL(ev.EvnVizit_id,0) as EvnVizit_id';
			$join = 'left join v_EvnVizit ev with(nolock) on t.TimetableGraf_id = ev.TimetableGraf_id';
		}
		if ( !isSuperAdmin() )
		{
			switch ($data['object'])
			{
				case 'TimetableGraf':
					$sel .= ', convert(varchar,cast(t.TimetableGraf_begTime as datetime),104) as Timetable_Date';
					break;
				case 'TimetablePar':
					$sel .= ', convert(varchar,cast(t.TimetablePar_begTime as datetime),104) as Timetable_Date';
					break;
				case 'TimetableStac':
					$sel .= ', convert(varchar,cast(t.TimetableStac_setDate as datetime),104) as Timetable_Date';
					break;
				case 'TimetableMedService':
					$sel .= ', convert(varchar,cast(t.TimetableMedService_begTime as datetime),104) as Timetable_Date';
					break;
				case 'TimetableResource':
					$sel .= ', convert(varchar,cast(t.TimetableResource_begTime as datetime),104) as Timetable_Date';
					break;
			}
		}

		if ($data['object'] == 'TimetableGraf') {
			$sel .= ' , ISNULL(MSF.MedPersonal_id, ED.MedPersonal_did) as MedPersonal_did
						,pg.Person_id AS Person_gid';
			$join .= ' left join v_MedStaffFact MSF (nolock) on MSF.MedStaffFact_id = t.MedStaffFact_id
						outer apply (
								SELECT TOP 1 
									rl.TimetableGraf_id, rl.Person_id
								FROM 
									v_TimeTableGrafRecList rl (nolock)
								WHERE
									rl.TimetableGraf_id = t.TimetableGraf_id 
						) pg ';
		} else {
			$sel .= ' , ED.MedPersonal_did';
		}

		$sql = "
			DECLARE @curLpu_id bigint = :Lpu_id;
			SELECT
				t.{$data['object']}_id,
				t.Person_id,
				t.pmUser_updId,
				
				DF.DirectionFrom,
				t.EvnDirection_id,
				ED.MedPersonal_id,
								
				pu.Lpu_id,
				l.Org_id
				{$sel}
			FROM {$data['object']} t (nolock)
			left join v_pmUser pu (nolock) on t.pmUser_updId = pu.pmUser_id
			left join v_Lpu l (nolock) on l.Lpu_id = pu.Lpu_id
			left join v_EvnDirection_all ED (nolock) on ED.EvnDirection_id = t.EvnDirection_id
			outer apply (
					SELECT
						CASE
							WHEN ISNULL(ED.Lpu_did, ED.Lpu_id) = ED.Lpu_sid THEN 'both'
							WHEN ISNULL(ED.Lpu_did, ED.Lpu_id) = @curLpu_id THEN 'incoming'
							ELSE 'outcoming' END
						as DirectionFrom
				) DF
			{$join}
			WHERE
				t.{$data['object']}_id = :Id
		";

		$res = $this->getFirstRowFromQuery(
			$sql,
			array(
				'Id' => $data[$data['object'].'_id'],
				'Lpu_id' => $session['lpu_id']
			)
		);
		if ( is_object($res) )
			$res = $res->result('array');
		if ( !isset($res[$data['object'].'_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( empty($res['Person_id']) && empty($res['Person_gid'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором была освобождена ранее.'
			);
		}


		$MedPersonal_id = $session['medpersonal_id'];

		$rules = array(
			'incoming' => array( // входящее направление, когда текущее Lpu_id равно EvnDirection.Lpu_did
				'allCanCancel' =>  (!empty($session['setting']['server']['evn_direction_cancel_right_mo_where_adressed']) && $session['setting']['server']['evn_direction_cancel_right_mo_where_adressed'] == 2) ? true : false, // если есть глобальная настройка
				'hasGroupThatCanCancel' => in_array('toCurrMoDirCancel', explode('|', $session['groups']) ) ? true : false, // если есть группа у пользователя
				'relates' => $res['MedPersonal_did'] == $MedPersonal_id ? true : false // если к нему
			),
			'outcoming' => array( // исходящее Lpu_id = EvnDirection.Lpu_sid
				'allCanCancel' => (!empty($session['setting']['server']['evn_direction_cancel_right_mo_where_created']) && $session['setting']['server']['evn_direction_cancel_right_mo_where_created'] == 2) ? true : false, // если есть глобальная настройка
				'hasGroupThatCanCancel' => in_array('currMoDirCancel', explode('|', $session['groups']) ) ? true : false, // если есть группа у пользователя
				'relates' => $res['MedPersonal_id'] == $MedPersonal_id ? true : false // если создал
			)
		);

		$rules['both'] = array( // внутри одной мо, Lpu_id = EvnDirection.Lpu_did = EvnDirection.Lpu_sid. Или правила для входяших, или для исходящих
			'allCanCancel' => $rules['incoming']['allCanCancel'] || $rules['outcoming']['allCanCancel'],
			'hasGroupThatCanCancel' => $rules['incoming']['hasGroupThatCanCancel'] || $rules['outcoming']['hasGroupThatCanCancel'],
			'relates' => $rules['incoming']['relates'] || $rules['outcoming']['relates']
		);

		$userHasRightToCancel = $rules[$res['DirectionFrom']]['allCanCancel'] || $rules[$res['DirectionFrom']]['hasGroupThatCanCancel'] || $rules[$res['DirectionFrom']]['relates'];


		global $_USER;
		$isNeedCheckUser = true;
		$this->load->helper('Reg');
		if (!empty($data['cancelType']) && $data['cancelType'] == 'decline') {
			// отклонение направления в МО, в которое направили
			// проверку на пользователя надо убрать, иначе ничего нельзя будет отклонить #68892
			$isNeedCheckUser = false;
		} else {
			// отмена направления в МО, из которого направили
			if (isCZAdmin()
				|| isLpuRegAdmin($res['Org_id'])
				|| isInetUser($res['pmUser_updId'])
				|| ($_USER->belongsToOrg($res['Org_id']) && getRegionNick() == 'pskov')
			) {
				// разрешить отменять записи созданные другими пользователями
				$isNeedCheckUser = false;
			}
			if ($isNeedCheckUser && !empty($data['session'])
				&& !empty($data['session']['CurArmType'])
				&& 'regpol' == $data['session']['CurArmType']
				&& (IsFerUser($res['pmUser_updId']) || IsFerPerson($res['Person_id']))
			) {
				// разрешить отменять записи пришедшие из ФЭР в АРМ регистратора поликлиники #69681
				$isNeedCheckUser = false;
			}
		}

		if ($isNeedCheckUser && ! $userHasRightToCancel /*$res['pmUser_updId'] != $_USER->pmuser_id*/) {
			return array(
				'success' => false,
				'Error_Msg' => 'У вас нет прав отменить запись на прием, <br/>так как она сделана не вами.'
			);
		}
		/*if ( isset($res['Timetable_Date']) ) {
			$cur_date = new DateTime(date('d.m.Y'));
			$check_date = new DateTime($res['Timetable_Date']);
			if ( $check_date < $cur_date )
			{
				return array(
					'success' => false,
					'Error_Msg' => 'У вас нет прав отменить запись на прием, <br/>так как запись была создана раньше текущего дня. '
				);
			}
		}*/
		if ( !empty($res['EvnVizit_id']) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'У вас нет прав отменить запись на прием, <br/>так как по ней был заведен ТАП.'
			);
		}
		return true;
	}

	/**
	 * Проверка существования записи на бирку перед созданием записи в очередь
	 */
	function checkRecordExists($data)
	{
		$params = array(
			'LpuUnit_id' => $data['LpuUnit_did'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_did'],
			'Person_id' => $data['Person_id']
		);

		$filter = "";
		// по тому же направлению не нужно выдавать предупреждений, чтобы сразу выполнялось перенаправление.
		if (!empty($data['EvnDirection_id'])) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= " and ttg.EvnDirection_id != :EvnDirection_id";
		}

		$selectPersonFio = "ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') as Person_Fio";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ps.Person_id";
			$selectPersonFio = "case when peh.PersonEncrypHIV_Encryp is null 
				then ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') 
				else peh.PersonEncrypHIV_Encryp 
			end as Person_Fio";
		}

		$query = "
			declare @curdate date = cast(dbo.tzGetDate() as date);
			
			select
				ttg.EvnDirection_id,
				ttg.TimetableGraf_id,
				
				ED.ARMType_id,
				ED.EvnStatus_id,
				
				--ISNULL(ED.Lpu_did, ED.Lpu_id) as Lpu_did,
				--ED.Lpu_sid as Lpu_sid,
				CASE ISNULL(ED.Lpu_did, ED.Lpu_id)
					WHEN ED.Lpu_sid THEN 'both'
					WHEN {$data['session']['lpu_id']} THEN 'incoming'
					ELSE 'outcoming' END as RecordDirection,
				
				ED.MedPersonal_id,
				ED.MedPersonal_did,
				
				{$selectPersonFio},
				msf.Person_Fin as MedPersonal_Fin,
				l.Lpu_Nick,
				convert(varchar(10), ttg.TimetableGraf_begTime, 104) as TimetableGraf_Date,
				convert(varchar(5), ttg.TimetableGraf_begTime, 108) as TimetableGraf_Time
			from
				v_TimetableGraf_lite ttg with(nolock)
				left join v_PersonState ps with(nolock) on ps.Person_id = ttg.Person_id
				left join v_MedStaffFact msf with(nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
				left join v_LpuSection ls with(nolock) on ls.LpuSection_id = msf.LpuSection_id
				left join v_LpuUnit lu with(nolock) on lu.LpuUnit_id = ls.LpuUnit_id
				left join v_Lpu l with(nolock) on l.Lpu_id = lu.Lpu_id
				left join v_EvnDirection_all ED with (nolock) on ED.EvnDirection_id = ttg.EvnDirection_id
				{$joinPersonEncrypHIV}
			where
				lu.LpuUnit_id = :LpuUnit_id
				and ls.LpuSectionProfile_id = :LpuSectionProfile_id
				and ttg.Person_id = :Person_id
				and ttg.TimetableType_id is not null	--Отсекаем бирки без записи
				and ttg.TimetableGraf_factTime is null	--Отсекаем бирки, по которым уже были посещения
				and cast(ttg.TimetableGraf_begTime as date) >= @curdate	--Отсекаем бирки в прошедшем времени
				{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$resp = $resp[0];
				$response = array(
					'TimetableGraf_id' => $resp['TimetableGraf_id'],
					'EvnDirection_id' => $resp['EvnDirection_id'],
					'EvnStatus_id' => $resp['EvnStatus_id'],
					'MedPersonal_id' => $resp['MedPersonal_id'],
					'MedPersonal_did' => $resp['MedPersonal_did'],
					'ARMType_id' => $resp['ARMType_id'],
					'RecordDirection' => $resp['RecordDirection']
				);
				$med_personal_fin = empty($resp['MedPersonal_Fin'])?'':", врач: {$resp['MedPersonal_Fin']}";
				$response['warning'] = "
					Пациент {$resp['Person_Fio']} уже имеет запись по этому профилю на {$resp['TimetableGraf_Date']}, {$resp['TimetableGraf_Time']}
					<br/>в ЛПУ: {$resp['Lpu_Nick']}{$med_personal_fin}.
					<br/>Отменить запись?
				";
				return $response;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * Проверка существования записи в очередь перед созданием записи на бирку
     * Контроль на нахождение пациента в очереди либо на бирке (койке) по какому-либо профилю должен применяться только при записи на прием к врачу, либо на стационарный профиль.
	 */
	function checkQueueExists($data)
	{
		$params = array(
			'LpuUnit_did' => isset($data['LpuUnit_did'])?$data['LpuUnit_did']:null,
			'LpuSectionProfile_did' => isset($data['LpuSectionProfile_id'])?$data['LpuSectionProfile_id']:null,
			'Person_id' => $data['Person_id']
		);

		$filter = "";
		// по тому же направлению не нужно выдавать предупреждений, чтобы сразу выполнялась запись из очереди.
		if (!empty($data['EvnDirection_id'])) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= " and eq.EvnDirection_id != :EvnDirection_id";
		}

		// если известна служба, проверяем её
		if (!empty($data['MedService_id'])) {
			$params['MedService_id'] = $data['MedService_id'];
			$filter .= " and ed.MedService_id = :MedService_id";
		}

		$selectPersonFio = "ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') as Person_Fio";
		$joinPersonEncrypHIV = "";
		if (allowPersonEncrypHIV($data['session'])) {
			$joinPersonEncrypHIV = "left join v_PersonEncrypHIV peh with(nolock) on peh.Person_id = ps.Person_id";
			$selectPersonFio = "case when peh.PersonEncrypHIV_Encryp is null 
				then ps.Person_SurName+' '+ps.Person_FirName+' '+isnull(ps.Person_SecName,'') 
				else peh.PersonEncrypHIV_Encryp 
			end as Person_Fio";
		}

		$query = "
			select top 1
				ed.EvnDirection_id,
				ed.EvnDirection_pid,
				isnull(ed.EvnDirection_IsAuto,1) as EvnDirection_IsAuto,
				ed.EvnStatus_SysNick,
				eq.EvnQueue_id,
				epd.EvnPrescr_id,
				{$selectPersonFio},
				mp.Person_Fin as MedPersonal_Fin,
				l.Lpu_Nick,
				convert(varchar(10), eq.EvnQueue_setDate, 104) as EvnQueue_Date
			from
				v_EvnQueue eq with(nolock)
				outer apply (
					select top 1
						ed.EvnDirection_id,
						ed.EvnDirection_pid,
						es.EvnStatus_SysNick,
						ed.EvnDirection_IsAuto,
						ed.MedService_id
					from v_EvnDirection_all ed (nolock) 
						left join v_EvnStatus es (nolock) on es.EvnStatus_id = ed.EvnStatus_id
					where 
						ed.EvnDirection_id = eq.EvnDirection_id
						and es.EvnStatus_SysNick = 'Queued'
				) ed
				left join v_PersonState ps with(nolock) on ps.Person_id = eq.Person_id
				left join v_MedPersonal mp with(nolock) on mp.MedPersonal_id = eq.MedPersonal_did
				left join v_LpuUnit lu with(nolock) on lu.LpuUnit_id = eq.LpuUnit_did
				left join v_Lpu l with(nolock) on l.Lpu_id = lu.Lpu_id
				left join v_EvnPrescrDirection epd with(nolock) on epd.EvnDirection_id = ed.EvnDirection_id
				{$joinPersonEncrypHIV}
			where
				eq.LpuUnit_did = :LpuUnit_did
				and ed.EvnDirection_id is not null
				and eq.LpuSectionProfile_did = :LpuSectionProfile_did
				and eq.Person_id = :Person_id
				and isnull(eq.EvnQueue_recDT, 0) = 0
				and eq.EvnQueue_failDT is null
				{$filter}
		";
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				$resp = $resp[0];
				$response = array(
					'EvnDirection_id' => $resp['EvnDirection_id'],
					'EvnDirection_pid' => $resp['EvnDirection_pid'],
					'EvnDirection_IsAuto' => $resp['EvnDirection_IsAuto'],
					'EvnStatus_SysNick' => $resp['EvnStatus_SysNick'],
					'EvnQueue_id' => $resp['EvnQueue_id'],
					'EvnPrescr_id' => $resp['EvnPrescr_id'],
				);
				$med_personal_fin = empty($resp['MedPersonal_Fin'])?'':", врач: {$resp['MedPersonal_Fin']}";
				$response['warning'] = "
					Пациент {$resp['Person_Fio']} находится в очереди по этому профилю с {$resp['EvnQueue_Date']}
					<br/>в ЛПУ: {$resp['Lpu_Nick']}{$med_personal_fin}.
					<br/>Исключить пациента из очереди по данному профилю?
				";
				return $response;
			} else {
				return false;
			}
		}
		return false;
	}
	/**
	 * Проверка записи на резервную бирку чужого МО
	 * @param array $data
	 * @return boolean
	 */
	function checkRecordTTGReserveOtherLpu($data){
		$ttg_data = $this->getRecordTTGData($data);
		return ($ttg_data['TimetableType_id']!=2);

	}
	
	/**
	 * Запись человека на бирку
	 */
	function Apply($data, $checkTimetableFree = true)
	{
		$EvnDirectionInfo = array(); // Массив с информацией о существующих направлениях

		if ( $checkTimetableFree && true !== ($res = $this->checkTimetableFree($data)) ) {
			return $res;
		}

		$this->load->helper('Reg');

		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			if (true !== ($res = $this->checkNotArchive($data))) {
				return array(
					'success' => false,
					'Error_Msg' => 'Запись на архивные даты запрещена'
				);
			}
		}
		if (empty($data['IgnoreCheckAlreadyHasRecordOnThisTime'])) {
			$res = $this->checkAlreadyHasRecordOnThisTime($data);
			if (is_array($res) && isset($res['info'])) {
				if ('TimetableMedService' == $data['object']) {
					return array(
						'success' => false,
						'alreadyHasRecordOnThisTime' => 'У пациента уже есть запись на выбранное время. '.$res['info']
					);
				} else {
					return array(
						'success' => false,
						'Error_Msg' => 'У пациента уже есть запись на выбранное время. '.$res['info']
					);
				}
			}
		}


		/* *** SWITCH: START *** */
		switch ($data['object'])
		{
			// Расписание, бирки по поликлинике ???
			case 'TimetableGraf':
				$tt_data = $this->getRecordTTGData($data);

				/* *** NGS: EXTRA CHECK - START *** */
				if(!$tt_data) {
					return array(
						'success' => false,
						'Error_Msg' => 'Пожалуйста, перезагрузите данную страницу и повторите попытку. '
					);
				};
				/* *** NGS: EXTRA CHECK - END *** */

				if (!IsCZUser() && IsOtherLpuRegUser($tt_data['Org_id'])){
					if(true !== ($res = $this->checkRecordTTGOtherLpu($data))) {
						return array(
							'success' => false,
							'Error_Msg' => 'Запись на выбранное время в чужую МО невозможна'
						);
					}
					if ( true !== ($res = $this->checkRecordTTGReserveOtherLpu($data))) {
						return array(
							'success' => false,
							'Error_Msg' => 'Запись на резервную бирку в чужую МО невозможна'
						);
					}
				}
				
				$this->load->model('TimetableQuote_model', 'tqmodel');
				// Если передан врач, то запись от него
				if (!empty($data['From_MedStaffFact_id'])) {
					$tt_data['From_MedStaffFact_id'] = $data['From_MedStaffFact_id'];
				}
				if ( ($err = $this->tqmodel->checkTimetableQuote($data, $tt_data, 'ttg')) !== true ) {
					return array(
						'success' => false,
						'Error_Msg'=> ($err === false) ? 'Запись невозможна. Превышена квота записи для вашей МО.' : $err
					);
				}

				if(!empty($tt_data) && !empty($tt_data['TimetableType_id']) && $tt_data['TimetableType_id'] == 14){
					if(!empty($tt_data['TimeTableGraf_countRec'])
						&& intval($tt_data['TimeTableGraf_countRec']) >= intval($tt_data['TimeTableGraf_PersRecLim']))
						return array(
							'success' => false,
							'Error_Msg'=> 'Запись невозможна. Превышено количество записей в группу.'
						);
				}
			break;

			// Расписание службы
			case 'TimetableMedService':
				$tt_data = $this->getRecordTTMSData($data);

				/* *** NGS: EXTRA CHECK - START *** */
				if(!$tt_data) {
					return array(
						'success' => false,
						'Error_Msg' => 'Пожалуйста, перезагрузите данную страницу и повторите попытку. '
					);
				};
				/* *** NGS: EXTRA CHECK - END *** */

				$this->load->model('TimetableQuote_model', 'tqmodel');

				if ( ($err = $this->tqmodel->checkTimetableQuote($data, $tt_data, 'ttms')) !== true ) {
					return array(
						'success' => false,
						'Error_Msg'=> ($err === false) ? 'Запись невозможна. Превышена квота записи для вашей МО.' : $err
					);
				}
				$EvnDirectionInfo['EvnDirection_setDate'] = $tt_data['TimetableMedService_DT'];
				$EvnDirectionInfo['MedService_Nick'] = $tt_data['MedService_Name'];
				$data['AnswerQueue'] = false;

				$data['MedServiceType_SysNick'] = $this->getFirstResultFromQuery("
					select top 1 MST.MedServiceType_SysNick
					from v_MedService MS with(nolock)
					left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					where MS.MedService_id = :MedService_id
				", $data);
			break;

			// Расписание службы для организаций
			case 'TimetableMedServiceOrg':
				$tt_data = $this->getRecordTTMSOData($data);

				/* *** NGS: EXTRA CHECK - START *** */
				if(!$tt_data) {
					return array(
						'success' => false,
						'Error_Msg' => 'Пожалуйста, перезагрузите данную страницу и повторите попытку. '
					);
				};
				/* *** NGS: EXTRA CHECK - END *** */

                $data['AnswerQueue'] = false;
				$tt_data['TimetableType_id']=1;
				$tt_data['object'] = 'TimetableMedServiceOrg';
			break;

			// Расписание ресурсов
			case 'TimetableResource':
				$tt_data = $this->getRecordTTRData($data);

				/* *** NGS: EXTRA CHECK - START *** */
				if(!$tt_data) {
					return array(
						'success' => false,
						'Error_Msg' => 'Пожалуйста, перезагрузите данную страницу и повторите попытку. '
					);
				};
				/* *** NGS: EXTRA CHECK - END *** */

				$this->load->model('TimetableQuote_model', 'tqmodel');
				// Если передан врач, то запись от него
				if (!empty($data['From_MedStaffFact_id'])) {
					$tt_data['From_MedStaffFact_id'] = $data['From_MedStaffFact_id'];
				}
				if ( ($err = $this->tqmodel->checkTimetableQuote($data, $tt_data, 'ttr')) !== true ) {
					return array(
						'success' => false,
						'Error_Msg'=> ($err === false) ? 'Запись невозможна. Превышена квота записи для вашей МО.' : $err
					);
				}

				$data['AnswerQueue'] = false;
			break;

			// Расписание, бирки по стационару
			case 'TimetableStac':
				$tt_data = $this->getRecordTTSData($data);

				/* *** NGS: EXTRA CHECK - START *** */
				if(!$tt_data) {
					return array(
						'success' => false,
						'Error_Msg' => 'Пожалуйста, перезагрузите данную страницу и повторите попытку.'
					);
				};
				/* *** NGS: EXTRA CHECK - END *** */
			break;
		}
		/* *** SWITCH: END *** */

		if (
			!empty($data['MedService_id']) && !empty($tt_data['MedService_id']) && $data['MedService_id'] != $tt_data['MedService_id'] // бирка должна быть от той службы, куда ведётся запись
			&& (empty($data['MedServiceType_SysNick']) || $data['MedServiceType_SysNick'] != 'pzm') // когда записываем на бирку пункта забора, то служба куда направили будет лабораторией.
			&& (empty($tt_data['MedServiceType_SysNick']) || $tt_data['MedServiceType_SysNick'] != 'pzm') // когда записываем на бирку пункта забора, то служба куда направили будет лабораторией.
		) {
			return array(
				'success' => false,
				'Error_Msg' => 'Некорректно выбрана бирка.'
			);
		}


		if ( empty($data['ignoreCanRecord']) && !canRecord($tt_data, $data) ) {
			// *** ДАННАЯ ОШИБКА ВОЗМОЖНА, ЕСЛИ НЕ УСТАНОВЛЕН USER->ORGTYPE == 'LPU'.  НУЖЕН ДОСТУП К ИСТОРИИ СЕССИЙ ***
			return array(
				'success' => false,
				'Error_Msg' => 'Извините, запись на бирку запрещена.'
			);
		}

		
		if ( !(IsLpuAdmin($data['Lpu_id']) || IsCZUser()) ) { // запись на прошедшее время разрешена пользователям ЦЗ и администраторам своего МО
			if ( true !== ($res = $this->checkPastTimeRecord($tt_data)) ) {
				return $res;
			}
		}
		if (!isset($data['AnswerQueue']) || $data['AnswerQueue']) {
			$queue = $this->checkQueueExists($data);
			if ($queue) {
				return array(
					'Person_id' => $data['Person_id'],
					'Server_id' => $data['Server_id'],
					'PersonEvn_id' => $data['PersonEvn_id'],
					'success' => false,
					'queue' => $queue
				);
			}
		}

		if (empty($data['OverrideWarning'])) {
			// Дополнительные проверки при записи
			$warnings = array();
			
			//TO-DO: напихать необходимых проверок
			switch ($data['object'])
			{
				case 'TimetableGraf':
					$warnings = $this->checkWarningsTTG($data);
				break;
			}

			
			if ( count($warnings) > 0 ) {
				return array(
                    'Person_id' => $data['Person_id'],
                    'Server_id' => $data['Server_id'],
                    'PersonEvn_id' => $data['PersonEvn_id'],
					'success' => false,
					'warning' => implode('<br/>', $warnings).'<br/> Продолжить запись?'
				);
			}
		}

		// Переход на расово верные хранимки
		if (isset($data['EmergencyData_id'])) {
			// Если бронируем койку, то вариант только один - мы записываем в стационар
			$sql = "
				declare
					@EmergencyData_id bigint,
					@Error_Code bigint,
					@Error_Message varchar(4000);
				exec p_TimetableStac_reservBed
					@TimetableStac_id = :TimetableStac_id,
					@Person_id = :Person_id,
					@RecClass_id = :RecClass_id,
					@EmergencyData_id = :EmergencyData_id,
					@Evn_pid = :Evn_pid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Code as Error_Code, @Error_Message as Error_Msg;";
			
			$r = $this->db->query(
				$sql,
				array(
					'Person_id' => $data['Person_id'],
					'pmUser_id' => $data['pmUser_id'],
					'Evn_pid' => $data['Evn_pid'],
					'EmergencyData_id' => $data['EmergencyData_id'],
					'RecClass_id' => 3, //Запись из ПромедВеб
					'TimetableStac_id' => $data[$data['object'].'_id']
				)
			);
			
			if ( is_object($r) ) {
				$res = $r->result('array');
				if (count($res) > 0 && empty($res[0]['Error_Msg'])) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $data[$data['object'].'_id'],
						'timeTable' => $data['object'],
						'action' => 'Reserv',
						'setDate' => date("c")
					),'Rule');
				}
			}
		} else {
			if ( isset($data['redirectEvnDirection']) && 600 == $data['redirectEvnDirection'] && empty($data['EvnQueue_id']) ) {
				return array(
					'success' => false,
					'Error_Code' => 400,
					'Error_Msg' => 'Параметр EvnQueue_id обязателен для правильной работы записи из очереди',
				);
			}
			if ('TimetableMedServiceOrg' == $data['object']) {
				$tt_data['pmUser_id']=$data['pmUser_id'];
				$tt_data['Org_id']=$data['Org_id'];
				$this->recordTimetableMedServiceOrgAuto($tt_data);
				$res =array($this->getRecordTTMSOData($data));
			}else{
				if ('TimetableStac' == $data['object']) {
					$data['Evn_id'] = $data['Evn_pid']; // какая-то нечеловеческая логика
				}

				if ( 'TimetableGraf' === $data['object'] && $tt_data['TimetableGraf_begTime'] instanceof DateTime
					&& false === empty($this->config->config['USER_PORTAL_IS_ALLOW_NOTIFY_ABOUT_RECORD_CANCEL'])
				) {
					if ( !empty($tt_data['Person_id']) ) {
						$Person_id = $tt_data['Person_id'];
					}
					else if ( !empty($data['Person_id']) ) {
						$Person_id = $data['Person_id'];
					}
					else {
						return array(
							'success' => false,
							'Error_Msg' => 'Не указан идентификатор пациента',
						);
					}

					$this->load->model('UserPortal_model');
					$this->UserPortal_model->notifyAboutRecordCancel($Person_id, $data['TimetableGraf_id'], $tt_data['TimetableGraf_begTime']);
				}

				$is_lab_diag = (!empty($tt_data['MedServiceType_SysNick']) && in_array($tt_data['MedServiceType_SysNick'], array('lab','pzm')));

				// Вместо обычной записи всегда создаем направление, запись происходит во время его создания
				$this->load->model('EvnDirection_model', 'edmodel');
				if (empty($data['EvnQueue_id']) && $data['object']=='TimetableStac' && $data['EvnDirection_IsAuto']==2) {
						$this->recordTimetableStacAuto($data);
						$res =array($this->getRecordTTSData($data));
				} else if (isset($data['redirectEvnDirection']) && 600 == $data['redirectEvnDirection']) {
					$res = $this->edmodel->applyEvnDirectionFromQueue($data);
				} else if ($this->usePostgreLis && $is_lab_diag) {
					$this->load->swapi('lis');
					$res = $this->lis->POST('EvnDirection', $data, 'list');
				} else {
					$res = $this->edmodel->saveEvnDirection($data);
				}
				if (!$this->isSuccessful($res)) {
					return $res;
				}
				
				$EvnDirectionInfo['EvnDirection_Num'] = !empty($res[0]['EvnDirection_Num'])?$res[0]['EvnDirection_Num']:null;
				$EvnDirectionInfo['EvnDirection_id'] = !empty($res[0]['EvnDirection_id'])?$res[0]['EvnDirection_id']:null;
				
				// При записи на бирку стационара нужно обновить продолжительность лечения у бирки
				if ($data['object']=='TimetableStac' && !empty($data['TimetableStac_id']) && !empty($data['EvnDirection_setDate'])) {
					$this->load->model('EvnSection_model', 'esmodel');
					
					$healDuration = $this->esmodel->getAverageDateStatement(array(
						'Person_id' => $data['Person_id'],
						'Evn_setDT' => $data['EvnDirection_setDate'],
						'Diag_id' => $data['Diag_id'],
						'LpuSection_id' => $data['LpuSection_did']
					))[0];
					
					if (!empty($healDuration['Duration'])) {
						$this->saveObject('TimeTableStac', array(
							'TimeTableStac_id' => $data['TimetableStac_id'],
							'TimeTableStac_CureDuration' => $healDuration['Duration']
						));
					}
					else {
						return array(
							'success' => false,
							'Error_Msg' => 'Не удалось определить продолжительность лечения',
						);
					}
				}

				$data['EvnDirection_id'] = $res[0]['EvnDirection_id'];
				if (!empty($res[0]['EvnLabRequest_id'])) {
					$data['EvnLabRequest_id'] = $res[0]['EvnLabRequest_id'];
				}

				if (!empty($data['TimetableMedService_id']) && $is_lab_diag) {
					$resp = $this->recordTimetableMedService($data);
					if (!$this->isSuccessful($resp)) {
						return $resp;
					}

					if (!empty($data['EvnLabRequest_id'])) {
						$prmTime = $this->getFirstResultFromQuery("
							select top 1 TimetableMedService_begTime
							from v_TimetableMedService_lite with(nolock)
							where TimetableMedService_id = :TimetableMedService_id
						", array(
							'TimetableMedService_id' => $data['TimetableMedService_id'],
						));
						if (empty($prmTime)) {
							return $this->createError('','Ошибка при получении времени записи');
						}

						if ($this->usePostgreLis) {
							$this->load->swapi('lis');
							$resp = $this->lis->PATCH('EvnLabRequest/prmTime', array(
								'EvnLabRequest_id' => $data['EvnLabRequest_id'],
								'EvnLabRequest_prmTime' => $prmTime
							), 'list');
					} else {
							$this->load->model('EvnLabRequest_model');
							$resp = $this->EvnLabRequest_model->saveEvnLabRequestPrmTime(array(
								'EvnLabRequest_id' => $data['EvnLabRequest_id'],
								'EvnLabRequest_prmTime' => $prmTime
							));
					}
						if (!$this->isSuccessful($resp)) {
							return $resp;
				}
					}
				}

				if ( is_array($res) && count($res) > 0 && empty($res[0]['Error_Msg'])) {
					// отправка STOMP-сообщения
					sendFerStompMessage(array(
						'id' => $data[$data['object'].'_id'],
						'timeTable' => $data['object'],
						'action' => 'RecPatient',
						'setDate' => date("c")
					),'Rule');
				}

				// сохраняем заказ, если есть необходимость
				if ($this->usePostgreLis && $is_lab_diag) {
					$this->load->swapi('lis');
					$order = $this->lis->POST('EvnUsluga/Order', $data, 'single');
					if (!$this->isSuccessful($order)) {
						return array($order);
			}
				} else {
					$this->load->model('EvnUsluga_model', 'eumodel');
					try {
						$order = $this->eumodel->saveUslugaOrder($data);
					} catch (Exception $e) {
						return array('success' => false, 'Error_Msg' => $e->getMessage());
		}
				}
				if (isset($order['EvnUsluga_id'])) {
					$data['EvnUsluga_id'] = $order['EvnUsluga_id'];
					$data['EvnUslugaPar_id'] = $order['EvnUsluga_id'];
				}
			}
		}

		if(!empty($data['PrescriptionType_Code']) && $data['PrescriptionType_Code'] == 11 && !empty($res[0]['EvnDirection_id'])){
			$UslugaComplexListByPrescr = $this->edmodel->getUslugaComplexByPrescrId($data['EvnPrescr_id']);
			if (!is_array($UslugaComplexListByPrescr)) {
				return $this->createError('','Ошибка при получении услуг по назначению');
			}

			// значит нам туда дорога, зна-чит-нам-ту-да-до-ро-га (Лабораторная диагностика)
			$this->load->model( 'Queue_model', 'Queue_model' );
			$UslugaList = $this->Queue_model->getUslugaWithoutDirectoryList($data);

			if(!empty($UslugaList) && is_array($UslugaList) && count($UslugaList)>0){
				$msg = "Услуги могут быть объединены: <br>";
				foreach($UslugaList as $key => $usluga){
					$msg .= (intval($key)+1).'. '.$usluga['UslugaComplex_Name'].'<br>';
				}
				$response[0]['EvnDirection_id'] = $data['EvnDirection_id'];
				$UslugaListJSON = json_encode($UslugaList);
			}
		}

		if ( is_array($res)  ) {
			if ( empty($res[0]['Error_Msg']) ) {
				return array(
					'success' => true
					,'object' => $data['object']
					,'id' => $data[$data['object'].'_id']
					,'EvnDirection_id' => isset($res[0]['EvnDirection_id'])?$res[0]['EvnDirection_id']:null
					,'EvnDirection_TalonCode' => isset($res[0]['EvnDirection_TalonCode'])?$res[0]['EvnDirection_TalonCode']:null
					,'addingMsg' => (!empty($msg))?$msg:null
					,'UslugaList' => (!empty($UslugaListJSON))?$UslugaListJSON:null
					,'EvnDirectionInfo' => (!empty($EvnDirectionInfo))?$EvnDirectionInfo:null
				);
			} else {
				return array(
					'success' => false,
					'Error_Code' => isset($res[0]['Error_Code']) ? $res[0]['Error_Code'] : null,
					'Error_Msg' => $res[0]['Error_Msg'],
				);
			}
		} else {
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка записи.'
			);
		}
	}

	/**
	 *
	 * @param type $data 
	 */
	function recordTimetableMedServiceOrgAuto($data){
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_TimetableMedServiceOrg_upd
				@TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id,
				@MedService_id = :MedService_id,
				@Org_id = :Org_id,
				@TimetableMedServiceOrg_begTime = :Timetable_Date,
				@TimetableMedServiceOrg_Day = :TimetableMedServiceOrg_Day,
				@TimetableMedServiceOrg_Time = :TimetableMedServiceOrg_Time,
				@TimetableMedServiceOrg_factTime = :TimetableMedServiceOrg_factTime,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		if(!isset($data['TimetableMedServiceOrg_factTime'])){
			$data['TimetableMedServiceOrg_factTime']=new DateTime();
		}
		$queryParams = array(
			
		);
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query, $data);
	}
	/**
	 *
	 * @param type $data 
	 */
	function recordTimetableStacAuto($data){
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_TimetableStac_record
				@TimetableStac_id = :TimetableStac_id,
				@Evn_id = :Evn_id,
				@RecClass_id = 3,
				@RecMethodType_id = 1,
				@Person_id = :Person_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'TimetableStac_id' => $data['TimetableStac_id'],
			'Evn_id' => $data['Evn_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->db->query($query, $queryParams);
	}

	/**
	 * Запись на службу
	 * @param array $data
	 * @return array
	 */
	function recordTimetableMedService($data) {
		$queryParams = array(
			'TimetableMedService_id' => $data['TimetableMedService_id'],
			'Person_id' => $data['Person_id'],
			'EvnDirection_pid' => $data['EvnDirection_pid'],
			'EvnDirection_id' => $data['EvnDirection_id'],
			'EvnDirection_IsAuto' => $data['EvnDirection_IsAuto'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_TimetableMedService_record
				@TimetableMedService_id = :TimetableMedService_id,
                @Person_id = :Person_id,
                @Evn_id = :EvnDirection_pid,
                @RecClass_id = 1,
                @EvnDirection_id = :EvnDirection_id,
                @TimeTableMedService_isAuto = :EvnDirection_IsAuto,
                @pmUser_id  = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		//echo getDebugSQL($query, $queryParams);die();
		$result = $this->queryResult($query, $queryParams);
		if (!is_array($result)) {
			return $this->createError('Ошибка при записи на бирку');
		}
		return $result;
	}

	/**
	 * Подготовка к записи
	*/
	function recordEvnDirection($data) {
		$query = "
			update TimeTableMedService with (ROWLOCK)
    		set
    		    Person_id = :Person_id,
    		    RecClass_id = :RecClass_id,
    		    Evn_id = :Evn_id,
    		    EvnDirection_id = :EvnDirection_id,
    		    RecMethodType_id = null,
    		    pmUser_updID = :pmUser_id,
    		    TimeTableMedService_updDT = dbo.tzGetDate()
    		where TimeTableMedService_id = :TimetableMedService_id
		";
		$this->db->query($query, $data);
		$data['EvnDirection_pid'] = $data['Evn_id'];
		return $this->recordTimetableMedService($data);
	}

	/**
	 * Проверка, что бирка существует и свободна
	 */
	function checkTimetableFree($data) {

		$tt_data = $this->getRecordData($data);
		if ( !isset($tt_data["{$data['object']}_id"]) ) {
			return array(
				'success' => false,
				'Error_Msg' => 'Бирка с таким идентификатором не существует.'
			);
		}
		if ( isset($tt_data['Person_id'])) {
			return array(
				'success' => false,
				'Error_Msg' => 'Выбранная вами бирка уже занята.'
			);
		}
		
		return true;
	}
	
	/**
	 * Проверка на запись на прошедшее время
	 */
	function checkPastTimeRecord($tt_data) {
		if ( isset($tt_data['Timetable_Date']) ) {
			$cur_date = new DateTime(date('d.m.Y H:i'));
			$check_date = $tt_data['Timetable_Date'];
			if ( $check_date < $cur_date )
			{
				return array(
					'success' => false,
					'Error_Msg' => 'Вы не можете записать пациента на прошедшее время.'
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
		
		return true;
	}
	
	
	/**
	 * Освобождение бирки
	 */
	function Clear($data)
    {
		if ( true !== ($res = $this->checkTimetableOccupied($data)) ) {
			return $res;
		}
		if ( true !== ($res = $this->checkHasRightsToClearRecord($data)) ) {
			return $res;
		}
		$this->beginTransaction();
		
		try {
			
			$queryParams = array(
				"{$data['object']}_id" => $data[$data['object'].'_id'],
				'pmUser_id' => $data['pmUser_id'],
			);

			// смотрим есть ли связанный лист ожидания по бирке, со статусом ожидания подтверждения
			if (!empty($data['TimetableGraf_id'])) {

				//todo: проверять включены ли листы ожидания на регионе и в ЛПУ

				$EvnQueue_id = $this->getFirstResultFromQuery("
					select top 1 EvnQueue_id 
					from v_EvnQueue (nolock) 
					where (1=1)
						and EvnQueueStatus_id = 2
						and TimetableGraf_id = :TimetableGraf_id
						and RecMethodType_id = 1
				",
					array('TimetableGraf_id' => $data['TimetableGraf_id'])
				);

				if (!empty($EvnQueue_id)) $data['dontCancelDirection'] = true;
			}

			if (empty($data['dontCancelDirection'])) {

			if (!empty($data['EvnStatusCause_id'])) {
				// значит DirFailType_id вычисляем на основе EvnStatusCause_id
					$data['DirFailType_id'] = $this->getFirstResultFromQuery("
					select top 1 escl.DirFailType_id from v_EvnStatusCauseLink escl (nolock) where escl.EvnStatusCause_id = :EvnStatusCause_id",
						array(
					'EvnStatusCause_id' => $data['EvnStatusCause_id']
						)
					);
			}

				if ( $this->usePostgreLis && !empty($data['TimetableMedService_id']) ) {
					$resp = $this->getFirstRowFromQuery("
						select top 1
							TMS.EvnDirection_id,
							MST.MedServiceType_SysNick
						from 
							v_TimetableMedService TMS with(nolock)
							left join v_UslugaComplexMedService UCMS with(nolock) on UCMS.UslugaComplexMedService_id = TMS.UslugaComplexMedService_id
							left join v_MedService MS with(nolock) on MS.MedService_id = isnull(TMS.MedService_id, UCMS.MedService_id)
							left join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
						where 
							TMS.TimetableMedService_id = :TimetableMedService_id
					", $data);
				}

				if ($this->usePostgreLis && !empty($resp['MedServiceType_SysNick']) && in_array($resp['MedServiceType_SysNick'], array('lab','pzm','reglab'))) {
					if(!empty($resp['EvnDirection_id'])){
						$this->load->model('EvnPrescr_model', 'epmodel');
						$data['EvnDirection_id'] = $resp['EvnDirection_id'];
						$err = $this->epmodel->findAndDeleteEvnPrescrDirection($data);
					}
					$this->load->swapi('lis');
					$err = $this->lis->POST('EvnDirection/cancelByRecord', $data, 'single');
				} else {
					$this->load->model('EvnDirection_model', 'edmodel');
					$err = $this->edmodel->cancelEvnDirectionbyRecord($data);
				}

			if (!empty($err)) {
				$this->rollbackTransaction();
				return array(
					'success' => false,
					'Error_Msg' => (is_array($err) && !empty($err['Error_Msg']))?$err['Error_Msg']:$err
				);
			}
			}

			if('TimetableGraf' === $data['object'] && !empty($data['TimetableGrafRecList_id'])){
				$err = $this->cancelTimetableGrafRecList($data);
				if (!empty($err[0]) && !empty($err[0]['Error_Msg'])) {
					$this->rollbackTransaction();
					return array(
						'success' => false,
						'Error_Msg' => $err[0]['Error_Msg']
					);
				}
			}

			if ('TimetableMedService' === $data['object']) {
				$this->load->model('Mse_model', 'msemodel');
				$err = $this->msemodel->cancelEvnPrescrbyRecord($data);
				if (!empty($err)) {
					$this->rollbackTransaction();
					return array(
						'success' => false,
						'Error_Msg' => $err
					);
				}
			}

			$resp = $this->execCommonSP("p_{$data['object']}_cancel", $queryParams);
			if ($resp) {
			    if (count($resp) > 0 && empty($resp[0]['Error_Msg'])) {

					if(!empty($data['TimetableGraf_id'])){
						// удаляем Источник записи 
						$tmp = $this->swUpdate('TimetableGraf', array(
							'TimetableGraf_id' => $queryParams['TimetableGraf_id'],
							'pmUser_id' => $queryParams['pmUser_id'],
							'RecMethodType_id' => null
						), true);
						if (empty($tmp) || false == is_array($tmp)) {
							throw new Exception('Ошибка запроса к БД при удалении источника записи бирки', 500);
						}
					}
					
					if (!empty($data['TimetableGraf_id'])){
						$tt_data = $this->getRecordTTGData($data);
					}

					if (!empty($data['TimetableMedService_id'])){
						$ttms_data = $this->getRecordTTMSData($data);
					}

					if ($data['object'] === 'TimetableGraf'
						&& !empty($this->config->config['USER_PORTAL_IS_ALLOW_NOTIFY_ABOUT_RECORD_CANCEL'])
						// для бирки связанной листом ожидания не отправляем это уведомление
						&& empty($data['EvnQueue_id']) && empty($EvnQueue_id)
					) {
						$this->load->model('UserPortal_model');
						$this->UserPortal_model->notifyAboutRecordCancel($tt_data['Person_id'], $data['TimetableGraf_id']);
					}

					// Если бирка связана с листом ожидания в статусе ожидает подверждения то возвращаем его в пред. состояние
					if (!empty($EvnQueue_id) && !empty($tt_data)) {
						$this->load->model('Queue_model');
						$this->Queue_model->getBackEvnQueue(
							array(
								'EvnQueue_id' => $EvnQueue_id,
								'Person_id' => $tt_data['Person_id'],
								'pmUser_id' => $data['pmUser_id'],
								'EvnQueueAction' => 'clear'
							)
						);
					}

				    if (isset($tt_data['TimetableGraf_facttime'])) {
					    // Удаление бирки если она была незапланированной
						$this->DeleteTTG($data);
				    } else if (isset($ttms_data['TimetableMedService_facttime'])) {
						// Удаление бирки если она была незапланированной
						$this->Delete($data);
					}

					// если на бирке есть примечание "Интеграция с ФЭР", надо его удалить.
					if ($data['object'] == 'TimetableGraf' && !empty($data['TimetableGraf_id'])) {
						$resp_te = $this->queryResult("
							select top 1 TimetableExtend_id from v_TimetableExtend (nolock) where TimetableGraf_id = :TimetableGraf_id and pmUser_updID = 999900
						", array(
							'TimetableGraf_id' => $data['TimetableGraf_id']
						));

						if (!empty($resp_te[0]['TimetableExtend_id'])) {
							$this->db->query("
								declare
									@Error_Code bigint,
									@Error_Message varchar(4000);
								exec p_TimetableExtend_del
									@TimetableExtend_id = :TimetableExtend_id,
									@Error_Code = @Error_Code output,
									@Error_Message = @Error_Message output;
								select @Error_Code as Error_Code, @Error_Message as Error_Msg;
							", array(
								'TimetableExtend_id' => $resp_te[0]['TimetableExtend_id']
							));
						}
					}

				    $this->commitTransaction();
				    return array('success' => true);

				    // отправка STOMP-сообщения
				    sendFerStompMessage(array(
					'id' => $data[$data['object'] . '_id'],
					'timeTable' => $data['object'],
					'action' => 'FreeTag_CancelDirect',
					'setDate' => date("c")
					    ), 'Rule');

			    } else {
				    throw new Exception($resp[0]['Error_Msg']);
			    }
			} else {
				throw new Exception('');
				return array(
					'success' => false,
					'Error_Msg' => 'Ошибки запроса к БД.'
				);
			}
			
		} catch (Exception $e) {
			$this->rollbackTransaction();
			return array(
				'success' => false,
				'Error_Msg' => 'Ошибка при освобождении бирки: ' . $e->getMessage()
			);
		}
	}

	/**
	 * Временная блокировка бирки для записи
	 * Блокирует бирку поликлиники или стационара или службы
	 */
	function lock($data)
	{
		if (empty($data['TimetableGraf_id']) && empty($data['TimetableStac_id']) && empty($data['TimetableMedService_id']) && empty($data['TimetableResource_id'])) {
			return array(array('Error_Code'=>400, 'Error_Msg'=>'Не указана бирка для блокировки'));
		}

		$queryParams = array(
			'pmUser_id' => $data['pmUser_id'],
			'TimetableGraf_id' => empty($data['TimetableGraf_id'])?null:$data['TimetableGraf_id'],
			'TimetableStac_id' => empty($data['TimetableStac_id'])?null:$data['TimetableStac_id'],
			'TimetableMedService_id' => empty($data['TimetableMedService_id'])?null:$data['TimetableMedService_id'],
			'TimetableResource_id' => empty($data['TimetableResource_id'])?null:$data['TimetableResource_id'],
		);
		
		if ( ($resp = $this->execCommonSP("p_TimetableLock_block", $queryParams))) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Снимает временную блокировку с бирки поликлиники или стационара или службы
	 */
	function unlock($data)
	{
		if (empty($data['TimetableGraf_id']) && empty($data['TimetableStac_id']) && empty($data['TimetableMedService_id']) && empty($data['TimetableResource_id'])) {
			return array(array('Error_Code'=>400, 'Error_Msg'=>'Не указана бирка для снятия блокировки'));
		}
		
		$queryParams = array(
			'pmUser_id' => $data['pmUser_id'],
			'TimetableGraf_id' => empty($data['TimetableGraf_id'])?null:$data['TimetableGraf_id'],
			'TimetableStac_id' => empty($data['TimetableStac_id'])?null:$data['TimetableStac_id'],
			'TimetableMedService_id' => empty($data['TimetableMedService_id'])?null:$data['TimetableMedService_id'],
			'TimetableResource_id' => empty($data['TimetableResource_id'])?null:$data['TimetableResource_id'],
		);
		
		if ( ($resp = $this->execCommonSP("p_TimetableLock_unblock", $queryParams))) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Проверка, что бирка не из архивной даты
	 */
	function checkNotArchive($data) {
		$archive_database_date = $this->config->item('archive_database_date');
		$this->load->helper('Reg');
		switch ($data['object'])
		{
			case 'TimetableGraf':
				$sql = "
					select
						TimetableGraf_Day as Day
					from
						v_TimetableGraf_lite ttg with (nolock)
					where
						ttg.TimetableGraf_id = :TimetableGraf_id
				";
				$res = $this->db->query(
					$sql,
					array(
						'TimetableGraf_id' => $data['TimetableGraf_id']
					)
				);
				if ( is_object($res) ) {
					$resp = $res->result('array');
					if ( !empty($resp[0]['Day']) && DayMinuteToTime( $resp[0]['Day'], 0) < strtotime($archive_database_date) ) {
						return false;
					}
				}
				break;
			case 'TimetableMedService':
				$sql = "
					select
						TimetableMedService_Day as Day
					from
						v_TimetableMedService_lite ttms with (nolock)
					where
						ttms.TimetableMedService_id = :TimetableMedService_id
				";
				$res = $this->db->query(
					$sql,
					array(
						'TimetableMedService_id' => $data['TimetableMedService_id'],
					)
				);
				if ( is_object($res) ) {
					$resp = $res->result('array');
					if ( !empty($resp[0]['Day']) && DayMinuteToTime( $resp[0]['Day'], 0) < strtotime($archive_database_date) ) {
						return false;
					}
				}
				break;
			case 'TimetableStac':
				$sql = "
					select
						TimetableStac_Day as Day
					from
						v_TimetableStac_lite tts with (nolock)
					where
						tts.TimetableStac_id = :TimetableStac_id
				";
				$res = $this->db->query(
					$sql,
					array(
						'TimetableStac_id' => $data['TimetableStac_id'],
					)
				);
				if ( is_object($res) ) {
					$resp = $res->result('array');
					if ( !empty($resp[0]['Day']) && DayMinuteToTime( $resp[0]['Day'], 0) < strtotime($archive_database_date) ) {
						return false;
					}
				}
				break;
		}

		return true;
	}

	/**
	 * Проверка что человек не умер
	 */
	function checkPersonIsDeath($data) {

		$person = $this->getFirstResultFromQuery(
		"
			select top 1
				ps.Person_id
			from v_PersonState ps (nolock)
			where ps.Person_id = :Person_id
				and (ps.Person_IsDead = 2 or ps.Person_deadDT is not null)
		", array('Person_id' => $data['Person_id']));

		return !empty($person) ? true : false;
	}

	/**
	 * Проверка, чтобы у человека уже не было записи на то же время
	 */
	function checkAlreadyHasRecordOnThisTime($data) {
		switch ($data['object'])
		{
			case 'TimetableGraf':
				$sql = "
					select
							ttg.TimetableGraf_id,
							l.Lpu_Nick,
							rtrim(msf.Person_Surname) + ' ' + msf.Person_Firname + isnull(' ' + msf.Person_Secname, '') as MedPersonal_FIO
						from v_TimetableGraf_lite ttg with (nolock)
						inner join v_Medstafffact msf with (nolock) on ttg.MedStaffFact_id = msf.MedStaffFact_id
						left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
						left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
						left join v_Lpu l with (nolock) on l.Lpu_id = lu.Lpu_id
						where
							ttg.Person_id = :Person_id
							and ttg.TimetableGraf_begtime = (select TimetableGraf_begTime from v_TimetableGraf_lite ttg1 with (nolock) where ttg1.TimetableGraf_id = :TimetableGraf_id)
						order by ttg.TimetableGraf_begtime desc
				";
				$res = $this->db->query(
					$sql,
					array(
						'Person_id' => $data['Person_id'],
						'TimetableGraf_id' => $data['TimetableGraf_id'],
					)
				);
				if ( is_object($res) ) {
					$res = $res->result('array');
					if ( isset($res[0]["TimetableGraf_id"]) ) {
						return array(
							'info' => 'МО: '.$res[0]['Lpu_Nick'].', врач: '.$res[0]['MedPersonal_FIO']
						);
					}
				}
				break;
			case 'TimetableMedService': 
				$sql = "
					select
							ttms.TimetableMedService_id,
							l.Lpu_Nick,
							MedService_Name as MedService_Name
						from v_TimetableMedService_lite ttms with (nolock)
						left join v_Person_ER p with (nolock) on ttms.Person_id = p.Person_id
						left join v_MedService ms with (nolock) on ttms.MedService_id = ms.MedService_id
						left join v_Lpu l with (nolock) on l.Lpu_id = ms.Lpu_id
						where
							ttms.Person_id = :Person_id
							and ttms.TimetableMedService_begtime = (select TimetableMedService_begTime from v_TimetableMedService_lite ttms1 with (nolock) where ttms1.TimetableMedService_id = :TimetableMedService_id)
						order by ttms.TimetableMedService_begtime desc
				";
				$res = $this->db->query(
					$sql,
					array(
						'Person_id' => $data['Person_id'],
						'TimetableMedService_id' => $data['TimetableMedService_id'],
					)
				);
				if ( is_object($res) ) {
					$res = $res->result('array');
					if ( isset($res[0]["TimetableMedService_id"]) ) {
						return array(
							'info' => 'МО: '.$res[0]['Lpu_Nick'].', служба: '.$res[0]['MedService_Name']
						);
					}
				}
				break;
			case 'TimetableStac': 
				//TO-DO
				break;
		}

		return true;
	}
	
	/**
	 * Проверка на дополнительные предупреждения при записи в поликлинику
	 */
	function checkWarningsTTG($data) {
		$warnings = array();
		
		$ttg_data = $this->getRecordTTGData($data);

		$params = array(
			'Person_id' => $data['Person_id']
		);

		$filter = "";
		// по тому же направлению не нужно выдавать предупреждений, чтобы сразу выполнялась перезапись.
		if (!empty($data['EvnDirection_id'])) {
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$filter .= " and ttg.EvnDirection_id != :EvnDirection_id";
		}

		// А также информацию о всех существующих записях на человека
		$sql = "
			select
				l.Lpu_id,
				ttg.TimetableGraf_Day,
				msf.MedSpecOms_id,
				lsp.LpuSectionProfile_id as Profile_id,
				l.Lpu_Nick,
				rtrim(msf.Person_Surname)+' '+left(msf.Person_Firname,1)+'. '+left(msf.Person_Secname,1)+'.' as MedPersonal_FIO
			from v_TimetableGraf_lite ttg with (nolock)
			inner join v_Medstafffact msf with (nolock) on ttg.MedStaffFact_id = msf.MedStaffFact_id
			left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
			left join v_LpuSectionProfile lsp with (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
			left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
			left join v_Lpu l with (nolock) on l.Lpu_id = lu.Lpu_id
			where
				ttg.Person_id = :Person_id
				{$filter}
			order by ttg.TimetableGraf_begtime desc
		";
		$res = $this->db->query(
			$sql,
			$params
		);
		if ( is_object($res) ) {
			$res = $res->result('array');

			// Предупреждение о записи одного человека на один день в одну МО, на один профиль.
			foreach ($res as $old_record) {
				if (
					$data['session']['region']['nick'] == 'ekb' &&
					$old_record['Lpu_id'] == $ttg_data['Lpu_id'] &&
					$old_record['MedSpecOms_id'] == $ttg_data['MedSpecOms_id'] &&
					$old_record['TimetableGraf_Day'] == $ttg_data['TimetableGraf_Day']
				) {
					$warnings[] = 'Уже есть запись пациента на этот день по той же специальности в МО: '.$old_record['Lpu_Nick'].', врач: '.$old_record['MedPersonal_FIO'];
				}
				else if (
					$data['session']['region']['nick'] != 'ekb' &&
					$old_record['Lpu_id'] == $ttg_data['Lpu_id'] &&
					$old_record['Profile_id'] == $ttg_data['LpuSectionProfile_id'] &&
					$old_record['TimetableGraf_Day'] == $ttg_data['TimetableGraf_Day']
				) {
					$warnings[] = 'Уже есть запись пациента на этот день по этому профилю в МО: '.$old_record['Lpu_Nick'].', врач: '.$old_record['MedPersonal_FIO'];
				}
			}
		}
		
		return $warnings;
	}
	
	
	/**
	 * Получение данных по бирке поликлиники
	 */
	function getRecordTTGData($data) {
		if ( !isset($data['TimetableGraf_id'])) {
			return false;
		}
		if ( empty($this->TTGData) || $this->TTGData['TimetableGraf_id'] != $data['TimetableGraf_id']) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "select
						ttg.TimetableGraf_id,
						ttg.TimetableGraf_Day,
						DATEDIFF(day,dbo.tzGetdate(),ttg.TimetableGraf_begTime) as DateDiff,
						l.Lpu_id,
						l.Org_id,
						ls.LpuSectionProfile_id,
						ls.LpuSection_id,
						ls.LpuUnit_id,
						msf.MedStaffFact_id,
						msf.MedPersonal_id,
						msf.MedSpecOms_id,
						msf.MedStaffFact_IsDirRec,
						msf.RecType_id,
						ttg.TimetableType_id,
						ttg.TimeTableGraf_PersRecLim,
						ttg. TimeTableGraf_countRec,
						coalesce(ttg.TimetableGraf_begtime, ttg.TimetableGraf_facttime) as Timetable_Date,
						ttg.TimetableGraf_facttime,
						ttg.TimetableGraf_begTime,
						ttg.Person_id,
						ev.EvnVizit_id
					from v_TimetableGraf_lite ttg with (nolock)
					left join v_Medstafffact msf with (nolock) on ttg.MedStaffFact_id = msf.MedStaffFact_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = msf.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
					left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_Lpu l with (nolock) on l.Lpu_id = lu.Lpu_id
					left join v_EvnVizit ev with (nolock) on ev.TimetableGraf_id = ttg.TimetableGraf_id
					where
						ttg.TimetableGraf_id = :TimetableGraf_id
					order by ttg.TimetableGraf_begtime desc";
			$res = $this->db->query(
				$sql,
				array(
					'TimetableGraf_id' => $data['TimetableGraf_id']
				)
			);
			$res = $res->result('array');
			if ( isset($res[0]) ) {
				$this->TTGData = $res[0];
			} else {
				return false;
			}
		}

		return $this->TTGData;
	}


	/**
	 * Получение данных по бирке
	 */
	function getTimetableData($data) {

		$object = $data['object'];
		$join = ""; $select = "";

		switch ($object) {
			case 'TimetableGraf':
			case 'TimetableMedService':
			case 'TimetableResource':
				break;
		}

		$query = "
			select top 1
				tt.{$object}_id,
				tt.{$object}_begTime,
				p.Person_Fio,
				convert(varchar,p.Person_BirthDay,104) as Person_BirthDay,
				ed.EvnDirection_TalonCode,
				case when tt.Person_id is not null then
					case
						when pu.pmUser_id is not null then rtrim(pu.pmUser_Name)
						else 'Запись через интернет'
					end
				end as pmUser_Name
				{$select}
			from {$object} as tt with (nolock)
			left join v_EvnDirection_all ed with (nolock) on ed.{$object}_id = tt.{$object}_id
			left join v_pmUser pu with (nolock) on pu.pmUser_id = tt.pmUser_updId
			outer apply(
					select top 1 Person_Fio, Person_BirthDay from v_Person_all with(nolock) where Person_id = tt.Person_id
			) as p
			{$join}
			where tt.{$object}_id = :{$object}_id
		";

		$resp = $this->queryResult($query, array("{$object}_id" => $data["{$object}_id"]));
		if (!empty($resp[0])) {

			$resp = $resp[0];

			if (!empty($resp["{$object}_begTime"]) && $resp["{$object}_begTime"] instanceof DateTime) {
				$resp["{$object}_begTime"] = $resp["{$object}_begTime"]->format('d.m.Y H:i:s');
			}
		}

		return $resp;
	}
	
	/**
	 * Получение данных по бирке стационара
	 */
	function getRecordTTSData($data) {
		if ( !isset($data['TimetableStac_id'])) {
			return false;
		}
		if ( empty($this->TTSData) || $this->TTSData['TimetableStac_id'] != $data['TimetableStac_id']) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "select
						tts.TimetableStac_id,
						tts.TimetableStac_Day,
						l.Lpu_id,
						ls.LpuSectionProfile_id,
						tts.TimetableType_id,
						cast(convert(varchar, tts.TimetableStac_setDate, 112) + :time as datetime)  as Timetable_Date,
						tts.Person_id,
						l.Org_id,
						lu.LpuUnit_id,
						tts.EvnDirection_id
					from v_TimetableStac_lite tts with (nolock)
					left join v_Person_ER p with (nolock) on tts.Person_id = p.Person_id
					left join v_LpuSection ls with (nolock) on ls.LpuSection_id = tts.LpuSection_id
					left join v_LpuSectionProfile lsp with (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id
					left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = ls.LpuUnit_id
					left join v_Lpu l with (nolock) on l.Lpu_id = lu.Lpu_id
					left join Address with (nolock) on Address.Address_id = lu.Address_id
					left join KLStreet with (nolock) on KLStreet.KLStreet_id = Address.KLStreet_id
					where
						tts.TimetableStac_id = :TimetableStac_id
					order by tts.TimetableStac_setDate desc";
			$res = $this->db->query(
				$sql,
				array(
					'TimetableStac_id' => $data['TimetableStac_id'],
					'time' => ' 23:59:59.000'
				)
			);
			$res = $res->result('array');
			if ( isset($res[0]) ) {
				$this->TTSData = $res[0];
			} else {
				return false;
			}
		}
		return $this->TTSData;
	}
	
	/**
	 * Получение данных по бирке службы
	 */
	function getRecordTTMSData($data) {
		if ( !isset($data['TimetableMedService_id'])) {
			return false;
		}
		if ( empty($this->TTMSData) || $this->TTMSData['TimetableMedService_id'] != $data['TimetableMedService_id']) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "select
						ttms.TimetableMedService_id,
						ttms.TimetableMedService_Day,
						l.Lpu_id,
						ttms.TimetableType_id,
						convert(varchar(10), ttms.TimetableMedService_begTime, 104) + ' ' + 
						convert(varchar(5), ttms.TimetableMedService_begTime, 108) as TimetableMedService_DT,
						ttms.TimetableMedService_begTime as Timetable_Date,
						ttms.Person_id,
						l.Org_id,
						ttms.TimetableMedService_facttime,
						ms.LpuUnit_id,
						ms.MedService_id,
						ms.MedService_Name,
						mst.MedServiceType_SysNick,
						ucms.UslugaComplex_id
					from v_TimetableMedService_lite ttms with (nolock)
					left join UslugaComplexMedService ucms with (nolock) on ucms.UslugaComplexMedService_id = ttms.UslugaComplexMedService_id
					left join v_Person_ER p with (nolock) on ttms.Person_id = p.Person_id
					left join v_MedService ms with (nolock) on ms.MedService_id = ISNULL(ucms.MedService_id, ttms.MedService_id)
					left join v_MedServiceType mst with (nolock) on ms.MedServiceType_id = mst.MedServiceType_id
					left join v_Lpu l with (nolock) on l.Lpu_id = ms.Lpu_id
					where
						ttms.TimetableMedService_id = :TimetableMedService_id
					order by ttms.TimetableMedService_begTime desc";

			$res = $this->db->query(
				$sql,
				array(
					'TimetableMedService_id' => $data['TimetableMedService_id']
				)
			);
			$res = $res->result('array');
			if ( isset($res[0]) ) {
				$this->TTMSData = $res[0];
			} else {
				return false;
			}
		}
		
		return $this->TTMSData;
	}
	/**
	 * Получение данных по бирке службы
	 */
	function getRecordTTMSOData($data) {
		if ( !isset($data['TimetableMedServiceOrg_id'])) {
			return false;
		}
		if ( empty($this->TTMSOData) || $this->TTMSOData['TimetableMedServiceOrg_id'] != $data['TimetableMedServiceOrg_id']) {
			// Получаем информацию о бирке, куда записываемся
			$sql = "select
						ttms.TimetableMedServiceOrg_id,
						ttms.TimetableMedServiceOrg_Day,
						l.Lpu_id,
						ttms.TimetableMedServiceOrg_begTime as Timetable_Date,
						ttms.TimetableMedServiceOrg_Time,
						ttms.MedService_id,
						ttms.Org_id,
						ms.LpuUnit_id
					from TimetableMedServiceOrg ttms with (nolock)
					left join v_Org p with (nolock) on ttms.Org_id = p.Org_id
					left join v_MedService ms with (nolock) on ms.MedService_id = ttms.MedService_id
					left join v_Lpu l with (nolock) on l.Lpu_id = ms.Lpu_id
					where
						ttms.TimetableMedServiceOrg_id = :TimetableMedServiceOrg_id
					order by ttms.TimetableMedServiceOrg_begTime desc";

			//echo getDebugSQL($sql,array('TimetableMedServiceOrg_id' => $data['TimetableMedServiceOrg_id']));die;
			$res = $this->db->query(
				$sql,
				array(
					'TimetableMedServiceOrg_id' => $data['TimetableMedServiceOrg_id']
				)
			);
			$res = $res->result('array');
			if ( isset($res[0]) ) {
				$this->TTMSOData = $res[0];
			} else {
				return false;
			}
		}
		
		return $this->TTMSOData;
	}
	/**
	 * Получение данных по бирке ресурса
	 */
	function getRecordTTRData($data) {
		if ( !isset($data['TimetableResource_id'])) {
			return false;
		}
		if ( empty($this->TTRData) || $this->TTRData['TimetableResource_id'] != $data['TimetableResource_id']) {

			// Получаем информацию о бирке, куда записываемся
			$sql = "select
						ttms.TimetableResource_id,
						ttms.TimetableResource_Day,
						l.Lpu_id,
						ttms.TimetableType_id,
						ttms.TimetableResource_begTime as Timetable_Date,
						ttms.Person_id,
						l.Org_id,
						ms.LpuUnit_id,
						ms.MedService_id,
						ttms.Resource_id
					from v_TimetableResource_lite ttms with (nolock)
						inner join [Resource] r with (nolock) on r.Resource_id = ttms.Resource_id
						left join v_MedService ms with (nolock) on ms.MedService_id = r.MedService_id
						left join v_Lpu l with (nolock) on l.Lpu_id = ms.Lpu_id
					where
						ttms.TimetableResource_id = :TimetableResource_id
					order by ttms.TimetableResource_begTime desc";

			// NGS: Могут проходить запросы на бирку ($data['TimetableResource_id']), которая не существует (была удалена после предварительной проверки бирки),
			// в этом случае вернется FALSE
			$res = $this->db->query(
				$sql,
				array(
					'TimetableResource_id' => $data['TimetableResource_id']
				)
			);
			$res = $res->result('array');
			if ( isset($res[0]) ) {
				$this->TTRData = $res[0];
			} else {
				return false;
			}
		}
		
		return $this->TTRData;
	}

	/**
	 * Получение данных по бирке, автоматически определяем тип бирки
	 */
	function getRecordData($data) {
		switch ($data['object'])
		{
			case 'TimetableGraf':
				return $this->getRecordTTGData($data);
			break;
			case 'TimetableStac':
				return $this->getRecordTTSData($data);
			break;
			case 'TimetableResource':
				return $this->getRecordTTRData($data);
			break;
			case 'TimetableMedService':
				return $this->getRecordTTMSData($data);
			break;
			case 'TimetableMedServiceOrg':
				return $this->getRecordTTMSOData($data);
			break;
		}
	}

	/**
	 * Проверка, что на данную поликлиническую бирку в чужую МО разрешена запись
	 */
	function checkRecordTTGOtherLpu($data) {

		$ttg_data = $this->getRecordTTGData($data);

		$current_day = new Datetime(date('Y-m-d'));
		$day_diff = $current_day->diff($ttg_data['Timetable_Date'])->days;

		return
			(
				(in_array($ttg_data['TimetableType_id'], array(1, 5))) || 
				(in_array($ttg_data['TimetableType_id'], array(4)) && IsCZUser())
			) &&
			(
				($day_diff > 1) || //запись на послезавтра или в пределах ближайших max_day дней
				($day_diff == 1 && date("H:i") < getCloseNextDayRecordTime()) //запись на завтра, но до getCloseNextDayRecordTime() часов
			);
	}

	/**
	 * Проверка времени записи перед блокировкой бирки
	 *
	 * Если добавляемое назначение имеет разницу по времени менее 15 минут
	 * бирки с каким-либо уже имеющимся в списке справа,
	 * выдавать предупреждение "Существует назначение, близкое по времени записи к создаваемому."
	 */
	function checkBeforeLock($data) {
		$sql = "
		declare
			@TimetableMedService_id bigint = :TimetableMedService_id,
			@Person_id bigint = :Person_id,
			@pmUser_id bigint = :pmUser_id,
			@day bigint,
			@setTime datetime,
			@begTime datetime,
			@endTime datetime;

		select
			@setTime = TimetableMedService_begTime,
			@day = TimetableMedService_Day
			from v_TimetableMedService_lite with (nolock)
			where TimetableMedService_id = @TimetableMedService_id

		set @begTime = DATEADD(minute, -14, @setTime);
		set @endTime = DATEADD(minute, 14, @setTime);

		select ttms.TimetableMedService_id
			from v_TimetableMedService_lite ttms with (nolock)
			where ttms.TimetableMedService_Day = @day
			and ttms.TimetableMedService_begTime between @begTime and @endTime
			and ttms.Person_id = @Person_id
		";
		$queryParams = array(
			'TimetableMedService_id' => $data['TimetableMedService_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		//echo getDebugSQL($sql, $queryParams);exit();
		$res = $this->db->query($sql, $queryParams);
		$response = array(array('Error_Msg'=>null,'Error_Code'=>null));
		if ( is_object($res) ) {
			$res = $res->result('array');
			if (count($res) > 0) {
				$response[0]['Alert_Msg'] = 'Существует назначение, близкое по времени записи к создаваемому';
			}
			return $response;
		} else {
			$response[0]['Error_Msg'] = 'Ошибка запроса к БД';
			$response[0]['Error_Code'] = 500;
			return $response;
		}
	}
	
	/**
	 * Снимает временную блокировку с бирок заблокированных переданным пользователем
	 */
	function unlockByUser($data)
	{
		$queryParams = array(
			'pmUser_id' => $data['pmUser_id']
		);
		
		if ( ($resp = $this->execCommonSP("p_TimetableLock_unblockByUser", $queryParams))) {
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Снимает временную блокировку с бирок заблокированных переданным пользователем
	 */
	function getApplyDataForApi($data)
	{
		$query = "
			select top 1
				ttg.TimetableGraf_id,
				ttg.MedStaffFact_id,
				ttg.TimetableGraf_begTime as time,
				cast(ttg.TimetableGraf_begTime as date) as EvnDirection_setDate,
                msf.Lpu_id as Lpu_did,
				msf.LpuSectionProfile_id,
				msf.LpuUnit_id as LpuUnit_did,
				msf.LpuSection_id as LpuSection_did,
				msf.MedPersonal_id as MedPersonal_did
			from v_TimetableGraf_lite ttg with (nolock)
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
			where TimetableGraf_id = :TimetableGraf_id
			"
		;

		$res = $this->queryResult($query, $data);

		if (!empty($res) && !empty($res[0])) return $res[0];
		else return array();
	}

	/**
	 * запись на бирку универсальная для апи
	 * перенес доп. логику из контролллера в единую модель
	 */
	function applyForApi($data)
	{
		$this->load->helper('Reg');

		$data['Day'] = TimeToDay(time()) ;
		$data['object'] = $data['tt']; //$data['TimetableObject_id'] = 3;

		// Проверка наличия блокирующего примечания
		$this->load->model("Annotation_model", "anmodel");
		$anncheck = $this->anmodel->checkBlockAnnotation($data);

		if (is_array($anncheck) && count($anncheck))
			throw new Exception("Запись на бирку невозможна. См. примечание.", 6);

		$this->dbmodel->beginTransaction();

		try {

			$apply_result = $this->Apply($data);

			if (isset($apply_result[0])) {
				$apply_result = $apply_result[0];
			}

			if (isset($apply_result['success']) && $apply_result['success']) {

				$data['EvnDirection_id'] = $apply_result['EvnDirection_id'];

				$resp = array(
					'object' => $apply_result['object'],
					'id' => $apply_result['id'],
					'EvnDirection_id' => $apply_result['EvnDirection_id'],
					'EvnDirection_TalonCode' => !empty($apply_result['EvnDirection_TalonCode']) ? $apply_result['EvnDirection_TalonCode'] : null
				);

				// сохраняем заказ, если есть необходимость
				if (empty($data['redirectEvnDirection'])) {

					$this->load->model('EvnUsluga_model', 'eumodel');

					try { $this->eumodel->saveUslugaOrder($data); }
					catch (Exception $e) { throw new Exception(toUTF($e->getMessage(), 6)); }
				}

				if ($data['object'] == 'TimetableResource') {

					$this->load->model('Resource_model', 'resmodel');

					// Отправка данных направлений с типом функциональная диагностика в сторонние сервисы
					$this->resmodel->transferDirection($data, $resp);
				}

				$this->dbmodel->commitTransaction();

			} elseif (!empty($apply_result['queue'])) {

				array_walk($apply_result['queue'], 'ConvertFromWin1251ToUTF8');
				throw new Exception(array($apply_result['queue']), 6);

			} elseif (!empty($apply_result['warning'])) {
				throw new Exception(toUTF($apply_result['warning']), 777);
			} elseif (!empty($apply_result['alreadyHasRecordOnThisTime'])) {
				throw new Exception(toUTF($apply_result['alreadyHasRecordOnThisTime']), 6);
			} else {
				throw new Exception(toUTF($apply_result['Error_Msg']), 6);
			}

		} catch(Exception $e) {

			$this->dbmodel->rollbackTransaction();
			throw new Exception($e->getMessage(), $e->getCode());
		}

		return $resp;
	}
	/**
	 * Отмена направления из групповой бирки
	 */
	function cancelTimetableGrafRecList($data) {

		$queryParams = array(
			'TimetableGrafRecList_id' => $data['TimetableGrafRecList_id'],
			'pmUser_id' => $data['pmUser_id'],
		);
		$cancelProc = "p_TimeTableGrafRecList_cancel";
		$resp = $this->execCommonSP($cancelProc, $queryParams);
		// если указан статус очереди, то при отмене шлем пуш и емэйл для пользователя и оповещение в портал
		return $resp;
	}

	/**
	 * запись на бирку универсальная для апи
	 * перенес доп. логику из контролллера в единую модель
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	function ApplyPortal($data) {
		$result = null;
		$tt_data = null;
		$data["object"] = "TimetableGraf";
		$this->load->helper("Reg");
		$timetable = $this->getRecordTTGData($data);
		$StandardValidation = $this->StandardApplyValidations($timetable, $data);
		if ($StandardValidation['success'] == false) {
			return $StandardValidation;
		}

		$BegTimeApplyValidations = $this->BegTimeApplyValidations($timetable, $data);
		if ($BegTimeApplyValidations['success'] == false) {
			return $BegTimeApplyValidations;
		}
		$this->load->model('Person_model');
		$patient = $this->Person_model->getPersonMain($data);
		$ApplyValidateRegional = $this->ApplyValidateRegional(array(
			'patient' => $patient,
			'data' => $data,
			'object' => $timetable
		));
		if ($ApplyValidateRegional['success'] == false) {
			return $ApplyValidateRegional;
		}
		$recordData = array(
			'Person_id' => $data['Person_id'],
			'TimetableGraf_id' => $data['TimetableGraf_id'],
			'pmuser_id' => $data['pmuser_id'],
			'isElectronicQueue' => false,
			'autoModeration' => true,
			'record_platform' => 1
		);

		$setResult = $this->setRecord($recordData); // занимаем бирку

		if (!empty($setResult)) {
			if (!empty($setResult['Error_Code'])) {
				$result['success'] = false;
				$result['error'] = !empty($setResult['Error_Msg']) ? $setResult['Error_Msg'] : 'Ошибка записи на бирку (код ' . $setResult['Error_Msg'] . ')';
				return $result;
			} else {
				$result['success'] = true;
				return $result;
			}
		}
	}

	/**
	 * Разделенные проверки времени записи
	 */
	function BegTimeApplyValidations($timetable, $data)
	{
		$result = array();
		$result['success'] = false;
		$result['error'] = array();
		$this->load->helper("Reg");

		$now = new DateTime();
		$max_day = $this->getRecordDayCount();

		// разрешенные типы пользователей бирок

		// 8 - интернет пользователи
		// 9 - пользователи инфомата
		// 4 - регистраторы своей МО

		$ttg_allowed_types = array(1,2,3,8,9);

		// если есть полномочия регистратора, добавляем их
		if (!empty($data['registerPermissionEnabled'])) {
			$ttg_allowed_types[] = 4;
		}

		if(is_object($timetable["TimetableGraf_begTime"])) {
			$timetable["TimetableGraf_begTime"] = $timetable["TimetableGraf_begTime"]->format("d.m.Y H:i:s");
		}

		$time = date('H:i', strtotime($timetable["TimetableGraf_begTime"]));// время
		// тип бирки (обычная, резервная, платная)
		loadLibrary('TTimetableTypes');
		$TimetableType = TTimetableTypes::instance()->getTimetableType($timetable['TimetableType_id']);
		//95. если айди персоны пуст (дубликат, нет?)
		if ($timetable['Person_id'] == "") {
			/*
			96. и если запись на этот тип бирки разрешен для
			Для врачей своей ЛПУ
			Запись через инфомат
			(для АРМ Холла) Регистратор своей МО
			*/
			if (in_array($timetable['TimetableType_id'], $ttg_allowed_types)) {
				/*
				97. и если тип записи к врачу НЕ:
				Только через регистратуру ЛПУ
				Прием по "живой очереди"
				Врачи ведущие только платный приём
				Без записи
				8 уже давно выпилили из базы
				*/
				if (!in_array($timetable['RecType_id'], array(2, 3, 5, 6, 8))) {
					$err = '';

					if ($timetable['DateDiff'] > $max_day) {
						$err .= "Запись на выбранный день еще не открыта. Она откроется в ближайшее время.";
					}

					if ($timetable['DateDiff'] == 1 && $now->format('H:i') > getCloseNextDayRecordTime()) {
						$err .= "Запись на завтра уже закрыта. Запишитесь на другой день.";
					}

					if ($timetable['DateDiff'] == $max_day && $now->format('H:i') < getShowNewDayTime()) {
						$err .= "Запись на выбранный день еще не открыта. Попробуйте записаться позднее.";
					}

					if (
						!empty($data['allow_record_today']) &&
						(
						($timetable['DateDiff'] == 0 && date('H:i', strtotime('+15 minutes')) > $time)
						)
					) {
						$err .= "До времени приема осталось меньше 15 минут. Запишитесь на более позднее время.";
					}
				} else {
					$recType_Name = $this->GetRecTypeMethod($timetable);
					$FirstlowerCase = mb_strtolower(mb_substr($recType_Name, 0, 1));
					$result['success'] = false;
					$result['error'] = 'Тип записи к данному врачу: ' .$FirstlowerCase.mb_substr($recType_Name, 1);
					return $result;
				}
			} else {
				$TimetableTypeName = $TimetableType->name;
				$FirstlowerCase = mb_strtolower(mb_substr($TimetableTypeName, 0, 1));
				$result['success'] = false;
				$result['error'] = 'Тип записи на данную бирку: .'.$FirstlowerCase.mb_substr($TimetableTypeName, 1);
				return $result;
			}
		} else {
			$result['success'] = false;
			$result['error'] = 'Данная бирка уже занята. Пожалуйста, попробуйте записаться на другое время.';
			return $result;
		}

		if (!empty($err)) {
			$result['success'] = false;
			$result['error'] = $err;
		} else {
			$result['success'] = true;
		}
		return $result;
	}

	/**
	 * Основные проверки при записи на бирку
	 */
	function StandardApplyValidations($timetable, $data) {
		//return $timetable;
		//return $data;
		$result = array();
		$result['success'] = false;
		$result['error'] = array();
		if (empty($timetable)) { // бирка не существует
			$result['error'] = "Время не найдено. Выберите другое время.";
			return $result;
		}
		if (!empty($timetable['Person_id'])) { // на эту бирку уже записались
			$result['error'] = "Время уже занято. Выберите другое время.";
			return $result;
		}
		$this->load->model("Annotation_model");
		// комментарий блокирует бирку
		$blockedAnnotation = $this->Annotation_model->checkBlockAnnotation($data);

		// комментарий блокирует бирку
		if (!empty($blockedAnnotation)) {
			$result['error'] = "Запись на выбранную бирку невозможна. Причина:" . '<br> ' . $blockedAnnotation[0]['Annotation_Comment'];
			return $result;
		}

		$result['success'] = true;

		return $result;
	}

	/**
	 * Комплекс региональных проверок при записи на прием
	 */
	function ApplyValidateRegional($data) {
		$patient = $data['patient'];
		$object = $data['object'];
		$result = array();
		$result['success'] = false;
		$result['error'] = "Запись не прошла региональную проверку";
		//Запись не прошла региональную проверку
		$flag_only_false = true; // Все проверки отключены, значит можно записаться

		// Модель для работы с пациентом
		$this->load->model('Person_model');

		$restriction_configs = json_decode($data['data']['restriction_configs'], true);

		//Если нет конфигов совсем, значит и ограничений никаких нет, расходимся
		if(!isset($restriction_configs) || !$restriction_configs)
		{
			$result['success'] = true;
			$result['error'] = null;
			return $result;
		}
		$data['patient']['attach_type'] = 1;
		$patient['attach'] = $this->Person_model->getPersonAttach($data['patient']);

		foreach($restriction_configs['restriction_configs'] as $restriction){
			if(empty($restriction['value']))
				continue;

			$flag_only_false = false; // Если все проверки отключены, до этого места не доберутся

			switch($restriction['code']){
				case 'attaching_point':
					if(isset($restriction['profiles']['value'])
						&& $restriction['profiles']['value']
						&& is_array($restriction['profiles']['profiles_array'])
						&& count($restriction['profiles']['profiles_array']) > 0)
					{
						// 1.1 пункт
						// Проверка на профили, к которым разрешена запись независимо от прикрепления
						if(in_array($object['LpuSectionProfile_id'],$restriction['profiles']['profiles_array'])){
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
						$result['error'] = "По данному профилю необходимо обратиться в МО прикрепления";
					}
					if(isset($restriction['patient_without_attaching']['value'])){
						// 1.2 пункт
						// Проверка на запись для неприкрепленного населения
						if($restriction['patient_without_attaching']['value']
							&& !(isset($patient['attach']['Lpu_id']) && $patient['attach']['Lpu_id']))
						{
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
						elseif(!(isset($patient['attach']['Lpu_id']) && $patient['attach']['Lpu_id']))
							$result['error'] = "Запись без актуального прикрепления недоступна";
						//Запись без актуального прикрепления недоступна
					}
					if(isset($restriction['mo_without_attaching']['value'])
						&& $restriction['mo_without_attaching']['value'])
					{
						// 1.4 пункт
						// Проверка на запись в МО без прикрепленного населения
						if(!($this->isAssignNasel($object['Lpu_id']))){
							// не проставлен чекбокс "МО имеет приписное население"
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
					}

					if (isset($patient['attach']['Lpu_id']) && $patient['attach']['Lpu_id'] == $object['Lpu_id']){
						$result['success'] = true;
						$result['error'] = null;
						return $result;
					}
					else
						$result['error'] = "Запись невозможна, т.к. Вы должны быть прикреплены к данному МО";
					//Запись невозможна, т.к. Вы должны быть прикреплены к данному МО.

					break;
				case 'mo_service_area':
					// 2 пункт
					// Проверка, что выбранная больница обслуживает территорию проживания человека
					if ( isset($patient['attach']['Terr_id']) ) {
						$this->load->model('Lpu_model');
						if ($this->Lpu_model->TerrInLpuServiceTerr($patient['attach']['Terr_id'], $object['Lpu_id'])){
							$result['success'] = true;
							$result['error'] = null;
							return $result;
						}
						else
							$result['error'] = "Выбранная МО не обслуживает адрес проживания человека";
					}
					break;
				case 'banned_profiles_list':
					// 3 пункт
					// Проверка на профиль, к которому запрещена запись (безусловно)
					// Грузим названия профилей, если нет в региональных берется стандартное
					// Если профиль есть и в стандратной и в региональной, его название берется из регионального конфига
					$profiles_name = $restriction_configs['profiles_list'];

					if(in_array($object['LpuSectionProfile_id'],$restriction['profiles_array']))
					{
						$profile_name = (isset($profiles_name[$object['LpuSectionProfile_id']]))?$profiles_name[$object['LpuSectionProfile_id']]:'';
						$result['error'] = "Запись на профиль .$profiles_name. ограничена";
						//Запись на профиль $profile_name ограничена
						return $result;
					}
					break;
			}
		}
		if($flag_only_false){ // если все условия были отключены, значит ни одной проверки не работает, можно записывать
			$result['success'] = true;
			$result['error'] = null;
		}

		return $result;
	}

	/**
	 * Проверка на проставленный чекбокс в Паспорте МО (вкладка "Справочная информация")
	 * "МО имеет приписное население"
	 * @param int $lpu_id идентификатор ЛПУ
	 */
	function isAssignNasel($lpu_id){
		$isAssignNasel = false;
		$result = $this->getFirstRowFromQuery("
						select
							Lpu_id,
							PasportMO_IsAssignNasel
						from
							fed.v_PasportMO
						where
							Lpu_id = :Lpu_id
						order by PasportMO_id desc",array('Lpu_id' => $lpu_id)
		);
		// Если чекбокс "МО имеет приписное население" в паспорте МО проставлен
		if(isset($result['PasportMO_IsAssignNasel']) && $result['PasportMO_IsAssignNasel'] == 2) {
			$isAssignNasel = true;
		}
		return $isAssignNasel;
	}

	/**
	 * Получение количества дней, на сколько вперед разрешена запись
	 */
	public function getRecordDayCount() {
		$result = $this->getFirstRowFromQuery("select dbo.PolRecordDayCount() as RecordDayCount");
		if (count($result) > 0 ) {
			return $result['RecordDayCount'];
		} else {
			return false;
		}
	}


	/**
	 * Динамическая подстановка типа записи
	 */
	function GetRecTypeMethod($timetable) {
		$params = array(
			'RecType_id' => $timetable['RecType_id'],
		);

		$result = $this->getFirstRowFromQuery("
            select RecType_Name
            from v_RecType
            where RecType_id = :RecType_id
        ",$params);

		return $result;
	}

	function isLocked($data) {

		$lockedRecords = $this->getLockedRecords($data);
		return (isset($lockedRecords[$data['Timetablegraf_id']]) && ($lockedRecords[$data['record_id']] != $data['user_id']));
	}
	/**
	 * получить заблоированные бирки
	 */
	function getLockedRecords($data) {

		return $this->queryResult("
            select
                TimetableGraf_id,
                pmUser_insID
            from TimetableLock
        ");
	}
	/**
	 * Записать человека на заданное время в расписании
	 * При необходимости получить код бронирования ЭО
	 * @param object $data параметры
	 */
	public function setRecord($data) {
		$params = array(
			'Person_id' => $data['Person_id'],
			'TimeTableGraf_id' => $data['TimetableGraf_id'],
			'pmuser_id' => $data['pmuser_id'],
			'RecClass_id' => 1,
			'RecMethodType_id' => !empty($data['record_platform']) ? $data['record_platform'] : 1,
			'TimeTableGraf_isAuto' => null,
			'TimetableGrafModeration_Status' => null,
			'TimetableGraf_IsModerated' => null,
		);

		if (!empty($data['isElectronicQueue'])) {
			// для получения кода брони обязательно указывать 3
			$params['TimeTableGraf_isAuto'] = '3';
		}

		if (!empty($data['autoModeration'])) {
			$params['TimetableGrafModeration_Status'] = 3;
			$params['TimetableGraf_IsModerated'] = 1;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@EvnDirection_TalonCode VARCHAR(4),
				@ErrMessage varchar(4000);
			exec p_TimeTableGraf_ModerateRecord
				@TimeTableGraf_id = :TimeTableGraf_id,
				@EvnDirection_TalonCode = @EvnDirection_TalonCode output,
				@Person_id = :Person_id,
				@RecClass_id = :RecClass_id,
				@RecMethodType_id = :RecMethodType_id,
				@pmUser_id = :pmuser_id,
				@TimetableGrafModeration_Status = :TimetableGrafModeration_Status,
				@TimetableGraf_IsModerated = :TimetableGraf_IsModerated,
				@TimeTableGraf_isAuto = :TimeTableGraf_isAuto,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output
			select @EvnDirection_TalonCode as EvnDirection_TalonCode, @ErrCode as Error_Code, @ErrMessage as Error_Msg";

		$result = $this->queryResult($query, $params);
		if (!empty($result[0]['Error_Msg'])) {
			throw new Exception($result[0]['Error_Msg'], 500);
		}
		return $result;
	}

	/**
	 * Создание расписания для вывода на страницу записи
	 *
	 * @param int $doctor_id Идентификатор врача
	 * @param boolean $allow_record_today Разрешаем запись на сегодня или на завтра после времени закрытия записи или на понедельник после времени закрытия записи в пятницу
	 * @param object $data сюда будем писать параметры
	 */
	public function MakeTimetable($doctor_id, $allow_record_today = false, $data = NULL) {

		// Начальная дата
		$start_date = time();

		// Модель для работы с пациентом
		$this->load->model('Person_model');
		$this->load->model('MedStaffFact_model');
		$this->load->helper("Reg");

		// Врач
		$doctor = $this->MedStaffFact_model->getDoctorInfo($doctor_id);

		$block_timetable = false;

		//todo пока на платные бирки очереди не будет
//		$this->load->model('EvnQueue_model');
//
//		// ставим блокировку записи на бирки
//		$block_timetable = $this->EvnQueue_model->isTimetableBlocked(
//			(object)array(
//				'MedStaffFact_id' => $doctor_id,
//				'Lpu_id' => $doctor['Lpu_id'],
//				'LpuSectionProfile_id' => $doctor['LpuSectionProfile_id']
//			));

		// Максимальный возможный день и неделя, максимальное время, с запасом для отображения
		$max_day = $this->getRecordDayCount();
		$end_date = strtotime('+' . (floor(($max_day+16) / 16)*16) . ' days', $start_date);
		// Получаем расписание из базы
		$timetable_items = $this->getTimetable($doctor_id, $start_date, $end_date);
		$msfDayDescriptions = array();
		$msfTtgDescriptions = array();

		$this->load->model('Annotation_model');
		// Получаем комменты на расписание
		$annotations = $this->Annotation_model->getDoctorAnnotationsByPeriod(array(
			'doctor_id' => $doctor_id,
			'start_date' => $start_date,
			'end_date' => $end_date
		));

		// обрабатываем их немного
		foreach ($annotations as $annotation) {

			if (!empty($annotation['Annotation_begDate'])) {

				// если не указаны поля времени начала и окончания, значит это комменты на день
				if (empty($annotation['Annotation_begTime']) && empty($annotation['Annotation_endTime'])) {

					// группируем по дню только коммент
					if (!empty($msfDayDescriptions[$annotation['day_id']]))
						$msfDayDescriptions[$annotation['day_id']] .= '<br>'.$annotation['Annotation_Comment'];
					else
						$msfDayDescriptions[$annotation['day_id']] = $annotation['Annotation_Comment'];
				} else {
					// иначе это комменты на бирку
					$annBegTime = (!empty($annotation['Annotation_begTime'])  ?
						DateTime::createFromFormat('H:i:s', $annotation['Annotation_begTime'])->format('H:i:s') :
						DateTime::createFromFormat('Y-m-d',$annotation['Annotation_begDate'])->format('H:i:s'));
					$annEndTime = (!empty($annotation['Annotation_endTime'])  ?
						DateTime::createFromFormat('H:i:s', $annotation['Annotation_endTime'])->format('H:i:s') :
						(!empty($annotation['Annotation_endDate']) ? DateTime::createFromFormat('Y-m-d',$annotation['Annotation_endDate'])->format('H:i:s') : '00:00:00'));
					// формируем здесь адекватную дату начала в формате Y-m-d H:i:s
					// т.к. поле $annotation->Annotation_begTime хранит в себе неверную дату

					$realBegDatetime =  DateTime::createFromFormat('Y-m-d', $annotation['Annotation_begDate'])->format('Y-m-d').' '.$annBegTime;
					$annotation['realBegDatetime'] = $realBegDatetime;

					$annotation['realBegTime'] = $annBegTime;

					// формируем здесь адекватную дату окончания аннотации в формате Y-m-d H:i:s
					// т.к. поле $annotation->Annotation_endTime хранит в себе неверную дату

					if (!empty($annotation['Annotation_endDate'])) {

						$realEndDatetime =  DateTime::createFromFormat('Y-m-d', $annotation['Annotation_endDate'])->format('Y-m-d').' '.$annEndTime;
					} else {
						// бывает такое (очень редко) что дату окончания не указывают
						// тогда мы будем считать этот коммент бессрочным
						// будем продлевать дату окончания постоянно на две недели вперед от даты начала
						$realEndDatetime =  DateTime::createFromFormat('Y-m-d', $annotation['Annotation_begDate'])->add(new DateInterval('P14D'))->format('Y-m-d').' '.$annBegTime;

					}
					$annotation['realEndDatetime'] = $realEndDatetime;
					$annotation['realEndTime'] = $annEndTime;

					// группируем по дню весь объект
					$msfTtgDescriptions[$annotation['day_id']][] = $annotation;
				}
			}
		}

		// Массив для хранения расписания по дням
		$timetable_days_raw = array();
		foreach ($timetable_items as $timetable_item) {
			$timetable_days_raw[$timetable_item['TimeTableGraf_Day']][] = $timetable_item;
		}
		$days_range = range(TimeToDay($start_date), TimeToDay($end_date)-1);

		$showNewDayTime = getShowNewDayTime();
		$closeNextDayRecordTime = getCloseNextDayRecordTime();

		$this->load->library('calendar');
		$timetable_days = array_values($timetable_days_raw);

		$cur_date = new Datetime();
		// Последняя дата в расписании доступная для записи
		$fin_date =  strtotime($cur_date->format('Y-m-d').'+' . $max_day . ' days');
		foreach ($timetable_days as &$timetable_day) {
			foreach ($timetable_day as &$timetable_item) {
				$timetable_item['time'] = $timetable_item['TimetableGraf_begTime']->format('H:i');
				$timetable_item['date'] = $timetable_item['TimetableGraf_begTime']->format('Y-m-d');
				$seldate = strtotime($timetable_item['date']);

				// Читаемая дата, время
				$dt = $timetable_item['TimetableGraf_begTime'];

				$timetable_item['datetime_readable_desc'] = $this->calendar->get_day_names()[$dt->format('w')] . $dt->format(', j ') . $this->calendar->get_month_name($dt->format('m'));

				$timetable_item['datetime_readable'] = $dt->format('j ') .
					$this->calendar->get_month_name($dt->format('m')) . ', ' .
					$this->calendar->get_day_names()[$dt->format('w')] . ' ' .
					$dt->format('H:i');

				// Классы стилей для вывода ячеек расписания
				$class = '';
				$tooltip = '';

				$timetable_item['is_free'] = false;
				$timetable_item['my_record'] = false;
				$timetable_item['annot'] = false;

				// если в хранилище есть комменты на этот день
				if (!empty($msfTtgDescriptions[$timetable_item['TimeTableGraf_Day']])) {

					// то для каждой аннотации на этот день смотрим
					foreach ($msfTtgDescriptions[$timetable_item['TimeTableGraf_Day']] as $annotation) {

						// и если бирка входит в промежуток времени на коммент
						if (
							$timetable_item['TimetableGraf_begTime'] >= $annotation['realBegDatetime']
							&& $timetable_item['TimetableGraf_begTime'] <= $annotation['realEndDatetime']
							&& $timetable_item['time'].":00" >= $annotation['realBegTime']
							&& $timetable_item['time'].":00" <= $annotation['realEndTime']
						) {

							// добавляем этот коммент к бирке
							if (!empty($timetable_item['annot']))
								$timetable_item['annot'] .= '<br>'.$annotation['Annotation_Comment'];
							else
								$timetable_item['annot'] = $annotation['Annotation_Comment'];
						}
					}
				}

				$timetable_item['MedStaffFactDay_Descr'] = (!empty($msfDayDescriptions[$timetable_item['TimeTableGraf_Day']]) ? $msfDayDescriptions[$timetable_item['TimeTableGraf_Day']] : '');

				if ($seldate == $fin_date && $cur_date->format('H:i') < $showNewDayTime) {
					// Если мы рассматриваем день равный последней доступной дате для записи и время меньше времени закрытия записи, то сообщаем, что скоро можно будет записаться
					$class .= 'miss' ;
					$tooltip = "Запись будет возможна сегодня после $showNewDayTime";
				} elseif ($timetable_item['DateDiff'] > $max_day || $seldate > $fin_date) {
					// Запись возможна только на max_day дней вперед
					$class .= ' miss grayCell';
					$tooltip = "Запись возможна только на $max_day дней вперед";
				} elseif ($timetable_item['DateDiff'] == 0 && ($allow_record_today && $timetable_item['time'] <= date('H:i', strtotime('+15 minutes')))) {
					// Запись на текущее время невозможна
					$class .= ' miss';
					$tooltip = "Время истекло. Запись недоступна";
				}
				elseif (($timetable_item['DateDiff'] == 0 && !$allow_record_today) || (isset($allow_record_today) && $timetable_item['IsFuture'] == 0)) {
					// Запись на текущий день невозможна
					$class .= ' miss';
					$tooltip = "Запись на текущий день невозможна";

				}  elseif ($timetable_item['DateDiff'] == 1 && $cur_date->format('H:i') > $closeNextDayRecordTime && !$allow_record_today) {
					// Запись на следующий день возможна только до Utils::getCloseNextDayRecordTime() текущего дня
					$class .= ' miss';
					$tooltip = "Запись на следующий день возможна только до $closeNextDayRecordTime текущего дня" ;
				} elseif ($timetable_item['DateDiff'] < 0) {
					// Дата посещения прошла
					$class .= ' miss';
					$tooltip = "Дата посещения прошла";
				} elseif (!$allow_record_today && ((IsWorkDay($cur_date)
							&& $cur_date->format('H:i') > $closeNextDayRecordTime)
						|| !IsWorkDay($cur_date)
					) && $timetable_item['TimetableGraf_begTime']->format('Y-m-d') == NextWorkDay($cur_date)->format('Y-m-d'))
				{
					// Запись на понедельник возможна только до Utils::getCloseNextDayRecordTime() предыдущей пятницы
					$class .= ' miss';
					$tooltip = "Запись была возможна до $closeNextDayRecordTime предыдущего рабочего дня";
				} elseif (($timetable_item['DateDiff'] > 0 || ($allow_record_today && $timetable_item['DateDiff'] >= 0)) && $timetable_item['DateDiff'] <= $max_day) {
					if (!empty($timetable_item['Person_id']) || !empty($timetable_item['TimetableLock_lockTime'])) {
						// Бирка уже занята
						$class .= ' busy';
						$tooltip = "Время занято";
						$auth_user = null;//Auth::instance()->get_user();
						$user_id = isset($auth_user->id) ? $auth_user->id : NULL;
						if (isset($auth_user) && $timetable_item['pmUser_updID'] == $user_id && !empty($user_id)) {
							$timetable_item['my_record'] = true;
							$class .= ' reserved';
							$timetable_item['person'] = $this->Person_model->getPersonMain($timetable_item['Person_id']);
							$tooltip = "Запись:" .$timetable_item['person']['Person_FIO']."<br/>Нажмите для отмены записи";
						}
					} else {
						loadLibrary('TTimetableTypes');
						$TimetableType = $this->TimetableType = TTimetableTypes::instance()->getTimetableType($timetable_item['TimetableType_id']);
						if ($doctor['RecType_id'] == 3) {
							// Прием по живой очереди
							$class .= ' miss';
							$tooltip = "Запись невозможна, врач ведет прием в порядке очереди.";
						} elseif ($doctor['RecType_id'] == 5 || $doctor['RecType_id'] == 6 ) {
							// Время заблокировано
							$class .= ' busy';
							$tooltip = "Время заблокировано";
						} elseif ( $timetable_item['TimetableType_id'] == 2) {
							// резервные бирки
							if (!empty($data['enableReservedRecords'])) {
								$class .= ' free';
								$tooltip = null;
								$timetable_item['is_free'] = true;
							} else {
								$class .= ' busy';
								$tooltip = "Время, запись на которое осуществляется в поликлинике.";
							}
						} elseif (!$TimetableType->inSources(array(8,9))) {
							// Время, запись на которое осуществляется в поликлинике.
							$class .= ' busy';
							$tooltip = "Время, запись на которое осуществляется в поликлинике.";
						}
						elseif ( $timetable_item['TimetableType_id'] == 3 ) { // Платный прием
							if ( isset($data['allow_pay']) && $data['allow_pay'] == true ) {
								$class .= ' free';
								$tooltip = $tooltip = "Платный прием";
								$timetable_item['is_free'] = true;
							} else {
								$class .= ' busy';
								$tooltip = "Время заблокировано"."<br/>" . "Только платный прием";
							}
						}
						elseif ($block_timetable && ($timetable_item['TimetableType_id'] == 1 || $timetable_item['TimetableType_id'] == 11)) {
							// если в включена автоматическая обработка очереди
							// и есть люди находящиеся в очереди
							$class .= ' busy';
							$tooltip = "Время занято";
						} else {
							// Время свободно
							$class .= ' free';
							$tooltip = null;
							$timetable_item['is_free'] = true;
						}
					}
				}

				if ($timetable_item['annot']) { // есть примечания на бирку
					$class .= ' annot';
					$class .= ' ttg-annot-' . hash('crc32', $timetable_item['annot']); // класс для идентификации одинаковых приемечаний
					$tooltip .= (empty($tooltip) ? '' : '<br>') . htmlspecialchars($timetable_item['annot']);


					$timetable_item['MedStaffFactDay_Descr'] .= '<br>'.$timetable_item['annot'];
				}

				$timetable_item['class'] = $class;
				$timetable_item['tooltip'] = $tooltip;
			}

		}

		$days = array();
		$day_x = time();

		foreach($days_range as $day_counter) {
			$day = array();
			$day['date'] = date('Y-m-d', $day_x); // Число, месяц;
			$day['datetime'] = new DateTime($day['date']);

			$day['date_readable'] = date('j', $day_x) . ' ' . mb_substr(mb_strtolower($this->calendar->get_month_name(date('m', $day_x))), 0, 3); // Число, месяц
			$day['date_readable_full'] = date('j', $day_x) . ' ' . mb_strtolower($this->calendar->get_month_name(date('m', $day_x))) .' '. date('Y', $day_x); // Число, месяц, год
			$day['day_of_week'] = $this->calendar->get_day_names('short')[date('w', $day_x)]; // День недели
			$day['day_of_week_full'] = mb_strtolower($this->calendar->get_day_names('long')[date('w', $day_x)]); // День недели
			$day['is_today'] = (date('j F', $day_x) == date('j F')) ? true : false; // Признак сегодня
			$day['day_num'] = date('j', $day_x);
			$day['month_add'] = mb_strtolower($this->calendar->get_month_name(date('m', $day_x)));

			$day_id = TimeToDay($day_x);
			$day['id'] = $day_id;
			$day['description'] = (!empty($msfDayDescriptions[$day_id]) ? $msfDayDescriptions[$day_id] : '');
			$day_x = strtotime('+1 day', $day_x);

			$day['timetable'] = array();
			$day['timetable_is_free'] = false;
			foreach ($timetable_days as &$timetable_day) {
				foreach ($timetable_day as &$timetable_item) {
					if ($timetable_item['date'] == $day['date']) {
						$day['timetable'][] = $timetable_item;
						if ($timetable_item['is_free']) {
							$day['timetable_is_free'] = true;
						}
					}
				}
			}
			$days[] = $day;
		}
		return $days;
	}

	/**
	 * Получить расписание врача
	 *
	 * @param int $doctor_id id врача
	 * @param int $start_date timestamp начала расписания
	 * @param int $end_date окончание расписания
	 */
	public function getTimetable($doctor_id, $start_date, $end_date) {
		//todo сделать функцию day_sql для дат
		$this->load->helper("Reg");
		$params = array(
			'doctor_id' => $doctor_id,
			'TimeTableGraf_Day_Start' => TimeToDay($start_date),
			'TimeTableGraf_Day_End' => TimeToDay($end_date)
		);
		$result = $this->queryResult("
		select
			t.TimetableGraf_id,
			t.Person_id,
			t.TimeTableGraf_Day,
			t.TimetableGraf_begTime,
			t.TimetableType_id,
			t.pmUser_updID,
			case when t.TimetableGraf_begTime > DATEADD(minute, 15, dbo.tzGetDate()) then 1 else 0 end as IsFuture,
			datediff(d, dbo.tzGetDate(), t.TimetableGraf_begTime) as DateDiff,
			ttl.TimetableLock_lockTime
		from
			v_TimeTableGraf_lite t with (nolock)
		left join TimetableLock ttl with (nolock) on t.TimetableGraf_id = ttl.TimetableGraf_id
		where
			t.TimeTableGraf_Day >= :TimeTableGraf_Day_Start and
			t.TimeTableGraf_Day < :TimeTableGraf_Day_End and
			t.MedStaffFact_Id = :doctor_id and
            t.TimetableGraf_begTime is not null and
            t.TimetableType_id != 12
		order by t.TimetableGraf_begTime",$params);

		return $result;
	}

	/**
	 * Получить статистику по свободным биркам на ближайшие 2 недели
	 *
	 * @param int $doctor_id ID врача
	 * @param boolean $allow_record_today Разрешаем запись на сегодня или на завтра после времени закрытия записи или на понедельник после времени закрытия записи в пятницу
	 * @param boolean $allow_pay
	 * @param object $data все параметры сюда кладем
	 * @return stdClass
	 */
	public function getFirstFreeDateStatistics($doctor_id, $allow_record_today = false, $allow_pay = false, $data = NULL) {
		$this->load->helper("Reg");
		$first_day = day_sql(time());
		$cur_date = new Datetime();
		$max_day = $this->getRecordDayCount();;
		$last_day = $first_day + $max_day - 1;
		$this->load->library('calendar');
		$days_name = $this->calendar->get_day_names('short');

		// Есть сегодня не рабочий день или завтра не рабочий день, то находим дату окончания выходных и праздников и от нее считаем first_day
		$next_date = clone $cur_date;
		$next_date->modify('+1 day');
		if (!IsWorkDay($cur_date) || (!IsWorkDay($next_date))) {
			$work_date = NextWorkDay($cur_date);
			$first_day =  day_sql(strtotime($work_date->format('Y-m-d')));
		}
		if (date('H:i') > getCloseNextDayRecordTime()) {
			$first_day++;
		}
		if (date('H:i') > getShowNewDayTime()) {
			$last_day++;
		}

		$filter_today = '';

		// если разрешена запись на сегодня,
		if ($allow_record_today) {

			// то учитываем сегодняшний день
			$first_day--;
			// учитываем текущее время
			$filter_today = "and ttg.TimeTableGraf_begTime > DATEADD(minute, 15, dbo.tzGetDate()) ";
		}

		$type_filter = " ttg.TimeTableType_id in (1,11";
		$type_attribute_filter = " ttal.TimetableTypeAttribute_id in (8,9";

		// для дисп учета
		$ttal = "and ttal.TimetableTypeAttributeLink_id is not null";
		if (!empty($data->enableReservedRecords)) {
			$type_filter .= ",2";
			$ttal = "";
		}

		// если разрешена запись на платные бирки
		// то берем обычные свободные и платные свободные
		if ( $allow_pay ) {
			$type_filter .= ',3';
		}

		$type_filter .= ') ';
		$type_attribute_filter .= ') ';

		$groupBy = "";

		$select = "
        	min(ttg.first_free_date) as first_free_date,
			sum(ttg.total) as total,
        ";
		$params = array(
			'doctor_id' => $doctor_id['MedStaffFact_list'],
			'TimeTableGraf_Day_Start' => $first_day,
			'TimeTableGraf_Day_End' => $last_day);

		$result = $this->getFirstRowFromQuery("
			select
				{$select}
				sum(mpd.MedpersonalDay_FreeRec) as free,
				sum(mpd.MedPersonalDay_InetRec) as inet,
				sum(mpd.MedpersonalDay_ReservRec) as reserved,
				sum(mpd.MedpersonalDay_PayRec) as pay
			from MedPersonalDay mpd (nolock)
			outer apply(
				select 
					count(ttg.TimeTableGraf_id) as total,
					min(case when ttg.Person_id is null {$ttal} and {$type_filter} {$filter_today} then ttg.TimeTableGraf_begTime else null end) as first_free_date
				from v_TimetableGraf_lite ttg (nolock)
				left join TimetableTypeAttributeLink ttal (nolock) on ttal.TimetableType_id = ttg.TimetableType_id and {$type_attribute_filter}
				left join v_EvnQueue queue (nolock) on queue.EvnDirection_id = ttg.EvnDirection_id
				left join v_EvnDirection ed (nolock) on ed.EvnDirection_id = queue.EvnDirection_id
				left join v_MedStaffFact msf (nolock) on msf.MedStaffFact_id = ed.MedStaffFact_id
				where mpd.Day_id = ttg.TimetableGraf_Day 
					and mpd.MedStaffFact_id = ttg.MedStaffFact_id
					and isnull(queue.EvnQueueStatus_id, 0) != 1
			) ttg
			where mpd.MedStaffFact_id = :doctor_id
				and mpd.Day_id > :TimeTableGraf_Day_Start
				and mpd.Day_id <= :TimeTableGraf_Day_End
			{$groupBy}
		",$params);

		if (count($result) != 0) {
			$res = array();
			if ( is_array($result['first_free_date']) ) {
				$res['FirstFreeDate'] = $result['first_free_date']->format('d.m.Y') . ' ' . $days_name[$result['first_free_date']->format('w')] . ' ' . $result['first_free_date']->format('H:i');
				$res['FirstFreeFullDate'] = $result['first_free_date'];
			} else {
				$res['FirstFreeDate'] = null;
				$res['FirstFreeFullDate'] = null;
			}

			$res['Total'] = $result['total'];
			$res['Free'] = $result['free'];
			$res['Reserved'] = $result['reserved'];
			$res['Pay'] = $result['pay'];
			$res['Inet'] = $result['inet'];
			$result = $res;
		} else {
			$result = false;
		}

		return $result;
	}

	/**
	 * Отменить запись
	 *
	 * @param int $record_id id записи
	 */
	function Cancel($record_id, $patient_id = null, $data)
	{
		$user_id = 0;
		if(!empty($data['pmuser_id'])) {
			$user_id = $data['pmuser_id'];
		}

		$now = new DateTime();
		$result = array();
		$result['success'] = false;
		$result['error'] = '';
		$this->load->helper("Reg");

		$q_query = $this->getFirstRowFromQuery("
			select
				ttg.pmUser_updId,
				ttg.Person_id,
				ttg.MedStaffFact_id,
				TimeTableGraf_Day,
				TimetableGraf_id,
				TimetableGraf_begTime,
				datediff(d, dbo.tzGetDate(), TimetableGraf_begTime) as DateDiff,
				msf.Person_FIO as MedPersonal_FIO,
				ps.Person_SurName + ' ' + ps.Person_FirName as Person_FIO,
			    queue.EvnQueue_id
			from v_TimetableGraf_lite ttg with (nolock)
			left join v_MedStaffFact msf with (nolock) on msf.MedStaffFact_id = ttg.MedStaffFact_id
			left join v_PersonState ps with (nolock) on ps.Person_id = ttg.Person_id
			outer apply(
			  select top 1
			  	EvnQueue_id 
			  from v_EvnQueue q (nolock)
			  where q.TimetableGraf_id = ttg.TimetableGraf_id
			  	and q.RecMethodType_id = 1
			  	and q.EvnDirection_id = ttg.EvnDirection_id
			  	and q.Person_id = ttg.Person_id
			  	and q.EvnQueueStatus_id = 3
			) queue	
			where TimetableGraf_id = :TimetableGraf_id", array('TimetableGraf_id' => $record_id));
		if (count($q_query) < 1) {
			$result['error'] = "Время не найдено. Выберите другое время.";
		} else {
			$q = $q_query;
			if (empty($patient_id) && $q['pmUser_updId'] != $user_id
				//&& $q['Person_id'] != $data['esia_person']
				&& empty($q['EvnQueue_id'])) {
				$result['error'] = "Вы не можете освободить эту бирку, так как она занята не вами.";
			} else {
				if (!empty($patient_id) && $patient_id != $q['Person_id']) {
					$result['error'] = "Вы не можете освободить эту бирку, так как она занята не вами.";
				} elseif (empty($q['Person_id'])) {
					$result['error'] = "Время уже свободно. Выберите другое время.";
				} else {
					if ($now->format('Y-m-d H:i:s') >=  $q['TimetableGraf_begTime']->format('Y-m-d H:i:s')) {
						$result['error'] = "Время посещения уже прошло, отмена невозможна";
						return $result;
					}
					//todo подгрузить из конфига
					//$data['allow_cancel_any_time'] = true;
					if (!$data['allow_cancel_any_time']) {
						if (($now->format('H:i') >= getCloseNextDayRecordTime() && $q['DateDiff'] == 1) || $q['DateDiff'] == 0) {
							$result['error'] = "Отменять запись можно не позднее" . getCloseNextDayRecordTime() . "дня предшествующего посещению!";
							return $result;
						}
					}


					$this->cancelRecord($record_id, $user_id);
					$this->UnblockTime($record_id, $user_id);
					$result['success'] = true;
					$result['error'] = '';
					$result['dataSend'] = array(
						'Person_FIO' => $q_query['Person_FIO'],
						'MedPersonal_FIO' => $q_query['MedPersonal_FIO']
					);
				}
			}
		}
		return $result;
	}

	/**
	 * Отменить запись на конкретную бирку
	 *
	 * @param int $record_id id записи
	 * @param int $user_id Идентификатор человека, от которого была запись
	 */
	public function cancelRecord($record_id, $user_id = null, $cancelEvnDirection = true)
	{
		if ($cancelEvnDirection) {

			$evnData = $this->getFirstRowFromQuery("
			select
				EvnDirection_id
			from v_TimeTableGraf_lite with (nolock)
			where
				TimeTableGraf_id = :ttg_id
		", array('ttg_id' => $record_id));


			if (count($evnData) > 0) {
				$queryParams = array(
					':EvnDirection_id' => $evnData['EvnDirection_id'],
					':DirFailType_id' => 5,
					':EvnStatusCause_id' => 1,
					':EvnComment_Comment' => null,
					':pmUser_id' => $user_id
				);

				$this->getFirstRowFromQuery("
					declare
					@ErrCode int,
					@ErrMessage varchar(4000);
				exec p_EvnDirection_cancel
					@EvnDirection_id = :EvnDirection_id,
					@DirFailType_id = :DirFailType_id,
					@EvnStatusCause_id = :EvnStatusCause_id,
					@EvnComment_Comment = :EvnComment_Comment,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
					)", $queryParams);

			}
		}
		$params = array(
			'record_id' => $record_id,
			'user_id' => $user_id
		);

		$result = $this->queryResult("
		declare
			@ErrCode int,
			@ErrMessage varchar(4000);
		exec p_TimeTableGraf_cancel 
			@TimeTableGraf_id = :record_id,
			@pmUser_id = :user_id,
			@Error_Code = @ErrCode output,
			@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;"
			, $params);

		return $result;
	}

	/**
	 * Снимает блокировку у переданной бирки
	 *
	 * @param int $record_id id записи
	 */
	function UnblockTime($record_id, $user_id)
	{
		if (!isset($user_id)) {
			$result = $this->getFirstRowFromQuery("
			declare
				@Err_Code int,
				@Err_Msg varchar(4000);

			set nocount on;

			begin try
				delete from TimetableLock with (rowlock)
				where TimetableGraf_id = :record_id
			end try

			begin catch
				set @Err_Code = error_number();
				set @Err_Msg = error_message();
			end catch

			set nocount off;

			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;"
				, array('record_id' => $record_id));

			return $result;
		} else {
			$params = array(
				'record_id' => $record_id,
				'pmUser_id' => $user_id
			);
			$result = $this->getFirstRowFromQuery("
			declare
				@Err_Code int,
				@Err_Msg varchar(4000);

			set nocount on;

			begin try
				delete from TimetableLock with (rowlock)
				where
					pmUser_insID = :pmUser_id and
					TimetableGraf_id = :record_id
			end try

			begin catch
				set @Err_Code = error_number();
				set @Err_Msg = error_message();
			end catch

			set nocount off;

			select @Err_Code as Error_Code, @Err_Msg as Error_Msg;", $params);

			return $result;
		}
	}
}
