<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * Модель для работы с данными портала
 *
 * @copyright    Copyright (c) Swan Ltd.
 * @author       Alexander Arefyev aka Alf (avaref@gmail.com)
 * @version      2011.07
 */
class Portal_model extends SwPgModel {

	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	protected $schema = 'dbo';

	public function setDb($name = 'portalPg')
	{
		parent::setDb($name);
	}

	/**
	 * Возвращает статью из таблицы articles
	 * 
	 * @param int $id Номер статьи
	 * @return object 
	 */
	function getArticle($id) {

		$sql = "SELECT id, title, body
			FROM articles
            WHERE id = ? ";

		$res = $this->db->query($sql, array($id));
		$article = $res->row();

		return $article;
	}

	/**
	 * Возвращает новость по ID
	 *
	 * @param int $id Номер новости
	 * @return object
	 */
	function getNewsEntry($id) {

		$sql = "SELECT news_id as id, title, body, datetime
			FROM news
            WHERE news_id = ? ";

		$res = $this->db->query($sql, array($id));
		$news_entry = $res->row();

		return $news_entry;
	}

	/**
	 * Редактирует новость
	 * 
	 * @param object $entry Новость
	 * @return int id записи
	 */
	function updateNewsEntry($entry) {

		$sql = "UPDATE news
                SET title = ?, body = ?, active = 1
                WHERE news_id = ? ";

		$res = $this->db->query($sql, array(
			$entry->title,
			$entry->body,
			$entry->id
				));

		return $entry->id;
	}

	/**
	 * 	Создает новость
	 * 
	 *  @param object $entry Новость
	 *  @return int id записи
	 */
	function insertNewsEntry($entry) {

		$sql = "INSERT INTO news
				(title, body, datetime, active)
				VALUES (?, ?, ?, 1)";

		$res = $this->db->query($sql, array(
			$entry->title,
			$entry->body,
			date('Y-m-d H:i:s') // вставит дату сервера, что правильно 
		));

		$entry_id = $this->db->insert_id();

		return $entry_id;
	}

	/**
	 * 	Удаляет новость
	 * 
	 *  @param int $id id новости
	 *  @return bool
	 */
	function deleteNewsEntry($id) {

		$sql = 'DELETE FROM news WHERE news_id = ?';
		$res = $this->db->query($sql, array($id));
		return $res;
	}

	/**
	 * Возвращает статьи
	 * 
	 * @return object 
	 */
	function getArticles() {

		$sql = "SELECT id, title, body
			FROM articles
            ORDER BY id DESC";

		$res = $this->db->query($sql);

		$articles = $res->result();

		return $articles;
	}

	/**
	 * Редактирует статью
	 * 
	 * @param object $article Статья
	 * @return int id записи
	 */
	function updateArticle($article) {

		$sql = "UPDATE articles
                SET title = ?, body = ?
                WHERE id = ? ";

		$res = $this->db->query($sql, array(
			$article->title,
			$article->body,
			$article->id
				));

		return $article->id;
	}

	/**
	 * 	Создает статью
	 * 
	 *  @param object $article Статья
	 *  @return int id записи
	 */
	function insertArticle($article) {

		$sql = "INSERT INTO articles
				(title, body)
				VALUES (?, ?)";

		$res = $this->db->query($sql, array(
			$article->title,
			$article->body,
				));

		$entry_id = $this->db->insert_id();

		return $entry_id;
	}

	/**
	 * 	Удаляет новость
	 * 
	 *  @param int $id id новости
	 *  @return bool
	 */
	function deleteArticle($id) {

		$sql = 'DELETE FROM articles WHERE id = ?';
		$res = $this->db->query($sql, array($id));
		return $res;
	}

    /**
     * Возвращает новости
     *
     * @return object
     */
    function getNewsAdmin() {

    	$sql = "SELECT
			news_id as id, title, body, datetime
			FROM news
			WHERE active=1 AND begdt IS NULL
			ORDER BY datetime DESC";

        $res = $this->db->query($sql);

        $news = $res->result();

        return $news;
    }

    /**
	 * Возвращает все новости
	 * 
	 * @param int $offset Смещение от начала таблицы
	 * @param int $top Количество записей
	 * @return object
	 */
	function getNews($offset = 0, $top = 0) {

		$sql = "SELECT
			news_id as id, title, body, datetime, begdt
			FROM news
			WHERE active=1  AND begdt IS NULL
			ORDER BY datetime DESC
			LIMIT $top OFFSET $offset
			";

		$res = $this->db->query($sql);

		$news = $res->result();

		return $news;
	}

	/**
	 * Возвращает общее кол-во новостей
	 * 
	 * @return int
	 */
	function getNewsCount() {

		$sql = "SELECT COUNT (*) as count
			FROM news
			WHERE active=1";
		$count = $this->db->query($sql)->row()->count;

		return $count;
	}

	/**
	 * Возвращает объявления
	 * 
	 * @return object
	 */
	function getNotices() {

		$sql = "SELECT
			id, title, body, datetime_end
			FROM notices
			WHERE CURRENT_DATE < datetime_end -- WHERE dbo.tzGetDate() < datetime_end в portal похоже нет dbo.tzGetDate(), надо будет добавить. пока убрал, чтобы работало
			ORDER BY datetime_end DESC";

		$res = $this->db->query($sql);

		$notices = $res->result();

		return $notices;
	}

	/**
	 * Возвращает объявление ID
	 * 
	 * @param int $id Номер объявления
	 * @return object 
	 */
	function getNotice($id) {

		$sql = "SELECT id, title, body, datetime_end
			FROM notices
            WHERE id = ? ";

		$res = $this->db->query($sql, array($id));
		$notice = $res->row();

		return $notice;
	}

	/**
	 * Редактирует объявление
	 * 
	 * @param object $notice Объявление
	 * @return int id записи
	 */
	function updateNotice($notice) {

		$sql = "UPDATE notices
                SET title = ?, body = ?, datetime_end = ?
                WHERE id = ? ";

		$res = $this->db->query($sql, array(
			$notice->title,
			$notice->body,
			$notice->datetime_end,
			$notice->id
				));

		return $notice->id;
	}

	/**
	 * 	Создает объявление
	 * 
	 *  @param object $notice Объявление
	 *  @return int id записи
	 */
	function insertNotice($notice) {

		$sql = "INSERT INTO notices
				(title, body, datetime_end)
				VALUES (?, ?, ?)";

		$res = $this->db->query($sql, array(
			$notice->title,
			$notice->body,
			$notice->datetime_end,
				));

		$entry_id = $this->db->insert_id();

		return $entry_id;
	}

	/**
	 * 	Удаляет объявление
	 * 
	 *  @param int $id id объявления
	 *  @return bool
	 */
	function deleteNotice($id) {

		$sql = 'DELETE FROM notices WHERE id = ?';
		$res = $this->db->query($sql, array($id));
		return $res;
	}

	/**
	 * Возвращает все объявления
	 * 
	 * @return object
	 */
	function getNoticesAll() {

		$sql = "SELECT
			id, title, body, datetime_end
			FROM notices
			ORDER BY datetime_end DESC";

		$res = $this->db->query($sql);

		$notices = $res->result();

		return $notices;
	}

    /**
     * Возвращает объявления о семинарах
     *
     * @return object
     */
    function getSeminars($all = false)
    {

		$where = '';
		$order = 'begdt DESC';
		if (!$all) {
			$where .= ' AND enddt >= CURRENT_DATE';
			$order = 'begdt';
		}

        $sql = "SELECT
        news_id as id, title, body, datetime, begdt, enddt
        FROM news
        WHERE active=1 AND begdt IS NOT NULL {$where}
        ORDER BY {$order}";

        $res = $this->db->query($sql);

        $seminars = $res->result();

        return $seminars;
    }

    /**
     * Возвращает объявление о семинаре по ID
     *
     * @param int $id Номер объявления о семинаре
     * @return object
     */
    function getSeminar($id)
	{

        $sql = "SELECT news_id as id, title, body, datetime, begdt, enddt
			FROM news
            WHERE news_id = ? ";

        $res = $this->db->query($sql, array($id));
        $seminar = $res->row();

        return $seminar;
    }

	/**
	 * Возвращает объявление о ближайшем семинаре
	 * 
	 * @return array|false
	 */
    function getSeminarNearest()
    {
        $sql = "
			SELECT news_id as id, title, body, datetime, begdt, enddt
			FROM news
            WHERE begdt IS NOT NULL AND begdt > CURRENT_DATE
            ORDER BY begdt LIMIT 1
		";
	    return $this->queryResult($sql);
    }

    /**
     * Возвращает кол-во объявлений о семинарах
     *
     * @return int
     */
    function getSeminarsCount($all = false) {

		$where = '';
		if (!$all) {
			$where .= ' AND enddt >= CURRENT_DATE';
		}

        $sql = "SELECT COUNT (*) as count
			FROM news
			WHERE active=1 AND begdt IS NOT NULL {$where}";
        $count = $this->db->query($sql)->row()->count;

        return $count;
    }

    /**
     * Создание объявления о семинаре
     *
     * @return int
     */
    function insertSeminar($seminar)
    {
        $sql = "INSERT INTO news
				(title, body, datetime, begdt, enddt, active)
				VALUES (?, ?, ?, ?, ?, 1)";

        $res = $this->db->query($sql, array(
            $seminar->title,
            $seminar->body,
            date('Y-m-d H:i:s'),
            $seminar->begdt,
            $seminar->enddt
        ));

        $seminar_id = $this->db->insert_id();

        return $seminar_id;
    }

    /**
     * Обнавляет объявление о семинаре
     *
     * @param object $seminar Объявление о семинаре
     * @return int
     */
    function updateSeminar($seminar)
    {

        $sql = "UPDATE news
                SET title = ?, body = ?, begdt = ?, enddt = ?, active = 1
                WHERE news_id = ? ";

        $res = $this->db->query($sql, array(
            $seminar->title,
            $seminar->body,
            $seminar->begdt,
            $seminar->enddt,
            $seminar->id
        ));

        return $seminar->id;
    }

    /**
     * Удаляет объявление о семинаре
     *
     * @param int $id Номер объявления о семинаре
     * @return bool
     */
    function deleteSeminar($id)
    {

        $sql = 'DELETE FROM news WHERE news_id = ?';
        $res = $this->db->query($sql, array($id));
        return $res;
    }

	/**
	 * Возвращает количество активных пользователей
	 *
	 * @param string $product Продукт: promed|er|k-vrachu
	 * @return int Кол-во пользователей
	 */
	function getUsersCount($product = 'promed') {
		global $config;
		
		$count = 'N/A';

		// todo: Надо проверять настройку из конфига session и надо название таблицы в mongodb (куда пишутся сессии, Session), также вынести в настройки 
		if ($product == 'promed' && isset($config['session_driver']) && $config['session_driver'] == 'mongodb') {
			try {
				switch (checkMongoDb()) {
					case 'mongo':
						$this->load->library('swMongodb', array('config_file'=>'mongodbsessions'), 'swmongodb');
						break;
					case 'mongodb':
						$this->load->library('swMongodbPHP7', array('config_file'=>'mongodbsessions'), 'swmongodb');
						break;
				}
				// Берем из настроек название таблицы
				$table = (isset($config['mongodb_session_settings']) && isset($config['mongodb_session_settings']['table']))?$config['mongodb_session_settings']['table']:'Session';
				// Получаем количество записей в таблице
				// Пояснения:  where_ne('value','eJwDAAAAAAE=') => исключая пустые сессии, 
				//             where_gt('updated', time()-1800) => где сессия изменена в последние полчаса
				$count = $this->swmongodb->where_gt('updated', time()-1800)->where('logged', 1)->count($table); // только залогиненные и активные последние полчаса
				// $count = $this->swmongodb->where_gt('updated', time()-1800)->where('$where', 'this.value.length > 1000')->count($table); // только залогиненные и активные последние полчаса
				// $count = $this->swmongodb->where_ne('value','eJwDAAAAAAE=')->count($table); // только залогиненные
				// $count = $this->swmongodb->count($table); // все пользователи
				
				// Дальше не идем, поскольку все необходимые данные получены
				return $count;
			} catch (Exception $e) {
				$count = 'N/A';
				return $count;
			}
		}
		$sessions_db = $this->load->database('php_session', true);

		if ($product != 'ias') {
			switch ($product) {
				case 'promed':
					$table = 'phpsessions';
					break;
				case 'er':
					$table = 'phpsessionser';
					break;
				case 'k-vrachu':
					$table = 'phpsessionsuserreg';
					break;
				default:
					break;
			}
			if (strtolower($sessions_db->dbdriver) == 'postgre'){
				$sql = "SELECT count(*) AS count FROM {$table} WHERE updated > (NOW() - interval '15 minutes')";
			} else {
				$sql = "SELECT count(*) AS count FROM {$table} WITH (nolock) WHERE updated > dateadd(n, -15, getdate())";
			}
		}

		if ($product == 'ias') {
			try {
				$sessions_db = $this->load->database('ias', true);
			} catch (Exception $e) {
				$count = 'N/A';
				return $count;
			}
			/* @todo Написать запрос для PG, кол-во сессий в IAS */
			if (strtolower($sessions_db->dbdriver) == 'postgre'){
			    $sql = '';
                $count = 'N/A';
                return $count;
            } else {
            	$sql = "SELECT COUNT(distinct USER_ID) as count
				FROM [IASReportingModule].[dbo].[USR_MONITOR_ACTIVITY] 
				where DATEDIFF(mi, [DATETIME_ACTIVITY], getdate())<10";
			}

		}

		if (function_exists('apc_fetch')) {
			$cached = 'users_' . $product;
			if (!apc_fetch($cached)) {
				try {
					$count = $sessions_db->query($sql)->row()->count;
				} catch (Exception $e) {
					$count = 'N/A';
				}
				$result = apc_store($cached, $count, $this->cache_time);
				if ($result) {
					$count = apc_fetch($cached);
				}
			}
		} else {
			try {
				$count = $sessions_db->query($sql)->row()->count;
			} catch (Exception $e) {
				$count = 'N/A';
			}
		}

		return $count;
	}

}
