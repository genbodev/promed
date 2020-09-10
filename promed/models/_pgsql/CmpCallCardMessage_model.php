<?php
class CmpCallCardMessage_model extends swPgModel {
	/**
	 * Конструктор.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Установка даты получения карты СМП на планшете
	 */
	function setCmpCallCardMessageTabletDT($data) {
		$this->db->query("update CmpCallCardMessage set CmpCallCardMessage_tabletDT = dbo.tzGetDate() where CmpCallCardMessage_tabletDT is null and CmpCallCard_id = :CmpCallCard_id", array(
			'CmpCallCard_id' => $data['CmpCallCard_id']
		));

		return true;
	}
}
