<?php defined('BASEPATH') or die('No direct script access allowed');

/**
 * PromedWeb - The New Generation of Medical Statistic Software
 *
 * @package      PromedWeb
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @link         http://swan.perm.ru/PromedWeb
 */

require_once('PersonRegisterBase_model.php');
/**
 * Модель объектов "Запись регистра по ВЗН (7 нозологиям)"
 *
 * @package      PersonRegister
 * @access       public
 * @copyright    Copyright (c) 2009-2015 Swan Ltd.
 * @author       Александр Пермяков
 * @version      02.2015
 *
 * @property string $сode № регистровой записи. Целое число, 13
 */
class PersonRegisterNolos_model extends PersonRegisterBase_model
{
	protected $_personRegisterTypeSysNick = 'nolos'; // всегда перекрывать
	protected $_userGroupCode = 'VznRegistry'; // можно не перекрывать, если задано стандартно, например "NolosRegistry" для типа регистра "nolos"
	protected $_PersonRegisterType_id = 49; // если не для всех регионов, то нельзя перекрывать
	protected $_exportLimit = 3145728; // 3 Мб, рекомендуется создавать файлы не больше 2-3 Мб, но не более 8

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->_setScenarioList(array(
			self::SCENARIO_AUTO_CREATE,
			self::SCENARIO_DO_SAVE,
			self::SCENARIO_SET_ATTRIBUTE,
			self::SCENARIO_LOAD_EDIT_FORM,
			self::SCENARIO_DELETE,
			'include',//включение в регистр по извещению/направлению
			//'create', // Добавление в регистр оператором в поточном вводе
			'except',//исключение из регистра
			'back',//возвращение в регистр
			'export',
		));
	}

	/**
	 * @return array Список кодов групп пользователей, имеющих доступ для выгрузки в федеральный регистр
	 */
	function getExportOperatorGroupCodeList()
	{
		return array(
			$this->_userGroupCode, // Регистр по ВЗН (7 нозологиям)
		);
	}

	/**
	 * Возвращает массив описаний всех используемых атрибутов объекта в формате ключ => описание
	 * @return array
	 */
	static function defAttributes()
	{
		$arr = parent::defAttributes();
		$arr[self::ID_KEY]['label'] = 'Запись регистра по ВЗН';
		$arr['diag_id']['save'] = 'trim|required';
		$arr['evnnotifybase_id']['save'] = 'trim|required';
		return $arr;
	}

	/**
	 * Определение правил для входящих параметров
	 * @param string $name
	 * @return array
	 */
	function getInputRules($name)
	{
		$rules = parent::getInputRules($name);
		switch ($name) {
			case 'loadEvnVKList':
				$rules = array(
					'Person_id' => array(
						'field' => 'Person_id',
						'label' => 'Человек',
						'rules' => 'trim|required',
						'type' => 'id'
					),
				);
				break;
			case 'except':
				$rules[] = array(
					'field' => 'EvnVK_id',
					'label' => 'Врачебная комиссия',
					'rules' => '',
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'Notify_Num',
					'label' => 'Номер извещения',
					'rules' => '',
					'type' => 'string'
				);
				$rules[] = array(
					'field' => 'Notify_setDate',
					'label' => 'Дата извещения',
					'rules' => '',
					'type' => 'date'
				);
				$rules[] = array(
					'field' => 'EvnNotifyRegister_OutComment',
					'label' => 'Комментарий',
					'rules' => '',
					'type' => 'string'
				);
				$rules[] = array(
					'field' => 'Person_id',
					'label' => 'Пациент',
					'rules' => '',
					'type' => 'id'
				);
				$rules[] = array(
					'field' => 'Diag_id',
					'label' => 'Диагноз',
					'rules' => '',
					'type' => 'id'
				);
				break;
			case 'back':
				$rules['EvnNotifyBase_id']['rules'] = 'trim|required';
				break;
			case 'export':
				$rules['ExportType']['rules'] = 'trim|required';
				$rules['Lpu_eid'] = array(
					'field' => 'Lpu_eid',
					'label' => 'МО',
					'rules' => 'trim',
					'type' => 'id'
				);
				break;
		}
		return $rules;
	}

	/**
	 * Извлечение значений параметров модели из входящих параметров, переданных из контроллера
	 * @param array $data
	 * @throws Exception
	 */
	function setParams($data)
	{
		parent::setParams($data);
		if (in_array($this->scenario, array('except'))) {
			$this->_params['EvnVK_id'] = isset($data['EvnVK_id']) ? $data['EvnVK_id'] : null ;
			$this->_params['Notify_Num'] = isset($data['Notify_Num']) ? $data['Notify_Num'] : null ;
			$this->_params['Notify_setDate'] = isset($data['Notify_setDate']) ? $data['Notify_setDate'] : null ;
			$this->_params['EvnNotifyRegister_OutComment'] = isset($data['EvnNotifyRegister_OutComment']) ? $data['EvnNotifyRegister_OutComment'] : null;
		}
		if (in_array($this->scenario, array('export'))) {
			$this->_params['LastExport'] = isset($data['LastExport']) ? $data['LastExport'] : null ;
			$this->_params['OutDir'] = isset($data['OutDir']) ? $data['OutDir'] : null ;
			$this->_params['Lpu_eid'] = isset($data['Lpu_eid']) ? $data['Lpu_eid'] : null ;
			$this->_params['ExportMod'] = isset($data['ExportMod']) ? $data['ExportMod'] : null ;
			$this->_params['ExportType'] = isset($data['ExportType']) ? $data['ExportType'] : null ;
			$this->_params['ExportDate'] = isset($data['ExportDate']) ? $data['ExportDate'] : null ;
			$this->_params['BegDate'] = isset($data['BegDate']) ? $data['BegDate'] : null ;
			$this->_params['EndDate'] = isset($data['EndDate']) ? $data['EndDate'] : null ;
		}
	}

	/**
	 * Проверка корректности данных модели для указанного сценария
	 * @throws Exception
	 */
	protected function _validate()
	{
		/*if ('except' == $this->scenario) {
			if (empty($this->_params['EvnVK_id'])) {
				throw new Exception('Нужно указать Врачебная комиссия');
			}
		}*/

		parent::_validate();
	}

	/**
	 * Загружаем список протоколов ВК для направлений/извещений
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	public function loadEvnVKList($data)
	{
		$params = array('Person_id' => $data['Person_id']);
		$result = $this->db->query("
			select
				v_EvnVK.EvnVK_id as \"EvnVK_id\",
				lpu.Lpu_Nick
					|| ' №' || coalesce(v_EvnVK.EvnVK_NumProtocol,'0')
					|| ' от ' || to_char(v_EvnVK.EvnVK_setDate, 'dd.mm.yyyy')
				as \"EvnVK_protocol\",
				v_EvnVK.Lpu_id as \"Lpu_id\",
				v_EvnVK.PersonEvn_id as \"PersonEvn_id\",
				v_EvnVK.Server_id as \"Server_id\"
			from v_EvnVK
				inner join v_Lpu_all lpu on lpu.Lpu_id = v_EvnVK.Lpu_id
			where v_EvnVK.Person_id = :Person_id
			order by v_EvnVK.EvnVK_setDate DESC
			limit 100
		", $params);
		if (false == is_object($result)) {
			throw new Exception('При запросе к БД возникла ошибка', 500);
		}
		return $result->result('array');
	}

	/**
	 * Создание объекта «Извещение об исключении из регистра»
	 * @throws Exception
	 */
	protected function _createEvnNotifyRegisterExcept()
	{
		if ('except' == $this->scenario) {
			//при сохранении формы исключения записи из регистра
			$this->load->model('EvnNotifyRegister_model');
			// могут быть региональные модели типа ufa_EvnNotifyRegister_model, наследующие EvnNotifyRegister_model
			$className = get_class($this->EvnNotifyRegister_model);
			/**
			 * @var EvnNotifyRegister_model $instance
			 */
			$instance = new $className('nolos', 3);
			$instance->disableLpuIdChecks = true; //отключение проверки идентификатора МО
			$res = $instance->doSave(array(
				'session' => $this->sessionParams,
				'scenario' => self::SCENARIO_AUTO_CREATE,
				'EvnNotifyRegister_setDate' => ($this->_params['Notify_setDate'] ? $this->_params['Notify_setDate'] : $this->disDate->format('Y-m-d')),
				'PersonRegister_id' => $this->id,
				'PersonRegisterOutCause_id' => $this->PersonRegisterOutCause_id,
				'EvnVK_id' => $this->_params['EvnVK_id'], // только для ВЗН
				'Lpu_did' => $this->Lpu_did,
				'MedPersonal_id' => $this->MedPersonal_did,
				'Person_id' => $this->Person_id,
				'PersonEvn_id' => $this->personData['PersonEvn_id'],
				'Server_id' => $this->personData['Server_id'],
				'PersonRegisterType_id' => 49,
				'EvnNotifyRegister_Num' => $this->_params['Notify_Num'],
				'EvnNotifyRegister_OutComment' => $this->_params['EvnNotifyRegister_OutComment']
			), false);
			$instance->disableLpuIdChecks = false;
			if (!empty($res['Error_Msg'])) {
				// отменяем исключение
				throw new Exception($res['Error_Msg'], 500);
			}
		}
	}

	/**
	 * Контроль направления/извещения включения в регистр
	 * @throws Exception
	 */
	protected function _checkEvnNotifyBase()
	{
		if ( empty($this->EvnNotifyBase_id) ) {
			throw new Exception('Нужно указать Направление на включение в регистр');
		}
		if ($this->_isAttributeChanged('EvnNotifyBase_id')) {
			if ( false == $this->isNewRecord ) {
				throw new Exception('Нельзя изменить Направление на включение в регистр');
			}
		}
	}

	/**
	 * Имя шаблона для экспорта записей регистра этого типа
	 * @return string
	 */
	function getExportTemplateName()
	{
		if ($this->_params['ExportMod'] == '06-FR') {
			return "vzn_register_06_FR";
		}
		return "vzn_register_04_FR";
	}

	/**
	 * Выгрузка в федеральный регистр регионального сегмента регистра по ВЗН
	 * @param array $data
	 * @return array
	 */
	function doExportOld($data)
	{
		set_time_limit(0); //обязательно, иначе на больших объемах выгружаемых данных до конца не выполнится
		try {
			if (!empty($data['ExportMod']) && $data['ExportMod'] == '03-FR') {
				// пока печать в html, потом может как сохранение файла pdf
				$export_data = $this->_loadExportData($data);
				if ( empty($export_data) ) {
					throw new Exception('При указанных параметрах нет записей');
				}
				$this->load->library('parser');
				array_walk_recursive($export_data, 'ConvertFromWin1251ToUTF8');
				$this->_saveResponse['html'] = $this->parser->parse('person_register/Nolos_03_FR', array('item_arr' => $export_data), true);
				return $this->_saveResponse;
			}
			//echo $this->exportLimit; exit();
			if (empty($this->exportLimit)) {
				throw new Exception('Ограничение размера xml-файла для выгрузки не определено');
			}
			// каталог в котором лежат выгружаемые файлы
			$out_dir = "re_xml_".time();
			if (!file_exists($this->exportPath)) {
				if (false == mkdir( $this->exportPath )) {
					throw new Exception('Не удалось создать корневую папку для экспортируемых файлов регистра');
				}
			}
			if (false == mkdir( $this->exportPath.$out_dir )) {
				throw new Exception('Не удалось создать папку для экспортируемых файлов регистра');
			}

			$this->ResultFileStrings = array();

			$this->_saveResponse['ExportErrorArray'] = array();
			$this->_saveResponse['ExportErrorArray'][] = array(
				'Text' => 'Начало выполнения запроса к базе данных',
				'Time' => date('H:i:s'),
			);
			$export_data = $this->_loadExportData($data);
			if (count($this->ResultFileStrings) > 0) {
				$link = $this->exportPath.$out_dir.'/result.txt';
				file_put_contents($link, implode("\n", $this->ResultFileStrings));
				$this->_saveResponse['ResultLink'] = $link;
			}
			if ( empty($export_data) ) {
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => 'Окончание выгрузки. При указанных параметрах нет записей для выгрузки',
					'Time' => date('H:i:s'),
				);
				return $this->_saveResponse;
			}
			$this->_saveResponse['ExportErrorArray'][] = array(
				'Text' => 'Получили все данные из базы данных',
				'Time' => date('H:i:s'),
			);

			$template = $this->exportTemplateName;
			$this->load->library('parser');
			array_walk_recursive($export_data, 'ConvertFromWin1251ToUTF8');

			$j=1;   //счётчик частей файлов
			$files_array = array();     //Массив файлов выгрузки
			$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r\n<root>\n";
			$short_file_name = strtoupper($template) . '_'.$j;
			$xml_file_name = $this->exportPath.$out_dir."/".$short_file_name.".xml";

			//Записываем в файл построчно и смотрим сколько записали на очередной итерации, по достижению лимита создаём новый файл и пишем в него
			foreach ($export_data as $row) {
				$xml .= $this->parser->parse('export_xml/'.$template, $row, true). "\n";
				if (file_put_contents($xml_file_name, $xml) > $this->exportLimit) {
					$xml .= "</root>";
					$xml = str_replace('&', '&amp;', $xml);
					file_put_contents($xml_file_name, $xml);
					$files_array[$xml_file_name] = $short_file_name;
					$xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r\n<root>\n";
					$j++;
					$short_file_name = strtoupper($template) . '_'.$j;
					$xml_file_name = $this->exportPath.$out_dir."/".$short_file_name.".xml";
				}
			}

			$xml .= "</root>";
			$xml = str_replace('&', '&amp;', $xml);
			file_put_contents($xml_file_name, $xml);
			$files_array[$xml_file_name] = $short_file_name;

			$file_zip_sign = $short_file_name;
			$file_zip_name = $this->exportPath.$out_dir."/".$file_zip_sign.".zip";
			$zip = new ZipArchive();
			$zip->open($file_zip_name, ZIPARCHIVE::CREATE);
			foreach ($files_array as $key => $value) {
				$zip->AddFile( $key, $value . ".xml" );
			}
			$zip->close();
			$this->_saveResponse['ExportErrorArray'][] = array(
				'Text' => 'Создан файл архива реестра',
				'Time' => date('H:i:s'),
			);

			foreach ($files_array as $key => $value) {
				unlink($key);
			}

			if (file_exists($file_zip_name)) {
				$this->_saveResponse['Link'] = $file_zip_name;
			} else {
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => 'Ошибка создания файла архива реестра',
					'Time' => date('H:i:s'),
				);
			}
		} catch (Exception $e) {
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
		}
		return $this->_saveResponse;
	}

	/**
	 * Выгрузка в федеральный регистр регионального сегмента регистра по ВЗН
	 * @param array $data
	 * @return array
	 */
	function doExport($data) {
		set_time_limit(0);

		$this->load->library('parser');

		$this->beginTransaction();

		try {
			$out_dir = "re_xml_".time();
			if (!file_exists($this->exportPath)) {
				if (false == mkdir( $this->exportPath )) {
					throw new Exception('Не удалось создать корневую папку для экспортируемых файлов регистра');
				}
			}
			if (false == mkdir($this->exportPath.$out_dir)) {
				throw new Exception('Не удалось создать папку для экспортируемых файлов регистра');
			}
			$data['OutDir'] = $this->exportPath.$out_dir;

			if ($data['ExportMod'] == 'RegisterRecords') {
				$data['LastExport'] = $this->getFirstResultFromQuery("
					select
						max(PRE.PersonRegisterExport_updDT) as \"LastExport\"
					from v_PersonRegisterExport PRE
						inner join v_PersonRegister PR on PR.PersonRegister_id = PRE.PersonRegister_id
						inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
					where PRT.PersonRegisterType_SysNick like 'nolos'
					limit 1
				", array(), true);
				if ($data['LastExport'] === false) {
					throw new Exception('Ошибка при получении даты последнего экспорта');
				}

				$this->setScenario('export');
				$this->setParams($data);

				$patientsResp = $this->exportRegisterPatients();
				$recordsResp = $this->exportRegisterRecords();
				$resp = array(
					'file_name' => 'vzn_register',
					'files_array' => array_merge($patientsResp['files_array'], $recordsResp['files_array']),
					'result_strings' => array_merge($patientsResp['result_strings'], $recordsResp['result_strings']),
				);
			} else if ($data['ExportMod'] == 'Recepts') {
				$this->setScenario('export');
				$this->setParams($data);

				$resp = $this->exportRecepts();
			}

			$file_zip_path = null;
			if (count($resp['files_array']) > 0) {
				$file_zip_path = $data['OutDir']."/".$resp['file_name'].".zip";
				$zip = new ZipArchive();
				$zip->open($file_zip_path, ZIPARCHIVE::CREATE);
				foreach ($resp['files_array'] as $name => $path) {
					$zip->AddFile($path, $name);
				}
				$zip->close();

				foreach($resp['files_array'] as $name => $path) {
					unlink($path);
				}
			}

			$file_result_path = null;
			if (count($resp['result_strings']) > 0) {
				$file_result_path = $data['OutDir']."/result.txt";
				file_put_contents($file_result_path, implode("\n", $resp['result_strings']));
			}

			$this->_saveResponse['Link'] = $file_zip_path;
			$this->_saveResponse['ResultLink'] = $file_result_path;
		} catch (Exception $e) {
			$this->rollbackTransaction();
			$this->_saveResponse['Error_Msg'] = $e->getMessage();
		}
		$this->commitTransaction();
		return $this->_saveResponse;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function exportRegisterPatients() {
		$filters = "";
		if ($this->_params['ExportType'] == 2) {
			$filters .= " and ENR.EvnNotifyRegister_id is not null";
			$filters .= " and (PRE.PersonRegisterExport_id is null or PS.PersonState_updDT >= (select dt from mv))";
		}

		$params = array(
			'lastExport' => $this->_params['LastExport'],
		);
		$query = "
			-- addit with
			with mv as (
				select
					:lastExport::timestamp as dt
			)
			--end addit with
			select
				-- select
				distinct
				PS.Person_id as \"id\",
				case
					when PRE.PersonRegisterExport_updDT < (select dt from mv) then 'update'
					else 'create'
				end as \"type\",
				PS.Person_Snils as \"snils\",
				PS.Person_SurName as \"lastName\",
				PS.Person_FirName as \"firstName\",
				PS.Person_SecName as \"patronymic\",
				Sex.Sex_fedid as \"gender\",
				to_char(PS.Person_BirthDay, 'yyyy-mm-dd') as \"birthDate\",
				OJ.Org_Nick as \"workPlace\",
				CitizenShip.FRMR_id as \"citizenShipId\",
				LSV.LegalStatusVZN_Code as \"noresidentStatusId\",
				PDG.PersonDecreedGroup_Code as \"decreedGroupId\",
				SS.SocStatus_id as \"socStatusId\",
				D.Document_Ser as \"documentSerial\",
				D.Document_Num as \"documentNumber\",
				to_char(D.Document_begDate, 'yyyy-mm-dd') as \"documentPassDate\",
				OD.Org_Nick as \"documentPassOrg\",
				DT.Frmr_id as \"documentId\"
				-- end select
			from 
				-- from
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
				inner join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Sex Sex on Sex.Sex_id = PS.Sex_id
				left join v_Job Job on Job.Job_id = PS.Job_id
				left join v_Org OJ on OJ.Org_id = Job.Org_id
				left join nsi.v_SocStatusLink SSL on SSL.SocStatus_did = PS.SocStatus_id
				left join nsi.v_SocStatus SS on SS.SocStatus_id = SSL.SocStatus_nid
				left join v_KLCountry CitizenShip on CitizenShip.KLCountry_id = PS.KLCountry_id
				left join v_LegalStatusVZN LSV on LSV.LegalStatusVZN_id = PS.LegalStatusVZN_id
				left join v_PersonDecreedGroup PDG on PDG.PersonDecreedGroup_id = PR.PersonDecreedGroup_id
				left join v_Document D on D.Document_id = PS.Document_id
				left join v_OrgDep OD on OD.OrgDep_id = D.OrgDep_id
				left join v_DocumentType DT on DT.DocumentType_id = D.DocumentType_id
				left join lateral(
					select PRE.*
					from v_PersonRegisterExport PRE
					where PRE.PersonRegister_id = PR.PersonRegister_id
					order by PRE.PersonRegisterExport_updDT desc
					limit 1
				) PRE on true
				left join lateral(
					select ENR.*
					from v_EvnNotifyRegister ENR
					where ENR.PersonRegister_id = PR.PersonRegister_id
					and ENR.EvnNotifyRegister_insDT >= (select dt from mv)
					and (
						ENR.NotifyType_id = 1
						or (ENR.NotifyType_id = 2 and PR.PersonRegister_disDate is null and PR.PersonRegister_updDT >= (select dt from mv))
						or (ENR.NotifyType_id = 3 and PR.PersonRegister_disDate is not null)
					)
					order by ENR.EvnNotifyRegister_insDT desc
					limit 1
				) ENR on true
				-- end from
			where
				-- where
				PRT.PersonRegisterType_SysNick = 'nolos' {$filters}
				-- end where
			order by
				-- order by
				PS.Person_id
				-- end order by
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных пациентов из регистра по ВЗН');
		}

		$patients = array();
		$patientsIds = array();
		foreach($resp as $patient) {
			$id = $patient['id'];
			$patientsIds[] = $id;
			$patients[$id] = $patient;
			$patients[$id]['relatives'] = array();
		}

		$patientsIds = array_unique($patientsIds);

		if (count($patientsIds) > 0) {
			$patientsIds_str = implode(',', $patientsIds);
			$params = array();
			$query = "
				select
					FR.Person_id as \"patientId\",
					FR.Person_cid as \"relativeId\",
					PS.Person_SurName as \"lastName\",
					PS.Person_FirName as \"firstName\",
					PS.Person_SecName as \"patronymic\",
					'+7'||PS.Person_Phone as \"phone\",
					Town.KLArea_AOID as \"addressAoidArea\",
					Street.KLStreet_AOID as \"addressAoidStreet\",
					null as \"addressHouseid\",
					A.KLRgn_id as \"addressRegion\",
					Town.KLArea_Name as \"addressAreaName\",
					TownSocr.KLSocr_Nick as \"addressPrefixArea\",
					Street.KLStreet_Name as \"addressStreetName\",
					StreetSocr.KLSocr_Nick as \"addressPrefixStreet\",
					nullif(rtrim(coalesce('д'||nullif(A.Address_House, ''), '')
						||' '||coalesce('к'||nullif(A.Address_Corpus, ''), '')), '')
					as \"addressHouse\",
					nullif(rtrim(A.Address_Flat), '') as \"addressFlat\"
				from
					v_FamilyRelation FR
						inner join v_PersonState PS on PS.Person_id = FR.Person_cid
						left join v_Address A on A.Address_id = coalesce(PS.PAddress_id, PS.UAddress_id)
						left join v_KLArea Town on Town.KLArea_id = coalesce(A.KLTown_id, A.KLCity_id)
						left join v_KLSocr TownSocr on TownSocr.KLSocr_id = Town.KLSocr_id
						left join v_KLStreet Street on Street.KLStreet_id = A.KLStreet_id
						left join v_KLSocr StreetSocr on StreetSocr.KLSocr_id = Street.KLSocr_id
				where
					FR.Person_id in ({$patientsIds_str})
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception('Ошибка при получении данных о родственниках пациентов из регистра по ВЗН');
			}

			foreach ($resp as $relative) {
				$patientId = $relative['patientId'];
				$patients[$patientId]['relatives'][] = $relative;
			}
		}

		$tpl = 'vzn_register_patient';
		$tpl_r = 'vzn_register_patient_record';

		$all_count = count($patients);
		$in_file_count = 0;
		$recepts = array();
		$error_strings = array();

		$required = array('snils','lastName','firstName','gender','birthDate','citizenShipId');

		foreach($patients as $id => $patient) {
			$hasErrors = false;
			foreach($patient as $key => $value) {
				if (in_array($key, $required) && empty($value)) {
					$hasErrors = true;
					$error_strings[] = "Пациент {$id}, поле {$key}";
				}
			}
			if ($hasErrors) {
				unset($patients[$id]);
			}
		}

		$in_file_count = count($patients);

		$file_name = mb_strtolower($tpl."s");
		$out_dir = $this->_params['OutDir'];

		$file_number = 1;
		$files_array = array();

		$openXml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r\n<records>\n";
		$closeXml = "</records>";

		$xml = $openXml;
		foreach(array_values($patients) as $index => $patient) {
			$tplParams = array(
				'type' => $patient['type'],
				'snils' => $patient['snils'],
				'patient' => $this->parser->parse('export_xml/'.$tpl, $patient, true),
			);
			$xml .= $this->parser->parse('export_xml/'.$tpl_r, $tplParams, true)."\n";

			if (strlen($xml) > $this->exportLimit || $index == count($patients)-1) {
				$xml .= $closeXml;
				$xml_file_name = $file_name."_".$file_number.".xml";
				$xml_file_path = $out_dir."/".$xml_file_name;
				file_put_contents($xml_file_path, $xml);

				$xml = $openXml;
				$file_number++;
				$files_array[$xml_file_name] = $xml_file_path;
			}
		}

		$result_strings = array(
			date('d.m.Y H:i'),
			"Всего пациентов  - {$all_count}",
			"Включено в файл выгрузки – {$in_file_count}",
		);
		if (count($error_strings) > 0) {
			$result_strings[] = "Ошибки:";
			$result_strings = array_merge($result_strings, $error_strings);
		}

		return array(
			'file_name' => $file_name,
			'files_array' => $files_array,
			'result_strings' => $result_strings,
		);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function exportRegisterRecords() {
		$filters = "";
		if ($this->_params['ExportType'] == 2) {
			$filters .= " and ENR.EvnNotifyRegister_id is not null";
		}

		$params = array(
			'Region_id' => $this->regionNumber,
			'lastExport' => $this->_params['LastExport'],
		);
		$query = "
			-- addit with
			with mv as (
				select
					:lastExport::timestamp as dt
			)
			select
				-- select
				PR.PersonRegister_id as \"id\",
				PS.Person_id as \"patientId\",
				case
					when ENR.NotifyType_id in (2,3) and PR.PersonRegister_insDT < (select dt from mv) and PRE.PersonRegisterExport_updDT < (select dt from mv) 
					then 'update' else 'create'
				end as \"type\",
				PS.Person_Snils as \"snils\",
				nullif(iOID.PassportToken_tid, '-1') as \"moId\",
				to_char(PR.PersonRegister_setDate, 'yyyy-mm-dd') as \"includeDate\",
				to_char(PR.PersonRegister_disDate, 'yyyy-mm-dd') as \"excludeDate\",
				PR.PersonRegister_Code as \"registryNumber\",		-- todo: check empty
				null as \"birthLastName\",
				PolisType.PolisType_CodeF008 as \"policTypeId\",
				nullif(Polis.Polis_Ser, '') as \"policSerial\",
				Polis.Polis_Num as \"policNumber\",
				OS.OrgSMO_VZNCode as \"imcCode\",
				to_char(PS.Person_deadDT, 'yyyy-mm-dd') as \"deathDate\",
				case when exists(
					select *
					from v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					where PP.Person_id = PS.Person_id and PP.PersonPrivilege_endDate is null and PT.ReceptFinance_id = 1
				)
				then 'true' else 'false' end as \"isInclRegistry\",
				case when exists(
					select *
					from v_PersonPrivilege PP
					inner join v_PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
					where PP.Person_id = PS.Person_id and PP.PersonPrivilege_endDate is null and PT.ReceptFinance_id = 2
				)
				then 'true' else 'false' end as \"isDrugSupply\",
				D.Diag_Code as \"desease\",
				null as \"disabilityGroupId\",
				case
					when PR.PersonRegisterOutCause_id is not null then 3
					when ENR.NotifyType_id = 2 and PR.PersonRegister_insDT < (select dt from mv) and PRE.PersonRegisterExport_updDT < (select dt from mv) then 2
					else 1
				end as \"registryOperationId\",
				case 
					when OC.PersonRegisterOutCause_SysNick = 'Death' then 1
					when OC.PersonRegisterOutCause_SysNick = 'OutFromRF' then 3
					when PR.PersonRegisterOutCause_id is not null then 4
				end as \"excludeReasonId\",
				nullif(dOID.PassportToken_tid, '-1') as \"excludeMoId\",
				left(Region.KLAdr_Ocatd, 2) as \"territoryId\",
				MZHead.Person_SurName||' '||MZHead.Person_FirName||coalesce(' '||MZHead.Person_SecName,'') as \"signedPerson\"
				-- end select
			from 
				-- from
				v_PersonRegister PR
				inner join v_PersonRegisterType PRT on PRT.PersonRegisterType_id = PR.PersonRegisterType_id
				inner join v_PersonState PS on PS.Person_id = PR.Person_id
				left join v_Polis Polis on Polis.Polis_id = PS.Polis_id
				left join v_PolisType PolisType on PolisType.PolisType_id = Polis.PolisType_id
				left join v_OrgSmo OS on OS.OrgSmo_id = Polis.OrgSmo_id
				left join v_Diag D on D.Diag_id = PR.Diag_id
				left join fed.PassportToken iOID on iOID.Lpu_id = PR.Lpu_iid
				left join fed.PassportToken dOID on dOID.Lpu_id = PR.Lpu_did
				left join v_KLArea Region on Region.KLArea_id = :Region_id
				left join v_PersonRegisterOutCause OC on OC.PersonRegisterOutCause_id = PR.PersonRegisterOutCause_id
				left join lateral(
					select O.*
					from v_Org O
					inner join v_OrgType OT on OT.OrgType_id = O.OrgType_id
					where OT.OrgType_Code = 13 and O.Org_Nick like 'Минздрав' and O.Region_id = Region.KLArea_id
					and exists(select * from v_OrgHead where Org_id = O.Org_id and OrgHeadPost_id = 12)
					order by O.Org_begDate desc
					limit 1
				) MZ on true
				left join lateral(
					select PS.*
					from v_OrgHead OH
					inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where OH.Org_id = MZ.Org_id and OH.OrgHeadPost_id = 12
					order by OH.OrgHead_CommissDate desc
					limit 1
				) MZHead on true
				left join lateral(
					select PRE.*
					from v_PersonRegisterExport PRE
					where PRE.PersonRegister_id = PR.PersonRegister_id
					order by PRE.PersonRegisterExport_updDT desc
					limit 1
				) PRE on true
				left join lateral(
					select ENR.*
					from v_EvnNotifyRegister ENR
					where ENR.PersonRegister_id = PR.PersonRegister_id
					and ENR.EvnNotifyRegister_insDT >= (select dt from mv)
					and (
						ENR.NotifyType_id = 1
						or (ENR.NotifyType_id = 2 and PR.PersonRegister_disDate is null and PR.PersonRegister_updDT >= (select dt from mv))
						or (ENR.NotifyType_id = 3 and PR.PersonRegister_disDate is not null)
					)
					order by ENR.EvnNotifyRegister_insDT desc
					limit 1
				) ENR on true
				-- end from
			where
				-- where
				PRT.PersonRegisterType_SysNick = 'nolos' {$filters}
				-- end where
			order by
				-- order by
				PR.PersonRegister_id
				-- end order by
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных записей из регистра по ВЗН');
		}


		$patientsIds = array();
		$patientsRecords = array();
		foreach($resp as $record) {
			$recordId = $record['id'];
			$patientId = $record['patientId'];
			$patientsIds[] = $patientId;
			$patientsRecords[$patientId][$recordId] = $record;
			$patientsRecords[$patientId][$recordId]['addresses'] = array();
		}

		$patientsIds = array_unique($patientsIds);

		if (count($patientsIds) > 0) {
			$patientsIds_str = implode(',', $patientsIds);
			$query = "
				with list as (
					select
						PS.Person_id as patientId,
						1 as addressTypeId,
						PS.UAddress_id as addressId
					from
						v_PersonState PS
					where 
						PS.Person_id in ({$patientsIds_str})
						and PS.UAddress_id is not null
					union
					select
						PS.Person_id as patientId,
						2 as addressTypeId,
						PS.PAddress_id as addressId
					from 
						v_PersonState PS
					where 
						PS.Person_id in ({$patientsIds_str})
						and PS.PAddress_id is not null
					union
					select
						PS.Person_id as patientId,
						3 as addressTypeId,
						coalesce(O.PAddress_id, O.UAddress_id) as addressId
					from 
						v_PersonState PS
						inner join v_Job J on J.Job_id = PS.Job_id
						inner join v_Org O on O.Org_id = J.Org_id
					where 
						PS.Person_id in ({$patientsIds_str})
						and coalesce(O.PAddress_id, O.UAddress_id) is not null
				)

				select
					pl.patientId as \"patientId\",
					pl.addressTypeId as \"addressTypeId\",
					Town.KLArea_AOID as \"aoidArea\",
					Street.KLStreet_AOID as \"aoidStreet\",
					null as \"houseid\",
					A.KLRgn_id as \"region\",
					Town.KLArea_Name as \"areaName\",
					TownSocr.KLSocr_Nick as \"prefixArea\",
					Street.KLStreet_Name as \"streetName\",
					StreetSocr.KLSocr_Nick as \"prefixStreet\",
					nullif(rtrim(coalesce('д'||nullif(A.Address_House, ''), '')||' '||coalesce('к'||nullif(A.Address_Corpus, ''), '')), '') as \"house\",
					nullif(rtrim(A.Address_Flat), '') as \"flat\"
				from
					list pl
					inner join v_Address A on A.Address_id = pl.addressId
					inner join v_KLArea Town on Town.KLArea_id = coalesce(A.KLTown_id, A.KLCity_id)
					left join v_KLSocr TownSocr on TownSocr.KLSocr_id = Town.KLSocr_id
					left join v_KLStreet Street on Street.KLStreet_id = A.KLStreet_id
					left join v_KLSocr StreetSocr on StreetSocr.KLSocr_id = Street.KLSocr_id
				order by
					pl.patientId,
					pl.addressTypeId
			";
			$resp = $this->queryResult($query, $params);
			if (!is_array($resp)) {
				throw new Exception('Ошибка при получении адресов пациентов из регистра по ВЗН');
			}

			foreach ($resp as $patientAddress) {
				$patientId = $patientAddress['patientId'];
				unset($patientAddress['patientId']);
				foreach ($patientsRecords[$patientId] as $recordId => $record) {
					$patientsRecords[$patientId][$recordId]['addresses'][] = $patientAddress;
				}
			}
		}

		$records = array();
		foreach($patientsRecords as $patientId => $_records) {
			$records = array_merge($records, array_values($_records));
		}
		unset($patientsRecords);

		$tpl_r = 'vzn_register_record';

		$all_count = count($records);
		$in_file_count = 0;
		$recepts = array();
		$error_strings = array();

		$required = array('snils','moId','includeDate','policTypeId','policNumber','imcCode','isInclRegistry','isDrugSupply','territoryId','signedPerson');

		foreach($records as $index => $record) {
			$hasErrors = false;
			foreach($record as $key => $value) {
				if (in_array($key, $required) && empty($value)) {
					$hasErrors = true;
					$error_strings[] = "Пациент {$record['patientId']}, дата включения {$record['includeDate']}, поле {$key}";
				}
			}
			if ($hasErrors) {
				unset($records[$index]);
			} else {
				$this->_insertPersonRegisterExport($record['id'], $this->_params['ExportType'], $this->promedUserId);
			}
		}

		$in_file_count = count($records);

		$file_name = mb_strtolower($tpl_r."s");
		$out_dir = $this->_params['OutDir'];

		$file_number = 1;
		$files_array = array();

		$openXml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r\n<records>\n";
		$closeXml = "</records>";

		$xml = $openXml;
		foreach(array_values($records) as $index => $record) {
			$tplParams = $record;
			$xml .= $this->parser->parse('export_xml/'.$tpl_r, $tplParams, true)."\n";

			if (strlen($xml) > $this->exportLimit || $index == count($records)-1) {
				$xml .= $closeXml;
				$xml_file_name = $file_name."_".$file_number.".xml";
				$xml_file_path = $out_dir."/".$xml_file_name;
				file_put_contents($xml_file_path, $xml);

				$xml = $openXml;
				$file_number++;
				$files_array[$xml_file_name] = $xml_file_path;
			}
		}

		$result_strings = array(
			date('d.m.Y H:i'),
			"Всего регистровых записей  - {$all_count}",
			"Включено в файл выгрузки – {$in_file_count}",
		);
		if (count($error_strings) > 0) {
			$result_strings[] = "Ошибки:";
			$result_strings = array_merge($result_strings, $error_strings);
		}

		return array(
			'file_name' => $file_name,
			'files_array' => $files_array,
			'result_strings' => $result_strings,
		);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	function exportRecepts() {
		$params = array(
			'begDate' => $this->_params['BegDate'],
			'endDate' => $this->_params['EndDate'],
			'Region_id' => $this->regionNumber,
		);
		if ($this->regionNick == 'perm') {
			$vznFilter = "coalesce(ER.EvnRecept_Is7Noz, 1) = 2";
		} else {
			$vznFilter = "WDCIT.WhsDocumentCostItemType_Nick = 'vzn'";
		}
		$query = "			
			select
				PR.PersonRegister_id as \"registryNumber\",
				ER.EvnRecept_Ser as \"recipeSerial\",
				ER.EvnRecept_Num as \"recipeNumber\",
				to_char(ER.EvnRecept_setDate, 'yyyy-mm-dd') as \"issueDate\",
				DV.DrugDose_id as \"issueDosageId\",
				DV.DrugKolDose_id as \"doseCount\",
				MP.Person_Snils as \"personId\",
				D.Diag_Code as \"desease\",
				NDV.DrugVZN_id as \"mnnId\",
				LpuOID.PassportToken_tid as \"moId\",
				DV.DrugFormVZN_id as \"drugFormId\",
				'false' as \"isTheraphyResistence\",
				left(Region.KLAdr_Ocatd, 2) as \"territoryId\",
				to_char(RO.EvnRecept_otpDate, 'yyyy-mm-dd') as \"deliveryDate\",
				oDV.DrugDose_id as \"dosageId\",
				oDV.DrugKolDose_id as \"doseInPack\",
				RO.EvnRecept_Kolvo as \"packCount\",
				FarmacyOID.PassportToken_tid as \"pharmacyId\",
				oDV.DrugRelease_id as \"vznDrugId\",
				MZHead.Person_SurName||' '||MZHead.Person_FirName||coalesce(' '||MZHead.Person_SecName,'') as \"signedPerson\",
				'' as \"note\"
			from
				ReceptOtov RO
				inner join v_EvnRecept ER on ER.EvnRecept_id = RO.EvnRecept_id
				inner join v_DrugFinance DF on DF.DrugFinance_id = ER.DrugFinance_id
				inner join v_WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
				inner join lateral(
					select PR.*
					from v_PersonRegister PR
					where PR.PersonRegisterType_id = 49 and PR.Diag_id = ER.Diag_id
					and ER.EvnRecept_setDate between PR.PersonRegister_setDate and coalesce(PR.PersonRegister_disDate, ER.EvnRecept_setDate)
					limit 1
				) PR on true
				left join lateral(
					select MP.*
					from v_MedPersonal MP
					where MP.MedPersonal_id = ER.MedPersonal_id
					limit 1
				) MP on true
				left join fed.PassportToken LpuOID on LpuOID.Lpu_id = ER.Lpu_id
				left join v_OrgFarmacy Farmacy on Farmacy.OrgFarmacy_id = RO.OrgFarmacy_id
				left join passport.v_PassportToken FarmacyOID on FarmacyOID.Org_id = Farmacy.Org_id
				left join v_KLArea Region on Region.KLArea_id = :Region_id
				left join v_Diag D on D.Diag_id = ER.Diag_id
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = ER.DrugComplexMnn_id
				left join lateral(
					select DV.*
					from rls.DrugVZN DV
					inner join rls.v_Drug D on D.Drug_id = DV.Drug_id
					where D.DrugComplexMnn_id = DCM.DrugComplexMnn_id and D.Drug_id = coalesce(ER.Drug_rlsid, D.Drug_id)
					limit 1
				) DV on true
				left join nsi.DrugVZN NDV on NDV.Actmatters_id = DCM.Actmatters_id
				left join lateral(
					select DV.*
					from rls.DrugVZN DV
					inner join rls.v_Drug D on D.Drug_id = DV.Drug_id
					where D.DrugComplexMnn_id = DCM.DrugComplexMnn_id and D.Drug_id = coalesce(RO.Drug_cid, D.Drug_id)
					limit 1
				) oDV on true
				left join lateral(
					select O.*
					from v_Org O
					inner join v_OrgType OT on OT.OrgType_id = O.OrgType_id
					where OT.OrgType_Code = 13 and O.Org_Nick like 'Минздрав' and O.Region_id = :Region_id
					and exists(select * from v_OrgHead where Org_id = O.Org_id and OrgHeadPost_id = 12)
					order by O.Org_begDate desc
					limit 1
				) MZ on true
				left join lateral(
					select PS.*
					from v_OrgHead OH
					inner join v_PersonState PS on PS.Person_id = OH.Person_id
					where OH.Org_id = MZ.Org_id and OH.OrgHeadPost_id = 12
					order by OH.OrgHead_CommissDate desc
					limit 1
				) MZHead on true
			where
				RO.EvnRecept_otpDate between :begDate and :endDate
				and DF.DrugFinance_SysNick = 'fed'
				and {$vznFilter}
		";
		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			throw new Exception('Ошибка при получении данных рецептов');
		}

		$all_count = count($resp);
		$in_file_count = 0;
		$recepts = array();
		$error_strings = array();

		foreach($resp as $recept) {
			$hasErrors = false;
			foreach($recept as $key => $value) {
				if (in_array($key, array('dosageId', 'doseInPack', 'packCount', 'pharmacyId', 'vznDrugId'))) {
					if (!empty($recept['deliveryDate']) && empty($value)) {
						$hasErrors = true;
						$errorStrings[] = "Рецепт {$recept['recipeSerial']} {$recept['recipeNumber']}, поле {$key}";
					}
				} elseif (!in_array($key, array('note')) && empty($value)) {
					$hasErrors = true;
					$error_strings[] = "Рецепт {$recept['recipeSerial']} {$recept['recipeNumber']}, поле {$key}";
				}
			}
			if (!$hasErrors) {
				$recepts[] = $recept;
			}
		}
		$in_file_count = count($recepts);

		$tpl_r = 'vzn_register_recept';
		$file_name = mb_strtolower($tpl_r."s");
		$out_dir = $this->_params['OutDir'];

		$file_number = 1;
		$files_array = array();

		$openXml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>\r\n<records>\n";
		$closeXml = "</records>";

		$xml = $openXml;
		foreach($recepts as $index => $recept) {
			$tplParams = $recept;
			$xml .= $this->parser->parse('export_xml/'.$tpl_r, $tplParams, true)."\n";

			if (strlen($xml) > $this->exportLimit || $index == count($recepts)-1) {
				$xml .= $closeXml;
				$xml_file_name = $file_name."_".$file_number.".xml";
				$xml_file_path = $out_dir."/".$xml_file_name;
				file_put_contents($xml_file_path, $xml);

				$xml = $openXml;
				$file_number++;
				$files_array[$xml_file_name] = $xml_file_path;
			}
		}

		$result_strings = array(
			date('d.m.Y H:i'),
			"Всего рецептов  - {$all_count}",
			"Включено в файл выгрузки – {$in_file_count}",
		);
		if (count($error_strings) > 0) {
			$result_strings[] = "Ошибки:";
			$result_strings = array_merge($result_strings, $error_strings);
		}

		return array(
			'file_name' => $file_name,
			'files_array' => $files_array,
			'result_strings' => $result_strings,
		);
	}

	/**
	 * Запрос данных для выгрузки в федеральный регистр регионального сегмента
	 * @param $data
	 * @return array
	 * @throws Exception
	 */
	protected function _loadExportData($data)
	{
		if ( false == swPersonRegister::isAllow($this->personRegisterTypeSysNick) ) {
			throw new Exception('Работа с данным типом регистра недоступна!');
		}
		$this->setScenario('export');
		$this->setParams($data);
		if (empty($this->_params['ExportMod'])) {
			$this->_params['ExportMod'] = '04-FR';
		}
		if (false == in_array($this->_params['ExportMod'], array('04-FR','06-FR','03-FR'))) {
			throw new Exception('Неправильный формат выгрузки');
		}
		if ('03-FR' == $this->_params['ExportMod']) {
			return $this->_exportNolos03Fr();
		}

		if ('06-FR' == $this->_params['ExportMod']) {
			/*if (empty($organ_ispoln_vlast_sf)) {
				throw new Exception('Не определен Орган исполнительной власти субъекта Российской Федерации (ФМБА России)');
			}*/
			if (false == $this->isAllowScenario()) {
				throw new Exception('Действие «Выгрузка в федеральный регистр» не доступно');
			}
			return $this->_exportRecepts();
		}

		if (false == $this->isAllowScenario()) {
			throw new Exception('Действие «Выгрузка в федеральный регистр» не доступно');
		}
		$response = $this->_exportPerson();

		$organ_ispoln_vlast_sf = '';
		$head_person = '';
		$org_data = $this->getFirstRowFromQuery("
			select o.Org_Name as \"Org_Name\", o.Org_Rukovod as \"Org_Rukovod\"
			from v_Org o
			where o.Org_id = :Org_id;
		", array('Org_id' => $this->sessionParams['org_id']));
		if ($org_data && !empty($org_data['Org_Name'])) {
			// нужно указывать полное наименование организации
			$organ_ispoln_vlast_sf = $org_data['Org_Name'];
			$head_person = $org_data['Org_Rukovod'];
		}

		foreach ($response as $i => $row) {
			$response[$i]['organ_ispoln_vlast_sf'] = $organ_ispoln_vlast_sf;
			$response[$i]['head_person'] = $head_person;
		}

		return $response;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function _exportNolos03Fr()
	{
		if (empty($this->_params['Lpu_eid'])) {
			throw new Exception('Нужно указать МО формирования журнала');
		}
		if (empty($this->_params['BegDate'])) {
			throw new Exception('Нужно указать дату начала периода');
		}
		if (empty($this->_params['EndDate'])) {
			$this->_params['EndDate'] = $this->currentDT->format('Y-m-d') ;
		}
		if ($this->_params['EndDate'] < $this->_params['BegDate']) {
			throw new Exception('Дата окончания периода не может быть раньше даты начала');
		}
		$params = array();
		$params['PersonRegisterType_id'] = $this->PersonRegisterType_id;
		$params['Lpu_id'] = $this->_params['Lpu_eid'];
		$params['BegDate'] = $this->_params['BegDate'];
		$params['EndDate'] = $this->_params['EndDate'];
		$query = "
			select
				v_Lpu.Lpu_Name as \"Lpu_Name\",
				pcard.PersonCard_Code as \"PersonCard_Code\",
				E.NotifyType_id as \"NotifyType_id\",
				E.EvnNotifyRegister_Num as \"EvnNotifyRegister_Num\",
				PS.Person_SurName as \"Person_SurName_p\",
				PS.Person_FirName as \"Person_FirName_p\",
				PS.Person_SecName as \"Person_SecName_p\",
				MP.Person_SurName as \"Person_SurName_m\",
				MP.Person_FirName as \"Person_FirName_m\",
				MP.Person_SecName as \"Person_SecName_m\",
				expert1.Person_SurName as \"Person_SurName_s\",
				expert1.Person_FirName as \"Person_FirName_s\",
				expert1.Person_SecName as \"Person_SecName_s\",
				predsed.Person_SurName as \"Person_SurName_v\",
				predsed.Person_FirName as \"Person_FirName_v\",
				predsed.Person_SecName as \"Person_SecName_v\",
				to_char(E.EvnNotifyRegister_setDT, 'dd.mm.yyyy') as \"EvnNotifyRegister_setDate\"
			from v_EvnNotifyRegister E
				inner join v_PersonState PS on PS.Person_id = E.Person_id
				left join v_Lpu on v_Lpu.Lpu_id = E.Lpu_id
				left join v_EvnVK EvnVK on EvnVK.EvnVK_id = E.EvnVK_id
				left join v_MedPersonal MP on MP.MedPersonal_id = E.MedPersonal_id and MP.Lpu_id = E.Lpu_id
				left join lateral(
					select pc.PersonCard_Code
					from v_PersonCard pc
					where pc.Person_id = PS.Person_id and pc.LpuAttachType_id = 1
					order by PersonCard_begDate desc
					limit 1
				) as pcard on true
				left join lateral(
					select
						v_EvnVKExpert.EvnVKExpert_id,
						MP.Person_SurName,
						MP.Person_FirName,
						MP.Person_SecName
					from v_EvnVKExpert
					inner join v_MedServiceMedPersonal MSMP on MSMP.MedServiceMedPersonal_id = v_EvnVKExpert.MedServiceMedPersonal_id
					inner join v_MedService MS on MS.MedService_id = MSMP.MedService_id
					inner join v_MedPersonal MP on MP.MedPersonal_id = MSMP.MedPersonal_id and MP.Lpu_id = MS.Lpu_id
					where v_EvnVKExpert.EvnVK_id = EvnVK.EvnVK_id and v_EvnVKExpert.ExpertMedStaffType_id = 1
					limit 1
				) predsed on true
				left join lateral(
					select
						v_EvnVKExpert.EvnVKExpert_id,
						MP.Person_SurName,
						MP.Person_FirName,
						MP.Person_SecName
					from v_EvnVKExpert
					inner join v_MedServiceMedPersonal MSMP on MSMP.MedServiceMedPersonal_id = v_EvnVKExpert.MedServiceMedPersonal_id
					inner join v_MedService MS on MS.MedService_id = MSMP.MedService_id
					inner join v_MedPersonal MP on MP.MedPersonal_id = MSMP.MedPersonal_id and MP.Lpu_id = MS.Lpu_id
					where v_EvnVKExpert.EvnVK_id = EvnVK.EvnVK_id and v_EvnVKExpert.ExpertMedStaffType_id = 2
					limit 1
				) expert1 on true
			where E.PersonRegisterType_id = :PersonRegisterType_id
                and E.Lpu_id = :Lpu_id
                and to_char(E.EvnNotifyRegister_setDT, 'yyyy-mm-dd hh24:mi:ss') >= :BegDate
				and to_char(E.EvnNotifyRegister_setDT, 'yyyy-mm-dd hh24:mi:ss') <= :EndDate
            ";
		//echo getDebugSQL($query, $params); die();
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			throw new Exception('Ошибка запроса к БД 06');
		}
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function _exportRecepts()
	{
		if (empty($this->_params['BegDate'])) {
			throw new Exception('Нужно указать дату начала периода');
		}
		if (empty($this->_params['EndDate'])) {
			$this->_params['EndDate'] = $this->currentDT->format('Y-m-d') ;
		}
		if ($this->_params['EndDate'] < $this->_params['BegDate']) {
			throw new Exception('Дата окончания периода не может быть раньше даты начала');
		}
		$params = array();
		$params['PersonRegisterType_id'] = $this->PersonRegisterType_id;
		$params['BegDate'] = $this->_params['BegDate'];
		$params['EndDate'] = $this->_params['EndDate'];

		$filterRecept = "and WDCIT.WhsDocumentCostItemType_Nick = 'vzn'";
		$filterReceptOtov = $filterRecept;
		$idIstochnikFinans = "case when DF.DrugFinance_SysNick = 'fed' then 1 else 0 end as \"id_istochnik_finans\",";
		if (getRegionNick() == 'perm') {
			$filterRecept = "and coalesce(ER.EvnRecept_Is7Noz,1) = 2";
			$filterReceptOtov = "and coalesce(Erec.EvnRecept_Is7Noz,1) = 2";
			$idIstochnikFinans = "case when RF.ReceptFinance_Code = 1 then 1 else 0 end \"as id_istochnik_finans\",";
		}

		$query = "
			with PR as (
				select
					PR.Person_id,
					PR.PersonRegister_Code,
					PRD.MorbusType_id
				from v_PersonRegister PR 
				inner join PersonRegisterDiag PRD on PRD.PersonRegisterType_id = PR.PersonRegisterType_id and PRD.Diag_id = PR.Diag_id
				where
					PR.PersonRegisterType_id = :PersonRegisterType_id
					and PR.PersonRegister_Code is not null
			),
			ER as (
				/*select
					'EvnRecept' as obj,
					ER.EvnRecept_id as obj_id,
					ER.Drug_rlsid,
					ER.Drug_id,
					ER.Lpu_id,
					ER.MedPersonal_id as MedPersonalRec_id,
					ER.OrgFarmacy_id,
					ER.DrugFinance_id,
					ER.ReceptFinance_id,
					ER.Person_id,
					(ER.EvnRecept_Ser || ' ' || ER.EvnRecept_Num) as pres_ser_num,
					to_char(ER.EvnRecept_setDT, 'dd.mm.yyyy') as pres_date,
					to_char(ER.EvnRecept_otpDT, 'dd.mm.yyyy') as pharmacy_date_out1,
					ER.EvnRecept_Kolvo as lekarstv_packs_out,
					1 as operation_code,
					PRD.Diag_FedCode as mkb_code,
					PR.PersonRegister_Code as u_numb_regist_record_04
				from v_EvnRecept ER
				inner join PersonRegisterDiag PRD on PRD.Diag_id = ER.Diag_id
					and PRD.PersonRegisterType_id = :PersonRegisterType_id
				inner join PR on PR.Person_id = ER.Person_id AND PR.MorbusType_id = PRD.MorbusType_id
				left join WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
				where
					ER.EvnRecept_otpDT is null
					and to_char(ER.EvnRecept_setDT, 'yyyy-mm-dd hh24:mi:ss') >= :BegDate
					and to_char(ER.EvnRecept_setDT, 'yyyy-mm-dd hh24:mi:ss') <= :EndDate
					{$filterRecept}
				union all*/
				select  /* Рецепты отпущенные */
					'ReceptOtov' as obj,
					ER.ReceptOtov_id as obj_id,
					coalesce(ER.Drug_cid,ReceptOtovDop.Drug_rlsid) as Drug_rlsid,
					ER.Drug_id,
					ER.Lpu_id,
					ER.MedPersonalRec_id,
					ER.OrgFarmacy_id,
					ER.DrugFinance_id,
					ER.ReceptFinance_id,
					ER.Person_id,
					(ER.EvnRecept_Ser || ' ' || ER.EvnRecept_Num) as pres_ser_num,
					to_char(ER.EvnRecept_setDT, 'dd.mm.yyyy') as pres_date,
					to_char(ER.EvnRecept_otpDate, 'dd.mm.yyyy') as pharmacy_date_out1,
					ER.EvnRecept_Kolvo as lekarstv_packs_out,
					2 as operation_code,
					PRD.Diag_FedCode as mkb_code,
					PR.PersonRegister_Code as u_numb_regist_record_04
				from ReceptOtov ER
				inner join v_EvnRecept Erec on Erec.EvnRecept_id = ER.EvnRecept_id
				left join PersonRegisterDiag PRD on PRD.Diag_id = ER.Diag_id
					and PRD.PersonRegisterType_id = :PersonRegisterType_id
				inner join PR on PR.Person_id = ER.Person_id /*AND PR.MorbusType_id = PRD.MorbusType_id*/
				left join WhsDocumentCostItemType WDCIT on WDCIT.WhsDocumentCostItemType_id = ER.WhsDocumentCostItemType_id
				left join ReceptOtovDop on ReceptOtovDop.ReceptOtov_id = ER.ReceptOtov_id
				where
					/* дата выписки (ранее периода выгрузки) не учитывается, а дата обеспечения - в периоде выгрузки */
					to_char(ER.EvnRecept_otpDate, 'yyyy-mm-dd hh24:mi:ss') >= :BegDate
					and to_char(ER.EvnRecept_otpDate, 'yyyy-mm-dd hh24:mi:ss') <= :EndDate
					{$filterReceptOtov}
			)

			select
				Org.Org_OGRN as \"gos_numb_okop_uz\",
				Org_F.org_OKATO as \"terr_code\",
				Org_F.org_OKATO as \"okato\",
				Org_F.org_OGRN as \"pharmacy_reg_no\",
				MP.MedPersonal_Code as \"pres_doctor\",
				DMVZN.DrugMnnVZN_Code as \"prepar_name\",
				(coalesce(DCMF.DrugComplexMnnFas_KolPrim, 0) * ER.lekarstv_packs_out) as \"doza_count\",
				TNVZN.TradeNamesVZN_Code as \"lekarstv_name\",
				DFMVZN.DrugFormVipVZN_Code as \"lekarstv_forma\",
				case
					when DCMF.DrugComplexMnnFas_MassPrim is null and DCMF.DrugComplexMnnFas_VolPrim is null
					then DCM.DrugComplexMnn_Dose
					else left(DCMF.DrugComplexMnnFas_Name, charindex(',', DCMF.DrugComplexMnnFas_Name)-1)
				end as \"lekarstv_doza_pres\",
				DCMF.DrugComplexMnnFas_KolPrim as \"lekarstv_doza_pack\",
				{$idIstochnikFinans}
				ER.u_numb_regist_record_04 as \"u_numb_regist_record_04\",
				null as \"u_numb_regist_record\", -- такой же
				ER.mkb_code as \"mkb_code\",
				ER.pres_ser_num as \"pres_ser_num\",
				ER.pres_date as \"pres_date\",
				ER.pharmacy_date_out1 as \"pharmacy_date_out1\",
				ER.lekarstv_packs_out as \"lekarstv_packs_out\",
				3 as \"operation_code\",
				null as \"fcomment\",
				HeadPerson.Person_SurName||' '||HeadPerson.Person_FirName||coalesce(' '||HeadPerson.Person_SecName,'') as \"head_person\",
				minzdrav.Org_Name as \"organ_ispoln_vlast_sf\",
				ER.obj || cast(ER.obj_id as varchar) as \"id\"
			from ER
				left join v_MedPersonal MP on MP.MedPersonal_id = ER.MedPersonalRec_id and MP.Lpu_id = ER.Lpu_id
				left join Lpu Lpu on Lpu.Lpu_id = ER.Lpu_id
				left join Org on Org.Org_id = Lpu.Org_id
				left join v_OrgHead OH on OH.Lpu_id = Lpu.Lpu_id and LpuUnit_id is null
				left join v_PersonState HeadPerson on HeadPerson.Person_id = OH.Person_id
				left join OrgFarmacy OrgFarm on OrgFarm.OrgFarmacy_id = ER.OrgFarmacy_id
				left join Org Org_F on OrgFarm.Org_id = Org_F.Org_id
				left join DrugFinance DF on DF.DrugFinance_id= ER.DrugFinance_id
				left join ReceptFinance RF on RF.ReceptFinance_id= ER.ReceptFinance_id
				/*Получение данных по Drug_id*/
				left join v_Drug D on D.Drug_id = ER.Drug_id
				left join v_DrugTorg DT on DT.DrugTorg_id = D.DrugTorg_id
				left join v_DrugMnn DM on DM.DrugMnn_id = D.DrugMnn_id
				left join rls.v_DrugTorgCode DTC on DTC.DrugTorgCode_Code = cast(DT.DrugTorg_Code as varchar)
				left join rls.v_DrugMnnCode DMC on DMC.DrugMnnCode_Code = cast(DM.DrugMnn_Code as varchar)
				/*left join rls.v_DrugNomen DN on DN.DrugTorgCode_id = DTC.DrugTorgCode_id and DN.DrugMnnCode_id = DMC.DrugMnnCode_id*/
				left join lateral(
					select *
					from rls.v_DrugNomen DN_t
					where DN_t.DrugTorgCode_id = DTC.DrugTorgCode_id
					and DN_t.DrugMnnCode_id = DMC.DrugMnnCode_id
					limit 1
                ) as DN on true
				/*Получение данных по Drug_rlsid*/
				left join rls.v_Drug DRls on DRls.Drug_id = coalesce(ER.Drug_rlsid,DN.Drug_id)
				left join rls.v_DrugComplexMnn DCM on DCM.DrugComplexMnn_id = DRls.DrugComplexMnn_id
				left join rls.prep P on P.Prep_id = DRls.DrugPrep_id
				left join rls.PREP_ACTMATTERS PA on PA.PREPID = P.Prep_id
				left join rls.v_DrugComplexMnnFas DCMF on DCMF.DrugComplexMnnFas_id = DCM.DrugComplexMnnFas_id
				/*Получение кодов торгового наименования и МНН из справочника ВЗН*/
				left join rls.DrugMnnVZN DMVZN on DMVZN.ACTMATTERS_ID = PA.MATTERID
				left join rls.TradeNamesVZN TNVZN on TNVZN.TRADENAMES_ID = P.TRADENAMEID
				left join lateral(
					select DrugFormVipVZN_Code
					from rls.DrugFormMnnVZN
					where DrugFormMnnVZN.DrugMnnVZN_Code = DMVZN.DrugMnnVZN_Code
					limit 1
				) DFMVZN on true
				left join lateral(
					select O.Org_Name
					from v_Org O
					where O.OrgType_id = 15 and O.Org_Name like 'Министерство здравоохранения%'
					and (O.Org_endDate is null or O.Org_endDate < ER.pharmacy_date_out1)
					limit 1
				) minzdrav on true
		";
		/*
				ER.Drug_id,
				(coalesce(DRls.Drug_Fas, D.Drug_Fas) * ER.lekarstv_packs_out) as doza_count,
				coalesce(DRls.Drug_Dose, D.Drug_DoseQ) as lekarstv_doza_pres,
				coalesce(DRls.Drug_Fas, D.Drug_Fas) as lekarstv_doza_pack,
				left join v_Drug D on D.Drug_id = ER.Drug_id
		*/
		/*echo getDebugSQL($query, $params); die();*/
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$tmp = $result->result('array');
		} else {
			throw new Exception('Ошибка запроса к БД 06');
		}

		$response = array();
		$errorStrings = array();
		$requiredFields = array(
			'u_numb_regist_record_04','gos_numb_okop_uz','pres_doctor','pres_ser_num','pres_date','okato',
			'doza_count','terr_code','pharmacy_reg_no','lekarstv_name','mkb_code','lekarstv_forma',
			'operation_code','organ_ispoln_vlast_sf','id_istochnik_finans'
		);

		foreach ($tmp as $row) {
			$isErrors = false;
			if (empty($row['pres_ser_num'])) {
				$err1 = "Запись регистра {$row['u_numb_regist_record_04']}";
				$err2 = "Запись регистра {$row['u_numb_regist_record_04']}, поле ";
			} else {
				$err1 = "Запись регистра {$row['u_numb_regist_record_04']} рецепт {$row['pres_ser_num']}";
				$err2 = "Рецепт {$row['pres_ser_num']}, поле ";
			}
			foreach($requiredFields as $fieldName) {
				if (!empty($row[$fieldName])) continue;
				$isErrors = true;
				$errorStrings[] = $err2.$fieldName;
				$err = $err1;
				switch($fieldName) {
					case 'pres_ser_num': $err .= ".<br/>Не заполнены Серия и номер рецепта";break;
					case 'prepar_name': $err .= ".<br/>Отсутствуют данные о МНН";break;
					case 'lekarstv_name': $err .= ".<br/>Отсутствуют данные о торговом наименовании";break;
					case 'lekarstv_forma': $err .= ".<br/>Отсутствуют данные о формах выпуска";break;
					case 'mkb_code': $err .= ".<br/>Не заполнен Код заболевания";break;
					case 'terr_code': $err .= ".<br/>Не заполнен Код территории отпуска лекарственного средства по ОКАТО";break;
					case 'doza_count': $err .= ".<br/>Не заполнено Выписанное количество доз лекарственного средства";break;
					case 'gos_numb_okop_uz': $err .= ".<br/>Не заполнен ОГРН (по ОКПО) МО, выдавшей рецепт";break;
					case 'pres_date': $err .= ".<br/>Не заполнена Дата выписки рецепта";break;
					case 'pres_doctor': $err .= ".<br/>Не заполнен Идентификационный номер врача, выписавшего рецепт";break;
					default: $err .= ".<br/>Не заполнено поле {$fieldName}";
				}
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => $err,
					'Time' => date('H:i:s'),
				);
			}
			if (!$isErrors && empty($response[$row['id']])) {
				$response[$row['id']] = $row;
			}
		}

		$strings = array(
			date('d.m.Y H:i'),
			"Всего рецептов  - ".count($tmp),
			"Включено в файл выгрузки – ".count($response),
		);
		if (count($errorStrings) > 0) {
			$strings[] = "Ошибки:";
			$strings = array_merge($strings, $errorStrings);
		}
		if (is_array($this->ResultFileStrings)) {
			$this->ResultFileStrings = array_merge($this->ResultFileStrings, $strings);
		}

		return $response;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	private function _exportPerson()
	{
		if (empty($this->_params['ExportType'])) {
			$this->_params['ExportType'] = 2;
		}
		if (false == in_array($this->_params['ExportType'], array(1,2))) {
			throw new Exception('Неправильный тип выгрузки');
		}
		if (empty($this->_params['ExportDate'])) {
			$this->_params['ExportDate'] = $this->currentDT->format('Y-m-d') ;
		}
		if ($this->_params['ExportDate'] != $this->currentDT->format('Y-m-d')) {
			throw new Exception('Дата выгрузки должна быть равна текущей дате');
		}
		$params = array();
		$params['PersonRegisterType_id'] = $this->PersonRegisterType_id;
		if (2 == $this->_params['ExportType']) {
			// Изменения
			$listId = $this->loadChangedPersonRegisterIdList($this->PersonRegisterType_id);
			if (empty($listId)) {
				return array();
			}
			$listId = implode(',', $listId);
			$filter = "PR.PersonRegister_id in ({$listId})";
		} else {
			// все
			$filter = "PR.PersonRegisterType_id = :PersonRegisterType_id";
		}
		$query = "
			with mv as(
				select
					cast(dbo.tzgetdate() as date) as dt
			), DRR as (
				select
					drr.Person_id
				from
					DrugRequestRow DRR
					left join DrugRequest DR on DRR.DrugRequest_id = DR.DrugRequest_id
					left join DrugRequestPeriod DRP on DRP.DrugRequestPeriod_id = DR.DrugRequestPeriod_id
				where
					date_part('year', DRP.DrugRequestPeriod_endDate) = date_part('year', (select dt from mv))
			)

			select
				PR.PersonRegister_id as \"PersonRegister_id\",
				RTRIM(PS.Person_SurName) as \"surname\",
				RTRIM(PS.Person_FirName) as \"name\",
				RTRIM(PS.Person_SecName) as \"patroname\",
				case
					when ps.sex_id = 1 then 'm'
					when ps.sex_id = 2 then 'f'
				end as \"sex\",
				to_char(PS.Person_Birthday, 'dd.mm.yyyy') as \"birthdate\",
				PS.Person_Snils as \"SNILS\",
				CASE
					WHEN p.PolisType_id = 4 THEN '-'
					WHEN p.PolisType_id = 1 THEN PS.Polis_Ser
					ELSE NULL
				END as \"ser_pol_oms\",
				os.OrgSMO_Name as \"ins_company\",
				CASE
					WHEN p.PolisType_id = 4 THEN PS.Person_EdNum
					WHEN p.PolisType_id = 1 THEN PS.Polis_Num
					ELSE NULL
				END as \"sernum_pol_oms\",
				substring(coalesce(UAS.KLAdr_Ocatd, UAA.KLAdr_Ocatd), 1, 2) as \"terr_code\",
				VPUA.Address_Address as \"address\",
				CASE
					WHEN UAS.KLAdr_Ocatd = NULL THEN UAA.KLAdr_Ocatd
					ELSE UAS.KLAdr_Ocatd
				END as \"okato\",
				DLT.DocumentTypeLink_Code as \"id_docum_type\",
				PS.Document_Ser as \"ser_docum\",
				PS.Document_Num as \"num_docum\",
				VOD.OrgDep_Nick as \"issue_docum\",
				to_char(Doc.Document_begDate, 'dd.mm.yyyy') as \"when_docum\",
				PRD.Diag_FedCode as \"id_mkb\",
				iorg.Org_OKPO as \"inst_code\",
				to_char(PR.PersonRegister_disDate, 'dd.mm.yyyy') as \"change_date_out\",
				dlpu.Lpu_Name as \"inst_name_out\",
				dlpu.Org_Code as \"inst_code_out\",
				CASE WHEN PP.PrivilegeType_Code = '81' THEN 3
					 WHEN PP.PrivilegeType_Code = '82' THEN 2
					 WHEN PP.PrivilegeType_Code = '83' THEN 1
					 WHEN PP.PrivilegeType_Code = '84' THEN 4
				END as \"physically_challenged\",
				to_char(PS.Person_deadDT, 'dd.mm.yyyy') as \"death_date\",
				CASE WHEN PR.PersonRegister_IsResist = 2 THEN 1 ELSE 0 END as \"therapy_resistance\",
				PS.Person_Snils as \"ins_account_num\",
				case
					when exists(
						select PP.PersonPrivilege_id
						from v_PersonPrivilege PP
						inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 1 /* ФЗ № 178 - если есть федеральная льгота */
						where PP.Person_id = PS.Person_id
							and PP.PersonPrivilege_begDate <= (select dt from mv)
							and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= (select dt from mv))
							and not exists (
								select PR.PersonRefuse_id from PersonRefuse PR
								where PR.Person_id = PP.Person_id
									and coalesce(PR.PersonRefuse_IsRefuse,1) = 2
									and PR.PersonRefuse_Year = date_part('year', (select dt from mv))
								limit 1
							)
						limit 1
					) then 1 else 0
				end as \"dis_register_1\",
				case
					when exists(
						select PP.PersonPrivilege_id
						from v_PersonPrivilege PP
						inner join PrivilegeType PT on PT.PrivilegeType_id = PP.PrivilegeType_id
							and PT.ReceptFinance_id = 2 /* постановление № 890 - если есть региональная льгота */
						where PP.Person_id = PS.Person_id
							and PP.PersonPrivilege_begDate <= (select dt from mv)
							and (PP.PersonPrivilege_endDate is null or PP.PersonPrivilege_endDate >= (select dt from mv))
							and not exists (
								select PR.PersonRefuse_id from PersonRefuse PR
								where PR.Person_id = PP.Person_id
									and coalesce(PR.PersonRefuse_IsRefuse,1) = 2
									and PR.PersonRefuse_Year = date_part('year', (select dt from mv))
								limit 1
							)
						limit 1
					) then 1 else 0
				end as \"dis_register_2\",
				case
					when exists(
						select DRR.Person_id
						from DRR
						where DRR.Person_id = PS.Person_id
						limit 1
					) then 1 else 0
				end as \"dis_signed\",
				  null as \"u_numb_regist_record\",
				case
					when PR.PersonRegister_updDT = PR.PersonRegister_insDT then 1
					when PR.PersonRegister_updDT > PR.PersonRegister_insDT and PR.PersonRegister_disDate is null then 2
					when PR.PersonRegister_updDT > PR.PersonRegister_insDT and PR.PersonRegister_disDate is not null then 3
					else null
				end as \"operation_code_id\",
				null as \"organ_ispoln_vlast_sf\",
				null as \"head_person\",
				to_char(PR.PersonRegister_updDT, 'dd.mm.yyyy') as \"change_datetime\",
				null as \"fcomment\",
				ilpu.Lpu_Name as \"inst_name\"
			from
				v_PersonRegister PR
				inner join v_PersonState PS on PS.Person_id = PR.Person_id
				left join PersonRegisterDiag PRD on PRD.PersonRegisterType_id = PR.PersonRegisterType_id and PRD.Diag_id = PR.Diag_id
				left join PersonUAddress PUA on PS.UAddress_id = PUA.UAddress_id
				left join v_PersonUAddress VPUA on PUA.PersonUAddress_id = VPUA.PersonUAddress_id
				left join v_KLStreet UAS on UAS.KLStreet_id = VPUA.KLStreet_id
				left join v_KLArea UAA on UAA.KLArea_id = VPUA.KLCity_id or UAA.KLArea_id = VPUA.KLTown_id
				left join v_Document Doc on Doc.Document_id = PS.Document_id
				left join v_DocumentType DocTP on Doc.DocumentType_id = DocTP.DocumentType_id
				left join dbo.DocumentTypeLink DLT on DocTP.DocumentType_id = DLT.DocumentType_id
				left join v_OrgDep VOD on Doc.OrgDep_id = VOD.OrgDep_id
				left join v_Polis p on p.Polis_id = ps.Polis_id
				left join v_OrgSMO os on os.OrgSmo_id = p.OrgSmo_id
				left join v_Lpu ilpu on ilpu.Lpu_id = PR.Lpu_iid
				left join v_Org iorg on iorg.Org_id = ilpu.Org_id
				left join v_Lpu dlpu on dlpu.Lpu_id = PR.Lpu_did
				left join lateral(select * from v_PersonPrivilege PP where PP.Person_id = PS.Person_id limit 1) PP on true
			where
				{$filter}
		";
		//echo getDebugSQL($query, $params); die();
		$result = $this->db->query($query, $params);
		if (is_object($result)) {
			$tmp = $result->result('array');
		} else {
			throw new Exception('Ошибка запроса к БД 04');
		}
		//echo count($tmp);
		$response = array();
		foreach ($tmp as $row) {
			// исключаем записи, в которых нет обязательных данных для выгрузки
			if (empty($row['surname'])
				|| empty($row['name'])
				|| empty($row['patroname'])
				|| empty($row['sex'])
				|| empty($row['birthdate'])
				|| empty($row['ser_pol_oms'])
				|| empty($row['sernum_pol_oms'])
				|| empty($row['terr_code'])
				|| empty($row['address'])
				|| empty($row['id_docum_type'])
				|| empty($row['ser_docum'])
				|| empty($row['num_docum'])
				|| empty($row['id_mkb'])
				|| empty($row['inst_code'])
			) {
				//echo"dsfsdfsd".$row['PersonRegister_id'];
				$err = $row['surname'] . ' ' . $row['name'] . ' ' . $row['patroname'] . ', ' . $row['birthdate']; // . ' ' . $row['PersonRegister_id']
				//echo $err;
				if (empty($row['surname'])) {
					$err .= '.<br/>Не заполнена Фамилия';
				}
				if (empty($row['name'])) {
					$err .= '.<br/>Не заполнено Имя';
				}
				if (empty($row['patroname'])) {
					$err .= '.<br/>Не заполнено Отчество';
				}
				if (empty($row['sex'])) {
					$err .= '.<br/>Не заполнен Пол';
				}
				if (empty($row['birthdate'])) {
					$err .= '.<br/>Не заполнена Дата рождения';
				}
				if (empty($row['ser_pol_oms'])) {
					$err .= '.<br/>Не заполнена Серия полиса ОМС';
				}
				if (empty($row['sernum_pol_oms'])) {
					$err .= '.<br/>Не заполнен Номер полиса ОМС';
				}
				if (empty($row['terr_code'])) {
					$err .= '.<br/>Не заполнен Код территории адреса больного';
				}
				if (empty($row['address'])) {
					$err .= '.<br/>Не заполнен Адрес места жительства';
				}
				if (empty($row['id_docum_type'])) {
					$err .= '.<br/>Не заполнен Код типа документа';
				}
				if (empty($row['ser_docum'])) {
					$err .= '.<br/>Не заполнена Серия документа';
				}
				if (empty($row['num_docum'])) {
					$err .= '.<br/>Не заполнен Номер документа';
				}
				if (empty($row['id_mkb'])) {
					$err .= '.<br/>Не заполнен Код заболевания';
				}
				if (empty($row['inst_code'])) {
					$err .= '.<br/>Не заполнен Код учреждения здравоохранения, направившего сведения о больном';
				}
				$this->_saveResponse['ExportErrorArray'][] = array(
					'Text' => $err,
					'Time' => date('H:i:s'),
				);
				continue;
			}
			if (empty($response[$row['PersonRegister_id']])) {
				$snils = '';
				if (!empty($row['ins_account_num'])) {
					$snils = preg_replace('/[^0-9]/', '', $row['ins_account_num']); //удаление всего кроме цифр
					if (strlen($snils) >= 11) {
						$row['ins_account_num'] = substr($snils, 0, 3).'-'.substr($snils, 3, 3).'-'.substr($snils, 6, 3).'-'.substr($snils, 9);
					}
				}
				if(!empty($row['ser_docum'])) {
					$ser_docum = preg_replace('/[^0-9]/', '', $row['ser_docum']); //удаление всего кроме цифр
					$snils = ((isset($snils))?$snils:'');
					if (strlen($ser_docum) == 4) {
						$row['ser_docum'] = substr($ser_docum, 0, 2).' '.substr($snils, 2);
					}
				}
				if(!empty($row['inst_name_out'])) {
					$inst_name_out = preg_replace('/"/', '', $row['inst_name_out']);
					$row['inst_name_out'] = $inst_name_out;
				}
				if(!empty($row['inst_name'])) {
					$inst_name = preg_replace('/"/', '', $row['inst_name']);
					$row['inst_name'] = $inst_name;
				}
				$response[$row['PersonRegister_id']] = $row;
				//Делаем записи о выгрузке
				$this->_insertPersonRegisterExport($row['PersonRegister_id'], $this->_params['ExportType'], $this->promedUserId);
			}
		}
		return $response;
	}
}