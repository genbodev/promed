<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 * @copyright Copyright (c) 2009-2011, Swan Ltd.
 * @link http://swan.perm.ru/PromedWeb
 *
 * @class swTextLog 
 * Библиотека для быстрого создания логов работы скриптов. 
 * Включает в себя проверку того, что пользователь залогинен и методы для обработки данных возвращаемых моделью и вывода их пользователю.
 *
 * @package Library
 * @access public
 *
 * @author Markoff A.A. <markov@swan.perm.ru>
 *
 * @version 06.2011
 *
 * Пример: 
 *     $this->load->library('textlog', array('file'=>'evnudost.log'));
 *     $this->textlog->add('saveEvnUdost: Запуск');
 * Пример строки в логе
 *     07.07.2013 07:07:07 | ust | getEvnForm: getEvnDocument выполнен
 *
 */
class Textlog {
	 /**
	 * @public string
	 * Формат даты, выводимой перед сообщением в лог. Если не надо - очистите переменную.
	 */
	public $date_format = "d.m.Y H:i:s";
	/**
	 * @public string
	 * Наименование файла лога по умолчанию
	 */
	public $filename = "swLog.log";
	 /**
	 * @private object
	 * Ссылка на файл лога
	 */
	private $filelog;
	/**
	 * @logging boolean
	 * Признак ведения логов
	 */
	private $logging = 0;
	/**
	 * @separator string
	 * Разделитель полей в строке лога
	 */
	public $separator = ";";
	/**
	 * @duration boolean
	 * Признак записи продолжительности от предыдущего значения
	 */
	private $duration = 0;
	/**
	 * @lasttime float
	 * Предыдущее значение времени в секундах
	 */
	private $lasttime = 0;
	/**
	 * @uniqueId string
	 * Уникальный идентификатор записи
	 */
	private $uniqueId = null;

	/**
	 * $prefix string
	 * Структура префикса сообщения
	 */
	private $prefixStructure = null;

	/**
	 * @format string
	 * Формат лога
	 */
	private $format = 'text';

	/**
	 * @parse_xml bool
	 */
	private $parse_xml = false;
	
	/**
	 * Constructor
	 * 
	 * Создает или открывает файл на запись
	 * @access public
	 * @param string $file Наименование и путь к файлу лога.
	 * @param boolean $rewrite По умолчанию: false. Установите true, если хотите перезаписывать файл при инициализации класса. 
	 * @param boolean $logging Позволяет отключить логирование для определенного функционала, не убирая функция логирования и не выключая константу DOLOG
	 */
	public function __construct($params = array()) {
		$ci = &get_instance();
		$this->uniqueId = uniqid();
		$this->logging = (defined('DOLOG'))?DOLOG:false;
		if ((isset($params['logging']))) { // если передаем непосредственно при создани признак логирования, то используем его 
			$this->logging = $params['logging'];
		}

		if ($this->logging) 
		{
			if ((!isset($params)) || (!is_array($params)))
			{
				$params = array('file'=>$this->filename, 'rewrite'=>false);
			}
			if (!isset($params['file']))
			{
				$params['file'] = $this->filename;
			}
			if (!isset($params['rewrite']))
			{
				$params['rewrite'] = false;
			}
			if (isset($params['separator']))
			{
				$this->separator = $params['separator'];
			}
			if (isset($params['duration']))
			{
				$this->duration = $params['duration'];
			}
			if (isset($params['prefixStructure']))
			{
				$this->prefixStructure = $params['prefixStructure'];
			}
			if (!empty($params['format']))
			{
				$this->format = $params['format'];
			}
			if (isset($params['parse_xml']))
			{
				$this->parse_xml = $params['parse_xml'];
				$ci->load->helper('xml');
			}
			$this->filename = $params['file'];
			
			$params['file'] = (defined('PROMED_LOGS')?PROMED_LOGS:'').$params['file'];

			if((boolean)$params['rewrite']==true) $mode = "w"; else $mode = "a";
			$this->filelog = fopen($params['file'], $mode);
		}
	}
	/**
	 * Получает количество секунд
	 * @access public
	 */
	function get_sec(){
		$mtime=microtime();
		$mtime=explode(" ",$mtime);
		$mtime=$mtime[1]+$mtime[0];
		return $mtime;
	}
	
	/**
	 * Записывает в файл лога переданную строку 
	 * @access public
	 * @param string $text Строка записываемая в лог.
	 */
	public function add($text) {
		if ($this->logging) {
			if ($this->format == 'json') {
				$string = $this->_addJson($text);
			} else {
				$string = $this->_addText($text);
			}

			fputs($this->filelog, $string);
		}
	}

	/**
	 * @param $text
	 * @return string
	 */
	private function _addText($text) {
		$st_time = "";
		if ($this->duration) {
			if ($this->lasttime>0) {
				$st_time = (Round($this->get_sec() - $this->lasttime, 3)." ".$this->separator." ");
			}
			$this->lasttime = $this->get_sec();
		}

		$prefix = "";

		// сборный префикс текстлога, если указана структура
		if (!empty($this->prefixStructure) && is_array($this->prefixStructure)) {
			foreach ($this->prefixStructure as $prefixItem) {

				if ($prefixItem === "date") {
					$prefix .= '[' . date('d-m-Y H:i:s') . ']';
				}

				if ($prefixItem === "uid") {
					$prefix .= '[' . $this->uniqueId. ']';
				}

				if ($prefixItem === "user") {
					$prefix .= (isset($_SESSION['login'])? '[' . $_SESSION['login']. ']' : "");
				}

				if ($prefixItem === "duration") {
					$prefix .= '[' .$st_time. ']' ;
				}
			}

			$prefix .= " ";

		} else {
			$prefix .= $this->uniqueId." ; ".date($this -> date_format)." ".$this->separator." ".(isset($_SESSION['login'])?$_SESSION['login']." ".$this->separator." ":"").$st_time;
		}

		if (is_array($text)) {
			$text = print_r($text, true);
		}

		return $prefix.$text."\n";
	}

	/**
	 * Удалить BOM из строки
	 * @param string $str - исходная строка
	 * @return string $str - строка без BOM
	 */
	private function _removeBOM($str = "") {
		if ( substr($str, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf) ) {
			$str = substr($str, 3);
		}
		return $str;
	}

	/**
	 * @param $text
	 * @return string
	 */
	private function _addJson($text) {
		$record = [];

		$parse_xml = function($arr) use(&$parse_xml) {
			foreach($arr as &$item) {
				if (is_array($item)) {
					$item = $parse_xml($item);
				} else if (is_string($item)) {
					$item = $this->_removeBOM($item);

					if ( preg_match('/^<\?xml.+/', $item) ) {
						$item = XmlToArray($item);
					}
				}
			}
			return $arr;
		};

		$st_time = null;
		if ($this->duration) {
			if ($this->lasttime>0) {
				$st_time = Round($this->get_sec() - $this->lasttime, 3);
			}
			$this->lasttime = $this->get_sec();
		}

		if (!empty($this->prefixStructure) && is_array($this->prefixStructure)) {
			foreach ($this->prefixStructure as $prefixItem) {
				if ($prefixItem === "date") {
					$record['dt'] = date($this->date_format);
				}
				if ($prefixItem === "uid") {
					$prefix['uid'] = $this->uniqueId;
				}
				if ($prefixItem === "user") {
					$prefix['user'] = !empty($_SESSION['login']) ? $_SESSION['login'] : null;
				}
				if ($prefixItem === "duration") {
					$prefix['duration'] = $st_time;
				}
			}
		} else {
			$record['uid'] = $this->uniqueId;
			$record['dt'] = date($this->date_format);
			$record['user'] = !empty($_SESSION['login']) ? $_SESSION['login'] : null;
			if ($this->duration) {
				$record['duration'] = $st_time;
			}
		}

		if (is_array($text)) {
			$record['package'] = $this->parse_xml?$parse_xml($text):$text;
		} else {
			$record['text'] = $text;
		}

		return json_encode($record, JSON_FORCE_OBJECT |  JSON_HEX_TAG |  JSON_HEX_QUOT |  JSON_HEX_AMP | JSON_HEX_APOS).",\n";
	}

	/**
	 * Закрываем ресурс досрочно
	 *
	 */
	public function close(){
		if(is_resource($this->filelog)){
			fclose($this->filelog);
		}
	}
	/**
	 * Desctructor
	 * 
	 * Закрывает файл
	 */ 
	function __destruct(){
		if ($this->logging&&is_resource($this->filelog))
			fclose($this->filelog);
	}
}