<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * PersonDopDisp - контроллер для выполенния операций с регистром людей по дополнительной диспансеризации.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
 * @version      22.06.2009
 *
 * @property Polka_PersonDopDisp_model dbmodel
 */

class PersonDopDisp extends swController {

	/**
	 * Описание правил для входящих параметров
	 * @var array
	 */
    var $inputRules = array(
	/*
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
	*/
		'exportPersonDopDispToDbf' => array(
			array(
				'field' => 'PersonDopDisp_Year',
				'label' => 'Год',
				'rules' => 'trim|required',
				'type' => 'int'
			)
		),
		'deletePersonDopDisp' => array(
			array(
				'field' => 'PersonDopDisp_id',
				'label' => 'Идентификатор',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'addPersonDopDisp' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'cancel_check_job_data',
				'label' => 'Флаг отмены проверки места работы',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'cancel_check_other_lpu',
				'label' => 'Флаг отмены проверки на присутствие в другом ЛПУ',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'PersonDopDisp_Year',
				'label' => 'Год',
				'rules' => 'trim',
				'type' => 'int'
			)
		)
    );

	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model("Polka_PersonDopDisp_model", "dbmodel");
	}
	
	/**
	 *  Функция получения архива экспорта регистра по ДД.
	 *  Входящие данные: $_POST с годом экспорта.
	 *  На выходе: JSON-строка с URL архива и списком ЛПУ с количествами людей в регистре.
	 */
	function exportPersonDopDispToDbf()
	{
		$data = $this->ProcessInputData('exportPersonDopDispToDbf', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadPersonDopDispListForDbf($data);

		if ( $response===false ) {
			echo 'Ошибка: обрыв коннекта. Повторите попытку.';
			return false;
		}

		if ( !is_array($response) || !(count($response) > 0) ) {
			echo 'В БД нет данных в регистре ДД.';
			return true;
		}

		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится

		// формируем массив с описанием полей бд
		if ( isSuperadmin() ) {
			$dd_def = array(
				array( "PERSON_ID", "N",16 , 0 ),
				array( "SURNAME", "C",30 , 0 ),
				array( "FIRNAME", "C",20 , 0 ),
				array( "SECNAME", "C",20 , 0 ),
				array( "SEX", "C",1 , 0 ),
				array( "BIRTHDAY", "D",8 , 0 ),
				array( "ADDR", "C",80 , 0 ),
				array( "POL_SER", "C",6 , 0 ),
				array( "POL_NUM", "C",16 , 0 ),
				array( "SNILS", "C",14 , 0 ),
				array( "O_OKVED", "C",10 , 0 ),
				array( "ORG_OGRN", "C",15 , 0 ),
				array( "TERR_ID_A", "N", 16, 0),
				array( "TERR_ID_B", "N", 16, 0),
				array( "LPU_OGRN", "C", 15, 0),
				array( "REGION_CD", "N", 5, 0),
				array( "Q_OGRN", "C", 15, 0)			
			);
		}
		else {
			$dd_def = array(
				array( "PERSON_ID", "N", 16, 0 ),
				array( "SURNAME", "C", 30, 0 ),
				array( "FIRNAME", "C", 30, 0 ),
				array( "SECNAME", "C", 30, 0 ),
				array( "BIRTHDAY", "D", 8, 0 ),
				array( "SEX", "N", 1, 0 ),
				array( "SMO_NAME", "C", 100, 0 ),
				array( "POLIS_S", "C", 10, 0 ),
				array( "POLIS_N", "C", 30, 0 ),
				array( "SNILS", "C", 11, 0 ),
				array( "O_NAME", "C", 100, 0 ),
				array( "O_INN", "C", 12, 0 ),
				array( "O_OGRN", "C", 15, 0 ),
				array( "O_ADDR", "C", 100, 0 ),
				array( "O_OKVED", "C", 10, 0 ),
				array( "TAL_DD", "N", 1, 0 )
			);
		}

		$out_dir = "dd_" . time();

		if ( !file_exists(EXPORTPATH_DD) ) {
			mkdir( EXPORTPATH_DD );
		}

		mkdir( EXPORTPATH_DD . $out_dir );

		$file_dd_sign = "dd";
		$file_dd_name = EXPORTPATH_DD.$out_dir."/".$file_dd_sign.".dbf";
		
		$file_dd_proto_sign = "dd_proto";
		$file_dd_proto_name = EXPORTPATH_DD.$out_dir."/".$file_dd_proto_sign.".txt";
		
		$file_zip_sign = "persondd".$data['PersonDopDisp_Year'];
		$file_zip_name = EXPORTPATH_DD.$out_dir."/".$file_zip_sign.".zip";
		
		$h = dbase_create( $file_dd_name, $dd_def );
		$records_count = 0;
		$export_date = date('d.m.Y');
		$export_status = "yспешно";

		foreach ( $response as $row ) {
			// определяем которые даты и конвертируем их
			foreach ( $dd_def as $descr ) {
				if ( $descr[1] == "D" ) {
					if ( !empty($row[$descr[0]]) ) {
						$row[$descr[0]] = date("Ymd",strtotime($row[$descr[0]]));
					}
					else {
						$row[$descr[0]] = '01/01/1970';
					}
				}
			}

			array_walk($row, 'ConvertFromWin1251ToCp866');
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
		$zip->AddFile( $file_dd_name, "SVOD.dbf" );
		$zip->AddFile( $file_dd_proto_name, "persondd".$data['PersonDopDisp_Year']."_proto.txt" );
		$zip->close();
		unlink($file_dd_name);
		unlink($file_dd_proto_name);

		if ( isSuperadmin() ) {
			// отдаем файл клиенту
			// формируем отчет
			$response = $this->dbmodel->loadPersonDopDispLpuReportForDbf($data);
			if ( $response === false ) {
				echo 'Ошибка: обрыв коннекта. Повторите попытку.';
				return false;
			}

			if ( !is_array($response) || !(count($response) > 0) ) {
				echo 'В БД нет данных в регистре ДД.';
				return true;
			}
			
			$table_header = iconv("windows-1251", "utf-8","<tr><th>ЛПУ</th><th>Количество людей в регистре по ДД</th></tr>");
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
			$title = iconv("windows-1251", "utf-8","Отчет о выгрузке регистра по ДД за " .$data['PersonDopDisp_Year']. " год");
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
		}
		else {
			$html = "";
		}

		$this->ReturnData(array("success" => true, "url" => "/".$file_zip_name, "html" => $html ));
		/*if ($fh = fopen($file_zip_name, "r"))
		{
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=".$file_mes_sign.".zip");
			$file = fread($fh, filesize($file_zip_name));
			print $file;
			fclose($fh);
		}
		else
		{
			echo "Ошибка создания архива МЭС!";
		}*/
	}

	/**
	 *  Функция получения списка пациентов из регистра доп. диспансеризации.
	 *  Входящие данные: $_POST с фильтрами
	 *  На выходе: JSON-строка
	 */
	/*
	
	Нигде не используется. TODO: удалить или рефакторить.
	function GetList() {
		$data = $this->ProcessInputData('GetList', true);
		
		if ( ArrayVal($data, 'mode') == 'streaminput' ) {
			if ($data) {
				$response = $this->dbmodel->getPersonDopDispStreamInputList($data);
				$this->ProcessModelList($response, true, true)->ReturnData();
				return true;
			}
		}

		$data = $_POST;
		$data['session'] = $_SESSION;
		
		$response = $this->dbmodel->getPersonDopDispList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
	}
	
	*/

	/**
	 *  Функция получения комбобокса годов
	 *  Входящие данные: нет
	 *  На выходе: JSON-строка
	 *  Используется: форма потокового ввода дополнительной диспанцеризации
	 */
	function GetPersonDopDispYearsCombo() {
		$data = getSessionParams();

		$year = date('Y');
		$info = $this->dbmodel->getPersonDopDispYearsCombo($data);
		$outdata = $this->ProcessModelList($info, true, true)->GetOutData();
		
		$flag = false;
		foreach ($outdata as $row) {
			if ( $row['PersonDopDisp_Year'] == $year ) {
                $flag = true;
            }
		}

        //При отсутствии записей по ДД искуcственно выводим нулевые значения на 2010, 2011 и 2012 года что бы акцентировать на это внимание
		if (!$flag && empty($outdata)) {
            $outdata[] = array('PersonDopDisp_Year'=>2010, 'count'=>0);
            $outdata[] = array('PersonDopDisp_Year'=>2011, 'count'=>0);
            $outdata[] = array('PersonDopDisp_Year'=>2012, 'count'=>0);
        }
		$this->ReturnData($outdata);
	}

	/**
	 * Проверка заполненности всех необходимых для добавления в регистр по ДД атрибутов
	 * 
	 * @param array $person_data Массив данных о человеке
	 * @var	array Массив с текстовыми описаниями ошибок
	 */
	function checkPersonDDAttributes($person_data) {
		$errors = Array();
		//var_dump($person_data);
		//exit;
		$job_errors = false;
		$person_errors = false;
		// проверки заполнености атрибутов у человека
		if (ArrayVal($person_data, 'Person_SurName') == '') {
			$errors[] = 'Не заполнена Фамилия';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'Person_FirName') == '') {
			$errors[] = 'Не заполнено Имя';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'Person_BirthDay') == '') {
			$errors[] = 'Не заполнена Дата рождения';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'Person_BirthDay') == '') {
			$errors[] = 'Не заполнена Дата рождения';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'Sex_id') == '') {
			$errors[] = 'Не заполнен Пол';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'SocStatus_id') == '') {
			$errors[] = 'Не заполнен Соц. статус';
			$person_errors = true;
		}
			/*elseif ( ArrayVal($person_data, 'SocStatus_id') != 1 ) {
				$errors[] = 'Соц. статус должен быть установлен "Работающий"';
				$person_errors = true;
		}*/
		if (ArrayVal($person_data, 'UAddress_id') == '') {
			$errors[] = 'Не заполнен Адрес по месту регистрации';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'OmsSprTerr_id') == '') {
			$errors[] = 'Не заполнена территория полиса';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'Polis_Num') == '') {
			$errors[] = 'Не заполнен Номер полиса';
			$person_errors = true;
		}
		/* Для Перми тоже убрали https://redmine.swan.perm.ru/issues/5666
		if (ArrayVal($person_data, 'Polis_Ser') == '')
		{
			// Заполнение серии полиса обязательно для полисов Пермского края, для полисов из других регионов нет #2661
			if (ArrayVal($person_data, 'KLRgn_id') == 59) {
				$errors[] = 'Не заполнена Серия полиса';
				$person_errors = true;
			}
		}
		*/
		if (ArrayVal($person_data, 'OrgSmo_id') == '') {
			$errors[] = 'Не заполнена Организация, выдавшая полис';
			$person_errors = true;
		}
		if (ArrayVal($person_data, 'OrgUAddress_id') == '') {
			$errors[] = 'Не заполнен Адрес места работы';
			$job_errors = true;
		}
		if (ArrayVal($person_data, 'Org_INN') == '') {
			$errors[] = 'Не заполнен ИНН места работы';
			$job_errors = true;
		}
		else {
			if ( $person_data['Check_INN'] != 0 ) {
				$errors[] = 'ИНН места работы не соответствует алгоритму формирования';
				$job_errors = true;
			}
		}

		if (ArrayVal($person_data, 'Org_OGRN') == '') {
			$errors[] = 'Не заполнен ОГРН места работы';
			$job_errors = true;
		}
		else {
			if ( $person_data['Check_OGRN'] != 0 ) {
				$errors[] = 'ОГРН места работы не соответствует алгоритму формирования';
				$job_errors = true;
			}
		}

		if (ArrayVal($person_data, 'Okved_id') == '') {
			$errors[] = 'Не заполнен ОКВЭД места работы';
			$job_errors = true;
		}
		return array('errors' =>$errors, 'job_errors' => $job_errors, 'person_errors' => $person_errors);
	}

	/**
	 * Добавление человека в регистр по дополнительной диспансеризации
	 */
	function addPersonDopDisp() {
		$this->load->helper('Text');
		$this->load->helper('Main');
		
		$data = $this->ProcessInputData('addPersonDopDisp', true);
		if ( $data === false ) { return false; }
		
		// проверяем присутствие человека в оплаченных реестрах, пока только на сервере Перми
		if ( $_SESSION['region']['number'] == 59 )
		{
			$info = $this->dbmodel->getYearInOldRegistry($data);
			if ( $info === false )
				die(json_encode(array('success'=>false, 'Error_Msg'=>toUTF('Не удалось проверить присутствие человека в регистре за прошлые года.'))));
			if ( $info !== true && in_array($info, array(2009, 2010, 2011)) ) {
				$this->ReturnData(array("success"=>false, 'Error_Code'=>670, 'Error_Msg'=>toUTF("Пациент не может быть добавлен в регистр, т.к. проходил ДД в {$info}-м году."), "cancelErrorHandle" => true));
				return true;
			}
			if ( $info !== true && in_array($info, array(2006, 2007, 2008)) ) {
				$this->ReturnData(array("success"=>false, 'Error_Code'=>670, 'Error_Msg'=>toUTF("Пациент не может быть добавлен в регистр, т.к. проходил ДД в {$info}-м году <br/>и по результатам ДД был поставлен на Диспансерный учет."), "cancelErrorHandle" => true));
				return true;
			}
		}
		$info = $this->dbmodel->getPersonData($data);
		$errors = array();
		if ( is_array($info) && count($info) > 0 ) {
			$errors = $this->checkPersonDDAttributes($info[0]);
			$errstr = implode("<br>", $errors['errors']);
			if (
				count($errors['errors']) > 0
				&& !($errors['job_errors']
				&& !$errors['person_errors']
				&& $_SESSION['region']['number'] == 2
				&& (isset($data['cancel_check_job_data']) && $data['cancel_check_job_data'] == true))
			) {
				// Для уфы, если ошибки только по месту работы
				if ( $errors['job_errors'] && !$errors['person_errors'] && $_SESSION['region']['number'] == 2 ) {
					$this->ReturnData(array("success"=>false, "Error_Code" => '661', "Error_Msg"=>toUTF($errstr), "cancelErrorHandle" => true));
				}
				else
					$this->ReturnData(array("success"=>false, "Error_Code" => '667', "Error_Msg"=>toUTF($errstr), "cancelErrorHandle" => true));
			}
			else { // добавляем
				// будем так же передавать Org_INN, Org_OGRN и Org_id
				$data['Org_INN'] = $info[0]['Org_INN'];
				$data['Org_OGRN'] = $info[0]['Org_OGRN'];
				$data['Org_id'] = $info[0]['Org_id'];
				$response = $this->dbmodel->addPersonDopDisp($data);
				$this->ProcessModelSave($response, true, 'Не удалось сохранить данные')->ReturnData();
			}
		}
		else {
			echo(json_encode(array('success'=>false, 'Error_Msg'=>toUTF('Не удалось получить данные о человеке'))));
		}
	}
	/**
	 * Удаление человека из регистра по дополнительной диспансеризации
	 * @return boolean
	 */
	function deletePersonDopDisp() {
		$data = $this->ProcessInputData('deletePersonDopDisp', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deletePersonDopDisp($data);
		$this->ProcessModelSave($response, true, 'Ошибка удаления')->ReturnData();
	}

}

?>