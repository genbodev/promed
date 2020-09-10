<?php

/**
 *
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Reg
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 */
class ImportInsured_model extends swPgModel {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		$this->badIdent = array(
			1 => 'Застрахованный умер',
			2 => 'Застрахован другой СМО РК',
			3 => 'Застрахован вне территории РК',
			4 => 'Выдача ВС в другой СМО',
			5 => 'Полис закрыт в связи с выявлением дубликата',
			6 => 'Прочие причины закрытия полиса (в том числе невозможность определить причину закрытия)',
			7 => 'У застрахованного другой открытый полис, СМО добавлены данные нового полиса',
			8 => 'ФИО или дата рождения не совпадают (при совпадении серии и номера полиса)',
			9 => 'Застрахованный не найден ни по ФИО и ДР, ни по номеру и серии полиса',
			10 => 'Прочие причины невозможности идентификации',
			11 => 'Аннулирование страховки (военнослужащие и прочее)',
			12 => 'Возраст застрахованного не соответствует профилю МО (взрослая, детская)',
			13 => 'Дата прикрепления позже отчетной даты сверки численности',
			14 => 'Поля "Номер участка", "Тип участка" и/или "СНИЛС медицинского работника участка" не заполнены или заполнены некорректно',
			15 => 'Поле "СНИЛС медицинского работника участка": не заполнено, заполнено некорректно или по указанному для застрахованного лица СНИЛС медицинского работника не найден в Региональном регистре медицинского персонала МО, к которой прикреплен застрахованный'
		);
		$this->polisType = array(
			1=>'полис ОМС старого образца',
			2=>'временное свидетельство',
			3=>'полис ОМС единого образца'

		);
		$this->caseVid = array(
			1=>'новорожденный (до года)',
			2=>'изменение ФИО',
			3=>'впервые застрахован на территории Республики Карелия',
			4=>'смена СМО',
			5=>'первичный выбор СМО (ранее не был застрахован в системе ОМС)',
			6=>'предыдущий полис был закрыт'
		);


		$this->counter = array();
	}

	private $type;
	private $SMOCode;
	private $MOCode;
	private $fileName;
	private $caseVid;
	private $badIdent;
	private $counter;
	private $polisTypeArray;
	private $fileZL;
	private $fileLog;
	private $polisType;

	/**
	 *
	 * @param type $name
	 */
	public function setFileName($name) {
		$this->fileName = $name;
	}

	/**
	 *
	 * @param type $smo
	 */
	public function setSMOCode($smo) {
		$this->SMOCode = $smo;
	}

	/**
	 *
	 * @param type $mo
	 */
	public function setMOCode($mo) {
		$this->MOCode = $mo;
	}


	/**
	 *
	 * @param type $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 *
	 * @param type $xml
	 * @return type
	 */
	private function toArray($xml) {
		foreach ($xml as $k => $v) {
			if (is_array($v)) {
				if (isset($v['i'])) {
					$xml[$k] = $v['i'];
				} else {
					if (empty($xml[$k])) {
						$xml[$k] = NULL;
					}else
						$xml[$k] = $this->toArray($v);
				}
			}
		}
		return $xml;
	}
	/**
	 *
	 * @param SimpleXMLElement $xml
	 * @param string $attributesKey
	 * @param string $childrenKey
	 * @param string $valueKey
	 * @return type
	 */
	function simpleXMLToArray(SimpleXMLElement $xml,$attributesKey=null,$childrenKey=null,$valueKey=null){
		if($childrenKey && !is_string($childrenKey)){$childrenKey = '@children';}
		if($attributesKey && !is_string($attributesKey)){$attributesKey = '@attributes';}
		if($valueKey && !is_string($valueKey)){$valueKey = '@values';}

		$return = array();
		$name = $xml->getName();
		$_value = trim((string)$xml);
		if(!strlen($_value)){$_value = null;};

		if($_value!==null){
			if($valueKey){$return[$valueKey] = $_value;}
			else{$return = $_value;}
		}

		$children = array();
		$first = true;
		$el = "";
		foreach($xml->children() as $elementName => $child){
			$value = $this->simpleXMLToArray($child,$attributesKey,$childrenKey,$valueKey);

			if ($el != $elementName) { // Если элемент меняется и такой же еще не существует, то считаем, что это первый
				$el = $elementName;
				if  (!isset($children[$elementName])) {
					$first = true;
				} else {
					$first = false;
				}
			}

			if(isset($children[$elementName])){
				if(is_array($children[$elementName])){
					if($first&&$elementName!='PR_CODE_MO'){
						$temp = $children[$elementName];
						unset($children[$elementName]);
						$children[$elementName][] = $temp;
						$first=false;
					}
					$children[$elementName][] = $value;
				}else{
					$children[$elementName] = array($children[$elementName],$value);
				}
			}
			else{
				$children[$elementName] = $value;
			}
		}
		if($children){
			if($childrenKey){$return[$childrenKey] = $children;}
			else{$return = array_merge($return,$children);}
		}

		$attributes = array();
		foreach($xml->attributes() as $name=>$value){
			$attributes[$name] = trim($value);
		}
		if($attributes){
			if($attributesKey){$return[$attributesKey] = $attributes;}
			else{
				if (is_array($return)) {
					$return = array_merge($return, $attributes);
				} else {
					// TODO: добавить обработку вместо с $return
					print $return;
				}
			}
		}

		return $return;
	}
	/**
	 * Получение списка вызовов на дом на дату по ЛПУ
	 */
	function Import($data) {
		set_time_limit(100000);
		ini_set("memory_limit", "2024M");
		ini_set("max_execution_time", "0");
		ini_set("max_input_time", "0");
		ini_set("post_max_size", "220");
		ini_set("default_socket_timeout", "999");
		ini_set("upload_max_filesize", "220M");
		$this->db->save_queries = false;

		$this->checkXsd($data);
		/**************/
		$this->load->model('RegisterListLog_model', 'RegisterListLog_model');
		$this->load->model('RegisterListDetailLog_model', 'RegisterListDetailLog_model');

		if(isset($data['pmUser_id'])){
			$this->RegisterListLog_model->setpmUser_id($data['pmUser_id']);
		} else {
			$this->RegisterListLog_model->setpmUser_id(1);
		}
		$this->RegisterListLog_model->setRegisterList_id($data['RegisterList_id']);
		$this->RegisterListLog_model->setRegisterListLog_begDT(new DateTime());
		$this->RegisterListLog_model->setRegisterListRunType_id(8); //8-вручную
		$this->RegisterListLog_model->setRegisterListResultType_id(2); //2-Выполняется
		$this->RegisterListLog_model->save();
		$RegisterListLog_id = $this->RegisterListLog_model->getRegisterListLog_id();

		RegisterListDetailLog_model::createLogMessage(new DateTime(), 1, 'Начало выполнения ', $this->RegisterListLog_model->getRegisterListLog_id(), 1);
		/***************/

		if($this->type=="NP"){
			$this->fileZL = fopen("export/NPLog".$RegisterListLog_id.".txt", "w");
		}else{
			$this->fileZL = fopen("export/ELog".$RegisterListLog_id.".txt", "w");
		}
		$this->fileLog = fopen("export/ImportInsuredLog".$RegisterListLog_id.".txt", "w");
		//log - Первой строкой лога должна быть информация  об имени файла, дате и времени начала запуска импорта. Далее следует указать количество записей в файле.

		$x = new SimpleXMLElement(file_get_contents($data['FileFullName']));
		$arr = $this->toArray($this->simpleXMLToArray($x));
		//echo"<pre>";print_r($arr);echo"</pre>";exit();
		if(isset($arr['PERS']['FAM'])){
			fputs($this->fileLog,date('Y-m-d H:i')." Файл - " . $this->fileName . "; Количество записей в файле - 1 \r\n");
		}else{
			fputs($this->fileLog,date('Y-m-d H:i')." Файл - " . $this->fileName . '; Количество записей в файле - ' . count($arr['PERS'])." \r\n");
		}
		$this->counter = array(
			'prin' => 0,
			'doubPrik' => 0,
			'badident' => 0,
			'reident' => 0,
			'add'=>0,
			'bad'=>0,
			'confl' => 1,
			'errIdent'=>1
		);

		ignore_user_abort(true);

		//посылаем ответ клиенту...
		if (function_exists('fastcgi_finish_request')) {
			echo json_encode(array("success" => "true"));
			fastcgi_finish_request();
		} else {
			ob_start();
			echo json_encode(array("success" => "true"));

			$size = ob_get_length();

			header("Content-Length: $size");
			header("Content-Encoding: none");
			header("Connection: close");

			ob_end_flush();
			ob_flush();
			flush();
		}

		try{
			if(isset($arr['PERS']['FAM'])){
				switch ($this->type) {
					case "E":
						$res = $this->importE($arr['PERS']);
						break;
					case "NP":
						$res = $this->importNP($arr['PERS']);
						break;
					default:
						throw new Exception('Это магия.'); //по идее сюда никак не попадет
						break;
				}
			}else{
				foreach ($arr['PERS'] as $persData) {

					switch ($this->type) {
						case "E":
							$res = $this->importE($persData);
							break;
						case "NP":
							$res = $this->importNP($persData);
							break;
						default:
							throw new Exception('Это магия.'); //по идее сюда никак не попадет
							break;
					}
				}
			}

			if($this->type=='E'){
				fputs($this->fileLog,'Принятые записи: '.$this->counter['prin'].'; Записи с двойным прикрилением: '.$this->counter['doubPrik'].'; Не идентифицированные: '.$this->counter['badident'].'; Повторно отправленные: '.$this->counter['reident'].';');
			}else{
				fputs($this->fileLog,'Успешно обновлены/добавлены: '.$this->counter['add'].'; Незагружены записи: '.$this->counter['bad'].';');
			}
			fclose($this->fileZL);
			fclose($this->fileLog);
			$this->RegisterListLog_model->setRegisterListResultType_id(1); //1-успешно
		}
		catch(Exception $e){
			if(isset($data['pmUser_id'])){
				$this->RegisterListDetailLog_model->setpmUser_id($data['pmUser_id']);
			} else {
				$this->RegisterListDetailLog_model->setpmUser_id(1);
			}
			$this->RegisterListDetailLog_model->setRegisterListLog_id($this->RegisterListLog_model->getRegisterListLog_id());
			$this->RegisterListDetailLog_model->setRegisterListLogType_id(2);
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_Message($e->getMessage() . '. Техническая информация: ' . var_export($e, true));
			$this->RegisterListDetailLog_model->setRegisterListDetailLog_setDT(new DateTime());
			$this->RegisterListDetailLog_model->save();
			$this->RegisterListLog_model->setRegisterListLog_endDT(new Datetime());
			$this->RegisterListLog_model->setRegisterListResultType_id(3); //3-завершено с ошибкой
		}
		$this->RegisterListLog_model->save();

		$zipname = EXPORTPATH_ROOT."ImportIns".$RegisterListLog_id.".zip";
		if(is_file($zipname)){
			unlink($zipname);
		}
		$zip = new ZipArchive();
		$zip->open($zipname, ZIPARCHIVE::CREATE);
		$zip->AddFile(EXPORTPATH_ROOT."ImportInsuredLog".$RegisterListLog_id.".txt", "ImportInsuredLog.txt");

		if($this->type=="NP"){
			$zip->AddFile(EXPORTPATH_ROOT."NPLog".$RegisterListLog_id.".txt", "NPLog.txt");
		}else{
			$zip->AddFile(EXPORTPATH_ROOT."ELog".$RegisterListLog_id.".txt", "ELog.txt");
		}
		//$zip->AddFile(EXPORTPATH_ROOT."ImportInsured".((mb_substr($file['name'], 0, 2)=="NP")?"NP":"E").".txt","ImportInsured".((mb_substr($file['name'], 0, 2)=="NP")?"NP":"E").".txt");
		$zip->close();
		return array(array('success' => true));
	}

	/**
	 *
	 * @param type $string
	 * @return type
	 */
	private function checkIsset($string){
		if(isset($string)){
			return $string;
		}else{
			return '';
		}
	}
	/**
	 *
	 * @param type $data
	 */
	private function importNP($data) {
		if (empty($this->polisTypeArray)) {
			$resp_p = $this->queryResult("
				select
					pt.PolisType_id as \"PolisType_id\",
					pt.PolisType_CodeF008 as \"PolisType_CodeF008\"
				from
					v_PolisType pt
			");
			if (is_array($resp_p)) {
				foreach($resp_p as $one_p) {
					$this->polisTypeArray[$one_p['PolisType_CodeF008']] = $one_p['PolisType_id'];
				}
			}
		}

		$AdressReg = array();
		$AdressProg = array();

		if(!empty($data['STREET_REG'])){
			$AdressReg = $this->getFirstRowFromQuery("
				select
					643 as \"KLCountry_id\",
					KLAdr_Index as \"KLAdr_Index\",
					KLRgn_id as \"KLRgn_id\",
					KLSubRgn_id as \"KLSubRgn_id\",
					KLCity_id as \"KLCity_id\",
					KLTown_id as \"KLTown_id\",
					PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
					KLStreet_id, as \"KLStreet_id\"
					Address_Address as \"Address_Address\"
				from dbo.f_parseKladrCode(
					:Kladr_Code,
					:Address_House,
					:Address_Corpus,
					:Address_Flat
				)
			", array(
				'Kladr_Code' => $data['STREET_REG'],
				'Address_House' => (!empty($data['HOUSE_REG']) ? $data['HOUSE_REG'] : NULL),
				'Address_Corpus' => (!empty($data['CORPUS_REG']) ? $data['CORPUS_REG'] : NULL),
				'Address_Flat' => (!empty($data['FLAT_REG']) ? $data['FLAT_REG'] : NULL)
			));

			if ( $AdressReg === false ) {
				$AdressReg = array();
			}
		}
		if(!empty($data['STREET_PROG'])){
			// Если адрес проживания совпадает с адресом регистрации...
			if (
				!empty($data['STREET_REG']) && $data['STREET_REG'] == $data['STREET_PROG']
				&& (!empty($data['HOUSE_REG']) ? $data['HOUSE_REG'] : '') == (!empty($data['HOUSE_PROG']) ? $data['HOUSE_PROG'] : '')
				&& (!empty($data['CORPUS_REG']) ? $data['CORPUS_REG'] : '') == (!empty($data['CORPUS_PROG']) ? $data['CORPUS_PROG'] : '')
				&& (!empty($data['FLAT_REG']) ? $data['FLAT_REG'] : '') == (!empty($data['FLAT_PROG']) ? $data['FLAT_PROG'] : '')
			) {
				// ... не делаем лишний запрос, тянем данные из адреса регистрации
				$AdressProg = $AdressReg;
			}
			else {
				$AdressProg = $this->getFirstRowFromQuery("
					select
						643 as \"KLCountry_id\",
						KLAdr_Index as \"KLAdr_Index\",
						KLRgn_id as \"KLRgn_id\",
						KLSubRgn_id as \"KLSubRgn_id\",
						KLCity_id as \"KLCity_id\",
						KLTown_id as \"KLTown_id\",
						PersonSprTerrDop_id as \"PersonSprTerrDop_id\",
						KLStreet_id as \"KLStreet_id\",
						Address_Address as \"Address_Address\"
					from dbo.f_parseKladrCode(
						:Kladr_Code,
						:Address_House,
						:Address_Corpus,
						:Address_Flat
					)
				", array(
					'Kladr_Code' => $data['STREET_PROG'],
					'Address_House' => (!empty($data['HOUSE_PROG']) ? $data['HOUSE_PROG'] : NULL),
					'Address_Corpus' => (!empty($data['CORPUS_PROG']) ? $data['CORPUS_PROG'] : NULL),
					'Address_Flat' => (!empty($data['FLAT_PROG']) ? $data['FLAT_PROG'] : NULL)
				));

				if ( $AdressProg === false ) {
					$AdressProg = array();
				}
			}
		}
		$query = 'select OrgSMO_Name as \"OrgSMO_Name\" from v_OrgSMO where Orgsmo_f002smocod =  :SMOCode limit 1';
		$params = array("SMOCode"=>$this->SMOCode);
		$res = $this->db->query($query, $params);
		$SMORes = $res->result('array');
		$query = 'select Org_Nick as \"Org_Nick\" from v_Lpu_all where Lpu_f003mcod = :MOCode limit 1';
		$params = array("MOCode"=>$this->MOCode);
		$res = $this->db->query($query, $params);
		$MORes = $res->result('array');
		if (in_array($data['CASE_VID'], array(1, 2))) {

			fputs($this->fileZL, "".$this->counter['confl'].")".
				"ФИО: ".$data['FAM']." ".$data['IM']." ".(isset($data['OT'])?$data['OT']:$data['OT'])."\r\n".
				"Дата рождения: ".$data['DR']."\r\n".
				"Пол: ".(($data['W']==2)?'Ж':'М')."\r\n".
				"Полис: ".(isset($data['VPOLIS'])?$this->polisType[$data['VPOLIS']]:'').", ".(isset($data['SPOLIS'])?$data['SPOLIS']:'').", ".(isset($data['NPOLIS'])?$data['NPOLIS']:'')."\r\n".
				"Дата открытия полиса: ".(isset($data['DATE_BEGIN'])?$data['DATE_BEGIN']:'')."\r\n".
				"Дата закрытия полиса: ".(isset($data['DATE_END'])?$data['DATE_END']:'')."\r\n".
				"СМО: ".$this->SMOCode." ".$SMORes[0]['OrgSMO_Name']."\r\n".
				"МО: ".$this->MOCode." ".$MORes[0]['Org_Nick']."\r\n".
				"Адрес регистрации:  ".((count($AdressReg)>0)?$AdressReg['Address_Address']:'')."\r\n".
				"Адрес проживания: ".((count($AdressProg)>0)?$AdressProg['Address_Address']:'')."\r\n".
				"Причина приёма заявления: ".$data['CASE_VID'].' - '.$this->caseVid[$data['CASE_VID']]."\r\n".
				"ФИО (старые): ".(isset($data['FAM_OLD'])?$data['FAM_OLD']:'')." ".(isset($data['IM_OLD'])?$data['IM_OLD']:'')." ".(isset($data['OT_OLD'])?$data['OT_OLD']:'')."\r\n".
				"ДР (старая): ".(isset($data['DR_OLD'])?$data['DR_OLD']:'')."\r\n".
				"\r\n\r\n"
			);
			$this->counter['bad']++;
			$this->counter['confl']++;
		} else {
			/*$query = "select top 1
						p.Person_Surname,
						p.Person_Firname,
						p.Person_Secname,
						CONVERT(varchar(10),p.Person_Birthday,120) as Person_Birthday,
						pol.Server_id,
						pol.Polis_Ser,
						pol.Polis_Num,
						pol.PolisType_id,
						pt.PolisType_CodeF008 as VPOLIS,
						p.Person_SNILS,
						p.PersonEvn_id,
						p.Person_id,
						pol.Polis_id,
						p.Person_EdNum,
						CONVERT(varchar(10),pol.Polis_begDate, 120) as Polis_begDate,
						CONVERT(varchar(10),pol.Polis_endDate, 120) as Polis_endDate
						from v_Person_all p with(nolock)
						left join Polis pol with (nolock) on pol.Polis_id=p.Polis_id
						left join dbo.PolisType pt (nolock) on pt.PolisType_id = pol.PolisType_id and isnull(pt.PolisType_CodeF008,0)<>0
						where REPLACE(p.Person_Surname,'ё','е') = REPLACE(:Person_Surname,'ё','е')
						and REPLACE(p.Person_Firname,'ё','е') = REPLACE(:Person_Firname,'ё','е')
						and isnull(REPLACE(p.Person_Secname,'ё','е'),'')=isnull(REPLACE(:Person_Secname,'ё','е'),'')
						and p.Person_Birthday = convert(date,:Person_Birthday)";
			*/
			$query = "select
						p.Person_Surname as \"Person_Surname\",
						p.Person_Firname as \"Person_Firname\",
						p.Person_Secname as \"Person_Secname\",
						to_char(p.Person_Birthday,'yyyy-mm-dd') as \"Person_Birthday\",
						rtrim(pol.Polis_Ser) as \"Polis_Ser\",
						rtrim(pol.Polis_Num) as \"Polis_Num\",
						coalesce(pol.Server_id,1) as \"Server_id\",
						pt.PolisType_CodeF008 as \"VPOLIS\",
						p.Person_id as \"Person_id\",
						to_char(p.Person_deadDT,'yyyy-mm-dd') as \"Person_DeadDT\"
						from v_PersonState p
						left join Polis pol on pol.Polis_id=p.Polis_id
						left join dbo.PolisType pt on pt.PolisType_id = pol.PolisType_id and coalesce(pt.PolisType_CodeF008,0)<>0
						where REPLACE(p.Person_Surname,'ё','е') = REPLACE(:Person_Surname,'ё','е') 
						and REPLACE(p.Person_Firname,'ё','е') = REPLACE(:Person_Firname,'ё','е')
						and coalesce(REPLACE(p.Person_Secname,'ё','е'),'')=coalesce(REPLACE(:Person_Secname,'ё','е'),'')
						and to_char(date,p.Person_Birthday) = to_char(date,:Person_Birthday)";
			$params = array(
				"Person_Surname" => $data['FAM'],
				"Person_Firname" => $data['IM'],
				"Person_Secname" => (isset($data['OT']) ? $data['OT'] : null),
				"Person_Birthday" => $data['DR'],
			);
			//echo getDebugSQL($query, $params);exit();
			$result = $this->db->query($query, $params);
			$res = $result->result('array');
			if (count($res) > 0) {
				if (count($res) == 1) {
					$data['ID_PAC'] = $res[0]['Person_id'];
					$data['Person_DeadDT'] = $res[0]['Person_DeadDT'];
					$identData = $res[0];
					$polisType_id=0;
					if (
						(isset($res[0]['VPOLIS']) && $res[0]['VPOLIS'] =! $data['VPOLIS'])
						|| (isset($data['NPOLIS']) && $res[0]['Polis_Num'] != $data['NPOLIS'])
						|| (isset($data['SPOLIS']) && $res[0]['Polis_Ser'] != $data['SPOLIS'])
					) {
						//Проверка предыдущего полиса
						$sql = "
							select
								PP.Person_id as \"Person_id\",
								PP.Server_id as \"Server_id\",
								PP.PersonPolis_id as \"PersonPolis_id\",
								PP.OmsSprTerr_id as \"OmsSprTerr_id\",
								PP.PolisType_id as \"PolisType_id\",
								PP.OrgSmo_id as \"OrgSmo_id\",
								PP.Polis_Ser as \"Polis_Ser\",
								PP.Polis_Num as \"Polis_Num\",
								PP.PolisFormType_id as \"PolisFormType_id\",
								PP.Polis_begDate as \"Polis_begDate\",
								PP.Polis_endDate as \"Polis_endDate\"
							from v_PersonPolis PP 
							where PP.Person_id = :Person_id and PP.Polis_begDate <= :Polis_begDate
							order by PP.Polis_begDate desc
							limit 1
						";
						$params = array(
							'Person_id' => $data['ID_PAC'],
							'Polis_begDate' => $data['DATE_BEGIN']
						);
						$prevPolis = $this->getFirstRowFromQuery($sql, $params);
						if (is_array($prevPolis) && empty($prevPolis['Polis_endDate'])) {
							$prevPolis['Polis_endDate'] = $data['DATE_BEGIN'];
							$prevPolis['pmUser_id'] = (isset($data['pmUser_id'])?$data['pmUser_id']:1);
							$sql = "
								select
								 	error_code as \"Error_Code\", 
            						error_message as \"Error_Msg\"
            					from p_PersonPolis_upd (
									PersonPolis_id := :PersonPolis_id,
									Server_id := :Server_id,
									Person_id := :Person_id,
									OmsSprTerr_id := :OmsSprTerr_id,
									PolisType_id := :PolisType_id,
									OrgSmo_id := :OrgSmo_id,
									Polis_Ser := :Polis_Ser,
									PolisFormType_id:=:PolisFormType_id,
									Polis_Num := :Polis_Num,
									Polis_begDate := :Polis_begDate,
									Polis_endDate := :Polis_endDate,
									pmUser_id := :pmUser_id
							);
							";
							if(empty($prevPolis['Polis_endDate']) || $prevPolis['Polis_endDate']>=$prevPolis['Polis_begDate']){
								$resp = $this->queryResult($sql, $prevPolis);
							}
						}

						$sql = "
						select
						    error_code as \"Error_Code\", 
            				error_message as \"Error_Msg\"
            			from p_PersonPolis_ins (
								Server_id := 1,
								Person_id := :Person_id,
								OmsSprTerr_id := 1392,
								PolisType_id := :PolisType_id,
								OrgSmo_id := (
										select smo.OrgSMO_id
										from v_OrgSMO smo
										left join v_Org o on o.Org_id = smo.Org_id
										where smo.Orgsmo_f002smocod = :SMO
										limit 1
									),
								Polis_Ser := :SPOLIS,
								Polis_Num := :NPOLIS,
								Polis_begDate := :DATE_BEGIN,
								Polis_endDate := :DATE_END,
								PersonPolis_begDT := :DATE_BEGIN,
								PersonPolis_insDT := dateadd('second',1,:DATE_BEGIN),
								pmUser_id := :pmUser_id
						);
						";
						$params = array(
							'Person_id' => $data['ID_PAC'],
							'VPOLIS'=>isset($data['VPOLIS'])?$data['VPOLIS']:null,
							'SMO'=>isset($data['SMO'])?$data['SMO']:null,
							'DATE_BEGIN'=>(isset($data['DATE_BEGIN'])?$data['DATE_BEGIN']:null),
							'DATE_END'=>(isset($data['DATE_END'])?$data['DATE_END']:null),
							'SPOLIS'=>(isset($data['SPOLIS'])?$data['SPOLIS']:null),
							'NPOLIS'=>(isset($data['NPOLIS'])?$data['NPOLIS']:null),
							'pmUser_id' => (isset($data['pmUser_id'])?$data['pmUser_id']:1)
						);
						//echo getDebugSQL($sql, $params);exit;
						if(empty($params['DATE_END']) || $params['DATE_END']>=$params['DATE_BEGIN']){
							if (!empty($this->polisTypeArray[$params['VPOLIS']])) {
								$params['PolisType_id'] = $this->polisTypeArray[$params['VPOLIS']];

								// если единый номер и номер полиса не 16 знаков, пропускаем
								if ($params['PolisType_id'] == 4 && mb_strlen($params['NPOLIS']) < 16) {
									return;
								}

								$res = $this->db->query($sql, $params);
								$res = $res->result('array');

								if ($params['PolisType_id'] == 4) {
									$params = array(
										'ID_PAC' => $data['ID_PAC'],
										'DATE_BEGIN' => (isset($data['DATE_BEGIN']) ? $data['DATE_BEGIN'] : null),
										'NPOLIS' => (isset($data['NPOLIS']) ? $data['NPOLIS'] : null),
										'pmUser_id' => (isset($data['pmUser_id']) ? $data['pmUser_id'] : 1)
									);
									$query = "
										select
											personpolisednum_id as \"PersonPolisEdNum_id\", 
            								error_code as \"Error_Code\", 
            								error_message as \"Error_Msg\"
	
										from p_PersonPolisEdNum_ins (
											PersonPolisEdNum_id := :PersonPolisEdNum_id,
											Server_id := 1,
											Person_id := :ID_PAC,
											PersonPolisEdNum_EdNum := :NPOLIS,
											PersonPolisEdNum_begDT := :DATE_BEGIN,
											PersonPolisEdNum_insDT := :DATE_BEGIN,
											pmUser_id := :pmUser_id
									);
									";
									//echo getDebugSQL($query, $data);
									$result = $this->db->query($query, $params);
								}
							}
						}
					}
					// Адрес регистрации
					if ( count($AdressReg) > 0 ) {
						$sel = $this->getFirstRowFromQuery("
							select
								p.PersonEvn_id as \"PersonEvn_id\",
								p.Server_id as \"Server_id\",
								COALESCE(Adr.KLRgn_id, 0) as \"KLRgn_id\",
								COALESCE(Adr.KLSubRgn_id, 0) as \"KLSubRgn_id\",
								COALESCE(Adr.KLCity_id, 0) as \"KLCity_id\",
								COALESCE(Adr.KLTown_id, 0) as \"KLTown_id\",
								COALESCE(Adr.KLStreet_id, 0) as \"KLStreet_id\",
								COALESCE(Adr.Address_House, '') as \"Address_House\",
								COALESCE(Adr.Address_Corpus, '') as \"Address_Corpus\",
								COALESCE(Adr.Address_Flat, '') as \"Address_Flat\"
							from
								v_Person_all p
								left join v_Address Adr on Adr.Address_id = p.UAddress_id
							where
								p.PersonEvnClass_id = 10
								and p.Person_id = :Person_id
							order by
								p.PersonEvn_insDT desc,
								p.PersonEvn_TimeStamp desc
							limit 1
						", array(
							'Person_id' => $data['ID_PAC'],
						));

						if (
							$sel === false || !is_array($sel) || count($sel) == 0
							|| $sel['KLRgn_id'] != (!empty($AdressReg['KLRgn_id']) ? $AdressReg['KLRgn_id'] : 0)
							|| $sel['KLSubRgn_id'] != (!empty($AdressReg['KLSubRgn_id']) ? $AdressReg['KLSubRgn_id'] : 0)
							|| $sel['KLCity_id'] != (!empty($AdressReg['KLCity_id']) ? $AdressReg['KLCity_id'] : 0)
							|| $sel['KLTown_id'] != (!empty($AdressReg['KLTown_id']) ? $AdressReg['KLTown_id'] : 0)
							|| $sel['KLStreet_id'] != (!empty($AdressReg['KLStreet_id']) ? $AdressReg['KLStreet_id'] : 0)
							|| $sel['Address_House'] != (!empty($data['HOUSE_REG']) ? $data['HOUSE_REG'] : '')
							|| $sel['Address_Corpus'] != (!empty($data['CORPUS_REG']) ? $data['CORPUS_REG'] : '')
							|| $sel['Address_Flat'] != (!empty($data['FLAT_REG']) ? $data['FLAT_REG'] : '')
						) {
							$res = $this->queryResult("
								select
								 	error_code as \"Error_Code\", 
            						error_message as \"Error_Msg\"
            					from p_PersonUAddress_ins (
									Server_id := 1,
									Person_id := :pid,
									KLCountry_id := :KLCountry_id,
									KLAreaType_id := null,
									KLRgn_id := :KLRgn_id,
									KLSubRgn_id := :KLSubRgn_id,
									KLCity_id := :KLCity_id,
									KLTown_id := :KLTown_id,
									KLStreet_id := :KLStreet_id,
									Address_Zip := :Address_Zip,
									Address_House := :Address_House,
									Address_Corpus := :Address_Corpus,
									Address_Flat := :Address_Flat,
									PersonSprTerrDop_id := :PersonSprTerrDop_id,
									Address_Address := null,
									--@Address_begDate := dbo.tzGetDate(),
									pmUser_id := :pmUser_id
							);
							", array(
								'pid'=>$data['ID_PAC'],
								'KLCountry_id'=>(!empty($AdressReg['KLCountry_id'])?$AdressReg['KLCountry_id']:null),
								'KLRgn_id'=>(!empty($AdressReg['KLRgn_id'])?$AdressReg['KLRgn_id']:null),
								'KLSubRgn_id'=>(!empty($AdressReg['KLSubRgn_id'])?$AdressReg['KLSubRgn_id']:null),
								'KLCity_id'=>(!empty($AdressReg['KLCity_id'])?$AdressReg['KLCity_id']:null),
								'KLTown_id'=>(!empty($AdressReg['KLTown_id'])?$AdressReg['KLTown_id']:null),
								'KLStreet_id'=>(!empty($AdressReg['KLStreet_id'])?$AdressReg['KLStreet_id']:null),
								'Address_Zip'=>(!empty($AdressReg['Address_Zip'])?$AdressReg['Address_Zip']:null),
								'Address_House'=>(!empty($data['HOUSE_REG']) ? $data['HOUSE_REG'] : NULL),
								'Address_Corpus'=>(!empty($data['CORPUS_REG']) ? $data['CORPUS_REG'] : NULL),
								'Address_Flat'=>(!empty($data['FLAT_REG']) ? $data['FLAT_REG'] : NULL),
								'PersonSprTerrDop_id'=>(!empty($AdressReg['PersonSprTerrDop_id'])?$AdressReg['PersonSprTerrDop_id']:null),
								'pmUser_id'=>(isset($data['pmUser_id'])?$data['pmUser_id']:1)
							));
						}
					}
					// Адрес проживания
					if ( count($AdressProg) > 0 ) {
						$sel = $this->getFirstRowFromQuery("
							select
								p.PersonEvn_id as \"PersonEvn_id\",
								p.Server_id as \"Server_id\",
								COALESCE(Adr.KLRgn_id, 0) as \"KLRgn_id\",
								COALESCE(Adr.KLSubRgn_id, 0) as \"KLSubRgn_id\",
								COALESCE(Adr.KLCity_id, 0) as \"KLCity_id\",
								COALESCE(Adr.KLTown_id, 0) as \"KLTown_id\",
								COALESCE(Adr.KLStreet_id, 0) as \"KLStreet_id\",
								COALESCE(Adr.Address_House, '') as \"Address_House\",
								COALESCE(Adr.Address_Corpus, '') as \"Address_Corpus\",
								COALESCE(Adr.Address_Flat, '') as \"Address_Flat\"
							from
								v_Person_all p 
								left join v_Address Adr on Adr.Address_id = p.PAddress_id
							where
								p.PersonEvnClass_id = 11
								and p.Person_id = :Person_id
							order by
								p.PersonEvn_insDT desc,
								p.PersonEvn_TimeStamp desc
							limit 1
						", array(
							'Person_id' => $data['ID_PAC'],
						));

						if (
							$sel === false || !is_array($sel) || count($sel) == 0
							|| $sel['KLRgn_id'] != (!empty($AdressProg['KLRgn_id']) ? $AdressProg['KLRgn_id'] : 0)
							|| $sel['KLSubRgn_id'] != (!empty($AdressProg['KLSubRgn_id']) ? $AdressProg['KLSubRgn_id'] : 0)
							|| $sel['KLCity_id'] != (!empty($AdressProg['KLCity_id']) ? $AdressProg['KLCity_id'] : 0)
							|| $sel['KLTown_id'] != (!empty($AdressProg['KLTown_id']) ? $AdressProg['KLTown_id'] : 0)
							|| $sel['KLStreet_id'] != (!empty($AdressProg['KLStreet_id']) ? $AdressProg['KLStreet_id'] : 0)
							|| $sel['Address_House'] != (!empty($data['HOUSE_PROG']) ? $data['HOUSE_PROG'] : '')
							|| $sel['Address_Corpus'] != (!empty($data['CORPUS_PROG']) ? $data['CORPUS_PROG'] : '')
							|| $sel['Address_Flat'] != (!empty($data['FLAT_PROG']) ? $data['FLAT_PROG'] : '')
						) {
							$res = $this->queryResult("
								
								SELECT 
									error_code as \"Error_Code\", 
            						error_message as \"Error_Msg\"

								from p_PersonPAddress_ins (
									Server_id := 1,
									Person_id := :pid,
									KLCountry_id := :KLCountry_id,
									KLAreaType_id := null,
									KLRgn_id := :KLRgn_id,
									KLSubRgn_id := :KLSubRgn_id,
									KLCity_id := :KLCity_id,
									KLTown_id := :KLTown_id,
									KLStreet_id := :KLStreet_id,
									Address_Zip := :Address_Zip,
									Address_House := :Address_House,
									Address_Corpus := :Address_Corpus,
									Address_Flat := :Address_Flat,
									PersonSprTerrDop_id := :PersonSprTerrDop_id,
									Address_Address := null,
									pmUser_id := :pmUser_id
							);
							", array(
								'pid'=>$data['ID_PAC'],
								'KLCountry_id'=>(!empty($AdressProg['KLCountry_id'])?$AdressProg['KLCountry_id']:null),
								'KLRgn_id'=>(!empty($AdressProg['KLRgn_id'])?$AdressProg['KLRgn_id']:null),
								'KLSubRgn_id'=>(!empty($AdressProg['KLSubRgn_id'])?$AdressProg['KLSubRgn_id']:null),
								'KLCity_id'=>(!empty($AdressProg['KLCity_id'])?$AdressProg['KLCity_id']:null),
								'KLTown_id'=>(!empty($AdressProg['KLTown_id'])?$AdressProg['KLTown_id']:null),
								'KLStreet_id'=>(!empty($AdressProg['KLStreet_id'])?$AdressProg['KLStreet_id']:null),
								'Address_Zip'=>(!empty($AdressProg['Address_Zip'])?$AdressProg['Address_Zip']:null),
								'Address_House'=>(!empty($data['HOUSE_PROG']) ? $data['HOUSE_PROG'] : NULL),
								'Address_Corpus'=>(!empty($data['CORPUS_PROG']) ? $data['CORPUS_PROG'] : NULL),
								'Address_Flat'=>(!empty($data['FLAT_PROG']) ? $data['FLAT_PROG'] : NULL),
								'PersonSprTerrDop_id'=>(!empty($AdressProg['PersonSprTerrDop_id'])?$AdressProg['PersonSprTerrDop_id']:null),
								'pmUser_id'=>(isset($data['pmUser_id'])?$data['pmUser_id']:1)
							));
						}
					}
					if($data['Person_DeadDT']==null){
						$this->LpuAttach($data);
					}
					$resp = $this->identifyAndAddNewPolisToPerson($identData);
					if (!empty($resp) && $resp === 4) {
						fputs($this->fileLog, $data['FAM'] . ' ' . $data['IM'] . ' ' . (isset($data['OT']) ? $data['OT'] : '') . '-' . $data['DR'] . " – пациенту не удалось определить тип полиса  \r\n");
					} else {
						fputs($this->fileLog, $data['FAM'] . ' ' . $data['IM'] . ' ' . (isset($data['OT']) ? $data['OT'] : '') . '-' . $data['DR'] . " – данные успешно обновлены  \r\n"); //log - «ФИО + ДР – данные успешно обновлены»
					}
					$this->counter['add']++;
				} else {
					//echo $data['FAM'];
					$pers = null;
					$fl = false;
					foreach ($res as $item) {
						if ((isset($item['VPOLIS'])&&$item['VPOLIS'] == $data['VPOLIS']) && ((isset($data['NPOLIS'])&&$item['Polis_Num'] == $data['NPOLIS']) || (isset($data['SPOLIS'])&&$item['Polis_Ser'] == $data['SPOLIS']))) {
							if ($pers != null) {
								$fl = false;
								//log
							} else {
								$fl = true;
								$pers = $item;
							}
						}
					}
					if (!$fl) {

						fputs($this->fileLog,$data['FAM'] . ' ' . $data['IM'] . ' ' . (isset($data['OT'])?$data['OT']:$data['OT']) . '-' . $data['DR'] . " – не удалось однозначно определить человека \r\n");
						fputs($this->fileZL,
							$this->counter['confl'].")\r\n".
							"ФИО: ".$data['FAM']." ".$data['IM']." ".(isset($data['OT'])?$data['OT']:$data['OT'])."\r\n".
							"Дата рождения: ".$data['DR']."\r\n".
							"Пол: ".(($data['W']==2)?'Ж':'М')."\r\n".
							"Полис: ".(isset($data['VPOLIS'])?$this->polisType[$data['VPOLIS']]:'').", ".(isset($data['SPOLIS'])?$data['SPOLIS']:'').", ".(isset($data['NPOLIS'])?$data['NPOLIS']:'')."\r\n".
							"Дата открытия полиса: ".(isset($data['DATE_BEGIN'])?$data['DATE_BEGIN']:'')."\r\n".
							"Дата закрытия полиса: ".(isset($data['DATE_END'])?$data['DATE_END']:'')."\r\n".
							"СМО: ".$this->SMOCode." ".$SMORes[0]['OrgSMO_Name']."\r\n".
							"МО: ".$this->MOCode." ".$MORes[0]['Org_Nick']."\r\n".
							"Адрес регистрации:  ".((count($AdressReg)>0)?$AdressReg['Address_Address']:'')."\r\n".
							"Адрес проживания: ".((count($AdressProg)>0)?$AdressProg['Address_Address']:'')."\r\n".
							"Причина приёма заявления: ".$data['CASE_VID'].' - '.$this->caseVid[$data['CASE_VID']]."\r\n".
							"ФИО (старые): ".(isset($data['FAM_OLD'])?$data['FAM_OLD']:'')." ".(isset($data['IM_OLD'])?$data['IM_OLD']:'')." ".(isset($data['OT_OLD'])?$data['OT_OLD']:'')."\r\n".
							"ДР (старая): ".(isset($data['DR_OLD'])?$data['DR_OLD']:'')."\r\n".
							"\r\n\r\n"
						);
						$this->counter['confl']++;
						$this->counter['bad']++;
					} else {
						$data['ID_PAC'] = $pers['Person_id'];
						$this->LpuAttach($data);
						fputs($this->fileLog,$data['FAM'] . ' ' . $data['IM'] . ' ' . (isset($data['OT'])?$data['OT']:'') . '-' . $data['DR'] . " – данные успешно обновлены  \r\n");
						$this->counter['add']++;
					}
				}
			} else {
				$query = "
					select
							person_id as \"Person_id\", 
							error_code as \"Error_Code\", 
							error_message as \"Error_Msg\"
					from p_PersonAll_ins (
						PersonSurname_Surname := :FAM,
						PersonFirname_Firname := :IM,
						PersonSecname_Secname := :OT,
						PersonBirthDay_BirthDay := :DR,
						Sex_id := :W,
						Server_id := 1,
						Person_id := :Person_id,
						pmUser_id := :pmUser_id
					);
				;";
				$params = array(
					'FAM'=>$data['FAM'],
					'IM'=>$data['IM'],
					'OT'=>(isset($data['OT'])?$data['OT']:''),
					'DR'=>$data['DR'],
					'W'=>$data['W'],
					'pmUser_id'=>(isset($data['pmUser_id'])?$data['pmUser_id']:1)
				);
				$result = $this->db->query($query, $params);
				$res = $result->result('array');
				$data['ID_PAC'] = $res[0]['id'];

				$sql = "
				select
				    error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"
				
				from p_PersonPolis_ins (
					Server_id := 1,
					Person_id := :ID_PAC,
					PersonPolis_insDT := :DATE_BEGIN,
					OmsSprTerr_id := 1392,
					PolisType_id := :PolisType_id,
					OrgSmo_id := (select  
									smo.OrgSMO_id 
								from
									v_OrgSMO smo
									left join v_Org o on o.Org_id = smo.Org_id
								where
									smo.Orgsmo_f002smocod = :SMO limit 1),
					Polis_Ser := :SPOLIS,
					Polis_Num := :NPOLIS,
					Polis_begDate := :DATE_BEGIN,
					Polis_endDate := :DATE_END,
					pmUser_id := :pmUser_id
				);
				";
				$params = array(
					'VPOLIS'=>(isset($data['VPOLIS'])?$data['VPOLIS']:null),
					'SMO'=>(isset($data['SMO'])?$data['SMO']:null),
					'ID_PAC'=>(isset($data['ID_PAC'])?$data['ID_PAC']:null),
					'DATE_BEGIN'=>(isset($data['DATE_BEGIN'])?$data['DATE_BEGIN']:null),
					'DATE_END'=>(isset($data['DATE_END'])?$data['DATE_END']:null),
					'SPOLIS'=>(isset($data['SPOLIS'])?$data['SPOLIS']:null),
					'NPOLIS'=>(isset($data['NPOLIS'])?$data['NPOLIS']:null),
					'pmUser_id'=>(isset($data['pmUser_id'])?$data['pmUser_id']:1)
				);
				//echo getDebugSQL($sql,$params);exit();
				if(empty($params['DATE_END']) || $params['DATE_END']>=$params['DATE_BEGIN']){
					if (!empty($this->polisTypeArray[$params['VPOLIS']])) {
						$params['PolisType_id'] = $this->polisTypeArray[$params['VPOLIS']];

						// если единый номер и номер полиса не 16 знаков, пропускаем
						if ($params['PolisType_id'] == 4 && mb_strlen($params['NPOLIS']) < 16) {
							return;
						}

						$res = $this->db->query($sql, $params);
						$res = $res->result('array');
						if ($params['PolisType_id'] == 4) {
							$query = "
								select
									PersonPolisEdNum_id as \"PersonPolisEdNum_id\"
								from
									v_PersonPolisEdNum
								where
									Person_id = :ID_PAC
									and PersonPolisEdNum_EdNum = :NPOLIS
									and PersonPolisEdNum_begDT = :DATE_BEGIN
								limit 1
							";
							$result = $this->db->query($query, $params);
							if (is_object($result)) {
								$resp = $result->result('array');
								if (is_object($result)) {
									if (empty($resp[0]['PersonPolisEdNum_id'])) {
										$query = "
											select
												personpolisednum_id as \"PersonPolisEdNum_id\", 
												error_code as \"Error_Code\", 
												error_message as \"Error_Msg\"
	
											from p_PersonPolisEdNum_ins (
												Server_id := 1,
												Person_id := :ID_PAC,
												PersonPolisEdNum_EdNum := :NPOLIS,
												PersonPolisEdNum_begDT := :DATE_BEGIN,
												PersonPolisEdNum_insDT := :DATE_BEGIN,
												pmUser_id := :pmUser_id
										);
										";
										//echo getDebugSQL($query, $data);
										$result = $this->db->query($query, $params);
									}
								}
							}
						}
					}
				}

				// Адрес регистрации
				if ( count($AdressReg) > 0 ) {
					$sel = $this->getFirstRowFromQuery("
						select
							p.PersonEvn_id as \"PersonEvn_id\",
							p.Server_id as \"Server_id\",
							COALESCE(Adr.KLRgn_id, 0) as \"KLRgn_id\",
							COALESCE(Adr.KLSubRgn_id, 0) as \"KLSubRgn_id\",
							COALESCE(Adr.KLCity_id, 0) as \"KLCity_id\",
							COALESCE(Adr.KLTown_id, 0) as \"KLTown_id\",
							COALESCE(Adr.KLStreet_id, 0) as \"KLStreet_id\",
							COALESCE(Adr.Address_House, '') as \"Address_House\",
							COALESCE(Adr.Address_Corpus, '') as \"Address_Corpus\",
							COALESCE(Adr.Address_Flat, '') as \"Address_Flat\"
						from
							v_Person_all p
							left join v_Address Adr on Adr.Address_id = p.UAddress_id
						where
							p.PersonEvnClass_id = 10
							and p.Person_id = :Person_id
						order by
							p.PersonEvn_insDT desc,
							p.PersonEvn_TimeStamp desc
						limit 1
					", array(
						'Person_id' => $data['ID_PAC'],
					));

					if (
						$sel === false || !is_array($sel) || count($sel) == 0
						|| $sel['KLRgn_id'] != (!empty($AdressReg['KLRgn_id']) ? $AdressReg['KLRgn_id'] : 0)
						|| $sel['KLSubRgn_id'] != (!empty($AdressReg['KLSubRgn_id']) ? $AdressReg['KLSubRgn_id'] : 0)
						|| $sel['KLCity_id'] != (!empty($AdressReg['KLCity_id']) ? $AdressReg['KLCity_id'] : 0)
						|| $sel['KLTown_id'] != (!empty($AdressReg['KLTown_id']) ? $AdressReg['KLTown_id'] : 0)
						|| $sel['KLStreet_id'] != (!empty($AdressReg['KLStreet_id']) ? $AdressReg['KLStreet_id'] : 0)
						|| $sel['Address_House'] != (!empty($data['HOUSE_REG']) ? $data['HOUSE_REG'] : '')
						|| $sel['Address_Corpus'] != (!empty($data['CORPUS_REG']) ? $data['CORPUS_REG'] : '')
						|| $sel['Address_Flat'] != (!empty($data['FLAT_REG']) ? $data['FLAT_REG'] : '')
					) {
						$res = $this->queryResult("
							select
								error_code as \"Error_Code\", 
								error_message as \"Error_Msg\"

							from p_PersonUAddress_ins (
								Server_id := 1,
								Person_id := :pid,
								KLCountry_id := :KLCountry_id,
								KLAreaType_id := null,
								KLRgn_id := :KLRgn_id,
								KLSubRgn_id := :KLSubRgn_id,
								KLCity_id := :KLCity_id,
								KLTown_id := :KLTown_id,
								KLStreet_id := :KLStreet_id,
								Address_Zip := :Address_Zip,
								Address_House := :Address_House,
								Address_Corpus := :Address_Corpus,
								Address_Flat := :Address_Flat,
								PersonSprTerrDop_id := :PersonSprTerrDop_id,
								Address_Address := null,
								pmUser_id := :pmUser_id
							);
						", array(
							'pid'=>$data['ID_PAC'],
							'KLCountry_id'=>(!empty($AdressReg['KLCountry_id'])?$AdressReg['KLCountry_id']:null),
							'KLRgn_id'=>(!empty($AdressReg['KLRgn_id'])?$AdressReg['KLRgn_id']:null),
							'KLSubRgn_id'=>(!empty($AdressReg['KLSubRgn_id'])?$AdressReg['KLSubRgn_id']:null),
							'KLCity_id'=>(!empty($AdressReg['KLCity_id'])?$AdressReg['KLCity_id']:null),
							'KLTown_id'=>(!empty($AdressReg['KLTown_id'])?$AdressReg['KLTown_id']:null),
							'KLStreet_id'=>(!empty($AdressReg['KLStreet_id'])?$AdressReg['KLStreet_id']:null),
							'Address_Zip'=>(!empty($AdressReg['Address_Zip'])?$AdressReg['Address_Zip']:null),
							'Address_House'=>(!empty($data['HOUSE_REG']) ? $data['HOUSE_REG'] : NULL),
							'Address_Corpus'=>(!empty($data['CORPUS_REG']) ? $data['CORPUS_REG'] : NULL),
							'Address_Flat'=>(!empty($data['FLAT_REG']) ? $data['FLAT_REG'] : NULL),
							'PersonSprTerrDop_id'=>(!empty($AdressReg['PersonSprTerrDop_id'])?$AdressReg['PersonSprTerrDop_id']:null),
							'pmUser_id'=>(isset($data['pmUser_id'])?$data['pmUser_id']:1)
						));
					}
				}
				// Адрес проживания
				if ( count($AdressProg) > 0 ) {
					$sel = $this->getFirstRowFromQuery("
						select
							p.PersonEvn_id as \"PersonEvn_id\",
							p.Server_id as \"Server_id\",
							COALESCE(Adr.KLRgn_id, 0) as \"KLRgn_id\",
							COALESCE(Adr.KLSubRgn_id, 0) as \"KLSubRgn_id\",
							COALESCE(Adr.KLCity_id, 0) as \"KLCity_id\",
							COALESCE(Adr.KLTown_id, 0) as \"KLTown_id\",
							COALESCE(Adr.KLStreet_id, 0) as \"KLStreet_id\",
							COALESCE(Adr.Address_House, '') as \"Address_House\",
							COALESCE(Adr.Address_Corpus, '') as \"Address_Corpus\",
							COALESCE(Adr.Address_Flat, '') as \"Address_Flat\"
						from
							v_Person_all p
							left join v_Address Adr on Adr.Address_id = p.PAddress_id
						where
							p.PersonEvnClass_id = 11
							and p.Person_id = :Person_id
						order by
							p.PersonEvn_insDT desc,
							p.PersonEvn_TimeStamp desc
						limit 1
					", array(
						'Person_id' => $data['ID_PAC'],
					));

					if (
						$sel === false || !is_array($sel) || count($sel) == 0
						|| $sel['KLRgn_id'] != (!empty($AdressProg['KLRgn_id']) ? $AdressProg['KLRgn_id'] : 0)
						|| $sel['KLSubRgn_id'] != (!empty($AdressProg['KLSubRgn_id']) ? $AdressProg['KLSubRgn_id'] : 0)
						|| $sel['KLCity_id'] != (!empty($AdressProg['KLCity_id']) ? $AdressProg['KLCity_id'] : 0)
						|| $sel['KLTown_id'] != (!empty($AdressProg['KLTown_id']) ? $AdressProg['KLTown_id'] : 0)
						|| $sel['KLStreet_id'] != (!empty($AdressProg['KLStreet_id']) ? $AdressProg['KLStreet_id'] : 0)
						|| $sel['Address_House'] != (!empty($data['HOUSE_PROG']) ? $data['HOUSE_PROG'] : '')
						|| $sel['Address_Corpus'] != (!empty($data['CORPUS_PROG']) ? $data['CORPUS_PROG'] : '')
						|| $sel['Address_Flat'] != (!empty($data['FLAT_PROG']) ? $data['FLAT_PROG'] : '')
					) {
						$res = $this->queryResult("
							select
								error_code as \"Error_Code\", 
            					error_message as \"Error_Msg\"

							from p_PersonPAddress_ins (
								Server_id := 1,
								Person_id := :pid,
								KLCountry_id := :KLCountry_id,
								KLAreaType_id := null,
								KLRgn_id := :KLRgn_id,
								KLSubRgn_id := :KLSubRgn_id,
								KLCity_id := :KLCity_id,
								KLTown_id := :KLTown_id,
								KLStreet_id := :KLStreet_id,
								Address_Zip := :Address_Zip,
								Address_House := :Address_House,
								Address_Corpus := :Address_Corpus,
								Address_Flat := :Address_Flat,
								PersonSprTerrDop_id := :PersonSprTerrDop_id,
								Address_Address := null,
								pmUser_id := :pmUser_id
							);
						", array(
							'pid'=>$data['ID_PAC'],
							'KLCountry_id'=>(!empty($AdressProg['KLCountry_id'])?$AdressProg['KLCountry_id']:null),
							'KLRgn_id'=>(!empty($AdressProg['KLRgn_id'])?$AdressProg['KLRgn_id']:null),
							'KLSubRgn_id'=>(!empty($AdressProg['KLSubRgn_id'])?$AdressProg['KLSubRgn_id']:null),
							'KLCity_id'=>(!empty($AdressProg['KLCity_id'])?$AdressProg['KLCity_id']:null),
							'KLTown_id'=>(!empty($AdressProg['KLTown_id'])?$AdressProg['KLTown_id']:null),
							'KLStreet_id'=>(!empty($AdressProg['KLStreet_id'])?$AdressProg['KLStreet_id']:null),
							'Address_Zip'=>(!empty($AdressProg['Address_Zip'])?$AdressProg['Address_Zip']:null),
							'Address_House'=>(!empty($data['HOUSE_PROG']) ? $data['HOUSE_PROG'] : NULL),
							'Address_Corpus'=>(!empty($data['CORPUS_PROG']) ? $data['CORPUS_PROG'] : NULL),
							'Address_Flat'=>(!empty($data['FLAT_PROG']) ? $data['FLAT_PROG'] : NULL),
							'PersonSprTerrDop_id'=>(!empty($AdressProg['PersonSprTerrDop_id'])?$AdressProg['PersonSprTerrDop_id']:null),
							'pmUser_id'=>(isset($data['pmUser_id'])?$data['pmUser_id']:1)
						));
					}
				}

				$this->LpuAttach($data);
				$identData = array(
					'Person_Surname'=>$data['FAM'],
					'Person_Firname'=>$data['IM'],
					'Person_Secname'=>(isset($data['OT'])?$data['OT']:null),
					'Person_Birthday'=>$data['DR'],
					'Person_id'=>$data['ID_PAC'],
					'Server_id'=>1
				);
				$resp = $this->identifyAndAddNewPolisToPerson($identData);
				if (!empty($resp) && $resp === 4) {
					fputs($this->fileLog, $data['FAM'] . ' ' . $data['IM'] . ' ' . (isset($data['OT']) ? $data['OT'] : '') . '-' . $data['DR'] . " – пациенту не удалось определить тип полиса  \r\n");
				} else {
					fputs($this->fileLog, $data['FAM'] . ' ' . $data['IM'] . ' ' . (isset($data['OT']) ? $data['OT'] : '') . '-' . $data['DR'] . " – данные успешно обновлены  \r\n");
				}
				$this->counter['add']++;
			}
		}
	}

	/**
	 *
	 * @param type $data
	 */
	private function LpuAttach($data) {
		$Lpu_id = $this->getFirstResultFromQuery("
			select Lpu_id as \"Lpu_id\" from v_Lpu  where Lpu_f003mcod = :CODE_MO limit 1
		", array(
			'CODE_MO' => $data['CODE_MO']
		));
		if (!$Lpu_id) {
			return false;
		}
		$pmUserId = $_SESSION['pmuser_id'];
		$queryParams = array(
			'ID_PAC' => $data['ID_PAC'],
			'Lpu_id' => $Lpu_id,
			'PersonCard_begDate' => date('Y-m-d'),
			'Person_Birthday'=>$data['DR'],
			'pmUser_id'=>$pmUserId
		);

		$sql = "
			SELECT
				count(*) as \"cnt\"
			FROM
				v_PersonCard
			WHERE
				Person_id = :ID_PAC
				and coalesce(PersonCard_endDate,dbo.tzGetDate())>=dbo.tzGetDate() and LpuAttachType_id = 1
		";
		//Получим участок по адресу человека
		$lpuRegion_id = null;
		$query_lpuregion = "
			select LR.LpuRegion_id as \"LpuRegion_id\"
			from dbo.LpuRegionStreet LRS
			left join lateral (
				select 
					a.KLStreet_id,
					a.Address_House,
					a.Address_Corpus
				from dbo.Address a
				where a.Address_id in((
							select PAddress_id
							from v_PersonState
							where Person_id = :ID_PAC
						),
						(select UAddress_id
						from v_PersonState
						where Person_id = :ID_PAC
						))
				limit 1
			) Addr on true
			inner join v_LpuRegion LR on (LR.LpuRegion_id = LRS.LpuRegion_id and LR.Lpu_id = :Lpu_id)
			where dbo.GetHouse(coalesce(LRS.LpuRegionStreet_HouseSet,'  '),(LTRIM(RTRIM(Addr.Address_House))||(CASE WHEN Addr.Address_Corpus IS NOT NULL THEN '/'||RTRIM(LTRIM(Addr.Address_Corpus)) ELSE '' end))) = 1
			and LRS.KLStreet_id = Addr.KLStreet_id
			and LR.LpuRegionType_id = (
							case when datediff('year', :Person_Birthday,dbo.tzgetDate()) < 18 then 2
							else 1 end
						)
			limit 1
		";
		$result_lpuregion = $this->db->query($query_lpuregion,$queryParams);
		if(is_object($result_lpuregion)){
			$result_lpuregion = $result_lpuregion->result('array');
			if(count($result_lpuregion) > 0 && !empty($result_lpuregion[0]['LpuRegion_id']))
			{
				$lpuRegion_id = $result_lpuregion[0]['LpuRegion_id'];
			}
		}
		$queryParams['LpuRegion_id'] = $lpuRegion_id;
		//echo getDebugSQL($sql,$queryParams);exit();
		$res = $this->db->query($sql, $queryParams);
		$result = $res->result('array');
		if ($result[0]['cnt'] == 0) {
			$this->load->model('PersonAmbulatCard_model', 'PersonAmbulatCard_model');

			/*$PersonAmbulatCard_Num = $this->getFirstResultFromQuery("
				declare @PersonAmbulatCard_Num bigint = isnull((
					select top 1 max(cast(PersonAmbulatCard_Num as bigint))+1
					from v_PersonAmbulatCard with(nolock)
					where ISNUMERIC(PersonAmbulatCard_Num) = 1
					and Lpu_id = :Lpu_id
				), 1)
				select @PersonAmbulatCard_Num as PersonAmbulatCard_Num
			", array('Lpu_id' => $Lpu_id));*/
			// #144884 не правильно, т.к. если кто-то вручную поставит код амбулаторной карты, например, 7943535345948, то следующим выдаст 7943535345949,
			// тогда как при ручном создании в АРМ регистратора поликлиники обычно 3-4-значные

			$counter = 0;// на всякий случай защита
			$startValue = 0;
			$PersonAmbulatCard_Num = 1;
			do{
				$sql = "
				select objid as \"ObjID\"
				from xp_GenpmID (
					ObjectName := 'PersonCard',
					MinValue := :startValue,
					Lpu_id := :Lpu_id,
					ObjectID := :ObjID
				);
				";
				$result = $this->db->query($sql, array($startValue, $Lpu_id));// генерация нового кода
				$personcard_result = $result->result('array');
				$PersonAmbulatCard_Num = $personcard_result[0]['PersonCard_Code'];

				//log_message('error', __LINE__.' LpuAttach() $PersonAmbulatCard_Num:'.$PersonAmbulatCard_Num);
				//echo __LINE__, ' LpuAttach() xp_GenpmID новый номер $PersonAmbulatCard_Num:', $PersonAmbulatCard_Num, "\n";

				$sqlCheck = "
				select PersonAmbulatCard_Num as \"PersonAmbulatCard_Num\"
				from v_PersonAmbulatCard
				where 
					PersonAmbulatCard_Num ~ '^([0-9]+[.]?[0-9]*|[.][0-9]+)$'
					and Lpu_id = :Lpu_id
					and PersonAmbulatCard_Num = :PACN
				";
				$resultCheck = $this->db->query($sqlCheck, array('Lpu_id' => $Lpu_id, 'PACN' => $PersonAmbulatCard_Num));// проверка на существование кода
				$resultCheckArr = $resultCheck->result('array');

				//log_message('error', __LINE__.' LpuAttach() $resultCheckArr:'.json_encode($resultCheckArr, JSON_UNESCAPED_UNICODE).', count(result):'.count($resultCheckArr));
				//echo __LINE__, ' LpuAttach() $resultCheckArr:', json_encode($resultCheckArr, JSON_UNESCAPED_UNICODE), ', count(result):', count($resultCheckArr), "\n";

				if((++$counter) > 9){
					$counter = 0;
					$startValue = $PersonAmbulatCard_Num + 100;
					//log_message('error', __LINE__.' LpuAttach() $startValue:'.$startValue);
					//echo __LINE__, ' LpuAttach() $startValue:', $startValue, "\n";
				}

			}while(count($resultCheckArr));

			//log_message('error', __LINE__.' LpuAttach() сгенерирован номер:'.$PersonAmbulatCard_Num.', Lpu_id:'.$Lpu_id.', Person_id:'.$data['ID_PAC'].', pmUser_id:'.$pmUserId;
			//echo __LINE__, ' LpuAttach() сгенерирован номер:', $PersonAmbulatCard_Num, ', Lpu_id:', $Lpu_id, ', Person_id:', $data['ID_PAC'], ', pmUser_id:', $pmUserId, "\n";

			$PersonAmbulatCard = $this->PersonAmbulatCard_model->checkPersonAmbulatCard(array(
				'Person_id' => $data['ID_PAC'],
				'Lpu_id' => $Lpu_id,
				'pmUser_id'=> $pmUserId,
				'Server_id'=> 1,
				'PersonAmbulatCard_Num' => $PersonAmbulatCard_Num,
				'getCount' => false
			));
			//log_message('error', __LINE__.' LpuAttach() $PersonAmbulatCard:'.json_encode($PersonAmbulatCard, JSON_UNESCAPED_UNICODE));
			//echo __LINE__, ' LpuAttach() checkPersonAmbulatCard вернул $PersonAmbulatCard:', json_encode($PersonAmbulatCard, JSON_UNESCAPED_UNICODE), "\n";

			if (is_array($PersonAmbulatCard) && !empty($PersonAmbulatCard[0]['PersonAmbulatCard_id'])) {
				$PersonAmbulatCard_id = $PersonAmbulatCard[0]['PersonAmbulatCard_id'];
				$PersonCard_Code = $PersonAmbulatCard[0]['PersonCard_Code'];
			} else {
				return false;
			}
			$queryParams['PersonCard_Code'] = $PersonCard_Code;

			$query = "
			select 
					personcard_id as \"PersonCard_id\", 
					error_code as \"Error_Code\", 
					error_message as \"Error_Msg\"
            from p_PersonCard_ins (
					PersonCard_id := :PersonCard_id,
					Server_id := 1,
					Person_id := :ID_PAC,
					Lpu_id := :Lpu_id,
					LpuAttachType_id := 1,
					LpuRegion_id := :LpuRegion_id,
					PersonCard_Code := :PersonCard_Code,
					PersonCard_begDate := :PersonCard_begDate,
					PersonCard_IsAttachCondit := 2,
					pmUser_id := :pmUser_id
			);
			";// @Res as PersonCard_id вместо @Res as PersonCard_Code #144884 - получались многозначные номера
			$result = $this->queryResult($query, $queryParams);

			if ($this->isSuccessful($result)) {
				$query = "
					select
						error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"
					from p_PersonAmbulatCardLink_ins (
						PersonAmbulatCardLink_id := null, 
						PersonAmbulatCard_id := :PersonAmbulatCard_id,
						PersonCard_id := :PersonCard_id,
						pmUser_id := :pmUser_id
					);
				";
				$this->queryResult($query, array(
					'PersonAmbulatCard_id' => $PersonAmbulatCard_id,
					'PersonCard_id' => $result[0]['PersonCard_id'],
					'pmUser_id' => $pmUserId
				));

				$sql="select Lpu_id as \"Lpu_id\" from PersonAmbulatCard where PersonAmbulatCard_id = :PersonAmbulatCard_id";
				$res = $this->queryResult($sql, array('PersonAmbulatCard_id'=>$PersonAmbulatCard_id));
				if(count($res)>0 && $res[0]['Lpu_id'] != $Lpu_id){
					$sql = 'UPDATE PersonAmbulatCard SET Lpu_id = :Lpu_id WHERE PersonAmbulatCard_id = :PersonAmbulatCard_id';
					$this->queryResult($sql, array('PersonAmbulatCard_id'=>$PersonAmbulatCard_id, 'Lpu_id'=>$Lpu_id));
				}
			}
		}
	}

	/**
	 *
	 * @param type $data
	 */
	private function importE($data) {
		//fputs($this->fileLog,$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') . " - " . $data['DR'] . " " . " начало".date('Y-m-d H:i:s')."  \r\n"); //log - «ФИО + ДР запись о прикреплении принята в СМО»
		$query = 'select Org_Nick as \"Org_Nick\" from v_Lpu_all where Lpu_f003mcod = :MOCode limit 1';
		$params = array("MOCode"=>$this->MOCode);
		$res = $this->db->query($query, $params);
		$MORes = $res->result('array');

		$query = "select 
						p.Person_Surname as \"Person_Surname\",
						p.Person_Firname as \"Person_Firname\",
						p.Person_Secname as \"Person_Secname\",
						to_char(p.Person_Birthday,'yyyy-mm-dd') as \"Person_Birthday\",
						coalesce(pol.Server_id,1) as \"Server_id\",
						pol.Polis_Ser as \"Polis_Ser\",
						pol.Polis_Num as \"Polis_Num\",
						pol.PolisType_id as \"PolisType_id\",
						p.Person_SNILS as \"Person_SNILS\",
						p.PersonEvn_id as \"PersonEvn_id\",
						p.Person_id as \"Person_id\",
						pol.Polis_id as \"Polis_id\",
						p.Person_EdNum as \"Person_EdNum\",
						to_char(pol.Polis_begDate, 'yyyy-mm-dd') as \"Polis_begDate\",
						to_char(pol.Polis_endDate, 'yyyy-mm-dd') as \"Polis_endDate\"
						from v_PersonState p
						left join Polis po on pol.Polis_id=p.Polis_id
						where p.Person_id =:Person_id
						limit 1";
		$params = array("Person_id" => $data['ID_PAC']);
		$result = $this->db->query($query, $params);
		$res = $result->result('array');
		if (count($res) > 0) {
			/*if($res[0]['Person_Birthday']!=$data['DR']){
				$sql = "
						declare @ErrCode int
						declare @ErrMsg varchar(400)
						declare @getdate datetime = dbo.tzGetDate();
						exec p_PersonBirthDay_ins
							@Server_id = 1,
							@Person_id = ?,
							@PersonBirthDay_insDT = @getdate,
							@PersonBirthDay_BirthDay = ?,
							@pmUser_id = 1,
							@Error_Code = @ErrCode output,
							@Error_Message = @ErrMsg output
						select @ErrMsg as ErrMsg
					";
					$res = $this->db->query($sql, array($data['ID_PAC'], $data['DR']));
			}*/
			/*if($res[0]['Person_Surname']!=$data['FAM']){
				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
					declare @getdate datetime = dbo.tzGetDate();
					exec p_PersonSurName_ins
						@Server_id = 1,
						@Person_id = ?,
						@PersonSurName_insDT = @getdate,
						@PersonSurName_SurName = ?,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql,  array($data['ID_PAC'], $data['FAM']));
			}
			if($res[0]['Person_firname']!=$data['IM']){
				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
					declare @getdate datetime = dbo.tzGetDate();
					exec p_PersonFirName_ins
						@Server_id = 1,
						@Person_id = ?,
						@PersonFirName_insDT = @getdate,
						@PersonFirName_FirName = ?,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql,  array($data['ID_PAC'], $data['IM']));
			}
			if(isset($data['OT'])&&$res[0]['Person_Surname']!=$data['OT']){
				$sql = "
					declare @ErrCode int
					declare @ErrMsg varchar(400)
					declare @getdate datetime = dbo.tzGetDate();
					exec p_PersonSecName_ins
						@Server_id = 1,
						@Person_id = ?,
						@PersonSecName_insDT = @getdate,
						@PersonSecName_SecName = ?,
						@pmUser_id = 1,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output
					select @ErrMsg as ErrMsg
				";
				$res = $this->db->query($sql,  array($data['ID_PAC'], $data['OT']));
			}*/
			if ($data['IDENT_RESULT'] == 1 && $data['PR_RESULT'] == 1) {
				$this->counter['prin']++;
				fputs($this->fileLog,$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') . " - " . $data['DR'] . " " . " запись о прикреплении принята в СМО  \r\n"); //log - «ФИО + ДР запись о прикреплении принята в СМО»
			} elseif ($data['IDENT_RESULT'] == 1 && $data['PR_RESULT'] == 2) {
				$this->counter['doubPrik']++;
				//Тип ошибки: Указывается «Конфликт прикрепления» если <IDENT_RESULT> =  1 и  <PR_RESULT> = 2
				//«Ошибки идентификации» если <IDENT_RESULT> =  0 и <REASON> = 8..10
				//ФИО:
				//Дата рождения:
				//Пол:
				//Полис: Вид полиса, Серия полис, Номер полиса
				//Дата прикрепления:
				//МО: Код и наименование МО по справочнику F002, для записей о конфликте прикрепления выводить все указанные МО (<PR_CODE_MO>)
				//Причина невозможности идентификации: Код и наименование причины. Наименование причин приведены в таблице №2 Приложения №1. Значение поля < REASON >.
				//+ значение поля <COMMENT>
				/*fputs($this->fileZL,
					"ФИО: ".$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') ." " . $data['DR'] . " - Конфликт прикрепления\r
					\r\r
					"
				);*/
				$MOSTR ='';
				if(is_array($data['PR_CODE_MO'])){
					foreach($data['PR_CODE_MO'] as $item){
						$query = 'select Org_Nick as \"Org_Nick\" from v_Lpu_all  where Lpu_f003mcod = :MOCode limit 1';
						$params = array("MOCode"=>$item);
						$respon =$this->db->query($query, $params);
						$respon = $respon->result('array');
						if(count($respon)>0)$MOSTR .="МО: ".$item." ".ConvertFromWin1251ToUTF8($respon[0]['Org_Nick'])."\r\n";

					}
				}else{
					$query = 'select Org_Nick as \"Org_Nick\" from v_Lpu_all where Lpu_f003mcod = :MOCode limit 1';
					$params = array("MOCode"=>$data['PR_CODE_MO']);
					$respon =$this->db->query($query, $params);
					$respon = $respon->result('array');
					if(count($respon)>0)$MOSTR .="МО: ".$data['PR_CODE_MO']." ".ConvertFromWin1251ToUTF8($respon[0]['Org_Nick'])."\r\n";
				}

				/*$MOQuery = "select Org_Nick from v_PersonCard PC with(nolock)
				left join v_Lpu_all lpu with(nolock) on lpu.Lpu_id = PC.Lpu_id
				where Person_id = :Person_id";
				$result = $this->db->query($MOQuery, array('Person_id'=>$data['ID_PAC']));
				$res = $result->result('array');*/
				fputs($this->fileZL,
					"Конфликт прикрепления \r\n"
					.$this->counter['confl'].") \r\n".
					"ФИО: ".$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') ."\r\n".
					"Пол: ".(($data['W']==2)?'Ж':'М')."\r\n".
					"Полис: ".(isset($data['VPOLIS'])?$this->polisType[$data['VPOLIS']]:'').", ".(isset($data['SPOLIS'])?$data['SPOLIS']:'').", ".(isset($data['NPOLIS'])?$data['NPOLIS']:'')."\r\n".
					"Дата прикрепления: ".(isset($data['DATE'])?$data['DATE']:'')."\r\n".
					$MOSTR.
					//"МО: ".$this->MOCode." ".$MORes[0]['Org_Nick']."\r\n".
					"\r\n\r\n"
				);
				$this->counter['confl']++;
				fputs($this->fileLog,$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') . " - " . $data['DR'] . " " . " конфликт прикрепления \r\n"); //log - «ФИО + ДР конфликт прикрепления»
			} elseif ($data['IDENT_RESULT'] == '0') {
				$this->counter['badident']++;
				if ($data['REASON'] <= 7||$data['REASON']==11) {
					$good = true;
					/*try {
						$val = $this->doPersonIdentRequestKareliya($res[0]);
						if (isset($val['Error_Msg']) && strlen($val['Error_Msg']) > 0) {
							$good = false;
						}
					} catch (Exception $e) {
						$good = false;
					}
					if ($good) {

					}*/
					try{
						$this->counter['reident']++;
						//$this->savePersonIdentPolis($val, $res[0]);
						$resp = $this->identifyAndAddNewPolisToPerson($res[0]);
						if (!empty($resp) && $resp === 4) {
							fputs($this->fileLog, $data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT']) ? $data['OT'] : '') . " - пациенту не удалось определить тип полиса  \r\n");
						} else {
							fputs($this->fileLog, $data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT']) ? $data['OT'] : '') . " - " . $data['DR'] . " " . " ошибка идентификации \"" . $data['REASON'] . " - " . $this->badIdent[$data['REASON']] . " \" . Запущена повторная идентификация  \r\n"); //log - «ФИО + ДР ошибка идентификации %код наименование ошибки% . Запушена повторная идентификация »
						}
					}catch(Exception $ex){

					}
				} else {

					fputs($this->fileZL,
						"Ошибки идентификации \r\n".
						$this->counter['confl'].") \r\n".
						"ФИО: ".$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') ."\r\n".
						"Пол: ".(($data['W']==2)?'Ж':'М')."\r\n".
						"Полис: ".(isset($data['VPOLIS'])?$this->polisType[$data['VPOLIS']]:'').", ".(isset($data['SPOLIS'])?$data['SPOLIS']:'').", ".(isset($data['NPOLIS'])?$data['NPOLIS']:'')."\r\n".
						"Дата прикрепления: ".(isset($data['DATE'])?$data['DATE']:'')."\r\n".
						"МО: ".$this->MOCode." ".$MORes[0]['Org_Nick']."\r\n".
						"Причина невозможности идентификации: ".(isset($data['REASON'])?$data['REASON'].'-'.$this->badIdent[$data['REASON']]:'')." ".(isset($data['COMMENT'])?$data['COMMENT']:'')."\r\n".
						"\r\n\r\n"
					);
					$this->counter['confl']++;
					fputs($this->fileLog,$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') . " - " . $data['DR'] . " " . " ошибка идентификации \"" . $data['REASON'] . " - " . $this->badIdent[$data['REASON']] . " \" . \r\n"); //log - ФИО + ДР ошибка идентификации %код наименование ошибки%»
				}
			} else {
				///
			}
		} else {
			fputs($this->fileLog,$data['FAM'] . " " . $data['IM'] . " " .(isset($data['OT'])?$data['OT']:''). " - " . $data['DR'] . " " . " значение '".$data['ID_PAC']."'  не удалось найти в системе.  \r\n"); //log - «ФИО + ДР + значение <ID_PAC>  не удалось найти в системе».
		}

		//log - Последней строкой лога должны быть подведены итоги импорта:
		//Количество принятых записей,
		//Количество записей о ЗЛ с двойным прикреплением (конфликт прикрепления),
		//Количество не идентифицированных записей,
		//Количество записей повторно отправленных на идентификацию (<REASON> = 1..7)
		//Тип ошибки: Указывается «Конфликт прикрепления» если <IDENT_RESULT> =  1 и  <PR_RESULT> = 2
		//«Ошибки идентификации» если <IDENT_RESULT> =  0 и <REASON> = 8..10
		//ФИО:
		//Дата рождения:
		//Пол:
		//Полис: Вид полиса, Серия полис, Номер полиса
		//Дата прикрепления:
		//МО: Код и наименование МО по справочнику F002, для записей о конфликте прикрепления выводить все указанные МО (<PR_CODE_MO>)
		//Причина невозможности идентификации: Код и наименование причины. Наименование причин приведены в таблице №2 Приложения №1. Значение поля < REASON >.
		//+ значение поля <COMMENT>
		//fputs($this->fileLog,$data['FAM'] . " " . $data['IM'] . " " . (isset($data['OT'])?$data['OT']:'') . " - " . $data['DR'] . " " . " конец".date('Y-m-d H:i:s')."  \r\n"); //log - «ФИО + ДР запись о прикреплении принята в СМО»
	}

	/**
	 *
	 * @param type $data
	 * @return type
	 */
	private function identifyAndAddNewPolisToPerson($data) {
		$this->load->model('PersonIdentRequest_model', 'identmodel');

		// 1. отправляем запрос сервису идентификации
		$this->load->library('swPersonIdentKareliyaSoap');
		$identObject = new swPersonIdentKareliyaSoap(
			$this->config->item('IDENTIFY_SERVICE_URI'),
			$this->config->item('IDENTIFY_SERVICE_LOGIN'),
			$this->config->item('IDENTIFY_SERVICE_PASS'),
			(int) $this->config->item('IS_DEBUG')
		);

		// Формирование данных для запроса к сервису БДЗ
		$requestData = array(
			'FAM' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
			'IM' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
			'OT' => mb_ucfirst(mb_strtolower($data['Person_Secname'])),
			'birthDate' => (!empty($data['Person_Birthday']) ? $data['Person_Birthday'] : '1900-01-01'),
			'SerPolis' => null, //$data['Polis_Ser'],
			'NumPolis' => null, //$data['Polis_Num'],
			'SerDocument' => null, //$data['Document_Ser'],
			'NumDocument' => null, //$data['Document_Num'],
			'SNILS' => null, //$data['Person_SNILS'], // тут снилс надо передавать в формате "ххх-ххх-ххх хх"
			'DATEON' => (!empty($data['DATEON']) ? $data['DATEON'] : date('Y-m-d')),
			'Type_Request' => 0 // без признака актуальности
		);
		//var_dump($requestData); echo "<br><br>";
		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);
		//var_dump($requestResponse);

		if (!empty($requestResponse['errorCode'])) {
			return $requestResponse['errorCode']; // не идентифицирован
		}

		$added = false;

		if (!empty($requestResponse['identData'][0]['FAM'])) {
			/*
			  $requestResponse['identData'][0]['typepolis'] -- тип полиса
			  $requestResponse['identData'][0]['serpolis'] -- серия полиса
			  $requestResponse['identData'][0]['numpolis'] -- номер полиса
			  $requestResponse['identData'][0]['vidpolic'] -- дата выдачи
			  $requestResponse['identData'][0]['closepolic'] -- дата закрытия
			  $requestResponse['identData'][0]['codestrah'] -- страховая организация
			 */
			$ptResponse = $this->identmodel->getPolisTypeCode($requestResponse['identData'][0]['typepolis']);
			if (is_array($ptResponse) && count($ptResponse) > 0 && !empty($ptResponse[0]['PolisType_id'])) {
				$data['PolisType_id'] = $ptResponse[0]['PolisType_id'];
			} else {
				return 4; // не удалось определить тип полиса
			}
			$data['Polis_Ser'] = $requestResponse['identData'][0]['serpolis'];
			$data['Polis_Num'] = $requestResponse['identData'][0]['numpolis'];
			$data['Snils'] = str_replace(array(' ', '-'), '', $requestResponse['identData'][0]['snils']);

			// https://redmine.swan.perm.ru/issues/43989
			// ... реализовать разделение на серию и номер временного свидетельства, аналогично тому, как это происходит при идентификации по кнопке.
			if ($data['PolisType_id'] == 3) {
				$data['Polis_Num'] = $data['Polis_Ser'].''.$data['Polis_Num'];
				$data['Polis_Ser']=null;
			}

			$smoIdResponse = $this->identmodel->getOrgSmoIdOnCode($requestResponse['identData'][0]['codestrah']);
			if (is_array($smoIdResponse) && count($smoIdResponse) > 0 && !empty($smoIdResponse[0]['OrgSmo_id'])) {
				$data['OrgSMO_id'] = $smoIdResponse[0]['OrgSmo_id'];
			} else {
				return 5; // не удалось опредеилть СМО
			}
			$data['Polis_begDate'] = $requestResponse['identData'][0]['vidpolic'];
			$data['Polis_endDate'] = ((!empty($requestResponse['identData'][0]['closepolic']) && mb_substr($requestResponse['identData'][0]['closepolic'], 0, 10) != "1899-12-30") ? $requestResponse['identData'][0]['closepolic'] : null);

			// если единый номер и номер полиса не 16 знаков, то "не удалось определить тип полиса"
			if ($data['PolisType_id'] == 4 && mb_strlen($data['Polis_Num']) < 16) {
				return 4;
			}

			//echo $data['Polis_endDate'];
			// проверяем есть ли у человека такой полис в PersonPolis, если нет добавляем
			$query = "
				select
					PersonPolis_id as \"PersonPolis_id\",
					coalesce(Polis_Ser, '') as \"Polis_Ser\",
					coalesce(Polis_Num, '') as \"Polis_Num\",
					to_char(Polis_endDate,'yyyy-mm-dd') as  \"Polis_endDate\"
				from
					v_PersonPolis 
				where
					Person_id = :Person_id
					and PolisType_id = :PolisType_id
					and OrgSMO_id = :OrgSMO_id
					and Polis_begDate = :Polis_begDate
				limit 1
			";
			//		and ISNULL(Polis_Num, '') = ISNULL(:Polis_Num, '')
			//		and ISNULL(Polis_Ser, '') = ISNULL(:Polis_Ser, '')
			$data['Server_id'] = isset($data['Server_id']) ? $data['Server_id'] : 0;
			$data['pmUser_id'] = $_SESSION['pmuser_id'];

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				$resp = $result->result('array');

				if (is_array($resp) && count($resp) > 0) {
					// Если серия и номер не совпадают, то обновляем
					if (
						($resp[0]['Polis_Ser'] != $data['Polis_Ser'] || $resp[0]['Polis_Num'] != $data['Polis_Num']||$resp[0]['Polis_endDate'] != $data['Polis_endDate'])
						&& (empty($data['Polis_endDate']) || $data['Polis_endDate'] >= $data['Polis_begDate'])
					) {
						$data['PersonPolis_id'] = $resp[0]['PersonPolis_id'];
						$query = "
							select personpolis_id as \"PersonPolis_id\"
							from p_PersonPolis_upd (
								PersonPolis_id := :PersonPolis_id,
								Server_id := :Server_id,
								Person_id := :Person_id,
								OmsSprTerr_id := 1392,
								PolisType_id := :PolisType_id,
								OrgSMO_id := :OrgSMO_id,
								Polis_Ser := :Polis_Ser,
								Polis_Num := :Polis_Num,
								Polis_begDate := :Polis_begDate,
								Polis_endDate := :Polis_endDate,
								PersonPolis_insDT := :Polis_begDate,
								pmUser_id := :pmUser_id
							);
						";
						//echo getDebugSQL($query, $data);
						$result = $this->db->query($query, $data);
						$resp = $result->result('array');

						$added = true;
					} else {
						$resp[0]['PersonPolis_id'] = null;
					}
				}
				// Если документа ОМС нет, то добавляем
				else {
					$query = "
						select personpolis_id as \"PersonPolis_id\"

						from p_PersonPolis_ins (
							PersonPolis_id := :PersonPolis_id,
							Server_id := :Server_id,
							Person_id := :Person_id,
							OmsSprTerr_id := 1392,
							PolisType_id := :PolisType_id,
							OrgSMO_id := :OrgSMO_id,
							Polis_Ser := :Polis_Ser,
							Polis_Num := :Polis_Num,
							Polis_begDate := :Polis_begDate,
							Polis_endDate := :Polis_endDate,
							PersonPolis_insDT := :Polis_begDate,
							pmUser_id := :pmUser_id
						);
					";
					//echo getDebugSQL($query, $data);
					if(empty($data['Polis_endDate']) || $data['Polis_endDate'] >= $data['Polis_begDate']){
						$result = $this->db->query($query, $data);
						$resp = $result->result('array');

						$added = true;
					}
				}

				// если вставили открытый полис, то все остальные открытые закрываем датой открытия нового минус один день
				if (!empty($resp[0]['PersonPolis_id']) && empty($data['Polis_endDate'])) {
					$query = "
						update
							p
						set
							p.Polis_endDate = :Polis_endDate
						from
							Polis p
							inner join v_PersonPolis pp on pp.Polis_id = p.Polis_id
						where
							pp.Person_id = :Person_id and pp.PersonPolis_id <> :PersonPolis_id and p.Polis_endDate is null
					";

					$this->db->query($query, array(
						'PersonPolis_id' => $resp[0]['PersonPolis_id'],
						'Person_id' => $data['Person_id'],
						'Polis_endDate' => date('Y-m-d', (strtotime($data['Polis_begDate']) - 60 * 60 * 24))
					));
				}
			}
			// для единого номера полиса проверяем есть ли у человека такой полис в PersonPolisEdNum, если нет добавляем
			if ($data['PolisType_id'] == 4) {
				$query = "
					select
						PersonPolisEdNum_id as \"PersonPolisEdNum_id\"
					from
						v_PersonPolisEdNum
					where
						Person_id = :Person_id
						and PersonPolisEdNum_EdNum = :Polis_Num
						and PersonPolisEdNum_begDT = :Polis_begDate
					limit 1
				";
				$result = $this->db->query($query, $data);
				if (is_object($result)) {
					$resp = $result->result('array');
					if (is_object($result)) {
						if (empty($resp[0]['PersonPolisEdNum_id'])) {
							$query = "
								select personpolisednum_id as \"PersonPolisEdNum_id\"

								from p_PersonPolisEdNum_ins (
									PersonPolisEdNum_id := :PersonPolisEdNum_id,
									Server_id := :Server_id,
									Person_id := :Person_id,
									PersonPolisEdNum_EdNum := :Polis_Num,
									PersonPolisEdNum_begDT := :Polis_begDate,
									PersonPolisEdNum_insDT := :Polis_begDate,
									pmUser_id := :pmUser_id
								);
							";
							//echo getDebugSQL($query, $data);
							$result = $this->db->query($query, $data);
							$added = true;
						}
					}
				}
			}

			// Обновляем СНИЛС, если нужно https://redmine.swan.perm.ru/issues/88816
			if (!empty($data['Snils'])) {

				$old = $this->getFirstResultFromQuery("
					select RTRIM(coalesce(PersonSnils_Snils, '')) as \"Person_Snils\"
					from v_PersonSnils
					where Person_id = :Person_id
					limit 1
				", array('Person_id' => $data['Person_id']));

				if (empty($old) || $data['Snils'] != str_replace(array('-', ' '), '', $old)) {

					$query = "
						select 
							personevn_id as \"PersonEvn_id\", 
							error_code as \"Error_Code\", 
							error_message as \"Error_Msg\"
						from p_PersonSnils_ins (
							PersonSnils_id := :PersonEvn_id,
							Server_id := :Server_id,
							Person_id := :Person_id,
							PersonSnils_Snils := :PersonSnils_Snils,
							pmUser_id := :pmUser_id
						);
					";

					$res = $this->db->query($query, array(
						'Server_id' => $data['Server_id'],
						'Person_id' => $data['Person_id'],
						'PersonSnils_Snils' => $data['Snils'],
						'pmUser_id' => $data['pmUser_id']
					));
				}
			}

			$sql = "
				select 
						error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"
				from p_Person_server (
					Server_id := 0,
					Person_id := :Person_id,
					BDZ_Guid := null,
					pmUser_id := :pmUser_id
					);
			";
			$res = $this->db->query($sql, array("Person_id" => $data['Person_id'], "pmUser_id" => $data['pmUser_id']));
			// запускаем xp_PersonAllocatePersonEvnByEvn, если что то добавили
			if ($added) {
				$query = "
					select 
						error_code as \"Error_Code\", 
            			error_message as \"Error_Msg\"

					from xp_PersonAllocatePersonEvnByEvn (
						Person_id := :Person_id);
				";

				$this->db->query($query, $data);
			}
		} else {
			return 1; // не идентифицирован
		}

		if ($added) {
			return 6; // запись изменена
		}

		return 7; // полис уже был добавлен
	}

	/**
	 *  Выполнение запроса на идентификацию пациента в базе данных застрахованных республики Карелия
	 *  Входящие данные: $_POST['Person_id']
	 *  На выходе: JSON-строка
	 *  Используется: форма редактирования человека
	 */
	private function doPersonIdentRequestKareliya($data) {


		$this->load->model("Options_model", "opmodel");


		$this->load->library('swPersonIdentKareliyaSoap');
		$identObject = new swPersonIdentKareliyaSoap(
			$this->config->item('IDENTIFY_SERVICE_URI'),
			$this->config->item('IDENTIFY_SERVICE_LOGIN'),
			$this->config->item('IDENTIFY_SERVICE_PASS'),
			(int) $this->config->item('IS_DEBUG')
		);

		$val = array();


		/*if (mb_strlen($err) > 0) {
			echo json_return_errors($err);
			return false;
		}*/

		$identDT = time();

		/* $response = $this->identmodel->getOrgSmoCode($data['OrgSmo_id']);
		  if ( is_array($response) && count($response) > 0 && !empty($response[0]['OrgSmo_Code'])) {
		  $data['OrgSmo_Code'] = $response[0]['OrgSmo_Code'];
		  } else {
		  $data['OrgSmo_Code'] = '';
		  } */

		/* $response = $this->identmodel->getKladrCode($data['KLArea_id'], $data['KLStreet_id']);
		  if ( is_array($response) && count($response) > 0 && !empty($response[0]['Kladr_Code'])) {
		  $data['Kladr_Code'] = $response[0]['Kladr_Code'];
		  } else {
		  $data['Kladr_Code'] = '';
		  } */

		// Формирование данных для запроса к сервису БДЗ
		$requestData = array(
			'FAM' => mb_ucfirst(mb_strtolower($data['Person_Surname'])),
			'IM' => mb_ucfirst(mb_strtolower($data['Person_Firname'])),
			'OT' => (!empty($data['Person_Secname']) ? mb_ucfirst(mb_strtolower($data['Person_Secname'])) : null),
			'birthDate' => (!empty($data['Person_Birthday']) ? $data['Person_Birthday'] : '1900-01-01'),
			'SerPolis' => null, //$data['Polis_Ser'],
			'NumPolis' => null, //$data['Polis_Num'],
			'SerDocument' => null, //$data['Document_Ser'],
			'NumDocument' => null, //$data['Document_Num'],
			'SNILS' => null, //$data['Person_SNILS'], // тут снилс надо передавать в формате "ххх-ххх-ххх хх"
			'DATEON' => date('Y-m-d'),
			'Type_Request' => false
		);

		/* данные для тестирования
		  $requestData = array(
		  'FAM' => 'ПЕТРОВ',
		  'IM' => 'ПАВЕЛ',
		  'OT' => 'ПАВЛОВИЧ',
		  'birthDate' => '1900-01-01',
		  'SerPolis' => null,//'РК',
		  'NumPolis' => null,//'73915',
		  'SerDocument' => null,
		  'NumDocument' => null,
		  'SNILS' => null,
		  'DATEON' => '1900-01-01',
		  'Type_Request' => 1
		  ); */
		$test = Array(
			"errorMsg" => null,
			"identData" => Array(
				Array(
					"FAM" => 'ВАСИЛЬЕВА',
					"IM" => 'ЛЮДМИЛА',
					"OT" => 'ЕФИМОВНА',
					"birthDate" => '1939-07-26',
					"sex" => 'Ж',
					"typepolis" => 3,
					"serpolis" => null,
					"numpolis" => '1072060873000108',
					"vidpolic" => '2013-08-05',
					"closepolic" => '2014-08-06',
					"typeclosepolis" => 3,
					"codestrah" => 10003,
					"codedoc" => 14,
					"serdoc" => '86 00',
					"numdoc" => '153440',
					"docdate" => '1899-12-30',
					"whovid" => null,
					'LPU_CODE' => '2000015376',
					'LPUDT' => '2014-11-11',
					'LPUDX' => '',
					'LPUAUTO' => '',
					"snils" => '084-093-745 82',
					"adresreg" => Array(
						"pred" => 'ОЛОНЕЦКИЙ Р-Н,ИЛЬИНСКИЙ П,ГАНИЧЕВА УЛ,д.2,кв.2',
						"codereg" => 86000,
						"codeOKATO" => null,
						"pochindex" => null,
						"rayon" => 'ОЛОНЕЦКИЙ Р-Н',
						"city" => 'ИЛЬИНСКИЙ П',
						"naspunkt" => null,
						"street" => 'ГАНИЧЕВА УЛ',
						"dom" => 2,
						"korpus" => null,
						"kvartira" => 2,
					),
					"adresfact" => Array(
						"pred" => 'ОЛОНЕЦКИЙ Р-Н,ИЛЬИНСКИЙ П,ГАНИЧЕВА УЛ,д.2,кв.2',
						"codereg" => '86000',
						"codeOKATO" => null,
						"pochindex" => null,
						"rayon" => 'ОЛОНЕЦКИЙ Р-Н',
						"city" => 'ИЛЬИНСКИЙ П',
						"naspunkt" => null,
						"street" => 'ГАНИЧЕВА УЛ',
						"dom" => 2,
						"korpus" => null,
						"kvartira" => 2,
					),
					"mocode" => null,
					"STAT" => 1,
					"PHONE" => null
				)
			),
			"success" => 1
		);
		//$requestResponse = $test;
		//
		// Выполнение запроса к сервису БДЗ
		$requestResponse = $identObject->doPersonIdentRequest($requestData);

		// По людям, которые однажды прошли идентификацию, и был проставлен признак БДЗ, при повторной идентификации, в случае возврата ответа "Соответствие не найдено" (то есть актуального полиса нет на момент идентификации) - делать повторный запрос без признака актуальности, но с указанием номера полиса.
		/* if ( $requestResponse['success'] === false && !empty($requestResponse['errorCode']) && $requestResponse['errorCode'] == 1 && !empty($data['Person_IsBDZ']) && $data['Person_IsBDZ'] == 1 ) {
		  $requestData['Type_Request'] = 0;
		  $requestResponse = $identObject->doPersonIdentRequest($requestData);
		  } */
		//print_r($requestResponse);
		if ($requestResponse['success'] === false) {
			echo json_return_errors($requestResponse['errorMsg']);
			return false;
		}

		// Полученные данные
		$personData = $requestResponse['identData'];

		// Если идентифицирован...
		if (is_array($personData)) {
			//print_r( count($personData));
			//if ( count($personData) == 1 && !empty($personData[0]['FAM']) ) {
			if (count($personData) >= 1 && !empty($personData[0]['FAM'])) { // похоже это ошибка сервиса
				// ... то формируем данные для подстановки на форму редактирования
				$val['Person_identDT'] = $identDT;
				$val['PersonIdentState_id'] = 1;
				$val['Server_id'] = 0;
				//print_r($personData);
				// Карелию -> в Уфу :)
				$map = array(
					'FAM' => 'FAM',
					'IM' => 'NAM',
					'OT' => 'FNAM',
					'sex' => 'SEX',
					'birthDate' => 'BORN_DATE',
					'serpolis' => 'POL_SER',
					'numpolis' => 'POL_NUM_16',
					'vidpolic' => 'GIV_DATE', // OpenPolis
					'closepolic' => 'ELIMIN_DATE', // ClosePolis
					'codestrah' => null,
					'typepolis' => null,
					'typeclosepolis' => null,
					//'codedoc' => 'DOC_TYPE',
					//'serdoc' => 'DOC_SER',
					//'numdoc' => 'DOC_NUM',
					//'docdate' => 'Document_begDate',
					//'whovid' => 'OrgDep_id',
					'mocode' => 'Lpu_Code',
					'snils' => 'SNILS',
					'STAT' => 'CATEG',
					'PHONE' => 'PersonPhone_Phone'
				);
				/*
				  adresreg		Адрес регистрации застрахованного лица
				  adresfact		Адрес жительства застрахованного лица
				 */

				foreach ($personData[0] as $key => $value) {
					switch ($key) {
						case 'adresreg': case 'adresfact': // разбор адресов
						/*
						  if ($key == 'adresreg') {
						  $val['RAddress_Name'] = $value['pred'];
						  } else {
						  $val['PAddress_Name'] = $value['pred'];
						  }
						 */
						/* if ( !empty($value) && preg_match('/^\d+$/', $value) && in_array(mb_strlen($value), array(13, 17, 19)) ) {
						  $parseKladrCodeResponse = $this->identmodel->parseKladrCode(
						  $this->identmodel->tmp_Altnames_getNewCode($value),// когда Обновление в СБЗ будет произведено, перекодировку надо будет убрать. подробнее см. #11630
						  (!empty($personData[0]['HOUSE']) ? $personData[0]['HOUSE'] : ''),
						  (!empty($personData[0]['CORP']) ? $personData[0]['CORP'] : ''),
						  (!empty($personData[0]['FLAT']) ? $personData[0]['FLAT'] : '')
						  );

						  if ( is_array($parseKladrCodeResponse) && count($parseKladrCodeResponse) > 0 && !empty($parseKladrCodeResponse[0]['Address_Address'])) {
						  $val['KLCountry_rid'] = $parseKladrCodeResponse[0]['KLCountry_id'];
						  if (empty($val['KLAdr_Index'])) {
						  $val['KLAdr_Index'] = $parseKladrCodeResponse[0]['KLAdr_Index'];
						  }
						  $val['KLRgn_rid'] = $parseKladrCodeResponse[0]['KLRgn_id'];
						  $val['KLSubRgn_rid'] = $parseKladrCodeResponse[0]['KLSubRgn_id'];
						  $val['KLCity_rid'] = $parseKladrCodeResponse[0]['KLCity_id'];
						  $val['KLTown_rid'] = $parseKladrCodeResponse[0]['KLTown_id'];
						  $val['KLStreet_rid'] = $parseKladrCodeResponse[0]['KLStreet_id'];
						  $val['PersonSprTerrDop_rid'] = $parseKladrCodeResponse[0]['PersonSprTerrDop_id'];
						  $val['RAddress_Name'] = $parseKladrCodeResponse[0]['Address_Address'];
						  }
						  else {
						  $val['Alert_Msg'] = 'Не удалось распознать адрес регистрации';
						  }
						  } */
						break;

						case 'closepolic': // обработка дат
							if (mb_strlen($value) > 0 && mb_substr($value, 0, 10) != "1899-12-30") {
								$val['PersonIdentState_id'] = 3;
								$val['Server_id'] = $data['Server_id'];
							}
						case 'birthDate':
						case 'vidpolic':
							$d = mb_substr($value, 0, 10);
							if ($d == "1899-12-30") { // сервис вместо пустой даты возвращает такое безобразие
								$val[$map[$key]] = null;
							} else {
								$val[$map[$key]] = ConvertDateEx(mb_substr($value, 0, 10), "-", ".");
							}
							break;

						case 'sex': // обработка пола
							if (mb_strtolower($value) == 'ж') {
								$val['Sex_Code'] = 2;
							} else if (mb_strtolower($value) == 'м') {
								$val['Sex_Code'] = 1;
							}
							break;

						case 'typepolis': // тип полиса
							if (is_numeric($value)) {
								$ptResponse = $this->identmodel->getPolisTypeCode($value);
								if (is_array($ptResponse) && count($ptResponse) > 0 && !empty($ptResponse[0]['PolisType_id'])) {
									$val['PolisType_id'] = $ptResponse[0]['PolisType_id'];
								} else {
									$val['Alert_Msg'] = 'Не удалось определить тип полиса';
								}
							}
							break;

						case 'codestrah': // страховая
							if (is_numeric($value)) {
								$smoIdResponse = $this->identmodel->getOrgSmoIdOnCode($value);
								if (is_array($smoIdResponse) && count($smoIdResponse) > 0 && !empty($smoIdResponse[0]['OrgSmo_id'])) {
									$val['OrgSmo_id'] = $smoIdResponse[0]['OrgSmo_id'];
								} else {
									$val['Alert_Msg'] = 'Не удалось определить идентификатор СМО';
								}
							}
							break;

						case 'snils': // снилс
							$val[$map[$key]] = str_replace(array(' ', '-'), '', $value);
							break;

						case 'STAT': // социальный статус (согласно спецификации возвращается только работающий и неработающий)
							$val[$map[$key]] = $this->identmodel->getValidSocStatusCode($value);
							break;

						case 'PHONE': // телефон
							$val[$map[$key]] = substr(preg_replace("/\D+/i", "", $value), -10);
							break;

						/* case 'codedoc': //
						  $documentTypeIdResponse = $this->identmodel->getDocumentTypeId($value);
						  if ( is_array($documentTypeIdResponse) && count($documentTypeIdResponse) > 0 && !empty($documentTypeIdResponse[0]['DocumentType_id'])) {
						  $val[$map[$key]] = $documentTypeIdResponse[0]['DocumentType_id'];
						  }
						  break; */

						default:
							if (isset($map[$key])) {
								$val[$map[$key]] = $value;
							} else {
								$val[$key] = $value;
							}

							break;
					}
				}
			} elseif (count($personData) > 1) { // нашли больше одной записи
				$val['Alert_Msg'] = 'По указанным данным невозможно выполнить идентификацию, пожалуйста, проверьте и уточните данные, и повторите попытку.';
				$val['PersonIdentState_id'] = 2;
			} else {
				// такая ошибка может быть очень иногда, когда сервис идентификации вернул данные, а фамилия пустая или ответ есть, а идентифицированных записей нет
				// (согласно спецификации если ничего не нашли, то ответ сервиса пустой, значит проверка отработает выше, а это нештатная ситуация)
				$val['Alert_Msg'] = 'Ошибка сервиса идентификации или по указанным данным человек не идентифицирован: ' . var_export($personData, true);
				$val['PersonIdentState_id'] = 2;
			}
		} else {
			// такое вряд ли будет
			$val['Error_Msg'] = 'Неверный ответ сервиса идентификации: ' . var_export($personData, true);
		}

		if (!empty($val['KLAdr_Index'])) {
			$val['RAddress_Name'] = $val['KLAdr_Index'] . (!empty($val['RAddress_Name']) ? ', ' . $val['RAddress_Name'] : '');
		}

		// Обработка данных после формирования массива данных
		// 1) Если тип полиса = временный, серия отсутствует и длина номера полиса больше 6 символов, то делим номер полиса на серию и номер по следующим правилам
		//    берем 3 символа слева и кидаем их в серию, остальное в номер (https://redmine.swan.perm.ru/issues/26562#note-76)
		if (($val['PolisType_id'] == 3)) {
			$val['POL_NUM_16'] = $val['POL_SER'].''.$val['POL_NUM_16'];
		}

		array_walk($val, 'ConvertFromWin1251ToUTF8');

		return $val;
	}

	/**
	 *
	 * @param type $val
	 * @param type $item
	 */
	private function savePersonIdentPolis($val, $item) {
		$Polis_begDate = empty($val['GIV_DATE']) ? NULL : date('Y-m-d', strtotime($val['GIV_DATE']));
		$Polis_closeDate = empty($val['GIV_DATE']) ? NULL : date('Y-m-d', strtotime($val['GIV_DATE'] . "-1 days"));
		$Polis_endDate = empty($val['ELIMIN_DATE']) ? NULL : date('Y-m-d', strtotime($val['ELIMIN_DATE']));
		$OmsSprTerr_id = 1392; //bad
		$PolisType_id = (empty($val['PolisType_id']) ? NULL : $val['PolisType_id']);
		$OrgSmo_id = (empty($val['OrgSmo_id']) ? NULL : $val['OrgSmo_id']);
		$Polis_Ser = (empty($val['POL_SER']) ? '' : $val['POL_SER']);
		$Polis_Num = (empty($val['POL_NUM_16']) ? '' : $val['POL_NUM_16']);
		$params = array();
		$Federal_Num = NULL;
		if ($PolisType_id == 4) {
			$Federal_Num = $Polis_Num;
		}
		$queryParams = array(
			'Polis_begDate' => $Polis_begDate,
			'Polis_endDate' => $Polis_endDate,
			'Polis_closeDate' => $Polis_closeDate
		);
		$proc = 'ins';
		if ($item['Polis_id'] != NULL) {
			if (!empty($item['Polis_begDate']) && (empty($item['Polis_endDate']) || $item['PolisType_id'] == 3)) {
				$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
				$queryParams['Polis_id'] = $item['Polis_id'];
				$this->db->query($sql, $queryParams);
			}
		}
		$params = array(
			'PersonEvn_id' => null,
			'Polis_id' => $item['Polis_id'],
			'Server_id' => 0,
			'Person_id' => $item['Person_id'],
			'OmsSprTerr_id' => $OmsSprTerr_id,
			'PolisType_id' => $PolisType_id,
			'OrgSmo_id' => $OrgSmo_id,
			'Polis_Ser' => $Polis_Ser,
			'Polis_Num' => $Polis_Num,
			'Polis_begDate' => $Polis_begDate,
			'Polis_endDate' => $Polis_endDate,
			'pmUser_id' => $_SESSION['pmuser_id']
		);

		if ($Federal_Num > 0 && $Federal_Num != $item['Person_EdNum']) {
			$query = "
				select 
					error_code as \"Error_Code\", 
            		error_message as \"Error_Msg\"

				from p_PersonPolisEdNum_ins (
					Server_id := 0,
					Person_id := :Person_id,
					PersonPolisEdNum_insDT := :Polis_begDate,
					PersonPolisEdNum_EdNum := :Polis_Num,
					pmUser_id := :pmUser_id
				);
			";
			$this->db->query($query, $params);
		}
		$query = "
		select 
			personevn_id as \"PersonEvn_id\", 
			error_code as \"Error_Code\", 
            error_message as \"Error_Msg\"
		from p_PersonPolis_ins (
			Server_id := :Server_id,
			Person_id := :Person_id,
			OmsSprTerr_id := :OmsSprTerr_id,
			PolisType_id := :PolisType_id,
			OrgSmo_id := :OrgSmo_id,
			Polis_Ser := :Polis_Ser,
			Polis_Num := :Polis_Num,
			Polis_begDate := :Polis_begDate,
			Polis_endDate := :Polis_endDate,
			pmUser_id := :pmUser_id
		);
		";

		if(empty($params['Polis_endDate']) || $params['Polis_endDate'] >= $params['Polis_begDate']){
			$res = $this->db->query($query, $params);
		}

		return true;
	}

	/**
	 *
	 * @param type $data
	 * @return DOMDocument
	 */
	private function checkXsd($data) {

		$xml = new DOMDocument();
		@$xml->load($data['FileFullName']);
		//$xml->encoding = 'utf-8';
		//print_r($xml);
		$xsd_tpl = '';
		switch ($this->type) {
			case "E":
				$xsd_tpl = $_SERVER['DOCUMENT_ROOT'] . '/documents/xsd/ZL-5.xsd';
				break;
			case "NP":
				$xsd_tpl = $_SERVER['DOCUMENT_ROOT'] . '/documents/xsd/ZL-3.xsd';
				break;
			default:
				throw new Exception('Это магия.'); //по идее сюда никак не попадет
				break;
		}
		//array_walk_recursive($sel, 'ConvertFromWin1251ToUTF8');
		$xsd_tpl = iconv('utf-8', 'cp1251', $xsd_tpl);

		/*if(!@$xml->schemaValidate($_SERVER['DOCUMENT_ROOT'] . '/documents/xsd/ZL-5.xsd')&&!@$xml->schemaValidate($_SERVER['DOCUMENT_ROOT'] . '/documents/xsd/ZL-3.xsd')){
			throw new Exception('very bad');
		}*/
		if (!is_object($xml)) {
			throw new Exception('Не удалось разобрать XML');
		}

		if (!@$xml->schemaValidate($xsd_tpl)) {
			throw new Exception('файл не соответствует XSD-схеме.');
		}
		return $xml;
	}

}

?>
