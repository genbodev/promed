<?php defined('BASEPATH') or die ('No direct script access allowed');
/**
 * KalugaPersonIdent_model
 */
class KalugaPersonIdent_model extends swModel {

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
			select top {$this->max_packet}
				p.Person_id,
				ppp.PersonIdentPackagePos_id,
				p.Person_SurName,
				p.Person_FirName,
				p.Person_SecName,
				p.Sex_id,
				CONVERT(varchar(10),p.Person_Birthday,120) as Person_Birthday,
				dt.DocumentType_Code,
				p.Document_Ser,
				p.Document_Num,
				p.Person_EdNum,
				p.Person_SNILS
			from PersonIdentPackagePos ppp with(nolock)
			inner join v_PersonState p with(nolock) on ppp.Person_id = p.Person_id
			left join v_Document d with(nolock) on p.Document_id = d.Document_id
			left join v_DocumentType dt with(nolock) on dt.DocumentType_id = d.DocumentType_id
			where PersonIdentPackage_id is null
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
			declare @ErrCode int
			declare @ErrMsg varchar(400)
			declare @PersonIdentPackage_id bigint = null
			exec p_PersonIdentPackage_ins
				@PersonIdentPackage_id = @PersonIdentPackage_id output,
				@PersonIdentPackage_Name = 'PersonIdentPackage_Name',
				@PersonIdentPackage_begDate = :PersonIdentPackage_begDate,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @PersonIdentPackage_id as PersonIdentPackage_id, @ErrMsg as ErrMsg
		";
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
				p.Person_id,
				p.PersonEvn_id,
				p.Server_id,
				ppp.Evn_id
			from v_PersonState p with(nolock)
			inner join PersonIdentPackagePos ppp with(nolock) on ppp.Person_id = p.Person_id
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
			select top 1
			pol.Polis_Ser,
			pol.Polis_Num,
			pol.PolisType_id,
			pol.OmsSprTerr_id,
			pol.OrgSmo_id,
			pol.Polis_id,
			CONVERT(varchar(10),pol.Polis_begDate, 120) as Polis_begDate,
			CONVERT(varchar(10),pol.Polis_endDate, 120) as Polis_endDate,
			p.Person_EdNum,
			p.PersonEvn_id
			from v_PersonState p with(nolock)
			inner join Polis pol with (nolock) on pol.Polis_id = p.Polis_id
			where p.Person_id = :Person_id
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
				declare @ErrCode int
				declare @ErrMsg varchar(400)

				exec p_PersonPolisEdNum_ins
				@Server_id = 0,
				@Person_id = :Person_id,
				@PersonPolisEdNum_insDT = :Polis_begDate,
				@PersonPolisEdNum_EdNum = :PersonPolisEdNum_EdNum,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

				select @ErrMsg as ErrMsg
			";
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
				declare @ErrCode int
				declare @ErrMsg varchar(400)
				declare @Res bigint = :PersonEvn_id
				exec p_PersonPolis_upd
					@PersonPolis_id = @Res output,
					@Server_id = :Server_id,
					@Person_id = :Person_id,
					@OmsSprTerr_id = :OmsSprTerr_id,
					@PolisType_id = :PolisType_id,
					@OrgSmo_id = :OrgSmo_id,
					@Polis_Ser = :Polis_Ser,
					@Polis_Num = :Polis_Num,
					@Polis_begDate = :Polis_begDate,
					@Polis_endDate = :Polis_endDate,
					@pmUser_id = 1,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as PersonEvn_id, @ErrMsg as ErrMsg
			";

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
			declare @ErrCode int
			declare @ErrMsg varchar(400)
			declare @Res bigint = :PersonEvn_id
			exec p_PersonPolis_ins
				@PersonPolis_id = @Res output,
				@Server_id = :Server_id,
				@Person_id = :Person_id,
				@OmsSprTerr_id = :OmsSprTerr_id,
				@PolisType_id = :PolisType_id,
				@OrgSmo_id = :OrgSmo_id,
				@Polis_Ser = :Polis_Ser,
				@Polis_Num = :Polis_Num,
				@Polis_begDate = :Polis_begDate,
				@Polis_endDate = :Polis_endDate,
				@pmUser_id = 1,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output

			select @Res as PersonEvn_id, @ErrMsg as ErrMsg
		";
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
			select OMSSprTerr_id from v_OMSSprTerr with (nolock) where OMSSprTerr_OKATO = :OMSSprTerr_OKATO
		", array(
			'OMSSprTerr_OKATO' => $OMSSprTerr_OKATO
		));
	}

	/**
	 * Получение OrgSMO_id по коду
	 */
	function getOrgSmoId($OrgSMO_FCode) {
		return $this->getFirstResultFromQuery("
			select OrgSMO_id from v_OrgSMO with (nolock) where OrgSMO_FCode = :OrgSMO_FCode
		", array(
			'OrgSMO_FCode' => $OrgSMO_FCode
		));
	}

	/**
	 * Получение OrgSMO_id по ОГРН
	 */
	function getOrgSmoIdOGRN($Org_OGRN) {
		return $this->getFirstResultFromQuery("
			select OS.Org_id
			from v_Org O with (nolock)
			inner join v_OrgSMO OS with (nolock) on OS.Org_id = O.Org_id
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
			select top 1 RTRIM(ISNULL(PersonSnils_Snils, '')) as Person_Snils
			from v_PersonSnils with (nolock)
			where Person_id = :Person_id
		", array('Person_id' => $person_id));

		if (empty($old) || str_replace(array('-', ' '), '', $data['RSNILS']) != str_replace(array('-', ' '), '', $old)) {

			$query = "
				declare @ErrCode int,
				 	@ErrMsg varchar(400),
					@Res bigint;

				exec p_PersonSnils_ins
					@PersonSnils_id = @Res output,
					@Server_id = :Server_id,
					@Person_id = :Person_id,
					@PersonSnils_Snils = :PersonSnils_Snils,
					@pmUser_id = 1,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as PersonEvn_id, @ErrMsg as ErrMsg
			";

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

			select top 1
				p.Document_Ser,
				p.Document_Num
			from v_PersonState p with (nolock)
			where p.Person_id = :Person_id
		", array('Person_id' => $person_id));

		if (empty($old) || $data['RDOCS'] != $old['Document_Ser'] || $data['RDOCN'] != $old['Document_Num']) {

			$query = "
				declare @ErrCode int,
					@ErrMsg varchar(400),
					@Res bigint;

				exec p_PersonDocument_ins
					@PersonDocument_id = @Res output,
					@Server_id = :Server_id,
					@Person_id = :Person_id,
					@DocumentType_id = :DocumentType_id,
					@Document_Ser = :Document_Ser,
					@Document_Num = :Document_Num,
					@pmUser_id = 1,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as PersonEvn_id, @ErrMsg as ErrMsg
			";

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
			select DocumentType_id from v_DocumentType with(nolock) where DocumentType_Code = :DocumentType_Code
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
