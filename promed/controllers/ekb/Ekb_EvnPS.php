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
 * @version      	09.07.2014
 * @region       	Екатеринбург
 *
 * @property Ekb_EvnPS_model dbmodel
 */

require_once(APPPATH.'controllers/EvnPS.php');

class Ekb_EvnPS extends EvnPS {

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	function exportHospDataForTfomsToXml() {
		$data = $this->ProcessInputData('exportHospDataForTfomsToXml', true);
		if ($data === false) { return false; }

		set_time_limit(0);
		$this->load->library('parser');
		$resp = $this->dbmodel->exportHospDataForTfomsToXml($data);
		$hosp_data_xml_arr = $resp['hosp_data_xml_arr'];
		$lpu_rcode_arr = $resp['lpu_rcode_arr'];

		if (!is_array($hosp_data_xml_arr)) {
			$this->ReturnData(array('success' => false,'Error_Msg' => toUtf('Ошибка при получении данных')));
			return false;
		}

		$template = 'hosp_data_for_tfoms_ekb';
		$date = date_format(date_create($data['Date']), 'dmy');
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
			$rcode = $lpu_rcode_arr[$fcode];
			$rcode_str = strlen($rcode) < 4 ? sprintf("%04d", $rcode) : (string)$rcode;
			$number_str = strlen($number) < 2 ? sprintf("%02d", $number) : (string)$number;

			$file_zip_sign = 'm0_1'.$rcode_str.'_'.$date.'_'.$number_str;
			$file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";

			$zip = new ZipArchive();

			foreach($lpu_hosp_data_xml as $method => $data_xml) {
				if (empty($data_xml)) { continue; }

				$file_name = $method.'_1'.$rcode_str.'_'.$date.'_'.$number_str;
				$file_path = $path.$out_dir."/".$file_name.".xml";

				$dom = new DOMDocument();
				$res = $dom->loadXML('<root>'.$data_xml.'</root>');
				$count = $dom->getElementsByTagName('ZAP')->length;

				$hosp_data = array();
				$hosp_data['VERS'] = '2.0';
				$hosp_data['DATE_REPT'] = date_format(date_modify(date_create($data['Date']), '-' . ($method == 'm4'? 0 : 1) . ' day'), 'Y-m-d');
				$hosp_data['TYPECONT'] = ($method=='m3'?1:null);
				$hosp_data['MO'] = $fcode;
				$hosp_data['RCOUNT'] = $count;
				$hosp_data['ZAP'] = $data_xml;

				$template = 'hosp_data_for_tfoms_ekb_main' . ($method == 'm3' ? '_m3' : '');

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/'.$template, $hosp_data, true), true);

				file_put_contents($file_path, $xml);

				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_path, $file_name . ".xml" );
				$zip->close();

				unlink($file_path);
				$xml_count++;
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
		/*if (count($file_name_arr) > 1) {
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
		}*/

		if (file_exists($file_zip_name))
		{
			$this->ReturnData(array('success' => true,'Link' => $file_zip_name));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива!')));
		}
		return true;
	}
}