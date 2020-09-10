<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* AutoAttach - контроллер для выполенния операций автоматическим прикреплением пациентов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      11.12.2009
*/

class AutoUsers extends swController {
	/**
	 * AutoUsers constructor.
	 */
	function __construct()
	{
		parent::__construct();
		$this->users = new pmAuthUsers('*');
	}

	/**
	 * Некая функция
	 */
	function insertUsersDo() {
		$strings = $this->read_strings_from_file('C:\WEBSERVER\htdocs\promed\promed\promed\controllers\users.csv');
		$usrs = $this->parseUsers($strings);
		$this->load->database();
		$this->load->model('User_model', 'dbmodel');
		$cnt = 0;
		reset($usrs);
		foreach ($usrs as $key=>$usr)
		{
			$id = $this->dbmodel->getLpuIdFromLpuNick($usr['Lpu_Nick']);			
			if ( $id !== false )
			{
				$usrs[$key]['Lpu_id'] = $id;
			}
			else
			{
				$id = $this->dbmodel->getLpuIdFromLpuName($usr['Lpu_Name']);
				if ( $id !== false )
				{
					$usrs[$key]['Lpu_id'] = $id;					
				}
				else
				{
					$usrs[$key]['Lpu_id'] = null;
				}
			}
			//echo $usrs[$key]['Lpu_id']."<br>";
			if ( isset($usrs[$key]['Lpu_id']) )
			{
				// проверяем, есть ли пользователь уже в базе
				if ( !$this->checkIfUserExists($usrs[$key]['User']) )
				{
					$data = array();
					$data['surname'] = 'Administrator'.$usrs[$key]['Lpu_id'];
					$data['firname'] = 'Administrator';
					$data['login'] = $usrs[$key]['User'];
					$data['pass'] = $usrs[$key]['Pass'];
					$data['group1'] = 'LpuAdmin';
					$data['lpu_id1'] = $usrs[$key]['Lpu_id'];
					$data['lpu_id'] = $usrs[$key]['Lpu_id'];
					$data['email'] = $usrs[$key]['Lpu_id'].'admin@admin.ru';
					$data['desc'] = 'admin_lpu'.$usrs[$key]['Lpu_id'];
					echo "<pre>";
					var_dump($data);
					echo "<br>";
					//$this->saveUserData($data);
					//sleep(1);
					//die('dsds');
				}
				else
				{
					$cnt++;
				}
			}
		}
		echo $cnt;
	}
	
	/**
	 * Сохранение информации о конкретном пользователе
	*/
	function saveUserData($data) {
		$this->load->helper('Text');
		
		$newUser = pmAuthUser::add(trim($data['surname'] . " " . $data['firname']), $data['login'], $data['pass'], $data['surname'], $data['secname'], $data['firname']);
		// добавляем новые группы
		if ( isset($data['group1']) ) {
			$i = 1;
			while ( isset( $data['group'.$i] ) ) {
				$newUser->addGroup($data['group'.$i]);
				$i++;
			}
		}
		// добавляем новые ЛПУ
		if ( isset($data['lpu_id1']) ) {
			$i = 1;
			while ( isset( $data['lpu_id'.$i] ) ) {
				$newUser->addLpu($data['lpu_id'.$i], $data['lpu_id'.$i]);
				$i++;
			}
		}
		else {
			
		}
		$newUser->email = $data['email'];
		$newUser->server_id = $data["lpu_id"];
		$newUser->desc = $data['desc'];
		$newUser->post();
	}

	/**
	 * @param $login
	 * @return bool
	 */
	function checkIfUserExists($login)
	{
		reset($this->users->users);
		foreach ( $this->users->users as $user )
		{
			
			if ( strtolower(trim($user->login)) ==  strtolower(trim($login)) )
			{
				//echo "'".$user->login."' - '".$login."'"."<br>";
				return true;
			}
		}
		//echo "'".$login."'"."<br>";
		return false;
	}

	/**
	 * @param $userstrs
	 * @return array
	 */
	function parseUsers($userstrs)
	{
		$arr = array();
		$res_arr = array();
		reset($userstrs);
		foreach ($userstrs as $ustr)
		{
			$arr = explode(';', $ustr);
			if ( isset($arr) && isset($arr[0]) && isset($arr[1]) && isset($arr[2]) && isset($arr[3]) && $arr[0] != "" && $arr[1] != "" && $arr[2] != "" && $arr[3] != "")
			{
				$res_arr[] = array('Lpu_Name'=>trim($arr[0]), 'Lpu_Nick'=>trim($arr[1]), 'User'=>trim($arr[2]), 'Pass'=>trim($arr[3]));
			}
		}
		return $res_arr;
	}

	/**
	 * @param $filename
	 * @return array|bool
	 */
	function read_strings_from_file($filename)
	{
		$ret = false;
		if ( !file_exists ($filename) )
			return $ret;
		
		$strings = array();
			
		$f = fopen($filename, 'r');
		while ( !feof($f) )
		{
			$str = fgets($f, 4096);
			$strings[] = $str;			
		}
		return $strings;
	}
}

?>