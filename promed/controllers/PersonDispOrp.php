<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonDispOrp - контроллер для выполенния операций с регистром детей сирот
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Марков Андрей
 * @version      май 2010
 */

class PersonDispOrp extends swController {

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
		'GetList' => array(
			array(
				'field' => 'beg_date',
				'label' => 'Дата начала добавления в регистр',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'beg_time',
				'label' => 'Время начала добавлений в регистр',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'mode',
				'label' => 'Режим работы',
				'rules' => 'trim',
				'type' => 'string'
			),
		),
		'exportPersonDispOrpToDbf' => array(
			array(
				'field' => 'PersonDispOrp_Year',
				'label' => 'Год',
				'rules' => 'trim|required',
				'type' => 'int'
			)
		)
    );

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 *  Функция получения архива экспорта регистра по ДД.
	 *  Входящие данные: $_POST с годом экспорта.
	 *  На выходе: JSON-строка с URL архива и списком ЛПУ с количествами людей в регистре.
	 */
	function exportPersonDispOrpToDbf()
	{
		$this->load->database();
		$this->load->model("PersonDispOrp_model", "dbmodel");
		define("ERROR_DEADLOCK","При обращении к базе данных произошла ошибка.<br/>"."Скорее всего данная ошибка вызвана повышенной нагрузкой на сервер. <br/>"."Повторите попытку, и, если ошибка появится вновь - <br/>"."свяжитесь с технической поддержкой.");

		if ( !isSuperadmin() )
		{
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('У вас нет прав для выгрузки регистра детей-сирот!')));
			exit;
		}

		$data = array();

		$data = $this->ProcessInputData('exportPersonDispOrpToDbf', true);
		if ($data === false)
		{
			return false;
		}
		
		$response = $this->dbmodel->loadPersonDispOrpListForDbf($data);

		if ( $response===false )
		{
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF(ERROR_DEADLOCK)));
			return false;
		}
		if ( !is_array($response) || !(count($response) > 0) )
		{
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Нет данных для выгрузки регистра детей-сирот.')));
			return true;
		}

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		// формируем массив с описанием полей бд

		$dd_def = array(
			array( "ORG_OGRN", "C",15 , 0 ),
			array( "ORG_NAME", "C",100 , 0 ),
			array( "FAM", "C",20 , 0 ),
			array( "IM", "C",20 , 0 ),
			array( "OT", "C",20 , 0 ),
			//array( "SEX", "C",1 , 0 ),
			array( "DR", "D",8 , 0 ),
			array( "LPU_OGRN", "C", 15, 0 )
		);

		$out_dir = "do_".time();
		if ( !file_exists(EXPORTPATH_DO) )
			mkdir( EXPORTPATH_DO );
		mkdir( EXPORTPATH_DO.$out_dir );

		$file_dd_sign = "do";
		$file_dd_name = EXPORTPATH_DO.$out_dir."/REG_ORP.dbf";
		
		$file_dd_proto_sign = "do_proto";
		$file_dd_proto_name = EXPORTPATH_DO.$out_dir."/REG_ORP_log.txt";
		
		$file_zip_sign = "persondd".$data['PersonDispOrp_Year'];
		$file_zip_name = EXPORTPATH_DO.$out_dir."/REG_ORP.zip";
		
		$h = dbase_create( $file_dd_name, $dd_def );
		$records_count = 0;
		$export_date = date('d.m.Y');
		$export_status = "yспешно";
		foreach ($response as $row)
		{
			// определяем которые даты и конвертируем их
			foreach ($dd_def as $descr)
			{
				if ( $descr[1] == "D" )
					if (!empty($row[$descr[0]]))
						$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
					else
						$row[$descr[0]] = '01/01/1970';
						
			}
			array_walk($row, 'ConvertFromUtf8ToCp866');
			dbase_add_record( $h, array_values($row) );
			$records_count++;
		}
		dbase_close ($h);
		
		// создаем файл протокола
		$fh = fopen($file_dd_proto_name, "w");
		fwrite($fh, "Число выгруженных записей: ".$records_count.chr(13).chr(10));
		fwrite($fh, "Дата выгрузки: ".$export_date.chr(13).chr(10));
		fwrite($fh, "Статус выгрузки: ".$export_status);
		fclose($fh);
		
		$zip=new ZipArchive();
		$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
		$zip->AddFile( $file_dd_name, "REG_ORP.dbf" );
		$zip->AddFile( $file_dd_proto_name, "REG_ORP_log.txt" );
		$zip->close();
		unlink($file_dd_name);
		unlink($file_dd_proto_name);
		// отдаем файл клиенту
		// формируем отчет
		$response = $this->dbmodel->loadPersonDispOrpLpuReportForDbf($data);
		if ( $response===false )
		{
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF(ERROR_DEADLOCK)));
			return false;
		}
		if ( !is_array($response) || !(count($response) > 0) )
		{
			echo json_encode(array('success' => false, 'Error_Msg' => toUTF('Нет данных для выгрузки регистра детей-сирот.')));
			return true;
		}
		
		$table_header = toUtf("<tr><th>ЛПУ</th><th>Количество людей в регистре детей-сирот</th></tr>");
		$style = "<style>
		 html,body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,p,blockquote,th,td{margin:0;padding:0;}img,body,html{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}ol,ul {list-style:none;}caption,th {text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;}q:before,q:after{content:'';}

		 table {
		   width: 100%;
		   text-align: left;
		   font-size: 11px;
		   font-family: arial;
		   border-collapse: collapse;
		 }

		 table th {
		   padding: 4px 3px 4px 5px;
		   border: 1px solid #d0d0d0;
		   border-left-color: #eee;
		   background-color: #ededed;
		 }

		 table td {
		   padding: 4px 3px 4px 5px;
		   border-style: none solid solid;
		   border-width: 1px;
		   border-color: #ededed;
		 }
		 </style>";
		$title = toUtf("Отчет о выгрузке регистра детей-сирот за " .$data['PersonDispOrp_Year']. " год");
		$html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//\EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		  <html>
			<head>
			  <meta content=\"text/html; charset=UTF-8\" http-equiv=\"Content-Type\" />
			  {$style}
			  <title>{$title}</title>
			</head>
			<body>
			<table>{$table_header}";
			
		foreach ($response as $row)
		{
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$html .= "<tr><td>{$row['Lpu_Name']}</td><td>{$row['cnt']}</td></tr>";
		}
		$html .= "</table></body></html>";
		echo json_encode(array("success" => true, "url" => "/".$file_zip_name, "html" => $html ));
	}

	/**
	 *  Функция получения списка пациентов из регистра детей сирот.
	 *  Входящие данные: $_POST с фильтрами
	 *  На выходе: JSON-строка
	 */
	function GetList() {
		$this->load->database();
		$this->load->model("PersonDispOrp_model", "pcmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = $this->ProcessInputData('GetList', true);
		if ($data) {
			if ( ArrayVal($data, 'mode') == 'streaminput' ) {
				$response = $this->pcmodel->getPersonDispOrpStreamInputList($data);
				$val = array();
				if (is_array($response) && count($response) > 0) {
					$val = array();
					foreach ($response as $row) {
						array_walk($row, 'ConvertFromWin1251ToUTF8');
						$val[] = $row;
					}
					$this->ReturnData($val);
				}
				else {
					echo json_encode(array());
				}
				return true;
			}
		}
		$data = $_POST;
		// Конвертируем все элементы из UTF8 в Windows-1251
		array_walk($data, 'ConvertFromUTF8ToWin1251');
		
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		$info = $this->pcmodel->getPersonDispOrpList($data);
		if ( is_array($info) && count($info) > 0 ) {
			$val = array();
			foreach ($info as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				$val[] = $row;
			}
			$this->ReturnData($val);

		}
		else {
			echo json_encode(array());
		}
	}

	/**
	 *  Функция получения комбобокса годов
	 *  Входящие данные: нет
	 *  На выходе: JSON-строка
	 *  Используется: форма потокового ввода регистра детей сирот 
	 */
	function GetPersonDispOrpYearsCombo() {
		
		$this->load->database();
		$this->load->model("PersonDispOrp_model", "pcmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$year = 2012;
		$info = $this->pcmodel->getPersonDispOrpYearsCombo($data);
		if ( is_array($info) && count($info) > 0 ) {
			$val = array();
			$flag = false;
			foreach ($info as $row) {
				array_walk($row, 'ConvertFromWin1251ToUTF8');
				if ( $row['PersonDispOrp_Year'] == $year )
					$flag = true;
				$val[] = $row;
			}
			if (!$flag)
				$val[] = array('PersonDispOrp_Year'=>$year, 'count'=>0);
			$this->ReturnData($val);

		}
		else {
			$val = array();
			$val[] = array('PersonDispOrp_Year'=>$year, 'count'=>0);
			$this->ReturnData($val);
		}
	}

	/**
	 * Проверка заполненности всех необходимых для добавления в регистр детей сирот атрибутов
	 * 
	 * @param array $person_data Массив данных о человеке
	 * @var	array Массив с текстовыми описаниями ошибок
	 */
	function checkPersonDOAttributes($person_data) {
		$errors = Array();
		// проверки заполнености атрибутов у человека
		if (ArrayVal($person_data, 'Person_SurName') == '')
			$errors[] = 'Не заполнена Фамилия';
		if (ArrayVal($person_data, 'Person_FirName') == '')
			$errors[] = 'Не заполнено Имя';
		if (ArrayVal($person_data, 'Person_BirthDay') == '')
			$errors[] = 'Не заполнена Дата рождения';
		if (ArrayVal($person_data, 'Person_BirthDay') == '')
			$errors[] = 'Не заполнена Дата рождения';
		if (ArrayVal($person_data, 'Sex_id') == '')
			$errors[] = 'Не заполнен Пол';
		if (ArrayVal($person_data, 'SocStatus_id') == '')
			$errors[] = 'Не заполнен социальный статус пациента';
		/*
		if (ArrayVal($person_data, 'UAddress_id') == '')
			$errors[] = 'Не заполнен Адрес по месту регистрации';
		if (ArrayVal($person_data, 'Polis_Num') == '')
			$errors[] = 'Не заполнен Номер полиса';
		if (ArrayVal($person_data, 'Polis_Ser') == '')
			$errors[] = 'Не заполнена Серия полиса';
		if (ArrayVal($person_data, 'OrgSmo_id') == '')
			$errors[] = 'Не заполнена Организация, выдавшая полис';
		*/
		if (ArrayVal($person_data, 'OrgUAddress_id') == '')
			$errors[] = 'Не заполнен Адрес организации, в которой содержится ребенок';
		/*
		if (ArrayVal($person_data, 'Org_INN') == '')
			$errors[] = 'Не заполнен ИНН организации, в которой содержится ребенок';
		else
			if ( $person_data['Check_INN'] != 0 )
				$errors[] = 'ИНН места работы не соответствует алгоритму формирования';
		*/
		if (ArrayVal($person_data, 'Org_OGRN') == '')		
			$errors[] = 'Не заполнен ОГРН организации, в которой содержится ребенок';
		else
			if ( $person_data['Check_OGRN'] != 0 )
				$errors[] = 'ОГРН места работы не соответствует алгоритму формирования';
		if (ArrayVal($person_data, 'Person_Age') >18)
			$errors[] = 'Возраст пациента 18 или более лет';
		/*
		if (ArrayVal($person_data, 'Okved_id') == '')
			$errors[] = 'Не заполнен ОКВЭД места работы';
		*/
		return $errors;
	}

	/**
	 * Добавление человека в регистр по диспасеризации детей-сирот
	 */
	function addPersonDispOrp() {
		
		$this->load->database();
		$this->load->model("PersonDispOrp_model", "pcmodel");
		$data = $_POST;
		$this->load->helper('Text');
		$this->load->helper('Main');
		array_walk($data, 'ConvertFromUTF8ToWin1251');

		// Получаем сессионные переменные
		$data['session'] = $_SESSION;
		
		// проверяем присутствие человека в оплаченных реестрах 
		/*
		$info = $this->pcmodel->getYearInOldRegistry($data);
		if ( is_array($info) && count($info) > 0 ) {
			if ( $info[0]['PersonDispOrp_Year'] > 0 )
			{
				echo json_encode(array("success"=>false, 'Error_Code'=>670, 'Error_Msg'=>toUTF("Данный человек не подлежит повторной диспасеризации детей-сирот в 2010 году, т.к. проходил ДД в {$info[0]['PersonDispOrp_Year']}-м году"), "cancelErrorHandle" => true));				
				return true;
			}
		}
		else {
			die(json_encode(array('success'=>false, 'Error_Msg'=>toUTF('Не удалось проверить присутствие человека в оплаченных реестрах за прошлый год.'))));
		}
		*/
		$info = $this->pcmodel->getPersonData($data);
		$errors = array();
		if ( is_array($info) && count($info) > 0 ) {
			$errors = $this->checkPersonDOAttributes($info[0]);
			$errstr = implode("<br>", $errors);
			if (count($errors) > 0 ) {
				echo json_encode(array("success"=>false, "Error_Code" => '667', "Error_Msg"=>toUTF($errstr), "cancelErrorHandle" => true));
			}
			else { // добавляем
				// будем так же передавать Org_INN, Org_OGRN и Org_id
				$data['Org_INN'] = $info[0]['Org_INN'];
				$data['Org_OGRN'] = $info[0]['Org_OGRN'];
				$data['Org_id'] = $info[0]['Org_id'];
				$info = $this->pcmodel->addPersonDispOrp($data);
				if ( is_array($info) && count($info) > 0 ) {
					if ( ArrayVal($info[0], 'Error_Code') == '' )
						echo json_encode(array("success"=>true));
					else
						echo json_encode(array("success"=>false, 'Error_Code'=>ArrayVal($info[0], 'Error_Code'), 'Error_Msg'=>toUTF($info[0]['Error_Msg']), "cancelErrorHandle" => true));

				}
				else {
					echo(json_encode(array('success'=>false, 'Error_Msg'=>toUTF('Не удалось сохранить данные'))));
				}
			}
		}
		else {
			echo(json_encode(array('success'=>false, 'Error_Msg'=>toUTF('Не удалось получить данные о человеке'))));
		}
	}
}

?>