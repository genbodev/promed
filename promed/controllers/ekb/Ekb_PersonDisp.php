<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/PersonDisp.php');

class Ekb_PersonDisp extends PersonDisp {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		$this->inputRules['exportPersonDispForPeriod'] = array_merge($this->inputRules['exportPersonDispForPeriod'], array(
			array('field' => 'FileCreationDate', 'label' => 'Дата формирования файла', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'ReportDate', 'label' => 'Отчетная дата', 'rules' => 'trim|required', 'type' => 'date'),
			array('field' => 'PackageNum', 'label' => 'Порядковый номер пакета', 'rules' => 'trim|required', 'type' => 'int'),
			array('field' => 'TypeFilterLpuAttach_id', 'label' => 'МО прикрепления', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TypeFilterLpuCard_id', 'label' => 'МО карты дисп. наблюдения', 'rules' => 'required', 'type' => 'id'),
		));
	}

	/**
	 * Выгрузка списка карт диспансерного наблюдения за период
	 */
	public function exportPersonDispForPeriod() {
		set_time_limit(0);
		ini_set("memory_limit", "2048M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");

		$data = $this->ProcessInputData('exportPersonDispForPeriod', true);
		if ($data === false) { return false; }

		if ( !isSuperadmin() && !isLpuAdmin($data['Lpu_id']) ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		$this->load->library('textlog', array('file' => 'exportPersonDispForPeriod_' . date('Y-m-d') . '.log'));
		$this->textlog->add("\n\r");
		$this->textlog->add("exportPersonDispForPeriod: Запуск" . "\n\r");
		$this->textlog->add("Регион: " . $data['session']['region']['nick'] . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->model("Polka_PersonCard_model", "pcmodel");

		$fileInfo = $this->pcmodel->getInfoForAttachesFile(array('AttachesLpu_id' => $data['Lpu_id']));
		$exportData = $this->dbmodel->exportPersonDispForPeriod($data);

		if ( !is_object($exportData) ) {
			$this->ReturnError('Ошибка при получении данных');
			return false;
		}

		$row = $exportData->_fetch_assoc();


		if ( !is_array($row) || count($row) == 0 ) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}

		$this->textlog->add("Задействовано памяти после выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->library('parser');

		$path = EXPORTPATH_ROOT . "person_disp_list/";

		if ( !file_exists($path) ) {
			mkdir($path);
		}

		$out_dir = "disp_" . time() . "_" . $data['pmUser_id'];

		mkdir($path . $out_dir);

		$file_name = $fileInfo[0]['Lpu_f003mcod'] . "_DU_" . date_format(date_create($data['ReportDate']), 'Ymd') . "_" . sprintf('%03d', $data['PackageNum']);

		$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";

		while ( file_exists($file_path) ) {
			$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";
		}

		$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";

		while ( file_exists($file_path_tmp) ) {
			$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";
		}

		// Основные данные
		$i = 1;
		$mainData = array();
		$row['nomer_z'] = $i;

		foreach ( $row as $key => $value ) {
			if ( $value instanceof DateTime ) {
				$row[$key] = $value->format('Ymd');
			}
		}

		if (!empty($row['docser']) && !empty($row['doctype']) && $row['doctype'] == '14') {
			$row['docser'] = str_replace(' ', '', $row['docser']);
			$row['docser'] = mb_substr($row['docser'],0,2).' '.mb_substr($row['docser'],2,2);
		}
		if (empty($row['docser'])) {
			$row['docser'] = '1';
		}
		if (empty($row['docnum'])) {
			$row['docnum'] = '1';
		}

		$mainData[] = $row;

		$template = "person_disp_body";

		while ( $row = $exportData->_fetch_assoc() ) {
			$i++;
			$row['nomer_z'] = $i;

			foreach ( $row as $key => $value ) {
				if ( $value instanceof DateTime ) {
					$row[$key] = $value->format('Ymd');
				}
			}

			if (!empty($row['docser']) && !empty($row['doctype']) && $row['doctype'] == '14') {
				$row['docser'] = str_replace(' ', '', $row['docser']);
				$row['docser'] = mb_substr($row['docser'],0,2).' '.mb_substr($row['docser'],2,2);
			}
			if (empty($row['docser'])) {
				$row['docser'] = '1';
			}
			if (empty($row['docnum'])) {
				$row['docnum'] = '1';
			}

			$mainData[] = $row;

			if ( count($mainData) == 1000 ) {
				array_walk_recursive($mainData, 'ConvertFromUTF8ToWin1251', true);
				$xml = $this->parser->parse('export_xml/' . $template, array('zl' => $mainData), true);
				$xml = str_replace('&', '&amp;', $xml);

				file_put_contents($file_path_tmp, $xml, FILE_APPEND);

				unset($xml);

				$mainData = array();

				$this->textlog->add("Задействовано памяти после выполнения записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");
			}
		}

		if ( count($mainData) > 0 ) {
			array_walk_recursive($mainData, 'ConvertFromUTF8ToWin1251', true);
			$xml = $this->parser->parse('export_xml/' . $template, array('zl' => $mainData), true);
			$xml = str_replace('&', '&amp;', $xml);

			file_put_contents($file_path_tmp, $xml, FILE_APPEND);

			unset($xml);
		}

		$this->textlog->add("Задействовано памяти после записи в файл " . $i . " записей: " . memory_get_usage() . "\n\r");

		unset($exportData);
		unset($mainData);

		$this->textlog->add("Задействовано памяти после очистки результатов запроса: " . memory_get_usage() . "\n\r");

		// Пишем данные в основной файл

		// Заголовок файла
		$template = 'person_disp_header';

		$zglv = array(
			 'filename' => $file_name
			,'data' => str_replace('-', '', $data['FileCreationDate'])
			,'codmof' => $fileInfo[0]['Lpu_f003mcod']
			,'dn' => str_replace('-', '', $data['ExportDateRange'][0])
			,'dk' => str_replace('-', '', $data['ExportDateRange'][1])
			,'dt_report' => str_replace('-', '', $data['ReportDate'])
			,'nfile' => $data['PackageNum']
			,'nrec' => $i
		);

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse('export_xml/' . $template, $zglv, true);
		$xml = str_replace('&', '&amp;', $xml);
		file_put_contents($file_path, $xml, FILE_APPEND);

		// Тело файла начитываем из временного
		// Заменяем простую, но прожорливую конструкцию, на чтение побайтно
		// https://redmine.swan.ekb.ru/issues/154409
		// file_put_contents($file_path, file_get_contents($file_path_tmp), FILE_APPEND);

		$fh = fopen($file_path_tmp, "rb");

		if ( $fh === false ) {
			$this->ReturnError('Ошибка при открытии файла');
			return false;
		}

		// Устанавливаем начитываемый объем данных
		$chunk = 10 * 1024 * 1024; // 10 MB

		while ( !feof($fh) ) {
			file_put_contents($file_path, fread($fh, $chunk), FILE_APPEND);
		}

		fclose($fh);

		// Конец файла
		$template = 'person_disp_footer';

		$xml = $this->parser->parse('export_xml/' . $template, array(), true);
		$xml = str_replace('&', '&amp;', $xml);
		file_put_contents($file_path, $xml, FILE_APPEND);

		$file_zip_sign = $file_name;
		$file_zip_name = $path . $out_dir . "/" . $file_zip_sign . ".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile($file_path, $file_name . ".xml");
		$zip->close();

		unlink($file_path);
		unlink($file_path_tmp);

		if ( file_exists($file_zip_name) ) {
			$this->ReturnData(array('success' => true, 'Link' => $file_zip_name, 'Count' => $i));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка создания архива!'));
		}

		return true;
	}
}