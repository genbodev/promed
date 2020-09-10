<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Specifica_model - модель для работы со спецификой
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      22.04.2010
*/

class Specifica_model extends swModel {
	/**
	 * Specifica_model constructor.
	 */
	function __construct()
	{
		parent::__construct();		
	}

	/**
	 * @param $data
	 */
	public function getSpecificDocumentSchema($data)
	{
		$sql = "
		";
	}

	/**
	 * Получение идентфиикатора исхода в движении
	 */
	function getBirthSpecStacId($data) {
		$params = array(
			'EvnSection_id' => $data['EvnSection_id']
		);
		$query = "
			select top 1 BSS.BirthSpecStac_id
			from v_BirthSpecStac BSS with(nolock)
			where BSS.EvnSection_id = :EvnSection_id
		";
		$BirthSpecStac_id = $this->getFirstResultFromQuery($query, $params, true);
		if ($BirthSpecStac_id === false) {
			return $this->createError('','Ошибка при получени идентификатора исхода беременности');
		}
		return array(array('success' => true, 'Error_Msg' => '', 'BirthSpecStac_id' => $BirthSpecStac_id));
	}
}
?>