<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		PromedWeb
 * @access		public
 * @copyright	Copyright (c) 2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		13.05.2013
 */

/**
 * Модель схем структуры Xml-документов.
 *
 * @package		XmlTemplate
 * @author		Александр Пермяков
 */
class XmlSchema_model extends swPgModel
{
	/**
	 * Пользователь
	 * @var integer
	 */
	public $pmUser_id;

	/**
	 * Список служебных параметров, которые должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_params = array(
		'pmUser_id',
	);

	/**
	 * Primary key
	 * @var integer
	 */
	protected $_XmlSchema_id;
	/**
	 * XML-схема структуры Xml-документов
	 * @var string
	 */
	protected $_XmlSchema_Data;

	/**
	 * Список атрибутов, которые могут быть записаны в модель и должны быть получены из входящих параметров
	 * @var array
	 */
	protected $_safeAttributes = array(
		'XmlSchema_Data',
		'XmlSchema_id',
	);

	/**
	 * Текст ошибки
	 * @var string
	 */
	protected $_errorMsg;
	/**
	 * Код ошибки
	 * @var integer
	 */
	protected $_errorCode;
	/**
	 * Имя сценария, определяющего правила валидации модели
	 *
	 * Возможные сценарии:
	 * create - Создание схемы
	 * update - Редактирование схемы
	 * read - Загрузка данных схемы
	 * destroy - Удаление схемы из БД
	 *
	 * @var string
	 */
	protected $_scenario;

	/**
	 * Конструктор
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Запись значения первичного ключа в модель
	 * @param int $XmlSchema_id
	 */
	public function setId($XmlSchema_id)
	{
		$this->_XmlSchema_id = $XmlSchema_id;
	}

	/**
	 * Получение значения первичного ключа из модели
	 * @return int
	 */
	public function getId()
	{
		return $this->_XmlSchema_id;
	}

	/**
	 * Извлечение значений атрибутов модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setSafeAttributes($data)
	{
		foreach ($this->_safeAttributes as $key) {
			$property = '_'.$key;
			if (property_exists($this, $property) && array_key_exists($key, $data)) {
				$this->{$property} = $data[$key];
			}
		}
	}

	/**
	 * Извлечение значений служебных параметров модели из входящих параметров
	 * @param array $data
	 * @return void
	 */
	public function setParams($data)
	{
		foreach ($this->_params as $key) {
			if (property_exists($this, $key) && array_key_exists($key, $data)) {
				$this->{$key} = $data[$key];
			}
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @return boolean
	 */
	protected function _validate()
	{
		if (empty($this->_scenario)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан сценарий';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'destroy', 'read')) && empty($this->_XmlSchema_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан ключ схемы проверки данных';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->_XmlSchema_Data)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указана схема проверки данных';
			return false;
		}
		if (in_array($this->_scenario,array('update', 'create')) && empty($this->pmUser_id)) {
			$this->_errorCode = 500;
			$this->_errorMsg = 'Не указан пользователь';
			return false;
		}
		return true;
	}

	/**
	 * Получение данных шаблона для редактирования
	 * Параметры должны быть установлены в контроллере
	 * @return array Стандартный ответ модели
	 */
	public function read()
	{
		$this->_scenario = 'read';
		if ( !$this->_validate() )
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}
		$sql = "
			SELECT
				XmlSchema_id as \"XmlSchema_id\",
				XmlSchema_Data as \"XmlSchema_Data\"
			FROM
				dbo.v_XmlSchema
			WHERE
				XmlSchema_id = ?
		";
		$result = $this->db->query($sql,  array($this->_XmlSchema_id));
		if ( is_object($result) )
			return $result->result('array');
		else
			return array(array('Error_Msg'=>'Ошибка БД, не удалось получить шаблон.'));
	}

	/**
	 * Логика перед сохранением, включающая в себя проверку данных
	 * @return boolean
	 */
	protected function _beforeSave($data = array())
	{
		if (empty($this->_scenario)) {
			$this->_scenario = 'update';
			if (empty($this->_XmlSchema_id)) {
				$this->_scenario = 'create';
			}
		}
		return $this->_validate();
	}

	/**
	 * Сохранение данных
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setParams и/или setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function save($data = array())
	{
		if (count($data) > 0) {
			$this->setParams($data);
			$this->setSafeAttributes($data);
		}

		if (!$this->_beforeSave())
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}

		$procedure = 'dbo.p_XmlSchema_upd';
		if (empty($this->_XmlSchema_id)) {
			$procedure = 'dbo.p_XmlSchema_ins';
		}

		$sql = "
			select
				XmlSchema_id as \"XmlSchema_id\",
				Error_Code as \"Error_Code\",
				Error_Message as \"Error_Msg\"
			from {$procedure}(
				XmlSchema_id := :XmlSchema_id,
				XmlSchema_Data := :XmlSchema_Data,
				pmUser_id := :pmUser_id
			)
		";
		$result = $this->db->query($sql, array(
			'XmlSchema_id' => $this->_XmlSchema_id,
			'XmlSchema_Data' => $this->_XmlSchema_Data,
			'pmUser_id' => $this->pmUser_id,
		));
		if ( is_object($result) )
			return $result->result('array');
		else
			return array(array('Error_Msg'=>'Ошибка БД, не удалось сохранить схему проверки.'));
	}

	/**
	 * Логика перед удалением, может включать в себя проверки данных
	 * @return boolean
	 */
	protected function _beforeDestroy()
	{
		$this->_scenario = 'destroy';
		return $this->_validate();
	}

	/**
	 * Удаление шаблона
	 * @param array $data Если параметры не передаются, то ранее нужно передать параметры при помощи setSafeAttributes
	 * @return array Стандартный ответ модели
	 */
	public function destroy($data = array())
	{
		if (count($data) > 0) {
			$this->setSafeAttributes($data);
		}
		if (!$this->_beforeDestroy())
		{
			return array(array('Error_Code'=>$this->_errorCode, 'Error_Msg'=>$this->_errorMsg));
		}
		$sql = '
			select
				Error_Code as "Error_Code",
				Error_Message as "Error_Msg"
			from dbo.p_XmlSchema_del(
				XmlSchema_id := :XmlSchema_id
			)
		';
		$res = $this->db->query( $sql, array(
			'XmlSchema_id' => $this->_XmlSchema_id,
		));
		if ( is_object($res) )
			return $res->result('array');
		else
			return array(array('Error_Code'=>500, 'Error_Msg'=>'Ошибка запроса к БД.'));
	}

}
