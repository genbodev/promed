<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonDispReg_model - модель для работы с регистром заболеваний
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Polka
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author			Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor	Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version			24.07.2009
*/
class PersonDispReg_model extends SwPgModel {
	/**
	 * PersonDispReg_model constructor.
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	* Получение категорий регистра заболеваний
	*/
	function getSicknessTree()
	{
		$sql = "
			SELECT
				1 as \"Sickness_id\",
				PrivilegeType_id as \"PrivilegeType_id\",
				Sickness_Name as \"Sickness_Name\",
				Sickness_id as \"Sickness_id\"
			FROM v_Sickness
			WHERE (1=1)
				and (PrivilegeType_id is not null)";
			$res = $this->db->query($sql);
			if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение информации о человеке в регистре заболеваний
	*/
	function getPersonDispReg($data)
	{
		$sql = "
			select
				to_char(cast(PersonDispReg_begDate as timestamp), 'dd.mm.yyyy') as \"Sickness_Date\",
				LpuSection_id as \"LpuSection_id\",
				MedPersonal_id as \"MedPersonal_id\",
				Diag_id as \"Diag_id\",
				to_char(cast(PersonDispReg_endDate as timestamp), 'dd.mm.yyyy') as \"Sickness_Date_End\",
				DispOutType_id as \"DispOutType_id\"
			from
				v_PersonDispReg
			where
				PersonDispReg_id = {$data['PersonDispReg_id']}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	* Сохранение человека в регистре заболеваний
	*/
	function savePersonDispReg($data)
	{
		switch ( $data['mode'] )
		{
			case 'add':
				$Server_id = trim($data['Server_id']);
				$Sickness_id = trim($data['Sickness_id']);
				$Person_id = trim($data['Person_id']);
				$Diag_id = trim($data['Diag_id']);
				$Lpu_id = $data['Lpu_id'];
				$LpuSection_id = trim($data['LpuSection_id']);
				$MedPersonal_id = trim($data['MedPersonal_id']);
				$insert_date = trim($data['Sickness_Date']);
				$PersonDispReg_begDate = $insert_date=='null'?"null":"'".$insert_date."'";
				$dis_date = trim($data['Sickness_Date_End']);
				$PersonDispReg_endDate = $dis_date=='null'?"null":"'".$dis_date."'";
				if ( isset($data['DispOutType_id']) )
				{
					$type = trim($data['DispOutType_id']);
					$DispOutType_id = empty($type)?"NULL":trim($data['DispOutType_id']);
				}
				else
					$DispOutType_id = "null";
				$sql = "
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\",
						PersonDispReg_id as \"PersonDispReg_id\"
					from p_PersonDispReg_ins(
						Server_id := {$Server_id},
						Sickness_id := {$Sickness_id},
						Person_id := {$Person_id},
						Diag_id := {$Diag_id},
						Lpu_id := {$Lpu_id},
						LpuSection_id := {$LpuSection_id},
						MedPersonal_id := {$MedPersonal_id},
						DispOutType_id := {$DispOutType_id},
						PersonDispReg_begDate  := cast({$PersonDispReg_begDate} as timestamp),
						PersonDispReg_endDate := cast({$PersonDispReg_endDate} as timestamp),
						pmUser_id := {$data['session']['pmuser_id']}
					)";
				return $res = $this->db->query($sql);
			break;
			case 'edit':
				$Server_id = trim($data['Server_id']);
				$Sickness_id = trim($data['Sickness_id']);
				$Person_id = trim($data['Person_id']);
				$Diag_id = trim($data['Diag_id']);
				$Lpu_id = $data['Lpu_id'];
				$LpuSection_id = trim($data['LpuSection_id']);
				$MedPersonal_id = trim($data['MedPersonal_id']);
				$PersonDispReg_id = trim($data['PersonDispReg_id']);
				$insert_date = trim($data['Sickness_Date']);
				$PersonDispReg_begDate = $insert_date=='null'?"null":"'".$insert_date."'";
				$dis_date = trim($data['Sickness_Date_End']);
				$PersonDispReg_endDate = $dis_date=='null'?"null":"'".$dis_date."'";
				if ( isset($data['DispOutType_id']) )
				{
					$type = trim($data['DispOutType_id']);
					$DispOutType_id = empty($type)?"NULL":trim($data['DispOutType_id']);
				}
				else
					$DispOutType_id = "null";
				$sql = " 
					select
						Error_Code as \"Error_Code\",
						Error_Message as \"Error_Msg\",
						PersonDispReg_id as \"PersonDispReg_id\"
					from p_PersonDispReg_upd(
						Server_id := {$Server_id},
						PersonDispReg_id := {$PersonDispReg_id},
						Sickness_id := {$Sickness_id},
						Person_id := {$Person_id},
						Diag_id := {$Diag_id},
						Lpu_id := {$Lpu_id},
						LpuSection_id := {$LpuSection_id},
						MedPersonal_id := {$MedPersonal_id},
						DispOutType_id := {$DispOutType_id},
						PersonDispReg_begDate  := cast({$PersonDispReg_begDate} as timestamp),
						PersonDispReg_endDate := cast({$PersonDispReg_endDate} as timestamp),
						pmUser_id := {$data['session']['pmuser_id']}
					)";
				return $res = $this->db->query($sql);
			break;
		}

		//код ниже, похоже, недостижимый
		if (!is_object($result))
		{
			return array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение талона по ДД)');
		}

		$response = $result->result('array');

		if (!is_array($response) || count($response) == 0)
		{
			return false;
		}
		else if ($response[0]['Error_Msg'])
		{
			return $response;
		}
		return array(0 => array('PersonDispReg_id' => $data['PersonDispReg_id'], 'Error_Msg' => ''));
	}

	/**
	* Получение людей в регистре по выбранной категории заболеваний
	*/
	function getPersonDispRegListBySickness($data)
	{
		$sql = "
			SELECT
				PersonDispReg_id as \"disp_reg_id\",
				Person_id as \"person_id\",
				Server_id as \"server_id\",
				Person_SurName as \"last\",
				Person_FirName as \"name\",
				Person_SecName as \"second\",
				to_char(cast(Person_BirthDay as timestamp), 'dd.mm.yyyy') as \"birthday\",
				Diag_Code as \"mkb\",
				to_char(cast(PersonDispReg_begDate as timestamp), 'dd.mm.yyyy') as \"insert_date\",
				to_char(cast(PersonDispReg_endDate as timestamp), 'dd.mm.yyyy') as \"dis_date\"
			FROM
				v_PersonDispReg_all
			WHERE
				Sickness_id = {$data['node']} and
				Lpu_id = {$data['Lpu_id']}
			";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Исключение человека из регистра заболеваний
	*/
	function dropPersonDispReg($data)
	{
		$sql = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_PersonDispReg_del(
				PersonDispreg_id := {$data['PersonDispReg_id']},
				Server_id := {$data['Server_id']}
			)
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

}
