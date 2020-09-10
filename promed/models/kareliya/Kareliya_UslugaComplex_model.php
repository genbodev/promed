<?php
/**
 * @property Usluga_model Usluga_model
 * @property CureStandartUslugaComplexLink_model $CureStandartUslugaComplexLink_model
 */

require_once(APPPATH.'models/UslugaComplex_model.php');

class Kareliya_UslugaComplex_model extends UslugaComplex_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	* Загрузка услуг для комбобокса 110
	* Только услуга A11.12.003.002
	* @task https://redmine.swan.perm.ru/issues/104701
	* #костыль #ортопедия
	*/	
	public function loadUslugaSMPCombo($data) {
		$query = "
			select
				 uc.UslugaComplex_id
				,ucat.UslugaCategory_id
				,ucat.UslugaCategory_Name
				,uc.UslugaComplex_pid
				,convert(varchar(10), uc.UslugaComplex_begDT, 104) as UslugaComplex_begDT
				,convert(varchar(10), uc.UslugaComplex_endDT, 104) as UslugaComplex_endDT
				,uc.UslugaComplex_Code
				,rtrim(isnull(uc.UslugaComplex_Name, '')) as UslugaComplex_Name
				,uc.UslugaComplex_UET
			from
				v_UslugaComplex uc with (nolock)
                inner join v_UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where
				uc.UslugaComplex_Code = 'A11.12.003.002'
		";
		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}
}