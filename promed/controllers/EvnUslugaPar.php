<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Usluga - контроллер для работы с услугами параклиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      15.12.2011
 *
 * @property EvnUslugaPar_model $dbmodel
 */

class EvnUslugaPar extends swController {

	/**
	 * Конструктор
	 */
	function __construct() {
		parent::__construct();
		
		$this->inputRules = array(
			'updateEvnDirectionFields' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PrehospDirect_id',
					'label' => 'Кем направлени',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_did',
					'label' => 'Направившая организация',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_did',
					'label' => 'Направившее отделение',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedPersonal_did',
					'label' => 'Направивший врач',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnDirection_Num',
					'label' => 'Номер направлния',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'EvnDirection_setDate',
					'label' => 'Дата направлния',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnUslugaPar_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaPar_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
			),
			'loadEvnUslugaParPanel' => array(
				array(
					'field' => 'EvnUslugaPar_pid',
					'label' => 'Идентификатор родительского события',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				),
				array('default' => 100,'field' => 'limit','label' => 'Лимит записей','rules' => '','type' => 'int'),
				array('default' => 0,'field' => 'start','label' => 'Начальная запись','rules' => '','type' => 'int')
			),
			'loadEvnUslugaParWorkPlace' => array(
				array(
					'field' => 'date_range',
					'label' => 'Период случаев',
					'rules' => 'required',
					'type' => 'daterange'
				),
				array(
					'field' => 'Person_SurName',
					'label' => 'Фамилия',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_FirName',
					'label' => 'Имя',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_SecName',
					'label' => 'Отчество',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Person_BirthDay',
					'label' => 'Дата рождения',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор комплексной услуги',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Usluga_isFromQueue',
					'label' => 'Признак, что услуга создана из очереди',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Место работы врача',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Отделение',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'deleteEvnUslugaPar' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Идентификатор параклинической услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnDirectionList' => array(
				array(
					'field' => 'EvnDirection_setDate_From',
					'label' => 'Начальная дата для диапазона дат направлений',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'EvnDirection_setDate_To',
					'label' => 'Конечная дата для диапазона дат направлений',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Идентификатор отделения',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaParEditForm' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Идентификатор параклинической услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaParViewForm' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Идентификатор параклинической услуги',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaParStreamList' => array(
				array(
					'field' => 'begDate',
					'label' => 'Дата начала ввода параклинических услуг',
					'rules' => 'trim',
					'type' => 'date'
				),
				array(
					'field' => 'begTime',
					'label' => 'Время начала ввода параклинических услуг',
					'rules' => '',
					'type' => 'time_with_seconds'
				),
				array(
					'default' => 0,
					'field' => 'start',
					'label' => 'Номер стартовой записи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'default' => 100,
					'field' => 'limit',
					'label' => 'Количество записей',
					'rules' => '',
					'type' => 'id'
				)
			),
			'saveEvnUslugaPar' => array(
				array('field' => 'EvnDirection_Num','label' => 'Номер направления','rules' => 'trim','type' => 'string'),
				array('field' => 'EvnDirection_setDate','label' => 'Дата направления','rules' => 'trim','type' => 'date'),
				array('field' => 'EvnUslugaPar_id','label' => 'Идентификатор параклинической услуги','rules' => '','type' => 'id'),
				array('field' => 'EvnUslugaPar_pid','label' => 'Идентификатор родительского события','rules' => '','type' => 'id'),
				array('field' => 'EvnDirection_id','label' => 'Идентификатор направления','rules' => '','type' => 'id'),
				array('field' => 'TimetablePar_id','label' => 'Идентификатор бирки','rules' => 'trim','type' => 'id'),// Обязателен при заполнении услуги заказанной по записи
				array('field' => 'EvnUslugaPar_isCito','label' => 'Признак срочности','rules' => 'trim','type' => 'id'),
				array('field' => 'EvnUslugaPar_Kolvo','label' => 'Количество','rules' => '','type' => 'int'),
				array('field' => 'EvnUslugaPar_setDate','label' => 'Дата начала оказания услуги','rules' => 'trim|required','type' => 'date'),
				array('field' => 'EvnUslugaPar_setTime','label' => 'Время начала оказания услуги','rules' => 'trim','type' => 'time'),
				array('field' => 'EvnUslugaPar_disDate','label' => 'Дата окончания оказания услуги','rules' => 'trim','type' => 'date'),
				array('field' => 'EvnUslugaPar_disTime','label' => 'Время окончания оказания услуги','rules' => 'trim','type' => 'time'),
				array('field' => 'Lpu_uid','label' => 'МО','rules' => '','type' => 'id'),
				array('field' => 'LpuSectionProfile_id','label' => 'Профиль','rules' => '','type' => 'id'),
				array('field' => 'MedSpecOms_id','label' => 'Специальность','rules' => '','type' => 'id'),
				array('field' => 'EvnUslugaPar_MedPersonalCode','label' => 'Код врача','rules' => '','type' => 'string'),
				array('field' => 'Lpu_did','label' => 'Идентификатор направившего ЛПУ','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_did','label' => 'Идентификатор направившего отделения','rules' => '','type' => 'id'),
				array('field' => 'LpuSection_uid','label' => 'Отделение','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_did','label' => 'Направивший врач','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_uid','label' => 'Врач','rules' => '','type' => 'id'),
				array('field' => 'MedPersonal_sid','label' => 'Код и ФИО среднего мед. персонала','rules' => '','type' => 'id'),
				array('field' => 'MedStaffFact_uid','label' => 'Рабочее место врача','rules' => '','type' => 'id'),
				array('field' => 'Org_did','label' => 'Идентификатор направившей организации','rules' => '','type' => 'id'),
				array('field' => 'Org_uid','label' => 'Идентификатор другой направившей организации','rules' => '','type' => 'id'),
				array('field' => 'PayType_id','label' => 'Вид оплаты','rules' => 'required','type' => 'id'),
				array('field' => 'Person_id','label' => 'Идентификатор пациента','rules' => 'required','type' => 'id'),
				array('field' => 'PersonEvn_id','label' => 'Идентификатор состояния пациента','rules' => 'required','type' => 'id'),
				array('field' => 'UslugaPlace_id','label' => 'Место выполнения','rules' => '','type' => 'id'),
				array('field' => 'PrehospDirect_id','label' => 'Кем направлен','rules' => '','type' => 'id'),
				array('field' => 'Server_id','label' => 'Идентификатор сервера','rules' => 'required','type' => 'int'),
				array('field' => 'Usluga_id','label' => 'Услуга','rules' => '','type' => 'id'),
				array('field' => 'UslugaComplex_id','label' => 'Услуга','rules' => 'required','type' => 'id'),
				array('field' => 'Diag_id', 'label' => 'Диагноз', 'rules' => '', 'type' => 'id'),
				array('field' => 'DeseaseType_id', 'label' => 'Характер', 'rules' => '', 'type' => 'id'),
				array('field' => 'TumorStage_id', 'label' => 'Стадия выявленного ЗНО', 'rules' => '', 'type' => 'id'),
				array('field' => 'Mes_id', 'label' => 'МЭС', 'rules' => '', 'type' => 'id'),
				array('field' => 'UslugaComplexTariff_id','label' => 'Тариф','rules' => '','type' => 'id'),
				array(
					'field' => 'EvnCostPrint_setDT',
					'label' => 'Дата выдачи справки/отказа',
					'rules' => '',
					'type' => 'date'
				),
				array(
					'field' => 'EvnCostPrint_IsNoPrint',
					'label' => 'Отказ',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnUslugaPar_IndexRep',
					'label' => 'Признак повторной подачи',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'EvnUslugaPar_IndexRepInReg',
					'label' => 'Признак повторной подачи в реестре',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'MedProductCard_id',
					'label' => 'Медицинское изделие',
					'rules' => '',
					'type' => 'id'
				),
				[
					'field' => 'FSIDI_id', 'label' => '', 'rules' => '', 'type' => 'id'
				]
			),
			'cancelDirection' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Идентификатор направления',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'EvnStatusCause_id',
					'label' => 'Причина отмены направления',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'EvnStatusHistory_Cause',
					'label' => 'Комментарий',
					'rules' => 'trim',
					'type' => 'string'
				)
			),
			'acceptWithoutRecord' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'MedPersonal_id',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_did',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuSection_id',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedStaffFact_id',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnit_did',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'id'
				)
			),
			'checkOpenEvnSection' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				)
			),
			'isOpenEvnSection' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				)
			),
			'recordPerson' => array(
				array(
					'field' => 'Person_id',
					'label' => 'Человек',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'MedService_id',
					'label' => 'Служба',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'LpuUnitType_SysNick',
					'label' => 'Тип',
					'rules' => '',
					'type' => 'string'
				)
			),
			'loadEvnUslugaParListByDirection' => array(
				array(
					'field' => 'EvnDirection_id',
					'label' => 'Направление',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'getLabStydyResultDoc' => array(
				array(
					'field' => 'EvnUslugaPar_id',
					'label' => 'Услуга',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'loadEvnUslugaParResults' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'checkForComplexUslugaList' => array(
				array(
					'field' => 'UslugaComplex_id',
					'label' => 'Идентификатор услуги',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Person_id',
					'label' => 'Идентификатор пациента',
					'rules' => 'required',
					'type' => 'id'
				)
			)
		);
		$this->load->database();
		$this->load->model('EvnUslugaPar_model', 'dbmodel');
	}


	/**
	*  Получение списка параклинических услуг для АРМа параклиники
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма АРМа параклиники
	*/
	function loadEvnUslugaParWorkPlace() {
		$data = $this->ProcessInputData('loadEvnUslugaParWorkPlace', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaParWorkPlace($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	*  Удаление параклинической услуги
	*  Входящие данные: $_POST['EvnUslugaPar_id']
	*  На выходе: JSON-строка
	*  Используется: форма поточного ввода параклинических услуг
	*/
	function deleteEvnUslugaPar() {
		$data = $this->ProcessInputData('deleteEvnUslugaPar', true);
		if ($data === false) { return false; }

		if (in_array(getRegionNick(), array('perm', 'kareliya','vologda'))) {
			// Цепляем реестровую БД
			$dbConnection = getRegistryChecksDBConnection();
			if ( $dbConnection != 'default' ) {
				$this->db = null;
				$this->load->database($dbConnection);
			}
			$this->load->model('Registry_model', 'Reg_model');

			$registryData = $this->Reg_model->checkEvnInRegistry($data);
			if ( !empty($registryData[0]['Error_Msg']) ) {
				$this->ReturnError($registryData[0]['Error_Msg']);
				return false;
			}

			if ( $dbConnection != 'default' ) {
				$this->db = null;
				$this->load->database();
			}
		}

		$response = $this->dbmodel->deleteEvnUslugaPar($data);
		$this->ProcessModelSave($response, true, 'При удалении параклинической услуги возникли ошибки')->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка направлений для выбранного отделения
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма поточного ввода параклинических услуг
	*/
	function loadEvnDirectionList() {
		$data = $this->ProcessInputData('loadEvnDirectionList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnDirectionList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение данных параклинической услуги для редактирования
	*  Входящие данные: $_POST['EvnUslugaPar_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования параклинической услуги
	*/
	function loadEvnUslugaParEditForm() {
		$data = $this->ProcessInputData('loadEvnUslugaParEditForm', true);
		if ($data === false) { return false; }

		$response = array();

		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$response = $this->lis->GET('EvnUsluga/ParEditForm', $data, 'list');
		}
		if (empty($response)) {
			$response = $this->dbmodel->loadEvnUslugaParEditForm($data);
		}

		$this->ProcessModelList($response, true)->ReturnData();
		return true;
	}
	
	/**
	*  Получение данных параклинической услуги для просмотра
	*  Входящие данные: $_POST['EvnUslugaPar_id']
	*  На выходе: JSON-строка с HTML в элементе 'html'
	*  Используется: форма просмотра и печати параклинической услуги
	*/
	function loadEvnUslugaParViewForm() {
		$val  = array();
		
		$this->load->helper("Xml");
		$this->load->library('parser');

		$data = $this->ProcessInputData('loadEvnUslugaParViewForm', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnUslugaParViewForm($data);
		
		if ( is_array($response) && count($response) > 0 ) {
			$val = "";
			if ( $response['html_template'] === false || $response['evnxml_data'] === false )
			{
				$val = "<div>Не найден шаблон отображения документа</div>";
			}
			else
			{
				if ($response[0]['UslugaComplex_id']>0) 
				{
					// если услуга комплексная, то читаем шаблон комплексной услуги 
					$response['evnxml_data'] = getHtmlTemplate($response['evnxml_data']);
					$response['evnxml_data'] = toUTF($response['evnxml_data']);
				}
				else 
				{
					$response['evnxml_data'] = toUTF($response['evnxml_data']);
					$doc_data = XmlToArray($response['evnxml_data']);
					array_walk_recursive($doc_data, 'ConvertFromUTF8ToWin1251');
					array_walk_recursive($doc_data, 'ReplaceRNToBr');
					$parse_data = $doc_data['data'];
					$parse_data['Lpu_Name_And_Addr'] = $response['usluga_data']['Lpu_Nick']."<br>".$response['usluga_data']['UAddress_Address'];
					$parse_data['lpu_section_name'] = $response['usluga_data']['LpuSection_Name'];
					$parse_data['med_personal_name'] = $response['usluga_data']['MedPersonal_FIO'];
					$parse_data['med_personal_code'] = $response['usluga_data']['MedPersonal_TabCode'];
					$parse_data['doc_number'] = $response['usluga_data']['EvnUslugaPar_Num'];
					$parse_data['usluga_date'] = $response['usluga_data']['EvnUslugaPar_setDate'];
					$parse_data['person_fio_dr_age'] = $response['usluga_data']['Person_FIO'].", ".$response['usluga_data']['Person_BirthDay'].", ".$response['usluga_data']['Person_Age'];
					$parse_data['person_sex'] = $response['usluga_data']['Sex_Name'];
					$parse_data['who_naprav'] = $response['usluga_data']['PrehospDirect_Name'];
					$parse_data['Usluga_Name'] = $response['usluga_data']['Usluga_Name'];
					$parse_data['cabinet_number'] = $response['usluga_data']['Cabinet_Num'];
					$parse_data['diag_code_name'] = $response['usluga_data']['Diag_Code']." ".$response['usluga_data']['Diag_Name'];
					$val = $this->parser->parse_string($response['html_template'], $parse_data, true);
				}
			}
			$val = toUTF($val);
			$this->ReturnData(array("success"=>true, "html" => $val));
		}		
		return true;
	}

	/**
	*  Получение списка параклинических услуг для поточного ввода
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма поточного ввода параклинических услуг
	*/
	function loadEvnUslugaParStreamList() {
		$data = $this->ProcessInputData('loadEvnUslugaParStreamList', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEvnUslugaParStreamList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	*  Сохранение параклинической услуги
	*  Входящие данные: ...
	*  На выходе: JSON-строка
	*  Используется: форма редактирования параклинической услуги
	*/
	function saveEvnUslugaPar() {
		$this->load->model("Org_model", "orgmodel");
		
		$data = $this->ProcessInputData('saveEvnUslugaPar', true);
		if ($data === false) { return false; }
		
		if ( (isset($data['PrehospDirect_id'])) && ($data['PrehospDirect_id'] == 2) ) {
			//$data['Lpu_did'] = $data['Org_did'];
			$response = $this->orgmodel->getLpuData(array('Org_id'=>$data['Org_did']));
			if (!empty($response[0]) && !empty($response[0]['Lpu_id'])) {
				$data['Lpu_did'] = $response[0]['Lpu_id'];
			}
			//$data['Org_did'] = NULL;
		}

		$isLis = false;
		/*
		проверки перед сохранением
		*/
		if(!empty($data['EvnUslugaPar_setDate']) && !empty($data['EvnUslugaPar_id']))
		{
			$this->load->model('EvnUsluga_model', 'eumodel');

			$checkDate = $this->eumodel->CheckEvnUslugaDate($data);
			if ( !empty($checkDate[0]['Error_Msg']) ) {
				$val = array('success' => false, 'Error_Msg' => $checkDate[0]['Error_Msg'], 'Error_Code' => $checkDate[0]['Error_Code']);
				$val['Alert_Msg'] = $this->eumodel->getAlertMsg();
				$this->ReturnData($val);
				return false;
			}

			$queue_data = false;
			//$data['TimetablePar_Day'] = TimeToDay(strtotime($data['EvnUslugaPar_setDate']));
			//print_r($data);
			$response = [];
			if ($this->usePostgreLis) {
				$this->load->swapi('lis');
				$response = $this->lis->GET('EvnUsluga/ParSaveData', array(
					'EvnUslugaPar_id' => $data['EvnUslugaPar_id'],
					'LpuSection_uid' => $data['LpuSection_uid']
				), 'list');
				$isLis = true;
			}
			if (empty($response) || (isset($response[0]) && empty($response[0]['EvnUslugaPar_id']))) {
				$response = $this->dbmodel->doSaveEvnUslugaPar($data);
				$isLis = false;
			}
			//print_r($response);
			if (is_array($response) && isset($response[0]))
			{
				$this->load->model('TimetableGraf_model', 'TimetableGraf_model');
				// Если разрешена запись, то записываем на свободную бирку или создаем дополнительную
				/*
				if($response[0]['allowApply'] == 2)
				{
					$apply_data = array(
						'object' => 'TimetablePar',
						'Person_id' => $response[0]['Person_id'],
						'pmUser_id' => $data['pmUser_id']
					);
					if (empty($response[0]['TimetablePar_id']))
					{
						//создаем дополнительную бирку
						$apply_data['Timetable_Day'] = $data['TimetablePar_Day'];
						$apply_data['LpuSection_id'] = $data['LpuSection_uid'];
						$apply_res = $this->TimetableGraf_model->Create($apply_data, false);
						if($apply_res == false || isset($apply_res[0]['Error_Msg']))
						{
							$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при записи параклинической услуги на дополнительную бирку.')));
							return false;
						}
						if (isset($apply_res[0]['TimetablePar_id']))
						{
							$apply_data['TimetablePar_id'] = $apply_res[0]['TimetablePar_id'];
							$data['TimetablePar_id'] = $apply_res[0]['TimetablePar_id'];
						}
					}
					else
					{
						//записываем на свободную бирку
						$apply_data['TimetablePar_id'] = $response[0]['TimetablePar_id'];
						$apply_res = $this->TimetableGraf_model->Apply($apply_data, false);
						if($apply_res == false)
						{
							$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при записи параклинической услуги на свободную бирку.')));
							return false;
						}
						$data['TimetablePar_id'] = $apply_data['TimetablePar_id'];
					}
				}
				*/

				// Если ранее было связанное направление, а сейчас его убрали, нужно вернуть ему прежний статус
				if (!empty($response[0]['UslugaEvnDirection_id']) && empty($data['EvnDirection_id'])) {
					if (!empty($response[0]['TimetablePar_id'])) {
						$EDEvnStatus_SysNick = 'DirZap';
					} else {
						$EDEvnStatus_SysNick = 'Queued';
					}
					if ($isLis) {
						$this->lis->PUT('Evn/Status', array(
							'Evn_id' => $response[0]['UslugaEvnDirection_id'],
							'EvnStatus_SysNick' => $EDEvnStatus_SysNick,
							'EvnClass_SysNick' => 'EvnDirection'
						));
					} else {
						$this->load->model('Evn_model', 'Evn_model');
						$this->Evn_model->updateEvnStatus(array(
							'Evn_id' => $response[0]['UslugaEvnDirection_id'],
							'EvnStatus_SysNick' => $EDEvnStatus_SysNick,
							'EvnClass_SysNick' => 'EvnDirection',
							'pmUser_id' => $data['pmUser_id']
						));
					}
				}

				// Если разрешена запись из очереди и запись завершилась успешно
				//if($response[0]['allowApplyFromQueue'] == 2 AND !empty($apply_data['TimetablePar_id']))
				// Если производится оказание услуги из очереди
				if($response[0]['allowApplyFromQueue'] == 2)
				{
					$queue_data = array(
						//'object' => 'TimetablePar',
						//'TimetablePar_id' => $apply_data['TimetablePar_id'],
						'EvnQueue_id' => $response[0]['EvnQueue_id'],
						'EvnQueue_recDT' => $data['EvnUslugaPar_setDate'],
						'EvnDirection_id' => $response[0]['EvnDirection_id'],
						'Direction_Num' => $response[0]['EvnDirection_Num'],
						'pmUser_id' => $data['pmUser_id']
					);
					
					$response = $this->TimetableGraf_model->ReceptionFromQueue($queue_data);
					if($response['success'] == false)
					{
						$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка при осуществлении процедуры оказания параклинической услуги из очереди.')));
						return false;
					}
				}
			}
			else
			{
				$this->ReturnData(array('success' => false, 'Error_Msg' => toUTF('Ошибка БД при получении данных о сохраняемой  параклинической услуге.')));
				return false;
			}
		}

		if ($isLis) {
			$response = $this->lis->POST('EvnUsluga/ParFull', $data, 'list');
			//если услуга есть в основной бд, то апдейтим её и там
			if (isset($data['EvnUslugaPar_id']) && $this->dbmodel->EvnUslugaParExustsInMainDb($data['EvnUslugaPar_id'])) {
				$this->dbmodel->saveEvnUslugaPar($data);
			}
		} else {
			$response = $this->dbmodel->saveEvnUslugaPar($data);
		}
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении параклинической услуги')->ReturnData();
		
		// Если есть направивший врач
		/*if( $data['MedPersonal_did'] > 0 ) { //закомментировал, т.к. реализована рассылка уведомлений (ниже)
			$data['EvnUslugaPar_id'] = ( $data['EvnUslugaPar_id'] > 0 ) ? $data['EvnUslugaPar_id'] : $response[0]['EvnUslugaPar_id'];
			// Получаем данные об услуге
			$uslData = $this->dbmodel->getUslugaParDataForNotice($data);
			
			$noticeData = array(
				'autotype' => 2
				,'Lpu_rid' => $data['Lpu_id']
				,'pmUser_id' => $data['pmUser_id']
				,'MedPersonal_rid' => $data['MedPersonal_did']
				,'type' => 1
				,'Evn_id' => $response[0]['EvnUslugaPar_id']
				,'title' => 'Выполнение исследования'
				,'text' => 'Исследование ' .$uslData['UslugaComplex_Name']. ', назначеннное пациенту ' .$uslData['Person_Fio'].', выполнено'
			);
			$this->load->model('Messages_model', 'Messages_model');
			$this->Messages_model->autoMessage($noticeData);
		}*/
		//рассылка уведомлений врачам о выполнении параклинической услуги 
		//в соответствии с настройками у каждого врача
		$this->load->helper('PersonNotice');
		$PersonNotice = new PersonNoticeEvn($data['Person_id'], 'EvnUslugaParPolka', $response[0]['EvnUslugaPar_id'], true);
		$PersonNotice->loadPersonInfo();
		//~ $PersonNotice->setEvnClassSysNick('EvnUslugaPar');
		//~ $PersonNotice->setEvnId($response[0]['EvnUslugaPar_id']);
		$PersonNotice->processStatusChange();//производим рассылку
		
		return true;
	}

	/**
	 * Отмена направления
	 */
	function cancelDirection() {
		$data = $this->ProcessInputData('cancelDirection', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->cancelDirection($data);
		$this->ProcessModelSave($response, true, 'При удалении услуги возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 *  Проверка причин закрытия
	 *  Входящие данные: id причины закрытия
	 *  На выходе: boolean - выводить ли ошибку
	 *  Используется: для проверки вывода ошибки при закрытой КВС/Движения
	 * @return bool
	 */
	function сheckingReasonForClosing($closeReasons){
		$error = false;
		switch (getRegionNick()) {
			case 'ufa':
			case 'ekb':
			case 'pskov':
				$error = true;
				break;
			case 'vologda':
			case 'khak':
				$error = (in_array($closeReasons, array('1', '2', '3', '6'))) ? true : false;
				break;
			case 'msk':
				$error = (!in_array($closeReasons, array('10', '11'))) ? true : false;
				break;
		}
		return $error;
	}

	/**
	 *  Проверка разрешена ли пациенту запись на прием в консультационный кабинет и выдача id КВС/Движения в случае если разрешена
	 *  Входящие данные: $data
	 *  На выходе: id движения/квс или ошибка
	 *  Используется: АРМ врача службы консультационной услуги
	 * @return json array
	 */
	function isOpenEvnSection($data) {
		$this->load->model('EvnSection_model', 'esmodel');

		$isPriemSection = $this->esmodel->isPriemSection($this->ProcessInputData('isOpenEvnSection', true));

		$evnSectionParam = $this->esmodel->paramEvnSection($data);
		$isEvnSection  = (!empty($evnSectionParam['EvnSection_id'])) ? true : false;
		$isClosed = (!empty($evnSectionParam['EvnSection_disDate'])) ? true : false;
		$EvnSection_id = $evnSectionParam['EvnSection_id'];
		$EvnSection_pid = $evnSectionParam['EvnSection_pid'];
		$esDiag_id = $evnSectionParam['Diag_id'];

		$EvnPS = $this->esmodel->paramEvnPS($data);
		$EvnPS_id = $EvnPS['EvnPS_id'];
		$epsDiag_id = $EvnPS['Diag_id'];
		$isEvnPS  = (!empty($EvnPS['EvnPS_id'])) ? true : false;
		$isClosedEvnPS = (!empty($EvnPS['EvnPS_disDate'])) ? true : false;

		$returnerror = false;
		$ESAnswer = false;

		if($isEvnSection && !$isClosed){
			$ESAnswer = $EvnSection_id;
			$Diag_id = $esDiag_id;
		}else if($isEvnSection && $isClosed && $isPriemSection) {
			$closeReasons = $this->esmodel->closeReasons($EvnSection_pid);
			$Diag_id = $esDiag_id;
			if ($closeReasons > 0) {
				if (in_array(getRegionNick(), array('ufa', 'ekb', 'vologda', 'khak', 'msk', 'pskov'))) {
					$isLink = $this->esmodel->isLink($EvnSection_pid);
					if (!$isLink) {
						$error = $this->сheckingReasonForClosing($closeReasons);
						if ($error = false)
							$ESAnswer = $EvnSection_id;
						else
							$returnerror = 'Необходимо создание посещения при отказе от госпитализации';
					} else
						$ESAnswer = $isLink;
				} else
					$ESAnswer = $EvnSection_id;
			} else
				$returnerror = 'Пациент не находится на стац лечении в текущей МО';
		}else if($isEvnPS && $isClosedEvnPS && !$isEvnSection && $isPriemSection) {
			$closeReasons = $this->esmodel->closeReasons($EvnPS_id);
			$Diag_id = $epsDiag_id;
			if ($closeReasons > 0) {
				if (in_array(getRegionNick(), array('ufa', 'ekb', 'vologda', 'khak', 'msk', 'pskov'))) {
					$isLink = $this->esmodel->isLink($EvnPS_id);
					if (!$isLink) {
						$error = $this->сheckingReasonForClosing($closeReasons);
						if ($error = false)
							$ESAnswer = $EvnSection_id;
						else
							$returnerror = 'Необходимо создание посещения при отказе от госпитализации.';
					} else
						$ESAnswer = $isLink;
				} else
					$ESAnswer = $EvnSection_id;
			} else
				$returnerror = 'Пациент не находится на стац лечении в текущей МО';
		}else
			$returnerror = 'Пациент не находится на стац лечении в текущей МО.';

		if($returnerror != false)
			return array('type'=>'error','value'=>$returnerror);
		else
			return array('type'=>'success','value'=>$ESAnswer,'Diag_id'=>$Diag_id);
	}

	/**
	 * Конвертирует PHP представление времени в идентификатор дня
	 */
	function TimeToDay( $nTime ) {
		$SECONDS_PER_DAY = 24 * 60 * 60;
		$arDate = getdate($nTime);
		$nGmtTime = gmmktime($arDate['hours'], $arDate['minutes'], $arDate['seconds'], $arDate['mon'], $arDate['mday'], $arDate['year']);
		$nTime += ( $nGmtTime - $nTime );
		return floor($nTime / $SECONDS_PER_DAY) + ( 36526 - 10956 ) - 2;
	}

	/**
	 *  Проверка разрешена ли пациенту запись на прием в консультационный кабинет и выдача id КВС/Движения в случае если разрешена
	 *  Входящие данные: $data
	 *  На выходе: id движения/квс или ошибка
	 *  Используется: АРМ врача службы консультационной услуги
	 * @return json array
	 */
	function checkOpenEvnSection(){
		$data = $this->ProcessInputData('checkOpenEvnSection', true);
		if(isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac') {
			$result = $this->isOpenEvnSection($data);
			if($result['type']=='success') {
				$EvnSection_id = $result['value'];
				echo "{'success' : 'true','EvnSection_id' : '".$EvnSection_id ."' }";
			}else if($result['type']=='error'){
				echo "{'error' : 'true','Error_Msg' : '".$result['value']."'}";
			}else{
				echo "{'error' : 'true','Error_Msg' : 'Ошибка проверки движения в стационаре'}";
			}
		}else{
			echo "{'success' : 'true'}";
		}
	}

	/**
	 * Прием без записи. Выношу отдельно, ибо нужно сразу несколько действий выполнить
	 */
	function acceptWithoutRecord () {

		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['acceptWithoutRecord'] = array_merge($this->inputRules['acceptWithoutRecord'], $this->inputRules['saveEvnUslugaPar']);
		$this->inputRules['acceptWithoutRecord'] = array_merge($this->inputRules['acceptWithoutRecord'], $this->EvnDirection->getSaveRules());
		$data = $this->ProcessInputData('acceptWithoutRecord', true);
		if ($data === false) { return false; }

		if(isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac'){
			//Создаем бирку
			$this->load->model('TimetableMedService_model', 'tmsmodel');
			$data['TimetableExtend_Descr'] = 'Прием без записи';
			$data['Day'] = $this->TimeToDay(time());
			$data['date'] = time();
			$data['StartTime'] = date("H:i");

			$rec = $this->tmsmodel->addTTMSDop($data);
			$data['TimetableMedService_id'] = $rec['TimetableMedService_id'];

			//Создаем направление
			$result = $this->isOpenEvnSection($data);
			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$data['Diag_id'] = $result['Diag_id'];

			$data['EvnDirection_pid'] = $result['value'];

		}
		$response = $this->dbmodel->acceptWithoutRecord($data);

		$this->ProcessModelSave($response, true, 'При сохранении услуги возникли ошибки')->ReturnData();
		if(!empty($EvnSection_id))
			echo '{"success":"true","EvnSection_id":"'.$EvnSection_id.'"}';
		else
			return true;
	}

	/**
	 * запись человека
	 */
	function recordPerson () {

		$this->load->model('EvnDirection_model', 'EvnDirection');
		$this->inputRules['recordPerson'] = array_merge($this->inputRules['recordPerson'], $this->inputRules['saveEvnUslugaPar']);
		$this->inputRules['recordPerson'] = array_merge($this->inputRules['recordPerson'], $this->EvnDirection->getSaveRules());
		$data = $this->ProcessInputData('recordPerson', true);
		if ($data === false) { return false; }

		if(isset($data['LpuUnitType_SysNick']) && $data['LpuUnitType_SysNick'] == 'stac'){
			$result = $this->isOpenEvnSection($data);

			$data['MedPersonal_id'] = $data['session']['medpersonal_id'];
			$data['Diag_id'] = $result['Diag_id'];

			$data['EvnDirection_pid'] = $result['value'];
		}

		$response = $this->dbmodel->recordPerson($data);
		$this->ProcessModelSave($response, true, 'При сохранении услуги возникли ошибки')->ReturnData();

		return true;
	}

	/**
	 * Загрузка пар услуг по направлению (используется в патологогистологии)
	 */
	function loadEvnUslugaParListByDirection() {
		$data = $this->ProcessInputData('loadEvnUslugaParListByDirection', true);
		if ($data === false) { return false; }
		
		$response = $this->dbmodel->loadEvnUslugaParListByDirection($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}

	/**
	 * Сохранение некоторых полей направления из формы параклинической услуги
	 */
	function updateEvnDirectionFields() {
		$data = $this->ProcessInputData('updateEvnDirectionFields', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->updateEvnDirectionFields($data);
		$this->ProcessModelSave($response, true)->ReturnData();

		return true;
	}

	/**
	 * Список связанных документов по исследованию
	 */
	function getLabStydyResultDoc() {
		$data = $this->ProcessInputData('getLabStydyResultDoc', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getLabStydyResultDoc($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}

	/**
	 *  Получение списка исследований в ЭМК
	 */
	function loadEvnUslugaParPanel() {
		$data = $this->ProcessInputData('loadEvnUslugaParPanel', true, true);
		if ( $data === false ) { return false; }

		$keys = [];
		$result = [];
		if ($this->usePostgreLis) {
			$this->load->swapi('lis');
			$result = $this->lis->GET('EvnUsluga/ParPanel', $data, 'single');
			foreach ($result['data'] as $resp) {
				$keys[] = $resp['EvnUslugaPar_id'];
			}
		}
		$responseDB = $this->dbmodel->loadEvnUslugaParPanel($data);
		if (empty($result['data'])) {
			$result = $responseDB;
		} else {
			foreach ($responseDB['data'] as $resp) {
				if (!in_array($resp['EvnUslugaPar_id'], $keys)) {
					$result['data'][] = $resp;
				}
			}
		}		
		$result['totalCount'] = count($result['data']);

		$this->ProcessModelMultiList($result, true, true)->ReturnData();
		return true;
	}
	/**
	 *  Получение списка результатов исследований в ЭМК
	 */
	function loadEvnUslugaParResults() {
		$data = $this->ProcessInputData('loadEvnUslugaParResults', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnUslugaParResults($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
	/**
	 *  Получение списка результатов исследований в ЭМК
	 */
	function checkForComplexUslugaList() {
		$data = $this->ProcessInputData('checkForComplexUslugaList', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->checkForComplexUslugaList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}