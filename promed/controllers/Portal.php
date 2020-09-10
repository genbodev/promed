<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Контроллер портала
 *
 * @copyright    Copyright (c) Swan Ltd.
 * @author       Alexander Arefyev aka Alf (avaref@gmail.com)
 * @version      2011.07
 * 
 * @property Portal_model $Portal_model
 */
class Portal extends SwController {
	var $NeedCheckLogin = false;
	private $data; // Данные для вывода
	public $portal_config; // Конфиг портала
	var $arMonth = [
		1 => "ЯНВ", 2 => "ФЕВ", 3 => "МАР", 4 => "АПР",
		5 => "МАЙ", 6 => "ИЮН", 7 => "ИЮЛ", 8 => "АВГ",
		9 => "СЕН", 10 => "ОКТ", 11 => "НОЯ", 12 => "ДЕК"
	];
	var $dayNames = [1=>'ПН',2=>'ВТ',3=>'СР',4=>'ЧТ',5=>'ПТ',6=>'СБ',7=>'ВС'];

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->load->model('Portal_model');
		$this->load->library('textile');
	}

	/**
	 *	_remap
	 */
	function _remap($method) {
		$this->config->load('portal', TRUE);

		$this->portal_config = $this->config->item('portal');

		$this->Portal_model->cache_time = $this->portal_config['settings']['cache_time'];

		$region = !empty($_SERVER['REGION'])?$_SERVER['REGION']:null;

		if ($region == 'kz') {
			//ПЕРЕНАПРАВЛЕНИЕ НА ДРУГОЙ РЕГИОН ВНУТРИ КАЗАХСТАНА
			$this->load->helper('kz/Region');
			$this->load->helper('url');

			$main_domain = getKzMainDomain();
			$region_url = getKzRegionUrl(!empty($_REQUEST['kz_region_name'])?$_REQUEST['kz_region_name']:$_SERVER['SERVER_NAME']);

			if ($region_url) {
				$parseurl = parse_url($region_url);
				$parseurl = $parseurl['host'];
				setcookie('kz_region_name', $parseurl, time() + 36000, '/', $main_domain);
			} else if (!empty($_COOKIE['kz_region_name'])) {
				$region_url = getKzRegionUrl($_COOKIE['kz_region_name']);
			}

			if ($region_url && $_SERVER['SERVER_NAME'] == $main_domain) {
				redirect($region_url);
			}
		}

		if(isset($_GET['lang']))
			$_COOKIE['lang'] = $_GET['lang'];
		else
			$_COOKIE['lang'] = 'ru';
		
		$this->lang->load('portal', $_COOKIE['lang']);

		$_SESSION['lang'] = $_COOKIE['lang'];
		if(isset($_SERVER['REGION']) && $_SERVER['REGION']!='ufa')
			switch($_SERVER['REGION']){
				case 'kz'	: 
					$this->portal_config['titles']['main_page_2'] = lang('Regionalnaya_analiticheskaya_medicinskaya_sistema'); 
					break;
				case 'msk'	:
					$this->portal_config['titles']['main_title'] = 'ЕМИАС МО';
					$this->portal_config['titles']['main_page_1'] = 'ЕМИАС МО';
					$this->portal_config['titles']['main_page_2'] = lang('Edinaya_medicinskaya_informacionno_analiticheskaya_sistema_moskovskoy_oblasty');
					break;
				default	: $this->portal_config['titles']['main_page_2'] = lang('Regionalnaya_informacionno_analiticheskaya_medicinskaya_sistema'); break;
			}

		if($_COOKIE['lang']=='kz') {
			$this->portal_config['titles']['auth_page_enter'] = lang('Vhod_v_KAZMED_inform');
			$this->portal_config['titles']['main_description'] = lang('Kazmedinform_descr');
		}


		$this->data = new StdClass();
		if ( array_key_exists('k-vrachu', $this->portal_config['products']) ) {
			$this->portal_config['products']['k-vrachu']['title'] = lang('Zapis_k_vrachu');
		}
		$this->portal_config['products']['promed']['url'] = '?c=portal&m=promed&lang='.$_COOKIE['lang'];
		$this->data->titles = $this->portal_config['titles'];
		$this->data->last_upd =lang('Poslednie_izmeneniya');
		$this->data->help_sys =lang('Spravochnaya_sistema');
		$this->data->username_label = $region == 'kz' ? lang('Imya_polzovatelya') : lang('Login');
		$this->data->password_label =lang('Parol');
		$this->data->auth_label = ($_SERVER['REGION']=='kz') ? lang('Vojti_v_sistemu') : lang('Vojti');
		$this->data->authcard_label = ($_SERVER['REGION']=='kz') ? lang('Vhod_po_epc') : lang('Vhod_po_karte');
		$this->data->phones_label_1 =lang('Telefony_slughby');
		$this->data->phones_label_2 =lang('Tekhnicheskoj_podderzhki');
		$this->data->phones_label_3 =lang('Kruglosutochno');
		$this->data->kz_label1 =lang('Video_uroki');
		$this->data->kz_label2 =lang('Vremya_raboti');
		$this->data->bottom_1 =lang('Razrabotka_i_podderzhka');
		$this->data->bottom_2 = lang('TOO_Global information systems');
		$this->data->timeout_msg =lang('Vremya_ozhidaniya_isteklo_Avtorizujtes_v_sisteme_zanovo');
		$this->data->new_pass =lang('Novyj_parol');
		$this->data->repeat_pass =lang('Povtorite_parol');
		if ($method=='index') {
			$this->data->products = '';
			foreach ($this->portal_config['products'] as $name => $product) {
				if ($_SERVER['REGION'] != 'msk' || $_SERVER['REGION'] == 'msk' && $name == 'promed') {
					$product['users_count'] = (in_array(getRegionNick(), ['kz', 'msk']) && in_array($name, ['promed', 'er', 'k-vrachu', 'ias'])) ? $this->Portal_model->getUsersCount($name) : -1;
					if ($name == 'promed') {
						switch ($_SERVER['REGION']) {
							case 'kz':
								$product['description'] = lang('Analiticheskaya_medicinskaya_sistema');
								break;
							case 'msk':
								$product['description'] = lang('');
								break;
							default:
								$product['description'] = lang('Informacionno_analiticheskaya_medicinskaya_sistema');
						}
					}
					if ($name == 'k-vrachu') {
						$product['title'] = lang('Zapis_k_vrachu');
						$product['description'] = lang('Sajt_zapisi_k_vrachu');
					}
					$product['users_descr'] = lang('Polzovatelej_onlajn');

					$region = empty($region) ? ((empty($_SERVER['REGION'])) ? null : $_SERVER['REGION']) : $region;
					if ($region == 'perm' && isset($product['exturl'])) {
						$product['url'] = (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $_SERVER['SERVER_NAME']) && ip2long($_SERVER['SERVER_NAME']) >= ip2long('172.22.99.0') && ip2long($_SERVER['SERVER_NAME']) <= ip2long('172.22.99.255') ? $product['url'] : $product['exturl']);
					}
					$product_view = $this->load->view('portal/product.php', $product, true);
					$this->data->products = $this->data->products . $product_view;
				}
			}
		}
		$this->data->ias_modules = '';
		if (!empty($this->portal_config['ias_modules'])) {
			foreach ($this->portal_config['ias_modules'] as $product) {
				$product_view = $this->load->view('portal/product.php', $product, true);
				$this->data->ias_modules = $this->data->ias_modules . $product_view;
			}
		}

		$this->data->service = '';
		if (!empty($this->portal_config['service'])) {
			foreach ($this->portal_config['service'] as $product) {
				$product_view = $this->load->view('portal/service.php', $product, true);
				$this->data->service = $this->data->service . $product_view;
			}
		}
		
		$this->data->mainTitle = '';
		switch (getRegionNick()) {//#pw-12940
			case 'kareliya':
				$title = 'Региональная информационная система здравоохранения Республики Карелия';
				break;
			case 'perm':
				$title = 'Единая информационная система здравоохранения Пермского края';
				break;
			case 'ufa':
				$title = 'Единая цифровая платформа &#8212; Республиканская медицинская информационно-аналитическая система Республики Башкортостан';
				break;
			case 'yaroslavl':
				$title = 'Сервис «Формирование реестра счетов на оплату медицинской помощи»';
				break;
			case 'penza':
				$title = 'Государственная информационная система в сфере здравоохранения Пензенской области';
				break;
			case 'krym':
				$title = 'Единая Медицинская Информационная Система Здравоохранения Республики Крым';
				break;
			case 'vologda':
				$title = 'Информационная система «Региональная медицинская информационная система Вологодской области»';
				break;
			case 'astra':
				$title = 'Региональная информационно-аналитическая медицинская система Астраханской области';
				break;
			case 'pskov':
				$title = 'Региональная информационно-аналитическая медицинская система Псковской области';
				break;
			default:
				$title = '';
				break;
		}
		$this->data->mainTitle = $title;
		
		$this->data->nav = '';
		if (!empty($this->portal_config['products'])) {
			foreach ($this->portal_config['products'] as $key => $item) {
				if($_SERVER['REGION'] != 'msk' || $_SERVER['REGION'] == 'msk' && $key == 'promed') {
					$nav_item_view = $this->load->view('portal/nav_item.php', $item, true);
					$this->data->nav = $this->data->nav . $nav_item_view;
				}
			}
		}
		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'perm' && isset($this->portal_config['links']['forum_exturl'])) {
			$this->portal_config['links']['forum'] = (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $_SERVER['SERVER_NAME']) && ip2long($_SERVER['SERVER_NAME']) >= ip2long('172.22.99.0') && ip2long($_SERVER['SERVER_NAME']) <= ip2long('172.22.99.255')
				? $this->portal_config['links']['forum'] : $this->portal_config['links']['forum_exturl']);
		}
		$this->data->links = $this->portal_config['links'];

		$this->$method();
	}

	/**
	 * Процедура логина
	 */
	function login() {
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$users = $this->portal_config['users'];

		foreach ($users as $user) {
			if ($username == $user['username'] && $password == $user['password']) {
				$_SESSION['username'] = $username;
				header("Location: /?c=portalAdmin");
				return;
			}
		}

		$_SESSION['login_failed'] = true;

		header("Location: /?c=portalAdmin&m=login");
	}

	/**
	 * Вывод основной страницы
	 */
	function index() {
		if (!in_array(getRegionNick(), ['kz', 'msk'])) {
			header("Location: /?c=portal&m=udp");
			die();
		}

		$news_on_page = $this->portal_config['settings']['news_on_main_page'];
		$body_cut = $this->portal_config['settings']['news_body_cut_lines'];

		$notices = $this->Portal_model->getNotices();
        $seminar = $this->Portal_model->getSeminarNearest();
		$news = $this->Portal_model->getNews(0, $news_on_page);
		$count = $this->Portal_model->getNewsCount();

		$this->data->end = false;
		if (count($news) >= $count) {
			$this->data->end = true;
		}
		if(isset($_GET['lang']))
			$_COOKIE['lang'] = $_GET['lang'];
		else
			$_COOKIE['lang'] = 'ru';

		$this->data->news_entries = '';
		foreach ($news as $news_entry) {
			$news_entry->datetime = ConvertDateFormat($news_entry->datetime, 'd.m.Y - H:i');

			$body_lines = preg_split('/(\r?\n)/', $news_entry->body);

			if (count($body_lines) > $body_cut) {
				$body_lines = array_slice($body_lines, 0, $body_cut);
				$news_entry->cut = true;
			}

			$news_entry->body = implode("\n", $body_lines);
			$news_entry->body = $this->textile->TextileThis($news_entry->body);

			$news_entry_view = $this->load->view('portal/news_entry_old.php', $news_entry, true);
			$this->data->news_entries = $this->data->news_entries . $news_entry_view;
		}

		$this->data->notices = '';
		foreach ($notices as $notice) {
			$notice->body = $this->textile->TextileThis($notice->body);
			$notice_view = $this->load->view('portal/notice.php', $notice, true);
			$this->data->notices = $this->data->notices . $notice_view;
		}


        $this->data->seminar = '';
		if (is_object($seminar))
		{
			/*if ( strlen($seminar->title) > 49 ) {
				$seminar->title = substr(strip_tags($seminar->title), 0, 49) . '...';
			}*/

			$seminar->body = $this->textile->TextileThis($seminar->body);

			$beg_date = ConvertDateFormat($seminar->begdt, 'd.m.Y');
			$beg_time = ConvertDateFormat($seminar->begdt, 'H:i');
			$end_date = ConvertDateFormat($seminar->enddt, 'd.m.Y');
			$end_time = ConvertDateFormat($seminar->enddt, 'H:i');
			$seminar->schedule = '';
			if ($beg_date == $end_date)
			{
				$seminar->schedule = "$beg_date с $beg_time до $end_time";
			}

			$seminar_view = $this->load->view('portal/seminar_nearest.php', $seminar, true);
			$this->data->seminar = $seminar_view;
		}

		if (isset($_REQUEST['from']) && $_REQUEST['from'] == 'promed') {
			$this->data->login_warning = TRUE;
		}
		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->helper('kz/Region');

			if ($_SERVER['SERVER_NAME'] == getKzMainDomain()) {
				$region_title = "Выберите регион";
			} else {
				$region_title = getKzRegionTitle();
			}

			$this->data->region_title = !empty($region_title) ? $region_title : "Отсутствует заголовок";

			$this->load->view('portal/kz/main.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/main.php', $this->data);
		} else {
			$this->load->view('portal/main.php', $this->data);
		}
	}

	/**
	 * Страница модулей ИАС
	 */
	function ias() {
		$this->data->title = 'ИАС';

		$this->data->content = $this->load->view('portal/ias.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
	}

	/**
	 * Страница сервисов
	 */
	function service() {
		$this->data->title = 'Сервис';

		$this->data->content = $this->load->view('portal/services.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
	}

	/**
	 * Страница входа в Единую Цифровую Платформу
	 */
	function udp() {
		$this->promed();
	}

	/**
	 * Страница входа в Промед
	 */
	protected function promed() {
		//$this->data->title = 'Вход в систему';
		$news_on_page = $this->portal_config['settings']['news_on_page']; // Кол-во новостей на странице
		$body_cut = $this->portal_config['settings']['news_body_cut_lines'];
		
		if(isset($_GET['lang']))
			$_COOKIE['lang'] = $_GET['lang'];
		else
			$_COOKIE['lang'] = 'ru';

		$IsLocalSMP = $this->config->item('IsLocalSMP');
		if ($IsLocalSMP === true) {
			// сразу переходим в промед, работает без авторизации.
			header("Location: /?c=promed");
		}
		if (isset($_SESSION['login'])) {
			header("Location: /?c=promed");
		}

		if (isset($_REQUEST['from']) && $_REQUEST['from'] == 'promed') {
			$this->data->login_warning = TRUE;
		}

		$this->data->newClsPage = 'new_design_page login_page';

		if (isset($_SESSION['esia_error'])) {
			$this->data->login_error = $_SESSION['esia_error'];
			unset($_SESSION['esia_error']);
		}
		
		if (isset($_SESSION['error_emias'])) {
			$this->data->login_error = $_SESSION['error_emias'];
			unset($this->data->login_warning);
			unset($_SESSION['error_emias']);
		}

		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = mb_strlen($characters);
		$randomString = '';
		$length = 33;
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}

		$this->data->ecp_message = base64_encode($randomString);
		// запихиваем в сессию
		$_SESSION['ecp_message'] = $this->data->ecp_message;

		$this->load->model("Options_model");
		$this->data->options = $this->Options_model->getOptionsGlobals(array('session' => array('login' => '')));

		$this->config->load('esia', false, true);
		$this->data->esia_config = $this->config->item('esia');

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		
		if (!in_array($region, ['kz', 'msk'])) {
			$this->data->content = $this->load->view('portal/login_promed.php', $this->data, true);
		} else {
			$this->data->content = $this->load->view('portal/login_promed_old.php', $this->data, true);
		}

		if($region == 'kz'){
			if($_COOKIE['lang']=='kz')
				$this->data->title = 'Жүйеге кіру';
			else
				$this->data->title = 'Вход в систему';
		}
		else {
			$this->data->title = 'Вход в систему';
		}

		// новости
		$news = $this->Portal_model->getNews(0, $news_on_page);
		$count = $this->Portal_model->getNewsCount();
		
		$this->data->more = false;
		if (count($news) < $count) {
			$this->data->more = min(($count - count($news)), $news_on_page);
		}

		$this->data->news_entries = '';
		foreach ($news as $news_entry) {
			$news_entry->datetime = $this->arMonth[$news_entry->datetime->format('n')]
				.' <b>'.$news_entry->datetime->format('d').'</b>'
				.' <i>'.$news_entry->datetime->format('Y').'</i>';
            if (isset($news_entry->schedule) && $news_entry->schedule){
                $news_entry->schedule = $news_entry->schedule->format('d M Y');
            }
			$body_lines = preg_split('/(\r?\n)/', $news_entry->body);
			$news_entry->cut = false;
			if (count($body_lines) > $body_cut) {
				$body_lines = array_slice($body_lines, 0, $body_cut);
				$news_entry->cut = true;
			}
			$news_entry->body = implode("\n", $body_lines);
			$news_entry->body = $this->textile->TextileThis($news_entry->body);
			$news_entry_view = $this->load->view('portal/news_entry.php', $news_entry, true);
			$this->data->news_entries = $this->data->news_entries . $news_entry_view;
		}

		$this->data->start = $news_on_page + 1;
		$this->data->num = $news_on_page;
		$this->data->news = $this->load->view('portal/news.php', $this->data, true);
		
		$notices = $this->Portal_model->getNotices();
        $seminar = $this->Portal_model->getSeminarNearest();
		$this->data->notices = '';
		foreach ($notices as $notice) {
			$notice->body = $this->textile->TextileThis($notice->body);
			$notice_view = $this->load->view('portal/notice.php', $notice, true);
			$this->data->notices = $this->data->notices . $notice_view;
		}

        $this->data->seminar = '';
		if (count($seminar)) {
			foreach ($seminar as $sm) {
				$body_lines = preg_split('/(\r?\n)/', $sm['body']);
				$sm['cut'] = false;
				if (count($body_lines) > $body_cut) {
					$body_lines = array_slice($body_lines, 0, $body_cut);
					$sm['cut'] = true;
				}
				$sm['body'] = implode("\n", $body_lines);
				$sm['body'] = $this->textile->TextileThis($sm['body']);
				$beg_date = $sm['begdt']->format('d.m.Y');
				$beg_time = $sm['begdt']->format('H:i');
				$end_time = $sm['enddt']->format('H:i');
				$sm['schedule'] = "$beg_date (".$this->dayNames[$sm['begdt']->format('N')]."), $beg_time &mdash; $end_time";
				$this->data->seminar .= $this->load->view('portal/seminar_nearest.php', $sm, true);
			}
		}

		switch($region) {
			case 'kz':
				$this->load->view('portal/kz/common.php', $this->data);
				break;
			case 'msk':
				$this->load->view('portal/common_old.php', $this->data);
				break;
			default:
				$this->load->view('portal/common.php', $this->data);
				break;
		}
	}

	/**
	 * Вывод статьи
	 */
	function article() {
		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

		$article = $this->Portal_model->getArticle($id);

		if ( !empty($article->title) ) {
			$this->data->title = $article->title;
			$this->data->body = $this->textile->TextileThis($article->body);
		}
		else {
			$this->data->title = 'Ошибка';
			$this->data->body = $this->textile->TextileThis('Статья не найдена');
		}

		$this->data->content = $this->load->view('portal/article.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
	}

	/**
	 * Вывод новости
	 */
	function news_entry() {
		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

		$article = $this->Portal_model->getNewsEntry($id);

		$this->data->title = $article->title;
		$this->data->datetime_news = $article->datetime->format('d.m.Y');
		$this->data->body = $this->textile->TextileThis($article->body);

		$this->data->newClsPage = 'new_design_page login_page news_entry';
		
		$this->data->content = $this->load->view('portal/article.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
	}

	/**
	 * Страница новостей
	 */
	function news() {
		$news_on_page = $this->portal_config['settings']['news_on_page']; // Кол-во новостей на странице

		$this->data->title = 'Новости';

		$news = $this->Portal_model->getNews(0, $news_on_page);
		$count = $this->Portal_model->getNewsCount();

		$this->data->end = false;
		if (count($news) >= $count) {
			$this->data->end = true;
		}

		$this->data->news_entries = '';
		foreach ($news as $news_entry) {
			$news_entry->datetime = ConvertDateFormat($news_entry->datetime, 'd.m.Y - H:i');
            if (isset($news_entry->schedule) && $news_entry->schedule){
                $news_entry->schedule = ConvertDateFormat($news_entry->schedule, 'd.m.Y - H:i');
            }
			$news_entry->body = $this->textile->TextileThis($news_entry->body);

			$news_entry_view = $this->load->view('portal/news_entry.php', $news_entry, true);
			$this->data->news_entries = $this->data->news_entries . $news_entry_view;
		}

		$this->data->start = $news_on_page + 1;
		$this->data->num = $news_on_page;
		$this->data->content = $this->load->view('portal/news.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
	}

	/**
	 * Вывод новостей по AJAX-запросу
	 */
	function getNewsMore() {
		$start = $this->input->post('start'); // Начинать с записи
		$num = $this->input->post('num'); // Кол-во новостей на запрос
		$body_cut = $this->portal_config['settings']['news_body_cut_lines'];

		$news = $this->Portal_model->getNews($start, $num);
		$count = $this->Portal_model->getNewsCount();

		$more = false;
		if ($start + $num >= $count) {
			$more = min(($count - $start - $num), $num);
		}

		$html = '';
		foreach ($news as $news_entry) {
			$news_entry->datetime = $this->arMonth[$news_entry->datetime->format('n')]
				.' <b>'.$news_entry->datetime->format('d').'</b>'
				.' <i>'.$news_entry->datetime->format('Y').'</i>';
			$body_lines = preg_split('/(\r?\n)/', $news_entry->body);
			$news_entry->cut = false;
			if (count($body_lines) > $body_cut) {
				$body_lines = array_slice($body_lines, 0, $body_cut);
				$news_entry->cut = true;
			}
			$news_entry->body = implode("\n", $body_lines);
			$news_entry->body = $this->textile->TextileThis($news_entry->body);
			$news_entry_view = $this->load->view('portal/news_entry.php', $news_entry, true);

			$html = $html . $news_entry_view;
		}

		$output = new stdClass();
		$output->html = $html;
		$output->success = true;
		$output->more = $more;

		echo json_encode($output);
	}

    /**
     * Вывод объявления о семинаре
     */
    function seminar() {
        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

        $article = $this->Portal_model->getSeminar($id);
		if (empty($article)) {
			show_404('portal/seminar');
			return;
		}

        $this->data->datetime = ConvertDateFormat($article->datetime, 'd.m.Y - H:i');

		$beg_date = ConvertDateFormat($article->begdt, 'd.m.Y');
		$beg_time = ConvertDateFormat($article->begdt, 'H:i');
		$end_date = ConvertDateFormat($article->enddt, 'd.m.Y');
		$end_time = ConvertDateFormat($article->enddt, 'H:i');
		$this->data->schedule = '';
		if ($beg_date == $end_date)
		{
			$this->data->schedule = "$beg_date с $beg_time до $end_time";
		}

        /*$this->data->begdt = $article->begdt->format('d.m.Y - H:i');
        $this->data->enddt = $article->enddt->format('d.m.Y - H:i');*/
        $this->data->title = $article->title;
        $this->data->body = $this->textile->TextileThis($article->body);

        $this->data->content = $this->load->view('portal/article.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
    }

    /**
     * Страница объявлений о семинарах
     */
    function seminars() {
        $news_on_page = $this->portal_config['settings']['news_on_page']; // Кол-во новостей на странице

        $this->data->title = 'Семинары';

        $seminars = $this->Portal_model->getSeminars(0, $news_on_page);
        $count = $this->Portal_model->getSeminarsCount();

        $this->data->end = false;
        if (count($seminars) >= $count) {
            $this->data->end = true;
        }

        $this->data->seminars = '';
        foreach ($seminars as $seminar) {
            $seminar->datetime = ConvertDateFormat($seminar->datetime, 'd.m.Y - H:i');

			$beg_date = ConvertDateFormat($seminar->begdt, 'd.m.Y');
			$beg_time = ConvertDateFormat($seminar->begdt, 'H:i');
			$end_date = ConvertDateFormat($seminar->enddt, 'd.m.Y');
			$end_time = ConvertDateFormat($seminar->enddt, 'H:i');
			$seminar->schedule = '';
			if ($beg_date == $end_date)
			{
				$seminar->schedule = "$beg_date с $beg_time до $end_time";
			}

            $seminar->body = $this->textile->TextileThis($seminar->body);

            $seminar_view = $this->load->view('portal/seminar.php', $seminar, true);
            $this->data->seminars = $this->data->seminars . $seminar_view;
        }

        $this->data->start = $news_on_page + 1;
        $this->data->num = $news_on_page;
        $this->data->content = $this->load->view('portal/seminars.php', $this->data, true);

		$region = empty( $region ) ? ( (empty( $_SERVER[ 'REGION' ] )) ? null : $_SERVER[ 'REGION' ] ) : $region ;
		if ($region == 'kz') {
			$this->load->view('portal/kz/common.php', $this->data);
		} else if($region == 'msk'){
			$this->load->view('portal/msk/common.php', $this->data);
		} else {
			$this->load->view('portal/common.php', $this->data);
		}
    }

    /**
     * Вывод новостей по AJAX-запросу
     */
    function getSeminarsMore() {
        $start = $this->input->post('start'); // Начинать с записи
        $num = $this->input->post('num'); // Кол-во новостей на запрос

        $seminars = $this->Portal_model->getSeminars($start, $num);
        $count = $this->Portal_model->getSeminarsCount();

        $end = false;
        if ($start + $num >= $count) {
            $end = true;
        }

        $html = '';
        foreach ($seminars as $seminar) {
            $seminar->datetime = ConvertDateFormat($seminar->datetime, 'd.m.Y - H:i');

			$beg_date = ConvertDateFormat($seminar->begdt, 'd.m.Y');
			$beg_time = ConvertDateFormat($seminar->begdt, 'H:i');
			$end_date = ConvertDateFormat($seminar->enddt, 'd.m.Y');
			$end_time = ConvertDateFormat($seminar->enddt, 'H:i');
			$this->data->schedule = '';
			if ($beg_date == $end_date)
			{
				$this->data->schedule = "$beg_date с $beg_time до $end_time";
			}

            $seminar->title = toUTF($seminar->title);
            $seminar->body = toUTF($seminar->body);

            $seminar_view = $this->load->view('portal/seminar.php', $seminar, true);

            $html = $html . $seminar_view;
        }

		$output = new stdClass();
        $output->html = $html;
        $output->success = true;
        $output->end = $end;

        echo json_encode($output);
    }

	/**
	 * Проверка является ли сервер локальным
	 */
    function checkIsLocalServer() {
    	$IsLocalSMP = $this->config->item('IsLocalSMP');
    	if ($IsLocalSMP === true) {
			echo json_encode(array('success' => true));
		} else {
			echo json_encode(array('success' => false));
		}
	}
}

?>
