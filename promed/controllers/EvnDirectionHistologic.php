<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionHistologic - контроллер для работы с направлениями на патологогистологическое исследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      11.02.2011
 */
 
class EvnDirectionHistologic extends swController {
	public $inputRules = array(
		'deleteEvnDirectionHistologic' => array(
			array(
				'field' => 'EvnDirectionHistologic_id',
				'label' => 'Идентификатор направления на патологогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnDirectionHistologic' => array(
			array(
				'field' => 'EvnDirectionHistologic_id',
				'label' => 'Идентификатор направления на патологогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDirectionHistologicEditForm' => array(
			array(
				'field' => 'EvnDirectionHistologic_id',
				'label' => 'Идентификатор направления на патологогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDirectionHistologicGrid' => array(
			array(
				'field' => 'EvnDirectionHistologic_IsUrgent',
				'label' => 'Срочность',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_Num',
				'label' => 'Номер направления на патологогистологическое исследование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_Ser',
				'label' => 'Серия направления на патологогистологическое исследование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'begDate',
				'label' => 'Дата начала',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'endDate',
				'label' => 'Дата окончания',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnType_id',
				'label' => 'Состояние направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия',
				'rules' => 'trim',
				'type' => 'string'
			),
			// Параметры страничного вывода
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
		'loadEvnDirectionHistologicList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnDirectionHistologic' => array(
            array(
                'field' => 'EvnDirectionHistologic_Descr',
                'label' => 'Обоснование направления',
                'rules' => 'trim',
                'type' => 'string'
            ),
            array(
                'field' => 'EvnDirectionHistologic_LawDocumentDate',
                'label' => 'Дата документа правоохранительных органов',
                'rules' => 'trim',
                'type' => 'date'
            ),
            array(
                'field' => 'Org_sid',
                'label' => 'Организация',
                'rules' => '',
                'type' => 'id'
            ),
			array(
				'field' => 'BiopsyOrder_id',
				'label' => 'Биопсия',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BiopsyReceive_id',
				'label' => 'Способ получения материала',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'BiopsyStudyType_ids',
				'label' => 'Задачи прижизненного патолого-анатомического исследования биопсийного (операционного) материала',
				'rules' => '',
				'type' => 'multipleid'
			),
			array(
				'field' => 'Diag_id',
				'label' => 'Диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EDHUslugaComplex_id',
				'label' => 'Ссылка на услугу движения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_BiopsyDate',
				'label' => 'Дата первичной биопсии',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionHistologic_BiopsyNum',
				'label' => 'Номер первичной биопсии',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_ClinicalData',
				'label' => 'Клинические данные',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_ClinicalDiag',
				'label' => 'Клинический диагноз',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_didDate',
				'label' => 'Дата операции',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionHistologic_didTime',
				'label' => 'Время операции',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDirectionHistologic_id',
				'label' => 'Идентификатор направления на патологогистологическое исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_IsBad',
				'label' => 'Признак испорченного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_IsPlaceSolFormalin',
				'label' => 'Материал помещен в 10%-ный раствор нейтрального формалина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_IsUrgent',
				'label' => 'Срочность',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_Num',
				'label' => 'Номер направления на патологогистологическое исследование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_NumCard',
				'label' => 'Карта стационарного больного (амбулаторная карта)',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_ObjectCount',
				'label' => 'Число объектов',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnDirectionHistologic_Operation',
				'label' => 'Вид операции',
				'rules' => 'rtrim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_PredOperTreat',
				'label' => 'Проведенное предоперационное лечение',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_Ser',
				'label' => 'Серия направления на патологогистологическое исследование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionHistologic_setDate',
				'label' => 'Дата направления',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionHistologic_setTime',
				'label' => 'Время направления',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDirectionHistologic_SpecimenSaint',
				'label' => 'Маркировка материала',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'HistologicMaterial_id',
				'label' => 'Материал',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_aid',
				'label' => 'ЛПУ',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_did',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_LpuSectionName',
				'label' => 'Отделение',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'assoc' => true,
				'field' => 'MarkingBiopsyData',
				'label' => 'Маркировка материала',
				'rules' => '',
				'type' => 'json_array'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Лечащий врач',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_MedPersonalFIO',
				'label' => 'Лечащий врач',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'PersonEvn_id',
				'label' => 'Идентификатор состояния человека',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'TimetableGraf_id',
				'label' => 'Идентификатор бирки поликлиники',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'TimetableStac_id',
				'label' => 'Идентификатор бирки стационара',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_pid',
				'label' => 'Идентификатор',
				'rules' => '',
				'type' => 'id'
			)
		),
		'setEvnDirectionHistologicIsBad' => array(
			array(
				'field' => 'EvnDirectionHistologic_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionHistologic_IsBad',
				'label' => 'Признак испорченного направления',
				'rules' => 'required',
				'type' => 'int'
			),
			array(
				'field' => 'EvnStatusCause_id',
				'label' => 'Причина',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnStatusHistory_Cause',
				'label' => 'Комментарий',
				'rules' => '',
				'type' => 'string'
			)
		),
		'loadEDHUslugaComplexCombo' => array(
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => 'trim',
				'type' => 'id'
			)
		)
	);


	/**
	 * comment
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnDirectionHistologic_model', 'dbmodel');
	}


	/**
	*  Удаление направления на патологогистологическое исследование
	*  Входящие данные: $_POST['EvnDirectionHistologic_id']
	*  На выходе: JSON-строка
	*  Используется: журнал направлений на патологогистологическое исследование
	*/
	function deleteEvnDirectionHistologic() {
		$data = $this->ProcessInputData('deleteEvnDirectionHistologic', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteEvnDirectionHistologic($data);
		$this->ProcessModelSave($response, true, 'При удалении направления на патологогистологическое исследование возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Получение номера направления на патологогистологическое исследование
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патологогистологическое исследование
	*/
	function getEvnDirectionHistologicNumber() {
		$val  = array();
		$data = getSessionParams();

		$result = $this->dbmodel->getEvnDirectionHistologicNumber($data);
		if ( is_array($result) && count($result) > 0 ) {
			$val['EvnDirectionHistologic_Num'] = $result[0]['rnumber'];
		}
		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования направления на патологогистологическое исследование
	*  Входящие данные: $_POST['EvnDirectionHistologic_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патологогистологическое исследование
	*/
	function loadEvnDirectionHistologicEditForm() {
		$val  = array();

		$data = $this->ProcessInputData('loadEvnDirectionHistologicEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionHistologicEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка направлений на патологогистологическое исследование
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: журнал направлений на патологогистологическое исследование
	*/
	function loadEvnDirectionHistologicGrid() {
		$data = $this->ProcessInputData('loadEvnDirectionHistologicGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionHistologicGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка направлений на патологогистологическое исследование
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма выбора направления на патологогистологическое исследование
	*/
	function loadEvnDirectionHistologicList() {
		$data = $this->ProcessInputData('loadEvnDirectionHistologicList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionHistologicList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}


	/**
	*  Печать направления на патологогистологическое исследование
	*  Входящие данные: $_GET['EvnDirectionHistologic_id']
	*  На выходе: форма для печати направления на патологогистологическое исследование
	*  Используется: форма редактирования направления на патологогистологическое исследование
	*                журнал направлениий на патологогистологическое исследование
	*/
	function printEvnDirectionHistologic() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnDirectionHistologic', true);
		if ( $data === false ) { return false; }
		
		// Получаем данные по направлению
		$response = $this->dbmodel->getEvnDirectionHistologicFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по направлению на патологогистологическое исследование';
			return true;
		}

        $arMonthOf = array(
            1 => "января",
            2 => "февраля",
            3 => "марта",
            4 => "апреля",
            5 => "мая",
            6 => "июня",
            7 => "июля",
            8 => "августа",
            9 => "сентября",
            10 => "октября",
            11 => "ноября",
            12 => "декабря",
        );
		$underline = 'text-decoration: underline;';
		$template = 'print_evn_direction_histologic';

		$this->load->model('EvnDirection_model', 'edmodel');
		$lpuFedCode = '';
		$MedPersonalSnils = '';
		if ($this->edmodel->regionNick == 'perm') {
			$lpuFedCode = returnValidHTMLString($response[0]['Lpu_FedCode']);
			$MedPersonalSnils = returnValidHTMLString($response[0]['MedPersonal_Snils']);
		}

		$print_data = array(
			'BiopsyOrder_1' => '',
			'BiopsyOrder_2' => '',
			'EDH_BiopsyDate' => returnValidHTMLString($response[0]['EvnDirectionHistologic_BiopsyDate']),
			'EDH_BiopsyNum' => returnValidHTMLString($response[0]['EvnDirectionHistologic_BiopsyNum']),
			'EDH_ClinicalData_1' => '&nbsp;',
			'EDH_ClinicalData_2' => '&nbsp;',
			'EDH_ClinicalData_3' => '&nbsp;',
			'EDH_ClinicalData_4' => '&nbsp;',
			'EDH_ClinicalData_5' => '&nbsp;',
			'EDH_ClinicalData_6' => '&nbsp;',
			'EDH_ClinicalData_7' => '&nbsp;',
			'EDH_ClinicalDiag_1' => '&nbsp;',
			'EDH_ClinicalDiag_2' => '&nbsp;',
			'EDH_ClinicalDiag_3' => '&nbsp;',
			'EDH_Day' => returnValidHTMLString(sprintf('%02d', $response[0]['EvnDirectionHistologic_Day'])),
			'EDH_didDate' => returnValidHTMLString($response[0]['EvnDirectionHistologic_didDate']),
			'EDH_Hour' => sprintf('%02d', $response[0]['EvnDirectionHistologic_Hour']),
			'EDH_Marking_1' => '&nbsp;',
			'EDH_Marking_2' => '&nbsp;',
			'EDH_Month' => returnValidHTMLString(array_key_exists($response[0]['EvnDirectionHistologic_Month'], $arMonthOf) ? $arMonthOf[$response[0]['EvnDirectionHistologic_Month']] : ''),
			'EDH_Num' => returnValidHTMLString($response[0]['EvnDirectionHistologic_Num']),
			'EDH_NumCard' => returnValidHTMLString($response[0]['EvnDirectionHistologic_NumCard']),
			'EDH_Operation' => returnValidHTMLString($response[0]['EvnDirectionHistologic_Operation']),
			'EDH_Ser' => returnValidHTMLString($response[0]['EvnDirectionHistologic_Ser']),
			'EDH_Year' => returnValidHTMLString($response[0]['EvnDirectionHistologic_Year']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'Lpu_Code' => $lpuFedCode,
			'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonal_Snils' => $MedPersonalSnils,
			'Person_Age' => $response[0]['Person_Age'] > 0 ? $response[0]['Person_Age'] :'&nbsp;',
			'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
			'Sex_1' => '',
			'Sex_2' => ''
		);

		if ( !empty($response[0]['EvnDirectionHistologic_SpecimenSaint']) ) {
			$print_data['EDH_Marking_1'] = $response[0]['EvnDirectionHistologic_SpecimenSaint'] . ',&nbsp;&nbsp;&nbsp;';
		}

		if ( $response[0]['EvnDirectionHistologic_ObjectCount'] > 0 ) {
			$print_data['EDH_Marking_1'] .= $response[0]['EvnDirectionHistologic_ObjectCount'];
		}

		switch ( $response[0]['Sex_Code'] ) {
			case 1:
				$print_data['Sex_1'] = $underline;
			break;

			case 2:
				$print_data['Sex_2'] = $underline;
			break;
		}

		switch ( $response[0]['BiopsyOrder_Code'] ) {
			case 1:
				$print_data['BiopsyOrder_1'] = $underline;
			break;

			case 2:
				$print_data['BiopsyOrder_2'] = $underline;
			break;
		}

		$clinical_data = $response[0]['EvnDirectionHistologic_ClinicalData'];
		$clinical_diag = $response[0]['EvnDirectionHistologic_ClinicalDiag'];

		$clinical_data = preg_replace("/[ \n]+/", ' ', $clinical_data);
		$clinical_diag = preg_replace("/[ \n]+/", ' ', $clinical_diag);
		$clinical_data_array = explode(' ', $clinical_data);
		$clinical_diag_array = explode(' ', $clinical_diag);

		if ( count($clinical_data_array) > 0 ) {
			$limit = 68;
			$line = 1;
			$print_data['EDH_ClinicalData_1'] = "";

			for ( $i = 0; $i < count($clinical_data_array) && $line <= 7; $i++ ) {
				if ( strlen($print_data['EDH_ClinicalData_' . $line] . $clinical_data_array[$i] ) <= $limit ) {
					$print_data['EDH_ClinicalData_' . $line] .= $clinical_data_array[$i] . ' ';
				}
				else {
					$print_data['EDH_ClinicalData_' . $line] = returnValidHTMLString($print_data['EDH_ClinicalData_' . $line]);
					$line++;
					$limit = 98;
					$print_data['EDH_ClinicalData_' . $line] = $clinical_data_array[$i] . ' ';
				}
			}
		}

		if ( count($clinical_diag_array) > 0 ) {
			$limit = 68;
			$line = 1;
			$print_data['EDH_ClinicalDiag_1'] = "";

			for ( $i = 0; $i < count($clinical_diag_array) && $line <= 3; $i++ ) {
				if ( strlen($print_data['EDH_ClinicalDiag_' . $line] . $clinical_diag_array[$i] ) <= $limit ) {
					$print_data['EDH_ClinicalDiag_' . $line] .= $clinical_diag_array[$i] . ' ';
				}
				else {
					$print_data['EDH_ClinicalDiag_' . $line] = returnValidHTMLString($print_data['EDH_ClinicalDiag_' . $line]);
					$line++;
					$limit = 98;
					$print_data['EDH_ClinicalDiag_' . $line] = $clinical_diag_array[$i] . ' ';
				}
			}
		}

		return $this->parser->parse($template, $print_data);
	}


	/**
	*  Сохранение направления на патологогистологическое исследование
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патологогистологическое исследование
	*/
	function saveEvnDirectionHistologic() {
		$data = $this->ProcessInputData('saveEvnDirectionHistologic', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnDirectionHistologic($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении направления на патологогистологическое исследование')->ReturnData();
		
		return true;
	}


	/**
	*  Установка/снятие признака испорченного направления на патологогистологическое исследование
	*  Входящие данные: EvnDirectionHistologic_id, EvnDirectionHistologic_IsBad
	*  На выходе: JSON-строка
	*  Используется: журнал направлениий на патологогистологическое исследование
	*/
	function setEvnDirectionHistologicIsBad() {
		$val  = array();

		$data = $this->ProcessInputData('setEvnDirectionHistologicIsBad', true);
		if ( $data === false ) { return false; }

		switch ( $data['EvnDirectionHistologic_IsBad'] ) {
			case 0:
				$data['EvnDirectionHistologic_IsBad'] = 1;
				$data['pmUser_pid'] = NULL;
			break;

			case 1:
				$data['EvnDirectionHistologic_IsBad'] = 2;
				$data['pmUser_pid'] = $data['pmUser_id'];
			break;

			default:
				echo json_return_errors('Неверное значение признака испорченного направления на патологогистологическое исследование');
				return false;
			break;
		}

		$response = $this->dbmodel->setEvnDirectionHistologicIsBad($data);
		$this->ProcessModelSave($response, true, 'Ошибка при установке/снятии признака испорченного направления на патологогистологическое исследование')->ReturnData();
		
		return true;
	}

	/**
	* Получение комбо услуг на форме выписки направления на патоморфогистологическое исследование
	*/
	function loadEDHUslugaComplexCombo() {
		$data = $this->ProcessInputData('loadEDHUslugaComplexCombo', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->loadEDHUslugaComplexCombo($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}
}