<?php
defined('BASEPATH') or die ('No direct script access allowed');

require_once(APPPATH.'models/_pgsql/TFOMSAutoInteract_model.php');

/**
 * TFOMSAutoInteract_model - модель для автоматического взаимодействия с ТФОМС
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			TFOMSAutoInteract
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan-it.ru)
 * @version			01.2019
 */
class Perm_TFOMSAutoInteract_model extends TFOMSAutoInteract_model {
	protected $allowSaveGUID = true;
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @return array
	 */
	function getPackageTypeMap() {
		return array_merge(parent::getPackageTypeMap(), array(
			'PERSONATTACHDISTRICT' => 'PERSONATTACH',
		));
	}

	/**
	 * @return array
	 */
	function getPackageFieldsMap() {
		$map = parent::getPackageFieldsMap();

		$map['DISPPLAN'] = array(
			'TEL1' => 'TEL',
		);

		return $map;
	}
}