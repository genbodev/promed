<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnDirectionMorfoHistologic - контроллер для работы с направлениями на патоморфогистологическое исследование
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Polka
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      14.02.2011
 *
 * @property EvnDirectionMorfoHistologic_model $dbmodel
 */

class EvnDirectionMorfoHistologic extends swController {
	public $inputRules = array(
		'deleteEvnDirectionMorfoHistologic' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления на патоморфогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'printEvnDirectionMorfoHistologic' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления на патоморфогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'deleteEvnDirectionMorfoHistologicItems' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologicItems_id',
				'label' => 'Идентификатор записи о прилагаемом документе или предмете',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDirectionMorfoHistologicEditForm' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления на патоморфогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDirectionMorfoHistologicGrid' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_Num',
				'label' => 'Номер направления на патоморфогистологическое исследование',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_Ser',
				'label' => 'Серия направления на патоморфогистологическое исследование',
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
		'loadEvnDirectionMorfoHistologicItemsGrid' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления на патоморфогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnDirectionMorfoHistologicList' => array(
			array(
				'field' => 'Person_id',
				'label' => 'Идентификатор пациента',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'saveEvnDirectionMorfoHistologic' => array(
			array(
				'field' => 'Diag_id',
				'label' => 'Основной диагноз',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_oid',
				'label' => 'Осложнение основного диагноза',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_sid',
				'label' => 'Сопутствующий диагноз',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_deathDate',
				'label' => 'Дата смерти',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_deathTime',
				'label' => 'Время смерти',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_Descr',
				'label' => 'Обоснование направления',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления на патоморфогистологическое исследование',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_IsBad',
				'label' => 'Признак испорченного направления',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_Num',
				'label' => 'Номер направления на патоморфогистологическое исследование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_Phone',
				'label' => 'Телефон отделения',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_Ser',
				'label' => 'Серия направления на патоморфогистологическое исследование',
				'rules' => 'trim|required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_setDate',
				'label' => 'Дата направления',
				'rules' => 'trim|required',
				'type' => 'date'
			),
            array(
                'field' => 'EvnDirectionMorfoHistologic_LawDocumentDate',
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
			array (
				'default' => '[]',
				'field' => 'EvnDirectionMorfoHistologicItemsList',
				'label' => 'Список прилагаемых документов и ценностей',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Карта стационарного больного',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_LpuSectionName',
				'label' => 'Отделение',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Медицинский работник',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_MedPersonalFIO',
				'label' => 'Медицинский работник',
				'rules' => '',
				'type' => 'string'
			),
			array(
				'field' => 'Lpu_did',
				'label' => 'Куда направлен',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Lpu_sid',
				'label' => 'Направившая МО',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'OrgAnatom_did',
				'label' => 'Куда направлен',
				'rules' => '',
				'type' => 'id'
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
				'field' => 'PrehospType_did',
				'label' => 'Тип госпитализации',
				'rules' => '',
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
			)
		),
		'setEvnDirectionMorfoHistologicIsBad' => array(
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_IsBad',
				'label' => 'Признак испорченного направления',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'checkEvnDirectionMorfoHistologic' => array(
			array(
				'field' => 'EvnSection_pid',
				'label' => 'Идентификатор движения',
				'rules' => 'required',
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
		$this->load->model('EvnDirectionMorfoHistologic_model', 'dbmodel');
	}


	/**
	*  Удаление направления на патоморфогистологическое исследование
	*  Входящие данные: $_POST['EvnDirectionMorfoHistologic_id']
	*  На выходе: JSON-строка
	*  Используется: журнал направлений на патоморфогистологическое исследование
	*/
	function deleteEvnDirectionMorfoHistologic() {
		$data = $this->ProcessInputData('deleteEvnDirectionMorfoHistologic', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteEvnDirectionMorfoHistologic($data);
		$this->ProcessModelSave($response, true, 'При удалении направления на патоморфогистологическое исследование возникли ошибки')->ReturnData();

		return true;
	}


	/**
	*  Удаление записи о прилагаемом документе или предмете
	*  Входящие данные: $_POST['EvnDirectionMorfoHistologicItems_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*/
	function deleteEvnDirectionMorfoHistologicItems() {
		$data = $this->ProcessInputData('deleteEvnDirectionMorfoHistologicItems', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteEvnDirectionMorfoHistologicItems($data);
		$this->ProcessModelSave($response, true, 'При удалении записи о прилагаемом документе или предмете возникли ошибки')->ReturnData();
		
		return true;
	}


	/**
	*  Получение номера направления на патоморфогистологическое исследование
	*  Входящие данные: нет
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*/
	function getEvnDirectionMorfoHistologicNumber() {
		$val  = array();
		$data = getSessionParams();

		$result = $this->dbmodel->getEvnDirectionMorfoHistologicNumber($data);
		if ( is_array($result) && count($result) > 0 ) {
			$val['EvnDirectionMorfoHistologic_Num'] = sprintf('%07d', $result[0]['rnumber']);
		}
		$this->ReturnData($val);

		return true;
	}


	/**
	*  Получение данных для формы редактирования направления на патоморфогистологическое исследование
	*  Входящие данные: $_POST['EvnDirectionMorfoHistologic_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*/
	function loadEvnDirectionMorfoHistologicEditForm() {
		$data = $this->ProcessInputData('loadEvnDirectionMorfoHistologicEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionMorfoHistologicEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка направлений на патоморфогистологическое исследование
	*  Входящие данные: <фильтры>
	*  На выходе: JSON-строка
	*  Используется: журнал направлений на патоморфогистологическое исследование
	*/
	function loadEvnDirectionMorfoHistologicGrid() {
		$data = $this->ProcessInputData('loadEvnDirectionMorfoHistologicGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionMorfoHistologicGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	*  Получение списка прилагаемых документов и предметов
	*  Входящие данные: $_POST['EvnDirectionMorfoHistologic_id']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*/
	function loadEvnDirectionMorfoHistologicItemsGrid() {
		$data = $this->ProcessInputData('loadEvnDirectionMorfoHistologicItemsGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionMorfoHistologicItemsGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	*  Получение списка направлений на патоморфогистологическое исследование
	*  Входящие данные: $_POST['Person_id']
	*  На выходе: JSON-строка
	*  Используется: форма выбора направления на патоморфогистологическое исследование
	*/
	function loadEvnDirectionMorfoHistologicList() {
		$data = $this->ProcessInputData('loadEvnDirectionMorfoHistologicList', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnDirectionMorfoHistologicList($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return false;
	}


	/**
	*  Печать направления на патоморфогистологическое исследование
	*  Входящие данные: $_GET['EvnDirectionMorfoHistologic_id']
	*  На выходе: форма для печати направления на патоморфогистологическое исследование
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*                журнал регистрации направлениий на патоморфогистологическое исследование
	*/
	function printEvnDirectionMorfoHistologic() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnDirectionMorfoHistologic', true);
		if ( $data === false ) { return false; }

		// Получаем данные по направлению
		$response = $this->dbmodel->getEvnDirectionMorfoHistologicFields($data);

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по направлению на патоморфогистологическое исследование';
			return true;
		}

		$evn_direction_morfo_histologic_items_data_1 = array();
		$evn_direction_morfo_histologic_items_data_2 = array();

		$response_temp = $this->dbmodel->getEvnDirectionMorfoHistologicItemsData($data);
		if ( is_array($response_temp) ) {
			$j = 0;
			$k = 0;

			for ( $i = 0; $i < count($response_temp); $i++ ) {
				switch ( $response_temp[$i]['MorfoHistologicItemsType_Code'] ) {
					case 1:
					case 2:
						$j++;
						$evn_direction_morfo_histologic_items_data_1[] = array(
							'Record_Num' => $j,
							'EvnDirectionMorfoHistologicItems_Descr' => $response_temp[$i]['EvnDirectionMorfoHistologicItems_Descr'],
							'EvnDirectionMorfoHistologicItems_Count' => $response_temp[$i]['EvnDirectionMorfoHistologicItems_Count'],
							'MorfoHistologicItemsType_Name' => ($response_temp[$i]['MorfoHistologicItemsType_Code'] == 1 ? 'листов' : 'снимков')
						);
					break;

					case 3:
						$k++;
						$evn_direction_morfo_histologic_items_data_2[] = array(
							'Record_Num' => $k,
							'EvnDirectionMorfoHistologicItems_Descr' => $response_temp[$i]['EvnDirectionMorfoHistologicItems_Descr'],
						);
					break;
				}
			}
		}

		if ( count($evn_direction_morfo_histologic_items_data_1) < 3 ) {
			for ( $i = count($evn_direction_morfo_histologic_items_data_1); $i < 3; $i++ ) {
				$evn_direction_morfo_histologic_items_data_1[] = array(
					'Record_Num' => $i + 1,
					'EvnDirectionMorfoHistologicItems_Descr' => '&nbsp;',
					'EvnDirectionMorfoHistologicItems_Count' => '&nbsp;&nbsp;&nbsp;&nbsp;',
					'MorfoHistologicItemsType_Name' => '&nbsp;'
				);
			}
		}

		if ( count($evn_direction_morfo_histologic_items_data_2) < 3 ) {
			for ( $i = count($evn_direction_morfo_histologic_items_data_2); $i < 3; $i++ ) {
				$evn_direction_morfo_histologic_items_data_2[] = array(
					'Record_Num' => $i + 1,
					'EvnDirectionMorfoHistologicItems_Descr' => '&nbsp;'
				);
			}
		}

		$lpuFedCode = '';
		$MedPersonalSnils = '';
		if($this->dbmodel->getRegionNick() == 'perm'){
			$lpuFedCode = returnValidHTMLString($response[0]['Lpu_FedCode']);
			$MedPersonalSnils = returnValidHTMLString($response[0]['MedPersonal_Snils']);
		}

		$template = 'print_evn_direction_morfo_histologic';

		$print_data = array(
			'deathDate' => returnValidHTMLString($response[0]['deathDate']),
			'deathHours' => returnValidHTMLString(sprintf('%02d', $response[0]['deathHours'])),
			'deathMinutes' => returnValidHTMLString(sprintf('%02d', $response[0]['deathMinutes'])),
			'DiagOsn_Code' => returnValidHTMLString($response[0]['DiagOsn_Code']),
			'DiagOsl_Code' => returnValidHTMLString($response[0]['DiagOsl_Code']),
			'DiagSop_Code' => returnValidHTMLString($response[0]['DiagSop_Code']),
			'EvnDirectionMorfoHistologic_Descr1' => '&nbsp;',
			'EvnDirectionMorfoHistologic_Descr2' => '&nbsp;',
			'EvnDirectionMorfoHistologic_Num' => returnValidHTMLString($response[0]['EvnDirectionMorfoHistologic_Num']),
			'EvnDirectionMorfoHistologic_Phone' => returnValidHTMLString($response[0]['EvnDirectionMorfoHistologic_Phone']),
			'EvnDirectionMorfoHistologic_Ser' => returnValidHTMLString($response[0]['EvnDirectionMorfoHistologic_Ser']),
			'EvnDirectionMorfoHistologicItems1' => $evn_direction_morfo_histologic_items_data_1,
			'EvnDirectionMorfoHistologicItems2' => $evn_direction_morfo_histologic_items_data_2,
			'EvnPS_dateRange' => returnValidHTMLString($response[0]['EvnPS_dateRange']),
			'GlavVrach_Fio' => returnValidHTMLString($response[0]['GlavVrach_Fio']),
			'Lpu_Address' => str_replace('РОССИЯ, ', '', str_replace('КРАЙ ПЕРМСКИЙ, ', '', str_replace('ПЕРМСКИЙ КРАЙ, ', '', returnValidHTMLString($response[0]['Lpu_Address'])))),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'Lpu_Code' => $lpuFedCode,
			'Lpu_OGRN' => returnValidHTMLString($response[0]['Lpu_OGRN']),
			'LpuSection_Name' => returnValidHTMLString($response[0]['LpuSection_Name']),
			'LpuSectionProfile_Name' => returnValidHTMLString($response[0]['LpuSectionProfile_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonal_Snils' => $MedPersonalSnils,
			'OrgAnatom_Name' => returnValidHTMLString($response[0]['OrgAnatom_Name']),
			'OrgSmo_Name' => returnValidHTMLString($response[0]['OrgSmo_Name']),
			'Person_Address' => str_replace('РОССИЯ, ', '', str_replace('КРАЙ ПЕРМСКИЙ, ', '', str_replace('ПЕРМСКИЙ КРАЙ, ', '', returnValidHTMLString($response[0]['Person_Address'])))),
			'Person_Birthday' => returnValidHTMLString($response[0]['Person_Birthday']),
			'Person_Firname' => returnValidHTMLString($response[0]['Person_Firname']),
			'Person_Secname' => returnValidHTMLString($response[0]['Person_Secname']),
			'Person_Surname' => returnValidHTMLString($response[0]['Person_Surname']),
			'Polis_begDate' => returnValidHTMLString($response[0]['Polis_begDate']),
			'Polis_Num' => returnValidHTMLString($response[0]['Polis_Num']),
			'Polis_Ser' => returnValidHTMLString($response[0]['Polis_Ser']),
			'Post_Name' => returnValidHTMLString($response[0]['Post_Name']),
			'PrehospType_Name' => returnValidHTMLString($response[0]['PrehospType_Name'])
		);

		$descr_data = $response[0]['EvnDirectionMorfoHistologic_Descr'];

		$descr_data_array = explode(' ', $descr_data);

		if ( count($descr_data_array) > 0 ) {
			$limit = 68;
			$line = 1;
			$print_data['EvnDirectionMorfoHistologic_Descr1'] = "";

			for ( $i = 0; $i < count($descr_data_array) && $line <= 2; $i++ ) {
				if ( strlen($print_data['EvnDirectionMorfoHistologic_Descr' . $line] . $descr_data_array[$i] ) <= $limit ) {
					$print_data['EvnDirectionMorfoHistologic_Descr' . $line] .= $descr_data_array[$i] . ' ';
				}
				else {
					$print_data['EvnDirectionMorfoHistologic_Descr' . $line] = returnValidHTMLString($print_data['EvnDirectionMorfoHistologic_Descr' . $line]);
					$line++;
					$limit = 98;
					$print_data['EvnDirectionMorfoHistologic_Descr' . $line] = $descr_data_array[$i] . ' ';
				}
			}
		}

		return $this->parser->parse($template, $print_data);
	}


	/**
	*  Сохранение направления на патоморфогистологическое исследование
	*  Входящие данные: <поля формы>
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*/
	function saveEvnDirectionMorfoHistologic() {
		$data = $this->ProcessInputData('saveEvnDirectionMorfoHistologic', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnDirectionMorfoHistologic($data);
		$this->ProcessModelSave($response, true, 'Ошибка при сохранении направления на патоморфогистологическое исследование')->ReturnData();

		return true;
	}


	/**
	*  Установка/снятие признака испорченного направления на патоморфогистологическое исследование
	*  Входящие данные: EvnDirectionMorfoHistologic_id, EvnDirectionMorfoHistologic_IsBad
	*  На выходе: JSON-строка
	*  Используется: журнал направлениий на патоморфогистологическое исследование
	*/
	function setEvnDirectionMorfoHistologicIsBad() {
		$val  = array();

		$data = $this->ProcessInputData('setEvnDirectionMorfoHistologicIsBad', true);
		if ( $data === false ) { return false; }

		switch ( $data['EvnDirectionMorfoHistologic_IsBad'] ) {
			case 0:
				$data['EvnDirectionMorfoHistologic_IsBad'] = 1;
				$data['pmUser_pid'] = NULL;
			break;

			case 1:
				$data['EvnDirectionMorfoHistologic_IsBad'] = 2;
				$data['pmUser_pid'] = $data['pmUser_id'];
			break;

			default:
				echo json_return_errors('Неверное значение признака испорченного направления на патоморфогистологическое исследование');
				return false;
			break;
		}

		$response = $this->dbmodel->setEvnDirectionMorfoHistologicIsBad($data);
		$this->ProcessModelSave($response, true, 'Ошибка при установке/снятии признака испорченного направления на патоморфогистологическое исследование')->ReturnData();
		
		return true;
	}


	/**
	*  Проверка возможности выписки направления на патоморфогистологическое исследование
	*  Входящие данные: $_POST['EvnSection_pid']
	*  На выходе: JSON-строка
	*  Используется: форма редактирования направления на патоморфогистологическое исследование
	*/
	function checkEvnDirectionMorfoHistologic() {
		$data = $this->ProcessInputData('checkEvnDirectionMorfoHistologic', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->getFirstResultFromQuery("select top 1 * from v_EvnDirectionMorfoHistologic with (nolock) where EvnPS_id = (select top 1 EvnPS_id from v_EvnPS with (nolock) where EvnPS_rid = :EvnSection_pid)", $data);
		//var_dump($response);
		$this->ReturnData(array('success' => true, 'response' => $response));
		return true;
	}

}