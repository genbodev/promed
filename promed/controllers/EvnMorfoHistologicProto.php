<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * EvnMorfoHistologicProto - контроллер для работы с протоколами патоморфогистологического исследования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Stac
 * @access       public
 * @copyright    Copyright (c) 2009-2011 Swan Ltd.
 * @author       SWAN developers
 * @version      14.02.2011
 * 
 * @property EvnMorfoHistologicProto_model $dbmodel
 */

class EvnMorfoHistologicProto extends swController {
	public $inputRules = array(
		'deleteEvnMorfoHistologicProto' => array(
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола патоморфогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnMorfoHistologicDiagDiscrepancyGrid' => array(
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола патоморфогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnMorfoHistologicMemberGrid' => array(
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола патоморфогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnMorfoHistologicProtoEditForm' => array(
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола патоморфогистологического исследования',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'loadEvnMorfoHistologicProtoGrid' => array(
			array(
				'field' => 'EvnType_id',
				'label' => 'Состояние протокола',
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
		'saveEvnMorfoHistologicProto' => array(
			array(
				'field' => 'DeathSvid_id',
				'label' => 'Идентификатор свидетельства о смерти',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PntDeathSvid_id',
				'label' => 'Идентификатор свидетельства о перинатальной смерти',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_did',
				'label' => 'Диагноз направившего учреждения',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_sid',
				'label' => 'Диагноз при поступлении',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_vid',
				'label' => 'Диагноз I. а)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_vid_Descr',
				'label' => 'Болезнь или состояние, непосредственно приведшие к смерти',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_wid',
				'label' => 'Диагноз б)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_wid_Descr',
				'label' => 'Патологическое состояние, которое привело к вышеуказанной причине',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_xid',
				'label' => 'Диагноз в)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_xid_Descr',
				'label' => 'Первоначальная причина смерти',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_yid',
				'label' => 'Диагноз г)',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_yid_Descr',
				'label' => 'Внешняя причина смерти при травмах и отравлениях',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Diag_zid',
				'label' => 'Диагноз II.',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Diag_zid_Descr',
				'label' => 'Прочие важные состояния, способствовавшие смерти',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnDirectionMorfoHistologic_id',
				'label' => 'Идентификатор направления на патоморфогистологическое исследование',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'evnMorfoHistologicDiagDiscrepancyData',
				'label' => 'Список ошибок клинической диагностики',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'evnMorfoHistologicMemberData',
				'label' => 'Список присутствовавших при вскрытии',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_autopsyDate',
				'label' => 'Дата вскрытия',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_BitCount',
				'label' => 'Взято кусочков',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_BlockCount',
				'label' => 'Изготовлено блоков',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_BrainWeight',
				'label' => 'Масса мозга',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_deathDate',
				'label' => 'Дата смерти',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_deathTime',
				'label' => 'Время смерти',
				'rules' => 'trim',
				'type' => 'time'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_DiagDescr',
				'label' => 'Текст диагноза',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_DiagNameDirect',
				'label' => 'Диагноз направившего учреждения (текст)',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_DiagPathology',
				'label' => 'Патологоанатомический диагноз (основное заболевание, осложнения, сопутствующие заболевания)',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_DiagSetDate',
				'label' => 'Дата установления заключительного диагноза',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_DiagNameSupply',
				'label' => 'Диагноз при поступлении (текст)',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_Epicrisis',
				'label' => 'Клинико-патологоанатомический эпикриз',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_HeartWeight',
				'label' => 'Масса сердца',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола патоморфогистологического исследования',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_IsBad',
				'label' => 'Признак испорченного протокола',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_KidneyLeftWeight',
				'label' => 'Масса левой почки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_KidneyRightWeight',
				'label' => 'Масса правой почки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_LiverWeight',
				'label' => 'Масса печени',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_LungsWeight',
				'label' => 'Масса легких',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_MethodDescr',
				'label' => 'Взят материал для других методов исследования',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_Num',
				'label' => 'Номер протокола',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_ProtocolDescr',
				'label' => 'Текст протокола',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_ResultLabStudy',
				'label' => 'Результаты клинико-лабораторных исследований',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_Ser',
				'label' => 'Серия протокола',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_setDate',
				'label' => 'Дата составления протокола',
				'rules' => 'trim|required',
				'type' => 'date'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_SpleenWeight',
				'label' => 'Масса селезенки',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'EvnPS_id',
				'label' => 'Идентификатор КВС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuSection_id',
				'label' => 'Отделение',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_aid',
				'label' => 'Патологоанатом',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_id',
				'label' => 'Лечащий врач',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'MedPersonal_zid',
				'label' => 'Заведующий отделением',
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
				'field' => 'PathologicCategoryType_id',
				'label' => 'Идентификатор сложности вскрытия',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'Server_id',
				'label' => 'Идентификатор сервера',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'setEvnMorfoHistologicProtoIsBad' => array(
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола',
				'rules' => 'required',
				'type' => 'id'
			),
			array(
				'field' => 'EvnMorfoHistologicProto_IsBad',
				'label' => 'Признак испорченного протокола',
				'rules' => 'required',
				'type' => 'int'
			)
		),
		'printEvnMorfoHistologicProto' => array(
			array(
				'field' => 'EvnMorfoHistologicProto_id',
				'label' => 'Идентификатор протокола',
				'rules' => 'required',
				'type' => 'id'
			)
		)
	);


	/**
	 * Description
	 */
	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->model('EvnMorfoHistologicProto_model', 'dbmodel');
	}


	/**
	 * Удаление протокола патоморфогистологического исследования
	 * Входящие данные: $_POST['EvnMorfoHistologicProto_id']
	 * На выходе: JSON-строка
	 * Используется: журнал протоколов патоморфогистологических исследований
	 */
	function deleteEvnMorfoHistologicProto() {
		$data = $this->ProcessInputData('deleteEvnMorfoHistologicProto', true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->deleteEvnMorfoHistologicProto($data);
		$this->ProcessModelSave($response, true, 'При удалении протокола патоморфогистологического исследования возникли ошибки')->ReturnData();

		return true;
	}


	/**
	 * Получение номера протокола патоморфогистологического исследования
	 * Входящие данные: нет
	 * На выходе: JSON-строка
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function getEvnMorfoHistologicProtoNumber() {
		$data = getSessionParams();

		$response = $this->dbmodel->getEvnMorfoHistologicProtoNumber($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		
		return true;
	}


	/**
	 * Получение списка ошибок клинической диагностики
	 * Входящие данные: $_POST['EvnMorfoHistologicProto_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function loadEvnMorfoHistologicDiagDiscrepancyGrid() {
		$data = $this->ProcessInputData('loadEvnMorfoHistologicDiagDiscrepancyGrid', true, true);
		if ( $data === false ) { return false; }
		
		$response = $this->dbmodel->loadEvnMorfoHistologicDiagDiscrepancyGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Получение списка присутствовавших при вскрытии
	 * Входящие данные: $_POST['EvnMorfoHistologicProto_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function loadEvnMorfoHistologicMemberGrid() {
		$data = $this->ProcessInputData('loadEvnMorfoHistologicMemberGrid', true, true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnMorfoHistologicMemberGrid($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}


	/**
	 * Получение данных для формы редактирования протокола патоморфогистологического исследования
	 * Входящие данные: $_POST['EvnMorfoHistologicProto_id']
	 * На выходе: JSON-строка
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function loadEvnMorfoHistologicProtoEditForm() {
		$data = $this->ProcessInputData('loadEvnMorfoHistologicProtoEditForm', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnMorfoHistologicProtoEditForm($data);
		$this->ProcessModelList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Получение списка протоколов патоморфогистологических исследований
	 * Входящие данные: <фильтры>
	 * На выходе: JSON-строка
	 * Используется: журнал протоколов патоморфогистологических исследований
	 */
	function loadEvnMorfoHistologicProtoGrid() {
		$data = $this->ProcessInputData('loadEvnMorfoHistologicProtoGrid', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->loadEvnMorfoHistologicProtoGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();

		return true;
	}


	/**
	 * Печать протокола патоморфогистологического исследования
	 * Входящие данные: $_GET['EvnMorfoHistologicProto_id']
	 * На выходе: форма для печати протокола патоморфогистологического исследования
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 *               журнал протоколов патоморфогистологических исследований
	 */
	function printEvnMorfoHistologicProto() {
		$this->load->library('parser');

		$data = $this->ProcessInputData('printEvnMorfoHistologicProto', true);
		if ( $data === false ) { return false; }

		// Получаем данные по направлению
		$response = $this->dbmodel->getEvnMorfoHistologicProtoFields($data);

		// Получение данных по ошибкам клинической диагностики
		$res = $this->dbmodel->loadEvnMorfoHistologicDiagDiscrepancyGrid($data);
		$by_underlying_disease = '';
		$by_complications = '';
		$by_concomitant_diseases = '';
		if (count($res) != 0) {
			$tmp1 = []; $tmp2 = []; $tmp3 = [];
			foreach ($res as $row) {
				// по основному заболеванию
				if ($row['DiagClinicalErrType_id'] == 1) {
					$tmp1[] = $row['DiagReasonDiscrepancy_Name'];
				}
				// по осложнениям
				if ($row['DiagClinicalErrType_id'] == 2) {
					$tmp2[] = $row['DiagReasonDiscrepancy_Name'];
				}
				// по сопутствующим заболевания
				if ($row['DiagClinicalErrType_id'] == 3) {
					$tmp3[] = $row['DiagReasonDiscrepancy_Name'];
				}
			}
			$by_underlying_disease = implode(', ', array_unique($tmp1));
			$by_complications = implode(', ', array_unique($tmp2));
			$by_concomitant_diseases = implode(', ', array_unique($tmp3));
		}

		// Получение списка присутствовавших при вскрытии
		$res = $this->dbmodel->loadEvnMorfoHistologicMemberGrid($data);
		$MedPersonal_Fio = '';
		if (count($res) != 0) {
			$MedPersonal_Fio = implode(', ', array_column($res, 'MedPersonal_Fio'));
		}

		if ( !is_array($response) || count($response) == 0 ) {
			echo 'Ошибка при получении данных по протоколу патоморфогистологического исследования';
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
		$template = 'print_evn_morfo_histologic_proto';

		$print_data = array(
			'DiagV_Code' => returnValidHTMLString($response[0]['DiagV_Code']),
			'DiagV_Descr' => returnValidHTMLString($response[0]['DiagV_Descr']),
			'DiagW_Code' => returnValidHTMLString($response[0]['DiagW_Code']),
			'DiagW_Descr' => returnValidHTMLString($response[0]['DiagW_Descr']),
			'DiagX_Code' => returnValidHTMLString($response[0]['DiagX_Code']),
			'DiagX_Descr' => returnValidHTMLString($response[0]['DiagX_Descr']),
			'DiagY_Code' => returnValidHTMLString($response[0]['DiagY_Code']),
			'DiagY_Descr' => returnValidHTMLString($response[0]['DiagY_Descr']),
			'DiagZ_Code' => returnValidHTMLString($response[0]['DiagZ_Code']),
			'DiagZ_Descr' => returnValidHTMLString($response[0]['DiagZ_Descr']),
			'EvnHistologicProtoData' => '&nbsp;',
			'EMHP_BitCount' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_BitCount']),
			'EMHP_BlockCount' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_BlockCount']),
			'EMHP_BrainWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_BrainWeight']),
			'EMHP_Day' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_setDay']),
			'EMHP_Epicrisis' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_Epicrisis']),
			'EMHP_HeartWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_HeartWeight']),
			'EMHP_KidneyLeftWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_KidneyLeftWeight']),
			'EMHP_KidneyRightWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_KidneyRightWeight']),
			'EMHP_LiverWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_LiverWeight']),
			'EMHP_LungsWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_LungsWeight']),
			'EMHP_MethodDescr' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_MethodDescr']),
			'EMHP_Month' => returnValidHTMLString(array_key_exists($response[0]['EvnMorfoHistologicProto_setMonth'], $arMonthOf) ? $arMonthOf[$response[0]['EvnMorfoHistologicProto_setMonth']] : ''),
			'EMHP_Num' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_Num']),
			'EMHP_NumDeath' => returnValidHTMLString($response[0]['DeathSvid_Num']),
			'EMHP_Protocol' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_ProtocolDescr']),
			'EMHP_Ser' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_Ser']),
			'EMHP_SpleenWeight' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_SpleenWeight']),
			'EMHP_Year' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_setYear']),
			'EMHP_DiagNameDirect' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_DiagNameDirect']),
			'EMHP_DiagNameSupply' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_DiagNameSupply']),
			'EMHP_ResultLabStudy' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_ResultLabStudy']),
			'EMHP_DiagPathology' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_DiagPathology']),
			'KLCityTown_Name' => '&nbsp;',
			'KLRgn_Name' => '&nbsp;',
			'Lpu_Address' => returnValidHTMLString($response[0]['Lpu_Address']),
			'Lpu_Name' => returnValidHTMLString($response[0]['Lpu_Name']),
			'MedPersonal_Fio' => returnValidHTMLString($response[0]['MedPersonal_Fio']),
			'MedPersonal_FioZ' => returnValidHTMLString($response[0]['MedPersonal_FioZ']),
			'Person_Age' => intval($response[0]['Person_Age']),
			'Person_Fio' => returnValidHTMLString($response[0]['Person_Fio']),
			'Person_PAddress' => returnValidHTMLString($response[0]['Person_PAddress']),
			'Post_Name' => '&nbsp;',
			'Person_Death_Date' => returnValidHTMLString($response[0]['EvnMorfoHistologicProto_deathDate']),
			'Person_Death_Time' => ($response[0]['EvnMorfoHistologicProto_deathTime'] == '00:00') ? '' : returnValidHTMLString($response[0]['EvnMorfoHistologicProto_deathTime']),
			'Sex_Code_1' => ($response[0]['Person_Sex'] == 1) ? 'text-decoration: underline;' : '',
			'Sex_Code_2' => ($response[0]['Person_Sex'] == 2) ? 'text-decoration: underline;' : '',
			'Diag_Direction' => returnValidHTMLString($response[0]['Diag_Direction']),
			'Diag_Income' => returnValidHTMLString($response[0]['Diag_Income']),
			'EvnPS_NumCard' => returnValidHTMLString($response[0]['EvnPS_NumCard']),
			'ByUnderlyingDisease' => $by_underlying_disease,
			'ByComplications' => $by_complications,
			'ByConcomitantDiseases' => $by_concomitant_diseases,
			'MedPersonal_Attended' => $MedPersonal_Fio
		);
		/*
		$description = preg_replace("/[ \n]+/", ' ', $response[0]['EvnMorfoHistologicProto_MacroDescr']);
		$description_array = explode(' ', $description);
		$description_line = 0;
		$histologic_conclusion = preg_replace("/[ \n]+/", ' ', $response[0]['EvnMorfoHistologicProto_HistologicConclusion']);
		$histologic_conclusion_array = explode(' ', $histologic_conclusion);

		if ( count($description_array) > 0 ) {
			$description_line++;
			$limit = 100;
			$print_data['EvnMorfoHistologicProto_Descr_1'] = "";

			for ( $i = 0; $i < count($description_array); $i++ ) {
				if ( strlen($print_data['EvnMorfoHistologicProto_Descr_' . $description_line] . $description_array[$i] ) <= $limit ) {
					$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] .= $description_array[$i] . ' ';
				}
				else {
					$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] = returnValidHTMLString($print_data['EvnMorfoHistologicProto_Descr_' . $description_line]);
					$description_line++;
					$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] = $description_array[$i] . ' ';
				}
			}
		}

		if ( count($histologic_conclusion_array) > 0 ) {
			$limit = 50;
			$line = 1;
			$print_data['EvnMorfoHistologicProto_HistologicConclusion_1'] = "";

			for ( $i = 0; $i < count($histologic_conclusion_array) && $line <= 7; $i++ ) {
				if ( strlen($print_data['EvnMorfoHistologicProto_HistologicConclusion_' . $line] . $histologic_conclusion_array[$i] ) <= $limit ) {
					$print_data['EvnMorfoHistologicProto_HistologicConclusion_' . $line] .= $histologic_conclusion_array[$i] . ' ';
				}
				else {
					$print_data['EvnMorfoHistologicProto_HistologicConclusion_' . $line] = returnValidHTMLString($print_data['EvnMorfoHistologicProto_HistologicConclusion_' . $line]);
					$line++;
					$limit = 100;
					$print_data['EvnMorfoHistologicProto_HistologicConclusion_' . $line] = $histologic_conclusion_array[$i] . ' ';
				}
			}
		}

		// Получаем микроскопические описания
		$response = $this->dbmodel->getEvnHistologicMicroData($data);

		if ( is_array($response) && count($response) > 0 ) {
			$description = "";
			$description_line++;

			for ( $i = 0; $i < count($response); $i++ ) {
				$description .= "Место забора материала: " . $response[$i]['HistologicSpecimenPlace_Name'] . ", ";
				$description .= "метод окраски: " . $response[$i]['HistologicSpecimenSaint_Name'] . ", ";
				$description .= "описание: " . $response[$i]['EvnHistologicMicro_Descr'] . ". ";
			}

			$description = preg_replace("/[ \n]+/", ' ', $description);
			$description_array = explode(' ', $description);

			if ( count($description_array) > 0 ) {
				$limit = 100;
				$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] = "";

				for ( $i = 0; $i < count($description_array); $i++ ) {
					if ( strlen($print_data['EvnMorfoHistologicProto_Descr_' . $description_line] . $description_array[$i] ) <= $limit ) {
						$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] .= $description_array[$i] . ' ';
					}
					else {
						$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] = returnValidHTMLString($print_data['EvnMorfoHistologicProto_Descr_' . $description_line]);
						$description_line++;
						$print_data['EvnMorfoHistologicProto_Descr_' . $description_line] = $description_array[$i] . ' ';
					}
				}
			}
		}
		*/
		return $this->parser->parse($template, $print_data);
	}


	/**
	 * Сохранение протокола патоморфогистологического исследования
	 * Входящие данные: <поля формы>
	 * На выходе: JSON-строка
	 * Используется: форма редактирования протокола патоморфогистологического исследования
	 */
	function saveEvnMorfoHistologicProto() {
		$data = $this->ProcessInputData('saveEvnMorfoHistologicProto', true);
		if ( $data === false ) { return false; }

		$response = $this->dbmodel->saveEvnMorfoHistologicProto($data);
		$val = $this->ProcessModelSave($response, true, 'Ошибка при сохранении протокола патоморфогистологического исследования')->GetOutData();
		
		if ( $val['success'] == true && !empty($data['evnMorfoHistologicMemberData']) ) {
			// Обработка списка присутствовавших при вскрытии
			$evnMorfoHistologicMemberData = json_decode($data['evnMorfoHistologicMemberData'], true);

			if ( is_array($evnMorfoHistologicMemberData) ) {
				$evnMorfoHistologicMemberBase = array('pmUser_id' => $data['pmUser_id'], 'EvnMorfoHistologicProto_id' => $val['EvnMorfoHistologicProto_id']);

				for ( $i = 0; $i < count($evnMorfoHistologicMemberData); $i++ ) {
					$evnMorfoHistologicMember = $evnMorfoHistologicMemberBase;

					if ( empty($evnMorfoHistologicMemberData[$i]['EvnMorfoHistologicMember_id']) || !is_numeric($evnMorfoHistologicMemberData[$i]['EvnMorfoHistologicMember_id']) ) {
						continue;
					}

					if ( empty($evnMorfoHistologicMemberData[$i]['MedStaffFact_id']) || !is_numeric($evnMorfoHistologicMemberData[$i]['MedStaffFact_id']) ) {
						continue;
					}

					if ( !isset($evnMorfoHistologicMemberData[$i]['RecordStatus_Code']) || !is_numeric($evnMorfoHistologicMemberData[$i]['RecordStatus_Code']) || !in_array($evnMorfoHistologicMemberData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					$evnMorfoHistologicMember['EvnMorfoHistologicMember_id'] = $evnMorfoHistologicMemberData[$i]['EvnMorfoHistologicMember_id'];
					$evnMorfoHistologicMember['MedStaffFact_id'] = $evnMorfoHistologicMemberData[$i]['MedStaffFact_id'];
					$evnMorfoHistologicMember['RecordStatus_Code'] = $evnMorfoHistologicMemberData[$i]['RecordStatus_Code'];

					switch ( $evnMorfoHistologicMember['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$response = $this->dbmodel->saveEvnMorfoHistologicMember($evnMorfoHistologicMember);
						break;

						case 3:
							$response = $this->dbmodel->deleteEvnMorfoHistologicMember($evnMorfoHistologicMember);
						break;
					}
				}
			}
		}

		if ( $val['success'] == true && !empty($data['evnMorfoHistologicDiagDiscrepancyData']) ) {
			// Обработка ошибок клинической диагностики
			$evnMorfoHistologicDiagDiscrepancyData = json_decode(toUTF($data['evnMorfoHistologicDiagDiscrepancyData']), true);

			if ( is_array($evnMorfoHistologicDiagDiscrepancyData) ) {
				$evnMorfoHistologicDiagDiscrepancyBase = array('pmUser_id' => $data['pmUser_id'], 'EvnMorfoHistologicProto_id' => $val['EvnMorfoHistologicProto_id']);

				for ( $i = 0; $i < count($evnMorfoHistologicDiagDiscrepancyData); $i++ ) {
					$evnMorfoHistologicDiagDiscrepancy = $evnMorfoHistologicDiagDiscrepancyBase;

					if ( empty($evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_id']) || !is_numeric($evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_id']) ) {
						continue;
					}

					if ( empty($evnMorfoHistologicDiagDiscrepancyData[$i]['DiagClinicalErrType_id']) || !is_numeric($evnMorfoHistologicDiagDiscrepancyData[$i]['DiagClinicalErrType_id']) ) {
						continue;
					}

					if ( empty($evnMorfoHistologicDiagDiscrepancyData[$i]['DiagReasonDiscrepancy_id']) || !is_numeric($evnMorfoHistologicDiagDiscrepancyData[$i]['DiagReasonDiscrepancy_id']) ) {
						continue;
					}

					if ( !isset($evnMorfoHistologicDiagDiscrepancyData[$i]['RecordStatus_Code']) || !is_numeric($evnMorfoHistologicDiagDiscrepancyData[$i]['RecordStatus_Code']) || !in_array($evnMorfoHistologicDiagDiscrepancyData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					if ( empty($evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_Note']) ) {
						$evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_Note'] = NULL;
					}
					else {
						$evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_Note'] = toAnsi($evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_Note']);
					}

					$evnMorfoHistologicDiagDiscrepancy['DiagClinicalErrType_id'] = $evnMorfoHistologicDiagDiscrepancyData[$i]['DiagClinicalErrType_id'];
					$evnMorfoHistologicDiagDiscrepancy['DiagReasonDiscrepancy_id'] = $evnMorfoHistologicDiagDiscrepancyData[$i]['DiagReasonDiscrepancy_id'];
					$evnMorfoHistologicDiagDiscrepancy['EvnMorfoHistologicDiagDiscrepancy_id'] = $evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_id'];
					$evnMorfoHistologicDiagDiscrepancy['EvnMorfoHistologicDiagDiscrepancy_Note'] = $evnMorfoHistologicDiagDiscrepancyData[$i]['EvnMorfoHistologicDiagDiscrepancy_Note'];
					$evnMorfoHistologicDiagDiscrepancy['RecordStatus_Code'] = $evnMorfoHistologicDiagDiscrepancyData[$i]['RecordStatus_Code'];

					switch ( $evnMorfoHistologicDiagDiscrepancy['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$response = $this->dbmodel->saveEvnMorfoHistologicDiagDiscrepancy($evnMorfoHistologicDiagDiscrepancy);
						break;

						case 3:
							$response = $this->dbmodel->deleteEvnMorfoHistologicDiagDiscrepancy($evnMorfoHistologicDiagDiscrepancy);
						break;
					}
				}
			}
		}

		$this->ReturnData($val);

		return true;
	}


	/**
	 * Установка/снятие признака испорченного протокола патоморфогистологического исследования
	 * Входящие данные: EvnDirectionHistologic_id, EvnDirectionHistologic_IsBad
	 * На выходе: JSON-строка
	 * Используется: журнал протоколов патоморфогистологическох исследований
	 */
	function setEvnMorfoHistologicProtoIsBad() {
		$data = $this->ProcessInputData('setEvnMorfoHistologicProtoIsBad', true);
		if ( $data === false ) { return false; }

		switch ( $data['EvnMorfoHistologicProto_IsBad'] ) {
			case 0:
				$data['EvnMorfoHistologicProto_IsBad'] = 1;
				$data['pmUser_pid'] = NULL;
			break;

			case 1:
				$data['EvnMorfoHistologicProto_IsBad'] = 2;
				$data['pmUser_pid'] = $data['pmUser_id'];
			break;

			default:
				echo json_return_errors('Неверное значение признака испорченного протокола патоморфогистологического исследования');
				return false;
			break;
		}

		$response = $this->dbmodel->setEvnMorfoHistologicProtoIsBad($data);

		$this->ProcessModelSave(array($response), true, 'Ошибка при установке/снятии признака испорченного протокола патоморфогистологического исследования')->ReturnData();

		return true;
	}
}
