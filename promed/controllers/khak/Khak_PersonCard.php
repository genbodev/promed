<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonCard - контроллер для выполенния операций с картотекой пациентов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      01.06.2009
*/
require_once(APPPATH.'controllers/PersonCard.php');

class Khak_PersonCard extends PersonCard {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Функция возвращает в XML список прикрепленного населения к указанной СМО на указанную дату
	 */
	function loadAttachedList() {
		$data = $this->ProcessInputData('loadAttachedList', true);
		if ($data === false) { return false; }

		if ( !isExpPop() ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		$this->load->library('textlog', array('file' => 'loadAttachedList.log'));
		$this->textlog->add("\n\r");
		$this->textlog->add("loadAttachedList: Запуск" . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		$attached_list_result = $this->pcmodel->loadAttachedList($data);

		if ($attached_list_result === false) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => $this->error_deadlock));
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->library('parser');

		if ( !file_exists(EXPORTPATH_ATACHED_LIST) ) {
			mkdir( EXPORTPATH_ATACHED_LIST );
		}

		// каталог в котором лежат выгружаемые файлы
		$out_dir = "khak_xml_attachedList_". date('ym');
		if ( !file_exists(EXPORTPATH_ATACHED_LIST.$out_dir) ) {
			mkdir(EXPORTPATH_ATACHED_LIST.$out_dir);
		}

		//Сканируем папку с файлами по маске, находим последний файл и присваеваем новому файлу счётчик на один больше
		$filenames = scandir(EXPORTPATH_ATACHED_LIST . $out_dir);
		$filename_counter = "001";

		foreach( $filenames as $filename ) {
			if ( preg_match('/^NI_019100_.......\.zip/i', $filename) ) {
				if ( intval(substr($filename, 14, 3)) >= intval($filename_counter) ) {
					$filename_counter = str_pad(intval(substr($filename, 14, 3)) + 1, 3, 0, STR_PAD_LEFT);
				}
				else {
					$filename_counter = str_pad($filename_counter, 3, 0, STR_PAD_LEFT);
				}
			}
		}

		$attached_list_file_name = "NI_019100_". date('ym'). $filename_counter;

		// файл-перс. данные
		$attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$attached_list_file_name.".xml";

		$zglv = array('FILENAME' => $attached_list_file_name);

		// Заголовок
		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n<RPN>" . $this->parser->parse('export_xml/person_' . $data['session']['region']['nick'] . '_zglv', $zglv, true);

		file_put_contents($attached_list_file_path, $xml, FILE_APPEND);

		// шаблон
		$templ = "person_" . $data['session']['region']['nick'] . "_zap";

		$attachData = array();
		$i = 0;

		while ( $array = $attached_list_result->_fetch_assoc() ) {
			$i++;
			$array['N_ZAP'] = $i;
			$attachData[] = $array;

			if ( count($attachData) == 1000 ) {
				array_walk_recursive($attachData, 'ConvertFromUTF8ToWin1251', true);

				$xml = $this->parser->parse('export_xml/' . $templ, array('ZAP' => $attachData), true);
				$xml = str_replace('&', '&amp;', $xml);

				file_put_contents($attached_list_file_path, $xml, FILE_APPEND);

				unset($xml);

				$attachData = array();

				$this->textlog->add("Задействовано памяти после выполнения записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");
			}
		}

		if ( $i == 0 ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Данные по прикрепленному населению при указанных параметрах в базе данных отсутствуют.'));
			return false;
		}

		if ( count($attachData) > 0 ) {
			array_walk_recursive($attachData, 'ConvertFromUTF8ToWin1251', true);

			$xml = $this->parser->parse('export_xml/' . $templ, array('ZAP' => $attachData), true);
			$xml = str_replace('&', '&amp;', $xml);

			file_put_contents($attached_list_file_path, $xml, FILE_APPEND);

			unset($xml);
		}

		$this->textlog->add("Задействовано памяти после записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");

		unset($attachData);

		$this->textlog->add("Задействовано памяти после очистки результатов запроса: " . memory_get_usage() . "\n\r");

		file_put_contents($attached_list_file_path, "</RPN>", FILE_APPEND);

		$file_zip_sign = $attached_list_file_name;
		$file_zip_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign.".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $attached_list_file_path, $attached_list_file_name . ".xml" );
		$zip->close();

		unlink($attached_list_file_path);

		if ( file_exists($file_zip_name) ) {
			$this->ReturnData(array('success' => true,'Link' => $file_zip_name/*, 'Doc' => $attached_list_data['DOC']*/));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка создания архива реестра!'));
		}

		return true;
	}
}
