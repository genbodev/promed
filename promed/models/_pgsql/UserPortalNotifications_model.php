<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Class UserPortalNotifications_model - модель для работы с оповещениями пользователей портала
 *
 * @property CI_DB_driver $db
 * @property CI_DB_driver $portaldb
 * @property UserPortal_model $UserPortal_model
 */
class UserPortalNotifications_model extends swPgModel
{
	function __construct($params = NULL)
	{
		parent::__construct();
		$defaultdb = $this->load->database("default", true);
		$this->maindb = $defaultdb->database;
		$this->portaldb = $this->load->database("UserPortal", true);
		$this->load->model("UserPortal_model");
	}

	/**
	 * Хранилище сообщений
	 * @param $data
	 * @return array|mixed
	 */
	function getNotificationsMetadata($data)
	{
		$store = [
			"evnqueue" => [
				"confirmRecord" => [
					"sms" => [],
					"push" => [
						"text" => ":person_fio записан(а] на прием на :time"
					],
					"email" => [
						"text" =>
							"Вы записали пациента :person_fio к врачу :doctor_fio на :time."."\xA"
							."С Уважением, Администрация сайта К врачу.ру",
						"subject" => "Запись на прием"
					],
					"notice" => []
				],
				"removeFromEvnQueue" => [
					"sms" => [],
					"push" => [
						"text" => ":person_fio удален(а] из очереди к врачу по причине: :cause"
					],
					"email" => [
						"text" =>
							":person_fio исключен из очереди к врачу по причине: :cause."."\xA"
							."С Уважением, Администрация сайта К врачу.ру",
						"subject" => "Отмена визита"
					],
					"notice" => []
				]
			],
			"attachment" => [
				"approved" => [
					"sms" => [],
					"push" => [
						"text" => ":Person_FullName прикреплен(а] к медицинской организации :Lpu_Nick."
					],
					"email" => [
						"text" => "Ваше заявление о смене прикрепления для :Person_FullName от :time рассмотрено." . "\xA Вы прикреплены к медицинской организации :Lpu_Nick." . "\xA Адрес медицинской организации: :LpuUnitAddress_Name.",
						"subject" => "Прикрепление к медицинской организации",
						"additionalText" => []
					],
					"notice" => []
				],
				"cancel" => [
					"sms" => [],
					"push" => [
						"text" => "Для :Person_FullName было отказано в прикреплении к медицинской организации."
					],
					"email" => [
						"text" => "Ваше заявление о смене прикрепления для :Person_FullName от :time рассмотрено." . "\xA Медицинская организация :Lpu_Nick отказала в прикреплении." . "\xA Причина: :CancelReason." . "\xA Пожалуйста, обратитесь в медицинскую организацию для смены прикрепления.",
						"subject" => "Отказ в прикреплении к медицинской организации",
						"additionalText" => []
					],
					"notice" => []
				]
			],
			"disp" => [
				"upcoming_visit" => [
					"sms" => [],
					"push" => [
						"text" => "Пациенту :Person_FullName необходимо пройти плановый осмотр у врача :profile :doctor_fio Прием ведется по записи."
					],
					"email" => [
						"text" => "пациенту :Person_FullName необходимо пройти плановый осмотр у врача :profile :doctor_fullfio." . "\xA Запишитесь на прием самостоятельно или обратитесь к участковому терапевту для записи.",
						"subject" => "Диспансерное наблюдение",
						"additionalText" => []
					],
					"notice" => []
				]
			],
			"eq" => [
				"changeOffice" => [
					"sms" => [],
					"push" => [
						"text" =>
							":Person_FullName прием врача :doctor_fin перенесен в кабинет :office_num, :Lpu_Nick"
					],
					"email" => [
						"text" =>
							"Вы записали пациента :Person_FullName к врачу :profile :doctor_fio на :time."."\xA"
							."Сообщаем вам, что прием врача перенесен в кабинет :office_num, :Lpu_Nick",
						"subject" => "Изменение места приема врача"
					],
					"notice" => []
				]
			]
		];
		$result = [];
		if (isset($store[$data["notify_object"]])) {
			$section = $store[$data["notify_object"]];
			if (isset($section[$data["notify_action"]])) {
				$result = $section[$data["notify_action"]];
			}
		}
		return $result;
	}

	/**
	 * Описание
	 * @param $user_id
	 * @return mixed|bool
	 */
	function checkIsSubscribed($user_id)
	{
		$query = "
			select coalesce(UserNotify_InformIsPortal, 0) as \"isSubscribed\"
			from UserNotify
			where User_id = :user_id
			limit 1
		";
		$params["user_id"] = $user_id;
		/**@var CI_DB_result $result */
		$result = $this->portaldb->query($query, $params);
		$result = $result->result_array();
		return !empty($result[0]["isSubscribed"]);
	}

	/**
	 * Парсер
	 * @param string $text
	 * @param array $params
	 * @param null $additionalText
	 * @return mixed|string
	 */
	function assign($text = "", $params = [], $additionalText = null)
	{
		if (!empty($params) && !empty($text)) {
			foreach ($params as $field => $value) {
				if (isset($additionalText[$field]) && !empty($value)) {
					$string = str_replace(":" . $field, $value, $additionalText[$field]);
					$text = str_replace(":" . $field, $string, $text);
					// убираем из параметров
					unset($additionalText[$field]);
				} else {
					$text = str_replace(":" . $field, $value, $text);
				}
			}
			// если в доп. тексте остались ключи, значит они не заменились, и надо их убрать из текста
			if (!empty($additionalText)) {
				foreach ($additionalText as $field => $value) {
					$text = str_replace(":" . $field, '', $text);
				}
			}
		}
		return $text;
	}

	/**
	 * @param $data
	 */
	function send($data)
	{
		$notification_data = $this->getNotificationData($data);
		if (!empty($notification_data)) {
			$this->sendNotifications($notification_data);
		}
	}

	/**
	 * @param $data
	 * @return array
	 */
	function getNotificationData($data)
	{
		$result = [];
		$person_data = $this->getPersonData($data);
		if (!empty($person_data)) {
			$notifications = $this->getNotificationsMetadata($data);
			$assign_params = ["Person_FullName" => $person_data["Person_FullName"]];
			$assign_params = array_merge($data, $assign_params);
			if (!empty($notifications)) {
				$result = [
					"assign_params" => $assign_params,
					"notifications" => $notifications,
					"Person_id" => $data["Person_id"]
				];
			}
		}
		return $result;
	}

	/**
	 * @param $data
	 */
	function sendNotifications($data)
	{
		$notifications = $data["notifications"];
		$assign_params = $data["assign_params"];
		foreach ($notifications as &$notify_type) {
			if (isset($notify_type["text"])) {
				$additionalText = !empty($notify_type["additionalText"]) ? $notify_type["additionalText"] : null;
				$notify_type["text"] = $this->assign($notify_type["text"], $assign_params, $additionalText);
			}
		}
		$email_message = !empty($notifications["email"]["text"]) ? $notifications["email"]["text"] : "";
		$push_message = !empty($notifications["push"]["text"]) ? $notifications["push"]["text"] : "";
		$portal_message = "";
		if (!empty($notifications["notice"]["text"])) {
			$portal_message = $notifications["notice"]["text"];
		} else {
			if (!empty($notifications["push"]["text"])) {
				$portal_message = $notifications["push"]["text"];
			}
		}
		$funcParams = [
			"pmUser_did" => !empty($data["pmUser_insID"]) ? $data["pmUser_insID"] : null,
			"Person_id" => $data["Person_id"],
			"showEmptyFCM" => true
		];
		$portal_accounts = $this->UserPortal_model->getPushNotificationTokens($funcParams);
		$this->load->helper("Notify");
		foreach ($portal_accounts as $account) {
			if ($email_message) {
				$canEmail = $this->checkIsSubscribed($account["pmUser_did"]);
				if ($canEmail) {
					$funcParams = [
						"first_name" => $account["first_name"],
						"second_name" => $account["second_name"],
						"email" => $account["email"],
						"subject" => !empty($notifications["email"]["subject"]) ? $notifications["email"]["subject"] : "Без темы",
						"text" => $email_message
					];
					$this->sendEmailMessage($funcParams);
				}
			}
			if ($push_message && !empty($account["FCM_Token"])) {
				$funcParams = [
					"text" => $push_message,
					"Person_id" => $data["Person_id"],
					"FCM_Token" => $account["FCM_Token"]
				];
				$this->sendPushMessage($funcParams);
			}
			if ($portal_message) {
				$funcParams = [
					"text" => $portal_message,
					"pmUser_did" => $account["pmUser_did"]
				];
				$this->sendPortalMessage($funcParams);
			}
		}
	}

	/**
	 * @param $data
	 */
	function sendPushMessage($data)
	{
		$params = [
			"Person_id" => $data["Person_id"],
			"message" => $data["text"],
			"PushNoticeType_id" => !empty($data["PushNoticeType_id"]) ? $data["PushNoticeType_id"] : 2,
			"action" => "call",
			"FCM_Token" => $data["FCM_Token"]
		];
		sendPushNotify($params);
	}

	/**
	 * @param $data
	 */
	function sendEmailMessage($data)
	{
		$text = "Уважаемый(-ая) {$data["first_name"]} {$data["second_name"]}, \xA{$data["text"]}\xA С уважением, администрация регионального портала медицинских услуг.";
		$params = [
			"EMail" => $data["email"],
			"title" => $data["subject"],
			"body" => $text
		];
		sendNotifyEmail($params);
	}

	/**
	 * @param $data
	 */
	function sendPortalMessage($data)
	{
		$params = [
			"pmUser_did" => $data["pmUser_did"],
			"message" => $data["text"],
			"PushNoticeType_id" => !empty($data["PushNoticeType_id"]) ? $data["PushNoticeType_id"] : 2
		];
		// сохраняем в оповещения портала
		$this->UserPortal_model->savePushNotificationHistory($params);
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function getPersonData($data)
	{
		$params = ["Person_id" => $data["Person_id"]];
		$query = "
			select
				rtrim(Person_Surname)||' '||substring(rtrim(Person_Firname), 1, 1)||'.'||coalesce(substring(rtrim(Person_Secname), 1, 1)||'.', '') as \"Person_FullName\"
			from v_PersonState
			where Person_id = :Person_id
			limit 1
		";
		return $this->getFirstRowFromQuery($query, $params);
	}
}