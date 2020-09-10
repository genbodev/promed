<?php
/**
 * @property Usluga_model Usluga_model
 * @property CureStandartUslugaComplexLink_model $CureStandartUslugaComplexLink_model
 */

require_once(APPPATH.'models/_pgsql/UslugaComplex_model.php');

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
				 uc.UslugaComplex_id as \"UslugaComplex_id\"
				,ucat.UslugaCategory_id as \"UslugaCategory_id\"
				,ucat.UslugaCategory_Name as \"UslugaCategory_Name\"
				,uc.UslugaComplex_pid as \"UslugaComplex_pid\"
				,to_char(uc.UslugaComplex_begDT, 'dd.mm.yyyy') as \"UslugaComplex_begDT\"
				,to_char(uc.UslugaComplex_endDT, 'dd.mm.yyyy') as \"UslugaComplex_endDT\"
				,uc.UslugaComplex_Code as \"UslugaComplex_Code\"
				,rtrim(COALESCE(uc.UslugaComplex_Name, '')) as \"UslugaComplex_Name\"
				,uc.UslugaComplex_UET as \"UslugaComplex_UET\"
			from
				v_UslugaComplex uc
                inner join v_UslugaCategory ucat on ucat.UslugaCategory_id = uc.UslugaCategory_id
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