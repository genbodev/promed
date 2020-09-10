<?php

defined('BASEPATH') or die('No direct script access allowed');

/**
 * @property ImportSchema_model ImportSchema_model
 */
class ImportSchema extends swController {

	public $inputRules = array(
		'expTarifList' => array(
			array('field' => 'ReportDate', 'label' => 'Дата отчета', 'rules' => 'required', 'type' => 'string')
		),
		'Export'=>array(
			array('field' => 'full', 'label' => 'Дата отчета', 'rules' => '', 'type' => 'checkbox')
		)
	);

	//Массив исключений для сравнения вхождения значений элементов первого массива во второй
	public $_deniedElsToCompare = array();

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('ImportSchema_model', 'ImportSchema_model');
	}

	/**
	 * Запуск
	 */
	public function run() {

		try {

			// 100000 seconds - 1 days, 3 hours, 46 minutes and 40 seconds.
			set_time_limit(100000);

			ini_set("memory_limit", "3096M");
			ini_set("max_execution_time", "0");
			ini_set("max_input_time", "0");
			ini_set("post_max_size", "2048");
			ini_set("default_socket_timeout", "999");
			ini_set("upload_max_filesize", "2048");

			$fl = null;

			// Подключаем логи
			$this->load->library('textlog', array('file' => 'ImportSchema.log'));


			$words = array();
			$files = array();
			$fileName = '';
            $tmp = getSessionParams();
            $this->ImportSchema_model->setPmUserId($tmp['pmUser_id']);


			// создаю каталог в системной временной папке
			$tmp_folder = tempnam(sys_get_temp_dir(), 'imp'); // создаю временный файл
            if ($tmp_folder) {
				unlink($tmp_folder); // удаляю его и использую как имя для временного каталога

				if (mkdir($tmp_folder)) {

					// перемещаю все файлы во временный каталог
					if ( ! empty($_FILES["sourcefiles"])) {
						$fl = substr($_FILES["sourcefiles"]["name"][0], strlen($_FILES["sourcefiles"]["name"][0]) - 3);


						if (in_array(strtolower($fl), array("ms0","ms1"))) {
							$fileName = $_FILES["sourcefiles"]['name'][0];
                            $res = array();
							$f = fopen($_FILES["sourcefiles"]["tmp_name"][0], 'r') or die("Невозможно открыть файл!");
							$id = 0;
                            $ctn = 0;
							$ss = "";
							$cId = "";
                            $ignoredelete = false;
							$this->ImportSchema_model->setVersion("4");


							// feof — Проверяет, достигнут ли конец файла
							while( ! feof($f)){

								$value = iconv('cp866', 'utf-8', fgets($f));

								// Запись типа "В" (Версия)
								if (mb_substr($value, 0, 1) == "В") {
									if(trim(mb_substr($value, 1, 3))=='4.1'){

									}
								}

								// Запись типа "О" (основная запись об учетных данных)
								if (mb_substr($value, 0, 1) == "О") {
									$id++;
									$ss = trim(mb_substr($value, 1, 14));
									$cId = trim(mb_substr($value, 846, 2));
									$isRefuse = 0;
									$isRefuseNext=0;
									if (trim(mb_substr($value, 803, 1)) == 'Д') {
										$isRefuse = 1;
									} else {
										$isRefuse = 2;
									}
									if (trim(mb_substr($value, 889, 1)) == 'Д') {
										$isRefuseNext = 1;
									} else {
										$isRefuseNext = 2;
									}

									$words[$id] = array(
										"ss" => trim(mb_substr($value, 1, 14)), //PersonSnils.PersonSnils_Snils
										"frlPerson_CodeReg"=>trim(mb_substr($value, 15,3)),//3
										"frlPerson_CodeArea"=>trim(mb_substr($value, 18,3)),//4
										"fam" => trim(mb_substr($value, 21, 40)), //PersonSurName.PersonSurName_SurName
										"im" => trim(mb_substr($value, 61, 40)), //PersonFirName.PersonFirName_FirName
										"ot" => trim(mb_substr($value, 101, 40)), //PersonSecName.PersonSecName_FirName
										"w" => trim(mb_substr($value, 141, 1)), //PersonSex.Sex_id
										"dr" => trim(mb_substr($value, 142, 10)), //PersonBirthDay.PersonBirthDay_BirthDay
										"frlPerson_Nationality"=>trim(mb_substr($value, 152,40)),//10
										"frlPerson_CodeAccom"=>trim(mb_substr($value, 192,1)),//11
										"c_doc" => trim(mb_substr($value, 193, 14)), //DocumentType.DocumentType_code
										//"doc_type"=>trim(mb_substr($value, 207,80)),//13
										"sn_doc"=>trim(mb_substr($value, 287, 8)).''.trim(mb_substr($value, 295,8)),
										"s_doc" => trim(mb_substr($value, 287, 8)), //Document.Document_Ser
										"n_doc"=>trim(mb_substr($value, 295,8)),//Document.Document_Num
										"dt_doc"=>trim(mb_substr($value, 303,10)),//Document.Document_BegDate
										"o_doc"=>trim(mb_substr($value, 313,80)),//17
										"adres" => trim(mb_substr($value, 393, 200)), //PersonUAddress"adres" => trim(mb_substr($value, 393, 200)), //PersonUAddress
										"adr_fact"=>trim(mb_substr($value, 593,200)),//PersonPAddress
										"adr_type"=>trim(mb_substr($value, 793,1)),//20
										"frlPerson_KolvoGSP"=>trim(mb_substr($value, 794,3)),//21
										"c_kat1" => trim(mb_substr($value, 797, 3)), //22
										"c_kat2" => (trim(mb_substr($value, 800, 3)) == "000") ? " " : trim(mb_substr($value, 800, 3)), //23
										"isRefuse" => $isRefuse, //признак отказа:  если =Н то PersonRefuse.PersonRefuse_isRefuse=2 //24
										"frlPerson_IsRefuse2"=>(trim(mb_substr($value, 804,1))=='Д')?1:2,//25
										"frlPerson_IsRefuse3"=>(trim(mb_substr($value, 805,1))=='Д')?1:2,//26
										"db_edv"=>trim(mb_substr($value, 806,10)),//27
										"de_edv"=>(trim(mb_substr($value, 816,1))== " ")?" ":trim(mb_substr($value, 816,10)),//28
										"date_rsb" => trim(mb_substr($value, 826, 10)), //29
										"date_rse" => (trim(mb_substr($value, 836, 1) == " ")) ? " " : trim(mb_substr($value, 836, 10)), //30
										"frlPerson_ChangeCode"=>trim(mb_substr($value, 846, 2)),//31
										"frlPerson_IsPensionPFR" => trim(mb_substr($value, 848,1)),//32
										"frlPerson_Summ" => trim(mb_substr($value, 849,10)),//33
										"frlPerson_Phone" => trim(mb_substr($value, 859,20)),//34
										"frlPerson_Number" => trim(mb_substr($value, 879,10)),//35
										"isRefuseNext" => $isRefuseNext,//признак отказа  //36
										"frlPerson_IsRefuseNext2"=>(trim(mb_substr($value, 890,1))=='Д')?1:2,//37
										"frlPerson_IsRefuseNext3"=>(trim(mb_substr($value, 891,1))=='Д')?1:2,//38
										"frlPerson_BirthdayNoStand" => date('Y-m-d',strtotime(trim(mb_substr($value, 892,10)))),//39
										"frlPerson_Descr" => trim(mb_substr($value, 902,100)),//40
										"frlPerson_Rezerv" => trim(mb_substr($value, 1002,30)),//41
										"L" => array()
									);

								}

								// Запись типа "Л" (запись о праве гражданина на получение государственной социальной помощи (далее - ГСП) в виде набора социальных услуг (далее - НСУ))
								if (mb_substr($value, 0, 1) == "Л") {

									$words[$id]["L"][] = array(
										"ss" => $ss,
										"c_katl" => trim(mb_substr($value, 1, 3)),
										"name_dl" => trim(mb_substr($value, 4, 80)),
										"s_dl" => trim(mb_substr($value, 84, 8)),
										"n_dl" => trim(mb_substr($value, 92, 10)),
										"sn_dl" => trim(mb_substr($value, 102,10)),
										"Org" => trim(mb_substr($value, 112, 80)),
										"date_bl" => trim(mb_substr($value, 192, 10)),
										"date_el" => (mb_substr($value, 202, 1) == "9") ? "2030/01/01" : trim(mb_substr($value, 202, 10)),
										"frlPrivilege_isWhoGSP"=>trim(mb_substr($value, 212,1)),
										"frlPrivilege_Rezerv"=>trim(mb_substr($value, 213,10))
									);

									if($id > 150000){ // Определенный лимит, после которого сохраняем массив, затем обнуляем счетчик и очищаем массив
                                        if (strtolower($fl) == "ms0") {


                                            $res = $this->ImportSchema_model->run($_POST['RegisterList_Name'], null, $_POST['RegisterList_id'], $words, $ignoredelete);
                                            $id = 0;
                                            $words = array();

                                            $ignoredelete = true;
                                        }
                                    }
								}
							}


							fclose($f);


						}
						else {
							foreach ($_FILES["sourcefiles"]["error"] as $key => $error) {
								if ($error == UPLOAD_ERR_OK) {
									$fl = substr($_FILES["sourcefiles"]["name"][$key], strlen($_FILES["sourcefiles"]["name"][$key]) - 3);
									if ($_POST["RegisterList_Name"] == "PersonDead") {
										move_uploaded_file($_FILES["sourcefiles"]["tmp_name"][$key], $tmp_folder . '/dead.DBF');
										$files[] = $tmp_folder . '/dead.DBF';
										$this->textlog->add("Load deadPerson");
									} else if(strtolower($fl) == "dbf"||strtolower($fl) == "xml"){
										$this->ImportSchema_model->setVersion("5");
										move_uploaded_file($_FILES["sourcefiles"]["tmp_name"][$key], $tmp_folder . '/' . $_FILES["sourcefiles"]["name"][$key]);
										$files[] = $tmp_folder . '/' . $_FILES["sourcefiles"]["name"][$key];
										$this->textlog->add("Load version 5.0");
									}else{
										@unlink($tmp_folder);
										$this->textlog->add('Ошибка: Неверное расширение загружаемого файла');
										throw new Exception('Неверное расширение загружаемого файла');
									}
								} else {
									@unlink($tmp_folder);
									$this->textlog->add('Ошибка: Не удалось загрузить все файлы');
									throw new Exception('Не удалось загрузить все файлы');
								}
							}
						}
					}

					// Если передан архив, то ожидаем внутри файлы .xml
					if ( ! empty($_FILES['ziparch'])) {
						if ($_FILES['ziparch']["tmp_name"] != "" && ! empty($_POST['FRMP'])) {

							$zip = new ZipArchive;
							$zip->open($_FILES['ziparch']["tmp_name"]);
							foreach(explode(',', $_POST['FRMP']) as $key => $value) {
								if ($zip->getFromName($value . ".xml")) {
									$zip->extractTo($tmp_folder, $value . ".xml");
									$files[] = $tmp_folder . '/' . $value . ".xml";
								}
							}
							$zip->close();
						} else {
							@unlink($tmp_folder);
							throw new Exception('Не удалось загрузить все файлы');
						}
					}

					if ( ! empty($_POST['RegisterList_Name'])) {
						if(strtolower($fl) == "ms0"){
							$res = $this->ImportSchema_model->run($_POST['RegisterList_Name'], null, $_POST['RegisterList_id'], $words, $ignoredelete, $fileName);
						}
						else if(strtolower($fl) == "ms1"){
							$res = $this->ImportSchema_model->runMS1($_POST['RegisterList_id'], $words, $ignoredelete, $fileName);
						}
						else if('Org' == $_POST['RegisterList_Name']){
							// Начинаем импорт организаций из xml файла(ов)
							$res = $this->importXmlOrg($files, $fileName);
						}
						else if ('CmpCallCard' == $_POST['RegisterList_Name']){
							$this->textlog->add("Начинаем импорт карт СМП из dbf файла");
							$this->db2 = $this->load->database('registry', true);
							$res = $this->ImportSchema_model->importCmpCallCardFromDBF($files, $fileName);
						}
						else {
							$res = $this->ImportSchema_model->run($_POST['RegisterList_Name'], $tmp_folder . '/', $_POST['RegisterList_id'], $files, false, $fileName);
						}
						$this->ProcessModelSave($res)->ReturnData();
					}
					else {
						throw new Exception('Не указаны обязательные параметры для запуска импорта');
					}

				}
				else {
					throw new Exception('Не удалось создать временный каталог');
				}

			}
			else {
				throw new Exception('Не удалось создать временный файл');
			}


		} catch (Exception $ex) {
			throw new Exception('Не удалось сохранить запись в таблицу.' . $ex->getMessage());
		}
	}

	/**
	 *	Импорт органиазции
	 * @return type
	 */
	public function importXmlOrg($files, $fileName=false) {

		set_time_limit(100000);
		ini_set("memory_limit", "2024M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");

		//посылаем ответ клиенту...
		ignore_user_abort(true);
		ob_start();
		echo json_encode(array("success" => "true"));

		$size = ob_get_length();

		header("Content-Length: $size");
		header("Content-Encoding: none");
		header("Connection: close");

		ob_end_flush();
		ob_flush();
		flush();

		if (session_id())
			session_write_close();

		//... и продолжаем выполнять скрипт на стороне сервера.
		libxml_use_internal_errors(true); // изменить на false для вывода ошибок в консоль
		error_reporting(0); // что бы не выводились предупреждения при отладке

		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');
		$this->load->model('Org_model', 'Org_model');

		$pmUserId = $this->ImportSchema_model->getPmUserId();

		$this->RegisterListLog_model->setpmUser_id($pmUserId);
		$this->RegisterListLog_model->setRegisterList_id(24);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		if(!empty($fileName)) $this->RegisterListLog_model->setRegisterListLog_NameFile($fileName);

		$this->RegisterListLog_model->save();
		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();
		//RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Запуск импорта организаций', $RegisterListLog_id, $pmUserId);

		$orgCounter = 0;
		$addedUpdatedOrgCounter = 0;
		foreach ($files as $key => $value) {
			$format = '';
			$filename = basename($value);
			$prefix = substr($filename,0,8);
			if($prefix=="VO_RUGFO" or $prefix=="VO_RUGFZ") {
				$format = 'ЮЛ';
			} else if($prefix=="VO_RIGFO" or $prefix=="VO_RIGFZ") {
				$format = 'ИП';
			}
			$xml_file_path = $value;

			//Преобразует simpleXML в массив
			//гружу с глушением ошибок @ потому что высирает некритичную для нас нелепицу про схемы
			$xmlcont = @new SimpleXMLElement(file_get_contents($xml_file_path));
			if (!is_object($xmlcont)) {
				$this->textlog->add('importXmlOrg: В файле '.$key.' не удалось разобрать XML');
				continue;
			}

			$OrgData = array();
			if($format!='') { //новый формат XML с 1 февраля 2017 года
				foreach($xmlcont as $row) {
					if ($row->getName() == 'Документ') {
						foreach ($row->children() as $org) {
							if($format=='ЮЛ' and $org->getName()=='СвЮЛ') {
								$orgCounter++;
								$kladr = !empty($org->СвАдресЮЛ->АдресРФ->attributes()->КодАдрКладр) ? substr($org->СвАдресЮЛ->АдресРФ->attributes()->КодАдрКладр,0,17) : '';
								// Формат КЛАДР (13 симв.): ССРРРГГГПППУУ  (С-регион,Р-район,Г-город,П-нас.пункт,У-улица)
								$KodRegion = !empty($kladr) ? substr($kladr,0,2) : '00';// регион
								$KodRaion = !empty($kladr) ? substr($kladr,2,3) : '000';//район
								$KodGorod = !empty($kladr) ? substr($kladr,5,3) : '000';//город
								$KodNasPunkt = !empty($kladr) ? substr($kladr,8,3) : '000';//населенный пункт
								$KodStreet = !empty($kladr) ? $kladr : '00';//улица

								$Dom = !empty($org->СвАдресЮЛ->АдресРФ->attributes()->Дом) ? (string)$org->СвАдресЮЛ->АдресРФ->attributes()->Дом : '';
								preg_match('/\d+.*/', $Dom, $matches);
								$DomNumber = $matches!=null ? $matches[0] : '';
								$Kvart = !empty($org->СвАдресЮЛ->АдресРФ->attributes()->Кварт) ? (string)$org->СвАдресЮЛ->АдресРФ->attributes()->Кварт : '';
								preg_match('/\d+.*/', $Kvart, $matcheskv);
								$KvartNumber = $matcheskv!=null ? $matcheskv[0] : '';

								$p = array(
									'Org_begDate' => !empty($org->СвОбрЮЛ->attributes()->ДатаРег) ? $org->СвОбрЮЛ->attributes()->ДатаРег : (!empty($org->СвОбрЮЛ->attributes()->ДатаОГРН) ? (string)$org->СвОбрЮЛ->attributes()->ДатаОГРН : ''),
									'Org_endDate' => !empty($org->СвПрекрЮЛ->attributes()->ДатаОГРН) ? (string)$org->СвПрекрЮЛ->attributes()->ДатаОГРН : '',
									'Org_Name' => !empty($org->СвНаимЮЛ->attributes()->НаимЮЛПолн) ? (string)$org->СвНаимЮЛ->attributes()->НаимЮЛПолн : '',
									'Org_Nick' => !empty($org->СвНаимЮЛ->attributes()->НаимЮЛСокр) ? (string)$org->СвНаимЮЛ->attributes()->НаимЮЛСокр : '',
									//КЛАДР
									'INDEKS' => !empty($org->СвАдресЮЛ->АдресРФ->attributes()->Индекс) ? (string)$org->СвАдресЮЛ->АдресРФ->attributes()->Индекс : '',
									'DOM' => $DomNumber,
									'KVART' => $KvartNumber,
									'REGION' => $KodRegion!='00' ? str_pad(substr($kladr,0,2), 13, 0) : '',
									'RAION' => $KodRaion!='000' ? str_pad(substr($kladr,0,5), 13, 0) : '',
									'GOROD' => $KodGorod!='000' ? str_pad(substr($kladr,0,8), 13, 0) : '',
									'NASPUNKT' => $KodNasPunkt!='000' ? str_pad(substr($kladr,0,11), 13, 0) :'',
									'STREET' => $KodStreet!='00' ? str_pad($kladr, 17, 0) : '',
									//КЛАДР конец
									'Org_OGRN' => !empty($org->attributes()->ОГРН) ? (string)$org->attributes()->ОГРН : '',
									'Org_INN' => !empty($org->attributes()->ИНН) ? (string)$org->attributes()->ИНН : '',
									'Org_KPP' => !empty($org->attributes()->КПП) ? (string)$org->attributes()->КПП : '',
									'Okved_Code' => !empty($org->СвОКВЭД->СвОКВЭДОсн) ? (string)$org->СвОКВЭД->СвОКВЭДОсн->attributes()->КодОКВЭД : '',
									'Okopf_Code' => !empty($org->attributes()->КодОПФ) ? (string)$org->attributes()->КодОПФ : '',
									'Org_Phone' => !empty($org->attributes()->НомТел) ? (string)$org->attributes()->НомТел : ''
								);
							} else if($format=='ИП' and $org->getName()=='СвИП') {
								$orgCounter++;
								$p = array(
									'Org_begDate'=>	!empty($org->attributes()->ДатаОГРНИП) ? (string)$org->attributes()->ДатаОГРНИП : '',
									'Org_Name' => 	!empty($org->СвФЛ->ФИОРус->attributes()->Фамилия) ? ('ИП ' . ((string)$org->СвФЛ->ФИОРус->attributes()->Фамилия) . ' ' . ((string)$org->СвФЛ->ФИОРус->attributes()->Имя) . ' ' . ((string)$org->СвФЛ->ФИОРус->attributes()->Отчество)) : '',
									'Org_Nick' => 	!empty($org->СвФЛ->ФИОРус->attributes()->Фамилия) ? ('ИП ' . ((string)$org->СвФЛ->ФИОРус->attributes()->Фамилия) . ' ' . ((string)$org->СвФЛ->ФИОРус->attributes()->Имя) . ' ' . ((string)$org->СвФЛ->ФИОРус->attributes()->Отчество)) : '',
									'Org_OGRN' => 	!empty($org->attributes()->ОГРНИП) ? (string)$org->attributes()->ОГРНИП : '',
									'Org_INN' => 	!empty($org->attributes()->ИННФЛ) ? (string)$org->attributes()->ИННФЛ : '',
									'Okved_Code' => !empty($org->СвОКВЭД->СвОКВЭДОсн) ? (string)$org->СвОКВЭД->СвОКВЭДОсн->attributes()->КодОКВЭД : '',
									'Org_Email' => 	!empty($org->attributes()->{'E-mail'}) ? (string)$org->attributes()->{'E-mail'} : ''
								);
							}
							$p['Org_StickNick'] = !empty($p['Org_Nick']) ? str_replace(array('"',"'"), '', $p['Org_Nick']) : '';
						}
						if (!empty($p)) {
							//меняем формат даты
							$p['Org_begDate'] = !empty($p['Org_begDate']) ? date('Y-m-d', strtotime($p['Org_begDate'])) : null;
							$p['Org_endDate'] = !empty($p['Org_endDate']) ? date('Y-m-d', strtotime($p['Org_endDate'])) : null;

							//Определяем тип организации: организации - 10 знаков, ИП - 12
							if (!empty($p['Org_INN']) && strlen($p['Org_INN']) == 12) {
								$p['OrgType_id'] = 20;
							} else {
								$p['OrgType_id'] = null;
							}
							array_push($OrgData, $p);
							unset($p);
						}
					}
				}
			} else {
				foreach ($xmlcont as $row) {
					if ($row->getName() == 'UL') {

						$orgCounter++;
						$this->_deniedElsToCompare = array('KODGOROD', 'TELEFON', 'INDEKS', 'DOM', 'KVART', 'REGION', 'RAION', 'GOROD', 'STREET', '', '');

						$p = array(
							'Org_begDate' => !empty($row->UL_START->attributes()->DTREG) ? (string)$row->UL_START->attributes()->DTREG : '',
							'Org_endDate' => !empty($row->UL_FINISH->attributes()->DTREG) ? (string)$row->UL_FINISH->attributes()->DTREG : '',
							'Org_Name' => !empty($row->UL_NAME->attributes()->NAMEP) ? (string)$row->UL_NAME->attributes()->NAMEP : '',
							'Org_Nick' => !empty($row->UL_NAME->attributes()->NAMES) ? (string)$row->UL_NAME->attributes()->NAMES : '',
							//КЛАДР
							'INDEKS' => !empty($row->UL_ADDRESS->ADDRESS->attributes()->INDEKS) ? (string)$row->UL_ADDRESS->ADDRESS->attributes()->INDEKS : '',
							'DOM' => !empty($row->UL_ADDRESS->ADDRESS->attributes()->DOM) ? (string)$row->UL_ADDRESS->ADDRESS->attributes()->DOM : '',
							'KVART' => !empty($row->UL_ADDRESS->ADDRESS->attributes()->KVART) ? (string)$row->UL_ADDRESS->ADDRESS->attributes()->KVART : '',
							'REGION' => !empty($row->UL_ADDRESS->ADDRESS->REGION->attributes()->KOD_KL) ? (string)$row->UL_ADDRESS->ADDRESS->REGION->attributes()->KOD_KL : '',
							'RAION' => !empty($row->UL_ADDRESS->ADDRESS->RAION->attributes()->KOD_KL) ? (string)$row->UL_ADDRESS->ADDRESS->RAION->attributes()->KOD_KL : '',
							'GOROD' => !empty($row->UL_ADDRESS->ADDRESS->GOROD->attributes()->KOD_KL) ? (string)$row->UL_ADDRESS->ADDRESS->GOROD->attributes()->KOD_KL : '',
							'STREET' => !empty($row->UL_ADDRESS->ADDRESS->STREET->attributes()->KOD_ST) ? (string)$row->UL_ADDRESS->ADDRESS->STREET->attributes()->KOD_ST : '',
							//КЛАДР конец
							'Org_OGRN' => !empty($row->attributes()->OGRN) ? (string)$row->attributes()->OGRN : '',
							'Org_INN' => !empty($row->attributes()->INN) ? (string)$row->attributes()->INN : '',
							'Org_KPP' => !empty($row->attributes()->KPP) ? (string)$row->attributes()->KPP : '',
							'Okved_Code' => !empty($row->OKVED->attributes()->KOD_OKVED) ? (string)$row->OKVED->attributes()->KOD_OKVED : '',

							//На счёт этих полей не уверен, надо перепроверить когда появятся примеры с данными
							'Org_OKATO' => !empty($row->attributes()->OKATO) ? (string)$row->attributes()->OKATO : '',
							'Okopf_Code' => !empty($row->attributes()->KOD_OPF) ? (string)$row->attributes()->KOD_OPF : '',
							'KODGOROD' => !empty($row->attributes()->KODGOROD) ? (string)$row->attributes()->KODGOROD : '',
							'TELEFON' => !empty($row->attributes()->TELEFON) ? (string)$row->attributes()->TELEFON : ''
						);

					} else if ($row->getName() == 'IP') {
						$orgCounter++;
						$this->_deniedElsToCompare = array('KODGOROD', 'TELEFON');

						$p = array(
							'Org_begDate' => !empty($row->FL->attributes()->DTSTART) ? (string)$row->FL->attributes()->DTSTART : '',
							'Org_Name' => !empty($row->FL->attributes()->FAM_FL) ? ('ИП ' . ((string)$row->FL->attributes()->FAM_FL) . ' ' . ((string)$row->FL->attributes()->NAME_FL) . ' ' . ((string)$row->FL->attributes()->OTCH_FL)) : '',
							'Org_Nick' => !empty($row->FL->attributes()->FAM_FL) ? ('ИП ' . ((string)$row->FL->attributes()->FAM_FL) . ' ' . ((string)$row->FL->attributes()->NAME_FL) . ' ' . ((string)$row->FL->attributes()->OTCH_FL)) : '',
							'Org_OGRN' => !empty($row->attributes()->OGRNIP) ? (string)$row->attributes()->OGRNIP : '',
							'Org_INN' => !empty($row->attributes()->INN) ? (string)$row->attributes()->INN : '',
							'Okved_Code' => !empty($row->OKVED->attributes()->KOD_OKVED) ? (string)$row->OKVED->attributes()->KOD_OKVED : '',
							'KODGOROD' => !empty($row->attributes()->KODGOROD) ? (string)$row->attributes()->KODGOROD : '',
							'TELEFON' => !empty($row->attributes()->TELEFON) ? (string)$row->attributes()->TELEFON : ''
						);
					}
				}

				if (!empty($p)) {
					//меняем формат даты
					$p['Org_begDate'] = !empty($p['Org_begDate']) ? date('Y-m-d', strtotime($p['Org_begDate'])) : null;
					$p['Org_endDate'] = !empty($p['Org_endDate']) ? date('Y-m-d', strtotime($p['Org_endDate'])) : null;

					//Определяем тип организации: организации - 10 знаков, ИП - 12
					if (!empty($p['Org_INN']) && strlen($p['Org_INN']) == 12) {
						$p['OrgType_id'] = 20;
					} else {
						$p['OrgType_id'] = null;
					}
					array_push($OrgData, $p);
					unset($p);
				}
			}

			if (count($OrgData) == 0) {
				$this->textlog->add('importXmlOrg: В файле '.$key.' не найдено организаций');
				continue;
			}

			$this->load->model('Org_model', 'Org_model');
			foreach ($OrgData as $k => $v) {
				$orgDataXml = array('pmUser_id' => $pmUserId);
				$orgDataXml = array_merge($orgDataXml,$v);

				//убираем пустые значения
				$orgDataXml = $this->ImportSchema_model->cleanArray($orgDataXml);

				//Тащим параметры организации
				$res = $this->ImportSchema_model->getOrgXmlActionAndParams($v);

				if ($res === false) {
					$this->textlog->add('importXmlOrg: При получении данных по организации произошла ошибка. Org_INN = '.$v['Org_INN'].', Org_OGRN = '.$v['Org_OGRN'].', Org_KPP = '.$v['Org_KPP']);

					RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, 'При получении данных по организации произошла ошибка.
						Org_INN - ' . $v['Org_INN'] . "\n
						Org_OGRN - " . $v['Org_OGRN'] . "\n
						Org_KPP - " . $v['Org_KPP'] . "\n", $RegisterListLog_id, $pmUserId);
					continue;
				}

				// @task https://redmine.swan.perm.ru/issues/79781
				if ( is_array($res) && count($res) > 0 ) {
					foreach ( $res as $keyTmp => $row ) {
						if ( !empty($row['OrgType_id']) && in_array($row['OrgType_id'], array(3, 11)) ) {
							$this->textlog->add('importXmlOrg: Запись не обработана, тип организации МО или СМО. OrgType_id = ' . $row['OrgType_id'] . ', Org_INN = '.$v['Org_INN'].', Org_OGRN = '.$v['Org_OGRN'].', Org_KPP = '.$v['Org_KPP']);

							RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, "OrgType_id - " . $row['OrgType_id'] . "\n
								Org_INN - " . $v['Org_INN'] . "\n
								Org_OGRN - " . $v['Org_OGRN'] . "\n
								Org_KPP - " . $v['Org_KPP'] . "\n", $RegisterListLog_id, $pmUserId
							);

							unset($res[$keyTmp]);
						}
					}

					if ( count($res) == 0 ) {
						continue;
					}
				}

				//определяем что делать с организацией - добавлять или обновлять
				if (is_array($res) && count($res) == 1 && !empty($res[0]['Org_id'])){

					//обновляем организацию
					$addedUpdatedOrgCounter = $addedUpdatedOrgCounter + $this->isAnyDifference($v, $res[0]);
					$orgDataXml = array_merge($res[0], $orgDataXml);
				} else if (is_array($res) && count($res) != 1 && !empty($res[0]['Org_id'])){
					if (!empty($orgDataXml['OrgType_id']) && $orgDataXml['OrgType_id'] == '19') {
						foreach ($res as $r_key => $r_value) {
							if ($r_value['Org_OKATO'] == $v['Org_OKATO'] && $r_value['Org_Name'] == $v['Org_Name']) {

								//обновляем органиазацию
								$addedUpdatedOrgCounter = $addedUpdatedOrgCounter + $this->isAnyDifference($v, $r_value);
								$orgDataXml = array_merge($r_value, $orgDataXml);
								break;
							}
						}

						//Если организация ещё не найдена - ослабляем условия
						if (empty($orgDataXml['Org_id'])) {
							foreach ($res as $r2_key => $r2_value) {
								if ($r2_value['Org_OKATO'] == $v['Org_OKATO']) {

									//обновляем органиазацию
									$addedUpdatedOrgCounter = $addedUpdatedOrgCounter + $this->isAnyDifference($v, $r2_value);
									$orgDataXml = array_merge($r2_value, $orgDataXml);
									break;
								}
							}
						}

						if (empty($orgDataXml['Org_id'])) {
							$addedUpdatedOrgCounter++;
						}
					} else /*if ($orgDataXml['OrgType_id'] == '20')*/ {
						//Если найдена организация с таким же наименованием то обновляем её, меняя тип
						if (empty($orgDataXml['Org_id'])) {
							foreach ($res as $r2_key => $r2_value) {
								if ($r2_value['Org_Name'] == $v['Org_Name']) {

									//обновляем органиазацию
									$addedUpdatedOrgCounter = $addedUpdatedOrgCounter + $this->isAnyDifference($v, $r2_value);
									$orgDataXml = array_merge($r2_value, $orgDataXml);
									break;
								}
							}
						}

						if (empty($orgDataXml['Org_id'])) {
							$addedUpdatedOrgCounter++;
						}
					}
				} else if (empty($res['Error_Msg'])){
					$addedUpdatedOrgCounter++;
				}
				//Сохраняем адреса организаци
				if (empty($orgDataXml['OrgType_id']) || $orgDataXml['OrgType_id'] != '20') {
					$res_adr = $this->Org_model->saveOrgXmlAddress($orgDataXml);
					if (!$res_adr) {
						$this->textlog->add('importXmlOrg: При получении данных по адресам организации произошла ошибка. Org_INN = ' . $v['Org_INN'] . ', Org_OGRN = ' . $v['Org_OGRN'] . ', Org_KPP = ' . $v['Org_KPP']);

						RegisterListDetailLog_model::createLogMessage(new DateTime(), 2, 'При получении данных по адресам организации произошла ошибка.
							Org_INN - ' . $v['Org_INN'] . "\n
							Org_OGRN - " . $v['Org_OGRN'] . "\n
							Org_KPP - " . $v['Org_KPP'] . "\n", $RegisterListLog_id, $pmUserId);
					}
				}

				$orgDataXml['PAddress_id'] = !empty($res_adr) && (is_array($res_adr) && !empty($res_adr['PAddress_id']))?$res_adr['PAddress_id']:null;
				$orgDataXml['UAddress_id'] = !empty($res_adr) && (is_array($res_adr) && !empty($res_adr['UAddress_id']))?$res_adr['UAddress_id']:null;

				//сохраняем/обновляем организацию
				$this->Org_model->saveOrgXml($orgDataXml);
			}
		}

		$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
		$this->RegisterListLog_model->setRegisterListLog_AllCount($orgCounter); //Всего организаций во всех файлах
		$this->RegisterListLog_model->setRegisterListLog_UploadCount($addedUpdatedOrgCounter); //Загружено и обновлено организаций
		$this->RegisterListLog_model->setRegisterListResultType_id(1);
		$this->RegisterListLog_model->save();

		$this->textlog->add('importXmlOrg: Закончен импорт организаций из всех файлов');
	}

	/**
	 * Получение статуса
	 */
	public function getStatus() {

		var_export($this->ImportSchema_model->getStatus());
	}

	/**
	 * Проверяет все ли элементы первого массива соответствуют одноименным элементам второго массива, за исключением элементов с наименованиями из &_deniedElsToCompare
	 */
	public function isAnyDifference($arr1, $arr2) {

		if (!is_array($arr1) || !is_array($arr2)){
			return 0;
		}

		foreach ($arr1 as $key => $value){
			if ($arr2[$key] != $value && !in_array($key,$this->_deniedElsToCompare)){
				return 1;
			}
		}

		return 0;
	}

	/**
	 *
	 * @return type 
	 */
	public function Export(){
		$data = array();
		$data = $this->ProcessInputData('Export', true);
		if ($data === false) {
			return false;
		}
		set_time_limit(100000);
		ini_set("memory_limit", "1024M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");
		$Privilege = $this->ImportSchema_model->createPrivelegeDBF($data);
		$Person = $this->ImportSchema_model->createPersonDBF($data);
		$fname = substr(md5(time()), 0, 5);
		$zipname = "export/". $fname . ".zip";
		$zip = new ZipArchive();
		$zip->open($zipname, ZIPARCHIVE::CREATE);
		$zip->AddFile($Privilege, "l0.dbf");
		$zip->AddFile($Person, "r0.dbf");
		$zip->close();
		unlink($Privilege);
		unlink($Person);
		$this->ReturnData(array('success' => true, 'url' => $zipname));
	}
	/**
	 *
	 * Экспорт тарификационного листа
	 */
	public function exportTarifList() {
		$data = array();
		$data = $this->ProcessInputData('expTarifList', true);
		if ($data === false) {
			return false;
		}
		$this->ImportSchema_model->createDBF($data);
		$this->ReturnData();
	}

}
