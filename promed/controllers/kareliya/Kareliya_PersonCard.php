<?php	defined('BASEPATH') or die ('No direct script access allowed');
require_once(APPPATH.'controllers/PersonCard.php');

class Kareliya_PersonCard extends PersonCard {
	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Функция возвращает в XML список прикрепленного населения к указанной СМО на указанную дату
	 */
	function loadAttachedList()
	{
		$data = $this->ProcessInputData('loadAttachedList', true);
		if ($data === false) { return false; }

		if ( !isExpPop() ) {
			$this->ReturnError('Функционал недоступен');
			return false;
		}

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		$attached_list_data = $this->pcmodel->loadAttachedList($data);

		if ($attached_list_data === false)
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
			return false;
		}
		if ( !empty($attached_list_data['Error_Code']) && ($attached_list_data['Error_Code'] == 1) )
		{
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данные по прикрепленному населению при указанных параметрах в базе данных отсутствуют.')));
			return false;
		}

		$this->load->library('parser');

		if (!file_exists(EXPORTPATH_ATACHED_LIST))
			mkdir( EXPORTPATH_ATACHED_LIST );
		
		$links = array();
		
		// каталог в котором лежат выгружаемые файлы
		$out_dir = "re_xml_".time()."_"."attachedList";
		mkdir( EXPORTPATH_ATACHED_LIST.$out_dir );
		$date = date_format(date_create($data['Date_upload']), 'Ymd');

		$file_zip_sign = 'ATT_LIST';
		/*$main_file_zip_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign.".zip";
		
		
		
		if (file_exists($main_file_zip_name)) {
			unlink($main_file_zip_name);
		}*/

		$mainZIP = new ZipArchive();

		//Формируются xml-файлы по СМО
		foreach($attached_list_data['Smo_Pers'] as $smo) {
			$zglv = $smo['ZGLV'][0];
			$attached_list_file_name = "P".$zglv['SMO'].$zglv['CODE_MO']."_".$date;
			// файл-перс. данные
			$attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$attached_list_file_name.".xml";

			$smo['ZGLV'][0]['FILENAME'] = $attached_list_file_name;

			$smo = toAnsiR($smo, true);

			$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse_ext('export_xml/person_kareliya', $smo, true);
			$xml = str_replace('&', '&amp;', $xml);

			file_put_contents($attached_list_file_path, $xml);

			$file_zip_sign = $attached_list_file_name;// . ".zip";
			$file_zip_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign.".zip";
			//var_dump($file_zip_name);die;
			if (file_exists($file_zip_name)) {
				unlink($file_zip_name);
			}

			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			$zip->AddFile( $attached_list_file_path, $attached_list_file_name . ".xml" );
			$zip->close();
			$links[] = $file_zip_name;//EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign;//$file_zip_name;
			
			//var_dump($links);die;
			/*$mainZIP->open($main_file_zip_name, ZIPARCHIVE::CREATE);
			$mainZIP->AddFile( $file_zip_name, $attached_list_file_name . ".zip" );
			$mainZIP->close();*/

			unlink($attached_list_file_path);
			//unlink($file_zip_name);
		}

		if (!empty($attached_list_data['Errors'])) {
			$errors_file_name = "errors";
			$errors_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$errors_file_name.".txt";
			$errors_str = "";
			foreach($attached_list_data['Errors'] as $error) {
				$errors_str .= "ИНН: ".$error['Org_INN'];
				$errors_str .= ", ОГРН: ".$error['Org_OGRN'];
				$errors_str .= ", наименование: ".$error['Org_Nick'];
				$errors_str .= ", адрес: ".$error['Address_Address'];
				$errors_str .= "\r\n";
			}
			file_put_contents($errors_file_path, $errors_str);

			/*$mainZIP->open($main_file_zip_name, ZIPARCHIVE::CREATE);
			$mainZIP->AddFile( $errors_file_path, $errors_file_name . ".txt" );
			$mainZIP->close();*/

			unlink($errors_file_path);
		}

		/*if (file_exists($main_file_zip_name))
		{
			$this->ReturnData(array('success' => true,'Link' => $main_file_zip_name));
		}*/
		if(!empty($links))
		{
		$this->ReturnData(array('success' => true,'Link' => $links));
		}
		else {
			$this->ReturnError('Ошибка создания архива реестра!');
		}

		return true;
	}
}
?>