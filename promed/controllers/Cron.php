<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Cron - контроллер для выполнения задач по расписанию 
* Вызывается из командной строки: php cron.php  --run=/Cron/<method>
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Alexander Chebukin
* @version      15.06.2016
*/


class Cron extends swController {
	
	var $NeedCheckLogin = false;

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();
		$user = isset($_SESSION['login']) ? pmAuthUser::find($_SESSION['login']) : false; // Оставим возможность ручного запуска для суперадмина
		if(!defined('CRON') && !($user && $user->havingGroup('SuperAdmin'))) die('Запуск только через cron');
		$this->load->database();
	}
	
	/**
	* Рассылка сообщений
	*/
	function NewslatterSend() {
	
		$this->load->model('Newslatter_model', 'dbmodel');
		$this->load->helper('Notify');
		$this->load->library('email');

		$response = $this->dbmodel->send();
		if ($response === false) {
			throw new Exception('Ошибка получения данных для рассылки');
		}

		foreach ($response as $ndata) {

			// Отправляем СМС
			if ($ndata['Newslatter_IsSMS'] == 2 && $ndata['NewslatterAccept_IsSMS'] == 2) {
				$ndata['NewslatterAccept_Phone'] = substr($ndata['NewslatterAccept_Phone'], 2, 10);
				$data = array(
					'sms_id' => 'nl_'.$ndata['Newslatter_id'].'_'.$ndata['NewslatterAccept_Phone'],
					'pmUser_Phone' => $ndata['NewslatterAccept_Phone'],
					'text' => $ndata['Newslatter_Text']
				);
				try {
					sendPmUserNotifySMS($data);
				} catch (Exception $e) {
					throw new Exception("Не удалось выполнить отправление СМС", 20);
				}
			}

			// Отправляем Email
			if ($ndata['Newslatter_IsEmail'] == 2 && $ndata['NewslatterAccept_IsEmail'] == 2) {
				@$resultsend = $this->email->sendPromed($ndata['NewslatterAccept_Email'], 'Уведомление', $ndata['Newslatter_Text']);
				if (!$resultsend) {
					throw new Exception("Не удалось выполнить отправление письма", 20);
				}
			}

		}

		echo "Рассылка выполнена";
		return true;
		
	}

	/**
	 * Метод рассылки уведомлений по заданным параметрам пациентам записанных в стационар. Работает по крону.
	 * @return bool
	 * @throws Exception
	 */
	function notifyPatients() {
		$this->load->database();
		$this->load->model("NoticeModeSettings_model", "dbmodel");
		
		$response = $this->dbmodel->getDataForSendNotify();
		
		foreach ($response as $item) {
			$notifyText = $this->dbmodel->generateTextForNotify($item);
			$this->dbmodel->sendNotify($item, $notifyText);
			try {
				$this->dbmodel->saveNoticeHistory(['Person_id' => $item['Person_id'], 'NoticeModeLink_id' => $item['NoticeModeLink_id']]);
			} catch (Exception $e) {
				throw new Exception($e->getMessage());
			}
		}
		
		echo "Рассылка выполнена";
		return true;
	}

	/**
	* Синхронизация данных кэша с данными из лдапа
	*/
	function syncLdapAndCacheUserData() {
		ini_set('max_execution_time', 0);
		
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		
		$cache_users = $this->dbmodel->getUsersList(array(
			'org' => null,
			'withoutPaging' => true
		));
		
		$ldap_users = new pmAuthUsers('*');
		
		foreach($ldap_users->users as $lu) {
			// Обновляем данные по пользователю в кэше
			$this->dbmodel->ReCacheUserData($lu);
			foreach($cache_users as $key => $cu) {
				if ($cu['pmUser_id'] == $lu->pmuser_id) {
					unset($cache_users[$key]);
				}
			}
		}
		
		// все что осталось в $cache_users это удаленные(из лдапа) пользователи
		foreach($cache_users as $cu) {
			$this->dbmodel->deleteUserOfCache($cu, false);
		}
		
		return true;
	}

	/**
	 * Запуск сервиса получения идентификаторов
	 */
	public function execGeneratorService() {
		$this->load->model("Generator_model");
		$resp = $this->Generator_model->exec();
		echo (!empty($resp['Error_Msg']) ? $resp['Error_Msg'] : "Список резервных идентификаторов актуализирован");
		return true;
	}
	
	/**
	 * Рассылка уведомлений об остатках квот
	 * получатели: сотрудники, находящиеся на службе типа «Регистратура поликлиники» МО
	 */
	public function QuoteNoticeSend() {
		$this->load->model('TimetableQuote_model', 'dbmodel');
		$res = $this->dbmodel->QuoteNoticeSend();
		echo "Задание выполнено ".date('d.m.Y , H:i:s');
	}
	
	/**
	 * Запуск задания на обработку данных от ФРМО
	 */
	public function execFRMODataParser() {
		$this->load->library('textlog', array('file' => 'FRMO_' . date('Y_m_d') . '.log'));
		$this->load->model('FRMO_model', 'dbmodel');
		$res = $this->dbmodel->parseFRMOData();
		echo (!empty($res) ? $res : "Обработка завершена");
	}
	
	/**
	 * Получение номеров рецептов
	 */
	public function getEvnReceptSerialNum() {
		$this->load->model('ServiceEMIAS_model', 'dbmodel');
		$res = $this->dbmodel->getEvnReceptSerialNumCron();
		echo "Задание выполнено ".date('d.m.Y, H:i:s');
	}
	
	/**
	 * Уведомления членов врачебной комиссии, просрочивших согласование направлений на ВК
	 */
	public function sendVoteListVKNotice() {
		$this->load->model('VoteListVK_model', 'dbmodel');
		$res = $this->dbmodel->sendVoteListVKNotice();
		echo "Задание выполнено ".date('d.m.Y, H:i:s');
	}
}