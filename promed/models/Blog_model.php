<?php
defined('BASEPATH') or die ('No direct script access allowed');

class Blog_model extends CI_Model
{
    /**
     * Blog_model constructor.
     */
    function __construct()
    {
        parent::__construct();
    }
    /**
	 * 	Получить новости
     *  $start - первая запись на странице
     *  $limit - количество записей на странице
     */
    function getNewsEntries($start, $limit)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT TOP '.$limit.' * FROM blog_records with(nolock) WHERE id > '.$start.' AND publish_news = 1 AND publish = 1 ORDER BY datetime DESC');
        $result = $query->result('array');
        return $result;
    }
    /**
	 * Общее количество новостей
     */
    function getNewsEntriesCount()
    {
        $portal_db = $this->load->database("portal", true);
        return $portal_db->query('SELECT COUNT (*) as count FROM blog_records with(nolock) WHERE publish_news = 1 AND publish = 1')->row()->count;
    }
    /**
	 * Получить одну запись
     *  $id - id записи
     */
    function getEntry($id)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT * FROM blog_records with(nolock) WHERE id = '.$id);
        return $query->row();
    }
    /**
	 * Получить все записи в блоге юзера
     *  $login - логин юзера
     */
    function getEntriesByUser($login)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT * FROM blog_records with(nolock) WHERE author = \''.$login.'\' AND publish = 1 ORDER BY datetime DESC');
        $result = $query->result('array');
        return $result[0];
    }
    /**
	 * Количество записей в блоге юзера
     *  $login - логин юзера
     */
    function getEntriesCountByUser($login)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('SELECT COUNT (*) as count FROM blog_records with(nolock) WHERE author = \''.$login.'\' AND publish = 1')->row()->count;
    }
    /**
	 * Получить все записи в блоге текущего юзера
     *  $start - первая запись на странице
     *  $limit - количество записей на странице
     */
    function getEntriesOfCurrentUser($data, $start, $limit)
    {
        $login = $data['session']['login'];
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT TOP '.$limit.' * FROM blog_records with(nolock) WHERE id > '.$start.' AND author = \''.$login.'\' ORDER BY datetime DESC');
        $result = $query->result('array');
        return $result;
    }
    /**
	 * Количество записей текущего юзера
     *  $login - логин юзера
     */
    function getEntriesCountOfCurrentUser($data)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('SELECT COUNT (*) as count FROM blog_records with(nolock) WHERE author = \''.$data['session']['login'].'\'')->row()->count;
    }
    /**
	 * Получить количество комментов к записи
     *  $id - id записи
     */
    function getCommentsCount($id)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('SELECT COUNT (*) as count FROM blog_comments with(nolock) WHERE record_id = '.$id)->row()->count;
    }
    /**
	 * Получить номер записи по номеру коммента
     *  $id - номер коммента
     */
    function getCommentRecordId($id)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('SELECT record_id FROM blog_comments with(nolock) WHERE id = '.$id)->row()->record_id;
    }
    /**
	 * Получить комменты
     *  $id - id записи
     */
    function getComments($id)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT * FROM blog_comments with(nolock) WHERE record_id = '.$id);
        $result = $query->result('array');
        return $result;

    }
    /**
	 * Добавление записи в блог
     *  $entry - запись
     */
    function addEntry($entry)
    {
        $portal_db = $this->load->database('portal', true);
        $columns = str_object_keys($entry);
        $values = str_object_values_escaped($entry);
        $portal_db->query('INSERT INTO blog_records ('.$columns.') VALUES (\''.$values.'\')');
        return $portal_db->insert_id();
    }
    /**
	 * Редактирование записи
     *  $entry - запись
     */
    function editEntry($entry)
    {
        $portal_db = $this->load->database('portal', true);
        $set = str_object_key_value($entry);
        return $portal_db->query('UPDATE blog_records SET '.$set.' WHERE id = '.$entry->id);
    }
    /**
	 * Удаление записи
     *  $entry - запись
     */
    function deleteEntry($entry)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('DELETE FROM blog_records with(rowlock) WHERE id = '.$entry->id);
    }
    /**
	 * Добавление комментария в блог
     *  $comment - коммент
     */
    function addComment($comment)
    {
        $portal_db = $this->load->database('portal', true);
        $columns = str_object_keys($comment);
        $values = str_object_values_escaped($comment);
        return $portal_db->query('INSERT INTO blog_comments ('.$columns.') VALUES (\''.$values.'\')');
    }
    /**
	 * Удаление комментария
     *  $comment - коммент
     */
    function deleteComment($comment)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('DELETE FROM blog_comments with(rowlock) WHERE id = '.$comment->id);
    }
    /**
	 * Удаление всех комментариев к записи
     *  $entry - запись
     */
    function deleteAllComments($entry)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('DELETE FROM blog_comments with(rowlock) WHERE record_id = '.$entry->id);
    }
    /**
	 * Добавление файла
     *  $file - объект файла
     */
    function addFile($file, $file_data)
    {
        $portal_db = $this->load->database('portal', true);
        $columns = str_object_keys($file);
        $values = str_object_values_escaped($file);
        $portal_db->query('INSERT INTO files ('.$columns.',file_data) VALUES (\''.$values.'\','.$file_data.')');
        return $portal_db->insert_id();
    }
    /**
	 * Удаление файла
     *  $id - id файла
     */
    function deleteFile($id)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('DELETE FROM files with(rowlock) WHERE id = '.$id);
    }
    /**
	 * Удаление всех файлов записи
     *  $entry - запись
     */
    function deleteAllFiles($entry)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('DELETE FROM files with(rowlock) WHERE id_attached = '.$entry->id);
    }
    /**
	 * Есть ли прикрепленные файлы
     *  если есть - возвращает инфу о аттачах
     *  $id - номер записи
     */
    function entryAttaches($id)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT * FROM files with(nolock) WHERE id_attached = '.$id.' ORDER BY is_image DESC');
        if ($query->num_rows() > 0)
        {
            return $query->result('array');
        }
        return false;
    }
    /**
	 * Есть ли прикрепленные картинки
     *  если есть - возвращает инфу о картинках
     *  $id - номер записи
     */
    function entryImages($id)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT * FROM files with(nolock) WHERE id_attached = '.$id.' AND is_image = 1');
        if ($query->num_rows() > 0)
        {
            return $query->result('array');
        } else
        {
            return false;
        }
    }
    /**
	 * Получить количество аттачей к записи
     *  $id - id записи
     */
    function entryAttachesCount($id)
    {
        $portal_db = $this->load->database('portal', true);
        return $portal_db->query('SELECT COUNT (id) as count with(nolock) FROM files WHERE id_attached = '.$id)->row()->count;
    }
    /**
	 * Получить данные об одном файле
     *  $id - id файла
     */
    function getFileData($id)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT * FROM files with(nolock) WHERE id = '.$id);
        return $query->row();
    }
    /**
	 * Получить информацию об одном файле. Без самого файла.
     *  $id - id файла
     */
    function getFileInfo($id)
    {
        $portal_db = $this->load->database('portal', true);
        $query = $portal_db->query('SELECT id, id_attached, id_comment_attached, username FROM files with(nolock) WHERE id = '.$id);
        return $query->row();
    }
}
?>
