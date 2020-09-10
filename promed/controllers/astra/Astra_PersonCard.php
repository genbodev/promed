<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* PersonCard - контроллер для выполенния операций с картотекой пациентов (Астрахань).
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

/**
 * @property Polka_PersonCard_model $pcmodel
 */
require_once(APPPATH.'controllers/PersonCard.php');

class Astra_PersonCard extends PersonCard {
	/**
	 *	Конструктор
	 */
	function __construct() {
		parent::__construct();

		// Меняем правила для некоторых полей
		foreach ( $this->inputRules['loadAttachedList'] as $key => $array ) {
			switch ( $array['field'] ) {
				case 'AttachLpu_id':
					$this->inputRules['saveLpuPassport'][$key]['rules'] = 'required';
				break;
			}
		}
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

		if ( $attached_list_data === false ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF($this->error_deadlock)));
			return false;
		}
		else if ( !empty($attached_list_data['Error_Code']) && $attached_list_data['Error_Code'] == 1 ) {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Данные по прикрепленному населению при указанных параметрах в базе данных отсутствуют.')));
			return false;
		}

		array_walk_recursive($attached_list_data, 'ConvertFromUTF8ToWin1251', true);
		$IDCASE = 0;
		foreach($attached_list_data['ZAP'] as $key => $value) {
			// Формируем правильный СНИЛС с разделителями
			if (!empty($value['SNILS']) && mb_strlen($value['SNILS']) == 11) {
				$attached_list_data['ZAP'][$key]['SNILS'] = mb_substr($value['SNILS'],0,3).'-'.mb_substr($value['SNILS'],3,3).'-'.mb_substr($value['SNILS'],6,3).' '.mb_substr($value['SNILS'],9,2);
			} else {
				$attached_list_data['ZAP'][$key]['SNILS'] = '';
			}
			if (!empty($value['SSD']) && mb_strlen($value['SSD']) == 11) {
				$attached_list_data['ZAP'][$key]['SSD'] = mb_substr($value['SSD'],0,3).'-'.mb_substr($value['SSD'],3,3).'-'.mb_substr($value['SSD'],6,3).' '.mb_substr($value['SSD'],9,2);
			} else {
				$attached_list_data['ZAP'][$key]['SSD'] = '';
			}

			if($value['Field_Type'] == '2'){ //Если это заявление, то ему нужно поставить статус "Отправлено в СМО"
				$change_params = array(
					'PersonCardAttach_id' => $value['Field_id'],
					'PersonCardAttachStatusType_id'	=> 8,
					'pmUser_id' => $_SESSION['pmuser_id']
				);
				$this->pcmodel->changePersonCardAttachStatus($change_params);
			}

			// Указываем порядковый номер записи
			$IDCASE++;
			$attached_list_data['ZAP'][$key]['IDCASE'] = $IDCASE;
		}

		$this->load->library('parser');

		if ( !file_exists(EXPORTPATH_ATACHED_LIST) ) {
			if ( @mkdir(EXPORTPATH_ATACHED_LIST) === false ) {
				$this->ReturnError('Ошибка при создании директории для выгрузки');
				return false;
			}
		}
		// каталог в котором лежат выгружаемые файлы
		$out_dir = "re_xml_".time()."_"."attachedList";

		if ( @mkdir(EXPORTPATH_ATACHED_LIST . $out_dir) === false ) {
			$this->ReturnError('Ошибка при создании директории для выгрузки');
			return false;
		}

		// @task https://redmine.swan.perm.ru/issues/98649
		$attached_list_file_mask = "P" . $attached_list_data['ZGLV'][0]['N_REESTR'] . "S" . $attached_list_data['ZGLV'][0]['SMO_CODE'];
		$attached_list_file_period = date('ym');

		$this->load->model('Utils_model', 'Utils_model');

		$index = $this->Utils_model->genObjectValue(array(
			'Lpu_id' => $data['Lpu_id'],
			'ObjectName' => $attached_list_file_mask,
			'ObjectValue' => $attached_list_file_period,
		));

		if ( $index === false ) {
			$index = 1;
		}

		$attached_list_file_name = $attached_list_file_mask . '_' . $attached_list_file_period . $data['PackageNum'];

		// файл-перс. данные
		$attached_list_file_path = EXPORTPATH_ATACHED_LIST.$out_dir."/".$attached_list_file_name.".xml";

		$attached_list_data['ZGLV'][0]['FILENAME'] = $attached_list_file_name;

		// Шаблон
		$templ = "person_astra";

		$xml = "<?xml version=\"1.0\" encoding=\"windows-1251\" standalone=\"yes\"?>\r\n" . $this->parser->parse('export_xml/'.$templ, $attached_list_data, true);
		$xml = str_replace('&', '&amp;', $xml);

		file_put_contents($attached_list_file_path, $xml);

		$file_zip_sign = $attached_list_file_name;
		$file_zip_name = EXPORTPATH_ATACHED_LIST.$out_dir."/".$file_zip_sign.".zip";
		$zip = new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $attached_list_file_path, $attached_list_file_name . ".xml" );
		$zip->close();

		unlink($attached_list_file_path);

		if (file_exists($file_zip_name)) {
			$this->ReturnData(array('success' => true,'Link' => $file_zip_name/*, 'Doc' => $attached_list_data['DOC']*/));
		}
		else {
			$this->ReturnData(array('success' => false, 'Error_Msg' => toUtf('Ошибка создания архива реестра!')));
		}

		return true;
	}

	/**
	*	Импорт ответа от СМО
	*/
	function importAnswerFromSMO(){
		$upload_path = './'.IMPORTPATH_ROOT.'importHospDataFromTfoms/'.$_SESSION['lpu_id'].'/';
		$allowed_types = explode('|','zip|xml|prik');

		$data = $this->ProcessInputData('importAnswerFromSMO', true);
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
		}
		else {
			$zip = new ZipArchive;
			if ($zip->open($_FILES["ImportFile"]["tmp_name"]) === TRUE)
			{
				for($i=0; $i<$zip->numFiles; $i++){
					$filename = $zip->getNameIndex($i);
					if ( preg_match('/P.*.xml/i', strtolower($filename)) > 0 ) {
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

		$outdata = array();
		foreach($xmlfiles as $xmlfile){
			$xml_file_path = $upload_path.'/'.$xmlfile;
			//Преобразует simpleXML в массив
			libxml_use_internal_errors(true);
			$xmlstr = file_get_contents($xml_file_path);
			$xmlcont = simplexml_load_string($xmlstr);

			if ($xmlcont) {
				$xmlcont = new SimpleXMLElement($xmlstr);
			}
			//Получили массив с данными XML файла
            $outdata = xml2array($xmlcont, $outdata);
			array_walk_recursive($outdata, 'ConvertFromUTF8ToWin1251');
		}
		$result = array();
		$log_arr = array();
		$file_array = array();
		$counter = 0;
		foreach ($outdata as $key=>$value){
			if($key == 'ZAP'){
				if(isset($value[0]))
				{
					for($i=0; $i<count($value);$i++)
					{
						$file_array[$i]['FAM'] = !is_array($value[$i]['FAM'])?$value[$i]['FAM']:0;
						$file_array[$i]['IM'] = !is_array($value[$i]['IM'])?$value[$i]['IM']:0;
						$file_array[$i]['OT'] = !is_array($value[$i]['OT'])?$value[$i]['OT']:0;
						$file_array[$i]['DR'] = !is_array($value[$i]['DR'])?$value[$i]['DR']:0;
						$file_array[$i]['SNILS'] = !is_array($value[$i]['SNILS'])?$value[$i]['SNILS']:0;
						$file_array[$i]['SP_PRIK'] = !is_array($value[$i]['SP_PRIK'])?$value[$i]['SP_PRIK']:0;
						$file_array[$i]['T_PRIK'] = $value[$i]['T_PRIK'];
						$file_array[$i]['DATE_1'] = $value[$i]['DATE_1'];
						$file_array[$i]['KODPODR'] = !is_array($value[$i]['KODPODR'])?$value[$i]['KODPODR']:0;
						$file_array[$i]['LPUUCH'] = !is_array($value[$i]['LPUUCH'])?$value[$i]['LPUUCH']:0;
						$file_array[$i]['SSD'] = !is_array($value[$i]['SSD'])?$value[$i]['SSD']:'0';
						if(isset($value[$i]['REFREASON']))
							$file_array[$i]['REFREASON'] = is_array($value[$i]['REFREASON'])?$value[$i]['REFREASON']:array($value[$i]['REFREASON']);
						else
							$file_array[$i]['REFREASON'] = array('0');
					}
				}
				else
				{
					$i = 0;
					$file_array[$i]['FAM'] = !is_array($value['FAM'])?$value['FAM']:0;
					$file_array[$i]['IM'] = !is_array($value['IM'])?$value['IM']:0;
					$file_array[$i]['OT'] = !is_array($value['OT'])?$value['OT']:0;
					$file_array[$i]['DR'] = !is_array($value['DR'])?$value['DR']:0;
					$file_array[$i]['SNILS'] = !is_array($value['SNILS'])?$value['SNILS']:0;
					$file_array[$i]['SP_PRIK'] = !is_array($value['SP_PRIK'])?$value['SP_PRIK']:0;
					$file_array[$i]['T_PRIK'] = $value['T_PRIK'];
					$file_array[$i]['DATE_1'] = $value['DATE_1'];
					$file_array[$i]['KODPODR'] = !is_array($value['KODPODR'])?$value['KODPODR']:0;
					$file_array[$i]['LPUUCH'] = !is_array($value['LPUUCH'])?$value['LPUUCH']:0;
					$file_array[$i]['SSD'] = !is_array($value['SSD'])?$value['SSD']:'0';
					if(isset($value['REFREASON']))
						$file_array[$i]['REFREASON'] = is_array($value['REFREASON'])?$value['REFREASON']:array($value['REFREASON']);
					else
						$file_array[$i]['REFREASON'] = array('0');
				}
			}
		}
		for($i=0; $i<count($file_array);$i++){
			$log_arr[] = 'ФИО: '. $file_array[$i]['FAM'].' '.$file_array[$i]['IM'].' '.$file_array[$i]['OT'];
			$log_arr[] = 'ДР: '.$file_array[$i]['DR'];
			$log_arr[] = 'СНИЛС: '.$file_array[$i]['SNILS'];
			//Ищем человека по ФИО и СНИЛС
			$person_id = $this->pcmodel->searchPerson($file_array[$i]);
			if($person_id == 0)
				{
					$log_arr[] = 'ПАЦИЕНТ НЕ НАЙДЕН!';
					$log_arr[] = ' ';
				}
			else
			{
				$file_array[$i]['PER_ID'] = $person_id;
				//Если найден, то получаем тип прикрепления, номер участка, врача и дату прикрепления(открепления)
				$file_array[$i]['LA_T'] = 'Основное прикрепление';
				if($file_array[$i]['LPUUCH']==0)
					$file_array[$i]['LR_N'] = '';
				else
					$file_array[$i]['LR_N'] = substr($file_array[$i]['LPUUCH'], 1);
				$file_array[$i]['PC_T'] = ($file_array[$i]['T_PRIK']=='2')?'Открепление: ':'Прикрепление: ';

				//$file_array[$i]['LPU_CODE'] = substr($file_array[$i]['KODPODR'],0,strlen($file_array[$i]['KODPODR'])-2);
				$file_array[$i]['LPU_CODE'] = substr($file_array[$i]['KODPODR'],0,6);
				$medpersonal = $this->pcmodel->searchMedPersonal($file_array[$i]['SSD'], $file_array[$i]['LPU_CODE']);//Ищем врача по СНИЛСу
				$log_arr[] = $file_array[$i]['PC_T'].$file_array[$i]['LA_T'].', участок №'.$file_array[$i]['LR_N'].', врач '.$medpersonal.', '.$file_array[$i]['DATE_1'];
				//Далее ищем прикрепление/открепление/заявление
				$personCard_data = $this->pcmodel->searchPersonCard($file_array[$i]);
				if($personCard_data['ItemExists'] == '0')
				{
					$log_arr[] = 'Результат: - ';
					$log_arr[] = 'Результат в системе: запись не найдена';
				}
				else
				{
					if($personCard_data['PersonCard_id'] != '0') //Нашли прикрепление (или открепление)
					{
						$log_arr[] = 'Нашли PersonCard_id';
						$errors = array();
						foreach($file_array[$i]['REFREASON'] as $item) {
							$refreason = str_replace(" ", "", (mb_strtolower($item)));
							if (!in_array($refreason, array('0','3','5','включенврегистр'))) {
								$errors[] = $this->getErrorText($item);
							}
						}
						if (count($errors) == 0) {
							$log_arr[] = 'Результат: Одобрено';
							$log_arr[] = 'Результат в системе: Одобрено';

							$change_data = array(
								'PersonCard_id' => $personCard_data['PersonCard_id'],
								'PersonCardAttachStatusType_id' => '9',
								'pmUser_id' => $_SESSION['pmuser_id']
							);
							$this->pcmodel->changePersonCardAttachStatusByPersonCard($change_data); //Ставим статус "одобрено"
						}
						else
						{
							$log_arr[] = "Результат: Отказано. Запись не принята по следующим причинам: ".implode("; ", $errors);
							$log_arr[] = 'Результат в системе: '.($file_array[$i]['T_PRIK']=='1')?'Необходимо удалить прикрепление':'Отказано';
						}
					}
					if($personCard_data['PersonCardAttach_id'] != '0')
					{
						$errors = array();
						foreach($file_array[$i]['REFREASON'] as $item) {
							$refreason = str_replace(" ", "", (mb_strtolower($item)));
							if (!in_array($refreason, array('0','3','5','включенврегистр'))) {
								$errors[] = $this->getErrorText($item);
							}
						}
						//Если нет критических ошибок, ставим заявлению статус "Одобрено" и создаем прикрепление
						if(count($errors) == 0){
							$change_data = array(
								'PersonCardAttach_id' => $personCard_data['PersonCardAttach_id'],
								'PersonCardAttachStatusType_id' => '9',
								'pmUser_id' => $_SESSION['pmuser_id']
							);
							$this->pcmodel->changePersonCardAttachStatus($change_data);
							$attach_data = array(
								'PersonCardAttach_id' => $personCard_data['PersonCardAttach_id'],
								'pmUser_id' => $_SESSION['pmuser_id'],
								'Server_id' => $_SESSION['server_id']
							);
							$this->pcmodel->addPersonCardByAttach($attach_data);
							$log_arr[] = 'Результат: Одобрено';
							$log_arr[] = 'Результат в системе: Запись обновлена';
						}
						else //Иначе - ставим статус "Отказано" и указываем причину
						{
							$log_arr[] = "Результат: Отказано. Запись не принята по следующим причинам: ".implode("; ", $errors);
							$log_arr[] = 'Результат в системе: Необходимо удалить заявление';
							$change_data = array(
								'PersonCardAttach_id' => $personCard_data['PersonCardAttach_id'],
								'PersonCardAttachStatusType_id' => '10',
								'pmUser_id' => $_SESSION['pmuser_id']
							);
							$this->pcmodel->changePersonCardAttachStatus($change_data);
						}
					}
				}
				$log_arr[] = ' ';
			}
		}
		$result['Error_Code'] = null;
        $result['Error_Msg'] = null;
		$result['success'] = true;
        $result['Protocol_Link'] = $this->getLogFile($log_arr, 'doknak');
        //var_dump($result);die;
        //return array($result);
        $this->ProcessModelSave($result, true)->ReturnData();
        return true;
	}

	/**
     * Запись лога импорта в файл
     */
    function getLogFile($log_array, $object_nick) {
        $link = '';

        if (empty($object_nick)) {
            $object_nick = "obj";
        }

        $out_dir = "import_{$object_nick}_".time();
        mkdir(EXPORTPATH_REGISTRY.$out_dir);

        $msg_count = 0;
        $link = EXPORTPATH_REGISTRY.$out_dir."/protocol.txt";
        $fprot = fopen($link, 'w');

        foreach($log_array as $log_msg) {
            $msg = $log_msg;
            $msg .= "\r\n";
            fwrite($fprot, $msg);
        }

        fclose($fprot);

        return $link;
    }

    /**
    *	Получение текста ошибки по коду
    */
    function getErrorText($err_code)
    {
    	switch ($err_code) {
    		case '1':
    			return 'IDCASE не уникально';
    			break;
    		case '2':
    			return 'Дубль по ФИО+ДР в списке МО';
    			break;
    		case '3':
    			return 'Дубль по реквизитам документа';
    			break;
    		case '4':
    			return 'Дубль по СНИЛС';
    			break;
    		case '5':
    			return 'Некорректное заполнение поля RZ или не заполнен';
    			break;
    		case '6':
    			return 'Некорректный СНИЛС';
    			break;
    		case '7':
    			return 'RZ принадлежит другому человеку';
    			break;
    		case '8':
    			return 'СНИЛС принадлежит другому человеку';
    			break;
    		case '9':
    			return 'Не идентифицирован в БД';
    			break;
    		case '10':
    			return 'Дубль с другой МО (указать с какой, способ прикрепления, дата)';
    			break;
    		case '11':
    			return 'Умер по данным ТФОМС';
    			break;
    		case '12':
    			return 'Некорректная дата прикрепления';
    			break;
    		case '13':
    			return 'Отсутствует возможность включения в ЕРЗ по возрасту';
    			break;
    		case '14':
    			return 'Возможность прикрепления к СМО недопустима';
    			break;
    		case '15':
    			return 'Код подразделения не заполнен или заполнен неверно';
    			break;
    		case '16':
    			return 'СНИЛС мед. работника не заполнен или заполнен неверно';
    			break;
    		case '17':
    			return 'Возможность прикрепления к СМО недопустима (на дату прикрепления)';
    			break;
    		default:
    			return '0';
    			break;
    	}
    }
}
