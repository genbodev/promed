<?php
/**
* Предопределенные константы, сделано контроллером, так как нам нужен движок для работы с сессиями
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Petukhov Ivan aka Lich (megatherion@list.ru)
* @version      16.11.2010
*/

class JsConstants extends CI_Controller
{
	/**
	 * Конструктов
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Константы
	 */
	function index() {
		header('Content-Type: application/javascript');
		echo "
/**
 * Предопределенные константы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      18.06.2009
 */

;
var project_logo='/images/promed-web-logo.gif';
";

		$arMonthOf = array(
			1 => "января",
			2 => "февраля",
			3 => "марта",
			4 => "апреля",
			5 => "мая",
			6 => "июня",
			7 => "июля",
			8 => "августа",
			9 => "сентября",
			10 => "октября",
			11 => "ноября",
			12 => "декабря",
		);
		
		$this->config->load('portal', TRUE);
		$this->portal_config = $this->config->item('portal');
		
		echo "var project_name = '".$this->config->item('PROMED_PAGE_NAME')."'".";\n";
		//Для казахстана (refs #89845)
		$PROMED_DEV_NAME = $this->config->item('PROMED_DEV_NAME') ? $this->config->item('PROMED_DEV_NAME') : "ООО &laquo;СВАН&raquo;";
		$PROMED_SITE_URL = $this->config->item('PROMED_SITE_URL') ? $this->config->item('PROMED_SITE_URL') : "http://swan-it.ru";
		echo "var promed_dev_name = '" . $PROMED_DEV_NAME . "'".";\n";
		echo "var promed_site_url = '" . $PROMED_SITE_URL . "'".";\n";
		echo "window.allow_firefox_cadesplugin_async=1;\n";

		echo "var project_copyright='".$this->config->item('PROMED_COPYRIGHT')."'".";\n";
		echo "var IS_DEBUG = ".( $this->config->item('IS_DEBUG') ? $this->config->item('IS_DEBUG') : "0").";\n";
		echo "var TEST_ID_ENABLED = ".( $this->config->item('TEST_ID_ENABLED') ? $this->config->item('TEST_ID_ENABLED') : "0").";\n";
		echo "var HIDE_MENU_ON_ARMS = ".( $this->config->item('HIDE_MENU_ON_ARMS') ? $this->config->item('HIDE_MENU_ON_ARMS') : "0").";\n";
		echo "var ETOKEN_PRO_ENABLED = ".( $this->config->item('ETOKEN_PRO_ENABLED') ? $this->config->item('ETOKEN_PRO_ENABLED') : "0").";\n";
		$EvnPLStomNew_begDate = getEvnPLStomNewBegDate();
		echo "var EVNPLSTOMNEW_BEGDATE = new Date(".date('Y', $EvnPLStomNew_begDate).", ".(date('m', $EvnPLStomNew_begDate)-1).", ".date('d', $EvnPLStomNew_begDate).");\n";
		echo "var UserLogin = '".ArrayVal($_SESSION,'login')."'\n";
		echo "var UserName = '".toAnsi(ArrayVal($_SESSION,'user'))."';\n";
		echo "var UserLpu = '".ArrayVal($_SESSION,'lpu_name')."';\n";
		echo "var UserEmail = '".ArrayVal($_SESSION,'email')."';\n";
		echo "var UserDescr = '".toAnsi(ArrayVal($_SESSION,'desc'))."';\n";
		echo "var MP_NOT_ERMP = ".(isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('ufa', 'pskov', 'tambov', 'komi', 'amur')) ? "true" : "false").";\n";
		echo "var LPU_EDIT_ISUFA = ".( $this->config->item('LPU_EDIT_ISUFA') ? "true" : "false").";\n";
		echo "var GEARS_WARNING = '".(defined('GEARS_WARNING') ? GEARS_WARNING : "")."';\n";
		echo "var ForumLink = '".$this->portal_config['links']['forum']."'".";\n";
		echo "var IIS_server = 'http://promedservice.opencode.su/';\n";
		echo "var DisconnectOnInactivityTime = " . (!empty($this->config->item('DisconnectOnInactivityTime')) ? $this->config->item('DisconnectOnInactivityTime') : "120") . ";";
		echo "var LpuUserWarning = " . (!empty($this->config->item('LpuUserWarning')) ? "'" . $this->config->item('LpuUserWarning') . "'" : "false") . ";";
		echo "var REGISTRY_EXPORT_IN_TFOMS_ADDR = '" . $this->config->item('REGISTRY_EXPORT_IN_TFOMS_ADDR') . "';";
		echo "var DISP_PLANNING_GIT = '" . $this->config->item('DISP_PLANNING_GIT') . "';";
		$version = getPromedVersion();
		echo "var Revision = '{$version['commit']}'\n";
		echo "var PromedVerDate = '{$version['date']}'\n";
		echo "var PromedVer = '{$version['version']}" . ((defined('USE_POSTGRESQL') && USE_POSTGRESQL) ? " (PostgreSQL)" : "") . "'" . ";\n";
	}
}
?>