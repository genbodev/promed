<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Registry_model - модель для работы с таблицей Registry
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      12.11.2009
*/
require_once(APPPATH.'models/_pgsql/Registry_model.php');

class Ekb_Registry_model extends Registry_model
{
	public $scheme = "r66";
	public $region = "ekb";
	public $orgSmoList = [];
	private $_exportTimeStamp = null;
	
	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Возвращает данные случаев по реестру для отправки в РМИС
	 */
	function getRegistryToRmis($data, $mode) {
		$this->load->model('Rmis_model');

		if ($mode == 'count') {
			// получаем количество всех случаев и количество полностью выгруженных
			$query = "
			    with cte as
			    (
			        select 
			            (
			                (
                                select count(e.Evn_id)
                                from {$this->scheme}.v_RegistryData rd
                                    inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                    inner join v_Evn e on e.Evn_id = rd.Evn_id
                                where rgl.Registry_pid = :Registry_id
                                limit 1
                            ) + (
                                select  count(e.Evn_id)
                                from {$this->scheme}.v_RegistryDataEvnPS rd
                                    inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                    inner join v_Evn e on e.Evn_id = rd.Evn_id
                                where rgl.Registry_pid = :Registry_id
                                limit 1
                            ) + (
                                select count(e.Evn_id)
                                from {$this->scheme}.v_RegistryDataDisp rd
                                    inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                    inner join v_Evn e on e.Evn_id = rd.Evn_id
                                where rgl.Registry_pid = :Registry_id
                                limit 1
                            ) + (
                                select count(e.Evn_id)
                                from {$this->scheme}.v_RegistryDataProf rd
                                    inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                    inner join v_Evn e  on e.Evn_id = rd.Evn_id
                                where rgl.Registry_pid = :Registry_id
                                limit 1
                            ) + (
                                select count(e.Evn_id)
                                from {$this->scheme}.v_RegistryDataPar rd
                                    inner join {$this->scheme}.v_RegistryGroupLink rgl  on rgl.Registry_id = rd.Registry_id
                                    inner join v_Evn e on e.Evn_id = rd.Evn_id
                                where rgl.Registry_pid = :Registry_id
                            )
                        ) as EvnCount
                ),
                cte2(
                    select 
                    (
                        (
                            select count(osl.ObjectSynchronLog_id)
                            from {$this->scheme}.v_RegistryData rd
                                inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                inner join v_Evn e on e.Evn_id = rd.Evn_id
                                left join lateral (
                                    select ObjectSynchronLog_id, Object_Name as SyncStatus
                                    from v_ObjectSynchronLog
                                    where ObjectSynchronLogService_id = 1 and Object_id = e.Evn_id
                                    and Object_Name in ('SyncedEvn','SyncedEvnFail')
                                    order by Object_setDT desc
                                ) osl on true
                            where rgl.Registry_pid = :Registry_id
                                and osl.SyncStatus = 'SyncedEvn'
                            limit 1
                        ) + (
                            select count(osl.ObjectSynchronLog_id)
                            from {$this->scheme}.v_RegistryDataEvnPS rd
                                inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                inner join v_Evn e on e.Evn_id = rd.Evn_id
                                left join lateral (
                                    select ObjectSynchronLog_id, Object_Name as SyncStatus
                                    from v_ObjectSynchronLog
                                    where ObjectSynchronLogService_id = 1 and Object_id = e.Evn_id
                                    and Object_Name in ('SyncedEvn','SyncedEvnFail')
                                    order by Object_setDT desc
                                    limit 1
                                ) osl on true
                            where rgl.Registry_pid = :Registry_id
                                and osl.SyncStatus = 'SyncedEvn'
                            limit 1
                        ) + (
                            select count(osl.ObjectSynchronLog_id)
                            from {$this->scheme}.v_RegistryDataDisp rd
                                inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                inner join v_Evn e on e.Evn_id = rd.Evn_id
                                left join lateral (
                                    select
                                        ObjectSynchronLog_id, Object_Name as SyncStatus
                                    from
                                        v_ObjectSynchronLog
                                    where
                                        ObjectSynchronLogService_id = 1 and Object_id = e.Evn_id
                                    and Object_Name in ('SyncedEvn','SyncedEvnFail')
                                    order by Object_setDT desc
                                    limit 1
                                ) osl
                            where rgl.Registry_pid = :Registry_id
                                and osl.SyncStatus = 'SyncedEvn'
                            limit 1
                        ) + (
                            select count(osl.ObjectSynchronLog_id)
                            from {$this->scheme}.v_RegistryDataProf rd
                                inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                inner join v_Evn e on e.Evn_id = rd.Evn_id
                                left join lateral (
                                    select ObjectSynchronLog_id, Object_Name as SyncStatus
                                    from v_ObjectSynchronLog
                                    where ObjectSynchronLogService_id = 1 and Object_id = e.Evn_id
                                    and Object_Name in ('SyncedEvn','SyncedEvnFail')
                                    order by Object_setDT desc
                                    limit 1
                                ) osl on true
                            where rgl.Registry_pid = :Registry_id
                                and osl.SyncStatus = 'SyncedEvn'
                            limit 1
                        ) + (
                            select count(osl.ObjectSynchronLog_id)
                            from {$this->scheme}.v_RegistryDataPar rd
                                inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                                inner join v_Evn e on e.Evn_id = rd.Evn_id
                                left join lateral (
                                    select ObjectSynchronLog_id, Object_Name as SyncStatus
                                    from v_ObjectSynchronLog
                                    where ObjectSynchronLogService_id = 1 and Object_id = e.Evn_id
                                    and Object_Name in ('SyncedEvn','SyncedEvnFail')
                                    order by Object_setDT desc
                                    limit 1
                                ) osl on true
                            where rgl.Registry_pid = :Registry_id
                                and osl.SyncStatus = 'SyncedEvn'
                            limit 1
                        )
                    ) as SyncedEvnCount
                )
				select
				    (select EvnCount from cte) as \"EvnCount\",
				    (select SyncedEvnCount from cte2) as \"SyncedEvnCount\"
			";
		} else {
			// получаем все случаи из реестра
			$query = "
				select
				     Evn_id as \"Evn_id\",
				     EvnClass_id as \"EvnClass_id\",
				     EvnClass_Name as \"EvnClass_Name\",
				     EvnClass_SysNick as \"EvnClass_SysNick\",
				     SyncStatus as \"SyncStatus\",
				     ObjectSynchronLog_id as \"ObjectSynchronLog_id\",
				     Evn_NumCard as \"Evn_NumCard\",
				     Evn_setDate as \"Evn_setDate\",
				     Evn_disDate as \"Evn_disDate\",
				     Lpu_Nick as \"Lpu_Nick\",
				     Person_Fio as \"Person_Fio\",
				     Person_BirthDay as \"Person_BirthDay\"
				from (
					select
						e.Evn_id,
						e.EvnClass_id,
						e.EvnClass_Name,
						e.EvnClass_SysNick,
						osl.SyncStatus,
						osl.ObjectSynchronLog_id,
						epl.EvnPL_NumCard as Evn_NumCard,
						to_char(e.Evn_setDT, 'dd.mm.yyyy') as Evn_setDate,
						to_char(e.Evn_disDT, 'dd.mm.yyyy') as Evn_disDate,
						l.Lpu_Nick,
						ps.Person_Fio,
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay
					from
						{$this->scheme}.v_RegistryData rd
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
						inner join v_Evn e on e.Evn_id = rd.Evn_id
						left join lateral (
							select 
							    ObjectSynchronLog_id,
                                Object_Name as SyncStatus
							from v_ObjectSynchronLog
							where ObjectSynchronLogService_id = '1' and Object_id = e.Evn_id
							and Object_Name in ('SyncedEvn','SyncedEvnFail')
							order by Object_setDT desc
							limit 1
						) osl on true
						left join v_EvnPL epl on epl.EvnPL_id = e.Evn_id
						left join v_Lpu_all l on l.Lpu_id = e.Lpu_id
						left join v_Person_all ps on ps.PersonEvn_id = e.PersonEvn_id and ps.Server_id = e.Server_id
					where
						rgl.Registry_pid = :Registry_id

					union all

					select
						e.Evn_id,
						e.EvnClass_id,
						e.EvnClass_Name,
						e.EvnClass_SysNick,
						osl.SyncStatus,
						osl.ObjectSynchronLog_id,
						eps.EvnPS_NumCard as Evn_NumCard,
						to_char(e.Evn_setDT, 'dd.mm.yyyy') as Evn_setDate,
						to_char(e.Evn_disDT, 'dd.mm.yyyy') as Evn_disDate,
						l.Lpu_Nick,
						ps.Person_Fio,
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay
					from
						{$this->scheme}.v_RegistryDataEvnPS rd
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
						inner join v_Evn e on e.Evn_id = rd.Evn_id
						left join lateral(
							select
                                ObjectSynchronLog_id,
                                Object_Name as SyncStatus
							from v_ObjectSynchronLog
							where ObjectSynchronLogService_id = '1' and Object_id = e.Evn_id
							and Object_Name in ('SyncedEvn','SyncedEvnFail')
							order by Object_setDT desc
							limit 1
						) osl on true
						left join v_EvnPS eps on eps.EvnPS_id = e.Evn_id
						left join v_Lpu_all l on l.Lpu_id = e.Lpu_id
						left join v_Person_all ps on ps.PersonEvn_id = e.PersonEvn_id and ps.Server_id = e.Server_id
					where
						rgl.Registry_pid = :Registry_id

					union all

					select
						e.Evn_id,
						e.EvnClass_id,
						e.EvnClass_Name,
						e.EvnClass_SysNick,
						osl.SyncStatus,
						osl.ObjectSynchronLog_id,
						null as Evn_NumCard,
						to_char(e.Evn_setDT, 'dd.mm.yyyy') as Evn_setDate,
						to_char(e.Evn_disDT, 'dd.mm.yyyy') as Evn_disDate,
						l.Lpu_Nick,
						ps.Person_Fio,
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay
					from
						{$this->scheme}.v_RegistryDataDisp rd
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
						inner join v_Evn e on e.Evn_id = rd.Evn_id
						left join lateral(
							select
                                ObjectSynchronLog_id,
                                Object_Name as SyncStatus
							from v_ObjectSynchronLog
							where ObjectSynchronLogService_id = '1' and Object_id = e.Evn_id
							and Object_Name in ('SyncedEvn','SyncedEvnFail')
							order by Object_setDT desc
							limit 1
						) osl on true
						left join v_Lpu_all l on l.Lpu_id = e.Lpu_id
						left join v_Person_all ps on ps.PersonEvn_id = e.PersonEvn_id and ps.Server_id = e.Server_id
					where
						rgl.Registry_pid = :Registry_id

					union all

					select
						e.Evn_id,
						e.EvnClass_id,
						e.EvnClass_Name,
						e.EvnClass_SysNick,
						osl.SyncStatus,
						osl.ObjectSynchronLog_id,
						null as Evn_NumCard,
						to_char(e.Evn_setDT, 'dd.mm.yyyy') as Evn_setDate,
						to_char(e.Evn_disDT, 'dd.mm.yyyy') as Evn_disDate,
						l.Lpu_Nick,
						ps.Person_Fio,
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay
					from
						{$this->scheme}.v_RegistryDataProf rd
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
						inner join v_Evn e on e.Evn_id = rd.Evn_id
						left join lateral (
							select
							    ObjectSynchronLog_id,
							    Object_Name as SyncStatus
							from v_ObjectSynchronLog
							where ObjectSynchronLogService_id = '1' and Object_id = e.Evn_id
							and Object_Name in ('SyncedEvn','SyncedEvnFail')
							order by Object_setDT desc
						) osl on true
						left join v_Lpu_all l on l.Lpu_id = e.Lpu_id
						left join v_Person_all ps on ps.PersonEvn_id = e.PersonEvn_id and ps.Server_id = e.Server_id
					where
						rgl.Registry_pid = :Registry_id

					union all

					select
						e.Evn_id,
						e.EvnClass_id,
						e.EvnClass_Name,
						e.EvnClass_SysNick,
						osl.SyncStatus,
						osl.ObjectSynchronLog_id,
						null as Evn_NumCard,
						to_char(e.Evn_setDT, 'dd.mm.yyyy') as Evn_setDate,
						to_char(e.Evn_disDT, 'dd.mm.yyyy') as Evn_disDate,
						l.Lpu_Nick,
						ps.Person_Fio,
						to_char(ps.Person_BirthDay, 'dd.mm.yyyy') as Person_BirthDay
					from
						{$this->scheme}.v_RegistryDataPar rd
						inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
						inner join v_Evn e on e.Evn_id = rd.Evn_id
						left join lateral (
							select
							    ObjectSynchronLog_id,
							    Object_Name as SyncStatus
							from v_ObjectSynchronLog
							where ObjectSynchronLogService_id = '1' and Object_id = e.Evn_id
							and Object_Name in ('SyncedEvn','SyncedEvnFail')
							order by Object_setDT desc
							limit 1
						) osl on true
						left join v_Lpu_all l on l.Lpu_id = e.Lpu_id
						left join v_Person_all ps on ps.PersonEvn_id = e.PersonEvn_id and ps.Server_id = e.Server_id
					where
						rgl.Registry_pid = :Registry_id
				) tab

				order by
					case
						when SyncStatus ilike 'SyncedEvn' then 2
						when SyncStatus ilike 'SyncedEvnFail' then 1
						else 0
					end,
					EvnClass_id
			";
		}

		return $this->queryResult($query, [
			'Registry_id' => $data['Registry_id'],
			'Service_id' => $this->Rmis_model->getServiceId()
		]);
	}

    /**
     * Простановка статуса реестра.
     * @param $data
     */
	function setRegistryCheckStatus($data)
	{
		if (!isset($data['RegistryCheckStatus_id'])) {
			$data['RegistryCheckStatus_id'] = null;
		}

		$query = "
			update
				{$this->scheme}.Registry
			set
				RegistryCheckStatus_id = :RegistryCheckStatus_id,
				pmUser_updID = :pmUser_id,
				Registry_updDT = dbo.tzGetDate()
			where
				Registry_id = :Registry_id
		";

		$this->db->query($query, $data);
	}

    /**
     * Экспорт реестра в РМИС
     * @param $data
     * @return array
     */
	public function cancelExportRegistryToRMIS($data)
    {
		if (!empty($data['Registry_id'])) {
			// если статус "Отправка данных в РМИС" или "Загружен в РМИС", запретить изменять
			$rcsresp = $this->queryResult("
			select
				r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
			from
				{$this->scheme}.Registry r
				inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id']
			]);
			if (!empty($rcsresp[0]['RegistryCheckStatus_id']) && in_array($rcsresp[0]['RegistryCheckStatus_id'], [47])) {
				return ['Error_Msg' => 'Отмена экспорта реестра в РМИС запрещена, т.к. статус реестра: ' . $rcsresp[0]['RegistryCheckStatus_Name']];
			}
		}

		// проставляем статус "Ошибка отправки в РМИС".
		$this->setRegistryCheckStatus([
			'Registry_id' => $data['Registry_id'],
			'RegistryCheckStatus_id' => 46,
			'pmUser_id' => $data['pmUser_id']
		]);

		return array('Error_Msg' => '');
	}

    /**
     * Экспорт реестра в РМИС
     * @param array $data
     * @return array
     */
	public function exportRegistryToRMIS($data)
    {
		if (!empty($data['Registry_id'])) {
			// если статус "Отправка данных в РМИС" или "Загружен в РМИС", запретить изменять
			$rcsresp = $this->queryResult("
			select
				r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
			from
				{$this->scheme}.Registry r
				inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id']
			]);
			if (!empty($rcsresp[0]['RegistryCheckStatus_id']) && in_array($rcsresp[0]['RegistryCheckStatus_id'], [45, 47])) {
				return ['Error_Msg' => 'Экспорт реестра в РМИС запрещён, т.к. статус реестра: ' . $rcsresp[0]['RegistryCheckStatus_Name']];
			}
		}

		$beg_datetime = $this->getFirstResultFromQuery("select dbo.tzGetDate()");
		$filepath = EXPORTPATH_ROOT.'rmis_log/';

		$file_list = glob($filepath."export_to_rmis_{$data['Registry_id']}_*.txt");
		$file_index = count($file_list)+1;

		$filename = "export_to_rmis_{$data['Registry_id']}_{$file_index}.txt";

		// проставляем статус "Отправка данных в РМИС".
		$this->setRegistryCheckStatus([
			'Registry_id' => $data['Registry_id'],
			'RegistryCheckStatus_id' => 45,
			'pmUser_id' => $data['pmUser_id']
		]);

		$resp = $this->getRegistryToRmis($data, 'export');
		if (!is_array($resp)) {
			// проставляем статус "Ошибка отправки в РМИС".
			$this->setRegistryCheckStatus([
				'Registry_id' => $data['Registry_id'],
				'RegistryCheckStatus_id' => 46,
				'pmUser_id' => $data['pmUser_id']
			]);
			return ['Error_Msg' => 'Ошибка при запросе случаев лечения'];
		}

		$allCount = 0;
		$sendCount = 0;
		$errorCount = 0;
		$errors = [];
		$fromBegin = (!empty($data['fromBegin']) && $data['fromBegin']);

		if ($fromBegin) {
			$allCount = count($resp);
		} else {
			$allCount = count(array_filter($resp, function($item){
				return ($item['SyncStatus'] == 'SyncedEvn');
			}));
		}

		$this->addLinesToLog($filepath, $filename, [
			ConvertDateTimeFormat($beg_datetime, "d.m.Y H:i:s") . " Подготовлено случаев для передачи: $allCount"
		]);

		$this->load->model('Rmis_model');
		$this->Rmis_model->Registry_id = $data['Registry_id'];
		$initresp = $this->Rmis_model->initSoapOptions($data['session'], true);
		if (!empty($initresp['Error_Msg'])) {
			return $initresp;
		}
		foreach($resp as $evn) {
			if (!$fromBegin && $evn['SyncStatus'] == 'SyncedEvn') {
				//Если грузим не сначала, то пропускаем уже полностью синхронизованные случаи
				continue;
			}
			$result = null;
			switch($evn['EvnClass_SysNick']) {
				case 'EvnPL':
				case 'EvnPLStom':
					$result = $this->Rmis_model->syncEvnPL($evn['Evn_id']);
					break;
				case 'EvnPS':
					$result = $this->Rmis_model->syncEvnPS($evn['Evn_id']);
					break;
				case 'EvnPLDispDop13':
				case 'EvnPLDispProf':
				case 'EvnPLDispOrp':
				case 'EvnPLDispTeenInspection':
					$result = $this->Rmis_model->syncEvnPLDisp($evn['Evn_id']);
					break;
			}
			if ($result && !empty($result['Error_Msg'])) {
				$errorCount++;
				$this->addLinesToLog($filepath, $filename, [
					"\n",
					$evn['EvnClass_Name'].(!empty($evn['Evn_NumCard'])?". Карта №".$evn['Evn_NumCard']:""),
					$evn['Lpu_Nick'],
					$evn['Evn_setDate'].(!empty($evn['Evn_disDate'])?" - ".$evn['Evn_disDate']:""),
					$evn['Person_Fio'].", ".$evn['Person_BirthDay'],
					(!empty($result['Error_Code'])?$result['Error_Code']." ":"").$result['Error_Msg']
				]);
			} else {
				$sendCount++;
			}
		}

		$end_datetime = $this->getFirstResultFromQuery("select dbo.tzGetDate()");
		$this->addLinesToLog($filepath, $filename, [
			"\n", ConvertDateTimeFormat($end_datetime, "d.m.Y H:i:s") . "Передано случаев: $sendCount, случаев с ошибками: $errorCount"
		]);

		if ($errorCount > 0) {
			// проставляем статус "Ошибка отправки в РМИС".
			$this->setRegistryCheckStatus([
				'Registry_id' => $data['Registry_id'],
				'RegistryCheckStatus_id' => 46,
				'pmUser_id' => $data['pmUser_id']
			]);
		} else {
			// проставляем статус "Загружен в РМИС".
			$this->setRegistryCheckStatus([
				'Registry_id' => $data['Registry_id'],
				'RegistryCheckStatus_id' => 47,
				'pmUser_id' => $data['pmUser_id']
			]);
		}

		return [
			'Error_Msg' => '',
			'allCount' => $allCount,
			'sendCount' => $sendCount,
			'errorCount' => $errorCount,
			'Link' => $filepath.$filename
		];
	}

    /**
     * Добавление строк в лог-файл
     * @param $filepath
     * @param $filename
     * @param $text_lines
     */
	public function addLinesToLog($filepath, $filename, $text_lines)
    {
		if (!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
		}
		file_put_contents($filepath.$filename, implode("\n", $text_lines), FILE_APPEND);
	}

    /**
     *    Обновление данных полиса идентифицированного человека
     * @param $data
     * @param $pers
     * @return string
     */
	public function updatePersonPolis($data, $pers) {
		
		if ( empty($pers['SCARD']) ) {
			return '';
		}

		try {
			$pers = array_map('trim', $pers);

			// $this->textlog->add('[' . date('Y-m-d H:i:s') . '] Зашли в !empty($pers[\'SCARD\'])');
			// ищем человека в БД
			$query = "
				select
					Person_id as \"Person_id\"
				from
					v_PersonState
				where
					Person_SurName = :Person_SurName
					and Person_FirName = :Person_FirName
					and coalesce(NULLIF(Person_SecName, ''), '-') = :Person_SecName
					and Person_BirthDay = :Person_BirthDay
			";
			$params = [
				'Person_SurName' => $pers['SNAME'],
				'Person_FirName' => $pers['FI'],
				'Person_SecName' => $pers['SI'],
				'Person_BirthDay' => date('Y-m-d', strtotime($pers['BORNDT']))
			];
			$result = $this->db->query($query, $params);
			// $this->textlog->add('[' . date('Y-m-d H:i:s') . '] Выполнили запрос: ' . getDebugSQL($query, $params));

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса к базе данных (поиск человека)');
			}

			$resp = $result->result('array');

			if ( !is_array($resp) || count($resp) != 1 ) {
				throw new Exception('Ошибка при поиске человека в базе данных по ФИО и ДР (' . $pers['SNAME'] . ' ' . $pers['FI'] . ' ' . $pers['SI'] . ')');
			}

			$data['PolisType_id'] = 1;
			$data['PolisFormType_id'] = null;

			switch ( $pers['VPOLIS'] ) {
				case 1:
					$data['PolisType_id'] = 1;
				break;

				case 2:
					$data['PolisType_id'] = 3;
				break;

				case 3:
					$data['PolisType_id'] = 4;

					// https://redmine.swan.perm.ru/issues/80392
					if ( !empty($pers['FPOLIS']) || $pers['FPOLIS'] === 0 || $pers['FPOLIS'] === '0' ) {
						switch ( $pers['FPOLIS'] ) {
							case 0: $data['PolisFormType_id'] = 47; break;
							case 1: $data['PolisFormType_id'] = 48; break;
							case 2: $data['PolisFormType_id'] = 49; break;
							case 3: $data['PolisFormType_id'] = 50; break;
						}
					}
				break;

				default:
					throw new Exception('Ошибка при определении типа документа ОМС');
				break;
			}

			if ( !array_key_exists(intval($pers['SMO']), $this->orgSmoList) ) {
				$this->orgSmoList[intval($pers['SMO'])] = $this->getFirstResultFromQuery("
					select
					    OrgSmo_id as \"OrgSmo_id\"
                    from
                        v_OrgSMO
                    where
                        RIGHT(Orgsmo_f002smocod, 2) = :SMO
                    and
                        KLRgn_id = 66
                    limit 1
				", [
				    'SMO' => intval($pers['SMO'])
				]);
			}

			$data['OrgSmo_id'] = $this->orgSmoList[intval($pers['SMO'])];

			if ( empty($data['OrgSmo_id']) ) {
				$data['OrgSmo_id'] = null;
			}
			

			$Polis_begDate = empty($pers['DVIZIT']) ? NULL : date('Y-m-d', strtotime($pers['DVIZIT']));
			$Polis_endDate = ($pers['DEND'] == '20991231' || empty($pers['DEND'])) ? null : date('Y-m-d', strtotime($pers['DEND']));
			$Polis_closeDate = empty($pers['DVIZIT']) ? NULL : date('Y-m-d', strtotime($pers['DVIZIT'] . "-1 days"));
			$Polis_nextDate = empty($Polis_endDate) ? NULL : date('Y-m-d', strtotime($Polis_endDate . "+1 days"));
			$OmsSprTerr_id = $data['OmsSprTerr_id'];//bad
			$PolisType_id = (empty($data['PolisType_id']) ? NULL : $data['PolisType_id']);
			$PolisFormType_id = (empty($data['PolisFormType_id']) ? NULL : $data['PolisFormType_id']);
			
			$OrgSmo_id = $data['OrgSmo_id'];
			$Polis_Ser = '';
			$Polis_Num = $pers['SCARD'];
			$Federal_Num = NULL;
			$Person_id = $resp[0]['Person_id'];

			//echo $Polis_closeDate." = ".$Polis_endDate." = ".$Polis_begDate;exit();

			if ( $PolisType_id == 4 ) {
				$Federal_Num = $Polis_Num;
			}

			$hasPersonEvnChanges = false;

			// 1) Нужно вытащить предыдущий документ ОМС и правильно закрыть его при необходимости
			$query = "
				select
					pa.Server_id as \"Server_id\",
					pa.PersonEvn_id as \"PersonEvn_id\",
					pol.Polis_id as \"Polis_id\",
					pol.OmsSprTerr_id as \"OmsSprTerr_id\",
					pol.OrgSmo_id as \"OrgSmo_id\",
					pol.PolisType_id as \"PolisType_id\",
					pol.PolisFormType_id as \"PolisFormType_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pa.Person_EdNum as \"Person_EdNum\",
					to_char(pol.Polis_begDate, 'yyyy-mm-dd') as \"Polis_begDate\",
					to_char(pol.Polis_endDate, 'yyyy-mm-dd') as \"Polis_endDate\"
				from v_Person_all pa 
					inner join v_Polis pol ON pa.Polis_id = pol.Polis_id
				where
					pa.Person_id = :Person_id
					and pa.PersonEvnClass_id = 8
					and cast(pol.Polis_begDate as date) < cast(:Polis_begDate as date)
				order by pol.Polis_begDate desc
				limit 1
			";
			$queryParams = [
				'Person_id' => $Person_id,
				'Polis_begDate' => $Polis_begDate,
			];
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса (получение полисных данных) (SCARD=' . $pers['SCARD'] . ')');
			}

			//$data['PersonIdentState_id'] = 1;
			$resp = $result->result('array');

			if (is_array($resp) && count($resp) > 0 && ($Polis_closeDate != $resp[0]['Polis_endDate'])) {
				$query = "
					select
					    Error_Message as \"Error_Msg\" 
					from p_PersonPolis_upd
					(
						PersonPolis_id := :PersonEvn_id,
						Server_id := :Server_id,
						Person_id := :Person_id,
						OmsSprTerr_id := :OmsSprTerr_id,
						PolisType_id := :PolisType_id,
						PolisFormType_id := :PolisFormType_id,
						OrgSmo_id := :OrgSmo_id,
						Polis_Ser := :Polis_Ser,
						Polis_Num := :Polis_Num,
						Polis_begDate := :Polis_begDate,
						Polis_endDate := :Polis_endDate,
						pmUser_id := :pmUser_id
                    )
                ";

				if ( !(empty($Polis_closeDate) || $Polis_closeDate >= $resp[0]['Polis_begDate']) ) {
					throw new Exception('Ошибка при попытке выполнении запроса (обновление полисных данных) (SCARD=' . $pers['SCARD'] . ') - дата закрытия полиса не может быть меньше даты открытия');
				}

				$result = $this->db->query($query, [
					'PersonEvn_id' => $resp[0]['PersonEvn_id'],
					'Server_id' => $resp[0]['Server_id'],
					'Person_id' => $Person_id,
					'OmsSprTerr_id' => $resp[0]['OmsSprTerr_id'],
					'PolisType_id' => $resp[0]['PolisType_id'],
					'PolisFormType_id' => $resp[0]['PolisFormType_id'],
					'OrgSmo_id' => $resp[0]['OrgSmo_id'],
					'Polis_Ser' => $resp[0]['Polis_Ser'],
					'Polis_Num' => $resp[0]['Polis_Num'],
					'Polis_begDate' => $resp[0]['Polis_begDate'],
					'Polis_endDate' => $Polis_closeDate,
					'pmUser_id' => $data['pmUser_id'],
				]);

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при выполнении запроса (обновление полисных данных) (SCARD=' . $pers['SCARD'] . ')');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					throw new Exception('Ошибка при обновлении полисных данных (SCARD=' . $pers['SCARD'] . ')');
				}
				else if ( !empty($resp[0]['ErrMsg']) ) {
					throw new Exception($resp[0]['ErrMsg']);
				}

				$hasPersonEvnChanges = true;
			}

			// 2. Ищем и брабатываем документы ОМС, которые начали действовать после даты начала действия документа ОМС из загруженного файла
			$query = "
				select
					pa.Server_id as \"Server_id\",
					pa.PersonEvn_id as \"PersonEvn_id\",
					pol.Polis_id as \"Polis_id\",
					pol.OmsSprTerr_id as \"OmsSprTerr_id\",
					pol.OrgSmo_id as \"OrgSmo_id\",
					pol.PolisType_id as \"PolisType_id\",
					pol.PolisFormType_id as \"PolisFormType_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pa.Person_EdNum as \"Person_EdNum\",
					to_char(pol.Polis_begDate, 'yyyy-mm-dd') as \"Polis_begDate\",
					to_char(pol.Polis_endDate, 'yyyy-mm-dd') as \"Polis_endDate\"
				from v_Person_all pa 
					inner join v_Polis pol ON pa.Polis_id = pol.Polis_id
				where
					pa.Person_id = :Person_id
					and pa.PersonEvnClass_id = 8
					and cast(pol.Polis_begDate as date) >= cast(:Polis_begDate as date)
			";
			$queryParams = [
				'Person_id' => $Person_id,
				'Polis_begDate' => $Polis_begDate
			];
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				throw new Exception('Ошибка при выполнении запроса (получение полисных данных) (SCARD=' . $pers['SCARD'] . ')');
			}

			//$data['PersonIdentState_id'] = 1;
			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 ) {
				foreach ( $resp as $row )  {
					if (
						$row['Server_id'] == 0
						&& $OrgSmo_id == $row['OrgSmo_id']
						&& $PolisType_id == $row['PolisType_id']
						&& $Polis_begDate == $row['Polis_begDate']
						&& $Polis_Num == $row['Polis_Num']
					) {
						$recordToUpdate = $row;
						continue;
					}
					else if ( empty($Polis_endDate) || (!empty($row['Polis_endDate']) && $Polis_endDate >= $row['Polis_endDate']) ) {
						$query = "
                            select
                                Error_Message as \"Error_Msg\"
							from dbo.xp_PersonRemovePersonEvn
							(
								Server_id := :Server_id,
								PersonEvn_id := :PersonEvn_id,
								Person_id := :Person_id,
								pmUser_id := :pmUser_id
							)
						";
						$result = $this->db->query($query, [
							'Server_id' => $row['Server_id'],
							'PersonEvn_id' => $row['PersonEvn_id'],
							'Person_id' => $Person_id,
							'pmUser_id' => $data['pmUser_id']
						]);

						if ( !is_object($result) ) {
							throw new Exception('Ошибка при выполнении запроса (удаление документа ОМС) (SCARD=' . $pers['SCARD'] . ')');
						}

						$resp = $result->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							throw new Exception('Ошибка при удалении документа ОМС (SCARD=' . $pers['SCARD'] . ')');
						}
						else if ( !empty($resp[0]['ErrMsg']) ) {
							throw new Exception($resp[0]['ErrMsg']);
						}

						$hasPersonEvnChanges = true;
					} else {
						// Сдвигаем дату начала действия следующих ДОМС на 1 день после даты окончания ДОМС из ответа ТФОМС
						if ($row['Polis_begDate'] != $Polis_nextDate) {
							$query = "
                                select
                                    Error_Message as \"Error_Msg\"
								from p_PersonPolis_upd
								(
									PersonPolis_id := :PersonEvn_id,
									Server_id := :Server_id,
									Person_id := :Person_id,
									OmsSprTerr_id := :OmsSprTerr_id,
									PolisType_id := :PolisType_id,
									PolisFormType_id := :PolisFormType_id,
									OrgSmo_id := :OrgSmo_id,
									Polis_Ser := :Polis_Ser,
									Polis_Num := :Polis_Num,
									Polis_begDate := :Polis_begDate,
									Polis_endDate := :Polis_endDate,
									pmUser_id := :pmUser_id
								)
							";

							if (!(empty($row['Polis_endDate']) || $row['Polis_endDate'] >= $Polis_nextDate)) {
								throw new Exception('Ошибка при попытке выполнении запроса (сдвиг даты начала действия более поздних документов ОМС) (SCARD=' . $pers['SCARD'] . ') - дата закрытия полиса не может быть меньше даты открытия');
							}

							$result = $this->db->query($query, [
								'PersonEvn_id' => $row['PersonEvn_id'],
								'Server_id' => $row['Server_id'],
								'Person_id' => $Person_id,
								'OmsSprTerr_id' => $row['OmsSprTerr_id'],
								'PolisType_id' => $row['PolisType_id'],
								'PolisFormType_id' => $row['PolisFormType_id'],
								'OrgSmo_id' => $row['OrgSmo_id'],
								'Polis_Ser' => $row['Polis_Ser'],
								'Polis_Num' => $row['Polis_Num'],
								'Polis_begDate' => $Polis_nextDate,
								'Polis_endDate' => $row['Polis_endDate'],
								'pmUser_id' => $data['pmUser_id'],
							]);

							if (!is_object($result)) {
								throw new Exception('Ошибка при выполнении запроса (сдвиг даты начала действия более поздних документов ОМС) (SCARD=' . $pers['SCARD'] . ')');
							}

							$resp = $result->result('array');

							if (!is_array($resp) || count($resp) == 0) {
								throw new Exception('Ошибка при сдвиге даты начала действия более поздних документов ОМС (SCARD=' . $pers['SCARD'] . ')');
							} else if (!empty($resp[0]['ErrMsg'])) {
								throw new Exception($resp[0]['ErrMsg']);
							}

							$hasPersonEvnChanges = true;
						}
					}
				}
			}

			if ( isset($recordToUpdate) && is_array($recordToUpdate) && count($recordToUpdate) > 0 ) {
				$proc = 'upd';
			}
			else {
				$proc = 'ins';
			}

			$updated = false;
			if (
				$proc == 'ins'
				// если обновление, то только если что то изменяется.
				|| $recordToUpdate['Polis_endDate'] != $Polis_endDate
				|| $recordToUpdate['OmsSprTerr_id'] != $OmsSprTerr_id
				|| $recordToUpdate['PolisFormType_id'] != $PolisFormType_id
				|| $recordToUpdate['Server_id'] != 0
			) {
				$query = "
					select
					    PersonPolis_id as \"PersonPolis_id\",
					    Error_Message as \"Error_Msg\"
					from p_PersonPolis_{$proc}
					(
						PersonPolis_id :=:PersonEvn_id;,
						Server_id := :Server_id,
						Person_id := :Person_id,
						OmsSprTerr_id := :OmsSprTerr_id,
						PolisType_id := :PolisType_id,
						PolisFormType_id := :PolisFormType_id,
						OrgSmo_id := :OrgSmo_id,
						Polis_Ser := :Polis_Ser,
						Polis_Num := :Polis_Num,
						Polis_begDate := :Polis_begDate,
						Polis_endDate := :Polis_endDate,
						PersonPolis_insDT := :Polis_begDate,
						pmUser_id := :pmUser_id
					)
				";

				if (!(empty($Polis_endDate) || $Polis_endDate >= $Polis_begDate)) {
					throw new Exception('Ошибка при попытке выполнении запроса (внесение информации об актуальном документе ОМС) (SCARD=' . $pers['SCARD'] . ') - дата закрытия полиса не может быть меньше даты открытия');
				}

				$result = $this->db->query($query, [
					'PersonEvn_id' => ($proc == "upd" ? $recordToUpdate['PersonEvn_id'] : null),
					'Server_id' => 0,
					'Person_id' => $Person_id,
					'OmsSprTerr_id' => $OmsSprTerr_id,
					'PolisType_id' => $PolisType_id,
					'PolisFormType_id' => $PolisFormType_id,
					'OrgSmo_id' => $OrgSmo_id,
					'Polis_Ser' => $Polis_Ser,
					'Polis_Num' => $Polis_Num,
					'Polis_begDate' => $Polis_begDate,
					'Polis_endDate' => $Polis_endDate,
					'pmUser_id' => $data['pmUser_id'],
				]);

				if (!is_object($result)) {
					throw new Exception('Ошибка при выполнении запроса (внесение информации об актуальном документе ОМС) (SCARD=' . $pers['SCARD'] . ')');
				}

				$resp = $result->result('array');

				if (!is_array($resp) || count($resp) == 0) {
					throw new Exception('Ошибка при внесении информации об актуальном документе ОМС (SCARD=' . $pers['SCARD'] . ')');
				} else if (!empty($resp[0]['ErrMsg'])) {
					throw new Exception($resp[0]['ErrMsg']);
				}

				$updated = true;
				$hasPersonEvnChanges = true;
			}

			// Проверяем необходимость добавления/обновления ЕНП
			if ( !empty($Federal_Num) ) {
				$enpAddFlag = false;

				$query = "
					select
						Server_id as \"Server_id\",
						PersonPolisEdNum_id as \"PersonPolisEdNum_id\",
						PersonPolisEdNum_EdNum as \"PersonPolisEdNum_EdNum\",
						to_char(PersonPolisEdNum_insDT, 'yyyy-mm-dd') as \"PersonPolisEdNum_insDate\"
					from v_PersonPolisEdNum
					where Person_id = :Person_id
						and PersonPolisEdNum_insDate <= :Polis_begDate
					order by PersonPolisEdNum_insDT desc
					limit 2
				";
				$result = $this->db->query($query, [
					'Person_id' => $Person_id,
					'Polis_begDate' => $Polis_begDate,
				]);

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при выполнении запроса (получение информации о ЕНП) (SCARD=' . $pers['SCARD'] . ')');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					$enpAddFlag = true;
				}
				else if ( $resp[0]['PersonPolisEdNum_EdNum'] != $Federal_Num ) {
					if ( !empty($resp[1]['PersonPolisEdNum_EdNum']) && $resp[1]['PersonPolisEdNum_EdNum'] != $Federal_Num ) {
						$enpAddFlag = true;
					}

					// Удаляем существующую периодику, если дата начала совпадает с датой начала действия полиса
					if ( $resp[0]['PersonPolisEdNum_insDate'] == $Polis_begDate ) {
						$query = "
							select
							    Error_Message as \"Error_Msg\"
							from dbo.xp_PersonRemovePersonEvn
							(
								Server_id := :Server_id,
								PersonEvn_id := :PersonEvn_id,
								Person_id := :Person_id,
								pmUser_id := :pmUser_id
							)
						";
						$result = $this->db->query($query, [
							'Server_id' => $resp[0]['Server_id'],
							'PersonEvn_id' => $resp[0]['PersonPolisEdNum_id'],
							'Person_id' => $Person_id,
							'pmUser_id' => $data['pmUser_id']
						]);

						if ( !is_object($result) ) {
							throw new Exception('Ошибка при выполнении запроса (удаление ЕНП) (SCARD=' . $pers['SCARD'] . ')');
						}

						$resp = $result->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							throw new Exception('Ошибка при удалении ЕНП (SCARD=' . $pers['SCARD'] . ')');
						}
						else if ( !empty($resp[0]['ErrMsg']) ) {
							throw new Exception($resp[0]['ErrMsg']);
						}

						$hasPersonEvnChanges = true;
					}
				}

				if ( $enpAddFlag == true ) {
					$query = "
						select
							Server_id as \"Server_id\",
							PersonPolisEdNum_id as \"PersonPolisEdNum_id\",
							PersonPolisEdNum_EdNum as \"PersonPolisEdNum_EdNum\",
							to_char(PersonPolisEdNum_insDT, 'yyyy-mm-dd') as \"PersonPolisEdNum_insDate\"
						from v_PersonPolisEdNum
						where Person_id = :Person_id
							and PersonPolisEdNum_insDate > :Polis_begDate
						order by PersonPolisEdNum_insDT
						limit 1
					";
					$result = $this->db->query($query, [
						'Person_id' => $Person_id,
						'Polis_begDate' => $Polis_begDate,
					]);

					if ( !is_object($result) ) {
						throw new Exception('Ошибка при выполнении запроса (получение информации о ЕНП) (SCARD=' . $pers['SCARD'] . ')');
					}

					$resp = $result->result('array');

					if ( is_array($resp) && count($resp) > 0 && $resp[0]['PersonPolisEdNum_EdNum'] == $Federal_Num ) {
						$query = "
                            select
                                Error_Message as \"Error_Msg\"
							from dbo.xp_PersonRemovePersonEvn
							(
								Server_id := :Server_id,
								PersonEvn_id := :PersonEvn_id,
								Person_id := :Person_id,
								pmUser_id := :pmUser_id
							)
						";
						$result = $this->db->query($query, [
							'Server_id' => $resp[0]['Server_id'],
							'PersonEvn_id' => $resp[0]['PersonPolisEdNum_id'],
							'Person_id' => $Person_id,
							'pmUser_id' => $data['pmUser_id']
						]);

						if ( !is_object($result) ) {
							throw new Exception('Ошибка при выполнении запроса (удаление ЕНП) (SCARD=' . $pers['SCARD'] . ')');
						}

						$resp = $result->result('array');

						if ( !is_array($resp) || count($resp) == 0 ) {
							throw new Exception('Ошибка при удалении ЕНП (SCARD=' . $pers['SCARD'] . ')');
						}
						else if ( !empty($resp[0]['ErrMsg']) ) {
							throw new Exception($resp[0]['ErrMsg']);
						}

						$hasPersonEvnChanges = true;
					}
				}

				if ( $enpAddFlag == true ) {
					$query = "
						select
						    Error_Message as \"Error_Msg\"
						from p_PersonPolisEdNum_ins
						(
							Server_id := 0,
							Person_id := :Person_id,
							PersonPolisEdNum_insDT := :Polis_begDate,
							PersonPolisEdNum_EdNum := :Polis_Num,
							pmUser_id := :pmUser_id
						)
					";
					$result = $this->db->query($query, [
						'Person_id' => $Person_id,
						'Polis_begDate' => $Polis_begDate,
						'Polis_Num' => $Federal_Num,
						'pmUser_id' => $data['pmUser_id'],
					]);

					if ( !is_object($result) ) {
						throw new Exception('Ошибка при выполнении запроса (добавление ЕНП) (SCARD=' . $pers['SCARD'] . ')');
					}

					$resp = $result->result('array');

					if ( !is_array($resp) || count($resp) == 0 ) {
						throw new Exception('Ошибка при добавлении ЕНП (SCARD=' . $pers['SCARD'] . ')');
					}
					else if ( !empty($resp[0]['ErrMsg']) ) {
						throw new Exception($resp[0]['ErrMsg']);
					}

					$hasPersonEvnChanges = true;
				}
			}

			if ( $proc == 'upd' && $updated ) {
				$sql = "
					select
					    Error_Message as \"Error_Msg\"
					from xp_PersonTransferDate
					(
						Server_id := 0,
						PersonEvn_id := :PersonEvn_id,
						PersonEvn_begDT := :Polis_begDate,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($sql, [
					'PersonEvn_id' => $recordToUpdate['PersonEvn_id'],
					'Polis_begDate' => $Polis_begDate,
					'pmUser_id' => $data['pmUser_id'],
				]);

				$resp = $result->result('array');
			}

			if ($hasPersonEvnChanges) {
				$sql = 'exec [dbo].[xp_PersonTransferEvn] @Person_id = :Person_id';
				$this->db->query($sql, array('Person_id' => $Person_id));
			}

			if ( empty($pers['ERR']) ) {
				// Проставляем человеку признак "из БДЗ"
				$sql = "
					select
					    Error_Code as \"Error_Code\", Error_Message as \"Error_Msg\"
					from p_Person_server
					(
						Person_id := :Person_id,
						Server_id := 0,
						BDZ_Guid := (select BDZ_Guid from v_Person where Person_id = :Person_id limit 1),
						pmUser_id := :pmUser_id
                    )
				";
				$result = $this->db->query($sql, [
					'Person_id' => $Person_id,
					'pmUser_id' => $data['pmUser_id'],
				]);

				if ( !is_object($result) ) {
					throw new Exception('Ошибка при выполнении запроса (изменение признака "из БДЗ") (SCARD=' . $pers['SCARD'] . ')');
				}

				$resp = $result->result('array');

				if ( !is_array($resp) || count($resp) == 0 ) {
					throw new Exception('Ошибка при изменении признака "из БДЗ" (SCARD=' . $pers['SCARD'] . ')');
				}
				else if ( !empty($resp[0]['ErrMsg']) ) {
					throw new Exception($resp[0]['ErrMsg']);
				}
			}

			$response = '';
		} catch ( Exception $e ) {
			$response = $e->getMessage();
		}

		return $response;
	}
	
	/**
	 *	Установка признака "Оплачен"
	 */
	function setRegistryDataIsPaid($data)
	{
		$cond = '';
		if (!empty($data['Evn_id'])) {
			$cond .= " and Evn_id = :Evn_id";
		}

		$this->setRegistryParamsByType($data);

		$query = "
			update
			    {$this->scheme}.{$this->RegistryDataObject}
            set {$this->RegistryDataObject}_isPaid = :RegistryData_isPaid
			where Registry_id = :Registry_id {$cond}
		";
			
		$result = $this->db->query($query, $data);	
	}

    /**
     * Установка признака "Оплачен". Если у случаев есть ошибка, то он будет помечен как "Не оплачен"
     * @param $data
     * @return bool
     */
	function setRegistryPaid($data)
	{
		$registry_list = [];
		$query = "
			select
				RT.RegistryType_SysNick as \"RegistryType_SysNick\"
			from
				{$this->scheme}.v_Registry R
				inner join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
			where
				R.Registry_id = :Registry_id
			limit 1
		";
		$RegistryType_SysNick = $this->getFirstResultFromQuery($query, $data);

		if ($RegistryType_SysNick == 'group') {
			$query = "
				select
				    RGL.Registry_id as \"Registry_id\"
				from
				    {$this->scheme}.v_RegistryGroupLink RGL
				where RGL.Registry_pid = :Registry_id
			";
			$result = $this->db->query($query, $data);
			$registry_list = $result->result('array');
		} else {
			$registry_list[] = ['Registry_id' => $data['Registry_id']];
		}

		foreach($registry_list as $registry) {
			$params = [
				'Registry_id' => $registry['Registry_id'],
				'pmUser_id' => $data['pmUser_id']
			];
			$query = "
				select
				    Error_Message as \"Error_Msg\"
				from {$this->scheme}.p_Registry_setPaid
				(
					Registry_id := :Registry_id,
					pmUser_id := :pmUser_id
				)
			";
			$resp = $this->getFirstRowFromQuery($query, $params);
			if (!$resp || !empty($resp['Error_Msg'])) {
				return false;
			}
		}
		return true;
	}

    /**
     * Получаем состояние реестра в данный момент, и тип реестра
     * @param $data
     * @return array
     */
	public function GetRegistryXmlExport($data)
	{
		$RegistryType_id = $this->getFirstResultFromQuery("
                select
                    RegistryType_id as \"RegistryType_id\"
                from
                    {$this->scheme}.v_Registry
                where
                    Registry_id = :Registry_id
                limit 1
            ", [
                'Registry_id' => $data['Registry_id']
            ]);

		if ( $RegistryType_id == 13 ) {
			// Объединенный реестр
			$xmlExportPath = "case when ( UR.Registry_xmlExpDT is null or datediff('mi', UR.Registry_xmlExpDT, dbo.tzGetDate()) < 5 ) then RTrim(UR.Registry_xmlExportPath) else NULL end as Registry_xmlExportPath";

			if ( isSuperadmin() ) {
				$xmlExportPath = 'RTrim(UR.Registry_xmlExportPath) as \"Registry_xmlExportPath\"';
			}

			$query = "
				with RDSumTmp (
					RegistryData_Count,
					RegistryData_ItogSum
				) as (
					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from 
						{$this->scheme}.v_RegistryData RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from 
						{$this->scheme}.v_RegistryDataEvnPS RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from 
						{$this->scheme}.v_RegistryDataDisp RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from 
						{$this->scheme}.v_RegistryDataProf RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from 
						{$this->scheme}.v_RegistryDataCmp RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = :Registry_id

					union all

					select 
						COUNT(RD.Evn_id) as RegistryData_Count,
						SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
					from 
						{$this->scheme}.v_RegistryDataPar RD
						inner join {$this->scheme}.v_RegistryGroupLink RGL on RGL.Registry_id = RD.Registry_id
					where
						RGL.Registry_pid = :Registry_id
				)

				select
					 {$xmlExportPath},
					 UR.RegistryType_id as \"RegistryType_id\",
					 UR.RegistryGroupType_id as \"RegistryGroupType_id\",
					 UR.RegistryStatus_id as \"RegistryStatus_id\",
					 UR.Registry_FileNum as \"Registry_FileNum\",
					 to_char(UR.Registry_begDate, 'yyyymmdd') as \"Registry_begDate\",
					 RSum.Registry_IsNeedReform as \"Registry_IsNeedReform\",
					 RSum.Registry_Sum - round(RDSum.RegistryData_ItogSum,2) as \"Registry_SumDifference\",
					 RDSum.RegistryData_Count as \"RegistryData_Count\",
					 to_char(UR.Registry_endDate, 'mmdd') as \"Registry_endMonth\", -- для использования в имени формируемых файлов https://redmine.swan.perm.ru/issues/6547
					 pt.PayType_SysNick as \"PayType_SysNick\",
					 L.Lpu_f003mcod as \"Lpu_f003mcod\"
				from {$this->scheme}.v_Registry UR
					inner join v_Lpu L on L.Lpu_id = UR.Lpu_id
					left join lateral(
						select 
							SUM(RegistryData_Count) as RegistryData_Count,
							SUM(RegistryData_ItogSum) as RegistryData_ItogSum
						from 
							RDSumTmp
					) RDSum on true
					left join lateral(
						select
							SUM(ISNULL(R.Registry_Sum,0)) as Registry_Sum,
							MAX(ISNULL(R.Registry_IsNeedReform, 1)) as Registry_IsNeedReform
						from
							{$this->scheme}.v_Registry R
							inner join {$this->scheme}.v_RegistryGroupLink RGL2 on RGL2.Registry_id = R.Registry_id
						where
							RGL2.Registry_pid = UR.Registry_id
					) RSum on true
					left join v_PayType pt on pt.PayType_id = UR.PayType_id
				where
					UR.Registry_id = :Registry_id
			";
		}
		else {
			// Простые реестры
			$this->setRegistryParamsByType($data);

			$xmlExportPath = "case when (R.Registry_xmlExpDT is null or datediff('mi', R.Registry_xmlExpDT, dbo.tzGetDate()) < 5 ) then RTrim(R.Registry_xmlExportPath) else NULL end as Registry_xmlExportPath";

			if ( isSuperadmin() ) {
				$xmlExportPath = 'RTrim(R.Registry_xmlExportPath) as \"Registry_xmlExportPath\"';
			}

			$query = "
				select
					 {$xmlExportPath},
					 R.RegistryType_id as \"RegistryType_id\",
					 null as \"RegistryGroupType_id\",
					 R.RegistryStatus_id as \"RegistryStatus_id\",
					 R.Registry_IsNeedReform as \"Registry_IsNeedReform\",
					 R.Registry_FileNum as \"Registry_FileNum\",
					 R.Registry_Sum - round(RDSum.RegistryData_ItogSum, 2) as \"Registry_SumDifference\",
					 to_char(R.Registry_begDate, 'yyyymmdd') as \"Registry_begDate\",
					 RDSum.RegistryData_Count as \"RegistryData_Count\",
					 to_char(R.Registry_endDate, 'mmdd') as \"Registry_endMonth\",
					 pt.PayType_SysNick as \"PayType_SysNick\",
					 L.Lpu_f003mcod as \"Lpu_f003mcod\"
				from {$this->scheme}.v_Registry R
					inner join v_Lpu L on L.Lpu_id = R.Lpu_id
					left join lateral (
						select 
							COUNT(RD.Evn_id) as RegistryData_Count,
							SUM(coalesce(RD.RegistryData_ItogSum,0)) as RegistryData_ItogSum
						from 
							{$this->scheme}.v_{$this->RegistryDataObject} RD
						where
							RD.Registry_id = R.Registry_id
					) RDSum on true
					left join v_PayType pt on pt.PayType_id = R.PayType_id
				where
					R.Registry_id = :Registry_id
			";
		}

		$result = $this->db->query($query, [
			'Registry_id' => $data['Registry_id']
		]);
		
		if ( !is_object($result) ) {
            return ['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'];
			
		}

        $r = $result->result('array');

        if ( is_array($r) && count($r)) {
            return $r;
        }
        
        return [];
	}

    /**
     * Изменение отметки об оплате случаев
     * @param $data
     * @return array|bool
     */
	public function setRegistryDataPaidFromJSON($data)
	{
		if (!empty($data['RegistryDataPaid'])) {
			$RegistryDataPaid = json_decode($data['RegistryDataPaid'],true);

			foreach($RegistryDataPaid as $record) {
				$record['Registry_id'] = $data['Registry_id'];
				$record['pmUser_id'] = $data['pmUser_id'];

				if ( empty($record['RegistryErrorType_Code']) && $record['RegistryData_IsPaid'] == 1 ) {
					if (!empty($data['RegistryStatus_id']) && $data['RegistryStatus_id'] == 2) {
						// При сохранении на все случаи с отметкой об не оплате, у которых ещё нет ошибки, добавляются ошибки с кодом «5.2.2.».
						$record['RegistryErrorType_Code'] = '5.2.2.';
					} else {
						return [['success' => false, 'Error_Msg' => 'Обнаружен случай, отмеченный как неоплаченный, у которого не указан код ошибки ТФОМС']];
					}
				}

				//if($record['RecordStatus_Code']==2) {
				if(!empty($record['RegistryErrorType_Code'])) {
					$response = $this->deleteRegistryDataErrorTFOMS($record);
					if (is_array($response) && count($response) > 0 && !empty($response[0]['Error_Msg'])) {
						return $response;
					}

					$params = $record;
					$params['OSHIB'] = $record['RegistryErrorType_Code'];
					$params['IM_POL'] = null;
					$params['BAS_EL'] = null;
					$params['COMMENT'] = $record['Registry_xmlExportFile'];
					$params['ROWNUM'] = $record['RegistryData_EvnNum'];
					$params['FATALITY'] = 1;

					$response = $this->setErrorFromImportRegistry($params);
					if (!empty($response[0]['Error_Msg'])) {
						return $response;
					}
				}
			}

			$params = [];
			$params['Registry_id'] = $data['Registry_id'];
			$params['pmUser_id'] = $data['pmUser_id'];
			$response = $this->setRegistryPaid($params);
			if (!empty($response[0]['Error_Msg'])) {
				return $response;
			}
		}
		return [['Registry_id' => $data['Registry_id'], 'success' => true]];
	}

    /**
     *    Удаление ошибок
     * @param $data
     * @return bool
     */
	public function deleteRegistryErrorTFOMS($data) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		$this->setRegistryParamsByType($data, true);

		if ( $this->RegistryType_id == 13 ) {
			$filter = "Registry_id in (select Registry_id from {$this->scheme}.v_RegistryGroupLink where Registry_pid = :Registry_id)";
		}
		else {
			$filter = "Registry_id = :Registry_id";
		}

		$query = "delete from {$this->scheme}.RegistryErrorTFOMS where {$filter}";
		$result = $this->db->query($query, $data);

		return true;
	}

    /**
     *    Удаление ошибок
     * @param $data
     * @return bool
     */
	public function deleteRegistryDataErrorTFOMS($data)
	{
		$params = [
			'Registry_id' => $data['Registry_id'],
			'Evn_id' => $data['Evn_id']
		];
		$query = "
			delete from {$this->scheme}.RegistryErrorTFOMS
			where Registry_id = :Registry_id and Evn_id = :Evn_id;
		";
		$result = $this->db->query($query, $params);
		return true;
	}

    /**
     *    Загрузка данных по реестру
     * @param $data
     * @return array|bool
     */
	public function loadRegistryData($data)
	{
		if ($data['Registry_id']==0) {
			return false;
		}
		
		if ($data['RegistryType_id']==0) {
			return false;
		}

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id'=>$data['session']['lpu_id']
		];
		$filter="(1=1)";
		
		if (isset($data['Person_SurName'])) {
			$filter .= " and RD.Person_SurName ilike :Person_SurName ";
			$params['Person_SurName'] = rtrim($data['Person_SurName'])."%";
		}
		if (isset($data['Person_FirName'])) {
			$filter .= " and RD.Person_FirName ilike :Person_FirName ";
			$params['Person_FirName'] = rtrim($data['Person_FirName'])."%";
		}
		
		if (isset($data['Person_SecName'])) {
			$filter .= " and RD.Person_SecName ilike :Person_SecName ";
			$params['Person_SecName'] = rtrim($data['Person_SecName'])."%";
		}
		
		if(!empty($data['Polis_Num'])) {
			$filter .= " and RD.Polis_Num = :Polis_Num";
			$params['Polis_Num'] = $data['Polis_Num'];
		}
		
		if(!empty($data['MedPersonal_id'])) {
			$filter .= " and RD.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}
		
		if(!empty($data['Evn_id'])) {
			$filter .= " and RD.Evn_id = :Evn_id";
			$params['Evn_id'] = $data['Evn_id'];
		}

		if(!empty($data['RegistryData_RowNum'])) {
			$filter .= " and coalesce(RDRN.RegistryData_RowNum, RD.RegistryData_RowNum) = :RegistryData_RowNum";
			$params['RegistryData_RowNum'] = $data['RegistryData_RowNum'];
		}

		if ( !empty($data['filterRecords']) ) {
			if ($data['filterRecords'] == 2) {
				$filter .= " and coalesce(RD.RegistryData_IsPaid,1) = 2";
			} elseif ($data['filterRecords'] == 3) {
				$filter .= " and coalesce(RD.RegistryData_IsPaid,1) = 1";
			}
		}

		$join = "";
		$fields = "";

		if ( in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$join .= "left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id as \"DispClass_id\", ";
		}

		if($data['RegistryType_id'] == 15){
			$join .= "left join v_EvnUslugaPar EUP on EUP.EvnUslugaPar_id = RD.Evn_id ";
			$join .= "left join v_EvnFuncRequest efr on efr.EvnFuncRequest_pid = EUP.EvnDirection_id ";
			$join .= "left join v_EvnLabRequest  elr on elr.EvnDirection_id = EUP.EvnDirection_id ";
			$fields .= "case when efr.EvnFuncRequest_id is not null then 'true' else 'false' end as \"isEvnFuncRequest\", ";
			$fields .= "case when elr.EvnLabRequest_id is not null then 'true' else 'false' end as \"isEvnLabRequest\", ";
			$fields .= "elr.MedService_id as \"MedService_id\", ";
			$fields .= "EUP.EvnDirection_id as \"EvnDirection_id\", ";
		}

		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$filter .= " and {$diagFilter}";
		}
		
		if ( in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) )
		{
			if (isset($data['RegistryStatus_id']) && (6==$data['RegistryStatus_id'])) {
                $source_table = 'v_RegistryDeleted_Data';
            } else {
                $source_table = 'v_' . $this->RegistryDataObject;
            }
            $query = "
				-- addit with 
				with PE (
					Person_id,
					PersonEvn_id,
					PersonEvn_insDT
				) as (
					select
						t1.Person_id,
						t1.PersonEvn_id,
						t1.PersonEvn_insDT
					from v_PersonEvn t1
						inner join {$this->scheme}.v_{$this->RegistryDataObject} t2 on t2.Person_id = t1.Person_id
					where t2.Registry_id = :Registry_id
				)
				-- end addit with 

				Select
					-- select
					RD.Evn_id as \"Evn_id\",
					RD.Evn_rid as \"Evn_rid\",
					RD.EvnClass_id as \"EvnClass_id\",
					RD.Registry_id as \"Registry_id\",
					RD.RegistryType_id as \"RegistryType_id\",
					coalesce(RDRN.RegistryData_RowNum, RD.RegistryData_RowNum) as \"RegistryData_RowNum\",
					RD.Person_id as \"Person_id\",
					RD.Server_id as \"Server_id\",
					PersonEvn.PersonEvn_id as \"PersonEvn_id\",
					{$fields}
					case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\",
					RD.needReform as \"needReform\",
					RD.checkReform as \"checkReform\",
					RD.timeReform as \"timeReform\",
					case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end \"isNoEdit\",
					RD.RegistryData_deleted as \"RegistryData_deleted\",
					RTrim(RD.NumCard) as \"EvnPL_NumCard\",
					RTrim(RD.Person_FIO) as \"Person_FIO\",
					RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
					CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
					RD.LpuSection_id as \"LpuSection_id\",
					RTrim(RD.LpuSection_Name) as \"LpuSection_Name\",
					RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
					RTrim(coalesce(to_char(cast(RD.Evn_setDate as timestamp), 'dd.mm.yyyy'), '')) as \"EvnVizitPL_setDate\",
					RTrim(coalesce(to_char(cast(RD.Evn_disDate as timestamp), 'dd.mm.yyyy'), '')) as \"Evn_disDate\",
					RD.RegistryData_Tariff as \"RegistryData_Tariff\",
					RD.RegistryData_KdFact as \"RegistryData_Uet\",
					RD.RegistryData_KdPay as \"RegistryData_KdPay\",
					RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
					RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
					coalesce(RegistryError.Err_Count, 0) + coalesce(RegistryErrorTFOMS.Err_Count, 0) as \"Err_Count\",
					PMT.PayMedType_Code as \"PayMedType_Code\"
					-- end select
				from
					-- from
					{$this->scheme}.{$source_table} RD
					{$join}
					left join lateral (
						select 
						    RegistryData_RowNum
						from r66.RegistryDataRowNum
						where {$this->RegistryDataEvnField} = RD.Evn_id
							and Registry_id = RD.Registry_id
						order by RegistryDataRowNum_id desc
						limit 1
					) RDRN on true
					left join v_PayMedType PMT on PMT.PayMedType_id = RD.PayMedType_id
					left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
					left join v_Diag D on D.Diag_id = RD.Diag_id
					left join lateral (
						select
						    RDLT.Person_id
                        from
                            {$this->scheme}.RegistryDataLgot RDLT
                        where
                            RD.Person_id = RDLT.Person_id
                        and (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
                        limit 1
					) RDL on true
					left join lateral (
						Select count(*) as Err_Count
						from {$this->scheme}.v_{$this->RegistryErrorObject} RE
						where RD.Evn_id = RE.Evn_id
							and RD.Registry_id = RE.Registry_id
					) RegistryError on true
					left join lateral (
						Select count(*) as Err_Count
						from {$this->scheme}.v_RegistryErrorTFOMS RET
						where RD.Evn_id = RET.Evn_id
							and RD.Registry_id = RET.Registry_id
					) RegistryErrorTFOMS on true
					left join lateral (
						select 
						    PersonEvn_id
						from PE
						where Person_id = RD.Person_id
							and PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.Evn_setDate)
						order by PersonEvn_insDT desc
						limit 1
					) PersonEvn on true
				-- end from
				where
					-- where
					RD.Registry_id = :Registry_id
					and
					{$filter}
					-- end where
				order by
					-- order by
					RD.Person_FIO
					-- end order by
			";
		}
		
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		
		if (!is_object($result)) {
			return false;
		}

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;

    }

    /**
     * Отметки об оплате случаев
     * @param $data
     * @return bool
     */
	public function loadRegistryDataPaid($data)
	{
		// Взависимости от типа реестра возвращаем разные наборы данных
		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['session']['lpu_id']
		];
		$join = "";
		$fields = "";

		$this->setRegistryParamsByType($data);

		if ( in_array($this->RegistryType_id, [7, 9, 12]) ) {
			$join .= "left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$fields .= "epd.DispClass_id, ";
		}

		$query = "
			-- addit with 
			with PE (
				Person_id,
				PersonEvn_id,
				PersonEvn_insDT
			) as (
				select
					t1.Person_id,
					t1.PersonEvn_id,
					t1.PersonEvn_insDT
				from v_PersonEvn t1
					inner join {$this->scheme}.v_{$this->RegistryDataObject} t2 on t2.Person_id = t1.Person_id
				where t2.Registry_id = :Registry_id
			)
			-- end addit with 

			Select
				-- select
				RD.Evn_id as \"Evn_id\",
				RD.Evn_rid as \"Evn_rid\",
				RD.EvnClass_id as \"EvnClass_id\",
				RD.Registry_id as \"Registry_id\",
				RD.RegistryType_id as \"RegistryType_id\",
				RD.Person_id as \"Person_id\",
				RD.Server_id as \"Server_id\",
				PersonEvn.PersonEvn_id as \"PersonEvn_id\",
				{$fields}
				case when RDL.Person_id is null then 0 else 1 end as \"IsRDL\",
				RD.needReform as \"needReform\",
				RD.checkReform as \"checkReform\",
				RD.timeReform as \"timeReform\",
				case when RD.needReform=2 and RegistryQueue.RegistryQueue_id is not null then 2 else 1 end \"isNoEdit\",
				RD.RegistryData_deleted as \"RegistryData_deleted\",
				RTrim(RD.NumCard) as \"EvnPL_NumCard\",
				RD.Person_FirName as \"Person_FirName\",
				RD.Person_SurName as \"Person_SurName\",
				RD.Person_SecName as \"Person_SecName\",
				RD.Polis_Num as \"Polis_Num\",
				RTrim(RD.Person_FIO) as \"Person_FIO\",
				RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'), '')) as \"Person_BirthDay\",
				CASE WHEN RD.Person_IsBDZ = 1 THEN 'true' ELSE 'false' END as \"Person_IsBDZ\",
				RD.LpuSection_id as \"LpuSection_id\",
				RTrim(RD.LpuSection_Name) as \"LpuSection_Name\",
				RTrim(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
				RTrim(coalesce(to_char(cast(RD.Evn_setDate as timestamp), 'dd.mm.yyyy'),'')) as \"EvnVizitPL_setDate\",
				RTrim(coalesce(to_char(cast(RD.Evn_disDate as timestamp), 'dd.mm.yyyy'),'')) as \"Evn_disDate\",
				RD.RegistryData_Tariff as \"RegistryData_Tariff\",
				RD.RegistryData_KdFact as \"RegistryData_Uet\",
				RD.RegistryData_KdPay as \"RegistryData_KdPay\",
				RD.RegistryData_KdPlan as \"RegistryData_KdPlan\",
				RD.RegistryData_ItogSum as \"RegistryData_ItogSum\",
				case when coalesce(RD.RegistryData_IsPaid, 1) = 1 and RET.RegistryErrorType_Code IS NOT NULL then 1 else 2 end as \"RegistryData_IsPaid\", -- отметка неоплаченности только для не оплаченных у которых есть ошибки
				PMT.PayMedType_Code as \"PayMedType_Code\",
				RET.RegistryErrorType_id as \"RegistryErrorType_id\",
				RET.RegistryErrorClass_id as \"RegistryErrorClass_id\",
				RET.RegistryErrorType_Code as \"RegistryErrorType_Code\",
				RET.RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
				null as \"Registry_xmlExportFile\",
				RET.RegistryErrorTFOMS_RowNum as \"RegistryData_EvnNum\",
				1 as RecordStatus_Code
				-- end select
			from
				-- from
				{$this->scheme}.v_{$this->RegistryDataObject} RD
				{$join}
				left join v_PayMedType PMT on PMT.PayMedType_id = RD.PayMedType_id
				left join {$this->scheme}.RegistryQueue on RegistryQueue.Registry_id = RD.Registry_id
				left join lateral (
					select
					    RDLT.Person_id
                    from {$this->scheme}.RegistryDataLgot RDLT
                    where
                        RD.Person_id = RDLT.Person_id
                    and
                        (RD.Evn_id = RDLT.Evn_id or RDLT.Evn_id is null)
                    limit 1
				) RDL on true
				left join lateral (
					select
					    t2.RegistryErrorType_id,
					    coalesce(t1.RegistryErrorClass_id, t2.RegistryErrorClass_id) as RegistryErrorClass_id,
					    t2.RegistryErrorType_Code, t1.RegistryErrorTFOMS_id,
					    t1.RegistryErrorTFOMS_Comment,
					    t1.RegistryErrorTFOMS_RowNum
					from {$this->scheme}.v_RegistryErrorTFOMS t1
						left join {$this->scheme}.v_RegistryErrorType t2 on t2.RegistryErrorType_id = t1.RegistryErrorType_id
					where t1.Registry_id = RD.Registry_id and t1.Evn_id = RD.Evn_id and ISNULL(t1.RegistryErrorClass_id, t2.RegistryErrorClass_id) = 1
					limit 10
				) as RET on true
				left join lateral (
					select PersonEvn_id
					from PE
					where Person_id = RD.Person_id
						and PersonEvn_insDT <= coalesce(RD.Evn_disDate, RD.Evn_setDate)
					order by PersonEvn_insDT desc
					limit 1
				) PersonEvn on true
			-- end from
			where
				-- where
				RD.Registry_id = :Registry_id
				-- end where
			order by
				-- order by
				RD.Person_FIO
				-- end order by
		";

		$this->load->library('textlog', array('file'=>'loadRegistryDataPaid_' . date('Y-m-d') . '.log'));
		$this->textlog->add('');
		$this->textlog->add(getDebugSQL($query, $params));

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return false;
		}

        $resp = $result->result('array');

        $exp = $this->getRegistryExportInfo($data);
        $evn_num_arr = json_decode($exp['Registry_EvnNum'], true);
        
        if (is_array($evn_num_arr)) {
            $evn_num_arr = array_flip($evn_num_arr);
        }
        
        $file_name = null;
        
        if (!empty($exp['Registry_xmlExportFile']) && $exp['Registry_xmlExportFile'] != 1) {
            $arr = explode('/',$exp['Registry_xmlExportFile']);
            $file_name = $arr[count($arr)-1];
            $file_name_array = explode('.', $file_name);
            if ( count($file_name_array) > 1 ) {
                unset($file_name_array[count($file_name_array) - 1]);
                $file_name = implode('.', $file_name_array);
            }
        }
        
        foreach($resp as &$registry_data) {
            $key = $registry_data['Evn_id'];
            if(isset($evn_num_arr[$key]) && empty($registry_data['RegistryData_EvnNum'])) {
                $registry_data['RegistryData_EvnNum'] = $evn_num_arr[$key];
            }
            if (empty($registry_data['Registry_xmlExportFile'])) {
                $registry_data['Registry_xmlExportFile'] = $file_name;
            }
        }

        return $resp;
	}

    /**
     * Данные об экспорте реестра
     * @param $data
     * @return array|bool
     */
	public function getRegistryExportInfo($data)
    {
		$params = ['Registry_id' => $data['Registry_id']];

		$query = "
			select
			    R.Registry_EvnNum as \"Registry_EvnNum\",
			    R.Registry_xmlExportPath as \"Registry_xmlExportFile\"
			from {$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_pid
			where RGL.Registry_id = :Registry_id
			limit 1
		";
		
		$resp = $this->getFirstRowFromQuery($query, $params);
		return $resp;
	}

    /**
     * Добавление ошибки в справочник
     * @param $data
     * @return bool
     */
	public function addRegistryErrorType($data)
    {
		$params = array(
			'RegistryErrorType_Code' => $data['RegistryErrorType_Code'],
			'RegistryErrorType_Name' => $data['RegistryErrorType_Name'],
			'RegistryErrorType_Descr' => !empty($data['RegistryErrorType_Descr']) ? $data['RegistryErrorType_Descr'] : '',
			'RegistryErrorClass_id' => !empty($data['RegistryErrorClass_id']) ? $data['RegistryErrorClass_id'] : 2,
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select
			    RegistryErrorType_id as \"RegistryErrorType_id\",
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_RegistryErrorType_ins
			(
				RegistryErrorType_Code := :RegistryErrorType_Code,
				RegistryErrorType_Name := :RegistryErrorType_Name,
				RegistryErrorType_Descr := :RegistryErrorType_Descr,
				RegistryErrorClass_id := :RegistryErrorClass_id,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $params);
		
		if (!is_object($result)) {
			return false;
		}
		
		return $result->result('array');
	}
	
	/**
	 *	Добавить ошибку для реестра из импортируемого файла 
	 */
	public function setErrorFromImportRegistry($data, $addErrorType = false) 
    {
		if ( empty($data['Registry_id'])) {
			return [['success' => false, 'Error_Msg' => 'Загрузка реестра не возможна!']];
		}

		if ( empty($data['FATALITY']) ) {
			$data['FATALITY'] = 2;
		}

		$query = "
            SELECT
                RegistryErrorClass_id as \"RegistryErrorClass_id\"
            FROM
                {$this->scheme}.v_RegistryErrorClass
            WHERE
                RegistryErrorClass_Code = :FATALITY
            limit 1
        ";
		$resp = $this->db->query($query, $data);

		if ( !is_object($resp) ) {
			return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду класса ошибки ' . $data['FATALITY']]];
		}

		$ret = $resp->result('array');

		if ( !is_array($ret) || count($ret) == 0 ) {
			return [['success' => false, 'Error_Msg' => 'Код класса ошибки ' . $data['FATALITY'] . ' не найден в бд']];
		}

		$data['FATALITY_ID'] = $ret[0]['RegistryErrorClass_id'];

		$query = "
            SELECT
                RegistryErrorType_id as \"RegistryErrorType_id\"
            FROM
                {$this->scheme}.v_RegistryErrorType
            WHERE
                RegistryErrorType_Code = cast(:OSHIB as varchar)
            LIMIT 1
        ";
		$resp = $this->db->query($query, $data);

		if ( !is_object($resp) ) {
			return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных по коду ошибки ' . $data['OSHIB']]];
		}

		$ret = $resp->result('array');

		if ( !is_array($ret) || count($ret) == 0 ) {
			if ( $addErrorType ) {
				$ret = $this->addRegistryErrorType([
					'RegistryErrorType_Code' => $data['OSHIB'],
					'RegistryErrorType_Name' => $data['COMMENT'],
					'RegistryErrorClass_id' => $data['FATALITY_ID'],
					'pmUser_id' => $data['pmUser_id']
				]);

				if ( !$ret ) {
					return [['success' => false, 'Error_Msg' => 'Ошибка при добавлении ошибки с кодом ' . $data['OSHIB'] . ' в справочник']];
				}

				if ( !empty($ret[0]['Error_Msg']) ) {
					return $ret;
				}
			} else {
				return [['success' => false, 'Error_Msg' => 'Код ошибки ' . $data['OSHIB'] . ' не найден в бд']];
			}
		}

		$data['OSHIB_ID'] = $ret[0]['RegistryErrorType_id'];

		if ( empty($data['ROWNUM']) ) {
			$data['ROWNUM'] = null;
		}

		$this->setRegistryParamsByType($data);
		$this->beginTransaction();
		
		$query = "
				insert {$this->scheme}.RegistryErrorTFOMS (
					Registry_id,
					" . ($this->RegistryType_id == 6 ? 'CmpCallCard_id' : 'Evn_id') . ",
					RegistryErrorType_id,
					RegistryErrorType_Code,
					RegistryErrorTFOMS_FieldName,
					RegistryErrorTFOMS_BaseElement,
					RegistryErrorTFOMS_Comment,
					RegistryErrorTFOMS_RowNum,
					RegistryErrorClass_id,
					pmUser_insID,
					pmUser_updID,
					RegistryErrorTFOMS_insDT,
					RegistryErrorTFOMS_updDT
				)
				select
					Registry_id,
					Evn_id,
					:OSHIB_ID as RegistryErrorType_id,
					:OSHIB as RegistryErrorType_Code,
					:IM_POL as RegistryErrorTFOMS_FieldName,
					:BAS_EL as RegistryErrorTFOMS_BaseElement,
					:COMMENT as RegistryErrorTFOMS_Comment,
					:ROWNUM as RegistryErrorTFOMS_RowNum,
					:FATALITY_ID as RegistryErrorClass_id,
					:pmUser_id as pmUser_insID,
					:pmUser_id as pmUser_updID,
					dbo.tzGetDate() as RegistryError_insDT,
					dbo.tzGetDate() as RegistryError_updDT
				from {$this->scheme}.v_{$this->RegistryDataObject}
				where Registry_id = :Registry_id
					and Evn_id = :Evn_id;
		
			returning 0 as \"Error_Code\", '' as \"Error_Msg\"
		";
		$this->commitTransaction();
		return $this->getFirstRowFromQuery($query, $data);
	}

    /**
     *    Идентификация СМО по SMO, SMO_OGRN, SMO_OK
     * @param $data
     * @return bool
     */
	public function identifyOrgSMO($data)
	{
		$query = "
			select 
				smo.OrgSMO_id as \"OrgSMO_id\"
			from
				v_OrgSMO smo
				left join v_Org o on o.Org_id = smo.Org_id
			where
				smo.Orgsmo_f002smocod = :SMO
				OR (o.Org_OGRN = :SMO_OGRN and o.Org_OKATO = :SMO_OK)	
			order by case when smo.Orgsmo_f002smocod = :SMO then 0 else 1 end  -- сначала по Orgsmo_f002smocod, потом по ОГРН и ОКАТО
			limit 1 
			   
		";
		
		$result = $this->db->query($query, $data);
		
        if (is_object($result)) {
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]['OrgSMO_id'];
			}
		}
		return false;
	}

    /**
     *    Получение идентификатор из справочника "Территории страхования"
     * @param $data
     * @return |null
     */
	function getOmsSprTerr($data)
	{
		$query = "
			select
				OmsSprTerr_id as \"OmsSprTerr_id\"
			from
				v_OmsSprTerr
			where
				OmsSprTerr_Code = :OmsSprTerr_Code
			limit 1
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]['OmsSprTerr_id'];
			}
		}
		return null;
	}

    /**
     *    Добавление полиса пациенту
     * @param $data
     * @return bool
     */
	public function addNewPolisToPerson($data)
	{
		$query = "
			 
            select 
                PersonEvn_id as \"PersonEvn_id\" 
            FROM p_PersonPolis_ins
            (
                Server_id := :Server_id,
                Person_id := :Person_id,
                OmsSprTerr_id := :OmsSprTerr_id,
                PolisType_id := :PolisType_id,
                OrgSMO_id := :OrgSMO_id,
                Polis_Ser := :Polis_Ser,
                Polis_Num := :Polis_Num,
                Polis_begDate := :Polis_begDate,
                Polis_endDate := NULL,
                pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $data);

		if (! is_object($result) ) {
            return false;
        }
		
        $resp = $result->result('array');
		
        if (!count($resp)) {
            return false;
        }

        $params = [
            'Evn_id' => $data['Evn_id'],
            'Server_id' => $data['Server_id'],
            'PersonEvn_id' => $resp[0]['PersonEvn_id']
        ];

        // перевязываем случай на новую периодику
        $query = "
            update
                Evn
            set
                PersonEvn_id = :PersonEvn_id,
                Server_id = :Server_id
            where
                Evn_id = :Evn_id
        ";

        $this->db->query($query, $params);

        return true;
	}

    /**
     *    Идентификация типа полиса по VPOLIS
     * @param $data
     * @return bool
     */
	public function identifyPolisType($data)
	{
		$query = "
			select 
				pt.PolisType_id as \"PolisType_id\"
			from
				v_PolisType pt
			where
				pt.PolisType_CodeF008 = :VPOLIS
			limit 1	
		";
		
		$result = $this->db->query($query, $data);
		
		if ( !is_object($result)) {
            return false;
        }

        $row = $result->result('array');
		
        if ( count($row)) {
            return $row[0]['PolisType_id'];
        }
        
        return false;
	}

    /**
     *    Установка реестра в очередь на формирование
     *    Возвращает номер в очереди
     * @param $data
     * @return array
     */
	public function saveRegistryQueue($data)
	{
		// Сохранение нового реестра
		if (0 == $data['Registry_id'])
		{
			$data['Registry_IsActive']=2;
			$operation = 'insert';
		} else {
			$operation = 'update';
		}

		$re = $this->loadRegistryQueue($data);
		if (is_array($re) && (count($re) > 0)) {
			if ($operation=='update')
			{
				if ($re[0]['RegistryQueue_Position']>0)
				{
					return [['success' => false, 'Error_Msg' => '<b>Запрос ЛПУ по данному реестру уже находится в очереди на формирование.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания текущего формирования реестра.']];
				}
			}
		}

		if ($data['Registry_IsFLK']) { // checkbox => YesNo
			$data['Registry_IsFLK'] = 2;
		} else {
			$data['Registry_IsFLK'] = 1;
		}

		$params = [
			'Registry_id' => $data['Registry_id'],
			'Lpu_id' => $data['Lpu_id'],
			'RegistryType_id' => $data['RegistryType_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'RegistryStacType_id' => $data['RegistryStacType_id'],
			'RegistryEventType_id' => $data['RegistryEventType_id'],
			'Registry_begDate' => $data['Registry_begDate'],
			'Registry_endDate' => $data['Registry_endDate'],
			'Registry_Num' => $data['Registry_Num'],
			'Registry_IsActive' => $data['Registry_IsActive'],
			'OrgRSchet_id' => $data['OrgRSchet_id'],
			'Registry_accDate' => $data['Registry_accDate'],
			'Registry_IsRepeated' => (!empty($data['Registry_IsRepeated'])?$data['Registry_IsRepeated']:1),
			'Registry_IsFLK' => (!empty($data['Registry_IsFLK'])?$data['Registry_IsFLK']:1),
			'pmUser_id' => $data['pmUser_id']
		];
		$fields = "";
		
		switch ($data['RegistryType_id'])
		{
			case 1:
				$params['PayType_id'] = (!empty($data['PayType_id'])?$data['PayType_id']:110);
				$fields .= "PayType_id := :PayType_id,";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$fields .= "LpuBuilding_id := :LpuBuilding_id,";
			break;

			case 2:
				$params['PayType_id'] = (!empty($data['PayType_id'])?$data['PayType_id']:110);
				$fields .= "PayType_id := :PayType_id,";
				$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
				$fields .= "LpuBuilding_id := :LpuBuilding_id,";
				// Переформирование по записям, пока только на полке
				if (isset($data['reform']))
				{
					$params['reform'] = $data['reform'];
					$fields .= "reform := :reform,";
				}
			break;

			case 6:
			case 15:
				$params['PayType_id'] = (!empty($data['PayType_id'])?$data['PayType_id']:110);
				$fields .= "PayType_id := :PayType_id,";
			break;
		}

		if ( !in_array($data['RegistryType_id'], $this->getAllowedRegistryTypes()) ) {
			return [['success' => false, 'Error_Msg' => 'Данный функционал пока не доступен!']];
		}

        $query = "
			
                select 
                    RegistryQueue_id as \"RegistryQueue_id\",
                    RegistryQueue_Position as \"RegistryQueue_Position\",
                    Error_Code as \"Error_Code\", 
                    Error_Message as \"Error_Msg\";
				from {$this->scheme}.p_RegistryQueue_ins
                (
                    RegistryStacType_id := :RegistryStacType_id,
                    RegistryEventType_id := :RegistryEventType_id,
                    Registry_id := :Registry_id,
                    RegistryType_id := :RegistryType_id,
                    Lpu_id := :Lpu_id,
                    OrgRSchet_id := :OrgRSchet_id,
                    Registry_begDate := :Registry_begDate,
                    Registry_endDate := :Registry_endDate,
                    Registry_IsRepeated := :Registry_IsRepeated,
                    Registry_IsFLK := :Registry_IsFLK,
                    {$fields}
                    Registry_Num := :Registry_Num,
                    Registry_accDate := dbo.tzGetDate(),
                    RegistryStatus_id := :RegistryStatus_id,
                    pmUser_id := :pmUser_id
                )
			";

        $result = $this->db->query($query, $params);

        if (!is_object($result)) {
            return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];
        }
        
        $resp = $result->result('array');
        
        if (!empty($resp[0]['RegistryQueue_id']) && !empty($data['LpuSection_id'])) {
            $savedLB = [];
            $LpuSection_ids = explode(',', $data['LpuSection_id']);
            if (!empty($data['Registry_id'])) {
                // получаем LpuSection's, которые нужно удалить
                $resp_lb = $this->queryResult("
						select
							RegistryLpuSection_id as \"RegistryLpuSection_id\",
							LpuSection_id as \"LpuSection_id\"
						from
							{$this->scheme}.v_RegistryLpuSection
						where
							Registry_id = :Registry_id
					", ['Registry_id' => $data['Registry_id']]
                );

                // удаляем не нужные
                foreach ($resp_lb as $one_lb) {
                    if (!in_array($one_lb['LpuSection_id'], $LpuSection_ids)) {
                        $this->db->query("
								    select 
								        Error_Code as \"Error_Code\", 
								        Error_Message as \"Error_Msg\"    
								    from  {$this->scheme}.p_RegistryLpuSection_del
								    (
								        RegistryLpuSection_id := :egistryLpuSection_id
									)	
								", ['RegistryLpuSection_id' => $one_lb['RegistryLpuSection_id']]);
                    } else {
                        $savedLB[] = $one_lb['LpuSection_id'];
                    }
                }
            }
            // добавляем нужные
            foreach ($LpuSection_ids as $one) {
                if (!in_array($one, $savedLB)) {
                    $query = "
                        select 
                            RegistryLpuSection_id as \"RegistryLpuSection_id\",
                            Error_Code as \"Error_Code\", 
                            Error_Message as \"Error_Msg\"
                        from {$this->scheme}.p_RegistryLpuSection_ins
                        (
                            RegistryLpuSection_id = NULL,
                            Registry_id := :Registry_id,
                            LpuSection_id := :LpuSection_id,
                            RegistryQueue_id := :RegistryQueue_id,
                            pmUser_id := :pmUser_id,
                        )	       
                    ";
                    
                    $params = [
                        'Registry_id' => $data['Registry_id'],
                        'LpuSection_id' => $one,
                        'RegistryQueue_id' => $resp[0]['RegistryQueue_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ];
                    $this->db->query($query, $params);
                }
            }
        }

        return $result->result('array');
	}

    /**
     *    Какая-то проверка
     * @param $data
     * @return bool
     */
	public function checkErrorDataInRegistry($data)
    {
		$Registry_EvnNum = null;
		$Registry_id = null;
		$RegistryType_id = null;
		
		// достаём массив Registry_EvnNum, ищем Evn_id по N_ZAP
		$query = "
			select
				Registry_EvnNum as \"Registry_EvnNum\",
				RegistryType_id as \"RegistryType_id\"
			from
				{$this->scheme}.v_Registry
			where
				Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$row = $result->result('array');

			if ( count($row) > 0 ) {
				$Registry_EvnNum = $row[0]['Registry_EvnNum'];
				$RegistryType_id = $row[0]['RegistryType_id'];
			}
		}
		
		if ( empty($Registry_EvnNum) ) {
			return false;
		}
		
		$evn_num = json_decode($Registry_EvnNum, true);

		if ( !empty($evn_num[$data['N_ZAP']]) ) {
			$data['Evn_id'] = $evn_num[$data['N_ZAP']];
		}
		else {
			return false;
		}

		$params['Registry_id'] = $data['Registry_id'];
		$params['Evn_id'] = $data['Evn_id'];

		if ( $RegistryType_id == 13 ) {
			// Для объединенного реестра определяем идентификатор и тип простого реестра, в который входит случай
			$query = "
			    select 
			        t.RegistryType_id as \"RegistryType_id\",
                    t.Registry_id as \"Registry_id\"
                from
                (
                    (
                        select
                            r.RegistryType_id,
                            r.Registry_id
                        from
                            {$this->scheme}.v_RegistryGroupLink RGL
                            inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                            inner join {$this->scheme}.v_RegistryData rd on rd.Registry_id = r.Registry_id
                        where
                            RGL.Registry_pid = :Registry_id
                            and RD.Evn_id = :Evn_id
                        limit 1
                    )
                union all
                    (
                        select
                            r.RegistryType_id,
                            r.Registry_id
                        from
                            {$this->scheme}.v_RegistryGroupLink RGL
                            inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                            inner join {$this->scheme}.v_RegistryDataEvnPS rd on rd.Registry_id = r.Registry_id
                        where
                            RGL.Registry_pid = :Registry_id
                            and RD.Evn_id = :Evn_id
                        limit 1
                    )
				union all
				    (
                        select
                            r.RegistryType_id, r.Registry_id
                        from
                            {$this->scheme}.v_RegistryGroupLink RGL
                            inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                            inner join {$this->scheme}.v_RegistryDataDisp rd on rd.Registry_id = r.Registry_id
                        where
                            RGL.Registry_pid = :Registry_id
                            and RD.Evn_id = :Evn_id
                        limit 1
                    )
				union all
                    (
                        select
                            r.RegistryType_id, r.Registry_id
                        from
                            {$this->scheme}.v_RegistryGroupLink RGL
                            inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                            inner join {$this->scheme}.v_RegistryDataProf rd on rd.Registry_id = r.Registry_id
                        where
                            RGL.Registry_pid = :Registry_id
                            and RD.Evn_id = :Evn_id
                        limit 1 
                    )
				union all
				    (
                        select
                            r.RegistryType_id, r.Registry_id
                        from
                            {$this->scheme}.v_RegistryGroupLink RGL
                            inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                            inner join {$this->scheme}.v_RegistryDataCmp rd on rd.Registry_id = r.Registry_id
                        where
                            RGL.Registry_pid = :Registry_id
                            and RD.Evn_id = :Evn_id
                        limit 1
                    )
				union all
				    (
                        select
                            r.RegistryType_id,
                            r.Registry_id
                        from
                            {$this->scheme}.v_RegistryGroupLink RGL
                            inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                            inner join {$this->scheme}.v_RegistryDataPar rd on rd.Registry_id = r.Registry_id
                        where
                            RGL.Registry_pid = :Registry_id
                            and RD.Evn_id = :Evn_id
                                limit 1
				    )
				) t
			";
			$registryInfo = $this->getFirstRowFromQuery($query, $params);

			if ( $registryInfo === false || !is_array($registryInfo) || count($registryInfo) == 0 ) {
				return false;
			}

			$params['Registry_id'] = $registryInfo['Registry_id'];
			$RegistryType_id = $registryInfo['RegistryType_id'];
		} else {
			$params['Registry_id'] = $data['Registry_id'];
		}

		$this->setRegistryParamsByType(['RegistryType_id' => $RegistryType_id], true);

		if ( $RegistryType_id == 6 ) {
			$query = "
				select
					rd.Registry_id as \"Registry_id\",
					ps.PersonEvn_id as \"PersonEvn_id\",
					ccc.Person_id as \"Person_id\",
					ccc.CmpCallCard_id as \"Evn_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pol.OrgSMO_id as \"OrgSMO_id\",
					pol.PolisType_id as \"PolisType_id\",
					to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
				from
					{$this->scheme}.v_{$this->RegistryDataObject} rd
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
					inner join CmpCallCard ccc on ccc.CmpCallCard_id = rd.Evn_id
					left join v_Polis pol on pol.Polis_Ser = ccc.Person_PolisSer and pol.Polis_Num = ccc.Person_PolisNum
					left join v_Person_reg ps on ps.Polis_id = pol.Polis_id
				where
					r.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
			";
		} else {
			$query = "
				select
					rd.Registry_id as \"Registry_id\",
					e.PersonEvn_id as \"PersonEvn_id\",
					e.Person_id as \"Person_id\",
					e.Evn_id as \"Evn_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pol.OrgSMO_id as \"OrgSMO_id\",
					pol.PolisType_id as \"PolisType_id\",
					to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
				from
					{$this->scheme}.v_{$this->RegistryDataObject} rd
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rd.Registry_id
					inner join Evn e on e.Evn_id = rd.Evn_id
					inner join v_Person_reg ps on ps.PersonEvn_id = e.PersonEvn_id AND ps.Server_id = e.Server_id
					left join v_Polis pol on pol.Polis_id = ps.Polis_id
				where
					r.Registry_id = :Registry_id
					and rd.Evn_id = :Evn_id
			";
		}

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			$row = $result->result('array');

			if ( count($row) > 0 ) {
				return $row[0]; // возвращаем данные о случае
			}
		}

		return false;
	}

    /**
     *    Проверка наличия случая в реестре
     * @param $data
     * @return bool
     */
	public function checkErrorDataInRegistryMod($data)
	{
		$Registry_EvnNum = null;
		
		// достаём массив Registry_EvnNum, ищем Evn_id по N_ZAP
		$query = "
			select
				Registry_EvnNum as \"Registry_EvnNum\"
			from
				{$this->scheme}.v_Registry
			where
				Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				$Registry_EvnNum = $row[0]['Registry_EvnNum'];
			}
		}
		
		if (empty($Registry_EvnNum)) {
			return false;
		}
		
		$data['Evn_id'] = null;
		$evn_num = json_decode($Registry_EvnNum, true);
		
		foreach ($evn_num as $key => $value) {
			if ($key == $data['N_ZAP']) {
				$data['Evn_id'] = $value;
			}
		}
		
		if (empty($data['Evn_id'])) {
			return false;
		}

		$params['Registry_id'] = $data['Registry_id'];
		$params['Evn_id'] = $data['Evn_id'];

		$query = "
		    select t.RegistryType_id as \"RegistryType_id\"
		    from (
                (
                    select
                        r.RegistryType_id
                    from
                        {$this->scheme}.v_RegistryGroupLink RGL
                        inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                        inner join {$this->scheme}.v_RegistryData rd on rd.Registry_id = r.Registry_id
                    where
                        RGL.Registry_pid = :Registry_id
                        and RD.Evn_id = :Evn_id
                    limit 1
				)
			union all
                (
                    select
                        r.RegistryType_id
                    from
                        {$this->scheme}.v_RegistryGroupLink RGL
                        inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                        inner join {$this->scheme}.v_RegistryDataEvnPS rd on rd.Registry_id = r.Registry_id
                    where
                        RGL.Registry_pid = :Registry_id
                        and RD.Evn_id = :Evn_id
                        limit 1
                )
    
            union all
                (
                    select
                        r.RegistryType_id
                    from
                        {$this->scheme}.v_RegistryGroupLink RGL
                        inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                        inner join {$this->scheme}.v_RegistryDataDisp rd on rd.Registry_id = r.Registry_id
                    where
                        RGL.Registry_pid = :Registry_id
                        and RD.Evn_id = :Evn_id
                        limit 1
                )
    
            union all
    
                (
                    select
                        r.RegistryType_id
                    from
                        {$this->scheme}.v_RegistryGroupLink RGL
                        inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                        inner join {$this->scheme}.v_RegistryDataProf rd on rd.Registry_id = r.Registry_id
                    where
                        RGL.Registry_pid = :Registry_id
                        and RD.Evn_id = :Evn_id
                        limit 1
                )
    
            union all
    
                (
                    select
                        r.RegistryType_id
                    from
                        {$this->scheme}.v_RegistryGroupLink RGL
                        inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                        inner join {$this->scheme}.v_RegistryDataCmp rd on rd.Registry_id = r.Registry_id
                    where
                        RGL.Registry_pid = :Registry_id
                        and RD.Evn_id = :Evn_id
                        limit 1
                )
    
            union all
    
                (
                    select
                        r.RegistryType_id
                    from
                        {$this->scheme}.v_RegistryGroupLink RGL
                        inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
                        inner join {$this->scheme}.v_RegistryDataPar rd on rd.Registry_id = r.Registry_id
                    where
                        RGL.Registry_pid = :Registry_id
                        and RD.Evn_id = :Evn_id
                        limit 1
                )
			) t
		";
		$registry_type = $this->getFirstResultFromQuery($query, $params);

		$this->setRegistryParamsByType(array('RegistryType_id' => $registry_type), true);

		if ($registry_type == 6) {
			$query = "
				select
					rd.Registry_id as \"Registry_id\",
					ps.PersonEvn_id as \"PersonEvn_id\",
					ccc.Person_id as \"Person_id\",
					ccc.CmpCallCard_id as \"Evn_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pol.OrgSMO_id as \"OrgSMO_id\",
					pol.PolisType_id as \"PolisType_id\",
					to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id
					inner join CmpCallCard ccc on ccc.CmpCallCard_id = rd.Evn_id
					left join v_Polis pol on pol.Polis_Ser = ccc.Person_PolisSer and pol.Polis_Num = ccc.Person_PolisNum
					left join v_Person_reg ps on ps.Polis_id = pol.Polis_id
				where
					RGL.Registry_pid = :Registry_id
					and rd.Evn_id = :Evn_id
			";
		} else {
			$query = "
				select
					rd.Registry_id as \"Registry_id\",
					e.PersonEvn_id as \"PersonEvn_id\",
					e.Person_id as \"Person_id\",
					e.Evn_id as \"Evn_id\",
					pol.Polis_Ser as \"Polis_Ser\",
					pol.Polis_Num as \"Polis_Num\",
					pol.OrgSMO_id as \"OrgSMO_id\",
					pol.PolisType_id as \"PolisType_id\",
					to_char(r.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(pol.Polis_begDate, 'dd.mm.yyyy') as \"Polis_begDate\",
					coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\"
				from
					{$this->scheme}.v_RegistryGroupLink RGL
					inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					inner join {$this->scheme}.v_{$this->RegistryDataObject} rd on rd.Registry_id = r.Registry_id
					inner join Evn e on e.Evn_id = rd.Evn_id
					inner join v_Person_reg ps on ps.PersonEvn_id = e.PersonEvn_id AND ps.Server_id = e.Server_id
					left join v_Polis pol on pol.Polis_id = ps.Polis_id
				where
					RGL.Registry_pid = :Registry_id
					and rd.Evn_id = :Evn_id
			";
		}

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			$row = $result->result('array');
			if ( count($row) > 0 )
			{
				return $row[0]; // возвращаем данные о случае
			}
		}
		
		return false;
	}

    /**
     *    Получение списка ошибок ТФОМС
     * @param $data
     * @return array|bool
     */
	public function loadRegistryErrorTFOMS($data)
	{
		if ($data['Registry_id'] <= 0) {
			return false;
		}
		
		$q = "SELECT RegistryType_id FROM {$this->scheme}.v_Registry WHERE Registry_id = :Registry_id";
		$data['RegistryType_id'] = $this->getFirstResultFromQuery($q, ['Registry_id' => $data['Registry_id']]);

		if (!(($data['start'] >= 0) && ($data['limit'] >= 0))) {
			return false;
		}

		$this->setRegistryParamsByType($data);

		$params = [
			'Registry_id' => $data['Registry_id']
		];
		$filter="(1=1)";
		if (isset($data['Person_SurName'])) {
			$filter .= " and ps.Person_SurName ilike :Person_SurName ";
			$params['Person_SurName'] = $data['Person_SurName']."%";
		}
		if (isset($data['Person_FirName'])) {
			$filter .= " and ps.Person_FirName ilike :Person_FirName ";
			$params['Person_FirName'] = $data['Person_FirName']."%";
		}
		if (isset($data['Person_SecName'])) {
			$filter .= " and ps.Person_SecName ilike :Person_SecName ";
			$params['Person_SecName'] = $data['Person_SecName']."%";
		}
		if (isset($data['RegistryErrorType_Code'])) {
			$filter .= " and RE.RegistryErrorType_Code = :RegistryErrorType_Code ";
			$params['RegistryErrorType_Code'] = $data['RegistryErrorType_Code'];
		}
		if (isset($data['Person_FIO'])) {
			$filter .= " and rtrim(ps.Person_SurName) || ' ' || rtrim(ps.Person_FirName) || ' ' || rtrim(coalesce(ps.Person_SecName, '')) ilike :Person_FIO ";
			$params['Person_FIO'] = $data['Person_FIO']."%";
		}
		if (isset($data['RegistryErrorClass_id'])) {
			$filter .= " and RE.RegistryErrorClass_id = :RegistryErrorClass_id ";
			$params['RegistryErrorClass_id'] = $data['RegistryErrorClass_id'];
		}
		if (!empty($data['Evn_id'])) {
			$filter .= " and RE.Evn_id = :Evn_id ";
			$params['Evn_id'] = $data['Evn_id'];
		}
		
		$addToSelect = "";
		$leftjoin = "";

		if ( !empty($data['RegistryType_id']) && in_array($data['RegistryType_id'], array(7, 9, 12)) ) {
			$leftjoin .= " left join v_EvnPLDisp epd on epd.EvnPLDisp_id = RD.Evn_rid ";
			$addToSelect .= ", epd.DispClass_id as \"DispClass_id\"";
		}

		if ($data['RegistryType_id'] == 6) {
			$evn_object = 'CmpCallCard';
			$evn_field = "
				null as \"Evn_rid\",
				111 as \"EvnClass_id\",
			";
		} else {
			$evn_object = 'Evn';
			$evn_field = "
				Evn.Evn_rid as \"Evn_rid\",
				Evn.EvnClass_id as \"EvnClass_id\",
			";
		}

		$addToSelect .= "
			,case when Evn.{$evn_object}_updDT < RE.RegistryErrorTFOMS_insDT then 1 else 2 end as \"ErrorEdited\"
		";

		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$leftjoin .= "left join v_Diag D on D.Diag_id = RD.Diag_id ";
			$filter .= " and {$diagFilter}";
		}

		$query = "
		Select 
			-- select
			RegistryErrorTFOMS_id as \"RegistryErrorTFOMS_id\",
			RE.Registry_id as \"Registry_id\",
			RD.Evn_id as \"Evn_id\",
			{$evn_field}
			ret.RegistryErrorType_Code as \"RegistryErrorType_Code\",
			rtrim(coalesce(coalesce(ps.Person_SurName, pst.Person_SurName),'')) || ' ' || rtrim(coalesce(coalesce(ps.Person_FirName, pst.Person_FirName),'')) || ' ' || rtrim(coalesce(coalesce(ps.Person_SecName, pst.Person_SecName), '')) as \"Person_FIO\",
			--ps.Person_id,
			coalesce(ps.Person_id, evn.Person_id) as \"Person_id\",
			ps.PersonEvn_id as \"PersonEvn_id\",
			ps.Server_id as \"Server_id\",
			RTrim(coaelsce(to_char(cast(coalesce(ps.Person_BirthDay, pst.Person_BirthDay) as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
			RegistryErrorTFOMS_FieldName as \"RegistryErrorTFOMS_FieldName\",
			RegistryErrorTFOMS_BaseElement as \"RegistryErrorTFOMS_BaseElement\",
			RegistryErrorTFOMS_Comment as \"RegistryErrorTFOMS_Comment\",
			--MP.Person_Fio as MedPersonal_Fio,
			RTRIM(RD.MedPersonal_Fio) as \"MedPersonal_Fio\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			LS.LpuSection_Name as \"LpuSection_Name\",
			coalesce(RD.RegistryData_deleted, 1) as \"RegistryData_deleted\",
			case when RD.Evn_id IS NOT NULL then 1 else 2 end as \"RegistryData_notexist\"
			{$addToSelect}
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryErrorTFOMS RE
			left join {$this->scheme}.v_{$this->RegistryDataObject} RD on RD.Registry_id = RE.Registry_id and RD.Evn_id = RE.{$evn_object}_id
			left join v_{$evn_object} Evn on Evn.{$evn_object}_id = RE.{$evn_object}_id
			--left join v_EvnSection es on ES.EvnSection_pid = RE.Evn_id and ES.EvnSection_Index = ES.EvnSection_Count - 1
			--left join v_EvnVizitPL evpl on evpl.EvnVizitPL_pid = RE.Evn_id and evpl.EvnVizitPL_Index = evpl.EvnVizitPL_Count - 1
			left join v_LpuSection LS on LS.LpuSection_id = RD.LpuSection_id
			left join v_LpuUnit LU on LU.LpuUnit_id = LS.LpuUnit_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = LU.LpuBuilding_id
			/*outer apply(
				select top 1 Person_Fio from v_MedPersonal where MedPersonal_id = ISNULL(ES.MedPersonal_id, evpl.MedPersonal_id)
			) as MP*/
			left join v_Person_all ps on ps.PersonEvn_id = RD.PersonEvn_id and ps.Server_id = RD.Server_id
			left join v_PersonState pst on Evn.Person_id = pst.Person_id
			left join {$this->scheme}.v_RegistryErrorType ret on ret.RegistryErrorType_id = RE.RegistryErrorType_id
			{$leftjoin}
			-- end from
		where
			-- where
			RE.Registry_id=:Registry_id
			and
			{$filter}
			-- end where
		order by
			-- order by
			RE.RegistryErrorType_Code
			-- end order by";

		//echo getDebugSql($query, $params);exit;
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->db->query(getCountSQLPH($query), $params);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		
		if (!is_object($result)) {
            return false;
		}

        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
	}

    /**
     * Удаление объединённого реестра
     * @param $data
     * @return array|bool
     */
	public function deleteUnionRegistry($data)
	{
		if (!empty($data['id'])) {
			// если статус "Отправка данных в РМИС" или "Загружен в РМИС", запретить изменять
			$rcsresp = $this->queryResult("
			select
				r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
			from
				{$this->scheme}.Registry r
				inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				Registry_id = :Registry_id
			", [
				'Registry_id' => $data['id']
			]);
			if (!empty($rcsresp[0]['RegistryCheckStatus_id']) && in_array($rcsresp[0]['RegistryCheckStatus_id'], array(45, 47))) {
				return ['Error_Msg' => 'Удаление запрещено, т.к. статус реестра: ' . $rcsresp[0]['RegistryCheckStatus_Name']];
			}
		}

		// 1. удаляем все связи
		$query = "
			delete {$this->scheme}.RegistryGroupLink
			where Registry_pid = :Registry_id
		";
		$this->db->query($query, [
			'Registry_id' => $data['id']
		]);
		
		// 2. удаляем сам реестр
		$query = "
			select
			    Error_Code as \"Error_Code\",
			    Error_Message as \"Error_Msg\"
			from {$this->scheme}.p_Registry_del
			(
				Registry_id := :Registry_id,
				pmUser_delID := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, [
			 'Registry_id' => $data['id'],
            'pmUser_id' => $data['pmUser_id']
		]);
		
		if (is_object($result)) {
			return $result->result('array');
		}
		
		return false;
	}

    /**
     * Различные региональные проверки перед переформированием
     * @param $data
     * @return array|bool
     */
	public function checkBeforeSaveRegistryQueue($data)
	{
		$result = parent::checkBeforeSaveRegistryQueue($data);

		if ( $result !== true ) {
			return $result;
		}

		$query = "
			select
				R.Registry_id
			from
				{$this->scheme}.v_RegistryGroupLink RGL
				inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_pid
			where
				RGL.Registry_id = :Registry_id
				and R.Registry_xmlExportPath = '1'
			limit 1
		";
		
		$result = $this->db->query($query, $data);
		if (is_object($result)) {
			$resp = $result->result('array');
			if (count($resp) > 0) {
				return [['success' => false, 'Error_Msg' => '<b>По данному реестру формируется выгрузка в XML.</b><br/>Для запуска формирования или переформирования реестра,<br/>дождитесь окончания выгрузки реестра.']];
			}
		}
		
		return true;
	}

    /**
     * Сохранение объединённого реестра
     * @param $data
     * @return array|bool|false
     */
	public function saveUnionRegistry($data)
	{
		if (!empty($data['Registry_id'])) {
			// если статус "Отправка данных в РМИС" или "Загружен в РМИС", запретить изменять
			$rcsresp = $this->queryResult("
                select
                    r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
                    rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
                from
                    {$this->scheme}.Registry r
                    inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
                where
                    Registry_id = :Registry_id
                ", [
                    'Registry_id' => $data['Registry_id']
			]);
			if (!empty($rcsresp[0]['RegistryCheckStatus_id']) && in_array($rcsresp[0]['RegistryCheckStatus_id'], [45, 47])) {
				return ['Error_Msg' => 'Сохранение запрещено, т.к. статус реестра: ' . $rcsresp[0]['RegistryCheckStatus_Name']];
			}
		}

		// Проверяем номер пачки на дубли
		$resp = $this->queryResult("
				select
				    Registry_id as \"Registry_id\"
				from {$this->scheme}.v_Registry r
				where Lpu_id = :Lpu_id
					and to_char(Registry_endDate, 'yyyy-mm') = :Registry_endMonth
					and Registry_FileNum = :Registry_FileNum
					and RegistryGroupType_id = :RegistryGroupType_id
					and Registry_id != ISNULL(:Registry_id, 0)
			", [
				'Lpu_id' => $data['Lpu_id'],
				'Registry_endMonth' => substr($data['Registry_endDate'], 0, 7),
				'Registry_FileNum' => $data['Registry_FileNum'],
				'Registry_id' => $data['Registry_id'],
				'RegistryGroupType_id' => $data['RegistryGroupType_id']
			]
		);
		if ( !empty($resp[0]['Registry_id']) ) {
			return ['Error_Msg' => 'Реестр с таким номером пакета уже существует'];
		}

		// 1. сохраняем объединённый реестр
		$proc = 'p_Registry_ins';
		if (!empty($data['Registry_id'])) {
			$proc = 'p_Registry_upd';
		}
		
		if(!empty($data['Registry_id'])) {
		    $q = "select Registry_Num from {$this->scheme}.v_Registry where Registry_id = :Registry_id";
        } else {
		    $q = "(select coalesce(MAX(coalesce(cast(Registry_Num as bigint), 0)), 0) + 1 from {$this->scheme}.v_Registry where RegistryType_id = 13 and Lpu_id = :Lpu_id and ISNUMERIC(Registry_Num) = 1)";
        }
		
        $data['Registry_Num'] = $this->getFirstResultFromQuery($q, $data);
		
		$query = "
		    select
		        Registry_id as \"Registry_id\",
		        Error_Code as \"Error_Code\",
		        Error_Message as \"Error_Msg\"
			from {$this->scheme}.{$proc}
			(
				Registry_id := :Registry_id,
				RegistryType_id := 13,
				RegistryStatus_id := 1,
				Registry_Sum := NULL,
				Registry_IsActive := 2,
				Registry_Num := :Registry_Num,
				Registry_accDate := :Registry_accDate,
				Registry_begDate := :Registry_begDate,
				Registry_endDate := :Registry_endDate,
				RegistryGroupType_id := :RegistryGroupType_id,
				Lpu_id := :Lpu_id,
				OrgSMO_id := null,
				KatNasel_id := null,
				Registry_FileNum := :Registry_FileNum,
				pmUser_id := :pmUser_id
			)
		";
		
		$result = $this->db->query($query, $data);
		
		if (!is_object($result)) {
			return false;
		}
        $resp = $result->result('array');
		
        if (count($resp) > 0 && !empty($resp[0]['Registry_id'])) {
            // 2. удаляем все связи
            $query = "
					delete {$this->scheme}.RegistryGroupLink
					where Registry_pid = :Registry_id
            ";
            $this->db->query($query, [
                'Registry_id' => $resp[0]['Registry_id']
            ]);

            // 3. выполняем поиск реестров которые войдут в объединённый
            $params = [
                'Lpu_id' => $data['Lpu_id'],
                'Registry_begDate' => $data['Registry_begDate'],
                'Registry_endDate' => $data['Registry_endDate'],
            ];
            $base_filters = 'R.RegistryType_id <> 13';
            $baseQuery = "
					select
						R.Registry_id as \"Registry_id\"
					from
						{$this->scheme}.v_Registry R
						left join v_PayType PT on PT.PayType_id = R.PayType_id
					where
						R.RegistryStatus_id = 2 -- к оплате
						and R.Lpu_id = :Lpu_id
						and R.Registry_begDate >= :Registry_begDate
						and R.Registry_endDate <= :Registry_endDate
						and not exists(select RegistryGroupLink_id from {$this->scheme}.v_RegistryGroupLink where Registry_id = R.Registry_id limit 1)
				";
            $queryArray = [];

            switch ( $data['RegistryGroupType_id'] ) {
                case 12: // Реестры ОМС
                    // Если выбрано значение “Реестры ОМС”, то в объединенный реестр должны попадать все реестры диспансеризаций/осмотров
                    $queryArray[] = $baseQuery . "and R.RegistryType_id in (7, 9, 11, 12, 15)";
                    // ... и реестры по поликлинике, стационару и СМП, у которых указан “Вид оплаты” “ОМС”. 
                    $queryArray[] = $baseQuery . "and R.RegistryType_id in (1, 2, 6) and PT.PayType_SysNick = 'oms'";
                    break;

                case 13: // Реестры СЗЗ
                    // Если выбрано значение “Реестры СЗЗ”, то в объединенный реестр должны попадать реестры по поликлинике и стационару, у которых указан
                    // “Вид оплаты” “Местный бюджет”.
                    $registryTypeArray = [1, 2, 15];

                    if ( substr($data['Registry_endDate'], 0, 4) >= 2017 ) {
                        $registryTypeArray[] = 6;
                    }

                    $queryArray[] = $baseQuery . "and R.RegistryType_id in (" . implode(',', $registryTypeArray) . ") and PT.PayType_SysNick = 'bud'";
                    break;

                case 14: // Реестры СМП сверх базовые
                    // Если выбрано значение “Реестры СМП сверх базовые”, то в объединенный реестр должны попадать реестры по СМП, у которых указан
                    // “Вид оплаты” “Местный бюджет”.
                    $queryArray[] = $baseQuery . "and R.RegistryType_id = 6 and PT.PayType_SysNick = 'bud'";
                    break;
            }

            if ( !count($queryArray)) {
                return false;
            }

            $query = implode(' union all ', $queryArray);
            $result_reg = $this->db->query($query, $params);

            if (is_object($result_reg)) {
                $resp_reg = $result_reg->result('array');
                // 4. сохраняем новые связи
                foreach($resp_reg as $one_reg) {
                    $query = "
                        select
                            RegistryGroupLink_id as \"RegistryGroupLink_id\",
                            Error_Code as \"Error_Code\",
                            Error_Message as \"Error_Msg\"
                        from {$this->scheme}.p_RegistryGroupLink_ins
                        (
                            RegistryGroupLink_id := null,
                            Registry_pid := :Registry_pid,
                            Registry_id := :Registry_id,
                            pmUser_id := :pmUser_id
                        )
                    ";

                    $this->db->query($query, [
                        'Registry_pid' => $resp[0]['Registry_id'],
                        'Registry_id' => $one_reg['Registry_id'],
                        'pmUser_id' => $data['pmUser_id']
                    ]);
                }
            }

            // пишем информацию о формировании реестра в историю
            $this->dumpRegistryInformation([
                'Registry_id' => $resp[0]['Registry_id']
            ], 1);
        }

        return $resp;
	}

    /**
     * Получение номера объединённого реестра
     * @param $data
     * @return int
     */
	public function getUnionRegistryNumber($data)
	{
		$query = "
			select
				coalesce(MAX(coalesce(cast(Registry_Num as bigint), 0)),0) + 1 as \"Registry_Num\"
			from
				{$this->scheme}.v_Registry
			where
				RegistryType_id = 13
				and Lpu_id = :Lpu_id
				and ISNUMERIC(Registry_Num) = 1
		";
		
		$result = $this->db->query($query, $data);
		
		if ( is_object($result) ) {
			$resp = $result->result('array');
			if (count($resp) > 0 && !empty($resp[0]['Registry_Num'])) {
				return $resp[0]['Registry_Num'];
			}
		}
		
		return 1;
	}

    /**
     * Загрузка формы редактирования объединённого реестра
     * @param $data
     * @return bool|array
     */
	public function loadUnionRegistryEditForm($data)
	{
		$query = "
			select
				R.Registry_id as \"Registry_id\",
				R.RegistryGroupType_id as \"RegistryGroupType_id\",
				R.Registry_Num as \"Registry_Num\",
				R.Registry_FileNum as \"Registry_FileNum\",
				to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
				to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
				to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
				R.Lpu_id as \"Lpu_id\"
			from
				{$this->scheme}.v_Registry R
			where
				R.Registry_id = :Registry_id
		";
		
		$result = $this->db->query($query, $data);
		
		if (is_object($result)) {
			return $result->result('array');
		}
		
		return false;
	}

    /**
     * Загрузка списка объединённых реестров
     * @param $data
     * @return array|bool
     */
	public function loadUnionRegistryGrid($data)
	{
		$query = "
		Select
			-- select
			R.Registry_id as \"Registry_id\",
			R.Registry_Num as \"Registry_Num\",
			R.Registry_FileNum as \"Registry_FileNum\",
			RGT.RegistryGroupType_Name as \"RegistryGroupType_Name\",
			to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
			to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
			to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
			coalesce(RS.Registry_RecordCount, 0) as \"Registry_RecordCount\",
			r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
			rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
			--ISNULL(RS.Registry_SumPaid, 0.00) as Registry_SumPaid
			-- end select
		from 
			-- from
			{$this->scheme}.v_Registry R -- объединённый реестр
			left join v_RegistryGroupType RGT on RGT.RegistryGroupType_id = R.RegistryGroupType_id
			left join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			left join lateral(
				select
					SUM(coalesce(R2.Registry_SumPaid, 0)) as Registry_SumPaid,
					SUM(coalesce(R2.Registry_RecordCount, 0)) as Registry_RecordCount
				from {$this->scheme}.v_Registry R2
					inner join {$this->scheme}.v_RegistryGroupLink RGL on R2.Registry_id = RGL.Registry_id
				where
					RGL.Registry_pid = R.Registry_id
			) RS on true
			-- end from
		where
			-- where
			R.Lpu_id = :Lpu_id
			and R.RegistryType_id = 13
			-- end where
		order by
			-- order by
			R.Registry_id desc
			-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
		$result_count = $this->db->query(getCountSQLPH($query), $data);

		if (is_object($result_count)) {
			$cnt_arr = $result_count->result('array');
			$count = $cnt_arr[0]['cnt'];
			unset($cnt_arr);
		} else {
			$count = 0;
		}
		
		if (!is_object($result)) {
            return false;
		}
		
        $response = [];
        $response['data'] = $result->result('array');
        $response['totalCount'] = $count;
        return $response;
	}

    /**
     * Загрузка списка обычных реестров, входящих в объединённый
     * @param $data
     * @return array|bool
     */
	public function loadUnionRegistryChildGrid($data)
	{
		$query = "
		Select 
			-- select
			R.Registry_id as \"Registry_id\",
			R.Registry_Num as \"Registry_Num\",
			to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
			to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
			to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
			RT.RegistryType_Name as \"RegistryType_Name\",
			coalesce(R.Registry_Sum, 0.00) as \"Registry_Sum\",
			--ISNULL(R.Registry_SumPaid, 0.00) as Registry_SumPaid,
			PT.PayType_Name as \"PayType_Name\",
			LB.LpuBuilding_Name as \"LpuBuilding_Name\",
			to_char(R.Registry_updDT, 'dd.mm.yyyy') as \"Registry_updDate\"
			-- end select
		from 
			-- from
			{$this->scheme}.v_RegistryGroupLink RGL
			inner join {$this->scheme}.v_Registry R on R.Registry_id = RGL.Registry_id -- обычный реестр
			left join v_RegistryType RT on RT.RegistryType_id = R.RegistryType_id
			left join v_PayType PT on PT.PayType_id = R.PayType_id
			left join v_LpuBuilding LB on LB.LpuBuilding_id = R.LpuBuilding_id
			-- end from
		where
			-- where
			RGL.Registry_pid = :Registry_pid
			-- end where
		order by
			-- order by
			R.Registry_id
			-- end order by";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
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
			$response = [];
			$response['data'] = $result->result('array');
			$response['totalCount'] = $count;
			return $response;
		} 
        return false;
	}


	/**
	 *	Получение списка дополнительных полей для выборки
	 */
	public function getReformRegistryAdditionalFields()
    {
		return ',Registry_IsRepeated, case when Registry_IsFLK = 2 then 1 else 0 end as \"Registry_IsFLK\"';
	}

    /**
     *    Получение данных для выгрузки реестров в XML
     * @param $type
     * @param $data
     * @param $number
     * @param $Registry_EvnNum
     * @param $file_re_data_name
     * @param $file_re_pers_data_name
     * @param bool $registryIsUnion
     * @return bool|int
     */
	function loadRegistryDataForXmlUsing($type, $data, &$number, &$Registry_EvnNum, $file_re_data_name, $file_re_pers_data_name, $registryIsUnion = true)
	{
		$dbreg = $this->load->database('registry', true); // получаем коннект к БД
		//$dbreg->close(); // коннект должен быть закрыт
		//$dbreg->char_set = "windows-1251"; // ставим правильную кодировку (файл выгружается в windows-1251)

		$person_data_template_body = "registry_ekb_person_body" . $data['TemplateModificator'];
		$registry_data_template_body = "registry_ekb_pl_body" . $data['TemplateModificator'];

		$simpleRegistryModificator = "";

		$this->setRegistryParamsByType([
			'RegistryType_id' => $type
		], true);

		switch ( $type ) {
			case 1: //stac
				$object = "EvnPS";
			break;

			case 2: //polka
				$object = "EvnPL";
			break;

			case 6: //smp
				$object = "SMP";
			break;

			case 7: //dd
				$object = "EvnPLDD13";
			break;

			case 9: //orp
				$object = "EvnPLOrp13";
			break;

			case 11: //prof
				$object = "EvnPLProf";
			break;

			case 12: //teen inspection
				$object = "EvnPLProfTeen";
			break;

			case 14: //htm
				$object = "EvnHTM";
			break;

			case 15: //parka
				$object = "EvnUslugaPar";
			break;

			default:
				return false;
			break;
		}

		$p_vizit = $this->scheme.".p_Registry_".$object.$simpleRegistryModificator."_expVizit";
		$p_usl = $this->scheme.".p_Registry_".$object.$simpleRegistryModificator."_expUsl";
		$p_pers = $this->scheme.".p_Registry_".$object.$simpleRegistryModificator."_expPac";

		if ( in_array($type, array(1, 2, 14)) ) {
			$p_ds2 = $this->scheme.".p_Registry_".$object.$simpleRegistryModificator."_expDS2";
		}

		if ( in_array($type, array(1, 2, 14)) ) {
			$p_ds3 = $this->scheme.".p_Registry_".$object.$simpleRegistryModificator."_expDS3";
		}

		if ( in_array($type, array(7, 9, 11)) ) {
			$p_naz = $this->scheme.".p_Registry_".$object.$simpleRegistryModificator."_expNAZ";
		}

		// люди
		$query = "
			select {$p_pers} (Registry_id := ?)
		";
		$result_pac = $dbreg->query($query, array($data['Registry_id']));
		if (!is_object($result_pac)) {
			return false;
		}

		// посещения
		$query = "
			select {$p_vizit} (:Registry_id := ?)
		";
		$result_sluch = $dbreg->query($query, [$data['Registry_id']]);
		if (!is_object($result_sluch)) {
			return false;
		}

		// услуги
		$query = "
			select {$p_usl} (Registry_id := ?)
		";
		$result_usl = $dbreg->query($query, [$data['Registry_id']]);
		if (!is_object($result_usl)) {
			return false;
		}
		
		$DS2 = [];
		$DS3 = [];
		$NAZ = [];
		$PACIENT = [];
		$USL = [];
		$ZAP = [];

		$refuseValue = toAnsi('ОТКАЗ', true);
		$unknownValue = toAnsi('НЕИЗВЕСТНЫЙ', true);

		// диагнозы
		if (!empty($p_ds2)) {
			$query = "
				select {$p_ds2} (Registry_id := ?)
			";
			$result_ds2 = $dbreg->query($query, [$data['Registry_id']]);
			if (!is_object($result_ds2)) {
				return false;
			}
			
			while ($diag = $result_ds2->_fetch_assoc()) {
				array_walk_recursive($diag, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($DS2[$diag['Evn_id']]) ) {
					$DS2[$diag['Evn_id']] = [];
				}

				$DS2[$diag['Evn_id']][] = $diag;
			}
		}

		if (!empty($p_ds3)) {
			$query = "
				select {$p_ds3} (Registry_id := ?)
			";
			$result_ds3 = $dbreg->query($query, [$data['Registry_id']]);
			if (!is_object($result_ds3)) {
				return false;
			}
			while ($diag = $result_ds3->_fetch_assoc()) {
				array_walk_recursive($diag, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($DS3[$diag['Evn_id']]) ) {
					$DS3[$diag['Evn_id']] = [];
				}

				$DS3[$diag['Evn_id']][] = $diag;
			}
		}

		if (!empty($p_naz)) {
			$query = "
				select {$p_naz} (Registry_id := ?)
			";
			$result_naz = $dbreg->query($query, array($data['Registry_id']));
			if (!is_object($result_naz)) {
				return false;
			}
			while ($row = $result_naz->_fetch_assoc()) {
				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);
				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = [];
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		// Формируем массив пациентов
		while ($pers = $result_pac->_fetch_assoc()) {
			array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);

			$pers['DOST'] = [];
			$pers['DOST_P'] = [];

			/**
			 * @task https://redmine.swan.perm.ru/issues/59102
			 */
			if ( empty($pers['FAM']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 2];
			}

			if ( empty($pers['IM']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 3];
			}

			if ( empty($pers['OT']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 1];
			}

			// https://redmine.swan.perm.ru/issues/69377
			// https://redmine.swan.perm.ru/issues/99607 x2
			// https://redmine.swan.perm.ru//issues/108925 ограничил поликлиникой и стационаром
			// https://redmine.swan.perm.ru/issues/116906 учел выгрузку простых реестров по поликлинику и стационару
			if ( ($registryIsUnion === false || $data['RegistryGroupType_id'] == 13) && in_array($type, array(1, 2)) && $pers['FAM'] == $unknownValue && empty($pers['IM']) && empty($pers['OT']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 7];

				// @task https://redmine.swan.perm.ru/issues/131979
				$pers['DOCTYPE'] = '';
				$pers['DOCSER'] = '';
				$pers['DOCNUM'] = '';
				$pers['SNILS'] = '';
			}

			if ( $pers['NOVOR'] != '0' ) {
				if ( empty($pers['FAM_P']) ) {
					$pers['DOST_P'][] = array('DOST_P_VAL' => 2);
				}

				if ( empty($pers['IM_P']) ) {
					$pers['DOST_P'][] = array('DOST_P_VAL' => 3);
				}

				if ( empty($pers['OT_P']) ) {
					$pers['DOST_P'][] = array('DOST_P_VAL' => 1);
				}
			}

			$PACIENT[$pers['Evn_id']] = $pers;
		}

		// Формируем массив услуг
		$IDSERV = 0;
		while ($usluga = $result_usl->_fetch_assoc()) {
			array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);

			if ( !isset($USL[$usluga['Evn_id']]) ) {
				$USL[$usluga['Evn_id']] = array();
			}

			$uslugaCount = (!empty($usluga['EvnUsluga_kolvo']) && $usluga['EvnUsluga_kolvo'] >= 1 ? $usluga['EvnUsluga_kolvo'] : 1);

			for ( $i = 1; $i <= $uslugaCount; $i++ ) {
				$IDSERV++;
				$usluga['IDSERV'] = $IDSERV;
				$USL[$usluga['Evn_id']][] = $usluga;
			}
		}

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
		$altKeys = [
			 'LPU_USL' => 'LPU'
			,'LPU_1_USL' => 'LPU_1'
			,'PODR_USL' => 'PODR'
			,'PROFIL_USL' => 'PROFIL'
			,'DATE_IN_USL' => 'DATE_IN'
			,'DATE_OUT_USL' => 'DATE_OUT'
			,'DS_USL' => 'DS'
			,'PRVS_USL' => 'PRVS'
			,'CODE_MD_USL' => 'CODE_MD'
		];

		$SD_Z = 0;
		$_MP_OTK_Pol_UslugaComplex = [];
		$_MP_OTK_Pol_UslugaComplexLoaded = false;

		// Идём по случаям, как набираем 1000 записей -> пишем сразу в файл.
		while ($visit = $result_sluch->_fetch_assoc()) {
			if ( empty($visit['Evn_id']) ) {
				continue;
			}
			array_walk_recursive($visit, 'ConvertFromUTF8ToWin1251', true);

			$key = $visit['Evn_id'];

			if ( !empty($visit['COMENTSL']) && strlen($visit['COMENTSL']) > 250 ) {
				$visit['COMENTSL'] = substr($visit['COMENTSL'], -250);
			}

			// Привязываем услуги
			if ( isset($USL[$key]) ) {
				// @task https://redmine.swan.perm.ru/issues/63735

				// Добавил условие "Дневной стационар"
				// @task https://redmine.swan.perm.ru/issues/68391
				// Только до 2016 года
				// @task https://redmine.swan.perm.ru/issues/98112
				if ( $visit['USL_OK'] == 2 && $visit['DATE_2'] < '2016-01-01' ) {
					// Сначала ищем КСГ 99/100 и считаем услуги A18.05.002/A18.30.001
					$arrayKSG99 = [];
					$arrayKSG100 = [];
					$arrayUsluga99 = [];
					$arrayUsluga100 = [];
					$countKSG99 = 0;
					$countKSG100 = 0;
					$countUsluga99 = 0;
					$countUsluga100 = 0;

					foreach ( $USL[$key] as $index => $usluga ) {
						switch ( $usluga['CODE_USL'] ) {
							case '99':
								$countKSG99++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG99) ) {
									$arrayKSG99[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayKSG99[$usluga['DATE_IN_USL']]++;
							break;

							case '100':
								$countKSG100++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG100) ) {
									$arrayKSG100[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayKSG100[$usluga['DATE_IN_USL']]++;
							break;

							case 'A18.05.002':
								$countUsluga99++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayUsluga99) ) {
									$arrayUsluga99[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayUsluga99[$usluga['DATE_IN_USL']]++;
							break;

							case 'A18.30.001':
								$countUsluga100++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayUsluga100) ) {
									$arrayUsluga100[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayUsluga100[$usluga['DATE_IN_USL']]++;
							break;
						}
					}

					// Добавляем услуги КСГ
					if ( $countKSG99 < $countUsluga99 || $countKSG100 < $countUsluga100 ) {
						foreach ( $USL[$key] as $index => $usluga ) {
							switch ( $usluga['CODE_USL'] ) {
								case 'A18.05.002':
									if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG99) ) {
										$arrayKSG99[$usluga['DATE_IN_USL']] = 0;
									}

									if ( $arrayKSG99[$usluga['DATE_IN_USL']] < $arrayUsluga99[$usluga['DATE_IN_USL']] ) {
										$arrayKSG99[$usluga['DATE_IN_USL']]++;
										$IDSERV++;

										$usluga['IDSERV'] = $IDSERV;
										$usluga['CODE_USL'] = '99';
										$usluga['RAZDEL_USL'] = '201';
										$USL[$key][] = $usluga;
									}
								break;

								case 'A18.30.001':
									if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG100) ) {
										$arrayKSG100[$usluga['DATE_IN_USL']] = 0;
									}

									if ( $arrayKSG100[$usluga['DATE_IN_USL']] < $arrayUsluga100[$usluga['DATE_IN_USL']] ) {
										$arrayKSG100[$usluga['DATE_IN_USL']]++;
										$IDSERV++;

										$usluga['IDSERV'] = $IDSERV;
										$usluga['CODE_USL'] = '100';
										$usluga['RAZDEL_USL'] = '201';
										$USL[$key][] = $usluga;
									}
								break;
							}
						}
					}
				}

				$visit['USL'] = $USL[$key];

				unset($USL[$key]);
			}
			
			if ( !isset($visit['USL']) ) {
				//$visit['USL'] = $this->getEmptyUslugaXmlRow();
				$visit['USL'] = [];
			}

			$MesCodeListForRequiredUslugaComplex = $this->config->item('MesCodeListForRequiredUslugaComplex');

			// @task https://redmine.swan.perm.ru/issues/112237
			// Если код МЭС 4153 или 4154, добиваем список услуг отказами
			// Только для поликлиники
			if ( $type == 2 && !empty($MesCodeListForRequiredUslugaComplex) && is_array($MesCodeListForRequiredUslugaComplex) && in_array($visit['CODE_MES1'], $MesCodeListForRequiredUslugaComplex) ) {
				// Выгребаем список идентификаторов оказанных услуг
				$uslugaComplexList = array();

				foreach ( $visit['USL'] as $usluga ) {
					if ( !empty($usluga['UslugaComplex_id']) && !in_array($usluga['UslugaComplex_id'], $uslugaComplexList) ) {
						$uslugaComplexList[] = $usluga['UslugaComplex_id'];
					}
				}

				// Тянем список обязательных услуг с объема МР_ОТК_Пол по своей МО
				if ( $_MP_OTK_Pol_UslugaComplexLoaded === false ) {
					$query = "
						with t1 as (
							select
							    av.AttributeValue_id
							from v_AttributeValue av
							where
								av.AttributeValue_TableName = 'dbo.VolumeType'
								and av.AttributeValue_TablePKey = 121
								and av.AttributeValue_rid is null
						),
						t2 as (
							select
								coalesce(av.AttributeValue_rid, av.AttributeValue_id) as AttributeValue_rid
							from v_AttributeValue av
								inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								inner join t1 on t1.AttributeValue_id = ISNULL(av.AttributeValue_rid, av.AttributeValue_id)
							where
								a.Attribute_SysNick = 'Lpu'
								and av.AttributeValue_ValueIdent = :Lpu_id
						)

						select
							attr_mes.AttributeValue_ValueString as \"Mes_Code\",
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							(select Lpu_f003mcod from v_Lpu where Lpu_id = :Lpu_id limit 1) as \"LPU\", -- task #112237
							uc.UslugaComplex_Code as \"CODE_USL\",
							lsp.LpuSectionProfile_Code as \"PROFIL\",
							mso.MedSpecOms_Code as \"PRVS\",
							wp.CodeDLO as \"CODE_MD_USL\",
							ls.Lpusection_Code as \"PODR_USL\",
							to_char(attr_mes.AttributeValue_begDate, 'yyyy-mm-dd') as \"AttributeValue_begDate\",
							to_char(attr_mes.AttributeValue_endDate, 'yyyy-mm-dd') as \"AttributeValue_endDate\"
						from t2
							inner join lateral (
								select AttributeValue_ValueString, AttributeValue_begDate, AttributeValue_endDate
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'MesOld'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_mes on true
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'UslugaComplex'
                                and
                                    coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_uc on true
							inner join UslugaComplex uc on uc.UslugaComplex_id = attr_uc.AttributeValue_ValueIdent
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'LpuSectionProfile'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_lsp on true
							inner join LpuSectionProfile lsp on lsp.LpuSectionProfile_id = attr_lsp.AttributeValue_ValueIdent
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'MedStaffFact'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_msf on true
							inner join v_MedStaffFact msf on msf.MedStaffFact_id = attr_msf.AttributeValue_ValueIdent
							left join MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
							left join persis.v_WorkPlace wp on wp.WorkPlace_id = msf.MedStaffFact_id
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'LpuSection'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_ls on true
							inner join v_Lpusection ls on ls.LpuSection_id = attr_ls.AttributeValue_ValueIdent
					";

					$_MP_OTK_Pol_UslugaComplex = $this->queryResult($query, [
						'Lpu_id' => $data['Lpu_id'],
						'DATE_2' => $visit['DATE_2'],
					]);

					if ( $_MP_OTK_Pol_UslugaComplex === false ) {
						$_MP_OTK_Pol_UslugaComplex = [];
					}
				
					$_MP_OTK_Pol_UslugaComplexLoaded = true;
				}

				// Если услуга из полученного списка на дату окончания случая ($visit['DATE_2']) не входит в $uslugaComplexList, добавляем услугу-отказ
				foreach ( $_MP_OTK_Pol_UslugaComplex as $rec ) {
					// Если МЭС не совпадает с МЭС случая...
					if ( $rec['Mes_Code'] != $visit['CODE_MES1'] ) {
						// на следующую итерацию
						continue;
					}
					// Если услуга есть в выгрузке...
					else if ( in_array($rec['UslugaComplex_id'], $uslugaComplexList) ) {
						// на следующую итерацию
						continue;
					}
					// Если запись из полученного списка не актуальна на дату окончания случая...
					else if (
						$rec['AttributeValue_begDate'] > $visit['DATE_2']
						|| (!empty($rec['AttributeValue_endDate']) && $rec['AttributeValue_endDate'] < $visit['DATE_2'])
					) {
						// на следующую итерацию
						continue;
					}

					$usluga = $this->getEmptyUslugaXmlRow();
					$usluga = $usluga[0];

					$IDSERV++;

					$usluga['IDSERV'] = $IDSERV;
					$usluga['LPU_USL'] = $rec['LPU'];
					//$usluga['LPU_1_USL'] = $visit['LPU_1']; // Не заполняем
					$usluga['PODR_USL'] = (!empty($rec['PODR']) ? $rec['PODR'] : null);
					$usluga['PROFIL_USL'] = (!empty($rec['PROFIL']) ? $rec['PROFIL'] : null);
					$usluga['DATE_IN_USL'] = $visit['DATE_2'];
					$usluga['DATE_OUT_USL'] = $visit['DATE_2'];
					$usluga['DS_USL'] = $visit['DS1'];
					$usluga['CODE_USL'] = (!empty($rec['CODE_USL']) ? $rec['CODE_USL'] : null);
					$usluga['KOL_USL'] = 1;
					$usluga['PRVS_USL'] = (!empty($rec['PRVS']) ? $rec['PRVS'] : null);
					$usluga['CODE_MD_USL'] = (!empty($rec['CODE_MD']) ? $rec['CODE_MD'] : null);
					//$usluga['NPR_DC_USL'] = $visit['NPR_DC']; // Не заполняем
					//$usluga['DST_MO'] = $visit['DST_MO']; // Не заполняем
					$usluga['NPL'] = null; // Не заполняем
					//$usluga['DENTAL'] = $visit['DENTAL'];
					$usluga['COMENTU'] = $refuseValue;

					// Код дернул из хранимки
					if ( in_array($visit['PayType_id'], [110, 112]) ) {
						$usluga['RAZDEL_USL'] = $this->getFirstResultFromQuery("
							select
							    ucp.UslugaComplexPartition_Code
                            from
                                r66.UslugaComplexPartitionLink ucpl
                                inner join r66.UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
                            where ucpl.UslugaComplex_id = :UslugaComplex_id
                            and ucpl.UslugaComplexPartitionLink_begDT <= :DATE_2
                            and (ucpl.UslugaComplexPartitionLink_endDT > :DATE_2 or ucpl.UslugaComplexPartitionLink_endDT is null)
                            and ucpl.PayType_id = :PayType_id
                            limit 1
							", [
								'UslugaComplex_id' => $rec['UslugaComplex_id'],
								'DATE_2' => $visit['DATE_2'],
								'PayType_id' => $visit['PayType_id'],
							]
						);

						if ( $usluga['RAZDEL_USL'] === false ) {
							$usluga['RAZDEL_USL'] = null;
						}
					}

					$visit['USL'][] = $usluga;
				}
			}

			// Привязываем диагнозы
			if ( isset($DS2[$key]) ) {
				$visit['DS2_DATA'] = $DS2[$key];
			} else if ( !empty($visit['DS2']) ) {
				$visit['DS2_DATA'] = [['DS2' => $visit['DS2'], 'DS2_PR' => (!empty($visit['DS2_PR']) ? $visit['DS2_PR'] : null)]];
			} else {
				$visit['DS2_DATA'] = [];
			}

			if ( isset($DS3[$key]) ) {
				$visit['DS3_DATA'] = $DS3[$key];
			} else if ( !empty($visit['DS3']) ) {
				$visit['DS3_DATA'] = [['DS3' => $visit['DS3']]];
			} else {
				$visit['DS3_DATA'] = [];
			}

			if ( isset($NAZ[$key]) ) {
				$visit['NAZ'] = $NAZ[$key];
			} else {
				$visit['NAZ'] = [];
			}

			unset($visit['DS2']);
			unset($visit['DS3']);

			$OS_SLUCH = [];

			if ( !empty($PACIENT[$key]['OS_SLUCH']) ) {
				$OS_SLUCH[] = ['OS_SLUCH_VAL' => $PACIENT[$key]['OS_SLUCH']];
				unset($PACIENT[$key]['OS_SLUCH']);
			}

			if ( !empty($PACIENT[$key]['OS_SLUCH1']) ) {
				$OS_SLUCH[] = ['OS_SLUCH_VAL' => $PACIENT[$key]['OS_SLUCH1']];
				unset($PACIENT[$key]['OS_SLUCH1']);
			}

			$visit['OS_SLUCH'] = $OS_SLUCH;

			$number++;

			$visit['IDCASE'] = $number;
			$PACIENT[$key]['ID_PAC'] = $number;

			$ZAP[$key] = [
				'N_ZAP' => $number,
				'PR_NOV' => $visit['PR_NOV'],
				'PACIENT' => [$PACIENT[$key]],
				'SLUCH' => [$visit]
			];

			$Registry_EvnNum[$number] = $key;

			if ( $registryIsUnion == true ) {
				$query = "
                    update
                        rd
                    set
                        rd.{$this->RegistryDataObject}_RowNum = :RegistryData_RowNum
                    from
                        {$this->scheme}.{$this->RegistryDataObject} rd
                        inner join {$this->scheme}.v_RegistryGroupLink rgl on rgl.Registry_id = rd.Registry_id
                    where
                        rgl.Registry_pid = :Registry_id
                        and rd.{$this->RegistryDataEvnField} = :Evn_id
					returning 0 as \"Error_Code\", '' as \"Error_Msg\"
				";
				
			} else {
				$query = "
                    update {$this->scheme}.{$this->RegistryDataObject}
						set {$this->RegistryDataObject}_RowNum = :RegistryData_RowNum
                    where
                        Registry_id = :Registry_id
                    and {$this->RegistryDataEvnField} = :Evn_id
	                returning 0 as \"Error_Code\", '' as \"Error_Msg\"
				";
			}

			// Проапдейтить поле RegistryData_RowNum
			$result = $this->db->query($query, [
				'Registry_id' => $data['Registry_id'],
				'Evn_id' => $key,
				'RegistryData_RowNum' => $number
			]);

			if ( !is_object($result) ) {
				return false;
			}

			$res = $result->result('array');

			if ( !is_array($res) || count($res) == 0 || !empty($res[0]['Error_Msg']) ) {
				return false;
			}

			if (count($ZAP) >= 1000) {
				$SD_Z += count($ZAP);
				// пишем в файл
				$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, ['ZAP' => $ZAP], true, false, $altKeys);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);
				unset($xml);
				unset($ZAP);
				$ZAP = [];
			}
		}

		if (count($ZAP) > 0) {
			$SD_Z += count($ZAP);
			// пишем в файл
			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, ['ZAP' => $ZAP], true, false, $altKeys);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);
			unset($xml);
			unset($ZAP);
		}

		unset($DS2);
		unset($DS3);
		unset($NAZ);
		unset($USL);

		$toFile = [];
		foreach($PACIENT as $onepac) {
			$toFile[] = $onepac;
			if (count($toFile) >= 1000) {
				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, ['PACIENT' => $toFile], true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($toFile);
				$toFile = [];
			}
		}
		if (count($toFile) > 0) {
			// пишем в файл
			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, ['PACIENT' => $toFile], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($toFile);
		}

		unset($toFile);
		unset($PACIENT);

		return $SD_Z;
	}

    /**
     *    Получение данных для выгрузки реестров в XML c 1.06.2018
     * @param $type
     * @param $data
     * @param $number
     * @param $Registry_EvnNum
     * @param $file_re_data_name
     * @param $file_re_pers_data_name
     * @param bool $registryIsUnion
     * @return bool|int
     */
	public function loadRegistryDataForXmlUsing2018($type, $data, &$number, &$Registry_EvnNum, $file_re_data_name, $file_re_pers_data_name, $registryIsUnion = true)
	{
		if ( empty($this->_exportTimeStamp) ) {
			$this->_exportTimeStamp = time();
		}

		$this->db->save_queries = false;
		$this->load->library('textlog', ['file'=>'exportRegistryToXml_' . date('Y_m_d') . '.log']);

		$person_data_template_body = "registry_ekb_person_body" . $data['TemplateModificator'];
		$registry_data_template_body = "registry_ekb_pl_body" . $data['TemplateModificator'];

		$this->setRegistryParamsByType([
			'RegistryType_id' => $type
		], true);

		switch ( $type ) {
			case 1: //stac
				$object = "EvnPS";
			break;

			case 2: //polka
				$object = "EvnPL";
			break;

			case 6: //smp
				$object = "SMP";
			break;

			case 7: //dd
				$object = "EvnPLDD13";
			break;

			case 9: //orp
				$object = "EvnPLOrp13";
			break;

			case 11: //prof
				$object = "EvnPLProf";
			break;

			case 12: //teen inspection
				$object = "EvnPLProfTeen";
			break;

			case 14: //htm
				$object = "EvnHTM";
			break;

			case 15: //parka
				$object = "EvnUslugaPar";
			break;

			default:
				$this->textlog->add('Не определен тип реетра');
				return false;
			break;
		}

        $p_pers = $this->scheme.".p_Registry_".$object."_expPac";


        if (in_array($type, [1, 2])) {
            $p_vizit = $this->scheme . ".p_Registry_" . $object . "_expVizit_2018";
        } else {
            $p_vizit = $this->scheme . ".p_Registry_" . $object . "_expVizit";
        }

        if (in_array($type, [1])) {
            $p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl_2018";
        } else {
            $p_usl = $this->scheme . ".p_Registry_" . $object . "_expUsl";
        }


        if (in_array($type, [1, 2, 14])) {
            $p_ds2 = $this->scheme . ".p_Registry_" . $object . "_expDS2";
        }

        if (in_array($type, [1, 2, 14])) {
            $p_ds3 = $this->scheme . ".p_Registry_" . $object . "_expDS3";
        }

        if (in_array($type, [7, 9, 11])) {
            $p_naz = $this->scheme . ".p_Registry_" . $object . "_expNAZ";
        }

        if (in_array($type, [1, 2])) {
            $p_bdiag = $this->scheme . ".p_Registry_" . $object . "_expBDIAG_2018";
        }

        if (in_array($type, [1])) {
            $p_onkousl = $this->scheme . ".p_Registry_" . $object . "_expONKOUSL_2018";
        }

		if ( in_array($type, [1, 2, 7, 9, 11, 12, 15]) ) {
			if ($data['registryIsAfter20180601'] === true && in_array($type, [1, 2])) {
				$p_napr = $this->scheme . ".p_Registry_" . $object . "_expNAPR_2018";
			}

			$p_cons = $this->scheme . ".p_Registry_{$object}_expCONS_2018";
			if ($type == 1) {
				$p_lek_pr = $this->scheme . ".p_Registry_{$object}_expLEK_PR_2018";
			}
		}

		if ( in_array($type, array(1, 2)) ) {
			$p_bprot = $this->scheme.".p_Registry_".$object."_expBPROT_2018";
		}
		// люди
		$query = "
			select {$p_pers} (Registry_id := ?)
		";
		$result_pac = $this->db->query($query, [$data['Registry_id']]);
		if (!is_object($result_pac)) {
			$this->textlog->add("Ошибка при выполнении процедуры {$p_pers}");
			return false;
		}

		// посещения
		$query = "
			select {$p_vizit}(Registry_id := ?)
		";
		$result_sluch = $this->db->query($query, array($data['Registry_id']));
		if (!is_object($result_sluch)) {
			$this->textlog->add("Ошибка при выполнении процедуры {$p_vizit}");
			return false;
		}

		// услуги
		$query = "
			select {$p_usl}( Registry_id := ?)
		";
		$result_usl = $this->db->query($query, array($data['Registry_id']));
		if (!is_object($result_usl)) {
			$this->textlog->add("Ошибка при выполнении процедуры {$p_usl}");
			return false;
		}

		$BDIAG = [];
		$BPROT = [];
		$CONS = [];
		$DS2 = [];
		$DS3 = [];
		$NAPR = [];
		$NAZ = [];
		$LEK_PR = [];
		$ONKUSL = [];
		$PACIENT = [];
		$USL = [];
		$ZAP = [];

		$rowNumArray = [];
		$rowNumInsertQuery = "
				insert into r66.RegistryDataRowNum (Registry_id, {$this->RegistryDataEvnField}, RegistryData_RowNum, RegistryDataRowNum_Session)
				values
				{values_array}
				returning 0 as \"Error_Code\", '' as \"Error_Msg\"
		";

		$refuseValue = toAnsi('ОТКАЗ', true);
		$unknownValue = toAnsi('НЕИЗВЕСТНЫЙ', true);

		// диагнозы
		if (!empty($p_ds2)) {
			$query = "
				select {$p_ds2} (Registry_id = ?)
			";
			$result_ds2 = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_ds2)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_ds2}");
				return false;
			}
			while ($diag = $result_ds2->_fetch_assoc()) {

				array_walk_recursive($diag, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS2[$diag['Evn_id']]) ) {
					$DS2[$diag['Evn_id']] = [];
				}

				$DS2[$diag['Evn_id']][] = $diag;
			}
		}

		if (!empty($p_ds3)) {
			$query = "
				select {$p_ds3}( Registry_id := ?)
			";
			$result_ds3 = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_ds3)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_ds3}");
				return false;
			}
			while ($diag = $result_ds3->_fetch_assoc()) {

				array_walk_recursive($diag, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($DS3[$diag['Evn_id']]) ) {
					$DS3[$diag['Evn_id']] = [];
				}

				$DS3[$diag['Evn_id']][] = $diag;
			}
		}

		if (!empty($p_naz)) {
			$query = "
				select {$p_naz}(Registry_id := ?)
			";
			$result_naz = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_naz)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_naz}");
				return false;
			}
			while ($row = $result_naz->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAZ[$row['Evn_id']]) ) {
					$NAZ[$row['Evn_id']] = [];
				}

				$NAZ[$row['Evn_id']][] = $row;
			}
		}

		if (!empty($p_napr)) {
			$query = "
				select {$p_napr}(Registry_id := ?)
			";
			$result_napr = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_napr)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_napr}");
				return false;
			}
			while ($row = $result_napr->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($NAPR[$row['Evn_id']]) ) {
					$NAPR[$row['Evn_id']] = [];
				}

				$NAPR[$row['Evn_id']][] = $row;
			}
		}

		if (!empty($p_bdiag)) {
			$query = "
				select {$p_bdiag} (Registry_id := ?)
			";
			$result_bdiag = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_bdiag)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_bdiag}");
				return false;
			}
			while ($row = $result_bdiag->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if(!empty($row['DIAG_TIP']) || !empty($row['DIAG_CODE']) || !empty($row['DIAG_RSLT']) || !empty($row['DIAG_DATE'])){
					if ( !isset($BDIAG[$row['Evn_id']]) ) {
						$BDIAG[$row['Evn_id']] = [];
					}

					$BDIAG[$row['Evn_id']][] = $row;
				}

			}
		}

		if (!empty($p_bprot)) {
			$query = "
				select {$p_bprot} (Registry_id := ?)
			";
			$result_bprot = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_bprot)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_bprot}");
				return false;
			}
			while ($row = $result_bprot->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($BPROT[$row['Evn_id']]) ) {
					$BPROT[$row['Evn_id']] = [];
				}

				$BPROT[$row['Evn_id']][] = $row;
			}
		}

		// сведения о проведении консилиума (CONS)
		if (!empty($p_cons)) {
			$query = "
				select {$p_cons} (Registry_id := ?)
			";
			$result_cons = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_cons)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_cons}");
				return false;
			}
			while ($row = $result_cons->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($CONS[$row['Evn_id']]) ) {
					$CONS[$row['Evn_id']] = [];
				}

				$CONS[$row['Evn_id']][] = $row;
			}
		}

		// Сведения о введенном противоопухолевом лекарственном препарате (LEK_PR)
		if ( !empty($p_lek_pr) ) {
			$query = "
				select {$p_lek_pr}(Registry_id := ?)
			";
			$result_lek_pr = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_lek_pr)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_lek_pr}");
				return false;
			}
			while ($row = $result_lek_pr->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if (!isset($LEK_PR[$row['EvnUsluga_id']])) {
					$LEK_PR[$row['EvnUsluga_id']] = [];
				}

				$LEK_PR[$row['EvnUsluga_id']][] = $row;
			}
		}

		// Сведения об услуге при лечении онкологического заболевания (ONKOUSL)
		if (!empty($p_onkousl)) {
			$query = "
				select {$p_onkousl} (Registry_id := ?)
			";
			$result_onkousl = $this->db->query($query, [$data['Registry_id']]);
			if (!is_object($result_onkousl)) {
				$this->textlog->add("Ошибка при выполнении процедуры {$p_onkousl}");
				return false;
			}
			while ($row = $result_onkousl->_fetch_assoc()) {

				array_walk_recursive($row, 'ConvertFromUTF8ToWin1251', true);

				if ( !isset($ONKUSL[$row['Evn_id']]) ) {
					$ONKUSL[$row['Evn_id']] = [];
				}

				$row['IDSERV_ONK'] = null;
				$row['LEK_PR_DATA'] = [];

				if (isset($row['EvnUsluga_id']) && isset($LEK_PR[$row['EvnUsluga_id']]) && in_array($row['USL_TIP'], [2, 4])) {
					$LEK_PR_DATA = [];

					foreach ($LEK_PR[$row['EvnUsluga_id']] as $rowTmp) {
						if (!isset($LEK_PR_DATA[$rowTmp['REGNUM']])) {
							$LEK_PR_DATA[$rowTmp['REGNUM']] = $rowTmp;
							unset($LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ']);
						}

						$LEK_PR_DATA[$rowTmp['REGNUM']]['DATE_INJ_DATA'][] = ['DATE_INJ' => $rowTmp['DATE_INJ']];
					}

					$row['LEK_PR_DATA'] = $LEK_PR_DATA;
					unset($LEK_PR[$row['EvnUsluga_id']]);
				}

				$ONKUSL[$row['Evn_id']][] = $row;
			}
		}

		$this->textlog->add("Чистим r66.RegistryDataRowNum по объединенному реестру {$data['Registry_id']}, тип предварительных: {$type}...");

		$result = $this->getFirstRowFromQuery("
				delete from r66.RegistryDataRowNum where Registry_id in (
					select rgl.Registry_id
					from {$this->scheme}.v_RegistryGroupLink as rgl
						inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
					where
						rgl.Registry_pid = :Registry_pid
						and r.RegistryType_id = :RegistryType_id
				)
                returning 0 as \"Error_Code\", '' as \"Error_Msg\"
		", [
			'Registry_pid' => $data['Registry_id'],
			'RegistryType_id' => $type,
		]);

		if ( $result === false || !is_array($result) || !empty($result['Error_Msg']) ) {
			$this->textlog->add("Ошибка при выполнении запроса");
			$this->textlog->add(print_r($result, true));
			return false;
		}

		$this->textlog->add("... выполнено");

		// Формируем массив пациентов
		while ($pers = $result_pac->_fetch_assoc()) {

			array_walk_recursive($pers, 'ConvertFromUTF8ToWin1251', true);

			$pers['DOST'] = [];
			$pers['DOST_P'] = [];

			/**
			 * @task https://redmine.swan.perm.ru/issues/59102
			 */
			if ( empty($pers['FAM']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 2];
			}

			if ( empty($pers['IM']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 3];
			}

			if ( empty($pers['OT']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 1];
			}

			// https://redmine.swan.perm.ru/issues/69377
			// https://redmine.swan.perm.ru/issues/99607 x2
			// https://redmine.swan.perm.ru//issues/108925 ограничил поликлиникой и стационаром
			// https://redmine.swan.perm.ru/issues/116906 учел выгрузку простых реестров по поликлинику и стационару
			if ( ($registryIsUnion === false || $data['RegistryGroupType_id'] == 13) && in_array($type, [1, 2]) && $pers['FAM'] == $unknownValue && empty($pers['IM']) && empty($pers['OT']) ) {
				$pers['DOST'][] = ['DOST_VAL' => 7];

				// @task https://redmine.swan.perm.ru/issues/131979
				$pers['DOCTYPE'] = '';
				$pers['DOCSER'] = '';
				$pers['DOCNUM'] = '';
				$pers['SNILS'] = '';
			}

			if ( $pers['NOVOR'] != '0' ) {
				if ( empty($pers['FAM_P']) ) {
					$pers['DOST_P'][] = ['DOST_P_VAL' => 2];
				}

				if ( empty($pers['IM_P']) ) {
					$pers['DOST_P'][] = ['DOST_P_VAL' => 3];
				}

				if ( empty($pers['OT_P']) ) {
					$pers['DOST_P'][] = ['DOST_P_VAL' => 1];
				}
			}

			$PACIENT[$pers['Evn_id']] = $pers;
		}

		// Формируем массив услуг
		$IDSERV = 0;
		while ($usluga = $result_usl->_fetch_assoc()) {

			array_walk_recursive($usluga, 'ConvertFromUTF8ToWin1251', true);

			if ( !isset($USL[$usluga['Evn_id']]) ) {
				$USL[$usluga['Evn_id']] = [];
			}

			$uslugaCount = (!empty($usluga['EvnUsluga_kolvo']) && $usluga['EvnUsluga_kolvo'] >= 1 ? $usluga['EvnUsluga_kolvo'] : 1);

			for ( $i = 1; $i <= $uslugaCount; $i++ ) {
				$IDSERV++;
				$usluga['IDSERV'] = $IDSERV;
				$USL[$usluga['Evn_id']][] = $usluga;
			}
		}

		// Соответствие полей в выгрузке и необходимых названий тегов в XML-файле
		// Реализовано, т.к. парсер неправильно подставляет значения для полей с одинаковыми названиями в блоках SLUCH и USL
		$altKeys = [
            'LPU_USL' => 'LPU',
            'LPU_1_USL' => 'LPU_1',
            'PODR_USL' => 'PODR',
            'PROFIL_USL' => 'PROFIL',
            'PROFIL_K_USL' => 'PROFIL_K',
            'P_CEL_USL' => 'P_CEL',
            'DATE_IN_USL' => 'DATE_IN',
            'DATE_OUT_USL' => 'DATE_OUT',
            'DS_USL' => 'DS',
            'PRVS_USL' => 'PRVS',
            'CODE_MD_USL' => 'CODE_MD',
            'IDSERV_ONK' => 'IDSERV'
		];

		$SD_Z = 0;
		$_MP_OTK_Pol_UslugaComplex = [];
		$_MP_OTK_Pol_UslugaComplexLoaded = false;
		$ONK_SL_FIELDS = array('DS1_T', 'STAD', 'ONK_T', 'ONK_N', 'ONK_M', 'MTSTZ');

		// Идём по случаям, как набираем 1000 записей -> пишем сразу в файл.
		while ($visit = $result_sluch->_fetch_assoc()) {

			array_walk_recursive($visit, 'ConvertFromUTF8ToWin1251', true);

			if ( empty($visit['Evn_id']) ) {
				continue;
			}

			$key = $visit['Evn_id'];

			if ( !empty($visit['COMENTSL']) && strlen($visit['COMENTSL']) > 250 ) {
				$visit['COMENTSL'] = substr($visit['COMENTSL'], -250);
			}

			// Привязываем услуги
			if ( isset($USL[$key]) ) {
				// @task https://redmine.swan.perm.ru/issues/63735

				// Добавил условие "Дневной стационар"
				// @task https://redmine.swan.perm.ru/issues/68391
				// Только до 2016 года
				// @task https://redmine.swan.perm.ru/issues/98112
				if ( $visit['USL_OK'] == 2 && $visit['DATE_2'] < '2016-01-01' ) {
					// Сначала ищем КСГ 99/100 и считаем услуги A18.05.002/A18.30.001
					$arrayKSG99 = [];
					$arrayKSG100 = [];
					$arrayUsluga99 = [];
					$arrayUsluga100 = [];
					$countKSG99 = 0;
					$countKSG100 = 0;
					$countUsluga99 = 0;
					$countUsluga100 = 0;

					foreach ( $USL[$key] as $index => $usluga ) {
						switch ( $usluga['CODE_USL'] ) {
							case '99':
								$countKSG99++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG99) ) {
									$arrayKSG99[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayKSG99[$usluga['DATE_IN_USL']]++;
							break;

							case '100':
								$countKSG100++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG100) ) {
									$arrayKSG100[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayKSG100[$usluga['DATE_IN_USL']]++;
							break;

							case 'A18.05.002':
								$countUsluga99++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayUsluga99) ) {
									$arrayUsluga99[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayUsluga99[$usluga['DATE_IN_USL']]++;
							break;

							case 'A18.30.001':
								$countUsluga100++;

								if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayUsluga100) ) {
									$arrayUsluga100[$usluga['DATE_IN_USL']] = 0;
								}

								$arrayUsluga100[$usluga['DATE_IN_USL']]++;
							break;
						}
					}

					// Добавляем услуги КСГ
					if ( $countKSG99 < $countUsluga99 || $countKSG100 < $countUsluga100 ) {
						foreach ( $USL[$key] as $index => $usluga ) {
							switch ( $usluga['CODE_USL'] ) {
								case 'A18.05.002':
									if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG99) ) {
										$arrayKSG99[$usluga['DATE_IN_USL']] = 0;
									}

									if ( $arrayKSG99[$usluga['DATE_IN_USL']] < $arrayUsluga99[$usluga['DATE_IN_USL']] ) {
										$arrayKSG99[$usluga['DATE_IN_USL']]++;
										$IDSERV++;

										$usluga['IDSERV'] = $IDSERV;
										$usluga['CODE_USL'] = '99';
										$usluga['RAZDEL_USL'] = '201';
										$USL[$key][] = $usluga;
									}
								break;

								case 'A18.30.001':
									if ( !array_key_exists($usluga['DATE_IN_USL'], $arrayKSG100) ) {
										$arrayKSG100[$usluga['DATE_IN_USL']] = 0;
									}

									if ( $arrayKSG100[$usluga['DATE_IN_USL']] < $arrayUsluga100[$usluga['DATE_IN_USL']] ) {
										$arrayKSG100[$usluga['DATE_IN_USL']]++;
										$IDSERV++;

										$usluga['IDSERV'] = $IDSERV;
										$usluga['CODE_USL'] = '100';
										$usluga['RAZDEL_USL'] = '201';
										$USL[$key][] = $usluga;
									}
								break;
							}
						}
					}
				}

				$visit['USL'] = $USL[$key];

				unset($USL[$key]);
			}

			if ( !isset($visit['USL']) ) {
				//$visit['USL'] = $this->getEmptyUslugaXmlRow();
				$visit['USL'] = [];
			}

			$MesCodeListForRequiredUslugaComplex = $this->config->item('MesCodeListForRequiredUslugaComplex');

			// @task https://redmine.swan.perm.ru/issues/112237
			// Если код МЭС 4153 или 4154, добиваем список услуг отказами
			// Только для поликлиники
			if ( $type == 2 && !empty($MesCodeListForRequiredUslugaComplex) && is_array($MesCodeListForRequiredUslugaComplex) && in_array($visit['CODE_MES1'], $MesCodeListForRequiredUslugaComplex) ) {
				// Выгребаем список идентификаторов оказанных услуг
				$uslugaComplexList = array();

				foreach ( $visit['USL'] as $usluga ) {
					if ( !empty($usluga['UslugaComplex_id']) && !in_array($usluga['UslugaComplex_id'], $uslugaComplexList) ) {
						$uslugaComplexList[] = $usluga['UslugaComplex_id'];
					}
				}

				// Тянем список обязательных услуг с объема МР_ОТК_Пол по своей МО
				if ( $_MP_OTK_Pol_UslugaComplexLoaded === false ) {
					$query = "
						with t1 as (
							select av.AttributeValue_id
							from v_AttributeValue av
							where
								av.AttributeValue_TableName = 'dbo.VolumeType'
								and av.AttributeValue_TablePKey = 121
								and av.AttributeValue_rid is null
						),
						t2 as (
							select
								coalesce(av.AttributeValue_rid, av.AttributeValue_id) as AttributeValue_rid
							from v_AttributeValue av
								inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								inner join t1 on t1.AttributeValue_id = coalesce(av.AttributeValue_rid, av.AttributeValue_id)
							where
								a.Attribute_SysNick = 'Lpu'
								and av.AttributeValue_ValueIdent = :Lpu_id
						)

						select
							attr_mes.AttributeValue_ValueString as \"Mes_Code\",
							uc.UslugaComplex_id as \"UslugaComplex_id\",
							(select Lpu_f003mcod from v_Lpu where Lpu_id = :Lpu_id limit 1) as LPU, -- task #112237
							uc.UslugaComplex_Code as \"CODE_USL\",
							lsp.LpuSectionProfile_Code as \"PROFIL\",
							mso.MedSpecOms_Code as \"PRVS\",
							wp.CodeDLO as \"CODE_MD_USL\",
							ls.Lpusection_Code as \"PODR_USL\",
							to_char(attr_mes.AttributeValue_begDate, 'yyyy-mm-dd') as \"AttributeValue_begDate\",
							to_char(attr_mes.AttributeValue_endDate, 'yyyy-mm-dd') as \"AttributeValue_endDate\"
						from t2
							inner join lateral (
								select AttributeValue_ValueString, AttributeValue_begDate, AttributeValue_endDate
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'MesOld'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_mes on true
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'UslugaComplex'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_uc on true
							inner join UslugaComplex uc on uc.UslugaComplex_id = attr_uc.AttributeValue_ValueIdent
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'LpuSectionProfile'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_lsp on true
							inner join LpuSectionProfile lsp on lsp.LpuSectionProfile_id = attr_lsp.AttributeValue_ValueIdent
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'MedStaffFact'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
								limit 1
							) attr_msf on true
							inner join v_MedStaffFact msf on msf.MedStaffFact_id = attr_msf.AttributeValue_ValueIdent
							left join MedSpecOms mso on mso.MedSpecOms_id = msf.MedSpecOms_id
							left join persis.v_WorkPlace wp on wp.WorkPlace_id = msf.MedStaffFact_id
							inner join lateral (
								select AttributeValue_ValueIdent
								from v_AttributeValue av
									inner join v_Attribute a on a.Attribute_id = av.Attribute_id
								where
									a.Attribute_SysNick = 'LpuSection'
									and coalesce(av.AttributeValue_rid, av.AttributeValue_id) = t2.AttributeValue_rid
									limit 1
							) attr_ls on true
							inner join v_Lpusection ls on ls.LpuSection_id = attr_ls.AttributeValue_ValueIdent
					";

					$_MP_OTK_Pol_UslugaComplex = $this->queryResult($query, [
						'Lpu_id' => $data['Lpu_id'],
						'DATE_2' => $visit['DATE_2'],
					]);

					if ( $_MP_OTK_Pol_UslugaComplex === false ) {
						$_MP_OTK_Pol_UslugaComplex = [];
					}

					$_MP_OTK_Pol_UslugaComplexLoaded = true;
				}

				// Если услуга из полученного списка на дату окончания случая ($visit['DATE_2']) не входит в $uslugaComplexList, добавляем услугу-отказ
				foreach ( $_MP_OTK_Pol_UslugaComplex as $rec ) {
					// Если МЭС не совпадает с МЭС случая...
					if ( $rec['Mes_Code'] != $visit['CODE_MES1'] ) {
						// на следующую итерацию
						continue;
					} else if ( in_array($rec['UslugaComplex_id'], $uslugaComplexList) ) { // Если услуга есть в выгрузке...
					    
						// на следующую итерацию
						continue;
					} else if (// Если запись из полученного списка не актуальна на дату окончания случая...
						$rec['AttributeValue_begDate'] > $visit['DATE_2']
						|| (!empty($rec['AttributeValue_endDate']) && $rec['AttributeValue_endDate'] < $visit['DATE_2'])
					) {
						// на следующую итерацию
						continue;
					}

					$usluga = $this->getEmptyUslugaXmlRow();
					$usluga = $usluga[0];

					$IDSERV++;

					$usluga['IDSERV'] = $IDSERV;
					$usluga['LPU_USL'] = $rec['LPU'];
					//$usluga['LPU_1_USL'] = $visit['LPU_1']; // Не заполняем
					$usluga['PODR_USL'] = (!empty($rec['PODR']) ? $rec['PODR'] : null);
					$usluga['PROFIL_USL'] = (!empty($rec['PROFIL']) ? $rec['PROFIL'] : null);
					$usluga['DATE_IN_USL'] = $visit['DATE_2'];
					$usluga['DATE_OUT_USL'] = $visit['DATE_2'];
					$usluga['DS_USL'] = $visit['DS1'];
					$usluga['CODE_USL'] = (!empty($rec['CODE_USL']) ? $rec['CODE_USL'] : null);
					$usluga['KOL_USL'] = 1;
					$usluga['PRVS_USL'] = (!empty($rec['PRVS']) ? $rec['PRVS'] : null);
					$usluga['CODE_MD_USL'] = (!empty($rec['CODE_MD']) ? $rec['CODE_MD'] : null);
					//$usluga['NPR_DC_USL'] = $visit['NPR_DC']; // Не заполняем
					//$usluga['DST_MO'] = $visit['DST_MO']; // Не заполняем
					$usluga['NPL'] = null; // Не заполняем
					//$usluga['DENTAL'] = $visit['DENTAL'];
					$usluga['COMENTU'] = $refuseValue;

					// Код дернул из хранимки
					if ( in_array($visit['PayType_id'], [110, 112]) ) {
						$usluga['RAZDEL_USL'] = $this->getFirstResultFromQuery("
							select ucp.UslugaComplexPartition_Code
								from r66.UslugaComplexPartitionLink ucpl
									inner join r66.UslugaComplexPartition ucp on ucp.UslugaComplexPartition_id = ucpl.UslugaComplexPartition_id
								where ucpl.UslugaComplex_id = :UslugaComplex_id
									and ucpl.UslugaComplexPartitionLink_begDT <= :DATE_2
									and (ucpl.UslugaComplexPartitionLink_endDT > :DATE_2 or ucpl.UslugaComplexPartitionLink_endDT is null)
									and ucpl.PayType_id = :PayType_id
								limit 1
							", [
								'UslugaComplex_id' => $rec['UslugaComplex_id'],
								'DATE_2' => $visit['DATE_2'],
								'PayType_id' => $visit['PayType_id'],
							]
						);

						if ( $usluga['RAZDEL_USL'] === false ) {
							$usluga['RAZDEL_USL'] = null;
						}
					}

					$visit['USL'][] = $usluga;
				}
			}

			// Привязываем диагнозы
			if ( isset($DS2[$key]) ) {
				$visit['DS2_DATA'] = $DS2[$key];
			} else if ( !empty($visit['DS2']) ) {
				$visit['DS2_DATA'] = [['DS2' => $visit['DS2'], 'DS2_PR' => (!empty($visit['DS2_PR']) ? $visit['DS2_PR'] : null)]];
			} else {
				$visit['DS2_DATA'] = [];
			}

			if ( isset($DS3[$key]) ) {
				$visit['DS3_DATA'] = $DS3[$key];
			} else if ( !empty($visit['DS3']) ) {
				$visit['DS3_DATA'] = [['DS3' => $visit['DS3']]];
			} else {
				$visit['DS3_DATA'] = [];
			}

			if ( isset($NAZ[$key]) ) {
				$visit['NAZ'] = $NAZ[$key];
			} else {
				$visit['NAZ'] = [];
			}

			unset($visit['DS2']);
			unset($visit['DS3']);

			$OS_SLUCH = [];

			if ( !empty($PACIENT[$key]['OS_SLUCH']) ) {
				$OS_SLUCH[] = ['OS_SLUCH_VAL' => $PACIENT[$key]['OS_SLUCH']];
				unset($PACIENT[$key]['OS_SLUCH']);
			}

			if ( !empty($PACIENT[$key]['OS_SLUCH1']) ) {
				$OS_SLUCH[] = ['OS_SLUCH_VAL' => $PACIENT[$key]['OS_SLUCH1']];
				unset($PACIENT[$key]['OS_SLUCH1']);
			}

			$visit['OS_SLUCH'] = $OS_SLUCH;

			// NAPR заполняется для случаев с подозрением на ЗНО
			if (
				isset($NAPR[$key])
				&& (!empty($visit['DS_ONK']) && $visit['DS_ONK'] == 1)
			) {
				$visit['NAPR_DATA'] = $NAPR[$key];
			} else {
				$visit['NAPR_DATA'] = [];
			}

			// CONS заполняется для случаев с подозрением на ЗНО и случаев лечения ЗНО.
			if (
				isset($CONS[$key])
				&& (
					(!empty($visit['DS_ONK']) && $visit['DS_ONK'] == 1)
					|| (
						!empty($visit['DS1'])
						&& (
							substr($visit['DS1'], 0, 1) == 'C'
							|| (substr($visit['DS1'], 0, 3) >= 'D00' && substr($visit['DS1'], 0, 3) <= 'D09')
							|| $visit['DS1'] == 'D70'
						)
					)
					|| (!empty($visit['RegistryData_IsZNO']) && $visit['RegistryData_IsZNO'] == 2)
				)
			) {
				$visit['CONS_DATA'] = $CONS[$key];
			} else {
				$visit['CONS_DATA'] = [];
			}

			$visit['ONK_SL_DATA'] = [];

			// ONK_SL заполняется для случаев лечения ЗНО
			if (
				(!empty($visit['RegistryData_IsZNO']) && $visit['RegistryData_IsZNO'] == 2)
				|| (
					(empty($visit['DS_ONK']) || $visit['DS_ONK'] != 1)
					&& !empty($visit['DS1'])
					&& (
						substr($visit['DS1'], 0, 1) == 'C'
						|| (substr($visit['DS1'], 0, 3) >= 'D00' && substr($visit['DS1'], 0, 3) <= 'D09')
						|| $visit['DS1'] == 'D70'
					)
				)
			) {
				$ONK_SL_DATA = [];
				foreach($ONK_SL_FIELDS as $onkslfield){
					if (isset($visit[$onkslfield])) {
						if (!in_array($onkslfield, ['PROT', 'D_PROT'])) {
							$ONK_SL_DATA[$onkslfield] = $visit[$onkslfield];
						}
						unset($visit[$onkslfield]);
					}
				}

				if (isset($BPROT[$key])) {
					$ONK_SL_DATA['B_PROT_DATA'] = $BPROT[$key];
				}

				if (isset($BDIAG[$key])) {
					$ONK_SL_DATA['B_DIAG_DATA'] = $BDIAG[$key];
				}

				if (isset($ONKUSL[$key])) {
					$ONK_SL_DATA['ONK_USL_DATA'] = $ONKUSL[$key];
				}

				if (count($ONK_SL_DATA) > 0) {
					foreach ($ONK_SL_FIELDS as $onkslfield) {
						if (!in_array($onkslfield, ['PROT', 'D_PROT']) && !isset($ONK_SL_DATA[$onkslfield])) {
							$ONK_SL_DATA[$onkslfield] = null; // заполняем недостающие поля null
						}
					}

					if (!isset($ONK_SL_DATA['B_PROT_DATA'])) {
						$ONK_SL_DATA['B_PROT_DATA'] = [];
					}

					if (!isset($ONK_SL_DATA['B_DIAG_DATA'])) {
						$ONK_SL_DATA['B_DIAG_DATA'] = [];
					}

					if (!isset($ONK_SL_DATA['ONK_USL_DATA'])) {
						$ONK_SL_DATA['ONK_USL_DATA'] = [];
					}

					$visit['ONK_SL_DATA'][] = $ONK_SL_DATA;
				}
			}

			// Проставляем в ONK_USL поле IDSERV_ONK
			if ( count($visit['ONK_SL_DATA']) > 0 && isset($visit['ONK_SL_DATA'][0]['ONK_USL_DATA']) && count($visit['ONK_SL_DATA'][0]['ONK_USL_DATA']) > 0 ) {
				foreach ( $visit['ONK_SL_DATA'][0]['ONK_USL_DATA'] as $k => $row ) {
					$IDSERV_ONK = null;

					if ( count($visit['USL']) > 0 ) {
						foreach ( $visit['USL'] as $usl ) {
							if (
								isset($usl['RAZDEL_USL'])
								&& in_array($usl['RAZDEL_USL'], ['101', '201'])
							) {
								$IDSERV_ONK = $usl['IDSERV'];
								break;
							}
						}
					}

					if ( empty($IDSERV_ONK) ) {
						$IDSERV++;
						$IDSERV_ONK = $IDSERV;
					}

					$visit['ONK_SL_DATA'][0]['ONK_USL_DATA'][$k]['IDSERV_ONK'] = $IDSERV_ONK;
				}
			}

			$number++;

			$visit['IDCASE'] = $number;
			$PACIENT[$key]['ID_PAC'] = $number;

			$ZAP[$key] = [
				'N_ZAP' => $number,
				'PR_NOV' => $visit['PR_NOV'],
				'PACIENT' => [$PACIENT[$key]],
				'SLUCH' => [$visit]
			];

			$Registry_EvnNum[$number] = $key;

			$rowNumArray[] = [
				'Registry_id' => $visit['Registry_id'],
				'Evn_id' => $key,
				'RegistryData_RowNum' => $number,
			];

			if (count($ZAP) >= 1000) {
				$SD_Z += count($ZAP);

				// пишем в файл
				$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, ['ZAP' => $ZAP], true, false, $altKeys);
				$xml = str_replace('&', '&amp;', $xml);
				$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
				file_put_contents($file_re_data_name, $xml, FILE_APPEND);

				// пишем связку номеров записей и случаев во временную таблицу
				$recCnt = 0;
				$rowNumInsertQueryBody = '';

				foreach ( $rowNumArray as $row ) {
					$rowNumInsertQueryBody .= "({$row['Registry_id']}, {$row['Evn_id']}, {$row['RegistryData_RowNum']}, {$this->_exportTimeStamp}),";
					$recCnt++;

					if ( $recCnt == 1000 ) {
						$this->textlog->add("Добавляем " . $recCnt . " записей в r66.RegistryDataRowNum...");

						$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

						if ( $result === false || !is_array($result) || !empty($result['Error_Msg']) ) {
							$this->textlog->add("Ошибка при выполнении запроса");
							$this->textlog->add(print_r($result, true));
							return false;
						}

						$this->textlog->add("... выполнено");

						$recCnt = 0;
						$rowNumInsertQueryBody = '';
					}
				}

				if ( $recCnt > 0 ) {
					$this->textlog->add("Добавляем " . $recCnt . " записей в r66.RegistryDataRowNum...");

					$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

					if ( $result === false || !is_array($result) || !empty($result['Error_Msg']) ) {
						$this->textlog->add("Ошибка при выполнении запроса");
						$this->textlog->add(print_r($result, true));
						return false;
					}

					$this->textlog->add("... выполнено");
				}

				unset($xml);
				unset($ZAP);
				unset($rowNumArray);
				$ZAP = [];
				$rowNumArray = [];
			}
		}

		if (count($ZAP) > 0) {
			$SD_Z += count($ZAP);

			// пишем в файл
			$xml = $this->parser->parse_ext('export_xml/' . $registry_data_template_body, ['ZAP' => $ZAP], true, false, $altKeys);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);
			file_put_contents($file_re_data_name, $xml, FILE_APPEND);

			// пишем связку номеров записей и случаев во временную таблицу
			$recCnt = 0;
			$rowNumInsertQueryBody = '';

			foreach ( $rowNumArray as $row ) {
				$rowNumInsertQueryBody .= "({$row['Registry_id']}, {$row['Evn_id']}, {$row['RegistryData_RowNum']}, {$this->_exportTimeStamp}),";
				$recCnt++;

				if ( $recCnt == 1000 ) {
					$this->textlog->add("Добавляем " . $recCnt . " записей в r66.RegistryDataRowNum...");

					$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

					if ( $result === false || !is_array($result) || !empty($result['Error_Msg']) ) {
						$this->textlog->add("Ошибка при выполнении запроса");
						$this->textlog->add(print_r($result, true));
						return false;
					}

					$this->textlog->add("... выполнено");

					$recCnt = 0;
					$rowNumInsertQueryBody = '';
				}
			}

			if ( $recCnt > 0 ) {
				$this->textlog->add("Добавляем " . $recCnt . " записей в r66.RegistryDataRowNum...");

				$result = $this->getFirstRowFromQuery(str_replace('{values_array}', trim($rowNumInsertQueryBody, ','), $rowNumInsertQuery), []);

				if ( $result === false || !is_array($result) || !empty($result['Error_Msg']) ) {
					$this->textlog->add("Ошибка при выполнении запроса");
					$this->textlog->add(print_r($result, true));
					return false;
				}

				$this->textlog->add("... выполнено");
			}

			unset($xml);
			unset($ZAP);
			unset($rowNumArray);
		}

		unset($DS2);
		unset($DS3);
		unset($NAZ);
		unset($USL);

		$toFile = [];
		foreach($PACIENT as $onepac) {
			$toFile[] = $onepac;
			if (count($toFile) >= 1000) {
				// пишем в файл
				$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, ['PACIENT' => $toFile], true);
				$xml_pers = str_replace('&', '&amp;', $xml_pers);
				$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
				file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
				unset($xml_pers);
				unset($toFile);
				$toFile = [];
			}
		}
		if (count($toFile) > 0) {
			// пишем в файл
			$xml_pers = $this->parser->parse_ext('export_xml/' . $person_data_template_body, ['PACIENT' => $toFile], true);
			$xml_pers = str_replace('&', '&amp;', $xml_pers);
			$xml_pers = preg_replace("/\R\s*\R/", "\r\n", $xml_pers);
			file_put_contents($file_re_pers_data_name, $xml_pers, FILE_APPEND);
			unset($xml_pers);
			unset($toFile);
		}

		unset($toFile);
		unset($PACIENT);

		return $SD_Z;
	}

    /**
     * Экспорт людей для идентификации
     * @param $data
     * @return array
     */
	public function exportRegistryDataForIdentification($data) {
		if ( !is_array($data['Registry_ids']) || count($data['Registry_ids']) == 0 ) {
			return $this->createError('','Отсутствуют идентификаторы реестров');
		}

        $this->db = $this->load->database('registry', true);

		set_time_limit(0);

		$ids = $data['Registry_ids'];

		$ids_str = implode(",", $ids);

		$this->setRegistryParamsByType(['Registry_id' => $ids[0]]);

		if ( $this->RegistryType_id == 6 ) {
			$selectEvnFields = "
				null as \"Evn_id\",
				P.Evn_id as \"CmpCallCard_id\",
			";
		}
		else {
			$selectEvnFields = "
				P.Evn_id as \"Evn_id\",
				null as \"CmpCallCard_id\",
			";
		}

		// Выборка людей
		$query = "
			select
			-- select
				PS.Person_id as \"Person_id\",
				{$selectEvnFields}
				to_char(P.Evn_setDate, 'yyyy-mm-dd') as \"PersonIdentPackagePos_identDT\",
				to_char(coalesce(P.Evn_disDate, P.Evn_setDate), 'yyyy-mm-dd') as \"PersonIdentPackagePos_identDT2\"
			-- end select
			from
			-- from
				(
					select distinct
						rd.Person_id,
						rd.Person_BirthDay,
						rd.Evn_id,
						rd.Evn_setDate,
						rd.Evn_disDate
					from
						{$this->scheme}.v_{$this->RegistryDataObject} rd
					where
						rd.Registry_id in ({$ids_str})
					union		
					select distinct
						re.Person_id,
						re.Person_BirthDay,
						re.Evn_id,
						re.Evn_setDate,
						re.Evn_disDate
					from
						{$this->scheme}.v_{$this->RegistryErrorObject} re
					where
						re.Registry_id in ({$ids_str})	
					union
					select distinct
						rnp.Person_id,
						rnp.Person_BirthDay,
						E.Evn_id,
						E.Evn_setDate,
						E.Evn_disDate
					from
						{$this->scheme}.v_RegistryNoPolis rnp
						left join v_evn E on E.Evn_id = rnp.Evn_id
					where
						rnp.Registry_id in ({$ids_str})
				) as P
				left join v_PersonDeputy PD on PD.Person_id = P.Person_id
				left join lateral (
					select case when 
					PD.Person_pid is not null and dbo.Age_newborn(P.Person_BirthDay, P.Evn_setDate) < 1
					then 1 else 0 end as flag
				) deputy on true
				inner join lateral (
					select
					    *
					from v_Person_all
					where
						(Person_id = P.Person_id and PersonEvn_insDT <= P.Evn_setDate) or 
						(Person_id = PD.Person_pid and PersonEvn_insDT <= P.Evn_setDate)
					order by
						case when 
							(deputy.flag=1 and Person_id = PD.Person_pid) or 
							(deputy.flag=0 and Person_id = P.Person_id) 
						then 0 else 1 end,
						PersonEvn_insDT desc
					limit 1
				) PS on true
			-- end from
			order by
			-- order by
				ps.Person_id
			-- end order by
		";

		$this->load->model('PersonIdentPackage_model');

		try {
			$stat = ['PackageCount' => 0, 'PersonCount' => 0];
			$file_zip_name = $this->PersonIdentPackage_model->createCustomPersonIdentPackages($query, $data, true, $stat);
			if ($stat['PersonCount'] == 0) {
				throw new Exception('Не найдены пациенты для экспорта');
			}
		} catch(Exception $e) {
			return $this->createError($e->getCode(), $e->getMessage());
		}
		if (!file_exists($file_zip_name)) {
			return $this->createError('','Ошибка создания архива экспорта');
		}

		return [['Error_Msg' => '', 'filename' => $file_zip_name]];
	}


    /**
     *    Установка статуса импорта реестра в XML
     * @param $data
     * @return array|bool
     */
	public function SetXmlExportStatus($data) 
	{
		if (empty($data['Registry_EvnNum'])) {
			$data['Registry_EvnNum'] = null;
		}
		
		if (!empty($data['Registry_id']))
		{
			$query = "
				update
					{$this->scheme}.Registry
				set
					Registry_xmlExportPath = :Status,
					Registry_EvnNum = :Registry_EvnNum,
					Registry_xmlExpDT = dbo.tzGetDate()
				where
					Registry_id = :Registry_id
			";
			
			$result = $this->db->query($query, [
					'Registry_id' => $data['Registry_id'],
					'Registry_EvnNum' => $data['Registry_EvnNum'],
					'Status' => $data['Status']
            ]);
			
			if (is_object($result)) {
				return true;
			} else {
				return ['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'];
			}
		} else {
			return ['success' => false, 'Error_Msg' => 'Пустые значения входных параметров'];
		}
	}

    /**
     *    Установка статуса реестра
     * @param $data
     * @return array
     */
	public function setRegistryStatus($data)
    {
		if ( empty($data['Registry_id']) || empty($data['RegistryStatus_id']) ) {
			return [['success' => false, 'Error_Msg' => 'Пустые значения входных параметров']];
		}

		if (!empty($data['Registry_id'])) {
			// если статус "Отправка данных в РМИС" или "Загружен в РМИС", запретить изменять
			$rcsresp = $this->queryResult("
			select
				r.RegistryCheckStatus_id as \"RegistryCheckStatus_id\",
				rcs.RegistryCheckStatus_Name as \"RegistryCheckStatus_Name\"
			from
				{$this->scheme}.v_RegistryGroupLink rgl
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_pid
				inner join v_RegistryCheckStatus rcs on rcs.RegistryCheckStatus_id = r.RegistryCheckStatus_id
			where
				rgl.Registry_id = :Registry_id
			", [
				'Registry_id' => $data['Registry_id']
			]);
			if (!empty($rcsresp[0]['RegistryCheckStatus_id']) && in_array($rcsresp[0]['RegistryCheckStatus_id'], array(45, 47))) {
				return ['Error_Msg' => 'Действия над реестром запрещены, т.к. он входит в объединённый реестр со статусом: ' . $rcsresp[0]['RegistryCheckStatus_Name']];
			}
		}

		// Предварительно получаем тип и статус реестра
		$RegistryType_id = 0;
		$RegistryStatus_id = 0;

		$query = "
			select
			    RegistryType_id as \"E:\OSPanel\domains\permaster.loc\promed\models\_pgsql\ekb\Ekb_Registry_model.php\",
			    RegistryStatus_id as \"E:\OSPanel\domains\permaster.loc\promed\models\_pgsql\ekb\Ekb_Registry_model.php\"
			from {$this->scheme}.v_Registry Registry
			where Registry_id = :Registry_id
		";
		$r = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		if (is_object($r) ) {
			$res = $r->result('array');

			if ( is_array($res) && count($res) > 0 ) {
				$RegistryType_id = $res[0]['RegistryType_id'];
				$RegistryStatus_id = $res[0]['RegistryStatus_id'];

				$data['RegistryType_id'] = $RegistryType_id;
			}
		}

		$this->setRegistryParamsByType($data);

		$fields = "";
			
		if ( $data['RegistryStatus_id'] == 3 ) { // если перевели в работу, то снимаем признак формирования
			//#11018 2. При перемещении реестра в других статусах в состояние "В работу " дополнительно сбрасывать Registry_xmlExpDT
			$fields .= "Registry_ExportPath = null, Registry_xmlExportPath = null, Registry_xmlExpDT = null, RegistryCheckStatus_id = null, ";
		}

		if ( $data['is_manual'] != 1 ) {
			if ( $data['RegistryStatus_id'] == 4 ) { // если переводим в оплаченные, то вызываем p_Registry_setPaid
				$query = "
					select
					    4 as \"RegistryStatus_id\",
					    Error_Code as \"Error_Code\",
					    Error_Message as \"Error_Msg\"
					from {$this->scheme}.p_Registry_setPaid
					(
						Registry_id := :Registry_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, $data);

				if ( !is_object($result) ) {
					return [['success' => false, 'Error_Msg' => 'Ошибка при отметке реестра как оплаченного']];
				}

				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
					return $res;
				}
			} else if ( $RegistryStatus_id == 4 && $data['RegistryStatus_id'] == 2 ) { // если переводим из "Оплаченный" в "К оплате" p_Registry_setUnPaid
				$check154914 = $this->checkRegistryDataIsInOtherRegistry($data);

				if ( !empty($check154914) ) {
					return [['success' => false, 'Error_Msg' => $check154914]];
				}

				$query = "
                    select
                        2 as \"RegistryStatus_id\",
                        Error_Code as \"Error_Code\",
                        Error_Message as \"Error_Msg\"
                    from {$this->scheme}.p_Registry_setUnPaid
					(
						Registry_id := :Registry_id,
						pmUser_id := :pmUser_id
					)
				";
				$result = $this->db->query($query, $data);

				if ( !is_object($result) ) {
					return [['success' => false, 'Error_Msg' => 'Ошибка при отметке к оплате']];
				}

				$res = $result->result('array');

				if ( is_array($res) && count($res) > 0 && !empty($res[0]['Error_Msg']) ) {
					return $res;
				}
			}
		}

		$query = "
				update {$this->scheme}.Registry set
					RegistryStatus_id = :RegistryStatus_id,
					Registry_updDT = dbo.tzGetDate(),
					{$fields}
					pmUser_updID = :pmUser_id
				where
					Registry_id = :Registry_id;
			returning :RegistryStatus_id as \"RegistryStatus_id\", 0 as \"Error_Code\", '' as \"Error_Msg\"
		";
		$result = $this->db->query($query, [
			'Registry_id' => $data['Registry_id'],
			'RegistryStatus_id' => $data['RegistryStatus_id'],
			'pmUser_id' => $data['pmUser_id']
		]);

		if ( !is_object($result) ) {
			return [['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных']];
		}

		if ( $data['RegistryStatus_id'] == 4 ) {
			// пишем информацию о смене статуса в историю
			$this->dumpRegistryInformation(['Registry_id' => $data['Registry_id']], 4);
		}

		return $result->result('array');
	}

    /**
     *    Получение номера выгружаемого файла реестра в отчетном периоде
     * @param $data
     * @return int
     */
	public function SetXmlPackNum($data)
    {
        $packNum = $this->getFirstResultFromQuery("
                select
                    Registry_FileNum
                from {$this->scheme}.v_Registry
                where
                    Registry_id = :Registry_id
                and
                    RegistryGroupType_id = :RegistryGroupType_id
                limit 1
		", $data);
        
        
        if(empty($packNum)) {
            $q = "
                select
                    max(Registry_FileNum)
                from
                    {$this->scheme}.v_Registry
                where
                    Lpu_id = :Lpu_id
                and
                    SUBSTRING(to_char(Registry_endDate, 'yyyymmdd'), 3, 4) = :Registry_endMonth
                and Registry_FileNum is not null
                and RegistryGroupType_id = :RegistryGroupType_id
            ";
            $packNum = $this->getFirstResultFromQuery($q, $data);
            
            if (empty($packNum)) {
                $packNum = 0;
            }
            
            $packNum++;
            
            $this->db->query("
                update {$this->scheme}.Registry
                set Registry_FileNum = :packNum
                where Registry_id = :Registry_id", [
                'Registry_id' => $data['Registry_id'],
                'packNum' => $packNum
            ]);
        }

        if(!$packNum && $packNum !== 0) {
            return 0;
        }
        
        if($packNum > 9) {
            $packNum = 9;
        }
	

		return $packNum;
	}

    /**
     *    Функция возрвращает набор данных для дерева реестра 2-го уровня (тип реестра)
     * @param $data
     * @return array
     */
	public function loadRegistryTypeNode($data) {
		$result = [
		    ['RegistryType_id' => 1, 'RegistryType_Name' => 'Стационар'],
            ['RegistryType_id' => 2, 'RegistryType_Name' => 'Поликлиника'],
            ['RegistryType_id' => 6, 'RegistryType_Name' => 'Скорая помощь'],
            ['RegistryType_id' => 7, 'RegistryType_Name' => 'Дисп-ция взр. населения с 2013 года'],
            ['RegistryType_id' => 9, 'RegistryType_Name' => 'Дисп-ция детей-сирот с 2013 года'],
            ['RegistryType_id' => 11, 'RegistryType_Name' => 'Проф.осмотры взр. населения'],
            ['RegistryType_id' => 12, 'RegistryType_Name' => 'Медосмотры несовершеннолетних'],
            ['RegistryType_id' => 15, 'RegistryType_Name' => 'Параклинические услуги']
		];

		return $result;
	}

    /**
     *    Функция возрвращает набор данных для дерева реестра 3-го уровня (статус реестра)
     * @param $data
     * @return array
     */
	public function loadRegistryStatusNode($data)
	{
		$result = [
			['RegistryStatus_id' => 5, 'RegistryStatus_Name' => 'В очереди'],
			['RegistryStatus_id' => 3, 'RegistryStatus_Name' => 'В работе'],
			['RegistryStatus_id' => 2, 'RegistryStatus_Name' => 'К оплате'],
			['RegistryStatus_id' => 4, 'RegistryStatus_Name' => 'Оплаченные']
		];

		return $result;
	}

    /**
	 *	Комментарий
	 */
	function loadRegistry($data) {
		$filter = "(1=1)";
		$params = ['Lpu_id' => (!empty($data['Lpu_id']) ? $data['Lpu_id'] : $data['session']['lpu_id'])];
		$filter .= ' and R.Lpu_id = :Lpu_id';
		
		if ( !empty($data['Registry_id']) ) {
			$filter .= ' and R.Registry_id = :Registry_id';
			$params['Registry_id'] = $data['Registry_id'];
		}

		if ( !empty($data['RegistryType_id']) ) {
			$filter .= ' and R.RegistryType_id = :RegistryType_id';
			$params['RegistryType_id'] = $data['RegistryType_id'];
		}

		$this->setRegistryParamsByType($data);

		//запрос для реестров в очереди
		if ( (isset($data['RegistryStatus_id'])) && ($data['RegistryStatus_id'] == 5) ) {
			$query = "
				Select
					R.RegistryQueue_id as \"Registry_id\",
					R.Lpu_id as \"Lpu_id\",
					R.RegistryType_id as \"RegistryType_id\",
					R.PayType_id as \"PayType_id\",
					PT.PayType_Name as \"PayType_Name\",
					5 as RegistryStatus_id as \"RegistryStatus_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					R.RegistryEventType_id as \"RegistryEventType_id\",
					2 as Registry_IsActive as \"Registry_IsActive\",
					1 as Registry_IsProgress as \"Registry_IsProgress\",
					1 as Registry_IsNeedReform as \"Registry_IsNeedReform\",
					RTrim(R.Registry_Num) || ' / в очереди: ' || LTrim(cast(RegistryQueue_Position as varchar)) as \"Registry_Num\",
					null as \"ReformTime\",
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
					0 as \"Registry_Count\",
					0 as \"Registry_RecordPaidCount\",
					0 as \"Registry_KdCount\",
					0 as \"Registry_KdPaidCount\",
					0 as \"Registry_Sum\",
					0 as \"Registry_SumPaid\",
					LpuBuilding.LpuBuilding_Name as \"LpuBuilding_Name\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					RTrim(RegistryEventType.RegistryEventType_Name) as \"RegistryEventType_Name\",
					R.Registry_IsRepeated as \"Registry_IsRepeated\",
					case when R.Registry_IsFLK = 2 then 'true' else 'false' end as \"Registry_IsFLK\",
					'' as \"Registry_updDate\",
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay
					0 as \"RegistryErrorCom_IsData\",
					0 as \"RegistryError_IsData\",
					0 as \"RegistryPerson_IsData\",
					0 as \"RegistryNoPolis_IsData\",
					0 as \"RegistryNoPay_IsData\",
					0 as \"RegistryErrorTFOMS_IsData\",
					0 as \"RegistryNoPaid_Count\", 
					0 as \"RegistryNoPay_UKLSum\",
					-1 as \"RegistryCheckStatus_Code\",
					0 as \"RegistryErrorTFOMSType_id\"
					--'' as RegistryCheckStatus_Name
				from {$this->scheme}.v_RegistryQueue R
					left join v_PayType PT on PT.PayType_id = R.PayType_id
					left join v_LpuBuilding LpuBuilding on LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join v_RegistryStacType RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join {$this->scheme}.v_RegistryEventType RegistryEventType on RegistryEventType.RegistryEventType_id = R.RegistryEventType_id
				where {$filter}
				order by R.RegistryQueue_id desc
			";
		} else {// для всех реестров, кроме тех что в очереди
            $source_table = 'v_Registry';

			if ( !empty($data['RegistryStatus_id']) ) {
				if ( 6 == (int)$data['RegistryStatus_id'] ) {
					//6 - если запрошены удаленные реестры
					$source_table = 'v_Registry_deleted';
					//т.к. для удаленных реестров статус не важен - не накладываем никаких условий на статус реестра.
				}
				else {
					$filter .= ' and R.RegistryStatus_id = :RegistryStatus_id';
					$params['RegistryStatus_id'] = $data['RegistryStatus_id'];
				}

				// только если оплаченные!!!
				if ( 4 == (int)$data['RegistryStatus_id'] ) {
					if( $data['Registry_accYear'] > 0 ) {
						$filter .= ' and convert(varchar(4), cast(R.Registry_begDate as date), 112) <= :Registry_accYear';
						$filter .= ' and convert(varchar(4), cast(R.Registry_endDate as date), 112) >= :Registry_accYear';
						$params['Registry_accYear'] = $data['Registry_accYear'];
					}
				}
			}

			$query = "
				Select
					R.Registry_id as \"Registry_id\",
					R.Lpu_id as \"Lpu_id\",
					R.RegistryType_id as \"RegistryType_id\",
					R.PayType_id as \"PayType_id\",
					PT.PayType_Name as \"PayType_Name\",
					" . (!empty($data['RegistryStatus_id']) && 6 == (int)$data['RegistryStatus_id'] ? "6 as \"RegistryStatus_id\"" : "R.RegistryStatus_id as \"RegistryStatus_id\"") . ",
					R.OrgRSchet_id as \"OrgRSchet_id\",
					R.RegistryStacType_id as \"RegistryStacType_id\",
					R.RegistryEventType_id as \"RegistryEventType_id\",
					R.Registry_IsActive as \"Registry_IsActive\",
					case when RQ.RegistryQueue_id is not null then 1 else 0 end as \"Registry_IsProgress\",
					coalesce(R.Registry_IsNeedReform, 1) as \"Registry_IsNeedReform\",
					RTrim(R.Registry_Num) as \"Registry_Num\",
					to_char(RQH.RegistryQueueHistory_endDT, 'dd.mm.yyyy hh24:mi:ss') as \"ReformTime\",
					to_char(R.Registry_accDate, 'dd.mm.yyyy') as \"Registry_accDate\",
					to_char(R.Registry_begDate, 'dd.mm.yyyy') as \"Registry_begDate\",
					to_char(R.Registry_endDate, 'dd.mm.yyyy') as \"Registry_endDate\",
					coalesce(R.Registry_RecordCount, 0) as \"Registry_Count\",
					coalesce(R.Registry_RecordPaidCount, 0) as \"Registry_RecordPaidCount\",
					coalesce(R.Registry_KdCount, 0) as \"Registry_KdCount\",
					coalesce(R.Registry_KdPaidCount, 0) as \"Registry_KdPaidCount\",
					coalesce(R.Registry_Sum, 0.00) as \"Registry_Sum\",
					0 as \"Registry_SumPaid\",
					LpuBuilding.LpuBuilding_Name as \"LpuBuilding_Name\",
					R.Registry_IsRepeated as \"Registry_IsRepeated\",
					case when R.Registry_IsFLK = 2 then 'true' else 'false' end as \"Registry_IsFLK\",
					RTrim(RegistryStacType.RegistryStacType_Name) as \"RegistryStacType_Name\",
					RTrim(RegistryEventType.RegistryEventType_Name) as \"RegistryEventType_Name\",
					RTrim(coalesce(to_char(cast(R.Registry_updDT as timestamp), 'dd.mm.yyyy hh24:mi:ss'), '')) as \"Registry_updDate\",
					-- количество записей в таблицах RegistryErrorCom, RegistryError, RegistryPerson, RegistryNoPolis, RegistryNoPay, RegistryDouble
					RegistryErrorCom.RegistryErrorCom_IsData as \"RegistryErrorCom_IsData\",
					RegistryError.RegistryError_IsData as \"RegistryError_IsData\",
					RegistryPerson.RegistryPerson_IsData as \"RegistryPerson_IsData\",
					RegistryNoPolis.RegistryNoPolis_IsData as \"RegistryNoPolis_IsData\",
					RegistryDouble.RegistryDouble_IsData as \"RegistryDouble_IsData\",
					0 as RegistryNoPay_IsData as \"RegistryNoPay_IsData\",
					RegistryErrorTFOMS.RegistryErrorTFOMS_IsData as \"RegistryErrorTFOMS_IsData\",
					RegistryNoPaid.RegistryNoPaid_Count as \"RegistryNoPaid_Count\",
					0 as \"RegistryNoPay_UKLSum\",
					coalesce(RegistryCheckStatus.RegistryCheckStatus_Code, -1) as \"RegistryCheckStatus_Code\",
					RegistryErrorTFOMS.RegistryErrorTFOMSType_id as \"RegistryErrorTFOMSType_id\"
 					--RegistryCheckStatus.RegistryCheckStatus_Name
				from {$this->scheme}.{$source_table} R
					left join v_PayType PT on PT.PayType_id = R.PayType_id
					left join v_RegistryStacType RegistryStacType on RegistryStacType.RegistryStacType_id = R.RegistryStacType_id
					left join {$this->scheme}.v_RegistryEventType RegistryEventType on RegistryEventType.RegistryEventType_id = R.RegistryEventType_id
					left join v_LpuBuilding LpuBuildingon LpuBuilding.LpuBuilding_id = R.LpuBuilding_id
					left join v_RegistryCheckStatus RegistryCheckStatus on RegistryCheckStatus.RegistryCheckStatus_id = R.RegistryCheckStatus_id
					left join lateral(
						select RegistryQueue_id
						from {$this->scheme}.v_RegistryQueue
						where Registry_id = R.Registry_id
						limit 1
					) RQ on true
					left join lateral (
						select RegistryQueueHistory_endDT
						from {$this->scheme}.RegistryQueueHistory
						where Registry_id = R.Registry_id
							and RegistryQueueHistory_endDT is not null
						order by RegistryQueueHistory_id desc
						limit 1
					) RQH on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorCom_IsData from {$this->scheme}.v_{$this->RegistryErrorComObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorCom on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryError_IsData from {$this->scheme}.v_{$this->RegistryErrorObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryError on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryPerson_IsData from {$this->scheme}.v_{$this->RegistryPersonObject} RE where RE.Registry_id = R.Registry_id and coalesce(RE.{$this->RegistryPersonObject}_IsDifferent, 1) = 1 limit 1) RegistryPerson on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryNoPolis_IsData from {$this->scheme}.v_RegistryNoPolis RE where RE.Registry_id = R.Registry_id limit 1) RegistryNoPolis on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryErrorTFOMS_IsData, RegistryErrorTFOMSType_id from {$this->scheme}.v_RegistryErrorTFOMS RE where RE.Registry_id = R.Registry_id limit 1) RegistryErrorTFOMS on true
					left join lateral(select case when RE.Registry_id is not null then 1 else 0 end as RegistryDouble_IsData from {$this->scheme}.v_{$this->RegistryDoubleObject} RE where RE.Registry_id = R.Registry_id limit 1) RegistryDouble on true
					left join lateral(
						select
							count(RDnoPaid.Evn_id) as RegistryNoPaid_Count
						from {$this->scheme}.v_{$this->RegistryDataObject} RDnoPaid
						where RDnoPaid.Registry_id = R.Registry_id and coalesce(RDnoPaid.RegistryData_isPaid, 1) = 1
					) RegistryNoPaid on true
				where
					{$filter}
				order by
					R.Registry_id desc
			";
		}
		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if (! is_object($result) ) {
			return false;
		}

        $resp = $result->result('array');

        if ( !empty($data['Registry_id']) && !empty($resp[0])) {
            $resp[0]['LpuSection_id'] = '';
            $resp_lb = $this->queryResult("
					select
						RegistryLpuSection_id as \"RegistryLpuSection_id\",
						LpuSection_id as \"LpuSection_id\"
					from
						{$this->scheme}.v_RegistryLpuSection
					where
						Registry_id = :Registry_id
				", [
                'Registry_id' => $data['Registry_id']
            ]);

            foreach($resp_lb as $one_lb) {
                if (!empty($resp[0]['LpuSection_id'])) {
                    $resp[0]['LpuSection_id'] .= ",";
                }
                $resp[0]['LpuSection_id'] .= $one_lb['LpuSection_id'];
            }
        }

        return $resp;
	}

    /**
     *    Переформирование реестра
     * @param $data
     * @return array
     */
	public function reformRegistry($data)
	{
		$addToSelect = $this->getReformRegistryAdditionalFields();

		$query = "
			select
				--Registry_id,
				--Lpu_id,
				RegistryType_id as \"RegistryType_id\",
				PayType_id as \"PayType_id\",
				RegistryStatus_id as \"RegistryStatus_id\",
				RegistryStacType_id as \"RegistryStacType_id\",
				RegistryEventType_id as \"RegistryEventType_id\",
				to_char(cast(Registry_begDate as timestamp), 'yyyymmdd') as Registry_begDate,
				to_char(cast(Registry_endDate as timestamp), 'yyyymmdd') as Registry_endDate,
				KatNasel_id as \"KatNasel_id\",
				LpuBuilding_id as \"LpuBuilding_id\",
				Registry_Num as \"Registry_Num\",
				Registry_Sum as \"Registry_Sum\",
				Registry_IsActive as \"Registry_IsActive\",
				OrgRSchet_id as \"OrgRSchet_id\",
				to_char(cast(Registry_accDate as timestamp), 'yyyymmdd') as \"Registry_accDate\"
				{$addToSelect}
			from
				{$this->scheme}.v_Registry Registry
			where
				Registry_id = ?
		";

		$result = $this->db->query($query, [$data['Registry_id']]);

		if (!is_object($result)) {
			return ['success' => false, 'Error_Msg' => 'Ошибка при выполнении запроса к базе данных'];
		}

        $row = $result->result('array');
        if ( is_array($row) && count($row) > 0 ) {
            foreach ( $row[0] as $key => $value ) {
                $data[$key] = $value;
            }
            // Переформирование реестра
            //return  $this->saveRegistry($data);
            // Постановка реестра в очередь
            return  $this->saveRegistryQueue($data);
        } else {
            return ['success' => false, 'Error_Msg' => 'Реестр не найден в базе'];
        }
	}

    /**
     * Получение данных Дубли посещений (RegistryDouble) для поликлин. и стац. реестров
     * @param $data
     * @return array|bool
     */
	public function loadRegistryDouble($data)
    {
		$this->setRegistryParamsByType($data);

		$join = '';
		$filterList = [];

		if ( !empty($data['MedPersonal_id']) ) {
			$filterList[] = "MP.MedPersonal_id = :MedPersonal_id";
			$params['MedPersonal_id'] = $data['MedPersonal_id'];
		}

		$diagFilter = getAccessRightsDiagFilter('D.Diag_Code');
		if (!empty($diagFilter)) {
			$join .= "left join {$this->scheme}.v_{$this->RegistryDataObject} RData on RData.Registry_id = RD.Registry_id and RData.Evn_id = RD.Evn_id ";
			$join .= "left join v_Diag D on D.Diag_id = RData.Diag_id ";
			$filterList[] = $diagFilter;
		}
		switch ( $this->RegistryType_id ) {
			case 6:
				$query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\",
						 RD.Evn_id as \"Evn_id\",
						 null as \"Evn_rid\",
						 RD.Person_id as \"Person_id\",
						 rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\",
						 RTrim(coalesce(to_char(cast(RD.Person_BirthDay as timestamp), 'dd.mm.yyyy'),'')) as \"Person_BirthDay\",
						 CCC.Year_num as \"Evn_Num\",
						 ETS.EmergencyTeamSpec_Name as \"LpuSection_FullName\",
						 MP.Person_Fio as \"MedPersonal_Fio\",
						 to_char(CCC.AcceptTime, 'dd.mm.yyyy') as \"Evn_setDate\",
						 CCC.CmpCallCard_id as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						".$join."
						left join v_CmpCloseCard CCC on CCC.CmpCloseCard_id = RD.Evn_id
						left join v_EmergencyTeamSpec ETS on ETS.EmergencyTeamSpec_id = CCC.EmergencyTeamSpec_id
						left join lateral (
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = CCC.MedPersonal_id limit 1
						) as MP on true
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
						-- end where
					order by
						-- order by
						RD.Person_SurName,
						RD.Person_FirName,
						RD.Person_SecName
						-- end order by
				";
			break;

			default:
				$query = "
					select
						-- select
						 RD.Registry_id as \"Registry_id\",
						 RD.Evn_id as \"Evn_id\",
						 RD.Evn_id as \"Evn_rid\",
						 RD.Person_id as \"Person_id\",
						 rtrim(coalesce(RD.Person_SurName,'')) || ' ' || rtrim(coalesce(RD.Person_FirName,'')) || ' ' || rtrim(coalesce(RD.Person_SecName, '')) as \"Person_FIO\",
						 to_char(RD.Person_BirthDay, 'dd.mm.yyyy') as \"Person_BirthDay\",
						 coalesce(EPL.EvnPL_NumCard, EPS.EvnPS_NumCard) as \"Evn_NumCard\",
						 R.RegistryType_id as \"RegistryType_id\",
						 LS.LpuSection_FullName as \"LpuSection_FullName\",
						 MP.Person_Fio as \"MedPersonal_Fio\",
						 to_char(coalesce(EVPL.EvnVizitPL_setDT, EPS.EvnPS_setDT), 'dd.mm.yyyy') as \"Evn_setDate\",
						 to_char(coalesce(EVPL.EvnVizitPL_setDT, EPS.EvnPS_disDT), 'dd.mm.yyyy') as \"Evn_disDate\",
						 null as \"CmpCallCard_id\"
						-- end select
					from
						-- from
						{$this->scheme}.v_{$this->RegistryDoubleObject} RD
						left join v_EvnPL EPL on EPL.EvnPL_id = RD.Evn_id
						left join v_EvnPS EPS on EPS.EvnPS_id = RD.Evn_id
						left join v_EvnVizitPL EVPL on EVPL.EvnVizitPL_pid = EPL.EvnPL_id and EVPL.EvnVizitPL_Index = EVPL.EvnVizitPL_Count - 1
						left join v_EvnSection ES on ES.EvnSection_pid = EPS.EvnPS_id and ES.EvnSection_Index = ES.EvnSection_Count - 1
						left join v_LpuSection LS on LS.LpuSection_id = ISNULL(EVPL.LpuSection_id, ES.LpuSection_id)
						left join {$this->scheme}.v_Registry R on R.Registry_id = RD.Registry_id
						left join lateral (
							select Person_Fio, MedPersonal_id from v_MedPersonal where MedPersonal_id = ISNULL(EVPL.MedPersonal_id, ES.MedPersonal_id) limit 1
						) as MP on true
						{$join}
						-- end from
					where
						-- where
						RD.Registry_id = :Registry_id
						" . (count($filterList) > 0 ? "and " . implode(" and ", $filterList) : "") . "
						-- end where
					order by
						-- order by
						RD.Person_SurName,
						RD.Person_FirName,
						RD.Person_SecName
						-- end order by
				";
				break;
		}
		if ( !empty($data['withoutPaging']) ) {
			$res = $this->db->query($query, $data);

			if ( is_object($res) ) {
				return $res->result('array');
			}
		} else {
			$result = $this->db->query(getLimitSQLPH($query, $data['start'], $data['limit']), $data);
			$result_count = $this->db->query(getCountSQLPH($query), $data);

			if ( is_object($result_count) ) {
				$cnt_arr = $result_count->result('array');
				$count = $cnt_arr[0]['cnt'];
				unset($cnt_arr);
			} else {
				$count = 0;
			}

			if ( is_object($result) ) {
				$response = array();
				$response['data'] = $result->result('array');
				$response['totalCount'] = $count;
				return $response;
			}
		}
		
        return false;

    }

    /**
     * Получение идентификатора класса ошибки по коду ошибки
     * @param $data
     * @return
     */
	public function getRegistryErrorClassId($data)
    {
		$query = "
            SELECT
                RegistryErrorClass_id as \"RegistryErrorClass_id\"
            FROM
                {$this->scheme}.v_RegistryErrorType
            WHERE
                RegistryErrorType_Code = cast(:OSHIB as varchar)
            limit 1
        ";
		$resp = $this->db->query($query, $data);
		if (is_object($resp)) {
			return $resp->result('array');
		}
		
		return false;
	}

    /**
     * Получение списка типов реестров, входящих в объединенный реестр
     * @param int $Registry_pid
     * @return array|bool
     */
	public function getUnionRegistryTypes($Registry_pid = 0)
    {
		$query = "
			select distinct
			    r.RegistryType_id as \"RegistryType_id\"
			from
			    {$this->scheme}.v_RegistryGroupLink rgl
				inner join {$this->scheme}.v_Registry r on r.Registry_id = rgl.Registry_id
			where
			    rgl.Registry_pid = :Registry_pid
		";
		$result = $this->db->query($query, array('Registry_pid' => $Registry_pid));

		if ( !is_object($result) ) {
			return false;
		}

		$registryTypes = [];
		$resp = $result->result('array');

		foreach ( $resp as $rec ) {
			$registryTypes[] = $rec['RegistryType_id'];
		}

		return $registryTypes;
	}

	/**
	 *	Получение данных для выгрузки реестров в XML
	 */
	public function loadRegistrySCHETForXmlUsing($data)
    {
		// шапка
		$query = "
			exec {$this->scheme}.p_Registry_expScet @Registry_id = :Registry_id
		";
		$result = $this->db->query($query, ['Registry_id' => $data['Registry_id']]);

		if ( is_object($result) ) {
			$header = $result->result('array');

			if (!empty($header[0])) {
				return [$header[0]];
			}
		}

		return false;
	}

    /**
     * Кэширование некоторых параметров реестра в зависимости от его типа
     * @param array $data
     * @param bool $force
     */
	public function setRegistryParamsByType($data = [], $force = false)
    {
		parent::setRegistryParamsByType($data, $force);

		switch ( $this->RegistryType_id ) {
			case 1:
				$this->RegistryDataObject = 'RegistryDataEvnPS';
				$this->RegistryErrorObject = 'RegistryErrorEvnPS';
				$this->RegistryDoubleObject = 'RegistryDoubleEvnPS';
			break;

			case 2:
				$this->RegistryDataObject = 'RegistryData';
				$this->RegistryErrorObject = 'RegistryError';
			break;

			case 6:
				$this->RegistryDataObject = 'RegistryDataCmp';
				$this->RegistryErrorObject = 'RegistryErrorCmp';
				$this->RegistryDataEvnField = 'CmpCallCard_id';
				$this->RegistryDoubleObject = 'RegistryCmpDouble';
			break;

			case 7:
			case 9:
				$this->RegistryDataObject = 'RegistryDataDisp';
				$this->RegistryErrorObject = 'RegistryErrorDisp';
			break;

			case 11:
			case 12:
				$this->RegistryDataObject = 'RegistryDataProf';
				$this->RegistryErrorObject = 'RegistryErrorProf';
			break;

			case 15:
				$this->RegistryDataObject = 'RegistryDataPar';
				$this->RegistryErrorObject = 'RegistryErrorPar';
			break;
		}
	}

	/**
	 * Получение пустой строки для услуги в выгрузке реестра в XML
	 * @todo Реализовать получение массива через парсинг шаблона
	 */
	public function getEmptyUslugaXmlRow()
    {
		return [
			[
				 'IDSERV' => null,
                'LPU_USL' => null,
                'LPU_1_USL' => null,
                'PODR_USL' => null,
                'PROFIL_USL' => null,
                'PROFIL_K_USL' => null,
                'P_CEL_USL' => null,
                'DATE_IN_USL' => null,
                'DATE_OUT_USL' => null,
                'TIME_IN' => null,
                'TIME_OUT' => null,
                'DS_USL' => null,
                'RAZDEL_USL' => null,
                'CODE_USL' => null,
                'KOL_USL' => null,
                'PRVS_USL' => null,
                'CODE_MD_USL' => null,
                'NPR_DC_USL' => null,
                'DST_MO' => null,
                'NPL' => null,
                'UP_ST' => null,
                'DENTAL' => null,
                'DENTAL_KPU' => null,
                'DENTAL_KOL' => null,
                'DENTAL_SIDE' => null,
                'CLASS_BLACK' => null,
                'COMENTU' => null
			]
		];
	}

    /**
     * Получение имени файла для выгрузки реестра
     * @param array $data
     * @return array|bool
     */
	public function getRegistryFileNameForExport($data = []) {
		if ( empty($data['Registry_id']) ) {
			return false;
		}

		$registryData = [];

		if ( array_key_exists('RegistryData', $data) && is_array($data['RegistryData']) && count($data['RegistryData']) > 0 ) {
			$registryData = $data['RegistryData'];
		} else {
			$res = $this->dbmodel->GetRegistryXmlExport($data);

			if ( !is_array($res) || count($res) == 0 ) {
				return false;
			}

			$registryData = $res[0];
		}

		$result = [
			'dataFile' => '',
			'persFile' => ''
		];

		$result['dataFile'] = $registryData['Lpu_f003mcod'] . "_" . $registryData['Registry_endMonth'] . $registryData['Registry_FileNum'];
		$result['persFile'] = 'L' . $result['dataFile'];

		if ( $registryData['RegistryType_id'] == 13 ) {
			if ( 13 == $registryData['RegistryGroupType_id'] ) {
				$result['dataFile'] = "B" . $result['dataFile'];
			} else if ( 14 == $registryData['RegistryGroupType_id'] ) {
				$result['dataFile'] = "Z" . $result['dataFile'];
			} else {
				$result['dataFile'] = "H" . $result['dataFile'];
			}
		} else {
			if ( 'bud' == $registryData['PayType_SysNick'] ) {
				$result['dataFile'] = "B" . $result['dataFile'];
			} else {
				$result['dataFile'] = "H" . $result['dataFile'];
			}
		}

		return $result;
	}

    /**
     * Возвращает список настроек ФЛК
     * @param $data
     * @return bool
     */
	public function loadRegistryEntiesSettings($data)
	{
		if(!$data['RegistryType_id']) return false;
		
		if( $data['RegistryType_id'] == 13 && isset($data['RegistryGroupType_id']) ){
			$where = ' AND RegistryGroupType_id = '.$data['RegistryGroupType_id'];
		}else{
			$where = ' AND RegistryType_id = '.$data['RegistryType_id'];
		}
		
		$params = array();
		$query = "
			SELECT
			    FLKSettings_id as \"FLKSettings_id\",
			    cast(getdate() as timestamp) as \"DD\",
			    RegistryType_id as \"RegistryType_id\",
			    FLKSettings_EvnData as \"FLKSettings_EvnData\",
			    FLKSettings_PersonData as \"FLKSettings_PersonData\"
			FROM v_FLKSettings
			WHERE 
				getdate() between FLKSettings_begDate and 
					case when FLKSettings_endDate is null
						then '2030-01-01'
						else FLKSettings_endDate
					end
				AND FLKSettings_EvnData ILIKE '%ekb%'
			limit 1
		".$where;
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

    /**
     *  ФЛК контроль
     * @param $xml_data
     * @param $xsd_tpl
     * @param string $type
     * @param string $output_file_name
     * @return bool
     */
	public function Reconciliation($xml_data, $xsd_tpl, $type = "string", $output_file_name = 'err_xml.html')
    {
		if( !file_exists($xsd_tpl) || !$xml_data) return false;
		
		libxml_use_internal_errors(true);  
		$xml = new DOMDocument();
	
		if($type == 'file'){
			$xml->load($xml_data); 
		}
		elseif($type == 'string'){
			$xml->loadXML($xml_data);   
		}
	
		if (!@$xml->schemaValidate($xsd_tpl)) {
			ob_start();
			$this->libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();
	
			file_put_contents($output_file_name, $res_errors);
			return false;
		} else {
			return true;
		}
	}
	
	/**
	* ФЛК контроль
	* Метод для формирования листа ошибок при сверке xml по шаблону xsd
	* @return (string)
	*/
	public function libxml_display_errors() 
	{
		$errors = libxml_get_errors();		
		foreach ($errors as $error) 
		{
			$return = "<br/>\n";	
			switch($error->level) 
			{
				case LIBXML_ERR_WARNING:
					$return .= "<b>Warning $error->code</b>: ";
					break;
				case LIBXML_ERR_ERROR:
					$return .= "<b>Error $error->code</b>: ";
					break;
				case LIBXML_ERR_FATAL:
					$return .= "<b>Fatal Error $error->code</b>: ";
					break;
			} 
	
			$return .= trim($error->message);
			if($error->file) 
			{
				$return .=    " in <b>$error->file</b>";
			}
	
			$return .= " on line <b>$error->line</b>\n";
			print $return;        
		}	
		libxml_clear_errors();
	}

	/**
	 * Запрос для проверки наличия данных для вкладки "Дублеи посещений"
	 */
	public function getRegistryDoubleCheckQuery($scheme = 'dbo')
    {
		return "
		    select
		        Evn_id as \"Evn_id\"
		    from (
                (select Evn_id from {$scheme}.v_RegistryDouble where Registry_id = R.Registry_id limit 1)
                union all
                (select  Evn_id from {$scheme}.v_RegistryCmpDouble where Registry_id = R.Registry_id limit 1)
			) t
		";
	}


}