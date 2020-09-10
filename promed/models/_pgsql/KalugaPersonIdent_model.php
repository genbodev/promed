<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * KalugaPersonIdent_model
 */
class KalugaPersonIdent_model extends SwPgModel {

	var $exchange_dir;
	var $max_packet;
	var $PersonEvn_id;

	/**
	 * construct
	 */
	function __construct() {
		parent::__construct();

		$this->load->library('textlog', array('file' => 'KalugaPersonIdent.log'));

		if (defined('IDENT_EXCHANGE_DIR')) {
			$this->exchange_dir = IDENT_EXCHANGE_DIR;
		} else {
			$this->textlog->add('--- Ошибка! Не задана папка обмена! ---');
			$this->textlog->add('Выполнение прервано');
			exit;
		}

		if (defined('IDENT_MAX_PACKET') && is_numeric(IDENT_MAX_PACKET) && IDENT_MAX_PACKET > 0) {
			$this->max_packet = IDENT_MAX_PACKET;
		} else {
			$this->max_packet = 50;
		}
	}
	
	/**
	 * сбор данных и запись в файлы для отправки запроса
	 */
	function PersonIdentSend() {
     
		$this->textlog->add('--- Запуск отправки ---');

		$PersonIdentPackage_id = null;
		$query="
			select
				p.Person_id as \"Person_id\",
				ppp.PersonIdentPackagePos_id as \"PersonIdentPackagePos_id\",
				p.Person_SurName as \"Person_SurName\",
				p.Person_FirName as \"Person_FirName\",
				p.Person_SecName as \"Person_SecName\",
				p.Sex_id as \"Sex_id\",
				to_char(p.Person_Birthday,'yyyy-mm-dd hh:mi:ss') as \"Person_Birthday\",
				dt.DocumentType_Code as \"DocumentType_Code\",
				p.Document_Ser as \"Document_Ser\",
				p.Document_Num as \"Document_Num\",
				p.Person_EdNum as \"Person_EdNum\",
				p.Person_SNILS as \"Person_SNILS\"
			from PersonIdentPackagePos ppp 
			inner join v_PersonState p  on ppp.Person_id = p.Person_id
			left join v_Document d  on p.Document_id = d.Document_id
			left join v_DocumentType dt  on dt.DocumentType_id = d.DocumentType_id
			where PersonIdentPackage_id is null
            limit {$this->max_packet}
		";
		$result = $this->db->query($query, array());
		if ( is_object($result) ) {
			$res = $result->result('array');
			if ( count($res) > 0 ) {

				foreach($res as $item){

					$PersonIdentPackage_id = $this->createPersonIdentPackage();
					if ($PersonIdentPackage_id === false) {
						$this->textlog->add('Ошибка при создании пакета');
						return array(array('errorMsg' => 'Ошибка при создании пакета', 'success' => false));
					}

					$PersonArr = $this->formatRequest($item);

					$sql = "
						update PersonIdentPackagePos
						set PersonIdentPackage_id = :PersonIdentPackage_id
						where PersonIdentPackagePos_id = :PersonIdentPackagePos_id
					";
					$this->db->query($sql, array(
						'PersonIdentPackagePos_id' => $item['PersonIdentPackagePos_id'],
						'PersonIdentPackage_id' => $PersonIdentPackage_id)
					);

					$this->KalugaPersonIdentSend($PersonArr, $PersonIdentPackage_id);
				}

			} else {
				$this->textlog->add('Кол-во элементов в массиве равно нулю');
				return array(array('success'=>true));
			}
		}
		else {
			return array(array('errorMsg' => 'Запрос не вернул объект.', 'success' => false));
		}

		$this->textlog->add('--- Окончание отправки ---');

		return array(array('success'=>true));
	}

	/**
	 * приведение данных к нужному формату для отправки
	 */
	function formatRequest($data) {

		if (!is_array($data)) return false;
		
		$res = array(
			'FAM' => mb_ucfirst(mb_strtolower($data['Person_SurName'])),
			'IM' => mb_ucfirst(mb_strtolower($data['Person_FirName'])),
			'OT' => mb_ucfirst(mb_strtolower($data['Person_SecName'])),
			'W' => $data['Sex_id'],
			'DR' => $data['Person_Birthday'],
			'DOCTP' => $data['DocumentType_Code'],
			'DOCS' => $data['Document_Ser'],
			'DOCN' => $data['Document_Num'],
			'ENP' => $data['Person_EdNum'],
			'SNILS' => $data['Person_SNILS']
		);
		
		// Условно-обязательные элементы. При отсутствии не передаются 
		$cond = array('DOCTP', 'DOCS', 'DOCN', 'ENP', 'SNILS');
		foreach($cond as $c) {
			if (empty($res[$c])) {
				unset($res[$c]);
			}
		}

		return $res;
	}

	/**
	 * создание нового пакета пациентов
	 */
	function createPersonIdentPackage() {

        $query = "
        select
            Error_Code as \"Error_Code\",
            Error_Message as \"Err_Msg\",
            PersonIdentPackage_id as \"PersonIdentPackage_id\"
        from p_PersonIdentPackage_ins
            (
 				PersonIdentPackage_id := null,
				PersonIdentPackage_Name := 'PersonIdentPackage_Name',
				PersonIdentPackage_begDate := :PersonIdentPackage_begDate,
				pmUser_id := 1
            )";


		$result = $this->db->query($query, array('PersonIdentPackage_begDate' => date('Y-m-d')));
		if ( is_object($result) ) {
			$result = $result->result('array');
			return $result[0]['PersonIdentPackage_id'];
		} else {
			return false;
		}
	}

	/**
	 * формирование файла-запроса
	 */
	function KalugaPersonIdentSend($item, $PersonIdentPackage_id) {

		$xml = simplexml_load_string('<?xml version="1.0" encoding="windows-1251"?><SMO_ZAPROS />');

		$filename = "Z400057_01_{$PersonIdentPackage_id}.xml";
		$filepath = $this->exchange_dir . $filename;

		$zglv = $xml->addChild('ZGLV');
		$zglv->addChild('DATA', date('Y-m-d'));
		$zglv->addChild('FILENAME', $filename);

		array_walk($item, 'ConvertFromUTF8ToWin1251');
		$person = $xml->addChild('PERSON');
		foreach ($item as $key => $val) {
			$person->addChild($key, $val);
		}

		$xmlfile = $xml->asXML();
		file_put_contents($filepath, $xmlfile);
		$this->textlog->add("Файл собран: $filepath");
	}

	/**
	 * Чтение файлов ответа и сохранение их в базе
	 */
	function PersonIdentRead() {

		$this->textlog->add('--- Запуск приема ---');

		foreach (glob($this->exchange_dir . "O*.xml") as $filename) {
			$this->textlog->add("Разбираем файл: $filename");
			preg_match("#O400057_01_(\d+).xml#is", $filename, $res);
			if (!is_array($res) || !count($res)) {continue;}
			$PersonIdentPackage_id = $res[1];
			$xml = simplexml_load_file($filename);
			rename($filename, str_replace('O4', 'RO4', $filename));
			if (!$xml || !isset($xml->PERSON) || !isset($xml->OTVET)) {return false;}
			$this->setPersonInfo($xml->OTVET, $xml->PERSON, $PersonIdentPackage_id);
		}

		$this->textlog->add('--- Окончание приема ---');

		return array(array('success' => true));
	}

	/**
	 * Сохраняем принятые данные
	 */
	function setPersonInfo($data, $person, $PersonIdentPackage_id) {
		$data = $this->getValidArray($data);
		$person = $this->getValidArray($person);
		$person_data = $this->getPersonId($person, $PersonIdentPackage_id);

		$this->PersonEvn_id = null;

		if (!$person_data) return false;

		$person_id = $person_data['Person_id'];
		$data['person'] = $person_data;

		// Человек не идентифицирован
		if (isset($data['EERP']) && $data['EERP'] == -1) {
			$this->setPersonErzStatus($person_id, 1);
			return array(array('success' => true));
		}

		// Обновляем полис
		$this->savePolisData($data, $person_id);

		// Сохраняем СНИЛС
		$this->setSnils($data, $person_id);

		// Сохраняем документ
		$this->setDocument($data, $person_id);

		// Устанавливаем статус
		$this->setPersonErzStatus($person_id, 2);

		// Обновляем периодику у события
		$this->updPersonEvn($person_data);

		return true;
	}

	/**
	 * Преобразуем объект simplexml в массив
	 */
	function getValidArray($data) {
		$res = array();
		foreach ((array)$data as $k => $v) {
			$res[$k] = (string)$v;
		}
		return $res;
	}

	/**
	 * Получаем id человека на основе данных запроса
	 */
	function getPersonId($data, $PersonIdentPackage_id) {
		return $this->getFirstRowFromQuery("
			select
				p.Person_id as \"Person_id\",
				p.PersonEvn_id as \"PersonEvn_id\",
				p.Server_id as \"Server_id\",
				ppp.Evn_id as \"Evn_id\"
			from v_PersonState p 
			inner join PersonIdentPackagePos ppp  on ppp.Person_id = p.Person_id
			where ppp.PersonIdentPackage_id = :PersonIdentPackage_id
		", array(
			'PersonIdentPackage_id' => $PersonIdentPackage_id
		));
	}

	/**
	 * Установка статауса
	 */
	function setPersonErzStatus($person_id, $status = null) {
		$sql = "update Person set Person_IsInErz = :status where Person_id = :Person_id";
		$this->db->query($sql, array(
			'Person_id' => $person_id,
			'status' => $status
		));
	}

	/**
	 * Сохраняем новые данные полиса
	 */
   function savePolisData($data, $person_id) {

		$query = "
			select
			pol.Polis_Ser as \"Polis_Ser\",
			pol.Polis_Num as \"Polis_Num\",
			pol.PolisType_id as \"PolisType_id\",
			pol.OmsSprTerr_id as \"OmsSprTerr_id\",
			pol.OrgSmo_id as \"OrgSmo_id\",
			pol.Polis_id as \"Polis_id\",
			to_char(pol.Polis_begDate, 'yyyy-mm-dd hh:mi:ss') as \"Polis_begDate\",
			to_char(pol.Polis_endDate, 'yyyy-mm-dd hh:mi:ss') as \"Polis_endDate\",
			p.Person_EdNum as \"Person_EdNum\",
			p.PersonEvn_id as \"PersonEvn_id\"
			from v_PersonState p
			inner join Polis pol  on pol.Polis_id = p.Polis_id
			where p.Person_id = :Person_id
            limit 1
		";

		$old_polis = $this->getFirstRowFromQuery($query, array(
			'Person_id' => $person_id
		));

		$OmsSprTerr_id = empty($data['ROKATO']) ? NULL : $this->getOMSSprTerrId($data['ROKATO']);
		$Polis_begDate = empty($data['RDBEG']) ? NULL : date('Y-m-d', strtotime($data['RDBEG']));
		$Polis_closeDate = empty($data['RDBEG']) ? NULL : date('Y-m-d', strtotime($data['RDBEG'] . "-1 days"));
		$Polis_endDate = empty($data['RDEND']) ? NULL : date('Y-m-d', strtotime($data['RDEND']));
		$PolisType_id = empty($data['ROPDOC']) ? NULL : $data['ROPDOC'];
		$OrgSmo_id = empty($data['RQ'])
			? (empty($data['RQOGRN']) ? NULL :  $this->getOrgSmoIdOGRN($data['RQOGRN']))
			: $this->getOrgSmoId($data['RQ']);
		$Polis_Ser = empty($data['RSPOL']) ? '' : $data['RSPOL'];
		$Polis_Num = empty($data['RNPOL']) ? '' : $data['RNPOL'];
		$PersonPolisEdNum = empty($data['RENP']) ? '' : $data['RENP'];

		$params = array(
			'PersonEvn_id' => null,
			'Server_id' => 0,
			'Person_id' => $person_id,
			'OmsSprTerr_id' => $OmsSprTerr_id,
			'PolisType_id' => $PolisType_id,
			'OrgSmo_id' => $OrgSmo_id,
			'Polis_Ser' => $Polis_Ser,
			'Polis_Num' => $Polis_Num,
			'Polis_begDate' => $Polis_begDate,
			'Polis_endDate' => $Polis_endDate,
			'PersonPolisEdNum_EdNum' => $PersonPolisEdNum
		);

		//  Сохраняем ЕНП, если изменился
		if(!empty($PersonPolisEdNum) && $PersonPolisEdNum != $old_polis['Person_EdNum']) {

            $query = "
                select
                    Error_Message as \"ErrMsg\"
                from  p_PersonPolisEdNum_ins
                    (
 				        Server_id := 0,
				        Person_id := :Person_id,
				        PersonPolisEdNum_insDT := :Polis_begDate,
				        PersonPolisEdNum_EdNum := :PersonPolisEdNum_EdNum,
				        pmUser_id := 1
                    )";


			$this->db->query($query, $params);
		}

		// Если серия и номер полиса не изменились - ничего не делаем
		if (
			isset($old_polis['Polis_id']) &&
			$Polis_Ser == $old_polis['Polis_Ser'] &&
			$Polis_Num == $old_polis['Polis_Num']
		) {
			return false;
		}

		// Если дата начала нового полиса меньше или равна дате начала старого полиса, обновляем старый полис, занося в него данные нового полиса
		if (!empty($old_polis['Polis_id']) && $Polis_begDate < $old_polis['Polis_begDate'] && (empty($params['Polis_endDate']) || $params['Polis_endDate']>=$params['Polis_begDate'])) {

             $query = "
                select
                    Error_Message as \"ErrMsg\",
                    PersonPolis_id as \"PersonEvn_id\"
                from p_PersonPolis_upd
                    (
 					PersonPolis_id := :PersonEvn_id,
					Server_id := :Server_id,
					Person_id := :Person_id,
					OmsSprTerr_id := :OmsSprTerr_id,
					PolisType_id := :PolisType_id,
					OrgSmo_id := :OrgSmo_id,
					Polis_Ser := :Polis_Ser,
					Polis_Num := :Polis_Num,
					Polis_begDate := :Polis_begDate,
					Polis_endDate := :Polis_endDate,
					pmUser_id := 1
                    )";


			$params['PersonEvn_id'] = $old_polis['PersonEvn_id'];
			$res = $this->db->query($query, $params);
			return $res;
		}

		// Иначе закрываем старый полис (если был)
		if ( isset($old_polis['Polis_id']) && $old_polis['Polis_id'] != NULL) {
			if (!empty($data['Polis_begDate'])) {
				$sql = "update Polis set Polis_endDate = :Polis_closeDate where Polis_id = :Polis_id";
				$queryParams = array(
					'Polis_id' => $old_polis['Polis_id'],
					'Polis_begDate' => $Polis_begDate,
					'Polis_endDate' => $Polis_endDate,
					'Polis_closeDate' => $Polis_closeDate
				);
				$this->db->query($sql, $queryParams);
			}
		}

		// Записываем новый
         $query = "
            select
                Error_Message as \"ErrMsg\",
                PersonPolis_id as \"PersonEvn_id\"
            from p_PersonPolis_ins
                (
 				PersonPolis_id := :PersonEvn_id,
				Server_id := :Server_id,
				Person_id := :Person_id,
				OmsSprTerr_id := :OmsSprTerr_id,
				PolisType_id := :PolisType_id,
				OrgSmo_id := :OrgSmo_id,
				Polis_Ser := :Polis_Ser,
				Polis_Num := :Polis_Num,
				Polis_begDate := :Polis_begDate,
				Polis_endDate := :Polis_endDate,
				pmUser_id := 1
                )";


		if(empty($params['Polis_endDate']) || $params['Polis_endDate']>=$params['Polis_begDate']){
			$res = $this->db->query($query, $params);
			$this->setPersonEvn($res);
		} else {
			$res = false;
		}

		return $res;
	}

	/**
	 * Получение OMSSprTerr_id по коду ОКАТО
	 */
	function getOMSSprTerrId($OMSSprTerr_OKATO) {
		return $this->getFirstResultFromQuery("
			select OMSSprTerr_id as \"OMSSprTerr_id\" from v_OMSSprTerr  where OMSSprTerr_OKATO = :OMSSprTerr_OKATO
		", array(
			'OMSSprTerr_OKATO' => $OMSSprTerr_OKATO
		));
	}

	/**
	 * Получение OrgSMO_id по коду
	 */
	function getOrgSmoId($OrgSMO_FCode) {
		return $this->getFirstResultFromQuery("
			select OrgSMO_id as \"OrgSMO_id\" from v_OrgSMO  where OrgSMO_FCode = :OrgSMO_FCode
		", array(
			'OrgSMO_FCode' => $OrgSMO_FCode
		));
	}

	/**
	 * Получение OrgSMO_id по ОГРН
	 */
	function getOrgSmoIdOGRN($Org_OGRN) {
		return $this->getFirstResultFromQuery("
			select OS.Org_id as \"Org_id\"
			from v_Org O 
			inner join v_OrgSMO OS  on OS.Org_id = O.Org_id
			where Org_OGRN = :Org_OGRN
		", array(
			'Org_OGRN' => $Org_OGRN
		));
	}

	/**
	 * Сохраняем СНИЛС
	 */
    function setSnils($data, $person_id) {

		if (empty($data['RSNILS'])) {
			return false;
		}

		$old = $this->getFirstResultFromQuery("
			select
                RTRIM(Coalesce(PersonSnils_Snils, '')) as \"Person_Snils\"
			from v_PersonSnils
			where Person_id = :Person_id
            limit 1
		", array('Person_id' => $person_id));

		if (empty($old) || str_replace(array('-', ' '), '', $data['RSNILS']) != str_replace(array('-', ' '), '', $old)) {

  
            $query = "
                select
                    Error_Message as \"ErrMsg\",
                    PersonSnils_id as \"PersonEvn_id\"
                from p_PersonSnils_ins
                    (
					        Server_id := :Server_id,
					        Person_id := :Person_id,
					        PersonSnils_Snils := :PersonSnils_Snils,
					        pmUser_id := 1
                    )";


			$res = $this->db->query($query, array(
				'Server_id' => $data['person']['Server_id'],
				'Person_id' => $data['person']['Person_id'],
				'PersonSnils_Snils' => str_replace(array('-', ' '), '', $data['RSNILS'])
			));

			$this->setPersonEvn($res);
			return $res;
		}

		return true;
	}


	/**
	 * Сохраняем документ
	 */
   function setDocument($data, $person_id) {

		if (empty($data['RDOCN'])) {
			return false;
		}

		$old = $this->getFirstRowFromQuery("
			select
				p.Document_Ser as \"Document_Ser\",
				p.Document_Num as \"Document_Num\"
			from v_PersonState p
			where p.Person_id = :Person_id
            limit 1
		", array('Person_id' => $person_id));

		if (empty($old) || $data['RDOCS'] != $old['Document_Ser'] || $data['RDOCN'] != $old['Document_Num']) {

  
            $query = "
                select
                    Error_Message as \"ErrMsg\",
                    PersonDocument_id as \"PersonEvn_id\"
                from p_PersonDocument_ins
                    (
  					Server_id := :Server_id,
					Person_id := :Person_id,
					DocumentType_id := :DocumentType_id,
					Document_Ser := :Document_Ser,
					Document_Num := :Document_Num,
					pmUser_id := 1
                    )";


			$res = $this->db->query($query, array(
				'Server_id' => $data['person']['Server_id'],
				'Person_id' => $data['person']['Person_id'],
				'DocumentType_id' => $this->getDocumentTypeId($data['RDOCTP']),
				'Document_Ser' => $data['RDOCS'],
				'Document_Num' => $data['RDOCN']
			));

			$this->setPersonEvn($res);
			return $res;
		}

		return true;
	}

	/**
	 * Получение DocumentType_id по коду
	 */
	function getDocumentTypeId($DocumentType_Code) {
		if (empty($DocumentType_Code)) return null;
		return $this->getFirstResultFromQuery("
			select DocumentType_id as \"DocumentType_id\" from v_DocumentType  where DocumentType_Code = :DocumentType_Code
		", array(
			'DocumentType_Code' => $DocumentType_Code
		));
	}


	/**
	 * обновляем PersonEvn_id по ходу выполнения
	 */
	function setPersonEvn($res) {
		if (is_object($res)) {
			$res = $res->result('array');
			if (
				count($res) &&
				isset($res[0]['PersonEvn_id']) &&
				$res[0]['PersonEvn_id'] > 0
			) {
				$this->PersonEvn_id = $res[0]['PersonEvn_id'];
			}
		}
	}

	/**
	 * обновляем PersonEvn_id в базе
	 */
	function updPersonEvn($person_data) {
		if (!empty($this->PersonEvn_id) && $person_data['Evn_id'] > 0) {
			$this->db->query("
				update Evn
				set PersonEvn_id = :PersonEvn_id
				where Evn_id = :Evn_id
			", array(
				'PersonEvn_id' => $this->PersonEvn_id,
				'Evn_id' => $person_data['Evn_id']
			));
		}
	}

}