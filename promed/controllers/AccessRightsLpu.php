<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * AccessRightsLpu - контроллер для работы c правами доступа к МО
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			19.09.2014
 *
 * @property AccessRightsDiag_model dbmodel
 */
require_once('AccessRights.php');

class AccessRightsLpu extends AccessRights {
	protected $model_name = 'AccessRightsLpu_model';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
}