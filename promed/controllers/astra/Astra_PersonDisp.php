<?php defined('BASEPATH') or die('No direct script access allowed');

require_once(APPPATH.'controllers/PersonDisp.php');

class Astra_PersonDisp extends PersonDisp {
	/**
	 * Конструктор
	 */
	public function __construct() {
		parent::__construct();

		$this->inputRules['exportPersonDispForPeriod'] = array_merge($this->inputRules['exportPersonDispForPeriod'], array(
			array('field' => 'OrgSMO_id', 'label' => 'СМО', 'rules' => '', 'type' => 'id'),
			array('field' => 'PackageNum', 'label' => 'Порядковый номер пакета', 'rules' => 'trim|required', 'type' => 'int'),
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
		$this->textlog->add("Запуск" . "\n\r");
		$this->textlog->add("Регион: " . $data['session']['region']['nick'] . "\n\r");

		$this->textlog->add("Задействовано памяти до выполнения запроса на получение данных: " . memory_get_usage() . "\n\r");

		$this->load->model("Polka_PersonCard_model", "pcmodel");

		$fileInfo = $this->pcmodel->getInfoForAttachesFile(array('AttachesLpu_id' => $data['Lpu_id']));
		$exportData = $this->dbmodel->exportPersonDispForPeriod($data);

		if ( !is_object($exportData) ) {
			$this->ReturnError('Ошибка при получении данных');
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

		if ( !empty($data['OrgSMO_id']) ) {
			$Orgsmo_f002smocod = $this->dbmodel->getFirstResultFromQuery("select top 1 Orgsmo_f002smocod from v_OrgSMO with (nolock) where OrgSMO_id = :OrgSMO_id", array('OrgSMO_id' => $data['OrgSMO_id']));

			if ( $Orgsmo_f002smocod === false ) {
				$this->ReturnError('Ошибка при получении реестрового кода СМО');
				return false;
			}
		}

		$X = 'DN'; // параметр, обозначающий передаваемые данные (DN- диспансерное наблюдение)
		$Pi = 'M'; // Параметр, определяющий организацию-источник: M – МО
		$Ni = $fileInfo[0]['Lpu_f003mcod']; // Номер источника (реестровый номер МО)
		$Pp = (!empty($data['OrgSMO_id']) ? 'S' : 'T'); // Параметр, определяющий организацию-получателя: S – СМО, T – ТФОМС
		$Np = (!empty($Orgsmo_f002smocod) ? $Orgsmo_f002smocod : '30'); // Номер получателя (двузначный код ТФОМС или реестровый номер СМО)

		$file_name = $X . $Pi . $Ni . $Pp . $Np . "_" . date('ymd') . '_' . sprintf('%04d', $data['PackageNum']);

		$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";

		while ( file_exists($file_path) ) {
			$file_path = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . ".xml";
		}

		$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";

		while ( file_exists($file_path_tmp) ) {
			$file_path_tmp = $path . $out_dir . "/" . swGenRandomString() . "_" . $file_name . "_tmp.xml";
		}

		$DN = array();
		$IDCASE = 0;
		$N_ZAP = 0;
		$Person_id = 0;
		$template = "person_disp_astra_body";
		$ZAP = array();

		while ( $row = $exportData->_fetch_assoc() ) {
			foreach ( $row as $key => $value ) {
				if ( $value instanceof DateTime ) {
					$row[$key] = $value->format('Y-m-d');
				}
			}

			if ( $row['Person_id'] != $Person_id ) {
				if ( count($ZAP) == 1000 ) {
					array_walk_recursive($ZAP, 'ConvertFromUTF8ToWin1251', true);
					$xml = $this->parser->parse('export_xml/' . $template, array('ZAP' => $ZAP), true);
					$xml = str_replace('&', '&amp;', $xml);
					$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);

					file_put_contents($file_path_tmp, $xml, FILE_APPEND);

					unset($xml);

					$ZAP = array();

					$this->textlog->add("Задействовано памяти после выполнения записи в файл 1000 записей: " . memory_get_usage() . "\n\r");
				}

				$Person_id = $row['Person_id'];
				$N_ZAP++;

				$ZAP[$N_ZAP] = array(
					'N_ZAP' => $N_ZAP,
					'YEAR' => substr($data['ExportDateRange'][1], 0, 4),
					'FAM' => $row['FAM'],
					'IM' => $row['IM'],
					'OT' => $row['OT'],
					'DR' => $row['DR'],
					'TEL' => $row['TEL'],
					'DN' => array(),
				);
			}

			$IDCASE++;

			$ZAP[$N_ZAP]['DN'][] = array(
				'IDCASE' => $IDCASE,
				'PROFIL' => $row['PROFIL'],
				'DS' => $row['DS'],
				'D_BEG' => $row['D_BEG'],
				'D_END' => $row['D_END'],
				'END_RES' => $row['END_RES'],
			);
		}

		/*if ( $N_ZAP == 0 ) {
			$this->ReturnError('Отсутствуют данные для выгрузки');
			return false;
		}*/

		if ( count($ZAP) > 0 ) {
			array_walk_recursive($ZAP, 'ConvertFromUTF8ToWin1251', true);
			$xml = $this->parser->parse('export_xml/' . $template, array('ZAP' => $ZAP), true);
			$xml = str_replace('&', '&amp;', $xml);
			$xml = preg_replace("/\R\s*\R/", "\r\n", $xml);

			file_put_contents($file_path_tmp, $xml, FILE_APPEND);

			unset($xml);

			$this->textlog->add("Задействовано памяти после выполнения записи в файл " . count($ZAP) . " записей: " . memory_get_usage() . "\n\r");
		}
		else {
			file_put_contents($file_path_tmp, '', FILE_APPEND);
		}

		unset($exportData);
		unset($ZAP);

		$this->textlog->add("Задействовано памяти после очистки результатов запроса: " . memory_get_usage() . "\n\r");

		// Пишем данные в основной файл

		// Заголовок файла
		$template = 'person_disp_astra_header';

		$ZGLV = array(
			'FNAME' => $file_name
		);

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . $this->parser->parse('export_xml/' . $template, $ZGLV, true);
		$xml = str_replace('&', '&amp;', $xml);
		file_put_contents($file_path, $xml, FILE_APPEND);

		// Тело файла начитываем побайтно из временного

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
		$template = 'person_disp_astra_footer';

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
			$this->ReturnData(array('success' => true, 'Link' => $file_zip_name, 'Count' => $N_ZAP));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => 'Ошибка создания архива!'));
		}

		return true;
	}
}