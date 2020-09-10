<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnPS - контроллер для работы с картами выбывшего из стационара (КВС)
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Stac
 * @access			public
 * @copyright		Copyright (c) 2009 Swan Ltd.
 * @author       Kirill Sabirov (ksabirov@swan.perm.ru)
 * @version      	18.06.2014
 * @region       	Астрахань
 *
 * @property User_model dbmodel
 */

require_once(APPPATH.'controllers/EvnPS.php');

class Astra_EvnPS extends EvnPS {

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	function exportHospDataForTfomsToXml() {
		$data = $this->ProcessInputData('exportHospDataForTfomsToXml', true);
		if ($data === false) { return false; }

		set_time_limit(0);
		$hosp_data_xml_arr = $this->dbmodel->exportHospDataForTfomsToXml($data);
		if (!is_array($hosp_data_xml_arr)) {
			$this->ReturnData(array('success' => false,'Error_Msg' => toUtf('Ошибка при получении данных')));
			return false;
		}

		$this->load->library('parser');
		$template = 'hosp_data_for_tfoms_astra';
		$date = date_format(date_create($data['Date']), 'ym');
		$number = 1;

		$path = EXPORTPATH_ROOT."hosp_data_for_tfoms/";

		if (!file_exists($path)) {
			mkdir( $path );
		}

		$out_dir = "re_xml_".$data['Date']."_"."hospDataForTfoms";
		if (!file_exists($path.$out_dir)) {
			mkdir( $path.$out_dir );
		} else {
			$files = scandir($path.$out_dir);
			if (is_array($files)) {
				foreach($files as $file) {
					if (preg_match('/\w+.zip$/', $file) > 0) {
						$number++;
					}
				}
			}
		}

		$file_name_arr = array();
		$xml_count = 0;

		foreach($hosp_data_xml_arr as $fcode => $lpu_hosp_data_xml) {
			if (!is_array($lpu_hosp_data_xml)) {continue;}

			$file_zip_sign = 'hosp_data_for_tfoms_'.$number;
			$file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";

			$file_errors_sign = 'hosp_data_errors';
			$file_errors_name = $path.$out_dir."/".$file_errors_sign.".txt";

			$zip = new ZipArchive();

			foreach($lpu_hosp_data_xml as $method => $data_xml) {
				if (empty($data_xml)) { continue; }

				$file_name = $method."M".$fcode."T30_".$date.$number;
				$file_path = $path.$out_dir."/".$file_name.".xml";

				$hosp_data = array();
				$hosp_data['VERSION'] = '1.0';
				$hosp_data['DATA'] = $data['Date'];
				$hosp_data['FILENAME'] = $file_name;
				$hosp_data['NPR'] = $data_xml;

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/'.$template, $hosp_data, true), true);

				file_put_contents($file_path, $xml);

				$error_list_data = $this->dbmodel->checkXmlDataOnErrors($method, $xml);
				if (count($error_list_data) > 0) {
					file_put_contents($file_errors_name, implode("\n", $error_list_data), FILE_APPEND);
				}

				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_path, $file_name . ".xml" );
				$zip->close();

				unlink($file_path);
				$xml_count++;
			}
			if (file_exists($file_zip_name) && file_exists($file_errors_name)) {
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile($file_errors_name, $file_errors_sign.".txt");
				$zip->close();

				unlink($file_errors_name);
			}
			if (file_exists($file_zip_name)) {
				$file_name_arr[$file_zip_sign] = $file_zip_name;
				$number++;
			}
		}

		if ($xml_count == 0) {
			$this->ReturnData(array('success' => false,'Error_Msg' => toUtf('Нет данных для выгрузки')));
			return false;
		}

		//Формируем пакет с пакетами для админа ЦОД
		if (count($file_name_arr) > 1) {
			$file_zip_sign = 'hosp_data_for_tfoms';
			$file_zip_path = $path.$out_dir."/package_".time();
			$file_zip_name = $file_zip_path."/".$file_zip_sign.".zip";

			if (!file_exists($file_zip_path)) {
				mkdir( $file_zip_path );
			}

			foreach($file_name_arr as $sign => $name) {
				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $name, $sign . ".zip" );
				$zip->close();
			}
		}

		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true,'Link' => $file_zip_name));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива!')));
		}
		return true;
	}

	/**
	 * Импорт данных из ТФОМС и СМО
	 */
	function importHospDataFromTfomsXml() {
		set_time_limit(0);
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "2048");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "2048");

		$this->load->library('textlog', array('file' => 'ImportHospData.log'));

		$upload_path = './'.IMPORTPATH_ROOT.'importHospDataFromTfoms/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|rar|xml');

		$data = $this->ProcessInputData('importHospDataFromTfomsXml', true);
		if ($data === false) { return false; }

		if (!isset($_FILES['ImportFile'])) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100011 , 'Error_Msg' => toUTF('Файл для импорта!') ) ) ;
			return false;
		}

		if (!is_uploaded_file($_FILES['ImportFile']['tmp_name']))
		{
			$error = (!isset($_FILES['ImportFile']['error'])) ? 4 : $_FILES['ImportFile']['error'];
			switch($error)
			{
				case 1:
					$message = 'Загружаемый файл превышает максимально допустимый размер, определённый в вашем файле конфигурации PHP.';
					break;
				case 2:
					$message = 'Загружаемый файл превышает максимально допустимый размер, заданный формой.';
					break;
				case 3:
					$message = 'Этот файл был загружен не полностью.';
					break;
				case 4:
					$message = 'Вы не выбрали файл для загрузки.';
					break;
				case 6:
					$message = 'Временная директория не найдена.';
					break;
				case 7:
					$message = 'Файл не может быть записан на диск.';
					break;
				case 8:
					$message = 'Неверный формат файла.';
					break;
				default :
					$message = 'При загрузке файла произошла ошибка.';
					break;
			}
			$this->ReturnData(array('success' => false, 'Error_Code' => 100012 , 'Error_Msg' => toUTF($message)));
			return false;
		}

		// Тип файла разрешен к загрузке?
		$x = explode('.', $_FILES['ImportFile']['name']);
		$file_data['file_ext'] = end($x);
		if (!in_array(strtolower($file_data['file_ext']), $allowed_types)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100013 , 'Error_Msg' => toUTF('Данный тип файла не разрешен.')));
			return false;
		}

		// Правильно ли указана директория для загрузки?
		$path = '';
		$folders = explode('/', $upload_path);
		for($i=0; $i<count($folders); $i++) {
			if ($folders[$i] == '') {continue;}
			$path .= $folders[$i].'/';
			if (!@is_dir($path)) {
				mkdir( $path );
			}
		}
		if (!@is_dir($upload_path)) {
			$this->ReturnData(array('success' => false, 'Error_Code' => 100014 , 'Error_Msg' => toUTF('Путь для загрузки файлов некорректен.')));
			return false;
		}

		// Имеет ли директория для загрузки права на запись?
		if (!is_writable($upload_path)) {
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Загрузка файла не возможна из-за прав пользователя.')));
			return false;
		}

		$xmlfiles = array();
		if (strtolower($file_data['file_ext']) == 'xml') {
			$filename = $_FILES['ImportFile']['name'];
			$xmlfiles[] = $filename;
			if (!move_uploaded_file($_FILES["ImportFile"]["tmp_name"], $upload_path.$filename)){
				$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не удаётся переместить файл.')));
				return false;
			}
		} else {
			// там должен быть файл a*.xml, если его нет -> файл не является архивом реестра
			$zip = new ZipArchive;
			if ($zip->open($_FILES["ImportFile"]["tmp_name"]) === TRUE)
			{
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/n.*.xml/i', strtolower($filename)) > 0 ) {
						$xmlfiles[] = $filename;
					}
				}

				$zip->extractTo( $upload_path );
				$zip->close();
			}
			unlink($_FILES["ImportFile"]["tmp_name"]);
		}

		if (count($xmlfiles) == 0)
		{
			$this->ReturnData( array('success' => false, 'Error_Code' => 100015 , 'Error_Msg' => toUTF('Не обнаружены файлы для импорта[1].')));
			return false;
		}

		$recall = 0;
		$recext = 0;
		$recint = 0;
		$recident = 0;
		$recupdate = 0;

		libxml_use_internal_errors(true);

		$log_files_list = array();
		$log_dir_name = 'log_'.time();
		$log_path = EXPORTPATH_ROOT.'hosp_data_for_tfoms/'.$log_dir_name.'/';

		foreach($xmlfiles as $xmlfile) {
			$dom = new DOMDocument();
			$res = $dom->load($upload_path . $xmlfile);

			foreach (libxml_get_errors() as $error) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 100015, 'Error_Msg' => toUTF('Не обнаружены файлы для импорта[2].')));
				return false;
			}
			libxml_clear_errors();

			$method = '';
			$dom_method = $dom->getElementsByTagName('FILENAME');
			foreach ($dom_method as $dom_onemethod) {
				$method = substr($dom_onemethod->nodeValue, 0, 2);
			}

			if (!in_array($method, array('N1', 'N2', 'N3', 'N5'))) {
				continue;
			}

			$stat = array('all' => 0, 'ident' => 0, 'ext' => 0, 'int' => 0, 'upd' => 0);	//Стат. данные по одному файлу
			$error_log = array();
			$beg_time = date('d.m.Y H:i:s');

			try{
				$tmpTableName = $this->dbmodel->saveHospDataInTmpTable($dom, $stat);						//Сохранение данных из файла во временную таблицу
				if ($stat['all'] > 0) {
					$this->dbmodel->identHospData($tmpTableName, $method, $stat, $error_log);					//Идентификация данных
					$this->dbmodel->saveEvnDirectionExt($tmpTableName, $data['session'], $stat, $error_log);	//Обновление данных внешних направлений
					$this->dbmodel->saveEvnDirectionInt($tmpTableName, $data['session'], $stat, $error_log);	//Обновление данных внутренних направлений
				}
			} catch (Exception $e) {
				$this->ReturnData(array('success' => false, 'Error_Code' => 100016, 'Error_Msg' => $e->getMessage()));
				return false;
			}

			$recall += $stat['all'];
			$recext += $stat['ext'];
			$recint += $stat['int'];
			$recident += $stat['ident'];
			$recupdate += $stat['upd'];

			$end_time = date('d.m.Y H:i:s');

			if (preg_match('/^(?<filename>.+).xml$/i', $xmlfile, $matches)) {
				$filename = $matches['filename'].'_ЛОГ.txt';
				$log_files_list[] = $filename;

				$lines = array_merge(
					array($xmlfile, "Записей в файле {$stat['all']}", "Время запуска {$beg_time}"),
					$error_log,
					array("Принятых записей {$stat['ident']}", "Время окончания {$end_time}")
				);

				if (!file_exists($log_path)) {
					mkdir($log_path);
				}

				file_put_contents(toAnsi($log_path.$filename, true), implode("\n", $lines));
			}
		}

		$log_link = null;
		if (count($log_files_list) == 1) {
			$log_link = $log_path.$log_files_list[0];
		} else if (count($log_files_list) > 1) {
			$zip = new ZipArchive();
			$zip->open($log_path.$log_dir_name.'.zip', ZIPARCHIVE::CREATE);
			foreach($log_files_list as $file) {
				$zip->AddFile(toAnsi($log_path.$file, true), iconv('UTF-8', 'cp866//IGNORE', $file));
			}
			$zip->close();
			$log_link = $log_path.$log_dir_name.'.zip';
		}

		$this->ReturnData(array('success' => true, 'recall' => $recall, 'recext' => $recext, 'recint' => $recint, 'recident' => $recident, 'recupdate' => $recupdate, 'log_link' => $log_link));
		return true;
	}
}