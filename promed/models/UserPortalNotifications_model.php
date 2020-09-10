<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * Class UserPortalNotifications_model - модель для работы с оповещениями пользователей портала
 */
class UserPortalNotifications_model extends swModel {

	/**
	 * Конструктор
	 */
	function __construct($params = NULL)
	{
		parent::__construct();

		$defaultdb = $this->load->database('default', true);
		$this->maindb = $defaultdb->database;
		$this->portaldb = $this->load->database('UserPortal', true);

		$this->load->model('UserPortal_model');
	}

	/**
	 * Хранилище сообщений
	 * todo: перенести куда-то в другое место
	 */
	function getNotificationsMetadata($data){

		$store = array(
			// todo: отрефакторить для листов ожидания (убрать рассылку из модели Queue_model)
			'evnqueue' => array(
				'confirmRecord' => array(
					'sms' => array(),
					'push' => array(
						'text' =>
							":person_fio записан(а) на прием на :time"
					),
					'email' => array(
						'text' =>
							"Вы записали пациента :person_fio к врачу :doctor_fio на :time."."\xA",
						'subject' => "Запись на прием"
					),
					'notice' => array()
				),
				'removeFromEvnQueue' => array(
					'sms' => array(),
					'push' => array(
						'text' =>
							':person_fio удален(а) из очереди к врачу по причине: :cause'
					),
					'email' => array(
						'text' =>
							":person_fio исключен из очереди к врачу по причине: :cause."."\xA",
						'subject' => "Отмена визита"
					),
					'notice' => array()
				)
			),
			'attachment' => array(
				'approved' => array(
					'sms' => array(),
					'push' => array(
						'text' => ":Person_FullName прикреплен(а) к медицинской организации :Lpu_Nick."
					),
					'email' => array(
						'text' =>
							"Ваше заявление о смене прикрепления для :Person_FullName от :time рассмотрено."."\xA"
							."Вы прикреплены к медицинской организации :Lpu_Nick."."\xA"
							."Адрес медицинской организации: :LpuUnitAddress_Name.",
						'subject' => "Прикрепление к медицинской организации",
						// опциональный текст, добавляется по вхождению параметров
						'additionalText' => array()
					),
					'notice' => array()
				),
				'cancel' => array(
					'sms' => array(),
					'push' => array(
						'text' => "Для :Person_FullName было отказано в прикреплении к медицинской организации."
					),
					'email' => array(
						'text' =>
							"Ваше заявление о смене прикрепления для :Person_FullName от :time рассмотрено."."\xA"
							."Медицинская организация :Lpu_Nick отказала в прикреплении."."\xA"
							."Причина: :CancelReason."."\xA"
							."Пожалуйста, обратитесь в медицинскую организацию для смены прикрепления.",
						'subject' => "Отказ в прикреплении к медицинской организации",
						// опциональный текст, добавляется по вхождению параметров
						'additionalText' => array()
					),
					'notice' => array()
				)
			),
			'disp' => array(
				'upcoming_visit' => array(
					'sms' => array(),
					'push' => array(
						'text' => "Пациенту :Person_FullName необходимо пройти плановый осмотр у врача :profile :doctor_fio Прием ведется по записи."
					),
					'email' => array(
						'text' =>
							"Пациенту :Person_FullName необходимо пройти плановый осмотр у врача ".
							":profile :doctor_fullfio."."\xA"
							."Запишитесь на прием самостоятельно или обратитесь к участковому терапевту для записи.",
						'subject' => "Диспансерное наблюдение",
						// опциональный текст, добавляется по вхождению параметров
						'additionalText' => array()
					),
					'notice' => array()
				)
			),
			'eq' => array(
				'changeOffice' => array(
					'sms' => array(),
					'push' => array(
						'text' =>
							":Person_FullName прием врача :doctor_fin перенесен в кабинет :office_num, :Lpu_Nick"
					),
					'email' => array(
						'text' =>
							"Вы записали пациента :Person_FullName к врачу :profile :doctor_fio на :time."."\xA"
							."Сообщаем вам, что прием врача перенесен в кабинет :office_num, :Lpu_Nick",
						'subject' => "Изменение места приема врача"
					),
					'notice' => array()
				)
			)
		);

		$result = array();

		if (isset($store[$data['notify_object']])) {
			$section = $store[$data['notify_object']];
			if (isset($section[$data['notify_action']])) {
				$result = $section[$data['notify_action']];
			}
		}

		return $result;
	}

	/**
	 * Описание
	 */
	function checkIsSubscribed($user_id) {

		$query = "
			select top 1
				isnull(UserNotify_InformIsPortal, 0) as isSubscribed
			from UserNotify (nolock)
			where User_id = :user_id
		";

		$params['user_id'] = $user_id;
		$result = $this->portaldb->query($query, $params)->result('array');

		return !empty($result[0]['isSubscribed']);
	}

	/**
	 * Парсер
	 */
	function assign($text = "", $params = array(), $additionalText = null) {

		if (!empty($params) && !empty($text)) {
			foreach ($params as $field => $value) {
				if (isset($additionalText[$field]) && !empty($value)) {
					$string = str_replace(":".$field, $value, $additionalText[$field]);
					$text = str_replace(":".$field, $string, $text);
					// убираем из параметров
					unset($additionalText[$field]);
				} else {
					$text = str_replace(":".$field, $value, $text);
				}
			}

			// если в доп. тексте остались ключи, значит они не заменились, и надо их убрать из текста
			if (!empty($additionalText)) {
				foreach ($additionalText as $field => $value) {
					$text = str_replace(":".$field, '', $text);
				}
			}
		}

		return $text;
	}

	/**
	 * Описание
	 */
	function send($data) {

		$notification_data = $this->getNotificationData($data);

		if (!empty($notification_data)) {
			$this->sendNotifications($notification_data);
		}
	}

	/**
	 * Описание
	 */
	function getNotificationData($data) {

		$result = array();
		$person_data = $this->getPersonData($data);

		if (!empty($person_data)) {

			$notifications = $this->getNotificationsMetadata($data);
			$assign_params = array(
				'Person_FullName' => $person_data['Person_FullName']
			);

			$assign_params = array_merge($data, $assign_params);

			if (!empty($notifications)) {

				$result = array(
					'assign_params' => $assign_params,
					'notifications' => $notifications,
					'Person_id' => $data['Person_id']
				);
			}
		}

		return $result;
	}

	/**
	 * Описание
	 */
	function sendNotifications($data) {

		$notifications = $data['notifications'];
		$assign_params = $data['assign_params'];

		foreach ($notifications as &$notify_type) {
			if (isset($notify_type['text'])) {
				$additionalText = !empty($notify_type['additionalText']) ? $notify_type['additionalText'] : null;
				$notify_type['text'] = $this->assign($notify_type['text'], $assign_params, $additionalText);
			}
		}

		$email_message = !empty($notifications['email']['text']) ? $notifications['email']['text'] : '';
		$push_message = !empty($notifications['push']['text']) ? $notifications['push']['text'] : '';

		$portal_message = '';
		if (!empty($notifications['notice']['text'])) {
			$portal_message = $notifications['notice']['text'];
		} else {
			if (!empty($notifications['push']['text'])) {
				$portal_message = $notifications['push']['text'];
			}
		}

		$portal_accounts = $this->UserPortal_model->getPushNotificationTokens(
			array(
				'pmUser_did' => !empty($data['pmUser_insID']) ? $data['pmUser_insID'] : null,
				'Person_id' => $data['Person_id'],
				'showEmptyFCM' => true
			)
		);

		$this->load->helper('Notify');

		// обычно один... но вдруг два или три
		foreach ($portal_accounts as $account) {

			if ($email_message) {

				$canEmail = $this->checkIsSubscribed($account['pmUser_did']);

				if ($canEmail) {
					$this->sendEmailMessage(
						array(
							'first_name' => $account['first_name'],
							'second_name' => $account['second_name'],
							'email' => $account['email'],
							'subject' => !empty($notifications['email']['subject']) ? $notifications['email']['subject'] : 'Без темы',
							'text' => $email_message
						)
					);
				}

			}

			if ($push_message && !empty($account['FCM_Token'])) {
				$this->sendPushMessage(
					array(
						'text' => $push_message,
						'Person_id' => $data['Person_id'],
						'FCM_Token' => $account['FCM_Token']
					)
				);
			}

			if ($portal_message) {
				$this->sendPortalMessage(
					array(
						'text' => $portal_message,
						'pmUser_did' => $account['pmUser_did']
					)
				);
			}
		}
	}

	/**
	 * Описание
	 */
	function sendPushMessage($data) {

		$params = array(
			'Person_id' => $data['Person_id'], // персона которая заходит
			'message' => $data['text'],
			'PushNoticeType_id' => !empty($data['PushNoticeType_id']) ? $data['PushNoticeType_id'] : 2,
			'action' => 'call',
			'FCM_Token' => $data['FCM_Token']
		);

		sendPushNotify($params);
	}

	/**
	 * Описание
	 */
	function sendEmailMessage($data) {

		$text = "Уважаемый(ая) ".$data['first_name']." ".$data['second_name'].".\xA"
			.$data['text']
			."\xA"."С уважением, администрация регионального портала медицинских услуг.";

		$params = array(
			'EMail' => $data['email'],
			'title' => $data['subject'],
			'body' =>  $text
		);

		sendNotifyEmail($params);
	}

	/**
	 * Описание
	 */
	function sendPortalMessage($data) {

		$params = array(
			'pmUser_did' => $data['pmUser_did'],
			'message' => $data['text'],
			'PushNoticeType_id' => !empty($data['PushNoticeType_id']) ? $data['PushNoticeType_id'] : 2
		);

		// сохраняем в оповещения портала
		$this->UserPortal_model->savePushNotificationHistory($params);
	}

	/**
	 * Описание
	 */
	function getPersonData($data) {

		$params = array(
			'Person_id' => $data['Person_id']
		);

		$personData = $this->getFirstRowFromQuery("
			select top 1
				rtrim(Person_Surname)+' '+SUBSTRING(rtrim(Person_Firname), 1, 1)+'.'+isnull(SUBSTRING(rtrim(Person_Secname), 1, 1)+'.', '') as Person_FullName
				from v_PersonState (nolock)
			where Person_id = :Person_id		 
		", $params);

		return $personData;
	}
}