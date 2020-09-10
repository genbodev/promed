<?php defined('BASEPATH') or die ('No direct script access allowed');


require_once(APPPATH.'controllers/Common.php');

class Samara_Common extends Common {
  
	function __construct() {
		parent::__construct();
					
		$this->load->model('Samara_Common_model', 'samara_dbmodel');
	}


	function loadPersonData() {
		$logging = true; //логирование обращений к сессии и к бд
	
		$this->load->helper('Text');

		$data = array();
		$val  = array();

		$is_short = false;
		$load_from_db = false; //загрузка из дб, игнорируя кэш
		$sess_var = 'person';
		$data['Person_id'] = 0;
		$data['Server_id'] = 0;
		$data['PersonEvn_id'] = 0;
		$data = $this->ProcessInputData('loadPersonData');

		if ( $data === false ) {
			return false;
		}
		
		if ( isset($_POST['LoadShort']) && $_POST['LoadShort'] == 'true' ) {
			$is_short = true;
			$sess_var = 'person_short';
		}
		if ( isset($_POST['loadFromDB']) && $_POST['loadFromDB'] == 'true' ) {
			$load_from_db = true;
		}
		// данные о человеке из сессии
		if ( 
			!$load_from_db &&
			isset($_SESSION[$sess_var]) && 
			((
				!isset($data['PersonEvn_id']) &&
				isset($data['Person_id']) &&
				isset($_SESSION[$sess_var]['Person_id']) && 
				$_SESSION[$sess_var]['Person_id'] == $data['Person_id']
			) ||
			(
				isset($data['PersonEvn_id']) &&
				isset($_SESSION[$sess_var]['PersonEvn_id']) &&
				$data['PersonEvn_id'] == $_SESSION[$sess_var]['PersonEvn_id'] && 
				isset($data['Server_id']) &&
				isset($_SESSION[$sess_var]['Server_id']) &&
				$data['Server_id'] == $_SESSION[$sess_var]['Server_id']
			))
		)
		{
			//пишем в лог id пользователя, данные для которого извлекли из бд 
			/*
			if ($logging) {
				$person_str = '';
				if (isset($_SESSION[$sess_var]['Person_id'])) $person_str .= 'Person_id: '.$_SESSION[$sess_var]['Person_id'].' ';
				if (isset($_SESSION[$sess_var]['PersonEvn_id'])) $person_str .= 'PersonEvn_id: '.$_SESSION[$sess_var]['PersonEvn_id'].' ';
				$f_log = fopen(PROMED_LOGS . 'load.person.data.from.session.log', 'a');				
				fputs($f_log, $person_str);
				fputs($f_log, "\r\n");
				fclose($f_log);
			} 
			*/
			$this->ReturnData(array($_SESSION[$sess_var]));
			return true;
		}
		// тянем из базы
		if ( $is_short )
			$response = $this->samara_dbmodel->loadPersonDataShort($data);
		else
		{
			$response = $this->samara_dbmodel->loadPersonData($data);
		}
		$this->ProcessModelList($response)->ReturnData();
		$val = $this->GetOutData(0);
		// записываем в сессию
		$_SESSION[$sess_var] = $val;
		// если запрашивали на определенное событие, то пишем еще и идентификатор события добавления периодики
		if ( isset($data['PersonEvn_id']) && $data['PersonEvn_id'] > 0 && isset($data['Server_id']) )
		{
			$_SESSION[$sess_var]['PersonEvn_id'] = $data['PersonEvn_id'];
			$_SESSION[$sess_var]['Server_id'] = $data['Server_id'];
		}
		
		//пишем в лог id пользователя, данные для которого извлекли из бд
		/*
		if ($logging) {
			$person_str = '';
			if (isset($val['Person_id'])) $person_str .= 'Person_id: '.$val['Person_id'].' ';
			if (isset($val['PersonEvn_id'])) $person_str .= 'PersonEvn_id: '.$val['PersonEvn_id'].' ';
			$f_log = fopen(PROMED_LOGS . 'load.person.data.from.bd.log', 'a');
			fputs($f_log, 'Person_id: '.$val['Person_id']);
			fputs($f_log, "\r\n");
			fclose($f_log);
		}
		*/
		return true;
	}	
}
