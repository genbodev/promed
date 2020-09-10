<?php

class SwParallelSessions {

	/**
	 * @var CI_Controller|null
	 */
	private $ci = null;

	/**
	 * SwParallelSessions constructor.
	 */
	public function __construct() {
		$this->ci =& get_instance();
		$this->ci->load->model( "User_model", "usermodel" );
	}

	function getCountParallelSessionsForUserAndGroups($user) {
		$userCountSessions = trim($user->parallelSessions);
		$userGroupsCountSessions = trim($user->getMinParallelSessionsCount());

		return array($userCountSessions, $userGroupsCountSessions);
	}

	function checkIPonParallelSessionCount($ip) {
		$this->ci->load->model( "IPSessionCount_model");

		$response = $this->ci->IPSessionCount_model->checkIPonExist($ip);

		return $response;
	}

	function getMinCountParallelSession($user) {
		$count = $this->getCountParallelSessionsForUserAndGroups($user);

		$ipCount = $this->checkIPonParallelSessionCount(getClientIP());

		if(isset($ipCount[0])){
			array_push($count, $ipCount[0]['IPSessionCount_Max']);
		}

		$diff = array_diff($count, array(''));
		if (count($diff) > 0) {
			$countParallelSessions = min($diff);
		} else {
			$countParallelSessions = 0;
		}

		return $countParallelSessions;
	}

    /**
     * @param pmAuthUser $user
     */
	public function checkOnParallelSessions(pmAuthUser $user) {

		$checkCountParallelSessions = $this->ci->usermodel->getCheckCountParallelSessions();

		if($checkCountParallelSessions) {
			$countParallelSessions = $this->getMinCountParallelSession($user);

			if(!$countParallelSessions) {
				$countParallelSessions = $this->ci->usermodel->getCountParallelSessions();
			}

			$sessions = $this->ci->usermodel->getUserSessionsByLogin($user->login);
			$sessionsCount = count($sessions);
			if ($sessionsCount >= $countParallelSessions) {
				$sessionsToDestroy = array_splice($sessions, -($sessionsCount-$countParallelSessions+1));
				$this->destroySessions($sessionsToDestroy);
			}
		}
	}

	private function destroySessions($sessions)
	{
		if(empty($sessions)) return false;

		if (session_id()){session_commit();}

		if (defined('USERAUDIT_LOGINS') && USERAUDIT_LOGINS) {
			require_once(APPPATH . 'libraries/UserAudit.php');
		}

		foreach ( $sessions as $session) {
			session_id($session['Session_id']);
			session_start();
			session_destroy();
			session_commit();
			if (class_exists('UserAudit')) {
				UserAudit::Logout($session['Session_id']);
			}
			if (getRegionNick() == 'vologda') $this->logoutNewSmp($session['pmUser_id']);
		}

		session_start();
		session_regenerate_id();
	}

	/*
	 * выход из сессии в смп 2 через апи
	 * передаем pmuser_id
	 */
	private function logoutNewSmp($pmuser_id) {
		if (empty($this->ci->config->item('newSmpServer'))){
			return false;
		}

		$url = substr($this->ci->config->item('newSmpServer'),0,-1); //test = 'https://expsmp.promedweb.ru'
		$port = $this->ci->config->item('newSmpPort'); //test = '8000'

		$urlAll = $url.':'.$port.'/api/user/logout?pmuser_id='.$pmuser_id;

		$ch = curl_init() ;
		curl_setopt( $ch , CURLOPT_URL , $urlAll ) ;
		curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 ) ;
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , 'POST' ) ;

		if (defined('USE_HTTP_PROXY') && USE_HTTP_PROXY) {
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, '' . ':' . '');
			curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_NTLM);
			curl_setopt($ch, CURLOPT_PROXY, '192.168.36.31:3128');
		}

		session_write_close() ;

		$fp = curl_exec( $ch ) ;

		curl_close( $ch ) ;
	}
}