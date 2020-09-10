<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Person - контроллер для управления людьми (Бурятия)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Common
* @access		public
* @copyright	Copyright (c) 2009 Swan Ltd.
* @author		Stanislav Bykov (savage@swan.perm.ru)
* @version		28.11.2014
* @property Person_model dbmodel
*/
require_once(APPPATH . 'controllers/Person.php');

class Buryatiya_Person extends Person {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Экспорт реестров неработающих застрахованных лиц
	 * https://redmine.swan.perm.ru/issues/50215
	 */
	function exportPersonPolisToXml() {
		set_time_limit(0);
		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");

		$data = $this->ProcessInputData('exportPersonPolisToXml', true);
		if ($data === false) { return false; }

		$this->exportPersonPolisToDBF($data);
	}

	/**
	 * Экспорт реестров неработающих застрахованных лиц
	 * https://redmine.swan.perm.ru/issues/50215
	 */
	function exportPersonPolisToDBF($data = array()) {
		if ( !is_array($data) ) {
			return false;
		}
		else if ( count($data) == 0 ) {
			if ( array_key_exists('exportPersonPolisToDBF', $this->inputRules) ) {
				$data = $this->ProcessInputData('exportPersonPolisToXml', true);
				if ($data === false) { return false; }
			}
			else {
				return false;
			}
		}

		$this->load->library('textlog', array('file' => 'exportPersonPolisToDBF.log'));
		$this->textlog->add("\n\r");
		$this->textlog->add("exportPersonPolisToDBF: Запуск" . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		if ( !isSuperAdmin() || empty($data['AttachLpu_id']) ) {
			$data['AttachLpu_id'] = $data['Lpu_id'];
		}

		$lpuData = $this->dbmodel->getLpuData($data);

		if ( $lpuData === false || !is_array($lpuData) || count($lpuData) == 0 ) {
			$this->ReturnError('Ошибка при получении федерального кода и наименования МО');
			return false;
		}

		// Возвращаем объект с данными, а не сами данные
		$list_object = $this->dbmodel->exportPersonPolisToDBF($data);

		if ( !is_object($list_object) ) {
			$this->ReturnError('Ошибка при получении данных');
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->library('parser');

		$path = EXPORTPATH_ROOT . "person_polis_list/";

		if ( !file_exists($path) ) {
			mkdir($path);
		}

		$out_dir = "re_dbf_" . time() . "_personPolisList";
		mkdir($path . $out_dir);

		$data['PersonPolis_ExportIndex'] = sprintf('%02d', trim($data['PersonPolis_ExportIndex'], '_'));

		if ( !empty($lpuData[0]['Lpu_f003mcod']) ) {
			$file_name = $lpuData[0]['Lpu_f003mcod'];
		}
		else {
			$file_name = preg_replace("/[^\w\d_ ]+/iu", "", trim($lpuData[0]['Lpu_Nick']));
			$file_name = preg_replace("/\s+/iu", "_", $file_name);
			$file_name = toAnsi($file_name, true);
		}

		$file_name .= date_format(date_create($data['PersonPolis_Date']), 'ym') . 'm' . $data['PersonPolis_ExportIndex'] . '001';

		$file_path = $path . $out_dir . "/" . $file_name . ". dbf";

		$file_def = array(
			array("NOM",		"N",	5, 0),
			array("FM",			"C",	30),
			array("IM",			"C",	30),
			array("OT",			"C",	30),
			array("V_P",		"C",	1),
			array("DR",			"D",	8),
			array("MR",			"C",	100),
			array("GRAG",		"C",	50),
			array("DOCTYPE",	"C",	100),
			array("PASPS",		"C",	10),
			array("PASPN",		"C",	10),
			array("PASPD",		"D",	8),
			array("PASPA",		"C",	30),
			array("MG",			"C",	100),
			array("MREG",		"C",	100),
			array("DREG",		"D",	8),
			array("SNILS",		"C",	14),
			array("POLS",		"C",	10),
			array("POLN",		"N",	20, 0),
			array("STRAHORGS",	"C",	100),
			array("STRAHORGD",	"D",	8),
			array("STATUS ",	"C",	20)
		);

		$h = dbase_create($file_path, $file_def);
		$i = 0;

		while ( $row = $list_object->_fetch_assoc() ) {
			$i++;
			array_walk_recursive($row, 'ConvertFromUTF8ToCp866');

			$row['NOM'] = $i;

			if ( !empty($row['SNILS']) ) {
				if ( strlen($row['SNILS']) == 11 ) {
					$row['SNILS'] = substr($row['SNILS'], 0, 3) . '-' . substr($row['SNILS'], 3, 3) . '-' . substr($row['SNILS'], 6, 3) . ' ' . substr($row['SNILS'], -2);
				}
				else if ( strlen($row['SNILS']) != 14 ) {
					$row['SNILS'] = '';
				}
			}

			dbase_add_record($h, array_values($row));
		}

		dbase_close($h);

		if ( $i == 0 ) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		$this->textlog->add("Задействовано памяти после записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");

		unset($list_object);

		$this->textlog->add("Задействовано памяти после очистки результатов запроса: " . memory_get_usage() . "\n\r");

		$file_zip_sign = $file_name;
		$file_zip_name = $path . $out_dir . "/" . $file_zip_sign . ".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		ConvertFromWin1251ToCp866($file_name);
		$zip->AddFile($file_path, $file_name . ".dbf" );
		$zip->close();

		unlink($file_path);

		if ( file_exists($file_zip_name) ) {
			$this->ReturnData(array('success' => true,'Link' => toUTF($file_zip_name, true), 'Count' => $i));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива!')));
		}

		return true;
	}
}
