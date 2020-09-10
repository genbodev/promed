<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * MedSvid - контроллер для работы с медицинскими свидетельствами
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 - 2010 Swan Ltd.
 * @author       Salakhov Rustam
 * @version      14.12.2011
 * @property MedSvid_model dbmodel
 */
require_once(APPPATH.'controllers/MedSvid.php');

class Kz_MedSvid extends MedSvid {
	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();

		foreach($this->inputRules['saveMedSvidBirth'] as &$item) {
			if (in_array($item['field'], array('BirthSvid_Ser','BirthSvid_Num','LpuLicence_id','OrgHead_id','MedStaffFact_cid'))) {
				$item['rules'] = '';
			}
		}
	}
}