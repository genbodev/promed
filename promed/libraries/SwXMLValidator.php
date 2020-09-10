<?php
/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package		Library
 * @access		private
 * @copyright	Copyright (c) 2009-2013 Swan Ltd.
 * @link		http://swan.perm.ru/PromedWeb
 * @version		12.2017
 */

/**
 * Валидатор XML
 *
 * @package		Common
 * @author		Stanislav Bykov
 */
class SwXMLValidator {
	/**
	 * Объект класса DOMDocument
	 */
	private $_xml;

	/**
	 * Схема
	 */
	private $_xsd;

	/**
	 * Файл с ошибкам
	 */
	private $_error_file;

	/**
	 * Установка параметров
	 */
	public function setParams($xml, $xsd, $type = "string", $error_file) {
		libxml_use_internal_errors(true);  

		$this->_error_file = $error_file;
		$this->_xml = new DOMDocument();
		$this->_xsd = $xsd;
	
		if ( $type == 'file' ) {
			$this->_xml->load($xml); 
		}
		else {
			$this->_xml->loadXML($xml);
		}

		return true;
	}

	/**
	 * Вывод ошибок
	 */
	private function _libxml_display_errors() {
		$errors = libxml_get_errors();
		
		foreach ( $errors as $error ) {
			$return = "<br/>\n";
	
			switch ( $error->level ) {
				case LIBXML_ERR_WARNING:
					$return .= "<b>Warning $error->code</b>: ";
					break;
				case LIBXML_ERR_ERROR:
					$return .= "<b>Error $error->code</b>: ";
					break;
				case LIBXML_ERR_FATAL:
					$return .= "<b>Fatal Error $error->code</b>: ";
					break;
			}

			$return .= trim($error->message);
   
			if ( $error->file ) {
				$return .= " in <b>$error->file</b>";
			}
	
			$return .= " on line <b>$error->line</b>\n";

			print $return;        
		}

		libxml_clear_errors();

		return true;
	}

	/**
	 * Проверка 
	 */
	public function validate() {
		if ( !@$this->_xml->schemaValidate($this->_xsd) ) {
			ob_start();
			$this->_libxml_display_errors();
			$res_errors = ob_get_contents();
			ob_end_clean();
	
			@file_put_contents($this->_error_file, $res_errors);

			return false;
		}
	}
}
