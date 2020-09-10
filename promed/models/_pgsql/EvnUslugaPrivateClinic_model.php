<?php

/**
 * UslugaPrivateClinic - контроллер для работы с услугами услугами частных медицинских учреждений
 *
 * @author       Gilmiyarov Artur aka gaf
 * @version      27.04.2018
 */

class EvnUslugaPrivateClinic_model extends SwPgModel
{
	public $inputRules = array(
			'save' => array(
				
				array(
					'field' => 'Evn_id',
					'label' => 'Идентификатор услуги',
					'rules' => '',
					'type' => 'id'
				),				
				array(
					'field' => 'Person_id',
					'label' => 'Учетный документ (посещение или движение в стационаре)',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Lpu_id',
					'label' => 'Состояние данных человека',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'int'
				)
			),
		);			
	
	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		

	}	
	/**
	 * Сохранение услуги
	 */
	function save($data) {
		// проверки перед сохранением
		$this->load->model('EvnUslugaPrivateClinic_model', 'EvnUslugaPrivateClinic');
			
		//echo "<pre>".print_r($data, 1)."</pre>";
		
		// сохраняем
		$procedure = 'p_EvnUslugaPrivateClinic_upd';
		if (empty($data['Evn_id']))
		{
			$procedure = 'p_EvnUslugaPrivateClinic_ins';
			$data['EvnUslugaOnkoChem_id'] = null;
		}
		$q = "	
            select 
                EvnUslugaid as \"EvnUsluga_id\",
                Error_Code as \"Error_Code\",
                Error_Message as \"Error_Message\"
			from dbo." . $procedure . " (
				Person_id := :Person_id,
				Lpu_id := :Lpu_id,
				UslugaComplex_id := :UslugaComplex_id,
				Evn_id := :Evn_id,
				MedPersonal_iidd := :MedPersonal_iidd,
				Research_Data := :Research_Data,
				pmUser_id := :pmUser_id
				)
		";		
		$p = array(
			'Person_id' => $data['Person_id'],
			'Lpu_id' => $data['Lpu_id'],
			//'Server_id' => $data['Server_id'],
			'Evn_id' => $data['Evn_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			//'MedPersonal_iidd' => isset($data['MedPersonal_iidd']) ? $data['MedPersonal_iidd'] : NULL,
			'MedPersonal_iidd' => isset($data['MedPersonal_iidd']) ? $data['MedPersonal_iidd'] : NULL,
			'Research_Data' => $data['Research_Data'],
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSql($q, $p);
		$r = $this->db->query($q, $p);
		if (is_object($r)) {
			$result = $r->result('array');
		}
		else {
			log_message('error', var_export(array('q' => $q, 'p' => $p, 'e' => sqlsrv_errors()), true));
			$result = array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
		return $result;
	}
	
	/**
	 * Получение Идентификатора врача
	 */
	function getMedPersonalid($data) {
		if ( $this->db->dbdriver == 'postgre' ) {
			$sql = "SELECT Lpu_id as \"Lpu_id\" FROM dbo.v_Lpu WHERE Org_id=? LIMIT 1";
			$query = $this->db->query( $sql, array( $data['Org_id'] ) );
			if ( is_object( $query ) ) {
				$result = $query->row_array();
				if ( sizeof( $result ) ) {
					return $result['Lpu_id'];
				}
			}

			return null;
		} else {
			$query = "
				SELECT
					eu.MedPersonal_id as \"MedPersonal_id\"
				FROM v_evnusluga eu
				WHERE eu.evnusluga_id = :Evn_id
				LIMIT 1
			";
			$result = NULL;
			//echo getDebugSql($query, array('Org_id' => $data['Org_id']));
			$res = $this->db->query($query, array('Evn_id' => $data['Evn_id']));
			
			//echo "<pre>22".print_r($res, 1)."</pre>";
			if ( is_object($res) ) {
				$rows = $res->result('array');
				if (count($rows)>0) {
					$result = $rows[0]['MedPersonal_id'];
				}
			}
			return $result;
		}
	}	
	/**
	 *	Помечаем запись реестра на удаление 
	 */
	function deleteData($data)
	{			
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from dbo.p_EvnUslugaPrivateClinic_del (
				Evn_id = :Evn_id
				)
		";
		$res = $this->db->query($query, $data);
		
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
		{
			return false;
		}
	}	
}
