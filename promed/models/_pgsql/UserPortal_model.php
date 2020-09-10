<?php
/**
 * UserPortal_model - модель для работы c БД аккаунтов регионального портала медуслуг
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (ethereallich@gmail.com)
 * @version      25.09.2013
 *
 * @property CI_DB_driver $maindb 
 * @property CI_DB_driver $portaldb
 * @property CI_Config $config
 * @property SwLoader $load
 */

class UserPortal_model extends SwPgModel
{
	function __construct()
	{
		parent::__construct();
		$defaultdb = $this->load->database("default", true);
		$this->maindb = $defaultdb->database;
		$this->portaldb = $this->load->database("UserPortal", true);
	}

	/**
	 * Создание нового СМС
	 * 
	 * @param $data
	 * @return bool
	 */
	function createSMS($data)
	{
		$query = "
			select
				SendSMS_id as \"sms_id\"
			from p_SendSMS_create (
				User_id := :User_id,
				SendSMS_Phone := :UserNotify_Phone,
				SendSMS_Text := :text,
				SendSMS_sendDT := null,
				SendSms_Status := null
			)
		";
		$result = $this->portaldb->query($query, array(
			'UserNotify_Phone' => $data['UserNotify_Phone'],
			'User_id' => $data['User_id'],
			'text' => $data['text']
		));
		if ( is_object($result) ) {
			$result = $result->result('array');
			if( count($result) > 0 && isset($result[0]['sms_id']) ) {
				return $result[0]['sms_id'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Устанавливает состояние отправки для SMS-сообщения
	 * 
	 * @param $sms_id
	 * @param $status
	 * @return mixed
	 */
	public function setSMSStatus($sms_id, $status)
	{
		$sql = "
			select
			from p_SendSMS_send (
				SendSMS_id := :sms_id,
				SendSMS_sendDT := dbo.tzGetdate(),
				SendSMS_Status := :status
			)
		";
		$result = $this->portaldb->query($sql, 
			array(
				'sms_id' => $sms_id,
				'status' => (int) $status
			)
		);

        return $result;
	}

	/**
	 * Получение настроек уведомлений пользователя
	 * 
	 * @param $user_id
	 * @return bool
	 */
	function getUserNotificationSettings($user_id)
	{
		$query = "
			select
				Users.id as \"User_id\",
				Users.first_name as \"FirstName\",
				Users.second_name as \"MidName\",
				UserNotify_Phone as \"UserNotify_Phone\",
				UserNotify_AcceptIsEmail as \"homevisit_email\",
				case when UserNotify_PhoneActStatus = 2 then UserNotify_AcceptIsSMS end as \"homevisit_sms\", -- настройки СМС только для активированных записей
				Users.email as \"EMail\"
			from
				UserNotify
				inner join Users on Users.id = UserNotify.User_id
			where Users.id = :User_id
			limit 1
		";
		$queryParams = [
			"User_id" => $user_id
		];
		$result = $this->portaldb->query($query, $queryParams);
		$result = $result->result("array");
		if (count($result) > 0) {
			return $result[0];
		} else {
			return false;
		}
	}

	/**
	 * Получение настроек уведомлений для пациента промеда
	 * 
	 * @param $person_mainid
	 * @return bool|mixed
	 */
	function getPromedPersonNotificationSettings($person_mainid)
	{
		$params = ["person_mainid" => $person_mainid];
		$query = "
			select
				U.id as user_id,
				U.email,
				UN.UserNotify_Phone as phone,
				UserNotify_AttachIsEmail as attach_email,
				case when UserNotify_PhoneActStatus = 2 then UN.UserNotify_AttachIsSMS end as attach_sms,
				UserNotify_NotifyIsEmail as homevisit_email,
				case when UserNotify_PhoneActStatus = 2 then UserNotify_NotifyIsSMS end as homevisit_sms -- настройки СМС только для активированных записей
			from
				Users U
				inner join Person P on U.id = P.pmUser_id and U.main_person = p.Person_mainId
				inner join UserNotify UN on UN.User_id = U.id
			where
				U.main_person = :person_mainid
		";
		$result = $this->portaldb->query($query, $params);
		$result = $result->result("array");
		if (count($result) > 0) {
			return $result;
		} else {
			return false;
		}
	}

	/**
	 * При создании записи на бирку создает запись в VizitNotify,
	 * если по person_id можно получить настройки СМС-оповещения с помощью UserPortal_model::getPromedPersonNotificationSettings
	 * и в настройках есть признак информирования по СМС и есть подтвержденный телефон.
	 *
	 * @param $person_id
	 * @param $TimetableGraf_id
	 * @param DateTime|NULL $TimetableGraf_begTime
	 * @return bool|mixed
	 *
	 * @throws Exception
	 */
	function notifyAboutRecordCancel($person_id, $TimetableGraf_id, DateTime $TimetableGraf_begTime = NULL)
	{
		if (false === empty($this->config->config["USER_PORTAL_IS_ALLOW_NOTIFY_ABOUT_RECORD_CANCEL"])) {
			$response = $this->getPromedPersonNotificationSettings($person_id);
			if (!is_array($response) || 0 === count($response) || empty($response[0]["user_id"])) {
				// person_id не является пользователем портала
				return true;
			}
			if ($TimetableGraf_begTime === null) {
				// Запись пользователя портала была отменена из промеда, надо удалить напоминание о приеме
				$params = [
					"TimetableGraf_id" => $TimetableGraf_id,
					"pmUser_id" => $response[0]["user_id"], // пользователь портала, запись которого отменили
				];
				$query = "
					delete from VizitNotify
					where TimetableGraf_id = :TimetableGraf_id
					  and pmUser_id = :pmUser_id
				";
				return $this->portaldb->query($query, $params);
			}
			if ($TimetableGraf_begTime && ($response[0]["homevisit_sms"] == 1)) {
				// Пользователь портала был записан из промеда и в настройках есть признак информирования по СМС и есть подтвержденный телефон
				// Создаем новое напоминание аналогично тому, как при записи на прием к врачу на портале см. addVizitNotify
				// Удалить напоминание о приеме см. deleteVizitNotify
				$params = [
					"TimetableGraf_id" => $TimetableGraf_id,
					"pmUser_id" => $response[0]["user_id"], // пользователь портала, запись которого отменили
				];
				$query = "
					delete from VizitNotify
					where TimetableGraf_id = :TimetableGraf_id
					  and pmUser_id = :pmUser_id
				";
				$this->portaldb->query($query, $params);
				$params = [
					"TimetableGraf_id" => $TimetableGraf_id,
					"Person_id" => $person_id,
					"NotifyTime" => $TimetableGraf_begTime->sub(new DateInterval("PT24H"))->format("Y-m-d H:i:s"), // напоминание должно прийти за день (24 часа) до посещения
					"pmUser_id" => $response[0]["user_id"], // пользователь портала, которого записали
					"VizitNotify_Email" => empty($response[0]["homevisit_email"]) ? NULL : 1,
					"VizitNotify_SMS" => empty($response[0]["homevisit_sms"]) ? NULL : 1,
				];
				$query = "
					insert into VizitNotify(TimetableGraf_id, Person_id, NotifyTime, pmUser_id, VizitNotify_Email, VizitNotify_SMS, VizitNotify_insDT)
					values
					(
						:TimetableGraf_id,
						:Person_id,
						:NotifyTime,
						:pmUser_id,
						:VizitNotify_Email,
						:VizitNotify_SMS,
						dbo.tzgetdate()
					)
				";
				return $this->portaldb->query($query, $params);
			}
		}
		return true;
	}

	/**
	 *  поиск FCM-токена в базе портала
	 * 
	 * @param $data
	 * @return mixed
	 */
	function getPushNotificationTokens($data)
	{
		// если указан pmUser_id > 1000000 и < 5000000, значит это челы с портала
		if ((!empty($data['pmUser_did']) && ($data['pmUser_did'] > 1000000 && $data['pmUser_did'] < 5000000 ))
			|| (!empty($data['pmUser_id']) && ($data['pmUser_id'] > 1000000 && $data['pmUser_id'] < 5000000 ))) {
			$query = "
				select 
				    u.id as \"pmUser_did\",
				   	u.email,
				   	u.first_name,
					u.second_name,
					u.FCM_Token
				from users u
				where u.id = :pmUser_id
				limit 1
			";
			$params['pmUser_id'] = !empty($data['pmUser_did']) ? $data['pmUser_did'] : $data['pmUser_id'];
		} else {
			// иначе попытаемся найти Person_id в чей-нибудь картотеке
			$filter = "";
			// если это не взрослый берем всех у кого он в картотеке находится
			if (!empty($data["Person_Age"]) && $data["Person_Age"] < 18) {
				$filter = "";
			}
			if (empty($data["showEmptyFCM"])) {
				$filter .= " and u.FCM_Token is not null ";
			}
			$query = "
				select distinct -- бывает что у двух пользователей портала один FCM_Token
					u.id as \"pmUser_did\",
					u.email,
					u.first_name,
					u.second_name,
					u.FCM_Token
				from
					Person p
					inner join users u on u.id = p.pmUser_id
				where
				    p.Person_mainId = :Person_id
					{$filter}
			";
			$params["Person_id"] = $data["Person_id"];
		}
		$result = $this->portaldb->query($query, $params);
		$result = $result->result("array");
		return $result;
	}

	/**
	 * Сохранение истории push в БД портала
	 * 
	 * @param $data
	 * @return mixed
	 */
	function savePushNotificationHistory($data)
	{
		$params = [
			"PushNoticeHistory_MessageText" => $data["message"],
			"pmUser_did" => $data["pmUser_did"],
			"PushNoticeType_id" => $data["PushNoticeType_id"],
			"pmUser_insID" => 1,
			"pmUser_updID" => 1
		];

		$query = "
			INSERT INTO
				dbo.PushNoticeHistory(PushNoticeHistory_MessageText, pmUser_did, PushNoticeType_id, pmUser_insID, pmUser_updID, PushNoticeHistory_insDT, PushNoticeHistory_updDT)
			VALUES (
				:PushNoticeHistory_MessageText,
				:pmUser_did,
				:PushNoticeType_id,
				:pmUser_insID,
				:pmUser_updID,
				dbo.tzgetdate(),
				dbo.tzgetdate()
			)
		";
		$result = $this->portaldb->query($query, $params);
		return $result;
	}

	/**
	 * запуск CURL-запроса
	 *
	 * @param $data
	 * @return bool
	 */
	function executeCurlRequest($data)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2); // подключение, сек
		curl_setopt($ch, CURLOPT_TIMEOUT, 2); // выполнение, сек
		curl_setopt($ch, CURLOPT_URL, $data->url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $data->headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data->curlRequestData));
		$result = curl_exec($ch);
		curl_close($ch);
		return !empty($result);
	}
}