<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Контроллер администраторского интерфейса портала
 *
 * @copyright    Copyright (c) Swan Ltd.
 * @author       Alexander Arefyev aka Alf (avaref@gmail.com)
 * @version      2011.07
 */
class PortalAdmin extends CI_Controller {

	private $data; // Данные для вывода
	var $usePostgre = false;
	var $arMonth = [
		1=>'января',2=>'февраля',3=>'марта',4=>'апреля',
		5=>'мая',6=>'июня',7=>'июля',8=>'августа',
		9=>'сентября',10=>'октябрья',11=>'ноября',12=>'декабря'
	];

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->usePostgre = defined('USE_POSTGRESQL') && USE_POSTGRESQL;
		$this->load->model('Portal_model');
		$this->load->library('textile');
		$this->data = new stdClass();
	}

	/**
	 *	_remap
	 */
	function _remap($method) {
		if (!isset($_SESSION['username'])) {
			$this->login();
		} else {
			$this->$method();
		}
	}

	/**
	 * Вывод основной страницы
	 */
	function login() {
		if (isset($_SESSION['login_failed'])) {
			$this->data->msg = 'Неверное имя пользователя или пароль';
			unset($_SESSION['login_failed']);
		}

		$this->load->view('portal/login.php', $this->data);

		unset($this->data->msg);
	}

	/**
	 * Выход
	 */
	function logout() {
		session_destroy();
		header("Location: /?c=portal");
	}

	/**
	 * Вывод основной страницы
	 */
	function index() {
		$this->dashboard();
	}

	/**
	 * Панель управления. Основные функции админки
	 */
	function dashboard() {
		header("Location: /?c=portalAdmin&m=news");
		die();
		
		$this->data->content = $this->load->view('portal/admin_dashboard.php', $this->data, true);

		$this->load->view('portal/admin.php', $this->data);
	}

	/**
	 * Вывод списка новостей
	 */
	function news() {
		$news = $this->Portal_model->getNewsAdmin();

		$this->data->news_entries = '';
		foreach ($news as $news_entry) {
			$news_entry->datetime = $news_entry->datetime->format('d ').$this->arMonth[$news_entry->datetime->format('n')].$news_entry->datetime->format(' Y');
			$news_entry->body = mb_substr(strip_tags($news_entry->body), 0, 300) . '...';

			$news_entry_view = $this->load->view('portal/admin_news_entry.php', $news_entry, true);
			$this->data->news_entries = $this->data->news_entries . $news_entry_view;
		}

		$this->data->content = $this->load->view('portal/admin_news.php', $this->data, true);

		$this->load->view('portal/admin.php', $this->data);
	}

	/**
	 * Редактирование новости
	 */
	function news_edit() {
		if (!empty($_POST)) {
			$entry = new StdClass();
			$entry->title = $_REQUEST['title'];
			$entry->body = $_REQUEST['body'];
			$entry->id = $_REQUEST['id'];

			$this->Portal_model->updateNewsEntry($entry);

			header("Location: /?c=portalAdmin&m=news");
		} else {
			$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

			$news_entry = $this->Portal_model->getNewsEntry($id);

			$this->data->title = $news_entry->title;
			$this->data->body = $news_entry->body;
			$this->data->date = ConvertDateFormat($news_entry->datetime, 'd.m.Y');
			$this->data->time = ConvertDateFormat($news_entry->datetime, 'H:i');

			$this->data->content = $this->load->view('portal/admin_news_edit.php', $this->data, true);

			$this->load->view('portal/admin.php', $this->data);
		}
	}

	/**
	 * Добавление новости
	 */
	function news_add() {
		if (!empty($_POST)) {
			$entry = new StdClass();
			$entry->title = $_REQUEST['title'];
			$entry->body = $_REQUEST['body'];

			$this->Portal_model->insertNewsEntry($entry);

			header("Location: /?c=portalAdmin&m=news");
		} else {
			$this->data->content = $this->load->view('portal/admin_news_add.php', $this->data, true);

			$this->load->view('portal/admin.php', $this->data);
		}
	}

	/**
	 * Удаление новости
	 */
	function news_delete() {
		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

		$article = $this->Portal_model->deleteNewsEntry($id);
		header("Location: /?c=portalAdmin&m=news");
	}

	/**
	 * Вывод списка объявлений
	 */
	function notices() {
		$notices = $this->Portal_model->getNoticesAll();

		$this->data->notices = '';
		foreach ($notices as $notice) {
			$notice->datetime_end = ConvertDateFormat($notice->datetime_end, 'd.m.Y - H:i');
			$notice->body = substr(strip_tags($notice->body), 0, 300) . '...';

			$notice_view = $this->load->view('portal/admin_notice.php', $notice, true);
			$this->data->notices = $this->data->notices . $notice_view;
		}

		$this->data->content = $this->load->view('portal/admin_notices.php', $this->data, true);

		$this->load->view('portal/admin.php', $this->data);
	}

	/**
	 * Редактирование объявления
	 */
	function notice_edit() {
		if (!empty($_POST)) {
			$notice = new StdClass();
			$notice->title = $_REQUEST['title'];
			$notice->body = $_REQUEST['body'];
			$notice->datetime_end = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'] . ' ' . $_REQUEST['hour'] . ':' . $_REQUEST['minute'] . ':00';
			$notice->id = $_REQUEST['id'];

			$this->Portal_model->updateNotice($notice);

			header("Location: /?c=portalAdmin&m=notices");
		} else {
			$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

			$news_entry = $this->Portal_model->getNotice($id);

			$this->data->title = $news_entry->title;
			$this->data->body = $news_entry->body;
			$this->data->datetime_end = ConvertDateFormat($news_entry->datetime_end, 'Y m d, H:i');

			$this->data->content = $this->load->view('portal/admin_notice_edit.php', $this->data, true);

			$this->load->view('portal/admin.php', $this->data);
		}
	}

	/**
	 * Добавление объявления
	 */
	function notice_add() {
		if (!empty($_POST)) {
			$notice = new StdClass();
			$notice->title = $_REQUEST['title'];
			$notice->body = $_REQUEST['body'];
			$notice->datetime_end = $_REQUEST['year'] . '-' . $_REQUEST['month'] . '-' . $_REQUEST['day'] . ' ' . $_REQUEST['hour'] . ':' . $_REQUEST['minute'] . ':00';

			$this->Portal_model->insertNotice($notice);

			header("Location: /?c=portalAdmin&m=notices");
		} else {
			$this->data->content = $this->load->view('portal/admin_notice_add.php', $this->data, true);

			$this->load->view('portal/admin.php', $this->data);
		}
	}

	/**
	 * Удаление объявления
	 */
	function notice_delete() {
		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

		$article = $this->Portal_model->deleteNotice($id);
		header("Location: /?c=portalAdmin&m=notices");
	}

	/**
	 *	Вывод информации о семинарах
	 */
    function seminars() {
        $seminars = $this->Portal_model->getSeminars(true);

        $this->data->seminars = '';
        foreach ($seminars as $seminar) {
            $seminar->begdt = ConvertDateFormat($seminar->begdt, 'd.m.Y - H:i');
            $seminar->enddt = ConvertDateFormat($seminar->enddt, 'd.m.Y - H:i');
            //$seminar->body = substr(strip_tags($seminar->body), 0, 30) . '...';

            $seminar_view = $this->load->view('portal/admin_seminar.php', $seminar, true);
            $this->data->seminars = $this->data->seminars . $seminar_view;
        }

        $this->data->content = $this->load->view('portal/admin_seminars.php', $this->data, true);

        $this->load->view('portal/admin.php', $this->data);
    }


    /**
     * Добавление семинара
     */
    function seminar_add() {
        if (!empty($_POST)) {
			$seminar = new StdClass();
            $seminar->title = $_REQUEST['title'];
            $seminar->body = $_REQUEST['body'];
            $seminar->begdt = $_REQUEST['begdt']['year'] . '-' . $_REQUEST['begdt']['month'] . '-' . $_REQUEST['begdt']['day'] . ' ' . $_REQUEST['begdt']['hour'] . ':' . $_REQUEST['begdt']['minute'] . ':00';
			if (empty($_REQUEST['enddt']['year']))
			{
				$seminar->enddt = $_REQUEST['begdt']['year'] . '-' . $_REQUEST['begdt']['month'] . '-' . $_REQUEST['begdt']['day'] . ' ' . $_REQUEST['enddt']['hour'] . ':' . $_REQUEST['enddt']['minute'] . ':00';
			} else {
				$seminar->enddt = $_REQUEST['enddt']['year'] . '-' . $_REQUEST['enddt']['month'] . '-' . $_REQUEST['enddt']['day'] . ' ' . $_REQUEST['enddt']['hour'] . ':' . $_REQUEST['enddt']['minute'] . ':00';
			}

            $this->Portal_model->insertSeminar($seminar);

            header("Location: /?c=portalAdmin&m=seminars");
        } else {
            $this->data->content = $this->load->view('portal/admin_seminar_add.php', $this->data, true);

            $this->load->view('portal/admin.php', $this->data);
        }
    }

    /**
     * Редактирования семинара
     */
    function seminar_edit() {
        if (!empty($_POST)) {
			$seminar = new StdClass();
            $seminar->title = $_REQUEST['title'];
            $seminar->body = $_REQUEST['body'];
            $seminar->begdt = $_REQUEST['begdt']['year'] . '-' . $_REQUEST['begdt']['month'] . '-' . $_REQUEST['begdt']['day'] . ' ' . $_REQUEST['begdt']['hour'] . ':' . $_REQUEST['begdt']['minute'] . ':00';
			if (empty($_REQUEST['enddt']['year']))
			{
				$seminar->enddt = $_REQUEST['begdt']['year'] . '-' . $_REQUEST['begdt']['month'] . '-' . $_REQUEST['begdt']['day'] . ' ' . $_REQUEST['enddt']['hour'] . ':' . $_REQUEST['enddt']['minute'] . ':00';
			} else {
				$seminar->enddt = $_REQUEST['enddt']['year'] . '-' . $_REQUEST['enddt']['month'] . '-' . $_REQUEST['enddt']['day'] . ' ' . $_REQUEST['enddt']['hour'] . ':' . $_REQUEST['enddt']['minute'] . ':00';
			}
            $seminar->id = $_REQUEST['id'];

            $this->Portal_model->updateSeminar($seminar);

            header("Location: /?c=portalAdmin&m=seminars");
        } else {
            $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

            $seminar = $this->Portal_model->getSeminar($id);

            $this->data->title = htmlspecialchars($seminar->title);
            $this->data->body = $seminar->body;
            $this->data->begdt = ConvertDateFormat($seminar->begdt, 'Y m d, H:i');
            $this->data->enddt = ConvertDateFormat($seminar->enddt, 'Y m d, H:i');

            $this->data->content = $this->load->view('portal/admin_seminar_edit.php', $this->data, true);

            $this->load->view('portal/admin.php', $this->data);
        }
    }

    /**
     * Удаление семинара
     */
    function seminar_delete() {
        $id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

        $article = $this->Portal_model->deleteSeminar($id);
        header("Location: /?c=portalAdmin&m=seminars");
    }

	/**
	 * Вывод списка статей
	 */
	function articles() {
		$articles = $this->Portal_model->getArticles();

		$this->data->articles = '';
		foreach ($articles as $article) {
			$article->body = substr(strip_tags($article->body), 0, 100) . '...';

			$article_view = $this->load->view('portal/admin_article.php', $article, true);
			$this->data->articles = $this->data->articles . $article_view;
		}

		$this->data->content = $this->load->view('portal/admin_articles.php', $this->data, true);

		$this->load->view('portal/admin.php', $this->data);
	}

	/**
	 * Редактирование статьи
	 */
	function article_edit() {
		if (!empty($_POST)) {
			$article = new StdClass();
			$article->title = $_REQUEST['title'];
			$article->body = $_REQUEST['body'];
			$article->id = $_REQUEST['id'];

			$this->Portal_model->updateArticle($article);

			header("Location: /?c=portalAdmin&m=articles");
		} else {
			$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

			$article = $this->Portal_model->getArticle($id);

			$this->data->title = $article->title;
			$this->data->body = $article->body;

			$this->data->content = $this->load->view('portal/admin_article_edit.php', $this->data, true);

			$this->load->view('portal/admin.php', $this->data);
		}
	}

	/**
	 * Добавление объявления
	 */
	function article_add() {
		if (!empty($_POST)) {
			$article = new StdClass();
			$article->title = $_REQUEST['title'];
			$article->body = $_REQUEST['body'];

			$this->Portal_model->insertArticle($article);

			header("Location: /?c=portalAdmin&m=articles");
		} else {
			$this->data->content = $this->load->view('portal/admin_article_add.php', $this->data, true);

			$this->load->view('portal/admin.php', $this->data);
		}
	}

	/**
	 * Удаление статьи
	 */
	function article_delete() {
		$id = isset($_REQUEST['id']) ? trim($_REQUEST['id']) : 0;

		$article = $this->Portal_model->deleteArticle($id);
		header("Location: /?c=portalAdmin&m=articles");
	}

}