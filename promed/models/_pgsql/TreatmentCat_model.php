<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* TreatmentCat_model - модель, для работы с таблицами
* редактируемых справочников обращений
* TreatmentCat, TreatmentMethodDispatch, TreatmentRecipientType
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Promed
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Permyakov Alexander <permjakov-am@mail.ru>
* @version      02.07.2010
*/

class TreatmentCat_model extends SwPgModel {

	private $object = '';

	/**
	 *	Method description
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Method description
	 */
	private function getObject($data) {
		if (empty($data['Object']))
			$data['Object'] = '';
		if(in_array($data['Object'],array('TreatmentCat','TreatmentMethodDispatch','TreatmentRecipientType')))
		{
			return $data['Object'];
		}
		else
		{
			exit(json_encode(array('success' => false, 'Error_Code' => 777 , 'Error_Msg' => toUTF('Неправильный параметр Object'))));
		}
	}

	/**
	*  Получение списка для комбобокса
	*/
	function getList($data) {
		$this->object = $this->getObject($data);
		$filter = '';
		if (!empty($data[$this->object.'_id']))
		{
			$filter .= " and {$this->object}_id = :{$this->object}_id";
		}
		if (!empty($data[$this->object.'_Code']))
		{
			$filter .= " and {$this->object}_Code = :{$this->object}_Code";
		}
		if (!empty($data[$this->object.'_Name']))
		{
			$data[$this->object.'_Name'] = '%'.$data[$this->object.'_Name'].'%';
			$filter .= ' and '.$this->object.'_Name ilike :'.$this->object.'_Name';
		}
		$query = "
			SELECT
				{$this->object}_id as \"{$this->object}_id\",
				{$this->object}_Name as \"{$this->object}_Name\",
				{$this->object}_Code as \"{$this->object}_Code\"
			FROM
				v_{$this->object}
			WHERE (1 = 1) {$filter}
			limit 100
		";
		$res = $this->db->query($query,$data);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	*  Возвращает максимальный TreatmentCat_Code + 1, для автогенерации кода нового элемента справочника
	*/
	function getMaxItemCode($data) {
		$this->object = $this->getObject($data);
		$query = "
			select
				MAX(". $this->object ."_Code) + 1 as \"Code\"
			from ". $this->object ."
		";
		$res = $this->db->query($query);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	* Сохраняет запись справочника
	*/
	function saveItem($data) {
		$this->object = $this->getObject($data);
		// Сохраняем или редактируем запись
		if ( empty($data['TreatmentCat_id']) )
		{
			$data['TreatmentCat_id'] = NULL;
			$procedure_action = "ins";
		}
		else
		{
			$procedure_action = "upd";
		}

		$query = "
			select
				{$this->object}_id as \"id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_{$this->object}_{$procedure_action}(
				Server_id := :Server_id,
				". $this->object ."_id := :id,
				". $this->object ."_Code := :Code,
				". $this->object ."_Name := :Name,
				". $this->object ."_IsDeletes := :IsDeletes,
				pmUser_id := :pmUser_id
			)
		";

		$queryParams = array(
			'id' => $data['TreatmentCat_id'],
			'Server_id' => $data['Server_id'],
			'Code' => $data['TreatmentCat_Code'],
			'Name' => $data['TreatmentCat_Name'],
			'IsDeletes' => $data['TreatmentCat_IsDeletes'],
			'pmUser_id' => $data['pmUser_id']
		);
		//die(getDebugSQL($query, $queryParams));
		$res = $this->db->query($query, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	*  Возвращает данные записи
	*/
	function getItem($data) {
		$this->object = $this->getObject($data);
		$query = "
			SELECT
				". $this->object ."_id as \"TreatmentCat_id\",
				". $this->object ."_Code as \"TreatmentCat_Code\",
				RTRIM(". $this->object ."_Name) as \"TreatmentCat_Name\",
				". $this->object ."_IsDeletes as \"TreatmentCat_IsDeletes\"
			FROM v_". $this->object ."
			WHERE ". $this->object ."_id = :id
			limit 1
		";
		$res = $this->db->query($query, array('id' => $data['id']));
		if ( is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	*  Проверяем использование записи в таблице обращений
	*/
	function checkUseItem($data) {
		$this->object = $this->getObject($data);
		$query = "
			SELECT
				Treatment_id as \"Treatment_id\"
			FROM v_Treatment
			WHERE ". $this->object ."_id = :id
			limit 1
		";
		$res = $this->db->query($query, array('id' => $data['id']));
		if ( is_object($res) )
		{
			if (count($res->result('array')) > 0)
				return array('success' => false, 'Error_Code' => 667 , 'Error_Msg' => toUTF('Элемент справочника не может быть удален! Возможные причины: данное значение используется в журнале обращений.'));
			else
				return false;
		}
		else
		{
			return array('success' => false, 'Error_Code' => 666 , 'Error_Msg' => toUTF('Ошибка запроса к базе данных при удалении записи! Возможные причины: удаление записи запрещено или запись не найдена.'));
		}
	}

	/**
	*  Удаление записи
	*/
	function delItem($data) {
		$this->object = $this->getObject($data);
		$query = "
			select
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from p_{$this->object}_del(
				{$this->object}_id := :id
			)
		";
		$result = $this->db->query($query, array(
			'id' => $data['id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return array('success' => false, 'Error_Code' => 666 , 'Error_Msg' => toUTF('Ошибка запроса к базе данных при удалении записи! Возможные причины: удаление записи запрещено или запись не найдена.'));
		}
	}
}
