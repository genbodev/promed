<?php
/**
 * Convert_model - модель для работы с кодировками
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2009-2012 Swan Ltd.
 * @author			Stas Bykov aka Savage (savage1981@gmail.com)
 * @version			2014-07-18
 */
class Convert_model extends swPgModel {
	/**
	 * Конструктор класса
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Проводит конвертацию данных из UTF-8 в WIN-1251 данных из таблицы DataStorage
	 */
	function convertDataStorage($data) {
		$query = "
			select
				DataStorage_id as \"DataStorage_id\",
				DataStorage_Value as \"DataStorage_Value\"
			from DataStorage
		";
		$queryParams = array();

		if ( !empty($data['object']) ) {
			$query .= "where DataStorage_Name = :DataStorage_Name";
			$queryParams['DataStorage_Name'] = $data['object'];
		}

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к БД (1)'));
		}

		$response = $result->result('array');

		if ( is_array($response) && count($response) > 0 ) {
			$query = "
				update DataStorage
				set DataStorage_Value = :DataStorage_Value
				where DataStorage_id = :DataStorage_id
			";

			foreach ( $response as $array ) {
				if ( empty($array['DataStorage_Value']) || is_numeric($array['DataStorage_Value']) ) {
					continue;
				}

				$value = toAnsi($array['DataStorage_Value'], true);

				if ( mb_detect_encoding($value, 'UTF-8', TRUE) == 'UTF-8' ) {
					//echo $array['DataStorage_id'], ': ', $value, '<br />';

					$queryParams = array(
						 'DataStorage_id' => $array['DataStorage_id']
						,'DataStorage_Value' => $value
					);

					$result = $this->db->query($query, $queryParams);
				}
			}
		}

		return array(array('Error_Msg' => ''));
	}
}
