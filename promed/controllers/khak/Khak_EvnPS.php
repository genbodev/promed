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
 * @author       	Kirill Sabirov (ksabirov@swan.perm.ru)
 * @version      	15.07.2014
 * @region       	Хакасия
 *
 * @property Khak_EvnPS_model dbmodel
 */

require_once(APPPATH.'controllers/EvnPS.php');

class Khak_EvnPS extends EvnPS {

	/**
	 * Выгрузка данных для ТФОМС и СМО
	 */
	function exportHospDataForTfomsToXml() {
		$data = $this->ProcessInputData('exportHospDataForTfomsToXml', true);
		if ($data === false) { return false; }

		set_time_limit(0);
		$hosp_data_xml_arr = $this->dbmodel->exportHospDataForTfomsToXml($data);
		if (!is_array($hosp_data_xml_arr)) {
			$this->ReturnData(array('success' => true,'Error_Msg' => toUtf('Ошибка при получении данных')));
		}

		$this->load->library('parser');
		$template = 'hosp_data_for_tfoms_khak';
		$from = ($data['ARMType'] == 'superadmin' || $data['ARMType'] == 'tfoms') ? 'F' : 'M';

		$path = EXPORTPATH_ROOT."hosp_data_for_tfoms/";

		if (!file_exists($path)) {
			mkdir( $path );
		}

		$out_dir = "re_xml_".time()."_"."hospDataForTfoms_khak";
		if (!file_exists($path.$out_dir)) {
			mkdir( $path.$out_dir );
		}

		$file_name_arr = array();
		$xml_count = 0;
		$currentDate = date('Y-m-d');
		foreach($hosp_data_xml_arr as $fcode => $lpu_hosp_data_xml) {
			if (!is_array($lpu_hosp_data_xml)) {continue;}

			$file_zip_sign = 'hosp_data_for_tfoms';
			$file_zip_name = $path.$out_dir."/".$file_zip_sign.".zip";

			$zip = new ZipArchive();

			foreach($lpu_hosp_data_xml as $method => $data_xml) {
				if (empty($data_xml)) { continue; }

				$file_name = $method."-".$from."-".$fcode."-".$currentDate;
				$file_path = $path.$out_dir."/".$file_name.".xml";

				$hosp_data = array();
				$hosp_data['DATA'] = $currentDate;
				$hosp_data['FILENAME'] = $file_name.".xml";
				$hosp_data['ZAP'] = $data_xml;

				$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\r\n" . toAnsi($this->parser->parse('export_xml/'.$template, $hosp_data, true), true);

				file_put_contents($file_path, $xml);

				$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
				$zip->AddFile( $file_path, $file_name . ".xml" );
				$zip->close();

				unlink($file_path);
				$xml_count++;
			}
		}

		if ($xml_count == 0) {
			$this->ReturnData(array('success' => false,'Error_Msg' => toUtf('Нет данных для выгрузки')));
			return false;
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
}