<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb
 *
 * Класс модели для общих операций используемых во всех модулях
 *
 * The New Generation of Medical Statistic Software
 *
 * @package				Common
 * @copyright			Copyright (c) 2009 Swan Ltd.
 * @author				Stas Bykov aka Savage (savage@swan.perm.ru)
 * @link				http://swan.perm.ru/PromedWeb
 * @version				?
 */

require_once(APPPATH.'models/_pgsql/Farmacy_model.php');

class Khak_Farmacy_model extends Farmacy_model {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
}
