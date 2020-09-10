<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsDiag - контроллер для работы c правами доступа к тестам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Abakhri Samir
 * @version			09.07.2015
 *
 * @property AccessRightsTest_model dbmodel
 */
require_once('AccessRights.php');

class AccessRightsTest extends AccessRights {
	protected $model_name = 'AccessRightsTest_model';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
}