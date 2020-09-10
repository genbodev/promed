<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Promed - основной контроллер, проверка логина, загрузка необходимых скриптов и стилей
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       SWAN Developers
 * @version      ?
 */
class Promed extends swController {

	private $cssCacheDir = '/css';
	private $jsCacheDir = '/jscache';
	private $_isFarmacy = false;
	private $_showMainMenu = 2;

	protected $inputRules = array(
		'getJSFile' => array(
			array(
				'field' => 'wnd',
				'label' => 'Файл',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'type',
				'label' => 'Тип загрузки',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'getDependecies',
				'label' => 'Получить зависимости',
				'rules' => '',
				'type' => 'int'
			)
		),
		'loadFiles' => array(
			array(
				'field' => 'group',
				'label' => 'Группа',
				'rules' => '',
				'type' => 'string'
			)
		),
		'saveFile' => array(
			array(
				'field' => 'id',
				'label' => 'id',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'code',
				'label' => 'Файл',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'region',
				'label' => 'Регион',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'group',
				'label' => 'Группа',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'title',
				'label' => 'Название объекта',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'desc',
				'label' => 'Название объекта',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'path',
				'label' => 'Путь',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'available',
				'label' => 'Всегда доступен',
				'rules' => '',
				'type' => 'checkbox',
				'default' => 0
			)
		),
        'getMSFList' => array(
            array(
                'field' => 'MedStaffFact_id',
                'label' => 'Идентификатор врача',
                'rules' => '',
                'type' => 'id'
            ),
            array(
                'field' => 'MedService_id',
                'label' => 'Идентификатор врача',
                'rules' => '',
                'type' => 'id'
            )
        )

	);
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		//перенесено в методы
		//$this->load->database();
	}
	/**
	 * Index
	 */
	function Index() {
		$this->load->database();
		global $_USER;
		$groups = explode('|', $_SESSION['groups']);

		if ( !isset($_SESSION['login']) ) {
			session_destroy();
			header("Status: 301");
			header("Location: /?c=main&from=promed");
		}
		else {
			// Если залогинены, то сохраняем данные о пользователе в глобальной переменной $_USER
			If (isset($_SESSION['login'])) {
				$_USER = pmAuthUser::find($_SESSION['login']);
			}

			$this->load->helper('Main');
			$options = @unserialize($_USER->settings);
			//Если установлен параметр setArm, значит требуется переключение между версиями ExtJS
			$defaultARM_params = isset($_SESSION['SetARM_id'])?explode('_', $_SESSION['SetARM_id']):array();
			//$dbl['ARMType'].'_'.$dbl['Lpu_id'].'_'.$dbl['MedStaffFact_id'].'_'.$dbl['LpuSection_id'].'_'.$dbl['MedService_id']
			$_defaultARM_params = array();
			if (is_array($defaultARM_params)&&  (sizeof($defaultARM_params)==6)) {
				$_defaultARM_params['ARMType'] = $defaultARM_params[0];
				$_defaultARM_params['Org_id'] = $defaultARM_params[1];
				$_defaultARM_params['MedStaffFact_id'] = $defaultARM_params[2];
				$_defaultARM_params['LpuSection_id'] = $defaultARM_params[3];
				$_defaultARM_params['LpuSectionProfile_id'] = $defaultARM_params[4];
				$_defaultARM_params['MedService_id'] = $defaultARM_params[5];
				$defaultARM_params = $_defaultARM_params; unset($_defaultARM_params);
			}
			$defaultARM = mb_strtolower((isset($defaultARM_params['ARMType']))?$defaultARM_params['ARMType']:(is_array($options) && array_key_exists('defaultARM', $options) && !empty($options['defaultARM']['ARMType']) ? $options['defaultARM']['ARMType'] : ''));
			// Определяем кто именно залогинился - аптека или ЛПУ - хотя тут можно прямо признак в сессию писать... но пока не важно
			$this->_isFarmacy = (isset($_SESSION['OrgFarmacy_id']));
			if (!empty($_GET['getwnd'])) {
				$defaultARM = 'smpadmin';
				$_SESSION['getwnd'] = $_GET['getwnd'];
			} else {
				$_SESSION['getwnd'] = '';
			}

			if (!empty($_GET['cccid'])) {
				$_SESSION['cccid'] = $_GET['cccid'];
			} else {
				$_SESSION['cccid'] = '';
			}
			if(!empty($_GET['showTop']))
			{
				$_SESSION['showTop'] = $_GET['showTop'];
			}
			else
			{
				$_SESSION['showTop'] = 2;
			}

			if (!empty($_GET['act'])) {
				$_SESSION['act'] = $_GET['act'];
			} else {
				$_SESSION['act'] = '';
			}

			//Обновляется расчет прав доступа при перезагрузке промеда
			$this->load->model('AccessRights_model', 'armodel');
			$_SESSION['access_rights'] = $this->armodel->getAccessRightsForUser(array(
				'pmUser_id' => $_SESSION['pmuser_id'], 'MedPersonal_id' => $_SESSION['medpersonal_id'], 'Lpus' => array($_SESSION['lpu_id']), 'UserGroups' => explode('|', $_SESSION['groups'])
			));

			//TODO если по умолчанию идем на арм, который на extjs4, то грузим 4-ку

			$this->load->model("User_model", "User_model");
			$arms = $this->User_model->getARMList();
			//Первичный приоритет загрузки имеет мобильный арм, далее ext4, далее ext2

			$IsMainServer = $this->config->item('IsMainServer');
			if ($IsMainServer === true && !empty($defaultARM) && in_array($defaultARM, array('smpadmin', 'smpdispatchcall', 'smpdispatchdirect', 'smpdispatchstation', 'smpheadduty', 'smpheadbrig', 'smpheaddoctor', 'zmk'))) {
				$defaultARM = null; // нет доступа к таким АРМ.
			}

			$IsLocalSMP = $this->config->item('IsLocalSMP');
			if ($IsLocalSMP === true) {
				$_SESSION['client'] = 'ext4';
			} else {
				$_SESSION['client'] = ((isMobileAgent()) && ($_USER->havingGroup('smpheadbrig'))) ? 'mobile' : ((isset($arms["$defaultARM"])) ? $arms["$defaultARM"]['client'] : 'ext2');
			}

			//$_SESSION['SetARM_id'] = 'smpheadduty_10011393___77';
			switch ($_SESSION['client']) {
				case 'ext4':
					$data = Array("css_files" => $this->GetCSS4Files(), "js_files" => $this->GetJS4Files());
					$PROMED_PAGE_TITLE = $this->config->item('PROMED_PAGE_TITLE');
					$data['promed_page_title'] = (!empty($PROMED_PAGE_TITLE) ? $PROMED_PAGE_TITLE : 'РИАМС ПроМед');
					$this->load->view("promed_view_4ext", $data);
					break;
				case 'ext2':
				case 'ext6':
				case 'ext6only':
					$data = Array("css_files" => $this->GetCSSFiles(), "js_files" => $this->GetJSFiles());
					$version = getPromedVersion();
					$data['Revision'] = $version['commit'];
					$data['RevDate'] = $version['date'];
					$data['PromedVer'] = $version['version'] . ((defined('USE_POSTGRESQL') && USE_POSTGRESQL) ? " (PostgreSQL)" : "");
					$data['PromedCopyright'] = $this->config->item('PROMED_COPYRIGHT');
					$data['promed_page'] = $this->config->item('PROMED_PAGE_NAME');
					$PROMED_PAGE_TITLE = $this->config->item('PROMED_PAGE_TITLE');
					$data['promed_page_title'] = (!empty($PROMED_PAGE_TITLE) ? $PROMED_PAGE_TITLE : 'РИАМС ПроМед');
					if ( $this->_isFarmacy ) {
						$this->load->view("farmacy_view", $data);
					}
					else {
						$this->load->view("promed_view", $data);
					}
					break;
				case 'mobile':
					$this->load->view('mobilebrig_view');
					header("Location: /?c=MobileBrig");
				break;
				default:

					break;
			}
		}
	}

	/**
	 * Устанавливает в сессию запрос на загрузку АРМа.
	 * Используется при переключении между версиями ExtJS
	 */
	function loadArm() {
		if (isset($_REQUEST['ARM_id'])) {
			$_SESSION['SetARM_id'] = $_REQUEST['ARM_id'];
		}
		usleep(1000000);//refs #104218, параметр SetARM_id не успевает записаться
		header('Location: /?c=promed');
	}

	/**
	 * Создает и возвращает меню в js
	 * @return array
	 */
	/*
	function getUserInfo() {
		return 	'<b>Информация о пользователе</b><br/>'.
				'Имя : '.toAnsi(ArrayVal($_SESSION,'user')).'<br/>'.
				'E-mail : '.ArrayVal($_SESSION,'email').'<br/>'.
				'Описание : '.toAnsi(ArrayVal($_SESSION,'desc')).'<br/>'.
				'ЛПУ : '.toAnsi(ArrayVal($_SESSION,'lpu_name'));
	}
	*/

	/**
	 * Создает и возвращает меню в js
	 * @return array
	 */
	function getMenu() {
		/**
		 * getMenuRec
		 */
		function getMenuRec($menu, $lvl, &$roles) {
			$str = '';
			$i = 0;
			$separator = false;
			foreach ($menu as $k=>$v) {
				if (is_array($v)) {
					if (isset($v['action'])) {
						if ((count($roles)>0) && (in_array($v['action'], $roles))) {
							$str .= "sw.Promed.Actions.".$v['action'].",";
							$separator = false;
							$i++;
						} elseif (count($roles)==0) {
							// Если роли нет, то меню доступно
							$str .= "sw.Promed.Actions.".$v['action'].",";
							$separator = false;
							$i++;
						}
					} elseif (isset($v['type']) && isset($v['text']) && (($v['type']=='spacer') || ($v['text']==' '))) {
						$str .= "{xtype : 'tbfill'},";
						$separator = false;
					} elseif (isset($v['type']) && isset($v['text']) && (($v['type']=='separator') || ($v['text']=='-'))) {
						if (($i>0) && (!$separator)) {
							$str .= "'-',";
							$separator = true;
						}
					} else {
						$strec = "{";
						if (!is_numeric($k)) {
							$strec .= "id: '$k',";
						}
						if (isset($v['title']))
							$strec .= "title: '".$v['title']."',";
						if (isset($v['text']))
							$strec .= "text: '".$v['text']."',";
						if (isset($v['iconCls']))
							$strec .= "iconCls: '".$v['iconCls']."',";
						if (isset($v['hidden']))
							$strec .= "hidden: '".$v['hidden']."',";

						if (isset($v['items'])) {
							$strec .= "items:[";
							$m = getMenuRec($v['items'], $lvl+1, $roles);
							$strec .= $m['data'];
							$strec .= "]";
						}
						if (isset($v['menu'])) {
							if (isset($v['this']) && isset($v['menuName']) && ($v['this']=='client')) {
								$strec .= "menu:this.".$v['menuName'];
							} else {
								$strec .= "menu:new Ext.menu.Menu({items:[";
								$m = getMenuRec($v['menu'], $lvl+1, $roles);
								$strec .= $m['data'];
								$strec .= "]})";
							}
						}

						if ($strec[strlen($strec)-1] == ',') {
							$strec = substr($strec, 0, strlen($strec) - 1);
						}

						$strec .= "},";
						if (is_array($m) && ($m['count']>0)) {
							$str .= $strec;
							$separator = false;
						}
					}
				}
			}
			$str = substr($str,0,(!$separator)?-1:-5);
			return array('data'=>$str, 'count'=>$i);
		}
		$this->load->helper('Config');
		$this->load->helper('Options');
		$options = getOptions();

		// выбираем установленное меню
		$menu = filetoarray(APPPATH.'config/menu.php');

		// Определяем тип используемого меню
		$menutype = ($options['appearance']['menu_type']=='ribbon')?'menu_advanced':'menu_normal';

		// определяем группы, в которые входит пользователь
		// для того, чтобы не читать LDAP возьмем их их сессии
		$groups = explode('|', $_SESSION['groups']);

		/*
		$user = pmAuthUser::find($data['user_login']);
		if (!$user)
			DieWithError('Не удалось найти пользователя.');
		else {
			$groups = array();
			foreach (array_values($user->group) as $group) {
				$groups[] = array(
					'Group_id' => $group->id,
					'Group_Name' => $group->name,
					'Group_Desc' => $group->desc
				);
			}
		}
		*/

		// В цикле по всем группам читаем  разрешения на меню и стоим из них общую разрешительную матрицу
		// или читаем из LDAP пользователя // TODO: Реализовать в дальнейщем
		$this->load->database();
		$this->load->model("User_model", "dbmodel");

		$data = array();
		$roles = array();
		for($i=0;$i<count($groups);$i++) {
			$data['Role_id'] = $groups[$i];
			$data['node'] = 'menus';//$menutype;
			$data['level'] = 1;
			$role = pmAuthGroups::loadRole($data['Role_id']);
			if (isset($role[$data['node']])) {
				$roles = mergeRoles($roles, $role[$data['node']]);
			}
		}
		//print_r($roles);
		// перерабатываем массив в простой для дальнейшей фильтрации
		$roles = $this->dbmodel->getSimpleMenuActions($roles);

		// Если нет меню, то по умолчанию показываем что то (?)
		// TODO: Пока показываем все

		// Получаем само меню выбранного типа
		$menu = $menu[$menutype];
		if (count($menu)>0) {
			// Формирование меню с наложением прав на существующее меню
			$getmenu = getMenuRec($menu, 1, $roles);
			$strmenu = $getmenu['data'];
			// накладываем все ограничения и приводим к божескому виду меню

			//print $this->getUserInfo();
			//$strmenu = str_replace('$userinfo', $this->getUserInfo(), $strmenu);
			$strmenu = str_replace('$username', $_SESSION['login'], $strmenu);
			echo 'sw.Promed.menu = ['.$strmenu.'];';
			//print_r($menu);
			return;
		}
		return;
	}

	/**
	 * Выведет содержимое files.php
	 */
	function echoFilesPHP() {
		$path = APPPATH.'config/files.php';
		if (file_exists($path)) {
			echo '<pre>';
			echo file_get_contents($path);
			echo '</pre>';
		} else {
			echo 'files.php не найден';
		}
	}

	/**
	 * Получает и возвращает список файлов с допданными
	 * @return array
	 */
	function loadFiles() {
		$this->load->helper('Config');
		$data = $this->ProcessInputData('loadFiles', true);
		$group = '';

		if ((strlen($data['group'])>0) && ($data['group']!='Все объекты')) {
			$group = $data['group'];
		}
		// выбираем установленное меню
		$files = filetoarray(APPPATH.'config/files.php');
		if (count($files)>0) {
			$f = array();
			$val = array();
			foreach ($files as $key=>$value) {
				if (isset($value['path'])) {
					if ((isset($value['group']) && ($group == $value['group'])) || ($group=='')) {
						$f = $value;
						$f['region'] = 'default';
						$f['code'] = $key;
						$f['id'] = $key.'_'.$f['region'];
						array_walk($f, 'ConvertFromWin1251ToUTF8');
						$val[] = $f;
					}
				} elseif (is_array($value)) {
					// Цикл по регионам
					foreach ($value as $k=>$v) {
						if ((isset($v['group']) && ($group == $v['group'])) || ($group=='')) {
							$f = $v;
							$f['code'] = $key;
							$f['region'] = $k;
							$f['id'] = $key.'_'.$k;
							array_walk($f, 'ConvertFromWin1251ToUTF8');
							$val[] = $f;
						}
					}
				}
			}
			$this->ReturnData($val);
		}
		return false;
	}

	/**
	 * Получает и возвращает список групп файлов (если они есть)
	 * @return array
	 */
	function loadGroupFiles() {
		$this->load->helper('Config');
		$files = filetoarray(APPPATH.'config/files.php');
		if (count($files)>0) {
			$f = array();
			$val = array();
			// Первым циклом собираем данные о группах
			$f[] = 'Все объекты';
			foreach ($files as $key=>$value) {
				if (isset($value['group']) && (!empty($value['group']))) {
					if (!in_array($value['group'], $f)) {
						$f[] = $value['group'];
					}
				} elseif (isset($value[$_SESSION['region']['nick']]['group']) && (!empty($value[$_SESSION['region']['nick']]['group']))) {
					$f[] = $value[$_SESSION['region']['nick']]['group'];
				} elseif (isset($value['default']['group']) && (!empty($value['default']['group']))) {
					$f[] = $value['default']['group'];
				}
			}
			// Вторым циклом приводим их в вид для дерева
			//print_r($f);
			foreach ($f as $k=>$v) {
				$val[] = array('id' => $k, 'text' => $v, 'iconCls' => 'group16', 'leaf' => true);
				array_walk($val[count($val)-1], 'ConvertFromWin1251ToUTF8');
			}
			$this->ReturnData($val);
		}
		return false;
	}

	/**
	 * Сохраняет конфиг файлов-окон, добавляет или изменяет объекты
	 * @return array
	 */
	function saveFile() {
		$this->load->helper('Config');
		$data = $this->ProcessInputData('saveFile', true);
		if ($data) {
			$files = filetoarray(APPPATH.'config/files.php');
			if (count($files)>0) {
				$code = $data['code'];
				if (isset($files[$code])) {
					// Редактирование
					if (($data['region']=='default') && (isset($files[$code]['path']))) {
						$files[$code] = array('path'=>$data['path'], 'group'=>$data['group'], 'title'=>$data['title'], 'desc'=>$data['desc'], 'available'=>$data['available'], 'access'=>'all');
					} else {
						$files[$code][$data['region']] = array('path'=>$data['path'], 'group'=>$data['group'], 'title'=>$data['title'], 'desc'=>$data['desc'], 'available'=>$data['available'], 'access'=>'all');
					}
				} else {
					// Добавление
					$files[$code] = array('path'=>$data['path'], 'group'=>$data['group'], 'title'=>$data['title'], 'desc'=>$data['desc'], 'available'=>$data['available'], 'access'=>'all');
				}
				arraytofile($files, APPPATH.'config/files.php', '/* Файлы */');
			}
		}
		$this->ReturnData(Array('success'=>true, 'Error_Code'=>null, 'Error_Name'=>null));
		return false;
	}

	/**
	 * Возвращает список необходимых к загрузке CSS файлов
	 * @return array Массив CSS файлов для загрузки
	 */
	public function GetCSSFiles() {
		$config = & get_config();

		if ( $config['develop'] ) {
			return $this->_getCSSFilesList(!empty($_SESSION['client']) ? $_SESSION['client'] : 'ext2');
		}

		return $this->_getEngineCSSFilesList($_SESSION['client']);
	}

	/**
	 * Возвращает список необходимых к загрузке CSS файлов для ExtJS4
	 * @return array Массив CSS файлов для загрузки
	 */
	public function GetCSS4Files() {
		return $this->_getCSS4FilesList();
	}

	/**
	 * Проверка что у пользователя есть организация ТОУЗ
	 */
	function checkIsTouzOrg() {
		$this->load->database();
		$this->load->model("Org_model", "Org_model");
		if (is_array($_SESSION['orgs']) && count($_SESSION['orgs']) > 0) {
			return $this->Org_model->checkIsTouzOrg(array('orgs' => $_SESSION['orgs']));
		}

		return false;
	}

	/**
	 * Проверка что у пользователя загружается нептун
	 */
	function checkNeptuneThemeForExtFour(){
		$this->load->helper('Options');
		$opt = getOptions();
		if (isset($opt['defaultARM']['ARMType'])){
			if(in_array($opt['defaultARM']['ARMType'], array(
			'forenbiodprtwithmolgenlabbsmesecretary',
			'forenbiodprtwithmolgenlabbsmehead',
			'forenbiodprtwithmolgenlabbsmeexpert',
			'forenbiodprtwithmolgenlabbsmedprthead',
			//АРМы службы "Судебно-химическое отделение"
			'forenchemdprtbsmesecretary',
			'forenchemdprtbsmehead',
			'forenchemdprtbsmeexpert',
			'forenchemdprtbsmedprthead',
			//АРМы службы "Медико-криминалистическое отделение"
			'medforendprtbsmesecretary',
			'medforendprtbsmehead',
			'medforendprtbsmeexpert',
			'medforendprtbsmedprthead',
			//АРМы службы "Судебно-гистологическое отделение"
			'forenhistdprtbsmesecretary',
			'forenhistdprtbsmehead',
			'forenhistdprtbsmeexpert',
			'forenhistdprtbsmedprthead',
			//АРМы службы "Отдел организационно-методический"
			'organmethdprtbsmesecretary',
			'organmethdprtbsmehead',
			'organmethdprtbsmeexpert',
			'organmethdprtbsmedprthead',
			//АРМы службы "Отдел судебно-медицинской экспертизы трупов с судебно-гистологическим отделением"
			'forenmedcorpsexpdprtbsmesecretary',
			'forenmedcorpsexpdprtbsmehead',
			'forenmedcorpsexpdprtbsmeexpert',
			'forenmedcorpsexpdprtbsmedprthead',
			//АРМы службы "Отдел судебно-медицинской экспертизы потерпевших, обвиняемых и других лиц"
			'forenmedexppersdprtbsmesecretary',
			'forenmedexppersdprtbsmehead',
			'forenmedexppersdprtbsmeexpert',
			'forenmedexppersdprtbsmedprthead',
			//АРМы службы "Отдел комиссионных и комплексных экспертиз"
			'commcomplexpbsmesecretary',
			'commcomplexpbsmehead',
			'commcomplexpbsmeexpert',
			'commcomplexpbsmedprthead',
			//АРМы службы "Районное отделение БСМЭ"
			'forenareadprtbsmesecretary',
			'forenareadprtbsmehead',
			'forenareadprtbsmeexpert',
			'forenareadprtbsmedprthead',
			// АРМ Лаборанта БСМЭ
			'forenmedexppersdprtbsmeexpertassistant',
			)
			)){
				return true;
				//$retArray[] = "/extjs4/resources/ext-theme-neptune/ext-theme-neptune-all.css";
			}
		}
		return false;
	}

	/**
	 * Генератор конфига files6.php
	 */
	public function generateFiles6() {
		if (!isSuperadmin()) {
			$this->ReturnError('Функционал только для суперадмина');
			return false;
		}

		// список папок из Ext6.Loader.setConfig из common.js
		$folders = array(
			'libs4',
			'Frames4',
			'Forms4/Common',
			'Forms4/EMD',
			'Forms4/Base',
			'Forms4/Common/Usluga',
			'Forms4/VideoChat',
			'ux',
			'smp'
		);

		$files = array();
		foreach($folders as $folder) {
			$files = array_merge($files, getFilesList('jscore/' . $folder));
		}
		$files = array_unique($files);

		$files6 = "<?php" . PHP_EOL;
		$files6 .= "/* files for ExtJS 6 */" . PHP_EOL;
		$files6 .= "return array(" . PHP_EOL;
		foreach($files as $file) {
			$filename = mb_substr($file, 7); // без jscore/
			$files6 .= "\t'{$filename}' => Array(" . PHP_EOL;
			$files6 .= "\t\t'path' => '/{$file}'," . PHP_EOL;
			$files6 .= "\t)," . PHP_EOL;
		}
		$files6 .= ");";
		$files6 .= "?>";

		file_put_contents(APPPATH . 'config/files6.php', $files6);

		$this->ReturnData(array('success' => true));
	}

	/**
	 * Возвращает список необходимых к загрузке JS файлов
	 * @return array Массив JS файлов для загрузки
	 */
	public function GetJSFiles() {
		$config = & get_config();

		// todo: Вообще не понял зачем здесь обращение к БД и получение АРМов пользователя - по идее все эти данные можно получать в настройках или как то по другому, надо разобраться
		$this->load->database();
		$this->load->model("User_model", "dbmodel");
		$data = $this->ProcessInputData('getMSFList', true, false);

		if ( $data ) {
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$result = $this->dbmodel->getUserMedStaffFactList($data);
			$result = $this->dbmodel->getArmsForMedStaffFactList($data, $result);

			$ArmsWithoutAccess = array();
			if ( !empty($data['session']['deniedarms']) ) {
				$ArmsWithoutAccess = json_decode($data['session']['deniedarms'], true);
			}
			$response = array();
			foreach ( $result as $oneresp ) {
				$ArmAccess_Params = json_encode(array(
					'l' => !empty($oneresp['Lpu_id'])?$oneresp['Lpu_id']:null,
					'o' => $oneresp['Org_id'],
					'msf' => $oneresp['MedStaffFact_id'],
					'ls' => $oneresp['LpuSection_id'],
					'lsp' => $oneresp['LpuSectionProfile_id'],
					'ms' => $oneresp['MedService_id'],
					'at' => $oneresp['ARMType']
				));

				if ( !in_array($ArmAccess_Params, $ArmsWithoutAccess) ) {
					$response[] = $oneresp;
				}
			}

			if ( count($response) > 0 ) {
				$this->_showMainMenu = 1;

				foreach ( $response as $r ) {
					if ( isset($r['ARMType']) ) {
						if ( !empty($r['ShowMainMenu']) && $r['ShowMainMenu'] == 2 ) {
							$this->_showMainMenu = 2;
						}
					}
				}
			}
		}

		if ( !empty($_SESSION['getwnd']) ) {
			$this->_showMainMenu = 1;
		}

		if ( $config['develop'] ) {
			return $this->_getJSFilesList(!empty($_SESSION['client']) ? $_SESSION['client'] : 'ext2');
		}

		return $this->_getEngineJSFilesList($_SESSION['client']);
	}

	/**
	 * Возвращает список необходимых к загрузке JS файлов ExtJS4
	 * @return array Массив JS файлов для загрузки
	 */
	public function GetJS4Files() {
		return $this->_getJS4FilesList();
	}

	/**
	 * getFilesForDev
	 */
	function getFilesForDev($files) {
		$a = array();
		if (count($files)>0) {
			foreach ($files as $k=>$v) {
				$a[] = $v['path'];
			}
		}
		return $a;
	}

	/**
	 * Получает массив наименований файлов и время последнего изменения файлов, читает их и объединяет в один отдавая объединенный файл
	 */
	protected function _mergeJSFiles($arr) {
		$result = null;
		$config = & get_config();
		// Читаем файлы с диска
		foreach ($arr as $key => $row)
		{
			try
			{
				// если develop == true, то возвращаем неужатый файл, иначе сжимаем и сохраняем на диск
				if ($config['develop']) {
					$file = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $row['path']);
				} else {
					$file = $this->_getMinifiedJS($row['path']);
				}

				if (in_array($key, array('swCmpCallCardNewCloseCardWindow', 'swCmpCallCardNewShortEditWindow', 'swWorkPlaceSMPDispatcherCallWindow'))) {
					// отдаём клиенту хэш
					$hash = getFormHash($key);
					$hashCode = "if (!sw.FormHashes) { sw.FormHashes = new Object(); } sw.FormHashes['{$key}'] = '{$hash}';";
					$file = $hashCode. PHP_EOL .$file;
				}

				$result .= $file;
			}
			catch (Exception $e)
			{
				// на на на, этот файл не берем и молчим :)
			}
		}
		return $result;
	}

	/**
	 * Получает массив наименований файлов, возвращает самую последнюю дату изменения
	 */
	protected function _getTimeModifed($arr) {
		// читаем даты файлов и определяем последнюю дату
		$maxtime = 0;
		foreach ($arr as $row)
		{
			try
			{
				// берем дату изменения файла $_SERVER['DOCUMENT_ROOT'].$row['path']
				$time = filemtime($_SERVER['DOCUMENT_ROOT'].$row['path']);
				if ($maxtime<$time) {
					$maxtime = $time;
				}
			}
			catch (Exception $e)
			{
				// на на на, этот файл не берем и молчим :)
			}
		}
		return $maxtime;
	}

	/**
	 * Возвращает на клиент подготовленный контент (смердженные файлы), с передачей нужных хидеров
	 */
	function returnJSFiles($files, $tojson = false, $withoutHeaders = false) {
		if (!$withoutHeaders) {
			header("Pragma: public");
			header("Cache-Control: public, must-revalidate");
			$time = $this->_getTimeModifed($files);
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $time) . " GMT");
			header('Content-Type: application/javascript');
		}

		// Если файл не был изменен с даты последнего изменения из заголовка
		if (!$withoutHeaders && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $time)  {
			// то пусть браузер использует кеш
			header('HTTP/1.0 304 Not Modified');
		}
		else {
			if ($tojson) {
				echo $this->_mergeJSFiles($files);
			} else {
				echo php2js(array('success' => true, 'data' => $this->_mergeJSFiles($files)));
			}
		}
		return;
	}
	/**
	 * Возвращает пути к файлам драйвера локального хранилища
	 */
	function getLocalStorages($ExtJSVersion) {
		$bp = "/jscore/libs/db";
		$index = "";
		switch($ExtJSVersion) {
			case 4:
				$bp = "/jscore4/lib/db";
				$index = "4";
				break;
			case 6:
				$bp = "/jscore/libs4/db";
				$index = "6";
				break;
		}
		return array(
			'gears'.$index => array(
				'Connection' => $bp.'/gears/Connection.js',
				'GearsConnection' => $bp.'/gears/GearsConnection.js',
				'DBFunctions' => $bp.'/gears/DBFunctions.js',
				'Table' => $bp.'/gears/Table.js',
				'Proxy' => $bp.'/gears/Proxy.js',
				'SQLiteStore' => $bp.'/gears/SQLiteStore.js',
			),
			'indexeddb'.$index => array(
				'IDBDriver' => $bp.'/indexeddb/IDBDriver.js',
				'IDBFunctions' => $bp.'/indexeddb/IDBFunctions.js',
				'IDBProxy' => $bp.'/indexeddb/IDBProxy.js',
				'IDBStore' => $bp.'/indexeddb/IDBStore.js'
			),
			'websqldb'.$index => array(
				'WSDBDriver' => $bp.'/websqldb/WSDBDriver.js',
				'WSDBFunctions' => $bp.'/websqldb/WSDBFunctions.js',
				'WSDBProxy' => $bp.'/websqldb/WSDBProxy.js',
				'WSDBStore' => $bp.'/websqldb/WSDBStore.js'
			),
			'remotedb'.$index => array(
				'RDBDriver' => $bp.'/remotedb/RDBDriver.js',
				'RDBFunctions' => $bp.'/remotedb/RDBFunctions.js',
				'RDBProxy' => $bp.'/remotedb/RDBProxy.js',
				'RDBStore' => $bp.'/remotedb/RDBStore.js'
			)
		);
	}

	/**
	 * Читает и отдает на клиент все JS-файлы (для develop-режима)
	 */
	function getJSAllFiles($d) {
		$config = & get_config();
		if (count($d)>0) {
			$data = $d;
		} else {
			return array(); // Если не передали доппараметры, значит и возвращать ничего не надо
		}

		$files = array();

		// Комбобоксы
		$files['comboboxes'] = array('path'=>"/jscore/libs/swComponentLibComboboxes.js",'access'=>'all');
		$files['comboboxes4'] = array('path'=>"/jscore/libs4/swComponentLibComboboxes.js",'access'=>'all');
		$files['comboboxes_ufa'] = array('path'=>"/jscore/libs/ufa/ufa_swComponentLibComboboxes.js",'access'=>'all');
		$files['parametervalue'] = array('path'=>'/jscore/libs/swParameterValue.js','access'=>'all');

		// Отчеты
		$files['edit_area_full'] = array('path'=>"/jscore/Forms/Report/edit_area_full.js",'access'=>'all');
		$files['records'] = array('path'=>"/jscore/Forms/Report/records.js",'access'=>'all');
		$files['stores'] = array('path'=>"/jscore/Forms/Report/stores.js",'access'=>'all');
		$files['ui'] = array('path'=>"/jscore/Forms/Report/ui.js",'access'=>'all');
		$files['content'] = array('path'=>"/jscore/Forms/Report/content.js",'access'=>'all');
		$files['engine'] = array('path'=>"/jscore/Forms/Report/engine.js",'access'=>'all');

		if ($config['develop']) { // Возвращаем список всех файлов из конфига config/files.php
			$files = array_merge($files, getFile());
			return $this->getFilesForDev($files);
		} else {
			return array();
		}
	}


	/**
	 * Читает и отдает на клиент JS-файл
	 */
	function getJSFile($d = array()) {
		$clientVersion = (!empty($_SESSION['client']) ? $_SESSION['client'] : 'ext2');

		$data = $this->ProcessInputData('getJSFile', false);

		// Проверяем, имеет ли пользователь права для чтения этого файла
		// $roles = isset($_SESSION['setting']['roles']['windows'])?$_SESSION['setting']['roles']['windows']:array();
		$files = array();
		// может прийти несколько наименований файлов, поэтому ищем разделитель
		$row = explode('|', $data['wnd']);
		foreach($row as $wnd) {

			// Проверка на доступ к формам
			/*if (isset($roles[$wnd]) && isset($roles[$wnd]['view']) && ($roles[$wnd]['view'] !== true) && ($_SESSION['client']!=='ext4') ) {
				DieWithError('У вас нет разрешения для открытия данной формы ('.$wnd.')');
				return false;
			}*/

			switch ($wnd) {
				case 'gears': case 'indexeddb': case 'websqldb': case 'remotedb':
					$storages = $this->getLocalStorages(2);
					// Драйвер для локального хранилища
					if ( isset($storages[$wnd]) ) {
						foreach($storages[$wnd] as $filename => $filepath) {
							$files[$filename] = array('path' => $filepath, 'access'=>'all');
						}
					}
					break;
				case 'gears4': case 'indexeddb4': case 'websqldb4': case 'remotedb4':
					$storages = $this->getLocalStorages(4);
					// Драйвер для локального хранилища
					if ( isset($storages[$wnd]) ) {
						foreach($storages[$wnd] as $filename => $filepath) {
							$files[$filename] = array('path' => $filepath, 'access'=>'all');
						}
					}
					break;
				case 'remotedb6':
					$storages = $this->getLocalStorages(6);
					// Драйвер для локального хранилища
					if ( isset($storages[$wnd]) ) {
						foreach($storages[$wnd] as $filename => $filepath) {
							$files[$filename] = array('path' => $filepath, 'access'=>'all');
						}
					}
					break;
				case 'comboboxes':
					// получение комбобоксов
					if ($clientVersion != 'ext6only') {
						$files['comboboxes'] = array('path' => "/jscore/libs/swComponentLibComboboxes.js", 'access' => 'all');
					}
					$files['comboboxes4'] = array('path'=>"/jscore/libs4/swComponentLibComboboxes.js",'access'=>'all');
					if ($clientVersion != 'ext6only') {
						$files['comboboxes_ufa'] = array('path' => "/jscore/libs/ufa/ufa_swComponentLibComboboxes.js", 'access' => 'all');
						$files['parametervalue'] = array('path' => '/jscore/libs/swParameterValue.js', 'access' => 'all');
					}
				case 'promed':
					// для нового варианта, который возможно уже и не будет использоваться
					// todo: С учетом изменений возврата нужно будет переделать, пока просто закомментировал
					/*
					$promed = array();
					$promed['comboboxes'] = array('path'=>"/jscore/libs/swComponentLibComboboxes.js",'access'=>'all');
					$promed['actions'] = array('path'=>"/jscore/actions.js",'access'=>'all');
					$promed['parametervalue'] = array('path'=>'/jscore/libs/swParameterValue.js','access'=>'all');
					//$promed['menu'] = array('path'=>"/?c=promed&m=getMenu",'access'=>'all');
					// объединяем контент и меню.
					// todo: Возврат файлов и меню надо будет разьединить для поддержки кеширования браузером, когда этот функционал потребуется

					echo $this->_mergeJSFiles($promed);
					echo $this->getMenu();
					return true;*/
					break;
				case 'reports':
					// движок отчетов
					// Editor for SQL with syntax highlighting
					$files['edit_area_full'] = array('path'=>"/jscore/Forms/Report/edit_area_full.js",'access'=>'all');
					$files['records'] = array('path'=>"/jscore/Forms/Report/records.js",'access'=>'all');
					$files['stores'] = array('path'=>"/jscore/Forms/Report/stores.js",'access'=>'all');
					$files['ui'] = array('path'=>"/jscore/Forms/Report/ui.js",'access'=>'all');
					$files['content'] = array('path'=>"/jscore/Forms/Report/content.js",'access'=>'all');
					$files['engine'] = array('path'=>"/jscore/Forms/Report/engine.js",'access'=>'all');
					break;
				default:
					// любой файл из files.php
					if (!empty($data['type']) && $data['type'] == 'extjs6') {
						$jsfile = getFile6($wnd);
					} else {
						$jsfile = getFile($wnd);
					}
					if (isset($jsfile)) {
						if ($jsfile === false) {
							DieWithError('Не найдено описание файла '.$wnd);
							return false;
						}

						if (!empty($data['getDependecies'])) {
							if (!empty($jsfile['dependencies'])) {
								foreach($jsfile['dependencies'] as $file) {
									$files[$file] = array(
										'path' => $file
									);
								}
							}
							$this->returnJSFiles($files, true, true);
							return;
						} else {
							$files[$wnd] = $jsfile;
							if (isset($jsfile['linked_files'])) {
								// Загружаем дополнительные файлы, если есть
								$allfiles = getFile();
								foreach ($jsfile['linked_files'] as $file) {
									$files[$file] = $allfiles[$file];
								}
							}
						}
					}
					break;
			}
		}
		$this->returnJSFiles($files, true);
	}


	/**
	 * Получаем имя файла и отдаёт его минимизированное содержимое или ссылку на файл
	 * Данные берутся из кэша
	 * @param $file Имя JS файла с путём
	 * @return Минимизированное содержимое JS файла
	 */
	protected function _getMinifiedJS($file, $islink = false) {

		// На всякий случай определяем константу снова, если она не была определена
		if (!defined('JSCACHE_PATH')) {
			define('JSCACHE_PATH', $_SERVER['DOCUMENT_ROOT'].$this->jsCacheDir);
		}

		// Берём дату изменения скрипта
		$ts = filemtime($_SERVER['DOCUMENT_ROOT'].$file);
		// Ссылка на скрипт
		$link = JSCACHE_PATH.$file.$ts;

		//Проверяем есть ли файл в кэше с такой датой изменения
		if (file_exists($link)) {
			if (!$islink) { // если есть и нужно вернуть не ссылку, то просто отдаём его содержимое
				$contents = file_get_contents($link);
			}
		} else {
			//если нет, то берем содержимое исходного js файла, минимизируем
			//записываем в кэш и отдаём. Из кэша удаляем старый кэш файла, если он есть
			$contents = file_get_contents($_SERVER['DOCUMENT_ROOT'].$file);
			$this->load->library('jsmin');
			// Берем минимизированное содержимое
			$contents = $this->jsmin->minify($contents);

			// Получаем путь до кэшированного файла
			$path_parts = pathinfo($link);
			$dir = $path_parts['dirname'];
			if (!is_dir($dir)) {
				//если папка еще не создана то создаём
				mkdir($dir, 0777, true);
			}

			// Ищем и удаляем старые кэши данного файла
			$files = sdir($dir, basename(JSCACHE_PATH.$file).'*');
			foreach($files as $old_file) {
				unlink($dir.'/'.$old_file);
			}

			// Записываем в кэш новую версию минимизированного содержимое
			file_put_contents($link, $contents);
		}
		if ($islink) {
			return $this->jsCacheDir.$file.$ts;
		} else {
			return $contents;
		}

	}

	/**
	 * Получение массива с CSS-файлами
	 */
	protected function _getCSSFilesList($clientVersion = 'ext2') {
		$retArray = array();

		//<!-- основные стили -->
		$retArray[] = "/extjs/resources/css/ext-all.css";
		// Загружаем выбранную тему
		$this->load->helper('Options');
		$opt = getOptions();
		if (isset($opt['appearance']['user_theme'])
			&& file_exists("css/themes/".$opt['appearance']['user_theme']."/xtheme.css")
		) {
			$retArray[] = "/css/themes/".$opt['appearance']['user_theme']."/xtheme.css";
		}
		else {
			$retArray[] = "/css/themes/blue/xtheme.css";
		}

		//Добавление иконок и правки
		$retArray[] = "/css/form.css";
		//<!-- Классы иконок -->
		$retArray[] = "/css/iconcls.css";
		//<!-- Классы сообщений -->
		$retArray[] = "/css/messages.css";
		//<!-- Классы грида -->
		$retArray[] = "/css/grid.css";
		//<!-- Классы панели -->
		$retArray[] = "/css/panel.css";
		//<!-- Классы панели ЭМК -->
		$retArray[] = "/css/emk.css";
		$retArray[] = "/css/ext6_emk.css";
		//<!-- Классы панели журнала событий -->
		$retArray[] = "/css/evnjournal.css";
		//<!-- Стили для элемента tree -->
		$retArray[] = "/css/tree.css";
		//<!-- стили для компонента daterangepicker -->
		$retArray[] = "/css/daterangepicker.css";
		//<!-- стили для расписания из ЭР -->
		$retArray[] = "/css/er.css";
		//<!-- стили для таскбара -->
		if (!empty($clientVersion) && in_array($clientVersion, array('ext6', 'ext6only'))) {
			$retArray[] = "/css/ext6_taskbar.css";
		} else {
			$retArray[] = "/css/taskbar.css";
		}
		//<!-- стили для спиннера -->
		$retArray[] = "/css/spinner.css";
		//<!-- стили для мультиселекта -->
		$retArray[] = "/css/ext.ux.andrie.select.css";
		//<!-- стили для календаря -->
		$retArray[] = "/css/extensible-all.css";
		//<!-- Классы для мастера выписки направлений -->
		$retArray[] = "/css/directionmaster.css";
		//<!-- стили для компонента uidesigner -->
		//$retArray[] = "/uidesigner/css/Ext.ux.guid.plugin.Designer.css";
		// ExtJS 6 sandbox
		$retArray[] = "/extjs6/classic/theme-triton-sandbox/resources/theme-triton-all.css";
		$retArray[] = "/extjs6/packages/charts/modern/modern-triton-sandbox/resources/charts-all-debug.css";
		//<!-- Переназначение и правки стилей фреймворка -->
		$retArray[] = "/css/customext.css";
		//<!-- стили для констркутора Анкет -->
		$retArray[] = "/css/constructorWorksheet.css";

		$retArray[] = "/css/increasedSize.css";
		if (!empty($clientVersion) && in_array($clientVersion, array('ext6', 'ext6only'))) {
			$retArray[] = "/css/ext6_customext.css";
		}

		$retArray[] = "/css/jq-hint.css";

		$retArray[] = "/css/js-barcode.css";

		return $retArray;
	}

	/**
	 * Получение массива с CSS-файлами (Ext4)
	 */
	protected function _getCSS4FilesList() {
		$retArray = array();

		$retArray[] = "/extjs4/resources/css/ext-all.css";
		//наши стили
		if($this->checkNeptuneThemeForExtFour()){
			$retArray[] = "/extjs4/resources/ext-theme-neptune/ext-theme-neptune-all.css";
		}
		$retArray[] = "/extjs4/resources/css/custom.css";
		$retArray[] = "/extjs4/resources/css/custom-icons.css";
		$retArray[] = "/css/iconcls.css";
		$retArray[] = "/css/leaflet.css";
		$retArray[] = "/css/wialon_track_player.css";

		return $retArray;
	}

	/**
	 * Получение массива с JS-файлами
	 */
	protected function _getJSFilesList($clientVersion = 'ext2') {
		$config = & get_config();
		$retArray = Array();

		$retArray[] = "/?c=JsConstants";
		// дебагер
		$retArray[] = "/jscore/libs/msiefix.js";
		if ($config['develop']) {
			$retArray[] = "/jscore/libs/prettyprint.js";
		}
		//<!-- Центральные скрипты -->
		if ($config['develop']) {
			if ($clientVersion != 'ext6only') {
				$retArray[] = "/extjs/adapter/ext/ext-base.js"; // Странно, но почему-то в 2.2.1 нет отладочной версии
				$retArray[] = "/extjs/ext-all-debug.js";
			}
			$retArray[] = '/extjs6/ext-all-sandbox-debug.js';
			$retArray[] = '/extjs6/packages/charts/classic/charts-debug.js';
		} else {
			if ($clientVersion != 'ext6only') {
				$retArray[] = "/extjs/adapter/ext/ext-base.js";
				$retArray[] = "/extjs/ext-all.js";
			}
			$retArray[] = '/extjs6/ext-all-sandbox.js';
			$retArray[] = '/extjs6/packages/charts/classic/charts.js';
		}

		$retArray[] = '/extjs6/classic/locale/locale-ru-sandbox.js';

		if ($clientVersion != 'ext6only') {
			$retArray[] = "/extjs/source/locale/ext-lang-ru.js";
		}

		if ($clientVersion != 'ext6only') {
			$retArray[] = "/jscore/PromedInit.js";
		} else {
			$retArray[] = "/jscore/ext6PromedInit.js";
		}

		if ($clientVersion != 'ext6only') {
			// общий файл для определения доступности Gears, IndexedDB и WebSql (возможно)
			$retArray[] = "/jscore/libs/db/localdb.js";
			$retArray[] = "/jscore/libs/db/storage.js";
		} else {
			// общий файл для определения доступности Gears, IndexedDB и WebSql (возможно)
			$retArray[] = "/jscore/libs4/db/localdb.js";
			$retArray[] = "/jscore/libs4/db/storage.js";
		}

		// Алиасы для окон для ExtJS 6
		$retArray[] = "/jscore/windows-alias.js";

		//<!-- Функции -->
		$retArray[] = "/jscore/libs/swFunctions.js";

		//<!-- Функции ExtJS 6 -->
		$retArray[] = "/jscore/libs4/swFunctions.js";
		$retArray[] = "/jscore/libs4/Ext4.ux.GridPrinter.js";
		$retArray[] = "/jscore/libs4/Ext4.ux.InputTextMask.js";
		$retArray[] = "/jscore/libs4/Ext6.ux.GridHeaderFilters.js";
		$retArray[] = "/jscore/libs4/extensible-core-debug.js";
		$retArray[] = "/jscore/libs4/calendar-debug.js";
		$retArray[] = "/jscore/libs4/ext.ux.daterangepicker.js";
		$retArray[] = "/jscore/libs4/Ext6.ux.Translit.js";
		$retArray[] = "/jscore/libs4/Ext6.ux.FieldReplicator.js";
		$retArray[] = "/jscore/libs4/Ext6.ux.PanelReplicator.js";

		if ($clientVersion != 'ext6only') {
			//Региональный набор функций для клиентской стророны
			$retArray[] = "/jscore/libs/ufa/ufa_swFunctions.js";

			$retArray[] = "/jscore/libs/swRegFunctions.js";
			$retArray[] = "/jscore/libs/swFarmacyFunctions.js";
		}

		//<!-- Объекты -->
		$retArray[] = "/jscore/libs/swObjects.js";

		if ($clientVersion != 'ext6only') {
			//<!-- Переопределения методов в базовых классах -->
			$retArray[] = "/jscore/libs/overrides.js";
		}

		$retArray[] = "/jscore/overrides.js";

		$retArray[] = "/jscore/libs4/overrides.js";

		// Пути до контроллеров на сервере
		$retArray[] = "/jscore/controllers.js";

		// Русские текстовые константы
		$lang = (isset($_SESSION['language']))?$_SESSION['language']:'ru';

		// todo: Сначала всегда будем загружать русский, пока языки не будут полностью заполнены
		if ($lang != 'ru') {
			$retArray[] = "/jscore/locale/ru.js";
			$retArray[] = "/jscore/locale/ru/promed.js";
		}
		// Загружаем перевод выбранного языка
		$retArray[] = "/jscore/locale/".$lang.".js";
		// Оба языковых файла на время перехода к функции langs
		$retArray[] = "/jscore/locale/".$lang."/promed.js";
		$retArray[] = "/jscore/locale/".$lang."/promed_new.js";

		//<!-- Константы с начальными значениями tabIndex -->
		$retArray[] = "/jscore/tabindexlist.js";

		// таскбар
		if (!empty($clientVersion) && in_array($clientVersion, array('ext6', 'ext6only'))) {
			$retArray[] = "/jscore/libs4/Ext6.ux.TaskBar.js";
		} else {
			$retArray[] = "/jscore/libs/ext.ux.taskbar.js";
		}

		if ($clientVersion != 'ext6only') {
			// moloco - серверный валидатор
			$retArray[] = "/jscore/libs/ext.ux.remotevalidator.js";
		}

		// jquery - для построения графиков
		$retArray[] = "/jscore/libs/flot/jquery.min.js";

		if ($clientVersion != 'ext6only') {
			$retArray[] = "/jscore/libs/flot/jquery.flot.js";
			$retArray[] = "/jscore/libs/flot/jquery.flot.stack.js";
			// jquery - для создания всплывающих подсказок
			$retArray[] = "/jscore/libs/jquery.hint.js";
			// jquery - для запросов jsonp с корректной обработкой ошибок
			$retArray[] = "/jscore/libs/jquery.jsonp.js";
		}

		// Функции подписания
		$retArray[] = "/jscore/libs/cadesplugin_api.js";
		$retArray[] = "/jscore/libs/signservice/date.js";
		$retArray[] = "/jscore/libs/signservice/authApplet.js";
		$retArray[] = "/jscore/libs/signservice/authApi.js";
		$retArray[] = "/jscore/libs/lss-client.js";
		$retArray[] = "/tinymce/tinymce.min.js";

		if ($clientVersion != 'ext6only') {
			// GroupHeaderPlugin - плагин для группировки заголовков грида (для EXT2)
			$retArray[] = "/jscore/libs/groupheader/GroupHeaderPlugin.js";
			// moloco
			$retArray[] = "/jscore/libs/ext.ux.inputtextmask.js";
			$retArray[] = "/jscore/libs/swStoreLib.js";
			$retArray[] = "/jscore/libs/swLpuComboboxes.js";
			$retArray[] = "/jscore/libs/swComponentLibCheckboxGroup.js";
			$retArray[] = "/jscore/libs/swComponentLibDateField.js";
			$retArray[] = "/jscore/libs/swComponentLibTextfields.js";
			$retArray[] = "/jscore/libs/swComponentLibToolbar.js";
			$retArray[] = "/jscore/libs/swComponentLibTabToolbar.js";
			$retArray[] = "/jscore/libs/swComponentLibTriggerFields.js";
			$retArray[] = "/jscore/libs/swComponentLibForms.js";
			$retArray[] = "/jscore/libs/ext.ux.gridprinter.js";
			$retArray[] = "/jscore/libs/ext.ux.gridprintervac.js";
			$retArray[] = "/jscore/libs/ext.ux.grid.plugins.GroupCheckboxSelection.js";
			$retArray[] = "/jscore/libs/ext.ux.grid.RowExpander.js";
			$retArray[] = "/jscore/libs/ext.ux.grid.FilterRow.js";
			$retArray[] = "/jscore/libs/ext.ux.translit.js";
			$retArray[] = "/jscore/libs/ext.ux.translit2en.js";
			$retArray[] = "/jscore/libs/Ext.ux.dd.GridReorderDropTarget.js";
			$retArray[] = "/jscore/libs/ext.ux.grid.RowExpander.js";
			$retArray[] = "/jscore/libs/ext.ux.grid.FilterRow.js";
			$retArray[] = "/jscore/libs/ext.ux.daterangepicker.js";
			$retArray[] = "/jscore/libs/ext.ux.daterangepickeradvanced.js";
			$retArray[] = "/jscore/libs/ext.ux.datepickerrange.js";
			$retArray[] = "/jscore/libs/ext.tree.mouseover.js";
			$retArray[] = "/jscore/libs/ext.ux.messagewindow.js";
			$retArray[] = "/jscore/libs/ext.form.fileuploadfield.js";
			$retArray[] = "/jscore/libs/ext.ux.panelcollapsedtitle.js";
			$retArray[] = "/jscore/libs/ext.ux.celltooltips.js";
			$retArray[] = "/jscore/libs/ext.ux.spinner.js";
			$retArray[] = "/jscore/libs/ext.ux.andrie.select.js";
			$retArray[] = "/jscore/libs/swFormatLib.js";
			$retArray[] = "/jscore/libs/swTimeField.js";
			$retArray[] = "/jscore/libs/swCKEditor.js";
			$retArray[] = "/jscore/libs/RateGrid.js";
			$retArray[] = "/jscore/libs/DispRateGrid.js";
			$retArray[] = "/jscore/libs/htmlentities.js";
			$retArray[] = "/jscore/libs/swFileList.js";
			$retArray[] = "/jscore/libs/swTreeSelection.js";
			$retArray[] = "/jscore/libs/check-tree-tristate.js";
		}

		$retArray[] = "/jscore/libs/swJavaApplets.js";
		$retArray[] = "/jscore/libs/swJavaAppletsES6.js";

		if ($clientVersion != 'ext6only') {
			$retArray[] = "/jscore/libs/Ext.ux.UploadDialog.js"; //
			$retArray[] = "/jscore/libs/ext.ux.menu.storemenu.js"; // динамические меню

			// Библиотека для автоматического расширения полей ввода
			$retArray[] = "/jscore/libs/jquery.autosize.input.js";
			// Плагин для мультибраузерного определения загрузки изображения
			$retArray[] = '/jscore/libs/jquery.bindImageLoad.js';
			// Плагин для инициализации простого статусбара
			$retArray[] = '/jscore/libs/jquery.progressbar.js';
			$retArray[] = "/jscore/libs/swGlossary.js"; //

			//<!-- Базовые формы и фреймы -->
			$retArray[] = "/jscore/Forms/Base/BaseForm.js";
			$retArray[] = "/jscore/Frames/Base/BaseFrame.js";
			$retArray[] = "/jscore/Frames/Base/ViewFrame.js";
			$retArray[] = "/jscore/Frames/Base/BaseJournal.js";
			$retArray[] = "/jscore/Frames/Base/ElectronicQueuePanel.js";
			$retArray[] = "/jscore/Frames/Base/AttributesFrame.js";
			$retArray[] = "/jscore/Frames/Base/BaseFiltersFrame.js";
			$retArray[] = "/jscore/Frames/Base/BaseSearchFiltersFrame.js";
			$retArray[] = "/jscore/Frames/Base/BaseWorkPlaceButtonsPanel.js"; // фрейм c кнопками в АРМе
			$retArray[] = "/jscore/Frames/Base/BaseWorkPlaceFilterPanel.js"; // фрейм c кнопками в АРМе
			$retArray[] = "/jscore/libs/swComponentLibPanels.js";
			$retArray[] = "/jscore/libs/swComponentLibMorbusPanel.js";
			$retArray[] = "/jscore/libs/swComponentLibDiagPanel.js";
			$retArray[] = "/jscore/libs/ChildGridPanel.js";
			$retArray[] = "/jscore/libs/ChildDeathGridPanel.js";
			$retArray[] = "/jscore/Frames/Base/WizardFrame.js";
			$retArray[] = "/jscore/Frames/PersonPregnancyWizardFrame.js";
		}

		//<!-- Базовые формы и фреймы, ExtJS 6 -->
		$retArray[] = "/jscore/Frames4/Base/ElectronicQueuePanel.js";
		$retArray[] = "/jscore/libs4/swComponentLibPanels.js";
		$retArray[] = "/jscore/libs4/swComponentLibFields.js";
		$retArray[] = "/jscore/libs4/Ext6.ux.Translit.js";
		if (!empty($clientVersion) && in_array($clientVersion, array('ext6', 'ext6only'))) {
			$retArray[] = "/jscore/libs4/swMessageBox.js";
		} else {
			$retArray[] = "/jscore/libs/swMessageBox.js";
		}

		if ($clientVersion != 'ext6only') {
			// обработка графики
			$retArray[] = "/jscore/libs/pixastic.custom.js";
			// Вообще это не обязательно, можно отсюда еще убрать
			$retArray[] = "/jscore/Forms/Common/swListSearchWindow.js";

			if (!$this->_isFarmacy) {
				//ЕРМП от Персис
				$retArray[] = "/ermp/Ermp.nocache.js";
				// структура
				$retArray[] = "/jscore/Frames/LpuStructureFrame.js";
				// Журнал событий
				$retArray[] = "/jscore/Frames/EvnJournalFrame.js";
			}


			// Базовый АРМ
			$retArray[] = "/jscore/Forms/Common/swWorkPlaceWindow.js";

			// socket.io теперь для общих системных функций #49651
			$retArray[] = '/jscore/Mobile/socket.io-1.3.5.js';

			if (havingGroup(array('SMPCallDispath', 'SMPDispatchDirections', 'smpheadduty', 'SMPAdmin', 'smpheadbrig', 'SMPMedServiceOper'))) {
				// Базовый АРМ СМ
				$retArray[] = "/jscore/Forms/Common/swWorkPlaceSMPDefaultWindow.js";
			}


			// Тестовый заплыв для dicom-просмотровщика
			$retArray[] = "/jscore/raphaeljs/raphael-min.js";

			$retArray[] = "/ckeditor/ckeditor-min.js";

			//Видесвязь
			$retArray[] = "/jscore/WebRTC/adapter.js";
			$retArray[] = "/jscore/WebRTC/html2canvas.min.js";
			$retArray[] = "/jscore/WebRTC/RecordRTC.js";

			//Базовый класс для 110
			$retArray[] = "/jscore/Forms/Ambulance/swMainCloseCardWindow.js";

			// Библиотека генерации штрихкодов
			$retArray[] = "/jscore/libs/JsBarcode.all.min.js";

			// Библиотека печати в 1 клик для принтеров Zebra
			$retArray[] = "/jscore/libs/BrowserPrint-1.0.4.min.js";
		}

		$retArray[] = "/jscore/common.js";

		if ($clientVersion != 'ext6only') {
			// Amcharts
			$retArray[] = '/jscore/libs/amcharts/amcharts.js';
			$retArray[] = '/jscore/libs/amcharts/serial.js';
			$retArray[] = '/jscore/libs/amcharts/radar.js';
		}


		// Загрузчики справочников
		$retArray[] = "/jscore/loader-init.js";

		if ($clientVersion != 'ext6only') {
			$retArray[] = "/jscore/loader.js";
		} else {
			$retArray[] = "/jscore/ext6loader.js";
		}

		$_SESSION['ArmMenuTitle'] = 2;

		if (!empty($clientVersion) && in_array($clientVersion, array('ext6', 'ext6only'))) {
			if ($clientVersion != 'ext6only') {
				$retArray[] = "/jscore/libs/vacUtils.js";
			}
			$retArray[] = "/jscore/Forms4/Common/NoticeWidget.js"; // виджет оповещений
			$retArray[] = "/jscore/ext6menu.js";
		} else if ($this->_showMainMenu == 1) {
			$retArray[] = "/jscore/libs/vacUtils.js";
			$retArray[] = "/jscore/windowwithoutmenu.js";
			$_SESSION['ArmMenuTitle'] = 1;
		} elseif ($this->_isFarmacy || (isset($_SESSION['orgtype']) && $_SESSION['orgtype']=='reg_dlo')) {
			$retArray[] = "/jscore/farmacy.js";
		} elseif (isLpuTariffSpec()) {
			$retArray[] = "/jscore/lputarifspec.js";
		} elseif (onlyCadrUserView()) {
			$retArray[] = "/jscore/" . ($_SESSION['medpersonal_id'] > 0 ? "promed.js" : "staff.js");
		} elseif (havingGroup('OuzSpec') && $this->checkIsTouzOrg()) {
			// если есть группа Специалист ОУЗ + на аккаунте добавлена хотя бы одна организация с типом "ТОУЗ"
			$retArray[] = "/jscore/touzspec.js";
		} elseif (havingGroup('OuzSpec') || havingGroup('PmuSpec')) {
			$retArray[] = "/jscore/ouzspec.js";
		} elseif (havingGroup('OuzSpecMPC')) {
			$retArray[] = "/jscore/ouzspecmpc.js";
		} else {
			$retArray[] = "/jscore/libs/vacUtils.js";
			$retArray[] = "/jscore/promed.js";
		}

		return $retArray;
	}

	/**
	 * Получение массива с JS-файлами (Ext4)
	 */
	protected function _getJS4FilesList() {
		$config = & get_config();
		$retArray = array();

		$retArray[] = "/?c=JsConstants";

		//<!-- Центральные скрипты -->
		if ($config['develop']) {
			$retArray[] = "/extjs4/ext-all-debug.js";
		}
		else {
			$retArray[] = "/extjs4/ext-all.js";
		}

		$retArray[] = "/jscore4/PromedInit.js";

		//<!-- Переопределения методов в базовых классах -->
		$retArray[] = "/jscore4/lib/overrides.js";

		// Пути до контроллеров на сервере
		$retArray[] = "/jscore4/controllers.js";

		//<!-- Функции -->
		$retArray[] = "/jscore4/lib/swFunctions.js";

		//глобальные экшены
		$retArray[] = "/jscore4/actions.js";

		// общий файл для определения доступности Gears, IndexedDB и WebSql (возможно)
		$retArray[] = "/jscore4/lib/db/localdb.js";
		$retArray[] = "/jscore4/lib/db/storage.js";

		//плагины
		$retArray[] = "/jscore4/lib/Ux.InputTextMask.js";
		$retArray[] = "/jscore4/lib/Ux.Translit.js";
		$retArray[] = "/jscore4/lib/Ux.DateTools.js";
		$retArray[] = "/jscore4/lib/Ux.ScrollableButtonPanel.js";
		$retArray[] = "/jscore4/lib/Printer.js";

		// Библиотеки компонентов
		$retArray[] = "/jscore4/lib/swComponentLibPanels.js";
		$retArray[] = "/jscore4/lib/swComponentLibFields.js";
		$retArray[] = "/jscore4/lib/BaseForm.js";
		$retArray[] = "/jscore4/lib/swWindows.js";
		$retArray[] = "/jscore4/lib/swMessageBox.js";
		$retArray[] = "/jscore4/lib/swJavaApplets.js";
		$retArray[] = "/jscore4/lib/swFormatLib.js";
		$retArray[] = "/jscore4/lib/swStoreLib.js";
		$retArray[] = "/jscore4/lib/swComponentLibComboboxes.js";
		$retArray[] = "/jscore4/lib/swComponentLibGridPanelTwoStore.js";
		$retArray[] = "/jscore4/lib/swComponentLibTextfields.js";
		$retArray[] = "/jscore4/lib/swComponentLibDateField.js";
		$retArray[] = "/jscore4/lib/CmpCallsList.js";
		$retArray[] = "/jscore4/lib/CmpCallsListExpertMark.js";
		$retArray[] = "/jscore4/lib/CmpServedCallsList.js";
		$retArray[] = "/jscore4/lib/CmpCalls112List.js";
		$retArray[] = "/jscore4/lib/CmpCallsUnderControlList.js";

		// Алиасы для окон для обратной совместимости с наименованиями окон в ExtJS 2.3
		$retArray[] = "/jscore4/windows-alias.js";

		// Русские текстовые константы
		$retArray[] = "/jscore4/locale/ru.js";
		$retArray[] = "/extjs4/locale/ext-lang-ru.js";

		// базовая форма АРМа
		// Добавление всех файлов в функции getJSFile
		// Здесь только общие файлы и библиотеки

		$retArray[] = "/jscore4/promed.js";
		$retArray[] = "/jscore4/loader-init.js";
		$retArray[] = "/jscore4/loader.js";

		$retArray[] = '/jscore/Mobile/socket.io-1.3.5.js';
		$retArray[] = "/jscore4/lib/jquery/jquery.js";
		$retArray[] = "/jscore4/lib/jquery/jquery_easing.js";
		$retArray[] = "/tinymce/tinymce.min.js";

		$retArray[] = "/jscore4/lib/nicEditor/nicEdit.js";

		//библиотеки для работы со звуком
		$retArray[] = "/jscore4/lib/voiceRecorder/mic.js";

		$retArray[] = "/jscore4/lib/leaflet.js";
		$retArray[] = "/jscore4/lib/jquery/jquery-ui.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.ui.touch-punch.min.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.localisation.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.flot.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.flot.navigate.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.flot.fillarea.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.flot.time.js";
		$retArray[] = "/jscore4/lib/jquery/jquery.flot.resize.js";
		$retArray[] = "/jscore4/lib/messages.js";
		$retArray[] = "/jscore4/lib/swWialonTrackPlayer.js";
		$retArray[] = "//apps.wialon.com/plugins/leaflet/webgis/webgis.leaflet.js";

		return $retArray;
	}

	/**
	 * Создание минимизированных движков CSS и JS
	 */
	public function createEngineFiles() {
		$this->createEngineCSS();
		$this->createEngineJS();

		return true;
	}

	/**
	 * Создание минимизированного CSS-движка
	 */
	public function createEngineCSS() {
		set_time_limit(0);

		if ( !isSuperAdmin() ) {
			echo 'Error';
		}
		else {
			$this->_getEngineCSSFilesList('ext2', true);
			echo '<div>ExtJS 2 CSS Engine - done</div>';
			$this->_getEngineCSSFilesList('ext6', true);
			echo '<div>ExtJS 6 CSS Engine - done</div>';
		}

		return true;
	}

	/**
	 * Создание минимизированного JS-движка
	 */
	public function createEngineJS() {
		set_time_limit(0);

		if ( !isSuperAdmin() ) {
			echo 'Error';
		}
		else {
			$this->_getEngineJSFilesList('ext2', true);
			echo '<div>ExtJS 2 JS Engine - done</div>';
			$this->_getEngineJSFilesList('ext6', true);
			echo '<div>ExtJS 6 JS Engine - done</div>';
		}

		return true;
	}

	/**
	 * Процедура создания минимизированного CSS-движка
	 */
	protected function _getEngineCSSFilesList($clientVersion = 'ext2', $forceCreate = false) {
		$retArray = $this->_getCSSFilesList($clientVersion);

		// Формируем engine.css
		$fileNames = array();
		// Получаем последнее время изменения файлов движка
		$engineTime = 0;
		// Переменные
		$engineFile = '/engine';
		$engineExtVerson = '-' . $clientVersion . '-';
		$engineExt = '.css';

		// Список файлов, которые нужно включить в engine, но не нужно минимизировать, поскольку они уже минимизированы
		$listDontMin = array();
		// Список файлов, которые не нужно включать в engine и не нужно сжимать, идут самими первыми в списке
		$listDontEngineFirst = array(
			"/extjs/resources/css/ext-all.css",
			"/extjs6/classic/theme-triton-sandbox/resources/theme-triton-all.css",
			"/extjs6/packages/charts/modern/modern-triton-sandbox/resources/charts-all-debug.css"
		);

		foreach ( $retArray as $filepath ) {
			if ( substr($filepath, -strlen('xtheme.css')) == 'xtheme.css' ) {
				$listDontEngineFirst[] = $filepath;
			}
		}

		// Список файлов, которые не нужно включать в engine, но нужно минимизировать каждый файл в отдельности и после основного движка
		$listDontEngineLast = array();

		foreach ( $retArray as $filepath ) {
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$filepath)) {
				$t = filemtime($_SERVER['DOCUMENT_ROOT'].$filepath);
				if ($t > $engineTime) {
					$engineTime = $t;
				}
			}
		}

		foreach ( $listDontEngineFirst as $filepath ) {
			if ( in_array($filepath, $retArray) ) {
				$fileNames[] = $filepath;
			}
		}

		// Имя файла движка с filetime
		$engineName = $engineFile.$engineExtVerson.$engineTime.$engineExt;
		if (!defined('CSSCACHE_PATH')) {
			define('CSSCACHE_PATH', $_SERVER['DOCUMENT_ROOT'].$this->cssCacheDir);
		}

		// Проверяем, есть ли кэш движка с такой датой изменения
		if ( file_exists(CSSCACHE_PATH.$engineName) && $forceCreate === false ) {
			$fileNames[] = $this->cssCacheDir.$engineName;
		}
		else {
			$this->load->library('textlog', array('file'=>'createEngineFiles_' . date('Y-m-d') . '.log'));
			// если нет, то соберем движок
			$engine = '';
			foreach ( $retArray as $filepath ) {
				if ( file_exists($_SERVER['DOCUMENT_ROOT'].$filepath) ) { // Если такой файл в принципе есть
					$this->textlog->add('file found: ' . $filepath);
					if (!in_array($filepath, $listDontEngineLast) && !in_array($filepath, $listDontEngineFirst)) { //  Если файл не нужно включать в движок, то здесь его обрабатывать не будем
						// читаем файл
						$content = file_get_contents($_SERVER['DOCUMENT_ROOT'].$filepath);

						// собираем в один
						$engine = $engine."\n\r/* include ".$filepath." */\n\r\n\r".$content;
					} else {
						$this->textlog->add('file not included in engine: ' . $filepath);
					}
				} else {
					$this->textlog->add('file not found: ' . $filepath);
				}
			}

			// Получаем путь до кэшированного файла
			$path_parts = pathinfo(CSSCACHE_PATH.$engineName);
			$dir = $path_parts['dirname'];
			if (!is_dir($dir)) {
				//если папка еще не создана то создаём
				mkdir($dir, 0777, true);
			}

			// Ищем и удаляем старые кэши данного файла
			$files = sdir($dir, basename(CSSCACHE_PATH.$engineFile.$engineExtVerson).'*');
			foreach($files as $old_file) {
				unlink($dir.'/'.$old_file);
			}
			// Записываем в кэш новую версию минимизированного содержимое
			file_put_contents(CSSCACHE_PATH.$engineName, $engine);
			$this->textlog->add('engine created: ' . CSSCACHE_PATH . $engineName);
			$fileNames[] = $this->cssCacheDir.$engineName;
		}

		// по файлам которые не нужно включать в движок, но нужно минимизировать и вывести уже после движка
		foreach ( $listDontEngineLast as $filepath ) {
			if ( in_array($filepath, $retArray) ) {
				$fileNames[] = $filepath;
			}
		}

		return $fileNames;
	}

	/**
	 * Процедура создания минимизированного JS-движка
	 */
	protected function _getEngineJSFilesList($clientVersion = 'ext2', $forceCreate = false) {
		$retArray = $this->_getJSFilesList($clientVersion);

		// Формируем engine.js
		$fileNames = array();
		// Получаем последнее время изменения файлов движка
		$engineTime = 0;
		// Переменные
		$engineFile = '/engine';
		$engineExtVerson = '-' . $clientVersion . '-';
		$engineExt = '.js';

		// Список файлов, которые нужно включить в engine, но не нужно минимизировать, поскольку они уже минимизированы
		$listDontMin = array(
			"/jscore/raphaeljs/raphael-min.js",
			"/ckeditor/ckeditor-min.js",
			"/jscore/libs/jquery.jsonp.js",
			"/extjs6/packages/charts/classic/charts.js"
		);
		// Список файлов, которые не нужно включать в engine и не нужно сжимать, идут самими первыми в списке
		$listDontEngineFirst = array(
			"/extjs/adapter/ext/ext-base.js",
			"/extjs/ext-all.js",
			"/extjs6/ext-all-sandbox.js",
			"/tinymce/tinymce.min.js",
		);
		// Список файлов, которые не нужно включать в engine, но нужно минимизировать каждый файл в отдельности и после основного движка
		$listDontEngineLast = array(
			"/jscore/common.js",
			"/jscore/promed.js",
			"/jscore/windowwithoutmenu.js",
			"/jscore/farmacy.js",
			"/jscore/staff.js",
			"/jscore/lputarifspec.js",
			"/jscore/touzspec.js",
			"/jscore/ouzspec.js",
			"/jscore/ouzspecmpc.js",
			"/jscore/libs/swJavaAppletsES6.js",
			"/jscore/Forms/Common/swWorkPlaceSMPDefaultWindow.js",
		);

		foreach ( $retArray as $filepath ) {
			// И тут же выберем файлы которые, расположены на других серверах или являются динамическими (и ermp/Ermp.nocache.js)
			if (substr($filepath,0,7)  == 'http://' || substr($filepath,0,8)  == 'https://' || strpos($filepath, '/?c=')!==false || $filepath == '/ermp/Ermp.nocache.js') { // js-файл с другого сервера или файл не надо включать в движок
				$fileNames[] = $filepath;
			} else if (file_exists($_SERVER['DOCUMENT_ROOT'].$filepath)) {
				$t = filemtime($_SERVER['DOCUMENT_ROOT'].$filepath);
				if ($t > $engineTime) {
					$engineTime = $t;
				}
			}
		}

		foreach ( $listDontEngineFirst as $filepath ) {
			if ( in_array($filepath, $retArray) ) {
				$fileNames[] = $filepath;
			}
		}

		// Имя файла движка с filetime
		$engineName = $engineFile.$engineExtVerson.$engineTime.$engineExt;
		$this->load->library('jsmin');
		if (!defined('JSCACHE_PATH')) {
			define('JSCACHE_PATH', $_SERVER['DOCUMENT_ROOT'].$this->jsCacheDir);
		}

		// Проверяем, есть ли кэш движка с такой датой изменения
		if ( file_exists(JSCACHE_PATH.$engineName) && $forceCreate === false ) {
			// если есть, то просто отдаём его содержимое
			$fileNames[] = $this->jsCacheDir.$engineName;
		}
		else {
			$this->load->library('textlog', array('file'=>'createEngineFiles_' . date('Y-m-d') . '.log'));
			// если нет, то соберем движок
			$engine = '';
			foreach($retArray as $filepath) {
				if (file_exists($_SERVER['DOCUMENT_ROOT'].$filepath)) { // Если такой файл в принципе есть
					$this->textlog->add('file found: ' . $filepath);
					if (!in_array($filepath, $listDontEngineLast) && !in_array($filepath, $listDontEngineFirst)) { //  Если файл не нужно включать в движок, то здесь его обрабатывать не будем
						// читаем файл
						$content = file_get_contents($_SERVER['DOCUMENT_ROOT'].$filepath);

						if (!in_array($filepath, $listDontMin)) { // Если файл минимизированный, то мы его не минимизируем
							$content = $this->jsmin->minify($content);
						}

						// собираем в один
						$engine = $engine."\n\r/* ".$filepath." */\n\r\n\r".$content;
					} else {
						$this->textlog->add('file not included in engine: ' . $filepath);
					}
				} else {
					$this->textlog->add('file not found: ' . $filepath);
				}
			}

			// Получаем путь до кэшированного файла
			$path_parts = pathinfo(JSCACHE_PATH.$engineName);
			$dir = $path_parts['dirname'];
			if (!is_dir($dir)) {
				//если папка еще не создана то создаём
				mkdir($dir, 0777, true);
			}

			// Ищем и удаляем старые кэши данного файла
			$files = sdir($dir, basename(JSCACHE_PATH.$engineFile.$engineExtVerson).'*');
			foreach($files as $old_file) {
				unlink($dir.'/'.$old_file);
			}
			// Записываем в кэш новую версию минимизированного содержимое
			file_put_contents(JSCACHE_PATH.$engineName, $engine);
			$this->textlog->add('engine created: ' . JSCACHE_PATH . $engineName);
			$fileNames[] = $this->jsCacheDir.$engineName;
		}

		// по файлам которые не нужно включать в движок, но нужно минимизировать и вывести уже после движка
		foreach ( $listDontEngineLast as $filepath ) {
			if ( in_array($filepath, $retArray) ) {
				$fileNames[] = $this->_getMinifiedJS($filepath, true);
			}
		}

		return $fileNames;
	}
}
