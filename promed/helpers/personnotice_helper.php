<?php

class PersonNoticeEvn {
	private $CI = null;
	private $Person_id = null;
	private $Person = array();
	private $users_base = array();
	private $users = array();
	private $status_list = array();
	private $allowed_evn_list = array();

	private $EvnClass_SysNick = null;
	private $Evn_id = null;
	private $isPolka = false; //уведомления для врачей поликлиники

	private $snapshot1 = null;
	private $snapshot2 = null;

	private $isPersonNoticeAllowed = false;

	/**
	 * Конструктор хелпера
	 * @param $Person_id
	 * @param null $EvnClass_SysNick
	 * @param null $Evn_id
	 */
	function __construct($Person_id, $EvnClass_SysNick = null, $Evn_id = null, $isPolka = false) {
		$this->CI = & get_instance();
		$this->CI->load->model('PersonNotice_model', 'pnmodel');
		$this->CI->load->model('Evn_model', 'evnmodel');
		$this->CI->load->model('Messages_model', 'Messages_model');
		$this->CI->load->library('email');
		$this->CI->load->helper('Notify');
		$this->CI->load->helper('Options');
		
		$this->Person_id = $Person_id;
		$this->EvnClass_SysNick = $EvnClass_SysNick;
		$this->Evn_id = $Evn_id;
		$this->isPolka = $isPolka;

		if (!empty($this->Person_id)) {
			if(!$isPolka) {
				$this->users_base = $this->CI->pnmodel->getUsersForPersonNotice(array(
					'Person_id' => $this->Person_id
				));
			} else {			
				$this->users_base = $this->CI->pnmodel->getUsersForPersonNoticePolka(array(
					'Person_id' => $this->Person_id
				));
			}
			
			$this->users = $this->filterUsersByEvnClass($this->users_base);
		}

		$status_settings = getEvnNoticeOptions();
		$this->full_status_list = $status_settings['full_evn_status_list'];

		if(!$isPolka) {
			$allowed_evn_list = $this->CI->evnmodel->getAllowedEvnClassListForNotice();
			
			foreach ($allowed_evn_list as $evn) {
				$this->allowed_evn_list[$evn['EvnClass_SysNick']] = $evn['EvnClass_Name'];
			}
		} else {
			$allowed_evn_list = array('EvnUslugaTelemed','EvnUslugaParPolka');		
			foreach ($allowed_evn_list as $evn) {
				$this->allowed_evn_list[$evn] = true;
			}
		}

		$this->refreshAllow();
	}

	/**
	 * Изменить идентификатор события
	 * @param $Evn_id
	 */
	public function setEvnId($Evn_id) {
		$this->Evn_id = $Evn_id;
		$this->refreshAllow();
	}

	/**
	 * Изменить отслеживаемое событие событие
	 * @param $EvnClass_SysNick
	 */
	public function setEvnClassSysNick($EvnClass_SysNick) {
		$this->EvnClass_SysNick = $EvnClass_SysNick;
		$this->Evn_id = null;
		$this->users = $this->filterUsersByEvnClass($this->users_base);
		$this->refreshAllow();
	}

	/**
	 * Обновление allow
	 */
	private function refreshAllow() {
		if (!empty($this->Person_id) && !empty($this->EvnClass_SysNick) && isset($this->allowed_evn_list[$this->EvnClass_SysNick])) {
			$this->isPersonNoticeAllowed = true;
		} else {
			$this->isPersonNoticeAllowed = false;
		}
	}

	/**
	 * Фильтрация пользователей по настройкам оповещений о событиях
	 * @param $users_base
	 * @return array
	 */
	private function filterUsersByEvnClass($users_base) {
		$users = array();
		if (empty($this->EvnClass_SysNick)) {return $users;}
		foreach($users_base as $user) {
			$is_enable_evn = (array_key_exists($this->EvnClass_SysNick, $user['notice_settings']) ? $user['notice_settings'][$this->EvnClass_SysNick] : false);
			if ($is_enable_evn) {
				$users[] = $user;
			}
		}
		return $users;
	}

	/**
	 * Загрузка информации о человеке (для формирования текста сообщения)
	 * @param null $PersonEvn_id
	 * @param null $Server_id
	 * @return bool
	 */
	public function loadPersonInfo($PersonEvn_id = null, $Server_id = null) {
		if (empty($this->Person_id)) {return false;}
		$this->CI->load->model('Common_model', 'Common_model');
		$Person = $this->CI->Common_model->loadPersonData(array(
			'Person_id' => $this->Person_id,
			'PersonEvn_id' => $PersonEvn_id,
			'Server_id' => $Server_id
		));

		if ( is_array($Person) && count($Person) > 0 ) {
			$Person[0]['Person_Fio'] = $Person[0]['Person_Surname'].' '.$Person[0]['Person_Firname'].' '.$Person[0]['Person_Secname'];
		}
		else {
			$Person[0]['Person_Fio'] = '';
		}

		$this->Person = $Person[0];

		return true;
	}

	/**
	 * Сохранение статусов до изменения события
	 * @return array|bool
	 */
	public function doStatusSnapshotFirst() {
		return $this->doSnapshot($this->snapshot1);
	}

	/**
	 * Сохранение статусов после изменения события
	 * @return array|bool
	 */
	public function doStatusSnapshotSecond() {
		return $this->doSnapshot($this->snapshot2);
	}

	/**
	 * Сохранение статусов события
	 * @param $snapshot
	 * @return array|bool
	 */
	private function doSnapshot(&$snapshot) {
		if (!$this->isPersonNoticeAllowed || count($this->users) == 0) {return false;}

		if (empty($this->Evn_id)) {
			$snapshot = array();
		} else {
			$response = $this->CI->evnmodel->getEvnStatusValues(array(
				'Evn_id' => $this->Evn_id,
				'EvnClass_SysNick' => $this->EvnClass_SysNick
			));
			$snapshot = $response[0];
		}

		return $snapshot;
	}

	/**
	 * Обработка изменений статусов события
	 * @return bool
	 */
	public function processStatusChange($ignoreException = false) {
		if (!$this->isPersonNoticeAllowed || count($this->users) == 0 || empty($this->Evn_id)) {return false;}

		$EvnClass_SysNick = $this->EvnClass_SysNick;
		$Evn_id = $this->Evn_id;
		$Person_id = $this->Person_id;
		$Person_Fio = $this->Person['Person_Fio'];
		$Person_Birthday = $this->Person['Person_Birthday'];
		
		$subject = ''; $message='';
		if(!$this->isPolka) {
			foreach($this->snapshot2 as $key => $value2) {
				if (!empty($this->snapshot1)) {
					$value1 = $this->snapshot1[$key];
				} else {
					$value1 = '';
				}

				if ($value1 != $value2) {
					$EvnClass_Name = $this->allowed_evn_list[$EvnClass_SysNick];
					$EvnStatus_Name = $this->full_status_list[$EvnClass_SysNick][$key];

					$subject = "Изменен статус события у пациента";
					$message = "Пациент {$Person_Fio} {$Person_Birthday} г. {$EvnClass_Name}. {$EvnStatus_Name} {$value2}.";

					$this->sendNoticeForUsers($subject, $message, $ignoreException);
				}
			}
		} else {
			switch($EvnClass_SysNick) {
				case 'EvnUslugaTelemed': 
					$subject = "Телемедицинская услуга выполнена";
					$message = "Пациент {$Person_Fio}<br>
						<a href='javascript:openNoticeResult(\"{$EvnClass_SysNick}\",{$Evn_id},{$Person_id});'>Результат </a>";
					break;
				case 'EvnUslugaParPolka':
					$subject = "Параклиническая услуга выполнена";
					$message = "Пациент {$Person_Fio} <br>
						<a href='javascript:openNoticeResult(\"EvnUslugaParPolka\",{$Evn_id},{$Person_id});'>Результат </a>";
					break;
			}
			if($subject!='' and $message!='') $this->sendNoticeForUsers($subject, $message, $ignoreException);
		}
		return true;
	}

	/**
	 * Рассылка оповещений пользователям
	 * @param $subject
	 * @param $message
	 */
	private function sendNoticeForUsers($subject, $message, $ignoreException = false) {
		foreach($this->users as $user) {
			if ($user['pmUser_IsMessage'] == 1) {
				$this->sendMessage($user['pmUser_id'], $subject, $message);
			}
			if ($user['pmUser_IsEmail'] == 1) {
				$email = $user['pmUser_Email'];
				$this->sendEmail($email, $subject, $message);
			}
			if ($user['pmUser_IsSMS'] == 1) {
				$phone = $user['pmUser_Phone'];
				//$ignoreException = true - отключаем возможность выбросить исключение при ошибке отправки смс
				$this->sendSms($phone, $message, $ignoreException);
			}
		}
	}

	/**
	 * Рассылка оповещений по email
	 * @param $email
	 * @param $subject
	 * @param $message
	 * @return bool
	 */
	private function sendEmail($email, $subject, $message) {
		$this->CI->email->sendPromed($email, $subject, $message);
		return true;
	}

	/**
	 * Рассылка оповещений по СМС
	 * @param $phone
	 * @param $message
	 * @return bool
	 */
	private function sendSms($phone, $message, $ignoreException = false) {
		try {
			sendPmUserNotifySMS(array(
				'pmUser_Phone' => $phone,
				'text' => $message,
				'sms_id' => $this->Evn_id
			), $ignoreException);
		} catch(Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Рассылка оповещений в системе сообщений промеда
	 * @param $pmUser_rid
	 * @param $subject
	 * @param $message
	 * @return mixed
	 */
	private function sendMessage($pmUser_rid, $subject, $message) {
		$noticeResponse = $this->CI->Messages_model->autoMessage(array(
			'pmUser_id' => $_SESSION['pmuser_id'],
			'User_rid' => $pmUser_rid,
			'autotype' => 5,
			'Evn_id' => $this->Evn_id,
			'title' => $subject,
			'text' => $message
		));

		return $noticeResponse;
	}
}