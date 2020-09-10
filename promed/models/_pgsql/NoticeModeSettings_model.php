<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * NoticeModeSettings_model - модель для работы c режимами уведомлений о госпитализации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @version			2019
 */

class NoticeModeSettings_model extends swPgModel {
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Проверяем есть ли такие же настройки уведомлений для МО
	 */
	function checkNotifySettingsExist($data) {
		$whereLpu = 'nms.Lpu_id = :Lpu_id';

		if ($data['Lpu_sid'] == null) {
			$whereLpu = 'nms.Lpu_id is null';
		}
		$params = [
			'Lpu_id' => $data['Lpu_sid'],
			'NoticeModesType_id' => $data['NoticeModesType_id'],
			'NoticeFreqUnitsType_id' => $data['NoticeFreqUnitsType_id'],
			'NoticeModeLink_Frequency' => $data['NoticeModeLink_Frequency'],
		];

		$query = "
			select *
			from v_NoticeModeLink nml
			left join v_NoticeModeSettings nms on nms.NoticeModeSettings_id = nml.NoticeModeSettings_id
			where {$whereLpu} and Region_id = dbo.getRegion()
				and nml.NoticeModesType_id = :NoticeModesType_id
				and nml.NoticeFreqUnitsType_id = :NoticeFreqUnitsType_id
				and nml.NoticeModeLink_Frequency = :NoticeModeLink_Frequency
			LIMIT 1
		";

		$result = $this->getFirstResultFromQuery($query, $params);

		if (!empty($result)) {
			return ['success' => true, 'exist' => true];
		}

		return ['success' => true, 'exist' => false];
	}
	
	/**
	 * Проверяем есть ли в базе уведомлений данное МО
	 * @param $data
	 * @return array
	 */
	function checkLpuSettingsExist($data) {
		$where = 'Lpu_id = :Lpu_id';

		if ($data['Lpu_sid'] == null) {
			$where = 'Lpu_id is null';
		}

		$query = "
			select NoticeModeSettings_id as \"NoticeModeSettings_id\"
			from v_NoticeModeSettings
			where {$where}
		";

		$result = $this->getFirstResultFromQuery($query, [
			'Lpu_id' => $data['Lpu_sid']
		]);

		if (!empty($result)) {
			return ['success' => true, 'exist' => true];
		}

		return ['success' => true, 'exist' => false];
	}

	/**
	 * Метод получения данных о пользователях которых нужно уведомить о госпитализации
	 * @param null $data
	 * @param int $evnStatus
	 * @return array
	 */
	function getDirectionDataForNotify($data = null, $evnStatus = 17) {
		$filter = '';
		$cronFilter = '';
		$params = [];
		$limit = '';

		$withLpu = 'where withLpu.NoticeModeLink_id is not null';

		if (isset($data['EvnDirection_id'])) {
			$filter = "AND ED.EvnDirection_id = :EvnDirection_id";
			$params['EvnDirection_id'] = $data['EvnDirection_id'];
			$withLpu = '';
			$limit = 'LIMIT 1';
		}

		if ($evnStatus == 17) {
			$cronFilter = "and (to_date(notify.\"EvnDirection_setDateTime\"::TEXT, 'yyyy.mm.dd HH24:MI:SS') > to_date(dbo.tzGetDate()::TEXT, 'yyyy.mm.dd HH24:MI:SS') )";
		}

		$params['EvnStatus_id'] = $evnStatus;

		$select = "
			select
				ED.EvnDirection_id as \"EvnDirection_id\",
				case when NLA.NewslatterAccept_IsSMS = 2 then NLA.NewslatterAccept_Phone 
					else null
				end as \"Phone\",
				case when NLA.NewslatterAccept_IsEmail = 2 then NLA.NewslatterAccept_Email 
					else null
				end as \"Email\",
				ED.Person_id as \"Person_id\",
				ED.Lpu_id as \"Lpu_id\",
				P.Sex_id as \"PersonSex_id\",
				PDEP.DeputyKind_id as \"DeputyKind_id\",
				PDEPSTATE.Sex_id as \"DeputySex_id\",
				LD.Lpu_Name as \"Lpu_did_Name\",
				LD.UAddress_Address as \"Lpu_did_Address\",
				LS.Lpu_Nick as \"Lpu_sid_Nick\",
				LS.UAddress_Address as \"Lpu_sid_Address\",
				nfut.NoticeFreqUnitsType_Name as \"NoticeFreqUnitsType_Name\",
				nfut.NoticeFreqUnitsType_id as \"NoticeFreqUnitsType_id\",
				nmt.NoticeModesType_Name as \"NoticeModesType_Name\",
				nmt.NoticeModesType_id as \"NoticeModesType_id\",
				nml.NoticeModeLink_Frequency as \"NoticeModeLink_Frequency\",
				nml.NoticeModeLink_id as \"NoticeModeLink_id\",
				to_char(coalesce(coalesce(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 'yyyy.mm.dd')
					|| ' ' || to_char(coalesce(coalesce(TTG.TimetableGraf_begTime, TTP.TimetablePar_begTime), TTS.TimetableStac_setDate), 'HH24:MI:SS') as \"EvnDirection_setDateTime\",
				case when PDEPSTATE.Person_id is not null THEN PDEPSTATE.Person_SurName || ' ' || PDEPSTATE.Person_FirName || ' ' || coalesce(PDEPSTATE.Person_SecName, '') ELSE '' END as \"DeputyPerson_Fio\",
				P.\"Person_Surname\" || ' ' || P.\"Person_Firname\" || ' ' || P.\"Person_Secname\" as \"Person_Fio\"
			";

		$query = "
			with withLpu AS (
				{$select}
				from v_EvnDirection_all ED
				left join v_Lpu_all LD on LD.Lpu_id = ED.Lpu_did
				left join v_Lpu_all LS on LS.Lpu_id = ED.Lpu_sid
				left join PersonDeputy PDEP on PDEP.Person_id = ED.Person_id
				left join v_PersonState PDEPSTATE on PDEPSTATE.Person_id = PDEP.Person_pid
				left join v_TimeTableGraf_lite TTG on TTG.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetablePar TTP on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableMedService_lite TTMS on TTMS.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableResource_lite TTR on TTR.EvnDirection_id = ED.EvnDirection_id
				left join v_NewslatterAccept NLA on NLA.Person_id = ED.Person_id
				left join v_NoticeModeSettings NMS on NMS.Lpu_id = ED.Lpu_id
				left join v_NoticeModeLink NML on NML.NoticeModeSettings_id = NMS.NoticeModeSettings_id
				left join v_NoticeModesType nmt on nmt.NoticeModesType_id = nml.NoticeModesType_id
				left join v_NoticeFreqUnitsType nfut on nfut.NoticeFreqUnitsType_id = nml.NoticeFreqUnitsType_id
				JOIN LATERAL (
					select
						coalesce(RTRIM(PS.Person_Surname), '') as \"Person_Surname\",
						coalesce(RTRIM(PS.Person_Firname), '') as \"Person_Firname\",
						coalesce(RTRIM(PS.Person_Secname), '') as \"Person_Secname\",
						PS.Sex_id
					from 
						v_PersonState PS
					where 
						PS.Person_id = ED.Person_id
					limit 1
				) P on true
				where ED.DirType_id in (1) 
					and ED.EvnQueue_id is null 
					and ED.EvnStatus_id = :EvnStatus_id and NLA.NewslatterAccept_endDate is null
					{$filter}
			), 
			withoutLPU as (
				{$select}
				from v_NoticeModeLink nml
				left join v_NoticeModesType nmt on nmt.NoticeModesType_id = nml.NoticeModesType_id
				left join v_NoticeFreqUnitsType nfut on nfut.NoticeFreqUnitsType_id = nml.NoticeFreqUnitsType_id
				left join v_NoticeModeSettings nms on nms.NoticeModeSettings_id = nml.NoticeModeSettings_id
				cross join v_EvnDirection_all ED
				left join v_Lpu_all LD on LD.Lpu_id = ED.Lpu_did
				left join v_Lpu_all LS on LS.Lpu_id = ED.Lpu_sid
				left join PersonDeputy PDEP on PDEP.Person_id = ED.Person_id
				left join v_PersonState PDEPSTATE on PDEPSTATE.Person_id = PDEP.Person_pid
				left join v_TimeTableGraf_lite TTG on TTG.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetablePar TTP on TTP.TimetablePar_id = ED.TimetablePar_id
				left join v_TimetableStac_lite TTS on TTS.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableMedService_lite TTMS on TTMS.EvnDirection_id = ED.EvnDirection_id
				left join v_TimetableResource_lite TTR on TTR.EvnDirection_id = ED.EvnDirection_id
				left join v_NewslatterAccept NLA on NLA.Person_id = ED.Person_id
				JOIN LATERAL (
					select
						coalesce(RTRIM(PS.Person_Surname), '') as \"Person_Surname\",
						coalesce(RTRIM(PS.Person_Firname), '') as \"Person_Firname\",
						coalesce(RTRIM(PS.Person_Secname), '') as \"Person_Secname\",
						PS.Sex_id
					from 
						v_PersonState PS
					where 
						PS.Person_id = ED.Person_id
					limit 1
				) P on true
				where nms.Lpu_id is null 
					and ED.DirType_id in (1) 
					and ED.EvnQueue_id is null 
					and ED.EvnStatus_id = :EvnStatus_id
				and TTS.TimetableStac_setDate > (select dbo.tzGetDate() as date)
				{$filter}
				and ED.Lpu_id not in (select Lpu_id
						from v_NoticeModeSettings
						where Lpu_id is not null)
			)
		
		select * from(
			select 
				withLpu.\"EvnDirection_setDateTime\",
				withLpu.\"EvnDirection_id\",
				withLpu.\"Phone\",
				withLpu.\"Email\",
				withLpu.\"Person_id\",
				withLpu.\"Person_Fio\",
				withLpu.\"PersonSex_id\",
				withLpu.\"DeputyKind_id\",
				withLpu.\"DeputyPerson_Fio\",
				withLpu.\"DeputySex_id\",
				withLpu.\"Lpu_id\",
				withLpu.\"NoticeFreqUnitsType_Name\",
				withLpu.\"NoticeFreqUnitsType_id\",
				withLpu.\"NoticeModesType_Name\",
				withLpu.\"NoticeModesType_id\",
				withLpu.\"NoticeModeLink_Frequency\",
				withLpu.\"NoticeModeLink_id\",
				withLpu.\"Lpu_did_Name\",
				withLpu.\"Lpu_did_Address\",
				withLpu.\"Lpu_sid_Nick\",
				withLpu.\"Lpu_sid_Address\"
			from withLpu
			{$withLpu}
			
			union 

			select 
				withoutLPU.\"EvnDirection_setDateTime\",
				withoutLPU.\"EvnDirection_id\",
				withoutLPU.\"Phone\",
				withoutLPU.\"Email\",
				withoutLPU.\"Person_id\",
				withoutLPU.\"Person_Fio\",
				withoutLPU.\"PersonSex_id\",
				withoutLPU.\"DeputyKind_id\",
				withoutLPU.\"DeputyPerson_Fio\",
				withoutLPU.\"DeputySex_id\",
				withoutLPU.\"Lpu_id\",
				withoutLPU.\"NoticeFreqUnitsType_Name\",
				withoutLPU.\"NoticeFreqUnitsType_id\",
				withoutLPU.\"NoticeModesType_Name\",
				withoutLPU.\"NoticeModesType_id\",
				withoutLPU.\"NoticeModeLink_Frequency\",
				withoutLPU.\"NoticeModeLink_id\",
				withoutLPU.\"Lpu_did_Name\",
				withoutLPU.\"Lpu_did_Address\",
				withoutLPU.\"Lpu_sid_Nick\",
				withoutLPU.\"Lpu_sid_Address\"
			from withoutLPU
		) notify
		where (notify.\"Phone\" is not null or notify.\"Email\" is not null)
		{$cronFilter}
		{$limit}
		";

		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		}

		return [];
	}

	/**
	 * Алгоритм для поиска и фильтрации нужных уведомлений
	 * @return array
	 * @throws Exception
	 */
	function getDataForSendNotify() {

		$notifications = $this->getDirectionDataForNotify(['EvnDirection_id' => 590101000024006]);

		$result = [];

		foreach ($notifications as $item) {
			$isNeedNotify = false;

			if ($item['NoticeModesType_id'] == 2) {
				//не уведомлять бирки назначеннные без точного времени
				if ($item['EvnDirection_setDateTime']->format('H') == '00') continue;

				$interval = $this->getInterval($item['NoticeFreqUnitsType_id'], $item['EvnDirection_setDateTime']);
				if ($interval == $item['NoticeModeLink_Frequency']) {
					$isNeedNotify = $this->checkUserLastNotify(['Person_id' => $item['Person_id'],
						'NoticeModeLink_id' => $item['NoticeModeLink_id']]);
				}
			} else {
				$params = [
					'Person_id' => $item['Person_id'],
					'NoticeModeLink_id' => $item['NoticeModeLink_id'],
					'Period' => $item['NoticeModeLink_Frequency'],
					'FreqUnits' => $item['NoticeFreqUnitsType_id']
				];
				$isNeedNotify = $this->checkUserLastNotifyByInterval($params);
			}

			if ($isNeedNotify) {
				array_push($result, $item);
			}
		}

		return $result;
	}

	/**
	 * Метод для генерации текста сообщений для уведомлений
	 * @param $data
	 * @param $evnStatus
	 * @return string
	 */
	function generateTextForNotify($data, $evnStatus = 17) {

		$patientStr= '';
		$oldDirectionDate = '';

		if (is_null($data['DeputyKind_id'])) {
			$helloStr = $data['PersonSex_id'] == 1 ? 'Уважаемый ' : 'Уважаемая ';
			$helloStr .= $data['Person_Fio'] . "!";
		} else {
			$helloStr = $data['DeputySex_id'] == 1 ? 'Уважаемый ' : 'Уважаемая ';
			$helloStr .= $data['DeputyPerson_Fio'] . "!";
			$patientStr = "пациента " . $data['Person_Fio'] . " ";
		}

		$withRespectStr = "\xAС уважением, " . $data['Lpu_sid_Nick'] . ".";
		$directionDate = $data['EvnDirection_setDateTime'];

		if (isset($data['old_EvnDirection_setDateTime'])) {
			$oldDirectionDate = $data['old_EvnDirection_setDateTime'];
		}

		$deputyText = is_null($data['DeputyKind_id']) ? "" : $patientStr;

		switch ($evnStatus) {
			case '17': {
				$notifyText = $helloStr . " 
					\xAНапоминаем о плановой госпитализации " . $deputyText . $directionDate . " 
					\xAв " . $data['Lpu_did_Name'] . " " . $data['Lpu_did_Address'] . ". 
					" . $withRespectStr;
				break;
			}
//			case 'change': {
//				$notifyText = $helloStr .
//					" Изменены параметры плановой госпитализации " . $deputyText . $oldDirectionDate . " 
//				в " . $data['old_Lpu_did_Name'] . " " . $data['old_Lpu_did_Address'] . ".
//				Новая дата госпитализации: " . $directionDate . ". 
//				Новое время госпитализации: %дата и время госпитализации%. 
//				Новая медицинская организация госпитализации: " . $data['Lpu_did_Name'] . " " . $data['Lpu_did_Address'] . ". " . $withRespectStr;
//				break;
//			}
			case '12': {
				$notifyText = $helloStr .
					" \xAОтменена плановая госпитализация " . $deputyText . $directionDate . " 
				\xAв " . $data['Lpu_did_Name'] . " " . $data['Lpu_did_Address'] . ". " .$withRespectStr;
				break;
			}
			default: {
				$notifyText = '';
				break;
			}
		}

		return $notifyText;
	}

	/**
	 * подготовка к отправке уведомлений
	 * @param $data
	 * @param $evnStatus
	 * @throws Exception
	 */
	function prepareNotify($data, $evnStatus) {

		$this->load->helper('Notify');
		$this->load->library('email');

		$response = $this->getDirectionDataForNotify($data, $evnStatus);

		foreach ($response as $item) {
			$notifyText = $this->generateTextForNotify($item, $evnStatus);
			$this->sendNotify($item, $notifyText);
		}
	}

	/**
	 * Метод для отпарвки уведомлений(СМС, E-mail)
	 * @param $data
	 * @param $text
	 * @throws Exception
	 */
	function sendNotify($data, $text) {
		
		$this->load->helper('Notify');
		$this->load->library('email');
		
		if (isset($data['Email'])) {
			@$resultsend = $this->email->sendPromed($data['Email'], 'Уведомление', $text);
			if (!$resultsend) {
				throw new Exception("Не удалось выполнить отправление письма", 20);
			}
		}

		if (isset($data['Phone'])) {
			$data['Phone'] = substr($data['Phone'], 2, 10);
			$params = array(
				'sms_id' => 'nl_'.$data['EvnDirection_id'].'_'.$data['Phone'],
				'pmUser_Phone' => $data['Phone'],
				'text' => $text
			);
			try {
				sendPmUserNotifySMS($params);
			} catch (Exception $e) {
				throw new Exception("Не удалось выполнить отправление СМС", 20);
			}
		}
	}

	/**
	 * Метод для сохранения истории уведомлений
	 * @param $data
	 * @return mixed
	 * @throws Exception
	 */
	function saveNoticeHistory($data) {

		$params = [
			'NoticeHistory_id' => null,
			'NoticeModeLink_id' => $data['NoticeModeLink_id'],
			'Person_id' => $data['Person_id'],
			'pmUser_id' => 1
		];
		$query = "
			select 
				NoticeHistory_id as \"NoticeHistory_id\", 
				Error_Code as \"Error_Code\", 
				Error_Message as \"Error_Msg\"
			from p_NoticeHistory_ins(
				NoticeHistory_id := :NoticeHistory_id,
				NoticeModeLink_id := :NoticeModeLink_id,
				Person_id := :Person_id,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			throw new Exception('Ошибка при сохранении истории оповещений.');
		}
		return $result->result('array');
	}

	/**
	 *
	 * @param $freqUnit
	 * @param $stacDate
	 * @return float|int
	 * @throws Exception
	 */
	private function getInterval($freqUnit, $stacDate) {

		$currentDate = new DateTime($freqUnit != 1 ? 'Today' : '');
		$interval = $currentDate->diff($stacDate);

		$period = function ($freqUnit) use ($interval, $currentDate, $stacDate) {
			$periods = [1 => 'h', 2 => 'd', 3 => 'w', 4 => 'm'];
			$p = $periods[$freqUnit];

			if ($freqUnit == 4) return $interval->d/30;
			if ($freqUnit == 3) return $interval->d/7;
			if ($freqUnit == 2 && $interval->h > 1) return 0;
			if ($freqUnit == 1) {
				$curTimestamp = $currentDate->format('U');
				$stacTimestamp = $stacDate->format('U');
				$difference = round(abs($stacTimestamp - $curTimestamp)/3600, 1);

				$tmpTime = $difference + 1;
				if ( substr($difference, -1) <= 5 ) {
					return floor($difference);
				} elseif (substr($tmpTime, -1) > 5) {
					return round($tmpTime);
				} else {
					return 0;
				}
			}

			return $interval->$p;
		};

		return $period($freqUnit);
	}

	private function checkUserLastNotifyByInterval($data) {

		$datepart = [1 => 'hour', 2 => 'day', 3 => 'week', 4 => 'month'];
		$data['Datepart'] = $datepart[$data['FreqUnits']];
		
		$query = "
			select 
				*
			from 
				v_NoticeHistory
			where 
				Person_id = :Person_id and NoticeModeLink_id = :NoticeModeLink_id and 
					NoticeHistory_insDT > dateadd(:Datepart, -:Period, (select dbo.tzGetDate() as date))
		";

		$result = $this->db->query($query, $data);

		if (is_object($result) && count($result->result('array')) > 0) {
			return false;
		}

		return true;
	}

	private function checkUserLastNotify($data) {

		$query = "
			select 
				*
			from 
				v_NoticeHistory
			where 
				Person_id = :Person_id and NoticeModeLink_id = :NoticeModeLink_id 
				and to_char(NoticeHistory_insDT, 'YYYY-mm-dd HH24:MI:SS') = (select dbo.tzGetDate() as date)
		";

		$result = $this->db->query($query, $data);

		if (is_object($result) && count($result->result('array')) > 0) {
			return false;
		}

		return true;
	}

	/**
	 * Получение данных для редактирования режима уведомлений
	 */
	function loadNoticeModeSettingsForm($data) {

		$params = ['NoticeModeSettings_id' => $data['NoticeModeSettings_id']];

		$query = "
			select
				nms.NoticeModeSettings_id as \"NoticeModeSettings_id\",
				nms.NoticeModeSettings_IsSMS as \"NoticeModeSettings_IsSMS\",
				nms.NoticeModeSettings_IsEmail as \"NoticeModeSettings_IsEmail\",
				nms.Lpu_id as \"Lpu_id\"
			from 
				v_NoticeModeSettings nms
			where 
				nms.NoticeModeSettings_id = :NoticeModeSettings_id
			LIMIT 1
		";

		$result = $this->getFirstRowFromQuery($query, $params);

		return array($result);
	}

	/**
	 * Получение списка МО с установленными режимами
	 * @return array|bool
	 */
	function loadNoticeModeSettingsGrid() {
		$query = "
			select
				nms.NoticeModeSettings_id as \"NoticeModeSettings_id\",
				case 
					when nms.Lpu_id is null then 'Все МО' 
					else L.Lpu_Nick 
				end as \"Lpu_Name\",
				nms.NoticeModeSettings_IsSMS as \"NoticeModeSettings_IsSMS\",
				nms.NoticeModeSettings_IsEmail as \"NoticeModeSettings_IsEmail\"
			from 
				v_NoticeModeSettings nms
				left join v_Lpu L on L.Lpu_id = nms.Lpu_id
			where 
				nms.Region_id = dbo.getRegion()
		";

		$result = $this->db->query($query);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Получение списка режимов уведомлений
	 * @param $data
	 * @return array|bool
	 */
	function loadNoticeModeLinkGrid($data) {
		$params = ['NoticeModeSettings_id' => $data['NoticeModeSettings_id']];

		$query = "
			select
				nmt.NoticeModesType_Name as \"NoticeModesType_Name\",
				nfut.NoticeFreqUnitsType_Name as \"NoticeFreqUnitsType_Name\",
				nml.NoticeModeLink_Frequency as \"NoticeModeLink_Frequency\",
				nml.NoticeModeLink_id as \"NoticeModeLink_id\"
			from 
				v_NoticeModeLink nml
				left join v_NoticeModesType nmt on nmt.NoticeModesType_id = nml.NoticeModesType_id
				left join v_NoticeFreqUnitsType nfut on nfut.NoticeFreqUnitsType_id = nml.NoticeFreqUnitsType_id
			where 
				nml.NoticeModeSettings_id = :NoticeModeSettings_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}

		return $result->result('array');
	}

	/**
	 * Получение данных для редактирования режима уведомлений
	 */
	function loadNoticeModeLinkForm($data) {

		$params = ['NoticeModeLink_id' => $data['NoticeModeLink_id']];

		$query = "
			select
				nml.NoticeModeLink_id as \"NoticeModeLink_id\",
				nml.NoticeModeSettings_id as \"NoticeModeSettings_id\",
				nml.NoticeModesType_id as \"NoticeModesType_id\",
				nml.NoticeFreqUnitsType_id as \"NoticeFreqUnitsType_id\",
				nml.NoticeModeLink_Frequency as \"NoticeModeLink_Frequency\"
			from 
				v_NoticeModeLink nml
			where 
				nml.NoticeModeLink_id = :NoticeModeLink_id
			LIMIT 1
		";

		$result = $this->getFirstRowFromQuery($query, $params);

		return array($result);
	}

	/**
	 * Сохранение настроек уведомлений
	 */
	function saveNoticeModeSettings($data) {
		$this->beginTransaction();

		$response = $this->saveSettings($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->commitTransaction();
		return $response;
	}

	/**
	 * Сохранение режима уведомлений
	 */
	function saveNoticeModeLink($data) {
		$this->beginTransaction();

		$response = $this->saveNotifications($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $response;
		}
		
		$this->commitTransaction();
		return $response;
	}
	
	/**
	 * Сохранение режима уведомлений
	 * @param $data
	 * @return array
	 */
	function saveNotifications($data) {
		$params = [
			'NoticeModeLink_id' => $data['NoticeModeLink_id'] ?? null,
			'NoticeModeSettings_id' => $data['NoticeModeSettings_id'],
			'NoticeModesType_id' => $data['NoticeModesType_id'],
			'NoticeFreqUnitsType_id' => $data['NoticeFreqUnitsType_id'],
			'NoticeModeLink_Frequency' => $data['NoticeModeLink_Frequency'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		];

		$procedure = 'p_NoticeModeLink_ins';
		if (!empty($data['NoticeModeLink_id'])) {
			$procedure = 'p_NoticeModeLink_upd';
		}

		$query = "
			select 
				NoticeModeLink_id as \"NoticeModeLink_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				NoticeModeLink_id := :NoticeModeLink_id,
				NoticeModeSettings_id := :NoticeModeSettings_id,
				NoticeModesType_id := :NoticeModesType_id,
				NoticeFreqUnitsType_id := :NoticeFreqUnitsType_id,
				NoticeModeLink_Frequency := :NoticeModeLink_Frequency,
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка сохранения режима уведомлений'));
		}
		return $result->result('array');
	}

	/**
	 * Сохранение режима уведомлений
	 * @param $data
	 * @return array
	 */
	function saveSettings($data) {
		$params = [
			'NoticeModeSettings_id' => $data['NoticeModeSettings_id'],
			'NoticeModeSettings_IsSMS' => $data['NoticeModeSettings_IsSMS'] ?? null,
			'NoticeModeSettings_IsEmail' => $data['NoticeModeSettings_IsEmail'] ?? null,
			'Lpu_id' => $data['Lpu_sid'],
			'pmUser_id' => $data['pmUser_id']
		];

		$procedure = 'p_NoticeModeSettings_ins';
		if (!empty($data['NoticeModeSettings_id'])) {
			$procedure = 'p_NoticeModeSettings_upd';
		}

		$query = "
			select 
				NoticeModeSettings_id as \"NoticeModeSettings_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				NoticeModeSettings_id := :NoticeModeSettings_id,
				Lpu_id := :Lpu_id,
				NoticeModeSettings_IsSMS := :NoticeModeSettings_IsSMS,
				NoticeModeSettings_IsEmail := :NoticeModeSettings_IsEmail,
				Region_id := dbo.getRegion(),
				pmUser_id := :pmUser_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка сохранения режима уведомлений'));
		}
		return $result->result('array');
	}

	/**
	 * Удаление режима уведомлений
	 */
	function deleteNoticeModeSettings($data) {
		$params = array('NoticeModeSettings_id' => $data['NoticeModeSettings_id']);

		$this->beginTransaction();

		$query = "
			select 
				NoticeModeLink_id as \"NoticeModeLink_id\"
			from 
				v_NoticeModeLink nms
			where 
				nms.NoticeModeSettings_id = :NoticeModeSettings_id
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			$this->rollbackTransaction();
			return [['Error_Msg' => 'Не найдены настройки для удаления']];
		}

		try {
			foreach ($result->result('array') as $item) {
				$response = $this->deleteNoticeModeLink($item['NoticeModeLink_id']);
			}
		} catch (Exception $e) {
			return [['Error_Msg' => 'Ошибка при удалении режима уведомлений']];
		}

		if (isset($response) && !is_object($response)) {
			$this->rollbackTransaction();
			return [['Error_Msg' => 'Ошибка при удалении режима уведомлений']];
		}

		$response = $this->deleteSettings($data);
		if (!empty($response[0]['Error_Msg'])) {
			$this->rollbackTransaction();
		} else {
			$this->commitTransaction();
		}

		return $response;
	}

	/**
	 * Удаление режима уведомлений
	 * @param $data
	 * @return array
	 */
	function deleteNoticeModeLink($data) {
		$params['NoticeModeLink_id'] = $data;

		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_NoticeModeLink_del(
				noticemodelink_id := :NoticeModeLink_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при удалении наименования группы диагнозов'));
		}
		return $result;
	}

	/**
	 * Удаление настроек
	 * @param $data
	 * @return array
	 */
	function deleteSettings($data) {
		$params = array('NoticeModeSettings_id' => $data['NoticeModeSettings_id']);

		$query = "
			select 
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_NoticeModeSettings_del(
				noticemodesettings_id := :NoticeModeSettings_id
			)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array(array('Error_Msg' => 'Ошибка при удалении наименования группы диагнозов'));
		}
		return $result->result('array');
	}
}