<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Astra_EvnUslugaDispDop_model - модель для работы с услугами дд (Астрахань)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009-2014 Swan Ltd.
* @author       Stanislav Bykov
* @version      08.008.2014
*/

require_once(APPPATH.'models/EvnUslugaDispDop_model.php');

class Astra_EvnUslugaDispDop_model extends EvnUslugaDispDop_model {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 *	Получнеие списка профилей
	 */
	function loadLpuSectionProfileList($data) {
		$query = "
			select
				LSP.LpuSectionProfile_id,
				LSP.LpuSectionProfile_Code,
				RTRIM(LSP.LpuSectionProfile_Name) as LpuSectionProfile_Name
			from
				v_LpuSectionProfile LSP (nolock)
			where
				ISNULL(LSP.LpuSectionProfile_begDT, '1970-01-01') <= :onDate
				and ISNULL(LSP.LpuSectionProfile_endDT, '2030-12-31') >= :onDate
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 *	Получение списка специальностей
	 */
	function loadMedSpecOmsList($data) {
		$query = "
			select
				MSO.MedSpecOms_id,
				MSO.MedSpecOms_Code,
				RTRIM(MSO.MedSpecOms_Name) as MedSpecOms_Name
			from
				v_MedSpecOms MSO (nolock)
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
}
